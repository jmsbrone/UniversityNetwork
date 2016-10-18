app.service('storage', ['api', function(api){
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
    api.get('semester_mod', 'list',{}).then(function(response){
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
        api.get('group_req','subject_list', {
            semesterID: obj.semester.id
        }).then(function(response){
            console.debug(response);
            obj.program = response.data;
        }, function(response){
            console.debug(response);
        });
    }, function(response){
        console.debug(response);
    });
   
    return obj;
}]);