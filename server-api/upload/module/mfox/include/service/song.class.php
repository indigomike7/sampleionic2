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
 * @link Mfox Api v3.0
 */
class Mfox_Service_Song extends Phpfox_Service {

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

    public function getDefaultImageSongPath(){
        return $this->_sDefaultImageAlbumPath;
    }

    /**
     * Input data:
     * + iAlbumId: int, optional.
     * + sNewAlbumTitle: string, optional.
     * + sDescription: string, optional.
     * + sTitle: string, optional.
     * + sPrivacyView: string, optional.
     * + sPrivacyComment: string, optional.
     * + iSearch: int, optional
     * + sType: string, optional. ex: 'profile', 'wall', 'message'
     * + mp3: mp3 file, required.
     *
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * + iSongId: int.
     * + sSongTitle: string.
     * + iAlbumId: int.
     *
     */
    public function create($aData)
    {
        if (!Phpfox::getUserParam('music.can_upload_music_public'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_upload_music_public")));
        }

        $aData['sPrivacyView'] = isset($aData['sPrivacyView']) ? $aData['sPrivacyView'] : 0;
        $aData['iPrivacy'] = $aData['sPrivacyView'];
        $aData['sPrivacyComment'] = isset($aData['sPrivacyComment']) ? $aData['sPrivacyComment'] : 0;
        $aData['iPrivacyComment'] = $aData['sPrivacyComment'];

        $aVals = array(
            'callback_module' => isset($aData['sCallbackModule']) ? $aData['sCallbackModule'] : '',
            'callback_item_id' => isset($aData['iCallbackItemId']) ? (int) $aData['iCallbackItemId'] : 0,
            'album_id' => isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : 0,
            'new_album_title' => isset($aData['sNewAlbumTitle']) ? trim($aData['sNewAlbumTitle']) : '',
            'title' => isset($aData['sTitle']) ? $aData['sTitle'] : '',
            'privacy' => isset($aData['iPrivacy']) ? (int) $aData['iPrivacy'] : 0,
            'privacy_comment' => isset($aData['iPrivacyComment']) ? (int) $aData['iPrivacyComment'] : 0,
            'privacy_list' => isset($aData['sPrivacyList']) ? explode(',', $aData['sPrivacyList']) : array(),
            // Avaliable on wall only.
            'music_title' => isset($aData['sMusicTitle']) && !empty($aData['sMusicTitle']) ? $aData['sMusicTitle'] : null,
            'status_info' => isset($aData['sStatusInfo']) ? $aData['sStatusInfo'] : '',
            'genre_id' => isset($aData['iGenreId']) ? (int) $aData['iGenreId'] : 0,
            'explicit' => isset($aData['iExplicit']) ? (int) $aData['iExplicit'] : null,
            'is_profile' => (isset($aData['bIsProfile']) && $aData['bIsProfile'] == 'true') ? 'yes' : 'no'
        );

        $aValidation = array(
            'title' =>  Phpfox::getPhrase('music.provide_a_name_for_this_song')
        );

        $oValidator = Phpfox::getLib('validator')->set(array(
            'sFormName' => 'js_music_form',
            'aParams' => $aValidation
                )
        );

        if (isset($aVals['music_title']))
        {
            $aVals['title'] = $aVals['music_title'];
        }

        if ($oValidator->isValid($aVals))
        {
            if (($aSong = $this->upload($aVals, (isset($aVals['album_id']) ? (int) $aVals['album_id'] : 0))))
            {
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'message' =>  Phpfox::getPhrase('music.song_successfully_uploaded'),
                    'iAlbumId' => $aSong['album_id'],
                    'sSongUrl' => Phpfox::getService('music')->getSongPath($aSong['song_path'], $aSong['server_id']),
                    'iSongId' => $aSong['song_id'],
                    'sSongTitle' => $aSong['title']
                );
            }
            else
            {
                return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
            }
        }
        else
        {
            Phpfox_Error::set('Title or music title variable are empty!');
            return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
        }
    }

    /**
     * Input data:
     * + mp3: mp3 file, required. In POST method.
     * + aVals: array of data:
     *   - title: string, required.
     *   - privacy: int, required.
     *   - privacy_comment: int, required.
     *   - privacy_list: array, optional.
     *   - new_album_title: string, optional.
     *   - genre_id: int, optional.
     *   - callback_module: string, optional.
     *   - callback_item_id: int, optional.
     *   - status_info: string, optional.
     *   - explicit: bool, optional.
     * 
     * @see Phpfox_File
     * @param array $aVals
     * @param int $iAlbumId
     * @return boolean
     */
    private function upload($aVals, $iAlbumId = 0)
    {
        if (!isset($_FILES['mp3']))
        {
            return Phpfox_Error::set( Phpfox::getPhrase('music.select_an_mp3'));
        }

        $aSong = Phpfox::getLib('file')->load('mp3', 'mp3', Phpfox::getUserParam('music.music_max_file_size'));
        if ($aSong === false)
        {
            return false;
        }
        if (empty($aVals['title']))
        {
            $aVals['title'] = $aSong['name'];
        }
        if (!isset($aVals['privacy']))
        {
            $aVals['privacy'] = 0;
        }
        if (!isset($aVals['privacy_comment']))
        {
            $aVals['privacy_comment'] = 0;
        }
        if ($iAlbumId > 0)
        {
            $aAlbum = $this->database()->select('*')
                    ->from(Phpfox::getT('music_album'))
                    ->where('album_id = ' . (int) $iAlbumId)
                    ->execute('getSlaveRow');

            $aVals['privacy'] = $aAlbum['privacy'];
            $aVals['privacy_comment'] = $aAlbum['privacy_comment'];
        }

        if (!empty($aVals['new_album_title']))
        {
            $iAlbumId = $this->database()->insert(Phpfox::getT('music_album'), array(
                'user_id' => Phpfox::getUserId(),
                'name' => $this->preParse()->clean($aVals['new_album_title']),
                'privacy' => (isset($aVals['privacy']) ? $aVals['privacy'] : '0'),
                'privacy_comment' => (isset($aVals['privacy_comment']) ? $aVals['privacy_comment'] : '0'),
                'time_stamp' => PHPFOX_TIME,
                'module_id' => (isset($aVals['callback_module']) ? $aVals['callback_module'] : null),
                'item_id' => (isset($aVals['callback_item_id']) ? (int) $aVals['callback_item_id'] : '0')
                    )
            );

            $aAlbum = $this->database()->select('*')
                    ->from(Phpfox::getT('music_album'))
                    ->where('album_id = ' . (int) $iAlbumId)
                    ->execute('getSlaveRow');

            $this->database()->insert(Phpfox::getT('music_album_text'), array(
                'album_id' => $iAlbumId
                    )
            );

            if ($aVals['privacy'] == '4')
            {
                // Phpfox::getService('privacy.process')->add('music_album', $iAlbumId, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : array()));			
            }
        }

        if (!Phpfox::getService('ban')->checkAutomaticBan($aVals['title']))
        {
            return false;
        }

        $aInsert = array(
            'view_id' => (Phpfox::getUserParam('music.music_song_approval') ? '1' : '0'),
            'privacy' => (isset($aVals['privacy']) ? $aVals['privacy'] : '0'),
            'privacy_comment' => (isset($aVals['privacy_comment']) ? $aVals['privacy_comment'] : '0'),
            'album_id' => $iAlbumId,
            'genre_id' => (isset($aVals['genre_id']) ? (int) $aVals['genre_id'] : '0'),
            'user_id' => Phpfox::getUserId(),
            'title' => Phpfox::getLib('parse.input')->clean($aVals['title'], 255),
            'description' => (isset($aVals['status_info']) ? Phpfox::getLib('parse.input')->clean($aVals['status_info'], 255) : null),
            'explicit' => ((isset($aVals['explicit']) && $aVals['explicit']) ? 1 : 0),
            'time_stamp' => PHPFOX_TIME,
            'module_id' => (isset($aVals['callback_module']) ? $aVals['callback_module'] : null),
            'item_id' => (isset($aVals['callback_item_id']) ? (int) $aVals['callback_item_id'] : '0')
        );

        $iId = $this->database()->insert(Phpfox::getT('music_song'), $aInsert);
        if (!$iId)
        {
            return false;
        }

        $sFileName = Phpfox::getLib('file')->upload('mp3', Phpfox::getParam('music.dir'), $iId);
        $sDuration = null;
        if (file_exists(PHPFOX_DIR_LIB . 'getid3' . PHPFOX_DS . 'getid3' . PHPFOX_DS . 'getid3.php'))
        {
            // Temp. disable error reporting
            Phpfox_Error::skip(true);

            require_once(PHPFOX_DIR_LIB . 'getid3' . PHPFOX_DS . 'getid3' . PHPFOX_DS . 'getid3.php');
            $oGetId3 = new getID3;
            $aMetaData = $oGetId3->analyze(Phpfox::getParam('music.dir') . sprintf($sFileName, ''));

            if (isset($aMetaData['playtime_string']))
            {
                $sDuration = $aMetaData['playtime_string'];
            }
        }

        $aInsert['song_id'] = $iId;
        $aInsert['duration'] = $sDuration;
        $aInsert['song_path'] = $sFileName;
        $aInsert['full_name'] = $sFileName;
        $aInsert['is_featured'] = 0;
        $aInsert['user_name'] = Phpfox::getUserBy('user_name');
        $aInsert['server_id'] = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
        // Return back error reporting
        Phpfox_Error::skip(false);

        $this->database()->update(Phpfox::getT('music_song'), array('song_path' => $sFileName, 'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID'), 'duration' => $sDuration), 'song_id = ' . (int) $iId);

        // Update user space usage
        if (!Phpfox::getUserParam('music.music_song_approval'))
        {
            Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'music', filesize(Phpfox::getParam('music.dir') . sprintf($sFileName, '')));
        }

        if ($aVals['privacy'] == '4')
        {
            Phpfox::getService('privacy.process')->add('music_song', $iId, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : array()));
        }

        $aCallback = null;
        if (!empty($aVals['callback_module']) && Phpfox::hasCallback($aVals['callback_module'], 'uploadSong'))
        {
            $aCallback = Phpfox::callback($aVals['callback_module'] . '.uploadSong', $aVals['callback_item_id']);
        }
        if ($iAlbumId > 0)
        {
            if (!Phpfox::getUserParam('music.music_song_approval'))
            {
                $this->database()->updateCounter('music_album', 'total_track', 'album_id', $iAlbumId);
                (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->callback($aCallback)->add('music_album', $iId, $aAlbum['privacy'], (isset($aAlbum['privacy_comment']) ? (int) $aAlbum['privacy_comment'] : 0), (isset($aVals['callback_item_id']) ? (int) $aVals['callback_item_id'] : '0')) : null);
            }
        }
        else
        {
            if (!Phpfox::getUserParam('music.music_song_approval'))
            {
                (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->callback($aCallback)->add('music_song', $iId, $aVals['privacy'], (isset($aVals['privacy_comment']) ? (int) $aVals['privacy_comment'] : 0), (isset($aVals['callback_item_id']) ? (int) $aVals['callback_item_id'] : '0')) : null);
            }
        }
        if (!Phpfox::getUserParam('music.music_song_approval'))
        {
            Phpfox::getService('user.activity')->update(Phpfox::getUserId(), 'music_song');
        }
        return $aInsert;
    }

    /**
     * Input data: N/A.
     * 
     * Output data:
     * + iGenreId: int.
     * + sName: string.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see song/genres
     * 
     * @param array $aData
     * @return array
     */
    public function genres($aData = array())
    {
        /**
         * @var array
         */
        $aGenres = Phpfox::getService('music.genre')->getList();
        /**
         * @var array
         */
        $aResult = array();
        foreach ($aGenres as $aGenre)
        {
            $aResult[] = array('iGenreId' => $aGenre['genre_id'], 'sName' => $aGenre['name']);
        }

        return $aResult;
    }

    /**
     * Input data:
     * + iSongId: int, required.
     * + sTitle: string, required.
     *
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * + iSongId: int.
     * + sSongTitle: string.
     * + iAlbumId: int.
     *
     */
    public function edit($aData)
    {

        if (!isset($aData['sTitle']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_title__is_required_and_can_not_be_empty"))
            );
        }
        if (!Phpfox::getUserParam('music.can_upload_music_public'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_upload_music_public")));
        }

        $iSongId = isset($aData['iSongId']) ? (int) $aData['iSongId'] : 0;
        if ($iSongId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.song_id_is_not_valid")));
        }

        $aEditSong = Phpfox::getService('music')->getForEdit($iSongId);
        if (!isset($aEditSong['song_id']))
        {
            Phpfox_Error::set('Song is not valid or has been deleted!');

            return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
        }
        // Check privacy for song.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_song', $aEditSong['song_id'], $aEditSong['user_id'], $aEditSong['privacy'], $aEditSong['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        if ($aEditSong['module_id'] == 'pages')
        {
            Phpfox::getService('pages')->setIsInPage();
        }

        $aVals = array(
            'callback_module' => isset($aData['sCallbackModule']) ? $aData['sCallbackModule'] : '',
            'callback_item_id' => isset($aData['iCallbackItemId']) ? (int) $aData['iCallbackItemId'] : 0,
            'album_id' => isset($aData['iAlbumId']) ? (int) $aData['iAlbumId'] : $aEditSong['album_id'],
            'title' => isset($aData['sTitle']) ? $aData['sTitle'] : '',
            'privacy' => isset($aData['iPrivacy']) ? (int) $aData['iPrivacy'] : $aEditSong['privacy'],
            'privacy_comment' => isset($aData['iPrivacyComment']) ? (int) $aData['iPrivacyComment'] : $aEditSong['privacy_comment'],
            'privacy_list' => isset($aData['sPrivacyList']) ? explode('|', $aData['sPrivacyList']) : array(),
            'genre_id' => isset($aData['iGenreId']) ? (int) $aData['iGenreId'] : $aEditSong['genre_id'],
            'explicit' => isset($aData['iExplicit']) ? (int) $aData['iExplicit'] : $aEditSong['explicit']
        );

        $aValidation = array(
            'title' =>  Phpfox::getPhrase('music.provide_a_name_for_this_song')
        );
        $oValidator = Phpfox::getLib('validator')->set(array(
            'sFormName' => 'js_music_form',
            'aParams' => $aValidation
                )
        );
        if ($oValidator->isValid($aVals))
        {
            if (Phpfox::getService('music.process')->update($aEditSong['song_id'], $aVals))
            {
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'message'=>html_entity_decode(Phpfox::getPhrase("mfox.song_successfully_updated")),
                    'iAlbumId' => $aVals['album_id'],
                    'iSongId' => $aEditSong['song_id'],
                    'sSongTitle' => $aVals['title']
                );
            }
        }

        return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
    }

    /**
     * Input data:
     * + sCallbackModule: string, optional.
     * + iCallbackItemId: int, optional.
     * 
     * Output data:
     * + iAlbumId: int.
     * + sName: string.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see song/albums
     * 
     * @param array $aData
     * @return array
     */
    public function albums($aData)
    {
        /**
         * @var string
         */
        $sModule = isset($aData['sCallbackModule']) ? $aData['sCallbackModule'] : false;
        /**
         * @var int
         */
        $iItem = isset($aData['iCallbackItemId']) ? (int) $aData['iCallbackItemId'] : false;
        /**
         * @var bool|array
         */
        $aCallback = false;
        if ($sModule !== false && $iItem !== false && Phpfox::hasCallback($sModule, 'getMusicDetails'))
        {
            if (($aCallback = Phpfox::callback($sModule . '.getMusicDetails', array('item_id' => $iItem))))
            {
                if ($sModule == 'pages' && !Phpfox::getService('pages')->hasPerm($iItem, 'music.share_music'))
                {
                    Phpfox_Error::set('Unable to view this item due to privacy settings.');

                    return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
                }
            }
        }
        /**
         * @var array
         */
        $aAlbums = Phpfox::getService('music.album')->getForUpload($aCallback);
        $aResult = array();
        foreach ($aAlbums as $aAlbum)
        {
            $aResult[] = array('iAlbumId' => $aAlbum['album_id'], 'sName' => $aAlbum['name']);
        }
        return $aResult;
    }

    /**
     * Input data:
     * + iSongId: int, required.
     *
     * Output data:
     * + iSongId: int.
     * + sSongTitle: string.
     * + iAlbumId: int.
     *
     */
    public function getSongForEdit($aData)
    {
        if (!Phpfox::getUserParam('music.can_upload_music_public'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_upload_music_public")));
        }

        $iSongId = isset($aData['iSongId']) ? (int) $aData['iSongId'] : 0;
        if ($iSongId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.song_id_is_not_valid")));
        }

        $aSong = Phpfox::getService('music')->getForEdit($iSongId);
        if (!isset($aSong['song_id']))
        {
            Phpfox_Error::set('Song is not valid or has been deleted!');

            return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
        }

        // Check privacy for song.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_song', $aSong['song_id'], $aSong['user_id'], $aSong['privacy'], $aSong['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        if ($aSong['module_id'] == 'pages')
        {
            Phpfox::getService('pages')->setIsInPage();
        }

        return array(
            'result' => 1,
            'iSongId' => $aSong['song_id'],
            'iGenreId' => $aSong['genre_id'],
            'sSongTitle' => $aSong['title'],
            'iAlbumId' => $aSong['album_id'],
            'iPrivacy' => $aSong['privacy'],
            'iPrivacyComment' => $aSong['privacy_comment'],
            'aAlbumList' => $this->albums(array('sCallbackModule' => $aSong['module_id'], 'iCallbackItemId' => $aSong['item_id'])),
            'aGenreList' => $this->genres(),
            'aPrivacyList' => Phpfox::getService('mfox.privacy')->privacy(array()),
            'aPrivacyCommentList' => Phpfox::getService('mfox.privacy')->privacycomment(array())
        );
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
    public function delete($aData)
    {
        $iSongId = isset($aData['iSongId']) ? (int) $aData['iSongId'] : 0;
        if ($iSongId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.song_id_is_not_valid")));
        }
        $aSong = Phpfox::getService('music')->getForEdit($iSongId);
        if (!isset($aSong['song_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.song_has_been_deleted_or_you_do_not_have_permission_to_delete_it")));
        }

        // Check privacy for song.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_song', $aSong['song_id'], $aSong['user_id'], $aSong['privacy'], $aSong['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        $mResult = Phpfox::getService('music.process')->delete($iSongId);
        if ($mResult !== false)
        {
            return array('result' => 1
                , 'error_code' => 0
                , 'message' =>  Phpfox::getPhrase('music.song_successfully_deleted')
            );
        }

        return array('result' => 0
            , 'error_code' => 1
            , 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.song_has_been_deleted_or_you_do_not_have_permission_to_delete_it"))
        );
    }

    /**
     * Input data:
     * + sAction: string, optional.
     * + iLastSongId: int, optional.
     * + iAmountOfSong: int, optional.
     * + sSearch: string, optional.
     * + sView: string, optional.
     * + bIsProfile: bool, optional.
     * + iProfileId: int, optional.
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
     * + bIsInvisible: bool.
     * + iUserLevelId: int.
     *
     */
    public function searchSong($aData)
    {
        return $this->getSongs($aData);
    }

    /**
     * Input data:
     * + sAction: string, optional.
     * + iLastSongId: int, optional.
     * + iAmountOfSong: int, optional.
     * + sSearch: string, optional.
     * + sView: string, optional.
     * + bIsProfile: bool, optional.
     * + iProfileId: int, optional.
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
     * + bIsInvisible: bool.
     * + iUserLevelId: int.
     */
    public function filter($aData)
    {
        return $this->getSongs($aData);
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
     * @see Mobile - API phpFox/Api V3.0
     * @see song/like
     * 
     * @param array $aData
     * @return array
     */
    public function like($aData)
    {
        /**
         * @var int
         */
        $iSongId = isset($aData['iSongId']) ? (int) $aData['iSongId'] : 0;

        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music")));
        }

        if (!($aSong = Phpfox::getService('music')->getSong($iSongId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('music.the_song_you_are_looking_for_cannot_be_found'));
        }

        // Check privacy for song.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_song', $aSong['song_id'], $aSong['user_id'], $aSong['privacy'], $aSong['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        return Phpfox::getService('mfox.like')->add(array('sType' => 'music_song', 'iItemId' => $iSongId));
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
     * @see Mobile - API phpFox/Api V3.0
     * @see song/unlike
     * 
     * @param array $aData
     * @return array
     */
    public function unlike($aData)
    {
        /**
         * @var int
         */
        $iSongId = isset($aData['iSongId']) ? (int) $aData['iSongId'] : 0;

        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music")));
        }

        if (!($aSong = Phpfox::getService('music')->getSong($iSongId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('music.the_song_you_are_looking_for_cannot_be_found'));
        }

        // Check privacy for song.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_song', $aSong['song_id'], $aSong['user_id'], $aSong['privacy'], $aSong['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        if (isset($aSong['is_liked']) && $aSong['is_liked'])
        {
            return Phpfox::getService('mfox.like')->delete(array('sType' => 'music_song', 'iItemId' => $iSongId));
        }
        else
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_have_already_unliked_this_item")));
        }
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
     * @param int $iSongId
     * @return array
     */
    public function checkPrivacyCommentOnSong($iSongId)
    {
        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music")));
        }

        if (!($aSong = Phpfox::getService('music')->getSong($iSongId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('music.the_song_you_are_looking_for_cannot_be_found'));
        }

        // Check privacy for song.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_song', $aSong['song_id'], $aSong['user_id'], $aSong['privacy'], $aSong['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        if (!Phpfox::getService('mfox.comment')->checkCanPostCommentOnItem($aSong))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_this_item")));
        }

        return null;
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
     * @param int $iSongId
     * @return array
     */
    public function checkPrivacyOnSong($iSongId)
    {
        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music")));
        }

        if (!($aSong = Phpfox::getService('music')->getSong($iSongId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('music.the_song_you_are_looking_for_cannot_be_found'));
        }

        // Check privacy for song.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_song', $aSong['song_id'], $aSong['user_id'], $aSong['privacy'], $aSong['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        return null;
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
     * @see Mobile - API phpFox/Api V3.0
     * @see song/listComment
     * 
     * @param array $aData
     * @return array
     */
    public function listComment($aData)
    {
        /**
         * @var int
         */
        $iSongId = isset($aData['iSongId']) ? (int) $aData['iSongId'] : 0;

        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music")));
        }

        if (!($aSong = Phpfox::getService('music')->getSong($iSongId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('music.the_song_you_are_looking_for_cannot_be_found'));
        }

        // Check privacy for song.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_song', $aSong['song_id'], $aSong['user_id'], $aSong['privacy'], $aSong['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        return Phpfox::getService('mfox.comment')->listallcomments(array('sType' => 'music_song', 'iItemId' => $iSongId));
    }

    /**
     * Using in notification.
     * @param array $aNotification
     * @return boolean|array
     */
    public function doSongGetNotificationSong_Like($aNotification)
    {
        /**
         * @var array
         */
        $aRow = $this->database()->select('ms.song_id, ms.title, ms.user_id, u.gender, u.full_name')
                ->from(Phpfox::getT('music_song'), 'ms')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = ms.user_id')
                ->where('ms.song_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');

        if (!isset($aRow['song_id']))
        {
            return array();
        }
        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            $sPhrase =  Phpfox::getPhrase('music.user_name_liked_gender_own_song_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('music.users_liked_your_song_title', array('users' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('music.user_name_liked_span_class_drop_data_user_full_name_s_span_song_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aRow['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }

        return array(
            'link' => array(
                'iSongId' => $aRow['song_id'],
                'sSongTitle' => $aRow['title']
            ),
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog'),
            'sModule' => 'music',
            'sMethod' => 'getNotificationSong_Like'
        );
    }

    /**
     * Using in notification.
     * @param array $aNotification
     * @return array
     */
    public function doSongGetCommentNotificationSong($aNotification)
    {
        /**
         * @var array
         */
        $aRow = $this->database()->select('l.song_id, l.title, u.user_id, u.gender, u.user_name, u.full_name')
                ->from(Phpfox::getT('music_song'), 'l')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
                ->where('l.song_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');
        
        if (!isset($aRow['song_id']))
        {
            return array();
        }
        
        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'] && !isset($aNotification['extra_users']))
        {
            $sPhrase =  Phpfox::getPhrase('music.users_commented_on_gender_song_title', array('users' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('music.users_commented_on_your_song_title', array('users' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('music.user_name_commented_on_span_class_drop_data_user_full_name_s_span_song_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aRow['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : $this->_iFallbackLength), '...')));
        }

        return array(
            'link' => array(
                'iSongId' => $aRow['song_id'],
                'sSongTitle' => $aRow['title']
            ),
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog'),
            'sModule' => 'music',
            'sMethod' => 'getCommentNotificationSong'
        );
    }

    /**
     * Using to get notification when comment on song.
     * @param int $iId
     * @return array
     */
    public function doSongGetRedirectCommentSong($iId)
    {
        return $this->getFeedRedirectSong($iId);
    }

    /**
     * Using to get feed redirect song.
     * @param int $iId
     * @return boolean|array
     */
    public function getFeedRedirectSong($iId)
    {
        /**
         * @var array
         */
        $aRow = $this->database()->select('m.song_id, m.title')
                ->from(Phpfox::getT('music_song'), 'm')
                ->where('m.song_id = ' . (int) $iId)
                ->execute('getSlaveRow');
        ;
        if (!isset($aRow['song_id']))
        {
            return false;
        }
        return array(
            'sModule' => 'music',
            'iAlbumId' => $aRow['song_id'],
            'sTitle' => $aRow['title'],
            'sCommentType' => 'music'
        );
    }

	public function isAdvancedModule(){
		return $this->_bIsAdvancedModule;
	}

    /**
     * Input data:
     * + iSongId: int, required
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
     * + bIsFriend: bool.
     *
     */
    public function detail($aData)
    {
    	$sModelType =  $aData['sModelType'];
		
    	if($sModelType == 'musicsharing_song'){
    		return Phpfox::getService('mfox.musicsharing.song')->detail($aData);
    	}
    	
        $iSongId = isset($aData['iSongId']) ? (int) $aData['iSongId'] : 0;
        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music"))
            );
        }
        if (Phpfox::isModule('notification'))
        {
            Phpfox::getService('notification.process')->delete('comment_music_song', $iSongId, Phpfox::getUserId());
            Phpfox::getService('notification.process')->delete('music_song_like', $iSongId, Phpfox::getUserId());
        }
        if (!($aSong = Phpfox::getService('music')->getSong($iSongId)))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('music.the_song_you_are_looking_for_cannot_be_found')
            );
        }
        if (Phpfox::isModule('notification') && $aSong['user_id'] == Phpfox::getUserId())
        {
            Phpfox::getService('notification.process')->delete('music_songapproved', $aSong['song_id'], Phpfox::getUserId());
        }

        $aCallback = false;
        if (!empty($aSong['module_id']))
        {
            if ($aCallback = Phpfox::callback($aSong['module_id'] . '.getMusicDetails', $aSong))
            {
                if ($aSong['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aCallback['item_id'], 'music.view_browse_music'))
                {
                    return array(
                        'result' => 0,
                        'error_code' => 1,
                        'error_message' =>  Phpfox::getPhrase('music.unable_to_view_this_item_due_to_privacy_settings')
                    );
                }
            }
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_song', $aSong['song_id'], $aSong['user_id'], $aSong['privacy'], $aSong['is_friend'], true))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time')
            );
        }
        if (!$aSong)
        {
            return array();
        }
        
        // Update play time.
        Phpfox::getService('music.process')->play($aSong['song_id']);        
        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aSong, '_50_square');
        $sAlbumImage = '';
        if((int)$aSong['album_id'] > 0){
            $aAlbum = Phpfox::getService('mfox.album')->getAlbumByID($aSong['album_id']);
            if(isset($aAlbum['album_id'])){
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
            } else {
                $sAlbumImage = $this->_sDefaultImageAlbumPath;
            }
        } else {
            $sAlbumImage = $this->_sDefaultImageAlbumPath;
        }

        $sGenre = '';
        if(isset($aSong['genre_id']) && (int)$aSong['genre_id'] > 0){
            $aGenre = Phpfox::getService('music.genre')->getGenre($aSong['genre_id']);
            if(isset($aGenre['genre_id'])){
                //
                $sGenre = html_entity_decode(Phpfox::getLib('locale')->convert($aGenre['name']));
            }
        }

        $aLike['likes'] = Phpfox::getService('mfox.helper.like')->getLikesForFeed('music_song', $aSong['song_id']
            ,  $aSong['is_liked'], 1000, true);              
        $aLike['feed_total_like'] = Phpfox::getService('like')->getTotalLikeCount();
        $aUserLike = array();
        foreach($aLike['likes'] as $like){
            $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
        }

        $aUserDislike = array();
        $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('music_song', $aSong['song_id'], $bGetCount = false);
        foreach($aDislike as $dislike){
            $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
        }        

        $bCanView = false;
        if(Phpfox::getService('privacy')->check($sModule = '', $iItemId = ''
            , $aSong['user_id'], $aSong['privacy'], $aSong['is_friend'], $bReturn = true)){
            $bCanView = true;
        }

        $iTotalLike = count($aUserLike);
        // if($aSong['is_liked']) {
        //     $iTotalLike++;
        // }

        return array(
            'bIsLiked' => $aSong['is_liked'],
            'bCanDislike' => Phpfox::getService('mfox.like')->isTypeSupportDislike('music_song'),
            'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('music_song', $aSong['song_id'], Phpfox::getUserId()),
            'aUserLike' => $aUserLike,
            'aUserDislike' => $aUserDislike,
            'bIsFriend' => $aSong['is_friend'],
            'iSongId' => $aSong['song_id'],
            'iViewId' => $aSong['view_id'],
            'iPrivacy' => $aSong['privacy'],
            'iPrivacyComment' => $aSong['privacy_comment'],
            'bIsFeatured' => $aSong['is_featured'],
            'bIsSponsor' => $aSong['is_sponsor'],
            'iAlbumId' => $aSong['album_id'],
            'iGenreId' => $aSong['genre_id'],
            'sGenre' => $sGenre,
            'iUserId' => $aSong['user_id'],
            'sTitle' => $aSong['title'],
            'sDescription' => $aSong['description'],
            'sSongPath' => $aSong['song_path'],
            'iExplicit' => $aSong['explicit'],
            'sDuration' => $aSong['duration'],
            'iOrdering' => $aSong['ordering'],
            'iTotalPlay' => $aSong['total_play'],
            'iTotalComment' => $aSong['total_comment'],
            'iTotalLike' => $iTotalLike,
            'iTotalDislike' => count($aUserDislike),
            'fTotalScore' => $aSong['total_score'],
            'iTotalRating' => $aSong['total_rating'],
            'iRatingCount' => $aSong['total_rating'],
            'iTimeStamp' => $aSong['time_stamp'],
            'sTimeStamp' => date('D, j M Y G:i:s O', $aSong['time_stamp']),
            'sFullTimeStamp' => Phpfox::getLib('date')->convertTime($aSong['time_stamp'], null),
            'sModuleId' => $aSong['module_id'],
            'iItemId' => $aSong['item_id'],
            'iSongTotalComment' => $aSong['song_total_comment'],
            'iSongTotalPlay' => $aSong['song_total_play'],
            'iSongTimeStamp' => $aSong['song_time_stamp'],
            'sSongTimeStamp' => date('l, F j', $aSong['song_time_stamp']),
            'sFullSongTimeStamp' => date('l, F j', $aSong['song_time_stamp']) . ' at ' . date('g:i a', $aSong['song_time_stamp']),
            'bSongIsSponsor' => $aSong['song_is_sponsor'],
            'sAlbumUrl' => $aSong['album_url'],
            'sAlbumName' => empty($aSong['album_url']) ? 'No name' : $aSong['album_url'],
            'bIsOnProfile' => $aSong['is_on_profile'],
            'bProfileUserId' => $aSong['profile_user_id'],
            'bHasRated' => $aSong['has_rated'],
            'bIsRating' => (isset($aSong['has_rated']) && (int)$aSong['has_rated'] > 0) ? true : false,
            'fRating' => (float) ($aSong['total_score']/2), 
            'iProfilePageId' => $aSong['profile_page_id'],
            'sUserName' => $aSong['user_name'],
            'sFullname' => $aSong['full_name'],
            'iGender' => $aSong['gender'],
            'sUserImage' => $sUserImage,
            'sImagePath' => $sUserImage,
            'bIsInvisible' => $aSong['is_invisible'],
            'iUserGroupId' => $aSong['user_group_id'],
            'iLanguageId' => $aSong['language_id'],
            "sAlbumModelType"=> 'music_album',
            'bCanPostComment' => Phpfox::getService('mfox.comment')->checkCanPostCommentOnItem($aSong),
            'sAlbumImage' => $sAlbumImage,
            'iUserLevelId' => $aSong['user_group_id'],
            'aEditAlbums' => $this->getAlbumsForEdit($aCallback),
            'bCanView' => $bCanView,
            'sLink_Url' => Phpfox::permalink('music', $aSong['song_id'], $aSong['title']),
        );
    }

    /**
     * Get albums for edit when edit.
     * @param array $aCallback
     * @return array
     */
    public function getAlbumsForEdit($aCallback = null)
    {
        /**
         * @var array
         */
        $aCond = array();
        if (isset($aCallback['module_id']))
        {
            $aCond[] = 'ma.view_id = 0 AND ma.user_id = ' . Phpfox::getUserId() . ' AND ma.item_id = 0';
        }
        else
        {
            $aCond[] = 'ma.view_id = 0 AND ma.user_id = ' . Phpfox::getUserId() . ' AND ma.item_id = 0';
        }
        /**
         * @var array Get albums.
         */
        $aAlbums = $this->database()
                        ->select('ma.album_id AS iAlbumId, ma.name AS sName')
                        ->from(Phpfox::getT('music_album'), 'ma')
                        ->where($aCond)
                        ->order('ma.name ASC')
                        ->execute('getSlaveRows');
        // Insert default.
        array_unshift($aAlbums, array('iAlbumId' => 0, 'sName' => 'Select:'));
        
        return $aAlbums;
    }
    /**
     * Input data:
     * + sModuleId: string, optional. In pages. Default ''.
     * + iItem: int, optional. In pages. Default 0.
     * 
     * Output data:
     * + iAlbumId: int.
     * + sName: string.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see song/getAlbumsForUpload
     * 
     * @param array $aData
     * @return array
     */
    public function getAlbumsForUpload($aData)
    {
        /**
         * @var array
         */
        $aCallback = array(
            'sModuleId' => isset($aData['sModuleId']) && !empty($aData['sModuleId']) ? $aData['sModuleId'] : null,
            'iItem' => isset($aData['iItem']) && $aData['iItem'] > 0 ? (int) $aData['iItem'] : null
        );
        return $this->getAlbumsForEdit($aCallback);
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
    public function updateCounterMusic($aData)
    {
        $iSongId = isset($aData['iSongId']) ? (int) $aData['iSongId'] : 0;        
        if (!Phpfox::getUserParam('music.can_access_music'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_music")));
        }

        if (!($aSong = Phpfox::getService('music')->getSong($iSongId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('music.the_song_you_are_looking_for_cannot_be_found'));
        }

        // Check privacy for song.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('music_song', $aSong['song_id'], $aSong['user_id'], $aSong['privacy'], $aSong['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        
        // Update play time.
        Phpfox::getService('music.process')->play($iSongId);
        
        return array('result' => 1, 'error_code' => 0, 'message'=>html_entity_decode(Phpfox::getPhrase("mfox.update_counter_music_successfully")));
    }
    /**
     * Get activity feed of the song.
     * @param array $aItem
     * @param boolean $bIsAlbum
     * @param bool $bIsChildItem
     * @return boolean
     */
    public function doSongGetActivityFeedSong($aItem, $bIsAlbum = false, $bIsChildItem = false)
	{
        /**
         * @var bool
         */
		$bIsAlbum = false;
		
		$this->database()->select('ma.name AS album_name, ma.album_id, u.gender, ')
			->leftJoin(Phpfox::getT('music_album'), 'ma', 'ma.album_id = ms.album_id')
			->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = ma.user_id');
		
		$this->database()->select('mp.play_id AS is_on_profile, ')->leftJoin(Phpfox::getT('music_profile'), 'mp', 'mp.song_id = ms.song_id AND mp.user_id = ' . Phpfox::getUserId());
		
		if ($bIsChildItem)
		{
			$this->database()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user'), 'u2', 'u2.user_id = ms.user_id');
		}		
		/**
         * @var array
         */
		$aRow = $this->database()->select('ms.song_id, ms.title, ms.module_id, ms.item_id, ms.description, ms.total_play, ms.privacy, ms.time_stamp, ms.total_comment, ms.total_like, ms.user_id, l.like_id AS is_liked')
			->from(Phpfox::getT('music_song'), 'ms')
			->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'music_song\' AND l.item_id = ms.song_id AND l.user_id = ' . Phpfox::getUserId())	
			->where('ms.song_id = ' . (int) $aItem['item_id'])
			->execute('getSlaveRow');		
			
		if (!isset($aRow['song_id']))
		{
			return false;
		}
		
		if ($bIsChildItem)
		{
			$aItem = array_merge($aRow, $aItem);
		}			
		
		if ((defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'music.view_browse_music'))
			|| (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aRow['item_id'], 'music.view_browse_music'))		
			)
		{
			return false;
		}			
		/**
         * @var bool
         */
		$bShowAlbumTitle = false;
		if (!empty($aRow['album_name']))
		{
			$bShowAlbumTitle = true;	
		}
        /**
         * @var int
         */
		$iTitleLength = (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : 50);
        /**
         * @var array
         */
		$aReturn = array(
            'sTypeId' => 'music',
            'iUserId' => $aRow['user_id'],
            'sUsername' => $aRow['user_name'],
            'sFullName' => $aRow['full_name'],

			'sFeedTitle' => Phpfox::getLib('parse.output')->shorten($aRow['title'], $iTitleLength, '...'),
			'sFeedStatus' => $aRow['description'],
			'sFeedInfo' => ($bShowAlbumTitle ?  Phpfox::getPhrase('feed.shared_a_song_from_gender_album_a_href_album_link_album_name_a', array('gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'album_link' => Phpfox::getLib('url')->permalink('music.album', $aRow['album_id'], $aRow['album_name']), 'album_name' => Phpfox::getLib('parse.output')->shorten($aRow['album_name'], (Phpfox::isModule('notification') ? Phpfox::getParam('notification.total_notification_title_length') : 50 ), '...'))) :  Phpfox::getPhrase('feed.shared_a_song')),
			'sFeedLink' => Phpfox::permalink('music', $aRow['song_id'], $aRow['title']),
			'sFeedContent' => ($aRow['total_play'] > 1 ? $aRow['total_play'] . ' ' .  Phpfox::getPhrase('music.plays_lowercase') :  Phpfox::getPhrase('music.1_play')),
            'iFeedTotalPlay' => $aRow['total_play'],
			'iTotalComment' => $aRow['total_comment'],
			'iFeedTotalLike' => $aRow['total_like'],
			'bFeedIsLiked' => $aRow['is_liked'],
			'sFeedIcon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/music.png', 'return_url' => true)),
			'iTimeStamp' => $aRow['time_stamp'],
            'Time' => date('l, F j, o', (int) $aRow['time_stamp']) . ' at ' . date('h:i a', (int) $aRow['time_stamp']),
            'TimeConverted' => Phpfox::getLib('date')->convertTime($aRow['time_stamp'], 'comment.comment_time_stamp'),
			'bEnableLike' => true,
			'bCommentTypeId' => ($bIsAlbum ? 'music_album' : 'music_song'),
			'sLikeTypeId' => 'music_song',
			
            'aSong' => array(
				'iPrivacy' => $aRow['privacy'],
				'iSongId' => $aRow['song_id'],
				'iUserId' => $aRow['user_id'],
				'bIsOnProfile' => $aRow['is_on_profile']
			)
		);
		
		if (!$bIsChildItem || (isset($aItem['feed_id']) && $aItem['feed_id'] > 0))
		{
			$aReturn['sFeedImage'] = Phpfox::getLib('image.helper')->display(array(
					'theme' => 'misc/play_button.png',
					'return_url' => true		
				)
			);
		}
		
		return $aReturn;
	}

    // -----------------------------------------------------------
    // VERSION 3.03
    // -----------------------------------------------------------
    public function getSongs($aData)
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
            'genre' => !empty($aData['iCategoryId']) ? (int) $aData['iCategoryId'] : null,
        ));

        Phpfox::getUserParam('music.can_access_music', true);
        
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
            $this->_oSearch->setCondition('AND m.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"');
        }

        // sort
        $sSort = '';
        switch ($this->_oReq->get('sort')) {
            case 'most_viewed':
                $sSort = 'm.total_play DESC';
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
            'module_id' => 'music.song',
            'alias' => 'm',
            'field' => 'song_id',
            'table' => Phpfox::getT('music_song'),
            'hide_view' => array('pending', 'my'),
            'service' => 'mfox.song'
        );

        $iGenre = $this->_oReq->getInt('genre');

        switch ($sView)
        {
            case 'my':
                Phpfox::isUser(true);
                $this->_oSearch->setCondition('AND m.user_id = ' . Phpfox::getUserId());
                break;
            case 'pending':
                Phpfox::isUser(true);
                Phpfox::getUserParam('music.can_approve_songs', true);
                $this->_oSearch->setCondition('AND m.view_id = 1');
                break;
            default:
                if ($bIsProfile === true)
                {
                    $this->_oSearch->setCondition("AND m.view_id IN(" . ($aUser['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ") AND m.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ") AND m.user_id = " . $aUser['user_id'] . "");  
                }
                else
                {               
                    $this->_oSearch->setCondition("AND m.view_id = 0 AND m.privacy IN(%PRIVACY%)"); 
                    if ($sView == 'featured')
                    {
                        $this->_oSearch->setCondition('AND m.is_featured = 1');
                    }
                }
                break;
        }
        
        if ($iGenre && ($aGenre = Phpfox::getService('music.genre')->getGenre($iGenre)))
        {
            $this->_oSearch->setCondition('AND m.genre_id = ' . (int) $iGenre); 
        }       
        
        if ($aParentModule !== null)
        {
            $this->_oSearch->setCondition("AND m.module_id = '" . Phpfox_Database::instance()->escape($aParentModule['module_id']) . "' AND m.item_id = " . (int) $aParentModule['item_id']);
        }
        else
        {
            $this->_oSearch->setCondition('AND m.item_id = 0');
        }
        
        $this->_oBrowse->params($aBrowseParams)->execute();
        
        $aSongs = $this->_oBrowse->getRows();

        return $aSongs;
    }

    public function processRows(&$aRows)
    {
        $aTmpRows = $aRows;
        $aRows = array();

        foreach ($aTmpRows as $aRow)
        {
            // get album image URL
            if($aRow['album_image_path'] == null){
                $sAlbumImage = $this->_sDefaultImageAlbumPath;
            } else {
                $sAlbumImage = Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aRow['album_server_id'],
                        'path' => 'music.url_image',
                        'file' => $aRow['album_image_path'],
                        'suffix' => MAX_SIZE_OF_IMAGE_ALBUM,
                        'return_url' => true
                            )
                    );
            }
            $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aRow, '_50_square');

            $aLike['likes'] = Phpfox::getService('mfox.helper.like')->getLikesForFeed('music_song', $aRow['song_id']
                ,  $aRow['is_liked'], 1000, true);              
            $aLike['feed_total_like'] = Phpfox::getService('like')->getTotalLikeCount();
            $aUserLike = array();
            foreach($aLike['likes'] as $like){
                $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
            }

            $aUserDislike = array();
            $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('music_song', $aRow['song_id'], $bGetCount = false);
            foreach($aDislike as $dislike){
                $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
            }                    

            $iTotalLike = count($aUserLike);

            $aRows[] = array(
                'bIsLiked' => isset($aRow['is_liked']) ? true : false,
                'aUserLike' => $aUserLike,
                'aUserDislike' => $aUserDislike,
                'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('music_song', $aRow['song_id'], Phpfox::getUserId()),
                'sAlbumName' => isset($aRow['album_name']) ? $aRow['album_name'] : 'No name',
                'bIsOnProfile' => isset($aRow['is_on_profile']) ? true : false,
                'iSongId' => $aRow['song_id'],
                'iViewId' => $aRow['view_id'],
                'iPrivacy' => $aRow['privacy'],
                'iPrivacyComment' => $aRow['privacy_comment'],
                'bIsFeatured' => (bool) $aRow['is_featured'],
                'bIsSponser' => (bool) $aRow['is_sponsor'],
                'iAlbumId' => $aRow['album_id'],
                'iGenreId' => $aRow['genre_id'],
                'iUserId' => $aRow['user_id'],
                'sTitle' => $aRow['title'],
                'sDescription' => $aRow['description'],
                'sSongPath' => Phpfox::getService('music')->getSongPath($aRow['song_path'], $aRow['server_id']),
                'iExplicit' => $aRow['explicit'],
                'sDuration' => $aRow['duration'],
                'iOrdering' => $aRow['ordering'],
                'iTotalPlay' => $aRow['total_play'],
                'iTotalComment' => $aRow['total_comment'],
                // 'iTotalLike' => $aRow['total_like'],
                // 'iTotalDislike' => $aRow['total_dislike'],
                'iTotalLike' => $iTotalLike,
                'iTotalDislike' => count($aUserDislike),
                'iTotalScore' => $aRow['total_score'],
                'iTotalRating' => $aRow['total_rating'],
                'iTimeStamp' => $aRow['time_stamp'],
                'sTimeStamp' => date('l, F j', $aRow['time_stamp']),
                'sFullTimeStamp' => Phpfox::getLib('date')->convertTime($aRow['time_stamp'], null),
                'sModuleId' => isset($aRow['module_id']) ? $aRow['module_id'] : '',
                'iItemId' => $aRow['item_id'],
                'iProfilePageId' => $aRow['profile_page_id'],
                'sUsername' => $aRow['user_name'],
                'sFullname' => $aRow['full_name'],
                'iGender' => $aRow['gender'],
                'sUserImage' => $sUserImage,
                'bIsInvisible' => (bool) $aRow['is_invisible'],
                'iUserGroupId' => (int) $aRow['user_group_id'],

                'sAlbumImage' => $sAlbumImage,
                'iUserLevelId' => (int) $aRow['user_group_id'],
                'fRating' => (float) ($aRow['total_score']/2), 
                "sAlbumModelType"=> 'music_album',

                'iLanguageId' => isset($aRow['language_id']) ? $aRow['language_id'] : 0,
                'bCanComment' => $this->checkPrivacyCommentOnSong($aRow['song_id']) == null ? true : false,
            );
        }
    }

    public function query()
    {
        return Phpfox::getService('music.song.browse')->query();
    }
    
    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Mfox_Service_Friend::instance()->queryJoin($bNoQueryFriend))
        {           
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = m.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }
    }

    public function getSongByID($songID){
        return $this->database()->select('*')
            ->from(Phpfox::getT('music_song'))
            ->where('song_id = ' . (int) $songID)
            ->execute('getSlaveRow');            
    }    

    /**
     * Input data:
     * + mp3: mp3 file or sFilePath string, required.
     *
     * Output data:
     * + result: int.
     * + error_code: int.
     * + iSongId: int.
     * + sSongTitle: string.
     * + sSongUrl: int.
     *
     */
    public function parser($aData)
    {
        $ret = $this->create($aData);

        return array(
            'result' => 1,
            'error_code' => 0,
            'message' =>  Phpfox::getPhrase('music.song_successfully_uploaded'),
            'iAlbumId' => $ret['album_id'],
            'sSongUrl' => $ret['sSongUrl'],
            'iSongId' => $ret['song_id'],
            'sSongTitle' => $ret['title']
        );
    }

    public function getgenre($aData){
    	if ($this->isAdvancedModule()){
    		return Phpfox::getService('mfox.musicsharing.song')->getgenre();
    	}
    	$aResult =  Phpfox::getService('music.genre')->getList();
		
		foreach($aResult as $index=>$row){
			$aResult[$index]['name']= html_entity_decode(Phpfox::getLib('locale')->convert($row['name']));
		}
		
		return $aResult;
    }

    public function fetch($aData){
        if(isset($aData['iMaxId'])){
            $aData['iLastSongId'] = $aData['iMaxId'];
        }

		if($this->isAdvancedModule()){
			return Phpfox::getService('mfox.musicsharing.song')->getSongs($aData);	
		}else {
			return $this->getSongs($aData);
		}
    }
}
