(function (wp) {
	'use strict';
	var config = window.ycaitAttachmentEnhance || {};
	if (!config.ajaxUrl || !config.nonce) { return; }
	var allowedTools = [
		{ id: 'bg-blur', label: 'Background Blur' },
		{ id: 'bg-replace', label: 'Background Change' },
		{ id: 'color-correction', label: 'Color Correction' },
		{ id: 'enhance', label: 'Photo Enhance' },
		{ id: 'face-swap', label: 'Face Swap' },
		{ id: 'lighting', label: 'Lighting Enhance' },
		{ id: 'image-extender', label: 'Image Extender' },
		{ id: 'photo-colorize', label: 'Photo Colorize' },
		{ id: 'watermark-removal', label: 'Watermark Removal' }
	];
	var faceBodyTools = [
		{ id: 'abs-filter', label: 'Abs Filter' },
		{ id: 'aging', label: 'Aging Simulation' },
		{ id: 'smile', label: 'AI Smile' },
		{ id: 'body-reshape', label: 'Body Reshape' },
		{ id: 'breast-augmentation', label: 'Breast Augmentation' },
		{ id: 'face-reshape', label: 'Face Reshape' },
		{ id: 'face-lift', label: 'Face Lift' }
	];
	var beautyTools = [
		{ id: 'bangs-filter', label: 'Bangs Filter' },
		{ id: 'beard-style', label: 'Beard Style Generator' },
		{ id: 'hair-color', label: 'Hair Color' },
		{ id: 'hair-extension', label: 'Hair Extension' },
		{ id: 'hair-style', label: 'Hair Style' },
		{ id: 'hair-volume', label: 'Hair Volume' },
		{ id: 'look-try-on', label: 'Look Try On' },
		{ id: 'makeup-transfer', label: 'Makeup Transfer' },
		{ id: 'nail-transfer', label: 'Nail Transfer' },
		{ id: 'teeth-whitening', label: 'Teeth Whitening' },
		{ id: 'wavy-hair', label: 'Wavy Hair' }
	];
	var layer;
	var frame;
	var activeAttachmentId = 0;
	var detailViews = {};

	function getActiveAttachmentId(anchor) {
		var idNode = anchor && anchor.closest('[data-id]');
		if (idNode && idNode.getAttribute('data-id')) { return parseInt(idNode.getAttribute('data-id'), 10) || 0; }
		if (config.attachmentId) { return parseInt(config.attachmentId, 10) || 0; }
		if (!wp || !wp.media) { return 0; }
		var frames = [wp.media.frame];
		if (wp.media.frames) {
			Object.keys(wp.media.frames).forEach(function (key) { frames.push(wp.media.frames[key]); });
		}
		for (var i = frames.length - 1; i >= 0; i--) {
			var mediaFrame = frames[i];
			if (!mediaFrame || !mediaFrame.state || !mediaFrame.state()) { continue; }
			var state = mediaFrame.state();
			var selection = state.get && state.get('selection');
			var attachment = selection && selection.single ? selection.single() : null;
			if (attachment && attachment.id) { return parseInt(attachment.id, 10) || 0; }
			var image = state.get && state.get('image');
			if (image && image.id) { return parseInt(image.id, 10) || 0; }
		}
		var selected = document.querySelector('.media-modal .attachments .attachment.selected[data-id], .media-frame .attachments .attachment.selected[data-id]');
		return selected ? parseInt(selected.getAttribute('data-id'), 10) || 0 : 0;
	}

	function insertMenu(anchor, attachmentId, detailView, tools, title, className) {
		if (!anchor || !anchor.parentNode) { return null; }
		var siblings = Array.prototype.slice.call(anchor.parentNode.children);
		var existing = siblings.filter(function (element) { return element.classList.contains(className); })[0];
		if (existing) { return existing; }
		var container = document.createElement('div');
		container.className = 'ycait-ai-enhance-menu-container ' + className;
		var trigger = document.createElement('button');
		trigger.type = 'button';
		trigger.className = 'button ycait-ai-enhance-toggle';
		trigger.textContent = title;
		trigger.setAttribute('aria-expanded', 'false');
		trigger.setAttribute('aria-haspopup', 'true');
		var menu = document.createElement('div');
		menu.className = 'imgedit-popup-menu ycait-ai-enhance-menu';
		menu.setAttribute('role', 'menu');
		menu.hidden = true;
		tools.forEach(function (tool) {
			var item = document.createElement('button');
			item.type = 'button';
			item.className = 'button';
			item.setAttribute('role', 'menuitem');
			item.textContent = tool.label;
			item.addEventListener('click', function () { menu.hidden = true; trigger.setAttribute('aria-expanded', 'false'); openEnhancement(tool.id, attachmentId || getActiveAttachmentId(anchor)); });
			menu.appendChild(item);
		});
		trigger.addEventListener('click', function () {
			var open = 'true' !== trigger.getAttribute('aria-expanded');
			trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
			menu.hidden = !open;
			if (open) { menu.querySelector('button').focus(); }
		});
		container.appendChild(trigger);
		container.appendChild(menu);
		anchor.insertAdjacentElement('afterend', container);
		if (detailView) { detailViews[attachmentId] = detailView; }
		return container;
	}

	function insertControl(anchor, attachmentId, detailView) {
		var enhanceContainer = insertMenu(anchor, attachmentId, detailView, allowedTools, config.title || 'Enhance Image', 'ycait-ai-enhance-control');
		if (enhanceContainer) {
			var faceBodyContainer = insertMenu(enhanceContainer, attachmentId, detailView, faceBodyTools, config.faceBodyTitle || 'Enhance Face & Body', 'ycait-face-body-control');
			if (faceBodyContainer) { insertMenu(faceBodyContainer, attachmentId, detailView, beautyTools, config.beautyTitle || 'Enhance Beauty', 'ycait-beauty-control'); }
		}
	}

	function closeMenu() {
		document.querySelectorAll('.ycait-ai-enhance-menu').forEach(function (openMenu) { openMenu.hidden = true; });
		document.querySelectorAll('.ycait-ai-enhance-toggle[aria-expanded="true"]').forEach(function (openTrigger) { openTrigger.setAttribute('aria-expanded', 'false'); });
	}

	function openEnhancement(tool, attachmentId) {
		if (layer) { layer.remove(); }
		activeAttachmentId = parseInt(attachmentId || config.attachmentId, 10);
		if (!activeAttachmentId) { return; }
		var url = new URL(config.ajaxUrl);
		url.searchParams.set('action', 'ycait_attachment_enhancement');
		url.searchParams.set('nonce', config.nonce);
		url.searchParams.set('attachment_id', activeAttachmentId);
		url.searchParams.set('tool', tool);
		layer = document.createElement('div');
		layer.className = 'ycait-attachment-enhance-layer';
		layer.setAttribute('role', 'dialog');
		layer.setAttribute('aria-modal', 'true');
		var iframe = document.createElement('iframe');
		var toolLabel = allowedTools.concat(faceBodyTools, beautyTools).filter(function (entry) { return entry.id === tool; })[0];
		iframe.title = (config.title || 'Enhance Image') + ' — ' + (toolLabel || {}).label;
		iframe.src = url.toString();
		layer.appendChild(iframe);
		document.body.appendChild(layer);
		frame = iframe;
	}

	function closeEnhancement(reload) {
		if (layer) { layer.remove(); layer = null; frame = null; }
		if (reload) {
			if (config.mediaLibrary && wp && wp.media) {
				var attachment = wp.media.attachment(activeAttachmentId);
				attachment.fetch().done(function () {
					var view = detailViews[activeAttachmentId];
					if (view) { view.render(); }
				});
			} else { window.location.reload(); }
		}
	}

	function initialize() {
		var style = document.createElement('style');
		style.textContent = '.ycait-ai-enhance-menu-container{position:relative;display:inline-block;margin-left:4px;vertical-align:middle}.ycait-ai-enhance-toggle{background-image:none!important}.ycait-ai-enhance-menu{position:absolute;top:auto;bottom:100%;left:0;z-index:100001;display:flex;min-width:190px;max-height:320px;overflow:auto;flex-direction:column;margin-bottom:4px;padding:4px;background:#fff;border:1px solid #8c8f94;box-shadow:0 2px 8px rgba(0,0,0,.2)}.ycait-ai-enhance-menu[hidden]{display:none}.ycait-ai-enhance-menu .button{display:block;width:100%;margin:0;padding:6px 10px;border:0;background:#fff;text-align:left;white-space:nowrap}.ycait-ai-enhance-menu .button:hover,.ycait-ai-enhance-menu .button:focus{background:#f0f0f1}.ycait-attachment-enhance-layer{position:fixed;z-index:1000000;inset:0;display:flex;align-items:center;justify-content:center;padding:24px;background:rgba(20,24,28,.65)}.ycait-attachment-enhance-layer iframe{width:min(760px,100%);height:min(900px,100%);border:0;background:#fff;box-shadow:0 12px 40px rgba(0,0,0,.28)}';
		document.head.appendChild(style);
		var observer = new MutationObserver(function () {
			var editButton = document.getElementById('imgedit-open-btn');
			if (editButton && config.attachmentId) { insertControl(editButton, config.attachmentId); }
			document.querySelectorAll('.attachment-details .edit-attachment, .attachment-media-view .attachment-actions .edit-attachment').forEach(function (button) {
				var id = getActiveAttachmentId(button);
				insertControl(button, id);
			});
		});
		observer.observe(document.body, { childList: true, subtree: true });
		var editButton = document.getElementById('imgedit-open-btn');
		if (editButton && config.attachmentId) { insertControl(editButton, config.attachmentId); }
		document.querySelectorAll('.attachment-details .edit-attachment, .attachment-media-view .attachment-actions .edit-attachment').forEach(function (button) {
			insertControl(button, getActiveAttachmentId(button));
		});
		if (wp && wp.media && wp.media.view && wp.media.view.Attachment && wp.media.view.Attachment.Details) {
			var Details = wp.media.view.Attachment.Details;
			var originalRender = Details.prototype.render;
			Details.prototype.render = function () {
				originalRender.apply(this, arguments);
				var attributes = this.model && this.model.toJSON ? this.model.toJSON() : {};
				if ('image' !== attributes.type) { return this; }
				var nativeButton = this.el.querySelector('.edit-attachment');
				if (nativeButton) { insertControl(nativeButton, attributes.id, this); }
				return this;
			};
		}
		document.addEventListener('click', function (event) {
			if (!event.target.closest('.ycait-ai-enhance-menu-container')) { closeMenu(); }
		});
		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && layer) { closeEnhancement(false); }
			if (event.key === 'Escape') { closeMenu(); }
		});
		window.addEventListener('message', function (event) {
			if (event.origin !== window.location.origin || !frame || event.source !== frame.contentWindow || !event.data || Number(event.data.attachmentId) !== Number(activeAttachmentId)) { return; }
			if ('ycait-enhancement-discarded' === event.data.type) { closeEnhancement(false); }
			if ('ycait-enhancement-saved' === event.data.type) { closeEnhancement(true); }
		});
	}

	if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded', initialize); } else { initialize(); }
}(window.wp));
