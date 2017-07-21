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
class Mfox_Service_Menu_Menu extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('mfox_leftnavi');
    }

    /**
     * @return mixed
     */
    public function getGroupOptions()
    {
        $aRows = $this->database()->select('*')
            ->from($this->_sTable)
            ->where('is_group = 1')
            ->order('sort_order ASC')
            ->execute('getRows');

        if (empty($aRows)) {
            return '';
        }

        $sOutput = '';
        foreach ($aRows as $aRow) {
            $sOutput .= '<option value="' . $aRow['id'] . '" id="js_mp_category_item_' . $aRow['id'] . '">' . Phpfox_Locale::instance()->convert($aRow['label']) . '</option>' . "\n";
        }

        return $sOutput;
    }

    /**
     * @param  $iId
     * @return mixed
     */
    public function get($iId)
    {
        return $this->database()->select('*')
            ->from($this->_sTable)
            ->where('id = ' . (int) $iId)
            ->execute('getRow');
    }

    /**
     * @return mixed
     */
    public function getForManage()
    {
        $aMenus = $this->getAll(false);

        if (empty($aMenus)) {
            return '';
        }

        $sOutput = '<ul>';

        foreach ($aMenus as $aMenu) {
            $sOutput .= '<li' . $this->_getTplClass($aMenu) . '><img src="' . Phpfox_Template::instance()->getStyle('image', 'misc/draggable.png') . '" alt="" /> <input type="hidden" name="order[' . $aMenu['id'] . ']" value="' . $aMenu['sort_order'] . '" class="js_mp_order" /><a href="#?id=' . $aMenu['id'] . '" class="js_drop_down">' . $aMenu['label'] . '</a>';

            if (!empty($aMenu['children'])) {
                $sOutput .= $this->_getChildrenTpl($aMenu['children']);
            }

            $sOutput .= '</li>' . "\n";
        }

        $sOutput .= '</ul>';

        return $sOutput;
    }

    /**
     * @param  $aChildren
     * @return mixed
     */
    private function _getChildrenTpl($aChildren)
    {
        $sOutput = '<ul>';

        foreach ($aChildren as $aChild) {
            $sOutput .= '<li' . $this->_getTplClass($aChild) . '><img src="' . Phpfox_Template::instance()->getStyle('image', 'misc/draggable.png') . '" alt="" /> <input type="hidden" name="order[' . $aChild['id'] . ']" value="' . $aChild['sort_order'] . '" class="js_mp_order" /><a href="#?id=' . $aChild['id'] . '" class="js_drop_down">' . $aChild['label'] . '</a></li>' . "\n";
        }

        $sOutput .= '</ul>';

        return $sOutput;
    }

    private function _getTplClass($aMenu)
    {
        $aClass = array();
        if ($aMenu['is_group']) {
            $aClass[] = 'menu-group';
        }
        if (!$aMenu['is_enabled']) {
            $aClass[] = 'disabled';
        }
        
        $sClass = '';
        if (!empty($aClass)) {
            $sClass = ' class="' . implode(' ', $aClass) . '"';
        }
        
        return $sClass;
    }

    /**
     * @return mixed
     */
    public function getAll($bIsActive = true)
    {
        if ($bIsActive) {
            $this->database()->where('is_enabled = 1');
        }

        $aRows = $this->database()->select('*')
            ->from($this->_sTable)
            ->order('sort_order ASC')
            ->execute('getRows');

        if (empty($aRows)) {
            return array();
        }

        $aMenus = array();
        foreach ($aRows as $key => $aRow) {
            $aRows[$key]['label'] = Phpfox_Locale::instance()->convert($aRow['label']);

            if (!$aRow['parent_id']) {
                $aMenus[] = $aRows[$key];
                unset($aRows[$key]);
            }
        }

        foreach ($aMenus as $key => $aMenu) {
            if ($aMenu['is_group'] == 1) {
                $this->_getGroupChildren($aMenus[$key], $aRows);
            }
        }

        return $aMenus;
    }

    /**
     * @param $aGroup
     * @param $aRows
     */
    private function _getGroupChildren(&$aGroup, &$aRows)
    {
        $aGroup['children'] = array();
        foreach ($aRows as $key => $aRow) {
            if ($aRow['parent_id'] == $aGroup['id']) {
                $aGroup['children'][] = $aRow;
                unset($aRows[$key]);
            }
        }
    }
}
