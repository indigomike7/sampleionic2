<?php

/**
 * @package mfox
 * @version 3.01
 */
defined('PHPFOX') or exit('NO DICE!');

class Mfox_Service_Helper_User extends Phpfox_Service
{
	public function __construct() {
		$this->_sTable = Phpfox::getT('user');
	}
	
	public function getUserActivityPoint($iUserId){
		$row =  $this->database()->select('activity_points')
		->from(Phpfox::getT('user_activity'))
		->where('user_id='. intval($iUserId))
		->execute('getRow');
		
		if($row){
			return (int) $row['activity_points'];
		}
		
		return 0;
	}
    public function getUserData($iUserId) {
        // if (Phpfox::isModule('photo'))
        // {
        // }

        $aRow =	$this->database()->select('u.*, user_field.* , p.photo_id as cover_photo_exists ')
                                 ->from(Phpfox::getT('user'), 'u')
                                 ->leftjoin(Phpfox::getT('user_field'), 'user_field', 'user_field.user_id = u.user_id')
                                 ->leftJoin(Phpfox::getT('photo'), 'p', 'p.photo_id = user_field.cover_photo')
                                 // ->leftJoin(Phpfox::getT('custom_relation_data'), 'crd', 'crd.user_id = ' . $iUserId)
                                 ->where('u.user_id = ' . $iUserId)
                                 ->execute('getRow');

        $aRow['birthday_time_stamp'] = $aRow['birthday'];
        $aRow['birthday'] = Phpfox::getService('user')->age($aRow['birthday']);
         $aRow['birthdate_display'] = Phpfox::getService('user')->getProfileBirthDate($aRow);
        if($aRow['birthday_search']) {
            $aRow['sDayOfBirth'] = date('Y-m-d', $aRow['birthday_search']);
            $aRow['sDayOfBirthDotFormat'] = date('m.d.Y', $aRow['birthday_search']);
        }

        if($aRow['gender'] == 1) {
            // $aRow['sGender'] =  Phpfox::getPhrase('user.male');
            $aRow['sGender'] = 'Male'; // no phrase because client side use string to compare
        } else if($aRow['gender'] == 2) {
            $aRow['sGender'] = 'Female';
        } else {
        	$aRow['sGender'] = '';
        }

        $aRow['sProfileImage'] = Phpfox::getService('mfox.user')->getImageUrl($aRow, '');
        $aRow['sProfileImageSmall'] = Phpfox::getService('mfox.user')->getImageUrl($aRow, '_50_square');

        if (isset($aRow['cover_photo']) && ((int)$aRow['cover_photo'] > 0) && 
                (
                    (isset($aRow['cover_photo_exists']) && $aRow['cover_photo_exists'] != $aRow['cover_photo']) ||
                    (!isset($aRow['cover_photo_exists']))
                )) //in casea the cover photo is deleted
        {
            $aRow['cover_photo'] = null;
        }

        $sCoverPhotoUrl = '';
        if($aRow['cover_photo']) {
            $aCoverPhoto = Phpfox::getService('photo')->getCoverPhoto($aRow['cover_photo']);
            $sCoverPhotoUrl = sprintf(Phpfox::getParam('photo.url_photo') . $aCoverPhoto['destination'], '_500');
        }


        $aRow['sCoverPhotoUrl'] = $sCoverPhotoUrl;

        $aRow['sLocation'] =  Phpfox::getPhraseT(Phpfox::getService('core.country')->getCountry($aRow['country_iso']), 'country');

        // $aRow['iTotalPhoto'] = Phpfox::getService('mfox.photo')->isAdvancedModule() ? $aRow['total_advancedphoto'] : $aRow['total_photo'];
        $aRow['iTotalPhoto'] = Phpfox::getService('mfox.photo')->isAdvancedModule() ? $this->database()->select('COUNT(*)')->from(Phpfox::getT('photo'))->where('view_id = 0 AND group_id = 0 AND type_id < 2 AND user_id = ' . (int) $iUserId )->execute('getSlaveField') : $aRow['total_photo'];

        $aRow['iTotalFriend'] = $aRow['total_friend'];
		$aRow['iActivityPoints'] = $this->getUserActivityPoint($iUserId);
		$aRow['sLastOnline'] = date('l, F j, Y g:i A', $aRow['last_activity']);
		$aRow['bIsOnline'] = $aRow['last_activity'] > Phpfox::getService('log.session')->getActiveTime() ?1:0;
        return $aRow;

    }

    public function isBlocked($iViewingUserId, $iViewedUserId) {
        return Phpfox::getService('user.block')->isBlocked($iViewingUserId, $iViewedUserId) == 1 ? true : false;
    }

    public function isFriend($iViewingUserId, $iViewedUserId) {
        return Phpfox::getService('friend')->isFriend($iViewingUserId, $iViewedUserId);
    }

    public function isFriendRequestSent($iViewingUserId, $iViewedUserId) {
        return Phpfox::getService('friend.request')->isRequested($iViewingUserId, $iViewedUserId);
    }

    public function isReceivedFriendRequest($iViewingUserId, $iViewedUserId) {
        return Phpfox::getService('friend.request')->isRequested($iViewedUserId, $iViewingUserId);
    }

    public function canViewProfileOfUser($iUserId) {
        // if (Phpfox::getService('user.block')->isBlocked($iUserId, Phpfox::getUserId()) && !Phpfox::getUserParam('user.can_override_user_privacy')) {
        //     return false;
        // }
        if (!Phpfox::getService('user.privacy')->hasAccess($iUserId, 0)) {
            return false;
        }

        return true;
    }
    public function canPostComment($iUserId, $iPrivacy = 0) {
        return Phpfox::getService('comment')->canPostComment($iUserId, $iPrivacy);
    }

    public function getRelationShipOfUser($iUserId) {

        $aRow = $this->database()->select('relation_data_id')
                                 ->from(Phpfox::getT('custom_relation_data'))
                                 ->where('user_id = ' . $iUserId)
                                 ->execute('getRow');
        if(!$aRow) {
            return false;
        }

        $aUser = array(
            'user_id' => $iUserId,
            'relation_data_id' => $aRow['relation_data_id']
        );
        $sRelationship = Phpfox::getService('custom')->getRelationshipPhrase($aUser);

        return $sRelationship;
    }

    public function block($iUserId) {
        return Phpfox::getService('user.block.process')->add($iUserId);
    }	

    public function unblock($iUserId) {
        return Phpfox::getService('user.block.process')->delete($iUserId);
    }	

    public function editProfile($iUserId, $aVals) {
        return $this->update($iUserId, $aVals);
    }

    public function update($iUserId, $aVals) {

        if(Phpfox::getUserId() == $iUserId) { // only owner can edit his profile
        	$aUpdate = array();
        	if(isset($aVals['gender']) && empty($aVals['gender']) == false)
        	{
        		$aUpdate['gender'] = intval($aVals['gender']);
        	} else {
        		$aUpdate['gender'] = 0;
        	}

            if(isset($aVals['full_name'])) {
                $aUpdate['full_name'] = Phpfox::getLib('parse.input')->clean($aVals['full_name']);
            }

            if(isset($aVals['country_iso'])) {
                $aUpdate['country_iso'] = Phpfox::getLib('parse.input')->clean($aVals['country_iso']);
            }

            if(isset($aVals['country_iso'])) {
                $aUpdate['country_iso'] = Phpfox::getLib('parse.input')->clean($aVals['country_iso']);
            }

            if (isset($aVals['day']) && $aVals['day'] > 0) {
                $aUpdate['birthday_search'] = (Phpfox::getUserParam('user.can_edit_dob') && isset($aVals['day']) && isset($aVals['month']) && isset($aVals['year']) ? Phpfox::getLib('date')->mktime(0, 0, 0, $aVals['month'], $aVals['day'], $aVals['year']) : 0);
    			if (isset($aUpdate['birthday_search']))
    			{
    				$aUpdate['birthday'] = date('mdY', $aUpdate['birthday_search']);
    			}            
            }

			if (isset($aVals['relation']) && Phpfox::getUserParam('custom.can_have_relationship') 
				)
			{
				Phpfox::getService('custom.relation.process')->updateRelationship($aVals['relation'], isset($aVals['relation_with']) ? $aVals['relation_with'] : null);				
			}			

            $this->database()->update(Phpfox::getT('user'), $aUpdate, 'user_id = ' . $iUserId);

            // update user field
            $aUserFieldUpdate = array();

            if(isset($aVals['city_location'])) {
                $aUserFieldUpdate['city_location'] = Phpfox::getLib('parse.input')->clean($aVals['city_location']);
            }

            if(isset($aVals['postal_code'])) {
                $aUserFieldUpdate['postal_code'] = Phpfox::getLib('parse.input')->clean($aVals['postal_code']);
            }
            if(isset($aVals['country_child_id'])) {
                $aUserFieldUpdate['country_child_id'] = $aVals['country_child_id'];
            }

            if($aUserFieldUpdate) {
                $this->database()->update(Phpfox::getT('user_field'), $aUserFieldUpdate, 'user_id = ' . $iUserId);
            }

            if(isset($aVals['about_me'])) {
                $this->updateCustomField('cf_about_me', $aVals['about_me'], $iUserId);
            }

            return true;
        } else {
        	$msg  = "You do not have permission to edit profile. ". $iUserId . "#". (Phpfox::getUserId());
        	Phpfox_Error::set($msg); 
            return false;
        }
    }

    /**
     * @todo: later
     */
    public function editAboutMe($iUserId, $sAbout) {

    }

    public function postCheckinStatus($aVals) {
        if (empty($aVals['privacy']))
        {
            $aVals['privacy'] = 0;
        }		
        
        if (empty($aVals['privacy_comment']))
        {
            $aVals['privacy_comment'] = 0;
        }

        $iParentUserId = isset($aVals['parent_user_id']) ? $aVals['parent_user_id'] : 0;

        $sStatus = $this->preParse()->prepare($aVals['user_status']);

        $aInsert = array(
            'user_id' => (int) Phpfox::getUserId(),
            'privacy' => $aVals['privacy'],
            'privacy_comment' => $aVals['privacy_comment'],
            'content' => $sStatus,
            'time_stamp' => PHPFOX_TIME
        );

        $aInsert['location_name'] = Phpfox::getLib('parse.input')->clean($aVals['location_name']);

        $aInsert['location_latlng'] = json_encode(array('latitude' => $aVals['latitude'], 'longitude' => $aVals['longitude']));
        $iStatusId = $this->database()->insert(Phpfox::getT('user_status'), $aInsert);		

        $iReturnId = Phpfox::getService('feed.process')->add('user_status', $iStatusId, $aVals['privacy'], $aVals['privacy_comment'], $iParentUserId, null, 0, (isset($aVals['parent_feed_id']) ? $aVals['parent_feed_id'] : 0), (isset($aVals['parent_module_id']) ? $aVals['parent_module_id'] : null));

        return $iReturnId;

    }

	public function add($aVals, $iUserGroupId = null)
	{
		if (!defined('PHPFOX_INSTALLER') && defined('PHPFOX_IS_HOSTED_SCRIPT'))
		{
			$iTotalMembersMax = (int) Phpfox::getParam('core.phpfox_grouply_members');
			$iCurrentTotalMembers = $this->database()->select('COUNT(*)')
				->from(Phpfox::getT('user'))
				->where('view_id = 0')
				->execute('getSlaveField');
			
			if ($iTotalMembersMax > 0 && $iCurrentTotalMembers >= $iTotalMembersMax)
			{
				Phpfox_Error::set('We are unable to setup an account for you at this time. This site has currently reached its limit on users.');	
			}
		}
		
		// if (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('user.split_full_name'))
		// {
		// 	if (empty($aVals['first_name']) || empty($aVals['last_name']))
		// 	{
		// 		Phpfox_Error::set( Phpfox::getPhrase('user.please_fill_in_both_your_first_and_last_name'));
		// 	}
		// }
		
		if (!defined('PHPFOX_INSTALLER') && !Phpfox::getParam('user.allow_user_registration'))
		{
			 Phpfox_Error::set( Phpfox::getPhrase('user.user_registration_has_been_disabled'));
			 return false;
		}
		
		$oParseInput = Phpfox::getLib('parse.input');
		$sSalt = $this->_getSalt();
		$aCustom = Phpfox::getLib('request')->getArray('custom');
		
		(($sPlugin = Phpfox_Plugin::get('user.service_process_add_1')) ? eval($sPlugin) : false);		
		/*
		$aCustomFields = Phpfox::getService('custom')->getForEdit(array('user_main', 'user_panel', 'profile_panel'), null, null, true);
		foreach ($aCustomFields as $aCustomField)
		{
			if ($aCustomField['on_signup'] && $aCustomField['is_required'] && empty($aCustom[$aCustomField['field_id']]))
			{
				Phpfox_Error::set( Phpfox::getPhrase('user.the_field_field_is_required', array('field' =>  Phpfox::getPhrase($aCustomField['phrase_var_name']))));
			}
		}
		*/
		/* Check if there should be a spam question answered */
		// $aSpamQuestions = $this->database()->select('*')->from(Phpfox::getT('user_spam'))->execute('getSlaveRows');
		// if (!defined('PHPFOX_INSTALLER') && !defined('PHPFOX_IS_FB_USER') && !empty($aSpamQuestions) && (isset($aVals['spam'])))
		// {			
		// 	$oParse = Phpfox::getLib('parse.input');
		// 	// The visitor's current language is...
		// 	$sLangId = Phpfox::getLib('locale')->getLangId();

		// 	foreach ($aVals['spam'] as $iQuestionId => $sAnswer)
		// 	{
		// 		$aDbQuestion = $this->database()->select('us.*')
		// 			->from(Phpfox::getT('user_spam'), 'us')
		// 			->where('us.question_id = ' . (int) $iQuestionId)
		// 			->execute('getSlaveRow');
					
		// 		if (!isset($aDbQuestion['answers_phrases']) || empty($aDbQuestion['answers_phrases']))
		// 		{
		// 			Phpfox_Error::set( Phpfox::getPhrase('user.that_question_does_not_exist_all_hack_attempts_are_forbidden_and_logged'));
		// 			break;
		// 		}
		// 		// now to compare the answers
		// 		$aAnswers = json_decode($aDbQuestion['answers_phrases']);
		// 		$bValidAnswer = false;
				
		// 		foreach ($aAnswers as $sDbAnswer)
		// 		{					
		// 			if (preg_match('/phrase var=&#039;([a-z\._0-9]+)/', $sDbAnswer, $aMatch))
		// 			{
		// 				$sDbAnswer =  Phpfox::getPhrase($aMatch[1], array(), false, null, $sLangId);
		// 				$sDbAnswer = html_entity_decode($sDbAnswer, null, 'UTF-8');
		// 			}
		// 			if (strcmp($sAnswer, $sDbAnswer) == 0)
		// 			{
		// 				$bValidAnswer = true;
		// 				break;
		// 			}
		// 		}
				
		// 		if ($bValidAnswer == false)
		// 		{
		// 			Phpfox_Error::set( Phpfox::getPhrase('user.captcha_failed'));
		// 			break;
		// 		}

		// 		// $this->database()->delete(Phpfox::getT('upload_track'), 'user_hash = "' . $sHash . '" OR time_stamp < ' . (PHPFOX_TIME - (60*15)));
		// 	}
			
			
		// }
		// else if (!defined('PHPFOX_INSTALLER') && !defined('PHPFOX_IS_FB_USER') && !empty($aSpamQuestions) && !isset($aVals['spam']))
		// {
		// 	Phpfox_Error::set('You forgot to answer the CAPTCHA questions');
		// }
		
		
		if (!Phpfox_Error::isPassed())
		{
			return false;
		}

		// if (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('user.split_full_name'))
		// {
		// 	$aVals['full_name'] = $aVals['first_name'] . ' ' . $aVals['last_name'];
		// }
		
		if (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('user.validate_full_name'))
		{
			if (!Phpfox::getLib('validator')->check($aVals['full_name'], array('html', 'url')))
			{
				return Phpfox_Error::set( Phpfox::getPhrase('user.not_a_valid_name'));
			}
		}
		
		if (!defined('PHPFOX_INSTALLER') && !Phpfox::getService('ban')->check('display_name', $aVals['full_name']))
		{
			Phpfox_Error::set( Phpfox::getPhrase('user.this_display_name_is_not_allowed_to_be_used'));
		}			

		if (!defined('PHPFOX_INSTALLER') && Phpfox::isModule('subscribe') && Phpfox::getParam('subscribe.enable_subscription_packages') && Phpfox::getParam('subscribe.subscribe_is_required_on_sign_up') && empty($aVals['package_id']))
		{
			$aPackages = Phpfox::getService('subscribe')->getPackages(true);
			
			if (count($aPackages))
			{
				return Phpfox_Error::set( Phpfox::getPhrase('user.select_a_membership_package'));
			}
		}

		if (!defined('PHPFOX_INSTALLER'))
		{
		    if (!defined('PHPFOX_SKIP_EMAIL_INSERT'))
		    {
				if (!Phpfox::getLib('mail')->checkEmail($aVals['email']))
			    {
					return Phpfox_Error::set( Phpfox::getPhrase('user.email_is_not_valid'));
			    }
		    }
		    
			if (Phpfox::getLib('parse.format')->isEmpty($aVals['full_name']))
			{
				Phpfox_Error::set( Phpfox::getPhrase('user.provide_a_name_that_is_not_representing_an_empty_name'));
			}		    
		}
		
		if (!isset($_FILES['image']['name']) || empty($_FILES['image']['name']) )
		{
		}
		else
		{
			$aImage = Phpfox::getLib('file')->load('image', array('jpg', 'gif', 'png'), (Phpfox::getUserParam('user.max_upload_size_profile_photo') === 0 ? null : (Phpfox::getUserParam('user.max_upload_size_profile_photo') / 1024)));

			if ($aImage !== false)
			{
				$bHasImage = true;
			}				
		}

		$aInsert = array(
			'user_group_id' => ($iUserGroupId === null ? NORMAL_USER_ID : $iUserGroupId),
			'full_name' => $oParseInput->clean($aVals['full_name'], 255),
			'password' => Phpfox::getLib('hash')->setHash($aVals['password'], $sSalt),
			'password_salt' => $sSalt,
			'email' => $aVals['email'],
			'joined' => PHPFOX_TIME,
			'country_iso' => (defined('PHPFOX_INSTALLER') || (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('core.registration_enable_location')) ? $aVals['country_iso'] : null),
			'language_id' => ((!defined('PHPFOX_INSTALLER') && Phpfox::getLib('session')->get('language_id')) ? Phpfox::getLib('session')->get('language_id') : null),
			'time_zone' => (isset($aVals['time_zone']) && (defined('PHPFOX_INSTALLER') || (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('core.registration_enable_timezone'))) ? $aVals['time_zone'] : null),
			'last_ip_address' => Phpfox::getIp(),
			'last_activity' => PHPFOX_TIME
		);

		if(isset($aVals['gender']) && empty($aVals['gender']) == false)
		{
			$aInsert['gender'] = (defined('PHPFOX_INSTALLER') || (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('core.registration_enable_gender')) ? $aVals['gender'] : 0);
		}

		if(isset($aVals['day']) 
			&& isset($aVals['month'])
			&& isset($aVals['year'])
		)
		{
			$aInsert['birthday'] = Phpfox::getService('user')->buildAge($aVals['day'],$aVals['month'],$aVals['year']);			
			$aInsert['birthday_search'] = Phpfox::getLib('date')->mktime(0, 0, 0, $aVals['month'], $aVals['day'], $aVals['year']);			
		}
		
		if (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('user.invite_only_community') && !Phpfox::getService('invite')->isValidInvite($aVals['email']) && Phpfox::isModule('invite'))
		{
			// the isValidInvite runs Phpfox_Error::set so we do not have to do it here
		}
		
		// pass verify
		// if (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('user.verify_email_at_signup'))
		// {
		// 	$aInsert['status_id'] = 1;// 1 = need to verify email
		// }
		$aInsert['status_id'] = 0;
		
		// pass approval
		if (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('user.approve_users'))
		{
			$aInsert['view_id'] = '1';// 1 = need to approve the user
		}		
		// $aInsert['view_id'] = '0';

		// BA changed logic here: remove username in client sigup form 
		// if (!Phpfox::getParam('user.profile_use_id') && !Phpfox::getParam('user.disable_username_on_sign_up'))
		// {
		// 	$aVals['user_name'] = str_replace(' ', '_', $aVals['user_name']);
		// 	$aInsert['user_name'] = $oParseInput->clean($aVals['user_name']);					
		// }
		
		/*
		if (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('user.maximum_length_for_full_name') > 0 && strlen($aInsert['full_name']) > Phpfox::getParam('user.maximum_length_for_full_name'))
		{
			$aChange = array('iMax' => Phpfox::getParam('user.maximum_length_for_full_name'));
			$sPhrase = Phpfox::getParam('user.display_or_full_name') == 'full_name' ?  Phpfox::getPhrase('user.please_shorten_full_name', $aChange) :  Phpfox::getPhrase('user.please_shorten_display_name', $aChange);
			Phpfox_Error::set($sPhrase);
		}
		*/
		
		(($sPlugin = Phpfox_Plugin::get('user.service_process_add_start')) ? eval($sPlugin) : false);

		if (!Phpfox_Error::isPassed())
		{
			return false;
		}
		$iId = $this->database()->insert($this->_sTable, $aInsert);
		$aInsert['user_id'] = $iId;
		$aExtras = array(
			'user_id' => $iId
		);

		(($sPlugin = Phpfox_Plugin::get('user.service_process_add_extra')) ? eval($sPlugin) : false);

		$this->database()->insert(Phpfox::getT('user_activity'), $aExtras);
		$this->database()->insert(Phpfox::getT('user_field'), $aExtras);
		$this->database()->insert(Phpfox::getT('user_space'), $aExtras);
		$this->database()->insert(Phpfox::getT('user_count'), $aExtras);

		// BA changed logic here: remove username in client sigup form 
		// if (Phpfox::getParam('user.profile_use_id') || Phpfox::getParam('user.disable_username_on_sign_up'))
		// {
			$this->database()->update($this->_sTable, array('user_name' => 'profile-' . $iId), 'user_id = ' . $iId);
		// }
		
		if ($bHasImage)
		{
			$this->uploadImage($iId, true, null, true);
		} else if (isset($aVals['sUserImageUrl']) && $aVals['sUserImageUrl'] != ''){
            if($sImagePath = $this->processUserImage($aVals['sUserImageUrl'], $iId))
            {
                $this->database()->update(Phpfox::getT('user'), array('user_image' => $sImagePath, 'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID')), 'user_id = '.$iId);
            }			
		}		

		if ( Phpfox::isModule('invite') && (Phpfox::getCookie('invited_by_email') || Phpfox::getCookie('invited_by_user')))
		{
			Phpfox::getService('invite.process')->registerInvited($iId);
		}
		elseif (Phpfox::isModule('invite'))
		{
			Phpfox::getService('invite.process')->registerByEmail($aInsert);
		}

		(($sPlugin = Phpfox_Plugin::get('user.service_process_add_feed')) ? eval($sPlugin) : false);
		
		if (!defined('PHPFOX_INSTALLER') && !Phpfox::getParam('user.verify_email_at_signup') && !Phpfox::getParam('user.approve_users') && !isset($bDoNotAddFeed))
		{
			//(Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->allowGuest()->add('user_joined', $iId, null, $iId) : null);
		}

		if (isset($aVals['country_child_id']))
		{
			Phpfox::getService('user.field.process')->update($iId, 'country_child_id', $aVals['country_child_id']);
		}
		
		if (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('user.split_full_name'))
		{
			Phpfox::getService('user.field.process')->update($iId, 'first_name', (empty($aVals['first_name']) ? null :$aVals['first_name']));
			Phpfox::getService('user.field.process')->update($iId, 'last_name', (empty($aVals['last_name']) ? null :$aVals['last_name']));
		}		
		
		if (!defined('PHPFOX_INSTALLER'))
		{
			// Updating for the birthday range
            if(isset($aVals['day'])) {
                $this->database()->update(Phpfox::getT('user_field'), array('birthday_range' => '\''.Phpfox::getService('user')->buildAge($aVals['day'], $aVals['month']) .'\''), 'user_id = ' . $iId, false);
            }
		}
		
		if (!defined('PHPFOX_INSTALLER'))
		{
			$iFriendId = (int) Phpfox::getParam('user.on_signup_new_friend');
			if ($iFriendId > 0 && Phpfox::isModule('friend'))
			{
				$iCheckFriend = $this->database()->select('COUNT(*)')
					->from(Phpfox::getT('friend'))
					->where('user_id = ' . (int) $iId . ' AND friend_user_id = ' . (int) $iFriendId)
					->execute('getSlaveField');
				
				if (!$iCheckFriend)
				{
					$this->database()->insert(Phpfox::getT('friend'), array(
							'list_id' => 0,
							'user_id' => $iId,
							'friend_user_id' => $iFriendId,
							'time_stamp' => PHPFOX_TIME
						)
					);
					
					$this->database()->insert(Phpfox::getT('friend'), array(
							'list_id' => 0,
							'user_id' => $iFriendId,
							'friend_user_id' => $iId,
							'time_stamp' => PHPFOX_TIME
						)
					);
	
					Phpfox::getService('friend.process')->updateFriendCount($iId, $iFriendId);
					Phpfox::getService('friend.process')->updateFriendCount($iFriendId, $iId);
				}
			}
			if ($sPlugin = Phpfox_Plugin::get('user.service_process_add_check_1'))
			{
				eval($sPlugin);
			}
			
			// Allow to send an email even if verify email is disabled
			if ( (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('user.verify_email_at_signup') == false && !isset($bDoNotSendWelcomeEmail)) || isset($bSendWelcomeEmailPlg))
			{
			    Phpfox::getLib('mail')
					->to($iId)
					->subject(array('core.welcome_email_subject', array('site' => Phpfox::getParam('core.site_title'))))
					->message(array('core.welcome_email_content'))
					->send();
			}
			
			switch (Phpfox::getParam('user.on_register_privacy_setting'))
			{
				case 'network':
					$iPrivacySetting = '1';
					break;
				case 'friends_only':
					$iPrivacySetting = '2';
					break;
				case 'no_one':
					$iPrivacySetting = '4';
					break;
				default:
					
					break;
			}
			
			if (isset($iPrivacySetting))
			{
				$this->database()->insert(Phpfox::getT('user_privacy'), array(
						'user_id' => $iId,
						'user_privacy' => 'profile.view_profile',
						'user_value' => $iPrivacySetting
					)
				);			
			}
		}
		
		(($sPlugin = Phpfox_Plugin::get('user.service_process_add_end')) ? eval($sPlugin) : false);
		
		if (!empty($aCustom))
		{
			if (!Phpfox::getService('custom.process')->updateFields($iId, $iId, $aCustom, true))
			{
				Phpfox_Error::set("Missing custom fields");
				return false;
			}
		}		
		
		$this->database()->insert(Phpfox::getT('user_ip'), array(
				'user_id' => $iId,
				'type_id' => 'register',
				'ip_address' => Phpfox::getIp(),
				'time_stamp' => PHPFOX_TIME
			)
		);			
		
		if (!defined('PHPFOX_INSTALLER') && Phpfox::getParam('user.verify_email_at_signup') && !isset($bSkipVerifyEmail))
		{
			$aVals['user_id'] = $iId;
			$sHash = Phpfox::getService('user.verify')->getVerifyHash($aVals);
			$this->database()->insert(Phpfox::getT('user_verify'), array('user_id' => $iId, 'hash_code' => $sHash, 'time_stamp' => Phpfox::getTime(), 'email' => $aVals['email']));
			// send email
			$sLink = Phpfox::getLib('url')->makeUrl('user.verify', array('link' => $sHash));
			Phpfox::getLib('mail')
				->to($iId)
				->subject(array('user.please_verify_your_email_for_site_title', array('site_title' => Phpfox::getParam('core.site_title'))))
				->message(array('user.you_registered_an_account_on_site_title_before_being_able_to_use_your_account_you_need_to_verify_that_this_is_your_email_address_by_clicking_here_a_href_link_link_a', array(
							'site_title' => Phpfox::getParam('core.site_title'),
							'link' => $sLink
						)
					)
				)
				->send();
		}
		
		if (!defined('PHPFOX_INSTALLER') && Phpfox::isModule('subscribe') && Phpfox::getParam('subscribe.enable_subscription_packages') && !empty($aVals['package_id']))
		{

			$aPackage = Phpfox::getService('subscribe')->getPackage($aVals['package_id']);
			if (isset($aPackage['package_id']))
			{
				$iPurchaseId = Phpfox::getService('subscribe.purchase.process')->add(array(
						'package_id' => $aPackage['package_id'],
						'currency_id' => $aPackage['default_currency_id'],
						'price' => $aPackage['default_cost']
					), $iId
				);
				
				$iDefaultCost = (int) str_replace('.', '', $aPackage['default_cost']);
				
				if ($iPurchaseId)
				{
					if ($iDefaultCost > 0)
					{							
						define('PHPFOX_MUST_PAY_FIRST', $iPurchaseId);
						
						Phpfox::getService('user.field.process')->update($iId, 'subscribe_id', $iPurchaseId);
					
						return array(
							'iId' => $iId, 
							'iPurchaseId' => $iPurchaseId, 
							'iPackageId' => $aPackage['package_id'], 
						);
					}
					else 
					{						
						Phpfox::getService('subscribe.purchase.process')->update($iPurchaseId, $aPackage['package_id'], 'completed', $iId, $aPackage['user_group_id'], $aPackage['fail_user_group']);
					}
				}
				else 
				{
					return false;
				}				
			}
		}		

		return array(
			'iId' => $iId, 
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

	public function uploadImage($iId, $bForce = true, $sPath = null, $bNoCheck = false)
	{		
		// if ($iId != Phpfox::getUserId() && $sPath === null && $bNoCheck === false)
		// {
		// 	Phpfox::getUserParam('user.can_change_other_user_picture', true);
		// }

		$oFile = Phpfox::getLib('file');
		$oImage = Phpfox::getLib('image');
		
		if ($bForce)
		{
			$sUserImage = Phpfox::getUserBy('user_image');
			if ($iId != Phpfox::getUserId())
			{
				$sUserImage = $this->database()->select('user_image')
					->from(Phpfox::getT('user'))
					->where('user_id = ' . (int) $iId)
					->execute('getSlaveField');
			}
			
			if (!empty($sUserImage))
			{			
				if (file_exists(Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '')))
				{
					$oFile->unlink(Phpfox::getParam('core.dir_user') . sprintf($sUserImage, ''));
					foreach(Phpfox::getParam('user.user_pic_sizes') as $iSize)
					{
						if (file_exists(Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '_' . $iSize)))
						{
							$oFile->unlink(Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '_' . $iSize));
						}
						
						if (file_exists(Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '_' . $iSize . '_square')))
						{
							$oFile->unlink(Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '_' . $iSize . '_square'));
						}					
					}
				}
			}
		}
		
		(($sPlugin = Phpfox_Plugin::get('user.service_process_uploadimage')) ? eval($sPlugin) : false);
		
		if ($sPath === null)
		{
			$sFileName = $oFile->upload('image', Phpfox::getParam('core.dir_user'), $iId);
		}
		else
		{
			$sFileName = $iId . '%s.' . substr($sPath, -3);
			$sTo = Phpfox::getParam('core.dir_user') . sprintf($sFileName,'');
			
			if (file_exists($sTo))
			{
				$oFile->unlink($sTo);
			}
			if (!$oFile->copy($sPath, $sTo))
			{
				
			}			
			
			if (Phpfox::getParam('core.allow_cdn'))
			{
				$bReturn = Phpfox::getLib('cdn')->put($sTo);					
			}			
		}
		
		if (true)
		{			
			if ($bForce)
			{
				$iServerId = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
	
				foreach(Phpfox::getParam('user.user_pic_sizes') as $iSize)
				{
					if (Phpfox::getParam('core.keep_non_square_images'))
					{
						$oImage->createThumbnail(Phpfox::getParam('core.dir_user') . sprintf($sFileName, ''), Phpfox::getParam('core.dir_user') . sprintf($sFileName, '_' . $iSize), $iSize, $iSize);
					}
					$oImage->createThumbnail(Phpfox::getParam('core.dir_user') . sprintf($sFileName, ''), Phpfox::getParam('core.dir_user') . sprintf($sFileName, '_' . $iSize . '_square'), $iSize, $iSize, false);
				
					if (defined('PHPFOX_IS_HOSTED_SCRIPT'))
					{
						unlink(Phpfox::getParam('core.dir_user') . sprintf($sFileName, '_' . $iSize));
						unlink(Phpfox::getParam('core.dir_user') . sprintf($sFileName, '_' . $iSize . '_square'));
					}				
				}				
			
				if (defined('PHPFOX_IS_HOSTED_SCRIPT'))
				{
					unlink(Phpfox::getParam('core.dir_user') . sprintf($sFileName, ''));
				}
				
				$this->database()->update($this->_sTable, array('user_image' => $sFileName, 'server_id' => $iServerId), 'user_id = ' . (int) $iId);
	
				if (!Phpfox::getUserBy('profile_page_id') && !defined('PHPFOX_PAGES_IS_IN_UPDATE') && $iId == Phpfox::getUserId())
				{
					(Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->delete('user_photo', $iId) : null);
					(Phpfox::isModule('feed') ? Phpfox::getService('feed.process')->add('user_photo', $iId) : null);
				}
				
				if (!defined('PHPFOX_USER_PHOTO_IS_COPY') && Phpfox::isModule('photo'))
				{
					Phpfox::getService('photo.album')->getForProfileView($iId, true);
				}
				
				// $this->saveUserCache($iId);
				
				return array('user_image' => $sFileName, 'server_id' => $iServerId);
			}			
			
			/*if (!defined('PHPFOX_USER_PHOTO_IS_COPY') && Phpfox::isModule('photo'))
			{
				Phpfox::getService('photo.album')->getForProfileView($iId, true);
			}*/
			
			// $this->saveUserCache($iId);

			return array('user_image' => $sFileName);
		}

		return false;
	}

	public function updateStatusWithCheckin($aVals)
	{
		// if (isset($aVals['user_status']) && ($iId = Phpfox::getService('user.process')->updateStatus($aVals)))
		if (isset($aVals['user_status']) && ($iId = $this->postCheckinStatus($aVals)))
		{
			return $iId;		
		}
		else 
		{
			return false;
		}
	}

    public function getCustomValue($sField, $iUserId) {
        $aRow = $this->database()->select('*')
             ->from(Phpfox::getT('user_custom_value'))
             ->where('user_id = ' . $iUserId)
             ->execute('getRow');
        

        if(isset($aRow[$sField])) {
            return $aRow[$sField];
        } else {
            return '';
        }
    }

    public function checkUserHaveCustomData($iUserId) {

        $aRow = $this->database()->select('user_id')
             ->from(Phpfox::getT('user_custom_value'))
             ->where('user_id = ' . $iUserId)
             ->execute('getRow');

        if(isset($aRow['user_id']) && $aRow['user_id'] > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function updateCustomField($sField, $sValue, $iUserId) {
        $aUpdate = array(
            $sField => Phpfox::getLib('parse.input')->clean($sValue)
        );

		$sTable = 'user_custom';
		$aExisting = $this->database()->select('user_id as userCustom, ' . $sField  . ' as currentValue')
				->from(Phpfox::getT($sTable))
				->where('user_id = ' . (int)$iUserId)
				->execute('getSlaveRow');
		if (isset($aExisting['userCustom']) && !empty($aExisting['userCustom']))
		{
			$this->database()->update(Phpfox::getT($sTable), array(
				$sField => $sValue
			), 'user_id = ' . (int)$iUserId);
		}
		else
		{
			$this->database()->insert(Phpfox::getT($sTable),array('user_id' => (int)$iUserId, $sField => $iUserId));
		}

        if(!$this->checkUserHaveCustomData($iUserId)) {
            $aUpdate['user_id'] = $iUserId;
            $this->database()->insert(Phpfox::getT('user_custom_value'), $aUpdate);
        } else {
            $this->database()->update(Phpfox::getT('user_custom_value'), $aUpdate, 'user_id = ' . $iUserId);
        }

    }

    public function getTokenDataFromSocialBridgeByIdentity($identity, $service = 'facebook'){
		// get latest token
		return $this -> database() -> select('*') 
					-> from(Phpfox::getT('socialbridge_token'), 'sbt') 
					-> where('sbt.identity = \'' . $this->database()->escape($identity) . '\' AND sbt.service = \'' . $this->database()->escape($service) . '\' ') 
					->order('sbt.timestamp DESC')
					->limit(1)
					-> execute('getSlaveRow');    	
    }

    public function getUserByEmail($sEmail){
		if (empty($sEmail))
		{
			return false;
		}
		$aRow = $this->database()->select('u.*')
			->from(Phpfox::getT('user'), 'u')
			->where(('u.email = \'' . $this->database()->escape($sEmail) . '\' '))
			->execute('getSlaveRow');
		
		return $aRow;    	
    }

    public function getCustomFieldByName($name){
		$aField = $this->database()->select('cf.*')
			->from(Phpfox::getT('custom_field'), 'cf')
			->where('cf.field_name = \'' . $name . '\'')
			->join(Phpfox::getT('module'), 'm', 'm.module_id = cf.module_id AND m.is_active = 1')
			->execute('getSlaveRow');

		return $aField;
    }

	public function loadImage($image_url, $sFilePath)
    {        
		$image_url = html_entity_decode($image_url, ENT_QUOTES, 'UTF-8');
		
		$ch = curl_init($image_url);
		$fp = fopen($sFilePath, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);

		return $sFilePath;
    }

    public function processUserImage($sImgUrl, $iUserId)
    {
        $oFile = Phpfox::getLib('file');
        $oImage = Phpfox::getLib('image');
        
        $sFileName = md5($iUserId . PHPFOX_TIME . uniqid());
        $sFileDir = $oFile->getBuiltDir(Phpfox::getParam('core.dir_user'));
        $sFilePath = $sFileDir . $sFileName . '%s.jpg';
        $sImagePath = str_replace(Phpfox::getParam('core.dir_user'), '', $sFilePath);
        
        $this->loadImage($sImgUrl, sprintf($sFilePath, ''));
        //put file to CDN
        if (Phpfox::getParam('core.allow_cdn'))
        {
            Phpfox::getLib('cdn')->put(sprintf($sFilePath, ''));
        }

        $iFileSize = filesize(sprintf($sFilePath, ''));
        if($iFileSize)
        {
            foreach(Phpfox::getParam('user.user_pic_sizes') as $iSize)
    		{
    			$oImage->createThumbnail(sprintf($sFilePath, ''), sprintf($sFilePath, '_' . $iSize), $iSize, $iSize);
    			$oImage->createThumbnail(sprintf($sFilePath, ''), sprintf($sFilePath, '_' . $iSize . '_square'), $iSize, $iSize, false);				
    		}
            
            return $sImagePath;
        }
        
        return false;
    }

    public function setUserSession(){
    	
		if(!Phpfox::getUserId()) return ;
		
		$this->database()->update(Phpfox::getT('session'), array(
					'last_activity' => PHPFOX_TIME							
				), 'user_id = ' . (int) Phpfox::getUserId());
		
		$this->database()->update(Phpfox::getT('log_session'), array(
					'last_activity' => PHPFOX_TIME							
				), 'user_id = ' . (int) Phpfox::getUserId());
		$this->database()->update(Phpfox::getT('user'), array(
					'last_activity' => PHPFOX_TIME							
				), 'user_id = ' . (int) Phpfox::getUserId());
		$row = Phpfox::getLib('database')
			->select('*')
			->from(Phpfox::getT('log_session'))
			->where('user_id='. Phpfox::getUserId())
			->execute('getSlaveRow');
			
		if($row)
			return ;

		$oSession = Phpfox::getLib('session');
		$oRequest = Phpfox::getLib('request');
		
		$sSessionHash = $oSession->get('session');		

		if (Phpfox::getParam('core.store_only_users_in_session'))
		{
			$aSession = Phpfox::getService('user.auth')->getUserSession();
		}
		else
		{

			$aSession = Phpfox::getService('user.auth')->getUserSession();
			
			if ($sSessionHash)
			{

				
				if (!isset($aSession['session_hash']) && !Phpfox::getParam('core.store_only_users_in_session'))
				{				

					$this->database()->where("s.session_hash = '" . $this->database()->escape($oSession->get('session')) . "' AND s.id_hash = '" . $this->database()->escape($oRequest->getIdHash()) . "'");
					
					$aSession = $this->database()->select('s.session_hash, s.id_hash, s.captcha_hash, s.user_id')
						->from(Phpfox::getT('log_session'), 's')					
						->execute('getRow');			
				}
			}		
		}
		
		$sLocation = $oRequest->get(PHPFOX_GET_METHOD);
		$sLocation = substr($sLocation, 0, 244);
		$sBrowser = substr(Phpfox::getLib('request')->getBrowser(), 0, 99);	
		$sIp = Phpfox::getLib('request')->getIp();			

		if (Phpfox::getParam('core.log_site_activity'))
		{
			// Unsure why this is here. Causes http://www.phpfox.com/tracker/view/15330/
			// Perhaps instead of the database delete, the log is only for logged in users?
			// I cannot find a reason why the script should log guests activity.
			// Besides, guest activity may increase the number of inserts into this table very largely
            /*if(Phpfox::getUserId() > 0) 
			{
				$this->database()->delete(Phpfox::getT('log_session'), 'user_id = ' . Phpfox::getUserId());
			}*/
			// Like this:
			if(Phpfox::getUserId() > 0)
			{
				$this->database()->insert(Phpfox::getT('log_view'), array(
						'user_id' => Phpfox::getUserId(),				
						'ip_address' => $sIp,				
						'protocal' => $_SERVER['REQUEST_METHOD'],				
						'cache_data' => serialize(array(
								'location' => $_SERVER['REQUEST_URI'],
								'referrer' => (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null),
								'user_agent' => $_SERVER['HTTP_USER_AGENT'],
								'request' => (strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' ? serialize($_POST) : serialize($_GET))
							)
						),
						'time_stamp' => PHPFOX_TIME
					)
				);
			}
		}

		/**
		 * @todo Needs to be added into the 'setting' db table
		 */
		$aDisAllow = array(
			'captcha/image'
		);
		
		// do not log a session into the DB if we disallow it
		if (Phpfox::getLib('url')->isUrl($aDisAllow))
		{
			return;
		}	
		
		$bIsForum = (strstr($sLocation, Phpfox::getParam('core.module_forum')) ? true : false);
		$iForumId = 0;
		if ($bIsForum)
		{
			$aForumIds = explode('-', $oRequest->get('req2'));
			if (isset($aForumIds[(count($aForumIds) - 1)]))
			{
				$iForumId = (int) $aForumIds[(count($aForumIds) - 1)];				
			}			
		}
		
		$iIsHidden = 0;
		if (Phpfox::isUser())
		{

			if (isset($aSession['is_invisible']) && $aSession['is_invisible'])
			{

				$iIsHidden = 1;	
			}			
		}
		
		if (Phpfox::getParam('core.store_only_users_in_session'))
		{
			if (Phpfox::isUser())
			{
				if (!isset($aSession['session_hash']))
				{
					if(Phpfox::getUserId() > 0)
					{
						$this->database()->delete(Phpfox::getT('session'), 'user_id = ' . Phpfox::getUserId());
					}
					$this->database()->insert(Phpfox::getT('session'), array(
							'user_id' => Phpfox::getUserId(),
							'last_activity' => PHPFOX_TIME
						)
					);
				}
				else
				{
					$this->database()->update(Phpfox::getT('session'), array(
							'last_activity' => PHPFOX_TIME							
					), 'user_id = ' . (int) Phpfox::getUserId());
				}	
			}		
		}
		else
		{		
			if (!isset($aSession['session_hash']))
			{

				$sSessionHash = $oRequest->getSessionHash();

				if(Phpfox::getUserId() > 0) 
				{
					$this->database()->delete(Phpfox::getT('log_session'), 'user_id = ' . Phpfox::getUserId());
				}
				$this->database()->insert(Phpfox::getT('log_session'), array(
						'session_hash' => $sSessionHash,
						'id_hash' => $oRequest->getIdHash(),
						'user_id' => Phpfox::getUserId(),
						'last_activity' => PHPFOX_TIME,
						'location' => $sLocation,
						'is_forum' => ($bIsForum ? '1' : '0'),
						'forum_id' => $iForumId,
						'im_hide' => $iIsHidden,
						'ip_address' => $sIp,
						'user_agent' => $sBrowser
					)
				);
				$oSession->set('session', $sSessionHash);
			}
			else if (isset($aSession['session_hash']))
			{
				$this->database()->update(Phpfox::getT('log_session'), array(
					'last_activity' => PHPFOX_TIME, 
					'user_id' => Phpfox::getUserId(),
					"location" => $sLocation,
					"is_forum" => ($bIsForum ? "1" : "0"),
					"forum_id" => $iForumId,
					'im_hide' => $iIsHidden,
					"ip_address" => $sIp,
					"user_agent" => $sBrowser
				), "session_hash = '" . $aSession["session_hash"] . "'");	
			}	
		}			
			
		if (!Phpfox::getCookie('visit'))
		{
			Phpfox::setCookie('visit', PHPFOX_TIME);			
		}		
		
		if (Phpfox::isUser())
		{
			if (!Phpfox::getCookie('last_login'))
			{			
				Phpfox::setCookie('last_login', PHPFOX_TIME, (PHPFOX_TIME + (Phpfox::getParam('log.active_session') * 60)));
				if (Phpfox::getUserBy('last_activity') < (PHPFOX_TIME + (Phpfox::getParam('log.active_session') * 60)))
				{
					$this->database()->update(Phpfox::getT('user'), array('last_login' => PHPFOX_TIME), 'user_id = ' . Phpfox::getUserId());
					$this->database()->insert(Phpfox::getT('user_ip'), array(
							'user_id' => Phpfox::getUserId(),
							'type_id' => 'session_login',
							'ip_address' => Phpfox::getIp(),
							'time_stamp' => PHPFOX_TIME
						)
					);	
				}
			}		
			
			if (!Phpfox::getParam('user.disable_store_last_user'))
			{
				$this->database()->update(Phpfox::getT('user'), array('last_activity' => PHPFOX_TIME, 'last_ip_address' => Phpfox::getIp()), 'user_id = ' . Phpfox::getUserId());
			}
		}
		
	
    }    

}
