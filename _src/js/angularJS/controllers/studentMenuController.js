app.controller('studentMenuController', ['$scope', '$state', function($scope, $state){
    $scope.menuItems = [
        {
            text: 'Профиль',
            icon: 'ic_face_black_24px',
            state: 'profile'
        },
        {
            text: 'Новости',
            icon: 'ic_library_books_black_24px',
            state: 'news'
        },
        {
            text: 'Журнал',
            icon: 'ic_speaker_notes_black_24px',
            state: 'group'
        },
        {
            text: 'Расписание',
            icon: 'ic_schedule_black_24px',
            state: 'schedule'
        },
        {
            text: 'Предметы',
            icon: 'ic_subject_black_24px',
            state: 'subjects'
        }
    ];
    
    $scope.setActiveItem = function(item){
        $scope.activeMenuItem = item;
        $state.go('app.student.'+item.state);
    };
    if ($state.current.name == 'app.student'){
        $state.go('app.student.profile');
        $scope.activeMenuItem = $scope.menuItems[0];
    } else {
        var state_parts = $state.current.name.split('.');
        var state = state_parts[state_parts.length - 1];
        for(i=0;i<$scope.menuItems.length;++i){
            if ($scope.menuItems[i].state == state) {
                $scope.activeMenuItem = $scope.menuItems[i];
                break;
            }
        }
    }
}]);