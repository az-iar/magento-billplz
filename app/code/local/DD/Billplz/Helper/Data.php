<?php

class DD_Billplz_Helper_Data extends Mage_Core_Helper_Data
{
    public function getCallbackUrl()
    {
        return $this->_getUrl('billplz/index/callback');
    }

    public function getRedirectUrl()
    {
        return $this->_getUrl('billplz/index/complete');
    }
}