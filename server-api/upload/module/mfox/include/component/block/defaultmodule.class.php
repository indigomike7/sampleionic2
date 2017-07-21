<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Component_Block_Defaultmodule extends Phpfox_Component {

	/**
	 * Class process method wnich is used to execute this component.
	 */
	public function process() {
		$sActiveKey = $this->getParam('sActiveKey');
        $this->template()->assign(array(
            'sActiveKey' => $sActiveKey, 
            )
        );
		return 'block';
	}

}

?>
