;
var ynmanageskp = {
	pt : []
	, init: function(){
        ynmanageskp.onChangeDevice();
		ynmanageskp.onChangeModuleId();
	}
	, onChangeDevice: function(){
		$( "#device" ).change(function() {
            ynmanageskp.loadKey();
		});		
	}
    , onChangeModuleId: function(){
        $( "#module_id" ).change(function() {
            ynmanageskp.loadKey();
        });     
    }
    , loadKey: function(){
        $Core.ajax('mfox.loadKeyByModuleId',
        {       
            type: 'POST',
            params:
            {               
                action: 'loadKeyByModuleId'
                , module_id: $( "#module_id" ).val()
                , device: $( "#device" ).val()
            },
            success: function(sOutput)
            {       
                var oOutput = $.parseJSON(sOutput);
                $( "#content_data" ).html(oOutput.content);                     
            }
        });                
    }
};

$Behavior.ynInitManageskp = function(){
    ynmanageskp.init();
};
