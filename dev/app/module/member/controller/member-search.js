define(function() {
    return function($scope, $http2, $site, $modal, gettext, gettextCatalog) {

        var age_options = [];

        for(var i =13; i<=100; ++i){
            age_options.push({key: i, val: i});
        }

        $scope.searchForm = {
            'gender_options': [{
                key: 0,
                val: gettextCatalog.getString('Gender')}
            ],
            'age_options': age_options
        };

        $scope.doInitForm = function(){
            $http2.get('member/form_search')
                .success(function(data){
                    if(data.error_code){
                    }else{
                        $.each(data.genderOptions, function(key, val){
                            $scope.searchForm.gender_options.push({
                                key: key,
                                val: val
                            });
                        });
                    }
                })
                .error(function(){})
                .finally(function(){
                    // do finally requrest
                });
        };

        $scope.onAgeOptionsChange = function(option){

            var age_to = parseInt($scope.searchMembers.age_to);
            var age_from = parseInt($scope.searchMembers.age_from);
            if (option == 'from'){
                if (age_to) {
                    $scope.searchMembers.age_to = (age_from > age_to) ? age_from : age_to;
                }
            } else {
                if (age_to) {
                    $scope.searchMembers.age_from = (age_from > age_to) ? age_to : age_from;
                }
            }
        };

        $scope.doInitForm();

        return $scope;
    };
});