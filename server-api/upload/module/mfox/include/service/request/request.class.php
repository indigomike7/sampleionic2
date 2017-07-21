<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Request_Request extends Phpfox_Service
{
    /**
     * Request data
     *
     * @var array
     */
    private $_aArgs = array();
    
    /**
     * List of requests being checked.
     *
     * @var array
     */
    private $_aName = array();
    
    /**
     * Last name being checked.
     *
     * @var string
     */
    private $_sName;

    /**
     * Class Constructor used to build the variable $this->_aArgs.
     * 
     */
    public function __construct()
    {
        
    }

    /**
     * @return Mfox_Service_Request_Request
     */
    public static function instance()
    {
        return Phpfox::getService('mfox.request');
    }
    
    /** 
     * Retrieve parameter value from request.
     * 
     * @param string $sName name of argument
     * @param string $sCommand is any extra commands we need to execute
     * @return string parameter value
     */
    public function get($sName = null, $mDef = '')
    {
        if ($this->_sName)
        {
            $sName = $this->_sName;
        }

        if ($sName === null) {
            return (object) $this->_aArgs;
        }
        
        return (isset($this->_aArgs[$sName]) ? ((empty($this->_aArgs[$sName]) && isset($this->_aName[$sName])) ? true : $this->_aArgs[$sName]) : $mDef);
    }

    /**
     * Set a request manually.
     *
     * @param mixed $mName ARRAY include a name and value, STRING just the request name.
     * @param string $sValue If the 1st argument is a string this must be the request value.
     */
    public function set($mName, $sValue = null)
    {
        if (!is_array($mName))
        {
            $mName = array($mName => $sValue);
        }
        
        foreach ($mName as $sKey => $sValue)
        {
            $this->_aArgs[$sKey] = $sValue;
        }
    }
    
    /**
     * Get a request and convert it into an INT.
     *
     * @param string $sName Name of the request.
     * @param string $mDef Default value in case the request does not exist.
     * @return int INT value of the request.
     */
    public function getInt($sName, $mDef = '')
    {
        return (int)$this->get($sName, $mDef);
    }    
    
    /**
     * Get a request and make sure it is an ARRAY.
     *
     * @param string $sName Name of the request.
     * @param array $mDef ARRAY of default values in case the request does not exist.
     * @return array Returns an ARRAY value.
     */
    public function getArray($sName, $mDef = array())
    {       
        return (array)(isset($this->_aArgs[$sName]) ? $this->_aArgs[$sName] : $mDef);
    }       
    
    /**
     * Get all the requests.
     *
     * @return array
     */
    public function getRequests()
    {
        return (array)$this->_aArgs;
    }
}

?>
