<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * PaymentDirectivesType
 *
 * @package PayPal
 */
class PaymentDirectivesType extends XSDSimpleType
{
    /**
     * Type of the Payment is it Instant or Echeck or Any.
     */
    var $PaymentType;

    function PaymentDirectivesType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'PaymentType' => 
              array (
                'required' => false,
                'type' => 'MerchantPullPaymentCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getPaymentType()
    {
        return $this->PaymentType;
    }
    function setPaymentType($PaymentType, $charset = 'iso-8859-1')
    {
        $this->PaymentType = $PaymentType;
        $this->_elements['PaymentType']['charset'] = $charset;
    }
}
