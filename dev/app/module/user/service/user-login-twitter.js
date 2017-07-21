define([], function() {
    return function($rootScope, $injector, $http, $http2, $modal, $site, $location, $window, gettext, gettextCatalog, userSignupData, $viewer) {
        function TwitterConnect() {
            
            this.token = "";
            this.token_secret = "";
            this.data = {};
            this.error = false;
            var that = this;
            
            this.connect = function() {
                var ref = window.open('social-connect-twitter.html', '_blank', 'location=no, status=no');

                ref.addEventListener('loadstop', function(event) {
                    
                    var url = decodeURI(event.url);

                    var token = /[\?\&]access_token=([^&]+)/.exec(url);
                    var token_secret = /[\?\&]secret_token=([^&]+)/.exec(url);
                    var json_data = /[\?\&]json_data=([^&]+)/.exec(url);
                    var error = /\?error=(.+)$/.exec(url);

                    if (token && token_secret) {

                        ref.close();
                        that.token = token[1];
                        that.token_secret = token_secret[1];
                        
                        
                        var data = JSON.parse(decodeURIComponent(json_data[1]));
                        
                        console.log('server response',JSON.stringify(data));
                        
                        if (data.id) {
                            var name = data.name.split('+');
                            var json = {
                                'id' : data.id,
                                'email' : '',
                                'first_name' : name[0],
                                'last_name' : name[1],
                                'username' : data.screen_name,
                                // server has been encode base 64 encode.
                                'profile_image_url' : atob(data.profile_image_url)
                            };
                            
                            console.log('that.processLogin[]',JSON.stringify(json));
                            
                            that.processLogin(json);
                        }

                    }
                    // Connect fail
                    if (error) {
                        $modal.alert(gettextCatalog.getString('connect twitter error') + ': ' + JSON.stringify(error));
                    }
                    
                    console.log('end process step url ' + url);
                });
            };

            this.quickLogin = function(data) {

                // save data
                localStorage.setItem('token', data.token);

                localStorage.setItem('viewer', JSON.stringify(data));
                
                $http.defaults.headers.common.token = data.token;
                
                $rootScope.$broadcast('user:login', data);

                // check subscription
                if ($viewer.get('bSubscription') === false) {
                    return $location.path('app/subscriptions/browse');
                }

                window.location.href = 'index.html';
            };

            this.processLogin = function(jsonData) {
                var postData = {
                    sLoginBy: 'twitter',
                    sEmail: '',
                    sLoginUID: jsonData.id,
                    sAccessToken: that.token,
                    sSecretToken: that.token_secret
                };

                $http2.post('user/login', postData)
                .success(function(resData) {
                    
                    console.log('user/login response',JSON.stringify(resData));
                    
                    if (resData == null) {
                        $modal.toast("Cannot connect to server");
                        return;
                    }

                    if (resData.token != null) {
                        that.quickLogin(resData);
                        return;
                    }

                    if (resData.error_step == 'signup') {

                        // Account do not exists, signup new account
                        $modal.toast("This account does not exists, please create a new one");

                        var updateData  = {
                            sFullName: jsonData.first_name + (jsonData.last_name ? ' ' + jsonData.last_name : ''),
                            sEmail : '',
                            iGender : "",
                            sLoginUID : jsonData.id,
                            sAccessToken: that.token,
                            sSecretToken: that.token_secret,
                            sFacebook : '',
                            sTwitter : jsonData.username,
                            sLoginBy : 'twitter',
                            sUserImageUrl : jsonData.profile_image_url
                        };
                        userSignupData.update(updateData);

                        console.log('userSignupData.update()',JSON.stringify(resData));
                        
                        $location.url('app/signup', true);
                        return ;
                    }
                    
                    if(resData.error_code){
                        $modal.toast(resData.error_message || gettextCatalog.getString('Can not get data from server'));
                        return ;
                    }
                    
                }).error(function() {
                    $modal.alert(gettextCatalog.getString('Cannot connect to server'));
                });
            };
        };
        
        this.login = function(){
            this.twitterConnect = new TwitterConnect();
            this.twitterConnect.connect();
        };
        
    };
});


