app.service('api', ['$http', function($http){
    return {
        basePath: '/_src/op/_rc?',
        get : function(rtype, type, data){
            var url = this.basePath + 'rtype='+rtype + '&type='+type;
            var s = '';
            for(var k in data){
                if (data.hasOwnProperty(k)){
                    s += '&' + k + '=' + encodeURIComponent(data[k]);
                }
            }
            if (s.length > 0) url += s;
            console.debug('url for get: ' + url)
            return $http.get(url);
        },
        post: function(rtype, type, data){
            var url = this.basePath + 'rtype='+rtype + '&type='+type;
            console.debug('url for post: ' + url);
            return $http({
                method: 'POST',
                url : url,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                data : "json=" + JSON.stringify(data)
            });
        }
    };
}]);