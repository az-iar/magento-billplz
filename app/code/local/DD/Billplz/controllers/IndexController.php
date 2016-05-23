<?php

class DD_Billplz_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Redirect user to bill payment form
     *
     * @throws Exception
     */
    public function redirectAction()
    {
        $paymentMethod = $this->getMethod();
        $billplz = $this->getBillplz();

        // retrieve order
        $order = $paymentMethod->getCheckout()->getLastRealOrder();

        // create billplz bill before redirect to billplz
        $bill = $billplz->createBill([
            'order_id'    => $order->getIncrementId(),
            'name'        => $order->getBillingAddress()->getName(),
            'email'       => $order->getBillingAddress()->getEmail(),
            'mobile'      => $order->getBillingAddress()->getTelephone(),
            'amount'      => $order->getBaseGrandTotal(),
            'description' => "Bill for order #{$order->getIncrementId()}",
        ]);

        if ($bill) {
            // save Billplz bill id
            $order->setData('billplz_bill_id', $bill->id);
            $order->save();

            Mage::log("Redirecting user to Billplz for bill: {$bill->id}", LOG_DEBUG, 'billplz.log');

            $this->_redirectUrl($bill->url);
        } else {
            $this->norouteAction();
        }
    }

    /**
     * Receive callback from Billplz
     */
    public function callbackAction()
    {
        if ($this->getRequest()->getMethod() != 'POST') {
            $this->norouteAction();
        }

        /** @var DD_Billplz_Model_Billplz $billplz */
        $billplz = $this->getBillplz();

        $bill_id = $this->getRequest()->getPost('id');
        $bill = $billplz->getBill($bill_id);

        Mage::log("Received callback for bill: {$bill_id}", LOG_DEBUG, 'billplz.log');

        // check bill status
        if ($bill) {
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->load($bill->id, 'billplz_bill_id');
            // If bill is paid and order status is pending payment, move order to processing
            if ($bill->paid && $order->getState() == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $this->__('Payment received from Billplz'), false);
                $order->save();
            }
        } else {
            // show error 404 if bill is not valid
            $this->norouteAction();
        }

    }

    public function completeAction()
    {
        $billplzParams = $this->getRequest()->get('billplz');
        $bill_id = $billplzParams['id'];

        Mage::log("Complete: {$bill_id}", LOG_DEBUG, 'billplz.log');

        /** @var DD_Billplz_Model_Billplz $billplz */
        $billplz = $this->getBillplz();

        $bill = $billplz->getBill($bill_id);
        // check bill status
        if ($bill) {
            if ($bill->paid) {
                /** @var Mage_Sales_Model_Order $order */
                $order = Mage::getModel('sales/order')->load($bill->id, 'billplz_bill_id');
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $this->__('Payment received from Billplz'), false);
                $order->save();

                $this->_redirect('checkout/onepage/success');
            } else {
                $this->_redirect('checkout/onepage/failure');
            }
        } else {
            // show error 404 if bill is not valid
            $this->norouteAction();
        }
    }

    /**
     * @return DD_Billplz_Model_Payment
     */
    private function getMethod()
    {
        return Mage::getModel('billplz/payment');
    }

    /**
     * @return DD_Billplz_Model_Billplz
     */
    private function getBillplz()
    {
        return Mage::getModel('billplz/billplz');
    }
}