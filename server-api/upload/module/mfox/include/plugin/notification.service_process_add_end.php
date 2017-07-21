<?php

if (Phpfox::isModule('mfox'))
{
    if ($iId > 0)
    {
        $aRow = $this->database()->select('n.*, n.user_id as item_user_id, '.Phpfox::getUserField())->from($this->_sTable, 'n')->join(Phpfox::getT('user'), 'u', 'u.user_id = n.owner_user_id')->where('n.notification_id = '.$iId)->execute('getSlaveRow');
        if (!empty($aRow))
        {
            if (substr($aRow['type_id'], 0, 8) != 'comment_' && !Phpfox::hasCallback($aRow['type_id'], 'getNotification'))
            {
                $aCallBack['link'] = '#';
                $aCallBack['message'] = '2. Notification is missing a callback. ['.$aRow['type_id'].'::getNotification]';
    
            }
            elseif (substr($aRow['type_id'], 0, 8) == 'comment_' && substr($aRow['type_id'], 0, 12) != 'comment_feed' && !Phpfox::hasCallback(substr_replace($aRow['type_id'], '', 0, 8), 'getCommentNotification'))
            {
                $aCallBack['link'] = '#';
                $aCallBack['message'] = 'Notification is missing a callback. ['.substr_replace($aRow['type_id'], '', 0, 8).'::getCommentNotification]';
            }
            else
            {
                $iCurUserId = Phpfox::getUserId();
                Phpfox::getService('user.auth')->setUserId($aRow['item_user_id']);
                $aCallBack = Phpfox::callback($aRow['type_id'].'.getNotification', $aRow);
                Phpfox::getService('user.auth')->setUserId($iCurUserId);
    
                if ($aCallBack === false)
                {
                    $aCallBack['message'] = '';
                }
            }
            
            if (!empty($aCallBack['message']))
            {
                $sMessage = Phpfox::getLib('parse.output')->shorten(strip_tags($aCallBack['message']), 97, '...');

                $sMessage = Phpfox::getService('mfox')->decodeUtf8Compat($sMessage);

                Phpfox::getService('mfox.cloudmessage')->send(array(
                    'ios' => array(
                        'aps' => array(
                            'alert' => $sMessage,
                            'badge' => 1,
                            'sound' => 'default'
                        ),
                        'iId' => $iId,
                        'sType' => 'notification'
                    ), 'android' => array(
                        'message' => $sMessage,
                        'iId' => $iId,
                        'sType' => 'notification'
                    )
                ), $iOwnerUserId, $aInsert['type_id']);
            }
        }
    }
}

?>