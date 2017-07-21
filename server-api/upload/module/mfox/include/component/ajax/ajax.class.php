<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Component_Ajax_Ajax extends Phpfox_Ajax {
    /**
     * Update style status.
     */
    public function updateStyleStatus()
    {
        Phpfox::getService('mfox.style')->updateStyleStatus($this->get('id'), $this->get('active'));
    }

    public function loadKeyByModuleId()
    {
        $module_id = $this->get('module_id');
        $device = $this->get('device');
        switch ($module_id) {
            case 'subscribe':
                Phpfox::getBlock('mfox.subscribe', array('module_id' => $module_id, 'device' => $device));
                echo json_encode(array(
                    'type' => 'block', 
                    'content' => $this->getContent(false), 
                ));
                break;
            
            default:
                $aStoreKitPurchase = Phpfox::getService('mfox.transaction')->getStoreKitPurchaseByModuleId($module_id, null, $device);
                Phpfox::getBlock('mfox.defaultmodule', array('sActiveKey' => (isset($aStoreKitPurchase['storekitpurchase_key']) ? $aStoreKitPurchase['storekitpurchase_key'] : '')));
                echo json_encode(array(
                    'type' => 'object', 
                    'content' => $this->getContent(false), 
                ));
                break;
        }

    }
}

?>