<?php

if ($iFriendId && Phpfox::isModule('mfox'))
{
    if ($iId > 0)
    {
        $aRow = $this->database()->select('fr.*, '.Phpfox::getUserField())
        ->from($this->_sTable, 'fr')
        ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = fr.friend_user_id')
        ->where('request_id = '.$iId)
        ->execute('getSlaveRow');
        
        if (!empty($aRow))
        {
            if ($aRow['relation_data_id'] > 0)
            {
                $aRelation = $this->database()->select('phrase_var_name')->from(Phpfox::getT('custom_relation_data'), 'crd')->where('crd.relation_data_id = '.$aRow['relation_data_id'])->join(Phpfox::getT('custom_relation'), 'cr', 'cr.relation_id = crd.relation_id')->order('crd.relation_data_id DESC')->limit(1)->execute('getSlaveField');
                if (!empty($aRelation))
                {
                    $aRow['message'] = Phpfox::getPhrase('mfox.full_name_wants_to_list_you_both_as_relationship', array('full_name' => $aRow['full_name'], 'relationship' => Phpfox::getPhrase($aRelation)));
                }
                else
                {
                    $aRow['message'] = Phpfox::getPhrase('mfox.full_name_wants_to_list_you_in_a_relationship', array('full_name' => $aRow['full_name']));
                }
            }
            else
            {
                $aRow['message'] = Phpfox::getPhrase('mfox.user_want_to_add_you_as_gender_friends', array('full_name' => $aRow['full_name'], 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1)));
            }
            
            $sMessage = Phpfox::getLib('parse.output')->shorten(strip_tags($aRow['message']), 97, '...');

            $sMessage = Phpfox::getService('mfox')->decodeUtf8Compat($sMessage);

            Phpfox::getService('mfox.cloudmessage')->send(array(
                'ios' => array(
                    'aps' => array(
                        'alert' =>$sMessage,
                        'badge' => 1,
                        'sound' => 'default'

                    ),
                    'iId' => $iId,
                    'sType' => 'friend_request'
                ),
                'android' => array(
                    'message' => $sMessage,
                    'iId' => $iId,
                    'sType' => 'friend_request'
                )
            ), $iFriendId);
        }
    }
}

?>