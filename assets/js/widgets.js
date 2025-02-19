function PhotoPicker(el, isMultiple, photos = []) {
	this.el = el
	this.isMultiple = isMultiple
	el.className = 'photopicker'
	if (!isMultiple) el.classList.add('single')

	let input = document.createElement('input')
	input.type = 'file'
	input.accept = '.jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff, .wepb|image/*'
	input.onchange = this.select.bind(this)
	let pic = document.createElement('div')
	pic.className = 'thumb selector'
	pic.appendChild(input)
	el.appendChild(pic)

	if (photos.length && !isMultiple) {
		photos = [photos[0]]
	}
	for (let url of photos) {
		this.add(url)
	}

	if (photos.length && !isMultiple) {
		el.lastElementChild.style.display = 'none'
	}

	this.updateValue()
}

PhotoPicker.init = function(els) {
	els.forEach(function(el) {
		let pickerContainer = document.createElement('div')
		pickerContainer.picker = new PhotoPicker(pickerContainer, el.dataset.isMultiple)
		pickerContainer.field = el
		el.photopicker = pickerContainer
		el.parentNode.insertBefore(pickerContainer, el)
		el.style.display = 'none'
	});
}

PhotoPicker.prototype.createCropModal = function(input) {
	let file = input.files[0]
	let { element: modal, showFunction } = PhotoPicker.prototype.modal
	if (!modal) {
		modal = document.createElement('div')
		modal.className = 'modal photo-crop-modal'
		modal.innerHTML = '<div class="header">Select image area</div><div class="content"></div><div class="footer"><center><button class="btn" onclick="this.closest(\'.modal\').style.display = \'none\'">Close</button></center></div>'
		
		let content = modal.querySelector('.content')
		content.innerHTML = '<div><image class="cropper"></div><div class="preview" style="float: left; width: 64px; height: 64px;"></div><div class="preview" style="float: left; width: 24px; height: 24px;"></div>'
		
		document.body.appendChild(modal)
	}
	modal.target = input
	let image = modal.querySelector('.cropper')
	
	if (file) {
		image.onload = () => {
			//URL.revokeObjectURL(image.src);
			
			var previews = image.parentNode.parentNode.querySelectorAll('.preview');
			
			var previewReady = false;
			
			var minCroppedWidth = 180;
			var minCroppedHeight = 180;
			
			var cropper = new Cropper(image, {
				aspectRatio: 1,
				viewMode: 1,
				ready: function () {
					var clone = this.cloneNode();

					clone.className = '';
					clone.style.cssText = (
						'display: block;' +
						'width: 100%;' +
						'min-width: 0;' +
						'min-height: 0;' +
						'max-width: none;' +
						'max-height: none;'
					);

					previews.forEach(function (elem) {
						elem.innerHTML = '';
						elem.appendChild(clone.cloneNode());
					});
					
					previewReady = true;
					
					setTimeout(this.crop, 100);
				},

				crop: function (event) {
					var data = event.detail;
					var cropper = this.cropper;
					var imageData = cropper.getImageData();
					
					var width = Math.round(data.width);
					var height = Math.round(data.height);
					
					if (
						width < minCroppedWidth
						|| height < minCroppedHeight
					) {
						cropper.setData({
							width: Math.max(minCroppedWidth, width),
							height: Math.max(minCroppedHeight, height),
						});
					}
					
					var previewAspectRatio = data.width / data.height;
					if (!previewReady) {
						setTimeout(arguments.callee.bind(this), 50, event);
						return;
					}

					previews.forEach(function (elem) {
						var previewImage = elem.getElementsByTagName('img').item(0);
						var previewWidth = elem.offsetWidth;
						var previewHeight = previewWidth / previewAspectRatio;
						var imageScaledRatio = data.width / previewWidth;

						elem.style.height = previewHeight + 'px';
						previewImage.style.width = imageData.naturalWidth / imageScaledRatio + 'px';
						previewImage.style.height = imageData.naturalHeight / imageScaledRatio + 'px';
						previewImage.style.marginLeft = -data.x / imageScaledRatio + 'px';
						previewImage.style.marginTop = -data.y / imageScaledRatio + 'px';
					});
					
					if (input) {
						var data = cropper.getData()
						input.cropData = { x: Math.round(data.x), y: Math.round(data.y), size: Math.round(data.width) }
					}
				},
			});
			modal.cropper = cropper
		}
		image.src = URL.createObjectURL(file);
	}
	
	if (showFunction) showFunction()
	else element.style.display = 'block'
}

PhotoPicker.prototype.select = function(event) {
	let input = event.target
	let self = this
	let container = this.el
	if (input.value) {
		if (event instanceof Event) {
			delete input.cropData
		}
		input.picker = self
		let picker = input.closest('.photopicker')
		if (picker.hasAttribute('select-area') && window.Cropper && !input.cropData) {
			self.createCropModal(input)
			return
		}
		if (input.parentNode.dataset.imageUrl) {
			let url = input.parentNode.dataset.imageUrl
			if (TaskManager.addedPhotos.indexOf(url) != -1) {
				removePhoto(url.replace(/^\/media/, ''))
				TaskManager.addedPhotos.splice(TaskManager.addedPhotos.indexOf(url), 1)
			} else {
				TaskManager.removedPhotos.push(url)
			}
		}
		
		let form = new FormData()
		form.append('file', input.files[0])
		if (picker.params) {
			for (let key in picker.params) {
				form.append(key, picker.params[key])
			}
		}
		if (input.cropData) {
			for (let key in input.cropData) {
				form.append(key, input.cropData[key])
			}
		}
		fetch(file_upload_url, {
			method: 'POST',
			body: form
		})
			.then(res => res.json())
			.then(res => {
				if (res.url) {
					let pic = null

					if (input.parentNode.dataset.imageUrl) {
						/* Replacing existing image */
						pic = input.parentNode
						pic.dataset.imageUrl = res.url
						pic.style.background = 'url("' + res.url + '") center center / contain no-repeat'
					} else {
						/* Adding new image */
						pic = self.add(res.url)
					}

					TaskManager.addedPhotos.push(res.url)

					if (!container.picker.isMultiple) {
						container.lastElementChild.style.display = 'none'
					}

					input.value = ''

					self.updateValue()
				}
			})
	}
}

PhotoPicker.prototype.setPhotos = function(photos) {
	let container = this.el
	for (let i = 0; i < container.children.length-1; i++) {
		container.removeChild(container.children[0])
	}
	if (!photos || !photos.length) {
		container.lastElementChild.style.display = ''
		this.updateValue()
		return
	}
	if (photos.length && !this.isMultiple) {
		photos = [photos[0]]
	}
	for (let url of photos) {
		this.add(url)
	}
	if (!container.picker.isMultiple) {
		container.lastElementChild.style.display = 'none'
	}
	this.updateValue()
}

PhotoPicker.prototype.add = function(url) {
	let container = this.el
	let pic = document.createElement('div')
	pic.className = 'thumb'
	let self = this

	if (url.indexOf(imagePath) !== 0) {
		url = imagePath + url
	}

	pic.dataset.imageUrl = url
	pic.style.background = 'url("' + url + '") center center / contain no-repeat'

	let removeBtn = document.createElement('div')
	removeBtn.className = 'remove_btn'
	pic.appendChild(removeBtn)
	removeBtn.onclick = function() {
		let url = this.parentNode.dataset.imageUrl
		if (TaskManager.addedPhotos.indexOf(url) != -1) {
			removePhoto(url.replace(/^\/media/, ''))
			TaskManager.addedPhotos.splice(TaskManager.addedPhotos.indexOf(url), 1)
		} else {
			TaskManager.removedPhotos.push(url)
		}
		let parent = this.parentNode.parentNode
		parent.removeChild(this.parentNode)
		if (parent.children.length == 1) {
			parent.children[0].style.display = ''
		}
		self.updateValue()
	}
	if (container.lastElementChild && container.lastElementChild.classList.contains('selector')) {
		container.insertBefore(pic, container.lastElementChild)
	} else {
		container.appendChild(pic)
	}

	let input = document.createElement('input')
	input.type = 'file'
	input.accept = '.jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff, .wepb|image/*'
	input.onchange = this.select.bind(this)
	pic.appendChild(input)

	return pic
}

PhotoPicker.prototype.updateValue = function() {
	let container = this.el
	if (container.field && container.field instanceof HTMLInputElement) {
		let photos = Array.prototype.slice.call(container.children, 0, -1).map(function (el) {
			return el.dataset.imageUrl
		})
		container.field.value = container.picker.isMultiple ? JSON.stringify(photos) : (photos.length ? photos[0] : '')
	}
}

function initTagsWidgets() {
	let els = document.querySelectorAll('[name="tags"]')
	els.forEach(function(el) {
		let tagsContainer = document.createElement('div')
		tagsContainer.classList.add('form-control')
		tagsContainer.classList.add('tags_list')
		tagsContainer.input = el
		el.tagsWidget = tagsContainer
		el.parentNode.insertBefore(tagsContainer, el)
		el.style.display = 'none'
		tagsContainer.innerHTML = '<div contenteditable=""></div>'
		tagsContainer.lastElementChild.addEventListener('keydown', function(event) {
			let value = this.textContent
			if (event.key.match(/[\[\]{}()<>&^*`|?!@~$#]/)) {
				event.preventDefault()
				return
			}
			if (event.key == 'Enter' && value.length) {
				let tag = document.createElement('div')
				tag.className = 'tag'
				tag.textContent = value
				this.parentNode.insertBefore(tag, this)
				this.textContent = ''
				event.preventDefault()
				this.parentNode.input.value = JSON.stringify(Array.prototype.slice.call(this.parentNode.children, 0, -1)
					.map(function(el) {
						return el.textContent
					})
				);
			} else if (event.key == 'Backspace' && !value.length && this.previousElementSibling) {
				let value = this.previousElementSibling.textContent
				this.parentNode.removeChild(this.previousElementSibling)
				this.textContent = value
				this.focus()
				document.getSelection().collapse(this, 1)
				event.preventDefault()
				this.parentNode.input.value = JSON.stringify(Array.prototype.slice.call(this.parentNode.children, 0, -1)
					.map(function(el) {
						return el.textContent
					})
				);
			}
		})
	});
}