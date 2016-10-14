app.filter('rdate', function(){
    return function(input, format){
        var months = ['Январь', 'Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'];
        var months_short = ['Янв','Фев','Март','Апр','Май','Июнь','Июль','Авг','Сен','Окт','Нояб','Дек'];
        var wdays = ['Воскресенье', 'Понедельник','Вторник','Среда','Четверг','Пятница','Суббота'];
        var wdays_short = ['Вс', 'Пн','Вт','Ср','Чт','Пт','Сб'];
        
        try {
            if (!format){
                format = 'dd-mm-yyyy';
            }
            // yy - 2-digit year
            // yyyy - 4-digit year
            // mm - 2-digit month
            // dd - 2-digit day
            // wd - short week text
            // WD - long week text
            // m - short month text
            // M - long month text
            return format
                .replace('yyyy', input.getFullYear())
                .replace('yy', input.getYear())
                .replace('mm', input.getMonth() + 1)
                .replace('m', months_short[input.getMonth()])
                .replace('M', months[input.getMonth()])
                .replace('dd', input.getDate())
                .replace('wd', wdays_short[input.getDay()])
                .replace('WD', wdays[input.getDay()])
                ;
        }catch(err){
            console.debug('invalid date');
            return '#';
        }
    }
});