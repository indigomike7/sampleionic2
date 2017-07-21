<?php

class Mfox_Service_Ynchat extends Phpfox_Service
{
    protected $_emoticons = null;
    
    public function pingStatus($aData) 
    {
        $config = $this->getConfig(array());
        
        return $config['usersettings'];
    }
    
    public function getConfig($aData) 
    {
        if (false == Phpfox::isModule('ynchat')) 
        {
            return array(
                'error_code' => 1,
                'error_debug' => 'ynchat module is disabled or still not installed',
                'error_message' => html_entity_decode(Phpfox::getPhrase("mfox.chat_service_is_not_available"))
            );
        }
        
        return Phpfox::getService('ynchat')->alias_mfox_getConfig($aData);
    }
    
    public function threadInfo($aData) 
    {
        if (false == Phpfox::isModule('ynchat')) 
        {
            return array(
                'error_code' => 1,
                'error_debug' => 'ynchat module is disabled or still not installed',
                'error_message' => html_entity_decode(Phpfox::getPhrase("mfox.chat_service_is_not_available"))
            );
        }
        
        return Phpfox::getService('ynchat')->threadInfo($aData);
    }
    
    public function upload($aData) 
    {
        if (false == Phpfox::isModule('ynchat')) 
        {
            return array(
                'error_code' => 1,
                'error_debug' => 'ynchat module is disabled or still not installed',
                'error_message' => html_entity_decode(Phpfox::getPhrase("mfox.chat_service_is_not_available"))
            );
        }
        
        return Phpfox::getService('ynchat')->upload($aData);
    }
    
    public function getOldConversation($aData) 
    {
        if (false == Phpfox::isModule('ynchat')) 
        {
            return array(
                'error_code' => 1,
                'error_debug' => 'ynchat module is disabled or still not installed',
                'error_message' => html_entity_decode(Phpfox::getPhrase("mfox.chat_service_is_not_available"))
            );
        }
        
        return Phpfox::getService('ynchat')->getOldConversation($aData);
    }
    
    public function updateStatusGoOnline($aData) 
    {
        if (false == Phpfox::isModule('ynchat')) 
        {
            return array(
                'error_code' => 1,
                'error_debug' => 'ynchat module is disabled or still not installed',
                'error_message' => html_entity_decode(Phpfox::getPhrase("mfox.chat_service_is_not_available"))
            );
        }
        
        return Phpfox::getService('ynchat')->updateStatusGoOnline($aData);
    }
    
    public function getFriendList($aData) 
    {
        if (false == Phpfox::isModule('ynchat')) 
        {
            return array(
                'error_code' => 1,
                'error_debug' => 'ynchat module is disabled or still not installed',
                'error_message' => html_entity_decode(Phpfox::getPhrase("mfox.chat_service_is_not_available"))
            );
        }
        
        return Phpfox::getService('ynchat')->alias_getFriendList($aData);
    }
    
    public function update_agent($aData) 
    {
        if (false == Phpfox::isModule('ynchat')) 
        {
            return array(
                'error_code' => 1,
                'error_debug' => 'ynchat module is disabled or still not installed',
                'error_message' => html_entity_decode(Phpfox::getPhrase("mfox.chat_service_is_not_available"))
            );
        }
        
        return Phpfox::getService('ynchat')->alias_mfox_updateAgent($aData);
    }
    
    public function download($aData) 
    {
        return Phpfox::getService('ynchat')->alias_mfox_download($aData);
    }
    
    public function getAllEmoticonsReplacement() 
    {
        if (null == $this->_emoticons) 
        {
            $sPicUrl = Phpfox::getParam('core.url_pic');
            $sPicUrl = str_replace("index.php" . PHPFOX_DS, "PF.Base" . PHPFOX_DS, $sPicUrl);
            $path = $sPicUrl . 'ynchat_emoticon' . PHPFOX_DS;
            
            $aRows = Phpfox::getService('ynchat')->getAllEmoticon();
            
            foreach ($aRows as $aItem) 
            {
                $from = '<img src="' . $path . $aItem['image'] . '" alt="' . $aItem['title'] . '" title="' . $aItem['title'] . '" class="v_middle" />';
                $to = ' ' . $aItem['text'] . ' ';
                
                $this->_emoticons[$from] = $to;
            }
        }
        
        return $this->_emoticons;
    }
    
    function pushNotification($aMessage) 
    {
        $fromUserId = intval($aMessage['`from`']);
        $toUserId = intval($aMessage['`to`']);
        $msg = $aMessage['`message`'];
        
        $fromUser = Phpfox::getService('user')->get($fromUserId);
        if (!$fromUser) 
        {
            return;
        }
        
        foreach ($this->getAllEmoticonsReplacement() as $from => $to) 
        {
            $msg = str_replace($from, $to, $msg);
        }
        
        $content = strip_tags(sprintf('%s: %s', $fromUser['full_name'], $msg));
        
        $params = array(
            'ios' => array(
                'aps' => array(
                    'alert' => $content,
                    'badge' => 0,
                    'sound' => 'default'
                ) ,
                'iId' => $fromUserId,
                'sType' => 'chat'
            ) ,
            'android' => array(
                'message' => $content,
                'iId' => $fromUserId,
                'sType' => 'chat'
            )
        );
        
        Phpfox::getService('mfox.cloudmessage')->send($params, $toUserId);
    }
}
