app.service('api', ['$http', '$timeout', function($http, $timeout){
    var obj = {
        basePath: '/_src/op/_rc?',
        pending: false,
        queue: []
    };
    obj['$serverPerformanceCallback'] = function(data){
        $http({
            method: 'POST',
            url : this.basePath + 'rtype=request_time&type=client',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            data : "json=" + JSON.stringify(data)
        }).then();
    };
    obj['$nextRequest'] = function(){
        if (this.pending) return;
        
        if (this.queue.length == 0) return;
        
        var request = this.queue.shift();
        this.pending = true;
        this.successFn = request.successFn;
        this.errorFn = request.errorFn;
        this.activeRequest = request;
        
        $http(request.config)
            .then(function(response){
                this.pending = false;

                // Server callback
                this.$serverPerformanceCallback({
                    request: response.config.requestString,
                    startTime: response.config.requestTimestamp,
                    endTime: response.config.responseTimestamp
                });
                
                if (this.successFn)
                    this.successFn(response);
            
                this.$nextRequest();
            }.bind(this), function(response){
                if (response.status == 503){
                    // case of mod_evasive blocking 
                    // due to high frequency of requests
                    //
                    // wait 3s
                    this.queue.unshift(this.activeRequest);
                    $timeout(this.$nextRequest, 3000);
                    return;
                }
                if (this.errorFn)
                    this.errorFn(response);
                this.pending = false;
                this.$nextRequest();
            }.bind(this));
        
    }.bind(obj);
    
    obj['$pushRequest'] = function(config, successFn, errorFn){
        this.queue.push({
            config: config,
            successFn: successFn,
            errorFn: errorFn
        });
        obj['$nextRequest']();
    }.bind(obj);
    
    obj['post'] = function(rtype, type, data, successFn, errorFn){
        this.$pushRequest({
            method: 'POST',
            url : this.basePath + 'rtype='+rtype + '&type='+type,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            data : "json=" + JSON.stringify(data),
            requestString: rtype + '_' + type
        }, successFn, errorFn);
    }.bind(obj);
    
    obj['upload'] = function(rtype, type, data, successFn, errorFn){
        var form = new FormData();
        for(var key in data){
            if (key != 'files'){
                form.append(key, data[key]);
                continue;
            }
            var c = 0;
            for(i=0;i<data['files'].length;++i){
                form.append('file'+c, data['files'][i]);
                form.append('filename'+c, data['filenames'][i].name);
                c++;
            }
        }

        this.$pushRequest({
            method: 'POST',
            url : this.basePath + 'rtype='+rtype + '&type='+type,
            headers: { 
                'Content-Type': undefined 
            },
            transformRequest: angular.identity,
            data : form
        }, successFn, errorFn);
    }.bind(this)
    
    return obj;
}]);