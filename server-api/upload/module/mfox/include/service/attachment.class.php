<?php
defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Attachment extends Phpfox_Service
{
	public function attachphoto($aData){
		$sModule = isset($aData['sModule']) ? $aData['sModule'] : '';
		if(strlen(trim($sModule)) == 0){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_module_name")),
                'result' => 0
            );
		}

		$aParam = array(
			'custom_attachment' => 'photo', 
			'category_name' => $sModule, 
		);

		return $this->__upload($aParam);
	}

	public function attachlink($aData){
		$sModule = isset($aData['sModule']) ? $aData['sModule'] : '';
		$sUrl = isset($aData['sUrl']) ? $aData['sUrl'] : '';

		if(strlen(trim($sModule)) == 0){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_module_name")),
                'result' => 0
            );
		}
		
		if(strlen(trim($sUrl)) == 0){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_url")),
                'result' => 0
            );
		}

		// get link
		if (!($aLink = Phpfox::getService('link')->getLink($sUrl)))
		{
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.cannot_get_link")),
                'result' => 0

            );
		}

		// insert to link object
		$aVals = array(
			'link' => array(
				'url' => $sUrl,
				'image_hide' => 0,
				'image' => $aLink['default_image'],
				'title' => $aLink['title'],
				'description' => $aLink['description'],
			), 
			'category_id' => $sModule, 
		);
		if (Phpfox::getService('link.process')->add($aVals, true)){
			$iId = Phpfox::getService('link.process')->getInsertId();
			$iAttachmentId = Phpfox::getService('attachment.process')->add(array(
					'category' => $aVals['category_id'],
					'link_id' => $iId
				)
			);			

            return array(
                'error_code' => 0,
                'error_message' => '', 
                'link_data' => array(
					'attachment_id' => $iAttachmentId,
					'url' => $sUrl,
					'image' => $aLink['default_image'],
					'title' => $aLink['title'],
					'description' => $aLink['description'],
            	),
            );

		} else {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.cannot_add_link")),
                'result' => 0
            );
		}
	}

	public function attachfile($aData){
		$sModule = isset($aData['sModule']) ? $aData['sModule'] : '';
		if(strlen(trim($sModule)) == 0){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_module_name")),
                'result' => 0
            );
		}

		$aParam = array(
			'custom_attachment' => '', 
			'category_name' => $sModule, 
		);

		return $this->__upload($aParam);		
	}

	public function delete($aData){
		$sModule = isset($aData['sModule']) ? $aData['sModule'] : '';
		$iItemId = isset($aData['iItemId']) ? $aData['iItemId'] : '';
		// photo/file/video/link
		$sType = isset($aData['sType']) ? $aData['sType'] : 'file';

		if(strlen(trim($sModule)) == 0){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_module_name")),
                'result' => 0
            );
		}

		if(strlen(trim($iItemId)) == 0){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_item_id")),
                'result' => 0
            );
		}

		switch ($sType) {
			case 'file':
				if (($iUserId = Phpfox::getService('attachment')->hasAccess($iItemId, 'delete_own_attachment', 'delete_user_attachment')) 
					&& is_numeric($iUserId) && Phpfox::getService('attachment.process')->delete($iUserId, $iItemId)
				)
				{
		            return array(
		                'error_code' => 0,
		                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.delete_successfully")),
		                'result' => 1
		            );
				}
				break;
			
			case 'link':
				$aAttachment = Phpfox::getService('attachment')->getForDownload($iItemId);
				if(isset($aAttachment['link_id']) && (int)$aAttachment['link_id'] > 0){
					$iItemId = (int) $aAttachment['link_id'];	
					Phpfox::getService('link.process')->delete($iItemId);
		            return array(
		                'error_code' => 0,
		                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.delete_successfully")),
		                'result' => 1
		            );
				} else {
		            return array(
		                'error_code' => 1,
		                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.link_does_not_found")),
		                'result' => 0
		            );					
				}			
				break;

			default:
				break;
		}

	    return array(
	        'error_code' => 1,
	        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.cannot_delete_attachment")),
	        'result' => 0
	    );
	}

	private function __upload($aParam = array()){
		$sText = '';
		// photo/video/file(== '')
		$custom_attachment = isset($aParam['custom_attachment']) ? $aParam['custom_attachment'] : '';
		$input = isset($aParam['input']) ? $aParam['input'] : '';
		$attachment_inline = isset($aParam['attachment_inline']) ? $aParam['attachment_inline'] : '';
		// module name (blog/coupon)
		$category_name = isset($aParam['category_name']) ? $aParam['category_name'] : '';
		$result = null;

		if (!isset($_FILES['image']))
		{
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_file_to_upload")),
                'result' => 0
            );
		}

		// check types do not support 
		if ($custom_attachment == 'video')
		{
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_video_yet")),
                'result' => 0
            );
		}		

		$oFile = Phpfox::getLib('file');
		$oImage = Phpfox::getLib('image');
		
		$oAttachment = Phpfox::getService('attachment.process');		
		
		$sIds = '';		
		$sStr = '';
		$iUploaded = 0;
		$iFileSizes = 0;
		if ($_FILES['image']['error']  == UPLOAD_ERR_OK)
		{
			$iKey = 0;
			$aValid = array('gif', 'png', 'jpg');
			if ($custom_attachment == 'photo')
			{
				$aValid = array('gif', 'png', 'jpg');	
			}
			elseif ($custom_attachment == 'video')
			{
				$aValid = Phpfox::getService('video')->getFileExt();	
			}
			
			if ($input == '' && $custom_attachment == '')
			{
				$aValid = Phpfox::getService('attachment.type')->getTypes();
			}
			
			$iMaxSize = null;
			
			if (Phpfox::getUserParam('attachment.item_max_upload_size') !== 0)
			{
				$iMaxSize = (Phpfox::getUserParam('attachment.item_max_upload_size') / 1024);
			}
			
			$aImage = $oFile->load('image', $aValid, $iMaxSize);
			
			if ($aImage !== false)
			{
				if (!Phpfox::getService('attachment')->isAllowed())
				{
		            return array(
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.failed_limit_reached")),
                        'error_code' => 1,
		                'result' => 0
		            );
				}
				
				$iUploaded++;
				$bIsImage = in_array($aImage['ext'], Phpfox::getParam('attachment.attachment_valid_images'));
				
				$iId = $oAttachment->add(array(
						'category' => $category_name,
						'file_name' => $_FILES['image']['name'],						
						'extension' => $aImage['ext'],
						'is_image' => $bIsImage
					)
				);
				
				$sIds .= $iId . ',';

				
				$sFileName = $oFile->upload('image', Phpfox::getParam('core.dir_attachment'), $iId);
	            $orgExt = Phpfox::getLib('file')->getFileExt(Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, ''));
	            Phpfox::getService('mfox.helper.image')->correctOrientation($orgExt, Phpfox::getParam('core.dir_attachment') . $sFileName);

				$sFileSize = filesize(Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, ''));
				$iFileSizes += $sFileSize;				
				
				$oAttachment->update(array(
					'file_size' => $sFileSize,
					'destination' => $sFileName,
					'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID')
				), $iId);
				
				if ($bIsImage)
				{
					$sThumbnail = Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, '_thumb');
					$sViewImage = Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, '_view');
					
					$oImage->createThumbnail(Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, ''), $sThumbnail, Phpfox::getParam('attachment.attachment_max_thumbnail'), Phpfox::getParam('attachment.attachment_max_thumbnail'));
					$oImage->createThumbnail(Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, ''), $sViewImage, Phpfox::getParam('attachment.attachment_max_medium'), Phpfox::getParam('attachment.attachment_max_medium'));
					
					$iFileSizes += (filesize($sThumbnail) + filesize($sThumbnail));
				}

				if ($custom_attachment == 'video')
				{
					Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'attachment', $iFileSizes);


		            return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_video_yet")),
		                'result' => 0
		            );
				}
				else 
				{
					$sText .= 'Completed ' . strip_tags($_FILES['image']['name']);
				}
			}
			else 
			{
				$sText .= 'Failed ' . strip_tags($_FILES['image']['name']) . ' - ' . implode(' ', Phpfox_Error::get());
	            return array(
	                'error_code' => 1,
	                'error_message' => $sText, 
	                'result' => 0
	            );
			}
		}
		
		if (!$iUploaded)
		{
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.there_are_some_errors_when_processing_please_check_again")),
                'result' => 0
            );
		}	
		
		if ($custom_attachment == 'photo' || $custom_attachment == 'video')
		{
			// attach photo/video
			$aAttachment = Phpfox::getLib('database')->select('*')
				->from(Phpfox::getT('attachment'))
				->where('attachment_id = ' . (int) $iId)
				->execute('getSlaveRow');

			if ($custom_attachment == 'photo')
			{		
				// return path excluding '[img][/img]'
				$sImagePath = Phpfox::getLib('image.helper')->display(array(
					'server_id' => $aAttachment['server_id'], 
					'path' => 'core.url_attachment', 
					'file' => $aAttachment['destination'],
					'suffix' => '_view', 
					// fixed: could not attachment to blogs. mail because image size.
					// JIRA: https://jira.younetco.com/browse/FMOBI-1305
					// 'max_width' => 'attachment.attachment_max_medium', 
					// 'max_height' =>'attachment.attachment_max_medium',
					'return_url' => true)
				);

				
				// return array($aAttachment, $sImagePath, Phpfox::getParam('core.url_attachment'));

				$result = array(
	                'error_code' => 0,
	                'error_message' => '', 
	                'iId' => $iId, 
	                'sImagePath' => $sImagePath, 
	                'result' => 1
	            );
			}
			else
			{
	            $result = array(
	                'error_code' => 0,
	                'error_message' => '', 
	                'iId' => $iId, 
	                'sImagePath' => $sImagePath, 
	                'result' => 1
	            );
			}			

		}
		else
		{
			list($iCnt, $aRows) = Phpfox::getService('attachment')->get(
				'attachment.attachment_id IN(' . rtrim($sIds, ',') . ')'
				,	'attachment.time_stamp ASC', 0, 500, false
			);		
			$aRow = $aRows[0];
			$sImagePath = '';
			$sImagePath = Phpfox::getLib('image.helper')->display(array('server_id' => $aRow['server_id'], 
				'path' => 'core.url_attachment', 'file' => $aRow['destination'], 
				'suffix' => '', 
				// fixed: could not attachment to blogs. mail because image size.
				// JIRA: https://jira.younetco.com/browse/FMOBI-1305
				// 'max_width' => '', 
				// 'max_height' =>'', 
				'return_url' => true));
            $result = array(
                'error_code' => 0,
                'error_message' => '', 
                'iId' => trim($sIds, ','), 
                'sImagePath' => $sImagePath, 
                'sFileName' => $aRow['file_name'], 
                'result' => 1
            );

			if ($category_name == 'theme')
			{
	            return array(
	                'error_code' => 1,
	                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_yet")),
	                'result' => 0
	            );
			}
		}
	
		// Update user space usage
		Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'attachment', $iFileSizes);		
		
		if ($attachment_inline)
		{
			// not support inline mode yet
		}

		return $result;
	}

}
