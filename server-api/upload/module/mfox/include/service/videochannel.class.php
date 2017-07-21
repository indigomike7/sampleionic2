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
class Mfox_Service_Videochannel extends Phpfox_Service {

    public function __construct(){

    }

    /**
     * <code>
     * Phpfox::getService('mfox.videochannel')->create(array('sModule'=>'user', 'iItemId'=>12));
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
        if (!Phpfox::getUserParam('videochannel.can_upload_videos'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_video_module")));
        }
        $sModule = isset($aData['sModule']) ? trim($aData['sModule']) : 'videochannel';
        if('video' == $sModule){
            $sModule = 'videochannel';
        }
        $iItem = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;
        $iCategoryId = isset($aData['category_id']) ? (int) $aData['category_id'] : 0;
        // Get the callback.
        $aCallback = false;
        if ($sModule !== false && $iItem !== false && Phpfox::hasCallback($sModule, 'getVideoDetails'))
        {
            if (($aCallback = Phpfox::callback($sModule . '.getVideoDetails', array('item_id' => $iItem))))
            {
                if ($sModule == 'pages' && !Phpfox::getService('pages')->hasPerm($iItem, 'videochannel.share_videos'))
                {
                    return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('videochannel.unable_to_view_this_item_due_to_privacy_settings'));
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
                break;
            }
        }

        // because it is 2 level category, we will seek parent in this loop
        for ($i = $iCount - 1; $i >= 0; $i--)
        {
            if ($aDataCategories[$i]['iCategoryId'] == $iParentId)
            {
                $aCategory[] = $iParentId;
                // Update new parent.
                $iParentId = $aDataCategories[$i]['iParentId'];
                break;
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
        if (($iFlood = Phpfox::getUserParam('videochannel.flood_control_videos')) !== 0)
        {
            $aFlood = array(
                'action' => 'last_post', // The SPAM action
                'params' => array(
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('channel_video'), // Database table we plan to check
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
                    'error_message' =>  Phpfox::getPhrase('videochannel.you_are_sharing_a_video_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
            }
        }
        if (Phpfox_Error::isPassed())
        {
            /**
             * Get the video information by the link.
             */
            if (Phpfox::getService('videochannel.grab')->get($aVals['url']))
            {
                // Add share video.
                if ($iId = Phpfox::getService('videochannel.process')->addShareVideo($aVals))
                {
                    /**
                     * @var array
                     */
                    $aVideo = Phpfox::getService('videochannel')->getForEdit($iId);
                    Phpfox::getService('mfox.helper.videochannel')->updateTitleAndDescription($aData, $iId);
                    // Check image:
                    if (Phpfox::getService('videochannel.grab')->hasImage())
                    {
                        if (isset($aVals['module']) && isset($aVals['item']) && Phpfox::hasCallback($aVals['module'], 'uploadVideo'))
                        {
                            $aCallback = Phpfox::callback($aVals['module'] . '.uploadVideo', $aVals['item']);
                            if ($aCallback !== false)
                            {
                                return array(
                                    'result' => 1,
                                    'error_code' => 0,
                                    'message' =>  Phpfox::getPhrase('videochannel.video_successfully_added'),
                                    'iVideoId' => $aVideo['video_id'],
                                    'sVideoTitle' => $aVideo['title'],
                                    'aCallback' => $aCallback);
                            }
                        }
                        return array(
                            'result' => 1,
                            'error_code' => 0,
                            'message' =>  Phpfox::getPhrase('videochannel.video_successfully_added'),
                            'iVideoId' => $aVideo['video_id'],
                            'sVideoTitle' => $aVideo['title']
                        );
                    }
                    else
                    {
                        return array(
                            'result' => 1,
                            'error_code' => 0,
                            'message' =>  Phpfox::getPhrase('videochannel.video_successfull_added_however_you_will_have_to_manually_upload_a_photo_for_it'),
                            'iVideoId' => $aVideo['video_id'],
                            'sVideoTitle' => $aVideo['title']
                        );
                    }

                }
            }
        }


        return array('result' => 0, 'error_code' => 1, 'error_message' => implode('<br />', Phpfox_Error::get()));
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
        $aVideo = Phpfox::getService('videochannel')->getForEdit($iVideoId);
        /**
         * @var int
         */
        $iCategoryId = isset($aData['iCategoryId']) ? (int) $aData['iCategoryId'] : 0;
        if (!isset($aVideo['video_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('videochannel.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }
        // Check privacy for song.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('videochannel', $aVideo['song_id'], $aVideo['user_id'], $aVideo['privacy'], $aVideo['is_friend'], true))
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
        if (($mReturn = Phpfox::getService('videochannel.process')->update($aVideo['video_id'], $aVals)))
        {
                    return array(
                        'result' => 1,
                        'message' =>  Phpfox::getPhrase('videochannel.video_successfully_updated'),
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
        if (!($aVideo = Phpfox::getService('videochannel')->callback($aCallback)->getVideo($iVideoId)))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('videochannel.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }
        // Check the privacy.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('videochannel', $aVideo['video_id'], $aVideo['user_id'], $aVideo['privacy'], $aVideo['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        // Delete video.
        if (Phpfox::getService('videochannel.process')->delete($iVideoId))
        {
            return array('result' => 1, 'error_code' => 0, 'message' =>  Phpfox::getPhrase('videochannel.video_successfully_deleted'));
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
        if (!($aVideo = Phpfox::getService('videochannel')->callback($aCallback)->getVideo($iVideoId)))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('videochannel.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }
        // Check the privacy.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('videochannel', $aVideo['video_id'], $aVideo['user_id'], $aVideo['privacy'], $aVideo['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        return Phpfox::getService('mfox.like')->add(array('sType' => 'videochannel', 'iItemId' => $iVideoId));
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
        if (!($aVideo = Phpfox::getService('videochannel')->callback($aCallback)->getVideo($iVideoId)))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('videochannel.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }

        // Check the privacy.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('videochannel', $aVideo['video_id'], $aVideo['user_id'], $aVideo['privacy'], $aVideo['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        if (isset($aVideo['is_liked']) && $aVideo['is_liked'])
        {
            return Phpfox::getService('mfox.like')->delete(array('sType' => 'videochannel', 'iItemId' => $iVideoId));
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
        if (!($aVideo = Phpfox::getService('videochannel')->callback($aCallback)->getVideo($iVideoId)))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('videochannel.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }

        // Check the privacy.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('videochannel', $aVideo['video_id'], $aVideo['user_id'], $aVideo['privacy'], $aVideo['is_friend'], true))
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
        if (!($aVideo = Phpfox::getService('videochannel')->callback($aCallback)->getVideo($iVideoId)))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('videochannel.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }

        // Check the privacy.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('videochannel', $aVideo['video_id'], $aVideo['user_id'], $aVideo['privacy'], $aVideo['is_friend'], true))
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

    public function getCategory($aCategories, $sPrefix = '')
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
                    'sName' => html_entity_decode($sPrefix . Phpfox::getLib('locale')->convert($aCategory['sName'])),
                    'iLevel' => $aCategory['iLevel'],
                    'iOrdering' => $aCategory['iOrdering']
                );

                if ($aCategory['aChild'])
                {
                    $aTemp = $this->getCategory($aCategory['aChild'], '--');

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
                ->from(Phpfox::getT('channel_category'))
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

    public function fetch($aData)
    {
        return $this->getVideos($aData);
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
        $this->_request()->set(array(
            'view' => !empty($aData['sView']) ? $aData['sView'] : '',
            'page' => !empty($aData['iPage']) ? (int) $aData['iPage'] : 1,
            'show' => !empty($aData['iLimit']) ? (int) $aData['iLimit'] : 10,
            'category' => !empty($aData['iCategory']) ? (int) $aData['iCategory'] : null,
            'search' => !empty($aData['sSearch']) ? $aData['sSearch'] : '',
            'sort' => !empty($aData['sOrder']) ? $aData['sOrder'] : '',
            'module_id' => !empty($aData['sModule']) ? $aData['sModule'] : null,
            'item_id' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : null,
            'profile' => (!empty($aData['bIsUserProfile']) && $aData['bIsUserProfile'] == 'true') ? true : false,
            'profile_id' => !empty($aData['iProfileId']) ? (int) $aData['iProfileId'] : null,
            'tag' => !empty($aData['sTag']) ? $aData['sTag'] : null,
            'sponsor' => !empty($aData['iSponsor']) ? $aData['iSponsor'] : null,
            'channel_id' => !empty($aData['iChannelId']) ? $aData['iChannelId'] : null,
        ));

        if (!Phpfox::getUserParam('videochannel.can_access_videos')) 
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_access_videos'),
            );
        }

        $sModule = $this->_request()->get('module_id', 'videochannel');
        $iItem = $this->_request()->getInt('item_id', 0);

        $aParentModule = null;
        if (!empty($sModule) && !empty($iItem))
        {
            $aParentModule = array(
                'module_id' => $sModule,
                'item_id' => $iItem
            );
        }

        //because only user can add channel can see channel list, so we use this variable to consider showing channel list
        $bCanAddChannelInPage = false;

        if ($aParentModule['module_id'] == 'pages') 
        {
            if ($iItem != 0) 
            {
                if (Phpfox::isAdmin()) 
                {
                    $bCanAddChannelInPage = true;
                }
            }
        }

        $bIsUserProfile = false;
        if ($this->_request()->get('profile') === true)
        {
            $bIsUserProfile = true;
            $aUser = Phpfox::getService('user')->get($this->_request()->get('profile_id'));
        }

        $sView = $this->_request()->get('view');
        
        if ($sView == 'channels' || $sView == 'all_channels') 
        {
            $aBrowseParams = array(
                'module_id' => 'videochannel.channel',
                'alias' => 'm',
                'field' => 'channel_id',
                'table' => Phpfox::getT('channel_channel'),
                'hide_view' => array('pending', 'my'),
                'service' => 'mfox.videochannel'
            );

            // search
            $sSearch = $this->_request()->get('search');
            if (!empty($sSearch))
            {
                $this->_search()->setCondition('AND m.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"');
            }

            // sort
            $sSort = '';
            switch ($this->_request()->get('sort')) 
            {
                case 'latest':
                default:
                    $sSort = 'm.time_stamp DESC';
                    break;
            }

            $this->_search()->setSort($sSort);
        } 
        else
        { 
            $aBrowseParams = array(
                'module_id' => 'videochannel',
                'alias' => 'm',
                'field' => 'video_id',
                'table' => Phpfox::getT('channel_video'),
                'hide_view' => array('pending', 'my'),
                'service' => 'mfox.videochannel'
            );

            // search
            $sSearch = $this->_request()->get('search');
            if (!empty($sSearch))
            {
                $this->_search()->setCondition('AND m.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"');
            }

            // sort
            $sSort = '';
            switch ($this->_request()->get('sort')) 
            {
                case 'most-talked':
                    $sSort = 'm.total_comment DESC';
                    break;

                case 'most-liked':
                    $sSort = 'm.total_like DESC';
                    break;

                case 'most-viewed':
                    $sSort = 'm.total_view DESC';
                    break;

                case 'featured':
                    $sSort = 'm.is_featured DESC';
                    break;

                case 'latest':
                default:
                    $sSort = 'm.time_stamp DESC';
                    break;
            }

            $this->_search()->setSort($sSort);
        }
        
        switch ($sView) 
        {
            case 'pending':
                if (Phpfox::getUserParam('videochannel.can_approve_videos')) 
                {
                    $this->_search()->setCondition('AND m.view_id = 2');
                }
                break;

            case 'my':
                if (!Phpfox::isUser())
                {
                    return array(
                        'error_code' => 1,
                        'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_access_videos'),
                    );
                }
                $this->_search()->setCondition('AND m.user_id = ' . Phpfox::getUserId() . ' AND  m.module_id = \'' . $sModule . '\' AND m.item_id = ' . $iItem);
                break;

            case 'channels':
                if (!Phpfox::isUser())
                {
                    return array(
                        'error_code' => 1,
                        'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_access_videos'),
                    );
                }
                //mm i add a page condition here
                if ($aParentModule['module_id'] == 'pages') 
                {
                    if (Phpfox::isAdmin() || $bCanAddChannelInPage) 
                    {
                        $this->_search()->setCondition('AND  m.module_id = \'' . Phpfox::getLib('database')->escape($aParentModule['module_id']) . '\' AND m.item_id = ' . (int)$aParentModule['item_id'] . ' AND m.user_id = ' . Phpfox::getUserId() . ' AND m.privacy IN(%PRIVACY%)');
                    } 
                    else
                    {
                        if (!Phpfox::isAdmin())
                        {
                            return array(
                                'error_code' => 1,
                                'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_access_videos'),
                            );
                        }
                    }
                } 
                else
                {
                    if (Phpfox::isAdmin() || Phpfox::getUserParam('videochannel.can_add_channels')) 
                    {
                        $this->_search()->setCondition('AND m.user_id = ' . Phpfox::getUserId() . ' AND m.item_id = 0 AND  m.module_id = \'' . $sModule . '\'');
                    } 
                    else
                    {
                        if (!Phpfox::isAdmin())
                        {
                            return array(
                                'error_code' => 1,
                                'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_access_videos'),
                            );
                        }
                    }
                }
                break;

            case 'all_channels':
                if (!Phpfox::isUser())
                {
                    return array(
                        'error_code' => 1,
                        'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_access_videos'),
                    );
                }
                //mm i add a page condition here
                if ($aParentModule['module_id'] == 'pages') 
                {
                    if (Phpfox::isAdmin() || $bCanAddChannelInPage) 
                    {
                        $this->_search()->setCondition('AND  m.module_id = \'' . (isset($aParentModule['module_id']) ? $aParentModule['module_id'] : 'videochannel') . '\' AND m.item_id = ' . (int)$aParentModule['item_id'] . ' AND m.privacy IN(%PRIVACY%)');
                    } 
                    else
                    {
                        if (!Phpfox::isAdmin())
                        {
                            return array(
                                'error_code' => 1,
                                'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_access_videos'),
                            );
                        }
                    }
                } 
                else
                {
                    if (!Phpfox::isAdmin())
                    {
                        return array(
                            'error_code' => 1,
                            'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_access_videos'),
                        );
                    }
                    else
                    {
                        $this->_search()->setCondition(' AND m.item_id = 0 AND m.privacy IN(%PRIVACY%)');
                    }
                }
                break;

            case 'favorite':
                if (!Phpfox::isUser())
                {
                    return array(
                        'error_code' => 1,
                        'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_access_videos'),
                    );
                }
                if ($aParentModule['module_id'] == 'pages') 
                {
                    $this->_search()->setCondition(' AND m.module_id = \'' . $sModule . '\' AND m.item_id = ' . $iItem . ' AND m.video_id IN (SELECT f.item_id FROM ' . Phpfox::getT('favorite') . ' f WHERE f.user_id =' . Phpfox::getUserId() . ' )');
                } 
                else
                {
                    $this->_search()->setCondition(' AND m.module_id = \'videochannel\' AND m.video_id IN (SELECT f.item_id FROM ' . Phpfox::getT('favorite') . ' f WHERE f.user_id =' . Phpfox::getUserId() . ' )');
                }
                break;

            default:
                if ($bIsUserProfile) 
                {
                    $this->_search()->setCondition('AND m.module_id = \'videochannel\' AND  m.in_process = 0 AND m.view_id ' . ($aUser['user_id'] == Phpfox::getUserId() ? 'IN(0,2)' : '= 0') . ' AND m.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ') AND m.user_id = ' . (int)$aUser['user_id']);
                } 
                else
                {
                    if ($aParentModule['module_id'] == 'pages') 
                    {
                        $this->_search()->setCondition('AND m.in_process = 0 AND m.view_id = 0 AND m.module_id = \'' . Phpfox::getLib('database')->escape($aParentModule['module_id']) . '\' AND m.item_id = ' . (int)$aParentModule['item_id'] . ' AND m.privacy IN(%PRIVACY%)');
                    } 
                    else
                    {
                        $this->_search()->setCondition('AND m.in_process = 0 AND m.view_id = 0 AND m.module_id = \'videochannel\' AND m.item_id = 0 AND m.privacy IN(%PRIVACY%)');
                    }
                }
                break;
        }
        
        $sTagSearchValue = null;
        $sTmpTag = $this->_request()->get('tag');
        if (!empty($sTmpTag)) 
        {
            $sTagSearchValue = $this->_request()->get('tag');
        }
        
        $sTmpCategory = $this->_request()->get('category');
        if (!empty($sTmpCategory)) 
        {
            $sCategory = $this->_request()->getInt('category');
            $this->_search()->setCondition('AND mcd.category_id = ' . (int)$sCategory);
        }
        
        if ($this->_request()->getInt('sponsor') == 1) 
        {
            $this->_search()->setCondition('AND m.is_sponsor != 1');
        }
        
        if ($sView == 'featured') 
        {
            $this->_search()->setCondition('AND m.is_featured = 1 AND m.privacy = 0');
        }

        $this->_search()->browse()->params($aBrowseParams)->execute();

        $aRows = $this->_search()->browse()->getRows();

        return $aRows;
    }

    public function processRows(&$aRows)
    {
        $aTmpRows = $aRows;
        $aRows = array();

        foreach ($aTmpRows as $aRow)
        {
            $aRows[] = $this->processRow($aRow);
        }
    }

    public function query()
    {
        $sView = $this->_request()->get('view');
        if ($sView == 'channels' || $sView == 'all_channels')
        {
            return $this->queryChannel();
        }

        return Phpfox::getService('videochannel.browse')->query();
    }

    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        $sView = $this->_request()->get('view');
        if ($sView == 'channels' || $sView == 'all_channels')
        {
            return $this->getQueryJoinsChannel($bIsCount, $bNoQueryFriend);
        }

        if (Phpfox::isModule('friend') && Mfox_Service_Friend::instance()->queryJoin($bNoQueryFriend))
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = m.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());   
        }

        if ($this->_request()->get('tag'))
        {
            $this->database()->innerJoin(Phpfox::getT('tag'), 'tag', 'tag.item_id = m.video_id AND tag.category_id = \''.(defined('PHPFOX_GROUP_VIEW') ? 'video_group' : 'videochannel').'\' AND tag_text = "'. $this->_request()->get('tag') . '"');
            if (!$bIsCount)
            {
                $this->database()->group('m.video_id');
            }
        }
        
        if ($this->_request()->get('category'))
        {       
            $this->database()->innerJoin(Phpfox::getT('channel_category_data'), 'mcd', 'mcd.video_id = m.video_id');
            if (!$bIsCount)
            {
                $this->database()->group('m.video_id');
            }
        }
        
        if ($this->_request()->get('channel_id'))
        {       
            $this->database()->join(Phpfox::getT('channel_channel_data'), 'chd', 'chd.video_id = m.video_id And chd.channel_id = '.$this->_request()->get('channel_id'));         
            if (!$bIsCount)
            {
                $this->database()->group('m.video_id');
            }
        }
    }

    public function processRow($aRow)
    {
        $sView = $this->_request()->get('view');
        if ($sView == 'channels' || $sView == 'all_channels')
        {
            return $this->processRowChannel($aRow);
        }
        $bIsFavourite = Phpfox::getService('videochannel')->isFavourite($aRow['video_id']);

        $aTemp = array(
            'iVideoId' => $aRow['video_id'],
            'bInProcess' => $aRow['in_process'],
            'bIsStream' => $aRow['is_stream'],
            'bIsFeatured' => $aRow['is_featured'],
            'bIsSpotlight' => $aRow['is_spotlight'],
            'bIsSponsor' => $aRow['is_sponsor'],
            'iViewId' => $aRow['view_id'],
            'sModuleId' => $aRow['module_id'],
            'sModelType' => 'videochannel', //override client model type
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
                'path' => 'core.url_pic',
                'file' => $aRow['image_path'],
                'suffix' => '_480',
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
            'bIsFavourite' => $bIsFavourite,
        );

        return Phpfox::getService('mfox.helper.videochannel')->retrieveMoreInfo($aRow, $aTemp);
    }

    /**
     * Query for channel
     */
    public function queryChannel()
    {
        return Phpfox::getService('videochannel.channel.browse')->query();
    }

    /**
     * Query joins for channel
     */
    public function getQueryJoinsChannel($bIsCount = false, $bNoQueryFriend = false)
    {
        if ($this->_request()->get('category'))
        {       
            $this->database()->innerJoin(Phpfox::getT('channel_category_data'), 'mcd', 'mcd.channel_id = m.channel_id')->innerJoin(Phpfox::getT('channel_category'),'mc','mc.category_id = mcd.category_id');
            if (!$bIsCount)
            {
                $this->database()->group('m.channel_id');
            }
        }
    }

    /**
     * Process channel row
     * @param array $aRow
     * @return array
     * @todo define and implement return keys
     */
    public function processRowChannel($aRow)
    {
        $cId = $aRow['channel_id'];
        $aVideo = Phpfox::getService('videochannel.channel.process')->getVideosBelongChannel($cId, 1);
        if (isset($aVideo[0])) 
        {
            $aRow['video_image'] = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aVideo[0]['image_server_id'],
                'path' => 'core.url_pic',
                'file' => $aVideo[0]['image_path'],
                'suffix' => '_480',
                'return_url' => true
            ));
        } 
        else
        {
            $aRow['video_image'] = Phpfox::getParam('core.url_module') . 'videochannel/static/image/no_item.jpg';
        }
        $aRow['en_title'] = base64_encode($aRow['title']);
        $aRow['en_summary'] = base64_encode($aRow['summary']);
        $aRow['en_url'] = base64_encode($aRow['url']);
        $aRow['en_video_image'] = base64_encode($aRow['video_image']);
        $aRow['isExist'] = $cId;
        $aRow['isBrowse'] = true;
        $aRow['link'] = ($this->_aCallback !== false ? Phpfox::getLib('url')->makeUrl($this->_aCallback['url'][0], array_merge($this->_aCallback['url'][1], array(
            $aRow['title']
        ))) : Phpfox::permalink('videochannel.channel', $aRow['channel_id'], $aRow['title']));

        $iNumberVideos = $this->database()->select('COUNT(*) as total')
                ->from(Phpfox::getT('channel_channel_data'))
                ->where('channel_id = ' . (int) $cId)
                ->execute('getField');
        $aTemp = array(
            'iChannelId' => $aRow['channel_id'],
            'sModuleId' => $aRow['module_id'],
            'sModelType' => 'videochannel-channel', //override client model type
            'iItemId' => $aRow['item_id'],
            'iPrivacy' => $aRow['privacy'],
            'iPrivacyComment' => $aRow['privacy_comment'],
            'sTitle' => $aRow['title'],
            'iUserId' => $aRow['user_id'],
            'sVideoImage' => $aRow['video_image'],
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
            'sChannelFeedUrl' => $aRow['url'],
            'iNumberVideos' => $iNumberVideos,
            'bCanEdit' => $this->canEditChannel($aRow),
            'bCanDelete' => $this->canDeleteChannel($aRow),
            'bCanAutoUpdate' => $this->canAutoUpdateChannel($aRow),

        );

        return $aTemp;
    }

    public function canEdit($aItem){
        if($aItem['user_id'] ==  Phpfox::getUserId()){
            return Phpfox::getUserParam('videochannel.can_edit_own_video');
        }
        return Phpfox::getUserParam('videochannel.can_edit_other_video');
    }

    public function canDelete($aItem){
        if($aItem['user_id'] ==  Phpfox::getUserId()){
            return Phpfox::getUserParam('videochannel.can_delete_own_video');
        }
        return Phpfox::getUserParam('videochannel.can_delete_other_video');
    }

    public function canEditChannel($aItem){

        if((($aItem['user_id'] == Phpfox::getUserId()) || Phpfox::isAdmin()) && Phpfox::getUserParam('videochannel.can_add_channels')){

            return true;
        }

        return false;
    }

    public function canDeleteChannel($aItem){
        if (($aItem['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('videochannel.can_delete_own_video')) 
            || (Phpfox::getUserParam('videochannel.can_delete_other_video') && $aItem['user_id'] != Phpfox::getUserId())){
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
        $aTag = Phpfox::getService('tag')->getTagInfo('videochannel', $sTag);

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
                ->from(Phpfox::getT('channel_video'), 'v')
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
            $sPhrase =  Phpfox::getPhrase('videochannel.user_name_liked_gender_own_video_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...'), 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1)));
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('videochannel.user_name_liked_your_video_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('videochannel.user_name_liked_span_class_drop_data_user_full_name_s_span_video_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aRow['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
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
                ->from(Phpfox::getT('channel_video'), 'l')
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
            $sPhrase =  Phpfox::getPhrase('videochannel.user_name_commented_on_gender_video_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('videochannel.user_name_commented_on_your_video_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('videochannel.user_name_commented_on_span_class_drop_data_user_full_name_s_span_video_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aRow['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aRow['title'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }

        return array(
            'link' => array(
                'iVideoId' => $aRow['video_id'],
                'sVideoTitle' => $aRow['title']
            ),
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog'),
            'sModule' => 'videochannel',
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
                ->from(Phpfox::getT('channel_video'), 'm')
                ->where('m.video_id = ' . (int) $iId)
                ->execute('getSlaveRow');

        if (!isset($aRow['video_id']))
        {
            return false;
        }

        return array(
            'sModule' => 'videochannel',
            'iVideoId' => $aRow['video_id'],
            'sTitle' => $aRow['title'],
            'sCommentType' => 'videochannel'
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

        if (Phpfox::getService('videochannel.process')->deleteImage($iVideoId))
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
            'message' => Phpfox_Error::get()
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
        $aVideo = Phpfox::getService('videochannel')->callback(array('sModule' => $sModule, 'iItem' => $iItem))->getVideo($iVideoId);

        if (!isset($aVideo['video_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('videochannel.the_video_you_are_looking_for_does_not_exist_or_has_been_removed'));
        }

        $aEmbedVideo = $this->database()->select('video_url, embed_code')
                ->from(Phpfox::getT('channel_video_embed'))
                ->where('video_id = ' . $aVideo['video_id'])
                ->execute('getslaveRow');

        if (isset($aEmbedVideo['video_url']) && !empty($aEmbedVideo['video_url']) && (preg_match('/youtube/i', $aEmbedVideo['video_url'], $aYouTubeMatch) || preg_match('/youtu\.be\/(.*)/i', $aEmbedVideo['video_url'], $aYouTubeMatchBe))) {
            if (isset($aYouTubeMatchBe))
            {
                $aVideo['youtube_video_url'] = $aYouTubeMatchBe[1];
            }
            else
            {
                $sUrl = parse_url($aEmbedVideo['video_url'], PHP_URL_QUERY);
                $aUrlParts = explode('&', $sUrl);
                foreach ($aUrlParts as $sPart)
                {
                    if (strpos($sPart, 'v=') !== false)
                    {
                        $aVideo['youtube_video_url'] = str_replace('v=', '', $sPart);
                        break;
                    }
                }                   
            }       
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

        $aUserDislike = array();
        $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('videochannel', $aVideo['video_id'], $bGetCount = false);
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
            'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('videochannel', $aVideo['video_id'], Phpfox::getUserId()),
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
            'iTotalScore' => (float)($aVideo['total_score']/2),            
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
            'sEmbed' => empty($aVideo['youtube_video_url']) ? '' : '<iframe width="420" height="315" src="http://www.youtube.com/embed/' . $aVideo['youtube_video_url']  . '?showinfo=0&wmode=opaque' . '" frameborder="0" allowfullscreen></iframe>',
            'iTotalUserVideos' => $aVideo['total_user_videos'],
            'sVideoWebLink' => Phpfox::getLib('url')->permalink('mobile.videochannel', $aVideo['video_id'], $aVideo['title']),
            'sTopic' => $sTags,
            'sModelType'=>'videochannel',
            'bCanPostComment' => $bCanComment,  // deprecated.
            'bCanComment' => $bCanComment,
            'bCanEdit' => $this->canEdit($aVideo),
            'bCanDelete' => $this->canDelete($aVideo),
        );

        if (Phpfox::isModule('track') && !$aVideo['video_is_viewed'])
        {
            Phpfox::getService('track.process')->add('videochannel', $aVideo['video_id']);
        }

        $aReturn = Phpfox::getService('mfox.helper.videochannel')->retrieveMoreInfo($aVideo, $aRow);

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
        $aVideo = Phpfox::getService('videochannel')->callback($aCallback)->getVideo($iVideoId);
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
                    ->from(Phpfox::getT('channel_video'), 'v')
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

        if (($aVideo['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('videochannel.can_delete_own_video')) || Phpfox::getUserParam('videochannel.can_delete_other_video') || isset($bOverPass))
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
                Phpfox::getService('user.space')->update($aVideo['user_id'], 'videochannel', $iFileSize, '-');
            }

            (Phpfox::isModule('comment') ? Phpfox::getService('comment.process')->deleteForItem(null, $aVideo['video_id'], 'videochannel') : null);
            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('videochannel', $aVideo['video_id']) : null);
            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('videochannel_comment', $aVideo['video_id']) : null);

            (Phpfox::isModule('tag') ? Phpfox::getService('tag.process')->deleteForItem($aVideo['user_id'], $aVideo['video_id'], 'videochannel') : null);

            $this->database()->delete(Phpfox::getT('channel_video'), 'video_id = ' . $aVideo['video_id']);
            $this->database()->delete(Phpfox::getT('channel_category_data'), 'video_id = ' . $aVideo['video_id']);
            $this->database()->delete(Phpfox::getT('channel_video_rating'), 'item_id = ' . $aVideo['video_id']);
            $this->database()->delete(Phpfox::getT('channel_video_text'), 'video_id = ' . $aVideo['video_id']);
            $this->database()->delete(Phpfox::getT('channel_video_embed'), 'video_id = ' . $aVideo['video_id']);

            // Update user activity
            Phpfox::getService('user.activity')->update($aVideo['user_id'], 'videochannel', '-');

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

        return Phpfox_Error::set( Phpfox::getPhrase('videochannel.invalid_permissions'));
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
        if (!Phpfox::getUserParam('videochannel.can_access_videos'))
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
            ->from(Phpfox::getT('channel_video'), 'v')
            ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'videochannel\' AND l.item_id = v.video_id AND l.user_id = ' . Phpfox::getUserId())
            ->leftJoin(Phpfox::getT('channel_video_text'), 'vt', 'vt.video_id = v.video_id')
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
        
        if ((defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'videochannel.view_browse_videos'))
            || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aRow['item_id'], 'videochannel.view_browse_videos'))    
            )
        {
            return false;
        }
        /**
         * @var array
         */
        $aReturn = array(
            'sTypeId' => 'videochannel',
            'iUserId' => $aRow['user_id'],
            'sUsername' => $aRow['user_name'],
            'sFullName' => $aRow['full_name'],

            'sFeedTitle' => $aRow['title'],
            'sFeedLink' => Phpfox::permalink('videochannel', $aRow['video_id'], $aRow['title']),
            'sFeedContent' => $aRow['text_parsed'],
            'iTotalComment' => $aRow['total_comment'],
            'iFeedTotalLike' => $aRow['total_like'],
            'bFeedIsLiked' => $aRow['is_liked'],
            'sFeedIcon' => Phpfox::getLib('image.helper')->display(array('theme' => 'module/video.png', 'return_url' => true)),
            'iTimeStamp' => $aRow['time_stamp'],
            'Time' => date('l, F j, o', (int) $aRow['time_stamp']) . ' at ' . date('h:i a', (int) $aRow['time_stamp']),
            'TimeConverted' => Phpfox::getLib('date')->convertTime($aRow['time_stamp'], 'comment.comment_time_stamp'),
            'bEnableLike' => true,          
            'sCommentTypeId' => 'videochannel',
            'sLikeTypeId' => 'videochannel',
            
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
                $aReturn['sFeedMiniContent'] =  Phpfox::getPhrase('feed.full_name_posted_a_href_link_a_video_a_on_a_href_profile_parent_full_name_a_s_a_href_profile_link_wall_a', array('full_name' => Phpfox::getService('user')->getFirstName($aItem['full_name']), 'link' => Phpfox::permalink('videochannel', $aRow['video_id'], $aRow['title']), 'profile' => Phpfox::getLib('url')->makeUrl($aRow['parent_user_name']), 'parent_full_name' => $aRow['parent_full_name'], 'profile_link' => Phpfox::getLib('url')->makeUrl($aRow['parent_user_name'])));
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
                        'path' => 'core.url_pic',
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
            $aVals['sItemType'] = 'videochannel';
            $aVals['iItemId'] = $aData['iVideoId'];
            $aVals['iRating'] = $aData['iRating'];

            $bResult = Phpfox::getService('mfox.helper.rate')->rate($aVals);
            if(!$bResult) {
                return array( 
                    'error_code' => 1,
                    'error_message' => implode(' ', Phpfox_Error::get())
                );
            } else {
                $aVideo = Phpfox::getService('mfox.helper.videochannel')->getSimpleVideoById($aData['iVideoId']);
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
                $aVideo = Phpfox::getService('mfox.helper.videochannel')->getSimpleVideoById($iVideoId);
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
            'bIsAdvancedModule'=> 1,
            'view_options'=> Phpfox::getService('mfox.privacy')->privacy($aData),
            'comment_options'=> $this->_privacyComment($aData),
            'category_options'=> $this->categories($aData),
            'bCanUploadVideo'=> $this->canUpload($aData),
        );
        
        $iValue =  Phpfox::getService('user.privacy')->getValue('videochannel.display_on_profile');
        $response['default_privacy_setting'] = Phpfox::getService('mfox.privacy')->defaultprivacy("$iValue", $response['view_options']);

        return $response;
    }

    private function _privacyComment($aData)
    {
        $aData['bPrivacyNoCustom'] = true;
        
        return Phpfox::getService('mfox.privacy')->privacy($aData);
    }

    public function getTextByVideoId($iVideoId){
        return $this->database()->select('vt.*')
            ->from(Phpfox::getT('channel_video_text'), 'vt')
            ->where('vt.video_id = ' . (int) $iVideoId)
            ->execute('getSlaveRow');
    }

    public function canUpload($aData)
    {
        return false;
    }

    /**
     * Mfox_Service_Request_Request
     */
    private function _request()
    {
        return Mfox_Service_Request_Request::instance();
    }

    /**
     * Mfox_Service_Search_Search
     */
    private function _search()
    {
        return Mfox_Service_Search_Search::instance();
    }

    public function favourite($aData){
        $bIsFavourite = Phpfox::getService('videochannel')->isFavourite($aData['iItemId']);
        if($bIsFavourite != $aData['bIsFavourite']){
            if($bIsFavourite){
                Phpfox::getService('videochannel.process')->unfavouriteVideo($aData['iItemId']);
                return array('result' => 0, 'error_code' => 0, 'message'=>html_entity_decode(_p('Unfavorite successfully!')));
            }else{
                phpFox::getService('videochannel')->addToFavorite('videochannel', $aData['iItemId']);
                return array('result' => 0, 'error_code' => 0, 'message'=>html_entity_decode(_p('Favorite successfully!')));
            }
        }else{
            return array('result' => 0, 'error_code' => 1);
        }
        
    }

    public function autoUpdate($aData){

        $oSerVideoChannelChannelProcess = Phpfox::getService('videochannel.channel.process');
        $oSerVideoChannel = Phpfox::getService('videochannel');

        Phpfox::isUser(true);
        $sModule = 'videochannel';

        $iItemId = $aData['iItemId'];
        
        if (!empty($iItemId))
        {
            // Get the channel.
            $aChannel = $oSerVideoChannelChannelProcess->getChannel($iItemId);
            
            if (($aChannel['user_id'] != Phpfox::getUserId()) && !Phpfox::isAdmin())
            {
                return array('result' => 0, 'error_code' => 1, 'error_message' => html_entity_decode(_p('videochannel.invalid_permissions')));
            }
            else
            {
                $iTotalAdded = (int) $oSerVideoChannelChannelProcess->updateChannels($iItemId);                
                $iNumberVideos = $oSerVideoChannelChannelProcess->getTotalAddedVideosOfChannel($iItemId);
                if ($iTotalAdded > 0)
                {

                    return array('result' => 0, 'error_code' => 0, 'message'=>html_entity_decode(_p('videochannel.total_x_videos_successfully_added', array('iTotal' => $iTotalAdded))), 'iNumberVideos'=>$iNumberVideos);
                }
                else
                {
                    $sMessage = _p('videochannel.no_new_videos_found');
                    return array('result' => 0, 'error_code' => 0, 'message'=>html_entity_decode(_p('videochannel.no_new_videos_found')), 'iNumberVideos' => $iNumberVideos);
                }
            }
            
        }

    }

    public function addChannelUrl($aData){
        Phpfox::isUser();
        $sModule = 'videochannel';
        $iItem = 0;
        if ($aData['sModule'])
        {
            $sModule = $aData['sModule'];
            $iItem = $aData['iItem'];
        }
        Phpfox::getService('videochannel')->getCanAddChannel($sModule, $iItem);
        $sUrl = $aData['sUrl'];
        if (!empty($sUrl))
        {
            if (Phpfox::getService('videochannel.channel.grab')->getGdataUrl($sUrl))
            {
                $api_key = 'AIzaSyDpUPT_nafV_MFSAlc-8AH4e1Gy578iK0M';
                $pattern = "/((http|https):\/\/|)(www\.|)youtube\.com\/(channel|user)\/([a-zA-Z0-9-_]{1,})/";
                $aMatch = array();
                preg_match($pattern, $sUrl . '?', $aMatch);
                if(!$aMatch)
                {
                    return array('result' => 0, 'error_code' => 1, 'error_message' => html_entity_decode(_p('videochannel.please_provide_a_valid_url_for_your_channel')));
                }
                $for = $aMatch[4];
                $id = $aMatch[5];
                $sChannelFeedUrl = '';
                $info = array();
                switch ($for) 
                {
                    case 'user':
                        $url = "https://www.googleapis.com/youtube/v3/channels?part=snippet&forUsername=$id&key=$api_key";
                        $data = @file_get_contents($url);
                        $data = json_decode($data);
                        $items = $data -> items;
                        if(count($items))
                        {
                            $info = $items[0] -> snippet;
                            $channelId = $items[0] -> id;
                            $sChannelFeedUrl = "https://www.googleapis.com/youtube/v3/search?key=$api_key&channelId=$channelId&part=snippet&order=date";
                        }
                        break;
                    
                    case 'channel':
                        $sChannelFeedUrl = "https://www.googleapis.com/youtube/v3/search?key=$api_key&channelId=$id&part=snippet&order=date";
                        $url = "https://www.googleapis.com/youtube/v3/search?part=snippet&channelId=$id&key=$api_key&maxResults=1";
                        $data = @file_get_contents($url);
                        


                        $data = json_decode($data);
                        $items = $data -> items;
                        if(count($items))
                        {
                            if(!empty($items[0] -> snippet -> channelTitle))
                            {
                                $userName = $items[0] -> snippet -> channelTitle;
                                $url = "https://www.googleapis.com/youtube/v3/channels?part=snippet&forUsername=$userName&key=$api_key";
                                $data = @file_get_contents($url);
                                $data = json_decode($data);
                                if(!empty($data))
                                    $items = $data -> items;
                            }
                            if(count($items))
                            {
                                $info = $items[0] -> snippet;
                            }
                        }
                        break;
                }

                if (Phpfox::getService('videochannel.channel.process')->isExist($sChannelFeedUrl, $sModule, $iItem))
                {
                    return array('result' => 0, 'error_code' => 1, 'error_message' => html_entity_decode(_p("videochannel.this_channel_is_already_added")));
                }
                $sTitle = null;
                $sDescription = null;
                $sImg = null;
                if ($info)
                {
                    $sTitle = $info->channelTitle;
                    $sDescription = $info->description;
                    $sImg = $info -> thumbnails -> high -> url;
                }

                $channelInfo = array(
                    'sSiteId' => 'youtube',
                    'sTitle' => $sTitle,
                    'sChannelFeedUrl' => $sChannelFeedUrl,
                    'sDescription' => $sDescription,
                    'sImg' => $sImg,
                    'iChannelId' => 0
                );
                return array('result' => 0, 'error_code' => 0, 'message'=>html_entity_decode(_p('')), 'channelInfo' => $channelInfo);

            }
        }
        return array('result' => 0, 'error_code' => 1, 'error_message' => html_entity_decode(_p('videochannel.please_provide_a_valid_url_for_your_channel')));
    }

    public function getVideoList($aData){
        Phpfox::isUser(true);
        $oSerVideoChannelChannelProcess = Phpfox::getService('videochannel.channel.process');
        $sUrl = '';
        $iChannelId = 0;

        if(isset($aData['sUrl']))
            $sUrl = $aData['sUrl'];
        if(isset($aData['iChannelId']))
            $iChannelId = (int) $aData['iChannelId'];

        //Limit videos per page
        $iLimit = 6;

        //How many video can grab by user
        $iMaxNum = Phpfox::getUserParam('videochannel.channel_add_videos_limit');

        //Grab videos
        $aVideos = array();

        if (!empty($sUrl))
        {            
            $aVideos = Phpfox::getService('videochannel.channel.process')->getVideos($sUrl, $iMaxNum, false);
            return array('result' => 0, 'error_code' => 0, 'message'=>html_entity_decode(_p('')), 'aVideos' => $aVideos, 'iVideoCount' => count($aVideos), 'iLimit' => $iLimit);
        }elseif(!empty($iChannelId)){
            $aChannel = $oSerVideoChannelChannelProcess->getChannel($iChannelId);
            if($aChannel){
                $sUrl = $aChannel['url'];
                $aVideos = Phpfox::getService('videochannel.channel.process')->getVideos($sUrl, $iMaxNum, false);
                return array('result' => 0, 'error_code' => 0, 'message'=>html_entity_decode(_p('')), 'aVideos' => $aVideos, 'iVideoCount' => count($aVideos), 'iLimit' => $iLimit);
            }

        }
        return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(_p('Please provide valid channel link or ID')));

    }

    public function saveChannel($aData){
        Phpfox::isUser(true);
        $oSerVideoChannelChannelProcess = Phpfox::getService('videochannel.channel.process');
        $oSerVideoChannel = Phpfox::getService('videochannel');
        $oLibParseInput = Phpfox::getLib('parse.input');
        
        $sModule = 'videochannel';
        $iItem = 0;

        $oSerVideoChannel->getCanAddChannel($sModule, $iItem);

        $aChannel = array(); //Channel information        
        $aVideos = array(); //Output video list

        $sVideos = $aData['aVideos']; //Selected videos
        
        if (!empty($aData))
        {
            $sChannelTitle = $oLibParseInput->clean($aData['sTitle']);
            
            if (empty($sChannelTitle))
            {
                return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(_p('videochannel.enter_channel_title')));     
            }
            
            //Check if not provide a category
            if (empty($aData['iCategoryId']))
            {
                return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(_p('videochannel.provide_a_category_channels_will_belong_to')));  
            }
            
            //Set channel info
            $aChannel['site_id'] = $aData['sSiteId'];
            $aChannel['url'] = $aData['sChannelFeedUrl'];
            $aChannel['title'] = $sChannelTitle;
            $aChannel['summary'] = $oLibParseInput->clean($aData['sDescription']);
            $aChannel['user_id'] = Phpfox::getUserId();
            $aChannel['category'][] = $aData['iCategoryId'];
            $aChannel['privacy'] = (isset($aData['auth_view']) ? $aData['auth_view'] : '0');
            $aChannel['privacy_comment'] = (isset($aData['auth_comment']) ? $aData['auth_comment'] : '0');
            
            $aChannel['callback_module'] = 'videochannel';
            $aChannel['callback_item_id'] = 0;
            
            //If channel is exits (edit action)
            if (($iChannelId = $oSerVideoChannelChannelProcess->isExist($aChannel['url'], $sModule, $iItem)))
            {
                $aChannel['channel_id'] = $iChannelId;
                
                $oEditedCh = $oSerVideoChannelChannelProcess->editChannel($aChannel);

                $aParentModule = $oSerVideoChannelChannelProcess->getChannelParentModule($iChannelId);
                
                if (!empty($sVideos))
                {
                    foreach ($sVideos as $key => $sValue)
                    {
                        $aVideo = array();
                        $aVideo['category'] = $aChannel['category'];
                        $aVideo['url'] = $sValue;
                        $aVideo['user_id'] = $aChannel['user_id'];
                        $aVideo['channel_id'] = $aChannel['channel_id'];
                        $aVideo['privacy'] = $aChannel['privacy'];
                        $aVideo['privacy_comment'] = $aChannel['privacy_comment'];
                        
                        if ($aParentModule)
                        {
                            $aVideo['callback_module'] = $aParentModule['module_id'];
                            $aVideo['callback_item_id'] = $aParentModule['item_id'];
                        }
                        $aVideos[] = $aVideo;
                    }

                    $aVideos = array_reverse($aVideos); //latest videos will be inserted last.
                    
                    $oSerVideoChannelChannelProcess->addVideos($aVideos);

                }
                
                
                return array('result' => 0, 'error_code' => 0, 'message'=>html_entity_decode(_p('Channel has been updated successfully')), 'iChannelId'=>$aChannel['channel_id']);  
                
            }
            else
            {
                //add a channel and its categories
                $oAddedCh = $oSerVideoChannelChannelProcess->addChannel($aChannel);
                
                if (!$oAddedCh['channel_id'])
                {
                    return 'videochannel.add_channel_failed';
                }
                
                if (!empty($sVideos))
                {
                    foreach ($sVideos as $key => $sValue)
                    {
                        $aVideo = array();
                        $aVideo['category'] = $aChannel['category'];
                        $aVideo['url'] = $sValue;
                        $aVideo['user_id'] = $aChannel['user_id'];
                        $aVideo['channel_id'] = $oAddedCh['channel_id'];
                        $aVideo['privacy'] = $aChannel['privacy'];
                        
                        $aVideo['callback_module'] = 'videochannel';
                        $aVideo['callback_item_id'] = 0;
                        
                        $aVideo['privacy_comment'] = (isset($aData['auth_comment']) ? $aData['auth_comment'] : '0');
                        $aVideos[] = $aVideo;
                    }

                    $aVideos = array_reverse($aVideos); //latest videos will be inserted last.
                    
                    $oSerVideoChannelChannelProcess->addVideos($aVideos);
                }
                //Added success        
                return array('result' => 0, 'error_code' => 0, 'message'=>html_entity_decode(_p('Channel has been added successfully')), 'iChannelId'=>$oAddedCh['channel_id']);        
            }
        }

    }

    public function addMoreVideo($aData){
        Phpfox::isUser(true);
        $oSerVideoChannelChannelProcess = Phpfox::getService('videochannel.channel.process');
        $oSerVideoChannel = Phpfox::getService('videochannel');
        
        $sModule = 'videochannel';
        $iItem = 0;

        $oSerVideoChannel->getCanAddChannel($sModule, $iItem);

        $iChannelId = 0;
        $sVideos = '';

        if(isset($aData['iChannelId']))
            $iChannelId = $aData['iChannelId'];
        if(isset($aData['aVideos']))
            $sVideos = $aData['aVideos'];

        if(empty($iChannelId))
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(_p('Please provide a valid channel ID'))); 
        
        if (!empty($sVideos))
        {
            $aChannel = $oSerVideoChannelChannelProcess->getChannel($iChannelId);
            if($aChannel){
                foreach ($sVideos as $key => $sValue)
                {
                    $aVideo = array();
                    $aVideo['category'] = $aChannel['category'];
                    $aVideo['url'] = $sValue;
                    $aVideo['user_id'] = $aChannel['user_id'];
                    $aVideo['channel_id'] = $aChannel['channel_id'];
                    $aVideo['privacy'] = $aChannel['privacy'];
                    $aVideo['privacy_comment'] = $aChannel['privacy_comment'];
                    
                    if ($aParentModule)
                    {
                        $aVideo['callback_module'] = $aParentModule['module_id'];
                        $aVideo['callback_item_id'] = $aParentModule['item_id'];
                    }
                    $aVideos[] = $aVideo;
                }
                $aVideos = array_reverse($aVideos); //latest videos will be inserted last.
                    
                $oSerVideoChannelChannelProcess->addVideos($aVideos);
            }

            //Added success                
            return array('result' => 0, 'error_code' => 0, 'message'=>html_entity_decode(_p('Added successfully')), 'iChannelId' => $aChannel['channel_id']);
        }
        return array('result' => 0, 'error_code' => 0, 'message'=>html_entity_decode(_p('There are no videos added')), 'iChannelId' => $iChannelId); 
        
    }

    public function findChannel($aData){
        $sModule = 'videochannel';
        $iItem = 0;
        $sTitle = Phpfox::getLib('parse.input')->clean(preg_replace('/\'/', "", $aData['keywords']));
        if(($aData['iPage'] == 1) || !isset($_SESSION['mobi_keywords']) || $_SESSION['mobi_keywords'] != $sTitle){
            $_SESSION['mobi_keywords'] = $sTitle;
            $_SESSION['mobi_page_token'] = "";
        }

        //Search channels
        if ($sTitle != "")
        {
            $aChannels = array();  //Array for found channels
            $sQuery = urlencode($sTitle); //Set search query
            
            $iMaxResult = Phpfox::getUserParam('videochannel.channel_search_results'); //Set max result per page
            if ($iMaxResult > 50)
                $iMaxResult = 50;

            $sPageToken = $_SESSION['mobi_page_token'];


            //Set key
            $api_key = 'AIzaSyDpUPT_nafV_MFSAlc-8AH4e1Gy578iK0M';
            
           //Generate feed URL
            $sFeedUrl = 'https://www.googleapis.com/youtube/v3/search?order=title&part=snippet&q='.$sQuery.'&key=' . $api_key . '&pageToken='
                    . $sPageToken . '&maxResults=' . $iMaxResult;
            //Find channels  search channels
            $aChannels = Phpfox::getService('videochannel.channel.process')->getChannels($sFeedUrl, $sPageTokenPrev, $sPageTokenNext, $sModule, $iItem);

            $aChannels = array_reverse($aChannels);
            $_SESSION['mobi_page_token'] = $sPageTokenNext;
            return $aChannels;
            // return $aChannels;
            return array('result' => 0, 'error_code' => 0, 'message'=>html_entity_decode(_p('')), 'aChannels' => $aChannels, 'sPageToken' => $sPageToken, 'sPageTokenPrev' => $sPageTokenPrev, 'sPageTokenNext' => $sPageTokenNext);
        }
        else{
            return "asdf";
        }
    }

    public function detailChannel($aData){
        $aChannel = Phpfox::getService('videochannel.channel.process')->getChannel($aData['iChannelId']);

        $aCategory = Phpfox::getService('videochannel.channel.process')->getCategory($aData['iChannelId']);

        // Redefine return values
        $aTemp = [
            'iChannelId' => $aChannel['channel_id'],
            'iViewPrivacy' => $aChannel['privacy'],
            'iCommentPrivacy' => $aChannel['privacy_comment'],
            'sSummary' => $aChannel['summary'],
            'sTitle' => $aChannel['title'],
            'iNumberVideos' => count($aChannel['videos']),
            'iTimestamp' => $aChannel['time_stamp'],
            'iCategoryId' => $aCategory[0]['category_id'],

        ];
        if (isset($aChannel['videos'][0])) 
        {
            $aTemp['sVideoImage'] = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aChannel['videos'][0]['image_server_id'],
                'path' => 'core.url_pic',
                'file' => $aChannel['videos'][0]['image_path'],
                'suffix' => '_480',
                'return_url' => true
            ));
        } 
        else
        {
            $aTemp['sVideoImage'] = Phpfox::getParam('core.url_module') . 'videochannel/static/image/no_item.jpg';
        }
        return $aTemp;
    }

    public function deleteChannel($aData){
        Phpfox::isUser(true);
        $sModule = 'videochannel';
        $iItem = 0;
        $oSerVideoChannelChannelProcess = Phpfox::getService('videochannel.channel.process');
        $oSerVideoChannel = Phpfox::getService('videochannel');
        
        $oSerVideoChannel->getCanAddChannel($sModule, $iItem);

        if ($oSerVideoChannelChannelProcess->deleteChannel($aData['iChannelId'], true))
        {
            return array('result' => 0, 'error_code' => 0, 'message'=>html_entity_decode(_p('Delete channel successfully')));
        }
        else
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(_p('videochannel.could_not_delete_this_channel')));
        }
    }

    public function canAutoUpdateChannel($aChannel){
        if($aChannel['user_id'] == Phpfox::getUserId() || Phpfox::isAdmin())
            return true;
        return false;
    }
}
