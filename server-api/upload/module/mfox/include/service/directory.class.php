<?php
defined('PHPFOX') or exit('NO DICE!');

/**
 * mfox.directory service
 * @author AnNT
 */
class Mfox_Service_Directory extends Phpfox_Service
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
    
    /**
     * Constructor
     */
    public function __construct() 
    {
        $this->_oReq = Phpfox::getService('mfox.request');
        $this->_oSearch = Phpfox::getService('mfox.search');
        $this->_oBrowse = Phpfox::getService('mfox.search.browse');
        
        $this->_sTableCategory = Phpfox::getT('directory_category');
        $this->_sTableFeed = Phpfox::getT('directory_feed');
        $this->_sTableReview = Phpfox::getT('directory_review');
    }

    /**
     * Fix callback
     * @todo remove when fixed from module
     */
    public function getActivityFeedCheckinhere($aItem, $aCallback = null, $bIsChildItem = false) 
    {
        if ($bIsChildItem) 
        {
            $this->database()->select(Phpfox::getUserField('u2') . ', ')->join(Phpfox::getT('user') , 'u2', 'u2.user_id = e.user_id');
        }
        
        $sWhere = '';
        $sWhere.= ' and e.business_status IN ( ' . Phpfox::getService('directory.helper')->getConst('business.status.running') . ',' . Phpfox::getService('directory.helper')->getConst('business.status.completed') . ',' . Phpfox::getService('directory.helper')->getConst('business.status.approved') . ' ) ';
        $aRow = $this->database()
            ->select('u.user_id, e.business_id, e.package_data, e.module_id, e.item_id, e.business_id, e.name, e.time_stamp, e.logo_path as image_path, e.server_id as image_server_id, e.total_like, e.total_comment, e.short_description_parsed as description_parsed, l.like_id AS is_liked')
            ->from(Phpfox::getT('directory_business') , 'e')
            ->join(PHpfox::getT('user') , 'u', 'u.user_id = e.user_id')
            ->leftJoin(Phpfox::getT('directory_business_text') , 'et', 'et.business_id = e.business_id')
            ->leftJoin(Phpfox::getT('like') , 'l', 'l.type_id = \'directory_checkinhere\' AND l.item_id = e.business_id AND l.user_id = ' . Phpfox::getUserId())
            ->where('e.business_id = ' . (int)$aItem['item_id'] . $sWhere)
            ->execute('getSlaveRow');
        
        if (!isset($aRow['business_id'])) 
        {
            return false;
        }
        $aRow['setting_support'] = Phpfox::getService('directory.permission')->getSettingSupportInBusiness($aRow['business_id'], $aRow);
        
        if ($bIsChildItem) 
        {
            $aItem = $aRow;
        }
        
        if ((defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'directory.view_browse_events')) || (!defined('PHPFOX_IS_PAGES_VIEW') && $aRow['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aRow['item_id'], 'directory.view_browse_events'))) 
        {
            return false;
        }
        
        $aReturn = array(
            'feed_title' => $aRow['name'],
            'feed_info' => Phpfox::getPhrase('directory.checked_in_this_business') ,
            'feed_link' => Phpfox::permalink('directory.detail', $aRow['business_id'], $aRow['name']) ,
            'feed_content' => $aRow['description_parsed'],
            'feed_icon' => Phpfox::getLib('image.helper')->display(array(
                'theme' => 'module/directory.png',
                'return_url' => true
            )) ,
            'time_stamp' => $aRow['time_stamp'],
            'feed_total_like' => $aRow['total_like'],
            'feed_is_liked' => $aRow['is_liked'],
            'enable_like' => true,
            'like_type_id' => 'directory_checkinhere',
            'total_comment' => $aRow['total_comment']
        );
        if ($aRow['setting_support']['allow_users_to_share_business'] == false) 
        {
            $aReturn['no_share'] = true;
        }
        
        if (!empty($aRow['image_path'])) 
        {
            $sImage = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aRow['image_server_id'],
                'path' => 'core.url_pic',
                'file' => $aRow['image_path'],
                'yndirectory_overridenoimage' => true,
                'suffix' => '_200',
                'max_width' => 120,
                'max_height' => 120
            ));
            
            $aReturn['feed_image'] = $sImage;
        }
        
        if ($bIsChildItem) 
        {
            $aReturn = array_merge($aReturn, $aItem);
        }
        
        return $aReturn;
    }

    /**
     * Invite people
     * @param array $aData[iBusinessId, sUserIds]
     * @return array
     */
    public function invite($aData)
    {
        if (!isset($aData['iBusinessId']) || !isset($aData['sUserIds']))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        $oParseInput = Phpfox::getLib('parse.input');

        $iBusinessId = $aData['iBusinessId'];
        $aBusiness = Phpfox::getService('directory')->getQuickBusinessById($iBusinessId);

        if (!$aBusiness)
        {
            return array(
                'error_code' => 1,
                'error_message'=> Phpfox::getPhrase('directory.business_is_not_found'),
            );
        }
        
        /**
         * @var array
         */
        $aVals = array('invite' => explode(',', $aData['sUserIds']));

        if (isset($aVals['invite']))
        {
            /**
             * @var array
             */
            $aInvites = $this->database()->select('invited_user_id, invited_email')
                    ->from(Phpfox::getT('directory_invite'))
                    ->where('business_id = ' . (int) $iBusinessId)
                    ->execute('getRows');
            /**
             * @var array
             */
            $aInvited = array();
            
            foreach ($aInvites as $aInvite)
            {
                $aInvited[(empty($aInvite['invited_email']) ? 'user' : 'email')][(empty($aInvite['invited_email']) ? $aInvite['invited_user_id'] : $aInvite['invited_email'])] = true;
            }
        }

        if (isset($aVals['invite']) && is_array($aVals['invite']))
        {
            /**
             * @var string
             */
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
            
            /**
             * @var array
             */
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

                /**
                 * @var string
                 */
                $sLink = Phpfox::getLib('url')->permalink('directory.detail', $aBusiness['business_id'], $aBusiness['name']);

                /**
                 * @var string
                 */
                $sMessage = Phpfox::getPhrase('directory.full_name_invited_you_to_the_title', array(
                    'full_name' => Phpfox::getUserBy('full_name'),
                    'title' => $oParseInput->clean($aBusiness['name'], 255),
                    'link' => $sLink
                ), false, null, $aUser['language_id']);

                /**
                 * @var string
                 */
                $sSubject = Phpfox::getPhrase('directory.full_name_invited_you_to_the_business_title', array(
                    'full_name' => Phpfox::getUserBy('full_name') ,
                    'title' => $oParseInput->clean($aBusiness['name'], 255) ,
                ), false, null, $aUser['language_id']);

                $sSubject = Phpfox::getService('directory.mail')->parseTemplate($sSubject, array(
                    $aBusiness
                ), $iInviteId = Phpfox::getUserId(), 'owner');
                
                $sMessage = Phpfox::getService('directory.mail')->parseTemplate($sMessage, array(
                    $aBusiness
                ), $iInviteId = Phpfox::getUserId(), 'owner');

                $aCustomMesssage = array(
                    'subject' => $sSubject,
                    'message' => $sMessage
                );

                $bResult = Phpfox::getService('directory.mail.process')->sendEmailTo($sType = 0, $aBusiness['business_id'], $aReceivers = $aUser['user_id'], $aCustomMesssage);
                
                if ($bResult) 
                {
                    $iInviteId = $this->database()->insert(Phpfox::getT('directory_invite') , array(
                        'business_id' => $aBusiness['business_id'],
                        'inviting_user_id' => Phpfox::getUserId(),
                        'invited_user_id' => $aUser['user_id'],
                        'time_stamp' => PHPFOX_TIME
                    ));

                    (Phpfox::isModule('request') ? Phpfox::getService('request.process')->add('directory_invited', $aBusiness['business_id'], $aUser['user_id']) : null);
                }
            }
        }

        if (!Phpfox_Error::isPassed())
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        return array(
            'error_code' => 0,
            'message' => Phpfox::getPhrase('mfox.members_invited'),
        );
    }

    /**
     * Get people to invite
     * @param array $aData[iBusinessId]
     * @return array
     */
    public function getinvitepeople($aData)
    {
        if (empty($aData['iBusinessId']))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        $aConds = array();
        $aInvitedIds = $this->_getInvitedUserIds($aData['iBusinessId']);
        $sInvitedIds = implode(', ', $aInvitedIds);
        $aConds[] = " AND friend.is_page = 0 AND friend.user_id = " . (int) Phpfox::getUserId() . " AND u.user_id NOT IN ({$sInvitedIds}) ";
        list($iCnt, $aFriends) = Phpfox::getService('friend')->get($aConds);

        $aResults = array();
        foreach ($aFriends as $aFriend)
        {
            $aResults[] = array(
                'UserProfileImg_Url' => Phpfox::getService('mfox.helper.image')->getUserUrl($aFriend['user_image']),
                'sFullName' => $aFriend['full_name'],
                'id' => $aFriend['user_id']
            );
        }

        return $aResults;
    }

    /**
     * Get invited user ids
     */
    private function _getInvitedUserIds($iBusinessId)
    {
        $aIds = array(Phpfox::getUserId());
        
        $aRows = $this->database()->select('invited_user_id')
             ->from(Phpfox::getT('directory_invite'))
             ->where('business_id  = ' . (int) $iBusinessId)
             ->execute('getSlaveRows');

        foreach ($aRows as $aRow)
        {
            $aIds[] = $aRow['invited_user_id'];
        }

        return $aIds;
    }

    /**
     * Get notification comment
     * @param array $aNotification
     * @return array
     */
    public function getNotificationComment($aNotification)
    {
        $aRow = $this->database()->select('fc.feed_comment_id, u.user_id, u.gender, u.user_name, u.full_name, e.business_id, e.name')
            ->from(Phpfox::getT('directory_feed_comment'), 'fc')            
            ->join(Phpfox::getT('directory_business'), 'e', 'e.business_id = fc.parent_user_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = e.user_id')
            ->where('fc.feed_comment_id = ' . (int) $aNotification['item_id'])
            ->execute('getSlaveRow');
        
        if (!count($aRow))
        {
            return false;
        }

        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['name'], Phpfox::getParam('notification.total_notification_title_length'), '...');
        
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            if (isset($aNotification['extra_users']) && count($aNotification['extra_users']))
            {
                $sPhrase = Phpfox::getPhrase('directory.users_commented_on_span_class_drop_data_user_row_full_name_s_span_business_title', array('users' => Phpfox::getService('notification')->getUsers($aNotification, true), 'row_full_name' => $aRow['full_name'], 'title' =>  $sTitle));
            }
            else 
            {
                $sPhrase = Phpfox::getPhrase('directory.users_commented_on_gender_own_business_title', array('users' => $sUsers, 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => $sTitle));  
            }
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase = Phpfox::getPhrase('directory.users_commented_on_your_business_title', array('users' => $sUsers, 'title' => $sTitle));
        }
        else 
        {
            $sPhrase = Phpfox::getPhrase('directory.users_commented_on_span_class_drop_data_user_row_full_name_s_span_business_title', array('users' => $sUsers, 'row_full_name' => $aRow['full_name'], 'title' => $sTitle));
        }
            
        return array(
            'link' => array(
                'iBusinessId' => $aRow['business_id'],
                'sTitle' => $aRow['name'],
                'sModule' => 'activities',
            ),
            'message' => $sPhrase,
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }

    /**
     * Get notification like a comment
     * @param array $aNotification
     * @return array
     */
    public function getNotificationComment_Like($aNotification)
    {
        $aRow = $this->database()->select('fc.feed_comment_id, u.user_id, u.gender, u.user_name, u.full_name, e.business_id, e.name')
            ->from(Phpfox::getT('directory_feed_comment'), 'fc')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
            ->join(Phpfox::getT('directory_business'), 'e', 'e.business_id = fc.parent_user_id')
            ->where('fc.feed_comment_id = ' . (int) $aNotification['item_id'])
            ->execute('getSlaveRow');
            
        if (!count($aRow))
        {
            return false;
        }
        
        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['name'], Phpfox::getParam('notification.total_notification_title_length'), '...');
        
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            if (isset($aNotification['extra_users']) && count($aNotification['extra_users']))
            {
                $sPhrase = Phpfox::getPhrase('directory.users_liked_span_class_drop_data_user_row_full_name_s_span_comment_on_the_business_title', array('users' => Phpfox::getService('notification')->getUsers($aNotification, true), 'row_full_name' => $aRow['full_name'], 'title' => $sTitle));
            }
            else 
            {
                $sPhrase = Phpfox::getPhrase('directory.users_liked_gender_own_comment_on_the_business_title', array('users' => $sUsers, 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => $sTitle));
            }
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())        
        {
            $sPhrase = Phpfox::getPhrase('directory.users_liked_one_of_your_comments_on_the_business_title', array('users' => $sUsers, 'title' => $sTitle));
        }
        else 
        {
            $sPhrase = Phpfox::getPhrase('directory.users_liked_one_on_span_class_drop_data_user_row_full_name_s_span_comments_on_the_business_title', array('users' => $sUsers, 'row_full_name' => $aRow['full_name'], 'title' => $sTitle));
        }
            
        return array(
            'link' => array(
                'iBusinessId' => $aRow['business_id'],
                'sTitle' => $aRow['name'],
                'iFeedCommentId' => $aRow['feed_comment_id'],
            ),
            'message' => $sPhrase,
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }

    /**
     * Get notification comment a comment
     * @param array $aNotification
     * @return array
     */
    public function getNotificationComment_Feed($aNotification)
    {
        $aRow = $this->database()->select('fc.feed_comment_id, u.user_id, u.gender, u.user_name, u.full_name, e.business_id, e.name')
            ->from(Phpfox::getT('directory_feed_comment'), 'fc')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
            ->join(Phpfox::getT('directory_business'), 'e', 'e.business_id = fc.parent_user_id')
            ->where('fc.feed_comment_id = ' . (int) $aNotification['item_id'])
            ->execute('getSlaveRow');
        
        if (!isset($aRow['feed_comment_id']))
        {
            return false;
        }
        
        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        $sTitle = Phpfox::getLib('parse.output')->shorten($aRow['name'], Phpfox::getParam('notification.total_notification_title_length'), '...');
        
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            if (isset($aNotification['extra_users']) && count($aNotification['extra_users']))
            {
                $sPhrase = Phpfox::getPhrase('directory.users_commented_on_span_class_drop_data_user_row_full_name_s_span_comment_on_the_business_title', array('users' => Phpfox::getService('notification')->getUsers($aNotification, true), 'row_full_name' => $aRow['full_name'], 'title' => $sTitle));
            }
            else 
            {
                $sPhrase = Phpfox::getPhrase('directory.users_commented_on_gender_own_comment_on_the_business_title', array('users' => $sUsers, 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => $sTitle));   
            }
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase = Phpfox::getPhrase('directory.users_commented_on_one_of_your_comments_on_the_business_title', array('users' => $sUsers, 'title' => $sTitle));
        }
        else 
        {
            $sPhrase = Phpfox::getPhrase('directory.users_commented_on_one_of_span_class_drop_data_user_row_full_name_s_span_comments_on_the_business_title', array('users' => $sUsers, 'row_full_name' => $aRow['full_name'], 'title' => $sTitle));
        }
            
        return array(
            'link' => array(
                'iBusinessId' => $aRow['business_id'],
                'sTitle' => $aRow['name'],
                'iFeedCommentId' => $aRow['feed_comment_id'],
            ),
            'message' => $sPhrase,
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }

    /**
     * Delete covers
     * @param array $aData[aCoverIds, iBusinessId]
     * @return array
     */
    public function delete_cover($aData)
    {
        if (isset($aData['aCoverIds']) && is_array($aData['aCoverIds']))
        {
            foreach ($aData['aCoverIds'] as $iPhotoId)
            {
                if (!Phpfox::getService('directory.process')->deleteImage($iPhotoId))
                {
                    return array(
                        'error_code' => 1,
                        'error_message' => implode(' ', Phpfox_Error::get()),
                    );
                }
            }
        }

        return array(
            'error_code' => 0,
            'message' => Phpfox::getPhrase('directory.deleted'),
        );
    }

    /**
     * Upload cover
     * @param array $aData[iBusinessId]
     * @return array
     */
    public function upload_cover($aData)
    {
        if (empty($aData['iBusinessId']))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        if (!$this->canEditBusiness($aData['iBusinessId']))
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        // convert single to mutiple mode
        foreach ($_FILES['image'] as $key => $value) {
            $_FILES['image'][$key] = array(
                0 => $value
            );
        }

        $aResult = Phpfox::getService('directory.process')->updateCoverPhotos(array(
            'businessid' => $aData['iBusinessId'],
        ));

        if (isset($aResult['error']) && $aResult['error'] && count($_FILES))
        {
            return array(
                'error_code' => 1,
                'error_message' => $aResult['message'],
            );
        }
        else
        {
            return array(
                'error_code' => 0,
                'message' => Phpfox::getPhrase('directory.updated_cover_photos_successfully'),
            );
        }
    }

    /**
     * Edit a business
     * @param array $aData
     * @return array
     */
    public function edit($aData)
    {
        if (empty($aData['iBusinessId']))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        if (!$this->canEditBusiness($aData['iBusinessId']))
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        if (!$this->updateBusiness($aData)) 
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        return array(
            'error_code' => 0,
            'message' => Phpfox::getPhrase('directory.business_successfully_updated'),
        );
    }

    /**
     * Update business info
     * @param int $aData
     * @return boolean
     */
    public function updateBusiness($aData)
    {
        $iBusinessId = $aData['iBusinessId'];
        $aBusiness = $this->detail($aData);

        $oImage = Phpfox::getLib('image');
        $oFile = Phpfox::getLib('file');

        Phpfox::getService('ban')->checkAutomaticBan($aData['sTitle']);
        
        if (empty($aData['iCategoryId'])) 
        {
            return Phpfox_Error::set(Phpfox::getPhrase('directory.provide_a_category_this_item_will_belong_to'));
        }
        
        $bHasImage = false;
        if (isset($_FILES['image']['name']) && ($_FILES['image']['name'] != '')) 
        {
            $aImage = $oFile->load('image', array(
                'jpg',
                'gif',
                'png'
            ), (Phpfox::getParam('directory.max_upload_size_photos') === 0 ? null : (Phpfox::getParam('directory.max_upload_size_photos') / 1024)));
            $bHasImage = true;
        }
        
        $aEditedBusiness = Phpfox::getService('directory')->getBusinessForEdit($iBusinessId);
        
        // update info
        $aUpdate = array(
            'name' => isset($aData['sTitle']) ? Phpfox::getLib('parse.input')->clean(strip_tags($aData['sTitle']), 255) : '',
            'time_update' => PHPFOX_TIME,
            'short_description' => isset($aData['sShortDesc']) ? Phpfox::getLib('parse.input')->clean($aData['sShortDesc']) : '',
            'short_description_parsed' => isset($aData['sShortDesc']) ? Phpfox::getLib('parse.input')->prepare($aData['sShortDesc']) : '',
            'email' => isset($aData['sEmail']) ? $aData['sEmail'] : '',
            'size' => isset($aData['sSize']) ? $aData['sSize'] : '',
        );
        
        $this->database()->update(Phpfox::getT('directory_business'), $aUpdate, 'business_id = ' . $iBusinessId);

        // update text
        $aUpdateText = array(
            'description' => isset($aData['sDescription']) ? Phpfox::getLib('parse.input')->clean($aData['sDescription']) : '',
            'description_parsed' => isset($aData['sDescription']) ? Phpfox::getLib('parse.input')->prepare($aData['sDescription']) : '',
        );

        $this->database()->update(Phpfox::getT('directory_business_text'), $aUpdateText, 'business_id = ' . $iBusinessId);
        
        // update image
        if ($bHasImage) 
        {
            Phpfox::getService('directory.process')->upload($iBusinessId, array(), $oFile, $oImage);
        }

        // update first location
        $this->_updateFirstLocation($aData, $aBusiness);

        // update first phone
        $this->_updateFirstPhone($aData, $aBusiness);

        // update first fax
        $this->_updateFirstFax($aData, $aBusiness);

        // update first website url
        $this->_updateFirstWebAddress($aData, $aBusiness);
        
        // update category
        $this->_updateMainCategory($aData, $aBusiness);

        // send notification to follower(s)
        $aFollowers = Phpfox::getService('directory')->getFollowerIds($iBusinessId);
        foreach ($aFollowers as $keyaFollowers => $valueaFollowers) 
        {
            Phpfox::getService('notification.process')->add('directory_updateinfobusiness', $iBusinessId, $valueaFollowers['user_id'], Phpfox::getUserId());
        }
        
        return true;
    }

    /**
     * Update main category
     */
    private function _updateMainCategory($aData, $aBusiness)
    {
        if (isset($aData['iCategoryId']))
        {
            // delete current
            $sCategoryTextRelated = Phpfox::getService('directory.category')->getCategoryIds($aData['iBusinessId']);

            $iCurrentCategoryId = $aBusiness['iCategoryId'];
            $iCurrentParentId = $this->database()->select('dc.parent_id')
                ->from($this->_sTableCategory, 'dc')
                ->where('dc.category_id = ' . $iCurrentCategoryId)
                ->execute('getSlaveField');

            if (!empty($iCurrentParentId))
            {
                $this->database()->delete(Phpfox::getT('directory_category_data'), 'business_id = ' . (int) $aData['iBusinessId'] . ' AND category_id = ' . (int) $iCurrentParentId);
            }

            $this->database()->delete(Phpfox::getT('directory_category_data'), 'business_id = ' . (int) $aData['iBusinessId'] . ' AND category_id = ' . (int) $iCurrentCategoryId);

            Phpfox::getService('directory')->updateCountBusinessForCategory($sCategoryTextRelated);

            // insert new
            $aSql = array(
                'business_id' => $aData['iBusinessId'],
                'category_id' => $aData['iCategoryId'],
                'is_main' => 1,
            );

            $iParentId = $this->database()->select('dc.parent_id')
                ->from($this->_sTableCategory, 'dc')
                ->where('dc.category_id = ' . (int) $aData['iCategoryId'])
                ->execute('getSlaveField');

            if (!empty($iParentId))
            {
                unset($aSql['is_main']);

                $aSqlParent = array(
                    'business_id' => $aData['iBusinessId'],
                    'category_id' => $iParentId,
                    'is_main' => 1,
                );

                $this->database()->insert(Phpfox::getT('directory_category_data'), $aSqlParent);
            }

            $this->database()->insert(Phpfox::getT('directory_category_data'), $aSql);

            $sCategoryTextRelated = Phpfox::getService('directory.category')->getCategoryIds($aData['iBusinessId']);

            Phpfox::getService('directory')->updateCountBusinessForCategory($sCategoryTextRelated);
        }
    }

    /**
     * Update first web address
     */
    private function _updateFirstWebAddress($aData, $aBusiness)
    {
        if (isset($aData['sWebAddress'])) 
        {
            $aSql = array(
                'business_id' => $aData['iBusinessId'],
                'website_text' => !empty($aData['sWebAddress']) ? Phpfox::getLib('parse.input')->clean(strip_tags($aData['sWebAddress'])) : '',
            );
            
            if (!empty($aBusiness['aWebAddress']))
            {
                return $this->database()->update(Phpfox::getT('directory_business_website'), $aSql, 'business_id = ' . (int) $aData['iBusinessId'] . ' AND website_text = \'' . $aBusiness['aWebAddress'][0] . '\'');
            }

            $this->database()->insert(Phpfox::getT('directory_business_website'), $aSql);
        }
    }

    /**
     * Update first fax
     */
    private function _updateFirstFax($aData, $aBusiness)
    {
        if (isset($aData['sFax'])) 
        {
            $aSql = array(
                'business_id' => $aData['iBusinessId'],
                'fax_number' => !empty($aData['sFax']) ? Phpfox::getLib('parse.input')->clean(strip_tags($aData['sFax'])) : 0,
            );
            
            if (!empty($aBusiness['aFax']))
            {
                return $this->database()->update(Phpfox::getT('directory_business_fax'), $aSql, 'business_id = ' . (int) $aData['iBusinessId'] . ' AND fax_number = \'' . $aBusiness['aFax'][0] . '\'');
            }

            $this->database()->insert(Phpfox::getT('directory_business_fax'), $aSql);
        }
    }

    /**
     * Update first phone
     */
    private function _updateFirstPhone($aData, $aBusiness)
    {
        if (isset($aData['sPhone'])) 
        {
            $aSql = array(
                'business_id' => $aData['iBusinessId'],
                'phone_number' => !empty($aData['sPhone']) ? Phpfox::getLib('parse.input')->clean(strip_tags($aData['sPhone'])) : 0,
            );
            
            if (!empty($aBusiness['aPhone']))
            {
                return $this->database()->update(Phpfox::getT('directory_business_phone'), $aSql, 'business_id = ' . (int) $aData['iBusinessId'] . ' AND phone_number = \'' . $aBusiness['aPhone'][0] . '\'');
            }

            $this->database()->insert(Phpfox::getT('directory_business_phone'), $aSql);
        }
    }

    /**
     * Update first location
     */
    private function _updateFirstLocation($aData, $aBusiness)
    {
        if (!empty($aData['sLocationAddress'])) 
        {
            $aSql = array(
                'business_id' => $aData['iBusinessId'],
                'location_title' => !empty($aData['sLocationAddress']) ? Phpfox::getLib('parse.input')->clean(strip_tags($aData['sLocationAddress'])) : '',
                'location_address' => !empty($aData['sLocationAddress']) ? Phpfox::getLib('parse.input')->clean(strip_tags($aData['sLocationAddress'])) : '',
                'location_longitude' => !empty($aData['sLong']) ? Phpfox::getLib('parse.input')->clean(strip_tags($aData['sLong'])) : 0,
                'location_latitude' => !empty($aData['sLat']) ? Phpfox::getLib('parse.input')->clean(strip_tags($aData['sLat'])) : 0,
            );
            
            if (!empty($aBusiness['aLocations']))
            {
                return $this->database()->update(Phpfox::getT('directory_business_location'), $aSql, 'location_id = ' . (int) $aBusiness['aLocations'][0]['iLocationId']);
            }

            $this->database()->insert(Phpfox::getT('directory_business_location'), $aSql);
        }
    }

    /**
     * Form edit
     * @param
     * @return array [aItem, categoryOptions, sizeOptions]
     */
    public function form_edit($aData)
    {
        if (empty($aData['iBusinessId']))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        if (!$this->canEditBusiness($aData['iBusinessId']))
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        return array(
            'aItem' => $this->detail($aData),
            'categoryOptions' => $this->getCategoryOptions(),
            'sizeOptions' => Phpfox::getService('directory.helper')->getBusinessSize(),
        );
    }

    /**
     * Check permission of editing business
     * @param int $iBusinessId
     * @return boolean
     */
    public function canEditBusiness($iBusinessId)
    {
        if (!Phpfox::getService('directory.permission')->canEditBusiness(Phpfox::getUserId(), $iBusinessId))
        {
            return Phpfox_Error::set(Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        return true;
    }

    /**
     * Add a business
     * @param array [iCategoryId, iPackageId, sDescription, sEmail, sFax, sLocationAddress, sPhone, sShortDesc, sSize, sTitle, sWebAddress]
     * @return array
     */
    public function add($aData) 
    {
        // check permission
        if (!$this->canCreateBusiness())
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        $this->_setFormRequest($aData);

        // init variables
        $_sModule = $this->_oReq->get('module', false);
        $_iItem = $this->_oReq->getInt('item', false);
        $type = $this->_oReq->get('type', false);
        $package_id = $this->_oReq->get('package', false);
        
        if (($type === false || in_array($type, array('business', 'claiming')) == false)) 
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }
        
        $aPackage = null;
        if ($type == 'business') 
        {
            if (($package_id === false || (int)$package_id <= 0)) 
            {
                return array(
                    'error_code' => 1,
                    'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
                );
            }
            
            $aPackage = Phpfox::getService('directory.package')->getById($package_id);
            if (isset($aPackage['package_id']) == false || (int)$aPackage['active'] != 1) 
            {
                return array(
                    'error_code' => 1,
                    'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
                );
            }
        }
        
        // process
        if ($this->_oReq->getArray('val')) 
        {
            $aVals = $this->_oReq->getArray('val');
            $aVals['type'] = $type;
            $aVals['package_id'] = $package_id;
            
            if ($_sModule && $_iItem) 
            {
                $aVals['module_id'] = $_sModule;
                $aVals['item_id'] = $_iItem;
            }

            if (isset($aPackage) && !empty($aPackage['themes']))
            {
                $aVals['val']['theme'] = $aPackage['themes'][0]['theme_id'];
            }

            if ($iId = Phpfox::getService('directory.process')->addBusiness($aVals)) 
            {
                if ((int)$aVals['package_id'] > 0) 
                {
                    $invoice_id = $this->_payPackage($aVals, $iId);
                    if ($invoice_id === true) 
                    {
                        // create business without fee
                        return array(
                            'error_code' => 0,
                            'message' => Phpfox::getPhrase('directory.business_successfully_added'),
                            'iBusinessId' => $iId,
                        );
                    } 
                    elseif ((int)$invoice_id > 0) 
                    {
                        // create business without fee
                        return array(
                            'error_code' => 0,
                            'message' => Phpfox::getPhrase('directory.business_successfully_added'),
                            'iBusinessId' => $iId,
                            'iInvoiceId' => $invoice_id,
                        );
                    } 
                    else
                    {
                        return array(
                            'error_code' => 1,
                            'error_message' => Phpfox::getPhrase('directory.some_issues_happen_please_try_again_thanks'),
                            'iBusinessId' => $iId,
                        );
                    }
                } 
                else
                {
                    // create claiming type
                    return array(
                        'error_code' => 0,
                        'message' => Phpfox::getPhrase('directory.business_successfully_added'),
                        'iBusinessId' => $iId,
                    );
                }
            } 
            else
            {
                return array(
                    'error_code' => 1,
                    'error_message' => implode(' ', Phpfox_Error::get()),
                );
            }
        }
    }

    /**
     * Process selected package
     */
    private function _payPackage($aVals, $iId) 
    {
        if ((int)$aVals['package_id'] > 0) 
        {
            $package_id = $aVals['package_id'];
            $aPackage = Phpfox::getService('directory.package')->getById($package_id);
            
            if (isset($aPackage['package_id']) == false || (int)$aPackage['active'] != 1) 
            {
                // do nothing
            } 
            else
            {
                $aGlobalSetting = Phpfox::getService('directory')->getGlobalSetting();
                $_iDefaultFeatureFee = (int)$aGlobalSetting[0]['default_feature_fee'];
                $aCurrentCurrencies = Phpfox::getService('directory.helper')->getCurrentCurrencies();
                $currency_id = $aCurrentCurrencies[0]['currency_id'];
                $packageFee = doubleval($aPackage['fee']);
                $featureFee = doubleval(($aVals['feature_number_days'] * $_iDefaultFeatureFee));
                $fFee = $packageFee + $featureFee;

                if ($fFee > 0) 
                {
                    // add invoice
                    $iInvoice = Phpfox::getService('directory.process')->addInvoice($iId, $currency_id, $fFee, 'business', array(
                        'pay_type' => (Phpfox::getUserParam('directory.can_feature_business') ? ($featureFee > 0 ? 'feature' : '') : '') . '|' . ($packageFee > 0 ? 'package' : ''),
                        'aPackage' => $aPackage,
                        'feature_days' => $aVals['feature_number_days']
                    ));

                    $aPurchase = Phpfox::getService('directory')->getInvoice($iInvoice);
                    
                    // process payment
                    if (empty($iInvoice['status'])) 
                    {
                        $this->setParam('gateway_data', array(
                            'item_number' => 'directory|' . $aPurchase['invoice_id'],
                            'currency_code' => $aPurchase['default_currency_id'],
                            'amount' => $aPurchase['default_cost'],
                            'item_name' => ($packageFee > 0 ? 'package' : '') . '|' . ($featureFee > 0 ? 'feature' : ''),
                            'return' => Phpfox::permalink('directory.detail', $iId, $aVals['name'], false, '') . 'businesspayment_done/',
                            'recurring' => '',
                            'recurring_cost' => '',
                            'alternative_cost' => '',
                            'alternative_recurring_cost' => ''
                        ));

                        return $aPurchase['invoice_id'];
                    }
                } 
                else
                {
                    // pay zero fee - package
                    $status = Phpfox::getService('directory.helper')->getConst('business.status.draft');
                    
                    if (Phpfox::getService('directory.helper')->getUserParam('directory.business_created_by_user_automatically_approved', Phpfox::getUserId())) 
                    {
                        $status = Phpfox::getService('directory.helper')->getConst('business.status.approved');
                    } 
                    else
                    {
                        $status = Phpfox::getService('directory.helper')->getConst('business.status.pending');
                    }
                    
                    Phpfox::getService('directory.process')->updateBusinessStatus($iId, $status);
                    
                    if ($status == Phpfox::getService('directory.helper')->getConst('business.status.approved')) 
                    {
                        Phpfox::getService('directory.process')->approveBusiness($iId, null);
                    }
                    
                    $theme_id = isset($aPackage['themes'][0]) ? $aPackage['themes'][0] : 1;
                    
                    Phpfox::getService('directory.process')->updateThemeForBusiness(array(
                        $theme_id,
                        $iId
                    ));
                    
                    // pay zero fee - feature
                    if ((int)$aVals['feature_number_days'] > 0) 
                    {
                        $start_time = PHPFOX_TIME;
                        $end_time = $start_time + ((int)$aVals['feature_number_days'] * 86400);
                        Phpfox::getService('directory.process')->updateBusinessFeatureTime($iId, $start_time, $end_time);
                    }

                    return true;
                }
            }
        }
        
        return false;
    }

    /**
     * Set form request
     * @param array $aData
     * @return null
     */
    private function _setFormRequest($aData)
    {
        $this->_oReq->set(array(
            'id' => !empty($aData['iBusinessId']) ? (int) $aData['iBusinessId'] : null,
            'module' => !empty($aData['sModule']) ? $aData['sModule'] : '',
            'item' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : '',
            'type' => !empty($aData['sType']) ? $aData['sType'] : '',
            'package' => !empty($aData['iPackageId']) ? (int) $aData['iPackageId'] : '',
            'val' => array(
                'theme' => 1, // default
                'attachment' => '',
                'selected_categories' => '',
                'name' => !empty($aData['sTitle']) ? $aData['sTitle'] : '',
                'short_description' => !empty($aData['sShortDesc']) ? $aData['sShortDesc'] : '',
                'description' => !empty($aData['sDescription']) ? $aData['sDescription'] : '',
                'location_title' => array(
                    '0' => !empty($aData['sLocationAddress']) ? $aData['sLocationAddress'] : '',
                ),
                'location_fulladdress' => array(
                    '0' => !empty($aData['sLocationAddress']) ? $aData['sLocationAddress'] : '',
                ),
                'location_address' => array(
                    '0' => !empty($aData['sLocationAddress']) ? $aData['sLocationAddress'] : '',
                ),
                'location_address_city' => array(
                    '0' => '',
                ),
                'location_address_country' => array(
                    '0' => '',
                ),
                'location_address_zipcode' => array(
                    '0' => '',
                ),
                'location_address_lat' => array(
                    '0' => !empty($aData['sLat']) ? $aData['sLat'] : '',
                ),
                'location_address_lng' => array(
                    '0' => !empty($aData['sLong']) ? $aData['sLong'] : '',
                ),
                'phone' => array(
                    '0' => !empty($aData['sPhone']) ? $aData['sPhone'] : '',
                ),
                'fax' => array(
                    '0' => !empty($aData['sFax']) ? $aData['sFax'] : '',
                ),
                'email' => !empty($aData['sEmail']) ? $aData['sEmail'] : '',
                'country_iso' => '',
                'city' => '',
                'province' => '',
                'zip_code' => '',
                'web_address' => array (
                    '0' => !empty($aData['sWebAddress']) ? $aData['sWebAddress'] : '',
                ),
                'size' => !empty($aData['sSize']) ? $aData['sSize'] : '',
                'visiting_hours_dayofweek_id' => array(
                    '0' => 1,
                ),
                'visiting_hours_hour_starttime' => array(
                    '0' => '00:00',
                ),
                'visiting_hours_hour_endtime' => array(
                    '0' => '00:00',
                ),
                'time_zone' => 'z0',
                'disable_visitinghourtimezone' => 'on',
                'founder' => '',
                'customfield_user_title' => array(
                    '0' => '',
                ),
                'customfield_user_content' => array(
                    '0' => '',
                ),
                'category' => $this->_getValCategory($aData),
                'maincategory' => 0,
                'tag_list' => '',
                'feature_number_days' => 0,
                'privacy' => 0,
            ),
        ));
    }

    /**
     * Get category value
     * @param array $aData
     * @return array
     */
    private function _getValCategory($aData)
    {
        $iCategoryId = !empty($aData['iCategoryId']) ? (int) $aData['iCategoryId'] : 0;
        $aCategory = array();

        if (!empty($iCategoryId))
        {
            $iParentId = $this->database()->select('dc.parent_id')
                ->from($this->_sTableCategory, 'dc')
                ->where('dc.category_id = ' . $iCategoryId)
                ->execute('getSlaveField');

            if (!empty($iParentId))
            {
                $aCategory = array(
                    '0' => array(
                        '0' => $iParentId,
                    ),
                    $iParentId => array(
                        '0' => $iCategoryId,
                    ),
                );
            }
            else
            {
                $aCategory = array(
                    '0' => array(
                        '0' => $iCategoryId,
                    )
                );
            }
        }

        return $aCategory;
    }

    /**
     * Form add
     * @param
     * @return array [categoryOptions, sizeOptions]
     */
    public function form_add()
    {
        return array(
            'categoryOptions' => $this->getCategoryOptions(),
            'sizeOptions' => Phpfox::getService('directory.helper')->getBusinessSize(),
        );
    }

    /**
     * Form add step 1
     * @param
     * @return array [aPackages]
     */
    public function form_add_step1()
    {
        if (!$this->canCreateBusiness())
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        return array(
            'aPackages' => $this->getPackages(),
            'bIsCreator' => Phpfox::getService('directory.permission')->canCreateBusinessForClaiming()
        );
    }

    /**
     * Get packages
     * @param
     * @return array
     */
    public function getPackages()
    {
        $aRows = Phpfox::getService('directory')->getAllPackages(1);

        $this->processPackageRows($aRows);

        return $aRows;
    }

    /**
     * Process package rows
     * @param array &aRows
     * @return array [aFeatures, aModules, fPrice, iPackageId, iValidAmount, sCurrency, sCurrencySymbol, sDescription, sFeatures, sModules, sTitle, sValidPeriod]
     */
    public function processPackageRows(&$aRows)
    {
        $aTmpRows = $aRows;
        $aRows = array();

        foreach ($aTmpRows as $aRow) {
            $aRows[] = array(
                'aFeatures' => $this->_processPackageFeatures($aRow['settings']),
                'aModules' => $this->_processPackageModules($aRow['modules']),
                'fPrice' => $aRow['fee'],
                'iExpireType' => (int) $aRow['expire_type'],
                'iPackageId' => (int) $aRow['package_id'],
                'iValidAmount' => (int) $aRow['expire_number'],
                'sCurrency' => $aRow['currency'],
                'sCurrencySymbol' => $aRow['fee_display'],
                'sDescription' => '',
                'sFeatures' => $this->_processPackageFeaturesText($aRow['settings']),
                'sModules' => $this->_processPackageModulesText($aRow['modules']),
                'sTitle' => $aRow['name'],
                'sValidPeriod' => $this->_getPackageExpireTypeName((int) $aRow['expire_type']),
            );
        }
    }

    /**
     * Get package expire type name
     * @param int $iExpireType
     * @return string
     */
    private function _getPackageExpireTypeName($iExpireType)
    {
        $aType = array(
            0 => Phpfox::getPhrase('directory.never_expired'),
            1 => Phpfox::getPhrase('directory.day_s'),
            2 => Phpfox::getPhrase('directory.week_s'),
            3 => Phpfox::getPhrase('directory.month_s'),
        );

        return $aType[$iExpireType];
    }

    /**
     * Process package modules to text
     * @param array $aModules
     * @return string
     */
    private function _processPackageModulesText($aModules)
    {
        $aRows = $this->_processPackageModules($aModules);

        if (!count($aRows))
        {
            return Phpfox::getPhrase('directory.nothing_item_s');
        }

        return implode(', ', $aRows);
    }

    /**
     * Process package features to text
     * @param array $aSettings
     * @return string
     */
    private function _processPackageFeaturesText($aSettings)
    {
        $aRows = $this->_processPackageFeatures($aSettings);

        if (!count($aRows))
        {
            return Phpfox::getPhrase('directory.nothing_item_s');
        }

        return implode(', ', $aRows);
    }

    /**
     * Process package modules
     * @param array $aModules
     * @return array
     */
    private function _processPackageModules($aModules)
    {
        $aRows = array();

        foreach ($aModules as $aModule)
        {
            $sModule = Phpfox::getLib('locale')->convert($aModule['module_phrase']);
            $sModule = Phpfox::getService('mfox')->decodeUtf8Compat($sModule);
            $aRows[] = $sModule;
        }

        return $aRows;
    }

    /**
     * Process package features
     * @param array $aSettings
     * @return array
     */
    private function _processPackageFeatures($aSettings)
    {
        $aRows = array();

        foreach ($aSettings as $aSetting)
        {
            $sSetting = Phpfox::getLib('locale')->convert($aSetting['setting_phrase']);
            $sSetting = Phpfox::getService('mfox')->decodeUtf8Compat($sSetting);
            $aRows[] = $sSetting;
        }

        return $aRows;
    }

    /**
     * Check permission of creating business
     * @param
     * @return boolean
     */
    public function canCreateBusiness()
    {
        if (!Phpfox::getService('directory.permission')->canCreateBusiness() && !Phpfox::getService('directory.permission')->canCreateBusinessForClaiming())
        {
            return Phpfox_Error::set(Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        if (!Phpfox::getService('directory.permission')->canCreateBusinessWithLimit())
        {
            return Phpfox_Error::set(Phpfox::getPhrase('directory.you_have_reached_your_creating_limit_please_contact_administrator'));
        }

        return true;
    }

    /**
     * Fetch event list
     * @param array $aData[int iLimit, int iPage, int iParentId]
     * @return array [...]
     */
    public function fetch_events($aData)
    {
        $iLimit = !empty($aData['iLimit']) ? (int) $aData['iLimit'] : 10;
        $iPage = !empty($aData['iPage']) ? (int) $aData['iPage'] : 1;
        $iBusinessId = !empty($aData['iParentId']) ? (int) $aData['iParentId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }
        
        $aConds = array(
            ' 1=1 '
        );
        
        $aExtra = array(
            'order' => 'm.event_id DESC',
            'limit' => $iLimit,
            'page' => ($iPage - 1) * $iLimit,
        );

        list($aRows, $iCnt) = $this->getEventByBusinessId($iBusinessId, $aConds, $aExtra, true);

        $sEventModule = Phpfox::getService('directory.helper')->getModuleIdEvent();
        $oEventService = Phpfox::getService('mfox.' . $sEventModule);
        $oEventService->processRows($aRows);

        return $aRows;
    }

    /**
     * Get events of a business
     * Fix bug from directory module
     * 
     * @param  $iBusinessId
     * @param  array          $aConds
     * @param  array          $aExtra
     * @param  $getData
     * @param  true           $upcoming
     * @return mixed
     */
    function getEventByBusinessId($iBusinessId, $aConds = array(), $aExtra = array(), $getData = true, $upcoming = false)
    {
        $module = Phpfox::getService('directory.helper')->isAdvEvent() ? 'fevent' : 'event';
        $table = Phpfox::getT($module);
        
        $aRows = array();
        $sCond = implode(' AND ', $aConds);

        $iTimeDisplay = Phpfox::getLib('date')->mktime(0, 0, 0, Phpfox::getTime('m'), Phpfox::getTime('d'), Phpfox::getTime('Y'));
        $iCount = $this->database()->select('COUNT(*)')
            ->from($table, 'm')
            ->where('m.view_id = 0 AND m.privacy IN(0) AND m.module_id = \'directory\' AND m.item_id = ' . (int) $iBusinessId . ' AND ' . $sCond)
            ->execute('getSlaveField');
        if ($getData) {
            if ($iCount) {
                if ($aExtra && isset($aExtra['limit'])) {
                    $this->database()->limit($aExtra['page'], $aExtra['limit']);
                }

                if ($aExtra && isset($aExtra['order'])) {
                    $this->database()->order($aExtra['order']);
                }

                if (Phpfox::isModule('like')) {
                    if (Phpfox::getService('directory.helper')->isAdvEvent()) {
                        $this->database()->select('lik.like_id AS is_liked, ')
                            ->leftJoin(Phpfox::getT('like'), 'lik', ' (lik.type_id = \'fevent\' AND lik.item_id = m.event_id AND lik.user_id = ' . Phpfox::getUserId() . ') ');
                    } else {
                        $this->database()->select('lik.like_id AS is_liked, ')
                            ->leftJoin(Phpfox::getT('like'), 'lik', ' (lik.type_id = \'event\' AND lik.item_id = m.event_id AND lik.user_id = ' . Phpfox::getUserId() . ') ');
                    }
                }

                $aRows = $this->database()->select('ei.rsvp_id, m.*, ' . Phpfox::getUserField())
                    ->from($table, 'm')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = m.user_id')
                    ->leftJoin(Phpfox::getT($module . '_invite'), 'ei', ' (ei.event_id = m.event_id AND ei.invited_user_id = ' . Phpfox::getUserId() . ') ')
                    ->where('m.view_id = 0 AND m.privacy IN(0) AND m.module_id = \'directory\' AND m.item_id = ' . (int) $iBusinessId . ' AND ' . $sCond)
                    ->group('m.event_id')
                    ->execute('getSlaveRows');
            }
        } else {
            return $iCount;
        }

        return array($aRows, $iCount);
    }

    /**
     * Fetch song list
     * @param array $aData[int iLimit, int iPage, int iParentId]
     * @return array [...]
     */
    public function fetch_songs($aData)
    {
        $iLimit = !empty($aData['iLimit']) ? (int) $aData['iLimit'] : 10;
        $iPage = !empty($aData['iPage']) ? (int) $aData['iPage'] : 1;
        $iBusinessId = !empty($aData['iParentId']) ? (int) $aData['iParentId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }
        
        $aConds = array(
            ' 1=1 '
        );
        
        $aExtra = array(
            'order' => 'm.song_id DESC',
            'limit' => $iLimit,
            'page' => ($iPage - 1) * $iLimit,
        );

        list($aRows, $iCnt) = Phpfox::getService('directory')->getMusicByBusinessId($iBusinessId, $aConds, $aExtra, true);

        Phpfox::getService('mfox.song')->processRows($aRows);

        return $aRows;
    }

    /**
     * Fetch video list
     * @param array $aData[int iLimit, int iPage, int iParentId]
     * @return array [...]
     */
    public function fetch_videos($aData)
    {
        $iLimit = !empty($aData['iLimit']) ? (int) $aData['iLimit'] : 10;
        $iPage = !empty($aData['iPage']) ? (int) $aData['iPage'] : 1;
        $iBusinessId = !empty($aData['iParentId']) ? (int) $aData['iParentId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }
        
        $aConds = array(
            ' 1=1 '
        );
        
        $aExtra = array(
            'order' => 'm.video_id DESC',
            'limit' => $iLimit,
            'page' => ($iPage - 1) * $iLimit,
        );

        list($aRows, $iCnt) = Phpfox::getService('directory')->getVideoByBusinessId($iBusinessId, $aConds, $aExtra, true);

        $sVideoModule = Phpfox::getService('directory.helper')->getModuleIdVideo();
        $oVideoService = Phpfox::getService('mfox.' . $sVideoModule);
        $oVideoService->processRows($aRows);

        return $aRows;
    }

    /**
     * Fetch photo list
     * @param array $aData[int iLimit, int iPage, int iParentId]
     * @return array [...]
     */
    public function fetch_photos($aData)
    {
        $iLimit = !empty($aData['iLimit']) ? (int) $aData['iLimit'] : 10;
        $iPage = !empty($aData['iPage']) ? (int) $aData['iPage'] : 1;
        $iBusinessId = !empty($aData['iParentId']) ? (int) $aData['iParentId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }
        
        $aConds = array(
            ' 1=1 '
        );
        
        $aExtra = array(
            'order' => 'photo.photo_id DESC',
            'limit' => $iLimit,
            'page' => ($iPage - 1) * $iLimit,
        );

        list($aRows, $iCnt) = Phpfox::getService('directory')->getPhotoByBusinessId($iBusinessId, $aConds, $aExtra, true);

        $sPhotoModule = Phpfox::getService('directory.helper')->getModuleIdPhoto();
        $oPhotoService = Phpfox::getService('mfox.' . $sPhotoModule);
        $oPhotoService->processRows($aRows);

        return $aRows;
    }

    /**
     * Edit review
     * @param array $aData[iBusinessId, iRateValue, sContent, sTitle]
     * @return array [$aItem, ...]
     */
    public function edit_review($aData)
    {
        $iBusinessId = !empty($aData['iBusinessId']) ? $aData['iBusinessId'] : null;
        $iRating = isset($aData['iRateValue']) ? ((int) $aData['iRateValue'] * 2) : 0;
        $sContent = isset($aData['sContent']) ? $aData['sContent'] : '';
        $sTitle = isset($aData['sTitle']) ? $aData['sTitle'] : '';

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        if ($sTitle == '')
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('directory.you_have_to_input_title'),
            );
        }

        if ($sContent == '')
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('directory.you_have_to_input_content'),
            );
        }

        if ($iRating == 0)
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('directory.you_have_to_rate_this_business'),
            );
        }

        $bCanEditRate = Phpfox::getUserParam('directory.can_edit_own_review');

        if (!$bCanEditRate)
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_do_this_action'),
            );
        }

        Phpfox::getService('directory.process')->editReviewForBusiness($iBusinessId, $sTitle, $sContent, $iRating);        
        
        if (!Phpfox_Error::isPassed())
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        $aReview = Phpfox::getService('directory')->getExistingReview($iBusinessId, Phpfox::getUserId());

        return array(
            'error_code' => 0,
            'aItem' => $this->processReviewRow($aReview),
        );
    }

    /**
     * Form edit review
     * @param array $aData[iReviewId]
     * @return array [aItem]
     */
    public function form_edit_review($aData)
    {
        $iReviewId = !empty($aData['iReviewId']) ? (int) $aData['iReviewId'] : null;

        if (empty($iReviewId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        $aReview = Phpfox::getService('directory')->getReviewsById($iReviewId);

        return array(
            'error_code' => 0,
            'aItem' => $this->processReviewRow($aReview),
        );
    }

    /**
     * Add a review
     * @param array $aData[iBusinessId, iRateValue, sContent, sTitle]
     * @return array [$aItem, ...]
     */
    public function add_review($aData)
    {
        $iBusinessId = !empty($aData['iBusinessId']) ? $aData['iBusinessId'] : null;
        $iRating = isset($aData['iRateValue']) ? ((int) $aData['iRateValue'] * 2) : 0;
        $sContent = isset($aData['sContent']) ? $aData['sContent'] : '';
        $sTitle = isset($aData['sTitle']) ? $aData['sTitle'] : '';

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        if ($sTitle == '')
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('directory.you_have_to_input_title'),
            );
        }

        if ($sContent == '')
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('directory.you_have_to_input_content'),
            );
        }

        if ($iRating == 0)
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('directory.you_have_to_rate_this_business'),
            );
        }

        $iOwnerBusinessId = Phpfox::getService('directory')->getBusinessOwnerId($iBusinessId);

        $bCanRate = Phpfox::getService('directory.permission')->canReviewBusiness($iOwnerBusinessId, $iBusinessId);
        
        if (!$bCanRate)
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_do_this_action'),
            );
        }

        Phpfox::getService('directory.process')->addReviewForBusiness($iBusinessId, $sTitle, $sContent, $iRating);        
        
        if (!Phpfox_Error::isPassed())
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        $aReview = Phpfox::getService('directory')->getExistingReview($iBusinessId, Phpfox::getUserId());

        return array(
            'error_code' => 0,
            'aItem' => $this->processReviewRow($aReview),
        );
    }

    /**
     * Fetch review list
     * @param array $aData[int iLimit, int iPage, int iBusinessId]
     * @return array [bCanDelete, bCanEdit, iBusinessId, iRateValue, iReviewId, iTimeStamp, sContent, sModelType, sTitle, user]
     */
    public function fetch_review($aData)
    {
        $iLimit = !empty($aData['iLimit']) ? (int) $aData['iLimit'] : 10;
        $iPage = !empty($aData['iPage']) ? (int) $aData['iPage'] : 1;
        $iBusinessId = !empty($aData['iBusinessId']) ? (int) $aData['iBusinessId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        $iOffset = ($iPage - 1) * $iLimit;
        
        $aRows = $this->database()->select('dr.*, ' . Phpfox::getUserField())
            ->from($this->_sTableReview, 'dr')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = dr.user_id')
            ->where('dr.business_id =' . (int) $iBusinessId)
            ->limit($iOffset, $iLimit)
            ->execute('getSlaveRows');

        $this->processReviewRows($aRows);

        return $aRows;
    }

    /**
     * Process review rows
     * @param array &$aRows
     * @return null
     */
    public function processReviewRows(&$aRows)
    {
        $aTmpRows = $aRows;
        $aRows = array();

        foreach($aTmpRows as $aRow)
        {
            $aRows[] = $this->processReviewRow($aRow);
        }
    }

    /**
     * Process review row
     * @param array $aRow
     * @return array
     */
    public function processReviewRow($aRow)
    {
        return array(
            'bCanDelete' => false,
            'bCanEdit' => ($aRow['user_id'] == Phpfox::getUserId()),
            'iBusinessId' => $aRow['business_id'],
            'iRateValue' => floatval($aRow['rating']) / 2,
            'iReviewId' => $aRow['review_id'],
            'iTimeStamp' => $aRow['timestamp'],
            'sContent' => $aRow['content'],
            'sModelType' => 'directory_review',
            'sTitle' => $aRow['title'],
            'user' => array(
                'id' => $aRow['user_id'],
                'img' => $this->getUserImageUrl($aRow, '_50_square'),
                'title' => $aRow['full_name'],
                'type' => 'user',
            ),
        );
    }

    /**
     * Add feed comment
     * @param array $aData
     * @return
     */
    public function addFeedComment($aData) 
    {
        /**
         * @var array
         */
        $aVals = array(
            'user_status' => isset($aData['sContent']) ? $aData['sContent'] : '',
            'callback_item_id' => isset($aData['iSubjectId']) ? $aData['iSubjectId'] : '',
            'callback_module' => isset($aData['sCallbackModule']) ? $aData['sCallbackModule'] : 'event',
            'is_user_profile' => isset($aData['bIsUserProfile']) ? $aData['bIsUserProfile'] : 0,
            'profile_user_id' => isset($aData['iProfileUserId']) ? $aData['iProfileUserId'] : 0,
            'group_id' => isset($aData['iGroupId']) ? $aData['iGroupId'] : $aData['iCallbackItemId'],
            'iframe' => isset($aData['iIframe']) ? $aData['iIframe'] : 1,
            'method' => isset($aData['sMethod']) ? $aData['sMethod'] : 'simple'
        );

        if (Phpfox::getLib('parse.format')->isEmpty($aVals['user_status'])) 
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('user.add_some_text_to_share')
            );
        }

        $aBusiness = Phpfox::getService('directory')->getBusinessById($aVals['callback_item_id'], true);

        if (!isset($aBusiness['business_id'])) 
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('directory.unable_to_find_the_business_you_are_trying_to_comment_on'),
            );
        }

        $sLink = Phpfox::getLib('url')->permalink('directory.detail', $aBusiness['business_id'], $aBusiness['name']);
        $aCallback = array(
            'module' => 'directory',
            'table_prefix' => 'directory_',
            'link' => $sLink,
            'email_user_id' => $aBusiness['user_id'],
            'subject' => Phpfox::getPhrase('directory.full_name_wrote_a_comment_on_your_business_title', array(
                'full_name' => Phpfox::getUserBy('full_name'),
                'title' => $aBusiness['name']
            )),
            'message' => Phpfox::getPhrase('directory.full_name_wrote_a_comment_on_your_business_a_href_link_title_a_to_see_the_comment_thread_follow_the_link_below_a_href_link_link_a', array(
                'full_name' => Phpfox::getUserBy('full_name'),
                'link' => $sLink,
                'title' => $aBusiness['name']
            )),
            'notification' => 'directory_comment',
            'feed_id' => 'directory_comment',
            'item_id' => $aBusiness['business_id']
        );

        $aVals['parent_user_id'] = $aVals['callback_item_id'];

        if (isset($aVals['user_status']) && ($iId = Phpfox::getService('feed.process')->callback($aCallback)->addComment($aVals))) 
        {
            Phpfox::getLib('database')->updateCounter('directory_business', 'total_comment', 'business_id', $aBusiness['business_id']);
            
            return array(
                'error_code' => 0,
                'iCommentId' => $iId,
                'message' => html_entity_decode(Phpfox::getPhrase("mfox.this_item_has_successfully_been_submitted"))
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
     * Get directory feed by id
     * @param int $iFeedId
     * @return array
     */
    public function getDirectoryFeedById($iFeedId)
    {
        return $this->database()->select('df.*')
            ->from($this->_sTableFeed, 'df')
            ->where('df.feed_id = ' . (int) $iFeedId)
            ->execute('getSlaveRow');
    }

    /**
     * Claim a business
     * @param array $aData[iBusinessId]
     * @return array [aItem, ...]
     */
    public function claim($aData)
    {
        $iBusinessId = !empty($aData['iBusinessId']) ? (int) $aData['iBusinessId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        Phpfox::isUser(true);

        $aBusiness = Phpfox::getService('directory')->getQuickBusinessById($iBusinessId);
        
        if (!isset($aBusiness['business_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('directory.business_is_not_found'),
            );
        }

        if ($aBusiness['type'] != 'claiming' || $aBusiness['business_status'] != Phpfox::getService('directory.helper')->getStatusCode('draft'))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('directory.the_business_is_not_for_claiming_or_claimed'),
            );
        }

        // update business_status = pendingclaiming
        Phpfox::getService('directory.process')->updateTypeOfBusiness($iBusinessId, 'business');

        // send email to owner
        $aUser = Phpfox::getService('user')->getUser(Phpfox::getUserId());
        $language_id = $aUser['language_id'] == null ? 'en' : $aUser['language_id'];
        $email = $aUser['email'];
        $aEmail = Phpfox::getService('directory.mail')->getEmailMessageFromTemplate(1 , $language_id , $iBusinessId, Phpfox::getUserId());
        Phpfox::getService('directory.mail.send')->send($aEmail['subject'], $aEmail['message'], $email);

        if (!Phpfox_Error::isPassed())
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        $aBusiness = $this->detail(array(
            'iBusinessId' => $iBusinessId
        ));

        return array(
            'aItem' => $aBusiness,
            'error_code' => 0,
            'message' => Phpfox::getPhrase('directory.you_have_just_claimed_the_business_title_successfully_please_wait_for_approval_from_administrator', array('title' => $aBusiness['sTitle'])),
        );
    }

    /**
     * Check-in Here
     * @param array $aData[iBusinessId]
     * @return array [aItem, ...]
     */
    public function checkin($aData)
    {
        $iBusinessId = !empty($aData['iBusinessId']) ? (int) $aData['iBusinessId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        Phpfox::isUser(true);

        $aBusiness = Phpfox::getService('directory')->getQuickBusinessById($iBusinessId);

        if (!isset($aBusiness['business_id']))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('directory.business_is_not_found'),
            );
        }
        
        if (!Phpfox::isModule('feed')
            || ($aBusiness['business_status'] != (int) Phpfox::getService('directory.helper')->getConst('business.status.completed')
            && $aBusiness['business_status'] != (int) Phpfox::getService('directory.helper')->getConst('business.status.approved')
            && $aBusiness['business_status'] != (int) Phpfox::getService('directory.helper')->getConst('business.status.running')
        ))
        {
            return array(
                'error_code' => 1,
                'error_message' => ''
            );
        }

        $sFeed = 'directory_checkinhere';
        $iFeedId = Phpfox::getService('feed.process')->add($sFeed, $iBusinessId);
        Phpfox::getService('directory.process')->addCheckinhere($iBusinessId, Phpfox::getUserId());
        Phpfox::getLib('database')->updateCount('directory_checkinhere', 'business_id = ' . (int) $iBusinessId . '', 'total_checkin', 'directory_business', 'business_id = ' . (int) $iBusinessId);

        if (!Phpfox_Error::isPassed())
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        $aBusiness = $this->detail(array(
            'iBusinessId' => $iBusinessId
        ));

        return array(
            'aItem' => $aBusiness,
            'error_code' => 0,
            'message' => Phpfox::getPhrase('directory.check_in_successfully'),
        );
    }

    /**
     * Leave
     * @param array $aData[iBusinessId]
     * @return array [aItem, ...]
     */
    public function leave($aData)
    {
        $iBusinessId = !empty($aData['iBusinessId']) ? (int) $aData['iBusinessId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        Phpfox::isUser(true);

        Phpfox::getService('directory.process')->deleteUserMemberRole($iBusinessId, Phpfox::getUserId());

        if (!Phpfox_Error::isPassed())
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        $aBusiness = $this->detail(array(
            'iBusinessId' => $iBusinessId
        ));

        return array(
            'aItem' => $aBusiness,
            'error_code' => 0,
            'message' => Phpfox::getPhrase('directory.you_leaved_this_business'),
        );
    }

    /**
     * Join
     * @param array $aData[iBusinessId]
     * @return array [aItem, ...]
     */
    public function join($aData)
    {
        $iBusinessId = !empty($aData['iBusinessId']) ? (int) $aData['iBusinessId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        Phpfox::isUser(true);
    
        $iOwnerBusinessId = Phpfox::getService('directory')->getBusinessOwnerId($iBusinessId); 
        $aRole = array();

        if ($iOwnerBusinessId == Phpfox::getUserId())
        {
            $aRole = Phpfox::getService('directory')->getRoleIdByBusinessId($iBusinessId, 'admin');            
        }
        else
        {
            $aRole = Phpfox::getService('directory')->getRoleIdByBusinessId($iBusinessId, 'member');
        }

        Phpfox::getService('directory.process')->updateUserMemberRole($iBusinessId, Phpfox::getUserId(), $aRole['role_id']);

        if (!Phpfox_Error::isPassed())
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        $aBusiness = $this->detail(array(
            'iBusinessId' => $iBusinessId
        ));

        return array(
            'aItem' => $aBusiness,
            'error_code' => 0,
            'message' => Phpfox::getPhrase('directory.you_became_a_member_of_this_business'),
        );
    }

    /**
     * Unfavorite
     * @param array $aData[iBusinessId]
     * @return array [aItem, ...]
     */
    public function unfavourite($aData)
    {
        $iBusinessId = !empty($aData['iBusinessId']) ? (int) $aData['iBusinessId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        Phpfox::isUser(true);

        $iFavoriteId = Phpfox::getLib('database')->select('favorite_id')
            ->from(Phpfox::getT('directory_favorite'))
            ->where("business_id = {$iBusinessId} and user_id = " . Phpfox::getUserId())
            ->execute('getSlaveField');

        if ($iFavoriteId)
        {
            Phpfox::getService('directory.process')->deleteFavorite($iFavoriteId);
        }

        if (!Phpfox_Error::isPassed())
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        $aBusiness = $this->detail(array(
            'iBusinessId' => $iBusinessId
        ));

        return array(
            'aItem' => $aBusiness,
            'error_code' => 0,
            'message' => Phpfox::getPhrase('directory.the_directory_removed_from_your_favorite_list'),
        );
    }

    /**
     * Favorite
     * @param array $aData[iBusinessId]
     * @return array [aItem, ...]
     */
    public function favourite($aData)
    {
        $iBusinessId = !empty($aData['iBusinessId']) ? (int) $aData['iBusinessId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        Phpfox::isUser(true);

        $iFavoriteId = Phpfox::getLib('database')->select('favorite_id')
            ->from(Phpfox::getT('directory_favorite'))
            ->where("business_id = {$iBusinessId} and user_id = " . Phpfox::getUserId())
            ->execute('getSlaveField');

        if (!$iFavoriteId)
        {
            Phpfox::getService('directory.process')->addFavorite($iBusinessId);
        }

        if (!Phpfox_Error::isPassed())
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        $aBusiness = $this->detail(array(
            'iBusinessId' => $iBusinessId
        ));

        return array(
            'aItem' => $aBusiness,
            'error_code' => 0,
            'message' => Phpfox::getPhrase('directory.the_business_was_added_to_your_favorite_list'),
        );
    }

    /**
     * Unfollow
     * @param array $aData[iBusinessId]
     * @return array [aItem, ...]
     */
    public function unfollow($aData)
    {
        $iBusinessId = !empty($aData['iBusinessId']) ? (int) $aData['iBusinessId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        Phpfox::isUser(true);

        $iFollowId = Phpfox::getLib('database')->select('follow_id')
            ->from(Phpfox::getT('directory_follow'))
            ->where("business_id = {$iBusinessId} and user_id = " . Phpfox::getUserId())
            ->execute('getSlaveField');

        if ($iFollowId)
        {
            Phpfox::getService('directory.process')->deleteFollow($iFollowId);
        }

        if (!Phpfox_Error::isPassed())
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        $aBusiness = $this->detail(array(
            'iBusinessId' => $iBusinessId
        ));

        return array(
            'aItem' => $aBusiness,
            'error_code' => 0,
            'message' => Phpfox::getPhrase('directory.the_business_was_removed_from_your_following_list'),
        );
    }

    /**
     * Follow
     * @param array $aData[iBusinessId]
     * @return array [aItem, ...]
     */
    public function follow($aData)
    {
        $iBusinessId = !empty($aData['iBusinessId']) ? (int) $aData['iBusinessId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }

        Phpfox::isUser(true);

        $iFollowId = Phpfox::getLib('database')->select('follow_id')
            ->from(Phpfox::getT('directory_follow'))
            ->where("business_id = {$iBusinessId} and user_id = " . Phpfox::getUserId())
            ->execute('getSlaveField');

        if (!$iFollowId)
        {
            Phpfox::getService('directory.process')->addFollow($iBusinessId);
        }

        if (!Phpfox_Error::isPassed())
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        $aBusiness = $this->detail(array(
            'iBusinessId' => $iBusinessId
        ));

        return array(
            'aItem' => $aBusiness,
            'error_code' => 0,
            'message' => Phpfox::getPhrase('directory.the_business_was_added_to_your_following_list'),
        );
    }

    /**
     * Fetch follower list
     * @param array $aData[int iLimit, int iPage, int iBusinessId]
     * @return array [iUserId, sModelType, sPhotoUrl, sTitle]
     */
    public function fetch_followers($aData)
    {
        $iLimit = !empty($aData['iLimit']) ? (int) $aData['iLimit'] : 10;
        $iPage = !empty($aData['iPage']) ? (int) $aData['iPage'] : 1;
        $iBusinessId = !empty($aData['iBusinessId']) ? (int) $aData['iBusinessId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }
        
        $aConds = array(
            ' 1=1 '
        );

        $aExtra = array(
            'order' => 'df.time_stamp DESC',
            'limit' => $iLimit,
            'page' => ($iPage - 1) * $iLimit,
        );

        list($aRows, $iCnt) = Phpfox::getService('directory')->getFollowersByBusinessId($iBusinessId, $aConds, $aExtra, true);

        $this->processFollowerRows($aRows);

        return $aRows;
    }

    /**
     * Process follower rows
     * @param array &$aRows
     * @return null
     */
    public function processFollowerRows(&$aRows)
    {
        $aTmpRows = $aRows;
        $aRows = array();

        foreach($aTmpRows as $aRow)
        {
            $aRows[] = array(
                'iUserId' => $aRow['user_id'],
                'sModelType' => 'user',
                'sPhotoUrl' => $this->getUserImageUrl($aRow, '_50_square'),
                'sTitle' => $aRow['full_name'],
            );
        }
    }

    /**
     * Fetch member list
     * @param array $aData[int iLimit, int iPage, int iBusinessId]
     * @return array [iUserId, sModelType, sPhotoUrl, sRole, sTitle]
     */
    public function fetch_members($aData)
    {
        $iLimit = !empty($aData['iLimit']) ? (int) $aData['iLimit'] : 10;
        $iPage = !empty($aData['iPage']) ? (int) $aData['iPage'] : 1;
        $iBusinessId = !empty($aData['iBusinessId']) ? (int) $aData['iBusinessId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.parameters_is_not_valid'),
            );
        }
        
        $aConds = array(
            ' 1=1 '
        );

        $aExtra = array(
            'order' => 'dm.time_stamp DESC',
            'limit' => $iLimit,
            'page' => ($iPage - 1) * $iLimit,
        );

        list($aRows, $iCnt) = Phpfox::getService('directory')->getMembersByBusinessId($iBusinessId, $aConds, $aExtra, true);

        $this->processMemberRows($aRows);

        return $aRows;
    }

    /**
     * Process member rows
     * @param array &$aRows
     * @return null
     */
    public function processMemberRows(&$aRows)
    {
        $aTmpRows = $aRows;
        $aRows = array();

        foreach($aTmpRows as $aRow)
        {
            $aRows[] = array(
                'iUserId' => $aRow['user_id'],
                'sModelType' => 'user',
                'sPhotoUrl' => $this->getUserImageUrl($aRow, '_50_square'),
                'sRole' => $aRow['role_title'],
                'sTitle' => $aRow['full_name'],
            );
        }
    }

    /**
     * detail
     * @param array $aData
     * @return array
     */
    public function detail($aData)
    {
        $iBusinessId = !empty($aData['iBusinessId']) ? $aData['iBusinessId'] : null;

        if (empty($iBusinessId))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('directory.business_not_found'),
            );
        }

        $aBusiness = Phpfox::getService('directory')->getBusinessById($iBusinessId);

        if (!$aBusiness)
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('directory.business_not_found'),
            );
        }

        Phpfox::getService('directory')->checkAndUpdateStatus($aBusiness);

        if (!$this->_canView($aBusiness))
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        if ($aBusiness['user_id'] != Phpfox::getUserId())
        {
            Phpfox::getService('directory.process')->updateTotalView($aBusiness['business_id']);
        }

        if ($aBusiness['type'] == 'claiming')
        {
            if (!Phpfox::getUserParam('directory.can_claim_business'))
            {
                return array(
                    'error_code' => 1,
                    'error_message' => Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'),
                );
            }
        }

        $this->_processMoreDetails($aBusiness);

        return $this->_getDetailFields($aBusiness);
    }

    /**
     * _getDetailFields
     * @param array $aBusiness
     * @return array
     */
    private function _getDetailFields($aBusiness)
    {
        return array(
            'aAdditional' => $this->_getAdditional($aBusiness),
            'aAvailableModules' => $this->_getAvailableModules($aBusiness),
            'aCoverPhotos' => $this->_getCoverPhotos($aBusiness),
            'aCoverPhotosEdit' => $this->_getCoverPhotosForEdit($aBusiness),
            // 'aDislikes' => array(),
            'aFax' => $this->_getFaxs($aBusiness),
            'aFounders' => $aBusiness['founder'],
            'aFullCategory' => $this->_getCategories($aBusiness),
            'aLocations' => $this->_getLocations($aBusiness),
            'aOperatingHours' => $this->_getOperatingHours($aBusiness),
            'aPhone' => $this->_getPhones($aBusiness),
            'aWebAddress' => $this->_getWebAddresses($aBusiness),
            'bCanCheckin' => $this->_canCheckin($aBusiness),
            'bCanClaim' => ($aBusiness['type'] == 'claiming'),
            'bCanComment' => $this->_canComment($aBusiness),
            'bCanCreateEvent' => $this->_canAddEvent($aBusiness),
            // 'bCanCreateTopic' => 0,
            'bCanCreateVideo' => $this->_canAddVideo($aBusiness),
            'bCanDelete' => $aBusiness['bCanDelete'],
            // 'bCanDislike' => 0,
            'bCanEdit' => $aBusiness['bCanEdit'],
            'bCanFavourite' => $this->_canFavorite($aBusiness),
            'bCanFollow' => $this->_canFollow($aBusiness),
            'bCanInvite' => $this->_canInvite($aBusiness),
            'bCanJoin' => $this->_canJoin($aBusiness),
            'bCanLeave' => $this->_canLeave($aBusiness),
            'bCanLike' => $this->_canLike($aBusiness),
            'bCanManage' => $aBusiness['canManageDashBoard'],
            'bCanMessageOwner' => $this->_canMessageOwner($aBusiness),
            // 'bCanOpenClose' => 0,
            'bCanReport' => $this->_canReport($aBusiness),
            // 'bCanRequest' => 0,
            'bCanReview' => $this->_canReview($aBusiness),
            'bCanShare' => $this->_canShare($aBusiness),
            'bCanUploadPhoto' => $this->_canAddPhoto($aBusiness),
            'bCanView' => $this->_canView($aBusiness),
            'bIsApproved' => !empty($aBusiness['time_approved']),
            'bIsCheckedIn' => $this->_isCheckedIn($aBusiness),
            // 'bIsClaimed' => 0,
            // 'bIsClaiming' => 0,
            // 'bIsDisliked' => 0,
            'bIsFavourite' => $this->_isFavorited($aBusiness),
            'bIsFeatured' => $aBusiness['featured'],
            'bIsFollowing' => $this->_isFollowing($aBusiness),
            // 'bIsInvited' => 0,
            'bIsLiked' => !empty($aBusiness['is_liked']) ? true : false,
            // 'bIsNeverExpire' => 0,
            'bIsRated' => $this->_isReviewed($aBusiness),
            // 'bIsRequireApproval' => 0,
            'bIsReviewed' => $this->_isReviewed($aBusiness),
            // 'bIsSearch' => 0,
            // 'bIsSentRequest' => 0,
            // 'bShowDislikeUsers' => 0,
            'fRating' => floatval($aBusiness['total_score']) / 2,
            'iBusinessId' => $aBusiness['business_id'],
            'iCategoryId' => $this->_getMainCategoryId($aBusiness),
            'iMaxCover' => $aBusiness['package_max_cover_photo'],
            'iPackageId' => $aBusiness['package_id'],
            'sSize' => $aBusiness['size'],
            'iTimeStamp' => $aBusiness['time_stamp'],
            'iTotalComment' => (int)$aBusiness['total_comment'],
            // 'iTotalDislike' => 0,
            'iTotalEvents' => (int)$this->_getTotalEvents($aBusiness),
            'iTotalFollow' => (int)$this->_getTotalFollowers($aBusiness),
            'iTotalLike' => (int)$aBusiness['total_like'],
            'iTotalMember' => (int)$this->_getTotalMembers($aBusiness),
            'iTotalMusic' => (int)$this->_getTotalMusic($aBusiness),
            'iTotalPhotos' => (int)$this->_getTotalPhotos($aBusiness),
            'iTotalRate' => (int)$aBusiness['total_rating'],
            'iTotalReview' => (int)$aBusiness['total_review'],
            'iTotalVideos' => (int)$this->_getTotalVideos($aBusiness),
            'iTotalView' => (int)$aBusiness['total_view'],
            // 'sApprovedDate' => 0,
            'sCategory' => $this->_getCategoriesText($aBusiness),
            'sCity' => $aBusiness['city'],
            // 'sClaimingStatus' => '',
            'sCountry' => $aBusiness['country_iso'],
            'sDescription' => Phpfox::getLib('parse.output')->parse($aBusiness['description']),
            'sEmail' => $aBusiness['email'],
            // 'sExpireDate' => 0,
            // 'sFacebook' => '',
            // 'sFeatureExpirationDate' => 0,
            'sFullPhotoUrl' => $this->getImageUrl($aBusiness, ''),
            'sHref' => Phpfox::getLib('url')->permalink('directory.detail', $aBusiness['business_id'], $aBusiness['name']),
            // 'sLastPaymentDate' => 0,
            'sLocation' => $aBusiness['location_title'],
            'sModelType' => 'directory',
            'sPackageName' => $aBusiness['package_name'],
            'sPhotoUrl' => $this->getImageUrl($aBusiness, '_400'),
            'sProvince' => $aBusiness['province'],
            'sShortDesc' => Phpfox::getLib('parse.output')->parse($aBusiness['short_description_parsed']),
            'sStatus' => $aBusiness['business_phrase_status'],
            'sTitle' => $aBusiness['name'],
            // 'sTwitter' => '',
            'sVideoModule' => Phpfox::getService('directory.helper')->getModuleIdVideo(),
            'sZipCode' => $aBusiness['postal_code'],
            'user' => $this->getUser($aBusiness),
            'sType' => $aBusiness['type']
        );
    }

    /**
     * Get additional information
     * @param array $aBusiness
     * @return array
     */
    private function _getAdditional($aBusiness)
    {
        $aResults = array();

        if (count($aBusiness['additioninfo']))
        {
            foreach ($aBusiness['additioninfo'] as $key => $value)
            {
                $aResults[] = array(
                    'sTitle' => $value['usercustomfield_title'],
                    'sContent' => $value['usercustomfield_content'],
                );
            }
        }

        $aCustomFields = $this->_getCustomFields($aBusiness['business_id']);

        $aResults = array_merge($aResults, $aCustomFields);

        return $aResults;
    }

    /**
     * Get custom fields
     * @param int $iBusinessId
     * @return array
     */
    private function _getCustomFields($iBusinessId)
    {
        $aResults = array();

        $aMainCategory = Phpfox::getService('directory')->getBusinessMainCategory($iBusinessId);
        $aCustomFields = Phpfox::getService('directory')->getCustomFieldByCategoryId($aMainCategory['category_id']);
        $aCustomData = array();
        $aCustomDataTemp = Phpfox::getService('directory.custom')->getCustomFieldByBusinessId($iBusinessId);
            
        if (count($aCustomFields))
        {
            foreach ($aCustomFields as $aField)
            {
                foreach ($aCustomDataTemp as $aFieldValue)
                {
                    if ($aField['field_id'] == $aFieldValue['field_id'])
                    {
                        $aCustomData[] = $aFieldValue;
                    }
                }
            }
        }

        if (count($aCustomData))
        {
            foreach ($aCustomData as $aField)
            {
                if (isset($aField['value']))
                {
                    switch ($aField['var_type'])
                    {
                        case 'text':
                        case 'textarea':
                            $sContent = $aField['value']; 
                            break;

                        case 'select':
                        case 'multiselect':
                        case 'checkbox':
                        case 'radio':
                            $sContent = '';
                            foreach ($aField['value'] as $value)
                            {
                                $sContent .= Phpfox::getPhrase($value) . '<br>';
                            }
                            $sContent = trim($sContent, '<br>');
                            break;

                        default:
                            $sContent = '';
                            break;
                    }

                    $aResults[] = array(
                        'sTitle' => Phpfox::getPhrase($aField['phrase_var_name']),
                        'sContent' => $sContent,
                    );
                }
            }
        }

        return $aResults;
    }

    /**
     * Process more details
     * @param array &$aBusiness
     * @return null
     */
    private function _processMoreDetails(&$aBusiness)
    {
        $aBusiness['category_id'] = Phpfox::getService('directory.category')->checkMainCategory($aBusiness['business_id']);
        $aBusiness = array_merge($aBusiness, array(
            'canManageDashBoard' => ($aBusiness['type'] != 'claiming') && Phpfox::getService('directory.permission')->canManageBusinessDashBoard($aBusiness['business_id']),
            'canTransferOwner' => Phpfox::getUserId() == $aBusiness['user_id'],
            'canInviteMember' => Phpfox::getService('directory.permission')->canInviteMember($aBusiness['business_id']),
            'childCategory' => Phpfox::getService('directory')->isHaveChildCategory($aBusiness['business_id'], $aBusiness['category_id']),
            'isLiked' => Phpfox::getService('like')->didILike('directory', $aBusiness['business_id']),
            'isMember' => Phpfox::getService('directory')->isMemberOfBusiness($aBusiness['business_id'], Phpfox::getUserId()),
        ));
    }

    /**
     * _canAddEvent
     * @param array $aBusiness
     * @return array
     */
    private function _canAddEvent($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        return Phpfox::getService('directory.permission')->canAddEventInBusiness($iBusinessId);
    }

    /**
     * _canAddVideo
     * @param array $aBusiness
     * @return array
     */
    private function _canAddVideo($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        return Phpfox::getService('directory.permission')->canAddVideoInBusiness($iBusinessId);
    }

    /**
     * _canAddPhoto
     * @param array $aBusiness
     * @return array
     */
    private function _canAddPhoto($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        return Phpfox::getService('directory.permission')->canAddPhotoInBusiness($iBusinessId);
    }

    /**
     * _getWebAddresses
     * @param array $aBusiness
     * @return array
     */
    private function _getWebAddresses($aBusiness)
    {
        $aRows = array();

        if (is_array($aBusiness['websites']) && count($aBusiness['websites']))
        {
            foreach ($aBusiness['websites'] as $aRow)
            {
                $aRows[] = $aRow['website_text'];
            }
        }

        return $aRows;
    }

    /**
     * _getPhones
     * @param array $aBusiness
     * @return array
     */
    private function _getPhones($aBusiness)
    {
        $aRows = array();

        if (is_array($aBusiness['phones']) && count($aBusiness['phones']))
        {
            foreach ($aBusiness['phones'] as $aRow)
            {
                $aRows[] = $aRow['phone_number'];
            }
        }

        return $aRows;
    }

    /**
     * _getOperatingHours
     * @param array $aBusiness
     * @return array
     */
    private function _getOperatingHours($aBusiness)
    {
        $aRows = array();

        if ($aBusiness['disable_visitinghourtimezone'])
        {
            return $aRows;
        }

        if (is_array($aBusiness['vistinghours']) && count($aBusiness['vistinghours']))
        {
            $aVisitingHours = Phpfox::getService('directory.helper')->getVisitingHours();
            $aVisitingHoursDetail = array();
            foreach ($aVisitingHours['dayofweek'] as $key => $visit)
            {
                $aVisitingHoursDetail[$visit['id']] = $visit;
            }

            foreach ($aBusiness['vistinghours'] as $aRow)
            {
                $aRows[] = array(
                    'sDay' => $aVisitingHoursDetail[$aRow['vistinghour_dayofweek']]['phrase'],
                    'sFrom' => $aRow['vistinghour_starttime'],
                    'sTo' => $aRow['vistinghour_endtime'],
                );
            }
        }

        return $aRows;
    }

    /**
     * _getFaxs
     * @param array $aBusiness
     * @return array
     */
    private function _getFaxs($aBusiness)
    {
        $aRows = array();

        if (is_array($aBusiness['faxs']) && count($aBusiness['faxs']))
        {
            foreach ($aBusiness['faxs'] as $aRow)
            {
                $aRows[] = $aRow['fax_number'];
            }
        }

        return $aRows;
    }

    /**
     * _getCoverPhotosForEdit
     * @param array $aBusiness
     * @return array
     */
    private function _getCoverPhotosForEdit($aBusiness)
    {
        return $this->_getCoverPhotos($aBusiness);
    }

    /**
     * _getCoverPhotos
     * @param array $aBusiness
     * @return array
     */
    private function _getCoverPhotos($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;
        $aRows = array();

        $aImages = $this->database()->select('di.*')
            ->from(Phpfox::getT('directory_image'),'di')
            ->where('di.business_id = ' . $iBusinessId)
            ->order('di.ordering ASC')
            ->execute('getSlaveRows');
        
        if ($aImages)
        {
            foreach($aImages as $aImage)
            {
                $aRows[] = array(
                    'iPhotoId' => $aImage['image_id'],
                    'sPhotoUrl' => Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aImage['server_id'],
                        'file' => 'yndirectory/' . $aImage['image_path'],
                        'path' => 'core.url_pic',
                        'suffix' => '',
                        'return_url' => true,
                    )),
                );
            }
        }

        return $aRows;
    }

    /**
     * _getAvailableModules
     * @param array $aBusiness
     * @return array
     */
    private function _getAvailableModules($aBusiness)
    {
        $aModuleView = $this->_getModuleView($aBusiness);
        $aModules = array();

        foreach ($aModuleView as $aModule)
        {
            if ($aModule['is_show'])
            {
                $aModules[] = ($aModule['module_name'] == 'videos') ? 'videochannel' : $aModule['module_name'];
            }
        }

        return $aModules;
    }
    
    /**
     * form_search
     * @param 
     * @return array
     */
    public function form_search() 
    {
        return array(
            'categoryOptions' => $this->getCategoryOptions()
        );
    }
    
    /**
     * getCategoryOptions
     * @param 
     * @return array
     */
    public function getCategoryOptions() 
    {
        $aRows = array();
        $aParents = $this->_getCategoriesByParentId(0);

        // low performance but for the same ordering with full site...
        foreach ($aParents as $aParent)
        {
            $aRows[] = $aParent;

            $aChildren = $this->_getCategoriesByParentId($aParent['id']);
            if (!empty($aChildren))
            {
                $aRows = array_merge($aRows, $aChildren);
            }
        }
        
        return $aRows;
    }

    /**
     * Get categories by parent id
     * @param int $iParentId
     * @return array [id, title]
     */
    private function _getCategoriesByParentId($iParentId = 0)
    {
        $aTmpRows = $this->database()->select('c.*')
            ->from($this->_sTableCategory, 'c')
            ->where('c.parent_id = ' . (int) $iParentId . ' AND c.is_active = 1')
            ->order('c.ordering ASC')
            ->execute('getSlaveRows');

        foreach ($aTmpRows as $aRow) 
        {
            $sTitle = Phpfox::getLib('locale')->convert(Core\Lib::phrase()->isPhrase($aRow['title']) ? _p($aRow['title']) : $aRow['title']);
            $sTitle = Phpfox::getService('mfox')->decodeUtf8Compat($sTitle);

            $aRows[] = array(
                'id' => $aRow['category_id'],
                'title' => (($iParentId > 0) ? '-- ' : '') . $sTitle,
            );
        }
        
        return $aRows;
    }
    
    /**
     * fetch
     * @param array $aData
     * @return array
     */
    public function fetch($aData) 
    {
        return $this->_get($aData);
    }
    
    /**
     * _get
     * @param array $aData
     * @return array
     */
    private function _get($aData) 
    {
        $this->_oReq->set(array(
            'view' => !empty($aData['sView']) ? $aData['sView'] : '',
            'page' => !empty($aData['iPage']) ? (int)$aData['iPage'] : 1,
            'show' => !empty($aData['iLimit']) ? (int)$aData['iLimit'] : 10,
            'sort' => !empty($aData['sOrder']) ? $aData['sOrder'] : '',
            'tag' => !empty($aData['sTag']) ? $aData['sTag'] : '',
            'category' => !empty($aData['iCategoryId']) ? (int)$aData['iCategoryId'] : null,
            'search' => !empty($aData['sSearch']) ? $aData['sSearch'] : '',
            'location_address' => !empty($aData['sLocationAddress']) ? $aData['sLocationAddress'] : null,
            'location_address_lat' => !empty($aData['sLat']) ? $aData['sLat'] : null,
            'location_address_lng' => !empty($aData['sLong']) ? $aData['sLong'] : null,
            'radius' => !empty($aData['sWithin']) ? $aData['sWithin'] : null,
            'module_id' => !empty($aData['sModule']) ? $aData['sModule'] : null,
            'item_id' => !empty($aData['iItemId']) ? (int)$aData['iItemId'] : null,
            'profile' => (!empty($aData['bIsProfile']) && $aData['bIsProfile'] == 'true') ? true : false,
            'profile_id' => !empty($aData['iProfileId']) ? (int)$aData['iProfileId'] : null,
        ));
        
        $sView = $this->_oReq->get('view');
        $sModuleId = $this->_oReq->get('module_id');
        $iItemId = $this->_oReq->get('item_id');
        
        $aParentModule = null;
        if (!empty($sModuleId) && !empty($iItemId)) 
        {
            $aParentModule = array(
                'module_id' => $sModuleId,
                'item_id' => $iItemId
            );
        }
        
        Phpfox::getUserParam('directory.can_view_business', true);
        
        $bIsProfile = false;
        if ($this->_oReq->get('profile') === true) 
        {
            $bIsProfile = true;
            $aUser = Phpfox::getService('user')->get($this->_oReq->get('profile_id'));
            $this->_oSearch->setCondition('AND dbus.user_id = ' . $aUser['user_id']);
        }
        
        $aBrowseParams = array(
            'module_id' => 'directory',
            'alias' => 'dbus',
            'field' => 'business_id',
            'table' => Phpfox::getT('directory_business'),
            'hide_view' => array(
                'pending',
                'my'
            ),
            'service' => 'mfox.directory'
        );
        
        switch ($sView) 
        {
            case 'mybusinesses':
                Phpfox::isUser(true);
                $this->_oSearch->setCondition('AND dbus.user_id = ' . Phpfox::getUserId() . ' AND dbus.type != \'claiming\' AND dbus.business_status != ' . (int)Phpfox::getService('directory.helper')->getConst('business.status.pendingclaiming') . ' AND dbus.business_status != ' . (int)Phpfox::getService('directory.helper')->getConst('business.status.deleted'));
                break;

            case 'myfavoritebusinesses':
                $this->_oSearch->setCondition('AND dfav.user_id = ' . Phpfox::getUserId() . ' AND dbus.business_status IN ' . "(" . Phpfox::getService('directory.helper')->getConst('business.status.approved') . "," . Phpfox::getService('directory.helper')->getConst('business.status.running') . "," . Phpfox::getService('directory.helper')->getConst('business.status.completed') . ")");
                break;

            case 'myfollowingbusinesses':
                $this->_oSearch->setCondition('AND dfo.user_id = ' . Phpfox::getUserId() . ' AND dbus.business_status IN ' . "(" . Phpfox::getService('directory.helper')->getConst('business.status.approved') . "," . Phpfox::getService('directory.helper')->getConst('business.status.running') . "," . Phpfox::getService('directory.helper')->getConst('business.status.completed') . ")");
                break;

            case 'claimingbusiness':
                Phpfox::getService('directory.permission')->canClaimBusiness(true);
                $this->_oSearch->setCondition('AND dbus.type = \'claiming\'' . ' AND dbus.business_status != ' . Phpfox::getService('directory.helper')->getStatusCode('deleted') . ' AND dbus.business_status != ' . Phpfox::getService('directory.helper')->getStatusCode('pendingclaiming'));
                break;

            default:
                if ($bIsProfile === true) 
                {
                    $this->_oSearch->setCondition("AND dbus.module_id = 'directory' AND dbus.user_id = " . $aUser['user_id']);
                    
                    if ($aUser['user_id'] != Phpfox::getUserId() && !Phpfox::isAdmin()) 
                    {
                        $this->_oSearch->setCondition(" AND dbus.business_status IN(" . (Phpfox::getService('directory.helper')->getStatusCode('running')) . ") AND dbus.privacy IN(" . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ")");
                    }
                } 
                else if ($aParentModule != null) 
                {
                    $this->_oSearch->setCondition("AND dbus.module_id = '" . $aParentModule['module_id'] . "' AND dbus.item_id  = " . $aParentModule['item_id'] . " AND dbus.privacy IN(%PRIVACY%) ");
                    $this->_oSearch->setCondition("AND ( (dbus.business_status IN " . "(" . Phpfox::getService('directory.helper')->getConst('business.status.approved') . "," . Phpfox::getService('directory.helper')->getConst('business.status.running') . ")" . " ) || dbus.user_id = " . Phpfox::getUserId() . ")");
                } 
                else
                {
                    $this->_oSearch->setCondition("AND dbus.module_id = 'directory' AND dbus.privacy IN(%PRIVACY%) ");
                }
                
                if (!$bIsProfile && $aParentModule == null && !defined('PHPFOX_IS_PAGES_VIEW')) 
                {
                    $this->_oSearch->setCondition("AND dbus.business_status IN " . "(" . Phpfox::getService('directory.helper')->getConst('business.status.approved') . "," . Phpfox::getService('directory.helper')->getConst('business.status.running') . ")");
                }
                break;
        }
        
        $iCategoryId = $this->_oReq->get('category', null);
        if (!empty($iCategoryId)) 
        {
            $sCategories = $iCategoryId;
            
            $sChildIds = Phpfox::getService('directory.category')->getChildIds($iCategoryId);
            if (!empty($sChildIds)) 
            {
                $sCategories.= ',' . $sChildIds;
            }
            
            $this->_oSearch->setCondition('AND dcd.category_id IN(' . $sCategories . ')');
        }
        
        $sLocation = $this->_oReq->get('location_address');
        $sLocationLat = floatval($this->_oReq->get('location_address_lat'));
        $sLocationLng = floatval($this->_oReq->get('location_address_lng'));
        $iRadius = floatval($this->_oReq->get('radius'));
        
        if ($iRadius == 0) 
        {
            $iRadius = 1;
        }
        
        if ($iRadius > 0 && $sLocation != '') 
        {
            $this->_oSearch->setCondition("AND (
                (3959 * acos(
                        cos( radians('{$sLocationLat}')) 
                        * cos( radians( dbl.location_latitude ) ) 
                        * cos( radians( dbl.location_longitude ) - radians('{$sLocationLng}') ) 
                        + sin( radians('{$sLocationLat}') ) * sin( radians( dbl.location_latitude ) ) 
                    ) < {$iRadius} 
                )
            )");
        }
        
        $sTag = $this->_oReq->get('tag');
        if (!empty($sTag)) 
        {
            if ($aTag = Phpfox::getService('tag')->getTagInfo('business', $sTag)) 
            {
                $this->_oSearch->setCondition('AND tag.tag_text = \'' . Phpfox::getLib('database')->escape($aTag['tag_text']) . '\'');
            }
        }
        
        // search
        $sSearch = $this->_oReq->get('search');
        if (!empty($sSearch)) 
        {
            $this->_oSearch->setCondition('AND dbus.name LIKE "%' . Phpfox::getLib('parse.input')->clean($sSearch) . '%"');
        }
        
        // sort
        $sSort = '';
        switch ($this->_oReq->get('sort')) 
        {
            case 'oldest':
                $sSort = 'dbus.time_stamp ASC';
                break;

            case 'a-z':
                $sSort = 'dbus.name ASC';
                break;

            case 'z-a':
                $sSort = 'dbus.name DESC';
                break;

            default:
                
                // newest
                $sSort = 'dbus.time_stamp DESC';
                break;
        }
        
        $this->_oSearch->setSort($sSort);
        
        $this->_oBrowse->params($aBrowseParams)->execute();
        
        $aRows = $this->_oBrowse->getRows();
        
        return $aRows;
    }
    
    /**
     * processRows
     * @param array $aRows
     * @return 
     */
    public function processRows(&$aRows) 
    {
        $aTmpRows = $aRows;
        $aRows = array();
        
        foreach ($aTmpRows as $aBusiness) 
        {
            $aBusiness = Phpfox::getService('directory')->retrieveMoreInfoFromBusiness($aBusiness, '');
            $aBusiness['childCategory'] = Phpfox::getService('directory')->isHaveChildCategory($aBusiness['business_id'], $aBusiness['category_id']);
            $aBusiness['canManageDashBoard'] = ($aBusiness['type'] != 'claiming') && Phpfox::getService('directory.permission')->canManageBusinessDashBoard($aBusiness['business_id']);
            
            $aRows[] = array(
                // 'aDislikes' => array(),
                'aLocations' => $this->_getLocations($aBusiness),
                'bCanCheckin' => $this->_canCheckin($aBusiness),
                'bCanClaim' => ($aBusiness['type'] == 'claiming'),
                'bCanComment' => $this->_canComment($aBusiness),
                'bCanDelete' => $aBusiness['bCanDelete'],
                // 'bCanDislike' => 0,
                'bCanEdit' => $aBusiness['bCanEdit'],
                'bCanFavourite' => $this->_canFavorite($aBusiness),
                'bCanFollow' => $this->_canFollow($aBusiness),
                'bCanInvite' => $this->_canInvite($aBusiness),
                'bCanJoin' => $this->_canJoin($aBusiness),
                'bCanLeave' => $this->_canLeave($aBusiness),
                'bCanLike' => $this->_canLike($aBusiness),
                'bCanManage' => $aBusiness['canManageDashBoard'],
                'bCanMessageOwner' => $this->_canMessageOwner($aBusiness),
                // 'bCanOpenClose' => 0,
                'bCanReport' => $this->_canReport($aBusiness),
                // 'bCanRequest' => 0,
                'bCanReview' => $this->_canReview($aBusiness),
                'bCanShare' => $this->_canShare($aBusiness),
                'bCanView' => $this->_canView($aBusiness),
                'bIsApproved' => !empty($aBusiness['time_approved']),
                'bIsCheckedIn' => $this->_isCheckedIn($aBusiness),
                // 'bIsClaimed' => 0,
                // 'bIsClaiming' => 0,
                // 'bIsDisliked' => 0,
                'bIsFavourite' => $this->_isFavorited($aBusiness),
                'bIsFeatured' => $aBusiness['featured'],
                'bIsFollowing' => $this->_isFollowing($aBusiness),
                // 'bIsInvited' => 0,
                'bIsLiked' => !empty($aBusiness['is_liked']) ? true : false,
                // 'bIsNeverExpire' => 0,
                'bIsRated' => $this->_isReviewed($aBusiness),
                // 'bIsRequireApproval' => 0,
                'bIsReviewed' => $this->_isReviewed($aBusiness),
                // 'bIsSentRequest' => 0,
                // 'bShowDislikeUsers' => 0,
                'fRating' => floatval($aBusiness['total_score']) / 2,
                'iBusinessId' => $aBusiness['business_id'],
                'iCategoryId' => $aBusiness['category_id'],
                'iPackageId' => $aBusiness['package_id'],
                'sSize' => $aBusiness['size'],
                'iTimeStamp' => $aBusiness['time_stamp'],
                'iTotalComment' => (int)$aBusiness['total_comment'],
                // 'iTotalDislike' => 0,
                'iTotalFollow' => (int)$this->_getTotalFollowers($aBusiness),
                'iTotalLike' => (int)$aBusiness['total_like'],
                'iTotalMember' => (int)$this->_getTotalMembers($aBusiness),
                'iTotalRate' => (int)$aBusiness['total_rating'],
                'iTotalReview' => (int)$aBusiness['total_review'],
                'iTotalView' => (int)$aBusiness['total_view'],
                // 'sApprovedDate' => "",
                'sCategory' => $this->_getMainCategoryText($aBusiness),
                // 'sClaimingStatus' => "",
                // 'sExpireDate' => "",
                // 'sFeatureExpirationDate' => 0,
                'sFullPhotoUrl' => $this->getImageUrl($aBusiness),
                'sHref' => Phpfox::getLib('url')->permalink('directory.detail', $aBusiness['business_id'], $aBusiness['name']),
                // 'sLastPaymentDate' => "",
                'sLocation' => $aBusiness['location_title'],
                'sModelType' => "directory",
                'sPackageName' => $aBusiness['package_name'],
                'sPhotoUrl' => $this->getImageUrl($aBusiness, '_400'),
                'sStatus' => $aBusiness['business_phrase_status'],
                'sTitle' => $aBusiness['name'],
                'user' => $this->getUser($aBusiness),
            );
        }
    }

    /**
     * getUser
     * @param array $aBusiness
     * @return array
     */
    public function getUser($aBusiness)
    {
        return array(
            'id' => $aBusiness['user_id'],
            'img' => $this->getUserImageUrl($aBusiness, '_50_square'),
            'title' => $aBusiness['full_name'],
            'type' => "user",
        );
    }

    /**
     * getUserImageUrl
     * @param array $aRow, string $sSuffix = ''
     * @return string
     */
    public function getUserImageUrl($aRow, $sSuffix = '')
    {
        return Phpfox::getService('mfox.user')->getImageUrl($aRow, $sSuffix);
    }

    /**
     * getImageUrl
     * @param array $aBusiness, string $sSuffix = ''
     * @return string
     */
    public function getImageUrl($aBusiness, $sSuffix = '')
    {
        if (empty($aBusiness['logo_path']))
        {
            return $this->getDefaultImageUrl();
        }

        return Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aBusiness['server_id'],
                'path' => 'core.url_pic',
                'file' => $aBusiness['logo_path'],
                'suffix' => $sSuffix,
                'return_url' => true
            )
        );
    }

    /**
     * Get default image url
     * @param 
     * @return string
     */
    public function getDefaultImageUrl()
    {
        return Phpfox::getParam('core.path') . 'module/directory/static/image/default_ava.png';
    }

    /**
     * _getMainCategoryText
     * @param array $aBusiness
     * @return string
     */
    private function _getMainCategoryText($aBusiness)
    {
        $sText = Phpfox::getLib('locale')->convert(Core\Lib::phrase()->isPhrase($aBusiness['category_title']) ? _p($aBusiness['category_title']) : $aBusiness['category_title']);

        if (isset($aBusiness['childCategory']) && isset($aBusiness['childCategory']['title']))
        {
            $sText .= ' >> ' . Phpfox::getLib('locale')->convert(Core\Lib::phrase()->isPhrase($aBusiness['childCategory']['title']) ? _p($aBusiness['childCategory']['title']) : $aBusiness['childCategory']['title']);
        }

        return $sText;
    }

    /**
     * _getMainCategoryId
     * @param array $aBusiness
     * @return string
     */
    private function _getMainCategoryId($aBusiness)
    {
        $iCategoryId = $aBusiness['category_id'];

        if (isset($aBusiness['childCategory']) && isset($aBusiness['childCategory']['category_id']))
        {
            $iCategoryId = $aBusiness['childCategory']['category_id'];
        }

        return $iCategoryId;
    }

    /**
     * _getCategoriesText
     * @param array $aBusiness
     * @return string
     */
    private function _getCategoriesText($aBusiness)
    {
        $sTextCategories = "";

        $aCategories = $this->_getCategories($aBusiness);
        
        foreach ($aCategories as $key_category => $aCategory)
        {
            $sTextCategories .= ' '.Core\Lib::phrase()->isPhrase($aCategory['title']) ? _p($aCategory['title']) : $aCategory['title'];
            
            if (isset($aCategory['sub']) && count($aCategory['sub']))
            {
                foreach ($aCategory['sub'] as $key_sub_category => $aSubCategory)
                {
                    $sTextCategories .= ' >> '.Core\Lib::phrase()->isPhrase($aSubCategory['title']) ? _p($aSubCategory['title']) : $aSubCategory['title'];
                }
            }

            $sTextCategories .= '|';
        }

        $sTextCategories = rtrim($sTextCategories, '|');

        return $sTextCategories;
    }

    /**
     * _getCategories
     * @param array $aBusiness
     * @return array
     */
    private function _getCategories($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        return Phpfox::getService('directory.category')->getForBrowseByBusinessId($iBusinessId);
    }

    /**
     * _getTotalEvents
     * @param array $aBusiness
     * @return int
     */
    private function _getTotalEvents($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        return Phpfox::getService('directory')->getNumberOfItemInBusiness($iBusinessId, 'events');
    }

    /**
     * _getTotalFollowers
     * @param array $aBusiness
     * @return int
     */
    private function _getTotalFollowers($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        return Phpfox::getService('directory')->getCountFollowerOfBusiness($iBusinessId);
    }

    /**
     * _getTotalMembers
     * @param array $aBusiness
     * @return int
     */
    private function _getTotalMembers($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        return Phpfox::getService('directory')->getCountMemberOfBusiness($iBusinessId);
    }

    /**
     * _getTotalMusic
     * @param array $aBusiness
     * @return int
     */
    private function _getTotalMusic($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        return Phpfox::getService('directory')->getNumberOfItemInBusiness($iBusinessId, 'musics');
    }

    /**
     * _getTotalPhotos
     * @param array $aBusiness
     * @return int
     */
    private function _getTotalPhotos($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        return Phpfox::getService('directory')->getNumberOfItemInBusiness($iBusinessId, 'photos');
    }

    /**
     * _getTotalVideos
     * @param array $aBusiness
     * @return int
     */
    private function _getTotalVideos($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        return Phpfox::getService('directory')->getNumberOfItemInBusiness($iBusinessId, 'videos');
    }

    /**
     * _isReviewed
     * @param array $aBusiness
     * @return boolean
     */
    private function _isReviewed($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        $aReview = Phpfox::getService('directory')->getExistingReview($iBusinessId, Phpfox::getUserId());

        if (!empty($aReview))
        {
            return true;
        }

        return false;
    }

    /**
     * _isFollowing
     * @param array $aBusiness
     * @return boolean
     */
    private function _isFollowing($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        return Phpfox::getService('directory')->isFollowBusiness(Phpfox::getUserId(), $iBusinessId);
    }

    /**
     * _isFavorited
     * @param array $aBusiness
     * @return boolean
     */
    private function _isFavorited($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        return Phpfox::getService('directory')->isFavoriteBusiness(Phpfox::getUserId(), $iBusinessId);
    }

    /**
     * _isCheckedIn
     * @param array $aBusiness
     * @return boolean
     */
    private function _isCheckedIn($aBusiness)
    {
        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        if (!Phpfox::isUser())
        {
            return false;
        }

        $aCheckinhere = Phpfox::getService('directory')->getCheckinhere($iBusinessId, Phpfox::getUserId());

        if (isset($aCheckinhere['checkinhere_id']))
        {
            return true;
        }

        return false;
    }
    
    /**
     * _canView
     * @param array $aBusiness
     * @return boolean
     */
    private function _canView($aBusiness) 
    {
        if (!Phpfox::getUserParam('directory.can_view_business')) 
        {
            return Phpfox_Error::set(Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }
        
        if (Phpfox::isModule('privacy')) 
        {
            if (!Phpfox::getService('privacy')->check('directory', $aBusiness['business_id'], $aBusiness['user_id'], $aBusiness['privacy'], $aBusiness['is_friend'], true)) 
            {
                return Phpfox_Error::set(Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
            }
        }
        
        if ($aBusiness['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aBusiness['item_id'], 'directory.view_browse_business')) 
        {
            return Phpfox_Error::set(Phpfox::getPhrase('directory.unable_to_view_this_item_due_to_privacy_settings'));
        }
        
        switch ($aBusiness['business_status']) 
        {
            case Phpfox::getService('directory.helper')->getConst('business.status.draft'):
                if ($aBusiness['user_id'] != Phpfox::getUserId()) 
                {
                    if (($aBusiness['type'] != 'claiming' || $aBusiness['business_status'] != Phpfox::getService('directory.helper')->getConst('business.status.draft'))) 
                    {
                        return Phpfox_Error::set(Phpfox::getPhrase('subscribe.the_feature_or_section_you_are_attempting_to_use_is_not_permitted_with_your_membership_level'));
                    }
                }
                break;

            case Phpfox::getService('directory.helper')->getConst('business.status.completed'):
                if ($aBusiness['user_id'] == Phpfox::getUserId() || Phpfox::isAdmin() || $aBusiness['canManageDashBoard']) 
                {
                } 
                else
                {
                    return Phpfox_Error::set(Phpfox::getPhrase('subscribe.the_feature_or_section_you_are_attempting_to_use_is_not_permitted_with_your_membership_level'));
                }
                break;

            case Phpfox::getService('directory.helper')->getConst('business.status.pending'):
                if ($aBusiness['user_id'] != Phpfox::getUserId()) 
                {
                    return Phpfox_Error::set(Phpfox::getPhrase('subscribe.the_feature_or_section_you_are_attempting_to_use_is_not_permitted_with_your_membership_level'));
                }
                break;
        }
        
        return true;
    }
    
    /**
     * _canCheckin
     * @param array $aBusiness
     * @return boolean
     */
    private function _canCheckin($aBusiness) 
    {
        return (($aBusiness['business_status'] != (int)Phpfox::getService('directory.helper')->getConst('business.status.draft')) && $aBusiness['bCanCheckinhere']);
    }
    
    /**
     * _canShare
     * @param array $aBusiness
     * @return boolean
     */
    private function _canShare($aBusiness) 
    {
        return (($aBusiness['business_status'] != (int)Phpfox::getService('directory.helper')->getConst('business.status.draft')) && $aBusiness['setting_support']['allow_users_to_share_business']);
    }
    
    /**
     * _canReview
     * @param array $aBusiness
     * @return boolean
     */
    private function _canReview($aBusiness) 
    {
        if (Phpfox::getUserId() == $aBusiness['user_id'])
        {
            return false;
        }

        $aReviewedByUser = Phpfox::getService('directory')->getExistingReview($aBusiness['business_id'], Phpfox::getUserId());

        if (count($aReviewedByUser))
        {
            return false;
        }
        
        return true;
    }
    
    /**
     * _canReport
     * @param array $aBusiness
     * @return boolean
     */
    private function _canReport($aBusiness) 
    {
        return ($aBusiness['business_status'] != (int)Phpfox::getService('directory.helper')->getConst('business.status.draft'));
    }
    
    /**
     * _canMessageOwner
     * @param array $aBusiness
     * @return boolean
     */
    private function _canMessageOwner($aBusiness) 
    {
        return (($aBusiness['business_status'] != (int)Phpfox::getService('directory.helper')->getConst('business.status.draft')) && PhpFox::getUserId() != $aBusiness['user_id']);
    }
    
    /**
     * _canLike
     * @param array $aBusiness
     * @return boolean
     */
    private function _canLike($aBusiness) 
    {
        return (Phpfox::isUser() && $aBusiness['type'] != 'claiming' && ($aBusiness['business_status'] != (int)Phpfox::getService('directory.helper')->getConst('business.status.draft')));
    }
    
    /**
     * _canLeave
     * @param array $aBusiness
     * @return boolean
     */
    private function _canLeave($aBusiness) 
    {
        return (($aBusiness['business_status'] != (int)Phpfox::getService('directory.helper')->getConst('business.status.draft')) && PhpFox::getUserId() != $aBusiness['user_id'] && $aBusiness['setting_support']['allow_users_to_confirm_working_at_the_business'] && $aBusiness['isMember']);
    }
    
    /**
     * _canJoin
     * @param array $aBusiness
     * @return boolean
     */
    private function _canJoin($aBusiness) 
    {
        return (($aBusiness['business_status'] != (int)Phpfox::getService('directory.helper')->getConst('business.status.draft')) && PhpFox::getUserId() != $aBusiness['user_id'] && $aBusiness['setting_support']['allow_users_to_confirm_working_at_the_business'] && !$aBusiness['isMember']);
    }
    
    /**
     * _canInvite
     * @param array $aBusiness
     * @return boolean
     */
    private function _canInvite($aBusiness) 
    {
        return (($aBusiness['business_status'] != (int)Phpfox::getService('directory.helper')->getConst('business.status.draft')) && $aBusiness['canInviteMember'] && $aBusiness['setting_support']['allow_users_to_invite_friends_to_business']);
    }
    
    /**
     * _canFollow
     * @param array $aBusiness
     * @return boolean
     */
    private function _canFollow($aBusiness) 
    {
        return ($aBusiness['business_status'] != (int)Phpfox::getService('directory.helper')->getConst('business.status.draft'));
    }
    
    /**
     * _canFavorite
     * @param array $aBusiness
     * @return boolean
     */
    private function _canFavorite($aBusiness) 
    {
        return ($aBusiness['business_status'] != (int)Phpfox::getService('directory.helper')->getConst('business.status.draft'));
    }

    /**
     * Check permission of posting comment
     * @param int $iBusinessId, string $sModuleId = '', int $iItemId = 0
     * @return array
     */
    public function canPostComment($iBusinessId, $sModuleId = '', $iItemId = 0)
    {
        $aError = $this->canView($iBusinessId, $sModuleId, $iItemId);
        if (!empty($aError))
        {
            return $aError;
        }

        if (!$this->_canComment($aBusiness))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('mfox.you_do_not_have_permission_to_post_comment_on_this_item'),
            );
        }

        return null;
    }

    /**
     * Check permission of viewing
     * @param int $iBusinessId, string $sModuleId = '', int $iItemId = 0
     * @return array
     */
    public function canView($iBusinessId, $sModuleId = '', $iItemId = 0)
    {
        if (!empty($sModuleId) && !empty($iItemId))
        {
            $aCallback = array(
                'module' => $sModuleId,
                'item' => $iItemId
            );
        }

        if (!($aBusiness = Phpfox::getService('directory')->callback($aCallback)->getBusinessById($iBusinessId)))
        {
            return array(
                'error_code' => 1,
                'error_message' => Phpfox::getPhrase('directory.business_is_not_found'),
            );
        }

        if (!$this->_canView($aBusiness))
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get()),
            );
        }

        return null;
    }

    /**
     * _canComment
     * @param array $aBusiness
     * @return boolean
     */
    private function _canComment($aBusiness) 
    {
        $bCanPostComment = true;
        
        if (isset($aBusiness['privacy_comment']) && $aBusiness['user_id'] != Phpfox::getUserId() && !Phpfox::getUserParam('privacy.can_comment_on_all_items')) 
        {
            switch ($aBusiness['privacy_comment']) 
            {
                case 1:
                    if (!Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aBusiness['user_id'])) 
                    {
                        $bCanPostComment = false;
                    }
                    break;
                    
                case 2:
                    if (!Phpfox::getService('friend')->isFriendOfFriend($aBusiness['user_id'])) 
                    {
                        $bCanPostComment = false;
                    }
                    break;
                    
                case 3:
                    $bCanPostComment = false;
                    break;
            }
        }
        
        if (Phpfox::getUserId()) 
        {
            $bIsBlocked = Phpfox::getService('user.block')->isBlocked($aBusiness['user_id'], Phpfox::getUserId());
            if ($bIsBlocked) 
            {
                $bCanPostComment = false;
            }
        }

        return $bCanPostComment;
    }
    
    /**
     * _isShowReviews
     * @param array $aBusiness
     * @return boolean
     */
    private function _isShowReviews($aBusiness) 
    {
        $aModuleView = $this->_getModuleView($aBusiness);
        
        return (($aBusiness['business_status'] != (int)Phpfox::getService('directory.helper')->getConst('business.status.draft')) && isset($aModuleView['reviews']) && $aModuleView['reviews']['is_show']);
    }
    
    /**
     * _isShowActivities
     * @param array $aBusiness
     * @return boolean
     */
    private function _isShowActivities($aBusiness) 
    {
        $aModuleView = $this->_getModuleView($aBusiness);
        
        return (($aBusiness['business_status'] != (int)Phpfox::getService('directory.helper')->getConst('business.status.draft')) && isset($aModuleView['activities']) && $aModuleView['activities']['is_show']);
    }
    
    /**
     * _getModuleView
     * @param array $aBusiness
     * @return array
     */
    private function _getModuleView($aBusiness) 
    {
        if (!is_array($aBusiness)) 
        {
            $iBusinessId = $aBusiness;
            $aBusiness = Phpfox::getService('directory')->getBusinessById($iBusinessId);
        } 
        else
        {
            $iBusinessId = $aBusiness['business_id'];
        }
        
        $aModules = Phpfox::getService('directory')->getPageModuleForManage($iBusinessId);
        $aModuleView = array();
        
        foreach ($aModules[0] as $iModuleId => $aModule) 
        {
            $aItem = Phpfox::getService('directory')->getPageByBusinessModuleId($iBusinessId, $iModuleId);
            if (isset($aItem['module_name'])) 
            {
                $aTmpItem = $aItem;
                
                $sTitle = $aBusiness['name'];
                if (!empty($sTitle)) 
                {
                    if (preg_match('/\{phrase var\=(.*)\}/i', $sTitle, $aMatches) && isset($aMatches[1])) 
                    {
                        $sTitle = str_replace(array(
                            "'",
                            '"',
                            '&#039;'
                        ), '', $aMatches[1]);
                        $sTitle = Phpfox::getPhrase($sTitle);
                    }
                    
                    $sTitle = Phpfox::getLib('url')->cleanTitle($sTitle);
                }
                
                $aTmpItem['link'] = Phpfox::getLib('url')->makeUrl('directory.detail' . '.' . $iBusinessId . '.' . $sTitle . '.' . $aTmpItem['module_name']);
                
                $aTmpItem['active'] = false;
                if ($sView == '' && $sViewPage == 0 && $aTmpItem['module_landing']) 
                {
                    $aTmpItem['active'] = true;
                } 
                else if ($sView == $aTmpItem['module_name']) 
                {
                    $aTmpItem['active'] = true;
                }
                
                $oPermission = Phpfox::getService('directory.permission');
                switch ($aTmpItem['module_name']) 
                {
                    case 'photos':
                        if (!$oPermission->canViewPhotoInBusiness($iBusinessId)) 
                        {
                            $aTmpItem['is_show'] = false;
                        }
                        break;

                    case 'videos':
                        if (!$oPermission->canViewVideoInBusiness($iBusinessId)) 
                        {
                            $aTmpItem['is_show'] = false;
                        }
                        break;

                    case 'musics':
                        if (!$oPermission->canViewMusicInBusiness($iBusinessId)) 
                        {
                            $aTmpItem['is_show'] = false;
                        }
                        break;

                    case 'blogs':
                        if (!$oPermission->canViewBlogInBusiness($iBusinessId)) 
                        {
                            $aTmpItem['is_show'] = false;
                        }
                        break;

                    case 'polls':
                        if (!$oPermission->canViewPollsInBusiness($iBusinessId)) 
                        {
                            $aTmpItem['is_show'] = false;
                        }
                        break;

                    case 'coupons':
                        if (!$oPermission->canViewCouponInBusiness($iBusinessId)) 
                        {
                            $aTmpItem['is_show'] = false;
                        }
                        break;

                    case 'events':
                        if (!$oPermission->canViewEventInBusiness($iBusinessId)) 
                        {
                            $aTmpItem['is_show'] = false;
                        }
                        break;

                    case 'jobs':
                        if (!$oPermission->canViewJobInBusiness($iBusinessId)) 
                        {
                            $aTmpItem['is_show'] = false;
                        }
                        break;

                    case 'marketplace':
                        if (!$oPermission->canViewMarketplaceInBusiness($iBusinessId)) 
                        {
                            $aTmpItem['is_show'] = false;
                        }
                        break;

                    default:
                        
                        // code...
                        break;
                }
                
                $aModuleView[$aTmpItem['module_name']] = $aTmpItem;
            }
        }
        
        return $aModuleView;
    }

    /**
     * _getLocations
     * @param array $aBusiness
     * @return array
     */
    private function _getLocations($aBusiness)
    {
        $aLocations = array();

        $iBusinessId = is_array($aBusiness) ? $aBusiness['business_id'] : $aBusiness;

        $aRows = $this->database()->select('dbl.*')
            ->from(Phpfox::getT('directory_business_location'), 'dbl')
            ->where('dbl.business_id = ' . $iBusinessId)
            ->order('dbl.location_id ASC')
            ->execute('getSlaveRows');

        foreach ($aRows as $aRow)
        {
            $aLocations[] = array(
                'fLatitude' => $aRow['location_latitude'],
                'fLongitude' => $aRow['location_longitude'],
                'iBusinessId' => $iBusinessId,
                'iLocationId' => $aRow['location_id'],
                'sLocation' => $aRow['location_address'],
                'sLocationTitle' => $aRow['location_title'],
            );
        }

        return $aLocations;
    }
    
    /**
     * query
     * @param 
     * @return 
     */
    public function query() 
    {
        $this->database()->select('dbt.description_parsed AS description, ');
        
        if (Phpfox::getParam('core.section_privacy_item_browsing')) 
        {
            $this->database()->select('dc.category_id,dc.title AS category_title, ');
            $this->database()->innerJoin(Phpfox::getT('directory_category_data'), 'dcd', 'dcd.business_id = dbus.business_id AND dcd.is_main = 1');
            $this->database()->join(Phpfox::getT('directory_category'), 'dc', 'dc.category_id = dcd.category_id');
        } 
        else
        {
            $this->database()->select('dc.category_id,dc.title AS category_title, ');
        }
        
        $this->database()->leftJoin(Phpfox::getT('directory_business_text'), 'dbt', 'dbt.business_id = dbus.business_id');
        
        if (Phpfox::isUser() && Phpfox::isModule('like')) 
        {
            $this->database()->select('lik.like_id AS is_liked, ')->leftJoin(Phpfox::getT('like'), 'lik', 'lik.type_id = \'directory\' AND lik.item_id = dbus.business_id AND lik.user_id = ' . Phpfox::getUserId());
        }
    }
    
    /**
     * getQueryJoins
     * @param boolean $bIsCount = false, boolean $bNoQueryFriend = false
     * @return 
     */
    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false) 
    {
        $this->database()->innerJoin(Phpfox::getT('user'), 'userDelete', 'userDelete.user_id = dbus.user_id');
        $this->database()->innerJoin(Phpfox::getT('directory_category_data'), 'dcd', 'dcd.business_id = dbus.business_id');
        $this->database()->innerJoin(Phpfox::getT('directory_category'), 'dc', 'dc.category_id = dcd.category_id');
        
        if (!$bIsCount) 
        {
            $this->database()->join(Phpfox::getT('directory_business_location'), 'dbl', 'dbl.business_id = dbus.business_id');
            $this->database()->group('dbus.business_id');
        }
        
        $sLocation = $this->_oReq->get('location_address');
        if ($bIsCount && $sLocation != '') 
        {
            $this->database()->join(Phpfox::getT('directory_business_location'), 'dbl', 'dbl.business_id = dbus.business_id');
        }
        
        if (Phpfox::isModule('friend') && Phpfox::getService('mfox.friend')->queryJoin($bNoQueryFriend)) 
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = dbus.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }
        
        if (Phpfox::getParam('core.section_privacy_item_browsing')) 
        {
            if ($this->_oSearch->isSearch()) 
            {
                $this->database()->join(Phpfox::getT('directory_business_text'), 'dbt', 'dbt.business_id = dbus.business_id');
            }
        } 
        else
        {
            if ($bIsCount && $this->_oSearch->isSearch()) 
            {
                $this->database()->join(Phpfox::getT('directory_business_text'), 'dbt', 'dbt.business_id = dbus.business_id');
            }
        }
        
        if ($this->_oReq->get('view') && $this->_oReq->get('view') == 'myfavoritebusinesses') 
        {
            $this->database()->join(Phpfox::getT('directory_favorite'), 'dfav', 'dfav.business_id = dbus.business_id');
        }
        
        if ($this->_oReq->get('view') && $this->_oReq->get('view') == 'myfollowingbusinesses') 
        {
            $this->database()->join(Phpfox::getT('directory_follow'), 'dfo', 'dfo.business_id = dbus.business_id');
        }
        
        $sTag = $this->_oReq->get('tag');
        if (!empty($sTag)) 
        {
            $this->database()->innerJoin(Phpfox::getT('tag'), 'tag', 'tag.item_id = dbus.business_id AND tag.category_id = \'business\'');
        }
    }
}
