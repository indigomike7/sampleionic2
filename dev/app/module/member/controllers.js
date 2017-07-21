define([
    'member/controller/member-browse',
    'member/controller/member-item',
    'member/controller/member-list',
    'member/controller/member-search',
], function() {
    angular.module('myapp.controllers')
        .controller('MemberBrowseCtrl',require('member/controller/member-browse'))
        .controller('MemberItemCtrl',require('member/controller/member-item'))
        .controller('MemberListCtrlCtrl',require('member/controller/member-list'))
        .controller('MemberSearchCtrl',require('member/controller/member-search'))
        ;
});