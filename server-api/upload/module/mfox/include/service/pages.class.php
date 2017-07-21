<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Pages extends Phpfox_Service 
{
	
	private $_sDefaultImagePath = null;
	
	private $_sDefaultCoverImagePath = null;
	
	function __construct(){
		$this->_sDefaultImagePath =  Phpfox::getParam('core.url_module') . 'mfox/static/image/pages_default.png';
		$this->_sDefaultCoverImagePath =  Phpfox::getParam('core.url_module') . 'mfox/static/image/pages_default.png';
	}
	
	function getDefaultImagePath(){
		return $this->_sDefaultImagePath;
	}
	
	function getDefaultCoverImagePath(){
		return $this->_sDefaultCoverImagePath;	
	}
	
    public function doPagesGetNotificationComment_Feed($aNotification)
    {
        $aRow = $this->database()->select('fc.feed_comment_id, u.user_id, u.gender, u.user_name, u.full_name, e.page_id, e.title, pu.vanity_url')
                ->from(Phpfox::getT('pages_feed_comment'), 'fc')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
                ->join(Phpfox::getT('pages'), 'e', 'e.page_id = fc.parent_user_id')
                ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = e.page_id')
                ->where('fc.feed_comment_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');
        if (!isset($aRow['feed_comment_id']))
        {
            return array();
        }
        if ($aNotification['user_id'] == $aRow['user_id'] && isset($aNotification['extra_users']) && count($aNotification['extra_users']))
        {
            $sUsers = Phpfox::getService('notification')->getUsers($aNotification, true);
        }
        else
        {
            $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        }

        $sGender = Phpfox::getService('user')->gender($aRow['gender'], 1);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...');
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            if (isset($aNotification['extra_users']) && count($aNotification['extra_users']))
            {
                $sPhrase =  Phpfox::getPhrase('pages.users_commented_on_span_class_drop_data_user_full_name_s_span_comment_on_the_page_title', array('users' => $sUsers, 'full_name' => $aRow['full_name'], 'title' => $sTitle));
            }

            else
            {
                $sPhrase =  Phpfox::getPhrase('pages.users_commented_on_gender_own_comment_on_the_page_title', array('users' => $sUsers, 'gender' => $sGender, 'title' => $sTitle));
            }
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('pages.users_commented_on_one_of_your_comments_on_the_page_title', array('users' => $sUsers, 'title' => $sTitle));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('pages.users_commented_on_one_of_full_name', array('users' => $sUsers, 'full_name' => $aRow['full_name'], 'title' => $sTitle));
        }
        return array(
            'link' => array(
                'iPageId' => $aRow['page_id'],
                'sTitle' => $aRow['title']
            ),
            'message' => strip_tags($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }

    public function setPagesView($status = true){
        define('PHPFOX_IS_PAGES_VIEW', $status);        
    }

    public function formadd($aData)
    {
        $response  =  array(
            'view_options'=> Phpfox::getService('mfox.privacy')->privacy($aData),
            'comment_options'=> Phpfox::getService('mfox.privacy')->privacycomment($aData),
            'category_options'=> $this->categories(),
        );
        
        return $response;
    }

    public function formedit($aData){
        return array_merge( 
            $this->info($aData), 
            $this->formadd($aData)
        );
    }        

    public function delete($aData){
        $iPageId = isset($aData['iPageId']) ? (int) $aData['iPageId'] : 0;
        if ($iPageId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'Page id is not valid!');
        }

        $aPage = Phpfox::getService('pages')->getForView($iPageId);
        if (!($aPage) || !isset($aPage['page_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('pages.the_page_you_are_looking_for_cannot_be_found'));
        }

        if (Phpfox::getUserBy('profile_page_id') <= 0 && Phpfox::isModule('privacy'))
        {
            if(!Phpfox::getService('privacy')->check('pages', $aPage['page_id'], $aPage['user_id'], $aPage['privacy'], (isset($aPage['is_friend']) ? $aPage['is_friend'] : 0), true)){
                return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
            }
        }    

        if (Phpfox::getService('pages.process')->delete($iPageId))
        {
            return array('result' => 1
                , 'error_code' => 0
                , 'message' =>  Phpfox::getPhrase('pages.page_successfully_deleted')
            );
        }

        return array('result' => 0
            , 'error_code' => 1
            , 'error_message' => 'Page has been deleted or you do not have permission to delete it!'
        );
    }

    public function fetch($aData){
        return $this->getPages($aData);
    }

    public function getPages($aData){
        // init 
        if (!isset($aData['iPage'])){
            $aData['iPage'] = 1;
        }
        if ((int)$aData['iPage'] == 0){
            return array();
        }

        $sSortBy = (isset($aData['sOrder']) && empty($aData['sOrder']) == false) ? $aData['sOrder'] : 'latest';
        $iCategoryId = (isset($aData['iCategoryId']) && (int)$aData['iCategoryId'] > 0) ? $aData['iCategoryId'] : 0;
        $iAmountOfPages = isset($aData['iAmountOfPages']) ? (int) $aData['iAmountOfPages'] : 10;
        $sSearch = isset($aData['sSearch']) ? $aData['sSearch'] : '';
        $sView = isset($aData['sView']) ? $aData['sView'] : '';
        $bIsProfile = (isset($aData['bIsProfile']) && $aData['bIsProfile'] == 'true') ? true : false;
        if ($bIsProfile)
        {
            $iProfileId = isset($aData['iProfileId']) ? (int) $aData['iProfileId'] : 0;
            $aUser = Phpfox::getService('user')->get($iProfileId);
            if (!isset($aUser['user_id']))
            {
                return array('result' => 0, 'error_code' => 1, 'error_message' => 'Profile is not valid!');
            }
        }

        // process 
        $aCond = array();

        if (!empty($sSearch))
        {
            $aCond[] = ' ( '
                        . 'pages.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"'
                        . ' ) ';
        }

        if ((int)$iCategoryId > 0)
        {
            $aType = Phpfox::getService('pages.type')->getById($iCategoryId);
            if (isset($aType['type_id']))
            {
                $aCond[] = ' pages.type_id = ' . (int) $aType['type_id'];
            }            
            if (isset($aType['category_id']))
            {
                $aCond[] = ' pages.category_id = ' . (int) $aType['category_id'];
            }
        }

        // Check privacy.
        switch ($sView) {
            // case 'friend':
            //     break;
            case 'my':
                $aCond[] = ' pages.item_type = 0 AND pages.app_id = 0 AND pages.view_id IN(0,1) AND pages.user_id = ' . Phpfox::getUserId();
                break;
            case 'pending':
                if (!Phpfox::getUserParam('pages.can_moderate_pages'))
                {
                    return array('result' => 0, 'error_code' => 1, 'error_message' => 'You do not have permission to approve pages!');
                }               
                $aCond[] = ' pages.item_type = 0 AND pages.app_id = 0 AND pages.view_id = 1';
                break;
            default:
                if (Phpfox::getUserParam('privacy.can_view_all_items'))
                {
                    $aCond[] = ' pages.item_type = 0 AND pages.app_id = 0';
                }
                else
                {
                    $aCond[] = ' pages.item_type = 0 AND pages.app_id = 0 AND pages.view_id = 0 AND pages.privacy IN(0)';
                }
                break;
        }

        // get count
        $this->database()
                ->select('COUNT(pages.page_id)')
                ->from(Phpfox::getT('pages'), 'pages');
        if (!$bIsProfile && $sView == 'friend')
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = pages.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }
        if ((int)$iCategoryId > 0) {
            if (isset($aType['type_id']))
            {
                $this->database()
                ->innerJoin(Phpfox::getT('pages_type'), 'ptype', 'ptype.type_id = pages.type_id')
                ->leftJoin(Phpfox::getT('pages_category'), 'pc', 'pc.category_id = pages.category_id');
            }            
            if (isset($aType['category_id']))
            {
                $this->database()
                ->innerJoin(Phpfox::getT('pages_category'), 'pc', 'pc.category_id = pages.category_id');
            }
        }
        // get counter
        $this->getQueryJoins($sView, true);
        $iCount = $this->database()
                ->where(implode(' AND ', $aCond))
                ->limit(1)
                ->execute('getField');
        if ($iCount == 0)
        {
            return array();
        }
        // check current page with total results 
        list($fromResult, $toResult, $isShowPrev, $pagePrev, $isShowNext, $pageNext) = Phpfox::getService('mfox.core')->caculatePaging(
            (int)$iCount, (int)$iAmountOfPages, (int)$aData['iPage'] - 1);
        if($pageNext == 0){
            return array();
        }

        // get data 
        $this->database()
                ->select('lik.like_id AS is_liked
                    , pages.*
                    , pt.text_parsed AS text
                    , pu.vanity_url
                    , pc.page_type, pc.name AS category_name
                    , u2.server_id AS profile_server_id, u2.user_image AS profile_user_image
                    , u.user_id, u.profile_page_id, u.server_id AS user_server_id, u.user_name, u.full_name, u.gender, u.user_image, u.is_invisible, u.user_group_id, u.language_id'
                )
                ->from(Phpfox::getT('pages'), 'pages');
        // Check friend condition.
        if (!$bIsProfile && $sView == 'friend')
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = pages.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }

        // sort by  
        $sOrder = 'pages.time_stamp DESC';
        if ($sSortBy == 'latest'){
            $sOrder = 'pages.time_stamp DESC';
        } else if ($sSortBy == 'most_liked'){
            $sOrder = 'pages.total_like DESC';
        }

        // get data
        if ((int)$iCategoryId > 0) {
            if (isset($aType['type_id']))
            {
                $this->database()
                ->innerJoin(Phpfox::getT('pages_type'), 'ptype', 'ptype.type_id = pages.type_id')
                ->leftJoin(Phpfox::getT('pages_category'), 'pc', 'pc.category_id = pages.category_id');
            }            
            if (isset($aType['category_id']))
            {
                $this->database()
                ->innerJoin(Phpfox::getT('pages_category'), 'pc', 'pc.category_id = pages.category_id');
            }
        } else {
            $this->database()
                ->leftJoin(Phpfox::getT('pages_category'), 'pc', 'pc.category_id = pages.category_id');
        }

        $this->getQueryJoins($sView);
        $aRows = $this->database()
                ->join(Phpfox::getT('pages_text'), 'pt', 'pt.page_id = pages.page_id')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = pages.user_id')
                ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = pages.page_id')
                ->leftJoin(Phpfox::getT('user'), 'u2', 'u2.profile_page_id = pages.page_id')
                ->leftJoin(Phpfox::getT('like'), 'lik', "lik.type_id = 'pages' AND lik.item_id = pages.page_id AND lik.user_id = " . Phpfox::getUserId())
                ->where(implode(' AND ', $aCond))
                ->order($sOrder)
                ->limit((int) $aData['iPage'], $iAmountOfPages, $iCount)
                ->execute('getRows');

        $aResult = array();
        foreach ($aRows as $aItem)
        {
            $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aItem, '_50_square');
				
			if($aItem['profile_user_image']){
				$sAvatarImage = Phpfox::getLib('image.helper')->display(array(
	                    'server_id' => $aItem['profile_server_id'],
	                    'path' => 'pages.url_image',
	                    'file' => $aItem['image_path'],
	                    'suffix' => '_120',
	                    'is_page_image' => true,
	                    'return_url' => true
	                        )
	                );	
			}else{
				$sAvatarImage =  $this->getDefaultImagePath();
			}

            
			
            $aCoverPhoto = $this->getCoverPhoto($aItem['cover_photo_id']);
            $sCoverImage = '';
			
			$bHasCover = 0;
			
            if(isset($aCoverPhoto['photo_id'])){
            	$bHasCover =  1;
                $sCoverImage = Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aCoverPhoto['server_id'],
                        'path' => 'photo.url_photo',
                        'file' => $aCoverPhoto['destination'],
                        'suffix' => '',
                        'return_url' => true
                            )
                    );
            }else{
            	$sCoverImage =  $this->getDefaultCoverImagePath();
            }

            $aLike['likes'] = Phpfox::getService('mfox.helper.like')->getLikesForFeed('pages', $aItem['page_id']
                ,  $aItem['is_liked'], 1000, true);              
            // $aLike['feed_total_like'] = Phpfox::getService('like')->getTotalLikeCount();
            $aUserLike = array();
            foreach($aLike['likes'] as $like){
                $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
            }
            $iTotalLike = count($aUserLike);     

            $aUserDislike = array();
            $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('pages', $aItem['page_id'], $bGetCount = false);
            foreach($aDislike as $dislike){
                $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
            }                    
            $iTotalDislike = count($aUserDislike);  

            $bCanComment = $this->canPostComment($aItem['page_id']);
            if(isset($bCanComment['error_code'])){
                $bCanComment = false;
            } else {
                $bCanComment = true;
            }
            $bCanView = $this->canView($aItem['page_id']);
            if(isset($bCanView['error_code'])){
                $bCanView = false;
            } else {
                $bCanView = true;
            }

            list($iTotalMembers, $aMembers) = $this->getMembers($aItem);
			
			$bCanEdit  =  0;
			$bCanDelete = 0;
			$bIsOwner = 0;
			
			
			if(Phpfox::getUserId() == $aItem['user_id']){
				$bIsOwner =  true;
			}else if ($aItem['module_id'] == 'pages' && $aItem['item_id']>0){
				$bIsAdmin =  1;
				$bIsOwner = Phpfox::getService('pages')->isAdmin($aItem['page_id']);
			}
			
			if (Phpfox::getUserParam('pages.can_moderate_pages')){
				$bCanEdit =  1;
				$bCanDelete = 1;
			}else {
				$bCanEdit =  $bIsOwner;
				$bCanDelete =  $bIsOwner;
			}
			
			
			

            $aResult[] = array(
                'bIsLiked' => $aItem['is_liked'],
                'bCanDislike' => Phpfox::getService('mfox.like')->isTypeSupportDislike('pages'),
                'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('pages', $aItem['page_id'], Phpfox::getUserId()),
                'aUserLike' => $aUserLike,
                'aUserDislike' => $aUserDislike,
                'bIsFriend' => $aItem['is_friend'],
                'iPageId' => $aItem['page_id'],
                'iPrivacy' => $aItem['privacy'],
                // 'iPrivacyComment' => $aItem['privacy_comment'],
                'iUserId' => $aItem['user_id'],
                'sTitle' => $aItem['title'],
                // 'sText' => $aItem['text'],
                'iTotalComment' => $aItem['total_comment'],
                'iTotalLike' => $iTotalLike,
                'iTotalDislike' => $iTotalDislike,
                'iTimeStamp' => $aItem['time_stamp'],
                'sTimeStamp' => date('D, j M Y G:i:s O', $aItem['time_stamp']),
                'sFullTimeStamp' => Phpfox::getLib('date')->convertTime($aItem['time_stamp'], null),
                // 'sModuleId' => $aItem['module_id'],
                // 'iItemId' => $aItem['item_id'],
                'iProfilePageId' => $aItem['profile_page_id'],
                'sUserName' => $aItem['user_name'],
                'sFullname' => $aItem['full_name'],
                'iGender' => $aItem['gender'],
                'sUserImage' => $sUserImage . '?t=' . time(),
                'sImagePath' => $sUserImage .'?t=' . time(),
                'bIsInvisible' => $aItem['is_invisible'],
                'iUserGroupId' => $aItem['user_group_id'],
                'iLanguageId' => $aItem['language_id'],
                'bCanComment' => $bCanComment,
                'iUserLevelId' => $aItem['user_group_id'],
                'bCanView' => $bCanView,
                'sCategoryName' => (Phpfox::isPhrase($aItem['category_name'])) ? Phpfox::getService('mfox')->decodeUtf8Compat(_p($aItem['category_name'])) :Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getLib('locale')->convert($aItem['category_name'])),
                'sAvatarImage' => $sAvatarImage . '?t=' . time(),
                'sCoverImage' => $sCoverImage,
                'bHasCover'=>$bHasCover,
                'bHasImage'=>$aItem['profile_user_image']?1:0,
                'sText' => $aItem['text'],
                'iPageType' => (int) $aItem['page_type'],
                'iTotalMembers' => $iTotalMembers,
                'bCanEdit'=>$bCanEdit,
                'bCanDelete'=>$bCanDelete,
                'bIsOwner'=>$bIsOwner,
            );   
        }

        return $aResult;
    }

    public function canView($iItemId){
        if (!Phpfox::getUserParam('pages.can_view_browse_pages'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'You do not have permission to access pages!');
        }

        if (!($aItem = Phpfox::getService('pages')->getForView($iItemId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'The pages you are looking for cannot be found.');
        }
        if (Phpfox::getUserParam('pages.can_moderate_pages') || $aItem['is_admin'])
        {
            
        }
        else
        {
            if ($aItem['view_id'] != '0')
            {
                return array('result' => 0, 'error_code' => 1, 'error_message' => 'The pages you are looking for cannot be found.');
            }
        }        
        if ($aItem['view_id'] == '2')
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'The pages you are looking for cannot be found.');
        }       

        if(Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('pages', $aItem['page_id'], $aItem['user_id'], $aItem['privacy'], (isset($aItem['is_friend']) ? $aItem['is_friend'] : 0), true)){
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        $bHasPermToViewPageFeed = Phpfox::getService('pages')->hasPerm($aItem['page_id'], 'pages.view_browse_updates');
        if($bHasPermToViewPageFeed == false){
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'You do not have permission to access pages!');
        }

        // return null - it means TRUE
        return null;
    }

    public function canPostComment($iItemId){
        if (!Phpfox::getUserParam('pages.can_view_browse_pages'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'You do not have permission to access pages!');
        }

        if (!($aItem = Phpfox::getService('pages')->getForView($iItemId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'The pages you are looking for cannot be found.');
        }
        if (Phpfox::getUserParam('pages.can_moderate_pages') || $aItem['is_admin'])
        {
            
        }
        else
        {
            if ($aItem['view_id'] != '0')
            {
                return array('result' => 0, 'error_code' => 1, 'error_message' => 'The pages you are looking for cannot be found.');
            }
        }        
        if ($aItem['view_id'] == '2')
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'The pages you are looking for cannot be found.');
        }       

        if(Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('pages', $aItem['page_id'], $aItem['user_id'], $aItem['privacy'], (isset($aItem['is_friend']) ? $aItem['is_friend'] : 0), true)){
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        $bHasPermToViewPageFeed = Phpfox::getService('pages')->hasPerm($aItem['page_id'], 'pages.view_browse_updates');
        if($bHasPermToViewPageFeed == false){
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'You do not have permission to access pages!');
        }

        $bCanPostComment = true;
        if (Phpfox::getUserId())
        {
            $bIsBlocked = Phpfox::getService('user.block')->isBlocked($aItem['user_id'], Phpfox::getUserId());
            if ($bIsBlocked)
            {
                $bCanPostComment = false;
            }
        }           

        $aFeedCallback = array(
                'module' => 'pages',
                'table_prefix' => 'pages_',
                'ajax_request' => 'pages.addFeedComment',
                'item_id' => $aItem['page_id'],
                'disable_share' => ($bCanPostComment ? false : true),
                'feed_comment' => 'pages_comment'               
        );  
        $this->setPagesView(true);        
        if (defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'pages.share_updates'))
        {
            $aFeedCallback['disable_share'] = true;
        }       


        if( ((int)$aItem['profile_page_id'] > 0 && defined('PHPFOX_IS_USER_PROFILE')) 
            || (isset($aFeedCallback['disable_share']) && $aFeedCallback['disable_share']) 
            ){
            // cannot post status
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'You do not have permission to access pages!');
        } else {
            // can post status
        }

        // return null - it means TRUE
        return null;
    }

    public function getCoverPhoto($cover_photo_id){
        $aCoverPhoto = Phpfox::getService('photo')->getCoverPhoto($cover_photo_id);
        if (!isset($aCoverPhoto['photo_id']))
        {
            return false;
        }

        return $aCoverPhoto;
    }

    public function getMembers($aPage = null, $iPageId = null){
        if(null == $aPage){
            $aPage = Phpfox::getService('pages')->getForView((int)$iPageId);
        }

        $aMembers = array();
        if ($aPage['page_type'] == '1')
        {
            list($iTotalMembers, $aMembers) = Phpfox::getService('pages')->getMembers($aPage['page_id']);
            foreach ($aMembers as $key => $value) {
                $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($value, '_50_square');

                $aMembers[$key]['user_image'] = $sUserImage;
            }
            return array($iTotalMembers, $aMembers);
        }

        $type_id = 'pages';
        $item_id = $aPage['page_id'];
        $aLikes = Phpfox::getService('like')->getLikes($type_id, $item_id);
        foreach ($aLikes as $key => $value) {
            $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($value, '_50_square');

            $aLikes[$key]['user_image'] = $sUserImage;
        }

        return array($aPage['total_like'], $aLikes);
    }

    public function info($aData){
        $iPageId = isset($aData['iPageId']) ? (int) $aData['iPageId'] : 0;
        if ($iPageId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'Page id is not valid!');
        }

        $aPage = Phpfox::getService('pages')->getForView($iPageId);
        if (!($aPage) || !isset($aPage['page_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('pages.the_page_you_are_looking_for_cannot_be_found'));
        }


		if($aPage['image_path']){
				$sAvatarImage = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aPage['profile_server_id'],
                'path' => 'pages.url_image',
                'file' => $aPage['pages_image_path'],
                'suffix' => '_120',
                'is_page_image' => true,
                'return_url' => true
                    )
            );	
		}else{
			$sAvatarImage =  $this->getDefaultImagePath();
		}

        // get cover
        $aCoverPhoto = $this->getCoverPhoto($aPage['cover_photo_id']);
		$bHasCover =  0;
        $sCoverImage = '';
        if(isset($aCoverPhoto['photo_id'])){
        	$bHasCover = 1;
            $sCoverImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aCoverPhoto['server_id'],
                    'path' => 'photo.url_photo',
                    'file' => $aCoverPhoto['destination'],
                    'suffix' => '',
                    'return_url' => true
                        )
                );
        }else{
        	$sCoverImage =  $this->getDefaultCoverImagePath();
        }

        // get member
        list($iTotalMembers, $aMembers) = $this->getMembers($aPage);         

        // get admins
        $aAdmins = $this->getAdminsByPageId($aPage['page_id'], $aPage);
        $iTotalAdmins = count($aAdmins);

        // extra info
        $bCanView = $this->canView($aPage['page_id']);
        if(isset($bCanView['error_code']) == false){
            $bCanView = true;
        } else {
            $bCanView = false;
        }
        $bCanComment = $this->canPostComment($aPage['page_id']);
        if(isset($bCanComment['error_code']) == false){
            $bCanComment = true;
        } else {
            $bCanComment = false;
        }

        $bIsLiked = Phpfox::getService('mfox.like')->checkIsLiked(
                    'pages'
                    , $aPage['page_id']
                    , Phpfox::getUserId()
        ); 
        $bIsInvited = $this->isInvited($aPage['page_id'], Phpfox::getUserId());

        // get permission on module(s)
        list($bCanShareBlogs, 
            $bCanShareEvents, 
            $bCanShareMusic, 
            $bCanSharePhotos, 
            $bCanShareVideos, 
            $bCanShareVideochannel, 
            $bCanInvite) = $this->getPermissions($aPage);

        $aUserDislike = array();
        $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('pages', $aPage['page_id'], $bGetCount = false);
        foreach($aDislike as $dislike){
            $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
        }   
        $iTotalDislike = count($aUserDislike);    
		
		$bCanEdit  =  0;
		$bCanDelete = 0;
		$bIsOwner = 0;
		
		
		if(Phpfox::getUserId() == $aPage['user_id']){
			$bIsOwner =  1;
			$bCanEdit =  1;
			$bCanDelete = 1;
		}else if(Phpfox::getService('pages')->isAdmin($iPageId)){
			$bIsAdmin =  1;
			$bCanEdit =  1;
			$bCanDelete = 0 ;
		}
		
		if (Phpfox::getUserParam('pages.can_moderate_pages')){
			$bCanEdit =  1;
			$bCanDelete = 1;
		}
		
        $aReturn = array(
            'iPageId' => $aPage['page_id'], 
            'iUserId' => $aPage['user_id'], 
            'sTitle' => $aPage['title'], 
            'sText' => Phpfox::getLib('phpfox.parse.output')->parse($aPage['text']),
            'sAvatarImage' => $sAvatarImage . '?t=' . time(), 
            'sCoverImage' => $sCoverImage, 
            'bHasCover'=>$bHasCover,
            'sCategoryName' => Phpfox::isPhrase($aPage['category_name']) ? Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase($aPage['category_name'])) : Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getLib('locale')->convert($aPage['category_name'])), 
            'iTotalMembers' => $iTotalMembers, 
            'aMembers' => $aMembers, 
            'iTotalAdmins' => $iTotalAdmins, 
            'aAdmins' => $aAdmins, 
            'bIsOwner' => ($aPage['user_id'] == Phpfox::getUserId() ? true : false), 
            'bCanView' => $bCanView, 
            'bCanComment' => $bCanComment, 
            'bIsLiked' => $bIsLiked, 
            'bIsInvited' => $bIsInvited, 
            'bCanShareBlogs' => $bCanShareBlogs, 
            'bCanShareEvents' => $bCanShareEvents, 
            'bCanShareMusic' => $bCanShareMusic, 
            'bCanSharePhotos' => $bCanSharePhotos, 
            'bCanShareVideos' => $bCanShareVideos, 
            'bCanShareVideochannel' => $bCanShareVideochannel, 
            'bCanInvite' => $bCanInvite, 
            'iTypeId' => $aPage['type_id'], 
            'bHasImage'=>$aPage['image_path']?1:0,
            'iCategoryId' => $aPage['category_id'], 
            'bCanDislike' => Phpfox::getService('mfox.like')->isTypeSupportDislike('pages'),
            'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('pages', $aPage['page_id'], Phpfox::getUserId()),
            'aUserDislike' => $aUserDislike,
            'iTotalDislike' => $iTotalDislike,
            'bCanEdit'=>$bCanEdit,
            'bCanDelete'=>$bCanDelete,
            'bIsOwner'=>$bIsOwner,
            'bCanLike' => $this->_canLike($aPage),
            'bCanUnlike' => $this->_canUnlike($aPage),
            'iPageType' => (int) $aPage['page_type'],
            'iRegMethod' => $aPage['reg_method'],
            'bCanShowAdmin' => Phpfox::getParam('pages.show_page_admins'),
        );

        $aEnabledMenus = $this->get_enabled_menus();
        $aReturn = array_merge($aReturn, $aEnabledMenus);
        
        return $aReturn;
    }

    private function _canUnlike($aPage)
    {
        $result = false;

        if (!Phpfox::getUserBy('profile_page_id')) {
            if ($aPage['is_liked']) {
                $result = true;
            }
        }

        return $result;
    }

    private function _canLike($aPage)
    {
        $result = false;
        
        if (!Phpfox::getUserBy('profile_page_id') && Phpfox::isUser()) {
            if ($aPage['reg_method'] == '2' && !isset($aPage['is_invited']) && $aPage['page_type'] == '1') {
            } else {
                if (isset($aPage['is_reg']) && $aPage['is_reg']) {
                } else {
                    if (isset($aPage['is_liked']) && $aPage['is_liked'] != true) {
                        $result = true;
                    }
                }
            }
        }

        return $result;
    }

    public function signup($aData)
    {
        $pageId = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;
        if (empty($pageId)) {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        if (!Phpfox::isUser()) {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.token_is_not_valid'),
            );
        }

        if (Phpfox::getService('pages.process')->register($pageId))
        {
            return array(
                'error_code' => 0,
                'message' => Phpfox::getPhrase('pages.successfully_registered_for_this_page'),
            );
        }

        return array(
            'error_code' => 1,
            'error_message' => implode(' ', Phpfox_Error::get()),
        );
    }

    public function get_enabled_menus()
    {
        $aReturn = array(
            'bEnabledBlogs' => $this->has_profile_menu('blog'),
            'bEnabledEvents' => Phpfox::getService('mfox.event')->isAdvancedModule() ? $this->has_profile_menu('fevent') : $this->has_profile_menu('event'),
            'bEnabledMusic' => Phpfox::getService('mfox.song')->isAdvancedModule() ? $this->has_profile_menu('musicsharing') : $this->has_profile_menu('music'),
            'bEnabledPhotos' => Phpfox::getService('mfox.photo')->isAdvancedModule() ? $this->has_profile_menu('advancedphoto') : $this->has_profile_menu('photo'),
            'bEnabledVideos' => $this->has_profile_menu('video'),
            'bEnabledVideochannel' => $this->has_profile_menu('videochannel'),
        );

        return $aReturn;
    }

    public function has_profile_menu($sModule)
    {
        return Phpfox::isModule($sModule) && Phpfox::hasCallback($sModule, 'getProfileMenu');
    }

    public function getInvitedUserIds($iPageId) {
        $aRows = $this->database()->select('invited_user_id')
             ->from(Phpfox::getT('pages_invite'))
             ->where('page_id  = ' . $iPageId)
             ->execute('getRows');
        $aIds = array(Phpfox::getUserId());
        foreach($aRows as $aRow) {
            $aIds[] = $aRow['invited_user_id'];
        }

        return $aIds;   
    }    

    public function getinvitepeople($aData){
        $iPageId = isset($aData['iPageId']) ? (int) $aData['iPageId'] : 0;
        if ($iPageId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'Page id is not valid!');
        }

        $aPage = Phpfox::getService('pages')->getPage($iPageId);
        if (!($aPage) || !isset($aPage['page_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('pages.the_page_you_are_looking_for_cannot_be_found'));
        }

        $aConds =  array();
        $aInvitedIds = $this->getInvitedUserIds($iPageId);
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

    public function invite($aData){
        $iPageId = isset($aData['iPageId']) ? (int) $aData['iPageId'] : 0;
        if ($iPageId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'Page id is not valid!');
        }

        $aPage = Phpfox::getService('pages')->getForEdit($iPageId);
        if (!($aPage) || !isset($aPage['page_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('pages.the_page_you_are_looking_for_cannot_be_found'));
        }

        // init
        $oParseInput = Phpfox::getLib('parse.input');
        $aVals = array('invite' => explode(',', $aData['sUserId']));

        // process 
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
        $aUsers = $this->database()->select('user_id, email, language_id, full_name')
            ->from(Phpfox::getT('user'))
            ->where('user_id IN(' . $sUserIds . ')')
            ->execute('getSlaveRows');
        $bSent = false;
        $sLink = Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);

        foreach ($aUsers as $aUser)
        {            
            $sMessage =  Phpfox::getPhrase('pages.full_name_invited_you_to_the_page_title', array('full_name' => Phpfox::getUserBy('full_name'), 'title' => $aPage['title']));
            $sMessage .= "\n" .  Phpfox::getPhrase('pages.to_view_this_page_click_the_link_below_a_href_link_link_a', array('link' => $sLink)) . "\n";
        
            $bSent = Phpfox::getLib('mail')->to($aUser['user_id'])                      
                ->subject(array('pages.full_name_sent_you_a_page_invitation', array('full_name' => Phpfox::getUserBy('full_name'))))
                ->message($sMessage)                    
                ->send();
                    
            if ($bSent)
            {                   
                $iInviteId = $this->database()->insert(Phpfox::getT('pages_invite'), array(
                        'page_id' => $iPageId,                              
                        'user_id' => Phpfox::getUserId(),
                        'invited_user_id' => $aUser['user_id'],
                        'time_stamp' => PHPFOX_TIME
                    )
                );
                
                (Phpfox::isModule('request') ? Phpfox::getService('request.process')->add('pages_invite', $iPageId, $aUser['user_id']) : null);
            }
        }

        return array(
            'error_code' => 0,
            'result' => 1,
            'message'=>Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase("mfox.members_invited")),
            'iPageId' => $iPageId,
        );        
    }

    public function getPermissions($aPage){
        $bCanShareBlogs = Phpfox::getService('pages')->hasPerm($aPage['page_id'], 'blog.share_blogs');
        $eventModule = Phpfox::getService('mfox.event')->isAdvancedModule() ? 'fevent' : 'event';
        $bCanShareEvents = Phpfox::getService('pages')->hasPerm($aPage['page_id'], $eventModule . '.share_events');
        $bCanShareMusic = Phpfox::getService('pages')->hasPerm($aPage['page_id'], 'music.share_music');
        $photoModule = Phpfox::getService('mfox.photo')->isAdvancedModule() ? 'advancedphoto' : 'photo';
        $bCanSharePhotos = Phpfox::getService('pages')->hasPerm($aPage['page_id'], $photoModule . '.share_photos');
        $bCanShareVideos = Phpfox::getService('pages')->hasPerm($aPage['page_id'], 'video.share_videos');
        $bCanShareVideochannel = Phpfox::getService('pages')->hasPerm($aPage['page_id'], 'videochannel.share_videos');

        $bCanInvite = false;
        if(Phpfox::getUserParam('pages.can_moderate_pages') || $aPage['user_id'] == Phpfox::getUserId()){
            $bCanInvite = true;
        }

        return array(
            $bCanShareBlogs, 
            $bCanShareEvents, 
            $bCanShareMusic, 
            $bCanSharePhotos, 
            $bCanShareVideos, 
            $bCanShareVideochannel, 
            $bCanInvite, 
        );        
    }

    public function isInvited($iPageId, $iUserId){
        $count = (int) $this->database()->select('COUNT(*)')
                ->from(Phpfox::getT('pages_invite'))
                ->where('page_id = ' . (int) $iPageId . ' AND invited_user_id = ' . $iUserId)
                ->execute('getSlaveField');

        if($count > 0){
            return true;
        }

        return false;
    }    

    public function getAdminsByPageId($iPageId, $aPage = array()){
        $aRow = $this->database()->select('u.user_image, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('pages_admin'), 'pa')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = pa.user_id')
            ->where('pa.page_id = ' . (int) $iPageId)
            ->execute('getSlaveRows');

        if(isset($aPage['page_id']) == false){
            $aPage = Phpfox::getService('pages')->getForView($iPageId);
        }
        $aOwnerAdmin = array();
        foreach ($aPage as $sKey => $mValue)
        {
            if (substr($sKey, 0, 6) == 'owner_')
            {
                $aOwnerAdmin[0][str_replace('owner_', '', $sKey)] = $mValue;
            }
        }
        $aRow = array_merge($aOwnerAdmin, $aRow); 

        foreach ($aRow as $key => $value) {
            $aRow[$key]['user_image'] = Phpfox::getService('mfox.user')->getImageUrl($value, '_50_square');
        }
        return $aRow;
    }

    public function edit_cover($aData, $aPage = null){
        $sAction = isset($aData['sAction']) ? $aData['sAction'] : 'add';
        $isEdit = true;
        if('add' == $sAction){
            $isEdit = false;
        } else {
            if(null == $aPage){
                $iPageId = isset($aData['iPageId']) ? (int) $aData['iPageId'] : 0;
                if ($iPageId < 1)
                {
                    return array('result' => 0, 'error_code' => 1, 'error_message' => 'Page id is not valid!');
                }

                $aPage = Phpfox::getService('pages')->getForView((int)$iPageId);
            }

            if (isset($aPage['page_id']) == false)
            {
                return array('result' => 0, 'error_code' => 1, 'error_message' => 'The pages you are looking for cannot be found.');
            }            
        }

        // check image exists
        $bHasImage = false;
        if (!isset($_FILES['image']))
        {           
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'No image to upload.');
        }
        // Make sure the user group is actually allowed to upload an image
        if (!Phpfox::getUserParam('photo.can_upload_photos'))
        {           
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'You cannot upload photo.');
        }

        if (($iFlood = Phpfox::getUserParam('photo.flood_control_photos')) !== 0)
        {
            $aFlood = array(
                'action' => 'last_post', // The SPAM action
                'params' => array(
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('photo'), // Database table we plan to check
                    'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);   
                )
            );
                            
                // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood))
            {               
                return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('photo.uploading_photos_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
            }
        }       

        // inti some parameters 
        $oFile = Phpfox::getLib('file');
        $oImage = Phpfox::getLib('image');
        $aVals = array(
            'action' => 'upload_photo_via_share', 
            'is_cover_photo' => '1', 
            'page_id' => $isEdit ? $aPage['page_id'] : 0, 
        );

        $bIsInline = false;
        if (isset($aVals['action']) && $aVals['action'] == 'upload_photo_via_share')
        {
            $bIsInline = true;
        }       

        $oServicePhotoProcess = Phpfox::getService('photo.process');
        $aImages = array(); 
        $aFeed = array();
        $iFileSizes = 0;
        $iCnt = 0;

        // process image 
        if ($_FILES['image']['error']  == UPLOAD_ERR_OK){
            if ($aImage = $oFile->load('image', array(
                        'jpg',
                        'gif',
                        'png'
                    ), (Phpfox::getUserParam('photo.photo_max_upload_size') === 0 ? null : (Phpfox::getUserParam('photo.photo_max_upload_size') / 1024))
                )
            ){
                if (isset($aVals['action']) && $aVals['action'] == 'upload_photo_via_share')
                {
                    $aVals['description'] = null;
                    $aVals['type_id'] = (isset($aVals['is_cover_photo']) ? '2' : '1');
                }   

                if ($iId = $oServicePhotoProcess->add(Phpfox::getUserId(), array_merge($aVals, $aImage)))
                {
                    $iCnt++;
                    $aPhoto = Phpfox::getService('photo')->getForProcess($iId);                    

                    // Move the uploaded image and return the full path to that image.
                    $sFileName = $oFile->upload('image', 
                        Phpfox::getParam('photo.dir_photo'), 
                        (Phpfox::getParam('photo.rename_uploaded_photo_names') ? Phpfox::getUserBy('user_name') . '-' . preg_replace('/&#/i', 'u', $aPhoto['title']) : $iId),
                        (Phpfox::getParam('photo.rename_uploaded_photo_names') ? array() : true)                            
                    );

                    if (!$sFileName)
                    {
                        return array('result' => 0, 'error_code' => 1, 'error_message' => 'Failed: ' . implode('', Phpfox_Error::get()));
                    }
					
					$sFileFullPath =  Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '');
					$sThumbnailFilePath = Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '_1024');

                    Phpfox::getLib('image')->createThumbnail($sFileFullPath, $sThumbnailFilePath, 1024, 1024, true,false);


                    // Get the original image file size.
                    $iFileSizes += filesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));
                    // Get the current image width/height
                    $aSize = getimagesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));

                    // Update the image with the full path to where it is located.
                    $aUpdate = array(
                            'destination' => $sFileName,
                            'width' => $aSize[0],
                            'height' => $aSize[1],
                            'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID'),
                            'allow_rate' => (empty($aVals['album_id']) ? '1' : '0'),
                            'description' => (empty($aVals['description']) ? null : $aVals['description'])
                        );
                    $oServicePhotoProcess->update(Phpfox::getUserId(), $iId, $aUpdate);             

                    // Assign vars for the template.
                    $aImages[] = array(
                        'photo_id' => $iId,
                        // 'album' => (isset($aAlbum) ? $aAlbum : null),
                        'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID'),
                        'destination' => $sFileName,
                        'name' => $aImage['name'],
                        'ext' => $aImage['ext'],
                        'size' => $aImage['size'],
                        'width' => $aSize[0],
                        'height' => $aSize[1],
                        'completed' => 'false'
                    );
                }
            }
        }

        $iFeedId = 0;
        
        // Make sure we were able to upload some images
        if (count($aImages)){
            if (defined('PHPFOX_IS_HOSTED_SCRIPT'))
            {
                unlink(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));
            }

            $sAction = (isset($aVals['action']) ? $aVals['action'] : 'view_photo');
            // Update the user space usage
            Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'photo', $iFileSizes);            

            if (isset($aVals['page_id']) && $aVals['page_id'] > 0){
                if (Phpfox::getService('pages.process')->setCoverPhoto($aVals['page_id'], $iId, true))
                 {
                    // upload successfully
                    $aVals['is_cover_photo'] = 1;
                 }
                 else
                 {
                    return array('result' => 0, 'error_code' => 1, 'error_message' => 'Something went wrong: ' . implode(Phpfox_Error::get()));
                 }                               
            }

            $sCoverImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID'),
                    'path' => 'photo.url_photo',
                    'file' => $sFileName,
                    'suffix' => '',
                    'return_url' => true
                        )
                );
				
            return array(
                'error_code' => 0,
                'error_message' => '', 
                'result' => 1, 
                'sCoverImage' => $sCoverImage,
                'sFileName' => $sFileName,
                'iPhotoId' => $aImages[0]['photo_id'],
                'iCoveServerId' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID'),
            );            
        } else {
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'Failed: ' . implode('', Phpfox_Error::get()));
        }
    }

    public function update_avatar($aData, $aPage = null){
        $sAction = isset($aData['sAction']) ? $aData['sAction'] : 'add';
        $isEdit = true;
        if('add' == $sAction){
            $isEdit = false;
        } else {
            if(null == $aPage){
                $iPageId = isset($aData['iPageId']) ? (int) $aData['iPageId'] : 0;
                if ($iPageId < 1)
                {
                    return array('result' => 0, 'error_code' => 1, 'error_message' => 'Page id is not valid!');
                }

                $aPage = Phpfox::getService('pages')->getForView((int)$iPageId);
            }

            if (isset($aPage['page_id']) == false)
            {
                return array('result' => 0, 'error_code' => 1, 'error_message' => 'The pages you are looking for cannot be found.');
            }            
        }

        // check image exists
        $bHasImage = false;
        if (isset($_FILES['image']['name']) && ($_FILES['image']['name'] != ''))
        {
            $aImage = Phpfox::getLib('file')->load('image', array(
                    'jpg',
                    'gif',
                    'png'
                ), (Phpfox::getUserParam('pages.max_upload_size_pages') === 0 ? null : (Phpfox::getUserParam('pages.max_upload_size_pages') / 1024))
            );
            
            if ($aImage === false)
            {
                $sText = 'Failed ' . strip_tags($_FILES['image']['name']) . ' - ' . implode(' ', Phpfox_Error::get());
                return array('result' => 0, 'error_code' => 1, 'error_message' => $sText);
            } else {
                $bHasImage = true;
            }            
        } else {
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'No image to upload.');
        }

        // process image 
        if($bHasImage){
            $iId = $isEdit ? $aPage['page_id'] : time();
            if($isEdit){
                $aUser = $this->database()->select('user_id')
                    ->from(Phpfox::getT('user'))
                    ->where('profile_page_id = ' . (int) $iId)
                    ->execute('getSlaveRow');                

                if (!empty($aPage['image_path']))
                {
                    Phpfox::getService('pages.process')->deleteImage($aPage);
                }
            }

            $aUpdate = array();
            $oImage = Phpfox::getLib('image');

            $sFileName = Phpfox::getLib('file')->upload('image', Phpfox::getParam('pages.dir_image'), $iId);
            $iFileSizes = filesize(Phpfox::getParam('pages.dir_image') . sprintf($sFileName, ''));          
            
            $aUpdate['image_path'] = $sFileName . '?v=' . PHPFOX_TIME;
            $aUpdate['image_server_id'] = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
            
            $iSize = 50;            
            $oImage->createThumbnail(Phpfox::getParam('pages.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('pages.dir_image') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);           
            $iFileSizes += filesize(Phpfox::getParam('pages.dir_image') . sprintf($sFileName, '_' . $iSize));           
            
            $iSize = 120;           
            $oImage->createThumbnail(Phpfox::getParam('pages.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('pages.dir_image') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);           
            $iFileSizes += filesize(Phpfox::getParam('pages.dir_image') . sprintf($sFileName, '_' . $iSize));

            $iSize = 200;           
            $oImage->createThumbnail(Phpfox::getParam('pages.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('pages.dir_image') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);           
            $iFileSizes += filesize(Phpfox::getParam('pages.dir_image') . sprintf($sFileName, '_' . $iSize));
            //200 square
            $iSize = 200;
            $oImage->createThumbnail(Phpfox::getParam('pages.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('pages.dir_image') . sprintf($sFileName, '_' . '200_square'), $iSize, $iSize, false);
            $iFileSizes += filesize(Phpfox::getParam('pages.dir_image') . sprintf($sFileName, '_' . $iSize));
            //Crop max width
            if (Phpfox::isModule('photo')){
                Photo_Service_Photo::instance()->cropMaxWidth(Phpfox::getParam('pages.dir_image') . sprintf($sFileName, ''));
            }
            
            define('PHPFOX_PAGES_IS_IN_UPDATE', true);
            
            if($isEdit){
                Phpfox::getService('user.process')->uploadImage($aUser['user_id'], true, Phpfox::getParam('pages.dir_image') . sprintf($sFileName, ''));
            }

            // Update user space usage
            Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'pages', $iFileSizes);

            if($isEdit){
                $this->database()->update(Phpfox::getT('pages'), $aUpdate, 'page_id = ' . (int) $iId);
            }

            $sAvatarImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aUpdate['image_server_id'],
                    'path' => 'core.url_user',
                    'file' => $aUpdate['image_path'],
                    'suffix' => MAX_SIZE_OF_USER_IMAGE,
                    'return_url' => true
                        )
                );

            return array(
                'error_code' => 0,
                'error_message' => '', 
                'result' => 1, 
                'iAvatarServerId' => $aUpdate['image_server_id'],
                'sAvatarImage' => $sAvatarImage,
                'sFileName' => $sFileName,
            );
        }
    }

    public function create($aData){
        if (!Phpfox::getUserParam('pages.can_add_new_pages'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'You do not have permission to create new page!');
        }

        Phpfox::getService('pages')->setIsInPage();
        $sTitle = isset($aData['sTitle']) ? $aData['sTitle'] : '';
        $sInfo = isset($aData['sInfo']) ? $aData['sInfo'] : '';
        $iTypeId = isset($aData['iTypeId']) ? (int)$aData['iTypeId'] : 0;
        $iCategoryId = isset($aData['iCategoryId']) ? (int)$aData['iCategoryId'] : 0;
        $sAvatarFileName = isset($aData['sAvatarFileName']) ? (int)$aData['sAvatarFileName'] : '';
        $iAvatarServerId = isset($aData['iAvatarServerId']) ? (int)$aData['iAvatarServerId'] : Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
        $sCoverFileName = isset($aData['sCoverFileName']) ? (int)$aData['sCoverFileName'] : '';
        $iCoveServerId = isset($aData['iCoveServerId']) ? (int)$aData['iCoveServerId'] : Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
        $iCovePhotoId = isset($aData['iCovePhotoId']) ? (int)$aData['iCovePhotoId'] : 0;

        if(strlen(trim($sTitle)) == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'Fill in a title for your page');
        }
        if($iTypeId == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'Please chose category');
        }

        //  processing 
        $aInsert = array(
                'title' => $sTitle,
                'info' => $sInfo,
                'type_id' => $iTypeId,
                'category_id' => $iCategoryId, 
            );

        $iId = Phpfox::getService('pages.process')->add($aInsert);
        if (!$iId)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => implode('', Phpfox_Error::get()));
        }

        // process avatar image 
        if(strlen(trim($sAvatarFileName)) > 0){
            $aUser = $this->database()->select('user_id')
                ->from(Phpfox::getT('user'))
                ->where('profile_page_id = ' . (int) $iId)
                ->execute('getSlaveRow');                

            // if (!empty($aPage['image_path']))
            // {
            //     Phpfox::getService('pages.process')->deleteImage($aPage);
            // }
            
            Phpfox::getService('user.process')->uploadImage($aUser['user_id']
                , true
                , Phpfox::getParam('pages.dir_image') . sprintf($sAvatarFileName, '')
            );

            $aUpdate = array();
            $aUpdate['image_path'] = $sFileName;
            $aUpdate['image_server_id'] = $iAvatarServerId;
            $this->database()->update(Phpfox::getT('pages'), $aUpdate, 'page_id = ' . (int) $iId);
        }

        // process cover image 
        if($iCovePhotoId > 0){
            Phpfox::getService('pages.process')->setCoverPhoto((int) $iId, $iCovePhotoId, true);
        }

        // end 
        $sMessage = 'Your page has been added';
        return array(
            'result' => 1,
            'error_code' => 0,
            'message' => $sMessage,
            'iPageId' => $iId,
        );
    }

    public function edit($aData){
        if (!Phpfox::getUserParam('pages.can_add_new_pages'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'You do not have permission to create new page!');
        }

        // get data
        Phpfox::getService('pages')->setIsInPage();
        $iPageId = isset($aData['iPageId']) ? $aData['iPageId'] : 0;
        $sTitle = isset($aData['sTitle']) ? $aData['sTitle'] : '';
        $sInfo = isset($aData['sInfo']) ? $aData['sInfo'] : '';
        $iTypeId = isset($aData['iTypeId']) ? (int)$aData['iTypeId'] : 0;
        $iCategoryId = isset($aData['iCategoryId']) ? (int)$aData['iCategoryId'] : 0;
        $sAvatarFileName = isset($aData['sAvatarFileName']) ? (int)$aData['sAvatarFileName'] : '';
        $iAvatarServerId = isset($aData['iAvatarServerId']) ? (int)$aData['iAvatarServerId'] : Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
        $sCoverFileName = isset($aData['sCoverFileName']) ? (int)$aData['sCoverFileName'] : '';
        $iCoveServerId = isset($aData['iCoveServerId']) ? (int)$aData['iCoveServerId'] : Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
        $iCovePhotoId = isset($aData['iCovePhotoId']) ? (int)$aData['iCovePhotoId'] : 0;

        // validate and chec permissions 
        $aPage = Phpfox::getService('pages')->getForEdit($iPageId);
        $aError = Phpfox_Error::get();
        if(is_array($aError) && count($aError) > 0){
            return array('result' => 0, 'error_code' => 1, 'error_message' => implode('', $aError));
        }
        if(isset($aPage['page_id']) == false){
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('pages.unable_to_find_the_page_you_are_trying_to_edit'));
        }
        if(strlen(trim($sTitle)) == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'Fill in a title for your page');
        }
        if($iTypeId == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'Please chose category');
        }

        //  processing 
        $aUpdate = array(
                'title' => $this->preParse()->clean($sTitle),
                'type_id' => $iTypeId,
                'category_id' => $iCategoryId, 
            );
        $this->database()->update(Phpfox::getT('pages'), $aUpdate, 'page_id = ' . (int) $iPageId);
        $this->database()->update(Phpfox::getT('pages_text'), array(
            'text' => $this->preParse()->clean($sInfo), 
            'text_parsed' => $this->preParse()->prepare($sInfo)
        ), 'page_id = ' . (int) $iPageId);      

        // process avatar image 
        if(strlen(trim($sAvatarFileName)) > 0){
            $aUser = $this->database()->select('user_id')
                ->from(Phpfox::getT('user'))
                ->where('profile_page_id = ' . (int) $iPageId)
                ->execute('getSlaveRow');                

            if (!empty($aPage['image_path']))
            {
                Phpfox::getService('pages.process')->deleteImage($aPage);
            }
            
            Phpfox::getService('user.process')->uploadImage($aUser['user_id']
                , true
                , Phpfox::getParam('pages.dir_image') . sprintf($sAvatarFileName, '')
            );

            $aUpdate = array();
            $aUpdate['image_path'] = $sFileName;
            $aUpdate['image_server_id'] = $iAvatarServerId;
            $this->database()->update(Phpfox::getT('pages'), $aUpdate, 'page_id = ' . (int) $iPageId);
        }

        // process cover image 
        if($iCovePhotoId > 0){
            Phpfox::getService('pages.process')->setCoverPhoto((int) $iPageId, $iCovePhotoId, true);
        }

        // end 
        $sMessage = 'Your page has been updated';
        return array(
            'result' => 1,
            'error_code' => 0,
            'message' => $sMessage,
            'iPageId' => $iPageId,
        );
    }    

    public function categories($aData){
        $aCategories = Phpfox::getService('pages.category')->getCategories();
        
        foreach ($aCategories as $iKey => $aCategory)
        {
            $aCategories[$iKey]['name'] = (Core\Lib::phrase()->isPhrase($aCategory['name'])) ? Phpfox::getService('mfox')->decodeUtf8Compat(_p($aCategory['name'])) : Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getLib('locale')->convert($aCategory['name']));

            unset($aCategories[$iKey]['time_stamp']);
            unset($aCategories[$iKey]['ordering']);
            unset($aCategories[$iKey]['is_active']);
            
            if (isset($aCategory['sub_categories']) && is_array($aCategory['sub_categories']))
            {
                foreach ($aCategory['sub_categories'] as $iSubKey => $aSub)
                {
                    $aCategories[$iKey]['sub_categories'][$iSubKey]['name'] = (Core\Lib::phrase()->isPhrase($aSub['name'])) ? Phpfox::getService('mfox')->decodeUtf8Compat(_p($aSub['name'])) : Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getLib('locale')->convert($aSub['name']));

                    unset($aCategories[$iKey]['sub_categories'][$iSubKey]['type_id']);
                    unset($aCategories[$iKey]['sub_categories'][$iSubKey]['page_type']);
                    unset($aCategories[$iKey]['sub_categories'][$iSubKey]['is_active']);
                    unset($aCategories[$iKey]['sub_categories'][$iSubKey]['ordering']);
                }
            }
        }
        
        return $aCategories;        
    }

    public function addFeedComment($aData){
        // validate 
        if(isset($aData['iSubjectId']) == false || (int)$aData['iSubjectId'] <= 0){
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'Missing Subject Id');
        }
        $aVals = array(
            'user_status' => isset($aData['sContent']) ? $aData['sContent'] : '',
            'callback_item_id' => isset($aData['iSubjectId']) ? $aData['iSubjectId'] : '',
            'callback_module' => isset($aData['sCallbackModule']) ? $aData['sCallbackModule'] : 'pages',
            'group_id' => isset($aData['iGroupId']) ? $aData['iGroupId'] : $aData['iSubjectId'],
            'iframe' => isset($aData['iIframe']) ? $aData['iIframe'] : 1,
            'method' => isset($aData['sMethod']) ? $aData['sMethod'] : 'simple', 
            'parent_user_id' => isset($aData['iParentUserId']) ? $aData['iParentUserId'] : $aData['iSubjectId'], 
            'action' => isset($aData['sAction']) ? $aData['sAction'] : 'upload_photo_via_share', 
        );

        // process 
        $aPage = Phpfox::getService('pages')->getPage($aVals['callback_item_id']);
        if (!isset($aPage['page_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('pages.unable_to_find_the_page_you_are_trying_to_comment_on'));
        }
        $aError = $this->canPostComment($aPage['page_id']);
        if ($aError)
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' => 'Unable to comment this item due to privacy settings.'
            );
        }


        if (Phpfox::getLib('parse.format')->isEmpty($aVals['user_status']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('user.add_some_text_to_share')
            );
        }

        $sLink = Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
        $custom_pages_post_as_page = isset($aData['iCustomPagesPostAsPage']) ? $aData['iCustomPagesPostAsPage'] : 0;
        $aCallback = array(
            'module' => 'pages',
            'table_prefix' => 'pages_',
            'link' => $sLink,
            'email_user_id' => $aPage['user_id'],
            'subject' =>  Phpfox::getPhrase('pages.full_name_wrote_a_comment_on_your_page_title', array('full_name' => Phpfox::getUserBy('full_name'), 'title' => $aPage['title'])),
            'message' =>  Phpfox::getPhrase('pages.full_name_wrote_a_comment_link', array('full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aPage['title'])),
            'notification' => ($custom_pages_post_as_page ? null : 'pages_comment'),
            'feed_id' => 'pages_comment',
            'item_id' => $aPage['page_id']
        );

        $aVals['parent_user_id'] = $aVals['callback_item_id'];
        if (isset($aVals['user_status']) 
            && ($iId = Phpfox::getService('feed.process')->callback($aCallback)->addComment($aVals))
        ){
            Phpfox::getLib('database')->updateCounter('pages', 'total_comment', 'page_id', $aPage['page_id']);      
            
            // Phpfox::getService('feed')->callback($aCallback)->processAjax($iId);
            return array(
                'error_code' => 0,
                'iCommentId' => $iId,
                'message'=>Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase("mfox.this_item_has_successfully_been_submitted"))
            );
        }
        else 
        {
            // $this->call('$Core.activityFeedProcess(false);');
            return array(
                'error_code' => 1,
                'error_message' => 'Failed: ' . implode('', Phpfox_Error::get())
            );
        }       

        // end 
    }

    public function getPagesFeedByFeedID($iId){
        return $this->database()->select('*')
            ->from(Phpfox::getT('pages_feed'), 'e')     
            ->where('e.feed_id = ' . (int) $iId)
            ->execute('getRow');        
    }    

    public function getPagesFeedByID($iId){
        return $this->database()->select('*')
            ->from(Phpfox::getT('pages_feed'), 'e')     
            ->where('e.feed_id = ' . (int) $iId)
            ->execute('getRow');

    }

    public function getPagesIDFeedByItemID($iId){
        return $this->database()->select('e.parent_user_id')
            ->from(Phpfox::getT('pages_feed'), 'e')     
            ->where('e.item_id = ' . (int) $iId)
            ->group('e.item_id')
            ->execute('getslavefield');
    }    

    public function changeType($sType)    
    {
        switch ($sType) {
            case 'music_album':
                $sType = 'music_song';
                break;

            default:
                break;
        }

        return $sType;
    }

    public function getQueryJoins($sView = '', $bIsCount = false, $bNoQueryFriend = false){
        if (Phpfox::isModule('friend') && Phpfox::getService('mfox.friend')->queryJoin($sView, $bNoQueryFriend))
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = pages.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());  
        }        
    }

}

