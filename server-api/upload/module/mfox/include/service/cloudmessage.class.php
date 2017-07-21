<?php
/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

/**
 * @package mfox
 * @subpackage mfox.service
 * @author Nam Nguyen <namnv@younetco.com>
 * @version 3.01
 * @since May 13, 2013
 * @link http://developer.android.com/google/gcm/index.html
 */

class Mfox_Service_Cloudmessage extends Phpfox_Service
{

	/**
	 * Url of google could messge to send
	 */
	CONST GOOGLE_SEND_URL = 'https://android.googleapis.com/gcm/send';

	CONST APPLE_SEND_URL = 'ssl://gateway.push.apple.com:2195';

	CONST DEBUG = false;

	public function PushNotificationforIpad($aData, $aDeviceIds)
	{
		try
		{

			$sURL = self::APPLE_SEND_URL;

			// @set via  Phpfox::getParam('mfox.mfox_apns_cert_file')
			$local_cert = Phpfox::getParam('mfox.mfox_apns_ipad_cert_file','');
			
			if(!$local_cert){
				$local_cert = PHPFOX_DIR_MODULE . 'mfox/push_imfox_ipad.pem';  
			}
			
			// @set via Phpfox::getParam('mfox.mfox_apns_passphrase')
			$passphrase = Phpfox::getParam('mfox.mfox_apns_ipad_passphrase','123456');
			//$passphrase = 'Appl3@YN';

			$params = array('ssl' => array(
					//'verify_peer' => true,
					'local_cert' => $local_cert,
					//'local_cert' => $local_cert,
					'passphrase' => $passphrase,
				));


			$context = stream_context_create($params);

			$fp = @stream_socket_client($sURL, $nError, $sError, 5000, STREAM_CLIENT_CONNECT, $context);

			self::DEBUG && print("Connecting $sURL error: {$nError} {$sError}\n");

			if (!$fp)
			{
				self::DEBUG && print("Could not connect $sURL error: {$nError} {$sError}\n");
				return FALSE;
			}

			$payload = json_encode($aData);

			foreach ($aDeviceIds as $device_id)
			{
				$msg = chr(0) . pack('n', 32) . pack('H*', $device_id) . pack('n', strlen($payload)) . $payload;
				$result = fwrite($fp, $msg, strlen($msg));
				self::DEBUG && print("Send $sURL msg ({$msg}) result ({$result})\n");
			}

			fclose($fp);

			self::DEBUG && print("send message successful to apns.");


			return TRUE;
		}
		catch(Exception $ex)
		{
			print("{$ex->getMessage()}");

		}
		return FALSE;		
	}

	/**
	 * @param array $aData send data must be array to parse by json_decode
	 * @param array $aDeviceIds
	 */
	public function PushNotificationforIOS($aData, $aDeviceIds)
	{

		try
		{

			$sURL = self::APPLE_SEND_URL;

			// @set via  Phpfox::getParam('mfox.mfox_apns_cert_file')
			$local_cert = Phpfox::getParam('mfox.mfox_apns_cert_file','');
			
			if(!$local_cert){
				$local_cert = PHPFOX_DIR_MODULE . 'mfox/push_imfox.pem';  
			}
			
			// @set via Phpfox::getParam('mfox.mfox_apns_passphrase')
			$passphrase = Phpfox::getParam('mfox.mfox_apns_passphrase','');
			//$passphrase = 'Appl3@YN';

			$params = array('ssl' => array(
					//'verify_peer' => true,
					'local_cert' => $local_cert,
					//'local_cert' => $local_cert,
					'passphrase' => $passphrase,
				));

			$context = stream_context_create($params);

			$fp = @stream_socket_client($sURL, $nError, $sError, 5000, STREAM_CLIENT_CONNECT, $context);

			self::DEBUG && print("Connecting $sURL error: {$nError} {$sError}\n");

			if (!$fp)
			{
				//print("Could not connect $sURL error: {$nError} {$sError}\n");
				return FALSE;
			}

			$payload = json_encode($aData);

			foreach ($aDeviceIds as $device_id)
			{
				$msg = chr(0) . pack('n', 32) . pack('H*', $device_id) . pack('n', strlen($payload)) . $payload;
				$result = fwrite($fp, $msg, strlen($msg));
				self::DEBUG && print("Send $sURL msg ({$msg}) result ({$result})\n");
			}

			fclose($fp);

			self::DEBUG && print("send message successful to apns.");


			return TRUE;
		}
		catch(Exception $ex)
		{
			print("{$ex->getMessage()}");

		}
		return FALSE;

	}

	public function PushNotificationforAndroid($aDeviceIds, $aData, $sServerApiKey)
	{

		$fields = array(
			'registration_ids' => $aDeviceIds,
			'data' => $aData,
		);

		$headers = array(
			'Authorization: key=' . $sServerApiKey,
			'Content-Type: application/json'
		);

		// Open connection
		$ch = curl_init();

		// Set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, self::GOOGLE_SEND_URL);

		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

		// Execute post
		$response = curl_exec($ch);

		// Close connection
		curl_close($ch);

		$result = json_decode($response, 1);

		return $result;
	}

    public function getUnseenTotal($iUserId)
    {
        return (int) Phpfox::getService('mfox.notification')->getUnseenTotal($iUserId);
    }

    /**
     * @since 3.08p2
     * Support filter notification type
     * issues: FMOBI-1887
     *
     * @param array $aData
     * @param int $iUserId
     * @param null $sNotificationType
     * @return array|false
     */
	function send($aData, $iUserId, $sNotificationType = null)
	{
		$aDataIOS = isset($aData['ios']) ? $aData['ios'] : null;
		$aDataAndroid = isset($aData['android']) ? $aData['android'] : null;

        if(null != $sNotificationType)
        {
            // check $sNotificationType is in supported notification type.

            if(!in_array($sNotificationType, $this->getSupportedNotificationTypes())){
                return false;
            }
        }


		/**
		 * etc: AIzaSyB3un2VRYz6LHmTVl8AvWRd-R7udZgTYDU
		 * @var string
		 */
		$sServerApiKey = Phpfox::getParam('mfox.google_key');

		if (strlen($sServerApiKey) < 8)
		{
//			return array('message' => 'google api key is empty!');
		}

		/**
		 * Registration Ids of devices, also called "devices id"
		 * @var array
		 */

		$response_ios = $response_android = $response_ipad = '';

		$aDeviceIds = Phpfox::getService('mfox.device') -> getIds($iUserId, 'android');

		if (!empty($aDeviceIds) && $aDataAndroid)
		{
			$response_android = $this -> PushNotificationforAndroid($aDeviceIds, $aDataAndroid, $sServerApiKey);
		}

		$aDeviceIds = Phpfox::getService('mfox.device') -> getIds($iUserId, 'ios');

        if(!empty($aDataIOS)){
            $aDataIOS['aps']['badge'] =  $this->getUnseenTotal($iUserId);
            $aDataIOS['aps']['alert'] =  strip_tags($aDataIOS['aps']['alert']);
        }


		if (!empty($aDeviceIds) && $aDataIOS)
		{
            $message['payload'] = json_encode($aDataIOS);
			$response_ios = $this -> PushNotificationforIOS($aDataIOS, $aDeviceIds);
		}

		// for ipad
		$aDeviceIds = Phpfox::getService('mfox.device') -> getIds($iUserId, 'ipad');

		if (!empty($aDeviceIds) && $aDataIOS)
		{

			$response_ipad = $this -> PushNotificationforIpad($aDataIOS, $aDeviceIds);
		}

		$response =  array(
			$response_android,
			$response_ios, 
			$response_ipad
		);

        if(self::DEBUG){
            exit(json_encode($response));
        }

		//$response = $response_ios!=""?$response_ios:$response_android;
		//return $response;

	}

    /**
     * @since 3.08p2
     * App received push notifications of unsupported modules
     * ID: FMOBI-1887
     *
     *
     * @return array
     */
    public function getSupportedNotificationTypes()
    {
        return Phpfox::getService('mfox.notification')->getSupportedNotificationTypes();
    }

}
