<?php

defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Helper_Attachment extends Phpfox_Service {

	public function getAttachmentByType($iItemId, $sType, $attachments = null, $attachment_edit = false, $attachment_no_header = false)
	{
		$iId = (int) $iItemId;
		$sType = $sType;
		$aRows = $attachments;
		$bIsAttachmentEdit = (bool) $attachment_edit;
		$bIsAttachmentNoHeader = (bool) $attachment_no_header;

		if ($bIsAttachmentEdit)
		{
			list($iCnt, $aRows) = Phpfox::getService('attachment')->get('attachment.attachment_id IN(' . rtrim($this->getParam('sIds'), ',') . ')',	'attachment.time_stamp ASC', 0, 500, false);
		}
		else 
		{
			if (!is_array($aRows))
			{
				list($iCnt, $aRows) = Phpfox::getService('attachment')->get("attachment.item_id = {$iId} AND attachment.view_id = 0 AND attachment.category_id = '" . Phpfox::getLib('database')->escape($sType) . "' " . ($bIsAttachmentNoHeader ? '' : 'AND attachment.is_inline = 0'), 'attachment.attachment_id DESC', '', '', false);
			}
		}

		return array($iCnt, $aRows);
	}

	public function getDetailAttachmentByType($type = 'image', $itemId = 0, $attachment = array()){
		switch ($type) {
			case 'image':
				$url = Phpfox::getLib('image.helper')->display(array(
                    'server_id' => $attachment['server_id'],
                    'path' => 'core.url_attachment',
                    'file' => $attachment['destination'],
                    'suffix' => '',
                    'return_url' => true
                    )
                );	

                return $url;		
				break;
			
			case 'link':
				$iLinkId = (int) $itemId;	
				if (!($aLink = Phpfox::getService('link')->getLinkById($iLinkId)))
				{
					return array();
				}

				if (Phpfox::getParam('core.warn_on_external_links'))
				{
					if (!preg_match('/' . preg_quote(Phpfox::getParam('core.host')) . '/i', $aLink['link']))
					{
						$aLink['link'] = Phpfox::getLib('url')->makeUrl('core.redirect', array('url' => Phpfox::getLib('url')->encode($aLink['link'])));				
					}						
				}
				
				if (substr($aLink['link'], 0, 7) != 'http://' && substr($aLink['link'], 0, 8) != 'https://')
				{
					$aLink['link'] = 'http://' . $aLink['link'];
				}
				
				return $aLink;
				break;

			default:
				return array();
				break;
		}

	}
}
