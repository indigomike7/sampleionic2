<?php

if (Phpfox::isModule('mfox'))
{
    if (isset($aVals['message']) && $iId > 0)
    {
        $sMessage = Phpfox::getUserBy('full_name').': '.Phpfox::getLib('parse.input')->prepare($aVals['message']);

        $sMessage = Phpfox::getLib('parse.output')->shorten(strip_tags($sMessage), 97, '...');

        $sMessage = Phpfox::getService('mfox')->decodeUtf8Compat($sMessage);
        
        $receivers = !empty($aOriginal) ? $aOriginal : (isset($aVals['user_id']) ? array($aVals['user_id']) : null);

        if (!empty($receivers)) {
            foreach ($receivers as $userId) {
                Phpfox::getService('mfox.cloudmessage')->send(array(
                    'ios' => array(
                        'aps' => array(
                            'alert' => $sMessage,
                            'badge' => 1,
                            'sound' => 'default'
                        ),
                        'iId' => $iId,
                        'sType' => 'mail'
                    ),
                    'android' => array(
                        'message' => $sMessage,
                        'iId' => $iId,
                        'sType' => 'mail'
                    )
                ), $userId);
            }
        }
    }
}

?>