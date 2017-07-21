define([
    'ImgCache',
],function(){
    return function($site) {
        
        return {
            restrict: 'A',
            link: function(scope, el, attrs) {
                attrs.$observe('ngBackground', function(src) {
                    $site.debug > 2 && console.log('ng-background: ',src);
                    if(!src) return ;
                    
                    el.css({
                        backgroundImage: 'url("'+src+'")',
                    });
                });
            }
        };
    };
});
