define([
    'moment'
], function(Moment) {
    return function() {
        return {
            restrict: 'A',
            scope: {},
            link: function(scope, element, attrs) {
                
                element.bind('click', function(e) {

                    var $currentTarget = $(e.currentTarget);
                    var time = $currentTarget.val();
                    var myNewTime = new Date();

                    if(/(\d{2}:\d{2}:\d{2})$/.test(time)) {
                        myNewTime.setHours(time.substr(0, 2));
                        myNewTime.setMinutes(time.substr(3, 2));
                    }
                    
                    $currentTarget.val(Moment(myNewTime.getTime()).format('HH:mm:00'));
                    $currentTarget.trigger('change');

                    datePicker.show({
                        date : myNewTime,
                        mode : 'time'
                    }, function(returnDate) {
                        if(returnDate !== "" && returnDate != "Invalid Date") {
                            var newDate = new Date(returnDate);
                            $currentTarget.val(Moment(newDate.getTime()).format('HH:mm:00'));
                            $currentTarget.trigger('change');
                        }
                        $currentTarget.blur();
                    });
                });
            }
        };
    };
});