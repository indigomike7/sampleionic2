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
class Mfox_Service_Advancedphoto extends Phpfox_Service
{
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

    public function __construct()
    {
        $this->_oReq = Mfox_Service_Request_Request::instance();
        $this->_oSearch = Mfox_Service_Search_Search::instance();
        $this->_oBrowse = Mfox_Service_Search_Browse::instance();

        $isUsing = Phpfox::getParam('mfox.replace_photo');
        $isAdv = Phpfox::isModule('advancedphoto');
        $isDefault = Phpfox::isModule('photo');

        $this->_bIsAdvancedPhotoModule = Phpfox::getService('mfox.core')->isAdvancedModule($isUsing, $isAdv, $isDefault);
    }

    public function isAdvancedModule(){
        return $this->_bIsAdvancedPhotoModule;
    }

    public function getfullalbumslide($aData){
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
        $iAlbumId = isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : 0;
        // Get the current album we are trying to view
        $aAlbum = Phpfox::getService('advancedphoto.album')->getForView($aData['iAlbumId']);
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
    
    public function albumslide($aData)
    {
        
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
        $aAlbum = Phpfox::getService('advancedphoto.album')->getForView($aData['iAlbumId']);
        
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

        $aCurrentPhoto = Phpfox::getService('advancedphoto')->getPhoto($aData['iCurrentPhotoId'], Phpfox::getUserId());
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
                    'comment_type_id' => 'advancedphoto',
                    'privacy' => $aPhoto['privacy'],
                    'comment_privacy' => $aPhoto['privacy_comment'],
                    'like_type_id' => 'advancedphoto',
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
                    'report_module' => 'advancedphoto',
                    'report_phrase' =>  Phpfox::getPhrase('photo.report_this_photo'));
                
                //  get list of liked user
                $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('advancedphoto'
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
                    'iTotalComment' => Phpfox::getService('mfox.comment')->getCommentCount(array('sItemType' => 'advancedphoto', 'iItemId' => $aPhoto['photo_id']), null),
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
                    'bIsLiked' => Phpfox::getService('mfox.like')->checkIsLiked('advancedphoto', $aPhoto['photo_id'], Phpfox::getUserId()),
                    'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('photo', $aPhoto['photo_id'], Phpfox::getUserId()),
                    'iPrivacy' => $aPhoto['privacy'],
                    'iPrivacyComment' => $aPhoto['privacy_comment'],

                    'aUserLike' => $aUserLike,
                    'aUserDislike' => $aUserDislike,
                    'sUserImageUrl' => $sUserImage,
                    'sUserName' => $aPhoto['full_name'],
                    'sItemType' => 'advancedphoto',
                    'sModelType' => 'advancedphoto',
                    'sAlbumType' => 'advancedphoto_album',
                    'iTimeStamp' =>  $aPhoto['time_stamp'],
                    
					'bCanMakeProfilePicture'=> ($aPhoto['user_id'] == Phpfox::getUserId())?1:0,

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
        $iPhotoId = isset($aData['iCurrentPhotoId']) ? (int) $aData['iCurrentPhotoId'] : 0;
        $iUserId = isset($aData['iUserId']) ? (int) $aData['iUserId'] : 0;
        if($iUserId > 0){
            $aData['bIsProfileUser'] = true;
        }
        $aPhoto = Phpfox::getService('advancedphoto')->getPhoto($iPhotoId, Phpfox::getUserId());
        
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid")),
                'error_code' => 1
            );
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('advancedphoto', $aPhoto['photo_id'], $aPhoto['user_id'], $aPhoto['privacy'], $aPhoto['is_friend'], true))
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'),
                'error_code' => 1
            );
        }
        
        $iAlbumId = isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : 0;        
        if ($iAlbumId > 0 && $aPhoto['album_id'] != $iAlbumId)
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.current_photo_does_not_exist_in_this_ablum")),
                'error_code' => 1
            );
        }
        
        if (Phpfox::isUser() && Phpfox::isModule('notification'))
        {
            Phpfox::getService('notification.process')->delete('comment_photo', $iPhotoId, Phpfox::getUserId());
            Phpfox::getService('notification.process')->delete('photo_like', $iPhotoId, Phpfox::getUserId());
            Phpfox::getService('notification.process')->delete('photo_tag', $iPhotoId, Phpfox::getUserId());
        }
        
        $sAction = isset($aData['sAction']) ? $aData['sAction'] : '';
        if(empty($sAction)){
            $aData['sAction'] = 'previous';
            $aPreviousPhotos = $this->photoslide($aData);
            
            $aData['sAction'] = 'current';
            $aCurrentPhoto = $this->photoslide($aData);
            
            $aData['sAction'] = 'next';
            $aNextPhotos = $this->photoslide($aData);
            // Add current photo.
            $aPreviousPhotos[] = $aCurrentPhoto;
            
            foreach($aNextPhotos as $aPhoto)
            {
                $aPreviousPhotos[] = $aPhoto;
            }
            
            return $aPreviousPhotos;
        } else if('next' == $sAction){
            // get one side
            $aData['sAction'] = 'next';
            $aData['iOneSide'] = '1';
            return $this->photoslide($aData);
        } else if('previous' == $sAction){
            // get one side
            $aData['sAction'] = 'previous';
            $aData['iOneSide'] = '1';
            return $this->photoslide($aData);
        }        
    }

    public function onephotoslide($aData)
    {
        $ret = $this->photoslide($aData);

        return array($ret);
    }
    
    public function photoslide($aData)
    {
        if (!isset($aData['iCurrentPhotoId']) || $aData['iCurrentPhotoId'] < 1)
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.current_photo_id_is_not_valid")),
                'error_code' => 1
            );
        }

        $iOneSide = isset($aData['iOneSide']) ? (int) $aData['iOneSide'] : 0;        
        $iAlbumId = isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : 0;        
        if (!isset($aData['iAmountOfPhoto']))
        {
            $aData['iAmountOfPhoto'] = 10;
        }

        $aCurrentPhoto = Phpfox::getService('advancedphoto')->getPhoto($aData['iCurrentPhotoId'], Phpfox::getUserId());        
        if (!isset($aCurrentPhoto['photo_id']))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.current_photo_is_not_valid")),
                'error_code' => 1
            );
        }
        
        if ($iAlbumId > 0 && $aCurrentPhoto['album_id'] != $iAlbumId)
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.current_photo_does_not_exist_in_this_ablum")),
                'error_code' => 1
            );
        }

        if(!empty($aData['sModule'])){
            $aParentModule = array(
                'module_id' => isset($aData['sModule']) ? $aData['sModule'] : '',
                'item_id' => isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0
            );
        }else if(!empty($aData['sParentType'])){
            $aParentModule = array(
                'module_id' => isset($aData['sParentType']) ? $aData['sParentType'] : '',
                'item_id' => isset($aData['iParentId']) ? (int) $aData['iParentId'] : 0
            );
        }else{
            $aParentModule = array(
                'module_id' => isset($aData['sModule']) ? $aData['sModule'] : '',
                'item_id' => isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0
            );
        }

        
        if (isset($aData['sAction']) && $aData['sAction'] == 'current')
        {
            if (isset($aData['iInDetails']) && $aData['iInDetails'] == '1'){
                $aFeed = array(             
                    'comment_type_id' => 'advancedphoto',
                    'privacy' => $aCurrentPhoto['privacy'],
                    'comment_privacy' => $aCurrentPhoto['privacy_comment'],
                    'like_type_id' => 'advancedphoto',
                    'feed_is_liked' => $aCurrentPhoto['is_liked'],
                    'feed_is_friend' => $aCurrentPhoto['is_friend'],
                    'item_id' => $aCurrentPhoto['photo_id'],
                    'user_id' => $aCurrentPhoto['user_id'],
                    'total_comment' => $aCurrentPhoto['total_comment'],
                    'total_like' => $aCurrentPhoto['total_like'],
                    'feed_link' => Phpfox::getLib('url')->permalink('photo', $aCurrentPhoto['photo_id'], $aCurrentPhoto['title']),
                    'feed_title' => $aCurrentPhoto['title'],
                    'feed_display' => 'view',
                    'feed_total_like' => $aCurrentPhoto['total_like'],
                    'report_module' => 'advancedphoto',
                    'report_phrase' =>  Phpfox::getPhrase('photo.report_this_photo'));

                //  get list of liked user
                $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('advancedphoto'
                    , $aCurrentPhoto['photo_id']
                    , false
                    , Phpfox::getParam('feed.total_likes_to_display'));
                $aUserLike = array();
                foreach($aLike['likes'] as $like){
                    $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
                }

                $aUserDislike = array();
                $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('photo', $aCurrentPhoto['photo_id'], $bGetCount = false);
                foreach($aDislike as $dislike){
                    $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
                }                

                $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aCurrentPhoto, '_50_square');

                return array(
                    'iPhotoId' => $aCurrentPhoto['photo_id'],
                    'sTitle' => $aCurrentPhoto['title'],
                    'bCanPostComment' => Phpfox::getService('mfox.comment')->checkCanPostComment($aFeed),
                    'sPhotoUrl' => Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aCurrentPhoto['server_id'],
                        'path' => 'photo.url_photo',
                        'file' => $aCurrentPhoto['destination'],
                        'suffix' => '_1024',
                        'return_url' => true
                            )
                    ),
                    'fRating' => $aCurrentPhoto['total_rating'],
                    'iTotalVote' => $aCurrentPhoto['total_vote'],
                    'iTotalBattle' => $aCurrentPhoto['total_battle'],
                    'iAlbumId' => $aCurrentPhoto['album_id'],
                    'sAlbumName' => $aCurrentPhoto['album_title'],
                    'iTotalLike' => $aCurrentPhoto['total_like'],
                    'iTotalDislike' => $aCurrentPhoto['total_dislike'],
                    'bIsFeatured' => $aCurrentPhoto['is_featured'],
                    'bIsCover' => $aCurrentPhoto['is_cover'],
                    'iTotalView' => $aCurrentPhoto['total_view'],
                    'iTotalComment' => $aCurrentPhoto['total_comment'],
                    'iTotalDownload' => $aCurrentPhoto['total_download'],
                    'iAllowDownload' => $aCurrentPhoto['allow_download'],
                    'iIsSponsor' => $aCurrentPhoto['is_sponsor'],
                    'iOrdering' => $aCurrentPhoto['ordering'],
                    'bIsProfilePhoto' => $aCurrentPhoto['is_profile_photo'],
                    'sFileName' => $aCurrentPhoto['file_name'],
                    'sFileSize' => $aCurrentPhoto['file_size'],
                    'sMimeType' => $aCurrentPhoto['mime_type'],
                    'sExtension' => $aCurrentPhoto['extension'],
                    'sDescription' => $aCurrentPhoto['description'],
                    'iWidth' => $aCurrentPhoto['width'],
                    'iHeight' => $aCurrentPhoto['height'],
                    'sAlbumUrl' => $aCurrentPhoto['album_url'],
                    'sAlbumTitle' => $aCurrentPhoto['album_title'],
                    'iAlbumProfileId' => $aCurrentPhoto['album_profile_id'],
                    'bIsViewed' => $aCurrentPhoto['is_viewed'],
                    'aCategories' => $aCurrentPhoto['categories'],
                    'bCategoryList' => $aCurrentPhoto['category_list'],
                    'sOriginalDestination' => $aCurrentPhoto['original_destination'],
                    'bIsFriend' => (bool) $aCurrentPhoto['is_friend'],
                    'iUserId' => $aCurrentPhoto['user_id'],
                    'iProfilePageId' => $aCurrentPhoto['profile_page_id'],
                    'iUserServerId' => $aCurrentPhoto['user_server_id'],
                    'sUserName' => $aCurrentPhoto['user_name'],
                    'sFullName' => $aCurrentPhoto['full_name'],
                    'iGender' => $aCurrentPhoto['gender'],
                    'sUserImage' => $sUserImage,
                    'bIsInvisible' => $aCurrentPhoto['is_invisible'],
                    'iUserGroupId' => $aCurrentPhoto['user_group_id'],
                    'iLanguageId' => (int) $aCurrentPhoto['language_id'],
                    'iViewId' => $aCurrentPhoto['view_id'],
                    'iTypeId' => $aCurrentPhoto['type_id'],
                    'sModuleId' => $aCurrentPhoto['module_id'],
                    'iGroupId' => (int) $aCurrentPhoto['group_id'],
                    'iParentUserId' => $aCurrentPhoto['parent_user_id'],
                    'iServerId' => $aCurrentPhoto['server_id'],
                    'iMature' => $aCurrentPhoto['mature'],
                    'iAllowComment' => $aCurrentPhoto['allow_comment'],
                    'iAllowRate' => $aCurrentPhoto['allow_rate'],
                    'bIsLiked' => isset($aCurrentPhoto['is_liked']) ? $aCurrentPhoto['is_liked'] : 0,
                    'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('photo', $aCurrentPhoto['photo_id'], Phpfox::getUserId()),
                    'iPrivacy' => $aCurrentPhoto['privacy'],
                    'iPrivacyComment' => $aCurrentPhoto['privacy_comment'],
                    
					
					'bCanMakeProfilePicture'=> ($aCurrentPhoto['user_id'] == Phpfox::getUserId())?1:0,

                    'aUserLike' => $aUserLike,
                    'aUserDislike' => $aUserDislike,
                    'sUserImageUrl' => $sUserImage,
                    'sUserName' => $aCurrentPhoto['full_name'],
                    'sItemType' => 'advancedphoto',
                    'sModelType' => 'advancedphoto',
                    'sAlbumType' => 'advancedphoto_album',
                    'iTimeStamp' =>  $aCurrentPhoto['time_stamp'],
					
                    'sTimeStamp' => date('l, F j, o', (int) $aCurrentPhoto['time_stamp']) . ' at ' . date('h:i a', (int) $aCurrentPhoto['time_stamp']), 
                    'bCanEdit' => $this->canEditPhoto($aCurrentPhoto),
                    'bCanDelete' => $this->canDeletePhoto($aCurrentPhoto),
                );
            } else {
                // get thumb image
                $sThumbUrl = Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aCurrentPhoto['server_id'],
                        'path' => 'photo.url_photo',
                        'file' => $aCurrentPhoto['destination'],
                        'suffix' => MAX_SIZE_OF_USER_IMAGE_PHOTO,
                        'return_url' => true
                            )
                    );

                return array(
                    'iPhotoId' => $aCurrentPhoto['photo_id'],
                    'sTitle' => $aCurrentPhoto['title'],
                    'sPhotoUrl' => $sThumbUrl
                    );
            }
        }
        
        $aConditions = array();
        if (isset($aData['sParentType']) && $aData['sParentType'] == 'feed'
            && isset($aData['iActionId']) && $aData['iActionId'] > 0
            )
        {
            $list_photo_id = $this->getPhotoIdInFeed((int)$aData['iActionId']);
            if(count($list_photo_id) > 0){
                $str_photo_id = implode(',', $list_photo_id);
                $str_photo_id = trim($str_photo_id, ',');
                $aConditions[] = ' photo.photo_id IN ( ' . $str_photo_id . ' ) ';
            }
        }

        $oDb = $this->database();

        $oDb->select('pa.name AS album_name, pa.profile_id AS album_profile_id, ppc.name as category_name, ppc.category_id, l.like_id as is_liked, adisliked.action_id as is_disliked, photo.*, u.user_id, u.profile_page_id, u.server_id AS user_server_id, u.user_name, u.full_name, u.gender, u.user_image, u.is_invisible, u.user_group_id, u.language_id, pi.*');
        $oDb->from(Phpfox::getT('photo'), 'photo');

        $bNoQueryFriend = false; 
        
        if($aData['sView'] == 'friend'){
            $bNoQueryFriend = true; 
        }else if($aData['sView'] != 'my' && Phpfox::getParam('core.friends_only_community')){
            // if(!$aData['iUserId']){
                $bNoQueryFriend =  true;    
            // }
        }

        if (Phpfox::isModule('friend') && $bNoQueryFriend)
        {
            $oDb->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = photo.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }
        
        if (isset($aData['iCategoryId']) && $aData['iCategoryId'] > 0)
        {
            // category
            $oDb->innerJoin(Phpfox::getT('photo_category_data'), 'pcd', 'pcd.photo_id = photo.photo_id');
        }
        $oDb->leftJoin(Phpfox::getT('photo_info'), 'pi', 'pi.photo_id = photo.photo_id');
        $oDb->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = photo.album_id');
        $oDb->leftJoin(Phpfox::getT('photo_category_data'), 'ppcd', 'ppcd.photo_id = photo.photo_id');
        $oDb->leftJoin(Phpfox::getT('photo_category'), 'ppc', 'ppc.category_id = ppcd.category_id');
        $oDb->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = "photo" AND l.item_id = photo.photo_id AND l.user_id = ' . Phpfox::getUserId());
        $oDb->leftJoin(Phpfox::getT('action'), 'adisliked', 'adisliked.action_type_id = 2 AND adisliked.item_id = photo.photo_id AND adisliked.user_id = ' . Phpfox::getUserId());
        $oDb->join(Phpfox::getT('user'), 'u', 'u.user_id = photo.user_id');

        if (isset($aData['sView']) && $aData['sView'] == 'my')
        {
            $aConditions[] = 'photo.user_id = ' . Phpfox::getUserId();
        }
        elseif (isset($aData['sView']) && $aData['sView'] == 'friend')
        {
            $aConditions[] = 'photo.view_id = 0';
            $aConditions[] = 'photo.group_id = 0';
            $aConditions[] = 'photo.type_id = 0';
            $aConditions[] = 'photo.privacy IN(0,1,2)';
        }
        else
        {
            // Not profile user.
            if (!isset($aData['bIsProfileUser']) || $aData['bIsProfileUser'] != 'true')
            {
                // In page.
                if (isset($aParentModule['module_id']) && $aParentModule['module_id'] != '' && isset($aParentModule['item_id']) && $aParentModule['item_id'] > 0)
                {
                    $aConditions[] = 'photo.view_id = 0';
                    $aConditions[] = 'photo.module_id = \'' . Phpfox::getLib('database')->escape($aParentModule['module_id']) . '\'';
                    $aConditions[] = 'photo.group_id = ' . (int) $aParentModule['item_id'];
                    $aConditions[] = 'photo.privacy IN(0)';
                }
                else  // All - Public and not in page.
                {
                    $aConditions[] = 'photo.view_id = 0';
                    // $aConditions[] = 'photo.group_id = 0';
                    // $aConditions[] = 'photo.type_id = 0';
                    $aConditions[] = 'photo.privacy IN(0)';
                }
            }
        }
        // Profile user.
        if (isset($aData['bIsProfileUser']) && $aData['bIsProfileUser'] == 'true')
        {
            $iUserId  =  intval($aData['iUserId'] );
			
            if (!$iUserId)
            {
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.user_id_is_not_valid"))
                );
            }
            elseif ($iUserId == Phpfox::getUserId()) // My profile.
            {
                $aConditions[] = 'photo.view_id IN(0,2)';
                $aConditions[] = 'photo.group_id = 0';
                $aConditions[] = 'photo.type_id < 2';
                // $aConditions[] = 'photo.privacy IN(0,1,2,3,4)';
                $aConditions[] = 'photo.user_id = ' . Phpfox::getUserId();
            }
            else
            {
            	// ensure about privacy entire the rest.
            	
                $aConditions[] = 'photo.view_id = 0';
                $aConditions[] = 'photo.group_id = 0';
                $aConditions[] = 'photo.type_id < 2';
                $aConditions[] = 'photo.user_id = ' . $iUserId;
				
				$aConditions[] = 'photo.privacy IN(0)';
				
				// if(Phpfox::getService('friend')->isFriend($iUserId, Phpfox::getUserId())){
					// $aConditions[] = 'photo.privacy IN(0,1,2)';	
				// }else if (Phpfox::getService('friend')->isFriendOfFriend($iUserId)){
					// $aConditions[] = 'photo.privacy IN(0,2)';
				// }else {
					// $aConditions[] = 'photo.privacy IN(0)';
				// }
            }
        }
        // Category filter.
        if (isset($aData['iCategoryId']) && $aData['iCategoryId'] > 0)
        {
            $aConditions[] = 'pcd.category_id = ' . (int) $aData['iCategoryId'];
        }
        // Set the current photo id condition.
        if (isset($aData['sAction']) && $aData['sAction'] == 'previous')
        {
            $aConditions[] = 'photo.photo_id > ' . (int) $aData['iCurrentPhotoId'];
        }
        else // Next
        {
            $aConditions[] = 'photo.photo_id < ' . (int) $aData['iCurrentPhotoId'];
        }
        
        // Filter by album.
        if ($iAlbumId > 0)
        {
            $aConditions[] = 'pa.album_id = ' . $iAlbumId;
        }
        
        // Set conditions.
        $oDb->where(implode(' AND ', $aConditions));
        $oDb->group('photo.photo_id');
        // Check action to get limit.
        if (isset($aData['sAction']) && $aData['sAction'] == 'previous')
        {
            $oDb->order('photo.photo_id ASC');
            $oDb->limit((int)$aData['iAmountOfPhoto']);
        }
        else // Next.
        {
            $oDb->order('photo.photo_id DESC');
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
            if (isset($aData['iInDetails']) && $aData['iInDetails'] == '1'){
                $aFeed = array(             
                    'comment_type_id' => 'advancedphoto',
                    'privacy' => $aPhoto['privacy'],
                    'comment_privacy' => $aPhoto['privacy_comment'],
                    'like_type_id' => 'advancedphoto',
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
                    'report_module' => 'advancedphoto',
                    'report_phrase' =>  Phpfox::getPhrase('photo.report_this_photo'));

                //  get list of liked user
                $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('advancedphoto'
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
                    'sAlbumName' => $aPhoto['album_name'],
                    'iTotalLike' => count($aUserLike),
                    'iTotalDislike' => $aPhoto['total_dislike'],
                    'bIsFeatured' => $aPhoto['is_featured'],
                    'bIsCover' => $aPhoto['is_cover'],
                    'iTotalView' => $aPhoto['total_view'],
                    'iTotalComment' => Phpfox::getService('mfox.comment')->getCommentCount(array('sItemType' => 'advancedphoto', 'iItemId' => $aPhoto['photo_id']), null),
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
                    'bIsLiked' => isset($aPhoto['is_liked']) ? $aPhoto['is_liked'] : 0,
                    'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('photo', $aPhoto['photo_id'], Phpfox::getUserId()),
                    'iPrivacy' => $aPhoto['privacy'],
                    'iPrivacyComment' => $aPhoto['privacy_comment'],

                    'aUserLike' => $aUserLike,
                    'aUserDislike' => $aUserDislike,
                    'sUserImageUrl' => $sUserImage,
                    'sUserName' => $aPhoto['full_name'],
                    'sItemType' => 'advancedphoto',
                    'sModelType' => 'advancedphoto',
                    'sAlbumType' => 'advancedphoto_album',
                    'iTimeStamp' =>  $aPhoto['time_stamp'],
                    
					'bCanMakeProfilePicture'=> ($aPhoto['user_id'] == Phpfox::getUserId())?1:0,

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
        if (!Phpfox::getUserParam('advancedphoto.can_view_photo_albums'))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_this_photo_albums"))
            );
        }

        if (!Phpfox::getUserParam('advancedphoto.can_view_photos'))
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
            $aAlbum = Phpfox::getService('advancedphoto.album')->getForProfileView($aData['iAlbumId']);
            $aAlbum['name'] = Phpfox::getLib('locale')->convert(Phpfox::getPhrase('photo.profile_pictures'));
        }
        else
        {
            // Get the current album we are trying to view
            $aAlbum = Phpfox::getService('advancedphoto.album')->getForView($aData['iAlbumId']);
            if ($aAlbum['profile_id'] > 0)
            {
                $bIsProfilePictureAlbum = true;
                $aAlbum['name'] = Phpfox::getLib('locale')->convert(Phpfox::getPhrase('photo.profile_pictures'));
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
        $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('advancedphoto_album'
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
        if ((Phpfox::getUserParam('advancedphoto.can_tag_own_photo') && $aAlbum['user_id'] == Phpfox::getUserId()) 
            || Phpfox::getUserParam('advancedphoto.can_tag_other_photos')){
            $bCanTag = true;
        }

        // sUserImage
        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aAlbum, '_50_square');

        return array(
            'bIsProfilePictureAlbum' => $bIsProfilePictureAlbum,
            'bIsFriend' => $aAlbum['is_friend'],
            'sModelType' => 'advancedphoto_album',
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
            'iTotalComment' => Phpfox::getService('mfox.comment')->getCommentCount(array('sItemType' => 'advancedphoto_album', 'iItemId' => $aAlbum['album_id']), null),
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
        //  init
        if (!Phpfox::getUserParam('advancedphoto.can_view_photo_albums'))
        {
            return array(
                'error_code' => 1,
                'result' => 0,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_this_photo_albums"))
            );
        }
        if (!Phpfox::getUserParam('advancedphoto.can_view_photos'))
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
            $aAlbum = Phpfox::getService('advancedphoto.album')->getForProfileView((int) $iProfileId);
            $aAlbum['name'] = Phpfox::getLib('locale')->convert(Phpfox::getPhrase('photo.profile_pictures'));
        }
        else
        {
            // Get the current album we are trying to view
            $aAlbum = Phpfox::getService('advancedphoto.album')->getForView((int) $aData['iAlbumId']);
            if ($aAlbum['profile_id'] > 0)
            {
                $bIsProfilePictureAlbum = true;
                $aAlbum['name'] = Phpfox::getLib('locale')->convert(Phpfox::getPhrase('photo.profile_pictures'));
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
        list($iCnt, $aPhotos) = Phpfox::getService('advancedphoto')->get($aConditions, 'p.photo_id DESC', $iPage, $iPageSize);
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
        $aPhoto = Phpfox::getService('advancedphoto')->getForEdit($aData['iPhotoId']);
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
            'type' => 'advancedphoto',
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
        if (!isset($aData['iPhotoId']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_id_is_missing"))
            );
        }

        $aPhoto = Phpfox::getService('advancedphoto')->getForEdit($aData['iPhotoId']);
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid_or_has_been_deleted"))
            );
        }

        $iUserId = Phpfox::getService('mfox.auth')->hasAccess('photo', 'photo_id', $aData['iPhotoId'], 'advancedphoto.can_edit_own_photo', 'advancedphoto.can_edit_other_photo');
        if (!$iUserId)
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_set_profile_this_photo"))
            );
        }

        $bResult = Phpfox::getService('advancedphoto.process')->makeProfilePicture($aData['iPhotoId']);
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
                'system_message' => implode(' ',Phpfox_Error::get())
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
        if (!isset($aData['iPhotoId']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_id_is_missing"))
            );
        }

        $aPhoto = Phpfox::getService('advancedphoto')->getForEdit($aData['iPhotoId']);
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

        $bResult = Phpfox::getService('advancedphoto.album.process')->setCover($aAlbum['album_id'], $aData['iPhotoId']);
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
        if (!isset($aData['iPhotoId']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_id_is_missing"))
            );
        }

        $aPhoto = Phpfox::getService('advancedphoto')->getForEdit($aData['iPhotoId']);
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
        if (!isset($aData['iPhotoId']))
        {
            return array(
                'error_code' => 1,
                'result' => 0,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        $aPhoto = Phpfox::getService('advancedphoto')->getForEdit($aData['iPhotoId']);
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
            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.can_not_delete_this_photo_maybe_you_do_not_have_permission_to_delete_it"))
        );
    }

    public function deletePhoto($iId, $bPass = false)
    {
        // Get the image ID and full path to the image.
        $aPhoto = $this->database()->select('user_id, module_id, group_id, is_sponsor, album_id, photo_id, destination')
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
    
        if ($bPass === false && !Phpfox::getService('mfox.auth')->hasAccess('photo', 'photo_id', $iId, 'advancedphoto.can_delete_own_photo', 'advancedphoto.can_delete_other_photos', $aPhoto['user_id']))
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
            Phpfox::getLib('file')->unlink(Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], ''));
        }
    
        // Loop thru all the other smaller images
        foreach(Phpfox::getParam('photo.photo_pic_sizes') as $iSize)
        {
            // Make sure the image exists
            if (!empty($aPhoto['destination']) && file_exists(Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], '_' . $iSize)))
            {
                // Add to the file size var
                $iFileSizes += filesize(Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], '_' . $iSize));
        
                // Remove the image
                Phpfox::getLib('file')->unlink(Phpfox::getParam('photo.dir_photo') . sprintf($aPhoto['destination'], '_' . $iSize));
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
        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('advancedphoto', $iId) : null);
        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('comment_photo', $iId) : null);
        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('comment_advancedphoto', $iId) : null);
        (Phpfox::isModule('tag') ? Phpfox::getService('tag.process')->deleteForItem($aPhoto['user_id'], $iId, 'photo') : null);
        (Phpfox::isModule('tag') ? Phpfox::getService('tag.process')->deleteForItem($aPhoto['user_id'], $iId, 'advancedphoto') : null);
    
        // Update user space usage
        if ($iFileSizes > 0)
        {
            Phpfox::getService('user.space')->update($aPhoto['user_id'], 'photo', $iFileSizes, '-');
        }
    
        // Update user activity
        Phpfox::getService('user.activity')->update($aPhoto['user_id'], 'advancedphoto', '-');
    
        if ($aPhoto['album_id'] > 0)
        {
            Phpfox::getService('advancedphoto.album.process')->updateCounter($aPhoto['album_id'], 'total_photo', true);
        }

        if ($aPhoto['is_sponsor'] == 1)
        {
            $this->cache()->remove('photo_sponsored');
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
        $aPhoto = Phpfox::getService('advancedphoto')->getForEdit($aData['iPhotoId']);
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'error_code' => 1,
                'result' => 0,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid"))
            );
        }
        $iUserId = Phpfox::getService('mfox.auth')->hasAccess('photo', 'photo_id', $aData['iPhotoId'], 'advancedphoto.can_edit_own_photo', 'advancedphoto.can_edit_other_photo');
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
        $oParseInput = Phpfox::getLib('parse.input');
    
        // Create the fields to insert.
        $aFields = array();

        // Make sure we are updating the album ID
        (!empty($aVals['album_id']) ? $aFields['album_id'] = 'int' : null);
    
        // Is this an update?
        if ($bIsUpdate)
        {
            // Make sure we only update the fields that the user is allowed to
            (Phpfox::getUserParam('advancedphoto.can_add_mature_images') ? $aFields['mature'] = 'int' : null);
            (Phpfox::getUserParam('advancedphoto.can_control_comments_on_photos') ? $aFields['allow_comment'] = 'int' : null);
            ((Phpfox::getUserParam('advancedphoto.can_add_to_rating_module') && Phpfox::getParam('photo.can_rate_on_photos')) ? $aFields['allow_rate'] = 'int' : null);
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
            if (Phpfox::isModule('tag') && isset($aVals['tag_list']) && !empty($aVals['tag_list']) && Phpfox::getUserParam('advancedphoto.can_add_tags_on_photos'))
            {
                Phpfox::getService('tag.process')->update('advancedphoto', $aVals['photo_id'], $iUserId, $aVals['tag_list']);
            }
    
            // Make sure if we plan to add categories for this image that there is something to add
            if (isset($aVals['category_id']) && count($aVals['category_id']))
            {
                // Loop thru all the categories
                $this->database()->delete(Phpfox::getT('photo_category_data'), 'photo_id = ' . (int) $aVals['photo_id']);
                foreach ($aVals['category_id'] as $iCategory)
                {
                    // Add each of the categories                   
                    Phpfox::getService('advancedphoto.category.process')->updateForItem($aVals['photo_id'], $iCategory);
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
                    Phpfox::getService('privacy.process')->update('advancedphoto', $iId, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : array()));
                }
                else 
                {
                    Phpfox::getService('privacy.process')->delete('advancedphoto', $iId);
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


            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->update('advancedphoto', $iId, $aVals['privacy'], $aVals['privacy_comment'], 0, $iUserId) : null);

            if (!empty($aVals['move_to']))
            {
                Phpfox::getService('advancedphoto.album.process')->updateCounter($iOldAlbumId, 'total_photo');
                Phpfox::getService('advancedphoto.album.process')->updateCounter($aVals['move_to'], 'total_photo');
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
    
            $aVals['view_id'] = (Phpfox::getUserParam('advancedphoto.photo_must_be_approved') ? '1' : '0');
            
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
    
            if (!Phpfox::getUserParam('advancedphoto.photo_must_be_approved'))
            {
                // Update user activity
                Phpfox::getService('user.activity')->update($iUserId, 'advancedphoto');
            }
            
            // Make sure if we plan to add categories for this image that there is something to add
            if (isset($aVals['category_id']) && count($aVals['category_id']))
            {
                // Loop thru all the categories
                foreach ($aVals['category_id'] as $iCategory)
                {
                    // Add each of the categories
                    Phpfox::getService('advancedphoto.category.process')->updateForItem($iId, $iCategory);
                }
            }           
            
            if (isset($aVals['privacy']))
            {
                if ($aVals['privacy'] == '4')
                {
                    Phpfox::getService('privacy.process')->add('advancedphoto', $iId, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : array()));          
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
        $aAlbum = $this->database()->select('album_id, user_id')
            ->from(Phpfox::getT('photo_album'))
            ->where('album_id = ' . (int) $iAlbumId)
            ->execute('getRow');
            
        if (!isset($aAlbum['album_id']))
        {
            return Phpfox_Error::set( Phpfox::getPhrase('photo.not_a_valid_photo_album_to_delete'));
        }
            
        if (!Phpfox::getService('mfox.auth')->hasAccess('photo_album', 'album_id', $iAlbumId, 'advancedphoto.can_delete_own_photo_album', 'advancedphoto.can_delete_other_photo_albums', $aAlbum['user_id']))
        {
            return Phpfox_Error::set( Phpfox::getPhrase('photo.you_do_not_have_sufficient_permission_to_delete_this_photo_album'));
        }           
        
        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('advancedphoto_album', $aAlbum['album_id']) : null);

        $aPhotos = $this->database()->select('photo_id')
            ->from(Phpfox::getT('photo'))
            ->where('album_id = ' . $aAlbum['album_id'])
            ->execute('getRows');
            
        foreach ($aPhotos as $aPhoto)
        {
            Phpfox::getService('advancedphoto.process')->delete($aPhoto['photo_id']);
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
        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_logged_in_users")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        $iAlbumId = isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : 0;
        $aAlbum = Phpfox::getService('advancedphoto.album')->getForEdit($iAlbumId);

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
            'module_id'=>$aAlbum['module_id'],
            'group_id'=>$aAlbum['group_id'],
            'description' => isset($aData['sDescription']) ? $aData['sDescription'] : '',
            'privacy' => isset($aData['iPrivacy']) ? $aData['iPrivacy'] : 0,
            'privacy_comment' => isset($aData['iPrivacyComment']) ? $aData['iPrivacyComment'] : 0,
            'privacy_list' => isset($aData['sPrivacyList']) ? explode(',', $aData['sPrivacyList']) : null
        );


        if (Phpfox::getService('advancedphoto.album.process')->update($aAlbum['album_id'], $aVals))
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
        if (!Phpfox::getUserParam('advancedphoto.can_upload_photos'))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_upload_photo")),
                'result' => 0
            );
        }

        if (($iFlood = Phpfox::getUserParam('advancedphoto.flood_control_photos')) !== 0)
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

        $oServicePhotoProcess = Phpfox::getService('advancedphoto.process');
        $aImages = array();
        $iFileSizes = 0;
        $iCnt = 0;
		$orgSize = null;
		$orgExt = null;

        if (!empty($aVals['album_id']))
        {
            $aAlbum = Phpfox::getService('advancedphoto.album')->getAlbum(Phpfox::getUserId(), $aVals['album_id'], true);
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
                //$iLimitUpload = (Phpfox::getUserParam('advancedphoto.photo_max_upload_size') === 0 ? null : (Phpfox::getUserParam('advancedphoto.photo_max_upload_size') / 1024));
                $iLimitUpload = null;

                if ($aImage = $oFile->load('image', array('jpg', 'gif', 'png'), $iLimitUpload))
                {
                    $aVals['description'] = ($aVals['is_cover_photo']) ? null : $aVals['status_info'];
                    $aVals['type_id'] = ($aVals['is_cover_photo']) ? '2' : '1';

                    if ($iId = $oServicePhotoProcess->add(Phpfox::getUserId(), array_merge($aVals, $aImage)))
                    {
                        $iCnt++;
                        $aPhoto = Phpfox::getService('advancedphoto')->getForProcess($iId);

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
                //$iLimitUpload = (Phpfox::getUserParam('advancedphoto.photo_max_upload_size') === 0 ? null : (Phpfox::getUserParam('advancedphoto.photo_max_upload_size') / 1024));
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
                        $aPhoto = Phpfox::getService('advancedphoto')->getForProcess($iId);

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
                $aAlbum = Phpfox::getService('advancedphoto.album')->getAlbum(Phpfox::getUserId(), $aVals['album_id'], true);

                // Set the album privacy
                Phpfox::getService('advancedphoto.album.process')->setPrivacy($aVals['album_id']);

                // Check if we already have an album cover
                if (!Phpfox::getService('advancedphoto.album.process')->hasCover($aVals['album_id']))
                {
                    // Set the album cover
                    Phpfox::getService('advancedphoto.album.process')->setCover($aVals['album_id'], $iId);
                }

                // Update the album photo count
                if (!Phpfox::getUserParam('advancedphoto.photo_must_be_approved'))
                {
                    Phpfox::getService('advancedphoto.album.process')->updateCounter($aVals['album_id'], 'total_photo', false, count($aImages));
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
                    $aPhoto = Phpfox::getService('advancedphoto')->getForProcess($aImage['photo_id']);

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

                // do not create feed directly.
                // since 3.08p2
                // ID: FMOBI-1879
                if (false && !Phpfox::getUserParam('advancedphoto.photo_must_be_approved') && !$bIsCoverPhoto)
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

                        (Phpfox::isModule('feed') ? $iFeedId = Phpfox::getService('feed.process')->callback($aCallback)->add('advancedphoto', $aPhoto['photo_id'], $aPhoto['privacy'], $aPhoto['privacy_comment'], (int) $iParentUserId) : null);

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
                    'sType' => 'advancedphoto'
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
                'sType' => 'advancedphoto'
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
        if (!isset($aData['iPhotoId']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_id_is_missing"))
            );
        }
        
        if (!Phpfox::getUserParam('advancedphoto.can_view_photos'))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_can_not_view_photos")),
                'result' => 0,
                'error_code' => 1
            );
        }
        
        $aCallback = null;
        $aPhoto = Phpfox::getService('advancedphoto')->getPhoto($aData['iPhotoId'], Phpfox::getUserId());
        $aFeed = array(             
                'comment_type_id' => 'advancedphoto',
                'privacy' => $aPhoto['privacy'],
                'comment_privacy' => $aPhoto['privacy_comment'],
                'like_type_id' => 'advancedphoto',
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
                'report_module' => 'advancedphoto',
                'report_phrase' =>  Phpfox::getPhrase('photo.report_this_photo'));
        
        $aPhoto['bCanPostComment'] = Phpfox::getService('mfox.comment')->checkCanPostComment($aFeed);
        
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid")),
                'error_code' => 1
            );
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('advancedphoto', $aPhoto['photo_id'], $aPhoto['user_id'], $aPhoto['privacy'], $aPhoto['is_friend'], true))
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

        $aPhotoStream = Phpfox::getService('advancedphoto')->getPhotoStream($aPhoto['photo_id'], (isset($aData['iAlbumId']) ? $aData['iAlbumId'] : '0'), $aCallback, $iUserId, $iCategory, $aPhoto['user_id']);

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
            $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('advancedphoto'
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
            if ((Phpfox::getUserParam('advancedphoto.can_tag_own_photo') && $aPhoto['user_id'] == Phpfox::getUserId()) 
                || Phpfox::getUserParam('advancedphoto.can_tag_other_photos')){
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
        $iTotalAlbums = Phpfox::getService('advancedphoto.album')->getAlbumCount(Phpfox::getUserId());

        // Check if they are allowed to create new albums
        $bAllowedAlbums = (Phpfox::getUserParam('advancedphoto.max_number_of_albums') == 'null' ? true : (!Phpfox::getUserParam('advancedphoto.max_number_of_albums') ? false : (Phpfox::getUserParam('advancedphoto.max_number_of_albums') <= $iTotalAlbums ? false : true)));

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
            $aVals['group_id'] = $aParentModule['item_id'];
        }   
        else
        {            
            $aVals['privacy'] = $aData['iPrivacy'];
            $aVals['privacy_comment'] = $aData['iPrivacyComment'];
            $aVals['privacy_list'] = isset($aData['sPrivacyList']) ? explode(',', $aData['sPrivacyList']) : null;
        }

        // Add the photo album
        if ($iId = Phpfox::getService('advancedphoto.album.process')->add($aVals))
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
            'sModule' => 'advancedphoto',
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
        
        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aAlbum['user_id'])
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_liked_gender_own_photo_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aAlbum['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten(Phpfox::getLib('locale')->convert($aAlbum['name']), Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        elseif ($aAlbum['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_liked_your_photo_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten(Phpfox::getLib('locale')->convert($aAlbum['name']), Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_liked_span_class_drop_data_user_full_name_s_span_photo_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aAlbum['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten(Phpfox::getLib('locale')->convert($aAlbum['name']), Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }

        return array(
            'link' => array(
                'iAlbumId' => $aAlbum['album_id'],
                'sAlbumTitle' => Phpfox::getLib('locale')->convert($aAlbum['name'])
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
        
        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aAlbum['user_id'] && !isset($aNotification['extra_users']))
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_commented_on_gender_photo_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aAlbum['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten(Phpfox::getLib('locale')->convert($aAlbum['name']), Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        elseif ($aAlbum['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_commented_on_your_photo_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten(Phpfox::getLib('locale')->convert($aAlbum['name']), Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('photo.user_name_commented_on_span_class_drop_data_user_full_name_s_span_photo_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aAlbum['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten(Phpfox::getLib('locale')->convert($aAlbum['name']), Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }

        return array(
            'link' => array(
                'iAlbumId' => $aAlbum['album_id'],
                'sAlbumTitle' => Phpfox::getLib('locale')->convert($aAlbum['name'])
            ),
            'message' => ($sPhrase),
            'sModule' => 'advancedphoto',
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
        if (!isset($aData['iPhotoId']))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid")),
                'error_code' => 1
            );
        }
        if (!Phpfox::getUserParam('advancedphoto.can_view_photos'))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_can_not_view_photos")),
                'error_code' => 1
            );
        }
        /**
         * @var array
         */
        $aPhoto = Phpfox::getService('advancedphoto')->getPhoto($aData['iPhotoId'], Phpfox::getUserId());
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid_or_has_been_deleted"))
            );
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('advancedphoto', $aPhoto['photo_id'], $aPhoto['user_id'], $aPhoto['privacy'], $aPhoto['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        return Phpfox::getService('mfox.comment')->listallcomments(array('sType' => 'advancedphoto', 'iItemId' => $aPhoto['photo_id']));
    }
    
    /**
     * Check privacy comment.
     * @param int $iPhotoId
     * @return null|array Error message array.
     */
    public function checkPrivacyCommentOnPhoto($iPhotoId)
    {        
        if (!Phpfox::getUserParam('advancedphoto.can_view_photos'))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_can_not_view_photos")),
                'error_code' => 1
            );
        }
        /**
         * @var array
         */
        $aPhoto = Phpfox::getService('advancedphoto')->getPhoto($iPhotoId, Phpfox::getUserId());
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid_or_has_been_deleted"))
            );
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('advancedphoto', $aPhoto['photo_id'], $aPhoto['user_id'], $aPhoto['privacy'], $aPhoto['is_friend'], true))
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
        if (!Phpfox::getUserParam('advancedphoto.can_view_photos'))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_can_not_view_photos")),
                'error_code' => 1
            );
        }
        /**
         * @var array
         */
        $aPhoto = Phpfox::getService('advancedphoto')->getPhoto($iPhotoId, Phpfox::getUserId());
        if (!isset($aPhoto['photo_id']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_is_not_valid_or_has_been_deleted"))
            );
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('advancedphoto', $aPhoto['photo_id'], $aPhoto['user_id'], $aPhoto['privacy'], $aPhoto['is_friend'], true))
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
        if (!Phpfox::getUserParam('advancedphoto.can_view_photo_albums'))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_this_photo_albums"))
            );
        }
        if (!Phpfox::getUserParam('advancedphoto.can_view_photos'))
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
            $aAlbum = Phpfox::getService('advancedphoto.album')->getForProfileView($iAlbumId);
        }
        else
        {
            // Get the current album we are trying to view
            $aAlbum = Phpfox::getService('advancedphoto.album')->getForView($iAlbumId);
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
        if (!Phpfox::getUserParam('advancedphoto.can_view_photo_albums'))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_this_photo_albums"))
            );
        }
        if (!Phpfox::getUserParam('advancedphoto.can_view_photos'))
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
            $aAlbum = Phpfox::getService('advancedphoto.album')->getForProfileView($iAlbumId);
        }
        else
        {
            // Get the current album we are trying to view
            $aAlbum = Phpfox::getService('advancedphoto.album')->getForView($iAlbumId);
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
        /**
         * @var int
         */
        $iPhotoId = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;

        /**
         * @var array
         */
        $aPhoto = Phpfox::getService('advancedphoto')->getPhoto($iPhotoId, Phpfox::getUserId());
        
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
            $aAlbum = Phpfox::getService('advancedphoto.album')->getForProfileView($iAlbumId);
        }
        else
        {
            // Get the current album we are trying to view
            $aAlbum = Phpfox::getService('advancedphoto.album')->getForView($iAlbumId);
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
        
        if (($aRow['mature'] == 0 || (($aRow['mature'] == 1 || $aRow['mature'] == 2) && Phpfox::getUserId() && Phpfox::getUserParam('advancedphoto.photo_mature_age_limit') <= Phpfox::getUserBy('age'))) || $aRow['user_id'] == Phpfox::getUserId())
        {
            /**
             * @var string
             */
            $sImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aRow['server_id'],
                    'path' => 'photo.url_photo',
                    'file' => Phpfox::getService('advancedphoto')->getPhotoUrl(array_merge($aRow, array('full_name' => $aItem['full_name']))),
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
            'sTypeId' => 'advancedphoto',
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
            'sCommentTypeId' => 'advancedphoto',
            'sLikeTypeId' => 'advancedphoto',
            
            'aPhoto' => array(
                'iPhotoId' => $aRow['photo_id'],
                'sPhotoTitle' => $aRow['title'],
                'iAlbumId' => $aRow['album_id'],
                'sAlbumName' => Phpfox::getLib('locale')->convert($aRow['name'])
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
                $aReturn['sFeedInfo'] = strip_tags( Phpfox::getPhrase('feed.added_new_photos_to_gender_album_a_href_link_name_a', array('gender' => Phpfox::getService('user')->gender($aItem['gender'], 1), 'link' => Phpfox::permalink('photo.album', $aRow['album_id'], Phpfox::getLib('locale')->convert($aRow['name'])), 'name' => Phpfox::getLib('parse.output')->shorten(Phpfox::getLib('locale')->convert($aRow['name']), Phpfox::getParam('notification.total_notification_title_length')))));
				$aReturn['iAlbumId'] = $aRow['album_id'];
				$aReturn['sAlbumName'] =  Phpfox::getLib('locale')->convert($aRow['name']);
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

    public function __getPhoto($aData)
    {
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

        if (!Phpfox::getUserParam('advancedphoto.can_view_photos')) {
            return array(); // skip error
        }

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
            if (!Phpfox::getService('user.privacy')->hasAccess($aUser['user_id'], 'advancedphoto.display_on_profile'))
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
            case 'most_liked':
                $sSort = 'photo.total_like DESC';
                break;
            default:
                $sSort = 'photo.time_stamp DESC';
                break;
        }

        $this->_oSearch->setSort($sSort);

        $aBrowseParams = array(
            'module_id' => 'advancedphoto',
            'alias' => 'photo',
            'field' => 'photo_id',
            'table' => Phpfox::getT('photo'),
            'hide_view' => array('pending', 'my'),
            'service' => 'mfox.advancedphoto'
        );

        switch ($sView)
        {
            case 'pending':
                if (!Phpfox::getUserParam('advancedphoto.can_approve_photos')) {
                    return array(); // ignore error
                }
                $this->_oSearch->setCondition('AND photo.view_id = 1');
                break;
            case 'my':
                if (!Phpfox::isUser()) {
                    return array(); // ignore error
                }
                $this->_oSearch->setCondition('AND photo.user_id = ' . Phpfox::getUserId());
                break;          
            default:
                if ($bIsUserProfile)
                {
                    $this->_oSearch->setCondition('AND photo.view_id ' . ($aUser['user_id'] == Phpfox::getUserId() ? 'IN(0,2)' : '= 0') . ' AND photo.group_id = 0 AND photo.type_id < 2 AND photo.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ') AND photo.user_id = ' . (int) $aUser['user_id']);
                }
                else
                {                   
                    if (defined('PHPFOX_IS_PAGES_VIEW') || defined('PHPFOX_IS_GROUP_VIEW'))
                    {
                        $this->_oSearch->setCondition('AND photo.view_id = 0 AND photo.module_id = \'' . Phpfox::getLib('database')->escape($aParentModule['module_id']) . '\' AND photo.group_id = ' . (int) $aParentModule['item_id'] . ' AND photo.privacy IN(%PRIVACY%)');
                    }
                    else
                    {                   
                        $this->_oSearch->setCondition('AND photo.view_id = 0 AND photo.group_id = 0 AND photo.type_id < 2 AND photo.privacy IN(%PRIVACY%)');
                    }
                }
                break;  
        }
                
        if (!empty($iCategoryId))
        {
            $sCategory = $iCategoryId;
            $this->_oSearch->setCondition('AND pcd.category_id = ' . (int) $sCategory);
        }
        
        if ($this->_oReq->get('tag'))
        {
            if (($aTag = Tag_Service_Tag::instance()->getTagInfo('photo', $this->_oReq->get('tag'))))
            {
                $this->_oSearch->setCondition('AND tag.tag_text = \'' . Phpfox::getLib('database')->escape($aTag['tag_text']) . '\'');
            }
        }
        
        if ($sView == 'featured')
        { 
            $this->_oSearch->setCondition('AND photo.is_featured = 1');
        }
        
        Phpfox::getService('advancedphoto.browse')->category($sCategory);
        
        if (!Phpfox::getParam('advancedphoto.display_profile_photo_within_gallery'))
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

        return $aPhotos;
    }  

    public function query()
    {
        $this->database()->select('pa.name AS album_name, pa.profile_id AS album_profile_id ,pa.cover_id , ppc.name as category_name, ppc.category_id, ')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = photo.album_id')
            ->leftJoin(Phpfox::getT('photo_category_data'), 'ppcd', 'ppcd.photo_id = photo.photo_id')
            ->leftJoin(Phpfox::getT('photo_category'), 'ppc', 'ppc.category_id = ppcd.category_id')
            ->group('photo.photo_id');
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
                $aPhoto['album_title'] =  Phpfox::getPhrase('advancedphoto.profile_pictures');
                $aPhoto['album_url'] = Phpfox::permalink('advancedphoto.album.profile', $aPhoto['user_id'], $aPhoto['user_name']);
            }
            else
            {
                $aPhoto['album_url'] = Phpfox::permalink('advancedphoto.album', $aPhoto['album_id'], $aPhoto['album_title']);
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
            'sModelType' => 'advancedphoto',
        );

        if ($this->_oReq->get('in_detail') === true)
        {
            $aFeed = array(             
                'comment_type_id' => 'advancedphoto',
                'privacy' => $aPhoto['privacy'],
                'comment_privacy' => $aPhoto['privacy_comment'],
                'like_type_id' => 'advancedphoto',
                'feed_is_liked' => $aPhoto['is_liked'],
                'feed_is_friend' => $aPhoto['is_friend'],
                'item_id' => $aPhoto['photo_id'],
                'user_id' => $aPhoto['user_id'],
                'total_comment' => $aPhoto['total_comment'],
                'total_like' => $aPhoto['total_like'],
                'feed_link' => Phpfox::getLib('url')->permalink('advancedphoto', $aPhoto['photo_id'], $aPhoto['title']),
                'feed_title' => $aPhoto['title'],
                'feed_display' => 'view',
                'feed_total_like' => $aPhoto['total_like'],
                'report_module' => 'advancedphoto',
                'report_phrase' =>  Phpfox::getPhrase('advancedphoto.report_this_photo'));
            
            //  get list of liked user
            $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('advancedphoto'
                , $aPhoto['photo_id']
                , false
                , Phpfox::getParam('feed.total_likes_to_display'));
            $aUserLike = array();
            foreach($aLike['likes'] as $like){
                $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
            }

            $aUserDislike = array();
            $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('advancedphoto', $aPhoto['photo_id'], $bGetCount = false);
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
                'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('advancedphoto', $aPhoto['photo_id'], Phpfox::getUserId()),
                'bIsFeatured' => $aPhoto['is_featured'],
                'bIsFriend' => (bool) $aPhoto['is_friend'],
                'bIsInvisible' => $aPhoto['is_invisible'],
                'bIsLiked' => Phpfox::getService('mfox.like')->checkIsLiked('advancedphoto', $aPhoto['photo_id'], Phpfox::getUserId()),
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
                'iTotalComment' => Phpfox::getService('mfox.comment')->getCommentCount(array('sItemType' => 'advancedphoto', 'iItemId' => $aPhoto['photo_id']), null),
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
                'sAlbumType' => 'advancedphoto_album',
                'sAlbumUrl' => $aPhoto['album_url'],
                'sDescription' => $aPhoto['description'],
                'sExtension' => $aPhoto['extension'],
                'sFileName' => $aPhoto['file_name'],
                'sFileSize' => $aPhoto['file_size'],
                'sFullName' => $aPhoto['full_name'],
                'sItemType' => 'advancedphoto',
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
            $this->database()->innerJoin(Phpfox::getT('tag'), 'tag', 'tag.item_id = photo.photo_id AND tag.category_id = \'advancedphoto\'');
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
        //  init 
        if (!isset($aData['iPage'])){
            $aData['iPage'] = 1;
        }
        if ((int)$aData['iPage'] == 0){
            return array();
        }
		
		if($aData['sParentType'] == 'album'){
			$aData['iAmountOfPhoto'] = 1000;
		}
		

        $aParentModule = array(
            'module_id' => isset($aData['sModule']) ? $aData['sModule'] : '',
            'item_id' => isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0
        );        
            
        $iLastAlbumIdViewed = (isset($aData['iLastAlbumIdViewed']) && (int)$aData['iLastAlbumIdViewed'] > 0) ? (int) $aData['iLastAlbumIdViewed'] : 0;
        $iLastTime = isset($aData['iLastTime']) ? (int) $aData['iLastTime'] : 0;

        $lastAlbumViewed = null;
        if($iLastAlbumIdViewed){
            $lastAlbumViewed = $this->getAlbumByID($iLastAlbumIdViewed);
            if(isset($lastAlbumViewed['album_id'])){
                $iLastTime = $lastAlbumViewed['time_stamp'];
            }
        }
        $sAction = isset($aData['sAction']) ? $aData['sAction'] : 'more';
        $iAmountOfAlbum = (isset($aData['iAmountOfAlbum']) && (int) $aData['iAmountOfAlbum'] > 0) ? (int) $aData['iAmountOfAlbum'] : self::GETALBUM_ALBUM_LIMIT;

        $bIsUserProfile = false;
        if (isset($aData['bIsUserProfile']) && $aData['bIsUserProfile'] == 'true')
        {
            //  get album of spcecific userID
            $bIsUserProfile = true;
            if (!isset($aData['iUserId']))
            {
                return array(
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid")),
                    'result' => 0,
                    'error_code' => 1
                );
            }

            $aUser = Phpfox::getService('user')->get($aData['iUserId'], true);
            if (!$aUser)
            {
                return array(
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.user_is_not_valid")),
                    'result' => 0,
                    'error_code' => 1
                );
            }
        }
        $sCond = ' TRUE ';
        if ($iLastTime > 0)
        {
            if ($sAction == self::ACTION_TYPE_NEW)
            {
                $sCond .= ' AND pa.time_stamp > ' . $iLastTime . ' ';
            }
            else
            {
                $sCond .= ' AND pa.time_stamp < ' . $iLastTime . ' ';
            }
        }
        if ($bIsUserProfile)
        {
            $sCond .= ' AND pa.view_id ' . ($aUser['user_id'] == Phpfox::getUserId() ? 'IN(0,2)' : '= 0') . ' AND pa.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ') AND pa.user_id = ' . (int) $aUser['user_id'];
        }
        else
        {
            if ($aData['sView'] == self::GETALBUM_TYPE_MY)
            {
                $sCond .= ' AND pa.user_id = ' . Phpfox::getUserId();
            }
            else
            {
                $sCond .= ' AND pa.view_id = 0 AND pa.privacy IN(%PRIVACY%) AND pa.total_photo > 0';
            }
        }

        if (!empty($aData['sSearch']))
        {
            $sCond .= ' AND pa.name LIKE \'%' . $aData['sSearch'] . '%\' ';
        }

        // support pages
        if ($aParentModule !== null && !empty($aParentModule['item_id']))
        {
            $sCond .= (' AND pa.module_id = \'' . $aParentModule['module_id']. '\' AND pa.group_id = ' . (int) $aParentModule['item_id']);
        }

        if ($aData['sView'] != self::GETALBUM_TYPE_MY && !Phpfox::getParam('photo.display_profile_photo_within_gallery')) {
            $sCond .= ' AND pa.profile_id = 0';
            $sCond .= ' AND pa.cover_id = 0';
        }

        $bNoQueryFriend = false; 
		
		if($aData['sView'] == 'friend'){
			$bNoQueryFriend = true;	
		}
		    
        if (Phpfox::isModule('friend') && $bNoQueryFriend)
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = pa.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }

        switch ($aData['sView']) {
            case 'friend':
                $sCond = str_replace('%PRIVACY%', '0,1,2', $sCond);
                break;
            case self::GETALBUM_TYPE_MY:
                $sCond = str_replace('%PRIVACY%', '0,1,2,3,4', $sCond);
                break;
            default:
                $sCond = str_replace('%PRIVACY%', '0', $sCond);
                break;
        }

        $iCnt = $this->database()
                ->select('COUNT(pa.album_id)')
                ->from(Phpfox::getT('photo_album'), 'pa')
                ->where($sCond)
                ->execute('getSlaveField');

        // check current page with total results 
        list($fromResult, $toResult, $isShowPrev, $pagePrev, $isShowNext, $pageNext) = Phpfox::getService('mfox.core')->caculatePaging((int)$iCnt, (int)$iAmountOfAlbum, (int)$aData['iPage'] - 1);
        if($pageNext == 0){
            return array();
        }

        $this->database()
                ->select('p.destination, p.server_id, ')
                ->leftJoin(Phpfox::getT('photo'), 'p', 'p.album_id = pa.album_id AND pa.view_id = 0 AND p.is_cover = 1');
        $this->database()
                ->select('pa.*, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('photo_album'), 'pa')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = pa.user_id')
                ->where($sCond);
        if ($iAmountOfAlbum > 0)
        {
            $this->database()->limit($aData['iPage'], $iAmountOfAlbum, $iCnt);
        }

        // order
        $order = 'pa.time_stamp DESC';
        if (!empty($aData['sOrder']))
        {
            if ($aData['sOrder'] == 'popular')
            {
                $order = 'pa.total_comment DESC';
            }
            else if ($aData['sOrder'] == 'recent')
            {
                $order = 'pa.time_stamp_update DESC';
            }
            else if ($aData['sOrder'] == 'latest')
            {
                $order = 'pa.time_stamp DESC';
            }
            else if ($aData['sOrder'] == 'most_disscussed')
            {
                $order = 'pa.total_comment DESC';
            }
        }
		
		if (Phpfox::isModule('friend') && $bNoQueryFriend)
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = pa.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }
		

        $aAlbumPhotos = $this->database()                
                ->order($order)
                ->execute('getRows');

        $aResult = array();
        $photoPicSizes = Phpfox::getParam('photo.photo_pic_sizes');
        $largestPhotoSize = '_1024';
        if(count($photoPicSizes) > 0){
            $largestPhotoSize = '_' . $photoPicSizes[count($photoPicSizes) - 1];
        }
        foreach ($aAlbumPhotos as $aAlbum)
        {
            //  get list of liked user
            $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('advancedphoto_album'
                , $aAlbum['album_id']
                , false
                , Phpfox::getParam('feed.total_likes_to_display'));
            $aUserLike = array();
            foreach($aLike['likes'] as $like){
                $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
            }

            //  get profile image URL of user  
            $sUserProfileImg_Url = Phpfox::getService('mfox.user')->getImageUrl($aAlbum, '_50_square');

            //  get list of photos in album 
            $photos = $this->getPhotoByAlbumID($aAlbum['album_id']);
            $aSamplePhotos = array();
            foreach($photos as $photo){
                // thumbnail image 
                $sThumbUrl = Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $photo['server_id'],
                        'path' => 'photo.url_photo',
                        'file' => $photo['destination'],
                        'suffix' => '_500',
                        'return_url' => true
                            )
                    );
                // original image of largest image 
                $sPhotoUrl = Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $photo['server_id'],
                        'path' => 'photo.url_photo',
                        'suffix'=>'_500',
                        'file' => $photo['destination'],
                        'return_url' => true
                            )
                    );
                if (!is_file($sPhotoUrl)){
                    $sPhotoUrl = Phpfox::getLib('image.helper')->display(array(
                            'server_id' => $photo['server_id'],
                            'path' => 'photo.url_photo',
                            'file' => $photo['destination'],
                        	'suffix'=>'_500',
                            'return_url' => true
                                )
                        );
                }

                $aSamplePhotos[] = array(
                    'iPhotoId' => $photo['photo_id']
                    , 'sThumbUrl' => $sThumbUrl
                    , 'sPhotoUrl' => $sPhotoUrl
                );
            }

            $aResult[] = array(
                'iAlbumId' => $aAlbum['album_id'],
                'iProfilePageId' => $aAlbum['profile_page_id'],
                'sModelType' => 'advancedphoto_album',
                'sAlbumImageURL' => Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aAlbum['server_id'],
                    'path' => 'photo.url_photo',
                    'file' => $aAlbum['destination'],
                    'suffix' => '_500',
                    'return_url' => true
                        )
                ),
                'sName' => Phpfox::getLib('locale')->convert($aAlbum['name']),
                'iTotalPhoto' => $aAlbum['total_photo'],
                'iTimeStamp' => $aAlbum['time_stamp'],
                'iTimeStampUpdate' => $aAlbum['time_stamp_update'],
                'iTotalComment' => Phpfox::getService('mfox.comment')->getCommentCount(array('sItemType' => 'advancedphoto_album', 'iItemId' => $aAlbum['album_id']), null),
                'iTotalLike' => count($aUserLike),
                'iTotalDislike' => $aAlbum['total_dislike'],
                'iPrivacy' => $aAlbum['privacy'],
                'iPrivacyComment' => $aAlbum['privacy_comment'],
                'iUserId' => $aAlbum['user_id'],
                'sModule' => $aAlbum['module_id'],
                'iItemId' => $aAlbum['group_id'],
                'aUserLike' => $aUserLike,
                'sUserName' => $aAlbum['full_name'],
                'sUserImageUrl' => $sUserProfileImg_Url,
                'aSamplePhotos' => $aSamplePhotos,
                'bIsLiked' => Phpfox::getService('mfox.like')->checkIsLiked('advancedphoto_album', $aAlbum['album_id'], Phpfox::getUserId()),
                'iViewId' => $aAlbum['view_id'], 
                'bCanEdit' => $this->canEditAlbum($aAlbum),
                'bCanDelete' => $this->canDeleteAlbum($aAlbum),
            );
        }

        return $aResult;
    }  

    public function getAlbumByID($iId)
    {
        return $this->database()->select('pa.*, pai.*')
            ->from(Phpfox::getT('photo_album'), 'pa')
            ->join(Phpfox::getT('photo_album_info'), 'pai', 'pai.album_id = pa.album_id')
            ->where('pa.album_id = ' . (int) $iId)
            ->execute('getSlaveRow');        
    }

    public function getPhotoByAlbumID($albumID){
        return $this->database()->select('p.*')
            ->from(Phpfox::getT('photo_album'), 'pa')
            ->join(Phpfox::getT('photo'), 'p', 'p.album_id = pa.album_id AND pa.view_id = 0')
            ->where('pa.album_id = ' . (int) $albumID)
            ->execute('getSlaveRows');  
    }

    public function getCoverByAlbumID($albumID){
            return $this->database()->select('p.destination, p.server_id, p.mature')
                ->from(Phpfox::getT('photo_album'), 'pa')
                ->leftJoin(Phpfox::getT('photo'), 'p', 'p.album_id = pa.album_id AND pa.view_id = 0 AND p.is_cover = 1')
                ->where('pa.album_id = ' . (int) $albumID)
                ->execute('getSlaveRows');          
    }

    public function getAlbumByPhotoID($photoID){
        return $this->database()->select('pa.*')
            ->from(Phpfox::getT('photo'), 'p')
            ->leftJoin(Phpfox::getT('photo_album'), 'pa', 'pa.album_id = p.album_id')           
            ->where('p.photo_id = ' . (int) $photoID)
            ->execute('getSlaveRow');       
    }

    public function filter_album($aData){
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
        // init
        // process 
        $categories = Phpfox::getService('advancedphoto.category')->getForBrowse();
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
        if (!Phpfox::getUserParam('advancedphoto.photo_must_be_approved') && Phpfox::isModule('feed')){
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
                        ->add('advancedphoto'
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
        $response  =  array(
            'view_options'=> Phpfox::getService('mfox.privacy')->privacy($aData),
            'comment_options'=> Phpfox::getService('mfox.privacy')->privacycomment($aData),
            'category_options'=> $this->categories($aData),
        );

        $iValue = Phpfox::getService('user.privacy')->getValue('advancedphoto.default_privacy_setting');
        $response['default_privacy_setting'] = Phpfox::getService('mfox.privacy')->defaultprivacy("$iValue", $response['view_options']);
        
        return $response;
    }

    public function canEditAlbum($aItem){
        if((Phpfox::getUserId() == $aItem['user_id'] && Phpfox::getUserParam('advancedphoto.can_edit_own_photo_album')) 
            || Phpfox::getUserParam('advancedphoto.can_edit_other_photo_albums')){
            return true;
        }

        return false;
    }

    public function canDeleteAlbum($aItem){
        if($aItem['profile_id'] == '0' && (((Phpfox::getUserId() == $aItem['user_id'] && Phpfox::getUserParam('advancedphoto.can_delete_own_photo_album')) || Phpfox::getUserParam('advancedphoto.can_delete_other_photo_albums'))))
        {
            return true;
        }

        return false;
    }

    public function canEditPhoto($aItem){
        if((Phpfox::getUserParam('advancedphoto.can_edit_own_photo') && $aItem['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('advancedphoto.can_edit_other_photo')){
            return true;
        }

        return false;
    }

    public function canDeletePhoto($aItem){
        if((Phpfox::getUserParam('advancedphoto.can_delete_own_photo') && $aItem['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('advancedphoto.can_delete_other_photos')){
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
