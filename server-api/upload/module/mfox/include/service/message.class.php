<?php

/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

/**
 * @author ductc@younetco.com
 * @package mfox
 * @subpackage mfox.service
 * @version 3.01
 * @since June 5, 2013
 * @link Mfox Api v1.0
 */
class Mfox_Service_Message extends Phpfox_Service {

    /**
     * Mfox_Service_Request_Request
     * @var object
     */
    private $_oReq = null;

    /**
     * Mfox_Service_Search_Search
     * @var object
     */
    private $_oSearch = null;

    /**
     * Mfox_Service_Search_Browse
     * @var object
     */
    private $_oBrowse = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_oReq = Mfox_Service_Request_Request::instance();
        $this->_oSearch = Mfox_Service_Search_Search::instance();
        $this->_oBrowse = Mfox_Service_Search_Browse::instance();
    }

    /**
     * Input data:
     * + iItemId: int, required.
     * 
     * Output data:
     * + result: int.
     * + error_code: int.
     * + error_message: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see message/markread
     * 
     * @param array $aData
     * @return array
     */
    public function markread($aData)
    {
        /**
         * @var int
         */
        $iMessageId = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;
        /**
         * @var array
         */
        $aMail = Phpfox::getService('mail')->getMail($iMessageId);
        if (!$aMail)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.message_is_not_valid"))
            );
        }
        if (($aMail['viewer_user_id'] != Phpfox::getUserId()) && ($aMail['owner_user_id'] != Phpfox::getUserId()))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('mail.invalid_message')
            );
        }
        if ($aMail['viewer_user_id'] == Phpfox::getUserId())
		{
			return array('result' => Phpfox::getService('mail.process')->toggleView($aMail['mail_id'], false));
		}
        else
        {
            return array('result' => false, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_can_not_mark_this_message")));
        }
    }
    /**
     * Input data:
     * + iItemId: int, required.
     * 
     * Output data:
     * + result: bool.
     * + error_code: int.
     * + error_message: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see message/reply
     * 
     * @param array $aData
     * @return array
     */
    public function reply($aData)
    {
        $this->_oReq->set(array(
            'val' => array(
                'thread_id' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : null,
                'attachment' => !empty($aData['aAttachment']) ? $aData['aAttachment'] : null,
                'message' => !empty($aData['sText']) ? $aData['sText'] : ''
            )
        ));

        $aVals = $this->_oReq->get('val');
        if ($aVals && ($iNewId = Mail_Service_Process::instance()->add($aVals)))
        {
            list($aCon, $aMessages) = Mail_Service_Mail::instance()->getThreadedMail($iNewId);
            $aMessages = array_reverse($aMessages);

            return array(
                'result' => 1,
                'error_code' => 0,
                'message' => '',
                'aItem' => $aMessages[0]
            );
        }
        else
        {
            $aErrors = Phpfox_Error::get();
            return array(
                'error_code' => 1,
                'error_message' => html_entity_decode($aErrors[0])
            );
        }
    }

    /**
     * Input data:
     * + sUserId: string, required. Ex: "5,4,6"
     * + sSubject: string, required.
     * + sText: string, required.
     * 
     * Output data:
     * + result: bool.
     * + iItemId: int.
     * + message: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see message/compose
     * 
     * @see Mail_Service_Process
     * @param array $aData
     * @return array
     */
    public function compose($aData)
    {
        // $api = Phpfox::getService('mfox.helper.mail');
        $api = Phpfox::getService('mail.process');
        /**
         * @var array
         */
        $aVals = array();
        if ($aData['sUserIds'])
        {
            $aIds = explode(',', $aData['sUserIds']);
            $aVals['to'] = $aIds; // support send to 1 first
        }
        if (isset($aData['sSubject']))
        {
            $aVals['subject'] = $aData['sSubject'];
        }
        if (isset($aData['sText']))
        {
            $aVals['message'] = $aData['sText'];
        }
        /**
         * @var int
         */
        $iId = $api->add($aVals);
        if ($iId)
        {
            return array(
                'result' => TRUE,
                'iItemId' => $iId,
								'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.your_message_was_successfully_sent")),
								'error_code' => 0
            );
        }
        else
        {
            $errors = Phpfox_Error::get();
            if(is_array($errors) && count($errors) > 0){
                return array('result' => FALSE, 'error_message' => $errors[0], 'error_code' => 1);
            } else {
                return array(
                    'result' => FALSE, 
                    'error_message' => $errors,
                    'error_code' => 1
                );
            }
        }
    }
    /**
     * Delete message from data.
     * 
     * Input data:
     * + sType: string, required. Ex: "sentbox", "trash" or empty string.
     * + iItemId: int, required.
     * 
     * Output data:
     * + result: int.
     * + message: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see message/delete
     * 
     * @param array $aData
     * @return array
     */
    public function delete($aData)
    {
        $this->_oReq->set(array(
            'id' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : null,
            'type' => !empty($aData['sType']) ? $aData['sType'] : null,
        ));

        Phpfox::isUser(true);
        
        $sType = $this->_oReq->get('type');        
        
        if (Phpfox::getParam('mail.threaded_mail_conversation'))
        {
            Mail_Service_Process::instance()->archiveThread($this->_oReq->get('id'));
            $sMessage = Phpfox::getPhrase('mail.message_successfully_archived');
        }
        else
        {
            if ($sType == 'trash')
            {
                Mail_Service_Process::instance()->deleteTrash($this->_oReq->get('id'));
                $sMessage = Phpfox::getPhrase('mail.mail_deleted_successfully');
            }
            else
            {
                Mail_Service_Process::instance()->delete($this->_oReq->get('id'), ($sType == 'sentbox' ? true : false));
                $sMessage = Phpfox::getPhrase('mail.mail_deleted_successfully');
            }
        }

        if (!Phpfox_Error::isPassed())
        {
            $aErrors = Phpfox_Error::get();
            return array(
                'error_code' => 1,
                'error_message' => html_entity_decode($aErrors[0])
            );
        }

        return array(
            'result' => 1,
            'error_code' => 0,
            'message' => html_entity_decode($sMessage)
        );
    }
    /**
     * Input data:
     * + iLastItemId: int, optional.
     * + iLimit: int, optional.
     * + sAction: string, optional.
     * 
     * Output data:
     * + mail_id: int.
	 * + parent_id: int.
	 * + mass_id: int.
	 * + subject: string.
	 * + preview: string.
	 * + owner_user_id: int.
	 * + owner_folder_id: int.
	 * + owner_type_id: int.
	 * + viewer_user_id: int.
	 * + viewer_folder_id: int.
	 * + viewer_type_id: int.
	 * + viewer_is_new: int.
	 * + time_stamp: int.
	 * + time_updated: int.
	 * + total_attachment: int.
	 * + text_reply: string.
	 * + text: string.
	 * + owner_profile_page_id: int.
	 * + user_owner_server_id: int.
	 * + owner_user_name: string.
	 * + owner_full_name: string.
	 * + owner_gender: int.
	 * + owner_user_image: string.
	 * + owner_is_invisible: bool.
	 * + owner_user_group_id: int.
	 * + owner_language_id: int.
	 * + viewer_profile_page_id: int.
	 * + viewer_server_id: int.
	 * + viewer_user_name: int.
	 * + viewer_full_name: string.
	 * + viewer_gender: int.
	 * + viewer_user_image: string.
	 * + viewer_is_invisible: bool.
	 * + viewer_user_group_id: int.
	 * + viewer_language_id: int.
     * + owner_user_image: string.
     * + viewer_user_image: string.
     * + Time: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see message/inbox
     * 
     * @param array $aData
     * @return array
     */
    public function inbox($aData)
    {
        /**
         * @var int
         */
        $iLastItemId = isset($aData['iLastItemId']) ? (int) $aData['iLastItemId'] : 0;
        /**
         * @var int
         */
        $iLimit = isset($aData['iAmountOfMessage']) ? (int) $aData['iAmountOfMessage'] : 10;
        $iPage = isset($aData['iPage']) ? (int) $aData['iPage'] : 1;
        /**
         * @var string
         */
        $sAction = isset($aData['sAction']) ? $aData['sAction'] : 'inbox';
        $this->database()
                ->select('m.*, mreply.text AS text_reply,mt.text AS text, mf.name AS sNameOfFolder, ' . Phpfox::getUserField('u', 'owner_') . ', ' . Phpfox::getUserField('u2', 'viewer_'))
                ->from(Phpfox::getT('mail'), 'm')
                ->join(Phpfox::getT('mail_text'), 'mt', 'mt.mail_id = m.mail_id')
                ->leftjoin(Phpfox::getT('user'), 'u', 'u.user_id = m.owner_user_id')
                ->join(Phpfox::getT('user'), 'u2', 'u2.user_id = m.viewer_user_id')
                ->leftJoin(Phpfox::getT('mail_text'), 'mreply', 'mreply.mail_id = m.parent_id')
                ->leftJoin(Phpfox::getT('mail_folder'), 'mf', 'mf.folder_id = m.viewer_folder_id');

        $aCond = array();
        switch ($sAction) {
            case 'sent':
                $aCond[] = ' AND m.owner_user_id = ' . Phpfox::getUserId() . ' AND m.owner_type_id = 0' . ($iLastItemId ? ' AND m.mail_id < ' . (int) $iLastItemId : '');
                break;
            case 'trash':
                $aCond[] = ' AND ((m.viewer_user_id = ' . Phpfox::getUserId() . ' AND m.viewer_type_id = 1) OR (m.owner_user_id = ' . Phpfox::getUserId() . ' AND m.owner_type_id = 1))' . ($iLastItemId ? ' AND m.mail_id < ' . (int) $iLastItemId : '');
                break;
            case 'inbox':
            default:
                $aCond[] = (' AND m.viewer_user_id = ' . Phpfox::getUserId() . ' AND m.viewer_type_id =0 ' . ($iLastItemId ? 'AND m.mail_id < ' . (int) $iLastItemId : '') . '');
                break;
        }

        if (isset($aData['sSearch']))
        {
            $aCond[] = ' AND m.subject LIKE \'%' . $aData['sSearch'] . '%\' ';
        }

        $aRows = $this->database()
                ->where($aCond)
                ->order('m.mail_id DESC')
                ->limit($offset = ($iPage - 1) * $iLimit, (int) $iLimit)
                ->execute('getSlaveRows');

		$aResults = array();
        foreach ($aRows as $index => $aRow)
        {
            if($aRow['owner_user_id'] == Phpfox::getUserId()){
                // sent
                $sNameOfFolder = 'sent';
            } else if($aRow['viewer_user_id'] == Phpfox::getUserId()){
                // inbox
                $sNameOfFolder = 'inbox';
                // check inbox/sent or other folder
                if ($aRow['viewer_folder_id'] > 0){
                    // other folder 
                    $sNameOfFolder = empty($aRow['sNameOfFolder']) ? '' : $aRow['sNameOfFolder'];
                }
            }

            if ($aRow['owner_user_image'])
            {
                $aRow['owner_user_image'] = Phpfox::getParam('core.url_user') . sprintf($aRow['owner_user_image'], '_50_square');
            }
            else
            {
                $aRow['owner_user_image'] = Phpfox::getService('mfox.user')->getImageUrl(array(), '_50_square');
            }
            if ($aRow['viewer_user_image'])
            {
                $aRow['viewer_user_image'] = Phpfox::getParam('core.url_user') . sprintf($aRow['viewer_user_image'], '_50_square');
            }
            else
            {
                $aRow['viewer_user_image'] = Phpfox::getService('mfox.user')->getImageUrl(array(), '_50_square');
            }
            $aRow['Time'] = Phpfox::getLib('date')->convertTime((int) $aRow['time_stamp']);

						$aResult = array(
							"iConversationId" => $aRow['mail_id'],
							"iOwnerId" => $aRow['owner_user_id'],
							"iOwnerLevelId" => $aRow['owner_user_group_id'],
							"iTimeStamp" => $aRow['time_stamp'],
							"iViewerId" => $aRow['viewer_user_id'],
							"iViewerLevelId" => $aRow['viewer_user_group_id'],
                            // "sBody" => $aRow['text'],
							"sBody" => Phpfox::getLib('parse.bbcode')->cleanCode(Phpfox::getLib('parse.output')->clean($aRow['preview'])),
							"sOwnerFullName" => $aRow['owner_full_name'],
							"sOwnerImage" => $aRow['owner_user_image'],
							"sOwnerUserName" => $aRow['owner_user_name'],
							"sTime" => $aRow['Time'],
							"sTimeConverted" => $aRow['Time'],
							"sTitle" => $aRow['subject'],
							"sViewerFullName" => $aRow['viewer_full_name'],
                            "sViewerImage" => $aRow['viewer_user_image'],
                            "bIsReply" => ((int)$aRow['parent_id'] > 0 ? true : false),
                            "sNameOfFolder" => $sNameOfFolder,
							"sViewerUserName" => $aRow['viewer_user_name']
						);
                        if('sent' == $sAction){
                            $aResult['bIsRead'] = true;
                        } else {
                            $aResult['bIsRead'] = $aRow['viewer_is_new'] == 1 ? false : true;
                        }

						$aResults[] = $aResult;
        }
        return $aResults;
    }
    /**
     * Input data:
     * + iItemId: int, required.
     * 
     * Output data:
	 * + parent_id: int.
	 * + mass_id: int.
	 * + subject: string.
	 * + preview: string.
	 * + owner_user_id: int.
	 * + owner_folder_id: int.
	 * + owner_type_id: int.
	 * + viewer_user_id: int.
	 * + viewer_folder_id: int.
	 * + viewer_type_id: int.
	 * + viewer_is_new: int.
	 * + time_stamp: int.
	 * + time_updated: int.
	 * + total_attachment: int.
	 * + text_reply: string.
	 * + text: string.
	 * + owner_profile_page_id: int.
	 * + user_owner_server_id: int.
	 * + owner_user_name: string.
	 * + owner_full_name: string.
	 * + owner_gender: int.
	 * + owner_user_image: string.
	 * + owner_is_invisible: bool.
	 * + owner_user_group_id: int.
	 * + owner_language_id: int.
	 * + viewer_profile_page_id: int.
	 * + viewer_server_id: int.
	 * + viewer_user_name: int.
	 * + viewer_full_name: string.
	 * + viewer_gender: int.
	 * + viewer_user_image: string.
	 * + viewer_is_invisible: bool.
	 * + viewer_user_group_id: int.
	 * + viewer_language_id: int.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see message/detail
     * 
     * @param array $aData
     * @return array
     */
    public function detail($aData)
    {
        $this->_oReq->set(array(
            'id' => !empty($aData['iItemId']) ? (int) $aData['iItemId'] : null
        ));

        Phpfox::isUser(true);
        if (!Phpfox::getParam('mail.threaded_mail_conversation'))
        {
            return array();
        }

        $iThreadId = $this->_oReq->getInt('id');
        
        list($aThread, $aMessages) = Mail_Service_Mail::instance()->getThreadedMail($iThreadId);
        
        if ($aThread === false)
        {
            return array(
                'error_code' => 1,
                'error_message' => html_entity_decode(Phpfox::getPhrase('mail.unable_to_find_a_conversation_history_with_this_user'))
            );
        }       

        if ($aThread['user_is_archive'])
        {
            $this->_oReq->set('view', 'trash');
        }

        Mail_Service_Process::instance()->threadIsRead($aThread['thread_id']);

        $iUserCnt = 0;
        $aUsers = array();
        $bCanViewThread = false;    
        foreach ($aThread['users'] as $aUser)
        {   
            if ($aUser['user_id'] == Phpfox::getUserId())
            {
                $bCanViewThread = true;
            }
            
            if ($aUser['user_id'] == Phpfox::getUserId())
            {
                continue;
            }           
            
            $iUserCnt++;

            $aUsers[] = $aUser;
        }
        
        if (!$bCanViewThread)
        {           
            return array(
                'error_code' => 1,
                'error_message' => html_entity_decode('Unable to view this thread.')
            );
        }

        if (!Phpfox_Error::isPassed())
        {
            $aErrors = Phpfox_Error::get();
            return array(
                'error_code' => 1,
                'error_message' => html_entity_decode($aErrors[0])
            );
        }

        $this->processMessageRows($aMessages);

        return $aMessages;
    }

    public function processMessageRows(&$aRows)
    {
        $aTmpRows = $aRows;
        $aRows = array();

        foreach ($aTmpRows as $aRow) {
            $aRows[] = array(
                'iMessageId' => $aRow['message_id'],
                'iConversationId' => $aRow['thread_id'],
                'iUserId' => $aRow['user_id'],
                'sBody' => Phpfox::getLib('phpfox.parse.output')->parse($aRow['text']),
                'iTimeStamp' => $aRow['time_stamp'],
                'iTotalAttachment' => $aRow['total_attachment'],
                'iHasForward' => $aRow['has_forward'],
                'iProfilePageId' => $aRow['profile_page_id'],
                'sUserName' => $aRow['user_name'],
                'sFullName' => $aRow['full_name'],
                'iGender' => $aRow['gender'],
                'sUserImage' => Phpfox::getService('mfox.user')->getImageUrl($aRow, '_50_square'),
                'iIsInvisible' => $aRow['is_invisible'],
                'aForwards' => $aRow['forwards'],
            );
        }
    }

    /**
     * Input data:
     * + iLastItemId: int, optional.
     * + iLimit: int, optional.
     * 
     * Output data:
     * + mail_id: int.
	 * + parent_id: int.
	 * + mass_id: int.
	 * + subject: string.
	 * + preview: string.
	 * + owner_user_id: int.
	 * + owner_folder_id: int.
	 * + owner_type_id: int.
	 * + viewer_user_id: int.
	 * + viewer_folder_id: int.
	 * + viewer_type_id: int.
	 * + viewer_is_new: int.
	 * + time_stamp: int.
	 * + time_updated: int.
	 * + total_attachment: int.
	 * + text_reply: string.
	 * + text: string.
	 * + owner_profile_page_id: int.
	 * + user_owner_server_id: int.
	 * + owner_user_name: string.
	 * + owner_full_name: string.
	 * + owner_gender: int.
	 * + owner_user_image: string.
	 * + owner_is_invisible: bool.
	 * + owner_user_group_id: int.
	 * + owner_language_id: int.
	 * + viewer_profile_page_id: int.
	 * + viewer_server_id: int.
	 * + viewer_user_name: int.
	 * + viewer_full_name: string.
	 * + viewer_gender: int.
	 * + viewer_user_image: string.
	 * + viewer_is_invisible: bool.
	 * + viewer_user_group_id: int.
	 * + viewer_language_id: int.
     * + owner_user_image: string.
     * + viewer_user_image: string.
     * + Time: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see message/sent
     * 
     * @param array $aData
     * @return array
     */
    public function sent($aData)
    {
        $aData['sAction'] = 'sent';
        return $this->inbox($aData);
    }
    
    /**
     * Input data:
     * + iLastItemId: int, optional.
     * + iLimit: int, optional.
     * 
     * Output data:
     * + mail_id: int.
	 * + parent_id: int.
	 * + mass_id: int.
	 * + subject: string.
	 * + preview: string.
	 * + owner_user_id: int.
	 * + owner_folder_id: int.
	 * + owner_type_id: int.
	 * + viewer_user_id: int.
	 * + viewer_folder_id: int.
	 * + viewer_type_id: int.
	 * + viewer_is_new: int.
	 * + time_stamp: int.
	 * + time_updated: int.
	 * + total_attachment: int.
	 * + text_reply: string.
	 * + text: string.
	 * + owner_profile_page_id: int.
	 * + user_owner_server_id: int.
	 * + owner_user_name: string.
	 * + owner_full_name: string.
	 * + owner_gender: int.
	 * + owner_user_image: string.
	 * + owner_is_invisible: bool.
	 * + owner_user_group_id: int.
	 * + owner_language_id: int.
	 * + viewer_profile_page_id: int.
	 * + viewer_server_id: int.
	 * + viewer_user_name: int.
	 * + viewer_full_name: string.
	 * + viewer_gender: int.
	 * + viewer_user_image: string.
	 * + viewer_is_invisible: bool.
	 * + viewer_user_group_id: int.
	 * + viewer_language_id: int.
     * + owner_user_image: string.
     * + viewer_user_image: string.
     * + Time: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see message/trash
     * 
     * @param array $aData
     * @return array
     */
    public function trash($aData)
    {
        $aData['sAction'] = 'trash';
        return $this->inbox($aData);
    }        

	public function conversation_detail($aData) 
    {
        $this->_oReq->set(array(
            'id' => !empty($aData['iConversationId']) ? (int) $aData['iConversationId'] : null
        ));

        Phpfox::isUser(true);
        if (!Phpfox::getParam('mail.threaded_mail_conversation'))
        {
            return array(); // ignore error
        }

        $iThreadId = $this->_oReq->getInt('id');
        
        list($aThread, $aMessages) = Mail_Service_Mail::instance()->getThreadedMail($iThreadId);
        
        if ($aThread === false)
        {
            return array(); // ignore error
        }       

        if ($aThread['user_is_archive'])
        {
            $this->_oReq->set('view', 'trash');
        }

        Mail_Service_Process::instance()->threadIsRead($aThread['thread_id']);

        $iUserCnt = 0;
        $aUsers = array();
        $bCanViewThread = false;    
        foreach ($aThread['users'] as $aUser)
        {   
            if ($aUser['user_id'] == Phpfox::getUserId())
            {
                $bCanViewThread = true;
            }
            
            if ($aUser['user_id'] == Phpfox::getUserId())
            {
                continue;
            }           
            
            $iUserCnt++;

            $aUsers[] = $aUser;
        }
        
        if (!$bCanViewThread)
        {           
            return array(); // ignore error
        }

        if (!Phpfox_Error::isPassed())
        {
            return array(); // ignore error
        }

        $this->processUserRows($aUsers);

        return array(
            'aRecipients' => $aUsers
        );
	}

    public function processUserRows(&$aRows)
    {
        $aTmpRows = $aRows;
        $aRows = array();

        foreach ($aTmpRows as $aRow) {
            $aRows[] = array(
                'iUserId' => $aRow['user_id'],
                'sUserName' => $aRow['user_name'],
                'sFullName' => $aRow['full_name']
            );
        }
    }

    public function attach($aData){
        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_logged_in_users")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        // If no images were uploaded lets get out of here.        
        if (!isset($_FILES['image']))
        {
            return array(
                'error_code' => 2,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_file")),
                'result' => 0
            );
        }

        $_FILES['file'] = $_FILES['image'];
        // $custom_attachment = photo/video
        $custom_attachment = isset($_FILES['sCustomAttachment']) ? ($_FILES['sCustomAttachment']) : 'photo';
        $input = isset($_FILES['input']) ? ($_FILES['input']) : '';
        $category_name = isset($_FILES['sCategoryName']) ? ($_FILES['sCategoryName']) : 'mail';

        $oFile = Phpfox::getLib('file');
        $oImage = Phpfox::getLib('image');
        
        $oAttachment = Phpfox::getService('attachment.process');        
        
        $sIds = '';     
        $sStr = '';
        $iUploaded = 0;
        $iFileSizes = 0;

        if (isset($_FILES['file']['error'])){
            $sError = $_FILES['file']['error'];
            if ($sError == UPLOAD_ERR_OK) {
                $aValid = array('gif', 'png', 'jpg');
                if ($custom_attachment == 'photo')
                {
                    $aValid = array('gif', 'png', 'jpg');   
                }
                elseif ($custom_attachment == 'video')
                {
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_attach_video")),
                        'result' => 0
                    );
                    // $aValid = Phpfox::getService('video')->getFileExt();    
                }

                $aImage = $oFile->load('file'
                    , (($input == '' && $custom_attachment == '') ? Phpfox::getService('attachment.type')->getTypes() : $aValid)
                    , (Phpfox::getUserParam('attachment.item_max_upload_size') === 0 ? null : (Phpfox::getUserParam('attachment.item_max_upload_size') / 1024))
                );

                if ($aImage !== false){
                    if (!Phpfox::getService('attachment')->isAllowed())
                    {
                        return array(
                            'error_code' => 3,
                            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.failed_limit_reached_attachment")),
                            'result' => 0
                        );
                    }
                    
                    $iUploaded++;
                    $bIsImage = in_array($aImage['ext']
                        , Phpfox::getParam('attachment.attachment_valid_images'));
                    
                    $iId = $oAttachment->add(array(
                            'category' => $category_name,
                            'file_name' => $_FILES['file']['name'],                      
                            'extension' => $aImage['ext'],
                            'is_image' => $bIsImage
                        )
                    );

                    $sIds .= $iId . ',';
                    
                    $sFileName = $oFile->upload('file', Phpfox::getParam('core.dir_attachment'), $iId);

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
                        return array(
                            'error_code' => 1,
                            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_attach_video")),
                            'result' => 0
                        );
                    }
                    else 
                    {
                        // echo '<script type="text/javascript">window.parent.$(\'#' . $this->_oReq->get('upload_id') . '\').find(\'.js_upload_form_image_holder:first\').html(\'<div class="js_upload_form_image_holder_image">' . Phpfox::getLib('image.helper')->display(array('theme' => 'misc/accept.png')) . '</div>Completed ' . strip_tags($_FILES['file']['name'][$iKey]) . '\');</script>';
                    }
                } 
                else 
                {
                    $errors = Phpfox_Error::get();
                    return array(
                        'error_code' => 4,
                        'error_message' => isset($errors[0]) ? $errors[0] : 'Get errors when attach file. Please try again.', 
                        'result' => 0
                    );
                    // echo '<script type="text/javascript">window.parent.$(\'#' . $this->_oReq->get('upload_id') . '\').find(\'.js_upload_form_image_holder:first\').html(\'<div class="js_upload_form_image_holder_image">' . Phpfox::getLib('image.helper')->display(array('theme' => 'misc/delete.png')) . '</div>Failed ' . strip_tags($_FILES['file']['name'][$iKey]) . ' <br /> <div class='error_message'>' . implode(' ', Phpfox_Error::get()) . '</div>\');</script>';
                }
            }
        }

        if (!$iUploaded)
        {
            return array(
                'error_code' => 4,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.get_errors_when_attach_file_please_try_again")),
                'result' => 0
            );
            // exit;
        }   

        if ($custom_attachment == 'photo' || $custom_attachment == 'video'){
            $aAttachment = Phpfox::getLib('database')->select('*')
                ->from(Phpfox::getT('attachment'))
                ->where('attachment_id = ' . (int) $iId)
                ->execute('getSlaveRow');
            
            if ($custom_attachment == 'photo')
            {               
                $sImagePath = Phpfox::getLib('image.helper')->display(array('server_id' => $aAttachment['server_id']
                    , 'path' => 'core.url_attachment'
                    , 'file' => $aAttachment['destination']
                    , 'suffix' => '_view'
                    // fix issue: https://jira.younetco.com/browse/FMOBI-1312
                    // , 'max_width' => 'attachment.attachment_max_medium'
                    // , 'max_height' =>'attachment.attachment_max_medium'
                    , 'return_url' => true));

                $sImagePath = '[img]' . $sImagePath . '[/img]';
                            }
            else
            {
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_attach_video")),
                    'result' => 0
                );
            }                       
        } else {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_attach_this_type")),
                'result' => 0
            );            
        }

        // Update user space usage
        Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'attachment', $iFileSizes);       

        return array(
            'error_code' => 0,
            'error_message' => '', 
            'sImagePath' => $sImagePath, 
            'result' => 1
        );            

    }

    public function fetch($aData)
    {
        $this->_oReq->set(array(
            'view' => !empty($aData['filter']) ? $aData['filter'] : '',
            'page' => !empty($aData['iPage']) ? (int) $aData['iPage'] : 1,
            'show' => !empty($aData['iAmountOfBlog']) ? (int) $aData['iAmountOfBlog'] : 10,
            'search' => !empty($aData['sSearch']) ? $aData['sSearch'] : '',
            'sort' => !empty($aData['sOrder']) ? $aData['sOrder'] : '',
            'legacy' => !empty($aData['legacy']) ? $aData['legacy'] : null,
        ));

        Phpfox::isUser(true);       
        
        $bIsInLegacyView = false;
        if (Phpfox::getParam('mail.threaded_mail_conversation') && $this->_oReq->get('legacy'))
        {               
            Phpfox::getLib('setting')->setParam('mail.threaded_mail_conversation', false);
            $bIsInLegacyView = true;            
        }

        $iPage = $this->_oReq->getInt('page');
        $iPageSize = $this->_oReq->getInt('show');
        $bIsSentbox = ($this->_oReq->get('view') == 'sent' ? true : false);
        $bIsTrash = ($this->_oReq->get('view') == 'trash' ? true : false);
        $iPrivateBox = ($this->_oReq->get('view') == 'box' ? $this->_oReq->getInt('id') : false);

        // search
        $sSearch = $this->_oReq->get('search');
        if (!empty($sSearch))
        {
            $this->_oSearch->setCondition('AND (' 
                . ' m.subject LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"'
                . ' OR m.preview LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $sSearch . '%') . '"' 
                . ')');
        }

        // sort
        $sSort = '';
        switch ($this->_oReq->get('sort')) {
            case 'unread_first':
                $sSort = 'm.viewer_is_new DESC';
                break;
            default:
                $sSort = 'm.time_stamp DESC';
                break;
        }

        $this->_oSearch->setSort($sSort);

        $aFolders = Phpfox::getService('mail.folder')->get();

        $sFolder = '';
        if (Phpfox::getParam('mail.threaded_mail_conversation'))
        {               
            if ($bIsTrash)
            {
                $this->_oSearch->setCondition('AND m.owner_user_id = ' . Phpfox::getUserId() . ' AND m.is_archive = 1');
            }
            else
            {
                $this->_oSearch->setCondition('AND m.viewer_user_id = ' . Phpfox::getUserId() . ' AND m.is_archive = 0');
            }
        }
        else
        {
            if ($bIsTrash)
            {
                $sFolder = Phpfox::getPhrase('mail.trash');
                $this->_oSearch->setCondition('AND (m.viewer_user_id = ' . Phpfox::getUserId() . ' AND m.viewer_type_id = 1) OR (m.owner_user_id = ' . Phpfox::getUserId() . ' AND m.owner_type_id = 1)');                  
            }
            elseif ($iPrivateBox)
            {
                if (isset($aFolders[$iPrivateBox]))
                {
                    $sFolder = $aFolders[$iPrivateBox]['name'];
                    $this->_oSearch->setCondition('AND m.viewer_folder_id = ' . (int) $iPrivateBox . ' AND m.viewer_user_id = ' . Phpfox::getUserId() . ' AND m.viewer_type_id = 0');
                }
                else
                {
                    return array(
                        'error_code' => 1,
                        'error_message' => html_entity_decode(Phpfox::getPhrase('mail.mail_folder_does_not_exist'))
                    );
                }
            }
            else
            {
                if ($bIsSentbox)
                {
                    $sFolder = Phpfox::getPhrase('mail.sent_messages');
                    $this->_oSearch->setCondition('AND m.owner_user_id = ' . Phpfox::getUserId() . ' AND m.owner_type_id = 0');
                }
                else
                {
                    $sFolder = Phpfox::getPhrase('mail.inbox');
                    $this->_oSearch->setCondition('AND m.viewer_folder_id = 0 AND m.viewer_user_id = ' . Phpfox::getUserId() . ' AND m.viewer_type_id = 0');
                }
            }
        }
        
        list($iCnt, $aRows, $aInputs) = Mail_Service_Mail::instance()->get($this->_oSearch->getConditions(), $this->_oSearch->getSort(), $this->_oSearch->getPage(), $iPageSize, $bIsSentbox, $bIsTrash);

        $this->processRows($aRows, $sFolder);

        return $aRows;
    }

    public function processRows(&$aRows, $sFolder = '')
    {
        $aTmpRows = $aRows;
        $aRows = array();
        $bIsThread = Phpfox::getParam('mail.threaded_mail_conversation');

        foreach ($aTmpRows as $aRow)
        {
            $aRows[] = array(
                'iConversationId' => $bIsThread ? $aRow['thread_id'] : $aRow['mail_id'],
                'sTitle' => $this->getMailTitle($aRow),
                'sBody' => Phpfox::getLib('parse.bbcode')->cleanCode(Phpfox::getLib('parse.output')->clean($aRow['preview'])),
                'iTimeStamp' => $aRow['time_stamp'],
                'sNameOfFolder' => $sFolder,
                'bIsRead' => $aRow['viewer_is_new'] ? false : true,
                'iOwnerId' => $aRow['user_id'],
                'sUserImage' => Phpfox::getService('mfox.user')->getImageUrl($aRow, '_50_square'),
                'sFullName' => $aRow['full_name'],
                'sUserName' => $aRow['user_name'],
            );
        }
    }

    public function getMailTitle($aRow)
    {
        $sTitle = '';

        if (Phpfox::getParam('mail.threaded_mail_conversation'))
        {
            if (count($aRow['users']))
            {
                $iUserCnt = 0;
                foreach ($aRow['users'] as $aUser)
                {
                    $iUserCnt++;
                    if (count($aRow['users']) == $iUserCnt && count($aRow['users']) > 1)
                    {
                        $sTitle .= ' &amp; ';
                    } 
                    else
                    {
                        if ($iUserCnt != 1 && count($aRow['users']) != 2)
                        {
                            $sTitle .= ', ';
                        }
                    }
                    $sTitle .= Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('phpfox.parse.output')->clean($aUser['full_name']), 35, '...');
                }
            }
        } 
        else
        {
            if ($aRow['parent_id'])
            {
                $sTitle .= Phpfox::getPhrase('mail.re') . ': ';
            }
            $sTitle .= Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('phpfox.parse.output')->clean($aRow['subject']), 35, '...');
        }

        return $sTitle;
    }

    public function fetchfriend($aData)
    {
        extract($aData, EXTR_SKIP);

        $iUserId = Phpfox::getUserId();
        
        if (!isset($iAmountOfFriend))
        {
            $iAmountOfFriend = 20;
        }

        if (!isset($sSearch) || strlen(trim($sSearch)) == 0)
        {
            $sSearch = false;
        }
        if (!isset($aData['iPage'])){
            $aData['iPage'] = 1;
        }
        if ((int)$aData['iPage'] == 0){
            return array();
        }

        list($iCnt, $aRows) = Phpfox::getService('mfox.friend')->getFromCache(false, $sSearch, $aData['iPage'], $iAmountOfFriend, true);

        if ($iCnt < ($iPage - 1) * $iAmountOfFriend){
            return array();
        }

        $aResult = array();
        
        foreach ($aRows as $aFriend)
        {
            $sImageUrl = Phpfox::getService('mfox.user')->getImageUrl($aFriend, '_50_square');
            
            $aResult[] = array(
                'id' => $aFriend['user_id'], // id of the friend
                'sFullName' => $aFriend['full_name'],
                'UserProfileImg_Url' => $sImageUrl,
            );
        }

        return $aResult;
    }    

}
