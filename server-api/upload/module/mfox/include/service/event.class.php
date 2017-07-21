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

class Mfox_Service_Event extends Phpfox_Service {

    /**
     * Mfox_Service_Request_Request
     * @var object
     */
    private $_oReq = null;

    /**
     * Mfox_Service_Search_Search
     * @var object
     */
    private $_oSearch = null;

    /**
     * Mfox_Service_Search_Browse
     * @var object
     */
    private $_oBrowse = null;
    
    private $_sCategory = null;
    
    private $_iAttending = null;
    
    private $_aCallback = false;

	/**
     *
     * @var bool 
     */
	private $_bHasImage = false;
	/**
     *
     * @var array 
     */
	private $_aCategories = array();
    /**
     *
     * @var bool 
     */
	private $_bIsEndingInThePast = false;

    private $_sDefaultImageEventPath = '';

    private $_bIsAdvancedEventModule = false;

    public function __construct()
    {
        $this->_oReq = Mfox_Service_Request_Request::instance();
        $this->_oSearch = Mfox_Service_Search_Search::instance();
        $this->_oBrowse = Mfox_Service_Search_Browse::instance();

        $this->_sDefaultImageEventPath = Phpfox::getParam('core.url_module') . 'mfox/static/image/event_cover_default.jpg';
        
        $isUsing = Phpfox::getParam('mfox.replace_event');
        $isAdv = Phpfox::isModule('fevent');
        $isDefault = Phpfox::isModule('event');

        $this->_bIsAdvancedEventModule = Phpfox::getService('mfox.core')->isAdvancedModule($isUsing, $isAdv, $isDefault);
    }

    public function isAdvancedModule($aData = null)
    {
        if (!empty($aData) && isset($aData['sModelType'])) {
            return ($aData['sModelType'] == 'fevent');
        }

        $reqEventModuleId = $this->_oReq->get('event_module_id', 'event');
        if ($reqEventModuleId == 'fevent') {
            return true;
        }

        return $this->_bIsAdvancedEventModule;
    }

    public function getDefaultImageEventPath(){
        return $this->_sDefaultImageEventPath;
    }
	
    /**
     * Input data:
     * + iEventId: int, required.
     * + image[]: file, required.
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: bool.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/photoprofile
     * 
     * @param array $aData
     * @return array
     */
    public function photoprofile($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->photoprofile($aData);
        }
        
        if (!isset($aData['iEventId']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        if (Phpfox::isUser())
        {
            $this->database()->select('ei.invite_id, ei.rsvp_id, ')->leftJoin(Phpfox::getT('event_invite'), 'ei', 'ei.event_id = e.event_id AND ei.invited_user_id = ' . Phpfox::getUserId());
        }

        if (Phpfox::isModule('friend'))
        {
            $this->database()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f', "f.user_id = e.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        }
        else
        {
            $this->database()->select('0 as is_friend, ');
        }
        /**
         * @var array
         */
        $aEvent = $this->database()->select('e.*, ' . (Phpfox::getParam('core.allow_html') ? 'et.description_parsed' : 'et.description') . ' AS description, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('event'), 'e')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
                ->join(Phpfox::getT('event_text'), 'et', 'et.event_id = e.event_id')
                ->where('e.event_id = ' . (int) $aData['iEventId'])
                ->execute('getRow');

        if (!isset($aEvent['event_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('event.the_event_you_are_looking_for_does_not_exist_or_has_been_removed')
            );
        }
        
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('event', $aEvent['event_id'], $aEvent['user_id'], $aEvent['privacy'], $aEvent['is_friend'], true))
		{
			return array(
                'result' => 0, 
                'error_code' => 1, 
                'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time')
            );
		}

        // Delete old image.
        if (!empty($aEvent['image_path']))
        {
            if ($this->deleteImage($aEvent['event_id']))
            {
                
            }
        }
        /**
         * @var array|bool
         */
        $aImage = false;
        if (isset($_FILES['image']['name']) && ($_FILES['image']['name'] != ''))
        {
            $aImage = Phpfox::getLib('file')->load('image', array(
                'jpg',
                'gif',
                'png'
                    ), (Phpfox::getUserParam('event.max_upload_size_event') === 0 ? null : (Phpfox::getUserParam('event.max_upload_size_event') / 1024))
            );
        }
        if ($aImage === false)
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get())
            );
        }

        $oImage = Phpfox::getLib('image');
        /**
         * @var string
         */
        $sFileName = Phpfox::getLib('file')->upload('image', Phpfox::getParam('event.dir_image'), $aEvent['event_id']);
        /**
         * @var int
         */
        $iFileSizes = filesize(Phpfox::getParam('event.dir_image') . sprintf($sFileName, ''));

        $aSql['image_path'] = $sFileName;
        $aSql['server_id'] = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
        /**
         * @var int
         */
        $iSize = 50;
        $oImage->createThumbnail(Phpfox::getParam('event.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('event.dir_image') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);
        $iFileSizes += filesize(Phpfox::getParam('event.dir_image') . sprintf($sFileName, '_' . $iSize));

        $iSize = 120;
        $oImage->createThumbnail(Phpfox::getParam('event.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('event.dir_image') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);
        $iFileSizes += filesize(Phpfox::getParam('event.dir_image') . sprintf($sFileName, '_' . $iSize));

        $iSize = 200;
        $oImage->createThumbnail(Phpfox::getParam('event.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('event.dir_image') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);
        $iFileSizes += filesize(Phpfox::getParam('event.dir_image') . sprintf($sFileName, '_' . $iSize));

        // Update user space usage
        Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'event', $iFileSizes);
        /**
         * @var bool
         */
        $bResult = $this->database()->update(Phpfox::getT('event'), $aSql, 'event_id = ' . $aEvent['event_id']);

        if ($bResult)
        {
            return array(
                'result' => true,
                'error_code' => 0,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.upload_image_for_event_successfully"))
            );
        }
        else
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.upload_fail"))
            );
        }
    }
    /**
     * Delete image.
     * @param int $iId
     * @return boolean
     */
    public function deleteImage($iId)
	{
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->deleteImage($iId);
        }
        
		$aEvent = $this->database()->select('user_id, image_path')
			->from(Phpfox::getT('event'))
			->where('event_id = ' . (int) $iId)
			->execute('getRow');		
			
		if (!isset($aEvent['user_id']))
		{
			return Phpfox_Error::set('Unable to find the event.');
		}
			
		if (!Phpfox::getService('mfox.auth')->hasAccess('event', 'event_id', $iId, 'event.can_edit_own_event', 'event.can_edit_other_event', $aEvent['user_id']))
		{
			return Phpfox_Error::set( Phpfox::getPhrase('event.you_do_not_have_sufficient_permission_to_modify_this_event'));
		}			
		
		if (!empty($aEvent['image_path']))
		{
            /**
             * @var array
             */
			$aImages = array(
				Phpfox::getParam('event.dir_image') . sprintf($aEvent['image_path'], ''),
				Phpfox::getParam('event.dir_image') . sprintf($aEvent['image_path'], '_50'),
				Phpfox::getParam('event.dir_image') . sprintf($aEvent['image_path'], '_120'),
				Phpfox::getParam('event.dir_image') . sprintf($aEvent['image_path'], '_200')
			);			
			/**
             * @var int
             */
			$iFileSizes = 0;
			foreach ($aImages as $sImage)
			{
				if (file_exists($sImage))
				{
					$iFileSizes += filesize($sImage);
					
					Phpfox::getLib('file')->unlink($sImage);
				}
			}
			
			if ($iFileSizes > 0)
			{
				Phpfox::getService('user.space')->update($aEvent['user_id'], 'event', $iFileSizes, '-');
			}
		}

		$this->database()->update(Phpfox::getT('event'), array('image_path' => null), 'event_id = ' . (int) $iId);	
		
		return true;
	}
    
    /**
     * Input data:
     * + iEventId: int, optional.
     * 
     * Output data:
     * + iNumGoing: int.
     * + iNumAll: int.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/getnumberguestlist
     * 
     * @param array $aData
     * @return array
     */
    public function getnumberguestlist($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->getnumberguestlist($aData);
        }
        
        if (!isset($aData['iEventId']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        /**
         * @var array
         */
        $aEvent = Phpfox::getService('event')->getEvent((int) $aData['iEventId']);

        if (!isset($aEvent['event_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('event.the_event_you_are_looking_for_does_not_exist_or_has_been_removed')
            );
        }
        
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('event', $aEvent['event_id'], $aEvent['user_id'], $aEvent['privacy'], $aEvent['is_friend'], true))
		{
			return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
		}
        /**
         * @var int
         */
        $iTotalGoing = $this->database()
                ->select('count(ei.invite_id)')
                ->from(Phpfox::getT('event_invite'), 'ei')
                ->where('ei.event_id = ' . (int) $aData['iEventId'] . ' AND ei.rsvp_id = 1')
                ->execute('getslavefield');

        $iTotalMaybe = $this->database()
                ->select('count(ei.invite_id)')
                ->from(Phpfox::getT('event_invite'), 'ei')
                ->where('ei.event_id = ' . (int) $aData['iEventId'] . ' AND ei.rsvp_id = 2')
                ->execute('getslavefield');

        $iTotalNotAttending = $this->database()
                ->select('count(ei.invite_id)')
                ->from(Phpfox::getT('event_invite'), 'ei')
                ->where('ei.event_id = ' . (int) $aData['iEventId'] . ' AND ei.rsvp_id = 3')
                ->execute('getslavefield');
        /**
         * @var int
         */
        $iTotalAll = $this->database()
                ->select('count(ei.invite_id)')
                ->from(Phpfox::getT('event_invite'), 'ei')
                ->where('ei.event_id = ' . (int) $aData['iEventId'])
                ->execute('getslavefield');

				return array(
					'iNumGoing' => $iTotalGoing,
					'iNumMaybe' => $iTotalMaybe,
					'iNumNotAttending' => $iTotalNotAttending,
					'iNumAll' => $iTotalAll
				);
    }

    /**
     * Input data:
     * + iEventId: int, required.
     * + iPage: int, optional. Not use.
     * + iLastTime: int, optional.
     * + iAmountOfFeed: int, optional.
     * + sAction: string, optional. Ex: "more" or "new".
     * + iUserId: int, optional. Not use. Do not send it when get feet event detail
     * + sOrder: string, optional. Ex: "time_stamp" or "time_update".
     * 
     * Output data:
	 * + id: int.
	 * + iUserId: int.
	 * + sUsername: string.
	 * + UserProfileImg_Url: string.
	 * + sFullName: string.
	 * + bCanPostComment: bool.
	 * + timestamp: int.
	 * + Time: string.
	 * + TimeConverted: string.
	 * + sTypeId: string.
	 * + iItemId: int.
	 * + sPhotoUrl: string.
	 * + aAlbum: array (iAlbumId: int, sAlbumTitle: string)
	 * + bReadMore: bool.
	 * + sContent: string.
	 * + sDescription: string.
	 * + iLikeId: int.
     * + error_code: int.
     * + error_message: string.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/getfeed
     * 
     * @param array $aData {"iUserId":"1","LastFeedIdViewed":"1","amountOfFeed":"5"}
     * @return array
     */
    public function getfeed($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->getfeed($aData);
        }
        
        if (!isset($aData['iEventId']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        /**
         * @var array
         */
        $aEvent = Phpfox::getService('event')->getEvent((int) $aData['iEventId']);
        
        if (!isset($aEvent['event_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('event.the_event_you_are_looking_for_does_not_exist_or_has_been_removed')
            );
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('event', $aEvent['event_id'], $aEvent['user_id'], $aEvent['privacy'], $aEvent['is_friend'], true))
		{
			return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
		}
        /**
         * @var bool
         */
        $bCanPostComment = true;
        if (isset($aEvent['privacy_comment']) && $aEvent['user_id'] != Phpfox::getUserId() && !Phpfox::getUserParam('privacy.can_comment_on_all_items'))
        {
            switch ($aEvent['privacy_comment']) {
                // Everyone is case 0. Skipped.
                // Friends only
                case 1:
                    if (!Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aEvent['user_id']))
                    {
                        $bCanPostComment = false;
                    }
                    break;
                // Friend of friends
                case 2:
                    if (!Phpfox::getService('friend')->isFriendOfFriend($aEvent['user_id']))
                    {
                        $bCanPostComment = false;
                    }
                    break;
                // Only me
                case 3:
                    $bCanPostComment = false;
                    break;
            }
        }
        /**
         * @var array
         */
        $aCallback = false;
        if ($aEvent['item_id'] && Phpfox::hasCallback($aEvent['module_id'], 'viewEvent'))
        {
            $aCallback = Phpfox::callback($aEvent['module_id'] . '.viewEvent', $aEvent['item_id']);

            if ($aEvent['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aCallback['item_id'], 'event.view_browse_events'))
            {
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_view_this_item_due_to_privacy_settings"))
                );
            }
        }

        if (Phpfox::getUserId())
        {
            /**
             * @var bool
             */
            $bIsBlocked = Phpfox::getService('user.block')->isBlocked($aEvent['user_id'], Phpfox::getUserId());
            if ($bIsBlocked)
            {
                $bCanPostComment = false;
            }
        }
        /**
         * @var int
         */
        $iUserId = isset($aData['iUserId']) ? $aData['iUserId'] : 0;
        $bIsCustomFeedView = false;
        $sCustomViewType = null;
        /**
         * @var array
         */
        $aFeedCallback = array(
            'module' => 'event',
            'table_prefix' => 'event_',
            'ajax_request' => 'event.addFeedComment',
            'item_id' => $aData['iEventId'],
            'disable_share' => ($bCanPostComment ? false : true)
        );
        /**
         * @var bool
         */
        $bIsProfile = (is_numeric($iUserId) && $iUserId > 0);

        if ($bIsProfile)
        {
            define('PHPFOX_IS_USER_PROFILE', true);
        }

        if (defined('PHPFOX_IS_USER_PROFILE') && !Phpfox::getService('user.privacy')->hasAccess($iUserId, 'feed.view_wall'))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_can_not_view_feeds_because_of_having_privacy_problem"))
            );
        }

        if (defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'pages.share_updates'))
        {
            $aFeedCallback['disable_share'] = true;
        }
        /**
         * @var int
         */
        $iFeedPage = isset($aData['iPage']) ? $aData['iPage'] : 0;

        if ($bIsProfile)
        {
            if (!Phpfox::getService('user.privacy')->hasAccess($iUserId, 'feed.display_on_profile'))
            {
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_can_not_view_feeds_because_of_having_privacy_problem"))
                );
            }
        }
        if (isset($aData['sAction']) && $aData['sAction'] == 'new')
        {
            $aResults = Phpfox::getService('mfox.feed')->setCallback($aFeedCallback)->getNew($aData);
        }
        else
        {
            $aResults = Phpfox::getService('mfox.feed')->setCallback($aFeedCallback)->get($aData);
        }

        foreach($aResults as &$aResult) {
            $aResult['isAllowDislike'] = 0;
            $aResult['parentModuleId'] = 'event';
        }

        return $aResults;
    }

    /**
     * Input data:
     * + sUserStatus: string, required. User will send the message.
     * + iCallbackItemId: int, required. Even id.
     * + sCallbackModule: string, required. Ex: "event".
     * + bIsUserProfile: bool, optional. In profile or not.
     * + iProfileUserId: int, optional. profile user id.
     * + iGroupId: int, optional. Group user id. Not use.
     * + iIframe: int. optional. Not use.
     * + sMethod: string. optional. Not use.
     * 
     * Output data:
     * + iCommentId: int.
     * + result: int.
     * + error_code: int.
     * + error_message: string.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/addfeedcomment
     * 
     * @param array $aData
     * @return array
     */
    public function addfeedcomment($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->addfeedcomment($aData);
        }
        
        /**
         * @var array
         */
        $aVals = array(
            'user_status' => isset($aData['sContent']) ? $aData['sContent'] : '',
            'callback_item_id' => isset($aData['iSubjectId']) ? $aData['iSubjectId'] : '',
            'callback_module' => isset($aData['sCallbackModule']) ? $aData['sCallbackModule'] : 'event',
            'is_user_profile' => isset($aData['bIsUserProfile']) ? $aData['bIsUserProfile'] : 0,
            'profile_user_id' => isset($aData['iProfileUserId']) ? $aData['iProfileUserId'] : 0,
            'group_id' => isset($aData['iGroupId']) ? $aData['iGroupId'] : $aData['iCallbackItemId'],
            'iframe' => isset($aData['iIframe']) ? $aData['iIframe'] : 1,
            'method' => isset($aData['sMethod']) ? $aData['sMethod'] : 'simple'
        );

        if (Phpfox::getLib('parse.format')->isEmpty($aVals['user_status']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('user.add_some_text_to_share')
            );
        }
        /**
         * @var array
         */
        $aEvent = Phpfox::getService('event')->getForEdit($aVals['callback_item_id'], true);

        if (!isset($aEvent['event_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('event.unable_to_find_the_event_you_are_trying_to_comment_on')
            );
        }

        if (($iFlood = Phpfox::getUserParam('comment.comment_post_flood_control')) !== 0)
        {
            /**
             * @var array
             */
            $aFlood = array(
                'action' => 'last_post', // The SPAM action
                'params' => array(
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('event_feed'), // Database table we plan to check
                    'condition' => 'type_id = \'' . $this->database()->escape('event_comment') . '\' AND user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                )
            );

            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood))
            {
                return array(
                    'error_message' =>  Phpfox::getPhrase('comment.posting_a_comment_a_little_too_soon_total_time', array('total_time' => Phpfox::getLib('spam')->getWaitTime())),
                    'error_code' => 1,
                    'result' => 0
                );
            }
        }
        /**
         * @var bool
         */
        $bCanPostComment = true;
        if (isset($aEvent['privacy_comment']) && $aEvent['user_id'] != Phpfox::getUserId() && !Phpfox::getUserParam('privacy.can_comment_on_all_items'))
        {
            switch ($aEvent['privacy_comment']) {
                // Everyone is case 0. Skipped.
                // Friends only
                case 1:
                    if (!Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aEvent['user_id']))
                    {
                        $bCanPostComment = false;
                    }
                    break;
                // Friend of friends
                case 2:
                    if (!Phpfox::getService('friend')->isFriendOfFriend($aEvent['user_id']))
                    {
                        $bCanPostComment = false;
                    }
                    break;
                // Only me
                case 3:
                    $bCanPostComment = false;
                    break;
            }
        }
        /**
         * @var array
         */
        $aCallback = false;
        if ($aEvent['item_id'] && Phpfox::hasCallback($aEvent['module_id'], 'viewEvent'))
        {
            $aCallback = Phpfox::callback($aEvent['module_id'] . '.viewEvent', $aEvent['item_id']);

            if ($aEvent['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aCallback['item_id'], 'event.view_browse_events'))
            {
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_comment_this_item_due_to_privacy_settings"))
                );
            }
        }

        if (Phpfox::getUserId())
        {
            $bIsBlocked = Phpfox::getService('user.block')->isBlocked($aEvent['user_id'], Phpfox::getUserId());
            if ($bIsBlocked)
            {
                $bCanPostComment = false;
            }
        }

        if (!$bCanPostComment)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_comment_this_item_due_to_privacy_settings"))
            );
        }
        /**
         * @var string
         */
        $sLink = Phpfox::permalink('event', $aEvent['event_id'], $aEvent['title']);
        $aCallback = array(
            'module' => 'event',
            'table_prefix' => 'event_',
            'link' => $sLink,
            'email_user_id' => $aEvent['user_id'],
            'subject' =>  Phpfox::getPhrase('event.full_name_wrote_a_comment_on_your_event_title', array('full_name' => Phpfox::getUserBy('full_name'), 'title' => $aEvent['title'])),
            'message' =>  Phpfox::getPhrase('event.full_name_wrote_a_comment_on_your_event_message', array('full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aEvent['title'])),
            'notification' => 'event_comment',
            'feed_id' => 'event_comment',
            'item_id' => $aEvent['event_id']
        );

        $aVals['parent_user_id'] = $aVals['callback_item_id'];

        if (($iId = Phpfox::getService('feed.process')->callback($aCallback)->addComment($aVals)))
        {
            $this->database()->updateCounter('event', 'total_comment', 'event_id', $aEvent['event_id']);

            return array(
                'error_code' => 0,
                'iCommentId' => $iId,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.this_item_has_successfully_been_submitted"))
            );
        }
        else
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get())
            );
        }
    }
    /**
     * Input data:
     * + iEventId: int, required.
     * + iRSVP: int, optional.
     * + iAmountOfInvite:: int, optional.
     * + iLastInviteIdViewed: int, optional.
     * 
     * Output data:
     * + iInviteId: int.
     * + iEventId: int.
     * + iTypeId: int.
     * + iRSVP: int.
     * + iUserId: int.
     * + sFullName: string.
     * + sUserImage: string.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/viewgetlist
     * 
     * @param array $aData
     * @return array
     */
    public function viewguestlist($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->viewguestlist($aData);
        }

        if (!isset($aData['iEventId']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        /**
         * @var bool
         */
        $bUseId = true;

        if (Phpfox::isUser())
        {
            $this->database()->select('ei.invite_id, ei.rsvp_id, ')->leftJoin(Phpfox::getT('event_invite'), 'ei', 'ei.event_id = e.event_id AND ei.invited_user_id = ' . Phpfox::getUserId());
        }

        if (Phpfox::isModule('friend'))
        {
            $this->database()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f', "f.user_id = e.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        }
        else
        {
            $this->database()->select('0 as is_friend, ');
        }
        /**
         * @var array
         */
        $aEvent = $this->database()->select('e.*, c.name AS country_name, ' . (Phpfox::getParam('core.allow_html') ? 'et.description_parsed' : 'et.description') . ' AS description, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('event'), 'e')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
                ->join(Phpfox::getT('event_text'), 'et', 'et.event_id = e.event_id')
                ->leftJoin(Phpfox::getT('country'), 'c', 'c.country_iso = e.country_iso')
                ->where('e.event_id = ' . (int) $aData['iEventId'])
                ->execute('getRow');

        if (!isset($aEvent['event_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('event.the_event_you_are_looking_for_does_not_exist_or_has_been_removed')
            );
        }

		if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('event', $aEvent['event_id'], $aEvent['user_id'], $aEvent['privacy'], $aEvent['is_friend'], true))
		{
			return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
		}

        $iRsvp = isset($aData['iRSVP']) ? (int) $aData['iRSVP'] : 1;
        $iPage = 1;
        $iPageSize = isset($aData['iAmountOfMember']) ? (int) $aData['iAmountOfMember'] : 5;

        if (isset($aData['iLastInviteIdViewed']) && $aData['iLastInviteIdViewed'] > 0)
        {
            $sCountCond = ' AND invite_id > ' . (int) $aData['iLastInviteIdViewed'];
            $sGetCond = ' AND ei.invite_id > ' . (int) $aData['iLastInviteIdViewed'];
        }
        else
        {
            $sCountCond = '';
            $sGetCond = '';
        }
        /**
         * @var array
         */
        $aEvent = Phpfox::getService('event')->getEvent($aData['iEventId'], true);

        if (!isset($aEvent['event_id']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.event_is_not_valid"))
            );
        }
        /**
         * @var array
         */
        $aInvites = array();
        /**
         * @var int
         */
        $iCnt = $this->database()->select('COUNT(invite_id)')
                ->from(Phpfox::getT('event_invite'))
                ->where('event_id = ' . (int) $aEvent['event_id'] . ' AND rsvp_id = ' . (int) $iRsvp . $sCountCond)
                ->execute('getSlaveField');

        if ($iCnt)
        {
            $aInvites = $this->database()
                    ->select('ei.*, ' . Phpfox::getUserField())
                    ->from(Phpfox::getT('event_invite'), 'ei')
                    ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = ei.invited_user_id')
                    ->where('ei.event_id = ' . (int) $aEvent['event_id'] . ' AND ei.rsvp_id = ' . (int) $iRsvp . $sGetCond)
                    ->limit($iPage, $iPageSize, $iCnt)
                    ->order('ei.invite_id DESC')
                    ->execute('getSlaveRows');
        }

        /**
         * @var array
         */
        $aResult = array();
        foreach ($aInvites as $aInvite)
        {
            $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aInvite, '_50_square');
            
            $aResult[] = array(
                'iInviteId' => $aInvite['invite_id'],
                'iEventId' => $aInvite['event_id'],
                'iTypeId' => $aInvite['type_id'],
                'iRSVP' => $aInvite['rsvp_id'],
                'iUserId' => $aInvite['user_id'],
                'sFullName' => $aInvite['full_name'],
                'sUserImage' => $sUserImage,
                'sBigUserImage' => $sUserImage
            );
        }
        return $aResult;
    }

    /**
     * @see Event_Service_Process
     * Input data:
     * + iEventId: int, required.
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + message: string.
     * + result: bool.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/delete
     * 
     * @param array $aData
     * @return arra
     */
    public function delete($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->delete($aData);
        }
        
        if (!isset($aData['iEventId']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
		/**
         * @var int
         */
		$iEventId = (int) $aData['iEventId'];
		
        if ($this->deleteEvent($iEventId))
        {
            return array(
                'result' => true, 
                'message' =>  Phpfox::getPhrase('event.event_successfully_deleted')
            );
        }

        return array(
            'error_code' => 1,
            'error_message' => implode(' ', Phpfox_Error::get())
        );
    }
    
    /**
     * Using to delete event.
     * @param int $iId
     * @param array $aEvent
     * @return string
     */
	public function deleteEvent($iId, &$aEvent = null)
	{
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->deleteEvent($iId, $aEvent);
        }
        
        /**
         * @var mix
         */
		$mReturn = true;
		if ($aEvent === null)
		{
            /**
             * @var array
             */
			$aEvent = $this->database()->select('user_id, module_id, item_id, image_path, is_sponsor, is_featured')
				->from(Phpfox::getT('event'))
				->where('event_id = ' . (int) $iId)
				->execute('getRow');
			
			if ($aEvent['module_id'] == 'pages' && Phpfox::getService('pages')->isAdmin($aEvent['item_id']))
			{
				$mReturn = Phpfox::getService('pages')->getUrl($aEvent['item_id']) . 'event/';
			}
			else
			{
				if (!isset($aEvent['user_id']))
				{
					return Phpfox_Error::set( Phpfox::getPhrase('event.unable_to_find_the_event_you_want_to_delete'));
				}

				if (!Phpfox::getService('mfox.auth')->hasAccess('event', 'event_id', $iId, 'event.can_delete_own_event', 'event.can_delete_other_event', $aEvent['user_id']))
				{
					return Phpfox_Error::set( Phpfox::getPhrase('event.you_do_not_have_sufficient_permission_to_delete_this_listing'));
				}
			}
		}
		
		if (!empty($aEvent['image_path']))
		{
            /**
             * @var array
             */
			$aImages = array(
				Phpfox::getParam('event.dir_image') . sprintf($aEvent['image_path'], ''),
				Phpfox::getParam('event.dir_image') . sprintf($aEvent['image_path'], '_50'),
				Phpfox::getParam('event.dir_image') . sprintf($aEvent['image_path'], '_120'),
				Phpfox::getParam('event.dir_image') . sprintf($aEvent['image_path'], '_200')
			);			
			/**
             * @var int
             */
			$iFileSizes = 0;
			foreach ($aImages as $sImage)
			{
				if (file_exists($sImage))
				{
					$iFileSizes += filesize($sImage);
					
					Phpfox::getLib('file')->unlink($sImage);
				}
			}
			
			if ($iFileSizes > 0)
			{
				Phpfox::getService('user.space')->update($aEvent['user_id'], 'event', $iFileSizes, '-');
			}
		}
		
		(Phpfox::isModule('comment') ? Phpfox::getService('comment.process')->deleteForItem(null, $iId, 'event') : null);		
		(Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('event', $iId) : null);
		(Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('comment_event', $iId) : null);
		/**
         * @var array
         */
		$aInvites = $this->database()->select('invite_id, invited_user_id')
			->from(Phpfox::getT('event_invite'))
			->where('event_id = ' . (int) $iId)
			->execute('getSlaveRows');
			
		foreach ($aInvites as $aInvite)
		{
			(Phpfox::isModule('request') ? Phpfox::getService('request.process')->delete('event_invite', $aInvite['invite_id'], $aInvite['invited_user_id']) : false);			
		}		
		
		$this->database()->delete(Phpfox::getT('event'), 'event_id = ' . (int) $iId);
		$this->database()->delete(Phpfox::getT('event_text'), 'event_id = ' . (int) $iId);
		$this->database()->delete(Phpfox::getT('event_category_data'), 'event_id = ' . (int) $iId);
		$this->database()->delete(Phpfox::getT('event_invite'), 'event_id = ' . (int) $iId);
        /**
         * @var int
         */
		$iTotalEvent = $this->database()
                        ->select('total_event')
                        ->from(Phpfox::getT('user_field'))
                        ->where('user_id =' . (int)$aEvent['user_id'])->execute('getSlaveField');
        $iTotalEvent = $iTotalEvent -1;
        
		if ($iTotalEvent > 0)
		{
			$this->database()->update(Phpfox::getT('user_field'),
                        array('total_event' => $iTotalEvent),
                        'user_id = ' . (int)$aEvent['user_id']);
		}
        
		if (isset($aEvent['is_sponsor']) && $aEvent['is_sponsor'] == 1)
		{
			$this->cache()->remove('event_sponsored');
		}
		if (isset($aEvent['is_featured']) && $aEvent['is_featured'])
		{
			$this->cache()->remove('event_featured', 'substr');
		}
		
		return $mReturn;
	}
	
    /**
     * @see Event_Service_Process
     * 
     * Input data:
     * + iEventId: int, required.
     * + sCategory: string, required.
     * + start_year: int, required.
     * + end_year: int, required.
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * 
     * @param array $aData
     * @return array|bool
     */
    public function edit($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->edit($aData);
        }
        
        if (!isset($aData['iEventId']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        $aEvent = Phpfox::getService('event')->getForEdit($aData['iEventId']);

        if (!$aEvent)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.event_is_not_valid"))
            );
        }

        if (!$this->_verify($aVals))
        {
            return array(
                'error_code' => 1,
                'error_message' => implode('', Phpfox_Error::get())
            );
        }        

        $aCategories = array();
        if (isset($aData['category_id']))
        {
            $aTemp = explode(',', $aData['category_id']);
            foreach ($aTemp as $iCategory)
            {
                if (is_numeric($iCategory))
                {
                    $aCategories[] = $iCategory;
                }
            }
        }
        $aData['category'] = $aCategories;
        unset($aData['category_id']);
        unset($aData['iEventId']);

				if (!isset($aData['start_date']) 
					|| !isset($aData['start_time'])
					|| !isset($aData['end_date'])
				 	|| !isset($aData['end_time']) )
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.start_date_or_end_date_is_not_valid"))
            );
        } else {

					//2014-01-25 
					$aParts = explode('-', $aData['start_date']);
					$aData['start_year'] = $aParts[0];
					$aData['start_month'] = $aParts[1];
					$aData['start_day'] = $aParts[2];
					
					//20:18:00
					$aParts = explode(':', $aData['start_time']);
					$aData['start_hour'] = $aParts[0];
					$aData['start_minute'] = $aParts[1];
					
					//2014-01-25 
					$aParts = explode('-', $aData['end_date']);
					$aData['end_year'] = $aParts[0];
					$aData['end_month'] = $aParts[1];
					$aData['end_day'] = $aParts[2];
					
					//20:18:00
					$aParts = explode(':', $aData['end_time']);
					$aData['end_hour'] = $aParts[0];
					$aData['end_minute'] = $aParts[1];
				}

		$iLimitYear = date("Y") + 1;

		if ($aData['start_year'] >  $iLimitYear)
		{
			return array(
                'error_code' => 1,
                 Phpfox::getPhrase("mfox.start_year_must_be_less_than_or_equal_to_year", array('year'=>$iLimitYear)),

            );
		}
		$aData['end_year'] = isset($aData['end_year']) ? (int) $aData['end_year'] : $aData['start_year'];
		// Limit end year.
		if ($aData['end_year'] >  $iLimitYear)
		{
			return array(
                'error_code' => 1,
                'error_message'=> Phpfox::getPhrase("mfox.end_year_must_be_less_than_or_equal_to_year", array('year'=>$iLimitYear)),
            );
		}
		
			$aData['privacy'] = $aData['auth_view'];
			$aData['privacy_comment'] = $aData['auth_comment'];
            $bResult = Phpfox::getService('event.process')->update($aEvent['event_id'], $aData, $aEvent);

			if($bResult) {
                $this->addImage($aEvent['event_id']);
				return array(
                'error_code' => 0,
                                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.update_sucessfully")),
                                'iEventId' =>  $aEvent['event_id'],
								'sModelType' => 'event', 
								'result' => 1
            );

			} else {
				return array(
                'error_code' => 1,
                'error_message' => implode('', Phpfox_Error::get())
                );

			}
    }

    /**
     * Input data:
     * + iAmountOfEvent: int, optional.
     * + iPage: int, optional.
     * + bIsUserProfile: bool, optional. (In profile)
     * + iUserId: int, optional. Default 0. Number 0 is all friend.
     * + bCallback: bool, optional.
     * + iCallbackItem: int, optional.
     * + sView: string, optional.
     * + sModuleId: string, optional. (In page)
     * + iItemId: int, optional. (In page)
     * + iSponsor: int, optional.
     * + iCategoryId: int, optional.
     * + sWhen: string, optional.
     * 
     * Output data:
     * + iEventId: int.
     * + sTitle: string.
     * + bCanPostComment: bool.
     * + sEventImageUrl: string.
     * + sFullName: string.
     * + iUserId: int.
     * + sUserImageUrl: string.
     * + iStartTime: int.
     * + sStartTime: string.
     * + sStartFullTime: string.
     * + iEndTime: int.
     * + sEndTime: string.
     * + sEndFullTime: string.
     * + iTimeStamp: int.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + iTotalDislike: int.
     * + bIsSponsor: bool.
     * + bIsFeatured: bool.
     * + sCountryISO: string.
     * + sLocation: string.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/upcoming
     * 
     * @param array $aData
     * @return array
     */
    public function upcoming($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->upcoming($aData);
        }
        
				$aData['sWhen'] = 'upcoming';
        return $this->getEvents($aData);
    }

    /**
     * Input data:
     * + iAmountOfEvent: int, optional.
     * + iPage: int, optional.
     * + bIsUserProfile: bool, optional. (In profile)
     * + iUserId: int, optional. Default login user id.
     * + bCallback: bool, optional.
     * + iCallbackItem: int, optional.
     * + sView: string, optional. Default 'my'.
     * + sModuleId: string, optional. (In page)
     * + iItemId: int, optional. (In page)
     * + iSponsor: int, optional.
     * + iCategoryId: int, optional. Default -1.
     * + sWhen: string, optional. Default 'past'.
     * 
     * Output data:
     * + iEventId: int.
     * + sTitle: string.
     * + bCanPostComment: bool.
     * + sEventImageUrl: string.
     * + sFullName: string.
     * + iUserId: int.
     * + sUserImageUrl: string.
     * + iStartTime: int.
     * + sStartTime: string.
     * + sStartFullTime: string.
     * + iEndTime: int.
     * + sEndTime: string.
     * + sEndFullTime: string.
     * + iTimeStamp: int.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + iTotalDislike: int.
     * + bIsSponsor: bool.
     * + bIsFeatured: bool.
     * + sCountryISO: string.
     * + sLocation: string.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/past
     * 
     * @param array $aData
     * @return array
     */
    public function past($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->past($aData);
        }
        
        $aData['iUserId'] = Phpfox::getUserId();
        if (!isset($aData['sWhen']))
        {
            $aData['sWhen'] = 'past';
        }
        return $this->getEvents($aData);
    }
    
     /**
     * Input data:
     * + iAmountOfEvent: int, optional.
     * + iPage: int, optional.
     * + bIsUserProfile: bool, optional. (In profile)
     * + iUserId: int, optional. Default 0. Number 0 is all friend.
     * + bCallback: bool, optional.
     * + iCallbackItem: int, optional.
     * + sView: string, optional. Default 'friend'.
     * + sModuleId: string, optional. (In page)
     * + iItemId: int, optional. (In page)
     * + iSponsor: int, optional.
     * + iCategoryId: int, optional. Default -1.
     * + sWhen: string, optional. Default 'upcoming'.
     * 
     * Output data:
     * + iEventId: int.
     * + sTitle: string.
     * + bCanPostComment: bool.
     * + sEventImageUrl: string.
     * + sFullName: string.
     * + iUserId: int.
     * + sUserImageUrl: string.
     * + iStartTime: int.
     * + sStartTime: string.
     * + sStartFullTime: string.
     * + iEndTime: int.
     * + sEndTime: string.
     * + sEndFullTime: string.
     * + iTimeStamp: int.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + iTotalDislike: int.
     * + bIsSponsor: bool.
     * + bIsFeatured: bool.
     * + sCountryISO: string.
     * + sLocation: string.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/friend
     * 
     * @param array $aData
     * @return array
     */
    public function friend($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->friend($aData);
        }
        
        $aData['sView'] = 'friend';
        if (!isset($aData['sWhen']))
        {
            $aData['sWhen'] = 'upcoming';
        }

        return $this->getEvents($aData);
    }

    /**
     * Input data:
     * + iAmountOfEvent: int, optional.
     * + iPage: int, optional.
     * + bIsUserProfile: bool, optional. (In profile)
     * + iUserId: int, optional. (In profile)
     * + bCallback: bool, optional.
     * + iCallbackItem: int, optional.
     * + sView: string, optional.
     * + sModuleId: string, optional. (In page)
     * + iItemId: int, optional. (In page)
     * + iSponsor: int, optional.
     * + iCategoryId: int, optional. Default -1.
     * + sWhen: string, optional. Default ''.
     * 
     * Output data:
     * + iEventId: int.
     * + sTitle: string.
     * + bCanPostComment: bool.
     * + sEventImageUrl: string.
     * + sFullName: string.
     * + iUserId: int.
     * + sUserImageUrl: string.
     * + iStartTime: int.
     * + sStartTime: string.
     * + sStartFullTime: string.
     * + iEndTime: int.
     * + sEndTime: string.
     * + sEndFullTime: string.
     * + iTimeStamp: int.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + iTotalDislike: int.
     * + bIsSponsor: bool.
     * + bIsFeatured: bool.
     * + sCountryISO: string.
     * + sLocation: string.
     * 
     * @param array $aData
     * @return array
     */
    private function getEvents($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->getEvents($aData);
        }

        $this->_oReq->set(array(
            'view' => !empty($aData['sView']) ? $aData['sView'] : '',
            'page' => !empty($aData['iPage']) ? (int) $aData['iPage'] : 1,
            'show' => !empty($aData['iAmountOfEvent']) ? (int) $aData['iAmountOfEvent'] : 10,
            'category' => !empty($aData['iCatSearch']) ? $aData['iCatSearch'] : null,
            'search' => !empty($aData['sSearch']) ? $aData['sSearch'] : '',
            'sort' => !empty($aData['sOrder']) ? $aData['sOrder'] : '',
            'module_id' => !empty($aData['sModule']) ? $aData['sModule'] : null,
            'item_id' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : null,
            'profile' => (!empty($aData['bIsUserProfile']) && $aData['bIsUserProfile'] !== 'false') ? true : false,
            'profile_id' => !empty($aData['iUserId']) ? (int) $aData['iUserId'] : null,
            'when' => !empty($aData['sWhen']) ? $aData['sWhen'] : null,
        ));

        // prepare view
        if ($this->_oReq->get('view') == 'upcoming')
        {
            $this->_oReq->set('view', '');
        }

        $sTmpCategory = $this->_oReq->get('category', null);
        if (!empty($sTmpCategory))
        {
            switch ($sTmpCategory)
            {
                case 'attending':
                    $this->_oReq->set('view', 'attending');
                    break;
                case 'may_attend':
                    $this->_oReq->set('view', 'may-attend');
                    break;
                case 'not_attending':
                    $this->_oReq->set('view', 'not-attending');
                    break;
                case 'invites':
                    $this->_oReq->set('view', 'invites');
                    break;
                default:
                    
                    break;
            }
        }

        if (!is_numeric($sTmpCategory))
        {
            $this->_oReq->set('category', null);
        }

        Phpfox::getUserParam('event.can_access_event', true);
        
        $aParentModule = null;
        $sModuleId = $this->_oReq->get('module_id');
        $iItemId = $this->_oReq->get('item_id');
        if (!empty($sModuleId) && !empty($iItemId))
        {
            $aParentModule = array(
                'module_id' => $sModuleId,
                'item_id' => $iItemId
            );
        }

        $bIsUserProfile = false;
        if ($this->_oReq->get('profile') === true)
        {
            $bIsUserProfile = true;
            $aUser = Phpfox::getService('user')->get($this->_oReq->get('profile_id'));
        }       

        $oServiceEventBrowse = $this;
        $sCategory = null;
        $sView = $this->_oReq->get('view', false);
        $aCallback = false;           

        // search_tool
        $this->_oSearch->setSearchTool(array(
            'default_when' => 'upcoming',
            'when_field' => 'start_time',
            'when_upcoming' => true,
            'when_ongoing' => true,
            'table_alias' => 'm',
        ));
        
        // search
        $sSearch = $this->_oReq->get('search');
        if (!empty($sSearch))
        {
            $this->_oSearch->setCondition('AND m.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"');
        }

        // sort
        $sSort = '';
        switch ($this->_oReq->get('sort')) {
            case 'most_liked':
                $sSort = 'm.total_like DESC';
                break;
            case 'most_discussed':
                $sSort = 'm.total_comment DESC';
                break;
            default:
                $sSort = 'm.start_time ASC';
                break;
        }

        $this->_oSearch->setSort($sSort);

        $aBrowseParams = array(
            'module_id' => 'event',
            'alias' => 'm',
            'field' => 'event_id',
            'table' => Phpfox::getT('event'),
            'hide_view' => array('pending', 'my'),
            'service' => 'mfox.event'
        );      
        
        switch ($sView)
        {
            case 'pending':
                if (Phpfox::getUserParam('event.can_approve_events'))
                {
                    $this->_oSearch->setCondition('AND m.view_id = 1');
                }
                break;
            case 'my':
                Phpfox::isUser(true);
                $this->_oSearch->setCondition('AND m.user_id = ' . Phpfox::getUserId());
                break;
            default:
                if ($bIsUserProfile)
                {                   
                    $this->_oSearch->setCondition('AND m.view_id ' . ($aUser['user_id'] == Phpfox::getUserId() ? 'IN(0,2)' : '= 0') . ' AND m.module_id = "event" AND m.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ') AND m.user_id = ' . (int) $aUser['user_id']);
                }
                elseif ($aParentModule !== null)
                {
                    $this->_oSearch->setCondition('AND m.view_id = 0 AND m.privacy IN(%PRIVACY%) AND m.module_id = \'' . Phpfox_Database::instance()->escape($aParentModule['module_id']) . '\' AND m.item_id = ' . (int) $aParentModule['item_id'] . '');
                }
                else
                {           
                    switch ($sView)
                    {
                        case 'attending':               
                            $oServiceEventBrowse->attending(1);
                            break;
                        case 'may-attend':              
                            $oServiceEventBrowse->attending(2);
                            break;  
                        case 'not-attending':               
                            $oServiceEventBrowse->attending(3);
                            break;
                        case 'invites':             
                            $oServiceEventBrowse->attending(0);
                            break;                          
                    }                       
                    
                    if ($sView == 'attending')
                    {
                        $this->_oSearch->setCondition('AND m.view_id = 0 AND m.privacy IN(%PRIVACY%)');
                    }
                    else
                    {
                        $this->_oSearch->setCondition('AND m.view_id = 0 AND m.privacy IN(%PRIVACY%) AND m.item_id = ' . ($aCallback !== false ? (int) $aCallback['item'] : 0) . '');
                    }
                    
                    if ($this->_oReq->getInt('user') && ($aUserSearch = Phpfox::getService('user')->getUser($this->_oReq->getInt('user'))))
                    {
                        $this->_oSearch->setCondition('AND m.user_id = ' . (int) $aUserSearch['user_id']);
                        $this->template()->setBreadcrumb($aUserSearch['full_name'] . '\'s Events', $this->url()->makeUrl('event', array('user' => $aUserSearch['user_id'])), true);
                    }
                }
                break;
        }
        
        if ($this->_oReq->getInt('sponsor') == 1)
        {
            $this->_oSearch->setCondition('AND m.is_sponsor != 1');
        }           
        
        $sCategory = $this->_oReq->get('category', null);
        if (!empty($sCategory))
        {
            $this->_oSearch->setCondition('AND mcd.category_id = ' . (int) $sCategory);
        }
        
        if ($sView == 'featured')
        {
            $this->_oSearch->setCondition('AND m.is_featured = 1');
        }       

        $oServiceEventBrowse->callback($aCallback)->category($sCategory);   

        $this->_oBrowse->params($aBrowseParams)->execute();

        $aItems = $this->_oBrowse->getRows();

        return $aItems;
    }

    public function processRows(&$aRows)
    {
        $aEvents = $aRows;
        $aRows = array();

        foreach ($aEvents as $aEvent)
        {
            if ($aEvent['image_path'] == null)
            {
                $sEventImageUrl = $this->_sDefaultImageEventPath;
                $sEventBigImageUrl = $this->_sDefaultImageEventPath;
            }
            else
            {
                $sEventImageUrl = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aEvent['server_id'],
                    'path' => 'event.url_image',
                    'file' => $aEvent['image_path'],
                    'suffix' => '',
                    'return_url' => true
                        )
                );
                $sEventBigImageUrl = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aEvent['server_id'],
                    'path' => 'event.url_image',
                    'file' => $aEvent['image_path'],
                    'suffix' => '',
                    'return_url' => true
                        )
                );   
            }

            $bCanView = true;
            if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('event', $aEvent['event_id'], $aEvent['user_id'], $aEvent['privacy'], $aEvent['is_friend'] == NULL ? false : true, true))
            {
                $bCanView = false;
            }
            
            $iStartTimeUser = Phpfox::getService('mfox.core')->convertToUserTimeZone((int)$aEvent['start_time']);
            $aStartTimeYear = date('Y', $iStartTimeUser);
            $aStartTimeMonth = date('M', $iStartTimeUser);
            $aStartTimeDay = date('d', $iStartTimeUser);
            $aStartTime = array(
                'iYear' => $aStartTimeYear, 
                'iMonth' => $aStartTimeMonth, 
                'iDay' => $aStartTimeDay, 
                );
                
            $bCanEdit  = 0;
            $bCanDelete = 0;
            $bIsPages = false;
            $bIsOwner = 0;
            
            if ($aEvent['user_id'] == Phpfox::getUserId())
            {
                $bIsOwner =  1;
            }
            else if ($aEvent['module_id'] == 'pages' && $aEvent['item_id'] > 0)
            {
                $bIsPages =  true;
                $bIsOwner = Phpfox::getService('pages')->isAdmin(intval($aEvent['item_id']));
            }
            
            if (($bIsOwner && Phpfox::getUserParam('event.can_edit_own_event'))
            || (!$bIsOwner && Phpfox::getUserParam('event.can_edit_other_event'))){
                $bCanEdit =  1;
            }
            
            if (($bIsOwner && Phpfox::getUserParam('event.can_delete_own_event'))
            || (!$bIsOwner && Phpfox::getUserParam('event.can_delete_other_event'))){
                $bCanDelete =  1;
            }   
            
            $bCanPostComment  =  Phpfox::getService('mfox.comment')->checkCanPostCommentOnItem($aEvent)?1:0;
            
            $aRows[] = array(
                'bCanView' => $bCanView,
                'bIsResourceApproval' => $aEvent['view_id'] == 1 ? true : false,
                'bOnRequest' => 0, //@todo: investigate what is it
                'iNumOfMember' => Phpfox::getService('mfox.helper.event')->getNumberOfGuestOfEvent($aEvent['event_id']),
                'iRsvp' => $aEvent['rsvp_id'],
                'bIsMember' => (!isset($aEvent['rsvp_id']) || $aEvent['rsvp_id'] == null) ? false : true,
                'sEventBigImageUrl' => $sEventBigImageUrl,
                'iEventId' => $aEvent['event_id'],
                'iProfilePageId' => $aEvent['profile_page_id'],
                'sTitle' => $aEvent['title'],
                'bCanPostComment' => $bCanPostComment,
                'sEventImageUrl' => $sEventImageUrl,
                'sFullName' => $aEvent['full_name'],
                'iUserId' => $aEvent['user_id'],
                'sUserImageUrl' => Phpfox::getService('mfox.user')->getImageUrl($aEvent, '_50_square'),
                'aStartTime' => $aStartTime,
                'iStartTime' => $aEvent['start_time'],
                'sStartTime' => date('l, F j', $aEvent['start_time']),
                'sStartFullTime' => date('l, F j', $aEvent['start_time']) . ' at ' . date('g:i a', $aEvent['start_time']),
                'sShortStartTime' => date('g:i A', $aEvent['start_time']),
                'iEndTime' => $aEvent['end_time'],
                'sEndTime' => date('l, F j', $aEvent['end_time']),
                'sEndFullTime' => date('l, F j', $aEvent['end_time'])  . ' at ' . date('g:i a', $aEvent['end_time']),
                'iTimeStamp' => $aEvent['time_stamp'],
                'iTotalComment' => $aEvent['total_comment'],
                'iTotalLike' => $aEvent['total_like'],
                'iTotalDislike' => $aEvent['total_dislike'],
                'bIsSponsor' => $aEvent['is_sponsor'],
                'bIsFeatured' => $aEvent['is_featured'],
                'sCountryISO' => $aEvent['country_iso'],
                'sLocation' => $aEvent['location'], 
                'sModelType' => 'event', 
                'sDescription' => Phpfox::getLib('parse.output')->parse($aEvent['text']), 
                'sUserTimezone' => Phpfox::getLib('date')->getTimeZone(),
                'bCanPostComment' => $bCanPostComment, 
                'bIsOwner'=>$bIsOwner,
                'bCanComment'=>$bCanPostComment,
                'bCanEdit' => $bCanEdit,
                'bCanDelete' => $bCanDelete,
                'bIsPages'=>$bIsPages,       
            );
        }
    }
    
    public function category($sCategory)
    {
        $this->_sCategory = $sCategory;
        
        return $this;
    }
    
    public function attending($iAttending)
    {
        $this->_iAttending = $iAttending;
        
        return $this;
    }
    
    public function callback($aCallback)
    {
        $this->_aCallback = $aCallback;
        
        return $this;
    }
    
    public function query()
    {
        if ($this->_iAttending !== null)
        {
            $this->database()->group('m.event_id');
        }
        
        if (Phpfox::isUser() && Phpfox::isModule('like'))
        {
            $this->database()->select('lik.like_id AS is_liked, ')
                    ->leftJoin(Phpfox::getT('like'), 'lik', 'lik.type_id = \'event\' AND lik.item_id = m.event_id AND lik.user_id = ' . Phpfox::getUserId());
        }           
    }
    
    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Mfox_Service_Friend::instance()->queryJoin($bNoQueryFriend))
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = m.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());  
        }               

        if ($this->_sCategory !== null)
        {       
            $this->database()->innerJoin(Phpfox::getT('event_category_data'), 'mcd', 'mcd.event_id = m.event_id');
            
            if (!$bIsCount)
            {
                $this->database()->group('m.event_id');
            }
        }                   
        
        if ($this->_iAttending !== null)
        {
            $this->database()->innerJoin(Phpfox::getT('event_invite'), 'ei', 'ei.event_id = m.event_id AND ei.rsvp_id = ' . (int) $this->_iAttending . ' AND ei.invited_user_id = ' . Phpfox::getUserId());
            
            if (!$bIsCount)
            {
                $this->database()->select('ei.rsvp_id, ');
                $this->database()->group('m.event_id');
            }           
        }
        else 
        {
            if (Phpfox::isUser())
            {
                $this->database()->leftJoin(Phpfox::getT('event_invite'), 'ei', 'ei.event_id = m.event_id AND ei.invited_user_id = ' . Phpfox::getUserId());
                
                if (!$bIsCount)
                {
                    $this->database()->select('ei.rsvp_id, ');
                    $this->database()->group('m.event_id');
                }                   
            }
        }
    }

    /**
     * Input data:
     * + iAmountOfEvent: int, optional.
     * + iPage: int, optional.
     * + bIsUserProfile: bool, optional. (In profile)
     * + iUserId: int, optional. Default login user id. Number 0 is all friend.
     * + bCallback: bool, optional.
     * + iCallbackItem: int, optional.
     * + sView: string, optional. Default 'my'.
     * + sModuleId: string, optional. (In page)
     * + iItemId: int, optional. (In page)
     * + iSponsor: int, optional.
     * + iCategoryId: int, optional. Default -1.
     * + sWhen: string, optional. Default 'upcoming'.
     * 
     * Output data:
     * + iEventId: int.
     * + sTitle: string.
     * + bCanPostComment: bool.
     * + sEventImageUrl: string.
     * + sFullName: string.
     * + iUserId: int.
     * + sUserImageUrl: string.
     * + iStartTime: int.
     * + sStartTime: string.
     * + sStartFullTime: string.
     * + iEndTime: int.
     * + sEndTime: string.
     * + sEndFullTime: string.
     * + iTimeStamp: int.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + iTotalDislike: int.
     * + bIsSponsor: bool.
     * + bIsFeatured: bool.
     * + sCountryISO: string.
     * + sLocation: string.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/my
     * 
     * @param array $aData
     * @return array
     */
    public function my($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->my($aData);
        }
        
        $aData['iUserId'] = Phpfox::getUserId();
        $aData['sView'] = 'my';
        if (!isset($aData['sWhen']))
        {
            $aData['sWhen'] = 'all-time';
        }
        return $this->getEvents($aData);
    }

    /**
     * Input data: N/A
     * 
     * Output data:
     * + iCategoryId: int.
     * + sName: string.
     * + iParentId: int.
     * + aChild: array
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/listcategories
     * 
     * @param array $aData
     * @return array
     */
    public function listcategories($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->listcategories($aData);
        }
        
        /**
         * @var array
         */
        $aParentCategories = $this->database()->select('category_id AS iCategoryId, name AS sName, parent_id AS iParentId')
                ->from(Phpfox::getT('event_category'))
                ->where('parent_id = 0 AND is_active = 1')
                ->order('ordering ASC')
                ->execute('getRows');
				
		if(!empty($aParentCategories)){
			foreach($aParentCategories as $index=>$row){
				$aParentCategories[$index]['sName'] =  html_entity_decode(Phpfox::getLib('locale')->convert(Core\Lib::phrase()->isPhrase($row['sName']) ? _p($row['sName']) : $row['sName']));
			}
		}
        /**
         * @var array
         */
        $aChildCategories = $this->database()->select('category_id AS iCategoryId, name AS sName, parent_id AS iParentId')
                ->from(Phpfox::getT('event_category'))
                ->where('parent_id != 0 AND is_active = 1')
                ->order('ordering ASC')
                ->execute('getRows');
				
		if(!empty($aChildCategories)){
			foreach($aChildCategories as $index=>$row){
				$aChildCategories[$index]['sName'] = html_entity_decode( Phpfox::getLib('locale')->convert(Core\Lib::phrase()->isPhrase($row['sName']) ? _p($row['sName']) : $row['sName']));
			}
		}
        /**
         * @var int
         */
        $iTotal = count($aParentCategories);
        for ($i = 0; $i < $iTotal; $i++)
        {
            $aTemp = array();
            foreach ($aChildCategories as $aCategory)
            {
                if ($aParentCategories[$i]['iCategoryId'] == $aCategory['iParentId'])
                {
                    $aTemp[] = $aCategory;
                }
            }

            $aParentCategories[$i]['aChild'] = $aTemp;
        }

        return $aParentCategories;
    }

    /**
     * Input data:
     * + sCategory: string, required.
     * + title: string, required.
     * + description: string, optional.
     * + location: string, optional.
     * + start_month: int, required.
     * + start_day: int, required.
     * + start_year: int, required.
     * + start_hour: int, required.
     * + start_minute: int, required.
     * + end_month: int, optional.
     * + end_day: int, optional.
     * + end_year: int, optional.
     * + end_hour: int, optional.
     * + end_minute: int, optional.
     * + address: string, optional.
     * + city: string, optional.
     * + postal_code: string, optional.
     * + country_iso: string, optional.
     * + privacy: int, optional.
     * + privacy_comment: int, optional.
     * + emails: string, optional.
     * + personal_message: string, optional.
     * + image: file, optional.
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + iEventId: int.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/create
     * 
     * @see Phpfox_Parse_Format
     * @param array $aData
     * @return array
     */
    public function create($aData)
    {
        if (isset($aData['sModule']) && $aData['sModule'] == 'directory') {
            if (!isset($aData['iItemId'])) {
                return array(
                    'error_code' => 1,
                    'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
                );
            }

            if ('fevent' == Phpfox::getService('directory.helper')->getModuleIdEvent()) {
                return Phpfox::getService('mfox.fevent')->create($aData);
            }
        }

        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->create($aData);
        }
        
		$iLimitYear = date("Y") + 1;
		
        // support pages
        $aParentModule = array(
            'module_id' => isset($aData['sModule']) ? $aData['sModule'] : '',
            'item_id' => isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0
        );
        $aCallback = false;     
        $sModule = false;
        $iItem = false;
        if ((int)$aParentModule['item_id'] > 0){
            $sModule = $aParentModule['module_id'];
            $iItem = $aParentModule['item_id'];
        }
        if ($sModule && $iItem && Phpfox::hasCallback($sModule, 'viewEvent'))
        {
            $aCallback = Phpfox::callback($sModule . '.viewEvent', $iItem);     
            if ($sModule == 'pages' && !Phpfox::getService('pages')->hasPerm($iItem, 'event.share_events'))
            {
                return array(
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getPhrase('event.unable_to_view_this_item_due_to_privacy_settings')
                );
            }               
        }       

        if (!isset($aData['category_id']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.category_is_not_valid"))
            );
        }

        $aData['category'] = explode(',', $aData['category_id']);
        if (count($aData['category']) == 0)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.category_is_not_valid"))
            );
        }

        if (!isset($aData['title']) || Phpfox::getLib('parse.format')->isEmpty($aData['title']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.title_is_not_valid"))
            );
        }

        if (!isset($aData['description']))
        {
            $aData['description'] = '';
        }


        if (!isset($aData['start_date'])
					|| !isset($aData['start_time'])
					|| !isset($aData['end_date'])
				 	|| !isset($aData['end_time']) )
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('mfox.start_date_or_end_date_invalid')
            );
        } else {

					//2014-01-25 
					$aParts = explode('-', $aData['start_date']);
					$aData['start_year'] = $aParts[0];
					$aData['start_month'] = $aParts[1];
					$aData['start_day'] = $aParts[2];
					
					//20:18:00
					$aParts = explode(':', $aData['start_time']);
					$aData['start_hour'] = $aParts[0];
					$aData['start_minute'] = $aParts[1];
					
					//2014-01-25 
					$aParts = explode('-', $aData['end_date']);
					$aData['end_year'] = $aParts[0];
					$aData['end_month'] = $aParts[1];
					$aData['end_day'] = $aParts[2];
					
					//20:18:00
					$aParts = explode(':', $aData['end_time']);
					$aData['end_hour'] = $aParts[0];
					$aData['end_minute'] = $aParts[1];
				}

				if ($aData['start_year'] >  $iLimitYear)
				{
					return array(
                        'error_code' => 1,
                        'error_message'=> Phpfox::getPhrase("mfox.start_year_must_be_less_than_or_equal_to_year", array('year'=>$iLimitYear)),
						);
				}

				// Limit end year.
				if ($aData['end_year'] >  $iLimitYear)
				{
					return array(
                        'error_code' => 1,
                        'error_message'=> Phpfox::getPhrase("mfox.end_year_must_be_less_than_or_equal_to_year", array('year'=>$iLimitYear)),
						);
				}
		
        if (!isset($aData['address']))
        {
            $aData['address'] = '';
        }
        if (!isset($aData['city']))
        {
            $aData['city'] = '';
        }
        if (!isset($aData['postal_code']))
        {
            $aData['postal_code'] = '';
        }
        if (!isset($aData['country_iso']))
        {
            $aData['country_iso'] = '';
        }
        if (!isset($aData['privacy']))
        {
            $aData['privacy'] = 0;
        }
        if (!isset($aData['privacy_comment']))
        {
            $aData['privacy_comment'] = 0;
        }
        if (!isset($aData['emails']))
        {
            $aData['emails'] = '';
        }
        if (!isset($aData['personal_message']))
        {
            $aData['personal_message'] = '';
        }
        if (!isset($aData['image']))
        {
            $aData['image'] = null;
        }

        if (($iFlood = Phpfox::getUserParam('event.flood_control_events')) !== 0)
        {
            $aFlood = array(
                'action' => 'last_post', // The SPAM action
                'params' => array(
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('event'), // Database table we plan to check
                    'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);	
                )
            );

            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood))
            {
                Phpfox_Error::set( Phpfox::getPhrase('event.you_are_creating_an_event_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
            }
        }

        if (Phpfox_Error::isPassed())
        {
            $iId = $this->add($aData, ($aCallback !== false ? $sModule : 'event'), ($aCallback !== false ? $iItem : 0));
            if($iId === false){
                return array(
                    'error_code' => 1,
                    'error_message' => implode('', Phpfox_Error::get())
                );
            }

            return array(
                'iEventId' => $iId, 
                'sModelType' => 'event', 
            );
        }

        return array(
            'error_code' => 1,
            'error_message' => implode(' ', Phpfox_Error::get())
        );
    }
    /**
     * Verify data when add event.
     * @param array $aVals
     * @param bool $bIsUpdate
     * @return boolean
     */
	private function _verify(&$aVals, $bIsUpdate = false)
	{
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->_verify($aVals, $bIsUpdate);
        }
        
		if (isset($aVals['category']) && is_array($aVals['category']))
		{
			foreach ($aVals['category'] as $iCategory)
			{		
				if (empty($iCategory))
				{
					continue;
				}

				if (!is_numeric($iCategory))
				{
					continue;
				}			

				$this->_aCategories[] = $iCategory;
			}
		}
		
        

		if (isset($_FILES['image']['name']) && ($_FILES['image']['name'] != ''))
		{
            

			$aImage = Phpfox::getLib('file')->load('image', array(
					'jpg',
					'gif',
					'png'
				), (Phpfox::getUserParam('event.max_upload_size_event') === 0 ? null : (Phpfox::getUserParam('event.max_upload_size_event') / 1024))
			);
			if ($aImage === false)
			{
				return false;
			}
			
			$this->_bHasImage = true;
		}
		
		if (true)
		{			
            /**
             * @var int
             */
			$iStartTime = Phpfox::getLib('date')->mktime($aVals['start_hour'], $aVals['start_minute'], 0, $aVals['start_month'], $aVals['start_day'], $aVals['start_year']);
            /**
             * @var int
             */
			$iEndTime = Phpfox::getLib('date')->mktime($aVals['end_hour'], $aVals['end_minute'], 0, $aVals['end_month'], $aVals['end_day'], $aVals['end_year']);			
			
			if ($iEndTime < $iStartTime)
			{
				$this->_bIsEndingInThePast = true;
			}
		}
		
		return true;
	}
	/**
     * Add new event.
     * @param array $aVals
     * @param string $sModule
     * @param int $iItem
     * @return boolean
     */
	public function add($aVals, $sModule = 'event', $iItem = 0)
	{		
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->add($aVals, $sModule, $iItem);
        }
        
		if (!$this->_verify($aVals))
		{
			return false;
		}
		
		if (!isset($aVals['privacy']))
		{
			$aVals['privacy'] = 0;
		}
		
		$oParseInput = Phpfox::getLib('parse.input');
		
		if (!Phpfox::getService('mfox.ban')->checkAutomaticBan($aVals))
		{
			return false;
		}
		$iStartTime = Phpfox::getLib('date')->mktime($aVals['start_hour'], $aVals['start_minute'], 0, $aVals['start_month'], $aVals['start_day'], $aVals['start_year']);		
		if ($this->_bIsEndingInThePast === true)
		{
			$aVals['end_hour'] = ($aVals['start_hour'] + 1);
			$aVals['end_minute'] = $aVals['start_minute'];
			$aVals['end_day'] = $aVals['start_day'];
			$aVals['end_year'] = $aVals['start_year'];			
		}
		$iEndTime = Phpfox::getLib('date')->mktime($aVals['end_hour'], $aVals['end_minute'], 0, $aVals['end_month'], $aVals['end_day'], $aVals['end_year']);				
		
		if ($iStartTime > $iEndTime)
		{
			$iEndTime = $iStartTime;
		}
		$aSql = array(
			'view_id' => (($sModule == 'event' && Phpfox::getUserParam('event.event_must_be_approved')) ? '1' : '0'),
			'privacy' => (isset($aVals['auth_view']) ? $aVals['auth_view'] : '0'),
			'privacy_comment' => (isset($aVals['auth_comment']) ? $aVals['auth_comment'] : '0'),
			'module_id' => $sModule,
			'item_id' => $iItem,
			'user_id' => Phpfox::getUserId(),
			'title' => $oParseInput->clean($aVals['title'], 255),
			'location' => $oParseInput->clean($aVals['location'], 255),
			'country_iso' => (empty($aVals['country_iso']) ? Phpfox::getUserBy('country_iso') : $aVals['country_iso']),
			'country_child_id' => (isset($aVals['country_child_id']) ? (int) $aVals['country_child_id'] : 0),
			'postal_code' => (empty($aVals['postal_code']) ? null : Phpfox::getLib('parse.input')->clean($aVals['postal_code'], 20)),
			'city' => (empty($aVals['city']) ? null : $oParseInput->clean($aVals['city'], 255)),
			'time_stamp' => PHPFOX_TIME,
			'start_time' => Phpfox::getLib('date')->convertToGmt($iStartTime),
			'end_time' => Phpfox::getLib('date')->convertToGmt($iEndTime),
			'start_gmt_offset' => Phpfox::getLib('date')->getGmtOffset($iStartTime),
			'end_gmt_offset' => Phpfox::getLib('date')->getGmtOffset($iEndTime),
			'address' => (empty($aVals['address']) ? null : Phpfox::getLib('parse.input')->clean($aVals['address']))
		);
		if (Phpfox::getUserParam('event.can_add_gmap') && isset($aVals['gmap']) 
				&& is_array($aVals['gmap']) && isset($aVals['gmap']['latitude'])
				&& isset($aVals['gmap']['longitude']))
		{
			$aSql['gmap'] = serialize($aVals['gmap']);
		}
		
		if (!Phpfox_Error::isPassed())
		{
			return false;
		}
		$iId = $this->database()->insert(Phpfox::getT('event'), $aSql);
		
		if (!$iId)
		{
			return false;
		}
		
		$this->database()->insert(Phpfox::getT('event_text'), array(
				'event_id' => $iId,
				'description' => (empty($aVals['description']) ? null : $oParseInput->clean($aVals['description'])),
				'description_parsed' => (empty($aVals['description']) ? null : $oParseInput->prepare($aVals['description']))
			)
		);		
		
		foreach ($this->_aCategories as $iCategoryId)
		{
			$this->database()->insert(Phpfox::getT('event_category_data'), array('event_id' => $iId, 'category_id' => $iCategoryId));
		}		
		$bAddFeed = ($sModule == 'event' ? (Phpfox::getUserParam('event.event_must_be_approved') ? false : true) : true);
		
		if ($bAddFeed === true)
		{
			if ($sModule == 'event' && Phpfox::isModule('feed'))
			{
				Phpfox::getService('feed.process')->add('event', $iId, $aVals['auth_view'], (isset($aVals['auth_comment']) ? (int) $aVals['auth_comment'] : 0));
			}
			else if (Phpfox::isModule('feed'))
			{
				Phpfox::getService('feed.process')
                        ->callback(Phpfox::callback($sModule . '.getFeedDetails', $iItem))
                        ->add('event', $iId, $aVals['auth_view'], (isset($aVals['auth_comment']) ? (int) $aVals['auth_comment'] : 0), $iItem);
			}			
			
			Phpfox::getService('user.activity')->update(Phpfox::getUserId(), 'event');
		}
		
		Phpfox::getService('event.process')->addRsvp($iId, 1, Phpfox::getUserId());
		$this->addImage($iId);

		return $iId;
	}
	/**
     * Input data:
     * + iEventId: int, required.
     * + iCommentId: int, optional.
     * 
     * Output data:
     * + iInviteId: int.
     * + iRsvpId: int.
     * + bIsFriend: bool.
     * + iEventId: int.
     * + iViewId: int.
     * + iPrivacy: int.
     * + iPrivacyComment: int.
     * + bIsFeatured: bool.
     * + bIsSponsor: bool.
     * + sEventImageUrl: string.
     * + sFullName: string.
     * + iUserId: int.
     * + sUserImageUrl: string.
     * + iStartTime: int.
     * + sStartTime: string.
     * + sStartFullTime: string.
     * + iEndTime: int.
     * + sEndTime: string.
     * + sEndFullTime: string.
     * + iTimeStamp: int.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + iTotalDislike: int.
     * + sTitle: string.
     * + sDescription: string.
     * + sCountryISO: string.
     * + sCountryName: string.
     * + sPostalCode: string.
     * + sCity: string.
     * + sAddress: string.
     * + bIsInvisible: bool.
     * + sEventDate: string.
     * + aCategory: array.
     * + sMapLocation: string.
     * + sLocation: string.
     * + iStartYear: int.
     * + iStartMonth: int.
     * + iStartDate: int.
     * + iStartHour: int.
     * + iStartMinute: int.
     * + iEndYear: int.
     * + iEndMonth: int.
     * + iEndDate: int.
     * + iEndHour: int.
     * + iEndMinute: int.
     * + bCanPostComment: int.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/view
     * 
     * @param array $aData
     * @return boolean|array
     */
    public function view($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->view($aData);
        }
        
        if (!isset($aData['iEventId']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

				$bCanView = true;
        /**
         * @var int
         */
        $iCommentId = isset($aData['iCommentId']) ? (int) $aData['iCommentId'] : 0;
        /**
         * @var int
         */
        $iEventId = isset($aData['iEventId']) ? (int) $aData['iEventId'] : 0;
        
        if (Phpfox::isUser())
        {
            $this->database()->select('ei.invite_id, ei.rsvp_id, ')->leftJoin(Phpfox::getT('event_invite'), 'ei', 'ei.event_id = e.event_id AND ei.invited_user_id = ' . Phpfox::getUserId());
        }

        if (Phpfox::isModule('friend'))
        {
            $this->database()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f', "f.user_id = e.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        }
        else
        {
            $this->database()->select('0 as is_friend, ');
        }
        /**
         * @var array
         */
        $aEvent = $this->database()->select('e.*, c.name AS country_name, ' . (Phpfox::getParam('core.allow_html') ? 'et.description_parsed' : 'et.description') . ' AS description, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('event'), 'e')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
                ->join(Phpfox::getT('event_text'), 'et', 'et.event_id = e.event_id')
                ->leftJoin(Phpfox::getT('country'), 'c', 'c.country_iso = e.country_iso')
                ->where('e.event_id = ' . (int) $aData['iEventId'])
                ->execute('getRow');

        if (!isset($aEvent['event_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('event.the_event_you_are_looking_for_does_not_exist_or_has_been_removed')
            );
        }

				//check bCanView
		if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('event', $aEvent['event_id'], $aEvent['user_id'], $aEvent['privacy'], $aEvent['is_friend'], true))
		{
			$bCanView = false;
		}
        
        if (Phpfox::isUser() && Phpfox::isModule('notification'))
		{
			if ($iCommentId)
			{
				Phpfox::getService('notification.process')->delete('event_comment', $iCommentId, Phpfox::getUserId());
				Phpfox::getService('notification.process')->delete('event_comment_feed', $iCommentId, Phpfox::getUserId());
				Phpfox::getService('notification.process')->delete('event_comment_like', $iCommentId, Phpfox::getUserId());
			}
			Phpfox::getService('notification.process')->delete('event_like', $iEventId, Phpfox::getUserId());
			Phpfox::getService('notification.process')->delete('event_invite', $iEventId, Phpfox::getUserId());
		}		
		
		/**
         * @var bool
         */
        $bCanPostComment = true;
        if (isset($aEvent['privacy_comment']) && $aEvent['user_id'] != Phpfox::getUserId() && !Phpfox::getUserParam('privacy.can_comment_on_all_items'))
        {
            switch ($aEvent['privacy_comment']) {
                // Everyone is case 0. Skipped.
                // Friends only
                case 1:
                    if (!Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aEvent['user_id']))
                    {
                        $bCanPostComment = false;
                    }
                    break;
                // Friend of friends
                case 2:
                    if (!Phpfox::getService('friend')->isFriendOfFriend($aEvent['user_id']))
                    {
                        $bCanPostComment = false;
                    }
                    break;
                // Only me
                case 3:
                    $bCanPostComment = false;
                    break;
            }
        }
        /**
         * @var array|bool
         */
        $aCallback = false;
        if ($aEvent['item_id'] && Phpfox::hasCallback($aEvent['module_id'], 'viewEvent'))
        {
            $aCallback = Phpfox::callback($aEvent['module_id'] . '.viewEvent', $aEvent['item_id']);

            if ($aEvent['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aCallback['item_id'], 'event.view_browse_events'))
            {
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_view_this_item_due_to_privacy_settings"))
                );
            }
        }

        if (Phpfox::getUserId())
        {
            /**
             * @var bool
             */
            $bIsBlocked = Phpfox::getService('user.block')->isBlocked($aEvent['user_id'], Phpfox::getUserId());
            if ($bIsBlocked)
            {
                $bCanPostComment = false;
            }
        }

        if (!isset($aEvent['event_id']))
        {

						return array(
								'error_code' => 1,
								'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.can_not_find_this_event"))
						);
        }

        if (!Phpfox::isUser())
        {
            $aEvent['invite_id'] = 0;
            $aEvent['rsvp_id'] = 0;
        }

        if ($aEvent['view_id'] == '1')
        {
            if ($aEvent['user_id'] == Phpfox::getUserId() || Phpfox::getUserParam('event.can_approve_events') || Phpfox::getUserParam('event.can_view_pirvate_events'))
            {
                
            }
            else
            {
							return array(
									'error_code' => 1,
									'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.can_not_view_pending_events"))
							);
            }
        }

        if (Phpfox::getUserId() == $aEvent['user_id'])
		{
			if (Phpfox::isModule('notification'))
			{
				Phpfox::getService('notification.process')->delete('event_approved', $iEventId, Phpfox::getUserId());
			}
			
			define('PHPFOX_FEED_CAN_DELETE', true);
		}					
		
        $aEvent['event_date'] = Phpfox::getTime(Phpfox::getParam('event.event_basic_information_time'), $aEvent['start_time']);
        if ($aEvent['start_time'] < $aEvent['end_time'])
        {
            $aEvent['event_date'] .= ' - ';
            if (date('dmy', $aEvent['start_time']) === date('dmy', $aEvent['end_time']))
            {
                $aEvent['event_date'] .= Phpfox::getTime(Phpfox::getParam('event.event_basic_information_time_short'), $aEvent['end_time']);
            }
            else
            {
                $aEvent['event_date'] .= Phpfox::getTime(Phpfox::getParam('event.event_basic_information_time'), $aEvent['end_time']);
            }
        }

        if (isset($aEvent['gmap']) && !empty($aEvent['gmap']))
        {
            $aEvent['gmap'] = unserialize($aEvent['gmap']);
        }
        /**
         * @var array
         */
        $aCategories = $this->database()->select('pc.parent_id AS iParentId, pc.category_id AS iCategoryId, pc.name AS sName')
                ->from(Phpfox::getT('event_category_data'), 'pcd')
                ->join(Phpfox::getT('event_category'), 'pc', 'pc.category_id = pcd.category_id')
                ->where('pcd.event_id = ' . (int) $aEvent['event_id'])
                ->order('pc.parent_id ASC, pc.ordering ASC')
                ->execute('getRows');
        
        $aEvent['categories'] = $aCategories;

        if (!empty($aEvent['address']))
        {
            $aEvent['map_location'] = $aEvent['address'];
            if (!empty($aEvent['city']))
            {
                $aEvent['map_location'] .= ',' . $aEvent['city'];
            }
            if (!empty($aEvent['postal_code']))
            {
                $aEvent['map_location'] .= ',' . $aEvent['postal_code'];
            }
            if (!empty($aEvent['country_child_id']))
            {
                $aEvent['map_location'] .= ',' . Phpfox::getService('core.country')->getChild($aEvent['country_child_id']);
            }
            if (!empty($aEvent['country_iso']))
            {
                $aEvent['map_location'] .= ',' . Phpfox::getService('core.country')->getCountry($aEvent['country_iso']);
            }

            $aEvent['map_location'] = urlencode($aEvent['map_location']);
        }
        
        if (!($aEvent))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('event.the_event_you_are_looking_for_does_not_exist_or_has_been_removed')
            );
        }

        if($aEvent['image_path'] == null){
            $sEventImageUrl = $this->_sDefaultImageEventPath;
        } else {
            $sEventImageUrl = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aEvent['server_id'],
                'path' => 'event.url_image',
                'file' => $aEvent['image_path'],
                'suffix' => '',
                'return_url' => true
                    )
            );
        }

        $sUserImageUrl = Phpfox::getService('mfox.user')->getImageUrl($aEvent, '_50_square');
        
        $iStartTimeUser = Phpfox::getService('mfox.core')->convertToUserTimeZone((int)$aEvent['start_time']);
        $aStartTimeYear = date('Y', $iStartTimeUser);
        $aStartTimeMonth = date('M', $iStartTimeUser);
        $aStartTimeDay = date('d', $iStartTimeUser);
        $aStartTime = array(
            'iYear' => $aStartTimeYear, 
            'iMonth' => $aStartTimeMonth, 
            'iDay' => $aStartTimeDay, 
            );
		
		$bCanEdit  = 0;
		$bCanDelete = 0;
		$bIsPages = false;

        if ($aEvent['module_id'] == 'pages' && $aEvent['item_id'] > 0)
        {
            $bIsPages =  true;
        }
		
		if($aEvent['user_id'] == Phpfox::getUserId()){
			$bIsOwner =  1;
		}else if ($bIsPages){
			$bIsOwner = Phpfox::getService('pages')->isAdmin(intval($aEvent['item_id']));
		}
		
		if (($bIsOwner && Phpfox::getUserParam('event.can_edit_own_event'))
		|| (!$bIsOwner && Phpfox::getUserParam('event.can_edit_other_even'))){
			$bCanEdit =  1;
		}
		
		if (($bIsOwner && Phpfox::getUserParam('event.can_delete_own_event'))
		|| (!$bIsOwner && Phpfox::getUserParam('event.can_delete_other_event'))){
			$bCanDelete =  1;
		}	


        return array(
						'bCanInvite' => $aEvent['user_id'] == Phpfox::getUserId() ? true : false,
						'bCanView' => $bCanView,
						'bIsMember' => $aEvent['invite_id'] ? true : false,
						'bIsResourceApproval' => $aEvent['view_id'] == 1 ? true : false,
						//@todo: verify later
						'bOnRequest' => false, // do not know what to do with it
						'iCategory' => $aEvent['categories'] ? $aEvent['categories'][0]['iCategoryId'] : -1, // currently we only get the first category, do not care about parent category
						'sCategory' => $aEvent['categories'] ? html_entity_decode(Phpfox::getLib('locale')->convert(Core\Lib::phrase()->isPhrase($aEvent['categories'][0]['sName']) ? _p($aEvent['categories'][0]['sName']) : $aEvent['categories'][0]['sName'])) : '', // currently we only get the first category, do not care about parent category
            'iRsvp' => $aEvent['rsvp_id'],
            'sCommentPrivacy' => $aEvent['privacy_comment'],
            'sCommentPrivacyFull' => Phpfox::getService('privacy')->getPhrase($aEvent['privacy_comment']), 
						'sEndDate' => date('Y-m-d', $aEvent['end_time']),
						'sEndTime' => date('H:i', $aEvent['end_time']),
                        'aStartTime' => $aStartTime,
						'sStartDate' => date('Y-m-d', $aEvent['start_time']),
						'sStartTime' => date('H:i', $aEvent['start_time']),
						'sEventBigImageUrl' => $sEventImageUrl,
						'sHost' => 'Not Implement because Fox does not have',
						'sViewPrivacy' => $aEvent['privacy'],
						'sViewPrivacyFull' => Phpfox::getService('privacy')->getPhrase($aEvent['privacy']), 
					//----
            'iInviteId' => $aEvent['invite_id'],
            'iRsvpId' => $aEvent['rsvp_id'],
            'bIsFriend' => $aEvent['is_friend'],
            'iEventId' => $aEvent['event_id'],
            'iProfilePageId' => $aEvent['profile_page_id'],
            'iViewId' => $aEvent['view_id'],
            'iPrivacy' => $aEvent['privacy'],
            'iPrivacyComment' => $aEvent['privacy_comment'],
            'bIsFeatured' => $aEvent['is_featured'],
            'bIsSponsor' => $aEvent['is_sponsor'],
            'sEventImageUrl' => $sEventImageUrl,
            'sFullName' => $aEvent['full_name'],
            'iUserId' => $aEvent['user_id'],
            'sUserImageUrl' => $sUserImageUrl,
            'iStartTime' => $aEvent['start_time'],
            // 'sStartTime' => date('l, F j', $aEvent['start_time']),
            'sStartFullTime' => date('l, F j', $aEvent['start_time']) . ' at ' . date('g:i a', $aEvent['start_time']),
            'iEndTime' => $aEvent['end_time'],
            // 'sEndTime' => date('l, F j', $aEvent['end_time']),
            'sEndFullTime' => date('l, F j', $aEvent['end_time']) . ' at ' . date('g:i a', $aEvent['end_time']),
            'iTimeStamp' => $aEvent['time_stamp'],
            'iTotalComment' => $aEvent['total_comment'],
            'iTotalLike' => $aEvent['total_like'],
            'iTotalDislike' => $aEvent['total_dislike'],
            'sTitle' => $aEvent['title'],
            'sDescription' => Phpfox::getLib('parse.output')->parse($aEvent['description']),
            'sCountryISO' => $aEvent['country_iso'],
            'sCountryName' => $aEvent['country_name'],
            'sPostalCode' => $aEvent['postal_code'],
            'sCity' => $aEvent['city'],
            'sAddress' => $aEvent['address'],
            'bIsInvisible' => $aEvent['is_invisible'],
            'sEventDate' => $aEvent['event_date'],
            'aCategory' => $aEvent['categories'],
            'sMapLocation' => $aEvent['map_location'],
            'sLocation' => $aEvent['location'],
            'iStartYear' => date('Y', $aEvent['start_time']),
            'iStartMonth' => date('n', $aEvent['start_time']),
            'iStartDate' => date('j', $aEvent['start_time']),
            'iStartHour' => date('G', $aEvent['start_time']),
            'iStartMinute' => date('i', $aEvent['start_time']),
            'iEndYear' => date('Y', $aEvent['end_time']),
            'iEndMonth' => date('n', $aEvent['end_time']),
            'iEndDate' => date('j', $aEvent['end_time']),
            'iEndHour' => date('G', $aEvent['end_time']),
            'iEndMinute' => date('i', $aEvent['end_time']),
            'sUserTimezone' => Phpfox::getLib('date')->getTimeZone(),
            'iNumOfMember' => Phpfox::getService('mfox.helper.event')->getNumberOfGuestOfEvent($aEvent['event_id']),
            'bCanPostComment' => $bCanPostComment, 
            'bCanComment'=>$bCanPostComment,
            'bCanEdit' => $bCanEdit,
            'bCanDelete' => $bCanDelete,
            'bIsPages'=>$bIsPages,            
            'sModuleId' => $aEvent['module_id'],
        );
    }

    /**
     * @see Event_Service_Process
     * 
     * Input data:
     * + iEventId: int, required.
     * + iRsvp: int, required.
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     * + message: string.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/addrsvp
     * 
     * @param array $aData
     * @return array
     */
    public function addrsvp($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->addrsvp($aData);
        }
        
        if (!isset($aData['iEventId']) || !isset($aData['iRsvp']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        
        $bUseId = true;

        if (Phpfox::isUser())
        {
            $this->database()->select('ei.invite_id, ei.rsvp_id, ')->leftJoin(Phpfox::getT('event_invite'), 'ei', 'ei.event_id = e.event_id AND ei.invited_user_id = ' . Phpfox::getUserId());
        }

        if (Phpfox::isModule('friend'))
        {
            $this->database()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f', "f.user_id = e.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        }
        else
        {
            $this->database()->select('0 as is_friend, ');
        }
        /**
         * @var array
         */
        $aEvent = $this->database()->select('e.*, c.name AS country_name, ' . (Phpfox::getParam('core.allow_html') ? 'et.description_parsed' : 'et.description') . ' AS description, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('event'), 'e')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
                ->join(Phpfox::getT('event_text'), 'et', 'et.event_id = e.event_id')
                ->leftJoin(Phpfox::getT('country'), 'c', 'c.country_iso = e.country_iso')
                ->where('e.event_id = ' . (int) $aData['iEventId'])
                ->execute('getRow');

        if (!isset($aEvent['event_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('event.the_event_you_are_looking_for_does_not_exist_or_has_been_removed')
            );
        }

		if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('event', $aEvent['event_id'], $aEvent['user_id'], $aEvent['privacy'], $aEvent['is_friend'], true))
		{
			return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
		}
		/**
         * @var bool
         */
        $bResult = Phpfox::getService('event.process')->addRsvp($aData['iEventId'], $aData['iRsvp'], Phpfox::getUserId());

        return array(
            'error_code' => 0,
            'result' => 1,
            'message'=>html_entity_decode(Phpfox::getPhrase("mfox.your_rsvp_has_been_updated")),
            'event_data' =>$this->detail(array_merge($aData, array('iEventId'=> $aData['iEventId']))),
        );
    }
    
    /**
     * Input data:
     * + iEventId: int, required.
     * + sUserId: string, required. (string split by comma)
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/invite
     * 
     * @param array $aData
     * @return array
     */
    public function invite($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->invite($aData);
        }
        
        if (!isset($aData['iEventId']) || !isset($aData['sUserId']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        /**
         * @var object
         */
        $oParseInput = Phpfox::getLib('parse.input');
        /**
         * @var array
         */
        $aEvent = $this->database()->select('event_id, user_id, title, module_id')
                ->from(Phpfox::getT('event'))
                ->where('event_id = ' . (int) $aData['iEventId'])
                ->execute('getSlaveRow');

        if (!$aEvent)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.event_is_not_available"))
            );
        }
        
        /**
         * @var array
         */
        $aVals = array('invite' => explode(',', $aData['sUserId']));

        if (isset($aVals['invite']))
        {
            /**
             * @var array
             */
            $aInvites = $this->database()->select('invited_user_id, invited_email')
                    ->from(Phpfox::getT('event_invite'))
                    ->where('event_id = ' . (int) $aData['iEventId'])
                    ->execute('getRows');
            /**
             * @var array
             */
            $aInvited = array();
            foreach ($aInvites as $aInvite)
            {
                $aInvited[(empty($aInvite['invited_email']) ? 'user' : 'email')][(empty($aInvite['invited_email']) ? $aInvite['invited_user_id'] : $aInvite['invited_email'])] = true;
            }
        }

        if (isset($aVals['invite']) && is_array($aVals['invite']))
        {
            /**
             * @var string
             */
            $sUserIds = '';
            foreach ($aVals['invite'] as $iUserId)
            {
                if (!is_numeric($iUserId))
                {
                    continue;
                }
                $sUserIds .= $iUserId . ',';
            }
            $sUserIds = rtrim($sUserIds, ',');
            /**
             * @var array
             */
            $aUsers = $this->database()->select('user_id, email, language_id, full_name')
                    ->from(Phpfox::getT('user'))
                    ->where('user_id IN(' . $sUserIds . ')')
                    ->execute('getSlaveRows');

            foreach ($aUsers as $aUser)
            {
                if (isset($aInvited['user'][$aUser['user_id']]))
                {
                    continue;
                }
                /**
                 * @var string
                 */
                $sLink = Phpfox::getLib('url')->permalink('event', $aEvent['event_id'], $aEvent['title']);
                /**
                 * @var string
                 */
                $sMessage =  Phpfox::getPhrase('event.full_name_invited_you_to_the_title', array(
                            'full_name' => Phpfox::getUserBy('full_name'),
                            'title' => $oParseInput->clean($aEvent['title'], 255),
                            'link' => $sLink
                                ), false, null, $aUser['language_id']);
                /**
                 * @var bool
                 */
                $bSent = Phpfox::getLib('mail')->to($aUser['user_id'])
                        ->subject(array('event.full_name_invited_you_to_the_event_title', array('full_name' => Phpfox::getUserBy('full_name'), 'title' => $oParseInput->clean($aEvent['title'], 255))))
                        ->message($sMessage)
                        ->notification('event.invite_to_event')
                        ->send();

                if ($bSent)
                {
                    /**
                     * @var int
                     */
                    $iInviteId = $this->database()->insert(Phpfox::getT('event_invite'), array(
                        'event_id' => $aData['iEventId'],
                        'user_id' => Phpfox::getUserId(),
                        'invited_user_id' => $aUser['user_id'],
                        'time_stamp' => PHPFOX_TIME
                            )
                    );

                    (Phpfox::isModule('request') ? Phpfox::getService('request.process')->add('event_invite', $aData['iEventId'], $aUser['user_id']) : null);
                }
            }
        }

        return array(
            'error_code' => 0,
            'result' => 1,
            'message'=>html_entity_decode(Phpfox::getPhrase("mfox.members_invited")),
            'event_data' =>$this->detail(array('iEventId'=> $aData['iEventId'])),
        );
    }
    /**
     * Input data:
     * + iEventId: int, required.
     * + sFeedback: string, optional.
     * + iReport: int, required.
     * - 1: Nudity or Pornography
     * - 2: Drug Use
     * - 3: Violence
     * - 4: Attacks Individual or Group
     * - 5: Copyright Infringement
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     * + message: string.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see event/report
     * 
     * @param array $aData
     * @return array
     */
    public function report($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->report($aData);
        }
        
        if (!isset($aData['iEventId']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        /**
         * @var array
         */
        $aEvent = Phpfox::getService('event')->getEvent((int) $aData['iEventId']);

        if (!isset($aEvent['event_id']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.event_is_not_valid_or_has_been_deleted"))
            );
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('event', $aEvent['event_id'], $aEvent['user_id'], $aEvent['privacy'], $aEvent['is_friend'], true))
		{
			return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
		}
        
        $oReport = Phpfox::getService('report');
        /**
         * @var array
         */
        $aVals = array(
            'type' => 'event',
            'id' => $aData['iEventId']
        );

        if (isset($aData['sFeedback']) && !Phpfox::getLib('parse.format')->isEmpty($aData['sFeedback']))
        {
            $aVals['feedback'] = $aData['sFeedback'];
        }
        else
        {
            $aVals['feedback'] = '';
            /**
             * @var array
             */
            $aReasons = $oReport->getOptions($aVals['type']);
            $aReasonId = array();
            foreach ($aReasons as $aReason)
            {
                $aReasonId[$aReason['report_id']] = $aReason['report_id'];
            }

            if (!isset($aData['iReport']) || !isset($aReasonId[$aData['iReport']]))
            {
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.reason_is_not_valid"))
                );
            }
        }

        $aVals['report'] = isset($aData['iReport']) ? $aData['iReport'] : '';
        /**
         * @var bool
         */
        $bCanReport = $oReport->canReport($aVals['type'], $aVals['id']);

        if ($bCanReport)
        {
            if ($bResult = Phpfox::getService('report.data.process')->add($aVals['report'], $aVals['type'], $aVals['id'], $aVals['feedback']))
            {
                return array(
                    'result' => $bResult,
                    'message'=>html_entity_decode(Phpfox::getPhrase("mfox.report_successfully"))
                );
            }
            else
            {
                return array(
                    'error_code' => 1,
                    'error_message' => implode(' ', Phpfox_Error::get())
                );
            }
        }
        else
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('report.you_have_already_reported_this_item')
            );
        }
    }
    /**
     * Using for notification.
     * @param array $aNotification
     * @return boolean|array
     */
    public function doEventGetCommentNotification($aNotification)
    {
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->doEventGetCommentNotification($aNotification);
        }
        
        /**
         * @var array
         */
        $aRow = $this->database()->select('fc.feed_comment_id, u.user_id, u.gender, u.user_name, u.full_name, e.event_id, e.title')
                ->from(Phpfox::getT('event_feed_comment'), 'fc')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
                ->join(Phpfox::getT('event'), 'e', 'e.event_id = fc.parent_user_id')
                ->where('fc.feed_comment_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');

        if (!isset($aRow['feed_comment_id']))
        {
            return array();
        }
        /**
         * @var string
         */
        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        /**
         * @var string
         */
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...');
        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            if (isset($aNotification['extra_users']) && count($aNotification['extra_users']))
            {
                $sPhrase =  Phpfox::getPhrase('event.users_commented_on_span_class_drop_data_user_row_full_name_s_span_comment_on_the_event_title', array('users' => Phpfox::getService('notification')->getUsers($aNotification, true), 'row_full_name' => $aRow['full_name'], 'title' => $sTitle));
            }
            else
            {
                $sPhrase =  Phpfox::getPhrase('event.users_commented_on_gender_own_comment_on_the_event_title', array('users' => $sUsers, 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => $sTitle));
            }
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('event.users_commented_on_one_of_your_comments_on_the_event_title', array('users' => $sUsers, 'title' => $sTitle));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('event.users_commented_on_one_of_span_class_drop_data_user_row_full_name_s_span_comments_on_the_event_title', array('users' => $sUsers, 'row_full_name' => $aRow['full_name'], 'title' => $sTitle));
        }

        return array(
            'link' => array(
                'iCommentId' => $aRow['feed_comment_id'],
                'iEventId' => $aRow['event_id'],
                'sTitle' => $aRow['title']
            ),
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }
    /**
     * Using for notification.
     * @param array $aNotification
     * @return array
     */
    public function doEventGetNotificationComment($aNotification)
    {
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->doEventGetNotificationComment($aNotification);
        }
        
        /**
         * @var array
         */
        $aRow = $this->database()->select('fc.feed_comment_id, u.user_id, u.gender, u.user_name, u.full_name, e.event_id, e.title')
                ->from(Phpfox::getT('event_feed_comment'), 'fc')
                ->join(Phpfox::getT('event'), 'e', 'e.event_id = fc.parent_user_id')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
                ->where('fc.feed_comment_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');
        
        if (!isset($aRow['feed_comment_id']))
        {
            return array();
        }
        
        /**
         * @var string
         */
        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        /**
         * @var string
         */
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...');
        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            if (isset($aNotification['extra_users']) && count($aNotification['extra_users']))
            {
                $sPhrase =  Phpfox::getPhrase('event.users_commented_on_span_class_drop_data_user_row_full_name_s_span_event_title', array('users' => Phpfox::getService('notification')->getUsers($aNotification, true), 'row_full_name' => $aRow['full_name'], 'title' => $sTitle));
            }
            else
            {
                $sPhrase =  Phpfox::getPhrase('event.users_commented_on_gender_own_event_title', array('users' => $sUsers, 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => $sTitle));
            }
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('event.users_commented_on_your_event_title', array('users' => $sUsers, 'title' => $sTitle));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('event.users_commented_on_span_class_drop_data_user_row_full_name_s_span_event_title', array('users' => $sUsers, 'row_full_name' => $aRow['full_name'], 'title' => $sTitle));
        }

        return array(
            'link' => array('iCommentId' => $aRow['feed_comment_id'],
                'iEventId' => $aRow['event_id'],
                'sTitle' => $aRow['title']),
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }
    /**
     * Using for notification.
     * @param array $aNotification
     * @return array
     */
    public function doEventGetNotificationComment_Feed($aNotification)
	{
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->doEventGetNotificationComment_Feed($aNotification);
        }
        
		return $this->doEventGetCommentNotification($aNotification);	
	}
    /**
     * Using for notification.
     * @param array $aNotification
     * @return array
     */
    public function doEventGetNotificationComment_Like($aNotification)
	{
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->doEventGetNotificationComment_Like($aNotification);
        }
        
        /**
         * @var array
         */
		$aRow = $this->database()->select('fc.feed_comment_id, u.user_id, u.gender, u.user_name, u.full_name, e.event_id, e.title')
			->from(Phpfox::getT('event_feed_comment'), 'fc')
			->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
			->join(Phpfox::getT('event'), 'e', 'e.event_id = fc.parent_user_id')
			->where('fc.feed_comment_id = ' . (int) $aNotification['item_id'])
			->execute('getSlaveRow');
        
        if (!isset($aRow['feed_comment_id']))
        {
            return array();
        }
        
        /**
         * @var string
         */
		$sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        /**
         * @var string
         */
		$sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...');
		/**
         * @var string
         */
		$sPhrase = '';
		if ($aNotification['user_id'] == $aRow['user_id'])
		{
			if (isset($aNotification['extra_users']) && count($aNotification['extra_users']))
			{
				$sPhrase =  Phpfox::getPhrase('event.users_liked_span_class_drop_data_user_row_full_name_s_span_comment_on_the_event_title', array('users' => Phpfox::getService('notification')->getUsers($aNotification, true), 'row_full_name' => $aRow['full_name'], 'title' => $sTitle));
			}
			else 
			{
				$sPhrase =  Phpfox::getPhrase('event.users_liked_gender_own_comment_on_the_event_title', array('users' => $sUsers, 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => $sTitle));
			}
		}
		elseif ($aRow['user_id'] == Phpfox::getUserId())		
		{
			$sPhrase =  Phpfox::getPhrase('event.users_liked_one_of_your_comments_on_the_event_title', array('users' => $sUsers, 'title' => $sTitle));
		}
		else 
		{
			$sPhrase =  Phpfox::getPhrase('event.users_liked_one_on_span_class_drop_data_user_row_full_name_s_span_comments_on_the_event_title', array('users' => $sUsers, 'row_full_name' => $aRow['full_name'], 'title' => $sTitle));
		}
			
        return array(
            'link' => array(
                'iCommentId' => $aRow['feed_comment_id'],
                'iEventId' => $aRow['event_id'],
                'sTitle' => $aRow['title']
            ),
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
	}	
    /**
     * Using for notification.
     * @param array $aNotification
     * @return boolean|array
     */
    public function doEventGetNotificationLike($aNotification)
    {
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->doEventGetNotificationLike($aNotification);
        }
        
        /**
         * @var array
         */
        $aRow = $this->database()->select('e.event_id, e.title, e.user_id, u.gender, u.full_name')
                ->from(Phpfox::getT('event'), 'e')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
                ->where('e.event_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');

        if (!isset($aRow['event_id']))
        {
            return array();
        }
        /**
         * @var string
         */
        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        /**
         * @var string
         */
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...');
        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            $sPhrase =  Phpfox::getPhrase('event.users_liked_gender_own_event_title', array('users' => $sUsers, 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => $sTitle));
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('event.users_liked_your_event_title', array('users' => $sUsers, 'title' => $sTitle));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('event.users_liked_span_class_drop_data_user_row_full_name_s_span_event_title', array('users' => $sUsers, 'row_full_name' => $aRow['full_name'], 'title' => $sTitle));
        }

        return array(
            'link' => array('iEventId' => $aRow['event_id'], 'sTitle' => $aRow['title']),
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }
    /**
     * Get activity feed of event.
     * @param array $aItem
     * @param array $aCallback
     * @param bool $bIsChildItem
     * @return boolean
     */
    public function doEventGetActivityFeed($aItem, $aCallback = null, $bIsChildItem = false)
	{				
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->doEventGetActivityFeed($aItem, $aCallback, $bIsChildItem);
        }
        
		if ($bIsChildItem)
		{
			$this->database()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user'), 'u2', 'u2.user_id = e.user_id');
		}			
		/**
         * @var array
         */
		$aRow = $this->database()->select('e.event_id, e.module_id, e.item_id, e.title, e.time_stamp, e.image_path, e.server_id, e.total_like, e.total_comment, et.description_parsed, l.like_id AS is_liked')
			->from(Phpfox::getT('event'), 'e')
			->leftJoin(Phpfox::getT('event_text'), 'et', 'et.event_id = e.event_id')
			->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'event\' AND l.item_id = e.event_id AND l.user_id = ' . Phpfox::getUserId())
			->where('e.event_id = ' . (int) $aItem['item_id'])
			->execute('getSlaveRow');
		
		if (!isset($aRow['event_id']))
		{
			return false;
		}
		
		if ($bIsChildItem)
		{
			$aItem = $aRow;
		}			
		
		if ((defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'event.view_browse_events'))
			|| (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aRow['item_id'], 'event.view_browse_events'))			
		)
		{
			return false;
		}
		/**
         * @var array
         */
		$aReturn = array(
            'sTypeId' => 'event',
            'iUserId' => $aRow['user_id'],
            'sUsername' => $aRow['user_name'],
            'sFullName' => $aRow['full_name'],

			'sFeedTitle' => $aRow['title'],
			'sFeedInfo' =>  Phpfox::getPhrase('feed.created_an_event'),
			'sFeedLink' => Phpfox::permalink('event', $aRow['event_id'], $aRow['title']),
			'sFeedContent' => $aRow['description_parsed'],
			'sFeedIcon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/event.png', 'return_url' => true)),
			'iTimeStamp' => $aRow['time_stamp'],
            'Time' => date('l, F j, o', (int) $aRow['time_stamp']) . ' at ' . date('h:i a', (int) $aRow['time_stamp']),
            'TimeConverted' => Phpfox::getLib('date')->convertTime($aRow['time_stamp'], 'comment.comment_time_stamp'),
			'iFeedTotalLike' => $aRow['total_like'],
			'bFeedIsLiked' => $aRow['is_liked'],
			'bEnableLike' => true,			
			'sLikeTypeId' => 'event',
			'iTotalComment' => $aRow['total_comment'],			
            
            'aEvent' => array(
                'iEventId' => $aRow['event_id'],
                'iUserId' => $aRow['user_id'],
                'iProfilePageId' => $aRow['profile_page_id'],
                'sUserName' => $aRow['user_name'],
                'sFullName' => $aRow['full_name'],
                'iGender' => $aRow['gender'],
                'sModuleId' => $aRow['module_id']
            )
		);
		
		$aReturn['sFeedImage'] = Phpfox::getService('mfox.event')->getImageUrl(array(
				'server_id' => $aRow['server_id'],
				'path' => 'event.url_image',
				'file' => $aRow['image_path'],
				'suffix' => '_120',
                'return_url' => true
			)
		);
		
		return $aReturn;
	}

	public function listphotos($aData) {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->listphotos($aData);
        }
        
		$aPhotos = Phpfox::getService('mfox.helper.event')->getPhotosOfEvent($aData['iEventId']);

		$aResults = array();
		foreach($aPhotos as $aPhoto) {
			$aResults[] = array(
                'iPhotoId' => $aPhoto['photo_id'],
				'sModelType' => Phpfox::getService('mfox.photo')->isAdvancedModule() ? 'advancedphoto' : 'photo',
				'iUserId' => $aPhoto['user_id'],
				'sPhotoThumbUrl' => Phpfox::getService('mfox.helper.image')->getPhotoUrl($aPhoto['destination'], '_75'),
				'sPhotoUrl' => Phpfox::getService('mfox.helper.image')->getPhotoUrl($aPhoto['destination'], '_240'),
				'sUserImageUrl' => Phpfox::getService('mfox.helper.image')->getUserUrl($aPhoto['user_image'], '_50'),
				'sUserName' => $aPhoto['user_name']
			);
		}

		return $aResults;

	}

	public function getinvitepeople($aData) {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->getinvitepeople($aData);
        }
        
		$iEventId = $aData['iEventId'];
		$aConds =  array();
		$aInvitedIds = Phpfox::getService('mfox.helper.event')->getInvitedUserIds($iEventId);
		$sInvited = implode(', ' , $aInvitedIds);
		$aConds[] = " AND friend.is_page = 0 AND friend.user_id = " . (int)Phpfox::getUserId() . " AND u.user_id NOT IN ({$sInvited}) ";
		list($iCnt, $aFriends) = Phpfox::getService('friend')->get($aConds);

		$aResults = array();
		foreach ($aFriends as $aFriend) {

			$aResults[] = array(
				'UserProfileImg_Url' => Phpfox::getService('mfox.user')->getImageUrl($aFriend, '_50_square'),
				'sFullName' => $aFriend['full_name'],
				'id' => $aFriend['user_id']
			);
		}

		return $aResults;

	}

    /**
     * this method should return BOOL value to signature if image is processed successfully.
     */
	public function addImage($iId) {
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->addImage($iId);
        }

        

		$aSql = array();
		if ($this->_bHasImage)
		{			
			$oImage = Phpfox::getLib('image');

            
			$sFileName = Phpfox::getLib('file')->upload('image', Phpfox::getParam('event.dir_image'), $iId);
			$iFileSizes = filesize(Phpfox::getParam('event.dir_image') . sprintf($sFileName, ''));			

            if(false == $sFileName){
                return false;
            }

            // var_dump($_FILES, Phpfox::getParam('event.dir_image'), $sFileName);exit;
            
            
			$aSql['image_path'] = $sFileName;
			$aSql['server_id'] = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
			
            $orgExt = Phpfox::getLib('file')->getFileExt(Phpfox::getParam('event.dir_image') . sprintf($sFileName, ''));
            Phpfox::getService('mfox.helper.image')->correctOrientation($orgExt, Phpfox::getParam('event.dir_image') . $sFileName);
			$iSize = 50;			
			$oImage->createThumbnail(Phpfox::getParam('event.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('event.dir_image') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);			
			$iFileSizes += filesize(Phpfox::getParam('event.dir_image') . sprintf($sFileName, '_' . $iSize));			
			
			$iSize = 120;			
			$oImage->createThumbnail(Phpfox::getParam('event.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('event.dir_image') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);			
			$iFileSizes += filesize(Phpfox::getParam('event.dir_image') . sprintf($sFileName, '_' . $iSize));

			$iSize = 200;			
			$oImage->createThumbnail(Phpfox::getParam('event.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('event.dir_image') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);			
			$iFileSizes += filesize(Phpfox::getParam('event.dir_image') . sprintf($sFileName, '_' . $iSize));
			
			// Update user space usage
			Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'event', $iFileSizes);
		}	

        
		if($aSql) {
			$this->database()->update(Phpfox::getT('event'), $aSql, 'event_id = ' . (int) $iId);
		}

        return true;
	}


    public function getCommentOfFeed($aData) {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->getCommentOfFeed($aData);
        }
        
        
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;

		$aRow = $this->database()->select('*')
			->from(Phpfox::getT('event_feed_comment'))
			->where('feed_ment_id = ' . (int) $iItemId)
			->execute('getSlaveRow');		
		

        if (!$sItemType || !$iItemId)
        {
            return array(
                'error_code' => 1,
                'error_elements' => 'sItemType or iItemId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        if (empty($sItemType) || $iItemId < 1)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }        

        $iLastCommentIdViewed = isset($aData['iLastCommentIdViewed']) ? (int)$aData['iLastCommentIdViewed'] : 0;
        $aComment = null;
        if($iLastCommentIdViewed){
            $aComment = $this->getCommentByID($iLastCommentIdViewed);

            if (!isset($aComment['comment_id']))
            {
                return array(
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.last_comment_viewd_does_not_exist_or_has_been_deleted")),
                    'error_code' => 1
                );
            }                    
        }

        $iAmountOfComment = (isset($aData['iAmountOfComment']) && (int)$aData['iAmountOfComment'])? (int)$aData['iAmountOfComment'] : 20;
        $sType = $sItemType;
        $sModule = isset($aData['sModule']) ? trim($aData['sModule']) : '';
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;
        $iLastTime = (null == $aComment) ? 0 : $aComment['time_stamp'];

        //  process 
        $sType = $this->changeType($sType);
        $aError = null;
        switch ($sType) {
            case 'advancedphoto':
            case 'photo':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnPhoto($iItemId);
                break;

            case 'advancedphoto_album':
            case 'photo_album':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnAlbum($iItemId);
                break;

            case 'music_song':
                $aError = Phpfox::getService('mfox.song')->checkPrivacyOnSong($iItemId);
                break;

            case 'music_album':
                $aError = Phpfox::getService('mfox.album')->checkPrivacyOnMusicAlbum($iItemId);
                break;

            case 'video':
                $aError = Phpfox::getService('mfox.video')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                break;
            
            case 'user_status':
                $aError = Phpfox::getService('mfox.feed')->checkPrivacyOnUserStatusFeed($iItemId, $sType, $sModule, $iItem);
                break;
            
            default:

                break;
        }

        if (isset($aError))
        {
            return $aError;
        }
        $aComments = $this->database()
                ->select('c.comment_id AS iCommentId, c.user_id AS iUserId, u.full_name AS sFullName, u.user_image, u.server_id AS user_server_id, ct.text_parsed AS sContent, c.time_stamp AS time, c.total_like AS iTotalLike, l.like_id AS bIsLiked, c.type_id AS sItemType')
                ->from(Phpfox::getT('comment'), 'c')
                ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
                ->leftJoin(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
                ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId())
                ->where('c.type_id = \'' . $this->database()->escape($sType) . '\' AND c.item_id = ' . (int) $iItemId . ($iLastTime > 0 ? ' AND c.time_stamp < ' . $iLastTime : ''))
                ->order('c.time_stamp DESC')
                ->limit($iAmountOfComment)
                ->execute('getRows');

        $aResult = array();
        foreach ($aComments as $aComment)
        {
            $obj = array(
                'iCommentId' => $aComment['iCommentId'],
                'sImage' => Phpfox::getService('mfox.user')->getImageUrl($aComment, '_50_square'),
                'iTimestamp' => $aComment['time'],
                'sTime' => date('l, F j, o', (int) $aComment['time']) . ' at ' . date('h:i a', (int) $aComment['time']),
                'sTimeConverted' => Phpfox::getLib('date')->convertTime($aComment['time'], 'comment.comment_time_stamp'),
                'iUserId' => $aComment['iUserId'],
                'sFullName' => $aComment['sFullName'],
                'sContent' => $aComment['sContent'],
                'iTotalLike' => $aComment['iTotalLike'],
                'bIsLiked' => $aComment['bIsLiked'],
                'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('comment', $aComment['iCommentId'], Phpfox::getUserId()),
                'iTotalComment' => -1,
                'aUserLike' => array(),
                'sItemType' => $aComment['sItemType'],
                'iItemId' => (null != $aFeed) ? $aFeed['feed_id'] : $iItemId
            );

            $aLike['likes'] = Phpfox::getService('like')->getLikesForFeed('feed_mini', $aComment['iCommentId']
                ,  $aComment['bIsLiked'], Phpfox::getParam('feed.total_likes_to_display'), true);              
            $aLike['feed_total_like'] = Phpfox::getService('like')->getTotalLikeCount();
            foreach($aLike['likes'] as $like){
                $obj['aUserLike'][] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
            }

            $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('comment', $aComment['iCommentId'], $bGetCount = false);
            foreach($aDislike as $dislike){
                $obj['aUserDislike'][] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
            }

            $aResult[] = $obj;
        }

        //  end 
        return $aResult;    

    }

    public function getEventFeedByID($iId){
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->getEventFeedByID($iId);
        }
        
        return $this->database()->select('*')
            ->from(Phpfox::getT('event_feed'), 'e')     
            ->where('e.feed_id = ' . (int) $iId)
            ->execute('getRow');

    }

    public function getEventIDFeedByItemID($iId){
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->getEventIDFeedByItemID($iId);
        }
        
        return $this->database()->select('e.parent_user_id')
            ->from(Phpfox::getT('event_feed'), 'e')     
            ->where('e.item_id = ' . (int) $iId)
            ->group('e.item_id')
            ->execute('getslavefield');
    }

    public function getEventFeedByFeedID($iId){
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->getEventFeedByFeedID($iId);
        }
        
        return $this->database()->select('*')
            ->from(Phpfox::getT('event_feed'), 'e')     
            ->where('e.feed_id = ' . (int) $iId)
            ->execute('getRow');        
    }

    public function get($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->get($aData);
        }
        
        extract($aData, EXTR_SKIP);
        
        if(!isset($sView)){
            $sView = 'upcoming';
        }

        switch ($sView) {
            case 'friend':
                return $this->friend($aData);
            case 'my':
                return $this->my($aData);
                break;
            case 'past':
                return $this->past($aData);
                break;
            default:
                return $this->upcoming($aData);
        }
    }

    /**
     * form add
     */
    public function formadd($aData){
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->formadd($aData);
        }
        
        $response  =  array(
            'view_options'=> Phpfox::getService('mfox.privacy')->privacy($aData),
            'comment_options'=> Phpfox::getService('mfox.privacy')->privacycomment($aData),
            'category_options'=> $this->listcategories($aData),
        );

        $iValue  = Phpfox::getService('user.privacy')->getValue('event.display_on_profile');
        $response['default_privacy_setting'] = Phpfox::getService('mfox.privacy')->defaultprivacy("$iValue", $response['view_options']);
        
        return $response;
    }

    /**
     * all data needed for event edit form.
     */
    public function formedit($aData){
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->formedit($aData);
        }
        
        $aItem = $this->view($aData);
        $aForm = $this->formadd(array());

        if (!empty($event['sModuleId']) && $event['sModuleId'] != 'event') {
            $aForm['view_options'] = array();
            $aForm['comment_options'] = array();
        }

        return array_merge($aItem, $aForm);
    }    

    public function detail($aData)
    {
        if ($this->isAdvancedModule($aData)) {
            return Phpfox::getService('mfox.fevent')->detail($aData);
        }
        
        $response  = $this->view($aData);
        
        $response['aGuestStatistic'] = $this->getnumberguestlist($aData);
        $response['aGuestList']      = array(
            'notAttend'=>$this->viewguestlist(array_merge($aData, array('iRSVP'=>3))),
            'maybe'=>$this->viewguestlist(array_merge($aData, array('iRSVP'=>2))),
            'going'=> $this->viewguestlist(array_merge($aData, array('iRSVP'=>1))),
        );
        
        
        return $response;
    }

    public function canEdit($aItem){
    	$bIsOwner = $aItem['user_id'] == Phpfox::getUserId();
		
        if(($bIsOwner && Phpfox::getUserParam('event.can_edit_own_event'))
			 || (!$bIsOwner && Phpfox::getUserParam('event.can_edit_other_event'))){
            return 1;
        }

        return 0;
    }

    public function canDelete($aItem){
        $isAdminInPage = false;
		
        if($aItem['module_id'] == 'pages' && (int)$aItem['item_id'] > 0){
            $isAdminInPage = Phpfox::getService('pages')->isAdmin((int)$aItem['item_id']);
        }

        if ((($aItem['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('event.can_delete_own_event')) || Phpfox::getUserParam('event.can_delete_other_event'))
            || ($isAdminInPage)){
            return true;
        }

        return false;
    }

    public function isOwner($iItemId)
    {
        if ($this->isAdvancedModule()) {
            return Phpfox::getService('mfox.fevent')->isOwner($iItemId);
        }

        $aItem = Phpfox::getService('event')->getInfoForAction($iItemId);
        if (isset($aItem['event_id']))
        {
            if ($aItem['user_id'] == Phpfox::getUserId())
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Get event image url
     * @param array $aParams
     * @return string
     */
    public function getImageUrl($aParams = array())
    {
        if (empty($aParams['file']))
        {
            return Phpfox::getParam('core.url_module') . 'mfox/static/image/event_cover_default.jpg';
        }

        return Phpfox::getLib('image.helper')->display($aParams);
    }
}
