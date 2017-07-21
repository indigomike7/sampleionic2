define([
    'moment'
], function(Moment) {
    return function() {
        return {
            restrict: 'A',
            scope: {
                minDate: '@',
                maxDate: '@'
            },
            link: function(scope, element, attrs) {
                
                element.bind('click', function(e) {

                    var $currentTarget = $(e.currentTarget);

                    var myNewDate = Date.parse($currentTarget.val()) || new Date();
                    if (typeof myNewDate === "number") {
                        myNewDate = new Date(myNewDate);
                    }

                    var minDate = '';
                    if (scope.minDate) {
                        minDate = Date.parse(scope.minDate + ' 00:00:00');
                        if (myNewDate.getTime() < minDate) {
                            myNewDate.setTime(minDate);
                        }
                    }

                    var maxDate = '';
                    if (scope.maxDate) {
                        var maxDate = Date.parse(scope.maxDate + ' 23:59:59');
                        if (myNewDate.getTime() > maxDate) {
                            myNewDate.setTime(maxDate);
                        }
                    }
                    
                    $currentTarget.val(Moment(myNewDate.getTime()).format('YYYY-MM-DD'));
                    $currentTarget.trigger('change');

                    datePicker.show({
                        date: myNewDate,
                        minDate: minDate,
                        maxDate: maxDate,
                        mode: 'date'
                    }, function(returnDate) {
                        if (returnDate !== "" && returnDate != "Invalid Date") {
                            var newDate = new Date(returnDate);
                            // fix case date picker return wrong selected value
                            if (newDate.getFullYear() <= 1930) {
                                newDate.setTime(newDate.getTime() + 3600e3);
                            }
                            if (maxDate && newDate.getTime() > maxDate) {
                                newDate.setTime(maxDate);
                            } else if (minDate && newDate.getTime() < minDate) {
                                newDate.setTime(minDate);
                            }
                            $currentTarget.val(Moment(newDate.getTime()).format('YYYY-MM-DD'));
                            $currentTarget.trigger('change');
                        }
                        $currentTarget.blur();
                    });
                });
            }
        };
    };
});