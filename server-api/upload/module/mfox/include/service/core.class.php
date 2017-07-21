<?php

/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

/**
 * @author     ductc@younetco.com
 * @package    mfox
 * @subpackage mfox.service
 * @version    3.01
 * @since      May 27, 2013
 * @link       Mfox Api v1.0
 */
class Mfox_Service_Core extends Phpfox_Service
{
    /**
     * Input data: N/A
     *
     * Output data:
     * + sISO: string.
     * + sCountry: string.
     *
     * @see Mobile - API phpFox/Api V2.0
     * @see core/country
     *
     * @param array $aData Array of data
     *
     * @return array
     */
    public function country($aData)
    {
        /**
         * @var array
         */
        $aCountries = Phpfox::getService('core.country')->get();
        /**
         * @var array
         */
        $aResult = array();

        foreach ($aCountries as $sISO => $sCountry) {
            $aResult[] = array(
                'sISO'     => $sISO,
                'sCountry' => $sCountry
            );
        }

        return $aResult;
    }

    function _fetchUserParams($params)
    {
        $result = array();
        foreach ($params as $sections) {
            foreach ($sections as $k => $v) {
                if (is_int($k)) {
                    $result[ $v ] = Phpfox::getUserParam($v, false);
                } else {
                    $result[ $v ] = Phpfox::getUserParam($k, false);
                }
            }
        }

        return $result;
    }

    public function allows()
    {

        $params['blog'] = array(
            'blog.view_blogs',
            'blog.add_new_blog'
        );
        $params['poll'] = array(
            'poll.can_access_polls',
            'poll.can_create_poll',
        );
        $params['marketplace'] = array(
            'marketplace.can_access_marketplace',
            'marketplace.can_create_listing',
        );

        $params['event'] = array(
            'event.can_access_event',
            'event.can_create_event',
        );

        $params['music'] = array(
            'music.can_access_music',
            'music.can_upload_music_public',
        );

        $params['photo'] = array(
            'photo.can_upload_photos',
            'photo.can_view_photos',
            'photo.can_view_photo_albums',
            'photo.can_create_photo_album'
        );

        $params['quiz'] = array(
            'quiz.can_access_quiz',
            'quiz.can_create_quiz',
        );

        $params['video'] = array(
            'video.can_access_videos',
            'video.can_upload_videos',
        );

        $params['pages'] = array(
            'pages.can_view_browse_pages',
            'pages.can_add_new_pages',
            'pages.can_add_cover_photo_pages',
        );

        $params['groups'] = array(
            'pf_group_browse',
            'pf_group_add',
            'pf_group_add_cover_photo',
        );

        if (Phpfox::isModule('videochannel'))
        {
            $params['videochannel'] = array(
                'videochannel.can_access_videos',
                'videochannel.can_upload_videos',
                'videochannel.can_add_channels',
            );
        }

        $params['profile'] = array(
            'profile.can_change_cover_photo',
        );

        if (Phpfox::getService('mfox.event')->isAdvancedModule()) {
            $params['event'] = array(
                'fevent.can_access_event' => 'event.can_access_event',
                'fevent.can_create_event' => 'event.can_create_event',
            );
        }

        if (Phpfox::getService('mfox.marketplace')->isAdvancedModule()) {
            $params['marketplace'] = array(
                'advancedmarketplace.can_access_advancedmarketplace' => 'marketplace.can_access_marketplace',
                'advancedmarketplace.can_create_listing'             => 'marketplace.can_create_listing',
            );
        }

        if (Phpfox::getService('mfox.photo')->isAdvancedModule()) {
            $params['photo'] = array(
                'advancedphoto.can_upload_photos'      => 'photo.can_upload_photos',
                'advancedphoto.can_view_photos'        => 'photo.can_view_photos',
                'advancedphoto.can_view_photo_albums'  => 'photo.can_view_photo_albums',
                'advancedphoto.can_create_photo_album' => 'photo.can_create_photo_album',
            );
        }

        if (Phpfox::isModule('directory'))
        {
            $params['directory'] = array(
                'directory.can_create_business',
                'directory.can_create_business_for_claiming',
                'directory.can_view_business',
            );
        }
        if (Phpfox::isModule('ultimatevideo'))
        {
            $params['ultimatevideo'] = array(
                'ynuv_can_view_video',
                'ynuv_can_upload_video',
                'ynuv_can_add_playlist'
            );
        }

        $allows = $this->_fetchUserParams($params);
        $allows['user.is_admin'] = Phpfox::isAdmin();

        return $allows;
    }

    /**
     * return left navigation structure
     *
     * @return array
     * @author MinhTA
     */
    public function leftMenu($aData)
    {
        $aMenus = array();
        $aTmpMenus = Phpfox::getService('mfox.menu')->getAll();

        foreach ($aTmpMenus as $aTmpMenu) {
            if ($aMenu = $this->_processMenuRow($aTmpMenu)) {
                $aMenus[] = $aMenu;
            }
        }

        return $aMenus;
    }

    private function _processMenuRow($aRow)
    {
        $bIsValid = false;
        
        if ($aRow['is_group'] == 1) {
            $bIsValid = true;
        }
        
        if (!empty($aRow['module']) && Phpfox::isModule($aRow['module'])) {
            $bIsValid = true;
        }

        if (!empty($aRow['module_alt']) && Phpfox::isModule($aRow['module_alt'])) {
            $bIsValid = true;
        }

        if ($bIsValid) {
            $aMenu = array(
                'href' => (string) $aRow['url'],
                'icon' => (string) $aRow['icon'],
                'label' => Phpfox::getService('mfox')->decodeUtf8Compat($aRow['label']),
                'name' => (string) $aRow['name'],
                'type' => $aRow['is_group'] ? 'menu_group' : 'menu_item',
            );

            if ($aRow['is_group'] == 1) {
                $aMenu['children'] = array();
                foreach ($aRow['children'] as $aTmpChild) {
                    if ($aChild = $this->_processMenuRow($aTmpChild)) {
                        $aMenu['children'][] = $aChild;
                    }
                }
            }

            return $aMenu;
        }
    
        return false;
    }

    /**
     * NOTICE: fox using 10 point and 5 star, so we need to x 2 when add and /2 when get
     */
    public function rate($aData)
    {
        if (empty($aData['sItemType']) || empty($aData['iItemId'])) {
            return array(
                'error_code'    => 1,
                'error_message' => html_entity_decode(Phpfox::getPhrase("mfox.can_not_find_the_item"))
            );
        }

        if (!isset($aData['iRating'])) {
            return array(
                'error_code'    => 2,
                'error_message' => html_entity_decode(Phpfox::getPhrase("mfox.missing_rating_value"))
            );
        }
        $sItemType = $aData['sItemType'];
        $aData['iRating'] = (int)$aData['iRating'] * 2;

        $sItemType = Phpfox::getService('mfox.like')->changeType($sItemType);
        switch ($sItemType) {
            case 'videochannel':
                return Phpfox::getService('mfox.videochannel')->rate(array(
                    'iVideoId' => $aData['iItemId'],
                    'iRating'  => $aData['iRating']
                ));
            case 'video':
                return Phpfox::getService('mfox.video')->rate(array(
                    'iVideoId' => $aData['iItemId'],
                    'iRating'  => $aData['iRating']
                ));
            case 'advancedphoto':
            case 'photo':
                return Phpfox::getService('mfox.helper.rate')->ratingPhoto(array(
                    'iPhotoId' => $aData['iItemId'],
                    'iRating'  => $aData['iRating']
                ));
            case 'musicsharing_song':
                return Phpfox::getService('mfox.helper.rate')->ratingMusicSharingAlbumSong(array(
                    'iSongId' => $aData['iItemId'],
                    'iRating' => $aData['iRating']
                ));
            case 'musicsharing_album':
                return Phpfox::getService('mfox.helper.rate')->ratingMusicSharingAlbum(array(
                    'iAlbumId' => $aData['iItemId'],
                    'iRating'  => $aData['iRating']
                ));
            case 'music_song':
                return Phpfox::getService('mfox.helper.rate')->ratingSong(array(
                    'iSongId' => $aData['iItemId'],
                    'iRating' => $aData['iRating']
                ));
            case 'music_album':
                return Phpfox::getService('mfox.helper.rate')->ratingMusicAlbum(array(
                    'iAlbumId' => $aData['iItemId'],
                    'iRating'  => $aData['iRating']
                ));
            case 'ultimatevideo_video':
                return Phpfox::getService('mfox.helper.rate')->ratingUltimateVideo(array(
                    'iVideoId' => $aData['iItemId'],
                    'iRating'  => $aData['iRating']
                ));

        }

        return array(
            'iTotal'  => 'Not Implement Yet',
            'fRating' => 'Not Implement Yet'
        );
    }

    /**
     * @return array supported languages
     */
    public function languages()
    {
        $response = array();

        $languages = Phpfox::getService('language')
            ->get(array('l.user_select=1'));

        foreach ($languages as $lang) {
            $response[] = array(
                'lang' => $lang['language_id'],
                'name' => Phpfox::getService('mfox')->decodeUtf8Compat($lang['title']),
            );
        }

        return $response;
    }

    /**
     * Input data:
     * + iPage: current page
     *
     */
    public function caculatePaging($totalRecord, $iLimit, $iPage)
    {
        //  init
        $fromResult = 0;
        $toResult = 0;
        $isShowPrev = '0';
        $pagePrev = 0;
        $isShowNext = '0';
        $pageNext = 0;

        //  process
        $toResult = (int)$iPage * (int)$iLimit;
        if ($toResult > $totalRecord) {
            $toResult = $totalRecord;
        }
        $fromResult = $toResult - (int)$iLimit + 1;

        if ($iPage > 1) {
            $isShowPrev = '1';
            $pagePrev = (int)$iPage - 1;
        }

        if ($toResult < $totalRecord) {
            $isShowNext = '1';
            $pageNext = (int)$iPage + 1;
        }

        //  end
        return array($fromResult, $toResult, $isShowPrev, $pagePrev, $isShowNext, $pageNext);
    }

    //display
    public function convertToUserTimeZone($iTime)
    {
        $iTimeZoneOffsetInSecond = Phpfox::getLib('date')->getTimeZone() * 60 * 60;
        // on the interface we have convert into gmt, now we roll back to server time
        $iTime = $iTime + $iTimeZoneOffsetInSecond;

        return $iTime;
    }

    //save to database
    public function convertFromUserTimeZone($iTime)
    {
        $iTimeZoneOffsetInSecond = Phpfox::getLib('date')->getTimeZone() * 60 * 60;
        // on the interface we have convert into gmt, now we roll back to server time
        $iTime = $iTime - $iTimeZoneOffsetInSecond;

        return $iTime;
    }

    public function sidebar()
    {
        $aRows = $this->database()
            ->select('*')
            ->from(Phpfox::getT('mfox_leftnavi'))
            ->where('is_enabled=1')
            ->order('sort_order asc')
            ->execute('getSlaveRows');

        $result = array();
        foreach ($aRows as $aRow) {
            $result[] = array(
                "sName"   => $aRow['name'],
                "sLabel"  => $aRow['label'],
                "sLayout" => $aRow['layout'],
                "sIcon"   => $aRow['icon'],
                "sUrl"    => $aRow['url'],
            );
        }

        return $result;
    }

    public function settings()
    {
        return array(
            'mfox_location_update_interval' => (int)Phpfox::getParam('mfox.location_update_interval'),
            'like_allow_dislike'            => Phpfox::getService('mfox.like')->allowdislike(false),
            'user_date_of_birth_start'      => (int)Phpfox::getParam('user.date_of_birth_start'),
            'user_date_of_birth_end'        => (int)Phpfox::getParam('user.date_of_birth_end'),
            'user_timezone'                 => Phpfox::getLib('date')->getTimeZone(),
            'bCanCreateQuiz'                => Phpfox::getUserParam('quiz.can_create_quiz'),
            'bCanEditQuiz'                  => Phpfox::getUserParam('quiz.can_edit_own_title'),
            'bCanViewPollResult'            => Phpfox::getUserparam('poll.view_poll_results_before_vote'),
            'bCanChangePollVote'            => Phpfox::getUserParam('poll.poll_can_change_own_vote'),
            'bCanEditOwnPoll'               => Phpfox::getUserParam('poll.poll_can_edit_own_polls'),
            'bCanEditOthersPoll'            => Phpfox::getUserParam('poll.poll_can_edit_others_polls'),
            'bCanDeleteOwnPoll'             => Phpfox::getUserParam('poll.poll_can_delete_own_polls'),
            'bCanDeleteOthersPoll'          => Phpfox::getUserParam('poll.poll_can_delete_others_polls'),
            'bCanViewOwnUserPollResult'     => Phpfox::getUserParam('poll.can_view_user_poll_results_own_poll'),
            'bCanViewOthersUserPollResult'  => Phpfox::getUserParam('poll.can_view_user_poll_results_other_poll'),
            'bCanCreatePoll'                => Phpfox::getUserParam('poll.can_create_poll'),
            'bCanEditEditPollQuestion'      => Phpfox::getUserParam('poll.can_edit_question'),
            'bCanAccessPoll'                => Phpfox::getUserParam('poll.can_access_polls'),
            'chat_module'                   => $this->_getChatModule(),
            'user_login_type'               => Phpfox::getParam('user.login_type'),
            'bAllowShowPoints'              => Phpfox::getParam('user.no_show_activity_points')
        );
    }

    /**
     * Get chat module
     * @param
     * @return string
     */
    private function _getChatModule()
    {
        $sParam = strtolower(Phpfox::getParam('mfox.chat_module'));

        if (empty($sParam) || $sParam == 'none' || ($sParam == 'YNChat' && !Phpfox::isModule($sParam)) || ($sParam == 'instant messaging' && !setting('pf_im_enabled')))
        {
            return '';
        }
        $sParam == 'instant messaging' && $sParam = 'chat';
        return $sParam;
    }

    public function isAdvancedModule($isUsing, $isAdv, $isDefault)
    {
        if ($isUsing) {
            if ($isAdv) {
                return true;
            } else {
                return false;
            }
        } else {
            if ($isAdv) {
                if ($isDefault) {
                    return false;
                } else {
                    return true;
                }
            } else {
                return false;
            }
        }
    }

    public function getchilds($aData)
    {
        $sCountryIso = isset($aData['sCountryIso']) ? $aData['sCountryIso'] : '';
        $aChildren = $this->database()->select('child_id, name')
            ->from(Phpfox::getT('country_child'))
            ->where('country_iso = \'' . $this->database()->escape($sCountryIso) . '\'')
            ->order('ordering ASC, name ASC')
            ->execute('getRows');

        foreach ($aChildren as $key => $value) {
            $aChildren[ $key ]['name'] = Phpfox::getService('mfox')->decodeUtf8Compat($value['name']);
        }

        return $aChildren;
    }

    public function isNumeric($val)
    {
        if (empty($val)) {
            return false;
        }

        if (!is_numeric($val)) {
            return false;
        }

        return true;
    }

    public function getcurrencies($aData)
    {
        $aCurrencies = Phpfox::getService('core.currency')->get();
        $result = array();
        foreach ($aCurrencies as $iKey => $aCurrency) {
            $aCurrencies[ $iKey ]['is_default'] = '0';

            if (Phpfox::getService('core.currency')->getDefault() == $iKey) {
                $aCurrencies[ $iKey ]['is_default'] = '1';
            }
            $aCurrencies[ $iKey ]['name'] = Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase($aCurrencies[ $iKey ]['name']));
            $aCurrencies[ $iKey ]['currency_id'] = $iKey;
            $result[] = $aCurrencies[ $iKey ];
        }

        return $result;
    }

    public function getCountries($aData)
    {
        $aCountries = $this->database()->select('c.country_iso, c.name')
            ->from(Phpfox::getT('country'), 'c')
            ->order('c.ordering ASC, c.name ASC')
            ->execute('getRows');

        foreach ($aCountries as $key => $value) {
            $aCountries[ $key ]['name'] = Phpfox::getService('mfox')->decodeUtf8Compat($value['name']);
        }

        return $aCountries;
    }

    function getHeaderValue($key, $defaultValue = null)
    {
        $response = null;
        $httpTemp = strtoupper('http_' . $key);

        if (!empty($_REQUEST[ $key ])) {
            $response = $_REQUEST[ $key ];
        } elseif (isset($_SERVER[ $httpTemp ])) {
            $response = $_SERVER[ $httpTemp ];
        } else if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();

            if (isset($headers[ $key ])) {
                $response = $headers[ $key ];
            }

            $temp = strtolower($key);

            if (isset($headers[ $temp ])) {
                $response = $headers[ $temp ];
            }
        }

        return !empty($response) ? $response : $defaultValue;
    }

    /**
     * @return string language ID
     * @usage ./mfox/include/plugin/locale_construct_end.php
     */
    public function initLocale()
    {
        $lang = $this->getHeaderValue('foxlang', null);

        if (!$lang || $lang == '' || $lang == 'null' || $lang == 'undefined' || $lang == '0') {
            return null;
        }

        return $lang;
    }

    public function test_phrase()
    {

    }

    /**
     * collect all required client phrase
     */
    public function phrases()
    {
        $phraseMap = include PHPFOX_DIR_MODULE . '/mfox/inc/client_phrase_map.php';

        $response = array();

        $mfox = Phpfox::getService('mfox');

        foreach ($phraseMap as $var => $name) {
            $response[ $name ] = $mfox->decodeUtf8Compat(Phpfox::getPhrase($var));
        }

        return $response;
    }

    public function css_build_number()
    {
        return Phpfox::getService('mfox.style')->getDefaultBuildNumber();
    }

    /**
     * redirect to target stylesheets file.
     */
    public function iphone_css()
    {
        Phpfox::getService('mfox.style')->css('iphone');
    }

    /**
     * redirect to target stylesheets file.
     */
    public function ipad_css()
    {
        Phpfox::getService('mfox.style')->css('ipad');
    }

    public function android_css()
    {
        Phpfox::getService('mfox.style')->css('android');
    }

    public function add_missing_phrase()
    {
        Phpfox::getPhrase('mfox.Newsfeed');
        Phpfox::getPhrase('mfox.Membership');
    }

    public function changeLanguage($aData){
        if(!isset($aData['language_id']) || empty($aData['language_id'])){
            return false;
        }
        if(Language_Service_Process::instance()->useLanguage($aData['language_id']))
        {
            return true;
        }
        return false;
    }
}
