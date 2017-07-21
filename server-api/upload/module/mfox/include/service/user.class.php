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
 * @since June 5, 2013
 * @link Mfox Api v1.0
 */
class Mfox_Service_User extends Phpfox_Service
{
	
	
	CONST COVER_SIZE  = '_500';
	/**
     * Input data:
     * 
     * Output data:
     * + sFullName: string.
     * + CoverImg_Url: string.
     * + iUserId: int.
     * + UserProfileImg_Url: string.
     * + sWorkat: string.
     * + sGraduated: string.
     * + sFrom: string.
     * + isFriend: bool.
     * + PhotoImg_Url: string.
     * + FriendImg1_Url: string.
     * + FriendImg2_Url: string.
     * + FriendImg3_Url: string.
     * + FriendImg4_Url: string.
     * + FriendImg5_Url: string.
     * + FriendImg6_Url: string.
     * + PhotoImage_Url: string.
     * + CoverImg_Url: string.
     * 
     * @see Mobile - API phpFox/Api V1.0 - Restful.
     * @see home
     * 
     * @param array $aVals
     * @param int $iForceUserId
     * @return array
     */
    function getByIdAction($aData, $iId)
    {
        return $this->home($aData, $iId);
    }
    
    /**
     * Input data:
     * + sEmail: string, required.
     * 
     * Output data:
     * + error_code: int.
     * + error_message: string.
     * 
     * @see Mobile - API phpFox/Api V2.0.
     * @see user/forgot
     * 
     * @see Phpfox_Mail
     * @param array $aData
     * @return array
     */
    public function forgot($aData)
    {
        /**
         * @var string.
         */
        $sEmail = isset($aData['sEmail']) ? $aData['sEmail'] : '';
        /**
         * @var array
         */
        $aUser = $this->database()->select('user_id, profile_page_id, email, full_name')
			->from($this->_sTable)
			->where('email = \'' . $this->database()->escape($sEmail) . '\'')
			->execute('getRow');
			
		if (!isset($aUser['user_id']))
		{
            return array(
                'error_message' =>  Phpfox::getPhrase('user.not_a_valid_email'),
                'error_code' => 1
            );
		}
		
		if (empty($aUser['email']) || $aUser['profile_page_id'] > 0)
		{
            return array(
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.unable_to_attain_a_password_for_this_account")),
                'error_code' => 1
            );
		}
			
		// Send the user an email
		$sHash = md5($aUser['user_id'] . $aUser['email'] . Phpfox::getParam('core.salt'));
		$sLink = Phpfox::getLib('url')->makeUrl('user.password.verify', array('id' => $sHash));
        
		Phpfox::getLib('mail')->to($aUser['user_id'])
			->subject(array('user.password_request_for_site_title', array('site_title' => Phpfox::getParam('core.site_title'))))
			->message(array('user.you_have_requested_for_us_to_send_you_a_new_password_for_site_title', array(
						'site_title' => Phpfox::getParam('core.site_title'),
						'link' => $sLink
					)
				)
			)
			->send();
		
		$this->database()->delete(Phpfox::getT('password_request'), 'user_id = ' . $aUser['user_id']);
		$this->database()->insert(Phpfox::getT('password_request'), array(
				'user_id' => $aUser['user_id'],
				'request_id' => $sHash,
				'time_stamp' => PHPFOX_TIME
			)
		);
		
        return array(
            'message' =>  Phpfox::getPhrase('user.password_request_successfully_sent_check_your_email_to_verify_your_request'),
            'error_code' => 0
        );
    }
    /**
     * Validate for register.
     * @param string $sStep
     * @return string
     */
    public function getValidation($sStep = null)
	{		
		$aValidation = array();

		if ($sStep == 1 || $sStep === null)
		{
			$aValidation['full_name'] =  Phpfox::getPhrase('user.provide_your_full_name');
			
			$aValidation['email'] = array(
				'def' => 'email',
				'title' =>  Phpfox::getPhrase('user.provide_a_valid_email_address')
			);
			$aValidation['password'] = array(
				'def' => 'password',
				'title' =>  Phpfox::getPhrase('user.provide_a_valid_password')
			);
			
		}
		return $aValidation;
	}
    
    /**
     * Input data:
     * + sFullName: string, required.
     * + sEmail: string, required.
     * + sPassword: string, required.
     * + iMonth: int, required.
     * + iDay: int, required.
     * + iYear: int, required.
     * + iGender: int, required.
     * 
     * Output data:
     * + error_message: string.
     * + error_code: int.
     * + result: int.
     * + user_id: int.
     * + full_name: string.
     * + user_name: string.
     * + profileimage: string.
     * + token: string.
     * 
     * @see Mobile - API phpFox/Api V2.0.
     * @see user/register
     * 
     * @param array $aData
     * @return array
     */
    public function register($aData)
    {
        
    }

    /**
     * Check email is ban.
     * @param string $sEmail
     * @return \Mfox_Service_User
     */
    private function email($sEmail)
	{
		$iCnt = $this->database()->select('COUNT(*)')
			->from($this->_sTable)
			->where("email = '" . $this->database()->escape($sEmail) . "'")
			->execute('getField');
		
		if ($iCnt)
		{
            $sMessage = 'There is already an account assigned with the email "' . trim(strip_tags($sEmail)) . '". If this is your email please login.';
			Phpfox_Error::set($sMessage);
		}
		
		if (!Phpfox::getService('ban')->check('email', $sEmail))
		{
			Phpfox_Error::set( Phpfox::getPhrase('user.this_email_is_not_allowed_to_be_used'));
		}		
		
		return $this;
	}
    
	private $_aUser = array();

	/**
	 * constructor
	 * @return void
	 */
	function __construct()
	{
		$this -> _sTable = Phpfox::getT('user');
	}

    /**
     * Input data:
     * 
     * Output data:
     * + sFullName: string.
     * + CoverImg_Url: string.
     * + iUserId: int.
     * + UserProfileImg_Url: string.
     * + sWorkat: string.
     * + sGraduated: string.
     * + sFrom: string.
     * + isFriend: bool.
     * + PhotoImg_Url: string.
     * + FriendImg1_Url: string.
     * + FriendImg2_Url: string.
     * + FriendImg3_Url: string.
     * + FriendImg4_Url: string.
     * + FriendImg5_Url: string.
     * + FriendImg6_Url: string.
     * + PhotoImage_Url: string.
     * + CoverImg_Url: string.
     * 
     * @param array $aVals
     * @param int $iForceUserId
     * @return array
     */
    function home($aVals, $iForceUserId = 0)
    {
        extract($aVals, EXTR_SKIP);

        if ($iForceUserId > 0)
        {
            $iUserId = $iForceUserId;
        }
		
		if (!isset($iUserId))
		{
			$iUserId = Phpfox::getUserId();
		}
        /**
         * @var array
         */
		$aUser = Phpfox::getService('user') -> get($iUserId);

		list($iCount, $aFriends) =  Phpfox::getService('friend')->get($aCond ='', $sSort = 'friend.time_stamp DESC', $iPage = '', $sLimit = 6, $bCount = true, $bAddDetails = false, $bIsOnline = false, $iUserId , $bIncludeList = false, $iListId = 0);

		$aFriendImages = array();
		
		for($i = 0; $i<6; ++$i)
		{
            $aFriendImages[] = $this->getImageUrl($aFriends[$i], MAX_SIZE_OF_USER_IMAGE);
		}
		
		if($aUser['cover_photo']){
			$aCoverPhoto  = Phpfox::getService('photo')->getCoverPhoto($aUser['cover_photo']);
			if($aCoverPhoto){
				$aCoverPhotoUrl  =  Phpfox::getParam('photo.url_photo') . sprintf($aCoverPhoto['destination'],self::COVER_SIZE);
			}
		}
        
        $aPhotos = Phpfox::getService('mfox.photo')->getMyLatestPhoto($iUserId);
        $sPhotoImageUrl = $this->getImageUrl(array(), '_50');
        if (isset($aPhotos[0]['sPhotoUrl']))
        {
            $sPhotoImageUrl = $aPhotos[0]['sPhotoUrl'];
        }
		return array(
			'sFullName' => $aUser['full_name'],
			'CoverImg_Url'=>$aUser['cover_photo'],
			'iUserId' => $iUserId,
			'UserProfileImg_Url' => $this->getImageUrl($aUser, '_100_square'),
			'sWorkat' => '[no data]',
			'sGraduated' => '[no data]',
			'sFrom' => $aUser['city_location'],
			'isFriend' => $aUser['is_friend'],
			'PhotoImg_Url' => $sPhotoImageUrl,
			'FriendImg1_Url' => $aFriendImages[0],
			'FriendImg2_Url' => $aFriendImages[1],
			'FriendImg3_Url' => $aFriendImages[2],
			'FriendImg4_Url' => $aFriendImages[3],
			'FriendImg5_Url' => $aFriendImages[4],
			'FriendImg6_Url' => $aFriendImages[5],
			'PhotoImage_Url' => $this->getImageUrl($aUser, '_100_square'),
            'CoverImg_Url' => $aCoverPhotoUrl,
		);	
	}

	/**
     * Get user by email.
     * @param string $sEmail
     * @return array|null
     */
	function getUserByEmail($sEmail)
	{
		$sEmail = $this -> database() -> escape($sEmail);
		$sCond = "email='{$sEmail}'";
		$aRow = $this -> database() -> select('*') -> from($this -> _sTable) -> where($sCond) -> execute('getSlaveRow');
		return $aRow;
	}

	/**
	 * @see User_Service_Auth
	 * @see Phpfox_Hash
	 *
	 * @param string $sPasswordHash hashed password in database
	 * @param string $sPasswordSalt salt string int database
	 * @param string $sPassword password string send from client
	 * @return TRUE|FALSE
	 */
	function checkPassword($sPasswordHash, $sPasswordSalt, $sPassword)
	{
		return $sPasswordHash == Phpfox::getLib('hash') -> setHash($sPassword, $sPasswordSalt);
	}

	/**
	 * login user id
	 * <pre>
	 * result: array{token: , error_code, error_message, user_id}
	 * </pre>
	 * @return array {token: required}
	 */

	/**
	 * Input data:
	 * + sLogin: string, required.
	 * + sPassword: string, required.
	 *
	 * Output data:
	 * + error_message: string.
	 * + error_code: int.
	 * + result: int.
	 * + user_id: int.
	 * + full_name: string.
	 * + user_name: string.
	 * + profileimage: string.
	 * + token: string.
	 *
	 */
	public function login($aData)
	{
		//	check login with social connect 
		$sLoginBy = $aData['sLoginBy']; 
		if (isset($sLoginBy))
		{
			$sLoginBy = strtolower($sLoginBy);
			if ( !in_array($sLoginBy, array('facebook', 'twitter', 'linkedin')) )
			{
				return array(
						'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.login_by_invalid_method")),
						'error_code' => 9
				);
			}
			else 
			{
				if (Phpfox::isModule('socialbridge')){
					if (Phpfox::getService('socialbridge')->hasProvider($sLoginBy)){
						$methodName = "loginBy" . ucfirst($sLoginBy);
						if (method_exists($this, $methodName))
						{
							return $this->{$methodName}($aData);
						}
					} else {
						return array(
								'error_message' => $sLoginBy . ' service needs to be configurated and enabled in server site.',
								'error_code' => 11
						);
					}
				} else {
					return array(
							'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.server_needs_to_be_installed_socialbridge_module_first")),
							'error_code' => 10
					);
				}					
			}
		}

		$sPassword = isset($aData['sPassword']) ? $aData['sPassword'] : '';
        $sLogin = isset($aData['sLogin']) ? $aData['sLogin'] : (isset($aData['sEmail']) ? $aData['sEmail'] : '');

        //  check params 
        if (empty($sLogin))
        {
            $sErrorMessage = '';
            switch (Phpfox::getParam('user.login_type'))
            {
                case 'user_name':
                    $sErrorMessage = html_entity_decode(Phpfox::getPhrase('user.provide_your_user_name'));
                    break;
                case 'email':
                    $sErrorMessage = html_entity_decode(Phpfox::getPhrase('user.provide_your_email'));
                    break;				
                default:
                    $sErrorMessage = html_entity_decode(Phpfox::getPhrase('user.provide_your_user_name_email'));
            }

            return array(
                'error_message'=> $sErrorMessage,
                'error_element' => 'login',
                'error_code' => 1
            );
        }

		if (empty($sPassword))
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase('user.provide_your_password')),
				'error_element' => 'password',
				'error_code' => 2
			);
		}

        $sSelect = 'user_id, email, user_name, password, password_salt, status_id, user_group_id';
        $sLogin = $this->database()->escape($sLogin);
        $sType = Phpfox::getParam('user.login_type');

        $aUser = $this->database()->select($sSelect)
            ->from($this->_sTable)
            ->where(($sType == 'both' ? "email = '" . $sLogin . "' OR user_name = '" . $sLogin . "'" : ($sType == 'email' ? "email" : "user_name") . " = '" . $sLogin . "'"))
            ->execute('getRow');
		
		if($aUser['view_id']==2){
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.your_account_has_been_denied")),
				'error_code' => 1,
				'result' => 0
			);
		}
		if (isset($aUser['status_id']) && $aUser['status_id'] == 1) // 0 good status; 1 => need to verify
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.user_need_to_verify_first")),
				'error_code' => 4,
				'result' => 0
			);	
		}

        if (!isset($aUser['user_name']))
        {
            switch (Phpfox::getParam('user.login_type'))
            {
                case 'user_name':
                    $sMessage = Phpfox::getPhrase('user.invalid_user_name');
                    break;
                case 'email':
                    $sMessage = Phpfox::getPhrase('user.invalid_email');
                    break;
                default:
                    $sMessage = Phpfox::getPhrase('user.invalid_login_id');
            }
    
                Phpfox_Error::set($sMessage);
                if ($sPlugin = Phpfox_Plugin::get('user.service_auth_login__no_user_name')){eval($sPlugin);}
                //return array(false, $aUser);
                $bReturn = true;
        }
        else
        {
            $bDoPhpfoxLoginCheck = true;
            if ($sPlugin = Phpfox_Plugin::get('user.service_auth_login__password')){eval($sPlugin);}

            if (strlen($aUser['password']) > 32) {
                $Hash = new Core\Hash();
                if (!$Hash->check($sPassword, $aUser['password'])) {
                    Phpfox_Error::set(Phpfox::getPhrase('user.invalid_password'));
                    $bReturn = true;
                }
            }
            else {
                if (!$bNoPasswordCheck && $bDoPhpfoxLoginCheck && (Phpfox::getLib('hash')->setHash($sPassword, $aUser['password_salt']) != $aUser['password']))
                {
                    Phpfox_Error::set(Phpfox::getPhrase('user.invalid_password'));
                    //return array(false, $aUser);
                    $bReturn = true;
                }
            }
        }
        
        // ban check
		$oBan = Phpfox::getService('ban');
		if (!$oBan->check('email', $aUser['email']))
		{
			Phpfox_Error::set( Phpfox::getPhrase('ban.global_ban_message'));
		}

		$aBanned = Phpfox::getService('ban')->isUserBanned($aUser);
		if ( $aBanned['is_banned'])
		{
			if (isset($aBanned['reason']) && !empty($aBanned['reason']))
			{
				$aBanned['reason'] = str_replace('&#039;', "'", Phpfox::getLib('parse.output')->parse($aBanned['reason']));
				$sReason = preg_replace('/\{phrase var=\'(.*)\'\}/ise', "'' .  Phpfox::getPhrase('\\1',array(), false, null, '" . Phpfox::getUserBy('language_id') . "') . ''", $aBanned['reason']);
				Phpfox_Error::set($sReason);
			}
			else
			{
				Phpfox_Error::set( Phpfox::getPhrase('ban.global_ban_message'));
			}
		}

		if (Phpfox::getService('user.group.setting')->getGroupParam($aUser['user_group_id'], 'core.user_is_banned'))
		{
			Phpfox_Error::set( Phpfox::getPhrase('ban.global_ban_message'));
		}

        if (!Phpfox_Error::isPassed())
        {
            return array(
				'error_message' => html_entity_decode(implode(' ', Phpfox_Error::get())),
				'error_code' => 1,
				'result' => 0
			);
        }        

		return $this->_loadUserAuthData($aUser['user_id']);
		
	}

	protected function loginByFacebook($aData){
		// sLoginUID == identity in profile 
		if (!isset($aData['sLoginUID']))
		{
			return array(
				'error_code' => 10,
				'error_element' => 'login facebook',
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_sloginuid"))
			);
		}
		else 
		{
			//	get user by sLoginUID on social bridge

			// fetch user from agent table.
			$iUserId = 0;

			$sbData  = phpFox::getLib('database')
					->select('*')
					->from(phpFox::getT('socialbridge_agents'))
					->where('service_id=\'1\' and identity='."'". $aData['sLoginUID']."'" )
					->order('user_id desc') 
					->execute('getRow');

			if($sbData && !empty($sbData['user_id'])){
				$iUserId =  $sbData['user_id'];
			} else {
				$sbData  = phpFox::getLib('database')
						->select('*')
						->from(phpFox::getT('socialbridge_token'))
						->where('service=\'facebook\' and identity='."'". $aData['sLoginUID']."'" )
						->execute('getRow');				
				if($sbData && !empty($sbData['user_id'])){
					$iUserId =  $sbData['user_id'];
				}
			}

			if(!$iUserId && Phpfox::isModule('opensocialconnect')){
				$sbData  = phpFox::getLib('database')
					->select('*')
					->from(phpFox::getT('socialconnect_agents'))
					->where('identity='."'". $aData['sLoginUID']."' and service_id=1" )
					->execute('getRow');
				if($sbData && !empty($sbData['user_id'])){
					$iUserId = $sbData['user_id'];
				}
			}

			$flag = false;
			// WHEN USER USING FACEBOOK CONNECTION ALREADY 
			// -> identity exist 
			// -> check user exist or not
			if($iUserId){
				//SHOULD CHECK $VIEWER HERE
				$aUser = Phpfox::getService('user')->getUser($iUserId);
				if(isset($aUser['user_id'])){
					$flag = true;
				} else {
					$flag = false;
					//SHOULD SIGNUP HERE
					return array(
							'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_user_found")),
							'error_step' => 'signup',
							'error_code' => 3,
							'result' => 0
					);					
				}
			} else if (isset ($aData['sEmail'])){
				// otherwise, check sEmail if exist 
				// -> if user exist, so update agent for facebook
				// update on socialbridge -> cannot use from social bridge service
				// and social connect -> cannot use from social bridge service
				$aUser = Phpfox::getService('mfox.helper.user')->getUserByEmail($aData['sEmail']);
				if(isset($aUser['user_id'])){
					$flag = true;
					//UPDATE Social Bridge token
					$this->updateAgentForFacebook($aUser, $aData['sLoginUID'], $aData['sAccessToken']);
				} else {
					$flag = false;
					//SHOULD SIGNUP HERE
					return array(
							'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_record_found_with_this_email")),
							'error_step' => 'signup',
							'error_code' => 3,
							'result' => 0
					);					
				}
			}
			if (!$flag)
			{
				return array(
					'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.invalid_credentials")),
					'error_step' => 'signup',
					'error_code' => 8,
					'result' => 0
				);	
			}

			// simple login 
			return $this->simpleLogin($aUser, 'facebook');		
		}
	}

	protected function simpleLogin($user, $source = 'facebook'){
		if (isset($user['status_id']) && $user['status_id'] == 1) // 0 good status; 1 => need to verify
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.user_need_to_verify_first")),
				'error_code' => 4,
				'result' => 0
			);	
		}
		list($bLoginOK, $aUser) = Phpfox::getService('user.auth')->login($user['email'], 'password', true, 'email', true);
		if($bLoginOK == true){
			
			return $this->_loadUserAuthData($aUser['user_id']);

		} else {
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.login_failed")),
				'error_step' => 'login_' . $source,
				'error_code' => 9,
				'result' => 0
			);	
		}
	}

	public function verify_account($aData){
		$iViewerId  =  (int)Phpfox::getUserId();

		if($iViewerId <= 0){
			return array('error_code'=>1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.invalid_account")));
		}

		return $this->_loadUserAuthData($iViewerId);
	}

	protected function updateAgentForFacebook($user, $sLoginUID, $sAccessToken){
		//UPDATE Social Bridge token
		$sService = 'facebook';
		$Provider = Phpfox::getService('socialbridge') -> getProvider($sService);
		$oApi = $Provider -> getApi();

		$oApi -> setAccessToken(NULL);
		$access_token = $sAccessToken;
		$oApi -> setAccessToken($access_token);
		$aProfile = $Provider -> getProfile();
		$Provider -> setTokenData($access_token, $aProfile, $user['user_id']);

		// add agent on social bridge
		$aToken = Phpfox::getService('socialbridge.libs')->getFBAccessToken();
		$identity = '';
		if(isset($aProfile['identity'])){
			$identity = $aProfile['identity']; 
		}
		$aExtra = array(
			'full_name' => $user['full_name'], 
			'user_name' => $user['user_name'], 
			'identity' => $identity , 
			'img_url' => $user['sProfileImage'], 
		);
		Phpfox::getService('socialbridge.agents')->addToken($user['user_id'], $sService, $aToken, $aExtra);

		// update facebook conenct by some think else.

		//WHEN YNSOCIAL-CONNECT EXISTED
		if (Phpfox::isModule('opensocialconnect') && isset($user['user_id'])){
			Phpfox::getService('opensocialconnect')->addAgent($user['user_id'], $aProfile);
		}
		
	}

	protected function loginByTwitter($aData){
		// sLoginUID == identity in profile 
		if (!isset($aData['sLoginUID']))
		{
			return array(
				'error_code' => 10,
				'error_element' => 'login twitter',
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_sloginuid"))
			);
		}
		else 
		{
			// fetch user from agent table.
			$iUserId = 0;

			if(!$iUserId){
				$sbData  = phpFox::getLib('database')
					->select('*')
					->from(phpFox::getT('socialbridge_agents'))
					->where('service_id=\'2\' and identity='."'". $aData['sLoginUID']."'" )
					->order('user_id desc')
					->execute('getRow');

				if($sbData && !empty($sbData['user_id'])){
					$iUserId =  $sbData['user_id'];
				} else {
					$sbData  = phpFox::getLib('database')
							->select('*')
							->from(phpFox::getT('socialbridge_token'))
							->where('service=\'twitter\' and identity='."'". $aData['sLoginUID']."'" )
							->execute('getRow');				
					if($sbData && !empty($sbData['user_id'])){
						$iUserId =  $sbData['user_id'];
					}					
				}
			}

			if(!$iUserId && Phpfox::isModule('opensocialconnect')){
				$sbData  = phpFox::getLib('database')
					->select('*')
					->from(phpFox::getT('socialconnect_agents'))
					->where('identity='."'". $aData['sLoginUID']."' and service_id=2" )
					->execute('getRow');
				if($sbData && !empty($sbData['user_id'])){
					$iUserId = $sbData['user_id'];
				}
			}

			// WHEN USER USING Twitter CONNECTION ALREADY 
			// -> identity exist 
			// -> check user exist or not
			if($iUserId){
				//SHOULD CHECK $VIEWER HERE
				$aUser = Phpfox::getService('user')->getUser($sbData['user_id']);
				if(isset($aUser['user_id'])){
					$flag = true;
				} else {
					$flag = false;
					//SHOULD SIGNUP HERE
					return array(
							'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_user_found")),
							'error_step' => 'signup',
							'error_code' => 3,
							'result' => 0
					);					
				}
			} else if (isset ($sbData['sEmail']) && $sbData['sEmail']){
				// otherwise, check sEmail if exist 
				// -> if user exist, so update agent for Twitter
				// update on socialbridge -> cannot use from social bridge service
				// and social connect -> cannot use from social bridge service
				$aUser = Phpfox::getService('mfox.helper.user')->getUserByEmail($sbData['sEmail']);
				if(isset($aUser['user_id'])){
					$flag = true;
					//UPDATE Social Bridge token
					$sAccessToken = $aData['sAccessToken']?$aData['sAccessToken']:"";
					$sSecretToken = $aData['sSecretToken']?$aData['sSecretToken']:"";

					$this->updateAgentForTwitter($aUser, $aData['sLoginUID'], $sAccessToken, $sSecretToken);
				} else {
					$flag = false;
					//SHOULD SIGNUP HERE
					return array(
							'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_record_found_with_this_email")),
							'error_step' => 'signup',
							'error_code' => 3,
							'result' => 0
					);					
				}
			}

			if (!$flag)
			{
				return array(
					'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.invalid_credentials")),
					'error_step' => 'signup',
					'error_code' => 8,
					'result' => 0
				);	
			}

			// simple login 
			return $this->simpleLogin($aUser, 'twitter');			
		}
	}

	protected function updateAgentForTwitter($user, $sLoginUID, $sAccessToken, $sSecretToken){
		//UPDATE Social Bridge token
		$oauth_token = $sAccessToken;
		$oauth_token_secret = $sSecretToken;
		$sService = 'twitter';
		$Provider = Phpfox::getService('socialbridge') -> getProvider($sService);
		$oTwitter = $Provider -> getApi();

		$oTwitter -> setOAuthToken($oauth_token);
		$oTwitter -> setOAuthTokenSecret($oauth_token_secret);

		$profile = $Provider -> getProfile();

		// session_id = null (browser and devices session_id is diferrent)
		$Provider -> setTokenData(array(
			'oauth_token' => $oauth_token,
			'oauth_token_secret' => $oauth_token_secret,
			'user_id' => $user['user_id'],
			'screen_name' => $profile['user_name'],
		), $profile, $user['user_id']);		
		
		// add agent on social bridge
		list($aToken, $aExtra) = Phpfox::getService('socialbridge.provider.twitter')->getTokenData();
		Phpfox::getService('socialbridge.agents')->addToken($user['user_id'], $sService, $aToken, $aExtra);

		//WHEN YNSOCIAL-CONNECT EXISTED
		if (Phpfox::isModule('opensocialconnect') && isset($user['user_id'])){
			Phpfox::getService('opensocialconnect')->addAgent($user['user_id'], $profile);
		}
	}

	protected function loginByLinkedin($aData)
	{
		return array(
			'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.not_implement")),
			'error_code' => 1
		);
	}


	/**
     * process logout
     * 
	 * Input data:
     * N/A
     * 
     * Output data:
     * + result: int.
     * 
     * @global string $token
     *
	 * @return array
	 */
	function logout()
	{
		global $token;

		if (NULL == $token)
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.token_required")),
				'error_code' => 1,
				'result' => 0
			);
		}

		Phpfox::getService('mfox.token') -> deleteToken($token);

		return array('result' => 1);
	}

	/**
	 * Returns how old is a user based on its birthdate
	 * @param string $sAge
	 * @return int
	 */
	public function age($sAge)
	{
		if (!$sAge)
		{
			return $sAge;
		}
		$iYear = intval(substr($sAge, 4));
		$iMonth = intval(substr($sAge, 0, 2));
		$iDay = intval(substr($sAge, 2, 2));
		$iAge = date('Y') - (int)$iYear;
		$iCurrDate = date('m') * 100 + date('d');
		$iBirthDate = $iMonth * 100 + $iDay;

		if ($iCurrDate < $iBirthDate)
		{
			$iAge--;
		}

		return $iAge;
	}

    /**
     * Get user fields.
     * @param bool $bReturnUserValues
     * @param array $aUser
     * @param string $sPrefix
     * @param int $iUserId
     * @return string
     */
	public function getUserFields($bReturnUserValues = false, &$aUser = null, $sPrefix = null, $iUserId = null)
	{
        /**
         * @var array
         */
		$aFields = array(
			'user_id',
			'profile_page_id',
			'server_id',
			'user_name',
			'full_name',
			'gender',
			'user_image',
			'is_invisible',
			'user_group_id', // Fixes DRQ-307282
			'language_id'
		);

		if (Phpfox::getParam('user.display_user_online_status'))
		{
			$aFields[] = 'last_activity';
		}

		/* Return $aFields but about iUserId */
		if ($iUserId != null)
		{
			$aUser = $this -> database() -> select(implode(',', $aFields)) -> from(Phpfox::getT('user')) -> where('user_id = ' . (int)$iUserId) -> execute('getSlaveRow');

			return $aUser;
		}
		if ($bReturnUserValues)
		{
			$aCache = array();
			foreach ($aFields as $sField)
			{
				if ($sPrefix !== null)
				{
					if ($sField == 'server_id')
					{
						$sField = 'user_' . $sPrefix . $sField;
					}
					else
					{
						$sField = $sPrefix . $sField;
					}
				}
				$aCache[$sField] = ($aUser === null ? Phpfox::getService('mfox.phpfox') -> getUserBy($sField) : $aUser[$sField]);
			}
			return $aCache;
		}
		return $aFields;
	}

    /**
     * Get user object.
     * @param int $iUserId
     * @return bool
     */
	public function getUserObject($iUserId)
	{
		return (isset($this -> _aUser[$iUserId]) ? (object)$this -> _aUser[$iUserId] : false);
	}

    /**
     * Get user by id or name.
     * @staticvar array $aUser
     * @param mix $mName
     * @param bool $bUseId
     * @return array|boolean
     */
	public function get($mName = null, $bUseId = true)
	{
		static $aUser = array();
        /**
         * @var int
         */
		$iPhpfoxUserId = Phpfox::getService('mfox.auth') -> getUserId();

		if (isset($aUser[$mName]))
		{
			return $aUser[$mName];
		}

		if (Phpfox::getService('mfox.auth') -> isUser())
		{
			$this -> database() -> select('ut.item_id AS is_viewed, ') -> leftJoin(Phpfox::getT('user_track'), 'ut', 'ut.item_id = u.user_id AND ut.user_id = ' . $iPhpfoxUserId);
		}

		$this -> database() -> select('ur.rate_id AS has_rated, ') -> leftJoin(Phpfox::getT('user_rating'), 'ur', 'ur.item_id = u.user_id AND ur.user_id = ' . $iPhpfoxUserId);

		if (Phpfox::getUserParam('user.can_feature'))
		{
			$this -> database() -> select('uf.user_id as is_featured, uf.ordering as featured_order, ') -> leftjoin(Phpfox::getT('user_featured'), 'uf', 'uf.user_id = u.user_id');
		}
        /**
         * @var array
         */
		$aRow = $this -> database() -> select('u.*, user_space.*, user_field.*, user_activity.*, ls.user_id AS is_online, ts.style_id AS designer_style_id, ts.folder AS designer_style_folder, t.folder AS designer_theme_folder, t.total_column, ts.l_width, ts.c_width, ts.r_width, t.parent_id AS theme_parent_id, ug.prefix, ug.suffix, ug.icon_ext, ug.title') -> from($this -> _sTable, 'u') -> join(Phpfox::getT('user_group'), 'ug', 'ug.user_group_id = u.user_group_id') -> join(Phpfox::getT('user_space'), 'user_space', 'user_space.user_id = u.user_id') -> join(Phpfox::getT('user_field'), 'user_field', 'user_field.user_id = u.user_id') -> join(Phpfox::getT('user_activity'), 'user_activity', 'user_activity.user_id = u.user_id') -> leftJoin(Phpfox::getT('theme_style'), 'ts', 'ts.style_id = user_field.designer_style_id AND ts.is_active = 1') -> leftJoin(Phpfox::getT('theme'), 't', 't.theme_id = ts.theme_id') -> leftJoin(Phpfox::getT('log_session'), 'ls', 'ls.user_id = u.user_id AND ls.im_hide = 0') -> where(($bUseId ? "u.user_id = " . (int)$mName . "" : "u.user_name = '" . $this -> database() -> escape($mName) . "'")) -> execute('getSlaveRow');

		if (isset($aRow['is_invisible']) && $aRow['is_invisible'])
		{
			$aRow['is_online'] = '0';
		}

		$aUser[$mName] = &$aRow;

		if (!isset($aUser[$mName]['user_name']))
		{
			return false;
		}

		$aUser[$mName]['user_server_id'] = $aUser[$mName]['server_id'];

		$aUser[$mName]['is_friend'] = false;
		$aUser[$mName]['is_friend_of_friend'] = false;

		if (Phpfox::getService('mfox.auth') -> isUser() && Phpfox::isModule('friend') && $iPhpfoxUserId != $aUser[$mName]['user_id'])
		{
			$aUser[$mName]['is_friend'] = Phpfox::getService('mfox.friend') -> isFriend($iPhpfoxUserId, $aUser[$mName]['user_id']);
			$aUser[$mName]['is_friend_of_friend'] = Phpfox::getService('friend') -> isFriendOfFriend($aUser[$mName]['user_id']);

			if (!$aUser[$mName]['is_friend'])
			{
				$aUser[$mName]['is_friend'] = (Phpfox::getService('friend.request') -> isRequested($iPhpfoxUserId, $aUser[$mName]['user_id']) ? 2 : false);
				if (!$aUser[$mName]['is_friend'])
				{
					$aUser[$mName]['is_friend'] = (Phpfox::getService('friend.request') -> isRequested($aUser[$mName]['user_id'], $iPhpfoxUserId) ? 3 : false);
				}
			}
		}

		$this -> _aUser[$aRow['user_id']] = $aUser[$mName];

		return $aUser[$mName];
	}
    /**
     * Using in notification.
     * @param array $aNotification
     * @return array
     */
    public function doUserGetCommentNotificationStatus($aNotification)
    {
        $aRow = $this->database()->select('us.status_id, u.user_id, us.content, u.gender, u.user_name, u.full_name')    
            ->from(Phpfox::getT('user_status'), 'us')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = us.user_id')
            ->where('us.status_id = ' . (int) $aNotification['item_id'])
            ->execute('getSlaveRow');
             
        $aRow['content'] = Phpfox::getLib('parse.bbcode')->removeTagText($aRow['content']); 
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            $sPhrase = Phpfox::getPhrase('user.span_class_drop_data_user_full_name_span_commented_on_gender_status_update_title',array('full_name' => $aNotification['full_name'], 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['content'], Phpfox::getParam('notification.total_notification_title_length'), '...'))); 
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())        
        {
            $sPhrase = Phpfox::getPhrase('user.span_class_drop_data_user_full_name_span_commented_on_your_status_update_title',array('full_name' => $aNotification['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aRow['content'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        else 
        {
            $sPhrase = Phpfox::getPhrase('user.span_class_drop_data_user_full_name_span_commented_on_span_class_drop_data_user_other_full_name_s_span_status_update_title',array('full_name' => $aNotification['full_name'], 'other_full_name' => $aRow['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aRow['content'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }

        $aLink = array(
            'iFeedId' => 0
        );

        $aFeeds = Phpfox::getService('mfox.feed')->getfeed(array('status-id' => $aRow['status_id']), $aRow['user_id']);
        if (count($aFeeds))
        {
            $aLink['iFeedId'] = $aFeeds[0]['feed_id'];
        }

        return array(
            'link' => $aLink,
            'message' => $sPhrase,
            'sModule' => 'user',
            'sMethod' => 'getCommentNotificationStatus'
        );
    }
    /**
     * Using in notification.
     * @param array $aNotification
     * @return array
     */
    public function doUserGetCommentNotificationStatusTag($aNotification)
    {
        return array(
            'message' =>  Phpfox::getPhrase('user.user_name_tagged_you_in_a_comment', array('user_name' => $aNotification['full_name'])),
            'link' => array('iCommentId' => $aNotification['item_id'])
        );
    }
    /**
     * Using in notification.
     * @param array $aNotification
     * @return array
     */
    public function doUserGetNotificationStatus_Like($aNotification)
    {
        /**
         * @var array
         */
        $aRow = $this->database()->select('us.status_id, us.content, us.user_id, u.gender, u.user_name, u.full_name')
                ->from(Phpfox::getT('user_status'), 'us')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = us.user_id')
                ->where('us.status_id = ' . (int) $aNotification['item_id'])
                ->execute('getSlaveRow');
        
        if (!isset($aRow['status_id']))
        {
            return array();
        }
        
        $aRow['content'] = Phpfox::getLib('parse.bbcode')->removeTagText($aRow['content']);
        /**
         * @var string
         */
        $sPhrase = '';
        if ($aNotification['user_id'] == $aRow['user_id'])
        {
            $sPhrase =  Phpfox::getPhrase('user.user_name_liked_gender_own_status_update_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'gender' => Phpfox::getService('user')->gender($aRow['gender'], 1), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['content'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        elseif ($aRow['user_id'] == Phpfox::getUserId())
        {
            $sPhrase =  Phpfox::getPhrase('user.user_name_liked_your_status_update_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'title' => Phpfox::getLib('parse.output')->shorten($aRow['content'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        else
        {
            $sPhrase =  Phpfox::getPhrase('user.user_name_liked_span_class_drop_data_user_full_name_s_span_status_update_title', array('user_name' => Phpfox::getService('notification')->getUsers($aNotification), 'full_name' => $aRow['full_name'], 'title' => Phpfox::getLib('parse.output')->shorten($aRow['content'], Phpfox::getParam('notification.total_notification_title_length'), '...')));
        }
        /**
         * @var array
         */
        $aFeeds = Phpfox::getService('mfox.feed')->getfeed(array('status-id' => $aRow['status_id']), $aRow['user_id']);
        
        if (!isset($aFeeds[0]['feed_id']))
        {
            return array();
        }
        
        return array(
            'link' => (isset($aFeeds[0]['feed_id']) ? array('iFeedId' => $aFeeds[0]['feed_id']) : 0),
            'message' => ($sPhrase),
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }

	public $oMfoxBrowse;
    /**
     * Input data:
     * + sOrderField: string, optional. Ex: iUserId, sFullName, iBirthdaySearch, sGender
     * + sOrderDirection: string, optional. Ex: ASC, DESC.
     * + iPageSize: int, optional. Ex: 10, 20, 30...
     * + iPageNumber: int, optional. Ex: 1, 2, 3 ...
     * 
     * Output data:
     * + iUserId: int.
     * + sFullName: string.
     * + sEmail: string.
     * + sGender: string.
     * + iBirthdaySearch: int.
     * + sBirthdaySearch: string.
     * + iPageNumber: int.
     * 
     * @param array $aData
     * @return array
     */
     
    public function getPendingUsers($aData)
    {
        $aConditions = array('AND u.view_id = 1', 'AND u.profile_page_id = 0');
        
        $sOrderField = isset($aData['sOrderField']) ? $aData['sOrderField'] : 'full_name';
        $sOrderDirection = (isset($aData['sOrderDirection']) && $aData['sOrderDirection'] == 'DESC') ? $aData['sOrderDirection'] : 'ASC';
        
        switch ($sOrderField) {
            case 'iUserId':
                $sSort = 'u.user_id';
                break;
            
            case 'iBirthdaySearch':
                $sSort = 'u.birthday_search';
                break;
            
            case 'sGender':
                $sSort = 'u.gender';
                break;
            
            case 'sEmail':
                $sSort = 'u.email';
                break;
            
            case 'sFullName':
            default:
                $sSort = 'u.full_name';
                break;
        }
        $sSort .= ' ' . $sOrderDirection;
        
        $iPageSize = isset($aData['iPageSize']) ? (int) $aData['iPageSize'] : 10;
        $iPageNumber = isset($aData['iPageNumber']) ? (int) $aData['iPageNumber'] : 0;
        
        define('PHPFOX_IS_ADMIN_SEARCH', true);
        
        $this->oMfoxBrowse = Phpfox::getService('mfox.browse');
        
        list($iCnt, $aUsers) = $this->oMfoxBrowse->conditions($aConditions)
		    ->callback(false)
		    ->sort($sSort)
		    ->page($iPageNumber)
		    ->limit($iPageSize)
		    ->online(false)
		    ->extend(true)
		    ->featured(false)
		    ->pending(false)
		    ->custom(false)
		    ->gender(false)
		    ->get();
        
        $aResult = array();
        
        if ($aUsers)
        {
            $oUserService = Phpfox::getService('user');
            
            foreach ($aUsers as $aUser)
            {
                $aResult[] = array(
                    'iUserId' => $aUser['user_id'],
                    'sFullName' => $aUser['full_name'],
                    'sUserImage' => $this->getImageUrl($aUser, MAX_SIZE_OF_USER_IMAGE),
                    'sEmail' => $aUser['email'],
                    'sGender' => $oUserService->gender($aUser['gender'], $iType = 0),
                    'iBirthdaySearch' => $aUser['birthday_search'],
                    'sBirthdaySearch' => date('F j, o', (int) $aUser['birthday_search']),
                    'iPageNumber' => $iPageNumber
                );
            }
        }
        
        return $aResult;
    }

/**
     * Input data:
     * + iUserId: int, required.
     * 
     * Output data:
     * + result: int.
     * + error_code: int.
     * + error_message: string.
     * 
     * @param array $aData
     * @return array
     */
    public function approve($aData)
    {
        $iUserId = isset($aData['iUserId']) ? (int) $aData['iUserId'] : 0;
        
        if (!Phpfox::isAdmin())
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('user.unable_to_find_this_member'),
                'error_code' => 1
            );
        }
        if ($aUser = Phpfox::getService('user.process')->userPending($iUserId, '1'))
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('user.user_s_successfully_approved'),
                'error_code' => 0,
                'result' => 1
            );
        }
        return array(
            'error_message' =>  Phpfox::getPhrase('user.unable_to_find_this_member'),
            'error_code' => 1
        );        
    }
	
	public function deny($aData)
    {
        $iUserId = isset($aData['iUserId']) ? (int) $aData['iUserId'] : 0;
        
        if (!Phpfox::isAdmin())
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('user.unable_to_find_this_member'),
                'error_code' => 1
            );
        }
        if (($aUser = Phpfox::getService('user.process')->userPending($iUserId, 2)))
        {
            return array(
                'error_message' =>  Phpfox::getPhrase('user.user_successfully_denied'),
                'error_code' => 0,
                'result' => 1
            );
        }
        return array(
            'error_message' =>  Phpfox::getPhrase('user.unable_to_find_this_member'),
            'error_code' => 1
        );
    }

    //MinhTA
    public function signup_term($aData) {
		// $aPage = Phpfox::getService('page')->getPage('terms', true);	
		// $message = '';
		// if (isset($aPage['page_id']))
		// {
		// 	$message = $aPage['text'];
		// }
    	return array(
    		'error_code' => 0,
    		'error_message' => '',
    		'message' => html_entity_decode(Phpfox::getPhrase('mfox.terms_of_service_content'))
    	);
    }

    public function signup_check_email($aData) {
    	$sEmail = $aData['sEmail'];
		if (empty($sEmail) || !isset($sEmail))
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_email_address")),
				'error_element' => 'email',
				'error_step' => '1',
				'error_code' => 1
			);
		}

    	$aReturn = array(
    		'error_code' => 0,
    		'error_message' => '',
    		'error_element' => '',
    		'error_step' => 1
    	);

    	$bResult = $this->__validateEmail($sEmail);
    	if($bResult !== true) { 
    		return $bResult;
    	} 

    	return $aReturn;
    }

    public function signup_check_username($aData) {
    	$sUsername = $aData['sUserName'];

		if (empty($sUsername) || !isset($sUsername))
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_user_name")),
				'error_element' => 'username',
				'error_step' => '1',
				'error_code' => 1
			);
		}

    	$aReturn = array(
    		'error_code' => 0,
    		'error_message' => '',
    		'error_element' => '',
    		'error_step' => 1
    	);


    	$bResult = $this->__validateUser($sUsername);

    	if($bResult !== true) { 
    		return $bResult;
    	} 

    	return $aReturn;
    }

    public function signup_check_username_email($aData) {
    	$sEmail = $aData['sEmail'];
		if (empty($sEmail) || !isset($sEmail))
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_email_address")),
				'error_element' => 'email',
				'error_step' => '1',
				'error_code' => 1
			);
		}

    	$aReturn = array(
    		'error_code' => 0,
    		'error_message' => '',
    		'error_element' => '',
    		'error_step' => 1
    	);

    	$bResult = $this->__validateEmail($sEmail);
    	if($bResult !== true) { 
    		return $bResult;
    	} 

    	$sUsername = $aData['sUserName'];

		if (empty($sUsername) || !isset($sUsername))
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_user_name")),
				'error_element' => 'username',
				'error_step' => '1',
				'error_code' => 1
			);
		}

    	$aReturn = array(
    		'error_code' => 0,
    		'error_message' => '',
    		'error_element' => '',
    		'error_step' => 1
    	);


    	$bResult = $this->__validateUser($sUsername);

    	if($bResult !== true) { 
    		return $bResult;
    	}     	

    	return $aReturn;

    }

    public function signup_timezone($aData) {
    	$aTimeZones = Phpfox::getService('core')->getTimeZones();

    	$aResult = array();
    	foreach ($aTimeZones as $key => $value) {
    		$aItem['sValue'] = $key;
    		$aItem['sPhrase'] = $value;
    		$aResult[] = $aItem;
    	}
    	return $aResult;
    }

    public function signup_account($aData) {

    	$aResult = array(
    		"email" => "",
    		"error_code" => 1,
    		'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.test")),
    		"full_name" => '',
    		"profileimage" => '',
    		"result" => 0,
    		"token" => '',
    		"user_id" => '',
    		"user_name" => ''
    	);

    	if(isset($aData['sFullName']) == false || empty($aData['sFullName']) == true){
			return array( 
				"error_code" => 1,
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_fullname_field")),
				"result" => 0,
			);
    	}

        $aVals = array(
            'full_name' => $aData['sFullName'],
            'email' => $aData['sEmail'],
            'password' => $aData['sPassword'],
        );
        if (isset($aData['sUserImageUrl']) && $aData['sUserImageUrl'] != ''){
        	$aVals['sUserImageUrl'] = $aData['sUserImageUrl'];
        }

        if(isset($aData['iGender']) && empty($aData['iGender']) == false)
        {
        	$aVals['gender'] = $aData['iGender'];
        }

		if(isset($aData['sBirthday']) && empty($aData['sBirthday']) == false) {
			$aParts = explode('-', $aData['sBirthday']);
			if(count($aParts) != 3) {
				return array( 
					"error_code" => 1,
					'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.wrong_birthday_format")),
					"result" => 0,
				);
			}
			$aVals['year'] = $aParts[0];
			$aVals['month'] = $aParts[1];
			$aVals['day'] = $aParts[2];
		};

		$aVals['package_id'] = (isset($aData['iPackageId']) &&  (int)$aData['iPackageId'] > 0 ? (int)$aData['iPackageId'] : '');
        
			if ($aResultSignup = Phpfox::getService('mfox.helper.user')->add($aVals)) {
				$iId = $aResultSignup['iId'];

                $aCoordinate = explode(':', $aData['sCoordinates']);
                $aCropData = array(
                    'x1' => $aCoordinate[0],
                    'y1' => $aCoordinate[1],
                    'w' => $aCoordinate[2],
                    'h' => $aCoordinate[3],
                    'image_height' => $aData['iHeight'],
                    'image_width' => $aData['iWidth'],
                );
                $aUser = Phpfox::getService('mfox.helper.user')->getUserData($iId);
                if(isset($aCoordinate[0]) == false
                	|| isset($aCoordinate[1]) == false
                	|| isset($aCoordinate[2]) == false
                	|| isset($aCoordinate[3]) == false
                	){
                	// not crop image but still set action is successfullly  
                } else {
	                Phpfox::getService('mfox.user')->cropPhoto($aCropData, $aUser['user_image']);
                }

                // Phpfox_Error::log('imagE: ' . serialize($aCropData), '', 11);
				$aLoginResult = $this->login(array(
					'sEmail' => $aVals['email'],
					'sPassword' => $aVals['password'],
				));
				$aResult = array(
					"email"        => $aVals['email'],
					"error_code"   => 0,
					"full_name"    => $aLoginResult['full_name'],
					"profileimage" => $aLoginResult['profileimage'],
					"result"       => 0,
					"token"        => $aLoginResult['token'],
					"user_id"      => $aLoginResult['user_id'],
					"user_name"    => $aLoginResult['user_name'], 
					"iPurchaseId"    => (isset($aResultSignup['iPurchaseId']) ? $aResultSignup['iPurchaseId'] : ''), 
					"iPackageId"    => (isset($aResultSignup['iPackageId']) ? $aResultSignup['iPackageId'] : ''), 
				);

				// save signup by facebook
				if(isset($aData['sLoginUID'])
					&& isset($aData['sAccessToken'])
					&& isset($aData['sLoginBy']) && $aData['sLoginBy'] == 'facebook'
					){
					// update token social for facebook 
					//UPDATE Social Bridge token
					$this->updateAgentForFacebook($aUser
						, $aData['sLoginUID']
						, $aData['sAccessToken']
					);					
				}

				// save signup by twitter
				if(isset($aData['sLoginUID'])
					&& isset($aData['sAccessToken'])
					&& isset($aData['sSecretToken'])
					&& isset($aData['sLoginBy']) && $aData['sLoginBy'] == 'twitter'
					){
					// update token social for twitter 
					//UPDATE Social Bridge token
					$this->updateAgentForTwitter($aUser
						, $aData['sLoginUID']
						, $aData['sAccessToken']
						, $aData['sSecretToken']
					);					
				}

				return $this->_loadUserAuthData($iId, array(
					"iPurchaseId"    => (isset($aResultSignup['iPurchaseId']) ? $aResultSignup['iPurchaseId'] : ''), 

					"iPackageId"    => (isset($aResultSignup['iPackageId']) ? $aResultSignup['iPackageId'] : ''), 
				));
			} else {

				return array( 
					"error_code" => 1,
                    'error_message' => implode('. ', Phpfox_Error::get()),
                    "error_debug"=>"undefined",
					"result" => 0,
				);
			}

        
    }

    public function _loadUserAuthData($iUserId, $extra = array()){

        // user needs to be approved first
        $aFields = $this->_getSimple($iUserId);
        if (isset($aFields['view_id']) && $aFields['view_id'] == 1) {
            return array(
                'error_code' => 5,
                'error_message' => Phpfox::getPhrase('user.your_account_is_pending_approval'),
            );
        }

    	$aUser = Phpfox::getService('user')->get($iUserId);

    	$aToken = Phpfox::getService('mfox.token') -> createToken($aUser);

		$sProfileImage = $this->getImageUrl($aUser, MAX_SIZE_OF_USER_IMAGE);

		// get user id by email
		// fix issue for new version of iphone.
		return array_merge(
			$this->profile(array('iUserId'=> $aUser['user_id'])),
				array_merge(array(
					'error_code' => 0,
					'result' => 1,
					'user_id' => $aUser['user_id'],
					'full_name' => $aUser['full_name'],
					'user_name' => $aUser['user_name'],
					'profileimage' => $sProfileImage,
					'token' => $aToken['token_id'],
					'allowdislike' => Phpfox::getService('mfox.like')->allowdislike(false),
					'timezone_offset'=> date('Z'),
					'email' => $aUser['email']
				), $extra)
			);
    }

    private function _getSimple($iUserId)
    {
    	return $this->database()->select('*')
    		->from($this->_sTable, 'u')
    		->where('user_id = ' . (int) $iUserId)
    		->execute('getRow');
    }

    public function profile($aData) {
    	if(!isset($aData['iUserId'])) {
    		return array(
    			'error_code' => 1,
    			'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_user_id"))
    		);
    	}
    	$iUserId = $aData['iUserId'];
    	// $aUser = $this->get($iUserId);
    	$aUser = Phpfox::getService('mfox.helper.user')->getUserData($iUserId);

        $photoModule = Phpfox::getService('mfox.photo')->isAdvancedModule() ? 'advancedphoto' : 'photo';
        $bCanViewPhoto = Phpfox::getService('user.privacy')->hasAccess($iUserId, $photoModule . '.display_on_profile');
        $sDayOfBirth = '';
        if(isset($aUser['birthdate_display']['Birth Date'])){
        	$sDayOfBirth = $aUser['birthdate_display']['Birth Date'];
        } else if(isset($aUser['birthdate_display']['Age'])){
        	$sDayOfBirth =  Phpfox::getPhrase('profile.age_years_old', array('age' => $aUser['birthdate_display']['Age']));
        }

        $bCanSendMessage = false;
        if(Phpfox::isModule('mail')
        	&& Phpfox::getService('user.privacy')->hasAccess($iUserId, 'mail.send_message')
        	&& Phpfox::getService('mail')->canMessageUser($iUserId)
        	&& Phpfox::getUserId() != $aData['iUserId']){
        	$bCanSendMessage = true;
        }

		$bCanChangeCover =  Phpfox::getUserParam('profile.can_change_cover_photo');
		$bIsBlockedBy =  Phpfox::getService('mfox.helper.user')->isBlocked($iUserId, Phpfox::getUserId());
		$bIsBanned =  Phpfox::getService('user.group.setting')->getGroupParam($aUser['user_group_id'],'core.user_is_banned');
		
		$bCanView = Phpfox::getService('mfox.profile')->canView($iUserId);

    	$aResult = array(
    		"BigUserProfileImg_Url"         => $aUser['sProfileImage'],
            "sCoverPhotoUrl"                => $aUser['sCoverPhotoUrl'],
    		"PhotoImg_Url"                  => $aUser['sProfileImage'],
    		"UserProfileImg_Url"            => $aUser['sProfileImage'],
    		'profileimage'                  => $aUser['sProfileImage'],
    		"iUserId"                       => $iUserId,
    		"isBlocked"                     => Phpfox::getService('mfox.helper.user')->isBlocked($viewing = Phpfox::getUserId(), $iUserId),
    		"isFriend"                      => Phpfox::getService('mfox.helper.user')->isFriend($viewing = Phpfox::getUserId(), $iUserId),
    		"isSentRequest"                 => Phpfox::getService('mfox.helper.user')->isFriendRequestSent($viewing = Phpfox::getUserId(), $iUserId),
    		"isReceivedRequestFromThisUser" => Phpfox::getService('mfox.helper.user')->isReceivedFriendRequest($viewing = Phpfox::getUserId(), $iUserId),
    		"sCity"                         => "not implement",
    		"sFrom"                         => "not implement",
			"sFullName"                     => $aUser['full_name'],
			'fullname'                      => $aUser['full_name'],
			"sGender"                       => $aUser['sGender'],
    		"sLocation"                     => $aUser['sLocation'],
    		"sRelationshipStatus"           => Phpfox::getService('mfox.helper.user')->getRelationShipOfUser($iUserId),
			"sZipPostalCode"                => $aUser['postal_code'] ? $aUser['postal_code'] : '', // avoid NULL
			"bCanPostComment"               => Phpfox::getService('mfox.helper.user')->canPostComment($iUserId),
			"isBlockedBy"                   => $bIsBlockedBy,
			"bCanView"                      => $bCanView,
			"bCanViewWall"                      => Phpfox::getService('user.privacy')->hasAccess($iUserId, 'feed.view_wall'),
    		"isSentRequestBy"               => Phpfox::getService('mfox.helper.user')->isFriendRequestSent($iUserId, Phpfox::getUserId()),
            'iTotalPhotos'                   => $aUser['iTotalPhoto'],
            'iTotalFriends'                  => $aUser['iTotalFriend'],
            'bCanShareOnWall'                  => Phpfox::getService('user.privacy')->hasAccess($iUserId, 'feed.share_on_wall'),
            'bCanViewFriend'                  => Phpfox::getService('user.privacy')->hasAccess($iUserId, 'friend.view_friend'),
            'bCanSendMessage'                  => $bCanSendMessage,
            'bCanViewPhoto'                  => $bCanViewPhoto,
            'bCanViewBasicInfo'                  => Phpfox::getService('user.privacy')->hasAccess($iUserId, 'profile.basic_info'),
            'bCanViewProfileInfo'                  => Phpfox::getService('user.privacy')->hasAccess($iUserId, 'profile.profile_info'),
            'sDayOfBirth'                  => $sDayOfBirth,
            'bIsBanned'=> $bIsBanned,
            'iActivityPoints'=> $aUser['iActivityPoints'],
            'sLastOnline'=> $aUser['sLastOnline'],
            'bIsOnline'=> $aUser['bIsOnline'],
    	);
		
		if( $iUserId != Phpfox::getUserId() && Phpfox::isModule('poke')){
			$aResult['bCanPoke'] = Phpfox::getService('poke')->canSendPoke($iUserId)?1:0;
		}else{
			$aResult['bCanPoke'] =  0;
		}
		
		return $aResult;

    }

	public function poke($aData){
		extract($aData);
		
		if (!Phpfox::getUserParam('poke.can_poke'))
		{
			return array(
				'error_code'=>1,
				'error_message'=> Phpfox::getPhrase('poke.you_are_not_allowed_to_send_pokes'),
			);
		}
		if (Phpfox::getUserParam('poke.can_only_poke_friends') && 
				!Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $iUserId))
		{
			return array(
				'error_code'=>1,
				'error_message'=> Phpfox::getPhrase('poke.you_can_only_poke_your_own_friends'),
			);
		}
		
		if (Phpfox::getService('poke.process')->sendPoke($iUserId))
		{
			return array(
				'error_code'=>0,
				'message'=>  Phpfox::getPhrase('poke.poke_sent'),
				'BasicInfo'=> $this->profile(array('iUserId'=>$iUserId)),
			);
		}
		else
		{
			return array(
				'error_code'=>1,
				'error_message'=>  Phpfox::getPhrase('poke.poke_could_not_be_sent')
			);
		}
	}

	public function update_location($aData){
		$iUserId  =  Phpfox::getUserId();
		
		$this->database()->update(Phpfox::getT('user'), array(
			'latitude'=>$aData['sLat'],
			'longitude'=>$aData['sLong'],
		), 'user_id = '. intval($iUserId));
		
		return array('error_code'=>0);
	}

	public function block($aData) {
		$iUserId = $aData['iUserId'];
		if(Phpfox::getService('mfox.helper.user')->block($iUserId)) {
			return array(
				'error_code' => 0,
				'result' => 1, 
			);
		} else {
			return array(
				'error_code' => 1,
                'result' => 0,
                'message' => implode('. ', Phpfox_Error::get()),
                'error_message' => implode('. ', Phpfox_Error::get())
			);
		}
	}

	public function unblock($aData) {
		$iUserId = $aData['iUserId'];

		$iUserId = $aData['iUserId'];
		if(Phpfox::getService('mfox.helper.user')->unblock($iUserId)) {
			return array(
				'error_code' => 0,
				'result' => 1
			);
		} else {
			return array(
				'error_code' => 1,
				'result' => 0,
                'message' => implode('. ', Phpfox_Error::get()),
                'error_message' => implode('. ', Phpfox_Error::get())
			);
		}
	}

	public function edit_profile($aData) {

		$iUserId = $aData['iUserId'];

		$aVals = array();
		if(isset($aData['iGender']) && empty($aData['iGender']) == false)
		{
			$aVals['gender'] = $aData['iGender'];
		}

		if(isset($aData['sBirthday']) && empty($aData['sBirthday']) == false) {
			$aParts = explode('-', $aData['sBirthday']);
			$aVals['year'] = $aParts[0];
			$aVals['month'] = $aParts[1];
			$aVals['day'] = $aParts[2];
		};
		if(isset($aData['sCountryIso'])) {
			$aVals['country_iso'] = $aData['sCountryIso'];
		};
		if(isset($aData['iCountryChildId'])) {
			$aVals['country_child_id'] = $aData['iCountryChildId'];
		};

		if(isset($aData['sAbout'])) {
			$aVals['about_me'] = $aData['sAbout'];
		};

		if(isset($aData['sZipCode'])) {
			$aVals['postal_code'] = $aData['sZipCode'];
		};

		if(isset($aData['sCity'])) {
			$aVals['city_location'] = $aData['sCity'];
		};

		// not implement at currenly 
		// $iRelation = isset($aData['iRelation']) ? (int) $aData['iRelation'] : 1;
		// $aVals['relation'] = $iRelation;

		if(Phpfox::getService('mfox.helper.user')->editProfile($iUserId, $aVals)) {
			return array(
				'error_code' => 0,
				'error_message' => '',
				'message'=>html_entity_decode(Phpfox::getPhrase("mfox.edited_successfully")),
				'full_name' => $aVals['full_name']
			);
		} else {
			return array(
				'error_code' => 0,
				'error_message' => implode(' ', Phpfox_Error::get()),
				'message'=>html_entity_decode(Phpfox::getPhrase("mfox.edited_failed")),
				'full_name' => 'no meaning, mimick SE returned'
			);
		}
	}

	public function checkin($aData) {
		if (!isset($aData['sLocation']))
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_location_title")),
				'error_code' => 1,
				'result' => 0
			);
		}
		if (!isset($aData['fLatitude']))
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_location_latitude")),
				'error_code' => 1,
				'result' => 0
			);
		}
		if (!isset($aData['fLongitude']))
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.missing_location_longitude")),
				'error_code' => 1,
				'result' => 0
			);
		}


        if(!Phpfox::isUser()){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_do_this_action")),
                'result' => 0
            );
        }

		$aVals = array(
			'user_status' => $aData['sStatus'],
			'location_name' => $aData['sLocation'],
			'parent_user_id' => $aData['iUserId'],
			'latitude' => $aData['fLatitude'],
			'longitude' => $aData['fLongitude'], 
            'privacy' => isset($aData['iPrivacy']) ? $aData['iPrivacy'] : 0,
			'location' => array(
				'latlng' => $aData['fLatitude'] . ',' . $aData['fLongitude'],
				'name' => $aData['sLocation']
				)
		);

		$ret = Phpfox::getService('mfox.helper.user')->updateStatusWithCheckin($aVals);
		if($ret === false){
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.there_are_errors_when_processing_please_try_again")),
                'result' => 0
            );
		}

		return array(
			'error' => 0,
			'message'=>html_entity_decode(Phpfox::getPhrase("mfox.check_in_sucessfully"))
		);
	}

	public function edit_avatar($aData) {
        // var_dump(Phpfox::getUserParam('user.max_upload_size_profile_photo'));
        //     var_dump('limi: ' . Phpfox::getUserParam('user.max_upload_size_profile_photo') === 0 ? null : (Phpfox::getUserParam('user.max_upload_size_profile_photo') / 1024), '', 11);
        // exit;
		$iUserId = $aData['iUserId'];
		if (!isset($_FILES['image']['name']) || empty($_FILES['image']['name']) )
		{
			return array(

				'error_code' =>  1,
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_image_uploaded_please_try_upload_a_new_one")),
			);
		}
		else
		{
			$aImage = Phpfox::getLib('file')->load('image', array('jpg', 'gif', 'png'), (Phpfox::getUserParam('user.max_upload_size_profile_photo') === 0 ? null : (Phpfox::getUserParam('user.max_upload_size_profile_photo') / 1024)));
            // Phpfox_Error::log('limi: ' . Phpfox::getUserParam('user.max_upload_size_profile_photo') === 0 ? null : (Phpfox::getUserParam('user.max_upload_size_profile_photo') / 1024), '', 11);
            // Phpfox_Error::log('imagE: ' . serialize($aData), '', 11);
             // Phpfox_Error::log('imagE: ' . Phpfox::getUserId());
             // Phpfox_Error::log('token: ' . $aData['token']);

			// if ($aImage !== false)
            if (!empty($aImage['name']))
			{
				Phpfox::getService('mfox.helper.user')->uploadImage($iUserId);

                $aCoordinate = explode(':', $aData['sCoordinates']);
                $aUser = Phpfox::getService('mfox.helper.user')->getUserData($iUserId);                    
                if(isset($aCoordinate[0]) == false
                	|| isset($aCoordinate[1]) == false
                	|| isset($aCoordinate[2]) == false
                	|| isset($aCoordinate[3]) == false
                	){
                	// not crop image but still set action is successfullly  
						return array(

							'error_code' =>  0,
                            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.edit_sucessfully")),
                            'message'=>html_entity_decode(Phpfox::getPhrase("mfox.edit_sucessfully")),
                            'user_image' => $aUser['sProfileImage']
						);                    	
                } else {
                    $aCropData = array(
                        'x1' => $aCoordinate[0],
                        'y1' => $aCoordinate[1],
                        'w' => $aCoordinate[2],
                        'h' => $aCoordinate[3],
                        'image_height' => $aData['iHeight'],
                        'image_width' => $aData['iWidth'],
                    );


                // Phpfox_Error::log('imagE: ' . serialize($aCropData), '', 11);
                    Phpfox::getService('mfox.user')->cropPhoto($aCropData, $aUser['user_image']);
					if (!Phpfox_Error::isPassed())
					{
						return array(

							'error_code' =>  1,
							'error_message' => implode(' ', Phpfox_Error::get())
						);
					} else {
						return array(

							'error_code' =>  0,
                            'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.edit_sucessfully")),
                            'message'=>html_entity_decode(Phpfox::getPhrase("mfox.edit_sucessfully")),
                            'user_image' => $aUser['sProfileImage']
						);

					}
                }
			} else {
				return array(
					'error_code' =>  1,
                    'error_message' => implode('. ', Phpfox_Error::get())
				);

			}
		}
	}

    public function cropPhoto($aVals, $sUserImage)
    {
        if (isset($aVals['skip_croping']))
        {
            return true;
        }
        
        Phpfox::getLib('image')->createThumbnail(Phpfox::getParam('core.dir_user') . sprintf($sUserImage, ''), Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '') . '_temp', $aVals['image_width'], $aVals['image_height'], false);		
        
        if (empty($aVals['w']))
        {			
            return Phpfox_Error::set( Phpfox::getPhrase('photo.select_an_area_on_your_photo_to_crop'));
        }
        
        Phpfox::getLib('image')->cropImage(
            Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '') . '_temp',
            Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '_50_square'),			
            $aVals['w'],
            $aVals['h'],
            $aVals['x1'],
            $aVals['y1'],
            75
        );
        
        /*Phpfox::getLib('image')->cropImage(
            Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '') . '_temp',
            Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '_75'),			
            $aVals['w'],
            $aVals['h'],
            $aVals['x1'],
            $aVals['y1'],
            75
        );		*/
        
        foreach(Phpfox::getParam('user.user_pic_sizes') as $iSize)
        {
            if ($iSize >= 75)
            {
                // continue;
            }
            
            if (Phpfox::getParam('core.keep_non_square_images'))
            {
                Phpfox::getLib('image')->createThumbnail(
                    Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '_'. $iSize.'_square'), 
                    Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '_' . $iSize), 
                    $iSize, 
                    $iSize
                );
            }
            Phpfox::getLib('image')->createThumbnail(
                Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '_'. $iSize.'_square'), 
                Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '_' . $iSize . '_square'), 
                $iSize, 
                $iSize, 
                false
            );
            
            if (defined('PHPFOX_IS_HOSTED_SCRIPT'))
            {
                unlink(Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '_' . $iSize));	
                unlink(Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '_' . $iSize . '_square'));
            }
        }
        
        unlink(Phpfox::getParam('core.dir_user') . sprintf($sUserImage, '') . '_temp');
        
        return true;
    }

	public function edit_cover($aData) {
		$iUserId = $aData['iUserId'];
		if (!isset($_FILES['image']['name']) || empty($_FILES['image']['name']) )
		{
			return array(

				'error_code' =>  1,
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_image_uploaded_please_try_upload_a_new_one")),
			);
		}
		else
		{
            $aResult = Phpfox::getService('mfox.photo')->upload(array(
                'isPostStatus' => 1,
                'is_cover_photo' => 1
            ));

			if ($aResult['error_code'] != 1)
			{
                return Phpfox::getService('mfox.photo')->__setcover(array(
                    'iPhotoId' => $aResult['iPhotoId']
                ));
			}
            else
            {
				return array(
					'error_code' =>  1,
					'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.no_image_uploaded_please_try_upload_a_new_one")),
				);
			}
		}
	}

	public function __validateEmail($sEmail)
	{
		// Split email address up and disallow '..'
		if ((strpos($sEmail, '..') !== false) || (!filter_var($sEmail, FILTER_VALIDATE_EMAIL)))
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.email_address_invalid")),
				'error_element' => 'email',
				'error_step' => '1',
				'error_code' => 2
			);
		}

		$iCnt = $this->database()->select('COUNT(*)')
			->from($this->_sTable)
			->where("email = '" . $this->database()->escape($sEmail) . "'")
			->execute('getField');
		
		if ($iCnt)
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.someone_has_already_registered_this_email_address_please_use_another_one")),
				'error_element' => 'email',
				'error_step' => '1',
				'error_code' => 3
			);
		}
		
		if (!Phpfox::getService('ban')->check('email', $sEmail))
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.this_email_address_is_not_available_please_use_another_one")),
				'error_element' => 'email',
				'error_step' => '1',
				'error_code' => 4
			);
		}		
		
		return true;		
	}

	public function __validateUser($sUser)
	{		
		$ret = Phpfox::getLib('parse.input')->allowTitle($sUser,  Phpfox::getPhrase('user.user_name_is_already_in_use'));
		if($ret !== true){
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.someone_has_already_registered_this_user_name_please_use_another_one")),
				'error_element' => 'username',
				'error_step' => '1',
				'error_code' => 1
			);
		}
		
		if (!Phpfox::getService('ban')->check('username', $sUser))
		{
			return array(
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.this_user_name_is_not_available_please_use_another_one")),
				'error_element' => 'username',
				'error_step' => '1',
				'error_code' => 2
			);
		}

		if (!Phpfox::getParam('user.profile_use_id') && !Phpfox::getParam('user.disable_username_on_sign_up'))
		{
			$sUser = str_replace(' ', '_', $sUser);
			$sUser = Phpfox::getLib('parse.input')->clean($sUser);
			/* Check if there is a page with the same url as the user name*/
			$aPages = Phpfox::getService('page')->get();
			foreach ($aPages as $aPage)
			{
				if ($aPage['title_url'] == strtolower($sUser))
				{
					return array(
							'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.user_name_is_not_valid")),
							'error_element' => 'username',
							'error_step' => '1',
							'error_code' => 1
					);
				}
			}
		}
		return true;
	}

	public function checksocialservice($aData = array()){
		if (Phpfox::isModule('socialbridge')){
			$aServices = array('facebook', 'twitter', 'linkedin');
			$result = array();
			foreach($aServices as $srv){
				if (Phpfox::getService('socialbridge')->hasProvider($srv)){
					$result[] = array($srv => 1);
				} else {
					$result[] = array($srv => 0);
				}
			}
			return array(
					'result' => $result,
					'error_message' => '',
					'error_code' => 0
			);
		} else {
			return array(
					'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.server_needs_to_be_installed_socialbridge_module_first")),
					'error_code' => 1
			);
		}					
	}
	
	public function genderOptions($aData = array()){
		$return = array();
		foreach (Phpfox::getService('core')->getGenders(true) as $iKey => $sGender){
			$return[] =  array(
				'id'=> intval($iKey),
				'title'=> Phpfox::getPhrase($sGender),
			);
		}
		
		return $return; 
	}
	
	public function form_signup($aData = array()){
		return array(
			'gender_options'=>$this->genderOptions(),
		);	
	}
	
	public function formeditprofile($aData){
		$iUserId = (isset($aData['iUserId']) && (int)$aData['iUserId'] > 0) ? (int)$aData['iUserId'] : 0;
        if ($iUserId < 1)
        {
            return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.user_id_is_not_valid")));
        }

		$result = array();

		// get user's info
		$aUser = Phpfox::getService('mfox.helper.user')->getUserData($iUserId);
		$info = array(
			'sProfileImage'     => $aUser['sProfileImage'],
			'sProfileImageBig' => $aUser['sProfileImage'],
			'sDisplayName'      => $aUser['full_name'],
            'sGender'            => isset($aUser['sGender']) ? $aUser['sGender'] : '',
            'sCoverImageBig'   => $aUser['sCoverPhotoUrl'],
            'sDateOfBirth'     => $aUser['sDayOfBirthDotFormat'],
            'sLocation'          => $aUser['sLocation'],
            'sCountryISO'       => $aUser['country_iso'],
            'iCountryChildId'       => $aUser['country_child_id'],
            'sZipCode'          => $aUser['postal_code'] ? $aUser['postal_code'] : '', // avoid NULL
            'sRelationship'      => Phpfox::getService('mfox.helper.user')->getRelationShipOfUser($iUserId),
            'sFirstName'        => $aUser['first_name'],
            'sLastName'         => $aUser['last_name'],
            'sFullName'         => $aUser['full_name'],
            'sDateOfBirthYMD' => $aUser['sDayOfBirth'],
            'sCity'              => $aUser['city_location'] ? $aUser['city_location'] : '', 
            'sAboutMe' => Phpfox::getService('mfox.helper.user')->getCustomValue('cf_about_me', $iUserId), 
		);
		$result['info'] = $info;

		// get location
		$result['aLocations'] = $this->database()->select('c.country_iso, c.name')
				->from(Phpfox::getT('country'), 'c')				
				->order('c.ordering ASC, c.name ASC')
				->execute('getRows');			

		foreach ($result['aLocations'] as $key => $value){
			$result['aLocations'][$key]['name'] = html_entity_decode($value['name']);
		}

		// not implement at currently 
		// // get relationships
		// $aRelationships = Phpfox::getService('custom')->getRelations();
		// // check can have relationship
		// $result['bCanHaveRelationship'] = Phpfox::getUserParam('custom.can_have_relationship') ;
		// $result['aRelationships'] = $aRelationships;

		// check existing about me
		$result['bActiveAboutMe'] = false;
		$aboutMe = Phpfox::getService('mfox.helper.user')->getCustomFieldByName('about_me');
		if(isset($aboutMe) && $aboutMe['is_active']){
			$result['bActiveAboutMe'] = true;
		}
		$result['iGenderId'] =  $aUser['gender'];
		$result['gender_options'] =  $this->genderOptions();

        $result['perms'] = array(
            'user.can_edit_dob' => Phpfox::getUserParam('user.can_edit_dob'),
            'user.can_edit_gender_setting' => Phpfox::getUserParam('user.can_edit_gender_setting'),
        );

		return $result;
	}	

	public function getsubscriptionpackages($aData){    
		return array(
			'aPackage' => Phpfox::getService('mfox.subscribe')->getSubscriptionPackages($aData), 
			'aPerm' => Phpfox::getService('mfox.subscribe')->getPermission($aData), 
		);
	}

	public function getusergroupinfo($aData){
		if(Phpfox::isUser()){
			$iUserGroupId = Phpfox::getService('user.group')->getUserGroupId();
			$aGroup = Phpfox::getService('user.group')->getGroup($iUserGroupId);
			return array('sTitle' => $aGroup['title']);
		} else {
			return array('result' => 0, 'error_code' => 1, 'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.please_login_first")));
		}
	}

	public function getSubscribeIdByUserId($iUserId = null){
		if($iUserId === null){
			$iUserId = Phpfox::getUserId();
		}

		if((int)$iUserId <= 0){
			return false;
		}

        $aRows = $this->database()->select('u.user_id, uf.subscribe_id')
             ->from(Phpfox::getT('user'), 'u')
             ->join(Phpfox::getT('user_count'), 'uc', 'uc.user_id = u.user_id')
             ->join(Phpfox::getT('user_field'), 'uf', 'uf.user_id = u.user_id')
             ->where('u.user_id = ' . $iUserId)
             ->execute('getRow');

         if(Phpfox::getParam('subscribe.subscribe_is_required_on_sign_up') && (int)$aRows['subscribe_id'] > 0){
         	Phpfox::getService('subscribe.purchase')->setRedirectId($aRows['subscribe_id']);
         	return $aRows['subscribe_id'];
         }

         return false;
	}

	// ####################################################//
	// SINCE 3.08 

	public function _detail($iUserId){
		$aUser = Phpfox::getService('mfox.helper.user')->getUserData($iUserId);

		return $aUser;
	}

	public function detail($aData){

		extract($aData);

		$iUserId  = isset($iUserId)?intval($iUserId):0;

		// try to get current viewer
		if(0 == $iUserId){
			$iUserId =  Phpfox::getUserId();
		}

		if(0 == $iUserId){
			return array(
				'error_code'=>1,
				'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.user_not_found")),
				'user_id'=>$iUserId,
			);
		}
		
		return $this->_detail($iUserId);
	}

    /**
     * Get user image url
     * @param array $aUser
     * @param string $sSuffix
     * @return string
     */
    public function getImageUrl($aUser = array(), $sSuffix = '')
    {
        if (empty($aUser['user_image']))
        {
            return Phpfox::getParam('core.url_module') . 'mfox/static/image/noimage/' . sprintf('profile.png');
        }

        if (substr($aUser['user_image'], 0, 1) == '{') {
            $object = json_decode($aUser['user_image'], true);
            $dataSrc = array_values($object)[0];
            $dataObject = array_keys($object)[0];
            if ($dataObject == 'fb') {
                return 'https://graph.facebook.com/' . $dataSrc . '/picture?type=square&width=200&height=200';
            } else {
                return '';
            }
        }

        return Phpfox::getLib('image.helper')->display(array(
            'user' => $aUser,
            'suffix' => $sSuffix,
            'return_url' => true
        ));
    }
    public function points ($aUser = array())
    {
    	if(!$aUser['iUserId'])
    	{
    		$iUserId = Phpfox::getUserId();
    	}
    	$aUser = Phpfox::getService('user')->get(Phpfox::getUserId(), true);
        $aModules = Phpfox::massCallback('getDashboardActivity');
        $aActivites = [Phpfox::getPhrase('core.total_items') => $aUser['activity_total'], Phpfox::getPhrase('core.activity_points') => $aUser['activity_points'],];
        foreach ($aModules as $aModule) {
            foreach ($aModule as $sPhrase => $sLink) {
                $aActivites[$sPhrase] = $sLink;
            }
        }
        return $aActivites;
    }
}
