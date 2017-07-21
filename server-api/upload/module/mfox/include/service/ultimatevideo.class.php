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
 * @since May 21, 2013
 * @link Mfox Api v3.0
 */
class Mfox_Service_UltimateVideo extends Phpfox_Service {

    public function __construct(){

    }

    public function canCreateVideo() {
        // check privacy
        if(!user('ynuv_can_view_video', 0)){
            return array('result' => 0, 'error_code' => 1, 'error_message' => _p('You do not have permission to add video'));
        }
        if(Phpfox::getService('ultimatevideo')->countVideoOfUserId(Phpfox::getUserId()) > user('ynuv_how_many_video_user_can_add', 0)){
            return array('result' => 0, 'error_code' => 1, 'error_message' => _p('You have reached your creating video limit. Please contact administrator.'));
        }
        if(user('ynuv_time_before_share_other_video',0) != 0)
        { 
            $iFlood = user('ynuv_time_before_share_other_video',0);
            $aFlood = array(
                'action' => 'last_post', // The SPAM action
                'params' => array(
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('ynultimatevideo_videos'), // Database table we plan to check
                    'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);   
                )
            );
                            
                // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood))
            {   
                return array('result' => 0, 'error_code' => 1, 'error_message' => _p('Uploading video a little too soon.') . ' ' . Phpfox::getLib('spam')->getWaitTime());
            }
        }  
        return array('result' => 0, 'error_code' => 0, 'message' => _p('You can add a video'));
    }

    public function create($aData)
    {

        $aError = $this->canCreateVideo();
        if($aError['error_message'])
            return $aError;
        $isPass = true;
        // map data from client to service
        $aVal = [
            'video_link' => $aData['sUrl'],
            'video_code' => $aData['sVideoCode'],
            'video_source' => $aData['sVideoSource'],
            'privacy' => $aData['iPrivacy'],
            'category' => array($aData['iCategoryId']),
            'video_embed' => $aData['sUrl'],
            'description' => $aData['sDescription'],
            'title' => $aData['sTitle'],
            'tag_list' => $aData['sTags'],
        ];
        if($aData['bAllowUploadChannel'])
            $aVal['allow_upload_channel'] = $aData['bAllowUploadChannel'];
        if($_FILES['video']['name'] != '') {
            $maxFileSize = user('ynuv_file_size_limit_in_megabytes', 0);
            if (isset($_FILES['video']['name']) && empty($_FILES['video']['name']) || (isset($_FILES['video']['size']) && (int)$_FILES['video']['size'] <= 0)) {
                $isPass = false;
                \Phpfox::addMessage(_p('No files found or file is not valid. Please try again.'));
            } else {
                $aVideo = \Phpfox::getLib('file')->load('video', [], $maxFileSize);

                if ($aVideo) {
                    //upload video file
                    $aVal['video_code'] = substr($_FILES['video']['type'], strpos($_FILES['video']['type'], '/') + 1);
                    $filePath = PHPFOX_DIR_FILE . 'ynultimatevideo' . PHPFOX_DS;
                    if (!is_dir($filePath)) {
                        if (!@mkdir($filePath, 0777, 1)) {

                        }
                    }
                    $videoFilePath = \PhpFox::getLib('file')->upload('video', $filePath, $_FILES['video']['name']);
                    $aVal['video_path'] = ($videoFilePath) ? $videoFilePath : "";
                } else {
                    $isPass = false;
                }
            }
        }
        

        if($isPass){
            $iId = \Phpfox::getService('ultimatevideo.process')->add($aVal,null,true);
            if($iId){
                return array('result' => 0, 'error_code' => 0, 'message' => _p('Success'));
            }
        }

        return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
    }

    public function validateUrl($aData){
        $url = $aData['sVideoCode'];
        $type = $aData['sVideoSource'];
        if($type == "VideoURL"){
            if(empty($url)){
                return array('result' => 0, 'error_code' => 1, 'error_message' => _p('We could not find a video there - please check the URL and try again.'));           
            }
            else{
                return array('result' => 0, 'error_code' => 0, 'title' => '', 'description' => ''); 
            }
        }
        $adapter = Phpfox::getService('ultimatevideo') -> getClass($type);
        if($type != "Dailymotion"){
            $adapter -> setParams(array('code' => $url));
        }
        else{
            $adapter -> setParams(array('link' => $url));
            $adapter -> setParams(array('code' => $url));
        }
        $valid = ($adapter -> isValid())?true:false;
        if($adapter -> fetchLink())
        {
            $title = strip_tags($adapter -> getVideoTitle());
            $description = $adapter -> getVideoDescription();
            $description = str_replace("<br />", "\r\n", $description);
            return array('result' => 0, 'error_code' => 0, 'title' => $title, 'description' => $description); 
        }
        else{
            return array('result' => 0, 'error_code' => 2, 'error_message' => _p('We could not find a video there - please check the URL and try again.')); 
        }
    }


    public function createPlaylist($aData){
        if(!user('ynuv_can_add_playlist')){
            return array('result' => 0, 'error_code' => 1, 'error_message' => _p('You do not have permission to create playlits'));
        }
        $aVals = [
            'title' => $aData['sTitle'],
            'privacy' => $aData['iPrivacy'],
            'description' => $aData['sDescription'],
            'category' => array($aData['iCategoryId']),
        ];
        $iPlaylistId = Phpfox::getService('ultimatevideo.playlist.process')->add($aVals);
        if($iPlaylistId){
            $this->uploadPlaylistImage($iPlaylistId);
            return array('result' => 0, 'error_code' => 0, 'message' =>  _p('Playlist has been created successfully'));
        }
        return array('result' => 0, 'error_code' => 1, 'error_message' =>  _p('Can not create playlist'));
    }

    public function editPlaylist($aData){
        $iPlaylistId = $aData['iPlaylistId'];
        if (!($aPlaylist = Phpfox::getService('ultimatevideo.playlist')->getPlaylistById($iPlaylistId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  _p('The playlist can not be found'));
        }

        $aVals = [
            'title' => $aData['sTitle'],
            'privacy' => $aData['iPrivacy'],
            'description' => $aData['sDescription'],
            'category' => array($aData['iCategoryId']),
        ];

        if(Phpfox::getService('ultimatevideo.playlist.process')->update($aVals, $iPlaylistId)){
            // Remove videos from playlist
            if(isset($aData['aRemoveVideos']) && count($aData['aRemoveVideos'])){
                foreach($aData['aRemoveVideos'] as $iVideoId){
                    Phpfox::getService('ultimatevideo.playlist.process')->removeVideo($iVideoId, $iPlaylistId);
                }
            }
            // Update playlist image
            $this->uploadPlaylistImage($iPlaylistId);
            return array('result' => 0, 'error_code' => 0, 'message' =>  _p('Playlist has been updated successfully'));
        }
        return array('result' => 0, 'error_code' => 1, 'error_message' =>  _p('Could not edit this playlist'));

    }

    public function uploadPlaylistImage($iPlaylistId){
        if($iPlaylistId){
            $imagePath = $this->database()->select('image_path')
                                ->from(Phpfox::getT('ynultimatevideo_playlists'))
                                ->where('playlist_id = '.$iPlaylistId)
                                ->execute('getSlaveField');
        }
        if(isset($_FILES['image']['name']) && empty($_FILES['image']['name'])){              
            return false;
        }
        else{
            $maxFileSize = ((int)user('ynuv_max_file_size_photos_upload') > 0 ) ? ((int)user('ynuv_max_file_size_photos_upload'))/1024 : null;
            $aImage = Phpfox::getLib('file')->load('image', array(
                                        'jpg','gif','png'
                                            ), $maxFileSize);
            if($aImage)
            {
                if($imagePath){
                    
                    $aImages = array(
                        Phpfox::getParam('core.dir_pic') . sprintf($imagePath, '_120'),
                        Phpfox::getParam('core.dir_pic') . sprintf($imagePath, '_250'),
                        Phpfox::getParam('core.dir_pic') . sprintf($imagePath, '_500'),
                        Phpfox::getParam('core.dir_pic') . sprintf($imagePath, '_1024')
                        );
                    foreach ($aImages as $sImage)
                    {
                        if (file_exists($sImage))
                        {
                            @unlink($sImage);
                        }
                    }                  
                }
                $sPicStorage = Phpfox::getParam('core.dir_pic') . 'ynultimatevideo/';

                if (!is_dir($sPicStorage)) {
                    @mkdir($sPicStorage, 0777, 1);
                    @chmod($sPicStorage, 0777);
                }
                $sNewFileName = Phpfox::getLib('file')->upload('image', $sPicStorage, PHPFOX_TIME);
                Phpfox::getLib('image')->createThumbnail($sPicStorage.sprintf($sNewFileName,''),$sPicStorage.sprintf($sNewFileName,'_'. 120), 120, 120);
                Phpfox::getLib('image')->createThumbnail($sPicStorage.sprintf($sNewFileName,''),$sPicStorage.sprintf($sNewFileName,'_'. 250), 250, 250);
                Phpfox::getLib('image')->createThumbnail($sPicStorage.sprintf($sNewFileName,''),$sPicStorage.sprintf($sNewFileName,'_'. 500), 500, 500);
                Phpfox::getLib('image')->createThumbnail($sPicStorage.sprintf($sNewFileName,''),$sPicStorage.sprintf($sNewFileName,'_'. 1024), 1024, 1024);

                $this->database()->update(Phpfox::getT('ynultimatevideo_playlists'),array('image_path'=> 'ynultimatevideo/'.$sNewFileName),'playlist_id='.(int)$iPlaylistId);
                $sTempFile = $sPicStorage . sprintf($sNewFileName, '');
                if (file_exists($sTempFile))
                {
                    @unlink($sTempFile);
                }
                return true;
            }
        }
    }

    
    /**
     * Delete one video.
     * 
     * input data:
     * + iVideoId: int, required.
     * + sModule: string, optional.
     * + iItem: int, optional
     * 
     * output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see video/delete
     * 
     * @param array $aData
     * @return array
     */
    public function delete($aData)
    {
        /**
         * @var int
         */
        $iVideoId = isset($aData['iVideoId']) ? (int) $aData['iVideoId'] : 0;
        /**
         * @var string
         */
        $sModule = isset($aData['sModule']) ? trim($aData['sModule']) : '';
        /**
         * @var int
         */
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;
        // Get the callback.
        if ($iItem > 0 && !empty($sModule))
        {
            $aCallback = array(
                'module' => $sModule,
                'item' => $iItem
            );
        }
        else
        {
            $aCallback = false;
        }
        // Get the video.
        if (!($aVideo = Phpfox::getService('ultimatevideo')->getVideo($iVideoId)))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('video.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }
        // Check the privacy.
        if(!$this->canDelete($aVideo)){
            return array('result' => 0, 'error_code' => 1, 'error_message' => 'You do not have permission to delete this video');
        }
        // Delete video.
        if (Phpfox::getService('ultimatevideo.process')->deleteVideo($iVideoId))
        {
            return array('result' => 1, 'error_code' => 0, 'message' =>  _p('Video has been deleted successfully'));
        }
        return array('result' => 0, 'error_code' => 1, 'error_message' => Phpfox_Error::get());
    }

    public function addWatchLater($aData){
        $iVideoId = isset($aData['iVideoId']) ? (int) $aData['iVideoId'] : 0;
        if (!($aVideo = Phpfox::getService('ultimatevideo')->getVideo($iVideoId)))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('video.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }
        Phpfox::getService('ultimatevideo.watchlater')->add(Phpfox::getUserId(), $iVideoId);
        if(Phpfox::getService('ultimatevideo.watchlater')->isWatchLater(Phpfox::getUserId(), $iVideoId)){
            return array('result' => 1, 'error_code' => 0, 'message' =>  html_entity_decode(_p('Video has been added to watch later')));
        }
        return array('result' => 0, 'error_code' => 1, 'message' => Phpfox_Error::get());
    }
    public function deleteWatchLater($aData){
        $iVideoId = isset($aData['iVideoId']) ? (int) $aData['iVideoId'] : 0;
        if (!($aVideo = Phpfox::getService('ultimatevideo')->getVideo($iVideoId)))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('video.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }
        Phpfox::getService('ultimatevideo.watchlater')->delete(Phpfox::getUserId(), $iVideoId);
        if(!Phpfox::getService('ultimatevideo.watchlater')->isWatchLater(Phpfox::getUserId(), $iVideoId)){
            return array('result' => 1, 'error_code' => 0, 'message' =>  html_entity_decode(_p('Video has been removed from watch later')));
        }
        return array('result' => 0, 'error_code' => 1, 'message' => Phpfox_Error::get());
    }

    public function addFavorite($aData){
        $iVideoId = isset($aData['iVideoId']) ? (int) $aData['iVideoId'] : 0;
        if (!($aVideo = Phpfox::getService('ultimatevideo')->getVideo($iVideoId)))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('video.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }
        Phpfox::getService('ultimatevideo.favorite')->add(Phpfox::getUserId(), $iVideoId);
        if(Phpfox::getService('ultimatevideo.favorite')->isFavorite(Phpfox::getUserId(), $iVideoId)){
            return array('result' => 1, 'error_code' => 0, 'message' =>  html_entity_decode(_p('Video has been added to favourite')));
        }
        return array('result' => 0, 'error_code' => 1, 'message' => Phpfox_Error::get());
    }
    public function deleteFavorite($aData){
        $iVideoId = isset($aData['iVideoId']) ? (int) $aData['iVideoId'] : 0;
        if (!($aVideo = Phpfox::getService('ultimatevideo')->getVideo($iVideoId)))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('video.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }
        Phpfox::getService('ultimatevideo.favorite')->delete(Phpfox::getUserId(), $iVideoId);
        if(!Phpfox::getService('ultimatevideo.favorite')->isFavorite(Phpfox::getUserId(), $iVideoId)){
            return array('result' => 1, 'error_code' => 0, 'message' =>  html_entity_decode(_p('Video has been removed from favourite')));
        }
        return array('result' => 0, 'error_code' => 1, 'message' => Phpfox_Error::get());
    }

    /**
     * input data:
     * + iVideoId: int, required.
     * + sModule: string, optional.
     * + iItem: int, optional.
     * 
     * output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see video/like
     * 
     * @param array $aData
     * @return array
     */
    public function like($aData)
    {
        /**
         * @var int
         */
        $iVideoId = isset($aData['iVideoId']) ? (int) $aData['iVideoId'] : 0;
        /**
         * @var string
         */
        $sModule = isset($aData['sModule']) ? trim($aData['sModule']) : '';
        /**
         * @var int
         */
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;
        // Get the callback.
        if ($iItem > 0 && !empty($sModule))
        {
            $aCallback = array(
                'module' => $sModule,
                'item' => $iItem
            );
        }
        else
        {
            $aCallback = false;
        }
        // Get the video.
        if (!($aVideo = Phpfox::getService('video')->callback($aCallback)->getVideo($iVideoId)))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('video.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }
        // Check the privacy.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('video', $aVideo['video_id'], $aVideo['user_id'], $aVideo['privacy'], $aVideo['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        return Phpfox::getService('mfox.like')->add(array('sType' => 'video', 'iItemId' => $iVideoId));
    }

    /**
     * input data:
     * + iVideoId: int, required.
     * + sModule: string, optional.
     * + iItem: int, optional.
     * 
     * output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see video/unlike
     * 
     * @param array $aData
     * @return array
     */
    public function unlike($aData)
    {
        /**
         * @var int
         */
        $iVideoId = isset($aData['iVideoId']) ? (int) $aData['iVideoId'] : 0;

        /**
         * @var string
         */
        $sModule = isset($aData['sModule']) ? trim($aData['sModule']) : '';

        /**
         * @var int
         */
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;

        // Get the callback.
        if ($iItem > 0 && !empty($sModule))
        {
            $aCallback = array(
                'module' => $sModule,
                'item' => $iItem
            );
        }
        else
        {
            $aCallback = false;
        }

        // Get the video.
        if (!($aVideo = Phpfox::getService('video')->callback($aCallback)->getVideo($iVideoId)))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('video.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }

        // Check the privacy.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('video', $aVideo['video_id'], $aVideo['user_id'], $aVideo['privacy'], $aVideo['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        if (isset($aVideo['is_liked']) && $aVideo['is_liked'])
        {
            return Phpfox::getService('mfox.like')->delete(array('sType' => 'video', 'iItemId' => $iVideoId));
        }
        else
        {
            return array('result' => 0, 'error_code' => 1, 'message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_have_already_unliked_this_item")));
        }
    }

    /**
     * Input data: N/A
     * 
     * Output data:
     * + iCategoryId: int.
     * + iParentId: int.
     * + bIsActive: bool.
     * + sName: string.
     * + iLevel: int.
     * + iOrdering: int.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see video/category
     * 
     * @param array $aData
     * @return array
     */
    public function categories($aData)
    {
        $aCategories = $this->_get(0, 1);
        return $this->getCategory($aCategories);
    }

    public function getCategory($aCategories)
    {
        $aResult = array();
        if ($aCategories)
        {
            foreach ($aCategories as $aCategory)
            {
                $aResult[] = array(
                    'iParentId' => $aCategory['iParentId'],
                    'iCategoryId' => $aCategory['iCategoryId'],
                    'bIsActive' => $aCategory['bIsActive'],
                    'sName' => html_entity_decode(Phpfox::getLib('locale')->convert($aCategory['sName'])),
                    'iLevel' => $aCategory['iLevel'],
                    'iOrdering' => $aCategory['iOrdering']
                );

                if ($aCategory['aChild'])
                {
                    $aTemp = $this->getCategory($aCategory['aChild']);

                    foreach ($aTemp as $aItem)
                    {
                        $aResult[] = $aItem;
                    }
                }
            }
        }
        return $aResult;
    }

    /**
     * Input data:
     * + iParentId: int.
     * + iActive: int.
     * 
     * Output data:
     * + iCategoryId: int.
     * + iParentId: int.
     * + bIsActive: bool.
     * + sName: string.
     * + iOrdering: int.
     * + aChild: array of sub categories.
     * 
     * @param int $iParentId Parent id of category.
     * @param int $iActive Is active category?
     * @return array
     */
    private function _get($iParentId, $iActive, $iLevel = 0)
    {
        /**
         * @var array
         */
        $aCategories = $this->database()
                ->select('category_id AS iCategoryId, parent_id AS iParentId, is_active AS bIsActive, title AS sName, ordering AS iOrdering')
                ->from(Phpfox::getT('ynultimatevideo_category'))
                ->where('parent_id = ' . (int) $iParentId . ' AND is_active = ' . (int) $iActive . '')
                ->order('ordering ASC')
                ->execute('getRows');

        if (count($aCategories))
        {
            foreach ($aCategories as $iKey => $aCategory)
            {
                $aCategories[$iKey]['iLevel'] = $iLevel;
                $aCategories[$iKey]['aChild'] = $this->_get($aCategory['iCategoryId'], $iActive, $iLevel + 1);
                $aCategories[$iKey]['sName'] = Core\Lib::phrase()->isPhrase($aCategory['sName']) ? _p($aCategory['sName']) : $aCategory['sName'];
            }
        }

        return $aCategories;
    }

    public function search($aData)
    {
        if($aData['sView'] == 'all_playlists')
            return $this->getPlaylists($aData);
        return $this->getVideos($aData);
    }

    public function fetch($aData)
    {
        if(($aData['sView'] == 'all_playlists') || ($aData['sView'] == 'my_playlists'))
            return $this->getPlaylists($aData);
        return $this->getVideos($aData);
    }

    public function processRows(&$aRows)
    {
        $aTmpRows = $aRows;
        $aRows = array();

        foreach($aTmpRows as $aRow)
        {
            $aRows[] = $this->processRow($aRow);
        }
    }

    private function getVideos($aData)
    {
        if (!Phpfox::getUserParam('ynuv_can_view_video'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_videos")));
        }

		$sSortBy = isset($aData['sOrder']) ? $aData['sOrder'] : 'creation_date';

        $sAction = (isset($aData['sAction']) && $aData['sAction'] == 'new') ? 'new' : 'more';
        $iLastTimeStamp = isset($aData['iLastTimeStamp']) ? (int) $aData['iLastTimeStamp'] : 0;
        $iLimit = isset($aData['iLimit']) ? (int) $aData['iLimit'] : 10;

        $iPage = isset($aData['iPage']) ? (int) $aData['iPage'] : 1;
        $sView = isset($aData['sView']) ? $aData['sView'] : '';
        $sTag = isset($aData['sTag']) ? $this->_tag($aData['sTag']) : '';
        $iCategory = isset($aData['iCategory']) ? (int) $aData['iCategory'] : 0;
        $iSponsor = isset($aData['iSponsor']) ? (int) $aData['iSponsor'] : 0;
        $sModule = isset($aData['sModule']) ? trim($aData['sModule']) : 'video';
        $iItem = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;
        $sSearch = isset($aData['sSearch']) ? $aData['sSearch'] : '';
        $bIsUserProfile = (isset($aData['bIsUserProfile']) && $aData['bIsUserProfile'] == 'true') ? true : false;
        $iPlaylistId = isset($aData['iPlaylistId']) ? (int) $aData['iPlaylistId'] : 0;

        // Get the callback.
        if ($iItem > 0 && !empty($sModule))
        {
            $aParentModule = array(
                'module_id' => $sModule,
                'item_id' => $iItem
            );
        }
        else
        {
            $aParentModule = false;
        }

        if ($bIsUserProfile)
        {
            /**
             * @var int
             */
            $iProfileId = isset($aData['iProfileId']) ? (int) $aData['iProfileId'] : 0;

            $aUser = Phpfox::getService('user')->get($iProfileId);

            if (!isset($aUser['user_id']))
            {
                return array('result' => 0, 'error_code' => 1, 'message'=>html_entity_decode(Phpfox::getPhrase("mfox.profile_is_not_valid")));
            }
        }
        /**
         * @var array
         */
        $aCond = array();

        // For search.
        if (!empty($sSearch))
        {
            $aCond[] = 'm.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"';
        }

        if ($iLastTimeStamp > 0)
        {
            if ($sAction == 'more')
            {
                $aCond[] = 'm.time_stamp < ' . $iLastTimeStamp;
            }
            else
            {
                $aCond[] = 'm.time_stamp > ' . $iLastTimeStamp;
            }
        }

        switch ($sView) {
            case 'pending':
                // if (Phpfox::getUserParam('video.can_approve_videos'))
                // {
                //     $aCond[] = 'm.view_id = 2';
                // }
                break;
            case 'my':
                $aCond[] = 'm.user_id = ' . Phpfox::getUserId();
                break;
            case 'favorite':
                if (!Phpfox::isUser())
                {
                    return array(
                        'error_code' => 1,
                        'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_access_videos'),
                    );
                }
                if ($aParentModule && $aParentModule['module_id'] == 'pages') 
                {
                    $aCond[] = 'm.module_id = \'' . $sModule . '\' AND m.video_id = ' . $iItem . ' AND m.video_id IN (SELECT f.video_id FROM ' . Phpfox::getT('ynultimatevideo_favorites') . ' f WHERE f.user_id =' . Phpfox::getUserId() . ' )';
                } 
                else
                {
                    $aCond[] = 'm.video_id IN (SELECT f.video_id FROM ' . Phpfox::getT('ynultimatevideo_favorites') . ' f WHERE f.user_id =' . Phpfox::getUserId() . ' )';
                }
                break;
            case 'watch-later':
                if (!Phpfox::isUser())
                {
                    return array(
                        'error_code' => 1,
                        'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_access_videos'),
                    );
                }
                if ($aParentModule && $aParentModule['module_id'] == 'pages') 
                {
                    $aCond[] = 'm.module_id = \'' . $sModule . '\' AND m.video_id = ' . $iItem . ' AND m.video_id IN (SELECT l.video_id FROM ' . Phpfox::getT('ynultimatevideo_favorites') . ' l WHERE l.user_id =' . Phpfox::getUserId() . ' )';
                } 
                else
                {
                    $aCond[] = 'm.video_id IN (SELECT h.video_id FROM ' . Phpfox::getT('ynultimatevideo_watchlaters') . ' h WHERE h.user_id =' . Phpfox::getUserId() . ' )';
                }
                break;
            case 'history':
                if (!Phpfox::isUser())
                {
                    return array(
                        'error_code' => 1,
                        'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_access_videos'),
                    );
                }
                if ($aParentModule && $aParentModule['module_id'] == 'pages') 
                {
                    $aCond[] = 'm.module_id = \'' . $sModule . '\' AND m.video_id = ' . $iItem . ' AND m.video_id IN (SELECT f.video_id FROM ' . Phpfox::getT('ynultimatevideo_favorites') . ' f WHERE f.user_id =' . Phpfox::getUserId() . ' )';
                } 
                else
                {
                    $aCond[] = 'm.video_id IN (SELECT f.item_id FROM ' . Phpfox::getT('ynultimatevideo_watchlaters') . ' f WHERE f.user_id =' . Phpfox::getUserId() . ' AND f.item_type = 0)';
                }
                break;
            default:
                if ($bIsUserProfile)
                {
                    $aCond[] = 'm.view_id ' . ($aUser['user_id'] == Phpfox::getUserId() ? 'IN(0,2)' : '= 0') . ' AND m.item_id = 0 AND m.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ') AND m.user_id = ' . (int) $aUser['user_id'];
                }
                else
                {
                    if ($aParentModule !== false)
                    {
                        $aCond[] = 'm.module_id = \'' . Phpfox::getLib('database')->escape($aParentModule['module_id']) . '\' AND m.item_id = ' . (int) $aParentModule['item_id'] . ' AND m.privacy IN(%PRIVACY%)';
                    }
                    else
                    {
                        $aCond[] = 'm.item_id = 0 AND m.privacy IN(%PRIVACY%)';
                    }
                }
                break;
        }

        if ($iSponsor == 1)
        {
            $aCond[] = 'm.is_sponsor != 1';
        }

        if ($sView == 'featured')
        {
            $aCond[] = 'm.is_featured = 1';
        }

        if ($sView != 'my') {
            $aCond[] = 'm.status = 1';
        }

        foreach ($aCond as $iKey => $sCond)
        {
            switch ($sView) {
                case 'friend':
                    $aCond[$iKey] = str_replace('%PRIVACY%', '0,1,2', $sCond);
                    break;
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

        if ($iCategory > 0)
        {
            $aCond[] = 'm.category_id = ' . (int) $iCategory;
        }

        if ($iPlaylistId > 0){
            $aCond[] = 'pd.playlist_id = ' . $iPlaylistId;

        }


        /**
         * @var array
         */
        $aRows = array();
        // Get array of the video.
        $this->database()
                ->select('m.*, u.user_id, u.profile_page_id, u.server_id AS user_server_id, u.user_name, u.full_name, u.gender, u.user_image, u.is_invisible, u.user_group_id, u.language_id, vr.rating AS has_rated, pd.playlist_id')
                ->from(Phpfox::getT('ynultimatevideo_videos'), 'm');

        if (!empty($sTag))
        {
            $this->database()->innerJoin(Phpfox::getT('tag'), 'tag', 'tag.item_id = m.video_id AND tag.category_id = \'' . (defined('PHPFOX_GROUP_VIEW') ? 'video_group' : 'video') . '\' AND tag_text = "' . $sTag . '"');
        }
        switch($sSortBy) {
            case 'rating': 
                $this->database()->order('m.rating DESC');
                break;
            case 'view_count':
                $this->database()->order('m.total_view DESC');
                break;
            case 'total_like':
                $this->database()->order('m.total_like DESC');
                break;
            case 'total_comment':
                $this->database()->order('m.total_comment DESC');
                break;
            case 'is_featured':
                $this->database()->order('m.is_featured DESC');
                break;
            case 'creation_date':
            default:
                $this->database()->order('m.time_stamp DESC');
        }

        /**
         * @var array
         */
        $aRows = $this->database()
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.user_id')
									->leftJoin(Phpfox::getT('ynultimatevideo_ratings'), 'vr', 'vr.video_id = m.video_id AND vr.user_id = ' . Phpfox::getUserId())
                ->leftJoin(Phpfox::getT('ynultimatevideo_playlist_data'), 'pd', 'pd.video_id = m.video_id')
                ->where(implode(' AND ', $aCond))
                ->limit($iPage, $iLimit)
                ->group('m.video_id')
                ->execute('getRows');

        /**
         * @var array
         */
        $aResult = array();

        foreach ($aRows as $aRow)
        {
            $aResult[] = $this->processRow($aRow);
        }

        return $aResult;
    }


    private function getPlaylists($aData)
    {
        if (!Phpfox::getUserParam('ynuv_can_view_video'))
        {
            return array('result' => 0, 'error_code' => 1, 'message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_videos")));
        }

        $sSortBy = isset($aData['sOrder']) ? $aData['sOrder'] : 'creation_date';

        $sAction = (isset($aData['sAction']) && $aData['sAction'] == 'new') ? 'new' : 'more';
        $iLastTimeStamp = isset($aData['iLastTimeStamp']) ? (int) $aData['iLastTimeStamp'] : 0;
        $iLimit = isset($aData['iLimit']) ? (int) $aData['iLimit'] : 10;

        $iPage = isset($aData['iPage']) ? (int) $aData['iPage'] : 1;
        $sView = isset($aData['sView']) ? $aData['sView'] : '';
        $sTag = isset($aData['sTag']) ? $this->_tag($aData['sTag']) : '';
        $iCategory = isset($aData['iCategory']) ? (int) $aData['iCategory'] : 0;
        $iSponsor = isset($aData['iSponsor']) ? (int) $aData['iSponsor'] : 0;
        $sModule = isset($aData['sModule']) ? trim($aData['sModule']) : 'video';
        $iItem = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;
        $sSearch = isset($aData['sSearch']) ? $aData['sSearch'] : '';
        $bIsUserProfile = (isset($aData['bIsUserProfile']) && $aData['bIsUserProfile'] == 'true') ? true : false;

        // Get the callback.
        if ($iItem > 0 && !empty($sModule))
        {
            $aParentModule = array(
                'module_id' => $sModule,
                'item_id' => $iItem
            );
        }
        else
        {
            $aParentModule = false;
        }

        if ($bIsUserProfile)
        {
            /**
             * @var int
             */
            $iProfileId = isset($aData['iProfileId']) ? (int) $aData['iProfileId'] : 0;

            $aUser = Phpfox::getService('user')->get($iProfileId);

            if (!isset($aUser['user_id']))
            {
                return array('result' => 0, 'error_code' => 1, 'message'=>html_entity_decode(Phpfox::getPhrase("mfox.profile_is_not_valid")));
            }
        }
        /**
         * @var array
         */
        $aCond = array();

        // For search.
        if (!empty($sSearch))
        {
            $aCond[] = 'm.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"';
        }

        if ($iLastTimeStamp > 0)
        {
            if ($sAction == 'more')
            {
                $aCond[] = 'm.time_stamp < ' . $iLastTimeStamp;
            }
            else
            {
                $aCond[] = 'm.time_stamp > ' . $iLastTimeStamp;
            }
        }

        switch ($sView) {
            case 'my_playlists':
                $aCond[] = 'm.user_id = ' . Phpfox::getUserId();
                break;
            default:
                
                break;
        }
        $aCond[] = 'm.privacy IN(%PRIVACY%)';

        if ($sView == 'featured')
        {
            $aCond[] = 'm.is_featured = 1';
        }

        foreach ($aCond as $iKey => $sCond)
        {
            switch ($sView) {
                case 'friend':
                    $aCond[$iKey] = str_replace('%PRIVACY%', '0,1,2', $sCond);
                    break;
                case 'my_playlists':
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

        if ($iCategory > 0)
        {
            $aCond[] = 'm.category_id = ' . (int) $iCategory;
        }

        // Get number of the video.
        $this->database()
                ->select('COUNT(*)')
                ->from(Phpfox::getT('ynultimatevideo_playlists'), 'm')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.user_id');
        /**
         * @var int
         */
        $iCount = $this->database()
                ->where(implode(' AND ', $aCond))
                ->limit(1)
                ->execute('getField');
                // ->execute();
                // return $iCount;

        /**
         * @var array
         */
        $aRows = array();
        if ($iCount > 0)
        {
            if($iCount < ($iPage - 1) * $iLimit) {
                return array();
            }

            // Get array of the video.
            $this->database()
                    ->select('m.*, u.user_id, u.profile_page_id, u.server_id AS user_server_id, u.user_name, u.full_name, u.gender, u.user_image, u.is_invisible, u.user_group_id, u.language_id')
                    ->from(Phpfox::getT('ynultimatevideo_playlists'), 'm');

            switch($sSortBy) {
                case 'view_count':
                    $this->database()->order('m.total_view DESC');
                    break;
                case 'total_like':
                    $this->database()->order('m.total_like DESC');
                    break;
                case 'total_comment':
                    $this->database()->order('m.total_comment DESC');
                    break;
                case 'creation_date':
                default:
                    $this->database()->order('m.time_stamp DESC');
            }

            /**
             * @var array
             */
            $aRows = $this->database()
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.user_id')
                    ->where(implode(' AND ', $aCond))
                    ->limit($iPage, $iLimit, $iCount)
                    ->execute('getRows');
        }

        /**
         * @var array
         */
        $aResult = array();

        foreach ($aRows as $aRow)
        {
            $aResult[] = $this->processRowPlaylist($aRow);
        }
        return $aResult;
    }

    public function processRow($aRow)
    {
        $aTemp = array(
            'bIsLiked' => Phpfox::isModule('like') ? Phpfox::getService('like')->didILike('ultimatevideo_video', $aRow['video_id']) : FALSE,
            'bIsRating' => $aRow['has_rated'] == NULL ? FALSE : TRUE,
            'iCategory' => $aRow['category_id'],
            'iDuration' => $aRow['duration'],
            'iRatingCount' => $aRow['total_rating'],
            'iUserLevelId' => $aRow['user_group_id'],
            'sCode' => $aRow['code'],
            'sDescription' => $aRow['description'],
            'sFullTimeStamp' => date('l, F j, o', (int) $aRow['time_stamp']) . ' at ' . date('h:i a', (int) $aRow['time_stamp']),
            'iTimeStamp' => $aRow['time_stamp'],
            'fRating' => (float)$aRow['rating'],
            //----
            'iVideoId' => $aRow['video_id'],
            'bIsFeatured' => $aRow['is_featured'],
            'sModuleId' => $aRow['module_id'],
            'iItemId' => $aRow['item_id'],
            'iPrivacy' => $aRow['privacy'],
            'sTitle' => $aRow['title'],
            'iUserId' => $aRow['user_id'],
            'iParentUserId' => $aRow['parent_user_id'],
            'iTotalComment' => $aRow['total_comment'],
            'iTotalLike' => $aRow['total_like'],
            'sVideoImage' => Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aRow['image_server_id'],
                'path' => 'core.url_pic',
                'file' => $aRow['image_path'],
                'suffix' => '_500',
                'return_url' => true
                    )
            ),
            'sModelType'=>'ultimatevideo_video',
            'iTotalView' => $aRow['total_view'],
            'iProfilePageId' => $aRow['profile_page_id'],
            'sUserImage' => Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aRow['user_server_id'],
                'path' => 'core.url_user',
                'file' => $aRow['user_image'],
                'suffix' => '_50_square',
                'return_url' => true
                    )
            ),
            'sUsername' => $aRow['user_name'],
            'sFullname' => $aRow['full_name'],
            'iGender' => $aRow['gender'],
            'bIsInvisible' => $aRow['is_invisible'],
            'iUserGroupId' => $aRow['user_group_id'],
            'iLanguageId' => isset($aRow['language_id']) ? $aRow['language_id'] : 0, 
            'bCanEdit' => $this->canEdit($aRow),
            'bCanDelete' => $this->canDelete($aRow),
            'bIsWatchLater' => Phpfox::getService('ultimatevideo.watchlater')->isWatchLater(Phpfox::getUserId(), $aRow['video_id']),
            'bIsFavorite' => Phpfox::getService('ultimatevideo.favorite')->isFavorite(Phpfox::getUserId(), $aRow['video_id']),
        );
        if(!$aTemp['sVideoImage'] || !$aRow['image_path']){
            $corePath = Phpfox::getParam('core.path_actual').'PF.Site/Apps/YouNet_UltimateVideos';
            $aTemp['sVideoImage'] = $corePath.'/assets/image/noimg_video.jpg';
        }
        return $aTemp;

        // return Phpfox::getService('mfox.helper.video')->retrieveMoreInfo($aRow, $aTemp);
    }

    public function processRowPlaylist($aRow)
    {
        $aTemp = array(
            'bIsLiked' => Phpfox::isModule('like') ? Phpfox::getService('like')->didILike('ultimatevideo_playlist', $aRow['video_id']) : FALSE,
            'iCategory' => $aRow['category_id'],
            'iUserLevelId' => $aRow['user_group_id'],
            'sDescription' => $aRow['description'],
            'sFullTimeStamp' => date('l, F j, o', (int) $aRow['time_stamp']) . ' at ' . date('h:i a', (int) $aRow['time_stamp']),
            'iTimeStamp' => $aRow['time_stamp'],
            //----
            'iPlaylistId' => $aRow['playlist_id'],
            'bIsFeatured' => $aRow['is_featured'],
            'sModuleId' => $aRow['module_id'],
            'iPrivacy' => $aRow['privacy'],
            'sTitle' => $aRow['title'],
            'iUserId' => $aRow['user_id'],
            'iParentUserId' => $aRow['parent_user_id'],
            'iTotalComment' => $aRow['total_comment'],
            'iTotalLike' => $aRow['total_like'],
            'sImage' => Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aRow['image_server_id'],
                'path' => 'core.url_pic',
                'file' => $aRow['image_path'],
                'suffix' => '_500',
                'return_url' => true
                    )
            ),
            'sModelType'=>'ultimatevideo_playlist',
            'iTotalView' => $aRow['total_view'],
            'iProfilePageId' => $aRow['profile_page_id'],
            'sUserImage' => Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aRow['user_server_id'],
                'path' => 'core.url_user',
                'file' => $aRow['user_image'],
                'suffix' => '_50_square',
                'return_url' => true
                    )
            ),
            'sUsername' => $aRow['user_name'],
            'sFullname' => $aRow['full_name'],
            'iGender' => $aRow['gender'],
            'bIsInvisible' => $aRow['is_invisible'],
            'iUserGroupId' => $aRow['user_group_id'],
            'iLanguageId' => isset($aRow['language_id']) ? $aRow['language_id'] : 0, 
            'bCanEdit' => $this->canEditPlaylist($aRow),
            'bCanDelete' => $this->canDeletePlaylist($aRow),
            'iTotalVideo' => $aRow['total_video'],
            'aVideoList' => Phpfox::getService('ultimatevideo.playlist.browse')->getSomeVideoOfPlaylist($aRow['playlist_id']),
        );
        if(!$aTemp['sImage'] || !$aRow['image_path']){
            $corePath = Phpfox::getParam('core.path_actual').'PF.Site/Apps/YouNet_UltimateVideos';
            $aTemp['sImage'] = $corePath.'/assets/image/noimg_playlist.jpg';
        }
        return $aTemp;

        // return Phpfox::getService('mfox.helper.video')->retrieveMoreInfo($aRow, $aTemp);
    }

    public function canEdit($aItem){
        if($aItem['user_id'] == Phpfox::getUserId())
            return Phpfox::getUserParam('ynuv_can_edit_own_video');
        return Phpfox::getUserParam('ynuv_can_edit_video_of_other_user');
    }

    public function canDelete($aItem){
        if($aItem['user_id'] == Phpfox::getUserId())
            return Phpfox::getUserParam('ynuv_can_delete_own_video');
        return Phpfox::getUserParam('ynuv_can_delete_video_of_other_user');
    }

    public function canEditPlaylist($aItem){
        if($aItem['user_id'] == Phpfox::getUserId())
            return Phpfox::getUserParam('ynuv_can_edit_own_playlists');
        return Phpfox::getUserParam('ynuv_can_edit_playlist_of_other_user');
    }

    public function canDeletePlaylist($aItem){
        if($aItem['user_id'] == Phpfox::getUserId())
            return Phpfox::getUserParam('ynuv_can_delete_own_playlists');
        return Phpfox::getUserParam('ynuv_can_delete_playlist_of_other_user');
    }

    /**
     * 
     * @param string $sTag
     * @return string
     */
    private function _tag($sTag)
    {
        /**
         * @var array
         */
        $aTag = Phpfox::getService('tag')->getTagInfo('video', $sTag);

        if (!empty($aTag['tag_text']))
        {
            return $aTag['tag_text'];
        }

        return '';
    }

    /**
     * Input data:
     * + sAction: string, optional, ex: "more" or "new".
     * + iLastTimeStamp: int, optional.
     * + iLimit: int, optional.
     * + sView: string, optional. Ex: "friend", "my" and "all".
     * + sTag: string, optional.
     * + iCategory: int, optional.
     * + iSponsor: int, optional.
     * + sModule: string, optional.
     * + iItem: int, optional.
     * + sSearch: string, optional.
     * + bIsUserProfile: string, optional, ex: "true" or "false".
     * + iProfileId: int, optional.
     * 
     * Output data:
     * + iVideoId: int.
     * + bInProcess: bool.
     * + bIsStream: bool.
     * + bIsFeatured: bool.
     * + bIsSpotlight: bool.
     * + bIsSponsor: bool.
     * + iViewId: bool.
     * + sModuleId: string.
     * + iItemId: int.
     * + iPrivacy: int.
     * + iPrivacyComment: int.
     * + sTitle: string.
     * + iUserId: int.
     * + iParentUserId: int.
     * + sDestination: string.
     * + sFileExt: string.
     * + sDuration: string.
     * + sResolutionX: string.
     * + sResolutionY: string.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + iTotalDislike: int.
     * + sVideoImage: string.
     * + iTotalScore: int.
     * + iTotalRating: int.
     * + iTimeStamp: int.
     * + sTimeStamp: string.
     * + iTotalView: int.
     * + bIsViewed: bool.
     * + iProfilePageId: int.
     * + sUserImage: string.
     * + sUsername: string.
     * + sFullname: string.
     * + iGender: int.
     * + bIsInvisible: bool.
     * + iUserGroupId: int.
     * + iLanguageId: int.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see video/filter
     * 
     * @param array $aData
     * @return array
     */
    public function filter($aData)
    {
        return $this->getVideos($aData);
    }

    
    /**
     * Using in notification.
     * @param int $iId
     * @param int $iChild
     * @return boolean|array
     */
    public function doVideoGetFeedRedirect($iId, $iChild = 0)
    {
        /**
         * @var array
         */
        $aRow = $this->database()
                ->select('m.video_id, m.title')
                ->from(Phpfox::getT('video'), 'm')
                ->where('m.video_id = ' . (int) $iId)
                ->execute('getSlaveRow');

        if (!isset($aRow['video_id']))
        {
            return false;
        }

        return array(
            'sModule' => 'video',
            'iVideoId' => $aRow['video_id'],
            'sTitle' => $aRow['title'],
            'sCommentType' => 'video'
        );
    }

    /**
     * Using in notification comment.
     * @param int $iId
     * @return boolean|array
     */
    public function doVideoGetRedirectComment($iId)
    {
        return $this->doVideoGetFeedRedirect($iId);
    }   

    public function upload($aData)
    {
        if (!Phpfox::isUser())
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_it"))
            );            
        }

        if (!Phpfox::getParam('video.allow_video_uploading'))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_it"))
            );            
        }
        
        if (!Phpfox::getUserParam('video.can_upload_videos'))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_it"))
            );            
        }

        $sContent = isset($aData['sContent']) ? $aData['sContent'] : '';
        $iSubjectId = isset($aData['iSubjectId']) ? (int) $aData['iSubjectId'] : 0;
        $sSubjectType = isset($aData['sSubjectType']) ? $aData['sSubjectType'] : 'user';
        $iPrivacy = isset($aData['iPrivacy']) ? (int) $aData['iPrivacy'] : 0;
        $bMassUploader = false;
        if (isset($_FILES['Filedata']) && !isset($_FILES['video']))
        {
            $_FILES['video'] = $_FILES['Filedata'];
        }

        $bIsInline = false;
        $aVals = array(
            'title' => isset($aData['sTitle']) ? $aData['sTitle'] : '', 
            'category' => isset($aData['iCategoryId']) ? array($aData['iCategoryId']) : array(''), 
            'text' => isset($aData['sDescription']) ? $aData['sDescription'] : '', 
            'tag_list' => '', 
            'privacy' => isset($aData['iPrivacy']) ? $aData['iPrivacy'] : 0, 
            'privacy_comment' => isset($aData['iPrivacyComment']) ? $aData['iPrivacyComment'] : 0, 
        );
        if($iSubjectId > 0){
            $aVals = array();
            $aVals['video_inline'] = 1;
            $bIsInline = true;
            // upload inline homepage/profile/pages
            if($sSubjectType == 'pages' 
                || $sSubjectType == 'event'
                || $sSubjectType == 'fevent'
                ){
                // in pages
                $aVals = array_merge($aVals, array(
                    'callback_item_id' => $iSubjectId, 
                    'callback_module' => $sSubjectType, 
                    'parent_user_id' => $iSubjectId, 
                    'group_id' => $iSubjectId, 
                    'video_title' => isset($aData['sTitle']) ? $aData['sTitle'] : '', 
                    'status_info' => $sContent, 
                    'iframe' => 1, 
                ));
            } else if($sSubjectType == 'user' && $iSubjectId != Phpfox::getUserId()){
                // in other profile
                $aVals = array_merge($aVals, array(
                    'parent_user_id' => $iSubjectId, 
                    'group_id' => '', 
                    'video_title' => isset($aData['sTitle']) ? $aData['sTitle'] : '', 
                    'status_info' => $sContent, 
                    'iframe' => 1, 
                ));
            } else {
                // in homepage or logining profile
                $aVals = array_merge($aVals, array(
                    'group_id' => '', 
                    'video_title' => isset($aData['sTitle']) ? $aData['sTitle'] : '', 
                    'status_info' => $sContent, 
                    'iframe' => 1, 
                    'privacy' => isset($aData['iPrivacy']) ? $aData['iPrivacy'] : 0, 
                ));
            }
        }

        if (!isset($_FILES['video']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('video.upload_failed_file_is_too_large')
            );            
        }

        if (($iFlood = Phpfox::getUserParam('video.flood_control_videos')) !== 0)
        {
            $aFlood = array(
                'action' => 'last_post', // The SPAM action
                'params' => array(
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('video'), // Database table we plan to check
                    'condition' => 'view_id = 0 AND user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);   
                )
            );
                                        
            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood))
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getPhrase('video.you_are_uploading_a_video_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime()
                );            
            }
        }                   

        if (!Phpfox_Error::isPassed())
        {
            if (!empty($_FILES['video']['tmp_name']))
            {
                Phpfox::getService('video.process')->delete();
            }
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get())
            );            
        }

        if ($iId = Phpfox::getService('video.process')->add($aVals))
        {
            // rotate video 
            $aVideo = Phpfox::getService('video')->getForEdit($iId, $bForce = true);
            Phpfox::getService('mfox.helper')->correctOrientationVideo($aVideo);
            
            if (Phpfox::getParam('video.vidly_support'))
            {
                $aVideo = Phpfox::getService('video')->getVideo($iId, true);

                Phpfox::getLib('cdn')->put(Phpfox::getParam('video.dir') . sprintf($aVideo['destination'], ''));

                Phpfox::getLib('database')->insert(Phpfox::getT('vidly_url'), array(
                        'video_id' => $aVideo['video_id'],
                        'video_url' => rtrim(Phpfox::getParam('core.rackspace_url'), '/') . '/file/video/' . sprintf($aVideo['destination'], ''),
                        'upload_video_id' => '0'
                    )
                );              
                
                $mReturn = Phpfox::getService('video')->vidlyPost('AddMedia', array('Source' => array(
                            'SourceFile' => rtrim(Phpfox::getParam('core.rackspace_url'), '/') . '/file/video/' . sprintf($aVideo['destination'], ''),
                            'CDN' => 'RS'
                        )
                    ), 'vidid_' . $aVideo['video_id'] . '/'
                );

                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'iVideoId' => $iId,
                    'message'=>html_entity_decode(Phpfox::getPhrase("mfox.upload_successfully"))
                );            
            } else {
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'iVideoId' => $iId,
                    'message' =>  Phpfox::getPhrase('video.your_video_has_successfully_been_uploaded_please_standby_while_we_convert_your_video')
                );            
            }
        } else {
            if (!empty($_FILES['video']['tmp_name']))
            {
                Phpfox::getService('video.process')->delete($this->request()->get('video_id'));
            }
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get())
            );            
        }
    }

    public function convert($aData){
        $iVideoId = isset($aData['iVideoId']) ? (int) $aData['iVideoId'] : 0;
        $iInline = isset($aData['iInline']) ? (int) $aData['iInline'] : 0;
        $attachment_id = $iVideoId;
        $full = false;
        $inline = false;
        if($iInline > 0){
            $inline = true;
        }

        if (Phpfox::getService('video.convert')->process($attachment_id, false))
        {
            if ($full)
            {
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'message'=>html_entity_decode(Phpfox::getPhrase("mfox.successfully"))
                );            
            }
            elseif ($inline)
            {
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'message'=>html_entity_decode(Phpfox::getPhrase("mfox.successfully"))
                );            
            }
            else {
                $aVideo = Phpfox::getService('video.convert')->getDetails();        
                Phpfox::getService('attachment.process')->update(array(
                        'destination' => $aVideo['destination'],
                        'extension' => $aVideo['extension'],
                        'is_video' => '1',
                        'video_duration' => $aVideo['duration']
                    ), $attachment_id
                );                      
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'message'=>html_entity_decode(Phpfox::getPhrase("mfox.successfully"))
                );            
            }
        } else {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' => implode(' ',Phpfox_Error::get())
            );            
        }
    }

    /**
     * Delete image only.
     * 
     * Input data:
     * + iVideoId: int, required.
     * 
     * Output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see video/deleteImage
     * 
     * @param array $aData
     * @return array
     */
    public function deleteImage($aData)
    {
        /**
         * @var int
         */
        $iVideoId = isset($aData['iVideoId']) ? (int) $aData['iVideoId'] : 0;

        if (Phpfox::getService('video.process')->deleteImage($iVideoId))
        {
            return array(
                'result' => 1,
                'error_code' => 0,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.delete_image_successfully"))
            );
        }

        return array(
            'result' => 0,
            'error_code' => 1,
            'message' => implode(' ',Phpfox_Error::get())
        );
    }

    /**
     * Input data:
     * + iVideoId: int, required.
     * 
     * Output data:
     * + bVideoIsViewed: bool.
     * + bIsFriend: bool.
     * + bIsLiked: bool.
     * + iVideoId: int.
     * + iCategoryId: int.
     * + bInProcess: bool.
     * + bIsStream: bool.
     * + bIsFeatured: bool.
     * + bIsSpotlight: bool.
     * + bIsSponsor: bool.
     * + iViewId: bool.
     * + sModuleId: string.
     * + iItemId: int.
     * + iPrivacy: int.
     * + iPrivacyComment: int.
     * + sTitle: string.
     * + iUserId: int.
     * + iParentUserId: int.
     * + sDestination: string.
     * + sFileExt: string.
     * + sDuration: string.
     * + sResolutionX: string.
     * + sResolutionY: string.
     * + iTotalComment: int.
     * + iTotalLike: int.
     * + iTotalDislike: int.
     * + sVideoImage: string.
     * + iTotalScore: int.
     * + iTotalRating: int.
     * + iTimeStamp: int.
     * + sTimeStamp: string.
     * + iTotalView: int.
     * + bIsViewed: bool.
     * + iProfilePageId: int.
     * + sUserImage: string.
     * + sUsername: string.
     * + sFullname: string.
     * + iGender: int.
     * + bIsInvisible: bool.
     * + iUserGroupId: int.
     * + iLanguageId: int.
     * + sYoutubeVideoUrl: string.
     * + sEmbed: string.
     * + iTotalUserVideos: int.
     * 
     * @var Mobile - API phpFox/Api V3.0
     * @var video/details
     * 
     * @see Phpfox_Parse_Format
     * 
     * @param array $aData
     * @return array
     */
    public function detail($aData)
    {
        Phpfox::getLib('setting')->setParam('core.allow_html', false);
                $bCanView = true;

        /**
         * @var int
         */
        $iVideoId = isset($aData['iVideoId']) ? (int) $aData['iVideoId'] : 0;
        /**
         * @var string
         */
        $sModule = isset($aData['sModule']) ? $aData['sModule'] : '';
        /**
         * @var int
         */
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;
        // Get the video by id.
        $aVideo = Phpfox::getService('ultimatevideo')->getVideo($iVideoId);
        $sError =  null;
        if (Phpfox::isModule('privacy'))
        {
            Privacy_Service_Privacy::instance()->check('ultimatevideo', $aVideo['video_id'], $aVideo['user_id'], $aVideo['privacy'], $aVideo['is_friend']);
        }
        if(!user('ynuv_can_view_video'))
        {
            $sError = _p('You don\'t have permission to view this video.');
        }
        if(!$aVideo)
        {
            $sError = _p('The video you are looking for does not exist or has been removed');
        }
        elseif ($aVideo['status'] == 0){
            $sError = _p('The video you are looking for does not exist or has not been processed yet.');
        }
        elseif($aVideo['status'] != 1 && $aVideo['status'] != 0 && $aVideo['status'] != 2)
        {
            $sError = _p('The video you are looking for was failed to upload.');
        }

        if ($sError != null)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => $sError);
        }


        // Check privacy for video.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('videochannel', $aVideo['song_id'], $aVideo['user_id'], $aVideo['privacy'], $aVideo['is_friend'], true))
        {
            $bCanView = false;
        }

        if (Phpfox::isUser() && Phpfox::isModule('notification'))
        {
            Phpfox::getService('notification.process')->delete('comment_video', $iVideoId, Phpfox::getUserId());
            Phpfox::getService('notification.process')->delete('video_like', $iVideoId, Phpfox::getUserId());
        }

        if (Phpfox::getUserId() == $aVideo['user_id'] && Phpfox::isModule('notification'))
        {
            Phpfox::getService('notification.process')->delete('video_approved', $iVideoId, Phpfox::getUserId());
        }

        $sVideoPath = '';
        $sVideoPath = (preg_match("/\{file\/videos\/(.*)\/(.*)\.flv\}/i", $aVideo['destination'], $aMatches) ? Phpfox::getParam('core.path') . str_replace(array('{', '}'), '', $aMatches[0]) : Phpfox::getParam('video.url') . $aVideo['destination']);
        if (Phpfox::getParam('core.allow_cdn') && !empty($aVideo['server_id']))
        {
            $sTempVideoPath = Phpfox::getLib('cdn')->getUrl($sVideoPath, $aVideo['server_id']);
            if (!empty($sTempVideoPath))
            {
                $sVideoPath = $sTempVideoPath;
            }
        }

        $sVideoPath = '';

        if (!$aVideo['is_stream'])
        {
            $sVideoPath = (preg_match("/\{file\/videos\/(.*)\/(.*)\.flv\}/i", $aVideo['destination'], $aMatches) ? Phpfox::getParam('core.path') . str_replace(array('{', '}'), '', $aMatches[0]) : Phpfox::getParam('ultimatevideo.url') . $aVideo['destination']);
            if (Phpfox::getParam('core.allow_cdn') && !empty($aVideo['server_id']))
            {
                $sTempVideoPath = Phpfox::getLib('cdn')->getUrl($sVideoPath, $aVideo['server_id']);
                if (!empty($sTempVideoPath))
                {
                    $sVideoPath = $sTempVideoPath;
                }
            }
        }
        $sVideoPath = str_replace('.flv', '.mp4', $sVideoPath);
        $aCategories = $this->database()
                ->select('pc.parent_id AS iParentId, pc.category_id AS iCategoryId, pc.name AS sName')
                ->from(Phpfox::getT('channel_category_data'), 'pcd')
                ->join(Phpfox::getT('channel_category'), 'pc', 'pc.category_id = pcd.category_id')
                ->where('pcd.video_id = ' . (int) $aVideo['video_id'])
                ->order('pc.parent_id ASC, pc.ordering ASC')
                ->execute('getSlaveRows');

        $iCategoryId = 0;
        foreach ($aCategories as $aCategory)
        {
            if ($iCategoryId <= $aCategory['iParentId'])
            {
                $iCategoryId = $aCategory['iCategoryId'];
            }
        }
        // return $aVideo;

        $bCanComment =        Phpfox::getService('mfox.comment')->checkCanPostCommentOnItem($aVideo);
        // return $aVideo;   

        $aRow = array(
                    
            'bCanView' => $bCanView,
            'sCommentPrivacy' => Phpfox::getService('privacy')->getPhrase($aVideo['privacy_comment']),
            'sViewPrivacy' => Phpfox::getService('privacy')->getPhrase($aVideo['privacy']),
            'sTags' => $this->getVideoTagString($aVideo['video_id'], 'ynultimatevideo'),
            'sType' => $aVideo['is_stream'] == 1 ? '' : 'file',
            'sVideoUrl' => $sVideoPath,
                    //-----
            'bVideoIsViewed' => $aVideo['video_is_viewed'],
            'bIsFriend' => $aVideo['is_friend'],
            'iVideoId' => $aVideo['video_id'],
            'iCategoryId' => $iCategoryId,
            'bInProcess' => $aVideo['in_process'],
            'bIsStream' => $aVideo['is_stream'],
            'bIsFeatured' => $aVideo['is_featured'],
            'bIsSpotlight' => $aVideo['is_spotlight'],
            'bIsSponsor' => $aVideo['is_sponsor'],
            'iViewId' => $aVideo['view_id'],
            'sModuleId' => $aVideo['module_id'],
            'iItemId' => $aVideo['item_id'],
            'iPrivacy' => $aVideo['privacy'],
            'iPrivacyComment' => $aVideo['privacy_comment'],
            'sTitle' => Phpfox::getLib('parse.output')->parse($aVideo['title']),
            'iUserId' => $aVideo['user_id'],
            'iParentUserId' => $aVideo['parent_user_id'],
            'sDestination' => $sVideoPath,
            'sFileExt' => $aVideo['file_ext'],
            'sDuration' => $aVideo['duration'],
            'sResolutionX' => $aVideo['resolution_x'],
            'sResolutionY' => $aVideo['resolution_y'],
            'iTotalComment' => $aVideo['total_comment'],
            'iTotalLike' => $aVideo['total_like'],
            // 'iTotalDislike' => $aVideo['total_dislike'],
            'sVideoImage' => Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aVideo['image_server_id'],
                'path' => 'core.url_pic',
                'file' => $aVideo['image_path'],
                'suffix' => '_120',
                'return_url' => true
                    )
            ),
            'iTotalScore' => (float)($aVideo['rating']),   
            'iTotalRating' => $aVideo['total_rating'],
            'iTimeStamp' => $aVideo['time_stamp'],
            'sTimeStamp' => date('l, F j, o', (int) $aVideo['time_stamp']) . ' at ' . date('h:i a', (int) $aVideo['time_stamp']),
            'iTotalView' => $aVideo['total_view'],
            'bIsViewed' => $aVideo['is_viewed'],
            'sText' => Phpfox::getLib('parse.output')->parse($aVideo['text']),
            'iProfilePageId' => $aVideo['profile_page_id'],
            'sUserImage' => Phpfox::getService('mfox.user')->getImageUrl($aVideo, '_50_square'),
            'sUsername' => $aVideo['user_name'],
            'sFullname' => $aVideo['full_name'],
            'iGender' => $aVideo['gender'],
            'bIsInvisible' => $aVideo['is_invisible'],
            'iUserGroupId' => $aVideo['user_group_id'],
            'iLanguageId' => isset($aVideo['language_id']) ? $aVideo['language_id'] : 0,
            'sYoutubeVideoUrl' => $aVideo['youtube_video_url'],
            'sEmbed' => $aVideo['embed_code'],
            'iTotalUserVideos' => $aVideo['total_user_videos'],
            'sVideoWebLink' => Phpfox::getLib('url')->permalink('mobile.videochannel', $aVideo['video_id'], $aVideo['title']),
            'sTopic' => $sTags,
            'sModelType'=>'ultimatevideo_video',
            'bCanPostComment' => $bCanComment,  // deprecated.
            'bCanComment' => $bCanComment,
            'bCanEdit' => $this->canEdit($aVideo),
            'bCanDelete' => $this->canDelete($aVideo),
        );

        $aReturn = Phpfox::getService('mfox.helper.ultimatevideo')->retrieveMoreInfo($aVideo, $aRow);
        Phpfox::getService('ultimatevideo.history')->addVideo(Phpfox::getUserId(), $iVideoId);

        return $aReturn;
    }

    public function getVideoTagString($iVideoId, $type)
    {
        return implode(', ', array_map(function($temp){
            return $temp['tag_text'];
            }, $this->database()
                ->select('tag_text')
                ->from(Phpfox::getT('tag'))
                ->where(strtr("category_id=':type' AND item_id=:id",[
                    ':type'=>$type,
                    ':id'=> intval($iVideoId)
                ]))
                ->execute('getSlaveRows')
        ));
        
    }

    public function detailPlaylist($aData){
        if(isset($aData['iPlaylistId']) && ($iId = $aData['iPlaylistId'])){
            $aPlaylist = Phpfox::getService('ultimatevideo.playlist')->getForEdit($iId);
            $aUser = Phpfox::getService('user')->getUser($aPlaylist['user_id']);
            if(Phpfox::isModule('like')) {
                list($iCnt, $aLikedUsers) = Phpfox::getService('like')->getForMembers('ultimatevideo_playlist', $aPlaylist['playlist_id']);
                $aLikedUsersReturn = array();

                foreach ($aLikedUsers as $aUser) {
                    $aLikedUsersReturn[] = array(
                        'iUserId' => $aUser['user_id'],
                        'sDisplayName' => $aUser['full_name']
                    );
                }
            } else {
                $aLikedUsersReturn = array();
            }

            $aResult = [
                'aUserLike' => $aLikedUsersReturn,
                'aCategories' => $aPlaylist['categories'],
                'iCategoryId' => $aPlaylist['category_id'],
                'sDescription' => $aPlaylist['description'],
                'sImage' => Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aPlaylist['image_server_id'],
                    'path' => 'core.url_pic',
                    'file' => $aPlaylist['image_path'],
                    'suffix' => '_250',
                    'return_url' => true
                )),
                'bIsApproved' => $aPlaylist['is_approved'],
                'bIsFeatured' => $aPlaylist['is_featured'],
                'iPlaylistId' => $aPlaylist['playlist_id'],
                'iPrivacy' => $aPlaylist['privacy'],
                'iTimestamp' => $aPlaylist['time_stamp'],
                'sTitle' => $aPlaylist['title'],
                'iTotalComment' => $aPlaylist['total_comment'],
                'iTotalLike' => $aPlaylist['total_like'],
                'iTotalView' => $aPlaylist['total_view'],
                'iUserId' => $aPlaylist['user_id'],
                'sFullname' => $aUser['full_name'],
                'sUserImage' => Phpfox::getService('mfox.user')->getImageUrl($aPlaylist, '_50_square'),
                'bIsLiked' => Phpfox::isModule('like') ? Phpfox::getService('like')->didILike('ultimatevideo_playlist', $aPlaylist['playlist_id']) : FALSE,
                'bShowRate' => false,
                'iTotalVideo' => $aPlaylist['total_video'],
                'bCanDelete' => $this->canDeletePlaylist($aPlaylist),
                'bCanEdit' => $this->canEditPlaylist($aPlaylist),

            ];
            Phpfox::getService('ultimatevideo.history')->addPlaylist(Phpfox::getUserId(), $iId);
            return $aResult;
        }
        return 'The playlist you are looking for does not exist or has been removed';
    }


    /**
     * form add
     */
    public function formadd($aData){
        $response  =  array(
        	'bIsAdvancedModule'=> 0,
            'view_options'=> Phpfox::getService('mfox.privacy')->privacy($aData),
            'category_options'=> $this->categories($aData),
            'bCanUploadVideo'=> user('ynuv_can_upload_video')
        );

        $iValue =  Phpfox::getService('user.privacy')->getValue('video.display_on_profile');
        $response['default_privacy_setting'] = Phpfox::getService('mfox.privacy')->defaultprivacy("$iValue", $response['view_options']);
        $aError = $this->canCreateVideo();
        $response['bCanAddVideo'] = ($aError['error_code'] ? 0 : 1);
        $response['message'] = ($aError['error_code'] ? $aError['error_message'] : $aError['message']);
        return $response;
    }

    public function formEditPlaylist($aData){
        $aItem = Phpfox::getService('ultimatevideo.playlist')->getForEdit($aData['iPlaylistId']);
        $aTemp = [
            'sTitle' => $aItem['title'],
            'sDescription' => $aItem['description'],
            'iCategoryId' => $aItem['category_id'],
            'iPrivacy' => $aItem['privacy'],
        ];
        $aForm = $this->formadd(array());
        return array_merge($aTemp, $aForm);
    }
    public function formEditVideo($aData){
        $aItem = Phpfox::getService('ultimatevideo')->getVideoForEdit($aData['iVideoId']);
        if (Phpfox::isModule('tag'))
        {
            $aItem['tag_list'] = '';                    

            $aTags = Phpfox::getService('tag')->getTagsById('ynultimatevideo', $aItem['video_id']);

            if (isset($aTags[$aItem['video_id']]))
            {
                foreach ($aTags[$aItem['video_id']] as $aTag)
                {
                    $aItem['tag_list'] .= ' ' . $aTag['tag_text'] . ',';    
                }
                $aItem['tag_list'] = trim(trim($aItem['tag_list'], ','));
            }
        }
        $aTemp = [
            'sTitle' => $aItem['title'],
            'sDescription' => $aItem['description'],
            'iCategoryId' => $aItem['category_id'],
            'iPrivacy' => $aItem['privacy'],
            'sTags' => $aItem['tag_list'],
            'iVideoId' => $aItem['video_id'],
            'bAllowUploadChannel' => $aItem['allow_upload_channel'],
            'iType' => $aItem['type']
        ];
        $aForm = $this->formadd(array());
        return array_merge($aTemp, $aForm);
    }

    public function getTextByVideoId($iVideoId){
        return $this->database()->select('vt.*')
            ->from(Phpfox::getT('video_text'), 'vt')
            ->where('vt.video_id = ' . (int) $iVideoId)
            ->execute('getSlaveRow');
    }

    

    public function deletePlaylist($aData){
        $iPlaylistId = isset($aData['iPlaylistId']) ? (int) $aData['iPlaylistId'] : 0;
        $sModule = isset($aData['sModule']) ? trim($aData['sModule']) : '';
        // Get the playlist.
        if (!($aPlaylist = Phpfox::getService('ultimatevideo.playlist')->getPlaylistById($iPlaylistId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  _p('The playlist can not be found'));
        }
        if(!$this->canDeletePlaylist($aPlaylist)){
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  _p('You do not have permission to delete this playlist'));   
        }
        // Delete playlist.
        if (Phpfox::getService('ultimatevideo.playlist.process')->delete($iPlaylistId))
        {
            return array('result' => 1, 'error_code' => 0, 'message' => _p('The playlist has been deleted successfully'));
        }
        return array('result' => 0, 'error_code' => 1, 'message' => Phpfox_Error::get());
    }

    public function getAllPlaylistOfUser($aData){
        $aPlaylists = Phpfox::getService('ultimatevideo.playlist')->getAllPlaylistOfUser($aData['iVideoId']);
        $aTemp = [];
        foreach($aPlaylists as $key=>$aPlaylist){
            $aTemp[$key] = [
                'bIsAdded' => ($aPlaylist['added_video'] ? true : false),
                'iCategoryId' => $aPlaylist['category_id'],
                'sDescription' => $aPlaylist['description'],
                'sTitle' => $aPlaylist['title'],
                'iPlaylistId' => $aPlaylist['playlist_id']
            ];
        }
        $bIsFavorite = Phpfox::getService('ultimatevideo.favorite')->isFavorite(Phpfox::getUserId(), $aData['iVideoId']);
        return array('aPlaylists' => $aTemp, 'bIsFavorite' => $bIsFavorite);
    }

    public function addVideoToPlaylist($aData){
        if(Phpfox::getService('ultimatevideo.playlist.process')->addVideo($aData['iVideoId'], $aData['iPlaylistId'])){
            return array('result' => 0, 'error_code' => 0, 'message' => 'Video has been added to playlist');
        }
        return array('result' => 0, 'error_code' => 1, 'error_message' => 'Can not add video to playlist');
    }
    public function removeVideoFromPlaylist($aData){
        if(Phpfox::getService('ultimatevideo.playlist.process')->removeVideo($aData['iVideoId'], $aData['iPlaylistId'])){
            return array('result' => 0, 'error_code' => 0, 'message' => 'Video has been removed from playlist');
        }
        return array('result' => 0, 'error_code' => 1, 'error_message' => 'Can not remove video from playlist');
    }

    public function addPlaylistOnAction($aData) {
        $iVideoId = $aData['iVideoId'];
        $aVals = array(
            'title' => $aData['sTitle']
        );

        if(!user('ynuv_can_add_playlist',0))
        {
            $message = _p('You don\'t have permission to add new playlist');
            return array('result' => 0, 'error_code' => 1, 'error_message' => $message);
        }
        if($iPlaylistId = Phpfox::getService('ultimatevideo.playlist.process')->add($aVals, true, $iVideoId))
        {
            $aPlaylist = Phpfox::getService('ultimatevideo.playlist')->getPlaylistById($iPlaylistId);
            $result = Phpfox::getService('ultimatevideo.playlist.process')->addVideo($iVideoId,$iPlaylistId);
            if($result){
                $message = _p('New playlist has just been created successfully');
                return array('result' => 0, 'error_code' => 0, 'message' => $message, 'iPlaylistId' => $iPlaylistId);
            }
            else{
                $message = _p('Can not add videos to new playlist.');
                return array('result' => 0, 'error_code' => 1, 'error_message' => $message);
            }
        }
        $message = _p('Can not create new playlist.');
        return array('result' => 0, 'error_code' => 1, 'error_message' => $message);
    }

    public function editVideo($aData){
        $iVideoId = isset($aData['iVideoId']) ? (int) $aData['iVideoId'] : 0;
        
        if($aVideo = Phpfox::getService('ultimatevideo')->getVideo($iVideoId)){
            if(!$this->canEdit($aVideo))
                return array('result' => 0, 'error_code' => 1, 'error_message' => _p('You do not have permission to edit this video'));
            $aVals = [
                'title' => (empty($aData['sTitle'])) ? "" : $aData['sTitle'],
                'privacy' => (int) (isset($aData['iPrivacy']) ? (int) $aData['iPrivacy'] : 0),
                'category' => [$aData['iCategoryId']],
                'description' => (empty($aData['sDescription'])) ? "" : $aData['sDescription'],
                'tag_list' => (empty($aData['sTags'])) ? "" : $aData['sTags'],
            ];
            if(isset($aData['bAllowUploadChannel']) && $aData['bAllowUploadChannel'])
                $aVals['allow_upload_channel'] = $aData['bAllowUploadChannel'];

            if(Phpfox::getService('ultimatevideo.process')->update($aVals, $iVideoId)){
                return array('result' => 0, 'error_code' => 0, 'message' => _p('This video has been updated successfully.'));
            }
        }
        return array('result' => 0, 'error_code' => 1, 'error_message' => _p('Unable to update this video'));
    }

    public function getHistory($aData){
        $sSortBy = isset($aData['sOrder']) ? $aData['sOrder'] : 'creation_date';
        $iLimit = isset($aData['iLimit']) ? (int) $aData['iLimit'] : 10;
        $iPage = isset($aData['iPage']) ? (int) $aData['iPage'] : 1;
        $iCategoryId = isset($aData['iCategory']) ? (int) $aData['iCategory'] : 0;
        $sSearch = isset($aData['sSearch']) ? $aData['sSearch'] : '';

        $aCond = [];
        $aCond[] = "h.user_id = " . Phpfox::getUserId();
        if($sSearch){
            $aCond[] = "m.title LIKE '" . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . "'";
        }
        if($iCategoryId){
            $aCond[] = "m.category_id = " . $iCategoryId;
        }

        $sCond = implode(' AND ', $aCond);

        $aPlaylists = $this->database()
                            ->select('*')
                            ->from(Phpfox::getT('ynultimatevideo_history'), 'h')
                            ->join(Phpfox::getT('ynultimatevideo_playlists'), 'm', 'm.playlist_id = h.item_id')
                            ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.user_id')                            
                            ->where("h.item_type = 1 AND " . $sCond)
                            ->limit($iPage, $iLimit)
                            ->execute('getRows');
        $aVideos = $this->database()
                            ->select('*')
                            ->from(Phpfox::getT('ynultimatevideo_history'), 'h')
                            ->join(Phpfox::getT('ynultimatevideo_videos'), 'm', 'm.video_id = h.item_id')
                            ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.user_id')
                            ->where("h.item_type = 0 AND " . $sCond)
                            ->limit($iPage, $iLimit)
                            ->execute('getRows');
        $aRows = array_merge($aPlaylists, $aVideos);
        $aRowsProcessed = [];
        foreach($aRows as $key=>$aRow){
            $aRowsProcessed[] = $this->processRowHistory($aRow);
        }

        // Sort
        $sOrder = (isset($aData['sOrder']))?$aData['sOrder']:'latest';
        switch ($sOrder) {
            case 'total_like':
                $orderby = 'iTotalLike';
                break;
            case 'total_comment':
                $orderby = 'iTotalComment';
                break;
            case 'view_count':
                $orderby = 'iTotalView';
                break;
            case 'is_featured':
                $orderby = 'bIsFeatured';
                break;
            
            default:
                $orderby = 'iHistoryId';
                break;
        }

        $sortArray = array(); 

        foreach($aRowsProcessed as $aRowProcessed){ 
            foreach($aRowProcessed as $key=>$value){ 
                if(!isset($sortArray[$key])){ 
                    $sortArray[$key] = array(); 
                } 
                $sortArray[$key][] = $value; 
            } 
        } 
        array_multisort($sortArray[$orderby],SORT_DESC,$aRowsProcessed); 


        return $aRowsProcessed;
    }

    public function deleteHistory($aData) {
        $iType = isset($aData['iType']) ? (int) $aData['iType'] : 0;
        $iItemId = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;

        if($iType == 0) {
            $result = Phpfox::getService('ultimatevideo.history')->deleteVideo(Phpfox::getUserId(), $iItemId);
        }elseif($iType == 1){
            $result = Phpfox::getService('ultimatevideo.history')->deletePlaylist(Phpfox::getUserId(), $iItemId);
        }elseif($iType == 2) {
            $result = Phpfox::getService('ultimatevideo.history')->deleteAllHistory(0) && Phpfox::getService('ultimatevideo.history')->deleteAllHistory(1);
        }
        if($result)
            return array('result' => 0, 'error_code' => 0, 'message' => _p('Removed item from history successfully'));    
        return array('result' => 0, 'error_code' => 1, 'message' => _p('This item can not be removed'));
    }

    public function processRowHistory($aRow){
        if($aRow['item_type'] == 1)
            $aResult = $this->processRowPlaylist($aRow);
        else $aResult = $this->processRow($aRow);
        $aResult['iHistoryId'] = $aRow['history_id'];
        return $aResult;
    }

    public function getNotificationLikevideo($aNotification)
    {
        $aRow = $this->database()->select('e.video_id, e.title, e.user_id, u.gender, u.full_name')  
            ->from(Phpfox::getT('ynultimatevideo_videos'), 'e')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->where('e.video_id = ' . (int) $aNotification['item_id'])
            ->execute('getSlaveRow');
            
        if (!isset($aRow['video_id']))
        {
            return false;
        }           
            
        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...');
        
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            $sPhrase = $sUsers._p(' liked ').Phpfox::getService('user')->gender($aRow['gender'])._p(' own video ').'"'.$sTitle.'"';
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())        
        {
            $sPhrase = $sUsers._p(' liked your video ').'"'.$sTitle.'"';
        }
        else 
        {
            $sPhrase = $sUsers._p(' liked ').'<span class="drop_data_user">'.$aRow['full_name'].'
        \'s</span> video "'.$sTitle.'"';
        }
            
        return array(
            'link' => array(
                'iVideoId' => $aRow['video_id'],
                'sTitle' => $aRow['title']
                ),
            'message' => $sPhrase,
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'video')
        );      
    }
    public function getNotificationCommentvideo($aNotification)
    {
        $aRow = $this->database()->select('e.video_id, e.title, e.user_id, u.gender, u.full_name')  
            ->from(Phpfox::getT('ynultimatevideo_videos'), 'e')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->where('e.video_id = ' . (int) $aNotification['item_id'])
            ->execute('getSlaveRow');
            
        if (!isset($aRow['video_id']))
        {
            return false;
        }           
            
        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...');
        
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            $sPhrase = $sUsers._p(' commented ').Phpfox::getService('user')->gender($aRow['gender'])._p(' own video ').'"'.$sTitle.'"';
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())        
        {
            $sPhrase = $sUsers._p(' commented your video ').'"'.$sTitle.'"';
        }
        else 
        {
            $sPhrase = $sUsers._p(' commented ').'<span class="drop_data_user">'.$aRow['full_name'].'
        \'s</span> video "'.$sTitle.'"';
        }
            
        return array(
            'link' => array(
                'iVideoId' => $aRow['video_id'],
                'sTitle' => $aRow['title']
                ),
            'message' => $sPhrase,
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'video')
        );      
    }
    public function getNotificationLikeplaylist($aNotification)
    {
        $aRow = $this->database()->select('e.playlist_id, e.title, e.user_id, u.gender, u.full_name')  
            ->from(Phpfox::getT('ynultimatevideo_playlists'), 'e')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->where('e.playlist_id = ' . (int) $aNotification['item_id'])
            ->execute('getSlaveRow');
            
        if (!isset($aRow['playlist_id']))
        {
            return false;
        }           
            
        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...');
        
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            $sPhrase = $sUsers._p(' liked ').Phpfox::getService('user')->gender($aRow['gender'])._p(' own playlist ').'"'.$sTitle.'"';
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())        
        {
            $sPhrase = $sUsers._p(' liked your playlist ').'"'.$sTitle.'"';
        }
        else 
        {
            $sPhrase = $sUsers._p(' liked ').'<span class="drop_data_user">'.$aRow['full_name'].'
        \'s</span> playlist "'.$sTitle.'"';
        }
            
        return array(
            'link' => array(
                'iPlaylistId' => $aRow['playlist_id'],
                'sTitle' => $aRow['title']
                ),
            'message' => $sPhrase,
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'video')
        );      
    }
    public function getNotificationCommentplaylist($aNotification)
    {
        $aRow = $this->database()->select('e.playlist_id, e.title, e.user_id, u.gender, u.full_name')  
            ->from(Phpfox::getT('ynultimatevideo_playlists'), 'e')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->where('e.playlist_id = ' . (int) $aNotification['item_id'])
            ->execute('getSlaveRow');
            
        if (!isset($aRow['playlist_id']))
        {
            return false;
        }           
            
        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...');
        
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            $sPhrase = $sUsers._p(' commented ').Phpfox::getService('user')->gender($aRow['gender'])._p(' own playlist ').'"'.$sTitle.'"';
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())        
        {
            $sPhrase = $sUsers._p(' commented your playlist ').'"'.$sTitle.'"';
        }
        else 
        {
            $sPhrase = $sUsers._p(' commented ').'<span class="drop_data_user">'.$aRow['full_name'].'
        \'s</span> playlist "'.$sTitle.'"';
        }
            
        return array(
            'link' => array(
                'iPlaylistId' => $aRow['playlist_id'],
                'sTitle' => $aRow['title']
                ),
            'message' => $sPhrase,
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'video')
        );      
    }
}
