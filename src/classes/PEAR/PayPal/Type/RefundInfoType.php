<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * RefundInfoType
 * 
 * Holds refunds payment status information
 *
 * @package PayPal
 */
class RefundInfoType extends XSDSimpleType
{
    /**
     * Refund status whether it is Instant or Delayed.
     */
    var $RefundStatus;

    /**
     * Tells us the reason when refund payment status is Delayed.
     */
    var $PendingReason;

    function RefundInfoType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'RefundStatus' => 
              array (
                'required' => false,
                'type' => 'PaymentStatusCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PendingReason' => 
              array (
                'required' => false,
                'type' => 'PendingStatusCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getRefundStatus()
    {
        return $this->RefundStatus;
    }
    function setRefundStatus($RefundStatus, $charset = 'iso-8859-1')
    {
        $this->RefundStatus = $RefundStatus;
        $this->_elements['RefundStatus']['charset'] = $charset;
    }
    function getPendingReason()
    {
        return $this->PendingReason;
    }
    function setPendingReason($PendingReason, $charset = 'iso-8859-1')
    {
        $this->PendingReason = $PendingReason;
        $this->_elements['PendingReason']['charset'] = $charset;
    }
}
