require('../../vendor/riktar/jkanban/jkanban')
require('materialize-css')

export class Kanban {
    constructor() {
        this.initListeners()
        this.getBoards()
    }

    initListeners()
    {
        $('#closing_date, #assigned_user').on('change', () => {
            this.getBoards()
        })
    }

    getBoards() {
        let closing_date = $('#closing_date').val()
        let user = $('#assigned_user').val()

        $('#kanban-board').children().remove()

        $('#kanban-loader').show()

        let url = $('meta[name="kanban-url"]').attr('content')
        url += `?closing_date=${closing_date}&user=${user}`

        $.get(url)
            .then((response) => {
                this.boards = response
                this.makeKanban()
                this.calculateTotals()
                $('#kanban-loader').hide()
            })
    }

    makeKanban() {
        const domainSlug = $('meta[name="domain"]').attr('content')
        const moduleName = $('meta[name="module"]').attr('content')

        new jKanban({
            element: '#kanban-board',
            gutter: '5px',
            widthBoard: '260px',
            dragBoards: false,
            click: (el) => {
                let url = $('meta[name="kanban-opportunity-url"]').attr("content")
                url += `?id=${el.dataset.eid}`
                document.location.href = url
            },
            dropEl: (el, target, source, sibling) => {
                let id = el.dataset.eid
                this.changeItemStage(el, id, target)
            },

            addItemButton: false,
            boards: this.boards
        })

        $("[data-tooltip").tooltip()
    }

    changeItemStage(el, id, target) {
        const domainSlug = $('meta[name="domain"]').attr('content')
        const moduleName = $('meta[name="module"]').attr('content')

        let newPhase = $(target).parents('.kanban-board:first').data('id')

        let url = $('meta[name="kanban-update-phase-url"]').attr("content")
        url += `?id=${id}&value=${newPhase}`

        $.get(url)
            .then((response) => {
                this.calculateTotals()
                // $('.step', el).text(uctrans.trans('opportunity.' + response.step, 'opportunity')) //TODO: translate
            })
            .fail(() => {
                swal(uctrans.trans('uccello::default.dialog.error.title'), '', 'error')
            })
    }

    calculateTotals() {
        $('.kanban-board').each((index, el) => {
            let subtotal = 0
            $('.amount', el).each(function() {
                subtotal += parseFloat($(this).data('amount'))
            })
            $(".total-amount", el).text(subtotal + ' €')
        })
    }
}