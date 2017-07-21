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
class Mfox_Service_Profile extends Phpfox_Service
{
    public function info($aData)
    {
			$iUserId = $aData['iUserId'];
			$aUser = Phpfox::getService('mfox.helper.user')->getUserData($iUserId);
            // var_dump($aUser);
			return array(
				'BasicInfo' => array(
					'Profile_Image'     => $aUser['sProfileImage'],
					'Profile_Image_Big' => $aUser['sProfileImage'],
					'Display_Name'      => $aUser['full_name'],
                    'Gender'            => isset($aUser['sGender']) ? $aUser['sGender'] : '',
                    'Cover_Image_Big'   => $aUser['sCoverPhotoUrl'],
                    'Date_Of_Birth'     => $aUser['sDayOfBirthDotFormat'],
                    'Location'          => $aUser['sLocation'],
                    'Country_ISO'       => $aUser['country_iso'],
                    'Zip_Code'          => $aUser['postal_code'] ? $aUser['postal_code'] : '', // avoid NULL
                    'Relationship'      => Phpfox::getService('mfox.helper.user')->getRelationShipOfUser($iUserId),
                    'First_Name'        => $aUser['first_name'],
                    'Last_Name'         => $aUser['last_name'],
                    'Full_Name'         => $aUser['full_name'],
                    'Date_Of_Birth_YMD' => $aUser['sDayOfBirth'],
                    'City'              => $aUser['city_location'] ? $aUser['city_location'] : ''
				),
				'About_Me' => array(
					'About_Me' => Phpfox::getService('mfox.helper.user')->getCustomValue('cf_about_me', $iUserId)
				),
				'Details' => array()
			);
    }
    /**
     * Profile info.
     * 
     * Input data:
     *Y-m-d + iUserId: int, required.
     * 
     * @param array $aData
     * @return array
     */
    public function info_temp($aData)
    {
        extract($aData, EXTR_SKIP);
        /**
         * @var int
         */
        $iUserId = isset($iUserId) ? (int) $iUserId : Phpfox::getUserId();
        /**
         * @var int
         */
        $iPhpfoxUserId = Phpfox::getUserId();
        /**
         * @var int
         */
        $iUserGroupId = Phpfox::getUserBy('user_group_id');
        if (!Phpfox::getService('user.privacy')->hasAccess($iUserId, 'profile.basic_info'))
		{
			return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.you_do_not_have_permission_to_view_this_profile"))
            );
		}
        if (($aUser = Phpfox::getService('user')->getUser($iUserId, 'u.user_id, u.user_name, u.full_name')) && isset($aUser['user_id']))
        {
            $iPhpfoxUserId = $aUser['user_id'];
            $iUserGroupId = $aUser['user_group_id'];
        }
        else
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.profile_is_not_valid"))
            );
        }
        /**
         * @var array
         */
        $aRelations = Phpfox::getService('custom.relation')->getAll();
        /**
         * @var array
         */
        $aCustomGroups = Phpfox::getService('custom.group')->getGroups('user_profile', $iUserGroupId);
        /**
         * @var array
         */
        $aCustomFields = Phpfox::getService('custom')->getForEdit(array('user_main', 'user_panel', 'profile_panel'), $iPhpfoxUserId, $iUserGroupId, false, $iPhpfoxUserId);
        /**
         * @var array
         */
        $aGroupCache = array();
        foreach ($aCustomFields as $aFields)
        {
            $aGroupCache[$aFields['group_id']] = true;
        }
        foreach ($aCustomGroups as $iKey => $aCustomGroup)
        {
            if (!isset($aGroupCache[$aCustomGroup['group_id']]))
            {
                unset($aCustomGroups[$iKey]);
            }
        }
        /**
         * @var array
         */
        $aRebuildKeys = $aCustomGroups;
        /**
         * @var array
         */
        $aCustomGroups = array();
        $iCnt = 0;
        foreach ($aRebuildKeys as $aCustomGroup)
        {
            $aCustomGroups[$iCnt] = $aCustomGroup;
            $iCnt++;
        }
        /**
         * @var array
         */
        $aTimeZones = Phpfox::getService('core')->getTimeZones();
        /**
         * @var array
         */
        $aFullProfileInfo = Phpfox::getService('user')->get($iPhpfoxUserId, true);

        /* we could put this part inside get but I fear its being wrongly used */
        $aRelation = Phpfox::getService('custom.relation')->getLatestForUser($iPhpfoxUserId, null, true);
        $sRelation = '';
        if (isset($aRelation['status_id']))
        {
            $aFullProfileInfo = array_merge($aFullProfileInfo, $aRelation);
            $sRelation =  Phpfox::getPhrase($aRelation['phrase_var_name']);
        }
        $aFullProfileInfo['month'] = substr($aFullProfileInfo['birthday'], 0, 2);
        $aFullProfileInfo['day'] = substr($aFullProfileInfo['birthday'], 2, 2);
        $aFullProfileInfo['year'] = substr($aFullProfileInfo['birthday'], 4);
        /**
         * @var array
         */
        $aProfileInfo = array();
        $sUserImage = Phpfox::getService('mfox.user')->getImageUrl($aFullProfileInfo, MAX_SIZE_OF_USER_IMAGE);
        $aProfileInfo['BasicInfo'] = array(
            'Location' => $aFullProfileInfo['country_iso'],
            'City' => $aFullProfileInfo['city_location'],
            'Zip_Postal_Code' => $aFullProfileInfo['postal_code'],
            'Date_Of_Birth' => date('Y-m-d', (int) $aFullProfileInfo['birthday_search']),
            'Gender' => ($aFullProfileInfo['gender'] == 1 ? 'Male' : 'Female'),
            'Relationship_Status' => $sRelation,
            'Forum_Signature' => $aFullProfileInfo['signature_clean'],
            'Profile_Image' => $sUserImage,
            'Display_Name' => $aFullProfileInfo['full_name']
        );
        foreach($aCustomGroups as $aGroup)
        {
            $aTemp = array();
            foreach($aCustomFields as $aField)
            {
                if ($aField['group_id'] == $aGroup['group_id'])
                {
                    if ($aField['var_type'] == 'select')
                    {
                        $aTemp[$this->changeTextForField($aField['field_name'])] = $aField['options'][$aField['customValue']]['value'];
                    }
                    else
                    {
                        $aTemp[$this->changeTextForField($aField['field_name'])] = $aField['value'];
                    }
                }
            }
            $aProfileInfo[$this->changeTextForGroup( Phpfox::getPhrase($aGroup['phrase_var_name']))] = $aTemp;
        }
        return $aProfileInfo;
    }

    /**
     * Change the text for group.
     * @param string $sText
     * @return string
     */
    public function changeTextForGroup($sText)
    {
        /**
         * @var array
         */
        $aWord = explode(' ', $sText);
        $aText = array();
        foreach($aWord as $sWord)
        {
            $aText[] = ucfirst($sWord);
        }
        return implode('_', $aText);
    }
    /**
     * Change the text for field.
     * @param string $sText
     * @return string
     */
    public function changeTextForField($sText)
    {
        /**
         * @var array
         */
        $aWord = explode('_', $sText);
        $aText = array();
        foreach($aWord as $sWord)
        {
            $aText[] = ucfirst($sWord);
        }
        return implode('_', $aText);
    }

    /**
     * 
     */
    public function getmenu()
    {

    }

    public function detail($aData){
        
        $info = $this->info($aData);
        
        $profile = Phpfox::getService('mfox.user')->profile($aData);
        
        $info['BasicInfo'] = $profile;
		
        
        return $info;
    }

    public function canView($iUserId)
    {
        $aRow = Phpfox::getService('user')->get($iUserId);
        if (!isset($aRow['user_id'])) {
            return false;
        }
        
        if (Phpfox::getService('user.block')->isBlocked($aRow['user_id'], Phpfox::getUserId()) && !Phpfox::getUserParam('user.can_override_user_privacy'))
        {
            return false;
        }
        
        if ( ((Phpfox::isModule('friend') && Phpfox::getParam('friend.friends_only_profile')) )
            && empty($aRow['is_friend'])
            && !Phpfox::getUserParam('user.can_override_user_privacy')
            && $aRow['user_id'] != Phpfox::getUserId()
        )
        {
            return false;
        }
        
        if (!Phpfox::getService('user.privacy')->hasAccess($aRow['user_id'], 'profile.view_profile'))
        {
            return false;
        }

        return true;
    }
}
