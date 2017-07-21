<?php
/**
 * short file description
 *
 * @category library
 * @author Nam Nguyen <namnv@younetco.com>
 * @version $Id$
 * @copyright $Copyright$
 * @license $License$
 * @package mfox.service
 */

/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

/**
 * Support quiz service
 *
 * @package  mfox.service.quiz
 * @author   Nam Nguyen <namnv@younetco.com>
 */
class Mfox_Service_Quiz extends Phpfox_Service {
    
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
     * @var string
     */
     
    protected  $_defaultImagePath = null;
    /**
     * @ignore
     * Class constructor
     */
    public function __construct()
    {
        $this->_oReq = Mfox_Service_Request_Request::instance();
        $this->_oSearch = Mfox_Service_Search_Search::instance();
        $this->_oBrowse = Mfox_Service_Search_Browse::instance();

        $this -> _sTable = Phpfox::getT('quiz');
    }

    /**
     * Fetch availble entries
     * <code>
     * curl -i -F "token=mbAA0lrgPSEKFp17V4qZTrWO" "http://product-dev.younetco.com/lytk/phpfox376/module/mfox/api.php/quiz/fetch"
     * </code>
     * Request options
     * - iPage:                 integer, optional, default "1"
     * - iAmountQuizOfCount:      integer, optional , default 10
     * - sKeyword:              string, optional
     * - sOrder:                string, optional, default "latest". Availabe values "latest, most_viewed, most_liked, most_discussed", most disscussed mean "order by total comment descendent"
     * - sView:                 string, optional, default "all". Availabe values "my, all"
     *
     * Response data contains
     *
     * - iQuizId:       integer
     * - iLikeCount:    integer
     * - iCommentCount: integer
     * - iDislikeCount: integer
     * - bCanLike:      integer [0,1]
     * - bCanDislike:   integer [0,1]
     * - bCanComment:   integer [0,1]
     * - bCanView:      integer [0,1]
     * - sTitle:        string  quiz question.
     * - sUserName:     string  username of the poster
     * - sFullName:     string  display name of the poster.
     * - sUserImage:    string  image url of the poster
     * - sModuleId:     string  post from module, always "user"
     *
     *
     * @param   array  $aData
     * @return  array
     */
    public function fetch($aData) {
        return $this -> getQuiz($aData);
    }
    
    
    public function canPostComment($iQuizId){
        
        if (!Phpfox::getUserParam('quiz.can_post_comment_on_quiz'))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_post_comment_on_blog")));
        }

        if (!($aItem = Phpfox::getService('quiz')->getQuizById($iQuizId)))
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_quiz_you_are_looking_for_cannot_be_found")));
        }

        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('quiz', $iQuizId,  $aItem['user_id'], $aItem['privacy'], $aItem['is_friend'], true))
        {
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
     * get quiz entries
     * called by fetch()
     */
    protected function getQuiz($aData) {

        $this->_oReq->set(array(
            'view' => !empty($aData['sView']) ? $aData['sView'] : '',
            'page' => !empty($aData['iPage']) ? (int) $aData['iPage'] : 1,
            'show' => !empty($aData['iAmountOfQuiz']) ? (int) $aData['iAmountOfQuiz'] : 10,
            'search' => !empty($aData['sSearch']) ? $aData['sSearch'] : '',
            'sort' => !empty($aData['sOrder']) ? $aData['sOrder'] : '',
            'module_id' => !empty($aData['sModule']) ? $aData['sModule'] : null,
            'item_id' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : null,
            'profile' => (!empty($aData['bIsProfile']) && $aData['bIsProfile'] == 'true') ? true : false,
            'profile_id' => !empty($aData['iProfileId']) ? (int) $aData['iProfileId'] : null,
        ));

        Phpfox::getUserParam('quiz.can_access_quiz', true);

        $bIsProfile = false;
        if ($this->_oReq->get('profile') === true)
        {
            $bIsProfile = true;
            $aUser = Phpfox::getService('user')->get($this->_oReq->get('profile_id'));
        }

        $sView = $this->_oReq->get('view');

        // search
        $sSearch = $this->_oReq->get('search');
        if (!empty($sSearch))
        {
            $this->_oSearch->setCondition('AND q.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"');
        }

        // sort
        $sSort = '';
        switch ($this->_oReq->get('sort')) {
            case 'most_viewed':
                $sSort = 'q.total_view DESC';
                break;
            case 'most_liked':
                $sSort = 'q.total_like DESC';
                break;
            case 'most_discussed':
                $sSort = 'q.total_comment DESC';
                break;
            default:
                $sSort = 'q.time_stamp DESC';
                break;
        }

        $this->_oSearch->setSort($sSort);

        switch ($sView)
        {
            case 'my':
                Phpfox::isUser(true);
                $this->_oSearch->setCondition('AND q.user_id = ' . (int) Phpfox::getUserId());
                break;
            case 'pending':
                Phpfox::isUser(true);
                Phpfox::getUserParam('quiz.can_approve_quizzes', true);
                $this->_oSearch->setCondition('AND q.view_id = 1');
                break;
            default:
                if ($bIsProfile === true)
                {
                    $this->_oSearch->setCondition('AND q.view_id IN(' . ($aUser['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ') AND q.user_id = ' . (int) $aUser['user_id'] . ' AND  q.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ')');
                }
                else 
                {
                    $this->_oSearch->setCondition('AND q.view_id = 0 AND q.privacy IN(%PRIVACY%)');
                }
                break;
        }       
        
        $aBrowseParams = array(
            'module_id' => 'quiz',
            'alias' => 'q',
            'field' => 'quiz_id',
            'table' => Phpfox::getT('quiz'),
            'hide_view' => array('pending', 'my'),
            'service' => 'mfox.quiz'
        );          
        
        $this->_oBrowse->params($aBrowseParams)->execute();
        
        $aQuizzes = $this->_oBrowse->getRows();

        return $aQuizzes;
    }

    public function processRows(&$aRows)
    {
        $aTmpRows = $aRows;
        $aRows = array();

        foreach ($aTmpRows as $aRow) {
            $aRows[] = $this->_prepareItem($aRow);
        }
    }
    
    public function query()
    {
        return Phpfox::getService('quiz.browse')->query();
    }
    
    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Mfox_Service_Friend::instance()->queryJoin($bNoQueryFriend))
        {
            $this->database()->join(Phpfox::getT('friend'), 'friends', 'friends.user_id = q.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());  
        }           
    }

    public function searchQuiz($aData){
        $aCond = array();

        $iCnt = 1000000;
        // limit 1M

        if (!isset($sSearch) || empty($sSearch))
            $sSearch = '';

        if (!isset($sView) || empty($sView))
            $sView = 'all';

        if (!isset($sOrder) || empty($sOrder))
            $sOrder = 'recent';

        if (!isset($iPage) || empty($iPage))
            $iPage = 1;
        
        $req = Phpfox::getLib('request');
        $req->set('page', $iPage);
        

        if (!isset($iAmountQuizOfCount) || empty($iAmountQuizOfCount))
            $iAmountQuizOfCount = 10;

        // process order
        $sOrderSql = 'q.time_stamp DESC';

        switch(strtolower($sOrder)) {
            case 'most_liked' :
                $sOrderSql = 'q.time_stamp DESC';
                break;

            case 'most_commented' :
            case 'most_discussed' :
                $sOrderSql = 'q.total_comment DESC';
                break;

            case 'most_viewed' :
                $sOrderSql = 'q.total_view DESC';
                break;

            case 'lasted' :
            case 'most_recent' :
            default :
                $sOrderSql = 'q.time_stamp DESC';
        }

        if ($sKeyword)
            $aCond[] = "";

        switch(strtolower($sView)){
            case 'my':
                $aCond[] = " AND (q.user_id='" . Phpfox::getUserId() . "') ";
                break;
            case 'all':
            default:
                $aCond[] = " AND (privacy=0) ";
            
                
        }
            

        if ($sSearch){
            
            $pSearch = Phpfox::getLib('parse.input') -> clean('%' . $sSearch . '%');
            
            $aCond[] = ' AND ( ' . 'q.title LIKE "' . $pSearch . '") ';
            
            // does not search field `description`  
            // $aCond[] = ' AND ( ' . 'q.title LIKE "' . $pSearch . '"' . ' OR q.description LIKE "' . $pSearch . '"' . ' ) ';
        }
        
        $this->search()->set(array(
                'type' => 'quiz',
                'field' => 'q.quiz_id',             
                'search_tool' => array(
                    'table_alias' => 'q',
                    'search' => array(
                        'action' => (defined('PHPFOX_IS_USER_PROFILE') ? $this->url()->makeUrl($aUser['user_name'], array('quiz', 'view' => $this->request()->get('view'))) : $this->url()->makeUrl('quiz', array('view' => $this->request()->get('view')))),
                        'default_value' =>  Phpfox::getPhrase('quiz.search_quizzes'),
                        'name' => 'search',
                        'field' => 'q.title'
                    ),
                    'sort' => array(
                        'latest' => array('q.time_stamp',  Phpfox::getPhrase('quiz.latest')),
                        'most-viewed' => array('q.total_view',  Phpfox::getPhrase('quiz.most_viewed')),
                        'most-liked' => array('q.total_like',  Phpfox::getPhrase('quiz.most_liked')),
                        'most-talked' => array('q.total_comment',  Phpfox::getPhrase('quiz.most_discussed'))
                    ),
                    'show' => array(Phpfox::getParam('quiz.quizzes_to_show'), Phpfox::getParam('quiz.quizzes_to_show') * 2, Phpfox::getParam('quiz.quizzes_to_show') * 3)
                )
            )
        );          
        
        
        $bIsProfile = false;
        
        switch ($sView)
        {
            case 'my':
                Phpfox::isUser(true);
                $this->search()->setCondition('AND q.user_id = ' . (int) Phpfox::getUserId());
                break;
            case 'pending':
                Phpfox::isUser(true);
                Phpfox::getUserParam('quiz.can_approve_quizzes', true);
                $this->search()->setCondition('AND q.view_id = 1');
                break;
            default:
                if ($bIsProfile)
                {
                    $this->search()->setCondition('AND q.view_id IN(' . ($aUser['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ') AND q.user_id = ' . (int) $aUser['user_id'] . ' AND  q.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ')');
                }
                else 
                {
                    $this->search()->setCondition('AND q.view_id = 0 AND q.privacy IN(%PRIVACY%)');
                }
                break;
        }       
        
        $aBrowseParams = array(
            'module_id' => 'quiz',
            'alias' => 'q',
            'field' => 'quiz_id',
            'table' => Phpfox::getT('quiz'),
            'hide_view' => array('pending', 'my')               
        );          
        
        $this->search()->browse()->params($aBrowseParams)->execute();
        
        $iCnt = $this->search()->browse()->getCount();
        $aQuizzes = $this->search()->browse()->getRows();
        
        $aResults = array();
        
        foreach($aQuizzes as $aQuiz){
            $aResults[] = $this->_prepareItem($aQuiz);
        }
        
        return $aResults;
    }

    public function search(){
        return Phpfox::getLib('search');
    }

    /**
     * Extends the url class and returns its class object.
     *
     * @see Phpfox_Url
     * @return object
     */
    protected function url()
    {
        return Phpfox::getLib('url');   
    }   
    

    public function detail($aData) {
        // curl -i -F "iQuizId=1" -F "token=IA920l8IGjOXbCrp72UqPT1T" "http://product-dev.younetco.com/lytk/phpfox376/module/mfox/api.php/quiz/detail"
        extract($aData);

        if (!isset($iQuizId) || empty($iQuizId))
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_params_iquizid"))
            );

        $aDetail = $this -> _getQuizById($iQuizId,true);

        $aDetail['takers'] = $this -> _getTakers($iQuizId, 1, 10);

        return $aDetail;
    }

    public function questions($aData) {

        extract($aData);

        if (!isset($iQuizId) || empty($iQuizId))
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_params_iquizid"))
            );

        $aDetail = $this -> _getQuizById($iQuizId);

        // remove questions from detail.
        // developers + quiz/questions to get question lists.
        $aDetail['questions'] = $this -> _getQuizQuestions($iQuizId);

        return $aDetail;

    }
    
    /**
     * @param string $iQuizId
     */
    protected function getTakerCount($iQuizId){
        $row = $this->database()
            ->select('count(distinct(user_id)) as taker_count')
            ->from(Phpfox::getT('quiz_result'))
            ->where('quiz_id='. intval($iQuizId))
            ->execute('getSlaveRow');
            
         if(empty($row))
            return 0;

        return $row['taker_count'];
    }
    
    
    protected function hasTaken($iQuizId, $iUserId){
        $row = $this->database()
            ->select('*')
            ->from(Phpfox::getT('quiz_result'))
            ->where('quiz_id='. intval($iQuizId) .' and user_id=' .intval($iUserId))
            ->execute('getSlaveRow');
            
         if(empty($row))
            return false;
        
         return true;
    }
    

    public function answer($aData) {

        extract($aData);

        $iUserId = Phpfox::getUserId();

        if (!isset($iQuizId) || empty($iQuizId))
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_params_iquizid"))
            );

        $aPreparedAnswers = array();

        foreach ($aAnswers as $aAnswer) {
            $aPreparedAnswers[$aAnswer['iQuestionId']] = $aAnswer['iAnswerId'];
        }

        return $this -> _answerQuiz($iQuizId, $aPreparedAnswers, $iUserId);
    }

    /**
     * submits one user's answers to a quiz
     * @param integer $iUser
     * @param array $aAnswers array('questionid' => 'answerid')
     * @return mixed    int if ok (score), string on error
     */
    protected function _answerQuiz($iQuizId, $aAnswers, $iUserId) {
        // we need to count how many questions are there for this quiz...

        // get the questions for this quiz
        $aDbQuiz = $this -> database() -> select('q.*, qq.*') -> from($this -> _sTable, 'q') -> join(Phpfox::getT('quiz_question'), 'qq', 'qq.quiz_id = q.quiz_id') -> where('q.quiz_id = ' . (int)$iQuizId) -> execute('getSlaveRows');

        if ($aDbQuiz[0]['view_id'] == 1) {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('quiz.you_cannot_answer_a_quiz_that_has_not_been_approved'),
            );
        }
        if (count($aDbQuiz) != count($aAnswers)) {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('quiz.you_need_to_answer_every_question'),
            );
        }

        // check if user can answer his own quizzes
        if (!Phpfox::getUserParam('quiz.can_answer_own_quiz')) {
            // check if its the same user
            if ($aDbQuiz[0]['user_id'] == $iUser) {
                return array(
                    'error_code' => 1,
                    'error_message' =>  Phpfox::getPhrase('quiz.you_cannot_answer_your_own_quiz')
                );
            }
        }

        // insert all the answers to the DB and build OR query
        $sQuestionsId = 'is_correct = 1 AND ( 1 = 2';
        foreach ($aAnswers as $iQuestion => $iAnswer) {
            $this -> database() -> insert(Phpfox::getT('quiz_result'), array(
                'quiz_id' => $aDbQuiz[0]['quiz_id'],
                'question_id' => $iQuestion,
                'answer_id' => $iAnswer,
                'user_id' => $iUserId,
                'time_stamp' => PHPFOX_TIME
            ));
            $sQuestionsId .= ' OR question_id = ' . $iQuestion;
        }

        //get the success for this quiz by this user
        $aCorrectAnswers = $this -> database() -> select('answer_id') -> from(Phpfox::getT('quiz_answer')) -> where($sQuestionsId . ')') -> execute('getSlaveRows');

        $iTotalCorrect = 0;
        foreach ($aCorrectAnswers as $iAnswerId) {
            $mSearch = array_search($iAnswerId['answer_id'], $aAnswers);

            if ($mSearch !== false) {
                $iTotalCorrect++;
            }
        }

        return array(
            'error_code' => 0,
            'message'=>html_entity_decode(Phpfox::getPhrase("mfox.successful")),
            'percentage' => (int)(($iTotalCorrect / count($aAnswers)) * 100),
        );
    }

    public function takers($aData) {
        extract($aData);

        if (!isset($iQuizId) || empty($iQuizId))
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_params_iquizid"))
            );

        if (!isset($iPage) || empty($iPage))
            $iPage = 1;

        if (!isset($iAmountOfTaker) || empty($iAmountOfTaker))
            $iAmountOfTaker = 10;

        return $this -> _getTakers($iQuizId, $iPage, $iAmountOfTaker);
    }

    /**
     * Gets the recent takers of a quiz
     * @param string $sQuizUrl
     * @return array
     */
    public function _getTakers($iQuizId, $iPage, $iAmountOfTaker) {
        // we get only the latest `quiz.takers_to_show`
        $aCount = $this -> database() -> select('DISTINCT qr.user_id') -> from(Phpfox::getT('quiz_result'), 'qr') -> where('qr.quiz_id=' . $iQuizId) -> order('qr.time_stamp DESC') -> limit($iPage, $iAmountOfTaker)

        // -> limit(Phpfox::getParam('quiz.takers_to_show'))
        -> execute('getSlaveRows');

        $sUsers = '1=1 ';

        foreach ($aCount as $aUser)
            $sUsers .= 'OR qr.user_id = ' . $aUser['user_id'] . ' ';

        //$sUsers = substr($sUsers, 3);

        $aResults = $this -> database() -> select('qr.*, q.*, ' . Phpfox::getUserField()) -> from($this -> _sTable, 'q') -> join(Phpfox::getT('quiz_result'), 'qr', 'q.quiz_id = qr.quiz_id') -> join(Phpfox::getT('user'), 'u', 'qr.user_id = u.user_id') -> order('qr.time_stamp DESC') -> where('q.quiz_id = ' . (int)$iQuizId . ' AND (' . $sUsers . ')') -> execute('getSlaveRows');

        if (empty($aResults))
            return array();

        $iQuizId = reset($aResults);
        $iQuizId = $iQuizId['quiz_id'];
        $aQuizzes = array();

        // skip all unrelated data.
        foreach ($aResults as $aUser) {
            $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aUser, '_50_square');

            $aQuizzes[$aUser['user_id']] = array(
                'iQuizId' => $iQuizId,
                'iUserId' => $aUser['user_id'],
                'sUserName' => $aUser['user_name'],
                'sFullName' => Phpfox::getService('mfox')->decodeUtf8Compat($aUser['full_name']),
                'sImageUrl' => $sUserImage
            );
        }

        // we now have the user_id as $aQuizzes[quizTakerId], we need the correct answers
        $aAnswers = $this -> database() -> select('qa.*') -> from(Phpfox::getT('quiz_answer'), 'qa') -> join(Phpfox::getT('quiz_question'), 'qq', 'qa.question_id = qq.question_id') -> where('qq.quiz_id = ' . $iQuizId) -> execute('getSlaveRows');

        // now match the correct ones
        $iTotalCorrect = 0;
        foreach ($aAnswers as $aAnswer) {// go through the correct answers
            if ($aAnswer['is_correct'] == 1)
                $iTotalCorrect++;

            foreach ($aResults as $aUserInput) {
                /*
                 * $aUser = array(
                 *      'total_correct' => Total correct answers in this quiz which is the same as count(questions)
                 *      'iUserCorrectAnswers => how many answers he submitted right. aAnswer[is_correct] == 1 && aAnswer[answer_id] == aUserInput[answer_id]
                 * );
                 */
                // Initialize
                $aQuizzes[$aUserInput['user_id']]['total_correct'] = $iTotalCorrect;
                if (!isset($aQuizzes[$aUserInput['user_id']]['iSuccessPercentage']))// success percentage for the user
                    $aQuizzes[$aUserInput['user_id']]['iSuccessPercentage'] = 0;
                if (!isset($aQuizzes[$aUserInput['user_id']]['iUserCorrectAnswers']))// correct count for user input
                    $aQuizzes[$aUserInput['user_id']]['iUserCorrectAnswers'] = 0;
                if (($aAnswer['answer_id'] == $aUserInput['answer_id']) && $aAnswer['is_correct'] == 1) {
                    $aQuizzes[$aUserInput['user_id']]['iUserCorrectAnswers']++;
                }
                if ($iTotalCorrect > 0) {
                    $iPerc = (($aQuizzes[$aUserInput['user_id']]['iUserCorrectAnswers'] / $iTotalCorrect) * 100);
                } else {
                    $iPerc = 0;
                }
                $aQuizzes[$aUserInput['user_id']]['iSuccessPercentage'] = round($iPerc);
            }
        }

        return array_values($aQuizzes);
    }

    public function result($aData){
        extract($aData);
        
        if (!isset($iQuizId) || empty($iQuizId))
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_params_iquizid"))
            );
        
        if (!isset($iUserId) || empty($iUserId))
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_params_iuserid"))
            );
            
         
         return $this->_getQuizResult($iQuizId, $iUserId);
    }
    

    public function _getQuizResult($iQuizId, $iUserId){
        
        
        // only get the results of one user -> $iUser
        // first get all the answers
        $aAnswers = $this->database()->select('qq.question_id, qa.answer, qq.question, qa.answer_id')
        ->from(Phpfox::getT('quiz_question'), 'qq')
        ->join(Phpfox::getT('quiz_answer'), 'qa', 'qq.question_id = qa.question_id')
        ->where('qa.is_correct = 1 and qq.quiz_id = ' . $iQuizId . ' ')
        ->order('qq.question_id ASC')
        ->execute('getSlaveRows');

        $aResults = $this->database()->select('*, ' . Phpfox::getUserField())
        ->from(Phpfox::getT('quiz_result'), 'qr')
        ->join(Phpfox::getT('quiz_answer'), 'qa', 'qa.answer_id = qr.answer_id')
        ->join(Phpfox::getT('user'), 'u', 'u.user_id = qr.user_id')
        ->where('qr.user_id = ' . (int)($iUserId) . ' AND qr.quiz_id = ' . $iQuizId)
        ->execute('getSlaveRows');

        $aUsersAnswers = array();
        $iTotalCorrect = 0;
        $iTotalAnswers = count($aAnswers);
        

        // now we check the user's answers vs the correct answers
        foreach ($aAnswers as $aAnswer)
        {
            // this is to initialize the array so any unanswered question caused by an edit will still show
            $aUsersAnswers[$aAnswer['question_id']]['sCorrectAnswer'] = Phpfox::getService('mfox')->decodeUtf8Compat($aAnswer['answer']);
            $aUsersAnswers[$aAnswer['question_id']]['sAnswer'] =  Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('quiz.not_answered'));
            $aUsersAnswers[$aAnswer['question_id']]['iAnswerUserId'] = '0';
            $aUsersAnswers[$aAnswer['question_id']]['iCorrectAnswer'] = $aAnswer['answer_id'];
            $aUsersAnswers[$aAnswer['question_id']]['sQuestion'] = Phpfox::getService('mfox')->decodeUtf8Compat($aAnswer['question']);
            $aUsersAnswers[$aAnswer['question_id']]['iQuesionId'] = $aAnswer['question_id'];
            
            foreach ($aResults as $aResult)
            {
                if ($aResult['question_id'] == $aAnswer['question_id'])
                { // its the same question
                    $id = $aAnswer['question_id'];
                    $aUsersAnswers[$id]['iAnswerUserId'] =  $aResult['user_id'];
                    $aUsersAnswers[$id]['sAnswer'] =  Phpfox::getService('mfox')->decodeUtf8Compat($aResult['answer']);
                    $aUsersAnswers[$id]['iAnswerId'] =  $aResult['answer_id'];
                    
                    // array(
                        // // 'sQuestion' => $aAnswer['question'],
                        // 'iAnswerId'=> $aResult['answer_id'],
                        // 'sAnswer' => $aResult['answer'],
                        // 'iCorrectAnswer' => $aAnswer['answer_id'],
                        // 'sCorrectAnswer' => $aAnswer['answer'],
                        // 'user_name' => $aResult['user_name'],
                        // 'user_id' => $aResult['user_id'],
                        // 'server_id' => $aResult['server_id'],
                        // 'full_name' => $aResult['full_name'],
                        // 'gender' => $aResult['gender'],
                        // 'user_image' => $aResult['user_image'],
                        // 'iTimeStamp' => $aResult['time_stamp']
                    // );
                    
                    if ($aResult['answer_id'] == $aAnswer['answer_id'])
                    {
                        $iTotalCorrect++;
                    }
                }   
            }   
        }


        $aUsersAnswers = array_values($aUsersAnswers);
        
        $userInfo = $this->getTakerInfo($iUserId);
    
        $response =  $this->_getQuizById($iQuizId);
        
        $response['sFullName'] = Phpfox::getService('mfox')->decodeUtf8Compat($userInfo['full_name']);
        
        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($userInfo, '_50_square');
        
        $response['sUserImage'] = $sUserImage;
        
        $response['iTotalCorrect'] = $iTotalCorrect;
        $response['iTotalCorrectAnswers'] = $iTotalAnswers;
        $response['iSuccessPercentage'] = ($iTotalAnswers > 0) ? round(($iTotalCorrect / $iTotalAnswers) *100) : 0;
        $response['aAnswers']  = $aUsersAnswers;
        
        return $response;
    
    }

    protected function getTakerInfo($iUserId){
        $aRow = $this->database()
            ->select('*')
            ->from(Phpfox::getT('user'))
            ->where('user_id='.intval($iUserId))
            ->execute('getSlaveRow');
            
        return $aRow;
    }
    
    /**
     * Checks if a user has taken a quiz
     * @param integer $iUser User identifier
     * @param integer $sQuiz Quiz identifier
     * @return boolean
     */
    public function _hasTakenQuiz($iQuizId, $iUserId)
    {
        $iTaken = (int) $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('quiz_result'), 'qr')
            ->join($this->_sTable, 'q', 'q.quiz_id = qr.quiz_id')
            ->where('q.quiz_id = ' . (int) $iQuizId . ' AND qr.user_id = ' . (int)$iUserId)
            ->execute('getSlaveField');

        return ($iTaken > 0);

    }

    /**
     * @ignore
     * predecated
     * 
     * Gets the recent takers of a quiz
     * @param string $sQuizUrl
     * @return array
     */
    public function __result($aData) {

        extract($aData);

        if (!isset($iQuizId) || empty($iQuizId))
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_params_iquizid"))
            );

        if (!isset($iPage) || empty($iPage))
            $iPage = 1;

        if (!isset($iAmountOfTaker) || empty($iAmountOfTaker))
            $iAmountOfTaker = 10;

        // we get only the latest `quiz.takers_to_show`
        $aCount = $this -> database() -> select('DISTINCT qr.user_id') -> from(Phpfox::getT('quiz_result'), 'qr') -> join($this -> _sTable, 'q', 'q.quiz_id = ' . (int)$iQuizId) -> order('qr.time_stamp DESC') -> limit($iPage, $iAmountOfTaker, 1000000)

        // -> limit(Phpfox::getParam('quiz.takers_to_show'))
        -> execute('getSlaveRows');

        // and make it a String so we can use it in the Results query
        $sUsers = '1=1 ';

        foreach ($aCount as $aUser)
            $sUsers .= 'OR qr.user_id = ' . $aUser['user_id'] . ' ';

        //$sUsers = substr($sUsers, 3);

        $aResults = $this -> database() -> select('qr.*, q.*, ' . Phpfox::getUserField()) -> from($this -> _sTable, 'q') -> join(Phpfox::getT('quiz_result'), 'qr', 'q.quiz_id = qr.quiz_id') -> join(Phpfox::getT('user'), 'u', 'qr.user_id = u.user_id') -> order('qr.time_stamp DESC') -> where('q.quiz_id = ' . (int)$iQuizId . ' AND (' . $sUsers . ')') -> execute('getSlaveRows');

        if (empty($aResults)) {
            return array();
        }

        $iQuizId = reset($aResults);
        $iQuizId = $iQuizId['quiz_id'];
        $aQuizzes = array();
        foreach ($aResults as $aUser) {
            $aQuizzes[$aUser['user_id']]['user_info'] = $aUser;
        }

        // we now have the user_id as $aQuizzes[quizTakerId], we need the correct answers
        $aAnswers = $this -> database() -> select('qa.*') -> from(Phpfox::getT('quiz_answer'), 'qa') -> join(Phpfox::getT('quiz_question'), 'qq', 'qa.question_id = qq.question_id') -> where('qq.quiz_id = ' . $iQuizId) -> execute('getSlaveRows');
        // now match the correct ones
        $iTotalCorrect = 0;
        foreach ($aAnswers as $aAnswer) {// go through the correct answers
            if ($aAnswer['is_correct'] == 1)
                $iTotalCorrect++;
            foreach ($aResults as $aUserInput) {
                /*
                 * $aUser = array(
                 *      'total_correct' => Total correct answers in this quiz which is the same as count(questions)
                 *      'iUserCorrectAnswers => how many answers he submitted right. aAnswer[is_correct] == 1 && aAnswer[answer_id] == aUserInput[answer_id]
                 * );
                 */
                // Initialize
                $aQuizzes[$aUserInput['user_id']]['total_correct'] = $iTotalCorrect;
                if (!isset($aQuizzes[$aUserInput['user_id']]['iSuccessPercentage']))// success percentage for the user
                    $aQuizzes[$aUserInput['user_id']]['iSuccessPercentage'] = 0;
                if (!isset($aQuizzes[$aUserInput['user_id']]['iUserCorrectAnswers']))// correct count for user input
                    $aQuizzes[$aUserInput['user_id']]['iUserCorrectAnswers'] = 0;
                if (($aAnswer['answer_id'] == $aUserInput['answer_id']) && $aAnswer['is_correct'] == 1) {
                    $aQuizzes[$aUserInput['user_id']]['iUserCorrectAnswers']++;
                }
                if ($iTotalCorrect > 0) {
                    $iPerc = (($aQuizzes[$aUserInput['user_id']]['iUserCorrectAnswers'] / $iTotalCorrect) * 100);
                } else {
                    $iPerc = 0;
                }
                $aQuizzes[$aUserInput['user_id']]['iSuccessPercentage'] = round($iPerc);
            }
        }

        return $aQuizzes;
    }

    /**
     * get configuration for added form.
     */
    public function formadd($aData) {
    	
        $response = array(
            'view_options' => Phpfox::getService('mfox.privacy') -> privacy($aData),
            'comment_options' => Phpfox::getService('mfox.privacy') -> privacycomment($aData),
            'perms'=> array(
                'iMinQuestion'=> Phpfox::getUserParam('quiz.min_questions'),
                'iMaxQuestion'=>Phpfox::getUserParam('quiz.max_questions'),
                'iMinAnswer'=> Phpfox::getUserParam('quiz.min_answers'),
                'iMaxAnswer'=>Phpfox::getUserParam('quiz.max_answers'),
                'bCanAnswerQuiz'=>Phpfox::getUserParam('quiz.can_answer_own_quiz'),
                'bcanApproveQuiz'=>Phpfox::getUserParam('quiz.can_approve_quizzes'),
                'bCanDeleteOtherQuiz'=>Phpfox::getUserParam('quiz.can_delete_others_quizzes'),
                'bNewQuizNeedModeration'=>Phpfox::getUserParam('quiz.new_quizzes_need_moderation'),
                'bCanDeleteOwnQuiz'=>Phpfox::getUserParam('quiz.can_delete_own_quiz'),
                'bCanPostCommentOnQuiz'=>Phpfox::getUserParam('quiz.can_post_comment_on_quiz'),
                'bCanEditOwnQuestion'=>Phpfox::getUserParam('quiz.can_edit_own_questions'),
                'bCanEditOthersQuestion'=>Phpfox::getUserParam('quiz.can_edit_others_questions'),
                'bCanEditOwnTitle'=>Phpfox::getUserParam('quiz.can_edit_own_title'),
                'bCanEditOthersTitle'=>Phpfox::getUserParam('quiz.can_edit_others_title'),
                'iPointQuiz'=>Phpfox::getUserParam('quiz.points_quiz'),
                'bCanViewResultBeforeAnswer'=>Phpfox::getUserParam('quiz.can_view_results_before_answering'),
                'bCanAccessQuiz'=>Phpfox::getUserParam('quiz.can_access_quiz'),
                'bCanCreateQuiz'=>Phpfox::getUserParam('quiz.can_create_quiz'),
                'bCanUploadPicture'=>Phpfox::getUserParam('quiz.can_upload_picture'),
            // 'category_options'=> $this->getCategories($aData),
            )
        );

        $iValue = Phpfox::getService('user.privacy')->getValue('quiz.default_privacy_setting');
        $response['default_privacy_setting'] = Phpfox::getService('mfox.privacy')->defaultprivacy("$iValue", $response['view_options']);

        return $response;
    }
    
    /**
     * request
     * + iQuizId: int required
     */
    public function formedit($aData) {
        
        extract($aData);
        
        $iUserId = Phpfox::getUserId();

        if (!isset($iQuizId) || empty($iQuizId))
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_params_iquizid"))
            );
        
        $response = array(
            'view_options' => Phpfox::getService('mfox.privacy') -> privacy($aData),
            'comment_options' => Phpfox::getService('mfox.privacy') -> privacycomment($aData),
            'detail'=> $this->_getQuizById($iQuizId),
            'questions'=> $this->_getQuizQuestions($iQuizId),
            'perms'=> $this->_getQuizPermission($iQuizId, $iUserId),
        );

        return $response;
    }

    function perms(){
        return array(
            'bCanEditTitle'=> Phpfox::getUserParam('quiz.can_edit_own_title') || Phpfox::getUserParam('quiz.can_edit_others_title'),
            'bCanEditQuestion'=>Phpfox::getUserParam('quiz.can_edit_own_questions') || Phpfox::getUserParam('quiz.can_edit_others_questions'),
            'iMinQuestion'=> Phpfox::getUserParam('quiz.min_questions'),
            'iMaxQuestion'=>Phpfox::getUserParam('quiz.max_questions'),
            'iMinAnswer'=> Phpfox::getUserParam('quiz.min_answers'),
            'iMaxAnswer'=>Phpfox::getUserParam('quiz.max_answers'),
            'bCanAnswerQuiz'=>Phpfox::getUserParam('quiz.can_answer_own_quiz'),
            'bcanApproveQuiz'=>Phpfox::getUserParam('quiz.can_approve_quizzes'),
            'bCanDeleteOtherQuiz'=>Phpfox::getUserParam('quiz.can_delete_others_quizzes'),
            'bNewQuizNeedModeration'=>Phpfox::getUserParam('quiz.new_quizzes_need_moderation'),
            'bCanDeleteOwnQuiz'=>Phpfox::getUserParam('quiz.can_delete_own_quiz'),
            'bCanPostCommentOnQuiz'=>Phpfox::getUserParam('quiz.can_post_comment_on_quiz'),
            'bCanEditOwnQuestion'=>Phpfox::getUserParam('quiz.can_edit_own_questions'),
            'bCanEditOthersQuestion'=>Phpfox::getUserParam('quiz.can_edit_others_questions'),
            'bCanEditOwnTitle'=>Phpfox::getUserParam('quiz.can_edit_own_title'),
            'bCanEditOthersTitle'=>Phpfox::getUserParam('quiz.can_edit_others_title'),
            'iPointQuiz'=>Phpfox::getUserParam('quiz.points_quiz'),
            'bCanViewResultBeforeAnswer'=>Phpfox::getUserParam('quiz.can_view_results_before_answering'),
            'bCanAccessQuiz'=>Phpfox::getUserParam('quiz.can_access_quiz'),
            'bCanCreateQuiz'=>Phpfox::getUserParam('quiz.can_create_quiz'),
            'bCanUploadPicture'=>Phpfox::getUserParam('quiz.can_upload_picture'),
        );
    }

    function _getDefaultQuizImagePath(){
        if(null == $this->_defaultImagePath)
            $this->_defaultImagePath = Phpfox::getParam('core.url_module') . 'mfox/static/image/quizzes_default_image.png';
        
        return $this->_defaultImagePath;
    }

    function _prepareItem($aItem, $detail = false) {
        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aItem, '_50_square');
        
        if($aItem['image_path']){
            $sImageUrl = Phpfox::getLib('image.helper') -> display(array(
                'server_id' => $aItem['server_id'],
                'path' => 'quiz.url_image',
                'file' => $aItem['image_path'],
                'suffix' => '',
                'return_url' => true
            ));
        }else{
            $sImageUrl = $this->_getDefaultQuizImagePath();
        }
        

        $aUserLike = array();
        $aUserDislike = array();
        $bIsDisliked = false;
        $bCanLike = true;
        $bCanComment = true;
        $bCanLike = true;
        $bCanDislike = true;

        $inputData = Phpfox::getService('mfox.like')->changeInputData(array('sItemType'=>'quiz','iItemId'=>$aItem['quiz_id']));


        $aLike = Phpfox::getService('mfox.like')->getListOfLikedUser(
                    $inputData['sItemType']
                    , $inputData['iItemId']
                    , false
                    , 999999
                );

        foreach($aLike['likes'] as $like){
            $aUserLike[] = array('iUserId' => $like['user_id'], 'sDisplayName' => Phpfox::getService('mfox')->decodeUtf8Compat($like['full_name']));
        }

        $aDislike = Phpfox::getService('mfox.like')->getListOfDislikeUser(
                    $inputData['sItemType']
                    , $inputData['iItemId']
                    , $bGetCount = false);
                foreach($aDislike as $dislike){
                    if(Phpfox::getUserId() ==  $dislike['user_id']){
                        $bIsDisliked = true;
                    }
                    $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => Phpfox::getService('mfox')->decodeUtf8Compat($dislike['full_name']));
                }

        $bIsLiked =  Phpfox::getService('mfox.like')->checkIsLiked(
            $inputData['sItemType']
            , $inputData['iItemId']
            , Phpfox::getUserId()
        ); 
        
        $bCanComment = Phpfox::getService('mfox.comment')->checkCanPostCommentOnItem($aItem) && Phpfox::getUserParam('quiz.can_post_comment_on_quiz');
        
        $bCanTake = true;
        
        if(Phpfox::getUserId() == $aItem['user_id']){
            // check if user can answer his own quizzes
            if (!Phpfox::getUserParam('quiz.can_answer_own_quiz'))
            {
                $bCanTake = false;
            }
        }

        $bCanTake = ($aItem['view_id'] ? 0 : $bCanTake)?($this->hasTaken($aItem['quiz_id'], Phpfox::getUserId())?0:$bCanTake):0;
		
		$bIsOwner  = Phpfox::getUserId() == $aItem['user_id'];  
		$bCanEdit  = 0;
		$bCanEditTitle  = 0;
		$bCanEditQuestion = 0;
		$bCanDelete = 0;
		$bCanAnswer =  1;
		
		if (($bIsOwner && Phpfox::getUserParam('quiz.can_edit_own_title'))
		|| (!$bIsOwner && Phpfox::getUserParam('quiz.can_edit_others_title'))){
			$bCanEditTitle =  1;
		}
		
		if (($bIsOwner && Phpfox::getUserParam('quiz.can_edit_own_questions'))
		|| (!$bIsOwner && Phpfox::getUserParam('quiz.can_edit_others_questions'))){
			$bCanEditQuestion =  1;
		}
		
		$bCanEdit =  ($bCanEditQuestion  || $bCanEditTitle)?1:0;
		
		if (($bIsOwner && Phpfox::getUserParam('quiz.can_delete_own_quiz'))
		|| (!$bIsOwner && Phpfox::getUserParam('quiz.can_delete_others_quizzes'))){
			$bCanDelete =  1;
		}
		
		if ($bIsOwner && !Phpfox::getUserParam('quiz.can_answer_own_quiz')){
			$bCanAnswer =  0;
		}
		
        return array(
            'iQuizId' => $aItem['quiz_id'],
            'iUserId' => $aItem['user_id'],
            'sTitle' => Phpfox::getService('mfox')->decodeUtf8Compat($aItem['title']),
            'sDescription' => Phpfox::getService('mfox')->decodeUtf8Compat($aItem['description']),
            'iQuestionCount' => $this->getQuestionCount($aItem['quiz_id']),
            'iPrivacy' => $aItem['privacy'],
            'iCommentPrivacy' => $aItem['privacy_comment'],
            'iTimeStamp' => $aItem['time_stamp'],
            'iTotalView' => $aItem['total_view'],
            'iTotalComment' => $aItem['total_comment'],
            'iTotalLike' => $aItem['total_like'],
            'iTotalDislike' => $aItem['total_dislike'],
            'aUserLike' => $aUserLike,
            'aUserDislike'=>$aUserDislike,
            'bIsLiked'=>$bIsLiked,
            'bIsDisliked'=>$bIsDisliked,
            'bCanLike'=>$bCanLike,
            'bCanDislike'=>$bCanDislike,
            'bCanComment'=>$bCanComment,
            'bCanPostComment'=>$bCanComment,
            'sUserName' => $aItem['user_name'],
            'sFullName' => Phpfox::getService('mfox')->decodeUtf8Compat($aItem['full_name']),
            'bCanTake' => $bCanTake,
            'sUserImage' => $sUserImage,
            'sImageUrl' => $sImageUrl,
            'bHasCustomImage'=> $aItem['image_path']?1:0,
            'bCanEdit'=>$bCanEdit,
            'bCanDelete'=>$bCanDelete,
            'bCanEditQuestion'=>$bCanEditQuestion,
            'bCanEditTitle'=>$bCanEditTitle,
            'bCanAnswer'=>$bCanAnswer,
            'iTakerCount' => $this->getTakerCount($aItem['quiz_id'])
        );
    }

    protected function getQuestionCount($iQuizId){
        
        $row = $this->database()
            ->select('count(*) as question_count')
            ->from(Phpfox::getT('quiz_question'))
            ->where('quiz_id='. intval($iQuizId))
            ->execute('getSlaveRow');
            
       if(empty($row))
            return 0;
       
       return intval($row['question_count']);
            
       
    }

    protected function _getQuizQuestions($iQuizId) {
        // then they can edit something
        // get the quiz and their questions
        $aQuestions = $this -> database() -> select('qq.*') -> from(Phpfox::getT('quiz_question'), 'qq') -> order('qq.question_id ASC') -> where('qq.quiz_id = ' . (int)$iQuizId) -> execute('getSlaveRows');

        if (empty($aQuestions))
            return array();

        // now get the answers
        $sQuestions = '';
        foreach ($aQuestions as $aQuestion)
            $sQuestions .= 'OR qa.question_id = ' . $aQuestion['question_id'] . ' ';

        $sQuestions = substr($sQuestions, 3);

        $aAnswers = $this -> database() 
            -> select('qa.*') 
            -> from(Phpfox::getT('quiz_answer'), 'qa')
            -> order('qa.answer_id ASC') 
            -> where($sQuestions) 
            -> execute('getSlaveRows');

        if (empty($aAnswers))
            return array();

        // glue them
        foreach ($aAnswers as $aAnswer) {
            foreach ($aQuestions as $aKey => $aQuestion) {
                if ($aQuestion['question_id'] == $aAnswer['question_id']) {
                    $aQuestions[$aKey]['answers'][] = $aAnswer;
                }
            }
        }

        return $aQuestions;
    }

    function _getQuizById($iQuizId, $detail = false) {
        $aItem = $this -> database() 
        -> select("q.*, " . Phpfox::getUserField()) 
        -> from($this -> _sTable, 'q') 
        -> join(Phpfox::getT('user'), 'u', 'u.user_id = q.user_id') 
        -> where('q.quiz_id=' . intval($iQuizId)) 
        -> order($sOrderSql) 
        -> limit($iPage, $iAmountQuizOfCount, $iCnt) 
        -> execute('getSlaveRow');

        if (!$aItem)
            return array();

        return $this -> _prepareItem($aItem, $detail);

    }

    /**
     * add new quiz
     * Request data structure
     * <code>
     * {
     *  sTitle,
     *   sDescription,
     *   sAuthView,
     *   sAuthComment,
     *   aQuestions
     *   [
     *     {
     *        sQuestion,
     *        aAnswers
     *        [
     *            {
     *                sAnswer
     *                bIsCorrect
     *            }
     *        ]
     *    }
     *  ]
     * }
     * </code>
     * 
     * 
     */
    function add($aData) {
        return $this -> _addQuiz($aData, Phpfox::getUserId());
    }
    
    function delete($aData){
        
        extract($aData);
        
        if (!isset($iQuizId) || empty($iQuizId))
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_params_iquizid"))
            );
        
        
        
        return $this->_deleteQuiz($iQuizId, Phpfox::getUserId());
    }
    
    /**
     * Deletes a quiz from the database along with its results, answers and questions
     * @param int $iQuiz
     * @param int $iUser User deleting the quiz (can be an admin or the quiz owner)
     * @return boolean
     */
    public function _deleteQuiz($iQuiz, $iUser)
    {

        // we need to get all the questions by joining to the questions table
        $aAnswers = $this->database()->select('qq.question_id, q.user_id')
        ->from(Phpfox::getT('quiz_question'), 'qq')
        ->join($this->_sTable, 'q', 'q.quiz_id = ' . (int)$iQuiz)
        ->where('qq.quiz_id  = ' . (int)$iQuiz)
        ->execute('getSlaveRows');

        $sAnswers = "(1 = 2) ";
        $iUserId = 0;
        foreach($aAnswers as $aAnswer)
        {
            $sAnswers .= ' OR question_id = ' . $aAnswer['question_id'];
            $iUserId = $aAnswer['user_id'];
        }
        $isOwner = ($iUserId == $iUser);
        if (($isOwner && !Phpfox::getUserParam('quiz.can_delete_own_quiz') ||
                (!$isOwner && !Phpfox::getUserParam('quiz.can_delete_others_quizzes'))))
        {
            return array(
                'error_code'=>1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_have_no_permission_to_delete_this_quiz")),
            );
        }
        $bDel = true;
        $bDel = $bDel && $this->database()->delete($this->_sTable, 'quiz_id = ' . (int)$iQuiz);
        $bDel = $bDel && $this->database()->delete(Phpfox::getT('quiz_track'), 'item_id = ' . (int)$iQuiz);
        $bDel = $bDel && $this->database()->delete(Phpfox::getT('quiz_answer'), $sAnswers);
        $bDel = $bDel && $this->database()->delete(Phpfox::getT('quiz_question'), 'quiz_id = ' . (int)$iQuiz);
        $bDel = $bDel && $this->database()->delete(Phpfox::getT('quiz_result'), 'quiz_id = ' . (int)$iQuiz);

        // Update user activity
        Phpfox::getService('user.activity')->update($iUserId, 'quiz', '-');
        
        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('quiz', $iQuiz) : null);
        (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('comment_quiz', $iQuiz) : null);
        
        return array(
            'error_code'=>0,
            'message'=>'Quiz deleted successfully',
        );
    }

    function edit($aData){
        return $this->_updateQuiz($aData, Phpfox::getUserId());
    }

    /**
     * @ignore
     * Adds a new Quiz
     *
     * @param array $aVals
     * @param int $iUser
     * @return boolean
     */
    public function _addQuiz($aVals, $iUserId) {
        // empty title
        if (!isset($aVals['sTitle']) or empty($aVals['sTitle']))
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_quiz_title")),
            );

        if (!isset($aVals['sDescription']) or empty($aVals['sDescription']))
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_quiz_description")),
            );

        // case where user had JS disabled
        if (!isset($aVals['aQuestions']))
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_questions")),
            );
        
            
        $aVals['aQuestions'] =  json_decode($aVals['aQuestions'],1);
        
        /**
         * added baned chekc here
         */
        if (!isset($aVals['sAuthView']) || empty($aVals['sAuthView']))
            $aVals['sAuthView'] = 0;

        if (!isset($aVals['sAuthComment']) || empty($aVals['sAuthComment']))
            $aVals['sAuthComment'] = 0;

        // insert to the quiz table:
        $iQuizId = $this -> database() -> insert($this -> _sTable, array(
            'view_id' => $aVals['view_id'] = Phpfox::getUserParam('quiz.new_quizzes_need_moderation') ? 1 : 0,
            'privacy' => $aVals['sAuthView'],
            'privacy_comment' => $aVals['sAuthComment'],
            'user_id' => (int)$iUserId,
            'title' => Phpfox::getLib('parse.input') -> clean($aVals['sTitle']),
            'description' => Phpfox::getLib('parse.input') -> clean($aVals['sDescription'], 255),
            'time_stamp' => PHPFOX_TIME
        ));

        // now we insert the questions and the answers
        foreach ($aVals['aQuestions'] as $aQuestions) {
            // first we need to insert the question to get its ID
            $iQuestionId = $this -> database() -> insert(Phpfox::getT('quiz_question'), array(
                'quiz_id' => $iQuizId,
                'question' => Phpfox::getLib('parse.input') -> clean($aQuestions['sQuestion'])
            ));

            foreach ($aQuestions['aAnswers'] as $aAnswer) {
                $this -> database() -> insert(Phpfox::getT('quiz_answer'), array(
                    'question_id' => $iQuestionId,
                    'answer' => Phpfox::getLib('parse.input') -> clean($aAnswer['sAnswer']),
                    'is_correct' => (int)$aAnswer['bIsCorrect']
                ));
            }
        }

        // Picture upload
        if (Phpfox::getUserParam('quiz.can_upload_picture') && isset($_FILES['image']['name']) && ($_FILES['image']['name'] != '')) {
            $oFile = Phpfox::getLib('file');
            $oImage = Phpfox::getLib('image');
            $aImage = $oFile -> load('image', array(
                'jpg',
                'gif',
                'png'
            ));

            if ($aImage !== false) {
                $sFileName = $oFile -> upload('image', Phpfox::getParam('quiz.dir_image'), $iQuizId);

                // update the poll
                $this -> database() -> update($this -> _sTable, array(
                    'image_path' => $sFileName,
                    'server_id' => Phpfox::getLib('request') -> getServer('PHPFOX_SERVER_ID')
                ), 'quiz_id = ' . $iQuizId);
                // now the thumbnails
                $iSize = Phpfox::getParam('quiz.quiz_max_image_pic_size');

                $oImage -> createThumbnail(Phpfox::getParam('quiz.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('quiz.dir_image') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);

                // Update user space usage
                Phpfox::getService('user.space') -> update(Phpfox::getUserId(), 'quiz', (filesize(Phpfox::getParam('quiz.dir_image') . sprintf($sFileName, '')) + filesize(Phpfox::getParam('quiz.dir_image') . sprintf($sFileName, '_' . $iSize))));
            }
        }

        if (!Phpfox::getUserParam('quiz.new_quizzes_need_moderation')) {
            if (Phpfox::isModule('feed'))
                Phpfox::getService('feed.process') -> add('quiz', $iQuizId, $aVals['sAuthView'], $aVals['sAuthComment']);

            // Update user activity
            Phpfox::getService('user.activity') -> update(Phpfox::getUserId(), 'quiz');
        }

        // if ($aVals['sAuthView'] == '4')
        // Phpfox::getService('privacy.process')->add('quiz', $iQuizId, (isset($aVals['iPrivacyList']) ? $aVals['iPrivacyList'] : array()));

        // Plugin call
        // disable plugin call
        // if ($sPlugin = Phpfox_Plugin::get('quiz.service_process_add__end')){eval($sPlugin);}

        return array(
            'error_code' => 0,
            'message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_have_added_new_quiz")),
            'iQuizId' => $iQuizId,
        );
    }

    /**
     */
    public function getFeedInfo($iQuizId){
        $aItem = $this -> database() 
        -> select("q.*") 
        -> from($this -> _sTable, 'q')
        -> where('q.quiz_id=' . intval($iQuizId))
        -> execute('getSlaveRow');

        if(empty($aItem))
            return array();
        
        if($aItem['image_path']){
             $sImageUrl = Phpfox::getLib('image.helper') -> display(array(
                'server_id' => $aItem['server_id'],
                'path' => 'quiz.url_image',
                'file' => $aItem['image_path'],
                'suffix' => '',
                'return_url' => true
            ));
        }else{
            $sImageUrl  = $this->_getDefaultQuizImagePath();
        }

        return array(
            'iId'=> $aItem['quiz_id'],
            'sModule'=>'quiz',
            'sDescription'=>Phpfox::getService('mfox')->decodeUtf8Compat($aItem['description']),
            'sTitle'=>Phpfox::getService('mfox')->decodeUtf8Compat($aItem['title']),
            'sType'=>'quiz',
            'sPhoto_Url'=> $sImageUrl,
            'sUserName'=>'',
            'sFullName'=>'',
            'sFeedTitle'=>'',
            'iUserId'=>'',
        );
		

    }
    
    /**
     * check quiz permission
     */
    public function _getQuizPermission($iQuizId, $iUserId){
        
        $aOriginalQuiz = $this->database()
            ->select('user_id, title, image_path')
            ->from($this->_sTable)
            ->where('quiz_id = '. (int)$aQuiz['quiz_id'])
            ->execute('getSlaveRow');
       
       $iQuizOwner = $aOriginalQuiz['user_id'];
       
       // check if can edit own items
        $bGuestIsOwner = $iCurrent == $iQuizOwner;
        $bEditOwn = (Phpfox::getUserParam('quiz.can_edit_own_questions') || Phpfox::getUserParam('quiz.can_edit_own_title'));
        $bEditOthers = (Phpfox::getUserParam('quiz.can_edit_others_questions') || Phpfox::getUserParam('quiz.can_edit_others_title'));
        
        
        return array(
            'bCanEditTitle'=> Phpfox::getUserParam('quiz.can_edit_own_title') || Phpfox::getUserParam('quiz.can_edit_others_title'),
            'bCanEditQuestion'=>Phpfox::getUserParam('quiz.can_edit_own_questions') || Phpfox::getUserParam('quiz.can_edit_others_questions'),
            'iMinQuestion'=> Phpfox::getUserParam('quiz.min_questions'),
            'iMaxQuestion'=>Phpfox::getUserParam('quiz.max_questions'),
            'iMinAnswer'=> Phpfox::getUserParam('quiz.min_answers'),
            'iMaxAnswer'=>Phpfox::getUserParam('quiz.max_answers'),
            'bCanAnswerQuiz'=>Phpfox::getUserParam('quiz.can_answer_own_quiz'),
            'bcanApproveQuiz'=>Phpfox::getUserParam('quiz.can_approve_quizzes'),
            'bCanDeleteOtherQuiz'=>Phpfox::getUserParam('quiz.can_delete_others_quizzes'),
            'bNewQuizNeedModeration'=>Phpfox::getUserParam('quiz.new_quizzes_need_moderation'),
            'bCanDeleteOwnQuiz'=>Phpfox::getUserParam('quiz.can_delete_own_quiz'),
            'bCanPostCommentOnQuiz'=>Phpfox::getUserParam('quiz.can_post_comment_on_quiz'),
            'bCanEditOwnQuestion'=>Phpfox::getUserParam('quiz.can_edit_own_questions'),
            'bCanEditOthersQuestion'=>Phpfox::getUserParam('quiz.can_edit_others_questions'),
            'bCanEditOwnTitle'=>Phpfox::getUserParam('quiz.can_edit_own_title'),
            'bCanEditOthersTitle'=>Phpfox::getUserParam('quiz.can_edit_others_title'),
            'iPointQuiz'=>Phpfox::getUserParam('quiz.points_quiz'),
            'bCanViewResultBeforeAnswer'=>Phpfox::getUserParam('quiz.can_view_results_before_answering'),
            'bCanAccessQuiz'=>Phpfox::getUserParam('quiz.can_access_quiz'),
            'bCanCreateQuiz'=>Phpfox::getUserParam('quiz.can_create_quiz'),
            'bCanUploadPicture'=>Phpfox::getUserParam('quiz.can_upload_picture'),
        );
        
    }
    
    /**
     * It deletes the existing questions and answers (if user has permission to edit that)
     * and reinserts, it relies on JS to keep the indexes and runs one query to be able to
     * compare users and set the title right on the "new" quiz.
     * @param array $aQuiz This array holds all the information that is going to be the final quiz
     * @return string on error | true on success
     */
    public function _updateQuiz($aQuiz, $iUser)
    {
        
        // check permissions
        $iCurrent = Phpfox::getUserId();
        
        $aOriginalQuiz = $this->database()
            ->select('user_id, title, image_path')
            ->from($this->_sTable)
            ->where('quiz_id = '. (int)$aQuiz['iQuizId'])
            ->execute('getSlaveRow');
            
        $iQuizOwner = $aOriginalQuiz['user_id'];
        

        // check if can edit own items
        $bGuestIsOwner = $iCurrent == $iQuizOwner;
        $bEditOwn = (Phpfox::getUserParam('quiz.can_edit_own_questions') || Phpfox::getUserParam('quiz.can_edit_own_title'));
        $bEditOthers = (Phpfox::getUserParam('quiz.can_edit_others_questions') || Phpfox::getUserParam('quiz.can_edit_others_title'));
        
        // check if user can edit anything
        if (!$bEditOthers && !$bEditOwn)
            return array(
                'error_code'=>1,
                'error_message'=> Phpfox::getPhrase('quiz.you_do_not_have_the_permission_to_edit_this_quiz'),
            );
            
        if (!isset($aQuiz['sAuthView']) || empty($aQuiz['sAuthView']))
            $aQuiz['sAuthView'] = 0;
        
        if (empty($aQuiz['sAuthComment']))
            $aQuiz['sAuthComment'] = 0;

        if (Phpfox::getUserParam('quiz.can_edit_others_title') && (!$bGuestIsOwner) ||
            Phpfox::getUserParam('quiz.can_edit_own_title') && ($bGuestIsOwner))
        {
            // update title, description and privacy
            $aUpdate = array(
                'privacy' => (isset($aQuiz['sAuthView']) ? $aQuiz['sAuthView'] : '0'),
                'privacy_comment' => (isset($aQuiz['sAuthComment']) ? $aQuiz['sAuthComment'] : '0'),
                'title' => Phpfox::getLib('parse.input')->clean($aQuiz['sTitle']),
                'description' => Phpfox::getLib('parse.input')->clean($aQuiz['sDescription'], 255)
            );
            
            (Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->update('quiz', $aQuiz['quiz_id'], $aQuiz['sAuthView'], (isset($aQuiz['sAuthComment']) ? (int) $aQuiz['sAuthComment'] : 0)) : null);

            // Update picture
            if (Phpfox::getUserParam('quiz.can_upload_picture') && isset($_FILES['image']['name']) && ($_FILES['image']['name'] != ''))
            {
                $oFile = Phpfox::getLib('file');
                $oImage = Phpfox::getLib('image');
                $aImage = $oFile->load('image', array(
                        'jpg',
                        'gif',
                        'png'
                    )
                );

                if ($aImage !== false)
                {
                    $sFileName = $oFile->upload('image', Phpfox::getParam('quiz.dir_image'), (int)$aQuiz['quiz_id']);
                    // update the poll
                    $aUpdate['image_path'] = $sFileName;
                    $aUpdate['server_id'] = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
                    // 'quiz_id = ' . (int)$aQuiz['quiz_id']);
                    // now the thumbnails
                    $iSize = Phpfox::getParam('quiz.quiz_max_image_pic_size');
                    $oImage->createThumbnail(Phpfox::getParam('quiz.dir_image') . sprintf($sFileName, ''), Phpfox::getParam('quiz.dir_image') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);

                    if (file_exists(Phpfox::getParam('quiz.dir_image') . sprintf($sFileName, '')) && 
                        file_exists(Phpfox::getParam('quiz.dir_image') . sprintf($sFileName, '_' . $iSize)) &&
                        isset($aOriginalQuiz['image_path']) && !empty($aOriginalQuiz['image_path']))
                    {
                        // delete the old picture                       
                            Phpfox::getLib('file')->unlink(Phpfox::getParam('quiz.dir_image') . sprintf($aOriginalQuiz['image_path'], ''));
                            Phpfox::getLib('file')->unlink(Phpfox::getParam('quiz.dir_image') . sprintf($aOriginalQuiz['image_path'], '_' . $iSize));
                            // get space used by current picture
                            $iOldPictureSpaceUsed = (filesize(Phpfox::getParam('quiz.dir_image') . sprintf($aOriginalQuiz['image_path'], '')) + filesize(Phpfox::getParam('quiz.dir_image') . sprintf($aOriginalQuiz['image_path'], '_' . $iSize)));
                            // decrease the count for the old picture
                            Phpfox::getService('user.space')->update((int)$iUser, 'quiz', $iOldPictureSpaceUsed, '-');
                    }

                    // Update user space usage with the new picture
                    Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'quiz', (filesize(Phpfox::getParam('quiz.dir_image') . sprintf($sFileName, '')) + filesize(Phpfox::getParam('quiz.dir_image') . sprintf($sFileName, '_' . $iSize))));
                }
            }

            $this->database()->update($this->_sTable, $aUpdate, 'quiz_id = ' . (int)$aQuiz['iQuizId']);
        }


        if (isset($aQuiz['aQuestions']) && ((Phpfox::getUserParam('quiz.can_edit_others_questions') && !$bGuestIsOwner) ||
                (Phpfox::getUserParam('quiz.can_edit_own_questions') && $bGuestIsOwner)))
        {
            
             $aQuiz['aQuestions'] =  json_decode($aQuiz['aQuestions'],1);
        
        

            // Step 1 : Delete all the questions from this quiz.
            $aFormerQuestions = $this->database()->select('qq.question_id')
            ->from(Phpfox::getT('quiz_question'), 'qq')
            ->where('qq.quiz_id = ' . (int)$aQuiz['iQuizId'])
            ->execute('getSlaveRows');
            

            $sQuestionId = '';
            foreach ($aFormerQuestions as $aFormer)
            {
                $sQuestionId .= ' OR question_id = '.$aFormer['question_id'];
            }
            $sQuestionId = substr($sQuestionId, 4);
            
            if($sQuestionId){
                // Step 1. Delete all current answers and questions
                $this->database()->delete(Phpfox::getT('quiz_question'), $sQuestionId);
                $this->database()->delete(Phpfox::getT('quiz_answer'), $sQuestionId);
            }
            foreach ($aQuiz['aQuestions'] as $aKey => $aQuestion)
            {
                // Step 2. Insert the question
                $aQuestionInsert = array(
                        'question' => $aQuestion['sQuestion'],
                        'quiz_id' => $aQuiz['iQuizId']
                );

                // safer if we get the question_id from the answer
                $aFirstAnswer = reset($aQuestion['answers']);
                $iQuestionId = $aFirstAnswer['question_id'];
                if (isset($aQuestion['question_id']))
                { // it means we're updating
                    $aQuestionInsert['question_id'] = $iQuestionId;
                }
                $iQuestionId = $this->database()->insert(Phpfox::getT('quiz_question'), $aQuestionInsert);

                // Step 3 Insert the answers
                foreach ($aQuestion['aAnswers'] as $aAnswer)
                {
                    $aAnswerInsert = array(
                        'question_id' => $iQuestionId,
                        'answer' => $aAnswer['sAnswer'],
                        'is_correct' => $aAnswer['bIsCorrect']
                    );
                    if (isset($aAnswer['answer_id']) && !empty($aAnswer['answer_id']))
                    {
                        // An update means Delete + Insert
                        $aAnswerInsert['answer_id'] = $aAnswer['answer_id'];
                    }
                    $this->database()->insert(Phpfox::getT('quiz_answer'), $aAnswerInsert);
                } // end loop answers
            } // end loop questions
        } // end editing questions/answers
        
        
        return array(
            'error_code'=>0,
            'message'=>'Updated',
        );
    }
}
