<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Photoalbum extends Phpfox_Service {

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

    public function query()
    {
        return Phpfox::getService('photo.album.browse')->query();
    }

    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Mfox_Service_Friend::instance()->queryJoin($bNoQueryFriend))
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = pa.user_id AND friends.friend_user_id = ' . Phpfox::getUserId()); 
        }
        
        // http://www.phpfox.com/tracker/view/14733/
        if (Phpfox::isModule('like'))
        {
            $this->database()->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = "photo_album" AND l.item_id = pa.album_id AND l.user_id = ' . Phpfox::getUserId() . '');                
        }
        // END
    }

    public function processRows(&$aRows)
    {
        $aAlbums = $aRows;
        $aRows = array();
        
        $largestPhotoSize = '_1024';
        $photoPicSizes = Phpfox::getParam('photo.photo_pic_sizes');
        if(count($photoPicSizes) > 0){
            $largestPhotoSize = '_' . $photoPicSizes[count($photoPicSizes) - 1];
        }

        foreach ($aAlbums as $aAlbum)
        {
            //  get list of liked user
            $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('photo_album'
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
            $photos = Phpfox::getService('mfox.photo')->getPhotoByAlbumID($aAlbum['album_id']);
            $aSamplePhotos = array();
            foreach($photos as $photo){
                // thumbnail image 
                $sThumbUrl = Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $photo['server_id'],
                        'path' => 'photo.url_photo',
                        'file' => $photo['destination'],
                        'suffix' => MAX_SIZE_OF_USER_IMAGE_PHOTO,
                        'return_url' => true
                            )
                    );
                // original image of largest image 
                $sPhotoUrl = Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $photo['server_id'],
                        'path' => 'photo.url_photo',
                        'file' => $photo['destination'],
                        'return_url' => true
                            )
                    );
                if (!is_file($sPhotoUrl)){
                    $sPhotoUrl = Phpfox::getLib('image.helper')->display(array(
                            'server_id' => $photo['server_id'],
                            'path' => 'photo.url_photo',
                            'file' => $photo['destination'],
                            'suffix' => $largestPhotoSize,
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

            $aRows[] = array(
                'iAlbumId' => $aAlbum['album_id'],
                'iProfilePageId' => $aAlbum['profile_page_id'],
                'sModelType' => 'photo_album',
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
                'iTotalComment' => Phpfox::getService('mfox.comment')->getCommentCount(array('sItemType' => 'photo_album', 'iItemId' => $aAlbum['album_id']), null),
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
                'bIsLiked' => Phpfox::getService('mfox.like')->checkIsLiked('photo_album', $aAlbum['album_id'], Phpfox::getUserId()),
                'iViewId' => $aAlbum['view_id'], 
                'bCanEdit' => Phpfox::getService('mfox.photo')->canEditAlbum($aAlbum),
                'bCanDelete' => Phpfox::getService('mfox.photo')->canDeleteAlbum($aAlbum),
            );
        }
    }
}

?>
