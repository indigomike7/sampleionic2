<?php

class Mfox_Service_Member extends Phpfox_Service{


	function form_search($aData){
		$aReturn = array();

		$genderOptions =  Phpfox::getService('core')->getGenders();
		foreach ($genderOptions as $key => $gender) {
			$genderOptions[$key] = Phpfox::getService('mfox')->decodeUtf8Compat($gender);
		}
		$aReturn['genderOptions'] = $genderOptions;

		return $aReturn;
	}
	
	function _modelViewHelper($aRow){
		$aReturn = array();
		$sImageUrl = Phpfox::getService('mfox.user')->getImageUrl($aRow, '_50_square');

		list($count, $aMutualList) = Phpfox::getService('friend')->getMutualFriends($aRow['user_id']);

		$aReturn =  array(
			'id'=> $aRow['user_id'],
			'sFullName'=> $aRow['full_name'],
			'UserProfileImg_Url'=> $sImageUrl,
			'BigUserProfileImg_Url'=>$sImageUrl,
			'iMutualFriends'=> $count,
			'isFriend'=>Phpfox::getService('mfox.helper.user')->isFriend(Phpfox::getUserId(), $aRow['user_id']),
			'isSentRequest' => Phpfox::getService('friend.request')->isRequested(Phpfox::getUserId(), $aRow['user_id']),
            'isSentRequestBy' => Phpfox::getService('friend.request')->isRequested($aRow['user_id'], Phpfox::getUserId()),
            'bIsOnline'=> $aRow['last_activity'] > Phpfox::getService('log.session')->getActiveTime() ?1:0,
            'iLastActivityTimestamp'=>(int)$aRow['last_activity'],
            'sLastActivityTimestamp'=> date('l, F j, Y g:i A', $aRow['last_activity']),
		);

		return $aReturn;
	}

	/**
	 * @param  array $aRow
	 * 
	 * @return array
	 */
	function _modelViewHelperSimple($aRow)
    {
		$sImageUrl = Phpfox::getService('mfox.user')->getImageUrl($aRow, '_50_square');
		$aReturn =  array(
			'id'=> $aRow['user_id'],
            'type'=>'user',
			'title'=> $aRow['full_name'],
			'img'=> $sImageUrl
		);

		return $aReturn;
	}

	/**
     * Get tags list for suggestion 
     * 
     * @link https://docs.google.com/spreadsheets/d/1QKYXo1NoGnWows5NQ0x6Yb8zuSHEm6-V8WsDtOkTmpg/edit#gid=507873474
     * @link https://jira.younetco.com/browse/PCUS-1035
     * @since 3.09p1
     * @author Nam Nguyen
     * date Jul 01, 2015
	 * <code> 
	 * member/get_tag_list?q=
	 * {
	 * q: "",
	 *   rows: [{id: string, title: string, img: string }]
	 * }
	 * </code>
	 */
	function get_tag_list($aData)
	{
		$user  =  $this->database()->select('*')->from(Phpfox::getT('user'))->where('user_id='. Phpfox::getUserId())->execute('getRow');
	
		$iLimit = 10;
		$iPage  = 1;
		$q = $aData['q'];
		
		$aConditions = array();

		$aConditions[] = "AND (u.profile_page_id = 0) ";

		$oDb = Phpfox::getLib('database');
		
		if ($q)
		{
            $like =  $q;
            if(substr($q,0,1) == '@'){
                $like = substr($q, 1);
            }
			$aConditions[] = 'AND (u.full_name LIKE \'%' . $oDb->escape($like) . '%\')';
		}

		
		// search by conditions.
		
		
		list($iCnt, $aUsers) = 
			Phpfox::getService('user.browse')
			->conditions($aConditions)
			->page($iPage)
			->limit($iLimit)
			->gender(false)
			->sort('u.full_name ASC')
			->get();

		if($iCnt < ($iPage -1 ) * $iLimit){
			return array();
		}

		$aReturn = array();

		foreach($aUsers as $aRow){
			$aReturn[] =  $this->_modelViewHelperSimple($aRow);
		}

		return array(
			'q'=>$q,
			'rows'=>$aReturn,
		);
	}

	function fetch($aData){
		/**
		 **/
		extract($aData);

		// return $aData;b
		
		if(!Phpfox::getUserId()){
			return array();
		}
		
		$user  =  $this->database()->select('*')->from(Phpfox::getT('user'))->where('user_id='. Phpfox::getUserId())->execute('getRow');
	

		$iLimit = isset($iLimit) ? intval($iLimit): 10;
		$iPage  = isset($iPage) ? intval($iPage):1;
		$sSearch = isset($sSearch)? $sSearch : "";
		$gender  = isset($gender) ? intval($gender): null;
		$from = isset($age_from)? intval($age_from): null;
		$to =  isset($age_to)? intval($age_to): null;
		$iYear = intval(date('Y'));

		$bIsGender = false;

		$aConditions = array();

		$aConditions[] = "AND (u.profile_page_id = 0) ";

		$oDb = Phpfox::getLib('database');
		
		if ($sSearch)
		{
			$aConditions[] = 'AND (u.user_name LIKE \'%' . $oDb->escape($sSearch) . '%\' OR u.full_name LIKE \'%' . $oDb->escape($sSearch) . '%\' OR u.email LIKE \'%' . $oDb->escape($sSearch) . '%\')';
		}

		if($gender){
			$aConditions[] =  'AND u.gender = \'' . $oDb->escape($gender) . '\'';
		}

		if($from){
			$aConditions[] = 'AND u.birthday_search <= \'' . Phpfox::getLib('date')->mktime(0, 0, 0, 1, 1, $iYear - $from). '\'' . ((defined('PHPFOX_IS_ADMIN_SEARCH') && Phpfox::getUserParam('user.remove_users_hidden_age')) ? '' : ' AND ufield.dob_setting IN(0,1,2)');
			$bIsGender = true;
		}

		if($to){
			$aConditions[] = 'AND u.birthday_search >= \'' . Phpfox::getLib('date')->mktime(0, 0, 0, 1, 1, $iYear - $to) .'\'' . ((defined('PHPFOX_IS_ADMIN_SEARCH') && Phpfox::getUserParam('user.remove_users_hidden_age')) ? '' : ' AND ufield.dob_setting IN(0,1,2)');
			$bIsGender = true;	
		}
		
		// search by conditions.
		
		
		list($iCnt, $aUsers) = 
			// Phpfox::getService('mfox.userbrowse')
			// ->setLatlong($user['latitude'], $user['longitude'])
			Phpfox::getService('user.browse')
			->conditions($aConditions)
			->page($iPage)
			->limit($iLimit)
			->gender($bIsGender)
			->sort('u.full_name ASC')
			->get();
		// var_dump(Phpfox_Error::get()); exit;

		if($iCnt < ($iPage -1 ) * $iLimit){
			return array();
		}

		$aReturn = array();

		// retrurn $aUsers;

		foreach($aUsers as $aRow){
			$aReturn[] =  $this->_modelViewHelper($aRow);
		}

		return $aReturn;
	}	
}