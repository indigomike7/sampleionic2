/*
 *
 * Copyright (c) 2010 C. F., Wong (<a href="http://cloudgen.w0ng.hk">Cloudgen Examplet Store</a>)
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 *
 */
var Typeahead = {
	/**
	 * cached data for later used.
	 */
	cached : {
	},
	data : {
	},
	emotions : [],
	init : function(ele, opts) {
		if (ele.attr('typeahead-init')) {
			return;
		}
		ele.attr('typeahead-init', '1');
		Typeahead.create(ele, opts);
	},
	setData : function(obj) {
		Typeahead.data = obj;
	},
	sharedTags : {},
	create : function(ele, newOpts, taggeds) {
		/**
		 * waiting time from keydown to process text
		 */
		var time = 20;
		/**
		 *timeout id
		 */
		var timeId = 0;

		/**
		 *  token position
		 */
		var tokenPos = 0;

		/**
		 * token string
		 */
		var tokenStr = '';

		/**
		 * input element
		 */
		var input = $(ele);

		/**
		 * closed
		 */
		var container = input.closest('._uiMentionTypeAheadContainer');

		/**
		 * dom
		 */
		var hidden = container.find('._uiMentionHidden');

		/**
		 *dom
		 */
		var hightlight = container.find('._uiMentionHighlighter');

		/**
		 * ac list is hidding
		 */
		var isHide = true;

		/**
		 * ul list
		 */
		var ul = 0;

		/**
		 * replace element.
		 */
		var reg2 = /(\[x\=\w+\@\d+\])([^\[]+)(\[\/x\])/gi;

		var reg3 = /(\#)([^\s"]+)/gi;

		// var quicktag = $('#id_status_quick_tag');

		var quicktagStage  = $('#id_status_quick_tag_wrapper');

		/**
		 * searchLink regular
		 */
		var linkReg = /(http|https):\/\/(\w|\-|\_)+(\.\w{2,})+([\w#!:.?+=&%@!\-\/])*(\s|\n)/i;

		var opts = $.extend({
			min : 0,
			max : 10,
			onSubmit : false,
			minHeight : 65,
			deltaHeight : 0,
			remoteUrl : false,
			maxDisplay : 10,
			autoParseLink : true,
			onParseLink : false,
			tagsKey : false,
			callback: function(){},
		}, newOpts);

		var tagsKey = 'all';

		if (opts.tagsKey == false) {
			tagsKey = input.attr('id');
		}

		if ( typeof Typeahead.sharedTags[tagsKey] == 'undefined') {
			Typeahead.sharedTags[tagsKey] = {};
		}

		/**
		 * escape regular expression
		 */
		function escape(text) {
			return text.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&");
		}

		function filterData(keyword) {
			console.log('filter data', keyword);
			var rows = new Array();
			var length = Typeahead.data.length;
			var taken = keyword.substr(1).replace(/$/g, '').split(/\s+/g);

			for (var i = 0; i < taken.length; ++i) {
				taken[i] = escape(taken[i]);
			}

			var num = taken.length;
			var reg = new RegExp('(^|\\s+)(' + taken.join('|') + ')', 'gi');

			// console.log(reg);

			for (var i = 0; i < length && rows.length <= 30; i++) {
				var obj = Typeahead.data[i];
				var match = obj.title.match(reg);

				if (match && match.length >= num) {
					rows.push(obj);
				}
			}

			var ret = {
				'q' : keyword,
				'rows' : rows
			};

			return ret;
		}

		/**
		 * create ul dom
		 */
		function createUl() {
			ul = $('#_uiAcList');
			if (ul.length == 0) {
				ul = $('<ul class="_uiFlyLayer _uiAcList" id="_uiAcList">').appendTo('body');
			}
		}

		/**
		 * hide ul list
		 */
		function hideUl() {
			isHide = true;
			ul && ul.hide();
			container.css({
				top: 0,
			});
			quicktagStage.addClass('ng-hide');
		}

		/**
		 *show
		 */
		function showUl() {
			// var offset = input.offset();
			ul.css({
				// left : offset.left,
				// top : offset.top + input.height(),
				// width : input.width()
			}).show();
			console.log('show ul');
			isHide = false;
			var pos = input.textareaHelper('caretPos');
			container.css({
				top: -pos.top,
			});

			quicktagStage.removeClass('ng-hide');
		}



		/**
		 * move list up
		 */
		function goUp() {
			var e = ul.find('li.active');
			e = e.prev();

			if (e.length == 0) {
				e = ul.find('li.tag-item:first');
			}
			ul.find('li').removeClass('active');
			e.addClass('active');
		}

		/**
		 * move list down
		 */
		function goDown() {
			var e = ul.find('li.active');
			e = e.next();

			if (e.length == 0) {
				e = ul.find('li.tag-item:first');
			}
			ul.find('li').removeClass('active');
			e.addClass('active');
		}

		/**
		 * get current active item
		 */
		function getActiveLi() {
			if (isHide) {
				return false;
			}
			var e = ul.find('li.active');

			if (e.length == 0) {
				e = ul.find('.tag-item:first');
			}

			if (e.length == 0) {
				return false;
			}

			return e;
		}

		/**
		 * create item
		 * @param Object {type, id, text, photo}
		 */
		function createLi(obj) {
			var html = '<img class="lfloat mright5" width="32" height="32" src="' + obj.img + '" />' + obj.title + '';
			var li = $('<li class="clearfix tag-item">').appendTo(ul);
			li.html(html);
			li.bind('click', function(evt) {
				console.log("click on li");
				selectHandle(obj);
			});
		}

		function createList(data) {
			createUl();
			ul.html('');
			Typeahead.cached[data.q] = data;

			if (data.q != tokenStr) {
				hideUl();
				return;
			}

			var tags = Typeahead.sharedTags[tagsKey];

			var count = 0;
			for (var i = 0; i < data.rows.length; ++i) {
				var o = data.rows[i];
				var id = o.type + '.' + o.id;
				if ( typeof tags[id] != 'undefined')
					continue;
				createLi(data.rows[i]);
				if (++count > opts.maxDisplay) {
					break;
				};
			}

			/**
			 *check if empty content.
			 */
			if (!count) {
				hideUl();
			} else {
				showUl();
			}

		}

		/**
		 * pre process
		 */
		function processInput() {
			/**
			 * check caret pos
			 */
			caretPos = getCaret();

			var val = input.val();
			val += " ";

			/**
			 * get query string
			 */
			var q = val.substr(0, caretPos);

			/**
			 * get token pos
			 */
			tokenPos = q.lastIndexOf('@');


			console.log('process input', caretPos,q, tokenPos);

			if (tokenPos < 0) {
				isHide || hideUl();
				return '';
			};



			q = q.substr(tokenPos, caretPos);

			/**
			 * do not check if token string is the same
			 */
			if (q == tokenStr) {
				isHide || hideUl();
				return;
			}

			/**
			 * apply new tokenStr to check
			 */
			tokenStr = q;

			if (tokenStr == '' || tokenStr == '@') {
				isHide || hideUl();
				return;
			}

			if (tokenStr.length < opts.min || tokenStr.length > opts.max) {
				isHide || hideUl();
				return;
			}
			/**
			 *check in cached
			 */
			if ( typeof Typeahead.cached[tokenStr] == 'object') {
				createList(Typeahead.cached[tokenStr]);
				return;
			}

			// quicktag.text(tokenStr);

			if (opts.remoteUrl) {
				/**
				 * get data from remote
				 */

				$.getJSON(opts.remoteUrl, {
					'q' : tokenStr
				}, createList);
			} else {
				createList(filterData(tokenStr));
			}

		}

		var oldText = '';

		function setCaret(start, end){
			console.log(start, end);
			if (ele.createTextRange) {
				var range = ele.createTextRange();
				range.move('character', start);
				range.select();
			} else {
				if (ele.selectionStart) {
					ele.focus();
					if (typeof(cordova) != 'undefined' && !cordova.plugins.Keyboard.isVisible) {
						cordova.plugins.Keyboard.show();
					}
					ele.setSelectionRange(start, end);
				} else {
					ele.focus();
					if (typeof(cordova) != 'undefined' && !cordova.plugins.Keyboard.isVisible) {
						cordova.plugins.Keyboard.show();
					}
				}
			}
		}


		function getCaret() {
			var caretPos = 0;
			// IE Support
			if(document.selection) {
				ele.focus();
				if (typeof(cordova) != 'undefined' && !cordova.plugins.Keyboard.isVisible) {
					cordova.plugins.Keyboard.show();
				}
				var Sel = document.selection.createRange();
				Sel.moveStart('character', -ele.value.length);
				caretPos = Sel.text.length;
			} else if(ele.selectionStart || ele.selectionStart == '0') {
				caretPos = ele.selectionStart;
			}else{
				console.log("does not support carret post.");
			}
			return caretPos;
		}

		/**
		 * update hidden val
		 */
		function updateHidden(val) {
			val = val.replace('﻿', '');
			var html = val.replace(reg2, '<b>$2</b>')
				.replace(reg3, '<b>$1$2</b>');

			hidden.val(val);
			hightlight.html(html + '  ');

			var newVal = val.replace(reg2, '$2﻿');
			var caret = getCaret();

			var height = hightlight.height();
			/**
			 * autoglow
			 */
			hightlight.css({
				width : input.width()
			});

			if (height > opts.minHeight) {
				input.css({
					'height' : height + opts.deltaHeight
				});
			}

			if (oldText && oldText != newVal) {
				input.val(newVal);
				/**
				 * it cause issue on IE: could not enter
				 */
				//input.caret(caret, caret);

			}
			oldText = newVal;
		}

		var isProcessing = false;

		/**
		 * var
		 */
		function processHidden() {
			if (isProcessing) {
				return;
			}

			isProcessing = true;

			var tags = Typeahead.sharedTags[tagsKey];

			var of = getCaret();
			var val = input.val();

			for (var i in tags) {
				var tag = tags[i];
				/**
				 * found tag,now replace by token
				 */
				var len = tag.title.length;
				var pos = val.search(tag.title);
				if (pos > -1) {
					val = val.substr(0, pos) + tag.token + val.substr(pos + len);
				} else {
					delete (tags[i]);
				}
			}

			Typeahead.sharedTags[tagsKey] = tags;
			updateHidden(val);

			var id = hidden.attr('id');

			isProcessing = false;
		}

		function pasteText(evt){
			var text = evt[0];
			var pos = getCaret();

			if(pos ==0){ pos = input.val().length;}

			var val = input.val();
			var newval =  val.substr(0,pos) + '' + text + '' + val.substr(pos);
			var newpos =  pos + text.length;

			input.val(newval);
			setCaret(newpos, newpos);
			processHidden();

		}

		function selectHandle(obj) {
			hideUl();
			var pos1 = tokenPos;
			var pos2 = getCaret();
			var text = obj.title + '﻿';
			var newCaretPos = pos1 + text.length + 1;

			/**
			 * update input val
			 */
			var val = input.val();
			val = val.substr(0, pos1) + text + ' ' + val.substr(pos2);
			input.val(val);
			setCaret(newCaretPos, newCaretPos);
			// input.caret(newCaretPos, newCaretPos);
			input.focus();
			if (typeof(cordova) != 'undefined' && !cordova.plugins.Keyboard.isVisible) {
				cordova.plugins.Keyboard.show();
			}
			
			/**
			 * update hidden val
			 */
			var id = obj.type + '.' + obj.id;
			var token = '[x=' + obj.type + '@' + obj.id + ']' + obj.title + '[/x]';
			Typeahead.sharedTags[tagsKey][id] = {
				'title' : text,
				'text2' : obj.title,
				'token' : token
			};
			processHidden();
		}

		/**
		 *
		 */
		function keyupHandle(evt) {
			processInput();
			//  s.length > 0 ? getFriends(s) : ac.hide();
			timeId = window.setTimeout(processHidden, time);
		}

		/**
		 * handle event keydown
		 * @param Event evt
		 * @return void
		 */
		function keydownHandle(evt) {

			if (evt && evt.keyCode) {
				var keyCode = evt.keyCode
				if (!evt.shiftKey && !evt.altKey && !evt.ctrlKey) {

					if (false == isHide) {

						if (38 == keyCode) {
							// move up
							evt.preventDefault();
							goUp();
						} else if (40 == keyCode) {
							// move down
							evt.preventDefault();
							goDown();
						} else if (13 == keyCode || 9 == keyCode) {
							evt.preventDefault();
							// enter
							var li = getActiveLi();
							if (li) {
								li.trigger('mousedown');
							}
						} else if (9 == keyCode || 27 == keyCode) {
							//escape
							evt.preventDefault();
							hideUl();

						} else if (37 == keyCode) {

						} else if (39 == keyCode) {

						}
					} else {
						if (13 == keyCode && opts.onSubmit) {
							evt.preventDefault();
							opts.onSubmit(input);
						}
					}
					//console.log(keyCode);
					return;
				}
				/**
				 *stop changed.
				 */
				if (evt.ctrlKey && (86 == evt.keyCode || 67 == evt.keyCode)) {

				}
			}
		}

		function clickHandle() {
			ul && hideUl();
		}

		function focus(evt) {

		}

		function changedHandle() {
			//console.log('changed handle');
			processHidden();
		}


		hightlight.css({
			'width' : input.width()
		});

		// console.log('textarea',input, 'hightlight', hightlight);

		input.bind('keyup', keyupHandle)
			.bind('keydown', keydownHandle)
			.bind('input', processHidden)
			.bind('paste', processHidden)
			.bind('cut', processHidden)
			.bind('pastetext', pasteText)
			.bind('changed', changedHandle);

		// $('#textarea').trigger('changed);

		$(document).bind('click', clickHandle);

		if(typeof opts.callback == 'function'){
			opts.callback();
		}


		Typeahead.sharedTags[tagsKey] =  typeof taggeds != 'undefined'?taggeds:{};

		console.log(Typeahead.sharedTags[tagsKey]);

		processHidden();
	}
};
