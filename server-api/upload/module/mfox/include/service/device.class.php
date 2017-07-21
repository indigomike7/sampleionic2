<?php
/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

/**
 * @author Nam Nguyen <namnv@younetco.com>
 * @version 3.01
 * @package mfox
 * @subpackage mfox.service
 * @since May 13, 2013
 *
 */
class Mfox_Service_Device extends Phpfox_Service
{
	/**
	 * table name
	 * @var string
	 */
	protected $_sTable;

	/**
	 * @constructor
	 */
	function __construct()
	{
		$this -> _sTable = Phpfox::getT('mfox_device');
	}

	/**
	 * called from mobile after login successful.
	 * @param array $aData
	 */
	function register($aData)
	{
        /**
         * @var string
         */
		$sDeviceId = isset($aData['sDeviceId']) ? $aData['sDeviceId'] : '';
		$sPlatform = isset($aData['sPlatform']) ? $aData['sPlatform'] : 'android';

        /**
         * @var int
         */
		$iUserId = Phpfox::getUserId();

		$this -> addId($iUserId, $sDeviceId,$sPlatform);

		return array(
			'success' => 1,
			'iUserId' => $iUserId,
			'device_id' => $sDeviceId
		);
	}

	/**
	 * unregister device
	 * @param array $aData
	 * @return array
	 */
	function unregister($aData)
	{
        /**
         * @var string
         */
		$sDeviceId = isset($aData['sDeviceId']) ? $aData['sDeviceId'] : '';
		$this -> removeId($sDeviceId);
		return array('success' => 1);

	}

	/**
	 * Get device id by $iUserId
	 * @param int $iUserId Phpfox user id that
	 * @param int $iUserId Phpfox user id that
	 * @return array
	 */
	function getIds($iUserId, $platform = 'android')
	{
		Phpfox::getService('mfox.helper.database')->reconnect(0);

		$result = array();
        /**
         * @var array
         */
		$rows = $this -> database() -> select('device_id') -> from($this -> _sTable) -> where('user_id=' . (int)$iUserId.' and platform="'.$platform.'"') -> execute('getRows');

		foreach ($rows as $row)
		{
			if ($row['device_id'])
			{
				$result[] = $row['device_id'];
			}
		}
        /**
         * @var array
         */
		$result = array_unique($result);

		return $result;

	}

	/**
	 * add or update
	 * @param string $iUserId
	 * @param string $sDeviceId
	 * @param string $platform
	 * @return void
	 */
	function addId($iUserId, $sDeviceId, $sPlatform = 'android')
	{
		global $token;

		if (!$token)
		{
			return ;
		}
        /**
         * @var int
         */
		$id = md5($sDeviceId);
        /**
         * @var array
         */
		$aRow = $this -> database() -> select('device_id') -> from($this -> _sTable) -> where("id='$id'") -> execute('getRow');

		if ($aRow)
		{
			$this -> database() -> update($this -> _sTable, array(
				'user_id' => $iUserId,
				'token' => $token,
				'timestamp' => time(),
			), "id='$id'");
		}
		else
		{
			$this -> database() -> insert($this -> _sTable, array(
				'id' => $id,
				'user_id' => $iUserId,
				'token' => $token,
				'device_id' => $sDeviceId,
				'timestamp' => time(),
				'platform' => $sPlatform
			));
		}
	}

	/**
	 * @param string $deviceId
	 */
	function removeId($sDeviceId)
	{
        /**
         * @var int
         */
		$id = md5($sDeviceId);

		$this -> database() -> delete($this -> _sTable, "id='$id'");
	}

	/**
	 * Remove device id from unregister
	 * @param string $sToken
	 * @return void
	 */
	function removeIdByToken($sToken = null)
	{
		global $token;

		if (null == $sToken)
		{
			$sToken = $token;
		}

		if (!$sToken)
		{
			return;
		}

		$this -> database() -> delete($this -> _sTable, "token='$sToken'");
	}

	public function getByToken($token){
		if(empty($token)){
			return array();
		}

		return $this->database()->select('*')
                ->from($this -> _sTable)
                ->where('token = \'' . $token . '\' ')
                ->execute('getSlaveRow');
	}

}
