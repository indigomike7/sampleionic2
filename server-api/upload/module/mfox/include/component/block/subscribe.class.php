<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Component_Block_Subscribe extends Phpfox_Component {

	/**
	 * Class process method wnich is used to execute this component.
	 */
	public function process() {
		$module_id = $this->getParam('module_id');
		$device = $this->getParam('device');
		$aPackages = Phpfox::getService('mfox.helper')->generateSubscribeSKPData($module_id, $device);
        $this->template()->assign(array(
                        'aPackages' => $aPackages, 
                        )
        );
		
		return 'block';
	}

}

?>
