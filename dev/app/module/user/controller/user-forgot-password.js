define([
    'global/base/BaseController',
    'global/validator'
], function(BaseController, Validator) {

    return function($scope, $site, gettext, gettextCatalog, $injector, $http2, $modal, $ionicSideMenuDelegate) {

        $injector.invoke(BaseController, this, {
            $scope : $scope
        });

        $scope.lockMenu = function() {
            $ionicSideMenuDelegate.canDragContent(false);
            if(window.nativeControl){
                window.nativeControl.lockMenu();
            }
        };

        $scope.lockMenu();

        $scope.isProcessing = false;
        $scope.data  = {
            sEmail: "",
        };

        $scope.onResetPassword = function() {
            if ($scope.isProcessing)
                return;
            
            if($scope.data.sEmail == '')
             {
                 $modal.alert(gettextCatalog.getString('Please enter your email'));
                 return ;
             }           
             
             if(false == Validator.isEmail($scope.data.sEmail)){
                 $modal.alert(gettextCatalog.getString('Please enter a valid email'));
                 return ;
             }     

            $scope.isProcessing = true;
            
            $http2.post('user/forgot',$scope.data)
            .success(function(data){
                if(data.error_code){
                    return $modal.alert(data.error_message);
                }

                $modal.alert(data.message || gettextCatalog.getString('Password request successfully sent. Check your email to verify your request.'));
                $scope.goBack();
            }).error(function(){
                $modal.alert(gettextCatalog.getString('Can not load data from server'));
            }).finally(function(){
                $scope.isProcessing = false;
            });
        };

        return $scope;
    };
});
