<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Helper_Ban extends Phpfox_Service {

	private function _flatten($aArr)
	{
		if (!is_array($aArr))
		{
			return $aArr;
		}
		$sStr = '';
		foreach ($aArr as $aA)
		{
			$sStr .= $this->_flatten($aA) . ' ';
		}
		return $sStr;
	}

	public function checkAutomaticBan($sValue)
	{
			/* Extra protection for admins so they dont get banned automatically. */
			if (Phpfox::isAdmin() || empty($sValue))
			{
				return true;
			}
			if (is_array($sValue))
			{
				$sValue = $this->_flatten($sValue);
			}
			$aFilters = $this->database()->select('*')
							->from(Phpfox::getT('ban'))
							->where('type_id = "word"')
							->execute('getRows');

			foreach ($aFilters as $iKey => $aFilter)
			{
				$aUserGroupsAffected = unserialize($aFilter['user_groups_affected']);

				if (is_array($aUserGroupsAffected) && !empty($aUserGroupsAffected) && in_array(Phpfox::getUserBy('user_group_id'), $aUserGroupsAffected) == false)
				{
					continue;
				}

				$sFilter = ''.str_replace('&#42;', '*', $aFilter['find_value']) .'';
				//$sFilter = str_replace(array(' *', '* '),'*', $sFilter);
				
				$bBan = false;
				$sFilter = str_replace("/", "\/", $sFilter);
				$sFilter = str_replace('&#42;', '*', $sFilter);
				if (preg_match('/\*/i', $sFilter))
				{
					$sFilter = str_replace(array('.', '*'), array('\.', '(.*?)'), $sFilter);

					$bBan = preg_match('/' . $sFilter . '/is', $sValue);
				}
				else
				{
					$bBan = preg_match("/(\W)". $sFilter ."(\W)/i", $sValue);
					if (!$bBan)
					{
						$bBan = preg_match("/^". $sFilter ."(\W)/i", $sValue);
					}
					if (!$bBan)
					{
						$bBan = preg_match("/(\W)". $sFilter ."$/i", $sValue);
					}
					if (!$bBan)
					{
						$bBan = preg_match("/^". $sFilter ."$/i", $sValue);
					}

				}
				if ($bBan)
				{
                                    
					if ($aFilter['days_banned'] === null)
					{
						return true;
					}
					$this->database()->insert(Phpfox::getT('ban_data'), array(
						'ban_id' => $aFilter['ban_id'],
						'user_id' => Phpfox::getUserId(),
						'start_time_stamp' => PHPFOX_TIME,
						'end_time_stamp' => $aFilter['days_banned'] > 0 ? PHPFOX_TIME + ($aFilter['days_banned'] * 86400) : 0,
						'return_user_group' => $aFilter['return_user_group'],
						'reason' => $aFilter['reason']
					));
					define('PHPFOX_USER_IS_BANNED', true);
					$aFilter['reason'] = str_replace('&#039;', "'", $aFilter['reason']);
					$sReason = preg_replace('/\{phrase var=\'(.*)\'\}/ise', "'' .  Phpfox::getPhrase('\\1',array(), false, null, '" . Phpfox::getUserBy('language_id') . "') . ''", $aFilter['reason']);

					$this->database()->update(Phpfox::getT('user'),
							array('user_group_id' => Phpfox::getParam('core.banned_user_group_id'))
							, 'user_id = ' . (int) Phpfox::getUserId());

					// Phpfox::getService('user.auth')->logout();
					if (defined('PHPFOX_IS_AJAX') && PHPFOX_IS_AJAX)
					{						
						// echo 'alert("' . $sReason . '");';
						// echo 'window.location.reload(true);';
					}
					else
					{
						// Phpfox::getLib('url')->send('', array(), $sReason);
					}
					return $sReason;
				}
			}
			return true;
	}	

}
