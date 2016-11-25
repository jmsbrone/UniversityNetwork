app.service('api', ['$http', function($http){
    return {
        basePath: '/_src/op/_rc?',
        get : function(rtype, type, data){
            var url = this.basePath + 'rtype='+rtype + '&type='+type;
            var s = '';
            for(var k in data){
                if (data.hasOwnProperty(k) && data[k]){
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
        },
        upload: function(rtype, type, data){
            var url = this.basePath + 'rtype='+rtype + '&type='+type;
            console.debug('url for upload: ' + url);
            var form = new FormData();
            for(var key in data){
                if (key != 'files'){
                    form.append(key, data[key]);
                    continue;
                }
                var c = 0;
                for(i=0;i<data['files'].length;++i){
                    form.append('file'+c++, data['files'][i]);
                }
            }
            return $http({
                method: 'POST',
                url : url,
                headers: { 
                    'Content-Type': undefined 
                },
                transformRequest: angular.identity,
                data : form
            });
        }
    };
}]);