import {Datatable} from 'uccello-datatable'

export class Tasks {
    constructor() {
        this.initDatatable()
    }

    initDatatable() {
        this.datatable = new Datatable()
        this.datatable.init($('table#tasks-widget'))

        this.initialContentUrl = $('table#tasks-widget').data('content-url')
        this.userId = 'me'
        this.dateStart = ''
        this.dateEnd = ''

        this.refreshDatatable()

        $('#tasks-user-id').on('change', (ev) => {
            this.userId = $(ev.currentTarget).val()
            this.refreshDatatable()
        })

        $('#tasks-period').on('change', (ev) => {
            var value = $(ev.currentTarget).val()
            switch(value) {
                case 'today':
                    this.dateStart = this.dateEnd = moment().format('YYYY-MM-DD')
                break

                case 'month':
                    this.dateStart = moment().startOf('month').format('YYYY-MM-DD')
                    this.dateEnd = moment().endOf('month').format('YYYY-MM-DD')
                break

                case 'week':
                default:
                    this.dateStart = moment().lang($('html').attr('lang')).startOf('week').format('YYYY-MM-DD')
                    this.dateEnd = moment().lang($('html').attr('lang')).endOf('week').format('YYYY-MM-DD')
                break
            }

            this.refreshDatatable()
        })
    }

    refreshDatatable() {
        let newUrl = `${this.initialContentUrl}?start=${this.dateStart}&end=${this.dateEnd}&user_id=${this.userId}`
        $('table#tasks-widget').attr('data-content-url', newUrl)
        this.datatable.makeQuery()
    }
}

new Tasks()