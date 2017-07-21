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
 * @since May 27, 2013
 * @link Mfox Api v2.0
 */

class Mfox_Service_Link extends Phpfox_Service {
    /**
     * Input data:
     * + sLink: string, required.
     *
     * Output data:
     * + sLink: string.
     * + sTitle: string.
     * + sDescription: string.
     * + sDefaultImage: string.
     * + sMedium: string
     * + iImageCount: int
     * + aImages: array()
     * + sEmbedCode: string.
     *
     */
    public function preview($aData)
    {
        $sLink = isset($aData['sLink']) ? $aData['sLink'] : '';
        if (empty($sLink))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid")),
                'error_code' => 1
            );
        }

        $uri = trim(strip_tags($sLink));        
        if (filter_var($uri, FILTER_VALIDATE_URL) === FALSE) 
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.link_is_not_valid")),
                'error_code' => 1
            );
        }

        $aLink = Phpfox::getService('link')->getLink($uri);
        if ($aLink)
        {
            return array(
                'sLink' => $aLink['link'],
                'sTitle' => $aLink['title'],
                'sDescription' => $aLink['description'],
                'sDefaultImage' => $aLink['default_image'],
                'aImages' => $aLink['images'],
                'sMedium' => '',
                'iImageCount' => count($aLink['images']),
                'sEmbedCode' => $aLink['embed_code']
            );
        }
        else
        {
            return array();
        }
    }

    public function add($aVals, $bIsCustom = false, $aCallback = null){
        if (empty($aVals['privacy_comment']))
        {
            $aVals['privacy_comment'] = 0;
        }   

        if (empty($aVals['privacy']))
        {
            $aVals['privacy'] = 0;
        }   
        $iId = $this->database()->insert(Phpfox::getT('link'), array(
                'user_id' => Phpfox::getUserId(),
                'is_custom' => ($bIsCustom ? '1' : '0'),
                'module_id' => ($aCallback === null ? null : $aCallback['module']),
                'item_id' => ($aCallback === null ? 0 : $aCallback['item_id']),
                'parent_user_id' => (isset($aVals['parent_user_id']) ? (int) $aVals['parent_user_id'] : 0),
                'link' => $this->preParse()->clean($aVals['link']['url'], 255),
                'image' => ((isset($aVals['link']['image_hide']) && $aVals['link']['image_hide'] == '1') || !isset($aVals['link']['image'])? null : $this->preParse()->clean($aVals['link']['image'], 255)),
                'title' => (isset($aVals['link']['title']) ?  $this->preParse()->clean($aVals['link']['title'], 255) : ''),
                'description' => isset($aVals['link']['description']) ? $this->preParse()->clean($aVals['link']['description'], 200) : '',
                'status_info' => (empty($aVals['status_info']) ? null : $this->preParse()->prepare($aVals['status_info'])),
                'privacy' => (int) $aVals['privacy'],
                'privacy_comment' => (int) $aVals['privacy_comment'],
                'time_stamp' => PHPFOX_TIME,
                'has_embed' => (empty($aVals['link']['embed_code']) ? '0' : '1')
            )
        );
        if (!empty($aVals['link']['embed_code']))
        {
            $this->database()->insert(Phpfox::getT('link_embed'), array(
                    'link_id' => $iId,
                    'embed_code' => $this->preParse()->prepare($aVals['link']['embed_code'])
                )
            );
        }
        if ($aCallback === null && isset($aVals['parent_user_id']) && $aVals['parent_user_id'] != Phpfox::getUserId())
        {
            $aUser = $this->database()->select('user_name')
                ->from(Phpfox::getT('user'))
                ->where('user_id = ' . (int) $aVals['parent_user_id'])
                ->execute('getRow');            
            
            $sLink = Phpfox::getLib('url')->makeUrl($aUser['user_name'], array('plink-id' => $iId));
            
            Phpfox::getLib('mail')->to($aVals['parent_user_id'])
                ->subject(array('link.full_name_posted_a_link_on_your_wall', array('full_name' => Phpfox::getUserBy('full_name'))))
                ->message(array('link.full_name_posted_a_link_on_your_wall_message', array('full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink)))
                ->notification('comment.add_new_comment')
                ->send();
            
            if (Phpfox::isModule('notification'))
            {
                Phpfox::getService('notification.process')->add('feed_comment_link', $iId, $aVals['parent_user_id']);       
            }           
        }

        // support hashtags
        // since 3.08p2
        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support') && !empty($aVals['status_info']) )
        {
            Phpfox::getService('tag.process')->add('link', $iId, Phpfox::getUserId(), $aVals['status_info'], true);
        }

        //  add feed
        if(Phpfox::isModule('feed')){
            $feedID = Phpfox::getService('feed.process')->callback($aCallback)->add('link', $iId, $aVals['privacy'], $aVals['privacy_comment'], (isset($aVals['parent_user_id']) ? (int) $aVals['parent_user_id'] : 0));
        }
        return array('linkID' => $iId, 'feedID' => $feedID);        
    }

    public function __getLink($sUrl){
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/32.0.1700.77 Safari/537.36';
        if (substr($sUrl, 0, 7) != 'http://' && substr($sUrl, 0, 8) != 'https://')
        {
            $sUrl = 'http://' . $sUrl;
        }
            
        $aParts = parse_url($sUrl); 
                
        if (!isset($aParts['host']))
        {
            return Phpfox_Error::set( Phpfox::getPhrase('link.not_a_valid_link'));
        }
                
        $aReturn = array();     
        $oVideo = json_decode($this->__send('http://api.embed.ly/1/oembed?format=json&maxwidth=400&url=' . urlencode($sUrl), array(), 'GET', $userAgent));

        if (isset($oVideo->provider_url) && (isset($oVideo->photo)))
        {
            $aReturn = array(
                'link' => $sUrl,
                'title' => (isset($oVideo->title) ? strip_tags($oVideo->title) : ''),
                'description' => (isset($oVideo->description) ? strip_tags($oVideo->description) : ''),
                'default_image' => ($oVideo->type == 'photo' ? $oVideo->url : (isset($oVideo->thumbnail_url) ? $oVideo->thumbnail_url : '')),
                'embed_code' => ($oVideo->type == 'video' ? $oVideo->html : '')
            );
                    
            return $aReturn;
        }   
        
        $aParseBuild = array();
        $sContent = $this->__send($sUrl, array(), 'GET', $userAgent);                
        preg_match_all('/<(meta|link)(.*?)>/i', $sContent, $aRegMatches);       
        if (isset($aRegMatches[2]))
        {
            foreach ($aRegMatches as $iKey => $aMatch)
            {
                if ($iKey !== 2)
                {
                    continue;
                }               
                
                foreach ($aMatch as $sLine)
                {
                    $sLine = rtrim($sLine, '/');
                    $sLine = trim($sLine);
                    
                    preg_match('/(property|name|rel)=("|\')(.*?)("|\')/ise', $sLine, $aType);
                    if (count($aType) && isset($aType[3]))
                    {
                        $sType = $aType[3];
                        preg_match('/(content|type)=("|\')(.*?)("|\')/i', $sLine, $aValue);
                        if (count($aValue) && isset($aValue[3]))
                        {                       
                            if ($sType == 'alternate')
                            {
                                $sType = $aValue[3];
                                preg_match('/href=("|\')(.*?)("|\')/i', $sLine, $aHref);
                                if (isset($aHref[2]))
                                {
                                    $aValue[3] = $aHref[2];
                                }
                            }
                            $aParseBuild[$sType] = $aValue[3];
                        }
                    }
                }
            }
            
            if (isset($aParseBuild['og:title']))
            {
                $aReturn['link'] = $sUrl;
                $aReturn['title'] = $aParseBuild['og:title'];
                $aReturn['description'] = (isset($aParseBuild['og:description']) ? $aParseBuild['og:description'] : '');
                $aReturn['default_image'] = (isset($aParseBuild['og:image']) ? $aParseBuild['og:image'] : '');
                if (isset($aParseBuild['application/json+oembed']))
                {
                    // $oJson = json_decode(Phpfox::getLib('request')->send($aParseBuild['application/json+oembed'], array(), 'GET', $_SERVER['HTTP_USER_AGENT']));                    if (isset($oJson->html))
                    $oJson = json_decode($this->__send($aParseBuild['application/json+oembed'], array(), 'GET', $userAgent));
                    if (isset($oJson->html))
                    {
                        $aReturn['embed_code'] = $oJson->html;  
                    }
                }

                return $aReturn;
            }
        }               
        
        $sContent = $this->__send($sUrl, array(), 'GET', $userAgent, null, true);                
        if( function_exists('mb_convert_encoding') )
        {
            $sContent = mb_convert_encoding($sContent, 'HTML-ENTITIES', "UTF-8");
        }       
                
        $aReturn['link'] = $sUrl;
        
        Phpfox_Error::skip(true);
        $oDoc = new DOMDocument();
        $oDoc->loadHTML($sContent);
        Phpfox_Error::skip(false);
        
        if (($oTitle = $oDoc->getElementsByTagName('title')->item(0)) && !empty($oTitle->nodeValue))
        {
            $aReturn['title'] = strip_tags($oTitle->nodeValue);
        }
        
        if (empty($aReturn['title']))
        {
            if (preg_match('/^(.*?)\.(jpg|png|jpeg|gif)$/i', $sUrl, $aImageMatches))
            {
                return array(
                    'link' => $sUrl,
                    'title' => '',
                    'description' => '',
                    'default_image' => $sUrl,
                    'embed_code' => ''
                );
            }

            return Phpfox_Error::set( Phpfox::getPhrase('link.not_a_valid_link_unable_to_find_a_title'));
        }
        
        $oXpath = new DOMXPath($oDoc);  
        $oMeta = $oXpath->query("//meta[@name='description']")->item(0);
        if (method_exists($oMeta, 'getAttribute'))
        {
            $sMeta = $oMeta->getAttribute('content');
            if (!empty($sMeta))
            {
                $aReturn['description'] = strip_tags($sMeta);
            }
        }
        
        $aImages = array();     
        $oMeta = $oXpath->query("//meta[@property='og:image']")->item(0);
        if (method_exists($oMeta, 'getAttribute'))
        {           
            $aReturn['default_image'] = strip_tags($oMeta->getAttribute('content'));
            $aImages[] = strip_tags($oMeta->getAttribute('content'));
        }       
        
        $oMeta = $oXpath->query("//link[@rel='image_src']")->item(0);
        if (method_exists($oMeta, 'getAttribute'))
        {           
            if (empty($aReturn['default_image']))
            {
                $aReturn['default_image'] = strip_tags($oMeta->getAttribute('href'));
            }
            $aImages[] = strip_tags($oMeta->getAttribute('href'));
        }           
        
        if (!isset($aReturn['default_image']))
        {
            $oMeta = $oXpath->query("//meta[@itemprop='image']")->item(0);
            if (method_exists($oMeta, 'getAttribute'))
            {
                $aReturn['default_image'] = strip_tags($oMeta->getAttribute('content'));
                if (strpos($aReturn['default_image'], $sUrl) === false)
                {
                    $aReturn['default_image'] = $sUrl . '/' . $aReturn['default_image'];
                }
            }           
        }
        
        // if (!isset($aReturn['default_image']))
        {                       
            $oImages = $oDoc->getElementsByTagName('img');
            $iIteration = 0;
            foreach ($oImages as $oImage)
            {
                $sImageSrc = $oImage->getAttribute('src');
                
                if (substr($sImageSrc, 0, 7) != 'http://' && substr($sImageSrc, 0, 1) != '/')
                {
                    continue;   
                }
                
                if (substr($sImageSrc, 0, 2) == '//')
                {
                    continue;
                }
                
                $iIteration++;      
                
                if (substr($sImageSrc, 0, 1) == '/')
                {                   
                    $sImageSrc = 'http://' . $aParts['host'] . $sImageSrc;
                }           
                
                if ($iIteration === 1 && empty($aReturn['default_image']))
                {
                    $aReturn['default_image'] = strip_tags($sImageSrc);
                }
                
                if ($iIteration > 20)
                {
                    break;
                }
                
                $aImages[] = strip_tags($sImageSrc);
            }
        }
        
        if (count($aImages))
        {
            $aReturn['images'] = $aImages;
        } else {
            $aReturn['images'] = array();
        }
        
        $oLink = $oXpath->query("//link[@type='text/xml+oembed']")->item(0);
        if (method_exists($oLink, 'getAttribute'))
        {   
            $aXml = Phpfox::getLib('xml.parser')->parse($this->__send($oLink->getAttribute('href'), array(), 'GET', $userAgent));            
            if (isset($aXml['html']))
            {
                $aReturn['embed_code'] = $aXml['html']; 
            }
        }               
        
        return $aReturn;
    }

    /**
     * Send a request to another server. Usually using CURL.
     *
     * @param string $sUrl URL of the server.
     * @param array $aPost $_POST data to send.
     * @param string $sMethod Method of request (GET or POST).
     * @param string $sUserAgent Useragent to send.
     * @param string $aCookies ARRAY of any cookies to pass.
     * @return mixed FALSE if failed to connect, STRING if anything was returned from the server.
     */
    private function __send($sUrl, $aPost = array(), $sMethod = 'POST', $sUserAgent = null, $aCookies = null, $bFollow = false)
    {
        $aHost = parse_url($sUrl);
        $sPost = '';
        foreach ($aPost as $sKey => $sValue)
        {
            $sPost .= '&' . $sKey . '=' . $sValue;
        }

        // Curl
        if (extension_loaded('curl') && function_exists('curl_init'))
        {
            $hCurl = curl_init();       
            
            curl_setopt($hCurl, CURLOPT_URL, (($sMethod == 'GET' && !empty($sPost)) ? $sUrl . '?' . ltrim($sPost, '&') : $sUrl));
            curl_setopt($hCurl, CURLOPT_HEADER, false);
            curl_setopt($hCurl, CURLOPT_FOLLOWLOCATION, $bFollow);
            curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, true);
            
            // Testing this out at the moment...
            curl_setopt($hCurl, CURLOPT_SSL_VERIFYPEER, false);
            
            // Run if this is a POST request method
            if ($sMethod != 'GET')
            {
                curl_setopt($hCurl, CURLOPT_POST, true);
                curl_setopt($hCurl, CURLOPT_POSTFIELDS, $sPost);    
            }
            
            // Add the browser agent
            curl_setopt($hCurl, CURLOPT_USERAGENT, ($sUserAgent === null ? "" . PHPFOX::BROWSER_AGENT . " (" . PhpFox::getVersion() . ")" : $sUserAgent));
            
            // Check if we need to set some cookies
            if ($aCookies !== null)
            {               
                $sLine = "\n";              
                // Loop thru all the cookies we currently have set
                foreach ($aCookies as $sKey => $sValue)
                {
                    // Make sure we do not see the session ID or the browser will crash
                    if ($sKey == 'PHPSESSID')
                    {
                        continue;
                    }
                        
                    // Add the cookies
                    $sLine .= '' . $sKey . '=' . $sValue . '; ';        
                }
                // Trim the cookie
                $sLine = trim(rtrim($sLine, ';'));
                    
                // Set the cookie
                curl_setopt($hCurl, CURLOPT_COOKIE, $sLine);
            }
            
            // Run the exec
            $sData = curl_exec($hCurl);
            
            // Close the curl connection
            curl_close($hCurl); 

            // Return whatever we can from the curl request
            return trim($sData);    
        }       
        
        // file_get_contents()
        if ($sMethod == 'GET' && ini_get('allow_url_fopen') && function_exists('file_get_contents'))
        {
            $sData = file_get_contents($sUrl . "?" . ltrim($sPost, '&'));
            
            return trim($sData);            
        }
        
        // fsockopen
        if (!isset($sData))
        {
            $hConnection = fsockopen($aHost['host'], 80, $errno, $errstr, 30);
            if (!$hConnection)
            {
                return false;
            }
            else
            {
                if ($sMethod == 'GET')
                {
                    $sUrl = $sUrl . '?' . ltrim($sPost, '&');
                }
                
                $sSend = "{$sMethod} {$sUrl}  HTTP/1.1\r\n";
                $sSend .= "Host: {$aHost['host']}\r\n";
                $sSend .= "User-Agent: " . PHPFOX::BROWSER_AGENT . " (" . PhpFox::getVersion() . ")\r\n";
                $sSend .= "Content-Type: application/x-www-form-urlencoded\r\n";
                $sSend .= "Content-Length: " . strlen($sPost) . "\r\n";
                $sSend .= "Connection: close\r\n\r\n";
                $sSend .= $sPost;
                fwrite($hConnection, $sSend);            
                $sData = '';
                while (!feof($hConnection))
                {
                    $sData .= fgets($hConnection, 128);
                }
                
                $aResponse = preg_split("/\r\n\r\n/", $sData);
                $sHeader = $aResponse[0];
                $sData = $aResponse[1];
                
                if(!(strpos($sHeader,"Transfer-Encoding: chunked")===false))
                {
                    $aAux = split("\r\n", $sData);
                    for($i=0; $i<count($aAux); $i++)
                    {
                        if($i==0 || ($i%2==0))
                        {
                            $aAux[$i] = '';
                        }
                        $sData = implode("",$aAux);
                    }
                }               
                
                return chop($sData);
            }
        }
        
        return false;
    }       
    
}
