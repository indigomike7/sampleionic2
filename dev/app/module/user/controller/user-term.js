define([
    'global/base/BaseController'
], function(BaseController) {

    return function($scope, $injector, $state, $http2, $site, $modal, gettext, gettextCatalog) {

        $injector.invoke(BaseController, this, {
            $scope: $scope
        });

        $scope.localTerms = "You will not tag users or send email invitations to non-users without their consent." +
            "YouNet offers social reporting tools to enable users to provide feedback about tagging." +
            "You will not post unauthorized commercial communications (such as spam)" +
            "You will not post content that: is hate speech, threatening, or pornographic;" +
            "incites violence; or contains nudity or graphic or gratuitous violence." +
            "You will not facilitate or encourage any violations of this Statement or our policies." +
            "Users who violate any of the above agreements will be banned without noticed.";

        $scope.getTerm = function() {

            $http2.get('user/signup_term')
                .success($scope.getTermSuccess)
                .error($scope.getTermError);
        };

        $scope.getTermSuccess = function(data) {

            if (data.error_code) {
                return $modal.alert(data.error_message || gettextCatalog.getString('Can not load data from server'));
            }

            $scope.termData = data.message || $scope.localTerms;
        };

        $scope.getTermError = function() {

            console.warn('getTerm', arguments);
            $modal.alert(gettextCatalog.getString('Can not load data from server'));
        };

        $scope.getTerm();
    };
});