<?php

class Mfox_Service_Chat extends Phpfox_Service {

    public function getstatus($aData)
    {
    	$iUserId = Phpfox::getUserId();    	

		if (Phpfox::getParam('core.store_only_users_in_session'))
		{
			$result = array();
			$result['im_hide'] = 0;
			$result['im_status'] = 0;
		}
		else
		{
	    	$result = $this->database()->select('ls.im_status, ls.im_hide')
	    		->from(Phpfox::getT('log_session'), 'ls')
	    		->where(' ls.user_id = ' . (int)$iUserId)
	    		->order('ls.last_activity ASC')
	    		->limit(1)
	    		->execute('getSlaveRow');
		}

		if ((int)$result['im_status'] == 0 && (int)$result['im_hide'] == 0)
		{
			return array(
				'sStatus' => 'online',
			);
		}
		else 
		{
			return array(
				'sStatus' => 'offline',					
			);
		}
    }

    public function getFriendList($aData) 
    {
        $friends = (new Api\Friend())->get([
            'limit' => 1000
        ]);
        $aResults = [];
        foreach($friends as $friend) {
            $aDetail = Phpfox::getService('mfox.helper.user')->getUserData($friend->id);
            $aResults[] = array(
                'iItemId' => (int) $friend->id,
                'sItemType' => 'user',
                'sImage' => Phpfox::getService('mfox.user')->getImageUrl($aDetail, '_50_square'),
                'bHasNewMessage' => '',
                'sStatus' => ($aDetail['bIsOnline'] ? 'online' : 'offline'),
                'sFullName' => $friend->name
            );
        }
        
        return $aResults;
    }

    public function getEmojis($aData) {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $html = '';
        $emojis = [];
        $emoticons = @file_get_contents('https://raw.githubusercontent.com/arvida/emoji-cheat-sheet.com/master/public/index.html');
        if ($emoticons !== false) {
            $doc->loadHTML($emoticons);
            $xml = $doc->saveXML($doc);

            $xml = @simplexml_load_string($xml);
            $skip = ['feelsgood', 'finnadie', 'goberserk', 'godmode', 'hurtrealbad', 'rage1', 'rage2', 'rage3', 'rage4', 'suspect', 'trollface', 'bowtie', 'disappointed_relieved', 'neckbeard', 'collision', 'hankey', 'shit', '+1', '-1', 'facepunch', 'metal', 'fu', 'running', 'raising_hand'];
            if ($xml instanceof SimpleXMLElement && isset($xml->body) && isset($xml->body->ul)) {
                foreach ($xml->body->ul as $ul) {
                    if ($ul instanceof SimpleXMLElement) {
                        if (!isset($ul->attributes()->class)) {
                            continue;
                        }

                        $class = (string) $ul->attributes()->class;
                        if ($class == 'people emojis') {
                            foreach ($ul->li as $li) {
                                $key = (string) $li->div->span;
                                if (in_array($key, $skip)) {
                                    continue;
                                }
                                $emojis[] = array(
                                    'class' => 'twa twa-' . str_replace('_', '-', $key),
                                    'key' => ':' . $key . ':');

                            }
                        }
                    }
                }
            }
        }

        return [
            'h1_clean' => 'Emoji Cheat Sheet',
            'content' => $emojis
        ];
    }

    public function attachLink($aData) {
        if (isset($aData['sUrl']) && $aData['sUrl']) {
            $link = Phpfox::getService('link')->getLink($aData['sUrl']);
            if (!isset($link['link'])) {
                return array('result' => 0, 'error_code' => 1,'error_message' => _p('Unable to attach this link.'));
            }

            if (empty($link['title'])) {
                return array('result' => 0, 'error_code' => 1,'error_message' => _p('Unable to attach a valid link with this URL.'));
            }

            return $link;
        }

        return array('result' => 0, 'error_code' => 1,'error_message' => _p('Attach link can not be empty'));
    }
    public function getConfig($aData) {

        $aUser = Phpfox::getService('user')->getUser(Phpfox::getUserId());
        if(!$aUser) {
            return array('result' => 0, 'error_code' => 1,'error_message' => _p('Please login to continue'));
        }
        $aUserInfo = array(
            'title' => $aUser['full_name'],
            'path' => 'core.url_user',
            'file' => $aUser['user_image'],
            'suffix' => '_50_square',
            'max_width' => 50,
            'no_default' => true,
            'thickbox' => true,
        );

        
        
        $sImage = Phpfox::getLib('image.helper')->display(array_merge(array('user' => User_Service_User::instance()->getUserFields(true, $aUser)), $aUserInfo));


        $sChatServer = setting('pf_im_node_server');
        if($sChatServer) {
            return array('result' => 0, 'error_code' => 0, 'sChatServer' => $sChatServer, 'sSitePhotoLink' => $sImage);
        }
        return array('result' => 0, 'error_code' => 1, 'error_message' => _p('Can not get Node JS Server address'));
    }

}
