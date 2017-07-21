define(function(){
    return {
        isUrl: function(string){
            var re = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/i;
            return re.test(string);
        },
        isEmail: function(string){
            var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
             return re.test(string);
        },
        isEmpty: function(string){
            return string == undefined || string.trim() == '';
        },
        isNotEmpty: function(string){
            return string != undefined && string.trim() != '';
        },
        isVimeoVideoUrl: function(string){
            var re = /\/\/(www\.)?vimeo.com\/(\d+)($|\/)/i;
            return re.test(string);
        },
        isYoutubeVideoUrl: function(string){
            var re = /^(?:https?:\/\/)?(?:www\.)?(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))((\w|-){11})(?:\S+)?$/i;
            return re.test(string);
        }
    };
});
