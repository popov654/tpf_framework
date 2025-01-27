<?php if (!isset($TPF_REQUEST) || !isset($TPF_REQUEST['session'])) die(); ?><!DOCTYPE html>
<html>
	<head>
		<title>Admin panel</title>
		<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css" />
		<link rel="stylesheet" href="style.css" />
		<script src="bootstrap/js/bootstrap.min.js"></script>
		<style>
			body {
				padding: 0;
				margin: 0;
				font-family: Segoe, Arial;
			}
			#header {
				display: flex;
				width: 100%;
				height: 60px;
				box-sizing: border-box;
				padding: 0 0 0 16px;
				background: #344d4a;
				color: #f8f8f8;
				gap: 60px;
				align-items: stretch;
				position: relative;
				z-index: 10;
				/* justify-content: space-between; */
			}
			#header .logo {
				font-size: 28px;
				line-height: 60px;
				flex-shrink: 0;
				white-space: nowrap;
			}
			#header nav {
				height: 100%;
				display: table;
				overflow: hidden;
			}
			#header a {
				color: #d5d6d8;
			}
			#header > nav > a {
				position: relative;
				margin: 4px 8px;
				text-decoration: none;
				text-align: center;
				display: table-cell;
				vertical-align: middle;
				width: 90px;
				height: 100%;
			}
			#header a.active {
				 color: #f3f3f3;
				background: rgba(120, 130, 138, 0.3);
			}
			#header a.active::after {
				display: block;
				content: '';
				width: 100%;
				height: 2px;
				background: #cce;
				position: absolute;
				bottom: 1px;
				left: 0;
			}
			#header a:hover:not(.active), #menu:hover .header {
				color: #e2e2e2;
				background: rgba(110, 120, 128, 0.18);
			}
			#header > .spacer {
				flex: 10 10 2000px;
			}
			#menu {
				float: right;
				position: relative;
				margin: 0;
				flex-shrink: 0;
				height: 100%:
			}
			#menu .header {
				display: flex;
				align-items: center;
				gap: 8px;
				cursor: pointer;
				height: 100%;
				padding: 8px 20px 8px 8px;
			}
			#menu .header .thumb {
				width: 32px;
				height: 32px;
			}
			#menu .menu {
				height: 0;
				overflow: hidden;
				background: #324a46;
				color: #eee;
				opacity: 1;
				border-radius: 0 0 4px 4px;
			}
			#menu .menu .item {
				padding: 4px 10px;
				list-style-type: none;
				cursor: default;
			}
			#menu .menu .item:hover {
				background: #073;
			}
			#menu:hover .menu {
				height: auto;
			}
			#subheader {
				width: 100%;
				height: 60px;
				box-sizing: border-box;
				padding: 16px;
			}
			#subheader-content {
				display: flex;
				align-items: baseline;
				gap: 36px;
			}
			#subheader-content > * {
				flex: 0 1 max-content;
			}
			#subheader-content .item_types {
				font-size: 0.86em;
			}
			#subheader .link {
				color: #666;
				cursor: pointer;
			}
			#subheader .link:hover {
				color: #386d6a;
				text-decoration: underline;
			}
			#subheader .link:not(:last-child)::after {
				display: inline-block;
				content: '';
				margin: 0 6px 0 9px;
				height: 15px;
				border-right: 2px solid #555;
				position: relative;
				top: 2px;
			}
			#subheader .link.active {
				color: #386d6a;
				font-weight: bold;
				text-decoration: underline;
			}
			#pages {
            height: calc(100vh - 160px);
         }
			#page-content {
				display: flex;
				height: 100%;
				gap: 10px;
				flex-wrap: nowrap;
			}
			#page-content > * {
				height: 100%;
				border: 1px solid #e8e8e8;
				border-radius: 3px;
				margin: 16px;
				flex: 1 1 400px;
				padding: 10px;
			}
			
			#page-content > .aside {
				width: 35%;
			}
			#page-content .toolbar {
				height: 30px;
				margin: 2px 0;
				padding: 1px;
				display: flex;
				flex-wrap: auto;
			}
			.toolbar .btn_group {
				flex: 0 0 auto;
				display: inline-flex;
				align-items: center;
			}
			.toolbar .btn_group.spacer {
				flex: 1 1 300px;
				min-width: 0;
			}
			.toolbar .form-control {
				margin: 0 4px;
				height: calc(100% - 2px);
			}
			.toolbar .btn {
				flex: 0 0 auto;
				width: 26px;
				height: 26px;
				padding: 5px;
				border-radius: 4px;
				cursor: pointer;
			}
			.toolbar .btn:hover {
				background-color: #fefefe;
				border: 1px solid #e6e6e6;
				border-top: 1px solid #ececec;
				border-bottom: 0.5px solid #eaeaef;
				border-right: 1px solid #ececf3;
				box-shadow: 0 0 3px 1.5px rgba(30, 30, 36, 0.05);
			}
			.toolbar .btn:focus, .toolbar .btn:active {
				box-shadow: none;
			}
			.toolbar .btn_separator {
				flex: 0 0.5 1px;
				display: inline-block;
				vertical-align: middle;
				width: 1px;
				height: 21px;
				margin: 2px 4px;
				background: #e3e3e3;
				position: relative;
				top: 1px;
			}
			#page-content hr {
				border-bottom: 1px solid #888;
				margin: 4px auto 13px;
				width: calc(100% - 6px);
				background: transparent;
			}
			.toolbar .btn {
				background-size: 24px;
				background-repeat: no-repeat;
				background-position: 0px 0px;
			}
			.toolbar .btn:active {
				outline: none;
			}
			.toolbar > .hidden {
            display: none;
         }
			.btn.new {
				background-image: url('/tpf/icons/file-new.svg');
			}
			.btn.move {
				background-image: url('/tpf/icons/file-move.svg');
			}
			.btn.delete {
				background-image: url('/tpf/icons/delete.svg');
			}
			.btn.import {
				background-image: url('/tpf/icons/import.svg');
			}
			.btn.export {
				background-image: url('/tpf/icons/export.svg');
			}
			.btn.check-all {
				background-image: url('/tpf/icons/check-all.svg');
			}
			.btn.check-none {
				background-image: url('/tpf/icons/check-none.svg');
			}
			.btn.restore {
            background-image: url('/tpf/icons/restore.svg');
         }
			
			.btn_group.search {
				margin-right: 4px;
			}
			.btn.search {
				background-image: url('/tpf/icons/search.svg');
			}
			.btn.clear-text {
				width: 20px;
				height: 20px;
				padding: 1px;
				background-image: url('/tpf/icons/clear-text.svg');
				background-position: center;
				background-size: 20px;
			}
			.search_wrap {
				position: relative;
				border-radius: 4px;
				box-sizing: border-box;
				border: 1px solid transparent;
				padding: 0 1px 0 0;
				width: 28px;
				height: 28px;
				max-width: 154px;
				overflow: hidden;
			}
			.search_wrap.active {
				width: auto;
				height: auto;
				top: 0;
				position: relative;
				border: 1px solid #ced4da;
				overflow: visible;
				/* border-radius: 4px 4px 0 0; */
			}
			.search_wrap > .btn {
				margin: 0;
			}
			.search_wrap.active > * > .btn:last-child {
				position: relative;
				right: 8px;
			}
			.search_wrap input {
				width: 103px;
				height: 19px;
				padding: 0;
				margin-left: 2px;
			}
			.search_wrap.active .btn:hover {
				border: 1px solid transparent;
				box-shadow: none;
				opacity: 0.48;
			}
			.search-input-field {
				background: none;
				border: none;
				position: relative;
				top: 1px;
				font-size: 14px;
			}
			.search-input-field:focus {
				outline: none;
			}
			
			.search_wrap.active .dropdown {
				display: none;
			}
			.search_wrap.active .dropdown {
				display: block;
				position: absolute;
				top: 24px;
				background: #fff;
				z-index: 100;
				border: 1px solid #dadada;
				border-radius: 5px;
				/* border-radius: 0 0 4px 4px; */
				/* border-top-color: #eee; */
				left: -0.5px;
				right: -0.5px;
				
				width: 260px;
				top: 30px;
				font-size: 14px;
			}
			.search_wrap.active .dropdown .options {
				font-size: 13px;
				padding: 3px 6px 4px;
			}
			.dropdown .options span {
				display: inline-block;
				padding: 1px 3px 2px;
				margin-right: 10px;
			}
			.dropdown .options .form-check-input {
				width: 12px;
				height: 12px;
				border-radius: 2px;
			}
			.dropdown .options .form-check-input:focus {
				border: 1px solid rgba(13,80,73,.45);
				box-shadow: 0 0 1px 1px rgba(13,110,93,.25);
			}
			.dropdown .options input[type="checkbox"] + label {
				top: 2px;
				left: 2px;
				line-height: 12px;
				user-select: none;
			}
			.search_wrap.active .dropdown .list {
				display: none;
				max-height: 136px;
				height: auto;
			}
			.search_wrap.active .dropdown .line {
				font-size: 13px;
				color: #363636;
			}
			.search_wrap.active .dropdown .line:hover,
			.search_wrap.active .dropdown .line.selected {
				background: #d3e8e6;
			}
			.search_wrap.active .dropdown .line > :nth-child(1) {
				display: none;
			}
			.search_wrap.active .dropdown .line > :nth-child(2) {
				margin-left: 6px;
			}
			.search_wrap.active .dropdown .line > :nth-child(2) {
				 min-width: 12px;
			}
			.search_wrap.active .dropdown .line > :nth-child(3) {
				margin-right: 4px;
			}
			.search_wrap.active .dropdown .line > :nth-child(4) {
				width: auto;
			}
			.search_wrap.active .dropdown .line > :nth-child(4) ~ * {
				visibility: hidden;
			}
			.search_wrap.active .dropdown .line:not(.user) > :last-child {
				width: 60px;
			}
			.search_wrap.active .dropdown .line.user > :last-child {
				text-align: left;
				margin-left: 8px;
				width: 230px;
			}
			
			.form-control.category-filter {
				margin-right: 10px;
				font-size: 14px;
				min-width: 200px;
				max-width: 250px;
			}
			.pagination .first, .pagination .last {
				width: 28px;
				background-position: center;
			}
			.pagination select {
				min-width: 28px;
				max-width: 45px;
				text-align: center;
				padding-right: 22px;
				font-size: 14px;
			}
			.pagination .first {
				background-image: url('/tpf/icons/first-page.svg');
				margin-right: 2px;
			}
			.pagination .last {
				background-image: url('/tpf/icons/last-page.svg');
				margin-left: 2px;
			}
			
			#page-content > .main .form {
				max-width: 86%;
				margin: 26px auto;
			}
			#page-content > .aside .items-list {
				height: calc(100% - 36px);
			}
			.list .line {
				display: flex;
				gap: 6px;
				padding: 2px 4px;
				border-radius: 3px;
				justify-content: space-between;
				align-items: center;
				cursor: default;
			}
			.list .line > *::selection {
				background-color: #56924b;
			}
			.list .line.selected {
				color: #fafafa;
				background: #405a57;
			}
			.list .line:not(.selected):hover {
				background: #d3e8e6;
			}
			.list .line > * {
				overflow: hidden;
				text-overflow: ellipsis;
				white-space: nowrap;
			}
			.list .line > :nth-child(1) {
				width: 24px;
				position: relative;
				height: 24px;
			}
			.list .line > :nth-child(2) {
				min-width: 24px;
			}
			.list .line > :nth-child(3) {
				width: 28px;
				margin-right: 12px;
			}
			.list .line > :nth-child(4) {
				width: calc(100% - 234px);
			}
			.list .line > :last-child {
				width: 170px;
				text-align: right;
			}
			.list .line input[type="checkbox"] {
				display: none;
			}
			.list .line input[type="checkbox"] + label {
				display: block;
				content: '';
				width: 20px;
				height: 20px;
				background: url('/tpf/icons/checkbox-blank.svg') center center / 100% no-repeat;
				user-select: none;
			}
			.list .line input[type="checkbox"]:checked + label {
				background: url('/tpf/icons/checkbox.svg') center center / 100% no-repeat;
			}
			.list .line.selected input[type="checkbox"] + label {
				filter: brightness(1.6) saturate(0.95);
			}
			.thumb {
				width: 24px;
				height: 24px;
				border-radius: 2px;
				margin: 3px;
			}
			.line .thumb.noimage, .no-edit .thumb.noimage {
				background-size: 45px !important;
			}
			.form-group {
				margin: 8px auto;
			}
			input[type="submit"], input[type="submit"]:focus {
				color: #f6f6f6;
				padding: 7px 14px;
				max-width: 100px;
			}
			#page-content > .main .form input[type="submit"] {
				margin: 12px 10px 6px 0;
			}
			.form-control {
				font-size: 15px;
				padding: 3px 5px;
			}
			input[type="number"] {
				max-width: 80px;
			}
			select {
				-webkit-appearance: none;
				-moz-appearance: none;
				appearance: none;
				background-image: url("data:image/svg+xml;charset=UTF-8,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2212%22%20height%3D%2212%22%20viewBox%3D%220%200%2012%2012%22%3E%3Ctitle%3Edown-arrow%3C%2Ftitle%3E%3Cg%20fill%3D%22%23000000%22%3E%3Cpath%20d%3D%22M10.293%2C3.293%2C6%2C7.586%2C1.707%2C3.293A1%2C1%2C0%2C0%2C0%2C.293%2C4.707l5%2C5a1%2C1%2C0%2C0%2C0%2C1.414%2C0l5-5a1%2C1%2C0%2C1%2C0-1.414-1.414Z%22%20fill%3D%22%23000000%22%3E%3C%2Fpath%3E%3C%2Fg%3E%3C%2Fsvg%3E");
				background-size: .6em;
				background-position: calc(100% - 0.5rem) center;
				background-repeat: no-repeat;
				text-overflow: ellipsis;
				padding-right: 25px !important;
			}
			input[type="checkbox"] {
				width: 16px;
				height: 16px;
				display: inline-block;
				vertical-align: middle;
				margin: 8px 8px 6px 0;
			}
			input[type="checkbox"] + label {
				position: relative;
				top: 3px;
			}
			input[readonly] {
				background: #efefef;
				color: #666;
			}
			
			.form-control.no-edit {
				display: flex;
				gap: 6px;
				align-items: center;
			}
			.form-control.no-edit > * {
				flex: 0 0 auto;
			}
			.actions {
				position: relative;
				top: -41px;
			}
			.actions > button {
				margin-right: 6px;
			}
			
			.photopicker {
				border: 1px solid #dadada;
				border-radius: 4px;
				padding: 6px;
				box-sizing: border-box;
				white-space: nowrap;
			}
			.photopicker.single {
				padding: 0;
				width: 60px;
				height: 60px;
				border: none;
			}
			.photopicker .thumb {
				display: inline-block;
				position: relative;
				margin: 6px;
				width: 40px;
				height: 40px;
				border-radius: 6px;
				overflow: hidden;
			}
			.photopicker .thumb .remove_btn {
				position: absolute;
				top: 4px;
				right: 4px;
				width: 16px;
				height: 16px;
				background: url('/tpf/icons/close.png') 50% 53% / 13px no-repeat rgba(90, 90, 90, 0.35);
				border-radius: 3px;
				cursor: pointer;
				z-index: 10;
			}
			.photopicker .thumb.selector {
				background: #dadada;
				cursor: pointer;
			}
			.photopicker .thumb.selector::before {
				display: block;
				content: '';
				position: absolute;
				top: 25%;
				left: 25%;
				width: 50%;
				aspect-ratio: 1;
				border-radius: 50%;
				cursor: pointer;
				background: url('/tpf/icons/add.png') center center / 26px 26px no-repeat #fff;
				z-index: 0;
			}
			.photopicker.single .thumb {
				margin: 0;
				width: 100%;
				height: 100%;
			}
			.photopicker.single .thumb.selector::before {
				top: 0;
				left: 0;
				width: 100%;
				border-radius: 6px;
				background: url('/tpf/icons/images/no-image.png') center center / 30px 30px no-repeat;
			}
			.photopicker .thumb input {
				width: 100%;
				height: 100%;
				opacity: 0;
				cursor: pointer;
				z-index: 1;
			}
			
			.tags_list > .tag {
				background: #e3e3e3;
				border: 1px solid #dadada;
				margin-right: 4px;
			}
			.tags_list > div {
				padding: 2px 5px;
				border-radius: 4px;
				display: inline;
				vertical-align: middle;
			}
			.tags_list > div[contenteditable] {
				padding: 2px;
			}
			.tags_list > div:focus {
				outline: none;
			}
			
			#modals {
				position: fixed;
				z-index: 100;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background: rgba(32, 32, 32, 0.18);
				display: flex;
				justify-content: center;
				align-items: center;
				visibility: hidden;
				opacity: 0;
			}
			.dialog {
				border: 1px solid #dadada;
				background: #fff;
				border-radius: 10px;
				padding: 26px 30px 20px;
				box-shadow: 2px 2px 3px 3px rgba(0, 0, 0, 0.05);
			}
			.dialog .buttons {
				text-align: center;
				margin-top: 12px;
				padding: 4px;
			}
			.dialog .buttons > * {
				margin: 0 4px;
				width: 68px;
				overflow: hidden;
				text-overflow: ellipsis;
			}
			#changeCategoryDialog .buttons > * {
				width: 92px;
			}
			
			.xscroll_thumb_horz, .xscroll_thumb_vert {
				background: #cdcdcd;
				border-radius: 4px;
			}
			.xscroll_thumb_horz:hover, .xscroll_thumb_vert:hover {
				background: #ccc;
			}
			.xscroll_btn_left:hover, .xscroll_btn_right:hover,
			.xscroll_btn_up:hover, .xscroll_btn_down:hover {
				opacity: 0.75;
			}
		</style>
		<script src="/tpf/js/search.js"></script>
		<script src="/tpf/js/widgets.js"></script>
		<script src="/tpf/js/xscroll.js"></script>
		<script>
			window.addEventListener('DOMContentLoaded', async function() {
				let links = document.querySelectorAll('#header a')
				links.forEach(function(link) {
					link.onclick = function(event) {
						event.preventDefault();
						localStorage.currentPage = link.getAttribute('href');
						updatePage();
					}
				});
				let sublinks = document.querySelectorAll('#subheader-content > .link')
				sublinks.forEach(function(link) {
					link.onclick = async function(event) {
						event.preventDefault();
						window.contentType = localStorage.contentType = link.getAttribute('data-value') || 'blog_post';
						await buildForm(window.contentType);
						await loadCategories(window.contentType);
						await loadContent(window.contentType);
					}
				});
				
				if (localStorage.currentPage) {
					updatePage();
				}
				
				window.currentItemId = 0;
				
				let container = document.querySelector('#page-content .items-list');
				container.addEventListener('click', function(event) {
					let target = event.target;
					while (target.classList && !target.classList.contains('line') && target.parentNode) {
						target = target.parentNode;
					}
					if (event.target == target.firstElementChild) {
						setTimeout(() => { event.target.children[0].click() }, 0);
						return;
					}
					if (formReady && !event.target.tagName.match(/^input|label$/i)) {
						if (target.dataset.id != currentItemId) {
							loadItemData(window.contentType, target.dataset.id);
							Array.from(target.parentNode.children).forEach(function(line) {
								line.classList.remove('selected');
								line.children[0].children[0].checked = false;
							});
						}
						target.classList.add('selected');
						target.children[0].children[0].checked = true;
						window.currentItemId = target.dataset.id;
					}
				});

                document.querySelectorAll('[data-action="settings"]').forEach(el => {
                    el.addEventListener('click', openSettings);
                });

                document.querySelectorAll('[data-action="logout"]').forEach(el => {
                    el.addEventListener('click', function() {
                        window.location.href = logout_url + '?hash=' + session_hash
                    })
                });

                function openSettings() {

                }
				
				document.querySelectorAll('[data-action^="check-"]').forEach(el => {
					el.addEventListener('click', function(event) {
						let checkboxes = document.querySelectorAll('.list .line input[type="checkbox"]')
						let on = this.dataset.action == 'check-all'
						checkboxes.forEach(checkbox => checkbox.checked = (on || checkbox.closest('.line.selected')))
					});
				});
				
				function debounce(func, timeout) {
					var last = 0
					var busy = false
					return function() {
						if (Date.now() - last > timeout) {
							if (busy && !timer) {
								timer = setTimeout(arguments.callee, timeout, ...arguments)
								return
							}
							busy = true
							try {
								if (timer) {
									clearTimeout(timer)
								}
								timer = null
								func.apply(this, arguments)
							} catch (e) {
								console.log(e)
							} finally {
								last = Date.now()
								busy = false
								if (timer) {
									clearTimeout(timer)
								}
								timer = null
							}
						} else {
							timer = setTimeout(arguments.callee, Math.max(0, timeout - (Date.now() - last)), ...arguments)
							return
						}
					}
					var timer = null
				}
				
				initSearch()
				
				document.querySelectorAll('.category-filter').forEach(el => {
					el.addEventListener('change', function(event) {
						localStorage.category = preserveNavigation ? this.value : '';
						reloadContent();
					});
				});
				
				document.querySelectorAll('.pagination .first').forEach(el => {
					el.addEventListener('click', function(event) {
						if (!this.classList.contains('disabled')) changePage(1);
					});
				});
				document.querySelectorAll('.pagination .last').forEach(el => {
					el.addEventListener('click', function(event) {
						if (!this.classList.contains('disabled')) changePage(window.pagesTotal ?? 1);
					});
				});
				document.querySelectorAll('.pagination select').forEach(el => {
					el.innerHTML = '';
					el.addEventListener('change', function(event) {
						changePage(this.value);
					});
				});
				
				document.querySelectorAll('#modals .btn-no').forEach(btn => {
					btn.onclick = hideModals
				});
				
				document.getElementById('subheader-content').addEventListener('click', function(event) {
					if (!event.target.classList.contains('link')) return
					Array.from(event.target.parentNode.children).forEach(link => {
						link.classList.toggle('active', link == event.target)
					})
					if (event.target.parentNode.classList.contains('realms') && cache.types) {
						updateItemTypes(cache.types, true)
					}
					contentType = document.querySelector('#subheader-content :nth-child(2) > .active').dataset.value
					localStorage.contentType = contentType
					reloadContent()
				});
				
				container.addEventListener('click', function(event) {
					if (event.shiftKey) {
						let target = event.target
						if (target.htmlFor) target = document.getElementById(target.htmlFor)
						
						if (target.getAttribute('type') != 'checkbox') return
						
						let container = event.target.parentNode
						while (!container.classList.contains('line') && container.parentNode) {
							container = container.parentNode
						}
						let currentLine = container
						container = container.parentNode
						let lastChecked = Array.from(container.querySelectorAll('.line input[type="checkbox"]:checked')).pop()
						let lastCheckedLine = container.querySelector('.line')
						if (lastChecked) {
							lastCheckedLine = lastChecked.parentNode
							while (!lastCheckedLine.classList.contains('line') && container.parentNode) {
								lastCheckedLine = lastCheckedLine.parentNode
							}
						}
						let index1 = lastCheckedLine.parentNode.childIndex(lastCheckedLine)
						let index2 = currentLine.parentNode.childIndex(currentLine)
						if (index1 == index2) return
						
						let from = Math.min(index1, index2)
						let to   = Math.max(index1, index2)
						
						if (from == index2) {
							from--
							to++
							event.preventDefault()
						}
						
						container.style.userSelect = 'none'
						setTimeout(() => {
							document.getSelection().removeAllRanges()
							container.style.userSelect = ''
						}, 800)
						
						let on = target.checked
						for (let i = 1; i < to - from; i++) {
							let line = lastCheckedLine.parentNode.children[from + i]
							let cbx = line.querySelector('input[type="checkbox"]')
							if (cbx) cbx.checked = !on
						}
					}
				});

                document.querySelectorAll('.userpic').forEach(el => {
                    el.innerHTML = generateThumbHtml(user);
                });
				
				Node.prototype.childIndex = function(el) {
					return Array.prototype.indexOf.call(this.children, el);
				}

				let types = await loadTypes();
				let realms = Object.keys(types);
				let realm = realms[0];
				
				if (!localStorage.contentType) localStorage.contentType = realm ? types[realm][0] : 'blog_post'
				
				window.contentType = localStorage.contentType;
				
				window.page = localStorage.page !== 'undefined' ? Math.max(1, parseInt(localStorage.page)) : 1;
				
				updateItemTypes(types);
				
				await buildForm(window.contentType);
				await loadCategories(window.contentType);
				
				if (localStorage.category !== undefined) {
					let catlist = document.querySelector('.category-filter');
					if (catlist) catlist.value = localStorage.category;
				}
				
				await reloadContent();
				
				if ('XScroll' in window) setTimeout(function() {
					XScroll.initAll();
				}, 50)
			});
			
			async function reloadContent(autoSelect = true, resetPage = false) {
				return loadContent(window.contentType, pageSize * (window.page - 1), pageSize, autoSelect);
			}
			
			let formReady = false
			
			let pageSize = 25;
			let imagePath = '/media/images/';
			
			let preserveNavigation = true;
			
			function changePage(value) {
				window.page = value;
				localStorage.page = preserveNavigation ? window.page : 1;
				reloadContent();
			}
			
			function updatePage() {
				let pages = document.querySelectorAll('#pages > div')
				pages.forEach(function(page) {
					page.style.display = (page.id == 'page-' + localStorage.currentPage.replace(/^#/, '')) ? '' : 'none'
				});
			}
			
			function updateItemTypes(types, typesOnly) {
				let container = document.querySelector('#subheader-content')
				if (!typesOnly) container.children[0].innerHTML = ''
				container.children[1].innerHTML = ''
				
				if (!typesOnly) {
					for (let realm in types) {
						let link = document.createElement('span')
						link.className = 'link'
						if (!container.children[0].children.length) {
							link.classList.add('active')
						}
						link.dataset.value = realm
						link.textContent = realm[0].toUpperCase() + realm.slice(1)
						container.children[0].appendChild(link)
					}
					
					let type = window.contentType.split('_')
					document.querySelectorAll('#subheader-content :first-child > .link').forEach(link => {
						link.classList.toggle('active', link.dataset.value == type[0])
					})
				}
				
				if (container.children[0].children.length) {
					let realm = container.querySelector(':first-child > .active').dataset.value
					for (let i = 0; i < types[realm].length; i++) {
						let type = types[realm][i]
						let link = document.createElement('span')
						link.className = 'link'
						if (!container.children[1].children.length) {
							link.classList.add('active')
						}
						link.dataset.value = type
						let name = type.split('_')[1]
						link.textContent = name[0].toUpperCase() + name.slice(1)
						container.children[1].appendChild(link)
					}
				}
				setTimeout(updateLinkStates, 50)
			}
			
			function updateLinkStates() {
				let type = window.contentType.split('_')
				if (!type.length) return
				
				document.querySelectorAll('#subheader-content :first-child > .link').forEach(link => {
					link.classList.toggle('active', link.dataset.value == type[0])
				})
				
				if (type.length > 1) {
					document.querySelectorAll('#subheader-content :nth-child(2) > .link').forEach(link => {
						link.classList.toggle('active', link.dataset.value == window.contentType)
					})
				}
			}
			
			var TaskManager = {
				addedPhotos: [],
				removedPhotos: [],
				formChanged: false
			}
			
			
			var cache = sessionStorage.cache ? JSON.parse(sessionStorage.cache) : { schemas: {}, categories: {} }
			
			if (!sessionStorage.cache) sessionStorage.cache = `{
				"schemas": {},
				"categories": {}
			}`;
			
			function updatePagination(elsTotal) {
				window.pagesTotal = Math.max(1, Math.ceil(elsTotal / pageSize))
				document.querySelectorAll('.pagination select').forEach(el => {
					el.innerHTML = ''
					for (let i = 1; i <= window.pagesTotal; i++) {
						let option = new Option(i, i)
						el.appendChild(option)
					}
					el.value = window.page || 1
					if (window.page <= 0) window.page = el.value
					if (el.previousElementSibling) el.previousElementSibling.classList.toggle('disabled', el.value == 1)
					if (el.nextElementSibling) el.nextElementSibling.classList.toggle('disabled', el.value == window.pagesTotal)
				});
			}
			
			function createItemLine(item) {
				let title = item.title || item.name;
				let createdAt = item.createdAt ? formatDate(new Date(item.createdAt), 'DD.MM.YYYY HH:mm:ss') : '-'
				let modifiedAt = item.modifiedAt ? formatDate(new Date(item.modifiedAt), 'DD.MM.YYYY HH:mm:ss') : '-'
				
				let line = document.createElement('div');
				line.classList.add('line');
				line.dataset.id = item.id;
				line.innerHTML = '<div><input type="checkbox" class="checkbox" id="ch' + item.id + '"><label for="ch' + item.id + '"></label></div>';
				line.innerHTML += '<div>' + item.id + '</div>';
				line.innerHTML += '<div>' +  generateThumbHtml(item) + '</div>';
				line.innerHTML += '<div>' + title + '</div><div>' + createdAt + '</div>';
				return line;
			}
			
			async function loadContent(type = 'blog_post', offset = 0, count = pageSize, autoSelect = true) {
				let catlist = document.querySelector('.category-filter')
				let query = (catlist && catlist.value ? '&category=' + catlist.value : '') + '&excludeSubCats'
				if (document.querySelector('.search-input-field').value.match(/^[^#][\w\d\s]+$/)) {
					query = '&search=' + encodeURIComponent(document.querySelector('.search-input-field').value)
					if (document.getElementById('search_in_text').checked) query += '&searchInText=1'
					if (document.getElementById('exact_match').checked) query += '&match=exact'
				}
				return fetch('/getItems?type=' + type + query + '&offset=' + offset + '&count=' + count)
					.then(res => res.json())
					.then(res => {
						let container = document.querySelector('#page-content .items-list');
						container.innerHTML = '';
						updatePagination(res.total)
						for (let item of res.data) {
							let line = createItemLine(item);
							container.appendChild(line);
						}
						if (query.indexOf('&search=') != -1) {
							window.isSearch = true;
						}
						let data = 'data' in res ? res.data : res
						if (autoSelect && data.length > 0) {
							container.children[0].click();
						} else if (catlist.value != 'trash') {
							newItem();
						}
						toggleTrashView();
					});
			}
			
			function toggleTrashView() {
				let catlist = document.querySelector('.category-filter');
				
				let toolbarItemsToHide = [2, 3];
				let toolbarItemsToShow = [5, 6];
				
				document.querySelector('.main .form').style.visibility = catlist.value != 'trash' ? '' : 'hidden';
				let toolbar = document.querySelector('.page .aside .toolbar');
				toolbarItemsToHide.forEach(index => {
					toolbar.children[index].classList.toggle('hidden', catlist.value == 'trash');
				})
				toolbarItemsToShow.forEach(index => {
					toolbar.children[index].classList.toggle('hidden', catlist.value != 'trash');
				})
			}
			
			function generateThumbHtml(item) {
				let image = item.image || item.image_url || item.cover || item.cover_url || item.photo || item.photo_url;
				image = image ? imagePath + image : '/tpf/icons/images/no-photo.jpg';	
				return '<div class="thumb' + (image == '/tpf/icons/images/no-photo.jpg' ? ' noimage' : '') + '" style="background: url(\'' + image + '\') center center / cover no-repeat"></div>';
			}
			
			async function loadTypes() {
				if (cache.types) {
					return cache.types;
				} else {
					return await fetch('/getAllEntityTypes')
					.then(res => res.json())
					.then(res => {
						let container = document.querySelector('#page-content .main .form');
						container.innerHTML = '';
						//console.log(res);
						cache.types = res;
						sessionStorage.cache = JSON.stringify(cache);
						return res;
					});
				}
			}
			
			async function getSchema(type) {
				if (cache.schemas[type]) {
					return cache.schemas[type];
				} else {
					return await fetch('/getSchema?type=' + type)
					.then(res => res.json())
					.then(res => {
						let container = document.querySelector('#page-content .main .form');
						container.innerHTML = '';
						//console.log(res);
						cache.schemas[type] = res;
						sessionStorage.cache = JSON.stringify(cache);
						return res;
					});
				}
			}
			
			async function buildForm(type) {
				getSchema(type).then(res => {
					let container = document.querySelector('#page-content .main .form');
					container.innerHTML = '';
					console.log(res);
					cache.schemas[type] = res;
					sessionStorage.cache = JSON.stringify(cache);
					for (let field in res) {
						if (field == 'isDeleted') continue;
						let type = res[field];
						if (type == 'int') {
							type = 'number';
						}
						let input = document.createElement(field == 'categories' ? 'select' : (type == 'text' ? 'textarea' : 'input'))
						if (type == 'number') {
							input.type = type;
						}
						if (type == 'array') {
							input.dataset.role = 'array';
						}
						if (type == 'image' || type == 'imagelist') {
							input.dataset.role = 'photopicker';
							if (type == 'imagelist') {
								input.dataset.isMultiple = true;
							}
						}
						if (field == 'id') {
							input.readOnly = true;
						}
						if (type == 'bool') {
							input.type = 'checkbox';
							input.value = '1';
						}
						input.name = field;
						input.id = field;
						input.className = input.type != 'checkbox' ? 'form-control' : 'form-control form-check-input';
						let label = document.createElement('label');
						label.htmlFor = input.id;
						field = field.replace(/([a-z])([A-Z])/g, '$1 $2');
						label.textContent = field[0].toUpperCase() + field.slice(1);
						
						if (label.textContent == 'Categories') label.textContent = 'Category';
						
						input.addEventListener('change', function() {
							TaskManager.formChanged = true
						})
						
						let row = document.createElement('div');
						row.className = 'form-group';
						if (input.type != 'checkbox') {
							row.appendChild(label);
							row.appendChild(input);
						} else {
							row.appendChild(input);
							row.appendChild(label);
						}
						if (field.match(/\w+ Id$/)) {
							label.textContent = label.textContent.replace(/ Id$/, '');
							input.style.display = 'none';
							let viewbox = document.createElement('div');
							viewbox.className = 'form-control no-edit';
							input.parentNode.appendChild(viewbox);
						}
						if (field == 'id') {
							var panel = document.createElement('div')
							panel.className = 'actions float-end'
							panel.innerHTML = '<button class="btn btn-primary" data-action="duplicate">Duplicate</button><button class="btn btn-primary" data-action="delete">Delete</button>'
							input.parentNode.appendChild(panel)
						}
						
						container.appendChild(row);
					}
					
					let row = document.createElement('div');
					row.className = 'form-group';
					let submitButton = document.createElement('input');
					submitButton.type = 'submit';
					submitButton.value = 'Save';
					submitButton.className = 'btn btn-primary';
					submitButton.addEventListener('click', updateItemData);
					row.appendChild(submitButton);
					container.appendChild(row);
					
					initPhotoPickers();
					initTagsWidgets();
					
					document.querySelectorAll('[data-action="new"]').forEach(function (el) {
						el.addEventListener('click', function(event) {
							newItem();
						});
					});
					document.querySelectorAll('[data-action="duplicate"]').forEach(function (el) {
						el.addEventListener('click', function(event) {
							duplicateItem();
						});
					});
					document.querySelectorAll('[data-action="move"]').forEach(function (el) {
						el.addEventListener('click', function(event) {
							openSetCategoryDialog();
						});
					});
					document.querySelectorAll('[data-action="delete"]').forEach(function (el) {
						el.addEventListener('click', function(event) {
							deleteItem();
						});
					});
					document.querySelectorAll('[data-action="batch-delete"]').forEach(function (el) {
						el.addEventListener('click', function(event) {
							batchDelete();
						});
					});
					document.querySelectorAll('[data-action="batch-restore"]').forEach(function (el) {
                  el.addEventListener('click', function(event) {
                     batchRestore();
                  });
               });
					
					formReady = true;
				});
			}
			
			async function loadCategories(type) {
				if (cache.categories[type]) {
					setCategories(cache.categories[type]);
					return;
				}
				return fetch('/getCategories?type=' + type)
					.then(res => res.json())
					.then(res => {
						res = res.sort((a, b) => {
							return a.parent != b.parent ? a.parent - b.parent : a.id - b.id;
						});
						console.log(res);
						cache.categories[type] = res;
						sessionStorage.cache = JSON.stringify(cache);
						
						setCategories(res);
					});
			}
			
			function setCategories(categories) {
				let els = document.querySelectorAll('.category-filter, [name="categories"]')
				els.forEach(function(list) {
					list.innerHTML = '';
					let option = new Option('None', '[]')
					list.appendChild(option);
					if (list.classList.contains('category-filter')) {
						list.children[0].textContent = 'Uncategorized';
						list.children[0].value = '0';
						option = new Option('All', '');
						list.insertBefore(option, list.children[0]);
						option = new Option('Trash', 'trash');
						list.appendChild(option);
						list.value = '';
					}
					for (let category of categories) {
						let fullCategoryName = category.path.join(' > ');
						let value = list.classList.contains('category-filter') ? category.id : JSON.stringify(category.id_path)
						let option = new Option(fullCategoryName, value)
						list.appendChild(option);
					}
				});
			}
			
			function loadItemData(type, id, children = 1) {
				if (TaskManager.formChanged || TaskManager.addedPhotos.length || TaskManager.removedPhotos.length) {
					if (!window.currentItemId || confirm('Are you sure you want to leave this form? Your changes will be lost!')) {
						removePhotosBatch(TaskManager.addedPhotos)
					} else {
						return
					}
				}
				fetch('/getItem?type=' + type + '&id=' + id + '&children=' + children)
					.then(res => res.json())
					.then(res => {
						let inputs = document.querySelectorAll('#page-content .main .form .form-control');
						for (let input of inputs) {
							if (['id', 'authorId', 'createdAt', 'modifiedAt'].indexOf(input.name) != -1) {
								input.disabled = false
								input.parentNode.style.display = ''
							}
							if (res[input.name] instanceof Array) {
								input.value = JSON.stringify(res[input.name])
								if (input.name == 'tags' && input.tagsWidget) {
									let widget = input.tagsWidget
									while (widget.children.length > 1) {
										widget.removeChild(widget.children[0])
									}
									for (let tagItem of res[input.name]) {
										let tag = document.createElement('div')
										tag.className = 'tag'
										tag.textContent = tagItem
										widget.insertBefore(tag, widget.lastElementChild)
										widget.lastElementChild.textContent = ''
									}
								}
							} else if (input.type != 'checkbox' && input.name in res) {
								input.value = res[input.name]
							} else if (input.type == 'checkbox' && input.name in res) {
								input.checked = !!res[input.name]
							}
							if (input.dataset.role == 'photopicker' && input.photopicker) {
								try {
									if (input.dataset.isMultiple) {
										input.photopicker.picker.setPhotos(JSON.parse(input.value))
									} else {
										input.photopicker.picker.setPhotos(input.value ? [input.value] : [])
									}
								} catch(e) {}
							}
							if (input.name && input.name.match(/^authorId|createdAt|modifiedAt$/)) {
								input.disabled = true
								let match = null
								if (input.name.match(/^createdAt|modifiedAt$/)) {
									input.value = input.value.replace('T', ' ')
								}
								else if (match = input.name.match(/([a-zA-Z0-9_]+)Id$/)) {
									let viewbox = input.nextElementSibling
									if (res[match[1]] && viewbox) {
										input.style.display = 'none'
										viewbox.innerHTML = '<div>' + res[match[1]].id + '</div><div>' + generateThumbHtml(res[match[1]]) + '</div><div>' + (!match[1].match(/^user|author$/) ? res[match[1]].name : res[match[1]].username) + '</div>'
										if (match[1].match(/^user|author$/)) {
											let fullName = [res[match[1]].firstname, res[match[1]].lastname].join(' ').trim()
											let cell = document.createElement('div')
											cell.textContent = fullName
											viewbox.insertBefore(cell, viewbox.lastElementChild)
											viewbox.lastElementChild.textContent = '( @' + viewbox.lastElementChild.textContent + ' )'
										}
									} else if (viewbox) {
										input.style.display = ''
										viewbox.style.display = 'none'
									}
								}
							}
						}
						document.querySelector('.main').scrollTop = 0
						window.currentItemId = id
						TaskManager.addedPhotos = []
						TaskManager.removedPhotos = []
						TaskManager.formChanged = false
					});
			}
			
			function updateItemData() {
				let inputs = document.querySelectorAll('#page-content .main .form .form-control');
				let data = {}
				for (let input of inputs) {
					if (!input.name || input.disabled) continue
					let value = input.value
					if (input.type == 'checkbox') {
						input.value = input.checked ? 1 : 0
					}
					if (input.dataset.role == 'array') {
						value = value.length ? JSON.parse(input.value) : []
					}
					data[input.name] = value
				}
				fetch('/saveItem?type=' + contentType + (currentItemId ? '&id=' + currentItemId : ''), {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json;charset=utf-8'
					},
					body: JSON.stringify(data)
				})
					.then(res => res.json())
					.then(res => {
						console.log(res)
						if (!currentItemId) {
							reloadContent()
							return
						}
						removePhotosBatch(TaskManager.removedPhotos)
						TaskManager.addedPhotos = []
						TaskManager.removedPhotos = []
						TaskManager.formChanged = false
					})
			}
			
			function disableField(field) {
				field.value = ''
				field.disabled = true
				field.parentNode.style.display = 'none'
			}
			
			function removeLinesSelection() {
				document.querySelectorAll('.aside .list .line').forEach(function(line) {
					line.classList.remove('selected');
				});
			}
			
			function newItem() {
				if (TaskManager.formChanged || TaskManager.addedPhotos.length || TaskManager.removedPhotos.length) {
					if (!window.currentItemId || confirm('Are you sure you want to leave this form? Your changes will be lost!')) {
						removePhotosBatch(TaskManager.addedPhotos)
					} else {
						return
					}
				}
				duplicateItem()
				document.querySelectorAll('.main .form .form-control').forEach(el => {
					if (el.dataset.role == 'photopicker') return
					el.value = el.dataset.role == 'array' ? '[]' : ''
					if (el.tagsWidget) {
						while (el.tagsWidget.children.length > 1) {
							el.tagsWidget.removeChild(el.tagsWidget.children[0])
						}
					}
				})
			}
			
			function duplicateItem() {
				document.querySelectorAll('[name="id"], [name="authorId"], [name="createdAt"], [name="modifiedAt"]').forEach(disableField)

				/* Clear photos */
				var photos_fields = document.querySelectorAll('[data-role="photopicker"]')
				photos_fields.forEach(function(input) {
					if (input.photopicker) {
						try {
							input.photopicker.picker.setPhotos([])
						} catch(e) {}
					}
				})
				removeLinesSelection()
				window.currentItemId = null
			}
			
			function actionConfirm(message, handler) {
				document.querySelectorAll('#modals .dialog').forEach(el => el.style.display = 'none')
				document.getElementById('confirmDialog').style.display = 'block'
				let text = document.querySelector('#confirmDialog .text')
				text.innerHTML = message
				document.querySelector('#confirmDialog .btn-yes').onclick = handler
				document.getElementById('modals').style.visibility = 'visible'
				setTimeout(() => document.getElementById('modals').style.opacity = '1', 0)
			}
			
			function openSetCategoryDialog() {
				document.querySelectorAll('#modals .dialog').forEach(el => el.style.display = 'none')
				document.getElementById('changeCategoryDialog').style.display = 'block'
				document.querySelector('#changeCategoryDialog .btn-yes').onclick = function() {
					let ids = getSelectedIds()
					let category = document.querySelector('#changeCategoryDialog select').value
					if (!category.match(/^\[((\d+)(\s*,\s*\d+)*)?\]$/)) return
					fetch('/setItemCategory?type=' + window.contentType + '&ids=[' + ids.join(',') + ']&category=' + category)
					.then(res => res.json())
					.then(res => {
						let line = document.querySelector('.aside .list .line.selected')
						let lineId = line ? line.dataset.id : null
						reloadContent(false)
							.then(() => {
								if (lineId) {
									line = document.querySelector('.aside .list .line[data-id="' + lineId + '"]')
									if (line) line.click()
								}
							})
					})
					hideModals()
				}
				document.getElementById('modals').style.visibility = 'visible'
				setTimeout(() => document.getElementById('modals').style.opacity = '1', 0)
			}
			
			function hideModals() {
				document.getElementById('modals').style.opacity = '0'
				setTimeout(() => {
					document.getElementById('modals').style.visibility = ''
					document.querySelectorAll('#modals .dialog').forEach(el => el.style.display = 'none')
				}, 250)
			}
			
			function getPlural(number, word) {
				return number + ' ' + word + (number != 1 ? 's' : '')
			}
			
			function getSelectedIds() {
				return unique(Array.prototype.map.call(document.querySelectorAll('.aside .list .line input[type="checkbox"]:checked'), function(checkbox) {
					let line = checkbox.parentNode
					while (line.parentNode && !line.classList.contains('line')) {
						line = line.parentNode
					}
					return line.dataset.id
				}));
				
				function unique(a) {
					var seen = {};
					return a.filter(function(item) {
					   return seen.hasOwnProperty(item) ? false : (seen[item] = true);
					});
				}
			}
			
			function batchDelete() {
				let ids = getSelectedIds();
				let message = 'Are you sure you want to delete ' + getPlural(ids.length, 'item') + '?'
				actionConfirm(message, function() {
					let category = document.querySelector('.category-filter')?.value
					fetch('/deleteItem?' + (category != 'trash' ? 'soft&' : '') + 'type=' + window.contentType + '&ids=[' + ids.join(',') + ']')
						.then(res => res.json())
						.then(res => {
							let line = document.querySelector('.aside .list .line.selected')
							let nextLineId = line.nextElementSibling ? line.nextElementSibling.dataset.id : null
							reloadContent(false)
								.then(() => {
									if (nextLineId) {
										line = document.querySelector('.aside .list .line[data-id="' + nextLineId + '"]')
										if (line) line.click()
									} else {
										line = document.querySelector('.aside .list .line')
										if (line) line.click()
									}
								})
						})
					hideModals()
				})
			}
			
			function batchRestore() {
				let ids = getSelectedIds();
				let message = 'Are you sure you want to restore ' + getPlural(ids.length, 'item') + '?'
				actionConfirm(message, function() {
					fetch('/restoreItem?type=' + window.contentType + '&ids=[' + ids.join(',') + ']')
						.then(res => res.json())
						.then(res => {
							let line = document.querySelector('.aside .list .line.selected')
							let nextLineId = line.nextElementSibling ? line.nextElementSibling.dataset.id : null
							reloadContent(false)
								.then(() => {
									if (nextLineId) {
										line = document.querySelector('.aside .list .line[data-id="' + nextLineId + '"]')
										if (line) line.click()
									} else {
										line = document.querySelector('.aside .list .line')
										if (line) line.click()
									}
								})
						})
					hideModals()
				})
			}
			
			function deleteItem() {
				if (!confirm('Are you sure you want to delete this item?')) {
					return
				}
				fetch('/deleteItem?type=' + window.contentType + '&ids=[' + currentItemId + ']')
					.then(res => res.json())
					.then(res => {
						let line = document.querySelector('.aside .list .line.selected')
						let nextLineId = line.nextElementSibling ? line.nextElementSibling.dataset.id : null
						reloadContent(false)
							.then(() => {
								if (nextLineId) {
									line = document.querySelector('.aside .list .line[data-id="' + nextLineId + '"]')
									if (line) line.click()
								}
								//line.parentNode.removeChild(line)
								//if (nextLine) nextLine.click()
							})
					})
			}
			
			const file_upload_url = '/upload'
			
			function initPhotoPickers() {
				let els = document.querySelectorAll('[data-role="photopicker"]')
				PhotoPicker.init(els);
			}
			
			function removePhoto(file) {
				fetch('/removeFile', {
					method: 'POST',
					headers: {
						 'Content-Type': 'application/x-www-form-urlencoded'
					},
					body: 'file=' + encodeURIComponent(file)
				})
			}
			
			function removePhotosBatch(files) {
				if (!(files instanceof Array)) {
					throw { error: 'Wrong argument type: strings array expected' }
				}
				if (!files.length) return
				files = files.map(el => el.replace(/^\/media/, ''))
				fetch('/removeFiles', {
					method: 'POST',
					headers: {
						 'Content-Type': 'application/x-www-form-urlencoded'
					},
					body: 'files=' + encodeURIComponent(JSON.stringify(files))
				})
			}
			
			function formatDate(e, n) {
				var t = {
					DD: function (e) {
						return p(2, '' + e.getDate())
					},
					D: function (e) {
						return '' + e.getDate()
					},
					MMMM: function(e) {
						return e.toLocaleDateString(navigator.language, {month: 'long'})
					},
					MMM: function(e) {
						return e.toLocaleDateString(navigator.language, {month: 'long'}).slice(0, 3)
					},
					MM: function (e) {
						return p(2, '' + (e.getMonth() + 1))
					},
					M: function (e) {
						return '' + (e.getMonth() + 1)
					},
					YYYY: function (e) {
						return p(4, '' + e.getFullYear())
					},
					YY: function (e) {
						return ('' + e.getFullYear()).substr( - 2)
					},
					HH: function (e) {
						return p(2, '' + e.getHours())
					},
					H: function (e) {
						return '' + e.getHours()
					},
					mm: function (e) {
						return p(2, '' + e.getMinutes())
					},
					m: function (e) {
						return '' + e.getMinutes()
					},
					ss: function (e) {
						return p(2, '' + e.getSeconds())
					},
					s: function (e) {
						return '' + e.getSeconds()
					}
				}
				function p(e, t) {
					return t.length >= e ? t : p(e, '0' + t)
				}
				
				return r = null, n.replace((r = Object.keys(t).concat('\\[[^\\[\\]]*\\]'), new RegExp(r.join('|'), 'g')), (function (n) {
					return t.hasOwnProperty(n) ? t[n](e) : n.replace(/\[|\]/g, '')
				}));
			}
		</script>
		<script>
			<?php $user = $TPF_REQUEST['session']->user ?? new User(); ?>let user = { username: '<?php echo $user->username; ?>', firstname: '<?php echo $user->firstname; ?>', lastname: '<?php echo $user->lastname; ?>', image: '<?php echo $user->photo; ?>' }
            let logout_url = '<?php echo $TPF_CONFIG['logout_url'] ?? '/logout' ?>'; let session_hash = '<?php echo substr($TPF_REQUEST['session']->secureSessionId, -8); ?>';
		</script>
	</head>
	<body>
		<div id="header">
			<div class="logo">Admin panel</div>
			<nav><a class="active" href="#content">Content</a><a href="#users">Users</a></nav>
            <div class="spacer"></div>
            <div id="menu">
				<div class="header"><div class="userpic"></div><span>Administrator</span></div>
				<div class="menu">
					<li class="item" data-action="settings">Settings</li>
					<li class="item" data-action="logout">Logout</li>
				</div>
			</div>
		</div>
		<div id="subheader">
			<div id="subheader-content">
				<div class="realms">
					<span class="link active" data-value="blog">Blog</span>
					<span class="link" data-value="shop">Shop</span>
				</div>
				<div class="item_types">
					<!-- <span class="link active" data-value="blog_post">Post</span> -->
				</div>
			</div>
		</div>
		<div id="pages">
			<div class="page" id="page-content">
				<div class="aside">
					<div class="toolbar">
						<div class="btn_group">
							<button class="btn new" data-action="new"></button>
							<button class="btn move" data-action="move"></button>
							<button class="btn delete" data-action="batch-delete"></button>
						</div>
						<div class="btn_separator"></div>
						<div class="btn_group">
							<button class="btn import" data-action="import"></button>
							<button class="btn export" data-action="export"></button>
						</div>
						<div class="btn_separator"></div>
						<div class="btn_group">
							<button class="btn check-all" data-action="check-all"></button>
							<button class="btn check-none" data-action="check-none"></button>
						</div>
						<div class="btn_separator hidden"></div>
                  <div class="btn_group hidden">
                     <button class="btn restore" data-action="batch-restore"></button>
                  </div>
						<div class="btn_group spacer"></div>
						<div class="btn_group search">
							<div class="search_wrap">
								<div style="white-space: nowrap; height: 24px">
									<button class="btn search" data-action="toggle-search"></button>
									<input type="text" class="search-input-field" data-action="search">
									<button class="btn clear-text" data-action="clear-search"></button>
								</div>
								<div class="dropdown">
									<div class="options"><span><input type="checkbox" class="form-control form-check-input" id="exact_match"><label for="exact_match">Exact match</label></span><span><input type="checkbox" class="form-control form-check-input" id="search_in_text"><label for="search_in_text">Search in text</label></span></div>
									<div id="search_variants" class="list scrollable scroll_y"></div>
								</div>
							</div>
						</div>
						<div class="btn_group">
							<select name="categories" class="category-filter form-control"><option value="[]">None</option></select>
						</div>
						<div class="btn_group pagination">
							<div class="btn first" data-action="first-page"></div>
							<select class="form-control"></select>
							<div class="btn last" data-action="last-page"></div>
						</div>
					</div>
					<hr>
					<div class="items-list list scrollable scroll_y" button-size="1" scroll-delta="20" thumb-width="6" thumb-length="200">
						<!-- <div class="line">
							<div>1</div><div><div class="thumb" style="background: url('/media/images/website-3483020_640.png') center center / cover no-repeat"></div></div><div>First blog entry</div><div>28.12.2024 15:43:17</div>
						</div>
						<div class="line">
							<div>2</div><div><div class="thumb" style="background: url('/media/images/website-3374825_1920.jpg') center center / cover no-repeat"></div></div><div>Second blog entry</div><div>29.12.2024 11:17:34</div>
						</div> -->
					</div>
				</div>
				<div class="main scrollable scroll_y" button-size="1" scroll-delta="20" thumb-width="6" thumb-length="300">
					<div class="form">
						<div class="form-group">
							<label for="item-title">Title</label>
							<input type="text" class="form-control" id="item-title">
						</div>
						<div class="form-group">
							<label for="item-desc">Description</label>
							<textarea class="form-control" id="item-desc"></textarea>
						</div>
						<div class="form-group">
							<input class="btn btn-primary" type="submit" value="Save">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="modals">
			<div class="dialog" id="confirmDialog">
				<div class="text">Are you sure you want to delete this item?</div>
				<div class="buttons"><div class="btn btn-primary btn-yes">Yes</div><div class="btn btn-primary btn-no">No</div></div>
			</div>
			<div class="dialog" id="infoDialog" style="display: none">
				<div class="text">Operation complete</div>
				<div class="buttons"><div class="btn btn-primary btn-no">OK</div></div>
			</div>
			<div class="dialog" id="changeCategoryDialog">
				<div class="text">Select new category:</div>
				<div><select data-role="set-category" name="categories" class="form-control"><option value="[]">None</option></select></div>
				<div class="buttons"><div class="btn btn-primary btn-yes">OK</div><div class="btn btn-primary btn-no">Cancel</div></div>
			</div>
		</div>
	</body>
</html>