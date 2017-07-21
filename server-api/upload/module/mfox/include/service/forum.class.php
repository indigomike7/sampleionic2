<?php
/**
 * Service component
 *
 * @category Mobile phpfox server api
 * @author Ly Tran <lytk@younetco.com>
 * @version $Id$
 * @copyright $Copyright$
 * @license $License$
 * @package mfox.forum
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 * Supported Forum api
 *
 * @package mfox.forum
 * @author Ly Tran <lytk@younetco.com>
 */
class Mfox_Service_Forum extends Phpfox_Service {

   /**
    * @ignore 
    */
    private $_bPassed = false;
   /**
    * @ignore 
    */
    private $_bUpdateCounter = true;    
   /**
    * @ignore 
    */
    private $_bUpdateCounterPost = true;
   /**
    * @ignore 
    */
    private $_mUpdateView = null;

   /**
    * @ignore 
    */
    private $_iCounterForumLevel = 0;

    /**
     * @ignore
     * Class constructor
     */
    public function __construct() {

    }

    /**
     * @ignore
     *
     * @see
     * reset counter forum level when getting mutil level forum
     */
    public function resetCounterForumLevel()
    {
        $this->_iCounterForumLevel = 0;
    }
   /**
    * @ignore 
    */
    public function view($iView)
    {
        $this->_mUpdateView = $iView;
        
        return $this;
    }
   /**
    * @ignore 
    */
    public function counter($bUpdate)
    {
        $this->_bUpdateCounter = $bUpdate;
        
        return $this;
    }
   /**
    * @ignore 
    */
    public function counterPost($bUpdate)
    {
        $this->_bUpdateCounterPost = $bUpdate;
        
        return $this;
    }   

    /**
     * Get configuration for adding form
     * 
     * Request options: 
     * - iForumId:          integer, required
     * 
     * Response data contains: 
     * - bCanSetClosed:             boolean 
     * - comment_options:           array, with each item is 
     * <code>
     *   $value = array('sPhrase' => 'val', 'sValue' => 'val');
     * </code>
     * - types:                     array, with each item is 
     * <code>
     *   $value = array('sPhrase' => 'val', 'sValue' => 'val');
     * </code>
     * - view_options:              array, with each item is 
     * <code>
     *   $value = array('sPhrase' => 'val', 'sValue' => 'val');
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function threadformadd($aData)
    {
        $response  =  array(
            'view_options'=> Phpfox::getService('mfox.privacy')->privacy($aData),
            'comment_options'=> Phpfox::getService('mfox.privacy')->privacycomment($aData),
            'types'=> $this->getTypes($aData, false),
            'bCanSetClosed'=> $this->__canSetClosed($aData['iForumId'], false),
            'bCanAddPoll' => Phpfox::isModule('poll'),
        );
        
        return $response;
    }

   /**
    * @ignore 
    */
    private function __canSetClosed($iForumId, $bIsEdit = false, $aThread = null){
        if(Phpfox::getUserParam('forum.can_close_a_thread') 
            || Phpfox::getService('forum.moderate')->hasAccess($iForumId, 'close_thread')){
            if(($bIsEdit && $aThread['is_announcement'] != 1) || (!$bIsEdit)){
                return true;
            }
        }

        return false;
    }

    /**
     * Get configuration for editing form
     * 
     * Request options: 
     * - iThreadId:          integer, required
     * 
     * Response data contains: 
     * - bCanSetClosed:             boolean 
     * 
     * - comment_options:           array, with each item is 
     * <br/><code>
     *   $value = array('sPhrase' => 'val', 'sValue' => 'val');
     * </code>
     * 
     * - types:                     array, with each item is 
     * <br/><code>
     *   $value = array('sPhrase' => 'val', 'sValue' => 'val');
     * </code>
     * 
     * - view_options:              array, with each item is 
     * <br/><code>
     *   $value = array('sPhrase' => 'val', 'sValue' => 'val');
     * </code>
     * 
     * - aPoll:              array, poll in thread, empty array if thread has nothing poll
     *  - aAnswer:              array
     *   - iAnswerId:            integer
     *   - iOrdering:            integer
     *   - iPollId:              integer
     *   - iTotalVotes:          integer
     *   - iVotePercentage:      integer
     *   - sAnswer:              integer, title of answer 
     *  - bHideVote:            boolean
     *  - bIsVoted:             boolean
     *  - bUserVotedThisPoll:   boolean
     *  - iAnswerId:            integer
     *  - iPercentage:          integer
     *  - iPollId:              integer
     *  - iProfilePageId:       integer
     *  - iTimeStamp:           integer
     *  - iTotalComment:        integer
     *  - iTotalDislike:        integer
     *  - iTotalLike:           integer
     *  - iTotalView:           integer
     *  - iTotalVotes:          integer
     *  - iUserId:              integer
     *  - iVoted:               integer
     *  - sFullName:            string, display name of the poster.
     *  - sImagePath:           string, image url of the poll
     *  - sQuestion:            string, title of the poll
     *  - sUserImage:           string, image url of the poster
     *  - sUserName:            string, username of the poster
     * 
     * - aThread:              array, detail data of thread 
     *  - bCanCloseThread:                     boolean
     *  - bCanDeleteThread:                    boolean
     *  - bCanEditThread:                      boolean
     *  - bCanManageThread:                    boolean
     *  - bCanMergeThread:                     boolean
     *  - bCanPurchaseSponsor:                 boolean
     *  - bCanStickThread:                     boolean
     *  - bIsAnnouncement:                     boolean
     *  - bIsClosed:                           boolean
     *  - bIsSeen:                             boolean
     *  - bIsSubscribed:                       boolean
     *  - iForumId:                            integer
     *  - iOrderId:                            integer
     *  - iPollId:                             integer
     *  - iThreadId:                           integer
     *  - iTimeStamp:                          integer
     *  - iTimeUpdate:                         integer
     *  - iTotalPost:                          integer
     *  - iUserId:                             integer
     *  - sText:                               string
     *  - sTextNotParsed:                      string
     *  - sTitle:                              string
     * 
     * @param array $aData
     * @return array
     */
    public function threadformedit($aData)
    {
        $aThread = $this->threaddetail($aData);
        $aThread['aThread']['sText'] = '';
        if(isset($aThread['aPost'][0])){
            $aThread['aThread']['sTextNotParsed'] = $aThread['aPost'][0]['sTextNotParsed'];
        }

        $response  =  array(
            'view_options'=> Phpfox::getService('mfox.privacy')->privacy($aData),
            'comment_options'=> Phpfox::getService('mfox.privacy')->privacycomment($aData),
            'types'=> $this->getTypes(array('iForumId' => $aThread['aThread']['iForumId']), true),
            'bCanSetClosed'=> $this->__canSetClosed($aThread['aThread']['iForumId'], false),
            'aThread'=> $aThread['aThread'],
            'aPoll'=> $aThread['aPoll'],
        );
        
        return $response;
    }

   /**
    * @ignore 
    */
    public function getTypes($aParams = array(), $bIsEdit = false){
        if(isset($aParams['iForumId']) == false || (int)$aParams['iForumId'] <= 0){
            return array();
        }

        $bIsGroup = '0';
        if(isset($aParams['sModule'])){
            $bIsGroup = '1';
        }

        $types = array();
        $types[] = array(
            'sPhrase' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.thread')),
            'sValue' => 'thread', 
        );

        if(Phpfox::getUserParam('forum.can_stick_thread') 
            || Phpfox::getService('forum.moderate')->hasAccess($iForumId, 'post_sticky')){
            $types[] = array(
                'sPhrase' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.sticky')),
                'sValue' => 'sticky', 
            );
        }

        if(Phpfox::getUserParam('forum.can_sponsor_thread') && (!isset($bIsGroup) || $bIsGroup != '1')){
            $types[] = array(
                'sPhrase' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.sponsor')),
                'sValue' => 'sponsor', 
            );
        }

        if((Phpfox::getUserParam('forum.can_post_announcement') || Phpfox::getService('forum.moderate')->hasAccess($iForumId, 'post_announcement')) 
            && !$bIsEdit){
            $types[] = array(
                'sPhrase' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.announcement')),
                'sValue' => 'announcement', 
            );
        }

        return $types;
    }

    /**
     * Get list of forums.
     * We get ONLY 2 levels currently. 
     * 
     * Request options: nothing
     * 
     * Response data contains: 
     * - aSubForum:        array, structure data is same parent object
     * - bIsCategory:      boolean  
     * - bIsClosed:        boolean  
     * - bIsInvisible:     boolean  
     * - bIsSeen:          boolean  
     * - iForumId:         integer  
     * - iGender:            integer
     * - iLanguageId:        integer
     * - iLastSeenTime:      integer
     * - iParentId:          integer
     * - iPostId:            integer
     * - iThreadId:          integer
     * - iThreadTimeStamp:   integer
     * - iTotalPost:         integer
     * - iTotalSubForum:     integer
     * - iTotalThread:       integer
     * - iUserGroupId:       integer
     * - iUserId:            integer
     * - iUserLevelId:       integer
     * - iViewId:            integer
     * - sDescription:       string
     * - sFullname:          string
     * - sImagePath:         string
     * - sName:              string
     * - sNameUrl:           string
     * - sThreadTitle:       string
     * - sThreadTitleUrl:    string
     * - sUserImage:         string
     * - sUserName:          string
     * 
     * @param array $aData
     * @return array
     */
    public function fetch($aData){
        $aData['bMoreInfo'] = true;
        $aData['iSubLevel'] = 1;
        $aData['bSubCategoryOnly'] = true;
        return $this->getForums($aData);
    }

   /**
    * @ignore 
    */
    public function getForums($aData){
        if(Phpfox::getUserParam('forum.can_view_forum') == false){
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_forum"))
            );
        }
        // init 
        if (!isset($aData['iPage'])){
            $aData['iPage'] = 1;
        }
        if ((int)$aData['iPage'] == 0){
            return array();
        }

        // process 
        $aCond = array();
        $aRows = Phpfox::getService('forum')->live()->getForums();

        $aResult = array();
        foreach ($aRows as $aItem)
        {
            $this->resetCounterForumLevel();
            $data = $this->__getForumData($aItem
                , ( (isset($aData['iSubLevel']) && (int)$aData['iSubLevel'] > 0) ? (int)$aData['iSubLevel'] : 'all')
                , (isset($aData['bSubCategoryOnly']) ? $aData['bSubCategoryOnly'] : true)
                , (isset($aData['bMoreInfo']) ? $aData['bMoreInfo'] : true));        
            $aResult[] = $data;
        }

        return $aResult;
    }

   /**
    * @ignore 
    */
    private function __getForumData($aItem, $subLevel = 'all', $subCategoryOnly = false, $bMoreInfo = true){
        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aItem, '_50_square');

        $shouldGetSubForum = true;
        if(($subLevel != 'all' && (int)$subLevel > 0 && ($this->_iCounterForumLevel + 1) > (int)$subLevel)
            || ($subCategoryOnly == true && $aItem['is_category'] == '0')
            ){
            $shouldGetSubForum = false;
        }

        $aSubForum = array();
        $iTotalSubForum = count($aItem['sub_forum']);
        if($shouldGetSubForum == true){
            $this->_iCounterForumLevel ++;            
            foreach($aItem['sub_forum'] as $forum){
                $aSubForum[] = $this->__getForumData($forum, $subLevel, $subCategoryOnly, $bMoreInfo);
            }
        }

        if($bMoreInfo == true){
            return array(
                    'iForumId' => $aItem['forum_id'],
                    'aSubForum' => $aSubForum,
                    'sName' => Core\Lib::phrase()->isPhrase($aItem['name']) ? Phpfox::getService('mfox')->decodeUtf8Compat(_p($aItem['name'])) : html_entity_decode(Phpfox::getLib('locale')->convert($aItem['name'])),

                    'iParentId' => $aItem['parent_id'],
                    'iViewId' => $aItem['view_id'],
                    'bIsCategory' => ($aItem['is_category'] == '1' ? true : false),
                    'sNameUrl' => $aItem['name_url'],
                    'sDescription' => $aItem['description'],
                    'bIsClosed' => ($aItem['is_closed'] == '1' ? true : false),
                    'bIsSeen' => ((int)$aItem['is_seen'] > 0 ? true : false),
                    'iLastSeenTime' => $aItem['last_seen_time'],
                    'iThreadId' => $aItem['thread_id'],
                    'iTotalThread' => $aItem['total_thread'],
                    'iTotalPost' => $aItem['total_post'],
                    'iPostId' => $aItem['post_id'],
                    'sThreadTitle' => $aItem['thread_title'],
                    'sThreadTitleUrl' => $aItem['thread_title_url'],
                    'iThreadTimeStamp' => $aItem['thread_time_stamp'],
                    'iUserId' => $aItem['user_id'],
                    'sUserName' => $aItem['user_name'],
                    'sFullname' => $aItem['full_name'],
                    'iGender' => $aItem['gender'],
                    'sUserImage' => $sUserImage,
                    'sImagePath' => $sUserImage,
                    'bIsInvisible' => $aItem['is_invisible'],
                    'iUserGroupId' => $aItem['user_group_id'],
                    'iLanguageId' => $aItem['language_id'],
                    'iUserLevelId' => $aItem['user_group_id'],
                    'iTotalSubForum' => $iTotalSubForum,
                ); 
        } else {
            return array(
                    'iForumId' => $aItem['forum_id'],
                    'sName' => html_entity_decode(Phpfox::getLib('locale')->convert($aItem['name'])),
                    'aSubForum' => $aSubForum,
                ); 
        }
    }

   /**
    * @ignore 
    */
    private function __getThreadData($aItem, $sMoreInfo = 'large'){
        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aItem, '_50_square');
        $sLastUserImage = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aItem['last_server_id'],
                'path' => 'core.url_user',
                'file' => $aItem['last_user_image'],
                'suffix' => MAX_SIZE_OF_USER_IMAGE,
                'return_url' => true
                    )
            );

        switch ($sMoreInfo) {
            case 'large':
                return array(
                        'bIsSeen' => ((int)$aItem['is_seen'] > 0 ? true : false),
                        'iLastSeenTime' => $aItem['last_seen_time'],
                        'iThreadId' => $aItem['thread_id'],
                        'iForumId' => $aItem['forum_id'],
                        'iGroupId' => $aItem['group_id'],
                        'iPollId' => $aItem['poll_id'],
                        'iViewId' => $aItem['view_id'],
                        'iStartId' => $aItem['start_id'],
                        'bIsAnnouncement' => ((int)$aItem['is_announcement'] > 0 ? true : false),
                        'bIsClosed' => ((int)$aItem['is_closed'] > 0 ? true : false),
                        'iUserId' => $aItem['user_id'],
                        'sTitle' => $aItem['title'],
                        'sTitleUrl' => $aItem['title_url'],
                        'iTimeStamp' => $aItem['time_stamp'],
                        'iTimeUpdate' => $aItem['time_update'],
                        'iOrderId' => $aItem['order_id'],
                        'iPostId' => $aItem['post_id'],
                        'iLastUserId' => $aItem['last_user_id'],
                        'iTotalPost' => $aItem['total_post'],
                        'iTotalView' => $aItem['total_view'],
                        'sUserName' => $aItem['user_name'],
                        'sFullname' => $aItem['full_name'],
                        'iGender' => $aItem['gender'],
                        'sUserImage' => $sUserImage,
                        'sImagePath' => $sUserImage,
                        'bIsInvisible' => $aItem['is_invisible'],
                        'iUserGroupId' => $aItem['user_group_id'],
                        'iLanguageId' => $aItem['language_id'],
                        'iUserLevelId' => $aItem['user_group_id'],
                        'sLastUserName' => $aItem['last_user_name'],
                        'sLastFullname' => $aItem['last_full_name'],
                        'iLastGender' => $aItem['last_gender'],
                        'sLastUserImage' => $sLastUserImage,
                        'sLastImagePath' => $sLastUserImage,
                        'bLastIsInvisible' => $aItem['last_is_invisible'],
                        'iLastUserGroupId' => $aItem['last_user_group_id'],
                        'iLastLanguageId' => $aItem['last_language_id'],
                        'iLastUserLevelId' => $aItem['last_user_group_id'],
                    ); 
                break;
            
            case 'medium':
                return array(
                        'iForumId' => $aItem['forum_id'],
                        'bIsAnnouncement' => ((int)$aItem['is_announcement'] > 0 ? true : false),
                        'bIsClosed' => ((int)$aItem['is_closed'] > 0 ? true : false),
                        'bIsSeen' => ((int)$aItem['is_seen'] > 0 ? true : false),
                        'iOrderId' => $aItem['order_id'],
                        'iPollId' => $aItem['poll_id'],
                        'iThreadId' => $aItem['thread_id'],
                        'iTimeStamp' => $aItem['time_stamp'],
                        'iTimeUpdate' => $aItem['time_update'],
                        'sTitle' => $aItem['title'],
                        'iUserId' => $aItem['user_id'],
                        'bIsSubscribed' => ((int)$aItem['is_subscribed'] > 0 ? true : false),
                    ); 
                break;

            case 'small':
                return array(
                        'iThreadId' => $aItem['thread_id'],
                        'iForumId' => $aItem['forum_id'],
                        'sTitle' => $aItem['title'],
                        'sTitleUrl' => $aItem['title_url'],
                    ); 
                break;

            default:
                break;
        }
    } 

   /**
    * @ignore 
    */
    private function __getPollData($aItem, $sMoreInfo = 'large'){
        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aItem, '_50_square');
        $image_path = Phpfox::getLib('image.helper')->display(array(
                'server_id' => $aItem['server_id'],
                'path' => 'poll.url_image',
                'file' => $aItem['image_path'],
                'suffix' => '',
                'return_url' => true
                    )
            );
        $aAnswer = array();
        foreach($aItem['answer'] as $val){
            $aAnswer[] = array(
                'sAnswer' => $val['answer'],
                'iAnswerId' => $val['answer_id'],
                'iOrdering' => $val['ordering'],
                'iPollId' => $val['poll_id'],
                'iTotalVotes' => $val['total_votes'],
                'iVotePercentage' => $val['vote_percentage'],
            );
        }
        switch ($sMoreInfo) {
            case 'large':
            case 'medium':
            case 'small':
                return array(
                        'aAnswer' => $aAnswer,
                        'iAnswerId' => (int)$aItem['answer_id'],
                        'sFullName' => $aItem['full_name'],
                        'bHideVote' => ((int)$aItem['hide_vote'] > 0 ? true : false),
                        'sImagePath' => $image_path,
                        'iPercentage' => $aItem['percentage'],
                        'iPollId' => $aItem['poll_id'],
                        'iProfilePageId' => $aItem['profile_page_id'],
                        'sQuestion' => $aItem['question'],
                        'iTimeStamp' => $aItem['time_stamp'],
                        'iTotalComment' => $aItem['total_comment'],
                        'iTotalDislike' => $aItem['total_dislike'],
                        'iTotalLike' => $aItem['total_like'],
                        'iTotalView' => $aItem['total_view'],
                        'iTotalVotes' => $aItem['total_votes'],
                        'iUserId' => $aItem['user_id'],
                        'sUserImage' => $sUserImage,
                        'sUserName' => $aItem['user_name'],
                        'bUserVotedThisPoll' => $aItem['user_voted_this_poll'],
                        'iVoted' => $aItem['voted'],
                        'bIsVoted' => ((int)$aItem['voted'] > 0 ? true : false),
                    ); 
                break;

            default:
                break;
        }
    }

   /**
    * @ignore 
    */
    private function __getAnnouncementData($aItem, $bMoreInfo = true){
        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aItem, '_50_square');

        if($bMoreInfo == true){
            return array(
                    'iThreadId' => $aItem['thread_id'],
                    'iForumId' => $aItem['forum_id'],
                    'iGroupId' => $aItem['group_id'],
                    'iPollId' => $aItem['poll_id'],
                    'iViewId' => $aItem['view_id'],
                    'iStartId' => $aItem['start_id'],
                    'bIsAnnouncement' => ((int)$aItem['is_announcement'] > 0 ? true : false),
                    'bIsClosed' => ((int)$aItem['is_closed'] > 0 ? true : false),
                    'iUserId' => $aItem['user_id'],
                    'sTitle' => $aItem['title'],
                    'sTitleUrl' => $aItem['title_url'],
                    'iTimeStamp' => $aItem['time_stamp'],
                    'iTimeUpdate' => $aItem['time_update'],
                    'iOrderId' => $aItem['order_id'],
                    'iPostId' => $aItem['post_id'],
                    'iLastUserId' => $aItem['last_user_id'],
                    'iTotalPost' => $aItem['total_post'],
                    'iTotalView' => $aItem['total_view'],
                    'sUserName' => $aItem['user_name'],
                    'sFullname' => $aItem['full_name'],
                    'iGender' => $aItem['gender'],
                    'sUserImage' => $sUserImage,
                    'sImagePath' => $sUserImage,
                    'bIsInvisible' => $aItem['is_invisible'],
                    'iUserGroupId' => $aItem['user_group_id'],
                    'iLanguageId' => $aItem['language_id'],
                    'iUserLevelId' => $aItem['user_group_id'],
                ); 
        } else {
            return array(
                    'iThreadId' => $aItem['thread_id'],
                    'iForumId' => $aItem['forum_id'],
                    'sTitle' => $aItem['title'],
                    'sTitleUrl' => $aItem['title_url'],
                ); 
        }
    }    

   /**
    * @ignore 
    */
    private function __getPostText($iPostId){
        return $this->database()->select('fpt.*')
            ->from(Phpfox::getT('forum_post_text'), 'fpt')
            ->where('fpt.post_id = ' . $iPostId)
            ->execute('getRow');
    }

   /**
    * @ignore 
    */
    private function __getPostData($aItem, $sMoreInfo = 'large', $aThread = null){
        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aItem, '_50_square');
        $oText = $this->__getPostText($aItem['post_id']);
        $sTextNotParsed = isset($oText['text']) ? $oText['text'] : '';

        if($sMoreInfo != 'small'){
            $bCanQuote = false;
            if ($aThread !== null && 
                    (
                        (Phpfox::getUserParam('forum.can_reply_to_own_thread') && $aThread['user_id'] == Phpfox::getUserId()) 
                        || Phpfox::getUserParam('forum.can_reply_on_other_threads') 
                        || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'can_reply')
                    )
                )
            {
                $bCanQuote = true;
            }

            $bCanLike = Phpfox::getService('mfox.like')->canLike($aItem['aFeed']);
        }

		$sLinkUrl =  Phpfox::getLib('url')->makeUrl('forum.thread', array($aItem['thread_id'], 'thread-title','view'=>$aItem['post_id'])) ;
        switch ($sMoreInfo) {
            case 'large':
            case 'medium':
                return array(
                        'aAttachments' => $aItem['attachments'], 
                        'iForumId' => $aItem['forum_id'], 
                        'sFullName' => $aItem['full_name'], 
                        'bIsLiked' => ((int)$aItem['is_liked'] > 0 ? true : false),
                        'iPostId' => $aItem['post_id'],
                        'iProfilePageId' => $aItem['profile_page_id'],
                        'sText' => Phpfox::getLib('parse.output')->parse($aItem['text']),
                        'iThreadId' => $aItem['thread_id'],
                        'iTimeStamp' => $aItem['time_stamp'],
                        'sTitle' => $aItem['title'],
                        'iTotalAttachment' => $aItem['total_attachment'],
                        'iTotalDislike' => $aItem['total_dislike'],
                        'iTotalLike' => $aItem['total_like'],
                        'iTotalPost' => $aItem['total_post'],
                        'iUpdateTime' => $aItem['update_time'],
                        'iUpdateUser' => $aItem['update_user'],
                        'iUserId' => $aItem['user_id'],
                        'sUserImage' => $sUserImage,
                        'sUserName' => $aItem['user_name'],
                        'bCanQuote' => $bCanQuote,
                        'bCanLike' => $bCanLike,
                        'sTextNotParsed' => $sTextNotParsed,
                        'sLink_Url'=> $sLinkUrl,
                    ); 
                break;

            case 'small':
                return array(
                        'iForumId' => $aItem['forum_id'], 
                        'sFullName' => $aItem['full_name'], 
                        'iPostId' => $aItem['post_id'],
                        'iProfilePageId' => $aItem['profile_page_id'],
                        'sText' => Phpfox::getLib('parse.output')->parse($aItem['text']),
                        'iThreadId' => $aItem['thread_id'],
                        'iTimeStamp' => $aItem['time_stamp'],
                        'sTitle' => isset($aItem['thread_title']) ? $aItem['thread_title'] : $aItem['title'],
                        'iUserId' => $aItem['user_id'],
                        'sUserName' => $aItem['user_name'],
                        'sTextNotParsed' => $sTextNotParsed,
                    ); 
                break;

            default:
                break;
        }
    }    

    /**
     * Get list of threads with specific view (my-thread/new/subscribed).
     * 
     * Request options: 
     * - sView:          string, required, value is my-thread/new/subscribed
     * - iPage:          integer, starting from 1
     * 
     * Response data contains: 
     * - bIsAnnouncement:              boolean
     * - bIsClosed:                    boolean
     * - bIsInvisible:                 boolean
     * - bIsSeen:                      boolean
     * - bLastIsInvisible:             boolean
     * - iForumId:                     integer
     * - iGender:                      integer, gender of poster
     * - iGroupId:                     integer
     * - iLanguageId:                  integer
     * - iLastGender:                  integer
     * - iLastLanguageId:              integer
     * - iLastSeenTime:                integer
     * - iLastUserGroupId:             integer
     * - iLastUserId:                  integer
     * - iLastUserLevelId:             integer
     * - iOrderId:                     integer
     * - iPollId:                      integer, zero if nothing poll
     * - iPostId:                      integer, first post in thread, zero if nothing post 
     * - iStartId:                     integer, post which is created when creating thread
     * - iThreadId:                    integer
     * - iTimeStamp:                   integer
     * - iTimeUpdate:                  integer
     * - iTotalPost:                   integer
     * - iTotalView:                   integer
     * - iUserGroupId:                 integer
     * - iUserId:                      integer
     * - iUserLevelId:                 integer
     * - iViewId:                      integer
     * - sFullname:                    string
     * - sImagePath:                   string
     * - sLastFullname:                string
     * - sLastImagePath:               string
     * - sLastUserImage:               string
     * - sLastUserName:                string
     * - sTitle:                       string
     * - sTitleUrl:                    string
     * - sUserImage:                   string
     * - sUserName:                    string
     * 
     * @param array $aData
     * @return array
     */
    public function threadfetch($aData){
        return $this->detail($aData);
    }

    /**
     * @ignore
     */
    public function postfetch($aData){
        
    }

    /**
     * List of all forums with recursive data  
     * 
     * Request options: nothing
     * 
     * Response data contains: 
     * - aSubForum:         array, structure data is same parent object
     * - iForumId:          integer
     * - sName:             string, title of forum
     * 
     * @param array $aData
     * @return array
     */
    public function getforumsearch($aData){
        $aData['bMoreInfo'] = false;
        $aData['iSubLevel'] = 0;
        $aData['bSubCategoryOnly'] = false;
        return $this->getForums($aData);
    }

    /**
     * Get detail data of forum
     * 
     * Request options: 
     * - iForumId:         integer, required
     * - iPage:         integer, starting from 1
     * - sOrder:         string, sort by time_update/full_name/total_post/title/total_view, default is time_update
     * - iAmountOfThread:         integer, default is 10
     * 
     * Response data contains: 
     * - aAnnouncement:         list of announcement, each item contains:
     * <br/><code>
     * <br/>  bIsAnnouncement: true
     * <br/>  bIsClosed: false
     * <br/>  bIsInvisible: "0"
     * <br/>  iForumId: "2"
     * <br/>  iGender: "1"
     * <br/>  iGroupId: "0"
     * <br/>  iLanguageId: "en"
     * <br/>  iLastUserId: "0"
     * <br/>  iOrderId: "0"
     * <br/>  iPollId: "0"
     * <br/>  iPostId: "0"
     * <br/>  iStartId: "149"
     * <br/>  iThreadId: "113"
     * <br/>  iTimeStamp: "1403868757"
     * <br/>  iTimeUpdate: "1403868757"
     * <br/>  iTotalPost: "0"
     * <br/>  iTotalView: "1"
     * <br/>  iUserGroupId: "1"
     * <br/>  iUserId: "1"
     * <br/>  iUserLevelId: "1"
     * <br/>  iViewId: "0"
     * <br/>  sFullname: "Admin"
     * <br/>  sImagePath: "http://product-dev.younetco.com/lytk/phpfox376/file/pic/user/1_50_square.jpg"
     * <br/>  sTitle: "New Announcement"
     * <br/>  sTitleUrl: "new-announcement"
     * <br/>  sUserImage: "http://product-dev.younetco.com/lytk/phpfox376/file/pic/user/1_50_square.jpg"
     * <br/>  sUserName: "admin"
     * </code>
     * 
     * - aForum:         array
     * <br/><code>
     * <br/> bCanAddThread: true
     * <br/> bIsCategory: false
     * <br/> bIsClosed: false
     * <br/> iForumId: "2"
     * <br/> iParentId: "1"
     * <br/> iTotalAnnouncement: 4
     * <br/> iTotalThread: "14"
     * <br/> iViewId: "0"
     * <br/> sDescription: null
     * <br/> sName: "General"
     * <br/> sNameUrl: "general"
     * </code>
     * 
     * - aSubForum:         list of sub-forum, see "fetch" method for each item
     * 
     * - aThread:         list of thread, see "threadfetch" method for each item
     * 
     * @param array $aData
     * @return array
     */
    public function detail($aData){
        $iForumId = isset($aData['iForumId']) ? (int) $aData['iForumId'] : 0;
        if (!Phpfox::getUserParam('forum.can_view_forum'))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_forum"))
            );
        }

        $aCond = array();
        $aCondSearchThreadPost = array();
        $aParentModule = array(
            'module_id' => isset($aData['sModule']) ? $aData['sModule'] : '',
            'item_id' => isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0
        );
        if($aParentModule['item_id'] == 0){
            $aParentModule = null;
        }
        $bIsSearch = false; 
        $isSearchThreadPost = isset($aData['isSearchThreadPost']) ? true : false;
        $bIsSearch = ($isSearchThreadPost ? true : false); 
        $sOrderThreadPost = 'ft.time_update DESC';
        $aCallback = null;
        $sView = (isset($aData['sView']) && !empty($aData['sView']) ? $aData['sView'] : ''); 
        $bShowPosts = false;

        $bIsTagSearch = false;
        $bIsModuleTagSearch = false;
        // if ($this->request()->get('req2') == 'tag' && $this->request()->get('req3'))
        // {
        //     $bIsSearch = true;
        //     $bIsTagSearch = true;
        // }

        // if ($this->request()->get('req2') == 'tag' && $this->request()->get('req5') && $this->request()->get('module'))
        // {           
        //     if ($aCallback = Phpfox::getService('group')->getGroup($this->request()->get('item')))
        //     {
        //         $bIsSearch = true;
        //         $bIsTagSearch = true;
        //         $bIsModuleTagSearch = true;
        //         $aCallback['url_home'] = 'group.' . $aCallback['title_url'] . '.forum';
        //     }           
        // }
        
        $bIsSearchQuery = false;
        $oSearch = Phpfox::getService('forum')->getSearchFilter($bIsSearchQuery);
        if ($isSearchThreadPost)
        {
            // support when searching thread/post
            $aSearch = array(
                'keyword' => isset($aData['sKeyword']) ? $aData['sKeyword'] : '', 
                'result' => (isset($aData['sSearchType']) && $aData['sSearchType'] == 'post' ? 1 : 0), 
                'submit' => 'Go', 
            );
            if(isset($aData['sForumIds']) && !empty($aData['sForumIds'])){
                $aForumId = explode(",", $aData['sForumIds']);
                $aSearch['forum'] = $aForumId;
            }    

            if (!empty($aSearch['forum']) && is_array($aSearch['forum']))
            {
                $sForumIds = '';
                foreach ($aSearch['forum'] as $iSearchForum)
                {
                    if (!is_numeric($iSearchForum))
                    {
                        continue;
                    }
                    
                    if (empty($aSearch['group_id']))
                    {
                        if (!Phpfox::getService('forum')->hasAccess($iSearchForum, 'can_view_forum'))
                        {
                            continue;
                        }
                    }
                    
                    $sForumIds .= $iSearchForum . ',';
                }
                $sForumIds = rtrim($sForumIds, ',');                
                
                if (!empty($sForumIds))
                {
                    $aCondSearchThreadPost[] = ' ft.forum_id IN(' . $sForumIds . ')';
                }
            } else {
                if (empty($aSearch['group_id']))
                {
                    $sForums = Phpfox::getService('forum')->getCanViewForumAccess('can_view_forum');
                    if ($sForums !== false)
                    {
                        $aCondSearchThreadPost[] = ' ft.forum_id NOT IN(' . $sForums . ')';                    
                    }                    
                }
            }

            if (!empty($aSearch['user']))
            {
                $aCondSearchThreadPost[] = Phpfox::getService('mfox.helper.libsearch')->search('like%', 'u.full_name', $aSearch['user']);
            }

            if ($aParentModule !== null)
            {
                $aCondSearchThreadPost[] = ' ft.group_id = ' . (int) $aParentModule['item_id'];
            }
            else
            {
                $aCondSearchThreadPost[] = ' ft.group_id = 0';
            }

            if ($sView == 'pending-post' && Phpfox::getUserParam('forum.can_approve_forum_post'))
            {
                $aSearch['result'] = '1';
                $aCondSearchThreadPost[] = ' fp.view_id = 1';
            }

            if (empty($aSearch['result']))
            {
                // search thread
                if (!empty($aSearch['keyword']))
                {
                    $aCondSearchThreadPost[] = Phpfox::getService('mfox.helper.libsearch')->search('like%', array('ft.title'), $aSearch['keyword']);
                }

                if (!empty($aSearch['days_prune']) && $aSearch['days_prune'] != '-1')
                {
                    $aCondSearchThreadPost[] = ' ft.time_stamp >= ' . (PHPFOX_TIME - ($aSearch['days_prune'] * 86400));
                }       
                
                $mField = 'ft.thread_id';
                $aSearchResults = Phpfox::getService('forum.thread')->getSearch(
                    implode(' AND ', $aCondSearchThreadPost)
                    , $sOrderThreadPost
                );
            } else 
            {
                // search post
                $mField = 'fp.post_id';
                if (!empty($aSearch['keyword']))
                {
                    $aCondSearchThreadPost[] = Phpfox::getService('mfox.helper.libsearch')->search('like%', array('fp.title', 'fpt.text'), $aSearch['keyword']);
                }               
                
                if (!empty($aSearch['days_prune']) && $aSearch['days_prune'] != '-1')
                {
                    $aCondSearchThreadPost[] = ' fp.time_stamp >= ' . (PHPFOX_TIME - ($aSearch['days_prune'] * 86400));
                }
                
                if (empty($aSearch['group_id']))
                {
                    $sForums = Phpfox::getService('forum')->getCanViewForumAccess('can_view_thread_content');
                    if ($sForums !== false)
                    {
                        $aCondSearchThreadPost[] = ' ft.forum_id NOT IN(' . $sForums . ')';
                    }                   
                }

                $aSearchResults = Phpfox::getService('forum.post')->getSearch(
                    implode(' AND ', $aCondSearchThreadPost)
                    , $sOrderThreadPost
                );                                                     
            }
        }

        if (!isset($aData['iPage'])){
            $aData['iPage'] = 1;
        }
        if ((int)$aData['iPage'] == 0){
            return array();
        }
        $iPage = (int)$aData['iPage'];
        $sSortBy = (isset($aData['sOrder']) && empty($aData['sOrder']) == false) ? $aData['sOrder'] : 'time_update';
        // sort by  
        $sOrder = 'ft.time_update DESC';
        if ($sSortBy == 'time_update'){
            $sOrder = 'ft.time_update DESC';
        } else if ($sSortBy == 'full_name'){
            $sOrder = 'u.full_name DESC';
        } else if ($sSortBy == 'total_post'){
            $sOrder = 'ft.total_post DESC';
        } else if ($sSortBy == 'title'){
            $sOrder = 'ft.title DESC';
        } else if ($sSortBy == 'total_view'){
            $sOrder = 'ft.total_view DESC';
        }        
        $iAmountOfThread = isset($aData['iAmountOfThread']) ? (int) $aData['iAmountOfThread'] : 10;
        $iPageSize = $iAmountOfThread;

        $sViewId = 'ft.view_id = 0';
        if ($aCallback === null)
        {           
            if (Phpfox::getUserParam('forum.can_approve_forum_thread') 
                || Phpfox::getService('forum.moderate')->hasAccess($iForumId, 'approve_thread'))
            {
                $sViewId = 'ft.view_id >= 0';   
            }
        }

        if ($aParentModule == null)
        {
            $aForums = Phpfox::getService('forum')->live()->id($iForumId)->getForums();     
            $aForum = Phpfox::getService('forum')->id($iForumId)->getForum();
        }
        else
        {
            $aForum = array();
            $aForums = array();
        }

        // support search thread/post
        if ($isSearchThreadPost){
            if(count($aSearchResults) == 0){
                return array('aThread' => array());
            }

            $iOffSet = Phpfox::getLib('pager')->getOffset($iPage, $iPageSize, count($aSearchResults));      
            $iCnt = 0;
            $sSearchResults = '';
            foreach ($aSearchResults as $iKey => $sValue)
            {
                // if ($iKey < $iOffSet)
                // {
                //     continue;
                // }
                        
                // $iCnt++;                    
                // if ($iCnt > $iPageSize)
                // {
                //     break;
                // }
                        
                $sSearchResults .= $sValue . ',';
            }
            $sSearchResults = rtrim($sSearchResults, ',');        
            $aCond[] = ' AND ' . $mField . ' IN(' . $sSearchResults . ')';                    
        }

        if (!$bIsSearch && $sView != 'pending-post')
        {
            if ($aParentModule === null)
            {
                if (!isset($aForum['forum_id']) && empty($sView))
                {               
                    return array(
                        'result' => 0,
                        'error_code' => 1,
                        'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.not_a_valid_forum'))
                    );
                }

                if (!empty($sView))
                {
                    switch ($sView)
                    {
                        case 'my-thread':
                            $aCond[] = ' AND ft.user_id = ' . Phpfox::getUserId();                    
                            // $bShowPosts = true;
                            break;
                        case 'pending-thread':
                            if (Phpfox::getUserParam('forum.can_approve_forum_thread'))
                            {
                                $sViewId = 'ft.view_id = 1';                                
                            }
                            break;
                        default:
                            
                            break;
                    }                   
                    
                    $aCond[] = ' AND ft.group_id = 0 AND ' . $sViewId . ' AND ft.is_announcement = 0';                    
                    
                    $bIsSearch = true;
                } else {
                    $aCond[] = ' AND ft.forum_id = ' . $aForum['forum_id'] . ' AND ft.group_id = 0 AND ' . $sViewId . ' AND ft.is_announcement = 0';                    
                }

            } else {
                $aCond[] = ' AND ft.forum_id = 0 AND ft.group_id = ' . $aParentModule['item_id'] . ' AND ' . $sViewId . ' AND ft.is_announcement = 0';
            }

            // get the forums that we cant access
            $aForbiddenForums = Phpfox::getService('forum')->getForbiddenForums();
            if (!empty($aForbiddenForums))
            {
                $aCond[] = ' AND ft.forum_id NOT IN (' . implode(',', $aForbiddenForums) . ')';                    
            }            
        }

        if ((isset($aSearch) && $aSearch['result'] == 1) || $sView == 'pending-post')
        {
            if ($sView == 'pending-post')
            {
                $bIsSearch = true;
                $bForceResult = true;               
                $aCond[] = ' AND fp.view_id = 1';                    
            }
            
            list($iCnt, $aThreads) = Phpfox::getService('forum.post')
                ->callback($aCallback)
                ->get($aCond
                    , $sOrder
                    , $iPage
                    , $iPageSize
            );                                  
        } else {
            $iDaysPrune = false;
            if ($iDaysPrune && $iDaysPrune != '-1')
            {
                $aCond[] = ' AND ft.time_stamp >= ' . (PHPFOX_TIME - ($iDaysPrune * 86400));                    
            }           

            // if ($bIsTagSearch === true)
            // {
            //     if ($bIsModuleTagSearch)
            //     {
            //         $oSearch->setCondition("AND ft.group_id = " . (int) $aCallback['group_id'] . " AND tag.tag_url = '" . Phpfox::getLib('database')->escape($this->request()->get('req5')) . "'");                 
            //     }
            //     else 
            //     {
            //         $oSearch->setCondition("AND ft.group_id = 0 AND tag.tag_url = '" . Phpfox::getLib('database')->escape($this->request()->get('req3')) . "'");
            //     }
            // }           
            list($iCnt, $aThreads) = Phpfox::getService('forum.thread')->isSearch($bIsSearch)
                ->isTagSearch($bIsTagSearch)
                ->isNewSearch(($sView == 'new' ? true : false))
                ->isSubscribeSearch(($sView == 'subscribed' ? true : false))
                ->isModuleSearch($bIsModuleTagSearch)
                ->get($aCond, 'ft.order_id DESC, ' . $sOrder, $iPage, $iPageSize);                      
        }
        // check current page with total results 
        list($fromResult, $toResult, $isShowPrev, $pagePrev, $isShowNext, $pageNext) = Phpfox::getService('mfox.core')->caculatePaging(
            (int)$iCnt
            , (int)$iPageSize
            , (int)$iPage - 1
        );
        if($pageNext == 0){
            $aThreads = array();
        }        

        $aAccess = Phpfox::getService('forum')->getUserGroupAccess($iForumId, Phpfox::getUserBy('user_group_id'));

        if ($bIsSearch)
        {
            // not support yet
        } else {
            if ($aParentModule === null)
            {
                if (!Phpfox::getService('forum')->hasAccess($aForum['forum_id'], 'can_view_forum'))
                {
                    return array(
                        'result' => 0,
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_forum"))
                    );
                }

                $aAnnouncements = Phpfox::getService('forum.thread')->getAnnoucements($iForumId);
            } else {
                $aAnnouncements = Phpfox::getService('forum.thread')->getAnnoucements(null, isset($aParentModule['item_id']) ? $aParentModule['item_id']: 1);
            }
        }

        // process result 
        if((isset($aSearch) && $aSearch['result'] == 1)){
            $aPost = $aThreads;
            $aPostData = array();
            foreach ($aPost as $aItem)
            {
                $data = $this->__getPostData($aItem, 'small', null);       
                $aPostData[] = $data;
            }            
        } else {
            $aThreadsData = array();
            foreach ($aThreads as $aItem)
            {
                $data = $this->__getThreadData($aItem, 'large');        
                $aThreadsData[] = $data;
            }            
        }

        // end 
        if($bIsSearch){
            if((isset($aSearch) && $aSearch['result'] == 1)){
                return array(
                    'aPost' => $aPostData, 
                );                
            } else {
                return array(
                    'aThread' => $aThreadsData, 
                );                
            }
        } else if($iPage == 1){
            $aSubForum = array();
            foreach ($aForums as $aItem)
            {
                $this->resetCounterForumLevel();
                $data = $this->__getForumData($aItem, 1, true, true);        
                $aSubForum[] = $data;
            }

            $aAnnouncementsData = array();
            foreach ($aAnnouncements as $aItem)
            {
                $data = $this->__getAnnouncementData($aItem, true);        
                $aAnnouncementsData[] = $data;
            }

            $bCanAddThread = false;
            if (!$aForum['is_closed'] && Phpfox::getUserParam('forum.can_add_new_thread') || Phpfox::getService('forum.moderate')->hasAccess($aForum['forum_id'], 'add_thread')) {
                $bCanAddThread = true;
            }

            if (!Forum_Service_Forum::instance()->hasAccess($aForum['forum_id'], 'can_start_thread')) {
                $bCanAddThread = false;
            }

            return array(
                'aForum' => array(
                                'iForumId' => $aForum['forum_id'],
                                 'sName' => Core\Lib::phrase()->isPhrase($aForum['name']) ? Phpfox::getService('mfox')->decodeUtf8Compat(_p($aForum['name'])) : html_entity_decode(Phpfox::getLib('locale')->convert($aForum['name'])),
                                'iParentId' => $aForum['parent_id'],
                                'iViewId' => $aForum['view_id'],
                                'bIsCategory' => ($aForum['is_category'] == '1' ? true : false),
                                'sNameUrl' => $aForum['name_url'],
                                'sDescription' => $aForum['description'],
                                'bIsClosed' => ($aForum['is_closed'] == '1' ? true : false),
                                'bCanAddThread' => $bCanAddThread,
                                'iTotalThread' => $iCnt,
                                'iTotalAnnouncement' => count($aAnnouncementsData),
                            ), 
                'aSubForum' => $aSubForum, 
                'aThread' => $aThreadsData, 
                'aAnnouncement' => $aAnnouncementsData, 
            );
        } else {
            return array(
                'aThread' => $aThreadsData, 
            );
        }
    }

    /**
     * Get detail data of thread
     * 
     * Request options: 
     * - iThreadId:         integer, required
     * - iPage:         integer, starting from 1
     * - iAmountOfPost:         integer, default is 10
     * 
     * Response data contains: 
     * - aPoll:         detail data of poll, see "threadformedit" method for object 
     * - aPost:         list of posts, data contains: 
     * <br/><code>
     * <br/>  aAttachments: list of attachments, we support ONLY link/image currently 
    * <div style="margin-left: 10px;">   
        * 0: 
            * <div style="margin-left: 10px;">  
               * attachment_id: "18"
               * <br/>  data: 
                   * <div style="margin-left: 10px;">  photo_url: "http://product-dev.younetco.com/lytk/phpfox376/file/attachment/2014/06/2e933e27cd54a36942b88bb0178a520c.jpg" </div>
               * <br/>  type: "image"
            * </div>  
        * 1: 
           * <div style="margin-left: 10px;">    
                * attachment_id: "17"
               * <br/>  data: 
                    * <div style="margin-left: 10px;">  description: "Th&#244;ng tin nhanh &amp; m&#7899;i nh&#7845;t &#273;&#432;&#7907;c c&#7853;p nh&#7853;t h&#224;ng gi&#7901;. Tin t&#7913;c Vi&#7879;t Nam &amp; th&#7871; gi&#7899;i v&#7873; x&#227; h&#7897;i, kinh doanh, ph"
                    * <br/>  has_embed: "0"
                    * <br/>  image: "http://st.f1.vnecdn.net/responsive/i/v5/logo_default.jpg"
                    * <br/>  is_custom: "1"
                    * <br/>  item_id: "0"
                    * <br/>  link: "http://vnexpress.net"
                    * <br/>  link_id: "16"
                    * <br/>  module_id: null
                    * <br/>  parent_user_id: "0"
                    * <br/>  privacy: "0"
                    * <br/>  privacy_comment: "0"
                    * <br/>  status_info: null
                    * <br/>  time_stamp: "1403576027"
                    * <br/>  title: "Tin nhanh VnExpress - &#272;&#7885;c b&#225;o, tin t&#7913;c online 24h"
                    * <br/>  total_comment: "0"
                    * <br/>  total_dislike: "0"
                    * <br/>  total_like: "0"
                    * <br/>  user_id: "4"
                    * <br/>  user_name: "profile-4"
               * </div>  type: "link"
            * </div>
     * </div>  bCanLike: true
     * <br/>  bCanQuote: true
     * <br/>  bIsLiked: true
     * <br/>  iForumId: "15"
     * <br/>  iPostId: "69"
     * <br/>  iProfilePageId: "0"
     * <br/>  iThreadId: "45"
     * <br/>  iTimeStamp: "1403576038"
     * <br/>  iTotalAttachment: "2"
     * <br/>  iTotalDislike: "0"
     * <br/>  iTotalLike: "1"
     * <br/>  iTotalPost: "24"
     * <br/>  iUpdateTime: "0"
     * <br/>  iUpdateUser: null
     * <br/>  iUserId: "4"
     * <br/>  sFullName: "An Nguyen An Nguyen"
     * <br/>  sText: "Bla bla<br /><img src="http://product-dev.younetco.com/lytk/phpfox376/file/attachment/2014/06/ef7d2aa2a7ba4dc814cc56fa20e4442f_view.jpg" alt="" />"
     * <br/>  sTextNotParsed: "Bla bla?[img]http://product-dev.younetco.com/lytk/phpfox376/file/attachment/2014/06/ef7d2aa2a7ba4dc814cc56fa20e4442f_view.jpg[/img]"
     * <br/>  sTitle: ""
     * <br/>  sUserImage: "http://product-dev.younetco.com/lytk/phpfox376/file/pic/user/2014/06/f20c7e5e8a2693d0ba9fb469960c029b_50_square.jpg"
     * <br/>  sUserName: "profile-4"
     * </code>
     * 
     * - aThread:         detail data of thread, see "threadformedit" method for object 
     * 
     * @param array $aData
     * @return array
     */
    public function threaddetail($aData){
        $iThreadId = isset($aData['iThreadId']) ? (int) $aData['iThreadId'] : 0;        
        if (!Phpfox::getUserParam('forum.can_view_forum'))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_forum"))
            );
        }

        // init 
        $aParentModule = array(
            'module_id' => isset($aData['sModule']) ? $aData['sModule'] : '',
            'item_id' => isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0
        );
        if($aParentModule['item_id'] == 0){
            $aParentModule = null;
        }
        if (!isset($aData['iPage'])){
            $aData['iPage'] = 1;
        }
        if ((int)$aData['iPage'] == 0){
            return array();
        }
        $iPage = (int)$aData['iPage'];
        $sSortBy = (isset($aData['sOrder']) && empty($aData['sOrder']) == false) ? $aData['sOrder'] : 'time_update';
        // sort by  
        $iAmountOfPost = isset($aData['iAmountOfPost']) ? (int) $aData['iAmountOfPost'] : 10;
        $iPageSize = $iAmountOfPost;
        $aThreadCondition = array();
        $aCallback = null;

        // if (Phpfox::isUser() && ($iView = $this->request()->getInt('view')) && Phpfox::isModule('notification'))
        // {
        //     Phpfox::getService('notification.process')->delete('forum_subscribed_post', $iView, Phpfox::getUserId());
        //     Phpfox::getService('notification.process')->delete('forum_post_like', $iView, Phpfox::getUserId()); 
        // }
        
        $aThreadCondition[] = 'ft.thread_id = ' . $iThreadId . '';
        $sPermaView = null;
        if ((int) $sPermaView <= 0)
        {
            $sPermaView = null;
        }

        list($iCnt, $aThread) = Phpfox::getService('forum.thread')->getThread($aThreadCondition
            , array(), 'fp.time_stamp ASC', $iPage, $iPageSize, $sPermaView);
        if (!isset($aThread['thread_id']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.not_a_valid_thread'))
            );
        }
        // check current page with total results 
        list($fromResult, $toResult, $isShowPrev, $pagePrev, $isShowNext, $pageNext) = Phpfox::getService('mfox.core')->caculatePaging(
            (int)$iCnt
            , (int)$iPageSize
            , (int)$iPage - 1
        );
        if($pageNext == 0){
            $aThread['posts'] = array();
        }  

        if ($aThread['group_id'] > 0)
        {
            $aCallback = Phpfox::callback('pages.addForum', $aThread['group_id']);  
            if (!Phpfox::getService('pages')->hasPerm($aThread['group_id'], 'forum.view_browse_forum'))
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.not_a_valid_thread'))
                );
            }
        }       

        // not support check title 
        // Phpfox::getService('core.redirect')->check($aThread['title'], 'req4');
        
        if ($aThread['view_id'] != '0' && $aThread['user_id'] != Phpfox::getUserId())
        {
            if (!Phpfox::getUserParam('forum.can_approve_forum_thread') 
                && !Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'approve_thread'))
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.not_a_valid_thread'))
                );
            }
        }

        if ($aCallback === null && !Phpfox::getService('forum')->hasAccess($aThread['forum_id'], 'can_view_forum'))
        {
            if (Phpfox::isUser())
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.you_do_not_have_the_proper_permission_to_view_this_thread'))
                );
            }
            else
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.log_in_to_view_thread'))
                );
            }            
        }

        if ($aCallback === null && !Phpfox::getService('forum')->hasAccess($aThread['forum_id'], 'can_view_thread_content'))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.you_do_not_have_the_proper_permission_to_view_this_thread'))
            );
        }

        $aForum = Phpfox::getService('forum')           
            ->id($aThread['forum_id'])
            ->getForum(); 

        // not support yet
        // if ($this->request()->get('approve') && (Phpfox::getUserParam('forum.can_approve_forum_thread') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'approve_thread')) && $aThread['view_id'])
        // {
        //     $sCurrentUrl = $this->url()->permalink('forum.thread', $aThread['thread_id'], $aThread['title']);
            
        //     if (Phpfox::getService('forum.thread.process')->approve($aThread['thread_id'], $sCurrentUrl))
        //     {
        //         $this->url()->forward($sCurrentUrl);
        //     }
        // }           
        
        // if ($iPostId = $this->request()->getInt('post'))
        // {
        //     $iCurrentPage = Phpfox::getService('forum.post')->getPostPage($aThread['thread_id'], $iPostId, $iPageSize);         
            
        //     $sFinalLink = $this->url()->permalink('forum.thread', $aThread['thread_id'], $aThread['title'], false, null, array('page' => $iCurrentPage));
            
        //     $this->url()->forward($sFinalLink . '#post' . $iPostId);
        // }           

        if (!$aThread['is_seen'])
        {
            if ($aCallback === null)
            {
                if (!Phpfox::isUser())
                {
                    return false;
                }
                //Table forum_track is removed
                /*foreach (Phpfox::getService('forum')->id($aForum['forum_id'])->getParents() as $iId)
                {
                    $this->database()->delete(Phpfox::getT('forum_track'), 'forum_id = ' . (int) $iId . ' AND user_id = ' . Phpfox::getUserId());
                    $this->database()->insert(Phpfox::getT('forum_track'), array(
                            'forum_id' => $iId,
                            'user_id' => Phpfox::getUserId(),
                            'time_stamp' => PHPFOX_TIME
                        )
                    );
                }*/
            }
            Phpfox::getService('forum.thread.process')->updateTrack($aThread['thread_id']);
        }

        // if (Phpfox::isModule('tag') && $aCallback === null)
        // {
        //     $aTags = Phpfox::getService('tag')->getTagsById(($aCallback === null ? 'forum' : 'forum_group'), $aThread['thread_id']);                
        //     if (isset($aTags[$aThread['thread_id']]))
        //     {
        //         $aThread['tag_list'] = $aTags[$aThread['thread_id']];
        //     }
        // }       

        // get permission on thread
        list($bCanEditThread, 
            $bCanDeleteThread, 
            $bCanStickThread, 
            $bCanCloseThread, 
            $bCanMergeThread, 
            $bCanManageThread, 
            $bCanPurchaseSponsor) = $this->__getPermissionThread($aCallback, $aThread);

        // update posts data 
        $aPost = array();
        foreach ($aThread['posts'] as $keyPost => $aItem)
        {
            $attachmentResult = array();
            if(isset($aItem['attachments']) == true){
                foreach ($aItem['attachments'] as $key => $att) {
                    if((int)$att['link_id'] > 0){
                        // link attachment
                        $type = 'link';
                        $link  = Phpfox::getService('mfox.helper.attachment')->getDetailAttachmentByType($type, (int)$att['link_id'], $att);
                        if(isset($link['link_id'])){
                            $attachmentResult[] = array('attachment_id' => $att['attachment_id'], 'type' => $type, 'data' => $link);
                        }
                    } else if($att['is_image']){
                        // image attachment
                        $type = 'image';
                        $url = Phpfox::getService('mfox.helper.attachment')->getDetailAttachmentByType($type, 0, $att);
                        $attachmentResult[] = array('attachment_id' => $att['attachment_id'], 'type' => $type, 'data' => array('photo_url' => $url));
                    }
                }
            }
            $aThread['posts'][$keyPost]['attachments'] = $attachmentResult;        
            $aThread['posts'][$keyPost] = $this->__getPostData($aThread['posts'][$keyPost], 'large', $aThread);       

            $aPost[] = $aThread['posts'][$keyPost];
        }

        if($iPage == 1){
            $aThreadData = $this->__getThreadData($aThread, 'medium');
			
			$sLinkUrl =  Phpfox::getLib('url')->makeUrl('forum.thread', array($iThreadId,'title' )) ;
			
            $aThreadData = array_merge($aThreadData, array(
                'iTotalPost' => $iCnt, 
                'sLink_Url'=>$sLinkUrl, // since 3.08
                'bCanManageThread' => $bCanManageThread,
                'bCanEditThread' => $bCanEditThread,
                'bCanDeleteThread' => $bCanDeleteThread,
                'bCanStickThread' => $bCanStickThread,
                'bCanCloseThread' => $bCanCloseThread,
                'bCanMergeThread' => $bCanMergeThread,
                'bCanPurchaseSponsor' => $bCanPurchaseSponsor,
            ));

            $aPoll = array();
            if(!empty($aThread['poll'])){
                $aPoll = $this->__getPollData($aThread['poll'], 'large');
            }

            return array(
                'aThread' => $aThreadData, 
                'aPoll' => $aPoll,
                'aPost' => $aPost,
            );
        } else {
            return array(
                'aPost' => $aPost,
            );            
        }
    }

   /**
    * @ignore 
    */
    private function __getPermissionThread($aCallback = null, $aThread = null){
        $bCanManageThread = false;      
        $bCanEditThread = false;
        $bCanDeleteThread = false;
        $bCanStickThread = false;
        $bCanCloseThread = false;
        $bCanMergeThread = false;

        if ($aCallback === null && isset($aThread['thread_id']))
        {           
            if (((Phpfox::getUserParam('forum.can_edit_own_post') && $aThread['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_edit_other_posts') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'edit_post')))
            {
                $bCanEditThread = true; 
            }
            
            if ((Phpfox::getUserParam('forum.can_delete_own_post') && $aThread['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_delete_other_posts') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'delete_post'))
            {
                $bCanDeleteThread = true;
            }
            
            if ((Phpfox::getUserParam('forum.can_stick_thread') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'post_sticky')))
            {
                $bCanStickThread = true;
            }
            
            if ((Phpfox::getUserParam('forum.can_close_a_thread') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'close_thread')))
            {
                $bCanCloseThread = true;
            }
            
            if ((Phpfox::getUserParam('forum.can_merge_forum_threads') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'merge_thread')))
            {
                $bCanMergeThread = true;
            }
            
            if (
                ((Phpfox::getUserParam('forum.can_edit_own_post') && $aThread['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_edit_other_posts') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'edit_post'))
                || (Phpfox::getUserParam('forum.can_move_forum_thread') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'move_thread'))
                || (Phpfox::getUserParam('forum.can_copy_forum_thread') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'copy_thread'))
                || (Phpfox::getUserParam('forum.can_delete_own_post') && $aThread['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_delete_other_posts') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'delete_post')
                || (Phpfox::getUserParam('forum.can_stick_thread') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'post_sticky'))
                || (Phpfox::getUserParam('forum.can_close_a_thread') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'close_thread'))
                || (Phpfox::getUserParam('forum.can_merge_forum_threads') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'merge_thread'))
            )
            {
                $bCanManageThread = true;   
            }
        }
        else 
        {
            if (Phpfox::getService('pages')->isAdmin($aCallback['item']))
            {
                $bCanEditThread = true;
                $bCanDeleteThread = true;
                $bCanStickThread = true;
                $bCanCloseThread = true;
                $bCanMergeThread = true;
                $bCanManageThread = true;
            }           
        }

        $bCanPurchaseSponsor = false;
        if ( 
            ((Phpfox::getUserParam('forum.can_purchase_sponsor') && $aThread['user_id'] == Phpfox::getUserId())
          || ($bCanCloseThread || $bCanStickThread)
          || Phpfox::getUserParam('forum.can_sponsor_thread')
            )) // sponsor is disabled in gorups
        {
            $bCanPurchaseSponsor = true;
        }        

        return array(
            $bCanEditThread, 
            $bCanDeleteThread, 
            $bCanStickThread, 
            $bCanCloseThread, 
            $bCanMergeThread, 
            $bCanManageThread, 
            $bCanPurchaseSponsor, 
        );                
    }

    /**
     * Create new thread 
     * 
     * Request options: 
     * - iForumId:                   integer, required
     * - sTitle:                     string, required
     * - sText:                      string, required
     * - sAttachment:                string, list of attachment id, example: '9,6,7,'
     * - iIsSubscribed:              integer
     * - iPollId:                    integer
     * - sTypeId:                    string, value is thread/sticky/announcement
     * - iAnnouncementForumId:       integer
     * - iIsClosed:                  integer
     * 
     * Response data contains: 
     * <code>
     * <br/>  "result":1,
     * <br/>  "error_code":0,
     * <br/>  'message':"Your thread has been added",
     * <br/>  "iThreadId":50
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function threadadd($aData){
        // validate
        $iForumId = isset($aData['iForumId']) ? (int) $aData['iForumId'] : 0;
        $sTitle = isset($aData['sTitle']) ? $aData['sTitle'] : '';
        $sText = isset($aData['sText']) ? $aData['sText'] : '';
        if(strlen(trim($sTitle)) == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.provide_title_for_your_thread")));
        }
        if(strlen(trim($sText)) == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.provide_some_text")));
        }
        
        $bCanEditPersonalData = true;
        $aCallback = false;
        $aParentModule = array(
            'module_id' => isset($aData['sModule']) ? $aData['sModule'] : '',
            'item_id' => isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0
        );
        $sModule = null;
        $iItemId = null;
        if($aParentModule['item_id'] == 0){
            $aParentModule = null;
        } else {
            $sModule = $aParentModule['module_id'];
            $iItemId = $aParentModule['item_id'];
        }
        if ($sModule !== null
            && Phpfox::isModule($sModule) 
            && $iItemId !== null
            && Phpfox::hasCallback($sModule, 'addForum')
        ){
            $aCallback = Phpfox::callback($sModule . '.addForum', $iItemId);            
            if ($sModule == 'pages' && !Phpfox::getService('pages')->hasPerm($iItemId, 'forum.share_forum'))
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.unable_to_view_this_item_due_to_privacy_settings'))
                );
            }           
        }

        $iId = $iForumId;
        $aAccess = Phpfox::getService('forum')->getUserGroupAccess($iId, Phpfox::getUserBy('user_group_id'));
        if ($aAccess['can_view_thread_content']['value'] != true)
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.unable_to_view_this_item_due_to_privacy_settings'))
            );
        }

        $bIsEdit = false;
        // support with "thread"
        if ($aCallback === false)
        {
            $aForum = Phpfox::getService('forum')
                ->id($iId)
                ->getForum();
            
            if (!isset($aForum['forum_id']))
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.not_a_valid_forum'))
                );
            }           
            
            if ($aForum['is_closed'])
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.forum_is_closed'))
                );
            }               
        }                

        if (!$bIsEdit)
        {
            $bPass = false;     
            if (Phpfox::getUserParam('forum.can_add_new_thread') || Phpfox::getService('forum.moderate')->hasAccess($aForum['forum_id'], 'add_thread'))
            {
                $bPass = true;  
            }       
            
            if ($bPass === false)
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.insufficient_permission_to_reply_to_this_thread'))
                );
            }
        }    

        $bPosted = false;
        $aVals = array(
            'attachment' => isset($aData['sAttachment']) ? $aData['sAttachment'] : '',  // example: '9,6,7,'
            'forum_id' => $iForumId,
            'title' => $sTitle,
            'text' => $sText,
            'is_subscribed' => (isset($aData['iIsSubscribed']) && (int)$aData['iIsSubscribed'] <= 0 ? 0 : 1),
            'poll_id' => (isset($aData['iPollId']) &&  (int)$aData['iPollId'] > 0 ? (int)$aData['iPollId'] : ''),
            'tag_list' => (isset($aData['sTopic']) ? $aData['sTopic'] : ''),
            'type_id' => (isset($aData['sTypeId']) ? $aData['sTypeId'] : 'thread'),
            'announcement_forum_id' => (isset($aData['iAnnouncementForumId']) &&  (int)$aData['iAnnouncementForumId'] > 0 ? (int)$aData['iAnnouncementForumId'] : $iForumId),
            'is_closed' => (isset($aData['iIsClosed']) &&  (int)$aData['iIsClosed'] > 0 ? 1 : 0),
        );

        if (isset($aVals['type_id']) && $aVals['type_id'] == 'announcement')
        {
            $bPosted = true;
        }

        if ($bIsEdit){
            //  not support editing 
        } else {
            if (($iFlood = Phpfox::getUserParam('forum.forum_thread_flood_control')) !== 0)
            {
                $aFlood = array(
                    'action' => 'last_post', // The SPAM action
                    'params' => array(
                        'field' => 'time_stamp', // The time stamp field
                        'table' => Phpfox::getT('forum_thread'), // Database table we plan to check
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
                        'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.posting_a_new_thread_a_little_too_soon')) . ' ' . Phpfox::getLib('spam')->getWaitTime()
                    );
                }                                           
            }

            // add thread 
            if (($iId = $this->__threadadd($aVals, $aCallback))){
                $sMessage = 'Your thread has been added';
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'message' => $sMessage,
                    'iThreadId' => $iId,
                );
            } else {
                $sMessage = 'There are some issues when processing. Please try again.';
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'message' => $sMessage,
                );
            }
        }               
    }

    /**
     * Reply on thread 
     * 
     * Request options: 
     * - iThreadId:                   integer, required
     * - sText:                      string, required
     * - sAttachment:                string, list of attachment id, example: '9,6,7,'
     * - iIsSubscribed:              integer
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       'message': "Reply successfully",
     * <br/>       "iPostId": 186
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function threadreply($aData){
        $iThreadId = isset($aData['iThreadId']) ? (int) $aData['iThreadId'] : 0;        
        $aThread = Phpfox::getService('forum.thread')->getForEdit($iThreadId);
        if (!isset($aThread['thread_id']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.not_a_valid_thread'))
            );
        }

        $aVals = array(
            'attachment' => isset($aData['sAttachment']) ? $aData['sAttachment'] : '',  // example: '9,6,7,'
            'total_post' => $aThread['total_post'],
            'thread_id' => $iThreadId,
            'text' => isset($aData['sText']) ? $aData['sText'] : '',
            'is_subscribed' => (isset($aData['iIsSubscribed']) &&  (int)$aData['iIsSubscribed'] <= 0 ? 0 : 1),
        );

        // check if the user entered a forbidden word
        $sReason = Phpfox::getService('mfox.helper.ban')->checkAutomaticBan($aVals['text']);
        if($sReason !== true){
            return array('result' => 0, 'error_code' => 1
                , 'error_message' => $sReason
            );          
        }
        if (Phpfox::getLib('parse.format')->isEmpty($aVals['text'])){
            return array('result' => 0, 'error_code' => 1
                , 'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.provide_a_reply'))
            );          
        }

        $aCallback = false;
        // if (isset($aVals['module'])
        //     && Phpfox::isModule($aVals['module']) 
        //     && isset($aVals['item'])
        //     && Phpfox::hasCallback($aVals['module'], 'addForum')
        // )
        // {
        //     $aCallback = Phpfox::callback($aVals['module'] . '.addForum', $aVals['item']);      
            
        //     if ($aCallback === false)
        //     {
        //         $this->alert( Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.only_members_can_add_a_reply_to_threads')));
        //         $this->call('$Core.processForm(\'#js_forum_submit_button\', true);');
                
        //         return false;
        //     }
        // }       
        
        $aThread = Phpfox::getService('forum.thread')->getActualThread($aVals['thread_id'], $aCallback);
        if ($aThread['is_closed'])
        {
            return array('result' => 0, 'error_code' => 1
                , 'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.thread_is_closed_for_posting'))
            );          
        }
        if ($aCallback === false && $aThread['is_announcement'])
        {
            return array('result' => 0, 'error_code' => 1
                , 'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.thread_is_closed_for_posting'))
            );          
        }

        $bPass = false;     
        if ((Phpfox::getUserParam('forum.can_reply_to_own_thread') && $aThread['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_reply_on_other_threads') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'can_reply'))
        {
            $bPass = true;  
        }       
        if ($bPass === false)
        {
            return array('result' => 0, 'error_code' => 1
                , 'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.insufficient_permission_to_reply_to_this_thread'))
            );          
        }       

        if (($iFlood = Phpfox::getUserParam('forum.forum_post_flood_control')) !== 0)
        {
            $aFlood = array(
                'action' => 'last_post', // The SPAM action
                'params' => array(
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('forum_post'), // Database table we plan to check
                    'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);   
                )
            );
                            
            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood))
            {       
                return array('result' => 0, 'error_code' => 1
                    , 'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.posting_a_new_thread_a_little_too_soon')) . ' ' . Phpfox::getLib('spam')->getWaitTime()
                );          
            }                                           
        }           

        $aVals['forum_id'] = $aThread['forum_id'];
        list($iId, $bApprovePost) = $this->__postadd($aVals, $aCallback);
        if (!$bApprovePost)
        {
            $sMessage = 'Reply successfully';
        } else {
            $sMessage =  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.your_post_has_successfully_been_added_however_it_is_pending_an_admins_approval_before_it_can_be_displayed_publicly'));
        }
        return array(
            'result' => 1,
            'error_code' => 0,
            'message' => $sMessage,
            'iPostId' => $iId,
        );
    }

    /**
     * @ignore
     *
     * @see
     * add new thread
     */
    private function __threadadd($aVals, $aCallback = false, $aExtra = array()){
        static $iLoop = 0;
        // check if the user entered a forbidden word
        $sReason = Phpfox::getService('mfox.helper.ban')->checkAutomaticBan($aVals['text'] . ' ' . $aVals['title']);
        if($sReason !== true){
            return array('result' => 0, 'error_code' => 1
                , 'error_message' => $sReason
            );          
        }
        $aAccess = Phpfox::getService('forum')->getUserGroupAccess($aVals['forum_id'], Phpfox::getUserBy('user_group_id'));
        if ($aAccess['can_view_thread_content']['value'] != true)
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.unable_to_view_this_item_due_to_privacy_settings'))
            );
        }

        if ($this->_bPassed === false){
            $this->__threadcheckType($aVals, $aCallback);
            if (!Phpfox_Error::isPassed())
            {
                return array(
                    'error_code' => 1,
                    'error_message' => implode(', ', Phpfox_Error::get()),
                    'result' => 0
                );
            }           

            if ($aCallback === false){
                if (isset($aVals['type_id']) && $aVals['type_id'] == 'announcement' && (Phpfox::getUserParam('forum.can_post_announcement') || Phpfox::getService('forum.moderate')->hasAccess($aVals['forum_id'], 'post_announcement')) && !empty($aVals['announcement_forum_id'])){
                    $this->_bPassed = true;

                    $aChildren = Phpfox::getService('forum')->id($aVals['announcement_forum_id'])->getChildren();
                    foreach (array_merge(array($aVals['announcement_forum_id']), (is_array($aChildren) ? $aChildren : array())) as $iForumid){
                        $aVals['forum_id'] = $iForumid;    
                        if ($iId = $this->__threadadd($aVals)){
                            $this->database()->insert(Phpfox::getT('forum_announcement'), array('forum_id' => $iForumid, 'thread_id' => $iId));
                        }                                
                    }

                    return $iId;
                }
            }
        }

        $iLoop++;
        $oParseInput = Phpfox::getLib('parse.input');       
        $bHasAttachments = (Phpfox::getUserParam('forum.can_add_forum_attachments') && Phpfox::isModule('attachment') && isset($aVals['attachment']) && !empty($aVals['attachment']));      
        $aInsert = array(
            'forum_id' => ($aCallback === false ? $aVals['forum_id'] : 0),
            'group_id' => ($aCallback === false ? 0 : (int) $aCallback['item']),
            'is_announcement' => (isset($aVals['is_announcement']) ? $aVals['is_announcement'] : 0),
            'is_closed' => ((isset($aVals['is_closed']) && (Phpfox::getUserParam('forum.can_close_a_thread') || Phpfox::getService('forum.moderate')->hasAccess($aVals['forum_id'], 'close_thread'))) ? $aVals['is_closed'] : 0),
            'user_id' => (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()),
            'title' => $oParseInput->clean($aVals['title'], 255),
            'title_url' => $oParseInput->prepareTitle('forum', $aVals['title'], 'title_url', null, Phpfox::getT('forum_thread'), null, false, false),
            'time_stamp' => (isset($aExtra['user_id']) ? $aExtra['time_stamp'] : PHPFOX_TIME),
            'time_update' => (isset($aExtra['time_update']) ? $aExtra['time_update'] : PHPFOX_TIME),
            'order_id' => (isset($aVals['order_id']) ? $aVals['order_id'] : 0)
        );      

        if ($this->_mUpdateView !== null)
        {
            $aInsert['total_view'] = $this->_mUpdateView;
        }
        if (Phpfox::getUserParam('forum.approve_forum_thread') && $aCallback === false)
        {
            $aInsert['view_id'] = '1';
            $bSkipFeedEntry = true;
        }
        if (!empty($aVals['poll_id']) && Phpfox::isModule('poll') && Phpfox::getUserParam('poll.can_create_poll'))
        {
            $aInsert['poll_id'] = (int) $aVals['poll_id'];  
        }

        $iId = $this->database()->insert(Phpfox::getT('forum_thread'), $aInsert);

        if (!empty($aVals['poll_id']) && Phpfox::isModule('poll') && Phpfox::getUserParam('poll.can_create_poll'))
        {
            $this->database()->update(Phpfox::getT('poll'), array('item_id' => $iId), 'poll_id = ' . (int) $aVals['poll_id'] . ' AND user_id = ' . Phpfox::getUserId());
        }

        $iPostId = $this->database()->insert(Phpfox::getT('forum_post'), array(
                'thread_id' => $iId,
                'user_id' => (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()),
                'title' => $oParseInput->clean($aVals['title'], 255),
                'total_attachment' => 0,
                'time_stamp' => (isset($aExtra['time_update']) ? $aExtra['time_update'] : PHPFOX_TIME)
            )
        );

        $this->database()->insert(Phpfox::getT('forum_post_text'), array(
                'post_id' => $iPostId,
                'text' => $oParseInput->clean($aVals['text']),
                'text_parsed' => $oParseInput->prepare($aVals['text'])
            )
        );

        $this->database()->update(Phpfox::getT('forum_thread'), array('start_id' => $iPostId), 'thread_id = ' . $iId);

        if ($aCallback === false && !isset($bSkipFeedEntry))
        {
            foreach (Phpfox::getService('forum')->id($aVals['forum_id'])->getParents() as $iForumid)
            {
                $this->database()->update(Phpfox::getT('forum'), array('thread_id' => $iId, 'post_id' => 0, 'last_user_id' => (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId())), 'forum_id = ' . $iForumid);
                
                Phpfox::getService('forum.process')->updateCounter($iForumid, 'total_thread');
            }
        }

        if ($this->_bUpdateCounter)
        {
            Phpfox::getService('user.field.process')->updateCounter(Phpfox::getUserId(), 'total_post');
        }

        // If we uploaded any attachments make sure we update the 'item_id'
        if ($bHasAttachments)
        {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()), $iPostId);
        }

        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support') && Phpfox::getUserParam('forum.can_add_tags_on_threads'))
        {
            Phpfox::getService('tag.process')->add(($aCallback === false ? 'forum' : 'forum_group'), $iId, (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()), $aVals['text'], true);
        }
        else
        {
            if (Phpfox::getUserParam('forum.can_add_tags_on_threads') && isset($aVals['tag_list']) && ((is_array($aVals['tag_list']) && count($aVals['tag_list'])) || (!empty($aVals['tag_list']))))
            {
                Phpfox::getService('tag.process')->add(($aCallback === false ? 'forum' : 'forum_group'), $iId, (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()), $aVals['tag_list']);
            }
        }

        if ($iLoop === 1 && empty($aExtra) && !isset($bSkipFeedEntry) && !Phpfox::getService('forum')->isPrivateForum($aVals['forum_id']))
        {       
            ((Phpfox::isModule('feed') && !defined('PHPFOX_SKIP_FEED_ENTRY')) ? Phpfox::getService('feed.process')->callback($aCallback)->add('forum', $iId, 0, 0, ($aCallback === null ? 0 : $aCallback['item'])) : null);
        }

        if (!isset($bSkipFeedEntry))
        {
            // Update user activity
            Phpfox::getService('user.activity')->update((isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()), 'forum');       
        }

        if (isset($aVals['is_subscribed']) && $aVals['is_subscribed'])
        {
            Phpfox::getService('forum.subscribe.process')->add($iId, (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()));
        }

        return $iId;
    }

    /**
     * @ignore
     */
    private function __postadd($aVals, $aCallback = false, $aExtra = array())
    {
        $aThread = $this->database()->select('*')
            ->from(Phpfox::getT('forum_thread'))
            ->where('thread_id = ' . (int) $aVals['thread_id'])
            ->execute('getSlaveRow');
        if ($aThread['group_id'] > 0)
        {
            $aCallback = Phpfox::callback('pages.addForum', $aThread['group_id']);
        }

        $oParseInput = Phpfox::getLib('parse.input');
        $bHasAttachments = (Phpfox::getUserParam('forum.can_add_forum_attachments') && Phpfox::isModule('attachment') && isset($aVals['attachment']) && !empty($aVals['attachment']));        
        $bApprovePost = ((Phpfox::getUserParam('forum.approve_forum_post') && $aCallback === false) ? true : false);

        // check if the user entered a forbidden word
        $sReason = Phpfox::getService('mfox.helper.ban')->checkAutomaticBan((isset($aVals['title']) && !empty($aVals['title']) ? $aVals['title'] : '') . ' ' . $aVals['text']);
        if($sReason !== true){
            return array('result' => 0, 'error_code' => 1
                , 'error_message' => $sReason
            );          
        }
        $iId = $this->database()->insert(Phpfox::getT('forum_post'), array(
                'thread_id' => $aVals['thread_id'],
                'view_id' => ($bApprovePost ? '1' : '0'),
                'user_id' => (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()),
                'title' => (empty($aVals['title']) ? '' : $oParseInput->clean($aVals['title'], 255)),
                'total_attachment' => 0,
                'time_stamp' => (isset($aExtra['user_id']) ? $aExtra['time_stamp'] : PHPFOX_TIME)
            )
        );

        $this->database()->insert(Phpfox::getT('forum_post_text'), array(
                'post_id' => $iId,
                'text' => $oParseInput->clean($aVals['text']),
                'text_parsed' => $oParseInput->prepare($aVals['text'])
            )
        );

        if (!$bApprovePost)
        {
            if ($aCallback === false)
            {
                if (empty($aVals['forum_id']))
                {
                    $aVals['forum_id'] = $aThread['forum_id'];
                }
                
                foreach (Phpfox::getService('forum')->id($aVals['forum_id'])->getParents() as $iForumid)
                {
                    $this->database()->update(Phpfox::getT('forum'), array('thread_id' => $aVals['thread_id'], 'post_id' => $iId, 'last_user_id' => (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId())), 'forum_id = ' . $iForumid);
                    
                    Phpfox::getService('forum.process')->updateCounter($iForumid, 'total_post');
                }
            }
            
            $this->database()->update(Phpfox::getT('forum_thread'), array('total_post' => array('= total_post +', 1), 'post_id' => $iId, 'time_update' => (isset($aExtra['user_id']) ? $aExtra['time_stamp'] : PHPFOX_TIME), 'last_user_id' => (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId())), 'thread_id = ' . (int) $aVals['thread_id']);
            
            if ($this->_bUpdateCounterPost)
            {
                Phpfox::getService('user.field.process')->updateCounter(Phpfox::getUserId(), 'total_post');
            }
        }

        // If we uploaded any attachments make sure we update the 'item_id'
        if ($bHasAttachments)
        {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()), $iId);
        }       

        if (!$bApprovePost)
        {
            // Update user activity
            Phpfox::getService('user.activity')->update((isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()), 'forum');
        }

        if (isset($aVals['is_subscribed']) && $aVals['is_subscribed'])
        {
            Phpfox::getService('forum.subscribe.process')->add($aVals['thread_id'], (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()));
        }
        else
        {
            Phpfox::getService('forum.subscribe.process')->delete($aVals['thread_id'], (isset($aExtra['user_id']) ? $aExtra['user_id'] : Phpfox::getUserId()));
        }

        if (empty($aExtra) && !$bApprovePost)
        {       
            Phpfox::getService('forum.subscribe')->sendEmails($aVals['thread_id'], $iId);
            
            $aThread = Phpfox::getService('forum.thread')->getActualThread($aVals['thread_id']);
            
            if (!Phpfox::getService('forum')->isPrivateForum($aThread['forum_id']))
            {
                if (Phpfox::isModule('feed') && !defined('PHPFOX_SKIP_FEED_ENTRY')) 
                {
                    Phpfox::getService('feed.process')->callback($aCallback)->add('forum_post', $iId, 0, 0, ($aCallback === false ? 0 : $aCallback['item']));
                }
            }
        }
        
        return array($iId, $bApprovePost);
    }

    /**
     * @ignore
     *
     * @see
     * check type of thread when creating new item 
     */
    private function __threadcheckType(&$aVals, $aCallback = false){
        unset($aVals['order_id'], $aVals['is_announcement']);   
        
        if (isset($aVals['type_id']))
        {
            switch ($aVals['type_id'])
            {
                case 'sticky':
                    $bHasAccess = false;
                    if ($aCallback !== false)
                    {
                        if (Phpfox::isModule('pages') && Phpfox::getService('pages')->isAdmin($aCallback['group_id']))
                        {
                            $bHasAccess = true; 
                        }
                    }
                    else 
                    {
                        if (Phpfox::getUserParam('forum.can_stick_thread') || Phpfox::getService('forum.moderate')->hasAccess($aVals['forum_id'], 'post_sticky'))
                        {
                            $bHasAccess = true;
                        }
                    }                   
                    if ($bHasAccess)
                    {
                        $aVals['order_id'] = 1;
                    }
                    break;
                case 'announcement':
                    $bHasAccess = false;
                    if ($aCallback !== false)
                    {
                        if (Phpfox::getService('pages')->isAdmin($aCallback['group_id']))
                        {
                            $bHasAccess = true; 
                        }
                    }
                    else 
                    {
                        if ((Phpfox::getUserParam('forum.can_post_announcement') || Phpfox::getService('forum.moderate')->hasAccess($aVals['forum_id'], 'post_announcement')) && !empty($aVals['announcement_forum_id']))
                        {
                            $bHasAccess = true;
                        }
                    }
                    
                    if ($bHasAccess)
                    {
                        $aVals['is_announcement'] = 1;
                    }
                    else 
                    {
                        Phpfox_Error::set( Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.select_a_forum_this_announcement_will_belong_to')));
                        
                        return $aVals;
                    }
                    break;
                case 'sponsor':
                    if (!Phpfox::getUserParam('forum.can_sponsor_thread'))
                    {
                        Phpfox_Error::set( Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.you_are_not_allowed_to_mark_threads_as_sponsor')));
                        return $aVals;
                    }
                    $aVals['order_id'] = 2;
                    break;

            }       
        }
        
        return $aVals;
    }

    /**
     * Attach poll on thread 
     * 
     * Request options: 
     * - sQuestion:                   string, required
     * - aAnswer:                      array, required, least 2 items
     * - iHideVote:              integer
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "aPoll": {
     * <br/>            "iPollId": "1",
     * <br/>            "sQuestion": "text",
     * <br/>            "bHideVote": true,
     * <br/>            "iUserId": 1,
     * <br/>       }
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function threadpolladd($aData){
        if(!Phpfox::isModule("poll"))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_create_new_poll"))
            );
        }
        if (!isset($aData['sQuestion']) || empty($aData['sQuestion']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.provide_a_question_for_your_poll"))
            );
        }
        $aData['aAnswer'] = json_decode($aData['aAnswer']);
        if (!isset($aData['aAnswer']) || count($aData['aAnswer']) < 2)
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_need_to_write_at_least_2_answers"))
            );
        }
        $aAnswerData = $aData['aAnswer'];
        $aAnswer = array();
        foreach ($aAnswerData as $key => $value) {
            $aAnswer[] = array('answer' => $value);
        }
        // init 
        $aVals = array(
            'module_id' => 'forum', 
            'question' => $aData['sQuestion'], 
            'answer' => $aAnswer, 
            'hide_vote' => (isset($aData['iHideVote']) &&  (int)$aData['iHideVote'] > 0 ? 1 : 0), 
        );
        $mErrors = Phpfox::getService('poll')->checkStructure($aVals);
        if (is_array($mErrors))
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(', ', $mErrors),
                'result' => 0
            );
        }       
        if (Phpfox_Error::isPassed() == false)
        {
            $mErrors = Phpfox_Error::get();
            return array(
                'error_code' => 1,
                'error_message' => $mErrors[0],
                'result' => 0
            );
        }       

        // check if question has a question mark
        if (strpos($aVals['question'], '?') === false)
        {
            $aVals['question'] = $aVals['question'] . '?';
        }
        
        if ((list($iId, $aPoll) = Phpfox::getService('poll.process')->add(Phpfox::getUserId(), $aVals)))
        {
            return array(
                'aPoll' => array(
                    'iPollId' => $iId,
                    'sQuestion' => $aPoll['question'],
                    'bHideVote' => ((int)$aPoll['hide_vote'] > 0 ? true : false),
                    'iUserId' => $aPoll['user_id'],
                ),
            );
        }
    }

    /**
     * Delete poll on thread 
     * 
     * Request options: 
     * - iPollId:              integer, required
     * - iThreadId:              integer, required when editing thread
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       'message': "Delete successfully.",
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function threadpolldelete($aData){
        // validate 
        if (!isset($aData['iPollId']) || (int)$aData['iPollId'] == 0)
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_poll_id_param"))
            );
        }

        $iPollId = (int)$aData['iPollId'];

        if (Phpfox::getService('user.auth')->hasAccess('poll', 'poll_id'
            , $iPollId, 'poll.poll_can_delete_own_polls', 'poll.poll_can_delete_others_polls'))
        {
            Phpfox::getService('poll.process')->moderatePoll($iPollId, 2);
            if (isset($aData['iThreadId']) && (int)$aData['iThreadId'] > 0)
            {
                $iThreadId = (int)$aData['iThreadId'];
                Phpfox::getLib('database')->update(Phpfox::getT('forum_thread')
                    , array('poll_id' => '0'), 'thread_id = ' . (int) $iThreadId);
            }

            return array(
                'result' => 1,
                'error_code' => 0,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.delete_successfully"))
            );        
        }

        return array(
            'result' => 0,
            'error_code' => 1,
            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_delete"))
        );        
    }

    /**
     * Search threads or posts
     * 
     * Request options: 
     * - sKeyword:              string, required
     * - sSearchType:              string, it can be {thread, post}
     * - sForumIds:              string, list of forum id (e.g: "1,7,14")
     * - iAmountOfThread:              integer, using both thread and post
     * - iPage:              integer, begining from 1
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "aThread": [
     * <br/>            {
     * <br/>                 "bIsSeen": true,
     * <br/>                 "iLastSeenTime": "1404704854",
     * <br/>                 "iThreadId": "124",
     * <br/>                 "iForumId": "11",
     * <br/>                 "iGroupId": "0",
     * <br/>                 "iPollId": "0",
     * <br/>                 "iViewId": "0",
     * <br/>                 "iStartId": "177",
     * <br/>                 "bIsAnnouncement": false,
     * <br/>                 "bIsClosed": false,
     * <br/>                 "iUserId": "1",
     * <br/>                 "sTitle": "thread for liking",
     * <br/>                 "sTitleUrl": "thread-for-liking",
     * <br/>                 "iTimeStamp": "1404704851",
     * <br/>                 "iTimeUpdate": "1404704851",
     * <br/>                 "iOrderId": "0",
     * <br/>                 "iPostId": "0",
     * <br/>                 "iLastUserId": null,
     * <br/>                 "iTotalPost": "0",
     * <br/>                 "iTotalView": "1",
     * <br/>                 "sUserName": "admin",
     * <br/>                 "sFullname": "Admin",
     * <br/>                 "iGender": "1",
     * <br/>                 "sUserImage": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/user/1_50_square.jpg",
     * <br/>                 "sImagePath": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/user/1_50_square.jpg",
     * <br/>                 "bIsInvisible": "0",
     * <br/>                 "iUserGroupId": "1",
     * <br/>                 "iLanguageId": "en",
     * <br/>                 "iUserLevelId": "1",
     * <br/>                 "sLastUserName": null,
     * <br/>                 "sLastFullname": null,
     * <br/>                 "iLastGender": null,
     * <br/>                 "sLastUserImage": "http://product-dev.younetco.com/lytk/phpfox376/theme/frontend/default/style/default/image/noimage/profile_50.png",
     * <br/>                 "sLastImagePath": "http://product-dev.younetco.com/lytk/phpfox376/theme/frontend/default/style/default/image/noimage/profile_50.png",
     * <br/>                 "bLastIsInvisible": null,
     * <br/>                 "iLastUserGroupId": null,
     * <br/>                 "iLastLanguageId": null,
     * <br/>                 "iLastUserLevelId": null
     * <br/>            }
     * <br/>    ]
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function search($aData){
        // valiate 
        $sKeyword = isset($aData['sKeyword']) ? $aData['sKeyword'] : '';
        if(empty($sKeyword)){
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.please_provide_keyword"))
            );
        }

        $aData['isSearchThreadPost'] = true;
        return $this->detail($aData);
    }

    /**
     * Delete thread 
     * 
     * Request options: 
     * - iThreadId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       'message': "Thread successfully deleted.",
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function threaddelete($aData){
        $iThreadId = isset($aData['iThreadId']) ? (int) $aData['iThreadId'] : 0;
        $aThread = Phpfox::getService('forum.thread')->getActualThread($iThreadId);
        $bHasAccess = false;
        if ((int) $aThread['group_id'] > 0)
        {
            if ((Phpfox::getUserParam('forum.can_delete_own_post') && $aThread['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_delete_other_posts'))
            {
                $bHasAccess = true;
            }
        }
        else 
        {
            if ((Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'delete_post') || Phpfox::getService('user.auth')->hasAccess('forum_thread', 'thread_id', $iThreadId, 'forum.can_delete_own_post', 'forum.can_delete_other_posts')))
            {
                $bHasAccess = true;
            }
        }  

        if ($bHasAccess){
            Phpfox::getService('forum.thread.process')->delete($iThreadId);        
            if ((int) $aThread['group_id'] > 0)
            {
                $aPage = Phpfox::getService('pages.callback')->addForum($aThread['group_id']);                
            }
            return array('result' => 1
                , 'error_code' => 0
                , 'message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.thread_successfully_deleted'))
            );
        } else {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_delete_it")));
        }
    }

    /**
     * Subscribe thread 
     * 
     * Request options: 
     * - iThreadId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       'message': "Subscribe successfully",
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function threadsubscribe($aData){
        $iThreadId = isset($aData['iThreadId']) ? (int) $aData['iThreadId'] : 0;
        Phpfox::getService('forum.subscribe.process')->add($iThreadId, Phpfox::getUserId());           

        return array('result' => 1
            , 'error_code' => 0
            , 'message'=>html_entity_decode(Phpfox::getPhrase("mfox.subscribe_successfully"))
        );
    }

    /**
     * Un-subscribe thread 
     * 
     * Request options: 
     * - iThreadId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       'message': "Unsubscribe successfully",
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function threadunsubscribe($aData){
        $iThreadId = isset($aData['iThreadId']) ? (int) $aData['iThreadId'] : 0;
        Phpfox::getService('forum.subscribe.process')->delete($iThreadId, Phpfox::getUserId());

        return array('result' => 1
            , 'error_code' => 0
            , 'message'=>html_entity_decode(Phpfox::getPhrase("mfox.unsubscribe_successfully"))
        );
    }

    /**
     * Delete post on thread 
     * 
     * Request options: 
     * - iPostId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       'message': "Post successfully deleted.",
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function postdelete($aData){
        $iPostId = isset($aData['iPostId']) ? (int) $aData['iPostId'] : 0;

        $aPost = Phpfox::getService('forum.post')->getPost($iPostId);
        if (!isset($aPost['post_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.post_has_been_deleted_or_you_do_not_have_permission_to_delete_it")));
        }

        $aThread = Phpfox::getService('forum.thread')->getForEdit($aPost['thread_id']);
        if (!isset($aThread['thread_id']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.thread_has_been_deleted_or_you_do_not_have_permission_to_delete_it")));
        }

        $bCanDelete = false;
        if ((Phpfox::getUserParam('forum.can_delete_own_post') && $aPost['user_id'] == Phpfox::getUserId()) 
            || Phpfox::getUserParam('forum.can_delete_other_posts') 
            || Phpfox::getService('forum.moderate')->hasAccess($aPost['forum_id'], 'delete_post') 
            || (!empty($aThread['group_id']) && Phpfox::getService('pages')->isAdmin($aThread['group_id']))
            )
        {
            $bCanDelete = true;
        }

        if($bCanDelete == false){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_delete_it")));
        }

        $bHasAccess = false;
        if ((int) $aPost['group_id'] > 0)
        {
            if (Phpfox::getService('pages')->isAdmin($aPost['group_id']))
            {
                $bHasAccess = true;
            }
        }
        else 
        {       
            if ((Phpfox::getService('forum.moderate')->hasAccess($aPost['forum_id'], 'delete_post') || Phpfox::getService('user.auth')->hasAccess('forum_post', 'post_id', $iPostId, 'forum.can_delete_own_post', 'forum.can_delete_other_posts')))
            {
                $bHasAccess = true;
            }
        }       
                
        if ($bHasAccess && Phpfox::getService('forum.post.process')->delete($iPostId))
        {            
            return array('result' => 1, 'error_code' => 0, 'message'=>html_entity_decode(Phpfox::getPhrase("mfox.post_successfully_deleted")));
        } else {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_delete_it")));
        }
    }

   /**
    * @ignore 
    */
    public function canView($iItemId = null){
        if (!Phpfox::getUserParam('forum.can_view_forum'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_forum")));
        }
    }

    /**
     * Get content of post and put as quote on forum 
     * 
     * Request options: 
     * - iThreadId:              integer, required
     * - iPostId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       "bIsSubscribed": true, // status of subscribed of thread
     * <br/>       "sQuote": "some text",
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function threadformreply($aData){
        $iThreadId = isset($aData['iThreadId']) ? (int) $aData['iThreadId'] : 0;        
        if (!Phpfox::getUserParam('forum.can_view_forum'))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_forum"))
            );
        }

        $aThread = Phpfox::getService('forum.thread')->getActualThread($iThreadId);
        if ( (!isset($aThread['thread_id'])))
        {           
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_thread_you_are_looking_for_cannot_be_found"))
            );
        }

        $iPostId = isset($aData['iPostId']) ? (int) $aData['iPostId'] : 0;  
        $sQuote = '';      
        if($iPostId > 0){
            $sQuote = Phpfox::getService('forum.post')->getQuotes($aThread['thread_id'], $iPostId . '');     
        }
        return array(
            'error_code' => 0,
            'bIsSubscribed' => ((int)$aThread['is_subscribed'] > 0 ? true : false),
            'sQuote' => $sQuote,
        );
    }

    /**
     * Edit thread
     * 
     * Request options: 
     * - iForumId:              integer, required
     * - iThreadId:              integer, required
     * - sTitle:              string, required
     * - sText:              string, required
     * - sAttachment:                string, list of attachment id, example: '9,6,7,'
     * - iIsSubscribed:              integer
     * - iPollId:                    integer
     * - sTypeId:                    string, value is thread/sticky/announcement
     * - sTopic:                    string
     * - iAnnouncementForumId:       integer
     * - iIsClosed:                  integer
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       'message': "Your thread has been edited"
     * <br/>       "iThreadId": "1",
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function threadedit($aData){
        // validate
        $iForumId = isset($aData['iForumId']) ? (int) $aData['iForumId'] : 0;
        $iThreadId = isset($aData['iThreadId']) ? (int)$aData['iThreadId'] : '';
        $sTitle = isset($aData['sTitle']) ? $aData['sTitle'] : '';
        $sText = isset($aData['sText']) ? $aData['sText'] : '';
        if(strlen(trim($sTitle)) == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.provide_title_for_your_thread")));
        }
        if(strlen(trim($sText)) == 0){
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.provide_some_text")));
        }
        
        $bCanEditPersonalData = true;
        $aCallback = false;
        $aParentModule = array(
            'module_id' => isset($aData['sModule']) ? $aData['sModule'] : '',
            'item_id' => isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0
        );
        $sModule = null;
        $iItemId = null;
        if($aParentModule['item_id'] == 0){
            $aParentModule = null;
        } else {
            $sModule = $aParentModule['module_id'];
            $iItemId = $aParentModule['item_id'];
        }
        if ($sModule !== null
            && Phpfox::isModule($sModule) 
            && $iItemId !== null
            && Phpfox::hasCallback($sModule, 'addForum')
        ){
            $aCallback = Phpfox::callback($sModule . '.addForum', $iItemId);            
            if ($sModule == 'pages' && !Phpfox::getService('pages')->hasPerm($iItemId, 'forum.share_forum'))
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.unable_to_view_this_item_due_to_privacy_settings'))
                );
            }           
        }

        $iId = $iForumId;
        $aAccess = Phpfox::getService('forum')->getUserGroupAccess($iId, Phpfox::getUserBy('user_group_id'));
        if ($aAccess['can_view_thread_content']['value'] != true)
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.unable_to_view_this_item_due_to_privacy_settings'))
            );
        }

        $bIsEdit = false;
        $iEditId = $iThreadId;
        // support with "thread"
        $aThread = Phpfox::getService('forum.thread')->getForEdit($iEditId);
        if (!isset($aThread['thread_id']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.not_a_valid_thread'))
            );
        }
        if ((Phpfox::getUserParam('forum.can_edit_own_post') && $aThread['user_id'] == Phpfox::getUserId()) 
            || Phpfox::getUserParam('forum.can_edit_other_posts') 
            || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'edit_post'))
        {
            $bIsEdit = true;
            if (Phpfox::getUserParam('forum.can_edit_other_posts') && Phpfox::getUserId() != $aThread['user_id'])
            {
                $bCanEditPersonalData = false;
            }                   
            
            $iId = $aThread['forum_id'];

            if (Phpfox::isModule('tag'))
            {
                $aThread['tag_list'] = Phpfox::getService('tag')->getForEdit('forum', $aThread['thread_id']);
            }
        } else {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.insufficient_permission_to_edit_this_thread'))
            );            
        }

        if ($aCallback === false)
        {
            $aForum = Phpfox::getService('forum')
                ->id($iId)
                ->getForum();
            
            if (!isset($aForum['forum_id']))
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.not_a_valid_forum'))
                );
            }           
            
            if ($aForum['is_closed'])
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.forum_is_closed'))
                );
            }               
        }                

        $bPosted = false;
        $aVals = array(
            'attachment' => isset($aData['sAttachment']) ? $aData['sAttachment'] : '',  // example: '9,6,7,'
            'forum_id' => $iForumId,
            'title' => $sTitle,
            'text' => $sText,
            'is_subscribed' => (isset($aData['iIsSubscribed']) && (int)$aData['iIsSubscribed'] <= 0 ? 0 : 1),
            'poll_id' => (isset($aData['iPollId']) &&  (int)$aData['iPollId'] > 0 ? (int)$aData['iPollId'] : ''),
            'tag_list' => (isset($aData['sTopic']) ? $aData['sTopic'] : ''),
            'type_id' => (isset($aData['sTypeId']) ? $aData['sTypeId'] : 'thread'),
            'announcement_forum_id' => (isset($aData['iAnnouncementForumId']) &&  (int)$aData['iAnnouncementForumId'] > 0 ? (int)$aData['iAnnouncementForumId'] : $iForumId),
            'is_closed' => (isset($aData['iIsClosed']) &&  (int)$aData['iIsClosed'] > 0 ? 1 : 0),
        );

        if (isset($aVals['type_id']) && $aVals['type_id'] == 'announcement')
        {
            $bPosted = true;
        }

        if ($bIsEdit){
            $aVals['post_id'] = $aThread['start_id'];
            $aVals['was_announcement'] = $aThread['is_announcement'];
            $aVals['forum_id'] = $aThread['forum_id'];

            if ($this->__threadupdate($aThread['thread_id'], $aThread['user_id'], $aVals))
            {
                $sMessage = 'Your thread has been edited';
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'message' => $sMessage,
                    'iThreadId' => $aThread['thread_id'],
                );
            } else {
                $sMessage = 'There are some issues when processing. Please try again.';
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'message' => $sMessage,
                );
            }
        }
    }

   /**
    * @ignore 
    */
    private function __threadupdate($iId, $iUserId, $aVals){
        $oParseInput = Phpfox::getLib('parse.input');       
        $sReason = Phpfox::getService('mfox.helper.ban')->checkAutomaticBan($aVals['title'] . ' ' . $aVals['text']);
        if($sReason !== true){
            return array('result' => 0, 'error_code' => 1
                , 'error_message' => $sReason
            );          
        }

        $this->__threadcheckType($aVals);   

        $bHasAttachments = (Phpfox::getUserParam('forum.can_add_forum_attachments') && Phpfox::isModule('attachment') && !empty($aVals['attachment']) && $iUserId == Phpfox::getUserId());
        
        // If we uploaded any attachments make sure we update the 'item_id'
        if ($bHasAttachments)
        {
            Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], $iUserId, $aVals['post_id']);          
        }   

        $aUpdate = array(
            'is_closed' => ((isset($aVals['is_closed']) && Phpfox::getUserParam('forum.can_close_a_thread')) ? $aVals['is_closed'] : 0),
            'title' => $oParseInput->clean($aVals['title'], 255),
            'order_id' => (isset($aVals['order_id']) ? $aVals['order_id'] : 0)          
        );  

        if (!empty($aVals['poll_id']) && Phpfox::isModule('poll') && Phpfox::getUserParam('poll.can_create_poll'))
        {
            $aUpdate['poll_id'] = (int) $aVals['poll_id'];  
        }       

        $this->database()->update(Phpfox::getT('forum_thread'), $aUpdate, 'thread_id = ' . (int) $iId);
        
        $this->database()->update(Phpfox::getT('forum_post'), array(
                'total_attachment' => (Phpfox::isModule('attachment') ? Phpfox::getService('attachment')->getCountForItem($aVals['post_id'], 'forum') : 0),
                'title' => $oParseInput->clean($aVals['title'], 255),
                'update_time' => PHPFOX_TIME,
                'update_user' => substr(Phpfox::getUserBy('full_name'), 0, 100)
            ), 'post_id = ' . (int) $aVals['post_id']
        );

        $this->database()->update(Phpfox::getT('forum_post_text'), array(
                'text' => $oParseInput->clean($aVals['text']),
                'text_parsed' => $oParseInput->prepare($aVals['text'])
            ), 'post_id = ' . (int) $aVals['post_id']
        );

        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support') && Phpfox::getUserParam('forum.can_add_tags_on_threads'))
        {
            Phpfox::getService('tag.process')->update('forum', $iId, $iUserId, $aVals['text'], true);
        }
        else
        {
            if (Phpfox::getUserParam('forum.can_add_tags_on_threads') && Phpfox::isModule('tag') && isset($aVals['tag_list']) && !empty($aVals['tag_list']))
            {
                Phpfox::getService('tag.process')->update('forum', $iId, $iUserId, $aVals['tag_list']);
            }
        }

        if (Phpfox::isModule('feed'))
        {
            // Phpfox::getService('feed.process')->update('forum', $iId, $oParseInput->clean($aVals['title'], 255));
            
            $aThread = Phpfox::getService('forum.thread')->getForEdit($iId);
            $aPosts = $this->database()->select('post_id')
                ->from(Phpfox::getT('forum_post'))
                ->where('thread_id = ' . (int) $iId)
                ->execute('getSlaveRows');
            foreach ($aPosts as $aPost)
            {
                Phpfox::getService('feed.process')->update('forum_post', $aPost['post_id'], serialize(array('post_id' => $aPost['post_id'], 'forum_id' => $aThread['forum_id'], 'forum_url' => $aThread['forum_url'], 'thread_url' => $aThread['title_url'], 'thread_title' => $aThread['title'])));
            }
        }

        if (Phpfox::isModule('feed') && Phpfox::getParam('feed.cache_each_feed_entry'))
        {
            $this->cache()->remove(array('feeds', 'forum_' . $iId));
        }

        return true;
    }

    /**
     * Get edting form of post 
     * 
     * Request options: 
     * - iForumId:              integer, required
     * - iThreadId:              integer, required
     * - sTitle:              string, required
     * - sText:              string, required
     * - sAttachment:                string, list of attachment id, example: '9,6,7,'
     * - iIsSubscribed:              integer
     * - iPollId:                    integer
     * - sTypeId:                    string, value is thread/sticky/announcement
     * - sTopic:                    string
     * - iAnnouncementForumId:       integer
     * - iIsClosed:                  integer
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "aAttachments": [],  // see "threaddetail" method for each post item 
     * <br/>       "iForumId": "15",
     * <br/>       "sFullName": "Admin",
     * <br/>       "bIsLiked": false,
     * <br/>       "iPostId": "187",
     * <br/>       "iProfilePageId": "0",
     * <br/>       "sText": "fasdfsadfds",
     * <br/>       "iThreadId": "108",
     * <br/>       "iTimeStamp": "1405074016",
     * <br/>       "sTitle": "",
     * <br/>       "iTotalAttachment": "0",
     * <br/>       "iTotalDislike": "0",
     * <br/>       "iTotalLike": "0",
     * <br/>       "iTotalPost": "134",
     * <br/>       "iUpdateTime": "0",
     * <br/>       "iUpdateUser": null,
     * <br/>       "iUserId": "1",
     * <br/>       "sUserImage": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/user/1_50_square.jpg",
     * <br/>       "sUserName": "admin",
     * <br/>       "bCanQuote": true,
     * <br/>       "bCanLike": true,
     * <br/>       "sTextNotParsed": "fasdfsadfds"
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function postformedit($aData){
        $iPostId = isset($aData['iPostId']) ? (int)$aData['iPostId'] : '';
        $iEditId = $iPostId;
        $aPost = Phpfox::getService('forum.post')->getPost($iEditId);
        
        if (!isset($aPost['post_id']))
        {
            return array('result' => 0, 'error_code' => 1
                , 'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.not_a_valid_post'))
            );          
        }

        $bCanEditPost = (Phpfox::getUserParam('forum.can_edit_own_post') && $aPost['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_edit_other_posts') || Phpfox::getService('forum.moderate')->hasAccess($aPost['forum_id'], 'edit_post');
        if ($bCanEditPost)
        {                   
            $bIsEdit = true;
            if (Phpfox::getUserParam('forum.can_edit_other_posts') && Phpfox::getUserId() != $aPost['user_id'])
            {
                $bCanEditPersonalData = false;
            }                   
            
            $iId = $aPost['thread_id'];                             
        }
        else 
        {
            return array('result' => 0, 'error_code' => 1
                , 'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.insufficient_permission_to_edit_this_thread'))
            );          
        }

        $aThread = Phpfox::getService('forum.thread')->getForEdit($aPost['thread_id']);
        $attachmentResult = array();
        if(isset($aPost['attachments']) == true){
            foreach ($aPost['attachments'] as $key => $att) {
                if((int)$att['link_id'] > 0){
                    // link attachment
                    $type = 'link';
                    $link  = Phpfox::getService('mfox.helper.attachment')->getDetailAttachmentByType($type, (int)$att['link_id'], $att);
                    if(isset($link['link_id'])){
                        $attachmentResult[] = array('attachment_id' => $att['attachment_id'], 'type' => $type, 'data' => $link);
                    }
                } else if($att['is_image']){
                    // image attachment
                    $type = 'image';
                    $url = Phpfox::getService('mfox.helper.attachment')->getDetailAttachmentByType($type, 0, $att);
                    $attachmentResult[] = array('attachment_id' => $att['attachment_id'], 'type' => $type, 'data' => array('photo_url' => $url));
                }
            }
        }
        $aPost['attachments'] = $attachmentResult;        
        $aPost = $this->__getPostData($aPost, 'large', $aThread);       

        return $aPost;
    }

    /**
     * Get edting form of post 
     * 
     * Request options: 
     * - iPostId:              integer, required
     * - sText:              string, required
     * - sAttachment:                string, list of attachment id, example: '9,6,7,'
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       'message': "Updated successfully"
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function postedit($aData){
        $iPostId = isset($aData['iPostId']) ? (int) $aData['iPostId'] : 0;        
        $iEditId = $iPostId;
        $aPost = Phpfox::getService('forum.post')->getPost($iEditId);
        if (!isset($aPost['post_id']))
        {
            return array('result' => 0, 'error_code' => 1
                , 'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.not_a_valid_post'))
            );          
        }

        $aThread = Phpfox::getService('forum.thread')->getForEdit($aPost['thread_id']);
        $aVals = array(
            'attachment' => isset($aData['sAttachment']) ? $aData['sAttachment'] : '',  // example: '9,6,7,'
            'text' => isset($aData['sText']) ? $aData['sText'] : '',
            'thread_id' => $aThread['thread_id'], 
            'total_post' => $aThread['total_post'], 
        );
        $sTxt = $aVals['text'];
        if (Phpfox::getLib('parse.format')->isEmpty($sTxt))
        {
            return array('result' => 0, 'error_code' => 1
                , 'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.add_some_text'))
            );          
        }       

        $bHasAccess = false;
        if ((int) $aPost['group_id'] > 0)
        {
            if ((Phpfox::getUserParam('forum.can_edit_own_post') && $aPost['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_edit_other_posts'))
            {
                $bHasAccess = true;
            }
        }
        else 
        {
            if ((Phpfox::getService('forum.moderate')->hasAccess($aPost['forum_id'], 'edit_post') || Phpfox::getService('user.auth')->hasAccess('forum_post', 'post_id', $iEditId, 'forum.can_edit_own_post', 'forum.can_edit_other_posts')))
            {
                $bHasAccess = true;
            }
        }           

        if ($bHasAccess)
        {
            if (Phpfox::getService('forum.post.process')->updateText($iEditId, $sTxt, $aVals))
            {
                return array('result' => 1, 'error_code' => 0
                    , 'message'=>html_entity_decode(Phpfox::getPhrase("mfox.updated_successfully"))
                );                                      
            }
        } else {
            return array('result' => 0, 'error_code' => 1
                , 'error_message' =>  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('forum.insufficient_permission_to_edit_this_thread'))
            );                      
        }
    }
}
