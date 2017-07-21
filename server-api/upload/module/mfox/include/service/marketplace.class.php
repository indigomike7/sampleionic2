<?php
/**
 * Service component
 *
 * @category Mobile phpfox server api
 * @author Ly Tran <lytk@younetco.com>
 * @version $Id$
 * @copyright $Copyright$
 * @license $License$
 * @package mfox.marketplace
 */

/**
 * Supported Marketplace api
 * 
 * @package mfox.marketplace
 * @author Ly Tran <lytk@younetco.com>
 */

class Mfox_Service_Marketplace extends Phpfox_Service {

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
     * @ignore
     */
    private $_bIsAdvancedMarketplaceModule = false;
	
	private $_sDefaultImagePath = null;
    
    private $_sCategory = null;
    
    private $_bIsSeen = false;

    /**
     * @ignore
     */
    public function __construct()
    {
        $this->_oReq = Mfox_Service_Request_Request::instance();
        $this->_oSearch = Mfox_Service_Search_Search::instance();
        $this->_oBrowse = Mfox_Service_Search_Browse::instance();

        $this->_sTable = Phpfox::getT('marketplace');

        $isUsing = Phpfox::getParam('mfox.replace_marketplace');
        $isAdv = Phpfox::isModule('advancedmarketplace');
        $isDefault = Phpfox::isModule('marketplace');
		
		$this->_sDefaultImagePath =  Phpfox::getParam('core.url_module') . 'mfox/static/image/marketplace_default.png';

        $this->_bIsAdvancedMarketplaceModule = Phpfox::getService('mfox.core')->isAdvancedModule($isUsing, $isAdv, $isDefault);
    }    
	
	function getDefaultImagePath(){
		return $this->_sDefaultImagePath;
	}

    /**
     * @ignore
     */
    public function isAdvancedModule(){
        return $this->_bIsAdvancedMarketplaceModule;
    }

    /**
     * Get/search list of marketplaces.
     * 
     * Request options: 
     * - iPage:          integer, starting from 1
     * - iAmountOfMarketplace:         integer, default is 10
     * - sView:              string, value is <empty>/my
     * - sSearch:              string
     * - sOrder:              string, value is latest/most_liked/most_discussed
     * - iCategoryId:              integer
     * - sCountryIso:              integer
     * 
     * Response data contains: 
     * <code>
     * <br/>  [
     * <br/>      {
     * <br/>           "bAutoSell": true,
     * <br/>           "sLocation": "",
     * <br/>           "sCity": null,
     * <br/>           "iCountryChildId": "0",
     * <br/>           "sCountryIso": "VN",
     * <br/>           "sCountry": "Viet Nam",
     * <br/>           "sCountryChild": "",
     * <br/>           "sCurrencyId": "EUR",
     * <br/>           "sSymbol": "&#8364;",
     * <br/>           "sDescription": null,
     * <br/>           "iGroupId": "0",
     * <br/>           "bIsClosed": false,
     * <br/>           "bIsFeatured": false,
     * <br/>           "bIsLiked": false,
     * <br/>           "bIsNotified": false,
     * <br/>           "bIsSell": true,
     * <br/>           "bIsSponsor": false,
     * <br/>           "iListingId": "33",
     * <br/>           "sMiniDescription": null,
     * <br/>           "sPostalCode": null,
     * <br/>           "sPrice": "5.00",
     * <br/>           "iPrivacy": "0",
     * <br/>           "iPrivacyComment": "0",
     * <br/>           "iTimeStamp": "1405069101",
     * <br/>           "sTitle": "Euros",
     * <br/>           "iTotalComment": "1",
     * <br/>           "iTotalLike": "0",
     * <br/>           "iTotalDislike": "0",
     * <br/>           "iViewId": "0",
     * <br/>           "sMarketplaceImage": "http://product-dev.younetco.com/lytk/phpfox376/theme/frontend/default/style/default/image/noimage/item.png",
     * <br/>           "bIsPending": false,
     * <br/>           "bCanComment": true,
     * <br/>           "bCanSendMessage": true,
     * <br/>           "iUserId": "1",
     * <br/>           "sUserImage": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/user/1_50_square.jpg",
     * <br/>           "sUserName": "admin",
     * <br/>           "sFullname": "Admin",
     * <br/>           "sModelType": "marketplace"
     * <br/>      }
     * <br/>  ]
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function fetch($aData) {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->fetch($aData);
        }

        return $this -> getMarketplaces($aData);
    }

    /**
     * @ignore
     */
    public function getMarketplaces($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->getMarketplaces($aData);
        }

        $this->_oReq->set(array(
            'view' => !empty($aData['sView']) ? $aData['sView'] : '',
            'page' => !empty($aData['iPage']) ? (int) $aData['iPage'] : 1,
            'show' => !empty($aData['iAmountOfMarketplace']) ? (int) $aData['iAmountOfMarketplace'] : 10,
            'category' => !empty($aData['iCategoryId']) ? (int) $aData['iCategoryId'] : null,
            'search' => !empty($aData['sSearch']) ? $aData['sSearch'] : '',
            'sort' => !empty($aData['sOrder']) ? $aData['sOrder'] : '',
            'module_id' => !empty($aData['sModule']) ? $aData['sModule'] : null,
            'item_id' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : null,
            'profile' => (!empty($aData['bIsProfile']) && $aData['bIsProfile'] == 'true') ? true : false,
            'profile_id' => !empty($aData['iProfileId']) ? (int) $aData['iProfileId'] : null,
            'location' => !empty($aData['sCountryIso']) ? $aData['sCountryIso'] : null,
        ));

        Phpfox::getUserParam('marketplace.can_access_marketplace', true);

        $bIsProfile = false;
        if ($this->_oReq->get('profile') === true)
        {
            $bIsProfile = true;
            $aUser = Phpfox::getService('user')->get($this->_oReq->get('profile_id'));
        }

        $oServiceMarketplaceBrowse = $this;
        $sCategoryUrl = null;
        $sView = $this->_oReq->get('view');

        // search
        $sSearch = $this->_oReq->get('search');
        if (!empty($sSearch))
        {
            $this->_oSearch->setCondition('AND (' 
                . ' l.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"'
                . ' OR mt.description_parsed LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"' 
                . ')');
        }

        // sort
        $sSort = '';
        switch ($this->_oReq->get('sort')) {
            case 'most_liked':
                $sSort = 'l.is_sponsor DESC, l.total_like DESC';
                break;
            case 'most_discussed':
                $sSort = 'l.is_sponsor DESC, l.total_comment DESC';
                break;
            default:
                $sSort = 'l.is_sponsor DESC, l.time_stamp DESC';
                break;
        }

        $this->_oSearch->setSort($sSort);

        $aBrowseParams = array(
            'module_id' => 'marketplace',
            'alias' => 'l',
            'field' => 'listing_id',
            'table' => Phpfox::getT('marketplace'),
            'hide_view' => array('pending', 'my'),
            'service' => 'mfox.marketplace'
        );          
        
        // http://www.phpfox.com/tracker/view/14708/
        if(Phpfox::getParam('core.section_privacy_item_browsing'))
        {
            $aBrowseParams['join'] = array(
                'alias' => 'mt',
                'field' => 'listing_id',
                'table' => Phpfox::getT('marketplace_text')             
            );
        }
        
        switch ($sView)
        {
            case 'sold':
                Phpfox::isUser(true);               
                $this->_oSearch->setCondition('AND l.user_id = ' . Phpfox::getUserId());                
                $this->_oSearch->setCondition('AND l.is_sell = 1');
                
                break;
            case 'featured':
                $this->_oSearch->setCondition('AND l.is_featured = 1');
                break;
            case 'my':
                Phpfox::isUser(true);               
                $this->_oSearch->setCondition('AND l.user_id = ' . Phpfox::getUserId());                
                break;
            case 'pending':
                if (Phpfox::getUserParam('marketplace.can_approve_listings'))
                {
                    $this->_oSearch->setCondition('AND l.view_id = 1');
                }
                break;
            case 'expired':
                if (Phpfox::getParam('marketplace.days_to_expire_listing') > 0 && Phpfox::getUserParam('marketplace.can_view_expired'))
                {
                    $iExpireTime = (PHPFOX_TIME - (Phpfox::getParam('marketplace.days_to_expire_listing') * 86400));
                    $this->_oSearch->setCondition('AND l.time_stamp < ' . $iExpireTime);
                    break;
                }
            case 'invoice':
                $this->url()->send('marketplace.invoice');
                break;
            default:
                if ($bIsProfile === true)
                {
                    $this->_oSearch->setCondition("AND l.view_id IN(" . ($aUser['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ") AND l.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ") AND l.user_id = " . $aUser['user_id'] . "");  
                }
                else
                {                   
                    switch ($sView)
                    {
                        case 'invites':
                            Phpfox::isUser(true);
                            $oServiceMarketplaceBrowse->seen();                             
                            break;
                    }
                    
                    $this->_oSearch->setCondition('AND l.view_id = 0 AND l.privacy IN(%PRIVACY%)');
                }
                break;
        }       
        
        if (($sLocation = $this->_oReq->get('location')))
        {
            $this->_oSearch->setCondition('AND l.country_iso = \'' . Phpfox_Database::instance()->escape($sLocation) . '\'');
        }
        
        if ($this->_oReq->get('category', null) !== null)
        {
            $sCategoryUrl = $this->_oReq->getInt('category');
            $this->_oSearch->setCondition('AND mcd.category_id = ' . (int) $sCategoryUrl);
        }       

        $oServiceMarketplaceBrowse->category($sCategoryUrl);    
            
        if (Phpfox::getParam('marketplace.days_to_expire_listing') > 0 && $sView != 'my' && $sView != 'expired')
        {
            $iExpireTime = (PHPFOX_TIME - (Phpfox::getParam('marketplace.days_to_expire_listing') * 86400));
            $this->_oSearch->setCondition(' AND l.time_stamp >=' . $iExpireTime );
        }

        $this->_oBrowse->params($aBrowseParams)->execute();

        $aRows = $this->_oBrowse->getRows();

        return $aRows;
    }

    public function processRows(&$aRows)
    {
        $aTmpRows = $aRows;
        $aRows = array();

        foreach ($aTmpRows as $aRow) {
            $aRows[] = $this->__getMarketplaceData($aRow, 'small');
        }
    }

    public function seen()
    {
        $this->_bIsSeen = true;
        
        return $this;
    }
        
    public function category($sCategory)
    {
        $this->_sCategory = $sCategory;
        
        return $this;
    }

    public function query()
    {
        $this->database()->select('l.country_iso as item_country_iso, l.country_child_id as item_country_child_id, ');

        $this->database()->select('mt.description_parsed AS description, ')->join(Phpfox::getT('marketplace_text'), 'mt', 'mt.listing_id = l.listing_id');

        if (Phpfox::isUser() && Phpfox::isModule('like'))
        {
            $this->database()->select('lik.like_id AS is_liked, ')
                    ->leftJoin(Phpfox::getT('like'), 'lik', 'lik.type_id = \'marketplace\' AND lik.item_id = l.listing_id AND lik.user_id = ' . Phpfox::getUserId());
        }
    }   
    
    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Mfox_Service_Friend::instance()->queryJoin($bNoQueryFriend))
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = l.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());  
        }               

        if ($this->_sCategory !== null)
        {       
            $this->database()->select('mc.name AS category_name, ')
                ->innerJoin(Phpfox::getT('marketplace_category_data'), 'mcd', 'mcd.listing_id = l.listing_id')
                ->join(Phpfox::getT('marketplace_category'), 'mc', 'mc.category_id = mcd.category_id');
            
            if (!$bIsCount)
            {
                $this->database()->group('l.listing_id');
            }
        }
        else
        {
            $this->database()
                ->select('mc.name AS category_name, ')
                ->leftJoin(Phpfox::getT('marketplace_category_data'), 'mcd', 'mcd.listing_id = l.listing_id')
                ->leftJoin(Phpfox::getT('marketplace_category'), 'mc', 'mc.category_id = mcd.category_id')
                ->group('l.listing_id');
        }

        if ($this->_bIsSeen !== false)
        {
            $this->database()->join(Phpfox::getT('marketplace_invite'), 'mi', 'mi.listing_id = l.listing_id AND mi.visited_id = 0 AND mi.invited_user_id = ' . Phpfox::getUserId());
        }       
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
    public function getCategories($aParams = array()){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->getCategories($aParams);
        }

        $iParentCategoryId = isset($aParams['iParentCategoryId']) ?  (int)$aParams['iParentCategoryId'] : 0;
        
        return Phpfox::getService('marketplace.category')->getForBrowse($iParentCategoryId);
    }

    /**
     * List of categories (return ONLY child categories if parent id exists)
     * 
     * Request options: 
     * - iParentCategoryId:              integer
     * 
     * Response data contains: 
     * <code>
     * <br/>  [
     * <br/>       {
     * <br/>            "category_id": "16",
     * <br/>            "name": "1.1",
     * <br/>            "url": "http://product-dev.younetco.com/lytk/phpfox376/index.php?do=/marketplace/category/16/1-1/",
     * <br/>            "sub": [
     * <br/>                 {
     * <br/>                      "category_id": "17",
     * <br/>                      "name": "1.1.1",
     * <br/>                      "url": "http://product-dev.younetco.com/lytk/phpfox376/index.php?do=/marketplace/category/17/1-1-1/"
     * <br/>                 }
     * <br/>            ]
     * <br/>       },
     * <br/>  ]
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function categories($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->categories($aData);
        }
        
        return $this->getCategories($aData);
    }

   
    /**
     * @ignore
     */
    private function __getCategories($aItem = array()){
        $aSubForum = array();
        $aItem['sub'] = $this->getCategories(array('iParentCategoryId' => $aItem['category_id']));
        foreach($aItem['sub'] as $cat){
            $aSubForum[] = $this->__getCategories($cat);
        }

        $name = '';
        if (phpfox::isPhrase($aItem['name']))
            $name = Phpfox::getService('mfox')->decodeUtf8Compat(_p($aItem['name']));
        else
            $name = html_entity_decode(Phpfox::getLib('locale')->convert($aItem['name']));

        return array(
            'category_id' => $aItem['category_id'],
            'name' => $name,
            'sub' => $aSubForum,
        );
    }

    /**
     * Get configuration for adding form
     * 
     * Request options: nothing
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "view_options": [
     * <br/>            {
     * <br/>                 * <br/>  "sPhrase": "Everyone",
     * <br/>                 * <br/>  "sValue": "0"
     * <br/>            }
     * <br/>       ],
     * <br/>       "comment_options": [
     * <br/>            {
     * <br/>                 * <br/>  "sPhrase": "Everyone",
     * <br/>                 * <br/>  "sValue": "0"
     * <br/>            }
     * <br/>       ],
     * <br/>       "category_options": [
     * <br/>            {
     * <br/>                 "category_id": "16",
     * <br/>                 "name": "1.1",
     * <br/>                 "sub": [
     * <br/>                      {
     * <br/>                           "category_id": "17",
     * <br/>                           "name": "1.1.1",
     * <br/>                           "sub": [
     * <br/>                                {
     * <br/>                                     "category_id": "20",
     * <br/>                                     "name": "1.1.1.1",
     * <br/>                                     "sub": []
     * <br/>                                }
     * <br/>                           ]
     * <br/>                      }
     * <br/>                 ]
     * <br/>            }
     * <br/>       ],
     * <br/>       "currency_options": [
     * <br/>            {
     * <br/>                 "symbol": "&#36;",
     * <br/>                 "name": "U.S. Dollars",
     * <br/>                 "is_default": "1",
     * <br/>                 "currency_id": "USD"
     * <br/>            }
     * <br/>       ],
     * <br/>       "country_options": [
     * <br/>            {
     * <br/>                 "country_iso": "AF",
     * <br/>                 "name": "Afghanistan"
     * <br/>            }        
     * <br/>       ],
     * <br/>       "perms": {
     * <br/>            "bCanSell": true,
     * <br/>            "bCanCreateListing": true
     * <br/>       }
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function formadd($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->formadd($aData);
        }
		        
        $response  =  array(
            'view_options'=> Phpfox::getService('mfox.privacy')->privacy($aData),
            'comment_options'=> Phpfox::getService('mfox.privacy')->privacycomment($aData),
            'category_options'=> $this->allcategories($aData),
            'currency_options'=> Phpfox::getService('mfox.core')->getcurrencies($aData),
            'country_options'=> Phpfox::getService('mfox.core')->getCountries($aData),
            'perms'=> $this->__getPermission($aData),
        );

        $iValue = Phpfox::getService('user.privacy')->getValue('marketplace.display_on_profile');
        $response['default_privacy_setting'] = Phpfox::getService('mfox.privacy')->defaultprivacy("$iValue", $response['view_options']);
        
        return $response;
    }    

    public function getListing($iId)
    {
        (($sPlugin = Phpfox_Plugin::get('marketplace.service_marketplace_getlisting')) ? eval($sPlugin) : false);
        
        if (Phpfox::isModule('like'))
        {
            $this->database()->select('lik.like_id AS is_liked, ')
                    ->leftJoin(Phpfox::getT('like'), 'lik', 'lik.type_id = \'marketplace\' AND lik.item_id = l.listing_id AND lik.user_id = ' . Phpfox::getUserId());
        }   
        
        $this->database()->select('f.friend_id AS is_friend, ')->leftJoin(Phpfox::getT('friend'), 'f', "f.user_id = l.user_id AND f.friend_user_id = " . Phpfox::getUserId());
        
        $aListing = $this->database()->select('l.*, l.country_iso as item_country_iso, l.country_child_id as item_country_child_id, ml.invite_id, ml.visited_id, uf.total_score, uf.total_rating, ua.activity_points, ' . (Phpfox::getParam('core.allow_html') ? 'mt.description_parsed' : 'mt.description') . ' AS description, ' . Phpfox::getUserField())
            ->from($this->_sTable, 'l')
            ->join(Phpfox::getT('marketplace_text'), 'mt', 'mt.listing_id = l.listing_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
            ->join(Phpfox::getT('user_field'), 'uf', 'uf.user_id = l.user_id')
            ->join(Phpfox::getT('user_activity'), 'ua', 'ua.user_id = l.user_id')
            ->leftJoin(Phpfox::getT('marketplace_invite'), 'ml', 'ml.listing_id = l.listing_id AND ml.invited_user_id = ' . Phpfox::getUserId())
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
            if ($aListing['user_id'] == Phpfox::getUserId() || Phpfox::getUserParam('marketplace.can_approve_listings'))
            {
                
            }
            else 
            {
                return false;
            }
        }       
            
        $aListing['categories'] = Phpfox::getService('marketplace.category')->getCategoriesById($aListing['listing_id']);
        $aListing['bookmark_url'] = Phpfox_Url::instance()->permalink('marketplace', $aListing['listing_id'], $aListing['title']);
        
        return $aListing;
    }

    /**
     * Detail of marketplace
     * 
     * Request options: 
     * - iListingId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "bAutoSell": false,
     * <br/>       "sLocation": "",
     * <br/>       "sCity": "Ho Chi Minh",
     * <br/>       "iCountryChildId": "0",
     * <br/>       "sCountryIso": "VN",
     * <br/>       "sCountry": "Viet Nam",
     * <br/>       "sCountryChild": "",
     * <br/>       "sCurrencyId": "USD",
     * <br/>       "sSymbol": "&#36;",
     * <br/>       "sDescription": "Thi&#7871;t k&#7871; c&#7911;a G3 l&#224; b&#432;&#7899;c c&#7843;i ti&#7871;n m&#7899;i t&#7915; G2 tuy v&#7851;n gi&#7919; nh&#7919;ng &#273;&#7863;c t&#237;nh c&#417; b&#7843;n nh&#432; v&#7883; tr&#237; ph&#237;m ngu&#7891;n, ph&#237;m &#226;m l&#432;&#7907;ng &#7903; m&#7863;t sau thay v&#236; &#7903; 2 c&#7841;nh nh&#432; c&#225;c s&#7843;n ph&#7849;m truy&#7873;n th&#7889;ng. Thi&#7871;t k&#7871; th&#7901;i th&#432;&#7907;ng n&#224;y gi&#250;p ng&#432;&#7901;i d&#249;ng thao t&#225;c d&#7877; d&#224;ng &#273;&#7889;i v&#7899;i nh&#7919;ng smartphone m&#224;n h&#236;nh l&#7899;n nh&#432; LG G3 d&#249; d&#249;ng tay tr&#225;i hay tay ph&#7843;i.",
     * <br/>       "iGroupId": "0",
     * <br/>       "bIsClosed": false,
     * <br/>       "bIsFeatured": false,
     * <br/>       "bIsLiked": false,
     * <br/>       "bIsNotified": false,
     * <br/>       "bIsSell": true,
     * <br/>       "bIsSponsor": false,
     * <br/>       "iListingId": "2",
     * <br/>       "sMiniDescription": "Cu&#7889;i c&#249;ng, sau bao ch&#7901; &#273;&#7907;i v&#224; &#273;&#7891;n &#273;o&#225;n, LG G3 &#273;&#227; ch&#237;nh th&#7913;c ra m&#7855;t t&#7841;i s&#7921; ki&#7879;n &#273;&#7863;c bi&#7879;t c&#7911;a h&#227;ng t&#7841;i London v&#224;o th",
     * <br/>       "sPostalCode": "12345",
     * <br/>       "sPrice": "750.00",
     * <br/>       "iPrivacy": "0",
     * <br/>       "iPrivacyComment": "0",
     * <br/>       "iTimeStamp": "1404371547",
     * <br/>       "sTitle": "LG G3 D855 32GB",
     * <br/>       "iTotalComment": "1",
     * <br/>       "iTotalLike": "1",
     * <br/>       "iTotalDislike": "0",
     * <br/>       "iViewId": "0",
     * <br/>       "sMarketplaceImage": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/marketplace/2014/07/0f8f76ee37e63f3a270887eef0fee606.jpg",
     * <br/>       "bIsPending": false,
     * <br/>       "bCanComment": true,
     * <br/>       "bCanSendMessage": true,
     * <br/>       "iUserId": "4",
     * <br/>       "sUserImage": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/user/2014/06/f20c7e5e8a2693d0ba9fb469960c029b_50_square.jpg",
     * <br/>       "sUserName": "profile-4",
     * <br/>       "sFullname": "An Nguyen An Nguyen",
     * <br/>       "sModelType": "marketplace",
     * <br/>       "iActivityPoints": "16947",
     * <br/>       "iInviteId": null,
     * <br/>       "bIsFriend": true,
     * <br/>       "iTotalRating": "0",
     * <br/>       "iTotalScore": "0.00",
     * <br/>       "iUserGroupId": "2",
     * <br/>       "iVisitedId": null,
     * <br/>       "aCategoriesData": [
     * <br/>            [
     * <br/>                 "Community",
     * <br/>                 "http://product-dev.younetco.com/lytk/phpfox376/index.php?do=/marketplace/category/1/community/"
     * <br/>            ]
     * <br/>       ],
     * <br/>       "aCategoriesId": [
     * <br/>            "1"
     * <br/>       ],
     * <br/>       "aImages": [
     * <br/>            {
     * <br/>                 "iImageId": "2",
     * <br/>                 "sImagePath": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/marketplace/2014/07/f13ffe2511f543dda17deed52fde8a9a.jpg"
     * <br/>            }
     * <br/>       ],
     * <br/>       "bIsAllowBuyInApp": true,
     * <br/>       "bIsShowBuyInFullSite": true,
     * <br/>       "sFullSiteUrl": "http://product-dev.younetco.com/lytk/phpfox376/index.php?do=/marketplace/2/lg-g3-d855-32gb/",
     * <br/>       "aStoreKitPurchaseId": {
     * <br/>            "iphone": "com.younetco.phpfox.dollar1",
     * <br/>            "ipad": "com.younetco.phpfox.dollar2"
     * <br/>       }
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */    
    public function detail($aData){
    	
        if($aData['sModelType'] == 'advancedmarketplace' || ($aData['sModelType'] == '' && $this->isAdvancedModule())){
            return Phpfox::getService('mfox.advancedmarketplace')->detail($aData);
        }
		
        
        $iListingId = isset($aData['iListingId']) ? (int) $aData['iListingId'] : 0;
        if (!Phpfox::getUserParam('marketplace.can_access_marketplace'))
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
                'error_message' =>  Phpfox::getPhrase('marketplace.the_listing_you_are_looking_for_either_does_not_exist_or_has_been_removed')
            );            
        }

        if (Phpfox::isUser() && $aListing['invite_id'] && !$aListing['visited_id'] && $aListing['user_id'] != Phpfox::getUserId())
        {
            Phpfox::getService('marketplace.process')->setVisit($aListing['listing_id'], Phpfox::getUserId());
        }       
        
        if (Phpfox::isUser() && Phpfox::isModule('notification'))
        {
            Phpfox::getService('notification.process')->delete('comment_marketplace', $this->request()->getInt('req2'), Phpfox::getUserId());
            Phpfox::getService('notification.process')->delete('marketplace_like', $this->request()->getInt('req2'), Phpfox::getUserId());
        }
        
        if (Phpfox::isModule('notification') && $aListing['user_id'] == Phpfox::getUserId())
        {
            Phpfox::getService('notification.process')->delete('marketplace_approved', $aListing['listing_id'], Phpfox::getUserId());
        }   

        $bCanView = false;
        if(Phpfox::getService('privacy')->check('marketplace', $aListing['listing_id'], $aListing['user_id'], $aListing['privacy'], $aListing['is_friend'], $bReturn = true)){
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
     * @ignore
     */
    private function __getMarketplaceData($aItem, $sMoreInfo = 'large'){
        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aItem, '_50_square');
        
		if ($aItem['image_path']){
			$sMarketplaceImage = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aItem['server_id'],
                'path' => 'core.url_pic',
                'file' => 'marketplace/' . $aItem['image_path'],
                'suffix' => '',
                'return_url' => true
                    )
            );	
		}else{
			$sMarketplaceImage = $this->getDefaultImagePath();
		}
		
        // if (!file_exists(Phpfox::getParam('marketplace.dir_image') . sprintf($aItem['image_path'], '')) || is_dir(Phpfox::getParam('marketplace.dir_image') . sprintf($aItem['image_path'], ''))){
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
			
	    $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser(
            'marketplace'
            , $aItem['listing_id']
            , false
            , 999999
        );
        $aUserLike = array();
        foreach($aLike['likes'] as $like){
            $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
        }
		   
			
		$bCanEdit  = 0;
		$bCanDelete = 0;
		$bIsOwner  = Phpfox::getUserId() == $aItem['user_id'];  
		
		if (($bIsOwner && Phpfox::getUserParam('marketplace.can_edit_own_listing'))
		|| (!$bIsOwner && Phpfox::getUserParam('marketplace.can_edit_other_listing'))){
			$bCanEdit =  1;
		}
		
		if (($bIsOwner && Phpfox::getUserParam('marketplace.can_delete_own_listing'))
		|| (!$bIsOwner && Phpfox::getUserParam('marketplace.can_delete_other_listings'))){
			$bCanDelete =  1;
		}	

        $result = array(
                    'bAutoSell' => ((int)$aItem['auto_sell'] > 0 ? true : false),
                    'sLocation' => isset($aItem['location']) ? $aItem['location'] : '',
                    'sCity'=>$aItem['city'],
                    'iCountryChildId'=>$aItem['item_country_child_id'],
                    'sCountryIso'=>$aItem['item_country_iso'],
                    'sCountry'=> Phpfox::getService('core.country')->getCountry($aItem['item_country_iso']),
                    'sCountryChild'=> Phpfox::getService('core.country')->getChild($aItem['item_country_child_id']),
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
                    'sMiniDescription'=> $aItem['mini_description'],
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
					'bHasImage'=> $aItem['image_path']?1:0,
                    'sModelType' => 'marketplace',
                    'aLikes' => $aUserLike,
                    ); 

        switch ($sMoreInfo) {
            case 'large':
                $aImages = $this->__getImages($aItem['listing_id']);
                $aTemp = Phpfox::getService('marketplace')->getForEdit($aItem['listing_id'], true);
                $sFullSiteUrl = Phpfox::getLib('url')->permalink('marketplace', $aItem['listing_id'], $aItem['title']);

                $sModuleId = 'marketplace';
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
        $aImages = Phpfox::getService('marketplace')->getImages($iListingId);
        $result = array();
        foreach ($aImages as $key => $value) {
            $sMarketplaceImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $value['server_id'],
                    'path' => 'marketplace.url_image',
                    'file' => $value['image_path'],
                    'suffix' => '',
                    'return_url' => true
                        )
                );

            $result[] = array('iImageId' => $value['image_id'], 'sImagePath' => $sMarketplaceImage);
        }

        return $result;
    }

    /**
     * Get configuration for editing form
     * 
     * Request options: 
     * - iListingId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>  "detail": {
     * <br/>       "bAutoSell": false,
     * <br/>       "sLocation": "",
     * <br/>       "sCity": "Ho Chi Minh",
     * <br/>       "iCountryChildId": "0",
     * <br/>       "sCountryIso": "VN",
     * <br/>       "sCountry": "Viet Nam",
     * <br/>       "sCountryChild": "",
     * <br/>       "sCurrencyId": "USD",
     * <br/>       "sSymbol": "&#36;",
     * <br/>       "sDescription": "Thi&#7871;t k&#7871; c&#7911;a G3 l&#224; b&#432;&#7899;c c&#7843;i ti&#7871;n m&#7899;i t&#7915; G2 tuy v&#7851;n gi&#7919; nh&#7919;ng &#273;&#7863;c t&#237;nh c&#417; b&#7843;n nh&#432; v&#7883; tr&#237; ph&#237;m ngu&#7891;n, ph&#237;m &#226;m l&#432;&#7907;ng &#7903; m&#7863;t sau thay v&#236; &#7903; 2 c&#7841;nh nh&#432; c&#225;c s&#7843;n ph&#7849;m truy&#7873;n th&#7889;ng. Thi&#7871;t k&#7871; th&#7901;i th&#432;&#7907;ng n&#224;y gi&#250;p ng&#432;&#7901;i d&#249;ng thao t&#225;c d&#7877; d&#224;ng &#273;&#7889;i v&#7899;i nh&#7919;ng smartphone m&#224;n h&#236;nh l&#7899;n nh&#432; LG G3 d&#249; d&#249;ng tay tr&#225;i hay tay ph&#7843;i.",
     * <br/>       "iGroupId": "0",
     * <br/>       "bIsClosed": false,
     * <br/>       "bIsFeatured": false,
     * <br/>       "bIsLiked": false,
     * <br/>       "bIsNotified": false,
     * <br/>       "bIsSell": true,
     * <br/>       "bIsSponsor": false,
     * <br/>       "iListingId": "2",
     * <br/>       "sMiniDescription": "Cu&#7889;i c&#249;ng, sau bao ch&#7901; &#273;&#7907;i v&#224; &#273;&#7891;n &#273;o&#225;n, LG G3 &#273;&#227; ch&#237;nh th&#7913;c ra m&#7855;t t&#7841;i s&#7921; ki&#7879;n &#273;&#7863;c bi&#7879;t c&#7911;a h&#227;ng t&#7841;i London v&#224;o th",
     * <br/>       "sPostalCode": "12345",
     * <br/>       "sPrice": "750.00",
     * <br/>       "iPrivacy": "0",
     * <br/>       "iPrivacyComment": "0",
     * <br/>       "iTimeStamp": "1404371547",
     * <br/>       "sTitle": "LG G3 D855 32GB",
     * <br/>       "iTotalComment": "1",
     * <br/>       "iTotalLike": "1",
     * <br/>       "iTotalDislike": "0",
     * <br/>       "iViewId": "0",
     * <br/>       "sMarketplaceImage": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/marketplace/2014/07/0f8f76ee37e63f3a270887eef0fee606.jpg",
     * <br/>       "bIsPending": false,
     * <br/>       "bCanComment": true,
     * <br/>       "bCanSendMessage": true,
     * <br/>       "iUserId": "4",
     * <br/>       "sUserImage": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/user/2014/06/f20c7e5e8a2693d0ba9fb469960c029b_50_square.jpg",
     * <br/>       "sUserName": "profile-4",
     * <br/>       "sFullname": "An Nguyen An Nguyen",
     * <br/>       "sModelType": "marketplace",
     * <br/>       "iActivityPoints": "16947",
     * <br/>       "iInviteId": null,
     * <br/>       "bIsFriend": true,
     * <br/>       "iTotalRating": "0",
     * <br/>       "iTotalScore": "0.00",
     * <br/>       "iUserGroupId": "2",
     * <br/>       "iVisitedId": null,
     * <br/>       "aCategoriesData": [
     * <br/>            [
     * <br/>                 "Community",
     * <br/>                 "http://product-dev.younetco.com/lytk/phpfox376/index.php?do=/marketplace/category/1/community/"
     * <br/>            ]
     * <br/>       ],
     * <br/>       "aCategoriesId": [
     * <br/>            "1"
     * <br/>       ],
     * <br/>       "aImages": [
     * <br/>            {
     * <br/>                 "iImageId": "2",
     * <br/>                 "sImagePath": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/marketplace/2014/07/f13ffe2511f543dda17deed52fde8a9a.jpg"
     * <br/>            }
     * <br/>       ],
     * <br/>       "bIsAllowBuyInApp": true,
     * <br/>       "bIsShowBuyInFullSite": true,
     * <br/>       "sFullSiteUrl": "http://product-dev.younetco.com/lytk/phpfox376/index.php?do=/marketplace/2/lg-g3-d855-32gb/",
     * <br/>       "aStoreKitPurchaseId": {
     * <br/>            "iphone": "com.younetco.phpfox.dollar1",
     * <br/>            "ipad": "com.younetco.phpfox.dollar2"
     * <br/>       }
     * <br/>  },
     * <br/>       "view_options": [
     * <br/>            {
     * <br/>                 * <br/>  "sPhrase": "Everyone",
     * <br/>                 * <br/>  "sValue": "0"
     * <br/>            }
     * <br/>       ],
     * <br/>       "comment_options": [
     * <br/>            {
     * <br/>                 * <br/>  "sPhrase": "Everyone",
     * <br/>                 * <br/>  "sValue": "0"
     * <br/>            }
     * <br/>       ],
     * <br/>       "category_options": [
     * <br/>            {
     * <br/>                 "category_id": "16",
     * <br/>                 "name": "1.1",
     * <br/>                 "sub": [
     * <br/>                      {
     * <br/>                           "category_id": "17",
     * <br/>                           "name": "1.1.1",
     * <br/>                           "sub": [
     * <br/>                                {
     * <br/>                                     "category_id": "20",
     * <br/>                                     "name": "1.1.1.1",
     * <br/>                                     "sub": []
     * <br/>                                }
     * <br/>                           ]
     * <br/>                      }
     * <br/>                 ]
     * <br/>            }
     * <br/>       ],
     * <br/>       "currency_options": [
     * <br/>            {
     * <br/>                 "symbol": "&#36;",
     * <br/>                 "name": "U.S. Dollars",
     * <br/>                 "is_default": "1",
     * <br/>                 "currency_id": "USD"
     * <br/>            }
     * <br/>       ],
     * <br/>       "country_options": [
     * <br/>            {
     * <br/>                 "country_iso": "AF",
     * <br/>                 "name": "Afghanistan"
     * <br/>            }        
     * <br/>       ],
     * <br/>       "perms": {
     * <br/>            "bCanSell": true,
     * <br/>            "bCanCreateListing": true
     * <br/>       }
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */    
    public function formedit($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->formedit($aData);
        }
        
        return array_merge( 
            $this->detail($aData), 
            $this->formadd($aData)
        );
    }   

    /**
     * Delete marketplace
     * 
     * Request options: 
     * - iListingId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       'message': "Listing successfully deleted"
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */   
    public function delete($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->delete($aData);
        }
        
        $iListingId = isset($aData['iListingId']) ? (int) $aData['iListingId'] : 0;
        if ($iListingId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.listing_id_is_not_valid")));
        }

        $aListing = Phpfox::getService('marketplace')->getForEdit($iListingId, true);
        if (!isset($aListing['listing_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.listing_has_been_deleted_or_you_do_not_have_permission_to_delete_it")));
        }

        // Check privacy
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('marketplace', $aListing['listing_id'], $aListing['user_id'], $aListing['privacy'], $aListing['is_friend'], $bReturn = true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        $mResult = Phpfox::getService('marketplace.process')->delete($iListingId);
        if ($mResult !== false)
        {
            return array('result' => 1
                , 'error_code' => 0
                , 'message' =>  Phpfox::getPhrase('marketplace.listing_successfully_deleted')
            );
        }

        return array('result' => 0
            , 'error_code' => 1
            , 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.listing_has_been_deleted_or_you_do_not_have_permission_to_delete_it"))
        );
    }     

    /**
     * Add new marketplace
     * 
     * Request options: 
     * - sTitle:              string, required
     * - sCountryIso:              string, required
     * - sPrice:              string, required
     * - aCategoryId:              array
     * - sMiniDescription:              string
     * - sDescription:              string
     * - sCurrencyId:              string, default is USD
     * - iIsSell:              integer, default is 0
     * - iAutoSell:              integer, default is 0 
     * - iCountryChildId:              integer
     * - sCity:              string
     * - sPostalCode:              string
     * - iPrivacy:              integer, default is 0
     * - iPrivacyComment:              integer, default is 0
     * - sEmails:              string
     * - sPersonalMessage:              string
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       "iListingId": 229194,
     * <br/>       'message': "Listing successfully added."
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */       
    public function create($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->create($aData);
        }
        
        if (!Phpfox::getUserParam('marketplace.can_create_listing'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_create_new_listing")));
        }

        $sTitle = isset($aData['sTitle']) ? $aData['sTitle'] : '';
        if(strlen(trim($sTitle)) == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('marketplace.provide_a_name_for_this_listing'));
        }        
        $sCountryIso = isset($aData['sCountryIso']) ? $aData['sCountryIso'] : '';
        if(strlen(trim($sTitle)) == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('marketplace.provide_a_location_for_this_listing'));
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
            if (($aListing = Phpfox::getService('marketplace')->getForEdit($iEditId, true)))
            {
                if (($aListing['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('marketplace.can_edit_own_listing')) 
                    || Phpfox::getUserParam('marketplace.can_edit_other_listing'))
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
            'mini_description' => isset($aData['sMiniDescription']) ? $aData['sMiniDescription'] : '', 
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
        );

        if ($bIsEdit){
            if (Phpfox::getService('marketplace.process')->update($aListing['listing_id'], $aVals))
            {
                $sMessage =  Phpfox::getPhrase('marketplace.listing_successfully_updated');
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'message' => $sMessage,
                    'iListingId' => $aListing['listing_id'],
                );                
            }
        } else {
            if (($iFlood = Phpfox::getUserParam('marketplace.flood_control_marketplace')) !== 0)
            {
                $aFlood = array(
                    'action' => 'last_post', // The SPAM action
                    'params' => array(
                        'field' => 'time_stamp', // The time stamp field
                        'table' => Phpfox::getT('marketplace'), // Database table we plan to check
                        'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                        'time_stamp' => $iFlood * 60 // Seconds);   
                    )
                );
                                
                // actually check if flooding
                if (Phpfox::getLib('spam')->check($aFlood))
                {
                    return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('marketplace.you_are_creating_a_listing_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
                }
            }                   
            if (Phpfox_Error::isPassed())
            {               
                if ($iId = Phpfox::getService('marketplace.process')->add($aVals))
                {                           
                    $sMessage =  Phpfox::getPhrase('marketplace.listing_successfully_added');
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

    /**
     * Update a marketplace
     * 
     * Request options: 
     * - iListingId:              integer, required
     * - sTitle:              string, required
     * - sCountryIso:              string, required
     * - sPrice:              string, required
     * - aCategoryId:              array
     * - sMiniDescription:              string
     * - sDescription:              string
     * - sCurrencyId:              string, default is USD
     * - iIsSell:              integer, default is 0
     * - iAutoSell:              integer, default is 0 
     * - iCountryChildId:              integer
     * - sCity:              string
     * - sPostalCode:              string
     * - iPrivacy:              integer, default is 0
     * - iPrivacyComment:              integer, default is 0
     * - sEmails:              string
     * - sPersonalMessage:              string
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       "iListingId": 229194,
     * <br/>       'message': "Listing successfully updated."
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */       
    public function edit($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->edit($aData);
        }
        
        return $this->create($aData);
    }    

    /**
     * Get form search
     * 
     * Request options: nothing
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "aCategory": [
     * <br/>            {
     * <br/>                 "category_id": "1",
     * <br/>                 "name": "Community",
     * <br/>                 "url": "http://product-dev.younetco.com/lytk/phpfox376/index.php?do=/marketplace/category/1/community/",
     * <br/>                 "sub": [
     * <br/>                      {
     * <br/>                           "category_id": "15",
     * <br/>                           "name": "1",
     * <br/>                           "url": "http://product-dev.younetco.com/lytk/phpfox376/index.php?do=/marketplace/category/15/1/"
     * <br/>                      },
     * <br/>                      {
     * <br/>                           "category_id": "22",
     * <br/>                           "name": "2",
     * <br/>                           "url": "http://product-dev.younetco.com/lytk/phpfox376/index.php?do=/marketplace/category/22/2/"
     * <br/>                      }
     * <br/>                 ]
     * <br/>            }
     * <br/>       ],
     * <br/>       "aLocation": [
     * <br/>            {
     * <br/>                 "country_iso": "AF",
     * <br/>                 "name": "Afghanistan"
     * <br/>            }
     * <br/>       ]
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */       
    public function formsearch($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->formsearch($aData);
        }
        
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
            'bCanSell' => Phpfox::getUserparam('marketplace.can_sell_items_on_marketplace'),
            'bCanCreateListing' => Phpfox::getUserparam('marketplace.can_create_listing'),
        ), $extra);        
    }

    /**
     * @ignore
     *
     */
    public function canView($iItemId){
        // if($this->isAdvancedModule()){
            // return Phpfox::getService('mfox.advancedmarketplace')->canView($iItemId);
        // }
        
        if (!Phpfox::getUserParam('marketplace.can_access_marketplace'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_marketplace")));
        }
        if (!($aListing = Phpfox::getService('marketplace')->getForEdit($iItemId, true)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_listing_you_are_looking_for_cannot_be_found")));
        }
		
		
		$bIsFriend = Phpfox::getService('friend')->isFriend($aListing['user_id'], Phpfox::getUserId());
		
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('marketplace', $aListing['listing_id'], $aListing['user_id'], $aListing['privacy'], $bIsFriend, $bReturn = true)){
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        // return null - it means TRUE
        return null;
    }    

    /**
     * @ignore
     *
     */
    public function canPostComment($iItemId){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->canPostComment($iItemId);
        }
        
        if (!Phpfox::getUserParam('marketplace.can_access_marketplace'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_marketplace")));
        }

        if (!Phpfox::getUserParam('marketplace.can_post_comment_on_listing'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_listing")));
        }

        if (!($aListing = Phpfox::getService('marketplace')->getForEdit($iItemId, true)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_listing_you_are_looking_for_cannot_be_found")));
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('marketplace', $aListing['listing_id'], $aListing['user_id'], $aListing['privacy'], $aListing['is_friend'], $bReturn = true)){
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        if (!Phpfox::getService('comment')->canPostComment($aListing['user_id'], $aListing['privacy_comment']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_this_item")));
        }

        // return null - it means TRUE
        return null;
    }

    /**
     * Upload ONE image in specific marketplace
     * 
     * Request options: 
     * - iListingId:              integer
     * - $_FILES[image]:              array
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       'message': "Upload successfully"
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */   
    public function photoupload($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->photoupload($aData);
        }
        
        $iListingId = isset($aData['iListingId']) ? (int) $aData['iListingId'] : 0;
        if ($iListingId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.listing_id_is_not_valid")));
        }

        $aListing = Phpfox::getService('marketplace')->getForEdit($iListingId, true);
        if (!isset($aListing['listing_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.listing_has_been_deleted_or_you_do_not_have_permission_to_delete_it")));
        }

        // Check privacy
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('marketplace', $aListing['listing_id'], $aListing['user_id'], $aListing['privacy'], $aListing['is_friend'], $bReturn = true))
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
                    ), (Phpfox::getUserParam('marketplace.max_upload_size_listing') === 0 ? null : (Phpfox::getUserParam('marketplace.max_upload_size_listing') / 1024))
                )
            ){
                $sFileName = Phpfox::getLib('file')->upload('image', Phpfox::getParam('marketplace.dir_image'), $iListingId);                
                $iFileSizes += filesize(Phpfox::getParam('marketplace.dir_image') . sprintf($sFileName, ''));                
                $this->database()->insert(Phpfox::getT('marketplace_image'), array('listing_id' => $iListingId, 'image_path' => $sFileName, 'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID')));     

                foreach ($aSizes as $iSize)
                {                       
                    $oImage->createThumbnail(Phpfox::getParam('marketplace.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('marketplace.dir_image') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);   
                    $oImage->createThumbnail(Phpfox::getParam('marketplace.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('marketplace.dir_image') . sprintf($sFileName, '_' . $iSize . '_square'), $iSize, $iSize, false);                        
                    
                    $iFileSizes += filesize(Phpfox::getParam('marketplace.dir_image') . sprintf($sFileName, '_' . $iSize));         
                }                   
            } else {
                return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
            }
        } else {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.please_try_again")));
        }

        if ($iFileSizes > 0)
        {
            $this->database()->update(Phpfox::getT('marketplace'), array('image_path' => $sFileName, 'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID')), 'listing_id = ' . $iListingId);
            // Update user space usage
            Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'marketplace', $iFileSizes);          
        }

        return array('result' => 1, 'error_code' => 0, 'message'=>html_entity_decode(Phpfox::getPhrase("mfox.upload_successfully")));
    }

    /**
     * Delete ONE photo of marketplace
     * 
     * Request options: 
     * - iImageId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       'message': "Delete successfully"
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */    
    public function photodelete($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->photodelete($aData);
        }
        
        $iImageId = isset($aData['iImageId']) ? (int) $aData['iImageId'] : 0;
        if (Phpfox::getService('marketplace.process')->deleteImage($iImageId))
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
             ->from(Phpfox::getT('marketplace_invite'))
             ->where('listing_id  = ' . $iListingId)
             ->execute('getRows');
        $aIds = array(Phpfox::getUserId());
        foreach($aRows as $aRow) {
            $aIds[] = $aRow['invited_user_id'];
        }

        return $aIds;   
    }    

    /**
     * Get people who have not been invited to listing yet
     * 
     * Request options: 
     * - iListingId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  [
     * <br/>       {
     * <br/>            "UserProfileImg_Url": "http://product-dev.younetco.com/lytk/phpfox376/theme/frontend/default/style/default/image/noimage/profile_50.png",
     * <br/>            "sFullName": "user1000",
     * <br/>            "id": "171"
     * <br/>       }
     * <br/>  ]
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function getinvitepeople($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->getinvitepeople($aData);
        }

        $iListingId = isset($aData['iListingId']) ? (int) $aData['iListingId'] : 0;
        if ($iListingId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.listing_id_is_not_valid")));
        }

        $aListing = Phpfox::getService('marketplace')->getForEdit($iListingId, true);
        if (!($aListing) || !isset($aListing['listing_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('marketplace.the_listing_you_are_looking_for_either_does_not_exist_or_has_been_removed'));
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

    /**
     * Invite user(s) to listing
     * 
     * Request options: 
     * - iListingId:              integer, required
     * - sUserId:              string, eg: '1,2,3'
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       "iListingId": 229194,
     * <br/>       'message': "Members invited!"
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */       
    public function invite($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->invite($aData);
        }
        
        $iListingId = isset($aData['iListingId']) ? (int) $aData['iListingId'] : 0;
        if ($iListingId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.listing_id_is_not_valid")));
        }

        $aListing = Phpfox::getService('marketplace')->getForEdit($iListingId, true);
        if (!($aListing) || !isset($aListing['listing_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('marketplace.the_listing_you_are_looking_for_either_does_not_exist_or_has_been_removed'));
        }

        // init
        $oParseInput = Phpfox::getLib('parse.input');
        $aVals = array('invite' => explode(',', $aData['sUserId']));
        // process 
        $aInvites = $this->database()->select('invited_user_id, invited_email')
            ->from(Phpfox::getT('marketplace_invite'))
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

            $sLink = Phpfox::getLib('url')->permalink('marketplace', $aListing['listing_id'], $aListing['title']);
            $sMessage =  Phpfox::getPhrase('marketplace.full_name_invited_you_to_view_the_marketplace_listing_title', array(
                    'full_name' => Phpfox::getUserBy('full_name'),
                    'title' => $oParseInput->clean($aListing['title'], 255),
                    'link' => $sLink
                ), false, null, $aUser['language_id']
            );
            if (!empty($aVals['personal_message']))
            {
                $sMessage .= "\n\n" .  Phpfox::getPhrase('marketplace.full_name_added_the_following_personal_message', array('full_name' => Phpfox::getUserBy('full_name')), false, null, $aUser['language_id']);
                $sMessage .= $aVals['personal_message'];
            }

            $bSent = Phpfox::getLib('mail')->to($aUser['user_id'])                      
                ->subject(array('marketplace.full_name_invited_you_to_view_the_listing_title', array('full_name' => Phpfox::getUserBy('full_name'), 'title' => $oParseInput->clean($aListing['title'], 255))))
                ->message($sMessage)
                ->notification('marketplace.new_invite')
                ->send();

            if ($bSent)
            {
                $this->database()->insert(Phpfox::getT('marketplace_invite'), array(
                        'listing_id' => $iListingId,                               
                        'user_id' => Phpfox::getUserId(),
                        'invited_user_id' => $aUser['user_id'],
                        'time_stamp' => PHPFOX_TIME
                    )
                );

                (Phpfox::isModule('request') ? Phpfox::getService('request.process')->add('marketplace_invite', $iListingId, $aUser['user_id']) : null);
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
                ->from(Phpfox::getT('marketplace_invite'))
                ->where('listing_id = ' . (int) $iListingId . ' AND invited_user_id = ' . $iUserId)
                ->execute('getSlaveField');

        if($count > 0){
            return true;
        }

        return false;
    }    

    /**
     * Add transaction listing
     * 
     * Request options: 
     * - iListingId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       "iListingId": 1,
     * <br/>       "iInvoiceId": 2,
     * <br/>       'message': "Added invoice successfully."
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */   
    public function transactionadd($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->transactionadd($aData);
        }
        
        $iListingId = isset($aData['iListingId']) ? (int) $aData['iListingId'] : 0;
        if (!($aListing = Phpfox::getService('marketplace')->getForEdit($iListingId, true)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('marketplace.unable_to_find_the_listing_you_are_looking_for'));
        }
        
        if (($iInvoiceId = Phpfox::getService('marketplace.process')->addInvoice($aListing['listing_id'], $aListing['currency_id'], $aListing['price'])))
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

    /**
     * Update success/fail to transaction listing 
     * 
     * Request options: 
     * - iInvoiceId:              integer, required
     * - sStoreKidTransactionId:              string
     * - sPlayStoreOrderId:              string
     * - sDevice:              string
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       'message': "Updated invoice successfully"
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */       
    public function transactionupdate($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->transactionupdate($aData);
        }
        
        $iInvoiceId = isset($aData['iInvoiceId']) ? (int) $aData['iInvoiceId'] : 0;
        $aInvoice = Phpfox::getService('marketplace')->getInvoice($iInvoiceId);
        if(!isset($aInvoice['listing_id'])){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_a_valid_invoice")));
        }

        $iListingId = $aInvoice['listing_id'];
        if (!($aListing = Phpfox::getService('marketplace')->getForEdit($iListingId, true)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('marketplace.unable_to_find_the_listing_you_are_looking_for'));
        }

        $sStoreKidTransactionId = isset($aData['sStoreKidTransactionId']) ? $aData['sStoreKidTransactionId'] : '';
        $sPlayStoreOrderId = isset($aData['sPlayStoreOrderId']) ? $aData['sPlayStoreOrderId'] : '';
        $sDevice = isset($aData['sDevice']) ? $aData['sDevice'] : '';
        $sStatus = '';
        switch ($aData['sStatus']) {
            case 'success':
                $sStatus = 'completed';
                $this->database()->update(Phpfox::getT('marketplace_invoice'), array(
                        'status' => $sStatus,
                        'time_stamp_paid' => PHPFOX_TIME
                    ), 'invoice_id = ' . $aInvoice['invoice_id']
                );      

                if ($aListing['auto_sell'])
                {
                    $this->database()->update(Phpfox::getT('marketplace'), array(
                            'view_id' => '2'
                        ), 'listing_id = ' . $aListing['listing_id']
                    );                  
                }       

                Phpfox::getLib('mail')->to($aListing['user_id'])                        
                    ->subject(array('marketplace.item_sold_title', array('title' => Phpfox::getLib('parse.input')->clean($aListing['title'], 255))))
                    ->fromName($aInvoice['full_name'])
                    ->message(array('marketplace.full_name_has_purchased_an_item_of_yours_on_site_name', array(
                                'full_name' => $aInvoice['full_name'],
                                'site_name' => Phpfox::getParam('core.site_title'),
                                'title' => $aListing['title'],
                                'link' => Phpfox::getLib('url')->makeUrl('marketplace.view', $aListing['title_url']),
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

                    $sModuleId = 'marketplace';
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
                // $this->database()->update(Phpfox::getT('marketplace_invoice'), array(
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

    /**
     * Get permissions in module basing on viewer 
     * 
     * Request options: nothing
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "bCanSell": true,
     * <br/>       "bCanCreateListing": true
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */       
    public function perms($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedmarketplace')->perms($aData);
        }

        return $this->__getPermission($aData);
    }
}
