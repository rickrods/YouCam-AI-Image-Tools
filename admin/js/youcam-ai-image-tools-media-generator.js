(function (wp, config) {
	'use strict';
	if (!wp || !wp.media || !wp.media.view || !wp.media.view.MediaFrame || !wp.media.view.MediaFrame.Select) { return; }

	var SelectFrame = wp.media.view.MediaFrame.Select;
	var originalInitialize = SelectFrame.prototype.initialize;
	var originalBrowseRouter = SelectFrame.prototype.browseRouter;
	var activeFrame = null;
	var frameOrigin = window.location.origin;
	var generatorOpenClass = 'ycait-generator-dialog-open';
	var galleryPickerRequestedUntil = 0;
	document.addEventListener('click', function (event) {
		var gallery = event.target.closest('#woocommerce-product-images');
		if (!gallery) { return; }
		var addButton = event.target.closest('.add_product_images, .add-product-images, button');
		if (addButton && /add product gallery images/i.test((addButton.textContent || '').trim())) { galleryPickerRequestedUntil = Date.now() + 5000; }
	}, true);

	function addFrameStyles() {
		if (document.getElementById('ycait-media-generator-frame-styles')) { return; }
		var style = document.createElement('style');
		style.id = 'ycait-media-generator-frame-styles';
		style.textContent = '.media-frame.' + generatorOpenClass + ' .media-frame-content{bottom:61px!important;height:auto!important}';
		document.head.appendChild(style);
	}

	SelectFrame.prototype.initialize = function () {
		originalInitialize.apply(this, arguments);
		this.ycaitProductGallery = !!(config.modelProduct && Date.now() < galleryPickerRequestedUntil);
		this.on('content:render:ycait-generate', this.ycaitRenderGenerator, this);
		if (this.ycaitProductGallery) { this.on('content:render:ycait-model-product', this.ycaitRenderModelProduct, this); }
		var frame = this;
		this.$el.on('click.ycaitMediaGenerator', '.media-router .media-menu-item', function () {
			window.setTimeout(function () {
				if (frame.content.mode() !== 'ycait-generate') { frame.$el.removeClass(generatorOpenClass); }
			}, 0);
		});
	};

	SelectFrame.prototype.browseRouter = function (router) {
		if (originalBrowseRouter) { originalBrowseRouter.apply(this, arguments); }
		var frame = this;
		router.set('ycait-generate', {
			text: config.title || 'AI Generate',
			priority: 80,
			click: function () {
				addFrameStyles();
				frame.content.mode('ycait-generate');
			}
		});
		if (config.modelProduct && this.ycaitProductGallery) {
			router.set('ycait-model-product', {
				text: config.modelProductTitle || 'Model Product',
				priority: 81,
				click: function () { frame.content.mode('ycait-model-product'); }
			});
		}
	};

	SelectFrame.prototype.ycaitRenderGenerator = function () {
		var frame = this;
		activeFrame = frame;
		var view = new wp.media.View({ className: 'ycait-media-generator-view', controller: frame });
		var iframe = document.createElement('iframe');
		var contentArea = frame.$el.find('.media-frame-content')[0];
		var url = new URL(config.ajaxUrl);
		url.searchParams.set('action', 'ycait_media_generator');
		url.searchParams.set('nonce', config.nonce);
		iframe.title = config.title || 'AI Generate';
		view.el.style.cssText = 'position:absolute;top:0;right:0;bottom:0;left:0;width:100%;overflow:hidden';
		iframe.style.cssText = 'position:absolute;top:0;left:0;display:block;width:100%;height:100%;min-height:0;border:0;background:#fff';
		iframe.src = url.toString();
		view.el.appendChild(iframe);
		frame.content.set(view);
		if (contentArea) {
			var syncViewSize = function () {
				var bounds = contentArea.getBoundingClientRect();
				view.el.style.height = Math.max(0, bounds.height) + 'px';
			};
			syncViewSize();
			if (window.ResizeObserver) {
				var resizeObserver = new ResizeObserver(syncViewSize);
				resizeObserver.observe(contentArea);
				view.once('remove', function () { resizeObserver.disconnect(); });
			} else {
				var resizeHandler = function () { syncViewSize(); };
				window.addEventListener('resize', resizeHandler);
				view.once('remove', function () { window.removeEventListener('resize', resizeHandler); });
			}
		}
	};

	SelectFrame.prototype.ycaitRenderModelProduct = function () {
		var frame = this;
		activeFrame = frame;
		var view = new wp.media.View({ className: 'ycait-model-product-view', controller: frame });
		var iframe = document.createElement('iframe');
		var contentArea = frame.$el.find('.media-frame-content')[0];
		var url = new URL(config.ajaxUrl);
		url.searchParams.set('action', 'ycait_media_model_product');
		url.searchParams.set('nonce', config.modelProductNonce || '');
		iframe.title = config.modelProductTitle || 'Model Product';
		view.el.style.cssText = 'position:absolute;inset:0;width:100%;overflow:hidden';
		iframe.style.cssText = 'position:absolute;inset:0;display:block;width:100%;height:100%;border:0;background:#fff';
		iframe.src = url.toString();
		view.el.appendChild(iframe);
		frame.content.set(view);
		if (contentArea) {
			var sync = function () { view.el.style.height = Math.max(0, contentArea.getBoundingClientRect().height) + 'px'; };
			sync();
			if (window.ResizeObserver) { var observer = new ResizeObserver(sync); observer.observe(contentArea); view.once('remove', function () { observer.disconnect(); }); }
		}
	};

	window.addEventListener('message', function (event) {
		if (event.origin !== frameOrigin || !event.data) { return; }
		if (event.data.type === 'ycait-media-generator-modal') {
			if (activeFrame && activeFrame.$el) {
				addFrameStyles();
				activeFrame.$el.toggleClass(generatorOpenClass, !!event.data.open);
			}
			return;
		}
		if (event.data.type !== 'ycait-media-image-saved') { return; }
		var id = parseInt(event.data.attachmentId, 10);
		if (!id) { return; }
		if (!activeFrame || !activeFrame.state || !activeFrame.state()) { return; }
		var selection = activeFrame.state().get('selection');
		if (!selection) { return; }
		var attachment = wp.media.attachment(id);
		attachment.fetch().done(function () {
			selection.reset([]);
			selection.add(attachment);
			attachment.trigger('selection:single', attachment);
			if (activeFrame.$el) { activeFrame.$el.find('.media-frame-content').attr('aria-label', 'AI generated image selected'); }
		});
	});
}(window.wp, window.ycaitMediaGenerator || {}));
