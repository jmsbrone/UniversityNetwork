app.controller('studentMenuController', ['$scope', '$state', '$timeout', function($scope, $state, $timeout){
    $scope.menuItems = [
        /*{
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
        */{
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
        $state.go('app.student.schedule');
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
    
    var menuFloatCount = 0;
    
    return;
    $(window).scroll(function(){
        menuFloatCount++;
        $timeout(function(){
            if (--menuFloatCount == 0){
                var menu = $('#main-menu');
                var diff = window.pageYOffset - menu.offset().top;
                if (menu.is(':animated')) return;
                menu.animate({
                    'padding-top': diff > 0 ? diff : 0
                }, 250, function(){
                    console.debug('complete');
                });
            }
        }, 100);
    });
}]);