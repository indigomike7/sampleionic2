<?php

/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Helper_Ultimatevideo extends Phpfox_Service
{
	public function retrieveMoreInfo($aRow, $aResult) {

		if(Phpfox::isModule('like')) {
			list($iCnt, $aLikedUsers) = Phpfox::getService('like')->getForMembers('ultimatevideo_video', $aRow['video_id']);
			$aLikedUsersReturn = array();

			foreach ($aLikedUsers as $aUser) {
				$aLikedUsersReturn[] = array(
					'iUserId' => $aUser['user_id'],
					'sDisplayName' => $aUser['full_name']
				);
			}
		} else {
			$aLikedUsersReturn = array();
		}

		if(!isset($aRow['duration']) 
			|| null == $aRow['duration']
			|| empty($aRow['duration']) == true
			){
			$iDuration = '00:00';
		} else {
			$iDuration = $aRow['duration'];
		}

		$bIsFavourite = Phpfox::getService('ultimatevideo.favorite')->isFavorite(Phpfox::getUserId(),$aRow['video_id']);
		return $aRow;

		$aResult = array_merge($aResult, array(
				'aUserLike' => $aLikedUsersReturn, //add
				'bIsLiked' => Phpfox::isModule('like') ? Phpfox::getService('like')->didILike('videochannel', $aRow['video_id']) : FALSE,
				'bIsRating' => $aRow['has_rated'] == NULL ? FALSE : TRUE,
				'fRating' => (float)($aRow['total_score']/2),
				'iCategory' => Phpfox::getService('videochannel.category')->getCategoryIds($aRow['video_id']),
				'iDuration' => $iDuration,
				'iRatingCount' => $aRow['total_rating'],
				'iUserLevelId' => $aRow['user_group_id'],
				'iParentId' => 'Not Implement Yet',
				'sCode' => 'Not Implement Yet',
				'sParentType' => 'Not Implement Yet',
				'sDescription' => $aRow['text'],
				'sFullTimeStamp' => Phpfox::getLib('date')->convertTime($aRow['time_stamp'], null),
				'sTimeStamp' => $aRow['time_stamp'],
				'iTimeStamp' => $aRow['time_stamp'],
                'bInProcess' => (int)$aRow['in_process'] > 0 ? true : false,
                'bIsFavourite' => $bIsFavourite,
				//----
		));

		return $aResult;

	}
}
