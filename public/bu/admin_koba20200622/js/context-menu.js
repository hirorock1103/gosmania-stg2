/* =============================================================================
	ContextMenu (Right Click Menu)
	version 0.7.6
	Copyright(c) 2016, ShanaBrian
	This software is released under the MIT License.
============================================================================= */
(function(global) {
	var win = window;

	/*
	 * default settings
	 *
	 * element               : メニューを割り当てる要素
	 * menuClassName         : コンテキストメニュー本体のクラス名
	 * menuTitleClassName    : メニュータイトルのクラス名
	 * menuListClassName     : メニューリストのクラス名
	 * menuItemClassName     : 個々のメニューのクラス名
	 * subMenuClassName      : サブメニューリストのクラス名
	 * openFlagClassName     : メニューが開いているかどうかのクラス名（body要素に付与される）
	 * closeFlagClassName    : メニューが閉じているかどうかのクラス名（body要素に付与される）
	 * openClassName         : メニューを開いたときのメニュー本体に付与されるクラス名
	 * closeClassName        : メニューを閉じたときのメニュー本体に付与されるクラス名
	 * subMenuOpenClassName  : サブメニューを開いたときのメニュー本体に付与されるクラス名
	 * subMenuCloseClassName : サブメニューを閉じたときのメニュー本体に付与されるクラス名
	 * headerTitle           : メニューのタイトル見出し
	 * menuList              : メニューの指定
	 * openCallback          : 開いたときのコールバック関数
	 * closeCallback         : 閉じたときのコールバック関数
	 */
	var defaultSettings = {
		element               : win,
		openAnimation         : 'none',
		closeAnimation        : 'none',
		openAnimationOption   : {},
		closeAnimationOption  : {},
		menuClassName         : 'contextmenu',
		menuTitleClassName    : 'title',
		menuListClassName     : 'menu-items',
		menuItemClassName     : 'menu-item',
		menuIconClassName     : 'icon',
		menuDivideClassName   : 'divide',
		menuDisabledClassName : 'disabled',
		subMenuClassName      : 'sub-menu',
		openFlagClassName     : 'contextmenu-open',
		closeFlagClassName    : 'contextmenu-close',
		openClassName         : 'open',
		closeClassName        : 'close',
		subMenuOpenClassName  : 'sub-menu-open',
		subMenuCloseClassName : 'sub-menu-close',
		headerTitle           : '',
		menuList              : [],
		openCallback          : function() {},
		closeCallback         : function() {}
	};

	/*
	 * animation default settings
	 *
	 * speed : アニメーション速度
	 * delay : アニメーションの遅延時間
	 */
	var animationDefaultSettings = {
		speed : 200,
		delay : 0
	};

	/*
	 * menu default settings
	 *
	 * text     : ボタン文字列
	 * href     : ボタンをクリックしたときに遷移するページのパス
	 * target   : ボタンをクリックしたときに遷移する仕方
	 * action   : ボタンをクリックしたときに実行する関数
	 * icon     : アイコンを表示する場合の画像パス
	 * disabled : ボタンの無効化
	 * subMenu  : サブメニューを表示する場合の指定
	 * divide   : ボタンの境界とするかどうか
	 */
	var menuDefaultSettings = {
		text     : '',
		href     : '',
		target   : '',
		action   : function() {},
		icon     : '',
		disabled : false,
		subMenu  : [],
		divide   : false
	};

	var ContextMenu = function() {
		this._initalize.apply(this, arguments);
		return this;
	};

	ContextMenu.prototype = {
		_settings : {},
		_data     : {},
		_doc      : null,

		/*
		 * initalize
		 */
		_initalize : function(options) {
			var i, len, element;

			this._settings = arrayMerge(defaultSettings, options);

			this._settings.openAnimationOption = arrayMerge(animationDefaultSettings, this._settings.openAnimationOption);

			this._settings.closeAnimationOption = arrayMerge(animationDefaultSettings, this._settings.closeAnimationOption);

			if (!this._settings.menuList || this._settings.menuList.length === 0) return;

			for (i = 0, len = this._settings.menuList.length; i < len; i++) {
				this._settings.menuList[i] = arrayMerge(menuDefaultSettings, this._settings.menuList[i]);
			}

			if (typeof this._settings.headerTitle !== 'string') {
				this._settings.headerTitle = '';
			}

			if (this._settings.headerTitle) {
				this._settings.headerTitle = this._settings.headerTitle.replace(/<(no)?script(.|\s)*?<\/(no)?script>/g, '');
			}

			this._doc = document.body;

			if (typeof this._settings.element === 'string') {
				element = document.querySelectorAll(this._settings.element);
			} else {
				element = this._settings.element;
			}

			this._data = {
				element          : element,
				menuElement      : null,
				menuItemElements : [],
				status           : '',
				callOutside      : false
			};

			this._setup();
		},

		/*
		 * _setup
		 */
		_setup : function() {
			var self    = this,
				element = this._data.element,
				x, y;

			var menuHandler = function(e) {
				if (!e) {
					e = event;
				}

				if (e.preventDefault) {
					e.preventDefault();
				} else {
					e.returnValue = false;
				}

				if (e.pageX) {
					x = e.pageX;
					y = e.pageY;
				} else {
					x = e.clientX;
					y = e.clientY;
				}

				if (self._data.status === 'closed') {
					self.openMenu(self._settings.openCallback);
					self._setMenuPosition(x, y);
				}
			};

			addEvent(document, 'click', function(e) {
				if (self._data.callOutside) {
					self._data.callOutside = false;
					return;
				}

				if (self._data.status !== 'opened' ||
					e.originalTarget === self._data.menuElement ||
					belongsElement(e.originalTarget, self._data.menuElement)) return;

				self.closeMenu(self._settings.closeCallback);
			});

			this._createMenu();

			if (!this._data.menuElement) return;

			if (element !== win && element.length) {
				for (var i = 0, len = element.length; i < len; i++) {
					addEvent(element[i], 'contextmenu', menuHandler);
				}
			} else {
				addEvent(element, 'contextmenu', menuHandler);
			}

			addEvent(document, 'keyup', function(e) {
				if (self._data.status === 'opened') {
					if (!e) {
						e = event;
					}

					if (!e) return true;
					if (e.keyCode !== 27) return true;

					self.closeMenu(self._settings.closeCallback);
				}
			});

			this._data.element = element;
		},

		/*
		 * _createMenu
		 */
		_createMenu : function() {
			var contextMenuElmt = document.createElement('div'),
				titleElement    = null,
				titleTextNode   = null,
				menuElmt        = null;

			contextMenuElmt.className = this._settings.menuClassName;

			menuElmt = this._createMenuGroup(this._settings.menuList, this._settings.menuListClassName, true);

			if (menuElmt) {
				if (this._settings.headerTitle) {
					titleElement  = document.createElement('p'),
					titleTextNode = document.createTextNode(this._settings.headerTitle),
					titleElement.className = this._settings.menuTitleClassName;
					titleElement.appendChild(titleTextNode);
					contextMenuElmt.appendChild(titleElement);
				}

				contextMenuElmt.appendChild(menuElmt);
				this._doc.appendChild(contextMenuElmt);
				this._data.menuElement = contextMenuElmt;

				addClassName(this._doc, this._settings.closeFlagClassName);
				addClassName(this._data.menuElement, this._settings.closeClassName);

				this._data.status = 'closed';
			}
		},

		/*
		 * _createMenuGroup
		 */
		_createMenuGroup : function(menuData, menuClassName, subMenuFlag) {
			var self            = this,
				groupElmt       = document.createElement('ul'),
				menuItemElmt    = document.createElement('li'),
				menuBtnElmt     = document.createElement('button'),
				iconElmt        = document.createElement('span'),
				iconImgElmt     = document.createElement('img'),
				setMenuItemElmt = null,
				setMenuBtnElmt  = null,
				setIconElmt     = null,
				setIconImgElmt  = null,
				subGroupElmt    = null,
				textNode        = null,
				i, len;

			if (menuData.length === 0) return null;

			addClassName(groupElmt, menuClassName);
			addClassName(menuItemElmt, this._settings.menuItemClassName);
			addClassName(iconElmt, this._settings.menuIconClassName);

			var btnAction = function(e) {
				var _this        = (e.target) ? this : e.srcElement;
				var selfMenuData = _this.parentNode.menuData;

				if (selfMenuData.disabled) return;

				selfMenuData.action.apply(self, [e]);

				if (selfMenuData.href) {
					win.open(selfMenuData.href, selfMenuData.target);
				}

				self.closeMenu(self._settings.closeCallback);
			};

			var subMenuOpenAction = function(e) {
				var _this = (e.target) ? this : e.srcElement;

				removeClassName(_this, self._settings.subMenuCloseClassName);
				addClassName(_this, self._settings.subMenuOpenClassName);
			};

			var subMenuCloseAction = function(e) {
				var _this = (e.target) ? this : e.srcElement;

				removeClassName(_this, self._settings.subMenuOpenClassName);
				addClassName(_this, self._settings.subMenuCloseClassName);
			};

			for (i = 0, len = menuData.length; i < len; i++) {
				setMenuItemElmt = menuItemElmt.cloneNode();
				setMenuBtnElmt  = menuBtnElmt.cloneNode();
				textNode        = document.createTextNode(menuData[i].text);
				subGroupElmt    = null;

				if (menuData[i].disabled) {
					setMenuBtnElmt.disabled = true;
					addClassName(setMenuItemElmt, this._settings.menuDisabledClassName);
				}

				if (menuData[i].divide) {
					addClassName(setMenuItemElmt, this._settings.menuDivideClassName);
				}

				setMenuItemElmt.menuData = menuData[i];

				if (subMenuFlag && menuData[i].subMenu && menuData[i].subMenu.length > 0) {
					subGroupElmt = this._createMenuGroup(menuData[i].subMenu, this._settings.subMenuClassName);
				}

				addEvent(setMenuBtnElmt, 'click', btnAction);

				if (menuData[i].icon && typeof menuData[i].icon === 'string' && menuData[i].icon.match(/^[^<>]+$/)) {
					setIconElmt    = iconElmt.cloneNode();
					setIconImgElmt = iconImgElmt.cloneNode();

					setIconImgElmt.setAttribute('src', menuData[i].icon);

					setIconElmt.appendChild(setIconImgElmt);
					setMenuBtnElmt.appendChild(setIconElmt);
				}

				setMenuBtnElmt.appendChild(textNode);
				setMenuItemElmt.appendChild(setMenuBtnElmt);

				if (subGroupElmt) {
					addEvent(setMenuItemElmt, 'mouseenter', subMenuOpenAction);
					addEvent(setMenuItemElmt, 'mouseleave', subMenuCloseAction);
					addClassName(setMenuItemElmt, this._settings.subMenuCloseClassName);

					setMenuItemElmt.appendChild(subGroupElmt);
				}

				groupElmt.appendChild(setMenuItemElmt);
			}

			return groupElmt;
		},

		/*
		 * _menuBtnsAddEvent
		 */
		_menuBtnsAddEvent : function(selector, menuList) {
			var menuBtnElmts = document.querySelectorAll(selector);

			if (menuBtnElmts.length === 0) return;

			for (i = 0, len = menuBtnElmts.length; i < len; i++) {
				menuBtnElmts[i].menuData = menuList[i];

				addEvent(menuBtnElmts[i], 'click', menuList[i].action);

				if (!menuList[i].href) continue;

				addEvent(menuBtnElmts[i], 'click', function() {
					win.open(this.menuData.href, this.menuData.target);
				});
			}
		},

		/*
		 * _setMenuPosition
		 */
		_setMenuPosition : function(x, y) {
			if (!this._data.menuElement) return this;

			this._data.menuElement.style.top  = y + 'px';
			this._data.menuElement.style.left = x + 'px';
		},

		/*
		 * openMenu
		 */
		openMenu : function(callback) {
			if (!this._data.menuElement) return this;

			removeClassName(this._doc, this._settings.closeFlagClassName);
			removeClassName(this._data.menuElement, this._settings.closeClassName);
			addClassName(this._doc, this._settings.openFlagClassName);
			addClassName(this._data.menuElement, this._settings.openClassName);

			this._data.menuElement.setAttribute('tabindex', 0);
			this._data.menuElement.focus();

			this._data.status = 'opened';

			if (typeof callback === 'function') {
				callback.apply(this, [this._data]);
			}

			if (arguments.callee.caller.length === 0) {
				this._data.callOutside = true;
			} else {
				this._data.callOutside = false;
			}

			return this;
		},

		/*
		 * closeMenu
		 */
		closeMenu : function(callback) {
			if (!this._data.menuElement) return this;

			removeClassName(this._doc, this._settings.openFlagClassName);
			removeClassName(this._data.menuElement, this._settings.openClassName);
			addClassName(this._doc, this._settings.closeFlagClassName);
			addClassName(this._data.menuElement, this._settings.closeClassName);

			this._data.menuElement.removeAttribute('tabindex');

			this._data.status = 'closed';

			if (typeof callback === 'function') {
				callback.apply(this, [this._data]);
			}

			if (arguments.callee.caller.length === 0) {
				this._data.callOutside = true;
			} else {
				this._data.callOutside = false;
			}

			return this;
		},

		/*
		 * toggleMenu
		 */
		toggleMenu : function(mode, callback) {
			if (!this._data.menuElement) return this;

			if (mode) {
				if (mode === 'open') {
					this.openMenu(callback);
				} else if (mode === 'close') {
					this.closeMenu(callback);
				}
			} else {
				if (this._data.status === 'opened') {
					this.openMenu(callback);
				} else if (this._data.status === 'opened') {
					this.closeMenu(callback);
				}
			}

			if (arguments.callee.caller.length === 0) {
				this._data.callOutside = true;
			} else {
				this._data.callOutside = false;
			}

			return this;
		},

		/*
		 * disable
		 */
		disable : function(mode, menuIndex, subMenuIndex) {
			if (!this._data.menuElement) return this;

			if (typeof mode !== 'boolean') return this;

			return this;
		},

		/*
		 * getStatus
		 */
		getStatus : function() {
			if (!this._data.menuElement) return this;
			return this._data.status;
		},

		/*
		 * destroy
		 */
		destroy : function() {
			var element = this._data.element;

			if (!this._data.menuElement && !element) return this;

			if (element !== win && element.length) {
				for (var i = 0, len = element.length; i < len; i++) {
					removeEvent(element[i], 'contextmenu', menuHandler);
				}
			} else {
				removeEvent(element, 'contextmenu', menuHandler);
			}

			return this;
		}
	};

	var arrayMerge = function() {
		if (arguments.length === 0) return false;

		var i, len, key, result = [];

		for (i = 0, len = arguments.length;i < len; i++) {
			if (typeof arguments[i] !== 'object') continue;

			for (key in arguments[i]) {
				if (isFinite(key)) {
					result.push(arguments[i][key]);
				} else {
					if (typeof result.length === 'number') result = {};
					result[key] = arguments[i][key];
				}
			}
		}

		return result;
	};

	var addEvent = (function() {
		if (win.addEventListener) {
			return function(element, type, handler) {
				element.addEventListener(type, handler, false);
				return element;
			};
		} else if (win.attachEvent) {
			return function(element, type, handler) {
				element.attachEvent('on' + type, handler);
				return element;
			};
		} else {
			return function(element) {
				return element;
			};
		}
	})();

	var removeEvent = (function() {
		if (win.removeEventListener) {
			return function(element, type, handler) {
				element.removeEventListener(type, handler, false);
				return element;
			};
		} else if (win.detachEvent) {
			return function(element, type, handler) {
				element.detachEvent('on' + type, handler);
				return element;
			};
		} else {
			return function(element) {
				return element;
			};
		}
	})();

	if (!document.querySelectorAll) {
		document.querySelectorAll = function(selectors) {
			var style = document.createElement('style'), elements = [], element;

			document.documentElement.firstChild.appendChild(style);
			document._qsa = [];

			style.styleSheet.cssText = selectors + '{x-qsa:expression(document._qsa && document._qsa.push(this))}';
			win.scrollBy(0, 0);
			style.parentNode.removeChild(style);

			while (document._qsa.length) {
				element = document._qsa.shift();
				element.style.removeAttribute('x-qsa');
				elements.push(element);
			}

			document._qsa = null;

			return elements;
		};
	}

	if (!document.querySelector) {
		document.querySelector = function(selectors) {
			var elements = document.querySelectorAll(selectors);
			return (elements.length) ? elements[0] : null;
		};
	}

	var inArray = function(searchValue, arrayData) {
		if (typeof searchValue === 'undefined' || typeof arrayData !== 'object') return -1;

		for (var key in arrayData) {
			if (arrayData[key] !== searchValue) continue;

			return key;
		}

		return -1;
	};

	var addClassName = function(element, classNameValue) {
		if (!element || typeof element.className === 'undefined' || typeof classNameValue !== 'string') return;

		var classNames = element.className.replace(/^\s+|\s+$/g, '').split(' ');

		if (classNames.toString() === '') {
			classNames = [];
		}

		if (inArray(classNameValue, classNames) > -1) return;

		classNames.push(classNameValue);

		element.className = classNames.join(' ');

		return element;
	};

	var removeClassName = function(element, classNameValue) {
		if (!element || !element.className || typeof classNameValue !== 'string') return;

		var classNames   = element.className.replace(/^\s+|\s+$/g, '').split(' '),
			hasClassName = false;

		if (inArray(classNameValue, classNames) === -1) return;

		if (classNames.toString() === '') {
			classNames = [];
		}

		for (var i = 0, len = classNames.length; i < len; i++) {
			if (classNames[i] !== classNameValue) continue;

			classNames.splice(i, 1);
			hasClassName = true;
			break;
		}

		if (hasClassName) {
			element.className = classNames.join(' ');
		}

		return element;
	};

	var belongsElement = function(targetElement, fromElement) {
		if (!targetElement || !fromElement) return;

		var parentElement = targetElement;

		while (parentElement.tagName.toLowerCase() !== 'html') {
			parentElement = parentElement.parentNode;

			if (parentElement === fromElement) return true;
		}

		return false;
	};

	global.ContextMenu = ContextMenu;
})(this || window);