define([
    'settings/site'
], function(Site) {
    Site.buildQueryString = function(obj, prefix) {

    var str = [];
    for (var p in obj) {
      var k = prefix ? prefix + "[" + p + "]" : p,
        v = obj[k];
      str.push(angular.isObject(v) ? this.buildQueryString(v, k) : (k) + "=" + encodeURIComponent(v));
    }
    return str.join("&");
  };
  
  Site.getApiUrl = function(api, params) {
    var path = (typeof api == 'function') ? api() : api;

    if (arguments.length > 1)
      params = '?' + this.buildQueryString(params);
    else
      params = '';

    return Site.apiUrl + path + params;
  };

  Site.getSiteUrl = function(path, params) {

    if (arguments.length > 1)

      params = '?' + this.buildQueryString(params);
    else
      params = '';

    return Site.siteUrl + path + params;
  };

  Site.getCometchatApiUrl = function(api, params) {
    var path = (typeof api == 'function') ? api() : api;

    if (arguments.length > 1)
      params = '&' + this.buildQueryString(params);
    else
      params = '';

    return Site.cometchatApiUrl + path + params;
  };
  
  return Site;
  
});