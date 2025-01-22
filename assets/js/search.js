function initSearch() {
	document.querySelectorAll('.search-input-field').forEach(input => {
		input.addEventListener('input', debounce(function(event) {
			if (event.target.value.length > 1) {
				// Search by item ID
				if (event.target.value.slice(0, 1) == '#') {
					toggleSearchOptions(false)
					suggestItemIds(event.target)
					return
				}
				// Search by author username
				if (event.target.value.slice(0, 1) == '@') {
					toggleSearchOptions(false)
					suggestAuthorUsernames(event.target)
					event.target.oldValue = event.target.value
					return
				}
				clearSearchSuggestions()
				reloadContent()
			} else if (document.querySelector('.search_wrap .dropdown').clientHeight > 0) {
				clearSearchSuggestions()
				if (event.target.value.match(/^@|#$/)) {
					document.querySelector('.search_wrap .dropdown').style.display = 'none'
				}
				reloadContent()
			}
		}, 500));
		input.addEventListener('focus', function(event) {
			document.querySelector('.search_wrap .dropdown').style.display = ''
			if (event.target.value.slice(0, 1) == '#') {
				toggleSearchOptions(false)
				suggestItemIds(event.target)
				return
			}
			if (event.target.value.slice(0, 1) == '@') {
				toggleSearchOptions(false)
				suggestAuthorUsernames(event.target)
				event.target.oldValue = event.target.value
				return
			}
		});
		input.addEventListener('blur', function(event) {
			if (event.target.value.length > 1) {
				// Search by item ID
				if (event.target.value.slice(0, 1) == '#') {
					if (!event.target.classList.contains('line') && !event.target.parentNode.classList.contains('line')) {
						searchByItemId(event.target.value.slice(1))
					}
					return
				}
				// Search by author username
				if (event.target.value.slice(0, 1) == '@') {
					window.searchTimer = setTimeout(() => {
						clearSearchSuggestions()
						document.querySelector('.search_wrap .dropdown').style.display = 'none'
						searchByAuthorUsername(event.target.value)
					}, 800)
					return
				}
			} else {
				clearSearchSuggestions()
				document.querySelector('.search_wrap .dropdown').style.display = 'none'
				reloadContent(true, true)
				toggleSearchOptions(true)
			}
		});
		document.querySelectorAll('.search_wrap .options input[type="checkbox"]').forEach(input => {
			input.addEventListener('change', () => reloadContent(true, true))
		})
	});
	
	document.getElementById('search_variants').addEventListener('click', function(event) {
		let line = event.target
		while (!line.classList.contains('line') && line.parentNode) {
			line = line.parentNode
		}
		if (window.searchTimer) {
			clearTimeout(window.searchTimer)
			window.searchTimer = null
		}
		if (line.classList.contains('user')) {
			document.querySelector('.search_wrap input').value = line.lastElementChild.firstChild.textContent.trim()
			searchByAuthorUsername(line)
		} else {
			document.querySelector('.search_wrap input').value = '#' + line.dataset.id
			searchByItemId(line)
		}
	});
	
	function toggleSearchOptions(value) {
		document.querySelector('.search_wrap .options').style.display = value ? '' : 'none'
		document.querySelector('.search_wrap .list').style.display = !value ? 'block' : ''
	}

	async function suggestItemIds(input) {
		fetch('/getEntities?type=' + window.contentType + '&search=%23' + input.value.slice(1))
			.then(res => res.json())
			.then(res => {
				document.querySelector('.search_wrap .dropdown').style.display = ''
				clearSearchSuggestions()
				let list = document.getElementById('search_variants')
				if (list.configured) list = list.children[0]
				
				for (let item of res.data) {
					let line = createItemLine(item)
					list.appendChild(line)
				}
				if (!list.clientHeight) document.querySelector('.search_wrap .dropdown').style.visibility = 'hidden'
				setTimeout(function() {
					list.parentNode.style.height = list.clientHeight + 'px'
					document.querySelector('.search_wrap .dropdown').style.visibility = ''
				}, 50)
			});
	}
	
	function clearSearchSuggestions() {
		let list = document.getElementById('search_variants')
		if (list.configured) list = list.children[0]
		list.innerHTML = ''
	}
	
	function searchByItemId(item) {
		//document.querySelector('.search_wrap').classList.remove('active');
		document.querySelector('.search_wrap .dropdown').style.display = 'none'
		
		if (typeof item == 'string' || typeof item == 'number') {
			item = document.querySelector('#search_variants .line[data-id="' + item + '"]')
			if (!item) {
				return
			}
		}
		let line = item.cloneNode(true);
		let container = document.querySelector('#page-content .items-list');
		container.innerHTML = '';
		updatePagination(1);
		container.appendChild(line);
		document.querySelector('.main .form').style.visibility = '';
		line.click();
	}
	
	async function suggestAuthorUsernames(input) {
		fetch('/getItems?type=user&search=' + input.value.slice(1))
			.then(res => res.json())
			.then(res => {
				
				let list = document.getElementById('search_variants')
				if (list.configured) list = list.children[0]
				
				if (input.oldValue.length != input.value.length && res.total < pageSize && list.children.length < pageSize) {
					return
				}
				
				document.querySelector('.search_wrap .dropdown').style.display = ''
				clearSearchSuggestions()
				
				for (let item of res.data) {
					let line = document.createElement('div')
					line.className = 'line user'
					line.innerHTML = '<div>' + item.id + '</div><div>' + generateThumbHtml(item) + '</div><div>@' + item.username + ' <span></span></div>'
					let fullName = [item.firstname, item.lastname].join(' ').trim()
					if (fullName.length) line.lastElementChild.lastElementChild.textContent = '(' + fullName + ')'
					list.appendChild(line)
				}
				if (!list.clientHeight) document.querySelector('.search_wrap .dropdown').style.visibility = 'hidden'
				setTimeout(function() {
					list.parentNode.style.height = list.clientHeight + 'px'
					document.querySelector('.search_wrap .dropdown').style.visibility = ''
				}, 50)
			});
	}
	
	function searchByAuthorUsername(item) {
		document.querySelector('.search_wrap .dropdown').style.display = 'none'
		
		let username = ''
		
		if (typeof item == 'string' || typeof item == 'number') {
			username = item
		} else {
			username = item.lastElementChild.firstChild.textContent.trim()
		}
		document.querySelector('.search-input-field').value = username
		document.querySelectorAll('.pagination select').forEach(el => el.value = '1')
		
		reloadContent(true, true)
	}
}