app.controller('registerController', ['$scope', 'api', '$state', '$mdDialog', function($scope, api, $state, $mdDialog){
    $scope.managerRegister = false;
    $scope.studentRegister = false;
    $scope.registerStage = 0;
    
    /*var prompt = $mdDialog.prompt()
        .title('Требуется код регистрации')
        .textContent('Код является персональным и используется для регистрации в системе.')
        .placeholder('148adf')
        .ariaLabel('hash')
        .ok('Подтвердить')
        .cancel('Закрыть');
    
    $mdDialog.show(prompt,function(result){
        $scope.hash = result;
        api.post('auth_req','invite_check', {
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
    });*/
    
    $scope.checkHash = function(event){
        if (event && event.keyCode !=13) return;
        
        $scope.httpPending = true;
        api.post('auth_req', 'invite_check', {
            hash: $scope.inviteHash
        }, function(response){
            $scope.registerStage = 1;
            $scope.httpPending = false;
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
            $scope.hashError = true;
            $scope.httpPending = false;
        });
    };
    
    $scope.confirm = function(event){
        if (event && event.keyCode != 13) return;
        if ($scope.registerForm.$invalid) {
            $scope.error = 'Поля не заполнены';
            return;
        }
        
        if ($scope.psw != $scope.psw_copy){
            $scope.error = 'Пароли не совпадают';
            return;
        }
        
        $scope.httpPending = true;
        api.post($scope.user.type + '_mod', 'register', {
            login: $scope.login,
            psw: $scope.psw,
            hash: $scope.inviteHash
        },function(response){
            $scope.httpPending = false;
            switch($scope.user.type){
                case 'manager':
                    $state.go('app.manager');
                    break;
                case 'student':
                    $state.go('app.student');
                    break;
            }
        }, function(response){
            $scope.error = 'Ошибка регистрации: ' + response.data;
            $scope.httpPending = false;
        });
    }
}]);