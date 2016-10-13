app.controller('registerController', ['$scope', 'api', '$state', function($scope, api, $state){
    if ($state.current.name == 'app.register.student' || $state.current.name == 'app.register.manager'){
        $scope.isRegisterInput = false;
        $state.go('app.register');
    }
    $scope.managerRegister = false;
    $scope.studentRegister = false;
    
    $scope.checkHash = function(){
        api.get('auth_req','invite_check', {
            hash : $scope.hash
        })
        .then(function(response){
            console.debug(response);
            $scope.user = response.data;
            $scope.managerRegister = false;
            $scope.studentRegister = false;
            switch($scope.user.type){
                case 'manager':
                    $scope.managerRegister = true;
                    break;
                case 'student':
                    $scope.studentRegister = true;
                    break;
            }
            $scope.isRegisterInput = true;
        }, function(response){
            console.debug(response);
        });
    }
    
    $scope.register = function(){
        if ($scope.login.length < 4 || $scope.psw.length < 4){
            alert('Not enough chars');
            return;
        }
        console.debug('registering...');
        api.post($scope.user.type + '_mod', 'register', {
            login: $scope.login,
            psw: $scope.psw,
            hash: $scope.hash
        }).then(function(response){
            console.debug(response);
            switch($scope.user.type){
                case 'manager':
                    $state.go('app.manager');
                    break;
                case 'student':
                    $state.go('app.student');
                    break;
            }
        }, function(response){
            console.debug(response);
        });
    }
}]);