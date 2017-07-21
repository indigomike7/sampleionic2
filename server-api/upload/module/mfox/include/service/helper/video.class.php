<?php

/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Helper_Video extends Phpfox_Service
{
	public function getSimpleVideoById($iVideoId) {
		$aRow = $this->database()->select('v.*, vt.*')
								 ->from(Phpfox::getT('video'), 'v')
								 ->leftJoin(Phpfox::getT('video_text'), 'vt', 'vt.video_id = v.video_id')
								 ->where('v.video_id = ' . $iVideoId)
								 ->execute('getRow');

		$aRow['video_image_url'] =  Phpfox::getLib('image.helper')->display(array(
				'server_id' => $aRow['image_server_id'],
				'path' => 'video.url_image',
				'file' => $aRow['image_path'],
				'suffix' => '_120',
				'return_url' => true
			));
		return $aRow;
	}


	public function updateTitleAndDescription($aData, $iVideoId) {
		if(isset($aData['title']) && strlen($aData['title']) > 0) {
			$aUpdate = array(
				'title' => $this->preParse()->clean($aData['title'], 255)
			);

			$this->database()->update(Phpfox::getT('video'), $aUpdate, 'video_id = ' . $iVideoId);
		}

		if(isset($aData['description']) && strlen($aData['description']) > 0) {
			$aUpdate = array(
				'text' => $this->preParse()->clean($aData['description']),
				'text_parsed' => $this->preParse()->prepare($aData['description'])		
			);

            // support hashtags
            // since 3.08p2
            if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support') )
            {
                Phpfox::getService('tag.process')->add('video', $iVideoId, Phpfox::getUserId(), $aData['description'], true);
            }

			$this->database()->update(Phpfox::getT('video_text'), $aUpdate, 'video_id = ' . $iVideoId);
		}

	}	

	public function retrieveMoreInfo($aRow, $aResult) {
		if(Phpfox::isModule('like')) {
			list($iCnt, $aLikedUsers) = Phpfox::getService('like')->getForMembers('video', $aRow['video_id']);
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

		$aResult = array_merge($aResult, array(
				'aUserLike' => $aLikedUsersReturn, //add
				'bIsLiked' => Phpfox::isModule('like') ? Phpfox::getService('like')->didILike('video', $aRow['video_id']) : FALSE,
				'bIsRating' => $aRow['has_rated'] == NULL ? FALSE : TRUE,
				'fRating' => (float)($aRow['total_score']/2),
				'iCategory' => Phpfox::getService('video.category')->getCategoryIds($aRow['video_id']),
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
				//----
		));

		return $aResult;

	}
}
