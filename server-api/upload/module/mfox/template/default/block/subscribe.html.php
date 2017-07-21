{foreach from=$aPackages item=aItem key=iKey}                    
<div class="table_left">
    {$aItem.title|convert|clean|shorten:30:'...'}
</div>
<div class="table_right" style="margin-bottom: 10px;">
    <input type="hidden" value="{$aItem.package_id}" name="val[package_id][]">
    <input type="text" id="storekitpurchase_key" name="val[storekitpurchase_key][]" size="50" maxlength="255" value="{$aItem.storekitpurchase_key}" />
</div>
<div class="clear"></div>
{/foreach}
