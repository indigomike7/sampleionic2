<?php
/**
 * @since 3.09
 * @version 4.09
 * @author  Nam Nguyen
 * @link    https://jira.younetco.com/browse/PCUS-1116
 * @date    Jun 22, 2015
 */


/**
 * Class Mfox_Service_Applink
 *
 */
class Mfox_Service_Applink extends Phpfox_Service
{
    /**
     * @return Phpfox_Request
     */
    public function req()
    {
        return Phpfox::getLib('request');
    }


    /**
     *
     */
    public function buildMetaForController()
    {
        $fullControllerName = Phpfox::getLib('module')->getFullControllerName();

        $sMobileUrl = '';

        switch ($fullControllerName) {
            case 'blog.view':
                $sMobileUrl = 'blog/' . $this->req()->getInt('req2');
                break;

            case 'fevent.view':
            case 'event.view':
                $sMobileUrl = 'event/' . $this->req()->getInt('req2');
                break;

            case 'marketplace.view':
                $sMobileUrl = 'event/' . $this->req()->getInt('req2');
                break;

            case 'advancedmarketplace.detail':
                $sMobileUrl = 'advancedmarketplace/' . $this->req()->getInt('req3');
                break;
            case 'profile.index':
                $profile = $this->req()->get('req1');
                $user = Phpfox::getService('user')
                    ->get($profile);

                $userId = $user['user_id'];
                // if user

                $sMobileUrl = 'user/' . $userId;
                break;
            case 'pages.view':
                $profile = $this->req()->get('req1');

                if ($profile == 'pages') {
                    $pageId = $this->req()->getInt('req2');

                } else {
                    $page = Phpfox::getService('user')
                        ->get($profile);
                    $pageId = $page['profile_page_id'];
                }
                $sMobileUrl = 'page/' . $pageId;
                break;
            case 'video.view':
                $sMobileUrl = 'video/' . $this->req()->getInt('req2');
                break;
            case 'videochannel.view':
                $sMobileUrl = 'videochannel/' . $this->req()->getInt('req2');
                break;
            case 'poll.view':
                $sMobileUrl = 'poll/' . $this->req()->getInt('req2');
                break;
            case 'quiz.view':
                $sMobileUrl = 'quiz/' . $this->req()->getInt('req2');
                break;
            case 'music.view':
                $sMobileUrl = 'music_song/' . $this->req()->getInt('req2');
                break;
            case 'music.view-album':
                $sMobileUrl = 'music_album/' . $this->req()->getInt('req3');
                break;
            case 'forum.thread':
                $sMobileUrl = 'forum_thread/' . $this->req()->getInt('req3');
                break;
            case 'forum.forum':
                $sMobileUrl = 'forum/' . $this->req()->getInt('req2');
                break;
            case 'musicsharing.listen':
                $playlistId = $this->req()->get('playlist');
                $musicId = $this->req()->get('music');
                if ($musicId) {
                    $sMobileUrl = 'musicsharing_album/' . $playlistId;
                } else if ($musicId) {
                    $sMobileUrl = 'musicsharing_song/' . $musicId;
                }
                break;
            case 'photo.view':
            case 'advancedphoto.view':
                $albumId  = $this->req()->get('albumid');
                if(empty($albumId)){
                    $sMobileUrl = 'photos/all_photos/-1/album_photo/' . $this->req()->getInt('req2');
                }else if($albumId){
                    $sMobileUrl = 'photos/all_photos/'.$albumId.'/album_photo/' . $this->req()->getInt('req2');
                }
                break;
            case 'photo.album':
            case 'advancedphoto.album':
                $sMobileUrl = 'album/' . $this->req()->getInt('req3');
                break;

        }

        if (!$sMobileUrl) {
            // debug only
//             header('mobileUrl: '. $fullControllerName);
        }

        if ($sMobileUrl) {
            $this->buildMetaForUrl($sMobileUrl);
        }
    }

    /**
     * @param $sMobileUrl
     */
    public function buildMetaForUrl($sMobileUrl)
    {
        $tpl = Phpfox::getLib('template');

        $iosPrefix = 'ynaltest://';
        $iosAppId = '12345';
        $iosAppName = 'App Links';

        $androidPrefix = 'ynaltest://';
        $androidPackageName = 'com.younetco.mobifox';
        $androidAppName = 'App Links';

        $webUrl =Phpfox::getParam('core.path');


        /**
         * WARNING: we have 2 variations on store ?
         */
        $tpl->setMeta(array(
            'al:ios:url'          => $iosPrefix . $sMobileUrl,
            'al:ios:app_store_id' => $iosAppId,
            'al:ios:app_name'     => $iosAppName,
            'al:android:url'      => $androidPrefix . $sMobileUrl,
            'al:android:app_name' => $androidAppName,
            'al:android:package'  => $androidPackageName,
            'al:web:url'          => $webUrl,
        ));
    }

}