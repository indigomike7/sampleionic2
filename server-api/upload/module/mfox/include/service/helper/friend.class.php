<?php

/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Helper_Friend extends Phpfox_Service {
    public function delete($iRequestingUserId, $iRequested) {
        $this->database()->detele(Phpfox::getT('friend_request'), 'user_id = ' . $iRequested . ' AND friend_user_id = ' . $iRequestingUserId);
    }

    public function cancelRequest($iRequestingUserId, $iRequested) {
        return $this->database()->delete(Phpfox::getT('friend_request'), 'user_id = ' . $iRequested . ' AND friend_user_id = ' . $iRequestingUserId);
    }

    /**
	 * Denies a friend request
	 */ 
	public function deny($iUserId, $iFriendId)
	{		
		$aRow = $this->database()->select('fr.request_id, fr.user_id, fr.friend_user_id, u.user_name')
			->from(Phpfox::getT('friend_request'), 'fr')
			->join(Phpfox::getT('user'), 'u', 'u.user_id = fr.friend_user_id')
			->where('fr.user_id = ' . (int) $iUserId . ' AND fr.friend_user_id = ' . (int) $iFriendId)
			->execute('getRow');
			
		if (isset($aRow['user_id']))
		{
			$this->database()->delete(Phpfox::getT('friend_request'), 'user_id = ' . (int)$iUserId . ' AND friend_user_id = ' . (int)$iFriendId);
			return false;
		}		
		
		$this->database()->update(Phpfox::getT('friend_request'), array('is_ignore' => 1), 'user_id = ' . (int) $iUserId . ' AND friend_user_id = ' . (int) $iFriendId);
		
		(Phpfox::isModule('request') ? Phpfox::getService('request.process')->delete('friend_request', $aRow['request_id'], $iUserId) : false);
		
		if (Phpfox::getParam('friend.enable_friend_suggestion'))
		{
			Phpfox::getService('friend.suggestion')->reBuild($iUserId);
			Phpfox::getService('friend.suggestion')->reBuild($iFriendId);
		}		
		
		return true;
	}
}
