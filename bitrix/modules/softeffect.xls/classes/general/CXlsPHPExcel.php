<?
/**
 * Adapter dlya PHPExcel biblioteki
 */
 
class CXlsPHPExcel {
	private $_file = "";
	private $_reader = null;
	private $_charset = 'cp1251';
	
	public function CXlsPHPExcel($file, $XLS_FIRST_ROW=1, $chunkSize=100) {
		
		PHPExcel_Settings::setLocale("ru_RU");
		
		// ispravim bag bitriksa pri nalichie v nazvanii probelov: ispravim %20 na probely
		$file = str_replace('%20', ' ', $file);

		$this->_file = $_SERVER['DOCUMENT_ROOT'].'/'.$file;
		
		// Proverka na validnost' Excel fayla
		$inputFileType = 'Excel2007';
		$reader = new PHPExcel_Reader_Excel2007();
		if (!$reader->canRead($this->_file)) {
			$inputFileType = 'Excel5';
			$reader = new PHPExcel_Reader_Excel5();
			if (!$reader->canRead($this->_file)) {
				throw new Exception(GetMessage('ERROR_FORMAT'));
			}
		}
		
		$this->_reader = PHPExcel_IOFactory::createReader($inputFileType);
		
		if (intval($chunkSize)>0) {
			$chunkFilter = new chunkReadFilter();
			$chunkFilter->setRows((int)$XLS_FIRST_ROW, $chunkSize);
			$this->_reader->setReadFilter($chunkFilter);
		}
		$this->_reader->setReadDataOnly(true);
		$this->_reader = $this->_reader->load($this->_file);
		
		//$this->_reader = PHPExcel_IOFactory::load($this->_file);
		
		// Opredelim tekuschuyu kodirovku
		$arSite = CSite::GetList($by="sort", $order="desc", array())->getNext();
		if ($arSite) {
			$this->_charset = $arSite['CHARSET'];
		}
	}
	
	public function setCharset($charset) {
		$this->_charset = $charset;
	}

	public function iconv($input) {
		return iconv('utf-8', $this->_charset, $input);
	}
	
	// Vozvraschaet massiv Listov knigi Excel
	public function getAllSheets() {
		return $this->_reader->getAllSheets();
	}
	
	public function getSheet($index) {
		return $this->_reader->getSheet($index);
	}
}