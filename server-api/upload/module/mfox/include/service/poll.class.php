<?php

/**
 * Service component
 *
 * @category Mobile phpfox server api
 * @author Ly Tran <lytk@younetco.com>
 * @version $Id$
 * @copyright $Copyright$
 * @license $License$
 * @package mfox.poll
 */

/**
 * Supported Poll api
 *
 * @package mfox.poll
 * @author Ly Tran <lytk@younetco.com>
 */
class Mfox_Service_Poll extends Phpfox_Service {

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
     * @ignore
     */
    private $_sDefaultImagePollPath = '';

    /**
     * use based item service
     *
     * @ignore
     */
    public function __construct()
    {
        $this->_oReq = Mfox_Service_Request_Request::instance();
        $this->_oSearch = Mfox_Service_Search_Search::instance();
        $this->_oBrowse = Mfox_Service_Search_Browse::instance();

        $this -> _sTable = Phpfox::getT('poll');
        $this->_sDefaultImagePollPath = Phpfox::getParam('core.url_module') . 'mfox/static/image/poll-default.png';
    }

    /**
     * @ignore
     */
    public function getDefaultImagePollPath(){
        return $this->_sDefaultImagePollPath;
    }

    /**
     * Get/search list of polls.
     *
     * Request options: 
     * - sView:          string
     * - sSearch:          string
     * - sOrder:          string
     * - iPage:          integer, starting from 1
     *
     * Response data contains: 
     * <code>
     * <br/>  [
     * <br/>       {
     * <br/>            "iPollId": "229194",
     * <br/>            "sQuestion": "bbbbbbbbbbb?",
     * <br/>            "iPrivacy": "0",
     * <br/>            "iPrivacyComment": "0",
     * <br/>            "iUserId": "1",
     * <br/>            "sModuleId": "forum",
     * <br/>            "sItemId": "0",
     * <br/>            "iViewId": "0",
     * <br/>            "iTimeStamp": "1406543489",
     * <br/>            "iTotalComment": "0",
     * <br/>            "iTotalLike": "0",
     * <br/>            "iTotalDislike": "0",
     * <br/>            "iTotalView": "0",
     * <br/>            "bIsHideVote": false,
     * <br/>            "bIsRandomize": false,
     * <br/>            "iAnswerId": null,
     * <br/>            "sPollImage": "http://product-dev.younetco.com/lytk/phpfox376/module/mfox/static/image/poll-default.png",
     * <br/>            "bIsLiked": false,
     * <br/>            "bIsVoted": false,
     * <br/>            "sUserImage": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/user/1_50_square.jpg",
     * <br/>            "sUserName": "admin",
     * <br/>            "sFullname": "Admin",
     * <br/>            "iTotalVotes": 0,
     * <br/>            "bHasImage": false,
     * <br/>            "bCanDislike": true,
     * <br/>            "bCanComment": true,
     * <br/>            "aDislikes": [],
     * <br/>            "aLikes": [],
     * <br/>            "bIsDisliked": false,
     * <br/>            "bIsApproved": true
     * <br/>       }
     * <br/>  ]
     * </code>
     *
     * @param   array   $aData
     * @return  array
     */
    public function fetch($aData) {

        extract($aData);

        return $this -> _getPolls($aData);

    }

    /**
     * @ignore
     *
     * @see
     * get item by aData
     */
    public function _getPolls($aData)
    {
        $this->_oReq->set(array(
            'view' => !empty($aData['sView']) ? $aData['sView'] : '',
            'page' => !empty($aData['iPage']) ? (int) $aData['iPage'] : 1,
            'show' => !empty($aData['iAmountOfPoll']) ? (int) $aData['iAmountOfPoll'] : 10,
            'category' => !empty($aData['iCategoryId']) ? (int) $aData['iCategoryId'] : null,
            'search' => !empty($aData['sSearch']) ? $aData['sSearch'] : '',
            'sort' => !empty($aData['sOrder']) ? $aData['sOrder'] : '',
            'module_id' => !empty($aData['sModule']) ? $aData['sModule'] : null,
            'item_id' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : null,
            'profile' => (!empty($aData['bIsProfile']) && $aData['bIsProfile'] == 'true') ? true : false,
            'profile_id' => !empty($aData['iProfileId']) ? (int) $aData['iProfileId'] : null,
        ));

        Phpfox::getUserParam('poll.can_access_polls', true);

        $sSuffix = '_' . Phpfox::getParam('poll.poll_max_image_pic_size');
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
            $this->_oSearch->setCondition('AND poll.question LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"');
        }

        // sort
        $sSort = '';
        switch ($this->_oReq->get('sort')) {
            case 'most_viewed':
                $sSort = 'poll.total_view DESC';
                break;
            case 'most_liked':
                $sSort = 'poll.total_like DESC';
                break;
            case 'most_discussed':
                $sSort = 'poll.total_comment DESC';
                break;
            default:
                $sSort = 'poll.time_stamp DESC';
                break;
        }

        $this->_oSearch->setSort($sSort);

        $aBrowseParams = array(
            'module_id' => 'poll',
            'alias' => 'poll',
            'field' => 'poll_id',
            'table' => Phpfox::getT('poll'),
            'hide_view' => array('pending', 'my'),
            'service' => 'mfox.poll'
        );          
        
        switch ($sView)
        {
            case 'my':
                Phpfox::isUser(true);
                $this->_oSearch->setCondition('AND poll.user_id = ' . (int) Phpfox::getUserId());
                break;
            case 'pending':
                Phpfox::isUser(true);
                Phpfox::getUserParam('poll.poll_can_moderate_polls', true);
                $this->_oSearch->setCondition('AND poll.view_id = 1');
                break;
            default:
                if ($bIsProfile === true)
                {
                    $this->_oSearch->setCondition('AND poll.item_id = 0 AND poll.user_id = ' . (int) $aUser['user_id'] . ' AND poll.view_id IN(' . ($aUser['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ') AND poll.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ')');
                }
                else 
                {
                    $this->_oSearch->setCondition('AND poll.item_id = 0 AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%)');
                }
                break;
        }
        
        $this->_oBrowse->params($aBrowseParams)->execute();
        
        $aPolls = $this->_oBrowse->getRows();

        return $aPolls;
    }

    public function processRows(&$aRows)
    {
        $aTmpRows = $aRows;
        $aRows = array();

        foreach ($aTmpRows as $aRow) {
            $aRows[] = $this->_prepare($aRow, 'small');
        }
    }
    
    public function query()
    {
        return Phpfox::getService('poll.browse')->query();     
    }
    
    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Mfox_Service_Friend::instance()->queryJoin($bNoQueryFriend))
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = poll.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());   
        }       
    }

   /**
    * @ignore 
    */
    private function _getPermission($iPollId = null) {
        $extra = array();
        if($iPollId != null){
            $aPoll = Phpfox::getService('poll')->getPollById((int) $iPollId);
            if (!empty($aPoll)){
                $bIsOwnPoll = ($aPoll['user_id'] == Phpfox::getUserId());
                
                $bCanEditTitle = (Phpfox::getUserParam('poll.can_edit_title') && ( ($bIsOwnPoll && Phpfox::getUserParam('poll.poll_can_edit_own_polls')) || (!$bIsOwnPoll && Phpfox::getUserParam('poll.poll_can_edit_others_polls'))));
                $bCanEditQuestion = (Phpfox::getUserParam('poll.can_edit_question') && ( ($bIsOwnPoll && Phpfox::getUserParam('poll.poll_can_edit_own_polls')) || (!$bIsOwnPoll && Phpfox::getUserParam('poll.poll_can_edit_others_polls'))));
                $bCanEditAnything = $bCanEditTitle || $bCanEditQuestion;
                
                $extra['bCanEditTitle'] = $bCanEditTitle;
                $extra['bCanEditQuestion'] = $bCanEditQuestion;
            }
        }
        return array_merge(array(
            'iMaxAnswer' => Phpfox::getUserparam('poll.maximum_answers_count'),
            'iMinAnswer' => 2,
            'bCanViewPollResult' => Phpfox::getUserparam('poll.view_poll_results_before_vote'),
            'bCanChangePollVote' => Phpfox::getUserParam('poll.poll_can_change_own_vote'),
            'bCanEditOwnPoll' => Phpfox::getUserParam('poll.poll_can_edit_own_polls'),
            'bCanEditOthersPoll' => Phpfox::getUserParam('poll.poll_can_edit_others_polls'),
            'bCanDeleteOwnPoll' => Phpfox::getUserParam('poll.poll_can_delete_own_polls'),
            'bCanDeleteOthersPoll' => Phpfox::getUserParam('poll.poll_can_delete_others_polls'),
            'bCanViewOwnUserPollResult' => Phpfox::getUserParam('poll.can_view_user_poll_results_own_poll'),
            'bCanViewOthersUserPollResult' => Phpfox::getUserParam('poll.can_view_user_poll_results_other_poll'),
            'bCanCreatePoll' => Phpfox::getUserParam('poll.can_create_poll'),
            'bCanEditQuestion' => Phpfox::getUserParam('poll.can_edit_question'),
            'bCanAccessPoll' => Phpfox::getUserParam('poll.can_access_polls'),
        ), $extra);
    }

    /**
     * Get configuration for adding form
     *
     * Request options: nothing
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>      "view_options": [
     * <br/>           {
     * <br/>                "sPhrase": "Everyone",
     * <br/>                "sValue": "0"
     * <br/>           },
     * <br/>           {
     * <br/>                "sPhrase": "Friends",
     * <br/>                "sValue": "1"
     * <br/>           },
     * <br/>           {
     * <br/>                "sPhrase": "Friends of Friends",
     * <br/>                "sValue": "2"
     * <br/>           },
     * <br/>           {
     * <br/>                "sPhrase": "Only Me",
     * <br/>                "sValue": "3"
     * <br/>           },
     * <br/>           {
     * <br/>                "sPhrase": "Custom",
     * <br/>                "sValue": "4"
     * <br/>           }
     * <br/>      ],
     * <br/>      "comment_options": [
     * <br/>           {
     * <br/>                "sPhrase": "Everyone",
     * <br/>                "sValue": "0"
     * <br/>           },
     * <br/>           {
     * <br/>                "sPhrase": "Friends",
     * <br/>                "sValue": "1"
     * <br/>           },
     * <br/>           {
     * <br/>                "sPhrase": "Friends of Friends",
     * <br/>                "sValue": "2"
     * <br/>           },
     * <br/>           {
     * <br/>                "sPhrase": "Only Me",
     * <br/>                "sValue": "3"
     * <br/>           }
     * <br/>      ],
     * <br/>      "perms": {
     * <br/>           "iMaxAnswer": 20,
     * <br/>           "iMinAnswer": 2,
     * <br/>           "bCanViewPollResult": true,
     * <br/>           "bCanChangePollVote": true,
     * <br/>           "bCanEditOwnPoll": true,
     * <br/>           "bCanEditOthersPoll": true,
     * <br/>           "bCanDeleteOwnPoll": true,
     * <br/>           "bCanDeleteOthersPoll": true,
     * <br/>           "bCanViewOwnUserPollResult": true,
     * <br/>           "bCanViewOthersUserPollResult": true,
     * <br/>           "bCanCreatePoll": true,
     * <br/>           "bCanEditQuestion": true,
     * <br/>           "bCanAccessPoll": true
     * <br/>      }
     * <br/>  }
     * </code>
     * 
     * @param       array   $aData
     * @return      array
     */
    public function formadd($aData) {
        $response = array(
            'view_options' => Phpfox::getService('mfox.privacy') -> privacy($aData),
            'comment_options' => Phpfox::getService('mfox.privacy') -> privacycomment($aData),
            'perms' => $this -> _getPermission(),
        );

        $iValue = Phpfox::getService('user.privacy')->getValue('poll.default_privacy_setting');
        $response['default_privacy_setting'] = Phpfox::getService('mfox.privacy')->defaultprivacy("$iValue", $response['view_options']);

        return $response;
    }

    /**
     * Get configuration for editing form
     *
     * Request options: 
     * - iPollId:          integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>  "detail": {
     * <br/>       "aAnswer": [
     * <br/>            {
     * <br/>                 "sAnswer": "fsafsfd",
     * <br/>                 "iAnswerId": "1",
     * <br/>                 "iOrdering": "1",
     * <br/>                 "iPollId": "1",
     * <br/>                 "iTotalVotes": "0",
     * <br/>                 "iVotePercentage": 0
     * <br/>            },
     * <br/>            {
     * <br/>                 "sAnswer": "fsafsfd",
     * <br/>                 "iAnswerId": "2",
     * <br/>                 "iOrdering": "2",
     * <br/>                 "iPollId": "1",
     * <br/>                 "iTotalVotes": "0",
     * <br/>                 "iVotePercentage": 0
     * <br/>            }
     * <br/>       ],
     * <br/>       "iPollId": "1",
     * <br/>       "sQuestion": "fsafsfd?",
     * <br/>       "iPrivacy": "0",
     * <br/>       "iPrivacyComment": "0",
     * <br/>       "iUserId": "1",
     * <br/>       "sModuleId": null,
     * <br/>       "sItemId": "0",
     * <br/>       "iViewId": "0",
     * <br/>       "iTimeStamp": "1398325219",
     * <br/>       "iTotalComment": "0",
     * <br/>       "iTotalLike": "1",
     * <br/>       "iTotalDislike": "0",
     * <br/>       "iTotalView": "1",
     * <br/>       "bIsHideVote": false,
     * <br/>       "bIsRandomize": false,
     * <br/>       "iAnswerId": null,
     * <br/>       "sPollImage": "http://product-dev.younetco.com/lytk/phpfox376/module/mfox/static/image/poll-default.png",
     * <br/>       "bIsLiked": false,
     * <br/>       "bIsVoted": false,
     * <br/>       "sUserImage": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/user/1_50_square.jpg",
     * <br/>       "sUserName": "admin",
     * <br/>       "sFullname": "Admin",
     * <br/>       "iPercentage": null,
     * <br/>       "iTotalVotes": 0,
     * <br/>       "bUserVotedThisPoll": false,
     * <br/>       "bHasImage": false,
     * <br/>       "bCanDislike": true,
     * <br/>       "bCanComment": true,
     * <br/>       "aDislikes": [],
     * <br/>       "aLikes": [
     * <br/>            {
     * <br/>                 "iUserId": "4",
     * <br/>                 "sDisplayName": "An Nguyen An Nguyen"
     * <br/>            }
     * <br/>       ],
     * <br/>       "bIsDisliked": false,
     * <br/>       "bIsApproved": true,
     * <br/>       "bCanViewUserResult": false,
     * <br/>       "bCanViewResult": true,
     * <br/>       "bShowResults": true
     * <br/>  },
     * <br/>      "view_options": [
     * <br/>           {
     * <br/>                "sPhrase": "Everyone",
     * <br/>                "sValue": "0"
     * <br/>           },
     * <br/>           {
     * <br/>                "sPhrase": "Friends",
     * <br/>                "sValue": "1"
     * <br/>           },
     * <br/>           {
     * <br/>                "sPhrase": "Friends of Friends",
     * <br/>                "sValue": "2"
     * <br/>           },
     * <br/>           {
     * <br/>                "sPhrase": "Only Me",
     * <br/>                "sValue": "3"
     * <br/>           },
     * <br/>           {
     * <br/>                "sPhrase": "Custom",
     * <br/>                "sValue": "4"
     * <br/>           }
     * <br/>      ],
     * <br/>      "comment_options": [
     * <br/>           {
     * <br/>                "sPhrase": "Everyone",
     * <br/>                "sValue": "0"
     * <br/>           },
     * <br/>           {
     * <br/>                "sPhrase": "Friends",
     * <br/>                "sValue": "1"
     * <br/>           },
     * <br/>           {
     * <br/>                "sPhrase": "Friends of Friends",
     * <br/>                "sValue": "2"
     * <br/>           },
     * <br/>           {
     * <br/>                "sPhrase": "Only Me",
     * <br/>                "sValue": "3"
     * <br/>           }
     * <br/>      ],
     * <br/>      "perms": {
     * <br/>           "iMaxAnswer": 20,
     * <br/>           "iMinAnswer": 2,
     * <br/>           "bCanViewPollResult": true,
     * <br/>           "bCanChangePollVote": true,
     * <br/>           "bCanEditOwnPoll": true,
     * <br/>           "bCanEditOthersPoll": true,
     * <br/>           "bCanDeleteOwnPoll": true,
     * <br/>           "bCanDeleteOthersPoll": true,
     * <br/>           "bCanViewOwnUserPollResult": true,
     * <br/>           "bCanViewOthersUserPollResult": true,
     * <br/>           "bCanCreatePoll": true,
     * <br/>           "bCanEditQuestion": true,
     * <br/>           "bCanAccessPoll": true
     * <br/>      }
     * <br/>  }
     * </code>
     * 
     * @param       array   $aData
     * @return      array
     */
    public function formedit($aData) {
        extract($aData);

        if (!isset($iPollId) || empty($iPollId))
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_params_ipollid"))
            );

        return array(
            'detail' => $this -> detail($aData),
            'view_options' => Phpfox::getService('mfox.privacy') -> privacy(array()),
            'comment_options' => Phpfox::getService('mfox.privacy') -> privacycomment(array()),
            'perms' => $this -> _getPermission($iPollId),
        );
    }

   /**
    * @ignore 
     */
    public function _prepare($aItem, $sMoreInfo = 'large') {
        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aItem, '_50_square');

        if($aItem['image_path'] == null){
            $sPollImage = $this->getDefaultImagePollPath();
        } else {
            $sPollImage = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $aItem['server_id'],
                    'path' => 'poll.url_image',
                    'file' => $aItem['image_path'],
                    'suffix' => '',
                    'return_url' => true
                        )
                );            
        }
        $bHasImage = true;
        if(empty($aItem['image_path'])){
            $bHasImage = false;
        }
        $aAnswerData = array();
        if(isset($aItem['answer'])){
            foreach($aItem['answer'] as $val){
                $aAnswerData[] = array(
                    'sAnswer' => Phpfox::getService('mfox')->decodeUtf8Compat($val['answer']),
                    'iAnswerId' => $val['answer_id'],
                    'iOrdering' => $val['ordering'],
                    'iPollId' => $val['poll_id'],
                    'iTotalVotes' => $val['total_votes'],
                    'iVotePercentage' => $val['vote_percentage'],
                );
            }
        }

        if(!isset($aItem['total_votes'])){
            $iTotalVotes = 0;
            $aAnswers = $this->database()->select('pa.*')
                ->from(Phpfox::getT('poll_answer'), 'pa')
                ->where('pa.poll_id = ' . (int) $aItem['poll_id'])
                ->order('pa.ordering ASC')
                ->execute('getSlaveRows');
            foreach ($aAnswers as $aAnswer)
            {
                $iTotalVotes += $aAnswer['total_votes'];
            }
            $aItem['total_votes'] = $iTotalVotes;
        }

        $bCanDislike = Phpfox::getService('mfox.like')->isTypeSupportDislike('poll');
        $bCanComment = $this->canPostComment($aItem['poll_id']);
        if($bCanComment === null){
            $bCanComment = true;
        } else {
            $bCanComment = false;
        }

       // for dislike 
        $aUserDislike = array();
        $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser(
            'poll'
            , $aItem['poll_id']
            , $bGetCount = false);
        $bIsDisliked = false;
        foreach($aDislike as $dislike){
            if(Phpfox::getUserId() ==  $dislike['user_id']){
                $bIsDisliked = true;
            }
            $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => Phpfox::getService('mfox')->decodeUtf8Compat($dislike['full_name']));
        }

        $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser(
            'poll'
            , $aItem['poll_id']
            , false
            , 999999
        );
        $aUserLike = array();
        foreach($aLike['likes'] as $like){
            $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => Phpfox::getService('mfox')->decodeUtf8Compat($like['full_name']));
        }
		
		$bCanChangeOwnVote =  Phpfox::getUserParam('poll.poll_can_change_own_vote');
		$bIsVoted = Phpfox::getService('poll.process')->hasUserVoted(Phpfox::getUserId(),(int)$aItem['poll_id']);
		$bModerated = Phpfox::getService('poll')->isModerated((int)$aItem['poll_id']);
		$bCanVote =  false;
		$bCanVoteOwnPoll  =  Phpfox::getUserParam('poll.can_vote_in_own_poll');
		
		$bCanVote =  0;
		
		$bHideVote =  $aItem['hide_vote']?1:0;
		
		$bIsOwner = $aItem['user_id'] == Phpfox::getUserId();
		$bCanViewUserResult = 0;
		$bCanViewResult =  0;
		
		if ($bIsOwner && !$bCanVoteOwnPoll){
			$bCanVote =  0;	
		}else if ($bIsVoted && $bCanChangeOwnVote){
			$bCanVote =  1;
		}else if (!$bIsVoted){
			$bCanVote = 1;
		}
		
		
		if ($bIsOwner && Phpfox::getUserParam('poll.can_view_user_poll_results_own_poll') ||
				(!$bIsOwner && !$bHideVote && Phpfox::getUserParam('poll.can_view_user_poll_results_other_poll'))
				|| Phpfox::getUserParam('poll.can_view_hidden_poll_votes')
			)
		{
			
			$bCanViewUserResult = 1;
		}
		
		if ((!$bIsVoted && Phpfox::getUserParam('poll.view_poll_results_before_vote')) 
			|| ($bIsVoted && Phpfox::getUserParam('poll.view_poll_results_after_vote'))){
				$bCanViewResult = 1;
			}
		
		$bCanEdit = 0;
		$bCanEditQuestion = 0;
		$bCanDelete = 0;
	
		$bIsModerator =  Phpfox::getUserParam('poll.poll_can_moderate_polls');
		
		
		if (
			($bIsOwner && Phpfox::getUserParam('poll.poll_can_edit_own_polls'))
			|| (!$bIsOwner && Phpfox::getUserParam('poll.poll_can_edit_others_polls'))
		){
			$bCanEdit =  1;
		}
		
		if($bCanEdit){
			$bCanEditQuestion = Phpfox::getUserParam('poll.can_edit_question');
		}
		
		if (
			($bIsModerator)
			|| ($bIsOwner && Phpfox::getUserParam('poll.poll_can_delete_own_polls'))
			|| (!$bIsOwner && Phpfox::getUserParam('poll.poll_can_delete_others_polls'))
		){
			$bCanDelete =  1;
		} 

        switch ($sMoreInfo) {
            case 'large':
            case 'medium':
                return array(
                    'aAnswer' => $aAnswerData,
                    'iPollId'=>$aItem['poll_id'],
                    'sQuestion'=> Phpfox::getService('mfox')->decodeUtf8Compat($aItem['question']),
                    'iPrivacy'=> $aItem['privacy'],
                    'iPrivacyComment'=> $aItem['privacy_comment'],
                    'iUserId'=> $aItem['user_id'],
                    'sModuleId'=> $aItem['module_id'],
                    'sItemId'=> $aItem['item_id'],
                    'iViewId'=> $aItem['view_id'],
                    'iTimeStamp'=> $aItem['time_stamp'],
                    'iTotalComment'=> $aItem['total_comment'],
                    'iTotalLike'=> $aItem['total_like'],
                    'iTotalDislike'=> $aItem['total_dislike'],
                    'iTotalView'=> $aItem['total_view'],
                    'bIsHideVote'=>$bHideVote,
                    'bIsRandomize'=> ((int)$aItem['randomize'] > 0 ? true : false),
                    'iAnswerId'=> $aItem['answer_id'],
                    // 'iAnswerId'=> $aItem['background'],
                    // 'iAnswerId'=> $aItem['border'],
                    'sPollImage'=> $sPollImage,
                    'bIsLiked'=> ((int)$aItem['is_liked'] > 0 ? true : false),
                    'bIsVoted'=> $bIsVoted,
                    'sUserImage' => $sUserImage,
                    'sUserName' => $aItem['user_name'],
                    'sFullname' => Phpfox::getService('mfox')->decodeUtf8Compat($aItem['full_name']),
                    'iPercentage' => $aItem['percentage'],
                    'iTotalVotes' => $aItem['total_votes'],
                    'bUserVotedThisPoll' => $bIsVoted,
                    'bHasImage' => $bHasImage,
                    'bCanDislike' => $bCanDislike,
                    'bCanComment' => $bCanComment,
                    'aDislikes' => $aUserDislike,
                    'aLikes' => $aUserLike,
                    'bIsDisliked' => $bIsDisliked,
                    'bIsApproved'=> ((int)$aItem['view_id'] > 0 ? false : true),
					'bCanVote'=> $bCanVote,
                    'bCanViewUserResult' => $bCanViewUserResult,
                    'bCanViewResult' => $bCanViewResult,
                    'bCanEdit'=>$bCanEdit,
                    'bCanDelete'=>$bCanDelete,
                    ); 
                break;

            case 'small':
                return array(
                    'iPollId'=>$aItem['poll_id'],
                    'sQuestion'=> Phpfox::getService('mfox')->decodeUtf8Compat($aItem['question']),
                    'iPrivacy'=> $aItem['privacy'],
                    'iPrivacyComment'=> $aItem['privacy_comment'],
                    'iUserId'=> $aItem['user_id'],
                    'sModuleId'=> $aItem['module_id'],
                    'sItemId'=> $aItem['item_id'],
                    'iViewId'=> $aItem['view_id'],
                    'iTimeStamp'=> $aItem['time_stamp'],
                    'iTotalComment'=> $aItem['total_comment'],
                    'iTotalLike'=> $aItem['total_like'],
                    'iTotalDislike'=> $aItem['total_dislike'],
                    'iTotalView'=> $aItem['total_view'],
                    'bIsHideVote'=> ((int)$aItem['hide_vote'] > 0 ? true : false),
                    'bIsRandomize'=> ((int)$aItem['randomize'] > 0 ? true : false),
                    'iAnswerId'=> $aItem['answer_id'],
                    // 'iAnswerId'=> $aItem['background'],
                    // 'iAnswerId'=> $aItem['border'],
                    'sPollImage'=> $sPollImage,
                    'bIsLiked'=> ((int)$aItem['is_liked'] > 0 ? true : false),
                    'bIsVoted'=> ((int)$aItem['voted'] > 0 ? true : false),
                    'sUserImage' => $sUserImage,
                    'sUserName' => $aItem['user_name'],
                    'sFullname' => Phpfox::getService('mfox')->decodeUtf8Compat($aItem['full_name']),
                    'iTotalVotes' => $aItem['total_votes'],
                    'bHasImage' => $bHasImage,
                    'bCanDislike' => $bCanDislike,
                    'bCanComment' => $bCanComment,
                    'aDislikes' => $aUserDislike,
                    'aLikes' => $aUserLike,
                    'bIsDisliked' => $bIsDisliked,
                    'bIsApproved'=> ((int)$aItem['view_id'] > 0 ? false : true),
                    'bCanEdit'=>$bCanEdit,
                    'bCanDelete'=>$bCanDelete,
                    ); 
                break;

            default:
                break;
        }
    }

   /**
    * @ignore 
     */
    public function categories($aData) {

    }

    /**
     * Vote poll
     * Request options: 
     * - iPollId:          integer, required
     * - iAnswerId:          integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "aAnswer": [
     * <br/>            {
     * <br/>                 "sAnswer": "fsafsfd",
     * <br/>                 "iAnswerId": "1",
     * <br/>                 "iOrdering": "1",
     * <br/>                 "iPollId": "1",
     * <br/>                 "iTotalVotes": "0",
     * <br/>                 "iVotePercentage": 0
     * <br/>            },
     * <br/>            {
     * <br/>                 "sAnswer": "fsafsfd",
     * <br/>                 "iAnswerId": "2",
     * <br/>                 "iOrdering": "2",
     * <br/>                 "iPollId": "1",
     * <br/>                 "iTotalVotes": "0",
     * <br/>                 "iVotePercentage": 0
     * <br/>            }
     * <br/>       ],
     * <br/>       "iPollId": "1",
     * <br/>       "sQuestion": "fsafsfd?",
     * <br/>       "iPrivacy": "0",
     * <br/>       "iPrivacyComment": "0",
     * <br/>       "iUserId": "1",
     * <br/>       "sModuleId": null,
     * <br/>       "sItemId": "0",
     * <br/>       "iViewId": "0",
     * <br/>       "iTimeStamp": "1398325219",
     * <br/>       "iTotalComment": "0",
     * <br/>       "iTotalLike": "1",
     * <br/>       "iTotalDislike": "0",
     * <br/>       "iTotalView": "1",
     * <br/>       "bIsHideVote": false,
     * <br/>       "bIsRandomize": false,
     * <br/>       "iAnswerId": null,
     * <br/>       "sPollImage": "http://product-dev.younetco.com/lytk/phpfox376/module/mfox/static/image/poll-default.png",
     * <br/>       "bIsLiked": false,
     * <br/>       "bIsVoted": false,
     * <br/>       "sUserImage": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/user/1_50_square.jpg",
     * <br/>       "sUserName": "admin",
     * <br/>       "sFullname": "Admin",
     * <br/>       "iPercentage": null,
     * <br/>       "iTotalVotes": 0,
     * <br/>       "bUserVotedThisPoll": false,
     * <br/>       "bHasImage": false,
     * <br/>       "bCanDislike": true,
     * <br/>       "bCanComment": true,
     * <br/>       "aDislikes": [],
     * <br/>       "aLikes": [
     * <br/>            {
     * <br/>                 "iUserId": "4",
     * <br/>                 "sDisplayName": "An Nguyen An Nguyen"
     * <br/>            }
     * <br/>       ],
     * <br/>       "bIsDisliked": false,
     * <br/>       "bIsApproved": true,
     * <br/>       "bCanViewUserResult": false,
     * <br/>       "bCanViewResult": true,
     * <br/>       "bShowResults": true
     * <br/>  }
     * </code>
     * 
     * @param   array  $aData
     * @return  array
     */
    public function vote($aData) {
        $iPollId = isset($aData['iPollId']) ? (int)$aData['iPollId'] : '';
        $iAnswerId = isset($aData['iAnswerId']) ? (int)$aData['iAnswerId'] : '';
        $aVals = array(
            'poll_id' => $iPollId, 
            'answer' => $iAnswerId, 
        );

        // check if the poll is being moderated
        $bModerated = Phpfox::getService('poll')->isModerated((int)$aVals['poll_id']);
        if ($bModerated)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('poll.this_poll_is_being_moderated_and_no_votes_can_be_added_yet'));
        }
        else{
            if (Phpfox::getService('poll.process')->addVote(Phpfox::getUserId(), (int) $aVals['poll_id'], (int) $aVals['answer']))
            {
                return $this->detail($aData);
            } else {
                return array('result' => 0, 'error_code' => 1, 'error_message' => implode(' ', Phpfox_Error::get()));                
            }
        }
    }

    /**
     * Get list of voter
     * 
     * Request options: 
     * - iPollId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>   [
     * <br/>        {
     * <br/>             "sAnswer": "sadfdsa",
     * <br/>             "iPollId": "159145",
     * <br/>             "iTimeStamp": "1404117933",
     * <br/>             "iUserId": "1",
     * <br/>             "sFullName": "Admin",
     * <br/>             "sUserName": "admin",
     * <br/>             "sUserImage": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/user/1_50_square.jpg"
     * <br/>        }
     * <br/>   ]
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function voter($aData) {
        $iPollId = isset($aData['iPollId']) ? (int)$aData['iPollId'] : '';
        $aVotes = Phpfox::getService('poll')->getVotes($iPollId);
        $result = array();
        foreach ($aVotes as $key => $aItem) {
            $result[] = $this->__getVoterData($aItem, 'large');
        }
        return $result;
    }

   /**
    * @ignore 
     */
    private function __getVoterData($aItem, $sMoreInfo = 'large'){
        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aItem, '_50_square');
        switch ($sMoreInfo) {
            case 'large':
            case 'medium':
            case 'small':
                return array(
                        'sAnswer' => Phpfox::getService('mfox')->decodeUtf8Compat($aItem['answer']),
                        'iPollId' => $aItem['poll_id'],
                        'iTimeStamp' => $aItem['time_stamp'],
                        'iUserId' => $aItem['user_id'],
                        'sFullName' => Phpfox::getService('mfox')->decodeUtf8Compat($aItem['full_name']),
                        'sUserName' => $aItem['user_name'],
                        'sUserImage' => $sUserImage,
                    ); 
                break;

            default:
                break;
        }        
    }

   /**
    * @ignore 
     */
    public function result($aData) {
    }

    /**
     * Detail of poll
     * 
     * Request options: 
     * - iPollId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "aAnswer": [
     * <br/>            {
     * <br/>                 "sAnswer": "fsafsfd",
     * <br/>                 "iAnswerId": "1",
     * <br/>                 "iOrdering": "1",
     * <br/>                 "iPollId": "1",
     * <br/>                 "iTotalVotes": "0",
     * <br/>                 "iVotePercentage": 0
     * <br/>            },
     * <br/>            {
     * <br/>                 "sAnswer": "fsafsfd",
     * <br/>                 "iAnswerId": "2",
     * <br/>                 "iOrdering": "2",
     * <br/>                 "iPollId": "1",
     * <br/>                 "iTotalVotes": "0",
     * <br/>                 "iVotePercentage": 0
     * <br/>            }
     * <br/>       ],
     * <br/>       "iPollId": "1",
     * <br/>       "sQuestion": "fsafsfd?",
     * <br/>       "iPrivacy": "0",
     * <br/>       "iPrivacyComment": "0",
     * <br/>       "iUserId": "1",
     * <br/>       "sModuleId": null,
     * <br/>       "sItemId": "0",
     * <br/>       "iViewId": "0",
     * <br/>       "iTimeStamp": "1398325219",
     * <br/>       "iTotalComment": "0",
     * <br/>       "iTotalLike": "1",
     * <br/>       "iTotalDislike": "0",
     * <br/>       "iTotalView": "1",
     * <br/>       "bIsHideVote": false,
     * <br/>       "bIsRandomize": false,
     * <br/>       "iAnswerId": null,
     * <br/>       "sPollImage": "http://product-dev.younetco.com/lytk/phpfox376/module/mfox/static/image/poll-default.png",
     * <br/>       "bIsLiked": false,
     * <br/>       "bIsVoted": false,
     * <br/>       "sUserImage": "http://product-dev.younetco.com/lytk/phpfox376/file/pic/user/1_50_square.jpg",
     * <br/>       "sUserName": "admin",
     * <br/>       "sFullname": "Admin",
     * <br/>       "iPercentage": null,
     * <br/>       "iTotalVotes": 0,
     * <br/>       "bUserVotedThisPoll": false,
     * <br/>       "bHasImage": false,
     * <br/>       "bCanDislike": true,
     * <br/>       "bCanComment": true,
     * <br/>       "aDislikes": [],
     * <br/>       "aLikes": [
     * <br/>            {
     * <br/>                 "iUserId": "4",
     * <br/>                 "sDisplayName": "An Nguyen An Nguyen"
     * <br/>            }
     * <br/>       ],
     * <br/>       "bIsDisliked": false,
     * <br/>       "bIsApproved": true,
     * <br/>       "bCanViewUserResult": false,
     * <br/>       "bCanViewResult": true,
     * <br/>       "bShowResults": true
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */
    public function detail($aData){
        $iPollId = isset($aData['iPollId']) ? (int) $aData['iPollId'] : 0;
        if (!Phpfox::getUserParam('poll.can_access_polls'))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_polls"))
            );
        }

        $sSuffix = '_' . Phpfox::getParam('poll.poll_max_image_pic_size');
        $iPage = 0;
        $iPageSize = 10;
        $iPoll = $iPollId;
        if (Phpfox::isModule('notification') && Phpfox::isUser())
        {
            Phpfox::getService('notification.process')->delete('comment_poll', $iPoll, Phpfox::getUserId());
            Phpfox::getService('notification.process')->delete('poll_like', $iPoll, Phpfox::getUserId());
        }               

        // we need to load one poll
        $aPoll = Phpfox::getService('poll')->getPollByUrl($iPoll, $iPage, $iPageSize, true);
        if ($aPoll === false)
        {           
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_a_valid_poll"))
            );
        }
        if (Phpfox::getUserId() == $aPoll['user_id'] && Phpfox::isModule('notification'))
        {
            Phpfox::getService('notification.process')->delete('poll_approved', $iPoll, Phpfox::getUserId());
        }               
        if (!isset($aPoll['is_friend']))
        {
            $aPoll['is_friend'] = 0;
        }

        $bCanView = false;
        if(Phpfox::getService('privacy')->check('poll', $aPoll['poll_id'], $aPoll['user_id'], $aPoll['privacy'], $aPoll['is_friend'], $bReturn = true)){
            $bCanView = true;
        }
        if($bCanView == false){
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time')
            );
        }

        // set if we can show the poll results
        // is guest the owner of the poll
        $bIsOwner = $aPoll['user_id'] == Phpfox::getUserId();
        $bShowResults = false;
        if ($bIsOwner && Phpfox::getUserParam('poll.can_view_user_poll_results_own_poll') ||
            (!$bIsOwner && Phpfox::getUserParam('poll.can_view_user_poll_results_other_poll'))
        )
        {
            $bShowResults = true;
        }

        if ($aPoll['view_id'] == 1)
        {
            if ((!Phpfox::getUserParam('poll.poll_can_moderate_polls') && $aPoll['user_id'] != Phpfox::getUserId()))
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getPhrase('poll.unable_to_view_this_poll')
                );
            }
            $sModerate = false;
            if ($sModerate)
            {
                if (!Phpfox::getUserParam('poll.poll_can_moderate_polls'))
                {
                    return array(
                        'result' => 0,
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_polls"))
                    );
                }
                switch ($sModerate)
                {
                    case 'approve':
                        if (Phpfox::getService('poll.process')->moderatePoll($aPoll['poll_id'], 0))
                        {
                            // $this->url()->send($aUser['user_name'], array('poll', $aPoll['question_url']),  Phpfox::getPhrase('poll.poll_successfully_approved'));
                        }
                        break;
                    default:
                        break;
                }
            }
        }

        // Track users
        if (Phpfox::isModule('track') && Phpfox::isUser() && (Phpfox::getUserId() != $aPoll['user_id']) && !$aPoll['poll_is_viewed'] && !Phpfox::getUserBy('is_invisible'))
        {
            Phpfox::getService('track.process')->add('poll', $aPoll['poll_id']);
            Phpfox::getService('poll.process')->updateView($aPoll['poll_id']);
        }
        if (Phpfox::isUser() && Phpfox::isModule('track') && Phpfox::getUserId() != $aPoll['user_id'] && $aPoll['poll_is_viewed'] && !Phpfox::getUserBy('is_invisible'))
        {
            Phpfox::getService('track.process')->update('poll_track', $aPoll['poll_id']);   
        }   

        // check editing permissions        
        $aPoll['bCanEdit'] = Phpfox::getService('poll')->bCanEdit($aPoll['user_id']);

        $aPollData = $this->_prepare($aPoll, 'large');

        return $aPollData;
    }

    /**
     * Get permissions in Poll module basing on viewer 
     * 
     * Request options: nothing
     * 
     * Response data contains: 
     * <code>
     * <br/>   {
     * <br/>        "iMaxAnswer": 20,
     * <br/>        "iMinAnswer": 2,
     * <br/>        "bCanViewPollResult": true,
     * <br/>        "bCanChangePollVote": true,
     * <br/>        "bCanEditOwnPoll": true,
     * <br/>        "bCanEditOthersPoll": true,
     * <br/>        "bCanDeleteOwnPoll": true,
     * <br/>        "bCanDeleteOthersPoll": true,
     * <br/>        "bCanViewOwnUserPollResult": true,
     * <br/>        "bCanViewOthersUserPollResult": true,
     * <br/>        "bCanCreatePoll": true,
     * <br/>        "bCanEditQuestion": true,
     * <br/>        "bCanAccessPoll": true
     * <br/>   }
     * </code>
     * 
     * @param array $aData
     * @return array
     */    
    public function perms($aData){
        return $this -> _getPermission();
    }

    /**
     * Delete poll
     * 
     * Request options: 
     * - iPollId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       'message': "Poll successfully deleted."
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */    
    public function delete($aData){
        $iDeleteId = isset($aData['iPollId']) ? (int) $aData['iPollId'] : 0;
        if ($iDeleteId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.poll_id_is_not_valid")));
        }

        if (Phpfox::getService('user.auth')->hasAccess('poll', 'poll_id', $iDeleteId, 'poll.poll_can_delete_own_polls', 'poll.poll_can_delete_others_polls') 
            && Phpfox::getService('poll.process')->moderatePoll($iDeleteId, 2)){
            return array('result' => 1
                , 'error_code' => 0
                , 'message' =>  Phpfox::getPhrase('poll.poll_successfully_deleted')
            );
        }

        return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.poll_has_been_deleted_or_you_do_not_have_permission_to_delete_it")));
    }

    /**
     * Add new poll
     * 
     * Request options: 
     * - sQuestion:              string, required
     * - aAnswer:              string (json encode from client), required
     * - iHideVote:              integer
     * - iPrivacy:              integer
     * - iPrivacyComment:              integer
     * - iRandomize:              integer
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       "iPollId": 229194,
     * <br/>       'message': "Your poll has been added."
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */    
    public function create($aData, $bIsEditPoll = false){
        if (!Phpfox::getUserParam('poll.can_create_poll'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_create_new_poll")));
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
        if($bIsEditPoll){
            $aAnswerData = ($aData['aAnswer']);
            $aAnswer = array();
            foreach ($aAnswerData as $key => $value) {
                $value = (array)$value;
                $aAnswer[] = array('answer' => $value['answer'], 'answer_id' => $value['answer_id']);
            }            
        } else {
            $aAnswerData = ($aData['aAnswer']);
            $aAnswer = array();
            foreach ($aAnswerData as $key => $value) {
                $aAnswer[] = array('answer' => $value);
            }            
        }

        // minimum answers
        $iMinAnswers = 2;
        $iMaxAnswers = (int)Phpfox::getUserParam('poll.maximum_answers_count');
        $iTotalDefaultAnswers = 4;
        $bIsEdit = false;

        $aVals = array(
            'question' => $aData['sQuestion'], 
            'answer' => $aAnswer, 
            'privacy' => isset($aData['iPrivacy']) ? $aData['iPrivacy'] : 0, 
            'privacy_comment' => isset($aData['iPrivacyComment']) ? $aData['iPrivacyComment'] : 0, 
            'hide_vote' => (isset($aData['iHideVote']) &&  (int)$aData['iHideVote'] > 0 ? 1 : 0), 
            'randomize' => (isset($aData['iRandomize']) &&  (int)$aData['iRandomize'] > 0 ? 1 : 0), 
        );

        $iPollId = isset($aData['iPollId']) ? (int)$aData['iPollId'] : '';
        if($iPollId > 0){
            $aPoll = Phpfox::getService('poll')->getPollById((int) $iPollId);
            if (!empty($aPoll)){
                $bIsOwnPoll = ($aPoll['user_id'] == Phpfox::getUserId());
                
                $bCanEditTitle = (Phpfox::getUserParam('poll.can_edit_title') && ( ($bIsOwnPoll && Phpfox::getUserParam('poll.poll_can_edit_own_polls')) || (!$bIsOwnPoll && Phpfox::getUserParam('poll.poll_can_edit_others_polls'))));
                $bCanEditQuestion = (Phpfox::getUserParam('poll.can_edit_question') && ( ($bIsOwnPoll && Phpfox::getUserParam('poll.poll_can_edit_own_polls')) || (!$bIsOwnPoll && Phpfox::getUserParam('poll.poll_can_edit_others_polls'))));
                $bCanEditAnything = $bCanEditTitle || $bCanEditQuestion;
                
                if ($bCanEditAnything &&
                    ($bIsOwnPoll && Phpfox::getUserParam('poll.poll_can_edit_own_polls') ||
                    (!$bIsOwnPoll && Phpfox::getUserId('poll.poll_can_edit_others_polls'))))
                {
                    $bIsEdit = true;
                }
            }
        }

        if (!$bIsEdit)
        {
            // avoid a flood
            $iFlood = Phpfox::getUserParam('poll.poll_flood_control');
            if ($iFlood != '0')
            {
                $aFlood = array(
                    'action' => 'last_post', // The SPAM action
                    'params' => array(
                        'field' => 'time_stamp', // The time stamp field
                        'table' => Phpfox::getT('poll'), // Database table we plan to check
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
                        'error_message' =>  Phpfox::getPhrase('poll.poll_flood_control', array('x' => $iFlood))
                    );
                }
            }            
        }
        $mErrors = Phpfox::getService('poll')->checkStructure($aVals);
        if (is_array($mErrors))
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(', ', $mErrors),
                'result' => 0
            );
        }       
        // check theres an image
        if (Phpfox::getParam('poll.is_image_required') && empty($_FILES['image']['name']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('poll.each_poll_requires_an_image'),
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
        // we do the insert
        // check if its updating:
        if ($bIsEdit)
        {
            $aVals['poll_id'] = $aPoll['poll_id'];
            $aVals['user_id'] = $aPoll['user_id'];
            if (Phpfox::getService('poll.process')->add(Phpfox::getUserId(), $aVals, true))
            {
                $sMessage =  Phpfox::getPhrase('poll.your_poll_has_been_updated');
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'message' => $sMessage,
                    'iPollId' => $aPoll['poll_id'],
                );
            }
        } else {
            if (list($iId, $aPoll) = Phpfox::getService('poll.process')->add(Phpfox::getUserId(), $aVals))
            {
                $sMessage =  Phpfox::getPhrase('poll.your_poll_has_been_added')
                            . ((Phpfox::getUserParam('poll.poll_requires_admin_moderation') == true) 
                                ? ' ' .  Phpfox::getPhrase('poll.your_poll_needs_to_be_approved_before_being_shown_on_the_site') : '');
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'message' => $sMessage,
                    'iPollId' => $iId,
                );
            }
        }
    }

    /**
     * Delete image of poll
     * 
     * Request options: 
     * - iPollId:              integer, required
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       'message': "Delete successfully"
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */    
    public function deleteimage($aData){
        $iPoll = isset($aData['iPollId']) ? (int) $aData['iPollId'] : 0;        

        if (Phpfox::getService('poll.process')->deleteImage($iPoll, Phpfox::getUserId()))
        {
            return array(
                'error_code' => 0,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.delete_successfully")),
                'result' => 1
            );            
        } else {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('poll.an_error_occured_and_your_image_could_not_be_deleted_please_try_again'),
                'result' => 0
            );            
        }
    }

    /**
     * Update poll
     * 
     * Request options: 
     * - iPollId:              integer, required
     * - sQuestion:              string, required
     * - aAnswer:              string (json encode from client), required
     * - iHideVote:              integer
     * - iPrivacy:              integer
     * - iPrivacyComment:              integer
     * - iRandomize:              integer
     * 
     * Response data contains: 
     * <code>
     * <br/>  {
     * <br/>       "result": 1,
     * <br/>       "error_code": 0,
     * <br/>       "iPollId": 229194,
     * <br/>       'message': "Your poll has been updated."
     * <br/>  }
     * </code>
     * 
     * @param array $aData
     * @return array
     */        
    public function edit($aData){
        return $this->create($aData, true);
    }

    /**
     * @ignore
     *
     */
    public function canPostComment($iItemId){
        if (!Phpfox::getUserParam('poll.can_access_polls'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_polls")));
        }

        if (!Phpfox::getUserParam('poll.can_post_comment_on_poll'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_polls")));
        }

        if (!($aItem = $this->getPollById($iItemId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_poll_you_are_looking_for_cannot_be_found")));
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('poll', $aItem['poll_id'], $aItem['user_id'], $aItem['privacy'], $aItem['is_friend'], $bReturn = true)){
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        if (!Phpfox::getService('comment')->canPostComment($aItem['user_id'], $aItem['privacy_comment']))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_this_item")));
        }

        // return null - it means TRUE
        return null;
    }

    /**
     * @ignore
     *
     */
    public function canView($iItemId){
        if (!Phpfox::getUserParam('poll.can_access_polls'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_access_polls")));
        }

        if (!($aItem = $this->getPollById($iItemId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_poll_you_are_looking_for_cannot_be_found")));
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('poll', $aItem['poll_id'], $aItem['user_id'], $aItem['privacy'], $aItem['is_friend'], $bReturn = true)){
            return array('result' => 0, 'error_code' => 1, 'error_message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }

        // return null - it means TRUE
        return null;
    }    

    /**
     * @ignore
     *
     */
    public function getPollById($iPollId)
    {
        return $this->database()->select('p.*')
            ->from(Phpfox::getT('poll'), 'p')
            ->where('p.poll_id = ' . (int) $iPollId)
            ->execute('getSlaveRow');
    }


}
