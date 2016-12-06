app.service('storage', ['api', '$timeout', '$state', function(api, $timeout, $state){
    var obj = {
        semester: null,
        getWeek: function(from){
            if (!from) {
                now = new Date();
            } else {
                now = new Date(from.valueOf());
            }
            
            var start_day = new Date(this.semester.startTimestamp.valueOf());
            start_day.setDate(start_day.getDate() - start_day.getDay() + 1);
            now.setDate(now.getDate() - (now.getDay() == 0 ? 7 : now.getDay()) + 1);
            now.setHours(0);
            now.setMinutes(0);
            now.setSeconds(0);
            now.setMilliseconds(0);
            return (now.valueOf() - start_day.valueOf()) / (1000 * 3600 * 24 * 7) + 1;
        }
    };
    var updateSubjectList = function(){
        api.post('group_req','subject_list', {
            semesterID: obj.semester.id
        },function(response){
            console.debug(response);
            obj.program = response.data;
        }, function(response){
            console.debug(response);
            $timeout(updateSubjectList, 500);
        });
    };
    var updateSchedule = function(){
        api.post('schedule_mod', 'list', {},function(response){
            obj.rules = response.data;
        }, function(response){
            console.debug(response);
            $timeout(updateSchedule, 500);
        });
    }
    var updateFn = function(){
        if ($state.current.name.search("app.student") == -1){
            $timeout(updateFn, 500);
            return;
        }
        api.post('semester_mod', 'list',{},function(response){
            console.debug(response);
            var now = new Date();
            for(i=0;i<response.data.length;++i){
                var s = response.data[i];
                s.startTimestamp = new Date(s.startTimestamp * 1000);
                s.endTimestamp = new Date(s.endTimestamp * 1000);
                if (s.startTimestamp < now && s.endTimestamp > now) {
                    obj.semester = s;
                    break;
                }
            }
            if (!obj.semester) return;
            obj.currentWeek = obj.getWeek();
            updateSubjectList();
            updateSchedule();
        }, function(response){
            console.debug(response);
            $timeout(updateFn, 500);
        });
    }
    updateFn();
    return obj;
}]);