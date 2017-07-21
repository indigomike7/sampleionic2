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
 * @link Mfox Api v2.0
 */
class Mfox_Service_Push extends Phpfox_Service {

    /**
     * Save data to push.
     * @param array $aData
     * @return int
     */
    public function savePush($aData, $iUserId)
    {
        /**
         * @var array
         */
        $aVals = array(
            'user_id' => $iUserId,
            'data' => json_encode($aData),
            'created_at' => PHPFOX_TIME
        );
        
        return $this->database()->insert(Phpfox::getT('mfox_push'), $aVals);
    }

    /**
     * Get push data.
     * @param int $iPushId
     * @return array 
     */
    public function getPush($iPushId)
    {
        /**
         * @var array
         */
        $aPush = $this->database()
                ->select('*')
                ->from(Phpfox::getT('mfox_push'))
                ->where('push_id = ' . (int) $iPushId)
                ->execute('getRow');
        
        if (isset($aPush['data']) && $aData = json_decode($aPush['data']))
        {
            return $aData;
        }
        else
        {
            return array();
        }
    }
    /**
     * Input data:
     * + iPushId: int, required. Get it from push notification service.
     * 
     * Output data:
     * + sType: string.
     * + iItemId: int.
     * + iItem: int.
     * + sModule: string.
     * 
     * @see Mobile - API phpFox/Api V3.0
     * @see push/accessPushNotification
     * 
     * @param array $aData
     * @return array
     */
    public function accessPushNotification($aData)
    {
        /**
         * @var int
         */
        $iPushId = isset($aData['iPushId']) ? (int) $aData['iPushId'] : 0;
        /**
         * @var array
         */
        $aData = $this->getPush($iPushId);
        
        return $aData;
    }
    
}

?>
