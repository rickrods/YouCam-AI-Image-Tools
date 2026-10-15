(function () {
	'use strict';
	var config = window.ycaitTools || {};
	var modal;
	var modalContent;
	var activeTrigger;
	document.addEventListener('DOMContentLoaded', function () {
		modal = document.querySelector('.ycait-tools-modal');
		modalContent = modal.querySelector('.ycait-tools-dialog-content');
		modalContent.addEventListener('change', handleFileChange);
		modalContent.addEventListener('dragover', handleFileDragOver);
		modalContent.addEventListener('dragleave', handleFileDragLeave);
		modalContent.addEventListener('drop', handleFileDrop);
		modalContent.addEventListener('click', handleImageSourceClick);
		document.addEventListener('click', function (event) {
			var launcher = event.target.closest('.ycait-tool-launch');
			if (launcher) { openModal(launcher); return; }
			if (event.target.closest('.ycait-tools-dialog-close') || event.target === modal.querySelector('.ycait-tools-modal-backdrop')) { closeModal(); }
		});
		document.addEventListener('keydown', function (event) {
			if (event.key === 'Escape' && !modal.hidden) { closeModal(); }
		});
		if (config.attachmentEnhance && config.initialTool) {
			var initialTrigger = document.querySelector('.ycait-tool-launch[data-tool="' + config.initialTool + '"]');
			if (initialTrigger) { openModal(initialTrigger); }
		}
		document.addEventListener('submit', function (event) {
			var form = event.target;
			if (!form.matches('.ycait-tool-form')) { return; }
			event.preventDefault();
			if (!validateImageSources(form)) { return; }
			var submit = form.querySelector('[type="submit"]') || form.querySelector('button');
			var resultBox = form.nextElementSibling;
			if (!resultBox || !resultBox.classList.contains('ycait-tool-result')) {
				resultBox = document.createElement('section');
				resultBox.className = 'ycait-tool-result';
				resultBox.setAttribute('aria-live', 'polite');
				form.insertAdjacentElement('afterend', resultBox);
			}
			resultBox.classList.add('is-visible');
			resultBox.setAttribute('aria-busy', 'true');
			var values = new FormData(form);
			values.append('action', 'ycait_tool_run'); values.append('nonce', config.nonce); values.append('tool', form.dataset.tool);
			submit.disabled = true; resultBox.textContent = config.polling || 'Submitting…';
			request(values).then(function (response) {
				if (!response.success) { throw new Error(response.data && response.data.message || config.error); }
				poll(form.dataset.tool, response.data.task_id, 0, resultBox, submit, response.data.stage || '');
			}).catch(function (error) { resultBox.textContent = error.message || config.error; resultBox.setAttribute('aria-busy', 'false'); submit.disabled = false; });
		});
	});
	function openModal(trigger) {
		var tool = trigger.getAttribute('data-tool');
		var template = document.querySelector('template[data-tool-template="' + tool + '"]');
		if (!template) { return; }
		activeTrigger = trigger;
		modalContent.innerHTML = '';
		modalContent.appendChild(template.content.cloneNode(true));
		addFieldDescriptions(tool);
		if (config.attachmentEnhance) {
			var sourceUpload = modalContent.querySelector('.ycait-upload-control');
			if (sourceUpload) { sourceUpload.remove(); }
			var source = document.createElement('input');
			source.type = 'hidden'; source.name = 'attachment_id'; source.value = config.attachmentId;
			var toolForm = modalContent.querySelector('.ycait-tool-form');
			if (toolForm) { toolForm.appendChild(source); }
		}
		if ('image-extender' === tool) {
			setExtenderDimensions(modalContent.querySelector('.ycait-tool-form'), config.sourceDimensions || null);
		}
		modal.querySelector('#ycait-tools-dialog-title').textContent = trigger.getAttribute('data-title') || '';
		modal.hidden = false;
		document.body.classList.add('ycait-tools-modal-open');
		notifyMediaGeneratorModal(true);
		var firstInput = modalContent.querySelector('input, textarea, select, button');
		if (firstInput) { firstInput.focus(); }
	}
	function closeModal() {
		if (!modal || modal.hidden) { return; }
		if (modalContent.querySelector('[aria-busy="true"]')) { return; }
		modalContent.querySelectorAll('.ycait-admin-file-preview[data-object-url]').forEach(function (preview) { URL.revokeObjectURL(preview.dataset.objectUrl); });
		modal.hidden = true;
		modalContent.innerHTML = '';
		document.body.classList.remove('ycait-tools-modal-open');
		notifyMediaGeneratorModal(false);
		if (config.attachmentEnhance && window.parent !== window) { window.parent.postMessage({ type: 'ycait-enhancement-discarded', attachmentId: config.attachmentId }, window.location.origin); }
		if (activeTrigger) { activeTrigger.focus(); }
	}
	function notifyMediaGeneratorModal(open) {
		if (config.mediaEmbed && window.parent !== window) {
			window.parent.postMessage({ type: 'ycait-media-generator-modal', open: open }, window.location.origin);
		}
	}
	function addFieldDescriptions(tool) {
		var descriptions = config.fieldDescriptions && config.fieldDescriptions[tool];
		var form = modalContent.querySelector('.ycait-tool-form');
		if (!descriptions || !form) { return; }
		Object.keys(descriptions).forEach(function (field) {
			var input = Array.prototype.find.call(form.querySelectorAll('[name]'), function (element) { return element.name === field; });
			if (!input) { return; }
			var help = document.createElement('small');
			help.className = 'ycait-field-description';
			help.textContent = descriptions[field];
			var upload = input.closest('.ycait-upload-control');
			if (upload) {
				upload.appendChild(help);
				return;
			}
			var label = input.closest('label');
			if (label) { label.appendChild(help); }
		});
	}
	function validateImageSources(form) {
		var controls = Array.prototype.slice.call(form.querySelectorAll('[data-image-source-control]'));
		for (var index = 0; index < controls.length; index++) {
			var control = controls[index];
			var librarySelected = control.querySelector('[data-image-source="library"].is-active');
			var attachment = control.querySelector('input[type="hidden"][name$="_attachment_id"]');
			var file = control.querySelector('.ycait-admin-file-input');
			if ((librarySelected && attachment && attachment.value) || (!librarySelected && file && file.files && file.files.length)) { continue; }
			if (librarySelected) {
				var button = control.querySelector('.ycait-library-select');
				if (button) { button.focus(); }
				window.alert('Choose an image from the Media Library.');
			} else if (file) {
				file.required = true;
				file.focus();
				file.reportValidity();
			}
			return false;
		}
		return true;
	}
	function handleImageSourceClick(event) {
		var tab = event.target.closest('[data-image-source]');
		if (tab) { setImageSource(tab.closest('[data-image-source-control]'), tab.dataset.imageSource); return; }
		var choose = event.target.closest('.ycait-library-select');
		if (choose) { openLibraryPicker(choose.closest('[data-image-source-control]')); }
	}
	function setImageSource(control, source) {
		if (!control) { return; }
		control.querySelectorAll('[data-image-source]').forEach(function (tab) {
			var active = tab.dataset.imageSource === source;
			tab.classList.toggle('is-active', active);
			tab.setAttribute('aria-selected', active ? 'true' : 'false');
		});
		control.querySelectorAll('[data-image-source-panel]').forEach(function (panel) {
			var active = panel.dataset.imageSourcePanel === source;
			panel.classList.toggle('is-active', active);
			panel.hidden = !active;
		});
		var file = control.querySelector('.ycait-admin-file-input');
		var attachment = control.querySelector('input[type="hidden"][name$="_attachment_id"]');
		if ('library' === source) {
			if (file) { clearFile(file, config.fileHelp || 'JPG or PNG, smaller than 10 MB'); file.required = false; }
		} else {
			if (attachment) { attachment.value = ''; }
			var preview = control.querySelector('.ycait-library-preview');
			if (preview) { preview.textContent = ''; preview.hidden = true; }
			if (file) { file.required = true; }
		}
	}
	function openLibraryPicker(control) {
		var media = window.wp && window.wp.media ? window.wp.media : (window.parent !== window && window.parent.wp ? window.parent.wp.media : null);
		if (!media) { window.alert('The Media Library is unavailable. Reload the page and try again.'); return; }
		var frame = media({ title: 'Select an image', button: { text: 'Use this image' }, library: { type: 'image' }, multiple: false });
		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			var input = control.querySelector('input[type="hidden"][name$="_attachment_id"]');
			var preview = control.querySelector('.ycait-library-preview');
			if (!input || !preview || !attachment || !attachment.id) { return; }
			input.value = attachment.id;
			preview.textContent = '';
			var image = document.createElement('img');
			image.src = attachment.url;
			image.alt = attachment.alt || attachment.title || 'Selected image preview';
			preview.appendChild(image);
			preview.hidden = false;
		});
		frame.open();
	}
	function request(data) {
		return fetch(config.ajaxUrl, { method: 'POST', body: data, credentials: 'same-origin' }).then(function (response) { return response.json(); });
	}
	function handleFileChange(event) {
		var input = event.target;
		if (!input.matches('.ycait-admin-file-input') || !input.files || !input.files[0]) { return; }
		var file = input.files[0];
		if (['image/jpeg', 'image/png'].indexOf(file.type) === -1) { clearFile(input, config.invalidFile || 'Use a JPG or PNG image.'); return; }
		if (file.size >= 10 * 1024 * 1024) { clearFile(input, config.largeFile || 'Image files must be smaller than 10 MB.'); return; }
		var control = input.closest('.ycait-upload-control');
		var zone = input.closest('.ycait-admin-dropzone');
		var preview = control.querySelector('.ycait-admin-file-preview');
		var previousUrl = preview.dataset.objectUrl;
		if (previousUrl) { URL.revokeObjectURL(previousUrl); }
		zone.querySelector('.ycait-admin-file-name').textContent = file.name;
		zone.classList.add('has-file');
		preview.textContent = '';
		var image = document.createElement('img');
		image.src = URL.createObjectURL(file);
		image.alt = config.previewAlt || 'Selected image preview';
		preview.dataset.objectUrl = image.src;
		preview.appendChild(image);
		preview.hidden = false;
		if (input.name === 'photo' && input.closest('.ycait-tool-form') && 'image-extender' === input.closest('.ycait-tool-form').dataset.tool) {
			image.onload = function () { setExtenderDimensions(input.closest('.ycait-tool-form'), [image.naturalWidth, image.naturalHeight]); };
		}
	}
	function setExtenderDimensions(form, dimensions) {
		if (!form || !dimensions || !dimensions[0] || !dimensions[1]) { return; }
		var width = form.querySelector('[name="output_width"]');
		var height = form.querySelector('[name="output_height"]');
		if (!width || !height) { return; }
		width.value = Math.min(4096, Math.max(Number(width.value) || 0, Math.ceil(dimensions[0] * 1.25)));
		height.value = Math.min(4096, Math.max(Number(height.value) || 0, Math.ceil(dimensions[1] * 1.25)));
	}
	function clearFile(input, message) {
		var control = input.closest('.ycait-upload-control');
		var zone = input.closest('.ycait-admin-dropzone');
		var preview = control.querySelector('.ycait-admin-file-preview');
		if (preview.dataset.objectUrl) { URL.revokeObjectURL(preview.dataset.objectUrl); }
		input.value = '';
		zone.classList.remove('has-file');
		zone.querySelector('.ycait-admin-file-name').textContent = message;
		preview.textContent = '';
		preview.hidden = true;
		delete preview.dataset.objectUrl;
	}
	function dropzoneFromEvent(event) {
		return event.target.closest && event.target.closest('.ycait-admin-dropzone');
	}
	function handleFileDragOver(event) {
		var zone = dropzoneFromEvent(event);
		if (!zone) { return; }
		event.preventDefault();
		zone.classList.add('is-dragging');
	}
	function handleFileDragLeave(event) {
		var zone = dropzoneFromEvent(event);
		if (!zone || (event.relatedTarget && zone.contains(event.relatedTarget))) { return; }
		zone.classList.remove('is-dragging');
	}
	function handleFileDrop(event) {
		var zone = dropzoneFromEvent(event);
		if (!zone) { return; }
		event.preventDefault();
		zone.classList.remove('is-dragging');
		var input = zone.querySelector('.ycait-admin-file-input');
		var file = event.dataTransfer && event.dataTransfer.files && event.dataTransfer.files[0];
		if (!file) { return; }
		try {
			var transfer = new DataTransfer();
			transfer.items.add(file);
			input.files = transfer.files;
			input.dispatchEvent(new Event('change', { bubbles: true }));
		} catch (error) {
			clearFile(input, config.invalidFile || 'Choose the image using the file picker.');
		}
	}
	function poll(tool, id, count, box, submit, stage) {
		if (count > 60) { box.textContent = config.timeout || 'Processing is taking longer than expected. Check again later.'; box.setAttribute('aria-busy', 'false'); submit.disabled = false; return; }
		var data = new FormData(); data.append('action', 'ycait_tool_poll'); data.append('nonce', config.nonce); data.append('tool', tool); data.append('task_id', id); data.append('stage', stage || '');
		setTimeout(function () {
			request(data).then(function (response) {
				if (!response.success) { throw new Error(response.data && response.data.message || config.error); }
				var payload = response.data.result;
				if (response.data.next_task_id) { poll(tool, response.data.next_task_id, count + 1, box, submit, response.data.stage || ''); return; }
				var task = payload.data || payload;
				var status = String(task.task_status || task.status || '').toLowerCase();
				if ('success' === status) { showResults(box, task, response.data.task_id || id, response.data.result_authorizations || []); box.setAttribute('aria-busy', 'false'); submit.disabled = false; return; }
				if ('error' === status || 'failed' === status) { box.textContent = task.message || task.error || config.error; box.setAttribute('aria-busy', 'false'); submit.disabled = false; return; }
				box.textContent = (config.polling || 'Working…') + ' (' + (count + 1) + ')'; poll(tool, id, count + 1, box, submit, stage);
			}).catch(function (error) { box.textContent = error.message || config.error; box.setAttribute('aria-busy', 'false'); submit.disabled = false; });
		}, 3000);
	}
	function findUrls(value, urls) {
		if (!value) { return urls; }
		if (typeof value === 'string' && /^https?:\/\//i.test(value)) { if (urls.indexOf(value) === -1) { urls.push(value); } }
		else if (Array.isArray(value)) { value.forEach(function (item) { findUrls(item, urls); }); }
		else if (typeof value === 'object') {
			Object.keys(value).forEach(function (key) {
				var item = value[key];
				if (typeof item === 'string' && /^https?:\/\//i.test(item) && /url|image|result|file/i.test(key)) { if (urls.indexOf(item) === -1) { urls.push(item); } }
				else if (item && typeof item === 'object') { findUrls(item, urls); }
			});
		}
		return urls;
	}
	function showResults(box, task, taskId, authorizations) {
		box.textContent = '';
		var urls = findUrls(task, []);
		if (!urls.length) { box.textContent = config.resultMissing || 'The task finished, but the API response did not include a result image URL.'; return; }
		var heading = document.createElement('h3');
		heading.className = 'ycait-tool-result-title';
		heading.textContent = config.resultTitle || 'Your result';
		box.appendChild(heading);
		var message = document.createElement('p');
		message.className = 'ycait-tool-result-message';
		message.textContent = config.resultReady || 'Your image is ready.';
		box.appendChild(message);
		urls.forEach(function (url) {
			var authorization = (authorizations || []).find(function (entry) { return entry && entry.url === url; }) || null;
			var item = document.createElement('div'); item.className = 'ycait-tool-result-item';
			var image = document.createElement('img'); image.src = url; image.alt = 'Generated or enhanced result';
			var link = document.createElement('a'); link.href = url; link.target = '_blank'; link.rel = 'noopener noreferrer'; link.textContent = config.openImage || 'Open full-size image';
			var actions = document.createElement('div'); actions.className = 'ycait-tool-result-actions';
			var saveButton = document.createElement('button'); saveButton.type = 'button'; saveButton.className = 'button ycait-result-save-button'; saveButton.textContent = config.attachmentEnhance ? (config.saveEnhancement || 'Save Enhancement') : (config.save || 'Save to Media Library');
			var saveStatus = document.createElement('span'); saveStatus.className = 'ycait-tool-save-status'; saveStatus.setAttribute('aria-live', 'polite');
			saveButton.addEventListener('click', function () { config.attachmentEnhance ? saveEnhancement(url, taskId, authorization, saveButton, saveStatus) : saveResult(url, taskId, authorization, saveButton, saveStatus); });
			actions.appendChild(saveButton);
			if (config.attachmentEnhance) {
				var discardButton = document.createElement('button'); discardButton.type = 'button'; discardButton.className = 'button ycait-enhancement-discard'; discardButton.textContent = config.discardEnhancement || 'Discard Enhancement';
				discardButton.addEventListener('click', function () {
					if (window.parent !== window) { window.parent.postMessage({ type: 'ycait-enhancement-discarded', attachmentId: config.attachmentId }, window.location.origin); }
				});
				actions.appendChild(discardButton);
			}
			actions.appendChild(saveStatus);
			item.appendChild(image); item.appendChild(link); item.appendChild(actions); box.appendChild(item);
		});
	}
	function addResultAuthorization(data, authorization) {
		if (authorization && authorization.token && authorization.expires) {
			data.append('result_token', authorization.token);
			data.append('result_expires', String(authorization.expires));
		}
	}
	function saveResult(url, taskId, authorization, button, status) {
		var data = new FormData();
		data.append('action', 'ycait_tool_save_result'); data.append('nonce', config.nonce); data.append('url', url); data.append('task_id', taskId || '');
		addResultAuthorization(data, authorization);
		button.disabled = true; button.textContent = config.saving || 'Saving…'; status.textContent = '';
		request(data).then(function (response) {
			if (!response.success) { throw new Error(response.data && response.data.message || config.error); }
			button.textContent = config.saved || 'Saved to Media Library';
			if (response.data.edit_url) {
				var editLink = document.createElement('a'); editLink.href = response.data.edit_url; editLink.textContent = config.viewAttachment || 'View attachment'; editLink.className = 'ycait-tool-attachment-link';
				status.appendChild(editLink);
			}
			if (config.mediaEmbed && window.parent !== window) {
				window.parent.postMessage({ type: 'ycait-media-image-saved', attachmentId: response.data.attachment_id }, window.location.origin);
				status.textContent = config.mediaSelectMessage || 'Image selected in the media library.';
			}
		}).catch(function (error) {
			button.disabled = false; button.textContent = config.save || 'Save to Media Library'; status.textContent = error.message || config.error;
		});
	}
	function saveEnhancement(url, taskId, authorization, button, status) {
		var data = new FormData();
		data.append('action', 'ycait_tool_save_enhancement'); data.append('nonce', config.nonce); data.append('url', url); data.append('attachment_id', config.attachmentId); data.append('task_id', taskId || '');
		addResultAuthorization(data, authorization);
		button.disabled = true; button.textContent = config.saving || 'Saving…'; status.textContent = '';
		request(data).then(function (response) {
			if (!response.success) { throw new Error(response.data && response.data.message || config.saveError || config.error); }
			button.textContent = config.saved || 'Enhancement saved';
			status.textContent = config.saved || 'Enhancement saved';
			if (window.parent !== window) { window.parent.postMessage({ type: 'ycait-enhancement-saved', attachmentId: response.data.attachment_id }, window.location.origin); }
		}).catch(function (error) {
			button.disabled = false; button.textContent = config.saveEnhancement || 'Save Enhancement'; status.textContent = error.message || config.saveError || config.error;
		});
	}
}());
