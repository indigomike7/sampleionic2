<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Helper_Libsearch extends Phpfox_Service
{
	/**
	 * Build a search ARRAY based on the SQL driver.
	 *
	 * @param string $sType Type of search we are performing.
	 * @param mixed $mFields SQL fields to check.
	 * @param string $sSearch Search value.
	 */
	public function search($sType, $mFields, $sSearch)
	{
		if (!is_array($mFields))
		{
			$mFields = array($mFields);
		}		
		
		return ltrim(Phpfox::getLib('database')->search($sType, $mFields, Phpfox::getLib('parse.input')->clean($sSearch)), "AND");
	}

}
