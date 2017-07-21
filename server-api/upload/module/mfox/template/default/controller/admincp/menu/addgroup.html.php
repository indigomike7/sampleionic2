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
<form class="mfox-menu-add" method="post" action="{url link='admincp.mfox.menu.addgroup'}">
{if $bIsEdit}
    <div><input type="hidden" name="id" value="{$aForms.id}" /></div>
{/if}
    <div class="table_header">
        {phrase var='mfox.menu_group_details'}
    </div>
    <div class="table form-group">
        <div class="table_left">
            {phrase var='mfox.label'}:
        </div>
        <div class="table_right">
            <input type="text" name="val[label]" size="30" maxlength="100" value="{value type='input' id='label'}" />
        </div>
        <div class="clear"></div>
    </div>
    <div class="table form-group">
        <div class="table_left">
            {phrase var='mfox.active'}:
        </div>
        <div class="table_right">
            <div class="item_is_active_holder">
                <span class="js_item_active item_is_active"><input type="radio" name="val[is_enabled]" value="1" {value type='radio' id='is_enabled' default='1' selected='true'}/> {phrase var='mfox.yes'}</span>
                <span class="js_item_active item_is_not_active"><input type="radio" name="val[is_enabled]" value="0" {value type='radio' id='is_enabled' default='0'}/> {phrase var='mfox.no'}</span>
            </div>
        </div>
        <div class="clear"></div>
    </div>
    <div class="table_clear">
        <input type="submit" value="{phrase var='mfox.submit'}" class="button btn-primary" />
    </div>
</form>