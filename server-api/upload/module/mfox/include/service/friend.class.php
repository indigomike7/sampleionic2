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
 * @since May 27, 2013
 * @link Mfox Api v1.0
 */
class Mfox_Service_Friend extends Phpfox_Service {

    /**
     * Mfox_Service_Request_Request
     * @var object
     */
    private $_oReq = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_oReq = Mfox_Service_Request_Request::instance();
    }

    /**
     * @return Mfox_Service_Friend
     */
    public static function instance()
    {
        return Phpfox::getService('mfox.friend');
    }
    
    public function queryJoin($bNoQueryFriend)
    {
        if ($this->_oReq->get('view') == 'friend' || ($bNoQueryFriend === false && (Phpfox::getParam('core.friends_only_community') && ($this->_oReq->get('view') == '' || $this->_oReq->get('view') == 'all'))))
        {
            return true;
        }
        
        return false;
    }

    /**
     * Input data:
     * + iFriendListId: int, required.
     * + sFriendId: string, required.
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     * + message: string.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see friend/addfriendstolist
     * 
     * @param array $aData
     * @return array
     */
    public function addfriendstolist($aData)
    {
        /**
         * @var int
         */
        $iFriendListId = isset($aData['iFriendListId']) ? (int) $aData['iFriendListId'] : 0;
        if ($iFriendListId < 1)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        /**
         * @var array
         */
        $aFriendList = Phpfox::getService('friend.list')->getList($aData['iFriendListId'], Phpfox::getUserId());
        
        if (!isset($aFriendList['list_id']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.friend_list_is_not_valid"))
            );
        }
        
        if (!isset($aData['sFriendId']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.friends_id_is_not_valid"))
            );
        }
        /**
         * @var array
         */
        $aTemp = explode(',', $aData['sFriendId']);
        /**
         * @var array
         */
        $aFriendId = array();
        
        foreach($aTemp as $iFriendId)
        {
            if (is_numeric($iFriendId))
            {
                $aFriendId[] = $iFriendId;
            }
        }
        
        if (count($aFriendId) == 0)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.friends_id_is_not_valid"))
            );
        }
        
        if (Phpfox::getService('friend.list.process')->addFriendsToList((int) $aData['iFriendListId'], (array) $aFriendId))
		{
			return array(
                'result' => true,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.add_friends_to_list_successfully"))
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
     * + bAllowCustom: bool.
     * 
     * Output data:
     * + iFriendId: int.
     * + bIsPage: bool.
     * + iListId: int.
     * + iUserId: int.
     * + iFriendUserId: int.
     * + bIsTopFriend: bool.
     * + sFullName: string.
     * + sUserImage: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see friend/buildcache
     * 
     * @param array $aData
     * @return array
     */
    public function buildcache($aData)
    {
        /**
         * @var bool
         */
        $bAllowCustom = isset($aData['bAllowCustom']) ? (bool) $aData['bAllowCustom'] : false;
        /**
         * @var array
         */
        $aFriends = Phpfox::getService('friend')->getFromCache($bAllowCustom);
        /**
         * @var array
         */
        $aResult = array();
        foreach($aFriends as $aFriend)
        {
            $aResult[] = array(
                'iFriendId' => $aFriend['friend_id'],
                'bIsPage' => $aFriend['is_page'],
                'iListId' => $aFriend['list_id'],
                'iUserId' => $aFriend['user_id'],
                'iFriendUserId' => $aFriend['friend_user_id'],
                'bIsTopFriend' => $aFriend['is_top_friend'],
                'sFullName' => $aFriend['full_name'],
                'sUserImage' => $aFriend['user_image']
            );
        }
        return $aResult;
    }
    /**
     * Input data:
     * + sName: string, required.
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + iFriendListId: int.
     * + sMessage: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see friend/addlist
     * 
     * @param array $aData
     * @return array
     */
    public function addlist($aData)
    {

		/**
         * @var string
         */
		$sName = isset($aData['sName']) ? $aData['sName'] : '';

		if (Phpfox::getLib('parse.format')->isEmpty($sName))
		{
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('friend.provide_a_name_for_your_list')
            );
		}
		elseif (Phpfox::getService('friend.list')->reachedLimit()) // Did they reach their limit?
		{
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('friend.you_have_reached_your_limit')
            );
		}			
		elseif (Phpfox::getService('friend.list')->isFolder($sName))
		{
			return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('mail.folder_already_use')
            );
		}
		else 
		{
			if ($iId = Phpfox::getService('friend.list.process')->add($sName))
			{
                return array(
                    'iFriendListId' => $iId,
                    'sMessage' =>  Phpfox::getPhrase('friend.list_successfully_created')
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
    }
    
    
    /**
     * => case sAction = confirm.
     * 
     * Input data:
     * + iPage: int, optional.
     * + iLimit: int, optional.
     * + iRequestId: int, optional.
     * 
     * Output data:
     * + id: int.
     * + sFullName: string.
     * + iUserId: int.
     * + UserProfileImg_Url: string.
     * 
     * @param array $aData
     * @return array
     * 
     * => Case sAction = all.
     * 
     * Input data:
     * + iUserId: int, required.
     * + amountOfFriend: int, optional.
     * + LastFriendIdViewed: int, optional.
     * + sType: string, optional. Ex: "more" or "new".
     * 
     * Output data:
     * + id: int.
     * + sFullName: string.
     * + iFriendId: int.
     * + UserProfileImg_Url: string.
     * + error_message: string.
     * + error_code: int.
     * + result: int.
     * 
     * @see Mobile - API phpFox/Api V1.0 - Restful.
     * @see friend
     * 
     * @param array $aData
     * @return array
     */
    public function getAction($aData)
    {
        if (isset($aData['sAction']))
        {
            switch ($aData['sAction']) {
                case 'confirm':
                    return $this->getfriendsrequest($aData);
                    break;

                case 'all':
                default:
                    return $this->getall($aData);
                    break;
            }
        }
        else
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.action_is_not_valid")),
                'error_code' => 1,
                'result' => 0
            );
        }
    }
    /**
     * Input data:
     * + iUserId: int, required.
     * + amountOfFriend: int, optional.
     * + LastFriendIdViewed: int, optional.
     * + sType: string, optional. Ex: "more" or "new".
     * 
     * Output data:
     * + id: int.
     * + sFullName: string.
     * + iFriendId: int.
     * + UserProfileImg_Url: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see friend/getall
     * 
     * @param array $aData
     * @return array
     */
    public function get($aData)
    {
        extract($aData, EXTR_SKIP);

        if (!isset($iUserId))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        $iUserId = (int) $iUserId;
        
        if (!isset($iAmountOfFriend))
        {
            $iAmountOfFriend = 20;
        }

        $sCondSearch = '';
        if (isset($sSearch) && strlen(trim($sSearch)) > 0)
        {
            $sCondSearch = ' AND u.full_name LIKE \'%' . (trim($sSearch)) . '%\' ';
        }

        $aCond = array('AND friend.is_page = 0 AND friend.user_id = ' . $iUserId . $sCondSearch);
        $sSort = 'friend.friend_id DESC';
        if (!isset($aData['iPage'])){
            $aData['iPage'] = 1;
        }
        if ((int)$aData['iPage'] == 0){
            return array();
        }

        $iCnt = 0;
        $aFriends = array();

        $iCnt = $this->database()->select('COUNT(DISTINCT u.user_id)')
                ->from(Phpfox::getT('friend'), 'friend')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = friend.friend_user_id AND u.status_id = 0')
                ->where($aCond)
                ->execute('getSlaveField');
        list($fromResult, $toResult, $isShowPrev, $pagePrev, $isShowNext, $pageNext) = Phpfox::getService('mfox.core')->caculatePaging((int)$iCnt, (int) $iAmountOfFriend, (int)$aData['iPage'] - 1);
        if($pageNext == 0){
            return array();
        }                

        if ($iCnt)
        {
            $aFriends = $this->database()->select('uf.dob_setting, friend.friend_id, friend.friend_user_id, friend.is_top_friend, friend.time_stamp, ' . Phpfox::getUserField())
                    ->from(Phpfox::getT('friend'), 'friend')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = friend.friend_user_id AND u.status_id = 0')
                    ->join(Phpfox::getT('user_field'), 'uf', 'u.user_id = uf.user_id')
                    ->where($aCond)
                    ->limit((int) $aData['iPage'], (int) $iAmountOfFriend, $iCnt)
                    ->order($sSort)
                    ->group('u.user_id')
                    ->execute('getSlaveRows');
        }

        $aResult = array();
        
        foreach ($aFriends as $aFriend)
        {
            $sImageUrl = Phpfox::getService('mfox.user')->getImageUrl($aFriend, '_50_square');

			list($count, $aMutualList) = Phpfox::getService('friend')->getMutualFriends($aFriend['user_id']);

            $aResult[] = array(
                'id' => $aFriend['user_id'], // id of the friend
                'sFullName' => $aFriend['full_name'],
								'UserProfileImg_Url' => $sImageUrl,
								'BigUserProfileImg_Url' => $sImageUrl,
								'isFriend' => Phpfox::getService('mfox.helper.user')->isFriend(Phpfox::getUserId(), $aFriend['user_id']),
								'iMutualFriends' => $count,
                'isSentRequest' => Phpfox::getService('friend.request')->isRequested(Phpfox::getUserId(), $aFriend['user_id']),
                'isSentRequestBy' => Phpfox::getService('friend.request')->isRequested($aFriend['user_id'], Phpfox::getUserId()), 
                );
        }

        return $aResult;
    }
    /**
     * Input data:
     * + iUserId: int, required.
     * + sText: string, optional.
     * 
     * Output data:
     * + result: string.
     * + error_code: int.
     * + error_message: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see friend/add
     * 
     * @param array $aData
     * @return array
     */
    public function add($aData)
    {
        /**
         * @var int
         */
        $iUserId = isset($aData['iUserId']) ? (int) $aData['iUserId'] : 0;
        /**
         * @var string
         */
        $sText = isset($aData['sText']) ? $aData['sText'] : '';
        /**
         * @var int
         */
        $iListId = isset($aData['iListId']) ? (int) $aData['iListId'] : null;
        
        if ($iUserId < 0)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        
        if (Phpfox::getUserId() === $iUserId)
        {
            return array('result' => false);
        }
        
        if (!Phpfox::getUserParam('friend.can_add_friends'))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_add_friend"))
            );
        }
        /**
         * @var array
         */
        $aUser = Phpfox::getService('user')->getUser($iUserId, 'u.user_id, u.user_name, u.user_image, u.server_id');

        if(empty($aUser)){
            return array(
                'error_code'=>1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.could_not_find_requested_member")),
                'result' => false
            );
        }
        
        if (Phpfox::getUserId() === $aUser['user_id'])
        {
            return array(
                    'error_code'=>1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_could_not_add_friend_yourself")),
                    'result' => false
                );
        }
        elseif (Phpfox::getService('friend.request')->isRequested(Phpfox::getUserId(), $aUser['user_id']))
        {
            return array(
                'error_code'=>1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.there_is_a_friend_request_is_sent_before")),
                'result' => false
            );
        }
        elseif (Phpfox::getService('friend.request')->isRequested($aUser['user_id'], Phpfox::getUserId()))
        {
            return array(
                'error_code'=>1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.there_is_a_friend_request_is_sent_before")),
                'result' => false
            );
        }
        elseif (Phpfox::getService('friend')->isFriend($aUser['user_id'], Phpfox::getUserId()))
        {
            return array(
                'error_code'=>1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_are_friend_with_the_requested_member")),
                'result' => false
            );
        }
        else if (Phpfox::getService('user.block')->isBlocked($aUser['user_id'], Phpfox::getUserId()) /* is user blocked */
                && (Phpfox::isModule('friend') && Phpfox::getParam('friend.allow_blocked_user_to_friend_request') == false)
        )
        {
            return array(
                'result'=>false,
                'error_code' => 1,
                'error_message' => Phpfox_Error::set('Unable to send a friend request to this user at this moment.')
            );
        }

        if (Phpfox::getService('friend.request.process')->add(Phpfox::getUserId(), $iUserId, (isset($iListId) ? (int) $iListId : 0), $sText))
        {
            return array('result' => true);
        }

        return array('result' => false);
    }
    /**
     * Input data:
     * + iUserId: int, required.
     * + iRequestId: int, required.
     * + iListId: int, optional.
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: bool.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see friend/confirm
     * 
     * @param array $aData
     * @return array
     */
    public function confirm($aData)
    {
        /**
         * @var int
         */
        $iUserId = isset($aData['iUserId']) ? (int) $aData['iUserId'] : 0;
        /**
         * @var int
         */
        $iRequestId = isset($aData['iRequestId']) ? (int) $aData['iRequestId'] : 0;
        /**
         * @var int
         */
        $iListId = isset($aData['iListId']) ? (int) $aData['iListId'] : null;
        
        if ($iUserId < 1 )
        {
            return array(
                'error_code' => 1,
								'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid")),
								'result' => 0
            );
        }

        if (!Phpfox::getUserParam('friend.can_add_friends'))
        {
            return array(
                'error_code' => 1,
								'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_confirm_this_friend")),
								'result' => 0
            );
        }

        if (Phpfox::getService('friend')->isFriend($iUserId, Phpfox::getUserId()))
        {
            Phpfox::getService('friend.request.process')->delete($iRequestId, $iUserId);

            return array(
                'error_code' => 1,
								'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.this_user_is_already_a_friend")),
								'result' => 0
            );
        }

        if (Phpfox::getService('friend.process')->add(Phpfox::getUserId(), $iUserId, (isset($iListId) ? (int) $iListId : 0)))
        {
            return array(
                'error_code' => 0,
								'error_message' => "",
								'result' => 1
            );
        }

				return array(
						'error_code' => 1,
						'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.confirm_failed")),
						'result' => 0
				);
    }
    /**
     * Input data:
     * + iUserId: int, required.
     * + iRequestId: int, required.
     * 
     * Output data:
     * + result: bool.
     * + error_code: int.
     * + error_message: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see friend/deny
     * 
     * @param array $aData
     * @return array
     */
    public function deny($aData)
    {
        /**
         * @var int
         */
        $iUserId = isset($aData['iUserId']) ? (int) $aData['iUserId'] : 0;
        /**
         * @var int
         */
        $iRequestId = isset($aData['iRequestId']) ? (int) $aData['iRequestId'] : 0;
        
        if ($iUserId < 1 )
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        if (!Phpfox::getUserParam('friend.can_add_friends'))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_add_friend"))
            );
        }

        if (Phpfox::getService('friend')->isFriend($iUserId, Phpfox::getUserId()))
        {
            Phpfox::getService('friend.request.process')->delete($iRequestId, $iUserId);

            return array('result' => false);
        }

        if (Phpfox::getService('mfox.helper.friend')->deny(Phpfox::getUserId(), $iUserId))
        {
            return array('result' => true);
        }

        return array('result' => false);
    }

    /**
     * Input data:
     * + iFriendId: int, required.
     * 
     * Output data:
     * + result: bool.
     * + error_code: int.
     * + error_message: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see friend/delete
     * 
     * @param array $aData
     * @return array
     */
    public function delete($aData)
    {
        /**
         * @var int
         */
        $iFriendId = isset($aData['iUserId']) ? (int) $aData['iUserId'] : 0;
        
        if ($iFriendId < 1)
        {
            return array(
                'error_code' => 1,
								'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid")),
								'result' => 0
            );
        }

        if (Phpfox::getService('friend.process')->delete($iFriendId, $bIsFriendId = false))
        {
					return array(
						'error_code' => 0,
						'error_message' => "",
						'result' => 1
					);
        }

				return array(
						'error_code' => 1,
						'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.delete_friend_failed")),
						'result' => 0
				);
    }
    /**
     * Input data:
     * + iPage: int, optional.
     * + iLimit: int, optional.
     * + iRequestId: int, optional.
     * 
     * Output data:
     * + id: int.
     * + sFullName: string.
     * + iUserId: int.
     * + UserProfileImg_Url: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see friend/getfriendsrequest
     * 
     * @param array $aData
     * @return array
     */
    public function getfriendsrequest($aData)
    {
        /**
         * @var int
         */
        $iPage = isset($aData['iPage']) ? (int) $aData['iPage'] : 0;
        /**
         * @var int
         */
        $iLimit = isset($aData['iLimit']) ? (int) $aData['iLimit'] : 5;
        /**
         * @var int
         */
        $iRequestId = isset($aData['iRequestId']) ? (int) $aData['iRequestId'] : 0;
        
        list($iCnt, $aFriends) = Phpfox::getService('friend.request')->get($iPage, $iLimit, $iRequestId);
        /**
         * @var array
         */
        $aResult = array();

        foreach($aFriends as $aFriend)
        {
            $aResult[] = array(
                'id' => $aFriend['request_id'],
                'sFullName' => $aFriend['full_name'],
                'iUserId' => $aFriend['user_id'],
                'UserProfileImg_Url' => Phpfox::getService('mfox.user')->getImageUrl($aFriend, '_50_square')
            );
        }

        return $aResult;
    }

    /**
     * Using for notification.
     * @param array $aNotification
     * @return array
     */
    public function doFriendGetNotificationAccepted($aNotification)
    {
        return array(
            'link' => array('sUserName' => $aNotification['user_name'], 'iUserId' => $aNotification['user_id']),
            'message' =>  Phpfox::getPhrase('friend.full_name_added_you_as_a_friend', array('full_name' => Phpfox::getLib('parse.output')->shorten($aNotification['full_name'], Phpfox::getParam('user.maximum_length_for_full_name')))),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'misc/user.png')
        );
    }

    public function cancelRequest($aData) {
        $result = Phpfox::getService('mfox.helper.friend')->cancelRequest($iRequestingUserId = Phpfox::getUserId(), $iRequested = $aData['iUserId']);
        if($result) {
            return array(
                'error_code' => 0,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.cancel_request_successfully"))
            );
        } else {
            return array(
                'error_code' => 1,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.cancel_request_failed"))
            );
        }
    }

    public function getFromCache($mAllowCustom = false, $sUserSearch = false, $iPage = '', $sLimit = '', $bCount = true)
    {
        $mAllowCustom = false;
        if (Phpfox::getUserBy('profile_page_id'))
        {
            $mAllowCustom = true;
        }
        
        if ($sUserSearch != false)
        {
            if($bCount){
                if (Phpfox::getUserParam('mail.restrict_message_to_friends') == true)
                {
                    $this->database()->join(Phpfox::getT('friend'), 'f', 'u.user_id = f.friend_user_id AND f.user_id=' . Phpfox::getUserId());
                }
                $iCnt = $this->database()->select('COUNT(DISTINCT u.user_id)')
                    ->from(Phpfox::getT('user'),'u')
                    ->where('u.full_name LIKE "%'. Phpfox::getLib('parse.input')->clean($sUserSearch) .'%" AND u.profile_page_id = 0')
                    ->execute('getSlaveField');
            }

            if (Phpfox::getUserParam('mail.restrict_message_to_friends') == true)
            {
                $this->database()->join(Phpfox::getT('friend'), 'f', 'u.user_id = f.friend_user_id AND f.user_id=' . Phpfox::getUserId());
            }
            
            $this->database()->select('' . Phpfox::getUserField())
                ->from(Phpfox::getT('user'),'u')
                ->where('u.full_name LIKE "%'. Phpfox::getLib('parse.input')->clean($sUserSearch) .'%" AND u.profile_page_id = 0')
                ->limit(Phpfox::getParam('friend.friend_cache_limit'))
                ->order('u.last_activity DESC');

            if($bCount){
                $this->database()->limit($iPage, $sLimit, $iCnt);
            } else {
                $this->database()->limit(Phpfox::getParam('friend.friend_cache_limit'));
            }

            $aRows = $this->database()->execute('getSlaveRows');
        }
        else
        {
            if($bCount){
                $iCnt = $this->database()->select('COUNT(DISTINCT f.friend_id)')
                    ->from(Phpfox::getT('friend'), 'f')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = f.friend_user_id')
                    ->where(($mAllowCustom ? '' : 'f.is_page = 0 AND') . ' f.user_id = ' . Phpfox::getUserId())
                    ->execute('getSlaveField');
            }

            $this->database()->select('f.*, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('friend'), 'f')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = f.friend_user_id')
                ->where(($mAllowCustom ? '' : 'f.is_page = 0 AND') . ' f.user_id = ' . Phpfox::getUserId())
                ->order('u.last_activity DESC');

            if($bCount){
                $this->database()->limit($iPage, $sLimit, $iCnt);
            } else {
                $this->database()->limit(Phpfox::getParam('friend.friend_cache_limit'));
            }
            $aRows = $this->database()->execute('getSlaveRows');
        }   
        if($bCount){
            return array($iCnt, $aRows);
        } else {
            return $aRows;
        }

        foreach ($aRows as $iKey => $aRow)
        {       
            if (Phpfox::getUserId() == $aRow['user_id'])
            {
                unset($aRows[$iKey]);
                
                continue;
            }
            
            $aRows[$iKey]['full_name'] = html_entity_decode(Phpfox::getLib('parse.output')->split($aRow['full_name'], 20), null, 'UTF-8');                      
            $aRows[$iKey]['user_profile'] = ($aRow['profile_page_id'] ? Phpfox::getService('pages')->getUrl($aRow['profile_page_id'], '', $aRow['user_name']) : Phpfox::getLib('url')->makeUrl($aRow['user_name']));
            $aRows[$iKey]['is_page'] = ($aRow['profile_page_id'] ? true : false);
            $aRows[$iKey]['user_image'] = Phpfox::getService('mfox.user')->getImageUrl($aRow, '_50_square');
        }       
        
        return $aRows;
    }   

    // SINCE 3.08

    /**
     * @author Nam Nguyen
     * @since  3.08
     */
    public function fetch($aData){
        return $this->get($aData);
    }


}
