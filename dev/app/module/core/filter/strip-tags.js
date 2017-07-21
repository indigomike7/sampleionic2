define([
    'global/helper'
], function(Helper) {
    return function() {
        return function(input) {
            return Helper.stripTags(input);
        };
    };
});
