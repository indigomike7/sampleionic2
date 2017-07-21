/* Simple JavaScript Inheritance
* By John Resig http://ejohn.org/
* MIT Licensed.
*/
// Inspired by base2 and Prototype
(function() {

    return function() {
        this.extend = function(destination, source) {
            for (var property in source) {
                destination[property] = source[property];
            };
            return destination;
        };
    };
})(); 