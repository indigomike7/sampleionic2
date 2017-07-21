<?php

/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Helper_Database extends Phpfox_Service
{
    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        
    }

    /**
     * Reconnect database
     * @param int $iPeriod Reconnect if over this period in seconds
     */
    public function reconnect($iPeriod = 0)
    {
        $iTime = time();
        $sCacheName = 'mfox_helper_database_reconnect_timestamp';
        $sCacheId = $this->cache()->set($sCacheName);
        $iReturn = $this->cache()->get($sCacheId);

        if (!$iReturn || ($iTime - $iPeriod) >= $iReturn)
        {
            // $this->database()->close();
            $bConnect = $this->database()->connect(Phpfox::getParam(array('db', 'host')), Phpfox::getParam(array('db', 'user')), Phpfox::getParam(array('db', 'pass')), Phpfox::getParam(array('db', 'name')), Phpfox::getParam(array('db', 'port')));
            if ($bConnect)
            {
                $sCacheId = $this->cache()->set($sCacheName);
                $this->cache()->save($sCacheId, $iTime);
            }
        }

        return $iTime;
    }
}
