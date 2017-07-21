<?php

class Mfox_Service_Setting extends Phpfox_Service{
	
	function _loadCurrentUser(){
		$iUserId =  Phpfox::getUserId();
		
		if (!$iUserId){
			echo json_encode(array(
				'error_code'=>1,
				'error_message'=>Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase("mfox.missing_token"))
			));
			
			exit;
		}
		
		$aUser =  Phpfox::getService('user')->get($iUserId,true);
		
		if (null == $aUser){
			echo json_encode(array(
				'error_code'=>1,
				'iUserId'=>$iUserId,
				'error_message'=>Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase("mfox.could_not_find_user"))
			));
			exit;
		}
		
		return $aUser;
	}
	
	/**
	 * output 
	 * <ouput>
	 * full_name,
		user_name,
		email,
		language_title,
		time_zone_title,
		default_currency_title,
		perms {
		  can_change_own_full_name,
		  can_change_own_user_name,
		  can_change_email,
		  can_edit_currency
		}
	 * </ouput>
	 */
	public function fetch($aData){
		$result =  array();
		
		$aUser =  $this->_loadCurrentUser();
		
		// return $aUser;
		
		$result['full_name'] =  $aUser['full_name'];
		$result['user_name'] =  $aUser['user_name'];
		$result['email'] =  $aUser['email'];
		$result['language_id'] =  $aUser['language_id'];
		$result['time_zone_id'] =  ($aUser['time_zone'] != null)?$aUser['time_zone']:Phpfox::getParam('core.default_time_zone_offset');
		
		$langId  =  (string)$aUser['language_id'];
		$sTimeZone = ($aUser['time_zone'] != null)?$aUser['time_zone']:Phpfox::getParam('core.default_time_zone_offset');
		
		// return $aUser;
		
		$aTimezones = Phpfox::getService('core')->getTimeZones();
		$sCurrency =  Phpfox::getService('core.currency')->getDefault();
		
		$aCurrencies  =  Phpfox::getService('core.currency')->get();
		$sCurrency =  Phpfox::getService('mfox')->decodeUtf8Compat( Phpfox::getPhrase($aCurrencies[$sCurrency]['name']));
		
		if (empty($langId))
		{
			$aLanguages = Phpfox::getService('language')->get();
			$langId = count($aLanguages) ? $aLanguages[0]['language_id'] : 'en';
		}

		$aLanguage = Phpfox::getService('language')->getLanguage($langId);
		
		$result['language_title'] = Phpfox::getService('mfox')->decodeUtf8Compat($aLanguage['title']);
		$result['time_zone_title'] = !empty($sTimeZone) ? Phpfox::getService('mfox')->decodeUtf8Compat($aTimezones[$sTimeZone]) : Phpfox::getService('mfox')->decodeUtf8Compat(reset($aTimezones));
		$result['default_currency_title'] = $sCurrency;
		
		$result['invisible'] = $aUser['is_invisible']?1:0;
		$result['perms'] =  array(
			'can_change_own_full_name'=> Phpfox::getUserParam('user.can_change_own_full_name')?1:0,
			'can_change_own_user_name'=> Phpfox::getUserParam('user.can_change_own_user_name')?1:0,
			'can_change_email'=> Phpfox::getUserParam('user.can_change_email')?1:0,
			'can_edit_currency'=>Phpfox::getUserParam('user.can_edit_currency')?1:0,
			'can_be_invisible'=> 0, // Phpfox::getUserParam('user.can_be_invisible')?1:0, // new
  			'can_control_profile_privacy' =>Phpfox::getUserParam('user.can_control_profile_privacy')?1:0, // new
  			'can_control_notification_privacy'=>Phpfox::getUserParam('user.can_control_notification_privacy')?1:0 // new
		);
		
		
		return $result;
	}
	
	
	/**
	 * <output>
	 * full_name,
		total_full_name_change,
		perms {
		  total_times_can_change_own_full_name
		}
	 * </ouput>
	 */
	public function form_full_name($aData){
		
		$result  =  array();
		
		$aUser =  $this->_loadCurrentUser();
		
		$result['full_name'] =  $aUser['full_name'];
		$result['can_change_own_full_name']= Phpfox::getUserParam('user.can_change_own_full_name')?1:0;
		$result['total_full_name_change'] = intval($aUser['total_full_name_change']);
		$result['perms'] = array(
			'total_times_can_change_own_full_name'=>Phpfox::getUserParam('user.total_times_can_change_own_full_name'),
		);
		
		return $result;
		
	}
	
	/**
	 * <input>
	 * full_name
	 * </input>
	 */
	public function update_full_name($aData){
		
		$iUserId =  Phpfox::getUserId();
		
		$sFullName =  $aData['full_name'];
		
		$aUser =  $this->_loadCurrentUser();
		
		$full_name_changes_allowed =  Phpfox::getUserParam('user.total_times_can_change_own_full_name');

		
		if (Phpfox::getLib('parse.format')->isEmpty($sFullName)){
			return array(
				'error_code'=>1,
				'error_message'=> Phpfox::getPhrase('user.provide_a_name_that_is_not_representing_an_empty_name'),
			);
		}
		
		if (!Phpfox::getService('ban')->check('display_name',$sFullName)){
			return array(
				'error_code'=>1,
				'error_message'=> Phpfox::getPhrase('user.this_display_name_is_not_allowed_to_be_used')
			);
		}
		
		

		$sFullName = Phpfox::getLib('parse.input')->clean($sFullName, 255);
		
		
		if($sFullName == $aUser['full_name']){
			return array(
				'error_code'=>0,
				'message'=> Phpfox::getPhrase('user.account_settings_updated'),
			);
		}
		
		$this->database()->updateCounter('user_field', 'total_full_name_change', 'user_id', $iUserId);
		
		$this->database()->update(Phpfox::getT('user'), array('full_name'=>$sFullName), 'user_id = ' . (int) $iUserId);
		
		
		return array(
			'error_code'=>0,
			'full_name'=>$sFullName,
			'message'=> Phpfox::getPhrase('user.account_settings_updated'),
		);
	}
	
	/**
	 * <output>
	 * user_name,
		total_user_change,
		perms {
		  total_times_can_change_user_name
		}
	 * </output>
	 */
	public function form_user_name($aData){
		$result  =  array();
		
		$aUser =  $this->_loadCurrentUser();
		
		// return $aUser;
		
		$result['user_name'] =  $aUser['user_name'];
		$result['total_user_change'] = intval($aUser['total_user_change']);
		$result['perms'] = array(
			'total_times_can_change_user_name'=>Phpfox::getUserParam('user.total_times_can_change_user_name'),
		);
		
		return $result;
	}
	
	/**
	 * <input>
	 * user_name
	 * </input>
	 */
	public function update_user_name($aData){
		
		$iUserId =  Phpfox::getUserId();
		
		$sUserName =  $aData['user_name'];
		$sUserName = str_replace(' ', '-', $sUserName);
		
		$aUser =  $this->_loadCurrentUser();
		
		Phpfox::getService('user.validate')->user($sUserName);
		
		
		if (!Phpfox_Error::isPassed())
		{
			return array(
				'error_code'=>1,
				'error_message'=>Phpfox::getService('mfox')->decodeUtf8Compat(implode(' ', Phpfox_Error::get())),
			);
		}
		
		if (Phpfox::getLib('parse.format')->isEmpty($sUserName)){
			return array(
				'error_code'=>1,
				'error_message'=> Phpfox::getPhrase('user.provide_a_user_name'),
			);
		}
		
		$this->database()->updateCounter('user_field', 'total_user_change', 'user_id', $iUserId);
		
		$this->database()->update(Phpfox::getT('user'), array('user_name'=>$sUserName), 'user_id = ' . (int) $iUserId);
		
		
		return array(
			'error_code'=>0,
			'user_name'=>$sUserName,
			'message'=> Phpfox::getPhrase('user.account_settings_updated'),
		);
	}
	
	/**
	 * <input>
	 * email
	 * </input>
	 */
	public function update_email($aData){
		
		$sEmail =  $aData['email'];
		
		$aUser =  $this->_loadCurrentUser();
		
		if (Phpfox::getLib('parse.format')->isEmpty($sEmail)){
			return array(
				'error_code'=>1,
				'error_message'=> Phpfox::getPhrase('user.provide_a_valid_email_address')
			);
		}

		
		Phpfox::getService('user.validate')->email($sEmail);
		
		if (!Phpfox_Error::isPassed())
		{
			return array(
				'error_code'=>1,
				'email'=>$sEmail,
				'error_message'=>Phpfox::getService('mfox')->decodeUtf8Compat(implode(' ', Phpfox_Error::get())),
			);
		}
		
		$iUserid = Phpfox::getUserId();
		
		
		$this->database()->update(Phpfox::getT('user'), array('email'=>$sEmail), 'user_id = ' . (int) $iUserid);
		
		return array(
			'error_code'=>0,
			'message'=> Phpfox::getPhrase('user.account_settings_updated'),
		);
	}
	
	private function _getSalt($iTotal = 3)
	{
		$sSalt = '';
		for ($i = 0; $i < $iTotal; $i++)
		{
			$sSalt .= chr(rand(33, 91));
		}

		return $sSalt;
	}
	
	/**
	 * old_password,
		new_password,
		confirm_password
	 */
	public function update_password($aVals){
		
		$aUser = $this->_loadCurrentUser();
		
		
		if (empty($aVals['old_password']))
		{
			return array(
				'error_code'=>1,
				'error_message'=> Phpfox::getPhrase('user.missing_old_password')
			);
		}
		
		if (empty($aVals['new_password']))
		{
			return array(
				'error_code'=>1,
				'error_message'=> Phpfox::getPhrase('user.missing_new_password')
			);
		}
		
		if (empty($aVals['confirm_password']))
		{
			return array(
				'error_code'=>1,
				'error_message'=> Phpfox::getPhrase('user.confirm_your_new_password')
			);
		}		
		
		if ($aVals['confirm_password'] != $aVals['new_password'])
		{
			return array(
				'error_code'=>1,
				'error_message'=> Phpfox::getPhrase('user.your_confirmed_password_does_not_match_your_new_password'),
			);
		}
		
		if (Phpfox::getLib('hash')->setHash($aVals['old_password'], $aUser['password_salt']) != $aUser['password'])
		{
			
			return array(
				'error_code'=>1,
				'error_message'=> Phpfox::getPhrase('user.your_current_password_does_not_match_your_old_password'),
			);
		}
		
		$sSalt = $this->_getSalt();
		$aInsert = array();
		$aInsert['password'] = Phpfox::getLib('hash')->setHash($aVals['new_password'], $sSalt);
		$aInsert['password_salt'] = $sSalt;
		
		$this->database()->update(Phpfox::getT('user'), $aInsert, 'user_id = ' . Phpfox::getUserId());
		
		
		
		$this->database()->insert(Phpfox::getT('user_ip'), array(
				'user_id' => Phpfox::getUserId(),
				'type_id' => 'update_password',
				'ip_address' => Phpfox::getIp(),
				'time_stamp' => PHPFOX_TIME
			)
		);	
		
		return array(
			'error_code'=>0,
			'message'=> Phpfox::getPhrase('user.account_settings_updated'),
		);
	}
	
	/**
	 * language_id, // current language
		language_options[] {
		  id: string,
		  title: String
		}
	 */
	public function form_language($aVals){
		
		
		$aLangs = Phpfox::getService('language')->get(array('l.user_select = 1'));
		
		$aUser = $this->_loadCurrentUser();
		
		$language_id = !empty($aUser['language_id']) ? $aUser['language_id'] : $aLangs[0]['language_id'];

		$result['language_id'] = $language_id;
		
		$options = array();
		
		foreach($aLangs as $lang){
			$options[] = array(
				'id'=>$lang['language_id'], 
				'title'=> Phpfox::getService('mfox')->decodeUtf8Compat($lang['title'])
			);
		}
		
		$result['language_options'] = $options;
		
		return $result; 
		
	}
	
	/**
	 * language_id
	 */
	public function update_language($aVals){
		
		$sLanguageId =  $aVals['language_id'];
		
		$aLangs = Phpfox::getService('language')->get(array('l.user_select = 1'));
		$valid =  false;
		
		foreach($aLangs  as $lang){
			if($lang['language_id'] == $sLanguageId){
				$valid = true;
			}
		}
		
		if(!$valid){
			return array(
				'error_code'=>1,
				'error_message'=>Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase("mfox.invalid_language")),
			);
		}
		
		$iUserId =  Phpfox::getUserId();
		
		$this->database()->update(Phpfox::getT('user'), array(
		  'language_id'=>$sLanguageId,
		), 'user_id = ' . Phpfox::getUserId());
		
		return array(
				'error_code'=>0,
				'message'=> Phpfox::getPhrase('user.account_settings_updated'),
			);
	}
	
	/**
	 * time_zone, // current time zone id
		time_zone_options[] {
		  id,
		  title
		}
	 */
	public function form_time_zone($aData){
		
		$aUser  =  $this->_loadCurrentUser();
		
		$sTimeZone = ($aUser['time_zone'] != null)?$aUser['time_zone']:Phpfox::getParam('core.default_time_zone_offset');
		
		$aTimeZones = Phpfox::getService('core')->getTimeZones();

		if (empty($sTimeZone))
		{
			reset($aTimeZones);
			$sTimeZone = key($aTimeZones);
		}

		$options = array();
		
		foreach ($aTimeZones as $key => $value) {
			$options[] = array(
				'id'=>$key,
				'title'=>$value,
			);
		}
		
		return array(
			'time_zone' => $sTimeZone,
			'time_zone_options'=>$options,
		);
	}
	
	/**
	 * time_zone
	 */
	public function update_time_zone($aVals){
		$aUser  =  $this->_loadCurrentUser();
		$sTimezone =  $aVals['time_zone'];
		$aTimeZones = Phpfox::getService('core')->getTimeZones();
		
		if(!array_key_exists($sTimezone, $aTimeZones)){
			return array(
				'error_code'=>1,
				'error_message'=>Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase("mfox.invalid_timezone")),
			);
		}
		
		$iUserId = Phpfox::getUserId();
		
		$this->database()->update(Phpfox::getT('user'), array(
		  'time_zone'=>$sTimezone,
		), 'user_id = ' . Phpfox::getUserId());
		
		return array(
			'error_code'=>0,
			'message'=> Phpfox::getPhrase('user.account_settings_updated'),
		);
	}
	
	/**
	 * default_currency,
		currency_options[] {
		  id,
		  title
		}
	 */
	public function form_default_currency($aData){
		
		$sDefaultCurrency = Phpfox::getService('core.currency')->getDefault();  
		
		$aCurrrencies  =  Phpfox::getService('core.currency')->get();
		
		$options = array();
		
		foreach($aCurrrencies as $key=>$row){
			$options[] = array(
				'id'=>$key,
				'title'=> Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase($row['name'])),
				'symbol'=> Phpfox::getService('mfox')->decodeUtf8Compat($row['symbol'])
			);
		}
		
		return array(
			'default_currency'=>$sDefaultCurrency,
			'currency_options'=>$options,
		);
	}
	/**
	 * default_currency
	 */
	public function update_default_currency($aVals){
		
		$aUser  =  $this->_loadCurrentUser();
		$default_currency =  $aVals['default_currency'];
		$aCurrrencies  =  Phpfox::getService('core.currency')->get();
		$iUserId = Phpfox::getUserId();
		
		if(!array_key_exists($default_currency, $aCurrrencies)){
			return array(
				'error_code'=>1,
				'error_message'=>Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase("mfox.invalid_currency")),
			);
		}
		
		
		Phpfox::getService('user.field.process')
			->update($iUserId, 'default_currency', $default_currency);
		// require clear cached.
		$this->cache()->remove(array('currency', $iUserId));
		
		return array(
			'error_code'=>0,
			'default_currency'=>$default_currency,
			'message'=> Phpfox::getPhrase('user.account_settings_updated'),
		);
	}
	
	/**
	 * privacy[] {
		  phrase,
		  key,
		  options[] {
		    id,
		    title
		  },
		  default // selected value
		},
		special {
		  dob_setting: 2,
	 	  dob_options[] {
		    id,
		    title
		  },
		}
			 * 
	 */
	public function form_privacy_profile($aData){
		$iUserId = Phpfox::getUserId();
		
		$aUser  = $this->_loadCurrentUser();
		
		list($aUserPrivacy, $aNotifications, $aProfiles, $aItems) = Phpfox::getService('user.privacy')->get($iUserId);
		
		$options = array();
		
		$avails_keys  =  array();
		
		foreach($aProfiles as $module=>$sections){
			foreach($sections  as $key=>$block){
				if ( ($key != 'rss.can_subscribe_profile') || !Phpfox::getParam('core.friends_only_community')){
				
					$avails = array();
					$avails_keys =  array();
				
					if(!isset($block['anyone']) && !Phpfox::getParam('core.friends_only_community')){
						$avails_keys[0] = 1;
						$avails[] = array(
							'id'=>0,
							'title'=> Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('user.anyone')),
						);
					}

					if(!isset($block['no_user'])){
						
						if(!Phpfox::getParam('core.friends_only_community')){
							$avails_keys[1] = 1;
							$avails[] = array(
								'id'=>1,
								'title'=> Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('user.community')),
							);	
						}
						
						if (Phpfox::isModule('friend')){
							$avails_keys[2] = 1;
							$avails[] = array(
								'id'=>2,
								'title'=> Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('user.friends_only')),
							);	
						}
					}
					
					$avails_keys[4] = 1;
					
					$avails[] = array(
						'id'=>4,
						'title'=> Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('user.no_one')),
					);	
					
					$default =  intval($block['default']);
					
					if(!array_key_exists($default, $avails_keys)){
						// return $avails_keys;
						$default = $avails[0]['id'];
					}
					
					$options[] = array(
						'key'=>$key, 
						'phrase'=> Phpfox::getService('mfox')->decodeUtf8Compat($block['phrase']),
						'default'=>$default,
						'options'=>$avails,
					);
				}				
			}
		}
		
		$special =  array(
			'dob_setting'=> $aUser['dob_setting'],
			'dob_options'=>array(
				array('id'=>0,'title'=>Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('core.select'))),
				array('id'=>1,'title'=>Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('user.show_only_month_amp_day_in_my_profile'))),
				array('id'=>2,'title'=>Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('user.display_only_my_age'))),
				array('id'=>3,'title'=>Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('user.don_t_show_my_birthday_in_my_profile'))),
				array('id'=>4,'title'=>Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('user.show_my_full_birthday_in_my_profile'))),
			),
		);
		
		return array(
			'privacy'=>$options,
			'special'=>$special,
		);
	}
	
	/**
	 * privacy {
		  <key1>,
		  <key2>, ...
		},
		special {
		  dob_setting: 2
		}
	 */
	public function update_privacy_profile($aVals){
		
		$iUserId = Phpfox::getUserId();
		
		$this->database()->delete(Phpfox::getT('user_privacy'), 'user_id = ' . $iUserId);
		
		foreach($aVals['privacy'] as $sVar=>$iVal){
			
			if (!$iVal)
			{
				continue;
			}
			
			$this->database()->insert(Phpfox::getT('user_privacy'), array(
					'user_id' => $iUserId,
					'user_privacy' => $sVar,
					'user_value' => $iVal
				)
			);
		}
		
		Phpfox::getService('user.field.process')->update($iUserId, 'dob_setting', (int) $aVals['special']['dob_setting']);
		$this->cache()->remove(array('udob', $iUserId));
		
		return array(
			'error_code'=>0,
			'message'=> Phpfox::getPhrase('user.privacy_settings_successfully_updated'),
		);
	}
	
	private function _getPrivacyItemControls($aVals){
		if (!Phpfox::getParam('core.friends_only_community'))
		{
			$aPrivacyControls[] = array(
				'title' => Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('privacy.everyone')),
				'id' => '0'
			);
		}
		if (Phpfox::isModule('friend'))
		{
			$aPrivacyControls[] = array(
			'title' => Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('privacy.friends')),
			'id' => '1'
			);
			$aPrivacyControls[] = array(
				'title' => Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('privacy.friends_of_friends')),
				'id' => '2'
			);
		}
		
		$aPrivacyControls[] = array(
			'title' => Phpfox::getService('mfox')->decodeUtf8Compat(Phpfox::getPhrase('privacy.only_me')),
			'id' => '3'
		);
		
		// if (Phpfox::isModule('friend') && !(bool) $this->getParam('privacy_no_custom', false))
		// {
			// $mCustomPrivacyId = $this->getParam('privacy_custom_id', null);
// 
			// $aPrivacyControls[] = array(
				// 'title' =>  Phpfox::getPhrase('privacy.custom_span_click_to_edit_span'),
				// 'id' => '4',
				// // 'onclick' => '$Core.box(\'privacy.getFriends\', \'\', \'no_page_click=true' . ($mCustomPrivacyId === null ? '' : '&amp;custom-id=' . $mCustomPrivacyId) . '&amp;privacy-array=' . $this->getParam('privacy_array', '') . '\');'
			// );
		// }
		
		return $aPrivacyControls;
		
	}
	
	/**
	 * [] {
		  phrase,
		  key,
		  options[] {
		    id,
		    title
		  },
		  default // selected value
		}
		// sample data in note
	 */
	public function form_privacy_items($aData){
		$iUserId = Phpfox::getUserId();
		
		$aUser  = $this->_loadCurrentUser();
		
		list($aUserPrivacy, $aNotifications, $aProfiles, $aItems) = Phpfox::getService('user.privacy')->get($iUserId);
		
		$options = array();
		
		foreach($aItems as $module=>$section){
			foreach($section as $key=>$block){
				
				$aPrivacyControls =  $this->_getPrivacyItemControls($aUserPrivacy['privacy']);
				
				$default =  $aPrivacyControls[0]['id']; 
				if(array_key_exists($key, $aUserPrivacy['privacy'])){
					$default  =  $aUserPrivacy['privacy'][$key];	
				}
				

				$options[] =  array(
					'key'=>$key,
					'phrase'=> Phpfox::getService('mfox')->decodeUtf8Compat($block['phrase']),
					'default'=>$default,
					'custom_id'=>$block['custom_id'],
					'options'=>$aPrivacyControls,
					'block'=>$block,
				);
			}
		}
		
		return $options;
	}
	
	/**
	 * <input>
	 * <key1>,
		<key2>, ...
	 * </input>
	 */
	public function update_privacy_items($aVals){
		
		$iUserId = Phpfox::getUserId();
		  
		if (Phpfox::getUserParam('user.can_control_profile_privacy'))
		{
				
		}	
		
		foreach ($aVals['privacy'] as $sVar => $aVal)
		{
			if (!preg_match('/(.*)\.(.*)/', $sVar, $aMatches))
			{
				continue;
			}
			
			if (!isset($aMatches[1]))
			{
				continue;
			}
			
			if (!Phpfox::isModule($aMatches[1]))
			{
				continue;
			}
						
			$iId = $this->database()->insert(Phpfox::getT('user_privacy'), array(
					'user_id' => $iUserId,
					'user_privacy' => $sVar,
					'user_value' => (int) $aVal[$sVar]
				)
			);
			/*
			if ($aVal[$sVar] == '4')
			{
				Phpfox::getService('privacy.process')->update('user', $iId, (isset($aVal['privacy_list']) ? $aVal['privacy_list'] : array()));			
			}
			*/					
		}		

		return array(
			'error_code'=>0,
			'message'=> Phpfox::getPhrase('user.privacy_settings_successfully_updated'),
		);
	}
	
	/**
	 * [] {
		  phrase,
		  key,
		  default // selected value
		}
		// sample data in note
	 */
	public function form_privacy_notifications($aData){
		$iUserId = Phpfox::getUserId();
		
		$aUser  = $this->_loadCurrentUser();
		
		list($aUserPrivacy, $aNotifications, $aProfiles, $aItems) = Phpfox::getService('user.privacy')->get($iUserId);
		
		$options = array();
		
		foreach($aNotifications as $module=>$section){
			foreach($section as $key=>$block){
				$options[] =  array(
					'key'=>$key,
                    'phrase'=>$block['phrase'],
					'default'=>$block['default'],
				);
			}
		}
		
		return $options;
	}
	
	/**
	 * <key1>,
		<key2>, ...
	 */
	public function update_privacy_notifications($aVals){
		// update privacy comment.
		
		$iUserId = Phpfox::getUserId();
		$this->database()->delete(Phpfox::getT('user_notification'), 'user_id = ' . $iUserId);
		
		foreach($aVals['notification'] as $sVar=>$iVal){
			if ($iVal)
			{
				continue;
			}
			
			$this->database()->insert(Phpfox::getT('user_notification'), array(
					'user_id' => $iUserId,
					'user_notification' => $sVar
				)
			);
		}
		
		return array(
			'error_code'=>0,
			'message'=> Phpfox::getPhrase('user.privacy_settings_successfully_updated'),
		);
	}
	
	/**
	 * [] {
		  iUserId,
		  sFullName,
		  sImage
		}
		// no limit
	 */
	public function fetch_blocked_users($aVals){
		$users =  Phpfox::getService('user.block')->get();
		
		// return $users;
		
		
		$results = array();
		
		foreach($users as $user){
			$sImageUrl = Phpfox::getService('mfox.user')->getImageUrl($user, '_50_square');
			
			$results[] = array(
				'iUserId'=>$user['user_id'],
				'sFullName'=>$user['full_name'],
				'sImage'=>$sImageUrl,
			);
		}
		
		return $results;
	}
	
	/**
	 * sUnblockUserIds
	 * 
	 */
	public function update_blocked_users($aVals){
		
		$sUnblockUserIds = $aVals['sUnblockUserIds'];
		
		if(empty($sUnblockUserIds)){
			return array(
				'error_code'=>0,
				'error_message'=> Phpfox::getPhrase('user.user_successfully_unblocked'),
			);
		}
		$aUserIds = explode(',', $sUnblockUserIds);
		
		if(empty($aUserIds)){
			return array(
				'error_code'=>0,
				'error_message'=> Phpfox::getPhrase('user.user_successfully_unblocked'),
			);
		}
		
		foreach($aUserIds as $sUserId){
			Phpfox::getService('user.block.process')->delete($sUserId);	
		}
		
		return array(
			'error_code'=>0,
			'error_message'=> Phpfox::getPhrase('user.user_successfully_unblocked'),
		);
	}
	
	/**
	 * invisible
	 */
	public function update_invisible_mode($aVals){
		
		$iUserId =  Phpfox::getUserId();
		
		$invisible =  intval($aVals['invisible']);
		
		$this->database()->update(Phpfox::getT('user'), array('is_invisible' => $invisible), 'user_id = ' . (int) $iUserId);
		
		return array(
			'error_code'=>0,
			'message'=> Phpfox::getPhrase('user.account_settings_updated'),
		);
	}
}
