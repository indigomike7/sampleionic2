define([
    'core/filter/is-plural',
    'core/filter/prepare-html',
    'core/filter/prepare-url',
    'core/filter/range',
    'core/filter/strip-tags',
    'core/filter/unescape',
    'core/filter/unix-format',
    'core/filter/unix-from-now'
], function() {
    angular.module('myapp.filters')
        .filter('isPlural', require('core/filter/is-plural'))
        .filter('prepareHtml', require('core/filter/prepare-html'))
        .filter('prepareUrl', require('core/filter/prepare-url'))
        .filter('range', require('core/filter/range'))
        .filter('stripTags', require('core/filter/strip-tags'))
        .filter('unescape', require('core/filter/unescape'))
        .filter('unixFormat', require('core/filter/unix-format'))
        .filter('unixFromNow', require('core/filter/unix-from-now'))
    ;
});
