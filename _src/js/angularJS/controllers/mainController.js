app.controller('mainController', ['$scope', '$state', function($scope, $state){
    $scope.checkState = function(st){
        if ($state.current.name.search(st) > 0) return true;
        return false;
    };
    
    $scope.state = $state.current;
}]);