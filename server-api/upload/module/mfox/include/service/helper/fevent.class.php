<?php

/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Helper_Fevent extends Phpfox_Service
{
	public function getNumberOfGuestOfEvent($iEventId, $rsvp_id = 1) {
		// at current, we just get attending + may-attending member ONLY
		$iCnt = $this->database()->select('COUNT(invite_id) ')
			 ->from(Phpfox::getT('fevent_invite'))
			 ->where('event_id  = ' . $iEventId . ' AND (rsvp_id = 1 OR rsvp_id = 2) ')
			 ->execute('getSlaveField');

		return $iCnt;


	}

	public function getPhotosOfEvent($iEventId) {
		$aRows = $this->database()->select('p.*, u.*')
			 ->from(Phpfox::getT('photo'), 'p')
			 ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
			 ->where('p.parent_user_id = ' . $iEventId . ' AND p.module_id = \'fevent\' ')
			 ->execute('getRows');

		// foreach($aRows as &$aRow) {
		// 	$aRow['sImageUrl'] = Phpfox::getParam('photo.url_photo') . $aRow['destination'];
		// 	$aRow['sUserImageUrl'] = Phpfox::getParam('core.url_user') . $aInvite['user_image'];
		// }

		return $aRows;
	}

	public function getInvitedUserIds($iEventId) {
		$aRows = $this->database()->select('invited_user_id')
			 ->from(Phpfox::getT('fevent_invite'))
			 ->where('event_id  = ' . $iEventId)
			 ->execute('getRows');
		$aIds = array(Phpfox::getUserId());
		foreach($aRows as $aRow) {
			$aIds[] = $aRow['invited_user_id'];
		}

		return $aIds;	

	}

}
