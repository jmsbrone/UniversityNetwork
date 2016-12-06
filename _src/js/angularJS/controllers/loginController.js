app.controller('loginController', ['$scope', 'api', '$state', '$timeout', function($scope, api, $state, $timeout){
    $scope.confirm = function(event){
        if (event){
            if (event.keyCode != 13) return;
        }
        $scope.httpPending = true;
        api.post('auth_req', 'login', {
                login: $scope.login,
                psw: $scope.psw
        }, function(response){
            //console.debug('request completed in ' + response.config.requestTime);
            $scope.httpPending = false;
            switch(response.data.accountType){
                case 'admin':
                    $state.go('app.admin');
                    break;
                case 'manager':
                    $state.go('app.manager');
                    break;
                case 'student': case 'president':
                    $state.go('app.student');
                    break;
            }
        }, function(response){
            $scope.httpPending = false;
            $scope.error = response.data;
        });
    };
}]);