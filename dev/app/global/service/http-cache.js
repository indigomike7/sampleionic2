define([
],function(){
    return function($site){
        
        function _sha1(s){function U(a,b,c){while(0<c--)a.push(b)}function L(a,b){return(a<<b)|(a>>>(32-b))}function P(a,b,c){return a^b^c}function A(a,b){var c=(b&0xFFFF)+(a&0xFFFF),d=(b>>>16)+(a>>>16)+(c>>>16);return((d&0xFFFF)<<16)|(c&0xFFFF)}var B="0123456789abcdef";return(function(a){var c=[],d=a.length*4,e;for(var i=0;i<d;i++){e=a[i>>2]>>((3-(i%4))*8);c.push(B.charAt((e>>4)&0xF)+B.charAt(e&0xF))}return c.join('')}((function(a,b){var c,d,e,f,g,h=a.length,v=0x67452301,w=0xefcdab89,x=0x98badcfe,y=0x10325476,z=0xc3d2e1f0,M=[];U(M,0x5a827999,20);U(M,0x6ed9eba1,20);U(M,0x8f1bbcdc,20);U(M,0xca62c1d6,20);a[b>>5]|=0x80<<(24-(b%32));a[(((b+65)>>9)<<4)+15]=b;for(var i=0;i<h;i+=16){c=v;d=w;e=x;f=y;g=z;for(var j=0,O=[];j<80;j++){O[j]=j<16?a[j+i]:L(O[j-3]^O[j-8]^O[j-14]^O[j-16],1);var k=(function(a,b,c,d,e){var f=(e&0xFFFF)+(a&0xFFFF)+(b&0xFFFF)+(c&0xFFFF)+(d&0xFFFF),g=(e>>>16)+(a>>>16)+(b>>>16)+(c>>>16)+(d>>>16)+(f>>>16);return((g&0xFFFF)<<16)|(f&0xFFFF)})(j<20?(function(t,a,b){return(t&a)^(~t&b)}(d,e,f)):j<40?P(d,e,f):j<60?(function(t,a,b){return(t&a)^(t&b)^(a&b)}(d,e,f)):P(d,e,f),g,M[j],O[j],L(c,5));g=f;f=e;e=L(d,30);d=c;c=k}v=A(v,c);w=A(w,d);x=A(x,e);y=A(y,f);z=A(z,g)}return[v,w,x,y,z]}((function(t){var a=[],b=255,c=t.length*8;for(var i=0;i<c;i+=8){a[i>>5]|=(t.charCodeAt(i/8)&b)<<(24-(i%32))}return a}(s)).slice(),s.length*8))))};
        
        function CacheObj(name,options){
            /**
             * cache name
             */
            this.cacheName =  name;
            
            /**
             * cached data object
             */
            this.cachedData =  {};
            
            /**
             * cached options
             */
            this.options = $.extend({
                max: 600, // max item to store
                buffer: 50, // buffer items.
                gcInterval: 300*1e3, // check to clear old item after 300 seconds,
                backupInterval: 60*1e3, // update storate after 1 minute, 
            }, options);
            
            /**
             * get cached size
             */
            this.size =  function(){
                return Object.keys(this.cachedData.length);
            };
            
            /**
             * garbaga collector
             */
            this.gc =  function(){
                var toRemove  = this.size() - this.options.max + this.options.buffer;
                if(toRemove > 0){
                    var index = 0;
                    for(var key in this.cachedData){
                        if(++ index > toRemove) return;
                        delete(this.cachedData[key]);
                        $site.debug >2 && console.log('delete cache key', key);
                    }
                }
            };
            
            /**
             * get cached item
             */
            this.get =  function(key){
                
                if($site.isOnline){
                    return ;
                }
                
                $site.debug > 2 && console.log('try to get from cachedData');
                
                var cacheId = _sha1(key);
                
                // test the location for all of this data.
                if(this.cachedData.hasOwnProperty(cacheId)){
                    $site.debug > 2 && console.log('use data from cache',cacheId);
                    var encoded = this.cachedData[cacheId];
                    if(encoded){
                        return JSON.parse(encoded);
                    }
                }
            };
            
            /**
             * write cache item
             */
            this.put = function(key,value){
                
                var cacheId = _sha1(key);
                
                $site.debug > 2 && console.log('write cache', cacheId, (new Date().getTime()));
                
                this.cachedData[cacheId] =  JSON.stringify(value);
                
                // localStorage.setItem(cacheId, encoded);
                
                // return encoded;
            };
            
            /**
             * remove cached item
             */
            this.remove =  function(key){
                var cacheId  = _sha1(key);
                if(this.cachedData.hasOwnProperty(cacheId)){
                  delete(this.cachedData[cacheId]);  
                };
            };

            this.storeKey =  function(){
                return 'cache.store.' + this.cacheName;
            };      
            /**
             * store cached item
             */
            this.restore  = function(){
                this.cachedData =  JSON.parse(localStorage.getItem(this.storeKey()) || '{}') || {};
            };
            
            /**
             * backup for offline mode
             */
            this.backup  = function(){
                localStorage.setItem(this.storeKey(), JSON.stringify(this.cachedData));
            };
            
            /**
             * need to flush cached after create/edit an item of collections. 
             */
            this.flush = function(){
                this.cachedData =  {};
            };
            
            this.restore();
            
            var $this = this;
            
            window.setInterval(function(){$this.backup()}, this.options.backupInterval);
            
        };
        
        this.cacheObjs = {};
        
        this.getCache = function(cacheName){
            if(!this.cacheObjs.hasOwnProperty(cacheName)){
                this.createCache(cacheName);
            }
            return this.cacheObjs[cacheName];
        };
        
        this.createCache = function(cacheName, cacheOptions){
            this.cacheObjs[cacheName] =  new CacheObj(cacheName, cacheOptions);
        };

        this.clear = function(cacheName) {
            localStorage.removeItem('cache.store.' + cacheName);
        };
        
        // dump cache store
        this._dump = function(){
            for(var x in localStorage)console.log(x+"="+((localStorage[x].length * 2)/1024/1024).toFixed(2)+" MB");
        };

        if($site.debug >2){
            window._dumpCache = this._dump;
            window.clearCache = this.clear;
        }
        
    };
});
