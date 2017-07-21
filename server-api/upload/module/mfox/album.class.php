<?php

/**
 * @package mfox
 * @version 3.08
 */
defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Musicsharing_Album extends Phpfox_Service {

    /**
     * Use to set the size of photos.
     * @var array Photo sizes.
     */
    private $_aPhotoSizes = array(50, 120, 200);

    private $_sDefaultImageAlbumPath = '';
	

    public function __construct(){
        $this->_sDefaultImageAlbumPath = Phpfox::getParam('core.url_module') . 'mfox/static/image/music_cover_default.png';
    }

    public function getDefaultImageAlbumPath(){
        return $this->_sDefaultImageAlbumPath;
    }    


    /**
     * Input data:
     * + iAlbumId: int, required.
     *
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * + sAlbumName: string.
     * + bIsOnProfile: bool.
     * + iSongId: int.
     * + iAlbumId: int.
     * + iUserId: int.
     * + sTitle: string.
     * + sSongPath: string.
     * + iOrdering: int.
     * + iTotalPlay: int.
     * + iTimeStamp: int.
     * + sTimeStamp: string.
     * + sFullTimeStamp: string.
     * + sFullname: string.
     * + sUserImage: string.
     * + sAlbumImage: string
     * + bIsInvisible: bool.
     * + iUserLevelId: int.
     *
     */
    public function list_songs($aData)
    {
        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music_module")));
        }

        $iAlbumId = isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : 0;
        $aAlbum = Phpfox::getService('music.album')->getAlbum($iAlbumId);
        if (!isset($aAlbum['album_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_find_the_album_you_want_to_get_songs")));
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        $aSongs = Phpfox::getService('music.album')->getTracks($aAlbum['user_id'], $aAlbum['album_id'], true);        
        // Update play time for song and album.
        if (isset($aSongs[0]))
        {
            Phpfox::getService('music.process')->play($aSongs[0]['song_id']);
        }
        
        $aResult = array();
        $ordering = 0;
        foreach ($aSongs as $aSong)
        {
            $sUserImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aSong['user_server_id'],
                    'path' => 'core.url_user',
                    'file' => $aSong['user_image'],
                    'suffix' => MAX_SIZE_OF_USER_IMAGE,
                    'return_url' => true
                        )
                );

            if($aAlbum['image_path'] == null){
                $sAlbumImage = $this->_sDefaultImageAlbumPath;
            } else {
                $sAlbumImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aAlbum['server_id'],
                    'path' => 'music.url_image',
                    'file' => $aAlbum['image_path'],
                    'suffix' => '',
                    'return_url' => true
                        )
                );            
            }

            $aResult[] = array(
            	'sModelType'=>'musicsharing_song',
                'iSongId' => $aSong['song_id'],
                'iUserId' => $aSong['user_id'],
                'iAlbumId' => $aSong['album_id'],
                'sTitle' => $aSong['title'],
                'iTotalPlay' => $aSong['total_play'],
                'sSongPath' => $aSong['song_path'],
                'bIsFeatured' => (bool) $aSong['is_featured'],
                'sSongPath' => $aSong['song_path'],
                'iViewId' => $aSong['view_id'],
                'iServerId' => $aSong['server_id'],
                'iExplicit' => $aSong['explicit'],
                'sDuration' => $aSong['duration'],
                'iTimeStamp' => $aSong['time_stamp'],
                'sTimeStamp' => date("D, j M Y G:i:s O", (int)$aSong['time_stamp']),
                'sFullTimeStamp' => Phpfox::getLib('date')->convertTime($aSong['time_stamp'], null),
                'sAlbumUrl' => $aSong['album_url'],
                'sUsername' => $aSong['user_name'],
                'bIsOnProfile' => isset($aSong['is_on_profile']) ? true : false,
                'iProfileUserId' => isset($aSong['profile_user_id']) ? (int) $aSong['profile_user_id'] : 0,
                'iProfilePageId' => $aSong['profile_page_id'],
                'iUserServerId' => $aSong['user_server_id'],
                'sFullname' => $aSong['full_name'],
                'iGender' => $aSong['gender'],
                'sUserImage' => $sUserImage,
                'bIsInvisible' => (bool) $aSong['is_invisible'],
                'iUserGroupId' => $aSong['user_group_id'],
                'iUserLevelId' => $aSong['user_group_id'],
                'sAlbumImage' => $sAlbumImage,
                'iOrdering' => $ordering,
                'iLanguageId' => isset($aSong['language_id']) ? $aSong['language_id'] : 0
            );

            $ordering ++;
        }
        return $aResult;
    }

    public function field_songs($aAlbum)
    {
        
        $aSongs = Phpfox::getService('music.album')->getTracks($aAlbum['user_id'], $aAlbum['album_id'], true);        
        // Update play time for song and album.
        if (isset($aSongs[0]))
        {
            Phpfox::getService('music.process')->play($aSongs[0]['song_id']);
        }
        
        $aResult = array();
        $ordering = 0;
        foreach ($aSongs as $aSong)
        {
            $sUserImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aSong['user_server_id'],
                    'path' => 'core.url_user',
                    'file' => $aSong['user_image'],
                    'suffix' => MAX_SIZE_OF_USER_IMAGE,
                    'return_url' => true
                        )
                );

            if($aAlbum['image_path'] == null){
                $sAlbumImage = $this->_sDefaultImageAlbumPath;
            } else {
                $sAlbumImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aAlbum['server_id'],
                    'path' => 'music.url_image',
                    'file' => $aAlbum['image_path'],
                    'suffix' => '',
                    'return_url' => true
                        )
                );            
            }

            $aResult[] = array(
                'iSongId' => $aSong['song_id'],
                'sModelType'=>'musicsharing_song',
                'iUserId' => $aSong['user_id'],
                'iAlbumId' => $aSong['album_id'],
                'sTitle' => $aSong['title'],
                'iTotalPlay' => $aSong['total_play'],
                'sSongPath' => $aSong['song_path'],
                'bIsFeatured' => (bool) $aSong['is_featured'],
                'sSongPath' => $aSong['song_path'],
                'iViewId' => $aSong['view_id'],
                'iServerId' => $aSong['server_id'],
                'iExplicit' => $aSong['explicit'],
                'sDuration' => $aSong['duration'],
                'iTimeStamp' => $aSong['time_stamp'],
                'sTimeStamp' => date("D, j M Y G:i:s O", (int)$aSong['time_stamp']),
                'sFullTimeStamp' => Phpfox::getLib('date')->convertTime($aSong['time_stamp'], null),
                'iOrdering' => $ordering,
            );

            $ordering ++;
        }
        return $aResult;
    }

    /**
     * Input data:
     * + iAlbumId: int, required.
     * 
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * 
     * @param int $iAlbumId
     * @return array
     */
    public function checkPrivacyCommentOnMusicAlbum($iAlbumId)
    {
        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music_module")));
        }
        /**
         * @var array
         */
        $aAlbum = Phpfox::getService('musicsharing.music')->getAlbumInfo($iAlbumId);

        if (!isset($aAlbum['album_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_find_the_album_you_want_to_get_songs")));
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        if (!Phpfox::getService('mfox.comment')->checkCanPostCommentOnItem($aAlbum))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_this_item")));
        }

        return null;
    }

    /**
     * Input data:
     * + iAlbumId: int, required.
     * 
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * 
     * @param int $iAlbumId
     * @return array
     */
    public function checkPrivacyOnMusicAlbum($iAlbumId)
    {
        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music_module")));
        }
        /**
         * @var array
         */
        $aAlbum = Phpfox::getService('musicsharing.music')->getAlbumInfo($iAlbumId);
        if (!isset($aAlbum['album_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_find_the_album_you_want_to_get_songs")));
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        return null;
    }
    
    /**
     * Input data:
     * + iAlbumId: int, required.
     * + lastCommentIdViewed: int, optional.
     * + amountOfComment: int, optional.
     * 
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * + sImage: string.
     * + iTimestamp: int.
     * + sTime: string.
     * + sTimeConverted: string.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see album/list_comment
     * 
     * @param array $aData
     * @return array
     */
    public function list_comment($aData)
    {
        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music_module")));
        }
        /**
         * @var int
         */
        $iAlbumId = isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : 0;
        /**
         * @var array
         */
        $aAlbum = Phpfox::getService('music.album')->getAlbum($iAlbumId);
        if (!isset($aAlbum['album_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_find_the_album_you_want_to_get_songs")));
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        $aData['sType'] = 'music_album';
        $aData['iItemId'] = $iAlbumId;
        return Phpfox::getService('mfox.comment')->listallcomments($aData);
    }

    /**
     * Input data:
     * + sAction: string, optional.
     * + iLastAlbumId: int, optional.
     * + sView: string, optional. Ex: my, all
     * + iAmountOfAlbum: int, optional.
     * + sSearch: string, optional.
     *
     * Output data:
     * + bIsLiked: bool.
     * + iAlbumId: int.
     * + iUserId: int.
     * + sName: string.
     * + sImagePath: string.
     * + iTotalTrack: int.
     * + iTotalPlay: int.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + aUserLike array
     * + iTimeStamp: int.
     * + sTimeStamp: string.
     * + sFullTimeStamp: string.
     * + sFullname: string.
     * + sUserImage: string.
     * + bIsInvisible: bool.
     * + iUserLevelId: int.
     *
     */
    public function search($aData)
    {
        return $this->getAlbums($aData);
    }
	
	
	public function _getAlbumTrackCount($iAlbumId){
		$row = $this->database()->select('count(*) as total')
			->from(Phpfox::getT('m2bmusic_album_song'))
			->where('album_id=' .  $iAlbumId)
			->execute('getSlaveRow');
			
		return intval($row['total']);
		
	}
	
	
	public function _parseAlbumItem($aRow){
		
		//  get list of liked user
		
		$bIsLiked =  Phpfox::getService('mfox.helper.like')->isLiked('musicsharing_album', $aRow['album_id']);
        $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('musicsharing_album'
            , $aRow['album_id']
            , $bIsLiked
            , Phpfox::getParam('feed.total_likes_to_display'));
			
        $aUserLike = array();
        foreach($aLike['likes'] as $like){
            $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
        }
		
		// get album image URL
		$sAlbumImage = $this->getDefaultImageAlbumPath();
      	if($aRow['album_image']){
            $sAlbumImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aRow['server_id'],
                    'path' => 'music.url_image',
                    'file' => $aRow['album_image'],
                    'suffix' => '',//MAX_SIZE_OF_IMAGE_ALBUM,
                    'return_url' => true
                        )
            );
            $sAlbumImage =  str_replace('/file/pic/music/', '/file/pic/musicsharing/', $sAlbumImage);
        }

        // musicsharing does not support dislike feature
		return array(
			"bIsLiked"=> $bIsLiked,
			// "row"=>$aRow,
		    "iAlbumId"=> $aRow['album_id'],
		    "iViewId"=> "0",
		    "iPrivacy"=> "0",
		    "iPrivacyComment"=> "0",
		    "bIsFeatured"=> false,
		    "bIsSponsor"=> false,
		    "iUserId"=> $aRow['user_id'],
		    "sName"=> $aRow['title'],
		    "iYear"=> null,
		    "sImagePath"=> $sAlbumImage,
		    "iTotalTrack"=> $this->_getAlbumTrackCount($aRow['album_id']),
		    "iTotalPlay"=> $aRow['play_count'],
		    "iTotalComment"=> $aRow['total_comment'],
		    "iTotalLike"=> $aRow['total_like'],
		    "iTotalDislike"=> "0",
		    "iTotalScore"=> "0.00",
		    "iTotalRating"=> "0",
		    "iTimeStamp"=> strtotime($aRow['creation_date']), 
		    // "sTimeStamp"=> "Tuesday, September 2",
		    // "sFullTimeStamp"=> "September 2, 2014",
		    "sModuleId"=> 0,
		    'sModelType'=>'musicsharing_album',
		    "iItemId"=> $aRow['album_id'],
		    "iProfilePageId"=> $aRow['profile_page_id'],
		    "iUserServerId"=> $aRow['server_id'],
		    "sUsername"=> $aRow['user_name'],
		    "sFullname"=> $aRow['full_name'],
		    "iGender"=> $aRow['gender'],
		    "sUserImage"=> "http://product-qc.younetco.com/phpfox3demo/file/pic/user/14287_50_square.jpg",
		    "bIsInvisible"=> $aRow['is_invisible'],
		    "iUserGroupId"=> $aRow['user_group_id'],
		    "iUserLevelId"=> $aRow['user_group_id'],
		    "aUserLike"=> $aUserLike,
		    "fRating"=> 0,
		    "sDescription"=>$aRow['description'],
		    'bShowRate'=>false,
		    "iLanguageId"=> "en"
		    
		);
	}
	
    
    public function getAlbums($aData)
    {
        if (!isset($aData['iPage'])){
            $aData['iPage'] = 1;
        }
        if ((int)$aData['iPage'] == 0){
            return array();
        }

        $sOrder = (isset($aData['sOrder']) && empty($aData['sOrder']) == false) ? $aData['sOrder'] : 'latest';
        $aParentModule = array(
            'module_id' => isset($aData['sModule']) ? $aData['sModule'] : '',
            'item_id' => isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0
        );
        $user_id =  $aData['user_id']? intval($aData['user_id']):0;
        
        $sView = isset($aData['sView']) ? $aData['sView'] : 'all';
        $iLimit = (isset($aData['iAmountOfAlbum']) && (int)$aData['iAmountOfAlbum'] > 0) ? (int) $aData['iAmountOfAlbum'] : 10;
        $sSearch = isset($aData['sSearch']) ? $aData['sSearch'] : '';
		$iPage =  intval($aData['iPage']);
        $sort = "";
        $where = " search = 1 ";
        
        $oMusic = phpFox::getService('musicsharing.music');
		$prefix = phpFox::getParam(array('db', 'prefix'));
		
		if ($user_id){
			$where = " " . $prefix . "m2bmusic_album.user_id = $user_id";	
		}
        
		switch($sOrder){
			case 'latest':
				$sort = " " . $prefix . "m2bmusic_album.creation_date DESC";
				break;
			case 'most_liked':
				$sort = " " . $prefix . "m2bmusic_album.total_like DESC";
				break;
			case 'most_discussed':
			$sort = " " . $prefix . "m2bmusic_album.total_comment DESC";
				break;
			case 'most_viewed':
			default:
				$sort = " " . $prefix . "m2bmusic_album.play_count DESC";
		}
		
		if ($sSearch){
			$where .= " AND " . $prefix . "m2bmusic_album.title_url LIKE '%" . $sSearch . "%'";
		}

		if ($sView == 'my'){
			$where .= " AND " . $prefix . "m2bmusic_album.user_id = " . phpFox::getUserId();
		}
        
		$list_total = $oMusic->get_total_album($where);
		
		$select =  $prefix . "m2bmusic_album.*, " 
			. $prefix . "user.*" 
			;
		
		$list_info = $oMusic->getAlbums(($iPage - 1) * $iLimit, $iLimit, $sort, $select, $where);
		
		$aResult =  array();
		
		// return $list_info;
		
		foreach($list_info as $aRow){
			$aResult[]  = $this->_parseAlbumItem($aRow);
		}
		
        return $aResult;
    }

    /**
     * Using for notification.
     * @param array $aNotification
     * @return array|bool
     */
    public function doAlbumGetNotificationAlbum_Like($aNotification)
    {
        /**
         * @var array
         */
        $aRow = $this->database()->select('ms.album_id, ms.name, ms.user_id, u.gender, u.full_name')
                ->from(Phpfox::getT('music_album'), 'ms')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = ms.user_id')
                ->where('ms.album_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');
        
        if (!isset($aRow['album_id']))
        {
            return array();
        }
        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            $sPhrase =  Phpfox::getPhrase('music.user_name_liked_gender_own_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('music.user_name_liked_your_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('music.user_name_liked_span_class_drop_data_user_full_name_s_span_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aRow['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aRow['name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }
        return array(
            'link' => array(
                'iAlbumId' => $aRow['album_id'],
                'sAlbumTitle' => $aRow['name']
            ),
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog'),
            'sModule' => 'music.album',
            'sMethod' => 'getNotificationAlbum_Like'
        );
    }
    /**
     * Using for notification.
     * @param array $aNotification
     * @return array
     */
    public function doMusicAlbumGetCommentNotificationAlbum($aNotification)
	{
        /**
         * @var array
         */
		$aRow = $this->database()
                ->select('l.album_id, l.name, u.user_id, u.gender, u.user_name, u.full_name')	
                ->from(Phpfox::getT('music_album'), 'l')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
                ->where('l.album_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');
        
        if (!isset($aRow['album_id']))
        {
            return array();
        }
        
        /**
         * @var string
         */
		$sPhrase = '';
		if ($aNotification['user_id'] == $aRow['user_id'] && !isset($aNotification['extra_users']))
		{ 
			$sPhrase =  Phpfox::getPhrase('music.user_name_commented_on_gender_album_title',array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength ), '...')));
		}
		elseif ($aRow['user_id'] == Phpfox::getUserId())
		{
			$sPhrase =  Phpfox::getPhrase('music.user_name_commented_on_your_album_title',array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength ), '...')));
		}
		else 
		{
			$sPhrase =  Phpfox::getPhrase('music.user_name_commented_on_span_class_drop_data_user_full_name_s_album_title',array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aRow['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aRow['name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength ), '...')));
		}
		return array(
			'link' => array(
                'iAlbumId' => $aRow['album_id'],
                'sName' => $aRow['name']
            ),
			'message' => ($sPhrase),
			'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog'),
            'sModule' => 'music.album',
            'sMethod' => 'getCommentNotificationAlbum'
		);
	}
    
    public function doMusicAlbumGetRedirectCommentAlbum($iId)
	{
		return $this->getFeedRedirectAlbum($iId);
	}
    /**
     * Get feed redirect album.
     * @param int $iId
     * @return boolean|array
     */
    public function getFeedRedirectAlbum($iId)
	{
        /**
         * @var array
         */
		$aRow = $this->database()->select('m.album_id, name')
			->from(Phpfox::getT('music_album'), 'm')
			->where('m.album_id = ' . (int) $iId)
			->execute('getSlaveRow');
		if (!isset($aRow['album_id']))
		{
			return array();
		}
		return array(
            'sModule' => 'music.album',
            'iAlbumId' => $aRow['album_id'],
            'sTitle' => $aRow['name'],
            'sCommentType' => 'music_album'
        );
	}

    /**
     * Input data:
     * + sAction: string, optional.
     * + iLastAlbumId: int, optional.
     * + sView: string, optional. Ex: my, all
     * + iAmountOfAlbum: int, optional.
     * + sSearch: string, optional.
     *
     * Output data:
     * + bIsLiked: bool.
     * + iAlbumId: int.
     * + iUserId: int.
     * + sName: string.
     * + sImagePath: string.
     * + iTotalTrack: int.
     * + iTotalPlay: int.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + aUserLike array
     * + iTimeStamp: int.
     * + sTimeStamp: string.
     * + sFullTimeStamp: string.
     * + sFullname: string.
     * + sUserImage: string.
     * + bIsInvisible: bool.
     * + iUserLevelId: int.
     *
     */
    public function filter($aData)
    {
        return $this->getAlbums($aData);
    }

    /**
     * Push Cloud Message for music album.
     * @param int $iAlbumId
     */
    public function doPushCloudMessageMusicAlbum($aData)
    {
        /**
         * @var int
         */
        $iAlbumId = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;
        /**
         * @var array
         */
        $aAlbum = Phpfox::getService('music.album')->getAlbum($iAlbumId);
        
        if (isset($aAlbum['user_id']) && $aAlbum['user_id'] != Phpfox::getUserId())
        {
            /**
             * @var int
             */
            $iPushId = Phpfox::getService('mfox.push')->savePush($aData, $aAlbum['user_id']);
            
            Phpfox::getService('mfox.cloudmessage') -> send(array('message' => 'notification', 'iPushId' => $iPushId), $aAlbum['user_id']);
        }
    }
    
    /**
     * Input data:
     * + iAlbumId: string, required.
     *
     * Output data:
     * + bIsLiked: bool.
     * + iAlbumId: int.
     * + iUserId: int.
     * + sAlbumName: string.
     * + sDescription: string.
     * + sImagePath: string.
     * + iTotalTrack: int.
     * + iTotalPlay: int.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + aUserLike array
     * + iTimeStamp: int.
     * + sTimeStamp: string.
     * + sFullTimeStamp: string.
     * + sFullname: string.
     * + sUserImage: string.
     * + bIsInvisible: bool.
     * + iUserLevelId: int.
     * + bIsFriend: bool.
     * + bCanPostComment: bool.
     *
     */
    public function detail($aData)
    {
    	
		$oMusic = phpFox::getService('musicsharing.music');
		$prefix = phpFox::getParam(array('db', 'prefix'));
		
		$iAlbumId = intval($aData['iAlbumId']);
		
		$aAlbum = Phpfox::getLib('database')
			->select('*')
			->from(Phpfox::getT('m2bmusic_album'))
			->where('album_id='. $iAlbumId)
			->execute('getSlaveRow');
		
		if($aAlbum && $aAlbum['module_id'] && $aAlbum['item_id']){
			phpFox::getLib('session')->set('pages_msf', array(
				'module_id' => $aAlbum['module_id'],
				'item_id'=>$aAlbum['item_id'] 
			));
		}
		
       	$select =  $prefix . "m2bmusic_album.*, " 
			. $prefix . "user.*" 
			;
		
		$where = $prefix . "m2bmusic_album.album_id=".$iAlbumId;
		
		$list_info = $oMusic->getAlbums(0, 1, "", $select, $where);
		
		if (empty($list_info)){
			return array(
				'error_code'=>1,
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_find_this_album")),
			);
		}
		
		$aResult = $this->_parseAlbumItem($list_info[0]);
		
		$aResult['aSongs'] =  $this->__field_songs($aResult['iAlbumId']);
		
		
      	return $aResult;
    }
	
	function getAlbumSummary($iAlbumId){
		$oMusic = phpFox::getService('musicsharing.music');
		$prefix = phpFox::getParam(array('db', 'prefix'));
		
       	$select =  $prefix . "m2bmusic_album.*, " 
			. $prefix . "user.*" 
			;
		
		$where = $prefix . "m2bmusic_album.album_id=".$iAlbumId;
		
		$list_info = $oMusic->getAlbums(0, 1, "", $select, $where);
		
		return $aResult =  $this->_parseAlbumItem($list_info[0]);
		
		
	}
	
	function __field_songs($iAlbumId){
		$oMusic = phpFox::getService('musicsharing.music');
		$prefix = phpFox::getParam(array('db', 'prefix'));
		
		$where = $prefix . "m2bmusic_album_song.album_id=".$iAlbumId;
		
		$select = "" . $prefix . "m2bmusic_album_song.*, " . $prefix . "m2bmusic_album.album_image as album_image_path, " . $prefix . "m2bmusic_album.server_id as album_server_id, " . $prefix . "m2bmusic_album.album_id, UNIX_TIMESTAMP(" . $prefix . "m2bmusic_album.creation_date) as `_creation_date`, " . $prefix . "m2bmusic_album.is_download, " . $prefix . "m2bmusic_album.title as album_title, " . $prefix . "m2bmusic_singer.title as singer_title, " . $prefix . "m2bmusic_singer.singer_id," . $prefix . "user.*," . $prefix . "m2bmusic_category.title as cat_title";
		
		$aSongs = phpFox::getService('musicsharing.music')->getSongs(0, 100, "", $select, $where . " AND search = 1");
		
		$aResult = array();
		
		foreach($aSongs as $aRow){
				
			$sSongPath = Phpfox::getParam('music.url'). $aRow['url'];
			
			$sSongPath =  str_replace('/file/music/', '/file/musicsharing/', $sSongPath); 
			
			$aResult[] =  array(
				'iSongId' => $aRow['song_id'],
				'sTitle'=>$aRow['title'],
				'iAlbumId'=>$aRow['album_id'],
				'sSongPath' => $sSongPath,
			);
		}
		
		return $aResult;
	}
	
	

    // -----------------------------------------------------------
    // VERSION 3.03
    // -----------------------------------------------------------
    public function getAlbumByID($albumID){
        return $this->database()->select('ma.*, mat.text, mat.text_parsed')
            ->from(Phpfox::getT('music_album'), 'ma')
            ->join(Phpfox::getT('music_album_text'), 'mat', 'mat.album_id = ma.album_id')
            ->where('ma.album_id = ' . (int) $albumID)
            ->execute('getSlaveRow');            
    }

    /**
     * Add song into album
     * Note that: song and album existed in server
     * 
     * Input data:
     * + iAlbumId: int, optional.
     * + iSongId: int, optional.
     *
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * + error_message: string.
     *
     */
    public function addsong($aData)
    {
        //  init 
        if (!Phpfox::getUserParam('music.can_upload_music_public'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_add_song")));
        }

        $iAlbumId = isset($aData['iAlbumId']) ? $aData['iAlbumId'] : 0;
        $aAlbum = $this->getAlbumByID($aData['iAlbumId']);
        $iSongId = isset($aData['iSongId']) ? $aData['iSongId'] : 0;
        $aSong = Phpfox::getService('mfox.song')->getSongByID($aData['iSongId']);
        if(!isset($aAlbum['album_id']) || !isset($aSong['song_id'])){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.album_or_song_does_not_exists")));
        }

        if($this->isSongInAlbum($iAlbumId, $iSongId) == true){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.this_album_already_has_this_song")));
        }

        // process
        $this->database()->update(Phpfox::getT('music_song')
            , array(
                'album_id' => $iAlbumId
                , 'privacy' => $aAlbum['privacy']
                , 'privacy_comment' => $aAlbum['privacy_comment']
            )
            , 'song_id = ' . (int)$iSongId
        );
        if (!Phpfox::getUserParam('music.music_song_approval'))
        {                   
            $this->database()->updateCounter('music_album', 'total_track', 'album_id', $iAlbumId);
        }           

        // end 
        return array(
            'result' => 1,
            'message'=>html_entity_decode(Phpfox::getPhrase("mfox.song_successfully_added"))
        );
    }    

    public function getSongByAlbumID($albumID){
        return $this->database()->select('*')
            ->from(Phpfox::getT('music_song'))
            ->where('album_id = ' . (int) $albumID)
            ->execute('getSlaveRow');            
    }

    public function isSongInAlbum($albumID, $songID){
        $ret = $this->database()->select('song_id')
            ->from(Phpfox::getT('music_song'))
            ->where('album_id = ' . (int) $albumID . ' AND song_id = ' . (int) $songID)
            ->execute('getSlaveRow');            

        if(isset($ret['song_id']) && (int)$ret['song_id'] > 0){
            return true;
        } 

        return false;
    }


    /**
     * Input data:
     * + sType : string, required.
     *
     * Output data:
     * - sPrivacyValue
     * - sPrivacyName
     *
     */
    public function privacy($aData){
        $sType = isset($aData['sType']) ? $aData['sType'] : 'view';
        switch ($sType)
        {
            case 'view' :
                return Phpfox::getService('mfox.privacy')->privacy(array());
                break;
            case 'comment' :
                return Phpfox::getService('mfox.privacy')->privacycomment(array());
                break;
        }

        return array('result' => 0
            , 'error_code' => 1
            , 'error_message' => 'Not support type ' . $sType . ' yet!');
    }

    public function fetch($aData){
        if(isset($aData['iMaxId'])){
            $aData['iLastAlbumId'] = $aData['iMaxId'];
        }
        
        return $this->getAlbums($aData);
    }
    
}

