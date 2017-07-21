<?php
/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');
/**
 * @author ductc@younetco.com
 * @package mfox
 * @subpackage mfox.service
 * @version 3.01
 * @since May 31, 2013
 * @link Mfox Api v1.0
 */
class Mfox_Service_Auth extends Phpfox_Service
{
    /**
     * Check the authentication to access.
     * @param string $sTable
     * @param string $sField
     * @param int $iId
     * @param string $sUserPerm
     * @param string $sGlobalPerm
     * @param int $iUserId
     * @return boolean
     */
    public function hasAccess($sTable, $sField, $iId, $sUserPerm, $sGlobalPerm, $iUserId = null)
	{
        /**
         * @var bool
         */
		$bAccess = false;

		if (Phpfox::isUser())
		{
			if ($iUserId === null)
			{
				$iUserId = $this->database()->select('u.user_id')
				->from(Phpfox::getT($sTable), 'a')
				->join(Phpfox::getT('user'), 'u', 'u.user_id = a.user_id')
				->where('a.' . $sField . ' = ' . (int) $iId)
				->execute('getSlaveField');

				if (!$iUserId)
				{
					$bAccess = false;
				}
			}

			if ($iUserId && Phpfox::getUserId() == $iUserId && Phpfox::getUserParam($sUserPerm))
			{
				$bAccess = $iUserId;
			}

			if ($iUserId && Phpfox::getUserParam($sGlobalPerm))
			{
				$bAccess = $iUserId;
			}
		}

		if ($bAccess === false && PHPFOX_IS_AJAX)
		{
			return false;
		}

		return $bAccess;
	}
    
}
