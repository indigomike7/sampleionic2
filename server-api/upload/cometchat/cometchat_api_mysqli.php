<?php


/**
 * skip check post token
 * @see ./include/library/phpfox/phpfox.class.php
 */
define('PHPFOX_NO_CSRF', TRUE);

/**
 * @var bool
 */
define('PHPFOX_IS_AJAX', TRUE);

/**
 * skip save page
 * @see ./include/library/phpfox/phpfox.class.php
 */
define('PHPFOX_DONT_SAVE_PAGE', TRUE);

/**
 * @see ./include/init.inc.php: PHPFOX_NO_PLUGINS
 * skip plugins
 */
define('PHPFOX_NO_PLUGINS', TRUE);

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

include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "cometchat_init.php";



header('Access-Control-Allow-Headers: token, content-type, foxlang');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Request-Method: GET, POST, OPTIONS');

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
	exit("{}");
}


ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('error_reporting', E_ERROR);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(empty($_POST)){
        $_POST = (array)json_decode(file_get_contents('php://input'),true);
        $_REQUEST =  array_merge($_GET, $_POST);
    };
}

$response = array();
$messages = array();
$lastPushedAnnouncement = 0;
$processFurther = 1;

$status['available'] = $language[30];
$status['busy'] = $language[31];
$status['offline'] = $language[32];
$status['invisible'] = $language[33];
$status['away'] = $language[34];

$q = $_REQUEST['q'];

$host = $_SERVER['HTTP_HOST'];

define('IMAGE_BASE_URL', 'http://' . $host);
defined('DO_NOT_START_SESSION') || define('DO_NOT_START_SESSION', true);

$result = array();

function api_getFriendsList($userid,$time) {

	global $hideOffline;
	$offlinecondition = '';
	$sql = ("select DISTINCT ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_NAME." username, ".TABLE_PREFIX.DB_USERTABLE.".user_name link, ".TABLE_PREFIX.DB_USERTABLE.".user_image avatar, cometchat_status.lastactivity lastactivity, cometchat_status.status, ".TABLE_PREFIX.DB_USERTABLE.".status message, cometchat_status.isdevice from  ".TABLE_PREFIX.DB_USERTABLE." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid where  ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." NOT IN (select ".TABLE_PREFIX."user_blocked.block_user_id from ".TABLE_PREFIX."user_blocked where user_id = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' UNION select ".TABLE_PREFIX."user_blocked.user_id from ".TABLE_PREFIX."user_blocked where block_user_id = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."') AND ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." IN (select ".TABLE_PREFIX."friend.friend_user_id from ".TABLE_PREFIX."friend where ".TABLE_PREFIX."friend.user_id = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."') AND ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." IN (select ".TABLE_PREFIX."friend.user_id from ".TABLE_PREFIX."friend where ".TABLE_PREFIX."friend.friend_user_id = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."')  order by username asc");
	
	if (defined('DISPLAY_ALL_USERS') && DISPLAY_ALL_USERS == 1) {
		if ($hideOffline) {
			$offlinecondition = "and (cometchat_status.status IS NULL OR cometchat_status.status <> 'invisible' OR cometchat_status.status <> 'offline')";
		}
		$sql = ("select DISTINCT ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." userid, ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_NAME." username, ".TABLE_PREFIX.DB_USERTABLE.".user_name link, ".TABLE_PREFIX.DB_USERTABLE.".user_image avatar, cometchat_status.lastactivity lastactivity, cometchat_status.status, ".TABLE_PREFIX.DB_USERTABLE.".status message, cometchat_status.isdevice from ".TABLE_PREFIX.DB_USERTABLE." left join cometchat_status on ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." = cometchat_status.userid where (('".mysqli_real_escape_string($GLOBALS['dbh'],$time)."' - cometchat_status.lastactivity < '".((ONLINE_TIMEOUT)*2)."') OR cometchat_status.isdevice = 1) and  ".TABLE_PREFIX.DB_USERTABLE.".".DB_USERTABLE_USERID." NOT IN (select ".TABLE_PREFIX."user_blocked.block_user_id from ".TABLE_PREFIX."user_blocked where user_id = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."' UNION select ".TABLE_PREFIX."user_blocked.user_id from ".TABLE_PREFIX."user_blocked where block_user_id = '".mysqli_real_escape_string($GLOBALS['dbh'],$userid)."') ".$offlinecondition." order by username asc");
	
	}
	
	return $sql;
}

function api_getChatlist($userid) {

	global $dbh ;
	global $hideOffline;

	$time = getTimeStamp();

	$sql = api_getFriendsList($userid, $time);

	$query = $dbh->query($sql);

	// echo $sql; exit;
	$blockList = array();
	$buddyList = array();

	while ($chat = $query->fetch_assoc())
	{
		if (!in_array($chat['userid'], $blockList))
		{
			if (((($time-processTime($chat['lastactivity'])) < ONLINE_TIMEOUT) && $chat['status'] != 'invisible' && $chat['status'] != 'offline') || $chat['isdevice'] == 1) {
				if ($chat['status'] != 'busy' && $chat['status'] != 'away') {
					$chat['status'] = 'available';
				}
			} else {
				$chat['status'] = 'offline';
			}

			if (!empty($chat['username'])) {
				if (function_exists('processName')) {
					$chat['username'] = processName($chat['username']);
				}

				if ($chat['message'] == null) {
					$chat['message'] = '';
				}

				if (empty($chat['grp'])) {
					$chat['grp'] = '';
				}

				if (!(in_array($chat['userid'],$bannedUsers)) && $chat['userid'] != $userid && ($hideOffline == 0||($hideOffline == 1 && $chat['status']!='offline'))) {
					$buddyList[] = array(
						'iItemId' => $chat['userid'],
						'sItemType' => 'user',
						'sFullName' => $chat['username'],
						'sStatus' => $chat['status'],
						'bHasNewMessage' => false,
						'sImage' => IMAGE_BASE_URL . getAvatar($chat['avatar']),
						'm' => $chat['message'],
						'g' => $chat['grp']
					);
				}
			}
		}
	}

	return $buddyList;
}

function api_sendMessageTo($userid, $to, $message) {

	global $dbh;

	$lastInsertId = 0;

	if (USE_COMET == 1)
	{

		$comet = new Comet(KEY_A, KEY_B);
		$info = $comet -> publish(array(
			'channel' => md5($to . KEY_A . KEY_B . KEY_C),
			'message' => array(
				"from" => $userid,
				"message" => ($message),
				"sent" => getTimeStamp(),
				"self" => 0
			)
		));

	} else
	{

		$sql = ("insert into cometchat (cometchat.from,cometchat.to,cometchat.message,cometchat.sent,cometchat.read,cometchat.direction) values ('" . $dbh->escape_string($userid) . "', '" . $dbh->escape_string($to). "','" . $dbh->escape_string($message) . "','" . getTimeStamp() . "',0,1)");
		$query = $dbh->query($sql);

		if (defined('DEV_MODE') && DEV_MODE == '1')
		{
			echo mysql_error();
		}
		$lastInsertId = $dbh->insert_id;
	}

	$sql_sender = getUserDetails($userid);

	$query_sender = $dbh->query($sql_sender);

	$sender = $query_sender->fetch_assoc();


	if (function_exists('processName'))
	{
		$sender['username'] = processName($sender['username']);
	}

	return array(
		'error_code' => 0,
		'error_message' => '',
		'message' => 'Sent chat message successfully!',
		'iMessageId' => $lastInsertId,
		'iItemId' => $to,
		'sItemType' => 'user',
		'iSenderId' => $userid,
		'sSenderName' => $sender['username'], //$viewer->getTitle()
		'sSenderImage' => IMAGE_BASE_URL . getAvatar($sender['avatar']), //$this->getUserPhoto($viewer)
		'sMessage' => $message,
		'iTimestamp' => getTimeStamp(),
	);
}

function api_getMessages($userid, $to) {

	global $dbh;

	$result = array();

	$sql_sender = getUserDetails($to);

	$query_sender = $dbh->query($sql_sender);

	$sender = $query_sender ->fetch_assoc();

	// return $sender;

	$action = @$_REQUEST['sAction'];
	$maxid = @$_REQUEST['iMaxId'];
	$minid = @$_REQUEST['iMinId'];
	$markAsRead = array();

	if ($action == 'more' && $maxid)
	{
		$sql = "select * from cometchat where ((`from`='{$userid}' and `to`='{$to}') or (`from`='{$to}' and `to`='{$userid}')) and id<'{$maxid}' order by `sent` desc limit 10";
	} else if ($action == 'new' && $minid)
	{
		$sql = "select * from cometchat where ((`from`='{$userid}' and `to`='{$to}') or (`from`='{$to}' and `to`='{$userid}')) and id>'{$minid}' order by `sent` asc limit 10";
	} else
	{
		$sql = "select * from cometchat where ((`from`='{$userid}' and `to`='{$to}') or (`from`='{$to}' and `to`='{$userid}')) order by `sent` desc limit 10";
	}

	$query = $dbh->query($sql);

	if (!$query)
	{
		echo $dbh->error;
	}

	while ($row = $query->fetch_assoc())
	{
		$markAsRead[] = $row['id'];

		$data = array(
			'iMessageId' => intval($row['id']),
			'iItemId' => $userid,
			'sItemType' => 'user',
			'iSenderId' => $row['from'],
			'sSenderName' => $sender['username'], //$sender->getTitle(),
			'sSenderImage' => IMAGE_BASE_URL . getAvatar($sender['avatar']), //$this->getUserPhoto($sender),
			'sMessage' => $row['message'],
			'iTimestamp' => $row['sent'],
		);

		if (function_exists('processName'))
		{
			$data['username'] = processName($sender['username']);
		}

		$result[] = $data;
	}

	if ($markAsRead)
	{
		$sql = "update cometchat set `read`= 1 where id IN (" . implode(',', $markAsRead) . ")";
		$dbh->query($sql);
	}

	// update read status.

	if ($action != 'new')
	{
		$result = array_reverse($result);
	}

	return $result;
}

function api_notification($userid) {

	global $dbh;

	$sql = "select * from cometchat where `to`='$userid' and `read`=0 group by `from`";
	
	$result = $dbh->query($sql);

	return array('iNotificationAmount' => $result->num_rows);
}

function api_ping($userid) {

	global $dbh;

	$iGetNewMessages = @$_REQUEST['iGetNewMessages'];
	$iLastTimeStamp = @$_REQUEST['iLastTimeStamp'];
	$result = array();
	$messages = array();

	if ($iGetNewMessages && $iLastTimeStamp)
	{
		$sql = "select * from cometchat where (`from`='{$userid}' or `to`='{$userid}') and sent > '{$iLastTimeStamp}' order by `sent` asc";

		$query = $dbh->query($sql);

		if (!$query)
		{
			echo $dbh->error;
		}

		while ($row = $query->fetch_assoc())
		{
			$data = array(
				'iMessageId' => $row['id'],
				'iItemId' => $userid,
				'sItemType' => 'user',
				'iSenderId' => $row['from'],
				'sSenderName' => 'name', //$sender->getTitle(),
				'sSenderImage' => 'avatar', //IMAGE_BASE_URL . getAvatar($sender['avatar']), //$this->getUserPhoto($sender),
				'sMessage' => $row['message'],
				'iTimestamp' => $row['sent'],
			);

			if (function_exists('processName'))
			{
				$data['username'] = processName($sender['username']);
			}

			$messages[] = $data;
		}

		foreach ($messages as $index => $row)
		{
			$sql_sender = getUserDetails($row['iSenderId']);

			$query_sender = $dbh->query ($sql_sender);

			$sender = $query_sender->fetch_assoc();

			if (function_exists('processName'))
			{
				$sender['username'] = processName($sender['username']);
			}

			$messages[$index]['sSenderName'] = $sender['username'];
			$messages[$index]['sSenderImage'] = IMAGE_BASE_URL . getAvatar($sender['avatar']);
		}
	}

	return array(
		'iLastTimeStamp' => getTimeStamp(),
		'aNewMessages' => $messages
	);
}

function api_changestatus($userid, $message) {
	global $dbh;

	$sql = ("insert into cometchat_status (userid,status) values ('".$userid."','".mysqli_escape_string(sanitize_core($message))."') on duplicate key update status = '".$dbh->escape_string(sanitize_core($message))."'");
	$query = $dbh->query($sql);

	if (function_exists('hooks_activityupdate'))
	{
		hooks_activityupdate($userid, $message);
	}

	return array(
		'message' => sprintf('Changed status to %s successfully!', $message),
		'sStatus' => $message
	);
}

$userid = isset($_REQUEST['user_id'])?$_REQUEST['user_id']: 0;
$toid = isset($_REQUEST['iItemId'])?$_REQUEST['iItemId']:0;

function api_updateLastActivity($userid){
	global $dbh;
	$sql = updateLastActivity($userid);
	$dbh->query($sql);
}
if($userid){
	api_updateLastActivity($userid);
}

switch($q) {
	case 'chat/status' :
		break;
	case 'chat/getchatlist' :
		$result = api_getChatlist($userid);
		break;
	case 'chat/getmessages' :
		//$result = api_getMessages($userid, $toid);
		$result = api_getMessages($userid, $toid);

		break;
	case 'chat/ping' :
		$result = api_ping($userid);
		break;
	case 'chat/sendmessage' :
		//{sMessage: "hello is it me you looking for", iItemId: "88", sItemType: "chat"}
		$message = isset($_REQUEST['sMessage'])? $_REQUEST['sMessage']: '';
		$toid = isset($_REQUEST['iItemId'])?$_REQUEST['iItemId']:0;

		if(!$userid || !$toid){return array();}
		$result = api_sendMessageTo($userid, $toid, $message);
		break;
	case 'chat/notification' :
		$result = api_notification($userid);
		break;
	case 'chat/changestatus' :
		$status = $_REQUEST['sStatus'];
		$result = api_changestatus($userid, $status);
		break;
	default :
		$result = array();
}

ob_get_clean();

$response = json_encode($result);

echo $response;
