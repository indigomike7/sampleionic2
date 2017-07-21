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
 * @link Mfox Api v1.0
 */
class Mfox_Service_Feed extends Phpfox_Service {

    /**
     * Mfox_Service_Request_Request
     * @var object
     */
    private $_oReq = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_oReq = Phpfox::getService('mfox.request');

        $this->_sTable = Phpfox::getT('feed');
    }

    /**
     * Input data:
     * + module: string.
     * + item_id: int.
     * + table_prefix: string.
     * + feed_comment: string. Type of the feed.
     * 
     * Callback for feed.
     * @var array Callback data
     */
    public $_aCallback;
    
    public $_aViewMoreFeeds;

    /**
     * Set table.
     * @param string $sTable
     */
    public function setTable($sTable)
    {
        $this->_sTable = $sTable;
    }

    /**
     * Input data:
     * + iItemId: int, optional. It is "iFeedId".
     * 
     * Output data:
	 * + iUserId: int.
	 * + sUsername: string.
	 * + iFeedId: int.
	 * + UserProfileImg_Url: string.
	 * + sFullName: string.
	 * + sContent: string.
	 * + timestamp: int.
	 * + Time: string.
	 * + TimeConverted: string.
	 * + sTypeId: string.
	 * + iItemId: int.
	 * + iLikeId: int.
	 * + iTotalLike: int.
     * 
     * @param array $aData
     * @param int $iFeedId Feed id. Use to replace 'iItemId'. Optional.
     * @return array
     */
    function getByIdAction($aData, $iId)
    {
        return $this->view($aData, $iId);
    }

    public function get($aData)
    {
        return $this->__get($aData);
    }

    public function refresh($aData)
    {
        $response = array();

        foreach($aData['aFeedId'] as $id){
            $response[$id] =  'deleted';
        }

        $aFeeds =  $this->__get($aData);

        foreach($aFeeds as $feed){
            if (!$this->__isSupportItemType($feed['sItemType']))
            {
                continue;
            }

            $id = $feed['iActionId'];
            if(!empty($response[$id])){
                $response[$id] =  $feed;
            }
        }

        return $response;
    }

    private function __isSupportItemType($sItemType)
    {
        $bSupport = true;
        switch ($sItemType) {
            case 'event':
                $bSupport = !Phpfox::getService('mfox.event')->isAdvancedModule();
                break;

            case 'fevent':
                $bSupport = Phpfox::getService('mfox.event')->isAdvancedModule();
                break;

            case 'marketplace':
                $bSupport = !Phpfox::getService('mfox.marketplace')->isAdvancedModule();
                break;

            case 'advancedmarketplace':
                $bSupport = Phpfox::getService('mfox.marketplace')->isAdvancedModule();
                break;

            case 'photo':
                $bSupport = !Phpfox::getService('mfox.photo')->isAdvancedModule();
                break;

            case 'advancedphoto':
                $bSupport = Phpfox::getService('mfox.photo')->isAdvancedModule();
                break;

            case 'music_album':
            case 'music_song':
                $bSupport = !Phpfox::getService('mfox.song')->isAdvancedModule();
                break;

            case 'musicsharing_album':
            case 'musicsharing_pagesalbum':
            case 'musicsharing_pagesplaylist':
            case 'musicsharing_playlist':
            case 'musicsharing_song':
                $bSupport = Phpfox::getService('mfox.song')->isAdvancedModule();
                break;

            default:
                # code...
                break;
        }

        return $bSupport;
    }

    public function emoticons($aData)
    {
        $aEmoticons = array();

        $aPackages = Phpfox::getService('emoticon')->getPackages();
        foreach ($aPackages as $aPackage)
        {
            if ($aPackage['is_active'])
            {
                $aRows = $this->__getEmoticons($aPackage['package_path']);
                $aEmoticons = array_merge($aEmoticons, $aRows);
            }
        }

        return $aEmoticons;
    }
    
    private function __getEmoticons($iPackage)
    {
        $aEmoticons = array();

        $aRows = Phpfox::getService('emoticon')->getEmoticons($iPackage);

        foreach ($aRows as $aRow)
        {
            $sImage = Phpfox::getParam('core.url_emoticon') . $aRow['package_path'] . '/' . $aRow['image'];

            $aEmoticons[] = array(
                'text' => $aRow['text'],
                'title' => $aRow['title'],
                'image' => $sImage,
            );
        }

        return $aEmoticons;
    }
    
    /**
     * Input data:
     * + module: string.
     * + item_id: int.
     * + table_prefix: string.
     * + feed_comment: string. Type of the feed.
     * 
     * @param array $aData
     * @return Mfox_Service_Feed
     */
    public function setCallback($aData)
    {
        $this->_aCallback = $aData;

        return $this;
    }

    /**
     * Clear callback.
     * 
     * @return \Mfox_Service_Feed
     */
    public function clearCallback()
    {
        $this->_aCallback = null;

        return $this;
    }

   
    /**
     * Input data:
     * + type_id: string, required.
     * + item_id: int, required.
     * 
     * Output data:
     * + like_id: int.
     * 
     * @param array $aItem
     * @return array
     */
    public function checkIsLiked($aItem)
    {
        /**
         * @var array
         */
        $aLike = $this->database()
                ->select('l.like_id')
                ->from(Phpfox::getT('like'), 'l')
                ->where('l.type_id = \'' . $this->database()->escape($aItem['type_id']) . '\' AND l.item_id = ' . (int) $aItem['item_id'] . ' AND l.user_id = ' . Phpfox::getUserId())
                ->execute('getRow');

        return isset($aLike['like_id']) ? $aLike['like_id'] : null;
    }

    /**
     * Input data:
     * + item_id: int, required.
     * 
     * Output data:
     * + content: string.
     * 
     * @param array $aItem
     * @return string
     */
    public function getContentOfFeedComment($aItem)
    {
        /**
         * @var array
         */
        $aRow = $this->database()->select('fc.*, l.like_id AS is_liked')
                ->from(Phpfox::getT('feed_comment'), 'fc')
                ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'feed_comment\' AND l.item_id = fc.feed_comment_id AND l.user_id = ' . Phpfox::getUserId())
                ->where('fc.feed_comment_id = ' . (int) $aItem['item_id'])
                ->execute('getSlaveRow');

        if (isset($aRow['content']))
        {
            return $aRow['content'];
        }

        return '';
    }

    /**
     * Input data:
     * + item_id: int, required.
     * 
     * Output data:
     * + content: string.
     * 
     * @param array $aItem
     * @return string
     */
    public function getContentOfUserStatus($aItem)
    {
        /**
         * @var array
         */
        $aRow = $this->database()->select('us.*, l.like_id AS is_liked')
                ->from(Phpfox::getT('user_status'), 'us')
                ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'user_status\' AND l.item_id = us.status_id AND l.user_id = ' . Phpfox::getUserId())
                ->where('us.status_id = ' . (int) $aItem['item_id'])
                ->execute('getSlaveRow');

        if (isset($aRow['content']))
        {
            return $aRow['content'];
        }

        return '';
    }
    
    /**
     * @param int $iStatusId
     * @return array
     */
    public function getUserStatus($iStatusId)
    {
        $aRow = $this->database()->select('us.*, l.like_id AS is_liked')
                ->from(Phpfox::getT('user_status'), 'us')
                ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = \'user_status\' AND l.item_id = us.status_id AND l.user_id = ' . Phpfox::getUserId())
                ->where('us.status_id = ' . (int) $iStatusId)
                ->execute('getSlaveRow');

        return $aRow;
    }

    /**
     * Limit the string.
     * @param string $str
     * @param int $limit
     * @param string $end_char
     * @return array (bool, string)
     */
    public function word_limiter($str, $limit = 100, $end_char = '&#8230;')
    {
        if (trim($str) == '')
        {
            return $str;
        }

        preg_match('/^\s*+(?:\S++\s*+){1,' . (int) $limit . '}/', $str, $matches);
        /**
         * @var bool
         */
        $bReadMore = true;
        if (strlen($str) == strlen($matches[0]))
        {
            $end_char = '';
            $bReadMore = false;
        }

        return array($bReadMore, rtrim($matches[0]) . $end_char);
    }

    /**
     * Input data:
     * + iItemId: int, optional. It is "iFeedId".
     * 
     * Output data:
	 * + iUserId: int.
	 * + sUsername: string.
	 * + iFeedId: int.
	 * + UserProfileImg_Url: string.
	 * + sFullName: string.
	 * + sContent: string.
	 * + timestamp: int.
	 * + Time: string.
	 * + TimeConverted: string.
	 * + sTypeId: string.
	 * + iItemId: int.
	 * + iLikeId: int.
	 * + iTotalLike: int.
     * 
     * @see Mobile - API phpFox/Api V1.0 - POST method.
     * @see view
     * 
     * @param array $aData
     * @return array
     */
    public function postAction($aData)
    {
        return $this->view($aData);
    }

    /**
     * Input data:
     * + comment-id: int, optional.
     * + status-id: int, optional.
     * + link-id: int, optional.
     * + plink-id: int, optional.
     * + poke-id: int, optional.
     * + year: int, optional.
     * + month: int, optional.
     * + ids: string, optional.
     * 
     * Output data:
	 * + feed_id: int.
	 * + app_id: int.
	 * + privacy: int.
	 * + privacy_comment: int.
	 * + type_id: int.
	 * + user_id: int.
	 * + parent_user_id: int.
	 * + item_id: int.
	 * + time_stamp: int.
	 * + feed_reference: int.
	 * + parent_feed_id: int.
	 * + parent_module_id: string.
	 * + time_update: string.
	 * + is_friend: bool.
	 * + app_title: string.
	 * + view_id: int.
	 * + profile_page_id: int.
	 * + user_server_id: int.
	 * + user_name: string.
	 * + full_name: string.
	 * + gender: int.
	 * + user_image: string.
	 * + is_invisible: bool.
	 * + user_group_id: int.
	 * + language_id: int.
	 * + feed_time_stamp: int.
	 * + can_post_comment: bool.
	 * + feed_title: string.
	 * + feed_title_sub: string.
	 * + feed_info: string.
	 * + feed_link: string.
	 * + feed_icon: string.
	 * + enable_like: bool.
	 * + feed_image: string.
	 * + bShowEnterCommentBlock: bool.
	 * + feed_month_year: string.
	 * + likes: array.
	 * + total_likes: int.
	 * + feed_like_phrase: string.
	 * + feed_is_liked: bool.
	 * + feed_total_like: int.
     * 
     * @see Mobile - API phpFox/Api V1.0.
     * @see feed/getfeed
     * 
     * @param array $aData
     * @param int $iUserId
     * @param int $iFeedId
     * @param int $iPage
     * @param bool $bForceReturn
     * @return array
     */
    public function getfeed($aData, $iUserId = null, $iFeedId = null, $iPage = 0, $bForceReturn = false)
    {
        if (isset($aData['comment-id']) && ($iCommentId = (int) $aData['comment-id']))
        {
            if (isset($this->_aCallback['feed_comment']))
            {
                $aCustomCondition = array('feed.type_id = \'' . $this->_aCallback['feed_comment'] . '\' AND feed.item_id = ' . (int) $iCommentId . ' AND feed.parent_user_id = ' . (int) $this->_aCallback['item_id']);
            }
            else
            {
                $aCustomCondition = array('feed.type_id IN(\'feed_comment\', \'feed_egift\') AND feed.item_id = ' . (int) $iCommentId . (!empty($iUserId) ? ' AND feed.parent_user_id = ' . $iUserId : ''));
            }

            $iFeedId = true;
        }
        elseif (isset($aData['status-id']) && ($iStatusId = (int) $aData['status-id']))
        {
            $aCustomCondition = array('feed.type_id = \'user_status\' AND feed.item_id = ' . (int) $iStatusId . (!empty($iUserId) ? ' AND feed.user_id = ' . $iUserId : ''));
            $iFeedId = true;
        }
        elseif (isset($aData['link-id']) && ($iLinkId = (int) $aData['link-id']))
        {
            $aCustomCondition = array('feed.type_id = \'link\' AND feed.item_id = ' . (int) $iLinkId . (!empty($iUserId) ? ' AND feed.user_id = ' . $iUserId : ''));
            $iFeedId = true;
        }
        elseif (isset($aData['plink-id']) && ($iLinkId = $aData['plink-id']))
        {
            $aCustomCondition = array('feed.type_id = \'link\' AND feed.item_id = ' . (int) $iLinkId . (!empty($iUserId) ? ' AND feed.parent_user_id  = ' . $iUserId : ''));
            $iFeedId = true;
        }
        elseif (isset($aData['poke-id']) && ($iPokeId = $aData['poke-id']))
        {
            $aCustomCondition = array('feed.type_id = \'poke\' AND feed.item_id = ' . (int) $iPokeId . (!empty($iUserId) ? ' AND feed.user_id = ' . $iUserId : ''));
            $iFeedId = true;
        }

        $iTotalFeeds = (int) Phpfox::getComponentSetting(($iUserId === null ? Phpfox::getUserId() : $iUserId), 'feed.feed_display_limit_' . ($iUserId !== null ? 'profile' : 'dashboard'), Phpfox::getParam('feed.feed_display_limit'));

        $iOffset = (int) ($iPage * $iTotalFeeds);

        $sOrder = 'feed.time_update DESC';
        if (Phpfox::getUserBy('feed_sort') || defined('PHPFOX_IS_USER_PROFILE'))
        {
            $sOrder = 'feed.time_stamp DESC';
        }

        $aCond = array();
        if (isset($this->_aCallback['module']))
        {
            $aNewCond = array();
            if (isset($aData['comment-id']) && ($iCommentId = $aData['comment-id']))
            {
                if (!isset($this->_aCallback['feed_comment']))
                {
                    $aCustomCondition = array('feed.type_id = \'' . $this->_aCallback['module'] . '_comment\' AND feed.item_id = ' . (int) $iCommentId . '');
                }
            }
            $aNewCond[] = 'AND feed.parent_user_id = ' . (int) $this->_aCallback['item_id'];
            if ($iUserId !== null && $iFeedId !== null)
            {
                $aNewCond[] = 'AND feed.feed_id = ' . (int) $iFeedId . ' AND feed.user_id = ' . (int) $iUserId;
            }

            $iTimelineYear = 0;
            if (isset($aData['year']) && ($iTimelineYear = (int) $aData['year']) && !empty($iTimelineYear))
            {
                $iMonth = 12;
                $iDay = 31;
                if (isset($aData['month']) && ($iTimelineMonth = (int) $aData['month']) && !empty($iTimelineMonth))
                {
                    $iMonth = $iTimelineMonth;
                    $iDay = Phpfox::getLib('date')->lastDayOfMonth($iMonth, $iTimelineYear);
                }
                $aNewCond[] = 'AND feed.time_stamp <= \'' . mktime(0, 0, 0, $iMonth, $iDay, $iTimelineYear) . '\'';
            }

            $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
                    ->from(Phpfox::getT($this->_aCallback['table_prefix'] . 'feed'), 'feed')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                    ->where((isset($aCustomCondition) ? $aCustomCondition : $aNewCond))
                    ->order($sOrder)
                    ->limit($iOffset, $iTotalFeeds)
                    ->execute('getSlaveRows');
        }
        elseif (isset($aData['ids']) && ($sIds = $aData['ids']))
        {
            $aParts = explode(',', $aData['ids']);
            $sNewIds = '';
            foreach ($aParts as $sPart)
            {
                $sNewIds .= (int) $sPart . ',';
            }
            $sNewIds = rtrim($sNewIds, ',');

            $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
                    ->from($this->_sTable, 'feed')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                    ->where('feed.feed_id IN(' . $sNewIds . ')')
                    ->order('feed.time_stamp DESC')
                    ->execute('getSlaveRows');
        }
        elseif ($iUserId === null && $iFeedId !== null)
        {
            $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
                    ->from($this->_sTable, 'feed')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                    ->where(isset($aCustomCondition) ? $aCustomCondition : ('feed.feed_id = ' . (int) $iFeedId))
                    ->order('feed.time_stamp DESC')
                    ->execute('getSlaveRows');
        }
        elseif ($iUserId !== null && $iFeedId !== null)
        {
            $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
                    ->from($this->_sTable, 'feed')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                    ->where(isset($aCustomCondition) ? $aCustomCondition : ('feed.feed_id = ' . (int) $iFeedId . ' AND feed.user_id = ' . (int) $iUserId . ''))
                    ->order('feed.time_stamp DESC')
                    ->limit(1)
                    ->execute('getSlaveRows');
        }
        elseif ($iUserId !== null)
        {
            if ($iUserId == Phpfox::getUserId())
            {
                $aCond[] = 'AND feed.privacy IN(0,1,2,3,4)';
            }
            else
            {
                if (Phpfox::getService('user')->getUserObject($iUserId)->is_friend)
                {
                    $aCond[] = 'AND feed.privacy IN(0,1,2)';
                }
                else if (Phpfox::getService('user')->getUserObject($iUserId)->is_friend_of_friend)
                {
                    $aCond[] = 'AND feed.privacy IN(0,2)';
                }
                else
                {
                    $aCond[] = 'AND feed.privacy IN(0)';
                }
            }

            $iTimelineYear = 0;

            if (isset($aData['year']) && ($iTimelineYear = (int) $aData['year']) && !empty($iTimelineYear))
            {
                $iMonth = 12;
                $iDay = 31;
                if (isset($aData['month']) && ($iTimelineMonth = $aData['month']) && !empty($iTimelineMonth))
                {
                    $iMonth = $iTimelineMonth;
                    $iDay = Phpfox::getLib('date')->lastDayOfMonth($iMonth, $iTimelineYear);
                }
                $aCond[] = 'AND feed.time_stamp <= \'' . mktime(0, 0, 0, $iMonth, $iDay, $iTimelineYear) . '\'';
            }

            $this->database()->select('feed.*')
                    ->from($this->_sTable, 'feed')
                    ->where(array_merge($aCond, array('AND type_id = \'feed_comment\' AND feed.user_id = ' . (int) $iUserId . '')))
                    ->union();

            $this->database()->select('feed.*')
                    ->from($this->_sTable, 'feed')
                    ->where(array_merge($aCond, array('AND feed.user_id = ' . (int) $iUserId . ' AND feed.feed_reference = 0 AND feed.parent_user_id = 0')))
                    ->union();

            if (Phpfox::isUser())
            {
                if (Phpfox::isModule('privacy'))
                {
                    $this->database()->join(Phpfox::getT('privacy'), 'p', 'p.module_id = feed.type_id AND p.item_id = feed.item_id')
                            ->join(Phpfox::getT('friend_list_data'), 'fld', 'fld.list_id = p.friend_list_id AND fld.friend_user_id = ' . Phpfox::getUserId() . '');
                }
                $this->database()->select('feed.*')
                        ->from($this->_sTable, 'feed')
                        ->where('feed.privacy IN(4) AND feed.user_id = ' . (int) $iUserId . ' AND feed.feed_reference = 0')
                        ->union();
            }

            $this->database()->select('feed.*')
                    ->from($this->_sTable, 'feed')
                    ->where(array_merge($aCond, array('AND feed.parent_user_id = ' . (int) $iUserId)))
                    ->union();

            $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField())
                    ->unionFrom('feed')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                    ->order('feed.time_stamp DESC')
                    ->group('feed.feed_id')
                    ->limit($iOffset, $iTotalFeeds)
                    ->execute('getSlaveRows');
        }
        else
        {
            // Users must be active within 7 days or we skip their activity feed
            $iLastActiveTimeStamp = ((int) Phpfox::getParam('feed.feed_limit_days') <= 0 ? 0 : (PHPFOX_TIME - (86400 * Phpfox::getParam('feed.feed_limit_days'))));

            if (Phpfox::isModule('privacy') && Phpfox::getUserParam('privacy.can_view_all_items'))
            {

                $aRows = $this->database()->select('feed.*, f.friend_id AS is_friend, ' . Phpfox::getUserField())
                        ->from(Phpfox::getT('feed'), 'feed')
                        ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                        ->leftJoin(Phpfox::getT('friend'), 'f', 'f.user_id = feed.user_id AND f.friend_user_id = ' . Phpfox::getUserId())
                        ->order($sOrder)
                        ->group('feed.feed_id')
                        ->limit($iOffset, $iTotalFeeds)
                        ->where('feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0')
                        ->execute('getSlaveRows');
            }
            else
            {
                if (Phpfox::getParam('feed.feed_only_friends'))
                {
                    // Get my friends feeds
                    $this->database()->select('feed.*')
                            ->from($this->_sTable, 'feed')
                            ->join(Phpfox::getT('friend'), 'f', 'f.user_id = feed.user_id AND f.friend_user_id = ' . Phpfox::getUserId())
                            ->where('feed.privacy IN(0,1,2) AND feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0')
                            ->limit($iTotalFeeds)
                            ->union();

                    // Get my feeds
                    $this->database()->select('feed.*')
                            ->from($this->_sTable, 'feed')
                            ->where('feed.privacy IN(0,1,2,3,4) AND feed.user_id = ' . Phpfox::getUserId() . ' AND feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0')
                            ->limit($iTotalFeeds)
                            ->union();
                }
                else
                {
                    $sMyFeeds = '1,2,3,4';

                    // Get my friends feeds
                    $this->database()->select('feed.*')
                            ->from($this->_sTable, 'feed')
                            ->join(Phpfox::getT('friend'), 'f', 'f.user_id = feed.user_id AND f.friend_user_id = ' . Phpfox::getUserId())
                            ->where('feed.privacy IN(1,2) AND feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0')
                            ->union();

                    // Get my friends of friends feeds
                    $this->database()->select('feed.*')
                            ->from($this->_sTable, 'feed')
                            ->join(Phpfox::getT('friend'), 'f1', 'f1.user_id = feed.user_id')
                            ->join(Phpfox::getT('friend'), 'f2', 'f2.user_id = ' . Phpfox::getUserId() . ' AND f2.friend_user_id = f1.friend_user_id')
                            ->where('feed.privacy IN(2) AND feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0')
                            ->union();

                    // Get my feeds
                    $this->database()->select('feed.*')
                            ->from($this->_sTable, 'feed')
                            ->where('feed.privacy IN(' . $sMyFeeds . ') AND feed.user_id = ' . Phpfox::getUserId() . ' AND feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0')
                            ->union();

                    // Get public feeds
                    $this->database()->select('feed.*')
                            ->from($this->_sTable, 'feed')
                            ->where('feed.privacy IN(0) AND feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0')
                            ->union();

                    if (Phpfox::isModule('privacy'))
                    {
                        $this->database()->join(Phpfox::getT('privacy'), 'p', 'p.module_id = feed.type_id AND p.item_id = feed.item_id')
                                ->join(Phpfox::getT('friend_list_data'), 'fld', 'fld.list_id = p.friend_list_id AND fld.friend_user_id = ' . Phpfox::getUserId() . '');
                    }
                    // Get feeds based on custom friends lists	
                    $this->database()->select('feed.*')
                            ->from($this->_sTable, 'feed')
                            ->where('feed.privacy IN(4) AND feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0')
                            ->union();
                }

                $this->_hashSearch();

                $aRows = $this->database()->select('feed.*, f.friend_id AS is_friend, u.view_id,  ' . Phpfox::getUserField())
                        ->unionFrom('feed')
                        ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                        ->leftJoin(Phpfox::getT('friend'), 'f', 'f.user_id = feed.user_id AND f.friend_user_id = ' . Phpfox::getUserId())
                        ->order($sOrder)
                        ->group('feed.feed_id')
                        ->limit($iOffset, $iTotalFeeds)
                        ->execute('getSlaveRows');
            }
        }

        if ($bForceReturn === true)
        {
            return $aRows;
        }

        $bFirstCheckOnComments = false;
        if (Phpfox::getParam('feed.allow_comments_on_feeds') && Phpfox::isUser() && Phpfox::isModule('comment'))
        {
            $bFirstCheckOnComments = true;
        }

        $aFeedLoop = $aRows;

        $aFeeds = array();

        if (Phpfox::isModule('like'))
        {
            $oLike = Phpfox::getService('like');
        }
        foreach ($aFeedLoop as $sKey => $aRow)
        {
            $aRow['feed_time_stamp'] = $aRow['time_stamp'];

            if (($aReturn = $this->_processFeed($aRow, $sKey, $iUserId, $bFirstCheckOnComments)))
            {
                if (isset($aReturn['force_user']))
                {
                    $aReturn['user_name'] = $aReturn['force_user']['user_name'];
                    $aReturn['full_name'] = $aReturn['force_user']['full_name'];
                    $aReturn['user_image'] = $aReturn['force_user']['user_image'];
                    $aReturn['server_id'] = $aReturn['force_user']['server_id'];
                }

                $aReturn['feed_month_year'] = date('m_Y', $aRow['feed_time_stamp']);
                $aReturn['feed_time_stamp'] = $aRow['feed_time_stamp'];

                if (isset($aReturn['like_type_id']) && isset($oLike) && method_exists($oLike, 'getActionsFor'))
                {
                    $aReturn['marks'] = $oLike->getActionsFor($aReturn['like_type_id'], (isset($aReturn['like_item_id']) ? $aReturn['like_item_id'] : $aReturn['item_id']));
                }

                /* Lets figure out the phrases for like.display right here */
                $this->getPhraseForLikes($aReturn);

                $aFeeds[] = $aReturn;
            }
        }

        if (((isset($aData['status-id']) && $aData['status-id'])
                || (isset($aData['comment-id']) && $aData['comment-id'])
                || (isset($aData['link-id']) && $aData['link-id'])
                || (isset($aData['poke-id']) && $aData['poke-id'])
                )
                && isset($aFeeds[0]))
        {
            $aFeeds[0]['feed_view_comment'] = true;
        }

        return $aFeeds;
    }

    /**
     * This function replaces the routine in the like.block.display template
     * @param array $aFeed
     * @return string
     */
    public function getPhraseForLikes(&$aFeed)
    {
        /**
         * @var string
         */
        $sPhrase = '';
        $oParse = Phpfox::getLib('phpfox.parse.output');

        if (Phpfox::isModule('like'))
        {
            $oLike = Phpfox::getService('like');
        }
        $oUrl = Phpfox::getLib('url');

        if (!isset($aFeed['likes']) && isset($oLike))
        {
            $aFeed['likes'] = $oLike->getLikesForFeed($aFeed['type_id'], $aFeed['item_id']);
            $aFeed['total_likes'] = count($aFeed['likes']);
        }
        if (isset($aFeed['feed_is_liked']) && $aFeed['feed_is_liked'])
        {
            if (count($aFeed['likes']) == 0)
            {
                $sPhrase =  Phpfox::getPhrase('like.you');
            }
            else if (count($aFeed['likes']) == 1)
            {
                $sPhrase =  Phpfox::getPhrase('like.you_and') . '&nbsp;';
            }
            else
            {
                $sPhrase =  Phpfox::getPhrase('like.you_comma');
            }
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('like.article_to_upper');
        }

        if (isset($aFeed['likes']) && is_array($aFeed['likes']) && count($aFeed['likes']))
        {
            foreach ($aFeed['likes'] as $iIteration => $aLike)
            {
                if ((isset($aFeed['feed_is_liked']) && $aFeed['feed_is_liked']) || $iIteration > 0)
                {
                    $sPhrase .=  Phpfox::getPhrase('like.article_to_lower');
                }

                $sPhrase .= '<span class="user_profile_link_span" id="js_user_name_link_' . $aLike['user_name'] . '"><a href="' . $oUrl->makeUrl($aLike['user_name']) . '">' . $oParse->shorten($aLike['full_name'], 30) . '</a></span>'; //Phpfox::getParam('user.maximum_length_for_full_name'));
                if (count($aFeed['likes']) > 1 && (1 + $iIteration) == (count($aFeed['likes']) - 1) && isset($aFeed['feed_total_like']) && $aFeed['feed_total_like'] <= Phpfox::getParam('feed.total_likes_to_display'))
                {
                    $sPhrase .= '&nbsp;' .  Phpfox::getPhrase('like.and') . '&nbsp;';
                }
                elseif (isset($aFeed['likes']) && (1 + $iIteration) != count($aFeed['likes']))
                {
                    $sPhrase .= ',&nbsp;';
                }
            }
        }

        if (isset($aFeed['feed_total_like']) && $aFeed['feed_total_like'] > Phpfox::getParam('feed.total_likes_to_display'))
        {
            $sPhrase .= '<a href="#" onclick="return $Core.box(\'like.browse\', 400, \'type_id=' . $aFeed['like_type_id'] . '&amp;item_id=' . $aFeed['item_id'] . '\');">';
            $iTotalLeftShow = ($aFeed['feed_total_like'] - Phpfox::getParam('feed.total_likes_to_display'));
            if ($iTotalLeftShow == 1)
            {
                $sPhrase .= '&nbsp;' .  Phpfox::getPhrase('like.and') . '&nbsp;' .  Phpfox::getPhrase('like.1_other_person') . '&nbsp;';
            }
            else
            {
                $sPhrase .= '&nbsp;' .  Phpfox::getPhrase('like.and') . '&nbsp;' . number_format($iTotalLeftShow) . '&nbsp;' .  Phpfox::getPhrase('like.others') . '&nbsp;';
            }
            $sPhrase .= '</a>' .  Phpfox::getPhrase('like.likes_this');
        }
        else
        {
            if (isset($aFeed['likes']) && count($aFeed['likes']) > 1)
            {
                $sPhrase .= '&nbsp;' .  Phpfox::getPhrase('like.like_this');
            }
            else
            {
                if (isset($aFeed['feed_is_liked']) && $aFeed['feed_is_liked'])
                {
                    if (count($aFeed['likes']) == 1)
                    {
                        $sPhrase .= '&nbsp;' .  Phpfox::getPhrase('like.like_this');
                    }
                    else
                    {
                        if (count($aFeed['likes']) == 0)
                        {
                            $sPhrase .= '&nbsp;' .  Phpfox::getPhrase('like.you_like');
                        }
                        else
                        {
                            $sPhrase .=  Phpfox::getPhrase('like.likes_this');
                        }
                    }
                }
                else
                {
                    if (isset($aFeed['likes']) && count($aFeed['likes']) == 1)
                    {
                        $sPhrase .= '&nbsp;' .  Phpfox::getPhrase('like.likes_this');
                    }
                    else if (strlen($sPhrase) > 1)
                    {
                        $sPhrase .=  Phpfox::getPhrase('like.like_this');
                    }
                }
            }
        }

        $aActions = array();
        if (isset($oLike) && method_exists($oLike, 'getActionsFor'))
        {
            $aActions = $oLike->getActionsFor($aFeed['type_id'], $aFeed['item_id']);
        }

        if (strlen($sPhrase) > 1 || count($aActions) > 0)
        {
            $aFeed['bShowEnterCommentBlock'] = true;
        }
        $sPhrase = str_replace('&nbsp;&nbsp;', '&nbsp;', $sPhrase);
        $aFeed['feed_like_phrase'] = $sPhrase;
        if (empty($sPhrase))
        {
            $aFeed['feed_is_liked'] = false;
            $aFeed['feed_total_like'] = 0;
        }

        return $sPhrase;
    }

    /**
     * Input data:
     * + iFeedId: int, optional.
     * + iItemId: int, required.
     * 
     * Output data:
     * + iUserId: int.
     * + sUsername: string.
     * + iFeedId: int.
     * + UserProfileImg_Url: string.
     * + sFullName: string.
     * + timestamp: int.
     * + Time: string.
     * + TimeConverted: string.
     * + sTypeId: string.
     * + iItemId: int.
     * + iLikeId: int.
     * + iTotalLike: int.
     * + sContent: string.
     * 
     * @param array $aData
     * @param int $iFeedId
     * @return array
     */
    public function getOneFeed($aData, $iFeedId = 0)
    {
        extract($aData, EXTR_SKIP);

        if ($iFeedId > 0)
        {
            $iItemId = $iFeedId;
        }

        $aCond = array();
        $aCond[] = 'feed.feed_id = ' . (int) $iItemId;
        /**
         * @var array
         */
        $aRow = $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id, l.like_id')
                ->from(Phpfox::getT('feed'), 'feed')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                ->leftJoin(Phpfox::getT('like'), 'l', 'l.type_id = feed.type_id AND l.item_id = feed.item_id AND l.user_id = ' . Phpfox::getUserId())
                ->where($aCond)
                ->order('feed.time_stamp DESC')
                ->limit(1)
                ->execute('getSlaveRow');

        // Count the total like on feed.
        if (isset($aRow['feed_id']))
        {
            $iCount = (int) $this->database()->select('COUNT(l.like_id)')
                            ->from(Phpfox::getT('like'), 'l')
                            ->where('l.type_id = \'' . $this->database()->escape($aRow['type_id']) . '\' AND l.item_id = ' . (int) $aRow['item_id'])
                            ->execute('getfield');
        }
        else
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.feed_is_not_valid_or_you_don_not_have_permission_to_view_this_feed"))
            );
        }


        if (isset($aRow['type_id']))
        {
            $aModule = explode('_', $aRow['type_id']);
            if (isset($aModule[0]) && Phpfox::isModule($aModule[0]) && Phpfox::hasCallback($aModule[0] . (isset($aModule[1]) ? '_' . $aModule[1] : ''), 'getReportRedirect'))
            {
                $aRow['report_module'] = $aRows[$iKey]['report_module'] = $aModule[0] . (isset($aModule[1]) ? '_' . $aModule[1] : '');
                $aRow['report_phrase'] = $aRows[$iKey]['report_phrase'] =  Phpfox::getPhrase('feed.report_this_entry');
                $aRow['force_report'] = $aRows[$iKey]['force_report'] = true;
            }
        }
        /**
         * @var array
         */
        $aTemp = array(
            'iUserId' => $aRow['user_id'],
            'sUsername' => $aRow['user_name'],
            'iFeedId' => $aRow['feed_id'],
            'UserProfileImg_Url' => Phpfox::getService('mfox.user')->getImageUrl($aRow, '_250_square'),
            'sFullName' => $aRow['full_name'],
            'timestamp' => $aRow['time_stamp'],
            'Time' => date('l, F j, o', (int) $aRow['time_stamp']) . ' at ' . date('h:i a', (int) $aRow['time_stamp']),
            'TimeConverted' => Phpfox::getLib('date')->convertTime($aRow['time_stamp'], 'comment.comment_time_stamp'),
            'sTypeId' => $aRow['type_id'],
            'iItemId' => $aRow['item_id'],
            'iLikeId' => $aRow['like_id'],
            'iTotalLike' => $iCount
        );
        
        switch ($aRow['type_id']) {
            case 'user_status':
                $aTemp['sContent'] = $this->getContentOfUserStatus($aRow);
                break;
            case 'feed_comment':
                $aTemp['sContent'] = $this->getContentOfFeedComment($aRow);
                break;
            default:
                $aTemp['sContent'] = $aRow['feed_content'];
                break;
        }
        
        return $aTemp;
    }

    public function view($aData)
    {
        //  MORE CHANGING FROM OLD VERSION
        if (!isset($aData['iActionId']))
        {
            return array(
                'error_code' => 1,
                'error_element' => 'iActionId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        return $this->__get(array('iActionId' => $aData['iActionId']));
    }

    /**
     * Post user status.
     * 
     * Input data:
     * + sContent: string, required.
     * + iParentUserId: int, required.
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + iUserId: int.
     * + sUsername: string.
     * + iFeedId: int.
     * + UserProfileImg_Url: string.
     * + sFullName: string.
     * + timestamp: int.
     * + Time: string.
     * + TimeConverted: string.
     * + sTypeId: string.
     * + iItemId: int.
     * + iLikeId: int.
     * + iTotalLike: int.
     * + sContent: string.
     * 
     * @see Feed_Service_Process
     * @param array $aData
     * @return array
     */
    public function addComment($aData)
    {
        extract($aData, EXTR_SKIP);
        /**
         * @var string
         */
        $sContent = isset($sContent) ? $sContent : '';
        /**
         * @var int
         */
        $iParentUserId = isset($iParentUserId) ? (int) $iParentUserId : 0;
        /**
         * @var array
         */
        $aVals = array(
            'action' => 'upload_photo_via_share',
            'user_status' => $sContent,
            'parent_user_id' => $iParentUserId,
            'iframe' => 1,
            'method' => 'simple'
        );

        if (Phpfox::getLib('parse.format')->isEmpty($aVals['user_status']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('user.add_some_text_to_share')
            );
        }

        if (isset($aVals['user_status']) && ($iId = Phpfox::getService('feed.process')->addComment($aVals)))
        {
            return $this->getOneFeed(array('iItemId' => 0), $iId);
        }
        else
        {
            return array(
                'error_code' => 1,
                'error_message' => implode(' ', Phpfox_Error::get())
            );
        }
    }

    /**
     * Input data:
     * + sContent: string, required.
     * + sTypeId: string, optional.
     * + iPrivacyComment: int, optional.
     * + iPrivacy: int, optional.
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
      * + iUserId: int.
     * + sUsername: string.
     * + iFeedId: int.
     * + UserProfileImg_Url: string.
     * + sFullName: string.
     * + timestamp: int.
     * + Time: string.
     * + TimeConverted: string.
     * + sTypeId: string.
     * + iItemId: int.
     * + iLikeId: int.
     * + iTotalLike: int.
     * + sContent: string.
     * 
     * @see Mobile - API phpFox/Api V1.0 - Restful - Put method.
     * @see updatestatus
     * 
     * @param array $aData
     * @return array
     */
    public function putAction($aData)
    {
        return $this->updatestatus($aData);
    }

    /**
     * Input data:
     * + sContent: string, required.
     * + sTypeId: string, optional.
     * + iPrivacyComment: int, optional.
     * + iPrivacy: int, optional.
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
      * + iUserId: int.
     * + sUsername: string.
     * + iFeedId: int.
     * + UserProfileImg_Url: string.
     * + sFullName: string.
     * + timestamp: int.
     * + Time: string.
     * + TimeConverted: string.
     * + sTypeId: string.
     * + iItemId: int.
     * + iLikeId: int.
     * + iTotalLike: int.
     * + sContent: string.
     * 
     * @see Mobile - API phpFox/Api V1.0.
     * @see feed/updatestatus
     * 
     * @param array $aData
     * @return array
     */
    public function updatestatus($aData)
    {
        extract($aData, EXTR_SKIP);

        if (!isset($sContent))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        if (!isset($sTypeId))
        {
            /**
             * @var string
             */
            $sTypeId = 'user_status';
        }

        if (!isset($iPrivacyComment))
        {
            /**
             * @var int
             */
            $iPrivacyComment = 0;
        }

        if (!isset($iPrivacy))
        {
            /**
             * @var int
             */
            $iPrivacy = 0;
        }

        /**
         * @var array
         */
        $aVals = array(
            'user_status' => $sContent,
            'privacy' => $iPrivacy,
            'privacy_comment' => $iPrivacyComment
        );

        if (Phpfox::getLib('parse.format')->isEmpty($aVals['user_status']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('user.add_some_text_to_share')
            );
        }

        if (!Phpfox::getService('ban')->checkAutomaticBan($aVals['user_status']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.your_account_has_been_banned"))
            );
        }
        /**
         * @var string
         */
        $sStatus = $this->preParse()->prepare($aVals['user_status']);
        /**
         * @var array
         */
        $aUpdates = $this->database()->select('content')
                ->from(Phpfox::getT('user_status'))
                ->where('user_id = ' . (int) Phpfox::getUserId())
                ->limit(Phpfox::getParam('user.check_status_updates'))
                ->order('time_stamp DESC')
                ->execute('getSlaveRows');

        /**
         * @var int
         */
        $iReplications = 0;
        foreach ($aUpdates as $aUpdate)
        {
            if ($aUpdate['content'] == $sStatus)
            {
                $iReplications++;
            }
        }

        if ($iReplications > 0)
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('user.you_have_already_added_this_recently_try_adding_something_else')
            );
        }

        if (empty($aVals['privacy']))
        {
            $aVals['privacy'] = 0;
        }

        if (empty($aVals['privacy_comment']))
        {
            $aVals['privacy_comment'] = 0;
        }
        
        #Insert vals
        $aInsert = array(
            'user_id' => (int) Phpfox::getUserId(),
            'privacy' => $aVals['privacy'],
            'privacy_comment' => $aVals['privacy_comment'],
            'content' => $sStatus,
            'time_stamp' => PHPFOX_TIME
        );
        
        if (!empty($sLatitude) && !empty($sLongitude))
        {
			$sLatitude = floatval($sLatitude);
			$sLongitude = floatval($sLongitude);
			$aInsert['location_latlng'] = json_encode(array('latitude' => $sLatitude, 'longitude' => $sLongitude));
        }
		
		if (!empty($aInsert['location_latlng']) && !empty($sLocationName))
		{
			$aInsert['location_name'] = Phpfox::getLib('parse.input')->clean($sLocationName);
		}
        
        /**
         * @var int
         */
        $iStatusId = $this->database()->insert(Phpfox::getT('user_status'), $aInsert);

        if (isset($aVals['privacy']) && $aVals['privacy'] == '4')
        {
            Phpfox::getService('privacy.process')->add('user_status', $iStatusId, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : array()));
        }

        Phpfox::getService('user.process')->notifyTagged($sStatus, $iStatusId, 'status');
        /**
         * @var int
         */
        $iId = Phpfox::getService('feed.process')->allowGuest()->add('user_status', $iStatusId, $aVals['privacy'], $aVals['privacy_comment'], 0, null, 0, (isset($aVals['parent_feed_id']) ? $aVals['parent_feed_id'] : 0), (isset($aVals['parent_module_id']) ? $aVals['parent_module_id'] : null));

        if ($iId)
        {
            return $this->getOneFeed(array('iItemId' => 0), $iId);
        }
        /**
         * @var string
         */
        $sMessage = '';
        $aErrorMessage = Phpfox_Error::get();
        foreach ($aErrorMessage as $sErrorMessage)
        {
            $sMessage .= $sErrorMessage;
        }

        return array(
            'error_code' => 1,
            'error_message' => $sMessage
        );
    }

    /**
     * Input data:
     * + iItemId: int, required.
     * + sModule: string, optional.
     * + iItem: int, optional.
     * 
     * Output data:
     * + result: int.
     * + error_code: int.
     * + error_message: string.
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see feed/delete
     * 
     * @global type $token
     * @param array $aData
     * @return array
     */
    public function deleteAction($aData)
    {
        return $this->delete($aData);
    }

    public function delete($aData)
    {
        //  init 
        $iActionId = isset($aData['iActionId']) ? (int)$aData['iActionId'] : 0;
        $iCommentId = isset($aData['iCommentId']) ? (int)$aData['iCommentId'] : 0;
        if (!$iActionId)
        {
            return array(
                'error_code' => 1,
                'error_element' => 'iActionId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid")),
                'result' => 0
            );
        }

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_delete_this_action")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        $sModule = null;
        $iItem = 0;
        if(isset($aData['sParentId'])){
            $sParentId = isset($aData['sParentId']) ? (int)$aData['sParentId'] : 0;
            if('event' == $aData['sParentId'] 
                || 'event_comment' == $aData['sParentId']
                || 'fevent' == $aData['sParentId'] 
                || 'fevent_comment' == $aData['sParentId']
            ){
                $eventFeed = Phpfox::getService('mfox.event')->getEventFeedByFeedID($iActionId);
                $sModule = Phpfox::getService('mfox.event')->isAdvancedModule() ? 'fevent' : 'event';
                $iItem = $eventFeed['item_id'];
            } else if('pages' == $aData['sParentId'] || 'pages_comment' == $aData['sParentId'] ) {
                $pagesFeed = Phpfox::getService('mfox.pages')->getPagesFeedByFeedID($iActionId);
                $sModule = 'pages';
                $iItem = $pagesFeed['item_id'];
            } else if('directory' == $aData['sParentId'] || 'directory_comment' == $aData['sParentId'] ) {
                $directoryFeed = Phpfox::getService('mfox.directory')->getDirectoryFeedById($iActionId);
                $sModule = 'directory';
                $iItem = $directoryFeed['item_id'];
            }
        } else {
            //  process 
            $feed = Phpfox::getService('feed')->getFeed($iActionId);
            if(!isset($feed['feed_id'])){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.oops_the_action_not_found")),
                    'result' => 0
                );
            }            
        }

        if (!$iCommentId){
            //  delete feed
            $ret = $this->__deleteFeed($iActionId, $sModule, $iItem);
            if(true === $ret){
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'message'=>html_entity_decode(Phpfox::getPhrase("mfox.this_activity_item_has_been_removed"))
                );
            } else if(false === $ret){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.oops_the_action_not_found")),
                    'result' => 0
                );
            } else {
                return $ret;
            }
        } else{
            //  delete comment
            $ret = $this->__deleteComment($iCommentId);
            if(true === $ret){
                return array(
                    'result' => 1,
                    'error_code' => 0,
                    'message'=>html_entity_decode(Phpfox::getPhrase("mfox.comment_has_been_deleted"))
                );
            } else if(false === $ret){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.oops_the_action_not_found")),
                    'result' => 0
                );
            } else {
                return $ret;
            }
        }
        
        //  end 
        return array(
            'error_code' => 1,
            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.oops_the_action_not_found")),
            'result' => 0
        );        
    }

    public function deleteFeed($iId, $sModule = null, $iItem = 0)
    {
        $aCallback = null;
        if (!empty($sModule))
        {
            if (Phpfox::hasCallback($sModule, 'getFeedDetails'))
            {
                $aCallback = Phpfox::callback($sModule . '.getFeedDetails', $iItem);
            }
        }
        $aFeed = Phpfox::getService('feed')->callback($aCallback)->getFeed($iId);

        if (!isset($aFeed['feed_id']))
        {
            return false;
        }

        // Check the privacy.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check($aFeed['type_id'], $aFeed['feed_id'], $aFeed['user_id'], $aFeed['privacy'], $aFeed['is_friend'], true))
        {
            return false;
        }

        $bCanDelete = false;
        if (Phpfox::getUserParam('feed.can_delete_own_feed') && ($aFeed['user_id'] == Phpfox::getUserId()))
        {
            $bCanDelete = true;
        }

        if (defined('PHPFOX_FEED_CAN_DELETE'))
        {
            $bCanDelete = true;
        }

        if (Phpfox::getUserParam('feed.can_delete_other_feeds'))
        {
            $bCanDelete = true;
        }

        if ($bCanDelete === true)
        {
            if (isset($aCallback['table_prefix']))
            {
                $this->database()->delete(Phpfox::getT($aCallback['table_prefix'] . 'feed'), 'feed_id = ' . (int) $iId);
            }

            //$this->database()->delete(Phpfox::getT('feed'), 'feed_id = ' . $aFeed['feed_id'] . ' AND user_id = ' . $aFeed['user_id'] .' AND time_stamp = ' . $aFeed['time_stamp']);
            $this->database()->delete(Phpfox::getT('feed'), 'user_id = ' . $aFeed['user_id'] . ' AND time_stamp = ' . $aFeed['time_stamp']);

            // Delete likes that belonged to this feed
            $this->database()->delete(Phpfox::getT('like'), 'type_id = "' . $aFeed['type_id'] . '" AND item_id = ' . $aFeed['item_id']);

            if (!empty($sModule))
            {
                if (Phpfox::hasCallback($sModule, 'deleteFeedItem'))
                {
                    Phpfox::callback($sModule . '.deleteFeedItem', $iItem);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Input data:
     * + type_id: string.
     * + feed_id: int.
     * + can_post_comment: bool.
     * 
     * @param array $aRow
     * @param string $sKey
     * @param int $iUserid
     * @param bool $bFirstCheckOnComments
     * @return array 
     */
    private function _processFeed($aRow, $sKey, $iUserid, $bFirstCheckOnComments)
    {
        switch ($aRow['type_id'])
        {
            case 'comment_profile':
            case 'comment_profile_my':
                $aRow['type_id'] = 'profile_comment'; break;
            case 'profile_info':
                $aRow['type_id'] = 'custom'; break;
            case 'comment_photo':
                $aRow['type_id'] = 'photo_comment'; break;
            case 'comment_blog':
                $aRow['type_id'] = 'blog_comment'; break;
            case 'comment_video':
                $aRow['type_id'] = 'video_comment'; break;
            case 'comment_group':
                $aRow['type_id'] = 'pages_comment'; break;              
        }
        
        if (preg_match('/(.*)_feedlike/i', $aRow['type_id'])
                || $aRow['type_id'] == 'profile_design'
        )
        {
            $this->database()->delete(Phpfox::getT('feed'), 'feed_id = ' . (int) $aRow['feed_id']);

            return false;
        }

        if (!Phpfox::hasCallback($aRow['type_id'], 'getActivityFeed'))
        {
            return false;
        }

        // we do not support cache here 
        // $bCacheFeed = false;
        // if (Phpfox::getParam('feed.cache_each_feed_entry'))
        // {
        //     $bCacheFeed = true;
        // }

        /**
         * Fix callback
         * @todo remove when fixed from module
         */
        switch ($aRow['type_id'])
        {
            case 'directory_checkinhere':
                $aFeed = Phpfox::getService('mfox.directory')->getActivityFeedCheckinhere($aRow, (isset($this->_aCallback['module']) ? $this->_aCallback : null));
                break;

            case 'blog':
                $aFeed = Phpfox::getService('mfox.blog')->getActivityFeed($aRow, (isset($this->_aCallback['module']) ? $this->_aCallback : null));
                break;
            
            default:
                $aFeed = Phpfox::callback($aRow['type_id'] . '.getActivityFeed', $aRow, (isset($this->_aCallback['module']) ? $this->_aCallback : null));
                break;
        }
        if ($aFeed === false)
        {
            return false;
        }

        if (isset($this->_aViewMoreFeeds[$sKey]))
        {
            foreach ($this->_aViewMoreFeeds[$sKey] as $iSubKey => $aSubRow)
            {
                $mReturnViewMore = $this->_processFeed($aSubRow, $iSubKey, $iUserid, $bFirstCheckOnComments);

                if ($mReturnViewMore === false)
                {
                    continue;
                }

                $aFeed['more_feed_rows'][] = $mReturnViewMore;
            }
        }

        if (Phpfox::isModule('like') && (isset($aFeed['like_type_id']) || isset($aRow['item_id'])) && ( (isset($aFeed['enable_like']) && $aFeed['enable_like'])) || (!isset($aFeed['enable_like'])) &&  (isset($aFeed['feed_total_like']) && (int) $aFeed['feed_total_like'] > 0))
        {
            $aFeed['likes'] = Phpfox::getService('like')->getLikesForFeed($aFeed['like_type_id'], (isset($aFeed['like_item_id']) ? $aFeed['like_item_id'] : $aRow['item_id']), ((int) $aFeed['feed_is_liked'] > 0 ? true : false), Phpfox::getParam('feed.total_likes_to_display'), true);              
            $aFeed['feed_total_like'] = Phpfox::getService('like')->getTotalLikeCount();
            
            
            // if (Phpfox::getParam('feed.cache_each_feed_entry'))
            // {
            //     $aAllLikesRows = $this->database()->select('user_id')
            //         ->from(Phpfox::getT('like'))
            //         ->where('type_id = \'' . $aFeed['like_type_id'] . '\' AND item_id = ' . (isset($aFeed['like_item_id']) ? $aFeed['like_item_id'] : $aRow['item_id']))
            //         ->execute('getSlaveRows');
            //     foreach ($aAllLikesRows as $aAllLikesRow)
            //     {
            //         $aFeed['likes_history'][$aAllLikesRow['user_id']] = true;
            //     }
            // }                  
        }

        if (isset($aFeed['comment_type_id']) && (int) $aFeed['total_comment'] > 0 && Phpfox::isModule('comment'))
        {   
                $aFeed['comments'] = Phpfox::getService('comment')->getCommentsForFeed($aFeed['comment_type_id'], $aRow['item_id'], Phpfox::getParam('comment.total_comments_in_activity_feed'));
                //$aFeed['comments'] = Phpfox::getService('comment')->getCommentsForFeed($aFeed['comment_type_id'], (!empty($aRow['feed_reference']) ? $aRow['feed_reference'] : $aRow['item_id']), Phpfox::getParam('comment.total_comments_in_activity_feed'));
                // if (Phpfox::getParam('feed.cache_each_feed_entry'))
                // {
                //     foreach ($aFeed['comments'] as $iCommentRowCnt => $aCommentRow)
                //     {
                //         $aCommentLikesRows = $this->database()->select('user_id')
                //             ->from(Phpfox::getT('like'))
                //             ->where('type_id = \'feed_mini\' AND item_id = ' . $aCommentRow['comment_id'])
                //             ->execute('getSlaveRows');
                //         foreach ($aCommentLikesRows as $aCommentLikesRow)
                //         {
                //             $aFeed['comments'][$iCommentRowCnt]['liked_history'][$aCommentLikesRow['user_id']] = true;
                //         }   
                //     }   
                // }
        }   

        if (isset($aRow['app_title']) && $aRow['app_id'])
        {
                $sLink = '<a href="' . Phpfox::permalink('apps', $aRow['app_id'], $aRow['app_title']) . '">' . $aRow['app_title'] . '</a>';
                $aFeed['app_link'] = $sLink;            
        }

        // Check if user can post comments on this feed/item
        $bCanPostComment = false;
        if ($bFirstCheckOnComments)
        {
                $bCanPostComment = true;    
        }       
        if ($iUserid !== null && $iUserid != Phpfox::getUserId())
        {
            switch ($aRow['privacy_comment'])
            {
                case '1':
                    // http://www.phpfox.com/tracker/view/14418/ instead of "if(!Phpfox::getService('user')->getUserObject($iUserid)->is_friend)"
                    if (Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aRow['user_id']))
                    {
                    $bCanPostComment = false;
                    }
                    break;
                case '2':
                    // http://www.phpfox.com/tracker/view/14418/ instead of "if (!Phpfox::getService('user')->getUserObject($iUserid)->is_friend && !Phpfox::getService('user')->getUserObject($iUserid)->is_friend_of_friend)"
                    if (Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aRow['user_id']) && Phpfox::getService('friend')->isFriendOfFriend($aRow['user_id']))
                    {
                    $bCanPostComment = false;
                    }
                    break;
                case '3':
                    $bCanPostComment = false;
                    break;
            }
        }

        if ($iUserid === null)
        {
            if ($aRow['user_id'] != Phpfox::getUserId())
            {
                switch ($aRow['privacy_comment'])
                {   
                    case '1':
                    case '2':
                            if (!isset($aRow['is_friend']) || !$aRow['is_friend'])
                            {
                                    $bCanPostComment = false;
                            }
                            break;
                    case '3':
                            $bCanPostComment = false;
                            break;
                }
            }
        }

        $aRow['can_post_comment'] = $bCanPostComment;

        if (!isset($aFeed['marks']))
        {
            if(Phpfox::isModule('like'))
            {
            }
        }       
        
        $aFeed['bShowEnterCommentBlock'] = false;
        if (
            ( isset($aFeed['feed_total_like']) && $aFeed['feed_total_like'] > 0) ||
            ( isset($aFeed['marks']) && is_array($aFeed['marks']) && count($aFeed['marks'])) ||
            ( isset($aFeed['comments']) && is_array($aFeed['comments']) && count($aFeed['comments']))
            )
        {
            $aFeed['bShowEnterCommentBlock'] = true;
        }


        // fix for item_id which has been changed
        $aRow['item_id_in_db'] = $aRow['item_id'];

        $aOut = array_merge($aRow, $aFeed);
        return $aOut;       
    }

    /**
     *
     * Input data:
     * + iFeedId: int, required.
     * + iCommentId: int, optional. (like on comment of this feed)
     *
     * Output data:
     * + result: int.
     * + error_code: int.
     * + error_message: string.
     *
     */
    public function like($aData)
    {
        //  init 
        $iFeedId = isset($aData['iFeedId']) ? (int)$aData['iFeedId'] : 0;
        $iCommentId = (isset($aData['iCommentId']) && !empty($aData['iCommentId'])) ? $aData['iCommentId'] : 0;
        $sParentId = (isset($aData['sParentId']) && !empty($aData['sParentId'])) ? $aData['sParentId'] : 0;
        if ($iFeedId < 1)
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_element' => 'iFeedId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        //  process
        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_like_this_action")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        if (!$iCommentId){
            if($sParentId 
                && ($sParentId == 'event' 
                    || $sParentId == 'fevent'
                    || $sParentId == 'pages'
                    || $sParentId == 'groups'
                    || $sParentId == 'directory'
                )
            ) {
                if($sParentId == 'groups')
                {
                    $feed = Phpfox::getService('feed')->callback(array(
                        'table_prefix' =>'pages_' 
                    ))->getFeed($iFeedId);
                }
                else{
                    $feed = Phpfox::getService('feed')->callback(array(
                        'table_prefix' => $sParentId . '_' 
                    ))->getFeed($iFeedId);
                }

            } else {
                $feed = Phpfox::getService('feed')->getFeed($iFeedId);
            }
            if(!isset($feed['feed_id'])){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.oops_the_activity_not_found")),
                    'result' => 0
                );
            }

            //  like feed
            $feed['is_friend'] = Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $feed['user_id'], $bRedirect = false);
            // Check the privacy.
            if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check($feed['type_id'], $feed['feed_id'], $feed['user_id'], $feed['privacy'], $feed['is_friend'], true))
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_like_this_feed"))
                );
            }

            if($feed['type_id'] == 'music_album') {
                $sType = 'music_song';
            } else if($feed['type_id'] == 'forum') {
                $aThread = Phpfox::getService('forum.thread')->getActualThread($feed['item_id']);
                $feed['item_id'] = $aThread['start_id'];
                $sType = 'forum_post';
            } else {
                $sType = $feed['type_id'];
            } 

            // custom work fore like
            $aLikeData = array(
                'sItemType' => $sType,
                'iItemId' => $feed['item_id'],
                'sParentId' => $sParentId,
                'eid' => !empty($aData['eid']) ? $aData['eid'] : 0,
            );

            $ret = Phpfox::getService('mfox.like')->add($aLikeData);
            if(true === $ret || $ret['result'] == 1){
                return $ret;
            } else if(false === $ret){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.oops_the_action_not_found")),
                    'result' => 0
                );
            } else {
                return $ret;
            }
        } else{
            //  like comment
            $aComment = Phpfox::getService('mfox.comment')->getCommentByID($iCommentId);

            if (!isset($aComment['comment_id']))
            {
                return array(
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.comment_does_not_exist_or_has_been_deleted")),
                    'error_code' => 1,
                    'result' => 0
                );
            }

            $ret = Phpfox::getService('mfox.like')->add(array('sItemType' => 'feed_mini', 'iItemId' => $aComment['comment_id'], 'sParentId' => $sParentId));
            if(true === $ret || $ret['result'] == 1){
                return $ret;
            } else if(false === $ret){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_action_not_found")),
                    'result' => 0
                );
            } else {
                return $ret;
            }
        }
        
        //  end 
        return array(
            'error_code' => 1,
            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_action_not_found")),
            'result' => 0
        );         
    }

    public function dislike($aData){
        //  init 
        $iFeedId = isset($aData['iFeedId']) ? (int)$aData['iFeedId'] : 0;
        $iCommentId = (isset($aData['iCommentId']) && !empty($aData['iCommentId'])) ? $aData['iCommentId'] : 0;
        if ($iFeedId < 1)
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_element' => 'iFeedId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        //  process
        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_dislike_this_action")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        if (!$iCommentId){
            $feed = Phpfox::getService('feed')->getFeed($iFeedId);
            if(!isset($feed['feed_id'])){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                    'result' => 0
                );
            }

            //  dislike feed
            $feed['is_friend'] = Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $feed['user_id'], $bRedirect = false);
            // Check the privacy.
            if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check($feed['type_id'], $feed['feed_id'], $feed['user_id'], $feed['privacy'], $feed['is_friend'], true))
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_dislike_this_feed"))
                );
            }

            $ret = Phpfox::getService('mfox.like')->dislikeadd(array('sItemType' => $feed['type_id'], 'iItemId' => $feed['item_id']));
            if(true === $ret || $ret['result'] == 1){
                return $ret;
            } else if(false === $ret){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_action_not_found")),
                    'result' => 0
                );
            } else {
                return $ret;
            }
        } else{
            //  dislike comment
            $aComment = Phpfox::getService('mfox.comment')->getCommentByID($iCommentId);

            if (!isset($aComment['comment_id']))
            {
                return array(
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.comment_does_not_exist_or_has_been_deleted")),
                    'error_code' => 1,
                    'result' => 0
                );
            }

            $ret = Phpfox::getService('mfox.like')->dislikeadd(array('sItemType' => 'feed_mini', 'iItemId' => $iFeedId, 'iCommentId' => $aComment['comment_id']));
            if(true === $ret || $ret['result'] == 1){
                return $ret;
            } else if(false === $ret){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_action_not_found")),
                    'result' => 0
                );
            } else {
                return $ret;
            }
        }

        //  end 
        return array(
            'error_code' => 1,
            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_action_not_found")),
            'result' => 0
        );        
    }

    /**
     * Check privacy on user status feed.
     * @param int $iFeedId Feed id.
     * @param string $sModule Module name in page.
     * @param int $iItem Item id in page.
     * @return array
     */
    public function checkPrivacyOnUserStatusFeed($iItemId, $sType, $sModule, $iItem)
    {
        /**
         * @var array
         */
        $aCallback = null;
        if (!empty($sModule))
        {
            if (Phpfox::hasCallback($sModule, 'getFeedDetails'))
            {
                $aCallback = Phpfox::callback($sModule . '.getFeedDetails', $iItem);
            }
        }

        $aFeed = $this->database()
                ->select('*')
                ->from(Phpfox::getT((isset($aCallback['table_prefix']) ? $aCallback['table_prefix'] : '') . 'feed'))
                ->where('item_id =' . (int) $iItemId . ' AND type_id = "' . $sType . '"')
                ->execute('getSlaveRow');
        
        if (!isset($aFeed['feed_id']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.feed_is_not_valid"))
            );
        }

        $aFeed['is_friend'] = Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aFeed['user_id'], $bRedirect = false);
        
        // Check the privacy.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check($aFeed['type_id'], $aFeed['feed_id'], $aFeed['user_id'], $aFeed['privacy'], $aFeed['is_friend'], true))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_this_feed"))
            );
        }
        
        return null;
    }
    
    /**
     *
     * Input data:
     * + iFeedId: int, required.
     * + iCommentId: int, optional. (like on comment of this feed)
     *
     * Output data:
     * + result: int.
     * + error_code: int.
     * + error_message: string.
     *
     */
    public function unlike($aData)
    {
        //  init 
        $iFeedId = isset($aData['iFeedId']) ? (int)$aData['iFeedId'] : 0;
        $iCommentId = (isset($aData['iCommentId']) && !empty($aData['iCommentId'])) ? $aData['iCommentId'] : 0;
        $sParentId = (isset($aData['sParentId']) && !empty($aData['sParentId'])) ? $aData['sParentId'] : 0;
        if ($iFeedId < 1)
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_element' => 'iFeedId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        //  process
        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_unlike_this_action")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        if (!$iCommentId){
            if($sParentId 
                && ($sParentId == 'event' 
                    || $sParentId == 'fevent'
                    || $sParentId == 'pages'
                    || $sParentId == 'directory'
                ) 
            ) {
                $feed = Phpfox::getService('feed')->callback(array(
                    'table_prefix' => $sParentId . '_' 
                ))->getFeed($iFeedId);

            } else {
                $feed = Phpfox::getService('feed')->getFeed($iFeedId);
            }

            if(!isset($feed['feed_id'])){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                    'result' => 0
                );
            }

            //  like feed
            $feed['is_friend'] = Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $feed['user_id'], $bRedirect = false);
            // Check the privacy.
            if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check($feed['type_id'], $feed['feed_id'], $feed['user_id'], $feed['privacy'], $feed['is_friend'], true))
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_like_this_feed"))
                );
            }

            if($feed['type_id'] == 'music_album') {
                $sType = 'music_song';
            } else if($feed['type_id'] == 'forum') {
                $aThread = Phpfox::getService('forum.thread')->getActualThread($feed['item_id']);
                $feed['item_id'] = $aThread['start_id'];
                $sType = 'forum_post';
            } else {
                $sType = $feed['type_id'];
            } 

            $ret = Phpfox::getService('mfox.like')->delete(array('sItemType' => $sType, 'iItemId' => $feed['item_id']));
            if(true === $ret || $ret['result'] == 1){
                return $ret;
            } else if(false === $ret){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_action_not_found")),
                    'result' => 0
                );
            } else {
                return $ret;
            }
        } else{
            //  like comment
            $aComment = Phpfox::getService('mfox.comment')->getCommentByID($iCommentId);

            if (!isset($aComment['comment_id']))
            {
                return array(
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.comment_does_not_exist_or_has_been_deleted")),
                    'error_code' => 1,
                    'result' => 0
                );
            }

            $ret = Phpfox::getService('mfox.like')->delete(array('sItemType' => 'feed_mini', 'iItemId' => $aComment['comment_id']));
            if(true === $ret || $ret['result'] == 1){
                return $ret;
            } else if(false === $ret){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_action_not_found")),
                    'result' => 0
                );
            } else {
                return $ret;
            }
        }
        
        //  end 
        return array(
            'error_code' => 1,
            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_action_not_found")),
            'result' => 0
        );       
    }

    public function dislikedelete($aData){
        //  init 
        $iFeedId = isset($aData['iFeedId']) ? (int)$aData['iFeedId'] : 0;
        $iCommentId = (isset($aData['iCommentId']) && !empty($aData['iCommentId'])) ? $aData['iCommentId'] : 0;
        if ($iFeedId < 1)
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_element' => 'iFeedId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        //  process
        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_remove_dislike_on_this_action")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        if (!$iCommentId){
            $feed = Phpfox::getService('feed')->getFeed($iFeedId);
            if(!isset($feed['feed_id'])){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                    'result' => 0
                );
            }

            //  remove dislike of feed
            $feed['is_friend'] = Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $feed['user_id'], $bRedirect = false);
            // Check the privacy.
            if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check($feed['type_id'], $feed['feed_id'], $feed['user_id'], $feed['privacy'], $feed['is_friend'], true))
            {
                return array(
                    'result' => 0,
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_remove_dislike_on_this_feed"))
                );
            }

            $ret = Phpfox::getService('mfox.like')->dislikedelete(array('sItemType' => $feed['type_id'], 'iItemId' => $feed['item_id']));
            if(true === $ret || $ret['result'] == 1){
                return $ret;
            } else if(false === $ret){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_action_not_found")),
                    'result' => 0
                );
            } else {
                return $ret;
            }
        } else{
            //  remove dislike on comment
            $aComment = Phpfox::getService('mfox.comment')->getCommentByID($iCommentId);

            if (!isset($aComment['comment_id']))
            {
                return array(
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.comment_does_not_exist_or_has_been_deleted")),
                    'error_code' => 1,
                    'result' => 0
                );
            }

            $ret = Phpfox::getService('mfox.like')->dislikedelete(array('sItemType' => 'feed_mini', 'iItemId' => $iFeedId, 'iCommentId' => $aComment['comment_id']));
            if(true === $ret || $ret['result'] == 1){
                return $ret;
            } else if(false === $ret){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_action_not_found")),
                    'result' => 0
                );
            } else {
                return $ret;
            }
        }
        
        //  end 
        return array(
            'error_code' => 1,
            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_action_not_found")),
            'result' => 0
        );         
    }
    
    /**
     * Input data:
     * + item_id: int, required.
     * + user_id: int, required.
     * + full_name: string, required.
     * 
     * Output data:
     * + link: array (iFeedId: int, sTypeId: string, iItemId: int)
     * + message: string.
     * + icon: string.
     * 
     * @param array $aNotification
     * @return boolean
     */
    public function doFeedGetNotificationComment_Profile($aNotification)
    {
        /**
         * @var array
         */
        $aRow = $this->database()->select('fc.feed_comment_id, u.user_id, u.gender, u.user_name, u.full_name')
                ->from(Phpfox::getT('feed_comment'), 'fc')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.parent_user_id')
                ->where('fc.feed_comment_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');
        
        /**
         * @var string
         */
        $sType = 'comment-id';
        if (empty($aRow))
        {
            $aRow = $this->database()->select('u.user_id, u.gender, u.user_name, u.full_name')
                    ->from(Phpfox::getT('user_status'), 'fc')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
                    ->where('fc.status_id = ' . (int) $aNotification['item_id'])
                    ->execute('getSlaveRow');

            $aRow['feed_comment_id'] = (int) $aNotification['item_id'];
            $sType = 'status-id';
            $bWasChanged = true;
        }
        /**
         * @var string
         */
        $sUsers = Phpfox::getService('notification')->getUsers($aNotification);
        if (empty($aRow) || !isset($aRow['user_id']))
        {
            return array();
        }
        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            if (isset($bWasChanged))
            {
                $sPhrase =  Phpfox::getPhrase('user.user_name_tagged_you_in_a_status_update', array('user_name' => $aNotification['full_name']));
            }
            else
            {
                $sPhrase =  Phpfox::getPhrase('feed.users_commented_on_gender_wall', array('users' => $sUsers, 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1)));
            }
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('feed.users_commented_on_your_wall', array('users' => $sUsers));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('feed.users_commented_on_one_span_class_drop_data_user_row_full_name_span_wall', array('users' => $sUsers, 'row_full_name' => $aRow['full_name']));
        }
        
        if (!isset($aRow['feed_comment_id']))
        {
            return array();
        }
        
        /**
         * @var array
         */
        $aFeeds = $this->getfeed(array($sType => $aRow['feed_comment_id']), $aRow['user_id']);
        
        /**
         * @var array
         */
        if (isset($aFeeds[0]['feed_id']))
        {
            $aLink = array('iFeedId' => $aFeeds[0]['feed_id'], 'sTypeId' => $aFeeds[0]['type_id'], 'iItemId' => $aFeeds[0]['item_id']);
        }
        else
        {
            return array();
        }
        
        return array(
            'link' => $aLink,
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }
    
    /**
     * Input data:
     * + item_id: int, required.
     * 
     * Output data:
     * + link: array.
     * + message: string.
     * + icon: string.
     * 
     * @param array $aNotification
     * @return array
     */
    public function doFeedGetNotificationMini_Like($aNotification)
    {
        /**
         * @var array
         */
        $aRow = $this->database()->select('c.comment_id, c.user_id, ct.text_parsed AS text, c.type_id, c.item_id')
                ->from(Phpfox::getT('comment'), 'c')
                ->join(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
                ->where('c.comment_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');
        
        if (!isset($aRow['comment_id']))
        {
            return array();
        }
        
        /**
         * @var array
         */
        $aLink = Phpfox::getService('mfox.comment')->doCommentGetRedirectRequest($aRow);
        /**
         * @var string
         */
        $sPhrase =  Phpfox::getPhrase('feed.users_liked_your_comment_text_that_you_posted', array('users' => Phpfox::getService('notification')->getUsers($aNotification), 'text' => Phpfox::getLib('parse.output')->shorten($aRow['text'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        if (!$aLink)
        {
            return array();
        }
        return array(
            'link' => $aLink,
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }
    
    /**
     * Note: feed id in home is different with feed id in page.
     * 
     * Input data:
     * + iFeedId: int, required.
     * + sModule: string, optional. (Required in page).
     * + iItem: int, optional. (Required in page)
     * + lastLikeIdViewed: int, optional.
     * + amountOfLike: int, option.
     * 
     * Output data:
	 * + iLikeId: int
	 * + iUserId: int
	 * + sFullName: string
	 * + sImage: string
     * 
     * @see Mobile - API phpFox/Api V1.0
     * @see feed/list_all_likes
     * 
     * @param array $aData
     * @return array
     */
    public function list_all_likes($aData)
    {
        /**
         * @var int
         */
        $iFeedId = isset($aData['iFeedId']) ? (int) $aData['iFeedId'] : 0;
        /**
         * @var string
         */
        $sModule = (isset($aData['sModule']) && !empty($aData['sModule'])) ? $aData['sModule'] : null;
        /**
         * @var int
         */
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;
        /**
         * @var array
         */
        $aCallback = null;
        if (!empty($sModule))
        {
            if (Phpfox::hasCallback($sModule, 'getFeedDetails'))
            {
                $aCallback = Phpfox::callback($sModule . '.getFeedDetails', $iItem);
            }
        }
        /**
         * @var array
         */
        $aFeed = Phpfox::getService('feed')->callback($aCallback)->getFeed($iFeedId);
        
        if (!isset($aFeed['feed_id']))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.feed_is_not_valid"))
            );
        }

        $aFeed['is_friend'] = Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aFeed['user_id'], $bRedirect = false);
        
        // Check the privacy.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check($aFeed['type_id'], $aFeed['feed_id'], $aFeed['user_id'], $aFeed['privacy'], $aFeed['is_friend'], true))
        {
            return array(
                'result' => 0,
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_this_feed"))
            );
        }
        
        /**
         * @var int
         */
        $lastLikeIdViewed = isset($aData['lastLikeIdViewed']) ? (int) $aData['lastLikeIdViewed'] : 0;
        /**
         * @var int
         */
        $amountOfLike = isset($aData['amountOfLike']) ? (int) $aData['amountOfLike'] : 20;
        
        return Phpfox::getService('mfox.like')->listalllikes(array(
            'sType' => $aFeed['type_id'], 
            'iItemId' => $aFeed['item_id'],
            'lastCommentIdViewed' => $lastLikeIdViewed,
            'amountOfComment' => $amountOfLike
        ));
    }
    /**
     * Push Cloud Message for user status.
     * @param array $aData
     */
    public function doPushCloudMessageUserStatus($aData)
    {
        /**
         * @var string
         */
        $sType = isset($aData['sType']) ? $aData['sType'] : '';

        /**
         * @var int
         */
        $iItemId = isset($aData['iItemId']) ? (int) $aData['iItemId'] : 0;

        /**
         * @var string In page only.
         */
        $sModule = isset($aData['sModule']) ? trim($aData['sModule']) : '';

        /**
         * @var int In page only.
         */
        $iItem = isset($aData['iItem']) ? (int) $aData['iItem'] : 0;
        /**
         * @var array
         */
        $aCallback = null;
        if (!empty($sModule))
        {
            if (Phpfox::hasCallback($sModule, 'getFeedDetails'))
            {
                $aCallback = Phpfox::callback($sModule . '.getFeedDetails', $iItem);
            }
        }
        /**
         * @var array
         */
        $aFeed = $this->database()
                ->select('*')
                ->from(Phpfox::getT((isset($aCallback['table_prefix']) ? $aCallback['table_prefix'] : '') . 'feed'))
                ->where('item_id =' . (int) $iItemId . ' AND type_id = "' . $sType . '"')
                ->execute('getSlaveRow');
        
        if (isset($aFeed['user_id']) && $aFeed['user_id'] != Phpfox::getUserId())
        {
            /**
             * @var int
             */
            $iPushId = Phpfox::getService('mfox.push')->savePush($aData, $aFeed['user_id']);
            // Push cloud message.
            Phpfox::getService('mfox.cloudmessage') -> send(array('message' => 'notification', 'iPushId' => $iPushId), $aFeed['user_id']);
        }
    }
    
    public function doFeedGetCommentNotificationFeed($aNotification)
	{
		$aRow = $this->database()->select('fc.feed_comment_id, u.user_id, fc.content, fc.parent_user_id, u.gender, u.user_name, u.full_name, u2.user_name AS parent_user_name')	
			->from(Phpfox::getT('feed_comment'), 'fc')
			->join(Phpfox::getT('user'), 'u', 'u.user_id = fc.user_id')
			->join(Phpfox::getT('user'), 'u2', 'u2.user_id = fc.parent_user_id')
			->where('fc.feed_comment_id = ' . (int) $aNotification['item_id'])
			->execute('getSlaveRow');
			
        if (!isset($aRow['feed_comment_id']))
        {
            return array();
        }
        
		$sUsers = Phpfox::getService('notification')->getUsers($aNotification);
		
		$sPhrase = '';
		if ($aNotification['user_id'] == $aRow['user_id'])
		{
			$sPhrase =  Phpfox::getPhrase('feed.users_commented_on_one_of_gender_wall_comments', array('users' => $sUsers, 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1)));
		}
		elseif ($aRow['user_id'] == Phpfox::getUserId())		
		{
			$aMentions = Phpfox::getService('user.process')->getIdFromMentions($aRow['content']);			
			$bUseDefault = true;
			foreach ($aMentions as $iKey => $iUser)
			{
				if ($iUser == $aRow['parent_user_id'])
				{
					$bUseDefault = false;
				}
			}
			if ($bUseDefault)
			{
				$sPhrase =  Phpfox::getPhrase('feed.users_commented_on_one_of_your_wall_comments', array('users' => $sUsers));
			}
			else
			{
				$sPhrase = $aRow['parent_user_name'] . ' commented on one of your status updates ';
			}
		}
		else 
		{
			$sPhrase =  Phpfox::getPhrase('feed.users_commented_on_one_of_span_class_drop_data_user_row_full_name_s_span_wall_comments', array('users' => $sUsers, 'row_full_name' => $aRow['full_name']));
		}
		
        /**
         * @var array
         */
        $aFeeds = $this->getfeed(array('comment-id' => $aRow['feed_comment_id']), $aRow['parent_user_id']);
        
        /**
         * @var array
         */
        $aLink = array();
        
        if (isset($aFeeds[0]['feed_id']))
        {
            $aLink = array('iFeedId' => $aFeeds[0]['feed_id'], 'sTypeId' => $aFeeds[0]['type_id'], 'iItemId' => $aFeeds[0]['item_id']);
        }
        else
        {
            return array();
        }
        
        return array(
			'link' => $aLink,
			'message' => ($sPhrase),
			'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog'),
            'sModule' => 'feed',
            'sMethod' => 'getCommentNotificationFeed'
		);
        
	}	

    /**
     * Input data:
     * + sItemType: string, required.
     * + iItemId: int, required.
     * + sContent: string optional.
     *
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     *
     */
    public function share($aData)
	{
        $sContent = isset($aData['sContent']) ? $aData['sContent'] : '';
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : '';
        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        $iPostType = isset($aData['iPostType']) ? (int) $aData['iPostType'] : 1;
        $sFriends = isset($aData['sFriends']) ? $aData['sFriends'] : '';

        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_share_this_action")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        if (Phpfox::getLib('parse.format')->isEmpty($sContent))
        {
            return array(
                'error_code' => 1,
                'result' => 0,
                'error_message' =>  Phpfox::getPhrase('user.add_some_text_to_share')
            );
        }

        //  share on wall (post_type = 1)
        switch ($sItemType) {
            case 'feed':
                $feed = Phpfox::getService('feed')->getFeed($iItemId);
                if(!isset($feed['feed_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $feed['item_id'];
                $parent_module_id = $feed['type_id'];
                break;
            
            case 'advancedphoto':
                $aItem = Phpfox::getService('advancedphoto')->getPhoto($iItemId, Phpfox::getUserId());
                if(!isset($aItem['photo_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['photo_id'];
                $parent_module_id = 'advancedphoto';
                break;
            case 'photo':
                $aItem = Phpfox::getService('photo')->getPhoto($iItemId, Phpfox::getUserId());
                if(!isset($aItem['photo_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['photo_id'];
                $parent_module_id = 'photo';
                break;
            case 'link':
                $aItem = Phpfox::getService('link')->getLinkById($iItemId);
                if(!isset($aItem['link_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['link_id'];
                $parent_module_id = 'link';
                break;
            case 'user_photo':
                $aItem = Phpfox::getService('photo')->getPhoto($iItemId, Phpfox::getUserId());
                if(!isset($aItem['photo_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['photo_id'];
                $parent_module_id = 'user_photo';
                break;
            case 'videochannel':
                $aItem = Phpfox::getService('videochannel')->getVideo($iItemId);
                if(!isset($aItem['video_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['video_id'];
                $parent_module_id = 'videochannel';
                break;
            
            case 'video':
                $aItem = Phpfox::getService('video')->getVideo($iItemId);
                if(!isset($aItem['video_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['video_id'];
                $parent_module_id = 'video';
                break;
            
            case 'fevent':
            case 'event':
                $aItem = Phpfox::getService('mfox.event')->isAdvancedModule()
                        ? Phpfox::getService('fevent')->getEvent($iItemId, true, true)
                        : Phpfox::getService('event')->getEvent($iItemId, true, true);

                if(!isset($aItem['event_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['event_id'];
                $parent_module_id = Phpfox::getService('mfox.event')->isAdvancedModule() ? 'fevent' : 'event';
                break;

            case 'pages': 
                $aItem = Phpfox::getService('pages')->getPage($iItemId);
                if(!isset($aItem['page_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['page_id'];
                $parent_module_id = 'pages';
                break;
            
            case 'music_song':
                $aItem = Phpfox::getService('music')->getSong($iItemId);
                if(!isset($aItem['song_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['song_id'];
                $parent_module_id = 'music_song';
                break;
			case 'musicsharing_song':
                $aItem = Phpfox::getService('musicsharing.music')->getSongFromId($iItemId);
                if(!isset($aItem['song_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['song_id'];
                $parent_module_id = 'musicsharing_song';
                break;
			case 'musicsharing_album':
            case 'musicsharing_pagesalbum':
                $aItem = Phpfox::getService('musicsharing.music')->getAlbumInfo($iItemId);
                if(!isset($aItem['album_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_album_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['album_id'];
                $parent_module_id = 'musicsharing_album';
                break;
            case 'music_album':
                $aItem = Phpfox::getService('music.album')->getAlbum($iItemId);
                if(!isset($aItem['album_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_album_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['album_id'];
                $parent_module_id = 'music_album';
                break;
            case 'quiz':
                $aItem = Phpfox::getService('quiz')->getQuizById($iItemId);
                $parent_feed_id = $aItem['quiz_id'];
                $parent_module_id = 'quiz';
                break;

            case 'blog':  
                $aItem = Phpfox::getService('blog')->getBlog($iItemId);
                if(!isset($aItem['blog_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['blog_id'];
                $parent_module_id = 'blog';
                break;

            case 'poll':  
                $aItem = Phpfox::getService('mfox.poll')->getPollById($iItemId);
                if(!isset($aItem['poll_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['poll_id'];
                $parent_module_id = 'poll';
                break;

            case 'forum':  
                return array(
                    'error_code' => 1,
                    'result' => 0,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_yet"))
                );
                break;

            case 'advancedmarketplace':
				   
            case 'marketplace':  
                $aItem = Phpfox::getService('mfox.marketplace')->isAdvancedModule()
                        ? Phpfox::getService('advancedmarketplace')->getForEdit($iItemId, true)
                        : Phpfox::getService('marketplace')->getForEdit($iItemId, true);
						
				
                if(!isset($aItem['listing_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }
                $parent_feed_id = $aItem['listing_id'];
                $parent_module_id = Phpfox::getService('mfox.marketplace')->isAdvancedModule() ? 'advancedmarketplace' : 'marketplace';
                break;

            case 'directory':  
                $aItem = Phpfox::getService('directory')->getBusinessById($iItemId);
                if(!isset($aItem['business_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['business_id'];
                $parent_module_id = 'directory';
                break;
            case 'ultimatevideo_video':
                $aItem = Phpfox::getService('ultimatevideo')->getVideo($iItemId);
                if(!isset($aItem['video_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['video_id'];
                $parent_module_id = 'ultimatevideo_video';
                break;
            case 'ultimatevideo_playlist':
                $aItem = Phpfox::getService('ultimatevideo.playlist')->getPlaylistById($iItemId);
                if(!isset($aItem['playlist_id'])){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_activity_not_found")),
                        'result' => 0
                    );
                }

                $parent_feed_id = $aItem['playlist_id'];
                $parent_module_id = 'ultimatevideo_playlist';
                break;

            default:
                return array(
                    'error_code' => 1,
                    'result' => 0,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_yet"))
                );
                break;
        }

		// followin issue with privacy is not 0.
		// https://jira.younetco.com/browse/FMOBI-1733
        // if (!$aItem || $aItem['privacy'] != 0)
		if (!$aItem)
        {
            return array(
                'error_code' => 1,
                'result' => 0,
                'message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_share_this_item_or_it_has_been_deleted"))
            );
        } 
        if ($iPostType == '2')
        {
            if (empty($sFriends))
            {
                return array(
                    'error_code' => 1,
                    'error_message' => html_entity_decode(Phpfox::getPhrase('mfox.select_a_friend_to_share_this_with'))
                );
            }
            else
            {
                $iCnt = 0;
                $aFriends = explode(',', $sFriends);
                foreach ($aFriends as $iFriendId)
                {
                    $aVals = array(
                        'user_status' => $sContent,
                        'parent_user_id' => $iFriendId,
                        'parent_feed_id' => $parent_feed_id,
                        'parent_module_id' => $parent_module_id
                    );
                    
                    if (Phpfox::getService('user.privacy')->hasAccess($iFriendId, 'feed.share_on_wall') && Phpfox::getUserParam('profile.can_post_comment_on_profile'))
                    {   
                        $iCnt++;
                        Phpfox::getService('feed.process')->addComment($aVals);
                    }               
                }           

                if (!$iCnt)
                {
                    return array(
                        'error_code' => 1,
                        'error_message' => html_entity_decode(Phpfox::getPhrase('user.unable_to_share_this_post_due_to_privacy_settings'))
                    );
                }

                return array(
                    'error_code' => 0,
                    'result' => 1,
                    'message' => html_entity_decode(Phpfox::getPhrase('feed.successfully_shared_this_item_on_your_friends_wall'))
                );
            }
        }

        $aVals = array(
            'user_status' => $sContent,
            'privacy' => '0',
            'privacy_comment' => '0',
            'parent_feed_id' => $parent_feed_id,
            'parent_module_id' => $parent_module_id
        );      

        if (($iId = Phpfox::getService('user.process')->updateStatus($aVals)))
        {
            return array(
                'result' => 1,
                'error_code' => 0,
                'error_message' => '', 
                'message' =>  Phpfox::getPhrase('feed.successfully_shared_this_item')
            );
        }

        return array(
            'error_code' => 1,
            'result' => 0,
            'error_message' => implode(', ', Phpfox_Error::get())
        );
	}

    // -----------------------------------------------------------
    // VERSION 3.03
    // -----------------------------------------------------------

    public function __get($aData){
        $result = array();
        $aRows = array();
        $sCond = '';

        $aFeedId = isset($aData['aFeedId'])? $aData['aFeedId']: null;

        $iItemId = isset($aData['iItemId']) ? (int)$aData['iItemId'] : 0;
        $sItemType = isset($aData['sItemType']) ? $aData['sItemType'] : null;
        $iMinId = isset($aData['iMinId']) ? (int)$aData['iMinId'] : null;
        $iMaxId = isset($aData['iMaxId']) ? (int)$aData['iMaxId'] : null;
        $iActionId = isset($aData['iActionId']) ? (int)$aData['iActionId'] : null;
        $iTotalFeeds = ((int) isset($aData['iAmountOfFeed']) && (int)$aData['iAmountOfFeed'] > 0) ? (int)$aData['iAmountOfFeed'] : Phpfox::getParam('feed.feed_display_limit');

        $iPage = isset($aData['iPage']) ? (int) $aData['iPage'] : 0;
        $sAction = isset($aData['sAction']) ? $aData['sAction'] : 'more';        
        $iUserId = isset($aData['iUserId']) ? (int) $aData['iUserId'] : null;        
        $sParentId = isset($aData['sParentId']) ?  $aData['sParentId'] : null;        

        $bIsGetOneFeed = isset($aData['bIsGetOneFeed']) ? $aData['bIsGetOneFeed'] : null;        

        /**
         * Fix for fevent type
         * @todo refactor
         */
        if ($sItemType == 'fevent') {
            $this->_oReq->set('event_module_id', 'fevent');
        }

        // check callback for event/pages
        if($sParentId == 'event' || $sParentId == 'fevent') { // know that it need 
            $sItemType = Phpfox::getService('mfox.event')->isAdvancedModule() ? 'fevent' : 'event';
            if($iActionId) {
                $iItemId = $iActionId;
            }
        } else if($sParentId == 'pages') { // know that it need 
            $sItemType = 'pages';
            if($iActionId) {
                $iItemId = $iActionId;
            }
            if (!defined('PHPFOX_IS_PAGES_VIEW')) {
                define('PHPFOX_IS_PAGES_VIEW', true);
            }
        }else if($sParentId == 'groups') { // know that it need
            $sItemType = 'groups';
            if($iActionId) {
                $iItemId = $iActionId;
            }
            if (!defined('PHPFOX_IS_GROUP_VIEW')) {
                define('PHPFOX_IS_GROUP_VIEW', true);
            }
        } else if($sParentId == 'directory') { // know that it need
            $sItemType = 'directory';
            if($iActionId) {
                $iItemId = $iActionId;
            }
        }

        $iPhpfoxUserId = Phpfox::getUserId();        
        $sUserField = Phpfox::getUserField();        
        $bIsUser = Phpfox::isUser();
        // $iOffset = (int) ($iPage * $iTotalFeeds);        
        $iOffset = 0; 
        $iLastTime = isset($aData['iLastTime']) ? (int) $aData['iLastTime'] : 0;        

        /**
         * Top Stories: time_update
         * Most Recent: time_stamp
         * by default, search by time stamp
         * Fox default is to search by time_update, however, client side is hard coded by SE rules, so we make time_stamp default
         */
        $sOrder = isset($aData['sOrder']) && $aData['sOrder'] == 'time_update' ? 'feed.time_update DESC' : 'feed.time_stamp DESC';

        if (isset($aData['sOrder']) && $aData['sOrder'] == 'time_stamp')
        {
            if ($sAction == 'new')
            {
                $sCond = $iLastTime > 0 ? ' AND feed.time_stamp > \'' . $iLastTime . '\'' : '';
            }
            else
            {
                $sCond = $iLastTime > 0 ? ' AND feed.time_stamp < \'' . $iLastTime . '\'' : '';
            }
        }
        else
        {
            if ($sAction == 'new')
            {
                $sCond = $iLastTime > 0 ? ' AND feed.time_update > \'' . $iLastTime . '\'' : '';
            }
            else
            {
                $sCond = $iLastTime > 0 ? ' AND feed.time_update < \'' . $iLastTime . '\'' : '';
            }
        }
        /**
         * @todo Remove this when support all feed types
         */
        $advancedphoto = ", 'photo', 'photo_tag' ";
        if(Phpfox::getService('mfox.photo')->isAdvancedModule()){
            $advancedphoto = ", 'photo', 'photo_tag', 'advancedphoto', 'advancedphoto_album_tag', 'advancedphoto_tag' ";
        }
        
        $fevent = ", 'event', 'event_comment'";
        if(Phpfox::getService('mfox.event')->isAdvancedModule()){
            $fevent = ", 'fevent', 'fevent_comment'";
        }

        $marketplace = ", 'marketplace'";
        if(Phpfox::getService('mfox.marketplace')->isAdvancedModule()){
            $marketplace = ", 'advancedmarketplace'";
        }
		
		$music = " , 'music_album', 'music_song'";
		if(Phpfox::getService('mfox.song')->isAdvancedModule()){
            $music = ", 'musicsharing_album', 'musicsharing_pagesalbum'";
        } 

        $videochannel = ", 'videochannel'";
		
        $sCond .= " AND feed.type_id IN (
            'user_status', 'user_photo'
            , 'feed_comment'
            , 'blog', 'comment_relation'
            , 'link'
            , 'poll'
            , 'quiz'
            , 'pages', 'pages_comment','pages_itemLiked'
            , 'groups', 'groups_comment','groups_itemLiked'
            , 'forum', 'forum_post', 'forum_reply'
            , 'custom' 
            , 'directory'
            , 'directory_checkinhere'
            , 'directory_comment'
            , 'ultimatevideo_video'
            , 'ultimatevideo_playlist'
            "
            . $music
            . $advancedphoto
            . $fevent
            . $videochannel
            . $marketplace
            . "
            )";

        $sParentModuleCond .= " AND (feed.parent_module_id IS NULL OR feed.parent_module_id = '' OR feed.parent_module_id IN (" . implode(',', $this->_getSupportedParentModuleIds()) . "))";

        if(null !== $iMinId){
            //  get new feeds
            $sCond .= " AND feed.feed_id >= " . (int)$iMinId . ' '; // min is inclusive
        } else if(null !== $iMaxId && $iMaxId >= 0){
            //  get old feeds
            $sCond .= " AND feed.feed_id <= " . (int)$iMaxId . ' '; // max is inclusive
        }
        if(null !== $iActionId){
            $sCond .= ' AND feed.feed_id =' . (int) $iActionId . ' ';
        }

        $aCond = array();
        if (isset($this->_aCallback['module']))
        {
            $aNewCond = array();

            $aNewCond[] = 'AND feed.parent_user_id = ' . (int) $this->_aCallback['item_id'];
            if ($iUserId !== null)
            {
                $aNewCond[] = 'AND feed.user_id = ' . (int) $iUserId;
            }
            $aNewCond[] = $sCond;

            $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
                    ->from(Phpfox::getT($this->_aCallback['table_prefix'] . 'feed'), 'feed')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                    ->where($aNewCond)
                    ->order($sOrder)
                    ->limit($iOffset, $iTotalFeeds)
                    ->execute('getSlaveRows');
        } else if($iItemId > 0 && null !== $sItemType){
            //  get feed of event, pages
            if ($sItemType == 'user')
            {
                $iUserId = $iItemId;
                //  get feed by userID
                if (!Phpfox::getService('user.privacy')->hasAccess($iUserId, 'feed.view_wall'))
                {
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_feed_on_wall"))
                    );
                }

                $aCond[] = $sCond;

                if ($iUserId == Phpfox::getUserId())
                {
                    $aCond[] = 'AND feed.privacy IN(0,1,2,3,4)';
                }
                else
                {
                    if (Phpfox::getService('friend')->isFriend($iUserId, Phpfox::getUserId()))
                    {
                        $aCond[] = 'AND feed.privacy IN(0,1,2)';
                    }
                    else if (Phpfox::getService('friend')->isFriendOfFriend($iUserId, Phpfox::getUserId()))
                    {
                        $aCond[] = 'AND feed.privacy IN(0,2)';
                    }
                    else
                    {
                        $aCond[] = 'AND feed.privacy IN(0)';
                    }
                }
				$aCond[] = 'AND (feed.parent_user_id = 0 OR feed.parent_user_id = '.$iUserId.')';


                $this->database()->select('feed.*')
                        ->from(Phpfox::getT('feed'), 'feed')
                        ->where(array_merge($aCond, array('AND type_id = \'feed_comment\' AND feed.user_id = ' . (int) $iUserId . '')))
                        ->union();

                $this->database()->select('feed.*')
                        ->from(Phpfox::getT('feed'), 'feed')
                        ->where(array_merge($aCond, array('AND feed.user_id = ' . (int) $iUserId . ' AND feed.parent_user_id = 0')))
                        ->union();

                if (Phpfox::isUser())
                {
                    if (Phpfox::isModule('privacy'))
                    {
                        $this->database()->join(Phpfox::getT('privacy'), 'p', 'p.module_id = feed.type_id AND p.item_id = feed.item_id')
                                ->join(Phpfox::getT('friend_list_data'), 'fld', 'fld.list_id = p.friend_list_id AND fld.friend_user_id = ' . Phpfox::getUserId() . '');
                    }
                    $this->database()->select('feed.*')
                            ->from(Phpfox::getT('feed'), 'feed')
                            ->where('feed.privacy IN(4) AND feed.user_id = ' . (int) $iUserId . ' AND feed.feed_reference = 0' . $sCond)
                            ->union();
                }

                $this->database()->select('feed.*')
                        ->from(Phpfox::getT('feed'), 'feed')
                        ->where(array_merge($aCond, array('AND feed.parent_user_id = ' . (int) $iUserId)))
                        ->union();

                $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField())
                        ->unionFrom('feed')
                        ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                        ->where(' TRUE ' . $sCond)
                        ->order('feed.time_stamp DESC')
                        ->group('feed.feed_id')
                        ->limit($iOffset, $iTotalFeeds)
                        ->execute('getSlaveRows');
            } else if ($sItemType == 'pages'){
                //  set callback config 
                if($bIsGetOneFeed) {
                    $aNewCond = array();

                    $aNewCond[] = 'AND feed.feed_id = ' . $iActionId;

                    $table = Phpfox::getT('pages_feed');
                    $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
                            ->from($table, 'feed')
                            ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                            ->where($aNewCond)
                            ->order($sOrder)
                            ->execute('getSlaveRows');
                } else {
                    $ret = $this->__setCallbackConfigForPages($iItemId);
                    if(true !== $ret){
                        return $ret;
                    }

                    $aNewCond = array();
                    $aNewCond[] = 'AND feed.parent_user_id = ' . (int) $this->_aCallback['item_id'];
                    if ($iUserId !== null)
                    {
                        $aNewCond[] = 'AND feed.user_id = ' . (int) $iUserId;
                    }
                    $aNewCond[] = $sCond;

                    $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
                            ->from(Phpfox::getT($this->_aCallback['table_prefix'] . 'feed'), 'feed')
                            ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                            ->where($aNewCond)
                            ->order($sOrder)
                            ->limit($iOffset, $iTotalFeeds)
                            ->execute('getSlaveRows');

                    // Fixes missing page_user_id, required to create the proper feed target
                    $aPage = Phpfox::getService('pages')->getForView((int) $this->_aCallback['item_id']);
                    foreach($aRows as $iKey => $aValue)
                    {
                        $aRows[$iKey]['page_user_id'] = (int) $aPage['page_user_id'];
                    }
                }
            } else if ($sItemType == 'groups'){
                //  set callback config
                if($bIsGetOneFeed) {
                    $aNewCond = array();

                    $aNewCond[] = 'AND feed.feed_id = ' . $iActionId;

                    $table = Phpfox::getT('pages_feed');
                    $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
                        ->from($table, 'feed')
                        ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                        ->where($aNewCond)
                        ->order($sOrder)
                        ->execute('getSlaveRows');
                } else {
                    $ret = $this->__setCallbackConfigForGroups($iItemId);

                    if(true !== $ret){
                        return $ret;
                    }

                    $aNewCond = array();
                    $aNewCond[] = 'AND feed.parent_user_id = ' . (int) $this->_aCallback['item_id'];
                    if ($iUserId !== null)
                    {
                        $aNewCond[] = 'AND feed.user_id = ' . (int) $iUserId;
                    }
                    $aNewCond[] = $sCond;

                    $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
                        ->from(Phpfox::getT($this->_aCallback['table_prefix'] . 'feed'), 'feed')
                        ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                        ->where($aNewCond)
                        ->order($sOrder)
                        ->limit($iOffset, $iTotalFeeds)
                        ->execute('getSlaveRows');

                    // Fixes missing page_user_id, required to create the proper feed target
                    $aPage = Phpfox::getService('groups')->getForView((int) $this->_aCallback['item_id']);
                    foreach($aRows as $iKey => $aValue)
                    {
                        $aRows[$iKey]['page_user_id'] = (int) $aPage['page_user_id'];
                    }
                }
            }
            else if ($sItemType == 'event' || $sItemType == 'fevent'){
                //  set callback config 
                if($bIsGetOneFeed) {
                    $aNewCond = array();

                    $aNewCond[] = 'AND feed.feed_id = ' . $iActionId;

                    $eventTable = Phpfox::getService('mfox.event')->isAdvancedModule() ? Phpfox::getT('fevent_feed') : Phpfox::getT('event_feed');
                    $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
                            ->from($eventTable, 'feed')
                            ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                            ->where($aNewCond)
                            ->order($sOrder)
                            ->execute('getSlaveRows');
                } else {
                    $ret = $this->__setCallbackConfigForEvent($iItemId);
                    if(true !== $ret){
                        return $ret;
                    }

                    $aNewCond = array();

                    $aNewCond[] = 'AND feed.parent_user_id = ' . (int) $this->_aCallback['item_id'];
                    if ($iUserId !== null)
                    {
                        $aNewCond[] = 'AND feed.user_id = ' . (int) $iUserId;
                    }
                    $aNewCond[] = $sCond;

                    $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
                            ->from(Phpfox::getT($this->_aCallback['table_prefix'] . 'feed'), 'feed')
                            ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                            ->where($aNewCond)
                            ->order($sOrder)
                            ->limit($iOffset, $iTotalFeeds)
                            ->execute('getSlaveRows');
                }
            }
            else if ($sItemType == 'directory')
            {
                if ($bIsGetOneFeed)
                {
                    $aNewCond = array();

                    $aNewCond[] = 'AND feed.feed_id = ' . $iActionId;

                    $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
                        ->from(Phpfox::getT('directory_feed'), 'feed')
                        ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                        ->where($aNewCond)
                        ->execute('getSlaveRows');
                }
                else if ($this->__setCallbackConfigForDirectory($iItemId))
                {
                    $aNewCond = array();

                    if ($bIsGetOneFeed)
                    {
                        $aNewCond[] = 'AND feed.feed_id = ' . $iActionId;
                    }

                    $aNewCond[] = 'AND feed.parent_user_id = ' . (int) $this->_aCallback['item_id'];
                    $aNewCond[] = $sCond;

                    $aRows = $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
                        ->from(Phpfox::getT($this->_aCallback['table_prefix'] . 'feed'), 'feed')
                        ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                        ->where($aNewCond)
                        ->order($sOrder)
                        ->limit($iOffset, $iTotalFeeds)
                        ->execute('getSlaveRows');
                }
            }            
        } else if(null != $aFeedId){
            foreach($this->__getFeedByIdList($aFeedId) as $aRow){
                $aRows[]=  $aRow;
            }
        }else if(null !== $iActionId){
            //  get specific feed with ID
            $aRows[] = $this->__getFeedByID((int)$iActionId);
        } else {
            //  get latest feed
            $sCond .= $sParentModuleCond;

            // Users must be active within 7 days or we skip their activity feed
            $iLastActiveTimeStamp = ((int) Phpfox::getParam('feed.feed_limit_days') <= 0 ? 0 : (PHPFOX_TIME - (86400 * Phpfox::getParam('feed.feed_limit_days'))));         
            
            if (Phpfox::isModule('privacy') && Phpfox::getUserParam('privacy.can_view_all_items') && isset($aData['view-all']))
            {
                $this->_hashSearch($aData['sHashTag']);

                $aRows = $this->database()->select('feed.*, f.friend_id AS is_friend, ' . $sUserField)
                        ->from(Phpfox::getT('feed'), 'feed')
                        ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                        ->leftJoin(Phpfox::getT('friend'), 'f', 'f.user_id = feed.user_id AND f.friend_user_id = ' . $iPhpfoxUserId)
                        ->order($sOrder)
                        ->group('feed.feed_id')
                        ->limit($iOffset, $iTotalFeeds)
                        ->where('feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0' . $sCond)
                        ->execute('getSlaveRows');
            }
            else
            {
                if (Phpfox::getParam('feed.feed_only_friends'))
                {
                    // Get my friends feeds
                    $this->database()->select('feed.*')
                            ->from(Phpfox::getT('feed'), 'feed')
                            ->join(Phpfox::getT('friend'), 'f', 'f.user_id = feed.user_id AND f.friend_user_id = ' . $iPhpfoxUserId)
                            ->where('feed.privacy IN(0,1,2) AND feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0 ' . $sCond)
                            ->union();

                    // Get my feeds
                    $this->database()->select('feed.*')
                            ->from(Phpfox::getT('feed'), 'feed')
                            ->where('feed.privacy IN(0,1,2,3,4) AND feed.user_id = ' . Phpfox::getUserId() . ' AND feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0 ' . $sCond)
                            ->union();
                }
                else
                {
                    // Get my friends feeds
                    $this->database()->select('feed.*')
                            ->from(Phpfox::getT('feed'), 'feed')
                            ->join(Phpfox::getT('friend'), 'f', 'f.user_id = feed.user_id AND f.friend_user_id = ' . $iPhpfoxUserId)
                            ->where('feed.privacy IN(1,2) AND feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0 ' . $sCond)
                            ->union();

                    // Get my friends of friends feeds
                    $this->database()->select('feed.*')
                            ->from(Phpfox::getT('feed'), 'feed')
                            ->join(Phpfox::getT('friend'), 'f1', 'f1.user_id = feed.user_id')
                            ->join(Phpfox::getT('friend'), 'f2', 'f2.user_id = ' . Phpfox::getUserId() . ' AND f2.friend_user_id = f1.friend_user_id')
                            ->where('feed.privacy IN(2) AND feed.time_stamp > \'' . $iLastActiveTimeStamp .  '\' AND feed.feed_reference = 0 ' . $sCond)
                            ->union();

                    // Get my feeds
                    $this->database()->select('feed.*')
                            ->from(Phpfox::getT('feed'), 'feed')
                            ->where('feed.privacy IN(1,2,3,4) AND feed.user_id = ' . Phpfox::getUserId() . ' AND feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0 ' . $sCond )
                            ->union();

                    // Get public feeds
                    $this->database()->select('feed.*')
                            ->from(Phpfox::getT('feed'), 'feed')
                            ->where('feed.privacy IN(0) AND feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0 ' . $sCond)
                            ->union();
                    
                    if (Phpfox::isModule('privacy'))
                    {
                        $this->database()->join(Phpfox::getT('privacy'), 'p', 'p.module_id = feed.type_id AND p.item_id = feed.item_id')
                            ->join(Phpfox::getT('friend_list_data'), 'fld', 'fld.list_id = p.friend_list_id AND fld.friend_user_id = ' . Phpfox::getUserId() . '');
                    }

                    // Get feeds based on custom friends lists
                    $this->database()->select('feed.*')
                            ->from(Phpfox::getT('feed'), 'feed')
                            ->where('feed.privacy IN(4) AND feed.time_stamp > \'' . $iLastActiveTimeStamp . '\' AND feed.feed_reference = 0 ' . $sCond)
                            ->union();
                }

                $this->_hashSearch($aData['sHashTag']);
                
                $aRows = $this->database()->select('feed.*, f.friend_id AS is_friend, u.view_id,  ' . $sUserField)
                        ->unionFrom('feed')
                        ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
                        ->leftJoin(Phpfox::getT('friend'), 'f', 'f.user_id = feed.user_id AND f.friend_user_id = ' . $iPhpfoxUserId)
                        ->where(' TRUE ' . $sCond)
                        ->order($sOrder)
                        ->group('feed.feed_id')
                        ->limit($iOffset, $iTotalFeeds)
                        ->execute('getSlaveRows');
            }
        }

        $bFirstCheckOnComments = false;
        if (Phpfox::getParam('feed.allow_comments_on_feeds') && $bIsUser && Phpfox::isModule('comment'))
        {
            $bFirstCheckOnComments = true;
        }

        $aFeedLoop = $aRows;
        $aFeeds = array();
        $aParentFeeds = array();
        if (Phpfox::isModule('like'))
        {
            $oLike = Phpfox::getService('like');
        }
		
		$bIsOwner = false;
		if($aData['sItemType'] == 'user' && $aData['iItemId']){
			$bIsOwner  = Phpfox::getUserId() == $aData['iItemId'];
		}else if ($aData['sItemType'] == 'pages' && $aData['iItemId']){
			$bIsOwner  =  Phpfox::getService('pages')->isAdmin($aData['iItemId']);
		}else if ($aData['sItemType'] == 'groups' && $aData['iItemId']){
			$bIsOwner  =  Phpfox::getService('groups')->isAdmin($aData['iItemId']);
		}else if ($aData['sItemType'] == 'directory' && $aData['iItemId']){

		}else if ($aData['sItemType']== 'event'){
			
		}else if ($aData['sItemType'] == 'fevent'){
			
		}

        foreach ($aFeedLoop as $sKey => $aRow)
        {
            $aRow['feed_time_stamp'] = $aRow['time_stamp'];
            if (($aReturn = $this->_processFeed($aRow, $sKey, $iUserId, $bFirstCheckOnComments)))
            {
                if (isset($aReturn['force_user']))
                {
                    $aReturn['user_name'] = $aReturn['force_user']['user_name'];
                    $aReturn['full_name'] = $aReturn['force_user']['full_name'];
                    $aReturn['user_image'] = $aReturn['force_user']['user_image'];
                    $aReturn['server_id'] = $aReturn['force_user']['server_id'];
                }

                $aReturn['feed_month_year'] = date('m_Y', $aRow['feed_time_stamp']);
                $aReturn['feed_time_stamp'] = $aRow['feed_time_stamp'];
                $aReturn['parent_user_id'] = $aRow['parent_user_id'];

                $aFeeds[] = $aReturn;
            }

            // Show the feed properly. If user A posted on page 1, then feed will say "user A > page 1 posted ..."
            if($sParentId == 'pages')
            {
                // If defined parent user, and the parent user is not the same page (logged in as a page)
                if (isset($aRow['page_user_id']) && $aReturn['page_user_id'] != $aReturn['user_id'])
                {
                    $aParentFeeds[$aReturn['feed_id']] = $aRow['page_user_id'];
                }
            }
            elseif (isset($aRow['parent_user_id']) && !isset($aRow['parent_user']) && $aRow['type_id'] != 'friend')
            {
                $aParentFeeds[$aRow['feed_id']] = $aRow['parent_user_id'];
            }            
        }

        if (count($aFeeds) == 0)
        {
            return $aFeeds;
        }
        
        // Get the parents for the feeds so it displays arrow.png 
        if (!empty($aParentFeeds))
        {
            $aParentUsers = $this->database()->select(Phpfox::getUserField())
                ->from(Phpfox::getT('user'), 'u')
                ->where('user_id IN (' . implode(',',array_values($aParentFeeds)) . ')')
                ->execute('getSlaveRows');
            
            $aFeedsWithParents = array_keys($aParentFeeds);
        }
        foreach ($aFeeds as $aElement)
        {
            $sUserProfileImg_Url = Phpfox::getService('mfox.user')->getImageUrl($aElement, '_250_square');
            
            $aTemp = array(
                'iActionId' => $aElement['feed_id']
                , 'iUserId' => $aElement['user_id']
                , 'sUsername' => $aElement['user_name']
                , 'UserProfileImg_Url' => $sUserProfileImg_Url
                , 'sFullName' => $aElement['full_name']
                , 'bCanPostComment' => true
                , 'bCanLike' => true
                , 'bCanReport' => true
                , 'bCanShare' => true
                , 'sTime' => date('l, F j, o', (int) $aElement['time_stamp']) . ' at ' . date('h:i a', (int) $aElement['time_stamp'])
                , 'sTimeConverted' => Phpfox::getLib('date')->convertTime($aElement['time_stamp'], 'comment.comment_time_stamp')
                , 'iTimestamp' => $aElement['time_stamp']
                , 'sActionType' => $aElement['type_id']
                , 'iItemId' => $aElement['item_id']
                , 'sItemTitle' => $aElement['full_name']
                , 'sItemType' => $aElement['type_id']
                , 'bIsLike' => ''
                , 'iTotalLike' => ''
                , 'aUserLike' => array()
                , 'iTotalComment' => $aElement['total_comment']
                , 'sContent' => ''
                , 'sParams' => ''
                , 'bReadMore' => ''
                , 'aAttachments' => array()
                , 'bIsShare' => true
                , 'bCanDelete' => ($aElement['user_id'] == Phpfox::getUserId() || $bIsOwner ),
            );

            if ($sParentId == 'event' || $sParentId == 'fevent')
            {
                if (Phpfox::getService('mfox.' . $sParentId)->isOwner($aElement['parent_user_id']))
                {
                    $aTemp['bCanDelete'] = true;
                }
            }
            
            // since 3.08p2

            $aPhotoLocation = array();

            $aTemp['aParentFeed'] = array();

            // Using "parent_module_id" and "parent_feed_id" to check the share feed.
            if($aElement['parent_module_id']) {
                switch ($aElement['parent_module_id']) {
                    case 'advancedphoto':
                        $aParentFeed = Phpfox::getService('mfox.advancedphoto')->doPhotoGetActivityFeed(array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), null, true);
						
                        if ($aParentFeed)
                        {
    						if($aParentFeed['sFeedImage']){
    							$aParentFeed['sType'] = 'advancedphoto';
    	                        $aParentFeed['sPhoto_Url'] = $aParentFeed['sFeedImage'];	
    						}else{
    							$aParentFeed['sType'] = 'deleted';	
    						}
    						
    						if($aParentFeed['iAlbumId']){
    							$aTemp['sActionType'] =  'advancedphoto_album';
    						}
                        }
                        break;
                    case 'photo':
                        $aParentFeed = Phpfox::getService('mfox.photo')->doPhotoGetActivityFeed(array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), null, true);

                        if ($aParentFeed)
                        {
    						if($aParentFeed['sFeedImage']){
    							$aParentFeed['sType'] = 'photo';
    	                        $aParentFeed['sPhoto_Url'] = $aParentFeed['sFeedImage'];	
    						}else{
    							$aParentFeed['sType'] = 'deleted';	
    						}
                        }
                        break;
                    case 'user_photo':
                        $aParentFeed = Phpfox::getService('mfox.photo')->doPhotoGetActivityFeed(array(
                            'feed_id' => $aElement['parent_feed_id'],
                            'item_id' => $aElement['parent_feed_id']
                        ), null, true);

                        if ($aParentFeed)
                        {
                            if($aParentFeed['sFeedImage']){
                                $aParentFeed['sType'] = 'photo';
                                $aParentFeed['sPhoto_Url'] = $aParentFeed['sFeedImage'];
                            }else{
                                $aParentFeed['sType'] = 'deleted';
                            }
                        }
                        break;
                    case 'fevent':
                    case 'event':
                        $aParentFeed = Phpfox::getService('mfox.event')->doEventGetActivityFeed(array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), null, true);
                        
                        if ($aParentFeed)
                        {
                            $aEvent = Phpfox::getService('mfox.event')->isAdvancedModule()
                                    ? Phpfox::getService('fevent')->getEvent($aElement['parent_feed_id'], false, true)
                                    : Phpfox::getService('event')->getEvent($aElement['parent_feed_id'], false, true);
                            $aParentFeed['sType'] = Phpfox::getService('mfox.event')->isAdvancedModule() ? 'fevent' : 'event';
                            $aParentFeed['iId'] = $aElement['parent_feed_id'];
                            $aParentFeed['sTitle'] =  $aParentFeed['sFeedTitle'];
                            $aParentFeed['iTotalGuest'] = Phpfox::getService('mfox.helper.event')->getNumberOfGuestOfEvent($aElement['parent_feed_id']);
                            $aParentFeed['sLocation'] = $aEvent['location'];
                        }
                        break;
                    case 'musicsharing_song':
                        $aTmpParentFeed = Phpfox::getService('mfox.musicsharing.song')->detail(array(
                            'iSongId' => $aElement['parent_feed_id'])
                        );
                        if (!empty($aTmpParentFeed['iSongId']))
                        {
                            $aParentFeed['iId'] = $aTmpParentFeed['iSongId'];
                            $aParentFeed['sType'] =$aTmpParentFeed['sModelType'];
                            $aParentFeed['sTitle'] = $aTmpParentFeed['sTitle'];
                            $aParentFeed['sDescription'] = $aTmpParentFeed['sDescription'];
                            $aParentFeed['sPhoto_Url'] = $aTmpParentFeed['sAlbumImage'];
                            $aParentFeed['sUserName'] =$aTmpParentFeed['sUsername'];
                            $aParentFeed['sFullName'] =  $aTmpParentFeed['sFullname'];
                            $aParentFeed['sFeedTitle'] = $aTmpParentFeed['sTitle'];
                            $aParentFeed['iUserId'] =  $aTmpParentFeed['iUserId'];
                        }
                        break;
					case 'musicsharing_album':
                    case 'musicsharing_pagesalbum':
						$aParentFeed = Phpfox::getService('mfox.musicsharing.album')->getAlbumSummary($aElement['parent_feed_id']);
						
                        if ($aParentFeed){
                            $aParentFeed['iId'] = $aParentFeed['iAlbumId'];
                            $aParentFeed['sType'] =$aParentFeed['sModelType'];
                            $aParentFeed['sTitle'] = $aParentFeed['sName'];
                            $aParentFeed['sDescription'] = $aParentFeed['sDescription'];
                            $aParentFeed['sPhoto_Url'] = $aParentFeed['sImagePath'];
							$aParentFeed['sUserName'] =$aParentFeed['sUsername'];
							$aParentFeed['sFullName'] =  $aParentFeed['sFullname'];
							$aParentFeed['sFeedTitle'] = $aParentFeed['sName'];
							$aParentFeed['iUserId'] =  $aParentFeed['iUserId'];
                        }   
                    	break;
                    case 'music_song':
                    case 'music_album':
                        $aParentFeed = Phpfox::getService('mfox.song')->doSongGetActivityFeedSong(array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), false, true);

                        if ($aParentFeed)
                        {
                            $aParentFeed['iId'] = $aElement['parent_feed_id'];
                            $aParentFeed['sType'] = 'music';
                            $aParentFeed['sTitle'] = $aParentFeed['sFeedTitle'];
                            $aParentFeed['sDescription'] = $aParentFeed['sFeedContent'];
                            $aParentFeed['sPhoto_Url'] = Phpfox::getService('mfox.song')->getDefaultImageSongPath();
                        }
                        break;
                    case 'blog':
                        // this case happens when object is shared feed (share blog in wall)
                        if (Phpfox::hasCallback($aElement['parent_module_id'], 'getActivityFeed')){
                            $aParentFeed = Phpfox::getService('mfox.blog')->getActivityFeed(array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), (isset($this->_aCallback['module']) ? $this->_aCallback : null),true);
                        }

                        if ($aParentFeed)
                        {
                            $aParentFeed['iId'] = $aElement['parent_feed_id'];
                            $aParentFeed['sType'] =$aParentFeed['comment_type_id'];
                            $aParentFeed['sTitle'] = $aParentFeed['feed_title'];
                            $aParentFeed['sDescription'] = $aParentFeed['feed_content'];
                            $aParentFeed['sPhoto_Url'] = $aParentFeed['feed_icon'];
							$aParentFeed['sUserName'] =$aParentFeed['user_name'];
							$aParentFeed['sFullName'] =  $aParentFeed['full_name'];
							$aParentFeed['sFeedTitle'] = $aParentFeed['feed_title'];
							$aParentFeed['iUserId'] =  $aParentFeed['user_id'];
                        }   
                        break;
					case 'quiz':
						// this case happens when object is shared feed (share blog in wall)
                        if (Phpfox::hasCallback($aElement['parent_module_id'], 'getActivityFeed')){
                            $aParentFeed = Phpfox::callback($aElement['parent_module_id'] . '.getActivityFeed', array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), (isset($this->_aCallback['module']) ? $this->_aCallback : null), true);
						}

                        if ($aParentFeed)
                        {
							$sImageUrl = "";
							
							if ($aParentFeed['image_path']){
								$sImageUrl = Phpfox::getLib('image.helper') -> display(array(
				                'server_id' => $aParentFeed['server_id'],
				                'path' => 'quiz.url_image',
				                'file' => $aParentFeed['image_path'],
				                'suffix' => '',
				                'return_url' => true
				            ));
							}else{
								$sImageUrl =  Phpfox::getService('mfox.quiz')->_getDefaultQuizImagePath();
							}
							
							
                            $aParentFeed['iId'] = $aElement['parent_feed_id'];
                            $aParentFeed['sType'] =$aParentFeed['comment_type_id'];
                            $aParentFeed['sTitle'] = $aParentFeed['feed_title'];
                            $aParentFeed['sDescription'] = $aParentFeed['feed_content'];
                            $aParentFeed['sPhoto_Url'] = $sImageUrl;
							$aParentFeed['sUserName'] =$aParentFeed['user_name'];
							$aParentFeed['sFullName'] =  $aParentFeed['full_name'];
							$aParentFeed['sFeedTitle'] = $aParentFeed['feed_title'];
							$aParentFeed['iUserId'] =  $aParentFeed['user_id'];
                        }   
                        break;
                    // case 'forum':
                    //     // not support yet
                    //     break;
                    case 'poll':
                        // this case happens when object is shared feed (share poll in wall)
                        if (Phpfox::hasCallback($aElement['parent_module_id'], 'getActivityFeed')){
                            $aParentFeed = Phpfox::callback($aElement['parent_module_id'] . '.getActivityFeed', array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), (isset($this->_aCallback['module']) ? $this->_aCallback : null), true);
						}

                        if ($aParentFeed)
                        {
							$sImageUrl = "";
							
							if ($aParentFeed['image_path']){
								$sImageUrl = Phpfox::getLib('image.helper') -> display(array(
				                'server_id' => $aParentFeed['server_id'],
				                'path' => 'poll.url_image',
				                'file' => $aParentFeed['image_path'],
				                'suffix' => '',
				                'return_url' => true
				            ));
							}else{
								$sImageUrl =  Phpfox::getService('mfox.poll')->getDefaultImagePollPath();
							}

                            $aParentFeed['iId'] = $aElement['parent_feed_id'];
                            $aParentFeed['sType'] = 'poll';
                            $aParentFeed['sTitle'] = $aParentFeed['feed_title'];
                            $aParentFeed['sDescription'] = '';
							$aParentFeed['sPhoto_Url'] = $sImageUrl;
							
							$aPoll = Phpfox::getService('poll')->getPollById($aElement['item_id']);
							
							
							$aParentFeed['bHasImage'] = $aPoll['image_path']?1:0;
		                    $aParentFeed['iTotalVotes'] = intval($aPoll['total_votes']);
		              		
							
							$aParentFeed['sUserName'] =$aParentFeed['user_name'];
							$aParentFeed['sFullName'] =  $aParentFeed['full_name'];
							$aParentFeed['sFeedTitle'] = $aParentFeed['feed_title'];
							$aParentFeed['iUserId'] =  $aParentFeed['user_id'];
                        }

                        break;
                    case 'advancedmarketplace':
						$sType =  'advancedmarketplace';
						
                        if (Phpfox::hasCallback($aElement['parent_module_id'], 'getActivityFeed')){
                            $aParentFeed = Phpfox::callback($aElement['parent_module_id'] . '.getActivityFeed', array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), (isset($this->_aCallback['module']) ? $this->_aCallback : null), true);
						}

                        if ($aParentFeed)
                        {
                            $aParentFeed['iId'] = $aElement['parent_feed_id'];
                            $aParentFeed['sType'] = $sType;
                            $aParentFeed['sTitle'] = $aParentFeed['feed_title'];
                            $aParentFeed['sDescription'] = $aParentFeed['feed_content'];
                            
							
							$aParentFeed['sUserName'] =$aParentFeed['user_name'];
							$aParentFeed['sFullName'] =  $aParentFeed['full_name'];
							$aParentFeed['sFeedTitle'] = $aParentFeed['feed_title'];
							$aParentFeed['iUserId'] =  $aParentFeed['user_id'];
							
							$aItem = Phpfox::getService('mfox.advancedmarketplace')->getListing($aElement['parent_feed_id']);
	                    	
							if ($aItem['image_path']){
								$sImageUrl = Phpfox::getLib('image.helper')->display(array(
					                'server_id' => $aItem['server_id'],
					                'path' => 'core.url_pic',
					                'file' => $sType. '/' . $aItem['image_path'],
					                'suffix' => '',
					                'return_url' => true
					                    )
					            );	
							}else{
								$sImageUrl = Phpfox::getService('mfox.marketplace')->getDefaultImagePath();
							}
							
							$aParentFeed['sPhoto_Url'] = $sImageUrl;
						
							$aParentFeed['sLocation'] =  isset($aItem['location']) ? $aItem['location'] : '';
							$aParentFeed['sCity'] = $aItem['city'];
							$aParentFeed['iCountryChildId'] = $aItem['country_child_id'];
							$aParentFeed['sCountryIso'] = $aItem['listing_country_iso'];
							$aParentFeed['sCountryChild'] = Phpfox::getService('core.country')->getChild($aItem['country_child_id']);
							$aParentFeed['sCountry'] = Phpfox::getService('core.country')->getCountry($aItem['listing_country_iso']);
							$aParentFeed['sCurrencyId'] = $aItem['currency_id'];
							$aParentFeed['sSymbol'] = Phpfox::getService('core.currency')->getSymbol($aItem['currency_id']);
							$aParentFeed['sPostalCode'] = $aItem['postal_code'];
							$aParentFeed['sPrice'] = $aItem['price'];
							$aParentFeed['aCategoriesData'] = $aItem['categories'];
							$aParentFeed['bHasImage'] = $aItem['image_path']?1:0;
							
                        }   
                        break;
                    case 'marketplace':
						$sType =  'marketplace';
						
                        if (Phpfox::hasCallback($aElement['parent_module_id'], 'getActivityFeed')){
                            $aParentFeed = Phpfox::callback($aElement['parent_module_id'] . '.getActivityFeed', array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), (isset($this->_aCallback['module']) ? $this->_aCallback : null), true);
						}

						if ($aParentFeed)
                        {	
                            $aParentFeed['iId'] = $aElement['parent_feed_id'];
                            $aParentFeed['sType'] = $sType;
                            $aParentFeed['sTitle'] = $aParentFeed['title'];
                            $aParentFeed['sDescription'] = $aParentFeed['mini_description'];
                            
							
							$aParentFeed['sUserName'] =$aParentFeed['user_name'];
							$aParentFeed['sFullName'] =  $aParentFeed['full_name'];
							$aParentFeed['sFeedTitle'] = $aParentFeed['feed_title'];
							$aParentFeed['iUserId'] =  $aParentFeed['user_id'];
							$aItem = Phpfox::getService('mfox.marketplace')->getListing($aElement['parent_feed_id']);
							
							
							if ($aItem['image_path']){
								$sImageUrl = Phpfox::getLib('image.helper')->display(array(
					                'server_id' => $aItem['server_id'],
					                'path' => 'core.url_pic',
					                'file' => $sType. '/' . $aItem['image_path'],
					                'suffix' => '',
					                'return_url' => true
					                    )
					            );	
							}else{
								$sImageUrl = Phpfox::getService('mfox.marketplace')->getDefaultImagePath();
							}
							$aParentFeed['sPhoto_Url'] = $sImageUrl;
						
							$aParentFeed['sLocation'] =  isset($aItem['location']) ? $aItem['location'] : '';
							$aParentFeed['sCity'] = $aItem['city'];
							$aParentFeed['iCountryChildId'] = $aItem['item_country_child_id'];
							$aParentFeed['sCountryIso'] = $aItem['item_country_iso'];
							$aParentFeed['sCountryChild'] = Phpfox::getService('core.country')->getChild($aItem['item_country_child_id']);
							$aParentFeed['sCountry'] = Phpfox::getService('core.country')->getCountry($aItem['item_country_iso']);
							$aParentFeed['sCurrencyId'] = $aItem['currency_id'];
							$aParentFeed['sSymbol'] = Phpfox::getService('core.currency')->getSymbol($aItem['currency_id']);
							$aParentFeed['sPostalCode'] = $aItem['postal_code'];
							$aParentFeed['sPrice'] = $aItem['price'];
							$aParentFeed['aCategoriesData'] = $aItem['categories'];
							$aParentFeed['bHasImage'] = $aItem['image_path']?1:0;
							
                        }   
                        break;
                    case 'pages':
                        // this case happens when object is shared feed (share pages in wall)
                        if (Phpfox::hasCallback($aElement['parent_module_id'], 'getActivityFeed')){
                            $aParentFeed = Phpfox::callback($aElement['parent_module_id'] . '.getActivityFeed', array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), (isset($this->_aCallback['module']) ? $this->_aCallback : null), true);
                        }

                        if ($aParentFeed)
                        {
                            $aParentFeed['iId'] = $aElement['parent_feed_id'];
                            $aParentFeed['sType'] = 'pages';
                            $aParentFeed['sTitle'] = $aParentFeed['feed_title'];
                            $aParentFeed['sDescription'] = $aParentFeed['feed_content'];
							
							if($aParentFeed['image_path'] ){
									$sImage = Phpfox::getLib('image.helper')->display(array(
	                                    'server_id' => $aParentFeed['server_id'],
	                                    'path' => 'core.url_user',
	                                    'file' => $aParentFeed['image_path'],
	                                    'suffix' => '_200',
					                    'is_page_image' => true,
					                    'return_url' => true
	                                )
	                            );
								
							}else{
								$sImage =  Phpfox::getService('mfox.pages')->getDefaultImagePath();
							}
							
							$info = Phpfox::getService('mfox.pages')->info(array('iPageId'=>$aElement['parent_feed_id']));
							
							$aParentFeed['sPhoto_Url'] = $info['sAvatarImage'];
                            // $aParentFeed['sPhoto_Url'] = $sImage;
							$aParentFeed['bHasImage'] =  $info['bHasImage']?1:0;
							
							$aParentFeed['sUserName'] =$aParentFeed['user_name'];
							$aParentFeed['sFullName'] =  $aParentFeed['full_name'];
							$aParentFeed['sFeedTitle'] = $aParentFeed['feed_title'];
							$aParentFeed['iUserId'] =  $aParentFeed['user_id'];
                        }   
                        break;
                    case 'groups':
                        // this case happens when object is shared feed (share pages in wall)
                        if (Phpfox::hasCallback($aElement['parent_module_id'], 'getActivityFeed')){
                            $aParentFeed = Phpfox::callback($aElement['parent_module_id'] . '.getActivityFeed', array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), (isset($this->_aCallback['module']) ? $this->_aCallback : null), true);
                        }

                        if ($aParentFeed)
                        {
                            $aParentFeed['iId'] = $aElement['parent_feed_id'];
                            $aParentFeed['sType'] = 'groups';
                            $aParentFeed['sTitle'] = $aParentFeed['feed_title'];
                            $aParentFeed['sDescription'] = $aParentFeed['feed_content'];

							if($aParentFeed['image_path'] ){
									$sImage = Phpfox::getLib('image.helper')->display(array(
	                                    'server_id' => $aParentFeed['server_id'],
	                                    'path' => 'core.url_user',
	                                    'file' => $aParentFeed['image_path'],
	                                    'suffix' => '_200',
					                    'is_page_image' => true,
					                    'return_url' => true
	                                )
	                            );

							}else{
								$sImage =  Phpfox::getService('mfox.groups')->getDefaultImagePath();
							}

							$info = Phpfox::getService('mfox.groups')->info(array('iGroupId'=>$aElement['parent_feed_id']));

							$aParentFeed['sPhoto_Url'] = $info['sAvatarImage'];
                            // $aParentFeed['sPhoto_Url'] = $sImage;
							$aParentFeed['bHasImage'] =  $info['bHasImage']?1:0;

							$aParentFeed['sUserName'] =$aParentFeed['user_name'];
							$aParentFeed['sFullName'] =  $aParentFeed['full_name'];
							$aParentFeed['sFeedTitle'] = $aParentFeed['feed_title'];
							$aParentFeed['iUserId'] =  $aParentFeed['user_id'];
                        }
                        break;
                    case 'videochannel':
                        $aParentFeed = Phpfox::getService('mfox.videochannel')->doVideoGetActivityFeed(array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), false, true);

                        if ($aParentFeed)
                            {
                            $aParentFeed['sType'] = 'videochannel';
                            $aParentFeed['sPhoto_Url'] = $aParentFeed['sFeedImage'];
                            $aParentFeed['sTitle'] = $aParentFeed['sFeedTitle'];
                            $aParentFeed['sDescription'] = $aParentFeed['sFeedContent'];
                            $aParentFeed['sLink_Url'] = $aParentFeed['sFeedLink'];
                        }
                        break;
                    case 'ultimatevideo_video':
                        $sType = 'ultimatevideo_video';
                        $aParentFeed = Phpfox::getService('ultimatevideo.callback')->getActivityFeedVideo(array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), false, true);
                        // return $aParentFeed;

                        if ($aParentFeed)
                        {   
                            $aParentFeed['iId'] = $aElement['parent_feed_id'];
                            $aParentFeed['sType'] = 'ultimatevideo/video';
                            $aParentFeed['sTypeId'] = $sType;
                            $aParentFeed['sTitle'] = $aParentFeed['title'];
                            $aParentFeed['sDescription'] = $aParentFeed['mini_description'];
                            
                            
                            $aParentFeed['sUserName'] =$aParentFeed['user_name'];
                            $aParentFeed['sFullName'] =  $aParentFeed['full_name'];
                            $aParentFeed['sFeedTitle'] = $aParentFeed['feed_title'];
                            $aParentFeed['iUserId'] =  $aParentFeed['user_id'];
                            $aItem = Phpfox::getService('ultimatevideo')->getVideo($aElement['parent_feed_id']);
                            
                            if ($aItem['image_path']){
                                $sImageUrl = Phpfox::getLib('image.helper')->display(array(
                                    'server_id' => $aItem['image_server_id'],
                                    'path' => 'core.url_pic',
                                    'file' => $aItem['image_path'],
                                    'suffix' => '_500',
                                    'return_url' => true
                                ));  
                            }else{
                                $sImageUrl = Phpfox::getService('mfox.marketplace')->getDefaultImagePath();
                            }
                            $aParentFeed['sPhoto_Url'] = $sImageUrl;

                            $aParentFeed['sLocation'] =  isset($aItem['location']) ? $aItem['location'] : '';
                            $aParentFeed['sCity'] = $aItem['city'];
                            $aParentFeed['iCountryChildId'] = $aItem['item_country_child_id'];
                            $aParentFeed['sCountryIso'] = $aItem['item_country_iso'];
                            $aParentFeed['sCountryChild'] = Phpfox::getService('core.country')->getChild($aItem['item_country_child_id']);
                            $aParentFeed['sCountry'] = Phpfox::getService('core.country')->getCountry($aItem['item_country_iso']);
                            $aParentFeed['sCurrencyId'] = $aItem['currency_id'];
                            $aParentFeed['sSymbol'] = Phpfox::getService('core.currency')->getSymbol($aItem['currency_id']);
                            $aParentFeed['sPostalCode'] = $aItem['postal_code'];
                            $aParentFeed['sPrice'] = $aItem['price'];
                            $aParentFeed['aCategoriesData'] = $aItem['categories'];
                            $aParentFeed['bHasImage'] = $aItem['image_path']?1:0;
                            

                            
                        }   
                        break;
                    case 'ultimatevideo_playlist':
                        $sType = 'ultimatevideo_playlist';
                        $aParentFeed = Phpfox::getService('ultimatevideo.callback')->getActivityFeedPlaylist(array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), false, true);
                        // return $aParentFeed;

                        if ($aParentFeed)
                        {   
                            $aParentFeed['iId'] = $aElement['parent_feed_id'];
                            $aParentFeed['sType'] = 'ultimatevideo/playlist';
                            $aParentFeed['sTypeId'] = $sType;
                            $aParentFeed['sTitle'] = $aParentFeed['title'];
                            $aParentFeed['sDescription'] = $aParentFeed['mini_description'];
                            
                            
                            $aParentFeed['sUserName'] =$aParentFeed['user_name'];
                            $aParentFeed['sFullName'] =  $aParentFeed['full_name'];
                            $aParentFeed['sFeedTitle'] = $aParentFeed['feed_title'];
                            $aParentFeed['iUserId'] =  $aParentFeed['user_id'];
                            $aItem = Phpfox::getService('ultimatevideo.playlist')->getPlaylistById($aElement['parent_feed_id']);
                            
                            if ($aItem['image_path']){
                                $sImageUrl = Phpfox::getLib('image.helper')->display(array(
                                    'server_id' => $aItem['image_server_id'],
                                    'path' => 'core.url_pic',
                                    'file' => $aItem['image_path'],
                                    'suffix' => '_500',
                                    'return_url' => true
                                ));  
                            }else{
                                $sImageUrl = Phpfox::getService('mfox.marketplace')->getDefaultImagePath();
                            }
                            $aParentFeed['sPhoto_Url'] = $sImageUrl;
                        
                            $aParentFeed['sLocation'] =  isset($aItem['location']) ? $aItem['location'] : '';
                            $aParentFeed['sCity'] = $aItem['city'];
                            $aParentFeed['iCountryChildId'] = $aItem['item_country_child_id'];
                            $aParentFeed['sCountryIso'] = $aItem['item_country_iso'];
                            $aParentFeed['sCountryChild'] = Phpfox::getService('core.country')->getChild($aItem['item_country_child_id']);
                            $aParentFeed['sCountry'] = Phpfox::getService('core.country')->getCountry($aItem['item_country_iso']);
                            $aParentFeed['sCurrencyId'] = $aItem['currency_id'];
                            $aParentFeed['sSymbol'] = Phpfox::getService('core.currency')->getSymbol($aItem['currency_id']);
                            $aParentFeed['sPostalCode'] = $aItem['postal_code'];
                            $aParentFeed['sPrice'] = $aItem['price'];
                            $aParentFeed['aCategoriesData'] = $aItem['categories'];
                            $aParentFeed['bHasImage'] = $aItem['image_path']?1:0;
                            
                            $aParentFeed['iTotalVideo'] = $aItem['total_video'];
                            $aParentFeed['aVideoList'] = Phpfox::getService('ultimatevideo.playlist.browse')->getSomeVideoOfPlaylist($aItem['playlist_id']);
                            
                        }   
                        break;
                    case 'video':
                        $aParentFeed = Phpfox::getService('mfox.video')->doVideoGetActivityFeed(array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), false, true);

                        if ($aParentFeed)
                            {
                            $aParentFeed['sType'] = 'video';
                            $aParentFeed['sPhoto_Url'] = $aParentFeed['sFeedImage'];
                            $aParentFeed['sTitle'] = $aParentFeed['sFeedTitle'];
                            $aParentFeed['sDescription'] = $aParentFeed['sFeedContent'];
                            $aParentFeed['sLink_Url'] = $aParentFeed['sFeedLink'];
                        }
                        break;

                    case 'directory':
                        if (Phpfox::hasCallback($aElement['parent_module_id'], 'getActivityFeed'))
                        {
                            $aTmpFeed = Phpfox::callback($aElement['parent_module_id'] . '.getActivityFeed', array(
                                'feed_id' => $aElement['parent_feed_id'],
                                'item_id' => $aElement['parent_feed_id']
                            ), (isset($this->_aCallback['module']) ? $this->_aCallback : null), true);
                        }

                        if ($aTmpFeed)
                        {
                            if (!empty($aTmpFeed['image_path']))
                            {
                                $sImageUrl = Phpfox::getLib('image.helper') -> display(array(
                                    'server_id' => $aTmpFeed['image_server_id'],
                                    'path' => 'core.url_pic',
                                    'file' => $aTmpFeed['image_path'],
                                    'suffix' => '_200',
                                    'return_url' => true
                                ));
                            }
                            else
                            {
                                $sImageUrl = Phpfox::getService('mfox.directory')->getDefaultImageUrl();
                            }

                            $aParentFeed['bHasImage'] = $aTmpFeed['image_path'] ? 1 : 0;
                            $aParentFeed['iBusinessId'] = $aElement['parent_feed_id'];
                            $aParentFeed['iId'] = $aElement['parent_feed_id'];
                            $aParentFeed['iUserId'] =  $aTmpFeed['user_id'];
                            $aParentFeed['sDescription'] = $aTmpFeed['description_parsed'];
                            $aParentFeed['sFeedTitle'] = $aTmpFeed['feed_title'];
                            $aParentFeed['sFullName'] =  $aTmpFeed['full_name'];
                            $aParentFeed['sPhoto_Url'] = $sImageUrl;
                            $aParentFeed['sTitle'] = $aTmpFeed['feed_title'];
                            $aParentFeed['sType'] = 'directory';
                            $aParentFeed['sUserName'] =$aTmpFeed['user_name'];
                        }
                        break;

                    default:
                        $aTemp['bIsShare'] = false;
                        break;
                }

                if ($aParentFeed)
                {
                    $aParentFeed['iId'] = $aElement['parent_feed_id'];
                }

                $aTemp['aParentFeed'] = $aParentFeed;
            }
            
            // Check type, no share and privacy.
            // $bCheckType = $aElement['type_id'] != 'video' && $aElement['type_id'] != 'photo' && $aElement['type_id'] != 'event' && $aElement['type_id'] != 'music_song';
            // $bCheckNoShare = isset($aElement['no_share']) && $aElement['no_share'] == true;
            $bCanShare = Phpfox::hasCallback($aElement['type_id'], 'canShareItemOnFeed');

            if($bCanShare) {
                $bCanShare = ($aElement['privacy'] != 0) ? false  : true;
            }

            /**
             * cannot share if the item privacy is not 0 or it is a shared feed
             */
            $aTemp['bCanShare'] = $bCanShare;
            
            $sDescription = '';
            $sContent = '';
            $sFeedInfo = '';

            $bIsMultiAttachment = false; // handle multi attachment case
            $aAttachments = array();

            $aAttachment = array();
            $aAttachment['sType'] = $aElement['type_id'];            
            $aAttachment['sModule'] = $aElement['type_id'];            
            $aAttachment['sPhoto_Url'] = null;   
            // not containing 'pages' type, because of feed is not created when creating new page
            switch ($aElement['type_id']) {
                case 'user_photo':
                    $aAttachment['sPhotoUrl'] = Phpfox::getService('mfox.user')->getImageUrl($aElement, '');
                    $sContent =  Phpfox::getPhrase('updated_gender_profile_photo', array('gender' => Phpfox::getService('user')->gender($aElement['gender'], 1)));
                    break;

                case 'user_status':
                    $sContent = "";
                    $aUserStatus = $this->getUserStatus($aElement['item_id']);
                    if (!empty($aUserStatus))
                    {
                        $sContent = $aUserStatus['content'];
                        if (!empty($aUserStatus['location_name']))
                        {
                            $aAttachment['sLocationName'] = $aUserStatus['location_name'];
                            $aAttachment['sType'] = 'ynmobile_map'; // because client check attachment base on type, we adjust to make it work
                            $aAttachment['sTitle'] = $aUserStatus['location_name'];
                            $aTemp['sActionType'] = 'ynmobile_checkin';
                        }
                        if (!empty($aUserStatus['location_latlng']))
                        {
                            $aLocationLatlng = json_decode($aUserStatus['location_latlng'], true);
                            $aAttachment['fLatitude'] = $aLocationLatlng['latitude'];
                            $aAttachment['fLongitude'] = $aLocationLatlng['longitude'];
                        }
                    }
                    break;
                
                case 'feed_comment':
                    $sContent = $this->getContentOfFeedComment($aElement);
                    break;
                
                case 'fevent':
                case 'event':
                    $sContent = $aElement['feed_content'];
                    $aEvent = Phpfox::getService('mfox.event')->isAdvancedModule()
                            ? Phpfox::getService('fevent')->getEvent($aElement['item_id'], false, true)
                            : Phpfox::getService('event')->getEvent($aElement['item_id'], false, true);

                    if($aEvent['image_path'] == null || $aEvent['image_path'] == ''){
                        $aAttachment['sEventImage'] = Phpfox::getService('mfox.event')->getDefaultImageEventPath();
                    } else {
                        $aAttachment['sEventImage'] = Phpfox::getLib('image.helper')->display(array(
                            'server_id' => $aEvent['image_server_id'],
                            'path' => 'event.url_image',
                            'file' => $aEvent['image_path'],
                            'suffix' => '',
                            'return_url' => true
                                )
                        );
                    }
                    $aAttachment['sPhoto_Url'] = $aAttachment['sEventImage'];            

                    $aAttachment['iTotalGuest'] = Phpfox::getService('mfox.helper.event')->getNumberOfGuestOfEvent($aElement['item_id']);
                    $aAttachment['sLocation'] = $aEvent['location'];
                    $aTemp['sItemTitle'] = $aElement['feed_title'];
                    $aAttachment['iStartTime'] = $aEvent['start_time'];
                    $aAttachment['sDescription'] = Phpfox::getLib('parse.output')->parse($aEvent['description']);

                    $iStartTimeUser = Phpfox::getService('mfox.core')->convertToUserTimeZone((int)$aEvent['start_time']);
                    $aStartTimeYear = date('Y', $iStartTimeUser);
                    $aStartTimeMonth = date('M', $iStartTimeUser);
                    $aStartTimeDay = date('d', $iStartTimeUser);
                    $aAttachment['aStartTime'] = array(
                        'iYear' => $aStartTimeYear, 
                        'iMonth' => $aStartTimeMonth, 
                        'iDay' => $aStartTimeDay, 
                        );
                    
                    break;
                
                case 'fevent_comment':
                case 'event_comment':
                    $sContent = $aElement['feed_status'];
                    break;

                case 'pages_comment':
                case 'groups_comment':
                    $sContent = $aElement['feed_status'];
                    break;
                
                case 'advancedphoto':
                    $aPhoto = Phpfox::getService('advancedphoto')->getPhoto($aElement['item_id']);

                    if(!empty($aPhoto['location_latlng']) && !empty($aPhoto['location_name'])){
                        list($lat, $lon ) = explode(';', $aPhoto['location_latlng']);

                        $aPhotoLocation =  array(
                            'fLatitude'=> $lat,
                            'fLongitude'=> $lon,
                            'sLocation'=>$aPhoto['location_name'],
                        );
                    }

                    $aTemp['bCanPostComment'] = Phpfox::getService('mfox.comment')->checkCanPostComment(array(
                        'comment_type_id' => 'advancedphoto',
                        'privacy' => $aPhoto['privacy'],
                        'comment_privacy' => $aPhoto['privacy_comment'],
                        'like_type_id' => 'advancedphoto',
                        'feed_is_liked' => $aPhoto['is_liked'],
                        'feed_is_friend' => $aPhoto['is_friend'],
                        'item_id' => $aPhoto['photo_id'],
                        'user_id' => $aPhoto['user_id'],
                        'total_comment' => $aPhoto['total_comment'],
                        'total_like' => $aPhoto['total_like'],
                        'feed_link' => Phpfox::getLib('url')->permalink('photo', $aPhoto['photo_id'], $aPhoto['title']),
                        'feed_title' => $aPhoto['title'],
                        'feed_display' => 'view',
                        'feed_total_like' => $aPhoto['total_like'],
                        'report_module' => 'advancedphoto',
                        'report_phrase' =>  Phpfox::getPhrase('photo.report_this_photo'))
                    );
                    $aPhotoIds = array();

                    $aPhotoIds[] = $aElement['item_id'];

                    $aRows = $this->database()->select('*')
                         ->from(Phpfox::getT('photo_feed'))
                         ->where('feed_id = ' . $aElement['feed_id'])
                         ->execute('getRows');

                    foreach($aRows as $aRow) {
                        $aPhotoIds[] = $aRow['photo_id'];
                    }

                    foreach($aPhotoIds as $iPhotoId) {
                        $aPhoto = Phpfox::getService('advancedphoto')->getPhoto($iPhotoId);
                        if(isset($aPhoto['photo_id'])) {
                            $aTempAttachment = $aAttachment;
                            $aTempAttachment['sPhoto_Url'] = Phpfox::getLib('image.helper')->display(array(
                                'server_id' => $aPhoto['server_id'],
                                'path' => 'photo.url_photo',
                                'file' => $aPhoto['destination'],
                                'suffix' => '_500',
                                'return_url' => true
                                    )
                            );

                            $aTempAttachment['sDescription'] = Phpfox::getLib('parse.output')->shorten($sDescription, 200, '...');
                            $aTempAttachment['sFeedInfo'] = $aElement['feed_info'];
                            $aTempAttachment['sLink_Url'] = $aElement['feed_link'];
                            $aTempAttachment['iId'] = $iPhotoId;
                            
                            $aTempAttachment['sTitle'] = $aElement['feed_title'];
                            $aTempAttachment['iAlbumId'] = intval($aPhoto['album_id']);
							
							$aTempAttachment['sAlbumName'] =  $aPhoto['name'];
							
							if($aTempAttachment['iAlbumId']){
								$aTemp['iAlbumId'] = $aTempAttachment['iAlbumId'];
								$aTemp['sActionType'] =   'advancedphoto_album';
								$aTemp['sAlbumName']= $aPhoto['album_title'];
								$aTemp['sAlbumModelType'] = 'advancedphoto_album';
								$aTemp['sFeedInfo'] = $aElement['feed_info'];
							}

                            $aAttachments[] = $aTempAttachment;
                        }
                    }

                    $bIsMultiAttachment = true;
                    
                    $sContent = $aElement['feed_status'];
                    $sFeedInfo = $aElement['feed_info'];

                    break;                
                case 'photo':
                    $aPhoto = Phpfox::getService('photo')->getPhoto($aElement['item_id']);

                    if(!empty($aPhoto['location_latlng']) && !empty($aPhoto['location_name'])){
                        list($lat, $lon ) = explode(';', $aPhoto['location_latlng']);

                        $aPhotoLocation =  array(
                            'fLatitude'=> $lat,
                            'fLongitude'=> $lon,
                            'sLocation'=>$aPhoto['location_name'],
                        );
                    }

                    $aTemp['bCanPostComment'] = Phpfox::getService('mfox.comment')->checkCanPostComment(array(
                        'comment_type_id' => 'photo',
                        'privacy' => $aPhoto['privacy'],
                        'comment_privacy' => $aPhoto['privacy_comment'],
                        'like_type_id' => 'photo',
                        'feed_is_liked' => $aPhoto['is_liked'],
                        'feed_is_friend' => $aPhoto['is_friend'],
                        'item_id' => $aPhoto['photo_id'],
                        'user_id' => $aPhoto['user_id'],
                        'total_comment' => $aPhoto['total_comment'],
                        'total_like' => $aPhoto['total_like'],
                        'feed_link' => Phpfox::getLib('url')->permalink('photo', $aPhoto['photo_id'], $aPhoto['title']),
                        'feed_title' => $aPhoto['title'],
                        'feed_display' => 'view',
                        'feed_total_like' => $aPhoto['total_like'],
                        'report_module' => 'photo',
                        'report_phrase' =>  Phpfox::getPhrase('photo.report_this_photo'))
                    );
                    $aPhotoIds = array();

                    $aPhotoIds[] = $aElement['item_id'];

                    if (!array_search($sItemType, array('pages', 'event', 'fevent', 'directory'))) {
                        $aRows = $this->database()->select('*')
                             ->from(Phpfox::getT('photo_feed'))
                             ->where('feed_id = ' . $aElement['feed_id'])
                             ->execute('getRows');

                        foreach($aRows as $aRow) {
                            $aPhotoIds[] = $aRow['photo_id'];
                        }
                    }

                    foreach($aPhotoIds as $iPhotoId) {
                        $aPhoto = Phpfox::getService('photo')->getPhoto($iPhotoId);
                        if(isset($aPhoto['photo_id'])) {
                            $aTempAttachment = $aAttachment;
                            $aTempAttachment['sPhoto_Url'] = Phpfox::getLib('image.helper')->display(array(
                                'server_id' => $aPhoto['server_id'],
                                'path' => 'photo.url_photo',
                                'file' => $aPhoto['destination'],
                                'suffix' => '_500',
                                'return_url' => true
                                    )
                            );

                            $aTempAttachment['sDescription'] = Phpfox::getLib('parse.output')->shorten($sDescription, 200, '...');
                            $aTempAttachment['sFeedInfo'] = $aElement['feed_info'];
                            $aTempAttachment['sLink_Url'] = $aElement['feed_link'];
                            $aTempAttachment['iId'] = $iPhotoId;
                            
                            $aTempAttachment['sTitle'] = $aElement['feed_title'];
                            $aTempAttachment['iAlbumId'] = intval($aPhoto['album_id']);
							$aTempAttachment['sAlbumName'] =  $aPhoto['name'];
							
							if($aTempAttachment['iAlbumId']){
								$aTemp['iAlbumId'] = $aTempAttachment['iAlbumId'];
								$aTemp['sActionType'] =   'album';
								$aTemp['sAlbumName']= $aPhoto['album_title'];
								$aTemp['sAlbumModelType'] = 'album';
								$aTemp['sFeedInfo'] = $aElement['feed_info'];
							}

                            $aAttachments[] = $aTempAttachment;
                        }
                    }

                    $bIsMultiAttachment = true;
                    
                    $sContent = $aElement['feed_status'];
                    $sFeedInfo = $aElement['feed_info'];

                    break;
                case 'music_song':
                    $sContent = $aElement['feed_title'];
                    $sDescription = $aElement['feed_content'];
                    $sFeedInfo = $aElement['feed_info'];
                    $aAttachment['sPhoto_Url'] = Phpfox::getService('mfox.song')->getDefaultImageSongPath();

                    break;
                case 'quiz':
                
                    $aAttachment =  phpFox::getService('mfox.quiz')->getFeedInfo($aElement['item_id']);
                    break;
				case 'musicsharing_album':
                case 'musicsharing_pagesalbum':
					$sContent = $aElement['feed_title'];
                    $sDescription = $aElement['feed_content'];
                    $sFeedInfo = $aElement['feed_info'];
                    $aMusicsharingAlbum = Phpfox::getService('mfox.musicsharing.album')->getAlbumSummary($aElement['item_id']);
                    $aAttachment['sPhoto_Url'] = $aMusicsharingAlbum['sImagePath'];
                    $aAttachment['sType'] =  'musicsharing_album';
					break;
                case 'music_album':
                    $sContent = $aElement['feed_title'];
                    $sDescription = $aElement['feed_content'];
                    $sFeedInfo = $aElement['feed_info'];
                    $aAttachment['sPhoto_Url'] = Phpfox::getService('mfox.album')->getDefaultImageAlbumPath();
                    $aAttachment['sType'] = 'music_song';
                    break;
                
                case 'videochannel':
                    // Get the image of video.
                    $aVideo = Phpfox::getService('videochannel')->getVideo($aElement['item_id']);
                    $aAttachment['sVideoImage'] = Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aVideo['image_server_id'],
                        'path' => 'core.url_pic',
                        'file' => $aVideo['image_path'],
                        'suffix' => '_120',
                        'return_url' => true
                            )
                    );

                    $aAttachment['sPhoto_Url'] = $aAttachment['sVideoImage'];
                    $aAttachment['iDuration'] = $aVideo['duration']; 

                    $aVideos = Phpfox::getService('videochannel')->getFeedVideos($aElement['feed_id']);
                    foreach($aVideos as $aVideo) {
                        $aVideo['sVideoImage'] = Phpfox::getLib('image.helper')->display(array(
                            'server_id' => $aVideo['image_server_id'],
                            'path' => 'core.url_pic',
                            'file' => $aVideo['image_path'],
                            'suffix' => '_120',
                            'return_url' => true
                                )
                        );
                        $aVideo['sPhoto_Url'] = $aVideo['sVideoImage'];
                        $aVideo['iDuration'] = $aVideo['duration'];
                        $aVideo['sModule'] = 'videochannel';
                        $aVideo['sType'] = 'videochannel';
                        $aVideo['iId']  = $aVideo['video_id'];
                        $aAttachments[] = $aVideo;
                    }
                    if(count($aVideos))
                        $bIsMultiAttachment = true;
                    
                    $sContent = $aElement['feed_status'];
                    $sFeedInfo = $aElement['feed_info'];

                    break;

                case 'ultimatevideo_video':
                    // Get the image of video.
                    $aVideo = Phpfox::getService('ultimatevideo')->getVideo($aElement['item_id']);
                    $aAttachment['sVideoImage'] = Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aVideo['image_server_id'],
                        'path' => 'core.url_pic',
                        'file' => $aVideo['image_path'],
                        'suffix' => '_500',
                        'return_url' => true
                            )
                    );

                    $aAttachment['sPhoto_Url'] = $aAttachment['sVideoImage'];
                    $aAttachment['iDuration'] = $aVideo['duration']; 

                    $sContent = $aElement['feed_status'];
                    $sFeedInfo = $aElement['feed_info'];

                    break;

                case 'ultimatevideo_playlist':
                    // Get the image of video.
                    $aPlaylist = Phpfox::getService('ultimatevideo')->getPlaylist($aElement['item_id']);

                    if($aPlaylist['image_path']){
                        $aAttachment['sPhoto_Url'] = Phpfox::getLib('image.helper')->display(array(
                            'server_id' => $aPlaylist['image_server_id'],
                            'path' => 'core.url_pic',
                            'file' => $aPlaylist['image_path'],
                            'suffix' => '_500',
                            'return_url' => true
                                )
                        );
                    }else{
                        $aAttachment['sPhoto_Url'] = Phpfox::getParam('core.path_actual').'PF.Site/Apps/YouNet_UltimateVideos' .'/assets/image/noimg_playlist.jpg';
                    }
                    
                    $aAttachment['iTotalVideo'] = $aPlaylist['total_video']; 

                    $sContent = $aElement['feed_status'];
                    $sFeedInfo = $aElement['feed_info'];

                    break;
                    
                case 'video':
                    $sContent = '';
                    $sDescription = $aElement['feed_content'];
                    
                    // Get the image of video.
                    $aVideo = Phpfox::getService('video')->getVideo($aElement['item_id']);
                    $aAttachment['sVideoImage'] = Phpfox::getLib('image.helper')->display(array(
                        'server_id' => $aVideo['image_server_id'],
                        'path' => 'video.url_image',
                        'file' => $aVideo['image_path'],
                        'suffix' => '_120',
                        'return_url' => true
                            )
                    );

                    $aAttachment['sPhoto_Url'] = $aAttachment['sVideoImage'];
                    $aAttachment['iDuration'] = $aVideo['duration']; 
                    break;
                    
                case 'music_song_comment':
                    
                    break;

                case 'link':
                    $aLink = Phpfox::getService('link')->getLinkById($aElement['item_id']);

                    if($aLink) {
                        $aAttachment['sOriginalLink_Url'] = $aLink['link'];
                        $aAttachment['sPhoto_Url'] = $aLink['image'];
                        $aAttachment['sDescription'] = $aLink['description'];
                        $aAttachment['sTitle'] = $aLink['title'];
                        $sContent = $aLink['status_info'];
                    }
                    break;

                case 'custom': //update profile
                case 'custom_relation': //update profile
                    $sContent = $aElement['feed_info'];
                    break;

                case 'blog':
                    // this case with blog feed (get attachment for feed)
                    // we use $aElement['item_id'] as objectId to get objectData or use data from $aElement
                    $aAttachment['sDescription'] = $aElement['text'];
                    $sContent = '';
                    break;

                case 'poll':
                    $aPoll = Phpfox::getService('poll')->getPollById($aElement['item_id']);
                    $bHasImage = true;
                    if(empty($aPoll['image_path'])){
                        $bHasImage = false;
						$aAttachment['sPhoto_Url'] = Phpfox::getParam('core.url_module') . 'mfox/static/image/poll-default.png';
                    } else {
                        $aAttachment['sPhoto_Url'] = Phpfox::getLib('image.helper')->display(array(
                            'server_id' => $aPoll['server_id'],
                            'path' => 'poll.url_image',
                            'file' => $aPoll['image_path'],
                            'suffix' => '',
                            'return_url' => true)
                        );
                    }
					
					$aAttachment['sDescription'] = '';
                    $aAttachment['bHasImage'] = $bHasImage;
                    $aAttachment['iTotalVotes'] = intval($aPoll['total_votes']);
					$aAttachment['bHasImage'] = $bHasImage;
              		$sContent = '';
					break;
					
                case 'forum':
                case 'forum_post':
                    if($aElement['type_id'] == 'forum_post'){
                        $aRow = $this->database()->select('fp.post_id, ft.thread_id, ft.group_id, ft.title, fp.user_id AS post_user_id, fp.total_like, fpt.text_parsed AS text, fp.time_stamp, fpt.text AS normal_text, ' . Phpfox::getUserField())
                            ->from(Phpfox::getT('forum_post'), 'fp')            
                            ->join(Phpfox::getT('forum_thread'), 'ft', 'ft.thread_id = fp.thread_id')
                            ->join(Phpfox::getT('user'), 'u', 'u.user_id = ft.user_id')
                            ->join(Phpfox::getT('forum_post_text'), 'fpt', 'fpt.post_id = fp.post_id')
                            ->where('fp.post_id = ' . (int) $aElement['item_id'])
                            ->execute('getSlaveRow');   

                        if ($aRow['user_id'] == $aRow['post_user_id'])
                        {
                            $sPhrase = 'replied on {gender} thread';
                            $sPhrase = str_replace('{gender}', Phpfox::getService('user')->gender($aRow['gender'], 1), $sPhrase);
                        }
                        else 
                        {
                            $sPhrase = 'replied on [x={user_id}]{full_name}[/x]\'s thread';
                            $sPhrase = str_replace(
                                array("{user_id}", "{full_name}")
                                , array($aRow['user_id'], $aRow['full_name'])
                                , $sPhrase);
                        }
                        $aTemp['sHeadLine'] = $sPhrase;
                    }
                    $aAttachment['sDescription'] = $aElement['feed_content'];
                    $sContent = '';
                    $aTemp['iItemId'] = $aElement['custom_data_cache']['thread_id'];
                    $aTemp['sItemTitle'] = $aElement['custom_data_cache']['title'];

                    break;

                case 'advancedmarketplace':
                    $sType = 'advancedmarketplace';
                    
                    $aItem = Phpfox::getService('mfox.advancedmarketplace')->getListing($aElement['item_id']);
                    $aAttachment['sDescription'] = $aElement['text'];
                                        
                    
                    if ($aItem['image_path']){
                        $sMarketplaceImage = Phpfox::getLib('image.helper')->display(array(
                            'server_id' => $aItem['server_id'],
                            'path' => 'core.url_pic',
                            'file' => $sType. '/' . $aItem['image_path'],
                            'suffix' => '',
                            'return_url' => true
                                )
                        );  
                    }else{
                        $sMarketplaceImage = Phpfox::getService('mfox.marketplace')->getDefaultImagePath();
                    }
                    $aAttachment['sPhoto_Url'] = $sMarketplaceImage;

                    $aAttachment = array_merge($aAttachment, array(
                        'sLocation' => isset($aItem['location']) ? $aItem['location'] : '',
                        'sCity'=>$aItem['city'],
                        'iCountryChildId'=>$aItem['country_child_id'],
                        'sCountryIso'=>$aItem['listing_country_iso'],
                        'sCountry'=> Phpfox::getService('core.country')->getCountry($aItem['listing_country_iso']),
                        'sCountryChild'=> Phpfox::getService('core.country')->getChild($aItem['country_child_id']),
                        'sCurrencyId'=>$aItem['currency_id'],
                        'sSymbol' => Phpfox::getService('core.currency')->getSymbol($aItem['currency_id']),
                        'sPostalCode'=> $aItem['postal_code'],
                        'sPrice'=> $aItem['price'],
                        'bHasImage'=>$aItem['image_path']?1:0,
                        'aCategoriesData' => $aItem['categories'],
                        'sTitle' => $aItem['title']
                    ));

                    $sContent = '';
                    break;

                case 'marketplace':
					$sType = 'marketplace';
					
                    $aItem = Phpfox::getService('mfox.marketplace')->getListing($aElement['item_id']);
                    $aAttachment['sDescription'] = $aElement['text'];
										
					
					if ($aItem['image_path']){
						$sMarketplaceImage = Phpfox::getLib('image.helper')->display(array(
			                'server_id' => $aItem['server_id'],
			                'path' => 'core.url_pic',
			                'file' => $sType. '/' . $aItem['image_path'],
			                'suffix' => '',
			                'return_url' => true
			                    )
			            );	
					}else{
						$sMarketplaceImage = Phpfox::getService('mfox.marketplace')->getDefaultImagePath();
					}
                    $aAttachment['sPhoto_Url'] = $sMarketplaceImage;

                    $aAttachment = array_merge($aAttachment, array(
                        'sLocation' => isset($aItem['location']) ? $aItem['location'] : '',
                        'sCity'=>$aItem['city'],
                        'iCountryChildId'=>$aItem['item_country_child_id'],
                        'sCountryIso'=>$aItem['item_country_iso'],
                        'sCountry'=> Phpfox::getService('core.country')->getCountry($aItem['item_country_iso']),
                        'sCountryChild'=> Phpfox::getService('core.country')->getChild($aItem['item_country_child_id']),
                        'sCurrencyId'=>$aItem['currency_id'],
                        'sSymbol' => Phpfox::getService('core.currency')->getSymbol($aItem['currency_id']),
                        'sPostalCode'=> $aItem['postal_code'],
                        'sPrice'=> $aItem['price'],
                        'bHasImage'=>$aItem['image_path']?1:0,
                        'aCategoriesData' => $aItem['categories'],
                        'sTitle' => $aItem['title']
                    ));

                    $sContent = '';
                    break;

                case 'directory':
                case 'directory_checkinhere':
                    $aItem = Phpfox::getService('directory')->getQuickBusinessById($aElement['item_id']);
                    $aAttachment = array_merge($aAttachment, array(
                        'bHasImage' => !empty($aItem['logo_path']),
                        'iBusinessId' => $aElement['item_id'],
                        'sDescription' => $aItem['short_description_parsed'],
                        'sPhoto_Url' => Phpfox::getService('mfox.directory')->getImageUrl($aItem),
                        'sTitle' => $aItem['name'],
                        'sType' => 'directory'
                    ));
                    $sContent = '';
                    break;

                case 'directory_comment':
                    $sContent = $aElement['feed_status'];
                    break;

                default:
                    $sContent = $aElement['feed_content'];
                    break;
            }

            list($aTemp['bReadMore'], $aTemp['sContent']) = $this->word_limiter($sContent, 30);
            if(strpos('[/user]',$aTemp['sContent'])) {
                $aTemp['sContent'] = Phpfox::getLib('parse.output')->feedStrip($aTemp['sContent']);
            }
            $aTemp['sFullContent'] = Phpfox::getLib('parse.output')->feedStrip($sContent);

            if(!$bIsMultiAttachment) {
                if(!isset($aAttachment['sDescription'])) {
                    $sDescription = Phpfox::getService('mfox')->decodeUtf8Compat($sDescription);
                    $aAttachment['sDescription'] = Phpfox::getLib('parse.output')->shorten($sDescription, 200, '...');
                }
                $aAttachment['sFeedInfo'] = $sFeedInfo;
                $aAttachment['sLink_Url'] = $aElement['feed_link'];
                // update item id again
                switch ($aAttachment['sType']) {
                    case 'blog':
                        $aAttachment['iId'] = $aElement['blog_id'];
                        break;
                    
                    case 'forum':
                    case 'forum_post':
                        $aAttachment['iId'] = $aElement['custom_data_cache']['thread_id'];
                        break;

                    default:
                        $aAttachment['iId'] = $aElement['item_id'];
                        break;
                }
                
                if(!isset($aAttachment['sTitle']))
                {
                    $aAttachment['sTitle'] = '';
                    
                    if (!empty($aElement['feed_title']))
                    {
                        $aAttachment['sTitle'] = $aElement['feed_title'];
                    }
                    else if (!empty($aElement['custom_data_cache']) && !empty($aElement['custom_data_cache']['title']))
                    {
                        $aAttachment['sTitle'] = $aElement['custom_data_cache']['title'];
                    }
                }
                
                $aAttachments = array($aAttachment);
            }


            // make it compatible with client
            $aTemp['aAttachments'] = $aAttachments;
            
            // if ($aElement['type_id'] == 'music_album')
            // {
            //     $aTempElement = $aElement;
            //     $aTempElement['type_id'] = 'music_song';
            //     $aTemp['bIsLike'] = $this->checkIsLiked($aTempElement);
            // }
            // else
            // {
            // minhTA removed: use feed is like
                // $aTemp['bIsLike'] = $this->checkIsLiked($aElement);
            // }
            if($aElement['feed_is_liked']){
                $aTemp['bIsLike'] = true;
            } else {
                $aTemp['bIsLike'] = false;
            }

            $aLike = array();
            if($aElement['type_id'] == 'music_album') {
                $sTypeForFeed = 'music_song';
                $iLikeItemId = $aElement['item_id_in_db'];
            } else if($aElement['type_id'] == 'forum') {
                $sTypeForFeed = 'forum_post';
                $iLikeItemId = $aElement['like_item_id'];
            } else {
                $sTypeForFeed = $aElement['type_id'];
                $iLikeItemId = $aElement['item_id_in_db'];
            }  
            $aLike['likes'] = Phpfox::getService('mfox.helper.like')->getLikesForFeed($sTypeForFeed, $iLikeItemId, $aTemp['bIsLike'], Phpfox::getParam('feed.total_likes_to_display'), true);
            $aLike['feed_total_like'] = Phpfox::getService('mfox.helper.like')->getTotalLikeCount();
            $aTemp['iTotalLike'] = $aLike['feed_total_like'];

            $aTemp['iPrivacy'] = $aElement['privacy'];
            foreach($aLike['likes'] as $like){
                $aTemp['aUserLike'][] = array('iUserId' => $like['user_id'], 'sDisplayName' => $like['full_name']);
            }

            // custom work get like emo
            $aEmoticons = Phpfox::getService('mfox')->getReEmoticons();
            foreach($aLike['likes'] as $iKey => $aReactionLike)
            {
                if(!isset($aReactionLike['reaction_id']))
                {
                    continue;
                }
                if($aReactionLike['reaction_id'] == 0)
                {
                    $aReactionLike['reaction_id'] = 1;
                }
                if(isset($aEmoticons[$aReactionLike['reaction_id']]))
                {
                    if(!isset($aEmoticons[$aReactionLike['reaction_id']]['counter']))
                    {
                        $aEmoticons[$aReactionLike['reaction_id']]['counter'] = 0;
                    }
                    $aEmoticons[$aReactionLike['reaction_id']]['counter'] += 1;
                }
            }

            //check share 
            if ($aElement['parent_feed_id']) {
                $aTemp['sActionType'] = 'share';
            }

            if($aTemp['aParentFeed']) { // this is shared feed
                $aTemp['aAttachments'] = array($aTemp['aParentFeed']);
                $aTemp['iSharedUserId'] = $aTemp['aParentFeed']['iUserId'];
                $aTemp['sSharedUserName'] = $aTemp['aParentFeed']['sFullName'];

                $aTemp['sSharedItemType'] = $aTemp['aParentFeed']['sType'];

                // lytk fix issue shared item.
                $aObjectUserCheckType = $this->database()->select('u.user_id, u.profile_page_id')
                    ->from(Phpfox::getT('user'), 'u')
                    ->where('u.user_id = ' . $aTemp['iSharedUserId'])
                    ->execute('getSlaveRow');
                if(isset($aObjectUserCheckType['profile_page_id']) && (int)$aObjectUserCheckType['profile_page_id'] > 0){
                    $aTemp['iSharedUserId'] = (int)$aObjectUserCheckType['profile_page_id'];
                    $aTemp['sSharedUserType'] = 'pages';
                } else {
                    $aTemp['sSharedUserType'] = 'user';
                }
            }


            $aTemp['bIsDisliked'] = Phpfox::getService('mfox.like')->checkIsDisliked($aElement['type_id'], $aTemp['iItemId']) ? true : false ;
            $aTemp['bCanDislike'] = Phpfox::getService('mfox.like')->allowdislike() ? 1 : 0;
            if($aElement['type_id'] == 'feed_mini'){
                $aDislikes = Phpfox::getService('mfox.like')->getDislikes($aElement['type_id'], $aTemp['iItemId'], false, null, null);
            } else {
                $aDislikes = Phpfox::getService('mfox.like')->getActionsFor($aElement['type_id'], $aTemp['iItemId'], null, null);
            }

            $aUserDislike = array();
            foreach($aDislikes as $dislike){
                $aUserDislike[] = array('iUserId' => $dislike['user_id'], 'sDisplayName' => $dislike['full_name']);
            }
            
            $aTemp['aDislikes'] = $aUserDislike;
            $aTemp['iTotalDislike'] = count($aUserDislike);

            $aTemp['parentModuleId'] = 'feed'; // to distinct with feed of event

            $aTemp['iParentUserId'] = $aElement['parent_user_id']; 
            $aTemp['sParentFullname'] = isset($aElement['parent_user']) ? $aElement['parent_user']['parent_full_name'] : (isset($aElement['custom_data_cache']['parent_full_name']) ? $aElement['custom_data_cache']['parent_full_name'] : '');

            if($aTemp['iParentUserId'] > 0 && $aTemp['sParentFullname'] == '') {
                $aUser = Phpfox::getService('mfox.helper.user')->getUserData($aTemp['iParentUserId']);
                $aTemp['sParentFullname'] = $aUser['full_name'];
            }

            if ($sParentId == 'directory')
            {
                $aTemp['bCanDislike'] = false;
                $aTemp['bCanShare'] = false;
                $aTemp['aDislikes'] = array();
                $aTemp['iTotalDislike'] = 0;
                $aTemp['parentModuleId'] = 'directory';
            }

            if($sParentId == 'event' || $sParentId == 'fevent') {
                $aTemp['bCanDislike'] = false;
                $aTemp['bCanShare'] = false;
                $aTemp['aDislikes'] = array();
                $aTemp['iTotalDislike'] = 0;
                $aTemp['parentModuleId'] = Phpfox::getService('mfox.event')->isAdvancedModule() ? 'fevent' : 'event';
            }

            if($sParentId == 'pages') {
                $aTemp['bCanDislike'] = false;
                // $aTemp['bCanShare'] = false;
                $aTemp['aDislikes'] = array();
                $aTemp['iTotalDislike'] = 0;
                $aTemp['parentModuleId'] = 'pages';
            }
            if($sParentId == 'groups') {
                $aTemp['bCanDislike'] = false;
                // $aTemp['bCanShare'] = false;
                $aTemp['aDislikes'] = array();
                $aTemp['iTotalDislike'] = 0;
                $aTemp['parentModuleId'] = 'groups';
            }

            if($aTemp['sActionType'] == 'user_photo' ||
               $aTemp['sActionType'] == 'custom'
            ) {
                $aTemp['bCanLike'] = true;
                $aTemp['bCanShare'] = true;
                $aTemp['bCanReport'] = false;
                $aTemp['bCanDislike'] = false;
                $aTemp['aDislikes'] = array();
                $aTemp['iTotalDislike'] = 0;
                $aTemp['bCanPostComment'] = false;
                $aTemp['bCanComment'] = false;
            }

            if($aTemp['sActionType'] == 'forum' ||
               $aTemp['sActionType'] == 'forum_post'
            ) {
                $aTemp['bCanPostComment'] = false;
            }
			
			if($aTemp['sActionType'] == 'event' ||
               $aTemp['sActionType'] == 'fevent' ||
               $aTemp['sActionType'] == 'directory' ||
               $aTemp['sActionType'] == 'directory_checkinhere'
            ) {
                $aTemp['bCanPostComment'] = false;
            }

			if($aTemp['sActionType'] == 'marketplace'){
				$aTemp['bCanPostComment'] = $aTemp['bCanPostComment'] && Phpfox::getUserParam('marketplace.can_post_comment_on_listing'); 
			}else if ($aTemp['sActionType'] ==  'advancedmarketplace'){
				$aTemp['bCanPostComment'] = $aTemp['bCanPostComment'] && Phpfox::getUserParam('advancedmarketplace.can_post_comment_on_listing');
			}else if ($aTemp['sActionType'] ==  'blog'){
				$aTemp['bCanPostComment'] = $aTemp['bCanPostComment'] && Phpfox::getUserParam('blog.can_post_comment_on_blog');
			}else if ($aTemp['sActionType'] == 'poll'){
				$aTemp['bCanPostComment']  =  (null == Phpfox::getService('mfox.poll')->canPostComment($aElement['item_id']));
			}else if($aTemp['sActionType'] == 'quiz'){
				
				
				$aTemp['bCanPostComment'] =  (null == Phpfox::getService('mfox.quiz')->canPostComment($aElement['item_id']));
				
			}

            if($aTemp['sActionType'] == 'musicsharing_pagesalbum'){
                $aTemp['sActionType'] = 'musicsharing_album';
            }

            $aNoDislikeModules = array(
                'videochannel',
                'advancedphoto',
                'fevent'
            );

            if(in_array($aTemp['sItemType'], $aNoDislikeModules)) {
                $aTemp['bCanDislike'] = false;
            }

            //GET OBJECT OWNER
            //GET OBJECT PARENT
            // Get the parents for the feeds so it displays arrow.png 
            $parent_user = null;
            if (!empty($aParentFeeds))
            {
                if (in_array($aElement['feed_id'], $aFeedsWithParents) && $aElement['type_id'] != 'photo_tag')
                {
                    foreach ($aParentUsers as $aUser)
                    {
                        if ($aUser['user_id'] == $aElement['parent_user_id'])
                        {
                            $aTempUser = array();
                            foreach ($aUser as $sField => $sVal)
                            {
                                $aTempUser['parent_' . $sField] = $sVal;
                            }
                            $parent_user = $aTempUser;
                        }
                    }                   
                }
            }

            if ($sParentId != 'pages' && $sParentId != 'groups' && $parent_user === null && isset($aElement['parent_user'])){
                $parent_user = $aElement['parent_user'];
            }

            // fix issue pages_comment
            if(null == $parent_user && $aElement['type_id']=='pages_comment'){
                $parent_user = $aElement['custom_data_cache'];

            }
            if(null == $parent_user && $aElement['type_id']=='groups_comment'){
                $parent_user = $aElement['custom_data_cache'];

            }

            if(isset($parent_user['parent_profile_page_id']) && (int)$parent_user['parent_profile_page_id'] > 0) {
                if(Phpfox::getService('groups')->getPage($parent_user['parent_profile_page_id']))
                    $sObjectParentType = 'groups';
                else $sObjectParentType = 'pages';
            }else{
                $sObjectParentType = 'user';
            }
            
            $iObjectParentId = '';
            if(isset($parent_user['parent_user_id'])){
                if(isset($parent_user['parent_profile_page_id']) && (int)$parent_user['parent_profile_page_id'] > 0){
                    $iObjectParentId = (int)$parent_user['parent_profile_page_id'];
                } else {
                    $iObjectParentId = (int)$parent_user['parent_user_id'];
                }
            }
            $aTemp2 = array(
                'bIsLiked' => $aTemp['bIsLike'],

                'iObjectId'=> $aTemp['iItemId'],
                'sObjectType'=> $aTemp['sItemType'],
                'sObjectTitle'=> $aTemp['sItemTitle'],
                    
                'iObjectOwnerId' => $aElement['user_id'],
                'sObjectOwnerType'=> 'user',
                'sObjectOwnerTitle'=> $aElement['full_name'],
                    
                'iObjectParentId' => $iObjectParentId,
                'sObjectParentType'=> $sObjectParentType,
                'sObjectParentTitle'=> ( (isset($parent_user['parent_user_id'])) ? $parent_user['parent_full_name'] : '' ),

                'sUserTimezone' => Phpfox::getLib('date')->getTimeZone(),                   
            );
			
			if($aTemp2['sObjectParentType'] == 'user' && $iObjectParentId == Phpfox::getUserId()){
				$aTemp2['bCanDelete'] = 1;
			}else if($aTemp2['sObjectParentType'] == 'pages' && Phpfox::getService('pages')->isAdmin($iObjectParentId)){
				$aTemp2['bCanDelete'] = 1;
			}
			
            $aTemp = array_merge($aTemp, $aTemp2);

            // update sPhoto_Url from sFeedImage
            if(isset($aTemp['aAttachments']) && is_array($aTemp['aAttachments']))
            {
                foreach($aTemp['aAttachments'] as $key => $val){
                    if(isset($val['sPhoto_Url']) == false && isset($val['sFeedImage']) == true){
                        $aTemp['aAttachments'][$key]['sPhoto_Url'] = $val['sFeedImage'];

                        if($val['sType'] == 'event' || $val['sType'] == 'fevent'){
                            $aEvent = Phpfox::getService('mfox.event')->isAdvancedModule()
                                ? Phpfox::getService('fevent')->getEvent((int) $val['iId'])
                                : Phpfox::getService('event')->getEvent((int) $val['iId']);

                            if($aEvent['image_path'] == null){
                                $sEventBigImageUrl = Phpfox::getService('mfox.event')->getDefaultImageEventPath();
                            } else {
                                $sEventBigImageUrl = Phpfox::getLib('image.helper')->display(array(
                                    'server_id' => $aEvent['server_id'],
                                    'path' => 'event.url_image',
                                    'file' => $aEvent['image_path'],
                                    'suffix' => '',
                                    'return_url' => true
                                        )
                                );   
                            }  

                            $aTemp['aAttachments'][$key]['sPhoto_Url'] = $sEventBigImageUrl; 
                            $aTemp['aAttachments'][$key]['iStartTime'] = $aEvent['start_time']; 
                            $aTemp['aAttachments'][$key]['sDescription'] = Phpfox::getLib('parse.output')->parse($aEvent['description']); 
                        } else if($val['sType'] == 'pages'){
                        }
                    }
                }
            }

            if(!empty($aPhotoLocation)){
                $aTemp['aLocation'] =  $aPhotoLocation;
            }

            $aTemp['iProfilePageId'] = (int)$aElement['profile_page_id'];

            if($aTemp['iProfilePageId']){
                $aTemp['sUserType'] = 'pages';
                $aTemp['iUserId']= $aTemp['iProfilePageId'];
            }else{
                $aTemp['sUserType'] = 'user';
            }

            // tags
            if(!empty($aTemp['sContent']))
            {
                $aTemp['sContent'] =  preg_replace('/\[x=(\d+)\]([^\[]+)\[\/x\]/mi','<a href-dir url="#/app/user/$1">$2</a>', $aTemp['sContent']);
            }

            if(!empty($aTemp['sFullContent']))
            {
                $aTemp['sFullContent'] =  preg_replace('/\[x=(\d+)\]([^\[]+)\[\/x\]/mi','<a href-dir url="#/app/user/$1">$2</a>', $aTemp['sFullContent']);
            }

			$aTemp['bCanComment'] =  $aTemp['bCanPostComment'];
            $aTemp['etag'] =  sha1(json_encode($aTemp));

            // custom work reaction
            $aTemp['aReactionEmoticons'] = $aEmoticons;
            //
            $result[] = $aTemp;
            // var_dump($aElement);

        }       

        return $result;
    }

    private function _getSupportedParentModuleIds()
    {
        $aIds = array(
            "'blog'",
            "'quiz'",
            "'poll'",
            "'pages'",
            "'videochannel'",
            "'directory'",
            "'user_photo'",
            "'ultimatevideo_video'",
            "'ultimatevideo_playlist'"
        );

        if (Phpfox::getService('mfox.song')->isAdvancedModule()) {
            $aIds = array_merge($aIds, array(
                "'musicsharing_song'",
                "'musicsharing_album'",
                "'musicsharing_pagesalbum'",
            ));
        } else {
            $aIds = array_merge($aIds, array(
                "'music_song'",
                "'music_album'",
            ));
        }

        if (Phpfox::getService('mfox.event')->isAdvancedModule()) {
            $aIds = array_merge($aIds, array(
                "'fevent'",
            ));
        } else {
            $aIds = array_merge($aIds, array(
                "'event'",
            ));
        }

        if (Phpfox::getService('mfox.photo')->isAdvancedModule()) {
            $aIds = array_merge($aIds, array(
                "'advancedphoto'",
            ));
        } else {
            $aIds = array_merge($aIds, array(
                "'photo'",
            ));
        }

        if (Phpfox::getService('mfox.marketplace')->isAdvancedModule()) {
            $aIds = array_merge($aIds, array(
                "'advancedmarketplace'",
            ));
        } else {
            $aIds = array_merge($aIds, array(
                "'marketplace'",
            ));
        }

        return $aIds;
    }

    /**
     * @param $aFeedId
     * @return array
     */
    public function __getFeedByIdList($aFeedId){

        $sFeedIdList = implode(',', $aFeedId);


        return $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
            ->from(Phpfox::getT((isset($this->_aCallback['table_prefix']) ? $this->_aCallback['table_prefix'] : '') . 'feed'), 'feed')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
            ->where('feed_id IN (' . $sFeedIdList . ')')
            ->execute('getSlaveRows')
            ;
    }

    public function __getFeedByID($iId)
    {
        return $this->database()->select('feed.*, ' . Phpfox::getUserField() . ', u.view_id')
            ->from(Phpfox::getT((isset($this->_aCallback['table_prefix']) ? $this->_aCallback['table_prefix'] : '') . 'feed'), 'feed')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = feed.user_id')
            ->where('feed_id =' . (int) $iId)
            ->execute('getSlaveRow');
    }

    /**
     * Set callback params for directory feeds
     * @param int $iBusinessId
     * @return boolean
     */
    private function __setCallbackConfigForDirectory($iBusinessId)
    {
        $aBusiness = Phpfox::getService('directory')->getQuickBusinessById($iBusinessId);

        $bCanPostComment = true;
        if (isset($aBusiness['privacy_comment']) && $aBusiness['user_id'] != Phpfox::getUserId() && !Phpfox::getUserParam('privacy.can_comment_on_all_items'))
        {
            switch ($aBusiness['privacy_comment'])
            {
                // Everyone is case 0. Skipped.
                // Friends only
                case 1:
                    if(!Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aBusiness['user_id']))
                    {
                        $bCanPostComment = false;
                    }
                    break;
                // Friend of friends
                case 2:
                    if (!Phpfox::getService('friend')->isFriendOfFriend($aBusiness['user_id']))
                    {
                        $bCanPostComment = false;    
                    }
                    break;
                // Only me
                case 3:
                    $bCanPostComment = false;
                    break;
            }
        }
                
        if (Phpfox::getUserId())
        {
            $bIsBlocked = Phpfox::getService('user.block')->isBlocked($aBusiness['user_id'], Phpfox::getUserId());
            if ($bIsBlocked)
            {
                $bCanPostComment = false;
            }
        }

        $this->setCallback(array(
                'module' => 'directory',
                'table_prefix' => 'directory_',
                'ajax_request' => 'directory.addFeedComment',
                'item_id' => $aBusiness['business_id'],
                'disable_share' => ($bCanPostComment ? false : true)
            )
        );

        return true;
    }

    public function __setCallbackConfigForEvent($iEventID){
        $aEvent = Phpfox::getService('mfox.event')->isAdvancedModule()
                ? Phpfox::getService('fevent')->getEvent((int) $iEventID, false, true)
                : Phpfox::getService('event')->getEvent((int) $iEventID, false, true);
        
        if (!isset($aEvent['event_id']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.the_event_you_are_looking_for_does_not_exist_or_has_been_removed"))
            );
        }

        $eventModuleId = Phpfox::getService('mfox.event')->isAdvancedModule() ? 'fevent' : 'event';
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check($eventModuleId, $aEvent['event_id'], $aEvent['user_id'], $aEvent['privacy'], $aEvent['is_friend'], true))
        {
            return array('result' => 0, 'error_code' => 1, 'message' =>  Phpfox::getPhrase('privacy.the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'));
        }        

        $bCanPostComment = true;
        if (isset($aEvent['privacy_comment']) && $aEvent['user_id'] != Phpfox::getUserId() && !Phpfox::getUserParam('privacy.can_comment_on_all_items'))
        {
            switch ($aEvent['privacy_comment']) {
                // Everyone is case 0. Skipped.
                // Friends only
                case 1:
                    if (!Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aEvent['user_id']))
                    {
                        $bCanPostComment = false;
                    }
                    break;
                // Friend of friends
                case 2:
                    if (!Phpfox::getService('friend')->isFriendOfFriend($aEvent['user_id']))
                    {
                        $bCanPostComment = false;
                    }
                    break;
                // Only me
                case 3:
                    $bCanPostComment = false;
                    break;
            }
        }

        $aCallback = false;
        if ($aEvent['item_id'] && Phpfox::hasCallback($aEvent['module_id'], 'viewEvent'))
        {
            $aCallback = Phpfox::callback($aEvent['module_id'] . '.viewEvent', $aEvent['item_id']);

            if ($aEvent['module_id'] == 'pages' && !Phpfox::getService('pages')->hasPerm($aCallback['item_id'], $eventModuleId . '.view_browse_events'))
            {
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_view_this_item_due_to_privacy_settings"))
                );
            }
        }

        if (Phpfox::getUserId())
        {
            $bIsBlocked = Phpfox::getService('user.block')->isBlocked($aEvent['user_id'], Phpfox::getUserId());
            if ($bIsBlocked)
            {
                $bCanPostComment = false;
            }
        }

        $aFeedCallback = array(
            'module' => Phpfox::getService('mfox.event')->isAdvancedModule() ? 'fevent' : 'event',
            'table_prefix' => (Phpfox::getService('mfox.event')->isAdvancedModule() ? 'fevent' : 'event') . '_',
            'ajax_request' => (Phpfox::getService('mfox.event')->isAdvancedModule() ? 'fevent' : 'event') . '.addFeedComment',
            'item_id' => $iEventID,
            'disable_share' => ($bCanPostComment ? false : true)
        );

        if (defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'pages.share_updates'))
        {
            $aFeedCallback['disable_share'] = true;
        }

        $this->setCallback($aFeedCallback);

        return true; 
    }

    public function __setCallbackConfigForPages($iPageId){
        if (!($aPage = Phpfox::getService('pages')->getForView($iPageId)))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('pages.the_page_you_are_looking_for_cannot_be_found')
            );
        }

        $bCanPostComment = true;
        if (Phpfox::getUserId())
        {
            $bIsBlocked = Phpfox::getService('user.block')->isBlocked($aPage['user_id'], Phpfox::getUserId());
            if ($bIsBlocked)
            {
                $bCanPostComment = false;
            }
        }           

        $aFeedCallback = array(
                'module' => 'pages',
                'table_prefix' => 'pages_',
                'ajax_request' => 'pages.addFeedComment',
                'item_id' => $aPage['page_id'],
                'disable_share' => ($bCanPostComment ? false : true),
                'feed_comment' => 'pages_comment'               
        );  
        if (defined('PHPFOX_IS_PAGES_VIEW') && !Phpfox::getService('pages')->hasPerm(null, 'pages.share_updates'))
        {
            $aFeedCallback['disable_share'] = true;
        }       

        $this->setCallback($aFeedCallback);
        return true; 
    }

    public function __setCallbackConfigForGroups($iPageId){
        if (!($aPage = Phpfox::getService('groups')->getForView($iPageId)))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  _p('The group you are looking for cannot be found.')
            );
        }

        $bCanPostComment = true;
        if (Phpfox::getUserId())
        {
            $bIsBlocked = Phpfox::getService('user.block')->isBlocked($aPage['user_id'], Phpfox::getUserId());
            if ($bIsBlocked)
            {
                $bCanPostComment = false;
            }
        }

        $aFeedCallback = array(
            'module' => 'groups',
            'table_prefix' => 'pages_',
            'ajax_request' => 'groups.addFeedComment',
            'item_id' => $aPage['page_id'],
            'disable_share' => ($bCanPostComment ? false : true),
            'feed_comment' => 'groups_comment'
        );

        $this->setCallback($aFeedCallback);
        return true;
    }
    /**
     * 
     * Create feed on wall (status, photo, check in {video, link})
     * 
     */
    public function post($aData){
        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_do_this_action"))
            );
        }
        $iUserId = Phpfox::getUserId();        
        $iPrivacy = isset($aData['iPrivacy']) ? $aData['iPrivacy'] : 0;

        //  init 
        extract($aData, EXTR_SKIP);
        if (!isset($sContent))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        $subject = null;
        // Use viewer as subject if no subject
        if (null === $subject)
        {
            $subject = $iUserId;
        }

//        $body = html_entity_decode($sContent, ENT_QUOTES, 'UTF-8');

        $body = html_entity_decode($sContent, ENT_QUOTES, 'UTF-8');


        // Tags
        /*
        $friend_tag_reg = "/(\[x\=(\w+)\@(\d+)\])([^\[]+)(\[\/x\])/mi";
        $matches = array();
        $count_matched = preg_match_all($friend_tag_reg, $body, $matches);

        if ($count_matched) {

            for ($index = 0; $index < $count_matched; ++$index) {

                $type = $matches[2][ $index ];
                $item_id = $matches[3][ $index ];
                $title = $matches[4][ $index ];

                $item = Engine_Api::_()->getItem($type, $item_id);

                if ($item) {
                    $arrTags[] = array('item_type' => $type, 'item_id' => $item_id);
                    $replacements[ $matches[0][ $index ] ] = sprintf('<a ng-url="#/app/%s/%s" href="%s">%s</a>', $type, $item_id, $item->getHref(), $title);
                } else {
                    $replacements[ $matches[0][ $index ] ] = $title;
                }
            }
        }*/

        $tag_pattern = "/(\[x\=(\w+)\@(\d+)\])([^\[]+)(\[\/x\])/mi";

        $body = preg_replace($tag_pattern, "[x=$3]$4[/x]", $body);

        /**
         * Fix for fevent type
         * @todo refactor
         */
        if (isset($aData['sSubjectType']) && $aData['sSubjectType'] == 'fevent') {
            $this->_oReq->set('event_module_id', 'fevent');
        }

        // set up action variable
        $action = null;
        $attachmentData = $aData['aAttachment'];

        // process for event/pages
        if(isset($aData['sSubjectType']) 
            && !$attachmentData 
        ) {
            if($aData['sSubjectType'] == 'event' || $aData['sSubjectType'] == 'fevent'){
                return Phpfox::getService('mfox.event')->addFeedComment($aData);
            } else if ($aData['sSubjectType'] == 'pages'){
                return Phpfox::getService('mfox.pages')->addFeedComment($aData);
            } else if ($aData['sSubjectType'] == 'groups'){
                return Phpfox::getService('mfox.groups')->addFeedComment($aData);
            } else if ($aData['sSubjectType'] == 'directory'){
                return Phpfox::getService('mfox.directory')->addFeedComment($aData);
            }
        }

        $sSubjectType = $aData['sSubjectType'];
        $iSubjectId = $aData['iSubjectId'];

        if(!$attachmentData) { // no attachment, simply update status
            $iViewedUserId = $aData['iSubjectId'];

            if(Phpfox::getUserId() != $iViewedUserId) { // we are commenting on other's profile
                $aParam = array(
                    'iParentUserId' => $iViewedUserId,
                    'sContent' => $body
                );
                return $this->addComment($aParam);

            } else {
                $ret = $this->__updateStatus($body, 'user_status', $iPrivacy);
                //  user.updateStatus
                if($ret['error_code'] == '1'){
                    return $ret;
                } else {
                    $feed = $this->__get(array('iActionId' => $ret['id']));

                    return array_merge(array(
                        'error_code' => 0,
                        'error_message' => "",
                        'iShare' => 1
                    )
                    , $feed[0]);
                };
            }

        } else {

        }
        // attachment type
        $type = $attachmentData['type'];

        // Correct type
        if ($type == 'photo' && Phpfox::getService('mfox.photo')->isAdvancedModule()) {
            $type = 'advancedphoto';
        }

        // MinhTA: as I understand, feed/post used to post user status
        switch ($type) {
            case 'status': // currently, no type of this case
                break;
            case 'comment':
                //  feed.addComment
                //  post feed on event/group/user
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_yet"))
                );
                break;
            case 'advancedphoto':
                if (!Phpfox::getUserParam('advancedphoto.photo_must_be_approved') && Phpfox::isModule('feed')) {
                    $aCallback = (!empty($sSubjectType) ? (Phpfox::hasCallback($sSubjectType, 'addPhoto') ? Phpfox::callback($sSubjectType . '.addPhoto', $iSubjectId) : null) : null);
                    $sCallbackModule = null;
                    $iCallbackItemId = null;

                    if ($aCallback !== null) {
                        $sCallbackModule = $aCallback['module'];
                        $iCallbackItemId = $aCallback['item_id'];
                    }

                    $aCallback = ($sCallbackModule ? (Phpfox::hasCallback($sCallbackModule, 'addPhoto') ? Phpfox::callback($sCallbackModule . '.addPhoto', $iCallbackItemId) : null) : null);

                    if ($sSubjectType == 'user') {
                        $iParentUserId = isset($iSubjectId) ? (int) $iSubjectId : 0;
                        $aCallback = null;

                        if ($iParentUserId == Phpfox::getUserId()) {
                            $iParentUserId = 0;
                        }
                    }

                    if ($sSubjectType == 'event' || $sSubjectType == 'fevent' || $sSubjectType == 'pages' || $sSubjectType == 'directory' || $sSubjectType == 'groups') {
                        $iParentUserId = isset($iSubjectId) ? (int) $iSubjectId : 0;
                    }

                    $ids = explode(',', $attachmentData['photo_id']);

                    if (!empty($attachmentData['ids'])) {
                        $ids = explode(',', $attachmentData['ids']);
                    }

                    $photoPrivacy = isset($aData['iPrivacy']) ? $aData['iPrivacy'] : 0;
                    $photoPrivacyComment = 0;

                    if (count($ids) > 0) {
                        $sPhotoType = 'advancedphoto';
                        foreach ($ids as $id) {
                            $iFeedId = Phpfox::getService('feed.process')->callback($aCallback)->add($sPhotoType, $id, $photoPrivacy, $photoPrivacyComment, (int) $iParentUserId);

                            if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support')) {
                                Phpfox::getService('tag.process')->add($sPhotoType, $id, Phpfox::getUserId(), $body, true);
                            }
                        }

                        if (isset($aData['aLocation']) && !empty($aData['aLocation'])) {
                            $sLocation = $aData['aLocation']['sLocation'];
                            $fLatitude = $aData['aLocation']['fLatitude'];
                            $fLongitude = $aData['aLocation']['fLongitude'];

                            $location_latlng = $fLatitude . ';' . $fLongitude;

                            $this->database()->update(Phpfox::getT('photo'), array(
                                'location_latlng' => $location_latlng,
                                'location_name' => $sLocation,
                            ), 'photo_id IN (' . implode(',', $ids) . ')');
                        }

                        $this->database()->update(Phpfox::getT('photo_info'), array(
                            'description' => $body,
                        ), 'photo_id IN (' . implode(',', $ids) . ')');

                        $aFeedData = array(
                            'iActionId' => $iFeedId,
                            'bIsGetOneFeed' => true,
                            'sParentId' => isset($sSubjectType) ? $sSubjectType : 'feed',
                        );

                        $feed = $this->get($aFeedData);

                        return array_merge(array(
                            'error_code' => 0,
                            'error_message' => '',
                            'iShare' => 1,
                        ), $feed[0]);
                    } else {
                        return array(
                            'error_code' => 1,
                            'error_message' => html_entity_decode(Phpfox::getPhrase('mfox.please_provide_list_of_photo_id')),
                        );
                    }
                } else {
                    return array(
                        'error_code' => 1,
                        'error_message' => html_entity_decode(Phpfox::getPhrase('mfox.photo_must_be_approved_first')),
                    );
                }
                break;
            case 'photo':
                //  photo/frame, photo.process
                if (!Phpfox::getUserParam('photo.photo_must_be_approved') && Phpfox::isModule('feed'))
                {
                    $aCallback = (!empty($sSubjectType) 
                        ? (Phpfox::hasCallback($sSubjectType, 'addPhoto') 
                            ? Phpfox::callback($sSubjectType . '.addPhoto', $iSubjectId) 
                            : null) 
                        : null);
                    $sCallbackModule = null;
                    $iCallbackItemId = null;

                    if ($aCallback !== null)
                    {
                        $sCallbackModule = $aCallback['module'];
                        $iCallbackItemId = $aCallback['item_id'];
                    }

                    $aCallback = ($sCallbackModule ? (Phpfox::hasCallback($sCallbackModule, 'addPhoto') ? Phpfox::callback($sCallbackModule . '.addPhoto', $iCallbackItemId) : null) : null);

                    if($sSubjectType == 'user'){
                        $iParentUserId = isset($iSubjectId) ? (int) $iSubjectId : 0;
                        $aCallback = null;

                        if($iParentUserId == Phpfox::getUserId()){
                            $iParentUserId =  0;
                        }
                    }

                    if($sSubjectType == 'event' || $sSubjectType == 'fevent' || $sSubjectType == 'pages' || $sSubjectType == 'directory'){
                        $iParentUserId = isset($iSubjectId) ? (int) $iSubjectId : 0;
                    }

                    $ids = explode(',', $attachmentData['photo_id']);

                    if(!empty($attachmentData['ids'])){
                        $ids = explode(',', $attachmentData['ids']);
                    }

                    $photoPrivacy = isset($aData['iPrivacy']) ? $aData['iPrivacy'] : 0;
                    $photoPrivacyComment = 0;

                     //aLocation":{"sLocation":"The Fairmont San Francisco Hotel","fLatitude":37.792395,"fLongitude":-122.410504}



                    if(count($ids) > 0){
                        $lastID = count($ids) - 1 ;



                        // since 3.08p2
                        // use correct phototype
                        $sPhotoType = 'photo';

                        if(isset($aData['aLocation']) && !empty($aData['aLocation'])){
                            $sLocation = $aData['aLocation']['sLocation'];
                            $fLatitude =  $aData['aLocation']['fLatitude'];
                            $fLongitude = $aData['aLocation']['fLongitude'];

                            $location_latlng = $fLatitude . ';' .  $fLongitude;



                            $this->database()->update(Phpfox::getT('photo'), array(
                                'location_latlng'=>$location_latlng,
                                'location_name'=>$sLocation
                            ), 'photo_id='. (int) $ids[$lastID]);
                        }

                        // support hashtags
                        // since 3.08p2
                        // apply following condition to re-check.
                        // && Phpfox::getUserParam('photo.can_add_tags_on_photos') && !empty($body)
                        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support') )
                        {
                            Phpfox::getService('tag.process')->add($sPhotoType, $ids[$lastID], Phpfox::getUserId(), $body, true);
                        }

                        $this->database()
                            ->update(Phpfox::getT('photo_info'), array(
                                'description'=> $body,
                            ), 'photo_id='. (int) $ids[$lastID]);

                        $aPhoto = array(
                            'photo_id' => $ids[$lastID]
                        , 'privacy' => $photoPrivacy
                        , 'privacy_comment' => $photoPrivacyComment
                        );


                        (Phpfox::isModule('feed') ? $iFeedId = Phpfox::getService('feed.process')
                            ->callback($aCallback)
                            ->add($sPhotoType
                                , $aPhoto['photo_id']
                                , $aPhoto['privacy']
                                , $aPhoto['privacy_comment']
                                , (int) $iParentUserId) : null
                            );

                        if (!$sCallbackModule)
                        {
                            foreach($ids as $photo_id){
                                if($ids[$lastID] == $photo_id){
                                    continue;
                                }

                                Phpfox::getLib('database')->insert(Phpfox::getT('photo_feed'), array(
                                    'feed_id' => $iFeedId,
                                    'photo_id' => $photo_id)
                                );                                
                            }
                        }

                        $aFeedData = array(
                            'iActionId' => $iFeedId,
                            'bIsGetOneFeed' => true,
                            'sParentId' => isset($sSubjectType) ? $sSubjectType : 'feed'
                        );

                        $feed = $this->get($aFeedData);

                        return array_merge(array(
                            'error_code' => 0,
                            'error_message' => "",
                            'iShare' => 1
                        )
                        , $feed[0]);                                            
                    } else {
                        return array(
                            'error_code' => 1,
                            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.please_provide_list_of_photo_id"))
                        );                                            
                    }
                } else {
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.photo_must_be_approved_first"))
                    );                    
                }
                break;
            case 'link':
                //  link.preview, link.addViaStatusUpdate
                $uri = $attachmentData['uri'];
                $title = $attachmentData['title'];
                $description = $attachmentData['description'];
                $thumb = $attachmentData['thumb'];

                $aLink = Phpfox::getService('link')->getLink($uri);
				
                if(!$aLink){
                    return array(
                        'error_code' => 1,
                        'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.it_is_not_link_please_provide_again"))
                    );                    
                } else {
                    $aCallback = null;
                    $iParentUserId = null;
                    if (isset($sSubjectType) && Phpfox::hasCallback($sSubjectType, 'addLink'))
                    {
                        $aCallback = Phpfox::callback($sSubjectType . '.addLink', array(
                            'callback_item_id' => $iSubjectId
                        ));  
                    }
					
					       
                    if($sSubjectType == 'user'){
                        $iParentUserId = isset($iSubjectId) ? (int) $iSubjectId : 0;

                        if($iParentUserId ==  Phpfox::getUserId()){
                            $iParentUserId = 0;
                        }
                        $aCallback = null;
                    }

                    if($sSubjectType == 'event' || $sSubjectType == 'fevent' || $sSubjectType == 'pages' || $sSubjectType == 'directory'){
                        $iParentUserId = isset($iSubjectId) ? (int) $iSubjectId : 0;
                    }

                    $aVals = array(
                        'link' => array(
                            'url' => $uri
                            , 'image_hide' => 0
                            , 'image' => $thumb
                            , 'title' => $title
                            , 'description' => $description
                            )
                        , 'privacy' => $iPrivacy 
                        , 'privacy_comment' => 0
                        , 'status_info' => $sContent
                        , 'parent_user_id' => $iParentUserId
                        );
						

                    $ret = Phpfox::getService('mfox.link')->add($aVals, false, $aCallback);
					
					
                    if(isset($ret['feedID']))
                    {
                        $aFeedData = array(
                            'iActionId' => $ret['feedID'],
                            'bIsGetOneFeed' => true,
                            'sParentId' => isset($sSubjectType) ? $sSubjectType : 'feed'
                        );

                        $feed = $this->get($aFeedData);

                        return array_merge(array(
                            'error_code' => 0,
                            'error_message' => "",
                            'iShare' => 1
                        ), $feed[0]);                                            
                    } else {
                        return array(
                            'error_code' => 1,
                            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.there_is_error_when_processing_please_try_again"))
                        );                                            
                    }
                }
                break;
            case 'music':
                //  music/frame, music.displayFeed
                $song_id = $attachmentData['song_id'];
                if (!Phpfox::getUserParam('music.music_song_approval') && isset($song_id))
                {   
                    $aCallback = null;
                    $iParentUserId = null;
                    $aCallback = Phpfox::callback($sSubjectType . '.uploadSong', $iSubjectId);   
                    if($sSubjectType == 'user'){
                        $iParentUserId = isset($iSubjectId) ? (int) $iSubjectId : 0;
                        $aCallback = null;
                    }

                    $songPrivacy = 0;
                    $songPrivacyComment = 0;

                    (Phpfox::isModule('feed') ? $iFeedId = Phpfox::getService('feed.process')->callback($aCallback)
                        ->add('music_song'
                            , $song_id
                            , $songPrivacy
                            , $songPrivacyComment
                            , (isset($iParentUserId) ? (int) $iParentUserId : '0')) : null);

                    if($iFeedId){
                        $feed = $this->__get(array('iActionId' => $iFeedId));

                        return array_merge(array(
                            'error_code' => 0,
                            'error_message' => "",
                            'iShare' => 1
                        )
                        , $feed[0]);                                            
                    } else {
                        return array(
                            'error_code' => 1,
                            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.there_is_error_when_processing_please_try_again"))
                        );

                    }
                }
                break;
            case 'videochannel': 
            case 'video': 
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_yet"))
                );
                break;
            
            default:
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_support_yet"))
                );

                break;
        }                
    }

    public function _hashSearch($sTag)
    {

//        $sTag =  isset($_REQUEST['sHashTag'])? $_REQUEST['sHashTag']: null;

        if(empty($sTag)){
            return ;
        }

        $sTag = Phpfox::getLib('parse.input')->clean($sTag, 255);

        $this->database()->join(Phpfox::getT('tag'), 'hashtag', 'hashtag.item_id = feed.item_id AND hashtag.category_id = feed.type_id AND (tag_text = \'' . Phpfox::getLib('database')->escape($sTag) . '\' OR tag_url = \''. Phpfox::getLib('database')->escape($sTag) .'\')');
    }

    public function __updateStatus($sContent, $sTypeId = 'user_status', $iPrivacy, $iPrivacyComment){
        if (!isset($sTypeId))
        {
            $sTypeId = 'user_status';
        }

        if (!isset($iPrivacyComment))
        {
            $iPrivacyComment = 0;
        }

        if (!isset($iPrivacy))
        {
            $iPrivacy = 0;
        }

        $aVals = array(
            'user_status' => $sContent,
            'privacy' => $iPrivacy,
            'privacy_comment' => $iPrivacyComment
        );

        if (Phpfox::getLib('parse.format')->isEmpty($aVals['user_status']))
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('user.add_some_text_to_share')
            );
        }

        if (!Phpfox::getService('ban')->checkAutomaticBan($aVals['user_status']))
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.your_account_has_been_banned"))
            );
        }
        $sStatus = $this->preParse()->prepare($aVals['user_status']);
        $aUpdates = $this->database()->select('content')
                ->from(Phpfox::getT('user_status'))
                ->where('user_id = ' . (int) Phpfox::getUserId())
                ->limit(Phpfox::getParam('user.check_status_updates'))
                ->order('time_stamp DESC')
                ->execute('getSlaveRows');

        $iReplications = 0;
        foreach ($aUpdates as $aUpdate)
        {
            if ($aUpdate['content'] == $sStatus)
            {
                $iReplications++;
            }
        }

        if ($iReplications > 0)
        {
            return array(
                'error_code' => 1,
                'error_message' =>  Phpfox::getPhrase('user.you_have_already_added_this_recently_try_adding_something_else')
            );
        }

        if (empty($aVals['privacy']))
        {
            $aVals['privacy'] = 0;
        }

        if (empty($aVals['privacy_comment']))
        {
            $aVals['privacy_comment'] = 0;
        }
        
        #Insert vals
        $aInsert = array(
            'user_id' => (int) Phpfox::getUserId(),
            'privacy' => $aVals['privacy'],
            'privacy_comment' => $aVals['privacy_comment'],
            'content' => $sStatus,
            'time_stamp' => PHPFOX_TIME
        );
        
        if (!empty($sLatitude) && !empty($sLongitude))
        {
            $sLatitude = floatval($sLatitude);
            $sLongitude = floatval($sLongitude);
            $aInsert['location_latlng'] = json_encode(array('latitude' => $sLatitude, 'longitude' => $sLongitude));
        }
        
        if (!empty($aInsert['location_latlng']) && !empty($sLocationName))
        {
            $aInsert['location_name'] = Phpfox::getLib('parse.input')->clean($sLocationName);
        }
        
        $iStatusId = $this->database()->insert(Phpfox::getT('user_status'), $aInsert);

        if (isset($aVals['privacy']) && $aVals['privacy'] == '4')
        {
            Phpfox::getService('privacy.process')->add('user_status', $iStatusId, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : array()));
        }

        Phpfox::getService('user.process')->notifyTagged($sStatus, $iStatusId, 'status');

        $iId = Phpfox::getService('feed.process')->allowGuest()->add('user_status', $iStatusId, $aVals['privacy'], $aVals['privacy_comment'], 0, null, 0, (isset($aVals['parent_feed_id']) ? $aVals['parent_feed_id'] : 0), (isset($aVals['parent_module_id']) ? $aVals['parent_module_id'] : null));

        if (Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_hashtag_support'))
        {
            Phpfox::getService('tag.process')->add('user_status', $iStatusId, Phpfox::getUserId(), $sStatus, true);
        }

        if ($iId)
        {
            return array(
                'error_code' => 0,
                'iItemId' => 0, 
                'id' => $iId, 
            );        
        }

        $sMessage = '';
        $aErrorMessage = Phpfox_Error::get();
        foreach ($aErrorMessage as $sErrorMessage)
        {
            $sMessage .= $sErrorMessage;
        }

        return array(
            'error_code' => 1,
            'error_message' => $sMessage
        );        
    }

    public function __deleteFeed($iId, $sModule = null, $iItem = 0)
    {
        $aCallback = null;
        if (!empty($sModule))
        {
            if (Phpfox::hasCallback($sModule, 'getFeedDetails'))
            {
                $aCallback = Phpfox::callback($sModule . '.getFeedDetails', $iItem);
            }
        }
        $aFeed = Phpfox::getService('feed')->callback($aCallback)->getFeed($iId);

        if (!isset($aFeed['feed_id']))
        {
            return false;
        }

        // Check the privacy.
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check($aFeed['type_id'], $aFeed['feed_id'], $aFeed['user_id'], $aFeed['privacy'], $aFeed['is_friend'], true))
        {
            return false;
        }

        // module/directory/include/plugin/feed.service_process_deletefeed
        if (!empty($sModule) && $sModule == 'directory')
        {
            $aBusiness = Phpfox::getService('directory')->getBusinessForEdit($aFeed['parent_user_id'], true);
            if (isset($aBusiness['business_id']) && $aBusiness['user_id'] == Phpfox::getUserId())
            {
                define('PHPFOX_FEED_CAN_DELETE', true);
            }
        }

        // module/pages/include/plugin/feed.service_process_deletefeed
        if (!empty($sModule) && $sModule == 'pages')
        {
            $aPage = Phpfox::getService('pages')->getPage($aFeed['parent_user_id']);
            if (isset($aPage['page_id']) && Phpfox::getService('pages')->isAdmin($aPage))
            {
                define('PHPFOX_FEED_CAN_DELETE', true);
            }
        }

        // module/event/include/plugin/feed.service_process_deletefeed
        if (!empty($sModule) && $sModule == 'event')
        {
            $aEvent = Phpfox::getService('event')->getForEdit($aFeed['parent_user_id'], true);
            if (isset($aEvent['event_id']) && $aEvent['user_id'] == Phpfox::getUserId())
            {
                define('PHPFOX_FEED_CAN_DELETE', true);
            }
        }

        // module/fevent/include/plugin/feed.service_process_deletefeed
        if (!empty($sModule) && $sModule == 'fevent')
        {
            $aEvent = Phpfox::getService('fevent')->getForEdit($aFeed['parent_user_id'], true);
            if (isset($aEvent['event_id']) && $aEvent['user_id'] == Phpfox::getUserId())
            {
                define('PHPFOX_FEED_CAN_DELETE', true);
            }
        }

        // module/user/include/plugin/feed.service_process_deletefeed
        if (empty($sModule) && $aFeed['parent_user_id'] == Phpfox::getUserId())
        {
            define('PHPFOX_FEED_CAN_DELETE', true);
        }

        $bCanDelete = false;
        if (Phpfox::getUserParam('feed.can_delete_own_feed') && ($aFeed['user_id'] == Phpfox::getUserId()))
        {
            $bCanDelete = true;
        }

        if (defined('PHPFOX_FEED_CAN_DELETE'))
        {
            $bCanDelete = true;
        }

        if (Phpfox::getUserParam('feed.can_delete_other_feeds'))
        {
            $bCanDelete = true;
        }

        if($aFeed['type_id'] == 'feed_comment' && $aFeed['parent_user_id'] == Phpfox::getUserId()) {
            $bCanDelete = true;
        }

        if ($bCanDelete === true)
        {
            if (isset($aCallback['table_prefix']))
            {
                $this->database()->delete(Phpfox::getT($aCallback['table_prefix'] . 'feed'), 'feed_id = ' . (int) $iId);
            }

            $this->database()->delete(Phpfox::getT('feed'), 'user_id = ' . $aFeed['user_id'] . ' AND time_stamp = ' . $aFeed['time_stamp']);

            // Delete likes that belonged to this feed
            $this->database()->delete(Phpfox::getT('like'), 'type_id = "' . $aFeed['type_id'] . '" AND item_id = ' . $aFeed['item_id']);

            if (!empty($sModule))
            {
                if (Phpfox::hasCallback($sModule, 'deleteFeedItem'))
                {
                    Phpfox::callback($sModule . '.deleteFeedItem', $iItem);
                }
            }

            return true;
        } else {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_delete_this_action"))
            );        
        }

        return false;
    }    

    public function __deleteComment($commentID){
        $aComment = Phpfox::getService('mfox.comment')->getCommentByID($commentID);

        if (!isset($aComment['comment_id']))
        {
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.comment_does_not_exist_or_has_been_deleted")),
                'error_code' => 1,
                'result' => 0
            );
        }

        return $ret = Phpfox::getService('comment.process')->deleteInline($aComment['comment_id'], $aComment['type_id']);
    }

    /**
     * Input data:
     * + iActionId: int, required.
     * + sText: string, required
     *
     * Output data:
     * + iLastId: int
     * + error_code: int.
     * + error_message: string.
     * + result: int.
     */
    public function comment($aData)
    {
        //  init 
        $sText = isset($aData['sText']) ? $aData['sText'] : "";
        $iActionId = isset($aData['iActionId']) ? (int)$aData['iActionId'] : 0;
        $sParentId = isset($aData['sParentId']) ? $aData['sParentId'] : '';
        if (trim($sText) == "" || !$iActionId)
        {
            return array(
                'error_code' => 1,
                'error_elements' => 'sText or iActionId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.add_some_text_to_your_comment")),
                'result' => 0
            );
        }
        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_comment_on_this_action")),
                'result' => 0
            );
        }
        $iUserID = Phpfox::getUserId();

        //  process 
        if($sParentId && $sParentId != 'feed') {
            if($sParentId == 'groups')
            {
                $feed = Phpfox::getService('feed')->callback(array(
                    'table_prefix' => 'pages_' 
                ))->getFeed($iActionId);
            }
            else{
                $feed = Phpfox::getService('feed')->callback(array(
                    'table_prefix' => $sParentId . '_' 
                ))->getFeed($iActionId);
            }
        } else {
            $feed = Phpfox::getService('feed')->getFeed($iActionId);
        }

        if(!isset($feed['feed_id'])){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.activity_does_not_exist")),
                'result' => 0
            );
        }

        $aVals = array(
            'iItemId' => $feed['item_id']
            , 'iParentItemId' => $iActionId
            , 'sText' => $sText
            , 'sItemType' => $feed['type_id']
            );

        $ret = Phpfox::getService('mfox.comment')->add($aVals);

        if(isset($ret['lastid'])){
            return $ret;     
        } else if(false === $ret){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_action_not_found")),
                'result' => 0
            );        
        } else {
            return $ret;
        }

        //  end 
        return array(
            'error_code' => 1,
            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.opps_the_action_not_found")),
            'result' => 0
        );        
    }

    /**
     * Input data:
     * + iActionId: int, required.
     * + iLastCommentIdViewed: int, optional.
     * + iAmountOfComment: int, optional.
     *
     * Output data:
     * + iLikeId: int
     * + iUserId: int
     * + sFullName: string
     * + sImage: string
     *
     */
    public function listallcomments($aData){
        //  init 
        $iActionId = isset($aData['iActionId']) ? $aData['iActionId'] : 0;
        $iLastCommentIdViewed = isset($aData['iLastCommentIdViewed']) ? (int)$aData['iLastCommentIdViewed'] : 0;
        $iAmountOfComment = (isset($aData['iAmountOfComment']) && (int)$aData['iAmountOfComment'] > 0) ? (int)$aData['iAmountOfComment'] : 10;        
        $sParentId = isset($aData['sParentId']) ? $aData['sParentId'] : '';

        if ($iActionId < 1)
        {
            return array(
                'error_code' => 1,
                'error_element' => 'iActionId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }

        if(!$sParentId || $sParentId == 'feed') {
            $iUserID = Phpfox::getUserId();

            //  process 
            $feed = Phpfox::getService('feed')->getFeed($iActionId);
            if(!isset($feed['feed_id'])){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.activity_does_not_exist"))
                );
            }

            $aVals = array(
                'sItemType' => $feed['type_id']
                , 'iItemId' => $feed['item_id']
                , 'sModule' => ''
                , 'iItem' => ''
                , 'iLastCommentIdViewed' => $iLastCommentIdViewed
                , 'iAmountOfComment' => $iAmountOfComment
            );
        } else {
            // for: event/fevent/pages
            $feed = Phpfox::getService('feed')->callback(array(
                'table_prefix' => ($sParentId == 'groups') ? 'pages_' : $sParentId . '_'
            ))->getFeed($iActionId);

            if(!isset($feed['feed_id'])){
                return array(
                    'error_code' => 1,
                    'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.activity_does_not_exist"))
                );
            }

            if($feed['type_id'] == 'link') {
                if('pages' == $sParentId || 'groups' == $sParentId || 'directory' == $sParentId){
                    $sParentId = ($sParentId == 'groups') ? 'pages_link' : $sParentId . '_link';                    
                } else {
                    $sParentId = (Phpfox::getService('mfox.event')->isAdvancedModule() ? 'fevent' : 'event') . '_link';                    
                }
            }

            $aVals = array(
                'sItemType' => $feed['type_id']
                , 'iItemId' => $feed['feed_id']
                , 'sParentId' => $sParentId
                , 'sModule' => ''
                , 'iItem' => ''
                , 'iLastCommentIdViewed' => $iLastCommentIdViewed
                , 'iAmountOfComment' => $iAmountOfComment
            );

            return $ret = Phpfox::getService('mfox.comment')->listallcomments($aVals); // I work on the case of sParent with no idea of this function so I pass all data as original
        }

        return $ret = Phpfox::getService('mfox.comment')->listallcomments($aVals, $feed);
    }

    /**
     * Input data: (get new like)
     * + iActionId: int, required.
     * + iLastLikeIdViewed: int, optional.
     * + iAmountOfLike: int, optional.
     *
     * Output data:
     * + iLikeId: int
     * + iUserId: int
     * + sFullName: string
     * + sImage: string
     *
     */
    public function listalllikes($aData)
    {
        //  init 
        $iActionId = isset($aData['iActionId']) ? (int)$aData['iActionId'] : 0;
        $lastLikeIdViewed = isset($aData['iLastLikeIdViewed']) ? (int)$aData['iLastLikeIdViewed'] : 0;
        $amountOfLike = (isset($aData['iAmountOfLike']) && (int)$aData['iAmountOfLike'] > 0 ) ? (int)$aData['iAmountOfLike'] : 20;

        if(isset($aData['sParentId'])){
            if('event' == $aData['sParentId'] 
                || 'fevent' == $aData['sParentId']
                || 'pages' == $aData['sParentId']
                || 'directory' == $aData['sParentId']
            ){
                $sItemType = $aData['sParentId'];
                if('event' == $sItemType 
                    || 'fevent' == $sItemType){
                    $sItemType = (Phpfox::getService('mfox.event')->isAdvancedModule() ? 'fevent' : 'event');
                }
                return $ret = Phpfox::getService('mfox.like')->listalllikes(array(
                    'sItemType' => $sItemType . '_comment', 
                    'iItemId' => $iActionId,
                    'iLastLikeIdViewed' => $lastLikeIdViewed,
                    'iAmountOfLike' => $amountOfLike
                ));    
            }
        }
        $sParentId = isset($aData['sParentId']) ? (int)$aData['sParentId'] : 0;

        if ($iActionId < 1)
        {
            return array(
                'error_code' => 1,
                'error_element' => 'iActionId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        
        //  process 
        $feed = Phpfox::getService('feed')->getFeed($iActionId);
        if(!isset($feed['feed_id'])){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.activity_does_not_exist"))
            );
        }

        return $ret = Phpfox::getService('mfox.like')->listalllikes(array(
            'sItemType' => $feed['type_id'], 
            'iItemId' => $feed['item_id'],
            'iLastLikeIdViewed' => $lastLikeIdViewed,
            'iAmountOfLike' => $amountOfLike
        ));    
    }

    public function listalldislikes($aData){
        //  init 
        $iActionId = isset($aData['iActionId']) ? (int)$aData['iActionId'] : 0;
        $iLastDislikeIdViewed = isset($aData['iLastDislikeIdViewed']) ? (int)$aData['iLastDislikeIdViewed'] : 0;
        $iAmountOfDislike = (isset($aData['iAmountOfDislike']) && (int)$aData['iAmountOfDislike'] > 0 ) ? (int)$aData['iAmountOfLike'] : 20;
        if ($iActionId < 1)
        {
            return array(
                'error_code' => 1,
                'error_element' => 'iActionId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.parameters_is_not_valid"))
            );
        }
        
        //  process 
        $feed = Phpfox::getService('feed')->getFeed($iActionId);
        if(!isset($feed['feed_id'])){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.activity_does_not_exist"))
            );
        }

        return $ret = Phpfox::getService('mfox.like')->dislikelistalldislike(array(
            'sItemType' => $feed['type_id'], 
            'iItemId' => $feed['item_id'],
            'iLastDislikeIdViewed' => $iLastDislikeIdViewed,
            'iAmountOfDislike' => $iAmountOfDislike
        ));            
    }

    /**
    * + iMinId: int, required.
    * + iItemId: int, optional.
    * + sItemType: string, optional.
    * Output data:
    * + iTotalFeedUpdate: int
    */
    public function getupdate($aData){
        if (!isset($aData['iMinId']))
        {
            return array(
                'error_code' => 1,
                'error_element' => 'iMinId',
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.iminid_is_not_valid"))
            );
        }

        $feeds = $this->__get($aData);
        return array('iTotalFeedUpdate' => count($feeds));        
    }

    public function fetch($aData){
        
        if(isset($aData['iLimit']))
        {
            $aData['iAmountOfFeed'] =  $aData['iLimit'];
        }

        $sAction =  isset($aData['sAction'])?$aData['sAction']:'new';

        $sAction = $sAction == 'new'?'new':'more';

        if($sAction == 'more'){
            if(isset($aData['iMinId'])){
                unset($aData['iMinId']);
            }
        }

        if($sAction =='new'){
            if(isset($aData['iMaxId'])){
                unset($aData['iMaxId']);
            }
        }

        if(isset($aData['iMaxId'])){
            $aData['iMaxId'] = $aData['iMaxId'] -1;
        }

        if(isset($aData['iMinId']) && $aData['iMinId'] > 0){
            $aData['iMinId'] = $aData['iMinId'] +1;
        }

        $aItems = $this->get($aData);

        if ($sAction == 'more')
        {
            while(count($aItems) == 0)
            {
                $iNextTryMaxId = $aData['iMaxId'] - $aData['iAmountOfFeed'];
                if ($iNextTryMaxId <= 0)
                {
                    break;
                }

                $aData['iMaxId'] = $iNextTryMaxId;
                $aItems = $this->get($aData);
            }
        }

        return $aItems;
    }    

    public function changeType($sType)    
    {
        switch ($sType) {
            case 'music_album':
                $sType = 'music_song';
                break;

            default:
                break;
        }

        return $sType;
    }

    public function formstatus($aData){
        $response  =  array(
            'bCanUploadVideo'=> Phpfox::getService('mfox.video')->canUpload($aData),
            'bCanUploadMultiPhotos' => !Phpfox::getService('mfox.photo')->isAdvancedModule(),
        );
        
        return $response;
    }
}

