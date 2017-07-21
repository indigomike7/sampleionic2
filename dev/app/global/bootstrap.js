/*global define, require, console, cordova, navigator */

define(['angular','ionic','ionicAngular'], function (angular,ionic,ionicAngular) {
    'use strict';

    var $html,
        onDeviceReady = function () {
            angular.bootstrap(document.body, ['myapp']);
            // save window height
            window.myappOuterHeight = window.outerHeight;
        };
    
    document.addEventListener("deviceready", onDeviceReady, false);
    
    if (typeof cordova === 'undefined') {
        $html = angular.element(document.getElementsByTagName('html')[0]);
        angular.element().ready(function () {
            try {
                angular.bootstrap(document.body, ['myapp']);
            } catch (e) {
                console.error(e.stack || e.message || e);
            }
        });
    }

    var onPause = function() {
        window.isInBackground = true;
    };

    var onResume = function() {
        window.isInBackground = false;
    };

    document.addEventListener("pause", onPause, false);

    document.addEventListener("resume", onResume, false);
});