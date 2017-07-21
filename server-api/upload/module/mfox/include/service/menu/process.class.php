<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');

/**
 * @version         4.04
 * @package         mfox
 *
 * @author          YouNetCo
 * @copyright       YouNetCo
 */
class Mfox_Service_Menu_Process extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('mfox_leftnavi');
    }

    /**
     * @param  $aVals
     * @return mixed
     */
    public function add($aVals)
    {
        if (empty($aVals['label'])) {
            return Phpfox_Error::set(Phpfox::getPhrase('mfox.label_is_required'));
        }

        $oParseInput = Phpfox::getLib('parse.input');
        $aInsert = array(
            'label' => $oParseInput->clean($aVals['label'], 255),
            'is_group' => (!empty($aVals['is_group']) ? (int) $aVals['is_group'] : 0),
            'parent_id' => (!empty($aVals['parent_id']) ? (int) $aVals['parent_id'] : 0),
            'is_enabled' => 1,
            'sort_order' => $this->_getMaxOrder() + 1,
        );

        $iId = $this->database()->insert($this->_sTable, $aInsert);

        return $iId;
    }

    /**
     * @return mixed
     */
    private function _getMaxOrder()
    {
        return $this->database()->select('MAX(sort_order)')
            ->from($this->_sTable)
            ->execute('getField');
    }

    /**
     * @param $iId
     * @param $aVals
     */
    public function update($iId, $aVals)
    {
        $aUpdate = array(
            'label' => Phpfox::getLib('parse.input')->clean($aVals['label'], 255),
            'parent_id' => (!empty($aVals['parent_id']) ? (int) $aVals['parent_id'] : 0),
            'is_enabled' => (int) $aVals['is_enabled'],
        );

        $this->database()->update($this->_sTable, $aUpdate, 'id = ' . (int) $iId);

        return true;
    }

    /**
     * @param $iId
     */
    public function delete($iId)
    {
        $aRow = Phpfox::getService('mfox.menu')->get($iId);

        if (!$aRow) {
            return Phpfox_Error::set(Phpfox::getPhrase('mfox.menu_not_found'));
        }

        if (!$aRow['is_group']) {
            return Phpfox_Error::set(Phpfox::getPhrase('mfox.can_not_delete_this_menu'));
        }

        $this->database()->update($this->_sTable, array('parent_id' => 0), 'parent_id = ' . (int) $iId);

        $this->database()->delete($this->_sTable, 'id = ' . (int) $iId);

        return true;
    }

    /**
     * @param $aVals
     */
    public function updateOrder($aVals)
    {
        foreach ($aVals as $iId => $iOrder) {
            $this->database()->update($this->_sTable, array('sort_order' => $iOrder), 'id = ' . (int) $iId);
        }

        return true;
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod    is the name of the method
     * @param array  $aArguments is the array of arguments of being passed
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('mfox.service_menu_process__call')) {
            return eval($sPlugin);
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}
