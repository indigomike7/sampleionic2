define([
    'comment/directive/comment-list',
    'comment/directive/comment-form-footer',
    'comment/directive/comment-form-subfooter',
    'comment/directive/comment-attachment-preview-dir',
], function(commentDir) {

    angular.module('myapp.directives')
        .directive('commentDir', require('comment/directive/comment-list'))
        .directive('commentFormFooterDir', require('comment/directive/comment-form-footer'))
        .directive('commentFormSubfooterDir', require('comment/directive/comment-form-subfooter'))
        .directive('commentAttachmentPreviewDir', require('comment/directive/comment-attachment-preview-dir'))
        ;
});