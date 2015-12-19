var EList = {
	Init: function()
	{	
		$('.kda-ie-tbl input[type=checkbox][name^="SETTINGS[CHECK_ALL]"]').bind('change', function(){
			var inputs = $(this).closest('tbody').find('input[type=checkbox]').not(this);
			if(this.checked)
			{
				inputs.attr('checked', true);
			}
			else
			{
				inputs.attr('checked', false);
			}
		});
		
		$('.kda-ie-tbl select[name^="SETTINGS[FIELDS_LIST]"]').bind('change', function(){
			EList.OnChangeFieldHandler(this);
		}).trigger('change');
		
		$('.kda-ie-tbl input[type=checkbox][name^="SETTINGS[LIST_ACTIVE]"]').bind('change', function(){
			var tr = $(this).closest('.kda-ie-tbl').find('tr.settings');
			if(this.checked)
			{
				tr.show();
			}
			else
			{
				tr.hide();
			}
			$(window).trigger('resize');
		});
		
		$('.kda-ie-tbl:not(.empty) tr.heading .showlist:eq(0)').trigger('click');
		
		
		$('.kda-ie-tbl:not(.empty) div.set').bind('scroll', function(){
			$(this).prev('.set_scroll').scrollLeft($(this).scrollLeft());
		});
		$('.kda-ie-tbl:not(.empty) div.set_scroll').bind('scroll', function(){
			$(this).next('.set').scrollLeft($(this).scrollLeft());
		});
		$(window).bind('resize', function(){
			EList.SetWidthList();
		});
		$(window).trigger('resize');
	},
	
	SetWidthList: function()
	{
		$('.kda-ie-tbl:not(.empty) div.set').each(function(){
			var div = $(this);
			div.css('width', 0);
			div.prev('.set_scroll').css('width', 0);
			var timer = setInterval(function(){
				var width = div.parent().width();
				if(width > 0)
				{
					div.css('width', width);
					div.prev('.set_scroll').css('width', width).find('>div').css('width', div.find('>table.list').width());
					clearInterval(timer);
					//$('select[name^="SETTINGS[FIELDS_LIST]"]', div).chosen();
				}
			}, 100);
			setTimeout(function(){clearInterval(timer);}, 3000);
		});
	},
	
	ToggleSettings: function(btn)
	{
		var tr = $(btn).closest('.kda-ie-tbl').find('tr.settings');
		if(tr.is(':visible'))
		{
			tr.hide();
			$(btn).removeClass('open');
		}
		else
		{
			tr.show();
			$(btn).addClass('open');
		}
		$(window).trigger('resize');		
	},

	ShowFull: function(btn)
	{
		var tbl = $(btn).closest('.kda-ie-tbl');
		var list = tbl.attr('data-list-index');
		var post = $(btn).closest('form').serialize() + '&ACTION=SHOW_FULL_LIST&LIST_NUMBER=' + list;
		$.post(window.location.href, post, function(data){
			data = $(data);
			var chb = $('input[type=checkbox][name^="SETTINGS[CHECK_ALL]"]', tbl);
			if(chb.length > 0)
			{
				if(chb[0].checked)
				{
					data.find('input[type=checkbox]').attr('checked', true);
				}
				else
				{
					data.find('input[type=checkbox]').attr('checked', false);
				}
			}
			$('table.list', tbl).append(data);
			$('table.list input[type=checkbox]', tbl).each(function(){
				BX.adminFormTools.modifyCheckbox(this);
			});
			$(window).trigger('resize');
		});
		$(btn).hide();
	},
	
	ApplyToAllLists: function(link)
	{
		var tbl = $(link).closest('.kda-ie-tbl');
		var tbls = tbl.parent().find('.kda-ie-tbl').not(tbl);
		var ts = tbl.find('select');
		for(var i=0; i<tbls.length; i++)
		{
			var tss = $('select', tbls[i]);
			for(var j=0; j<tss.length; j++)
			{
				if(ts[j])
				{
					$('option', tss[j]).remove();
					$('option', ts[j]).clone().appendTo($(tss[j]));
					tss[j].selectedIndex = ts[j].selectedIndex;
				}
			}
		}
	},
	
	ChooseIblock: function(select)
	{
		var form = $(select).closest('form')[0];
		var post = {
			'MODE': 'AJAX',
			'ACTION': 'GET_SECTION_LIST',
			'IBLOCK_ID': select.value,
			'PROFILE_ID': form.PROFILE_ID.value
		}
		$.post(window.location.href, post, function(data){
			var sections = $(data).find('select[name=sections]');
			var fields = $(data).find('select[name=fields]');
			var sectSelect = $(select).closest('table').find('select[name="'+select.name.replace('[IBLOCK_ID]', '[SECTION_ID]')+'"]');
			sections.attr('name', sectSelect.attr('name'));
			sectSelect.replaceWith(sections);
			
			var ptable = $(select).closest('.kda-ie-tbl');
			ptable.find('select[name^="SETTINGS[FIELDS_LIST]"]').each(function(){
				var fields = $(data).find('select[name=fields]');
				fields.attr('name', this.name);
				fields.val(this.value);
				$(this).replaceWith(fields);
				fields.bind('change', function(){
					EList.OnChangeFieldHandler(this);
				});
				fields.trigger('change');
			});
			
			ptable.find('table.list tbody, table.list tfoot').show();
			ptable.attr('data-iblock-id', select.value);
		});
	},
	
	OnChangeFieldHandler: function(select)
	{
		var val = select.value;
		var link = $(select).next('a.field_settings');
		/*if(val.indexOf("ICAT_PRICE")===0 || val=="ICAT_PURCHASING_PRICE")
		{
			link.removeClass('inactive');
		}
		else
		{
			link.addClass('inactive');
		}*/
	},
	
	ShowFieldSettings: function(btn)
	{
		//if($(btn).hasClass('inactive')) return;
		var select = $(btn).prev('select');
		var val = select.val();
		var name = select[0].name;
		var ptable = $(btn).closest('.kda-ie-tbl');
		var form = $(btn).closest('form')[0];
		
		var dialog = new BX.CAdminDialog({
			'content_url':'/bitrix/admin/kda_import_excel_field_settings.php?field='+val+'&field_name='+name+'&IBLOCK_ID='+ptable.attr('data-iblock-id')+'&PROFILE_ID='+form.PROFILE_ID.value,
			'width':'800',
			'height':'350',
			'resizable':true});
			
		dialog.SetButtons([
			dialog.btnCancel,
			new BX.CWindowButton(
			{
				title: BX.message('JS_CORE_WINDOW_SAVE'),
				id: 'savebtn',
				name: 'savebtn',
				className: BX.browser.IsIE() && BX.browser.IsDoctype() && !BX.browser.IsIE10() ? '' : 'adm-btn-save',
				action: function () {
					this.disableUntilError();
					this.parentWindow.PostParameters();
					//this.parentWindow.Close();
				}
			})/*,
			dialog.btnSave*/
		]);
			
		BX.addCustomEvent(dialog, 'onWindowRegister', function(){
			$('input[type=checkbox]', this.DIV).each(function(){
				BX.adminFormTools.modifyCheckbox(this);
			});
		});
			
		dialog.Show();
	}
}

var EProfile = {
	Init: function()
	{
		var select = $('select#PROFILE_ID');
		if(select.length > 0)
		{
			select = select[0]
			/*this.Choose(select[0]);*/
			if(select.value=='new')
			{
				$('#new_profile_name').css('display', '');
			}
			else
			{
				$('#new_profile_name').css('display', 'none');
			}
		
			$('select.adm-detail-iblock-list').bind('change', function(){
				$.post(window.location.href, {'MODE': 'AJAX', 'IBLOCK_ID': this.value, 'ACTION': 'GET_UID'}, function(data){
					var fields = $(data).find('select[name="fields[]"]');
					var select = $('select[name="SETTINGS_DEFAULT[ELEMENT_UID][]"]');
					fields.val(select.val());
					fields.attr('name', select.attr('name'));
					//$('select.chosen').chosen('destroy');
					select.replaceWith(fields);
					//$('select.chosen').chosen();
					
					var fields2 = $(data).find('select[name="fields_sku[]"]');
					var select2 = $('select[name="SETTINGS_DEFAULT[ELEMENT_UID_SKU][]"]');
					if(select2.val()) fields2.val(select2.val());
					fields2.attr('name', select2.attr('name'));
					select2.replaceWith(fields2);
					if(fields2[0].options.length > 0) $('#element_uid_sku').show();
					else $('#element_uid_sku').hide();
				});
			});
			
			var select = $('select[name="SETTINGS_DEFAULT[ELEMENT_UID][]"]');
			if(!select.val()) select[0].options[0].selected = true;
			/*$('select.chosen').chosen();*/
		}
	},
	
	Choose: function(select)
	{
		/*if(select.value=='new')
		{
			$('#new_profile_name').css('display', '');
		}
		else
		{
			$('#new_profile_name').css('display', 'none');
		}*/
		var id = (typeof select == 'object' ? select.value : select);
		var query = window.location.search.replace(/PROFILE_ID=[^&]*&?/, '');
		if(query.length < 2) query = '?';
		if(query.length > 1 && query.substr(query.length-1)!='&') query += '&';
		query += 'PROFILE_ID=' + id;
		window.location.href = query;
	},
	
	Delete: function()
	{
		var obj = this;
		var select = $('select#PROFILE_ID');
		var option = select[0].options[select[0].selectedIndex];
		var id = option.value;
		$.post(window.location.href, {'MODE': 'AJAX', 'ID': id, 'ACTION': 'DELETE_PROFILE'}, function(data){
			obj.Choose('');
		});
	},
	
	ShowRename: function()
	{
		var select = $('select#PROFILE_ID');
		var option = select[0].options[select[0].selectedIndex];
		var name = option.innerHTML;
		
		var tr = $('#new_profile_name');
		var input = $('input[type=text]', tr);
		input.val(name);
		input.after('&nbsp;<input type="button" onclick="EProfile.Rename();" value="OK">');
		tr.css('display', '');
	},
	
	Rename: function()
	{
		var select = $('select#PROFILE_ID');
		var option = select[0].options[select[0].selectedIndex];
		var id = option.value;
		
		var tr = $('#new_profile_name');
		var input = $('input[type=text]', tr);
		var value = $.trim(input.val());
		if(value.length==0) return false;
		
		tr.css('display', 'none');
		option.innerHTML = value;
		
		$.post(window.location.href, {'MODE': 'AJAX', 'ID': id, 'NAME': value, 'ACTION': 'RENAME_PROFILE'}, function(data){});
	},
	
	ShowCron: function()
	{
		var dialog = new BX.CAdminDialog({
			'content_url':'/bitrix/admin/kda_import_excel_cron_settings.php',
			'width':'800',
			'height':'350',
			'resizable':true});
			
		dialog.SetButtons([
			dialog.btnCancel/*,
			new BX.CWindowButton(
			{
				title: BX.message('JS_CORE_WINDOW_SAVE'),
				id: 'savebtn',
				name: 'savebtn',
				className: BX.browser.IsIE() && BX.browser.IsDoctype() && !BX.browser.IsIE10() ? '' : 'adm-btn-save',
				action: function () {
					this.disableUntilError();
					this.parentWindow.PostParameters();
					//this.parentWindow.Close();
				}
			})*/
		]);
			
		dialog.Show();
	},
	
	SaveCron: function(btn)
	{
		var form = $(btn).closest('form');
		$.post(form[0].getAttribute('action'), form.serialize()+'&subaction='+btn.name, function(data){
			if(data) alert(data);
		});
	}	
}

var EImport = {
	params: {},

	Init: function(post)
	{
		BX.scrollToNode($('#resblock .adm-info-message')[0]);
		this.wait = BX.showWait();
		this.post = post;
		this.SendData();
		this.pid = post.PROFILE_ID;
		this.idleCounter = 0;
		this.errorStatus = false;
		var obj = this;
		setTimeout(function(){obj.SetTimeout();}, 3000);
	},
	
	SetTimeout: function()
	{
		if($('#progressbar').hasClass('end')) return;
		var obj = this;
		this.timer = setTimeout(function(){obj.GetStatus();}, 1000);
	},
	
	GetStatus: function()
	{
		var obj = this;
		$.ajax({
			type: "GET",
			url: '/upload/tmp/kda.importexcel/'+this.pid+'.txt?hash='+(new Date()).getTime(),
			success: function(data){
				if(data && data.indexOf('{')==0)
				{
					eval('var result = '+data+';');
				}
				else
				{
					var result = false;
				}
				
				if(result.action!='finish')
				{
					obj.UpdateStatus(result);
					obj.SetTimeout();
				}
				else
				{
					obj.UpdateStatus(result, true);
				}
			},
			error: function(){
				obj.SetTimeout();
			},
			timeout: 5000
		});
	},
	
	UpdateStatus: function(result, end)
	{
		if($('#progressbar').hasClass('end')) return;
		if(end && this.timer) clearTimeout(this.timer);
		
		if(typeof result == 'object')
		{
			if(end && (parseInt(result.total_read_line) < parseInt(result.total_file_line)))
			{
				result.total_read_line = result.total_file_line;
			}
			
			$('#total_line').html(result.total_line);
			$('#correct_line').html(result.correct_line);
			$('#error_line').html(result.error_line);
			$('#killed_line').html(result.killed_line);
			
			var span = $('#progressbar .presult span');
			//span.html(span.attr('data-prefix')+': '+result.total_read_line+'/'+result.total_file_line);
			span.css('visibility', 'hidden');
			if(end)
			{
				$('#progressbar .presult').removeClass('load');
			}
			var percent = Math.round((result.total_read_line / result.total_file_line) * 100);
			if(percent >= 100)
			{
				if(end) percent = 100;
				else percent = 99;
			}
			$('#progressbar .presult b').html(percent+'%');
			$('#progressbar .pline').css('width', percent+'%');
			
			if(this.tmpparams && this.tmpparams.total_read_line==result.total_read_line)
			{
				this.idleCounter++;
			}
			else
			{
				this.idleCounter = 0;
			}
			this.tmpparams = result;
		}
		
		if(this.idleCounter > 10 && this.errorStatus)
		{
			var obj = this;
			for(var i in obj.tmpparams)
			{
				obj.params[i] = obj.tmpparams[i];
			}
			obj.SendData();
		}
	},
	
	SendData: function()
	{
		var post = this.post;
		post.ACTION = 'DO_IMPORT';
		post.stepparams = this.params;
		var obj = this;
		
		$.ajax({
			type: "POST",
			url: window.location.href,
			data: post,
			success: function(data){
				obj.errorStatus = false;
				obj.OnLoad(data);
			},
			error: function(){
				obj.errorStatus = true;
			}
		});
	},
	
	OnLoad: function(data)
	{
		eval('var result = '+data+';');
		if(typeof result == 'object')
		{		
			if(typeof result.errors == 'object' && result.errors.length > 0)
			{
				$('#block_error').show();
				for(var i=0; i<result.errors.length; i++)
				{
					$('#res_error').append('<div>'+result.errors[i]+'</div>');
				}
			}
			
			if(result.action=='continue')
			{
				this.UpdateStatus(result.params);
				this.params = result.params;
				this.SendData();
				return true;
			}
		}
		else
		{
			this.SendData();
			return true;
		}

		this.UpdateStatus(result.params, true);
		BX.closeWait(null, this.wait);
		/*$('#res_continue').hide();
		$('#res_finish').show();*/
		
		if(result.params.redirect_url && result.params.redirect_url.length > 0)
		{
			$('#redirect_message').html($('#redirect_message').html() + result.params.redirect_url);
			$('#redirect_message').show();
			setTimeout(function(){window.location.href = result.params.redirect_url}, 3000);
		}
		return false;
	}
}

var ESettings = {
	AddValue: function(link)
	{
		var input = $(link).prev('div').find('input[type=text]');
		var name = (input.length > 0 ? input[0].name : '');
		$(link).before('<div><input type="text" name="'+name+'" value=""></div>');
	},
	
	AddMargin: function(link)
	{
		var div = $(link).closest('td').find('.kda-ie-settings-margin:eq(0)');
		if(!div.is(':visible'))
		{
			div.show();
		}
		else
		{
			var div2 = div.clone(true);
			$('select, input', div2).val('');
			$(link).before(div2);
		}
	},
	
	RemoveMargin: function(link)
	{
		var divs = $(link).closest('td').find('.kda-ie-settings-margin');
		if(divs.length > 1)
		{
			$(link).closest('.kda-ie-settings-margin').remove();
		}
		else
		{
			$('select, input', divs).val('');
			divs.hide();
		}
	},
	
	ShowMarginTemplateBlock: function(link)
	{
		var div = $('#margin_templates');
		div.toggle();
	},
	
	ShowMarginTemplateBlockLoad: function(link, action)
	{
		var div = $('#margin_templates_load');
		if(action == 'hide') div.hide();
		else div.toggle();
	},
	
	SaveMarginTemplate: function(input, message)
	{
		var div = $(input).closest('div');
		var tid = $('select[name=MARGIN_TEMPLATE_ID]', div).val();
		var tname = $('input[name=MARGIN_TEMPLATE_NAME]', div).val();
		if(tid.length==0 && tname.length==0) return false;
		
		var wm = BX.WindowManager.Get();
		var url = wm.PARAMS.content_url;
		var params = wm.GetParameters().replace(/(^|&)action=[^&]*($|&)/, '&').replace(/^&+/, '').replace(/&+$/, '')
		params += '&action=save_margin_template&template_id='+tid+'&template_name='+tname;
		$.post(url, params, function(data){
			var jData = $(data);
			$('#margin_templates').replaceWith(jData.find('#margin_templates'));
			$('#margin_templates_load').replaceWith(jData.find('#margin_templates_load'));
			alert(message);
		});
		
		return false;
	},
	
	LoadMarginTemplate: function(input)
	{
		var div = $(input).closest('div');
		var tid = $('select[name=MARGIN_TEMPLATE_ID]', div).val();
		if(tid.length==0) return false;
		
		var wm = BX.WindowManager.Get();
		var url = wm.PARAMS.content_url;
		var params = wm.GetParameters().replace(/(^|&)action=[^&]*($|&)/, '&').replace(/^&+/, '').replace(/&+$/, '')
		params += '&action=load_margin_template&template_id='+tid;
		var obj = this;
		$.post(url, params, function(data){
			var jData = $(data);
			$('#settings_margins').replaceWith(jData.find('#settings_margins'));
			obj.ShowMarginTemplateBlockLoad('hide');
		});
		
		return false;
	},
	
	RemoveMarginTemplate: function(input, message)
	{
		var div = $(input).closest('div');
		var tid = $('select[name=MARGIN_TEMPLATE_ID]', div).val();
		if(tid.length==0) return false;
		
		var wm = BX.WindowManager.Get();
		var url = wm.PARAMS.content_url;
		var params = wm.GetParameters().replace(/(^|&)action=[^&]*($|&)/, '&').replace(/^&+/, '').replace(/&+$/, '')
		params += '&action=delete_margin_template&template_id='+tid;
		$.post(url, params, function(data){
			var jData = $(data);
			$('#margin_templates').replaceWith(jData.find('#margin_templates'));
			$('#margin_templates_load').replaceWith(jData.find('#margin_templates_load'));
			alert(message);
		});
		
		return false;
	},
}

$(document).ready(function(){
	if($('#preview_file').length > 0)
	{
		var post = $('#preview_file').closest('form').serialize() + '&ACTION=SHOW_REVIEW_LIST';
		$.post(window.location.href, post, function(data){
			$('#preview_file').html(data);
			if($('.kda-ie-tbl:not([data-init])').length > 0)
			{
				EList.Init();
				$('.kda-ie-tbl').attr('data-init', 1);
			}
		});
	}

	EProfile.Init();
});