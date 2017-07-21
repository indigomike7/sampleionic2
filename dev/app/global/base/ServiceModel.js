define(function(){
    
    function ModelFactoryProvider(){
        
        this.vars = {};
        
        this.register = function(type, factory){
            this.vars[type] = factory;
        };
        
        this.get = function(){
            
        };
    }
    
    return new ModelProvider();
});
