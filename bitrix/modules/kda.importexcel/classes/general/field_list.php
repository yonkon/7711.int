<?php
IncludeModuleLangFile(__FILE__);

class CKDAFieldList {
	function __construct($params)
	{
		$this->uid = $params['ELEMENT_UID'];
		$this->isSku = !empty($params['ELEMENT_UID_SKU']);
		
		$this->sectionLevels = (is_numeric($params['MAX_SECTION_LEVEL']) > 0 ? $params['MAX_SECTION_LEVEL'] : 5);
		$this->sectionLevels = max(0, $this->sectionLevels);
		$this->sectionLevels = min(100, $this->sectionLevels);
	}
	
	public static function GetIblockElementFields()
	{
		return array(
			"IE_NAME" => array(
				"uid" => "Y",
				"name" => GetMessage("KDA_IE_FI_NAME"),
			) ,
			"IE_ID" => array(
				"uid" => "Y",
				"name" => GetMessage("KDA_IE_FI_ID"),
			) ,
			"IE_XML_ID" => array(
				"uid" => "Y",
				"name" => GetMessage("KDA_IE_FI_UNIXML"),
			) ,
			"IE_CODE" => array(
				"uid" => "Y",
				"name" => GetMessage("KDA_IE_FI_CODE"),
			) ,
			"IE_PREVIEW_PICTURE" => array(
				"name" => GetMessage("KDA_IE_FI_CATIMG"),
			) ,
			"IE_PREVIEW_TEXT" => array(
				"name" => GetMessage("KDA_IE_FI_CATDESCR"),
			) ,
			"IE_PREVIEW_TEXT|PREVIEW_TEXT_TYPE=html" => array(
				"name" => GetMessage("KDA_IE_FI_CATDESCR").' (html)',
			) ,
			"IE_DETAIL_PICTURE" => array(
				"name" => GetMessage("KDA_IE_FI_DETIMG"),
			) ,
			"IE_DETAIL_TEXT" => array(
				"name" => GetMessage("KDA_IE_FI_DETDESCR"),
			) ,
			"IE_DETAIL_TEXT|DETAIL_TEXT_TYPE=html" => array(
				"name" => GetMessage("KDA_IE_FI_DETDESCR").' (html)',
			) ,
			"IE_ACTIVE" => array(
				"name" => GetMessage("KDA_IE_FI_ACTIV"),
			) ,
			"IE_ACTIVE_FROM" => array(
				"name" => GetMessage("KDA_IE_FI_ACTIVFROM"),
			) ,
			"IE_ACTIVE_TO" => array(
				"name" => GetMessage("KDA_IE_FI_ACTIVTO"),
			) ,
			"IE_SORT" => array(
				"name" => GetMessage("KDA_IE_FI_SORT"),
			) ,
			"IE_TAGS" => array(
				"name" => GetMessage("KDA_IE_FI_TAGS"),
			) ,
		);
	}
	
	public static function GetIblockSectionFields($i)
	{
		$arSections = array(
			'ISECT'.$i.'_NAME' => array(
				"name" => GetMessage("KDA_ISECT_FI_NAME")
			),
			'ISECT'.$i.'_CODE' => array(
				"name" => GetMessage("KDA_ISECT_FI_CODE")
			),
			'ISECT'.$i.'_SORT' => array(
				"name" => GetMessage("KDA_ISECT_FI_SORT")
			)
		);
		$arSections['ISECT'.$i.'_NAME'] = array(
			"name" => GetMessage("KDA_ISECT_FI_NAME")
		);
		return $arSections;
	}
	
	public static function GetCatalogFields($IBLOCK_ID)
	{
		$arCatalogFields = array();
		if(CModule::IncludeModule('catalog'))
		{
			$dbRes = CCatalog::GetList(array("ID"=>"ASC"), array("IBLOCK_ID"=>$IBLOCK_ID));
			$arCatalog = $dbRes->Fetch();
			
			if($arCatalog)
			{
				$arCatalogFields[] = array(
					"value" => "ICAT_PURCHASING_PRICE",
					"name" => GetMessage("KDA_IE_FI_PURCHASING_PRICE"),
				);
				$arCatalogFields[] = array(
					"value" => "ICAT_PURCHASING_CURRENCY",
					"name" => GetMessage("KDA_IE_FI_PURCHASING_PRICE")." [".GetMessage("KDA_IE_FI_PRICE_CURRENCY")."]",
				);
			
				$dbPriceType = CCatalogGroup::GetList(array("SORT" => "ASC"));
				while($arPriceType = $dbPriceType->Fetch())
				{
					$arCatalogFields[] = array(
						"value" => "ICAT_PRICE".$arPriceType["ID"]."_PRICE",
						"name" => $arPriceType["NAME_LANG"],
					);
					$arCatalogFields[] = array(
						"value" => "ICAT_PRICE".$arPriceType["ID"]."_CURRENCY",
						"name" => $arPriceType["NAME_LANG"]." [".GetMessage("KDA_IE_FI_PRICE_CURRENCY")."]",
					);
				}
				
				$arCatalogFields[] = array(
					"value" => "ICAT_QUANTITY",
					"name" => GetMessage("KDA_IE_FI_QUANTITY"),
				);
				
				$dbRes = CCatalogStore::GetList(array("SORT"=>"ID"), array(), false, false, array("ID", "TITLE"));
				while($arStore = $dbRes->Fetch())
				{
					$arCatalogFields[] = array(
						"value" => "ICAT_STORE".$arStore["ID"]."_AMOUNT",
						"name" => GetMessage("KDA_IE_FI_QUANTITY_STORE").' "'.$arStore["TITLE"].'"'
					);
				}
				
				$arCatalogFields[] = array(
					"value" => "ICAT_WEIGHT",
					"name" => GetMessage("KDA_IE_FI_WEIGHT"),
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_LENGTH",
					"name" => GetMessage("KDA_IE_FI_LENGTH"),
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_WIDTH",
					"name" => GetMessage("KDA_IE_FI_WIDTH"),
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_HEIGHT",
					"name" => GetMessage("KDA_IE_FI_HEIGHT"),
				);
				
				$arCatalogFields[] = array(
					"value" => "ICAT_VAT_INCLUDED",
					"name" => GetMessage("KDA_IE_FI_VAT_INCLUDED"),
				);
			}
		}
		return (!empty($arCatalogFields) ? $arCatalogFields : false);
	}
	
	public static function GetIblockProperties($IBLOCK_ID)
	{
		$arProperties = array();
		if(CModule::IncludeModule('iblock'))
		{
			$dbRes = CIBlockProperty::GetList(array(
				"sort" => "asc",
				"name" => "asc",
			) , array(
				"ACTIVE" => "Y",
				"IBLOCK_ID" => $IBLOCK_ID,
				"CHECK_PERMISSIONS" => "N",
			));
			while($arr = $dbRes->Fetch())
			{
				$bUid = (in_array($arr['PROPERTY_TYPE'], array('S', 'N')) && $arr['MULTIPLE']=='N');
				$arProperties[] = array(
					"value" => "IP_PROP".$arr["ID"],
					"name" => $arr["NAME"],
					"uid" => ($bUid ? "Y" : "N"),
				);
			}
		}
		return (!empty($arProperties) ? $arProperties : false);
	}

	public function GetFields($IBLOCK_ID, $offers = false)
	{
		if(!$this->aFields)
		{
			$this->aFields = array();
		}
		
		if(!$this->aFields[$IBLOCK_ID])
		{
			$this->aFields[$IBLOCK_ID]['element'] = array(
				'title' => ($offers ? GetMessage("KDA_IE_GROUP_OFFER") : GetMessage("KDA_IE_GROUP_ELEMENT")),
				'items' => array()
			);
			foreach(self::GetIblockElementFields() as $k=>$ar)
			{
				if($this->uid && ((is_array($this->uid) && !in_array('IE_ID', $this->uid)) || (!is_array($this->uid) && $this->uid!='IE_ID')) && $k=='IE_ID') continue;
				if($offers) $k = 'OFFER_'.$k;
				$this->aFields[$IBLOCK_ID]['element']['items'][$k] = $ar["name"];
			}
			
			if(!$offers)
			{
				for($i=1; $i<$this->sectionLevels+1; $i++)
				{
					$this->aFields[$IBLOCK_ID]['section'.$i] = array(
						'title' => sprintf(GetMessage("KDA_IE_GROUP_SECTION_LEVEL"), $i),
						'items' => array()
					);
					foreach(self::GetIblockSectionFields($i) as $k=>$ar)
					{
						$this->aFields[$IBLOCK_ID]['section'.$i]['items'][$k] = $ar["name"];
					}
				}
			}
			
			if($arPropFields = self::GetIblockProperties($IBLOCK_ID))
			{
				$this->aFields[$IBLOCK_ID]['prop'] = array(
					'title' => ($offers ? GetMessage("KDA_IE_GROUP_OFFER").' ('.GetMessage("KDA_IE_GROUP_PROP").')' : GetMessage("KDA_IE_GROUP_PROP")),
					'items' => array()
				);
				foreach($arPropFields as $ar)
				{
					if($offers)
					{
						if(preg_match('/\D'.$offers.'$/', $ar["value"])) continue;
						$ar["value"] = 'OFFER_'.$ar["value"];
					} 
					$this->aFields[$IBLOCK_ID]['prop']['items'][$ar["value"]] = $ar["name"];
				}
			}
			
			if($arCatalogFields = self::GetCatalogFields($IBLOCK_ID))
			{
				$this->aFields[$IBLOCK_ID]['catalog'] = array(
					'title' => ($offers ? GetMessage("KDA_IE_GROUP_OFFER").' ('.GetMessage("KDA_IE_GROUP_CATALOG").')' : GetMessage("KDA_IE_GROUP_CATALOG")),
					'items' => array()
				);
				foreach($arCatalogFields as $ar)
				{
					if($offers) $ar["value"] = 'OFFER_'.$ar["value"];
					$this->aFields[$IBLOCK_ID]['catalog']['items'][$ar["value"]] = $ar["name"];
				}
			}
		}
	
		return $this->aFields[$IBLOCK_ID];
	}
	
	public function GetFieldNames($IBLOCK_ID)
	{
		if(!$this->arFieldNames)
		{
			$this->arFieldNames = array();
		}
		
		if(!$this->arFieldNames[$IBLOCK_ID])
		{
			$this->arFieldNames[$IBLOCK_ID] = array();
			$arFields = $this->GetFields($IBLOCK_ID);
			foreach($arFields as $k=>$v)
			{
				if(is_array($v['items']))
				{
					foreach($v['items'] as $k2=>$v2)
					{
						$this->arFieldNames[$IBLOCK_ID][$k2] = $v2;
					}
				}
			}
		}

		return $this->arFieldNames[$IBLOCK_ID];
	}
	
	public function ShowSelectFields($IBLOCK_ID, $fname, $value)
	{
		$arGroups = $this->GetFields($IBLOCK_ID);
		$arGroupsOffers = array();
		if($this->isSku)
		{
			$arOffer = CKDAImportExcel::GetOfferIblock($IBLOCK_ID, true);
			if($arOffer) $arGroupsOffers = $this->GetFields($arOffer['OFFERS_IBLOCK_ID'], $arOffer['OFFERS_PROPERTY_ID']);
		}
		?><select name="<?echo $fname;?>"><option value=""><?echo GetMessage("KDA_IE_CHOOSE_FIELD");?></option><?
		foreach($arGroups as $k2=>$v2)
		{
			?><optgroup label="<?echo $v2['title']?>"><?
			foreach($v2['items'] as $k=>$v)
			{
				?><option value="<?echo $k; ?>" <?if($k==$value){echo 'selected';}?>><?echo htmlspecialcharsbx($v); ?></option><?
			}
			?></optgroup><?
		}
		foreach($arGroupsOffers as $k2=>$v2)
		{
			?><optgroup label="<?echo $v2['title']?>"><?
			foreach($v2['items'] as $k=>$v)
			{
				?><option value="<?echo $k; ?>" <?if($k==$value){echo 'selected';}?>><?echo htmlspecialcharsbx($v); ?></option><?
			}
			?></optgroup><?
		}
		?></select><?
	}
	
	public function ShowSelectUidFields($IBLOCK_ID, $fname, $val=false, $prefix='')
	{
		$IBLOCK_ID = intval($IBLOCK_ID);
		if(!$this->UidFields)
		{
			$this->UidFields = array();
		}
		
		if(!$this->UidFields[$IBLOCK_ID])
		{
			ob_start();
			foreach(self::GetIblockElementFields() as $k=>$ar)
			{
				if($ar['uid']=="Y")
				{
					$k = $prefix.$k;
					?><option value="<?echo $k; ?>" <?if((is_array($val) && in_array($k, $val)) || $k==$val){echo 'selected';}?>><?echo htmlspecialcharsbx($ar["name"]); ?></option><?
				}
			}
			
			if($arPropFields = self::GetIblockProperties($IBLOCK_ID))
			{
				foreach($arPropFields as $ar)
				{
					if($ar['uid']=="Y")
					{
						$ar["value"] = $prefix.$ar["value"];
						?><option value="<?echo $ar["value"] ?>" <?if((is_array($val) && in_array($ar["value"], $val)) || $ar["value"]==$val){echo 'selected';}?>><?echo GetMessage("KDA_IE_FI_PROP");?> "<?echo htmlspecialcharsbx($ar["name"]); ?>"</option><?
					}
				}
			}		
			$this->UidFields[$IBLOCK_ID] = ob_get_clean();
		}
	
		?><select name="<?echo $fname;?>" class="chosen" multiple><?echo $this->UidFields[$IBLOCK_ID];?></select><?
	}
	
	public function ShowSelectSections($IBLOCK_ID, $fname, $value)
	{
		if(!$this->Sections)
		{
			$this->Sections = array();
		}
		
		if(!$this->Sections[$IBLOCK_ID])
		{
			if($IBLOCK_ID)
			{
				$this->Sections[$IBLOCK_ID][] = array(
					'ID' => '',
					'NAME' => GetMessage("KDA_IE_NO_SECTION")
				);
				
				if(CModule::IncludeModule('iblock'))
				{
				$dbRes = CIBlockSection::GetList(array("LEFT_MARGIN"=>"ASC"), array('IBLOCK_ID'=>$IBLOCK_ID), false, array('ID', 'NAME', 'DEPTH_LEVEL'));
					while($arr = $dbRes->Fetch())
					{
						$this->Sections[$IBLOCK_ID][] = array(
							'ID' => $arr['ID'],
							'NAME' => str_repeat(' . ', $arr['DEPTH_LEVEL']).$arr['NAME']
						);
					}
				}
			}
			else
			{
				$this->Sections[$IBLOCK_ID][] = array(
					'ID' => '',
					'NAME' => GetMessage("KDA_IE_CHOOSE_SECTION_FIRST")
				);
			}
		}
	
		?><select name="<?echo $fname;?>"><?
		foreach($this->Sections[$IBLOCK_ID] as $arr)
		{
			?><option value="<?echo $arr['ID'];?>" <?if($arr['ID']==$value){echo 'selected';}?>><?echo htmlspecialcharsbx($arr['NAME']); ?></option><?
		}
		?></select><?
	}
	
	public function GetIblocks()
	{
		$arIblocks = array();
		$dbRes = CIBlock::GetList(array('NAME'=>'ASC'), array());
		while($arr = $dbRes->Fetch())
		{
			$arIblocks[$arr['IBLOCK_TYPE_ID']][] = array(
				'ID' => $arr['ID'],
				'NAME' => $arr['NAME']
			);
		}
		
		$dbRes = CIBlockType::GetList(array("SORT"=>"ASC", "NAME"=>"ASC"), array("LANGUAGE_ID" => LANG));
		while($arr = $dbRes->Fetch())
		{
			$arr = array(
				'ID' => $arr['ID'],
				'NAME' => $arr['NAME']
			);
			$arr['IBLOCKS'] = $arIblocks[$arr['ID']];
			$arIblocks[$arr['ID']] = $arr;
		}
		return $arIblocks;
	}
}