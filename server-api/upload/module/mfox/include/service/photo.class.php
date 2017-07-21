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
 * @since June 5, 2013
 * @link Mfox Api v2.0
 */
class Mfox_Service_Photo extends Phpfox_Service {

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

    const GETPHOTO_TYPE_MY = 'my';
    const GETPHOTO_TYPE_FEATURED = 'featured';
    const GETPHOTO_TYPE_FRIEND = 'friend';
    const GETPHOTO_TYPE_ALBUM = 'album';
    const GETPHOTO_TYPE_SLIDE = 'slide';
    const GETPHOTO_PHOTO_LIMIT = 10;

    const GETALBUM_TYPE_MY = 'my';
    const GETALBUM_TYPE_FEATURED = 'featured';
    const GETALBUM_TYPE_FRIEND = 'friend';
    const GETALBUM_ALBUM_LIMIT = 5;

    const ACTION_TYPE_MORE = 'more';
    const ACTION_TYPE_NEW = 'new';

    const ACTION_TYPE_NEXT = 'next';
    const ACTION_TYPE_PREVIOUS = 'previous';

    private $_bIsAdvancedPhotoModule = false;

    private $_sDefaultImagePath = null;

    public function __construct()
    {
        $this->_oReq = Mfox_Service_Request_Request::instance();
        $this->_oSearch = Mfox_Service_Search_Search::instance();
        $this->_oBrowse = Mfox_Service_Search_Browse::instance();

        $this->_sTable = Phpfox::getT('photo');

        $isUsing = Phpfox::getParam('mfox.replace_photo');
        $isAdv = Phpfox::isModule('advancedphoto');
        $isDefault = Phpfox::isModule('photo');

        $this->_sDefaultImageEventPath = Phpfox::getParam('core.url_module') . 'mfox/static/image/event_cover_default.jpg';

        $this->_bIsAdvancedPhotoModule = Phpfox::getService('mfox.core')->isAdvancedModule($isUsing, $isAdv, $isDefault);
    }



    public function isAdvancedModule(){
        return $this->_bIsAdvancedPhotoModule;
    }

    public function getfullalbumslide($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->getfullalbumslide($aData);
        }

        return $this->fullalbumslide($aData);
    }
    /**
     * Input data:
     * + iCurrentPhotoId: int, required.
     * + iLimitPhoto: int, optional.
     * + iAlbumId: int, optional.
     *
     * Output data:
     * + iPhotoId: int.
     * + sTitle: string.
     * + bCanPostComment: bool.
     * + sPhotoUrl: string.
     * + fRating: float.
     * + iTotalVote: int.
     * + iTotalBattle: int.
     * + iAlbumId: int.
     * + sAlbumName: string.
     * + iTotalLike: int.
     * + iTotalDislike: int.
     * + bIsFeatured: bool.
     * + bIsCover: bool.
     * + iTotalView: int.
     * + iTotalComment: int.
     * + iTotalDownload: int.
     * + iAllowDownload: int.
     * + iIsSponsor: int.
     * + iOrdering: int.
     * + bIsProfilePhoto: bool.
     * + sFileName: string.
     * + sFileSize: string.
     * + sMimeType: string.
     * + sExtension: string.
     * + sDescription: string.
     * + iWidth: int.
     * + iHeight: int.
     * + sAlbumUrl: string.
     * + sAlbumTitle: string.
     * + iAlbumProfileId: int.
     * + bIsViewed: bool.
     * + aCategories: array.
     * + bCategoryList: bool.
     * + sOriginalDestination: string.
     * + bIsFriend: bool.
     * + iUserId: int.
     * + iProfilePageId: int.
     * + iUserServerId: int.
     * + sUserName: string.
     * + sFullName: string.
     * + iGender: int.
     * + sUserImage: string.
     * + bIsInvisible: bool.
     * + iUserGroupId: int.
     * + iLanguageId: int.
     * + iViewId: int.
     * + iTypeId: int.
     * + sModuleId: string.
     * + iGroupId: int.
     * + iParentUserId: int.
     * + iServerId: int.
     * + iMature: int.
     * + iAllowComment: int.
     * + iAllowRate: int.
     * + bIsLiked: bool.
     * + iPrivacy: int.
     * + iPrivacyComment: int.
     * + sTimeStamp: string.
     *
     * @see Mobile - API phpFox/Api V2.0
     * @see photo/getfullalbumslide
     *
     * @param array $aData
     * @return array
     */
    public function fullalbumslide($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->fullalbumslide($aData);
        }

        $iAlbumId = isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : 0;
        // Get the current album we are trying to view
        $aAlbum = Phpfox::getService('photo.album')->getForView($aData['iAlbumId']);
        // Make sure this is a valid album
        if (!isset($aAlbum['album_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('photo.invalid_photo_album')
            );
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('photo_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'),
                'error_code' => 1
            );
        }

        $sAction = isset($aData['sAction']) ? $aData['sAction'] : '';
        if(empty($sAction)){
            $aPreviousData = $aData;
            $aPreviousData['sAction'] = 'previous';
            $aPreviousPhotos = $this->albumslide($aPreviousData);

            $aNextData = $aData;
            $aNextData['sAction'] = 'next-with-current';
            $aNextPhotos = $this->albumslide($aNextData);

            foreach($aNextPhotos as $aPhoto)
            {
                $aPreviousPhotos[] = $aPhoto;
            }

            return $aPreviousPhotos;
        } else if('next' == $sAction){
            $aNextData = $aData;
            $aNextData['sAction'] = 'next';
            $aNextData['iOneSide'] = '1';
            return $this->albumslide($aNextData);
        } else if('previous' == $sAction){
            $aPreviousData = $aData;
            $aPreviousData['sAction'] = 'previous';
            $aPreviousData['iOneSide'] = '1';
            return $this->albumslide($aPreviousData);
        }
    }

    public function fetch_photo($aData){
        if($aData['sParentType'] == 'album'){
            $aData['iAlbumId'] = $aData['iParentId'];
            return $this->listalbumphoto($aData);
        }
        if($aData['sParentType'] == 'user'){
            $aData['iUserId'] =  $aData['iParentId'];

            return $this->filter($aData);
        }

        return $this->filter($aData);
    }

    public function albumslide($aData)
    {

        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->albumslide($aData);
        }
        if (!isset($aData['iCurrentPhotoId']) || $aData['iCurrentPhotoId'] < 1)
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.current_photo_id_is_not_valid")),
                'error_code' => 1
            );
        }

        if (!isset($aData['iAlbumId']) || $aData['iAlbumId'] < 1)
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.album_id_is_not_valid")),
                'error_code' => 1
            );
        }

        $iOneSide = isset($aData['iOneSide']) ? (int) $aData['iOneSide'] : 0;
        $iAlbumId = (int) $aData['iAlbumId'];
        // Get the current album we are trying to view
        $aAlbum = Phpfox::getService('photo.album')->getForView($aData['iAlbumId']);

        // Make sure this is a valid album
        if (!isset($aAlbum['album_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('photo.invalid_photo_album')
            );
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('photo_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'),
                'error_code' => 1
            );
        }

        $aCurrentPhoto = Phpfox::getService('photo')->getPhoto($aData['iCurrentPhotoId'], Phpfox::getUserId());
        if (!isset($aCurrentPhoto['photo_id']))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.current_photo_is_not_valid")),
                'error_code' => 1
            );
        }

        if (!isset($aData['iAmountOfPhoto']))
        {
            $aData['iAmountOfPhoto'] = 10;
        }

        $oDb = $this->database();
        $oDb->select('l.like_id as is_liked, u.user_id, u.profile_page_id, u.server_id AS user_server_id, u.user_name, u.full_name, u.gender, u.user_image, u.is_invisible, u.user_group_id, u.language_id, p.*, pa.name AS album_url, pa.name AS album_title, pa.profile_id AS album_profile_id, pi.*');
        $oDb->from(Phpfox::getT('photo'), 'p');

        $oDb->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = "photo" AND l.item_id = p.photo_id AND l.user_id = ' . Phpfox::getUserId());
        $oDb->leftJoin(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = p.photo_id');
        $oDb->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id');
        $oDb->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id');

        // please correct photo privacies.



        $aConditions = array();
        $aConditions[] = 'p.album_id = ' . (int) $iAlbumId;
        // Set the current photo id condition.
        if (isset($aData['sAction']) && $aData['sAction'] == 'previous')
        {
            $aConditions[] = 'p.photo_id > ' . (int) $aData['iCurrentPhotoId'];
        }
        elseif(isset($aData['sAction']) && $aData['sAction'] == 'next-with-current')
        {
            $aConditions[] = 'p.photo_id <= ' . (int) $aData['iCurrentPhotoId'];
        }
        else // Next
        {
            $aConditions[] = 'p.photo_id < ' . (int) $aData['iCurrentPhotoId'];
        }



        $oDb->where(implode(' AND ', $aConditions));

        if (isset($aData['sAction']) && $aData['sAction'] == 'previous')
        {
            $oDb->order('p.photo_id ASC');
            $oDb->limit((int)$aData['iAmountOfPhoto']);
        }
        else // Next.
        {
            $oDb->order('p.photo_id DESC');
            if($iOneSide){
                $oDb->limit((int)$aData['iAmountOfPhoto']);
            } else {
                // i do not know old workflow, why it counts 1
                // so i duplicate for one side case
                $oDb->limit((int)$aData['iAmountOfPhoto'] + 1);
            }
        }

        $aPhotos = $oDb->execute('getRows');
        if (isset($aData['sAction']) && $aData['sAction'] == 'previous')
        {
            $aPhotos = array_reverse($aPhotos);
        }
        else // Next.
        {
            // Do nothing.
        }

        $aResult = array();
        foreach ($aPhotos as $aPhoto)
        {
            // update album info
            if (empty($aPhoto['album_id']))
            {
                $aPhoto['album_url'] = 'view';
            }
            if ($aPhoto['album_id'] > 0)
            {
                if ($aPhoto['album_profile_id'] > 0)
                {
                    $aPhoto['album_title'] =  Phpfox::getPhrase('photo.profile_pictures');
                    $aPhoto['album_url'] = Phpfox::permalink('photo.album.profile', $aPhoto['user_id'], $aPhoto['user_name']);
                }
                else
                {
                    $aPhoto['album_url'] = Phpfox::permalink('photo.album', $aPhoto['album_id'], $aPhoto['album_title']);
                }
            }

            if (isset($aData['iInDetails']) && $aData['iInDetails'] == '1'){
                $aFeed = array(
                    'comment_type_id' => 'photo',
                    'privacy' => $aPhoto['privacy'],
                    'comment_privacy' => $aPhoto['privacy_comment'],
                    'like_type_id' => 'photo',
                    'feed_is_liked' => $aPhoto['is_liked'],
                    'feed_is_friend' => $aPhoto['is_friend'],
                    'item_id' => $aPhoto['photo_id'],
                    'user_id' => $aPhoto['user_id'],
                    'total_comment' => $aPhoto['total_comment'],
                    'total_like' => $aPhoto['total_like'],
                    'feed_link' => Phpfox::getLib('url')->permalink('photo', $aPhoto['photo_id'], $aPhoto['title']),
                    'feed_title' => $aPhoto['title'],
                    'feed_display' => 'view',
                    'feed_total_like' => $aPhoto['total_like'],
                    'report_module' => 'photo',
                    'report_phrase' =>  Phpfox::getPhrase('photo.report_this_photo'));

                //  get list of liked user
                $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('photo'
                    , $aPhoto['photo_id']
                    , false
                    , Phpfox::getParam('feed.total_likes_to_display'));
                $aUserLike = array();
                foreach($aLike['likes'] as $like){
                    $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
                }

                $aUserDislike = array();
                $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('photo', $aPhoto['photo_id'], $bGetCount = false);
                foreach($aDislike as $dislike){
                    $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
                }

                $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aPhoto, '_50_square');

                $aResult[] = array(
                    'iPhotoId' => $aPhoto['photo_id'],
                    'iProfilePageId' => $aPhoto['profile_page_id'],
                    'sTitle' => $aPhoto['title'],
                    'bCanPostComment' => Phpfox::getService('mfox.comment')->checkCanPostComment($aFeed),
                    'sPhotoUrl' => Phpfox::getLib('image.helper')->display(array(
                            'server_id' => $aPhoto['server_id'],
                            'path' => 'photo.url_photo',
                            'file' => $aPhoto['destination'],
                            'suffix' => '_1024',
                            'return_url' => true
                        )
                    ),
                    'fRating' => $aPhoto['total_rating'],
                    'iTotalVote' => $aPhoto['total_vote'],
                    'iTotalBattle' => $aPhoto['total_battle'],
                    'iAlbumId' => $aPhoto['album_id'],
                    'sAlbumName' => $aPhoto['album_title'],
                    'iTotalLike' => count($aUserLike),
                    'iTotalDislike' => $aPhoto['total_dislike'],
                    'bIsFeatured' => $aPhoto['is_featured'],
                    'bIsCover' => $aPhoto['is_cover'],
                    'iTotalView' => $aPhoto['total_view'],
                    'iTotalComment' => Phpfox::getService('mfox.comment')->getCommentCount(array('sItemType' => 'photo', 'iItemId' => $aPhoto['photo_id']), null),
                    'iTotalDownload' => $aPhoto['total_download'],
                    'iAllowDownload' => $aPhoto['allow_download'],
                    'iIsSponsor' => $aPhoto['is_sponsor'],
                    'iOrdering' => $aPhoto['ordering'],
                    'bIsProfilePhoto' => $aPhoto['is_profile_photo'],
                    'sFileName' => $aPhoto['file_name'],
                    'sFileSize' => $aPhoto['file_size'],
                    'sMimeType' => $aPhoto['mime_type'],
                    'sExtension' => $aPhoto['extension'],
                    'sDescription' => $aPhoto['description'],
                    'iWidth' => $aPhoto['width'],
                    'iHeight' => $aPhoto['height'],
                    'sAlbumUrl' => $aPhoto['album_url'],
                    'sAlbumTitle' => $aPhoto['album_title'],
                    'iAlbumProfileId' => $aPhoto['album_profile_id'],
                    'bIsViewed' => $aPhoto['is_viewed'],
                    'aCategories' => $aPhoto['categories'],
                    'bCategoryList' => $aPhoto['category_list'],
                    'sOriginalDestination' => $aPhoto['original_destination'],
                    'bIsFriend' => (bool) $aPhoto['is_friend'],
                    'iUserId' => $aPhoto['user_id'],
                    'iProfilePageId' => $aPhoto['profile_page_id'],
                    'iUserServerId' => $aPhoto['user_server_id'],
                    'sUserName' => $aPhoto['user_name'],
                    'sFullName' => $aPhoto['full_name'],
                    'iGender' => $aPhoto['gender'],
                    'sUserImage' => $sUserImage,
                    'bIsInvisible' => $aPhoto['is_invisible'],
                    'iUserGroupId' => $aPhoto['user_group_id'],
                    'iLanguageId' => (int) $aPhoto['language_id'],
                    'iViewId' => $aPhoto['view_id'],
                    'iTypeId' => $aPhoto['type_id'],
                    'sModuleId' => $aPhoto['module_id'],
                    'iGroupId' => (int) $aPhoto['group_id'],
                    'iParentUserId' => $aPhoto['parent_user_id'],
                    'iServerId' => $aPhoto['server_id'],
                    'iMature' => $aPhoto['mature'],
                    'iAllowComment' => $aPhoto['allow_comment'],
                    'iAllowRate' => $aPhoto['allow_rate'],
                    'bIsLiked' => Phpfox::getService('mfox.like')->checkIsLiked('photo', $aPhoto['photo_id'], Phpfox::getUserId()),
                    'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('photo', $aPhoto['photo_id'], Phpfox::getUserId()),
                    'iPrivacy' => $aPhoto['privacy'],
                    'iPrivacyComment' => $aPhoto['privacy_comment'],

                    'bCanMakeProfilePicture'=>  ($aPhoto['user_id'] == Phpfox::getUserId())?1:0,

                    'aUserLike' => $aUserLike,
                    'aUserDislike' => $aUserDislike,
                    'sUserImageUrl' => $sUserImage,
                    'sUserName' => $aPhoto['full_name'],
                    'sItemType' => 'photo',
                    'sModelType' => 'photo',
                    'sAlbumType' => 'photo_album',
                    'bCanDislike' => Phpfox::getService('mfox.like')->isTypeSupportDislike('photo'),
                    'iTimeStamp' =>  $aPhoto['time_stamp'],

                    'sTimeStamp' => date('l, F j, o', (int) $aPhoto['time_stamp']) . ' at ' . date('h:i a', (int) $aPhoto['time_stamp']),
                    'bCanEdit' => $this->canEditPhoto($aPhoto),
                    'bCanDelete' => $this->canDeletePhoto($aPhoto),
                );
            } else {
                // get thumb image
                $sThumbUrl = Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aPhoto['server_id'],
                        'path' => 'photo.url_photo',
                        'file' => $aPhoto['destination'],
                        'suffix' => MAX_SIZE_OF_USER_IMAGE_PHOTO,
                        'return_url' => true
                    )
                );

                $aResult[] = array(
                    'iPhotoId' => $aPhoto['photo_id'],
                    'sTitle' => $aPhoto['title'],
                    'sPhotoUrl' => $sThumbUrl
                );
            }
        }

        return $aResult;
    }

    public function getfullphotoslide($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->getfullphotoslide($aData);
        }

        return $this->fullphotoslide($aData);
    }

    /**
     * Input data:
     * + iLimitPhoto: int, optional.
     * + iAlbumId: int, optional.
     * + iCurrentPhotoId: int, required.
     * + sView: string, optional.
     * + iCategoryId: int, optional.
     * + bIsProfileUser: bool, optional. In profile.
     * + sModuleId: string, optional. Ex: "pages".
     * + iUserId: int, optional. In profile.
     *
     * Output data:
     * + iPhotoId: int.
     * + sTitle: string.
     * + bCanPostComment: bool.
     * + sPhotoUrl: string.
     * + fRating: float.
     * + iTotalVote: int.
     * + iTotalBattle: int.
     * + iAlbumId: int.
     * + sAlbumName: string.
     * + iTotalLike: int.
     * + iTotalDislike: int.
     * + bIsFeatured: bool.
     * + bIsCover: bool.
     * + iTotalView: int.
     * + iTotalComment: int.
     * + iTotalDownload: int.
     * + iAllowDownload: int.
     * + iIsSponsor: int.
     * + iOrdering: int.
     * + bIsProfilePhoto: bool.
     * + sFileName: string.
     * + sFileSize: string.
     * + sMimeType: string.
     * + sExtension: string.
     * + sDescription: string.
     * + iWidth: int.
     * + iHeight: int.
     * + sAlbumUrl: string.
     * + sAlbumTitle: string.
     * + iAlbumProfileId: int.
     * + bIsViewed: bool.
     * + aCategories: array.
     * + bCategoryList: bool.
     * + sOriginalDestination: string.
     * + bIsFriend: bool.
     * + iUserId: int.
     * + iProfilePageId: int.
     * + iUserServerId: int.
     * + sUserName: string.
     * + sFullName: string.
     * + iGender: int.
     * + sUserImage: string.
     * + bIsInvisible: bool.
     * + iUserGroupId: int.
     * + iLanguageId: int.
     * + iViewId: int.
     * + iTypeId: int.
     * + sModuleId: string.
     * + iGroupId: int.
     * + iParentUserId: int.
     * + iServerId: int.
     * + iMature: int.
     * + iAllowComment: int.
     * + iAllowRate: int.
     * + bIsLiked: bool.
     * + iPrivacy: int.
     * + iPrivacyComment: int.
     * + sTimeStamp: string.
     *
     * @see Mobile - API phpFox/Api V2.0
     * @see photo/albumslide
     *
     * @param array $aData
     * @return array
     */
    public function fullphotoslide($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->fullphotoslide($aData);
        }

        if (!empty($aData['iUserId']))
        {
            $aData['bIsUserProfile'] = true;
        }

        $aRows = array();

        if (empty($aData['sAction']))
        {
            $aData['iAmountOfPhoto'] = !empty($aData['iAmountOfPhoto']) ? floor((int) $aData['iAmountOfPhoto'] / 2) : 10;
            $aData['iPage'] = 1;

            // prev
            $aData['sAction'] = 'prev';
            $aPrevRows = $this->getSlides($aData);
            $aRows = $aPrevRows;

            // current
            $aRows[] = $this->getSlide($aData);

            // next
            $this->_oSearch->reset();

            $aData['sAction'] = 'next';
            $aNextRows = $this->getSlides($aData);
            foreach ($aNextRows as $aRow)
            {
                $aRows[] = $aRow;
            }
        }
        else if ($aData['sAction'] == 'prev')
        {
            $aData['iPage'] = $aData['iPrevPage'];
            $aRows = $this->getSlides($aData);
        }
        else if ($aData['sAction'] == 'next')
        {
            $aData['iPage'] = $aData['iNextPage'];
            $aRows = $this->getSlides($aData);
        }

        return $aRows;
    }

    public function onephotoslide($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->onephotoslide($aData);
        }

        $aRow = $this->getSlide($aData);

        return array($aRow);
    }

    public function getSlides($aData)
    {
        $this->_oReq->set(array(
            'view' => !empty($aData['sView']) ? $aData['sView'] : null,
            'page' => !empty($aData['iPage']) ? (int) $aData['iPage'] : 1,
            'show' => !empty($aData['iAmountOfPhoto']) ? (int) $aData['iAmountOfPhoto'] : null,
            'category' => !empty($aData['iCategoryId']) ? (int) $aData['iCategoryId'] : null,
            'search' => !empty($aData['sSearch']) ? $aData['sSearch'] : '',
            'sort' => !empty($aData['sOrder']) ? $aData['sOrder'] : null,
            'module_id' => !empty($aData['sModule']) ? $aData['sModule'] : null,
            'item_id' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : null,
            'profile' => (!empty($aData['bIsUserProfile']) && $aData['bIsUserProfile'] !== 'false') ? true : false,
            'profile_id' => !empty($aData['iUserId']) ? (int) $aData['iUserId'] : null,
            'in_detail' => (!empty($aData['iInDetails']) && $aData['iInDetails'] == 1) ? true : false,
            'parent_type' => !empty($aData['sParentType']) ? $aData['sParentType'] : null,
            'parent_id' => !empty($aData['iParentId']) ? (int) $aData['iParentId'] : null,
            'photo_id' => !empty($aData['iCurrentPhotoId']) ? (int) $aData['iCurrentPhotoId'] : null,
            'position' => !empty($aData['sAction']) ? $aData['sAction'] : null,
            'album_id' => !empty($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : null,
            'action_id' => !empty($aData['iActionId']) ? (int) $aData['iActionId'] : null,
        ));

        // Show all album photos
        if ($this->_oReq->get('parent_type') == 'album')
        {
            $this->_oReq->set('show', 1000);
        }

        // Custom parent
        if ($this->_oReq->get('parent_type') == 'pages')
        {
            $this->_oReq->set('module_id', $this->_oReq->get('parent_type'));
            $this->_oReq->set('item_id', $this->_oReq->get('parent_id'));
        }

        Phpfox::getUserParam('photo.can_view_photos', true);

        $iCategoryId = $this->_oReq->get('category', null);
        if (!empty($iCategoryId))
        {
            $_SESSION['photo_category'] = $iCategoryId;
        }
        else
        {
            $_SESSION['photo_category'] = '';
        }

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
            $aUser = Phpfox::getService('user')->get($this->_oReq->getInt('profile_id'));
        }

        // Used to control privacy 
        $bNoAccess = false;
        if ($bIsUserProfile)
        {
            if (!Phpfox::getService('user.privacy')->hasAccess($aUser['user_id'], 'photo.display_on_profile'))
            {
                $bNoAccess = true;
            }
        }

        if(isset($aUser) && $aUser['profile_page_id'] != 0)
        {
            $bIsUserProfile = false;

            $aParentModule = array(
                'module_id' => 'pages',
                'item_id' => $aUser['profile_page_id']
            );
            define('PHPFOX_IS_PAGES_VIEW', true);
        }

        if ($aParentModule !== null && $aParentModule['module_id'] == 'pages')
        {
            $bIsUserProfile = false;
            define('PHPFOX_IS_PAGES_VIEW', true);
        }

        $sCategory = null;
        $sView = $this->_oReq->get('view', false);

        // search
        $sSearch = $this->_oReq->get('search');
        if (!empty($sSearch))
        {
            $this->_oSearch->setCondition('AND photo.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"');
        }

        $sPosition = $this->_oReq->get('position');

        $sSort = 'photo.photo_id DESC';
        if ($sPosition == 'prev')
        {
            $sSort = 'photo.photo_id ASC';
        }

        $this->_oSearch->setSort($sSort);

        // custom condition
        $iPhotoId = $this->_oReq->getInt('photo_id');
        if ($sPosition == 'prev')
        {
            $this->_oSearch->setCondition('AND photo.photo_id > ' . $iPhotoId);
        }
        else if ($sPosition == 'next')
        {
            $this->_oSearch->setCondition('AND photo.photo_id < ' . $iPhotoId);
        }

        $iAlbumId = $this->_oReq->getInt('album_id');
        if (!empty($iAlbumId))
        {
            $this->_oSearch->setCondition('AND photo.album_id = ' . $iAlbumId);
        }

        if ($this->_oReq->get('parent_type') == 'feed')
        {
            $iActionId = $this->_oReq->getInt('action_id');
            if (!empty($iActionId))
            {
                $aPhotoIds = $this->getPhotoIdInFeed($iActionId);
                if (count($aPhotoIds))
                {
                    $this->_oSearch->setCondition('AND photo.photo_id IN (' . implode(',', $aPhotoIds) . ')');
                }
            }
        }

        $aBrowseParams = array(
            'module_id' => 'photo',
            'alias' => 'photo',
            'field' => 'photo_id',
            'table' => Phpfox::getT('photo'),
            'hide_view' => array('pending', 'my'),
            'service' => 'mfox.photo'
        );

        switch ($sView)
        {
            case 'pending':
                Phpfox::getUserParam('photo.can_approve_photos', true);
                $this->_oSearch->setCondition('AND photo.view_id = 1');
                break;
            case 'my':
                Phpfox::isUser(true);
                $this->_oSearch->setCondition('AND photo.user_id = ' . Phpfox::getUserId());
                break;
            default:
                if ($bIsUserProfile)
                {
                    $this->_oSearch->setCondition('AND photo.view_id ' . ($aUser['user_id'] == Phpfox::getUserId() ? 'IN(0,2)' : '= 0') . ' AND photo.group_id = 0 AND photo.type_id = 0 AND photo.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ') AND photo.user_id = ' . (int) $aUser['user_id']);
                }
                else
                {
                    if (defined('PHPFOX_IS_PAGES_VIEW'))
                    {
                        $this->_oSearch->setCondition('AND photo.view_id = 0 AND photo.module_id = \'' . Phpfox_Database::instance()->escape($aParentModule['module_id']) . '\' AND photo.group_id = ' . (int) $aParentModule['item_id'] . ' AND photo.privacy IN(%PRIVACY%)');
                    }
                    else
                    {
                        $this->_oSearch->setCondition('AND photo.view_id = 0 AND photo.group_id = 0 AND photo.type_id = 0 AND photo.privacy IN(%PRIVACY%)');
                    }
                }
                break;
        }

        if (!empty($iCategoryId))
        {
            $sCategory = $iCategory = $iCategoryId;
            $sWhere = 'AND pcd.category_id = ' . (int) $sCategory;

            if (!is_int($iCategory))
            {
                $iCategory = Phpfox::getService('photo.category')->getCategoryId($sCategory);

            }

            // Get sub-categories
            $aSubCategories = Phpfox::getService('photo.category')->getForBrowse($iCategory);

            if (!empty($aSubCategories) && is_array($aSubCategories))
            {
                $aSubIds = Phpfox::getService('photo.category')->extractCategories($aSubCategories);
                if (!empty($aSubIds))
                {
                    $sWhere = 'AND pcd.category_id IN (' . (int)$sCategory . ',' . join(',', $aSubIds) . ')';
                }
            }

            $this->_oSearch->setCondition($sWhere);
        }

        if ($this->_oReq->get('tag'))
        {
            if (($aTag = Tag_Service_Tag::instance()->getTagInfo('photo', $this->_oReq->get('tag'))))
            {
                $this->_oSearch->setCondition('AND tag.tag_text = \'' . Phpfox_Database::instance()->escape($aTag['tag_text']) . '\'');
            }
        }

        if ($sView == 'featured')
        {
            $this->_oSearch->setCondition('AND photo.is_featured = 1');
        }

        Phpfox::getService('photo.browse')->category($sCategory);

        if (!Phpfox::getParam('photo.display_profile_photo_within_gallery'))
        {
            $this->_oSearch->setCondition('AND photo.is_profile_photo = 0');
        }

        $this->_oBrowse->params($aBrowseParams)->execute();

        if ($bNoAccess == false)
        {
            $aPhotos = $this->_oBrowse->getRows();
            $iCnt = $this->_oBrowse->getCount();
        }
        else
        {
            $aPhotos = array();
            $iCnt = 0;
        }

        if ($sPosition == 'prev')
        {
            $aPhotos = array_reverse($aPhotos);
        }

        return $aPhotos;
    }

    public function getSlide($aData)
    {
        $this->_oReq->set(array(
            'view' => !empty($aData['sView']) ? $aData['sView'] : null,
            'page' => !empty($aData['iPage']) ? (int) $aData['iPage'] : 1,
            'show' => !empty($aData['iAmountOfPhoto']) ? (int) $aData['iAmountOfPhoto'] : null,
            'category' => !empty($aData['iCategoryId']) ? (int) $aData['iCategoryId'] : null,
            'search' => !empty($aData['sSearch']) ? $aData['sSearch'] : '',
            'sort' => !empty($aData['sOrder']) ? $aData['sOrder'] : null,
            'module_id' => !empty($aData['sModule']) ? $aData['sModule'] : null,
            'item_id' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : null,
            'profile' => (!empty($aData['bIsUserProfile']) && $aData['bIsUserProfile'] !== 'false') ? true : false,
            'profile_id' => !empty($aData['iUserId']) ? (int) $aData['iUserId'] : null,
            'in_detail' => (!empty($aData['iInDetails']) && $aData['iInDetails'] == 1) ? true : false,
            'parent_type' => !empty($aData['sParentType']) ? $aData['sParentType'] : null,
            'parent_id' => !empty($aData['iParentId']) ? (int) $aData['iParentId'] : null,
            'photo_id' => !empty($aData['iCurrentPhotoId']) ? (int) $aData['iCurrentPhotoId'] : null,
            'position' => !empty($aData['sAction']) ? $aData['sAction'] : null,
            'album_id' => !empty($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : null,
            'action_id' => !empty($aData['iActionId']) ? (int) $aData['iActionId'] : null,
        ));

        $iPhotoId = !empty($aData['iCurrentPhotoId']) ? (int) $aData['iCurrentPhotoId'] : null;
        $aPhoto = Phpfox::getService('photo')->getPhoto($iPhotoId, Phpfox::getUserId());

        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid")),
                'error_code' => 1
            );
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('photo', $aPhoto['photo_id'], $aPhoto['user_id'], $aPhoto['privacy'], $aPhoto['is_friend'], true))
        {
            return array(
                'error_message' =>html_entity_decode(Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time')),
                'error_code' => 1
            );
        }

        if (Phpfox::isUser() && Phpfox::isModule('notification'))
        {
            Phpfox::getService('notification.process')->delete('comment_photo', $iPhotoId, Phpfox::getUserId());
            Phpfox::getService('notification.process')->delete('photo_like', $iPhotoId, Phpfox::getUserId());
            Phpfox::getService('notification.process')->delete('photo_tag', $iPhotoId, Phpfox::getUserId());
        }

        $aRow = $this->prepareRow($aPhoto);

        return $aRow;
    }

    /**
     * INPUT
     * + iAlbumId: int, required.
     *
     * OUTPUT
     * + iAlbumId: int.
     * + bIsFriend: bool.
     * + sTitle: int.
     * + sDescription: string.
     * + sAlbumImageUrl: string.
     * + iUserId: int.
     * + sUserFullName: string.
     * + sUserImageUrl: string.
     * + iCategoryId: int.
     * + iCreationDate: int.
     * + iModifiedDate: int.
     * + iSearch: int
     * + sType: int
     * + iTotalView: int.
     * + iTotalLike: int.
     * + aUserLike array
     * + iTotalPhoto: int.
     * + bCanComment: int.
     * + bCanView: int
     * + bCanTag: int
     */
    public function albumview($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->albumview($aData);
        }

        if (!Phpfox::getUserParam('photo.can_view_photo_albums'))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_this_photo_albums"))
            );
        }

        if (!Phpfox::getUserParam('photo.can_view_photos'))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_photos"))
            );
        }

        if (!isset($aData['iAlbumId']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_this_photo_album"))
            );
        }

        if (Phpfox::isUser() && Phpfox::isModule('notification'))
        {
            Phpfox::getService('notification.process')->delete('comment_photo_album', $aData['iAlbumId'], Phpfox::getUserId());
            Phpfox::getService('notification.process')->delete('photo_album_like', $aData['iAlbumId'], Phpfox::getUserId());
        }

        $bIsProfilePictureAlbum = false;
        if (isset($aData['bIsUserProfile']) && $aData['bIsUserProfile'] == "true")
        {
            $bIsProfilePictureAlbum = true;
            $aAlbum = Phpfox::getService('photo.album')->getForProfileView($aData['iAlbumId']);
            $aAlbum['name'] =  Phpfox::getPhrase('photo.profile_pictures');
        }
        else
        {
            // Get the current album we are trying to view
            $aAlbum = Phpfox::getService('photo.album')->getForView($aData['iAlbumId']);
            if ($aAlbum['profile_id'] > 0)
            {
                $bIsProfilePictureAlbum = true;
                $aAlbum['name'] =  Phpfox::getPhrase('photo.profile_pictures');
            }
        }

        // Make sure this is a valid album
        if (!isset($aAlbum['album_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('photo.invalid_photo_album')
            );
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('photo_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'),
                'error_code' => 1
            );
        }

        // get cover photo
        $coverPhoto = $this->getCoverByAlbumID($aAlbum['album_id']);
        $sCoverImage = '';
        if(isset($coverPhoto['destination']) && strlen($coverPhoto['destination']) > 0){
            $sCoverImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $coverPhoto['server_id'],
                    'path' => 'photo.url_photo',
                    'file' => $coverPhoto['destination'],
                    'suffix' => '_500',
                    'return_url' => true,
                )
            );
        }

        //  get list of liked user
        $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('photo_album'
            , $aAlbum['album_id']
            , false
            , Phpfox::getParam('feed.total_likes_to_display'));
        $aUserLike = array();
        foreach($aLike['likes'] as $like){
            $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
        }

        $aUserDislike = array();
        $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('photo_album', $aAlbum['album_id'], $bGetCount = false);
        foreach($aDislike as $dislike){
            $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
        }

        // check bCanPostComment
        $bCanPostComment = false;
        if(Phpfox::getService('comment')->canPostComment($aAlbum['user_id'], $aAlbum['privacy_comment'])){
            $bCanPostComment = true;
        }
        // check bCanView
        $bCanView = false;
        if(Phpfox::getService('privacy')->check($sModule = '', $iItemId = '', $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], $bReturn = true)){
            $bCanView = true;
        }
        // check bCanTag
        $bCanTag = false;
        if ((Phpfox::getUserParam('photo.can_tag_own_photo') && $aAlbum['user_id'] == Phpfox::getUserId())
            || Phpfox::getUserParam('photo.can_tag_other_photos')){
            $bCanTag = true;
        }

        // sUserImage
        // $sUserImage = Phpfox::getLib('image.helper')->display(array(
        // 'user' => $aAlbum,
        // 'suffix' => MAX_SIZE_OF_USER_IMAGE,
        // 'max_height' => 50,
        // 'max_width' => 50,
        // 'return_url' => true
        // ));

        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aAlbum, '_50_square');

        return array(
            'bIsProfilePictureAlbum' => $bIsProfilePictureAlbum,
            'bIsFriend' => $aAlbum['is_friend'],
            'sModelType' => 'photo_album',
            'sModule'=>$aAlbum['module_id'],
            'iItemId'=>$aAlbum['group_id'],
            'bIsLiked' => $aAlbum['is_liked'],
            'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('photo_album', $aAlbum['album_id'], Phpfox::getUserId()),
            'iAlbumId' => $aAlbum['album_id'],
            'iProfilePageId' => $aAlbum['profile_page_id'],
            'iViewId' => $aAlbum['view_id'],
            'iPrivacy' => $aAlbum['privacy'],
            'iPrivacyComment' => $aAlbum['privacy_comment'],
            'iUserId' => $aAlbum['user_id'],
            'sName' => Phpfox::getLib('locale')->convert($aAlbum['name']),
            'iTotalPhoto' => $aAlbum['total_photo'],
            'iTotalComment' => Phpfox::getService('mfox.comment')->getCommentCount(array('sItemType' => 'photo_album', 'iItemId' => $aAlbum['album_id']), null),
            'iTotalLike' => count($aUserLike),
            'iTotalDislike' => $aAlbum['total_dislike'],
            'sDescription' => $aAlbum['description'],
            'sFullName' => $aAlbum['full_name'],
            'sTitle' => Phpfox::getLib('locale')->convert($aAlbum['name']),
            'sAlbumImageUrl' => $sCoverImage,
            'sUserFullName' => $aAlbum['full_name'],
            'sUserImageUrl' => $sUserImage,
            'iCategoryId' => null,
            'iCreationDate' => $aAlbum['time_stamp'],
            'iTimeStamp'=> intval($aAlbum['time_stamp']),
            'iModifiedDate' => $aAlbum['time_stamp_update'],
            'time_stamp_update' => null,
            'sType' => null,
            'iTotalView' => null,
            'aUserLike' => $aUserLike,
            'aUserDislike' => $aUserDislike,
            'bCanPostComment' => $bCanPostComment,
            'bCanView' => $bCanView,
            'bCanTag' => $bCanTag,
            'sViewPrivacy' => $aAlbum['privacy'],
            'sCommentPrivacy' => $aAlbum['privacy_comment'],
            'sUserImage' => $sUserImage,
            'bCanEdit' => $this->canEditAlbum($aAlbum),
            'bCanDelete' => $this->canDeleteAlbum($aAlbum),
        );
    }

    /**
     * List photos by albumID
     *
     * Input data:
     * + iAlbumId: int, required.
     * + iLastPhotoIdViewed:: int, optional.
     * + iAmountOfAlbum: int, optional.
     * + sType: string, optional ('wall','profile','message','blog') --> NOT USE
     * + sAction: string, optional ('new', 'more')
     *
     * Output Data:
     * SUCCESS:
     * + iPhotoId: int.
     * + sTitle: string.
     * + sPhotoUrl: string.
     * FAIL:
     * + error_code: int
     * + error_message: string
     * + result: int
     */
    public function listalbumphoto($aData)
    {

        if(!empty($aData['iLimit'])){
            $aData['iAmountOfPhoto'] =  $aData['iLimit'];
        }

        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->listalbumphoto($aData);
        }

        //  init
        if (!Phpfox::getUserParam('photo.can_view_photo_albums'))
        {
            return array(
                'error_code' => 1,
                'result' => 0,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_this_photo_albums"))
            );
        }
        if (!Phpfox::getUserParam('photo.can_view_photos'))
        {
            return array(
                'error_code' => 1,
                'result' => 0,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_photos"))
            );
        }

        if (!isset($aData['iPage'])){
            $aData['iPage'] = 1;
        }
        if ((int)$aData['iPage'] == 0){
            return array();
        }

        if (empty($aData['iAlbumId']) || !is_numeric($aData['iAlbumId']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.album_id_is_missing_or_invalid"))
            );
        }

        if (!empty($aData['iAmountOfPhoto']))
        {
            $aData['iAmountOfPhoto'] = (int)$aData['iAmountOfPhoto'];
            if ($aData['iAmountOfPhoto'] <= 0)
            {
                $aData['iAmountOfPhoto'] = self::GETPHOTO_PHOTO_LIMIT;
            }
        }
        else
        {
            $aData['iAmountOfPhoto'] = self::GETPHOTO_PHOTO_LIMIT;
        }

        // below action is same view album in default phpFox
        if (Phpfox::isUser() && Phpfox::isModule('notification'))
        {
            Phpfox::getService('notification.process')->delete('comment_photo_album', $aData['iAlbumId'], Phpfox::getUserId());
            Phpfox::getService('notification.process')->delete('photo_album_like', $aData['iAlbumId'], Phpfox::getUserId());
        }

        $iPageSize = $aData['iAmountOfPhoto'];
        $iLastPhotoIdViewed = isset($aData['iLastPhotoIdViewed']) ? (int) $aData['iLastPhotoIdViewed'] : 0;
        $iAlbumId = isset($aData['iAlbumId']) ? $aData['iAlbumId'] : 0;

        $bIsProfilePictureAlbum = false;
        if (isset($aData['bIsUserProfile']) && $aData['bIsUserProfile'] == 'true')
        {
            $iProfileId = isset($aData['iProfileId']) ? (int) $aData['iProfileId'] : 0;
            if ($iProfileId < 1)
            {
                return array(
                    'error_code' => 1,
                    'result' => 0,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.profile_id_is_not_valid"))
                );
            }

            $bIsProfilePictureAlbum = true;
            $aAlbum = Phpfox::getService('photo.album')->getForProfileView((int) $iProfileId);
            $aAlbum['name'] =  Phpfox::getPhrase('photo.profile_pictures');
        }
        else
        {
            // Get the current album we are trying to view
            $aAlbum = Phpfox::getService('photo.album')->getForView((int) $aData['iAlbumId']);
            if ($aAlbum['profile_id'] > 0)
            {
                $bIsProfilePictureAlbum = true;
                $aAlbum['name'] =  Phpfox::getPhrase('photo.profile_pictures');
            }
        }

        // Make sure this is a valid album
        if (!isset($aAlbum['album_id']))
        {
            return array(
                'error_code' => 1,
                'result' => 0,
                'error_message' =>  Phpfox::getPhrase('photo.invalid_photo_album')
            );
        }

        $aCallback = null;
        if (!empty($aAlbum['module_id']))
        {
            $aCallback = Phpfox::callback($aAlbum['module_id'] . '.getPhotoDetails', $aAlbum);
        }

        if (Phpfox::isModule('privacy'))
        {
            $bResult = Phpfox::getService('privacy')->check('photo_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true);

            if (!$bResult)
            {
                return array(
                    'error_code' => 1,
                    'result' => 0,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_this_photo_albums"))
                );
            }
        }

        // Setup the page data
        $iPage = (int)$aData['iPage'];

        if (isset($aData['sAction']) && $aData['sAction'] == self::ACTION_TYPE_NEW)
        {
            $sCond = $iLastPhotoIdViewed > 0 ? ' AND p.photo_id > ' . $iLastPhotoIdViewed : '';
        }
        else
        {
            $sCond = $iLastPhotoIdViewed > 0 ? ' AND p.photo_id < ' . $iLastPhotoIdViewed : '';
        }

        // Create the SQL condition array
        $aConditions = array();
        $aConditions[] = 'p.album_id = ' . $iAlbumId . ' ' . $sCond;

        // Get the photos based on the conditions
        list($iCnt, $aPhotos) = Phpfox::getService('photo')->get($aConditions, 'p.photo_id DESC', $iPage, $iPageSize);
        // check current page with total results 
        list($fromResult, $toResult, $isShowPrev, $pagePrev, $isShowNext, $pageNext) = Phpfox::getService('mfox.core')->caculatePaging((int)$iCnt, (int)$iPageSize, (int)$iPage - 1);
        if($pageNext == 0){
            return array();
        }


        $aResult = array();
        foreach ($aPhotos as $aPhoto)
        {
            $aResult[] = array(
                'iPhotoId' => $aPhoto['photo_id'],
                'sTitle' => $aPhoto['title'],
                'sPhotoUrl' => Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aPhoto['server_id'],
                        'path' => 'photo.url_photo',
                        'file' => $aPhoto['destination'],
                        'suffix' => '_500',
                        'return_url' => true,
                    )
                )
            );
        }

        return $aResult;
    }

    /**
     * Not support currently
     *
     * Input data:
     * + iPhotoId: int, required.
     * + sFeedback: string, optional.
     * + iReport: int, optional.
     *
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     * + message: string.
     *
     * @see Mobile - API phpFox/Api V2.0
     * @see photo/report
     *
     * @param array $aData
     * @return array
     */
    public function report($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->report($aData);
        }

        return array(
            'result' => 0,
            'error_code' => 1,
            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_yet"))
        );

        // ---------------------------------------------------------------
        if (!isset($aData['iPhotoId']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        /**
         * @var array
         */
        $aPhoto = Phpfox::getService('photo')->getForEdit($aData['iPhotoId']);
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid_or_has_been_deleted"))
            );
        }
        $oReport = Phpfox::getService('report');
        /**
         * @var array
         */
        $aVals = array(
            'type' => 'photo',
            'id' => $aData['iPhotoId']
        );

        if (isset($aData['sFeedback']) && !Phpfox::getLib('parse.format')->isEmpty($aData['sFeedback']))
        {
            $aVals['feedback'] = $aData['sFeedback'];
        }
        else
        {
            $aVals['feedback'] = '';

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
     * Input data:
     * + iPhotoId: int.
     *
     * Output data:
     * + result: int.
     * + message: string.
     * + sProfileImage: string.
     * + error_code: int.
     * + error_message: string.
     * + system_message: string.
     *
     */
    public function setprofile($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->setprofile($aData);
        }

        if (!isset($aData['iPhotoId']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_id_is_missing"))
            );
        }

        $aPhoto = Phpfox::getService('photo')->getForEdit($aData['iPhotoId']);
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid_or_has_been_deleted"))
            );
        }

        $iUserId = Phpfox::getService('mfox.auth')->hasAccess('photo', 'photo_id', $aData['iPhotoId'], 'photo.can_edit_own_photo', 'photo.can_edit_other_photo');
        if (!$iUserId)
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_set_profile_this_photo"))
            );
        }

        $bResult = Phpfox::getService('photo.process')->makeProfilePicture($aData['iPhotoId']);
        if ($bResult)
        {
            return array(
                'result' => 1,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.set_as_profile_photo_successfully")),
                'iPhotoId' => $aData['iPhotoId'],
                'sProfileImage' => Phpfox::getLib('image.helper')->display(array(
                        'server_id' => Phpfox::getUserBy('server_id'),
                        'title' => Phpfox::getUserBy('full_name'),
                        'path' => 'core.url_user',
                        'file' => Phpfox::getUserBy('user_image'),
                        'suffix' => '',
                        'return_url' => true,
                    )
                )
            );
        }
        else
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_set_profile_this_photo")),
                'system_message' => Phpfox_Error::get()
            );
        }
    }

    /**
     * Set cover for album
     *
     * INPUT
     * + iPhotoId: int, required
     *
     */
    public function setcover($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->setcover($aData);
        }

        if (!isset($aData['iPhotoId']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_id_is_missing"))
            );
        }

        $aPhoto = Phpfox::getService('photo')->getForEdit($aData['iPhotoId']);
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid_or_has_been_deleted"))
            );
        }

        $iUserId = Phpfox::getService('mfox.auth')->hasAccess('photo', 'photo_id', $aData['iPhotoId'], 'profile.can_change_cover_photo');
        if (!$iUserId)
        {
            return array(
                'error_code' => 1,
                'result' => 0,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_set_cover_this_photo"))
            );
        }

        $aAlbum = $this->getAlbumByPhotoID($aData['iPhotoId']);
        if(!isset($aAlbum['album_id']) || (int)$aAlbum['album_id'] <= 0){
            return array(
                'error_code' => 1,
                'result' => 0,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.album_is_not_available"))
            );
        }

        $bResult = Phpfox::getService('photo.album.process')->setCover($aAlbum['album_id'], $aData['iPhotoId']);
        if ($bResult)
        {
            return array(
                'result' => 1,
                'error_code' => 0,
                'error_message' => "",
                'iPhotoId' => $aData['iPhotoId']
            );
        }
        else
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get())
            );
        }
    }

    /**
     * Set cover for user
     *
     * INPUT
     * + iPhotoId: int, required
     *
     */
    public function __setcover($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->__setcover($aData);
        }

        if (!isset($aData['iPhotoId']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_id_is_missing"))
            );
        }

        $aPhoto = Phpfox::getService('photo')->getForEdit($aData['iPhotoId']);
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid_or_has_been_deleted"))
            );
        }

        $iUserId = Phpfox::getService('mfox.auth')->hasAccess('photo', 'photo_id', $aData['iPhotoId'], 'profile.can_change_cover_photo');
        if (!$iUserId)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_set_cover_this_photo"))
            );
        }

        $bResult = Phpfox::getService('user.process')->updateCoverPhoto($aData['iPhotoId']);
        if ($bResult)
        {
            return array(
                'result' => $bResult,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.set_cover_photo_successfully")),
                'sCoverImage' => Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aPhoto['server_id'],
                        'path' => 'photo.url_photo',
                        'file' => $aPhoto['destination'],
                        'suffix' => '_500',
                        'return_url' => true,
                    )
                )
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
     * INPUT
     * + iPhotoId: int, required.
     */
    public function delete($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->delete($aData);
        }

        if (!isset($aData['iPhotoId']))
        {
            return array(
                'error_code' => 1,
                'result' => 0,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        $aPhoto = Phpfox::getService('photo')->getForEdit($aData['iPhotoId']);
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'error_code' => 1,
                'result' => 0,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid_or_has_been_deleted"))
            );
        }

        if ($this->deletePhoto($aData['iPhotoId']))
        {
            return array(
                'result' => 1,
                'error_code' => 0,
                'error_message' => "",
                'iPhotoId' => $aData['iPhotoId']
            );
        }

        return array(
            'error_code' => 1,
            'result' => 0,
            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.can_not_delete_this_photo__maybe_you_do_not_have_permission_to_delete_it"))
        );
    }

    public function delete_photos($aData){
        foreach($aData['iPhotoIds'] as $id){
            $this->deletePhoto($id);
        }

        return array('error_code'=>0,'message'=>'Photo deleted!');
    }

    public function deletePhoto($iId, $bPass = false)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->deletePhoto($iId, $bPass);
        }

        // Get the image ID and full path to the image.
        $aPhoto = $this->database()->select('user_id, module_id, group_id, is_sponsor, is_featured, album_id, photo_id, destination, server_id, is_cover')
            ->from(Phpfox::getT('photo'))
            ->where('photo_id = ' . (int) $iId)
            ->execute('getRow');
        if (!isset($aPhoto['user_id']))
        {
            return false;
        }

        if ($aPhoto['module_id'] == 'pages' && Phpfox::getService('pages')->isAdmin($aPhoto['group_id']))
        {
            $bPass = true;
        }

        if ($bPass === false && !Phpfox::getService('mfox.auth')->hasAccess('photo', 'photo_id', $iId, 'photo.can_delete_own_photo', 'photo.can_delete_other_photos', $aPhoto['user_id']))
        {
            return false;
        }

        // Create the total file size var for all the images
        $iFileSizes = 0;
        // Make sure the original image exists
        if (!empty($aPhoto['destination']) && file_exists(Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], '')))
        {
            // Add to the file size var
            $iFileSizes += filesize(Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], ''));

            // Remove the image
            Phpfox_File::instance()->unlink(Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], ''));
        }

        // If CDN is in use, remove the original image, as done above
        if(Phpfox::getParam('core.allow_cdn') && $aPhoto['server_id'] > 0)
        {
            // Get the file size stored when the photo was uploaded
            $iFileSizes += $this->database()->select('file_size')
                ->from(Phpfox::getT('photo_info'))
                ->where('photo_id = ' . (int) $iId)
                ->execute('getField');

            Phpfox::getLib('cdn')->remove(Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], ''));
        }

        // Loop through all the other smaller images
        foreach(Phpfox::getParam('photo.photo_pic_sizes') as $iSize)
        {
            // Make sure the image exists
            if (!empty($aPhoto['destination']) && file_exists(Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], '_' . $iSize)))
            {
                // Add to the file size var
                $iFileSizes += filesize(Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], '_' . $iSize));

                // Remove the image
                Phpfox_File::instance()->unlink(Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], '_' . $iSize));
            }

            // If CDN is in use, remove the thumbnails there too
            if(Phpfox::getParam('core.allow_cdn') && $aPhoto['server_id'] > 0)
            {
                // Get the file size stored when the photo was uploaded
                $sTempUrl = Phpfox::getLib('cdn')->getUrl(Phpfox::getParam('photo.url_photo') . sprintf($aPhoto['destination'], '_' . $iSize));

                $aHeaders = get_headers($sTempUrl, true);
                if(preg_match('/200 OK/i', $aHeaders[0]))
                {
                    $iFileSizes += (int) $aHeaders["Content-Length"];
                }

                Phpfox::getLib('cdn')->remove(Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], '_' . $iSize));
            }
        }

        // Delete this entry from the database
        $this->database()->delete(Phpfox::getT('photo'), 'photo_id = ' . $aPhoto['photo_id']);
        $this->database()->delete(Phpfox::getT('photo_info'), 'photo_id = ' . $aPhoto['photo_id']);
        // delete the ratings for this photo
        $this->database()->delete(Phpfox::getT('photo_rating'), 'photo_id = ' . $aPhoto['photo_id']);
        // delete the photo tags
        $this->database()->delete(Phpfox::getT('photo_tag'), 'photo_id = ' . $aPhoto['photo_id']);
        // delete the category_data
        $this->database()->delete(Phpfox::getT('photo_category_data'), 'photo_id = ' . $aPhoto['photo_id']);
        // delete the battles
        $this->database()->delete(Phpfox::getT('photo_battle'), 'photo_1 = ' . $aPhoto['photo_id'] . ' OR photo_2 = ' . $aPhoto['photo_id']);

        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('photo', $iId) : null);
        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('comment_photo', $iId) : null);
        (Phpfox::isModule('tag') ? Phpfox::getService('tag.process')->deleteForItem($aPhoto['user_id'], $iId, 'photo') : null);

        // Update user space usage
        if ($iFileSizes > 0)
        {
            Phpfox::getService('user.space')->update($aPhoto['user_id'], 'photo', $iFileSizes, '-');
        }

        // Update user activity
        Phpfox::getService('user.activity')->update($aPhoto['user_id'], 'photo', '-');

        if ($aPhoto['album_id'] > 0)
        {
            Phpfox::getService('photo.album.process')->updateCounter($aPhoto['album_id'], 'total_photo', true);
        }

        //if deleting photo is cover, set other photo to cover
        if (isset($aPhoto['is_cover']) && $aPhoto['is_cover'] && isset($aPhoto['album_id']) && $aPhoto['album_id']){
            //Select random photo from this album
            $iRandomCoverPhotoId = $this->database()->select('photo_id')
                ->from(':photo')
                ->where('album_id=' . (int) $aPhoto['album_id'])
                ->order('rand()')
                ->execute('getSlaveField');
            $this->database()->update(':photo', ['is_cover' => 1], 'photo_id=' . (int) $iRandomCoverPhotoId);
        }

        if ($aPhoto['is_sponsor'] == 1)
        {
            $this->cache()->remove('photo_sponsored');
        }
        if ($aPhoto['is_featured'] == 1)
        {
            $this->cache()->remove('photo_featured');
        }
        return true;
    }

    /**
     * Edit photo information
     * INPUT
     * + iPhotoId: int, required.
     * + iAlbumId: int, optional, album to move photo to
     * + sTitle: string, optional
     * + sDescription: string, optional
     * + iOrder: int, optional
     */
    public function edit($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->edit($aData);
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_logged_in_users")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        if (!isset($aData['iPhotoId']))
        {
            return array(
                'error_code' => 1,
                'result' => 0,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_id_is_missing"))
            );
        }
        $aPhoto = Phpfox::getService('photo')->getForEdit($aData['iPhotoId']);
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'error_code' => 1,
                'result' => 0,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid"))
            );
        }

        $iUserId = Phpfox::getService('mfox.auth')->hasAccess('photo', 'photo_id', $aData['iPhotoId'], 'photo.can_edit_own_photo', 'photo.can_edit_other_photo');
        if ($iUserId)
        {
            if (!isset($aData['sTitle']) || Phpfox::getLib('parse.format')->isEmpty($aData['sTitle']))
            {
                return array(
                    'error_code' => 1,
                    'result' => 0,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.title_is_not_valid"))
                );
            }

            $aVals = array(
                'title' => $aData['sTitle'],
                'description' => isset($aData['sDescription']) ? $aData['sDescription'] : '',
                'tag_list' => isset($aData['sTagList']) ? $aData['sTagList'] : $aPhoto['tag_list'],
                'mature' => isset($aData['iMature']) ? (int) $aData['iMature'] : $aPhoto['mature'],
                'allow_rate' => isset($aData['bAllowRate']) ? (bool) $aData['bAllowRate'] : $aPhoto['allow_rate'],
                'allow_download' => isset($aData['bAllowDownload']) ? (bool) $aData['bAllowDownload'] : $aPhoto['allow_download'],
                'set_album_cover' => ((isset($aData['bSetAlbumCover']) && $aData['bSetAlbumCover'] === 'true') ? $aData['iPhotoId'] : null),
                'album_id' => isset($aData['iAlbumId']) ? $aData['iAlbumId'] : $aPhoto['album_id']
            );

            if ($aVals['mature'] > 2 || $aVals['mature'] < 0)
            {
                $aVals['mature'] = 0;
            }
            if (isset($aData['sCategory']))
            {
                $aTemp = explode(',', $aData['sCategory']);
                $aCategories = array();
                foreach ($aTemp as $iCategory)
                {
                    if (is_numeric($iCategory))
                    {
                        $aCategories[] = $iCategory;
                    }
                }
                $aVals['category_id'] = $aCategories;
            }
            else
            {
                $aCategories = array();
                foreach ($aPhoto['categories'] as $aCategory)
                {
                    $aCategories[] = $aCategory['category_id'];
                }
                $aVals['category_id'] = $aCategories;
            }

            // Fix bug duplicate cover album photo.
            $iMoveTo = isset($aData['iMoveTo']) && $aData['iMoveTo'] > 0 ? (int) $aData['iMoveTo'] : '';
            if ($iMoveTo == $aPhoto['album_id'])
            {
                $aVals['move_to'] = '';
            }
            else
            {
                Phpfox::getLib('database')->update(Phpfox::getT('photo'), array('is_cover' => '0'), 'photo_id = ' . (int) $aPhoto['photo_id']);

                $aVals['move_to'] = isset($aData['iMoveTo']) ? (int) $aData['iMoveTo'] : '';
            }

            $aVals['privacy'] = isset($aData['iPrivacy']) ? $aData['iPrivacy'] : $aPhoto['privacy'];
            $aVals['privacy_comment'] = isset($aData['iPrivacyComment']) ? $aData['iPrivacyComment'] : $aPhoto['privacy_comment'];

            if ($this->updatePhoto($iUserId, $aData['iPhotoId'], $aVals))
            {
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'error_message' => "",
                    'iPhotoId' => $aData['iPhotoId']
                );
            }

            return array(
                'error_code' => 1,
                'result' => 0,
                'error_message' => implode(' ', Phpfox_Error::get())
            );
        }

        return array(
            'error_code' => 1,
            'result' => 0,
            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_edit_this_photo"))
        );
    }

    /**
     * Updating a new photo. We piggy back on the add() method so we do not have to do the same code twice.
     *
     * @param int $iUserId User ID of the user that the photo belongs to.
     * @param array $aVals Array of the post data being passed to insert.
     * @param boolean $bAllowTitleUrl Set to true to allow the editing of the SEO url.
     *
     * @return int ID of the newly added photo or the ID of the current photo we are editing.
     */
    public function updatePhoto($iUserId, $iId, $aVals, $bAllowTitleUrl = false)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->updatePhoto($iUserId, $iId, $aVals, $bAllowTitleUrl);
        }

        $aVals['photo_id'] = $iId;

        return $this->addPhoto($iUserId, $aVals, true, $bAllowTitleUrl);
    }

    /**
     * Adding a new photo.
     *
     * @param int $iUserId User ID of the user that the photo belongs to.
     * @param array $aVals Array of the post data being passed to insert.
     * @param boolean $bIsUpdate True if we plan to update the entry or false to insert a new entry in the database.
     * @param boolean $bAllowTitleUrl Set to true to allow the editing of the SEO url.
     *
     * @return int ID of the newly added photo or the ID of the current photo we are editing.
     */
    public function addPhoto($iUserId, $aVals, $bIsUpdate = false, $bAllowTitleUrl = false)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->addPhoto($iUserId, $aVals, $bIsUpdate, $bAllowTitleUrl);
        }

        $oParseInput = Phpfox::getLib('parse.input');

        // Create the fields to insert.
        $aFields = array();

        // Make sure we are updating the album ID
        (!empty($aVals['album_id']) ? $aFields['album_id'] = 'int' : null);

        // Is this an update?
        if ($bIsUpdate)
        {
            // Make sure we only update the fields that the user is allowed to
            (Phpfox::getUserParam('photo.can_add_mature_images') ? $aFields['mature'] = 'int' : null);
            (Phpfox::getUserParam('photo.can_control_comments_on_photos') ? $aFields['allow_comment'] = 'int' : null);
            ((Phpfox::getUserParam('photo.can_add_to_rating_module') && Phpfox::getParam('photo.can_rate_on_photos')) ? $aFields['allow_rate'] = 'int' : null);
            (!empty($aVals['destination']) ? $aFields[] = 'destination' : null);
            $aFields['allow_download'] = 'int';
            $aFields['server_id'] = 'int';

            // Check if we really need to update the title
            if (!empty($aVals['title']))
            {
                $aFields[] = 'title';

                // Clean the title for any sneaky attacks
                $aVals['title'] = $oParseInput->clean($aVals['title'], 255);

                if (Phpfox::getParam('photo.rename_uploaded_photo_names'))
                {
                    $aFields[] = 'destination';
                    /**
                     * @var array
                     */
                    $aPhoto = $this->database()->select('destination')
                        ->from(Phpfox::getT('photo'))
                        ->where('photo_id = ' . $aVals['photo_id'])
                        ->execute('getRow');
                    /**
                     * @var string
                     */
                    $sNewName = preg_replace("/^(.*?)-(.*?)%(.*?)$/", "$1-" . str_replace('%', '', $aVals['title']) . "%$3", $aPhoto['destination']);

                    $aVals['destination'] = $sNewName;

                    Phpfox::getLib('file')->rename(Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], ''), Phpfox::getParam('photo.dir_photo') . sprintf($sNewName, ''));

                    // Create thumbnails with different sizes depending on the global param.
                    foreach(Phpfox::getParam('photo.photo_pic_sizes') as $iSize)
                    {
                        Phpfox::getLib('file')->rename(Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], '_' . $iSize), Phpfox::getParam('photo.dir_photo') . sprintf($sNewName, '_' . $iSize));
                    }
                }
            }
            /**
             * @var int
             */
            $iAlbumId = (int) (empty($aVals['move_to']) ? (isset($aVals['album_id']) ? $aVals['album_id'] : 0) : $aVals['move_to']);
            if (!empty($aVals['set_album_cover']))
            {
                $aFields['is_cover'] = 'int';
                $aVals['is_cover'] = '1';

                $this->database()->update(Phpfox::getT('photo'), array('is_cover' => '0'), 'album_id = ' . (int) $iAlbumId);
            }

            if (!empty($aVals['move_to']))
            {
                $aFields['album_id'] = 'int';
                $iOldAlbumId = $aVals['album_id'];
                $aVals['album_id'] = (int) $aVals['move_to'];
            }

            if (isset($aVals['privacy']))
            {
                $aFields['privacy'] = 'int';
                $aFields['privacy_comment'] = 'int';
            }

            // Update the data into the database.
            $this->database()->process($aFields, $aVals)->update(Phpfox::getT('photo'), 'photo_id = ' . (int) $aVals['photo_id']);

            // Check if we need to update the description of the photo
            $aFieldsInfo = array(
                'description'
            );
            if (!empty($aVals['description']))
            {
                // Clean the data before we add it into the database
                $aVals['description'] = $oParseInput->clean($aVals['description']);
            }

            (!empty($aVals['width']) ? $aFieldsInfo[] = 'width' : 0);
            (!empty($aVals['height']) ? $aFieldsInfo[] = 'height' : 0);

            // Check if we have anything to add into the photo_info table
            if (isset($aFieldsInfo))
            {
                $this->database()->process($aFieldsInfo, $aVals)->update(Phpfox::getT('photo_info'), 'photo_id = ' . (int) $aVals['photo_id']);
            }

            // Add tags for the photo
            if (Phpfox::isModule('tag') && isset($aVals['tag_list']) && !empty($aVals['tag_list']) && Phpfox::getUserParam('photo.can_add_tags_on_photos'))
            {
                Phpfox::getService('tag.process')->update('photo', $aVals['photo_id'], $iUserId, $aVals['tag_list']);
            }

            // Make sure if we plan to add categories for this image that there is something to add
            if (isset($aVals['category_id']) && count($aVals['category_id']))
            {
                // Loop thru all the categories
                $this->database()->delete(Phpfox::getT('photo_category_data'), 'photo_id = ' . (int) $aVals['photo_id']);
                foreach ($aVals['category_id'] as $iCategory)
                {
                    // Add each of the categories
                    Phpfox::getService('photo.category.process')->updateForItem($aVals['photo_id'], $iCategory);
                }
            }
            /**
             * @var int
             */
            $iId = $aVals['photo_id'];

            if (Phpfox::isModule('privacy') && isset($aVals['privacy']))
            {
                if ($aVals['privacy'] == '4')
                {
                    Phpfox::getService('privacy.process')->update('photo', $iId, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : array()));
                }
                else
                {
                    Phpfox::getService('privacy.process')->delete('photo', $iId);
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

            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->update('photo', $iId, $aVals['privacy'], $aVals['privacy_comment'], 0, $iUserId) : null);

            if (!empty($aVals['move_to']))
            {
                Phpfox::getService('photo.album.process')->updateCounter($iOldAlbumId, 'total_photo');
                Phpfox::getService('photo.album.process')->updateCounter($aVals['move_to'], 'total_photo');
            }
        }
        else
        {
            if (!empty($aVals['callback_module']))
            {
                $aVals['module_id'] = $aVals['callback_module'];
            }

            // Define all the fields we need to enter into the database
            $aFields['user_id'] = 'int';
            $aFields['parent_user_id'] = 'int';
            $aFields['type_id'] = 'int';
            $aFields['time_stamp'] = 'int';
            $aFields['server_id'] = 'int';
            $aFields['view_id'] = 'int';
            $aFields['group_id'] = 'int';
            $aFields[] = 'module_id';
            $aFields[] = 'title';

            if (isset($aVals['privacy']))
            {
                $aFields['privacy'] = 'int';
                $aFields['privacy_comment'] = 'int';
            }

            // Define all the fields we need to enter into the photo_info table
            $aFieldsInfo = array(
                'photo_id' => 'int',
                'file_name',
                'mime_type',
                'extension',
                'file_size' => 'int',
                'description'
            );

            // Clean and prepare the title and SEO title
            $aVals['title'] = $oParseInput->clean(rtrim(preg_replace("/^(.*?)\.(jpg|jpeg|gif|png)$/i", "$1", $aVals['name'])), 255);

            // Add the user_id
            $aVals['user_id'] = $iUserId;

            // Add the original server ID for LB.
            $aVals['server_id'] = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');

            // Add the time stamp.
            $aVals['time_stamp'] = PHPFOX_TIME;

            $aVals['view_id'] = (Phpfox::getUserParam('photo.photo_must_be_approved') ? '1' : '0');

            // Insert the data into the database.
            $iId = $this->database()->process($aFields, $aVals)->insert(Phpfox::getT('photo'));

            // Prepare the data to enter into the photo_info table
            $aInfo = array(
                'photo_id' => $iId,
                'file_name' => Phpfox::getLib('parse.input')->clean($aVals['name'], 100),
                'extension' => strtolower($aVals['ext']),
                'file_size' => $aVals['size'],
                'mime_type' => $aVals['type'],
                'description' => (empty($aVals['description']) ? null : $this->preParse()->prepare($aVals['description']))
            );

            // Insert the data into the photo_info table
            $this->database()->process($aFieldsInfo, $aInfo)->insert(Phpfox::getT('photo_info'));

            if (!Phpfox::getUserParam('photo.photo_must_be_approved'))
            {
                // Update user activity
                Phpfox::getService('user.activity')->update($iUserId, 'photo');
            }

            // Make sure if we plan to add categories for this image that there is something to add
            if (isset($aVals['category_id']) && count($aVals['category_id']))
            {
                // Loop thru all the categories
                foreach ($aVals['category_id'] as $iCategory)
                {
                    // Add each of the categories
                    Phpfox::getService('photo.category.process')->updateForItem($iId, $iCategory);
                }
            }

            if (isset($aVals['privacy']))
            {
                if ($aVals['privacy'] == '4')
                {
                    Phpfox::getService('privacy.process')->add('photo', $iId, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : array()));
                }
            }
        }

        // Return the photo ID#
        return $iId;
    }

    /**
     * INPUT
     * + $iAlbumId: int, required.
     */
    public function albumdelete($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->albumdelete($aData);
        }

        if (!isset($aData['iAlbumId']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.album_id_is_missing"))
            );
        }

        if ($this->deleteAlbum($aData['iAlbumId']))
        {
            return array(
                'result' => 1,
                'error_code' => 0,
                'error_message' => "",
                'iAlbumId' => $aData['iAlbumId']
            );
        }

        return array(
            'error_code' => 1,
            'error_message' => implode(' ', Phpfox_Error::get())
        );
    }

    public function deleteAlbum($iAlbumId)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->deleteAlbum($iAlbumId);
        }

        $aAlbum = $this->database()->select('album_id, user_id')
            ->from(Phpfox::getT('photo_album'))
            ->where('album_id = ' . (int) $iAlbumId)
            ->execute('getRow');

        if (!isset($aAlbum['album_id']))
        {
            return Phpfox_Error::set( Phpfox::getPhrase('photo.not_a_valid_photo_album_to_delete'));
        }

        if (!Phpfox::getService('mfox.auth')->hasAccess('photo_album', 'album_id', $iAlbumId, 'photo.can_delete_own_photo_album', 'photo.can_delete_other_photo_albums', $aAlbum['user_id']))
        {
            return Phpfox_Error::set( Phpfox::getPhrase('photo.you_do_not_have_sufficient_permission_to_delete_this_photo_album'));
        }

        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('photo_album', $aAlbum['album_id']) : null);

        $aPhotos = $this->database()->select('photo_id')
            ->from(Phpfox::getT('photo'))
            ->where('album_id = ' . $aAlbum['album_id'])
            ->execute('getRows');

        foreach ($aPhotos as $aPhoto)
        {
            Phpfox::getService('photo.process')->delete($aPhoto['photo_id']);
        }

        $this->database()->delete(Phpfox::getT('photo_album'), 'album_id = ' . $aAlbum['album_id']);
        $this->database()->delete(Phpfox::getT('photo_album_info'), 'album_id = ' . $aAlbum['album_id']);

        return true;
    }

    /**
     * INPUT
     * + iAlbumId: int, required.
     * + sTitle: int, optional.
     * + sDescription: string, optional.
     * + iPhotoID: int, optional, album cover --> phpFox not set cover album when editing
     * + sType: string, optional, in array ('wall','profile','message','blog'), default is null --> NOT USE
     * + iCategoryid: sstring, optional, use 0 by default --> NOT USE
     * + iSearch: string, optional, use 1 by default --> NOT USE
     * + sAuthView:  string, optional, in array(1,2,3,4,...),  'everyone' by default
     * + sAuthComment: string, optional, in array(1,2,3,4,...),  'everyone' by default.
     * + sAuthTag: string, optional --> NOT USE
     */
    public function albumedit($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->albumedit($aData);
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_logged_in_users")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        $iAlbumId = isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : 0;
        $aAlbum = Phpfox::getService('photo.album')->getForEdit($iAlbumId);

        if (!isset($aAlbum['album_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('photo.photo_album_not_found'),
                'result' => 0
            );
        }

        if (!isset($aData['sTitle']) || Phpfox::getLib('parse.format')->isEmpty($aData['sTitle']))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.name_is_not_valid")),
                'error_code' => 1,
                'result' => 0
            );
        }
        if (isset($aData['sAuthView'])){
            $aData['iPrivacy'] = $aData['sAuthView'];
        }
        if (isset($aData['sAuthComment'])){
            $aData['iPrivacyComment'] = $aData['sAuthComment'];
        }

        if (!isset($aData['sDescription']))
        {
            $aData['sDescription'] = '';
        }
        if (!isset($aData['iPrivacy']))
        {
            $aData['iPrivacy'] = 0;
        }
        if (!isset($aData['iPrivacyComment']))
        {
            $aData['iPrivacyComment'] = 0;
        }

        $aVals = array(
            'name' => $aData['sTitle'],
            'description' => isset($aData['sDescription']) ? $aData['sDescription'] : '',
            'privacy' => isset($aData['iPrivacy']) ? $aData['iPrivacy'] : 0,
            'privacy_comment' => isset($aData['iPrivacyComment']) ? $aData['iPrivacyComment'] : 0,
            'privacy_list' => isset($aData['sPrivacyList']) ? explode(',', $aData['sPrivacyList']) : null
        );

        if (Phpfox::getService('photo.album.process')->update($aAlbum['album_id'], $aVals))
        {
            return array(
                'result' => 1,
                'error_code' => 0,
                'error_message' => "",
                'iAlbumId' => $aAlbum['album_id']
            );
        }

        return array(
            'error_code' => 1,
            'error_message' => implode(' ', Phpfox_Error::get())
        );
    }

    /**
     * Get friend photos
     * + If iUserId is available, get photos of user whose iUserId belongs to
     * + Else get friends' photos of viewer
     *
     * Input Data:
     * + iAmountOfPhoto: int, optional.
     * + iLastPhotoIdViewed: int, optional.
     * + iUserId: int, optional.
     * + sType: string, optional ('wall','profile','message','blog')
     * + iCategoryId: int, optional.
     * + sTag: string, optional.
     * + sAction: string, optional ('new', 'more')
     *
     * Output Data:
     * SUCCESS:
     * + iPhotoId: int.
     * + sTitle: string.
     * + sPhotoUrl: string.
     * FAIL:
     * + error_code: int
     * + error_message: string
     * + result: int
     */
    public function friend($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->friend($aData);
        }

        if(isset($aData['iUserId']) && (int)$aData['iUserId'] > 0){
            // get photo of user with $iUserId
            $aData['bIsUserProfile'] = true;
        } else {
            //  get photo of viewerId's friends
            if(!Phpfox::isUser()){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_logged_in_users")),
                    'result' => 0
                );
            }
            $iUserID = Phpfox::getUserId();
            $aData['iViewerId'] = $iUserID;
            $aData['sView'] = self::GETPHOTO_TYPE_FRIEND;
        }

        return $this->__getPhoto($aData);
    }

    /**
     * Input data:
     * + iAmountOfPhoto: int, optional.
     * + iLastPhotoIdViewed: int, optional.
     * + bIsUserProfile: bool, optional.
     * + iUserId: int, optional.
     * + sView: string, optional.
     * + iCategory: int, optional.
     * + sType: string, optional.
     * + sModuleId: string, optional.
     * + iItemId: int, optional.
     * + sTag: string, optional.
     *
     * Output data:
     * + iPhotoId: int.
     * + sTitle: string.
     * + sPhotoUrl: string.
     *
     * @param array $aData
     * @return array
     */
    public function getMyLatestPhoto($iUserId)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->getMyLatestPhoto($iUserId);
        }

        $aData = array(
            'iAmountOfPhoto' => 1,
            'bIsUserProfile' => 'true',
            'iUserId' => $iUserId
        );
        return $this->__getPhoto($aData);
    }

    /**
     * Get user photos
     * Input Data:
     * + iAmountOfPhoto: int, optional.
     * + iLastPhotoIdViewed: int, optional.
     * + sType: string, optional ('wall','profile','message','blog').
     * + iCategoryId: int, optional.
     * + sTag: string, optional.
     * + sAction: string, optional ('new', 'more')
     *
     * Output Data:
     * SUCCESS:
     * + iPhotoId: int.
     * + sTitle: string.
     * + sPhotoUrl: string.
     * FAIL:
     * + error_code: int
     * + error_message: string
     * + result: int
     */
    public function my($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->my($aData);
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_logged_in_users")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        $aData['iViewerId'] = $iUserID;
        $aData['sView'] = self::GETPHOTO_TYPE_MY;

        return $this->__getPhoto($aData);
    }

    /**
     * Input data:
     * + iAlbumId: int, optional.
     * + sTitle: string, optional. --> NOT USE
     * + sDescription: string, optional. --> NOT USE
     * + $_FILE['Filedata']: photo file, required.
     *
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * + iPhotoId: int.
     * + sPhotoTitle: string.
     * + iAlbumId: int.
     *
     */
    public function upload($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->upload($aData);
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_logged_in_users")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        // If no images were uploaded lets get out of here.        
        if (!isset($_FILES['image']))
        {
            return array(
                'error_code' => 2,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_file")),
                'result' => 0
            );
        }

        // Make sure the user group is actually allowed to upload an image
        if (!Phpfox::getUserParam('photo.can_upload_photos'))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_upload_photo")),
                'result' => 0
            );
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
                Phpfox_Error::set( Phpfox::getPhrase('photo.uploading_photos_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
            }

            if (!Phpfox_Error::isPassed())
            {
                return array(
                    'error_code' => 1,
                    'error_message' => implode(' ', Phpfox_Error::get())
                );
            }
        }

        $oFile = Phpfox::getLib('file');
        $oImage = Phpfox::getLib('image');

        $aVals = array();
        if (isset($aData['privacy']))
        {
            $aVals['privacy'] = $aData['privacy'];
        }
        if (isset($aData['privacy_comment']))
        {
            $aVals['privacy_comment'] = $aData['privacy_comment'];
        }
        if (isset($aData['iAlbumId']))
        {
            $aVals['album_id'] = $aData['iAlbumId'];
        }
        else
        {
            $aVals['album_id'] = '';
        }

        // support for pages 
        $aParentModule = array(
            'module_id' => isset($aData['sModule']) ? $aData['sModule'] : '',
            'item_id' => isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0
        );
        if ((int)$aParentModule['item_id'] > 0){
            $aVals['callback_module'] = $aParentModule['module_id'];
            $aVals['callback_item_id'] = $aParentModule['item_id'];
            $aVals['group_id'] = $aParentModule['item_id'];
            $aVals['parent_user_id'] = $aParentModule['item_id'];
        }

        // support for uploading on feed of pages/event
        if($aData['sSubjectType'] == 'event'
            || $aData['sSubjectType'] == 'fevent'
            || $aData['sSubjectType'] == 'pages'
        ) {
            $aVals['callback_module'] = $aData['sSubjectType'];
            $aVals['callback_item_id'] = $aData['iSubjectId'];
        }

        $aVals['is_cover_photo'] = isset($aData['is_cover_photo']) ? (int) $aData['is_cover_photo'] : 0;

        $aVals['privacy'] = isset($aData['iPrivacy']) ? (int) $aData['iPrivacy'] : 0;
        $aVals['status_info'] = isset($aData['sStatusInfo']) ? $aData['sStatusInfo'] : '';
        $aVals['user_status'] = isset($aData['sUserStatus']) ? $aData['sUserStatus'] : '';
        $aVals['destination'] = '';
        $aVals['iframe'] = 1;
        $aVals['method'] = 'simple';
        $aVals['video_inline'] = 1;
        $aVals['video_title'] = isset($aData['sVideoTitle']) ? $aData['sVideoTitle'] : '';
        $bTwitterConnection = isset($aData['bTwitterConnection']) ? $aData['bTwitterConnection'] : 0;
        $aVals['connection']['twitter'] = $bTwitterConnection;
        $aVals['twitter_connection'] = $bTwitterConnection;
        $bFacebookConnection = isset($aData['bFacebookConnection']) ? $aData['bFacebookConnection'] : 0;
        $aVals['connection']['facebook'] = $bFacebookConnection;
        $aVals['facebook_connection'] = $bFacebookConnection;

        if (isset($aData['sAction']) && !empty($aData['sAction']))
        {
            $aVals['action'] = $aData['sAction'];
        }
        else
        {
            $aVals['action'] = '';
        }
        if (isset($aData['iPageId']))
        {
            $aVals['page_id'] = $aData['iPageId'];
        }
        else
        {
            $aVals['page_id'] = 0;
        }

        if (!is_array($aVals))
        {
            $aVals = array();
        }

        $bIsInline = false;
        if (isset($aVals['action']) && $aVals['action'] == 'upload_photo_via_share')
        {
            $bIsInline = true;
        }

        $oServicePhotoProcess = Phpfox::getService('photo.process');
        $aImages = array();
        $iFileSizes = 0;
        $iCnt = 0;
        $orgSize = null;
        $orgExt = null;

        if (!empty($aVals['album_id']))
        {
            $aAlbum = Phpfox::getService('photo.album')->getAlbum(Phpfox::getUserId(), $aVals['album_id'], true);
        }

        if (isset($aData['sStatusInfo']) && !empty($aData['sStatusInfo']))
        {
            $aVals['description'] = $aData['sStatusInfo'];
        }

        if(isset($aData['isPostStatus']) && $aData['isPostStatus']) { // in case post status we deal in another way
            // I duplicate it here because I do not know others do what which the ELSE case
            if ($_FILES['image']['error']  == UPLOAD_ERR_OK)
            {
                $iKey = 0;
                //$iLimitUpload = (Phpfox::getUserParam('photo.photo_max_upload_size') === 0 ? null : (Phpfox::getUserParam('photo.photo_max_upload_size') / 1024));
                $iLimitUpload = null;

                if ($aImage = $oFile->load('image', array('jpg', 'gif', 'png'), $iLimitUpload))
                {
                    $aVals['description'] = ($aVals['is_cover_photo']) ? null : $aVals['status_info'];
                    $aVals['type_id'] = ($aVals['is_cover_photo']) ? '2' : '1';

                    if ($iId = $oServicePhotoProcess->add(Phpfox::getUserId(), array_merge($aVals, $aImage)))
                    {
                        $iCnt++;
                        $aPhoto = Phpfox::getService('photo')->getForProcess($iId);

                        // Move the uploaded image and return the full path to that image.
                        $sFileName = $oFile->upload('image[' . $iKey . ']', Phpfox::getParam('photo.dir_photo'), (Phpfox::getParam('photo.rename_uploaded_photo_names') ? Phpfox::getUserBy('user_name') . '-' . $aPhoto['title'] : $iId), (Phpfox::getParam('photo.rename_uploaded_photo_names') ? array() : true));

                        // Get the original image file size.
                        $iFileSizes += filesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));

                        // Get the current image width/height
                        $aSize = getimagesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));
                        $orgSize = $aSize;
                        $orgExt = $aImage['ext'];

                        // Update the image with the full path to where it is located.
                        $oServicePhotoProcess->update(Phpfox::getUserId(), $iId, array(
                                'destination' => $sFileName,
                                'width' => $aSize[0],
                                'height' => $aSize[1],
                                'description' => $aVals['description'],
                                'server_id' => 0,
                                'allow_rate' => (empty($aVals['album_id']) ? '1' : '0')
                            )
                        );

                        // Assign vars for the template.
                        $aImages[] = array(
                            'photo_id' => $iId,
                            'server_id' => 0,
                            'destination' => $sFileName,
                            'description' => $aVals['description'],
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
            else
            {
                Phpfox_Error::set('Upload file error : ' . $sError);
            }
        } else {
            if ($_FILES['image']['error']  == UPLOAD_ERR_OK)
            {
                $iKey = 0;
                //$iLimitUpload = (Phpfox::getUserParam('photo.photo_max_upload_size') === 0 ? null : (Phpfox::getUserParam('photo.photo_max_upload_size') / 1024));
                $iLimitUpload = null;

                if ($aImage = $oFile->load('image', array('jpg', 'gif', 'png'), $iLimitUpload))
                {
                    if (isset($aVals['action']) && $aVals['action'] == 'upload_photo_via_share')
                    {
                        $aVals['description'] = ($aVals['is_cover_photo']) ? null : $aVals['status_info'];
                        $aVals['type_id'] = ($aVals['is_cover_photo']) ? '2' : '1';
                    }

                    if ($iId = $oServicePhotoProcess->add(Phpfox::getUserId(), array_merge($aVals, $aImage)))
                    {
                        $iCnt++;
                        $aPhoto = Phpfox::getService('photo')->getForProcess($iId);

                        // Move the uploaded image and return the full path to that image.
                        $sFileName = $oFile->upload('image[' . $iKey . ']', Phpfox::getParam('photo.dir_photo'), (Phpfox::getParam('photo.rename_uploaded_photo_names') ? Phpfox::getUserBy('user_name') . '-' . $aPhoto['title'] : $iId), (Phpfox::getParam('photo.rename_uploaded_photo_names') ? array() : true));

                        // Get the original image file size.
                        $iFileSizes += filesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));

                        // Get the current image width/height
                        $aSize = getimagesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));
                        $orgSize = $aSize;
                        $orgExt = $aImage['ext'];

                        // Update the image with the full path to where it is located.
                        $oServicePhotoProcess->update(Phpfox::getUserId(), $iId, array(
                                'destination' => $sFileName,
                                'width' => $aSize[0],
                                'height' => $aSize[1],
                                'server_id' => 0,
                                'allow_rate' => (empty($aVals['album_id']) ? '1' : '0')
                            )
                        );

                        // Assign vars for the template.
                        $aImages[] = array(
                            'photo_id' => $iId,
                            'server_id' => 0,
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
            else
            {
                Phpfox_Error::set('Upload file error : ' . $sError);
            }
        }

        $iFeedId = 0;

        // Make sure we were able to upload some images
        if (count($aImages))
        {
            if (defined('PHPFOX_IS_HOSTED_SCRIPT'))
            {
                unlink(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));
            }

            $aCallback = (!empty($aVals['callback_module']) ? (Phpfox::hasCallback($aVals['callback_module'], 'addPhoto') ? Phpfox::callback($aVals['callback_module'] . '.addPhoto', $aVals['callback_item_id']) : null) : null);


            $sAction = (isset($aVals['action']) ? $aVals['action'] : 'view_photo');

            // Have we posted an album for these set of photos?
            if (isset($aVals['album_id']) && !empty($aVals['album_id']))
            {
                $aAlbum = Phpfox::getService('photo.album')->getAlbum(Phpfox::getUserId(), $aVals['album_id'], true);

                // Set the album privacy
                Phpfox::getService('photo.album.process')->setPrivacy($aVals['album_id']);

                // Check if we already have an album cover
                if (!Phpfox::getService('photo.album.process')->hasCover($aVals['album_id']))
                {
                    // Set the album cover
                    Phpfox::getService('photo.album.process')->setCover($aVals['album_id'], $iId);
                }

                // Update the album photo count
                if (!Phpfox::getUserParam('photo.photo_must_be_approved'))
                {
                    Phpfox::getService('photo.album.process')->updateCounter($aVals['album_id'], 'total_photo', false, count($aImages));
                }

                $sAction = 'view_album';
            }

            // Update the user space usage
            Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'photo', $iFileSizes);

            if (isset($aVals['page_id']) && $aVals['page_id'] > 0)
            {
                if (Phpfox::getService('pages.process')->setCoverPhoto($aVals['page_id'], $iId, true))
                {
                    $aVals['is_cover_photo'] = 1;
                }
                else
                {
                    return array(
                        'error_code' => 1,
                        'error_message' => implode(' ', Phpfox_Error::get())
                    );
                }
            }

            $sExtra = '';
            if (!empty($aVals['start_year']) && !empty($aVals['start_month']) && !empty($aVals['start_day']))
            {
                $sExtra .= '&start_year= ' . $aVals['start_year'] . '&start_month= ' . $aVals['start_month'] . '&start_day= ' . $aVals['start_day'] . '';
            }

            $oImage = Phpfox::getLib('image');
            $iFileSizes = 0;
            $iGroupId = 0;
            $bProcess = false;
            $sCallbackModule = null;
            $iCallbackItemId = null;
            if ($aCallback !== null)
            {
                $sCallbackModule = $aCallback['module'];
                $iCallbackItemId = $aCallback['item_id'];
            }
            $iParentUserId = (isset($aVals['parent_user_id']) ? (int) $aVals['parent_user_id'] : 0);
            $bIsCoverPhoto = $aVals['is_cover_photo'];
            $iPageId = ((isset($aVals['page_id']) && $aVals['page_id'] > 0) ? $aVals['page_id'] : null);

            foreach ($aImages as $iKey => $aImage)
            {
                if ($aImage['completed'] == 'false')
                {
                    $aPhoto = Phpfox::getService('photo')->getForProcess($aImage['photo_id']);

                    if (isset($aPhoto['photo_id']))
                    {
                        if (Phpfox::getParam('core.allow_cdn'))
                        {
                            Phpfox::getLib('cdn')->setServerId($aPhoto['server_id']);
                        }

                        if ($aPhoto['group_id'] > 0)
                        {
                            $iGroupId = $aPhoto['group_id'];
                        }

                        $sFileName = $aPhoto['destination'];

                        // fix rotate bug
                        if($orgExt !== null){
                            if (($orgExt == 'jpg' || $orgExt == 'jpeg') && function_exists('exif_read_data'))
                            {
                                $exif = exif_read_data(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));//d($exif);die();
                                if(!empty($exif['Orientation'])){
                                    switch($exif['Orientation'])
                                    {
                                        case 1:
                                        case 2:
                                            break;
                                        case 3:
                                        case 4:
                                            // 90 degrees
                                            $oImage->rotate(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''), 'right');
                                            // 180 degrees
                                            $oImage->rotate(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''), 'right');
                                            break;
                                        case 5:
                                        case 6:
                                            // 90 degrees right
                                            $oImage->rotate(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''), 'right');
                                            break;
                                        case 7:
                                        case 8:
                                            // 90 degrees left
                                            $oImage->rotate(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''), 'left');
                                            break;
                                        default:
                                            break;
                                    }
                                }
                            }
                        }

                        foreach (Phpfox::getParam('photo.photo_pic_sizes') as $iSize)
                        {
                            // Create the thumbnail
                            if ($oImage->createThumbnail(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''), Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize, true, ((Phpfox::getParam('photo.enabled_watermark_on_photos') && Phpfox::getParam('core.watermark_option') != 'none') ? (Phpfox::getParam('core.watermark_option') == 'image' ? 'force_skip' : true) : false)) === false)
                            {
                                continue;
                            }

                            if (Phpfox::getParam('photo.enabled_watermark_on_photos'))
                            {
                                $oImage->addMark(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '_' . $iSize));
                            }

                            // Add the new file size to the total file size variable
                            $iFileSizes += filesize(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '_' . $iSize));

                            if (defined('PHPFOX_IS_HOSTED_SCRIPT'))
                            {
                                unlink(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '_' . $iSize));
                            }
                        }

                        // Get is_page variable.
                        $bIsPage = ((isset($aVals['page_id']) && !empty($aVals['page_id'])) ? 1 : 0);

                        if (Phpfox::getParam('photo.delete_original_after_resize') && $bIsPage != 1)
                        {
                            Phpfox::getLib('file')->unlink(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));
                        }
                        else if (Phpfox::getParam('photo.enabled_watermark_on_photos'))
                        {
                            $oImage->addMark(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''));
                        }

                        $aImages[$iKey]['completed'] = 'true';

                        break;
                    }
                }
            }

            // Update the user space usage
            Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'photo', $iFileSizes);

            $iNotCompleted = 0;
            foreach ($aImages as $iKey => $aImage)
            {
                if ($aImage['completed'] == 'false')
                {
                    $iNotCompleted++;
                }
            }


            if ($iNotCompleted === 0)
            {
                $aCallback = ($sCallbackModule ? (Phpfox::hasCallback($sCallbackModule, 'addPhoto') ? Phpfox::callback($sCallbackModule . '.addPhoto', $iCallbackItemId) : null) : null);

                $iFeedId = 0;

                if (false && !Phpfox::getUserParam('photo.photo_must_be_approved') && !$bIsCoverPhoto)
                {
                    if(isset($aData['isPostStatus']) && $aData['isPostStatus']) {
                        // create feed only for post status
                        // with other upload photo, we are using photo/postfeed after uploading done

                        if($aData['sSubjectType'] == 'user' && $aData['iSubjectId'] != Phpfox::getUserId()){
                            $iParentUserId = isset($aData['iSubjectId']) ? (int) $aData['iSubjectId'] : 0;
                            $aCallback = null;
                        } else if($aData['sSubjectType'] == 'event'
                            || $aData['sSubjectType'] == 'fevent'
                            || $aData['sSubjectType'] == 'pages'
                        ) {
                            $iParentUserId = $aData['iSubjectId'];
                        } else {
                            $iParentUserId = 0;
                        }


                        (Phpfox::isModule('feed') ? $iFeedId = Phpfox::getService('feed.process')->callback($aCallback)->add('photo', $aPhoto['photo_id'], $aPhoto['privacy'], $aPhoto['privacy_comment'], (int) $iParentUserId) : null);

                        if (count($aImages) && !$sCallbackModule)
                        {
                            $aExtraPhotos = array();

                            foreach ($aImages as $aImage)
                            {
                                if ($aImage['photo_id'] == $aPhoto['photo_id'])
                                {
                                    continue;
                                }

                                Phpfox::getLib('database')->insert(Phpfox::getT('photo_feed'), array(
                                        'feed_id' => $iFeedId,
                                        'photo_id' => $aImage['photo_id']
                                    )
                                );
                            }
                        }

                    }
                }

                // this next if is the one you will have to bypass if they come from sharing a photo in the activity feed.
                if ($sAction == 'upload_photo_via_share')
                {
                    if ($bIsCoverPhoto)
                    {
                        Phpfox::getService('user.process')->updateCoverPhoto($aImage['photo_id']);
                    }
                    else
                    {
                        $aFeeds = Phpfox::getService('feed')->get(Phpfox::getUserId(), $iFeedId);

                        if (!isset($aFeeds[0]))
                        {
                            Phpfox::addMessage( Phpfox::getPhrase('feed.this_item_has_successfully_been_submitted'));
                        }
                    }

                    Phpfox::addMessage( Phpfox::getPhrase('photo.photo_successfully_uploaded'));
                }
                else
                {
                    // Only display the photo block if the user plans to upload more pictures
                    if ($sAction == 'view_photo')
                    {
                        Phpfox::addMessage((count($aImages) == 1 ?  Phpfox::getPhrase('photo.photo_successfully_uploaded') :  Phpfox::getPhrase('photo.photos_successfully_uploaded')));
                    }
                    elseif ($sAction == 'view_album' && isset($aImages[0]['album']))
                    {
                        Phpfox::addMessage((count($aImages) == 1 ?  Phpfox::getPhrase('photo.photo_successfully_uploaded') :  Phpfox::getPhrase('photo.photos_successfully_uploaded')));
                    }
                    else
                    {
                        Phpfox::addMessage((count($aImages) == 1 ?  Phpfox::getPhrase('photo.photo_successfully_uploaded') :  Phpfox::getPhrase('photo.photos_successfully_uploaded')));
                    }
                }
                return array(
                    'result' => true,
                    'message' => Phpfox::getMessage(),
                    'iPhotoId' => $aPhoto['photo_id'],
                    'sType' => 'photo'
                );
            }
            else
            {
                $iProfileUserId = isset($aData['iProfileUserId']) ? (int) $aData['iProfileUserId'] : 0;
                if ($iProfileUserId > 0 && $iProfileUserId != Phpfox::getUserId() && Phpfox::isModule('notification'))
                {
                    Phpfox::getService('notification.process')->add('feed_comment_profile', $aPhoto['photo_id'], $iProfileUserId);
                }

                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.one_file_per_time_only"))
                );
            }

            return array(
                'error_code' => 0,
                'message' => Phpfox::getMessage(),
                'iPhotoId' => $aPhoto['photo_id'],
                'sType' => 'photo'
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
     * INPUT
     * + iPhotoId: int, required.
     *
     * OUTPUT
     * + iPhotoId: int.
     * + iAlbumId: int.
     * + sTitle: int.
     * + sDescription: string.
     * + sPhotoImageUrl: string.
     * + iCategoryId: int.
     * + iNextPhotoId: int.
     * + iPreviousPhotoId: int.
     * + iCreationDate: int.
     * + iModifiedDate: int.
     * + bCover: boolean.
     * + sType: int
     * + iTotalView: int.
     * + iTotalLike: int.
     * + aUserLike array
     * + bCanComment: int.
     * + bCanView: int
     * + bCanTag: int
     * + sFileName: string.
     * + sFileSize: string.
     * + sFileExtension: string.
     * + iUserId: int.
     * + sUserFullName: string.
     * + sUserImageUrl: string.
     */
    public function view($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->view($aData);
        }

        if (!isset($aData['iPhotoId']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_id_is_missing"))
            );
        }

        if (!Phpfox::getUserParam('photo.can_view_photos'))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_can_not_view_photos")),
                'result' => 0,
                'error_code' => 1
            );
        }

        $aCallback = null;
        $aPhoto = Phpfox::getService('photo')->getPhoto($aData['iPhotoId'], Phpfox::getUserId());
        $aFeed = array(
            'comment_type_id' => 'photo',
            'privacy' => $aPhoto['privacy'],
            'comment_privacy' => $aPhoto['privacy_comment'],
            'like_type_id' => 'photo',
            'feed_is_liked' => $aPhoto['is_liked'],
            'feed_is_friend' => $aPhoto['is_friend'],
            'item_id' => $aPhoto['photo_id'],
            'user_id' => $aPhoto['user_id'],
            'total_comment' => $aPhoto['total_comment'],
            'total_like' => $aPhoto['total_like'],
            'feed_link' => Phpfox::getLib('url')->permalink('photo', $aPhoto['photo_id'], $aPhoto['title']),
            'feed_title' => $aPhoto['title'],
            'feed_display' => 'view',
            'feed_total_like' => $aPhoto['total_like'],
            'report_module' => 'photo',
            'report_phrase' =>  Phpfox::getPhrase('photo.report_this_photo'));

        $aPhoto['bCanPostComment'] = Phpfox::getService('mfox.comment')->checkCanPostComment($aFeed);

        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid")),
                'error_code' => 1
            );
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('photo', $aPhoto['photo_id'], $aPhoto['user_id'], $aPhoto['privacy'], $aPhoto['is_friend'], true))
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'),
                'error_code' => 1
            );
        }

        $iCategory = (isset($aData['iCategoryId']) && $aData['iCategoryId'] > 0) ? $aData['iCategoryId'] : null;
        $iUserId = isset($aData['iUserId']) ? $aData['iUserId'] : 0;

        if (!empty($aPhoto['module_id']) && $aPhoto['module_id'] != 'photo')
        {
            $aCallback = Phpfox::callback($aPhoto['module_id'] . '.getPhotoDetails', $aPhoto);

            if ($aPhoto['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aCallback['item_id'], 'photo.view_browse_photos'))
            {
                return array(
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_view_this_item_due_to_privacy_settings")),
                    'error_code' => 1
                );
            }
        }

        $aPhotoStream = Phpfox::getService('photo')->getPhotoStream($aPhoto['photo_id'], (isset($aData['iAlbumId']) ? $aData['iAlbumId'] : '0'), $aCallback, $iUserId, $iCategory, $aPhoto['user_id']);

        if ($aPhoto)
        {
            // get thumb image
            $sThumbUrl = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aPhoto['server_id'],
                    'path' => 'photo.url_photo',
                    'file' => $aPhoto['destination'],
                    'suffix' => MAX_SIZE_OF_USER_IMAGE_PHOTO,
                    'return_url' => true
                )
            );
            //  get list of liked user
            $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('photo'
                , $aPhoto['photo_id']
                , false
                , Phpfox::getParam('feed.total_likes_to_display'));
            $aUserLike = array();
            foreach($aLike['likes'] as $like){
                $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
            }

            $aUserDislike = array();
            $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('photo', $aPhoto['photo_id'], $bGetCount = false);
            foreach($aDislike as $dislike){
                $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
            }

            // check bCanView
            $bCanView = false;
            if(Phpfox::getService('privacy')->check($sModule = '', $iItemId = '', $aPhoto['user_id'], $aPhoto['privacy'], $aPhoto['is_friend'], $bReturn = true)){
                $bCanView = true;
            }
            // check bCanTag
            $bCanTag = false;
            if ((Phpfox::getUserParam('photo.can_tag_own_photo') && $aPhoto['user_id'] == Phpfox::getUserId())
                || Phpfox::getUserParam('photo.can_tag_other_photos')){
                $bCanTag = true;
            }
            // get sUserImage
            $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aPhoto, '_50_square');

            return array(
                'iPhotoId' => $aPhoto['photo_id'],
                'sTitle' => $aPhoto['title'],
                'sPhotoUrl' => Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aPhoto['server_id'],
                        'path' => 'photo.url_photo',
                        'file' => $aPhoto['destination'],
                        'suffix' => '_1024',
                        'return_url' => true
                    )
                ),
                'fRating' => $aPhoto['total_rating'],
                'iTotalVote' => $aPhoto['total_vote'],
                'iTotalBattle' => $aPhoto['total_battle'],
                'iAlbumId' => $aPhoto['album_id'],
                'sAlbumName' => $aPhoto['album_title'],
                'iTotalLike' => $aPhoto['total_like'],
                'iTotalDislike' => $aPhoto['total_dislike'],
                'bIsFeatured' => $aPhoto['is_featured'],
                'bIsCover' => $aPhoto['is_cover'],
                'iTotalView' => $aPhoto['total_view'],
                'iTotalComment' => $aPhoto['total_comment'],
                'iTotalDownload' => $aPhoto['total_download'],
                'iAllowDownload' => $aPhoto['allow_download'],
                'iIsSponsor' => $aPhoto['is_sponsor'],
                'iOrdering' => $aPhoto['ordering'],
                'bIsProfilePhoto' => $aPhoto['is_profile_photo'],
                'sFileName' => $aPhoto['file_name'],
                'sFileSize' => $aPhoto['file_size'],
                'sMimeType' => $aPhoto['mime_type'],
                'sExtension' => $aPhoto['extension'],
                'sDescription' => $aPhoto['description'],
                'iWidth' => $aPhoto['width'],
                'iHeight' => $aPhoto['height'],
                'sAlbumUrl' => $aPhoto['album_url'],
                'sAlbumTitle' => $aPhoto['album_title'],
                'iAlbumProfileId' => $aPhoto['album_profile_id'],
                'bIsViewed' => $aPhoto['is_viewed'],
                'aCategories' => $aPhoto['categories'],
                'bCategoryList' => $aPhoto['category_list'],
                'sOriginalDestination' => $aPhoto['original_destination'],
                'iNextPhotoId' => isset($aPhotoStream['next']['photo_id']) ? $aPhotoStream['next']['photo_id'] : 0,
                'iPreviousPhotoId' => isset($aPhotoStream['previous']['photo_id']) ? $aPhotoStream['previous']['photo_id'] : 0,
                'bIsFriend' => (bool) $aPhoto['is_friend'],
                'iUserId' => $aPhoto['user_id'],
                'iProfilePageId' => $aPhoto['profile_page_id'],
                'iUserServerId' => $aPhoto['user_server_id'],
                'sUserName' => $aPhoto['user_name'],
                'sFullName' => $aPhoto['full_name'],
                'iGender' => $aPhoto['gender'],
                'sUserImage' => $sUserImage,
                'bIsInvisible' => $aPhoto['is_invisible'],
                'bCanPostComment' => $aPhoto['bCanPostComment'],
                'iUserGroupId' => $aPhoto['user_group_id'],
                'iLanguageId' => (int) $aPhoto['language_id'],
                'iViewId' => $aPhoto['view_id'],
                'iTypeId' => $aPhoto['type_id'],
                'iModuleId' => (int) $aPhoto['module_id'],
                'iGroupId' => (int) $aPhoto['group_id'],
                'iParentUserId' => $aPhoto['parent_user_id'],
                'iServerId' => $aPhoto['server_id'],
                'iMature' => $aPhoto['mature'],
                'iAllowComment' => $aPhoto['allow_comment'],
                'iAllowRate' => $aPhoto['allow_rate'],
                'bIsLiked' => isset($aPhoto['is_liked']) ? $aPhoto['is_liked'] : 0,
                'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('photo', $aPhoto['photo_id'], Phpfox::getUserId()),
                'iPrivacy' => $aPhoto['privacy'],
                'iPrivacyComment' => $aPhoto['privacy_comment'],
                'sPhotoImageUrl' => $sThumbUrl,
                'iCategoryId' => '',
                'iCreationDate' => $aPhoto['time_stamp'],
                'iModifiedDate' => $aPhoto['time_stamp'],
                'sType' => '',
                'iSearch' => '',
                'bCover' => ($aPhoto['is_cover']) ? true : false,
                'aUserLike' => $aUserLike,

                'bCanMakeProfilePicture'=>($aPhoto['user_id'] == Phpfox::getUserId())?1:0,

                'bCanComment' => $aPhoto['bCanPostComment'],
                'bCanView' => $bCanView,
                'bCanTag' => $bCanTag,
                'sUserFullName' => $aPhoto['full_name'],
                'sUserImageUrl' => $sUserImage,
                'sFileExtension' => $aPhoto['extension'],
                'sTimeStamp' => date('l, F j, o', (int) $aPhoto['time_stamp']) . ' at ' . date('h:i a', (int) $aPhoto['time_stamp'])
            );
        }
        else
        {
            return array();
        }
    }

    /**
     * Input data:
     * + iLastAlbumIdViewed: int, optional.
     * + sAction: string, optional ("more" or "new").
     * + iAmountOfAlbum: int, optional.
     * + sType: string, optional ('wall','profile','message','blog')
     * + sAction: string, optional ('new', 'more')
     *
     * OUTPUT DATA:
     * Success:
     * + iAlbumId: int.
     * + sAlbumImageURL: string.
     * + sName: string.
     * + iTotalPhoto: int.
     * + iTimeStamp: int.
     * + iTimeStampUpdate: int.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + aUserLike array
     * + iUserId: int.
     *
     * Failure:
     *
     */
    public function myalbum($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->myalbum($aData);
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_logged_in_users")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        $aData['iViewerId'] = $iUserID;
        $aData['sView'] = self::GETALBUM_TYPE_MY;

        return $this->__getAlbum($aData);
    }

    /**
     * If having iUserId -> get albums of user with iUserId
     * , otherwise get albums of viewer's friends
     *
     * Input data:
     * + iLastAlbumIdViewed: int, optional.
     * + sAction: string, optional ("more" or "new").
     * + iAmountOfAlbum: int, optional.
     * + sType: string, optional ('wall','profile','message','blog')
     * + sAction: string, optional ('new', 'more')
     * + iUserId: int, optional. Default 0 for all friends.
     *
     * OUTPUT DATA:
     * Success:
     * + iAlbumId: int.
     * + sAlbumImageURL: string.
     * + sName: string.
     * + iTotalPhoto: int.
     * + iTimeStamp: int.
     * + iTimeStampUpdate: int.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + aUserLike array
     * + iUserId: int.
     *
     * Failure:
     *
     */
    public function profilealbum($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->profilealbum($aData);
        }

        if(isset($aData['iUserId']) && (int)$aData['iUserId'] > 0){
            // get albums of user with iUserId
            $aData['bIsUserProfile'] = true;
        } else {
            //  get albums of viewer's friends
            if(!Phpfox::isUser()){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_logged_in_users")),
                    'result' => 0
                );
            }
            $iUserID = Phpfox::getUserId();
            $aData['iViewerId'] = $iUserID;
            $aData['sView'] = self::GETPHOTO_TYPE_FRIEND;
        }

        return $this->__getAlbum($aData);
    }

    /**
     * Create album with default photo_id = 0
     * INPUT
     * + sTitle: string, required, use "Untitled Album" by default
     * + sDescription: string, optional.
     * + sType: string, optional, in array ('wall','profile','message','blog'), default is null --> NOT USE
     * + iCategoryid: sstring, optional, use 0 by default --> NOT USE
     * + iSearch: string, optional, use 1 by default --> NOT USE
     * + sAuthView:  string, optional, in array(1,2,3,4,...),  'everyone' by default
     * + sAuthComment: string, optional, in array(1,2,3,4,...),  'everyone' by default.
     * + sAuthTag: string, optional --> NOT USE
     */
    public function albumcreate($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->albumcreate($aData);
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_logged_in_users")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        if (!isset($aData['sTitle']) || Phpfox::getLib('parse.format')->isEmpty($aData['sTitle']))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.name_is_not_valid")),
                'error_code' => 1,
                'result' => 0
            );
        }
        if (isset($aData['sAuthView'])){
            $aData['iPrivacy'] = $aData['sAuthView'];
        }
        if (isset($aData['sAuthComment'])){
            $aData['iPrivacyComment'] = $aData['sAuthComment'];
        }

        if (!isset($aData['sDescription']))
        {
            $aData['sDescription'] = '';
        }
        if (!isset($aData['iPrivacy']))
        {
            $aData['iPrivacy'] = 0;
        }
        if (!isset($aData['iPrivacyComment']))
        {
            $aData['iPrivacyComment'] = 0;
        }

        $aParentModule = array(
            'module_id' => isset($aData['sModule']) ? $aData['sModule'] : '',
            'item_id' => isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0
        );


        if('photo' == $aParentModule['module_id']){
            $aParentModule['module_id'] = '';
        }

        // Get the total number of albums this user has
        $iTotalAlbums = Phpfox::getService('photo.album')->getAlbumCount(Phpfox::getUserId());

        // Check if they are allowed to create new albums
        $bAllowedAlbums = (Phpfox::getUserParam('photo.max_number_of_albums') == 'null' ? true : (!Phpfox::getUserParam('photo.max_number_of_albums') ? false : (Phpfox::getUserParam('photo.max_number_of_albums') <= $iTotalAlbums ? false : true)));

        // Are they allowed to create new albums?
        if (!$bAllowedAlbums)
        {
            // They have reached their limit
            return array(
                'error_message' =>  Phpfox::getPhrase('photo.you_have_reached_your_limit_you_are_currently_unable_to_create_new_photo_albums'),
                'error_code' => 1,
                'result' => 0
            );
        }

        // Assigned the post vals
        $aVals = array(
            'name' => $aData['sTitle'],
            'description' => $aData['sDescription'],
        );
        if ((int)$aParentModule['item_id'] > 0)
        {
            $aVals['module_id'] = $aParentModule['module_id'];
            $aVals['item_id'] = $aParentModule['item_id'];
            $aVals['group_id'] = $aParentModule['item_id'];
        }
        else
        {
            $aVals['privacy'] = $aData['iPrivacy'];
            $aVals['privacy_comment'] = $aData['iPrivacyComment'];
            $aVals['privacy_list'] = isset($aData['sPrivacyList']) ? explode(',', $aData['sPrivacyList']) : null;
        }


        // Add the photo album
        if ($iId = Phpfox::getService('photo.album.process')->add($aVals))
        {
            return array(
                'result' => 1,
                'error_code' => 0,
                'error_message' => "",
                'iAlbumId' => $iId
            );
        }

        return array(
            'error_message' => implode(' ', Phpfox_Error::get()),
            'error_code' => 1,
            'result' => 0
        );
    }

    /**
     * Use for notification.
     * @param array $aNotification
     * @return array
     */
    public function doPhotoGetCommentNotification($aNotification)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->doPhotoGetCommentNotification($aNotification);
        }

        /**
         * @var array
         */
        $aPhoto = $this->database()->select('p.photo_id, p.title, u.user_id, u.gender, u.user_name, u.full_name')
            ->from(Phpfox::getT('photo'), 'p')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = p.user_id')
            ->where('p.photo_id = ' . (int) $aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aPhoto['photo_id']))
        {
            return array();
        }

        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aPhoto['user_id'] && !isset($aNotification['extra_users']))
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_commented_on_gender_photo_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aPhoto['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten($aPhoto['title'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        elseif ($aPhoto['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_commented_on_your_photo_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aPhoto['title'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_commented_on_span_class_drop_data_user_full_name_s_span_photo_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aPhoto['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aPhoto['title'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }

        return array(
            'link' => array('iPhotoId' => $aPhoto['photo_id'], 'sTitle' => $aPhoto['title']),
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog'),
            'sModule' => 'photo',
            'sMethod' => 'getCommentNotification'
        );
    }
    /**
     * Use for notification.
     * @param array $aNotification
     * @return array
     */
    public function doPhotoAlbumGetNotificationAlbum_Like($aNotification)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->doPhotoAlbumGetNotificationAlbum_Like($aNotification);
        }

        /**
         * @var array
         */
        $aAlbum = $this->database()->select('b.album_id, b.name, b.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('photo_album'), 'b')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->where('b.album_id = ' . (int) $aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aAlbum['album_id']))
        {
            return array();
        }

        $sAlbumName = Phpfox::getLib('locale')->convert($aAlbum['name']);

        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aAlbum['user_id'])
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_liked_gender_own_photo_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aAlbum['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten($sAlbumName, Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        elseif ($aAlbum['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_liked_your_photo_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($sAlbumName, Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_liked_span_class_drop_data_user_full_name_s_span_photo_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aAlbum['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($sAlbumName, Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }

        return array(
            'link' => array(
                'iAlbumId' => $aAlbum['album_id'],
                'sAlbumTitle' => $sAlbumName
            ),
            'message' => ($sPhrase),
            'sModule' => '',
            'sMethod' => '',
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }
    /**
     * Use for notification.
     * @param array $aNotification
     * @return array
     */
    public function doPhotoAlbumGetCommentNotificationAlbum($aNotification)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->doPhotoAlbumGetCommentNotificationAlbum($aNotification);
        }

        /**
         * @var array
         */
        $aAlbum = $this->database()->select('b.album_id, b.name, b.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('photo_album'), 'b')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->where('b.album_id = ' . (int) $aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aAlbum['album_id']))
        {
            return array();
        }

        $sAlbumName = Phpfox::getLib('locale')->convert($aAlbum['name']);

        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aAlbum['user_id'] && !isset($aNotification['extra_users']))
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_commented_on_gender_photo_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aAlbum['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten($sAlbumName, Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        elseif ($aAlbum['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_commented_on_your_photo_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($sAlbumName, Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_commented_on_span_class_drop_data_user_full_name_s_span_photo_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aAlbum['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($sAlbumName, Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }

        return array(
            'link' => array(
                'iAlbumId' => $aAlbum['album_id'],
                'sAlbumTitle' => $sAlbumName
            ),
            'message' => ($sPhrase),
            'sModule' => 'photo',
            'sMethod' => 'getCommentNotificationAlbum',
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }
    /**
     * Use for notification.
     * @param array $aNotification
     * @return boolean
     */
    public function doPhotoGetNotificationLike($aNotification)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->doPhotoGetNotificationLike($aNotification);
        }

        /**
         * @var array
         */
        $aRow = $this->database()
            ->select('b.photo_id, b.title, b.user_id, u.gender, u.full_name')
            ->from(Phpfox::getT('photo'), 'b')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = b.user_id')
            ->where('b.photo_id = ' . (int) $aNotification['item_id'])
            ->execute('getSlaveRow');

        if (!isset($aNotification['user_id']) || !isset($aRow['user_id']) || !isset($aRow['photo_id']))
        {
            return array();
        }
        $aRow['title'] = Phpfox::getLib('parse.output')->split($aRow['title'], 20);
        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_liked_gender_own_photo_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_liked_your_photo_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_liked_span_class_drop_data_user_full_name_s_span_photo_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aRow['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }

        return array(
            'link' => array('iPhotoId' => $aRow['photo_id'], 'sTitle' => $aRow['title']),
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }

    /**
     * Input data:
     * + iPhotoId: int, required.
     *
     * Output data:
     * + sImage: string.
     * + iTimestamp: int.
     * + sTimeConverted: string.
     * + iCommentId: int.
     * + iUserId: int.
     * + sFullName: string.
     * + sContent: string.
     * + iTotalLike: int.
     * + bIsLiked: bool.
     *
     * @see Mobile - API phpFox/Api V1.0
     * @see photo/list_all_comments
     *
     * @param array $aData
     * @return array
     */
    public function list_all_comments($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->list_all_comments($aData);
        }

        if (!isset($aData['iPhotoId']))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid")),
                'error_code' => 1
            );
        }
        if (!Phpfox::getUserParam('photo.can_view_photos'))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_can_not_view_photos")),
                'error_code' => 1
            );
        }
        /**
         * @var array
         */
        $aPhoto = Phpfox::getService('photo')->getPhoto($aData['iPhotoId'], Phpfox::getUserId());
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid_or_has_been_deleted"))
            );
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('photo', $aPhoto['photo_id'], $aPhoto['user_id'], $aPhoto['privacy'], $aPhoto['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        return Phpfox::getService('mfox.comment')->listallcomments(array('sType' => 'photo', 'iItemId' => $aPhoto['photo_id']));
    }

    /**
     * Check privacy comment.
     * @param int $iPhotoId
     * @return null|array Error message array.
     */
    public function checkPrivacyCommentOnPhoto($iPhotoId)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->checkPrivacyCommentOnPhoto($iPhotoId);
        }

        if (!Phpfox::getUserParam('photo.can_view_photos'))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_can_not_view_photos")),
                'error_code' => 1
            );
        }
        /**
         * @var array
         */
        $aPhoto = Phpfox::getService('photo')->getPhoto($iPhotoId, Phpfox::getUserId());
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid_or_has_been_deleted"))
            );
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('photo', $aPhoto['photo_id'], $aPhoto['user_id'], $aPhoto['privacy'], $aPhoto['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        // Check can post comment or not.
        if (!Phpfox::getService('mfox.comment')->checkCanPostCommentOnItem($aPhoto))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_this_item")));
        }
        return null;
    }

    /**
     * Check privacy on photo.
     * @param int $iPhotoId
     * @return null|array
     */
    public function checkPrivacyOnPhoto($iPhotoId)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->checkPrivacyOnPhoto($iPhotoId);
        }

        if (!Phpfox::getUserParam('photo.can_view_photos'))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_can_not_view_photos")),
                'error_code' => 1
            );
        }
        /**
         * @var array
         */
        $aPhoto = Phpfox::getService('photo')->getPhoto($iPhotoId, Phpfox::getUserId());
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid_or_has_been_deleted"))
            );
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('photo', $aPhoto['photo_id'], $aPhoto['user_id'], $aPhoto['privacy'], $aPhoto['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        return null;
    }
    /**
     * Using to check privacy comment on album.
     * @param int $iAlbumId
     * @param bool $bIsUserProfile
     * @return null|array
     */
    public function checkPrivacyCommentOnAlbum($iAlbumId, $bIsUserProfile = false)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->checkPrivacyCommentOnAlbum($iAlbumId, $bIsUserProfile);
        }

        if (!Phpfox::getUserParam('photo.can_view_photo_albums'))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_this_photo_albums"))
            );
        }
        if (!Phpfox::getUserParam('photo.can_view_photos'))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_photos"))
            );
        }
        if (Phpfox::isUser() && Phpfox::isModule('notification'))
        {
            Phpfox::getService('notification.process')->delete('comment_photo_album', $iAlbumId, Phpfox::getUserId());
            Phpfox::getService('notification.process')->delete('photo_album_like', $iAlbumId, Phpfox::getUserId());
        }
        /**
         * @var bool
         */
        if ($bIsUserProfile)
        {
            $aAlbum = Phpfox::getService('photo.album')->getForProfileView($iAlbumId);
        }
        else
        {
            // Get the current album we are trying to view
            $aAlbum = Phpfox::getService('photo.album')->getForView($iAlbumId);
        }
        // Make sure this is a valid album
        if (!isset($aAlbum['album_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('photo.invalid_photo_album')
            );
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('photo_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'),
                'error_code' => 1
            );
        }
        // Check can post comment or not.
        if (!Phpfox::getService('mfox.comment')->checkCanPostCommentOnItem($aAlbum))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_this_item")));
        }
        return null;
    }
    /**
     * Check privacy on album.
     * @param int $iAlbumId
     * @param bool $bIsUserProfile
     * @return null|array
     */
    public function checkPrivacyOnAlbum($iAlbumId, $bIsUserProfile = false)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->checkPrivacyOnAlbum($iAlbumId, $bIsUserProfile);
        }

        if (!Phpfox::getUserParam('photo.can_view_photo_albums'))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_this_photo_albums"))
            );
        }
        if (!Phpfox::getUserParam('photo.can_view_photos'))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_photos"))
            );
        }
        if (Phpfox::isUser() && Phpfox::isModule('notification'))
        {
            Phpfox::getService('notification.process')->delete('comment_photo_album', $iAlbumId, Phpfox::getUserId());
            Phpfox::getService('notification.process')->delete('photo_album_like', $iAlbumId, Phpfox::getUserId());
        }
        /**
         * @var bool
         */
        if ($bIsUserProfile)
        {
            $aAlbum = Phpfox::getService('photo.album')->getForProfileView($iAlbumId);
        }
        else
        {
            // Get the current album we are trying to view
            $aAlbum = Phpfox::getService('photo.album')->getForView($iAlbumId);
        }
        // Make sure this is a valid album
        if (!isset($aAlbum['album_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('photo.invalid_photo_album')
            );
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('photo_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'),
                'error_code' => 1
            );
        }
        return null;
    }
    /**
     * Push Cloud Message for photo.
     * @param int $iPhotoId
     */
    public function doPushCloudMessagePhoto($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->doPushCloudMessagePhoto($aData);
        }

        /**
         * @var int
         */
        $iPhotoId = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;

        /**
         * @var array
         */
        $aPhoto = Phpfox::getService('photo')->getPhoto($iPhotoId, Phpfox::getUserId());

        if (isset($aPhoto['user_id']) && $aPhoto['user_id'] != Phpfox::getUserId())
        {
            /**
             * @var int
             */
            $iPushId = Phpfox::getService('mfox.push')->savePush($aData, $aPhoto['user_id']);
            // Push cloud message.
            Phpfox::getService('mfox.cloudmessage')->send(array('message' => 'notification', 'iPushId' => $iPushId), $aPhoto['user_id']);
        }
    }
    /**
     * Push Cloud Message for photo album.
     * @param array $aData
     */
    public function doPushCloudMessagePhotoAlbum($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->doPushCloudMessagePhotoAlbum($aData);
        }

        /**
         * @var int
         */
        $iAlbumId = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;
        /**
         * @var bool
         */
        $bIsUserProfile = isset($aData['bIsUserProfile']) ? (bool) $aData['bIsUserProfile'] : false;
        if ($bIsUserProfile)
        {
            $aAlbum = Phpfox::getService('photo.album')->getForProfileView($iAlbumId);
        }
        else
        {
            // Get the current album we are trying to view
            $aAlbum = Phpfox::getService('photo.album')->getForView($iAlbumId);
        }

        if (isset($aAlbum['user_id']) && $aAlbum['user_id'] != Phpfox::getUserId())
        {
            /**
             * @var int
             */
            $iPushId = Phpfox::getService('mfox.push')->savePush($aData, $aAlbum['user_id']);
            // Push cloud message.
            Phpfox::getService('mfox.cloudmessage')->send(array('message' => 'notification', 'iPushId' => $iPushId), $aAlbum['user_id']);
        }
    }
    /**
     * Get activity feed for photo.
     * @param array $aItem
     * @param bool $aCallback
     * @param bool $bIsChildItem
     * @return boolean|array Array of feed.
     */
    public function doPhotoGetActivityFeed($aItem, $aCallback = null, $bIsChildItem = false)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->doPhotoGetActivityFeed($aItem, $aCallback, $bIsChildItem);
        }

        if ($aCallback === null)
        {
            $this->database()->select(Phpfox::getUserField('u', 'parent_') . ', ')->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = photo.parent_user_id');
        }

        if ($bIsChildItem)
        {
            $this->database()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user'), 'u2', 'u2.user_id = photo.user_id');
        }
        /**
         * @var array
         */
        $aRow = $this->database()->select('photo.*, l.like_id AS is_liked, pi.description, pfeed.photo_id AS extra_photo_id, pa.album_id, pa.name')
            ->from(Phpfox::getT('photo'), 'photo')
            ->join(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = photo.photo_id')
            ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'photo\' AND l.item_id = photo.photo_id AND l.user_id = ' . Phpfox::getUserId())
            ->leftJoin(Phpfox::getT('photo_feed'), 'pfeed', 'pfeed.feed_id = ' . (int) $aItem['feed_id'])
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = photo.album_id')
            ->where('photo.photo_id = ' . (int) $aItem['item_id'])
            ->execute('getSlaveRow');

        if ($bIsChildItem)
        {
            $aItem = $aRow;
        }

        if (!isset($aRow['photo_id']))
        {
            return false;
        }

        if ((defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'photo.view_browse_photos'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aRow['group_id'], 'photo.view_browse_photos'))
        )
        {
            return false;
        }
        /**
         * @var bool
         */
        $bIsPhotoAlbum = false;
        if ($aRow['album_id'])
        {
            $bIsPhotoAlbum = true;
        }
        /**
         * @var string
         */
        $sLink = Phpfox::permalink('photo', $aRow['photo_id'], $aRow['title']) . ($bIsPhotoAlbum ? 'albumid_' . $aRow['album_id'] : 'userid_' . $aRow['user_id']) . '/';

        if (($aRow['mature'] == 0 || (($aRow['mature'] == 1 || $aRow['mature'] == 2) && Phpfox::getUserId() && Phpfox::getUserParam('photo.photo_mature_age_limit') <= Phpfox::getUserBy('age'))) || $aRow['user_id'] == Phpfox::getUserId())
        {
            /**
             * @var string
             */
            $sImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aRow['server_id'],
                    'path' => 'photo.url_photo',
                    'file' => Phpfox::getService('photo')->getPhotoUrl(array_merge($aRow, array('full_name' => $aItem['full_name']))),
                    'suffix' => '_500',
                    'return_url' => true
                )
            );
        }
        else
        {
            /**
             * @var string
             */
            $sImage = Phpfox::getLib('image.helper')->display(array(
                    'theme' => 'misc/no_access.png',
                    'return_url' => true
                )
            );
        }

        /**
         * @var array
         */
        $aReturn = array(
            'sTypeId' => 'photo',
            'iUserId' => $aRow['user_id'],
            'sUsername' => $aRow['user_name'],
            'sFullName' => $aRow['full_name'],

            'sFeedTitle' => '',
            'sFeedImage' => $sImage,
            'sFeedStatus' => $aRow['description'],
            'sFeedLink' => $sLink,
            'iTotalComment' => $aRow['total_comment'],
            'iFeedTotalLike' => $aRow['total_like'],
            'iFeedIsLiked' => $aRow['is_liked'],
            'sFeedIcon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/photo.png', 'return_url' => true)),
            'iTimeStamp' => $aRow['time_stamp'],
            'Time' => date('l, F j, o', (int) $aRow['time_stamp']) . ' at ' . date('h:i a', (int) $aRow['time_stamp']),
            'TimeConverted' => Phpfox::getLib('date')->convertTime($aRow['time_stamp'], 'comment.comment_time_stamp'),
            'bEnableLike' => true,
            'sCommentTypeId' => 'photo',
            'sLikeTypeId' => 'photo',

            'aPhoto' => array(
                'iPhotoId' => $aRow['photo_id'],
                'sPhotoTitle' => $aRow['title'],
                'iAlbumId' => $aRow['album_id'],
                'sAlbumName' => $aRow['name']
            )
        );

        if ($aRow['module_id'] == 'pages')
        {
            $aRow['parent_user_id'] = '';
            $aRow['parent_user_name'] = '';
        }

        if (empty($aRow['parent_user_id']))
        {
            if ($aRow['album_id'])
            {
                $aReturn['sFeedStatus'] = '';
                $aReturn['sFeedInfo'] = strip_tags( Phpfox::getPhrase('feed.added_new_photos_to_gender_album_a_href_link_name_a', array('gender' => Phpfox::getService('user')->gender($aItem['gender'], 1), 'link' => Phpfox::permalink('photo.album', $aRow['album_id'], $aRow['name']), 'name' => Phpfox::getLib('parse.output')->shorten($aRow['name'], Phpfox::getParam('notification.total_notification_title_length')))));
                $aReturn['iAlbumId'] =  $aRow['album_id'];
                $aReturn['sAlbumName'] =  $aRow['name'];

            }
            else
            {
                $aReturn['sFeedInfo'] =  Phpfox::getPhrase('feed.shared_a_photo');
            }
        }

        if ($aCallback === null)
        {
            if (!empty($aRow['parent_user_name']) && !defined('PHPFOX_IS_USER_PROFILE') && empty($_POST))
            {
                $aReturn['sParentUser'] = Phpfox::getService('user')->getUserFields(true, $aRow, 'parent_');
            }

            if (!PHPFOX_IS_AJAX && defined('PHPFOX_IS_USER_PROFILE') && !empty($aRow['parent_user_name']) && $aRow['parent_user_id'] != Phpfox::getService('profile')->getProfileUserId())
            {
                $aReturn['bFeedMini'] = true;
                $aReturn['sFeedMiniContent'] = strip_tags( Phpfox::getPhrase('photo.full_name_posted_a_href_link_photo_a_photo_a_on_a_href_link_user_parent_full_name_a_s_a_href_link_wall_wall_a',array('full_name' => Phpfox::getService('user')->getFirstName($aItem['full_name']), 'link_photo' => Phpfox::permalink('photo', $aRow['photo_id'], $aRow['title']), 'link_user' => Phpfox::getLib('url')->makeUrl($aRow['parent_user_name']), 'parent_full_name' => $aRow['parent_full_name'], 'link_wall' => Phpfox::getLib('url')->makeUrl($aRow['parent_user_name']))));

                unset($aReturn['sFeedStatus'], $aReturn['sFeedImage']);
            }
        }

        return $aReturn;
    }

    // -----------------------------------------------------------
    // VERSION 3.03
    // -----------------------------------------------------------

    /**
     *
     * Ouput from other platform:
     * sType : not use currently ('wall','profile','message','blog')
     *
     * Output in function:
     * sView : my/pending/default
     *
     */
    public function __getPhoto($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->__getPhoto($aData);
        }

        $this->_oReq->set(array(
            'view' => !empty($aData['sView']) ? $aData['sView'] : '',
            'page' => !empty($aData['iPage']) ? (int) $aData['iPage'] : 1,
            'show' => !empty($aData['iAmountOfPhoto']) ? (int) $aData['iAmountOfPhoto'] : 10,
            'category' => !empty($aData['iCategoryId']) ? (int) $aData['iCategoryId'] : null,
            'search' => !empty($aData['sSearch']) ? $aData['sSearch'] : '',
            'sort' => !empty($aData['sOrder']) ? $aData['sOrder'] : '',
            'module_id' => !empty($aData['sModule']) ? $aData['sModule'] : null,
            'item_id' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : null,
            'profile' => (!empty($aData['bIsUserProfile']) && $aData['bIsUserProfile'] !== 'false') ? true : false,
            'profile_id' => !empty($aData['iUserId']) ? (int) $aData['iUserId'] : null,
            'in_detail' => (!empty($aData['iInDetails']) && $aData['iInDetails'] == 1) ? true : false,
            'parent_type' => !empty($aData['sParentType']) ? $aData['sParentType'] : null,
        ));

        // Show all album photos
        if ($this->_oReq->get('parent_type') == 'album')
        {
            $this->_oReq->set('show', 1000);
        }

        Phpfox::getUserParam('photo.can_view_photos', true);

        $iCategoryId = $this->_oReq->get('category', null);
        if (!empty($iCategoryId))
        {
            $_SESSION['photo_category'] = $iCategoryId;
        }
        else
        {
            $_SESSION['photo_category'] = '';
        }

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
            $aUser = Phpfox::getService('user')->get($this->_oReq->getInt('profile_id'));
        }

        // Used to control privacy 
        $bNoAccess = false;
        if ($bIsUserProfile)
        {
            if (!Phpfox::getService('user.privacy')->hasAccess($aUser['user_id'], 'photo.display_on_profile'))
            {
                $bNoAccess = true;
            }
        }

        if(isset($aUser) && $aUser['profile_page_id'] != 0)
        {
            $bIsUserProfile = false;

            $aParentModule = array(
                'module_id' => 'pages',
                'item_id' => $aUser['profile_page_id']
            );
            define('PHPFOX_IS_PAGES_VIEW', true);
        }

        if ($aParentModule !== null && $aParentModule['module_id'] == 'pages')
        {
            $bIsUserProfile = false;
            define('PHPFOX_IS_PAGES_VIEW', true);
        }
        if ($aParentModule !== null && $aParentModule['module_id'] == 'groups')
        {
            $bIsUserProfile = false;
            define('PHPFOX_IS_GROUP_VIEW', true);
        }
        $sCategory = null;
        $sView = $this->_oReq->get('view', false);

        // search
        $sSearch = $this->_oReq->get('search');
        if (!empty($sSearch))
        {
            $this->_oSearch->setCondition('AND photo.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"');
        }

        $sSort = '';
        switch ($this->_oReq->get('sort')) {
            case 'most_viewed':
                $sSort = 'photo.total_view DESC';
                break;
            case 'most_disscussed':
                $sSort = 'photo.total_comment DESC';
                break;
            case 'top_rated':
                $sSort = 'photo.total_rating DESC';
                break;
            case 'top_battle':
                $sSort = 'photo.total_battle DESC';
                break;
            default:
                $sSort = 'photo.photo_id DESC';
                break;
        }

        $this->_oSearch->setSort($sSort);

        $aBrowseParams = array(
            'module_id' => 'photo',
            'alias' => 'photo',
            'field' => 'photo_id',
            'table' => Phpfox::getT('photo'),
            'hide_view' => array('pending', 'my'),
            'service' => 'mfox.photo'
        );

        switch ($sView)
        {
            case 'pending':
                Phpfox::getUserParam('photo.can_approve_photos', true);
                $this->_oSearch->setCondition('AND photo.view_id = 1');
                break;
            case 'my':
                Phpfox::isUser(true);
                $this->_oSearch->setCondition('AND photo.user_id = ' . Phpfox::getUserId());
                break;
            default:
                if ($bIsUserProfile)
                {
                    $this->_oSearch->setCondition('AND photo.view_id ' . ($aUser['user_id'] == Phpfox::getUserId() ? 'IN(0,2)' : '= 0') . ' AND photo.group_id = 0 AND photo.type_id = 0 AND photo.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ') AND photo.user_id = ' . (int) $aUser['user_id']);
                }
                else
                {
                    if (defined('PHPFOX_IS_PAGES_VIEW') || defined('PHPFOX_IS_GROUP_VIEW'))
                    {
                        $this->_oSearch->setCondition('AND photo.view_id = 0 AND photo.module_id = \'' . Phpfox_Database::instance()->escape($aParentModule['module_id']) . '\' AND photo.group_id = ' . (int) $aParentModule['item_id'] . ' AND photo.privacy IN(%PRIVACY%)');
                    }
                    else
                    {
                        $this->_oSearch->setCondition('AND photo.view_id = 0 AND photo.group_id = 0 AND photo.type_id = 0 AND photo.privacy IN(%PRIVACY%)');
                    }
                }
                break;
        }

        if (!empty($iCategoryId))
        {
            $sCategory = $iCategory = $iCategoryId;
            $sWhere = 'AND pcd.category_id = ' . (int) $sCategory;

            if (!is_int($iCategory))
            {
                $iCategory = Phpfox::getService('photo.category')->getCategoryId($sCategory);

            }

            // Get sub-categories
            $aSubCategories = Phpfox::getService('photo.category')->getForBrowse($iCategory);

            if (!empty($aSubCategories) && is_array($aSubCategories))
            {
                $aSubIds = Phpfox::getService('photo.category')->extractCategories($aSubCategories);
                if (!empty($aSubIds))
                {
                    $sWhere = 'AND pcd.category_id IN (' . (int)$sCategory . ',' . join(',', $aSubIds) . ')';
                }
            }

            $this->_oSearch->setCondition($sWhere);
        }

        if ($this->_oReq->get('tag'))
        {
            if (($aTag = Tag_Service_Tag::instance()->getTagInfo('photo', $this->_oReq->get('tag'))))
            {
                $this->_oSearch->setCondition('AND tag.tag_text = \'' . Phpfox_Database::instance()->escape($aTag['tag_text']) . '\'');
            }
        }

        if ($sView == 'featured')
        {
            $this->_oSearch->setCondition('AND photo.is_featured = 1');
        }

        Phpfox::getService('photo.browse')->category($sCategory);

        if (!Phpfox::getParam('photo.display_profile_photo_within_gallery'))
        {
            $this->_oSearch->setCondition('AND photo.is_profile_photo = 0');
        }
        //die(d($this->_oSearch->getConditions()));
        $this->_oBrowse->params($aBrowseParams)->execute();

        if ($bNoAccess == false)
        {
            $aPhotos = $this->_oBrowse->getRows();
            $iCnt = $this->_oBrowse->getCount();
        }
        else
        {
            $aPhotos = array();
            $iCnt = 0;
        }

        return $aPhotos;
    }

    public function query()
    {
        $this->database()->select('pa.name AS album_name, pa.profile_id AS album_profile_id, ppc.name as category_name, ppc.category_id, ')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = photo.album_id')
            ->leftJoin(Phpfox::getT('photo_category_data'), 'ppcd', 'ppcd.photo_id = photo.photo_id')
            ->leftJoin(Phpfox::getT('photo_category'), 'ppc', 'ppc.category_id = ppcd.category_id')
            ->group('photo.photo_id');

        if (Phpfox::isModule('like'))
        {
            $this->database()->select('l.like_id as is_liked, adisliked.action_id as is_disliked, ')
                ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = "photo" AND l.item_id = photo.photo_id AND l.user_id = ' . Phpfox::getUserId() . '')
                ->leftJoin(Phpfox::getT('action'), 'adisliked', 'adisliked.action_type_id = 2 AND adisliked.item_id = photo.photo_id AND adisliked.user_id = ' . Phpfox::getUserId());
        }
    }

    public function processRows(&$aRows)
    {
        $aPhotos = $aRows;
        $aRows = array();

        foreach ($aPhotos as $aPhoto)
        {
            $aRows[] = $this->prepareRow($aPhoto);
        }
    }

    public function prepareRow($aPhoto)
    {
        // update album info
        if (empty($aPhoto['album_id']))
        {
            $aPhoto['album_url'] = 'view';
        }

        if ($aPhoto['album_id'] > 0)
        {
            if ($aPhoto['album_profile_id'] > 0)
            {
                $aPhoto['album_title'] =  Phpfox::getPhrase('photo.profile_pictures');
                $aPhoto['album_url'] = Phpfox::permalink('photo.album.profile', $aPhoto['user_id'], $aPhoto['user_name']);
            }
            else
            {
                $aPhoto['album_url'] = Phpfox::permalink('photo.album', $aPhoto['album_id'], $aPhoto['album_title']);
            }
        }

        $sPhotoUrl = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aPhoto['server_id'],
                'path' => 'photo.url_photo',
                'file' => $aPhoto['destination'],
                'suffix' => '_500',
                'return_url' => true
            )
        );

        $aRow = array(
            'iPhotoId' => $aPhoto['photo_id'],
            'iTimeStamp' => $aPhoto['time_stamp'],
            'sPhotoUrl' => $sPhotoUrl,
            'sTitle' => $aPhoto['title'],
            'sModelType' => 'photo',
        );

        if ($this->_oReq->get('in_detail') === true)
        {
            $aFeed = array(
                'comment_type_id' => 'photo',
                'privacy' => $aPhoto['privacy'],
                'comment_privacy' => $aPhoto['privacy_comment'],
                'like_type_id' => 'photo',
                'feed_is_liked' => $aPhoto['is_liked'],
                'feed_is_friend' => $aPhoto['is_friend'],
                'item_id' => $aPhoto['photo_id'],
                'user_id' => $aPhoto['user_id'],
                'total_comment' => $aPhoto['total_comment'],
                'total_like' => $aPhoto['total_like'],
                'feed_link' => Phpfox::getLib('url')->permalink('photo', $aPhoto['photo_id'], $aPhoto['title']),
                'feed_title' => $aPhoto['title'],
                'feed_display' => 'view',
                'feed_total_like' => $aPhoto['total_like'],
                'report_module' => 'photo',
                'report_phrase' =>  Phpfox::getPhrase('photo.report_this_photo'));

            //  get list of liked user
            $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('photo'
                , $aPhoto['photo_id']
                , false
                , Phpfox::getParam('feed.total_likes_to_display'));
            $aUserLike = array();
            foreach($aLike['likes'] as $like){
                $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
            }

            $aUserDislike = array();
            $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('photo', $aPhoto['photo_id'], $bGetCount = false);
            foreach($aDislike as $dislike){
                $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
            }

            $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aPhoto, '_50_square');

            $aRow = array_merge($aRow, array(
                'aCategories' => $aPhoto['categories'],
                'aUserDislike' => $aUserDislike,
                'aUserLike' => $aUserLike,
                'bCanDelete' => $this->canDeletePhoto($aPhoto),
                'bCanEdit' => $this->canEditPhoto($aPhoto),
                'bCanMakeProfilePicture'=> ($aPhoto['user_id'] == Phpfox::getUserId())?1:0,
                'bCanPostComment' => Phpfox::getService('mfox.comment')->checkCanPostComment($aFeed),
                'bCategoryList' => $aPhoto['category_list'],
                'bIsCover' => $aPhoto['is_cover'],
                'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('photo', $aPhoto['photo_id'], Phpfox::getUserId()),
                'bIsFeatured' => $aPhoto['is_featured'],
                'bIsFriend' => (bool) $aPhoto['is_friend'],
                'bIsInvisible' => $aPhoto['is_invisible'],
                'bIsLiked' => (isset($aPhoto['is_liked'])) ? true : false,
                'bIsProfilePhoto' => $aPhoto['is_profile_photo'],
                'bIsViewed' => $aPhoto['is_viewed'],
                'fRating' => (float)($aPhoto['average_rating']/2),
                'iAlbumId' => $aPhoto['album_id'],
                'iAlbumProfileId' => $aPhoto['album_profile_id'],
                'iAllowComment' => $aPhoto['allow_comment'],
                'iAllowDownload' => $aPhoto['allow_download'],
                'iAllowRate' => $aPhoto['allow_rate'],
                'iGender' => $aPhoto['gender'],
                'iGroupId' => (int) $aPhoto['group_id'],
                'iHeight' => $aPhoto['height'],
                'iIsSponsor' => $aPhoto['is_sponsor'],
                'iItemId' => $aPhoto['group_id'],
                'iLanguageId' => (int) $aPhoto['language_id'],
                'iMature' => $aPhoto['mature'],
                'iOrdering' => $aPhoto['ordering'],
                'iParentUserId' => $aPhoto['parent_user_id'],
                'iPrivacy' => $aPhoto['privacy'],
                'iPrivacyComment' => $aPhoto['privacy_comment'],
                'iProfilePageId' => $aPhoto['profile_page_id'],
                'iServerId' => $aPhoto['server_id'],
                'iTotalBattle' => $aPhoto['total_battle'],
                'iTotalComment' => Phpfox::getService('mfox.comment')->getCommentCount(array('sItemType' => 'photo', 'iItemId' => $aPhoto['photo_id']), null),
                'iTotalDislike' => $aPhoto['total_dislike'],
                'iTotalDownload' => $aPhoto['total_download'],
                'iTotalLike' => count($aUserLike),
                'iTotalView' => $aPhoto['total_view'],
                'iTotalVote' => $aPhoto['total_vote'],
                'iTypeId' => $aPhoto['type_id'],
                'iUserGroupId' => $aPhoto['user_group_id'],
                'iUserId' => $aPhoto['user_id'],
                'iUserServerId' => $aPhoto['user_server_id'],
                'iViewId' => $aPhoto['view_id'],
                'iWidth' => $aPhoto['width'],
                'sAlbumName' => $aPhoto['album_title'],
                'sAlbumTitle' => $aPhoto['album_title'],
                'sAlbumType' => 'photo_album',
                'sAlbumUrl' => $aPhoto['album_url'],
                'sDescription' => $aPhoto['description'],
                'sExtension' => $aPhoto['extension'],
                'sFileName' => $aPhoto['file_name'],
                'sFileSize' => $aPhoto['file_size'],
                'sFullName' => $aPhoto['full_name'],
                'sItemType' => 'photo',
                'sMimeType' => $aPhoto['mime_type'],
                'sModule' => $aPhoto['module_id'],
                'sModuleId' => $aPhoto['module_id'],
                'sOriginalDestination' => $aPhoto['original_destination'],
                'sTimeStamp' => date('l, F j, o', (int) $aPhoto['time_stamp']) . ' at ' . date('h:i a', (int) $aPhoto['time_stamp']),
                'sUserImage' => $sUserImage,
                'sUserImageUrl' => $sUserImage,
                'sUserName' => $aPhoto['user_name'],
            ));
        }

        return $aRow;
    }

    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Mfox_Service_Friend::instance()->queryJoin($bNoQueryFriend))
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = photo.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }

        if ($this->_oReq->get('tag'))
        {
            $this->database()->innerJoin(Phpfox::getT('tag'), 'tag', 'tag.item_id = photo.photo_id AND tag.category_id = \'photo\'');
        }

        if (isset($_SESSION['photo_category']) && $_SESSION['photo_category'] != '')
        {
            $this->database()->innerJoin(Phpfox::getT('photo_category_data'), 'pcd', 'pcd.photo_id = photo.photo_id');
            if (!$bIsCount)
            {
                $this->database()->group('photo.photo_id');
            }
        }
    }

    private function __getAlbum($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->__getAlbum($aData);
        }
        //  init 
        if (!isset($aData['iPage'])){
            $aData['iPage'] = 1;
        }
        if ((int)$aData['iPage'] == 0){
            return array();
        }

        $this->_oReq->set(array(
            'view' => !empty($aData['sView']) ? $aData['sView'] : '',
            'page' => !empty($aData['iPage']) ? (int) $aData['iPage'] : 1,
            'show' => !empty($aData['iAmountOfAlbum']) ? (int) $aData['iAmountOfAlbum'] : 10,
            'search' => !empty($aData['sSearch']) ? $aData['sSearch'] : '',
            'sort' => !empty($aData['sOrder']) ? $aData['sOrder'] : '',
            'module_id' => !empty($aData['sModule']) ? $aData['sModule'] : null,
            'item_id' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : null,
            'profile' => (!empty($aData['bIsUserProfile']) && $aData['bIsUserProfile'] !== 'false') ? true : false,
            'profile_id' => !empty($aData['iUserId']) ? (int) $aData['iUserId'] : null,
        ));

        // prepare view
        if ($this->_oReq->get('view') == 'my')
        {
            $this->_oReq->set('view', 'myalbums');
        }

        Phpfox::getUserParam('photo.can_view_photos', true);

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

        $aBrowseParams = array(
            'module_id' => 'photo.album',
            'alias' => 'pa',
            'field' => 'album_id',
            'table' => Phpfox::getT('photo_album'),
            'hide_view' => array('pending', 'myalbums'),
            'service' => 'mfox.photoalbum'
        );

        // search
        $sSearch = $this->_oReq->get('search');
        if (!empty($sSearch))
        {
            $this->_oSearch->setCondition('AND pa.name LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"');
        }

        $sSort = '';
        switch ($this->_oReq->get('sort')) {
            case 'most_disscussed':
                $sSort = 'pa.total_comment DESC';
                break;
            default:
                $sSort = 'pa.time_stamp DESC';
                break;
        }

        $this->_oSearch->setSort($sSort);

        if ($bIsUserProfile)
        {
            $this->_oSearch->setCondition('AND pa.view_id ' . ($aUser['user_id'] == Phpfox::getUserId() ? 'IN(0,2)' : '= 0') . ' AND pa.group_id = 0 AND pa.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ') AND pa.user_id = ' . (int) $aUser['user_id']);
        }
        else
        {
            if ($this->_oReq->get('view') == 'myalbums')
            {
                Phpfox::isUser(true);

                $this->_oSearch->setCondition('AND pa.user_id = ' . Phpfox::getUserId() . ' AND pa.profile_id = 0');
            }
            else
            {
                $this->_oSearch->setCondition('AND pa.view_id = 0 AND pa.privacy IN(%PRIVACY%) AND pa.total_photo > 0 AND pa.profile_id = 0');
            }
        }

        if ($aParentModule !== null && !empty($aParentModule['item_id']))
        {
            $this->_oSearch->setCondition('AND pa.module_id = \'' . $aParentModule['module_id']. '\' AND pa.group_id = ' . (int) $aParentModule['item_id']);
        }
        else
        {
            $this->_oSearch->setCondition("AND (pa.module_id IS NULL OR pa.module_id = '')");
        }

        $this->_oBrowse->params($aBrowseParams)->execute();

        $aAlbums = $this->_oBrowse->getRows();

        return $aAlbums;
    }

    public function getAlbumByID($iId)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->getAlbumByID($iId);
        }

        return $this->database()->select('pa.*, pai.*')
            ->from(Phpfox::getT('photo_album'), 'pa')
            ->join(Phpfox::getT('photo_album_info'), 'pai', 'pai.album_id = pa.album_id')
            ->where('pa.album_id = ' . (int) $iId)
            ->execute('getSlaveRow');
    }

    public function getPhotoByAlbumID($albumID){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->getPhotoByAlbumID($albumID);
        }

        return $this->database()->select('p.*')
            ->from(Phpfox::getT('photo_album'), 'pa')
            ->join(Phpfox::getT('photo'), 'p', 'p.album_id = pa.album_id AND pa.view_id = 0')
            ->where('pa.album_id = ' . (int) $albumID)
            ->execute('getSlaveRows');
    }

    public function getCoverByAlbumID($albumID){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->getCoverByAlbumID($albumID);
        }

        return $this->database()->select('p.destination, p.server_id, p.mature')
            ->from(Phpfox::getT('photo_album'), 'pa')
            ->leftJoin(Phpfox::getT('photo'), 'p', 'p.album_id = pa.album_id AND pa.view_id = 0 AND p.is_cover = 1')
            ->where('pa.album_id = ' . (int) $albumID)
            ->execute('getSlaveRows');
    }

    public function getAlbumByPhotoID($photoID){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->getAlbumByPhotoID($photoID);
        }

        return $this->database()->select('pa.*')
            ->from(Phpfox::getT('photo'), 'p')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')
            ->where('p.photo_id = ' . (int) $photoID)
            ->execute('getSlaveRow');
    }

    public function filter_album($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->filter_album($aData);
        }

        //Getting Featured ALBUMs
        if ($aData['sFilterBy'] == self::GETALBUM_TYPE_FEATURED)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_yet")),
                'result' => 0
            );
        } else if ($aData['sFilterBy'] == self::GETALBUM_TYPE_MY){
            if(!Phpfox::isUser()){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_logged_in_users")),
                    'result' => 0
                );
            }
            $iUserID = Phpfox::getUserId();

            $aData['iViewerId'] = $iUserID;
            $aData['sView'] = self::GETALBUM_TYPE_MY;
        }
        return $this->__getAlbum($aData);
    }

    public function filter($aData)
    {


        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->filter($aData);
        }

        if($aData['sParentType'] == 'album'){
            $aData['iAmountOfPhoto'] = 1000;
            $aData['iLimit'] = 1000;
        }


        if ($aData['sFilterBy'] == self::GETPHOTO_TYPE_FEATURED)
        {
            $aData['iFeatured'] = 1;
            $aData['sView'] = self::GETPHOTO_TYPE_FEATURED;
        } else if ($aData['sFilterBy'] == self::GETPHOTO_TYPE_MY){
            if(!Phpfox::isUser()){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_logged_in_users")),
                    'result' => 0
                );
            }
            $iUserID = Phpfox::getUserId();

            $aData['iViewerId'] = $iUserID;
            $aData['sView'] = self::GETPHOTO_TYPE_MY;
        }
        return $this->__getPhoto($aData);
    }

    public function categories(){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->categories();
        }

        // init
        // process 
        $categories = Phpfox::getService('photo.category')->getForBrowse();
        $result = array();
        foreach($categories as $cat){
            $sub = array();
            foreach($cat['sub'] as $catSub){
                $sub[] = array(
                    'iId' => $catSub['category_id']
                , 'sName' => html_entity_decode(Phpfox::getLib('locale')->convert(Phpfox::isPhrase($catSub['name'])?  _p($catSub['name']) : $catSub['name'] ))
                );
            }
            $result[] = array(
                'iId' => $cat['category_id']
            , 'sName' => html_entity_decode(Phpfox::getLib('locale')->convert(Phpfox::isPhrase($cat['name']) ? _p($cat['name']) : $cat['name']))
            , 'sub' => $sub
            );
        }
        // end
        return $result;
    }

    /**
     * Input data:
     * + sPhotoIds: string, required.
     * + iAlbumId: int, required.
     *
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + iActionId: int.
     */
    public function postfeed($aData)
    {
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->postfeed($aData);
        }

        if (isset($aData['iAlbumId']))
        {
            $aAlbum = $this->getAlbumByID($aData['iAlbumId']);
            if (!isset($aAlbum['album_id']))
            {
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.album_is_not_valid"))
                );
            }

            $aData['sSubjectType'] = $aAlbum['module_id'];
            $aData['iSubjectId'] = $aAlbum['group_id'];
        }

        if (!isset($aData['sPhotoIds']))
        {
            return array(
                'error_code' => 1,
                'error_element' => 'sPhotoIds',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        // process
        if (!Phpfox::getUserParam('photo.photo_must_be_approved') && Phpfox::isModule('feed')){
            $aCallback = (!empty($aData['sSubjectType'])
                ? (Phpfox::hasCallback($aData['sSubjectType'], 'addPhoto')
                    ? Phpfox::callback($aData['sSubjectType'] . '.addPhoto', $aData['iSubjectId'])
                    : null)
                : null);

            $sCallbackModule = null;
            $iCallbackItemId = null;
            if ($aCallback !== null)
            {
                $sCallbackModule = $aCallback['module'];
                $iCallbackItemId = $aCallback['item_id'];
            }
            $aCallback = ($sCallbackModule ? (Phpfox::hasCallback($sCallbackModule, 'addPhoto') ? Phpfox::callback($sCallbackModule . '.addPhoto', $iCallbackItemId) : null) : null);

            if($aData['sSubjectType'] == 'user'){
                $iParentUserId = isset($aData['iSubjectId']) ? (int) $aData['iSubjectId'] : 0;
                $aCallback = null;

                if($iParentUserId == Phpfox::getUserId()){
                    $iParentUserId =  0;
                }
            }

            if($aData['sSubjectType'] == 'event' || $aData['sSubjectType'] == 'fevent' || $aData['sSubjectType'] == 'pages' || $aData['sSubjectType'] == 'directory'){
                $iParentUserId = isset($aData['iSubjectId']) ? (int) $aData['iSubjectId'] : 0;
            }

            $ids = explode(',', $aData['sPhotoIds']);
            $photoPrivacy = 0;
            $photoPrivacyComment = 0;
            if(count($ids) > 0)
            {
                $lastID = count($ids) - 1 ;
                $aPhoto = array(
                    'photo_id' => $ids[$lastID]
                , 'privacy' => $photoPrivacy
                , 'privacy_comment' => $photoPrivacyComment
                );

                // support multiple photo upload
                if(isset($aData['isPostStatus']) && $aData['isPostStatus']){
                    // do not add feed
                    return array(
                        'iPhotoId'=>implode(',',$ids),
                    );
                }else{
                    (Phpfox::isModule('feed') ? $iFeedId = Phpfox::getService('feed.process')
                        ->callback($aCallback)
                        ->add('photo'
                            , $aPhoto['photo_id']
                            , $aPhoto['privacy']
                            , $aPhoto['privacy_comment']
                            , (int) $iParentUserId) : null
                    );

                    if (!$sCallbackModule)
                    {
                        foreach($ids as $photo_id){
                            if($ids[$lastID] == $photo_id){
                                continue;
                            }
                            Phpfox::getLib('database')->insert(Phpfox::getT('photo_feed'), array(
                                    'feed_id' => $iFeedId,
                                    'photo_id' => $photo_id
                                )
                            );
                        }
                    }
                    return array(
                        'error_code' => 0,
                        'error_message' => "",
                        'iActionId' => $iFeedId
                    );
                }
            } else {
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.please_provide_list_of_photo_id"))
                );
            }
        }

        // end         
    }

    /**
     * form add
     */
    public function formadd($aData){
        if($this->isAdvancedModule()){
            return Phpfox::getService('mfox.advancedphoto')->formadd($aData);
        }

        $response  =  array(
            'view_options'=> Phpfox::getService('mfox.privacy')->privacy($aData),
            'comment_options'=> Phpfox::getService('mfox.privacy')->privacycomment($aData),
            'category_options'=> $this->categories($aData),
        );

        $iValue = Phpfox::getService('user.privacy')->getValue('photo.default_privacy_setting');
        $response['default_privacy_setting'] = Phpfox::getService('mfox.privacy')->defaultprivacy("$iValue", $response['view_options']);

        return $response;
    }

    public function canEditAlbum($aItem){
        if((Phpfox::getUserId() == $aItem['user_id'] && Phpfox::getUserParam('photo.can_edit_own_photo_album'))
            || Phpfox::getUserParam('photo.can_edit_other_photo_albums')){
            return true;
        }

        return false;
    }

    public function canDeleteAlbum($aItem){
        if($aItem['profile_id'] == '0' && (((Phpfox::getUserId() == $aItem['user_id'] && Phpfox::getUserParam('photo.can_delete_own_photo_album')) || Phpfox::getUserParam('photo.can_delete_other_photo_albums'))))
        {
            return true;
        }

        return false;
    }

    public function canEditPhoto($aItem){
        if((Phpfox::getUserParam('photo.can_edit_own_photo') && $aItem['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('photo.can_edit_other_photo')){
            return true;
        }

        return false;
    }

    public function canDeletePhoto($aItem){
        if((Phpfox::getUserParam('photo.can_delete_own_photo') && $aItem['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('photo.can_delete_other_photos')){
            return true;
        }

        return false;
    }

    public function getPhotoIdInFeed($feed_id){
        $main_photo = $this->database()->select("*")
            ->from(Phpfox::getT('feed'), 'feed')
            ->where('feed.feed_id = ' . (int) $feed_id)
            ->execute('getSlaveRow');

        $list_photo_id = array();
        if(isset($main_photo['item_id'])){
            $list_photo_id[] = $main_photo['item_id'];

            $sub_photo = $this->database()->select("pf.*")
                ->from(Phpfox::getT('photo_feed'), 'pf')
                ->where('pf.feed_id = ' . (int) $feed_id)
                ->execute('getSlaveRows');
            foreach ($sub_photo as $keysub_photo => $valuesub_photo) {
                $list_photo_id[] = $valuesub_photo['photo_id'];
            }
        }

        return $list_photo_id;
    }

}

