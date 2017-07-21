define([
    'global/validator',
    'global/viewer',
],function(Validator,Viewer){
    return function($rootScope, $http2, $http, $modal,userSignupData, $site, $location,$window,gettext, gettextCatalog, $viewer){
        
        function FacebookConnect(){
        
            // that buid
            var that = this;
            
            this.token = null;
            this.permissions = ["email","user_friends","user_birthday"];
            
            this.connect = function(){
                
                if(typeof facebookConnectPlugin == 'undefined')
                {
                    $modal.alert(gettextCatalog.getString('Could not find plugin facebookConnectPlugin!'));
                }else{
                    facebookConnectPlugin.getAccessToken(function(response){
                        that.facebookLogoutThenLogin();
                    },function(){
                        that.loginToFacebook();
                    });   
                }
            };
            
            this.facebookLogoutThenLogin = function(){
                facebookConnectPlugin.logout(function(){
                    that.loginToFacebook();
                },function(){
                    that.loginToFacebook();
                });
            };
            
            this.loginToFacebook = function(){
                 facebookConnectPlugin.login(that.permissions, that.getProfileInfo, function(error){
                    $modal.alert(error.errorMessage);
                },function(){
                    console.log(JSON.stringify(arguments));
                });
            };
    
            this.getProfileInfo = function(data){
                if(data && data.hasOwnProperty('authResponse'))
                    that.token = data.authResponse.accessToken;
    
                
                facebookConnectPlugin.api('me/?fields=id,email,birthday,first_name,last_name,timezone,languages,name,gender,picture.width(1000)', [],
                    that.processLogin,
                    function () {
                        console.log(arguments);
                    });
            };
            
            this.quickLogin = function(data){

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
                    "sLoginBy": 'facebook',
                    "sEmail": jsonData.email,
                    "sLoginUID": jsonData.id,
                    'sAccessToken': that.token
                };
                
                console.log("processLogin(): ", JSON.stringify(jsonData));
                
                // Try to login
                $http2.post('user/login', postData)
                .success(function(resData) {
                    
                    if (resData == null) {
                        $modal.alert(gettextCatalog.getString('Can not connect to server'));
                        return;
                    }
                    
                    // login successful
                    if(resData.token != null){
                        that.quickLogin(resData);
                        return ;
                    }
                    
                    if(resData.error_step && resData.error_step == 'signup'){
                         // Account do not exists, signup new account
                        $modal.toast(gettextCatalog.getString('This account does not exists, please create a new one.'));
        
                        var sBirthday = jsonData.birthday;
        
                        if(sBirthday){
                            var ar = sBirthday.split('/');
                            // fb reurn 'mm/dd/yyyy'
                            sBirthday = ar[2] + '-' + ar[0] + '-'+ ar[1];
                        }
        
                        var updateData = {
                            sFullName: jsonData.name,
                            sBirthday: sBirthday,
                            sEmail: jsonData.email,
                            iGender: jsonData.gender == 'male' ? 1 : (jsonData.gender == 'female' ? 0 : 2),
                            sLoginUID: jsonData.id,
                            sAccessToken: that.token,
                            sSecretToken: '',
                            sFacebook: '',
                            sLoginBy: 'facebook'
                        };
        
                        // get profile picture
                        if (jsonData.picture) {
                            updateData.sUserImageUrl = jsonData.picture.data.url;
                        }
                    
                        userSignupData.update(updateData);
        
                        $location.url('app/signup',true);
    
                    }else if(resData.error_code){
                        $modal.alert(resData.error_message);
                        return ;
                    }
                   
                }).error(function(){
                    $modal.alert(gettextCatalog.getString('Can not process login by facebook'));
                });
            };
        }
        
        this.login = function(){
            this.facebookConnect = new FacebookConnect();
            this.facebookConnect.connect();
        };
        
    };
});


