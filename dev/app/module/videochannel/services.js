define([
    'videochannel/service/add-channel-data',
],function(){
    angular.module('myapp.services')
        .service('addChannelData', require('videochannel/service/add-channel-data'))
    ;
});
