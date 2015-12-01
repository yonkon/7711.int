<?
///////////////////////////////////////////////
//   Copyright (c) 2012-2014 PHP Solutions   //
//   Поддержка: support@phpsolutions.ru      //
///////////////////////////////////////////////

Class CPHPSolutionsBacktotop{
	function AddScriptBacktotop(){
		global $APPLICATION; 
		if(CModule::IncludeModule('phpsolutions.backtotop')){		
            if(!defined(ADMIN_SECTION) && ADMIN_SECTION!==true){		
				$phpsolutions_backtotop_exclude_url = COption::GetOptionString('phpsolutions.backtotop', 'phpsolutions_backtotop_exclude_url'.'_'.SITE_ID, "" );
				if( $phpsolutions_backtotop_exclude_url != '' ){
					$tmp_list = explode( "\n", $phpsolutions_backtotop_exclude_url ) ;
					foreach( $tmp_list as $v ){
						if( $path = parse_url( trim( $v ), PHP_URL_PATH ) ) $exclusion_list[] = $path ;
					}
					if( in_array( $_SERVER[ 'REQUEST_URI' ], $exclusion_list ) ) return ;
				}
				if( COption::GetOptionString( 'phpsolutions.backtotop', 'phpsolutions_backtotop_jquery'.'_'.SITE_ID, 'Y' ) == 'Y' ) CUtil::InitJSCore( Array( "jquery" ) ) ;
				$phpsolutions_backtotop_position = COption::GetOptionString('phpsolutions.backtotop', 'phpsolutions_backtotop_position'.'_'.SITE_ID, 'bottom-right');
				$phpsolutions_backtotop_position_indent_y = COption::GetOptionString( 'phpsolutions.backtotop', 'phpsolutions_backtotop_pos_y'.'_'.SITE_ID, '30') ;
				$phpsolutions_backtotop_position_indent_x = COption::GetOptionString( 'phpsolutions.backtotop', 'phpsolutions_backtotop_pos_x'.'_'.SITE_ID, '30' ) ;
				$phpsolutions_backtotop_skip = COption::GetOptionString( 'phpsolutions.backtotop', 'phpsolutions_backtotop_skip'.'_'.SITE_ID, '1000' ) ;
				$phpsolutions_backtotop_scroll_speed = COption::GetOptionString( 'phpsolutions.backtotop', 'phpsolutions_backtotop_scroll_speed'.'_'.SITE_ID, 'normal' ) ;
				$phpsolutions_backtotop_selected_image = COption::GetOptionString( "phpsolutions.backtotop", "phpsolutions_backtotop_selected_image".'_'.SITE_ID, "/bitrix/images/phpsolutions.backtotop/backtotop1.png" ) ;
				$size = getimagesize( $_SERVER['DOCUMENT_ROOT'].$phpsolutions_backtotop_selected_image ) ;
				$phpsolutions_backtotop_button_opacity = COption::GetOptionString( 'phpsolutions.backtotop', 'phpsolutions_backtotop_button_opacity'.'_'.SITE_ID, '80' ) ;	
				$APPLICATION->AddHeadString( "<script>
				phpsolutions_backtotop_button_opacity='".$phpsolutions_backtotop_button_opacity."';
				phpsolutions_backtotop_image_width='".$size[ 0 ]."';
				phpsolutions_backtotop_image_height='".$size[ 1 ]."';
				phpsolutions_backtotop_selected_image='".$phpsolutions_backtotop_selected_image."';
				phpsolutions_backtotop_position ='".$phpsolutions_backtotop_position."';
				phpsolutions_backtotop_skip = '".$phpsolutions_backtotop_skip."';
				phpsolutions_backtotop_scroll_speed = '".$phpsolutions_backtotop_scroll_speed."';
				phpsolutions_backtotop_position_indent_x = '".$phpsolutions_backtotop_position_indent_x."';
				phpsolutions_backtotop_position_indent_y = '".$phpsolutions_backtotop_position_indent_y."'
				</script>", true ) ;			
				$APPLICATION->AddHeadScript( "/bitrix/js/phpsolutions.backtotop/backtotop.js" ) ;			
				$APPLICATION->AddHeadString( "<link href='/bitrix/js/phpsolutions.backtotop/backtotop.css' type='text/css' rel='stylesheet' />", true ) ;
			}		
		}
	}	
}
?>