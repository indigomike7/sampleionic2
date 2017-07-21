<?php

/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Helper_Rate extends Phpfox_Service
{
	public function rate($aData) {
		$sItemType = $aData['sItemType'];
		$iItemId = $aData['iItemId'];
		$iRating = $aData['iRating']; 

		$aRate = array(
			'type' => $sItemType,
			'item_id' => $iItemId,
			'star' => $iRating,
		);
		return $this->add($aRate);
	}

	public function add($aRating)
	{
		if(Phpfox::isUser() == false){
			return array(
					'error_code' => 2,
					'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_rate_this_item"))
			);			
		}
		
		if (!is_array($aRating))
		{
			return Phpfox_Error::set(Phpfox::getPhrase('mfox.not_a_valid_post'));
		}
		
		$sModule = $aRating['type'];
		$sExtra = '';
		if (strpos($aRating['type'], '_'))
		{
			$aParts = explode('_', $aRating['type']);
			$sModule = $aParts[0];
			$sExtra = ucfirst($aParts[1]);
		}
		
		$aCallback = Phpfox::callback($sModule . '.getRatingData' . $sExtra, $aRating['item_id']);
				
		$aRow = $this->database()->select($aCallback['field'] . ', user_id')
			->from(Phpfox::getT((isset($aCallback['check_table']) ? $aCallback['check_table'] : $aCallback['table'])))
			->where($aCallback['field'] . ' = ' . (int) $aRating['item_id'])
			->execute('getSlaveRow');
			
		if (!isset($aRow[$aCallback['field']]))
		{
			return Phpfox_Error::set(Phpfox::getPhrase('mfox.not_a_valid_item_to_rate'));
		}
		
		// @todo: review this business rule
		if ($aRow['user_id'] == Phpfox::getUserId())
		{
			return Phpfox_Error::set(Phpfox::getPhrase('mfox.sorry_you_are_not_able_to_rate_your_own_item'));
		}
		
		$iIsRated = $this->database()->select('COUNT(*)')
			->from(Phpfox::getT($aCallback['table_rating']))
			->where('item_id = ' . (int) $aRating['item_id'] . ' AND user_id = ' . Phpfox::getUserId())
			->execute('getSlaveField');
			
		if (!$iIsRated)
		{
			$aParts = explode('|', $aRating['star']);
			$iId = $this->database()->insert(Phpfox::getT($aCallback['table_rating']), array(
					'item_id' => $aRating['item_id'],
					'user_id' => Phpfox::getUserId(),
					'rating' => (int) $aParts[0],
					'time_stamp' => PHPFOX_TIME
				)
			);	
			
			$aAverage = $this->database()->select('COUNT(*) AS count, AVG(rating) AS average_rating')
				->from(Phpfox::getT($aCallback['table_rating']))
				->where('item_id = ' . (int) $aRating['item_id'])
				->execute('getRow');

			$this->database()->update(Phpfox::getT($aCallback['table']), array(
					'total_score' => round($aAverage['average_rating']),
					'total_rating' => $aAverage['count']
				), $aCallback['field'] . ' = ' . (int) $aRating['item_id']
			);
			
			return $iId;
		}
		
		return Phpfox_Error::set(Phpfox::getPhrase('mfox.you_have_already_voted_on_this_item'));
	}

	public function ratingPhoto($aData) {
		if(Phpfox::getUserParam('photo.can_view_photos') == false
			|| Phpfox::getParam('photo.can_rate_on_photos') == false
			){
			return array(
					'error_code' => 2,
					'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_rate_this_photo"))
			);
		}
		$iIsRated = $this->isRatedPhoto($aData['iPhotoId'], Phpfox::getUserId());
		if($iIsRated == false){
			$ret = Phpfox::getService('photo.rate.process')->add($aData['iPhotoId'], $aData['iRating']);
			if($ret == true){
				$ratingData = $this->getRatingDataPhoto($aData['iPhotoId']);
				if($ratingData === false){
					return array(
						'error_code' => 4,
						'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.rating_failed"))
					);				
				} else {
					return $ratingData;
				}
			} else {
				return array(
					'error_code' => 4,
					'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.rating_failed"))
				);				
			}
		}

		return array(
			'error_code' => 3,
			'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_have_already_rated_this_item"))
		);
	}

	public function isRatedPhoto($photoID, $userID = null){
		if(null == $userID){
			$userID = Phpfox::getUserId();
		}

		$iIsRated = $this->database()->select('COUNT(p.rating_id)')
			->from(Phpfox::getT('photo_rating'), 'p')
			->where('p.photo_id = ' . (int) $photoID . ' AND p.user_id = ' . (int)$userID)
			->execute('getSlaveField');
		if (!$iIsRated){
			return false;
		}

		return true;
	}

	public function getRatingDataPhoto($iPhotoId){
		$aPhoto = $this->database()->select('p.photo_id, p.total_vote, AVG(pr.rating) AS average_rating')
			->from(Phpfox::getT('photo'), 'p')
			->innerJoin(Phpfox::getT('photo_rating'), 'pr', 'pr.photo_id = p.photo_id')
			->where('p.photo_id = ' . (int) $iPhotoId)
			->group('p.photo_id')
			->execute('getRow');

		if (!isset($aPhoto['photo_id']))
		{
			return false;
		}

		return array(
				'error_code' => 0,
				'fRating' => (float)($aPhoto['average_rating']/2),
				'iTotal' => ($aPhoto['total_vote'])
		);		
	}

	public function ratingSong($aData){
		$aRate = array(
			'type' => 'music_song',
			'item_id' => $aData['iSongId'],
			'star' => $aData['iRating'],
		);
		$ret = $this->add($aRate);		
		if(!$ret) {
			$errors = Phpfox_Error::get();
			if(is_array($errors) == true && count($errors) > 0){
				return array( 
					'error_code' => 1,
					'error_message' => $errors[0]
				);				
			} else {
				return array( 
					'error_code' => 1,
					'error_message' => $errors
				);				
			}
		} else {
			$aSong = Phpfox::getService('mfox.song')->getSongByID($aData['iSongId']);
			return array(
				'error_code' => 0,
				'iTotal' => $aSong['total_rating'],
				'fRating' => (float) ($aSong['total_score']/2)
			);
		}		
	}
	
	function ratingMusicSharingAlbum($aData){
		return array(
			'error_code'=>1,
			'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.music_sharing_does_not_support_rating")),
		);
	}
	
	function musicsharingAlbumSongCaculateRating($iSongId){
		$votes = Phpfox::getService('musicsharing.music')->getVoteBySongId($iSongId);
		
		$value = 1;
		$total_score = 0;
		$total_counter  = 0;
		foreach($votes as $vote){
			$total_counter +=1;
			$total_score += intval($vote['rating']);
		}
		
		if($total_counter){
			return $total_score * 1.0/  $total_counter / 2.0;
		}
		
		return 0.0;
	}

	function ratingMusicSharingAlbumSong($aData){
		$songid = $aData['iSongId'];
		$vote =  $aData['iRating']; 
		$result = phpFox::getService('musicsharing.music')->voteSong($songid, $vote);
		
		if ($result){
			return array(
				'user_id'=>Phpfox::getUserId(),
				'item_id'=> $songid,
				'item_type'=>'musicsharing_song',
				'iTotal' =>1,
				'fRating'=> $this->musicsharingAlbumSongCaculateRating($songid),
			);
		}else{
			return array(
				'error_code'=>1,
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.could_not_rate_this_song")),
			);
		}
	}

	public function ratingMusicAlbum($aData){
		$aRate = array(
			'type' => 'music_album',
			'item_id' => $aData['iAlbumId'],
			'star' => $aData['iRating'],
		);
		$ret = $this->add($aRate);		
		if(!$ret) {
			$errors = Phpfox_Error::get();
			if(is_array($errors) == true && count($errors) > 0){
				return array( 
					'error_code' => 1,
					'error_message' => $errors[0]
				);				
			} else {
				return array( 
					'error_code' => 1,
					'error_message' => $errors
				);				
			}
		} else {
			$aAlbum = Phpfox::getService('mfox.album')->getAlbumByID($aData['iAlbumId']);
			return array(
				'error_code' => 0,
				'iTotal' => $aAlbum['total_rating'],
				'fRating' => (float) ($aAlbum['total_score']/2)
			);
		}		
	}

	public function ratingUltimateVideo($aData){
		if(Phpfox::getService('ultimatevideo.rating')->add(Phpfox::getUserId(),$aData['iVideoId'], (float)$aData['iRating']/2)){
			$aVideo = Phpfox::getService('ultimatevideo')->getVideo($aData['iVideoId']);
			return array(
				'error_code' => 0,
				'iTotal' => $aVideo['total_rating'],
				'fRating' => (float) ($aVideo['rating'])
			);
		}else{
			$errors = Phpfox_Error::get();
			if(is_array($errors) == true && count($errors) > 0){
				return array( 
					'error_code' => 1,
					'error_message' => $errors[0]
				);				
			} else {
				return array( 
					'error_code' => 1,
					'error_message' => $errors
				);				
			}
		}
	}

}
