define([
    'global/validator',
    'global/base/BaseController',
], function (Validator, Ctrl) {
    return function ($state, $injector, $scope, $ionicPopup, $http2, $site, $modal, gettext, gettextCatalog, $location, $window, $q) {
        console.log('VideoChannelEditChannelCtrl');
        $injector.invoke(Ctrl, this, {
            $scope: $scope
        });

        $scope.form = {};
        $scope.formData = {};
        $scope.dataReady = false;
        $scope.isProcessing = false;
        $scope.iChannelId = $state.params.iChannelId;
        $scope.loadInit = function () {
            var sendData = {
                iChannelId: $scope.iChannelId
            };

            // fetch form data by individual apis, may need improvement
            $q.all([
                $http2.post('videochannel/formadd', {
                    bPrivacyNoCustom: true
                }),
                $http2.get('videochannel/detailChannel', sendData),
            ]).then(function (data) {

                //check for error in any request, need to better verify
                for (i = 0; i < data.length; ++i) {
                    if (data[i].error_code) {
                        $modal.alert(gettextCatalog.getString('Can not get data from server'));
                        return $scope.goBack();
                    }
                }
                $scope.form.categoryOptions = data[0].data.category_options;
                $scope.form.viewOptions = data[0].data.view_options;
                var channelInfo = data[1].data;
                $scope.formData.sImg = channelInfo.sVideoImage;
                $scope.formData.sTitle = channelInfo.sTitle;
                $scope.formData.sDescription = channelInfo.sSummary;
                $scope.formData.auth_view = channelInfo.iViewPrivacy;
                $scope.formData.iCategoryId = channelInfo.iCategoryId;
                $scope.dataReady = true;
            });
        };

        // implement do save
        $scope.doSave = function () {

            if ($scope.isProcessing)
                return true;

            if (!$scope.formData.sTitle) {
                $modal.alert(gettextCatalog.getString('Channel title is required'));
                return;
            }

            $scope.isProcessing = true;

            $http2.post('videochannel/saveChannel', $scope.formData)
                .success(function (data) {
                    console.log(data);
                    if (data.error_code) {
                        $modal.alert(data.error_message);
                    } else {
                        $modal.toast(data.message);
                        $scope.goToPage('app/videochannel/add-video/' + $scope.iChannelId);
                    }
                })
                .error(function () {

                })
                .finally(function () {
                    $scope.isProcessing = false;
                });
        };

        $scope.loadInit();
    };
});
