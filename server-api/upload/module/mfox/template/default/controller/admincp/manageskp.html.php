<form method="post" action="{url link='admincp.mfox.manageskp'}">
    <div class="table">
        <div class="table_header">
            {phrase var='mfox.manage_store_kit_product_on_iap'}
        </div>
        <div class="table">
            <div class="table_left">
                {phrase var='mfox.device'}:
            </div>
            <div class="table_right">
                <select id="device" name="val[device]" style="width:300px;">
                  {foreach from=$aDevice item=aItem key=iKey}                    
                    <option {if $sActiveDevice == $aItem.id}selected{/if]} value="{$aItem.id}" > {$aItem.phrase}</option> 
                  {/foreach}
                </select>
            </div>
        </div>
        <div class="clear"></div>
        <div class="table">
            <div class="table_left">
                {phrase var='mfox.module'}:
            </div>
            <div class="table_right">
                <select id="module_id" name="val[module_id]" style="width:300px;">
                  {foreach from=$aModules item=aItem key=iKey}                    
                    <option {if $sActiveModuleId == $iKey}selected{/if]} value="{$iKey}" > {$aItem}</option> 
                  {/foreach}
                </select>
            </div>
        </div>
        <div class="clear"></div>
        <div id="content_data" class="table">
            {if 'subscribe' == $sActiveModuleId}
                {template file='mfox.block.subscribe'}
            {else}
                {template file='mfox.block.defaultmodule'}
            {/if}
        </div>  
    </div>
    <div class="table_clear">
        <input type="submit" value="{phrase var='mfox.save'}" class="button" />
    </div>
</form>