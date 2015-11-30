
; /* Start:"a:4:{s:4:"full";s:125:"/bitrix/templates/.default/components/bitrix/catalog/template1_copy/bitrix/catalog.element/.default/script.js?143383960471670";s:6:"source";s:109:"/bitrix/templates/.default/components/bitrix/catalog/template1_copy/bitrix/catalog.element/.default/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function (window) {

if (!!window.JCCatalogElement)
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
		children: [this.nameNode],
		events : this.contextEvents
	});
	if (BX.browser.IsIE())
	{
		this.buttonNode.setAttribute("hideFocus", "hidefocus");
	}
};
BX.extend(BasketButton, BX.PopupWindowButton);

window.JCCatalogElement = function (arParams)
{
	this.productType = 0;

	this.config = {
		useCatalog: true,
		showQuantity: true,
		showPrice: true,
		showAbsent: true,
		showOldPrice: false,
		showPercent: false,
		showSkuProps: false,
		showOfferGroup: false,
		useCompare: false,
		mainPictureMode: 'IMG',
		showBasisPrice: false,
		basketAction: ['BUY'],
		showClosePopup: false
	};

	this.checkQuantity = false;
	this.maxQuantity = 0;
	this.stepQuantity = 1;
	this.isDblQuantity = false;
	this.canBuy = true;
	this.currentBasisPrice = {};
	this.canSubscription = true;
	this.currentIsSet = false;
	this.updateViewedCount = false;

	this.precision = 6;
	this.precisionFactor = Math.pow(10,this.precision);

	this.listID = {
		main: ['PICT_ID', 'BIG_SLIDER_ID', 'BIG_IMG_CONT_ID'],
		stickers: ['STICKER_ID'],
		productSlider: ['SLIDER_CONT', 'SLIDER_LIST', 'SLIDER_LEFT', 'SLIDER_RIGHT'],
		offerSlider: ['SLIDER_CONT_OF_ID', 'SLIDER_LIST_OF_ID', 'SLIDER_LEFT_OF_ID', 'SLIDER_RIGHT_OF_ID'],
		offers: ['TREE_ID', 'TREE_ITEM_ID', 'DISPLAY_PROP_DIV', 'OFFER_GROUP'],
		quantity: ['QUANTITY_ID', 'QUANTITY_UP_ID', 'QUANTITY_DOWN_ID', 'QUANTITY_MEASURE', 'QUANTITY_LIMIT', 'BASIS_PRICE'],
		price: ['PRICE_ID'],
		oldPrice: ['OLD_PRICE_ID', 'DISCOUNT_VALUE_ID'],
		discountPerc: ['DISCOUNT_PERC_ID'],
		basket: ['BASKET_PROP_DIV', 'BUY_ID', 'ADD_BASKET_ID', 'BASKET_ACTIONS_ID', 'NOT_AVAILABLE_MESS'],
		magnifier: ['MAGNIFIER_ID', 'MAGNIFIER_AREA_ID'],
		compare: ['COMPARE_LINK_ID']
	};

	this.visualPostfix = {
		// main pict
		PICT_ID: '_pict',
		BIG_SLIDER_ID: '_big_slider',
		BIG_IMG_CONT_ID: '_bigimg_cont',
		// stickers
		STICKER_ID: '_sticker',
		// product pict slider
		SLIDER_CONT: '_slider_cont',
		SLIDER_LIST: '_slider_list',
		SLIDER_LEFT: '_slider_left',
		SLIDER_RIGHT: '_slider_right',
		// offers sliders
		SLIDER_CONT_OF_ID: '_slider_cont_',
		SLIDER_LIST_OF_ID: '_slider_list_',
		SLIDER_LEFT_OF_ID: '_slider_left_',
		SLIDER_RIGHT_OF_ID: '_slider_right_',
		// offers
		TREE_ID: '_skudiv',
		TREE_ITEM_ID: '_prop_',
		DISPLAY_PROP_DIV: '_sku_prop',
		// quantity
		QUANTITY_ID: '_quantity',
		QUANTITY_UP_ID: '_quant_up',
		QUANTITY_DOWN_ID: '_quant_down',
		QUANTITY_MEASURE: '_quant_measure',
		QUANTITY_LIMIT: '_quant_limit',
		BASIS_PRICE: '_basis_price',
		// price and discount
		PRICE_ID: '_price',
		OLD_PRICE_ID: '_old_price',
		DISCOUNT_VALUE_ID: '_price_discount',
		DISCOUNT_PERC_ID: '_dsc_pict',
		// basket
		BASKET_PROP_DIV: '_basket_prop',
		BUY_ID: '_buy_link',
		ADD_BASKET_ID: '_add_basket_link',
		BASKET_ACTIONS_ID: '_basket_actions',
		NOT_AVAILABLE_MESS: '_not_avail',
		// magnifier
		MAGNIFIER_ID: '_magnifier',
		MAGNIFIER_AREA_ID: '_magnifier_area',
		// offer groups
		OFFER_GROUP: '_set_group_',
		// compare
		COMPARE_LINK_ID: '_compare_link'
	};

	this.visual = {};

	this.basketMode = '';
	this.product = {
		checkQuantity: false,
		maxQuantity: 0,
		stepQuantity: 1,
		startQuantity: 1,
		isDblQuantity: false,
		canBuy: true,
		canSubscription: true,
		name: '',
		pict: {},
		id: 0,
		addUrl: '',
		buyUrl: '',
		slider: {},
		sliderCount: 0,
		useSlider: false,
		sliderPict: []
	};
	this.mess = {};

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
		preview: null,
		detail: null
	};

	this.offers = [];
	this.offerNum = 0;
	this.treeProps = [];
	this.obTreeRows = [];
	this.showCount = [];
	this.showStart = [];
	this.selectedValues = {};
	this.sliders = [];

	this.obProduct = null;
	this.obQuantity = null;
	this.obQuantityUp = null;
	this.obQuantityDown = null;
	this.obBasisPrice = null;
	this.obPict = null;
	this.obPictAligner = null;
	this.obPrice = {
		price: null,
		full: null,
		discount: null,
		percent: null
	};
	this.obTree = null;
	this.obBuyBtn = null;
	this.obAddToBasketBtn = null;
	this.obBasketActions = null;
	this.obNotAvail = null;
	this.obSkuProps = null;
	this.obSlider = null;
	this.obMeasure = null;
	this.obQuantityLimit = {
		all: null,
		value: null
	};
	this.obCompare = null;

	this.viewedCounter = {
		path: '/bitrix/components/bitrix/catalog.element/ajax.php',
		params: {
			AJAX: 'Y',
			SITE_ID: '',
			PRODUCT_ID: 0,
			PARENT_ID: 0
		}
	};

	this.currentImg = {
		src: '',
		width: 0,
		height: 0,
		screenWidth: 0,
		screenHeight: 0,
		screenOffsetX: 0,
		screenOffsetY: 0,
		scale: 1
	};

	this.obPopupWin = null;
	this.basketUrl = '';
	this.basketParams = {};

	this.obPopupPict = null;
	this.magnify = {
		obMagnifier: null,
		obMagnifyPict: null,
		obMagnifyArea: null,
		obBigImg: null,
		obBigSlider: null,
		magnifyShow: false,
		areaParams : {
			width: 100,
			height: 130,
			left: 0,
			top: 0,
			scaleFactor: 1,
			globalLeft: 0,
			globalTop: 0,
			globalRight: 0,
			globalBottom: 0
		},
		magnifierParams: {
			top: 0,
			left: 0,
			width: 0,
			height: 0,
			ratioX: 10,
			ratioY: 13,
			defaultScale: 1
		},
		magnifyPictParams: {
			marginTop: 0,
			marginLeft: 0,
			width: 0,
			height: 0
		}
	};

	this.treeRowShowSize = 5;
	this.treeEnableArrow = { display: '', cursor: 'pointer', opacity: 1 };
	this.treeDisableArrow = { display: '', cursor: 'default', opacity: 0.2 };
	this.sliderRowShowSize = 5;
	this.sliderEnableArrow = { display: '', cursor: 'pointer', opacity: 1 };
	this.sliderDisableArrow = { display: '', cursor: 'default', opacity: 0.2 };

	this.errorCode = 0;

	if (typeof arParams === 'object')
	{
		this.params = arParams;
		this.initConfig();

		if (!!this.params.MESS)
		{
			this.mess = this.params.MESS;
		}
		switch (this.productType)
		{
			case 0:// no catalog
			case 1://product
			case 2://set
				this.initProductData();
				break;
			case 3://sku
				this.initOffersData();
				break;
			default:
				this.errorCode = -1;
		}
		this.initBasketData();
		this.initCompareData();
	}
	if (0 === this.errorCode)
	{
		BX.ready(BX.delegate(this.Init,this));
	}
	this.params = {};
};

window.JCCatalogElement.prototype.Init = function()
{
	var i = 0,
		j = 0,
		strPrefix = '',
		SliderImgs = null,
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
	else
	{
		this.obPictAligner = this.obPict.parentNode;
	}

	if (this.config.showPrice)
	{
		this.obPrice.price = BX(this.visual.PRICE_ID);
		if (!this.obPrice.price && this.config.useCatalog)
		{
			this.errorCode = -16;
		}
		else
		{
			if (this.config.showOldPrice)
			{
				this.obPrice.full = BX(this.visual.OLD_PRICE_ID);
				this.obPrice.discount = BX(this.visual.DISCOUNT_VALUE_ID);
				if (!this.obPrice.full || !this.obPrice.discount)
				{
					this.config.showOldPrice = false;
				}
			}
			if (this.config.showPercent)
			{
				this.obPrice.percent = BX(this.visual.DISCOUNT_PERC_ID);
				if (!this.obPrice.percent)
				{
					this.config.showPercent = false;
				}
			}
		}
		this.obBasketActions = BX(this.visual.BASKET_ACTIONS_ID);
		if (!!this.obBasketActions)
		{
			if (BX.util.in_array('BUY', this.config.basketAction))
			{
				this.obBuyBtn = BX(this.visual.BUY_ID);
			}
			if (BX.util.in_array('ADD', this.config.basketAction))
			{
				this.obAddToBasketBtn = BX(this.visual.ADD_BASKET_ID);
			}
		}
		this.obNotAvail = BX(this.visual.NOT_AVAILABLE_MESS);
	}

	if (this.config.showQuantity)
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
		if (this.config.showBasisPrice)
		{
			this.obBasisPrice = BX(this.visual.BASIS_PRICE);
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
		if (!!this.visual.QUANTITY_LIMIT)
		{
			this.obQuantityLimit.all = BX(this.visual.QUANTITY_LIMIT);
			if (!!this.obQuantityLimit.all)
			{
				this.obQuantityLimit.value = BX.findChild(this.obQuantityLimit.all, {tagName: 'span'}, false, false);
				if (!this.obQuantityLimit.value)
				{
					this.obQuantityLimit.all = null;
				}
			}
		}
	}

	if (this.config.showSkuProps)
	{
		if (!!this.visual.DISPLAY_PROP_DIV)
		{
			this.obSkuProps = BX(this.visual.DISPLAY_PROP_DIV);
		}
	}

	if (this.config.useCompare)
	{
		this.obCompare = BX(this.visual.COMPARE_LINK_ID);
	}

	if (0 === this.errorCode)
	{
		if (this.config.showQuantity)
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
			case 0://no catalog
			case 1://product
			case 2://set
				if (this.product.useSlider)
				{
					this.product.slider = {
						COUNT: this.product.sliderCount,
						ID: this.visual.SLIDER_CONT,
						CONT: BX(this.visual.SLIDER_CONT),
						LIST: BX(this.visual.SLIDER_LIST),
						LEFT: BX(this.visual.SLIDER_LEFT),
						RIGHT: BX(this.visual.SLIDER_RIGHT),
						START: 0
					};
					SliderImgs = BX.findChildren(this.product.slider.LIST, {tagName: 'li'}, true);
					if (!!SliderImgs && 0 < SliderImgs.length)
					{
						for (j = 0; j < SliderImgs.length; j++)
						{
							BX.bind(SliderImgs[j], 'click', BX.delegate(this.ProductSelectSliderImg, this));
						}
					}
					if (!!this.product.slider.LEFT)
					{
						BX.bind(this.product.slider.LEFT, 'click', BX.delegate(this.ProductSliderRowLeft, this));
						BX.adjust(this.product.slider.LEFT, { style: this.sliderDisableArrow } );

					}
					if (!!this.product.slider.RIGHT)
					{
						BX.bind(this.product.slider.RIGHT, 'click', BX.delegate(this.ProductSliderRowRight, this));
						BX.adjust(this.product.slider.RIGHT, { style: this.sliderEnableArrow } );
					}
					this.setCurrentImg(this.product.sliderPict[0], true);
				}
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
				for (i = 0; i < this.offers.length; i++)
				{
					this.offers[i].SLIDER_COUNT = parseInt(this.offers[i].SLIDER_COUNT, 10);
					if (isNaN(this.offers[i].SLIDER_COUNT))
					{
						this.offers[i].SLIDER_COUNT = 0;
					}
					if (0 === this.offers[i].SLIDER_COUNT)
					{
						this.sliders[i] = {
							COUNT: this.offers[i].SLIDER_COUNT,
							ID: ''
						};
					}
					else
					{
						for (j = 0; j < this.offers[i].SLIDER.length; j++)
						{
							this.offers[i].SLIDER[j].WIDTH = parseInt(this.offers[i].SLIDER[j].WIDTH, 10);
							this.offers[i].SLIDER[j].HEIGHT = parseInt(this.offers[i].SLIDER[j].HEIGHT, 10);
						}
						this.sliders[i] = {
							COUNT: this.offers[i].SLIDER_COUNT,
							OFFER_ID: this.offers[i].ID,
							ID: this.visual.SLIDER_CONT_OF_ID+this.offers[i].ID,
							CONT: BX(this.visual.SLIDER_CONT_OF_ID+this.offers[i].ID),
							LIST: BX(this.visual.SLIDER_LIST_OF_ID+this.offers[i].ID),
							LEFT: BX(this.visual.SLIDER_LEFT_OF_ID+this.offers[i].ID),
							RIGHT: BX(this.visual.SLIDER_RIGHT_OF_ID+this.offers[i].ID),
							START: 0
						};
						SliderImgs = BX.findChildren(this.sliders[i].LIST, {tagName: 'li'}, true);
						if (!!SliderImgs && 0 < SliderImgs.length)
						{
							for (j = 0; j < SliderImgs.length; j++)
							{
								BX.bind(SliderImgs[j], 'click', BX.delegate(this.SelectSliderImg, this));
							}
						}
						if (!!this.sliders[i].LEFT)
						{
							BX.bind(this.sliders[i].LEFT, 'click', BX.delegate(this.SliderRowLeft, this));
						}
						if (!!this.sliders[i].RIGHT)
						{
							BX.bind(this.sliders[i].RIGHT, 'click', BX.delegate(this.SliderRowRight, this));
						}
					}
				}
				this.SetCurrent();
				break;
		}

		if (!!this.obBuyBtn)
		{
			BX.bind(this.obBuyBtn, 'click', BX.proxy(this.BuyBasket, this));
		}
		if (!!this.obAddToBasketBtn)
		{
			BX.bind(this.obAddToBasketBtn, 'click', BX.proxy(this.Add2Basket, this));
		}
		if (!!this.obCompare)
		{
			BX.bind(this.obCompare, 'click', BX.proxy(this.Compare, this));
		}

		this.setMainPictHandler();
	}
};

window.JCCatalogElement.prototype.initConfig = function()
{
	this.productType = parseInt(this.params.PRODUCT_TYPE, 10);
	if (!!this.params.CONFIG && typeof(this.params.CONFIG) === 'object')
	{
		if (this.params.CONFIG.USE_CATALOG !== 'undefined' && BX.type.isBoolean(this.params.CONFIG.USE_CATALOG))
		{
			this.config.useCatalog = this.params.CONFIG.USE_CATALOG;
		}
		this.config.showQuantity = !!this.params.CONFIG.SHOW_QUANTITY;
		this.config.showPrice = !!this.params.CONFIG.SHOW_PRICE;
		this.config.showPercent = !!this.params.CONFIG.SHOW_DISCOUNT_PERCENT;
		this.config.showOldPrice = !!this.params.CONFIG.SHOW_OLD_PRICE;
		this.config.showSkuProps = !!this.params.CONFIG.SHOW_SKU_PROPS;
		this.config.showOfferGroup = !!this.params.CONFIG.OFFER_GROUP;
		this.config.useCompare = !!this.params.CONFIG.DISPLAY_COMPARE;
		if (!!this.params.CONFIG.MAIN_PICTURE_MODE)
		{
			this.config.mainPictureMode = this.params.CONFIG.MAIN_PICTURE_MODE;
		}
		this.config.showBasisPrice = !!this.params.CONFIG.SHOW_BASIS_PRICE;
		if (!!this.params.CONFIG.ADD_TO_BASKET_ACTION)
		{
			this.config.basketAction = this.params.CONFIG.ADD_TO_BASKET_ACTION;
		}
		this.config.showClosePopup = !!this.params.CONFIG.SHOW_CLOSE_POPUP;
	}
	else
	{
		// old version
		if (this.params.USE_CATALOG !== 'undefined' && BX.type.isBoolean(this.params.USE_CATALOG))
		{
			this.config.useCatalog = this.params.USE_CATALOG;
		}
		this.config.showQuantity = !!this.params.SHOW_QUANTITY;
		this.config.showPrice = !!this.params.SHOW_PRICE;
		this.config.showPercent = !!this.params.SHOW_DISCOUNT_PERCENT;
		this.config.showOldPrice = !!this.params.SHOW_OLD_PRICE;
		this.config.showSkuProps = !!this.params.SHOW_SKU_PROPS;
		this.config.showOfferGroup = !!this.params.OFFER_GROUP;
		this.config.useCompare = !!this.params.DISPLAY_COMPARE;
		if (!!this.params.MAIN_PICTURE_MODE)
		{
			this.config.mainPictureMode = this.params.MAIN_PICTURE_MODE;
		}
		this.config.showBasisPrice = !!this.params.SHOW_BASIS_PRICE;
		if (!!this.params.ADD_TO_BASKET_ACTION)
		{
			this.config.basketAction = this.params.ADD_TO_BASKET_ACTION;
		}
		this.config.showClosePopup = !!this.params.SHOW_CLOSE_POPUP;
	}

	if (!this.params.VISUAL || typeof(this.params.VISUAL) !== 'object' || !this.params.VISUAL.ID)
	{
		this.errorCode = -1;
		return;
	}
	this.visual.ID = this.params.VISUAL.ID;
	this.initVisualParams('main');
	if (this.config.showQuantity)
	{
		this.initVisualParams('quantity');
	}
	if (this.config.showPrice)
	{
		this.initVisualParams('price');
	}
	if (this.config.showOldPrice)
	{
		this.initVisualParams('oldPrice');
	}
	if (this.config.showPercent)
	{
		this.initVisualParams('discountPerc');
	}
	this.initVisualParams('basket');
	if (this.config.mainPictureMode === 'MAGNIFIER')
	{
		this.initVisualParams('magnifier');
	}
	if (this.config.useCompare)
	{
		this.initVisualParams('compare');
	}
};

window.JCCatalogElement.prototype.initVisualParams = function(ID)
{
	var i = 0,
		key = '';

	if (!this.listID[ID])
	{
		this.errorCode = -1;
		return;
	}
	for (i = 0; i < this.listID[ID].length; i++)
	{
		key = this.listID[ID][i];
		this.visual[key] = (!!this.params.VISUAL[key] ? this.params.VISUAL[key] : this.visual.ID+this.visualPostfix[key]);
	}
};

window.JCCatalogElement.prototype.initProductData = function()
{
	var j = 0;
	this.initVisualParams('productSlider');
	if (!!this.params.PRODUCT && 'object' === typeof(this.params.PRODUCT))
	{
		if (this.config.showQuantity)
		{
			this.product.checkQuantity = this.params.PRODUCT.CHECK_QUANTITY;
			this.product.isDblQuantity = this.params.PRODUCT.QUANTITY_FLOAT;
			if (this.product.checkQuantity)
			{
				this.product.maxQuantity = (this.product.isDblQuantity ? parseFloat(this.params.PRODUCT.MAX_QUANTITY) : parseInt(this.params.PRODUCT.MAX_QUANTITY, 10));
			}
			this.product.stepQuantity = (this.product.isDblQuantity ? parseFloat(this.params.PRODUCT.STEP_QUANTITY) : parseInt(this.params.PRODUCT.STEP_QUANTITY, 10));

			this.checkQuantity = this.product.checkQuantity;
			this.isDblQuantity = this.product.isDblQuantity;
			this.maxQuantity = this.product.maxQuantity;
			this.stepQuantity = this.product.stepQuantity;
			if (this.isDblQuantity)
			{
				this.stepQuantity = Math.round(this.stepQuantity*this.precisionFactor)/this.precisionFactor;
			}
		}
		this.product.canBuy = this.params.PRODUCT.CAN_BUY;
		this.product.canSubscription = this.params.PRODUCT.SUBSCRIPTION;
		if (this.config.showPrice)
		{
			this.currentBasisPrice = this.params.PRODUCT.BASIS_PRICE;
		}

		this.canBuy = this.product.canBuy;
		this.canSubscription = this.product.canSubscription;

		this.product.name = this.params.PRODUCT.NAME;
		this.product.pict = this.params.PRODUCT.PICT;
		this.product.id = this.params.PRODUCT.ID;

		if (!!this.params.PRODUCT.ADD_URL)
		{
			this.product.addUrl = this.params.PRODUCT.ADD_URL;
		}
		if (!!this.params.PRODUCT.BUY_URL)
		{
			this.product.buyUrl = this.params.PRODUCT.BUY_URL;
		}

		if (!!this.params.PRODUCT.SLIDER_COUNT)
		{
			this.product.sliderCount = parseInt(this.params.PRODUCT.SLIDER_COUNT, 10);
			if (isNaN(this.product.sliderCount))
			{
				this.product.sliderCount = 0;
			}
			if (0 < this.product.sliderCount && !!this.params.PRODUCT.SLIDER.length && 0 < this.params.PRODUCT.SLIDER.length)
			{
				for (j = 0; j < this.params.PRODUCT.SLIDER.length; j++)
				{
					this.product.useSlider = true;
					this.params.PRODUCT.SLIDER[j].WIDTH = parseInt(this.params.PRODUCT.SLIDER[j].WIDTH, 10);
					this.params.PRODUCT.SLIDER[j].HEIGHT = parseInt(this.params.PRODUCT.SLIDER[j].HEIGHT, 10);
				}
				this.product.sliderPict = this.params.PRODUCT.SLIDER;
				this.setCurrentImg(this.product.sliderPict[0], false);
			}
		}
		this.currentIsSet = true;
	}
	else
	{
		this.errorCode = -1;
	}
};

window.JCCatalogElement.prototype.initOffersData = function()
{
	this.initVisualParams('offerSlider');
	this.initVisualParams('offers');
	if (!!this.params.OFFERS && BX.type.isArray(this.params.OFFERS))
	{
		this.offers = this.params.OFFERS;
		this.offerNum = 0;
		if (!!this.params.OFFER_SELECTED)
		{
			this.offerNum = parseInt(this.params.OFFER_SELECTED, 10);
		}
		if (isNaN(this.offerNum))
		{
			this.offerNum = 0;
		}
		if (!!this.params.TREE_PROPS)
		{
			this.treeProps = this.params.TREE_PROPS;
		}
		if (!!this.params.DEFAULT_PICTURE)
		{
			this.defaultPict.preview = this.params.DEFAULT_PICTURE.PREVIEW_PICTIRE;
			this.defaultPict.detail = this.params.DEFAULT_PICTURE.DETAIL_PICTURE;
		}
		if (!!this.params.PRODUCT && typeof(this.params.PRODUCT) === 'object')
		{
			this.product.id = parseInt(this.params.PRODUCT.ID, 10);
			this.product.name = this.params.PRODUCT.NAME;
		}
	}
	else
	{
		this.errorCode = -1;
	}
};

window.JCCatalogElement.prototype.initBasketData = function()
{
	if (!!this.params.BASKET && 'object' === typeof(this.params.BASKET))
	{
		if (1 === this.productType || 2 === this.productType)
		{
			this.basketData.useProps = !!this.params.BASKET.ADD_PROPS;
			this.basketData.emptyProps = !!this.params.BASKET.EMPTY_PROPS;
		}

		if (!!this.params.BASKET.QUANTITY)
		{
			this.basketData.quantity = this.params.BASKET.QUANTITY;
		}
		if (!!this.params.BASKET.PROPS)
		{
			this.basketData.props = this.params.BASKET.PROPS;
		}
		if (!!this.params.BASKET.BASKET_URL)
		{
			this.basketData.basketUrl = this.params.BASKET.BASKET_URL;
		}
		if (3 === this.productType)
		{
			if (!!this.params.BASKET.SKU_PROPS)
			{
				this.basketData.sku_props = this.params.BASKET.SKU_PROPS;
			}
		}
		if (!!this.params.BASKET.ADD_URL_TEMPLATE)
		{
			this.basketData.add_url = this.params.BASKET.ADD_URL_TEMPLATE;
		}
		if (!!this.params.BASKET.BUY_URL_TEMPLATE)
		{
			this.basketData.buy_url = this.params.BASKET.BUY_URL_TEMPLATE;
		}
		if (this.basketData.add_url === '' && this.basketData.buy_url === '')
		{
			this.errorCode = -1024;
		}
	}
};

window.JCCatalogElement.prototype.initCompareData = function()
{
	if (this.config.useCompare)
	{
		if (!!this.params.COMPARE && typeof(this.params.COMPARE) === 'object')
		{
			if (!!this.params.COMPARE.COMPARE_PATH)
			{
				this.compareData.comparePath = this.params.COMPARE.COMPARE_PATH;
			}
			if (!!this.params.COMPARE.COMPARE_URL_TEMPLATE)
			{
				this.compareData.compareUrl = this.params.COMPARE.COMPARE_URL_TEMPLATE;
			}
			else
			{
				this.config.useCompare = false;
			}
		}
		else
		{
			this.config.useCompare = false;
		}
	}
};

window.JCCatalogElement.prototype.setMainPictHandler = function()
{
	switch (this.config.mainPictureMode)
	{
		case 'GALLERY':
			break;
		case 'MAGNIFIER':
			this.magnify.obBigImg = BX(this.visual.BIG_IMG_CONT_ID);
			this.magnify.obBigSlider = BX(this.visual.BIG_SLIDER_ID);
			if (!!this.magnify.obBigImg && !!this.magnify.obBigSlider)
			{
				this.magnify.obMagnifyArea = this.obPictAligner.appendChild(BX.create(
					'DIV',
					{
						props: {
							id: this.visual.MAGNIFIER_AREA_ID,
							className: 'bx_item_slider_lupe_small'
						},
						style: {
							display: 'none',
							top: 0,
							left: 0,
							width: '100px',
							height: '130px'
						},
						events: {
							mouseover: BX.delegate(this.onMagnifierArea, this),
							mouseout: BX.delegate(function(){this.outMagnifierArea(); this.hideMagnifier(); }, this)
						}
					}
				));

				this.magnify.obMagnifier = this.magnify.obBigSlider.appendChild(BX.create(
					'DIV',
					{
						props: {
							id: this.visual.MAGNIFIER_ID,
							className: 'bx_item_slider_lupe'
						},
						style: {
							display: 'none'
						}
					}
				));
				this.magnify.obMagnifyPict = this.magnify.obMagnifier.appendChild(BX.create(
					'IMG',
					{
						props: {
							src: this.currentImg.src
						}
					}
				));
				BX.bind(this.obPict, 'mouseover', BX.delegate(this.showMagnifier, this));
			}
			break;
		case 'POPUP':
			this.obPopupPict = new BX.PopupWindow('CatalogElementPopup_'+this.visual.ID, null, {
				autoHide: false,
				offsetLeft: 0,
				offsetTop: 0,
				overlay : false,
				closeByEsc: true,
				titleBar: true,
				closeIcon: {top: '10px', right: '10px'}
			});
			BX.bind(this.obPict, 'click', BX.delegate(this.showMainPictPopup, this));
			BX.adjust(this.obPict, { style: { cursor: 'pointer' } });
			BX.addCustomEvent(this.obPopupPict, "onAfterPopupShow", BX.proxy(this.onPopupWindowShow, this));
			BX.addCustomEvent(this.obPopupPict, "onPopupClose", BX.proxy(this.onPopupWindowClose, this));
			break;
		default:
			break;
	}
};

window.JCCatalogElement.prototype.setCurrentImg = function(img, showImage)
{
	showImage = !!showImage;
	this.currentImg.src = img.SRC;
	this.currentImg.width = img.WIDTH;
	this.currentImg.height = img.HEIGHT;
	if (showImage && !!this.obPict)
	{
		if (this.config.mainPictureMode === 'MAGNIFIER')
		{
			this.outMagnifierArea();
			this.hideMagnifier();
		}
		BX.adjust(this.obPict, { props: { src: this.currentImg.src } });
		var dest = {
				width: parseInt(this.obPictAligner.offsetWidth, 10),
				height: parseInt(this.obPictAligner.offsetHeight, 10)
			},
			result,
			newMarginTop;
		result = this.scaleImg(this.currentImg, dest);

		newMarginTop = (result.height < dest.height ? (dest.height - result.height) >>> 1 : 0);
		if (newMarginTop !== this.currentImg.screenOffsetY)
		{
			BX.style(this.obPictAligner, 'marginTop', newMarginTop+'px');
			this.currentImg.screenOffsetY = newMarginTop;
		}
		this.currentImg.screenOffsetX = (result.width < dest.width ? (dest.width - result.width) >>> 1 : 0);
		this.currentImg.screenWidth = result.width;
		this.currentImg.screenHeight = result.height;
	}
};

window.JCCatalogElement.prototype.scaleImg = function(src, dest)
{
	var
		scaleX,
		scaleY,
		scale,
		result = {};

	if (dest.width >= src.width && dest.height >= src.height)
	{
		result.width = src.width;
		result.height = src.height;
	}
	else
	{
		scaleX = dest.width/src.width;
		scaleY = dest.height/src.height;
		scale =  Math.min(scaleX, scaleY);
		result.width = Math.max(1, parseInt(scale*src.width , 10));
		result.height = Math.max(1, parseInt(scale*src.height , 10));
	}
	return result;
};

window.JCCatalogElement.prototype.showMagnifier = function(e)
{
	if (!this.magnify.magnifyShow)
	{
		this.calcMagnifierParams();
		this.calcMagnifyAreaSize();
		this.calcMagnifyAreaPos(e);
		this.calcMagnifyPictSize();
		this.calcMagnifyPictPos();
		this.setMagnifyAreaParams(true);
		this.setMagnifyPictParams(true);
		this.setMagnifierParams(true);
		BX.bind(document, 'mousemove', BX.proxy(this.moveMagnifierArea, this));
	}
};

window.JCCatalogElement.prototype.hideMagnifier = function()
{
	if (!this.magnify.magnifyShow)
	{
		if (!!this.magnify.obMagnifier)
		{
			BX.adjust(this.magnify.obMagnifier, { style: { display: 'none' } });
		}
		if (!!this.magnify.obMagnifyArea)
		{
			BX.adjust(this.magnify.obMagnifyArea, { style: { display: 'none' } });
		}
		BX.unbind(document, 'mousemove', BX.proxy(this.moveMagnifierArea, this));
	}
};

window.JCCatalogElement.prototype.moveMagnifierArea = function(e)
{
	var
		currentPos = {
			X: 0,
			Y: 0
		},
		posBigImg = BX.pos(this.obPict),
		intersect = {},
		params = {},
		paramsPict = {};

	currentPos = this.inRect(e, posBigImg);
	if (this.inBound(posBigImg, currentPos))
	{
		intersect = this.intersectArea(currentPos, posBigImg);
		switch (intersect.X)
		{
			case -1:
				this.magnify.areaParams.left = this.currentImg.screenOffsetX;
				break;
			case 0:
				this.magnify.areaParams.left = this.currentImg.screenOffsetX + currentPos.X - (this.magnify.areaParams.width >>> 1);
				break;
			case 1:
				this.magnify.areaParams.left = this.currentImg.screenOffsetX + posBigImg.width - this.magnify.areaParams.width;
				break;
		}
		switch (intersect.Y)
		{
			case -1:
				this.magnify.areaParams.top = 0;
				break;
			case 0:
				this.magnify.areaParams.top = currentPos.Y - (this.magnify.areaParams.height >>> 1);
				break;
			case 1:
				this.magnify.areaParams.top = posBigImg.height - this.magnify.areaParams.height;
				break;
		}
		this.magnify.magnifyPictParams.marginLeft = -parseInt(((this.magnify.areaParams.left-this.currentImg.screenOffsetX)*this.currentImg.scale), 10);
		this.magnify.magnifyPictParams.marginTop = -parseInt(((this.magnify.areaParams.top)*this.currentImg.scale), 10);
		params.left = this.magnify.areaParams.left+'px';
		params.top = this.magnify.areaParams.top+'px';
		BX.adjust(this.magnify.obMagnifyArea, { style: params });
		paramsPict.marginLeft = this.magnify.magnifyPictParams.marginLeft+'px';
		paramsPict.marginTop = this.magnify.magnifyPictParams.marginTop+'px';
		BX.adjust(this.magnify.obMagnifyPict, { style: paramsPict });
	}
	else
	{
		this.outMagnifierArea();
		this.hideMagnifier();
	}
};

window.JCCatalogElement.prototype.onMagnifierArea = function()
{
	this.magnify.magnifyShow = true;
};

window.JCCatalogElement.prototype.outMagnifierArea = function()
{
	this.magnify.magnifyShow = false;
};

window.JCCatalogElement.prototype.calcMagnifierParams = function()
{
	if (!!this.magnify.obBigImg)
	{
		var pos = BX.pos(this.magnify.obBigImg, true);

		this.magnify.magnifierParams.width = pos.width;
		this.magnify.magnifierParams.height = pos.height;
		this.magnify.magnifierParams.top = pos.top;
		this.magnify.magnifierParams.left = pos.left + pos.width + 2;
	}
};

window.JCCatalogElement.prototype.setMagnifierParams = function(show)
{
	if (!!this.magnify.obMagnifier)
	{
		show = !!show;
		var params = {
			top: this.magnify.magnifierParams.top+'px',
			left: this.magnify.magnifierParams.left+'px',
			width: this.magnify.magnifierParams.width+'px',
			height: this.magnify.magnifierParams.height+'px'
		};
		if (show)
		{
			params.display = '';
		}
		BX.adjust(this.magnify.obMagnifier, { style: params });
	}
};

window.JCCatalogElement.prototype.setMagnifyAreaParams = function(show)
{
	if (!!this.magnify.obMagnifier)
	{
		show = !!show;
		var params = {
			top: this.magnify.areaParams.top+'px',
			left: this.magnify.areaParams.left+'px',
			width: this.magnify.areaParams.width+'px',
			height: this.magnify.areaParams.height+'px'
		};
		if (show)
		{
			params.display = '';
		}
		BX.adjust(this.magnify.obMagnifyArea, { style: params });
	}
};

window.JCCatalogElement.prototype.calcMagnifyAreaPos = function(e)
{
	var currentPos,
		posBigImg,
		intersect;

	posBigImg = BX.pos(this.obPict);
	currentPos = this.inRect(e, posBigImg);
	if (this.inBound(posBigImg, currentPos))
	{
		intersect = this.intersectArea(currentPos, posBigImg);
		switch (intersect.X)
		{
			case -1:
				this.magnify.areaParams.left = this.currentImg.screenOffsetX;
				break;
			case 0:
				this.magnify.areaParams.left = this.currentImg.screenOffsetX + currentPos.X - (this.magnify.areaParams.width >>> 1);
				break;
			case 1:
				this.magnify.areaParams.left = this.currentImg.screenOffsetX + posBigImg.width - this.magnify.areaParams.width;
				break;
		}
		switch (intersect.Y)
		{
			case -1:
				this.magnify.areaParams.top = 0;
				break;
			case 0:
				this.magnify.areaParams.top = currentPos.Y - (this.magnify.areaParams.height >>> 1);
				break;
			case 1:
				this.magnify.areaParams.top = posBigImg.height - this.magnify.areaParams.height;
				break;
		}
	}
};

window.JCCatalogElement.prototype.inBound = function(rect, point)
{
	return ((0 <= point.Y && rect.height >= point.Y) && (0 <= point.X && rect.width >= point.X));
};

window.JCCatalogElement.prototype.inRect = function(e, rect)
{
	var wndSize = BX.GetWindowSize(),
		currentPos = {
			X: 0,
			Y: 0,
			globalX: 0,
			globalY: 0
		};

	currentPos.globalX = e.clientX + wndSize.scrollLeft;
	currentPos.X = currentPos.globalX - rect.left;
	currentPos.globalY = e.clientY + wndSize.scrollTop;
	currentPos.Y = currentPos.globalY - rect.top;
	return currentPos;
};

window.JCCatalogElement.prototype.intersectArea = function(currentPos, rect)
{
	var intersect = {
			X: 0,
			Y: 0
		},
		halfX = this.magnify.areaParams.width >>> 1,
		halfY = this.magnify.areaParams.height >>> 1;

	if (currentPos.X <= halfX)
	{
		intersect.X = -1;
	}
	else if (currentPos.X >= (rect.width - halfX))
	{
		intersect.X = 1;
	}
	else
	{
		intersect.X = 0;
	}
	if (currentPos.Y <= halfY)
	{
		intersect.Y = -1;
	}
	else if (currentPos.Y >= (rect.height - halfY))
	{
		intersect.Y = 1;
	}
	else
	{
		intersect.Y = 0;
	}

	return intersect;
};

window.JCCatalogElement.prototype.calcMagnifyAreaSize = function()
{
	var scaleX,
		scaleY,
		scale;

	if (
		this.magnify.magnifierParams.width < this.currentImg.width &&
			this.magnify.magnifierParams.height < this.currentImg.height
		)
	{
		scaleX = this.magnify.obBigImg.offsetWidth/this.currentImg.width;
		scaleY = this.magnify.obBigImg.offsetHeight/this.currentImg.height;
		scale =  Math.min(scaleX, scaleY);
		this.currentImg.scale = 1/scale;
		this.magnify.areaParams.width = Math.max(1, parseInt(scale*this.magnify.magnifierParams.width , 10));
		this.magnify.areaParams.height = Math.max(1, parseInt(scale*this.magnify.magnifierParams.height , 10));
		this.magnify.areaParams.scaleFactor = this.magnify.magnifierParams.defaultScale;
	}
	else
	{
		scaleX = this.obPict.offsetWidth/this.magnify.obBigImg.offsetWidth;
		scaleY = this.obPict.offsetHeight/this.magnify.obBigImg.offsetHeight;
		scale =  Math.min(scaleX, scaleY);
		this.currentImg.scale = 1/scale;
		this.magnify.areaParams.width = Math.max(1, parseInt(scale*this.magnify.magnifierParams.width , 10));
		this.magnify.areaParams.height = Math.max(1, parseInt(scale*this.magnify.magnifierParams.height , 10));

		scaleX = this.magnify.magnifierParams.width/this.currentImg.width;
		scaleY = this.magnify.magnifierParams.height/this.currentImg.height;
		scale = Math.max(scaleX, scaleY);
		this.magnify.areaParams.scaleFactor = scale;
	}
};

window.JCCatalogElement.prototype.calcMagnifyPictSize = function()
{
	this.magnify.magnifyPictParams.width = this.currentImg.width*this.magnify.areaParams.scaleFactor;
	this.magnify.magnifyPictParams.height = this.currentImg.height*this.magnify.areaParams.scaleFactor;
};

window.JCCatalogElement.prototype.calcMagnifyPictPos = function()
{
	this.magnify.magnifyPictParams.marginLeft = -parseInt(((this.magnify.areaParams.left-this.currentImg.screenOffsetX)*this.currentImg.scale), 10);
	this.magnify.magnifyPictParams.marginTop = -parseInt(((this.magnify.areaParams.top)*this.currentImg.scale), 10);
};

window.JCCatalogElement.prototype.setMagnifyPictParams = function(show)
{
	if (!!this.magnify.obMagnifier)
	{
		show = !!show;
		var params = {
			width: this.magnify.magnifyPictParams.width+'px',
			height: this.magnify.magnifyPictParams.height+'px',
			marginTop: this.magnify.magnifyPictParams.marginTop+'px',
			marginLeft: this.magnify.magnifyPictParams.marginLeft+'px'
		};
		if (show)
		{
			params.display = '';
		}
		BX.adjust(this.magnify.obMagnifyPict, { style: params, props: { src: this.currentImg.src } });
	}
};

window.JCCatalogElement.prototype.ProductSliderRowLeft = function()
{
	var target = BX.proxy_context;
	if (!!target)
	{
		if (this.sliderRowShowSize < this.product.slider.COUNT)
		{
			if (0 > this.product.slider.START)
			{
				this.product.slider.START++;
				BX.adjust(this.product.slider.LIST, { style: { marginLeft: this.product.slider.START*20+'%' }});
				BX.adjust(this.product.slider.RIGHT, { style: this.sliderEnableArrow });
			}

			if (0 <= this.product.slider.START)
			{
				BX.adjust(this.product.slider.LEFT, { style: this.sliderDisableArrow });
			}
		}
	}
};

window.JCCatalogElement.prototype.ProductSliderRowRight = function()
{
	var target = BX.proxy_context;
	if (!!target)
	{
		if (this.sliderRowShowSize < this.product.slider.COUNT)
		{
			if ((this.sliderRowShowSize - this.product.slider.START) < this.product.slider.COUNT)
			{
				this.product.slider.START--;
				BX.adjust(this.product.slider.LIST, { style: { marginLeft: this.product.slider.START*20+'%' }});
				BX.adjust(this.product.slider.LEFT, { style: this.sliderEnableArrow } );
			}

			if ((this.sliderRowShowSize - this.product.slider.START) >= this.product.slider.COUNT)
			{
				BX.adjust(this.product.slider.RIGHT, { style: this.sliderDisableArrow } );
			}
		}
	}
};

window.JCCatalogElement.prototype.ProductSelectSliderImg = function()
{
	var strValue = '',
		target = BX.proxy_context;
	if (!!target && target.hasAttribute('data-value'))
	{
		strValue = target.getAttribute('data-value');
		this.SetProductMainPict(strValue);
	}
};

window.JCCatalogElement.prototype.SetProductMainPict = function(intPict)
{
	var indexPict = -1,
		i = 0,
		j = 0,
		value = '',
		strValue = '',
		RowItems = null;

	if (0 < this.product.sliderCount)
	{
		for (j = 0; j < this.product.sliderPict.length; j++)
		{
			if (intPict === this.product.sliderPict[j].ID)
			{
				indexPict = j;
				break;
			}
		}
		if (-1 < indexPict)
		{
			if (!!this.product.sliderPict[indexPict])
			{
				this.setCurrentImg(this.product.sliderPict[indexPict], true);
			}
			RowItems = BX.findChildren(this.product.slider.LIST, {tagName: 'li'}, false);
			if (!!RowItems && 0 < RowItems.length)
			{
				strValue = intPict;
				for (i = 0; i < RowItems.length; i++)
				{
					value = RowItems[i].getAttribute('data-value');
					if (value === strValue)
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

window.JCCatalogElement.prototype.SliderRowLeft = function()
{
	var strValue = '',
		index = -1,
		i,
		target = BX.proxy_context;
	if (!!target && target.hasAttribute('data-value'))
	{
		strValue = target.getAttribute('data-value');
		for (i = 0; i < this.sliders.length; i++)
		{
			if (this.sliders[i].OFFER_ID === strValue)
			{
				index = i;
				break;
			}
		}
		if (-1 < index && this.sliderRowShowSize < this.sliders[index].COUNT)
		{
			if (0 > this.sliders[index].START)
			{
				this.sliders[index].START++;
				BX.adjust(this.sliders[index].LIST, { style: { marginLeft: this.sliders[index].START*20+'%' }});
				BX.adjust(this.sliders[index].RIGHT, { style: this.sliderEnableArrow });
			}

			if (0 <= this.sliders[index].START)
			{
				BX.adjust(this.sliders[index].LEFT, { style: this.sliderDisableArrow });
			}
		}
	}
};

window.JCCatalogElement.prototype.SliderRowRight = function()
{
	var strValue = '',
		index = -1,
		i,
		target = BX.proxy_context;
	if (!!target && target.hasAttribute('data-value'))
	{
		strValue = target.getAttribute('data-value');
		for (i = 0; i < this.sliders.length; i++)
		{
			if (this.sliders[i].OFFER_ID === strValue)
			{
				index = i;
				break;
			}
		}
		if (-1 < index && this.sliderRowShowSize < this.sliders[index].COUNT)
		{
			if ((this.sliderRowShowSize - this.sliders[index].START) < this.sliders[index].COUNT)
			{
				this.sliders[index].START--;
				BX.adjust(this.sliders[index].LIST, { style: { marginLeft: this.sliders[index].START*20+'%' }});
				BX.adjust(this.sliders[index].LEFT, { style: this.sliderEnableArrow } );
			}

			if ((this.sliderRowShowSize - this.sliders[index].START) >= this.sliders[index].COUNT)
			{
				BX.adjust(this.sliders[index].RIGHT, { style: this.sliderDisableArrow } );
			}
		}
	}
};

window.JCCatalogElement.prototype.SelectSliderImg = function()
{
	var strValue = '',
		arItem = [],
		target = BX.proxy_context;
	if (!!target && target.hasAttribute('data-value'))
	{
		strValue = target.getAttribute('data-value');
		arItem = strValue.split('_');
		this.SetMainPict(arItem[0], arItem[1]);
	}
};

window.JCCatalogElement.prototype.SetMainPict = function(intSlider, intPict)
{
	var index = -1,
		indexPict = -1,
		i,
		j,
		value = '',
		RowItems = null,
		strValue = '';

	for (i = 0; i < this.offers.length; i++)
	{
		if (intSlider === this.offers[i].ID)
		{
			index = i;
			break;
		}
	}
	if (-1 < index)
	{
		if (0 < this.offers[index].SLIDER_COUNT)
		{
			for (j = 0; j < this.offers[index].SLIDER.length; j++)
			{
				if (intPict === this.offers[index].SLIDER[j].ID)
				{
					indexPict = j;
					break;
				}
			}
			if (-1 < indexPict)
			{
				if (!!this.offers[index].SLIDER[indexPict])
				{
					this.setCurrentImg(this.offers[index].SLIDER[indexPict], true);
				}
				RowItems = BX.findChildren(this.sliders[index].LIST, {tagName: 'li'}, false);
				if (!!RowItems && 0 < RowItems.length)
				{
					strValue = intSlider+'_'+intPict;
					for (i = 0; i < RowItems.length; i++)
					{
						value = RowItems[i].getAttribute('data-value');
						if (value === strValue)
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
	}
};

window.JCCatalogElement.prototype.SetMainPictFromItem = function(index)
{
	if (!!this.obPict)
	{
		var boolSet = false,
			obNewPict = {};

		if (!!this.offers[index])
		{
			if (!!this.offers[index].DETAIL_PICTURE)
			{
				obNewPict = this.offers[index].DETAIL_PICTURE;
				boolSet = true;
			}
			else if (!!this.offers[index].PREVIEW_PICTURE)
			{
				obNewPict = this.offers[index].PREVIEW_PICTURE;
				boolSet = true;
			}
		}
		if (!boolSet)
		{
			if (!!this.defaultPict.detail)
			{
				obNewPict = this.defaultPict.detail;
				boolSet = true;
			}
			else if (!!this.defaultPict.preview)
			{
				obNewPict = this.defaultPict.preview;
				boolSet = true;
			}
		}
		if (boolSet)
		{
			this.setCurrentImg(obNewPict, true);
		}
	}
};

window.JCCatalogElement.prototype.showMainPictPopup = function(e)
{
	var pictContent = '';

	pictContent = '<div style="text-align: center;"><img src="'+
		this.currentImg.src+
		'" width="'+this.currentImg.width+'" height="'+this.currentImg.height+'" name=""></div>';
	this.obPopupPict.setContent(pictContent);
	this.obPopupPict.show();
	return BX.PreventDefault(e);
};

window.JCCatalogElement.prototype.QuantityUp = function()
{
	var curValue = 0,
		boolSet = true,
		calcPrice;

	if (0 === this.errorCode && this.config.showQuantity && this.canBuy)
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

window.JCCatalogElement.prototype.QuantityDown = function()
{
	var curValue = 0,
		boolSet = true,
		calcPrice;

	if (0 === this.errorCode && this.config.showQuantity && this.canBuy)
	{
		curValue = (this.isDblQuantity ? parseFloat(this.obQuantity.value) : parseInt(this.obQuantity.value, 10));
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

window.JCCatalogElement.prototype.QuantityChange = function()
{
	var curValue = 0,
		calcPrice,
		intCount,
		count;

	if (0 === this.errorCode && this.config.showQuantity)
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
					count = curValue/this.stepQuantity;
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

window.JCCatalogElement.prototype.QuantitySet = function(index)
{
	var basisPrice = '',
		strLimit;
	if (this.errorCode === 0)
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
		if (this.config.showQuantity)
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
			if (!!this.obQuantityLimit.all)
			{
				if (!this.checkQuantity)
				{
					BX.adjust(this.obQuantityLimit.value, { html: '' });
					BX.adjust(this.obQuantityLimit.all, { style: {display: 'none'} });
				}
				else
				{
					strLimit = this.offers[index].MAX_QUANTITY;
					if (!!this.offers[index].MEASURE)
					{
						strLimit += (' '+this.offers[index].MEASURE);
					}
					BX.adjust(this.obQuantityLimit.value, { html: strLimit});
					BX.adjust(this.obQuantityLimit.all, { style: {display: ''} });
				}
			}
			if (!!this.obBasisPrice)
			{
				if (!!this.offers[index].BASIS_PRICE)
				{
					basisPrice = BX.message('BASIS_PRICE_MESSAGE');
					basisPrice = basisPrice.replace(
						'#PRICE#',
						BX.Currency.currencyFormat(this.offers[index].BASIS_PRICE.DISCOUNT_VALUE, this.offers[index].BASIS_PRICE.CURRENCY, true)
					);
					basisPrice = basisPrice.replace('#MEASURE#', this.offers[index].MEASURE);
					BX.adjust(this.obBasisPrice, { style: { display: '' }, html: basisPrice });
				}
				else
				{
					BX.adjust(this.obBasisPrice, { style: { display: 'none' }, html: '' });
				}
			}
		}
		this.currentBasisPrice = this.offers[index].BASIS_PRICE;
	}
};

window.JCCatalogElement.prototype.SelectOfferProp = function()
{
	var i = 0,
		strTreeValue = '',
		arTreeItem = [],
		RowItems = null,
		target = BX.proxy_context;

	if (!!target && target.hasAttribute('data-treevalue'))
	{
		strTreeValue = target.getAttribute('data-treevalue');
		arTreeItem = strTreeValue.split('_');
		this.SearchOfferPropIndex(arTreeItem[0], arTreeItem[1]);
		RowItems = BX.findChildren(target.parentNode, {tagName: 'li'}, false);
		if (!!RowItems && 0 < RowItems.length)
		{
			for (i = 0; i < RowItems.length; i++)
			{
				BX.removeClass(RowItems[i], 'bx_active');
			}
		}
		BX.addClass(target, 'bx_active');
	}
};

window.JCCatalogElement.prototype.SearchOfferPropIndex = function(strPropID, strPropValue)
{
	var strName = '',
		arShowValues = false,
		arCanBuyValues = [],
		index = -1,
		i, j,
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
		arFilter[strName] = strPropValue;
		for (i = index+1; i < this.treeProps.length; i++)
		{
			strName = 'PROP_'+this.treeProps[i].ID;
			arShowValues = this.GetRowValues(arFilter, strName);
			if (!arShowValues)
			{
				break;
			}
			if (this.config.showAbsent)
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
};

window.JCCatalogElement.prototype.RowLeft = function()
{
	var strTreeValue = '',
		index = -1,
		i,
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

window.JCCatalogElement.prototype.RowRight = function()
{
	var strTreeValue = '',
		index = -1,
		i,
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

window.JCCatalogElement.prototype.UpdateRow = function(intNumber, activeID, showID, canBuyID)
{
	var i = 0,
		showI = 0,
		value = '',
		countShow = 0,
		strNewLen = '',
		obData = {},
		RowItems = null,
		pictMode = false,
		extShowMode = false,
		isCurrent = false,
		selectIndex = 0,
		obLeft = this.treeEnableArrow,
		obRight = this.treeEnableArrow,
		currentShowStart = 0;

	if (-1 < intNumber && intNumber < this.obTreeRows.length)
	{
		RowItems = BX.findChildren(this.obTreeRows[intNumber].LIST, {tagName: 'li'}, false);
		if (!!RowItems && 0 < RowItems.length)
		{
			pictMode = ('PICT' === this.treeProps[intNumber].SHOW_MODE);
			countShow = showID.length;
			extShowMode = this.treeRowShowSize < countShow;
			strNewLen = (this.treeRowShowSize < countShow ? (100/countShow)+'%' : '20%');
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
				BX.adjust(this.obTreeRows[intNumber].CONT, {props: {className: (extShowMode < countShow ? 'bx_item_detail_size full' : 'bx_item_detail_size')}});
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

window.JCCatalogElement.prototype.GetRowValues = function(arFilter, index)
{
	var arValues = [],
		i = 0,
		j = 0,
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

window.JCCatalogElement.prototype.GetCanBuy = function(arFilter)
{
	var i = 0,
		j = 0,
		boolOneSearch = true,
		boolSearch = false;

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

window.JCCatalogElement.prototype.SetCurrent = function()
{
	var i = 0,
		j = 0,
		strName = '',
		arShowValues = false,
		arCanBuyValues = [],
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
		if (this.config.showAbsent)
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

window.JCCatalogElement.prototype.ChangeInfo = function()
{
	var index = -1,
		i = 0,
		j = 0,
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
		this.setPrice(this.offers[index].PRICE);
		for (i = 0; i < this.offers.length; i++)
		{
			if (this.config.showOfferGroup && this.offers[i].OFFER_GROUP)
			{
				if (i !== index)
				{
					BX.adjust(BX(this.visual.OFFER_GROUP+this.offers[i].ID), { style: {display: 'none'} });
				}
			}
			if (!!this.sliders[i].ID)
			{
				if (i === index)
				{
					this.sliders[i].START = 0;
					BX.adjust(this.sliders[i].LIST, {style: { marginLeft: '0%' }});
					BX.adjust(this.sliders[i].CONT, {style: { display: ''}});
					BX.adjust(this.sliders[i].LEFT, { style: this.sliderDisableArrow } );
					BX.adjust(this.sliders[i].RIGHT, { style: this.sliderEnableArrow } );
				}
				else
				{
					BX.adjust(this.sliders[i].CONT, {style: { display: 'none'}});
				}
			}
		}
		if (this.config.showOfferGroup && this.offers[index].OFFER_GROUP)
		{
			BX.adjust(BX(this.visual.OFFER_GROUP+this.offers[index].ID), { style: {display: ''} });
		}
		if (0 < this.offers[index].SLIDER_COUNT)
		{
			this.SetMainPict(this.offers[index].ID, this.offers[index].SLIDER[0].ID);
		}
		else
		{
			this.SetMainPictFromItem(index);
		}

		if (this.config.showSkuProps && !!this.obSkuProps)
		{
			if (!this.offers[index].DISPLAY_PROPERTIES || this.offers[index].DISPLAY_PROPERTIES.length === 0)
			{
				BX.adjust(this.obSkuProps, {style: {display: 'none'}, html: ''});
			}
			else
			{
				BX.adjust(this.obSkuProps, {style: {display: ''}, html: this.offers[index].DISPLAY_PROPERTIES});
			}
		}
		this.offerNum = index;
		this.QuantitySet(this.offerNum);

		this.incViewedCounter();
		BX.onCustomEvent('onCatalogStoreProductChange', [this.offers[this.offerNum].ID]);
	}
};

window.JCCatalogElement.prototype.setPrice = function(price)
{
	var economyInfo = '';

	if (!!this.obPrice.price)
	{
		BX.adjust(this.obPrice.price, {html: BX.Currency.currencyFormat(price.DISCOUNT_VALUE, price.CURRENCY, true)});
		if (price.DISCOUNT_VALUE !== price.VALUE)
		{
			if (this.config.showOldPrice)
			{
				if (!!this.obPrice.full)
				{
					BX.adjust(this.obPrice.full, {style: {display: ''}, html: BX.Currency.currencyFormat(price.VALUE, price.CURRENCY, true)});
				}
				if (!!this.obPrice.discount)
				{
					economyInfo = BX.message('ECONOMY_INFO_MESSAGE');
					economyInfo = economyInfo.replace('#ECONOMY#', BX.Currency.currencyFormat(price.DISCOUNT_DIFF, price.CURRENCY, true));
					BX.adjust(this.obPrice.discount, {style: {display: ''}, html: economyInfo });
				}
			}
			if (this.config.showPercent)
			{
				if (!!this.obPrice.percent)
				{
					BX.adjust(this.obPrice.percent, {style: {display: ''}, html: price.DISCOUNT_DIFF_PERCENT+'%'});
				}
			}
		}
		else
		{
			if (this.config.showOldPrice)
			{
				if (!!this.obPrice.full)
				{
					BX.adjust(this.obPrice.full, {style: {display: 'none'}, html: ''});
				}
				if (!!this.obPrice.discount)
				{
					BX.adjust(this.obPrice.discount, {style: {display: 'none'}, html: ''});
				}
			}
			if (this.config.showPercent)
			{
				if (!!this.obPrice.percent)
				{
					BX.adjust(this.obPrice.percent, {style: {display: 'none'}, html: ''});
				}
			}
		}
	}
};

window.JCCatalogElement.prototype.Compare = function()
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

window.JCCatalogElement.prototype.CompareResult = function(result)
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
		if (this.config.showClosePopup)
		{
			popupButtons = [
				new BasketButton({
					ownerClass: this.obProduct.className,
					text: BX.message('BTN_MESSAGE_COMPARE_REDIRECT'),
					events: {
						click: BX.delegate(this.CompareRedirect, this)
					},
					style: {marginRight: '10px'}
				}),
				new BasketButton({
					ownerClass: this.obProduct.className,
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
					ownerClass: this.obProduct.className,
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
				ownerClass: this.obProduct.className,
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

window.JCCatalogElement.prototype.CompareRedirect = function()
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

window.JCCatalogElement.prototype.InitBasketUrl = function()
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
	if (this.config.showQuantity)
	{
		this.basketParams[this.basketData.quantity] = this.obQuantity.value;
	}
	if (!!this.basketData.sku_props)
	{
		this.basketParams[this.basketData.sku_props_var] = this.basketData.sku_props;
	}
};

window.JCCatalogElement.prototype.FillBasketProps = function()
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

window.JCCatalogElement.prototype.SendToBasket = function()
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
		BX.proxy(this.BasketResult, this)
	);
};

window.JCCatalogElement.prototype.Add2Basket = function()
{
	this.basketMode = 'ADD';
	this.Basket();
};

window.JCCatalogElement.prototype.BuyBasket = function()
{
	this.basketMode = 'BUY';
	this.Basket();
};

window.JCCatalogElement.prototype.Basket = function()
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
					ownerClass: this.obProduct.className,
					text: BX.message('BTN_SEND_PROPS'),
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

window.JCCatalogElement.prototype.BasketResult = function(arResult)
{
	var popupContent, popupButtons, popupTitle, productPict;
	if (!!this.obPopupWin)
	{
		this.obPopupWin.close();
	}
	if (typeof arResult !== 'object')
	{
		return false;
	}
	if (arResult.STATUS === 'OK' && this.basketMode === 'BUY')
	{
		this.BasketRedirect();
	}
	else
	{
		this.InitPopupWindow();
		popupTitle = {
			content: BX.create('div', {
				style: { marginRight: '30px', whiteSpace: 'nowrap' },
				text: (arResult.STATUS === 'OK' ? BX.message('TITLE_SUCCESSFUL') : BX.message('TITLE_ERROR'))
			})
		};
		if (arResult.STATUS === 'OK')
		{
			BX.onCustomEvent('OnBasketChange');
			switch (this.productType)
			{
				case 1://
				case 2://
					productPict = this.product.pict.SRC;
					break;
				case 3:
					productPict = (!!this.offers[this.offerNum].PREVIEW_PICTURE ?
						this.offers[this.offerNum].PREVIEW_PICTURE.SRC :
						this.defaultPict.pict.SRC
						);
					break;
			}
			popupContent = '<div style="width: 96%; margin: 10px 2%; text-align: center;"><img src="'+productPict+'" height="130"><p>'+this.product.name+'</p></div>';
			if (this.config.showClosePopup)
			{
				popupButtons = [
					new BasketButton({
						ownerClass: this.obProduct.className,
						text: BX.message('BTN_MESSAGE_BASKET_REDIRECT'),
						events: {
							click: BX.delegate(this.BasketRedirect, this)
						},
						style: {marginRight: '10px'}
					}),
					new BasketButton({
						ownerClass: this.obProduct.className,
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
						ownerClass: this.obProduct.className,
						text: BX.message('BTN_MESSAGE_BASKET_REDIRECT'),
						events: {
							click: BX.delegate(this.BasketRedirect, this)
						}
					})
				];
			}
		}
		else
		{
			popupContent = '<div style="width: 96%; margin: 10px 2%; text-align: center;"><p>'+(!!arResult.MESSAGE ? arResult.MESSAGE : BX.message('BASKET_UNKNOWN_ERROR'))+'</p></div>';
			popupButtons = [
				new BasketButton({
					ownerClass: this.obProduct.className,
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
	}
	return false;
};

window.JCCatalogElement.prototype.BasketRedirect = function()
{
	location.href = (!!this.basketData.basketUrl ? this.basketData.basketUrl : BX.message('BASKET_URL'));
};

window.JCCatalogElement.prototype.InitPopupWindow = function()
{
	if (!!this.obPopupWin)
	{
		return;
	}
	this.obPopupWin = BX.PopupWindowManager.create('CatalogElementBasket_'+this.visual.ID, null, {
		autoHide: false,
		offsetLeft: 0,
		offsetTop: 0,
		overlay : true,
		closeByEsc: true,
		titleBar: true,
		closeIcon: {top: '10px', right: '10px'}
	});
};

window.JCCatalogElement.prototype.onPopupWindowShow = function(popup)
{
	BX.bind(document, 'click', BX.proxy(this.popupWindowClick, this));
};

window.JCCatalogElement.prototype.onPopupWindowClose = function(popup, event)
{
	BX.unbind(document, 'click', BX.proxy(this.popupWindowClick, this));
};

window.JCCatalogElement.prototype.popupWindowClick = function()
{
	if (!!this.obPopupPict && typeof (this.obPopupPict) === 'object')
	{
		if (this.obPopupPict.isShown())
		{
			this.obPopupPict.close();
		}
	}
};

window.JCCatalogElement.prototype.incViewedCounter = function()
{
	if (this.currentIsSet && !this.updateViewedCount)
	{
		switch (this.productType)
		{
			case 1:
			case 2:
				this.viewedCounter.params.PRODUCT_ID = this.product.id;
				this.viewedCounter.params.PARENT_ID = this.product.id;
				break;
			case 3:
				this.viewedCounter.params.PARENT_ID = this.product.id;
				this.viewedCounter.params.PRODUCT_ID = this.offers[this.offerNum].ID;
				break;
			default:
				return;
		}
		this.viewedCounter.params.SITE_ID = BX.message('SITE_ID');
		this.updateViewedCount = true;
		BX.ajax.post(
			this.viewedCounter.path,
			this.viewedCounter.params,
			BX.delegate(function(){ this.updateViewedCount = false; }, this)
		);
	}
};

window.JCCatalogElement.prototype.allowViewedCount = function(update)
{
	update = !!update;
	this.currentIsSet = true;
	if (update)
	{
		this.incViewedCounter();
	}
};
})(window);
/* End */
;
; /* Start:"a:4:{s:4:"full";s:78:"/bitrix/components/bitrix/iblock.vote/templates/stars/script.js?14338395603018";s:6:"source";s:63:"/bitrix/components/bitrix/iblock.vote/templates/stars/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
;(function(window) {
	if (window.JCIblockVoteStars)
		return;

	JCIblockVoteStars = function (params)
	{
		this.progressId = params.progressId;
		this.ratingId = params.ratingId;
		this.starsId = params.starsId;
		this.ajaxUrl = params.ajaxUrl;
		this.voteId = params.voteId;
		this.starsObj = null;
		this.votedValue = null;
		this.preVotedValue = null;
	};

	JCIblockVoteStars.prototype.setValue = function(value)
	{
		value = parseInt(value);
		if (isNaN(value))
			value = 0;
		else if(value > 100)
			value = 100;
		else if(value < 0)
			value = 0;

		var progressObj = BX(this.progressId);

		if(!!progressObj)
		{
			progressObj.style.width = parseInt(value)+"%";
			this.votedValue = value;
		}
	};

	JCIblockVoteStars.prototype.setVotes = function(value)
	{
		var ratingObj = BX(this.ratingId);

		if(!!ratingObj)
			ratingObj.innerHTML = "( "+value+" )";
	};

	JCIblockVoteStars.prototype.getStarsObj = function()
	{
		if(!this.starsObj)
			this.starsObj = BX(this.starsId);

		return this.starsObj;
	};

	//todo: IE 8 has no pageX, pageY.
	JCIblockVoteStars.prototype.onMouseMove = function(event)
	{
		var starsPos = BX.pos(this.getStarsObj());

		var voteValue = (event.pageX - starsPos.left)/starsPos.width*5;

		for (var i = 1; i <= 5; i++)
		{
			if(voteValue < i)
			{
				voteValue = i;
				break;
			}
		}

		this.setValue(voteValue*20);
	};

	JCIblockVoteStars.prototype.onMouseOver = function(event)
	{
		BX.bind(this.getStarsObj(), 'click', BX.proxy(this.onVote, this));
		this.preVotedValue = this.votedValue;
	};

	JCIblockVoteStars.prototype.onMouseOut = function(event)
	{
		BX.unbind(this.getStarsObj(), 'click', BX.proxy(this.onVote, this));
		this.votedValue = this.preVotedValue;
		this.setValue(this.votedValue);
	};

	JCIblockVoteStars.prototype.onVote = function(event)
	{
		this.unBindEvents();

		this.ajaxParams.rating = parseInt(this.votedValue/20)-1;
		this.ajaxParams.vote = "Y";
		this.ajaxParams.vote_id = this.voteId;

		BX.ajax({
			timeout:   30,
			method:   'POST',
			dataType: 'json',
			url:       this.ajaxUrl,
			data:      this.ajaxParams,
			onsuccess: BX.delegate(this.SetResult, this)
		});
	};

	JCIblockVoteStars.prototype.SetResult = function(result)
	{
		if (!!result && !result.ERROR)
		{
			this.setValue((result.value+1)*20);
			this.setVotes(result.votes);
		}
	};

	JCIblockVoteStars.prototype.bindEvents = function()
	{
		var starsObj = this.getStarsObj();
		BX.bind(starsObj, 'mousemove', BX.proxy(this.onMouseMove, this));
		BX.bind(starsObj, 'mouseover', BX.proxy(this.onMouseOver, this));
		BX.bind(starsObj, 'mouseout', BX.proxy(this.onMouseOut, this));
	};

	JCIblockVoteStars.prototype.unBindEvents = function()
	{
		var starsObj = this.getStarsObj();
		BX.unbind(starsObj, 'mousemove', BX.proxy(this.onMouseMove, this));
		BX.unbind(starsObj, 'mouseover', BX.proxy(this.onMouseOver, this));
		BX.unbind(starsObj, 'mouseout', BX.proxy(this.onMouseOut, this));
		BX.unbind(this.getStarsObj(), 'click', BX.proxy(this.onVote, this));
	};
})(window);
/* End */
;
; /* Start:"a:4:{s:4:"full";s:88:"/bitrix/components/bitrix/catalog.brandblock/templates/.default/script.js?14338395571822";s:6:"source";s:73:"/bitrix/components/bitrix/catalog.brandblock/templates/.default/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
;(function(window) {
if (window.JCIblockBrands)
{
	return;
}

window.JCIblockBrands = function (params)
{
	this.blockID = '';
	this.obBlock = null;
	this.dataName = 'data-popup';
	if (!!params && typeof params === 'object')
	{
		if (!!params.blockID && BX.type.isNotEmptyString(params.blockID))
		{
			this.blockID = params.blockID;
		}
		if (!!params.dataName && BX.type.isNotEmptyString(params.dataName))
		{
			this.dataName = params.dataName;
		}
	}
	if (this.blockID !== '')
	{
		BX.ready(BX.delegate(this.Init, this));
	}
};

window.JCIblockBrands.prototype.Init = function()
{
	if (this.blockID === '')
	{
		return;
	}
	this.obBlock = BX(this.blockID);
	if (!!this.obBlock)
	{
		BX.bindDelegate(this.obBlock, 'mouseover', { 'attribute': this.dataName }, BX.proxy(this.mouseOver, this));
		BX.bindDelegate(this.obBlock, 'mouseout', { 'attribute': this.dataName }, BX.proxy(this.mouseOut, this));
	}
};

window.JCIblockBrands.prototype.mouseOver = function()
{
	var strValue = '',
		target = BX.proxy_context,
		popup = null,
		popupParams = null;

	if (!!target && target.hasAttribute(this.dataName))
	{
		strValue = target.getAttribute(this.dataName);
		popup = BX(strValue);
		if (!!popup)
		{
			if (!BX.hasClass(target, 'hover'))
			{
				BX.addClass(target, 'hover');
				if (popup.offsetHeight > 40)
				{
					popup.style.top = "-1px";
				}
				else
				{
					popup.style.top = "50%";
					popup.style.marginTop = "-"+parseInt(popup.offsetHeight, 10)/2+"px";
				}
			}
		}
	}
};

window.JCIblockBrands.prototype.mouseOut = function()
{
	var strValue = '',
		target = BX.proxy_context,
		popup = null;
	if (!!target && target.hasAttribute(this.dataName))
	{
		strValue = target.getAttribute(this.dataName);
		popup = BX(strValue);
		if (!!popup)
		{
			BX.removeClass(target, "hover");
		}
	}
};
})(window);
/* End */
;
; /* Start:"a:4:{s:4:"full";s:86:"/bitrix/components/bitrix/catalog.comments/templates/.default/script.js?14338395655409";s:6:"source";s:71:"/bitrix/components/bitrix/catalog.comments/templates/.default/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
;(function(window) {
if (!!window.JCCatalogSocnetsComments)
{
	return;
}

window.JCCatalogSocnetsComments = function(arParams)
{
	var i;

	this.errorCode = 0;

	this.params = {};

	this.serviceList = {
		blog: false,
		facebook: false,
		vk: false
	};
	this.settings = {
		blog: {
			ajaxUrl: '',
			ajaxParams: {},
			contID: 'bx-cat-soc-comments-blg'
		},
		facebook: {
			contID: 'bx-cat-soc-comments-fb',
			contWidthID: '',
			parentContID: 'soc_comments',
			facebookJSDK: 'facebook-jssdk',
			facebookPath: ''
		},
		vk: {}
	};

	this.services = {
		blog: {
			obBlogCont: null
		},
		facebook: {
			obFBCont: null,
			obFBContWidth: null,
			obFBParentCont: null,
			obFBjSDK: null,
			currentWidth: 0
		}
	};

	if ('object' === typeof arParams)
	{
		this.params = arParams;
		if (!!this.params.serviceList && typeof(this.params.serviceList) === 'object')
		{
			for (i in this.serviceList)
			{
				if (this.serviceList.hasOwnProperty(i) && !!this.params.serviceList[i])
				{
					this.serviceList[i] = true;
				}
			}
		}
		if (this.serviceList.blog)
		{
			this.initParams('blog');
		}
		if (this.serviceList.facebook)
		{
			this.initParams('facebook');
		}
	}
	else
	{
		this.errorCode = -1;
	}

	if (this.errorCode === 0)
	{
		BX.ready(BX.delegate(this.Init, this));
	}
};

window.JCCatalogSocnetsComments.prototype.initParams = function(id)
{
	var i;

	if (!!this.params.settings && typeof(this.params.settings) === 'object' && typeof(this.params.settings[id]) === 'object')
	{
		for (i in this.settings[id])
		{
			if (this.settings[id].hasOwnProperty(i) && !!this.params.settings[id][i])
			{
				this.settings[id][i] = this.params.settings[id][i];
			}
		}
	}
};

window.JCCatalogSocnetsComments.prototype.Init = function()
{
	if (this.serviceList.blog)
	{
		this.services.blog.obBlogCont = BX(this.settings.blog.contID);
		if (!this.services.blog.obBlogCont)
		{
			this.serviceList.blog = false;
			this.errorCode = -16;
		}
	}
	if (this.serviceList.facebook)
	{
		this.services.facebook.obFBCont = BX(this.settings.facebook.contID);
		if (!this.services.facebook.obFBCont)
		{
			this.serviceList.facebook = false;
			this.errorCode = -32;
		}
		else
		{
			this.services.facebook.obFBContWidth = this.services.facebook.obFBCont.firstChild;
		}
		this.services.facebook.obFBParentCont = BX(this.settings.facebook.parentContID);
	}

	if (this.errorCode === 0)
	{
		if (this.serviceList.blog)
		{
			this.loadBlog();
		}
		if (this.serviceList.facebook)
		{
			this.loadFB();
		}
	}

	this.params = {};
};

window.JCCatalogSocnetsComments.prototype.loadBlog = function()
{
	var postData;

	if (this.errorCode !== 0 || !this.serviceList.blog || this.settings.blog.ajaxUrl.length === 0)
	{
		return;
	}

	postData = this.settings.blog.ajaxParams;
	postData.sessid = BX.bitrix_sessid();
	BX.ajax({
		timeout:   30,
		method:   'POST',
		dataType: 'html',
		url:       this.settings.blog.ajaxUrl,
		data:      postData,
		onsuccess: BX.proxy(this.loadBlogResult, this)
	});
};

window.JCCatalogSocnetsComments.prototype.loadBlogResult = function(result)
{
	if (BX.type.isNotEmptyString(result))
	{
		BX.adjust(this.services.blog.obBlogCont, { html: result });
	}
};

window.JCCatalogSocnetsComments.prototype.loadFB = function()
{
	var width;

	if (this.services.facebook.obFBParentCont && this.services.facebook.obFBContWidth)
	{
		width = parseInt(this.services.facebook.obFBParentCont.offsetWidth, 10);
		if (!isNaN(width))
		{
			BX.adjust(this.services.facebook.obFBContWidth, { attrs: { 'data-width': (width-20) } });
			this.services.facebook.currentWidth = width;
		}

		if (!this.services.facebook.obFBjSDK)
		{
			this.services.facebook.obFBjSDK = true;
			BX.defer(BX.proxy((function(d, s, id, fbpath) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (d.getElementById(id))
				{
					return;
				}
				js = d.createElement(s); js.id = id;
				js.src = fbpath;
				fjs.parentNode.insertBefore(js, fjs);
			}(document, "script", this.settings.facebook.facebookJSDK, this.settings.facebook.facebookPath)), this));
		}
	}
};

window.JCCatalogSocnetsComments.prototype.getFBParentWidth = function()
{
	var width = 0;
	if (!!this.services.facebook.obFBParentCont)
	{
		width = parseInt(this.services.facebook.obFBParentCont.offsetWidth, 10);
		if (isNaN(width))
		{
			width = 0;
		}
	}
	return width;
};

window.JCCatalogSocnetsComments.prototype.setFBWidth = function(width)
{
	var obFrame = null,
		src,
		newSrc;

	if (
		this.serviceList.facebook &&
		this.services.facebook.currentWidth !== width &&
		width > 20 &&
		!!this.services.facebook.obFBContWidth
	)
	{
		if (!!this.services.facebook.obFBContWidth.firstChild && !!this.services.facebook.obFBContWidth.firstChild.fitrstChild)
		{
			obFrame = this.services.facebook.obFBContWidth.firstChild.fitrstChild;
			if (!!obFrame)
			{
				src = obFrame.getAttribute("src");
				newSrc = src.replace(/width=(\d+)/ig, "width="+width);
				BX.adjust(this.services.facebook.obFBContWidth, { attrs: { 'data-width': (width-20) } });
				this.services.facebook.currentWidth = width;
				BX.style(this.services.facebook.obFBContWidth.firstChild, 'width', width+'px');
				BX.adjust(obFrame, { attrs : { src: newSrc }, style: { width: width+'px' } });
			}
		}
	}
};

window.JCCatalogSocnetsComments.prototype.onResize = function()
{
	if (this.serviceList.facebook)
	{
		this.setFBWidth(this.getFBParentWidth());
	}
};
})(window);
/* End */
;
; /* Start:"a:4:{s:4:"full";s:90:"/bitrix/components/bitrix/catalog.store.amount/templates/.default/script.js?14338395811401";s:6:"source";s:75:"/bitrix/components/bitrix/catalog.store.amount/templates/.default/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
window.JCCatalogStoreSKU = function(params)
{
	var i;

	if(!params)
		return;

	this.config = {
		'id' : params.ID,
		'showEmptyStore'	: params.SHOW_EMPTY_STORE,
		'useMinAmount'		: params.USE_MIN_AMOUNT,
		'minAmount'			: params.MIN_AMOUNT
	};

	this.messages = params.MESSAGES;
	this.sku = params.SKU;
	this.stores = params.STORES;
	this.obStores = {};
	for (i in this.stores)
		this.obStores[this.stores[i]] = BX(this.config.id+"_"+this.stores[i]);

	BX.addCustomEvent(window, "onCatalogStoreProductChange", BX.proxy(this.offerOnChange, this));
};

window.JCCatalogStoreSKU.prototype.offerOnChange = function(id)
{
	var curSku = this.sku[id],
		k,
		message,
		parent;

	for (k in this.obStores)
	{
		message = (!!this.config.useMinAmount) ? this.getStringCount(0) : '0';
		BX.adjust(this.obStores[k], {html: message});
		if (!!curSku[k])
		{
			message = (!!this.config.useMinAmount) ? this.getStringCount(curSku[k]) : curSku[k];
			BX.adjust(this.obStores[k],  {html: message});
		}
		parent = BX.findParent(this.obStores[k], {tagName: 'li'});
		if (!!this.config.showEmptyStore || curSku[k] > 0)
			BX.show(parent);
		else
			BX.hide(parent);
	}
};

window.JCCatalogStoreSKU.prototype.getStringCount = function(num)
{
	if (num == 0)
		return this.messages['ABSENT'];
	else if (num >= this.config.minAmount)
		return this.messages['LOT_OF_GOOD'];
	else
		return this.messages['NOT_MUCH_GOOD'];
};
/* End */
;
; /* Start:"a:4:{s:4:"full";s:99:"/bitrix/components/bitrix/catalog.bigdata.products/templates/.default/script.min.js?143393747323411";s:6:"source";s:79:"/bitrix/components/bitrix/catalog.bigdata.products/templates/.default/script.js";s:3:"min";s:83:"/bitrix/components/bitrix/catalog.bigdata.products/templates/.default/script.min.js";s:3:"map";s:83:"/bitrix/components/bitrix/catalog.bigdata.products/templates/.default/script.map.js";}"*/
(function(t){if(!!t.JCCatalogBigdataProducts){return}var e=function(t){e.superclass.constructor.apply(this,arguments);this.nameNode=BX.create("span",{props:{className:"bx_medium bx_bt_button",id:this.id},text:t.text});this.buttonNode=BX.create("span",{attrs:{className:t.ownerClass},style:{marginBottom:"0",borderBottom:"0 none transparent"},children:[this.nameNode],events:this.contextEvents});if(BX.browser.IsIE()){this.buttonNode.setAttribute("hideFocus","hidefocus")}};BX.extend(e,BX.PopupWindowButton);t.JCCatalogBigdataProducts=function(t){this.productType=0;this.showQuantity=true;this.showAbsent=true;this.secondPict=false;this.showOldPrice=false;this.showPercent=false;this.showSkuProps=false;this.visual={ID:"",PICT_ID:"",SECOND_PICT_ID:"",QUANTITY_ID:"",QUANTITY_UP_ID:"",QUANTITY_DOWN_ID:"",PRICE_ID:"",DSC_PERC:"",SECOND_DSC_PERC:"",DISPLAY_PROP_DIV:"",BASKET_PROP_DIV:""};this.product={checkQuantity:false,maxQuantity:0,stepQuantity:1,isDblQuantity:false,canBuy:true,canSubscription:true,name:"",pict:{},id:0,addUrl:"",buyUrl:""};this.basketData={useProps:false,emptyProps:false,quantity:"quantity",props:"prop",basketUrl:""};this.defaultPict={pict:null,secondPict:null};this.checkQuantity=false;this.maxQuantity=0;this.stepQuantity=1;this.isDblQuantity=false;this.canBuy=true;this.canSubscription=true;this.precision=6;this.precisionFactor=Math.pow(10,this.precision);this.offers=[];this.offerNum=0;this.treeProps=[];this.obTreeRows=[];this.showCount=[];this.showStart=[];this.selectedValues={};this.obProduct=null;this.obQuantity=null;this.obQuantityUp=null;this.obQuantityDown=null;this.obPict=null;this.obSecondPict=null;this.obPrice=null;this.obTree=null;this.obBuyBtn=null;this.obDscPerc=null;this.obSecondDscPerc=null;this.obSkuProps=null;this.obMeasure=null;this.obPopupWin=null;this.basketUrl="";this.basketParams={};this.treeRowShowSize=5;this.treeEnableArrow={display:"",cursor:"pointer",opacity:1};this.treeDisableArrow={display:"",cursor:"default",opacity:.2};this.lastElement=false;this.containerHeight=0;this.errorCode=0;if("object"===typeof t){this.productType=parseInt(t.PRODUCT_TYPE,10);this.showQuantity=t.SHOW_QUANTITY;this.showAbsent=t.SHOW_ABSENT;this.secondPict=!!t.SECOND_PICT;this.showOldPrice=!!t.SHOW_OLD_PRICE;this.showPercent=!!t.SHOW_DISCOUNT_PERCENT;this.showSkuProps=!!t.SHOW_SKU_PROPS;this.visual=t.VISUAL;switch(this.productType){case 1:case 2:if(!!t.PRODUCT&&"object"===typeof t.PRODUCT){if(this.showQuantity){this.product.checkQuantity=t.PRODUCT.CHECK_QUANTITY;this.product.isDblQuantity=t.PRODUCT.QUANTITY_FLOAT;if(this.product.checkQuantity){this.product.maxQuantity=this.product.isDblQuantity?parseFloat(t.PRODUCT.MAX_QUANTITY):parseInt(t.PRODUCT.MAX_QUANTITY,10)}this.product.stepQuantity=this.product.isDblQuantity?parseFloat(t.PRODUCT.STEP_QUANTITY):parseInt(t.PRODUCT.STEP_QUANTITY,10);this.checkQuantity=this.product.checkQuantity;this.isDblQuantity=this.product.isDblQuantity;this.maxQuantity=this.product.maxQuantity;this.stepQuantity=this.product.stepQuantity;if(this.isDblQuantity){this.stepQuantity=Math.round(this.stepQuantity*this.precisionFactor)/this.precisionFactor}}this.product.canBuy=t.PRODUCT.CAN_BUY;this.product.canSubscription=t.PRODUCT.SUBSCRIPTION;this.canBuy=this.product.canBuy;this.canSubscription=this.product.canSubscription;this.product.name=t.PRODUCT.NAME;this.product.pict=t.PRODUCT.PICT;this.product.id=t.PRODUCT.ID;if(!!t.PRODUCT.ADD_URL){this.product.addUrl=t.PRODUCT.ADD_URL}if(!!t.PRODUCT.BUY_URL){this.product.buyUrl=t.PRODUCT.BUY_URL}if(!!t.BASKET&&"object"===typeof t.BASKET){this.basketData.useProps=!!t.BASKET.ADD_PROPS;this.basketData.emptyProps=!!t.BASKET.EMPTY_PROPS}}else{this.errorCode=-1}break;case 3:if(!!t.OFFERS&&BX.type.isArray(t.OFFERS)){if(!!t.PRODUCT&&"object"===typeof t.PRODUCT){this.product.name=t.PRODUCT.NAME;this.product.id=t.PRODUCT.ID}this.offers=t.OFFERS;this.offerNum=0;if(!!t.OFFER_SELECTED){this.offerNum=parseInt(t.OFFER_SELECTED,10)}if(isNaN(this.offerNum)){this.offerNum=0}if(!!t.TREE_PROPS){this.treeProps=t.TREE_PROPS}if(!!t.DEFAULT_PICTURE){this.defaultPict.pict=t.DEFAULT_PICTURE.PICTURE;this.defaultPict.secondPict=t.DEFAULT_PICTURE.PICTURE_SECOND}}else{this.errorCode=-1}break;default:this.errorCode=-1}if(!!t.BASKET&&"object"===typeof t.BASKET){if(!!t.BASKET.QUANTITY){this.basketData.quantity=t.BASKET.QUANTITY}if(!!t.BASKET.PROPS){this.basketData.props=t.BASKET.PROPS}if(!!t.BASKET.BASKET_URL){this.basketData.basketUrl=t.BASKET.BASKET_URL}}this.lastElement=!!t.LAST_ELEMENT&&"Y"===t.LAST_ELEMENT}if(0===this.errorCode){BX.ready(BX.delegate(this.Init,this))}};t.JCCatalogBigdataProducts.prototype.Init=function(){var e=0,i="",s=null;this.obProduct=BX(this.visual.ID);if(!this.obProduct){this.errorCode=-1}this.obPict=BX(this.visual.PICT_ID);if(!this.obPict){this.errorCode=-2}if(this.secondPict&&!!this.visual.SECOND_PICT_ID){this.obSecondPict=BX(this.visual.SECOND_PICT_ID)}this.obPrice=BX(this.visual.PRICE_ID);if(!this.obPrice){this.errorCode=-16}if(this.showQuantity&&!!this.visual.QUANTITY_ID){this.obQuantity=BX(this.visual.QUANTITY_ID);if(!!this.visual.QUANTITY_UP_ID){this.obQuantityUp=BX(this.visual.QUANTITY_UP_ID)}if(!!this.visual.QUANTITY_DOWN_ID){this.obQuantityDown=BX(this.visual.QUANTITY_DOWN_ID)}}if(3===this.productType){if(!!this.visual.TREE_ID){this.obTree=BX(this.visual.TREE_ID);if(!this.obTree){this.errorCode=-256}i=this.visual.TREE_ITEM_ID;for(e=0;e<this.treeProps.length;e++){this.obTreeRows[e]={LEFT:BX(i+this.treeProps[e].ID+"_left"),RIGHT:BX(i+this.treeProps[e].ID+"_right"),LIST:BX(i+this.treeProps[e].ID+"_list"),CONT:BX(i+this.treeProps[e].ID+"_cont")};if(!this.obTreeRows[e].LEFT||!this.obTreeRows[e].RIGHT||!this.obTreeRows[e].LIST||!this.obTreeRows[e].CONT){this.errorCode=-512;break}}}if(!!this.visual.QUANTITY_MEASURE){this.obMeasure=BX(this.visual.QUANTITY_MEASURE)}}if(!!this.visual.BUY_ID){this.obBuyBtn=BX(this.visual.BUY_ID)}if(this.showPercent){if(!!this.visual.DSC_PERC){this.obDscPerc=BX(this.visual.DSC_PERC)}if(this.secondPict&&!!this.visual.SECOND_DSC_PERC){this.obSecondDscPerc=BX(this.visual.SECOND_DSC_PERC)}}if(this.showSkuProps){if(!!this.visual.DISPLAY_PROP_DIV){this.obSkuProps=BX(this.visual.DISPLAY_PROP_DIV)}}if(0===this.errorCode){if(this.showQuantity){if(!!this.obQuantityUp){BX.bind(this.obQuantityUp,"click",BX.delegate(this.QuantityUp,this))}if(!!this.obQuantityDown){BX.bind(this.obQuantityDown,"click",BX.delegate(this.QuantityDown,this))}if(!!this.obQuantity){BX.bind(this.obQuantity,"change",BX.delegate(this.QuantityChange,this))}}switch(this.productType){case 1:break;case 3:s=BX.findChildren(this.obTree,{tagName:"li"},true);if(!!s&&0<s.length){for(e=0;e<s.length;e++){BX.bind(s[e],"click",BX.delegate(this.SelectOfferProp,this))}}for(e=0;e<this.obTreeRows.length;e++){BX.bind(this.obTreeRows[e].LEFT,"click",BX.delegate(this.RowLeft,this));BX.bind(this.obTreeRows[e].RIGHT,"click",BX.delegate(this.RowRight,this))}this.SetCurrent();break}if(!!this.obBuyBtn){BX.bind(this.obBuyBtn,"click",BX.delegate(this.Basket,this))}if(this.lastElement){this.containerHeight=parseInt(this.obProduct.parentNode.offsetHeight,10);if(isNaN(this.containerHeight)){this.containerHeight=0}this.setHeight();BX.bind(t,"resize",BX.delegate(this.checkHeight,this));BX.bind(this.obProduct.parentNode,"mouseover",BX.delegate(this.setHeight,this));BX.bind(this.obProduct.parentNode,"mouseout",BX.delegate(this.clearHeight,this))}}};t.JCCatalogBigdataProducts.prototype.checkHeight=function(){this.containerHeight=parseInt(this.obProduct.parentNode.offsetHeight,10);if(isNaN(this.containerHeight)){this.containerHeight=0}};t.JCCatalogBigdataProducts.prototype.setHeight=function(){if(0<this.containerHeight){BX.adjust(this.obProduct.parentNode,{style:{height:this.containerHeight+"px"}})}};t.JCCatalogBigdataProducts.prototype.clearHeight=function(){BX.adjust(this.obProduct.parentNode,{style:{height:"auto"}})};t.JCCatalogBigdataProducts.prototype.QuantityUp=function(){var t=0,e=true;if(0===this.errorCode&&this.showQuantity&&this.canBuy){t=this.isDblQuantity?parseFloat(this.obQuantity.value):parseInt(this.obQuantity.value,10);if(!isNaN(t)){t+=this.stepQuantity;if(this.checkQuantity){if(t>this.maxQuantity){e=false}}if(e){if(this.isDblQuantity){t=Math.round(t*this.precisionFactor)/this.precisionFactor}this.obQuantity.value=t}}}};t.JCCatalogBigdataProducts.prototype.QuantityDown=function(){var t=0,e=true;if(0===this.errorCode&&this.showQuantity&&this.canBuy){t=this.isDblQuantity?parseFloat(this.obQuantity.value):parseInt(this.obQuantity.value,10);if(!isNaN(t)){t-=this.stepQuantity;if(t<this.stepQuantity){e=false}if(e){if(this.isDblQuantity){t=Math.round(t*this.precisionFactor)/this.precisionFactor}this.obQuantity.value=t}}}};t.JCCatalogBigdataProducts.prototype.QuantityChange=function(){var t=0,e=true;if(0===this.errorCode&&this.showQuantity){if(this.canBuy){t=this.isDblQuantity?parseFloat(this.obQuantity.value):parseInt(this.obQuantity.value,10);if(!isNaN(t)){if(this.checkQuantity){if(t>this.maxQuantity){e=false;t=this.maxQuantity}else if(t<this.stepQuantity){e=false;t=this.stepQuantity}}if(!e){this.obQuantity.value=t}}else{this.obQuantity.value=this.stepQuantity}}else{this.obQuantity.value=this.stepQuantity}}};t.JCCatalogBigdataProducts.prototype.QuantitySet=function(t){if(0===this.errorCode){this.canBuy=this.offers[t].CAN_BUY;if(this.canBuy){BX.addClass(this.obBuyBtn,"bx_bt_button");BX.removeClass(this.obBuyBtn,"bx_bt_button_type_2");this.obBuyBtn.innerHTML=BX.message("CBD_MESS_BTN_BUY")}else{BX.addClass(this.obBuyBtn,"bx_bt_button_type_2");BX.removeClass(this.obBuyBtn,"bx_bt_button");this.obBuyBtn.innerHTML=BX.message("CBD_MESS_NOT_AVAILABLE")}if(this.showQuantity){this.isDblQuantity=this.offers[t].QUANTITY_FLOAT;this.checkQuantity=this.offers[t].CHECK_QUANTITY;if(this.isDblQuantity){this.maxQuantity=parseFloat(this.offers[t].MAX_QUANTITY);this.stepQuantity=Math.round(parseFloat(this.offers[t].STEP_QUANTITY)*this.precisionFactor)/this.precisionFactor}else{this.maxQuantity=parseInt(this.offers[t].MAX_QUANTITY,10);this.stepQuantity=parseInt(this.offers[t].STEP_QUANTITY,10)}this.obQuantity.value=this.stepQuantity;this.obQuantity.disabled=!this.canBuy;if(!!this.obMeasure){if(!!this.offers[t].MEASURE){BX.adjust(this.obMeasure,{html:this.offers[t].MEASURE})}else{BX.adjust(this.obMeasure,{html:""})}}}}};t.JCCatalogBigdataProducts.prototype.SelectOfferProp=function(){var t=0,e="",i="",s=[],a=null,o=BX.proxy_context;if(!!o&&o.hasAttribute("data-treevalue")){i=o.getAttribute("data-treevalue");s=i.split("_");if(this.SearchOfferPropIndex(s[0],s[1])){a=BX.findChildren(o.parentNode,{tagName:"li"},false);if(!!a&&0<a.length){for(t=0;t<a.length;t++){e=a[t].getAttribute("data-onevalue");if(e===s[1]){BX.addClass(a[t],"bx_active")}else{BX.removeClass(a[t],"bx_active")}}}}}};t.JCCatalogBigdataProducts.prototype.SearchOfferPropIndex=function(t,e){var i="",s=false,a,o,r=[],h=-1,n={},u=[];for(a=0;a<this.treeProps.length;a++){if(this.treeProps[a].ID===t){h=a;break}}if(-1<h){for(a=0;a<h;a++){i="PROP_"+this.treeProps[a].ID;n[i]=this.selectedValues[i]}i="PROP_"+this.treeProps[h].ID;s=this.GetRowValues(n,i);if(!s){return false}if(!BX.util.in_array(e,s)){return false}n[i]=e;for(a=h+1;a<this.treeProps.length;a++){i="PROP_"+this.treeProps[a].ID;s=this.GetRowValues(n,i);if(!s){return false}if(this.showAbsent){r=[];u=[];u=BX.clone(n,true);for(o=0;o<s.length;o++){u[i]=s[o];if(this.GetCanBuy(u)){r[r.length]=s[o]}}}else{r=s}if(!!this.selectedValues[i]&&BX.util.in_array(this.selectedValues[i],r)){n[i]=this.selectedValues[i]}else{n[i]=r[0]}this.UpdateRow(a,n[i],s,r)}this.selectedValues=n;this.ChangeInfo()}return true};t.JCCatalogBigdataProducts.prototype.RowLeft=function(){var t=0,e="",i=-1,s=BX.proxy_context;if(!!s&&s.hasAttribute("data-treevalue")){e=s.getAttribute("data-treevalue");for(t=0;t<this.treeProps.length;t++){if(this.treeProps[t].ID===e){i=t;break}}if(-1<i&&this.treeRowShowSize<this.showCount[i]){if(0>this.showStart[i]){this.showStart[i]++;BX.adjust(this.obTreeRows[i].LIST,{style:{marginLeft:this.showStart[i]*20+"%"}});BX.adjust(this.obTreeRows[i].RIGHT,{style:this.treeEnableArrow})}if(0<=this.showStart[i]){BX.adjust(this.obTreeRows[i].LEFT,{style:this.treeDisableArrow})}}}};t.JCCatalogBigdataProducts.prototype.RowRight=function(){var t=0,e="",i=-1,s=BX.proxy_context;if(!!s&&s.hasAttribute("data-treevalue")){e=s.getAttribute("data-treevalue");for(t=0;t<this.treeProps.length;t++){if(this.treeProps[t].ID===e){i=t;break}}if(-1<i&&this.treeRowShowSize<this.showCount[i]){if(this.treeRowShowSize-this.showStart[i]<this.showCount[i]){this.showStart[i]--;BX.adjust(this.obTreeRows[i].LIST,{style:{marginLeft:this.showStart[i]*20+"%"}});BX.adjust(this.obTreeRows[i].LEFT,{style:this.treeEnableArrow})}if(this.treeRowShowSize-this.showStart[i]>=this.showCount[i]){BX.adjust(this.obTreeRows[i].RIGHT,{style:this.treeDisableArrow})}}}};t.JCCatalogBigdataProducts.prototype.UpdateRow=function(t,e,i,s){var a=0,o=0,r="",h=0,n="",u={},l=false,c=false,f=false,p=0,d=this.treeEnableArrow,b=this.treeEnableArrow,P=0,B=null;if(-1<t&&t<this.obTreeRows.length){B=BX.findChildren(this.obTreeRows[t].LIST,{tagName:"li"},false);if(!!B&&0<B.length){l="PICT"===this.treeProps[t].SHOW_MODE;h=i.length;c=this.treeRowShowSize<h;n=c?100/h+"%":"20%";u={props:{className:""},style:{width:n}};if(l){u.style.paddingTop=n}for(a=0;a<B.length;a++){r=B[a].getAttribute("data-onevalue");f=r===e;if(BX.util.in_array(r,s)){u.props.className=f?"bx_active":""}else{u.props.className=f?"bx_active bx_missing":"bx_missing"}u.style.display="none";if(BX.util.in_array(r,i)){u.style.display="";if(f){p=o}o++}BX.adjust(B[a],u)}u={style:{width:(c?20*h:100)+"%",marginLeft:"0%"}};if(l){BX.adjust(this.obTreeRows[t].CONT,{props:{className:c?"bx_item_detail_scu full":"bx_item_detail_scu"}})}else{BX.adjust(this.obTreeRows[t].CONT,{props:{className:c?"bx_item_detail_size full":"bx_item_detail_size"}})}if(c){if(p+1===h){b=this.treeDisableArrow}if(this.treeRowShowSize<=p){P=this.treeRowShowSize-p-1;u.style.marginLeft=P*20+"%"}if(0===P){d=this.treeDisableArrow}BX.adjust(this.obTreeRows[t].LEFT,{style:d});BX.adjust(this.obTreeRows[t].RIGHT,{style:b})}else{BX.adjust(this.obTreeRows[t].LEFT,{style:{display:"none"}});BX.adjust(this.obTreeRows[t].RIGHT,{style:{display:"none"}})}BX.adjust(this.obTreeRows[t].LIST,u);this.showCount[t]=h;this.showStart[t]=P}}};t.JCCatalogBigdataProducts.prototype.GetRowValues=function(t,e){var i=0,s,a=[],o=false,r=true;if(0===t.length){for(i=0;i<this.offers.length;i++){if(!BX.util.in_array(this.offers[i].TREE[e],a)){a[a.length]=this.offers[i].TREE[e]}}o=true}else{for(i=0;i<this.offers.length;i++){r=true;for(s in t){if(t[s]!==this.offers[i].TREE[s]){r=false;break}}if(r){if(!BX.util.in_array(this.offers[i].TREE[e],a)){a[a.length]=this.offers[i].TREE[e]}o=true}}}return o?a:false};t.JCCatalogBigdataProducts.prototype.GetCanBuy=function(t){var e=0,i,s=false,a=true;for(e=0;e<this.offers.length;e++){a=true;for(i in t){if(t[i]!==this.offers[e].TREE[i]){a=false;break}}if(a){if(this.offers[e].CAN_BUY){s=true;break}}}return s};t.JCCatalogBigdataProducts.prototype.SetCurrent=function(){var t=0,e=0,i=[],s="",a=false,o={},r=[],h=this.offers[this.offerNum].TREE;for(t=0;t<this.treeProps.length;t++){s="PROP_"+this.treeProps[t].ID;a=this.GetRowValues(o,s);if(!a){break}if(BX.util.in_array(h[s],a)){o[s]=h[s]}else{o[s]=a[0];this.offerNum=0}if(this.showAbsent){i=[];r=[];r=BX.clone(o,true);for(e=0;e<a.length;e++){r[s]=a[e];if(this.GetCanBuy(r)){i[i.length]=a[e]}}}else{i=a}this.UpdateRow(t,o[s],a,i)}this.selectedValues=o;this.ChangeInfo()};t.JCCatalogBigdataProducts.prototype.ChangeInfo=function(){var t=0,e,i=-1,s={},a=true,o="";for(t=0;t<this.offers.length;t++){a=true;for(e in this.selectedValues){if(this.selectedValues[e]!==this.offers[t].TREE[e]){a=false;break}}if(a){i=t;break}}if(-1<i){if(!!this.obPict){if(!!this.offers[i].PREVIEW_PICTURE){BX.adjust(this.obPict,{style:{backgroundImage:"url("+this.offers[i].PREVIEW_PICTURE.SRC+")"}})}else{BX.adjust(this.obPict,{style:{backgroundImage:"url("+this.defaultPict.pict.SRC+")"}})}}if(this.secondPict&&!!this.obSecondPict){if(!!this.offers[i].PREVIEW_PICTURE_SECOND){BX.adjust(this.obSecondPict,{style:{backgroundImage:"url("+this.offers[i].PREVIEW_PICTURE_SECOND.SRC+")"}})}else if(!!this.offers[i].PREVIEW_PICTURE.SRC){BX.adjust(this.obSecondPict,{style:{backgroundImage:"url("+this.offers[i].PREVIEW_PICTURE.SRC+")"}})}else if(!!this.defaultPict.secondPict){BX.adjust(this.obSecondPict,{style:{backgroundImage:"url("+this.defaultPict.secondPict.SRC+")"}})}else{BX.adjust(this.obSecondPict,{style:{backgroundImage:"url("+this.defaultPict.pict.SRC+")"}})}}if(this.showSkuProps&&!!this.obSkuProps){if(0===this.offers[i].DISPLAY_PROPERTIES.length){BX.adjust(this.obSkuProps,{style:{display:"none"},html:""})}else{BX.adjust(this.obSkuProps,{style:{display:""},html:this.offers[i].DISPLAY_PROPERTIES})}}if(!!this.obPrice){o=this.offers[i].PRICE.PRINT_DISCOUNT_VALUE;if(this.showOldPrice&&this.offers[i].PRICE.DISCOUNT_VALUE!==this.offers[i].PRICE.VALUE){o+=" <span>"+this.offers[i].PRICE.PRINT_VALUE+"</span>"}BX.adjust(this.obPrice,{html:o});if(this.showPercent){if(this.offers[i].PRICE.DISCOUNT_VALUE!==this.offers[i].PRICE.VALUE){s={style:{display:""},html:this.offers[i].PRICE.DISCOUNT_DIFF_PERCENT}}else{s={style:{display:"none"},html:""}}if(!!this.obDscPerc){BX.adjust(this.obDscPerc,s)}if(!!this.obSecondDscPerc){BX.adjust(this.obSecondDscPerc,s)}}}this.offerNum=i;this.QuantitySet(this.offerNum)}};t.JCCatalogBigdataProducts.prototype.InitBasketUrl=function(){switch(this.productType){case 1:case 2:this.basketUrl=this.product.addUrl;break;case 3:this.basketUrl=this.offers[this.offerNum].ADD_URL;break}this.basketParams={ajax_basket:"Y",rcm:"yes"};if(this.showQuantity){this.basketParams[this.basketData.quantity]=this.obQuantity.value}};t.JCCatalogBigdataProducts.prototype.FillBasketProps=function(){if(!this.visual.BASKET_PROP_DIV){return}var t=0,e=null,i=false,s=null;if(this.basketData.useProps&&!this.basketData.emptyProps){if(!!this.obPopupWin&&!!this.obPopupWin.contentContainer){s=this.obPopupWin.contentContainer}}else{s=BX(this.visual.BASKET_PROP_DIV)}if(!s){return}e=s.getElementsByTagName("select");if(!!e&&!!e.length){for(t=0;t<e.length;t++){if(!e[t].disabled){switch(e[t].type.toLowerCase()){case"select-one":this.basketParams[e[t].name]=e[t].value;i=true;break;default:break}}}}e=s.getElementsByTagName("input");if(!!e&&!!e.length){for(t=0;t<e.length;t++){if(!e[t].disabled){switch(e[t].type.toLowerCase()){case"hidden":this.basketParams[e[t].name]=e[t].value;i=true;break;case"radio":if(e[t].checked){this.basketParams[e[t].name]=e[t].value;i=true}break;default:break}}}}if(!i){this.basketParams[this.basketData.props]=[];this.basketParams[this.basketData.props][0]=0}};t.JCCatalogBigdataProducts.prototype.SendToBasket=function(){if(!this.canBuy){return}this.InitBasketUrl();this.FillBasketProps();if(this.product&&this.product.id){this.RememberRecommendation(this.obProduct,this.product.id)}BX.ajax({method:"POST",dataType:"json",url:this.basketUrl,data:this.basketParams,onsuccess:BX.delegate(this.BasketResult,this)})};t.JCCatalogBigdataProducts.prototype.RememberRecommendation=function(t,e){var i=BX.findParent(t,{className:"bigdata_recommended_products_items"});var s=BX.findChild(i,{attr:{name:"bigdata_recommendation_id"}},true).value;var a=BX.cookie_prefix+"_RCM_PRODUCT_LOG";var o=getCookie(a);var r=false;var h=[],n;if(o){h=o.split(".")}var u=h.length;while(u--){n=h[u].split("-");if(n[0]==e){n=h[u].split("-");n[1]=s;n[2]=BX.current_server_time;h[u]=n.join("-");r=true}else{if(BX.current_server_time-n[2]>3600*24*30){h.splice(u,1)}}}if(!r){h.push([e,s,BX.current_server_time].join("-"))}var l=h.join(".");var c=new Date((new Date).getTime()+1e3*3600*24*365*10);document.cookie=a+"="+l+"; path=/; expires="+c.toUTCString()+"; domain="+BX.cookie_domain};t.JCCatalogBigdataProducts.prototype.Basket=function(){var t="";if(!this.canBuy){return}switch(this.productType){case 1:case 2:if(this.basketData.useProps&&!this.basketData.emptyProps){this.InitPopupWindow();this.obPopupWin.setTitleBar({content:BX.create("div",{style:{marginRight:"30px",whiteSpace:"nowrap"},text:BX.message("CBD_TITLE_BASKET_PROPS")})});if(BX(this.visual.BASKET_PROP_DIV)){t=BX(this.visual.BASKET_PROP_DIV).innerHTML}this.obPopupWin.setContent(t);this.obPopupWin.setButtons([new e({ownerClass:this.obProduct.parentNode.parentNode.parentNode.className,text:BX.message("CBD_BTN_MESSAGE_SEND_PROPS"),events:{click:BX.delegate(this.SendToBasket,this)}})]);this.obPopupWin.show()}else{this.SendToBasket()}break;case 3:this.SendToBasket();break}};t.JCCatalogBigdataProducts.prototype.BasketResult=function(t){var i="",s="",a="",o=true,r=[];if(!!this.obPopupWin){this.obPopupWin.close()}if("object"!==typeof t){return false}o="OK"===t.STATUS;if(o){BX.onCustomEvent("OnBasketChange");s=this.product.name;switch(this.productType){case 1:case 2:a=this.product.pict.SRC;break;case 3:a=!!this.offers[this.offerNum].PREVIEW_PICTURE?this.offers[this.offerNum].PREVIEW_PICTURE.SRC:this.defaultPict.pict.SRC;break}i='<div style="width: 96%; margin: 10px 2%; text-align: center;"><img src="'+a+'" height="130"><p>'+s+"</p></div>";r=[new e({ownerClass:this.obProduct.parentNode.parentNode.parentNode.className,text:BX.message("CBD_BTN_MESSAGE_BASKET_REDIRECT"),events:{click:BX.delegate(function(){location.href=!!this.basketData.basketUrl?this.basketData.basketUrl:BX.message("BASKET_URL")},this)}})]}else{i=!!t.MESSAGE?t.MESSAGE:BX.message("CBD_BASKET_UNKNOWN_ERROR");r=[new e({ownerClass:this.obProduct.parentNode.parentNode.parentNode.className,text:BX.message("CBD_BTN_MESSAGE_CLOSE"),events:{click:BX.delegate(this.obPopupWin.close,this.obPopupWin)}})]}this.InitPopupWindow();this.obPopupWin.setTitleBar({content:BX.create("div",{style:{marginRight:"30px",whiteSpace:"nowrap"},text:o?BX.message("CBD_TITLE_SUCCESSFUL"):BX.message("CBD_TITLE_ERROR")})});this.obPopupWin.setContent(i);this.obPopupWin.setButtons(r);this.obPopupWin.show()};t.JCCatalogBigdataProducts.prototype.InitPopupWindow=function(){if(!!this.obPopupWin){return}this.obPopupWin=BX.PopupWindowManager.create("CatalogSectionBasket_"+this.visual.ID,null,{autoHide:false,offsetLeft:0,offsetTop:0,overlay:true,closeByEsc:true,titleBar:true,closeIcon:{top:"10px",right:"10px"}})}})(window);function getCookie(t){var e=document.cookie.match(new RegExp("(?:^|; )"+t.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g,"\\$1")+"=([^;]*)"));return e?decodeURIComponent(e[1]):undefined}function bx_rcm_recommendation_event_attaching(t){var e=BX.findChildren(t,{className:"bx_rcm_view_link"},true);if(e){for(i in e){BX.bind(e[i],"click",function(t){window.JCCatalogBigdataProducts.prototype.RememberRecommendation(BX(this),BX(this).getAttribute("data-product-id"))})}}}function bx_rcm_get_from_cloud(t,e,i){var s="https://analytics.bitrix.info/crecoms/v1_0/recoms.php";var a=BX.ajax.prepareData(e);if(a){s+=(s.indexOf("?")!==-1?"&":"?")+a;a=""}var o=function(e){if(!e.items){e.items=[]}BX.ajax({url:"/bitrix/components/bitrix/catalog.bigdata.products/ajax.php?"+BX.ajax.prepareData({AJAX_ITEMS:e.items,RID:e.id}),method:"POST",data:i,dataType:"html",processData:false,start:true,onsuccess:function(e){var i=BX.processHTML(e);BX(t).innerHTML=i.HTML;BX.ajax.processScripts(i.SCRIPT)}})};BX.ajax({method:"GET",dataType:"json",url:s,timeout:3,onsuccess:o,onfailure:o})}
/* End */
;
; /* Start:"a:4:{s:4:"full";s:99:"/bitrix/components/bitrix/catalog.recommended.products/templates/.default/script.js?143383958131730";s:6:"source";s:83:"/bitrix/components/bitrix/catalog.recommended.products/templates/.default/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function (window) {

if (!!window.JCCatalogSectionRec)
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

window.JCCatalogSectionRec = function (arParams)
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

window.JCCatalogSectionRec.prototype.Init = function()
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

window.JCCatalogSectionRec.prototype.checkHeight = function()
{
	this.containerHeight = parseInt(this.obProduct.parentNode.offsetHeight, 10);
	if (isNaN(this.containerHeight))
	{
		this.containerHeight = 0;
	}
};

window.JCCatalogSectionRec.prototype.setHeight = function()
{
	if (0 < this.containerHeight)
	{
		BX.adjust(this.obProduct.parentNode, {style: { height: this.containerHeight+'px'}});
	}
};

window.JCCatalogSectionRec.prototype.clearHeight = function()
{
	BX.adjust(this.obProduct.parentNode, {style: { height: 'auto'}});
};

window.JCCatalogSectionRec.prototype.QuantityUp = function()
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

window.JCCatalogSectionRec.prototype.QuantityDown = function()
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

window.JCCatalogSectionRec.prototype.QuantityChange = function()
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

window.JCCatalogSectionRec.prototype.QuantitySet = function(index)
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

window.JCCatalogSectionRec.prototype.SelectOfferProp = function()
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

window.JCCatalogSectionRec.prototype.SearchOfferPropIndex = function(strPropID, strPropValue)
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

window.JCCatalogSectionRec.prototype.RowLeft = function()
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

window.JCCatalogSectionRec.prototype.RowRight = function()
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

window.JCCatalogSectionRec.prototype.UpdateRow = function(intNumber, activeID, showID, canBuyID)
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

window.JCCatalogSectionRec.prototype.GetRowValues = function(arFilter, index)
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

window.JCCatalogSectionRec.prototype.GetCanBuy = function(arFilter)
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

window.JCCatalogSectionRec.prototype.SetCurrent = function()
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

window.JCCatalogSectionRec.prototype.ChangeInfo = function()
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

window.JCCatalogSectionRec.prototype.InitBasketUrl = function()
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

window.JCCatalogSectionRec.prototype.FillBasketProps = function()
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

window.JCCatalogSectionRec.prototype.SendToBasket = function()
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

window.JCCatalogSectionRec.prototype.Basket = function()
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

window.JCCatalogSectionRec.prototype.BasketResult = function(arResult)
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

window.JCCatalogSectionRec.prototype.InitPopupWindow = function()
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
; /* Start:"a:4:{s:4:"full";s:96:"/bitrix/components/bitrix/sale.recommended.products/templates/.default/script.js?143383955831755";s:6:"source";s:80:"/bitrix/components/bitrix/sale.recommended.products/templates/.default/script.js";s:3:"min";s:0:"";s:3:"map";s:0:"";}"*/
(function (window) {

if (!!window.JCCatalogSectionSRec)
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

window.JCCatalogSectionSRec = function (arParams)
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

window.JCCatalogSectionSRec.prototype.Init = function()
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

window.JCCatalogSectionSRec.prototype.checkHeight = function()
{
	this.containerHeight = parseInt(this.obProduct.parentNode.offsetHeight, 10);
	if (isNaN(this.containerHeight))
	{
		this.containerHeight = 0;
	}
};

window.JCCatalogSectionSRec.prototype.setHeight = function()
{
	if (0 < this.containerHeight)
	{
		BX.adjust(this.obProduct.parentNode, {style: { height: this.containerHeight+'px'}});
	}
};

window.JCCatalogSectionSRec.prototype.clearHeight = function()
{
	BX.adjust(this.obProduct.parentNode, {style: { height: 'auto'}});
};

window.JCCatalogSectionSRec.prototype.QuantityUp = function()
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

window.JCCatalogSectionSRec.prototype.QuantityDown = function()
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

window.JCCatalogSectionSRec.prototype.QuantityChange = function()
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

window.JCCatalogSectionSRec.prototype.QuantitySet = function(index)
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

window.JCCatalogSectionSRec.prototype.SelectOfferProp = function()
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

window.JCCatalogSectionSRec.prototype.SearchOfferPropIndex = function(strPropID, strPropValue)
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

window.JCCatalogSectionSRec.prototype.RowLeft = function()
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

window.JCCatalogSectionSRec.prototype.RowRight = function()
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

window.JCCatalogSectionSRec.prototype.UpdateRow = function(intNumber, activeID, showID, canBuyID)
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

window.JCCatalogSectionSRec.prototype.GetRowValues = function(arFilter, index)
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

window.JCCatalogSectionSRec.prototype.GetCanBuy = function(arFilter)
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

window.JCCatalogSectionSRec.prototype.SetCurrent = function()
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

window.JCCatalogSectionSRec.prototype.ChangeInfo = function()
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

window.JCCatalogSectionSRec.prototype.InitBasketUrl = function()
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

window.JCCatalogSectionSRec.prototype.FillBasketProps = function()
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

window.JCCatalogSectionSRec.prototype.SendToBasket = function()
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

window.JCCatalogSectionSRec.prototype.Basket = function()
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

window.JCCatalogSectionSRec.prototype.BasketResult = function(arResult)
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

window.JCCatalogSectionSRec.prototype.InitPopupWindow = function()
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
;; /* /bitrix/templates/.default/components/bitrix/catalog/template1_copy/bitrix/catalog.element/.default/script.js?143383960471670*/
; /* /bitrix/components/bitrix/iblock.vote/templates/stars/script.js?14338395603018*/
; /* /bitrix/components/bitrix/catalog.brandblock/templates/.default/script.js?14338395571822*/
; /* /bitrix/components/bitrix/catalog.comments/templates/.default/script.js?14338395655409*/
; /* /bitrix/components/bitrix/catalog.store.amount/templates/.default/script.js?14338395811401*/
; /* /bitrix/components/bitrix/catalog.bigdata.products/templates/.default/script.min.js?143393747323411*/
; /* /bitrix/components/bitrix/catalog.recommended.products/templates/.default/script.js?143383958131730*/
; /* /bitrix/components/bitrix/sale.recommended.products/templates/.default/script.js?143383955831755*/

//# sourceMappingURL=page_e0239390b878b8d3691b6da9ea271a49.map.js