app.controller('registerController', ['$scope', 'api', '$state', '$mdDialog', function($scope, api, $state, $mdDialog){
    $scope.managerRegister = false;
    $scope.studentRegister = false;
    
    var prompt = $mdDialog.prompt()
        .title('Требуется код регистрации')
        .textContent('Код является персональным и используется для регистрации в системе.')
        .placeholder('')
        .ariaLabel('hash')
        .ok('Подтвердить')
        .cancel('Закрыть');
    
    $mdDialog.show(prompt).then(function(result){
        $scope.hash = result;
        api.get('auth_req','invite_check', {
            hash : result
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
        }, function(response){
            console.debug(response);
            $state.go('app.main');
            $mdDialog.show(
                $mdDialog.alert()
                    .clickOutsideToClose(true)
                    .title('Неверный код регистрации')
                    .textContent('Код регистрации не найден либо уже использован.')
                    .ok('Закрыть')
            );
        });
    }, function(){
        $state.go('app.main');
    });
    
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
    };
}]);