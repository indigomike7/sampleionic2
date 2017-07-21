<?php

if (!defined('PHPFOX')) {
	define('PHPFOX', true);
	define('PHPFOX_DS', DIRECTORY_SEPARATOR);
	define('PHPFOX_DIR', dirname(dirname(dirname(__FILE__))) . PHPFOX_DS);
	define('PHPFOX_START_TIME', array_sum(explode(' ', microtime())));
}

define('PHPFOX_NO_RUN', true);

require(PHPFOX_DIR . 'start.php');

header("Content-type: text/css", true);
echo Phpfox::getService('mfox.style') -> getCustomCss();
