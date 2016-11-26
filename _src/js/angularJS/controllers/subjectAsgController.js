app.controller('subjectAsgController', ['$scope', 'api', 'flib', '$mdDialog', 'storage', '$timeout', function($scope, api, flib, $mdDialog, storage, $timeout){
    $scope.close = function(){
        $mdDialog.hide();
    };
    
    $scope.program = storage.activeProgram;

    api.get('lab_mod', 'list', {
        programID: $scope.program.id
    }).then(function(response){
        console.debug(response);
        $scope.labs = response.data;
        for(i=0;i<$scope.labs.length;++i){
            $scope.labs[i].completed = $scope.labs[i].completed == '1';
        }
    }, function(response){
        console.debug(response);
    });
    
    api.get('cg_mod', 'list', {
        programID: $scope.program.id
    }).then(function(response){
        console.debug(response);
        $scope.cgs = response.data;
        for(i=0;i<$scope.cgs.length;++i){
            $scope.cgs[i].completed = $scope.cgs[i].completed == '1';
        }
    }, function(response){
        console.debug(response);
    });
    
    api.get('kr_mod', 'list', {
        programID: $scope.program.id
    }).then(function(response){
        console.debug(response);
        $scope.krs = response.data;
        for(i=0;i<$scope.krs.length;++i){
            $scope.krs[i].completed = $scope.krs[i].completed == '1';
        }
    }, function(response){
        console.debug(response);
    });
    
    api.get('tests_mod', 'list', {
        programID: $scope.program.id
    }).then(function(response){
        console.debug(response);
        $scope.tests = response.data;
        for(i=0;i<$scope.tests.length;++i){
            $scope.tests[i].completed = $scope.tests[i].completed == '1';
        }
    }, function(response){
        console.debug(response);
    });
    
    $scope.deleteAsgFn = function(type, asg){
        var returnArray = null;
        var requestGroup = '';
        
        switch(type){
            case 'lab':
                requestGroup = 'lab';
                returnArray = 'labs';
                break;
            case 'cg':
                requestGroup = 'cg';
                returnArray = 'cgs';
                break;
            case 'kr':
                requestGroup = 'kr';
                returnArray = 'kr';
                break;
            case 'test':
                requestGroup = 'tests';
                returnArray = 'tests';
                break;
        }
        api.get(requestGroup + '_mod', 'delete', {
            id: asg.id
        }).then(function(response){
            $scope[returnArray] = flib.eject($scope[returnArray], asg);
        }, function(response){
            console.debug(response);
        });
    };
    
   $scope.updateStatus = function(type, asg){
        if (!asg._count) {
            asg._count = 0;
        }
        asg._count++;
        $timeout(function(){
            if (--asg._count != 0) return;
            api.get(type+'_mod', asg.completed ? 'set' : 'unset',{
                asgID: asg.id
            }).then(function(response){
                
            }, function(response){
                console.debug(response);
            });
        }, 500);
    };
}]);