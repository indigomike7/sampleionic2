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
class Mfox_Component_Controller_Admincp_Menu_Addgroup extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        $bIsEdit = false;
        if ($iEditId = $this->request()->getInt('id')) {
            if ($aMenu = Phpfox::getService('mfox.menu')->get($iEditId)) {
                if ($aMenu['is_group'] != 1) {
                    $this->url()->send('admincp.mfox.menu.add', array('id' => $iEditId));
                }
                
                $bIsEdit = true;

                $this->template()->assign('aForms', $aMenu);
            }
        }

        if ($aVals = $this->request()->getArray('val')) {
            $aVals['is_group'] = 1;
            
            if ($bIsEdit) {
                if (Phpfox::getService('mfox.menu.process')->update($aMenu['id'], $aVals)) {
                    $this->url()->send('admincp.mfox.menu', null, Phpfox::getPhrase('mfox.menu_group_successfully_updated'));
                }
            } else {
                if (Phpfox::getService('mfox.menu.process')->add($aVals)) {
                    $this->url()->send('admincp.mfox.menu', null, Phpfox::getPhrase('mfox.menu_group_successfully_added'));
                }
            }
        }

        $this->template()->setTitle(($bIsEdit ? Phpfox::getPhrase('mfox.edit_a_menu_group') : Phpfox::getPhrase('mfox.create_a_new_menu_group')))
            ->setBreadcrumb(($bIsEdit ? Phpfox::getPhrase('mfox.edit_a_menu_group') : Phpfox::getPhrase('mfox.create_a_new_menu_group')), $this->url()->makeUrl('admincp.mfox.menu.add'))
            ->assign(array(
                'bIsEdit' => $bIsEdit,
            ));
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('mfox.component_controller_admincp_menu_add_group_clean')) ? eval($sPlugin) : false);
    }
}
