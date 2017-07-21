<?php

defined('PHPFOX') or exit('NO DICE!');
class Mfox_Component_Controller_Admincp_Manageskp extends Phpfox_Component
{

    /**
     * Class process method wnich is used to execute this component.
     */
    public function process()
    {
        if ($aVals = $this->request()->getArray('val'))
        {
	        switch ($aVals['module_id']) {
	        	case 'subscribe':
		    		Phpfox::getService('mfox.transaction')->deleteStoreKitPurchaseByModuleId($aVals['module_id'], $aVals['device']);
	        		foreach($aVals['package_id'] as $key => $val){
			    		$id = Phpfox::getService('mfox.transaction')->insertStoreKitPurchaseByModuleId(array(
							'storekitpurchase_key' => $aVals['storekitpurchase_key'][$key],
							'storekitpurchase_module_id' => $aVals['module_id'],
							'storekitpurchase_type' => 'purchase_product',			
							'storekitpurchase_item_id' => $val,			
							'storekitpurchase_device' => $aVals['device'],
						));
	        		}
	        		break;
	        	
	        	default:
		    		Phpfox::getService('mfox.transaction')->deleteStoreKitPurchaseByModuleId($aVals['module_id'], $aVals['device']);
		    		$id = Phpfox::getService('mfox.transaction')->insertStoreKitPurchaseByModuleId(array(
						'storekitpurchase_key' => $aVals['storekitpurchase_key'],
						'storekitpurchase_module_id' => $aVals['module_id'],
						'storekitpurchase_type' => 'purchase_product',			
						'storekitpurchase_item_id' => 0,			
						'storekitpurchase_device' => $aVals['device'],
					));
	        		break;
	        }

			$this->url()->send('admincp.mfox.manageskp', array('moduleid' => $aVals['module_id'], 'device' => $aVals['device']),  Phpfox::getPhrase('mfox.save_successfully'));
        }
        
        $aModules = Phpfox::getLib('module')->getModules();
        foreach ($aModules as $key => $value) {
        	$aModules[$key] = ucfirst($value);
        }
        
        reset($aModules);
		$sActiveModuleId = key($aModules);
		$sActiveDevice = 1;
        if ($sModuleId = $this->request()->get('moduleid'))
        {
        	$sActiveModuleId = $sModuleId;
        }
        if ($iDevice = $this->request()->get('device'))
        {
        	$sActiveDevice = $iDevice;
        }
		$sActiveKey = '';
        switch ($sActiveModuleId) {
        	case 'subscribe':
        		$aPackages = Phpfox::getService('mfox.helper')->generateSubscribeSKPData($sActiveModuleId, $sActiveDevice);
		        $this->template()->assign(array(
		                        'aPackages' => $aPackages, 
		                        )
		        );
        		break;
        	
        	default:
				$aStoreKitPurchase = Phpfox::getService('mfox.transaction')->getStoreKitPurchaseByModuleId($sActiveModuleId, null, $sActiveDevice);
				if(isset($aStoreKitPurchase['storekitpurchase_id'])){
					$sActiveKey = $aStoreKitPurchase['storekitpurchase_key'];
				}
        		break;
        }

        $aDevice = Phpfox::getService('mfox.helper')->getAllDevice();
        $this->template()->setTitle( Phpfox::getPhrase('mfox.manage_store_kit_product_on_iap'))
                ->setBreadcrumb( Phpfox::getPhrase('mfox.manage_store_kit_product_on_iap'), $this->url()->makeUrl('admincp.mfox.manageskp'))
			->setHeader(array(
					'manageskp.js' => 'module_mfox',
				)
			)
                ->assign(array(
                        'aModules' => $aModules, 
                        'sActiveModuleId' => $sActiveModuleId, 
                        'sActiveKey' => $sActiveKey, 
                        'sActiveDevice' => $sActiveDevice, 
                        'aDevice' => $aDevice['support'], 
                        )
        );
    }
}

?>