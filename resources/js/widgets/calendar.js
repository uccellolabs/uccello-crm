// import 'bootstrap' // Mandatory to user $.modal()
import 'fullcalendar'
import allLocales from 'fullcalendar/dist/locale-all'

export class Calendar {
    constructor() {
        this.initFullCalendar()
    }

    initFullCalendar() {
        this.calendar = $('#calendar').fullCalendar({
            header: {
                left:   'title',
                center: '',
                right:  'month,agendaWeek,agendaDay,prev,next',
            },
            height: $(document).height() - 320,
            defaultView: 'agendaWeek',
            minTime: '07:00:00',
            maxTime: '21:00:00',
            locales: allLocales,
            locale: $('html').attr('lang'),
            timeFormat: 'H:mm',
            groupByResource: true,
            editable: false,
            handleWindowResize: true,
            weekends: false, // Hide weekends
            displayEventTime: true, // Display event time
            selectable: false,
            selectHelper: true,
            eventSources: [
                $('meta[name="calendar-events-url"]').attr('content'),
            ],
        })
    }
}

new Calendar()