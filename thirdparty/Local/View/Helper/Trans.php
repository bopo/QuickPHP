<?php
/**
 * View plugin for special string translate
 */
class View_Helper_Trans
{
	protected $translate;
	
	public function __construct()
	{
		$sws = Config_Sws::getInstance();
		$this->translate = $sws->getTranslate();
	}

	/**
	 * Translate string
	 *
	 * @return sting
	 */
	public function trans($str)
	{
		return $this->translate->_($str);
	}
}
