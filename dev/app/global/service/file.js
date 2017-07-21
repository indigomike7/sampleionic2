define(['global/site'],function(Site) {

    return function($q, $ionicLoading) {

        this.upload = function(api, path, params, type, fileKey) {

            var deffered = $q.defer();
            var ft = new FileTransfer();
            var options = new FileUploadOptions();
            var file_name = this.getFileName(path, type);
            var percent = 0;

            $.extend(params, {
                token: window.localStorage.getItem('token'),
                sTitle: params.sTitle || file_name
            });

            $.extend(options, {
                fileKey: fileKey || ('video' == type ? 'video' : 'image'),
                fileName: file_name,
                mimeType: this.getMimetype(type),
                chunkedMode: false,
                params: params
            });

            var success = function(result) {
                $ionicLoading.hide();
                var data = $.parseJSON(result.response);
                deffered.resolve(data);
            };

            var error = function() {
                $ionicLoading.hide();
                deffered.reject(arguments);
            };

            ft.onprogress = function(progressEvent) {
                if (progressEvent.lengthComputable) {
                    percent = (progressEvent.loaded * 100) / progressEvent.total;
                    $ionicLoading.show({
                        template: 'Uploading ' + parseInt(percent, 10) + '%, please wait ...'
                    });
                }
            };

            $ionicLoading.show({
                template: 'Uploading 0%, please wait ...'
            });
            console.log('options', options);
            ft.upload(path, encodeURI(api), success, error, options);

            return deffered.promise;
        };

        this.getFileName = function(path, type) {

            var name = '';

            if ('video' == type) {
                if (ionic.Platform.isAndroid()) {
                    name = path.match(/\/([^\/]+)$/)[1] + '.mp4';
                } else {
                    name = path.split('/').pop();
                }
            } else {
                if (path.match(/[^\/]+.jpg$/g)) {
                    name = path.match(/[^\/]+.jpg$/ig)[0];
                } else if (path.match(/id=(\w|\-)+/)) {
                    name = path.match(/id=(\w|\-)+/)[0].replace('id=', '') + '.jpg';
                } else if (ionic.Platform.isAndroid()) {
                    name = path.match(/\/([^\/]+)$/)[1] + '.jpg';
                }
            }

            return name;
        };

        this.getMimetype = function(type) {

            if ('video' == type) {
                return (ionic.Platform.isAndroid()) ? 'video/mp4' : 'video/quicktime';
            } else {
                return 'image/jpeg';
            }
        };
    };
});