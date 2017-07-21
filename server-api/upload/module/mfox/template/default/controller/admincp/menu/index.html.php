<?php
/**
 * [PHPFOX_HEADER]
 *
 * @version         4.04
 * @package         mfox
 *
 * @author          YouNetCo
 * @copyright       YouNetCo
 */

defined('PHPFOX') or exit('NO DICE!');

?>
<div id="js_menu_drop_down" style="display:none;">
	<div class="link_menu dropContent" style="display:block;">
		<ul>
			<li><a href="#" onclick="return $Core.mfoxMenu.action(this, 'edit');">{phrase var='mfox.edit'}</a></li>
			<li class="link-delete"><a href="#" onclick="return $Core.mfoxMenu.action(this, 'delete');">{phrase var='mfox.delete'}</a></li>
		</ul>
	</div>
</div>
<div class="table_header">
	{phrase var='mfox.manage_menus'}
</div>
<form class="mfox-menu" method="post" action="{url link='admincp.mfox.menu'}">
	<div class="table form-group">
		<div class="sortable">
			{$sMenus}
		</div>
	</div>
	<div class="table_clear">
		<input type="submit" value="{phrase var='mfox.update_order'}" class="button btn-primary" />
	</div>
</form>
