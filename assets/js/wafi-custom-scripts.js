jQuery(document).ready(function ($) {
    updateButtons();
    $(document.body).on('updated_checkout', function () {
        $('label[for="payment_method_wafi"]').css({
            'display': 'flex',
            'align-items': 'center',
        });
        $('input[name="payment_method"]').on('change', function () {
            updateButtons();
        });
        $('label[for="payment_method_wafi"]').on('click', function () {
            console.log("other button clicked!");
            return
        });
        $('.wafi-learn-more-open').on('click', function () {

            $('.learn-more-container').css({
                'display': 'flex',


            });
        });
        $('.wafi-learn-more-close-btn').on('click', function () {

            $('.learn-more-container').css({
                'display': 'none',


            });
        });
        $('.wafi-learn-more-close').on('click', function () {

            $('.learn-more-container').css({
                'display': 'none',
            });


        });


        $('input[name="payment_method"]:checked').trigger('change');


    });

    $(document.body).on('click', '#wafiCustomPlaceOrder', function (e) {
        e.preventDefault();
        console.log("Custom button clicked!");

        // Assuming that your form has the class 'checkout', you can submit it
        $('form.checkout').submit();
    });

    // Function to update button visibility
    function updateButtons() {
        var chosenPaymentMethod = $('input[name="payment_method"]:checked').val();


        if (chosenPaymentMethod === 'wafi') {

            $('#wafiCustomPlaceOrder').show();
            $('#place_order').hide();
        } else {

            $('#wafiCustomPlaceOrder').hide();
            $('#place_order').show();
        }
    }
});

;

