app.controller('headerController', ['$scope', 'api', '$state', function($scope, api, $state){
    $scope.visible = function() {
        switch($state.current.name){
            case 'app.main': case 'app.login': case 'app.register':
                return false;
                break;
            default: return true; break;
        }
    }
    $scope.signOut = function(){
        $state.go('app.main');
        api.get('auth_req', 'signout', {}).then(null, null);
    }
}]);