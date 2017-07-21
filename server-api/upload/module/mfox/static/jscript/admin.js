
$Core.mfoxMenu =
{
	sUrl: '',
	
	url: function(sUrl)
	{
		this.sUrl = sUrl;
	},	
	
	action: function(oObj, sAction)
	{
		aParams = $.getParams(oObj.href);	
		
		$('.dropContent').hide();	
		
		switch (sAction)
		{
			case 'edit':
				window.location.href = this.sUrl + 'add/?id=' + aParams['id'];
				break;
			case 'delete':
				if (confirm(oTranslations['core.are_you_sure']))
				{
					window.location.href = this.sUrl + '?delete=' + aParams['id'];
				}
				break;				
			default:
			
				break;	
		}
		
		return false;
	}
}

$Behavior.mfoxAdmin = function()
{
	$('.sortable ul').sortable({
			axis: 'y',
			update: function(element, ui)
			{
				var iCnt = 0;
				$('.js_mp_order').each(function()
				{
					iCnt++;
					this.value = iCnt;
				});
			},
			opacity: 0.4
		}
	);	
	
	$('.js_drop_down').click(function()
	{		
		eleOffset = $(this).offset();
		
		aParams = $.getParams(this.href);

        var showDelete = $(this).closest('li').hasClass('menu-group');
        console.log('showDelete', showDelete);
        $('.dropContent .link-delete').toggle(showDelete);
		
		$('#js_cache_menu').remove();
		
		$('body').prepend('<div id="js_cache_menu" style="position:absolute; left:' + eleOffset.left + 'px; top:' + (eleOffset.top + 15) + 'px; z-index:100;">' + $('#js_menu_drop_down').html() + '</div>');
		
		$('#js_cache_menu .link_menu li a').each(function()
		{			
			this.href = '#?id=' + aParams['id'];			
		});
		
		$('.dropContent').show();		
		
		$('.dropContent').mouseover(function()
		{
			$('.dropContent').show(); 
			
			return false;
		});
		
		$('.dropContent').mouseout(function()
		{
			$('.dropContent').hide(); 
			$('.sJsDropMenu').removeClass('is_already_open');			
		});
		
		return false;
	});		
	
};