<?php

/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Helper_Image extends Phpfox_Service
{
	/**
	 * NOTICE: we commonly have image_path under form: 2012/06/abc%.jpg,
	 * We wish to turnt the path into a full url so that client can display it without any further processing
	 * this function serves for the task of transform a image_path into a full URL
	 */
	public function getPhotoUrl($sImage, $sSuffix = '_240') {
			$sUrl = Phpfox::getParam('photo.url_photo') . $sImage;
			$sFinalUrl = sprintf($sUrl, $sSuffix);

			return $sFinalUrl;
	}

	public function getUserUrl($sImage, $sSuffix = '_50') {

		if(!$sImage) {
            $sFinalUrl = Phpfox::getService('mfox.user')->getImageUrl(array(), $sSuffix);
		} else {
			$sUrl = Phpfox::getParam('core.url_user') . $sImage;
			$sFinalUrl = sprintf($sUrl, $sSuffix);
		}

		return $sFinalUrl;
	}

	public function correctOrientation($orgExt, $sFileName){
		if (($orgExt == 'jpg' || $orgExt == 'jpeg') && function_exists('exif_read_data')){
			$sFileNameOrg = $sFileName;
			$sFileName = sprintf($sFileName, '');
            $exif = exif_read_data($sFileName);//d($exif);die();
            if(!empty($exif['Orientation'])){
            	$oImage = Phpfox::getLib('image');
				$iSize = 1;			
				$oImage->createThumbnail($sFileName, Phpfox::getParam('event.dir_image') . sprintf($sFileNameOrg, '_' . $iSize), $iSize, $iSize);			
                switch($exif['Orientation'])
                {
                    case 1:
                    case 2:
                        break;
                    case 3:
                    case 4:
                        // 90 degrees
                        $oImage->rotate($sFileName, 'right');
                        // 180 degrees
                        $oImage->rotate($sFileName, 'right');
                        break;
                    case 5:
                    case 6:
                        // 90 degrees right
                        $oImage->rotate($sFileName, 'right');
                        break;
                    case 7:
                    case 8:
                        // 90 degrees left
                        $oImage->rotate($sFileName, 'left');
                        break;
                    default:
                        break;
                }                                    
            }
		}
	}
}
