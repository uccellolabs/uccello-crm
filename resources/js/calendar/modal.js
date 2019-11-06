import 'devbridge-autocomplete'
import 'materialize-css'

export class CalendarModal {
    constructor() {
        this.descriptionEditor = document.getElementById('addEventModal').getElementsByClassName('trumbowyg-editor')[0]
        this.initAccountField()
        this.initRelatedRecordListener()
        this.initModalOpenListener()
    }

    initAccountField() {
        let el = $('#addEventModal #account')
        el.devbridgeAutocomplete({
            serviceUrl: el.attr('data-url'),
            type: 'get',
            paramName: 'q',
            onSearchStart: () => {
                el.removeClass('invalid')
            },
            onSearchComplete: (query, suggestions) => {
                if (suggestions.length === 0) {
                    el.addClass('invalid')
                }
            },
            onSelect: (record) => {
                $('#addEventModal #subject').val(record.value).parents('.input-field:first').find('label').addClass('active')
                $('#addEventModal #moduleName').val(record.data.module)
                $('#addEventModal #recordId').val(record.data.id)

                this.getRelatedRecords(record.data.id)
            },
            showNoSuggestionNotice: false,
            transformResult: function(response, originalQuery) {
                let results = {
                    suggestions: []
                }

                let originalResults = JSON.parse(response)
                for (let result of originalResults) {
                    results.suggestions.push({
                        value: result.title,
                        data: {
                            module: result.type,
                            id: result.searchable.id
                        }
                    })
                }

                return results
            }
        })
    }

    initRelatedRecordListener() {
        $('#addEventModal #related_record').on('change', (event) => {
            let newSubject = ''
            let relatedRecordData = $(event.currentTarget).val().split(',')
            if (relatedRecordData.length === 2) {
                $('#addEventModal #moduleName').val(relatedRecordData[0])
                $('#addEventModal #recordId').val(relatedRecordData[1])
                newSubject = $('#addEventModal #account').val() + ' - ' + $('option:selected', event.currentTarget).text()

            } else {
                $('#addEventModal #moduleName').val('')
                $('#addEventModal #recordId').val('')
                newSubject = $('#addEventModal #account').val()
            }

            $('#addEventModal #subject').val(newSubject)
        })
    }

    getRelatedRecords(recordId) {
        let url = $('meta[name="related-records-url"]').attr('content')

        $.get(url, { id: recordId }).then((data) => {
            let htmlContacts = ''
            let htmlOpportunities = ''
            for (let record of data.contacts) {
                htmlContacts += `<option value="${record.module},${record.id}">${record.label}</option>`
            }

            for (let record of data.opportunities) {
                htmlOpportunities += `<option value="${record.module},${record.id}">${record.label}</option>`
            }

            let address = `${data.record.billing_lane} ${data.record.billing_postal_code} ${data.record.billing_city}`
            address = _.trim(address.replace('null', ''))
            if (address) {
                $('#addEventModal #location').val(address).parent().find('label').addClass('active')
            }

            let phone = _.trim(data.record.phone)
            if (phone) {
                this.descriptionEditor.dispatchEvent(new CustomEvent('update.html', { detail: 'Tel: ' + phone }))
            }

            let html = `<option value="">---</option><optgroup label="${uctrans.trans('contact.contact')}">${htmlContacts}</optgroup><optgroup label="${uctrans.trans('opportunity.opportunity')}">${htmlOpportunities}</optgroup>`

            $('#addEventModal #related_record').html(html)
            $('#addEventModal #related_record').formSelect()
            $('#addEventModal #related_record_container').show()
        })
    }

    initModalOpenListener() {
        document.getElementById('addEventModal').addEventListener('modal.open', (event) => {
            $('#addEventModal #related_record_container').hide()
        })
    }
}

new CalendarModal()