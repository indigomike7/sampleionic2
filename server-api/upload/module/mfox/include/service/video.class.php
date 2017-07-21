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
class Mfox_Service_Video extends Phpfox_Service {

    public function __construct(){

    }

    /**
     * <code>
     * Phpfox::getService('mfox.video')->create(array('sModule'=>'user', 'iItemId'=>12));
     * </code>
     * 
     * input data:
     * + sModule: string, required, In page.
     * + iItem: int, required. In page.
     * + iCategoryId: int, optional.
     * + sUrl: string, required.
     * + iPrivacy: int, optional.
     * + iPrivacyComment: int, optional.
     * 
     * output data:
     * + result: 1 if success and 0 otherwise.
     * + error_code: 1 if error, and 0 otherwise.
     * + message: Message to show the bug.
     * + iVideoId: Video id.
     * + sTitle: Title of video.
     * + aCallback: The callback info.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see video/create
     * 
     * @param array $aData
     * @return array
     * 
     */
    public function create($aData)
    {
        if (!Phpfox::getUserParam('video.can_upload_videos'))
        {
            return array('result' => 0, 
            	'error_code' => 1, 
            	'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_video_module")));
        }
		
        $sModule = isset($aData['sModule']) ? $aData['sModule'] : 'video';
        $iItem = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;
        $iCategoryId = isset($aData['category_id']) ? (int) $aData['category_id'] : 0;
        // Get the callback.
        $aCallback = false;
        if ($sModule !== false && $iItem !== false && Phpfox::hasCallback($sModule, 'getVideoDetails'))
        {
            if (($aCallback = Phpfox::callback($sModule . '.getVideoDetails', array('item_id' => $iItem))))
            {
                if ($sModule == 'pages' && !Phpfox::getService('pages')->hasPerm($iItem, 'video.share_videos'))
                {
                    return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('video.unable_to_view_this_item_due_to_privacy_settings'));
                }
            }
        }

        $aDataCategories = $this->categories(array());
        $aCategory = array();

        // Check parent.
        $iCount = count($aDataCategories);
        $iParentId = $iCategoryId;
        for ($i = $iCount - 1; $i >= 0; $i--)
        {
            if ($aDataCategories[$i]['iCategoryId'] == $iParentId)
            {
                $aCategory[] = $iParentId;
                // Update new parent.
                $iParentId = $aDataCategories[$i]['iParentId'];
            }
        }

        $aVals = array(
            'url' => isset($aData['sUrl']) ? $aData['sUrl'] : '',
            'privacy' => isset($aData['auth_view']) ? (int) $aData['auth_view'] : 0,
            'privacy_comment' => isset($aData['auth_comment']) ? (int) $aData['auth_comment'] : 0,
            'category' => $aCategory,
            'module' => $sModule,
            'item' => $iItem,
            'callback_item_id' => $iItem,
            'callback_module' => $sModule,
        );
		
		
        // Check flood.
        if (($iFlood = Phpfox::getUserParam('video.flood_control_videos')) !== 0)
        {
            $aFlood = array(
                'action' => 'last_post', // The SPAM action
                'params' => array(
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('video'), // Database table we plan to check
                    'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);	
                )
            );
            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood))
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getPhrase('video.you_are_sharing_a_video_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
            }
        }
        if (Phpfox_Error::isPassed())
        {
            /**
             * Get the video information by the link.
             */
            if (Phpfox::getService('video.grab')->get($aVals['url']))
            {
                // Add share video.
                if ($iId = Phpfox::getService('video.process')->addShareVideo($aVals))
                {
                    /**
                     * @var array
                     */
                    $aVideo = Phpfox::getService('video')->getForEdit($iId);
                    Phpfox::getService('mfox.helper.video')->updateTitleAndDescription($aData, $iId);
                    // Check image:
                    if (Phpfox::getService('video.grab')->hasImage())
                    {
                        if (isset($aVals['module']) && isset($aVals['item']) && Phpfox::hasCallback($aVals['module'], 'uploadVideo'))
                        {
                            $aCallback = Phpfox::callback($aVals['module'] . '.uploadVideo', $aVals['item']);
                            if ($aCallback !== false)
                            {
                                return array(
                                    'result' => 1,
                                    'error_code' => 0,
                                    'message' =>  Phpfox::getPhrase('video.video_successfully_added'),
                                    'iVideoId' => $aVideo['video_id'],
                                    'sVideoTitle' => $aVideo['title'],
                                    'aCallback' => $aCallback);
                            }
                        }
                        return array(
                            'result' => 1,
                            'error_code' => 0,
                            'message' =>  Phpfox::getPhrase('video.video_successfully_added'),
                            'iVideoId' => $aVideo['video_id'],
                            'sVideoTitle' => $aVideo['title']
                        );
                    }
                    else
                    {
                        return array(
                            'result' => 1,
                            'error_code' => 0,
                            'message' =>  Phpfox::getPhrase('video.video_successfull_added_however_you_will_have_to_manually_upload_a_photo_for_it'),
                            'iVideoId' => $aVideo['video_id'],
                            'sVideoTitle' => $aVideo['title']
                        );
                    }

                }
            }
        }


        return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
    }

    /**
     * input data:
     * + iVideoId: int, required.
     * + sTitle: string, required.
     * + iCategoryId: int, optional.
     * + sDescription: string, optional
     * + sTopic: string, optional.
     * + image: file, optional. To change image default.
     * 
     * output data: 
     * + result: int.
     * + error_code: int.
     * + message: string.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see video/edit
     * 
     * @param array $aData  
     * @return array
     */
    public function edit($aData)
    {
        /**
         * @var int
         */
        $iVideoId = isset($aData['iVideoId']) ? (int) $aData['iVideoId'] : 0;
        // Get the video by id.
        $aVideo = Phpfox::getService('video')->getForEdit($iVideoId);
        /**
         * @var int
         */
        $iCategoryId = isset($aData['iCategoryId']) ? (int) $aData['iCategoryId'] : 0;
        if (!isset($aVideo['video_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('video.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }
        // Check privacy for song.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('video', $aVideo['song_id'], $aVideo['user_id'], $aVideo['privacy'], $aVideo['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        $aDataCategories = $this->categories(array());
        $aCategory = array();

        // Check parent.
        $iCount = count($aDataCategories);
        $iParentId = $iCategoryId;
        for ($i = $iCount - 1; $i >= 0; $i--)
        {
            if ($aDataCategories[$i]['iCategoryId'] == $iParentId)
            {
                $aCategory[] = $iParentId;
                // Update new parent.
                $iParentId = $aDataCategories[$i]['iParentId'];
            }
        }

        /**
         * @var array
         */
        $aVals = array(
            'title' => isset($aData['title']) ? $aData['title'] : '',
            'category' => $aCategory,
            'text' => isset($aData['description']) ? $aData['description'] : '',
            'privacy' => isset($aData['auth_view']) ? (int) $aData['auth_view'] : 0,
            'privacy_comment' => isset($aData['auth_comment']) ? (int) $aData['auth_comment'] : 0,
            'tag_list' => isset($aData['sTopic']) ? $aData['sTopic'] : '',
        );
        if (($mReturn = Phpfox::getService('video.process')->update($aVideo['video_id'], $aVals)))
        {
					return array(
						'result' => 1,
						'message' =>  Phpfox::getPhrase('video.video_successfully_updated'),
						'iVideoId' => $aVideo['video_id'],
						'iVideoTitle' => 'Not Implement Yet',
					);
        }
        return array('result' => 0, 'error_code' => 1, 'message' => Phpfox_Error::get());
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
        if (!($aVideo = Phpfox::getService('video')->callback($aCallback)->getVideo($iVideoId)))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('video.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }
        // Check the privacy.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('video', $aVideo['video_id'], $aVideo['user_id'], $aVideo['privacy'], $aVideo['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        // Delete video.
        if (Phpfox::getService('video.process')->delete($iVideoId))
        {
            return array('result' => 1, 'error_code' => 0, 'message' =>  Phpfox::getPhrase('video.video_successfully_deleted'));
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
     * Need to be changed.
     * 
     * input data:
     * + iVideoId: int, required.
     * + sModule: string, optional. In page.
     * + iItem: int, optional. In page.
     * 
     * output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * + lastid: int. Last comment id.
     * 
     * @param int $iVideoId
     * @param string $sModule
     * @param int $iItem
     * @return array
     */
    public function checkPrivacyCommentOnVideo($iVideoId, $sModule = '', $iItem = 0)
    {
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

        if (!Phpfox::getService('mfox.comment')->checkCanPostCommentOnItem($aVideo))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_this_item")));
        }

        return null;
    }

    /**
     * Need to be changed.
     * 
     * input data:
     * + iVideoId: int, required.
     * + sModule: string, optional. In page.
     * + iItem: int, optional. In page.
     * 
     * output data:
     * + result: int.
     * + error_code: int.
     * + message: string.
     * + lastid: int. Last comment id.
     * 
     * @param int $iVideoId
     * @param string $sModule
     * @param int $iItem
     * @return array
     */
    public function checkPrivacyOnVideo($iVideoId, $sModule = '', $iItem = 0)
    {
        // Get the callback.
        if ($iItem > 0 && !empty($sModule) && $sModule !== 'feed')
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

        return null;
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
                ->select('category_id AS iCategoryId, parent_id AS iParentId, is_active AS bIsActive, name AS sName, ordering AS iOrdering')
                ->from(Phpfox::getT('video_category'))
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

    /**
     * Input data:
     * + sAction: string, optional, ex: "more" or "new".
     * + iLastTimeStamp: int, optional.
     * + iLimit: int, optional.
     * + sView: string, optional.
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
     * @see video/search
     * 
     * @param array $aData
     * @return array
     */
    public function search($aData)
    {
        return $this->getVideos($aData);
    }

    // alternate of video/search
    /** 
    * @since 3.08
    */
    public function fetch($aData)
    {
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

    /**
     * Input data:
     * + sAction: string, optional, ex: "more" or "new".
     * + iLastTimeStamp: int, optional.
     * + iLimit: int, optional.
     * + sView: string, optional.
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
     * 
     * @param array $aData
     * @return array
     */
    private function getVideos($aData)
    {
        if (!Phpfox::getUserParam('video.can_access_videos'))
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
            case 'pending':
                if (Phpfox::getUserParam('video.can_approve_videos'))
                {
                    $aCond[] = 'm.view_id = 2';
                }
                break;
            case 'my':
                $aCond[] = 'm.user_id = ' . Phpfox::getUserId();
                break;
            default:
                if ($bIsUserProfile)
                {
                    $aCond[] = 'm.in_process = 0 AND m.view_id ' . ($aUser['user_id'] == Phpfox::getUserId() ? 'IN(0,2)' : '= 0') . ' AND m.item_id = 0 AND m.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ') AND m.user_id = ' . (int) $aUser['user_id'];
                }
                else
                {
                    if ($aParentModule !== false)
                    {
                        $aCond[] = 'm.in_process = 0 AND m.view_id = 0 AND m.module_id = \'' . Phpfox::getLib('database')->escape($aParentModule['module_id']) . '\' AND m.item_id = ' . (int) $aParentModule['item_id'] . ' AND m.privacy IN(%PRIVACY%)';
                    }
                    else
                    {
                        $aCond[] = 'm.in_process = 0 AND m.view_id = 0 AND m.item_id = 0 AND m.privacy IN(%PRIVACY%)';
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
            $aCond[] = 'mcd.category_id = ' . (int) $iCategory;
        }

        // Get number of the video.
        $this->database()
                ->select('COUNT(*)')
                ->from(Phpfox::getT('video'), 'm')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.user_id');

        if ($iCategory > 0)
        {
            $this->database()->innerJoin(Phpfox::getT('video_category_data'), 'mcd', 'mcd.video_id = m.video_id');
        }
        if (Phpfox::isModule('friend') && ($sView == 'friend' || ($sView != 'my' && Phpfox::getParam('core.friends_only_community'))))
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = m.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }
        if (!empty($sTag))
        {
            $this->database()->innerJoin(Phpfox::getT('tag'), 'tag', 'tag.item_id = m.video_id AND tag.category_id = \'' . (defined('PHPFOX_GROUP_VIEW') ? 'video_group' : 'video') . '\' AND tag_text = "' . $sTag . '"');
        }
        /**
         * @var int
         */
        $iCount = $this->database()
                ->where(implode(' AND ', $aCond))
                ->limit(1)
                ->execute('getField');
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
                    ->select('m.*, u.user_id, u.profile_page_id, u.server_id AS user_server_id, u.user_name, u.full_name, u.gender, u.user_image, u.is_invisible, u.user_group_id, u.language_id, rate_id AS has_rated, vt.text')
                    ->from(Phpfox::getT('video'), 'm');

            if ($iCategory > 0)
            {
                $this->database()->innerJoin(Phpfox::getT('video_category_data'), 'mcd', 'mcd.video_id = m.video_id');
            }
            if (!empty($sTag))
            {
                $this->database()->innerJoin(Phpfox::getT('tag'), 'tag', 'tag.item_id = m.video_id AND tag.category_id = \'' . (defined('PHPFOX_GROUP_VIEW') ? 'video_group' : 'video') . '\' AND tag_text = "' . $sTag . '"');
            }
            if (Phpfox::isModule('friend') && ($sView == 'friend' || ($sView != 'my' && Phpfox::getParam('core.friends_only_community'))))
            {
                $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = m.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
            }

            switch($sSortBy) {
                case 'rating': 
                    $this->database()->order('m.total_score DESC');
                    break;
                case 'view_count':
                    $this->database()->order('m.total_view DESC');
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
                    ->leftJoin(Phpfox::getT('video_text'), 'vt', 'vt.video_id = m.video_id')
										->leftJoin(Phpfox::getT('video_rating'), 'vr', 'vr.item_id = m.video_id AND vr.user_id = ' . Phpfox::getUserId())
                    ->where(implode(' AND ', $aCond))
                    ->limit($iPage, $iLimit, $iCount)
                    ->execute('getRows');
        }
        /**
         * @var array
         */
        $aResult = array();

				// var_dump($aRows);
        foreach ($aRows as $aRow)
        {
						// if(Phpfox::isModule('like')) {
						// 	list($iCnt, $aLikedUsers) = Phpfox::getService('like')->getForMembers('video', $aRow['video_id']);
						// 	$aLikedUsersReturn = array();

						// 	foreach ($aLikedUsers as $aUser) {
						// 		$aLikedUsersReturn[] = array(
						// 			'iUserId' => $aUser['user_id'],
						// 			'sDisplayName' => $aUser['full_name']
						// 		);
						// 	}
						// } else {
						// 	$aLikedUsersReturn = array();
						// }
            $aResult[] = $this->processRow($aRow);
        }

        return $aResult;
    }

    public function processRow($aRow)
    {
        $aTemp = array(
            // 'aUserLike' => $aLikedUsersReturn, //add
            // 'bIsLike' => Phpfox::isModule('like') ? Phpfox::getService('like')->didILike('video', $aRow['video_id']) : FALSE,
            // 'bIsRating' => $aRow['has_rated'] == NULL ? FALSE : TRUE,
            // 'fRating' => $aRow['total_score'],
            // 'iCategory' => Phpfox::getService('video.category')->getCategoryIds($aRow['video_id']),
            // 'iDuration' => $aRow['duration'],
            // 'iRatingCount' => $aRow['total_rating'],
            // 'iUserLevelId' => $aRow['user_group_id'],
            // 'iParentId' => 'Not Implement Yet',
            // 'sCode' => 'Not Implement Yet',
            // 'sParentType' => 'Not Implement Yet',
            // 'sDescription' => $aRow['text'],
            // 'sFullTimeStamp' => date('l, F j, o', (int) $aRow['time_stamp']) . ' at ' . date('h:i a', (int) $aRow['time_stamp']),
            // 'sTimeStamp' => $aRow['time_stamp'],
            // 'iTimeStamp' => $aRow['time_stamp'],
            //----
            'iVideoId' => $aRow['video_id'],
            'bInProcess' => $aRow['in_process'],
            'bIsStream' => $aRow['is_stream'],
            'bIsFeatured' => $aRow['is_featured'],
            'bIsSpotlight' => $aRow['is_spotlight'],
            'bIsSponsor' => $aRow['is_sponsor'],
            'iViewId' => $aRow['view_id'],
            'sModuleId' => $aRow['module_id'],
            'iItemId' => $aRow['item_id'],
            'iPrivacy' => $aRow['privacy'],
            'iPrivacyComment' => $aRow['privacy_comment'],
            'sTitle' => $aRow['title'],
            'iUserId' => $aRow['user_id'],
            'iParentUserId' => $aRow['parent_user_id'],
            'sDestination' => $aRow['destination'],
            'sFileExt' => $aRow['file_ext'],
            'sResolutionX' => $aRow['resolution_x'],
            'sResolutionY' => $aRow['resolution_y'],
            'iTotalComment' => $aRow['total_comment'],
            'iTotalLike' => $aRow['total_like'],
            'iTotalDislike' => $aRow['total_dislike'],
            'sVideoImage' => Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aRow['image_server_id'],
                'path' => 'video.url_image',
                'file' => $aRow['image_path'],
                'suffix' => '_120',
                'return_url' => true
                    )
            ),
            'iTotalScore' => (float)($aRow['total_score']/2),
            'iTotalView' => $aRow['total_view'],
            'bIsViewed' => $aRow['is_viewed'],
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
        );

        return Phpfox::getService('mfox.helper.video')->retrieveMoreInfo($aRow, $aTemp);
    }

    public function canEdit($aItem){
        if(($aItem['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('video.can_edit_own_video')) 
            || Phpfox::getUserParam('video.can_edit_other_video')){
            return true;
        }

        return false;
    }

    public function canDelete($aItem){
        if (($aItem['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('video.can_delete_own_video')) 
            || Phpfox::getUserParam('video.can_delete_other_video')){
            return true;
        }

        return false;
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
     * @param array $aNotification
     * @return boolean|array
     */
    public function doVideoGetNotificationLike($aNotification)
    {
        /**
         * @var array
         */
        $aRow = $this->database()->select('v.video_id, v.title, v.user_id, u.gender, u.full_name')
                ->from(Phpfox::getT('video'), 'v')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = v.user_id')
                ->where('v.video_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');

        if (!isset($aRow['video_id']))
        {
            return array();
        }
        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            $sPhrase =  Phpfox::getPhrase('video.user_name_liked_gender_own_video_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...'), 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1)));
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('video.user_name_liked_your_video_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('video.user_name_liked_span_class_drop_data_user_full_name_s_span_video_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aRow['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }

        return array(
            'link' => array(
                'iVideoId' => $aRow['video_id'],
                'sVideoTitle' => $aRow['title']
            ),
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }

    /**
     * 
     * @param array $aNotification
     * @return array
     */
    public function doVideoGetCommentNotification($aNotification)
    {
        /**
         * @var array
         */
        $aRow = $this->database()->select('l.video_id, l.title, u.user_id, u.gender, u.user_name, u.full_name')
                ->from(Phpfox::getT('video'), 'l')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
                ->where('l.video_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');

        if (!isset($aRow['video_id']))
        {
            return array();
        }

        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'] && !isset($aNotification['extra_users']))
        {
            $sPhrase =  Phpfox::getPhrase('video.user_name_commented_on_gender_video_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('video.user_name_commented_on_your_video_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('video.user_name_commented_on_span_class_drop_data_user_full_name_s_span_video_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aRow['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }

        return array(
            'link' => array(
                'iVideoId' => $aRow['video_id'],
                'sVideoTitle' => $aRow['title']
            ),
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog'),
            'sModule' => 'video',
            'sMethod' => 'getCommentNotification'
        );
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
        $aVideo = Phpfox::getService('video')->callback(array('sModule' => $sModule, 'iItem' => $iItem))->getVideo($iVideoId);
        if (!isset($aVideo['video_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('video.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }
        // Check privacy for video.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('video', $aVideo['song_id'], $aVideo['user_id'], $aVideo['privacy'], $aVideo['is_friend'], true))
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
        if (!$aVideo['is_stream'])
        {
            $sVideoPath = (preg_match("/\{file\/videos\/(.*)\/(.*)\.flv\}/i", $aVideo['destination'], $aMatches) ? Phpfox::getParam('core.path') . str_replace(array('{', '}'), '', $aMatches[0]) : Phpfox::getParam('video.url') . $aVideo['destination']);
            if (Phpfox::getParam('core.allow_cdn') && !empty($aVideo['server_id']))
            {
                $sTempVideoPath = Phpfox::getLib('cdn')->getUrl($sVideoPath, $aVideo['server_id']);
                if (!empty($sTempVideoPath))
                {
                    $sVideoPath = $sTempVideoPath;
                }
            }
        }
        $aTagList = isset($aVideo['tag_list']) ? $aVideo['tag_list'] : 0;
        $aTags = array();
        foreach ($aTagList as $aTag)
        {
            $aTags[] = $aTag['tag_text'];
        }
        $sTags = implode(', ', $aTags);

        $sVideoPath = '';

        if (!$aVideo['is_stream'])
        {
            $sVideoPath = (preg_match("/\{file\/videos\/(.*)\/(.*)\.flv\}/i", $aVideo['destination'], $aMatches) ? Phpfox::getParam('core.path') . str_replace(array('{', '}'), '', $aMatches[0]) : Phpfox::getParam('video.url') . $aVideo['destination']);
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
                ->from(Phpfox::getT('video_category_data'), 'pcd')
                ->join(Phpfox::getT('video_category'), 'pc', 'pc.category_id = pcd.category_id')
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

        $aUserDislike = array();
        $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('video', $aVideo['video_id'], $bGetCount = false);
        foreach($aDislike as $dislike){
            $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
        }      

        $bCanComment =        Phpfox::getService('mfox.comment')->checkCanPostCommentOnItem($aVideo);   
        $textObject = $this->getTextByVideoId($aVideo['video_id']);

        $aRow = array(
					
						'bCanView' => $bCanView,
						'sCommentPrivacy' => Phpfox::getService('privacy')->getPhrase($aVideo['privacy_comment']),
						'sViewPrivacy' => Phpfox::getService('privacy')->getPhrase($aVideo['privacy']),
						'sEmbedCode' => $aVideo['embed_code'],
						'sTags' => $sTags,
						'sType' => $aVideo['is_stream'] == 1 ? '' : 'file',
						'sVideoUrl' => $sVideoPath,
					//-----
            'bVideoIsViewed' => $aVideo['video_is_viewed'],
            'bIsFriend' => $aVideo['is_friend'],
            'aUserDislike' => $aUserDislike,
            'bIsLiked' => $aVideo['is_liked'],
            'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('video', $aVideo['video_id'], Phpfox::getUserId()),
            'bCanDislike' => Phpfox::getService('mfox.like')->isTypeSupportDislike('video'),
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
            'iTotalDislike' => $aVideo['total_dislike'],
            'sVideoImage' => Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aVideo['image_server_id'],
                'path' => 'video.url_image',
                'file' => $aVideo['image_path'],
                'suffix' => '_120',
                'return_url' => true
                    )
            ),
            'iTotalScore' => (float)($aVideo['total_score']/2),            
            'iTotalRating' => $aVideo['total_rating'],
            'iTimeStamp' => $aVideo['time_stamp'],
            'sTimeStamp' => date('l, F j, o', (int) $aVideo['time_stamp']) . ' at ' . date('h:i a', (int) $aVideo['time_stamp']),
            'iTotalView' => $aVideo['total_view'],
            'bIsViewed' => $aVideo['is_viewed'],
            'sText' => Phpfox::getLib('parse.output')->parse($aVideo['text']),
            'iProfilePageId' => $aVideo['profile_page_id'],
            'sUserImage' => Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aVideo['user_server_id'],
                'path' => 'core.url_user',
                'file' => $aVideo['user_image'],
                'suffix' => '_50_square',
                'return_url' => true
                    )
            ),
            'sUsername' => $aVideo['user_name'],
            'sFullname' => $aVideo['full_name'],
            'iGender' => $aVideo['gender'],
            'bIsInvisible' => $aVideo['is_invisible'],
            'iUserGroupId' => $aVideo['user_group_id'],
            'iLanguageId' => isset($aVideo['language_id']) ? $aVideo['language_id'] : 0,
            'sYoutubeVideoUrl' => $aVideo['youtube_video_url'],
            'sEmbed' => empty($aVideo['youtube_video_url']) ? '' : '<iframe width="420" height="315" src="http://www.youtube.com/embed/' . $aVideo['youtube_video_url']  . '?showinfo=0&wmode=opaque' . '" frameborder="0" allowfullscreen></iframe>',
            'iTotalUserVideos' => $aVideo['total_user_videos'],
            'sVideoWebLink' => Phpfox::getLib('url')->permalink('mobile.video', $aVideo['video_id'], $aVideo['title']),
            'sTopic' => $sTags,
            'bCanPostComment' => $bCanComment,  // deprecated.
            'bCanComment' => $bCanComment,
            'bCanEdit' => $this->canEdit($aVideo),
            'bCanDelete' => $this->canDelete($aVideo),
        );

        if (Phpfox::isModule('track') && !$aVideo['video_is_viewed'])
		{
			Phpfox::getService('track.process')->add('video', $aVideo['video_id']);
        }

				$aReturn = Phpfox::getService('mfox.helper.video')->retrieveMoreInfo($aVideo, $aRow);

                $sDes = $aReturn['sDescription'];
                if(isset($textObject['video_id'])){
                    $aReturn['sDescription'] = $textObject['text_parsed'];
                } else {
                    $aReturn['sDescription'] = Phpfox::getLib('parse.output')->parse($sDes);
                }
                $aReturn['sDescriptionOrigin'] = $sDes;

				return $aReturn;
    }

    /**
     * Push Cloud Message for video.
     * @param int $iVideoId
     * @param string $sModule
     * @param int $iItem
     */
    public function doPushCloudMessageVideo($aData)
    {
        /**
         * @var string
         */
        $sModule = isset($aData['sModule']) ? $aData['sModule'] : '';
        /**
         * @var int
         */
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;
        /**
         * @var int
         */
        $iVideoId = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;

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
        $aVideo = Phpfox::getService('video')->callback($aCallback)->getVideo($iVideoId);
        if (isset($aVideo['user_id']) && $aVideo['user_id'] != Phpfox::getUserId())
        {
            /**
             * @var int
             */
            $iPushId = Phpfox::getService('mfox.push')->savePush($aData, $aVideo['user_id']);

            Phpfox::getService('mfox.cloudmessage')->send(array('message' => 'notification', 'iPushId' => $iPushId), $aVideo['user_id']);
        }
    }

    /**
     * Delete temp file when upload failure.
     * @param int $iId
     * @param array $aVideo
     * @return boolean
     */
    public function deleteTempUploadFile($iId = null, &$aVideo = null)
    {
        if ($aVideo === null)
        {
            /**
             * @var array
             */
            $aVideo = $this->database()
                    ->select('v.video_id, v.module_id, v.item_id, v.is_sponsor, v.is_featured, v.user_id, v.destination, v.image_path')
                    ->from(Phpfox::getT('video'), 'v')
                    ->where(($iId === null ? 'v.view_id = 1 AND v.user_id = ' . Phpfox::getUserId() : 'v.video_id = ' . (int) $iId))
                    ->execute('getSlaveRow');

            if (!isset($aVideo['video_id']))
            {
                return false;
            }

            if ($aVideo['module_id'] == 'pages' && Phpfox::getService('pages')->isAdmin($aVideo['item_id']))
            {
                $bOverPass = true;
            }
        }
        else
        {
            $bOverPass = true;
        }

        if (($aVideo['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('video.can_delete_own_video')) || Phpfox::getUserParam('video.can_delete_other_video') || isset($bOverPass))
        {
            /**
             * @var int
             */
            $iFileSize = 0;

            if (!empty($aVideo['destination']))
            {
                $sVideo = Phpfox::getParam('video.dir') . sprintf($aVideo['destination'], '');
                if (file_exists($sVideo))
                {
                    $iFileSize += filesize($sVideo);

                    Phpfox::getLib('file')->unlink($sVideo);
                }
            }

            if (!empty($aVideo['image_path']))
            {
                $sImage = Phpfox::getParam('video.dir_image') . sprintf($aVideo['image_path'], '');
                if (file_exists($sImage))
                {
                    $iFileSize += filesize($sImage);
                    if ($iFileSize > 0)
                    {
                        Phpfox::getLib('file')->unlink($sImage);
                    }
                }
            }

            if ($iFileSize > 0)
            {
                Phpfox::getService('user.space')->update($aVideo['user_id'], 'video', $iFileSize, '-');
            }

            (Phpfox::isModule('comment') ? Phpfox::getService('comment.process')->deleteForItem(null, $aVideo['video_id'], 'video') : null);
            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('video', $aVideo['video_id']) : null);
            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('comment_video', $aVideo['video_id']) : null);

            (Phpfox::isModule('tag') ? Phpfox::getService('tag.process')->deleteForItem($aVideo['user_id'], $aVideo['video_id'], 'video') : null);

            $this->database()->delete(Phpfox::getT('video'), 'video_id = ' . $aVideo['video_id']);
            $this->database()->delete(Phpfox::getT('video_category_data'), 'video_id = ' . $aVideo['video_id']);
            $this->database()->delete(Phpfox::getT('video_rating'), 'item_id = ' . $aVideo['video_id']);
            $this->database()->delete(Phpfox::getT('video_text'), 'video_id = ' . $aVideo['video_id']);
            $this->database()->delete(Phpfox::getT('video_embed'), 'video_id = ' . $aVideo['video_id']);

            // Update user activity
            Phpfox::getService('user.activity')->update($aVideo['user_id'], 'video', '-');

            if (isset($aVideo['is_sponsor']) && $aVideo['is_sponsor'] == 1)
            {
                $this->cache()->remove('video_sponsored');
            }
            if (isset($aVideo['is_featured']) && $aVideo['is_featured'] == 1)
            {
                $this->cache()->remove('video_featured');
            }
            if (Phpfox::getParam('core.allow_cdn'))
            {
                Phpfox::getLib('cdn')->remove(Phpfox::getParam('video.dir') . sprintf($aVideo['destination'], ''));
            }

            return true;
        }

        return Phpfox_Error::set( Phpfox::getPhrase('video.invalid_permissions'));
    }

    /**
     * Get activity feed of the video.
     * @param array $aItem
     * @param array $aCallback
     * @param bool $bIsChildItem
     * @return boolean
     */
    public function doVideoGetActivityFeed($aItem, $aCallback = null, $bIsChildItem = false)
	{				
		if (!Phpfox::getUserParam('video.can_access_videos'))
		{
			return false;
		}
		
		if ($aCallback === null)
		{
			$this->database()->select(Phpfox::getUserField('u', 'parent_') . ', ')->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = v.parent_user_id');
		}
		
		if ($bIsChildItem)
		{
			$this->database()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user'), 'u2', 'u2.user_id = v.user_id');
		}		
		/**
         * @var array
         */
		$aRow = $this->database()->select('v.video_id, v.module_id, v.item_id, v.title, v.time_stamp, v.total_comment, v.total_like, v.image_path, v.image_server_id, l.like_id AS is_liked, vt.text_parsed')
			->from(Phpfox::getT('video'), 'v')
			->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'video\' AND l.item_id = v.video_id AND l.user_id = ' . Phpfox::getUserId())
			->leftJoin(Phpfox::getT('video_text'), 'vt', 'vt.video_id = v.video_id')
			->where('v.video_id = ' . (int) $aItem['item_id'])
			->execute('getSlaveRow');		
		
		if (!isset($aRow['video_id']))
		{
			return false;
		}
		
		if ($bIsChildItem)
		{
			$aItem = $aRow;
		}		
		
		if ((defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'video.view_browse_videos'))
			|| (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aRow['item_id'], 'video.view_browse_videos'))	
			)
		{
			return false;
		}
        /**
         * @var array
         */
		$aReturn = array(
            'sTypeId' => 'video',
            'iUserId' => $aRow['user_id'],
            'sUsername' => $aRow['user_name'],
            'sFullName' => $aRow['full_name'],

			'sFeedTitle' => $aRow['title'],
			'sFeedLink' => Phpfox::permalink('video', $aRow['video_id'], $aRow['title']),
			'sFeedContent' => $aRow['text_parsed'],
			'iTotalComment' => $aRow['total_comment'],
			'iFeedTotalLike' => $aRow['total_like'],
			'bFeedIsLiked' => $aRow['is_liked'],
			'sFeedIcon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/video.png', 'return_url' => true)),
			'iTimeStamp' => $aRow['time_stamp'],
            'Time' => date('l, F j, o', (int) $aRow['time_stamp']) . ' at ' . date('h:i a', (int) $aRow['time_stamp']),
            'TimeConverted' => Phpfox::getLib('date')->convertTime($aRow['time_stamp'], 'comment.comment_time_stamp'),
			'bEnableLike' => true,			
			'sCommentTypeId' => 'video',
			'sLikeTypeId' => 'video',
            
            'aVideo' => array(
                'iVideoId' => $aRow['video_id'],
                'sTitle' => $aRow['title'],
                'iUserId' => $aRow['user_id'],
                'sUserName' => $aRow['user_name'],
                'sFullName' => $aRow['full_name'],
                'iGender' => $aRow['gender'],
                'iProfilePageId' => $aRow['profile_page_id'],
                'sModuleId' => $aRow['module_id'],
                'iItemId' => $aRow['item_id']
            )
		);
		
		if ($aRow['module_id'] == 'pages')
		{
			$aRow['parent_user_id'] = '';
			$aRow['parent_user_name'] = '';
		}		
		
		if (empty($aRow['parent_user_id']))
		{
			$aReturn['sFeedInfo'] =  Phpfox::getPhrase('feed.shared_a_video');
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
				$aReturn['sFeedMiniContent'] =  Phpfox::getPhrase('feed.full_name_posted_a_href_link_a_video_a_on_a_href_profile_parent_full_name_a_s_a_href_profile_link_wall_a', array('full_name' => Phpfox::getService('user')->getFirstName($aItem['full_name']), 'link' => Phpfox::permalink('video', $aRow['video_id'], $aRow['title']), 'profile' => Phpfox::getLib('url')->makeUrl($aRow['parent_user_name']), 'parent_full_name' => $aRow['parent_full_name'], 'profile_link' => Phpfox::getLib('url')->makeUrl($aRow['parent_user_name'])));
				$aReturn['sFeedTitle'] = '';
				unset($aReturn['feed_status'], $aReturn['feed_image'], $aReturn['feed_content']);
			}		
		}		
		
		if (!PHPFOX_IS_AJAX && defined('PHPFOX_IS_USER_PROFILE') && !empty($aRow['parent_user_name']) && $aRow['parent_user_id'] != Phpfox::getService('profile')->getProfileUserId())
		{
			$aReturn['sFeedImage'] = Phpfox::getParam('core.path') . "theme/frontend/default/style/default/image/noimage/profile_50.png";
		}
		else
		{
			if (!empty($aRow['image_path']))
			{
				$aReturn['sFeedImage'] = Phpfox::getLib('image.helper')->display(array(
						'server_id' => $aRow['image_server_id'],
						'path' => 'video.url_image',
						'file' => $aRow['image_path'],
						'suffix' => '_120',
                        'return_url' => true
					)
				);
			}
            else
            {
                $aReturn['sFeedImage'] = Phpfox::getParam('core.path') . "theme/frontend/default/style/default/image/noimage/profile_50.png";
            }
		}
		
		return $aReturn;
	}


		public function privacy($aData) {
			// return Phpfox::getService('mfox.privacy')->privacy($aData);
		}

		public function rate($aData) {
			$aVals['sItemType'] = 'video';
			$aVals['iItemId'] = $aData['iVideoId'];
			$aVals['iRating'] = $aData['iRating'];

			$bResult = Phpfox::getService('mfox.helper.rate')->rate($aVals);
			if(!$bResult) {
				return array( 
					'error_code' => 1,
					'error_message' => implode(' ',Phpfox_Error::get()),
				);
			} else {
				$aVideo = Phpfox::getService('mfox.helper.video')->getSimpleVideoById($aData['iVideoId']);
				return array(
					// 'error_code' => 0,
					'iTotal' => $aVideo['total_rating'],
					'fRating' => (float) ($aVideo['total_score']/2)
				);
			}

		}

		public function parser($aData) {
			$aAdaptedData = array(
				'sUrl' => $aData['sLink']
			);

			$aResult = $this->create($aAdaptedData);

			if($aResult['result'] == 1) {
				$iVideoId = $aResult['iVideoId'];
				$aVideo = Phpfox::getService('mfox.helper.video')->getSimpleVideoById($iVideoId);
				return array(
					'iVideoId' => $iVideoId,
					'iPhotoId' => 'Not Implement Yet',
					'sDescription' => $aVideo['text'],
					'sThumb' => $aVideo['video_image_url'],
					'sTitle' => $aVideo['title']
				);

			} else {
				return array(
					'error_code' => 1,
					'error_message' => $aResult['message']
				);

			}


			
		}

    /**
     * form add
     */
    public function formadd($aData){
        $response  =  array(
        	'bIsAdvancedModule'=> 0,
            'view_options'=> Phpfox::getService('mfox.privacy')->privacy($aData),
            'comment_options'=> Phpfox::getService('mfox.privacy')->privacycomment($aData),
            'category_options'=> $this->categories($aData),
            'bCanUploadVideo'=> $this->canUpload($aData),
        );

        $iValue =  Phpfox::getService('user.privacy')->getValue('video.display_on_profile');
        $response['default_privacy_setting'] = Phpfox::getService('mfox.privacy')->defaultprivacy("$iValue", $response['view_options']);
        
        return $response;
    }

    public function getTextByVideoId($iVideoId){
        return $this->database()->select('vt.*')
            ->from(Phpfox::getT('video_text'), 'vt')
            ->where('vt.video_id = ' . (int) $iVideoId)
            ->execute('getSlaveRow');
    }

    public function canUpload($aData)
    {
        if (!Phpfox::isModule('video'))
        {
            return false;
        }

        if (!Phpfox::getParam('video.allow_video_uploading'))
        {
            return false;
        }

        if (!Phpfox::getParam('video.upload_for_html5'))
        {
            return false;
        }

        return true;
    }
}
