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

PhotoPicker.prototype.select = function(event) {
    let input = event.target
    let self = this
    let container = this.el
    if (input.value) {
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