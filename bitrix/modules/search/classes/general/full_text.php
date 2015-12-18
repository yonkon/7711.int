<?
class CSearchFullText
{
	/**
	 * @var CSearchFullText
	 */
	protected static $instance = null;
	/**
	 * Returns current instance of the full text indexer.
	 *
	 * @return CSearchFullText
	 */
	public static function getInstance()
	{
		if (!isset(static::$instance))
		{
			if ( COption::GetOptionString("search", "full_text_engine") === "sphinx")
			{
				self::$instance = new CSearchSphinx;
				self::$instance->connect(
					COption::GetOptionString("search", "sphinx_connection"),
					COption::GetOptionString("search", "sphinx_index_name")
				);
			}
			else
			{
				self::$instance = new CSearchStemTable();
			}
		}
		return static::$instance;
	}
	public function connect($connectionString)
	{
		return true;
	}
	public function truncate()
	{
	}
	public function deleteById($ID)
	{
	}
	public function replace($ID, $arFields)
	{
	}
	public function update($ID, $arFields)
	{
	}
	public function search($arParams, $aSort, $aParamsEx, $bTagsCloud)
	{
//		$obTitle = new CSearchTitle;
//		$obTitle->setMinWordLength($_REQUEST["l"]);
//		$obTitle->_arPhrase[$arParams['QUERY']] = 0;
//		return $obTitle->searchTitle($arParams['QUERY'], 5, $arParams,  false, "");
		return false;
	}
	function searchTitle($phrase = "", $arPhrase = array(), $nTopCount = 5, $arParams = array(), $bNotFilter = false, $order = "")
	{
//		$obTitle = new CSearchTitle;
//		$obTitle->setMinWordLength($_REQUEST["l"]);
//		return $obTitle->searchTitle($phrase, $arParams, $nTopCount, $bNotFilter, $order);
		return false;
	}
	public function getErrorText()
	{
		return "";
	}
	public function getErrorNumber()
	{
		return 0;
	}
}
?>
