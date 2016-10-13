app.controller('adminController', ['$scope', '$http', '$state', function($scope, $http, $state){
    // List of managers
    $http.get('/op/_rc?rtype=manager_mod&type=list')
    .then(function(response){
        console.debug(response);
        $scope.list = response.data;
    }, function(response){
        console.debug('getting manager list failed: ');
        console.debug(response);
    });
    
    $scope.add = function(){
        if ($scope.name.length < 4) return;
        $http.get('/op/_rc?rtype=manager_mod&type=invite&name='+encodeURI($scope.name))
        .then(function(response){
            console.debug(response);
            $scope.list.push(response.data);
            $scope.addForm = false;
        }, function(response){
            console.debug('adding manager failed: ');
            console.debug(response);
        });
    }
}]);