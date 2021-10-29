<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * PaymentRequestInfoType
 * 
 * Contains payment request information for each bucket in the cart.
 *
 * @package PayPal
 */
class PaymentRequestInfoType extends XSDSimpleType
{
    /**
     * Contains the transaction id of the bucket.
     */
    var $TransactionId;

    /**
     * Contains the bucket id.
     */
    var $PaymentRequestID;

    /**
     * Contains the error details.
     */
    var $PaymentError;

    function PaymentRequestInfoType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'TransactionId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PaymentRequestID' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PaymentError' => 
              array (
                'required' => false,
                'type' => 'ErrorType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getTransactionId()
    {
        return $this->TransactionId;
    }
    function setTransactionId($TransactionId, $charset = 'iso-8859-1')
    {
        $this->TransactionId = $TransactionId;
        $this->_elements['TransactionId']['charset'] = $charset;
    }
    function getPaymentRequestID()
    {
        return $this->PaymentRequestID;
    }
    function setPaymentRequestID($PaymentRequestID, $charset = 'iso-8859-1')
    {
        $this->PaymentRequestID = $PaymentRequestID;
        $this->_elements['PaymentRequestID']['charset'] = $charset;
    }
    function getPaymentError()
    {
        return $this->PaymentError;
    }
    function setPaymentError($PaymentError, $charset = 'iso-8859-1')
    {
        $this->PaymentError = $PaymentError;
        $this->_elements['PaymentError']['charset'] = $charset;
    }
}
