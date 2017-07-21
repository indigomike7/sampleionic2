<?php
require_once "cli.php";
$sPathFB = 'include' . PHPFOX_DS . 'service' . PHPFOX_DS . 'libraries' . PHPFOX_DS . 'Facebook' . PHPFOX_DS . 'Api.php';
if (file_exists($sPathFB))
{
  require_once($sPathFB);
} else {
  exit('Site does not allow Facebook Connect.');
}

$sService = 'facebook';

if (!Phpfox::isModule('socialbridge') 
   || Phpfox::getService('socialbridge')->hasProvider($sService) == FALSE
){
  exit('Administrators does not allow Facebook Connect');
}         

$settings = Phpfox::getService('socialbridge')->getSetting($sService);
if( empty($settings['secret']) 
  || empty($settings['app_id'])
    )
{

  exit('Administrators does not allow Facebook Connect!.');
}

try{ 

$facebook = new Facebook_Api(array(
  'appId'  => $settings['app_id'],
  'secret' => $settings['secret'],
  'cookie' => false, // @todo make sure this works
  'allowSignedRequest'=>false,
  'fileUpload'=>false,
  //'baseDomain' => $_SERVER['HTTP_HOST'],
));

if(isset($_GET['access_token']))
{
  // if logined before, show Facebook username
  $username = $_GET['username'];
  echo "<div style='text-align: center; line-height: 40px; padding-top: 10px'>Connecting with <strong>$username</strong> ... </div>";
  exit;  
}

if(isset($_GET['confirm_token']))
{
  $confirm_token = $_GET['confirm_token'];
  
  $me = $facebook->api('/me');
  $email = isset($me['email']) ? ($me['email']) : "";
  $username = $me['name'];
  $uid = $me['id'];
  
  $newurl =  '?'. http_build_query(array('access_token'=>$confirm_token
    ,'name'=>'facebook'
    ,'email'=>$email
    ,'uid'=>$uid
    ,'username'=>$username
    ,'service'=>'facebook'
  ));

  $logoutUrl = $facebook->getLogoutUrl(array(
    'next'=> Phpfox::getParam('core.path') . 'module/mfox/facebook.php?name=facebook&pass_confirm=1',
  ));
  $avatar = sprintf("https://graph.facebook.com/%s/picture/?type=square", $uid);
    
  $formHTML = <<<EOF
  <div style="width: 100%; text-align: center; margin-top: 30px;">
    <div>
    <a href="{$newurl}"><img src="{$avatar}" width="50px" height="50px" /></a></div>
    <div>
    <a href="{$newurl}" style="color: #333; text-decoration: none; line-height: 40px;">Logged in as <strong>{$username}</strong></a>
    </div>
    <div style=""><button onclick="window.location.assign('{$logoutUrl}')">Login with another account</button></div>
  </div>
EOF;
  echo $formHTML; 
  exit;

}

if(!isset($_GET['code']))
{
	$url =  $facebook->getLoginUrl(array('scope'=>'email'));
	
	header('location: '. $url);
	exit();
}

$access_token  = $facebook->getAccessToken();

if($access_token)
{
  $me = $facebook->api('/me');
  $email = ($me['email']) ? ($me['email']) : "";
  $username = ($me['name']) ? ($me['name']) : "";
  $uid = $me['id'];

  if(@$_SESSION['fb_connected'] != true || isset($_GET['pass_confirm']))
  {
    $url =  '?'. http_build_query(array('access_token'=>$access_token
      ,'name'=>'facebook'
      ,'email'=>$email
      ,'uid'=> $uid
      ,'username'=> $username
      ,'service'=>'facebook'
    ));
  }
  else
  {
    $url =  '?'. http_build_query(array('confirm_token'=>$access_token
      ,'name'=>'facebook'
      ,'email'=>$email
      ,'uid'=> $uid
      ,'username'=> $username
      ,'service'=>'facebook'
    ));
  }

  $_SESSION['fb_connected'] = true;

  header('location: ' . $url);
}
else{
  exit('Could not connect Facebook please try again.');
}

} catch (Exception $e) {
  exit('Could not connect Facebook please try again.');
}
