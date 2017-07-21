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
class Mfox_Component_Controller_Admincp_Menu_Index extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        if ($aOrder = $this->request()->getArray('order')) {
            if (Phpfox::getService('mfox.menu.process')->updateOrder($aOrder)) {
                $this->url()->send('admincp.mfox.menu', null, Phpfox::getPhrase('mfox.menu_order_successfully_updated'));
            }
        }

        if ($iDelete = $this->request()->getInt('delete')) {
            if (Phpfox::getService('mfox.menu.process')->delete($iDelete)) {
                $this->url()->send('admincp.mfox.menu', null, Phpfox::getPhrase('mfox.menu_successfully_deleted'));
            }
        }

        $this->template()->setTitle(Phpfox::getPhrase('mfox.manage_menus'))
            ->setBreadcrumb(Phpfox::getPhrase('mfox.manage_menus'), $this->url()->makeUrl('admincp.mfox.menu'))
            ->setHeader(array(
                'admin.css' => 'module_mfox',
                'jquery/ui.js' => 'static_script',
                'admin.js' => 'module_mfox',
                '<script type="text/javascript">$Behavior.menuMfoxAdminUrl = function() { $Core.mfoxMenu.url(\'' . $this->url()->makeUrl('admincp.mfox.menu') . '\'); };</script>',
            ))
            ->assign(array(
                'sMenus' => Phpfox::getService('mfox.menu')->getForManage(),
            ));
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('mfox.component_controller_admincp_menu_index_clean')) ? eval($sPlugin) : false);
    }
}
