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
 * @since May 24, 2013
 * @link Mfox Api v2.0
 */
class Mfox_Service_Album extends Phpfox_Service {

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
     * Use to set the size of photos.
     * @var array Photo sizes.
     */
    private $_aPhotoSizes = array(50, 120, 200);

    private $_sDefaultImageAlbumPath = '';
	
	private $_bIsAdvancedModule =  false;

    public function __construct()
    {
        $this->_oReq = Mfox_Service_Request_Request::instance();
        $this->_oSearch = Mfox_Service_Search_Search::instance();
        $this->_oBrowse = Mfox_Service_Search_Browse::instance();

        $this->_sDefaultImageAlbumPath = Phpfox::getParam('core.url_module') . 'mfox/static/image/music_cover_default.png';
		
		$isUsing = Phpfox::getParam('mfox.replace_music');
        $isAdv = Phpfox::isModule('musicsharing');
        $isDefault = Phpfox::isModule('music');

        $this->_bIsAdvancedModule = Phpfox::getService('mfox.core')->isAdvancedModule($isUsing, $isAdv, $isDefault);	
    }
	
	public function isAdvancedModule(){
		return $this->_bIsAdvancedModule;
	}

    public function getDefaultImageAlbumPath(){
        return $this->_sDefaultImageAlbumPath;
    }    

    /**
     * Input data:
     * + sName: string, required.
     * + sDescription: string, optional.
     * + sPrivacyView: string, optional.
     * + sPrivacyComment: string, optional.
     * + iSearch: int, optional
     * + image: file, optional.
     *
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * + error_message: string
     * + iAlbumId: int.
     * + sAlbumTitle: string.
     *
     */
    public function create($aData)
    {
        if (!Phpfox::getUserParam('music.can_access_music') || !Phpfox::getUserParam('music.can_upload_music_public'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music_module")));
        }

        $playlist_title = isset($aData['sName']) ? $aData['sName'] : '';
        if (empty($playlist_title))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_album_name")),
                'error_element' => 'sName',
                'error_code' => 1
            );
        }

        $aData['sPrivacyView'] = isset($aData['sPrivacyView']) ? $aData['sPrivacyView'] : 0;
        $aData['iPrivacy'] = $aData['sPrivacyView'];
        $aData['sPrivacyComment'] = isset($aData['sPrivacyComment']) ? $aData['sPrivacyComment'] : 0;
        $aData['iPrivacyComment'] = $aData['sPrivacyComment'];

        $aVals = array(
            'name' => isset($aData['sName']) ? $aData['sName'] : '',
            'year' => isset($aData['sYear']) ? $aData['sYear'] : '',
            'text' => isset($aData['sDescription']) ? $aData['sDescription'] : '',
            'privacy' => isset($aData['iPrivacy']) ? (int) $aData['iPrivacy'] : 0,
            'privacy_comment' => isset($aData['iPrivacyComment']) ? (int) $aData['iPrivacyComment'] : 0,
            'privacy_list' => isset($aData['sPrivacyList']) ? explode(',', $aData['sPrivacyList']) : array()
        );

        $aValidation = array(
            'name' =>  Phpfox::getPhrase('music.provide_a_name_for_this_album'),
            'year' => array(
                'def' => 'year'
            )
        );

        $oValidator = Phpfox::getLib('validator')->set(array(
            'sFormName' => 'js_album_form',
            'aParams' => $aValidation
                )
        );

        // Validate data.
        if ($oValidator->isValid($aVals))
        {
            if ($iId = $this->add($aVals))
            {
                return array('result' => 1
                    , 'error_code' => 0
                    , 'message' =>  Phpfox::getPhrase('music.album_successfully_added')
                    , 'iAlbumId' => $iId
                    , 'iAlbumTitle' => $aData['sName']
                );
            }
        }

        return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
    }

    /**
     * Input data:
     * + iAlbumId: int, required.
     * + sName: string, required.
     * + sDescription: string, optional.
     * + iSearch: int, optional
     * + sPrivacyView: int, optional.
     * + sPrivacyComment: int, optional.
     * + image: file, optional.
     *
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * + iAlbumId: int.
     * + sAlbumTitle: string.
     *
     */
    public function edit($aData)
    {
        if (!Phpfox::getUserParam('music.can_access_music') || !Phpfox::getUserParam('music.can_upload_music_public'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music_module")));
        }

        $iAlbumId = isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : 0;

        $aVals = array(
            'name' => isset($aData['sName']) ? $aData['sName'] : '',
            'year' => isset($aData['sYear']) ? $aData['sYear'] : '',
            'text' => isset($aData['sDescription']) ? $aData['sDescription'] : '',
            'privacy' => isset($aData['iPrivacy']) ? (int) $aData['iPrivacy'] : 0,
            'privacy_comment' => isset($aData['iPrivacyComment']) ? (int) $aData['iPrivacyComment'] : 0,
            'privacy_list' => isset($aData['sPrivacyList']) ? explode(',', $aData['sPrivacyList']) : array()
        );

        $aValidation = array(
            'name' =>  Phpfox::getPhrase('music.provide_a_name_for_this_album'),
            'year' => array(
                'def' => 'year'
            )
        );

        $oValidator = Phpfox::getLib('validator')->set(array(
            'sFormName' => 'js_album_form',
            'aParams' => $aValidation
                )
        );

        if ($oValidator->isValid($aVals))
        {
            if ($this->update($iAlbumId, $aVals))
            {
                return array('result' => 1
                    , 'error_code' => 0
                    , 'message' =>  Phpfox::getPhrase('music.album_successfully_updated')
                    , 'iAlbumId' => $iAlbumId
                    , 'sAlbumTitle' => $aData['sName']
                );
            }
        }

        return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
    }

    /**
     * 
     * @see Mfox_Service_Ban
     * @see Privacy_Service_Privacy
     * 
     * @param int $iId Album id.
     * @param array $aVals
     * Input data:
     * + name: string, required.
     * + year: string, required.
     * + text: string, optional.
     * + privacy: int, optional.
     * + privacy_comment: int, optional.
     * + privacy_list: string, optional, ex: "5,4,9".
     * + image: file upload.
     * 
     * @return boolean
     */
    private function update($iId, $aVals)
    {
        /**
         * @var array
         */
        $aAlbum = $this->database()->select('*')
                ->from(Phpfox::getT('music_album'))
                ->where('album_id = ' . (int) $iId)
                ->execute('getSlaveRow');

        if (!isset($aAlbum['album_id']))
        {
            return Phpfox_Error::set( Phpfox::getPhrase('music.unable_to_find_the_album_you_want_to_edit'));
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        if (!Phpfox::getService('mfox.ban')->checkAutomaticBan($aVals['name'] . ' ' . $aVals['text']))
        {
            return false;
        }

        if (($aAlbum['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('music.can_edit_own_albums')) || Phpfox::getUserParam('music.can_edit_other_music_albums'))
        {
            if (empty($aVals['privacy']))
            {
                $aVals['privacy'] = 0;
            }

            if (empty($aVals['privacy_comment']))
            {
                $aVals['privacy_comment'] = 0;
            }

            $this->database()->update(Phpfox::getT('music_album'), array(
                'privacy' => (isset($aVals['privacy']) ? $aVals['privacy'] : '0'),
                'privacy_comment' => (isset($aVals['privacy_comment']) ? $aVals['privacy_comment'] : '0'),
                'name' => $this->preParse()->clean($aVals['name'], 255),
                'year' => $aVals['year']
                    ), 'album_id = ' . $aAlbum['album_id']
            );

            $this->database()->update(Phpfox::getT('music_album_text'), array(
                'text' => (empty($aVals['text']) ? null : $this->preParse()->clean($aVals['text'])),
                'text_parsed' => (empty($aVals['text']) ? null : $this->preParse()->prepare($aVals['text']))
                    ), 'album_id = ' . $aAlbum['album_id']
            );
            /**
             * @var array
             */
            $aSongs = $this->database()->select('song_id, user_id')
                    ->from(Phpfox::getT('music_song'))
                    ->where('album_id = ' . (int) $aAlbum['album_id'])
                    ->execute('getSlaveRows');

            if (count($aSongs))
            {
                foreach ($aSongs as $aSong)
                {
                    $this->database()->update(Phpfox::getT('music_song'), array(
                        'privacy' => (isset($aVals['privacy']) ? $aVals['privacy'] : '0'),
                        'privacy_comment' => (isset($aVals['privacy_comment']) ? $aVals['privacy_comment'] : '0'),
                            ), 'song_id = ' . $aSong['song_id']
                    );

                    (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->update('music_album', $aSong['song_id'], $aVals['privacy'], $aVals['privacy_comment'], 0, $aSong['user_id']) : null);

                    if (Phpfox::isModule('privacy'))
                    {
                        if ($aVals['privacy'] == '4')
                        {
                            Phpfox::getService('privacy.process')->update('music_song', $aSong['song_id'], (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : array()));
                        }
                        else
                        {
                            Phpfox::getService('privacy.process')->delete('music_song', $aSong['song_id']);
                        }
                    }
                }
            }

            if (Phpfox::isModule('privacy'))
            {
                if ($aVals['privacy'] == '4')
                {
                    Phpfox::getService('privacy.process')->update('music_album', $iId, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : array()));
                }
                else
                {
                    Phpfox::getService('privacy.process')->delete('music_album', $iId);
                }
            }

            /**
             * Check the upload file image.
             */
            if (!empty($_FILES['image']['name']))
            {
                /**
                 * @var array
                 */
                $aImage = Phpfox::getLib('file')->load('image', array(
                    'jpg',
                    'gif',
                    'png'
                        )
                );

                if ($aImage === false)
                {
                    return false;
                }

                $oImage = Phpfox::getLib('image');
                $oFile = Phpfox::getLib('file');
                /**
                 * @var string
                 */
                $sFileName = Phpfox::getLib('file')->upload('image', Phpfox::getParam('music.dir_image'), $iId);
                /**
                 * @var int
                 */
                $iFileSizes = filesize(Phpfox::getParam('music.dir_image') . sprintf($sFileName, ''));

                foreach ($this->_aPhotoSizes as $iSize)
                {
                    $oImage->createThumbnail(Phpfox::getParam('music.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('music.dir_image') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);
                    $oImage->createThumbnail(Phpfox::getParam('music.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('music.dir_image') . sprintf($sFileName, '_' . $iSize . '_square'), $iSize, $iSize, false);

                    $iFileSizes += filesize(Phpfox::getParam('music.dir_image') . sprintf($sFileName, '_' . $iSize));
                }

                $this->database()->update(Phpfox::getT('music_album'), array('image_path' => $sFileName, 'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID')), 'album_id = ' . $iId);

                // Update user space usage
                Phpfox::getService('user.space')->update($aAlbum['user_id'], 'music_image', $iFileSizes);
            }

            return true;
        }

        return Phpfox_Error::set( Phpfox::getPhrase('music.unable_to_edit_this_album'));
    }

    private function add($aVals)
    {
        if (!empty($_FILES['image']['name']))
        {
            $aImage = Phpfox::getLib('file')->load('image', array(
                'jpg',
                'gif',
                'png'
                    )
            );

            if ($aImage === false)
            {
                return false;
            }
        }

        if (empty($aVals['privacy']))
        {
            $aVals['privacy'] = 0;
        }

        if (empty($aVals['privacy_comment']))
        {
            $aVals['privacy_comment'] = 0;
        }

        if (!Phpfox::getService('mfox.ban')->checkAutomaticBan($aVals['name'] . ' ' . $aVals['text']))
        {
            return false;
        }

        $iId = $this->database()->insert(Phpfox::getT('music_album'), array(
            'view_id' => 0,
            'privacy' => (isset($aVals['privacy']) ? $aVals['privacy'] : '0'),
            'privacy_comment' => (isset($aVals['privacy_comment']) ? $aVals['privacy_comment'] : '0'),
            'user_id' => Phpfox::getUserId(),
            'name' => $this->preParse()->clean($aVals['name'], 255),
            'year' => $aVals['year'],
            'time_stamp' => PHPFOX_TIME
                )
        );

        if (!$iId)
        {
            return false;
        }

        $this->database()->insert(Phpfox::getT('music_album_text'), array(
            'album_id' => $iId,
            'text' => (empty($aVals['text']) ? null : $this->preParse()->clean($aVals['text'])),
            'text_parsed' => (empty($aVals['text']) ? null : $this->preParse()->prepare($aVals['text']))
                )
        );

        if (isset($aImage))
        {
            $oImage = Phpfox::getLib('image');

            $sFileName = Phpfox::getLib('file')->upload('image', Phpfox::getParam('music.dir_image'), $iId);

            $iFileSizes = filesize(Phpfox::getParam('music.dir_image') . sprintf($sFileName, ''));

            foreach ($this->_aPhotoSizes as $iSize)
            {
                $oImage->createThumbnail(Phpfox::getParam('music.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('music.dir_image') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);
                $oImage->createThumbnail(Phpfox::getParam('music.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('music.dir_image') . sprintf($sFileName, '_' . $iSize . '_square'), $iSize, $iSize, false);

                $iFileSizes += filesize(Phpfox::getParam('music.dir_image') . sprintf($sFileName, '_' . $iSize));
            }

            $this->database()->update(Phpfox::getT('music_album'), array('image_path' => $sFileName, 'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID')), 'album_id = ' . $iId);

            // Update user space usage
            Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'music_image', $iFileSizes);
        }

        if ($aVals['privacy'] == '4')
        {
            Phpfox::getService('privacy.process')->add('music_album', $iId, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : array()));
        }

        return $iId;
    }

    /**
     * Input data:
     * + iAlbumId: int, required.
     *
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * + error_message: string.
     *
     */
    public function delete($aData)
    {
        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music_module")));
        }

        $iAlbumId = isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : 0;
        $aAlbum = Phpfox::getService('music.album')->getAlbum($iAlbumId);
        if (!isset($aAlbum['album_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_find_the_album_you_want_to_delete")));
        }

        // Check privacy.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        if (Phpfox::getService('music.album.process')->delete($iAlbumId))
        {
            return array('result' => 1
                , 'error_code' => 0
                , 'message' =>  Phpfox::getPhrase('music.album_successfully_deleted'));
        }

        return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
    }

    /**
     * Input data:
     * + iAlbumId: int, required.
     *
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * + sAlbumName: string.
     * + bIsOnProfile: bool.
     * + iSongId: int.
     * + iAlbumId: int.
     * + iUserId: int.
     * + sTitle: string.
     * + sSongPath: string.
     * + iOrdering: int.
     * + iTotalPlay: int.
     * + iTimeStamp: int.
     * + sTimeStamp: string.
     * + sFullTimeStamp: string.
     * + sFullname: string.
     * + sUserImage: string.
     * + sAlbumImage: string
     * + bIsInvisible: bool.
     * + iUserLevelId: int.
     *
     */
    public function list_songs($aData)
    {
        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music_module")));
        }

        $iAlbumId = isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : 0;
        $aAlbum = Phpfox::getService('music.album')->getAlbum($iAlbumId);
        if (!isset($aAlbum['album_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_find_the_album_you_want_to_get_songs")));
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        $aSongs = Phpfox::getService('music.album')->getTracks($aAlbum['user_id'], $aAlbum['album_id'], true);        
        // Update play time for song and album.
        if (isset($aSongs[0]))
        {
            Phpfox::getService('music.process')->play($aSongs[0]['song_id']);
        }
        
        $aResult = array();
        $ordering = 0;
        foreach ($aSongs as $aSong)
        {
            $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aSong, '_50_square');

            if($aAlbum['image_path'] == null){
                $sAlbumImage = $this->_sDefaultImageAlbumPath;
            } else {
                $sAlbumImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aAlbum['server_id'],
                    'path' => 'music.url_image',
                    'file' => $aAlbum['image_path'],
                    'suffix' => '',
                    'return_url' => true
                        )
                );            
            }

            $aResult[] = array(
                'iSongId' => $aSong['song_id'],
                'iUserId' => $aSong['user_id'],
                'iAlbumId' => $aSong['album_id'],
                'sTitle' => $aSong['title'],
                'iTotalPlay' => $aSong['total_play'],
                'sSongPath' => $aSong['song_path'],
                'bIsFeatured' => (bool) $aSong['is_featured'],
                'sSongPath' => $aSong['song_path'],
                'iViewId' => $aSong['view_id'],
                'iServerId' => $aSong['server_id'],
                'iExplicit' => $aSong['explicit'],
                'sDuration' => $aSong['duration'],
                'iTimeStamp' => $aSong['time_stamp'],
                'sTimeStamp' => date("D, j M Y G:i:s O", (int)$aSong['time_stamp']),
                'sFullTimeStamp' => Phpfox::getLib('date')->convertTime($aSong['time_stamp'], null),
                'sAlbumUrl' => $aSong['album_url'],
                'sUsername' => $aSong['user_name'],
                'bIsOnProfile' => isset($aSong['is_on_profile']) ? true : false,
                'iProfileUserId' => isset($aSong['profile_user_id']) ? (int) $aSong['profile_user_id'] : 0,
                'iProfilePageId' => $aSong['profile_page_id'],
                'iUserServerId' => $aSong['user_server_id'],
                'sFullname' => $aSong['full_name'],
                'iGender' => $aSong['gender'],
                'sUserImage' => $sUserImage,
                'bIsInvisible' => (bool) $aSong['is_invisible'],
                'iUserGroupId' => $aSong['user_group_id'],
                'iUserLevelId' => $aSong['user_group_id'],
                'sAlbumImage' => $sAlbumImage,
                'iOrdering' => $ordering,
                'iLanguageId' => isset($aSong['language_id']) ? $aSong['language_id'] : 0
            );

            $ordering ++;
        }
        return $aResult;
    }

    public function field_songs($aAlbum)
    {
        
        $aSongs = Phpfox::getService('music.album')->getTracks($aAlbum['user_id'], $aAlbum['album_id'], true);        
        // Update play time for song and album.
        if (isset($aSongs[0]))
        {
            Phpfox::getService('music.process')->play($aSongs[0]['song_id']);
        }
        
        $aResult = array();
        $ordering = 0;
        foreach ($aSongs as $aSong)
        {
            $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aSong, '_50_square');

            if($aAlbum['image_path'] == null){
                $sAlbumImage = $this->_sDefaultImageAlbumPath;
            } else {
                $sAlbumImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aAlbum['server_id'],
                    'path' => 'music.url_image',
                    'file' => $aAlbum['image_path'],
                    'suffix' => '',
                    'return_url' => true
                        )
                );            
            }

            $aResult[] = array(
                'iSongId' => $aSong['song_id'],
                'iUserId' => $aSong['user_id'],
                'iAlbumId' => $aSong['album_id'],
                'sTitle' => $aSong['title'],
                'iTotalPlay' => $aSong['total_play'],
                'sSongPath' => $aSong['song_path'],
                'bIsFeatured' => (bool) $aSong['is_featured'],
                'sSongPath' => $aSong['song_path'],
                'iViewId' => $aSong['view_id'],
                'iServerId' => $aSong['server_id'],
                'iExplicit' => $aSong['explicit'],
                'sDuration' => $aSong['duration'],
                'iTimeStamp' => $aSong['time_stamp'],
                'sTimeStamp' => date("D, j M Y G:i:s O", (int)$aSong['time_stamp']),
                'sFullTimeStamp' => Phpfox::getLib('date')->convertTime($aSong['time_stamp'], null),
                'iOrdering' => $ordering,
            );

            $ordering ++;
        }
        return $aResult;
    }

    /**
     * Input data:
     * + iAlbumId: int, required.
     * 
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * 
     * @param int $iAlbumId
     * @return array
     */
    public function checkPrivacyCommentOnMusicAlbum($iAlbumId)
    {
        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music_module")));
        }
        /**
         * @var array
         */
        $aAlbum = Phpfox::getService('music.album')->getAlbum($iAlbumId);

        if (!isset($aAlbum['album_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_find_the_album_you_want_to_get_songs")));
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        if (!Phpfox::getService('mfox.comment')->checkCanPostCommentOnItem($aAlbum))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_this_item")));
        }

        return null;
    }

    /**
     * Input data:
     * + iAlbumId: int, required.
     * 
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * 
     * @param int $iAlbumId
     * @return array
     */
    public function checkPrivacyOnMusicAlbum($iAlbumId)
    {
        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music_module")));
        }
        /**
         * @var array
         */
        $aAlbum = Phpfox::getService('music.album')->getAlbum($iAlbumId);
        if (!isset($aAlbum['album_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_find_the_album_you_want_to_get_songs")));
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        return null;
    }
    
    /**
     * Input data:
     * + iAlbumId: int, required.
     * + lastCommentIdViewed: int, optional.
     * + amountOfComment: int, optional.
     * 
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * + sImage: string.
     * + iTimestamp: int.
     * + sTime: string.
     * + sTimeConverted: string.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see album/list_comment
     * 
     * @param array $aData
     * @return array
     */
    public function list_comment($aData)
    {
        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music_module")));
        }
        /**
         * @var int
         */
        $iAlbumId = isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : 0;
        /**
         * @var array
         */
        $aAlbum = Phpfox::getService('music.album')->getAlbum($iAlbumId);
        if (!isset($aAlbum['album_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_find_the_album_you_want_to_get_songs")));
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        $aData['sType'] = 'music_album';
        $aData['iItemId'] = $iAlbumId;
        return Phpfox::getService('mfox.comment')->listallcomments($aData);
    }

    /**
     * Input data:
     * + sAction: string, optional.
     * + iLastAlbumId: int, optional.
     * + sView: string, optional. Ex: my, all
     * + iAmountOfAlbum: int, optional.
     * + sSearch: string, optional.
     *
     * Output data:
     * + bIsLiked: bool.
     * + iAlbumId: int.
     * + iUserId: int.
     * + sName: string.
     * + sImagePath: string.
     * + iTotalTrack: int.
     * + iTotalPlay: int.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + aUserLike array
     * + iTimeStamp: int.
     * + sTimeStamp: string.
     * + sFullTimeStamp: string.
     * + sFullname: string.
     * + sUserImage: string.
     * + bIsInvisible: bool.
     * + iUserLevelId: int.
     *
     */
    public function search($aData)
    {
        return $this->getAlbums($aData);
    }
    
    private function getAlbums($aData)
    {
        $this->_oReq->set(array(
            'view' => !empty($aData['sView']) ? $aData['sView'] : '',
            'page' => !empty($aData['iPage']) ? (int) $aData['iPage'] : 1,
            'show' => !empty($aData['iLimit']) ? (int) $aData['iLimit'] : 10,
            'search' => !empty($aData['sSearch']) ? $aData['sSearch'] : '',
            'sort' => !empty($aData['sOrder']) ? $aData['sOrder'] : '',
            'module_id' => !empty($aData['sModule']) ? $aData['sModule'] : null,
            'item_id' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : null,
            'profile' => (!empty($aData['bIsProfile']) && $aData['bIsProfile'] == 'true') ? true : false,
            'profile_id' => !empty($aData['iProfileId']) ? (int) $aData['iProfileId'] : null,
        ));

        Phpfox::getUserParam('music.can_access_music', true);

        $sView = $this->_oReq->get('view');

        $bIsProfile = false;
        if ($this->_oReq->get('profile') === true)
        {
            $bIsProfile = true;
            $aUser = Phpfox::getService('user')->get($this->_oReq->get('profile_id'));
        }

        // search
        $sSearch = $this->_oReq->get('search');
        if (!empty($sSearch))
        {
            $this->_oSearch->setCondition('AND m.name LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"');
        }

        // sort
        $sSort = '';
        switch ($this->_oReq->get('sort')) {
            case 'most_viewed':
                $sSort = 'm.total_view DESC';
                break;
            case 'most_liked':
                $sSort = 'm.total_like DESC';
                break;
            case 'most_discussed':
                $sSort = 'm.total_comment DESC';
                break;
            default:
                $sSort = 'm.time_stamp DESC';
                break;
        }

        $this->_oSearch->setSort($sSort);

        $aBrowseParams = array(
            'module_id' => 'music.album',
            'alias' => 'm',
            'field' => 'album_id',
            'table' => Phpfox::getT('music_album'),
            'hide_view' => array('pending', 'my', 'my-album'),
            'service' => 'mfox.album'
        );      
        
        switch ($sView)
        {
            case 'my':
            case 'my-album':
                Phpfox::isUser(true);
                $this->_oSearch->setCondition('AND m.user_id = ' . Phpfox::getUserId());
                break;
            default:
                $this->_oSearch->setCondition("AND m.view_id = 0 AND m.privacy IN(%PRIVACY%)"); 
                if ($sView == 'featured')
                {
                    $this->_oSearch->setCondition('AND m.is_featured = 1');
                }               
                break;
        }       

        $this->_oBrowse->params($aBrowseParams)->execute();

        $aRows = $this->_oBrowse->getRows();

        return $aRows;
    }

    public function processRows(&$aRows)
    {
        $aAlbums = $aRows;
        $aRows = array();

        foreach ($aAlbums as $aAlbum)
        {
            if($aAlbum['image_path'] == null){
                $sImagePath = $this->_sDefaultImageAlbumPath;
            } else {
                $sImagePath = Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aAlbum['server_id'],
                        'path' => 'music.url_image',
                        'file' => $aAlbum['image_path'],
                        'suffix' => MAX_SIZE_OF_IMAGE_ALBUM,
                        'return_url' => true
                            )
                    );                
            }

            $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aAlbum, '_50_square');

            //  get list of liked user
            $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('music_album'
                , $aAlbum['album_id']
                , false
                , Phpfox::getParam('feed.total_likes_to_display'));
            $aUserLike = array();
            foreach($aLike['likes'] as $like){
                $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
            }

            $aRows[] = array(
                'bIsLiked' => isset($aAlbum['is_liked']) ? (bool) $aAlbum['is_liked'] : false,
                'iAlbumId' => $aAlbum['album_id'],
                'iViewId' => $aAlbum['view_id'],
                'iPrivacy' => $aAlbum['privacy'],
                'iPrivacyComment' => $aAlbum['privacy_comment'],
                'bIsFeatured' => (bool) $aAlbum['is_featured'],
                'bIsSponsor' => (bool) $aAlbum['is_sponsor'],
                'iUserId' => $aAlbum['user_id'],
                'sName' => $aAlbum['name'],
                'iYear' => $aAlbum['year'],
                'sImagePath' => $sImagePath,
                'iTotalTrack' => $aAlbum['total_track'],
                'iTotalPlay' => $aAlbum['total_play'],
                'iTotalComment' => $aAlbum['total_comment'],
                'iTotalLike' => $aAlbum['total_like'],
                'iTotalDislike' => $aAlbum['total_dislike'],
                'iTotalScore' => $aAlbum['total_score'],
                'iTotalRating' => $aAlbum['total_rating'],
                'iTimeStamp' => $aAlbum['time_stamp'],
                'sTimeStamp' => date('l, F j', $aAlbum['time_stamp']),
                'sFullTimeStamp' => Phpfox::getLib('date')->convertTime($aAlbum['time_stamp'], null),
                'sModuleId' => isset($aAlbum['module_id']) ? $aAlbum['module_id'] : 0,
                'iItemId' => $aAlbum['item_id'],
                'iProfilePageId' => $aAlbum['profile_page_id'],
                'iUserServerId' => $aAlbum['user_server_id'],
                'sUsername' => $aAlbum['user_name'],
                'sFullname' => $aAlbum['full_name'],
                'iGender' => $aAlbum['gender'],
                'sUserImage' => $sUserImage,
                'bIsInvisible' => $aAlbum['is_invisible'],
                'iUserGroupId' => $aAlbum['user_group_id'],
                'iUserLevelId' => $aAlbum['user_group_id'],
                'aUserLike' => $aUserLike,
                'fRating' => (float) ($aAlbum['total_score']/2), 
                'iLanguageId' => isset($aAlbum['language_id']) ? $aAlbum['language_id'] : 0
            );
        }
    }

    public function query()
    {
        return Phpfox::getService('music.album.browse')->query();
    }
    
    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Mfox_Service_Friend::instance()->queryJoin($bNoQueryFriend))
        {           
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = m.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }
    }

    /**
     * Using for notification.
     * @param array $aNotification
     * @return array|bool
     */
    public function doAlbumGetNotificationAlbum_Like($aNotification)
    {
        /**
         * @var array
         */
        $aRow = $this->database()->select('ms.album_id, ms.name, ms.user_id, u.gender, u.full_name')
                ->from(Phpfox::getT('music_album'), 'ms')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = ms.user_id')
                ->where('ms.album_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');
        
        if (!isset($aRow['album_id']))
        {
            return array();
        }
        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            $sPhrase =  Phpfox::getPhrase('music.user_name_liked_gender_own_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('music.user_name_liked_your_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('music.user_name_liked_span_class_drop_data_user_full_name_s_span_album_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aRow['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aRow['name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }
        return array(
            'link' => array(
                'iAlbumId' => $aRow['album_id'],
                'sAlbumTitle' => $aRow['name']
            ),
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog'),
            'sModule' => 'music.album',
            'sMethod' => 'getNotificationAlbum_Like'
        );
    }
    /**
     * Using for notification.
     * @param array $aNotification
     * @return array
     */
    public function doMusicAlbumGetCommentNotificationAlbum($aNotification)
	{
        /**
         * @var array
         */
		$aRow = $this->database()
                ->select('l.album_id, l.name, u.user_id, u.gender, u.user_name, u.full_name')	
                ->from(Phpfox::getT('music_album'), 'l')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
                ->where('l.album_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');
        
        if (!isset($aRow['album_id']))
        {
            return array();
        }
        
        /**
         * @var string
         */
		$sPhrase = '';
		if ($aNotification['user_id'] == $aRow['user_id'] && !isset($aNotification['extra_users']))
		{ 
			$sPhrase =  Phpfox::getPhrase('music.user_name_commented_on_gender_album_title',array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength ), '...')));
		}
		elseif ($aRow['user_id'] == Phpfox::getUserId())
		{
			$sPhrase =  Phpfox::getPhrase('music.user_name_commented_on_your_album_title',array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength ), '...')));
		}
		else 
		{
			$sPhrase =  Phpfox::getPhrase('music.user_name_commented_on_span_class_drop_data_user_full_name_s_album_title',array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aRow['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aRow['name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength ), '...')));
		}
		return array(
			'link' => array(
                'iAlbumId' => $aRow['album_id'],
                'sName' => $aRow['name']
            ),
			'message' => ($sPhrase),
			'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog'),
            'sModule' => 'music.album',
            'sMethod' => 'getCommentNotificationAlbum'
		);
	}
    
    public function doMusicAlbumGetRedirectCommentAlbum($iId)
	{
		return $this->getFeedRedirectAlbum($iId);
	}
    /**
     * Get feed redirect album.
     * @param int $iId
     * @return boolean|array
     */
    public function getFeedRedirectAlbum($iId)
	{
        /**
         * @var array
         */
		$aRow = $this->database()->select('m.album_id, name')
			->from(Phpfox::getT('music_album'), 'm')
			->where('m.album_id = ' . (int) $iId)
			->execute('getSlaveRow');
		if (!isset($aRow['album_id']))
		{
			return array();
		}
		return array(
            'sModule' => 'music.album',
            'iAlbumId' => $aRow['album_id'],
            'sTitle' => $aRow['name'],
            'sCommentType' => 'music_album'
        );
	}

    /**
     * Input data:
     * + sAction: string, optional.
     * + iLastAlbumId: int, optional.
     * + sView: string, optional. Ex: my, all
     * + iAmountOfAlbum: int, optional.
     * + sSearch: string, optional.
     *
     * Output data:
     * + bIsLiked: bool.
     * + iAlbumId: int.
     * + iUserId: int.
     * + sName: string.
     * + sImagePath: string.
     * + iTotalTrack: int.
     * + iTotalPlay: int.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + aUserLike array
     * + iTimeStamp: int.
     * + sTimeStamp: string.
     * + sFullTimeStamp: string.
     * + sFullname: string.
     * + sUserImage: string.
     * + bIsInvisible: bool.
     * + iUserLevelId: int.
     *
     */
    public function filter($aData)
    {
        return $this->getAlbums($aData);
    }

    /**
     * Push Cloud Message for music album.
     * @param int $iAlbumId
     */
    public function doPushCloudMessageMusicAlbum($aData)
    {
        /**
         * @var int
         */
        $iAlbumId = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;
        /**
         * @var array
         */
        $aAlbum = Phpfox::getService('music.album')->getAlbum($iAlbumId);
        
        if (isset($aAlbum['user_id']) && $aAlbum['user_id'] != Phpfox::getUserId())
        {
            /**
             * @var int
             */
            $iPushId = Phpfox::getService('mfox.push')->savePush($aData, $aAlbum['user_id']);
            
            Phpfox::getService('mfox.cloudmessage') -> send(array('message' => 'notification', 'iPushId' => $iPushId), $aAlbum['user_id']);
        }
    }
    
    /**
     * Input data:
     * + iAlbumId: string, required.
     *
     * Output data:
     * + bIsLiked: bool.
     * + iAlbumId: int.
     * + iUserId: int.
     * + sAlbumName: string.
     * + sDescription: string.
     * + sImagePath: string.
     * + iTotalTrack: int.
     * + iTotalPlay: int.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + aUserLike array
     * + iTimeStamp: int.
     * + sTimeStamp: string.
     * + sFullTimeStamp: string.
     * + sFullname: string.
     * + sUserImage: string.
     * + bIsInvisible: bool.
     * + iUserLevelId: int.
     * + bIsFriend: bool.
     * + bCanPostComment: bool.
     *
     */
    public function detail($aData)
    {
    	$sModelType = $aData['sModelType'];
    	
		if ($sModelType == 'musicsharing_album'){
			return Phpfox::getService('mfox.musicsharing.album')->detail($aData);	
		}
		
        $iAlbumId = isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : 0;        
        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'iAlbumId'=>$iAlbumId ,'error_code' => 1, 'error_message' => 'You do not have permission to access music module!');
        }
		
		if (Phpfox::isUser() && Phpfox::isModule('notification'))
		{
			Phpfox::getService('notification.process')->delete('comment_music_album', $iAlbumId, Phpfox::getUserId());
			Phpfox::getService('notification.process')->delete('music_song_album', $iAlbumId, Phpfox::getUserId());
			Phpfox::getService('notification.process')->delete('music_album_like', $iAlbumId, Phpfox::getUserId());
		}			

		$aAlbum = Phpfox::getService('music.album')->getAlbum($iAlbumId);	
		if (!isset($aAlbum['album_id']))
		{
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('music.unable_to_find_the_album_you_are_looking_for'));
		}
        
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_album', $aAlbum['album_id'], $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        
        //  get list of liked user
        $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser('music_album'
            , $aAlbum['album_id']
            , false
            , Phpfox::getParam('feed.total_likes_to_display'));
        $aUserLike = array();
        foreach($aLike['likes'] as $like){
            $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
        }

        $aUserDislike = array();
        $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('music_album', $aAlbum['album_id'], $bGetCount = false);
        foreach($aDislike as $dislike){
            $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
        }                

        // check bCanView
        $bCanView = false;
        if(Phpfox::getService('privacy')->check($sModule = '', $iItemId = ''
            , $aAlbum['user_id'], $aAlbum['privacy'], $aAlbum['is_friend'], $bReturn = true)){
            $bCanView = true;
        }

        if($aAlbum['image_path'] == null){
            $sImagePath = $this->_sDefaultImageAlbumPath;
        } else {
            $sImagePath = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aAlbum['server_id'],
                    'path' => 'music.url_image',
                    'file' => $aAlbum['image_path'],
                    'suffix' => '',
                    'return_url' => true
                        )
                );                
        }                

        return array(
            'bIsLiked' => $aAlbum['is_liked'],
            'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('music_album', $aAlbum['album_id'], Phpfox::getUserId()),
            'bIsFriend' => $aAlbum['is_friend'],
            'iAlbumId' => $aAlbum['album_id'],
            'iViewId' => $aAlbum['view_id'],
            'iPrivacy' => $aAlbum['privacy'],
            'iPrivacyComment' => $aAlbum['privacy_comment'],
            'bIsFeatured' => (bool) $aAlbum['is_featured'],
            'bIsSponsor' => (bool) $aAlbum['is_sponsor'],
            'iUserId' => $aAlbum['user_id'],
            'sAlbumName' => $aAlbum['name'],
            'iYear' => $aAlbum['year'],
            'sImagePath' => $sImagePath,
            'iTotalTrack' => $aAlbum['total_track'],
            'iTotalPlay' => $aAlbum['total_play'],
            'iTotalComment' => $aAlbum['total_comment'],
            'iTotalLike' => $aAlbum['total_like'],
            'iTotalDislike' => $aAlbum['total_dislike'],
            'fTotalScore' => $aAlbum['total_score'],
            'iTotalRating' => $aAlbum['total_rating'],
            'iRatingCount' => $aAlbum['total_rating'],
            'iTimeStamp' => $aAlbum['time_stamp'],
            'sTimeStamp' => date("D, j M Y G:i:s O", (int)$aAlbum['time_stamp']),
            'sFullTimeStamp' => Phpfox::getLib('date')->convertTime($aAlbum['time_stamp'], null),
            'sModuleId' => $aAlbum['module_id'],
            'iItemId' => $aAlbum['item_id'],
            'sDescription' => $aAlbum['text'],
            'sUserName' => $aAlbum['user_name'],
            'bHasRated' => $aAlbum['has_rated'],
            'bIsRating' => (isset($aAlbum['has_rated']) && (int)$aAlbum['has_rated'] > 0) ? true : false,
            'iProfilePageId' => $aAlbum['profile_page_id'],
            'sUserImage' => Phpfox::getService('mfox.user')->getImageUrl($aAlbum, '_50_square'),
            'sFullname' => $aAlbum['full_name'],
            'iGender' => $aAlbum['gender'],
            'bIsInvisible' => $aAlbum['is_invisible'],
            'iUserGroupId' => $aAlbum['user_group_id'],
            'iUserLevelId' => $aAlbum['user_group_id'],        
            'aUserLike' => $aUserLike,        
            'aUserDislike' => $aUserDislike,        
            'iLanguageId' => $aAlbum['language_id'],
            'bCanView' => $bCanView,
            'bCanShare' => false,
            'bCanPostComment' => Phpfox::getService('mfox.comment')->checkCanPostCommentOnItem($aAlbum),
            'fRating' => (float) ($aAlbum['total_score']/2), 
            'sTitle' => '',
            'aSongs'=> $this->field_songs($aAlbum),
            'sLink_Url' => Phpfox::permalink('music.album', $aAlbum['album_id'], $aAlbum['name']),
        );
    }

    // -----------------------------------------------------------
    // VERSION 3.03
    // -----------------------------------------------------------
    public function getAlbumByID($albumID){
        return $this->database()->select('ma.*, mat.text, mat.text_parsed')
            ->from(Phpfox::getT('music_album'), 'ma')
            ->join(Phpfox::getT('music_album_text'), 'mat', 'mat.album_id = ma.album_id')
            ->where('ma.album_id = ' . (int) $albumID)
            ->execute('getSlaveRow');            
    }

    /**
     * Add song into album
     * Note that: song and album existed in server
     * 
     * Input data:
     * + iAlbumId: int, optional.
     * + iSongId: int, optional.
     *
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * + error_message: string.
     *
     */
    public function addsong($aData)
    {
        //  init 
        if (!Phpfox::getUserParam('music.can_upload_music_public'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_add_song")));
        }

        $iAlbumId = isset($aData['iAlbumId']) ? $aData['iAlbumId'] : 0;
        $aAlbum = $this->getAlbumByID($aData['iAlbumId']);
        $iSongId = isset($aData['iSongId']) ? $aData['iSongId'] : 0;
        $aSong = Phpfox::getService('mfox.song')->getSongByID($aData['iSongId']);
        if(!isset($aAlbum['album_id']) || !isset($aSong['song_id'])){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.album_or_song_does_not_exists")));
        }

        if($this->isSongInAlbum($iAlbumId, $iSongId) == true){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.this_album_already_has_this_song")));
        }

        // process
        $this->database()->update(Phpfox::getT('music_song')
            , array(
                'album_id' => $iAlbumId
                , 'privacy' => $aAlbum['privacy']
                , 'privacy_comment' => $aAlbum['privacy_comment']
            )
            , 'song_id = ' . (int)$iSongId
        );
        if (!Phpfox::getUserParam('music.music_song_approval'))
        {                   
            $this->database()->updateCounter('music_album', 'total_track', 'album_id', $iAlbumId);
        }           

        // end 
        return array(
            'result' => 1,
            'message'=>html_entity_decode(Phpfox::getPhrase("mfox.song_successfully_added"))
        );
    }    

    public function getSongByAlbumID($albumID){
        return $this->database()->select('*')
            ->from(Phpfox::getT('music_song'))
            ->where('album_id = ' . (int) $albumID)
            ->execute('getSlaveRow');            
    }

    public function isSongInAlbum($albumID, $songID){
        $ret = $this->database()->select('song_id')
            ->from(Phpfox::getT('music_song'))
            ->where('album_id = ' . (int) $albumID . ' AND song_id = ' . (int) $songID)
            ->execute('getSlaveRow');            

        if(isset($ret['song_id']) && (int)$ret['song_id'] > 0){
            return true;
        } 

        return false;
    }

    /**
     * Input data:
     * + iSongId: int, required.
     *
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     *
     */
    public function deletesong($aData){
        return Phpfox::getService('mfox.song')->delete($aData);
    }

    /**
     * Input data:
     * + sType : string, required.
     *
     * Output data:
     * - sPrivacyValue
     * - sPrivacyName
     *
     */
    public function privacy($aData){
        $sType = isset($aData['sType']) ? $aData['sType'] : 'view';
        switch ($sType)
        {
            case 'view' :
                return Phpfox::getService('mfox.privacy')->privacy(array());
                break;
            case 'comment' :
                return Phpfox::getService('mfox.privacy')->privacycomment(array());
                break;
        }

        return array('result' => 0
            , 'error_code' => 1
            , 'error_message' => 'Not support type ' . $sType . ' yet!');
    }

    public function upload($aData)
    {
        if(isset($aData['$iPlaylistId'])){
            $aData['$iAlbumId'] = $aData['$iPlaylistId'];
        }

        if (!isset($_FILES['song']))
        {
            return array(
                'error_code' => 2,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_file"))
            );
        } else {
            $_FILES['mp3'] = $_FILES['song'];
        }

        return Phpfox::getService('mfox.song')->create($aData);
    }

    public function fetch($aData){
        if(isset($aData['iMaxId'])){
            $aData['iLastAlbumId'] = $aData['iMaxId'];
        }
        
		if ($this->isAdvancedModule()){
			return Phpfox::getService('mfox.musicsharing.album')->getAlbums($aData);	
		}
		
        return $this->getAlbums($aData);
    }
    
}

