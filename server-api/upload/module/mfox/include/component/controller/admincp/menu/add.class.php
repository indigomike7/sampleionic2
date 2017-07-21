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
class Mfox_Component_Controller_Admincp_Menu_Add extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        $bIsEdit = false;
        if ($iEditId = $this->request()->getInt('id')) {
            if ($aMenu = Phpfox::getService('mfox.menu')->get($iEditId)) {
                if ($aMenu['is_group'] == 1) {
                    $this->url()->send('admincp.mfox.menu.addgroup', array('id' => $iEditId));
                }

                $bIsEdit = true;

                $this->template()->setHeader('<script type="text/javascript">$Behavior.editMenuMfoxAdmin = function(){$(\'#js_mp_category_item_' . $aMenu['parent_id'] . '\').attr(\'selected\', true);};</script>')->assign('aForms', $aMenu);
            }
        } else {
            $this->url()->send('admincp.mfox.menu'); // Not supported add menu item
        }

        if ($aVals = $this->request()->getArray('val')) {
            if ($bIsEdit) {
                if (Phpfox::getService('mfox.menu.process')->update($aMenu['id'], $aVals)) {
                    $this->url()->send('admincp.mfox.menu', null, Phpfox::getPhrase('mfox.menu_successfully_updated'));
                }
            } else {
                if (Phpfox::getService('mfox.menu.process')->add($aVals)) {
                    $this->url()->send('admincp.mfox.menu', null, Phpfox::getPhrase('mfox.menu_successfully_added'));
                }
            }
        }

        $this->template()->setTitle(($bIsEdit ? Phpfox::getPhrase('mfox.edit_a_menu') : Phpfox::getPhrase('mfox.create_a_new_menu')))
            ->setBreadcrumb(($bIsEdit ? Phpfox::getPhrase('mfox.edit_a_menu') : Phpfox::getPhrase('mfox.create_a_new_menu')), $this->url()->makeUrl('admincp.mfox.menu.add'))
            ->assign(array(
                'sOptions' => Phpfox::getService('mfox.menu')->getGroupOptions(),
                'bIsEdit' => $bIsEdit,
            ));
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('mfox.component_controller_admincp_menu_add_clean')) ? eval($sPlugin) : false);
    }
}
