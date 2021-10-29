<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractRequestType.php';

/**
 * RefundTransactionRequestType
 *
 * @package PayPal
 */
class RefundTransactionRequestType extends AbstractRequestType
{
    /**
     * Unique identifier of the transaction you are refunding.
     */
    var $TransactionID;

    /**
     * Invoice number corresponding to transaction details for tracking the refund of a
     * payment. This parameter is passed by the merchant or recipient while refunding
     * the transaction. This parameter does not affect the business logic, it is
     * persisted in the DB for transaction reference
     */
    var $InvoiceID;

    /**
     * Type of refund you are making
     */
    var $RefundType;

    /**
     * Refund amount.
     */
    var $Amount;

    /**
     * Custom memo about the refund.
     */
    var $Memo;

    /**
     * The maximum time till which refund must be tried.
     */
    var $RetryUntil;

    /**
     * The type of funding source for refund.
     */
    var $RefundSource;

    var $MerchantStoreDetails;

    function RefundTransactionRequestType()
    {
        parent::AbstractRequestType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'TransactionID' => 
              array (
                'required' => true,
                'type' => 'TransactionId',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'InvoiceID' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'RefundType' => 
              array (
                'required' => false,
                'type' => 'RefundType',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'Amount' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'Memo' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'RetryUntil' => 
              array (
                'required' => false,
                'type' => 'dateTime',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'RefundSource' => 
              array (
                'required' => false,
                'type' => 'RefundSourceCodeType',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'MerchantStoreDetails' => 
              array (
                'required' => false,
                'type' => 'MerchantStoreDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getTransactionID()
    {
        return $this->TransactionID;
    }
    function setTransactionID($TransactionID, $charset = 'iso-8859-1')
    {
        $this->TransactionID = $TransactionID;
        $this->_elements['TransactionID']['charset'] = $charset;
    }
    function getInvoiceID()
    {
        return $this->InvoiceID;
    }
    function setInvoiceID($InvoiceID, $charset = 'iso-8859-1')
    {
        $this->InvoiceID = $InvoiceID;
        $this->_elements['InvoiceID']['charset'] = $charset;
    }
    function getRefundType()
    {
        return $this->RefundType;
    }
    function setRefundType($RefundType, $charset = 'iso-8859-1')
    {
        $this->RefundType = $RefundType;
        $this->_elements['RefundType']['charset'] = $charset;
    }
    function getAmount()
    {
        return $this->Amount;
    }
    function setAmount($Amount, $charset = 'iso-8859-1')
    {
        $this->Amount = $Amount;
        $this->_elements['Amount']['charset'] = $charset;
    }
    function getMemo()
    {
        return $this->Memo;
    }
    function setMemo($Memo, $charset = 'iso-8859-1')
    {
        $this->Memo = $Memo;
        $this->_elements['Memo']['charset'] = $charset;
    }
    function getRetryUntil()
    {
        return $this->RetryUntil;
    }
    function setRetryUntil($RetryUntil, $charset = 'iso-8859-1')
    {
        $this->RetryUntil = $RetryUntil;
        $this->_elements['RetryUntil']['charset'] = $charset;
    }
    function getRefundSource()
    {
        return $this->RefundSource;
    }
    function setRefundSource($RefundSource, $charset = 'iso-8859-1')
    {
        $this->RefundSource = $RefundSource;
        $this->_elements['RefundSource']['charset'] = $charset;
    }
    function getMerchantStoreDetails()
    {
        return $this->MerchantStoreDetails;
    }
    function setMerchantStoreDetails($MerchantStoreDetails, $charset = 'iso-8859-1')
    {
        $this->MerchantStoreDetails = $MerchantStoreDetails;
        $this->_elements['MerchantStoreDetails']['charset'] = $charset;
    }
}
