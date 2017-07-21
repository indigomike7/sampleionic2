<?php
require_once "cli.php";

if(isset($_REQUEST['access_token']))
{
	exit('Connecting with Twitter');
}

$sService = 'twitter';

if (!Phpfox::isModule('socialbridge') 
   || Phpfox::getService('socialbridge')->hasProvider($sService) == FALSE
){
  exit('Administrators does not allow Twitter Connect.');
}         
$Provider = Phpfox::getService('socialbridge') -> getProvider($sService);
$oTwitter = $Provider -> getApi();
$twitter_token = false;

$aParams = $_REQUEST;

if (isset($aParams['bredirect']))
{
	$_SESSION['bredirect'] = $aParams['bredirect'];
}
if (isset($aParams['callbackUrl']))
{
	$_SESSION['callbackUrl'] = urldecode($aParams['callbackUrl']);
}

$bRedirect = isset($_SESSION['bredirect']) ? $_SESSION['bredirect'] : 1;
$sRedirectUrl = isset($_SESSION['callbackUrl']) ? $_SESSION['callbackUrl'] : '';
$sConnected = '';

if (isset($_GET['denied']))
{
	exit('Twitter denied your request. Please try again.');
}

if (isset($aParams['oauth_token']) 
	&& $aParams['oauth_token'] 
	&& isset($aParams['oauth_verifier']) 
	&& $aParams['oauth_verifier']
	)
{
	//	got token, send data to client 
	$oauth_token = $aParams['oauth_token'];
	$oauth_verifier = $aParams['oauth_verifier'];

	$response = $oTwitter -> oAuthAccessToken($oauth_token, $oauth_verifier);

	$oTwitter -> setOAuthToken($response['oauth_token']);
	$oTwitter -> setOAuthTokenSecret($response['oauth_token_secret']);

	// $profile = $Provider -> getProfile();

	$twitter_token = $response['oauth_token'];
	$twitter_secret = $response['oauth_token_secret'];

	$me = $oTwitter -> accountVerifyCredentials();
	$profileImage = isset($me['profile_image_url']) ? ($me['profile_image_url']): ($me['profile_image_url_https']) ;
		
	$profileImage = str_replace("_normal", "", $profileImage);

	$json =  array(
		'id'=> isset($me['id_str']) ? $me['id_str'] : 0,
		'name'=>@$me['name'],
		'screen_name'=>@$me['screen_name'], 
		'profile_image_url'=>base64_encode($profileImage), 
	);

	
	session_destroy();

	$url = '?'. http_build_query(array(
		'access_token'=>$twitter_token, 
		'secret_token' => $twitter_secret, 
		'json_data'=>json_encode($json)),null,'&');

	header('location: ' .$url);

	exit($url);

	echo("Connecting with Twitter ...");
	
	exit;
} else
{
	// not exist token, request to Twitter
	if(isset($_SESSION['twitter_token'])){
		unset($_SESSION['twitter_token']);
	}
	if(isset($_SESSION['twitter_secret'])){
		unset($_SESSION['twitter_secret']);
	}

	// NEED TO CHECK IF USER LOGINED? --> ERROR HAPPENS WHEN TEST ON API
	// IT COULD HAPPEN ON DEVICES OR NOT? 
	$callback = Phpfox::getParam('core.path') . 'module/mfox/twitter.php';
	$oTwitter->setOAuthTokenSecret('');
	$oTwitter->setOAuthTokenSecret('');
	$response = $oTwitter -> oAuthRequestToken($callback);
	if(isset($response['oauth_token']))
	{
		$_SESSION['twitter_token']  = $response['oauth_token'];
	    $_SESSION['twitter_secret'] = $response['oauth_token_secret'];
		$oTwitter -> oAuthAuthorize($response['oauth_token']);
	}
	else 
	{
		var_dump($response); exit;
		$url = $pageURL;
		header('location: '. $url);
	}
	exit();
}

