<?php
use SilverStripe\Omnipay\Service\PurchaseService;

/**
 * This replaces the default OnsitePaymentCheckoutComponent and uses stripe's checkout
 * returning only a nonce to the server.
 *
 * NOTE: This handles ALL javascript setup so you don't need to do anything. By default it
 * will replace OnsitePaymentCheckoutComponent via the injector in either single page or
 * multi-step checkout.
 *
 * @author  Mark Guinn <markguinn@gmail.com>
 * @date    9.24.2016
 * @package silvershop-stripe
 */
class StripePaymentCheckoutComponent extends OnsitePaymentCheckoutComponent
{
    /** @var bool - if for some reason the gateway is not actually stripe, fall back to OnsitePayment */
    protected $isStripe;

    /** @var \Omnipay\Common\AbstractGateway|\Omnipay\Stripe\Gateway */
    protected $gateway;

    /**
     * @param Order $order
     *
     * @return \Omnipay\Common\AbstractGateway|\Omnipay\Stripe\Gateway
     */
    protected function getGateway($order)
    {
        if (!isset($this->gateway)) {
            $tempPayment = new Payment(
                [
                    'Gateway' => Checkout::get($order)->getSelectedPaymentMethod(false),
                ]
            );
            $service = PurchaseService::create($tempPayment);
            $this->gateway = $service->oGateway();
            $this->isStripe = ($this->gateway instanceof \Omnipay\Stripe\Gateway);
        }

        return $this->gateway;
    }

    /**
     * @param \Omnipay\Common\AbstractGateway|\Omnipay\Stripe\Gateway $gateway
     * @return $this
     */
    public function setGateway($gateway)
    {
        $this->gateway = $gateway;
        $this->isStripe = ($this->gateway instanceof \Omnipay\Stripe\Gateway);
        return $this;
    }

    /**
     * Get form fields for manipulating the current order,
     * according to the responsibility of this component.
     *
     * @param Order $order
     * @param Form $form
     *
     * @return FieldList
     */
    public function getFormFields(Order $order, Form $form = null)
    {
        $gateway = $this->getGateway($order);
        if (!$this->isStripe) {
            return parent::getFormFields($order);
        }

        // Generate the standard set of fields and allow it to be customised
        $fields = FieldList::create(
            [
                StripeField::create('number', _t('Stripe.CardNumber', 'Card Number')),
                StripeField::create('exp_month', _t('Stripe.ExpirationMonth', 'Expiration (MM)')),
                StripeField::create('exp_year', _t('Stripe.ExpirationYear', 'Expiration (YY)')),
                StripeField::create('cvc', _t('Stripe.CVC', 'CVC')),
                StripeField::create('address_zip', _t('Stripe.BillingZip', 'Billing Postal Code')),
                $tokenField = HiddenField::create('token', '', ''),
            ]
        );
        $this->extend('updateFormFields', $fields);

        // Generate a basic config and allow it to be customised
        $stripeConfig = Config::inst()->get('GatewayInfo', 'Stripe');
        $jsConfig = [
            'formID'     => $form ? $form->getHTMLID() : 'PaymentForm_PaymentForm',
            'tokenField' => $tokenField->getName(),
            'key'        => isset($stripeConfig['parameters']) && isset($stripeConfig['parameters']['publishableKey'])
                                ? $stripeConfig['parameters']['publishableKey']
                                : '',
        ];
        $this->extend('updateStripeConfig', $jsConfig);

        if (empty($jsConfig['key'])) {
            user_error('Publishable key was not set. Should be in GatewayInfo.Stripe.parameters.publishableKey.');
        }

        // Finally, add the javascript to the page
        Requirements::javascript('https://js.stripe.com/v2/');
        Requirements::javascript('silvershop-stripe/javascript/checkout.js');
        Requirements::customScript("window.StripeConfig = " . json_encode($jsConfig), 'StripeJS');

        return $fields;
    }

    /**
     * Get the data fields that are required for the component.
     *
     * @param  Order $order [description]
     *
     * @return array        required data fields
     */
    public function getRequiredFields(Order $order)
    {
        $this->getGateway($order);
        if (!$this->isStripe) {
            return parent::getRequiredFields($order);
        } else {
            return [];
        }
    }

    /**
     * Is this data valid for saving into an order?
     *
     * This function should never rely on form.
     *
     * @param Order $order
     * @param array $data data to be validated
     *
     * @throws ValidationException
     * @return boolean the data is valid
     */
    public function validateData(Order $order, array $data)
    {
        $this->getGateway($order);
        if (!$this->isStripe) {
            return parent::validateData($order, $data);
        } else {
            // NOTE: Stripe will validate clientside and if for some reason that falls through
            // it will fail on payment and give an error then. It would be a lot of work to get
            // the token to be namespaced so it could be passed here and there would be no point.
            return true;
        }
    }

    /**
     * Get required data out of the model.
     *
     * @param  Order $order order to get data from.
     *
     * @return array        get data from model(s)
     */
    public function getData(Order $order)
    {
        $this->getGateway($order);
        if (!$this->isStripe) {
            return parent::getData($order);
        } else {
            return [];
        }
    }

    /**
     * Set the model data for this component.
     *
     * This function should never rely on form.
     *
     * @param Order $order
     * @param array $data data to be saved into order object
     *
     * @throws Exception
     * @return Order the updated order
     */
    public function setData(Order $order, array $data)
    {
        $this->getGateway($order);
        if (!$this->isStripe) {
            return parent::setData($order, $data);
        } else {
            return [];
        }
    }
}
