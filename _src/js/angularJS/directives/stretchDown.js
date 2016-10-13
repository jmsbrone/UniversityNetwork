app.directive('stretchDown', function(){
    return {
        restrict: 'A',
        link: function($scope, elem, attr){
            var newHeight = $(window).height() - $(elem).offset().top;
            $(elem).height(newHeight);
        }
    }
});