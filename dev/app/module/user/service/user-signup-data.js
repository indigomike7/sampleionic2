define(function(){
    return function(){
        this.data = {};
        
        this.update = function(data){
            this.data = data;
        };
        
        this.extend = function(data){
            $.extend(this.data, data);
        };
        
        this.clean = function(){
            this.data = {};
        };
    };
});
