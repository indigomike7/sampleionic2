<?php

if (!defined('PHPFOX')) {
	define('PHPFOX', true);
	define('PHPFOX_DS', DIRECTORY_SEPARATOR);
	define('PHPFOX_DIR', dirname(dirname(dirname(__FILE__))) . PHPFOX_DS);
	define('PHPFOX_START_TIME', array_sum(explode(' ', microtime())));
}

define('PHPFOX_NO_RUN', true);

require(PHPFOX_DIR . 'start.php');

// nothing for some issue.
if (function_exists('ini_set'))
{
    ini_set('display_startup_errors', 1);
    ini_set('display_errors', 1);
    error_reporting(E_ERROR);
}

?>