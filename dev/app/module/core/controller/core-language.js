define([
    'global/base/BaseController',
],function(Ctrl){
   return function($injector, $http2, $scope, $site, gettext, gettextCatalog){
       
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });
        
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
        
        $scope.doLanguageSetting = $scope._setting($scope, function(){
            var btns = [];
            
            
            if($site.lang.options.length > 1){
                for(var i =0; i<$site.lang.options.length; ++i)
                {
                    var opt = $site.lang.options[i];
                    btns.push({
                        text: opt.name,
                        action: _chooseLanguage(opt.lang),
                    });
                }
            }
            
            
            return btns;
        });
        
        return $scope;
   };
});
