<?php
require_once(dirname(__FILE__).'/../../lib/PHPExcel/PHPExcel.php');
IncludeModuleLangFile(__FILE__);

class CKDAImportExcel {
	function __construct($filename, $params, $fparams, $stepparams, $pid = false)
	{
		global $DB;
		$this->db = $DB;
		$this->filename = $_SERVER['DOCUMENT_ROOT'].$filename;
		$this->params = $params;
		$this->fparams = $fparams;
		$this->maxReadRows = 2000;
		$this->sections = array();
		$this->propVals = array();
		$this->hlbl = array();
		$this->errors = array();
		$this->fl = new CKDAFieldList();
		$this->stepparams = $stepparams;
		$this->stepparams['total_read_line'] = intval($this->stepparams['total_read_line']);
		$this->stepparams['total_line'] = intval($this->stepparams['total_line']);
		$this->stepparams['correct_line'] = intval($this->stepparams['correct_line']);
		$this->stepparams['error_line'] = intval($this->stepparams['error_line']);
		$this->stepparams['killed_line'] = intval($this->stepparams['killed_line']);
		$this->stepparams['last_proccess_id'] = intval($this->stepparams['last_proccess_id']);
		$this->stepparams['last_proccess_active_id'] = intval($this->stepparams['last_proccess_active_id']);
		$this->stepparams['worksheetCurrentRow'] = intval($this->stepparams['worksheetCurrentRow']);
		$this->stepparams['total_file_line'] = 0;
		if(is_array($this->params['LIST_LINES']))
		{
			foreach($this->params['LIST_ACTIVE'] as $k=>$v)
			{
				if($v=='Y')
				{
					$this->stepparams['total_file_line'] += $this->params['LIST_LINES'][$k];
				}
			}
		}
		if($this->stepparams['tmpfile'])
		{
			$this->stepparams = array_merge($this->stepparams, unserialize(file_get_contents($this->stepparams['tmpfile'])));
		}
		
		if(!isset($this->params['MAX_EXECUTION_TIME']) || $this->params['MAX_EXECUTION_TIME']!==0)
		{
			$this->params['MAX_EXECUTION_TIME'] = intval(ini_get('max_execution_time')) - 10;
			if($this->params['MAX_EXECUTION_TIME'] < 10) $this->params['MAX_EXECUTION_TIME'] = 20;
			if($this->params['MAX_EXECUTION_TIME'] > 120) $this->params['MAX_EXECUTION_TIME'] = 120;
		}
		
		$this->filecnt = 0;
		$dir = $_SERVER["DOCUMENT_ROOT"].'/upload/tmp/kda.importexcel/';
		CheckDirPath($dir);
		$i = 0;
		while(($tmpdir = $dir.$i.'/') && file_exists($tmpdir)){$i++;}
		$this->tmpdir = $tmpdir;
		CheckDirPath($this->tmpdir);
		
		if($pid!==false)
		{
			$this->procfile = $dir.$pid.'.txt';
			$this->errorfile = $dir.$pid.'_error.txt';
			if($this->stepparams['total_line'] < 1)
			{
				unlink($this->procfile);
				unlink($this->errorfile);
			}
		}
	}
	
	public static function GetFileName($fn)
	{
		global $APPLICATION;
		if(file_exists($_SERVER['DOCUMENT_ROOT'].$fn)) return $fn;
		
		if(defined("BX_UTF")) $tmpfile = $APPLICATION->ConvertCharsetArray($fn, LANG_CHARSET, 'CP1251');
		else $tmpfile = $APPLICATION->ConvertCharsetArray($fn, LANG_CHARSET, 'UTF-8');
		
		if(file_exists($_SERVER['DOCUMENT_ROOT'].$tmpfile)) return $tmpfile;
		
		return false;
	}
	
	public function Import()
	{
		$this->InitImport();
		$time = time();
		while($arItem = $this->GetNextRecord())
		{
			$this->SaveRecord($arItem);
			if($this->params['MAX_EXECUTION_TIME'] && (time()-$time >= $this->params['MAX_EXECUTION_TIME']))
			{
				return $this->GetBreakParams();
			}
		}
		
		return $this->EndOfLoading($time);
	}
	
	public function EndOfLoading($time)
	{
		if($this->params['ELEMENT_MISSING_DEACTIVATE']=='Y')
		{
			$arFieldsList = array();
			$arOffersExists = array();
			foreach($this->params['IBLOCK_ID'] as $k=>$v)
			{
				if($this->params['LIST_ACTIVE'][$k]!='Y') continue;
				
				if(count(preg_grep('/^OFFER_/', $this->params['FIELDS_LIST'][$k])) > 0)
				{
					$arOffersExists[$k] = true;
				}
				
				$arFieldsList[$k] = array(
					'IBLOCK_ID' => $v
				);
				if($this->params['SECTION_ID'][$k])
				{
					$arFieldsList[$k]['SECTION_ID'] =  $this->params['SECTION_ID'][$k];
				}
				if(is_array($this->fparams[$k]))
				{
					$propsDef = $this->props[$v];
					foreach($this->fparams[$k] as $k2=>$ffilter)
					{
						if($ffilter['USE_FILTER_FOR_DEACTIVATE']=='Y' && $this->params['FIELDS_LIST'][$k][$k2] && (!empty($ffilter['UPLOAD_VALUES']) || !empty($ffilter['NOT_UPLOAD_VALUES'])))
						{
							$fieldName = '';
							$field = $this->params['FIELDS_LIST'][$k][$k2];
							if(strpos($field, 'IE_')===0)
							{
								$fieldName = substr($field, 3);
								if(strpos($fieldName, '|')!==false) $fieldName = current(explode('|', $fieldName));
							}
							elseif(strpos($field, 'IP_PROP')===0)
							{
								$propId = substr($field, 7);
								$fieldName = 'PROPERTY_'.$propId;
								if($propsDef[$propId]['PROPERTY_TYPE']=='L')
								{
									$fieldName .= '_VALUE';
								}
							}
							if(strlen($fieldName) > 0)
							{
								if(!empty($ffilter['UPLOAD_VALUES']))
								{
									$arFieldsList[$k][$fieldName] = $ffilter['UPLOAD_VALUES'];
								}
								elseif(!empty($ffilter['NOT_UPLOAD_VALUES']))
								{
									$arFieldsList[$k]['!'.$fieldName] = $ffilter['NOT_UPLOAD_VALUES'];
								}
							}
						}
					}
				}
			}
			$el = new CIblockElement();
			foreach($arFieldsList as $arFields)
			{
				$arFieldsOrig = $arFields;
				if($this->stepparams['begin_time'])
				{
					$arFields['<TIMESTAMP_X'] = $this->stepparams['begin_time'];
				}
				$arFields['ACTIVE'] = 'Y';
				//if(!empty($this->stepparams['updated_elements'])) $arFields['!ID'] = $this->stepparams['updated_elements'];
				if($this->stepparams['last_proccess_id']) $arFields['>ID'] = $this->stepparams['last_proccess_id'];
				$dbRes = CIblockElement::GetList(array('ID'=>'ASC'), $arFields, false, false, array('ID'));
				while($arr = $dbRes->Fetch())
				{
					$this->stepparams['last_proccess_id'] = $arr['ID'];
					if(is_array($this->stepparams['updated_elements']) && in_array($arr['ID'], $this->stepparams['updated_elements']))
					{
						if($arOffersExists)
						{
							$this->DeactivateOffersByProductId($arr['ID'], $arFields['IBLOCK_ID']);
						}
						$key = array_search($arr['ID'], $this->stepparams['updated_elements']);
						unset($this->stepparams['updated_elements'][$key]);
						continue;
					}
					$el->Update($arr['ID'], array('ACTIVE'=>'N'));
					$this->stepparams['killed_line']++;
					$this->SaveStatusImport();
					if($this->params['MAX_EXECUTION_TIME'] && (time()-$time >= $this->params['MAX_EXECUTION_TIME']))
					{
						return $this->GetBreakParams();
					}
				}
				
				if($arOffersExists && $this->stepparams['begin_time'])
				{
					$arFields = $arFieldsOrig;
					$arFields['>=TIMESTAMP_X'] = $this->stepparams['begin_time'];
					$arFields['ACTIVE'] = 'Y';
					if($this->stepparams['last_proccess_active_id']) $arFields['>ID'] = $this->stepparams['last_proccess_active_id'];
					$dbRes = CIblockElement::GetList(array('ID'=>'ASC'), $arFields, false, false, array('ID'));
					while($arr = $dbRes->Fetch())
					{
						$this->DeactivateOffersByProductId($arr['ID'], $arFields['IBLOCK_ID']);
						if($this->params['MAX_EXECUTION_TIME'] && (time()-$time >= $this->params['MAX_EXECUTION_TIME']))
						{
							return $this->GetBreakParams();
						}
					}
				}
			}
		}
		
		if($this->params['SECTION_EMPTY_DEACTIVATE']=='Y' || $this->params['SECTION_NOTEMPTY_ACTIVATE']=='Y')
		{
			foreach($this->params['IBLOCK_ID'] as $k=>$v)
			{
				if($this->params['LIST_ACTIVE'][$k]!='Y') continue;
		
				$arFieldsListSections = array(
					'IBLOCK_ID' => $v
				);
				$arFieldsListElements = array(
					'IBLOCK_ID' => $v,
					'ACTIVE' => 'Y'
				);
				if($this->params['SECTION_ID'][$k])
				{
					$arFieldsListElements['SECTION_ID'] = $this->params['SECTION_ID'][$k];
					$arFieldsListElements['INCLUDE_SUBSECTIONS'] = 'Y';
					$dbRes = CIBlockSection::GetList(array(), array('ID'=>$this->params['SECTION_ID'][$k]), false, array('LEFT_MARGIN', 'RIGHT_MARGIN'));
					if($arr = $dbRes->Fetch())
					{
						$arFieldsListSections['>=LEFT_MARGIN'] = $arr['LEFT_MARGIN'];
						$arFieldsListSections['<=RIGHT_MARGIN'] = $arr['RIGHT_MARGIN'];
					}
					else
					{
						continue;
					}
				}
				
				$arListSections = array();
				$dbRes = CIBlockSection::GetList(array('DEPTH_LEVEL'=>'DESC'), $arFieldsListSections, false, array('ID', 'IBLOCK_SECTION_ID'));
				while($arr = $dbRes->Fetch())
				{
					$arListSections[$arr['ID']] = ($arFieldsListElements['SECTION_ID']==$arr['ID'] ? false : $arr['IBLOCK_SECTION_ID']);
				}
				
				$arActiveSections = array();
				$dbRes = CIblockElement::GetList(array(), $arFieldsListElements, array('IBLOCK_SECTION_ID'), false, array('IBLOCK_SECTION_ID'));
				while($arr = $dbRes->Fetch())
				{
					$sid = $arr['IBLOCK_SECTION_ID'];
					$arActiveSections[] = $sid;
					while($sid = $arListSections[$sid])
					{
						$arActiveSections[] = $sid;
					}
				}
				
				$sect = new CIBlockSection();
				if($this->params['SECTION_NOTEMPTY_ACTIVATE']=='Y')
				{
					foreach($arActiveSections as $sid)
					{
						$sect->Update($sid, array('ACTIVE'=>'Y'));
					}
				}
				
				if($this->params['SECTION_EMPTY_DEACTIVATE']=='Y')
				{
					$arInactiveSections = array_diff(array_keys($arListSections), $arActiveSections);
					foreach($arInactiveSections as $sid)
					{
						$sect->Update($sid, array('ACTIVE'=>'N'));
					}
				}
			}
		}
		
		$this->SaveStatusImport(true);
		/*unlink($this->procfile);
		unlink($this->errorfile);*/
		
		foreach(GetModuleEvents("kda.importexcel", "OnEndImport", true) as $arEvent)
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, array());
			if($bEventRes['ACTION']=='REDIRECT')
			{
				$this->stepparams['redirect_url'] = $bEventRes['LOCATION'];
			}
		}
		
		return $this->GetBreakParams('finish');
	}
	
	public function DeactivateOffersByProductId($ID, $IBLOCK_ID)
	{
		if(!$this->iblockoffers || !isset($this->iblockoffers[$IBLOCK_ID]))
		{
			$this->iblockoffers[$IBLOCK_ID] = self::GetOfferIblock($IBLOCK_ID, true);
		}
		if(!$this->iblockoffers[$IBLOCK_ID]) return false;
		$OFFERS_IBLOCK_ID = $this->iblockoffers[$IBLOCK_ID]['OFFERS_IBLOCK_ID'];
		$OFFERS_PROPERTY_ID = $this->iblockoffers[$IBLOCK_ID]['OFFERS_PROPERTY_ID'];
		
		$arFields = array(
			'IBLOCK_ID' => $OFFERS_IBLOCK_ID,
			'ACTIVE' => 'Y',
			'PROPERTY_'.$OFFERS_PROPERTY_ID => $ID
		);
		
		if($this->stepparams['begin_time'])
		{
			$arFields['<TIMESTAMP_X'] = $this->stepparams['begin_time'];
		}
		
		$el = new CIblockElement();
		$dbRes = CIblockElement::GetList(array('ID'=>'ASC'), $arFields, false, false, array('ID'));
		while($arr = $dbRes->Fetch())
		{
			if(is_array($this->stepparams['updated_offers']) && in_array($arr['ID'], $this->stepparams['updated_offers']))
			{
				$key = array_search($arr['ID'], $this->stepparams['updated_offers']);
				unset($this->stepparams['updated_offers'][$key]);
				continue;
			}
			$el->Update($arr['ID'], array('ACTIVE'=>'N'));
		}
	}
	
	public function InitImport()
	{
		//$this->efile = PHPExcel_IOFactory::load($this->filename);

		$this->objReader = PHPExcel_IOFactory::createReaderForFile($this->filename);
		$this->objReader->setReadDataOnly(true);
		$this->chunkFilter = new chunkReadFilter2();
		$this->objReader->setReadFilter($this->chunkFilter);
		$this->chunkFilter->setRows($this->stepparams['worksheetCurrentRow'] + 1, $this->maxReadRows);
		$this->efile = $this->objReader->load($this->filename);

		if(is_array($this->stepparams) && isset($this->stepparams['worksheetNum']))
		{
			$worksheetNum = intval($this->stepparams['worksheetNum']);
			$worksheetCurrentRow = intval($this->stepparams['worksheetCurrentRow']);
			$this->SetPosition();
			$this->SetWorksheet($worksheetNum, $worksheetCurrentRow);
		}
		else
		{
			$this->SetPosition();
		}
	}
	
	public function SetPosition()
	{
		if(!isset($this->worksheetIterator))
		{
			$this->worksheetIterator = $this->efile->getWorksheetIterator();
			$this->NextWorksheet();
		}
	}
	
	public function GetBreakParams($action = 'continue')
	{
		$arStepParams = array(
			'params'=> array_merge($this->stepparams, array(
				'worksheetNum' => intval($this->worksheetNum),
				'worksheetCurrentRow' => $this->worksheetCurrentRow
			)),
			'action' => $action,
			'errors' => $this->errors
		);
		
		if($action == 'continue')
		{
			if(!isset($arStepParams['params']['tmpfile']))
			{
				$dir = $_SERVER["DOCUMENT_ROOT"].'/upload/tmp/kda.importexcel/';
				CheckDirPath($dir);
				$arStepParams['params']['tmpfile'] = tempnam($dir, 'load');
			}
			file_put_contents($arStepParams['params']['tmpfile'], serialize($arStepParams['params']));
		}
		elseif($arStepParams['params']['tmpfile'])
		{
			unlink($arStepParams['params']['tmpfile']);
		}
		unset($arStepParams['params']['updated_elements'], $arStepParams['params']['updated_offers']);
		
		if($this->tmpdir)
		{
			DeleteDirFilesEx(substr($this->tmpdir, strlen($_SERVER['DOCUMENT_ROOT'])));
		}
		
		return $arStepParams;
	}
	
	public function SetWorksheet($worksheetNum, $worksheetCurrentRow)
	{
		while($worksheetNum > $this->worksheetNum)
		{
			$this->NextWorksheet();
		}
		
		$this->worksheetCurrentRow = $worksheetCurrentRow;
	}
	
	public function NextWorksheet()
	{
		if(isset($this->worksheet))
		{
			$this->worksheetIterator->next();
			$this->worksheetNum++;
			if(!$this->worksheetIterator->valid())
			{
				$this->worksheet = false;
				return false;
			}
		}
		else
		{
			$this->worksheetNum = 0;
		}
		$this->worksheet = $this->worksheetIterator->current();
		if($this->params['LIST_ACTIVE'][$this->worksheetNum]!='Y')
		{
			return $this->NextWorksheet();
		}
		
		$filedList = $this->params['FIELDS_LIST'][$this->worksheetNum];
		$iblockId = $this->params['IBLOCK_ID'][$this->worksheetNum];
		if((is_array($this->params['ELEMENT_UID']) && count(array_diff($this->params['ELEMENT_UID'], $filedList)) > 0)
			|| (!is_array($this->params['ELEMENT_UID']) && !in_array($this->params['ELEMENT_UID'], $filedList)))
		{
			if($this->worksheet->getHighestRow() > 0)
			{
				$nofields = (is_array($this->params['ELEMENT_UID']) ? array_diff($this->params['ELEMENT_UID'], $filedList) : array($this->params['ELEMENT_UID']));
				$fieldNames = $this->fl->GetFieldNames($iblockId);
				foreach($nofields as $k=>$field)
				{
					$nofields[$k] = '"'.$fieldNames[$field].'"';
				}
				$nofields = implode(', ', $nofields);
				$this->errors[] = sprintf(GetMessage("KDA_IE_NOT_SET_UID"), $this->worksheetNum+1, $nofields);
			}
			return $this->NextWorksheet();
		}
		
		$this->fieldSettings = array();
		foreach($filedList as $k=>$field)
		{
			$this->fieldSettings[$field] = $this->fparams[$this->worksheetNum][$k];
		}
		
		$this->worksheetColumns = PHPExcel_Cell::columnIndexFromString($this->worksheet->getHighestColumn());
		$this->worksheetRows = $this->worksheet->getHighestRow();
		$this->worksheetCurrentRow = 0;
		return true;
	}
	
	public function SetFilePosition($pos)
	{
		$this->worksheetCurrentRow = $pos;
		if($this->worksheetCurrentRow >= $this->worksheetRows)
		{
			$worksheetNum = $this->worksheetNum;
			$this->chunkFilter->setRows($pos, $this->maxReadRows);
			$this->efile = $this->objReader->load($this->filename);
			unset($this->worksheetIterator, $this->worksheet);
			$this->SetPosition();
			$this->SetWorksheet($worksheetNum, $pos);
			
			if($this->worksheetCurrentRow >= $this->worksheetRows)
			{
				$this->NextWorksheet();
			}
		}
	}
	
	public function CheckSkipLine()
	{
		$load = true;
		if(is_array($this->fparams[$this->worksheetNum]))
		{
			foreach($this->fparams[$this->worksheetNum] as $k=>$v)
			{
				if(!is_array($v)) continue;
				if(is_array($v['UPLOAD_VALUES']) || is_array($v['NOT_UPLOAD_VALUES']))
				{
					$val = $this->worksheet->getCellByColumnAndRow($k, $this->worksheetCurrentRow+1);
					$val = ToLower(trim($this->GetCalculatedValue($val)));
				}
				else
				{
					$val = '';
				}
				
				if(is_array($v['UPLOAD_VALUES']))
				{
					$subload = false;
					foreach($v['UPLOAD_VALUES'] as $needval)
					{
						if(ToLower(trim($needval))==$val)
						{
							$subload = true;
						}
					}
					$load = ($load && $subload);
				}
				
				if(is_array($v['NOT_UPLOAD_VALUES']))
				{
					$subload = true;
					foreach($v['NOT_UPLOAD_VALUES'] as $needval)
					{
						if(ToLower(trim($needval))==$val)
						{
							$subload = false;
						}
					}
					$load = ($load && $subload);
				}
			}
		}
		return !$load;
	}
	
	public function GetNextRecord()
	{
		while($this->worksheet && 
				((!$this->params['CHECK_ALL'][$this->worksheetNum] && !isset($this->params['IMPORT_LINE'][$this->worksheetNum][$this->worksheetCurrentRow])) || 
				(isset($this->params['IMPORT_LINE'][$this->worksheetNum][$this->worksheetCurrentRow]) && !$this->params['IMPORT_LINE'][$this->worksheetNum][$this->worksheetCurrentRow])
				||
				$this->CheckSkipLine()))
		{
			$this->SetFilePosition($this->worksheetCurrentRow + 1);
		}

		if(!$this->worksheet)
		{
			return false;
		}
		
		$arItem = array();
		for($column = 0; $column < $this->worksheetColumns; $column++) 
		{
			$val = $this->worksheet->getCellByColumnAndRow($column, $this->worksheetCurrentRow+1);
			$val = $this->GetCalculatedValue($val);
			$arItem[$column] = trim($val);
			$arItem['~'.$column] = $val;
		}
		$this->worksheetNumForSave = $this->worksheetNum;
		$this->SetFilePosition($this->worksheetCurrentRow + 1);
		return $arItem;
	}
	
	public function SaveRecord($arItem)
	{
		$this->stepparams['total_read_line']++;
		if(count(array_diff(array_map('trim', $arItem), array('')))==0) return false;
		$this->stepparams['total_line']++;
		
		$filedList = $this->params['FIELDS_LIST'][$this->worksheetNumForSave];
		$IBLOCK_ID = $this->params['IBLOCK_ID'][$this->worksheetNumForSave];
		$SECTION_ID = $this->params['SECTION_ID'][$this->worksheetNumForSave];
		$arFieldsDef = $this->fl->GetFields($IBLOCK_ID);

		if(!$this->iblockFields[$IBLOCK_ID])
		{
			$this->iblockFields[$IBLOCK_ID] = CIBlock::GetFields($IBLOCK_ID);
		}
		$iblockFields = $this->iblockFields[$IBLOCK_ID];
		
		$arFieldsElement = array();
		$arFieldsElementOrig = array();
		$arFieldsPrices = array();
		$arFieldsProduct = array();
		$arFieldsProductStores = array();
		$arFieldsProps = array();
		$arFieldsPropsOrig = array();
		$arFieldsSections = array();
		foreach($filedList as $k=>$field)
		{
			if(strpos($field, 'IE_')===0)
			{
				if(strpos($field, '|')!==false)
				{
					list($field, $adata) = explode('|', $field);
					$adata = explode('=', $adata);
					if(count($adata) > 1)
					{
						$arFieldsElement[$adata[0]] = $adata[1];
					}
				}
				$arFieldsElement[substr($field, 3)] = $arItem[$k];
				$arFieldsElementOrig[substr($field, 3)] = $arItem['~'.$k];
			}
			elseif(strpos($field, 'ISECT')===0)
			{
				$arSect = explode('_', substr($field, 5), 2);
				$arFieldsSections[$arSect[0]][$arSect[1]] = $arItem[$k];
			}
			elseif(strpos($field, 'ICAT_PRICE')===0)
			{
				$val = $arItem[$k];
				$margins = $this->fparams[$this->worksheetNumForSave][$k]['MARGINS'];
				if(is_array($margins) && count($margins) > 0)
				{
					foreach($margins as $margin)
					{
						if((strlen(trim($margin['PRICE_FROM']))==0 || $arItem[$k] >= floatval($margin['PRICE_FROM']))
							&& (strlen(trim($margin['PRICE_TO']))==0 || $arItem[$k] <= floatval($margin['PRICE_TO'])))
						{
							$val *= (1 + ($margin['TYPE'] > 0 ? 1 : -1)*floatval($margin['PERCENT'])/100);
						}
					}
				}
				
				$arPrice = explode('_', substr($field, 10), 2);
				$arFieldsPrices[$arPrice[0]][$arPrice[1]] = $val;
			}
			elseif(strpos($field, 'ICAT_STORE')===0)
			{
				$arStore = explode('_', substr($field, 10), 2);
				$arFieldsProductStores[$arStore[0]][$arStore[1]] = $arItem[$k];
			}
			elseif(strpos($field, 'ICAT_')===0)
			{
				$arFieldsProduct[substr($field, 5)] = $arItem[$k];
			}
			elseif(strpos($field, 'IP_PROP')===0)
			{
				$arFieldsProps[substr($field, 7)] = $arItem[$k];
				$arFieldsPropsOrig[substr($field, 7)] = $arItem['~'.$k];
			}
		}

		$arUid = array();
		if(!is_array($this->params['ELEMENT_UID'])) $this->params['ELEMENT_UID'] = array($this->params['ELEMENT_UID']);
		foreach($this->params['ELEMENT_UID'] as $tuid)
		{
			$uid = $valUid = $nameUid = '';
			if(strpos($tuid, 'IE_')===0)
			{
				$nameUid = $arFieldsDef['element']['items'][$tuid];
				$uid = substr($tuid, 3);
				if(strpos($uid, '|')!==false) $uid = current(explode('|', $uid));
				$valUid = $arFieldsElementOrig[$uid];
			}
			elseif(strpos($tuid, 'IP_PROP')===0)
			{
				$nameUid = $arFieldsDef['prop']['items'][$tuid];
				$uid = substr($tuid, 7);
				$valUid = $arFieldsPropsOrig[$uid];
				if($arFieldsDef[$uid]['PROPERTY_TYPE']=='L')
				{
					$uid = 'PROPERTY_'.$uid.'_VALUE';
				}
				else
				{
					$uid = 'PROPERTY_'.$uid;
				}
			}
			if($uid)
			{
				$arUid[] = array(
					'uid' => $uid,
					'nameUid' => $nameUid,
					'valUid' => $valUid,
				);
			}
		}
		
		$emptyFields = array();
		foreach($arUid as $k=>$v)
		{
			if(!trim($v['valUid'])) $emptyFields[] = $v['nameUid'];
		}
		
		if(!empty($emptyFields) || empty($arUid))
		{
			$this->errors[] = sprintf(GetMessage("KDA_IE_NOT_SET_FIELD"), implode(', ', $emptyFields), $this->worksheetNumForSave+1, $this->worksheetCurrentRow);
			$this->stepparams['error_line']++;
			return false;
		}
		
		$arDates = array('ACTIVE_FROM', 'ACTIVE_TO');
		foreach($arDates as $dateName)
		{
			if($arFieldsElement[$dateName])
			{
				$time = strtotime($arFieldsElement[$dateName]);
				if($time > 0)
				{
					$arFieldsElement[$dateName] = date($this->db->DateFormatToPHP(CLang::GetDateFormat("FULL")), $time);
				}
				else
				{
					unset($arFieldsElement[$dateName]);
				}
			}
		}
		
		$arPictures = array('PREVIEW_PICTURE', 'DETAIL_PICTURE');
		foreach($arPictures as $picName)
		{
			if($arFieldsElement[$picName])
			{
				$arFieldsElement[$picName] = $this->GetFileArray($arFieldsElement[$picName]);
			}
		}
		
		if(isset($arFieldsElement['ACTIVE']))
		{
			$arFieldsElement['ACTIVE'] = $this->GetBoolValue($arFieldsElement['ACTIVE']);
		}
		elseif($this->params['ELEMENT_LOADING_ACTIVATE']=='Y')
		{
			$arFieldsElement['ACTIVE'] = 'Y';
		}

		if(($this->params['ELEMENT_NO_QUANTITY_DEACTIVATE']=='Y' && isset($arFieldsProduct['QUANTITY']) && floatval($arFieldsProduct['QUANTITY'])==0)
			|| ($this->params['ELEMENT_NO_PRICE_DEACTIVATE']=='Y' && $this->IsEmptyPrice($arFieldsPrices)))
		{
			$arFieldsElement['ACTIVE'] = 'N';
		}
		
		$this->GetSections($arFieldsElement, $IBLOCK_ID, $SECTION_ID, $arFieldsSections);
		
		$arKeys = array_merge(array('ID', 'NAME'), array_keys($arFieldsElement));
		if(in_array('IBLOCK_SECTION', $arKeys)) $arKeys[] = 'IBLOCK_SECTION_ID';
		
		$arFilter = array('IBLOCK_ID'=>$IBLOCK_ID);
		foreach($arUid as $v)
		{
			if(strlen($v['valUid']) != strlen(trim($v['valUid'])))
			{
				$arFilter[] = array('LOGIC'=>'OR', array($v['uid']=>trim($v['valUid'])), array($v['uid']=>$v['valUid']));
			}
			else
			{
				$arFilter[$v['uid']] = trim($v['valUid']);
			}
		}
		
		$dbRes = CIblockElement::GetList(array(), $arFilter, false, false, $arKeys);
		while($arElement = $dbRes->Fetch())
		{
			$ID = $arElement['ID'];
			/*Delete unchanged data*/
			foreach($arFieldsElement as $k=>$v)
			{
				if($k=='IBLOCK_SECTION' && is_array($v))
				{
					if((!$arElement['IBLOCK_SECTION_ID'] && empty($v)) || (count($v)==1 && current($v)==$arElement['IBLOCK_SECTION_ID']))
					{
						unset($arFieldsElement[$k]);
					}
				}
				elseif($v==$arElement[$k])
				{
					unset($arFieldsElement[$k]);
				}
			}
			/*/Delete unchanged data*/
			
			$this->SaveProduct($ID, $arFieldsProduct, $arFieldsPrices, $arFieldsProductStores);
			$this->SaveProperties($ID, $IBLOCK_ID, $arFieldsProps, (bool)(empty($arFieldsElement)));
			
			$el = new CIblockElement();
			if(!empty($arFieldsElement))
			{
				if($el->Update($ID, $arFieldsElement, false, true, true))
				{
					$this->SetTimeBegin($ID);
				}
				else
				{
					$this->stepparams['updated_elements'][] = $ID;
				}
			}
			else
			{
				$this->stepparams['updated_elements'][] = $ID;
			}
			
			if(!$arFieldsElement['NAME']) $arFieldsElement['NAME'] = $arElement['NAME'];
		}
		
		if($dbRes->SelectedRowsCount()==0 && $this->params['ONLY_UPDATE_MODE']!='Y')
		{
			if(strlen($arFieldsElement['NAME'])==0)
			{
				$this->stepparams['error_line']++;
				$this->errors[] = sprintf(GetMessage("KDA_IE_NOT_SET_FIELD"), $arFieldsDef['element']['items']['IE_NAME'], $this->worksheetNumForSave+1, $this->worksheetCurrentRow);
				return false;
			}
			if($this->params['ELEMENT_NEW_DEACTIVATE']=='Y')
			{
				$arFieldsElement['ACTIVE'] = 'N';
			}
			elseif(!$arFieldsElement['ACTIVE'])
			{
				$arFieldsElement['ACTIVE'] = 'Y';
			}
			$arFieldsElement['IBLOCK_ID'] = $IBLOCK_ID;
			if($iblockFields['CODE']['IS_REQUIRED']=='Y' && strlen($arFieldsElement['CODE'])==0)
			{
				$arFieldsElement['CODE'] = $this->Str2Url($arFieldsElement['NAME']);
			}
			$el = new CIblockElement();
			$ID = $el->Add($arFieldsElement, false, true, true);
			
			if($ID)
			{
				$this->SetTimeBegin($ID);
				$this->SaveProduct($ID, $arFieldsProduct, $arFieldsPrices, $arFieldsProductStores);
				$this->SaveProperties($ID, $IBLOCK_ID, $arFieldsProps, true);
			}
			else
			{
				$this->stepparams['error_line']++;
				$this->errors[] = sprintf(GetMessage("KDA_IE_ADD_ELEMENT_ERROR"), $el->LAST_ERROR, $this->worksheetNumForSave+1, $this->worksheetCurrentRow);
				return false;
			}
		}
		
		if($ID && $this->params['ELEMENT_UID_SKU'])
		{
			$this->SaveSKU($ID, $arFieldsElement['NAME'], $IBLOCK_ID, $arItem);
		}
		
		$this->stepparams['correct_line']++;
		
		$this->SaveStatusImport();
	}
	
	public function SaveStatusImport($end = false)
	{
		if($this->procfile)
		{
			$writeParams = $this->stepparams;
			unset($writeParams['updated_elements'], $writeParams['updated_offers'], $writeParams['tmpfile']);
			$writeParams['action'] = ($end ? 'finish' : 'continue');
			file_put_contents($this->procfile, CUtil::PhpToJSObject($writeParams));
		}
	}
	
	public function SaveSKU($ID, $NAME, $IBLOCK_ID, $arItem)
	{
		if(!$this->iblockoffers || !isset($this->iblockoffers[$IBLOCK_ID]))
		{
			$this->iblockoffers[$IBLOCK_ID] = self::GetOfferIblock($IBLOCK_ID, true);
		}
		if(!$this->iblockoffers[$IBLOCK_ID]) return false;
		$OFFERS_IBLOCK_ID = $this->iblockoffers[$IBLOCK_ID]['OFFERS_IBLOCK_ID'];
		$OFFERS_PROPERTY_ID = $this->iblockoffers[$IBLOCK_ID]['OFFERS_PROPERTY_ID'];
		
		$filedList = $this->params['FIELDS_LIST'][$this->worksheetNumForSave];
		$arFieldsDef = $this->fl->GetFields($OFFERS_IBLOCK_ID);

		if(!$this->iblockFields[$OFFERS_IBLOCK_ID])
		{
			$this->iblockFields[$OFFERS_IBLOCK_ID] = CIBlock::GetFields($OFFERS_IBLOCK_ID);
		}
		$iblockFields = $this->iblockFields[$OFFERS_IBLOCK_ID];
		
		$arFieldsElement = array();
		$arFieldsElementOrig = array();
		$arFieldsPrices = array();
		$arFieldsProduct = array();
		$arFieldsProductStores = array();
		$arFieldsProps = array($OFFERS_PROPERTY_ID => $ID);
		$arFieldsPropsOrig = array($OFFERS_PROPERTY_ID => $ID);
		foreach($filedList as $k=>$field)
		{
			if(strpos($field, 'OFFER_')!==0) continue;
			$field = substr($field, 6);
			
			if(strpos($field, 'IE_')===0)
			{
				if(strpos($field, '|')!==false)
				{
					list($field, $adata) = explode('|', $field);
					$adata = explode('=', $adata);
					if(count($adata) > 1)
					{
						$arFieldsElement[$adata[0]] = $adata[1];
					}
				}
				$arFieldsElement[substr($field, 3)] = $arItem[$k];
				$arFieldsElementOrig[substr($field, 3)] = $arItem['~'.$k];
			}
			elseif(strpos($field, 'ICAT_PRICE')===0)
			{
				$val = $arItem[$k];
				$margins = $this->fparams[$this->worksheetNumForSave][$k]['MARGINS'];
				if(is_array($margins) && count($margins) > 0)
				{
					foreach($margins as $margin)
					{
						if((strlen(trim($margin['PRICE_FROM']))==0 || $arItem[$k] >= floatval($margin['PRICE_FROM']))
							&& (strlen(trim($margin['PRICE_TO']))==0 || $arItem[$k] <= floatval($margin['PRICE_TO'])))
						{
							$val *= (1 + ($margin['TYPE'] > 0 ? 1 : -1)*floatval($margin['PERCENT'])/100);
						}
					}
				}
				
				$arPrice = explode('_', substr($field, 10), 2);
				$arFieldsPrices[$arPrice[0]][$arPrice[1]] = $val;
			}
			elseif(strpos($field, 'ICAT_STORE')===0)
			{
				$arStore = explode('_', substr($field, 10), 2);
				$arFieldsProductStores[$arStore[0]][$arStore[1]] = $arItem[$k];
			}
			elseif(strpos($field, 'ICAT_')===0)
			{
				$arFieldsProduct[substr($field, 5)] = $arItem[$k];
			}
			elseif(strpos($field, 'IP_PROP')===0)
			{
				$arFieldsProps[substr($field, 7)] = $arItem[$k];
				$arFieldsPropsOrig[substr($field, 7)] = $arItem['~'.$k];
			}
		}

		$arUid = array();
		if(!is_array($this->params['ELEMENT_UID_SKU'])) $this->params['ELEMENT_UID_SKU'] = array($this->params['ELEMENT_UID_SKU']);
		$this->params['ELEMENT_UID_SKU'][] = 'OFFER_IP_PROP'.$OFFERS_PROPERTY_ID;
		foreach($this->params['ELEMENT_UID_SKU'] as $tuid)
		{
			$tuid = substr($tuid, 6);
			$uid = $valUid = '';
			if(strpos($tuid, 'IE_')===0)
			{
				$uid = substr($tuid, 3);
				if(strpos($uid, '|')!==false) $uid = current(explode('|', $uid));
				$valUid = $arFieldsElementOrig[$uid];
			}
			elseif(strpos($tuid, 'IP_PROP')===0)
			{
				$uid = substr($tuid, 7);
				$valUid = $arFieldsPropsOrig[$uid];
				if($arFieldsDef[$uid]['PROPERTY_TYPE']=='L')
				{
					$uid = 'PROPERTY_'.$uid.'_VALUE';
				}
				else
				{
					$uid = 'PROPERTY_'.$uid;
				}
			}
			if($uid)
			{
				$arUid[] = array(
					'uid' => $uid,
					'valUid' => $valUid
				);
			}
		}
		
		$emptyFields = array();
		foreach($arUid as $k=>$v)
		{
			if(!trim($v['valUid'])) $emptyFields[] = $v['uid'];
		}
		
		if(!empty($emptyFields) || empty($arUid))
		{
			return false;
		}
		
		$arDates = array('ACTIVE_FROM', 'ACTIVE_TO');
		foreach($arDates as $dateName)
		{
			if($arFieldsElement[$dateName])
			{
				$time = strtotime($arFieldsElement[$dateName]);
				if($time > 0)
				{
					$arFieldsElement[$dateName] = date($this->db->DateFormatToPHP(CLang::GetDateFormat("FULL")), $time);
				}
				else
				{
					unset($arFieldsElement[$dateName]);
				}
			}
		}
		
		$arPictures = array('PREVIEW_PICTURE', 'DETAIL_PICTURE');
		foreach($arPictures as $picName)
		{
			if($arFieldsElement[$picName])
			{
				$arFieldsElement[$picName] = $this->GetFileArray($arFieldsElement[$picName]);
			}
		}
		
		if(isset($arFieldsElement['ACTIVE']))
		{
			$arFieldsElement['ACTIVE'] = $this->GetBoolValue($arFieldsElement['ACTIVE']);
		}
		elseif($this->params['ELEMENT_LOADING_ACTIVATE']=='Y')
		{
			$arFieldsElement['ACTIVE'] = 'Y';
		}

		if(($this->params['ELEMENT_NO_QUANTITY_DEACTIVATE']=='Y' && isset($arFieldsProduct['QUANTITY']) && floatval($arFieldsProduct['QUANTITY'])==0)
			|| ($this->params['ELEMENT_NO_PRICE_DEACTIVATE']=='Y' && $this->IsEmptyPrice($arFieldsPrices)))
		{
			$arFieldsElement['ACTIVE'] = 'N';
		}
		
		$arKeys = array_merge(array('ID'), array_keys($arFieldsElement));
		
		$arFilter = array('IBLOCK_ID'=>$OFFERS_IBLOCK_ID);
		foreach($arUid as $v)
		{
			if(strlen($v['valUid']) != strlen(trim($v['valUid'])))
			{
				$arFilter[] = array('LOGIC'=>'OR', array($v['uid']=>trim($v['valUid'])), array($v['uid']=>$v['valUid']));
			}
			else
			{
				$arFilter[$v['uid']] = trim($v['valUid']);
			}
		}
		
		$dbRes = CIblockElement::GetList(array(), $arFilter, false, false, $arKeys);
		while($arElement = $dbRes->Fetch())
		{
			$OFFER_ID = $arElement['ID'];
			/*Delete unchanged data*/
			foreach($arFieldsElement as $k=>$v)
			{
				if($v==$arElement[$k])
				{
					unset($arFieldsElement[$k]);
				}
			}
			/*/Delete unchanged data*/
			
			$el = new CIblockElement();
			if(!empty($arFieldsElement))
			{
				if($el->Update($OFFER_ID, $arFieldsElement, false, true, true))
				{
					$this->SetTimeBegin($OFFER_ID);
				}
				else
				{
					$this->stepparams['updated_offers'][] = $OFFER_ID;
				}
			}
			else
			{
				$this->stepparams['updated_offers'][] = $OFFER_ID;
			}
			$this->SaveProduct($OFFER_ID, $arFieldsProduct, $arFieldsPrices, $arFieldsProductStores);
			$this->SaveProperties($OFFER_ID, $OFFERS_IBLOCK_ID, $arFieldsProps, (bool)(empty($arFieldsElement)));
		}
		
		if($dbRes->SelectedRowsCount()==0 && $this->params['ONLY_UPDATE_MODE']!='Y')
		{
			if(strlen($arFieldsElement['NAME'])==0)
			{
				$arFieldsElement['NAME'] = $NAME;
			}
			if($this->params['ELEMENT_NEW_DEACTIVATE']=='Y')
			{
				$arFieldsElement['ACTIVE'] = 'N';
			}
			elseif(!$arFieldsElement['ACTIVE'])
			{
				$arFieldsElement['ACTIVE'] = 'Y';
			}
			$arFieldsElement['IBLOCK_ID'] = $OFFERS_IBLOCK_ID;
			if($iblockFields['CODE']['IS_REQUIRED']=='Y' && strlen($arFieldsElement['CODE'])==0)
			{
				$arFieldsElement['CODE'] = $this->Str2Url($arFieldsElement['NAME']);
			}
			$el = new CIblockElement();
			$OFFER_ID = $el->Add($arFieldsElement, false, true, true);
			
			if($OFFER_ID)
			{
				$this->SetTimeBegin($OFFER_ID);
				$this->SaveProduct($OFFER_ID, $arFieldsProduct, $arFieldsPrices, $arFieldsProductStores);
				$this->SaveProperties($OFFER_ID, $OFFERS_IBLOCK_ID, $arFieldsProps);
			}
			else
			{
				$this->stepparams['error_line']++;
				$this->errors[] = sprintf(GetMessage("KDA_IE_ADD_OFFER_ERROR"), $el->LAST_ERROR, $this->worksheetNumForSave+1, $this->worksheetCurrentRow);
				return false;
			}
		}
	}
	
	public function GetFileArray($file, $arDef=array())
	{
		$file = trim($file);
		if(strpos($file, '/')===0)
		{
			$tmpsubdir = $this->tmpdir.($this->filecnt++).'/';
			CheckDirPath($tmpsubdir);
			$arFile = CFile::MakeFileArray($file);
			$file = $tmpsubdir.$arFile['name'];
			copy($arFile['tmp_name'], $file);
		}
		$arFile = CFile::MakeFileArray($file);
		if(strpos($arFile['type'], 'image/')===0)
		{
			$ext = ToLower(str_replace('image/', '', $arFile['type']));
			if(substr($arFile['name'], -(strlen($ext) + 1))!='.'.$ext)
			{
				if($ext!='jpeg' || (($ext='jpg') && substr($arFile['name'], -(strlen($ext) + 1))!='.'.$ext))
				{
					$arFile['name'] = $arFile['name'].'.'.$ext;
				}
			}
		}
		if(!empty($arDef))
		{
			$arFile = $this->PictureProcessing($arFile, $arDef);
		}
		return $arFile;
	}	
	
	public function SetTimeBegin($ID)
	{
		if($this->stepparams['begin_time']) return;
		$dbRes = CIblockElement::GetList(array(), array('ID'=>$ID), false, false, array('TIMESTAMP_X'));
		if($arr = $dbRes->Fetch())
		{
			$this->stepparams['begin_time'] = $arr['TIMESTAMP_X'];
		}
	}
	
	public function IsEmptyPrice($arPrices)
	{
		if(is_array($arPrices))
		{
			foreach($arPrices as $arPrice)
			{
				if($arPrice['PRICE'] > 0)
				{
					return false;
				}
			}
		}
		return true;
	}
	
	public function GetBoolValue($val)
	{
		$trueVals = array_map('trim', explode(',', GetMessage("KDA_IE_FIELD_VAL_Y")));
		$falseVals = array_map('trim', explode(',', GetMessage("KDA_IE_FIELD_VAL_N")));
		if(in_array(ToLower($val), $trueVals))
		{
			return 'Y';
		}
		elseif(in_array(ToLower($val), $falseVals))
		{
			return 'N';
		}
		else
		{
			return false;
		}
	}
	
	public function GetSections(&$arElement, $IBLOCK_ID, $SECTION_ID, $arSections)
	{
		if(empty($arSections) || !isset($arSections[1]) || count(array_diff($arSections[1], array('')))==0)
		{
			if($SECTION_ID > 0)
			{
				$arElement['IBLOCK_SECTION'] = array($SECTION_ID);
				return true;
			}
			return false;
		}
		$iblockFields = $this->iblockFields[$IBLOCK_ID];
		
		$parent = $i = 0;
		while(++$i && !empty($arSections[$i]))
		{
			if(!$arSections[$i]['NAME']) continue;
			if($arSections[$i]['NAME'] && $this->sections[$parent][$arSections[$i]['NAME']])
			{
				$parent = $this->sections[$parent][$arSections[$i]['NAME']];
			}
			else
			{
				$arFields = $arSections[$i];
				$dbRes = CIBlockSection::GetList(array(), array('NAME'=>$arFields['NAME'], 'SECTION_ID'=>$parent, 'IBLOCK_ID'=>$IBLOCK_ID), false, array('ID'));
				if($arSect = $dbRes->Fetch())
				{
					$sectId = $arSect['ID'];
					$bs = new CIBlockSection;
					$bs->Update($sectId, $arFields);
				}
				elseif($this->params['ONLY_UPDATE_MODE']!='Y')
				{
					$arFields['ACTIVE'] = 'Y';
					$arFields['IBLOCK_ID'] = $IBLOCK_ID;
					$arFields['IBLOCK_SECTION_ID'] = $parent;
					if($iblockFields['SECTION_CODE']['IS_REQUIRED']=='Y' && strlen($arFields['CODE'])==0)
					{
						$arFields['CODE'] = $this->Str2Url($arFields['NAME']);
					}
					$bs = new CIBlockSection;
					$sectId = $j = 0;
					$code = $arFields['CODE'];
					while($j<100 && !($sectId = $bs->Add($arFields)) && ($arFields['CODE'] = $code.strval(++$j))){}
					if(!$sectId)
					{
						$this->errors[] = sprintf(GetMessage("KDA_IE_ADD_SECTION_ERROR"), $arFields['NAME'], $bs->LAST_ERROR, $this->worksheetNumForSave+1, $this->worksheetCurrentRow);
					}
				}
				if(!$sectId) return false;
				$parent = $this->sections[$parent][$arSections[$i]['NAME']] = $sectId;
			}
		}
		
		if($parent > 0)
		{
			$arElement['IBLOCK_SECTION'] = array($parent);
		}
	}
	
	public function SaveProperties($ID, $IBLOCK_ID, $arProps, $needUpdate = false)
	{
		if(empty($arProps)) return false;
		if(!$this->props[$IBLOCK_ID])
		{
			$this->props[$IBLOCK_ID] = array();
			$dbRes = CIBlockProperty::GetList(array(), array('IBLOCK_ID'=>$IBLOCK_ID));
			while($arProp = $dbRes->Fetch())
			{
				$this->props[$IBLOCK_ID][$arProp['ID']] = $arProp;
			}
		}
		$propsDef = $this->props[$IBLOCK_ID];
		
		foreach($arProps as $k=>$prop)
		{
			if($propsDef[$k]['MULTIPLE']=='Y')
			{
				$arVal = explode($this->params['ELEMENT_MULTIPLE_SEPARATOR'], $prop);
				foreach($arVal as $k2=>$val)
				{
					$arVal[$k2] = $this->GetPropValue($propsDef[$k], $val);
				}
				$arProps[$k] = $arVal;
			}
			else
			{
				$arProps[$k] = $this->GetPropValue($propsDef[$k], $prop);
			}
		}
		CIBlockElement::SetPropertyValuesEx($ID, $IBLOCK_ID, $arProps);
		
		if($needUpdate && $this->params['ELEMENT_NOT_UPDATE_WO_CHANGES']!='Y')
		{
			$el = new CIblockElement();
			$el->Update($ID, array('ID'=>$ID), false, true, true);
		}
	}
	
	public function GetPropValue($arProp, $val)
	{
		if($arProp['PROPERTY_TYPE']=='F')
		{
			$picSettings = array();
			if($this->fieldSettings['IP_PROP'.$arProp['ID']]['PICTURE_PROCESSING'])
			{
				$picSettings = $this->fieldSettings['IP_PROP'.$arProp['ID']]['PICTURE_PROCESSING'];
			}
			$val = $this->GetFileArray($val, $picSettings);
		}
		elseif($arProp['PROPERTY_TYPE']=='L')
		{
			if($val)
			{
				if(!isset($this->propVals[$arProp['ID']][$val]))
				{
					$dbRes = CIBlockPropertyEnum::GetList(array(), array("PROPERTY_ID"=>$arProp['ID'], "VALUE"=>$val));
					if($arPropEnum = $dbRes->Fetch())
					{
						$this->propVals[$arProp['ID']][$val] = $arPropEnum['ID'];
					}
					else
					{
						$ibpenum = new CIBlockPropertyEnum;
						if($propId = $ibpenum->Add(array('PROPERTY_ID'=>$arProp['ID'], 'VALUE'=>$val)))
						{
							$this->propVals[$arProp['ID']][$val] = $propId;
						}
						else
						{
							$this->propVals[$arProp['ID']][$val] = false;
						}
					}
				}
				$val = $this->propVals[$arProp['ID']][$val];
			}
		}
		elseif($arProp['PROPERTY_TYPE']=='S' && $arProp['USER_TYPE']=='directory')
		{
			if($val && CModule::IncludeModule('highloadblock') && $arProp['USER_TYPE_SETTINGS']['TABLE_NAME'])
			{
				if(!isset($this->propVals[$arProp['ID']][$val]))
				{
					if(!$this->hlbl[$arProp['ID']])
					{
						$hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getList(array('filter'=>array('TABLE_NAME'=>$arProp['USER_TYPE_SETTINGS']['TABLE_NAME'])))->fetch();
						$entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
						$this->hlbl[$arProp['ID']] = $entity->getDataClass();
					}
					$entityDataClass = $this->hlbl[$arProp['ID']];
					
					$dbRes2 = $entityDataClass::GetList(array('filter'=>array("UF_NAME"=>$val), 'select'=>array('ID', 'UF_XML_ID'), 'limit'=>1));
					if($arr2 = $dbRes2->Fetch())
					{
						$this->propVals[$arProp['ID']][$val] = $arr2['UF_XML_ID'];
					}
					else
					{
						$arFields = Array(
							"UF_NAME" => $val,
							"UF_XML_ID" => $this->Str2Url($val)
						);
						$entityDataClass::Add($arFields);
						$this->propVals[$arProp['ID']][$val] = $arFields['UF_XML_ID'];
					}
				}
				$val = $this->propVals[$arProp['ID']][$val];
			}
		}
		elseif($arProp['USER_TYPE']=='DateTime')
		{
			$time = strtotime($val);
			if($time > 0)
			{
				$val = date($this->db->DateFormatToPHP(CLang::GetDateFormat("FULL")), $time);
			}
			else
			{
				$val = false;
			}
		}

		return $val;
	}
	
	public function PictureProcessing($arFile, $arDef)
	{
		if($arDef["SCALE"] === "Y")
		{
			$arNewPicture = CIBlock::ResizePicture($arFile, $arDef);
			if(is_array($arNewPicture))
			{
				$arFile = $arNewPicture;
			}
			/*elseif($arDef["IGNORE_ERRORS"] !== "Y")
			{
				unset($arFile);
				$strWarning .= GetMessage("IBLOCK_FIELD_PREVIEW_PICTURE").": ".$arNewPicture."<br>";
			}*/
		}

		if($arDef["USE_WATERMARK_FILE"] === "Y")
		{
			CIBLock::FilterPicture($arFile["tmp_name"], array(
				"name" => "watermark",
				"position" => $arDef["WATERMARK_FILE_POSITION"],
				"type" => "file",
				"size" => "real",
				"alpha_level" => 100 - min(max($arDef["WATERMARK_FILE_ALPHA"], 0), 100),
				"file" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_FILE"]),
			));
		}

		if($arDef["USE_WATERMARK_TEXT"] === "Y")
		{
			CIBLock::FilterPicture($arFile["tmp_name"], array(
				"name" => "watermark",
				"position" => $arDef["WATERMARK_TEXT_POSITION"],
				"type" => "text",
				"coefficient" => $arDef["WATERMARK_TEXT_SIZE"],
				"text" => $arDef["WATERMARK_TEXT"],
				"font" => $_SERVER["DOCUMENT_ROOT"].Rel2Abs("/", $arDef["WATERMARK_TEXT_FONT"]),
				"color" => $arDef["WATERMARK_TEXT_COLOR"],
			));
		}
		return $arFile;
	}
	
	public function SaveProduct($ID, $arProduct, $arPrices, $arStores)
	{
		if(!is_array($arProduct))
		{
			$arProduct = array();
		}
			
		if((!empty($arProduct) || !empty($arPrices) || !empty($arStores)))
		{
			$arProduct['ID'] = $ID;
		}
		
		if(empty($arProduct)) return false;
		
		if(isset($arProduct['VAT_INCLUDED']))
		{
			$arProduct['VAT_INCLUDED'] = $this->GetBoolValue($arProduct['VAT_INCLUDED']);
		}
		
		if($this->params['QUANTITY_TRACE']=='Y')
		{
			$arProduct['QUANTITY_TRACE'] = 'Y';
		}
		
		if(isset($arProduct['QUANTITY'])) $arProduct['QUANTITY'] = $this->GetFloatVal($arProduct['QUANTITY']);
		if(isset($arProduct['VAT_INCLUDED'])) $arProduct['VAT_INCLUDED'] = $this->GetBoolValue($arProduct['VAT_INCLUDED']);
		
		$dbRes = CCatalogProduct::GetList(array(), array('ID'=>$ID), false, false, array_keys($arProduct));
		while($arCProduct = $dbRes->Fetch())
		{
			/*Delete unchanged data*/
			foreach($arProduct as $k=>$v)
			{
				if($v==$arCProduct[$k])
				{
					unset($arProduct[$k]);
				}
			}
			/*/Delete unchanged data*/
			if(!empty($arProduct))
			{
				CCatalogProduct::Update($arCProduct['ID'], $arProduct);
			}
		}
		
		if($dbRes->SelectedRowsCount()==0)
		{
			CCatalogProduct::Add($arProduct);
		}
		
		if(!empty($arPrices))
		{
			$this->SavePrice($ID, $arPrices);
		}
		
		if(!empty($arStores))
		{
			$this->SaveStore($ID, $arStores);
		}
	}
	
	public function SavePrice($ID, $arPrices)
	{
		foreach($arPrices as $gid=>$arFieldsPrice)
		{
			if(!$arFieldsPrice['CURRENCY'])
			{
				$arFieldsPrice['CURRENCY'] = $this->params['DEFAULT_CURRENCY'];
			}
			$arKeys = array_merge(array('ID'), array_keys($arFieldsPrice));
			$dbRes = CPrice::GetList(array(), array('PRODUCT_ID'=>$ID, 'CATALOG_GROUP_ID'=>$gid), false, false, $arKeys);
			while($arPrice = $dbRes->Fetch())
			{
				/*Delete unchanged data*/
				foreach($arFieldsPrice as $k=>$v)
				{
					if($v==$arPrice[$k])
					{
						unset($arFieldsPrice[$k]);
					}
				}
				/*/Delete unchanged data*/
				if(!empty($arFieldsPrice))
				{
					CPrice::Update($arPrice["ID"], $arFieldsPrice);
				}
			}
			
			if($dbRes->SelectedRowsCount()==0)
			{
				$arFieldsPrice['PRODUCT_ID'] = $ID;
				$arFieldsPrice['CATALOG_GROUP_ID'] = $gid;
				CPrice::Add($arFieldsPrice);
			}
		}
	}
	
	public function SaveStore($ID, $arStores)
	{
		foreach($arStores as $sid=>$arFieldsStore)
		{
			if(isset($arFieldsStore['AMOUNT'])) $arFieldsStore['AMOUNT'] = $this->GetFloatVal($arFieldsStore['AMOUNT']);
			$dbRes = CCatalogStoreProduct::GetList(array(), array('PRODUCT_ID'=>$ID, 'STORE_ID'=>$sid), false, false, array('ID'));
			while($arPrice = $dbRes->Fetch())
			{
				CCatalogStoreProduct::Update($arPrice["ID"], $arFieldsStore);
			}
			
			if($dbRes->SelectedRowsCount()==0)
			{
				$arFieldsStore['PRODUCT_ID'] = $ID;
				$arFieldsStore['STORE_ID'] = $sid;
				CCatalogStoreProduct::Add($arFieldsStore);
			}
		}
	}
	
	public static function GetPreviewData($file, $showLines)
	{
		$file = $_SERVER['DOCUMENT_ROOT'].$file;
		$objReader = PHPExcel_IOFactory::createReaderForFile($file);
		$objReader->setReadDataOnly(true);
		$chunkFilter = new chunkReadFilter2();
		$objReader->setReadFilter($chunkFilter);
		$chunkFilter->setRows(1, max($showLines, 100));
		
		$efile = $objReader->load($file);
		//file_put_contents(dirname(__FILE__).'/test.txt', print_r($efile, true));
		$arWorksheets = array();
		foreach($efile->getWorksheetIterator() as $worksheet) 
		{
			$columns_count = PHPExcel_Cell::columnIndexFromString($worksheet->getHighestColumn());
			$rows_count = $worksheet->getHighestRow();

			$arLines = array();
			$emptyLines = 0;
			for ($row = 0; ($row < $rows_count && count($arLines) < $showLines+$emptyLines); $row++) 
			{
				$arLine = array();
				for ($column = 0; $column < $columns_count; $column++) 
				{
					$val = $worksheet->getCellByColumnAndRow($column, $row+1);
					$val = self::GetCalculatedValue($val);
					$arLine[] = $val;
				}
				$arLineTmp = array_diff($arLine, array(""));
				$arLines[] = $arLine;
				if(empty($arLineTmp))
				{
					$emptyLines++;
				}
			}
			
			$arCells = explode(':', $worksheet->getSelectedCells());
			$heghestRow = intval(preg_replace('/\D+/', '', end($arCells)));
			if($worksheet->getHighestRow() > $heghestRow) $heghestRow = intval($worksheet->getHighestRow());
			if(stripos($file, '.csv'))
			{
				$heghestRow = count(file($file));
			}

			$arWorksheets[] = array(
				'title' => self::CorrectCalculatedValue($worksheet->GetTitle()),
				'show_more' => ($row < $rows_count - 1),
				'lines_count' => $heghestRow,
				'lines' => $arLines
			);
		}
		return $arWorksheets;
	}
	
	public function GetCalculatedValue($val)
	{
		try{
			$val = $val->getCalculatedValue();
		}catch(Exception $ex){}
		return self::CorrectCalculatedValue($val);
	}
	
	public static function CorrectCalculatedValue($val)
	{
		$val = str_ireplace('_x000D_', '', $val);
		if((!defined('BX_UTF') || !BX_UTF) && CUtil::DetectUTF8($val)/*function_exists('mb_detect_encoding') && (mb_detect_encoding($val) == 'UTF-8')*/)
		{
			$val = utf8win1251($val);
		}
		return $val;
	}
	
	public function GetFloatVal($val)
	{
		return floatval(preg_replace('/[^\d\.]+/', '', $val));
	}
	
	public static function GetOfferIblock($IBLOCK_ID, $retarray=false)
	{
		if(!$IBLOCK_ID || !CModule::IncludeModule('catalog')) return false;
		$dbRes = CCatalog::GetList(array(), array('IBLOCK_ID'=>$IBLOCK_ID));
		$arFields = $dbRes->Fetch();
		if($arFields['OFFERS_IBLOCK_ID'])
		{
			if($retarray) return $arFields;
			else return $arFields['OFFERS_IBLOCK_ID'];
		}
		return false;
	}
	
	public function Str2Url($string)
	{
		/*$trans= array ('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'i', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'E', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'I', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Sch', 'Ъ' => '', 'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya');
		$string = strtr($string, $trans);

		$string = preg_replace('/[^a-zA-Z0-9\s\'\:\/\[\]-\pL]/u', '', $string);
		$string = preg_replace('/[\s\'\:\/\[\]-]+/', ' ', $string);
		$string = str_replace(array(' ', '/'), '-', $string);
		$string = ToLower($string);
		return($string);*/
		return CUtil::translit($string, LANGUAGE_ID);
	}
}

class chunkReadFilter2 implements PHPExcel_Reader_IReadFilter
{
	private $_startRow = 0;
	private $_endRow = 0;
	/**  Set the list of rows that we want to read  */

	public function setRows($startRow, $chunkSize) {
		$this->_startRow    = $startRow;
		$this->_endRow      = $startRow + $chunkSize;
	}

	public function readCell($column, $row, $worksheetName = '') {
		//  Only read the heading row, and the rows that are configured in $this->_startRow and $this->_endRow
		if (($row == 1) || ($row >= $this->_startRow && $row < $this->_endRow)) {
			return true;
		}
		return false;
	}
}	
?>