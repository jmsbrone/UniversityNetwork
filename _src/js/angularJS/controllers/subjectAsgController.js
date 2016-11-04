app.controller('subjectAsgController', ['$scope', 'api', '$mdDialog', 'storage', '$timeout', function($scope, api, $mdDialog, storage, $timeout){
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