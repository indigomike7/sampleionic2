define([
    'angular',
    'myapp',
    
    'user/controller/user-forgot-password',
    'user/controller/user-login',
    'user/controller/user-signup',
    'user/controller/user-about',
    'user/controller/user-activity',
    'user/controller/user-detail',
    'user/controller/user-edit-avatar',
    'user/controller/user-edit-cover',
    'user/controller/user-edit-info',
    'user/controller/user-friend',
    'user/controller/user-mutual-friend',
    'user/controller/user-item',
    'user/controller/user-photo',
    'user/controller/user-term',
    'user/controller/user-point',
], function() {
    angular.module('myapp.controllers')
        .controller('UserForgotPasswordCtrl', require('user/controller/user-forgot-password'))
        .controller('UserLoginCtrl', require('user/controller/user-login'))
        .controller('UserSignupCtrl', require('user/controller/user-signup'))
        .controller('UserDetailCtrl', require('user/controller/user-detail'))
        .controller('UserEditAvatarCtrl', require('user/controller/user-edit-avatar'))
        .controller('UserEditCoverCtrl', require('user/controller/user-edit-cover'))
        .controller('UserEditInfoCtrl', require('user/controller/user-edit-info'))
        .controller('UserPhotoCtrl', require('user/controller/user-photo'))
        .controller('UserFriendCtrl', require('user/controller/user-friend'))
        .controller('UserMutualFriendCtrl', require('user/controller/user-mutual-friend'))
        .controller('UserActivityCtrl', require('user/controller/user-activity'))
        .controller('UserAboutCtrl', require('user/controller/user-about'))
        .controller('UserTermCtrl', require('user/controller/user-term'))
        .controller('UserPointCtrl', require('user/controller/user-point'))
        ;
});