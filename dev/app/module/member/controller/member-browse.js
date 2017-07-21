define([
    'global/base/BrowseController',
    'text!tpl/member/member-search.html'
], function(BrowseController, searchTemplate) {
    
    return function($scope, $injector) {

        $injector.invoke(BrowseController, this, {
            $scope: $scope
        });

        $scope.searchTemplate = searchTemplate;
        
        $scope.searchMembers =  {
            sSearch: '',
            iPage: 1,
            iLimit:40,
            age_from: "0",
            age_to: "0",
            gender: 0
        };
        
        return $scope;
    };
});