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
 * @link Mfox Api v2.0
 */
class Mfox_Service_Report extends Phpfox_Service {

    /**
     * Input data:
     * + iItemId: int, required.
     * + sItemType: string, required.
     *
     * Output data:
     * + iReportId: int.
     * + sDescription: string.
     *
     */
    public function reason($aData)
    {
        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_logged_in_users")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $aData['sType'] = $sItemType;        
        $sType = isset($aData['sType']) ? $aData['sType'] : '';
        $aReasons = Phpfox::getService('report')->getOptions($sType);

        $aResult = array();
        foreach($aReasons as $i => $aReason)
        {
            $aMatches = null;
            preg_match('/\{phrase var\=&#039;(.*)&#039;\}/ise', $aReason['message'], $aMatches);
            $sMessage = isset($aMatches[1]) ?  Phpfox::getPhrase($aMatches[1]) : $aReasons[$i]['message'];
            
            $aResult[] = array(
                'iReportId' => $aReason['report_id'],
                'sMessage' => Phpfox::getService('mfox')->decodeUtf8Compat($sMessage)
            );
        }
        return $aResult;
    }
 
    /**
     * Input data:
     * + sCategory: int, required.
     * + sDescription: string, required.
     * + iItemId: int, required.
     * + sItemType: string, required.
     *
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     * + message: string.
     *
     */
    public function add($aData)
    {
        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_logged_in_users")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        $sDescription = isset($aData['sDescription']) ? $aData['sDescription'] : '';
        $aData['sFeedback'] = $sDescription;
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $aData['sType'] = $sItemType;        
        $sFeedback = isset($aData['sFeedback']) ? $aData['sFeedback'] : '';
        $iItemId = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;
        $sType = isset($aData['sType']) ? $aData['sType'] : '';
        $iReport = isset($aData['iReport']) ? (int) $aData['iReport'] : 0;
        $sModule = isset($aData['sModule']) ? $aData['sModule'] : '';
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;

        if (!trim($sDescription)|| !$iItemId || !trim($sItemType))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.invalid_data"))
            );
        }
        
        $oReport = Phpfox::getService('report');
        $aVals = array(
            'type' => $sType,
            'id' => $iItemId
        );

        if (!Phpfox::getLib('parse.format')->isEmpty($sFeedback))
        {
            $aVals['feedback'] = $sFeedback;
        }
        else
        {
            $aVals['feedback'] = '';
            $aReasons = $oReport->getOptions($aVals['type']);
            $aReasonId = array();
            foreach ($aReasons as $aReason)
            {
                $aReasonId[$aReason['report_id']] = $aReason['report_id'];
            }

            if (!isset($aReasonId[$iReport]))
            {
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.reason_is_not_valid"))
                );
            }
        }
        $aVals['report'] = $iReport > 0 ? $iReport : '';
        
        // Check privacy.
        $sType = $this->changeType($sType);
        if($sType == 'feed'){
            $aFeed = Phpfox::getService('mfox.feed')->__getFeedByID($iItemId);
            if(isset($aFeed['feed_id'])){
                $sType = $aFeed['type_id'];
                $aVals['type'] = $aFeed['type_id'];
                $iItemId = $aFeed['item_id'];
                $aVals['id'] = $aFeed['item_id'];
            }
        }

        switch ($sType) {
            case 'advancedphoto':
            case 'photo':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnPhoto($iItemId);
                break;
            
            case 'advancedphoto_album':
            case 'photo_album':
                $aError = Phpfox::getService('mfox.photo')->checkPrivacyOnAlbum($iItemId);
                break;
            
            case 'music_song':
                $aError = Phpfox::getService('mfox.song')->checkPrivacyOnSong($iItemId);
                break;
            
            case 'music_album':
                $aError = Phpfox::getService('mfox.album')->checkPrivacyOnMusicAlbum($iItemId);
                break;
            
            case 'videochannel':
                $aError = Phpfox::getService('mfox.videochannel')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                break;

            case 'video':
                $aError = Phpfox::getService('mfox.video')->checkPrivacyOnVideo($iItemId, $sModule, $iItem);
                break;
            
            case 'user_status':
                $aError = Phpfox::getService('mfox.feed')->checkPrivacyOnUserStatusFeed($iItemId, $sType, $sModule, $iItem);
                break;

            case 'blog':
                $aError = Phpfox::getService('mfox.blog')->canView($iItemId);
                break;

            case 'poll':
                $aError = Phpfox::getService('mfox.poll')->canView($iItemId);
                break;

            case 'forum_post':
                // $aError = Phpfox::getService('mfox.forum')->canView($iItemId);
                break;

            case 'advancedmarketplace':
            case 'marketplace':
                $aError = Phpfox::getService('mfox.marketplace')->canView($iItemId);
                break;

            default:                
                break;
        }
        
        if (isset($aError))
        {
            return $aError;
        }
        
        if ($oReport->canReport($aVals['type'], $aVals['id']))
        {
            if ($bResult = Phpfox::getService('report.data.process')->add($aVals['report'], $aVals['type'], $aVals['id'], $aVals['feedback']))
            {
                return array(
                    'result' => 1,
                    'message'=>html_entity_decode(Phpfox::getPhrase("mfox.your_report_has_been_submitted"))
                );
            }
            else
            {
                return array(
                    'error_code' => 1,
                    'error_message' => implode(' ', Phpfox_Error::get())
                );
            }
        }
        else
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('report.you_have_already_reported_this_item')
            );
        }
    }
    
    /**
     * Change the type if needed.
     * @param string $sType
     * @return string
     */
    public function changeType($sType)
    {
        switch ($sType) {
            case 'feed_mini':
                break;

            default:
                break;
        }
        
        return $sType;
    }
}
