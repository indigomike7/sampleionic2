<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Helper_Helper extends Phpfox_Service
{
	private $_aDevice ;

	public function __construct() {
		$this->_aTransaction = array( 
			'status' => Phpfox::getService('mfox.transaction')->getAllTransactionStatus(),
			'method' => Phpfox::getService("mfox.transaction")->getAllTransactionMethods(),
		);

		$this->_aDevice = array( 
			'support' => array( 
				"ios" => array(
					"id" => 1, 
					"phrase" =>html_entity_decode(Phpfox::getPhrase("mfox.iphone")),
				),
				"ipad" => array(
					"id" => 2, 
					"phrase" =>html_entity_decode(Phpfox::getPhrase("mfox.ipad")),
				),
				"android" => array(
					"id" => 3, 
					"phrase" =>html_entity_decode(Phpfox::getPhrase("mfox.android")),
				),
			),
		);
	}

	public function getAllDevice() {
		return $this->_aDevice;
	}

	public function getPhraseById($sParam, $iId) {

		$sType = 'phrase';
		return $this->getConstById($sParam, $iId, $sType);
	}	

	public function getNameById($sParam, $iId) {

		$sType = 'name';
		return $this->getConstById($sParam, $iId, $sType);
	}

	public function getAllById($sParam, $iId) {

		$sType = 'all';
		return $this->getConstById($sParam, $iId, $sType);
	}

	public function getConstById($sParam, $iId, $sType) {
		$aParts = explode('.', $sParam);

		if(count($aParts) > 3) { 
			return false;
		}

		$sFirst = array_shift($aParts);
		$sLast = array_pop($aParts);

		$aList = $this->getList($sFirst);
		
		if(!isset($aList[$sLast])) {
			return false;
		}

		foreach($aList[$sLast] as $aItem) {
			if($aItem['id'] == $iId) {
				if($sType == 'all') {
					return $aItem;
				}

				return isset($aItem[$sType]) ? $aItem[$sType] : false;
			}
		}
		
		return false;

	}

	/**
	 * @params sParam input string, for ex, ad.pending, track.view
	 * @param get : id, phrase or get all
	 * @return id of the const
	 */
	public function getConst($sParam, $sGet = 'id') {
		$aParts = explode('.', $sParam);

		if(count($aParts) > 3) { 
			return false;
		}

		$sFirst = array_shift($aParts);
		$sLast = array_pop($aParts);

		// first is name of corresponding item
		$aList = $this->getList($sFirst);


		if(!$aParts) { // if there is not middle part
			// assume that it is to get all list ex 'transaction.status' 
			if($aList) { 
				return $aList[$sLast];
			} else {
				return false;
			}
			//$sMiddle = 'type'; // by default, all constants seem to be a type of s.thing
		} else {
			$sMiddle = $aParts[0];
		}

		if(isset($aList[$sMiddle][$sLast])) {
			switch($sGet) {
			case 'id' :
				return $aList[$sMiddle][$sLast]['id'];
				break;

			case 'phrase' :
				return $aList[$sMiddle][$sLast]['phrase'];
				break;

			case 'all' :
				return $aList[$sMiddle][$sLast];
				break;
			}

			return $aList[$sMiddle][$sLast]['id'];


		} else { 
			return false;
		}
	}	

	public function getList($sName) {
		$aList = array();
		switch($sName) {
			case 'transaction':
				$aList = $this->_aTransaction;
				break;

			case 'device':
				$aList = $this->_aDevice;
				break;

			case 'default':
				break;
		}

		return $aList;
	}


	public function generateSubscribeSKPData($module_id, $device = 1){
		$aPackages = Phpfox::getService('subscribe')->getForAdmin();
		$aMultiStoreKitPurchase = Phpfox::getService('mfox.transaction')->getMultiStoreKitPurchaseByModuleId($module_id, $device);		
		foreach ($aPackages as $keyPackage => $valuePackage) {
			$aPackages[$keyPackage]['storekitpurchase_key'] = '';
			foreach ($aMultiStoreKitPurchase as $keySKP => $valueSKP) {
				if($valuePackage['package_id'] == $valueSKP['storekitpurchase_item_id']){
					$aPackages[$keyPackage]['storekitpurchase_key'] = $valueSKP['storekitpurchase_key'];
					break;
				}
			}
		}

		return $aPackages;
	}

	public function changeTypeDevice($type){
		switch ($type) {
			case 'iphone':
				$type = 'ios';
				break;			
		}

		return $type;
	}

	public function correctOrientationVideo($aVideo){
        $file_ext = $aVideo['file_ext'];            
        // Make sure FFMPEG path is set
        $ffmpeg_path = Phpfox::getParam('video.ffmpeg_path');
        if (empty($ffmpeg_path) == false){
            $bCanRotate = true;
            // Make sure FFMPEG can be run
            if (!@file_exists($ffmpeg_path) || !@is_executable($ffmpeg_path))
            {
                $output = null;
                $return = null;
                exec($ffmpeg_path . ' -version', $output, $return);
                if ($return > 0)
                {
                    $bCanRotate = false;
                    // throw new Exception('Ffmpeg found, but is not executable');
                }
            }

            // Check we can execute
            if (!function_exists('shell_exec'))
            {
                $bCanRotate = false;
                // throw new Exception('Unable to execute shell commands using shell_exec(); the function is disabled.');
            }

            // Check the video temporary directory
            $tmpDir = Phpfox::getParam('video.dir');
            if (!is_writable($tmpDir))
            {
                $bCanRotate = false;
                // throw new Exception('Video temporary directory is not writable.');
            }       

            if($bCanRotate){
                $originalPath = Phpfox::getParam('video.dir') . sprintf($aVideo['destination'], '');
                $ffprobe = str_replace('ffmpeg', 'ffprobe', $ffmpeg_path);
                $cmd = $ffprobe . " " . $originalPath . " -show_streams 2>/dev/null";
                $result = shell_exec($cmd);
                $orientation = 0;

                if (strpos($result, 'TAG:rotate') !== FALSE)
                {
                    $result = explode("\n", $result);
                    foreach ($result as $line)
                    {
                        if (strpos($line, 'TAG:rotate') !== FALSE)
                        {
                            $stream_info = explode("=", $line);
                            $orientation = $stream_info[1];
                        }
                    }
                }

                if ($orientation)
                {
                    $transpose = 1;
                    switch ($orientation)
                    {
                        case 90 :
                            $transpose = 1;
                            break;

                        case 180 :
                            $transpose = 3;
                            break;

                        case 270 :
                            $transpose = 2;
                            break;
                    }
                    $outputPath = Phpfox::getParam('video.dir') . sprintf($aVideo['destination'], '_vrotated');
                    // Check and rotate video
                    $cmd = '';
                    $h = '';
                    if (strtolower($file_ext) == '3gp')
                    {
                        $h = '-s 352x288';
                    }
                    if ($transpose == 3)
                    {
                        $cmd = $ffmpeg_path . ' -i ' . escapeshellarg($originalPath) . ' -vf "vflip,hflip' . '" ' . $h . ' -b 2000k -r 30 -acodec copy -metadata:s:v:0 rotate=0 ' . escapeshellarg($outputPath);
                    }
                    else
                    {
                        $cmd = $ffmpeg_path . ' -i ' . escapeshellarg($originalPath) . ' -vf "transpose=' . $transpose . '" ' . $h . ' -b 2000k -r 30 -acodec copy -metadata:s:v:0 rotate=0 ' . escapeshellarg($outputPath);
                    }

                    shell_exec($cmd);
                    Phpfox::getLib('file')->unlink($originalPath);
                    Phpfox::getLib('file')->rename($outputPath, $originalPath);
                    Phpfox::getLib('file')->unlink($outputPath);

                    return true;
                }                                                         
            }
        }		

        return false;
	}


}
