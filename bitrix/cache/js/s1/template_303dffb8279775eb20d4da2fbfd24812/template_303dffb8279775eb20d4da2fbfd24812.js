
; /* Start:"a:4:{s:4:"full";s:59:"/bitrix/templates/eshop_adapt_blue/script.js?14338396071122";s:6:"source";s:44:"/bitrix/templates/eshop_adapt_blue/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
function eshopOpenNativeMenu()
{
	var native_menu = BX("bx_native_menu"),
		is_menu_active = BX.hasClass(native_menu, "active"),
		topHeight,
		easing;

	if (is_menu_active)
	{
		BX.removeClass(native_menu, "active");
		BX.removeClass(BX('bx_menu_bg'), "active");
		BX("bx_eshop_wrap").style.position = "";
		BX("bx_eshop_wrap").style.top = "";
		BX("bx_eshop_wrap").style.overflow = "";
	}
	else
	{
		BX.addClass(native_menu, "active");
		BX.addClass(BX('bx_menu_bg'), "active");
		topHeight = document.body.scrollTop;
		BX("bx_eshop_wrap").style.position = "fixed";
		BX("bx_eshop_wrap").style.top = -topHeight+"px";
		BX("bx_eshop_wrap").style.overflow = "hidden";
	}

	easing = new BX.easing({
		duration : 300,
		start : { left : (is_menu_active ? 0 : -100) },
		finish : { left : (is_menu_active ? -100 : 0) },
		transition : BX.easing.transitions.quart,
		step : function(state){
			native_menu.style.left = state.left + "%";
		}
	});
	easing.animate();
}

BX.bind(window, 'resize',
	function() {
		if (window.innerWidth >= 640 && BX.hasClass(BX("bx_native_menu"), "active"))
		{
			eshopOpenNativeMenu();
		}
	}
);
/* End */
;
; /* Start:"a:4:{s:4:"full";s:93:"/bitrix/components/bitrix/sale.basket.basket.line/templates/.default/script.js?14338395724656";s:6:"source";s:78:"/bitrix/components/bitrix/sale.basket.basket.line/templates/.default/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
'use strict';

function BitrixSmallCart(){}

BitrixSmallCart.prototype = {

	activate: function ()
	{
		this.cartElement = BX(this.cartId);
		this.fixedPosition = this.arParams.POSITION_FIXED == 'Y';
		if (this.fixedPosition)
		{
			this.cartClosed = true;
			this.maxHeight = false;
			this.itemRemoved = false;
			this.verticalPosition = this.arParams.POSITION_VERTICAL;
			this.horizontalPosition = this.arParams.POSITION_HORIZONTAL;
			this.topPanelElement = BX("bx-panel");

			this.fixAfterRender(); // TODO onready
			this.fixAfterRenderClosure = this.closure('fixAfterRender');

			var fixCartClosure = this.closure('fixCart');
			this.fixCartClosure = fixCartClosure;

			if (this.topPanelElement && this.verticalPosition == 'top')
				BX.addCustomEvent(window, 'onTopPanelCollapse', fixCartClosure);

			var resizeTimer = null;
			BX.bind(window, 'resize', function() {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(fixCartClosure, 200);
			});
		}
		this.setCartBodyClosure = this.closure('setCartBody');
		BX.addCustomEvent(window, 'OnBasketChange', this.closure('refreshCart', {}));
	},

	fixAfterRender: function ()
	{
		this.statusElement = BX(this.cartId + 'status');
		if (this.statusElement)
		{
			if (this.cartClosed)
				this.statusElement.innerHTML = this.openMessage;
			else
				this.statusElement.innerHTML = this.closeMessage;
		}
		this.productsElement = BX(this.cartId + 'products');
		this.fixCart();
	},

	closure: function (fname, data)
	{
		var obj = this;
		return data
			? function(){obj[fname](data)}
			: function(arg1){obj[fname](arg1)};
	},

	toggleOpenCloseCart: function ()
	{
		if (this.cartClosed)
		{
			BX.removeClass(this.cartElement, 'close');
			this.statusElement.innerHTML = this.closeMessage;
			this.cartClosed = false;
		}
		else // Opened
		{
			BX.addClass(this.cartElement, 'close');
			this.statusElement.innerHTML = this.openMessage;
			this.cartClosed = true;
		}
		setTimeout(this.fixCartClosure, 100);
	},

	setVerticalCenter: function(windowHeight)
	{
		var top = windowHeight/2 - (this.cartElement.offsetHeight/2);
		if (top < 5)
			top = 5;
		this.cartElement.style.top = top + 'px';
	},

	fixCart: function()
	{
		// set horizontal center
		if (this.horizontalPosition == 'hcenter')
		{
			var windowWidth = 'innerWidth' in window
				? window.innerWidth
				: document.documentElement.offsetWidth;
			var left = windowWidth/2 - (this.cartElement.offsetWidth/2);
			if (left < 5)
				left = 5;
			this.cartElement.style.left = left + 'px';
		}

		var windowHeight = 'innerHeight' in window
			? window.innerHeight
			: document.documentElement.offsetHeight;

		// set vertical position
		switch (this.verticalPosition) {
			case 'top':
				if (this.topPanelElement)
					this.cartElement.style.top = this.topPanelElement.offsetHeight + 5 + 'px';
				break;
			case 'vcenter':
				this.setVerticalCenter(windowHeight);
				break;
		}

		// toggle max height
		if (this.productsElement)
		{
			if (this.cartClosed)
			{
				if (this.maxHeight)
				{
					BX.removeClass(this.cartElement, 'max_height');
					this.maxHeight = false;
				}
			}
			else // Opened
			{
				if (this.maxHeight)
				{
					if (this.productsElement.scrollHeight == this.productsElement.clientHeight)
					{
						BX.removeClass(this.cartElement, 'max_height');
						this.maxHeight = false;
					}
				}
				else
				{
					if (this.verticalPosition == 'top' || this.verticalPosition == 'vcenter')
					{
						if (this.cartElement.offsetTop + this.cartElement.offsetHeight >= windowHeight)
						{
							BX.addClass(this.cartElement, 'max_height');
							this.maxHeight = true;
						}
					}
					else
					{
						if (this.cartElement.offsetHeight >= windowHeight)
						{
							BX.addClass(this.cartElement, 'max_height');
							this.maxHeight = true;
						}
					}
				}
			}

			if (this.verticalPosition == 'vcenter')
				this.setVerticalCenter(windowHeight);
		}
	},

	refreshCart: function (data)
	{
		if (this.itemRemoved)
		{
			this.itemRemoved = false;
			return;
		}
		data.sessid = BX.bitrix_sessid();
		data.siteId = this.siteId;
		data.templateName = this.templateName;
		data.arParams = this.arParams;
		BX.ajax({
			url: this.ajaxPath,
			method: 'POST',
			dataType: 'html',
			data: data,
			onsuccess: this.setCartBodyClosure
		});
	},

	setCartBody: function (result)
	{
		if (this.cartElement)
			this.cartElement.innerHTML = result;
		if (this.fixedPosition)
			setTimeout(this.fixAfterRenderClosure, 100);
	},

	removeItemFromCart: function (id)
	{
		this.refreshCart ({sbblRemoveItemFromCart: id});
		this.itemRemoved = true;
		BX.onCustomEvent('OnBasketChange');
	}
};

/* End */
;
; /* Start:"a:4:{s:4:"full";s:67:"/bitrix/components/bitrix/search.title/script.min.js?14339373296196";s:6:"source";s:48:"/bitrix/components/bitrix/search.title/script.js";s:3:"min";s:52:"/bitrix/components/bitrix/search.title/script.min.js";s:3:"map";s:52:"/bitrix/components/bitrix/search.title/script.map.js";}"*/
function JCTitleSearch(t){var e=this;this.arParams={AJAX_PAGE:t.AJAX_PAGE,CONTAINER_ID:t.CONTAINER_ID,INPUT_ID:t.INPUT_ID,MIN_QUERY_LEN:parseInt(t.MIN_QUERY_LEN)};if(t.WAIT_IMAGE)this.arParams.WAIT_IMAGE=t.WAIT_IMAGE;if(t.MIN_QUERY_LEN<=0)t.MIN_QUERY_LEN=1;this.cache=[];this.cache_key=null;this.startText="";this.running=false;this.currentRow=-1;this.RESULT=null;this.CONTAINER=null;this.INPUT=null;this.WAIT=null;this.ShowResult=function(t){if(BX.type.isString(t)){e.RESULT.innerHTML=t}e.RESULT.style.display=e.RESULT.innerHTML!==""?"block":"none";var s=e.adjustResultNode();var i;var r;var n=BX.findChild(e.RESULT,{tag:"table","class":"title-search-result"},true);if(n){r=BX.findChild(n,{tag:"th"},true)}if(r){var a=BX.pos(n);a.width=a.right-a.left;var l=BX.pos(r);l.width=l.right-l.left;r.style.width=l.width+"px";e.RESULT.style.width=s.width+l.width+"px";e.RESULT.style.left=s.left-l.width-1+"px";if(a.width-l.width>s.width)e.RESULT.style.width=s.width+l.width-1+"px";a=BX.pos(n);i=BX.pos(e.RESULT);if(i.right>a.right){e.RESULT.style.width=a.right-a.left+"px"}}var o;if(n)o=BX.findChild(e.RESULT,{"class":"title-search-fader"},true);if(o&&r){i=BX.pos(e.RESULT);o.style.left=i.right-i.left-18+"px";o.style.width=18+"px";o.style.top=0+"px";o.style.height=i.bottom-i.top+"px";o.style.display="block"}};this.onKeyPress=function(t){var s=BX.findChild(e.RESULT,{tag:"table","class":"title-search-result"},true);if(!s)return false;var i;var r=s.rows.length;switch(t){case 27:e.RESULT.style.display="none";e.currentRow=-1;e.UnSelectAll();return true;case 40:if(e.RESULT.style.display=="none")e.RESULT.style.display="block";var n=-1;for(i=0;i<r;i++){if(!BX.findChild(s.rows[i],{"class":"title-search-separator"},true)){if(n==-1)n=i;if(e.currentRow<i){e.currentRow=i;break}else if(s.rows[i].className=="title-search-selected"){s.rows[i].className=""}}}if(i==r&&e.currentRow!=i)e.currentRow=n;s.rows[e.currentRow].className="title-search-selected";return true;case 38:if(e.RESULT.style.display=="none")e.RESULT.style.display="block";var a=-1;for(i=r-1;i>=0;i--){if(!BX.findChild(s.rows[i],{"class":"title-search-separator"},true)){if(a==-1)a=i;if(e.currentRow>i){e.currentRow=i;break}else if(s.rows[i].className=="title-search-selected"){s.rows[i].className=""}}}if(i<0&&e.currentRow!=i)e.currentRow=a;s.rows[e.currentRow].className="title-search-selected";return true;case 13:if(e.RESULT.style.display=="block"){for(i=0;i<r;i++){if(e.currentRow==i){if(!BX.findChild(s.rows[i],{"class":"title-search-separator"},true)){var l=BX.findChild(s.rows[i],{tag:"a"},true);if(l){window.location=l.href;return true}}}}}return false}return false};this.onTimeout=function(){e.onChange(function(){setTimeout(e.onTimeout,500)})};this.onChange=function(t){if(e.running)return;e.running=true;if(e.INPUT.value!=e.oldValue&&e.INPUT.value!=e.startText){e.oldValue=e.INPUT.value;if(e.INPUT.value.length>=e.arParams.MIN_QUERY_LEN){e.cache_key=e.arParams.INPUT_ID+"|"+e.INPUT.value;if(e.cache[e.cache_key]==null){if(e.WAIT){var s=BX.pos(e.INPUT);var i=s.bottom-s.top-2;e.WAIT.style.top=s.top+1+"px";e.WAIT.style.height=i+"px";e.WAIT.style.width=i+"px";e.WAIT.style.left=s.right-i+2+"px";e.WAIT.style.display="block"}BX.ajax.post(e.arParams.AJAX_PAGE,{ajax_call:"y",INPUT_ID:e.arParams.INPUT_ID,q:e.INPUT.value,l:e.arParams.MIN_QUERY_LEN},function(s){e.cache[e.cache_key]=s;e.ShowResult(s);e.currentRow=-1;e.EnableMouseEvents();if(e.WAIT)e.WAIT.style.display="none";if(!!t)t();e.running=false});return}else{e.ShowResult(e.cache[e.cache_key]);e.currentRow=-1;e.EnableMouseEvents()}}else{e.RESULT.style.display="none";e.currentRow=-1;e.UnSelectAll()}}if(!!t)t();e.running=false};this.UnSelectAll=function(){var t=BX.findChild(e.RESULT,{tag:"table","class":"title-search-result"},true);if(t){var s=t.rows.length;for(var i=0;i<s;i++)t.rows[i].className=""}};this.EnableMouseEvents=function(){var t=BX.findChild(e.RESULT,{tag:"table","class":"title-search-result"},true);if(t){var s=t.rows.length;for(var i=0;i<s;i++)if(!BX.findChild(t.rows[i],{"class":"title-search-separator"},true)){t.rows[i].id="row_"+i;t.rows[i].onmouseover=function(t){if(e.currentRow!=this.id.substr(4)){e.UnSelectAll();this.className="title-search-selected";e.currentRow=this.id.substr(4)}};t.rows[i].onmouseout=function(t){this.className="";e.currentRow=-1}}}};this.onFocusLost=function(t){setTimeout(function(){e.RESULT.style.display="none"},250)};this.onFocusGain=function(){if(e.RESULT.innerHTML.length)e.ShowResult()};this.onKeyDown=function(t){if(!t)t=window.event;if(e.RESULT.style.display=="block"){if(e.onKeyPress(t.keyCode))return BX.PreventDefault(t)}};this.adjustResultNode=function(){var t;var s=BX.findParent(e.CONTAINER,BX.is_fixed);if(!!s){e.RESULT.style.position="fixed";e.RESULT.style.zIndex=BX.style(s,"z-index")+2;t=BX.pos(e.CONTAINER,true)}else{e.RESULT.style.position="absolute";t=BX.pos(e.CONTAINER)}t.width=t.right-t.left;e.RESULT.style.top=t.bottom+2+"px";e.RESULT.style.left=t.left+"px";e.RESULT.style.width=t.width+"px";return t};this._onContainerLayoutChange=function(){if(e.RESULT.style.display!=="none"&&e.RESULT.innerHTML!==""){e.adjustResultNode()}};this.Init=function(){this.CONTAINER=document.getElementById(this.arParams.CONTAINER_ID);BX.addCustomEvent(this.CONTAINER,"OnNodeLayoutChange",this._onContainerLayoutChange);this.RESULT=document.body.appendChild(document.createElement("DIV"));this.RESULT.className="title-search-result";this.INPUT=document.getElementById(this.arParams.INPUT_ID);this.startText=this.oldValue=this.INPUT.value;BX.bind(this.INPUT,"focus",function(){e.onFocusGain()});BX.bind(this.INPUT,"blur",function(){e.onFocusLost()});if(BX.browser.IsSafari()||BX.browser.IsIE())this.INPUT.onkeydown=this.onKeyDown;else this.INPUT.onkeypress=this.onKeyDown;if(this.arParams.WAIT_IMAGE){this.WAIT=document.body.appendChild(document.createElement("DIV"));this.WAIT.style.backgroundImage="url('"+this.arParams.WAIT_IMAGE+"')";if(!BX.browser.IsIE())this.WAIT.style.backgroundRepeat="none";this.WAIT.style.display="none";this.WAIT.style.position="absolute";this.WAIT.style.zIndex="1100"}BX.bind(this.INPUT,"bxchange",function(){e.onChange()})};BX.ready(function(){e.Init(t)})}
/* End */
;
; /* Start:"a:4:{s:4:"full";s:102:"/bitrix/templates/eshop_adapt_blue/components/bitrix/menu/catalog_horizontal2/script.js?14338396075394";s:6:"source";s:87:"/bitrix/templates/eshop_adapt_blue/components/bitrix/menu/catalog_horizontal2/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function(window) {

	if (!window.BX || BX.CatalogMenu)
		return;

	BX.CatalogMenu = {
		items : {},
		idCnt : 1,
		currentItem : null,
		overItem : null,
		outItem : null,
		timeoutOver : null,
		timeoutOut : null,

		getItem : function(item)
		{
			if (!BX.type.isDomNode(item))
				return null;

			var id = !item.id || !BX.type.isNotEmptyString(item.id) ? (item.id = "menu-item-" + this.idCnt++) : item.id;

			if (!this.items[id])
				this.items[id] = new CatalogMenuItem(item);

			return this.items[id];
		},

		itemOver : function(item)
		{
			var menuItem = this.getItem(item);
			if (!menuItem)
				return;

			if (this.outItem == menuItem)
			{
				clearTimeout(menuItem.timeoutOut);
			}

			this.overItem = menuItem;

			if (menuItem.timeoutOver)
			{
				clearTimeout(menuItem.timeoutOver);
			}

			menuItem.timeoutOver = setTimeout(function() {
				if (BX.CatalogMenu.overItem == menuItem)
				{
					menuItem.itemOver();
				}

			}, 100);
		},

		itemOut : function(item)
		{
			var menuItem = this.getItem(item);
			if (!menuItem)
				return;

			this.outItem = menuItem;

			if (menuItem.timeoutOut)
			{
				clearTimeout(menuItem.timeoutOut);
			}

			menuItem.timeoutOut = setTimeout(function() {

				if (menuItem != BX.CatalogMenu.overItem)
				{
					menuItem.itemOut();
				}
				if (menuItem == BX.CatalogMenu.outItem)
				{
					menuItem.itemOut();
				}

			}, 100);
		}
	};

	var CatalogMenuItem = function(item)
	{
		this.element = item;
		this.popup = BX.findChild(item, { className: "bx_children_container" }, false, false);
		this.isLastItem = BX.lastChild(this.element.parentNode) == this.element;
	};

	CatalogMenuItem.prototype.itemOver = function()
	{
		if (!BX.hasClass(this.element, "hover"))
		{
			BX.addClass(this.element, "hover");
			this.alignPopup();
		}
	};

	CatalogMenuItem.prototype.itemOut = function()
	{
		BX.removeClass(this.element, "hover");
	};

	CatalogMenuItem.prototype.alignPopup = function()
	{
		if (!this.popup)
			return;

		this.popup.style.cssText = "";

		var ulContainer = this.element.parentNode;
		var offsetRightPopup = this.popup.offsetLeft + this.popup.offsetWidth;
		var offsetRightMenu = ulContainer.offsetLeft + ulContainer.offsetWidth;

		if (offsetRightPopup >= offsetRightMenu)
		{
			this.popup.style.right = /*this.isLastItem ? "0px" :*/ "0";
		}
	};
})(window);

BX.namespace("BX.Main.Menu");
BX.Main.Menu.CatalogHorizontal = (function()
{
	var CatalogHorizontal = function(menuBlockId)
	{
		this.catalogMenuFirstWidth = 0;
		this.menuBlockId = menuBlockId;
		this.catalogMenuFirstWidth = this.resizeMenu(this.menuBlockId) + 20;

		if (this.catalogMenuFirstWidth > 640)
			this.setAlign();
		else
			this.setPadding();

		this.resizeMenu();

		BX.bind(window, "resize", BX.proxy(this.resizeMenu, this));
	};

	CatalogHorizontal.prototype.resizeMenu = function()
	{
		var widthSum = 0; // sum of width for all li
		var wpa;

		var firstLevelLi = BX.findChildren(BX(this.menuBlockId), {className : "bx_hma_one_lvl"}, true);

		if (firstLevelLi)
		{
			for(var i = 0; i < firstLevelLi.length; i++)
			{
				wpa = BX.firstChild(firstLevelLi[i]).clientWidth;
				widthSum += wpa;
			}

			if((widthSum+20) <= this.catalogMenuFirstWidth)
				BX.addClass(BX(this.menuBlockId), "small");   //adaptive
			else
				BX.removeClass(BX(this.menuBlockId), "small");
		}

		return widthSum;
	};

	CatalogHorizontal.prototype.setAlign = function()
	{
		var firstLevelLi = BX.findChildren(BX(this.menuBlockId), {className : "bx_hma_one_lvl"}, true);
		var widthSum = 0;

		if (firstLevelLi)
		{
			for(var i = 0; i < firstLevelLi.length; i++)
			{
				firstLevelLi[i].removeAttribute("style");
				var wp = firstLevelLi[i].clientWidth;
				widthSum += wp;
			}

			var coefWidth = widthSum/100;

			var numFirstLevelLi = firstLevelLi.length;
			var percentWidth = 0;
			for(i = 0; i < numFirstLevelLi; i++)
			{
				wp = firstLevelLi[i].clientWidth/coefWidth;
				percentWidth += wp;
				if (i == numFirstLevelLi-1)
				{
					if (percentWidth > 100)
						wp -= percentWidth - 100;
					else if (percentWidth < 100)
						wp += 100 - percentWidth;
				}
				firstLevelLi[i].style.width = wp + "%";
			}
		}
	};

	CatalogHorizontal.prototype.setPadding = function()
	{
		var firstLevelLi = BX.findChildren(BX(this.menuBlockId), {className : "bx_hma_one_lvl"}, true);
		if (firstLevelLi)
		{
			for(var i = 0; i < firstLevelLi.length; i++)
			{
				BX.firstChild(firstLevelLi[i]).style.padding = "19px 10px";
			}
		}
	};

	CatalogHorizontal.prototype.changeSectionPicure = function(element)
	{
		var descrSpan = BX.nextSibling(element);
		var curDescr = descrSpan.innerHTML || '';
		if (descrSpan)
		{
			var curImgWrapObj = BX.nextSibling(descrSpan);
			var curImgObj = BX.clone(BX.firstChild(curImgWrapObj));
		}
		var parentObj = BX.hasClass(element, 'bx_hma_one_lvl') ? element : BX.findParent(element, {className:'bx_hma_one_lvl'});
		var sectionImgObj = BX.findChild(parentObj, {className:'bx_section_picture'}, true, false);
		sectionImgObj.innerHTML = "";
		if (curImgObj)
		{
			sectionImgObj.appendChild(curImgObj);
		}
		var sectionDescrObj = BX.findChild(parentObj, {className:'bx_section_description'}, true, false);
		sectionDescrObj.innerHTML = curDescr;
		BX.previousSibling(sectionDescrObj).innerHTML = element.innerHTML;
		sectionImgObj.parentNode.href = element.href;
	};

	return CatalogHorizontal;
})();



/* End */
;
; /* Start:"a:4:{s:4:"full";s:89:"/bitrix/components/bitrix/catalog.top/templates/.default/banner/script.js?143383957337567";s:6:"source";s:73:"/bitrix/components/bitrix/catalog.top/templates/.default/banner/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function (window) {

	if (!!window.JCCatalogTopBanner)
		return;

	var BasketButton = function(params)
	{
		BasketButton.superclass.constructor.apply(this, arguments);
		this.nameNode = BX.create('span', {
			props: { className : 'bx_bt_button big', id : this.id },
			style: { position: 'static', top: '0', right: '0', marginTop: '0' },
			text: params.text
		});
		this.buttonNode = BX.create('span', {
			attrs: { className: params.ownerClass },
			style: { marginTop: '0', overflow: '', paddingTop: '0', position: 'static' },
			children: [this.nameNode],
			events: this.contextEvents
		});
		if (BX.browser.IsIE())
		{
			this.buttonNode.setAttribute("hideFocus", "hidefocus");
		}
	};
	BX.extend(BasketButton, BX.PopupWindowButton);

	window.JCCatalogTopBanner = function (arParams)
	{
		this.productType = 0;
		this.showQuantity = true;
		this.showAbsent = true;
		this.secondPict = false;
		this.showOldPrice = false;
		this.showPercent = false;
		this.showSkuProps = false;
		this.visual = {
			ID: '',
			PICT_ID: '',
			SECOND_PICT_ID: '',
			QUANTITY_ID: '',
			QUANTITY_UP_ID: '',
			QUANTITY_DOWN_ID: '',
			PRICE_ID: '',
			DSC_PERC: '',
			SECOND_DSC_PERC: '',
			DISPLAY_PROP_DIV: ''
		};
		this.product = {
			checkQuantity: false,
			maxQuantity: 0,
			stepQuantity: 1,
			isDblQuantity: false,
			canBuy: true,
			canSubscription: true,
			name: '',
			pict: {},
			id: 0,
			addUrl: '',
			buyUrl: ''
		};
		this.basketData = {
			useProps: false,
			emptyProps: false,
			quantity: 'quantity',
			props: 'prop',
			basketUrl: ''
		};

		this.defaultPict = {
			pict: null,
			secondPict: null
		};

		this.checkQuantity = false;
		this.maxQuantity = 0;
		this.stepQuantity = 1;
		this.isDblQuantity = false;
		this.canBuy = true;
		this.canSubscription = true;
		this.precision = 6;
		this.precisionFactor = Math.pow(10,this.precision);

		this.offers = [];
		this.offerNum = 0;
		this.treeProps = [];
		this.obTreeRows = [];
		this.showCount = [];
		this.showStart = [];
		this.selectedValues = {};

		this.obProduct = null;
		this.obQuantity = null;
		this.obQuantityUp = null;
		this.obQuantityDown = null;
		this.obPict = null;
		this.obSecondPict = null;
		this.obPrice = null;
		this.obTree = null;
		this.obBuyBtn = null;
		this.obDscPerc = null;
		this.obSecondDscPerc = null;
		this.obSkuProps = null;
		this.obMeasure = null;

		this.errorCode = 0;

		if ('object' === typeof arParams)
		{
			this.productType = parseInt(arParams.PRODUCT_TYPE);
			this.showQuantity = arParams.SHOW_QUANTITY;
			this.showAbsent = arParams.SHOW_ABSENT;
			if (!!arParams.SECOND_PICT)
				this.secondPict = true;
			if (!!arParams.SHOW_OLD_PRICE)
				this.showOldPrice = true;
			if (!!arParams.SHOW_DISCOUNT_PERCENT)
				this.showPercent = true;
			if (!!arParams.SHOW_SKU_PROPS)
				this.showSkuProps = true;
			this.visual = arParams.VISUAL;
			switch (this.productType)
			{
				case 1://product
				case 2://set
					if (!!arParams.PRODUCT && 'object' == typeof(arParams.PRODUCT))
					{
						if (this.showQuantity)
						{
							this.product.checkQuantity = arParams.PRODUCT.CHECK_QUANTITY;
							this.product.isDblQuantity = arParams.PRODUCT.QUANTITY_FLOAT;
							if (this.product.checkQuantity)
								this.product.maxQuantity = (this.product.isDblQuantity
										? parseFloat(arParams.PRODUCT.MAX_QUANTITY)
										: parseInt(arParams.PRODUCT.MAX_QUANTITY)
								);
							this.product.stepQuantity = (this.product.isDblQuantity
								? parseFloat(arParams.PRODUCT.STEP_QUANTITY)
								: parseInt(arParams.PRODUCT.STEP_QUANTITY)
							);

							this.checkQuantity = this.product.checkQuantity;
							this.isDblQuantity = this.product.isDblQuantity;
							this.maxQuantity = this.product.maxQuantity;
							this.stepQuantity = this.product.stepQuantity;
							if (this.isDblQuantity)
							{
								this.stepQuantity = Math.round(this.stepQuantity*this.precisionFactor)/this.precisionFactor;
							}
						}
						this.product.canBuy = arParams.PRODUCT.CAN_BUY;
						this.product.canSubscription = arParams.PRODUCT.SUBSCRIPTION;

						this.canBuy = this.product.canBuy;
						this.canSubscription = this.product.canSubscription;

						this.product.name = arParams.PRODUCT.NAME;
						this.product.pict = arParams.PRODUCT.PICT;
						this.product.id = arParams.PRODUCT.ID;
						if (!!arParams.PRODUCT.ADD_URL)
							this.product.addUrl = arParams.PRODUCT.ADD_URL;
						if (!!arParams.PRODUCT.BUY_URL)
							this.product.buyUrl = arParams.PRODUCT.BUY_URL;
						if (!!arParams.BASKET && 'object' === typeof(arParams.BASKET))
						{
							this.basketData.useProps = !!arParams.BASKET.ADD_PROPS;
							this.basketData.emptyProps = !!arParams.BASKET.EMPTY_PROPS;
						}
					}
					else
					{
						this.errorCode = -1;
					}
					break;
				case 3://sku
					if (!!arParams.OFFERS && BX.type.isArray(arParams.OFFERS))
					{
						this.product.name = arParams.PRODUCT.NAME;
						this.product.id = arParams.PRODUCT.ID;
						this.offers = arParams.OFFERS;
						this.offerNum = 0;
						if (!!arParams.OFFER_SELECTED)
							this.offerNum = parseInt(arParams.OFFER_SELECTED);
						if (isNaN(this.offerNum))
							this.offerNum = 0;
						if (!!arParams.TREE_PROPS)
							this.treeProps = arParams.TREE_PROPS;
						if (!!arParams.DEFAULT_PICTURE)
						{
							this.defaultPict.pict = arParams.DEFAULT_PICTURE.PICTURE;
							this.defaultPict.secondPict = arParams.DEFAULT_PICTURE.PICTURE_SECOND;
						}
					}
					else
					{
						this.errorCode = -1;
					}
					break;
				default:
					this.errorCode = -1;
			}
			if (!!arParams.BASKET && 'object' == typeof(arParams.BASKET))
			{
				if (!!arParams.BASKET.QUANTITY)
					this.basketData.quantity = arParams.BASKET.QUANTITY;
				if (!!arParams.BASKET.PROPS)
					this.basketData.props = arParams.BASKET.PROPS;
				if (!!arParams.BASKET.BASKET_URL)
				{
					this.basketData.basketUrl = arParams.BASKET.BASKET_URL;
				}
			}
		}

		if (0 === this.errorCode)
		{
			BX.ready(BX.delegate(this.Init,this));
		}
	};

	window.JCCatalogTopBanner.prototype.Init = function()
	{
		var i = 0;
		this.obProduct = BX(this.visual.ID);
		if (!this.obProduct)
			this.errorCode = -1;
		this.obPict = BX(this.visual.PICT_ID);
		if (!this.obPict)
			this.errorCode = -2;
		if (this.secondPict && !!this.visual.SECOND_PICT_ID)
		{
			this.obSecondPict = BX(this.visual.SECOND_PICT_ID);
		}
		this.obPrice = BX(this.visual.PRICE_ID);
		if (!this.obPrice)
			this.errorCode = -16;
		if (this.showQuantity && !!this.visual.QUANTITY_ID)
		{
			this.obQuantity = BX(this.visual.QUANTITY_ID);
			if (!!this.visual.QUANTITY_UP_ID)
			{
				this.obQuantityUp = BX(this.visual.QUANTITY_UP_ID);
			}
			if (!!this.visual.QUANTITY_DOWN_ID)
			{
				this.obQuantityDown = BX(this.visual.QUANTITY_DOWN_ID);
			}
		}
		if (3 === this.productType)
		{
			if (!!this.visual.TREE_ID)
			{
				this.obTree = BX(this.visual.TREE_ID);
				if (!this.obTree)
					this.errorCode = -256;
				var strPrefix = this.visual.TREE_ITEM_ID;
				for (i = 0; i < this.treeProps.length; i++)
				{
					this.obTreeRows[i] = {
						LEFT: BX(strPrefix+this.treeProps[i].ID+'_left'),
						RIGHT: BX(strPrefix+this.treeProps[i].ID+'_right'),
						LIST: BX(strPrefix+this.treeProps[i].ID+'_list'),
						CONT: BX(strPrefix+this.treeProps[i].ID+'_cont')
					};
					if (!this.obTreeRows[i].LEFT || !this.obTreeRows[i].RIGHT || !this.obTreeRows[i].LIST || !this.obTreeRows[i].CONT)
					{
						this.errorCode = -512;
						break;
					}
				}
			}
			if (!!this.visual.QUANTITY_MEASURE)
			{
				this.obMeasure = BX(this.visual.QUANTITY_MEASURE);
			}
		}
		if (!!this.visual.BUY_ID)
		{
			this.obBuyBtn = BX(this.visual.BUY_ID);
		}

		if (this.showPercent)
		{
			if (!!this.visual.DSC_PERC)
			{
				this.obDscPerc = BX(this.visual.DSC_PERC);
			}
			if (this.secondPict && !!this.visual.SECOND_DSC_PERC)
			{
				this.obSecondDscPerc = BX(this.visual.SECOND_DSC_PERC);
			}
		}

		if (this.showSkuProps)
		{
			if (!!this.visual.DISPLAY_PROP_DIV)
			{
				this.obSkuProps = BX(this.visual.DISPLAY_PROP_DIV);
			}
		}

		if (0 === this.errorCode)
		{
			if (this.showQuantity)
			{
				if (!!this.obQuantityUp)
				{
					BX.bind(this.obQuantityUp, 'click', BX.delegate(this.QuantityUp, this));
				}
				if (!!this.obQuantityDown)
				{
					BX.bind(this.obQuantityDown, 'click', BX.delegate(this.QuantityDown, this));
				}
				if (!!this.obQuantity)
				{
					BX.bind(this.obQuantity, 'change', BX.delegate(this.QuantityChange, this));
				}
			}
			switch (this.productType)
			{
				case 1://product
					break;
				case 3://sku
					var TreeItems = BX.findChildren(this.obTree, {tagName: 'li'}, true);
					if (!!TreeItems && 0 < TreeItems.length)
					{
						for (i = 0; i < TreeItems.length; i++)
						{
							BX.bind(TreeItems[i], 'click', BX.delegate(this.SelectOfferProp, this));
						}
					}
					for (i = 0; i < this.obTreeRows.length; i++)
					{
						BX.bind(this.obTreeRows[i].LEFT, 'click', BX.delegate(this.RowLeft, this));
						BX.bind(this.obTreeRows[i].RIGHT, 'click', BX.delegate(this.RowRight, this));
					}
					this.SetCurrent();
					break;
			}
			if (!!this.obBuyBtn)
			{
				BX.bind(this.obBuyBtn, 'click', BX.delegate(this.Basket, this));
			}
		}
	};

	window.JCCatalogTopBanner.prototype.QuantityUp = function()
	{
		var curValue = 0;
		var boolSet = true;
		if (0 === this.errorCode && this.showQuantity)
		{
			curValue = (
				this.isDblQuantity
				? parseFloat(this.obQuantity.value)
				: parseInt(this.obQuantity.value)
			);
			if (!isNaN(curValue))
			{
				curValue += this.stepQuantity;
				if (this.checkQuantity)
				{
					if (curValue > this.maxQuantity)
						boolSet = false;
				}
				if (boolSet)
				{
					if (this.isDblQuantity)
					{
						curValue = Math.round(curValue*this.precisionFactor)/this.precisionFactor;
					}
					this.obQuantity.value = curValue;
				}
			}
		}
	};

	window.JCCatalogTopBanner.prototype.QuantityDown = function()
	{
		var curValue = 0;
		var boolSet = true;
		if (0 === this.errorCode && this.showQuantity)
		{
			curValue = (
				this.isDblQuantity
				? parseFloat(this.obQuantity.value)
				: parseInt(this.obQuantity.value)
			);
			if (!isNaN(curValue))
			{
				curValue -= this.stepQuantity;
				if (curValue < this.stepQuantity)
					boolSet = false;
				if (boolSet)
				{
					if (this.isDblQuantity)
					{
						curValue = Math.round(curValue*this.precisionFactor)/this.precisionFactor;
					}
					this.obQuantity.value = curValue;
				}
			}
		}
	};

	window.JCCatalogTopBanner.prototype.QuantityChange = function()
	{
		var curValue = 0;
		var boolSet = true;
		if (0 === this.errorCode && this.showQuantity)
		{
			curValue = (
				this.isDblQuantity
				? parseFloat(this.obQuantity.value)
				: parseInt(this.obQuantity.value)
			);
			if (!isNaN(curValue))
			{
				if (this.checkQuantity)
				{
					if (curValue > this.maxQuantity)
					{
						boolSet = false;
						curValue = this.maxQuantity;
					}
					else if (curValue < this.stepQuantity)
					{
						boolSet = false;
						curValue = this.stepQuantity;
					}
				}
				if (!boolSet)
				{
					this.obQuantity.value = curValue;
				}
			}
			else
			{
				this.obQuantity.value = this.stepQuantity;
			}
		}
	};

	window.JCCatalogTopBanner.prototype.QuantitySet = function(index)
	{
		if (0 === this.errorCode)
		{
			this.canBuy = this.offers[index].CAN_BUY;
			if (this.showQuantity)
			{
				this.isDblQuantity = this.offers[index].QUANTITY_FLOAT;
				this.checkQuantity = this.offers[index].CHECK_QUANTITY;
				this.maxQuantity = (this.isDblQuantity
					? parseFloat(this.offers[index].MAX_QUANTITY)
					: parseInt(this.offers[index].MAX_QUANTITY)
				);
				this.stepQuantity = (this.isDblQuantity
					? parseFloat(this.offers[index].STEP_QUANTITY)
					: parseInt(this.offers[index].STEP_QUANTITY)
				);
				if (this.isDblQuantity)
				{
					this.stepQuantity = Math.round(this.stepQuantity*this.precisionFactor)/this.precisionFactor;
				}
				this.obQuantity.value = this.stepQuantity;
				if (!!this.obMeasure)
				{
					if (!!this.offers[index].MEASURE)
					{
						BX.adjust(this.obMeasure, { html : this.offers[index].MEASURE});
					}
					else
					{
						BX.adjust(this.obMeasure, { html : ''});
					}
				}
			}
		}
	};

	window.JCCatalogTopBanner.prototype.SelectOfferProp = function()
	{
		var i = 0;
		var value = '';
		var target = BX.proxy_context;
		if (!!target && target.hasAttribute('data-treevalue'))
		{
			var strTreeValue = target.getAttribute('data-treevalue');
			var arTreeItem = strTreeValue.split('_');
			if (this.SearchOfferPropIndex(arTreeItem[0], arTreeItem[1]))
			{
				var RowItems = BX.findChildren(target.parentNode, {tagName: 'li'}, false);
				if (!!RowItems && 0 < RowItems.length)
				{
					for (i = 0; i < RowItems.length; i++)
					{
						value = RowItems[i].getAttribute('data-onevalue');
						if (value == arTreeItem[1])
							BX.addClass(RowItems[i], 'bx_active');
						else
							BX.removeClass(RowItems[i], 'bx_active');
					}
				}
			}
		}
	};

	window.JCCatalogTopBanner.prototype.SearchOfferPropIndex = function(strPropID, strPropValue)
	{
		var strName = '';
		var arShowValues = null;
		var arCanBuyValues = [];
		var index = -1;
		for (var i = 0; i < this.treeProps.length; i++)
		{
			if (this.treeProps[i].ID == strPropID)
			{
				index = i;
				break;
			}
		}

		if (-1 < index)
		{
			var arFilter = {};
			for (i = 0; i < index; i++)
			{
				strName = 'PROP_'+this.treeProps[i].ID;
				arFilter[strName] = this.selectedValues[strName];
			}
			strName = 'PROP_'+this.treeProps[index].ID;
			arShowValues = this.GetRowValues(arFilter, strName);
			if (!arShowValues)
				return false;
			if (!BX.util.in_array(strPropValue, arShowValues))
				return false;
			arFilter[strName] = strPropValue;
			for (i = index+1; i < this.treeProps.length; i++)
			{
				strName = 'PROP_'+this.treeProps[i].ID;
				arShowValues = this.GetRowValues(arFilter, strName);
				if (!arShowValues)
					return false;
				if (this.showAbsent)
				{
					arCanBuyValues = [];
					var tmpFilter = [];
					tmpFilter = BX.clone(arFilter, true);
					for (var j = 0; j < arShowValues.length; j++)
					{
						tmpFilter[strName] = arShowValues[j];
						if (this.GetCanBuy(tmpFilter))
							arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
					}
				}
				else
				{
					arCanBuyValues = arShowValues;
				}
				if (!!this.selectedValues[strName] && BX.util.in_array(this.selectedValues[strName], arCanBuyValues))
					arFilter[strName] = this.selectedValues[strName];
				else
					arFilter[strName] = arCanBuyValues[0];
				this.UpdateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
			}
			this.selectedValues = arFilter;
			this.ChangeInfo();
		}
		return true;
	};

	window.JCCatalogTopBanner.prototype.RowLeft = function()
	{
		var target = BX.proxy_context;
		if (!!target && target.hasAttribute('data-treevalue'))
		{
			var strTreeValue = target.getAttribute('data-treevalue');
			var index = -1;
			for (var i = 0; i < this.treeProps.length; i++)
			{
				if (this.treeProps[i].ID == strTreeValue)
				{
					index = i;
					break;
				}
			}
			if (-1 < index && 5 < this.showCount[index])
			{
				if (0 > this.showStart[index])
				{
					this.showStart[index]++;
					BX.adjust(this.obTreeRows[index].LIST, { style: { marginLeft: this.showStart[index]*20+'%' }});
				}
			}
		}
	};

	window.JCCatalogTopBanner.prototype.RowRight = function()
	{
		var target = BX.proxy_context;
		if (!!target && target.hasAttribute('data-treevalue'))
		{
			var strTreeValue = target.getAttribute('data-treevalue');
			var index = -1;
			for (var i = 0; i < this.treeProps.length; i++)
			{
				if (this.treeProps[i].ID == strTreeValue)
				{
					index = i;
					break;
				}
			}
			if (-1 < index && 5 < this.showCount[index])
			{
				if ((5 - this.showStart[index]) < this.showCount[index])
				{
					this.showStart[index]--;
					BX.adjust(this.obTreeRows[index].LIST, { style: { marginLeft: this.showStart[index]*20+'%' }});
				}
			}
		}
	};

	window.JCCatalogTopBanner.prototype.UpdateRow = function(intNumber, activeID, showID, canBuyID)
	{
		var i = 0;
		var value;
		var countShow = 0;
		var strNewLen = '';
		if (-1 < intNumber && intNumber < this.obTreeRows.length)
		{
			var RowItems = BX.findChildren(this.obTreeRows[intNumber].LIST, {tagName: 'li'}, false);
			if (!!RowItems && 0 < RowItems.length)
			{
				countShow = showID.length;
				strNewLen = (5 < countShow ? (100/countShow)+'%' : '20%');
				var obData = {
					props: { className: '' },
					style: {
						width: strNewLen
					}
				};
				if ('PICT' == this.treeProps[intNumber].SHOW_MODE)
					obData.style.paddingTop = strNewLen;
				for (i = 0; i < RowItems.length; i++)
				{
					value = RowItems[i].getAttribute('data-onevalue');
					if (BX.util.in_array(value, canBuyID))
					{
						if (value == activeID)
							obData.props.className = 'bx_active';
						else
							obData.props.className = '';
					}
					else
					{
						if (value == activeID)
							obData.props.className = 'bx_active bx_missing';
						else
							obData.props.className = 'bx_missing';
					}
					if (BX.util.in_array(value, showID))
						obData.style.display = '';
					else
						obData.style.display = 'none';
					BX.adjust(RowItems[i], obData);
				}
				obData = {
					style: {
						width: (5 < countShow ? 20*countShow : 100)+'%',
						marginLeft: '0%'
					}
				};
				BX.adjust(this.obTreeRows[intNumber].LIST, obData);
				if ('PICT' == this.treeProps[intNumber].SHOW_MODE)
					BX.adjust(this.obTreeRows[intNumber].CONT, {props: {className: (5 < countShow ? 'bx_item_detail_scu full' : 'bx_item_detail_scu')}});
				else
					BX.adjust(this.obTreeRows[intNumber].CONT, {props: {className: (5 < countShow ? 'bx_item_detail_size full' : 'bx_item_detail_size')}});
				if (5 < countShow)
				{
					BX.adjust(this.obTreeRows[intNumber].LEFT, {style: {display: ''}});
					BX.adjust(this.obTreeRows[intNumber].RIGHT, {style: {display: ''}});
				}
				else
				{
					BX.adjust(this.obTreeRows[intNumber].LEFT, {style: {display: 'none'}});
					BX.adjust(this.obTreeRows[intNumber].RIGHT, {style: {display: 'none'}});
				}
				this.showCount[intNumber] = countShow;
				this.showStart[intNumber] = 0;
			}
		}
	};

	window.JCCatalogTopBanner.prototype.GetRowValues = function(arFilter, index)
	{
		var arValues = [];
		var boolSearch = false;
		var i = 0;
		if (0 === arFilter.length)
		{
			for (i = 0; i < this.offers.length; i++)
			{
				if (!BX.util.in_array(this.offers[i].TREE[index], arValues))
					arValues[arValues.length] = this.offers[i].TREE[index];
			}
			boolSearch = true;
		}
		else
		{
			for (i = 0; i < this.offers.length; i++)
			{
				var boolOneSearch = true;
				for (var j in arFilter)
				{
					if (arFilter[j] != this.offers[i].TREE[j])
					{
						boolOneSearch = false;
						break;
					}
				}
				if (boolOneSearch)
				{
					if (!BX.util.in_array(this.offers[i].TREE[index], arValues))
						arValues[arValues.length] = this.offers[i].TREE[index];
					boolSearch = true;
				}
			}
		}
		return (boolSearch ? arValues : false);
	};

	window.JCCatalogTopBanner.prototype.GetCanBuy = function(arFilter)
	{
		var boolSearch = false;
		for (var i = 0; i < this.offers.length; i++)
		{
			var boolOneSearch = true;
			for (var j in arFilter)
			{
				if (arFilter[j] != this.offers[i].TREE[j])
				{
					boolOneSearch = false;
					break;
				}
			}
			if (boolOneSearch)
			{
				if (this.offers[i].CAN_BUY)
				{
					boolSearch = true;
					break;
				}
			}
		}
		return boolSearch;
	};

	window.JCCatalogTopBanner.prototype.SetCurrent = function()
	{
		var arFilter = {};
		var arCanBuyValues = [];
		var current = this.offers[this.offerNum].TREE;
		for (var i = 0; i < this.treeProps.length; i++)
		{
			var strName = 'PROP_'+this.treeProps[i].ID;
			var arShowValues = this.GetRowValues(arFilter, strName);
			if (!arShowValues)
				break;
			if (BX.util.in_array(current[strName], arShowValues))
			{
				arFilter[strName] = current[strName];
			}
			else
			{
				arFilter[strName] = arShowValues[0];
				this.offerNum = 0;
			}
			if (this.showAbsent)
			{
				arCanBuyValues = [];
				var tmpFilter = [];
				tmpFilter = BX.clone(arFilter, true);
				for (var j = 0; j < arShowValues.length; j++)
				{
					tmpFilter[strName] = arShowValues[j];
					if (this.GetCanBuy(tmpFilter))
						arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
				}
			}
			else
			{
				arCanBuyValues = arShowValues;
			}
			this.UpdateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
		}
		this.selectedValues = arFilter;
		this.ChangeInfo();
	};

	window.JCCatalogTopBanner.prototype.ChangeInfo = function()
	{
		var index = -1;
		for (var i = 0; i < this.offers.length; i++)
		{
			var boolOneSearch = true;
			for (var j in this.selectedValues)
			{
				if (this.selectedValues[j] != this.offers[i].TREE[j])
				{
					boolOneSearch = false;
					break;
				}
			}
			if (boolOneSearch)
			{
				index = i;
				break;
			}
		}
		if (-1 < index)
		{
			if (!!this.obPict)
			{
				if (!!this.offers[index].PREVIEW_PICTURE)
					BX.adjust(this.obPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE.SRC+')'}});
				else
					BX.adjust(this.obPict, {style: {backgroundImage: 'url('+this.defaultPict.pict.SRC+')'}});
			}
			if (this.secondPict && !!this.obSecondPict)
			{
				if (!!this.offers[index].PREVIEW_PICTURE_SECOND)
					BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE_SECOND.SRC+')'}});
				else if (!!this.offers[index].PREVIEW_PICTURE.SRC)
					BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE.SRC+')'}});
				else if (!!this.defaultPict.secondPict)
					BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.defaultPict.secondPict.SRC+')'}});
				else
					BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.defaultPict.pict.SRC+')'}});
			}
			if (this.showSkuProps && !!this.obSkuProps)
			{
				if (0 === this.offers[index].DISPLAY_PROPERTIES.length)
				{
					BX.adjust(this.obSkuProps, {style: {display: 'none'}, html: ''});
				}
				else
				{
					BX.adjust(this.obSkuProps, {style: {display: ''}, html: this.offers[index].DISPLAY_PROPERTIES});
				}
			}
			if (!!this.obPrice)
			{
				var strPrice = this.offers[index].PRICE.PRINT_DISCOUNT_VALUE;
				if (this.showOldPrice && (this.offers[index].PRICE.DISCOUNT_VALUE != this.offers[index].PRICE.VALUE))
					strPrice += ' <span>'+this.offers[index].PRICE.PRINT_VALUE+'</span>';
				BX.adjust(this.obPrice, {html: strPrice});
				if (this.showPercent)
				{
					var obData = {};
					if (this.offers[index].PRICE.DISCOUNT_VALUE != this.offers[index].PRICE.VALUE)
					{
						obData = {
							style: {
								display: ''
							},
							html: this.offers[index].PRICE.DISCOUNT_DIFF_PERCENT
						};
					}
					else
					{
						obData = {
							style: {
								display: 'none'
							},
							html: ''
						};
					}
					if (!!this.obDscPerc)
						BX.adjust(this.obDscPerc, obData);
					if (!!this.obSecondDscPerc)
						BX.adjust(this.obSecondDscPerc, obData);
				}
			}
			this.offerNum = index;
			if (this.showQuantity)
			{
				this.QuantitySet(this.offerNum);
			}
		}
	};

	window.JCCatalogTopBanner.prototype.InitBasketUrl = function()
	{
		switch (this.productType)
		{
			case 1://product
			case 2://set
				this.basketUrl = this.product.addUrl;
				break;
			case 3://sku
				this.basketUrl = this.offers[this.offerNum].ADD_URL;
				break;
		}
		this.basketParams = {
			'ajax_basket': 'Y'
		};
		if (this.showQuantity)
		{
			this.basketParams[this.basketData.quantity] = this.obQuantity.value;
		}
	};

	window.JCCatalogTopBanner.prototype.FillBasketProps = function()
	{
		if (!this.visual.BASKET_PROP_DIV)
		{
			return;
		}
		var
			i = 0,
			propCollection = null,
			foundValues = false,
			obBasketProps = null;

		if (this.basketData.useProps && !this.basketData.emptyProps)
		{
			if (!!this.obPopupWin && !!this.obPopupWin.contentContainer)
			{
				obBasketProps = this.obPopupWin.contentContainer;
			}
		}
		else
		{
			obBasketProps = BX(this.visual.BASKET_PROP_DIV);
		}
		if (!obBasketProps)
		{
			return;
		}
		propCollection = obBasketProps.getElementsByTagName('select');
		if (!!propCollection && !!propCollection.length)
		{
			for (i = 0; i < propCollection.length; i++)
			{
				if (!propCollection[i].disabled)
				{
					switch(propCollection[i].type.toLowerCase())
					{
						case 'select-one':
							this.basketParams[propCollection[i].name] = propCollection[i].value;
							foundValues = true;
							break;
						default:
							break;
					}
				}
			}
		}
		propCollection = obBasketProps.getElementsByTagName('input');
		if (!!propCollection && !!propCollection.length)
		{
			for (i = 0; i < propCollection.length; i++)
			{
				if (!propCollection[i].disabled)
				{
					switch(propCollection[i].type.toLowerCase())
					{
						case 'hidden':
							this.basketParams[propCollection[i].name] = propCollection[i].value;
							foundValues = true;
							break;
						case 'radio':
							if (propCollection[i].checked)
							{
								this.basketParams[propCollection[i].name] = propCollection[i].value;
								foundValues = true;
							}
							break;
						default:
							break;
					}
				}
			}
		}
		if (!foundValues)
		{
			this.basketParams[this.basketData.props] = [];
			this.basketParams[this.basketData.props][0] = 0;
		}
	};

	window.JCCatalogTopBanner.prototype.SendToBasket = function()
	{
		if (!this.canBuy)
		{
			return;
		}
		this.InitBasketUrl();
		this.FillBasketProps();
		BX.ajax.loadJSON(
			this.basketUrl,
			this.basketParams,
			BX.delegate(this.BasketResult, this)
		);
	};

	window.JCCatalogTopBanner.prototype.Basket = function()
	{
		var contentBasketProps = '';
		if (!this.canBuy)
		{
			return;
		}
		switch (this.productType)
		{
			case 1://product
			case 2://set
				if (this.basketData.useProps && !this.basketData.emptyProps)
				{
					this.InitPopupWindow();
					this.obPopupWin.setTitleBar({
						content: BX.create('div', {
							style: { marginRight: '30px', whiteSpace: 'nowrap' },
							text: BX.message('TITLE_BASKET_PROPS')
						})
					});
					if (BX(this.visual.BASKET_PROP_DIV))
					{
						contentBasketProps = BX(this.visual.BASKET_PROP_DIV).innerHTML;
					}
					this.obPopupWin.setContent(contentBasketProps);
					this.obPopupWin.setButtons([
						new BasketButton({
							ownerClass: this.obProduct.parentNode.parentNode.className,
							text: BX.message('BTN_MESSAGE_SEND_PROPS'),
							events: {
								click: BX.delegate(this.SendToBasket, this)
							}
						})
					]);
					this.obPopupWin.show();
				}
				else
				{
					this.SendToBasket();
				}
				break;
			case 3://sku
				this.SendToBasket();
				break;
		}
	};

	window.JCCatalogTopBanner.prototype.BasketResult = function(arResult)
	{
		var strContent = '',
			strName = '',
			strPict = '',
			successful = true,
			buttons = [];

		if (!!this.obPopupWin)
		{
			this.obPopupWin.close();
		}
		if ('object' !== typeof arResult)
		{
			return false;
		}
		successful = ('OK' === arResult.STATUS);
		if (successful)
		{
			BX.onCustomEvent('OnBasketChange');
			strName = this.product.name;
			switch(this.productType)
			{
				case 1://
				case 2://
					strPict = this.product.pict.SRC;
					break;
				case 3:
					strPict = (!!this.offers[this.offerNum].PREVIEW_PICTURE ?
						this.offers[this.offerNum].PREVIEW_PICTURE.SRC :
						this.defaultPict.pict.SRC
						);
					break;
			}
			strContent = '<div style="width: 96%; margin: 10px 2%; text-align: center;"><img src="'+strPict+'" height="130"><p>'+strName+'</p></div>';
			buttons = [
				new BasketButton({
					ownerClass: this.obProduct.parentNode.parentNode.className,
					text: BX.message("BTN_MESSAGE_BASKET_REDIRECT"),
					events: {
						click: BX.delegate(function(){
							location.href = (!!this.basketData.basketUrl ? this.basketData.basketUrl : BX.message('BASKET_URL'));
						}, this)
					}
				})
			];
		}
		else
		{
			strContent = (!!arResult.MESSAGE ? arResult.MESSAGE : BX.message('BASKET_UNKNOWN_ERROR'));
			buttons = [
				new BasketButton({
					ownerClass: this.obProduct.parentNode.parentNode.className,
					text: BX.message('BTN_MESSAGE_CLOSE'),
					events: {
						click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
					}
				})
			];
		}
		this.InitPopupWindow();
		this.obPopupWin.setTitleBar({
			content: BX.create('div', {
				style: { marginRight: '30px', whiteSpace: 'nowrap' },
				text: (successful ? BX.message('TITLE_SUCCESSFUL') : BX.message('TITLE_ERROR'))
			})
		});
		this.obPopupWin.setContent(strContent);
		this.obPopupWin.setButtons(buttons);
		this.obPopupWin.show();
	};

	window.JCCatalogTopBanner.prototype.InitPopupWindow = function()
	{
		if (!!this.obPopupWin)
		{
			return;
		}
		this.obPopupWin = BX.PopupWindowManager.create('CatalogSectionBasket_'+this.visual.ID, null, {
			autoHide: false,
			offsetLeft: 0,
			offsetTop: 0,
			overlay : true,
			closeByEsc: true,
			titleBar: true,
			closeIcon: {top: '10px', right: '10px'}
		});
	};


if (!!window.JCCatalogTopBannerList)
	return;

window.JCCatalogTopBannerList = function (arParams)
{
	this.params = null;
	this.prevIndex = -1;
	this.currentIndex = 0;
	this.size = 0;
	this.rotate = false;
	this.rotateTimer = 30000;
	this.rotatePause = false;
	this.showPages = false;
	this.errorCode = 0;

	this.slider = {
		cont: null,
		row: null,
		items: null,
		arrows: null,
		left: null,
		right: null,
		pagination: null,
		pages: null
	};

	if (!arParams || 'object' != typeof(arParams))
	{
		this.errorCode = -1;
	}
	if (0 === this.errorCode)
	{
		this.params = arParams;
	}
	if (!!this.params.rotate)
		this.rotate = this.params.rotate;
	if (!!this.params.rotateTimer)
	{
		this.params.rotateTimer = parseInt(this.params.rotateTimer);
		if (!isNaN(this.params.rotateTimer) && 0 <= this.params.rotateTimer)
			this.rotateTimer = this.params.rotateTimer;
	}

	if (0 === this.errorCode)
	{
		BX.ready(BX.delegate(this.Init,this));
	}
};

window.JCCatalogTopBannerList.prototype.Init = function()
{
	if (0 > this.errorCode)
		return;

	var i = 0;
	if (!!this.params.cont)
	{
		this.slider.cont = BX(this.params.cont);
	}
	if (!!this.params.items && BX.type.isArray(this.params.items))
	{
		this.slider.items = [];
		for (i = 0; i < this.params.items.length; i++)
		{
			this.slider.items[this.slider.items.length] = BX(this.params.items[i]);
			this.slider.items[this.slider.items.length-1].style.opacity = 0;
			if (!this.slider.row)
				this.slider.row = this.slider.items[this.slider.items.length-1].parentNode;
		}
		this.slider.items[0].style.opacity = 1;
		this.size = this.slider.items.length;
	}

	if (!!this.params.arrows)
	{
		if (BX.type.isDomNode(this.params.arrows))
			this.slider.arrows = this.params.arrows;
		else if ('object' == typeof(this.params.arrows))
			this.slider.arrows = this.slider.cont.appendChild(BX.create(
				'DIV',
				{
					props: {
						id: this.params.arrows.id,
						className: this.params.arrows.className
					}
				}
			));
		else if (BX.type.isNotEmptyString(this.params.arrows))
			this.slider.arrows = BX(this.params.arrows);
	}
	if (!this.slider.arrows)
	{
		this.slider.arrows = this.slider.cont;
	}
	if (!!this.params.left)
	{
		if (BX.type.isDomNode(this.params.left))
			this.slider.left = this.params.left;
		else if ('object' == typeof(this.params.left))
			this.slider.left = this.slider.arrows.appendChild(BX.create(
				'DIV',
				{
					props: {
						id: this.params.left.id,
						className: this.params.left.className
					}
				}
			));
		else if (BX.type.isNotEmptyString(this.params.left))
			this.slider.left = BX(this.params.left);
	}
	if (!!this.params.right)
	{
		if (BX.type.isDomNode(this.params.right))
			this.slider.right = this.params.right;
		else if ('object' == typeof(this.params.right))
			this.slider.right = this.slider.arrows.appendChild(BX.create(
				'DIV',
				{
					props: {
						id: this.params.right.id,
						className: this.params.right.className
					}
				}
			));
		else if (BX.type.isNotEmptyString(this.params.right))
			this.slider.right = BX(this.params.right);
	}
	if (!!this.params.pagination)
	{
		if (BX.type.isDomNode(this.params.pagination))
			this.slider.pagination = this.params.pagination;
		else if ('object' == typeof(this.params.pagination))
			this.slider.pagination = this.slider.cont.appendChild(BX.create(
				'UL',
				{
					props: {
						id: this.params.pagination.id,
						className: this.params.pagination.className
					}
				}
			));
		else if (BX.type.isNotEmptyString(this.params.pagination))
			this.slider.pagination = BX(this.params.pagination);
	}
	if (!!this.slider.pagination)
	{
		this.showPages = true;
		this.slider.pages = [];
		for (i = 0; i < this.slider.items.length; i++)
		{
			this.slider.pages[this.slider.pages.length] = this.slider.pagination.appendChild(BX.create(
				'LI',
				{
					props: {
						className: (0 === i ? 'active' : '')
					},
					attrs: {
						'data-pagevalue': i.toString()
					},
					events: {
						'click': BX.delegate(this.RowMove, this)
					},
					html: '<span></span>'
				}
			));
		}
	}

	if (0 === this.errorCode)
	{
		if (this.rotate && !!this.slider.cont && 0 < this.rotateTimer)
		{
			BX.bind(this.slider.cont, 'mouseover', BX.delegate(this.RotateStop, this));
			BX.bind(this.slider.cont, 'mouseout', BX.delegate(this.RotateStart, this));
			setTimeout(BX.delegate(this.RowRotate, this), this.rotateTimer);
		}
		if (!!this.slider.left)
		{
			BX.bind(this.slider.left, 'click', BX.delegate(this.RowLeft, this));
		}
		if (!!this.slider.right)
		{
			BX.bind(this.slider.right, 'click', BX.delegate(this.RowRight, this));
		}
	}
};

window.JCCatalogTopBannerList.prototype.RowStart = function()
{
	if (0 > this.errorCode)
		return;
	BX.removeClass(this.slider.items[this.prevIndex], 'active');
	if (this.showPages)
	{
		BX.removeClass(this.slider.pages[this.prevIndex], 'active');
	}
};

window.JCCatalogTopBannerList.prototype.RowAnimate = function(state)
{
	if (0 > this.errorCode)
		return;
	this.slider.items[this.prevIndex].style.opacity = (100 - state.opacity)/100;
	this.slider.items[this.currentIndex].style.opacity = state.opacity/100;
};

window.JCCatalogTopBannerList.prototype.RowComplete = function()
{
	if (0 > this.errorCode)
		return;
	BX.addClass(this.slider.items[this.currentIndex], 'active');
	if (this.showPages)
	{
		BX.addClass(this.slider.pages[this.currentIndex], 'active');
	}
};

window.JCCatalogTopBannerList.prototype.RowLeft = function()
{
	if (0 > this.errorCode)
		return;
	this.prevIndex = this.currentIndex;
	this.currentIndex = (0 === this.currentIndex ? this.size : this.currentIndex)-1;
	new BX.easing({
		duration : 800,
		start : { left : -this.prevIndex*100 },
		finish : { left : -this.currentIndex*100 },
		transition : BX.easing.transitions.quart,
		step : BX.delegate(function(state){
			this.slider.row.style.left = state.left+'%';
		}, this)
	}).animate();
	this.RowStart();
	new BX.easing({
		duration : 1200,
		start : { opacity : 0 },
		finish : { opacity : 100 },
		transition : BX.easing.transitions.quart,
		step : BX.delegate(function(state) {this.RowAnimate(state); }, this),
		complete: BX.delegate(this.RowComplete, this)
	}).animate();
};

window.JCCatalogTopBannerList.prototype.RowRight = function()
{
	if (0 > this.errorCode)
		return;
	this.prevIndex = this.currentIndex;
	this.currentIndex++;
	if (this.currentIndex == this.size)
		this.currentIndex = 0;
	new BX.easing({
		duration : 800,
		start : { left : -this.prevIndex*100 },
		finish : { left : -this.currentIndex*100 },
		transition : BX.easing.transitions.quart,
		step : BX.delegate(function(state){
			this.slider.row.style.left = state.left+'%';
		}, this)
	}).animate();
	this.RowStart();
	new BX.easing({
		duration : 1200,
		start : { opacity : 0 },
		finish : { opacity : 100 },
		transition : BX.easing.transitions.quart,
		step : BX.delegate(function(state) {this.RowAnimate(state); }, this),
		complete: BX.delegate(this.RowComplete, this)
	}).animate();
};

window.JCCatalogTopBannerList.prototype.RowMove = function()
{
	if (0 > this.errorCode)
		return;
	var target = BX.proxy_context;
	if (!!target && target.hasAttribute('data-pagevalue'))
	{
		var pageValue = parseInt(target.getAttribute('data-pagevalue'));
		if (!isNaN(pageValue) && pageValue < this.size)
		{
			this.prevIndex = this.currentIndex;
			this.currentIndex = pageValue;
			this.slider.row.style.left = -this.currentIndex*100+'%';
			this.slider.items[this.prevIndex].style.opacity = 0;
			this.RowStart();
			new BX.easing({
				duration : 800,
				start : { opacity : 0 },
				finish : { opacity : 100 },
				transition : BX.easing.transitions.quart,
				step : BX.delegate(function(state) { this.RowAnimate(state); }, this),
				complete: BX.delegate(this.RowComplete, this)
			}).animate();
		}
	}
};

window.JCCatalogTopBannerList.prototype.RowRotate = function()
{
	if (0 > this.errorCode)
		return;
	if (!this.rotatePause)
	{
		this.RowRight();
	}
	setTimeout(BX.delegate(this.RowRotate, this), this.rotateTimer);
};

window.JCCatalogTopBannerList.prototype.RotateStart = function()
{
	if (0 > this.errorCode)
		return;
	this.rotatePause = false;
};

window.JCCatalogTopBannerList.prototype.RotateStop = function()
{
	if (0 > this.errorCode)
		return;
	this.rotatePause = true;
};
})(window);
/* End */
;
; /* Start:"a:4:{s:4:"full";s:89:"/bitrix/components/bitrix/catalog.top/templates/.default/slider/script.js?143393746546429";s:6:"source";s:73:"/bitrix/components/bitrix/catalog.top/templates/.default/slider/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function (window)
{
if (!!window.JCCatalogTopSlider)
{
	return;
}

var BasketButton = function(params)
{
	BasketButton.superclass.constructor.apply(this, arguments);
	this.nameNode = BX.create('span', {
		props : { className : 'bx_medium bx_bt_button', id : this.id },
		style: typeof(params.style) === 'object' ? params.style : {},
		text: params.text
	});
	this.buttonNode = BX.create('span', {
		attrs: { className: params.ownerClass },
		style: { borderBottom: '0 none transparent', marginBottom: '0', position: 'static' },
		children: [this.nameNode],
		events : this.contextEvents
	});
	if (BX.browser.IsIE())
	{
		this.buttonNode.setAttribute("hideFocus", "hidefocus");
	}
};
BX.extend(BasketButton, BX.PopupWindowButton);

window.JCCatalogTopSlider = function (arParams)
{
	this.productType = 0;
	this.showQuantity = true;
	this.showAbsent = true;
	this.secondPict = false;
	this.showOldPrice = false;
	this.showPercent = false;
	this.showSkuProps = false;
	this.basketAction = 'ADD';
	this.showClosePopup = false;
	this.useCompare = false;
	this.visual = {
		ID: '',
		PICT_ID: '',
		SECOND_PICT_ID: '',
		QUANTITY_ID: '',
		QUANTITY_UP_ID: '',
		QUANTITY_DOWN_ID: '',
		PRICE_ID: '',
		DSC_PERC: '',
		SECOND_DSC_PERC: '',
		DISPLAY_PROP_DIV: ''
	};
	this.product = {
		checkQuantity: false,
		maxQuantity: 0,
		stepQuantity: 1,
		isDblQuantity: false,
		canBuy: true,
		canSubscription: true,
		name: '',
		pict: {},
		id: 0,
		addUrl: '',
		buyUrl: ''
	};

	this.basketMode = '';
	this.basketData = {
		useProps: false,
		emptyProps: false,
		quantity: 'quantity',
		props: 'prop',
		basketUrl: '',
		sku_props: '',
		sku_props_var: 'basket_props',
		add_url: '',
		buy_url: ''
	};

	this.compareData = {
		compareUrl: '',
		comparePath: ''
	};

	this.defaultPict = {
		pict: null,
		secondPict: null
	};

	this.checkQuantity = false;
	this.maxQuantity = 0;
	this.stepQuantity = 1;
	this.isDblQuantity = false;
	this.canBuy = true;
	this.currentBasisPrice = {};
	this.canSubscription = true;
	this.precision = 6;
	this.precisionFactor = Math.pow(10,this.precision);

	this.offers = [];
	this.offerNum = 0;
	this.treeProps = [];
	this.obTreeRows = [];
	this.showCount = [];
	this.showStart = [];
	this.selectedValues = {};

	this.obProduct = null;
	this.obQuantity = null;
	this.obQuantityUp = null;
	this.obQuantityDown = null;
	this.obPict = null;
	this.obSecondPict = null;
	this.obPrice = null;
	this.obTree = null;
	this.obBuyBtn = null;
	this.obBasketActions = null;
	this.obNotAvail = null;
	this.obDscPerc = null;
	this.obSecondDscPerc = null;
	this.obSkuProps = null;
	this.obMeasure = null;
	this.obCompare = null;

	this.treeRowShowSize = 5;
	this.treeEnableArrow = { display: '', cursor: 'pointer', opacity: 1 };
	this.treeDisableArrow = { display: '', cursor: 'default', opacity:0.2 };

	this.lastElement = false;
	this.containerHeight = 0;

	this.errorCode = 0;

	if ('object' === typeof arParams)
	{
		this.productType = parseInt(arParams.PRODUCT_TYPE, 10);
		this.showQuantity = arParams.SHOW_QUANTITY;
		this.showAbsent = arParams.SHOW_ABSENT;
		this.secondPict = !!arParams.SECOND_PICT;
		this.showOldPrice = !!arParams.SHOW_OLD_PRICE;
		this.showPercent = !!arParams.SHOW_DISCOUNT_PERCENT;
		this.showSkuProps = !!arParams.SHOW_SKU_PROPS;
		if (!!arParams.ADD_TO_BASKET_ACTION)
		{
			this.basketAction = arParams.ADD_TO_BASKET_ACTION;
		}
		this.showClosePopup = !!arParams.SHOW_CLOSE_POPUP;
		this.useCompare = !!arParams.DISPLAY_COMPARE;

		this.visual = arParams.VISUAL;
		switch (this.productType)
		{
			case 1://product
			case 2://set
				if (!!arParams.PRODUCT && 'object' === typeof(arParams.PRODUCT))
				{
					if (this.showQuantity)
					{
						this.product.checkQuantity = arParams.PRODUCT.CHECK_QUANTITY;
						this.product.isDblQuantity = arParams.PRODUCT.QUANTITY_FLOAT;
						if (this.product.checkQuantity)
						{
							this.product.maxQuantity = (this.product.isDblQuantity ? parseFloat(arParams.PRODUCT.MAX_QUANTITY) : parseInt(arParams.PRODUCT.MAX_QUANTITY, 10));
						}
						this.product.stepQuantity = (this.product.isDblQuantity ? parseFloat(arParams.PRODUCT.STEP_QUANTITY) : parseInt(arParams.PRODUCT.STEP_QUANTITY, 10));

						this.checkQuantity = this.product.checkQuantity;
						this.isDblQuantity = this.product.isDblQuantity;
						this.maxQuantity = this.product.maxQuantity;
						this.stepQuantity = this.product.stepQuantity;
						if (this.isDblQuantity)
						{
							this.stepQuantity = Math.round(this.stepQuantity*this.precisionFactor)/this.precisionFactor;
						}
					}
					this.product.canBuy = arParams.PRODUCT.CAN_BUY;
					this.product.canSubscription = arParams.PRODUCT.SUBSCRIPTION;
					if (!!arParams.PRODUCT.BASIS_PRICE)
					{
						this.currentBasisPrice = arParams.PRODUCT.BASIS_PRICE;
					}

					this.canBuy = this.product.canBuy;
					this.canSubscription = this.product.canSubscription;

					this.product.name = arParams.PRODUCT.NAME;
					this.product.pict = arParams.PRODUCT.PICT;
					this.product.id = arParams.PRODUCT.ID;
					if (!!arParams.PRODUCT.ADD_URL)
					{
						this.product.addUrl = arParams.PRODUCT.ADD_URL;
					}
					if (!!arParams.PRODUCT.BUY_URL)
					{
						this.product.buyUrl = arParams.PRODUCT.BUY_URL;
					}
					if (!!arParams.BASKET && 'object' === typeof(arParams.BASKET))
					{
						this.basketData.useProps = !!arParams.BASKET.ADD_PROPS;
						this.basketData.emptyProps = !!arParams.BASKET.EMPTY_PROPS;
					}
				}
				else
				{
					this.errorCode = -1;
				}
				break;
			case 3://sku
				if (!!arParams.OFFERS && BX.type.isArray(arParams.OFFERS))
				{
					this.product.name = arParams.PRODUCT.NAME;
					this.product.id = arParams.PRODUCT.ID;
					this.offers = arParams.OFFERS;
					this.offerNum = 0;
					if (!!arParams.OFFER_SELECTED)
					{
						this.offerNum = parseInt(arParams.OFFER_SELECTED, 10);
					}
					if (isNaN(this.offerNum))
					{
						this.offerNum = 0;
					}
					if (!!arParams.TREE_PROPS)
					{
						this.treeProps = arParams.TREE_PROPS;
					}
					if (!!arParams.DEFAULT_PICTURE)
					{
						this.defaultPict.pict = arParams.DEFAULT_PICTURE.PICTURE;
						this.defaultPict.secondPict = arParams.DEFAULT_PICTURE.PICTURE_SECOND;
					}
				}
				break;
			default:
				this.errorCode = -1;
		}
		if (!!arParams.BASKET && 'object' === typeof(arParams.BASKET))
		{
			if (!!arParams.BASKET.QUANTITY)
			{
				this.basketData.quantity = arParams.BASKET.QUANTITY;
			}
			if (!!arParams.BASKET.PROPS)
			{
				this.basketData.props = arParams.BASKET.PROPS;
			}
			if (!!arParams.BASKET.BASKET_URL)
			{
				this.basketData.basketUrl = arParams.BASKET.BASKET_URL;
			}
			if (3 === this.productType)
			{
				if (!!arParams.BASKET.SKU_PROPS)
				{
					this.basketData.sku_props = arParams.BASKET.SKU_PROPS;
				}
			}
			if (!!arParams.BASKET.ADD_URL_TEMPLATE)
			{
				this.basketData.add_url = arParams.BASKET.ADD_URL_TEMPLATE;
			}
			if (!!arParams.BASKET.BUY_URL_TEMPLATE)
			{
				this.basketData.buy_url = arParams.BASKET.BUY_URL_TEMPLATE;
			}
			if (this.basketData.add_url === '' && this.basketData.buy_url === '')
			{
				this.errorCode = -1024;
			}
		}
		if (this.useCompare)
		{
			if (!!arParams.COMPARE && typeof(arParams.COMPARE) === 'object')
			{
				if (!!arParams.COMPARE.COMPARE_PATH)
				{
					this.compareData.comparePath = arParams.COMPARE.COMPARE_PATH;
				}
				if (!!arParams.COMPARE.COMPARE_URL_TEMPLATE)
				{
					this.compareData.compareUrl = arParams.COMPARE.COMPARE_URL_TEMPLATE;
				}
				else
				{
					this.useCompare = false;
				}
			}
			else
			{
				this.useCompare = false;
			}
		}
		this.lastElement = (!!arParams.LAST_ELEMENT && 'Y' === arParams.LAST_ELEMENT);
	}
	if (0 === this.errorCode)
	{
		BX.ready(BX.delegate(this.Init,this));
	}
};

window.JCCatalogTopSlider.prototype.Init = function()
{
	var i = 0,
		strPrefix = '',
		TreeItems = null;

	this.obProduct = BX(this.visual.ID);
	if (!this.obProduct)
	{
		this.errorCode = -1;
	}
	this.obPict = BX(this.visual.PICT_ID);
	if (!this.obPict)
	{
		this.errorCode = -2;
	}
	if (this.secondPict && !!this.visual.SECOND_PICT_ID)
	{
		this.obSecondPict = BX(this.visual.SECOND_PICT_ID);
	}
	this.obPrice = BX(this.visual.PRICE_ID);
	if (!this.obPrice)
	{
		this.errorCode = -16;
	}
	if (this.showQuantity && !!this.visual.QUANTITY_ID)
	{
		this.obQuantity = BX(this.visual.QUANTITY_ID);
		if (!!this.visual.QUANTITY_UP_ID)
		{
			this.obQuantityUp = BX(this.visual.QUANTITY_UP_ID);
		}
		if (!!this.visual.QUANTITY_DOWN_ID)
		{
			this.obQuantityDown = BX(this.visual.QUANTITY_DOWN_ID);
		}
	}
	if (3 === this.productType && this.offers.length > 0)
	{
		if (!!this.visual.TREE_ID)
		{
			this.obTree = BX(this.visual.TREE_ID);
			if (!this.obTree)
			{
				this.errorCode = -256;
			}
			strPrefix = this.visual.TREE_ITEM_ID;
			for (i = 0; i < this.treeProps.length; i++)
			{
				this.obTreeRows[i] = {
					LEFT: BX(strPrefix+this.treeProps[i].ID+'_left'),
					RIGHT: BX(strPrefix+this.treeProps[i].ID+'_right'),
					LIST: BX(strPrefix+this.treeProps[i].ID+'_list'),
					CONT: BX(strPrefix+this.treeProps[i].ID+'_cont')
				};
				if (!this.obTreeRows[i].LEFT || !this.obTreeRows[i].RIGHT || !this.obTreeRows[i].LIST || !this.obTreeRows[i].CONT)
				{
					this.errorCode = -512;
					break;
				}
			}
		}
		if (!!this.visual.QUANTITY_MEASURE)
		{
			this.obMeasure = BX(this.visual.QUANTITY_MEASURE);
		}
	}

	this.obBasketActions = BX(this.visual.BASKET_ACTIONS_ID);
	if (!!this.obBasketActions)
	{
		if (!!this.visual.BUY_ID)
		{
			this.obBuyBtn = BX(this.visual.BUY_ID);
		}
	}
	this.obNotAvail = BX(this.visual.NOT_AVAILABLE_MESS);

	if (this.showPercent)
	{
		if (!!this.visual.DSC_PERC)
		{
			this.obDscPerc = BX(this.visual.DSC_PERC);
		}
		if (this.secondPict && !!this.visual.SECOND_DSC_PERC)
		{
			this.obSecondDscPerc = BX(this.visual.SECOND_DSC_PERC);
		}
	}

	if (this.showSkuProps)
	{
		if (!!this.visual.DISPLAY_PROP_DIV)
		{
			this.obSkuProps = BX(this.visual.DISPLAY_PROP_DIV);
		}
	}

	if (0 === this.errorCode)
	{
		if (this.showQuantity)
		{
			if (!!this.obQuantityUp)
			{
				BX.bind(this.obQuantityUp, 'click', BX.delegate(this.QuantityUp, this));
			}
			if (!!this.obQuantityDown)
			{
				BX.bind(this.obQuantityDown, 'click', BX.delegate(this.QuantityDown, this));
			}
			if (this.obQuantity)
			{
				BX.bind(this.obQuantity, 'change', BX.delegate(this.QuantityChange, this));
			}
		}
		switch (this.productType)
		{
			case 1://product
				break;
			case 3://sku
				if (this.offers.length > 0)
				{
					TreeItems = BX.findChildren(this.obTree, {tagName: 'li'}, true);
					if (!!TreeItems && 0 < TreeItems.length)
					{
						for (i = 0; i < TreeItems.length; i++)
						{
							BX.bind(TreeItems[i], 'click', BX.delegate(this.SelectOfferProp, this));
						}
					}
					for (i = 0; i < this.obTreeRows.length; i++)
					{
						BX.bind(this.obTreeRows[i].LEFT, 'click', BX.delegate(this.RowLeft, this));
						BX.bind(this.obTreeRows[i].RIGHT, 'click', BX.delegate(this.RowRight, this));
					}
					this.SetCurrent();
				}
				break;
		}
		if (!!this.obBuyBtn)
		{
			if (this.basketAction === 'ADD')
			{
				BX.bind(this.obBuyBtn, 'click', BX.delegate(this.Add2Basket, this));
			}
			else
			{
				BX.bind(this.obBuyBtn, 'click', BX.delegate(this.BuyBasket, this));
			}
		}
		if (this.lastElement)
		{
			this.containerHeight = parseInt(this.obProduct.parentNode.offsetHeight, 10);
			if (isNaN(this.containerHeight))
			{
				this.containerHeight = 0;
			}
			this.setHeight();
			BX.bind(window, 'resize', BX.delegate(this.checkHeight, this));
			BX.bind(this.obProduct.parentNode, 'mouseover', BX.delegate(this.setHeight, this));
			BX.bind(this.obProduct.parentNode, 'mouseout', BX.delegate(this.clearHeight, this));
		}
		if (this.useCompare)
		{
			this.obCompare = BX(this.visual.COMPARE_LINK_ID);
			if (!!this.obCompare)
			{
				BX.bind(this.obCompare, 'click', BX.proxy(this.Compare, this));
			}
		}
	}
};

window.JCCatalogTopSlider.prototype.checkHeight = function()
{
	this.containerHeight = parseInt(this.obProduct.parentNode.offsetHeight, 10);
	if (isNaN(this.containerHeight))
	{
		this.containerHeight = 0;
	}
};

window.JCCatalogTopSlider.prototype.setHeight = function()
{
	if (0 < this.containerHeight)
	{
		BX.adjust(this.obProduct.parentNode, {style: { height: this.containerHeight+'px'}});
	}
};

window.JCCatalogTopSlider.prototype.clearHeight = function()
{
	BX.adjust(this.obProduct.parentNode, {style: { height: 'auto'}});
};

window.JCCatalogTopSlider.prototype.QuantityUp = function()
{
	var curValue = 0,
		boolSet = true,
		calcPrice;

	if (0 === this.errorCode && this.showQuantity && this.canBuy)
	{
		curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value) : parseInt(this.obQuantity.value, 10));
		if (!isNaN(curValue))
		{
			curValue += this.stepQuantity;
			if (this.checkQuantity)
			{
				if (curValue > this.maxQuantity)
				{
					boolSet = false;
				}
			}
			if (boolSet)
			{
				if (this.isDblQuantity)
				{
					curValue = Math.round(curValue*this.precisionFactor)/this.precisionFactor;
				}
				this.obQuantity.value = curValue;
				calcPrice = {
					DISCOUNT_VALUE: this.currentBasisPrice.DISCOUNT_VALUE * curValue,
					VALUE: this.currentBasisPrice.VALUE * curValue,
					DISCOUNT_DIFF: this.currentBasisPrice.DISCOUNT_DIFF * curValue,
					DISCOUNT_DIFF_PERCENT: this.currentBasisPrice.DISCOUNT_DIFF_PERCENT,
					CURRENCY: this.currentBasisPrice.CURRENCY
				};
				this.setPrice(calcPrice);
			}
		}
	}
};

window.JCCatalogTopSlider.prototype.QuantityDown = function()
{
	var curValue = 0,
		boolSet = true,
		calcPrice;

	if (0 === this.errorCode && this.showQuantity && this.canBuy)
	{
		curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value): parseInt(this.obQuantity.value, 10));
		if (!isNaN(curValue))
		{
			curValue -= this.stepQuantity;
			if (curValue < this.stepQuantity)
			{
				boolSet = false;
			}
			if (boolSet)
			{
				if (this.isDblQuantity)
				{
					curValue = Math.round(curValue*this.precisionFactor)/this.precisionFactor;
				}
				this.obQuantity.value = curValue;
				calcPrice = {
					DISCOUNT_VALUE: this.currentBasisPrice.DISCOUNT_VALUE * curValue,
					VALUE: this.currentBasisPrice.VALUE * curValue,
					DISCOUNT_DIFF: this.currentBasisPrice.DISCOUNT_DIFF * curValue,
					DISCOUNT_DIFF_PERCENT: this.currentBasisPrice.DISCOUNT_DIFF_PERCENT,
					CURRENCY: this.currentBasisPrice.CURRENCY
				};
				this.setPrice(calcPrice);
			}
		}
	}
};

window.JCCatalogTopSlider.prototype.QuantityChange = function()
{
	var curValue = 0,
		calcPrice,
		intCount,
		count;

	if (0 === this.errorCode && this.showQuantity)
	{
		if (this.canBuy)
		{
			curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value) : parseInt(this.obQuantity.value, 10));
			if (!isNaN(curValue))
			{
				if (this.checkQuantity)
				{
					if (curValue > this.maxQuantity)
					{
						curValue = this.maxQuantity;
					}
				}
				if (curValue < this.stepQuantity)
				{
					curValue = this.stepQuantity;
				}
				else
				{
					count = Math.round((curValue*this.precisionFactor)/this.stepQuantity)/this.precisionFactor;
					intCount = parseInt(count, 10);
					if (isNaN(intCount))
					{
						intCount = 1;
						count = 1.1;
					}
					if (count > intCount)
					{
						curValue = (intCount <= 1 ? this.stepQuantity : intCount*this.stepQuantity);
						curValue = Math.round(curValue*this.precisionFactor)/this.precisionFactor;
					}
				}
				this.obQuantity.value = curValue;
			}
			else
			{
				this.obQuantity.value = this.stepQuantity;
			}
		}
		else
		{
			this.obQuantity.value = this.stepQuantity;
		}
		calcPrice = {
			DISCOUNT_VALUE: this.currentBasisPrice.DISCOUNT_VALUE * this.obQuantity.value,
			VALUE: this.currentBasisPrice.VALUE * this.obQuantity.value,
			DISCOUNT_DIFF: this.currentBasisPrice.DISCOUNT_DIFF * this.obQuantity.value,
			DISCOUNT_DIFF_PERCENT: this.currentBasisPrice.DISCOUNT_DIFF_PERCENT,
			CURRENCY: this.currentBasisPrice.CURRENCY
		};
		this.setPrice(calcPrice);
	}
};

window.JCCatalogTopSlider.prototype.QuantitySet = function(index)
{
	if (0 === this.errorCode)
	{
		this.canBuy = this.offers[index].CAN_BUY;
		if (this.canBuy)
		{
			if (!!this.obBasketActions)
			{
				BX.style(this.obBasketActions, 'display', '');
			}
			if (!!this.obNotAvail)
			{
				BX.style(this.obNotAvail, 'display', 'none');
			}
		}
		else
		{
			if (!!this.obBasketActions)
			{
				BX.style(this.obBasketActions, 'display', 'none');
			}
			if (!!this.obNotAvail)
			{
				BX.style(this.obNotAvail, 'display', '');
			}
		}
		if (this.showQuantity)
		{
			this.isDblQuantity = this.offers[index].QUANTITY_FLOAT;
			this.checkQuantity = this.offers[index].CHECK_QUANTITY;
			if (this.isDblQuantity)
			{
				this.maxQuantity = parseFloat(this.offers[index].MAX_QUANTITY);
				this.stepQuantity = Math.round(parseFloat(this.offers[index].STEP_QUANTITY)*this.precisionFactor)/this.precisionFactor;
			}
			else
			{
				this.maxQuantity = parseInt(this.offers[index].MAX_QUANTITY, 10);
				this.stepQuantity = parseInt(this.offers[index].STEP_QUANTITY, 10);
			}

			this.obQuantity.value = this.stepQuantity;
			this.obQuantity.disabled = !this.canBuy;
			if (!!this.obMeasure)
			{
				if (!!this.offers[index].MEASURE)
				{
					BX.adjust(this.obMeasure, { html : this.offers[index].MEASURE});
				}
				else
				{
					BX.adjust(this.obMeasure, { html : ''});
				}
			}
		}
		this.currentBasisPrice = this.offers[index].BASIS_PRICE;
	}
};

window.JCCatalogTopSlider.prototype.SelectOfferProp = function()
{
	var i = 0,
		value = '',
		strTreeValue = '',
		arTreeItem = [],
		RowItems = null,
		target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-treevalue'))
	{
		strTreeValue = target.getAttribute('data-treevalue');
		arTreeItem = strTreeValue.split('_');
		if (this.SearchOfferPropIndex(arTreeItem[0], arTreeItem[1]))
		{
			RowItems = BX.findChildren(target.parentNode, {tagName: 'li'}, false);
			if (!!RowItems && 0 < RowItems.length)
			{
				for (i = 0; i < RowItems.length; i++)
				{
					value = RowItems[i].getAttribute('data-onevalue');
					if (value === arTreeItem[1])
					{
						BX.addClass(RowItems[i], 'bx_active');
					}
					else
					{
						BX.removeClass(RowItems[i], 'bx_active');
					}
				}
			}
		}
	}
};

window.JCCatalogTopSlider.prototype.SearchOfferPropIndex = function(strPropID, strPropValue)
{
	var strName = '',
		arShowValues = false,
		i, j,
		arCanBuyValues = [],
		index = -1,
		arFilter = {},
		tmpFilter = [];

	for (i = 0; i < this.treeProps.length; i++)
	{
		if (this.treeProps[i].ID === strPropID)
		{
			index = i;
			break;
		}
	}

	if (-1 < index)
	{
		for (i = 0; i < index; i++)
		{
			strName = 'PROP_'+this.treeProps[i].ID;
			arFilter[strName] = this.selectedValues[strName];
		}
		strName = 'PROP_'+this.treeProps[index].ID;
		arShowValues = this.GetRowValues(arFilter, strName);
		if (!arShowValues)
		{
			return false;
		}
		if (!BX.util.in_array(strPropValue, arShowValues))
		{
			return false;
		}
		arFilter[strName] = strPropValue;
		for (i = index+1; i < this.treeProps.length; i++)
		{
			strName = 'PROP_'+this.treeProps[i].ID;
			arShowValues = this.GetRowValues(arFilter, strName);
			if (!arShowValues)
			{
				return false;
			}
			if (this.showAbsent)
			{
				arCanBuyValues = [];
				tmpFilter = [];
				tmpFilter = BX.clone(arFilter, true);
				for (j = 0; j < arShowValues.length; j++)
				{
					tmpFilter[strName] = arShowValues[j];
					if (this.GetCanBuy(tmpFilter))
					{
						arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
					}
				}
			}
			else
			{
				arCanBuyValues = arShowValues;
			}
			if (!!this.selectedValues[strName] && BX.util.in_array(this.selectedValues[strName], arCanBuyValues))
			{
				arFilter[strName] = this.selectedValues[strName];
			}
			else
			{
				arFilter[strName] = arCanBuyValues[0];
			}
			this.UpdateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
		}
		this.selectedValues = arFilter;
		this.ChangeInfo();
	}
	return true;
};

window.JCCatalogTopSlider.prototype.RowLeft = function()
{
	var i = 0,
		strTreeValue = '',
		index = -1,
		target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-treevalue'))
	{
		strTreeValue = target.getAttribute('data-treevalue');
		for (i = 0; i < this.treeProps.length; i++)
		{
			if (this.treeProps[i].ID === strTreeValue)
			{
				index = i;
				break;
			}
		}
		if (-1 < index && this.treeRowShowSize < this.showCount[index])
		{
			if (0 > this.showStart[index])
			{
				this.showStart[index]++;
				BX.adjust(this.obTreeRows[index].LIST, { style: { marginLeft: this.showStart[index]*20+'%' }});
				BX.adjust(this.obTreeRows[index].RIGHT, { style: this.treeEnableArrow });
			}

			if (0 <= this.showStart[index])
			{
				BX.adjust(this.obTreeRows[index].LEFT, { style: this.treeDisableArrow });
			}
		}
	}
};

window.JCCatalogTopSlider.prototype.RowRight = function()
{
	var i = 0,
		strTreeValue = '',
		index = -1,
		target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-treevalue'))
	{
		strTreeValue = target.getAttribute('data-treevalue');
		for (i = 0; i < this.treeProps.length; i++)
		{
			if (this.treeProps[i].ID === strTreeValue)
			{
				index = i;
				break;
			}
		}
		if (-1 < index && this.treeRowShowSize < this.showCount[index])
		{
			if ((this.treeRowShowSize - this.showStart[index]) < this.showCount[index])
			{
				this.showStart[index]--;
				BX.adjust(this.obTreeRows[index].LIST, { style: { marginLeft: this.showStart[index]*20+'%' }});
				BX.adjust(this.obTreeRows[index].LEFT, { style: this.treeEnableArrow });
			}

			if ((this.treeRowShowSize - this.showStart[index]) >= this.showCount[index])
			{
				BX.adjust(this.obTreeRows[index].RIGHT, { style: this.treeDisableArrow });
			}
		}
	}
};

window.JCCatalogTopSlider.prototype.UpdateRow = function(intNumber, activeID, showID, canBuyID)
{
	var i = 0,
		showI = 0,
		value = '',
		countShow = 0,
		strNewLen = '',
		obData = {},
		pictMode = false,
		extShowMode = false,
		isCurrent = false,
		selectIndex = 0,
		obLeft = this.treeEnableArrow,
		obRight = this.treeEnableArrow,
		currentShowStart = 0,
		RowItems = null;

	if (-1 < intNumber && intNumber < this.obTreeRows.length)
	{
		RowItems = BX.findChildren(this.obTreeRows[intNumber].LIST, {tagName: 'li'}, false);
		if (!!RowItems && 0 < RowItems.length)
		{
			pictMode = ('PICT' === this.treeProps[intNumber].SHOW_MODE);
			countShow = showID.length;
			extShowMode = this.treeRowShowSize < countShow;
			strNewLen = (extShowMode ? (100/countShow)+'%' : '20%');
			obData = {
				props: { className: '' },
				style: {
					width: strNewLen
				}
			};
			if (pictMode)
			{
				obData.style.paddingTop = strNewLen;
			}
			for (i = 0; i < RowItems.length; i++)
			{
				value = RowItems[i].getAttribute('data-onevalue');
				isCurrent = (value === activeID);
				if (BX.util.in_array(value, canBuyID))
				{
					obData.props.className = (isCurrent ? 'bx_active' : '');
				}
				else
				{
					obData.props.className = (isCurrent ? 'bx_active bx_missing' : 'bx_missing');
				}
				obData.style.display = 'none';
				if (BX.util.in_array(value, showID))
				{
					obData.style.display = '';
					if (isCurrent)
					{
						selectIndex = showI;
					}
					showI++;
				}
				BX.adjust(RowItems[i], obData);
			}

			obData = {
				style: {
					width: (extShowMode ? 20*countShow : 100)+'%',
					marginLeft: '0%'
				}
			};
			if (pictMode)
			{
				BX.adjust(this.obTreeRows[intNumber].CONT, {props: {className: (extShowMode ? 'bx_item_detail_scu full' : 'bx_item_detail_scu')}});
			}
			else
			{
				BX.adjust(this.obTreeRows[intNumber].CONT, {props: {className: (extShowMode ? 'bx_item_detail_size full' : 'bx_item_detail_size')}});
			}
			if (extShowMode)
			{
				if (selectIndex +1 === countShow)
				{
					obRight = this.treeDisableArrow;
				}
				if (this.treeRowShowSize <= selectIndex)
				{
					currentShowStart = this.treeRowShowSize - selectIndex - 1;
					obData.style.marginLeft = currentShowStart*20+'%';
				}
				if (0 === currentShowStart)
				{
					obLeft = this.treeDisableArrow;
				}
				BX.adjust(this.obTreeRows[intNumber].LEFT, {style: obLeft });
				BX.adjust(this.obTreeRows[intNumber].RIGHT, {style: obRight });
			}
			else
			{
				BX.adjust(this.obTreeRows[intNumber].LEFT, {style: {display: 'none'}});
				BX.adjust(this.obTreeRows[intNumber].RIGHT, {style: {display: 'none'}});
			}
			BX.adjust(this.obTreeRows[intNumber].LIST, obData);
			this.showCount[intNumber] = countShow;
			this.showStart[intNumber] = currentShowStart;
		}
	}
};

window.JCCatalogTopSlider.prototype.GetRowValues = function(arFilter, index)
{
	var i = 0,
		j,
		arValues = [],
		boolSearch = false,
		boolOneSearch = true;

	if (0 === arFilter.length)
	{
		for (i = 0; i < this.offers.length; i++)
		{
			if (!BX.util.in_array(this.offers[i].TREE[index], arValues))
			{
				arValues[arValues.length] = this.offers[i].TREE[index];
			}
		}
		boolSearch = true;
	}
	else
	{
		for (i = 0; i < this.offers.length; i++)
		{
			boolOneSearch = true;
			for (j in arFilter)
			{
				if (arFilter[j] !== this.offers[i].TREE[j])
				{
					boolOneSearch = false;
					break;
				}
			}
			if (boolOneSearch)
			{
				if (!BX.util.in_array(this.offers[i].TREE[index], arValues))
				{
					arValues[arValues.length] = this.offers[i].TREE[index];
				}
				boolSearch = true;
			}
		}
	}
	return (boolSearch ? arValues : false);
};

window.JCCatalogTopSlider.prototype.GetCanBuy = function(arFilter)
{
	var i = 0,
		j,
		boolSearch = false,
		boolOneSearch = true;

	for (i = 0; i < this.offers.length; i++)
	{
		boolOneSearch = true;
		for (j in arFilter)
		{
			if (arFilter[j] !== this.offers[i].TREE[j])
			{
				boolOneSearch = false;
				break;
			}
		}
		if (boolOneSearch)
		{
			if (this.offers[i].CAN_BUY)
			{
				boolSearch = true;
				break;
			}
		}
	}
	return boolSearch;
};

window.JCCatalogTopSlider.prototype.SetCurrent = function()
{
	var i = 0,
		j = 0,
		arCanBuyValues = [],
		strName = '',
		arShowValues = false,
		arFilter = {},
		tmpFilter = [],
		current = this.offers[this.offerNum].TREE;

	for (i = 0; i < this.treeProps.length; i++)
	{
		strName = 'PROP_'+this.treeProps[i].ID;
		arShowValues = this.GetRowValues(arFilter, strName);
		if (!arShowValues)
		{
			break;
		}
		if (BX.util.in_array(current[strName], arShowValues))
		{
			arFilter[strName] = current[strName];
		}
		else
		{
			arFilter[strName] = arShowValues[0];
			this.offerNum = 0;
		}
		if (this.showAbsent)
		{
			arCanBuyValues = [];
			tmpFilter = [];
			tmpFilter = BX.clone(arFilter, true);
			for (j = 0; j < arShowValues.length; j++)
			{
				tmpFilter[strName] = arShowValues[j];
				if (this.GetCanBuy(tmpFilter))
				{
					arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
				}
			}
		}
		else
		{
			arCanBuyValues = arShowValues;
		}
		this.UpdateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
	}
	this.selectedValues = arFilter;
	this.ChangeInfo();
};

window.JCCatalogTopSlider.prototype.ChangeInfo = function()
{
	var i = 0,
		j,
		index = -1,
		obData = {},
		boolOneSearch = true,
		strPrice = '';

	for (i = 0; i < this.offers.length; i++)
	{
		boolOneSearch = true;
		for (j in this.selectedValues)
		{
			if (this.selectedValues[j] !== this.offers[i].TREE[j])
			{
				boolOneSearch = false;
				break;
			}
		}
		if (boolOneSearch)
		{
			index = i;
			break;
		}
	}
	if (-1 < index)
	{
		if (!!this.obPict)
		{
			if (!!this.offers[index].PREVIEW_PICTURE)
			{
				BX.adjust(this.obPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE.SRC+')'}});
			}
			else
			{
				BX.adjust(this.obPict, {style: {backgroundImage: 'url('+this.defaultPict.pict.SRC+')'}});
			}
		}
		if (this.secondPict && !!this.obSecondPict)
		{
			if (!!this.offers[index].PREVIEW_PICTURE_SECOND)
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE_SECOND.SRC+')'}});
			}
			else if (!!this.offers[index].PREVIEW_PICTURE.SRC)
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE.SRC+')'}});
			}
			else if (!!this.defaultPict.secondPict)
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.defaultPict.secondPict.SRC+')'}});
			}
			else
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.defaultPict.pict.SRC+')'}});
			}
		}
		if (this.showSkuProps && !!this.obSkuProps)
		{
			if (0 === this.offers[index].DISPLAY_PROPERTIES.length)
			{
				BX.adjust(this.obSkuProps, {style: {display: 'none'}, html: ''});
			}
			else
			{
				BX.adjust(this.obSkuProps, {style: {display: ''}, html: this.offers[index].DISPLAY_PROPERTIES});
			}
		}
		this.setPrice(this.offers[index].PRICE);
		this.offerNum = index;
		this.QuantitySet(this.offerNum);
	}
};

window.JCCatalogTopSlider.prototype.setPrice = function(price)
{
	var strPrice,
		obData;

	if (!!this.obPrice)
	{
		strPrice = BX.Currency.currencyFormat(price.DISCOUNT_VALUE, price.CURRENCY, true);
		if (this.showOldPrice && (price.DISCOUNT_VALUE !== price.VALUE))
		{
			strPrice += ' <span>'+BX.Currency.currencyFormat(price.VALUE, price.CURRENCY, true)+'</span>';
		}
		BX.adjust(this.obPrice, {html: strPrice});
		if (this.showPercent)
		{
			if (price.DISCOUNT_VALUE !== price.VALUE)
			{
				obData = {
					style: {
						display: ''
					},
					html: price.DISCOUNT_DIFF_PERCENT
				};
			}
			else
			{
				obData = {
					style: {
						display: 'none'
					},
					html: ''
				};
			}
			if (!!this.obDscPerc)
			{
				BX.adjust(this.obDscPerc, obData);
			}
			if (!!this.obSecondDscPerc)
			{
				BX.adjust(this.obSecondDscPerc, obData);
			}
		}
	}
};

window.JCCatalogTopSlider.prototype.Compare = function()
{
	var compareParams, compareLink;
	if (!!this.compareData.compareUrl)
	{
		switch (this.productType)
		{
			case 1://product
			case 2://set
				compareLink = this.compareData.compareUrl.replace('#ID#', this.product.id.toString());
				break;
			case 3://sku
				compareLink = this.compareData.compareUrl.replace('#ID#', this.offers[this.offerNum].ID);
				break;
		}
		compareParams = {
			ajax_action: 'Y'
		};
		BX.ajax.loadJSON(
			compareLink,
			compareParams,
			BX.proxy(this.CompareResult, this)
		);
	}
};

window.JCCatalogTopSlider.prototype.CompareResult = function(result)
{
	var popupContent, popupButtons, popupTitle;
	if (!!this.obPopupWin)
	{
		this.obPopupWin.close();
	}
	if (typeof result !== 'object')
	{
		return false;
	}
	this.InitPopupWindow();
	popupTitle = {
		content: BX.create('div', {
			style: { marginRight: '30px', whiteSpace: 'nowrap' },
			text: BX.message('COMPARE_TITLE')
		})
	};
	if (result.STATUS === 'OK')
	{
		BX.onCustomEvent('OnCompareChange');
		popupContent = '<div style="width: 96%; margin: 10px 2%; text-align: center;"><p>'+BX.message('COMPARE_MESSAGE_OK')+'</p></div>';
		if (this.showClosePopup)
		{
			popupButtons = [
				new BasketButton({
					ownerClass: this.obProduct.parentNode.parentNode.parentNode.parentNode.className,
					text: BX.message('BTN_MESSAGE_COMPARE_REDIRECT'),
					events: {
						click: BX.delegate(this.CompareRedirect, this)
					},
					style: {marginRight: '10px'}
				}),
				new BasketButton({
					ownerClass: this.obProduct.parentNode.parentNode.parentNode.parentNode.className,
					text: BX.message('BTN_MESSAGE_CLOSE_POPUP'),
					events: {
						click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
					}
				})
			];
		}
		else
		{
			popupButtons = [
				new BasketButton({
					ownerClass: this.obProduct.parentNode.parentNode.parentNode.parentNode.className,
					text: BX.message('BTN_MESSAGE_COMPARE_REDIRECT'),
					events: {
						click: BX.delegate(this.CompareRedirect, this)
					}
				})
			];
		}
	}
	else
	{
		popupContent = '<div style="width: 96%; margin: 10px 2%; text-align: center;"><p>'+(!!result.MESSAGE ? result.MESSAGE : BX.message('COMPARE_UNKNOWN_ERROR'))+'</p></div>';
		popupButtons = [
			new BasketButton({
				ownerClass: this.obProduct.parentNode.parentNode.parentNode.parentNode.className,
				text: BX.message('BTN_MESSAGE_CLOSE'),
				events: {
					click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
				}

			})
		];
	}
	this.obPopupWin.setTitleBar(popupTitle);
	this.obPopupWin.setContent(popupContent);
	this.obPopupWin.setButtons(popupButtons);
	this.obPopupWin.show();
	return false;
};

window.JCCatalogTopSlider.prototype.CompareRedirect = function()
{
	if (!!this.compareData.comparePath)
	{
		location.href = this.compareData.comparePath;
	}
	else
	{
		this.obPopupWin.close();
	}
};

window.JCCatalogTopSlider.prototype.InitBasketUrl = function()
{
	this.basketUrl = (this.basketMode === 'ADD' ? this.basketData.add_url : this.basketData.buy_url);
	switch (this.productType)
	{
		case 1://product
		case 2://set
			this.basketUrl = this.basketUrl.replace('#ID#', this.product.id.toString());
			break;
		case 3://sku
			this.basketUrl = this.basketUrl.replace('#ID#', this.offers[this.offerNum].ID);
			break;
	}
	this.basketParams = {
		'ajax_basket': 'Y'
	};
	if (this.showQuantity)
	{
		this.basketParams[this.basketData.quantity] = this.obQuantity.value;
	}
	if (!!this.basketData.sku_props)
	{
		this.basketParams[this.basketData.sku_props_var] = this.basketData.sku_props;
	}
};

window.JCCatalogTopSlider.prototype.FillBasketProps = function()
{
	if (!this.visual.BASKET_PROP_DIV)
	{
		return;
	}
	var
		i = 0,
		propCollection = null,
		foundValues = false,
		obBasketProps = null;

	if (this.basketData.useProps && !this.basketData.emptyProps)
	{
		if (!!this.obPopupWin && !!this.obPopupWin.contentContainer)
		{
			obBasketProps = this.obPopupWin.contentContainer;
		}
	}
	else
	{
		obBasketProps = BX(this.visual.BASKET_PROP_DIV);
	}
	if (!!obBasketProps)
	{
		propCollection = obBasketProps.getElementsByTagName('select');
		if (!!propCollection && !!propCollection.length)
		{
			for (i = 0; i < propCollection.length; i++)
			{
				if (!propCollection[i].disabled)
				{
					switch(propCollection[i].type.toLowerCase())
					{
						case 'select-one':
							this.basketParams[propCollection[i].name] = propCollection[i].value;
							foundValues = true;
							break;
						default:
							break;
					}
				}
			}
		}
		propCollection = obBasketProps.getElementsByTagName('input');
		if (!!propCollection && !!propCollection.length)
		{
			for (i = 0; i < propCollection.length; i++)
			{
				if (!propCollection[i].disabled)
				{
					switch(propCollection[i].type.toLowerCase())
					{
						case 'hidden':
							this.basketParams[propCollection[i].name] = propCollection[i].value;
							foundValues = true;
							break;
						case 'radio':
							if (propCollection[i].checked)
							{
								this.basketParams[propCollection[i].name] = propCollection[i].value;
								foundValues = true;
							}
							break;
						default:
							break;
					}
				}
			}
		}
	}
	if (!foundValues)
	{
		this.basketParams[this.basketData.props] = [];
		this.basketParams[this.basketData.props][0] = 0;
	}
};

window.JCCatalogTopSlider.prototype.Add2Basket = function()
{
	this.basketMode = 'ADD';
	this.Basket();
};

window.JCCatalogTopSlider.prototype.BuyBasket = function()
{
	this.basketMode = 'BUY';
	this.Basket();
};

window.JCCatalogTopSlider.prototype.SendToBasket = function()
{
	if (!this.canBuy)
	{
		return;
	}
	this.InitBasketUrl();
	this.FillBasketProps();
	BX.ajax.loadJSON(
		this.basketUrl,
		this.basketParams,
		BX.delegate(this.BasketResult, this)
	);
};

window.JCCatalogTopSlider.prototype.Basket = function()
{
	var contentBasketProps = '';
	if (!this.canBuy)
	{
		return;
	}
	switch (this.productType)
	{
		case 1://product
		case 2://set
			if (this.basketData.useProps && !this.basketData.emptyProps)
			{
				this.InitPopupWindow();
				this.obPopupWin.setTitleBar({
					content: BX.create('div', {
						style: { marginRight: '30px', whiteSpace: 'nowrap' },
						text: BX.message('TITLE_BASKET_PROPS')
					})
				});
				if (BX(this.visual.BASKET_PROP_DIV))
				{
					contentBasketProps = BX(this.visual.BASKET_PROP_DIV).innerHTML;
				}
				this.obPopupWin.setContent(contentBasketProps);
				this.obPopupWin.setButtons([
					new BasketButton({
						ownerClass: this.obProduct.parentNode.parentNode.parentNode.parentNode.className,
						text: BX.message('BTN_MESSAGE_SEND_PROPS'),
						events: {
							click: BX.delegate(this.SendToBasket, this)
						}
					})
				]);
				this.obPopupWin.show();
			}
			else
			{
				this.SendToBasket();
			}
			break;
		case 3://sku
			this.SendToBasket();
			break;
	}
};

window.JCCatalogTopSlider.prototype.BasketResult = function(arResult)
{
	var strContent = '',
		strPict = '',
		successful,
		buttons = [];

	if (!!this.obPopupWin)
	{
		this.obPopupWin.close();
	}
	if ('object' !== typeof arResult)
	{
		return false;
	}
	successful = (arResult.STATUS === 'OK');
	if (successful && this.basketAction === 'BUY')
	{
		this.BasketRedirect();
	}
	else
	{
		this.InitPopupWindow();
		if (successful)
		{
			BX.onCustomEvent('OnBasketChange');
			switch(this.productType)
			{
				case 1://
				case 2://
					strPict = this.product.pict.SRC;
					break;
				case 3:
					strPict = (!!this.offers[this.offerNum].PREVIEW_PICTURE ?
						this.offers[this.offerNum].PREVIEW_PICTURE.SRC :
						this.defaultPict.pict.SRC
						);
					break;
			}
			strContent = '<div style="width: 96%; margin: 10px 2%; text-align: center;"><img src="'+strPict+'" height="130"><p>'+this.product.name+'</p></div>';
			if (this.showClosePopup)
			{
				buttons = [
					new BasketButton({
						ownerClass: this.obProduct.parentNode.parentNode.parentNode.parentNode.className,
						text: BX.message("BTN_MESSAGE_BASKET_REDIRECT"),
						events: {
							click: BX.delegate(this.BasketRedirect, this)
						},
						style: {marginRight: '10px'}
					}),
					new BasketButton({
						ownerClass: this.obProduct.parentNode.parentNode.parentNode.parentNode.className,
						text: BX.message("BTN_MESSAGE_CLOSE_POPUP"),
						events: {
							click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
						}
					})
				];
			}
			else
			{
				buttons = [
					new BasketButton({
						ownerClass: this.obProduct.parentNode.parentNode.parentNode.parentNode.className,
						text: BX.message("BTN_MESSAGE_BASKET_REDIRECT"),
						events: {
							click: BX.delegate(this.BasketRedirect, this)
						}
					})
				];
			}
		}
		else
		{
			strContent = '<div style="width: 96%; margin: 10px 2%; text-align: center;"><p>'+(!!arResult.MESSAGE ? arResult.MESSAGE : BX.message('BASKET_UNKNOWN_ERROR'))+'</p></div>';
			buttons = [
				new BasketButton({
					ownerClass: this.obProduct.parentNode.parentNode.parentNode.parentNode.className,
					text: BX.message('BTN_MESSAGE_CLOSE'),
					events: {
						click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
					}
				})
			];
		}
		this.obPopupWin.setTitleBar({
			content: BX.create('div', {
				style: { marginRight: '30px', whiteSpace: 'nowrap' },
				text: (successful ? BX.message('TITLE_SUCCESSFUL') : BX.message('TITLE_ERROR'))
			})
		});
		this.obPopupWin.setContent(strContent);
		this.obPopupWin.setButtons(buttons);
		this.obPopupWin.show();
	}
};

window.JCCatalogTopSlider.prototype.BasketRedirect = function()
{
	location.href = (!!this.basketData.basketUrl ? this.basketData.basketUrl : BX.message('BASKET_URL'));
};

window.JCCatalogTopSlider.prototype.InitPopupWindow = function()
{
	if (!!this.obPopupWin)
	{
		return;
	}
	this.obPopupWin = BX.PopupWindowManager.create('CatalogSectionBasket_'+this.visual.ID, null, {
		autoHide: false,
		offsetLeft: 0,
		offsetTop: 0,
		overlay : true,
		closeByEsc: true,
		titleBar: true,
		closeIcon: {top: '10px', right: '10px'}
	});
};

if (!!window.JCCatalogTopSliderList)
{
	return;
}

window.JCCatalogTopSliderList = function (arParams)
{
	this.params = null;
	this.currentIndex = 0;
	this.size = 0;
	this.rotate = false;
	this.rotateTimer = 30000;
	this.rotatePause = false;
	this.showPages = false;
	this.errorCode = 0;

	this.slider = {
		cont: null,
		rows: null,
		left: null,
		right: null,
		pagination: null,
		pages: null
	};

	if (!arParams || 'object' !== typeof arParams)
	{
		this.errorCode = -1;
	}
	if (0 === this.errorCode)
	{
		this.params = arParams;
	}
	if (!!this.params.rotate)
	{
		this.rotate = this.params.rotate;
	}
	if (!!this.params.rotateTimer)
	{
		this.params.rotateTimer = parseInt(this.params.rotateTimer, 10);
		if (!isNaN(this.params.rotateTimer) && 0 <= this.params.rotateTimer)
		{
			this.rotateTimer = this.params.rotateTimer;
		}
	}

	if (0 === this.errorCode)
	{
		BX.ready(BX.delegate(this.Init,this));
	}
};

window.JCCatalogTopSliderList.prototype.Init = function()
{
	if (0 > this.errorCode)
	{
		return;
	}

	var i = 0;
	if (!!this.params.cont)
	{
		this.slider.cont = BX(this.params.cont);
	}
	if (!!this.params.rows && BX.type.isArray(this.params.rows))
	{
		this.slider.rows = [];
		for (i = 0; i < this.params.rows.length; i++)
		{
			this.slider.rows[this.slider.rows.length] = BX(this.params.rows[i]);
			if (!this.slider.cont)
			{
				this.slider.cont = this.slider.rows[this.slider.rows.length-1].parent;
			}
		}
		this.size = this.slider.rows.length;
	}
	if (!!this.params.left)
	{
		if (BX.type.isDomNode(this.params.left))
		{
			this.slider.left = this.params.left;
		}
		else if ('object' === typeof(this.params.left))
		{
			this.slider.left = this.slider.cont.appendChild(BX.create(
				'DIV',
				{
					props: {
						id: this.params.left.id,
						className: this.params.left.className
					}
				}
			));
		}
		else if (BX.type.isNotEmptyString(this.params.left))
		{
			this.slider.left = BX(this.params.left);
		}
	}
	if (!!this.params.right)
	{
		if (BX.type.isDomNode(this.params.right))
		{
			this.slider.right = this.params.right;
		}
		else if ('object' === typeof(this.params.right))
		{
			this.slider.right = this.slider.cont.appendChild(BX.create(
				'DIV',
				{
					props: {
						id: this.params.right.id,
						className: this.params.right.className
					}
				}
			));
		}
		else if (BX.type.isNotEmptyString(this.params.right))
		{
			this.slider.right = BX(this.params.right);
		}
	}
	if (!!this.params.pagination)
	{
		if (BX.type.isDomNode(this.params.pagination))
		{
			this.slider.pagination = this.params.pagination;
		}
		else if ('object' === typeof(this.params.pagination))
		{
			this.slider.pagination = this.slider.cont.appendChild(BX.create(
				'UL',
				{
					props: {
						id: this.params.pagination.id,
						className: this.params.pagination.className
					}
				}
			));
		}
		else if (BX.type.isNotEmptyString(this.params.pagination))
		{
			this.slider.pagination = BX(this.params.pagination);
		}
	}
	if (!!this.slider.pagination)
	{
		this.showPages = true;
		this.slider.pages = [];
		for (i = 0; i < this.slider.rows.length; i++)
		{
			this.slider.pages[this.slider.pages.length] = this.slider.pagination.appendChild(BX.create(
				'LI',
				{
					props: {
						className: (0 === i ? 'active' : 'notactive')
					},
					attrs: {
						'data-pagevalue': i.toString()
					},
					events: {
						'click': BX.delegate(this.RowMove, this)
					},
					html: '<span></span>'
				}
			));
		}
	}

	if (0 === this.errorCode)
	{
		if (this.rotate && !!this.slider.cont && 0 < this.rotateTimer)
		{
			BX.bind(this.slider.cont, 'mouseover', BX.delegate(this.RotateStop, this));
			BX.bind(this.slider.cont, 'mouseout', BX.delegate(this.RotateStart, this));
			setTimeout(BX.delegate(this.RowRotate, this), this.rotateTimer);
		}
		if (!!this.slider.left)
		{
			BX.bind(this.slider.left, 'click', BX.delegate(this.RowLeft, this));
		}
		if (!!this.slider.right)
		{
			BX.bind(this.slider.right, 'click', BX.delegate(this.RowRight, this));
		}
	}
};

window.JCCatalogTopSliderList.prototype.RowLeft = function()
{
	if (0 > this.errorCode)
	{
		return;
	}
	if (this.showPages)
	{
		BX.adjust(this.slider.pages[this.currentIndex], {props: {className: 'notactive'}});
	}
	BX.adjust(this.slider.rows[this.currentIndex], {props: {className: 'bx_catalog_tile_slide notactive'}});
	this.currentIndex = (0 === this.currentIndex ? this.size : this.currentIndex)-1;
	if (this.showPages)
	{
		BX.adjust(this.slider.pages[this.currentIndex], {props: {className: 'active'}});
	}
	BX.adjust(this.slider.rows[this.currentIndex], {props: {className: 'bx_catalog_tile_slide active'}});
};

window.JCCatalogTopSliderList.prototype.RowRight = function()
{
	if (0 > this.errorCode)
	{
		return;
	}
	if (this.showPages)
	{
		BX.adjust(this.slider.pages[this.currentIndex], {props: {className: 'notactive'}});
	}
	BX.adjust(this.slider.rows[this.currentIndex], {props: {className: 'bx_catalog_tile_slide notactive'}});
	this.currentIndex++;
	if (this.currentIndex === this.size)
	{
		this.currentIndex = 0;
	}
	if (this.showPages)
	{
		BX.adjust(this.slider.pages[this.currentIndex], {props: {className: 'active'}});
	}
	BX.adjust(this.slider.rows[this.currentIndex], {props: {className: 'bx_catalog_tile_slide active'}});
};

window.JCCatalogTopSliderList.prototype.RowMove = function()
{
	if (0 > this.errorCode)
	{
		return;
	}
	var pageValue = 0,
		target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-pagevalue'))
	{
		pageValue = parseInt(target.getAttribute('data-pagevalue'), 10);
		if (!isNaN(pageValue) && pageValue < this.size)
		{
			if (this.showPages)
			{
				BX.adjust(this.slider.pages[this.currentIndex], {props: {className: 'notactive'}});
			}
			BX.adjust(this.slider.rows[this.currentIndex], {props: {className: 'bx_catalog_tile_slide notactive'}});
			this.currentIndex = pageValue;
			if (this.showPages)
			{
				BX.adjust(this.slider.pages[this.currentIndex], {props: {className: 'active'}});
			}
			BX.adjust(this.slider.rows[this.currentIndex], {props: {className: 'bx_catalog_tile_slide active'}});
		}
	}
};

window.JCCatalogTopSliderList.prototype.RowRotate = function()
{
	if (0 > this.errorCode)
	{
		return;
	}

	if (!this.rotatePause)
	{
		this.RowRight();
	}
	setTimeout(BX.delegate(this.RowRotate, this), this.rotateTimer);
};

window.JCCatalogTopSliderList.prototype.RotateStart = function()
{
	if (0 > this.errorCode)
	{
		return;
	}
	this.rotatePause = false;
};

window.JCCatalogTopSliderList.prototype.RotateStop = function()
{
	if (0 > this.errorCode)
	{
		return;
	}
	this.rotatePause = true;
};
})(window);
/* End */
;
; /* Start:"a:4:{s:4:"full";s:90:"/bitrix/components/bitrix/catalog.top/templates/.default/section/script.js?143393746539599";s:6:"source";s:74:"/bitrix/components/bitrix/catalog.top/templates/.default/section/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function (window)
{
if (!!window.JCCatalogTopSection)
{
	return;
}

var BasketButton = function(params)
{
	BasketButton.superclass.constructor.apply(this, arguments);
	this.nameNode = BX.create('span', {
		props : { className : 'bx_medium bx_bt_button', id : this.id },
		style: typeof(params.style) === 'object' ? params.style : {},
		text: params.text
	});
	this.buttonNode = BX.create('span', {
		attrs: { className: params.ownerClass },
		style: { marginBottom: '0', borderBottom: '0 none transparent' },
		children: [this.nameNode],
		events : this.contextEvents
	});
	if (BX.browser.IsIE())
	{
		this.buttonNode.setAttribute("hideFocus", "hidefocus");
	}
};
BX.extend(BasketButton, BX.PopupWindowButton);

window.JCCatalogTopSection = function (arParams)
{
	this.productType = 0;
	this.showQuantity = true;
	this.showAbsent = true;
	this.secondPict = false;
	this.showOldPrice = false;
	this.showPercent = false;
	this.showSkuProps = false;
	this.basketAction = 'ADD';
	this.showClosePopup = false;
	this.useCompare = false;
	this.visual = {
		ID: '',
		PICT_ID: '',
		SECOND_PICT_ID: '',
		QUANTITY_ID: '',
		QUANTITY_UP_ID: '',
		QUANTITY_DOWN_ID: '',
		PRICE_ID: '',
		DSC_PERC: '',
		SECOND_DSC_PERC: '',
		DISPLAY_PROP_DIV: ''
	};
	this.product = {
		checkQuantity: false,
		maxQuantity: 0,
		stepQuantity: 1,
		isDblQuantity: false,
		canBuy: true,
		canSubscription: true,
		name: '',
		pict: {},
		id: 0,
		addUrl: '',
		buyUrl: ''
	};

	this.basketMode = '';
	this.basketData = {
		useProps: false,
		emptyProps: false,
		quantity: 'quantity',
		props: 'prop',
		basketUrl: '',
		sku_props: '',
		sku_props_var: 'basket_props',
		add_url: '',
		buy_url: ''
	};

	this.compareData = {
		compareUrl: '',
		comparePath: ''
	};

	this.defaultPict = {
		pict: null,
		secondPict: null
	};

	this.checkQuantity = false;
	this.maxQuantity = 0;
	this.stepQuantity = 1;
	this.isDblQuantity = false;
	this.canBuy = true;
	this.currentBasisPrice = {};
	this.canSubscription = true;
	this.precision = 6;
	this.precisionFactor = Math.pow(10,this.precision);

	this.offers = [];
	this.offerNum = 0;
	this.treeProps = [];
	this.obTreeRows = [];
	this.showCount = [];
	this.showStart = [];
	this.selectedValues = {};

	this.obProduct = null;
	this.obQuantity = null;
	this.obQuantityUp = null;
	this.obQuantityDown = null;
	this.obPict = null;
	this.obSecondPict = null;
	this.obPrice = null;
	this.obTree = null;
	this.obBuyBtn = null;
	this.obBasketActions = null;
	this.obNotAvail = null;
	this.obDscPerc = null;
	this.obSecondDscPerc = null;
	this.obSkuProps = null;
	this.obMeasure = null;
	this.obCompare = null;

	this.treeRowShowSize = 5;
	this.treeEnableArrow = { display: '', cursor: 'pointer', opacity: 1 };
	this.treeDisableArrow = { display: '', cursor: 'default', opacity:0.2 };

	this.lastElement = false;
	this.containerHeight = 0;

	this.errorCode = 0;

	if ('object' === typeof arParams)
	{
		this.productType = parseInt(arParams.PRODUCT_TYPE, 10);
		this.showQuantity = arParams.SHOW_QUANTITY;
		this.showAbsent = arParams.SHOW_ABSENT;
		this.secondPict = !!arParams.SECOND_PICT;
		this.showOldPrice = !!arParams.SHOW_OLD_PRICE;
		this.showPercent = !!arParams.SHOW_DISCOUNT_PERCENT;
		this.showSkuProps = !!arParams.SHOW_SKU_PROPS;
		if (!!arParams.ADD_TO_BASKET_ACTION)
		{
			this.basketAction = arParams.ADD_TO_BASKET_ACTION;
		}
		this.showClosePopup = !!arParams.SHOW_CLOSE_POPUP;
		this.useCompare = !!arParams.DISPLAY_COMPARE;

		this.visual = arParams.VISUAL;

		switch (this.productType)
		{
			case 1://product
			case 2://set
				if (!!arParams.PRODUCT && 'object' === typeof(arParams.PRODUCT))
				{
					if (this.showQuantity)
					{
						this.product.checkQuantity = arParams.PRODUCT.CHECK_QUANTITY;
						this.product.isDblQuantity = arParams.PRODUCT.QUANTITY_FLOAT;
						if (this.product.checkQuantity)
						{
							this.product.maxQuantity = (this.product.isDblQuantity ? parseFloat(arParams.PRODUCT.MAX_QUANTITY) : parseInt(arParams.PRODUCT.MAX_QUANTITY, 10));
						}
						this.product.stepQuantity = (this.product.isDblQuantity ? parseFloat(arParams.PRODUCT.STEP_QUANTITY) : parseInt(arParams.PRODUCT.STEP_QUANTITY, 10));

						this.checkQuantity = this.product.checkQuantity;
						this.isDblQuantity = this.product.isDblQuantity;
						this.maxQuantity = this.product.maxQuantity;
						this.stepQuantity = this.product.stepQuantity;
						if (this.isDblQuantity)
						{
							this.stepQuantity = Math.round(this.stepQuantity*this.precisionFactor)/this.precisionFactor;
						}
					}
					this.product.canBuy = arParams.PRODUCT.CAN_BUY;
					this.product.canSubscription = arParams.PRODUCT.SUBSCRIPTION;
					if (!!arParams.PRODUCT.BASIS_PRICE)
					{
						this.currentBasisPrice = arParams.PRODUCT.BASIS_PRICE;
					}

					this.canBuy = this.product.canBuy;
					this.canSubscription = this.product.canSubscription;

					this.product.name = arParams.PRODUCT.NAME;
					this.product.pict = arParams.PRODUCT.PICT;
					this.product.id = arParams.PRODUCT.ID;
					if (!!arParams.PRODUCT.ADD_URL)
					{
						this.product.addUrl = arParams.PRODUCT.ADD_URL;
					}
					if (!!arParams.PRODUCT.BUY_URL)
					{
						this.product.buyUrl = arParams.PRODUCT.BUY_URL;
					}
					if (!!arParams.BASKET && 'object' === typeof(arParams.BASKET))
					{
						this.basketData.useProps = !!arParams.BASKET.ADD_PROPS;
						this.basketData.emptyProps = !!arParams.BASKET.EMPTY_PROPS;
					}
				}
				else
				{
					this.errorCode = -1;
				}
				break;
			case 3://sku
				if (!!arParams.OFFERS && BX.type.isArray(arParams.OFFERS))
				{
					this.product.name = arParams.PRODUCT.NAME;
					this.product.id = arParams.PRODUCT.ID;
					this.offers = arParams.OFFERS;
					this.offerNum = 0;
					if (!!arParams.OFFER_SELECTED)
					{
						this.offerNum = parseInt(arParams.OFFER_SELECTED, 10);
					}
					if (isNaN(this.offerNum))
					{
						this.offerNum = 0;
					}
					if (!!arParams.TREE_PROPS)
					{
						this.treeProps = arParams.TREE_PROPS;
					}
					if (!!arParams.DEFAULT_PICTURE)
					{
						this.defaultPict.pict = arParams.DEFAULT_PICTURE.PICTURE;
						this.defaultPict.secondPict = arParams.DEFAULT_PICTURE.PICTURE_SECOND;
					}
				}
				break;
			default:
				this.errorCode = -1;
		}
		if (!!arParams.BASKET && 'object' === typeof(arParams.BASKET))
		{
			if (!!arParams.BASKET.QUANTITY)
			{
				this.basketData.quantity = arParams.BASKET.QUANTITY;
			}
			if (!!arParams.BASKET.PROPS)
			{
				this.basketData.props = arParams.BASKET.PROPS;
			}
			if (!!arParams.BASKET.BASKET_URL)
			{
				this.basketData.basketUrl = arParams.BASKET.BASKET_URL;
			}
			if (3 === this.productType)
			{
				if (!!arParams.BASKET.SKU_PROPS)
				{
					this.basketData.sku_props = arParams.BASKET.SKU_PROPS;
				}
			}
			if (!!arParams.BASKET.ADD_URL_TEMPLATE)
			{
				this.basketData.add_url = arParams.BASKET.ADD_URL_TEMPLATE;
			}
			if (!!arParams.BASKET.BUY_URL_TEMPLATE)
			{
				this.basketData.buy_url = arParams.BASKET.BUY_URL_TEMPLATE;
			}
			if (this.basketData.add_url === '' && this.basketData.buy_url === '')
			{
				this.errorCode = -1024;
			}
		}
		if (this.useCompare)
		{
			if (!!arParams.COMPARE && typeof(arParams.COMPARE) === 'object')
			{
				if (!!arParams.COMPARE.COMPARE_PATH)
				{
					this.compareData.comparePath = arParams.COMPARE.COMPARE_PATH;
				}
				if (!!arParams.COMPARE.COMPARE_URL_TEMPLATE)
				{
					this.compareData.compareUrl = arParams.COMPARE.COMPARE_URL_TEMPLATE;
				}
				else
				{
					this.useCompare = false;
				}
			}
			else
			{
				this.useCompare = false;
			}
		}
		this.lastElement = (!!arParams.LAST_ELEMENT && 'Y' === arParams.LAST_ELEMENT);
	}
	if (0 === this.errorCode)
	{
		BX.ready(BX.delegate(this.Init,this));
	}
};

window.JCCatalogTopSection.prototype.Init = function()
{
	var i = 0,
		strPrefix = '',
		TreeItems = null;

	this.obProduct = BX(this.visual.ID);
	if (!this.obProduct)
	{
		this.errorCode = -1;
	}
	this.obPict = BX(this.visual.PICT_ID);
	if (!this.obPict)
	{
		this.errorCode = -2;
	}
	if (this.secondPict && !!this.visual.SECOND_PICT_ID)
	{
		this.obSecondPict = BX(this.visual.SECOND_PICT_ID);
	}
	this.obPrice = BX(this.visual.PRICE_ID);
	if (!this.obPrice)
	{
		this.errorCode = -16;
	}
	if (this.showQuantity && !!this.visual.QUANTITY_ID)
	{
		this.obQuantity = BX(this.visual.QUANTITY_ID);
		if (!!this.visual.QUANTITY_UP_ID)
		{
			this.obQuantityUp = BX(this.visual.QUANTITY_UP_ID);
		}
		if (!!this.visual.QUANTITY_DOWN_ID)
		{
			this.obQuantityDown = BX(this.visual.QUANTITY_DOWN_ID);
		}
	}
	if (3 === this.productType && this.offers.length > 0)
	{
		if (!!this.visual.TREE_ID)
		{
			this.obTree = BX(this.visual.TREE_ID);
			if (!this.obTree)
			{
				this.errorCode = -256;
			}
			strPrefix = this.visual.TREE_ITEM_ID;
			for (i = 0; i < this.treeProps.length; i++)
			{
				this.obTreeRows[i] = {
					LEFT: BX(strPrefix+this.treeProps[i].ID+'_left'),
					RIGHT: BX(strPrefix+this.treeProps[i].ID+'_right'),
					LIST: BX(strPrefix+this.treeProps[i].ID+'_list'),
					CONT: BX(strPrefix+this.treeProps[i].ID+'_cont')
				};
				if (!this.obTreeRows[i].LEFT || !this.obTreeRows[i].RIGHT || !this.obTreeRows[i].LIST || !this.obTreeRows[i].CONT)
				{
					this.errorCode = -512;
					break;
				}
			}
		}
		if (!!this.visual.QUANTITY_MEASURE)
		{
			this.obMeasure = BX(this.visual.QUANTITY_MEASURE);
		}
	}
	this.obBasketActions = BX(this.visual.BASKET_ACTIONS_ID);
	if (!!this.obBasketActions)
	{
		if (!!this.visual.BUY_ID)
		{
			this.obBuyBtn = BX(this.visual.BUY_ID);
		}
	}
	this.obNotAvail = BX(this.visual.NOT_AVAILABLE_MESS);

	if (this.showPercent)
	{
		if (!!this.visual.DSC_PERC)
		{
			this.obDscPerc = BX(this.visual.DSC_PERC);
		}
		if (this.secondPict && !!this.visual.SECOND_DSC_PERC)
		{
			this.obSecondDscPerc = BX(this.visual.SECOND_DSC_PERC);
		}
	}

	if (this.showSkuProps)
	{
		if (!!this.visual.DISPLAY_PROP_DIV)
		{
			this.obSkuProps = BX(this.visual.DISPLAY_PROP_DIV);
		}
	}

	if (0 === this.errorCode)
	{
		if (this.showQuantity)
		{
			if (!!this.obQuantityUp)
			{
				BX.bind(this.obQuantityUp, 'click', BX.delegate(this.QuantityUp, this));
			}
			if (!!this.obQuantityDown)
			{
				BX.bind(this.obQuantityDown, 'click', BX.delegate(this.QuantityDown, this));
			}
			if (!!this.obQuantity)
			{
				BX.bind(this.obQuantity, 'change', BX.delegate(this.QuantityChange, this));
			}
		}
		switch (this.productType)
		{
			case 1://product
				break;
			case 3://sku
				if (this.offers.length > 0)
				{
					TreeItems = BX.findChildren(this.obTree, {tagName: 'li'}, true);
					if (!!TreeItems && 0 < TreeItems.length)
					{
						for (i = 0; i < TreeItems.length; i++)
						{
							BX.bind(TreeItems[i], 'click', BX.delegate(this.SelectOfferProp, this));
						}
					}
					for (i = 0; i < this.obTreeRows.length; i++)
					{
						BX.bind(this.obTreeRows[i].LEFT, 'click', BX.delegate(this.RowLeft, this));
						BX.bind(this.obTreeRows[i].RIGHT, 'click', BX.delegate(this.RowRight, this));
					}
					this.SetCurrent();
				}
				break;
		}
		if (!!this.obBuyBtn)
		{
			if (this.basketAction === 'ADD')
			{
				BX.bind(this.obBuyBtn, 'click', BX.delegate(this.Add2Basket, this));
			}
			else
			{
				BX.bind(this.obBuyBtn, 'click', BX.delegate(this.BuyBasket, this));
			}
		}
		if (this.lastElement)
		{
			this.containerHeight = parseInt(this.obProduct.parentNode.offsetHeight, 10);
			if (isNaN(this.containerHeight))
			{
				this.containerHeight = 0;
			}
			this.setHeight();
			BX.bind(window, 'resize', BX.delegate(this.checkHeight, this));
			BX.bind(this.obProduct.parentNode, 'mouseover', BX.delegate(this.setHeight, this));
			BX.bind(this.obProduct.parentNode, 'mouseout', BX.delegate(this.clearHeight, this));
		}
		if (this.useCompare)
		{
			this.obCompare = BX(this.visual.COMPARE_LINK_ID);
			if (!!this.obCompare)
			{
				BX.bind(this.obCompare, 'click', BX.proxy(this.Compare, this));
			}
		}
	}
};

window.JCCatalogTopSection.prototype.checkHeight = function()
{
	this.containerHeight = parseInt(this.obProduct.parentNode.offsetHeight, 10);
	if (isNaN(this.containerHeight))
	{
		this.containerHeight = 0;
	}
};

window.JCCatalogTopSection.prototype.setHeight = function()
{
	if (0 < this.containerHeight)
	{
		BX.adjust(this.obProduct.parentNode, {style: { height: this.containerHeight+'px'}});
	}
};

window.JCCatalogTopSection.prototype.clearHeight = function()
{
	BX.adjust(this.obProduct.parentNode, {style: { height: 'auto'}});
};

window.JCCatalogTopSection.prototype.QuantityUp = function()
{
	var curValue = 0,
		boolSet = true,
		calcPrice;

	if (0 === this.errorCode && this.showQuantity && this.canBuy)
	{
		curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value) : parseInt(this.obQuantity.value, 10));
		if (!isNaN(curValue))
		{
			curValue += this.stepQuantity;
			if (this.checkQuantity)
			{
				if (curValue > this.maxQuantity)
				{
					boolSet = false;
				}
			}
			if (boolSet)
			{
				if (this.isDblQuantity)
				{
					curValue = Math.round(curValue*this.precisionFactor)/this.precisionFactor;
				}
				this.obQuantity.value = curValue;
				calcPrice = {
					DISCOUNT_VALUE: this.currentBasisPrice.DISCOUNT_VALUE * curValue,
					VALUE: this.currentBasisPrice.VALUE * curValue,
					DISCOUNT_DIFF: this.currentBasisPrice.DISCOUNT_DIFF * curValue,
					DISCOUNT_DIFF_PERCENT: this.currentBasisPrice.DISCOUNT_DIFF_PERCENT,
					CURRENCY: this.currentBasisPrice.CURRENCY
				};
				this.setPrice(calcPrice);
			}
		}
	}
};

window.JCCatalogTopSection.prototype.QuantityDown = function()
{
	var curValue = 0,
		boolSet = true,
		calcPrice;

	if (0 === this.errorCode && this.showQuantity && this.canBuy)
	{
		curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value): parseInt(this.obQuantity.value, 10));
		if (!isNaN(curValue))
		{
			curValue -= this.stepQuantity;
			if (curValue < this.stepQuantity)
			{
				boolSet = false;
			}
			if (boolSet)
			{
				if (this.isDblQuantity)
				{
					curValue = Math.round(curValue*this.precisionFactor)/this.precisionFactor;
				}
				this.obQuantity.value = curValue;
				calcPrice = {
					DISCOUNT_VALUE: this.currentBasisPrice.DISCOUNT_VALUE * curValue,
					VALUE: this.currentBasisPrice.VALUE * curValue,
					DISCOUNT_DIFF: this.currentBasisPrice.DISCOUNT_DIFF * curValue,
					DISCOUNT_DIFF_PERCENT: this.currentBasisPrice.DISCOUNT_DIFF_PERCENT,
					CURRENCY: this.currentBasisPrice.CURRENCY
				};
				this.setPrice(calcPrice);
			}
		}
	}
};

window.JCCatalogTopSection.prototype.QuantityChange = function()
{
	var curValue = 0,
		calcPrice,
		intCount,
		count;

	if (0 === this.errorCode && this.showQuantity)
	{
		if (this.canBuy)
		{
			curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value) : parseInt(this.obQuantity.value, 10));
			if (!isNaN(curValue))
			{
				if (this.checkQuantity)
				{
					if (curValue > this.maxQuantity)
					{
						curValue = this.maxQuantity;
					}
				}
				if (curValue < this.stepQuantity)
				{
					curValue = this.stepQuantity;
				}
				else
				{
					count = Math.round((curValue*this.precisionFactor)/this.stepQuantity)/this.precisionFactor;
					intCount = parseInt(count, 10);
					if (isNaN(intCount))
					{
						intCount = 1;
						count = 1.1;
					}
					if (count > intCount)
					{
						curValue = (intCount <= 1 ? this.stepQuantity : intCount*this.stepQuantity);
						curValue = Math.round(curValue*this.precisionFactor)/this.precisionFactor;
					}
				}
				this.obQuantity.value = curValue;
			}
			else
			{
				this.obQuantity.value = this.stepQuantity;
			}
		}
		else
		{
			this.obQuantity.value = this.stepQuantity;
		}
		calcPrice = {
			DISCOUNT_VALUE: this.currentBasisPrice.DISCOUNT_VALUE * this.obQuantity.value,
			VALUE: this.currentBasisPrice.VALUE * this.obQuantity.value,
			DISCOUNT_DIFF: this.currentBasisPrice.DISCOUNT_DIFF * this.obQuantity.value,
			DISCOUNT_DIFF_PERCENT: this.currentBasisPrice.DISCOUNT_DIFF_PERCENT,
			CURRENCY: this.currentBasisPrice.CURRENCY
		};
		this.setPrice(calcPrice);
	}
};

window.JCCatalogTopSection.prototype.QuantitySet = function(index)
{
	if (0 === this.errorCode)
	{
		this.canBuy = this.offers[index].CAN_BUY;
		if (this.canBuy)
		{
			if (!!this.obBasketActions)
			{
				BX.style(this.obBasketActions, 'display', '');
			}
			if (!!this.obNotAvail)
			{
				BX.style(this.obNotAvail, 'display', 'none');
			}
		}
		else
		{
			if (!!this.obBasketActions)
			{
				BX.style(this.obBasketActions, 'display', 'none');
			}
			if (!!this.obNotAvail)
			{
				BX.style(this.obNotAvail, 'display', '');
			}
		}
		if (this.showQuantity)
		{
			this.isDblQuantity = this.offers[index].QUANTITY_FLOAT;
			this.checkQuantity = this.offers[index].CHECK_QUANTITY;
			if (this.isDblQuantity)
			{
				this.maxQuantity = parseFloat(this.offers[index].MAX_QUANTITY);
				this.stepQuantity = Math.round(parseFloat(this.offers[index].STEP_QUANTITY)*this.precisionFactor)/this.precisionFactor;
			}
			else
			{
				this.maxQuantity = parseInt(this.offers[index].MAX_QUANTITY, 10);
				this.stepQuantity = parseInt(this.offers[index].STEP_QUANTITY, 10);
			}

			this.obQuantity.value = this.stepQuantity;
			this.obQuantity.disabled = !this.canBuy;
			if (!!this.obMeasure)
			{
				if (!!this.offers[index].MEASURE)
				{
					BX.adjust(this.obMeasure, { html : this.offers[index].MEASURE});
				}
				else
				{
					BX.adjust(this.obMeasure, { html : ''});
				}
			}
		}
		this.currentBasisPrice = this.offers[index].BASIS_PRICE;
	}
};

window.JCCatalogTopSection.prototype.SelectOfferProp = function()
{
	var i = 0,
		value = '',
		strTreeValue = '',
		arTreeItem = [],
		RowItems = null,
		target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-treevalue'))
	{
		strTreeValue = target.getAttribute('data-treevalue');
		arTreeItem = strTreeValue.split('_');
		if (this.SearchOfferPropIndex(arTreeItem[0], arTreeItem[1]))
		{
			RowItems = BX.findChildren(target.parentNode, {tagName: 'li'}, false);
			if (!!RowItems && 0 < RowItems.length)
			{
				for (i = 0; i < RowItems.length; i++)
				{
					value = RowItems[i].getAttribute('data-onevalue');
					if (value === arTreeItem[1])
					{
						BX.addClass(RowItems[i], 'bx_active');
					}
					else
					{
						BX.removeClass(RowItems[i], 'bx_active');
					}
				}
			}
		}
	}
};

window.JCCatalogTopSection.prototype.SearchOfferPropIndex = function(strPropID, strPropValue)
{
	var strName = '',
		arShowValues = false,
		i, j,
		arCanBuyValues = [],
		index = -1,
		arFilter = {},
		tmpFilter = [];

	for (i = 0; i < this.treeProps.length; i++)
	{
		if (this.treeProps[i].ID === strPropID)
		{
			index = i;
			break;
		}
	}

	if (-1 < index)
	{
		for (i = 0; i < index; i++)
		{
			strName = 'PROP_'+this.treeProps[i].ID;
			arFilter[strName] = this.selectedValues[strName];
		}
		strName = 'PROP_'+this.treeProps[index].ID;
		arShowValues = this.GetRowValues(arFilter, strName);
		if (!arShowValues)
		{
			return false;
		}
		if (!BX.util.in_array(strPropValue, arShowValues))
		{
			return false;
		}
		arFilter[strName] = strPropValue;
		for (i = index+1; i < this.treeProps.length; i++)
		{
			strName = 'PROP_'+this.treeProps[i].ID;
			arShowValues = this.GetRowValues(arFilter, strName);
			if (!arShowValues)
			{
				return false;
			}
			if (this.showAbsent)
			{
				arCanBuyValues = [];
				tmpFilter = [];
				tmpFilter = BX.clone(arFilter, true);
				for (j = 0; j < arShowValues.length; j++)
				{
					tmpFilter[strName] = arShowValues[j];
					if (this.GetCanBuy(tmpFilter))
					{
						arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
					}
				}
			}
			else
			{
				arCanBuyValues = arShowValues;
			}
			if (!!this.selectedValues[strName] && BX.util.in_array(this.selectedValues[strName], arCanBuyValues))
			{
				arFilter[strName] = this.selectedValues[strName];
			}
			else
			{
				arFilter[strName] = arCanBuyValues[0];
			}
			this.UpdateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
		}
		this.selectedValues = arFilter;
		this.ChangeInfo();
	}
	return true;
};

window.JCCatalogTopSection.prototype.RowLeft = function()
{
	var i = 0,
		strTreeValue = '',
		index = -1,
		target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-treevalue'))
	{
		strTreeValue = target.getAttribute('data-treevalue');
		for (i = 0; i < this.treeProps.length; i++)
		{
			if (this.treeProps[i].ID === strTreeValue)
			{
				index = i;
				break;
			}
		}
		if (-1 < index && this.treeRowShowSize < this.showCount[index])
		{
			if (0 > this.showStart[index])
			{
				this.showStart[index]++;
				BX.adjust(this.obTreeRows[index].LIST, { style: { marginLeft: this.showStart[index]*20+'%' }});
				BX.adjust(this.obTreeRows[index].RIGHT, { style: this.treeEnableArrow });
			}

			if (0 <= this.showStart[index])
			{
				BX.adjust(this.obTreeRows[index].LEFT, { style: this.treeDisableArrow });
			}
		}
	}
};

window.JCCatalogTopSection.prototype.RowRight = function()
{
	var i = 0,
		strTreeValue = '',
		index = -1,
		target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-treevalue'))
	{
		strTreeValue = target.getAttribute('data-treevalue');
		for (i = 0; i < this.treeProps.length; i++)
		{
			if (this.treeProps[i].ID === strTreeValue)
			{
				index = i;
				break;
			}
		}
		if (-1 < index && this.treeRowShowSize < this.showCount[index])
		{
			if ((this.treeRowShowSize - this.showStart[index]) < this.showCount[index])
			{
				this.showStart[index]--;
				BX.adjust(this.obTreeRows[index].LIST, { style: { marginLeft: this.showStart[index]*20+'%' }});
				BX.adjust(this.obTreeRows[index].LEFT, { style: this.treeEnableArrow });
			}

			if ((this.treeRowShowSize - this.showStart[index]) >= this.showCount[index])
			{
				BX.adjust(this.obTreeRows[index].RIGHT, { style: this.treeDisableArrow });
			}
		}
	}
};

window.JCCatalogTopSection.prototype.UpdateRow = function(intNumber, activeID, showID, canBuyID)
{
	var i = 0,
		showI = 0,
		value = '',
		countShow = 0,
		strNewLen = '',
		obData = {},
		pictMode = false,
		extShowMode = false,
		isCurrent = false,
		selectIndex = 0,
		obLeft = this.treeEnableArrow,
		obRight = this.treeEnableArrow,
		currentShowStart = 0,
		RowItems = null;

	if (-1 < intNumber && intNumber < this.obTreeRows.length)
	{
		RowItems = BX.findChildren(this.obTreeRows[intNumber].LIST, {tagName: 'li'}, false);
		if (!!RowItems && 0 < RowItems.length)
		{
			pictMode = ('PICT' === this.treeProps[intNumber].SHOW_MODE);
			countShow = showID.length;
			extShowMode = this.treeRowShowSize < countShow;
			strNewLen = (extShowMode ? (100/countShow)+'%' : '20%');
			obData = {
				props: { className: '' },
				style: {
					width: strNewLen
				}
			};
			if (pictMode)
			{
				obData.style.paddingTop = strNewLen;
			}
			for (i = 0; i < RowItems.length; i++)
			{
				value = RowItems[i].getAttribute('data-onevalue');
				isCurrent = (value === activeID);
				if (BX.util.in_array(value, canBuyID))
				{
					obData.props.className = (isCurrent ? 'bx_active' : '');
				}
				else
				{
					obData.props.className = (isCurrent ? 'bx_active bx_missing' : 'bx_missing');
				}
				obData.style.display = 'none';
				if (BX.util.in_array(value, showID))
				{
					obData.style.display = '';
					if (isCurrent)
					{
						selectIndex = showI;
					}
					showI++;
				}
				BX.adjust(RowItems[i], obData);
			}

			obData = {
				style: {
					width: (extShowMode ? 20*countShow : 100)+'%',
					marginLeft: '0%'
				}
			};
			if (pictMode)
			{
				BX.adjust(this.obTreeRows[intNumber].CONT, {props: {className: (extShowMode ? 'bx_item_detail_scu full' : 'bx_item_detail_scu')}});
			}
			else
			{
				BX.adjust(this.obTreeRows[intNumber].CONT, {props: {className: (extShowMode ? 'bx_item_detail_size full' : 'bx_item_detail_size')}});
			}
			if (extShowMode)
			{
				if (selectIndex +1 === countShow)
				{
					obRight = this.treeDisableArrow;
				}
				if (this.treeRowShowSize <= selectIndex)
				{
					currentShowStart = this.treeRowShowSize - selectIndex - 1;
					obData.style.marginLeft = currentShowStart*20+'%';
				}
				if (0 === currentShowStart)
				{
					obLeft = this.treeDisableArrow;
				}
				BX.adjust(this.obTreeRows[intNumber].LEFT, {style: obLeft });
				BX.adjust(this.obTreeRows[intNumber].RIGHT, {style: obRight });
			}
			else
			{
				BX.adjust(this.obTreeRows[intNumber].LEFT, {style: {display: 'none'}});
				BX.adjust(this.obTreeRows[intNumber].RIGHT, {style: {display: 'none'}});
			}
			BX.adjust(this.obTreeRows[intNumber].LIST, obData);
			this.showCount[intNumber] = countShow;
			this.showStart[intNumber] = currentShowStart;
		}
	}
};

window.JCCatalogTopSection.prototype.GetRowValues = function(arFilter, index)
{
	var i = 0,
		j,
		arValues = [],
		boolSearch = false,
		boolOneSearch = true;

	if (0 === arFilter.length)
	{
		for (i = 0; i < this.offers.length; i++)
		{
			if (!BX.util.in_array(this.offers[i].TREE[index], arValues))
			{
				arValues[arValues.length] = this.offers[i].TREE[index];
			}
		}
		boolSearch = true;
	}
	else
	{
		for (i = 0; i < this.offers.length; i++)
		{
			boolOneSearch = true;
			for (j in arFilter)
			{
				if (arFilter[j] !== this.offers[i].TREE[j])
				{
					boolOneSearch = false;
					break;
				}
			}
			if (boolOneSearch)
			{
				if (!BX.util.in_array(this.offers[i].TREE[index], arValues))
				{
					arValues[arValues.length] = this.offers[i].TREE[index];
				}
				boolSearch = true;
			}
		}
	}
	return (boolSearch ? arValues : false);
};

window.JCCatalogTopSection.prototype.GetCanBuy = function(arFilter)
{
	var i = 0,
		j,
		boolSearch = false,
		boolOneSearch = true;

	for (i = 0; i < this.offers.length; i++)
	{
		boolOneSearch = true;
		for (j in arFilter)
		{
			if (arFilter[j] !== this.offers[i].TREE[j])
			{
				boolOneSearch = false;
				break;
			}
		}
		if (boolOneSearch)
		{
			if (this.offers[i].CAN_BUY)
			{
				boolSearch = true;
				break;
			}
		}
	}
	return boolSearch;
};

window.JCCatalogTopSection.prototype.SetCurrent = function()
{
	var i = 0,
		j = 0,
		arCanBuyValues = [],
		strName = '',
		arShowValues = false,
		arFilter = {},
		tmpFilter = [],
		current = this.offers[this.offerNum].TREE;

	for (i = 0; i < this.treeProps.length; i++)
	{
		strName = 'PROP_'+this.treeProps[i].ID;
		arShowValues = this.GetRowValues(arFilter, strName);
		if (!arShowValues)
		{
			break;
		}
		if (BX.util.in_array(current[strName], arShowValues))
		{
			arFilter[strName] = current[strName];
		}
		else
		{
			arFilter[strName] = arShowValues[0];
			this.offerNum = 0;
		}
		if (this.showAbsent)
		{
			arCanBuyValues = [];
			tmpFilter = [];
			tmpFilter = BX.clone(arFilter, true);
			for (j = 0; j < arShowValues.length; j++)
			{
				tmpFilter[strName] = arShowValues[j];
				if (this.GetCanBuy(tmpFilter))
				{
					arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
				}
			}
		}
		else
		{
			arCanBuyValues = arShowValues;
		}
		this.UpdateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
	}
	this.selectedValues = arFilter;
	this.ChangeInfo();
};

window.JCCatalogTopSection.prototype.ChangeInfo = function()
{
	var i = 0,
		j,
		index = -1,
		boolOneSearch = true;

	for (i = 0; i < this.offers.length; i++)
	{
		boolOneSearch = true;
		for (j in this.selectedValues)
		{
			if (this.selectedValues[j] !== this.offers[i].TREE[j])
			{
				boolOneSearch = false;
				break;
			}
		}
		if (boolOneSearch)
		{
			index = i;
			break;
		}
	}
	if (-1 < index)
	{
		if (!!this.obPict)
		{
			if (!!this.offers[index].PREVIEW_PICTURE)
			{
				BX.adjust(this.obPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE.SRC+')'}});
			}
			else
			{
				BX.adjust(this.obPict, {style: {backgroundImage: 'url('+this.defaultPict.pict.SRC+')'}});
			}
		}
		if (this.secondPict && !!this.obSecondPict)
		{
			if (!!this.offers[index].PREVIEW_PICTURE_SECOND)
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE_SECOND.SRC+')'}});
			}
			else if (!!this.offers[index].PREVIEW_PICTURE.SRC)
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE.SRC+')'}});
			}
			else if (!!this.defaultPict.secondPict)
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.defaultPict.secondPict.SRC+')'}});
			}
			else
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.defaultPict.pict.SRC+')'}});
			}
		}
		if (this.showSkuProps && !!this.obSkuProps)
		{
			if (0 === this.offers[index].DISPLAY_PROPERTIES.length)
			{
				BX.adjust(this.obSkuProps, {style: {display: 'none'}, html: ''});
			}
			else
			{
				BX.adjust(this.obSkuProps, {style: {display: ''}, html: this.offers[index].DISPLAY_PROPERTIES});
			}
		}
		this.setPrice(this.offers[index].PRICE);
		this.offerNum = index;
		this.QuantitySet(this.offerNum);
	}
};

window.JCCatalogTopSection.prototype.setPrice = function(price)
{
	var strPrice,
		obData;

	if (!!this.obPrice)
	{
		strPrice = BX.Currency.currencyFormat(price.DISCOUNT_VALUE, price.CURRENCY, true);
		if (this.showOldPrice && (price.DISCOUNT_VALUE !== price.VALUE))
		{
			strPrice += ' <span>'+BX.Currency.currencyFormat(price.VALUE, price.CURRENCY, true)+'</span>';
		}
		BX.adjust(this.obPrice, {html: strPrice});
		if (this.showPercent)
		{
			if (price.DISCOUNT_VALUE !== price.VALUE)
			{
				obData = {
					style: {
						display: ''
					},
					html: price.DISCOUNT_DIFF_PERCENT
				};
			}
			else
			{
				obData = {
					style: {
						display: 'none'
					},
					html: ''
				};
			}
			if (!!this.obDscPerc)
			{
				BX.adjust(this.obDscPerc, obData);
			}
			if (!!this.obSecondDscPerc)
			{
				BX.adjust(this.obSecondDscPerc, obData);
			}
		}
	}
};

window.JCCatalogTopSection.prototype.Compare = function()
{
	var compareParams, compareLink;
	if (!!this.compareData.compareUrl)
	{
		switch (this.productType)
		{
			case 1://product
			case 2://set
				compareLink = this.compareData.compareUrl.replace('#ID#', this.product.id.toString());
				break;
			case 3://sku
				compareLink = this.compareData.compareUrl.replace('#ID#', this.offers[this.offerNum].ID);
				break;
		}
		compareParams = {
			ajax_action: 'Y'
		};
		BX.ajax.loadJSON(
			compareLink,
			compareParams,
			BX.proxy(this.CompareResult, this)
		);
	}
};

window.JCCatalogTopSection.prototype.CompareResult = function(result)
{
	var popupContent, popupButtons, popupTitle;
	if (!!this.obPopupWin)
	{
		this.obPopupWin.close();
	}
	if (typeof result !== 'object')
	{
		return false;
	}
	this.InitPopupWindow();
	popupTitle = {
		content: BX.create('div', {
			style: { marginRight: '30px', whiteSpace: 'nowrap' },
			text: BX.message('COMPARE_TITLE')
		})
	};
	if (result.STATUS === 'OK')
	{
		BX.onCustomEvent('OnCompareChange');
		popupContent = '<div style="width: 96%; margin: 10px 2%; text-align: center;"><p>'+BX.message('COMPARE_MESSAGE_OK')+'</p></div>';
		if (this.showClosePopup)
		{
			popupButtons = [
				new BasketButton({
					ownerClass: this.obProduct.parentNode.parentNode.className,
					text: BX.message('BTN_MESSAGE_COMPARE_REDIRECT'),
					events: {
						click: BX.delegate(this.CompareRedirect, this)
					},
					style: {marginRight: '10px'}
				}),
				new BasketButton({
					ownerClass: this.obProduct.parentNode.parentNode.className,
					text: BX.message('BTN_MESSAGE_CLOSE_POPUP'),
					events: {
						click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
					}
				})
			];
		}
		else
		{
			popupButtons = [
				new BasketButton({
					ownerClass: this.obProduct.parentNode.parentNode.className,
					text: BX.message('BTN_MESSAGE_COMPARE_REDIRECT'),
					events: {
						click: BX.delegate(this.CompareRedirect, this)
					}
				})
			];
		}
	}
	else
	{
		popupContent = '<div style="width: 96%; margin: 10px 2%; text-align: center;"><p>'+(!!result.MESSAGE ? result.MESSAGE : BX.message('COMPARE_UNKNOWN_ERROR'))+'</p></div>';
		popupButtons = [
			new BasketButton({
				ownerClass: this.obProduct.parentNode.parentNode.className,
				text: BX.message('BTN_MESSAGE_CLOSE'),
				events: {
					click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
				}

			})
		];
	}
	this.obPopupWin.setTitleBar(popupTitle);
	this.obPopupWin.setContent(popupContent);
	this.obPopupWin.setButtons(popupButtons);
	this.obPopupWin.show();
	return false;
};

window.JCCatalogTopSection.prototype.CompareRedirect = function()
{
	if (!!this.compareData.comparePath)
	{
		location.href = this.compareData.comparePath;
	}
	else
	{
		this.obPopupWin.close();
	}
};

window.JCCatalogTopSection.prototype.InitBasketUrl = function()
{
	this.basketUrl = (this.basketMode === 'ADD' ? this.basketData.add_url : this.basketData.buy_url);
	switch (this.productType)
	{
		case 1://product
		case 2://set
			this.basketUrl = this.basketUrl.replace('#ID#', this.product.id.toString());
			break;
		case 3://sku
			this.basketUrl = this.basketUrl.replace('#ID#', this.offers[this.offerNum].ID);
			break;
	}
	this.basketParams = {
		'ajax_basket': 'Y'
	};
	if (this.showQuantity)
	{
		this.basketParams[this.basketData.quantity] = this.obQuantity.value;
	}
	if (!!this.basketData.sku_props)
	{
		this.basketParams[this.basketData.sku_props_var] = this.basketData.sku_props;
	}
};

window.JCCatalogTopSection.prototype.FillBasketProps = function()
{
	if (!this.visual.BASKET_PROP_DIV)
	{
		return;
	}
	var
		i = 0,
		propCollection = null,
		foundValues = false,
		obBasketProps = null;

	if (this.basketData.useProps && !this.basketData.emptyProps)
	{
		if (!!this.obPopupWin && !!this.obPopupWin.contentContainer)
		{
			obBasketProps = this.obPopupWin.contentContainer;
		}
	}
	else
	{
		obBasketProps = BX(this.visual.BASKET_PROP_DIV);
	}
	if (!!obBasketProps)
	{
		propCollection = obBasketProps.getElementsByTagName('select');
		if (!!propCollection && !!propCollection.length)
		{
			for (i = 0; i < propCollection.length; i++)
			{
				if (!propCollection[i].disabled)
				{
					switch(propCollection[i].type.toLowerCase())
					{
						case 'select-one':
							this.basketParams[propCollection[i].name] = propCollection[i].value;
							foundValues = true;
							break;
						default:
							break;
					}
				}
			}
		}
		propCollection = obBasketProps.getElementsByTagName('input');
		if (!!propCollection && !!propCollection.length)
		{
			for (i = 0; i < propCollection.length; i++)
			{
				if (!propCollection[i].disabled)
				{
					switch(propCollection[i].type.toLowerCase())
					{
						case 'hidden':
							this.basketParams[propCollection[i].name] = propCollection[i].value;
							foundValues = true;
							break;
						case 'radio':
							if (propCollection[i].checked)
							{
								this.basketParams[propCollection[i].name] = propCollection[i].value;
								foundValues = true;
							}
							break;
						default:
							break;
					}
				}
			}
		}
	}
	if (!foundValues)
	{
		this.basketParams[this.basketData.props] = [];
		this.basketParams[this.basketData.props][0] = 0;
	}
};

window.JCCatalogTopSection.prototype.Add2Basket = function()
{
	this.basketMode = 'ADD';
	this.Basket();
};

window.JCCatalogTopSection.prototype.BuyBasket = function()
{
	this.basketMode = 'BUY';
	this.Basket();
};

window.JCCatalogTopSection.prototype.SendToBasket = function()
{
	if (!this.canBuy)
	{
		return;
	}
	this.InitBasketUrl();
	this.FillBasketProps();
	BX.ajax.loadJSON(
		this.basketUrl,
		this.basketParams,
		BX.delegate(this.BasketResult, this)
	);
};

window.JCCatalogTopSection.prototype.Basket = function()
{
	var contentBasketProps = '';
	if (!this.canBuy)
	{
		return;
	}
	switch (this.productType)
	{
		case 1://product
		case 2://set
			if (this.basketData.useProps && !this.basketData.emptyProps)
			{
				this.InitPopupWindow();
				this.obPopupWin.setTitleBar({
					content: BX.create('div', {
						style: { marginRight: '30px', whiteSpace: 'nowrap' },
						text: BX.message('TITLE_BASKET_PROPS')
					})
				});
				if (BX(this.visual.BASKET_PROP_DIV))
				{
					contentBasketProps = BX(this.visual.BASKET_PROP_DIV).innerHTML;
				}
				this.obPopupWin.setContent(contentBasketProps);
				this.obPopupWin.setButtons([
					new BasketButton({
						ownerClass: this.obProduct.parentNode.parentNode.className,
						text: BX.message('BTN_MESSAGE_SEND_PROPS'),
						events: {
							click: BX.delegate(this.SendToBasket, this)
						}
					})
				]);
				this.obPopupWin.show();
			}
			else
			{
				this.SendToBasket();
			}
			break;
		case 3://sku
			this.SendToBasket();
			break;
	}
};

window.JCCatalogTopSection.prototype.BasketResult = function(arResult)
{
	var strContent = '',
		strPict = '',
		successful,
		buttons = [];

	if (!!this.obPopupWin)
	{
		this.obPopupWin.close();
	}
	if ('object' !== typeof arResult)
	{
		return false;
	}
	successful = (arResult.STATUS === 'OK');
	if (successful && this.basketAction === 'BUY')
	{
		this.BasketRedirect();
	}
	else
	{
		this.InitPopupWindow();
		if (successful)
		{
			BX.onCustomEvent('OnBasketChange');
			switch(this.productType)
			{
				case 1://
				case 2://
					strPict = this.product.pict.SRC;
					break;
				case 3:
					strPict = (!!this.offers[this.offerNum].PREVIEW_PICTURE ?
						this.offers[this.offerNum].PREVIEW_PICTURE.SRC :
						this.defaultPict.pict.SRC
						);
					break;
			}
			strContent = '<div style="width: 96%; margin: 10px 2%; text-align: center;"><img src="'+strPict+'" height="130"><p>'+this.product.name+'</p></div>';
			if (this.showClosePopup)
			{
				buttons = [
					new BasketButton({
						ownerClass: this.obProduct.parentNode.parentNode.className,
						text: BX.message("BTN_MESSAGE_BASKET_REDIRECT"),
						events: {
							click: BX.delegate(this.BasketRedirect, this)
						},
						style: {marginRight: '10px'}
					}),
					new BasketButton({
						ownerClass: this.obProduct.parentNode.parentNode.className,
						text: BX.message("BTN_MESSAGE_CLOSE_POPUP"),
						events: {
							click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
						}
					})
				];
			}
			else
			{
				buttons = [
					new BasketButton({
						ownerClass: this.obProduct.parentNode.parentNode.className,
						text: BX.message("BTN_MESSAGE_BASKET_REDIRECT"),
						events: {
							click: BX.delegate(this.BasketRedirect, this)
						}
					})
				];
			}
		}
		else
		{
			strContent = '<div style="width: 96%; margin: 10px 2%; text-align: center;"><p>'+(!!arResult.MESSAGE ? arResult.MESSAGE : BX.message('BASKET_UNKNOWN_ERROR'))+'</p></div>';
			buttons = [
				new BasketButton({
					ownerClass: this.obProduct.parentNode.parentNode.className,
					text: BX.message('BTN_MESSAGE_CLOSE'),
					events: {
						click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
					}
				})
			];
		}
		this.obPopupWin.setTitleBar({
			content: BX.create('div', {
				style: { marginRight: '30px', whiteSpace: 'nowrap' },
				text: (successful ? BX.message('TITLE_SUCCESSFUL') : BX.message('TITLE_ERROR'))
			})
		});
		this.obPopupWin.setContent(strContent);
		this.obPopupWin.setButtons(buttons);
		this.obPopupWin.show();
	}
};

window.JCCatalogTopSection.prototype.BasketRedirect = function()
{
	location.href = (!!this.basketData.basketUrl ? this.basketData.basketUrl : BX.message('BASKET_URL'));
};

window.JCCatalogTopSection.prototype.InitPopupWindow = function()
{
	if (!!this.obPopupWin)
	{
		return;
	}
	this.obPopupWin = BX.PopupWindowManager.create('CatalogSectionBasket_'+this.visual.ID, null, {
		autoHide: false,
		offsetLeft: 0,
		offsetTop: 0,
		overlay : true,
		closeByEsc: true,
		titleBar: true,
		closeIcon: {top: '10px', right: '10px'}
	});
};
})(window);
/* End */
;
; /* Start:"a:4:{s:4:"full";s:87:"/bitrix/components/bitrix/sale.bestsellers/templates/.default/script.js?143383955631755";s:6:"source";s:71:"/bitrix/components/bitrix/sale.bestsellers/templates/.default/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function (window) {

if (!!window.JCCatalogSectionBest)
{
	return;
}

var BasketButton = function(params)
{
	BasketButton.superclass.constructor.apply(this, arguments);
	this.nameNode = BX.create('span', {
		props : { className : 'bx_medium bx_bt_button', id : this.id },
		text: params.text
	});
	this.buttonNode = BX.create('span', {
		attrs: { className: params.ownerClass },
		style: { marginBottom: '0', borderBottom: '0 none transparent' },
		children: [this.nameNode],
		events : this.contextEvents
	});
	if (BX.browser.IsIE())
	{
		this.buttonNode.setAttribute("hideFocus", "hidefocus");
	}
};
BX.extend(BasketButton, BX.PopupWindowButton);

window.JCCatalogSectionBest = function (arParams)
{
	this.productType = 0;
	this.showQuantity = true;
	this.showAbsent = true;
	this.secondPict = false;
	this.showOldPrice = false;
	this.showPercent = false;
	this.showSkuProps = false;
	this.visual = {
		ID: '',
		PICT_ID: '',
		SECOND_PICT_ID: '',
		QUANTITY_ID: '',
		QUANTITY_UP_ID: '',
		QUANTITY_DOWN_ID: '',
		PRICE_ID: '',
		DSC_PERC: '',
		SECOND_DSC_PERC: '',
		DISPLAY_PROP_DIV: '',
		BASKET_PROP_DIV: ''
	};
	this.product = {
		checkQuantity: false,
		maxQuantity: 0,
		stepQuantity: 1,
		isDblQuantity: false,
		canBuy: true,
		canSubscription: true,
		name: '',
		pict: {},
		id: 0,
		addUrl: '',
		buyUrl: ''
	};
	this.basketData = {
		useProps: false,
		emptyProps: false,
		quantity: 'quantity',
		props: 'prop',
		basketUrl: ''
	};

	this.defaultPict = {
		pict: null,
		secondPict: null
	};

	this.checkQuantity = false;
	this.maxQuantity = 0;
	this.stepQuantity = 1;
	this.isDblQuantity = false;
	this.canBuy = true;
	this.canSubscription = true;
	this.precision = 6;
	this.precisionFactor = Math.pow(10,this.precision);

	this.offers = [];
	this.offerNum = 0;
	this.treeProps = [];
	this.obTreeRows = [];
	this.showCount = [];
	this.showStart = [];
	this.selectedValues = {};

	this.obProduct = null;
	this.obQuantity = null;
	this.obQuantityUp = null;
	this.obQuantityDown = null;
	this.obPict = null;
	this.obSecondPict = null;
	this.obPrice = null;
	this.obTree = null;
	this.obBuyBtn = null;
	this.obDscPerc = null;
	this.obSecondDscPerc = null;
	this.obSkuProps = null;
	this.obMeasure = null;

	this.obPopupWin = null;
	this.basketUrl = '';
	this.basketParams = {};

	this.treeRowShowSize = 5;
	this.treeEnableArrow = { display: '', cursor: 'pointer', opacity: 1 };
	this.treeDisableArrow = { display: '', cursor: 'default', opacity:0.2 };

	this.lastElement = false;
	this.containerHeight = 0;

	this.errorCode = 0;

	if ('object' === typeof arParams)
	{
		this.productType = parseInt(arParams.PRODUCT_TYPE, 10);
		this.showQuantity = arParams.SHOW_QUANTITY;
		this.showAbsent = arParams.SHOW_ABSENT;
		this.secondPict = !!arParams.SECOND_PICT;
		this.showOldPrice = !!arParams.SHOW_OLD_PRICE;
		this.showPercent = !!arParams.SHOW_DISCOUNT_PERCENT;
		this.showSkuProps = !!arParams.SHOW_SKU_PROPS;

		this.visual = arParams.VISUAL;
		switch (this.productType)
		{
			case 1://product
			case 2://set
				if (!!arParams.PRODUCT && 'object' === typeof(arParams.PRODUCT))
				{
					if (this.showQuantity)
					{
						this.product.checkQuantity = arParams.PRODUCT.CHECK_QUANTITY;
						this.product.isDblQuantity = arParams.PRODUCT.QUANTITY_FLOAT;
						if (this.product.checkQuantity)
						{
							this.product.maxQuantity = (this.product.isDblQuantity ? parseFloat(arParams.PRODUCT.MAX_QUANTITY) : parseInt(arParams.PRODUCT.MAX_QUANTITY, 10));
						}
						this.product.stepQuantity = (this.product.isDblQuantity ? parseFloat(arParams.PRODUCT.STEP_QUANTITY) : parseInt(arParams.PRODUCT.STEP_QUANTITY, 10));

						this.checkQuantity = this.product.checkQuantity;
						this.isDblQuantity = this.product.isDblQuantity;
						this.maxQuantity = this.product.maxQuantity;
						this.stepQuantity = this.product.stepQuantity;
						if (this.isDblQuantity)
						{
							this.stepQuantity = Math.round(this.stepQuantity*this.precisionFactor)/this.precisionFactor;
						}
					}
					this.product.canBuy = arParams.PRODUCT.CAN_BUY;
					this.product.canSubscription = arParams.PRODUCT.SUBSCRIPTION;

					this.canBuy = this.product.canBuy;
					this.canSubscription = this.product.canSubscription;

					this.product.name = arParams.PRODUCT.NAME;
					this.product.pict = arParams.PRODUCT.PICT;
					this.product.id = arParams.PRODUCT.ID;
					if (!!arParams.PRODUCT.ADD_URL)
					{
						this.product.addUrl = arParams.PRODUCT.ADD_URL;
					}
					if (!!arParams.PRODUCT.BUY_URL)
					{
						this.product.buyUrl = arParams.PRODUCT.BUY_URL;
					}
					if (!!arParams.BASKET && 'object' === typeof(arParams.BASKET))
					{
						this.basketData.useProps = !!arParams.BASKET.ADD_PROPS;
						this.basketData.emptyProps = !!arParams.BASKET.EMPTY_PROPS;
					}
				}
				else
				{
					this.errorCode = -1;
				}
				break;
			case 3://sku
				if (!!arParams.OFFERS && BX.type.isArray(arParams.OFFERS))
				{
					if (!!arParams.PRODUCT && 'object' === typeof(arParams.PRODUCT))
					{
						this.product.name = arParams.PRODUCT.NAME;
						this.product.id = arParams.PRODUCT.ID;
					}
					this.offers = arParams.OFFERS;
					this.offerNum = 0;
					if (!!arParams.OFFER_SELECTED)
					{
						this.offerNum = parseInt(arParams.OFFER_SELECTED, 10);
					}
					if (isNaN(this.offerNum))
					{
						this.offerNum = 0;
					}
					if (!!arParams.TREE_PROPS)
					{
						this.treeProps = arParams.TREE_PROPS;
					}
					if (!!arParams.DEFAULT_PICTURE)
					{
						this.defaultPict.pict = arParams.DEFAULT_PICTURE.PICTURE;
						this.defaultPict.secondPict = arParams.DEFAULT_PICTURE.PICTURE_SECOND;
					}
				}
				else
				{
					this.errorCode = -1;
				}
				break;
			default:
				this.errorCode = -1;
		}
		if (!!arParams.BASKET && 'object' === typeof(arParams.BASKET))
		{
			if (!!arParams.BASKET.QUANTITY)
			{
				this.basketData.quantity = arParams.BASKET.QUANTITY;
			}
			if (!!arParams.BASKET.PROPS)
			{
				this.basketData.props = arParams.BASKET.PROPS;
			}
			if (!!arParams.BASKET.BASKET_URL)
			{
				this.basketData.basketUrl = arParams.BASKET.BASKET_URL;
			}
		}
		this.lastElement = (!!arParams.LAST_ELEMENT && 'Y' === arParams.LAST_ELEMENT);
	}
	if (0 === this.errorCode)
	{
		BX.ready(BX.delegate(this.Init,this));
	}
};

window.JCCatalogSectionBest.prototype.Init = function()
{
	var i = 0,
		strPrefix = '',
		TreeItems = null;

	this.obProduct = BX(this.visual.ID);
	if (!this.obProduct)
	{
		this.errorCode = -1;
	}
	this.obPict = BX(this.visual.PICT_ID);
	if (!this.obPict)
	{
		this.errorCode = -2;
	}
	if (this.secondPict && !!this.visual.SECOND_PICT_ID)
	{
		this.obSecondPict = BX(this.visual.SECOND_PICT_ID);
	}
	this.obPrice = BX(this.visual.PRICE_ID);
	if (!this.obPrice)
	{
		this.errorCode = -16;
	}
	if (this.showQuantity && !!this.visual.QUANTITY_ID)
	{
		this.obQuantity = BX(this.visual.QUANTITY_ID);
		if (!!this.visual.QUANTITY_UP_ID)
		{
			this.obQuantityUp = BX(this.visual.QUANTITY_UP_ID);
		}
		if (!!this.visual.QUANTITY_DOWN_ID)
		{
			this.obQuantityDown = BX(this.visual.QUANTITY_DOWN_ID);
		}
	}
	if (3 === this.productType)
	{
		if (!!this.visual.TREE_ID)
		{
			this.obTree = BX(this.visual.TREE_ID);
			if (!this.obTree)
			{
				this.errorCode = -256;
			}
			strPrefix = this.visual.TREE_ITEM_ID;
			for (i = 0; i < this.treeProps.length; i++)
			{
				this.obTreeRows[i] = {
					LEFT: BX(strPrefix+this.treeProps[i].ID+'_left'),
					RIGHT: BX(strPrefix+this.treeProps[i].ID+'_right'),
					LIST: BX(strPrefix+this.treeProps[i].ID+'_list'),
					CONT: BX(strPrefix+this.treeProps[i].ID+'_cont')
				};
				if (!this.obTreeRows[i].LEFT || !this.obTreeRows[i].RIGHT || !this.obTreeRows[i].LIST || !this.obTreeRows[i].CONT)
				{
					this.errorCode = -512;
					break;
				}
			}
		}
		if (!!this.visual.QUANTITY_MEASURE)
		{
			this.obMeasure = BX(this.visual.QUANTITY_MEASURE);
		}
	}
	if (!!this.visual.BUY_ID)
	{
		this.obBuyBtn = BX(this.visual.BUY_ID);
	}

	if (this.showPercent)
	{
		if (!!this.visual.DSC_PERC)
		{
			this.obDscPerc = BX(this.visual.DSC_PERC);
		}
		if (this.secondPict && !!this.visual.SECOND_DSC_PERC)
		{
			this.obSecondDscPerc = BX(this.visual.SECOND_DSC_PERC);
		}
	}

	if (this.showSkuProps)
	{
		if (!!this.visual.DISPLAY_PROP_DIV)
		{
			this.obSkuProps = BX(this.visual.DISPLAY_PROP_DIV);
		}
	}

	if (0 === this.errorCode)
	{
		if (this.showQuantity)
		{
			if (!!this.obQuantityUp)
			{
				BX.bind(this.obQuantityUp, 'click', BX.delegate(this.QuantityUp, this));
			}
			if (!!this.obQuantityDown)
			{
				BX.bind(this.obQuantityDown, 'click', BX.delegate(this.QuantityDown, this));
			}
			if (!!this.obQuantity)
			{
				BX.bind(this.obQuantity, 'change', BX.delegate(this.QuantityChange, this));
			}
		}
		switch (this.productType)
		{
			case 1://product
				break;
			case 3://sku
				TreeItems = BX.findChildren(this.obTree, {tagName: 'li'}, true);
				if (!!TreeItems && 0 < TreeItems.length)
				{
					for (i = 0; i < TreeItems.length; i++)
					{
						BX.bind(TreeItems[i], 'click', BX.delegate(this.SelectOfferProp, this));
					}
				}
				for (i = 0; i < this.obTreeRows.length; i++)
				{
					BX.bind(this.obTreeRows[i].LEFT, 'click', BX.delegate(this.RowLeft, this));
					BX.bind(this.obTreeRows[i].RIGHT, 'click', BX.delegate(this.RowRight, this));
				}
				this.SetCurrent();
				break;
		}
		if (!!this.obBuyBtn)
		{
			BX.bind(this.obBuyBtn, 'click', BX.delegate(this.Basket, this));
		}
		if (this.lastElement)
		{
			this.containerHeight = parseInt(this.obProduct.parentNode.offsetHeight, 10);
			if (isNaN(this.containerHeight))
			{
				this.containerHeight = 0;
			}
			this.setHeight();
			BX.bind(window, 'resize', BX.delegate(this.checkHeight, this));
			BX.bind(this.obProduct.parentNode, 'mouseover', BX.delegate(this.setHeight, this));
			BX.bind(this.obProduct.parentNode, 'mouseout', BX.delegate(this.clearHeight, this));
		}
	}
};

window.JCCatalogSectionBest.prototype.checkHeight = function()
{
	this.containerHeight = parseInt(this.obProduct.parentNode.offsetHeight, 10);
	if (isNaN(this.containerHeight))
	{
		this.containerHeight = 0;
	}
};

window.JCCatalogSectionBest.prototype.setHeight = function()
{
	if (0 < this.containerHeight)
	{
		BX.adjust(this.obProduct.parentNode, {style: { height: this.containerHeight+'px'}});
	}
};

window.JCCatalogSectionBest.prototype.clearHeight = function()
{
	BX.adjust(this.obProduct.parentNode, {style: { height: 'auto'}});
};

window.JCCatalogSectionBest.prototype.QuantityUp = function()
{
	var curValue = 0,
		boolSet = true;

	if (0 === this.errorCode && this.showQuantity && this.canBuy)
	{
		curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value) : parseInt(this.obQuantity.value, 10));
		if (!isNaN(curValue))
		{
			curValue += this.stepQuantity;
			if (this.checkQuantity)
			{
				if (curValue > this.maxQuantity)
				{
					boolSet = false;
				}
			}
			if (boolSet)
			{
				if (this.isDblQuantity)
				{
					curValue = Math.round(curValue*this.precisionFactor)/this.precisionFactor;
				}
				this.obQuantity.value = curValue;
			}
		}
	}
};

window.JCCatalogSectionBest.prototype.QuantityDown = function()
{
	var curValue = 0,
		boolSet = true;

	if (0 === this.errorCode && this.showQuantity && this.canBuy)
	{
		curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value): parseInt(this.obQuantity.value, 10));
		if (!isNaN(curValue))
		{
			curValue -= this.stepQuantity;
			if (curValue < this.stepQuantity)
			{
				boolSet = false;
			}
			if (boolSet)
			{
				if (this.isDblQuantity)
				{
					curValue = Math.round(curValue*this.precisionFactor)/this.precisionFactor;
				}
				this.obQuantity.value = curValue;
			}
		}
	}
};

window.JCCatalogSectionBest.prototype.QuantityChange = function()
{
	var curValue = 0,
		boolSet = true;

	if (0 === this.errorCode && this.showQuantity)
	{
		if (this.canBuy)
		{
			curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value) : parseInt(this.obQuantity.value, 10));
			if (!isNaN(curValue))
			{
				if (this.checkQuantity)
				{
					if (curValue > this.maxQuantity)
					{
						boolSet = false;
						curValue = this.maxQuantity;
					}
					else if (curValue < this.stepQuantity)
					{
						boolSet = false;
						curValue = this.stepQuantity;
					}
				}
				if (!boolSet)
				{
					this.obQuantity.value = curValue;
				}
			}
			else
			{
				this.obQuantity.value = this.stepQuantity;
			}
		}
		else
		{
			this.obQuantity.value = this.stepQuantity;
		}
	}
};

window.JCCatalogSectionBest.prototype.QuantitySet = function(index)
{
	if (0 === this.errorCode)
	{
		this.canBuy = this.offers[index].CAN_BUY;
		if (this.canBuy)
		{
			BX.addClass(this.obBuyBtn, 'bx_bt_button');
			BX.removeClass(this.obBuyBtn, 'bx_bt_button_type_2');
			this.obBuyBtn.innerHTML = BX.message('MESS_BTN_BUY');
		}
		else
		{
			BX.addClass(this.obBuyBtn, 'bx_bt_button_type_2');
			BX.removeClass(this.obBuyBtn, 'bx_bt_button');
			this.obBuyBtn.innerHTML = BX.message('MESS_NOT_AVAILABLE');
		}
		if (this.showQuantity)
		{
			this.isDblQuantity = this.offers[index].QUANTITY_FLOAT;
			this.checkQuantity = this.offers[index].CHECK_QUANTITY;
			if (this.isDblQuantity)
			{
				this.maxQuantity = parseFloat(this.offers[index].MAX_QUANTITY);
				this.stepQuantity = Math.round(parseFloat(this.offers[index].STEP_QUANTITY)*this.precisionFactor)/this.precisionFactor;
			}
			else
			{
				this.maxQuantity = parseInt(this.offers[index].MAX_QUANTITY, 10);
				this.stepQuantity = parseInt(this.offers[index].STEP_QUANTITY, 10);
			}

			this.obQuantity.value = this.stepQuantity;
			this.obQuantity.disabled = !this.canBuy;
			if (!!this.obMeasure)
			{
				if (!!this.offers[index].MEASURE)
				{
					BX.adjust(this.obMeasure, { html : this.offers[index].MEASURE});
				}
				else
				{
					BX.adjust(this.obMeasure, { html : ''});
				}
			}
		}
	}
};

window.JCCatalogSectionBest.prototype.SelectOfferProp = function()
{
	var i = 0,
		value = '',
		strTreeValue = '',
		arTreeItem = [],
		RowItems = null,
		target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-treevalue'))
	{
		strTreeValue = target.getAttribute('data-treevalue');
		arTreeItem = strTreeValue.split('_');
		if (this.SearchOfferPropIndex(arTreeItem[0], arTreeItem[1]))
		{
			RowItems = BX.findChildren(target.parentNode, {tagName: 'li'}, false);
			if (!!RowItems && 0 < RowItems.length)
			{
				for (i = 0; i < RowItems.length; i++)
				{
					value = RowItems[i].getAttribute('data-onevalue');
					if (value === arTreeItem[1])
					{
						BX.addClass(RowItems[i], 'bx_active');
					}
					else
					{
						BX.removeClass(RowItems[i], 'bx_active');
					}
				}
			}
		}
	}
};

window.JCCatalogSectionBest.prototype.SearchOfferPropIndex = function(strPropID, strPropValue)
{
	var strName = '',
		arShowValues = false,
		i, j,
		arCanBuyValues = [],
		index = -1,
		arFilter = {},
		tmpFilter = [];

	for (i = 0; i < this.treeProps.length; i++)
	{
		if (this.treeProps[i].ID === strPropID)
		{
			index = i;
			break;
		}
	}

	if (-1 < index)
	{
		for (i = 0; i < index; i++)
		{
			strName = 'PROP_'+this.treeProps[i].ID;
			arFilter[strName] = this.selectedValues[strName];
		}
		strName = 'PROP_'+this.treeProps[index].ID;
		arShowValues = this.GetRowValues(arFilter, strName);
		if (!arShowValues)
		{
			return false;
		}
		if (!BX.util.in_array(strPropValue, arShowValues))
		{
			return false;
		}
		arFilter[strName] = strPropValue;
		for (i = index+1; i < this.treeProps.length; i++)
		{
			strName = 'PROP_'+this.treeProps[i].ID;
			arShowValues = this.GetRowValues(arFilter, strName);
			if (!arShowValues)
			{
				return false;
			}
			if (this.showAbsent)
			{
				arCanBuyValues = [];
				tmpFilter = [];
				tmpFilter = BX.clone(arFilter, true);
				for (j = 0; j < arShowValues.length; j++)
				{
					tmpFilter[strName] = arShowValues[j];
					if (this.GetCanBuy(tmpFilter))
					{
						arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
					}
				}
			}
			else
			{
				arCanBuyValues = arShowValues;
			}
			if (!!this.selectedValues[strName] && BX.util.in_array(this.selectedValues[strName], arCanBuyValues))
			{
				arFilter[strName] = this.selectedValues[strName];
			}
			else
			{
				arFilter[strName] = arCanBuyValues[0];
			}
			this.UpdateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
		}
		this.selectedValues = arFilter;
		this.ChangeInfo();
	}
	return true;
};

window.JCCatalogSectionBest.prototype.RowLeft = function()
{
	var i = 0,
		strTreeValue = '',
		index = -1,
		target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-treevalue'))
	{
		strTreeValue = target.getAttribute('data-treevalue');
		for (i = 0; i < this.treeProps.length; i++)
		{
			if (this.treeProps[i].ID === strTreeValue)
			{
				index = i;
				break;
			}
		}
		if (-1 < index && this.treeRowShowSize < this.showCount[index])
		{
			if (0 > this.showStart[index])
			{
				this.showStart[index]++;
				BX.adjust(this.obTreeRows[index].LIST, { style: { marginLeft: this.showStart[index]*20+'%' }});
				BX.adjust(this.obTreeRows[index].RIGHT, { style: this.treeEnableArrow });
			}

			if (0 <= this.showStart[index])
			{
				BX.adjust(this.obTreeRows[index].LEFT, { style: this.treeDisableArrow });
			}
		}
	}
};

window.JCCatalogSectionBest.prototype.RowRight = function()
{
	var i = 0,
		strTreeValue = '',
		index = -1,
		target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-treevalue'))
	{
		strTreeValue = target.getAttribute('data-treevalue');
		for (i = 0; i < this.treeProps.length; i++)
		{
			if (this.treeProps[i].ID === strTreeValue)
			{
				index = i;
				break;
			}
		}
		if (-1 < index && this.treeRowShowSize < this.showCount[index])
		{
			if ((this.treeRowShowSize - this.showStart[index]) < this.showCount[index])
			{
				this.showStart[index]--;
				BX.adjust(this.obTreeRows[index].LIST, { style: { marginLeft: this.showStart[index]*20+'%' }});
				BX.adjust(this.obTreeRows[index].LEFT, { style: this.treeEnableArrow });
			}

			if ((this.treeRowShowSize - this.showStart[index]) >= this.showCount[index])
			{
				BX.adjust(this.obTreeRows[index].RIGHT, { style: this.treeDisableArrow });
			}
		}
	}
};

window.JCCatalogSectionBest.prototype.UpdateRow = function(intNumber, activeID, showID, canBuyID)
{
	var i = 0,
		showI = 0,
		value = '',
		countShow = 0,
		strNewLen = '',
		obData = {},
		pictMode = false,
		extShowMode = false,
		isCurrent = false,
		selectIndex = 0,
		obLeft = this.treeEnableArrow,
		obRight = this.treeEnableArrow,
		currentShowStart = 0,
		RowItems = null;

	if (-1 < intNumber && intNumber < this.obTreeRows.length)
	{
		RowItems = BX.findChildren(this.obTreeRows[intNumber].LIST, {tagName: 'li'}, false);
		if (!!RowItems && 0 < RowItems.length)
		{
			pictMode = ('PICT' === this.treeProps[intNumber].SHOW_MODE);
			countShow = showID.length;
			extShowMode = this.treeRowShowSize < countShow;
			strNewLen = (extShowMode ? (100/countShow)+'%' : '20%');
			obData = {
				props: { className: '' },
				style: {
					width: strNewLen
				}
			};
			if (pictMode)
			{
				obData.style.paddingTop = strNewLen;
			}
			for (i = 0; i < RowItems.length; i++)
			{
				value = RowItems[i].getAttribute('data-onevalue');
				isCurrent = (value === activeID);
				if (BX.util.in_array(value, canBuyID))
				{
					obData.props.className = (isCurrent ? 'bx_active' : '');
				}
				else
				{
					obData.props.className = (isCurrent ? 'bx_active bx_missing' : 'bx_missing');
				}
				obData.style.display = 'none';
				if (BX.util.in_array(value, showID))
				{
					obData.style.display = '';
					if (isCurrent)
					{
						selectIndex = showI;
					}
					showI++;
				}
				BX.adjust(RowItems[i], obData);
			}

			obData = {
				style: {
					width: (extShowMode ? 20*countShow : 100)+'%',
					marginLeft: '0%'
				}
			};
			if (pictMode)
			{
				BX.adjust(this.obTreeRows[intNumber].CONT, {props: {className: (extShowMode ? 'bx_item_detail_scu full' : 'bx_item_detail_scu')}});
			}
			else
			{
				BX.adjust(this.obTreeRows[intNumber].CONT, {props: {className: (extShowMode ? 'bx_item_detail_size full' : 'bx_item_detail_size')}});
			}
			if (extShowMode)
			{
				if (selectIndex +1 === countShow)
				{
					obRight = this.treeDisableArrow;
				}
				if (this.treeRowShowSize <= selectIndex)
				{
					currentShowStart = this.treeRowShowSize - selectIndex - 1;
					obData.style.marginLeft = currentShowStart*20+'%';
				}
				if (0 === currentShowStart)
				{
					obLeft = this.treeDisableArrow;
				}
				BX.adjust(this.obTreeRows[intNumber].LEFT, {style: obLeft });
				BX.adjust(this.obTreeRows[intNumber].RIGHT, {style: obRight });
			}
			else
			{
				BX.adjust(this.obTreeRows[intNumber].LEFT, {style: {display: 'none'}});
				BX.adjust(this.obTreeRows[intNumber].RIGHT, {style: {display: 'none'}});
			}
			BX.adjust(this.obTreeRows[intNumber].LIST, obData);
			this.showCount[intNumber] = countShow;
			this.showStart[intNumber] = currentShowStart;
		}
	}
};

window.JCCatalogSectionBest.prototype.GetRowValues = function(arFilter, index)
{
	var i = 0,
		j,
		arValues = [],
		boolSearch = false,
		boolOneSearch = true;

	if (0 === arFilter.length)
	{
		for (i = 0; i < this.offers.length; i++)
		{
			if (!BX.util.in_array(this.offers[i].TREE[index], arValues))
			{
				arValues[arValues.length] = this.offers[i].TREE[index];
			}
		}
		boolSearch = true;
	}
	else
	{
		for (i = 0; i < this.offers.length; i++)
		{
			boolOneSearch = true;
			for (j in arFilter)
			{
				if (arFilter[j] !== this.offers[i].TREE[j])
				{
					boolOneSearch = false;
					break;
				}
			}
			if (boolOneSearch)
			{
				if (!BX.util.in_array(this.offers[i].TREE[index], arValues))
				{
					arValues[arValues.length] = this.offers[i].TREE[index];
				}
				boolSearch = true;
			}
		}
	}
	return (boolSearch ? arValues : false);
};

window.JCCatalogSectionBest.prototype.GetCanBuy = function(arFilter)
{
	var i = 0,
		j,
		boolSearch = false,
		boolOneSearch = true;

	for (i = 0; i < this.offers.length; i++)
	{
		boolOneSearch = true;
		for (j in arFilter)
		{
			if (arFilter[j] !== this.offers[i].TREE[j])
			{
				boolOneSearch = false;
				break;
			}
		}
		if (boolOneSearch)
		{
			if (this.offers[i].CAN_BUY)
			{
				boolSearch = true;
				break;
			}
		}
	}
	return boolSearch;
};

window.JCCatalogSectionBest.prototype.SetCurrent = function()
{
	var i = 0,
		j = 0,
		arCanBuyValues = [],
		strName = '',
		arShowValues = false,
		arFilter = {},
		tmpFilter = [],
		current = this.offers[this.offerNum].TREE;

	for (i = 0; i < this.treeProps.length; i++)
	{
		strName = 'PROP_'+this.treeProps[i].ID;
		arShowValues = this.GetRowValues(arFilter, strName);
		if (!arShowValues)
		{
			break;
		}
		if (BX.util.in_array(current[strName], arShowValues))
		{
			arFilter[strName] = current[strName];
		}
		else
		{
			arFilter[strName] = arShowValues[0];
			this.offerNum = 0;
		}
		if (this.showAbsent)
		{
			arCanBuyValues = [];
			tmpFilter = [];
			tmpFilter = BX.clone(arFilter, true);
			for (j = 0; j < arShowValues.length; j++)
			{
				tmpFilter[strName] = arShowValues[j];
				if (this.GetCanBuy(tmpFilter))
				{
					arCanBuyValues[arCanBuyValues.length] = arShowValues[j];
				}
			}
		}
		else
		{
			arCanBuyValues = arShowValues;
		}
		this.UpdateRow(i, arFilter[strName], arShowValues, arCanBuyValues);
	}
	this.selectedValues = arFilter;
	this.ChangeInfo();
};

window.JCCatalogSectionBest.prototype.ChangeInfo = function()
{
	var i = 0,
		j,
		index = -1,
		obData = {},
		boolOneSearch = true,
		strPrice = '';

	for (i = 0; i < this.offers.length; i++)
	{
		boolOneSearch = true;
		for (j in this.selectedValues)
		{
			if (this.selectedValues[j] !== this.offers[i].TREE[j])
			{
				boolOneSearch = false;
				break;
			}
		}
		if (boolOneSearch)
		{
			index = i;
			break;
		}
	}
	if (-1 < index)
	{
		if (!!this.obPict)
		{
			if (!!this.offers[index].PREVIEW_PICTURE)
			{
				BX.adjust(this.obPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE.SRC+')'}});
			}
			else
			{
				BX.adjust(this.obPict, {style: {backgroundImage: 'url('+this.defaultPict.pict.SRC+')'}});
			}
		}
		if (this.secondPict && !!this.obSecondPict)
		{
			if (!!this.offers[index].PREVIEW_PICTURE_SECOND)
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE_SECOND.SRC+')'}});
			}
			else if (!!this.offers[index].PREVIEW_PICTURE.SRC)
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.offers[index].PREVIEW_PICTURE.SRC+')'}});
			}
			else if (!!this.defaultPict.secondPict)
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.defaultPict.secondPict.SRC+')'}});
			}
			else
			{
				BX.adjust(this.obSecondPict, {style: {backgroundImage: 'url('+this.defaultPict.pict.SRC+')'}});
			}
		}
		if (this.showSkuProps && !!this.obSkuProps)
		{
			if (0 === this.offers[index].DISPLAY_PROPERTIES.length)
			{
				BX.adjust(this.obSkuProps, {style: {display: 'none'}, html: ''});
			}
			else
			{
				BX.adjust(this.obSkuProps, {style: {display: ''}, html: this.offers[index].DISPLAY_PROPERTIES});
			}
		}
		if (!!this.obPrice)
		{
			strPrice = this.offers[index].PRICE.PRINT_DISCOUNT_VALUE;
			if (this.showOldPrice && (this.offers[index].PRICE.DISCOUNT_VALUE !== this.offers[index].PRICE.VALUE))
			{
				strPrice += ' <span>'+this.offers[index].PRICE.PRINT_VALUE+'</span>';
			}
			BX.adjust(this.obPrice, {html: strPrice});
			if (this.showPercent)
			{
				if (this.offers[index].PRICE.DISCOUNT_VALUE !== this.offers[index].PRICE.VALUE)
				{
					obData = {
						style: {
							display: ''
						},
						html: this.offers[index].PRICE.DISCOUNT_DIFF_PERCENT
					};
				}
				else
				{
					obData = {
						style: {
							display: 'none'
						},
						html: ''
					};
				}
				if (!!this.obDscPerc)
				{
					BX.adjust(this.obDscPerc, obData);
				}
				if (!!this.obSecondDscPerc)
				{
					BX.adjust(this.obSecondDscPerc, obData);
				}
			}
		}
		this.offerNum = index;
		this.QuantitySet(this.offerNum);
	}
};

window.JCCatalogSectionBest.prototype.InitBasketUrl = function()
{
	switch (this.productType)
	{
		case 1://product
		case 2://set
			this.basketUrl = this.product.addUrl;
			break;
		case 3://sku
			this.basketUrl = this.offers[this.offerNum].ADD_URL;
			break;
	}
	this.basketParams = {
		'ajax_basket': 'Y'
	};
	if (this.showQuantity)
	{
		this.basketParams[this.basketData.quantity] = this.obQuantity.value;
	}
};

window.JCCatalogSectionBest.prototype.FillBasketProps = function()
{
	if (!this.visual.BASKET_PROP_DIV)
	{
		return;
	}
	var
		i = 0,
		propCollection = null,
		foundValues = false,
		obBasketProps = null;

	if (this.basketData.useProps && !this.basketData.emptyProps)
	{
		if (!!this.obPopupWin && !!this.obPopupWin.contentContainer)
		{
			obBasketProps = this.obPopupWin.contentContainer;
		}
	}
	else
	{
		obBasketProps = BX(this.visual.BASKET_PROP_DIV);
	}
	if (!obBasketProps)
	{
		return;
	}
	propCollection = obBasketProps.getElementsByTagName('select');
	if (!!propCollection && !!propCollection.length)
	{
		for (i = 0; i < propCollection.length; i++)
		{
			if (!propCollection[i].disabled)
			{
				switch(propCollection[i].type.toLowerCase())
				{
					case 'select-one':
						this.basketParams[propCollection[i].name] = propCollection[i].value;
						foundValues = true;
						break;
					default:
						break;
				}
			}
		}
	}
	propCollection = obBasketProps.getElementsByTagName('input');
	if (!!propCollection && !!propCollection.length)
	{
		for (i = 0; i < propCollection.length; i++)
		{
			if (!propCollection[i].disabled)
			{
				switch(propCollection[i].type.toLowerCase())
				{
					case 'hidden':
						this.basketParams[propCollection[i].name] = propCollection[i].value;
						foundValues = true;
						break;
					case 'radio':
						if (propCollection[i].checked)
						{
							this.basketParams[propCollection[i].name] = propCollection[i].value;
							foundValues = true;
						}
						break;
					default:
						break;
				}
			}
		}
	}
	if (!foundValues)
	{
		this.basketParams[this.basketData.props] = [];
		this.basketParams[this.basketData.props][0] = 0;
	}
};

window.JCCatalogSectionBest.prototype.SendToBasket = function()
{
	if (!this.canBuy)
	{
		return;
	}
	this.InitBasketUrl();
	this.FillBasketProps();
	BX.ajax.loadJSON(
		this.basketUrl,
		this.basketParams,
		BX.delegate(this.BasketResult, this)
	);
};

window.JCCatalogSectionBest.prototype.Basket = function()
{
	var contentBasketProps = '';
	if (!this.canBuy)
	{
		return;
	}
	switch (this.productType)
	{
	case 1://product
	case 2://set
		if (this.basketData.useProps && !this.basketData.emptyProps)
		{
			this.InitPopupWindow();
			this.obPopupWin.setTitleBar({
				content: BX.create('div', {
					style: { marginRight: '30px', whiteSpace: 'nowrap' },
					text: BX.message('TITLE_BASKET_PROPS')
				})
			});
			if (BX(this.visual.BASKET_PROP_DIV))
			{
				contentBasketProps = BX(this.visual.BASKET_PROP_DIV).innerHTML;
			}
			this.obPopupWin.setContent(contentBasketProps);
			this.obPopupWin.setButtons([
				new BasketButton({
					ownerClass: this.obProduct.parentNode.parentNode.parentNode.className,
					text: BX.message('BTN_MESSAGE_SEND_PROPS'),
					events: {
						click: BX.delegate(this.SendToBasket, this)
					}
				})
			]);
			this.obPopupWin.show();
		}
		else
		{
			this.SendToBasket();
		}
		break;
	case 3://sku
		this.SendToBasket();
		break;
	}
};

window.JCCatalogSectionBest.prototype.BasketResult = function(arResult)
{
	var strContent = '',
		strName = '',
		strPict = '',
		successful = true,
		buttons = [];

	if (!!this.obPopupWin)
	{
		this.obPopupWin.close();
	}
	if ('object' !== typeof arResult)
	{
		return false;
	}
	successful = ('OK' === arResult.STATUS);
	if (successful)
	{
		BX.onCustomEvent('OnBasketChange');
		strName = this.product.name;
		switch(this.productType)
		{
		case 1://
		case 2://
			strPict = this.product.pict.SRC;
			break;
		case 3:
			strPict = (!!this.offers[this.offerNum].PREVIEW_PICTURE ?
				this.offers[this.offerNum].PREVIEW_PICTURE.SRC :
				this.defaultPict.pict.SRC
			);
			break;
		}
		strContent = '<div style="width: 96%; margin: 10px 2%; text-align: center;"><img src="'+strPict+'" height="130"><p>'+strName+'</p></div>';
		buttons = [
			new BasketButton({
				ownerClass: this.obProduct.parentNode.parentNode.parentNode.className,
				text: BX.message("BTN_MESSAGE_BASKET_REDIRECT"),
				events: {
					click: BX.delegate(function(){
						location.href = (!!this.basketData.basketUrl ? this.basketData.basketUrl : BX.message('BASKET_URL'));
					}, this)
				}
			})
		];
	}
	else
	{
		strContent = (!!arResult.MESSAGE ? arResult.MESSAGE : BX.message('BASKET_UNKNOWN_ERROR'));
		buttons = [
			new BasketButton({
				ownerClass: this.obProduct.parentNode.parentNode.parentNode.className,
				text: BX.message('BTN_MESSAGE_CLOSE'),
				events: {
					click: BX.delegate(this.obPopupWin.close, this.obPopupWin)
				}
			})
		];
	}
	this.InitPopupWindow();
	this.obPopupWin.setTitleBar({
		content: BX.create('div', {
			style: { marginRight: '30px', whiteSpace: 'nowrap' },
			text: (successful ? BX.message('TITLE_SUCCESSFUL') : BX.message('TITLE_ERROR'))
		})
	});
	this.obPopupWin.setContent(strContent);
	this.obPopupWin.setButtons(buttons);
	this.obPopupWin.show();
};

window.JCCatalogSectionBest.prototype.InitPopupWindow = function()
{
	if (!!this.obPopupWin)
	{
		return;
	}
	this.obPopupWin = BX.PopupWindowManager.create('CatalogSectionBasket_'+this.visual.ID, null, {
		autoHide: false,
		offsetLeft: 0,
		offsetTop: 0,
		overlay : true,
		closeByEsc: true,
		titleBar: true,
		closeIcon: {top: '10px', right: '10px'}
	});
};
})(window);
/* End */
;
; /* Start:"a:4:{s:4:"full";s:98:"/bitrix/components/bitrix/catalog.viewed.products/templates/.default/script.min.js?143393747321421";s:6:"source";s:78:"/bitrix/components/bitrix/catalog.viewed.products/templates/.default/script.js";s:3:"min";s:82:"/bitrix/components/bitrix/catalog.viewed.products/templates/.default/script.min.js";s:3:"map";s:82:"/bitrix/components/bitrix/catalog.viewed.products/templates/.default/script.map.js";}"*/
(function(t){if(!!t.JCCatalogSectionViewed){return}var e=function(t){e.superclass.constructor.apply(this,arguments);this.nameNode=BX.create("span",{props:{className:"bx_medium bx_bt_button",id:this.id},text:t.text});this.buttonNode=BX.create("span",{attrs:{className:t.ownerClass},style:{marginBottom:"0",borderBottom:"0 none transparent"},children:[this.nameNode],events:this.contextEvents});if(BX.browser.IsIE()){this.buttonNode.setAttribute("hideFocus","hidefocus")}};BX.extend(e,BX.PopupWindowButton);t.JCCatalogSectionViewed=function(t){this.productType=0;this.showQuantity=true;this.showAbsent=true;this.secondPict=false;this.showOldPrice=false;this.showPercent=false;this.showSkuProps=false;this.visual={ID:"",PICT_ID:"",SECOND_PICT_ID:"",QUANTITY_ID:"",QUANTITY_UP_ID:"",QUANTITY_DOWN_ID:"",PRICE_ID:"",DSC_PERC:"",SECOND_DSC_PERC:"",DISPLAY_PROP_DIV:"",BASKET_PROP_DIV:""};this.product={checkQuantity:false,maxQuantity:0,stepQuantity:1,isDblQuantity:false,canBuy:true,canSubscription:true,name:"",pict:{},id:0,addUrl:"",buyUrl:""};this.basketData={useProps:false,emptyProps:false,quantity:"quantity",props:"prop",basketUrl:""};this.defaultPict={pict:null,secondPict:null};this.checkQuantity=false;this.maxQuantity=0;this.stepQuantity=1;this.isDblQuantity=false;this.canBuy=true;this.canSubscription=true;this.precision=6;this.precisionFactor=Math.pow(10,this.precision);this.offers=[];this.offerNum=0;this.treeProps=[];this.obTreeRows=[];this.showCount=[];this.showStart=[];this.selectedValues={};this.obProduct=null;this.obQuantity=null;this.obQuantityUp=null;this.obQuantityDown=null;this.obPict=null;this.obSecondPict=null;this.obPrice=null;this.obTree=null;this.obBuyBtn=null;this.obDscPerc=null;this.obSecondDscPerc=null;this.obSkuProps=null;this.obMeasure=null;this.obPopupWin=null;this.basketUrl="";this.basketParams={};this.treeRowShowSize=5;this.treeEnableArrow={display:"",cursor:"pointer",opacity:1};this.treeDisableArrow={display:"",cursor:"default",opacity:.2};this.lastElement=false;this.containerHeight=0;this.errorCode=0;if("object"===typeof t){this.productType=parseInt(t.PRODUCT_TYPE,10);this.showQuantity=t.SHOW_QUANTITY;this.showAbsent=t.SHOW_ABSENT;this.secondPict=!!t.SECOND_PICT;this.showOldPrice=!!t.SHOW_OLD_PRICE;this.showPercent=!!t.SHOW_DISCOUNT_PERCENT;this.showSkuProps=!!t.SHOW_SKU_PROPS;this.visual=t.VISUAL;switch(this.productType){case 1:case 2:if(!!t.PRODUCT&&"object"===typeof t.PRODUCT){if(this.showQuantity){this.product.checkQuantity=t.PRODUCT.CHECK_QUANTITY;this.product.isDblQuantity=t.PRODUCT.QUANTITY_FLOAT;if(this.product.checkQuantity){this.product.maxQuantity=this.product.isDblQuantity?parseFloat(t.PRODUCT.MAX_QUANTITY):parseInt(t.PRODUCT.MAX_QUANTITY,10)}this.product.stepQuantity=this.product.isDblQuantity?parseFloat(t.PRODUCT.STEP_QUANTITY):parseInt(t.PRODUCT.STEP_QUANTITY,10);this.checkQuantity=this.product.checkQuantity;this.isDblQuantity=this.product.isDblQuantity;this.maxQuantity=this.product.maxQuantity;this.stepQuantity=this.product.stepQuantity;if(this.isDblQuantity){this.stepQuantity=Math.round(this.stepQuantity*this.precisionFactor)/this.precisionFactor}}this.product.canBuy=t.PRODUCT.CAN_BUY;this.product.canSubscription=t.PRODUCT.SUBSCRIPTION;this.canBuy=this.product.canBuy;this.canSubscription=this.product.canSubscription;this.product.name=t.PRODUCT.NAME;this.product.pict=t.PRODUCT.PICT;this.product.id=t.PRODUCT.ID;if(!!t.PRODUCT.ADD_URL){this.product.addUrl=t.PRODUCT.ADD_URL}if(!!t.PRODUCT.BUY_URL){this.product.buyUrl=t.PRODUCT.BUY_URL}if(!!t.BASKET&&"object"===typeof t.BASKET){this.basketData.useProps=!!t.BASKET.ADD_PROPS;this.basketData.emptyProps=!!t.BASKET.EMPTY_PROPS}}else{this.errorCode=-1}break;case 3:if(!!t.OFFERS&&BX.type.isArray(t.OFFERS)){if(!!t.PRODUCT&&"object"===typeof t.PRODUCT){this.product.name=t.PRODUCT.NAME;this.product.id=t.PRODUCT.ID}this.offers=t.OFFERS;this.offerNum=0;if(!!t.OFFER_SELECTED){this.offerNum=parseInt(t.OFFER_SELECTED,10)}if(isNaN(this.offerNum)){this.offerNum=0}if(!!t.TREE_PROPS){this.treeProps=t.TREE_PROPS}if(!!t.DEFAULT_PICTURE){this.defaultPict.pict=t.DEFAULT_PICTURE.PICTURE;this.defaultPict.secondPict=t.DEFAULT_PICTURE.PICTURE_SECOND}}else{this.errorCode=-1}break;default:this.errorCode=-1}if(!!t.BASKET&&"object"===typeof t.BASKET){if(!!t.BASKET.QUANTITY){this.basketData.quantity=t.BASKET.QUANTITY}if(!!t.BASKET.PROPS){this.basketData.props=t.BASKET.PROPS}if(!!t.BASKET.BASKET_URL){this.basketData.basketUrl=t.BASKET.BASKET_URL}}this.lastElement=!!t.LAST_ELEMENT&&"Y"===t.LAST_ELEMENT}if(0===this.errorCode){BX.ready(BX.delegate(this.Init,this))}};t.JCCatalogSectionViewed.prototype.Init=function(){var e=0,i="",s=null;this.obProduct=BX(this.visual.ID);if(!this.obProduct){this.errorCode=-1}this.obPict=BX(this.visual.PICT_ID);if(!this.obPict){this.errorCode=-2}if(this.secondPict&&!!this.visual.SECOND_PICT_ID){this.obSecondPict=BX(this.visual.SECOND_PICT_ID)}this.obPrice=BX(this.visual.PRICE_ID);if(!this.obPrice){this.errorCode=-16}if(this.showQuantity&&!!this.visual.QUANTITY_ID){this.obQuantity=BX(this.visual.QUANTITY_ID);if(!!this.visual.QUANTITY_UP_ID){this.obQuantityUp=BX(this.visual.QUANTITY_UP_ID)}if(!!this.visual.QUANTITY_DOWN_ID){this.obQuantityDown=BX(this.visual.QUANTITY_DOWN_ID)}}if(3===this.productType){if(!!this.visual.TREE_ID){this.obTree=BX(this.visual.TREE_ID);if(!this.obTree){this.errorCode=-256}i=this.visual.TREE_ITEM_ID;for(e=0;e<this.treeProps.length;e++){this.obTreeRows[e]={LEFT:BX(i+this.treeProps[e].ID+"_left"),RIGHT:BX(i+this.treeProps[e].ID+"_right"),LIST:BX(i+this.treeProps[e].ID+"_list"),CONT:BX(i+this.treeProps[e].ID+"_cont")};if(!this.obTreeRows[e].LEFT||!this.obTreeRows[e].RIGHT||!this.obTreeRows[e].LIST||!this.obTreeRows[e].CONT){this.errorCode=-512;break}}}if(!!this.visual.QUANTITY_MEASURE){this.obMeasure=BX(this.visual.QUANTITY_MEASURE)}}if(!!this.visual.BUY_ID){this.obBuyBtn=BX(this.visual.BUY_ID)}if(this.showPercent){if(!!this.visual.DSC_PERC){this.obDscPerc=BX(this.visual.DSC_PERC)}if(this.secondPict&&!!this.visual.SECOND_DSC_PERC){this.obSecondDscPerc=BX(this.visual.SECOND_DSC_PERC)}}if(this.showSkuProps){if(!!this.visual.DISPLAY_PROP_DIV){this.obSkuProps=BX(this.visual.DISPLAY_PROP_DIV)}}if(0===this.errorCode){if(this.showQuantity){if(!!this.obQuantityUp){BX.bind(this.obQuantityUp,"click",BX.delegate(this.QuantityUp,this))}if(!!this.obQuantityDown){BX.bind(this.obQuantityDown,"click",BX.delegate(this.QuantityDown,this))}if(!!this.obQuantity){BX.bind(this.obQuantity,"change",BX.delegate(this.QuantityChange,this))}}switch(this.productType){case 1:break;case 3:s=BX.findChildren(this.obTree,{tagName:"li"},true);if(!!s&&0<s.length){for(e=0;e<s.length;e++){BX.bind(s[e],"click",BX.delegate(this.SelectOfferProp,this))}}for(e=0;e<this.obTreeRows.length;e++){BX.bind(this.obTreeRows[e].LEFT,"click",BX.delegate(this.RowLeft,this));BX.bind(this.obTreeRows[e].RIGHT,"click",BX.delegate(this.RowRight,this))}this.SetCurrent();break}if(!!this.obBuyBtn){BX.bind(this.obBuyBtn,"click",BX.delegate(this.Basket,this))}if(this.lastElement){this.containerHeight=parseInt(this.obProduct.parentNode.offsetHeight,10);if(isNaN(this.containerHeight)){this.containerHeight=0}this.setHeight();BX.bind(t,"resize",BX.delegate(this.checkHeight,this));BX.bind(this.obProduct.parentNode,"mouseover",BX.delegate(this.setHeight,this));BX.bind(this.obProduct.parentNode,"mouseout",BX.delegate(this.clearHeight,this))}}};t.JCCatalogSectionViewed.prototype.checkHeight=function(){this.containerHeight=parseInt(this.obProduct.parentNode.offsetHeight,10);if(isNaN(this.containerHeight)){this.containerHeight=0}};t.JCCatalogSectionViewed.prototype.setHeight=function(){if(0<this.containerHeight){BX.adjust(this.obProduct.parentNode,{style:{height:this.containerHeight+"px"}})}};t.JCCatalogSectionViewed.prototype.clearHeight=function(){BX.adjust(this.obProduct.parentNode,{style:{height:"auto"}})};t.JCCatalogSectionViewed.prototype.QuantityUp=function(){var t=0,e=true;if(0===this.errorCode&&this.showQuantity&&this.canBuy){t=this.isDblQuantity?parseFloat(this.obQuantity.value):parseInt(this.obQuantity.value,10);if(!isNaN(t)){t+=this.stepQuantity;if(this.checkQuantity){if(t>this.maxQuantity){e=false}}if(e){if(this.isDblQuantity){t=Math.round(t*this.precisionFactor)/this.precisionFactor}this.obQuantity.value=t}}}};t.JCCatalogSectionViewed.prototype.QuantityDown=function(){var t=0,e=true;if(0===this.errorCode&&this.showQuantity&&this.canBuy){t=this.isDblQuantity?parseFloat(this.obQuantity.value):parseInt(this.obQuantity.value,10);if(!isNaN(t)){t-=this.stepQuantity;if(t<this.stepQuantity){e=false}if(e){if(this.isDblQuantity){t=Math.round(t*this.precisionFactor)/this.precisionFactor}this.obQuantity.value=t}}}};t.JCCatalogSectionViewed.prototype.QuantityChange=function(){var t=0,e=true;if(0===this.errorCode&&this.showQuantity){if(this.canBuy){t=this.isDblQuantity?parseFloat(this.obQuantity.value):parseInt(this.obQuantity.value,10);if(!isNaN(t)){if(this.checkQuantity){if(t>this.maxQuantity){e=false;t=this.maxQuantity}else if(t<this.stepQuantity){e=false;t=this.stepQuantity}}if(!e){this.obQuantity.value=t}}else{this.obQuantity.value=this.stepQuantity}}else{this.obQuantity.value=this.stepQuantity}}};t.JCCatalogSectionViewed.prototype.QuantitySet=function(t){if(0===this.errorCode){this.canBuy=this.offers[t].CAN_BUY;if(this.canBuy){BX.addClass(this.obBuyBtn,"bx_bt_button");BX.removeClass(this.obBuyBtn,"bx_bt_button_type_2");this.obBuyBtn.innerHTML=BX.message("CVP_MESS_BTN_BUY")}else{BX.addClass(this.obBuyBtn,"bx_bt_button_type_2");BX.removeClass(this.obBuyBtn,"bx_bt_button");this.obBuyBtn.innerHTML=BX.message("CVP_MESS_NOT_AVAILABLE")}if(this.showQuantity){this.isDblQuantity=this.offers[t].QUANTITY_FLOAT;this.checkQuantity=this.offers[t].CHECK_QUANTITY;if(this.isDblQuantity){this.maxQuantity=parseFloat(this.offers[t].MAX_QUANTITY);this.stepQuantity=Math.round(parseFloat(this.offers[t].STEP_QUANTITY)*this.precisionFactor)/this.precisionFactor}else{this.maxQuantity=parseInt(this.offers[t].MAX_QUANTITY,10);this.stepQuantity=parseInt(this.offers[t].STEP_QUANTITY,10)}this.obQuantity.value=this.stepQuantity;this.obQuantity.disabled=!this.canBuy;if(!!this.obMeasure){if(!!this.offers[t].MEASURE){BX.adjust(this.obMeasure,{html:this.offers[t].MEASURE})}else{BX.adjust(this.obMeasure,{html:""})}}}}};t.JCCatalogSectionViewed.prototype.SelectOfferProp=function(){var t=0,e="",i="",s=[],o=null,a=BX.proxy_context;if(!!a&&a.hasAttribute("data-treevalue")){i=a.getAttribute("data-treevalue");s=i.split("_");if(this.SearchOfferPropIndex(s[0],s[1])){o=BX.findChildren(a.parentNode,{tagName:"li"},false);if(!!o&&0<o.length){for(t=0;t<o.length;t++){e=o[t].getAttribute("data-onevalue");if(e===s[1]){BX.addClass(o[t],"bx_active")}else{BX.removeClass(o[t],"bx_active")}}}}}};t.JCCatalogSectionViewed.prototype.SearchOfferPropIndex=function(t,e){var i="",s=false,o,a,h=[],r=-1,n={},u=[];for(o=0;o<this.treeProps.length;o++){if(this.treeProps[o].ID===t){r=o;break}}if(-1<r){for(o=0;o<r;o++){i="PROP_"+this.treeProps[o].ID;n[i]=this.selectedValues[i]}i="PROP_"+this.treeProps[r].ID;s=this.GetRowValues(n,i);if(!s){return false}if(!BX.util.in_array(e,s)){return false}n[i]=e;for(o=r+1;o<this.treeProps.length;o++){i="PROP_"+this.treeProps[o].ID;s=this.GetRowValues(n,i);if(!s){return false}if(this.showAbsent){h=[];u=[];u=BX.clone(n,true);for(a=0;a<s.length;a++){u[i]=s[a];if(this.GetCanBuy(u)){h[h.length]=s[a]}}}else{h=s}if(!!this.selectedValues[i]&&BX.util.in_array(this.selectedValues[i],h)){n[i]=this.selectedValues[i]}else{n[i]=h[0]}this.UpdateRow(o,n[i],s,h)}this.selectedValues=n;this.ChangeInfo()}return true};t.JCCatalogSectionViewed.prototype.RowLeft=function(){var t=0,e="",i=-1,s=BX.proxy_context;if(!!s&&s.hasAttribute("data-treevalue")){e=s.getAttribute("data-treevalue");for(t=0;t<this.treeProps.length;t++){if(this.treeProps[t].ID===e){i=t;break}}if(-1<i&&this.treeRowShowSize<this.showCount[i]){if(0>this.showStart[i]){this.showStart[i]++;BX.adjust(this.obTreeRows[i].LIST,{style:{marginLeft:this.showStart[i]*20+"%"}});BX.adjust(this.obTreeRows[i].RIGHT,{style:this.treeEnableArrow})}if(0<=this.showStart[i]){BX.adjust(this.obTreeRows[i].LEFT,{style:this.treeDisableArrow})}}}};t.JCCatalogSectionViewed.prototype.RowRight=function(){var t=0,e="",i=-1,s=BX.proxy_context;if(!!s&&s.hasAttribute("data-treevalue")){e=s.getAttribute("data-treevalue");for(t=0;t<this.treeProps.length;t++){if(this.treeProps[t].ID===e){i=t;break}}if(-1<i&&this.treeRowShowSize<this.showCount[i]){if(this.treeRowShowSize-this.showStart[i]<this.showCount[i]){this.showStart[i]--;BX.adjust(this.obTreeRows[i].LIST,{style:{marginLeft:this.showStart[i]*20+"%"}});BX.adjust(this.obTreeRows[i].LEFT,{style:this.treeEnableArrow})}if(this.treeRowShowSize-this.showStart[i]>=this.showCount[i]){BX.adjust(this.obTreeRows[i].RIGHT,{style:this.treeDisableArrow})}}}};t.JCCatalogSectionViewed.prototype.UpdateRow=function(t,e,i,s){var o=0,a=0,h="",r=0,n="",u={},l=false,f=false,c=false,p=0,d=this.treeEnableArrow,b=this.treeEnableArrow,P=0,T=null;if(-1<t&&t<this.obTreeRows.length){T=BX.findChildren(this.obTreeRows[t].LIST,{tagName:"li"},false);if(!!T&&0<T.length){l="PICT"===this.treeProps[t].SHOW_MODE;r=i.length;f=this.treeRowShowSize<r;n=f?100/r+"%":"20%";u={props:{className:""},style:{width:n}};if(l){u.style.paddingTop=n}for(o=0;o<T.length;o++){h=T[o].getAttribute("data-onevalue");c=h===e;if(BX.util.in_array(h,s)){u.props.className=c?"bx_active":""}else{u.props.className=c?"bx_active bx_missing":"bx_missing"}u.style.display="none";if(BX.util.in_array(h,i)){u.style.display="";if(c){p=a}a++}BX.adjust(T[o],u)}u={style:{width:(f?20*r:100)+"%",marginLeft:"0%"}};if(l){BX.adjust(this.obTreeRows[t].CONT,{props:{className:f?"bx_item_detail_scu full":"bx_item_detail_scu"}})}else{BX.adjust(this.obTreeRows[t].CONT,{props:{className:f?"bx_item_detail_size full":"bx_item_detail_size"}})}if(f){if(p+1===r){b=this.treeDisableArrow}if(this.treeRowShowSize<=p){P=this.treeRowShowSize-p-1;u.style.marginLeft=P*20+"%"}if(0===P){d=this.treeDisableArrow}BX.adjust(this.obTreeRows[t].LEFT,{style:d});BX.adjust(this.obTreeRows[t].RIGHT,{style:b})}else{BX.adjust(this.obTreeRows[t].LEFT,{style:{display:"none"}});BX.adjust(this.obTreeRows[t].RIGHT,{style:{display:"none"}})}BX.adjust(this.obTreeRows[t].LIST,u);this.showCount[t]=r;this.showStart[t]=P}}};t.JCCatalogSectionViewed.prototype.GetRowValues=function(t,e){var i=0,s,o=[],a=false,h=true;if(0===t.length){for(i=0;i<this.offers.length;i++){if(!BX.util.in_array(this.offers[i].TREE[e],o)){o[o.length]=this.offers[i].TREE[e]}}a=true}else{for(i=0;i<this.offers.length;i++){h=true;for(s in t){if(t[s]!==this.offers[i].TREE[s]){h=false;break}}if(h){if(!BX.util.in_array(this.offers[i].TREE[e],o)){o[o.length]=this.offers[i].TREE[e]}a=true}}}return a?o:false};t.JCCatalogSectionViewed.prototype.GetCanBuy=function(t){var e=0,i,s=false,o=true;for(e=0;e<this.offers.length;e++){o=true;for(i in t){if(t[i]!==this.offers[e].TREE[i]){o=false;break}}if(o){if(this.offers[e].CAN_BUY){s=true;break}}}return s};t.JCCatalogSectionViewed.prototype.SetCurrent=function(){var t=0,e=0,i=[],s="",o=false,a={},h=[],r=this.offers[this.offerNum].TREE;for(t=0;t<this.treeProps.length;t++){s="PROP_"+this.treeProps[t].ID;o=this.GetRowValues(a,s);if(!o){break}if(BX.util.in_array(r[s],o)){a[s]=r[s]}else{a[s]=o[0];this.offerNum=0}if(this.showAbsent){i=[];h=[];h=BX.clone(a,true);for(e=0;e<o.length;e++){h[s]=o[e];if(this.GetCanBuy(h)){i[i.length]=o[e]}}}else{i=o}this.UpdateRow(t,a[s],o,i)}this.selectedValues=a;this.ChangeInfo()};t.JCCatalogSectionViewed.prototype.ChangeInfo=function(){var t=0,e,i=-1,s={},o=true,a="";for(t=0;t<this.offers.length;t++){o=true;for(e in this.selectedValues){if(this.selectedValues[e]!==this.offers[t].TREE[e]){o=false;break}}if(o){i=t;break}}if(-1<i){if(!!this.obPict){if(!!this.offers[i].PREVIEW_PICTURE){BX.adjust(this.obPict,{style:{backgroundImage:"url("+this.offers[i].PREVIEW_PICTURE.SRC+")"}})}else{BX.adjust(this.obPict,{style:{backgroundImage:"url("+this.defaultPict.pict.SRC+")"}})}}if(this.secondPict&&!!this.obSecondPict){if(!!this.offers[i].PREVIEW_PICTURE_SECOND){BX.adjust(this.obSecondPict,{style:{backgroundImage:"url("+this.offers[i].PREVIEW_PICTURE_SECOND.SRC+")"}})}else if(!!this.offers[i].PREVIEW_PICTURE.SRC){BX.adjust(this.obSecondPict,{style:{backgroundImage:"url("+this.offers[i].PREVIEW_PICTURE.SRC+")"}})}else if(!!this.defaultPict.secondPict){BX.adjust(this.obSecondPict,{style:{backgroundImage:"url("+this.defaultPict.secondPict.SRC+")"}})}else{BX.adjust(this.obSecondPict,{style:{backgroundImage:"url("+this.defaultPict.pict.SRC+")"}})}}if(this.showSkuProps&&!!this.obSkuProps){if(0===this.offers[i].DISPLAY_PROPERTIES.length){BX.adjust(this.obSkuProps,{style:{display:"none"},html:""})}else{BX.adjust(this.obSkuProps,{style:{display:""},html:this.offers[i].DISPLAY_PROPERTIES})}}if(!!this.obPrice){a=this.offers[i].PRICE.PRINT_DISCOUNT_VALUE;if(this.showOldPrice&&this.offers[i].PRICE.DISCOUNT_VALUE!==this.offers[i].PRICE.VALUE){a+=" <span>"+this.offers[i].PRICE.PRINT_VALUE+"</span>"}BX.adjust(this.obPrice,{html:a});if(this.showPercent){if(this.offers[i].PRICE.DISCOUNT_VALUE!==this.offers[i].PRICE.VALUE){s={style:{display:""},html:this.offers[i].PRICE.DISCOUNT_DIFF_PERCENT}}else{s={style:{display:"none"},html:""}}if(!!this.obDscPerc){BX.adjust(this.obDscPerc,s)}if(!!this.obSecondDscPerc){BX.adjust(this.obSecondDscPerc,s)}}}this.offerNum=i;this.QuantitySet(this.offerNum)}};t.JCCatalogSectionViewed.prototype.InitBasketUrl=function(){switch(this.productType){case 1:case 2:this.basketUrl=this.product.addUrl;break;case 3:this.basketUrl=this.offers[this.offerNum].ADD_URL;break}this.basketParams={ajax_basket:"Y"};if(this.showQuantity){this.basketParams[this.basketData.quantity]=this.obQuantity.value}};t.JCCatalogSectionViewed.prototype.FillBasketProps=function(){if(!this.visual.BASKET_PROP_DIV){return}var t=0,e=null,i=false,s=null;if(this.basketData.useProps&&!this.basketData.emptyProps){if(!!this.obPopupWin&&!!this.obPopupWin.contentContainer){s=this.obPopupWin.contentContainer}}else{s=BX(this.visual.BASKET_PROP_DIV)}if(!s){return}e=s.getElementsByTagName("select");if(!!e&&!!e.length){for(t=0;t<e.length;t++){if(!e[t].disabled){switch(e[t].type.toLowerCase()){case"select-one":this.basketParams[e[t].name]=e[t].value;i=true;break;default:break}}}}e=s.getElementsByTagName("input");if(!!e&&!!e.length){for(t=0;t<e.length;t++){if(!e[t].disabled){switch(e[t].type.toLowerCase()){case"hidden":this.basketParams[e[t].name]=e[t].value;i=true;break;case"radio":if(e[t].checked){this.basketParams[e[t].name]=e[t].value;i=true}break;default:break}}}}if(!i){this.basketParams[this.basketData.props]=[];this.basketParams[this.basketData.props][0]=0}};t.JCCatalogSectionViewed.prototype.SendToBasket=function(){if(!this.canBuy){return}this.InitBasketUrl();this.FillBasketProps();BX.ajax.loadJSON(this.basketUrl,this.basketParams,BX.delegate(this.BasketResult,this))};t.JCCatalogSectionViewed.prototype.Basket=function(){var t="";if(!this.canBuy){return}switch(this.productType){case 1:case 2:if(this.basketData.useProps&&!this.basketData.emptyProps){this.InitPopupWindow();this.obPopupWin.setTitleBar({content:BX.create("div",{style:{marginRight:"30px",whiteSpace:"nowrap"},text:BX.message("CVP_TITLE_BASKET_PROPS")})});if(BX(this.visual.BASKET_PROP_DIV)){t=BX(this.visual.BASKET_PROP_DIV).innerHTML}this.obPopupWin.setContent(t);this.obPopupWin.setButtons([new e({ownerClass:this.obProduct.parentNode.parentNode.parentNode.className,text:BX.message("CVP_BTN_MESSAGE_SEND_PROPS"),events:{click:BX.delegate(this.SendToBasket,this)}})]);this.obPopupWin.show()}else{this.SendToBasket()}break;case 3:this.SendToBasket();break}};t.JCCatalogSectionViewed.prototype.BasketResult=function(t){var i="",s="",o="",a=true,h=[];if(!!this.obPopupWin){this.obPopupWin.close()}if("object"!==typeof t){return false}a="OK"===t.STATUS;if(a){BX.onCustomEvent("OnBasketChange");s=this.product.name;switch(this.productType){case 1:case 2:o=this.product.pict.SRC;break;case 3:o=!!this.offers[this.offerNum].PREVIEW_PICTURE?this.offers[this.offerNum].PREVIEW_PICTURE.SRC:this.defaultPict.pict.SRC;break}i='<div style="width: 96%; margin: 10px 2%; text-align: center;"><img src="'+o+'" height="130"><p>'+s+"</p></div>";h=[new e({ownerClass:this.obProduct.parentNode.parentNode.parentNode.className,text:BX.message("CVP_BTN_MESSAGE_BASKET_REDIRECT"),events:{click:BX.delegate(function(){location.href=!!this.basketData.basketUrl?this.basketData.basketUrl:BX.message("CVP_BASKET_URL")},this)}})]}else{i=!!t.MESSAGE?t.MESSAGE:BX.message("CVP_BASKET_UNKNOWN_ERROR");h=[new e({ownerClass:this.obProduct.parentNode.parentNode.parentNode.className,text:BX.message("CVP_BTN_MESSAGE_CLOSE"),events:{click:BX.delegate(this.obPopupWin.close,this.obPopupWin)}})]}this.InitPopupWindow();this.obPopupWin.setTitleBar({content:BX.create("div",{style:{marginRight:"30px",whiteSpace:"nowrap"},text:a?BX.message("CVP_TITLE_SUCCESSFUL"):BX.message("CVP_TITLE_ERROR")})});this.obPopupWin.setContent(i);this.obPopupWin.setButtons(h);this.obPopupWin.show()};t.JCCatalogSectionViewed.prototype.InitPopupWindow=function(){if(!!this.obPopupWin){return}this.obPopupWin=BX.PopupWindowManager.create("CatalogSectionBasket_"+this.visual.ID,null,{autoHide:false,offsetLeft:0,offsetTop:0,overlay:true,closeByEsc:true,titleBar:true,closeIcon:{top:"10px",right:"10px"}})}})(window);
/* End */
;; /* /bitrix/templates/eshop_adapt_blue/script.js?14338396071122*/
; /* /bitrix/components/bitrix/sale.basket.basket.line/templates/.default/script.js?14338395724656*/
; /* /bitrix/components/bitrix/search.title/script.min.js?14339373296196*/
; /* /bitrix/templates/eshop_adapt_blue/components/bitrix/menu/catalog_horizontal2/script.js?14338396075394*/
; /* /bitrix/components/bitrix/catalog.top/templates/.default/banner/script.js?143383957337567*/
; /* /bitrix/components/bitrix/catalog.top/templates/.default/slider/script.js?143393746546429*/
; /* /bitrix/components/bitrix/catalog.top/templates/.default/section/script.js?143393746539599*/
; /* /bitrix/components/bitrix/sale.bestsellers/templates/.default/script.js?143383955631755*/
; /* /bitrix/components/bitrix/catalog.viewed.products/templates/.default/script.min.js?143393747321421*/

//# sourceMappingURL=template_303dffb8279775eb20d4da2fbfd24812.map.js