define([
    'global/validator',
    'global/base/BaseController',
], function(Validator, Ctrl) {
    return function($state, $injector, $scope, $ionicPopup,$http2, $site, $modal, gettext, gettextCatalog, $location, $window, $q) {

        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.form = {};
        $scope.formData = {};
        $scope.dataReady = false;
        $scope.isProcessing = false;

        $scope.loadInit = function(){
            var sendData= {
                iVideoId: $state.params.iVideoId
                //fields: 'id,title,desc,category,canEdit,viewOptions,commentOptions,categoryOptions,auth,model,tags',
            };

            // fetch form data by individual apis, may need improvement
            $q.all([
                $http2.post('video/formadd',{
                    bPrivacyNoCustom: true
                }),
                $http2.get('video/detail', sendData),
            ]).then(function(data){

                //check for error in any request, need to better verify
                for (i = 0; i < data.length; ++i) {
                    if (data[i].error_code){
                        $modal.alert(gettextCatalog.getString('Can not get data from server'));
                        return $scope.goBack();
                    }
                }
                $scope.form.categoryOptions = data[0].data.category_options;
                $scope.form.viewOptions = data[0].data.view_options;
                $scope.form.commentOptions = data[0].data.comment_options;
                var formData = data[1].data;
                $scope.formData.title           =  formData.sTitle;
                $scope.formData.description     =  formData.sDescription;
                $scope.formData.iCategoryId     =  formData.iCategoryId;
                $scope.formData.iVideoId        =  formData.iVideoId;
                $scope.formData.auth_view       =  formData.iPrivacy;
                $scope.formData.auth_comment    =  formData.iPrivacyComment;
                $scope.formData.tags            =  formData.sTags;
                $scope.dataReady = true;
            });
        };

        // implement do save
        $scope.doSave =  function(){

            if($scope.isProcessing)
                return true;

            if(!$scope.formData.title){
                $modal.alert(gettextCatalog.getString('Video title is required'));
                return ;
            }

            $scope.isProcessing = true;

            $http2.post('video/edit', $scope.formData)
                .success(function(data){
                    console.log(data);
                    if(data.error_code){
                        $modal.alert(data.error_message);
                        return ;
                    }else{
                        $modal.toast(data.message);
                        $scope.goBack();
                    }
                })
                .error(function(){

                })
                .finally(function(){
                    $scope.isProcessing  = false;
                });
        };

        $scope.loadInit();
    };
});
