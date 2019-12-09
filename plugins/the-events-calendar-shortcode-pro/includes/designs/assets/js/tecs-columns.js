function tecsDestroyColumns(element) {
    element.masonry('destroy');
}
function tecsInitializeColumns(element) {
    element.imagesLoaded( function() {
        element.masonry({
            itemSelector: '.ecs-event',
            columnWidth: '.ecs-grid-sizer',
            percentPosition: true,
            //fitWidth: true,
            gutter: '.ecs-gutter-sizer'
        });
    });
}

(function($){
    $(document).ready(function(){
        var $ecsGrid = $('.ecs-grid');
        tecsInitializeColumns($ecsGrid);
    });
})(jQuery);