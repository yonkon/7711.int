
; /* Start:/bitrix/components/bitrix/sale.order.ajax/templates/.default/script.js*/
BX.saleOrderAjax = {

	BXCallAllowed: false,

	options: {},
	indexCache: {},
	controls: {},

	modes: {},
	properties: {},

	// called once, on component load
	init: function(options)
	{
		var ctx = this;
		this.options = options;

		window.submitFormProxy = BX.proxy(function(){
			ctx.submitFormProxy.apply(ctx, arguments);
		}, this);

		BX(function(){
			ctx.initDeferredControl();
		});
		BX(function(){
			ctx.BXCallAllowed = true; // unlock form refresher
		});

		this.controls.scope = BX('order_form_div');

		// user presses "add location" when he cannot find location in popup mode
		BX.bindDelegate(this.controls.scope, 'click', {className: '-bx-popup-set-mode-add-loc'}, function(){

			var input = BX.create('input', {
				attrs: {
					type: 'hidden',
					name: 'PERMANENT_MODE_STEPS',
					value: '1'
				}
			});

			BX.prepend(input, BX('ORDER_FORM'));

			ctx.BXCallAllowed = false;
			submitForm();
		});
	},

	cleanUp: function(){

		for(var k in this.properties){
			if(typeof this.properties[k].input != 'undefined'){
				BX.unbindAll(this.properties[k].input);
				this.properties[k].input = null;
			}

			if(typeof this.properties[k].control != 'undefined'){
				BX.unbindAll(this.properties[k].control);
			}
		}

		this.properties = {};
	},

	addPropertyDesc: function(desc){
		this.properties[desc.id] = desc.attributes;
		this.properties[desc.id].id = desc.id;
	},

	// called each time form refreshes
	initDeferredControl: function()
	{
		var ctx = this;

		// first, init all controls
		if(typeof window.BX.locationsDeferred != 'undefined'){

			this.BXCallAllowed = false;

			for(var k in window.BX.locationsDeferred){

				window.BX.locationsDeferred[k].call(this);
				window.BX.locationsDeferred[k] = null;
				delete(window.BX.locationsDeferred[k]);

				this.properties[k].control = window.BX.locationSelectors[k];
				delete(window.BX.locationSelectors[k]);
			}
		}

		for(var k in this.properties){

			// zip input handling
			if(this.properties[k].isZip){
				var row = this.controls.scope.querySelector('[data-property-id-row="'+k+'"]');
				if(BX.type.isElementNode(row)){

					var input = row.querySelector('input[type="text"]');
					if(BX.type.isElementNode(input)){
						this.properties[k].input = input;

						// set value for the first "location" property met
						var locPropId = false;
						for(var m in this.properties){
							if(this.properties[m].type == 'LOCATION'){
								locPropId = m;
								break;
							}
						}

						if(locPropId !== false){
							BX.bindDebouncedChange(input, function(value){

								input = null;
								row = null;

								if(/^\s*\d{6}\s*$/.test(value)){

									ctx.getLocationByZip(value, function(locationId){
										ctx.properties[locPropId].control.setValueById(locationId);
									}, function(){
										try{
											ctx.properties[locPropId].control.clearSelected(locationId);
										}catch(e){}
									});
								}
							});
						}
					}
				}
			}

			if(this.checkAbility(k, 'canHaveAltLocation')){

				//this.checkMode(k, 'altLocationChoosen');

				var control = this.properties[k].control;

				// control can have "select other location" option
				control.setOption('pseudoValues', ['other']);

				// when control tries to search for items
				control.bindEvent('before-control-item-discover-done', function(knownItems, adapter){

					control = null;

					var parentValue = adapter.getParentValue();

					// you can choose "other" location only if parentNode is not root and is selectable
					if(parentValue == this.getOption('rootNodeValue') || !this.checkCanSelectItem(parentValue))
						return;

					knownItems.unshift({DISPLAY: ctx.options.messages.otherLocation, VALUE: 'other', CODE: 'other', IS_PARENT: false});
				});

				// currently wont work for initially created controls, so commented out
				/*
				// when control is being created with knownItems
				control.bindEvent('before-control-placed', function(adapter){
					if(typeof adapter.opts.knownItems != 'undefined')
						adapter.opts.knownItems.unshift({DISPLAY: so.messages.otherLocation, VALUE: 'other', CODE: 'other', IS_PARENT: false});

				});
				*/

				// add special value "other", if there is "city" input
				if(this.checkMode(k, 'altLocationChoosen')){
					
					var altLocProp = this.getAltLocPropByRealLocProp(k);
					this.toggleProperty(altLocProp.id, true);

					var adapter = control.getAdapterAtPosition(control.getStackSize() - 1);

					// also restore "other location" label on the last control
					if(typeof adapter != 'undefined' && adapter !== null)
						adapter.setValuePair('other', ctx.options.messages.otherLocation); // a little hack
				}else{

					var altLocProp = this.getAltLocPropByRealLocProp(k);
					this.toggleProperty(altLocProp.id, false);

				}
			}else{

				var altLocProp = this.getAltLocPropByRealLocProp(k);
				if(altLocProp !== false){

					// replace default boring "nothing found" label for popup with "-bx-popup-set-mode-add-loc" inside
					if(this.properties[k].type == 'LOCATION' && typeof this.properties[k].control != 'undefined' && this.properties[k].control.getSysCode() == 'sls')
						this.properties[k].control.replaceTemplate('nothing-found', this.options.messages.notFoundPrompt);

					this.toggleProperty(altLocProp.id, false);
				}
			}

			if(typeof this.properties[k].control != 'undefined' && this.properties[k].control.getSysCode() == 'slst'){

				var control = this.properties[k].control;

				// if a children of CITY is shown, we must replace label for 'not selected' variant
				var adapter = control.getAdapterAtPosition(control.getStackSize() - 1);
				var node = this.getPreviousAdapterSelectedNode(control, adapter);

				if(node !== false && node.TYPE_ID == ctx.options.cityTypeId){

					var selectBox = adapter.getControl();
					if(selectBox.getValue() == false){

						adapter.getControl().replaceMessage('notSelected', ctx.options.messages.moreInfoLocation);
						adapter.setValuePair('', ctx.options.messages.moreInfoLocation);
					}
				}
			}

		}

		this.BXCallAllowed = true;
	},

	checkMode: function(propId, mode){

		//if(typeof this.modes[propId] == 'undefined')
		//	this.modes[propId] = {};

		//if(typeof this.modes[propId] != 'undefined' && this.modes[propId][mode])
		//	return true;

		if(mode == 'altLocationChoosen'){

			if(this.checkAbility(propId, 'canHaveAltLocation')){

				var input = this.getInputByPropId(this.properties[propId].altLocationPropId);
				var altPropId = this.properties[propId].altLocationPropId;

				if(input !== false && input.value.length > 0 && !input.disabled && this.properties[altPropId].valueSource != 'default'){

					//this.modes[propId][mode] = true;
					return true;
				}
			}
		}

		return false;
	},

	checkAbility: function(propId, ability){

		if(typeof this.properties[propId] == 'undefined')
			this.properties[propId] = {};

		if(typeof this.properties[propId].abilities == 'undefined')
			this.properties[propId].abilities = {};

		if(typeof this.properties[propId].abilities != 'undefined' && this.properties[propId].abilities[ability])
			return true;

		if(ability == 'canHaveAltLocation'){

			if(this.properties[propId].type == 'LOCATION'){

				// try to find corresponding alternate location prop
				if(typeof this.properties[propId].altLocationPropId != 'undefined' && typeof this.properties[this.properties[propId].altLocationPropId]){

					var altLocPropId = this.properties[propId].altLocationPropId;

					if(typeof this.properties[propId].control != 'undefined' && this.properties[propId].control.getSysCode() == 'slst'){

						if(this.getInputByPropId(altLocPropId) !== false){
							this.properties[propId].abilities[ability] = true;
							return true;
						}
					}
				}
			}

		}

		return false;
	},

	getInputByPropId: function(propId){
		if(typeof this.properties[propId].input != 'undefined')
			return this.properties[propId].input;

		var row = this.getRowByPropId(propId);
		if(BX.type.isElementNode(row)){
			var input = row.querySelector('input[type="text"]');
			if(BX.type.isElementNode(input)){
				this.properties[propId].input = input;
				return input;
			}
		}

		return false;
	},

	getRowByPropId: function(propId){

		if(typeof this.properties[propId].row != 'undefined')
			return this.properties[propId].row;

		var row = this.controls.scope.querySelector('[data-property-id-row="'+propId+'"]');
		if(BX.type.isElementNode(row)){
			this.properties[propId].row = row;
			return row;
		}

		return false;
	},

	getAltLocPropByRealLocProp: function(propId){
		if(typeof this.properties[propId].altLocationPropId != 'undefined')
			return this.properties[this.properties[propId].altLocationPropId];

		return false;
	},

	toggleProperty: function(propId, way, dontModifyRow){

		var prop = this.properties[propId];

		if(typeof prop.row == 'undefined')
			prop.row = this.getRowByPropId(propId);

		if(typeof prop.input == 'undefined')
			prop.input = this.getInputByPropId(propId);

		if(!way){
			if(!dontModifyRow)
				BX.hide(prop.row);
			prop.input.disabled = true;
		}else{
			if(!dontModifyRow)
				BX.show(prop.row);
			prop.input.disabled = false;
		}
	},

	submitFormProxy: function(item, control)
	{
		var propId = false;
		for(var k in this.properties){
			if(typeof this.properties[k].control != 'undefined' && this.properties[k].control == control){
				propId = k;
				break;
			}
		}

		if(item != 'other'){

			if(this.BXCallAllowed){

				// drop mode "other"
				if(propId != false){
					if(this.checkAbility(propId, 'canHaveAltLocation')){

						if(typeof this.modes[propId] == 'undefined')
							this.modes[propId] = {};

						this.modes[propId]['altLocationChoosen'] = false;

						var altLocProp = this.getAltLocPropByRealLocProp(propId);
						if(altLocProp !== false){

							this.toggleProperty(altLocProp.id, false);
						}
					}
				}

				this.BXCallAllowed = false;
				submitForm();
			}

		}else{ // only for sale.location.selector.steps

			if(this.checkAbility(propId, 'canHaveAltLocation')){

				var adapter = control.getAdapterAtPosition(control.getStackSize() - 2);
				if(adapter !== null){
					var value = adapter.getValue();
					control.setTargetInputValue(value);

					// set mode "other"
					if(typeof this.modes[propId] == 'undefined')
						this.modes[propId] = {};
						
					this.modes[propId]['altLocationChoosen'] = true;

					var altLocProp = this.getAltLocPropByRealLocProp(propId);
					if(altLocProp !== false){

						this.toggleProperty(altLocProp.id, true, true);
					}

					this.BXCallAllowed = false;
					submitForm();
				}
			}
		}
	},

	getPreviousAdapterSelectedNode: function(control, adapter){

		var index = adapter.getIndex();
		var prevAdapter = control.getAdapterAtPosition(index - 1);

		if(typeof prevAdapter !== 'undefined' && prevAdapter != null){
			var prevValue = prevAdapter.getControl().getValue();

			if(typeof prevValue != 'undefined'){
				var node = control.getNodeByValue(prevValue);

				if(typeof node != 'undefined')
					return node;

				return false;
			}
		}

		return false;
	},
	getLocationByZip: function(value, successCallback, notFoundCallback)
	{
		if(typeof this.indexCache[value] != 'undefined')
		{
			successCallback.apply(this, [this.indexCache[value]]);
			return;
		}

		ShowWaitWindow();

		var ctx = this;

		BX.ajax({

			url: this.options.source,
			method: 'post',
			dataType: 'json',
			async: true,
			processData: true,
			emulateOnload: true,
			start: true,
			data: {'ACT': 'GET_LOC_BY_ZIP', 'ZIP': value},
			//cache: true,
			onsuccess: function(result){

				//try{

				CloseWaitWindow();
				if(result.result){

					ctx.indexCache[value] = result.data.ID;

					successCallback.apply(ctx, [result.data.ID]);

				}else
					notFoundCallback.call(ctx);

				//}catch(e){console.dir(e);}

			},
			onfailure: function(type, e){

				CloseWaitWindow();
				// on error do nothing
			}

		});
	}

}
/* End */
;
; /* Start:/bitrix/components/bitrix/sale.location.selector.search/templates/.default/script.js*/
(function(){

	if(typeof BX.autoCompleteSLS == 'undefined'){

		BX.autoCompleteSLS = function(opts, nf){

			this.parentConstruct(BX.autoCompleteSLS, opts);

			BX.merge(this, {
				opts: {

					usePagingOnScroll: 		true,
					pageSize: 				10,
					//scrollThrottleTimeout: 	100,
					arrowScrollAdditional: 	2,
					pageUpWardOffset: 		3,

					bindEvents: {

						'after-input-value-modify': function(){

							this.ctrls.fullRoute.value = '';
							
						},
						'after-select-item': function(itemId){

							var so = this.opts;

							//this.setEditLink();

							var cItem = this.vars.cache.nodes[itemId];

							var path = cItem.DISPLAY;
							if(typeof cItem.PATH == 'object'){
								for(var i = 0; i < cItem.PATH.length; i++){
									path += ', '+this.vars.cache.path[cItem.PATH[i]];
								}
							}

							this.ctrls.inputs.fake.setAttribute('title', path);
							this.ctrls.fullRoute.value = path;

							if(typeof this.opts.callback == 'string' && this.opts.callback.length > 0 && this.opts.callback in window)
								window[this.opts.callback].apply(this, [itemId, this]);
						},
						'after-deselect-item': function(){
							this.ctrls.fullRoute.value = '';
							this.ctrls.inputs.fake.setAttribute('title', '');
						},
						'before-render-variant': function(itemData){

							if(itemData.PATH.length > 0){
								var path = '';
								for(var i = 0; i < itemData.PATH.length; i++)
									path += ', '+this.vars.cache.path[itemData.PATH[i]];

								itemData.PATH = path;
							}else
								itemData.PATH = '';
						}
					}
				},
				vars: {
					cache: {
						path: {}
					}
				},
				sys: {
					code: 'sls'
				}
			});
			
			this.handleInitStack(nf, BX.autoCompleteSLS, opts);
		}
		BX.extend(BX.autoCompleteSLS, BX.ui.autoComplete);
		BX.merge(BX.autoCompleteSLS.prototype, {

			// member of stack of initializers, must be defined even if do nothing
			init: function(){

				// process options
				if(typeof this.opts.pathNames == 'object')
					BX.merge(this.vars.cache.path, this.opts.pathNames);

				this.pushFuncStack('buildUpDOM', BX.autoCompleteSLS);
				this.pushFuncStack('bindEvents', BX.autoCompleteSLS);
			},

			buildUpDOM: function(){

				var sc = this.ctrls,
					so = this.opts,
					sv = this.vars,
					ctx = this,
					code = this.sys.code;
				
				// full route node
				sc.fullRoute = BX.create('input', {
					props: {
						className: 'bx-ui-'+code+'-route'
					},
					attrs: {
						type: 'text',
						disabled: 'disabled',
						autocomplete: 'off'
					}
				});

				// todo: use metrics instead!
				BX.style(sc.fullRoute, 'paddingTop', BX.style(sc.inputs.fake, 'paddingTop'));
				BX.style(sc.fullRoute, 'paddingLeft', BX.style(sc.inputs.fake, 'paddingLeft'));
				BX.style(sc.fullRoute, 'paddingRight', '0px');
				BX.style(sc.fullRoute, 'paddingBottom', '0px');

				BX.style(sc.fullRoute, 'marginTop', BX.style(sc.inputs.fake, 'marginTop'));
				BX.style(sc.fullRoute, 'marginLeft', BX.style(sc.inputs.fake, 'marginLeft'));
				BX.style(sc.fullRoute, 'marginRight', '0px');
				BX.style(sc.fullRoute, 'marginBottom', '0px');

				if(BX.style(sc.inputs.fake, 'borderTopStyle') != 'none'){
					BX.style(sc.fullRoute, 'borderTopStyle', 'solid');
					BX.style(sc.fullRoute, 'borderTopColor', 'transparent');
					BX.style(sc.fullRoute, 'borderTopWidth', BX.style(sc.inputs.fake, 'borderTopWidth'));
				}

				if(BX.style(sc.inputs.fake, 'borderLeftStyle') != 'none'){
					BX.style(sc.fullRoute, 'borderLeftStyle', 'solid');
					BX.style(sc.fullRoute, 'borderLeftColor', 'transparent');
					BX.style(sc.fullRoute, 'borderLeftWidth', BX.style(sc.inputs.fake, 'borderLeftWidth'));
				}

				BX.prepend(sc.fullRoute, sc.container);

				sc.inputBlock = this.getControl('input-block');
				sc.loader = this.getControl('loader');
			},

			bindEvents: function(){

				var ctx = this;

				// quick links
				BX.bindDelegate(this.getControl('quick-locations', true), 'click', {tag: 'a'}, function(){
					ctx.setValueByLocationId(BX.data(this, 'id'));
				});

				this.vars.outSideClickScope = this.ctrls.inputBlock;
			},

			refineRequest: function(request){

				var filter = {};
				var exact = 0;
				if(typeof request['QUERY'] != 'undefined')
					filter['QUERY'] = request.QUERY;

				if(typeof request['VALUE'] != 'undefined'){
					filter['QUERY'] = request.VALUE;
					exact = 1;
				}

				return {
					FILTER: BX.merge(filter, this.opts.query.FILTER),
					
					BEHAVIOUR: BX.merge({
						EXPECT_EXACT: exact
					}, this.opts.query.BEHAVIOUR),

					SHOW: {
						PATH: 1,
						TYPE_ID: 1
					}
				}
			},

			refineResponce: function(responce, request){

				if(typeof responce.ETC.PATH_NAMES != 'undefined')
					this.vars.cache.path = BX.merge(this.vars.cache.path, responce.ETC.PATH_NAMES);

				return this.refineItems(responce.ITEMS);
			},

			refineItems: function(items){
				for(var k in items){
					items[k].DISPLAY = items[k].NAME;
					items[k].VALUE = items[k].ID;
				}

				return items;
			},

			/*
			fillCache: function(items, key){

				items = this.refineItems(items);
				BX.autoCompleteSLS.superclass.fillCache.apply(this, [items, false]);
			},
			*/

			// custom value getter
			getSelectorValue: function(value){

				if(this.opts.provideLinkBy == 'id')
					return value;

				if(typeof this.vars.cache.nodes[value] != 'undefined')
					return this.vars.cache.nodes[value].CODE;
				else
					return '';
			},

			whenLoaderToggle: function(way){
				BX[way ? 'show' : 'hide'](this.ctrls.loader);
			},

			// location id is just a value in terms of autocomplete
			setValueByLocationId: function(id, autoSelect){
				this.setValue(id, autoSelect);
			},

			setValueByLocationCode: function(code, autoSelect){
				// not implemented
			}

		});
	}

})();
/* End */
;
; /* Start:/bitrix/components/bitrix/sale.ajax.delivery.calculator/templates/.default/proceed.js*/
function deliveryCalcProceed(arParams)
{
	var delivery_id = arParams.DELIVERY;
	var profile_id = arParams.PROFILE;
	var getExtraParamsFunc = arParams.EXTRA_PARAMS_CALLBACK;

	function __handlerDeliveryCalcProceed(data)
	{
		var obContainer = document.getElementById('delivery_info_' + delivery_id + '_' + profile_id);
		if (obContainer)
		{
			obContainer.innerHTML = data;
		}

		PCloseWaitMessage('wait_container_' + delivery_id + '_' + profile_id, true);
	}

	PShowWaitMessage('wait_container_' + delivery_id + '_' + profile_id, true);
	
	var url = '/bitrix/components/bitrix/sale.ajax.delivery.calculator/templates/.default/ajax.php';
	
	var TID = CPHttpRequest.InitThread();
	CPHttpRequest.SetAction(TID, __handlerDeliveryCalcProceed);

	if(!getExtraParamsFunc)
	{
		CPHttpRequest.Post(TID, url, arParams);
	}
	else
	{
		eval(getExtraParamsFunc);

		BX.addCustomEvent('onSaleDeliveryGetExtraParams', function(params){
			arParams.EXTRA_PARAMS = params;
			CPHttpRequest.Post(TID, url, arParams);
		});

	}
}
/* End */
;
; /* Start:/bitrix/components/bitrix/sale.location.selector.steps/templates/.default/script.js*/
(function(){

	if(typeof BX.chainedSelectorsSLS == 'undefined'){

		BX.chainedSelectorsSLS = function(opts, nf){

			this.parentConstruct(BX.chainedSelectorsSLS, opts);

			BX.merge(this, {
				opts: {
					bindEvents: {
						'after-select-item': function(value){

							if(typeof this.opts.callback == 'string' && this.opts.callback.length > 0 && this.opts.callback in window)
								window[this.opts.callback].apply(this, [value, this]);
						}
					},
					disableKeyboardInput: false,
					dontShowNextChoice: false
				},
				sys: {
					code: 'slst'
				}
			});
			
			this.handleInitStack(nf, BX.chainedSelectorsSLS, opts);
		};
		BX.extend(BX.chainedSelectorsSLS, BX.ui.chainedSelectors);
		BX.merge(BX.chainedSelectorsSLS.prototype, {

			// member of stack of initializers, must be defined even if does nothing
			init: function(){
				this.pushFuncStack('buildUpDOM', BX.chainedSelectorsSLS);
				this.pushFuncStack('bindEvents', BX.chainedSelectorsSLS);
			},

			// add additional controls
			buildUpDOM: function(){},

			bindEvents: function(){

				var ctx = this,
					so = this.opts;

				if(so.disableKeyboardInput){ //toggleDropDown
					this.bindEvent('after-control-placed', function(adapter){

						var control = adapter.getControl();

						BX.unbindAll(control.ctrls.toggle);
						// spike, bad idea to access fields directly
						BX.bind(control.ctrls.scope, 'click', function(e){
							control.toggleDropDown();
						});
					});
				}

				// quick links
				BX.bindDelegate(this.getControl('quick-locations', true), 'click', {tag: 'a'}, function(){
					ctx.setValueById(BX.data(this, 'id'));
				});
			},

			////////// PUBLIC: free to use outside

			setValueById: function(id){
				this.setValue(id);
			},

			setValueByCode: function(code){
				//todo
			},

			setTargetValue: function(value){
				this.setTargetInputValue(this.opts.provideLinkBy == 'code' ? this.vars.cache.nodes[value].CODE: value);

				//console.dir('FIRE!!!');
				//console.log((new Error).stack);

				this.fireEvent('after-select-item', [value]);
			},

			////////// PRIVATE: forbidden to use outside (for compatibility reasons)

			controlChangeActions: function(stackIndex, value){

				var ctx = this,
					so = this.opts,
					sv = this.vars,
					sc = this.ctrls;

				this.hideError();

				////////////////

				if(value.length == 0){

					ctx.truncateStack(stackIndex);
					ctx.setTargetValue(ctx.getLastValidValue());

				}else if(BX.util.in_array(value, so.pseudoValues)){

					ctx.truncateStack(stackIndex);
					this.fireEvent('after-select-item', [value]);

				}else{

					var node = sv.cache.nodes[value];

					if(typeof node == 'undefined')
						throw new Error('Selected node not found in the cache');

					// node found

					ctx.truncateStack(stackIndex);

					if(so.dontShowNextChoice){
						if(node.IS_UNCHOOSABLE)
							ctx.appendControl(value);
					}else{
						if(typeof sv.cache.links[value] != 'undefined' || node.IS_PARENT)
							ctx.appendControl(value);
					}

					if(ctx.checkCanSelectItem(value))
						ctx.setTargetValue(value);
				}
			},

			// adapter to ajax page request
			refineRequest: function(request){

				var newRequest = {};

				if(typeof request.PARENT_VALUE != 'undefined'){ // bundle for PARENT_VALUE will be downloaded

					newRequest = {
						FILTER: BX.merge({
							PARENT_ID: request.PARENT_VALUE
						}, this.opts.query.FILTER),
						
						BEHAVIOUR: BX.merge({
							EXPECT_EXACT: 0,
							PREFORMAT: 1
						}, this.opts.query.BEHAVIOUR),

						SHOW: {
							CHILD_EXISTENCE: 1
						}
					};

					// we are already inside linked sub-tree, no deeper check for SITE_ID needed
					if(typeof newRequest.FILTER.SITE_ID != 'undefined' && typeof this.vars.cache.nodes[request.PARENT_VALUE] != 'undefined' && !this.vars.cache.nodes[request.PARENT_VALUE].IS_UNCHOOSABLE)
						delete(newRequest.FILTER.SITE_ID);

				}else if(typeof request.VALUE != 'undefined') // route will be downloaded
					newRequest = {
						FILTER: BX.merge({
							QUERY: request.VALUE
						}, this.opts.query.FILTER),
						
						BEHAVIOUR: BX.merge({
							EXPECT_EXACT: 1,
							PREFORMAT: 1
						}, this.opts.query.BEHAVIOUR),

						SHOW: {
							PATH: 1,
							CHILD_EXISTENCE: 1 // do we need this here?
						}
					};

				return newRequest;
			},

			// adapter to ajax page responce
			refineResponce: function(responce, request){

				if(responce.length == 0)
					return responce;

				if(typeof request.PARENT_VALUE != 'undefined'){ // it was a bundle request

					var r = {};
					r[request.PARENT_VALUE] = responce['ITEMS'];
					responce = r;

				}else if(typeof request.VALUE != 'undefined'){ // it was a route request

					var levels = {};

					if(typeof responce.ITEMS[0]){

						var parentId = 0;
						for(var k = responce.ITEMS[0]['PATH'].length - 1; k >= 0; k--){
							var itemId = responce.ITEMS[0]['PATH'][k];

							var item = responce.ETC.PATH_ITEMS[itemId];
							item.IS_PARENT = true;

							levels[parentId] = [item];

							parentId = item.VALUE;
						}

						// add item itself
						levels[parentId] = [responce.ITEMS[0]];
					}

					responce = levels;
				}

				return responce;
			},

			showError: function(parameters){

				if(parameters.type != 'server-logic')
					parameters.errors = [this.opts.messages.error]; // generic error on js error

				this.setCSSState('error', this.ctrls.scope);
				this.ctrls.errorMessage.innerHTML = '<p><font class="errortext">'+BX.util.htmlspecialchars(parameters.errors.join(', '))+'</font></p>';
			}
		});
	}

})();
/* End */
;; /* /bitrix/components/bitrix/sale.order.ajax/templates/.default/script.js*/
; /* /bitrix/components/bitrix/sale.location.selector.search/templates/.default/script.js*/
; /* /bitrix/components/bitrix/sale.ajax.delivery.calculator/templates/.default/proceed.js*/
; /* /bitrix/components/bitrix/sale.location.selector.steps/templates/.default/script.js*/
