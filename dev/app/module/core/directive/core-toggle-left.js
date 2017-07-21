define([
    
], function() {

    return function() {

        var link = function(scope, element, attrs) {

            element.bind('click', function(e) {
                if(window.nativeControl){
                    window.nativeControl.toggleMenu();   
                }else{
                    scope.toggleLeftSideMenu();    
                }
                
            });
        };

        return {
            restrict: 'A',
            link: link
        };
    };
});