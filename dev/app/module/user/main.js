define([
    'user/controllers',
    'user/plugin/activity',
    'user/directives',
    'user/services',
    
    'text!tpl/user/forgot-password.html',
    'text!tpl/user/login.html',
    'text!tpl/user/signup.html',
    'text!tpl/user/user-about.html',
    'text!tpl/user/user-activity.html',
    'text!tpl/user/user-detail.html',
    'text!tpl/user/user-edit-avatar.html',
    'text!tpl/user/user-edit-cover.html',
    'text!tpl/user/user-edit-info.html',
    'text!tpl/user/user-friend.html',
    'text!tpl/user/user-mutual-friend.html',
    'text!tpl/user/user-photo.html',
    'text!tpl/user/user-term.html',
    'text!tpl/user/user-point.html',
], function() {
    angular.module('myapp')
    .config(function($stateProvider, $urlRouterProvider, gettext) {
        $stateProvider.state('app.login', {
            module: 'user',
            url: '/login',
            cache: false,
            history: {
                isRoot: true
            },
            views: {
                menuContent: {
                    template: require('text!tpl/user/login.html'),
                    controller: 'UserLoginCtrl',
                }
            }
        })
        .state('app.forgotPassword', {
            module: 'user',
            url: '/forgot-password',
            cache: false,
            history: false,
            views: {
                menuContent: {
                    template: require('text!tpl/user/forgot-password.html'),
                    controller: 'UserForgotPasswordCtrl',
                }
            }
        })
        .state('app.signup', {
            module: 'user',
            url: '/signup',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/user/signup.html'),
                    controller: 'UserSignupCtrl',
                }
            }
        })
        .state('app.userEditAvatar',{
            module: 'user',
            url: '/user/edit-avatar/:iUserId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/user/user-edit-avatar.html'),
                    controller: 'UserEditAvatarCtrl',
                }
            }
        })
        .state('app.userEditCover',{
            module: 'user',
            url: '/user/edit-cover/:iUserId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/user/user-edit-cover.html'),
                    controller: 'UserEditCoverCtrl',
                }
            }
        })
        .state('app.userEditInfo',{
            module: 'user',
            url: '/user/edit-info/:iUserId',
            cache: false,
            history: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/user/user-edit-info.html'),
                    controller: 'UserEditInfoCtrl',
                }
            }
        })
        .state('app.userProfile',{
            module: 'user',
            url: '/user/{iUserId}',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/user/user-detail.html'),
                    controller: 'UserDetailCtrl',
                },
                'tabContent@app.userProfile': {
                    template: require('text!tpl/user/user-activity.html'),
                    controller: 'UserActivityCtrl',
                },
            }
        })
        .state('app.userProfileFromNav',{
            module: 'user',
            url: '/user-from-nav/{iUserId}',
            cache: false,
            history: {
                title: '',
                isRoot: true
            },
            data: {
                fromNav: 1,
            },
            views: {
                'menuContent': {
                    template: require('text!tpl/user/user-detail.html'),
                    controller: 'UserDetailCtrl',
                },
                'tabContent@app.userProfileFromNav': {
                    template: require('text!tpl/user/user-activity.html'),
                    controller: 'UserActivityCtrl',
                },
            }
        })
        .state('app.userPhotos',{
            module: 'user',
            url: '/user_photos/{iUserId}',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/user/user-detail.html'),
                    controller: 'UserDetailCtrl',
                },
                'tabContent@app.userPhotos': {
                    template: require('text!tpl/user/user-photo.html'),
                    controller: 'UserPhotoCtrl',
                },
            }
        })
        .state('app.userAbout',{
            module: 'user',
            url: '/user_about/{iUserId}',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/user/user-detail.html'),
                    controller: 'UserDetailCtrl',
                },
                'tabContent@app.userAbout': {
                    template: require('text!tpl/user/user-about.html'),
                    controller: 'UserPhotoCtrl',
                },
            }
        })
         .state('app.userFriends',{
            module: 'user',
            url: '/user_friends/{iUserId}',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/user/user-detail.html'),
                    controller: 'UserDetailCtrl',
                },
                'tabContent@app.userFriends': {
                    template: require('text!tpl/user/user-friend.html'),
                    controller: 'UserFriendCtrl',
                },
            }
        })
        .state('app.userMutualFriends',{
            module: 'user',
            url: '/user_mutual_friends/{iUserId}',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/user/user-detail.html'),
                    controller: 'UserDetailCtrl',
                },
                'tabContent@app.userMutualFriends': {
                    template: require('text!tpl/user/user-mutual-friend.html'),
                    controller: 'UserMutualFriendCtrl',
                },
            }
        })
        .state('app.userPoints',{
            module: 'user',
            url: '/user_points/{iUserId}',
            cache: false,
            views: {
                'menuContent': {
                    template: require('text!tpl/user/user-detail.html'),
                    controller: 'UserDetailCtrl',
                },
                'tabContent@app.userPoints': {
                    template: require('text!tpl/user/user-point.html'),
                    controller: 'UserPointCtrl',
                },
            }
        })
        ;
    });
});