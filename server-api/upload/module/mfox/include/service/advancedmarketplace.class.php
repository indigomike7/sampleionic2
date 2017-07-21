<?php
/**
 * Service component
 *
 * @category Mobile phpfox server api
 * @author Ly Tran <lytk@younetco.com>
 * @version $Id$
 * @copyright $Copyright$
 * @license $License$
 * @package mfox.advancedmarketplace
 */

/**
 *
 * @package mfox.advancedmarketplace
 * @author Ly Tran <lytk@younetco.com>
 */

class Mfox_Service_Advancedmarketplace extends Phpfox_Service {

    /**
     * @ignore
     */
    private $_sCategory = null;    
    /**
     * @ignore
     */
    private $_bIsSeen = false;
	
	
	private $_sDefaultImagePath = null;
    /**
     * @ignore
     */
    public function __construct() {
    	$this->_sDefaultImagePath =  Phpfox::getParam('core.url_module') . 'mfox/static/image/marketplace_default.png';
    } 
	
	function getDefaultImagePath(){
		return $this->_sDefaultImagePath;
	}   

    public function fetch($aData) {
        return $this -> getMarketplaces($aData);
    }

    public function getMarketplaces($aData){
        if(Phpfox::getUserParam('advancedmarketplace.can_access_advancedmarketplace') == false){
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_marketplace"))
            );
        }

        // Phpfox::getService('marketplace.process')->sendExpireNotifications();
        
        if (!isset($aData['iPage'])){
            $aData['iPage'] = 1;
        }
        if ((int)$aData['iPage'] == 0){
            return array();
        }
        $sSortBy = (isset($aData['sOrder']) && empty($aData['sOrder']) == false) ? $aData['sOrder'] : 'latest';
        $iCategoryId = (isset($aData['iCategoryId']) && (int)$aData['iCategoryId'] > 0) ? $aData['iCategoryId'] : 0;
        $sLocation = (isset($aData['sCountryIso']) && empty($aData['sCountryIso']) == false) ? $aData['sCountryIso'] : false;
        $iAmountOfMarketplace = isset($aData['iAmountOfMarketplace']) ? (int) $aData['iAmountOfMarketplace'] : 10;
        $sSearch = isset($aData['sSearch']) ? $aData['sSearch'] : '';
        $sView = isset($aData['sView']) ? $aData['sView'] : '';
        $aParentModule = array(
            'module_id' => isset($aData['sModule']) ? $aData['sModule'] : '',
            'item_id' => isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0
        );
        $bIsPage = $aParentModule['module_id'] == 'pages' && $aParentModule['item_id'] > 0;
        $bIsProfile = (isset($aData['bIsProfile']) && $aData['bIsProfile'] == 'true') ? true : false;
        if ($bIsProfile)
        {
            $iProfileId = isset($aData['iProfileId']) ? (int) $aData['iProfileId'] : 0;
            $aUser = Phpfox::getService('user')->get($iProfileId);
            if (!isset($aUser['user_id']))
            {
                return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.profile_is_not_valid")));
            }
        }

        // process 
        $aCond = array();
        if (!empty($sSearch))
        {
            $aCond[] = ' ( '
                        . 'l.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"'
                        . ' OR mt.description_parsed LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"'
                        . ' ) ';
        }

        switch ($sView)
        {
            case 'sold':
                $aCond[] = ' l.user_id = ' . Phpfox::getUserId();
                $aCond[] = ' l.is_sell = 1';
                break;

            case 'featured':
                $aCond[] = ' l.is_featured = 1';
                break;

            case 'my':
                $aCond[] = ' l.user_id = ' . Phpfox::getUserId();
                break;

            case 'pending':
                if (Phpfox::getUserParam('advancedmarketplace.can_approve_listings'))
                {
                    $aCond[] = ' l.view_id = 1';
                } else {
                    return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_approve_marketplace")));
                }
                break;

            case 'expired':
                if (Phpfox::getParam('advancedmarketplace.days_to_expire_listing') > 0 && Phpfox::getUserParam('advancedmarketplace.can_view_expired'))
                {
                    $iExpireTime = (PHPFOX_TIME - (Phpfox::getParam('advancedmarketplace.days_to_expire_listing') * 86400));
                    $aCond[] = ' l.time_stamp < ' . $iExpireTime;
                    break;
                }

            case 'invoice':
                return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_yet")));
                break;

            default:
                if ($bIsProfile === true)
                {
                    $aCond[] = " l.view_id IN(" . ($aUser['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ") AND l.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ") AND l.user_id = " . $aUser['user_id'] . "";
                }
                else
                {
                    switch ($sView)
                    {
                        case 'invites':
                            $this->seen();
                            break;
                    }

                    if (($sLocation))
                    {
                        $aCond[] = ' l.country_iso = \'' . Phpfox::getLib('database')->escape($sLocation) . '\'';
                    }

                    $aCond[] = ' l.view_id = 0 AND l.privacy IN(%PRIVACY%)';
                }
				$aCond[] = ' l.post_status = 1 ';
                break;
        }           
        if ((int)$iCategoryId > 0)
        {
            $aCond[] = ' mcd.category_id = ' . (int) $iCategoryId;
        }

        $this->category($iCategoryId);    
        
        if (Phpfox::getParam('advancedmarketplace.days_to_expire_listing') > 0 && $sView != 'my' && $sView != 'expired')
        {
            $iExpireTime = (PHPFOX_TIME - (Phpfox::getParam('advancedmarketplace.days_to_expire_listing') * 86400));
            $aCond[] = '  l.time_stamp >=' . $iExpireTime;
        }

        // not support yet
        // // if its a user trying to buy sponsor space he should get only his own listings
        // if ($this->request()->get('sponsor') == 'help')
        // {
        //     $this->search()->setCondition('AND m.user_id = ' . Phpfox::getUserId() . ' AND is_sponsor != 1');
        // }
        
        // update privacy 
        foreach ($aCond as $iKey => $sCond)
        {
            switch ($sView) {
                case 'my':
                    $aCond[$iKey] = str_replace('%PRIVACY%', '0,1,2,3,4', $sCond);
                    break;
                default:
                    if (Phpfox::getParam('core.section_privacy_item_browsing')) {
                        $aCond[$iKey] = str_replace('%PRIVACY%', '0,1,2,3,4', $sCond);
                    } else {
                        $aCond[$iKey] = str_replace('%PRIVACY%', '0', $sCond);
                    }                    
                    break;
            }
        }

        // get counter
        $this->database()
                ->select('COUNT(l.listing_id)')
                ->from(Phpfox::getT('advancedmarketplace'), 'l')
                ->leftJoin(Phpfox::getT('advancedmarketplace_text'), 'mt', 'mt.listing_id = l.listing_id')   ;
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
            (int)$iCount, (int)$iAmountOfMarketplace, (int)$aData['iPage'] - 1);
        if($pageNext == 0){
            return array();
        }

        // sort by  
        switch(strtolower($sSortBy)){
            case 'latest':
                $sOrder = 'l.time_stamp DESC, l.listing_id ASC';
                break;                
            case 'most_liked':
                $sOrder = 'l.total_like DESC, l.listing_id ASC';
                break;                
            case 'most_discussed':
                $sOrder = 'l.total_comment DESC, l.listing_id ASC';
                break;                
            default:
                $sOrder = 'l.time_stamp DESC';
                break;
        }

        // get data 
        $this->database()
                ->select('mt.description_parsed AS description, mt.short_description_parsed AS mini_description, lik.like_id AS is_liked, l.*, l.country_iso AS listing_country_iso,  u.user_id, u.profile_page_id, u.server_id AS user_server_id, u.user_name, u.full_name, u.gender, u.user_image, u.is_invisible, u.user_group_id, u.language_id')
                ->from(Phpfox::getT('advancedmarketplace'), 'l');
        $this->getQueryJoins($sView);
        $aRows = $this->database()
                ->leftJoin(Phpfox::getT('advancedmarketplace_text'), 'mt', 'mt.listing_id = l.listing_id')   
                ->leftJoin(Phpfox::getT('like'), 'lik', "lik.type_id = 'advancedmarketplace' AND lik.item_id = l.listing_id AND lik.user_id = " . Phpfox::getUserId())
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
                ->where(implode(' AND ', $aCond))
                ->order($sOrder)
                ->limit((int) $aData['iPage'], $iAmountOfMarketplace, $iCount)
                ->execute('getSlaveRows');
                
        $aMarketplaces = array();  
        foreach($aRows as $aItem){
            $aMarketplaces[] = $this->__getMarketplaceData($aItem, 'small');
        }        
        
        return $aMarketplaces;
    }

	
    /**
     * @ignore
     */
    public function getCategories($aParams = array()){
        $iParentCategoryId = isset($aParams['iParentCategoryId']) ?  (int)$aParams['iParentCategoryId'] : 0;
        
        return Phpfox::getService('advancedmarketplace.category')->getForBrowse($iParentCategoryId);
    }

    public function categories($aData){
        return $this->getCategories($aData);
    }

    /**
     * @ignore
     */
    public function allcategories($aData){
        $categories = $this->getCategories($aData);
        $result = array();
        foreach ($categories as $key => $aItem){
            $result[] = $this->__getCategories($aItem);
        }

        return $result;
    }    
    /**
     * @ignore
     */
    public function __getCategories($aItem = array()){
        $aSubForum = array();
        $aItem['sub'] = $this->getCategories(array('iParentCategoryId' => $aItem['category_id']));
        foreach($aItem['sub'] as $cat){
            $aSubForum[] = $this->__getCategories($cat);
        }

        return array(
            'category_id' => $aItem['category_id'], 
            'name' => (Phpfox::isPhrase($aItem['name'])) ? Phpfox::getService('mfox')->decodeUtf8Compat(_p($aItem['name'])) :html_entity_decode(Phpfox::getLib('locale')->convert($aItem['name'])),
            'sub' => $aSubForum, 
        );
    }

    public function formadd($aData)
    {
        $response  =  array(
            'view_options'=> Phpfox::getService('mfox.privacy')->privacy($aData),
            'comment_options'=> Phpfox::getService('mfox.privacy')->privacycomment($aData),
            'category_options'=> $this->allcategories($aData),
            'currency_options'=> Phpfox::getService('mfox.core')->getcurrencies($aData),
            'country_options'=> Phpfox::getService('mfox.core')->getCountries($aData),
            'perms'=> $this->__getPermission($aData),
        );

        $iValue  = Phpfox::getService('user.privacy')->getValue('advancedmarketplace.display_on_profile');
        $response['default_privacy_setting'] = Phpfox::getService('mfox.privacy')->defaultprivacy("$iValue", $response['view_options']);
        
        return $response;
    }    

    public function detail($aData){
        $iListingId = isset($aData['iListingId']) ? (int) $aData['iListingId'] : 0;
        if (!Phpfox::getUserParam('advancedmarketplace.can_access_advancedmarketplace'))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_marketplace"))
            );
        }

        if (!($aListing = $this->getListing($iListingId)))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('advancedmarketplace.the_listing_you_are_looking_for_either_does_not_exist_or_has_been_removed')
            );            
        }

        if (Phpfox::isUser() && $aListing['invite_id'] && !$aListing['visited_id'] && $aListing['user_id'] != Phpfox::getUserId())
        {
            Phpfox::getService('advancedmarketplace.process')->setVisit($aListing['listing_id'], Phpfox::getUserId());
        }       
        
        if (Phpfox::isUser() && Phpfox::isModule('notification'))
        {
            Phpfox::getService('notification.process')->delete('comment_advancedmarketplace', $this->request()->getInt('req2'), Phpfox::getUserId());
            Phpfox::getService('notification.process')->delete('advancedmarketplace_like', $this->request()->getInt('req2'), Phpfox::getUserId());
        }
        
        if (Phpfox::isModule('notification') && $aListing['user_id'] == Phpfox::getUserId())
        {
            Phpfox::getService('notification.process')->delete('advancedmarketplace_approved', $aListing['listing_id'], Phpfox::getUserId());
        }   

        $bCanView = false;
        if(Phpfox::getService('privacy')->check('advancedmarketplace', $aListing['listing_id'], $aListing['user_id'], $aListing['privacy'], $aListing['is_friend'], $bReturn = true)){
            $bCanView = true;
        }
        if($bCanView == false){
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time')
            );
        }

        $aListingData = $this->__getMarketplaceData($aListing, 'large');
        return $aListingData;
    }

    /**
     * Override to fix issue from advancedmarketplace
     * @see AdvancedMarketplace_Service_AdvancedMarketplace->getListing
     */
    public function getListing($iId)
    {
        if (Phpfox::isModule('like'))
        {
            $this->database()->select('lik.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'lik', 'lik.type_id = \'advancedmarketplace\' AND lik.item_id = l.listing_id AND lik.user_id = ' . Phpfox::getUserId());
        }

        if (Phpfox::isModule('track'))
        {
            $this->database()->select("advancedmarketplace_track.item_id AS is_viewed, ")->leftJoin(Phpfox::getT('advancedmarketplace_track'), 'advancedmarketplace_track', 'advancedmarketplace_track.item_id = l.listing_id AND advancedmarketplace_track.user_id = ' . Phpfox::getUserBy('user_id'));
        }

        $this->database()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f', "f.user_id = l.user_id AND f.friend_user_id = " . Phpfox::getUserId());

        $aListing = $this->database()->select('mt.short_description, mt.short_description_parsed, l.*, l.country_iso AS listing_country_iso, ml.invite_id, ml.visited_id, uf.total_score, uf.total_rating, ua.activity_points, ' . (Phpfox::getParam('core.allow_html') ? 'mt.description_parsed' : 'mt.description') . ' AS description, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('advancedmarketplace'), 'l')
            ->join(Phpfox::getT('advancedmarketplace_text'), 'mt', 'mt.listing_id = l.listing_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
            ->join(Phpfox::getT('user_field'), 'uf', 'uf.user_id = l.user_id')
            ->join(Phpfox::getT('user_activity'), 'ua', 'ua.user_id = l.user_id')
            ->leftJoin(Phpfox::getT('advancedmarketplace_invite'), 'ml', 'ml.listing_id = l.listing_id AND ml.invited_user_id = ' . Phpfox::getUserId())
            ->where('l.listing_id = ' . (int) $iId)
            ->execute('getSlaveRow');

        if (!isset($aListing['listing_id']))
        {
            return false;
        }
        
        if (!Phpfox::isModule('like'))
        {
            $aListing['is_liked'] = false;
        }
        
        if ($aListing['view_id'] == '1')
        {
            if ($aListing['user_id'] == Phpfox::getUserId() || Phpfox::getUserParam('advancedmarketplace.can_approve_listings'))
            {

            }
            else
            {
                return false;
            }
        }
        
        if (!empty($aListing['location']))
        {
            $aListing['map_location'] = $aListing['location'];
            if (!empty($aListing['address']))
            {
                $aListing['map_location'] .= ',' . $aListing['address'];
            }
            if (!empty($aListing['city']))
            {
                $aListing['map_location'] .= ',' . $aListing['city'];
            }
            if (!empty($aListing['postal_code']))
            {
                $aListing['map_location'] .= ',' . $aListing['postal_code'];
            }   
            if (!empty($aListing['country_child_id']))
            {
                $aListing['map_location'] .= ',' . Phpfox::getService('core.country')->getChild($aListing['country_child_id']);
            }           
            if (!empty($aListing['listing_country_iso']))
            {
                $aListing['map_location'] .= ',' . Phpfox::getService('core.country')->getCountry($aListing['listing_country_iso']);
            }           
            
            $aListing['map_location'] = urlencode($aListing['map_location']);
        }
        
        $aListing['categories'] = Phpfox::getService('advancedmarketplace.category')->getCategoriesById($aListing['listing_id']);
        $aListing['bookmark_url'] = Phpfox::getLib('url')->permalink('advancedmarketplace', $aListing['listing_id'], $aListing['title']);
        $aListing['category'] = Phpfox::getService('advancedmarketplace.category')->getCategoryId($aListing['listing_id']);
        
        if ( (Phpfox::getParam('advancedmarketplace.days_to_expire_listing') > 0) && ( $aListing['time_stamp'] < (PHPFOX_TIME - (Phpfox::getParam('advancedmarketplace.days_to_expire_listing') * 86400)) ) )
        {
            $aListing['is_expired'] = true;
        }
        
        return $aListing;
    }

    /**
     * @ignore
     */
    private function __getMarketplaceData($aItem, $sMoreInfo = 'large'){
        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aItem, '_50_square');
			
		if ($aItem['image_path']){
			$sMarketplaceImage = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aItem['server_id'],
                'path' => 'core.url_pic',
                'file' => 'advancedmarketplace/' . $aItem['image_path'],
                'suffix' => '',
                'return_url' => true
                    )
            );	
		}else{
			$sMarketplaceImage = $this->getDefaultImagePath();
		}
        
        // if (!file_exists(Phpfox::getParam('core.dir_pic') . "advancedmarketplace/" . sprintf($aItem['image_path'], '')) || is_dir(Phpfox::getParam('core.dir_pic') . "advancedmarketplace/" . sprintf($aItem['image_path'], ''))){
            // $sMarketplaceImage = Phpfox::getLib('template')->getStyle('image', 'noimage/item.png');
        // }

        $bCanComment = $this->canPostComment($aItem['listing_id']);
        if($bCanComment === null){
            $bCanComment = true;
        } else {
            $bCanComment = false;
        }                

        $bCanSendMessage = true;
        if (Phpfox::getUserParam('mail.can_compose_message') == false
            || (Phpfox::getParam('mail.spam_check_messages') && Phpfox::isSpammer())
            || (Phpfox::getService('mail')->canMessageUser($aItem['user_id']) == false)
            ){
            $bCanSendMessage = false;
        }      

        $sMiniDescription = '';
        if(isset($aItem['mini_description'])){
            $sMiniDescription = $aItem['mini_description'];
        } else if(isset($aItem['short_description_parsed'])){
            $sMiniDescription = $aItem['short_description_parsed'];
        } else if(isset($aItem['short_description'])){
            $sMiniDescription = $aItem['short_description'];
        }
		
		$bCanEdit  = 0;
		$bCanDelete = 0;
		$bIsOwner  = Phpfox::getUserId() == $aItem['user_id'];  
		
		if (($bIsOwner && Phpfox::getUserParam('advancedmarketplace.can_edit_own_listing'))
		|| (!$bIsOwner && Phpfox::getUserParam('advancedmarketplace.can_edit_other_listing'))){
			$bCanEdit =  1;
		}
		
		if (($bIsOwner && Phpfox::getUserParam('advancedmarketplace.can_delete_own_listing'))
		|| (!$bIsOwner && Phpfox::getUserParam('advancedmarketplace.can_delete_other_listings'))){
			$bCanDelete =  1;
		}	
		
		
		 $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser(
            'advancedmarketplace'
            , $aItem['listing_id']
            , false
            , 999999
        );
        $aUserLike = array();
        foreach($aLike['likes'] as $like){
            $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
        }
		
        $result = array(
                    'bAutoSell' => ((int)$aItem['auto_sell'] > 0 ? true : false),
                    'sLocation' => isset($aItem['location']) ? $aItem['location'] : '',
                    'sCity'=>$aItem['city'],
                    'iCountryChildId'=>$aItem['country_child_id'],
                    'sCountryIso'=>$aItem['listing_country_iso'],
                    'sCountry'=> Phpfox::getService('core.country')->getCountry($aItem['listing_country_iso']),
                    'sCountryChild'=> Phpfox::getService('core.country')->getChild($aItem['country_child_id']),
                    'sCurrencyId'=>$aItem['currency_id'],
                    'sSymbol' => Phpfox::getService('core.currency')->getSymbol($aItem['currency_id']),
                    'sDescription'=>$aItem['description'],
                    'iGroupId'=>$aItem['group_id'],
                    'bIsClosed'=>((int)$aItem['is_closed'] > 0 ? true : false),
                    'bIsFeatured'=>((int)$aItem['is_featured'] > 0 ? true : false),
                    'bIsLiked'=> ((int)$aItem['is_liked'] > 0 ? true : false),
                    'bIsNotified'=> ((int)$aItem['is_notified'] > 0 ? true : false),
                    'bIsSell'=> ((int)$aItem['is_sell'] > 0 ? true : false),
                    'bIsSponsor'=> ((int)$aItem['is_sponsor'] > 0 ? true : false),
                    'iListingId'=> $aItem['listing_id'],
                    'sMiniDescription'=> $sMiniDescription,
                    'sPostalCode'=> $aItem['postal_code'],
                    'sPrice'=> $aItem['price'],
                    'iPrivacy'=> $aItem['privacy'],
                    'iPrivacyComment'=> $aItem['privacy_comment'],
                    'iTimeStamp'=> $aItem['time_stamp'],
                    'sTitle'=> $aItem['title'],
                    'iTotalComment'=> $aItem['total_comment'],
                    'iTotalLike'=> $aItem['total_like'],
                    'iTotalDislike'=> $aItem['total_dislike'],
                    'iViewId'=> $aItem['view_id'],
                    'sMarketplaceImage' => $sMarketplaceImage,
                    'bIsPending'=> ((int)$aItem['view_id'] == 1 ? true : false),
                    'bCanComment' => $bCanComment,
                    'bCanSendMessage' => $bCanSendMessage,
					'bHasImage'=> $aItem['image_path']?1:0,
                    'bIsOwner'=>$bIsOwner,
                    'bCanEdit'=>$bCanEdit,
                    'bCanDelete'=>$bCanDelete,
                    
                    'iUserId'=> $aItem['user_id'],
                    'sUserImage' => $sUserImage,
                    'sUserName' => $aItem['user_name'],
                    'sFullname' => $aItem['full_name'],

                    'sModelType' => 'advancedmarketplace',
                    'aLikes' => $aUserLike,
                    ); 

        switch ($sMoreInfo) {
            case 'large':
                $aImages = $this->__getImages($aItem['listing_id']);
                $aTemp = Phpfox::getService('advancedmarketplace')->getForEdit($aItem['listing_id'], true);
                $sFullSiteUrl = Phpfox::getLib('url')->permalink('advancedmarketplace.detail', $aItem['listing_id'], $aItem['title']);

                $sModuleId = 'advancedmarketplace';
                $sStoreKitPurchaseIdIphone = '';
                $sStoreKitPurchaseIdIpad = '';
                $deviceIphone = Phpfox::getService('mfox.helper')->getConst('device.support.ios');
                $deviceIpad = Phpfox::getService('mfox.helper')->getConst('device.support.ipad');
                $aStoreKitPurchase = Phpfox::getService('mfox.transaction')->getAllStoreKitPurchaseByModuleId($sModuleId);
                foreach ($aStoreKitPurchase as $key => $value) {
                    switch ($value['storekitpurchase_device']) {
                        case $deviceIphone:
                            $sStoreKitPurchaseIdIphone = $value['storekitpurchase_key'];
                            break;

                        case $deviceIpad:
                            $sStoreKitPurchaseIdIpad = $value['storekitpurchase_key'];                        
                            break;                        
                        default:
                            break;
                    }
                }

                return array_merge($result, array(
                    'iActivityPoints' => $aItem['activity_points'],
                    'iInviteId' => $aItem['invite_id'],
                    'bIsFriend'=> ((int)$aItem['is_friend'] > 0 ? true : false),
                    'iTotalRating' => $aItem['total_rating'],
                    'iTotalScore' => $aItem['total_score'],
                    'iUserGroupId' => $aItem['user_group_id'],
                    'iVisitedId' => $aItem['visited_id'],
                    'aCategoriesData' => $aItem['categories'],
                    'aCategoriesId' => explode(',', $aTemp['categories']),
                    'aImages' => $aImages,
                    'bIsAllowBuyInApp' => Phpfox::getParam('mfox.is_allow_buy_in_app'),
                    'bIsShowBuyInFullSite' => Phpfox::getParam('mfox.is_show_buy_in_full_site'),
                    'sFullSiteUrl' => $sFullSiteUrl,
                    'bHasImage'=> $aItem['image_path']?1:0,
                    'aStoreKitPurchaseId' => array(
                        'iphone' => $sStoreKitPurchaseIdIphone, 
                        'ipad' => $sStoreKitPurchaseIdIpad, 
                        ),
                ));
                break;
            case 'medium':
            case 'small':
                return $result;
                break;
        }
    }

    /**
     * @ignore
     */
    private function __getImages($iListingId){
        $aImages = Phpfox::getService('advancedmarketplace')->getImages($iListingId);
        $result = array();
        foreach ($aImages as $key => $value) {
            $sMarketplaceImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $value['server_id'],
                    'path' => 'core.url_pic',
                    'file' => 'advancedmarketplace/' . $value['image_path'],
                    'suffix' => '',
                    'return_url' => true
                        )
                );

            $result[] = array('iImageId' => $value['image_id'], 'sImagePath' => $sMarketplaceImage);
        }

        return $result;
    }
    
    /**
     * @ignore
     */
    public function getQueryJoins($sView = '', $bIsCount = false, $bNoQueryFriend = false){
        if (Phpfox::isModule('friend') && Phpfox::getService('mfox.friend')->queryJoin($sView, $bNoQueryFriend))
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = l.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());  
        }        
        if ($this->_sCategory !== null)
        {       
            $this->database()->innerJoin(Phpfox::getT('advancedmarketplace_category_data'), 'mcd', 'mcd.listing_id = l.listing_id');
            
            if (!$bIsCount)
            {
                $this->database()->group('l.listing_id');
            }
        }   

        if ($this->_bIsSeen !== false)
        {
            $this->database()->join(Phpfox::getT('advancedmarketplace_invite'), 'mi', 'mi.listing_id = l.listing_id AND mi.visited_id = 0 AND mi.invited_user_id = ' . Phpfox::getUserId());
        }       
    }

    /**
     * @ignore
     */
    public function seen()
    {
        $this->_bIsSeen = true;
        
        return $this;
    }

    /**
     * @ignore
     */
    public function category($sCategory)
    {
        $this->_sCategory = $sCategory;
        
        return $this;
    }

    public function formedit($aData){
        return array_merge( 
            $this->detail($aData), 
            $this->formadd($aData)
        );
    }   

    public function delete($aData){
        $iListingId = isset($aData['iListingId']) ? (int) $aData['iListingId'] : 0;
        if ($iListingId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.listing_id_is_not_valid")));
        }

        $aListing = Phpfox::getService('advancedmarketplace')->getForEdit($iListingId, true);
        if (!isset($aListing['listing_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.listing_has_been_deleted_or_you_do_not_have_permission_to_delete_it")));
        }

        // Check privacy
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('advancedmarketplace', $aListing['listing_id'], $aListing['user_id'], $aListing['privacy'], $aListing['is_friend'], $bReturn = true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        $mResult = Phpfox::getService('advancedmarketplace.process')->delete($iListingId);
        if ($mResult !== false)
        {
            return array('result' => 1
                , 'error_code' => 0
                , 'message' =>  Phpfox::getPhrase('advancedmarketplace.listing_successfully_deleted')
            );
        }

        return array('result' => 0
            , 'error_code' => 1
            , 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.listing_has_been_deleted_or_you_do_not_have_permission_to_delete_it"))
        );
    }     

    public function create($aData){
        if (!Phpfox::getUserParam('advancedmarketplace.can_create_listing'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_create_new_listing")));
        }

        $sTitle = isset($aData['sTitle']) ? $aData['sTitle'] : '';
        if(strlen(trim($sTitle)) == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('advancedmarketplace.provide_a_name_for_this_listing'));
        }        
        $sCountryIso = isset($aData['sCountryIso']) ? $aData['sCountryIso'] : '';
        if(strlen(trim($sTitle)) == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('advancedmarketplace.provide_a_location_for_this_listing'));
        }
        
        $sPrice = isset($aData['sPrice']) ? $aData['sPrice'] : "0";
		if ($sPrice == "0"){
			$sPrice = "0.0";
		}
		
        if(Phpfox::getService('mfox.core')->isNumeric($sPrice) == false){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.provide_a_valid_price")));
        }

        $bIsEdit = false;
        $iEditId = false;
        $bIsSetup = false;
        $sAction = '';   
        $iListingId = isset($aData['iListingId']) ? (int)$aData['iListingId'] : '';
        if($iListingId > 0){
            $bIsEdit = true;
            $iEditId = $iListingId;
        }        
        if ($iEditId)
        {            
            if (($aListing = Phpfox::getService('advancedmarketplace')->getForEdit($iEditId, true)))
            {
                if (($aListing['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('advancedmarketplace.can_edit_own_listing')) 
                    || Phpfox::getUserParam('advancedmarketplace.can_edit_other_listing'))
                {
                    $bIsEdit = true;                
                } else {
                    return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_edit_this_listing")));
                }
            } else {
                return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_find_the_listing_you_are_trying_to_edit")));
            }
        }

        $aVals = array(
            'category' => $aData['aCategoryId'], 
            'title' => $sTitle, 
            'short_description' => isset($aData['sMiniDescription']) ? $aData['sMiniDescription'] : '', 
            'description' => isset($aData['sDescription']) ? $aData['sDescription'] : '', 
            'currency_id' => isset($aData['sCurrencyId']) ? $aData['sCurrencyId'] : 'USD', 
            'price' => $sPrice, 
            'is_sell' => (isset($aData['iIsSell']) &&  (int)$aData['iIsSell'] > 0 ? 1 : 0), 
            'auto_sell' => (isset($aData['iAutoSell']) &&  (int)$aData['iAutoSell'] > 0 ? 1 : 0), 
            'country_iso' => $sCountryIso, 
            'country_child_id' => isset($aData['iCountryChildId']) ? $aData['iCountryChildId'] : '', 
            'city' => isset($aData['sCity']) ? $aData['sCity'] : '', 
            'postal_code' => isset($aData['sPostalCode']) ? $aData['sPostalCode'] : '', 
            'privacy' => isset($aData['iPrivacy']) ? $aData['iPrivacy'] : 0, 
            'privacy_comment' => isset($aData['iPrivacyComment']) ? $aData['iPrivacyComment'] : 0, 
            'emails' => isset($aData['sEmails']) ? $aData['sEmails'] : '', 
            'personal_message' => isset($aData['sPersonalMessage']) ? $aData['sPersonalMessage'] : '', 

            'address' => isset($aData['sAddress']) ? $aData['sAddress'] : '', 
            'gmap' => array('latitude' => 0, 'longitude' => 0), 
        );

        if ($bIsEdit){
            if (Phpfox::getService('advancedmarketplace.process')->update($aListing['listing_id'], $aVals))
            {
                $sMessage =  Phpfox::getPhrase('advancedmarketplace.listing_successfully_updated');
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'message' => $sMessage,
                    'iListingId' => $aListing['listing_id'],
                );                
            }
        } else {

            if (Phpfox_Error::isPassed())
            {               
                if ($iId = Phpfox::getService('advancedmarketplace.process')->add($aVals))
                {                           
                    $sMessage =  Phpfox::getPhrase('advancedmarketplace.listing_successfully_added');
                    return array(
                        'result' => 1,
                        'error_code' => 0,
                        'message' => $sMessage,
                        'iListingId' => $iId,
                    );
                }                
            }            
        }

        return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
    }

    public function edit($aData){
        return $this->create($aData);
    }    

    public function formsearch($aData){
        $response  =  array(
            'aCategory'=> $this->allcategories($aData),
            'aLocation'=> Phpfox::getService('mfox.core')->getCountries($aData),
        );
        
        return $response;
    }    

   /**
    * @ignore 
    */
    private function __getPermission($aParams = array()) {
        $extra = array();
        return array_merge(array(
            'bCanSell' => Phpfox::getUserparam('advancedmarketplace.can_sell_items_on_advancedmarketplace'),
            'bCanCreateListing' => Phpfox::getUserparam('advancedmarketplace.can_create_listing'),
        ), $extra);        
    }

    /**
     * @ignore
     *
     */
    public function canView($iItemId){
    	
		
		
        if (!Phpfox::getUserParam('advancedmarketplace.can_access_advancedmarketplace'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_marketplace")));
        }

        if (!($aListing = Phpfox::getService('advancedmarketplace')->getForEdit($iItemId, true)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_listing_you_are_looking_for_cannot_be_found")));
        }
		
		$bIsFriend = Phpfox::getService('friend')->isFriend($aListing['user_id'], Phpfox::getUserId());

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('advancedmarketplace', $aListing['listing_id'], $aListing['user_id'], $aListing['privacy'], $bIsFriend, $bReturn = true)){
            return array('result' => 0, 'listing'=>$aListing, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        // return null - it means TRUE
        return null;
    }    

    /**
     * @ignore
     *
     */
    public function canPostComment($iItemId){
        if (!Phpfox::getUserParam('advancedmarketplace.can_access_advancedmarketplace'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_marketplace")));
        }

        if (!Phpfox::getUserParam('advancedmarketplace.can_post_comment_on_listing'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_listing")));
        }

        if (!($aListing = Phpfox::getService('advancedmarketplace')->getForEdit($iItemId, true)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_listing_you_are_looking_for_cannot_be_found")));
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('advancedmarketplace', $aListing['listing_id'], $aListing['user_id'], $aListing['privacy'], $aListing['is_friend'], $bReturn = true)){
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        if (!Phpfox::getService('comment')->canPostComment($aListing['user_id'], $aListing['privacy_comment']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_this_item")));
        }

        // return null - it means TRUE
        return null;
    }

    public function photoupload($aData){
        $iListingId = isset($aData['iListingId']) ? (int) $aData['iListingId'] : 0;
        if ($iListingId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.listing_id_is_not_valid")));
        }

        $aListing = Phpfox::getService('advancedmarketplace')->getForEdit($iListingId, true);
        if (!isset($aListing['listing_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.listing_has_been_deleted_or_you_do_not_have_permission_to_delete_it")));
        }

        // Check privacy
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('advancedmarketplace', $aListing['listing_id'], $aListing['user_id'], $aListing['privacy'], $aListing['is_friend'], $bReturn = true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        $oImage = Phpfox::getLib('image');
        $oFile = Phpfox::getLib('file');
        $aSizes = array(50, 120, 200, 400);        
        $iFileSizes = 0;

        if ($_FILES['image']['error']  == UPLOAD_ERR_OK){
            if ($aImage = $oFile->load('image', array(
                        'jpg',
                        'gif',
                        'png'
                    ), (Phpfox::getUserParam('advancedmarketplace.max_upload_size_listing') === 0 ? null : (Phpfox::getUserParam('advancedmarketplace.max_upload_size_listing') / 1024))
                )
            ){
                $sFileName = Phpfox::getLib('file')->upload('image', Phpfox::getParam('core.dir_pic') . "advancedmarketplace/", $iListingId);                
                $iFileSizes += filesize(Phpfox::getParam('core.dir_pic') . "advancedmarketplace/" . sprintf($sFileName, ''));                
                $this->database()->insert(Phpfox::getT('advancedmarketplace_image'), array('listing_id' => $iListingId, 'image_path' => $sFileName, 'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID')));     

                foreach ($aSizes as $iSize)
                {                       
                    $oImage->createThumbnail(Phpfox::getParam('core.dir_pic') . "advancedmarketplace/" . sprintf($sFileName, ''), Phpfox::getParam('core.dir_pic') . "advancedmarketplace/" . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);   
                    $oImage->createThumbnail(Phpfox::getParam('core.dir_pic') . "advancedmarketplace/" . sprintf($sFileName, ''), Phpfox::getParam('core.dir_pic') . "advancedmarketplace/" . sprintf($sFileName, '_' . $iSize . '_square'), $iSize, $iSize, false);                        
                    
                    $iFileSizes += filesize(Phpfox::getParam('core.dir_pic') . "advancedmarketplace/" . sprintf($sFileName, '_' . $iSize));         
                }                   
            } else {
                return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
            }
        } else {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.please_try_again")));
        }

        if ($iFileSizes > 0)
        {
            $this->database()->update(Phpfox::getT('advancedmarketplace'), array('image_path' => $sFileName, 'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID')), 'listing_id = ' . $iListingId);
            // Update user space usage
            Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'advancedmarketplace', $iFileSizes);          
        }

        return array('result' => 1, 'error_code' => 0, 'message'=>html_entity_decode(Phpfox::getPhrase("mfox.upload_successfully")));
    }

    public function photodelete($aData){
        $iImageId = isset($aData['iImageId']) ? (int) $aData['iImageId'] : 0;
        if (Phpfox::getService('advancedmarketplace.process')->deleteImage($iImageId))
        {
            return array('result' => 1, 'error_code' => 0, 'message'=>html_entity_decode(Phpfox::getPhrase("mfox.delete_successfully")));
        }        

        return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.please_try_again")));
    }

    /**
     * @ignore
     */
    private function __getInvitedUserIds($iListingId) {
        $aRows = $this->database()->select('invited_user_id')
             ->from(Phpfox::getT('advancedmarketplace_invite'))
             ->where('listing_id  = ' . $iListingId)
             ->execute('getRows');
        $aIds = array(Phpfox::getUserId());
        foreach($aRows as $aRow) {
            $aIds[] = $aRow['invited_user_id'];
        }

        return $aIds;   
    }    

    public function getinvitepeople($aData){
        $iListingId = isset($aData['iListingId']) ? (int) $aData['iListingId'] : 0;
        if ($iListingId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.listing_id_is_not_valid")));
        }

        $aListing = Phpfox::getService('advancedmarketplace')->getForEdit($iListingId, true);
        if (!($aListing) || !isset($aListing['listing_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('advancedmarketplace.the_listing_you_are_looking_for_either_does_not_exist_or_has_been_removed'));
        }

        $aConds =  array();
        $aInvitedIds = $this->__getInvitedUserIds($iListingId);
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
        $iListingId = isset($aData['iListingId']) ? (int) $aData['iListingId'] : 0;
        if ($iListingId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.listing_id_is_not_valid")));
        }

        $aListing = Phpfox::getService('advancedmarketplace')->getForEdit($iListingId, true);
        if (!($aListing) || !isset($aListing['listing_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('advancedmarketplace.the_listing_you_are_looking_for_either_does_not_exist_or_has_been_removed'));
        }

        // init
        $oParseInput = Phpfox::getLib('parse.input');
        $aVals = array('invite' => explode(',', $aData['sUserId']));
        // process 
        $aInvites = $this->database()->select('invited_user_id, invited_email')
            ->from(Phpfox::getT('advancedmarketplace_invite'))
            ->where('listing_id = ' . (int) $iListingId)
            ->execute('getRows');
        $aInvited = array();
        foreach ($aInvites as $aInvite)
        {
            $aInvited[(empty($aInvite['invited_email']) ? 'user' : 'email')][(empty($aInvite['invited_email']) ? $aInvite['invited_user_id'] : $aInvite['invited_email'])] = true;
        }           

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

        foreach ($aUsers as $aUser)
        {            
            if (isset($aInvited['user'][$aUser['user_id']]))
            {
                continue;
            }

            $sLink = Phpfox::getLib('url')->permalink('advancedmarketplace', $aListing['listing_id'], $aListing['title']);
            $sMessage =  Phpfox::getPhrase('advancedmarketplace.full_name_invited_you_to_view_the_advancedmarketplace_listing_title', array(
                    'full_name' => Phpfox::getUserBy('full_name'),
                    'title' => $oParseInput->clean($aListing['title'], 255),
                    'link' => $sLink
                ), false, null, $aUser['language_id']
            );
            if (!empty($aVals['personal_message']))
            {
                $sMessage .= "\n\n" .  Phpfox::getPhrase('advancedmarketplace.full_name_added_the_following_personal_message', array('full_name' => Phpfox::getUserBy('full_name')), false, null, $aUser['language_id']);
                $sMessage .= $aVals['personal_message'];
            }

            $bSent = Phpfox::getLib('mail')->to($aUser['user_id'])                      
                ->subject(array('advancedmarketplace.full_name_invited_you_to_view_the_listing_title', array('full_name' => Phpfox::getUserBy('full_name'), 'title' => $oParseInput->clean($aListing['title'], 255))))
                ->message($sMessage)
                ->notification('advancedmarketplace.new_invite')
                ->send();

            if ($bSent)
            {
                $this->database()->insert(Phpfox::getT('advancedmarketplace_invite'), array(
                        'listing_id' => $iListingId,                               
                        'user_id' => Phpfox::getUserId(),
                        'invited_user_id' => $aUser['user_id'],
                        'time_stamp' => PHPFOX_TIME
                    )
                );

                (Phpfox::isModule('request') ? Phpfox::getService('request.process')->add('advancedmarketplace_invite', $iListingId, $aUser['user_id']) : null);
            }
        }

        return array(
            'error_code' => 0,
            'result' => 1,
            'message'=>html_entity_decode(Phpfox::getPhrase("mfox.members_invited")),
            'iListingId' => $iListingId,
        );        
    }

    /**
     * @ignore
     */
    private function __isInvited($iListingId, $iUserId){
        $count = (int) $this->database()->select('COUNT(*)')
                ->from(Phpfox::getT('advancedmarketplace_invite'))
                ->where('listing_id = ' . (int) $iListingId . ' AND invited_user_id = ' . $iUserId)
                ->execute('getSlaveField');

        if($count > 0){
            return true;
        }

        return false;
    }        

    public function transactionadd($aData){
        $iListingId = isset($aData['iListingId']) ? (int) $aData['iListingId'] : 0;
        if (!($aListing = Phpfox::getService('advancedmarketplace')->getForEdit($iListingId, true)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('advancedmarketplace.the_listing_you_are_looking_for_either_does_not_exist_or_has_been_removed'));
        }
        
        if (($iInvoiceId = Phpfox::getService('advancedmarketplace.process')->addInvoice($aListing['listing_id'], $aListing['currency_id'], $aListing['price'])))
        {
            return array(
                'error_code' => 0,
                'result' => 1,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.added_invoice_successfully")),
                'iListingId' => $iListingId,
                'iInvoiceId' => $iInvoiceId,
            );        
        }

        return array(
            'error_code' => 1,
            'result' => 0,
            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.please_try_again")),
            'iListingId' => $iListingId,
        );        
    }

    public function transactionupdate($aData){
        $iInvoiceId = isset($aData['iInvoiceId']) ? (int) $aData['iInvoiceId'] : 0;
        $aInvoice = Phpfox::getService('advancedmarketplace')->getInvoice($iInvoiceId);
        if(!isset($aInvoice['listing_id'])){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_a_valid_invoice")));
        }

        $iListingId = $aInvoice['listing_id'];
        if (!($aListing = Phpfox::getService('advancedmarketplace')->getForEdit($iListingId, true)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('advancedmarketplace.the_listing_you_are_looking_for_either_does_not_exist_or_has_been_removed'));
        }

        $sStoreKidTransactionId = isset($aData['sStoreKidTransactionId']) ? $aData['sStoreKidTransactionId'] : '';
        $sPlayStoreOrderId = isset($aData['sPlayStoreOrderId']) ? $aData['sPlayStoreOrderId'] : '';
        $sDevice = isset($aData['sDevice']) ? $aData['sDevice'] : '';
        $sStatus = '';
        switch ($aData['sStatus']) {
            case 'success':
                $sStatus = 'completed';
                $this->database()->update(Phpfox::getT('advancedmarketplace_invoice'), array(
                        'status' => $sStatus,
                        'time_stamp_paid' => PHPFOX_TIME
                    ), 'invoice_id = ' . $aInvoice['invoice_id']
                );      

                if ($aListing['auto_sell'])
                {
                    $this->database()->update(Phpfox::getT('advancedmarketplace'), array(
                            'view_id' => '2'
                        ), 'listing_id = ' . $aListing['listing_id']
                    );                  
                }       

                Phpfox::getLib('mail')->to($aListing['user_id'])
                    ->subject(array('advancedmarketplace.item_sold_title', array('title' => Phpfox::getLib('parse.input')->clean($aListing['title'], 255))))
                    ->fromName($aInvoice['full_name'])
                    ->message(array('advancedmarketplace.full_name_has_purchased_an_item_of_yours_on_site_name', array(
                                'full_name' => $aInvoice['full_name'],
                                'site_name' => Phpfox::getParam('core.site_title'),
                                'title' => $aListing['title'],
                                'link' => Phpfox::getLib('url')->makeUrl('advancedmarketplace.view', $aListing['title_url']),
                                'user_link' => Phpfox::getLib('url')->makeUrl($aInvoice['user_name']),
                                'price' => Phpfox::getService('core.currency')->getCurrency($aInvoice['price'], $aInvoice['currency_id'])
                            )
                        )
                    )
                    ->send();

                $message = 'Updated invoice successfully';

                // update transaction 
                if(empty($sDevice) == false){
                    $sDevice = Phpfox::getService('mfox.helper')->changeTypeDevice($sDevice);
                    $aExtra  =  array();
                    $platform = $sDevice;
                    $transaction_item_type = Phpfox::getService("mfox.helper")->getConst("device.support." . $platform, "id");

                    $sModuleId = 'advancedmarketplace';
                    $transaction_store_kit_purchase_id = '';
                    $aStoreKitPurchase = Phpfox::getService('mfox.transaction')->getAllStoreKitPurchaseByModuleId($sModuleId, $aItem['package_id']);
                    foreach ($aStoreKitPurchase as $key => $value) {
                        if($transaction_item_type == $value['storekitpurchase_device']){
                            $transaction_store_kit_purchase_id = $value['storekitpurchase_key'];
                            break;
                        }
                    }

                    $aVals = array(
                        'transaction_method_id' => Phpfox::getService("mfox.helper")->getConst("transaction.method.inapppurchase", "id"), 
                        'extra' => serialize($aExtra), 
                        'transaction_amount' => $aInvoice['price'], 
                        'transaction_currency' => $aInvoice['currency_id'], 
                        'transaction_item_id' => $aInvoice['listing_id'], 
                        'transaction_item_type' => $transaction_item_type, 
                        'transaction_module_id' => $sModuleId, 
                        'transaction_user_id' => Phpfox::getUserId(), 
                        'transaction_store_kit_purchase_id' => $transaction_store_kit_purchase_id, 
                        'transaction_store_kit_transaction_id' => (empty($sStoreKidTransactionId) ? $sPlayStoreOrderId : $sStoreKidTransactionId), 
                    );
                    Phpfox::getService('mfox.transaction')->addTransaction($aVals);
                }                

                break;

            case 'fail':
                $sStatus = 'cancel';
                // fail (cancelled/pending)
                // $this->database()->update(Phpfox::getT('advancedmarketplace_invoice'), array(
                //         'status' => 'cancelled',
                //     ), 'invoice_id = ' . $aInvoice['invoice_id']
                // );      

                $message = 'Please try purchase again.';
                break;
            
            default:
                return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_this_status_yet")));
                break;
        }

        return array('result' => 1, 'error_code' => 0, 'message' => $message);
    }

    public function perms($aData){
        return $this->__getPermission($aData);
    }

}
