<?
require_once( $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/main/include/prolog_admin_before.php' );

\Bitrix\Main\Loader::includeModule( 'omg.import' );

IncludeModuleLangFile( __FILE__ );

$sTableID = 'tbl_import';

$oSort = new CAdminSorting( $sTableID, 'ID', 'asc' );
$lAdmin = new CAdminList( $sTableID, $oSort );

if( $arID = $lAdmin->GroupAction() ){
	if( $_REQUEST['action_target'] == 'selected' ){
		$arID = array();
		$rsImport = \OmgImportTable::getList();
		while( $arResult = $rsImport->fetch() )
			$arID[] = $arResult['ID'];
	}

	foreach( $arID as $ID ){
		if( strlen( $ID ) <= 0 )
			continue;

		switch( $_REQUEST['action'] ){
			case 'delete':
				@set_time_limit(0);

				$DB->StartTransaction();

				if( !\OmgImportTable::delete( $ID ) ){
					$DB->Rollback();

					if( $ex = $APPLICATION->GetException() )
						$lAdmin->AddGroupError( $ex->GetString(), $ID );
					else
						$lAdmin->AddGroupError( GetMessage( 'DELETE_ERROR' ), $ID );
				}

				$DB->Commit();

				break;
			case 'activate':
			case 'deactivate':
				$arFields = array(
					'ACTIVE' => $_REQUEST['action'] == 'activate' ? 'Y' : 'N'
				);

				if( !\OmgImportTable::update( $ID, $arFields ) ){
					if( $ex = $APPLICATION->GetException() )
						$lAdmin->AddGroupError( $ex->GetString(), $ID );
					else
						$lAdmin->AddGroupError( GetMessage( 'UPDATE_ERROR' ), $ID );
				}

				break;
		}
	}
}

$rsObj = \OmgImportTable::getList();

$lAdmin->AddHeaders(
	array(
		array( 'id' => 'ID', 'content' => GetMessage( 'ID' ), 'sort' => 'id', 'default' => true ),
		array( 'id' => 'NAME', 'content' => GetMessage( 'NAME' ), 'sort' => 'NAME', 'default' => true ),
		array( 'id' => 'FILE', 'content' => GetMessage( 'FILE' ), 'sort' => 'FILE', 'default' => true )
	)
);

while( $arObj = $rsObj->fetch() ){
	$row =& $lAdmin->AddRow( $arObj['ID'], $arObj, 'omg_import_view.php?ID='.$arObj['ID'], GetMessage( 'CHANGE' ) );

	$row->AddField( 'ID', '<a href="omg_import_view.php?ID='.$arObj['ID'].'">'.$arObj['ID'].'</a>' );
	$row->AddField( 'NAME', $arObj['NAME'] );
	$row->AddField( 'SORT', $arObj['SORT'] );

	$arActions = array();

	$arActions[] = array(
		'ICON' => 'edit',
		'TEXT' => GetMessage( 'CHANGE' ),
		'TITLE' => GetMessage( 'CHANGE_DETAIL' ),
		'ACTION' => $lAdmin->ActionRedirect( 'omg_import_view.php?ID='.$arObj['ID'] ),
		'DEFAULT' => true,
	);
	$arActions[] = array( 'SEPARATOR' => true );
	$arActions[] = array(
		'ICON' => 'delete',
		'TEXT' => GetMessage( 'DELETE' ),
		'TITLE' => GetMessage( 'DELETE_DETAIL' ),
		'ACTION' => 'if( confirm( \''.GetMessage( 'DELETE_DETAIL_QUESTION' ).'\' ) ) '.$lAdmin->ActionDoGroup( $arImport['ID'], 'delete' ),
	);

	$row->AddActions( $arActions );
}

$lAdmin->AddGroupActionTable(
	array(
		'delete' => GetMessage( 'DELETE' ),
		'activate' => GetMessage( 'ACTIVATE' ),
		'deactivate' => GetMessage( 'DEACTIVATE' ),
	)
);

$arDDMenu = array();

$arContext = array(
	array(
		'TEXT' => GetMessage( 'ADD_MESSAGE' ),
		'TITLE' => GetMessage( 'ADD_MESSAGE_DETAIL' ),
		'LINK' => 'omg_import_view.php',
		'ICON' => 'btn_new'
	)
);
$lAdmin->AddAdminContextMenu( $arContext );

$lAdmin->CheckListMode();

$APPLICATION->SetTitle( GetMessage( 'TITLE' ) );

require( $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php' );

$lAdmin->DisplayList();

require( $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php' );
?>

