/**
 * Hooks into the checkout form, activating the Stripe.js api's to retrieve a token and store it in a hidden field.
 * It doesn't depend on jQuery or any other javascript library.
 *
 * @author Mark Guinn <markguinn@gmail.com>
 * @date 10.1.2016
 * @package silvershop-stripe
 */
(function(window, document, undefined) {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        var config = window.StripeConfig,
            submitting = false,
            form = document.getElementById(config.formID);

        if (!config) {
            console.error('StripeConfig was not set');
            return;
        }
        if (!form) {
            console.error('Form was not found on the page!', config.formID);
            return;
        }

        Stripe.setPublishableKey(config.key);

        form.addEventListener('submit', function(event) {
            event.stopPropagation();
            event.preventDefault();
            if (submitting) return false;

            // Disable the submit button to prevent repeated clicks:
            var submitButton = form.querySelector('.action');
            if (submitButton) submitButton.disabled = true;
            submitting = true;

            // Request a token from Stripe:
            Stripe.card.createToken(form, function stripeResponseHandler(status, response) {
                if (submitButton) submitButton.disabled = false;
                submitting = false;

                if (response.error) {
                    var errorContainer = document.getElementById(config.formID + '_error');
                    if (errorContainer) {
                        errorContainer.innerHTML = response.error.message;
                        errorContainer.style.display = '';
                    } else {
                        console.error(response.error);
                    }
                } else {
                    form.querySelector('[name="' + config.tokenField + '"]').value = response.id;
                    form.submit();
                }
            });

            // Prevent the form from being submitted:
            return false;
        }, true);
    });

})(this, this.document);
