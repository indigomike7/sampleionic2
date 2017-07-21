define([
    'angular',
    'global/site'
],function(angular,Site){
    
    /**
    * 
    * @param int iItemId
    * @return true|false
    */
   Array.prototype.deleteItem = function(iItemId){
     for(var i =0;i< this.length; ++i){
         if(this[i].getId() == iItemId){
            this.splice(i, 1);
            return true;             
         }
     }
     return false;
   };
   
   /**
    * 
    * @param int iItemId
    * @return {Object} | false
    */
   Array.prototype.findItem = function(iItemId){
    for(var i =0;i< this.length; ++i){
         if(this[i].getId() == iItemId){
            return this[i];         
         }
     }
     return false;
   };
   
   /**
    * 
    * @param int iItemId
    * @return int|false
    */
   Array.prototype.findItemIndex = function(iItemId){
       for(var i =0;i< this.length; ++i){
         if(this[i].getId() == iItemId){
            return i;      
         }
     }
     return false;
   };
   
   
   String.prototype.replaceAll = function(find,replace){
     return this.replace(new RegExp(find, 'g'), replace);     
   };
   
   
   $.test = function(api, data){
       
       $.ajaxSetup({
           headers: {
               token: localStorage.getItem('token'),
           }
       });
       
       $.getJSON(Site.getApiUrl(api), data)
       .success(function(data){
           console.log(data);
       })
       .error(function(){
           console.log(arguments);
       });
   };   
});
