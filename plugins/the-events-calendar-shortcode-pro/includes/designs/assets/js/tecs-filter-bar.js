(function($){
    $(document).ready(function(){
        function tecsGetCalendarId(element) {
            return parseInt(element.attr('data-calendar-id'));
        }
        function tecsGetDesign(element) {
            return element.attr('data-design');
        }
        function tecsChangeSetting(calendarId, design, setting, value) {
            if (design === 'calendar') {
                tecEventCalendarSettings['ecs-calendar-' + calendarId][setting] = value ? value : '';
            } else {
                tecEventSettings[design][calendarId][setting] = value ? value : '';
            }
        }
        function tecsRefreshCalendar(calendarId, design) {
            if (design === 'calendar') {
                $('#ecs-calendar-' + calendarId).fullCalendar( 'refetchEvents' );
            } else {
                $.ajax({
                    url: tecEventSettings[design][calendarId]['ajaxurl'],
                    type: 'POST',
                    data: tecEventSettings[design][calendarId],
                    success: function(data) {
                        try {
                            var elem = $('.ecs-events.' + design + '-' + calendarId);
                            elem.html(data.html);
                            if (design === 'columns') {
                                tecsDestroyColumns(elem);
                                tecsInitializeColumns(elem);
                            }
                        } catch (e) {
                            console.log(e);
                        }
                    }
                });
            }
        }

        var tecsFilterBarItems = [
            {'name': 'category', 'setting': 'cat'},
            {'name': 'venue', 'setting': 'venue_id'},
            {'name': 'state', 'setting': 'state'},
            {'name': 'country', 'setting': 'country'}
        ];
        tecsFilterBarItems.forEach(function(item) {
            var itemSelect = $('.ecs-' + item.name + '-select');
            if (itemSelect.length) {
                itemSelect.select2({
                    placeholder: '',
                    allowClear: true
                });
                itemSelect.on('change', function(element) {
                    var calendarId = tecsGetCalendarId($(element.currentTarget));
                    var design = tecsGetDesign($(element.currentTarget));
                    tecsChangeSetting(calendarId, design, item.setting, $(this).val());
                    tecsRefreshCalendar(calendarId, design);
                });
            }
        });
    });
})(jQuery);