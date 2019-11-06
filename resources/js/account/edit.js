export class Edit {
    constructor() {
        this.initListeners()
    }

    initListeners()
    {
        // Update shipping fields according with billing fields values
        $('#billing_lane, #billing_postal_code, #billing_city').on('keyup change', (event) => {
            this.changeShippingFieldsValue(event)
        })

        $('#billing_country').on('changed.bs.select change', (event) => {
            this.changeShippingFieldsValue(event, true)
        })
    }

    changeShippingFieldsValue(event, isSelectField) {
        let elementId = $(event.currentTarget).attr('id')
        let value = $(event.currentTarget).val()

        let elementToUpdateId = elementId.replace('billing', 'shipping')
        $(`#${elementToUpdateId}`).val(value)
        $(`#${elementToUpdateId}`).parents('.form-line:first').addClass('focused')

        if (isSelectField) {
            $(`#${elementToUpdateId}`).selectpicker('refresh')
        }
    }
}