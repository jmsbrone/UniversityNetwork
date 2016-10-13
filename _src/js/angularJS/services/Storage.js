app.service('storage', ['api', function(api){
    var obj = {
        semester: null
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
        var start_day = new Date(obj.semester.startTimestamp.valueOf());
        start_day.setDate(start_day.getDate() - start_day.getDay());
        now.setDate(now.getDate() - now.getDay());
        obj.currentWeek = Math.ceil((now.valueOf() - start_day.valueOf()) / (1000 * 3600 * 24 * 7));
        console.debug('current week is ' + obj.currentWeek);
    }, function(response){
        console.debug(response);
    });
    return obj;
}]);