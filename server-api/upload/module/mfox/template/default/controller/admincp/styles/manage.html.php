<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div id="js_category_holder">
    <div class="table_header">
        {phrase var='mfox.manage_styles'}
    </div>

    <form method="post" action="{url link='admincp.mfox.styles.manage'}">
        {if count($aStyles)}
        <table>
            <thead>
            <tr>
                <td style="width:10px;"><input type="checkbox" name="val[id]" value="" id="js_check_box_all" class="main_checkbox" /></td>
                <td><strong>{phrase var='mfox.style_id'}</strong></td>
                <td><strong>{phrase var='mfox.style_name'}</strong></td>
                <td><strong>{phrase var='mfox.style_active'}</strong></td>
                <td><strong>{phrase var='mfox.edit'}</strong></th>
            </tr>
            </thead>
            {foreach from=$aStyles key=iKey item=aStyle}
            <tr id="js_row{$aStyle.style_id}" class="checkRow{if is_int($iKey/2)} tr{else}{/if}">
                <td><input type="checkbox" name="id[]" class="checkbox" value="{$aStyle.style_id}" id="js_id_row{$aStyle.style_id}" /></td>
                <td>{$aStyle.style_id}</td>
                
                <td>{$aStyle.name|convert|clean}</td>
                <td class="t_center">
                    {if !$aStyle.is_publish}
                    {phrase var='core.no'}
                    {else}
                    {phrase var='core.yes'}
                    {/if}
                </td>
            <td>
                <a href="{url link='admincp.mfox.styles' id={$aStyle.style_id}">{phrase var='mfox.edit'}</a>
            </td>
            </tr>
            {/foreach}
        </table>
        <div class="table_bottom">
            <input type="submit" name="delete" value="{phrase var='mfox.delete_selected'}" class="sJsConfirm delete button sJsCheckBoxButton disabled" disabled="true" />
        </div>
        {else}
        <div class="extra_info">
            {phrase var='mfox.no_styles'}
        </div>
        {/if}
    </form>
</div>
{pager}