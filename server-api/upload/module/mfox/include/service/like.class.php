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
class Mfox_Service_Like extends Phpfox_Service
{
    private $_aTypeSupportDislike = array(
        // 'advancedphoto', 
        'photo', 
        // 'advancedphoto_album', 
        'photo_album', 
        'music_song', 
        'music_album', 
        // 'videochannel', 
        'video', 
        'link', 
        // 'fevent', 
        // 'event', 
        'feed_comment', 
        'feed', 
        'blog', 
        // 'forum', 
        // 'forum_post', 
        // 'pages', 
        'poll', 
        // 'marketplace', 
        // 'advancedmarketplace', 
    );

    public function isTypeSupportDislike($type){
        if (in_array($type, $this->_aTypeSupportDislike)) {
            return true;
        }

        return false;
    }

    /**
     * Change the type if needed.
     * @param string $sType
     * @return string
     */
    public function changeType($sType)
    {
        switch ($sType) {
            case 'feed_mini':
                break;

            case 'music_playlist':
                $sType = 'music_album';
                break;

            case 'marketplace':
                $sType = (Phpfox::getService('mfox.marketplace')->isAdvancedModule() ? 'advancedmarketplace' : 'marketplace');
                break;

            case 'photo':
                $sType = (Phpfox::getService('mfox.photo')->isAdvancedModule() ? 'advancedphoto' : 'photo');
                break;

            case 'event':
                $sType = (Phpfox::getService('mfox.event')->isAdvancedModule() ? 'fevent' : 'event');
                break;

            default:
                break;
        }
        
        return $sType;
    }

    /**
     * Input data:
     * + sType: string, required.
     * + iItemId: int, required.
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see like/add
     * 
     * @global string $token
     * @param array $aData
     * @return array
     */
    public function postAction($aData)
    {
        return $this->add($aData);
    }

    /**
     * Input data (like other objects which are not feed, if it is feed using feed/like)
     * + sItemType: string, required.
     * + iItemId: int, required.
     * + iCommentId: int, optional (like on comment of object (music/album/...))
     *
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     *
     */
    public function add($aData)
    {
        if (Phpfox::isModule('like') == false){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.like_module_is_disable"))
            );
        }
        //  init 
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;
        if (!$sItemType || !$iItemId)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_like_this_item")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        //  process 
        $sType = $sItemType;
        $sModule = isset($aData['sModule']) ? $aData['sModule'] : '';
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;

        $sType = $this->changeType($sType);
        $sModuleName = $sType;
        switch ($sType) {
            case 'advancedphoto':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnPhoto($iItemId);
                $sModuleName = 'advancedphoto';
                break;
            case 'photo':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnPhoto($iItemId);
                $sModuleName = 'photo';
                break;
            
            case 'advancedphoto_album':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnAlbum($iItemId);
                $sModuleName = 'advancedphoto';
                break;
            case 'photo_album':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnAlbum($iItemId);
                $sModuleName = 'photo';
                break;
				
			case 'musicsharing_song':
                $aError = Phpfox::getService('mfox.musicsharing.song')->checkPrivacyOnSong($iItemId);
                $sModuleName = 'musicsharing';
                break;
            
            case 'music_song':
                $aError = Phpfox::getService('mfox.song')->checkPrivacyOnSong($iItemId);
                $sModuleName = 'music';
                break;
			
			case 'musicsharing_album':
                $aError = Phpfox::getService('mfox.musicsharing.album')->checkPrivacyOnMusicAlbum($iItemId);
                $sModuleName = 'musicsharing';
                break;
				
            case 'music_album':
                $aError = Phpfox::getService('mfox.album')->checkPrivacyOnMusicAlbum($iItemId);
                $sModuleName = 'music';
                break;
            
            case 'videochannel':
                $aError = Phpfox::getService('mfox.videochannel')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                $sModuleName = 'videochannel';
                break;
            
            case 'video':
                $aError = Phpfox::getService('mfox.video')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                $sModuleName = 'video';
                break;
            
            case 'user_status':
                $aError = Phpfox::getService('mfox.feed')->checkPrivacyOnUserStatusFeed($iItemId, $sType, $sModule, $iItem);
                $sModuleName = 'user';
                break;

            case 'blog':
                $aError = Phpfox::getService('mfox.blog')->canView($iItemId);
                $sModuleName = 'blog';
                break;

            case 'poll':
                $aError = Phpfox::getService('mfox.poll')->canView($iItemId);
                $sModuleName = 'poll';
                break;

            case 'quiz':
                $sModuleName = 'quiz';
                break;

            case 'forum':
            case 'forum_post':
                $aError = Phpfox::getService('mfox.forum')->canView($iItemId);
                $sModuleName = 'forum_post';
                break;

            case 'advancedmarketplace':
				$aError = Phpfox::getService('mfox.advancedmarketplace')->canView($iItemId);
				$sModuleName  = 'advancedmarketplace';
				break;
            case 'marketplace':
                $aError = Phpfox::getService('mfox.marketplace')->canView($iItemId);
                $sModuleName  = 'marketplace';
                break;                

            case 'pages':
                $aError = Phpfox::getService('mfox.pages')->canView($iItemId);
                $sModuleName = 'pages';
                break;

            case 'groups':
                $aError = Phpfox::getService('mfox.groups')->canView($iItemId);
                $sModuleName = 'groups';
                break;    

            case 'event':
                $sModuleName = 'event';
                break;

            case 'fevent':
                $sModuleName = 'fevent';
                break;
            case 'ultimatevideo_video':
                $sModuleName = 'ultimatevideo';
                break;
            case 'ultimatevideo_playlist':
                $sModuleName = 'ultimatevideo';
                break;

            default:                
                // directory
                break;
        }
        //  end 
        if (isset($aError))
        {
            return $aError;
        }
        if ($iCommentId){
            //  check if liking comment
            $iItemId = $iCommentId;
            $sType = 'feed_mini';
        }
        $success = false;
        if (Phpfox::isModule('like'))
        {
            // remove dislike if existing 
        /*  if (Phpfox::getService('like')->hasBeenMarked(2, $sType, $iItemId))
            {
                // $sTypeId = $sType
                // $sModuleId = $sModuleName - name of module (photo/user/video/music)
                if(empty($sModule) == false){
                    $sModuleName = $sModule;
                }
                $ret = $this->removeAction($sType, $sModuleName, $iItemId);
                if($ret == false){
                    return array(
                        'error_code' => 1,
                        'result' => 0, 
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.action_failed"))
                    );
                }
            }*/

            // if (Phpfox::getService('like.process')->add($sType, $iItemId))
            if (Phpfox::getService('mfox.helper.like')->add($sType, $iItemId))
            {
                $success = true;
            }
        }

        if($success){
            $result = array(
                'result' => 1, 
                'error_code' => 0,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_liked_successful"))
            );
            // custom work reaction
            if (phpfox::isModule('reactions') && $aData['eid']) {
                $iEid = $aData['eid'];
                phpfox::getService('reactions')->updateReaction($sItemType,$iItemId,$iUserID,PHPFOX_TIME,$iEid);
            }
            //
        } else {
            $errors = Phpfox_Error::get();
            $result = array(
                'error_code' => 1,
                'error_message' => isset($errors[0]) ? $errors[0] : '', 
                'result' => 0, 
            );            
        }

        // custom work
        $extra = $this->statistic($aData);
        if ($success) {
            $extra['bIsLiked'] = true;
        }

        return array_merge($result, $extra);
    }

    public function statistic($aData){
        $inputData = $this->changeInputData($aData);            
        $response = array();

        if(isset($inputData['sItemType']) && isset($inputData['iItemId'])){
            $aLike = $this->getListOfLikedUser(
                $inputData['sItemType']
                , $inputData['iItemId']
                , false
                , 999999
            );
            $aUserLike = array();
            foreach($aLike['likes'] as $like){
                $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
            }

            // custom work
            $aEmoticons = Phpfox::getService('mfox')->getReEmoticons();

            foreach($aLike['likes'] as $iKey => $aReactionLike)
            {
                if(!isset($aReactionLike['reaction_id']))
                {
                    continue;
                }
                if($aReactionLike['reaction_id'] == 0)
                {
                    $aReactionLike['reaction_id'] = 1;
                }
                if(isset($aEmoticons[$aReactionLike['reaction_id']]))
                {
                    if(!isset($aEmoticons[$aReactionLike['reaction_id']]['counter']))
                    {
                        $aEmoticons[$aReactionLike['reaction_id']]['counter'] = 0;
                    }
                    $aEmoticons[$aReactionLike['reaction_id']]['counter'] += 1;
                }
            }

            $response['iTotalLike']=  $aLike['feed_total_like'];
            $response['aLikes'] = $aUserLike;
            $response['aReactionEmoticons'] = $aEmoticons;
            $response['bIsLiked'] =  $this->checkIsLiked(
                $inputData['sItemType']
                , $inputData['iItemId']
                , Phpfox::getUserId()
            ); 

           // for dislike 
            $aUserDislike = array();
            $aDislike = $this->getListOfDislikeUser(
                $inputData['sItemType']
                , $inputData['iItemId']
                , $bGetCount = false);
            $response['bIsDisliked'] = false;
            foreach($aDislike as $dislike){
                if(Phpfox::getUserId() ==  $dislike['user_id']){
                    $response['bIsDisliked'] = true;
                }
                $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
            }
            $response['iTotalDislike']=  count($aUserDislike);
            $response['aDislikes'] = $aUserDislike;
        }        

        return $response;
    }
    
    /**
     * Input data:
     * + sType: string, required.
     * + iItemId: int, required.
     * 
     * Output data:
     * + result: int.
     * + error_code: int.
     * + error_message: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see like/delete
     * 
     * @see Like_Service_Process
     * @param array $aData
     * @return array
     */
    public function deleteAction($aData)
    {
        return $this->delete($aData);
    }

    /**
     * Input data: (unlike other objects which are not feed, if it is feed using feed/like)
     * + sItemType: string, required.
     * + iItemId: int, required.
     * + iCommentId: int, optional
     *
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     *
     */
    public function delete($aData)
    {
        if (Phpfox::isModule('like') == false){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.like_module_is_disable"))
            );
        }
        //  init 
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;
        if (!$sItemType || !$iItemId)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_delete_this_action")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();
        //  process 
        $sType = $sItemType;
        $sModule = isset($aData['sModule']) ? $aData['sModule'] : '';
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;

        $sType = $this->changeType($sType);
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
            
            case 'videochannel':
                $aError = Phpfox::getService('mfox.videochannel')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                break;
            
            case 'video':
                $aError = Phpfox::getService('mfox.video')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                break;
            
            case 'user_status':
                $aError = Phpfox::getService('mfox.feed')->checkPrivacyOnUserStatusFeed($iItemId, $sType, $sModule, $iItem);
                break;

            case 'blog':
                $aError = Phpfox::getService('mfox.blog')->canView($iItemId);
                $sModuleName = 'blog';
                break;

            case 'poll':
                $aError = Phpfox::getService('mfox.poll')->canView($iItemId);
                $sModuleName = 'poll';
                break;

            case 'quiz':
                $sModuleName = 'quiz';
                break;

            case 'forum':
            case 'forum_post':
                $aError = Phpfox::getService('mfox.forum')->canView($iItemId);
                $sModuleName = 'forum_post';
                break;

            case 'advancedmarketplace':
				$aError = Phpfox::getService('mfox.advancedmarketplace')->canView($iItemId);
				$sModule = 'advancedmarketplace';
				break;
				
            case 'marketplace':
                $aError = Phpfox::getService('mfox.marketplace')->canView($iItemId);
                $sModuleName = 'marketplace';
                break;

            case 'pages':
                $aError = Phpfox::getService('mfox.pages')->canView($iItemId);
                $sModuleName = 'pages';
                break;
            case 'groups':
                $aError = Phpfox::getService('mfox.groups')->canView($iItemId);
                $sModuleName = 'groups';
                break;        
            case 'event':
                $sModuleName = 'event';
                break;

            case 'fevent':
                $sModuleName = 'fevent';
                break;

            case 'ultimatevideo_video':
                $sModuleName = 'ultimatevideo';
                break;
            case 'ultimatevideo_playlist':
                $sModuleName = 'ultimatevideo';
                break;

            default:
                // directory
                break;
        }
        //  end 
        if (isset($aError))
        {
            return $aError;
        }

        if ($iCommentId){
            //  check if liking comment
            $iItemId = $iCommentId;
            $sType = 'feed_mini';
        }

        $success = false;
        if (Phpfox::isModule('like'))
        {
            if (Phpfox::getService('like.process')->delete($sType, $iItemId))
            {
                $success = true;
            }
        }

        $extra = $this->statistic($aData);
        if($success){
            $result = array(
                'result' => 1, 
                'error_code' => 0,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_unliked_successful")),
            );
        } else {
            $errors = Phpfox_Error::get();
            $result = array(
                'error_code' => 1,
                'result' => 0, 
                'error_message' => isset($errors[0]) ? $errors[0] : '', 
            );            
        }

        return array_merge($result, $extra);        
    }
    
    /**
     * Input data:
     * + sType: string, required.
     * + iItemId: int, required.
     * + lastLikeIdViewed: int, required.
     * + amountOfLike: int, required.
     * 
     * Output data:
	 * + iLikeId: int
	 * + iUserId: int
	 * + sFullName: string
	 * + sImage: string
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see like/listalllikes
     * 
     * @param array $aData
     * @return array
     */
    public function getAction($aData)
    {
        return $this->listalllikes($aData);
    }

    /**
     * Input data:
     * + iId: int, required.
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     * 
     * @param array $aData
     * @param int $iId
     * @return array
     */
    function deleteByIdAction($aData, $iId)
    {
        /**
         * @var array
         */
        $aLike = $this->database()
                ->select('l.type_id AS sType, l.item_id AS iItemId')
                ->from(Phpfox::getT('like'), 'l')
                ->where('l.like_id = ' . (int) $iId)
                ->execute('getRow');
        if (!$aLike)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unlike_with_error"))
            );
        }
        return $this->delete($aLike);
    }

    /**
     * Input data: (not support for feed yet, using feed/listalllikes)
     * Get new likes
     * + sItemType: string, required.
     * + iItemId: int, required.
     * + iLastLikeIdViewed: int, optional.
     * + iAmountOfLike: int, optional.
     *
     * Output data:
     * + iLikeId: int
     * + iUserId: int
     * + sFullName: string
     * + sImage: string
     *
     */
    public function listalllikes($aData)
    {
        if (Phpfox::isModule('like') == false){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.like_module_is_disable"))
            );
        }
        
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        if('feed' == $sItemType){
            if(isset($aData['sParentId']) 
                && ('event' == $aData['sParentId'] 
                    || 'fevent' == $aData['sParentId']
                    || 'pages' == $aData['sParentId']
                    || 'directory' == $aData['sParentId']
                )
            ){
                    $sItemType =  $aData['sParentId'] . '_comment';
            } else {
                $feed = Phpfox::getService('feed')->getFeed($iItemId);
                if(!isset($feed['feed_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.activity_does_not_exist"))
                    );
                }

                $feed['type_id'] = Phpfox::getService('mfox.feed')->changeType($feed['type_id']);
                $sItemType = $feed['type_id'];
                $iItemId = $feed['item_id'];                
            }
        }

        $lastLikeIdViewed = isset($aData['iLastLikeIdViewed']) ? (int)$aData['iLastLikeIdViewed'] : 0;
        $amountOfLike = (isset($aData['iAmountOfLike']) && (int)$aData['iAmountOfLike'] > 0)? (int)$aData['iAmountOfLike'] : 20;
        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;
        if (empty($sItemType) || $iItemId < 1)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        //  process 
        $sType = $sItemType;
        $sModule = isset($aData['sModule']) ? $aData['sModule'] : '';
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;

        $sType = $this->changeType($sType);
        $sModuleName = $sType;
        switch ($sType) {
            case 'advancedphoto':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnPhoto($iItemId);
                $sModuleName = 'advancedphoto';
                break;
            case 'photo':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnPhoto($iItemId);
                $sModuleName = 'photo';
                break;
            
            case 'advancedphoto_album':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnAlbum($iItemId);
                $sModuleName = 'advancedphoto';
                break;
            case 'photo_album':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnAlbum($iItemId);
                $sModuleName = 'photo';
                break;
            
            case 'music_song':
                $aError = Phpfox::getService('mfox.song')->checkPrivacyOnSong($iItemId);
                $sModuleName = 'music';
                break;
            
            case 'music_album':
                $aError = Phpfox::getService('mfox.album')->checkPrivacyOnMusicAlbum($iItemId);
                $sModuleName = 'music';
                break;
            
            case 'videochannel':
                $aError = Phpfox::getService('mfox.videochannel')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                $sModuleName = 'videochannel';
                break;

            case 'video':
                $aError = Phpfox::getService('mfox.video')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                $sModuleName = 'video';
                break;

            case 'fevent_comment':
            case 'event_comment':
                if ($iCommentId){
                } else {
                    $aEventFeed = Phpfox::getService('mfox.event')->getEventFeedByID($iItemId);
                    $iItemId = $aEventFeed['item_id'];                    
                    $sType = $aEventFeed['type_id'];
                }
                $sModuleName = ($sType == 'fevent_comment') ? 'fevent' : 'event';
                break;

            case 'pages_comment':
                if ($iCommentId){
                } else {
                    $aPagesFeed = Phpfox::getService('mfox.pages')->getPagesFeedByID($iItemId);
                    $iItemId = $aPagesFeed['item_id'];                    
                    $sType = $aPagesFeed['type_id'];
                }
                $sModuleName = 'pages';
                break;
            
            case 'user_status':
                $aError = Phpfox::getService('mfox.feed')->checkPrivacyOnUserStatusFeed($iItemId, $sType, $sModule, $iItem);
                $sModuleName = 'user';
                break;
            
            case 'blog':
                $aError = Phpfox::getService('mfox.blog')->canView($iItemId);
                $sModuleName = 'blog';
                break;

            case 'poll':
                $aError = Phpfox::getService('mfox.poll')->canView($iItemId);
                $sModuleName = 'poll';
                break;

            case 'quiz':
                $sModuleName = 'quiz';
                break;

            case 'forum':
            case 'forum_post':
                // $aError = Phpfox::getService('mfox.forum')->canView($iItemId);
                $sModuleName = 'forum_post';
                break;

            case 'advancedmarketplace':
				$aError = Phpfox::getService('mfox.advancedmarketplace')->canView($iItemId);
				$sModuleName = 'advancedmarketplace';
				
				break;
            case 'marketplace':
                $aError = Phpfox::getService('mfox.marketplace')->canView($iItemId);
                $sModuleName = 'marketplace';
				
                break;

            case 'directory_comment':
                if ($iCommentId) {
                } else {
                    $aFeed = Phpfox::getService('mfox.directory')->getDirectoryFeedById($iItemId);
                    $iItemId = $aFeed['item_id'];                    
                    $sType = $aFeed['type_id'];
                }
                $sModuleName = 'directory';
                break;

            default:
                // directory
                break;
        }
        if (isset($aError))
        {
            return $aError;
        }
		

        if ($iCommentId){
            //  check if liking comment
            $iItemId = $iCommentId;
            $sType = 'feed_mini';
        }
        $aLikes = $this->database()
                ->select('l.like_id, l.user_id, u.full_name, u.user_image, u.server_id AS user_server_id')
                ->from(Phpfox::getT('like'), 'l')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
                ->where('l.type_id = \'' . $this->database()->escape($sType) . '\' AND l.item_id = ' . (int) $iItemId . ($lastLikeIdViewed > 0 ? ' AND l.like_id > ' . $lastLikeIdViewed : ''))
                ->order('l.like_id DESC')
                ->limit((int)$amountOfLike)
                ->execute('getRows');
                
        $aResult = array();
        foreach($aLikes as $aLike)
        {
            $aResult[] = array(
                'iLikeId' => $aLike['like_id'],
                'iUserId' => $aLike['user_id'],
                'sFullName' => $aLike['full_name'],
                'sImage' => Phpfox::getService('mfox.user')->getImageUrl($aLike, '_50_square')
            );
        }
        return $aResult;
    }

    public function getLikeCount($aData)
    {
        if (Phpfox::isModule('like') == false){
            return 0;
        }
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $pos = strpos($sItemType, '_comment');
        if ($pos === false) {
            $isUpdateItem = true;
        } else {
            $isUpdateItem = false;
        }        

        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;
        if (empty($sItemType) || $iItemId < 1)
        {
            return 0;
        }

        if('feed' == $sItemType){
            if(isset($aData['sParentId']) 
                && ('event' == $aData['sParentId'] 
                    || 'fevent' == $aData['sParentId']
                    || 'pages' == $aData['sParentId']
                    || 'directory' == $aData['sParentId']
                )
            ){
                $sItemType =  $aData['sParentId'] . '_comment';
            } else {
                $feed = Phpfox::getService('feed')->getFeed($iItemId);
                if(!isset($feed['feed_id'])){
                    return 0;
                }

                $feed['type_id'] = Phpfox::getService('mfox.feed')->changeType($feed['type_id']);
                $sItemType = $feed['type_id'];
                $iItemId = $feed['item_id'];                
            }
        }

        //  process 
        $sType = $sItemType;
        $sModule = isset($aData['sModule']) ? $aData['sModule'] : '';
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;

        $sType = $this->changeType($sType);
        $sModuleName = $sType;
        switch ($sType) {
            case 'advancedphoto':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnPhoto($iItemId);
                $sModuleName = 'advancedphoto';
                break;
            case 'photo':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnPhoto($iItemId);
                $sModuleName = 'photo';
                break;
            
            case 'advancedphoto_album':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnAlbum($iItemId);
                $sModuleName = 'advancedphoto';
                break;
            case 'photo_album':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnAlbum($iItemId);
                $sModuleName = 'photo';
                break;
            
            case 'music_song':
                $aError = Phpfox::getService('mfox.song')->checkPrivacyOnSong($iItemId);
                $sModuleName = 'music';
                break;
            
            case 'music_album':
                $aError = Phpfox::getService('mfox.album')->checkPrivacyOnMusicAlbum($iItemId);
                $sModuleName = 'music';
                break;
            
            case 'videochannel':
                $aError = Phpfox::getService('mfox.videochannel')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                $sModuleName = 'videochannel';
                break;

            case 'video':
                $aError = Phpfox::getService('mfox.video')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                $sModuleName = 'video';
                break;

            case 'fevent_comment':
            case 'event_comment':
                if ($iCommentId){
                } else {
                    if($isUpdateItem){
                        $aEventFeed = Phpfox::getService('mfox.event')->getEventFeedByID($iItemId);
                        $iItemId = $aEventFeed['item_id'];                                            
                    }
                }
                $sModuleName = ($sType == 'fevent_comment') ? 'fevent' : 'event';;
                break;
            
            case 'user_status':
                $aError = Phpfox::getService('mfox.feed')->checkPrivacyOnUserStatusFeed($iItemId, $sType, $sModule, $iItem);
                $sModuleName = 'user';
                break;
            
            case 'blog':
                $aError = Phpfox::getService('mfox.blog')->canView($iItemId);
                $sModuleName = 'blog';
                break;

            case 'poll':
                $aError = Phpfox::getService('mfox.poll')->canView($iItemId);
                $sModuleName = 'poll';
                break;

            case 'quiz':
                $sModuleName = 'quiz';
                break;

            case 'forum':
            case 'forum_post':
                // $aError = Phpfox::getService('mfox.forum')->canView($iItemId);
                $sModuleName = 'forum_post';
                break;

            case 'advancedmarketplace':
            case 'marketplace':
			
                $aError = Phpfox::getService('mfox.marketplace')->canView($iItemId);
                $sModuleName = Phpfox::getService('mfox.marketplace')->isAdvancedModule() ? 'advancedmarketplace' : 'marketplace';
                break;                

            case 'pages_comment':
                if ($iCommentId){
                } else {
                    if($isUpdateItem){
                        $aPagesFeed = Phpfox::getService('mfox.pages')->getPagesFeedByID($iItemId);
                        $iItemId = $aPagesFeed['item_id'];                    
                    }
                }
                $sModuleName = 'pages';
                break;

            case 'directory_comment':
                if ($iCommentId) {
                } else {
                    if($isUpdateItem){
                        $aFeed = Phpfox::getService('mfox.directory')->getDirectoryFeedById($iItemId);
                        $iItemId = $aFeed['item_id'];                    
                    }
                }
                $sModuleName = 'directory';
                break;

            default:
                // directory
                break;
        }
        if (isset($aError))
        {
            return 0;
        }

        if ($iCommentId){
            //  check if liking comment
            $iItemId = $iCommentId;
            $sType = 'feed_mini';
        }
        return $this->database()
                ->select('COUNT(l.like_id)')
                ->from(Phpfox::getT('like'), 'l')
                ->where('l.type_id = \'' . $this->database()->escape($sType) . '\' AND l.item_id = ' . (int) $iItemId)
                ->execute('getSlaveField');                
    }

    public function getListOfLikedUser($type, $id, $isLike = false, $limit = null){

        if(null == $limit){
            $limit = Phpfox::getParam('feed.total_likes_to_display');
        }

        $aLike = array();
        $aLike['likes'] = Phpfox::getService('mfox.helper.like')->getLikesForFeed(
            $type
            , $id
            ,  $isLike
            , $limit
            , true);              
        $aLike['feed_total_like'] = Phpfox::getService('mfox.helper.like')->getTotalLikeCount();

        return $aLike;
    }

    public function getListOfDislikeUser($sType, $iItemId, $bGetCount = false){
        return $this->getDislikes($sType, $iItemId, $bGetCount);
    }

    public function dislikeadd($aData){
        if (Phpfox::isModule('like') == false){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.like_module_is_disable"))
            );
        }
        //  init 
        if($this->allowdislike() == false){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.dislike_setting_is_disable"))
            );
        }

        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        $action_type_id = isset($aData['iActionTypeId']) ? (int)$aData['iActionTypeId'] : 2;
        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;
        if (!$sItemType || !$iItemId)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_dislike_this_item")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        //  process 
        $sType = $sItemType;
        $sModule = isset($aData['sModule']) ? $aData['sModule'] : '';
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;

        if ($iCommentId){
            //  check if disliking comment
            $iItemId = $iCommentId;
            $sType = 'feed_mini';
        }

        $sType = $this->changeType($sType);
        $module_name = $sType;
        switch ($sType) {
            case 'advancedphoto':
                $aError = Phpfox::getService('mfox.advancedphoto')->checkPrivacyOnPhoto($iItemId);
                $module_name = 'photo';
                break;
            case 'photo':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnPhoto($iItemId);
                $module_name = 'photo';
                break;
            
            case 'advancedphoto_album':
                $aError = Phpfox::getService('mfox.advancedphoto')->checkPrivacyOnAlbum($iItemId);
                $module_name = 'photo';
                break;
            case 'photo_album':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnAlbum($iItemId);
                $module_name = 'photo';
                break;
            
            case 'music_song':
                $aError = Phpfox::getService('mfox.song')->checkPrivacyOnSong($iItemId);
                $module_name = 'music';
                break;
            
            case 'music_album':
                if (!$iCommentId){
                    return array(
                        'error_code' => 1,
                        'result' => 0, 
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_dislike_for_music_album"))
                    );
                }
                $aError = Phpfox::getService('mfox.album')->checkPrivacyOnMusicAlbum($iItemId);
                $module_name = 'music';
                break;
            
            case 'videochannel':
                $aError = Phpfox::getService('mfox.videochannel')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                $module_name = 'videochannel';
                break;
            
            case 'video':
                $aError = Phpfox::getService('mfox.video')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                $module_name = 'video';
                break;
            
            case 'user_status':
                $aError = Phpfox::getService('mfox.feed')->checkPrivacyOnUserStatusFeed($iItemId, $sType, $sModule, $iItem);
                $module_name = 'user';
                break;

            case 'link':
                $module_name = 'link';
                break;

            case 'fevent':
                $module_name = 'fevent';
                break;
            case 'event':
                $module_name = 'event';
                break;
            case 'feed_comment':
                $module_name = 'feed';
                break;

            case 'blog':
                $aError = Phpfox::getService('mfox.blog')->canView($iItemId);
                $module_name = 'blog';
                break;

            case 'poll':
                $aError = Phpfox::getService('mfox.poll')->canView($iItemId);
                $module_name = 'poll';
                break;

            case 'quiz':
                $module_name = 'quiz';
                break;

            case 'pages':
                $module_name = 'pages';
                break;

            case 'advancedmarketplace':
            case 'marketplace':
                $aError = Phpfox::getService('mfox.marketplace')->canView($iItemId);
                $module_name = Phpfox::getService('mfox.marketplace')->isAdvancedModule() ? 'advancedmarketplace' : 'marketplace';
                break;

            default:
                // directory
                break;
        }
        if ($iCommentId){
            //  check if disliking comment
            $module_name = 'comment';
        }

        //  end 
        if (isset($aError))
        {
            return $aError;
        }

        // check isDisliked
        $shouldDislike = true;
        $msg = '';
        if(Phpfox::getService('mfox.like')->checkIsDisliked(
            $sType
            , $iItemId
            , Phpfox::getUserId()
            ) == true
        ){
            $shouldDislike = false;
            $msg = 'You have already disliked this item';
        }

        $success = false;
        if (Phpfox::isModule('like') && $shouldDislike)
        {
            if (Phpfox::getService('like.process')->doAction(
                $action_type_id
                , $sType
                , $iItemId
                , $module_name )
                )
            {
                $success = true;
            }
        }

        $extra = $this->statistic($aData);
        if($success){
            $result = array(
                'result' => 1, 
                'error_code' => 0,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_disliked_successful")),
            );
        } else {
            $errors = Phpfox_Error::get();
            $result = array(
                'error_code' => 1,
                'result' => 0, 
                'error_message' => isset($errors[0]) ? $errors[0] : $msg, 
            );            
        }

        return array_merge($result, $extra);        
    }

    public function dislikedelete($aData){
        if (Phpfox::isModule('like') == false){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.like_module_is_disable"))
            );
        }
        if($this->allowdislike() == false){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.dislike_setting_is_disable"))
            );
        }
        //  init 
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;
        if (!$sItemType || !$iItemId)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_remove_this_action")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();
        //  process 
        $sType = $sItemType;
        $sModule = isset($aData['sModule']) ? $aData['sModule'] : '';
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;

        $sType = $this->changeType($sType);
        $module_name = $sType;
        switch ($sType) {
            case 'advancedphoto':
                $aError = Phpfox::getService('mfox.advancedphoto')->checkPrivacyOnPhoto($iItemId);
                $module_name = 'photo';
                break;
            case 'photo':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnPhoto($iItemId);
                $module_name = 'photo';
                break;
            
            case 'advancedphoto_album':
                $aError = Phpfox::getService('mfox.advancedphoto')->checkPrivacyOnAlbum($iItemId);
                $module_name = 'photo';
                break;
            case 'photo_album':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnAlbum($iItemId);
                $module_name = 'photo';
                break;
            
            case 'music_song':
                $aError = Phpfox::getService('mfox.song')->checkPrivacyOnSong($iItemId);
                $module_name = 'music';
                break;
            
            case 'music_album':
                if (!$iCommentId){
                    return array(
                        'error_code' => 1,
                        'result' => 0, 
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_dislike_for_music_album"))
                    );
                }
                $aError = Phpfox::getService('mfox.album')->checkPrivacyOnMusicAlbum($iItemId);
                $module_name = 'music';
                break;
            
            case 'videochannel':
                $aError = Phpfox::getService('mfox.videochannel')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                $module_name = 'videochannel';
                break;
            
            case 'video':
                $aError = Phpfox::getService('mfox.video')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                $module_name = 'video';
                break;
            
            case 'user_status':
                $aError = Phpfox::getService('mfox.feed')->checkPrivacyOnUserStatusFeed($iItemId, $sType, $sModule, $iItem);
                $module_name = 'user';
                break;
				
            case 'feed_comment':
                $module_name = 'feed';
                break;

            case 'blog':
                $aError = Phpfox::getService('mfox.blog')->canView($iItemId);
                $module_name = 'blog';
                break;

            case 'poll':
                $aError = Phpfox::getService('mfox.poll')->canView($iItemId);
                $module_name = 'poll';
                break;

            case 'quiz':
                $module_name = 'quiz';
                break;

            case 'pages':
                $module_name = 'pages';
                break;

            case 'advancedmarketplace':
            case 'marketplace':
                $aError = Phpfox::getService('mfox.marketplace')->canView($iItemId);
                $module_name = Phpfox::getService('mfox.marketplace')->isAdvancedModule() ? 'advancedmarketplace' : 'marketplace';
                break;

            case 'event':
                $module_name = 'event';
                break;

            case 'fevent':
                $module_name = 'fevent';
                break;

            default:
                // directory
                break;
        }

        if ($iCommentId){
            //  check if liking comment
            $iItemId = $iCommentId;
            $sType = 'feed_mini';
            $module_name = 'comment';
        }

        $success = false;
        if($this->removeAction($sType, $module_name, $iItemId)){
            $success = true;
        }

        $extra = $this->statistic($aData);
        if($success){
            $result = array(
                'result' => 1, 
                'error_code' => 0,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_removed_disliked_successful")),
            );
        } else {
            $errors = Phpfox_Error::get();
            $result = array(
                'error_code' => 1,
                'result' => 0, 
                'error_message' => isset($errors[0]) ? $errors[0] : '', 
            );            
        }

        return array_merge($result, $extra);           
    }

    public function dislikelistalldislike($aData){
        if (Phpfox::isModule('like') == false){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.like_module_is_disable"))
            );
        }
        if($this->allowdislike() == false){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.dislike_setting_is_disable"))
            );
        }

        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        if('feed' == $sItemType){
            $feed = Phpfox::getService('feed')->getFeed($iItemId);
            if(!isset($feed['feed_id'])){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.activity_does_not_exist"))
                );
            }

            $feed['type_id'] = Phpfox::getService('mfox.feed')->changeType($feed['type_id']);
            $sItemType = $feed['type_id'];
            $iItemId = $feed['item_id'];                
        }

        $iLastDislikeIdViewed = isset($aData['iLastDislikeIdViewed']) ? (int)$aData['iLastDislikeIdViewed'] : 0;
        $iAmountOfDislike = (isset($aData['iAmountOfDislike']) && (int)$aData['iAmountOfDislike'] > 0)? (int)$aData['iAmountOfDislike'] : 20;
        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;
        if (empty($sItemType) || $iItemId < 1)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        //  process 
        $sType = $sItemType;
        $sModule = isset($aData['sModule']) ? $aData['sModule'] : '';
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;

        $sType = $this->changeType($sType);
        $sModuleName = $sType;
        switch ($sType) {
            case 'advancedphoto':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnPhoto($iItemId);
                $sModuleName = 'advancedphoto';
                break;
            case 'photo':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnPhoto($iItemId);
                $sModuleName = 'photo';
                break;
            
            case 'advancedphoto_album':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnAlbum($iItemId);
                $sModuleName = 'advancedphoto';
                break;
            case 'photo_album':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnAlbum($iItemId);
                $sModuleName = 'photo';
                break;
            
            case 'music_song':
                $aError = Phpfox::getService('mfox.song')->checkPrivacyOnSong($iItemId);
                $sModuleName = 'music';
                break;
            
            case 'music_album':
                if (!$iCommentId){
                    return array(
                        'error_code' => 1,
                        'result' => 0, 
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_dislike_for_music_album"))
                    );
                }
                $aError = Phpfox::getService('mfox.album')->checkPrivacyOnMusicAlbum($iItemId);
                $sModuleName = 'music';
                break;

            case 'videochannel':
                $aError = Phpfox::getService('mfox.videochannel')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                $sModuleName = 'videochannel';
                break;
            
            case 'video':
                $aError = Phpfox::getService('mfox.video')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                $sModuleName = 'video';
                break;
            
            case 'user_status':
                $aError = Phpfox::getService('mfox.feed')->checkPrivacyOnUserStatusFeed($iItemId, $sType, $sModule, $iItem);
                $sModuleName = 'user';
                break;
            
            case 'blog':
                $aError = Phpfox::getService('mfox.blog')->canView($iItemId);
                $sModuleName = 'blog';
                break;

            case 'poll':
                $aError = Phpfox::getService('mfox.poll')->canView($iItemId);
                $sModuleName = 'poll';
                break;

            case 'quiz':
                $sModuleName = 'quiz';
                break;

            case 'advancedmarketplace':
            case 'marketplace':
                $aError = Phpfox::getService('mfox.marketplace')->canView($iItemId);
                $sModuleName = Phpfox::getService('mfox.marketplace')->isAdvancedModule() ? 'advancedmarketplace' : 'marketplace';
                break;

            case 'event':
                $sModuleName = 'event';
                break;

            case 'fevent':
                $sModuleName = 'fevent';
                break;

            default:
                // directory
                break;
        }

        if (isset($aError))
        {
            return $aError;
        }


        if ($iCommentId){
            //  check if liking comment
            $iItemId = $iCommentId;
            $sType = 'feed_mini';
        }        

        if($sType == 'feed_mini'){
            $aDislikes = $this->getDislikes($sType, $iItemId, $bGetCount = false, $iLastDislikeIdViewed, $iAmountOfDislike);
        } else {
            $aDislikes = $this->getActionsFor($sType, $iItemId, $iLastDislikeIdViewed, $iAmountOfDislike);    
        }
        
        $aResult = array();
        foreach($aDislikes as $aDislike)
        {
            $aResult[] = array(
                'iActionId' => $aDislike['action_id'],
                'iActionTypeId' => $aDislike['action_type_id'],
                'iUserId' => $aDislike['user_id'],
                'sFullName' => $aDislike['full_name'],
                'sImage' => Phpfox::getService('mfox.user')->getImageUrl($aDislike, '_50_square')
            );
        }
        return $aResult;
    }

    public function allowdislike($isClient = false){
        if (Phpfox::getParam('like.allow_dislike'))
        {
            if($isClient){
                $errors = Phpfox_Error::get();
                return array(
                    'error_code' => 0,
                    'bAllowDislike' => true, 
                    'error_message' => isset($errors[0]) ? $errors[0] : '', 
                );                
            } else {
                return true;
            }
        }

        if($isClient){
            $errors = Phpfox_Error::get();
            return array(
                'error_code' => 1,
                'bAllowDislike' => false, 
                'error_message' => isset($errors[0]) ? $errors[0] : '', 
            );
        } else {
            return false;            
        }
    }

    public function removeAction($sTypeId = '', $sModuleId = '', $iItemId){
        // its not decrementing the total_dislike column        
        if (Phpfox::getService('like.process')->removeAction( 2
            , $sTypeId
            , $iItemId
            , $sModuleId ))
        {
            return true;
        }

        return false;
    }

    public function getActionsFor($sItemTypeId, $iItemId, $iLastDislikeIdViewed = null, $iAmountOfDislike = null){
        $oParse = Phpfox::getLib('parse.input');
        $sItemTypeId = str_replace('_','-', $sItemTypeId);
        $aModule = explode('-', $sItemTypeId);
        if ($aModule[0] == 'feed' && $sItemTypeId != 'feed-comment')
        {
            $sItemTypeId = 'feed';
        }
    
        $oUrl = Phpfox::getLib('url');
        // Check that the module exists
        if (!Phpfox::isModule($aModule[0]) || !Phpfox::hasCallback($aModule[0], 'getActions'))
        {
            return false;           
        }

        $aCallback = Phpfox::callback($aModule[0] . '.getActions');
        // find this specific callback
        $aThisAction = null;
        foreach ($aCallback as $aAction)
        {
            if (str_replace('_', '-', $aAction['item_type_id']) == $sItemTypeId || ($aModule[0] . '-' . $aAction['item_type_id']) == $sItemTypeId || ($aModule[0] == $aAction['item_type_id']))
            {
                $aThisAction = $aAction;
                break;
            }
        }
        
        if ($aThisAction == null)
        {
            return false;           
        }
        if (!isset($aThisAction['phrase_in_past_tense']))
        {
            return false;           
        }

        $sCond = '';
        if($iLastDislikeIdViewed != null && (int)$iLastDislikeIdViewed > 0){
            $sCond = ' AND a.action_id > ' . $iLastDislikeIdViewed;
        }
        $sWhere = 'a.item_type_id = "'. $oParse->clean($sItemTypeId) .'" AND a.item_id = ' . (int)$iItemId;

        if($iAmountOfDislike != null && (int)$iAmountOfDislike > 0){
            $this->database()->limit((int)$iAmountOfDislike);
        }
        
        // get all the actions related to this item
        $aActions = $this->database()->select('a.*, u.user_name, u.full_name, f.friend_id as is_friend, l.type_id as like_type_id, l.time_stamp as like_time_stamp, u.server_id AS user_server_id, u.user_image')
            ->from(Phpfox::getT('action'), 'a')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = a.user_id')
            ->leftjoin(Phpfox::getT('friend'), 'f', 'f.friend_user_id = a.user_id AND f.user_id = ' . Phpfox::getUserId())
            ->leftjoin(Phpfox::getT('like'), 'l', 'l.item_id = a.item_id AND l.type_id = "'. str_replace('-', '_', $oParse->clean($sItemTypeId)) .'"')
            ->group('a.action_id')
            ->where($sWhere . $sCond)
            ->execute('getSlaveRows');

        return $aActions;
    }

    public function getDislikes($sType, $iItemId, $bGetCount = false, $iLastDislikeIdViewed = null, $iAmountOfDislike = null)
    {
        if ($sType == 'feed_mini')
        {
            $sType = 'comment';
        }
        if ('feed_comment' != $sType
			&& strpos($sType, 'feed') !== false
			)
        {
            $sType = 'feed';
        }
        $sType = str_replace('_', '-', $sType);

        $sCond = '';
        if($iLastDislikeIdViewed != null && (int)$iLastDislikeIdViewed > 0){
            $sCond = ' AND a.action_id > ' . $iLastDislikeIdViewed;
        }

        if ($bGetCount == true)
        {
            $this->database()
                ->select('COUNT(*)')
                ->order('u.full_name ASC');
            $sGetHow = 'getSlaveField';
        }
        else
        {
            if($iAmountOfDislike != null && (int)$iAmountOfDislike > 0){
                $this->database()->limit((int)$iAmountOfDislike);
            }

            $this->database()
                ->select('a.*, ' . Phpfox::getUserField() )
                ->group('a.action_id');
            $sGetHow = 'getSlaveRows';
        }
        $aDislikes = $this->database()
            ->from(Phpfox::getT('action'), 'a')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = a.user_id')          
            ->where('a.item_type_id = "' . $this->database()->escape($sType) . '" AND a.item_id = ' . (int)$iItemId . $sCond)            
            ->execute($sGetHow);
        return $aDislikes;
    }

    public function checkIsDisliked($sType, $iItemId, $userID = null){
        if(null == $userID){
            $userID = Phpfox::getUserId();
        }

        if ($sType == 'feed_mini')
        {
            $sType = 'comment';
        }
        if ('feed_comment' != $sType
			&& strpos($sType, 'feed') !== false
			)
        {
            $sType = 'feed';
        }

        $sType = str_replace('_', '-', $sType);
        $this->database()
            ->select('a.user_id')
            ->group('a.user_id');
        $sGetHow = 'getRow';
        $ret = $this->database()
            ->from(Phpfox::getT('action'), 'a')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = a.user_id')          
            ->where('a.item_type_id = "' . $this->database()->escape($sType) 
                . '" AND a.item_id = ' . (int)$iItemId
                . ' AND a.user_id = ' . (int)$userID
            )            
            ->execute($sGetHow);
        if(isset($ret['user_id']) && (int)$ret['user_id'] > 0){
            return true;
        }

        return false;
    }

    public function checkIsLiked($sType, $iItemId, $userID = null){
        if(null == $userID){
            $userID = Phpfox::getUserId();
        }

        if ($sType == 'feed')
        {
            $sCond = ('(l.type_id = "feed" OR l.type_id = "feed_comment") AND l.item_id = ' . (int)$iItemId);
        }
        else
        {
            $sCond = ('l.type_id = \'' . $this->database()->escape($sType) . '\' AND l.item_id = ' . (int) $iItemId);
        }
        $ret = $this->database()->select('u.user_id')
            ->from(Phpfox::getT('like'), 'l')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
            ->group('u.user_id')
            ->where($sCond
                . ' AND l.user_id = ' . (int)$userID
            )            
            ->execute('getRow');
        
        if(isset($ret['user_id']) && (int)$ret['user_id'] > 0){
            return true;
        }

        return false;
    }

    public function like($aData){
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;
        $sParentId = isset($aData['sParentId']) ? $aData['sParentId'] : 0;

        if (!$sItemType || !$iItemId)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_like_this_item")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();
        $sType = $sItemType;
        $sType = $this->changeType($sType);

        if('feed' === $sParentId) {
            $sType = 'feed';
        }

        switch ($sType) {
            case 'advancedphoto':
            case 'photo':
            case 'advancedphoto_album':
            case 'photo_album':
            case 'music_song':
            case 'music_album':
            case 'videochannel':
            case 'video':
            case 'blog':
            case 'pages':
            case 'groups':
            case 'forum':
            case 'forum_post':
            case 'poll':
            case 'quiz':
            case 'marketplace':
            case 'advancedmarketplace':
			case 'musicsharing_album':
			case 'musicsharing_song':
            case 'event':
            case 'fevent':
            case 'ultimatevideo_video':
            case 'ultimatevideo_playlist':
            case 'directory':
                return $this->add($aData);
                break;
            case 'user_status':
                $feed = Phpfox::getService('feed')->getForItem('user_status', $aData['iItemId']);
                if($feed){
                    $aData['iFeedId'] = $feed['feed_id'];
                }else{
                    $aData['iFeedId'] = $aData['iItemId'];
                }
                return Phpfox::getService('mfox.feed')->like($aData);
                break;
            case 'feed':
            default:
                $aData['iFeedId'] = $aData['iItemId'];
                return Phpfox::getService('mfox.feed')->like($aData);
                break;
        }
    }

    public function unlike($aData)
    {
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;
        $sParentId = isset($aData['sParentId']) ? $aData['sParentId'] : 0;

        if (!$sItemType || !$iItemId)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_unlike_this_action")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        $sType = $sItemType;
        $sType = $this->changeType($sType);

        if($sParentId === 'feed') {
            $sType = 'feed';
        }
        switch ($sType) {
            case 'advancedphoto':
            case 'photo':
            case 'advancedphoto_album':
            case 'photo_album':
            case 'music_song':
            case 'music_album':
            case 'videochannel':
            case 'video':
            case 'blog':
            case 'pages':
            case 'groups':
            case 'forum':
            case 'forum_post':
            case 'poll':
            case 'quiz':
            case 'marketplace':
            case 'advancedmarketplace':
			case 'musicsharing_album':
			case 'musicsharing_song':
            case 'event':
            case 'fevent':
            case 'directory':
            case 'ultimatevideo_video':
            case 'ultimatevideo_playlist':
                return $this->delete($aData);
                break;
            case 'user_status':
                $feed = Phpfox::getService('feed')->getForItem('user_status', $aData['iItemId']);
                if($feed){
                    $aData['iFeedId'] = $feed['feed_id'];
                }else{
                    $aData['iFeedId'] = $aData['iItemId'];
                }
                return Phpfox::getService('mfox.feed')->unlike($aData);
                break;
            case 'feed':
            default:
                $aData['iFeedId'] = $aData['iItemId'];
                return Phpfox::getService('mfox.feed')->unlike($aData);
                break;
        }

    }

    public function changeInputData($aData)
    {
        if (Phpfox::isModule('like') == false){
            return false;
        }
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        if('feed' == $sItemType){
            if(isset($aData['sParentId']) 
                && ('event' == $aData['sParentId'] 
                    || 'fevent' == $aData['sParentId']
                    || 'pages' == $aData['sParentId']
                    || 'directory' == $aData['sParentId']
                )
            ){
                $sItemType = $aData['sParentId'] . '_comment';
            } else {
                $feed = Phpfox::getService('feed')->getFeed($iItemId);
                if(!isset($feed['feed_id'])){
                    return false;
                }

                $feed['type_id'] = Phpfox::getService('mfox.feed')->changeType($feed['type_id']);
                $sItemType = $feed['type_id'];
                $iItemId = $feed['item_id'];                
            }
        }

        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;
        if (empty($sItemType) || $iItemId < 1)
        {
            return false;
        }

        //  process 
        $sType = $sItemType;
        $sType = $this->changeType($sType);

        if ($iCommentId){
            //  check if liking comment
            $iItemId = $iCommentId;
            $sType = 'feed_mini';
        }
        return array('sItemType' => $sType, 'iItemId' => $iItemId);
    }

    public function dislike($aData){
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;
        $action_type_id = isset($aData['iActionTypeId']) ? (int)$aData['iActionTypeId'] : 2;
        $sParentId = isset($aData['sParentId']) ? $aData['sParentId'] : '';

        if (!$sItemType || !$iItemId)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_dislike_this_item")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        $sType = $sItemType;
        $sType = $this->changeType($sType);

        if($sParentId === 'feed') {
            $sType = 'feed';
        }

        switch ($sType) {
            case 'advancedphoto':
            case 'photo':
            case 'advancedphoto_album':
            case 'photo_album':
            case 'music_song':
            case 'music_album':
            case 'videochannel':
            case 'video':
            case 'link':
            case 'fevent':
            case 'fevent_comment':
            case 'event':
            case 'event_comment':
            case 'feed_comment':
            case 'blog':
            case 'pages':
            case 'poll':
            case 'quiz':
            case 'marketplace':
            case 'advancedmarketplace':
            case 'directory':
            case 'directory_comment':
                return $this->dislikeadd($aData);
                break;
            case 'user_status':
                $feed = Phpfox::getService('feed')->getForItem('user_status', $aData['iItemId']);
                if($feed){
                    $aData['iFeedId'] = $feed['feed_id'];
                }else{
                    $aData['iFeedId'] = $aData['iItemId'];
                }
                return Phpfox::getService('mfox.feed')->dislike($aData);
                break;
            case 'feed':
            default:
                $aData['iFeedId'] = $aData['iItemId'];
                return Phpfox::getService('mfox.feed')->dislike($aData);
                break;
        }
    }    

    public function removedislike($aData)
    {
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;

        if (!$sItemType || !$iItemId)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_unlike_this_action")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        $sType = $sItemType;
        $sType = $this->changeType($sType);
        switch ($sType) {
            case 'advancedphoto':
            case 'photo':
            case 'advancedphoto_album':
            case 'photo_album':
            case 'music_song':
            case 'music_album':
            case 'fevent':
            case 'fevent_comment':
            case 'event':
            case 'event_comment':
            case 'videochannel':
            case 'video':
            case 'blog':
            case 'pages':
            case 'poll':
            case 'quiz':
            case 'marketplace':
            case 'advancedmarketplace':
            case 'directory':
            case 'directory_comment':
                return $this->dislikedelete($aData);
                break;
            case 'user_status':
                $feed = Phpfox::getService('feed')->getForItem('user_status', $aData['iItemId']);
                if($feed){
                    $aData['iFeedId'] = $feed['feed_id'];
                }else{
                    $aData['iFeedId'] = $aData['iItemId'];
                }
                return Phpfox::getService('mfox.feed')->dislikedelete($aData);
                break;
            case 'feed':
            default:
                $aData['iFeedId'] = $aData['iItemId'];
                return Phpfox::getService('mfox.feed')->dislikedelete($aData);
                break;
        }

    }

    public function canLike($aFeed){
        if(Phpfox::isUser() && Phpfox::isModule('like') && isset($aFeed['like_type_id'])){
            return true;
        }

        return false;
    }

}
