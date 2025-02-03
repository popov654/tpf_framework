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
				cursor: default;
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
			#categories-toggle-wrap {
				float: right;
				margin-right: 4px;
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
			.toolbar .hidden {
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
				min-width: 12px;
			}
			.search_wrap.active .dropdown .line > :nth-child(3) {
				margin-right: 4px;
			}
			.search_wrap.active .dropdown .line > :nth-child(4) {
				width: auto;
			}
			.search_wrap.active .dropdown .line > :nth-child(4) ~ * {
				display: none;
			}
			.search_wrap.active .dropdown .line > :nth-child(4) {
				width: 60px;
				flex-grow: 1;
			}
			
			.toolbar.users .search_wrap {
				max-width: 190px;
			}
			.toolbar.users .search_wrap input {
				width: 139px;
			}
			.toolbar.users .btn.move {
				display: none;
			}
			.toolbar.users .btn_group.categories {
				display: none;
			}
			
			.btn.btn-comments {
				width: 30px;
				height: 30px;
				background: url('/tpf/icons/comments.svg') center / 30px no-repeat;
				position: relative;
				top: -40px;
				left: -12px;
			}
			
			.form-control.category-filter {
				margin-right: 10px;
				font-size: 14px;
				min-width: 190px;
				max-width: 210px;
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
			.line:not(.user) .thumb.noimage {
				background-size: 45px !important;
			}
			.line.user .thumb {
				background-size: 95% !important;
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
			.password-form-field {
				resize: none;
				line-height: 1.8;
				padding: 4px 5px;
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
			#commentsDialog .list {
				width: 640px;
				height: 400px;
				border: 1px solid #dadada;
				border-radius: 4px;
			}
			.comment {
				background: #e9f6fd;
				border-radius: 8px;
				border: 1px solid #d3d3d3;
				padding: 24px 68px 22px 28px;
				margin: 8px;
				display: block;
				width: max-content;
				position: relative;
			}
			.comment.deleted {
				background: #f1d5d5;
				border-color: #c8b8b8;
			}
			.comment .actions, .category > .actions {
				position: absolute;
				top: 6px;
				right: 10px;
				width: 75px;
				white-space: nowrap;
				text-align: right;
			}
			.comment .user {
				display: flex;
				align-items: center;
				margin-bottom: 1rem;
			}
			.comment .thumb {
				width: 32px;
				height: 32px;
			}
			.comment .thumb, .comment .name {
				display: inline-block;
				margin: 0 10px 0 0;
			}
			.comment .actions .btn, .category .actions .btn {
				opacity: 0.75;
			}
			.comment .actions .btn:hover, .category .actions .btn:hover {
				opacity: 1;
			}
			.comment .btn.edit, .comment .btn.delete, .comment .btn.restore,
			.category .btn.edit, .category .btn.delete, .category .btn.restore {
				display: inline-block;
				width: 24px;
				height: 24px;
			}
			.comment .btn.edit, .category .btn.edit {
				background: url('/tpf/icons/edit-comment.svg') center / 24px no-repeat;
			}
			.comment .btn.delete, .category .btn.delete {
				background: url('/tpf/icons/delete-comment.svg') center / 22px no-repeat;
			}
			.comment .btn.restore, .category .btn.restore {
				background: url('/tpf/icons/restore-comment.svg') center / 22px no-repeat;
				display: none;
			}
			.comment.deleted .btn.restore, .category.deleted .btn.restore {
				display: inline-block;
			}
			.category .header > .actions {
				visibility: hidden;
			}
			.category .header:hover > .actions {
				visibility: visible;
			}
			.comment textarea {
				display: block;
			}
			.comment .link {
				font-size: 12px;
				position: relative;
				top: 8px;
				left: calc(100% - 48px);
				width: 45px;
				text-align: right;
				display: inline-block;
				color: #5f6f83;
				cursor: pointer;
			}
			.comment .link:hover {
				text-decoration: underline;
			}
			#categoriesDialog > .list {
				width: 540px;
				height: 400px;
				border: 1px solid #dadada;
				border-radius: 4px;
			}
			#categoriesDialog .list.children {
				border: none;
				margin: 0 0 0 40px;
			}
			#categoriesDialog .category {
				cursor: default;
				position: relative;
				border-radius: 4px;
			}
			#categoriesDialog > .list > .category {
				border-radius: 0;
			}
			#categoriesDialog .category .btn.add-child {
				display: none;
				width: 24px;
				height: 24px;
				background: url('/tpf/icons/create-category.svg') center / 20px no-repeat;
				opacity: 0.75;
				position: absolute;
				left: 2px;
				bottom: 4px;
			}
			#categoriesDialog .category:has(> .children > *) > .btn.add-child {
				display: block;
			}
			.category .btn.add-child:hover {
				opacity: 1;
			}
			#categoriesDialog .header {
				padding: 4px;
				border-radius: 3px;
				position: relative;
			}
			#categoriesDialog .header > .actions {
				position: absolute;
				right: 4px;
				top: 3px;
				filter: hue-rotate(-30deg);
			}
			#categoriesDialog .category:hover {
				background: #edfafa;
			}
			#categoriesDialog .category.deleted, #categoriesDialog .category.deleted:hover {
				background: #f3dcdc;
			}
			#categoriesDialog .category:hover:not(.deleted):not(:has(.category:hover)) > .header {
				background: #d4eaea;
			}
			#newCategoryDialog {
				min-width: 410px;
				position: fixed;
				top: 50%;
				left: 50%;
				transform: translate(-50%, -54%);
			}
			#newCategoryDialog > .text {
				margin-bottom: 23px;
			}
			#newCategoryDialog > div > label {
				display: inline-block;
				width: 23%;
				max-width: 100px;
				/* margin-right: 10px; */
			}
			#newCategoryDialog > .line {
				display: flex;
				gap: 8px;
				flex-wrap: nowrap;
				align-items: center;
				margin: 6px 0 10px;
			}
			#newCategoryDialog > .line > .form-control {
				margin: 0;
				flex: 1 0 calc(77% - 10px);
			}
			/* #newCategoryDialog > .line > .form-control {
				display: inline-block;
				max-width: calc(77% - 10px);
			} */
			#newCategoryDialog .buttons {
				margin-top: 25px;
			}
			#newCategoryDialog .buttons > * {
				width: 80px;
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
						// updatePage();
					}
				});
				
				if (localStorage.currentPage) {
					updatePage();
				}
				
				window.currentItemId = 0;
				
				let container = document.querySelector('#page-content .items-list');
				container.addEventListener('click', function(event) {
					if (!this.querySelectorAll('.line').length) return
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

				document.querySelectorAll('.page .main').forEach(el => {
					el.addEventListener('click', function(event) {
						if (event.target.dataset.action == 'show-comments') {
							openComments();
						}
					});
				});
				
				initCommentsEditor()
				
				function initCommentsEditor() {
					let container = document.querySelector('#commentsDialog .list')
					if (container.configured) container = container.children[0]
					container.addEventListener('click', function(event) {
						let comment = event.target.closest('.comment')
						if (event.target.classList.contains('edit')) {
							editComment(comment)
						} else if (event.target.classList.contains('delete')) {
							deleteComment(comment)
						} else if (event.target.classList.contains('restore')) {
							restoreComment(comment)
						} else if (event.target.dataset.action == 'cancel-edit') {
							cancelEdit(comment)
						}
					});
				}
				
				function editComment(comment) {
					if (comment.isEditing) {
						let textBlock = comment.querySelector('.text')
						let textarea = comment.querySelector('textarea')
						let link = textarea ? textarea.nextElementSibling : null
						if (textarea) {
							let content = textarea.value
							let oldValue = textarea.oldValue ?? content
							cancelEdit(comment)
							textBlock.innerHTML = textarea.value.replace(/\r?\n/g, '<br>')
							
							fetch('/editComment?id=' + comment.dataset.id, {
								method: 'POST',
								headers: {
									'Content-Type': 'application/json'
								},
								body: JSON.stringify({ text : content })
							})
							.then(res => {
								if (res.status >= 400) throw {};
								return res.json()
							})
							.catch(() => {
								textBlock.innerHTML = oldValue.replace(/\r?\n/g, '<br>')
							})
						}
						return
					}
					let textarea = document.createElement('textarea')
					let textBlock = comment.querySelector('.text')
					textarea.style.width = '100%'
					textarea.style.marginRight = '-42px'
					textarea.style.marginBottom = '-17px'
					textarea.style.height = textBlock.clientHeight + 10 + 'px'
					textarea.style.padding = '4px 4px 3px 4px'
					textarea.style.position = 'relative'
					textarea.style.left = '-5px'
					textarea.style.top = '-5px'
					textarea.value = textBlock.innerHTML.replace('<br>', '\n')
					textarea.oldValue = textarea.value
					textBlock.style.display = 'none'
					if (textBlock.nextElementSibling) {
						comment.insertBefore(textarea, textBlock.nextElementSibling)
					} else {
						comment.appendChild(textarea)
					}
					
					let cancel = document.createElement('span')
					cancel.className = 'link'
					cancel.setAttribute('data-action', 'cancel-edit')
					cancel.innerHTML = 'Cancel'
					if (textarea.nextElementSibling) {
						comment.insertBefore(cancel, textarea.nextElementSibling)
					} else {
						comment.appendChild(cancel)
					}
					
					textarea.focus()
					comment.isEditing = true
				}
				
				function cancelEdit(comment) {
					if (!comment.isEditing) return
					let textBlock = comment.querySelector('.text')
					let textarea = comment.querySelector('textarea')
					let link = textarea ? textarea.nextElementSibling : null
					if (textarea) {
						textBlock.innerHTML = textarea.oldValue.replace(/\r?\n/g, '<br>')
						comment.removeChild(textarea)
						comment.removeChild(link)
						textBlock.style.display = ''
						comment.isEditing = false
					}
				}
				
				function deleteComment(comment) {
					let soft = !comment.classList.contains('deleted')
					fetch('/deleteItem?type=comment&ids=[' + comment.dataset.id + ']' + (soft ? '&soft' : ''))
					.then(res => {
						if (res.status >= 400) throw {};
						return res.json()
					})
					.catch(() => {
						comment.classList.remove('deleted')
					})
					if (soft) {
						comment.classList.add('deleted')
					} else {
						comments_offset--
						comment.parentNode.removeChild(comment)
					}
				}
				
				function restoreComment(comment) {
					fetch('/restoreItem?type=comment&ids=[' + comment.dataset.id + ']')
					.then(res => {
						if (res.status >= 400) throw {};
						return res.json()
					})
					.catch(() => {
						comment.classList.add('deleted')
					})
					comment.classList.remove('deleted')
				}
				
				document.querySelector('.main').addEventListener('click', function (event) {
					if (event.target.dataset.action == 'delete') {
						deleteItem();
					}
					else if (event.target.dataset.action == 'duplicate') {
						duplicateItem();
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
				
				function openComments() {
					document.querySelectorAll('#modals .dialog').forEach(el => el.style.display = 'none')
					document.getElementById('commentsDialog').style.display = 'block'
					
					comments_offset = 0
					loadComments()
					
					document.getElementById('modals').style.visibility = 'visible'
					setTimeout(() => document.getElementById('modals').style.opacity = '1', 0)
				}
				
				var loadingComments = false
				
				function loadComments(append = false) {
					let offset = comments_offset
					let count = comments_count
					let id = document.getElementById('id').value
					loadingComments = true
					fetch('/getComments?type=' + window.contentType + '&id=' + id + '&offset=' + offset + '&count=' + count)
						.then(res => res.json())
						.then(res => {
							loadingComments = false
							let container = document.querySelector('#commentsDialog .list')
							if (container.configured) container = container.children[0]
							if (!append) container.innerHTML = ''
							for (let item of res.data) {
								let comment = document.createElement('div')
								comment.className = 'comment'
								if (item.isDeleted) comment.classList.add('deleted')
								comment.dataset.id = item.id
								let fullName = [item.author.firstname, item.author.lastname].join(' ').trim()
								comment.innerHTML += '<div class="actions"><div class="btn edit"></div><div class="btn delete"></div><div class="btn restore"></div></div>'
								comment.innerHTML += '<div class="user">' + generateThumbHtml(item.author) + '<div class="name">' + fullName + '</div></div>'
								comment.innerHTML += '<div class="text">' + item.text.replace(/\r?\n/g, '<br>') + '</div>'
								container.appendChild(comment)
							}
							comments_offset += res.data.length
						})
				}
				
				let commentsList = document.querySelector('#commentsDialog .list')
				if (commentsList) {
					let lastPos = 0
					commentsList.addEventListener('scroll', debounce(function(event) {
						let target = event.target
						if (!loadingComments && target.scrollTop != lastPos && target.scrollHeight - target.scrollTop - target.clientHeight < 60) {
							loadComments(true)
							lastPos = target.scrollTop
						}
					}, 800))
				}
				
				var comments_offset = 0
				var comments_count = 10
				
				
				document.querySelectorAll('[data-action="show-categories"]').forEach(el => {
					el.addEventListener('click', function(event) {
						openCategories();
					});
				});
				
				document.querySelectorAll('#categoriesDialog .btn-create').forEach(el => {
					el.addEventListener('click', openNewCategoryDialog);
				});
				
				function initCategoriesEditor() {
					let container = document.querySelector('#categoriesDialog .list')
					if (container.configured) container = container.children[0]
					container.addEventListener('click', function(event) {
						let category = event.target.closest('.category')
						if (event.target.classList.contains('edit')) {
							editCategory(category)
						} else if (event.target.classList.contains('delete')) {
							deleteCategory(category)
						} else if (event.target.classList.contains('restore')) {
							restoreCategory(category)
						} else if (event.target.dataset.action == 'cancel-edit') {
							cancelEdit(category)
						} else if (event.target.dataset.action == 'create-child-category') {
							let category = event.target.closest('.category')
							let select = document.querySelector('#newCategoryDialog select')
							select.value = category ? Array.prototype.find.call(select.children, el => {
								return el.value.match(new RegExp('(\\[|,\\s*)' + category.dataset.id + '\\]$'))
							}).value : '[]'
							openNewCategoryDialog()
						}
					});
				}
				
				function openCategories() {
					document.querySelectorAll('#modals .dialog').forEach(el => el.style.display = 'none')
					document.getElementById('categoriesDialog').style.display = 'block'
					
					document.getElementById('modals').style.visibility = 'visible'
					setTimeout(() => document.getElementById('modals').style.opacity = '1', 0)
				}
				
				function openNewCategoryDialog() {
					document.querySelectorAll('#modals .dialog').forEach(el => el.style.display = 'none')
					document.getElementById('categoriesDialog').style.display = 'block'
					document.getElementById('newCategoryDialog').style.display = 'block'
					document.querySelector('#newCategoryDialog .btn-yes').onclick = function() {
						createCategory()
						
						this.closest('.dialog').style.display = 'none'
						document.querySelector('#newCategoryDialog input').value = ''
						document.querySelector('#newCategoryDialog select').value = '[]'
					}
					if (document.getElementById('modals').style.visibility != 'visible') { 
						document.getElementById('modals').style.visibility = 'visible'
						setTimeout(() => document.getElementById('modals').style.opacity = '1', 0)
					}
					setTimeout(() => document.querySelector('#newCategoryDialog input').focus(), 0)
				}
				
				function filterCategoryName(value) {
					return value.replace(/[<>^{}@#\r\n]/g, '')
				}
				
				function createCategory() {
					let name = filterCategoryName(document.querySelector('#newCategoryDialog input').value)
					if (name.match(/^\s*$/)) return
					let parent = []
					try {
						parent = JSON.parse(document.querySelector('#newCategoryDialog select').value)
					} catch (ex) {}
					
					fetch('/createCategory', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify({ type: window.contentType, name: name, parent: parent })
					})
					.then(res => {
						if (res.status >= 400) throw {};
						return res.json()
					})
					.then((res) => {
						if (res && res.id && res.name) {
							cache.categories[contentType].push(res)
							sessionStorage.cache = JSON.stringify(cache)
						}
						
						// Update from backend in case someone else is making edits
						refreshCategories()
					})
				}
				
				function editCategory(category) {
					if (category.isEditing) {
						let textBlock = category.querySelector('.name')
						let textarea = category.querySelector('textarea')
						//let link = textarea ? textarea.nextElementSibling : null
						if (textarea) {
							let content = filterCategoryName(textarea.value)
							let oldValue = textarea.oldValue ?? content
							cancelEdit(category)
							textBlock.innerHTML = content
							
							fetch('/renameCategory?id=' + category.dataset.id, {
								method: 'POST',
								headers: {
									'Content-Type': 'application/json'
								},
								body: JSON.stringify({ name : content })
							})
							.then(res => {
								if (res.status >= 400) throw {};
								return res.json()
							})
							.then(() => {
								// Update from backend in case someone else is making edits
								refreshCategories()
							})
							.catch(() => {
								textBlock.textContent = filterCategoryName(oldValue)
							})
							
							let cachedCategory = cache.categories[contentType].find((cat) => cat.id == category.dataset.id)
							if (cachedCategory) cachedCategory.name = textBlock.textContent
							sessionStorage.cache = JSON.stringify(cache)
						}
						return
					}
					let textarea = document.createElement('textarea')
					let textBlock = category.querySelector('.name')
					textarea.style.width = 'calc(100% - 80px)'
					textarea.style.marginRight = '-42px'
					textarea.style.marginBottom = '-12px'
					textarea.style.height = textBlock.clientHeight + 5 + 'px'
					textarea.style.padding = '2px 4px 3px 4px'
					textarea.style.position = 'relative'
					textarea.style.left = '-5px'
					textarea.style.top = '-3px'
					textarea.value = textBlock.textContent.replace('<br>', '')
					textarea.oldValue = textarea.value
					textBlock.style.display = 'none'
					if (textBlock.nextElementSibling) {
						textBlock.parentNode.insertBefore(textarea, textBlock.nextElementSibling)
					} else {
						textBlock.parentNode.appendChild(textarea)
					}
					
					textarea.addEventListener('input', function() {
						this.value = filterCategoryName(this.value)
					})
					
					textarea.addEventListener('keydown', function(event) {
						if (event.key == 'Enter') editCategory(this.closest('.category'))
						else if (event.key == 'Escape') cancelEdit(this.closest('.category'))
					})
					
					textarea.addEventListener('blur', function(event) {
						if (!event.relatedTarget || !event.relatedTarget.classList.contains('edit')) {
							setTimeout(() => {
								cancelEdit(this.closest('.category'))
							}, 50)
						}
					})
					
					textarea.focus()
					category.isEditing = true
				}
				
				function cancelEdit(category) {
					if (!category || !category.isEditing) return
					let textBlock = category.querySelector('.name')
					let textarea = category.querySelector('textarea')
					//let link = textarea ? textarea.nextElementSibling : null
					if (textarea) {
						textBlock.innerHTML = textarea.oldValue.replace(/[<>^{}@#\r\n]/g, '')
						textBlock.parentNode.removeChild(textarea)
						//textBlock.parentNode.removeChild(link)
						textBlock.style.display = ''
						category.isEditing = false
					}
				}
				
				function deleteCategory(category) {
					let soft = !category.classList.contains('deleted')
					fetch('/deleteCategory?ids=[' + category.dataset.id + ']' + (soft ? '&soft' : ''))
					.then(res => {
						if (res.status >= 400) throw {};
						return res.json()
					})
					.then(() => {
						// Update from backend in case someone else is making edits
						refreshCategories()
					})
					.catch(() => {
						category.classList.remove('deleted')
					})
					let cachedCategory = cache.categories[contentType].find((cat) => cat.id == category.dataset.id)
					if (soft) {
						category.classList.add('deleted')
						if (cachedCategory) cachedCategory.isDeleted = true
					} else {
						category.parentNode.removeChild(category)
						if (cachedCategory) cache.categories[contentType].splice(cache.categories[contentType].indexOf(cachedCategory), 1)
					}
					sessionStorage.cache = JSON.stringify(cache)
				}
				
				function restoreCategory(category) {
					fetch('/restoreCategory?ids=[' + category.dataset.id + ']')
					.then(res => {
						if (res.status >= 400) throw {};
						return res.json()
					})
					.then(() => {
						// Update from backend in case someone else is making edits
						refreshCategories()
					})
					.catch(() => {
						category.classList.add('deleted')
					})
					category.classList.remove('deleted')
					
					let cachedCategory = cache.categories[contentType].find((cat) => cat.id == category.dataset.id)
					if (cachedCategory) cachedCategory.isDeleted = false
					sessionStorage.cache = JSON.stringify(cache)
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
						if (timer) {
							clearTimeout(timer)
						}
						timer = null
						if (Date.now() - last > timeout) {
							if (busy && !timer) {
								timer = setTimeout(arguments.callee, timeout, ...arguments)
								return
							}
							busy = true
							try {
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
							if (!timer) timer = setTimeout(arguments.callee, Math.max(0, timeout - (Date.now() - last)), ...arguments)
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
					btn.onclick = function(event) {
						if (this.closest('.dialog').dataset.type == 'secondary') {
							this.closest('.dialog').style.display = 'none'
							return
						}
						hideModals()
					}
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
					await buildForm(window.contentType)
					await loadCategories(window.contentType)
					await reloadContent()
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
				
				document.querySelectorAll('#header nav > a').forEach(link => {
					link.addEventListener('click', function() {
						if (this.dataset.href) changeSection(this.dataset.href);
					});
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
				
				initToolbar()
				
				await buildForm(window.contentType);
				await loadCategories(window.contentType);
				
				initCategoriesEditor()
				
				if (localStorage.category !== undefined) {
					let catlist = document.querySelector('.category-filter');
					if (catlist) catlist.value = localStorage.category;
				}
				
				await reloadContent();
				
				if ('XScroll' in window) setTimeout(function() {
					XScroll.initAll();
				}, 50)
			});
			
			
			
			async function changeSection(section) {
				if (section.charAt(0) == '#') {
					section = section.slice(1)
				}
				window.page = 1
				window.currentItemId = 0
				
				document.getElementById('subheader-content').style.display = (section == 'content' ? '' : 'none')
				
				if (section == 'content') {
					let link = document.querySelector('#subheader-content .item_types .link.active') || document.querySelector('#subheader-content .item_types .link')
					let type = link && link.dataset.value || 'blog_post'
					window.contentType = type
					await buildForm(window.contentType)
					await loadCategories(window.contentType)
					await reloadContent()
				}
				else if (section == 'users') {
					window.contentType = 'user'
					await buildForm(window.contentType)
					await reloadContent()
				}
				
				document.querySelector('.toolbar').classList.toggle('users', section == 'users')
				document.querySelector('.search_wrap .dropdown').style.display = (section == 'users' ? 'none' : '')
				
				document.querySelectorAll('#header nav > a').forEach(link => {
					link.classList.toggle('active', link.dataset.href == '#' + section)
				})
			}
			
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
			
			function initToolbar() {
				document.querySelectorAll('[data-action="new"]').forEach(function (el) {
					el.addEventListener('click', function(event) {
						newItem();
					});
				});
				document.querySelectorAll('[data-action="move"]').forEach(function (el) {
					el.addEventListener('click', function(event) {
						openSetCategoryDialog();
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
				
				if (contentType == 'user') {
					title = [item.firstname, item.lastname].join(' ').trim()
					createdAt = item.registeredAt ? formatDate(new Date(item.registeredAt), 'DD.MM.YYYY HH:mm:ss') : '-'
					modifiedAt = item.lastLoginAt ? formatDate(new Date(item.lastLoginAt), 'DD.MM.YYYY HH:mm:ss') : '-'
				}
				
				let line = document.createElement('div');
				line.classList.add('line');
				if (contentType == 'user') {
					line.classList.add('user');
				}
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
				if (document.querySelector('.search-input-field').value.match(/^(@[\w\d]+|[^@#\s])/)) {
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
				let nophoto = item.username ? '/tpf/icons/userpic.svg' : '/tpf/icons/images/no-photo.jpg';
				image = image ? imagePath + image : nophoto;
				return '<div class="thumb' + (image == nophoto ? ' noimage' : '') + '" style="background: url(\'' + image + '\') center center / cover no-repeat"></div>';
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
					return Promise.resolve(cache.schemas[type]);
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
				return getSchema(type).then(res => {
					let container = document.querySelector('#page-content .main .form');
					container.innerHTML = '';
					console.log(res);
					for (let field in res.schema) {
						if (field == 'isDeleted') continue;
						let type = res.schema[field];
						if (type == 'int') {
							type = 'number';
						}
						let input = document.createElement(field == 'categories' || contentType == 'user' && field == 'role' ? 'select' : (type == 'text' ? 'textarea' : 'input'))
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
						
						let fieldName = field;
						field = field.replace(/([a-z])([A-Z])/g, '$1 $2');
						field = field[0].toUpperCase() + field.slice(1);
						label.textContent = field;
						
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
						if (contentType == 'user' && field.match(/(Registered|Last Login) At/)) {
							input.readOnly = true;
						}
						if (contentType == 'user' && field == 'Role') {
							for (let role in users_roles) {
								let option = new Option(users_roles[role], role)
								input.appendChild(option)
							}
						}
						if (field == 'Id') {
							var panel = document.createElement('div')
							panel.className = 'actions float-end'
							panel.innerHTML = '<button class="btn btn-primary" data-action="duplicate">Duplicate</button><button class="btn btn-primary" data-action="delete">Delete</button>'
							input.parentNode.appendChild(panel)
							
							if (contentType != 'user') {
								var btn = document.createElement('div')
								btn.className = 'btn btn-comments float-end'
								btn.setAttribute('data-action', 'show-comments')
								input.parentNode.appendChild(btn)
							}
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
			
			function refreshCategories() {
				delete cache.categories[contentType]
				sessionStorage.cache = JSON.stringify(cache)
				loadCategories(contentType)
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
						if (category.isDeleted) continue
						let fullCategoryName = category.path.join(' > ');
						let value = list.classList.contains('category-filter') ? category.id : JSON.stringify(category.id_path);
						let option = new Option(fullCategoryName, value);
						list.appendChild(option);
					}
				});
				updateEditorCategories(categories)
			}
			
			function updateEditorCategories(categories) {
				let container = document.querySelector('#categoriesDialog .list')
				if (container.configured) container = container.children[0]
				container.innerHTML = ''
				let data = [], map = {}
				for (let i = 0; i < categories.length; i++) {
					let id_path = categories[i].id_path
					if (!id_path.length) continue
					let id = id_path[id_path.length-1]
					let name = categories[i].name
					let parent_id = id_path.length > 1 ? id_path[id_path.length-2] : null
					if (!map[id]) {
						map[id] = { id: id, name: name, path: JSON.stringify(id_path), id_path: id_path, children: [], deleted: categories[i].isDeleted }
						if (!parent_id) data.push(map[id])
						else map[parent_id].children.push(map[id])
					}
				}
				
				//console.log(data)
				
				addCategoryList(container, data)
				
				function addCategoryList(container, data) {
					for (let category of data) {
						let block = document.createElement('div')
						block.className = 'category empty'
						if (category.deleted) {
							block.classList.add('deleted')
						}
						block.innerHTML += '<div class="header"><div class="name">' + category.name + '</div></div>'
						block.children[0].innerHTML += '<div class="actions"><div class="btn edit"></div><div class="btn delete"></div><div class="btn restore"></div></div>'
						block.dataset.id = category.id
						container.appendChild(block)
						
						if (category.children && category.children instanceof Array) {
							let childrenBlock = document.createElement('div')
							childrenBlock.className = 'list children'
							block.appendChild(childrenBlock)
							addCategoryList(childrenBlock, category.children)
							if (category.children.length) block.classList.remove('empty')
						}
						
						block.innerHTML += '<button class="btn add-child" data-action="create-child-category"></button>'
					}
				}
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
							} else if (contentType == 'user' && input.name.match(/^(registered|lastLogin)At$/)) {
								if (res[input.name]) {
									input.value = Intl ? new Intl.DateTimeFormat('en-GB', {
										dateStyle: 'short',
										timeStyle: 'long',
										timeZone: 'GMT',
									}).format(Date.parse(res[input.name])) : res[input.name].replace('T', ' ');
								} else {
									input.value = 'Never'
								}
								input.disabled = true
							} else if (contentType == 'user' && input.name == 'password') {
								input.hiddenValue = res[input.name]
								input.value = '**********'
								input.classList.add('password-form-field')
								input.rows = 1
								input.disabled = true
							}else if (input.type != 'checkbox' && input.name in res) {
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
					if (contentType == 'user' && input.name == 'password') {
						if (!input.hiddenValue) continue
						value = input.hiddenValue
					}
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
					line.querySelector('input[type="checkbox"]').checked = false
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

				let passwordField = document.querySelector('.form-control[name="password"]')
				if (passwordField) {
					passwordField.disabled = false
					passwordField.readOnly = true
				}

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
					fetch('/deleteItem?' + (category != 'trash' && contentType != 'user' ? 'soft&' : '') + 'type=' + window.contentType + '&ids=[' + ids.join(',') + ']')
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
			let users_roles = <?php echo json_encode(Tpf\Service\UsersService::getRoles()); ?>;
		</script>
	</head>
	<body>
		<div id="header">
			<div class="logo">Admin panel</div>
			<nav><a class="active" data-href="#content">Content</a><a data-href="#users">Users</a></nav>
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
			<div id="categories-toggle-wrap">
				<span class="link" data-action="show-categories">Categories</span>
			</div>
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
									<div id="search_variants" class="list scrollable scroll_y" button-size="1" scroll-delta="20" thumb-width="6" thumb-length="200"></div>
								</div>
							</div>
						</div>
						<div class="btn_group categories">
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
			<div class="dialog" id="commentsDialog">
				<div class="list scrollable scroll_y" button-size="1" scroll-delta="20" thumb-width="6" thumb-length="200"></div>
				<div class="buttons"><div class="btn btn-primary btn-no">Close</div></div>
			</div>
			<div class="dialog" id="categoriesDialog">
				<div class="list scrollable scroll_y" button-size="1" scroll-delta="20" thumb-width="6" thumb-length="200"></div>
				<div class="buttons"><div class="btn btn-primary btn-create">New</div><div class="btn btn-primary btn-no">Close</div></div>
			</div>
			<div class="dialog" id="newCategoryDialog" data-type="secondary">
				<div class="text">Create new category:</div>
				<div class="line"><label for="new-category-parent">Parent</label><select data-role="set-category" name="categories" class="form-control" id="new-category-parent"><option value="[]">None</option></select></div>
				<div class="line"><label for="new-category-title">Title</label><input type="text" name="category-name" class="form-control" id="new-category-title"></div>
				<div class="buttons"><div class="btn btn-primary btn-yes">Create</div><div class="btn btn-primary btn-no">Cancel</div></div>
			</div>
		</div>
	</body>
</html>
