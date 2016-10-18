app.controller('studentSubjectsController', ['$scope', 'api', 'storage', '$timeout', function($scope, api, storage, $timeout){
    var updateFn = function(){
        if (!storage.program) {
            $timeout(updateFn, 500);
            return;
        }
        $scope.list = storage.program;
    };
    $timeout(updateFn, 500);
}]);