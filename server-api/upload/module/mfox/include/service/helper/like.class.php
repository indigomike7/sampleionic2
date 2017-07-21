<?php

/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Helper_Like extends Phpfox_Service
{
	private $_iTotalLikeCount = 0;
	
	
	public function isLiked($sType, $iItemId){
		
		$iUserId  = Phpfox::getUserId();
		$iCheck = $this->database()->select('COUNT(*)')
			->from(Phpfox::getT('like'))
			->where('type_id = \'' . $this->database()->escape($sType) . '\' AND item_id = ' . (int) $iItemId . ' AND user_id = ' . $iUserId)
			->execute('getField');
			
		return $iCheck =="0" ? 0 : 1;
	}

	public function add($sType, $iItemId, $iUserId = null)
	{
		$bIsNotNull = false;
		if ($iUserId === null) 
		{
			$iUserId = Phpfox::getUserId();
			$bIsNotNull = true;
		}
		if($sType == 'pages')
		{
			$bIsNotNull = false;
		} 
		
		// check if iUserId can Like this item
		$aFeed = $this->database()->select('privacy, user_id')
			->from(Phpfox::getT('feed'))
			->where('item_id = ' . (int)$iItemId . ' AND type_id = "' . Phpfox::getLib('parse.input')->clean($sType) . '"')
			->execute('getSlaveRow');
		/*
		if (empty($aFeed))
		{
			return Phpfox_Error::display('Item does not exist.');
		}
		*/

		if (!empty($aFeed) && isset($aFeed['privacy']) && !empty($aFeed['privacy']) && !empty($aFeed['user_id']) && $aFeed['user_id'] != $iUserId)
		{
			if ($aFeed['privacy'] == 1 && Phpfox::getService('friend')->isFriend($iUserId, $aFeed['user_id']) != true)
			{
				return Phpfox_Error::set('Not allowed to like this item.');
			}
			else if ($aFeed['privacy'] == 2 && Phpfox::getService('friend')->isFriendOfFriend($iUserId) != true)
			{
				return Phpfox_Error::set('Not allowed to like this item.');
			}
			else if ($aFeed['privacy'] == 4 && ( $bCheck = Phpfox::getService('privacy')->check($sType, $iItemId, $aFeed['user_id'], $aFeed['privacy'], null, true)) != true)
			{	
				return Phpfox_Error::set('Not allowed to like this item.');
			}
		}
		
		$iCheck = $this->database()->select('COUNT(*)')
			->from(Phpfox::getT('like'))
			->where('type_id = \'' . $this->database()->escape($sType) . '\' AND item_id = ' . (int) $iItemId . ' AND user_id = ' . $iUserId)
			->execute('getField');
		
		if ($iCheck)
		{
			$errorStr = html_entity_decode(Phpfox::getPhrase('feed.you_have_already_liked_this_feed'));
			return Phpfox_Error::set( $errorStr);
		}		
		
		$iCnt = (int) $this->database()->select('COUNT(*)')	
			->from(Phpfox::getT('like_cache'))
			->where('type_id = \'' . $this->database()->escape($sType) . '\' AND item_id = ' . (int) $iItemId . ' AND user_id = ' . (int) $iUserId)
			->execute('getSlaveField');
		
		$this->database()->insert(Phpfox::getT('like'), array(
				'type_id' => $sType,
				'item_id' => (int) $iItemId,
				'user_id' => $iUserId,
				'time_stamp' => PHPFOX_TIME
			)
		);
		$iCnt = 0;
		if (!$iCnt)
		{
			$this->database()->insert(Phpfox::getT('like_cache'), array(
					'type_id' => $sType,
					'item_id' => (int) $iItemId,
					'user_id' => $iUserId
				)
			);				
		}
		
		Phpfox::getService('feed.process')->clearCache($sType, $iItemId);
		
		if ($sPlugin = Phpfox_Plugin::get('like.service_process_add__1')){eval($sPlugin);}
		
		
		Phpfox::callback($sType . '.addLike', $iItemId, ($iCnt ? true : false), ($bIsNotNull ? null : $iUserId));
		
		return true;
	}


    public function getLikesForFeed($sType, $iItemId, $bIsLiked = false, $iLimit = 4, $bLoadCount = false)
	{
		if ($bIsLiked)
		{
			// $iLimit--;
		}
		
		$sWhere = '(l.type_id = \'' . $this->database()->escape(str_replace('-','_',$sType)) . '\' OR l.type_id = \'' . str_replace('_','-',$sType) . '\') AND l.item_id = ' . (int) $iItemId;
		
		if (Phpfox::getParam('like.show_user_photos'))
		{
			$this->database()->where($sWhere);
		}
		else
		{
			$this->database()->where($sWhere);
			/*if (Phpfox::getParam('feed.cache_each_feed_entry'))
			{
				$this->database()->where($sWhere);
			}
			else
			{
				//$this->database()->where($sWhere . ' AND l.user_id != ' . Phpfox::getUserId());
			}*/
		}
		
		$aRowLikes = $this->database()->select('l.*, ' . Phpfox::getUserField() .', a.time_stamp as action_time_stamp')
			->from(Phpfox::getT('like'), 'l')
			->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')			
            ->leftjoin(Phpfox::getT('action'), 'a', 'a.item_id = l.item_id AND a.user_id = l.user_id AND a.item_type_id = "' . str_replace('_', '-', $this->database()->escape($sType)) .'"')
			->order('l.time_stamp DESC')
			->group('u.user_id')
			->limit(100)
			->execute('getSlaveRows');
		
		
		
		$aLikes = array();
        $aDontCount = array();
        foreach ($aRowLikes as $iKey => $aLike)
        {        	
            if (!empty($aLike['action_time_stamp']) && $aLike['action_time_stamp'] > $aLike['time_stamp'])
            {
                $aDontCount[] = $aLike['like_id'];

                continue;
            }
            
            $aLikes[$aLike['user_id']] = $aLike;
        }
		$this->_iTotalLikeCount = count($aLikes);
                
        if ($bLoadCount == true)
        {
            //$sWhere = 'l.type_id = \'' . $this->database()->escape($sType) . '\' AND l.item_id = ' . (int) $iItemId;
            if (!empty($aDontCount))
            {
                $sWhere .= ' AND l.like_id NOT IN (' . implode(',', $aDontCount) . ')';
            }
            $this->_iTotalLikeCount = $this->database()->select('COUNT(*)')
                    ->from(Phpfox::getT('like'), 'l')
                    ->where($sWhere)
                    ->execute('getSlaveField') ;
        }
		
		return $aLikes;
	}

	public function getTotalLikeCount()
	{
		return $this->_iTotalLikeCount;
	}
	
}
