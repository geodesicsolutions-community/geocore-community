<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * PaymentInfoType
 * 
 * PaymentInfoType Payment information.
 *
 * @package PayPal
 */
class PaymentInfoType extends XSDSimpleType
{
    /**
     * A transaction identification number.
     */
    var $TransactionID;

    /**
     * Its Ebay transaction id.
     */
    var $EbayTransactionID;

    /**
     * Parent or related transaction identification number. This field is populated for
     * the following transaction types:
     */
    var $ParentTransactionID;

    /**
     * Receipt ID
     */
    var $ReceiptID;

    /**
     * The type of transaction
     */
    var $TransactionType;

    /**
     * The type of payment
     */
    var $PaymentType;

    /**
     * The type of funding source
     */
    var $RefundSourceCodeType;

    /**
     * eCheck latest expected clear date
     */
    var $ExpectedeCheckClearDate;

    /**
     * Date and time of payment
     */
    var $PaymentDate;

    /**
     * Full amount of the customer's payment, before transaction fee is subtracted
     */
    var $GrossAmount;

    /**
     * Transaction fee associated with the payment
     */
    var $FeeAmount;

    /**
     * Amount deposited into the account's primary balance after a currency conversion
     * from automatic conversion through your Payment Receiving Preferences or manual
     * conversion through manually accepting a payment. This amount is calculated after
     * fees and taxes have been assessed.
     */
    var $SettleAmount;

    /**
     * Amount of tax for transaction
     */
    var $TaxAmount;

    /**
     * Exchange rate for transaction
     */
    var $ExchangeRate;

    /**
     * The status of the payment:
     */
    var $PaymentStatus;

    /**
     * The reason the payment is pending: none: No pending reason
     */
    var $PendingReason;

    /**
     * The reason for a reversal if TransactionType is reversal: none: No reason code
     */
    var $ReasonCode;

    /**
     * HoldDecision is returned in the response only if PaymentStatus is
     * Completed-Funds-Held. The reason the funds are kept in pending balance:
     * newsellerpaymenthold: The seller is new.
     */
    var $HoldDecision;

    /**
     * Shipping method selected by the user during check-out.
     */
    var $ShippingMethod;

    /**
     * Protection Eligibility for this Transaction - None, SPP or ESPP
     */
    var $ProtectionEligibility;

    /**
     * Protection Eligibility details for this Transaction
     */
    var $ProtectionEligibilityType;

    /**
     * Amount of shipping charged on transaction
     */
    var $ShipAmount;

    /**
     * Amount of ship handling charged on transaction
     */
    var $ShipHandleAmount;

    /**
     * Amount of shipping discount on transaction
     */
    var $ShipDiscount;

    /**
     * Amount of Insurance amount on transaction
     */
    var $InsuranceAmount;

    /**
     * Subject as entered in the transaction
     */
    var $Subject;

    /**
     * Details about the seller.
     */
    var $SellerDetails;

    /**
     * Unique identifier and mandatory for each bucket in case of split payement
     */
    var $PaymentRequestID;

    /**
     * Thes are filters that could result in accept/deny/pending action.
     */
    var $FMFDetails;

    /**
     * This will be enhanced info for the payment: Example: UATP details
     */
    var $EnhancedPaymentInfo;

    /**
     * This will indicate the payment status for individual payment request in case of
     * split payment
     */
    var $PaymentError;

    /**
     * Type of the payment instrument.
     */
    var $InstrumentDetails;

    /**
     * Offer Details.
     */
    var $OfferDetails;

    function PaymentInfoType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'TransactionID' => 
              array (
                'required' => true,
                'type' => NULL,
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'EbayTransactionID' => 
              array (
                'required' => false,
                'type' => NULL,
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ParentTransactionID' => 
              array (
                'required' => false,
                'type' => 'TransactionId',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ReceiptID' => 
              array (
                'required' => false,
                'type' => NULL,
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'TransactionType' => 
              array (
                'required' => true,
                'type' => 'PaymentTransactionCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PaymentType' => 
              array (
                'required' => false,
                'type' => 'PaymentCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'RefundSourceCodeType' => 
              array (
                'required' => false,
                'type' => 'RefundSourceCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ExpectedeCheckClearDate' => 
              array (
                'required' => true,
                'type' => 'dateTime',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PaymentDate' => 
              array (
                'required' => true,
                'type' => 'dateTime',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'GrossAmount' => 
              array (
                'required' => true,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'FeeAmount' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'SettleAmount' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'TaxAmount' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ExchangeRate' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PaymentStatus' => 
              array (
                'required' => true,
                'type' => 'PaymentStatusCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PendingReason' => 
              array (
                'required' => false,
                'type' => 'PendingStatusCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ReasonCode' => 
              array (
                'required' => false,
                'type' => 'ReversalReasonCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'HoldDecision' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ShippingMethod' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ProtectionEligibility' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ProtectionEligibilityType' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ShipAmount' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ShipHandleAmount' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ShipDiscount' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'InsuranceAmount' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Subject' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'SellerDetails' => 
              array (
                'required' => false,
                'type' => 'SellerDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PaymentRequestID' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'FMFDetails' => 
              array (
                'required' => false,
                'type' => 'FMFDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'EnhancedPaymentInfo' => 
              array (
                'required' => false,
                'type' => 'EnhancedPaymentInfoType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PaymentError' => 
              array (
                'required' => false,
                'type' => 'ErrorType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'InstrumentDetails' => 
              array (
                'required' => false,
                'type' => 'InstrumentDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'OfferDetails' => 
              array (
                'required' => false,
                'type' => 'OfferDetailsType',
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
    function getEbayTransactionID()
    {
        return $this->EbayTransactionID;
    }
    function setEbayTransactionID($EbayTransactionID, $charset = 'iso-8859-1')
    {
        $this->EbayTransactionID = $EbayTransactionID;
        $this->_elements['EbayTransactionID']['charset'] = $charset;
    }
    function getParentTransactionID()
    {
        return $this->ParentTransactionID;
    }
    function setParentTransactionID($ParentTransactionID, $charset = 'iso-8859-1')
    {
        $this->ParentTransactionID = $ParentTransactionID;
        $this->_elements['ParentTransactionID']['charset'] = $charset;
    }
    function getReceiptID()
    {
        return $this->ReceiptID;
    }
    function setReceiptID($ReceiptID, $charset = 'iso-8859-1')
    {
        $this->ReceiptID = $ReceiptID;
        $this->_elements['ReceiptID']['charset'] = $charset;
    }
    function getTransactionType()
    {
        return $this->TransactionType;
    }
    function setTransactionType($TransactionType, $charset = 'iso-8859-1')
    {
        $this->TransactionType = $TransactionType;
        $this->_elements['TransactionType']['charset'] = $charset;
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
    function getRefundSourceCodeType()
    {
        return $this->RefundSourceCodeType;
    }
    function setRefundSourceCodeType($RefundSourceCodeType, $charset = 'iso-8859-1')
    {
        $this->RefundSourceCodeType = $RefundSourceCodeType;
        $this->_elements['RefundSourceCodeType']['charset'] = $charset;
    }
    function getExpectedeCheckClearDate()
    {
        return $this->ExpectedeCheckClearDate;
    }
    function setExpectedeCheckClearDate($ExpectedeCheckClearDate, $charset = 'iso-8859-1')
    {
        $this->ExpectedeCheckClearDate = $ExpectedeCheckClearDate;
        $this->_elements['ExpectedeCheckClearDate']['charset'] = $charset;
    }
    function getPaymentDate()
    {
        return $this->PaymentDate;
    }
    function setPaymentDate($PaymentDate, $charset = 'iso-8859-1')
    {
        $this->PaymentDate = $PaymentDate;
        $this->_elements['PaymentDate']['charset'] = $charset;
    }
    function getGrossAmount()
    {
        return $this->GrossAmount;
    }
    function setGrossAmount($GrossAmount, $charset = 'iso-8859-1')
    {
        $this->GrossAmount = $GrossAmount;
        $this->_elements['GrossAmount']['charset'] = $charset;
    }
    function getFeeAmount()
    {
        return $this->FeeAmount;
    }
    function setFeeAmount($FeeAmount, $charset = 'iso-8859-1')
    {
        $this->FeeAmount = $FeeAmount;
        $this->_elements['FeeAmount']['charset'] = $charset;
    }
    function getSettleAmount()
    {
        return $this->SettleAmount;
    }
    function setSettleAmount($SettleAmount, $charset = 'iso-8859-1')
    {
        $this->SettleAmount = $SettleAmount;
        $this->_elements['SettleAmount']['charset'] = $charset;
    }
    function getTaxAmount()
    {
        return $this->TaxAmount;
    }
    function setTaxAmount($TaxAmount, $charset = 'iso-8859-1')
    {
        $this->TaxAmount = $TaxAmount;
        $this->_elements['TaxAmount']['charset'] = $charset;
    }
    function getExchangeRate()
    {
        return $this->ExchangeRate;
    }
    function setExchangeRate($ExchangeRate, $charset = 'iso-8859-1')
    {
        $this->ExchangeRate = $ExchangeRate;
        $this->_elements['ExchangeRate']['charset'] = $charset;
    }
    function getPaymentStatus()
    {
        return $this->PaymentStatus;
    }
    function setPaymentStatus($PaymentStatus, $charset = 'iso-8859-1')
    {
        $this->PaymentStatus = $PaymentStatus;
        $this->_elements['PaymentStatus']['charset'] = $charset;
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
    function getReasonCode()
    {
        return $this->ReasonCode;
    }
    function setReasonCode($ReasonCode, $charset = 'iso-8859-1')
    {
        $this->ReasonCode = $ReasonCode;
        $this->_elements['ReasonCode']['charset'] = $charset;
    }
    function getHoldDecision()
    {
        return $this->HoldDecision;
    }
    function setHoldDecision($HoldDecision, $charset = 'iso-8859-1')
    {
        $this->HoldDecision = $HoldDecision;
        $this->_elements['HoldDecision']['charset'] = $charset;
    }
    function getShippingMethod()
    {
        return $this->ShippingMethod;
    }
    function setShippingMethod($ShippingMethod, $charset = 'iso-8859-1')
    {
        $this->ShippingMethod = $ShippingMethod;
        $this->_elements['ShippingMethod']['charset'] = $charset;
    }
    function getProtectionEligibility()
    {
        return $this->ProtectionEligibility;
    }
    function setProtectionEligibility($ProtectionEligibility, $charset = 'iso-8859-1')
    {
        $this->ProtectionEligibility = $ProtectionEligibility;
        $this->_elements['ProtectionEligibility']['charset'] = $charset;
    }
    function getProtectionEligibilityType()
    {
        return $this->ProtectionEligibilityType;
    }
    function setProtectionEligibilityType($ProtectionEligibilityType, $charset = 'iso-8859-1')
    {
        $this->ProtectionEligibilityType = $ProtectionEligibilityType;
        $this->_elements['ProtectionEligibilityType']['charset'] = $charset;
    }
    function getShipAmount()
    {
        return $this->ShipAmount;
    }
    function setShipAmount($ShipAmount, $charset = 'iso-8859-1')
    {
        $this->ShipAmount = $ShipAmount;
        $this->_elements['ShipAmount']['charset'] = $charset;
    }
    function getShipHandleAmount()
    {
        return $this->ShipHandleAmount;
    }
    function setShipHandleAmount($ShipHandleAmount, $charset = 'iso-8859-1')
    {
        $this->ShipHandleAmount = $ShipHandleAmount;
        $this->_elements['ShipHandleAmount']['charset'] = $charset;
    }
    function getShipDiscount()
    {
        return $this->ShipDiscount;
    }
    function setShipDiscount($ShipDiscount, $charset = 'iso-8859-1')
    {
        $this->ShipDiscount = $ShipDiscount;
        $this->_elements['ShipDiscount']['charset'] = $charset;
    }
    function getInsuranceAmount()
    {
        return $this->InsuranceAmount;
    }
    function setInsuranceAmount($InsuranceAmount, $charset = 'iso-8859-1')
    {
        $this->InsuranceAmount = $InsuranceAmount;
        $this->_elements['InsuranceAmount']['charset'] = $charset;
    }
    function getSubject()
    {
        return $this->Subject;
    }
    function setSubject($Subject, $charset = 'iso-8859-1')
    {
        $this->Subject = $Subject;
        $this->_elements['Subject']['charset'] = $charset;
    }
    function getSellerDetails()
    {
        return $this->SellerDetails;
    }
    function setSellerDetails($SellerDetails, $charset = 'iso-8859-1')
    {
        $this->SellerDetails = $SellerDetails;
        $this->_elements['SellerDetails']['charset'] = $charset;
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
    function getFMFDetails()
    {
        return $this->FMFDetails;
    }
    function setFMFDetails($FMFDetails, $charset = 'iso-8859-1')
    {
        $this->FMFDetails = $FMFDetails;
        $this->_elements['FMFDetails']['charset'] = $charset;
    }
    function getEnhancedPaymentInfo()
    {
        return $this->EnhancedPaymentInfo;
    }
    function setEnhancedPaymentInfo($EnhancedPaymentInfo, $charset = 'iso-8859-1')
    {
        $this->EnhancedPaymentInfo = $EnhancedPaymentInfo;
        $this->_elements['EnhancedPaymentInfo']['charset'] = $charset;
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
    function getInstrumentDetails()
    {
        return $this->InstrumentDetails;
    }
    function setInstrumentDetails($InstrumentDetails, $charset = 'iso-8859-1')
    {
        $this->InstrumentDetails = $InstrumentDetails;
        $this->_elements['InstrumentDetails']['charset'] = $charset;
    }
    function getOfferDetails()
    {
        return $this->OfferDetails;
    }
    function setOfferDetails($OfferDetails, $charset = 'iso-8859-1')
    {
        $this->OfferDetails = $OfferDetails;
        $this->_elements['OfferDetails']['charset'] = $charset;
    }
}
