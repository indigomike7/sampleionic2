define([],function(){
    return function($ionicActionSheet, $site, gettext, gettextCatalog,$http2){
        function _chooseLanguage(lang){
            return function(){
                console.log('choose language', lang);
                localStorage.setItem('lang', lang);
                $http2.post('core/changeLanguage', {language_id:lang})
                    .success(function(){
                        window.location.href = 'index.html';
                    })
                    .error()
                    .finally();
            };
        };
        
        function _buttons(){
            var buttons = [];
                if($site.lang.options.length > 1){
                    for(var i =0; i<$site.lang.options.length; ++i)
                    {
                        var opt = $site.lang.options[i];
                        buttons.push({
                            text: gettext(opt.name),
                            action: _chooseLanguage(opt.lang),
                        });
                    }
                }
            return buttons;
        };
        
        this.doSelectLanguage  = function(){
            var btns  = _buttons();
            $ionicActionSheet.show({
                buttons: btns,
                cancelText: gettextCatalog.getString('Cancel'),
                buttonClicked: function(index) {
                    btns[index].action();
                    return true;
                }
            });
        };
    };
});
