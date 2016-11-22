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
    
    $scope.deleteAsg = function(type, asg){
        switch(type){
            case 'lab':
                api.get('lab_mod', 'delete', {
                    id: asg.id
                }).then(function(response){
                    $scope.labs = flib.eject($scope.labs, asg);
                }, function(response){
                    console.debug(response);
                });
                break;
        }
    };
    
    $scope.updateStatus = function(lab){
        if (!lab._count) {
            lab._count = 0;
        }
        lab._count++;
        $timeout(function(){
            if (--lab._count != 0) return;
            api.get('lab_mod', lab.completed ? 'set' : 'unset',{
                asgID: lab.id
            }).then(function(response){
                
            }, function(response){
                console.debug(response);
            });
        }, 500);
    };
}]);