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
 * @since May 31, 2013
 * @link Mfox Api v1.0
 */
class Mfox_Service_Comment extends Phpfox_Service {

    /**
     * Not for feed.
     * @param array $aData
     * @return boolean
     */
    public function checkCanPostCommentOnItem($aItem)
    {
        /**
         * @var bool
         */
        $bCanPostComment = true;
        if (isset($aItem['privacy_comment']) && $aItem['user_id'] != Phpfox::getUserId() && !Phpfox::getUserParam('privacy.can_comment_on_all_items'))
        {
            switch ($aItem['privacy_comment']) {
                // Everyone is case 0. Skipped.
                // Friends only
                case 1:
                    if (!Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aItem['user_id']))
                    {
                        $bCanPostComment = false;
                    }
                    break;
                // Friend of friends
                case 2:
                    if (!Phpfox::getService('friend')->isFriendOfFriend($aItem['user_id']))
                    {
                        $bCanPostComment = false;

                        if (Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aItem['user_id']))
                        {
                            $bCanPostComment = true;
                        }
                    }
                    break;
                // Only me
                case 3:
                    $bCanPostComment = false;
                    break;
            }
        }

        if (Phpfox::getUserId())
        {
            $bIsBlocked = Phpfox::getService('user.block')->isBlocked($aItem['user_id'], Phpfox::getUserId());
            if ($bIsBlocked)
            {
                $bCanPostComment = false;
            }
        }

        return $bCanPostComment;
    }

    /**
     * Check can post comment or not.
     * @param array $aData
     * @return boolean
     */
    public function checkCanPostComment($aData)
    {
        /**
         * @var bool
         */
        $bCanPostComment = true;
        if (isset($aData['comment_privacy']) && $aData['user_id'] != Phpfox::getUserId() && !Phpfox::getUserParam('privacy.can_comment_on_all_items'))
        {
            switch ($aData['comment_privacy']) {
                case 1:
                    if ((int) $aData['feed_is_friend'] <= 0)
                    {
                        $bCanPostComment = false;
                    }
                    break;
                case 2:
                    if ((int) $aData['feed_is_friend'] > 0)
                    {
                        $bCanPostComment = true;
                    }
                    else
                    {
                        if (!Phpfox::getService('friend')->isFriendOfFriend($aData['user_id']))
                        {
                            $bCanPostComment = false;
                        }
                    }
                    break;
                case 3:
                    $bCanPostComment = false;
                    break;
            }
        }
        $aData['can_post_comment'] = $bCanPostComment;

        if (Phpfox::isModule('comment')
                && isset($aData['comment_type_id'])
                && Phpfox::getParam('feed.allow_comments_on_feeds')
                && Phpfox::isUser()
                && $aData['can_post_comment']
                && Phpfox::getUserParam('feed.can_post_comment_on_feed'))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Change the type when add a new comment.
     * @param string $sType
     * @return string
     */
    public function changeType($sType)
    {
        switch ($sType) {
            case 'user_status':
                break;

            case 'photo':
                break;

            case 'music_playlist':
                $sType = 'music_album';
                break;

            case 'event_comment':
                $sType = 'event';
                break;

            case 'fevent_comment':
                $sType = 'fevent';
                break;

            case 'feed_comment':
                $sType = 'feed';
                break;

            case 'pages_comment':
                $sType = 'pages';
                break;
            case 'groups_comment':
                $sType = 'groups';
                break;
            case 'directory_comment':
                $sType = 'directory';
                break;

            default:
                break;
        }

        return $sType;
    }

    /**
     * Input data:
     * + sType: string, required.
     * + iItemId: int, required.
     * + sText: string, required.
     * 
     * Output data:
     * + error_message: string.
     * + error_code: int.
     * + result: int.
     * + lastid: int.
     * 
     * @see Mobile - API phpFox/Api V2.0 - Restful. Method Post.
     * @see comment
     * 
     * @param array $aData
     * @return array
     */
    public function postAction($aData)
    {
        return $this->add($aData);
    }

    /**
     * @param $json
     *
*@return array|string
     */
    public function sanitizeCommentAttachment($json)
    {
        if(empty($json)){
            return array();
        }

        if(empty($json['sType'])){
            return array();
        }

        if ($json['sType'] == 'core_link'){
            return $json;
        }

        // get photo src then return.
        if ($json['sType'] == 'photo' && !empty($json['iPhotoId']))
        {
            $aRow = Phpfox::getService('photo')->getCoverPhoto($json['iPhotoId']);

            $sPhotoUrl  =  Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aRow['server_id'],
                    'path' => 'photo.url_photo',
                    'file' => $aRow['destination'],
                    'suffix' => '_500',
                    'return_url' => true
                )
            );

            $json['sPhotoUrl'] = $sPhotoUrl;

            return $json;
        }

        return array();
    }

    /**
     * Get tags list for suggestion
     *
     * @link https://docs.google.com/spreadsheets/d/1QKYXo1NoGnWows5NQ0x6Yb8zuSHEm6-V8WsDtOkTmpg/edit#gid=507873474
     * @link https://jira.younetco.com/browse/PCUS-1035
     * @since 3.09p1
     * @author Nam Nguyen
     * date Jul 01, 2015
     * <code>
     * member/get_tag_list?q=
     * {
     * q: "",
     *   rows: [{id: string, title: string, img: string }]
     * }
     * </code>
     *
     *
     * Input data: (if it is feed using feed/comment)
     * + sItemType: string, required.
     * + iItemId: int, required.
     * + sText: string, optional
     *
     * Output data:
     * + iLastId: int
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     *
     */
    public function add($aData)
    {
        //  init 
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        $iOriginalItemId = $iItemId;
        $sParentId = isset($aData['sParentId']) ? $aData['sParentId'] : '';
        if (!$sItemType || !$iItemId)
        {
            return array(
                'error_code' => 1,
                'error_elements' => 'sItemType or iItemId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        $sText = isset($aData['sText']) ? $aData['sText'] : "";

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_comment_on_this_item")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        //  process 
        $sModule = isset($aData['sModule']) ? trim($aData['sModule']) : '';
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;
        $iParentId = isset($aData['iParentId']) ? (int) $aData['iParentId'] : 0;
        // process for feed/comment
        if('feed' == $sItemType){
            if($sParentId && $sParentId != 'feed') {
                $feed = Phpfox::getService('feed')->callback(array(
                    'table_prefix' => ($sParentId == 'groups') ? 'pages_' : $sParentId . '_' 
                ))->getFeed($iItemId);
                if(isset($feed['feed_id']) && ('pages' == $sParentId || 'groups' == $sParentId)){
                    $feed['type_id'] = Phpfox::getService('mfox.'.$sParentId)->changeType($feed['type_id']);
                }
            } else {
                $feed = Phpfox::getService('feed')->getFeed($iItemId);
                if(isset($feed['feed_id'])){
                    $feed['type_id'] = Phpfox::getService('mfox.feed')->changeType($feed['type_id']);
                }
            }

            if(!isset($feed['feed_id'])){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.activity_does_not_exist")),
                    'result' => 0
                );
            }

            $aData['iParentItemId'] = $iItemId;
            $iItemId = $feed['item_id'];
            $sItemType = $feed['type_id'];
        }

        $sType = $sItemType;
        $sType = $this->changeType($sType);
        if (Phpfox::hasCallback($sType, 'getAjaxCommentVar'))
        {
            $sVar = Phpfox::callback($sType . '.getAjaxCommentVar');

            if ($sVar !== null)
            {
                if(!Phpfox::getUserParam($sVar)){
					return array(
						'error_code' => 1,
						'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_comment_on_this_item")),
						'result' => 0
					);
				}
            }
        }

        if ($sType == 'profile' && !Phpfox::getService('user.privacy')->hasAccess($iItemId, 'comment.add_comment'))
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('bulletin.you_do_not_have_permission_to_add_a_comment_on_this_persons_profile'),
                'error_code' => 1,
                'result' => 0
            );
        }

        if ($sType == 'group' && (!Phpfox::getService('group')->hasAccess($iItemId, 'can_use_comments', true)))
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('bulletin.only_members_of_this_group_can_leave_a_comment'),
                'error_code' => 1,
                'result' => 0
            );
        }

        if (!Phpfox::getUserParam('comment.can_comment_on_own_profile') && $sType == 'profile' && $iItemId == Phpfox::getUserId() && !isset($iParentId))
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('comment.you_cannot_write_a_comment_on_your_own_profile'),
                'error_code' => 1,
                'result' => 0
            );
        }

        if (($iFlood = Phpfox::getUserParam('comment.comment_post_flood_control')) !== 0)
        {
            $aFlood = array(
                'action' => 'last_post', // The SPAM action
                'params' => array(
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('comment'), // Database table we plan to check
                    'condition' => 'type_id = \'' . Phpfox::getLib('database')->escape($sType) . '\' AND user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                )
            );

            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood))
            {
                return array(
                    'error_message' =>  Phpfox::getPhrase('comment.posting_a_comment_a_little_too_soon_total_time', array('total_time' => Phpfox::getLib('spam')->getWaitTime())),
                    'error_code' => 1,
                    'result' => 0
                );
            }
        }

        if (Phpfox::getLib('parse.format')->isEmpty($sText) && (!isset($aData['adv_file']) || empty($aData['adv_file'])))
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('comment.add_some_text_to_your_comment'),
                'error_code' => 1,
                'result' => 0
            );
        }

        // Check privacy comment.
        $aError = $this->canPostComment(array(
            'sType' => $sType, 
            'iItemId' => $iItemId, 
            'sModule' => $sModule, 
            'iItem' => $iItem, 
        ));
		
        if ($aError)
        {
            return $aError;
        }

        if($sType == 'event_comment' || $sType == 'fevent_comment') {
            $sType = ($sType == 'fevent_comment') ? 'fevent' : 'event';
        } else if($sType == 'pages_comment' || $sType == 'pages_comment') {
            $sType = 'pages';
        } else if($sType == 'groups_comment') {
            $sType = 'groups';
        }  else if ($sType == 'directory_comment') {
            $sType = 'directory';
        }

        $tag_pattern = "/(\[x\=(\w+)\@(\d+)\])([^\[]+)(\[\/x\])/mi";

        $sText = preg_replace($tag_pattern, "[x=$3]$4[/x]", $sText);

        $aVals = array(
            'parent_id' => $iParentId,
            'text' => $sText,
            'type' => $sType,
            'item_id' => $iItemId,
            // 'attachment_item_text'=> !(empty($aData['aAttachmentItem']))?json_encode($aData['aAttachmentItem']): '',
            'iParentItemId' => $aData['iParentItemId']
        );

        if (($mId = $this->addComment($aVals)) === false)
        {
            return array(
                'error_code' => 1,
                'error_message' => html_entity_decode(implode(' ', Phpfox_Error::get())),
                'result' => 0
            );
        }
        else
        {
            $aVals = array_merge($aData, $aVals);

            Phpfox::getService('feed.process')->clearCache($aVals['type'] . ($aVals['type'] == 'feed' ? '_comment' : ''), $aVals['item_id']);
            // custom work
            if(isset($aVals['adv_file']) && !empty($aVals['adv_file']))
            {
                $aFiles = array();
                if(is_array($aVals['adv_file']) && count($aVals['adv_file']))
                {
                    $aFiles = $aVals['adv_file'];
                }
                else
                {
                    $aFiles = array($aVals['adv_file']);
                }
                $iAttachId = 0;
                $iLimit = Phpfox::getUserParam('advcomment.the_maximum_pictures_can_upload_per_comment');
                if($iLimit <0)
                {
                    $iLimit = 0;
                }
                foreach($aFiles as $iKey => $sFile)
                {
                    if($iKey >= $iLimit)
                    {
                        continue;
                    }
                    $aInsert = array(
                        'comment_id' => $mId,
                        'destination' => $sFile,
                        'server_id' => isset($aVals['server_id'][$iKey])?$aVals['server_id'][$iKey]:0,
                        'data_type' => isset($aVals['data_type'])?$aVals['data_type']:"photo",
                        'sticker_id' => isset($aVals['sticker_id'])?$aVals['sticker_id']:0,
                        'photo_chain' => $iAttachId
                    );
                    if($iKey == 0)
                    {
                        $iAttachId = phpfox::getService('advcomment.process')->addAttach($aInsert);
                    }
                    else
                    {
                        unset($aInsert['sticker_id']);
                        unset($aInsert['data_type']);
                        phpfox::getService('advcomment')->addChainPhoto($aInsert);
                    }
                }

            }
            if(!empty($aVals['text']))
            {
                $aLink = isset($aVals['link'])?$aVals['link']:array();
                if(!isset($aLink['link']))
                {
                    $aLink = phpfox::getService('advcomment.link')->get($mId, $aVals['text']);
                    $aLink['image'] = isset($aLink['default_image'])?$aLink['default_image']:"";
                }
                if(isset($aLink['link']))
                {
                    $aLink['image_hide'] = isset($aLink['image_hide'])?$aLink['image_hide']:0;
                    //$aLink['image'] = isset($aLink['default_image'])?$aLink['default_image']:"";
                    phpfox::getService('advcomment.link')->add(array('link' => $aLink,'comment_id' => $mId));
                }
            }
            // end

//            $aComment = Phpfox::getService('comment')->getComment($mId);
            // custom work
            $aComment = Phpfox::getService('advcomment')->getComment($mId);
            $sProfileImage = Phpfox::getService('mfox.user')->getImageUrl($aComment, MAX_SIZE_OF_USER_IMAGE);

//            $aAttachmentItem = $aData['aAttachmentItem'];

            $sContent  = $aComment['text'];

            if(!empty($sContent)){
                $sContent = preg_replace('/#(\w+)\b(?!;)/mi', '<a href-dir url="#app/newsfeed/hashtag/$1">#$1</a>', $sContent);
            }

            if(!empty($sContent)){
                $sContent = preg_replace('/\[x=(\d+)\]([^\[]+)\[\/x\]/mi','<a href-dir url="#/app/user/$1">$2</a>',$sContent);
            }

            $returnComment =  array(
                'lastid' => $mId, 
                'iLastId' => $mId,
                'iCommentId' => $mId,
                'sModelType' => $sItemType, 
                'iUserId' => $aComment['user_id'],
                'sFullName' => $aComment['full_name'],
                'sImage' => $sProfileImage,
                'sContent' => $sContent,
                'iTotalLike' => $aComment['total_like'],
                'iTotalComment' => 0, 
                'aUserLike' => array(),
                'bIsLiked' => false,
                'bCanDislike' => true,
                'iTimeStamp'=> $aComment['unix_time_stamp'],
                'sTimeConverted' => $aComment['post_convert_time'],
                'sItemType' => $sItemType,
                'iItemId' => $aComment['item_id'],
//                'aAttachmentItem' => $this->sanitizeCommentAttachment($aAttachmentItem),
                'result' => 1,
                'error_code' => 0,
                'error_message' => "",
                'aAttachmentItems' => $this->processAttachments($aComment),
            );

            return $returnComment;
        }
    }

    /**
     * Add comment.
     * @param array $aVals
     * @param int $iUserId
     * @param string $sUserName
     * @return boolean|string
     */
    public function addComment($aVals, $iUserId = null, $sUserName = null)
    {
        /**
         * @var int
         */
        $iUserId = ($iUserId === null ? Phpfox::getUserId() : (int) $iUserId);
        /**
         * @var string
         */
        $sUserName = ($sUserName === null ? Phpfox::getUserBy('full_name') : $sUserName);

        if (isset($aVals['parent_group_id']) && isset($aVals['group_view_id']) && $aVals['group_view_id'] > 0)
        {
            define('PHPFOX_SKIP_FEED', true);
        }
		
		
		
        if (Phpfox::getParam('comment.comment_hash_check'))
        {
            if (Phpfox::getLib('spam.hash', array(
                        'table' => 'comment_hash',
                        'total' => Phpfox::getParam('comment.comments_to_check'),
                        'time' => Phpfox::getParam('comment.total_minutes_to_wait_for_comments'),
                        'content' => $aVals['text']
                            )
                    )->isSpam())
            {
            	Phpfox_Error::set("Spammer!");
                return false;
            }
        }
		
		
        /**
         * @var array
         */
        // $aItem = Phpfox::callback('event.getCommentItem', $aVals['item_id']);
        $aItem = Phpfox::callback($aVals['type'] . '.getCommentItem', $aVals['item_id']);
		
        if (!isset($aItem['comment_item_id']))
        {
        	// $aItem['comment_item_id'] =  $iId;
        	// var_dump($aItem);exit;
        	// Phpfox_Error::set("comment_item_id not found !" . $aVals['type'] . '.getCommentItem:'. $aVals['item_id']);
            // return false;
        }
		
        /**
         * @var bool
         */
        $bIsBlocked = Phpfox::getService('user.block')->isBlocked($aItem['comment_user_id'], Phpfox::getUserId());
        if ($bIsBlocked)
        {
            Phpfox_Error::set('Unable to leave a comment at this time.');
            return false;
        }
        /**
         * @var array
         */
        $aVals = array_merge($aItem, $aVals);
        /**
         * @var bool
         */
        $bCheck = Phpfox::getService('mfox.ban')->checkAutomaticBan($aVals['text']);
        if ($bCheck == false)
        {
            return false;
        }
        /**
         * @var array
         */
        $aInsert = array(
            'parent_id' => $aVals['parent_id'],
            'type_id' => $aVals['type'],
            'item_id' => $aVals['item_id'],
            'user_id' => $iUserId,
            'owner_user_id' => $aItem['comment_user_id'],
            'time_stamp' => PHPFOX_TIME,
            'ip_address' => Phpfox::getLib('request')->getServer('REMOTE_ADDR'),
            'view_id' => (($aItem['comment_view_id'] == 2 && $aItem['comment_user_id'] != $iUserId) ? '1' : '0'),
            // 'author' => (!empty($aVals['is_via_feed']) ? (int) $aVals['is_via_feed'] : '')
            // 'attachment_item_text'=>$aVals['attachment_item_text'],
            'author' => (!empty($aVals['iParentItemId']) ? (int) $aVals['iParentItemId'] : '')
        );



        if (!$iUserId)
        {
            $aInsert['author'] = substr($aVals['author'], 0, 255);
            $aInsert['author_email'] = $aVals['author_email'];
            if (!empty($aVals['author_url']) && Phpfox::getLib('validator')->verify('url', $aVals['author_url']))
            {
                $aInsert['author_url'] = $aVals['author_url'];
            }
        }
        /**
         * @var bool
         */
        $bIsSpam = false;
        if (Phpfox::getParam('comment.spam_check_comments'))
        {
            if (Phpfox::getLib('spam')->check(array(
                        'action' => 'isSpam',
                        'params' => array(
                            'module' => 'comment',
                            'content' => Phpfox::getLib('parse.input')->prepare($aVals['text'])
                        )
                            )
                    )
            )
            {
                $aInsert['view_id'] = '9';
                $bIsSpam = true;
                Phpfox_Error::set( Phpfox::getPhrase('comment.your_comment_has_been_marked_as_spam_it_will_have_to_be_approved_by_an_admin'));
            }
        }

        if (Phpfox::getUserParam('comment.approve_all_comments'))
        {
            $aInsert['view_id'] = '1';
            $bIsSpam = true;
            Phpfox_Error::set( Phpfox::getPhrase('comment.your_comment_has_successfully_been_added_however_it_is_pending_an_admins_approval'));
        }



        /**
         * @var int
         */
        $iId = $this->database()->insert(Phpfox::getT('comment'), $aInsert);

        Phpfox::getLib('parse.bbcode')->useVideoImage(($aVals['type'] == 'feed' ? true : false));

        $aVals['text_parsed'] = Phpfox::getLib('parse.input')->prepare($aVals['text']);

        $this->database()->insert(Phpfox::getT('comment_text'), array(
            'comment_id' => $iId,
            'text' => Phpfox::getLib('parse.input')->clean($aVals['text']),
            'text_parsed' => $aVals['text_parsed']
                )
        );

        // http://www.phpfox.com/tracker/view/14660/
        $sComment = Phpfox::getLib('parse.input')->clean($aVals['text']);
        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support'))
        {
            Phpfox::getService('tag.process')->add($aInsert['type_id'], $aInsert['item_id'], Phpfox::getUserId(), $sComment, true);
        }

        $aVals['comment_id'] = $iId;

        if (!empty($aVals['parent_id']))
        {
            $this->database()->updateCounter('comment', 'child_total', 'comment_id', (int) $aVals['parent_id']);
        }

        if ($bIsSpam === true)
        {
            return false;
        }

        Phpfox::getService('user.process')->notifyTagged($aVals['text'], $iId, $aVals['type']);

        // Callback this action to other modules
        Phpfox::callback($aVals['type'] . '.addComment', $aVals, $iUserId, $sUserName);

        if (($aItem['comment_view_id'] == 2 && $aItem['comment_user_id'] != $iUserId))
        {
            (Phpfox::isModule('request') ? Phpfox::getService('request.process')->add('comment_pending', $iId, $aItem['comment_user_id']) : false);

            return 'pending_moderation';
        }

        // Update user activity
        Phpfox::getService('user.activity')->update(Phpfox::getUserId(), 'comment');
        /**
         * @var string
         */
        $sFeedPrefix = '';
        $sNewTypeId = $aVals['type'];
        if (!empty($aItem['parent_module_id']) 
            && ($aItem['parent_module_id'] == 'pages' 
                || $aItem['parent_module_id'] == 'groups' 
                || $aItem['parent_module_id'] == 'event'
                || $aItem['parent_module_id'] == 'fevent'
                || $aItem['parent_module_id'] == 'directory')
            )
        {
            $sFeedPrefix = ($aItem['parent_module_id'] == 'groups') ? 'pages_' : $aItem['parent_module_id'] . '_';
            if ($sNewTypeId == 'pages')
            {
                $sNewTypeId = 'pages_comment';
            }

            if ($sNewTypeId == 'groups')
            {
                $sNewTypeId = 'groups_comment';
            }

            if ($sNewTypeId == 'event' || $sNewTypeId == 'fevent')
            {
                $sNewTypeId = $sNewTypeId . '_comment';
            }

            if ($sNewTypeId == 'directory')
            {
                $sNewTypeId = $sNewTypeId . '_comment';
            }
        }

        $this->database()->update(Phpfox::getT($sFeedPrefix . 'feed'), array('time_update' => PHPFOX_TIME), 'type_id = \'' . $this->database()->escape($sNewTypeId) . '\' AND item_id = ' . (int) $aVals['item_id']);

        return $iId;
    }

    /**
     * Input data:
     * + iItemId: int, required.
     * + sText: string, required.
     * 
     * Output data:
     * + error_message: string.
     * + error_code: int.
     * + result: int.
     * 
     * @see Mobile - API phpFox/Api V2.0 - Restful. Method Put.
     * @see comment
     * 
     * @param array $aData
     * @return array
     */
    public function putAction($aData)
    {
        return $this->edit($aData);
    }

    /**
     * Input data (edit any comment if having permission, not use {iItemId, sItemType})
     * Get error when editing comment of Event in phpFox 3.6 which belongs to core
     * 
     * + iCommentId: int, required.
     * + iItemId: int, required.
     * + sItemType: string, required.
     * + sText: string, optional
     *
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     */
    public function edit($aData)
    {
        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_comment_on_this_item")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        if (!$sItemType || !$iItemId)
        {
            return array(
                'error_code' => 1,
                'error_elements' => 'sItemType or iItemId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid")),
                'result' => 0
            );
        }

        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;
        if ($iCommentId < 1)
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_comment")),
                'error_code' => 1,
                'error_element' => 'iCommentId',
                'result' => 0
            );
        }

        $sText = isset($aData['sText']) ? $aData['sText'] : "";
        if (Phpfox::getLib('parse.format')->isEmpty($sText))
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('comment.add_some_text_to_your_comment'),
                'error_code' => 1,
                'result' => 0
            );
        }

        //  end 
        if ($this->updateText($iCommentId, $sText))
        {
            return array('result' => 1, 'error_code' => 0, 'error_message' => '');
        }
        else
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
        }
    }

    private function updateText($iId, $sText)
    {
        if (Phpfox::getService('comment')->hasAccess($iId, 'edit_own_comment', 'edit_user_comment'))
        {
            $oFilter = Phpfox::getLib('parse.input');

            if (!Phpfox::getService('ban')->checkAutomaticBan($sText))
            {
                return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));
            }

            if (Phpfox::getParam('comment.spam_check_comments'))
            {
                if (Phpfox::getLib('spam')->check(array(
                            'action' => 'isSpam',
                            'params' => array(
                                'module' => 'comment',
                                'content' => Phpfox::getLib('parse.input')->prepare($sText)
                            )
                                )
                        )
                )
                {
                    $this->database()->update(Phpfox::getT('comment'), array('view_id' => '9'), "comment_id = " . (int) $iId);

                    Phpfox_Error::set( Phpfox::getPhrase('comment.your_comment_has_been_marked_as_spam_it_will_have_to_be_approved_by_an_admin'));
                }
            }
            $aVals = $this->database()->select('cmt.*')
                    ->from(Phpfox::getT('comment'), 'cmt')
                    ->where('cmt.comment_id = ' . (int) $iId)
                    ->execute('getSlaveRow');

            Phpfox::getLib('parse.bbcode')->useVideoImage(($aVals['type_id'] == 'feed' ? true : false));

            $this->database()->update(Phpfox::getT('comment'), array('update_time' => PHPFOX_TIME, "update_user" => Phpfox::getUserBy("full_name")), "comment_id = " . (int) $iId);
            $this->database()->update(Phpfox::getT('comment_text'), array('text' => $oFilter->clean($sText), "text_parsed" => $oFilter->prepare($sText)), "comment_id = " . (int) $iId);
            if (Phpfox::hasCallback($aVals['type_id'], 'updateCommentText'))
            {
                Phpfox::callback($aVals['type_id'] . '.updateCommentText', $aVals, $oFilter->prepare($sText));
            }
            return true;
        }

        Phpfox_Error::set('You do not have permission to edit this comment!');

        return false;
    }

    /**
     * Input data:
     * + iItemId: int, required.
     * 
     * Output data:
     * + error_message: string.
     * + error_code: int.
     * + result: int.
     * 
     * @see Mobile - API phpFox/Api V2.0 - Restful. Method Delete.
     * @see comment
     * 
     * @param array $aData
     * @return array
     */
    public function deleteAction($aData)
    {
        return $this->delete($aData);
    }

    public function remove($aData)
    {
        return $this->delete($aData);
    }

    /**
     * Input data: not use {iItemId, sItemType}
     * + iItemId: int, required.
     * + sItemType: string, required.
     * + iCommentId: int, required.
     *
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     *
     */
    public function delete($aData)
    {
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        if (!$sItemType || !$iItemId)
        {
            return array(
                'error_code' => 1,
                'error_elements' => 'sItemType or iItemId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;
        if ($iCommentId < 1)
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_comment")),
                'error_code' => 1,
                'error_element' => 'iCommentId',
                'result' => 0
            );
        }

        $aComment = $this->getCommentByID($iCommentId);
        if (!isset($aComment['comment_id']))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.comment_does_not_exist_or_has_been_deleted")),
                'error_code' => 1,
                'result' => 0
            );
        }
        if($aComment['type_id'] == 'photo_album' || $aComment['type_id'] == 'advancedphoto_album'){
            $ret = $this->__deleteInline($iCommentId, $aComment['type_id']);
        } else {
            $ret = Phpfox::getService('comment.process')->deleteInline($iCommentId, $aComment['type_id']);
        }
        if(true === $ret){
            if (Phpfox::getParam('feed.cache_each_feed_entry'))
            {
                Phpfox::getService('feed.process')->clearCache($aComment['type_id'] . ($aComment['type_id'] == 'feed' ? '_comment' : ''), $aComment['item_id']);   
            }
            return array(
                'result' => 1,
                'error_code' => 0,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.comment_has_been_deleted"))
            );
        } else if(false === $ret){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.oops_the_action_not_found")),
                'result' => 0
            );
        } else {
            return $ret;
        }        
    }

    private function __deleteInline($iId, $iTypeId, $bForce = false){
        $bCanDeleteOnProfile = false;
        $aCore = Phpfox::getLib('request')->get('core');
        if (isset($aCore['is_user_profile']) && $aCore['is_user_profile'])
        {           
            if ($iTypeId == 'feed')
            {
                $this->database()->join(Phpfox::getT('feed_comment'), 'fc', 'fc.feed_comment_id = c1.item_id');
            }
            else
            {
                $this->database()->join(Phpfox::getT('feed'), 'fc', 'c1.type_id = fc.type_id AND c1.item_id = fc.item_id');
            }
            $aParent = $this->database()->select('fc.parent_user_id, c1.owner_user_id')
                ->from(Phpfox::getT('comment'), 'c1')               
                ->where('c1.comment_id = ' . (int) $iId)
                ->execute('getSlaveRow');

            $bCanDeleteComment = false;
            if (isset($aParent['parent_user_id']) && $aParent['parent_user_id'] == Phpfox::getUserId())
            {
                $bCanDeleteComment = true;
            }
            elseif (isset($aParent['owner_user_id']) && $aParent['owner_user_id'] == Phpfox::getUserId())
            {
                $bCanDeleteComment = true;
            }
    
            $bCanDeleteOnProfile = ($bCanDeleteComment && Phpfox::getUserParam('comment.can_delete_comments_posted_on_own_profile'));           
        }       
        
        
        if (Phpfox::isModule('pages') && Phpfox::getLib('request')->get('type_id') == 'pages')
        {
            $aPagesParent = $this->database()->select('c1.*, pf.parent_user_id')
                ->from(Phpfox::getT('comment'), 'c1')
                ->join(Phpfox::getT('pages_feed'), 'pf', 'pf.item_id = c1.item_id')
                ->where('c1.comment_id = ' . (int) $iId)
                ->execute('getSlaveRow');

            if (isset($aPagesParent['comment_id']) && Phpfox::getService('pages')->isAdmin($aPagesParent['parent_user_id']))
            {
                $bCanDeleteOnProfile = true;
            }
        }
        
        if ($bForce === true)
        {
            $bCanDeleteOnProfile = true;
        }
        if ((($iUserId = Phpfox::getService('comment')->hasAccess($iId, 'delete_own_comment', 'delete_user_comment')) !== false) || $bCanDeleteOnProfile == true)
        {
            $aCommentRow = $this->database()->select('*')
                ->from(Phpfox::getT('comment'))
                ->where('comment_id = ' . (int) $iId)
                ->execute('getRow');                
            Phpfox::getService('comment.process')->delete($iId);
            if (empty($aCommentRow['parent_id']))
            {
                // Phpfox::callback($iTypeId . '.deleteComment', $aCommentRow['item_id']);
            }       

            // Update user activity
            Phpfox::getService('user.activity')->update($iUserId, 'comment', '-');

            if (Phpfox::getParam('feed.cache_each_feed_entry'))
            {
                Phpfox::getService('feed.process')->clearCache($aRowComment['type_id'], $aRowComment['item_id']);   
            }
            
            return true;
        }

        return false;        
    }

    /**
     * Input data:
     * + sType: string, required.
     * + iItemId: int, required.
     * + lastCommentIdViewed: int, optional.
     * + amountOfComment: int, optional.
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
     * @see Mobile - API phpFox/Api V1.0 - Restful. Method Get.
     * @see comment
     * 
     * @param array $aData
     * @return array
     */
    public function getAction($aData)
    {
        return $this->listallcomments($aData);
    }

    /**
     * Input data:(get old comments)
     * + sItemType: string, required.
     * + iItemId: int, required.
     * + iLastCommentIdViewed: int, optional.
     * + iAmountOfComment: int, optional.
     *
     * $aFeed : using to return iItemId = feed_id
     * , if in client mobile does not use feed_id so that we do not assign this parameter
     *
     * Output data:
     * + iLikeId: int
     * + iUserId: int
     * + sFullName: string
     * + sImage: string
     *
     */
    public function listallcomments($aData, $aFeed = null)
    {

        //  init 
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;

        $sParentId = isset($aData['sParentId']) ? $aData['sParentId'] : '';
        if($sParentId == 'event' 
            || $sParentId == 'fevent'
            || $sParentId == 'pages'
            || $sParentId == 'groups'
            || $sParentId == 'directory'
        ) {
            $sItemType = $sParentId;
        }
        if (!$sItemType || !$iItemId)
        {
            return array(
                'error_code' => 1,
                'error_elements' => 'sItemType or iItemId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        if (empty($sItemType) || $iItemId < 1)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }        

        $iLastCommentIdViewed = isset($aData['iLastCommentIdViewed']) ? (int)$aData['iLastCommentIdViewed'] : 0;
        $aComment = null;
        if($iLastCommentIdViewed){
            $aComment = $this->getCommentByID($iLastCommentIdViewed);

            if (!isset($aComment['comment_id']))
            {
                return array(
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.last_comment_viewd_does_not_exist_or_has_been_deleted")),
                    'error_code' => 1
                );
            }                    
        }

        $iAmountOfComment = (isset($aData['iAmountOfComment']) && (int)$aData['iAmountOfComment'])? (int)$aData['iAmountOfComment'] : 20;
        $sModule = isset($aData['sModule']) ? trim($aData['sModule']) : '';
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;
        $iLastTime = (null == $aComment) ? 0 : $aComment['time_stamp'];        
        // process for feed/comment
        if('feed' == $sItemType){

            $feed = Phpfox::getService('feed')->getFeed($iItemId);


            if(!isset($feed['feed_id'])){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.activity_does_not_exist"))
                );
            }

            $feed['type_id'] = Phpfox::getService('mfox.feed')->changeType($feed['type_id']);
            $sItemType = $feed['type_id'];
            $iItemId = $feed['item_id'];
            $aFeed = $feed;
        } else if('event' == $sItemType 
            || 'fevent' == $sItemType
            || 'pages' == $sItemType
            || 'groups' == $sItemType
            || 'directory' == $sItemType
        ){
            $feed = Phpfox::getService('feed')->callback(array(
                'table_prefix' => ($sParentId == 'groups') ? 'pages_' : $sParentId . '_' 
            ))->getFeed($iItemId);

            if(!isset($feed['feed_id'])){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.activity_does_not_exist"))
                );
            }

            if('pages' == $sParentId || 'groups' == $sParentId){
                $feed['type_id'] = Phpfox::getService('mfox.'.$sParentId)->changeType($feed['type_id']);
            }

            $sItemType = $feed['type_id'];
            $iItemId = $feed['item_id'];            
        }



        //  process 
        $sType = $sItemType;
        $sType = $this->changeType($sType);

        // does not required to check can view all comment permission
        // $aError = $this->canView(array(
        //     'sType' => $sType, 
        //     'iItemId' => $iItemId, 
        //     'sModule' => $sModule, 
        //     'iItem' => $iItem, 
        // ));        

        // if (isset($aError) && $aError)
        // {
        //     return $aError;
        // }

        $sCond = 'c.type_id = \'' . $this->database()->escape($sType) . '\'  ' . ' AND c.item_id = ' . (int) $iItemId . ($iLastTime > 0 ? ' AND c.time_stamp > ' . $iLastTime : '');

        // For custom post comment with attachment, need add this field to all _comment tables
        // $this->database()->select('c.attachment_item_text as sAttachmentText');

        $aFeedComments = $this->database()
                ->select('advcomment_data.*,advcomment_link_embed.embed_code as link_embed_code,advcomment_link.title as link_title,advcomment_link.link as link_embed, advcomment_link.description as link_description,advcomment_link.image as link_image,advcomment_link.link_id as advcomment_link_id, c.*, u.full_name AS sFullName, u.user_image, u.server_id AS user_server_id, ct.text AS sContentText, ct.text_parsed AS sContent, l.like_id AS bIsLiked')
                ->from(Phpfox::getT('comment'), 'c')
                ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
                ->leftJoin(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
                ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId())
                ->leftJoin(Phpfox::getT('advcomment_data'), 'advcomment_data', 'advcomment_data.comment_id = c.comment_id')
                ->leftJoin(Phpfox::getT('advcomment_link'), 'advcomment_link', 'advcomment_link.comment_id = c.comment_id')
                ->leftJoin(Phpfox::getT('advcomment_link_embed'), 'advcomment_link_embed', 'advcomment_link_embed.link_id = advcomment_link.link_id')
                ->where($sCond)
                ->order('c.comment_id ASC')
                ->limit($iAmountOfComment)
                ->execute('getRows');

        $aComments = array();
        if (count($aFeedComments))
        {
            foreach ($aFeedComments as $iFeedCommentKey => $aFeedComment)
            {
                $aFeedComments[$iFeedCommentKey]['post_convert_time'] = Phpfox::getLib('date')->convertTime($aFeedComment['time_stamp'], 'comment.comment_time_stamp');

                if (Phpfox::getParam('comment.comment_is_threaded'))
                {
                    $aFeedComments[$iFeedCommentKey]['children'] = $this->_getChildren($aFeedComment['comment_id'], $sType, $iItemId);
                }
            }

            $aComments = $aFeedComments;
        }

        $aComments = Phpfox::getService('advcomment')->getChainPhotos($aComments);

        $aResult = array();
        foreach ($aComments as $aComment)
        {
            /* For custom comment with tag, note: missing parse emoticons
            $sContent  = $aComment['sContentText'];

            if(!empty($sContent)){
                $sContent = preg_replace('/#(\w+)\b(?!;)/mi', '<a href-dir url="#app/newsfeed/hashtag/$1">#$1</a>', $sContent);
            }

            if(!empty($sContent)){
                $sContent = preg_replace('/\[x=(\d+)\]([^\[]+)\[\/x\]/mi','<a href-dir url="#/app/user/$1">$2</a>',$sContent);
            }
            */

            $obj = array(
                'iCommentId' => $aComment['comment_id'],
                'sImage' => Phpfox::getService('mfox.user')->getImageUrl($aComment, '_50_square'),
                'iTimestamp' => $aComment['time_stamp'],
                'sTime' => date('l, F j, o', (int) $aComment['time_stamp']) . ' at ' . date('h:i a', (int) $aComment['time_stamp']),
                'sTimeConverted' => Phpfox::getLib('date')->convertTime($aComment['time_stamp'], 'comment.comment_time_stamp'),
                'iUserId' => $aComment['user_id'],
                'sFullName' => $aComment['sFullName'],
                'sContent' => $aComment['sContent'], 
                // 'sContent' => $sContent,
                'iTotalLike' => $aComment['total_like'],
                'bIsLiked' => $aComment['bIsLiked'],
                'bIsDisliked' => Phpfox::getService('mfox.like')->checkIsDisliked('comment', $aComment['iCommentId'], Phpfox::getUserId()),
                'iTotalComment' => -1,
                'aUserLike' => array(),
                'sItemType' => $aComment['type_id'],
                'bCanDislike' => true,
                'sModelType' => $aComment['type_id'],
                'iItemId' => $aComment['item_id'],
                'aAttachmentItems'=> $this->processAttachments($aComment),
            );

            $aLike['likes'] = Phpfox::getService('like')->getLikesForFeed('feed_mini', $aComment['iCommentId']
                ,  $aComment['bIsLiked'], Phpfox::getParam('feed.total_likes_to_display'), true);              
            $aLike['feed_total_like'] = Phpfox::getService('like')->getTotalLikeCount();
            foreach($aLike['likes'] as $like){
                $obj['aUserLike'][] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
            }

            $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser('comment', $aComment['iCommentId'], $bGetCount = false);
            // currently, not use aUserDislike
            // foreach($aDislike as $dislike){
            //     $obj['aUserDislike'][] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
            // }
            $obj['iTotalDislike'] = count($aDislike);

            $aResult[] = $obj;
        }

        //  end 
        return $aResult;    
    }

    private function processAttachments($aFeedComment)
    {
        if (empty($aFeedComment['data_type'])) {
            return array();
        }
        if ($aFeedComment['data_type'] == 'sticker') {
            $aAttachment = array(
                array(
                    'data_type' => 'sticker',
                    'sPhotoUrl' => Phpfox::getBaseUrl() . 'PF.Base' . $aFeedComment['destination']
                )
            );
        } else {
            $aAttachment = array();
            $aAttachment[] = array(
                'data_type' => 'photo',
                'sPhotoUrl' => Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aFeedComment['server_id'],
                        'path' => 'photo.url_photo',
                        'file' => $aFeedComment['destination'],
                        'suffix' => '_500',
                        'return_url' => true
                    ))
            );
            if (!empty($aFeedComment['chains'])) {
                foreach ($aFeedComment['chains'] as $chain) {
                    $aAttachment[] = array(
                        'data_type' => 'photo',
                        'sPhotoUrl' => Phpfox::getLib('image.helper')->display(array(
                            'server_id' => $chain['server_id'],
                            'path' => 'photo.url_photo',
                            'file' => $chain['destination'],
                            'suffix' => '_500',
                            'return_url' => true
                        ))
                    );
                }
            }
        }
        return $aAttachment;
    }

    private function _getChildren($iParentId, $sType, $iItemId, $iCommentId = null, $iCnt = 0)
    {
        $iTotalComments = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->where('c.parent_id = ' . (int) $iParentId . ' AND c.type_id = \'' . $this->database()->escape($sType) . '\' AND c.item_id = ' . (int) $iItemId . ' AND c.view_id = 0')
            ->execute('getSlaveField');

        if(Phpfox::isModule('like'))
        {
            $this->database()->select('l.like_id AS is_liked, ')
                ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId());
        }

        if ($iCommentId === null)
        {
            $this->database()->limit(Phpfox::getParam('comment.thread_comment_total_display'));
        }

        $aFeedComments = $this->database()->select($this->getFields().',c.* ,' . (Phpfox::getParam('core.allow_html') ? "ct.text_parsed" : "ct.text") .' AS text, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->leftJoin(Phpfox::getT('advcomment_data'), 'advcomment_data', 'advcomment_data.comment_id = c.comment_id')
            ->where('c.parent_id = ' . (int) $iParentId . ' AND c.type_id = \'' . $this->database()->escape($sType) . '\' AND c.item_id = ' . (int) $iItemId . ' AND c.view_id = 0')
            ->order('c.time_stamp ASC')
            ->execute('getSlaveRows');

        $iCnt++;
        if (count($aFeedComments))
        {
            foreach ($aFeedComments as $iFeedCommentKey => $aFeedComment)
            {
                $aFeedComments[$iFeedCommentKey]['iteration'] = $iCnt;

                $aFeedComments[$iFeedCommentKey]['post_convert_time'] = Phpfox::getLib('date')->convertTime($aFeedComment['time_stamp'], 'comment.comment_time_stamp');
                if($aFeedComment['comment_id'])
                {
                    $aFeedComments[$iFeedCommentKey]['children'] = $this->_getChildren($aFeedComment['comment_id'], $sType, $iItemId, $iCommentId, $iCnt);
                }

            }
        }
        $aFeedComments = $this->getChainPhotos($aFeedComments);
        return array('total' => (int) ($iTotalComments - Phpfox::getParam('comment.thread_comment_total_display')), 'comments' => $aFeedComments);
    }

    /**
     * Get redirect request.
     * @param array $aComment
     * @return boolean|string
     */
    public function doCommentGetRedirectRequest($aComment)
    {
        if (!isset($aComment['item_id']) || $aComment['item_id'] == 0)
        {
            return array();
        }

        $aResult = array();
        
        switch ($aComment['type_id']) {
            case 'music_song':
                $aResult = array(
                    'iSongId' => $aComment['item_id'],
                    'sView' => 'music',
                    'sCommentType' => 'music_song'
                );
                break;

            case 'user_status':
                $aFeeds = Phpfox::getService('mfox.feed')->getfeed(array('status-id' => $aComment['item_id']));
                if (count($aFeeds) && !empty($aFeeds[0]['feed_id']))
                {
                    $aResult = array(
                        'iFeedId' => $aFeeds[0]['feed_id'],
                        'sView' => 'feed',
                        'sCommentType' => 'user_status'
                    );
                }
                break;

            case 'link':
                $aFeeds = Phpfox::getService('mfox.feed')->getfeed(array('link-id' => $aComment['item_id']));
                if (count($aFeeds) && !empty($aFeeds[0]['feed_id']))
                {
                    $aResult = array(
                        'iFeedId' => $aFeeds[0]['feed_id'],
                        'sView' => 'feed',
                        'sCommentType' => 'user_status'
                    );
                }
                break;

            case 'advancedphoto':
                $aResult = array(
                    'iPhotoId' => $aComment['item_id'],
                    'sView' => 'advancedphoto',
                    'sCommentType' => 'advancedphoto'
                );
                break;
            case 'photo':
                $aResult = array(
                    'iPhotoId' => $aComment['item_id'],
                    'sView' => 'photo',
                    'sCommentType' => 'photo'
                );
                break;

            case 'advancedphoto_album':
                $aResult = array(
                    'iPhotoAlbumId' => $aComment['item_id'],
                    'sView' => 'photo.album',
                    'sCommentType' => 'advancedphoto_album'
                );
                break;
            case 'photo_album':
                $aResult = array(
                    'iPhotoAlbumId' => $aComment['item_id'],
                    'sView' => 'photo.album',
                    'sCommentType' => 'photo_album'
                );
                break;

            case 'fevent':
            case 'event':
                $aTemp = Phpfox::getService('mfox.event')->doEventGetNotificationComment($aComment);
                $iEventId = 0;
                if (isset($aTemp['link']['iEventId']))
                {
                    $iEventId = $aTemp['link']['iEventId'];
                }
                if ($iEventId > 0)
                {
                    $aResult = array(
                        'iEventId' => $iEventId,
                        'sView' => $aComment['type_id'],
                        'sCommentType' => $aComment['type_id']
                    );
                }
                else
                {
                    $aResult = array();
                }
                break;

            case 'videochannel':
                $aResult = Phpfox::getService('mfox.videochannel')->doVideoGetRedirectComment($aComment['item_id']);
                break;

            case 'video':
                $aResult = Phpfox::getService('mfox.video')->doVideoGetRedirectComment($aComment['item_id']);
                break;

            case 'music_album':
                $aResult = Phpfox::getService('mfox.album')->doMusicAlbumGetRedirectCommentAlbum($aComment['item_id']);
                break;

            case 'blog':
                $aResult = array(
                    'iBlogId' => $aComment['item_id'],
                    'sView' => 'blog',
                    'sCommentType' => 'blog'
                );
                break;

            case 'quiz':
                $aResult = array(
                    'iQuizId' => $aComment['item_id'],
                    'sView' => 'quiz',
                    'sCommentType' => 'quiz'
                );
                break;

            case 'poll':
                $aResult = array(
                    'iPollId' => $aComment['item_id'],
                    'sView' => 'poll',
                    'sCommentType' => 'poll'
                );
                break;

            case 'advancedmarketplace':
                $aResult = array(
                    'iListingId' => $aComment['item_id'],
                    'sView' => 'advancedmarketplace',
                    'sCommentType' => 'advancedmarketplace'
                );
                break;
            case 'marketplace':
                $aResult = array(
                    'iListingId' => $aComment['item_id'],
                    'sView' => 'marketplace',
                    'sCommentType' => 'marketplace'
                );
                break;

            case 'pages': 
                $aTemp = Phpfox::getService('mfox.pages')->doPagesGetNotificationComment_Feed($aComment);
                $iPageId = 0;
                if (isset($aTemp['link']['iPageId']))
                {
                    $iPageId = $aTemp['link']['iPageId'];
                }
                if ($iPageId > 0)
                {
                    $aResult = array(
                        'iPageId' => $iPageId,
                        'sView' => $aComment['type_id'],
                        'sCommentType' => $aComment['type_id']
                    );
                }
                else
                {
                    $aResult = array();
                }
                break;

            default:
                $aResult = array();
                break;
        }

        return $aResult;
    }

    public function getCommentByID($iCommentId)
    {
        return $this->database()->select('cmt.*, comment_text.text AS text')
            ->from(Phpfox::getT('comment'), 'cmt')
            ->join(Phpfox::getT('comment_text'), 'comment_text', 'comment_text.comment_id = cmt.comment_id')
            ->where('cmt.comment_id = ' . (int) $iCommentId)
            ->execute('getSlaveRow');                       
    }

    public function isAllowed($aData)
    {
        //  init 
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        $sParentId = isset($aData['sParentId']) ? $aData['sParentId'] : '';
        if (!$sItemType || !$iItemId)
        {
            return false;
        }

        if(!Phpfox::isUser()){
            return false;
        }
        $iUserID = Phpfox::getUserId();

        //  process 
        $sModule = isset($aData['sModule']) ? trim($aData['sModule']) : '';
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;
        $iParentId = isset($aData['iParentId']) ? (int) $aData['iParentId'] : 0;
        // process for feed/comment
        if('feed' == $sItemType){
            if($sParentId && $sParentId != 'feed') {
                $feed = Phpfox::getService('feed')->callback(array(
                    'table_prefix' => $sParentId . '_' 
                ))->getFeed($iItemId);
                if(isset($feed['feed_id']) && 'pages' == $sParentId){
                    $feed['type_id'] = Phpfox::getService('mfox.pages')->changeType($feed['type_id']);
                }
            } else {
                $feed = Phpfox::getService('feed')->getFeed($iItemId);
                if(isset($feed['feed_id'])){
                    $feed['type_id'] = Phpfox::getService('mfox.feed')->changeType($feed['type_id']);
                }
            }

            if(!isset($feed['feed_id'])){
                return false;
            }

            $aData['iParentItemId'] = $iItemId;
            $iItemId = $feed['item_id'];
            $sItemType = $feed['type_id'];
        }

        $sType = $sItemType;
        $sType = $this->changeType($sType);
        if (Phpfox::hasCallback($sType, 'getAjaxCommentVar'))
        {
            $sVar = Phpfox::callback($sType . '.getAjaxCommentVar');

            if ($sVar !== null)
            {
                if(!Phpfox::getUserParam($sVar)){
                    return false;
                }
            }
        }

        if ($sType == 'profile' && !Phpfox::getService('user.privacy')->hasAccess($iItemId, 'comment.add_comment'))
        {
            return false;
        }

        if ($sType == 'group' && (!Phpfox::getService('group')->hasAccess($iItemId, 'can_use_comments', true)))
        {
            return false;
        }

        if (!Phpfox::getUserParam('comment.can_comment_on_own_profile') && $sType == 'profile' && $iItemId == Phpfox::getUserId() && !isset($iParentId))
        {
            return false;
        }

        if (($iFlood = Phpfox::getUserParam('comment.comment_post_flood_control')) !== 0)
        {
            $aFlood = array(
                'action' => 'last_post', // The SPAM action
                'params' => array(
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('comment'), // Database table we plan to check
                    'condition' => 'type_id = \'' . Phpfox::getLib('database')->escape($sType) . '\' AND user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                )
            );

            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood))
            {
                return false;
            }
        }

        // Check privacy comment.
        $aError = $this->canPostComment(array(
            'sType' => $sType, 
            'iItemId' => $iItemId, 
            'sModule' => $sModule, 
            'iItem' => $iItem, 
        ));

        if ($aError)
        {
            return false;
        }

        return true;
    }

    public function getCommentCount($aData, $aFeed = null)
    {
        //  init 
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;

        $sParentId = isset($aData['sParentId']) ? $aData['sParentId'] : '';
        if($sParentId == 'event' 
            || $sParentId == 'fevent'
            || $sParentId == 'pages'
            || $sParentId == 'directory'
        ) {
            $sItemType = $sParentId;
        }
        if (!$sItemType || !$iItemId)
        {
            return 0;
        }
        if (empty($sItemType) || $iItemId < 1)
        {
            return 0;
        }        

        $sModule = isset($aData['sModule']) ? trim($aData['sModule']) : '';
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;
        // process for feed/comment
        if('feed' == $sItemType){
            $feed = Phpfox::getService('feed')->getFeed($iItemId);
            if(!isset($feed['feed_id'])){
                return 0;
            }

            $feed['type_id'] = Phpfox::getService('mfox.feed')->changeType($feed['type_id']);
            $sItemType = $feed['type_id'];
            $iItemId = $feed['item_id'];
            $aFeed = $feed;
        } else if('event' == $sItemType 
            || 'fevent' == $sItemType
            || 'pages' == $sItemType
            || 'directory' == $sItemType
        ){
            // for: event/fevent/pages
            $feed = Phpfox::getService('feed')->callback(array(
                'table_prefix' => $sParentId . '_' 
            ))->getFeed($iItemId);

            if(!isset($feed['feed_id'])){
                return 0;
            }

            if('pages' == $sParentId){
                $feed['type_id'] = Phpfox::getService('mfox.pages')->changeType($feed['type_id']);
            }
            if($feed['type_id'] == 'link') {
                if('pages' == $sItemType || 'directory' == $sItemType){
                    $sParentId = $sItemType . '_link';                    
                } else if ('event' == $sItemType || 'fevent' == $sItemType) {
                    $sParentId = (Phpfox::getService('mfox.event')->isAdvancedModule() ? 'fevent' : 'event') . '_link';
                }
            }

            $iItemId = $feed['feed_id'];            
        }

        //  process 
        $sType = $sItemType;
        $sType = $this->changeType($sType);
        $aError = $this->canView(array(
            'sType' => $sType, 
            'iItemId' => $iItemId, 
            'sModule' => $sModule, 
            'iItem' => $iItem, 
        ));        

        if (isset($aError) && $aError)
        {
            return 0;
        }

        $sCond = 'c.type_id = \'' . $this->database()->escape($sType) . '\'  ';
        if($sParentId == 'event' 
            || $sParentId == 'event_link'
            || $sParentId == 'fevent'
            || $sParentId == 'fevent_link'
            || $sParentId == 'pages'
            || $sParentId == 'pages_link'
            || $sParentId == 'directory'
            || $sParentId == 'directory_link'
        ) {
            $sCond .= ' AND c.author = ' . (int) $iItemId ;
        } else {
            $sCond .= ' AND c.item_id = ' . (int) $iItemId ;
        }

        return $this->database()
                ->select('COUNT(c.comment_id)')
                ->from(Phpfox::getT('comment'), 'c')
                ->where($sCond)
                ->execute('getSlaveField');                 
    }

    public function canPostComment($aParam = array()){
        $sType = $aParam['sType'];
        $iItemId = $aParam['iItemId'];
        $sModule = $aParam['sModule'];
        $iItem = $aParam['iItem'];
        $aError = false;

        switch ($sType) {
            case 'advancedphoto':
            case 'photo':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyCommentOnPhoto($iItemId);
                break;

            case 'advancedphoto_album':
            case 'photo_album':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyCommentOnAlbum($iItemId);
                break;
				
			case 'musicsharing_album':
				$aError = Phpfox::getService('mfox.musicsharing.album')->checkPrivacyCommentOnMusicAlbum($iItemId);
				break;
				
			case 'musicsharing_song':  
				$aError = Phpfox::getService('mfox.musicsharing.song')->checkPrivacyCommentOnSong($iItemId);
				break;

            case 'music_song':
                $aError = Phpfox::getService('mfox.song')->checkPrivacyCommentOnSong($iItemId);
                break;

            case 'music_album':
                $aError = Phpfox::getService('mfox.album')->checkPrivacyCommentOnMusicAlbum($iItemId);
                break;

            case 'videochannel':
                $aError = Phpfox::getService('mfox.videochannel')->checkPrivacyCommentOnVideo($iItemId, $sModule, $iItem);
                break;

            case 'video':
                $aError = Phpfox::getService('mfox.video')->checkPrivacyCommentOnVideo($iItemId, $sModule, $iItem);
                break;
            
            case 'user_status':
                $aError = Phpfox::getService('mfox.feed')->checkPrivacyOnUserStatusFeed($iItemId, $sType, $sModule, $iItem);
                break;
            
            case 'blog':
                $aError = Phpfox::getService('mfox.blog')->canPostComment($iItemId);
                break;

            case 'poll':
                $aError = Phpfox::getService('mfox.poll')->canPostComment($iItemId);
                break;

            case 'quiz':
                $aError = Phpfox::getService('mfox.quiz')->canPostComment($iItemId);
                break;

            case 'advancedmarketplace':
            case 'marketplace':
                $aError = Phpfox::getService('mfox.marketplace')->canPostComment($iItemId);
                break;

            default:

                break;
        }

        return $aError;
    }

    public function canView($aParam = array()){
        $sType = $aParam['sType'];
        $iItemId = $aParam['iItemId'];
        $sModule = $aParam['sModule'];
        $iItem = $aParam['iItem'];
        $aError = false;
        
        switch ($sType) {
            case 'advancedphoto':
            case 'photo':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnPhoto($iItemId);
                break;

            case 'advancedphoto_album':
            case 'photo_album':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnAlbum($iItemId);
                break;

            case 'music_song':
                $aError = Phpfox::getService('mfox.song')->checkPrivacyOnSong($iItemId);
                break;

            case 'music_album':
                $aError = Phpfox::getService('mfox.album')->checkPrivacyOnMusicAlbum($iItemId);
                break;

            case 'videochannel':
                $aError = Phpfox::getService('mfox.videochannel')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                break;
                
            case 'video':
                $aError = Phpfox::getService('mfox.video')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                break;
            
            case 'user_status':
                $aError = Phpfox::getService('mfox.feed')->checkPrivacyOnUserStatusFeed($iItemId, $sType, $sModule, $iItem);
                break;
            
            case 'blog':
                $aError = Phpfox::getService('mfox.blog')->canView($iItemId);
                break;

            case 'poll':
                $aError = Phpfox::getService('mfox.poll')->canView($iItemId);
                break;

            case 'quiz':
                break;

            case 'advancedmarketplace':
            case 'marketplace':
                $aError = Phpfox::getService('mfox.marketplace')->canView($iItemId);
                break;

            default:

                break;
        }

        return $aError;
    }    

    // custom work
    public function get_stickers() {
        $aResult = array();
        if(phpfox::isModule('advcomment')) {
            $aCollections = phpfox::getService('advcomment.sticker')->getCollections();
            if(count($aCollections) && is_array($aCollections))
            {
                $aCollection = $aCollections[0];
                $iCollectionId = $aCollection['collection_id'];
                $aStickers = phpfox::getService('advcomment.sticker')->getAllStickers($iCollectionId);
                foreach ($aStickers as $key => $aSticker) {
                    $aSticker['sticker_path'] = Phpfox::getBaseUrl() . 'PF.Base' . $aSticker['sticker_destination'];
                    $aResult[] = $aSticker;
                }
            }
        }
        return $aResult;
    }

    public function upload($aData) {
        if (!Phpfox::isUser())
        {
            exit;
        }
        $oFile = Phpfox::getLib('file');
        $oImage = Phpfox::getLib('image');
        $aVals = $this->request()->get('val');
        $aReturn = array(
            'status' => 0,
            'message' => '',
            'feed_id' => isset($aVals['feed_id'])?$aVals['feed_id']:0,
        );
        if ($aImage = $oFile->load('image', array(
            'jpg',
            'gif',
            'png'
        ), (Phpfox::getUserParam('advcomment.photo_max_upload_size') === 0 ? null : (Phpfox::getUserParam('advcomment.photo_max_upload_size') / 1024))
        )
        )
        {
            $sFileName = $oFile->upload('image',
                Phpfox::getParam('photo.dir_photo'),
                md5(uniqid().time()),
                true
            );
            $iSize = 500;
            $oImage->createThumbnail(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''), Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize, true, false);
            $iSize = 250;
            $oImage->createThumbnail(Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, ''), Phpfox::getParam('photo.dir_photo') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize, true, false);

            $aReturn['status'] = 1;
            $aReturn['data'] = array(
                'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID'),
                'destination' => $sFileName,
                'name' => $aImage['name'],
                'ext' => $aImage['ext'],
                'size' => $aImage['size'],
            );
            $sPath = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID'),
                    'path' => 'photo.url_photo',
                    'file' => $aReturn['data']['destination'],
                    'suffix' => '_' . $iSize,
                    'return_url' => true
                )
            );
            $aReturn['data'] = array(
                'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID'),
                'destination' => $sFileName,
                'name' => $aImage['name'],
                'ext' => $aImage['ext'],
                'size' => $aImage['size'],
                'full_destination' => $sPath
            );
        }
        else{
            $aError = Phpfox_Error::get();
            $aReturn['message'] = implode('\r\n',$aError);
        }

        return $aReturn;
    }
}
