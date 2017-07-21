<?php

if (!defined('PHPFOX')) {
	define('PHPFOX', true);
	define('PHPFOX_DS', DIRECTORY_SEPARATOR);
	define('PHPFOX_DIR', dirname(dirname(dirname(__FILE__))) . PHPFOX_DS);
	define('PHPFOX_START_TIME', array_sum(explode(' ', microtime())));
}

define('PHPFOX_NO_RUN', true);

require(PHPFOX_DIR . 'start.php');

define('MAX_SIZE_OF_USER_IMAGE', '_50_square');
define('MAX_SIZE_OF_USER_IMAGE_EVENT', '_50');
define('MAX_SIZE_OF_USER_IMAGE_PHOTO', '_150');
define('MAX_SIZE_OF_IMAGE_ALBUM', '_200');

/**
 * skip check post token
 * @see ./include/library/phpfox/phpfox.class.php
 */
define('PHPFOX_NO_CSRF', TRUE);

/**
 * skip save page
 * @see ./include/library/phpfox/phpfox.class.php
 */
define('PHPFOX_DONT_SAVE_PAGE', TRUE);

/**
 * @see ./include/init.inc.php: PHPFOX_NO_PLUGINS
 * skip plugins
 */
// define('PHPFOX_NO_PLUGINS', TRUE);

/**
 * @see ./include/init.inc.php: PHPFOX_NO_SESSION
 * skip session init
 */
define('PHPFOX_NO_SESSION', TRUE);

/**
 * @see ./include/init.inc.php: PHPFOX_NO_USER_SESSION
 *
 */
define('PHPFOX_NO_USER_SESSION', TRUE);

defined('PHPFOX_MOBILE_MODE') or define('PHPFOX_MOBILE_MODE',TRUE);


/**
 * start init process.
 */
include_once PHPFOX_DIR . '/module/mfox/fn.inc.php';
include_once PHPFOX_DIR . '/module/mfox/include/library/ynlog.php';


// nothing for some issue.
if (function_exists('ini_set'))
{
	ini_set('display_startup_errors', 1);
	ini_set('display_errors', 1);
	error_reporting(E_ERROR);
}



/**
 * set error handler
 */
set_error_handler(array(
	'Ynlog',
	'handleError'
));

/**
 * set exception handler
 */
set_exception_handler(array(
	'Ynlog',
	'handleException'
));
/**
 * Register the shutdown PHP script function.
 * If there is a fatal error, this function will clear all buffer and return the error json.
 */
register_shutdown_function(array(
	'Ynlog',
	'handeShutdown'
));



/**
 * @var string
 */
define('MFOX_TOKEN_KEY','token');

define('MFOX_TOKEN_KEY_HTTP','HTTP_TOKEN');

// if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    // exit("1");
// }



header('Access-Control-Allow-Headers: token, content-type, foxlang');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Request-Method: GET, POST');




if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
	exit("{}	");
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(empty($_POST)){
        $_POST = (array)json_decode(file_get_contents('php://input'),true);
    };
}


/**
 * set shutdown function
 */
// register_shutdown_function(array('Ynlog','handleShutdown'));

$sUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

$sUri = trim($sUri, '/');

if ($pos = strpos($sUri, '.php'))
{
	$sUri = substr($sUri, $pos + 5);
}

$requestMethod = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
$actionType = 1;
$sMethod =  'get';

/**
 * generate data
 */
$aData = array_merge($_GET, $_POST);
$iId = NULL;
$sService = null;



if (preg_match("#^(\w+)\/(\d+)#", $sUri, $matches))
{
	$actionType =  1;
	$sService = $matches[1];
	$sMethod = strtolower($requestMethod) . 'ByIdAction';
	$iId = $matches[2];
}
else
if (preg_match("#^(\w+)\/(\w+)#", $sUri, $matches))
{
	$actionType =  2;
	$sService = $matches[1];
	$sMethod = $matches[2];
	$iId = NULL;
}
else if (preg_match("#^(\w+)#", $sUri, $matches))
{
	$actionType = 3;
	$sService = $matches[1];
	$sMethod = strtolower($requestMethod) . 'Action';
	$iId = NULL;
}

$sService = str_replace('/', '.', 'mfox/' . $sService);

$isResful = FALSE;

if (!Phpfox::isModule('mfox'))
{
    echo json_encode(array(
		'error_code' => 1,
		'error_message' => "mobile application module is disabled!"
	));
    die;
}

$mFox = Phpfox::getService('mfox');

$oService = NULL;

Phpfox::getService('user.auth') -> setUserId(0);

if (!$mFox -> hasService($sService))
{
	echo json_encode(array(
		'error_code' => 1,
		'error_message' => "Invalid service [{$sService}] request URI [{$sUri}]"
	));
    die;
}
else
{
	// Call the service.
	$oService = Phpfox::getService($sService);
}

global $token;

if (isset($aData[MFOX_TOKEN_KEY]))
{
	$token = $aData[MFOX_TOKEN_KEY];
}
else
if (!$token && isset($_SERVER[MFOX_TOKEN_KEY_HTTP]))
{
	$token = $_SERVER[MFOX_TOKEN_KEY_HTTP];
}
else
if (!$token && function_exists('apache_request_headers'))
{
	

	$headers = apache_request_headers();

	if (isset($headers[MFOX_TOKEN_KEY]))
	{
		$token  =  $headers[MFOX_TOKEN_KEY];
	}

	$key = strtolower(MFOX_TOKEN_KEY);

	if (isset($headers[MFOX_TOKEN_KEY]))
	{
		$token  =  $headers[$key];
	}
}

$mFox -> log($aData);


/**
 * check if token is exsits.
 */
if (($sService != 'token') 
	&& ( $sService != 'user'
        && $sMethod != 'ipad_css'
        && $sMethod != 'iphone_css'
        && $sMethod != 'android_css'
        && $sMethod != 'phrases'
        && $sMethod != 'languages'
		&& $sMethod != 'login' 
		&& $sMethod != 'register'
		&& $sMethod != 'forgot'
	 	&& $sMethod != 'sidebar'
	 	&& $sMethod != 'verify_account'
	 	&& $sMethod != 'settings'
	 	&& $sMethod != 'ping'
	 	&& $sMethod != 'signup_term'
	 	&& $sMethod != 'getsubscriptionpackages'
		)
)
{
	extract($aData, EXTR_SKIP);

	$aResult = Phpfox::getService('mfox.token') -> isValid($token);

	// Is not valid.
	if (count($aResult) > 0)
	{
		echo json_encode($aResult);

		ob_end_flush();
		die ;
	}
}

// verify token at first.
if ($token)
{
	$aToken = Phpfox::getService('mfox.token') -> getToken($token);
	
		
	if ($aToken && isset($aToken['user_id']))
	{
		$iViewerId  =  (int)$aToken['user_id'];
		
		$oAuth = Phpfox::getService('user.auth') ; 
		$oAuth -> setUserId($iViewerId);
		$aUser = Phpfox::getService('user')->get($iViewerId);
		$oAuth->setUser($aUser);
	}
	
	$mFox->log(array('iViewerId'=>Phpfox::isUser()));
}

$bCheckSubscribe = false;
if (Phpfox::isModule('subscribe')
	&& ( $subscribe != 'user' 
		&& $sMethod != 'detail' 
		&&  $sMethod != 'transactionadd' 
		&& $sMethod != 'transactionupdate'
		)
)
{
	$bCheckSubscribe = true;
}


/*set session*/
if(Phpfox::getUserId() > 0){
	Phpfox::getService('mfox.helper.user')->setUserSession();
}
/*end set session*/

// subscribe id in user field which is purchase id in subscribe module 
$iPurchaseId = Phpfox::getService('mfox.user') -> getSubscribeIdByUserId(Phpfox::getUserId());
if($bCheckSubscribe == true && (int)$iPurchaseId > 0){
	$aPurchase = Phpfox::getService('subscribe.purchase')->getPurchase($iPurchaseId);
	$aResult = array(
                'result' => 0,
                'error_code' => 1,
                'error_message' => 'PHPFOX_MUST_PAY_FIRST', 
                'iPurchaseId' => $iPurchaseId, 
                'iPackageId' => isset($aPurchase['package_id']) ? $aPurchase['package_id'] : 0, 
            );
} else {
	$aResult = $oService -> {$sMethod}($aData, $iId);	
}

header('content-type: application/json');
ob_start();

// since 3.08p2
// decode html for mobile view.
//

if(!empty($aResult['error_message'])){
    $aResult['error_message'] =  Phpfox::getService('mfox')->decodeUtf8Compat($aResult['error_message']);
}else if(!empty($aResult['message'])){
    $aResult['message'] =  Phpfox::getService('mfox')->decodeUtf8Compat($aResult['message']);
}


$content = json_encode($aResult);


$mFox -> log($content);

while(ob_get_level()){
	ob_get_clean();
}


echo $content;
exit(0);
