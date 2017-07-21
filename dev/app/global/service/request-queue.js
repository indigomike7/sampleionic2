define(function(){
    return function($http2, $interval){

        var $requestQueue = {
            interval: null,
            updateDelay: 1000,
            queue: [],
            processingQueue: [],
            storage: {},
            debug: false
        };

        /* status:
         waiting
         modified
         unmodified
         deleted
         */

        $requestQueue.addToQueue = function(id, callback) {

            $requestQueue.debug && console.log('$requestQueue : add ' + id);

            // check if the id is in the queue or not
            if ($requestQueue.queue.indexOf(id) == -1) {
                $requestQueue.queue.push(id);
                $requestQueue.storage[id] = {
                    status: 'waiting',
                    callback: callback
                };

                // start the interval if it's not running
                $requestQueue._start();
            }
        };

        $requestQueue.removeFromQueue = function(id) {

            var index = $requestQueue.queue.indexOf(id);
            $requestQueue.queue.splice(index, 1);
        };

        $requestQueue.getFromQueue = function(id) {

            var result = $requestQueue.storage[id] || {};
            delete $requestQueue.storage[id];
            return result;
        };

        $requestQueue._update = function(){

            $requestQueue.debug && console.log('$requestQueue request :');
            $requestQueue.debug && console.log($requestQueue.queue);
            $requestQueue._cancel();

            // move the queue to processing queue
            $requestQueue.processingQueue = $requestQueue.queue;
            $requestQueue.queue = [];

            var successCb = function(data) {
                if (data.error_code) {
                    return $requestQueue._updateError.apply($requestQueue, arguments);
                }
                $requestQueue._updateSuccess.apply($requestQueue, arguments);
            };

            var errorCb = function() {
                $requestQueue._updateError.apply($requestQueue, arguments);
            };

            $http2.post('feed/refresh', {
                aFeedId: $requestQueue.processingQueue
            })
                .success(successCb)
                .error(errorCb);
        };

        $requestQueue._updateSuccess = function(data) {

            $requestQueue._set(data);

            if ($requestQueue.queue.length > 0) {
                $requestQueue._start();
            }
        };

        $requestQueue._updateError = function() {

        };

        // cancel the interval
        $requestQueue._cancel = function() {
            if ($interval.cancel($requestQueue.interval)){
                $requestQueue.interval = null;
            }
        };

        // set interval
        $requestQueue._start = function() {

            if ($requestQueue.interval == null) {
                $requestQueue.interval = $interval($requestQueue._update, $requestQueue.interval);
            }
        };

        // set data to storage
        $requestQueue._set = function(data) {

            angular.forEach(data, function(item, id){
                $requestQueue.debug && console.log('$requestQueue : set ' + id);
                if (item == "deleted") {
                    $requestQueue.storage[id].status = "deleted";
                } else {
                    $.extend($requestQueue.storage[id], {status:"modified"}, item);
                }
                if ($requestQueue.storage[id].callback && typeof $requestQueue.storage[id].callback == 'function') {
                    $requestQueue.storage[id].callback($requestQueue.storage[id].status);
                }
            });
        };

        return $requestQueue;
    }
});