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
class Mfox_Service_Token extends Phpfox_Service
{
    /**
     * @var int Token length.
     */
    CONST TOKEN_LEN = 24;
    /**
     * Constructor.
     */
    function __construct()
    {
        $this->_sTable = Phpfox::getT('mfox_token');
    }
	/**
     * Get one.
     * @return array|null
     */
     
	function getOne()
	{
		return $this->database()
            ->select('*')
            ->from($this->_sTable)
            ->where('1')
            ->execute('getSlaveRow');
	}

    /**
     * Add token by $iUserId
     *
     * @see Mfox_Service_Mfox
     *
     * @global string $token
     * @param array $aUser
     * @return array
     */
    function createToken($aUser)
    {
        global $token;
        /**
         * @var int
         */
        $iUserId = intval($aUser['user_id']);

        if ($token)
        {
            // delete old token
            $sCond = "token_id='{$token}'";
            $this->database()->delete($this->_sTable, $sCond);
        }

        //refine token
        $token = Phpfox::getService('mfox')->getRandomString(self::TOKEN_LEN);

        // insert new token
        $aInsert = array(
            'token_id' => $token,
            'user_id' => $iUserId,
            'created_at' => PHPFOX_TIME,
        );

        $this->database()->insert($this->_sTable, $aInsert);

        return $aInsert;
    }

    /**
     * delete token by token id
     * @param string $sToken
     */
    function deleteToken($sToken)
    {
        $this->database()->delete($this->_sTable, "token_id='{$sToken}'");

        $this->database()->delete(Phpfox::getT('mfox_device'), "token='{$sToken}'");
    }

    /**
     * @see Phpfox_Database_Driver_Mysql
     * Get token information.
     * 
     * @param string $sToken
     * @return array Token information.
     */
    function getToken($sToken)
    {
        $sCond = "token_id='{$sToken}'";
        return $this->database()
                ->select('*')
                ->from($this->_sTable)
                ->where($sCond)
                ->execute('getSlaveRow');
    }

    /**
     * If token is not valid, return an error message and error code.
     *
     * @param string $sToken
     * @return array
     */
    public function isValid($sToken)
    {
        /**
         * @var array
         */
        $aToken = $this->getToken($sToken);
return array();
        if (!$aToken)
        {
            return array(
                'error_code' => 1,
                'error_message'=>html_entity_decode(Phpfox::getPhrase("mfox.token_is_not_valid"))
            );
        }

        return array();
    }

}
