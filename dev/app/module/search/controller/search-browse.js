define([
    'global/base/BaseController'
], function(Ctrl) {
    
    return function($scope, $injector, gettext, gettextCatalog, $timeout) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.showAdvSearch = true;
        
        var ngInputSearchQ = angular.element('#input_search_q');
        
        $timeout(function() {
            ngInputSearchQ.focus();
            if (typeof(cordova) != 'undefined' && !cordova.plugins.Keyboard.isVisible) {
                cordova.plugins.Keyboard.show();
            }
        }, 1000);

        $scope.closeKeyboard = function() {
            if (typeof(cordova) != 'undefined' && cordova.plugins.Keyboard.isVisible) {
                cordova.plugins.Keyboard.close();
            }
        };

        ngInputSearchQ.on('blur', $scope.closeKeyboard);

        $scope.$on('$destroy', function() {
            ngInputSearchQ.off('blur', $scope.closeKeyboard);
        });
        
        $scope.searchCriteria =  {
            sSearch: '',
            iPage: 1,
            iLimit:20,
            hasSearch: false,
            sFilterBy: '',
        };
        
        $scope.afterResetQuery = function(){
          $scope.searchCriteria.hasSearch =  $scope.searchCriteria.sSearch != '';
        };
        
        return $scope;
    };
});