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
 * @link Mfox Api v1.0
 */
class Mfox_Service_Privacy extends Phpfox_Service
{
    public $service;
    public $isCount = false;
    public $condition = [];

    /**
     * Class Constructor
     * 
     */
    public function __construct()
    {
        
    }

    /**
     * @return Mfox_Service_Privacy
     */
    public static function instance()
    {
        return Phpfox::getService('mfox.privacy');
    }

    /**
     * Input data: N/A
     * 
     * Output data:
     * + iListId: int.
     * + sName: string.
     * + bUsed: bool.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see privacy/getfriends
     * 
     * @param array $aData
     * @return array
     */
    public function getfriends($aData)
    {
        /**
         * @var array
         */
        $aList = Phpfox::getService('friend.list')->get();
        $aResult = array();
        foreach($aList as $aItem)
        {
            $aResult[] = array(
                'iResourceId' => $aItem['list_id'],
                'sName' => $aItem['name'],
                // 'bUsed' => $aItem['used']
            );
        }
        return $aResult;
    }
    
    /**
     * Input data:
     * + bPrivacyNoCustom: bool.
     * 
     * Output data:
     * + sPhrase: string.
     * + iValue: int.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see privacy/privacy
     * 
     * @param array $aData
     * @return array
     */
    public function privacy($aData)
    {
        /**
         * @var array
         */
        $aPrivacyControls = array();
        
        // if (!Phpfox::getParam('core.friends_only_community'))
        {
            $aPrivacyControls[] = array(
                'sPhrase' => Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('privacy.everyone')),
                'sValue' => '0'
            );
        }
        
        if (Phpfox::isModule('friend'))
        {
            $aPrivacyControls[] = array(
                'sPhrase' => Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('privacy.friends')),
                'sValue' => '1'
            );
            $aPrivacyControls[] = array(
                'sPhrase' => Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('privacy.friends_of_friends')),
                'sValue' => '2'
            );
        }

        $aPrivacyControls[] = array(
            'sPhrase' => Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('privacy.only_me')),
            'sValue' => '3'
        );

        if (!isset($aData['bPrivacyNoCustom']))
        {
            $aData['bPrivacyNoCustom'] = false;
        }
        
        // skip this
        if (false && Phpfox::isModule('friend'))
        {
            if (isset($aData['bPrivacyNoCustom']) && $aData['bPrivacyNoCustom'])
            {
                $sCustomPhrase = preg_replace('/<span>(.*)<\/span>/i', '',  Phpfox::getPhrase('privacy.custom_span_click_to_edit_span'));
            }
            else
            {
                // $sCustomPhrase = strip_tags( Phpfox::getPhrase('privacy.custom_span_click_to_edit_span'));
                $sCustomPhrase = preg_replace('/<span>(.*)<\/span>/i', '',  Phpfox::getPhrase('privacy.custom_span_click_to_edit_span'));
                $aPrivacyControls[] = array(
                    'sPhrase' => $sCustomPhrase,
                    'sValue' => '4'
                );
            }
        }
        
        return $aPrivacyControls;
    }

    /**
     * @param string $sDefault, array $aPrivacy
     * @return int
     */
    public function defaultprivacy($sDefault = '0', $aPrivacy)
    {
        $bInArray = false;
        
        foreach ($aPrivacy as $key => $value) {
            if ($value['sValue'] == $sDefault) {
                $bInArray = true;
            }
        }

        if (!$bInArray) {
            $sDefault = $aPrivacy[0]['sValue'];
        }

        return $sDefault;
    }
    
    /**
     * Input data: N/A
     * 
     * Output data:
     * + sPhrase: string.
     * + iValue: int.
     * 
     * @see Mobile - API phpFox/Api V2.0
     * @see privacy/privacycomment
     * 
     * @param array $aData
     * @return array
     */
    public function privacycomment($aData)
    {
        return array();
        
        // $aData['bPrivacyNoCustom'] = true;
        
        // return $this->privacy($aData);
    }

    public function buildPrivacy($aCond = array())
    {       
        $bIsCount = (isset($aCond['count']) ? true : false);
        
        $oObject = Phpfox::getService($aCond['service']);

        $this->service = $oObject;
        $this->isCount = $bIsCount;
        $this->condition = $aCond;

        if ($sPlugin = Phpfox_Plugin::get('privacy.service_privacy_buildprivacy')) {
            eval($sPlugin);
        }

        if (isset($callback) && is_callable($callback)) {
            return call_user_func($callback, $this);
        }

        if (Phpfox::getUserParam('core.can_view_private_items'))
        {
            $oObject->getQueryJoins($bIsCount, true);

            // http://www.phpfox.com/tracker/view/14708/
            if(!$bIsCount && isset($aCond['join']) && !empty($aCond['join']))
            {
                $this->database()->leftjoin(
                    $aCond['join']['table'],
                    $aCond['join']['alias'],
                    $aCond['join']['alias'] . "." . $aCond['join']['field'] . ' = ' . $aCond['alias'] . "." . $aCond['field']
                );
            }
            // p($this->_search()->getConditions()); exit;
            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])
                ->where(str_replace('%PRIVACY%', '0,1,2,3,4', $this->_search()->getConditions()))
                ->union();

            return null;
        }
        
        $aUserCond = array();
        $aFriendCond = array();
        $aFriendOfFriends = array();
        $aCustomCond = array();
        $aPublicCond = array();
        foreach ($this->_search()->getConditions() as $sCond)
        {           
            $aFriendCond[] = str_replace('%PRIVACY%', '1,2', $sCond);
            $aFriendOfFriends[] = str_replace('%PRIVACY%', '2', $sCond);
            $aUserCond[] = str_replace('%PRIVACY%', (Phpfox::getParam('core.friends_only_community') ? '' : '') . '1,2,3,4', $sCond);
            $aCustomCond[] = str_replace('%PRIVACY%', '4', $sCond);
            $aPublicCond[] = str_replace('%PRIVACY%', '0', $sCond);
        }       
        
        // Users items
        if (Phpfox::isUser())
        {                           
            $oObject->getQueryJoins($bIsCount, true);
                    
            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])
                ->where(array_merge(array('AND ' . $aCond['alias'] . '.user_id = ' . Phpfox::getUserId()), $aUserCond))                                 
                ->union();
        }               
            
        // Items based on custom lists
        if (Phpfox::isUser())
        {           
            $oObject->getQueryJoins($bIsCount);
                                        
            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])                        
                ->join(Phpfox::getT('privacy'), 'p', 'p.module_id = \'' . str_replace('.', '_', $aCond['module_id']) . '\' AND p.item_id = ' . $aCond['alias'] . '.' . $aCond['field'])
                ->join(Phpfox::getT('friend_list_data'), 'fld', 'fld.list_id = p.friend_list_id AND fld.friend_user_id = ' . Phpfox::getUserId() . '')
                ->where($aCustomCond)                                   
                ->union();
        }                   
            
        // Friend of friends items      
        if (!Phpfox::getParam('core.friends_only_community') && Phpfox::isUser())
        {           
            $oObject->getQueryJoins($bIsCount);

            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])        
                ->join(Phpfox::getT('friend'), 'f1', 'f1.is_page = 0 AND f1.user_id = ' . $aCond['alias'] . '.user_id')
                ->join(Phpfox::getT('friend'), 'f2', 'f2.is_page = 0 AND f2.user_id = ' . Phpfox::getUserId() . ' AND f2.friend_user_id = f1.friend_user_id')
                ->where(array_merge(array($aCond['alias'] . '.user_id = f1.user_id AND ' . $aCond['alias'] . '.user_id != ' . Phpfox::getUserId() . ''), $aFriendOfFriends))                
                ->union();
        }       
                
        // Friends items                    
        if (Phpfox::isUser())
        {           
            $oObject->getQueryJoins($bIsCount, true);

            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])
                ->join(Phpfox::getT('friend'), 'f', 'f.is_page = 0 AND f.user_id = ' . $aCond['alias'] . '.user_id AND f.friend_user_id = ' . Phpfox::getUserId())
                ->where($aFriendCond)
                ->union();  
        }

        $forcePublic = false;
        if (in_array($this->request()->segment(1), ['marketplace'])) {
            // $forcePublic = true;
        }
        
        if (Phpfox::getParam('core.friends_only_community')
            && !$forcePublic
        )
        {
            // Public items
            $oObject->getQueryJoins($bIsCount);
                        
            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])                
                ->where(array_merge(array('AND ' . $aCond['alias'] . '.user_id != ' . Phpfox::getUserId()), $aPublicCond))
                ->union();
            
            // Public items for the specific user
            $oObject->getQueryJoins($bIsCount, true);
                            
            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])                
                ->where(array_merge(array('AND ' . $aCond['alias'] . '.user_id = ' . Phpfox::getUserId()), $aPublicCond))
                ->union();          
        }
        else 
        {
            // Public items
            $oObject->getQueryJoins($bIsCount);

            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])                
                ->where($aPublicCond)                       
                ->union();          
        }
    }

    /**
     * Mfox_Service_Search_Search
     */
    private function _search()
    {
        return Mfox_Service_Search_Search::instance();
    }
}
