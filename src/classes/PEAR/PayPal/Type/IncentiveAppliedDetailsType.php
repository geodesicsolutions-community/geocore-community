<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * IncentiveAppliedDetailsType
 * 
 * Details of incentive application on individual bucket/item.
 *
 * @package PayPal
 */
class IncentiveAppliedDetailsType extends XSDSimpleType
{
    /**
     * PaymentRequestID uniquely identifies a bucket. It is the "bucket id" in the
     * world of EC API.
     */
    var $PaymentRequestID;

    /**
     * The item id passed through by the merchant.
     */
    var $ItemId;

    /**
     * The item transaction id passed through by the merchant.
     */
    var $ExternalTxnId;

    /**
     * Discount offerred for this bucket or item.
     */
    var $DiscountAmount;

    /**
     * SubType for coupon.
     */
    var $SubType;

    function IncentiveAppliedDetailsType()
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
              'ExternalTxnId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'DiscountAmount' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'SubType' => 
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
    function getExternalTxnId()
    {
        return $this->ExternalTxnId;
    }
    function setExternalTxnId($ExternalTxnId, $charset = 'iso-8859-1')
    {
        $this->ExternalTxnId = $ExternalTxnId;
        $this->_elements['ExternalTxnId']['charset'] = $charset;
    }
    function getDiscountAmount()
    {
        return $this->DiscountAmount;
    }
    function setDiscountAmount($DiscountAmount, $charset = 'iso-8859-1')
    {
        $this->DiscountAmount = $DiscountAmount;
        $this->_elements['DiscountAmount']['charset'] = $charset;
    }
    function getSubType()
    {
        return $this->SubType;
    }
    function setSubType($SubType, $charset = 'iso-8859-1')
    {
        $this->SubType = $SubType;
        $this->_elements['SubType']['charset'] = $charset;
    }
}
