<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * BillingApprovalDetailsType
 *
 * @package PayPal
 */
class BillingApprovalDetailsType extends XSDSimpleType
{
    /**
     * The Type of Approval requested - Billing Agreement or Profile
     */
    var $ApprovalType;

    /**
     * The Approval subtype - Must be MerchantInitiatedBilling for BillingAgreement
     * ApprovalType
     */
    var $ApprovalSubType;

    /**
     * Description about the Order
     */
    var $OrderDetails;

    /**
     * Directives about the type of payment
     */
    var $PaymentDirectives;

    /**
     * Client may pass in its identification of this Billing Agreement. It used for the
     * client's tracking purposes.
     */
    var $Custom;

    function BillingApprovalDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ApprovalType' => 
              array (
                'required' => true,
                'type' => 'ApprovalTypeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ApprovalSubType' => 
              array (
                'required' => false,
                'type' => 'ApprovalSubTypeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'OrderDetails' => 
              array (
                'required' => false,
                'type' => 'OrderDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PaymentDirectives' => 
              array (
                'required' => false,
                'type' => 'PaymentDirectivesType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Custom' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getApprovalType()
    {
        return $this->ApprovalType;
    }
    function setApprovalType($ApprovalType, $charset = 'iso-8859-1')
    {
        $this->ApprovalType = $ApprovalType;
        $this->_elements['ApprovalType']['charset'] = $charset;
    }
    function getApprovalSubType()
    {
        return $this->ApprovalSubType;
    }
    function setApprovalSubType($ApprovalSubType, $charset = 'iso-8859-1')
    {
        $this->ApprovalSubType = $ApprovalSubType;
        $this->_elements['ApprovalSubType']['charset'] = $charset;
    }
    function getOrderDetails()
    {
        return $this->OrderDetails;
    }
    function setOrderDetails($OrderDetails, $charset = 'iso-8859-1')
    {
        $this->OrderDetails = $OrderDetails;
        $this->_elements['OrderDetails']['charset'] = $charset;
    }
    function getPaymentDirectives()
    {
        return $this->PaymentDirectives;
    }
    function setPaymentDirectives($PaymentDirectives, $charset = 'iso-8859-1')
    {
        $this->PaymentDirectives = $PaymentDirectives;
        $this->_elements['PaymentDirectives']['charset'] = $charset;
    }
    function getCustom()
    {
        return $this->Custom;
    }
    function setCustom($Custom, $charset = 'iso-8859-1')
    {
        $this->Custom = $Custom;
        $this->_elements['Custom']['charset'] = $charset;
    }
}
