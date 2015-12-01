function xlsProfileSelectChange(select) {
	console.log(select.value);
	if (select.value == '0') {
		document.getElementById('xls_change_profile').disabled = true;
		document.getElementById('xls_profile_rename').disabled = true;
		document.getElementById('xls_delete_profile').disabled = true;

		document.getElementById('xls_profile_name').disabled = true;
		document.getElementsByName('submit_btn')[0].disabled = false;
		document.getElementsByName('skipAllSteps')[0].disabled = false;
	} else {
		if (select.value == 'new') {
			document.getElementById('xls_change_profile').disabled = true;
			document.getElementById('xls_profile_rename').disabled = true;
			document.getElementById('xls_profile_rename').style.display = 'none';
			document.getElementById('xls_profile_rename_link').style.display = 'none';
			document.getElementById('xls_delete_profile').disabled = true;

			document.getElementById('xls_profile_name').disabled = false;
			document.getElementById('xls_profile_name').focus();
			document.getElementsByName('submit_btn')[0].disabled = true;
			document.getElementsByName('skipAllSteps')[0].disabled = true;
		} else {
			document.getElementById('xls_change_profile').disabled = false;
			document.getElementById('xls_profile_rename').disabled = false;
			document.getElementById('xls_profile_rename').style.display = 'none';
			document.getElementById('xls_profile_rename_link').style.display = 'inline';
			document.getElementById('xls_delete_profile').disabled = false;

			document.getElementById('xls_profile_name').disabled = true;
			document.getElementsByName('submit_btn')[0].disabled = true;
			document.getElementsByName('skipAllSteps')[0].disabled = true;
		}
	}
	document.getElementById('xls_profile_name').value = '';
}

function xlsConfirmDeleteProfile(text) {
	return window.confirm(text);
}

function checkNameProfile(val) {
	if (val.length>0)
		document.getElementsByName('submit_btn')[0].disabled = false;
	else
		document.getElementsByName('submit_btn')[0].disabled = true;
}

if(document.getElementsByClassName) {
	getElementsByClass = function(classList, node) {
		return (node || document).getElementsByClassName(classList)
	}
} else {
	getElementsByClass = function(classList, node) {
		var node = node || document,
		list = node.getElementsByTagName('*'),
		length = list.length,
		classArray = classList.split(/\s+/),
		classes = classArray.length,
		result = [], i,j
		for(i = 0; i < length; i++) {
			for(j = 0; j < classes; j++)  {
				if(list[i].className.search('\\b' + classArray[j] + '\\b') != -1) {
					result.push(list[i])
					break
				}
			}
		}

		return result
	}
}

function LoadCheck(obj) {
	var chck = obj.checked;
	elements = getElementsByClass('XLS_SELECT_UPLOAD_PROPERTY');
	for (var i=0; i < elements.length; i++) {
		if (chck) {
			elements[i].checked='checked';
		} else {
			elements[i].checked=false;
		}
	};
}

function xls_SelectAllRows(select) {
	var inputs = select.parentNode.parentNode.parentNode.parentNode.getElementsByTagName('input');
	for (var i in inputs) {
		if (i == 0) continue;
		inputs[i].checked = select.checked;
	}
}

function xlsRenameProfile() {
	document.getElementById('xls_profile_name').disabled = false;
	document.getElementById('xls_profile_name').focus();
	document.getElementById('xls_profile_rename').style.display = 'inline';
}

$(function () {
	if ($('select[name=XLS_PROFILE_SELECT]').val()>0) {
		$('#xls_profile_rename_link').css('display', 'inline');
	}

	// загружать все сво-ва
	$('#property_check_all').click(function(event) {
		if($(this).attr('checked')=='checked') {
			$('.XLS_SELECT_UPLOAD_PROPERTY').attr('checked', 'checked');
		} else {
			$('.XLS_SELECT_UPLOAD_PROPERTY').removeAttr('checked');
		}
	});

	// снять галку "загружать все сво-ва", если отмечены не все сво-ва
	if ($('.XLS_SELECT_UPLOAD_PROPERTY[checked=checked]').length<$('.XLS_SELECT_UPLOAD_PROPERTY').length) {
		$('#property_check_all').removeAttr('checked');
	}

	$('.xls_table_property tbody input[type=checkbox]').change(function(event) {
		checkedProp=0;
		$('.xls_table_property tbody input[type=checkbox]').each(function(index, el) {
			if ($(this).attr('checked')=='checked') checkedProp++;
		});

		if (checkedProp==$('.xls_table_property tbody input[type=checkbox]').length) {
			$('#property_check_all').attr('checked', 'checked');
		} else {
			$('#property_check_all').removeAttr('checked');
		}
	});
});