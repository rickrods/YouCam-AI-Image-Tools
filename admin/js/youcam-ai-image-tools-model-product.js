(function (config) {
	'use strict';
	var notice = document.getElementById('ycait-model-notice');
	var choices = document.getElementById('ycait-model-choices');
	var generate = document.getElementById('ycait-model-generate');
	var generationStatus = document.getElementById('ycait-model-status');
	var result = document.getElementById('ycait-model-result');
	var taskId = '';

	(config.models || []).forEach(function (model, index) {
		var label = document.createElement('label');
		label.className = 'ycait-model-choice';
		var radio = document.createElement('input');
		radio.type = 'radio'; radio.name = 'model_gender'; radio.value = model.gender; radio.checked = index === 0;
		var image = document.createElement('img'); image.src = model.url; image.alt = '';
		var name = document.createElement('span'); name.textContent = model.name;
		label.appendChild(radio); label.appendChild(image); label.appendChild(name); choices.appendChild(label);
	});
	if (!(config.models || []).length) { notice.textContent = config.missingModel; generate.disabled = true; }
	else if (!readProductImageId()) { notice.textContent = config.missingImage; }
	try {
		window.parent.document.addEventListener('change', function (event) {
			if (event.target && '_thumbnail_id' === event.target.id && !result.children.length) { notice.textContent = readProductImageId() ? '' : config.missingImage; }
		});
	} catch (error) { /* The Generate action performs the same check before submitting. */ }

	generate.addEventListener('click', function () {
		var imageId = readProductImageId();
		if (!imageId) { notice.textContent = config.missingImage; return; }
		var selected = document.querySelector('input[name="model_gender"]:checked');
		if (!selected) { notice.textContent = config.missingModel; return; }
		result.textContent = '';
		notice.textContent = '';
		generationStatus.textContent = config.polling;
		generate.disabled = true; generate.textContent = config.generate;
		var data = new FormData();
		data.append('action', 'ycait_tool_run'); data.append('nonce', config.nonce); data.append('tool', 'model-product');
		data.append('product_image_id', String(imageId)); data.append('model_gender', selected.value); data.append('product_cat_ids', readProductCategoryIds().join(','));
		request(data).then(function (response) {
			if (!response.success) { throw new Error(response.data && response.data.message || config.error); }
			taskId = response.data.task_id;
			poll(taskId, 0);
		}).catch(function (error) { fail(error.message); });
	});

	function readProductImageId() {
		try {
			var parentDoc = window.parent.document;
			var input = parentDoc.querySelector('#_thumbnail_id');
			if (input && parseInt(input.value, 10)) { return parseInt(input.value, 10); }
			if (window.parent.wp && window.parent.wp.data) {
				var editor = window.parent.wp.data.select('core/editor');
				if (editor && parseInt(editor.getEditedPostAttribute('featured_media'), 10)) { return parseInt(editor.getEditedPostAttribute('featured_media'), 10); }
			}
		} catch (error) { /* The media picker remains usable if the editor does not expose the image field. */ }
		return 0;
	}
	function readProductCategoryIds() {
		try {
			var parentDoc = window.parent.document;
			var checked = parentDoc.querySelectorAll('#product_catchecklist input[type="checkbox"]:checked, .editor-post-taxonomies__hierarchical-terms-list input[type="checkbox"]:checked');
			if (checked.length) { return Array.prototype.map.call(checked, function (input) { return parseInt(input.value, 10); }).filter(Boolean); }
			if (window.parent.wp && window.parent.wp.data) {
				var editor = window.parent.wp.data.select('core/editor');
				var terms = editor && editor.getEditedPostAttribute('product_cat');
				if (Array.isArray(terms)) { return terms.map(function (term) { return parseInt(term, 10); }).filter(Boolean); }
			}
		} catch (error) { /* Categories are optional; clothing is the default route. */ }
		return [];
	}
	function request(data) {
		return fetch(config.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' }).then(function (response) { return response.json(); });
	}
	function poll(id, attempts) {
		if (attempts > 80) { fail(config.timeout || config.error); return; }
		window.setTimeout(function () {
			var data = new FormData();
			data.append('action', 'ycait_tool_poll'); data.append('nonce', config.nonce); data.append('tool', 'model-product'); data.append('task_id', id);
			request(data).then(function (response) {
				if (!response.success) { throw new Error(response.data && response.data.message || config.error); }
				if (response.data.next_task_id) { poll(response.data.next_task_id, attempts + 1); return; }
				var task = response.data.result && (response.data.result.data || response.data.result);
				var status = String(task && (task.task_status || task.status) || '').toLowerCase();
				if ('success' === status) { showResults(task, response.data.task_id || id, response.data.result_authorizations || []); return; }
				if ('error' === status || 'failed' === status) { fail(task.message || task.error || config.error); return; }
				generationStatus.textContent = config.polling;
				poll(id, attempts + 1);
			}).catch(function (error) { fail(error.message); });
		}, 2500);
	}
	function findUrls(value, urls, key) {
		if (!value) { return urls; }
		if (typeof value === 'string' && /^https:\/\//i.test(value) && /url|image|result|file/i.test(key || '')) { if (urls.indexOf(value) < 0) { urls.push(value); } }
		else if (Array.isArray(value)) { value.forEach(function (item) { findUrls(item, urls, key); }); }
		else if (typeof value === 'object') { Object.keys(value).forEach(function (childKey) { findUrls(value[childKey], urls, childKey); }); }
		return urls;
	}
	function showResults(task, id, authorizations) {
		var urls = findUrls(task.results || task, [], '');
		if (!urls.length) { fail(config.error); return; }
		notice.textContent = '';
		generationStatus.textContent = '';
		urls.forEach(function (url) {
			var image = document.createElement('img'); image.src = url; image.alt = 'Product shown on the selected model';
			var actions = document.createElement('div'); actions.className = 'ycait-model-result-actions';
			var save = document.createElement('button'); save.type = 'button'; save.className = 'button ycait-model-button'; save.textContent = config.save;
			var status = document.createElement('span'); status.setAttribute('aria-live', 'polite');
			var authorization = (authorizations || []).find(function (entry) { return entry && entry.url === url; });
			save.addEventListener('click', function () {
				save.disabled = true; save.textContent = config.saving; status.textContent = '';
				var data = new FormData();
				data.append('action', 'ycait_tool_save_result'); data.append('nonce', config.nonce); data.append('url', url); data.append('task_id', id);
				if (authorization && authorization.token && authorization.expires) { data.append('result_token', authorization.token); data.append('result_expires', String(authorization.expires)); }
				request(data).then(function (response) {
					if (!response.success) { throw new Error(response.data && response.data.message || config.error); }
					save.textContent = config.saved; status.textContent = config.addGallery;
					if (window.parent !== window) { window.parent.postMessage({ type: 'ycait-media-image-saved', attachmentId: response.data.attachment_id }, window.location.origin); }
				}).catch(function (error) { save.disabled = false; save.textContent = config.save; status.textContent = error.message || config.error; });
			});
			actions.appendChild(save); actions.appendChild(status);
			var item = document.createElement('div'); item.appendChild(image); item.appendChild(actions); result.appendChild(item);
		});
		generate.disabled = false; generate.textContent = config.generate;
	}
	function fail(message) { generationStatus.textContent = ''; notice.textContent = message || config.error; generate.disabled = false; generate.textContent = config.generate; }
}(window.ycaitModelProduct || {}));
