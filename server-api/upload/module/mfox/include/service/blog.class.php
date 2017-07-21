<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Blog extends Phpfox_Service {

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

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_oReq = Mfox_Service_Request_Request::instance();
        $this->_oSearch = Mfox_Service_Search_Search::instance();
        $this->_oBrowse = Mfox_Service_Search_Browse::instance();
    }

    public function getActivityFeed($aRow, $aCallback = null, $bIsChildItem = false)
    {
        if (!Phpfox::getUserParam('blog.view_blogs'))
        {
            return false;
        }
        
        if (Phpfox::isUser())
        {
            $this->database()->select('l.like_id AS is_liked, ')
                    ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'blog\' AND l.item_id = b.blog_id AND l.user_id = ' . Phpfox::getUserId());
        }

        if (Phpfox::isModule('pages') && $aRow['page_id']){
            $this->database()->select('pages.*, pages.title AS pages_title,pages_url.vanity_url,' . Phpfox::getUserField('puser', 'parents_'). ',')
                ->leftJoin(':pages', 'pages', 'pages.page_id=b.item_id AND b.module_id="pages"')
                ->leftJoin(':pages_url', 'pages_url', 'pages_url.page_id=b.item_id AND b.module_id="pages"')
                ->leftJoin(':user', 'puser', 'puser.profile_page_id=b.item_id AND b.module_id="pages"');
        }
        
        if ($bIsChildItem)
        {
            $this->database()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user'), 'u2', 'u2.user_id = b.user_id');
        }               
        
        $aRow = $this->database()->select('b.blog_id, b.title, b.time_stamp, b.total_comment, b.total_like, bt.text_parsed AS text, b.module_id, b.item_id')
            ->from(Phpfox::getT('blog'), 'b')
            ->join(Phpfox::getT('blog_text'), 'bt', 'bt.blog_id = b.blog_id')
            ->where('b.blog_id = ' . (int) $aRow['item_id'])
            ->execute('getSlaveRow');
        $aRowData = $aRow;
        if (!isset($aRow['blog_id']))
        {
            return false;
        }       
        
        if (((defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'blog.view_browse_blogs'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aRow['item_id'], 'blog.view_browse_blogs')))
            )
        {
            return false;
        }
        
        (($sPlugin = Phpfox_Plugin::get('blog.component_service_callback_getactivityfeed__1')) ? eval($sPlugin) : false);

        $aRow['item_id'] = $aRow['blog_id'];
        $aRow['text'] = preg_replace('~\[img\](.+?)\[/img\]~', '', $aRow['text']);

        $aReturn =  array_merge(array(
            'feed_title' => $aRow['title'],
            'feed_info' => Phpfox::getPhrase('feed.posted_a_blog'),
            'feed_link' => Phpfox::permalink('blog', $aRow['blog_id'], $aRow['title']),
            'feed_content' => $aRow['text'],
            'total_comment' => $aRow['total_comment'],
            'feed_total_like' => $aRow['total_like'],
            'feed_is_liked' => isset($aRow['is_liked']) ? $aRow['is_liked'] : false,
            'feed_icon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/blog.png', 'return_url' => true)),
            'time_stamp' => $aRow['time_stamp'],            
            'enable_like' => true,          
            'comment_type_id' => 'blog',
            'like_type_id' => 'blog',
            'custom_data_cache' => $aRow
        ), $aRow);
        if (Phpfox::isModule('pages') && $aRowData['page_id']){
            $aReturn['parent_user_name'] = Pages_Service_Pages::instance()->getUrl($aRowData['page_id'], $aRowData['pages_title'], $aRowData['vanity_url']);
            if (!defined('PHPFOX_IS_PAGES_VIEW') && empty($_POST)) {
                $aReturn['parent_user'] = [
                    'parent_user_id' => $aRowData['parents_user_id'],
                    'parent_profile_page_id' => $aRowData['parents_profile_page_id'],
                    'parent_server_id' => $aRowData['parents_server_id'],
                    'parent_user_name' => $aRowData['parents_user_name'],
                    'parent_full_name' =>$aRowData['parents_full_name'],
                    'parent_gender' =>$aRowData['parents_gender'],
                    'parent_user_image' =>$aRowData['parents_user_image'],
                    'parent_is_invisible' =>$aRowData['parents_is_invisible'],
                    'parent_user_group_id' => $aRowData['parents_user_group_id'],
                    'parent_language_id' => $aRowData['parents_language_id'],
                    'parent_birthday' => $aRowData['parents_birthday'],
                    'parent_country_iso' => $aRowData['parents_country_iso'],
                ];
                unset($aReturn['feed_info']);
            }
        }
        return $aReturn;
    }

    public function formadd($aData)
    {
        $response  =  array(
            'view_options'=> Phpfox::getService('mfox.privacy')->privacy($aData),
            'comment_options'=> Phpfox::getService('mfox.privacy')->privacycomment($aData),
            'category_options'=> $this->getCategories($aData),
        );
        
        $iValue =  Phpfox::getService('user.privacy')->getValue('blog.default_privacy_setting');
        $response['default_privacy_setting'] = Phpfox::getService('mfox.privacy')->defaultprivacy("$iValue", $response['view_options']);
        
        return $response;
    }

    public function getCategories($aData, $bIsProfile = false, $filter = ''){
    	// get user if bIsProfile = true
		$aUser = array();    	

		$public = Phpfox::getService('blog.category')->getCategories('c.user_id = ' . ($bIsProfile ? $aUser['user_id'] : '0'));    	
		if (!is_array($public))
		{
			$public = array();
		}
		if (!$public)
		{
			$public = array();
		}
        foreach ($public as $key => $value) {
            // isPhrase will be depricated in 4.4.5 and removed in 4.4.6
            // may need to use /Lib/Core::isPhrase or create a facade for it
            if (phpfox::isPhrase($value['name'])) {
                $public[$key]['name'] = _p($value['name']);
            } else {
                $public[$key]['name'] = html_entity_decode(Phpfox::getLib('locale')->convert($value['name']));
            }
        }

        $personal = Phpfox::getService('blog.category')->getCategories('c.user_id = ' . (int)Phpfox::getUserId());     
        if (!is_array($personal))
        {
            $personal = array();
        }
        if (!$personal)
        {
            $personal = array();
        }
        foreach ($personal as $key => $value) {
            if (phpfox::isPhrase($value['name'])) {
                $personal[$key]['name'] = _p($value['name']);
            } else {
                $personal[$key]['name'] = html_entity_decode(Phpfox::getLib('locale')->convert($value['name']));
            }
        }

        if('all' == $filter){
            return array_merge($public, $personal);            
        } else if('public' == $filter){
            return $public;
        } else {
            return array('public' => $public, 'personal' => $personal);            
        }
    }

    public function formedit($aData){
        return array_merge( 
            $this->detail($aData), 
            $this->formadd($aData)
        );
    }        

    public function detail($aData){
    	$iBlogId = isset($aData['iBlogId']) ? (int) $aData['iBlogId'] : 0;

        if (!Phpfox::getUserParam('blog.view_blogs'))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_blog"))
            );
        }

		if (Phpfox::isUser() && Phpfox::isModule('notification'))
		{
			Phpfox::getService('notification.process')->delete('comment_blog', $iBlogId, Phpfox::getUserId());
			Phpfox::getService('notification.process')->delete('blog_like', $iBlogId, Phpfox::getUserId());
		}

		// default is false, need to update when supporting profile in future 
		$bIsProfile = false;		

		$aItem = Phpfox::getService('blog')->getBlog($iBlogId);

		if ( (!isset($aItem['blog_id'])) || 
			(isset($aItem['module_id']) && Phpfox::isModule($aItem['module_id']) != true))
		{			
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_blog_you_are_looking_for_cannot_be_found"))
            );
		}

		if (Phpfox::getUserId() == $aItem['user_id'] && Phpfox::isModule('notification'))
		{
			Phpfox::getService('notification.process')->delete('blog_approved', $iBlogId, Phpfox::getUserId());
		}			

        $bCanView = false;
        if(Phpfox::getService('privacy')->check('blog', $aItem['blog_id'], $aItem['user_id'], $aItem['privacy'], $aItem['is_friend'], $bReturn = true)){
            $bCanView = true;
        }

        if($bCanView == false){
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time')
            );
        }

		if (!Phpfox::getUserParam('blog.can_approve_blogs'))
		{
			if ($aItem['is_approved'] != '1' && $aItem['user_id'] != Phpfox::getUserId())
			{
	            return array(
	                'result' => 0,
	                'error_code' => 1,
	                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_blog_you_are_looking_for_cannot_be_found"))
	            );
			}
		}

		if ($aItem['post_status'] == 2 && Phpfox::getUserId() != $aItem['user_id'])
		{
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_blog_you_are_looking_for_cannot_be_found"))
            );
		}		

		if (Phpfox::isModule('track') && Phpfox::isUser() 
			&& Phpfox::getUserId() != $aItem['user_id'] && !$aItem['is_viewed'])
		{
			Phpfox::getService('track.process')->add('blog', $aItem['blog_id']);
			Phpfox::getService('blog.process')->updateView($aItem['blog_id']);
		}

		if (Phpfox::isUser() && Phpfox::isModule('track') 
			&& Phpfox::getUserId() != $aItem['user_id'] && $aItem['is_viewed'] 
			&& !Phpfox::getUserBy('is_invisible'))
		{
			Phpfox::getService('track.process')->update('blog_track', $aItem['blog_id']);	
		}		

		// we do not support for "blog_password" feature 
		// $sPassword = $this->request()->get('blog_password')

		$aCategories = Phpfox::getService('blog.category')->getCategoriesById($aItem['blog_id']);

		if (Phpfox::isModule('tag'))
		{
			$aTags = Phpfox::getService('tag')->getTagsById('blog', $aItem['blog_id']);	
			if (isset($aTags[$aItem['blog_id']]))
			{
				$aItem['tag_list'] = $aTags[$aItem['blog_id']];
			}
		}

		if (isset($aCategories[$aItem['blog_id']]))
		{
			$sCategories = '';
			foreach ($aCategories[$aItem['blog_id']] as $iKey => $aCategory)
			{
				$sCategories .= ($iKey != 0 ? ',' : '') 
					. ' <a href="' 
					. ($aCategory['user_id'] ? Phpfox::getLib('url')->permalink($aItem['user_name'] . '.blog.category', $aCategory['category_id'], $aCategory['category_name']) : Phpfox::getLib('url')->permalink('blog.category', $aCategory['category_id'], $aCategory['category_name'])) 
					. '">' . Phpfox::getLib('locale')->convert(Phpfox::getLib('parse.output')->clean($aCategory['category_name'])) . '</a>';
			}
		}

		if (isset($sCategories))
		{
			$aItem['info'] =  Phpfox::getPhrase('blog.posted_x_by_x_in_x', array('date' => Phpfox::getTime(Phpfox::getParam('blog.blog_time_stamp'), $aItem['time_stamp']), 'link' => Phpfox::getLib('url')->makeUrl('profile', array($aItem['user_name'])), 'user' => $aItem, 'categories' => $sCategories));
		}
		else 
		{
			$aItem['info'] =  Phpfox::getPhrase('blog.posted_x_by_x', array('date' => Phpfox::getTime(Phpfox::getParam('blog.blog_time_stamp'), $aItem['time_stamp']), 'link' => Phpfox::getLib('url')->makeUrl('profile', array($aItem['user_name'])), 'user' => $aItem));
		}		

		// support module pages
		// if (isset($aItem['module_id']) && Phpfox::hasCallback($aItem['module_id'], 'getVideoDetails'))
		// {
		//     if ($aCallback = Phpfox::callback($aItem['module_id'] . '.getVideoDetails', $aItem))
		// 	{
		// 	}
		// }
		// if (isset($aCallback) && isset($aCallback['item_id']))
		// {
		//     $sBreadcrumb = $this->url()->makeUrl('pages.' . $aCallback['item_id'] .'.blog');
		// }
		
		// if (isset($aCallback) && isset($aCallback['module_id']) && $aCallback['module_id'] == 'pages')
		// {
		// 	$this->setParam('sTagListParentModule', $aItem['module_id']);
		// 	$this->setParam('iTagListParentId', (int) $aItem['item_id']);
		// }

        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aItem, '_50_square');  

        $aLike['likes'] = Phpfox::getService('mfox.helper.like')->getLikesForFeed('blog', $aItem['blog_id']
            ,  $aItem['is_liked'], 1000, true);              
        // $aLike['feed_total_like'] = Phpfox::getService('mfox.helper.like')->getTotalLikeCount();
        $aUserLike = array();
        foreach($aLike['likes'] as $like){
            $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
        }
        $iTotalLike = count($aUserLike);     

        $aUserDislike = array();
        $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('blog', $aItem['blog_id'], $bGetCount = false);
        foreach($aDislike as $dislike){
            $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
        }   
        $iTotalDislike = count($aUserDislike);     

		if (Phpfox::isModule('tag'))
		{
			$aTags = Phpfox::getService('tag')->getTagsById('blog', $aItem['blog_id']);	
			if (isset($aTags[$aItem['blog_id']]))
			{
				$aItem['tag_list'] = $aTags[$aItem['blog_id']];
			}
		}        

		$bIsAttachmentNoHeader = false;		
		list($iCnt, $aAttachment) = Phpfox::getService('mfox.helper.attachment')->getAttachmentByType($aItem['blog_id'], 'blog', null, false, false);
		$attachmentResult = array();
		foreach ($aAttachment as $key => $att) {
			if((int)$att['link_id'] > 0){
				// link attachment
				$type = 'link';
				$link  = Phpfox::getService('mfox.helper.attachment')->getDetailAttachmentByType($type, (int)$att['link_id'], $att);
                if(isset($link['link_id'])){
                    $attachmentResult[] = array('attachment_id' => $att['attachment_id'], 'type' => $type, 'data' => $link);
                }
			} else if($att['is_image']){
				// image attachment
				$type = 'image';
				$url = Phpfox::getService('mfox.helper.attachment')->getDetailAttachmentByType($type, 0, $att);
				$attachmentResult[] = array('attachment_id' => $att['attachment_id'], 'type' => $type, 'data' => array('photo_url' => $url));
			}
		}

        // get category of blog 
        $aSelectedCategories = $this->getCategoriesById($aItem['blog_id']);                                    

        $aBlogText = $this->getTextById($aItem['blog_id']);
		
		$bCanPostComment =  Phpfox::getService('mfox.comment')->checkCanPostCommentOnItem($aItem) && Phpfox::getUserParam('blog.can_post_comment_on_blog');

        // end 
        return array(
            'bIsLiked' => $aItem['is_liked'],
            'bCanDislike' => Phpfox::getService('mfox.like')->isTypeSupportDislike('blog'),
            'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('blog', $aItem['blog_id'], Phpfox::getUserId()),
            'aUserLike' => $aUserLike,
            'aUserDislike' => $aUserDislike,
            'bIsFriend' => $aItem['is_friend'],
            'iBlogId' => $aItem['blog_id'],
            'iProfilePageId' => (int)$aItem['item_id'],
            'iPrivacy' => $aItem['privacy'],
            'iPrivacyComment' => $aItem['privacy_comment'],
            'iUserId' => $aItem['user_id'],
            'sTitle' => $aItem['title'],
            'sText' => Phpfox::getLib('phpfox.parse.output')->parse($aItem['text']),
            'sTextNotParsed' => $aBlogText['text'],
            'iTotalComment' => $aItem['total_comment'],
            'iTotalLike' => $iTotalLike,
            'iTotalDislike' => $iTotalDislike,
            'iTimeStamp' => $aItem['time_stamp'],
            'sTimeStamp' => date('D, j M Y G:i:s O', $aItem['time_stamp']),
            'sFullTimeStamp' => Phpfox::getLib('date')->convertTime($aItem['time_stamp'], null),
            'sModuleId' => $aItem['module_id'],
            'iItemId' => $aItem['item_id'],
            'iProfilePageId' => $aItem['profile_page_id'],
            'sUserName' => $aItem['user_name'],
            'sFullname' => $aItem['full_name'],
            'iGender' => $aItem['gender'],
            'sUserImage' => $sUserImage,
            'sImagePath' => $sUserImage,
            'bIsInvisible' => $aItem['is_invisible'],
            'iUserGroupId' => $aItem['user_group_id'],
            'iLanguageId' => $aItem['language_id'],
            'bCanComment' => $bCanPostComment ?1 :0,
            'iUserLevelId' => $aItem['user_group_id'],
            'bCanView' => $bCanView, 
            'aTagList' => $aItem['tag_list'],
            'bIsApproved' => ($aItem['is_approved'] == 1) ? true : false,
            'bIsPublish' => ($aItem['post_status'] ==1) ? true : false,
            'bUserSettingApproveBlogs' => Phpfox::getUserParam('blog.approve_blogs'),
            'aAttachment' => $attachmentResult,
            'aSelectedCategories' => $aSelectedCategories,
            'bCanEdit' => $this->canEdit($aItem),
            'bCanDelete' => $this->canDelete($aItem),            
        );		
    }

    public function getTextById($iBlogId){
        return $this->database()->select("blog_text.*")
            ->from(Phpfox::getT('blog_text'), 'blog_text')
            ->where('blog_text.blog_id = ' . (int) $iBlogId)
            ->execute('getSlaveRow');               
    }

    public function getCategoriesById($iBlogId){
        $aCategories = Phpfox::getService('blog.category')->getCategoriesById($iBlogId);
        $aSelectedCategories = array();
        if (isset($aCategories[$iBlogId]))
        {
            foreach ($aCategories[$iBlogId] as $aCategory)
            {
                $aSelectedCategories[] = array(
                    'category_id' => $aCategory['category_id'], 
                    'category_name' => html_entity_decode(Phpfox::getLib('locale')->convert($aCategory['category_name'])), 
                    'user_id' => $aCategory['user_id'], 
                    'type' => ((int)$aCategory['user_id'] > 0 ? 'personal' : 'public'), 
                );
            }
        } 

        return $aSelectedCategories; 
    }

    public function fetch($aData){
        return $this->getBlogs($aData);
    }

    public function getBlogs($aData)
    {
        $this->_oReq->set(array(
            'view' => !empty($aData['sView']) ? $aData['sView'] : '',
            'page' => !empty($aData['iPage']) ? (int) $aData['iPage'] : 1,
            'show' => !empty($aData['iAmountOfBlog']) ? (int) $aData['iAmountOfBlog'] : 10,
            'category' => !empty($aData['iCategoryId']) ? (int) $aData['iCategoryId'] : null,
            'search' => !empty($aData['sSearch']) ? $aData['sSearch'] : '',
            'sort' => !empty($aData['sOrder']) ? $aData['sOrder'] : '',
            'module_id' => !empty($aData['sModule']) ? $aData['sModule'] : null,
            'item_id' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : null,
            'profile' => (!empty($aData['bIsProfile']) && $aData['bIsProfile'] == 'true') ? true : false,
            'profile_id' => !empty($aData['iProfileId']) ? (int) $aData['iProfileId'] : null,
        ));
        
        $sView = $this->_oReq->get('view');
        $sModuleId = $this->_oReq->get('module_id');
        $iItemId = $this->_oReq->get('item_id');

        $aParentModule = null;
        if (!empty($sModuleId) && !empty($iItemId))
        {
            $aParentModule = array(
                'module_id' => $sModuleId,
                'item_id' => $iItemId
            );
        }

        Phpfox::getUserParam('blog.view_blogs', true);

        $bIsProfile = false;
        if ($this->_oReq->get('profile') === true)
        {
            $bIsProfile = true;
            $aUser = Phpfox::getService('user')->get($this->_oReq->get('profile_id'));
            $this->_oSearch->setCondition('AND blog.user_id = ' . $aUser['user_id']);
        }

        $aBrowseParams = array(
            'module_id' => 'blog',
            'alias' => 'blog',
            'field' => 'blog_id',
            'table' => Phpfox::getT('blog'),
            'hide_view' => array('pending', 'my'),
            'service' => 'mfox.blog'
        );      
        
        switch ($sView)
        {
            case 'spam':
                Phpfox::isUser(true);
                if (Phpfox::getUserParam('blog.can_approve_blogs'))
                {
                    $this->_oSearch->setCondition('AND blog.is_approved = 9');
                }
                break;
            case 'pending':
                Phpfox::isUser(true);
                if (Phpfox::getUserParam('blog.can_approve_blogs'))
                {
                    $this->_oSearch->setCondition('AND blog.is_approved = 0');
                }               
                break;
            case 'my':
                Phpfox::isUser(true);
                $this->_oSearch->setCondition('AND blog.user_id = ' . Phpfox::getUserId());             
                break;
            case 'draft':
                Phpfox::isUser(true);
                $this->_oSearch->setCondition("AND blog.user_id = " . $aUser['user_id'] . " AND blog.is_approved IN(" . ($aUser['user_id'] == Phpfox::getUserId() ? '0,1' : '1') . ") AND blog.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ") AND blog.post_status = 2");
                break;
            default:
                $this->_oSearch->setCondition("AND blog.is_approved = 1 AND blog.post_status = 1" . (Phpfox::getUserParam('privacy.can_comment_on_all_items') ? "" : " AND blog.privacy IN(%PRIVACY%)"));
                break;
        }

        $iCategoryId = $this->_oReq->get('category', null);
        if (!empty($iCategoryId))
        {           
            if ($aBlogCategory = Phpfox::getService('blog.category')->getCategory($iCategoryId))
            {
                $this->_oSearch->setCondition('AND blog_category.category_id = ' . $iCategoryId . ' AND blog_category.user_id = ' . ($bIsProfile ? (int) $aUser['user_id'] : 0));
                $bIsValidCategory = true;
            }           
        }
        
        if (isset($aParentModule) && isset($aParentModule['module_id']))
        {
            /* Only get items without a parent (not belonging to pages) */
            $this->_oSearch->setCondition('AND blog.module_id = \''. $aParentModule['module_id'] .'\' AND blog.item_id = ' . (int) $aParentModule['item_id']);         
        }
        else if ($aParentModule === null)
        {
            if (($sView == 'pending' || $sView == 'draft') && Phpfox::getUserParam('blog.can_approve_blogs'))
            {
                
            }
            else
            {
                $this->_oSearch->setCondition('AND blog.module_id = \'blog\'');
            }
        }

        // search
        $sSearch = $this->_oReq->get('search');
        if (!empty($sSearch))
        {
            $this->_oSearch->setCondition('AND blog.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"');
        }

        // sort
        $sSort = '';
        switch ($this->_oReq->get('sort')) {
            case 'most_viewed':
                $sSort = 'blog.total_view DESC';
                break;
            case 'most_liked':
                $sSort = 'blog.total_like DESC';
                break;
            case 'most_discussed':
                $sSort = 'blog.total_comment DESC';
                break;
            default:
                $sSort = 'blog.time_stamp DESC';
                break;
        }

        $this->_oSearch->setSort($sSort);
        
        // http://www.phpfox.com/tracker/view/15375/
        if ($aParentModule['module_id'] == 'pages' && Phpfox::getService('pages')->hasPerm($aParentModule['item_id'], 'blog.view_browse_blogs'))
        {
            if(Phpfox::getService('pages')->isAdmin($aParentModule['item_id']))
            {
                $this->_oReq->set('view', 'pages_admin');
            }
            elseif(Phpfox::getService('pages')->isMember($aParentModule['item_id']))
            {
                $this->_oReq->set('view', 'pages_member');
            }
        }

        $this->_oBrowse->params($aBrowseParams)->execute();
        
        $aItems = $this->_oBrowse->getRows();

        return $aItems;
    }

    public function processRows(&$aRows)
    {
        $aTmpRows = $aRows;
        $aRows = array();

        foreach ($aTmpRows as $aItem)
        {
            $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aItem, '_50_square');

            $aLike['likes'] = Phpfox::getService('mfox.helper.like')->getLikesForFeed('blog', $aItem['blog_id']
                ,  $aItem['is_liked'], 1000, true);              
            // $aLike['feed_total_like'] = Phpfox::getService('like')->getTotalLikeCount();
            $aUserLike = array();
            foreach($aLike['likes'] as $like){
                $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
            }
            $iTotalLike = count($aUserLike);     

            $aUserDislike = array();
            $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('blog', $aItem['blog_id'], $bGetCount = false);
            foreach($aDislike as $dislike){
                $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
            }                    
            $iTotalDislike = count($aUserDislike);  

            if (Phpfox::isModule('tag'))
            {
                $aTags = Phpfox::getService('tag')->getTagsById('blog', $aItem['blog_id']); 
                if (isset($aTags[$aItem['blog_id']]))
                {
                    $aItem['tag_list'] = $aTags[$aItem['blog_id']];
                }
            }     
            
            $bCanPostComment = $this->canPostComment($aItem['blog_id']);   

            $aRows[] = array(
                'bIsLiked' => $aItem['is_liked'],
                'bCanDislike' => Phpfox::getService('mfox.like')->isTypeSupportDislike('blog'),
                'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('blog', $aItem['blog_id'], Phpfox::getUserId()),
                'aUserLike' => $aUserLike,
                'aUserDislike' => $aUserDislike,
                'bIsFriend' => $aItem['is_friend'],
                'iBlogId' => $aItem['blog_id'],
                'iProfilePageId' => (int)$aItem['profile_page_id'],
                'iPrivacy' => $aItem['privacy'],
                'iPrivacyComment' => $aItem['privacy_comment'],
                'iUserId' => $aItem['user_id'],
                'sTitle' => $aItem['title'],
                'sText' => $aItem['text'],
                'sTextNotParsed' => $aItem['text_not_parsed'],
                'iTotalComment' => $aItem['total_comment'],
                'iTotalLike' => $iTotalLike,
                'iTotalDislike' => $iTotalDislike,
                'iTimeStamp' => $aItem['time_stamp'],
                'sTimeStamp' => date('D, j M Y G:i:s O', $aItem['time_stamp']),
                'sFullTimeStamp' => Phpfox::getLib('date')->convertTime($aItem['time_stamp'], null),
                'sModuleId' => $aItem['module_id'],
                'iItemId' => $aItem['item_id'],
                'iProfilePageId' => $aItem['profile_page_id'],
                'sUserName' => $aItem['user_name'],
                'sFullname' => $aItem['full_name'],
                'iGender' => $aItem['gender'],
                'sUserImage' => $sUserImage,
                'sImagePath' => $sUserImage,
                'bIsInvisible' => $aItem['is_invisible'],
                'iUserGroupId' => $aItem['user_group_id'],
                'iLanguageId' => $aItem['language_id'],
                'bCanComment' => $bCanPostComment?1:0,
                'iUserLevelId' => $aItem['user_group_id'],
                'bCanView' => $bCanView, 
                'aTagList' => $aItem['tag_list'],
                'bIsApproved' => ($aItem['is_approved'] == 1) ? true : false,
                'bIsPublish' => ($aItem['post_status'] ==1) ? true : false,
                'bUserSettingApproveBlogs' => Phpfox::getUserParam('blog.approve_blogs'),
                'bCanEdit' => $this->canEdit($aItem),
                'bCanDelete' => $this->canDelete($aItem),
            );   
        }
    }

    public function query()
    {
        return Phpfox::getService('blog.browse')->query();
    }

    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Mfox_Service_Friend::instance()->queryJoin($bNoQueryFriend))
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = blog.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());   
        }
        
        if (Phpfox::getParam('core.section_privacy_item_browsing'))
        {
            if ($this->_oSearch->isSearch())
            {
                $this->database()->join(Phpfox::getT('blog_text'), 'blog_text', 'blog_text.blog_id = blog.blog_id');
            }           
        }
        else
        {
            if ($bIsCount && $this->_oSearch->isSearch())
            {
                $this->database()->join(Phpfox::getT('blog_text'), 'blog_text', 'blog_text.blog_id = blog.blog_id');
            }
        }
        
        if ($this->_oReq->get('tag'))
        {
            $this->database()->innerJoin(Phpfox::getT('tag'), 'tag', 'tag.item_id = blog.blog_id AND tag.category_id = \'blog\'');  
        }
        
        if ($this->_oReq->get('category'))
        {       
            $this->database()
                ->innerJoin(Phpfox::getT('blog_category_data'), 'blog_category_data', 'blog_category_data.blog_id = blog.blog_id')
                ->innerJoin(Phpfox::getT('blog_category'), 'blog_category', 'blog_category.category_id = blog_category_data.category_id');          
        }       
    }

    public function canEdit($aItem){
    	
		
				
        if((Phpfox::getUserParam('blog.edit_own_blog') && Phpfox::getUserId() == $aItem['user_id']) 
            || Phpfox::getUserParam('blog.edit_user_blog')){
            return true;
        }

        return false;
    }

    public function canDelete($aItem){
        if ((Phpfox::getUserParam('blog.delete_own_blog') && Phpfox::getUserId() == $aItem['user_id']) 
            || Phpfox::getUserParam('blog.delete_user_blog')){
            return true;
        }

        return false;
    }

    public function delete($aData){
    	$iBlogId = isset($aData['iBlogId']) ? (int) $aData['iBlogId'] : 0;
        if ($iBlogId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.blog_id_is_not_valid")));
        }

        $aRow = Phpfox::getService('blog')->getBlogForEdit($iBlogId);
        if (!isset($aRow['blog_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.blog_has_been_deleted_or_you_do_not_have_permission_to_delete_it")));
        }

        // Check privacy
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('blog', $aRow['blog_id'], $aRow['user_id'], $aRow['privacy'], $aRow['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        $mResult = Phpfox::getService('blog.process')->delete($iBlogId);
        if ($mResult !== false)
        {
            return array('result' => 1
                , 'error_code' => 0
                , 'message' =>  Phpfox::getPhrase('blog.blog_successfully_deleted')
            );
        }

        return array('result' => 0
            , 'error_code' => 1
            , 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.blog_has_been_deleted_or_you_do_not_have_permission_to_delete_it"))
        );

    }

    public function create($aData){
        if (!Phpfox::getUserParam('blog.add_new_blog'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_create_new_blog")));
        }

		$sTitle = isset($aData['sTitle']) ? $aData['sTitle'] : '';
		$sText = isset($aData['sText']) ? $aData['sText'] : '';
		$iPrivacy = isset($aData['iPrivacy']) ? $aData['iPrivacy'] : 0;
		$iPrivacyComment = isset($aData['iPrivacyComment']) ? $aData['iPrivacyComment'] : 0;
		// example: '9,6,7,'
		$sCategories = isset($aData['sCategories']) ? $aData['sCategories'] : '';
		// example: '9,6,7,'
		$sAttachment = isset($aData['sAttachment']) ? $aData['sAttachment'] : '';
		// example: 'abc, def,'
		$sTopic = isset($aData['sTopic']) ? $aData['sTopic'] : '';
		// publish/draft
		$sStatus = isset($aData['sStatus']) ? $aData['sStatus'] : 'publish';
        $sModule = isset($aData['sModule']) ? $aData['sModule'] : 'blog';
		$iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
		$iEditId = false;

		if(strlen(trim($sTitle)) == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.fill_in_a_title_for_your_blog")));
		}
		if(strlen(trim($sText)) == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.add_some_content_to_your_blog")));
		}

		if (!empty($sModule) && Phpfox::hasCallback($sModule, 'getItem')){
			// support pages
            $aCallback = Phpfox::callback($sModule . '.getItem' , $iItemId);      
		} else {
			// not in pages
		}

		if (($iFlood = Phpfox::getUserParam('blog.flood_control_blog')) !== 0)
		{
			$aFlood = array(
				'action' => 'last_post', // The SPAM action
				'params' => array(
					'field' => 'time_stamp', // The time stamp field
					'table' => Phpfox::getT('blog'), // Database table we plan to check
					'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
					'time_stamp' => $iFlood * 60 // Seconds);	
				)
			);
				 			
			// actually check if flooding
			if (Phpfox::getLib('spam')->check($aFlood))
			{
	            return array('result' => 0, 'error_code' => 1
	            	, 'error_message' =>  Phpfox::getPhrase('blog.your_are_posting_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
			}
		}	
		// init pattern 
		$aVals = array(
			'attachment' => $sAttachment, 
			'selected_categories' => $sCategories, 
			'title' => $sTitle, 
			'text' => $sText, 
			'tag_list' => $sTopic, 
			'privacy' => $iPrivacy, 
			'privacy_comment' => $iPrivacyComment, 
			'module_id' => $sModule, 
			'item_id' => $iItemId, 
		);
		if ('draft' == $sStatus){
			$aVals['post_status'] = 2;
		} else {
			$aVals['post_status'] = 1;
		}		
		// insert blog 
		$oFilter = Phpfox::getLib('parse.input');						
		if (isset($aVals['module_id']) && !empty($aVals['module_id']) && isset($aVals['item_id']) && !empty($aVals['item_id']) && 
			($aVals['privacy'] == 0 || $aVals['privacy_comment'] == 0) && Phpfox::hasCallback($aVals['module_id'], 'getItem'))
		{
			$aNewPrivacy = Phpfox::callback($aVals['module_id'] . '.getItem', $aVals['item_id']);
			
			if ($aVals['privacy'] == 0 && $aNewPrivacy['privacy'] != 0)
			{
				$aVals['privacy'] = $aNewPrivacy['privacy'];
			}
			if (isset($aNewPrivacy['privacy_comment']) && $aVals['privacy_comment'] == 0 && $aNewPrivacy['privacy_comment'] != 0)
			{
				$aVals['privacy_comment'] = $aNewPrivacy['privacy_comment'];
			}			
		}

		// check if the user entered a forbidden word
		$sReason = Phpfox::getService('mfox.helper.ban')->checkAutomaticBan($aVals['text'] . ' ' . $aVals['title']);
		if($sReason !== true){
            return array('result' => 0, 'error_code' => 1
            	, 'error_message' => $sReason
        	);			
		}

		if (!Phpfox::getParam('blog.allow_links_in_blog_title'))
		{
			if (!Phpfox::getLib('validator')->check($aVals['title'], array('url')))
			{
	            return array('result' => 0, 'error_code' => 1
	            	, 'error_message' =>  Phpfox::getPhrase('blog.we_do_not_allow_links_in_titles')
	        	);			
			}
		}		
		if (!isset($aVals['privacy']))
		{
			$aVals['privacy'] = 0;
		}
		if (!isset($aVals['privacy_comment']))
		{
			$aVals['privacy_comment'] = 0;
		}
		$sTitle = $oFilter->clean($aVals['title'], 255);
		$bHasAttachments = (!empty($aVals['attachment']) && Phpfox::getUserParam('attachment.can_attach_on_blog'));		
		if (!isset($aVals['post_status']))
		{
			$aVals['post_status'] = 1;
		}
		$aInsert = array(
			'user_id' => Phpfox::getUserId(),
			'title' => $sTitle,
			'time_stamp' => PHPFOX_TIME,
			'is_approved' => 1,
			'privacy' => (!empty($aVals['privacy']) ? $aVals['privacy'] : '0'),
			'privacy_comment' => (!empty($aVals['privacy_comment']) ? $aVals['privacy_comment'] : '0'),
			'post_status' => (isset($aVals['post_status']) ? $aVals['post_status'] : '1'),
			'total_attachment' => 0
		);		
		if (isset($aVals['item_id']) && isset($aVals['module_id']))
		{
			$aInsert['item_id'] = (int)$aVals['item_id'];
			$aInsert['module_id'] = $oFilter->clean($aVals['module_id']);
		}
		$bIsSpam = false;
		if (Phpfox::getParam('blog.spam_check_blogs'))
		{
			if (Phpfox::getLib('spam')->check(array(
						'action' => 'isSpam',										
						'params' => array(
							'module' => 'blog',
							'content' => $oFilter->prepare($aVals['text'])
						)
					)
				)
			)
			{
				$aInsert['is_approved'] = '9';
				$bIsSpam = true;				
			}
		}

		if (Phpfox::getUserParam('blog.approve_blogs'))
		{
			$aInsert['is_approved'] = '0';
			$bIsSpam = true;
		}
		$iId = $this->database()->insert(Phpfox::getT('blog'), $aInsert);		

		$this->database()->insert(Phpfox::getT('blog_text'), array(
				'blog_id' => $iId,
				'text' => $oFilter->clean($aVals['text']),
				'text_parsed' => $oFilter->prepare($aVals['text'])
			)
		);

		if (!empty($aVals['selected_categories']))
		{
			Phpfox::getService('blog.category.process')->addCategoryForBlog($iId, explode(',', rtrim($aVals['selected_categories'], ',')), ($aVals['post_status'] == 1 ? true : false));
		}

		if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support') && Phpfox::getUserParam('tag.can_add_tags_on_blogs'))
		{
			Phpfox::getService('tag.process')->add('blog', $iId, Phpfox::getUserId(), $aVals['text'], true);
		}
		else
		{
			if (Phpfox::getUserParam('tag.can_add_tags_on_blogs') && Phpfox::isModule('tag') && isset($aVals['tag_list']) && ((is_array($aVals['tag_list']) && count($aVals['tag_list'])) || (!empty($aVals['tag_list']))))
			{
				Phpfox::getService('tag.process')->add('blog', $iId, Phpfox::getUserId(), $aVals['tag_list']);
			}
		}

		// If we uploaded any attachments make sure we update the 'item_id'
		if ($bHasAttachments)
		{
			Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], Phpfox::getUserId(), $iId);
		}		

		if ($bIsSpam === true)
		{			
			// not support spam currently
			// return $iId;
		} else {
            if ($aVals['post_status'] == 1)
            {
                if (isset($aVals['module_id']) && $aVals['module_id'] == 'pages')
                {
                    (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->callback(Phpfox::callback($aVals['module_id'] . '.getFeedDetails', $aVals['item_id']))->add('blog', $iId, $aVals['privacy'], (isset($aVals['privacy_comment']) ? (int) $aVals['privacy_comment'] : 0), $aVals['item_id']) : null);
                }
                else
                {
                    (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->add('blog', $iId, $aVals['privacy'], (isset($aVals['privacy_comment']) ? (int) $aVals['privacy_comment'] : 0)) : null);
                }
                
                // Update user activity
                Phpfox::getService('user.activity')->update(Phpfox::getUserId(), 'blog', '+');
            }   

            if ($aVals['privacy'] == '4')
            {
                Phpfox::getService('privacy.process')->add('blog', $iId, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : array()));           
            }                   
        }		

		if ($aVals['post_status'] == 2)
		{
			$sMessage =  Phpfox::getPhrase('blog.blog_successfully_saved');
		}
		else 
		{
			$sMessage =  Phpfox::getPhrase('blog.your_blog_has_been_added');
		}

        return array(
            'result' => 1,
            'error_code' => 0,
            'message' => $sMessage,
            'iBlogId' => $iId,
        );
    }

    public function edit($aData){
		$iId = isset($aData['iBlogId']) ? (int)$aData['iBlogId'] : '';
		$oBlog = Phpfox::getService('blog');
		$aRow = $oBlog->getBlogForEdit($iId);
		if ($aRow['is_approved'] != '1' && 
			($aRow['user_id'] != Phpfox::getUserId() && !Phpfox::getUserParam('blog.edit_user_blog')) )
		{
            return array('result' => 0, 'error_code' => 1
            	, 'error_message' =>  Phpfox::getPhrase('blog.unable_to_edit_this_blog')
        	);			
		}

		$sTitle = isset($aData['sTitle']) ? $aData['sTitle'] : '';
		$sText = isset($aData['sText']) ? $aData['sText'] : '';
		$iPrivacy = isset($aData['iPrivacy']) ? $aData['iPrivacy'] : 0;
		$iPrivacyComment = isset($aData['iPrivacyComment']) ? $aData['iPrivacyComment'] : 0;
		// example: '9,6,7,'
		$sCategories = isset($aData['sCategories']) ? $aData['sCategories'] : '';
		// example: '9,6,7,' ONLY new attachment 
		$sAttachment = isset($aData['sAttachment']) ? $aData['sAttachment'] : '';
		// example: 'abc, def,'
		$sTopic = isset($aData['sTopic']) ? $aData['sTopic'] : '';
		// publish/draft
		$sStatus = isset($aData['sStatus']) ? $aData['sStatus'] : 'publish';
		$sModule = isset($aData['sModule']) ? $aData['sModule'] : 'blog';
		$iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;

		if(strlen(trim($sTitle)) == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.fill_in_a_title_for_your_blog")));
		}
		if(strlen(trim($sText)) == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.add_some_content_to_your_blog")));
		}

		if (!empty($sModule) && Phpfox::hasCallback($sModule, 'getItem')){
			// support pages
            $aCallback = Phpfox::callback($sModule . '.getItem' , $iItemId);      
		} else {
			// not in pages
		}


        $iUserId = Phpfox::getUserId();
		// init pattern 
		$aVals = array(
			'attachment' => $sAttachment, 
			'selected_categories' => $sCategories, 
			'title' => $sTitle, 
			'text' => $sText, 
			'tag_list' => $sTopic, 
			'privacy' => $iPrivacy, 
			'privacy_comment' => $iPrivacyComment, 
			'module_id' => $sModule, 
			'item_id' => $iItemId, 
		);
		if ('draft' == $sStatus){
			$aVals['post_status'] = 2;
		} else {
			$aVals['post_status'] = 1;
		}

		// update blog 
		$oFilter = Phpfox::getLib('parse.input');
		

		$bHasAttachments = (!empty($aVals['attachment']) && Phpfox::getUserParam('attachment.can_attach_on_blog') && $iUserId == Phpfox::getUserId());		
		$sReason = Phpfox::getService('mfox.helper.ban')->checkAutomaticBan($aVals['text'] . ' ' . $aVals['title']);
		if($sReason !== true){
            return array('result' => 0, 'error_code' => 1
            	, 'error_message' => $sReason
        	);			
		}


		if ($bHasAttachments)
		{
			Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], $iUserId, $iId);
		}

		$sTitle = $oFilter->clean($aVals['title'], 255);
		$aUpdate = array(
			'title' => $sTitle,
			'time_update' => PHPFOX_TIME,
			'is_approved' => 1,
			'privacy' => (isset($aVals['privacy']) ? $aVals['privacy'] : '0'),
			'privacy_comment' => (isset($aVals['privacy_comment']) ? $aVals['privacy_comment'] : '0'),
			'post_status' => (isset($aVals['post_status']) ? $aVals['post_status'] : '1'),
			'total_attachment' => (Phpfox::isModule('attachment') ? Phpfox::getService('attachment')->getCountForItem($iId, 'blog') : '0')
		);		

		if ($aRow !== null && isset($aVals['post_status']) && $aRow['post_status'] == '2' && $aVals['post_status'] == '1')
		{
			$aUpdate['time_stamp'] = PHPFOX_TIME;	
		}


		if (Phpfox::getUserParam('blog.approve_blogs')) // if the blogs added by this user group need to be approved...
		{
			$aVals['is_approved'] = $aUpdate['is_approved'] = 0;
		}

		$bIsSpam = false;
		if (Phpfox::getParam('blog.spam_check_blogs'))
		{
			if (Phpfox::getLib('spam')->check(array(
						'action' => 'isSpam',										
						'params' => array(
							'module' => 'blog',
							'content' => $oFilter->prepare($aVals['text'])
						)
					)
				)
			)
			{
				$aUpdate['is_approved'] = '9';
				$bIsSpam = true;				
			}
		}

		
		(($sPlugin = Phpfox_Plugin::get('blog.service_process_update')) ? eval($sPlugin) : false);
		
        $aVals['text'] =  html_entity_decode($aVals["text"]);
        $text_parsed =  $oFilter->prepare($aVals["text"]);
        $text_parsed = str_replace(' title="', ' style="', $text_parsed);
		$this->database()->update(Phpfox::getT('blog'), $aUpdate, 'blog_id = ' . (int) $iId);	
		$this->database()->update(Phpfox::getT('blog_text'), array(
			'text' => $oFilter->clean($aVals['text']), 
			'text_parsed' => $text_parsed,
		), 'blog_id = ' . (int) $iId);

		Phpfox::getService('blog.category.process')->updateCategoryForBlog($iId, explode(',', rtrim($aVals['selected_categories'], ',')), ($aVals['post_status'] == 1 ? true : false));


		if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support') && Phpfox::getUserParam('tag.can_add_tags_on_blogs'))
		{
			Phpfox::getService('tag.process')->update('blog', $iId, Phpfox::getUserId(), $aVals['text'], true);
		}
		else
		{
			if (Phpfox::isModule('tag') && Phpfox::getUserParam('tag.can_add_tags_on_blogs'))
			{
				Phpfox::getService('tag.process')->update('blog', $iId, $iUserId, (!Phpfox::getLib('parse.format')->isEmpty($aVals['tag_list']) ? $aVals['tag_list'] : null));
			}
		}

		if ($aRow !== null && $aRow['post_status'] == '2' && $aVals['post_status'] == '1')
		{	
			(Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->add('blog', $iId, $aVals['privacy'], $aVals['privacy_comment'], 0, $iUserId) : null);
			
			// Update user activity
			Phpfox::getService('user.activity')->update($iUserId, 'blog');			
		}
		else 
		{
			(Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->update('blog', $iId, $aVals['privacy'], $aVals['privacy_comment'], 0, $iUserId) : null);
		}		

		if (Phpfox::isModule('privacy'))
		{
			if ($aVals['privacy'] == '4')
			{
				Phpfox::getService('privacy.process')->update('blog', $iId, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : array()));
			}
			else 
			{
				Phpfox::getService('privacy.process')->delete('blog', $iId);
			}			
		}


		$sMessage =  Phpfox::getPhrase('blog.blog_updated');
        return array(
            'result' => 1,
            'error_code' => 0,
            'message' => $sMessage,
            'iBlogId' => $iId,
        );
    }

    public function categories($aData){
    	return $this->getCategories($aData, false, 'public');
    }

    public function canPostComment($iItemId){
    	
		if(Phpfox::getUserParam('privacy.can_comment_on_all_items')){
			return null;
		}
		
		if (!Phpfox::getUserParam('blog.can_post_comment_on_blog')){
			return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_blog")));
		}
		
        if (!Phpfox::getUserParam('blog.view_blogs'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_blog")));
        }

        if (!Phpfox::getUserParam('blog.can_post_comment_on_blog'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_blog")));
        }

        if (!($aItem = Phpfox::getService('blog')->getBlog($iItemId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_blog_you_are_looking_for_cannot_be_found")));
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('blog', $aItem['blog_id'], $aItem['user_id'], $aItem['privacy'], $aItem['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        if (!Phpfox::getService('comment')->canPostComment($aItem['user_id'], $aItem['privacy_comment']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_this_item")));
        }

        // return null - it means TRUE
        return null;
    }

    public function canView($iItemId){
        if (!Phpfox::getUserParam('blog.view_blogs'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_blog")));
        }

        if (!($aItem = Phpfox::getService('blog')->getBlog($iItemId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_blog_you_are_looking_for_cannot_be_found")));
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('blog', $aItem['blog_id'], $aItem['user_id'], $aItem['privacy'], $aItem['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        // return null - it means TRUE
        return null;
    }
    
}
