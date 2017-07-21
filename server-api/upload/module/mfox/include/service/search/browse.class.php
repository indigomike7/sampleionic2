<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Search_Browse extends Phpfox_Service
{
    /**
     * Item count.
     * 
     * @var int
     */
    private $_iCnt = 0;
    
    /**
     * ARRAY of items
     * 
     * @var array
     */
    private $_aRows = array();
    
    /**
     * ARRAY of params we are going to work with.
     * 
     * @var array
     */
    private $_aParams = array();
    
    /**
     * Service object for the specific module we are working with
     * 
     * @var object
     */
    private $_oBrowse = null;
    
    /**
     * Short access to the "view" request.
     * 
     * @var string
     */
    private $_sView = '';

    /**
     * Class constructor.
     *
     */
    public function __construct()
    {
        
    }

    /**
     * @return Mfox_Service_Search_Browse
     */
    public static function instance() {
        return Phpfox::getService('mfox.search.browse');
    }
    
    /**  
     * Set the params for the browse routine.
     *   
     * @param array $aParams ARRAY of params.
     * @return Phpfox_Search_Browse
     */
    public function params($aParams)
    {
        $this->_aParams = $aParams;
        
        $this->_oBrowse = Phpfox::getService($this->_aParams['service']);
        
        $this->_sView = $this->_request()->get('view');
        
        if ($this->_sView == 'friend')
        {
            Phpfox::isUser(true);
        }
        
        return $this;
    }
    
    /**
     * 
     * Execute the browse routine. Runs the SQL query.
     */
    public function execute()
    {
        $aActualConditions = (array) $this->_search()->getConditions();
        
        $this->_aConditions = array();
        foreach ($aActualConditions as $sCond)
        {
            switch ($this->_sView)
            {
                case 'friend':
                    $this->_aConditions[] = str_replace('%PRIVACY%', '0,1,2', $sCond);
                    break;
                case 'my':
                    $this->_aConditions[] = str_replace('%PRIVACY%', '0,1,2,3,4', $sCond);
                    break;              
                case 'pages_member':
                    $this->_aConditions[] = str_replace('%PRIVACY%', '0,1', $sCond);
                    break;
                case 'pages_admin':
                    $this->_aConditions[] = str_replace('%PRIVACY%', '0,1,2', $sCond);
                    break;
                default:
                    $this->_aConditions[] = str_replace('%PRIVACY%', '0', $sCond);
                    break;
            }
        }

        if (Phpfox::getParam('core.section_privacy_item_browsing')
            && (isset($this->_aParams['hide_view']) && !in_array($this->_sView, $this->_aParams['hide_view'])))
        {
            Mfox_Service_Privacy::instance()->buildPrivacy($this->_aParams);
            
            $this->database()->unionFrom($this->_aParams['alias']);
        }
        else 
        {
            $this->_oBrowse->getQueryJoins();
            
            $this->database()->from($this->_aParams['table'], $this->_aParams['alias'])->where($this->_aConditions);
        }       

        $this->_oBrowse->query();

        $this->_aRows = $this->database()->select($this->_aParams['alias'] . '.*, ' . (isset($this->_aParams['select']) ? $this->_aParams['select'] : '') . Phpfox::getUserField())
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ' . $this->_aParams['alias'] . '.user_id')
            ->order($this->_search()->getSort())
            ->limit($this->_search()->getPage(), $this->_search()->getDisplay(), $this->_iCnt, false, false)
            ->execute('getSlaveRows');

        if ($this->_search()->getPage() > 0 && count($this->_aRows) < 1)
        {
            // throw error('no_items');
        }
        
        if (method_exists($this->_oBrowse, 'processRows'))
        {
            $this->_oBrowse->processRows($this->_aRows);
        }
    }
    
    /**
     * Gets the count.
     * 
     * @return int Total items.
     */
    public function getCount()
    {
        return (int) $this->_iCnt;
    }
    
    /**
     * Get items
     * 
     * @return array ARRAY of items.
     */
    public function getRows()
    {
        return (array) $this->_aRows;
    }
    
    /**
     * Extends database class
     * 
     * @see Phpfox_Database
     * @return object Returns database object
     */
    public function database()
    {
        return Phpfox_Database::instance();
    }
    
    /**
     * Extends search class
     * 
     * @see Phpfox_Search
     * @return Phpfox_Search
     */
    public function search()
    {
        return Phpfox_Search::instance();
    }
    
    /**
     * Reset the search
     *
     */
    public function reset()
    {
        $this->_aRows = array();
        $this->_iCnt = 0;
        $this->_aConditions = array();
        $this->_aParams = array();
        
        Phpfox_Search::instance()->reset();
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
}

?>
