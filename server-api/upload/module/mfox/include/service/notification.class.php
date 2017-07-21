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
 * @since May 21, 2013
 * @link Mfox Api v2.0
 */
class Mfox_Service_Notification extends Phpfox_Service {

    /**
     * Callback of notification.
     * @staticvar array $aModules
     * @param string $sCall
     * @param array $aParams
     * @return array
     */
    private function callback($sCall, $aParams = array())
    {
        static $aModules = array();

        // Lets get the module and method we plan on calling
        $aParts1 = explode('.', $sCall);
        $sModule = $aParts1[0];
        $sMethod = $aParts1[1];

        if (strpos($sModule, '_'))
        {
            $aParts = explode('_', $sModule);
            $sModule = $aParts[0];
            $sMethod = $sMethod . ucfirst(strtolower($aParts[1]));
            if (isset($aParts[2]))
            {
                $sMethod .= '_' . ucfirst(strtolower($aParts[2]));
            }
        }

        // Have we cached the object?
        if (!isset($aModules[$sModule]))
        {
            // Make sure its a valid/enabled module
            if (!Phpfox::isModule($sModule))
            {
                echo json_encode(array(
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.invalid_module")). ' ' . $sModule,
                    'error_code' => 1
                ));
                die;
            }

            // Cache the object and get the callback service
            $aModules[$sModule] = Phpfox::getService($sModule . '.callback');
        }

        if (!isset($aModules[$sModule]))
        {
            return array();
        }

        $aResult = array();
        
        switch ($sModule) {
            case 'music':
                switch ($sMethod) {
                    case 'getNotificationSong_Like':
                        $aResult = Phpfox::getService('mfox.song')->doSongGetNotificationSong_Like($aParams);
                        break;
                    
                    case 'getNotificationAlbum_Like':
                        $aResult = Phpfox::getService('mfox.album')->doAlbumGetNotificationAlbum_Like($aParams);
                        break;
                    
                    default:
                        break;
                }
            
            case 'videochannel':
                switch ($sMethod) {
                    case 'getNotificationLike':
                        $aResult = Phpfox::getService('mfox.videochannel')->doVideoGetNotificationLike($aParams);
                        break;

                    default:
                        break;
                }
                break;
            
            case 'video':
                switch ($sMethod) {
                    case 'getNotificationLike':
                        $aResult = Phpfox::getService('mfox.video')->doVideoGetNotificationLike($aParams);
                        break;

                    default:
                        break;
                }
                break;
            case 'ultimatevideo':
                switch ($sMethod) {
                    case 'getNotificationLikevideo':
                        $aResult = Phpfox::getService('mfox.ultimatevideo')->getNotificationLikevideo($aParams);
                        break;
                    case 'getNotificationCommentvideo':
                        $aResult = Phpfox::getService('mfox.ultimatevideo')->getNotificationCommentvideo($aParams);
                        break;

                    case 'getNotificationLikeplaylist':
                        $aResult = Phpfox::getService('mfox.ultimatevideo')->getNotificationLikeplaylist($aParams);
                        break;
                    case 'getNotificationCommentplaylist':
                        $aResult = Phpfox::getService('mfox.ultimatevideo')->getNotificationCommentplaylist($aParams);
                        break;

                    default:
                        break;
                }
                break;
            
            case 'pages':
                switch ($sMethod) {
                    case 'getNotificationComment_Feed':
                        $aResult = Phpfox::getService('mfox.pages')->doPagesGetNotificationComment_Feed($aParams);
                        break;
                    default:
                        break;
                }
                break;

            case 'friend':
                switch ($sMethod) {
                    case 'getNotificationAccepted':
                        $aResult = Phpfox::getService('mfox.friend')->doFriendGetNotificationAccepted($aParams);
                        break;

                    default:
                        break;
                }


                break;

            case 'feed':
                switch ($sMethod) {
                    case 'getNotificationComment_Profile':
                        $aResult = Phpfox::getService('mfox.feed')->doFeedGetNotificationComment_Profile($aParams);
                        break;
                    
                    case 'getNotificationMini_Like':
                        $aResult = Phpfox::getService('mfox.feed')->doFeedGetNotificationMini_Like($aParams);
                        break;

                    default:
                        break;
                }
                break;
            case 'fevent':
            case 'event':
                if(Phpfox::getService('mfox.event')->isAdvancedModule() && $sModule == 'event'){
                    break;
                }

                switch ($sMethod) {
                    case 'getNotificationLike':
                        $aResult = Phpfox::getService('mfox.event')->doEventGetNotificationLike($aParams);
                        break;

                    case 'getNotificationComment':
                        $aResult = Phpfox::getService('mfox.event')->doEventGetNotificationComment($aParams);

                        break;

                    case 'getNotificationComment_Like':
                        $aResult = Phpfox::getService('mfox.event')->doEventGetNotificationComment_Like($aParams);

                        break;
                    
                    case 'getNotificationComment_Feed':
                        $aResult = Phpfox::getService('mfox.event')->doEventGetNotificationComment_Feed($aParams);
                        break;
                    
                    default:
                        break;
                }
                break;

            case 'advancedphoto':
            case 'photo':
                if(Phpfox::getService('mfox.photo')->isAdvancedModule() && $sModule == 'photo'){
                    break;
                }
                switch ($sMethod) {
                    case 'getNotificationLike':
                        $aResult = Phpfox::getService('mfox.photo')->doPhotoGetNotificationLike($aParams);
                        break;
                    case 'getNotificationAlbum_Like':
                        $aResult = Phpfox::getService('mfox.photo')->doPhotoAlbumGetNotificationAlbum_Like($aParams);
                        break;
                    default:
                        break;
                }
                break;

            case 'comment':
                switch ($sMethod) {
                    case 'getNotificationUser_Status':
                        $aResult = array(
                            'link' => array('iUserId' => $aParams['user_id'], 'sView' => 'profile'),
                            'message' => '',
                        );
                        break;

                    case 'getNotificationPhoto':
                        $aResult = array(
                            'link' => array('iItemId' => $aParams['item_id'], 'sView' => 'photo'),
                            'message' => '',
                        );
                        break;

                    default:
                        break;
                }
                break;

            case 'user':
                switch ($sMethod) {
                    case 'getCommentNotificationStatus':
                        $aResult = Phpfox::getService('mfox.user')->doUserGetCommentNotificationStatusTag($aParams);
                        break;

                    case 'getNotificationStatus_Like':
                        $aResult = Phpfox::getService('mfox.user')->doUserGetNotificationStatus_Like($aParams);
                        break;

                    default:
                        break;
                }
                break;
			
            case 'directory':
                switch ($sMethod) {
                    case 'getNotificationComment':
                        $aResult = Phpfox::getService('mfox.directory')->getNotificationComment($aParams);
                        break;
                    
                    case 'getNotificationComment_Like':
                        $aResult = Phpfox::getService('mfox.directory')->getNotificationComment_Like($aParams);
                        break;
                    
                    case 'getNotificationComment_Feed':
                        $aResult = Phpfox::getService('mfox.directory')->getNotificationComment_Feed($aParams);
                        break;
                    
                    default:
                        break;
                }
                break;

            default:
                break;
        }

        // Update method and module.
        $aResult['sMethod'] = $sMethod;
        $aResult['sModule'] = $sModule;

        return $aResult;
    }

    /**
     * Input data: N/A
     * 
     * Output data:
     * + iNumberOfFriendRequest: int.
     * + iNumberOfMessage: int.
     * + iNumberNotification: int.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see notification/get
     * 
     * @param array $aData
     * @return array
     */
    public function update($aData)
    {
        $iNumberOfFriendRequest = Phpfox::getService('friend.request')->getUnseenTotal();
        $iNumberOfMessage = Phpfox::getService('mail')->getUnseenTotal();
        $iNumberNotification = $this->getUnseenTotal();

        $aError = Phpfox_Error::get();
        if (count($aError))
        {
            return array(
                'error_message' => $aError,
                'error_code' => 1,
                'result' => 0
            );
        }

        $aUser = Phpfox::getService('mfox.helper.user')->getUserData(Phpfox::getUserId()); // should use another cache machenism to remove overhead here
        return array(
            'iNumberOfFriendRequest' => $iNumberOfFriendRequest,
            'iNumberOfMessage' => $iNumberOfMessage,
            'iNumberNotification' => $iNumberNotification,
            'sUserProfileImage' => isset($aUser['sProfileImageSmall']) ? $aUser['sProfileImageSmall'] : '',
            'sFullName' => isset($aUser['full_name']) ? $aUser['full_name'] : '',
        );
    }

    public function getUnseenTotal($iUserId = null)
    {
        if(null == $iUserId){
            $iUserId =  Phpfox::getUserId();
        }

        $types =  array();

        foreach($this->getSupportedNotificationTypes() as $type){
            $types[]= "'" .$type. "'";
        }

        $iCnt = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('notification'), 'n')
            ->where('n.user_id = ' . (int) $iUserId . ' AND n.is_seen = 0 AND n.type_id IN ('.implode(',', $types) . ')')
            ->execute('getSlaveField');

        return $iCnt;
    }

    /**
     * @link ./mfox/doc/notitication-type.txt
     * @return array
     */
    public function getSupportedNotificationTypes(){
        return array(
            "advancedmarketplace_approved",
            "advancedmarketplace_follow",
            "advancedmarketplace_invite",
            "advancedmarketplace_like",
            "advancedphoto_album_like",
            // "advancedphoto_album_tag",
            "advancedphoto_like",
            // "advancedphoto_tag",
            // "advancedshare_blog",
            // "advancedshare_bloglike",
            // "advancedshare_fevent",
            // "advancedshare_feventlike",
            // "advancedshare_link",
            // "advancedshare_linklike",
            // "advancedshare_musicsharing_playlist",
            // "advancedshare_music_song",
            // "advancedshare_newsfeedlike",
            // "advancedshare_pages",
            // "advancedshare_pageslike",
            // "advancedshare_photo",
            // "advancedshare_photocomment",
            // "advancedshare_photolike",
            // "advancedshare_photo_album",
            // "advancedshare_video",
            // "advancedshare_videochannel",
            // "advancedshare_videochannelcomment",
            "blog_approved",
            "blog_like",
            "comment_advancedmarketplace",
            // "comment_advancedmarketplace_tag",
            "comment_advancedphoto",
            "comment_advancedphoto_album",
            // "comment_advancedphoto_tag",
            "comment_blog",
            // "comment_blog_tag",
            "comment_feed",
            // "comment_feed_tag",
            "comment_fevent",
            // "comment_link",
            // "comment_link_tag",
            "comment_marketplace",
            "comment_musicsharing_album",
            // "comment_musicsharing_playlist",
            "comment_music_album",
            "comment_music_song",
            // "comment_newsfeed",
            // "comment_pages",
            "comment_photo",
            "comment_photo_album",
            // "comment_photo_tag",
            "comment_poll",
            // "comment_poll_tag",
            "comment_quiz",
            "comment_user_status",
            // "comment_user_statustag",
            "comment_video",
            "comment_videochannel",
            // "comment_videochannel_tag",
            // "comment_video_tag",
            "directory_approve_business",
            "directory_approve_claimrequest",
            "directory_comment",
            "directory_comment_feed",
            "directory_comment_like",
            "directory_expirenotify",
            "directory_invited",
            "directory_like",
            "directory_postitem",
            "directory_updateinfobusiness",
            "event_comment",
            "event_comment_feed",
            "event_comment_like",
            "event_invite",
            "event_like",
            // "feed_comment_like",
            // "feed_comment_link",
            "feed_comment_profile",
            "feed_mini_like",
            "fevent_approved",
            "fevent_comment",
            "fevent_comment_feed",
            "fevent_comment_like",
            "fevent_invite",
            "fevent_like",
            // "fevent_repeattonormalwarning",
            "forum_post_like",
            "forum_subscribed_post",
            "friend_accept",
            "friend_accepted",
            // "jobposting_addjobfollowedcompany",
            // "link_like",
            "marketplace_like",
            "marketplace_invite",
            "musicsharing_album_like",
            // "musicsharing_playlist_like",
            "music_album_like",
            "music_song_like",
            // "newsfeed_like",
            // "pages_approved",
            "pages_comment",
            "pages_comment_feed",
            "pages_comment_like",
            "pages_invite",
            "pages_joined",
            // "pages_like",
            // "pages_register",
            "photo_album_like",
            // "photo_approved",
            // "photo_feed_profile",
            "photo_like",
            // "photo_tag",
            // "poke",
            "poll",
            "poll_approved",
            "poll_like",
            "quiz",
            "quiz_like",
            // "suggestion",
            "user_status_like",
            "videochannel",
            "videochannel_approved",
            "videochannel_favourite",
            "videochannel_like",
            "video_like",
            // "wall_comment",
            // "wall_photo",
            // "wall_status",
            // "wall_walllink"
            "ultimatevideo_likevideo",
            "ultimatevideo_commentvideo",
            "ultimatevideo_likeplaylist",
            "ultimatevideo_commentplaylist"
        );
    }

    /**
     * DEPRECATED
     * @return int
     */
    public function _getUnseenTotal()
    {
        // we do not use this function currently
        return 0;

        $sCond = '';
        $aGetRows = $this->database()->select('n.notification_id, n.type_id')
                ->from(Phpfox::getT('notification'), 'n')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = n.owner_user_id')
                ->innerJoin('(SELECT * FROM ' . Phpfox::getT('notification') . ' AS n WHERE n.user_id = ' . Phpfox::getUserId() . ' ORDER BY n.time_stamp DESC)', 'ninner', 'ninner.notification_id = n.notification_id')
                ->where('n.user_id = ' . Phpfox::getUserId() . ' AND n.is_seen = 0 ' . $sCond)
                ->execute('getSlaveRows');

        $aRows = array();
        foreach ($aGetRows as $aGetRow)
        {
            $aRows[(int) $aGetRow['notification_id']] = $aGetRow;
        }

        arsort($aRows);

        // Call the callback function.
        $aNotifications = array();
        foreach ($aRows as $aRow)
        {
            $aParts1 = explode('.', $aRow['type_id']);
            $sModule = $aParts1[0];
            if (strpos($sModule, '_'))
            {
                $aParts = explode('_', $sModule);
                $sModule = $aParts[0];
            }

            if (Phpfox::isModule($sModule))
            {
                $isGetCallback = true;
                if(Phpfox::getService('mfox.event')->isAdvancedModule() && $sModule == 'event'){
                    $isGetCallback = false;
                }
                if(Phpfox::getService('mfox.photo')->isAdvancedModule() && $sModule == 'photo'){
                    $isGetCallback = false;
                }

                if($isGetCallback == true){
                    $aNotifications[] = $aRow;
                }
            }
        }                
                    
        return count($aNotifications);
    }    

    /**
     * @see Mail_Service_Mail
     * 
     * Input data: N/A
     * 
     * Output data:
     * + iMailId: int.
     * + sTitle: string.
     * + sPreview: string.
     * + iUserId: int.
     * + sFullName: string.
     * + sUserImage: string.
     * + iTimeStamp: int.
     * + sTime: string.
     * + sTimeConverted: string.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see notification/message
     * 
     * @param array $aData
     * @return array
     */
    public function message($aData)
    {
        $aMessages = Phpfox::getService('mail')->getLatest();

        $aResult = array();
        foreach ($aMessages as $aMessage)
        {
            $aResult[] = array(
                'iMailId' => $aMessage['mail_id'],
                'iConversationId' => $aMessage['mail_id'],
								'bIsRead' => $aMessage['viewer_is_new'] == 1 ? false : true,
                'sTitle' => $aMessage['subject'],
                'sPreview' => $aMessage['preview'],
                'iUserId' => $aMessage['user_id'],
                'sFullName' => $aMessage['full_name'],
                'sUserImage' => Phpfox::getService('mfox.user')->getImageUrl($aMessage, '_50_square'),
                'iTimeStamp' => $aMessage['time_stamp'],
                'sTime' => date('l, F j, o', (int) $aMessage['time_stamp']) . ' at ' . date('h:i a', (int) $aMessage['time_stamp']),
                'sTimeConverted' => Phpfox::getLib('date')->convertTime($aMessage['time_stamp'], 'comment.comment_time_stamp'),
								// @todo: update later
                'sInboxUpdated' => Phpfox::getLib('date')->convertTime($aMessage['time_stamp'], 'comment.comment_time_stamp'),
            );
        }

        return $aResult;
    }

    /**
     * @see Friend_Service_Request_Request
     * 
     * Input data: N/A
     * 
     * Output data:
     * + iRequestId: int.
     * + iUserId: int.
     * + sFullName: string.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see notification/friendrequested
     * 
     * @param array $aData
     * @return array
     */
    public function friendrequested($aData)
    {
		$iPage = isset($aData['iPage']) ?  intval($aData['iPage']) : 1;
		$iLimit = isset($aData['iAmountOfNotification']) ? $aData['iAmountOfNotification'] : 10;
        list($iCnt, $aFriends) = Phpfox::getService('friend.request')->get($iPage, $iLimit);



        $aResult = array();

        // since 3.08 by nam nguyen
        // friend.request does not work correctly, fixed here
        if ($iCnt < ($iPage - 1)*$iLimit){
            return $aResult;
        }
        
        foreach ($aFriends as $aFriend)
        {
			$aUser = Phpfox::getService('mfox.helper.user')->getUserData($aFriend['user_id']);
            $aResult[] = array(
                'iResourceId' => $aFriend['friend_user_id'],
                'iUserId' => $aFriend['user_id'],
								'sFullName' => $aFriend['full_name'],
                                'iTimeStamp'=> time(), 
								'UserProfileImg_Url' => $aUser['sProfileImageSmall']
            );
        }

        return $aResult;
    }
    
    /**
     * @see Notification_Service_Notification
     * 
     * Input data: N/A
     * 
     * Output data:
     * + iNotificationId: int.
     * + sMessage: string.
     * + aLink: array.
     * + sCallbackModule: string.
     * + sCallbackMethod: string.
     * + iUserId: int.
     * + iOwnerUserId: int.
     * + sFullName: string.
     * + sUserName: string.
     * + sTypeId: string.
     * + iItemId: int.
     * + iIsSeen: int.
     * + iItemUserId: int.
     * + iTotalExtra: int.
     * + iProfilePageId: int.
     * + sFriendModule: string.
     * + iGender: int.
     * + sIcon: string.
     * + sUserImage: string.
     * + iTimeStamp: int.
     * + sTime: string.
     * + sTimeConverted: string.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see notification/notification
     * 
     * @param array $aData
     * @return array
     */
    public function notification($aData)
    {
        $iLimit = !empty($aData['iLimit']) ? (int) $aData['iLimit'] : 10;
        $sAction = !empty($aData['sAction']) ? $aData['sAction'] : 'more';

        $sCond = '';
        if ($sAction == 'new' && !empty($aData['iMinId']))
        {
            $sCond = ' AND n.notification_id > ' . (int) $aData['iMinId'];
        }
        elseif ($sAction == 'more' && !empty($aData['iMaxId']))
        {
            $sCond = ' AND n.notification_id < ' . (int) $aData['iMaxId'];
        }

        $types =  array();

        foreach($this->getSupportedNotificationTypes() as $type){
            $types[]= "'" .$type. "'";
        }

        /**
         * @var array
         */
        $aGetRows = $this->database()->select('n.*, n.user_id as item_user_id, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('notification'), 'n')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = n.owner_user_id')
                ->innerJoin('(SELECT * FROM ' . Phpfox::getT('notification') . ' AS n WHERE n.user_id = ' . Phpfox::getUserId() . ' ORDER BY n.time_stamp DESC)', 'ninner', 'ninner.notification_id = n.notification_id')
                ->where('n.user_id = ' . Phpfox::getUserId() . ' AND n.is_seen = 0 ' . $sCond .' AND n.type_id IN ('.implode(',', $types). ') ')
                // ->group('n.type_id, n.item_id')
                ->order('n.is_seen ASC, n.time_stamp DESC')
                ->limit($iLimit)
                ->execute('getSlaveRows');


        /**
         * @var array
         */
        $aRows = array();
        foreach ($aGetRows as $aGetRow)
        {
            $aRows[(int) $aGetRow['notification_id']] = $aGetRow;
        }

        arsort($aRows);


        // Call the callback function.
        $aNotifications = array();
        // return $aRows;
        foreach ($aRows as $aRow)
        {
            $aParts1 = explode('.', $aRow['type_id']);
            

            $sModule = $aParts1[0];
            if (strpos($sModule, '_'))
            {
                $aParts = explode('_', $sModule);
                $sModule = $aParts[0];
            }

            if (Phpfox::isModule($sModule))
            {
                // if ((int) $aRow['total_extra'] > 1)
                {
                    $aExtra = $this->database()->select('n.owner_user_id, n.time_stamp, n.is_seen, u.full_name')
                            ->from(Phpfox::getT('notification'), 'n')
                            ->join(Phpfox::getT('user'), 'u', 'u.user_id = n.owner_user_id')
                            ->where('n.type_id = \'' . $this->database()->escape($aRow['type_id']) . '\' AND n.item_id = ' . (int) $aRow['item_id'])
                            ->group('u.user_id')
                            ->order('n.time_stamp DESC')
                            ->limit(10)
                            ->execute('getSlaveRows');

                    foreach ($aExtra as $iKey => $aExtraUser)
                    {
                        if ($aExtraUser['owner_user_id'] == $aRow['user_id'])
                        {
                            unset($aExtra[$iKey]);
                        }

                        if (!$aRow['is_seen'] && $aExtraUser['is_seen'])
                        {
                            unset($aExtra[$iKey]);
                        }
                    }

                    if (count($aExtra))
                    {
                        $aRow['extra_users'] = $aExtra;
                    }
                }

                $aCallBack = array();
                if (substr($aRow['type_id'], 0, 8) != 'comment_')
                {
                    if (!Phpfox::hasCallback($aRow['type_id'], 'getNotification'))
                    {
                        $aCallBack['link'] = null;
                        $aCallBack['message'] = '2. Notification is missing a callback. [' . $aRow['type_id'] . '::getNotification]';
                        $aCallBack['sModule'] = '';
                        $aCallBack['sMethod'] = '';
                    }
                    else
                    {
                        $aCallBack = $this->callback($aRow['type_id'] . '.getNotification', $aRow);
                    }
                }
                elseif (substr($aRow['type_id'], 0, 8) == 'comment_')
                {
                    if (substr($aRow['type_id'], 0, 12) != 'comment_feed' && !Phpfox::hasCallback(substr_replace($aRow['type_id'], '', 0, 8), 'getCommentNotification'))
                    {
                        $aCallBack['link'] = null;
                        $aCallBack['message'] = 'Notification is missing a callback. [' . substr_replace($aRow['type_id'], '', 0, 8) . '::getCommentNotification]';
                        $aCallBack['sModule'] = '';
                        $aCallBack['sMethod'] = '';
                    }
                    else
                    {
                        $aCallBack = $this->_getExtraCallback($aRow);
                    }
                }

                if (!isset($aCallBack['message']))
                {
                    $aCallBack2 = Phpfox::callback($aCallBack['sModule'] . '.' . $aCallBack['sMethod'], $aRow);
                    if(!isset($aCallBack2['link']) || !is_array($aCallBack2['link']))
                    {
                        $aCallBack2['link'] = array('iResourceId' => (int) $aRow['item_id']);
                    }
                    $aCallBack = array_merge($aCallBack, $aCallBack2);
                }

                $aRow['item_id_in_db'] = $aRow['item_id'];
                $aNotifications[] = array_merge($aRow, (array) $aCallBack);
            }

            // On web, this flow is used
            // on mobile, this flow is not used
            // $this->database()->update(Phpfox::getT('notification'), array('is_seen' => '1'), 'type_id = \'' . $this->database()->escape($aRow['type_id']) . '\' AND item_id = ' . (int) $aRow['item_id']);
        }

        $aResult = array();

        foreach ($aNotifications as $aNotification)
        {
            if (!isset($aNotification['link']))
            {
                $aLink = array();
            } else {
                $aLink = $aNotification['link'];
            }
            
            $aNotification = $this->__prepareItemId($aNotification);
            
            $aResult[] = array(
                'sType' => $aNotification['type_id'], // added
                'sItemType' => $aParts[0], //added
                'iNotificationId' => $aNotification['notification_id'],
                'sMessage' => $aNotification['message'],
                'aLink' => $aLink,
                'sCallbackModule' => $aNotification['sModule'],
                'sCallbackMethod' => $aNotification['sMethod'],
                'iUserId' => $aNotification['user_id'],
                'iOwnerUserId' => $aNotification['owner_user_id'],
                'sFullName' => $aNotification['full_name'],
                'sUserName' => $aNotification['user_name'],
                'sTypeId' => $aNotification['type_id'],
                'iItemId' => $aNotification['item_id'],
                'iIsSeen' => $aNotification['is_seen'],
                'iItemUserId' => $aNotification['item_user_id'],
                // 'iTotalExtra' => $aNotification['total_extra'],
                'iProfilePageId' => $aNotification['profile_page_id'],
                'sFriendModule' => $aNotification['final_module'],
                'iGender' => $aNotification['gender'],
                'sIcon' => isset($aNotification['icon']) ? $aNotification['icon'] : '',
                'sUserImage' => Phpfox::getService('mfox.user')->getImageUrl($aNotification, '_50_square'),
                'iTimeStamp' => $aNotification['time_stamp'],
                'sTime' => date('l, F j, o', (int) $aNotification['time_stamp']) . ' at ' . date('h:i a', (int) $aNotification['time_stamp']),
                'sTimeConverted' => Phpfox::getLib('date')->convertTime($aNotification['time_stamp'], 'comment.comment_time_stamp')
            );
        }

        return $aResult;
    }

    /**
     * Get extra callback.
     * @param array $aRow
     * @return array
     */
    private function _getExtraCallback($aRow)
    {
        if (!isset($aRow['type_id']))
        {
            return array();
        }
        
        $aCallBack = array();
        
        switch ($aRow['type_id']) {
            case 'comment_advancedphoto':
            case 'comment_photo':
                $aCallBack = Phpfox::getService('mfox.photo')->doPhotoGetCommentNotification($aRow);
                break;
            case 'comment_user_status':
                $aCallBack = Phpfox::getService('mfox.user')->doUserGetCommentNotificationStatus($aRow);
                break;
            case 'comment_advancedphoto_album':
            case 'comment_photo_album':
                $aCallBack = Phpfox::getService('mfox.photo')->doPhotoAlbumGetCommentNotificationAlbum($aRow);
                break;
            case 'comment_videochannel':
                $aCallBack = Phpfox::getService('mfox.videochannel')->doVideoGetCommentNotification($aRow);
                break;
            case 'comment_video':
                $aCallBack = Phpfox::getService('mfox.video')->doVideoGetCommentNotification($aRow);
                break;
            case 'comment_music_song':
                $aCallBack = Phpfox::getService('mfox.song')->doSongGetCommentNotificationSong($aRow);
                break;
            case 'comment_music_album':
                $aCallBack = Phpfox::getService('mfox.album')->doMusicAlbumGetCommentNotificationAlbum($aRow);
                break;
            case 'comment_musicsharing_album':
                $aCallBack = Phpfox::getService('mfox.musicsharing.album')->doMusicAlbumGetCommentNotificationAlbum($aRow);
                break;
            case 'comment_feed':
                $aCallBack = Phpfox::getService('mfox.feed')->doFeedGetCommentNotificationFeed($aRow);
				break;
            case 'comment_blog':
                $aCallBack = Phpfox::getService('blog.callback')->getCommentNotification($aRow);
                break;
        }

        $aParts = explode('_', $aRow['type_id']);
        $aCallBack['sModule'] = $aParts[1];
        $aCallBack['sMethod'] = 'getCommentNotification';
        
        return $aCallBack;
    }
    
    /**
     * Input data:
     * + iAmountOfNotification: int, optional.
     * + iLastNotificationTimeStamp: int, optional.
     * 
     * Output data:
     * + iNotificationId: int.
     * + sMessage: string.
     * + aLink: array.
     * + sCallbackModule: string.
     * + sCallbackMethod: string.
     * + iUserId: int.
     * + iOwnerUserId: int.
     * + sFullName: string.
     * + sUserName: string.
     * + sTypeId: string.
     * + iItemId: int.
     * + iIsSeen: int.
     * + iItemUserId: int.
     * + iTotalExtra: int.
     * + iProfilePageId: int.
     * + sFriendModule: string.
     * + iGender: int.
     * + sIcon: string.
     * + sUserImage: string.
     * + iTimeStamp: int.
     * + sTime: string.
     * + sTimeConverted: string.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see notification/getall
     * 
     * @param array $aData
     * @return array
     */
    public function getall($aData)
    {
        /**
         * @var int
         */
        $iPage = 0;
        /**
         * @var int
         */
        $iPageTotal = isset($aData['iAmountOfNotification']) ? (int) $aData['iAmountOfNotification'] : 20;
        /**
         * @var int
         */
        $iLastNotificationTimeStamp = isset($aData['iLastNotificationTimeStamp']) ? (int) $aData['iLastNotificationTimeStamp'] : 0;
        /**
         * @var string
         */
        $sCond = $iLastNotificationTimeStamp > 0 ? ' AND n.time_stamp < ' . $iLastNotificationTimeStamp : '';
        /**
         * @var int
         */
        $iCnt = $this->database()->select('COUNT(*)')
                ->from(Phpfox::getT('notification'), 'n')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = n.owner_user_id')
                ->where('n.user_id = ' . Phpfox::getUserId() . '' . $sCond)
                ->execute('getSlaveField');
        /**
         * @var array
         */
        $aRows = $this->database()->select('n.*, n.user_id as item_user_id, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('notification'), 'n')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = n.owner_user_id')
                ->where('n.user_id = ' . Phpfox::getUserId() . '' . $sCond)
                ->order('n.time_stamp DESC')
                ->limit($iPage, $iPageTotal, $iCnt)
                ->execute('getSlaveRows');

        $sIds = '';
        /**
         * @var array
         */
        $aNotifications = array();
        foreach ($aRows as $aRow)
        {
            $sIds .= (int) $aRow['notification_id'] . ',';

            $aParts1 = explode('.', $aRow['type_id']);
            $sModule = $aParts1[0];
            if (strpos($sModule, '_'))
            {
                $aParts = explode('_', $sModule);
                $sModule = $aParts[0];
            }
            
            $aCallBack = array();
            
            if (Phpfox::isModule($sModule))
            {
                if (substr($aRow['type_id'], 0, 8) != 'comment_' && !Phpfox::hasCallback($aRow['type_id'], 'getNotification'))
                {
                    $aCallBack['link'] = null;
                    $aCallBack['message'] = '2. Notification is missing a callback. [' . $aRow['type_id'] . '::getNotification]';
                    $aCallBack['sModule'] = '';
                    $aCallBack['sMethod'] = '';
                }
                elseif (substr($aRow['type_id'], 0, 8) == 'comment_' && substr($aRow['type_id'], 0, 12) != 'comment_feed' && !Phpfox::hasCallback(substr_replace($aRow['type_id'], '', 0, 8), 'getCommentNotification'))
                {
                    $aCallBack['link'] = null;
                    $aCallBack['message'] = 'Notification is missing a callback. [' . substr_replace($aRow['type_id'], '', 0, 8) . '::getCommentNotification]';
                    $aCallBack['sModule'] = '';
                    $aCallBack['sMethod'] = '';
                }
                elseif (Phpfox::hasCallback($aRow['type_id'], 'getNotification'))
                {
                    $aCallBack = $this->callback($aRow['type_id'] . '.getNotification', $aRow);
                }
                else
                {
                    $aCallBack = $this->_getExtraCallback($aRow);
                }

                $aNotifications[] = array_merge($aRow, (array) $aCallBack);
            }
        }

        $sIds = rtrim($sIds, ',');

        if (!empty($sIds))
        {
            $this->database()->update(Phpfox::getT('notification'), array('is_seen' => '1'), 'notification_id IN(' . $sIds . ')');
        }

        $aResult = array();

        foreach ($aNotifications as $aNotification)
        {
            if (!isset($aNotification['link']))
            {
                $aLink = array();
            } else {
                $aLink = $aNotification['link'];
            }            
            
            $aResult[] = array(
                'iNotificationId' => $aNotification['notification_id'],
                'sMessage' => $aNotification['message'],
                'aLink' => $aLink,
                'sCallbackModule' => $aNotification['sModule'],
                'sCallbackMethod' => $aNotification['sMethod'],
                'iUserId' => $aNotification['user_id'],
                'iOwnerUserId' => $aNotification['owner_user_id'],
                'sFullName' => $aNotification['full_name'],
                'sUserName' => $aNotification['user_name'],
                'sTypeId' => $aNotification['type_id'],
                'sType' => $aNotification['type_id'],
								// @todo: implement later
                'sItemType' => 'Implement Later',
                'iItemId' => $aNotification['item_id'],
                'iIsSeen' => $aNotification['is_seen'],
                'iItemUserId' => $aNotification['item_user_id'],
                // 'iTotalExtra' => $aNotification['total_extra'],
                'iProfilePageId' => $aNotification['profile_page_id'],
                'sFriendModule' => $aNotification['final_module'],
                'iGender' => $aNotification['gender'],
                'sIcon' => isset($aNotification['icon']) ? $aNotification['icon'] : '',
                'sUserImage' => Phpfox::getService('mfox.user')->getImageUrl($aNotification, '_50_square'),
                'iTimeStamp' => $aNotification['time_stamp'],
                'sTime' => date('l, F j, o', (int) $aNotification['time_stamp']) . ' at ' . date('h:i a', (int) $aNotification['time_stamp']),
                'sTimeConverted' => Phpfox::getLib('date')->convertTime($aNotification['time_stamp'], 'comment.comment_time_stamp')
            );
        }

        return $aResult;
    }

    /**
     * Input data:
     * + iNotificationId: int, required.
     * 
     * Output data:
     * + iNotificationId: int.
     * + sMessage: string.
     * + aLink: array.
     * + sCallbackModule: string.
     * + sCallbackMethod: string.
     * + iUserId: int.
     * + iOwnerUserId: int.
     * + sFullName: string.
     * + sUserName: string.
     * + sTypeId: string.
     * + iItemId: int.
     * + iIsSeen: int.
     * + iItemUserId: int.
     * + iTotalExtra: int.
     * + iProfilePageId: int.
     * + sFriendModule: string.
     * + iGender: int.
     * + sIcon: string.
     * + sUserImage: string.
     * + iTimeStamp: int.
     * + sTime: string.
     * + sTimeConverted: string.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see notification/getOneNotification
     * 
     * @param array $aData
     * @return array
     */
    public function detail($aData)
    {
        /**
         * @var int
         */
        $iNotificationId = isset($aData['iNotificationId']) ? (int) $aData['iNotificationId'] : 0;
        
        /**
         * @var int
         */
        $iCnt = $this->database()
                ->select('COUNT(*)')
                ->from(Phpfox::getT('notification'), 'n')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = n.owner_user_id')
                ->where('n.user_id = ' . Phpfox::getUserId() . ' AND n.notification_id = ' . $iNotificationId)
                ->execute('getSlaveField');
        /**
         * @var array
         */
        if ($iCnt > 0)
        {
            $aRow = $this->database()
                    ->select('n.*, n.user_id as item_user_id, ' . Phpfox::getUserField())
                    ->from(Phpfox::getT('notification'), 'n')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = n.owner_user_id')
                    ->where('n.user_id = ' . Phpfox::getUserId() . ' AND n.notification_id = ' . $iNotificationId)
                    ->execute('getRow');
        }
        
        if ($aRow == null)
        {
            return array();
        }
        
        /**
         * @var array
         */
        $aNotification = array();
        $aParts1 = explode('.', $aRow['type_id']);
        $sModule = $aParts1[0];
        if (strpos($sModule, '_'))
        {
            $aParts = explode('_', $sModule);
            $sModule = $aParts[0];
        }

        $aCallBack = array();

        if (Phpfox::isModule($sModule))
        {
            if (substr($aRow['type_id'], 0, 8) != 'comment_' && !Phpfox::hasCallback($aRow['type_id'], 'getNotification'))
            {
                $aCallBack['link'] = null;
                $aCallBack['message'] = '2. Notification is missing a callback. [' . $aRow['type_id'] . '::getNotification]';
                $aCallBack['sModule'] = '';
                $aCallBack['sMethod'] = '';
            }
            elseif (substr($aRow['type_id'], 0, 8) == 'comment_' && substr($aRow['type_id'], 0, 12) != 'comment_feed' && !Phpfox::hasCallback(substr_replace($aRow['type_id'], '', 0, 8), 'getCommentNotification'))
            {
                $aCallBack['link'] = null;
                $aCallBack['message'] = 'Notification is missing a callback. [' . substr_replace($aRow['type_id'], '', 0, 8) . '::getCommentNotification]';
                $aCallBack['sModule'] = '';
                $aCallBack['sMethod'] = '';
            }
            elseif (Phpfox::hasCallback($aRow['type_id'], 'getNotification'))
            {
                $aCallBack = $this->callback($aRow['type_id'] . '.getNotification', $aRow);
                if(!isset($aCallBack['message'])){
                    $isGetCallback = true;
                    if(Phpfox::getService('mfox.event')->isAdvancedModule() && $sModule == 'event'){
                        $isGetCallback = false;
                    }
                    if(Phpfox::getService('mfox.photo')->isAdvancedModule() && $sModule == 'photo'){
                        $isGetCallback = false;
                    }

                    if($isGetCallback == true){
                        $aCallBack2 = Phpfox::callback($aCallBack['sModule'] . '.' . $aCallBack['sMethod'], $aRow);
                        if(!isset($aCallBack2['link']) || !is_array($aCallBack2['link'])){
                            $aCallBack2['link'] = array('iResourceId' => (int)$aNotification['item_id']); 
                        }
                        $aCallBack = array_merge($aCallBack, $aCallBack2);
                    }
                }
            }
            else
            {
                $aCallBack = $this->_getExtraCallback($aRow);
            }

            if(count($aCallBack) == 0 || !isset($aCallBack['message'])){
                if (($aCallBack = Phpfox::callback($aRow['type_id'] . '.getNotification', $aRow)))
                {           
                    if (!isset($aCallBack['message']))
                    {
                        $aRow['link'] = '#';
                        $aRow['message'] = 'Notification is missing a message/link param. [' . $aRow['type_id'] . '::getNotification]';             
                    }
                }
            }
            else{
                $aRow['item_id_in_db'] = $aRow['item_id'];
                $aNotification = array_merge($aRow, (array) $aCallBack); 
            }
        }
        
        $this->database()->update(Phpfox::getT('notification'), array('is_seen' => '1'), 'notification_id IN(' . $aRow['notification_id'] . ')');
        
        if (!isset($aNotification['link']))
        {
            return array('error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_this_item_or_it_has_been_deleted")));
        }
        
        $aParts = explode('_', $aNotification['type_id']);

        if (!isset($aNotification['link']))
        {
            $aLink = array();
        } else {
            $aLink = $aNotification['link'];
        }

        switch($aNotification['type_id']){
            case 'pages_invite':
            case 'pages_approved':
                $aLink['iPageId'] = $aNotification['item_id_in_db'];
                break;
            case 'forum_subscribed_post':
            case 'forum_post_like':
                $aPost = Phpfox::getService('forum.post')->getForEdit($aNotification['item_id_in_db']);
                if(isset($aPost['thread_id'])){
                    $aNotification['item_id'] = $aPost['thread_id'];
                }
                break;
            case 'quiz':
            case 'quiz_like':
            case 'comment_quiz':
                break;
        }
        
        $aNotification = $this->__prepareItemId($aNotification);
                            
        return array(
            'iNotificationId' => $aNotification['notification_id'],
            'sMessage' => $aNotification['message'],
            'aLink' => $aLink,
            'sCallbackModule' => $aNotification['sModule'],
            'sCallbackMethod' => $aNotification['sMethod'],
            'iUserId' => $aNotification['user_id'],
            'iOwnerUserId' => $aNotification['owner_user_id'],
            'sFullName' => $aNotification['full_name'],
            'sUserName' => $aNotification['user_name'],
            'sTypeId' => $aNotification['type_id'],
            'sType' => $aNotification['type_id'], // added
            'sItemType' => $aParts[0], //added
            'iItemId' => $aNotification['item_id'],
            'iIsSeen' => $aNotification['is_seen'],
            'iItemUserId' => $aNotification['item_user_id'],
            // 'iTotalExtra' => $aNotification['total_extra'],
            'iProfilePageId' => $aNotification['profile_page_id'],
            'sFriendModule' => $aNotification['final_module'],
            'iGender' => $aNotification['gender'],
            'sIcon' => isset($aNotification['icon']) ? $aNotification['icon'] : '',
            'sUserImage' => Phpfox::getService('mfox.user')->getImageUrl($aNotification, '_50_square'),
            'iTimeStamp' => $aNotification['time_stamp'],
            'sTime' => date('l, F j, o', (int) $aNotification['time_stamp']) . ' at ' . date('h:i a', (int) $aNotification['time_stamp']),
            'sTimeConverted' => Phpfox::getLib('date')->convertTime($aNotification['time_stamp'], 'comment.comment_time_stamp')
        );
    }

    private function __prepareItemId($aNotification)
    {
        

        $aParts = explode('_', $aNotification['type_id']);
        if('event' == $aParts[0] || 'fevent' == $aParts[0]){
            switch($aNotification['type_id']){
                case 'fevent_invite':
                case 'event_invite':
                    $aNotification['item_id'] = $aNotification['item_id_in_db'];
                    break;
                default:
                    $aNotification['item_id'] = Phpfox::getService('mfox.event')->getEventIDFeedByItemID($aNotification['item_id']);
                    break;
            }
            
        } else if('pages' == $aParts[0]){
            $aNotification['item_id'] = Phpfox::getService('mfox.pages')->getPagesIDFeedByItemID($aNotification['item_id']);
        }

        return $aNotification;
    }

	public function makeread($aData) {
		$iNotificationId = $aData['iNotificationId'];
		Phpfox::getService('notification.process')->updateSeen($iNotificationId);

		return array(
			'error_code' => 0,
			'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.update_sucessfully"))
		);

	}

    public function markreadall($aData){
        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_mark_read_all_notifications"))
            );
        }
        $iUserID = Phpfox::getUserId();

        $this->__markReadAll($iUserID);
        return array(
            'error_code' => 0,
            'error_message' => ""
        );
    }

    public function __markReadAll($userID){
        $this->database()->update(Phpfox::getT('notification')
            , array('is_seen' => 1)
            , 'user_id = ' . (int)$userID
        );
    }



    public function fetch_notification($aData)
    {
        return $this->notification($aData);
    }

    // FROM 3.08

    public function fetch($aData){
        return $this->fetch_notification($aData);
    }
    
}
