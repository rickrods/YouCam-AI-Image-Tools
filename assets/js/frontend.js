/**
 * YouCam AI Image Tools — Frontend JavaScript
 *
 * Handles the modal interaction flow:
 *   1. User clicks "Virtual Try-On" button on the product page
 *   2. Modal opens — user uploads a photo or picks a default model
 *   3. Photo is uploaded to the File API (AJAX)
 *   4. Try-on task is created (AJAX)
 *   5. Task status is polled until success or error
 *   6. Result image is displayed
 */
(function () {
	'use strict';

	var data = window.ycaitData || {};
	var i18n = data.i18n || {};
	var photoStorageKey = data.photoStorageKey || 'ycaitUploadedPhoto';

	var modal = null;
	var overlay = null;
	var state = {
		productId: '',
		productType: 'clothes',
		uploadToken: '',
		modelGender: '',
		source: 'upload', // 'upload' or 'model'
	};

	document.addEventListener('DOMContentLoaded', init);

	function init() {
		var buttons = document.querySelectorAll('.ycait-tryon-btn');
		buttons.forEach(function (btn) {
			btn.addEventListener('click', openModal);
		});
	}

	/**
	 * Build and open the modal.
	 */
	function openModal(e) {
		var btn = e.currentTarget;
		state.productId = btn.getAttribute('data-product-id') || '';
		state.productType = btn.getAttribute('data-product-type') || 'clothes';
		state.source = 'upload';
		state.uploadToken = '';
		state.modelGender = '';

		// Remove any existing modal.
		closeModal();

		overlay = document.createElement('div');
		overlay.className = 'ycait-modal-overlay';
		overlay.innerHTML = buildModalHTML();

		document.body.appendChild(overlay);
		document.body.style.overflow = 'hidden';

		modal = overlay.querySelector('.ycait-modal');

		// Animate in.
		requestAnimationFrame(function () {
			overlay.classList.add('ycait-active');
		});

		bindModalEvents();
		try {
			var savedPhoto = JSON.parse(sessionStorage.getItem(photoStorageKey) || 'null');
			if (savedPhoto && savedPhoto.upload_token) {
				state.uploadToken = savedPhoto.upload_token;
				state.source = 'upload';
				var preview = overlay.querySelector('#ycait-preview');
				preview.innerHTML = '<p>' + escHtml(i18n.savedPhoto || 'Your uploaded photo is ready for this session.') + '</p>';
				preview.style.display = 'block';
				enableStartButton(true);
			}
		} catch (ignore) {}
	}

	/**
	 * Build the modal HTML.
	 */
	function buildModalHTML() {
		var femaleImg = data.defaultFemale || '';
		var maleImg = data.defaultMale || '';
		var femaleName = data.modelNameFemale || i18n.femaleModel;
		var maleName = data.modelNameMale || i18n.maleModel;

		var html = '<div class="ycait-modal">';
		html += '<div class="ycait-modal-header">';
		html += '<h2 class="ycait-modal-title">' + escHtml(i18n.title) + '</h2>';
		html += '<button type="button" class="ycait-modal-close" aria-label="' + escHtml(i18n.close) + '">&times;</button>';
		html += '</div>';
		html += '<div class="ycait-modal-body">';
		if (state.productType !== 'clothes') {
			html += '<label class="ycait-gender-select">' + escHtml(i18n.gender || 'Model gender') + ' <select id="ycait-gender"><option value="female">' + escHtml(femaleName) + '</option><option value="male">' + escHtml(maleName) + '</option></select></label>';
		}

		// Source tabs.
		html += '<div class="ycait-source-tabs">';
		html += '<button type="button" class="ycait-source-tab ycait-active" data-source="upload">' + escHtml(i18n.uploadOwn) + '</button>';
		html += '<button type="button" class="ycait-source-tab" data-source="model">' + escHtml(femaleName) + ' & ' + escHtml(maleName) + '</button>';
		html += '</div>';

		// Upload panel.
		html += '<div class="ycait-source-panel ycait-active" data-panel="upload">';
		html += '<div class="ycait-dropzone" id="ycait-dropzone">';
		html += '<span class="ycait-dropzone-icon">&#128247;</span>';
		html += '<p class="ycait-dropzone-text">' + escHtml(i18n.dragDrop) + '</p>';
		html += '<input type="file" id="ycait-file-input" accept="image/jpeg,image/png,image/webp" />';
		html += '</div>';
		html += '<div class="ycait-upload-preview" id="ycait-preview" style="display:none;"></div>';
		html += '</div>';

		// Model panel.
		html += '<div class="ycait-source-panel" data-panel="model">';
		html += '<div class="ycait-models">';
		if (femaleImg) {
			html += '<div class="ycait-model-card" data-model-url="' + escAttr(femaleImg) + '" data-model-label="female">';
			html += '<img src="' + escAttr(femaleImg) + '" alt="' + escHtml(femaleName) + '" />';
			html += '<div class="ycait-model-card-label">' + escHtml(femaleName) + '</div>';
			html += '</div>';
		}
		if (maleImg) {
			html += '<div class="ycait-model-card" data-model-url="' + escAttr(maleImg) + '" data-model-label="male">';
			html += '<img src="' + escAttr(maleImg) + '" alt="' + escHtml(maleName) + '" />';
			html += '<div class="ycait-model-card-label">' + escHtml(maleName) + '</div>';
			html += '</div>';
		}
		if (!femaleImg && !maleImg) {
			html += '<p style="color:#999;text-align:center;">No default models are configured.</p>';
		}
		html += '</div>';
		html += '</div>';

		// Action button.
		html += '<button type="button" class="ycait-modal-action" id="ycait-start-btn" disabled>' + escHtml(i18n.title) + '</button>';

		html += '</div>'; // body
		html += '</div>'; // modal

		return html;
	}

	/**
	 * Bind all interactive events inside the modal.
	 */
	function bindModalEvents() {
		// Close button.
		var closeBtn = overlay.querySelector('.ycait-modal-close');
		closeBtn.addEventListener('click', closeModal);

		// Click outside modal to close.
		overlay.addEventListener('click', function (e) {
			if (e.target === overlay) {
				closeModal();
			}
		});

		// Escape key to close.
		document.addEventListener('keydown', handleEscape);

		// Source tabs.
		var tabs = overlay.querySelectorAll('.ycait-source-tab');
		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				var source = tab.getAttribute('data-source');
				switchSource(source);
			});
		});

		// Dropzone click → file input.
		var dropzone = overlay.querySelector('#ycait-dropzone');
		var fileInput = overlay.querySelector('#ycait-file-input');

		dropzone.addEventListener('click', function () {
			fileInput.click();
		});

		fileInput.addEventListener('change', function () {
			if (fileInput.files && fileInput.files[0]) {
				handleFileSelected(fileInput.files[0]);
			}
		});

		// Drag & drop.
		dropzone.addEventListener('dragover', function (e) {
			e.preventDefault();
			dropzone.classList.add('ycait-dragover');
		});
		dropzone.addEventListener('dragleave', function () {
			dropzone.classList.remove('ycait-dragover');
		});
		dropzone.addEventListener('drop', function (e) {
			e.preventDefault();
			dropzone.classList.remove('ycait-dragover');
			if (e.dataTransfer.files && e.dataTransfer.files[0]) {
				handleFileSelected(e.dataTransfer.files[0]);
			}
		});

		// Model cards.
		var modelCards = overlay.querySelectorAll('.ycait-model-card');
		modelCards.forEach(function (card) {
			card.addEventListener('click', function () {
				modelCards.forEach(function (c) { c.classList.remove('ycait-selected'); });
				card.classList.add('ycait-selected');
				state.modelGender = card.getAttribute('data-model-label') || '';
				state.source = 'model';
				enableStartButton();
			});
		});

		// Start button.
		var startBtn = overlay.querySelector('#ycait-start-btn');
		startBtn.addEventListener('click', startTryOn);
	}

	function handleEscape(e) {
		if (e.key === 'Escape') {
			closeModal();
		}
	}

	/**
	 * Switch between "upload" and "model" source panels.
	 */
	function switchSource(source) {
		state.source = source;

		// Update tabs.
		overlay.querySelectorAll('.ycait-source-tab').forEach(function (tab) {
			tab.classList.toggle('ycait-active', tab.getAttribute('data-source') === source);
		});

		// Update panels.
		overlay.querySelectorAll('.ycait-source-panel').forEach(function (panel) {
			panel.classList.toggle('ycait-active', panel.getAttribute('data-panel') === source);
		});

		// Reset state when switching.
		if (source === 'upload') {
			var preview = overlay.querySelector('#ycait-preview');
			var fileInput = overlay.querySelector('#ycait-file-input');
			if (fileInput) {
				fileInput.value = '';
			}
			try {
				var savedPhoto = JSON.parse(sessionStorage.getItem(photoStorageKey) || 'null');
				state.uploadToken = savedPhoto && savedPhoto.upload_token ? savedPhoto.upload_token : '';
				if (preview) {
					preview.innerHTML = state.uploadToken ? '<p>' + escHtml(i18n.savedPhoto || 'Your uploaded photo is ready for this session.') + '</p>' : '';
					preview.style.display = state.uploadToken ? 'block' : 'none';
				}
			} catch (ignore) { state.uploadToken = ''; }
		} else if (source === 'model') {
			state.uploadToken = '';
			// Check if a model is already selected.
			var selected = overlay.querySelector('.ycait-model-card.ycait-selected');
			if (!selected) {
				state.modelGender = '';
			}
		}

		updateStartButton();
	}

	/**
	 * Handle a file selected by the user — preview it and upload via AJAX.
	 */
	function handleFileSelected(file) {
		state.uploadToken = '';
		// Validate file type.
		var validTypes = ['image/jpeg', 'image/png', 'image/webp'];
		if (validTypes.indexOf(file.type) === -1) {
			showInlineError(i18n.invalidFile);
			return;
		}

		// Show preview.
		var reader = new FileReader();
		reader.onload = function (e) {
			var preview = overlay.querySelector('#ycait-preview');
			preview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" />';
			preview.style.display = 'block';
		};
		reader.readAsDataURL(file);

		// Upload the file.
		enableStartButton(false);
		uploadFile(file);
	}

	/**
	 * Upload the user's photo via AJAX → File API.
	 */
	function uploadFile(file) {
		var formData = new FormData();
		formData.append('action', 'ycait_upload');
		formData.append('nonce', data.nonce);
		formData.append('photo', file);

		fetch(data.ajaxUrl, {
			method: 'POST',
			body: formData
		})
			.then(function (res) { return res.json(); })
			.then(function (response) {
				if (response.success && response.data.upload_token) {
					state.uploadToken = response.data.upload_token;
					try { sessionStorage.setItem(photoStorageKey, JSON.stringify({ upload_token: state.uploadToken })); } catch (ignore) {}
					enableStartButton(true);
				} else {
					showInlineError((response.data && response.data.message) || i18n.error);
				}
			})
			.catch(function () {
				showInlineError(i18n.error);
			});
	}

	/**
	 * Start the try-on process.
	 */
	function startTryOn() {
		if ( ('upload' === state.source && !state.uploadToken) || ('model' === state.source && !state.modelGender) ) {
			showInlineError(i18n.noPhoto);
			return;
		}

		if (!state.productId) {
			showInlineError(i18n.error);
			return;
		}

		showLoading(i18n.processing, [i18n.uploading, i18n.processing, i18n.success]);

		// Step 1: Create the try-on task.
		var tryOnFormData = new FormData();
		tryOnFormData.append('action', 'ycait_tryon');
		tryOnFormData.append('nonce', data.nonce);
		tryOnFormData.append('source', state.source);
		tryOnFormData.append('upload_token', state.source === 'upload' ? state.uploadToken : '');
		tryOnFormData.append('model_gender', state.source === 'model' ? state.modelGender : '');
		tryOnFormData.append('garment_category', 'auto');
		tryOnFormData.append('product_id', state.productId);
		var genderSelect = overlay.querySelector('#ycait-gender');
		tryOnFormData.append('gender', genderSelect ? genderSelect.value : 'female');

		fetch(data.ajaxUrl, {
			method: 'POST',
			body: tryOnFormData
		})
			.then(function (res) { return res.json(); })
			.then(function (response) {
				if (response.success && response.data.task_id) {
					markStepDone(0);
					pollTask(response.data.task_id, 0, response.data.product_type || state.productType);
				} else {
					showErrorState((response.data && response.data.message) || i18n.error);
				}
			})
			.catch(function () {
				showErrorState(i18n.error);
			});
	}

	/**
	 * Poll the task status until success or error.
	 *
	 * @param {string} taskId The API task ID.
	 * @param {number} attempt Current poll attempt.
	 */
	function pollTask(taskId, attempt, productType) {
		attempt = attempt || 0;
		var maxAttempts = 60;
		var interval = 3000;

		if (attempt >= maxAttempts) {
			showErrorState(i18n.error);
			return;
		}

		var formData = new FormData();
		formData.append('action', 'ycait_poll');
		formData.append('nonce', data.nonce);
		formData.append('task_id', taskId);
		formData.append('product_type', productType || state.productType);

		fetch(data.ajaxUrl, {
			method: 'POST',
			body: formData
		})
			.then(function (res) { return res.json(); })
			.then(function (response) {
				if (!response.success) {
					showErrorState((response.data && response.data.message) || i18n.error);
					return;
				}

				var status = response.data.status;

				if (status === 'success') {
					markStepDone(1);
					var resultUrl = response.data.result_url || '';
					// If the API returns results_raw, try to extract URL from it
					if (!resultUrl && response.data.results_raw) {
						var raw = response.data.results_raw;
						if (Array.isArray(raw) && raw[0] && raw[0].url) {
							resultUrl = raw[0].url;
						} else if (raw && raw.url) {
							resultUrl = raw.url;
						}
					}
					if (resultUrl) {
						showResult(resultUrl);
					} else {
						showErrorState(i18n.error);
					}
				} else if (status === 'error') {
					showErrorState(response.data.message || i18n.error);
				} else {
					// Still processing — poll again after interval.
					setTimeout(function () {
						pollTask(taskId, attempt + 1, productType);
					}, interval);
				}
			})
			.catch(function () {
				showErrorState(i18n.error);
			});
	}

	// ----- UI helpers -----

	function enableStartButton(enabled) {
		var btn = overlay.querySelector('#ycait-start-btn');
		if (typeof enabled === 'undefined') {
			// Auto-determine whether the selected source is ready.
			btn.disabled = 'upload' === state.source ? !state.uploadToken : !state.modelGender;
		} else {
			btn.disabled = !enabled;
		}
	}

	function updateStartButton() {
		enableStartButton();
	}

	function showInlineError(message) {
		var preview = overlay.querySelector('#ycait-preview');
		preview.innerHTML = '<p style="color:#ea4335;font-weight:600;">' + escHtml(message) + '</p>';
		preview.style.display = 'block';
	}

	/**
	 * Replace modal body with a loading state.
	 */
	function showLoading(mainText, stepLabels) {
		var body = overlay.querySelector('.ycait-modal-body');
		body.innerHTML = '';

		var loading = document.createElement('div');
		loading.className = 'ycait-loading';

		var spinner = document.createElement('div');
		spinner.className = 'ycait-spinner';
		loading.appendChild(spinner);

		var text = document.createElement('p');
		text.className = 'ycait-loading-text';
		text.textContent = mainText;
		loading.appendChild(text);

		// Progress steps.
		var steps = document.createElement('div');
		steps.className = 'ycait-steps';

		stepLabels.forEach(function (label, index) {
			var step = document.createElement('div');
			step.className = 'ycait-step' + (index === 0 ? ' ycait-step-active' : '');
			step.setAttribute('data-step', index);

			var dot = document.createElement('span');
			dot.className = 'ycait-step-dot';
			step.appendChild(dot);

			var labelEl = document.createElement('span');
			labelEl.textContent = label;
			step.appendChild(labelEl);

			steps.appendChild(step);
		});

		loading.appendChild(steps);
		body.appendChild(loading);
	}

	function markStepDone(stepIndex) {
		var step = overlay.querySelector('.ycait-step[data-step="' + stepIndex + '"]');
		if (step) {
			step.classList.remove('ycait-step-active');
			step.classList.add('ycait-step-done');
		}

		var next = overlay.querySelector('.ycait-step[data-step="' + (stepIndex + 1) + '"]');
		if (next) {
			next.classList.add('ycait-step-active');
		}
	}

	/**
	 * Show the result image.
	 */
	function showResult(imageUrl) {
		markStepDone(2);

		var body = overlay.querySelector('.ycait-modal-body');
		body.innerHTML = '';

		var result = document.createElement('div');
		result.className = 'ycait-result';

		var img = document.createElement('img');
		img.className = 'ycait-result-image';
		img.src = imageUrl;
		img.alt = 'Virtual Try-On Result';
		result.appendChild(img);

		var text = document.createElement('p');
		text.className = 'ycait-result-text';
		text.textContent = i18n.success;
		result.appendChild(text);

		var tryAgainBtn = document.createElement('button');
		tryAgainBtn.type = 'button';
		tryAgainBtn.className = 'ycait-modal-action';
		tryAgainBtn.textContent = i18n.tryAgain;
		tryAgainBtn.addEventListener('click', function () {
			closeModal();
			// Re-open fresh.
			var btn = document.querySelector('.ycait-tryon-btn');
			if (btn) {
				btn.click();
			}
		});
		result.appendChild(tryAgainBtn);

		body.appendChild(result);
	}

	/**
	 * Show an error state in the modal body.
	 */
	function showErrorState(message) {
		var body = overlay.querySelector('.ycait-modal-body');
		body.innerHTML = '';

		var error = document.createElement('div');
		error.className = 'ycait-error';

		var icon = document.createElement('div');
		icon.className = 'ycait-error-icon';
		icon.innerHTML = '&#9888;';
		error.appendChild(icon);

		var text = document.createElement('p');
		text.className = 'ycait-error-text';
		text.textContent = message;
		error.appendChild(text);

		var tryAgainBtn = document.createElement('button');
		tryAgainBtn.type = 'button';
		tryAgainBtn.className = 'ycait-modal-action';
		tryAgainBtn.textContent = i18n.tryAgain;
		tryAgainBtn.addEventListener('click', function () {
			closeModal();
			var btn = document.querySelector('.ycait-tryon-btn');
			if (btn) {
				btn.click();
			}
		});
		error.appendChild(tryAgainBtn);

		body.appendChild(error);
	}

	/**
	 * Close and remove the modal.
	 */
	function closeModal() {
		if (overlay) {
			overlay.classList.remove('ycait-active');
			var existingOverlay = overlay;
			setTimeout(function () {
				if (existingOverlay && existingOverlay.parentNode) {
					existingOverlay.parentNode.removeChild(existingOverlay);
				}
			}, 300);
		}
		overlay = null;
		modal = null;
		document.body.style.overflow = '';
		document.removeEventListener('keydown', handleEscape);
	}

	// ----- Sanitization helpers -----

	function escHtml(str) {
		var div = document.createElement('div');
		div.textContent = str || '';
		return div.innerHTML;
	}

	function escAttr(str) {
		return (str || '').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
	}
})();
