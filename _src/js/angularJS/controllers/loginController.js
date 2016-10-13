app.controller('loginController', ['$scope', 'api', '$state', function($scope, api, $state){
    $scope.confirm = function(){
        api.post('auth_req', 'login', {
                login: $scope.login,
                psw: $scope.psw
        })
        .then(function(response){
            console.debug(response);
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
            console.debug(response);
            alert('failed');
        });
    }
}]);