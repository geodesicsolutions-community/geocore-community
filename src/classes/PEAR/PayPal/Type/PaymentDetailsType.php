<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * PaymentDetailsType
 * 
 * PaymentDetailsType Information about a payment. Used by DCC and Express
 * Checkout.
 *
 * @package PayPal
 */
class PaymentDetailsType extends XSDSimpleType
{
    /**
     * Total of order, including shipping, handling, and tax. You must set the
     * currencyID attribute to one of the three-character currency codes for any of the
     * supported PayPal currencies.
     */
    var $OrderTotal;

    /**
     * Sum of cost of all items in this order. You must set the currencyID attribute to
     * one of the three-character currency codes for any of the supported PayPal
     * currencies.
     */
    var $ItemTotal;

    /**
     * Total shipping costs for this order. You must set the currencyID attribute to
     * one of the three-character currency codes for any of the supported PayPal
     * currencies.
     */
    var $ShippingTotal;

    /**
     * Total handling costs for this order. You must set the currencyID attribute to
     * one of the three-character currency codes for any of the supported PayPal
     * currencies.
     */
    var $HandlingTotal;

    /**
     * Sum of tax for all items in this order. You must set the currencyID attribute to
     * one of the three-character currency codes for any of the supported PayPal
     * currencies.
     */
    var $TaxTotal;

    /**
     * Description of items the customer is purchasing.
     */
    var $OrderDescription;

    /**
     * A free-form field for your own use.
     */
    var $Custom;

    /**
     * Your own invoice or tracking number.
     */
    var $InvoiceID;

    /**
     * An identification code for use by third-party applications to identify
     * transactions.
     */
    var $ButtonSource;

    /**
     * Your URL for receiving Instant Payment Notification (IPN) about this
     * transaction.
     */
    var $NotifyURL;

    /**
     * Address the order will be shipped to.
     */
    var $ShipToAddress;

    var $ShippingMethod;

    /**
     * Date and time (in GMT in the format yyyy-MM-ddTHH:mm:ssZ) at which address was
     * changed by the user.
     */
    var $ProfileAddressChangeDate;

    /**
     * Information about the individual purchased items
     */
    var $PaymentDetailsItem;

    /**
     * Total shipping insurance costs for this order.
     */
    var $InsuranceTotal;

    /**
     * Shipping discount for this order, specified as a negative number.
     */
    var $ShippingDiscount;

    /**
     * Information about the Insurance options.
     */
    var $InsuranceOptionOffered;

    /**
     * Allowed payment methods for this transaction.
     */
    var $AllowedPaymentMethod;

    /**
     * Enhanced Data section to accept channel specific data.
     */
    var $EnhancedPaymentData;

    /**
     * Details about the seller.
     */
    var $SellerDetails;

    /**
     * Note to recipient/seller.
     */
    var $NoteText;

    /**
     * PayPal Transaction Id, returned once DoExpressCheckout is completed.
     */
    var $TransactionId;

    /**
     * How you want to obtain payment.
     */
    var $PaymentAction;

    /**
     * Unique identifier and mandatory for the particular payment request in case of
     * multiple payment
     */
    var $PaymentRequestID;

    /**
     * URL on Merchant site pertaining to this invoice.
     */
    var $OrderURL;

    /**
     * Soft Descriptor supported for Sale and Auth in DEC only. For Order this will be
     * ignored.
     */
    var $SoftDescriptor;

    /**
     * BranchLevel is used to identify chain payment. If BranchLevel is 0 or 1, this
     * payment is where money moves to. If BranchLevel greater than 1, this payment
     * contains the actual seller info.
     */
    var $BranchLevel;

    /**
     * Soft Descriptor supported for Sale and Auth in DEC only. For Order this will be
     * ignored.
     */
    var $OfferDetails;

    /**
     * Flag to indicate the recurring transaction
     */
    var $Recurring;

    function PaymentDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'OrderTotal' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ItemTotal' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ShippingTotal' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'HandlingTotal' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'TaxTotal' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'OrderDescription' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Custom' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'InvoiceID' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ButtonSource' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'NotifyURL' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ShipToAddress' => 
              array (
                'required' => false,
                'type' => 'AddressType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ShippingMethod' => 
              array (
                'required' => false,
                'type' => 'ShippingServiceCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ProfileAddressChangeDate' => 
              array (
                'required' => false,
                'type' => 'dateTime',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PaymentDetailsItem' => 
              array (
                'required' => false,
                'type' => 'PaymentDetailsItemType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'InsuranceTotal' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ShippingDiscount' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'InsuranceOptionOffered' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'AllowedPaymentMethod' => 
              array (
                'required' => false,
                'type' => 'AllowedPaymentMethodType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'EnhancedPaymentData' => 
              array (
                'required' => false,
                'type' => 'EnhancedPaymentDataType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'SellerDetails' => 
              array (
                'required' => false,
                'type' => 'SellerDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'NoteText' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'TransactionId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PaymentAction' => 
              array (
                'required' => false,
                'type' => 'PaymentActionCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PaymentRequestID' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'OrderURL' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'SoftDescriptor' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'BranchLevel' => 
              array (
                'required' => false,
                'type' => 'integer',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'OfferDetails' => 
              array (
                'required' => false,
                'type' => 'OfferDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Recurring' => 
              array (
                'required' => false,
                'type' => 'RecurringFlagType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getOrderTotal()
    {
        return $this->OrderTotal;
    }
    function setOrderTotal($OrderTotal, $charset = 'iso-8859-1')
    {
        $this->OrderTotal = $OrderTotal;
        $this->_elements['OrderTotal']['charset'] = $charset;
    }
    function getItemTotal()
    {
        return $this->ItemTotal;
    }
    function setItemTotal($ItemTotal, $charset = 'iso-8859-1')
    {
        $this->ItemTotal = $ItemTotal;
        $this->_elements['ItemTotal']['charset'] = $charset;
    }
    function getShippingTotal()
    {
        return $this->ShippingTotal;
    }
    function setShippingTotal($ShippingTotal, $charset = 'iso-8859-1')
    {
        $this->ShippingTotal = $ShippingTotal;
        $this->_elements['ShippingTotal']['charset'] = $charset;
    }
    function getHandlingTotal()
    {
        return $this->HandlingTotal;
    }
    function setHandlingTotal($HandlingTotal, $charset = 'iso-8859-1')
    {
        $this->HandlingTotal = $HandlingTotal;
        $this->_elements['HandlingTotal']['charset'] = $charset;
    }
    function getTaxTotal()
    {
        return $this->TaxTotal;
    }
    function setTaxTotal($TaxTotal, $charset = 'iso-8859-1')
    {
        $this->TaxTotal = $TaxTotal;
        $this->_elements['TaxTotal']['charset'] = $charset;
    }
    function getOrderDescription()
    {
        return $this->OrderDescription;
    }
    function setOrderDescription($OrderDescription, $charset = 'iso-8859-1')
    {
        $this->OrderDescription = $OrderDescription;
        $this->_elements['OrderDescription']['charset'] = $charset;
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
    function getInvoiceID()
    {
        return $this->InvoiceID;
    }
    function setInvoiceID($InvoiceID, $charset = 'iso-8859-1')
    {
        $this->InvoiceID = $InvoiceID;
        $this->_elements['InvoiceID']['charset'] = $charset;
    }
    function getButtonSource()
    {
        return $this->ButtonSource;
    }
    function setButtonSource($ButtonSource, $charset = 'iso-8859-1')
    {
        $this->ButtonSource = $ButtonSource;
        $this->_elements['ButtonSource']['charset'] = $charset;
    }
    function getNotifyURL()
    {
        return $this->NotifyURL;
    }
    function setNotifyURL($NotifyURL, $charset = 'iso-8859-1')
    {
        $this->NotifyURL = $NotifyURL;
        $this->_elements['NotifyURL']['charset'] = $charset;
    }
    function getShipToAddress()
    {
        return $this->ShipToAddress;
    }
    function setShipToAddress($ShipToAddress, $charset = 'iso-8859-1')
    {
        $this->ShipToAddress = $ShipToAddress;
        $this->_elements['ShipToAddress']['charset'] = $charset;
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
    function getProfileAddressChangeDate()
    {
        return $this->ProfileAddressChangeDate;
    }
    function setProfileAddressChangeDate($ProfileAddressChangeDate, $charset = 'iso-8859-1')
    {
        $this->ProfileAddressChangeDate = $ProfileAddressChangeDate;
        $this->_elements['ProfileAddressChangeDate']['charset'] = $charset;
    }
    function getPaymentDetailsItem()
    {
        return $this->PaymentDetailsItem;
    }
    function setPaymentDetailsItem($PaymentDetailsItem, $charset = 'iso-8859-1')
    {
        $this->PaymentDetailsItem = $PaymentDetailsItem;
        $this->_elements['PaymentDetailsItem']['charset'] = $charset;
    }
    function getInsuranceTotal()
    {
        return $this->InsuranceTotal;
    }
    function setInsuranceTotal($InsuranceTotal, $charset = 'iso-8859-1')
    {
        $this->InsuranceTotal = $InsuranceTotal;
        $this->_elements['InsuranceTotal']['charset'] = $charset;
    }
    function getShippingDiscount()
    {
        return $this->ShippingDiscount;
    }
    function setShippingDiscount($ShippingDiscount, $charset = 'iso-8859-1')
    {
        $this->ShippingDiscount = $ShippingDiscount;
        $this->_elements['ShippingDiscount']['charset'] = $charset;
    }
    function getInsuranceOptionOffered()
    {
        return $this->InsuranceOptionOffered;
    }
    function setInsuranceOptionOffered($InsuranceOptionOffered, $charset = 'iso-8859-1')
    {
        $this->InsuranceOptionOffered = $InsuranceOptionOffered;
        $this->_elements['InsuranceOptionOffered']['charset'] = $charset;
    }
    function getAllowedPaymentMethod()
    {
        return $this->AllowedPaymentMethod;
    }
    function setAllowedPaymentMethod($AllowedPaymentMethod, $charset = 'iso-8859-1')
    {
        $this->AllowedPaymentMethod = $AllowedPaymentMethod;
        $this->_elements['AllowedPaymentMethod']['charset'] = $charset;
    }
    function getEnhancedPaymentData()
    {
        return $this->EnhancedPaymentData;
    }
    function setEnhancedPaymentData($EnhancedPaymentData, $charset = 'iso-8859-1')
    {
        $this->EnhancedPaymentData = $EnhancedPaymentData;
        $this->_elements['EnhancedPaymentData']['charset'] = $charset;
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
    function getNoteText()
    {
        return $this->NoteText;
    }
    function setNoteText($NoteText, $charset = 'iso-8859-1')
    {
        $this->NoteText = $NoteText;
        $this->_elements['NoteText']['charset'] = $charset;
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
    function getPaymentAction()
    {
        return $this->PaymentAction;
    }
    function setPaymentAction($PaymentAction, $charset = 'iso-8859-1')
    {
        $this->PaymentAction = $PaymentAction;
        $this->_elements['PaymentAction']['charset'] = $charset;
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
    function getOrderURL()
    {
        return $this->OrderURL;
    }
    function setOrderURL($OrderURL, $charset = 'iso-8859-1')
    {
        $this->OrderURL = $OrderURL;
        $this->_elements['OrderURL']['charset'] = $charset;
    }
    function getSoftDescriptor()
    {
        return $this->SoftDescriptor;
    }
    function setSoftDescriptor($SoftDescriptor, $charset = 'iso-8859-1')
    {
        $this->SoftDescriptor = $SoftDescriptor;
        $this->_elements['SoftDescriptor']['charset'] = $charset;
    }
    function getBranchLevel()
    {
        return $this->BranchLevel;
    }
    function setBranchLevel($BranchLevel, $charset = 'iso-8859-1')
    {
        $this->BranchLevel = $BranchLevel;
        $this->_elements['BranchLevel']['charset'] = $charset;
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
    function getRecurring()
    {
        return $this->Recurring;
    }
    function setRecurring($Recurring, $charset = 'iso-8859-1')
    {
        $this->Recurring = $Recurring;
        $this->_elements['Recurring']['charset'] = $charset;
    }
}
