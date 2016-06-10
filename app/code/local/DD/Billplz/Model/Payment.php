<?php

class DD_Billplz_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'billplz';

    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canUseCheckout = true;
    protected $_canOrder = true;
    protected $_canRefund = false;
    protected $_canReviewPayment = true;

    protected $_quote = null;
    protected $_order = null;

    public function isInitializeNeeded()
    {
        return true;
    }

    public function initialize($paymentAction, $stateObject)
    {
        $state = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;

        $stateObject->setState($state);
        $stateObject->setStatus($state);
        $stateObject->setIsNotified(false);

        return $this;
    }

    public function canUseForCurrency($currencyCode)
    {
        return 'MYR' == $currencyCode;
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Check whether payment method can be used
     *
     * @param Mage_Sales_Model_Quote
     *
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        return parent::isAvailable($quote) && $this->getConfigData('active');
    }

    /**
     * Return Order place redirect url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('billplz/index/redirect', array('_secure' => true));
    }

    public function getSecretKey()
    {
        return $this->getConfigData('secret_key');
    }
}
