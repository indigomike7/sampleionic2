define([
    'user/service/user-signup-data',
    'user/service/user-login-facebook',
    'user/service/user-login-twitter',
],function(){
    angular.module('myapp.services')
    .service('userSignupData', require('user/service/user-signup-data'))
    .service('userLoginFacebook', require('user/service/user-login-facebook'))
    .service('userLoginTwitter', require('user/service/user-login-twitter'))
    ;
});
