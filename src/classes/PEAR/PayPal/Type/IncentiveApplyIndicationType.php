<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * IncentiveApplyIndicationType
 * 
 * Defines which bucket or item that the incentive should be applied to.
 *
 * @package PayPal
 */
class IncentiveApplyIndicationType extends XSDSimpleType
{
    /**
     * The Bucket ID that the incentive is applied to.
     */
    var $PaymentRequestID;

    /**
     * The item that the incentive is applied to.
     */
    var $ItemId;

    function IncentiveApplyIndicationType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'PaymentRequestID' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ItemId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
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
    function getItemId()
    {
        return $this->ItemId;
    }
    function setItemId($ItemId, $charset = 'iso-8859-1')
    {
        $this->ItemId = $ItemId;
        $this->_elements['ItemId']['charset'] = $charset;
    }
}
