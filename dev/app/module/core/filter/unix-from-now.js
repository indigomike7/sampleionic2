define([
    'moment'
], function(Moment) {
    return function() {
        return function(input, withoutSuffix) {
            if (isNaN(parseInt(input))) {
                return '';
            }

            withoutSuffix = (false === withoutSuffix) ? false : true;

            return Moment.unix(input).fromNow(withoutSuffix);
        };
    };
});
