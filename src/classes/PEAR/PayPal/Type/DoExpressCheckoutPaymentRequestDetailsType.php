<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * DoExpressCheckoutPaymentRequestDetailsType
 *
 * @package PayPal
 */
class DoExpressCheckoutPaymentRequestDetailsType extends XSDSimpleType
{
    /**
     * How you want to obtain payment.
     */
    var $PaymentAction;

    /**
     * The timestamped token value that was returned by SetExpressCheckoutResponse and
     * passed on GetExpressCheckoutDetailsRequest.
     */
    var $Token;

    /**
     * Encrypted PayPal customer account identification number as returned by
     * GetExpressCheckoutDetailsResponse.
     */
    var $PayerID;

    /**
     * URL on Merchant site pertaining to this invoice.
     */
    var $OrderURL;

    /**
     * Information about the payment
     */
    var $PaymentDetails;

    /**
     * Flag to indicate if previously set promoCode shall be overriden. Value 1
     * indicates overriding.
     */
    var $PromoOverrideFlag;

    /**
     * Promotional financing code for item. Overrides any previous PromoCode setting.
     */
    var $PromoCode;

    /**
     * Contains data for enhanced data like Airline Itinerary Data.
     */
    var $EnhancedData;

    /**
     * Soft Descriptor supported for Sale and Auth in DEC only. For Order this will be
     * ignored.
     */
    var $SoftDescriptor;

    /**
     * Information about the user selected options.
     */
    var $UserSelectedOptions;

    /**
     * Information about the Gift message.
     */
    var $GiftMessage;

    /**
     * Information about the Gift receipt enable.
     */
    var $GiftReceiptEnable;

    /**
     * Information about the Gift Wrap name.
     */
    var $GiftWrapName;

    /**
     * Information about the Gift Wrap amount.
     */
    var $GiftWrapAmount;

    /**
     * Information about the Buyer marketing email.
     */
    var $BuyerMarketingEmail;

    /**
     * Information about the survey question.
     */
    var $SurveyQuestion;

    /**
     * Information about the survey choice selected by the user.
     */
    var $SurveyChoiceSelected;

    /**
     * An identification code for use by third-party applications to identify
     * transactions.
     */
    var $ButtonSource;

    /**
     * Merchant specified flag which indicates whether to create billing agreement as
     * part of DoEC or not.
     */
    var $SkipBACreation;

    function DoExpressCheckoutPaymentRequestDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'PaymentAction' => 
              array (
                'required' => false,
                'type' => 'PaymentActionCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Token' => 
              array (
                'required' => true,
                'type' => 'ExpressCheckoutTokenType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PayerID' => 
              array (
                'required' => true,
                'type' => 'UserIDType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'OrderURL' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PaymentDetails' => 
              array (
                'required' => false,
                'type' => 'PaymentDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PromoOverrideFlag' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PromoCode' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'EnhancedData' => 
              array (
                'required' => false,
                'type' => 'EnhancedDataType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'SoftDescriptor' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'UserSelectedOptions' => 
              array (
                'required' => false,
                'type' => 'UserSelectedOptionType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'GiftMessage' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'GiftReceiptEnable' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'GiftWrapName' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'GiftWrapAmount' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'BuyerMarketingEmail' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'SurveyQuestion' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'SurveyChoiceSelected' => 
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
              'SkipBACreation' => 
              array (
                'required' => false,
                'type' => 'boolean',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
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
    function getToken()
    {
        return $this->Token;
    }
    function setToken($Token, $charset = 'iso-8859-1')
    {
        $this->Token = $Token;
        $this->_elements['Token']['charset'] = $charset;
    }
    function getPayerID()
    {
        return $this->PayerID;
    }
    function setPayerID($PayerID, $charset = 'iso-8859-1')
    {
        $this->PayerID = $PayerID;
        $this->_elements['PayerID']['charset'] = $charset;
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
    function getPaymentDetails()
    {
        return $this->PaymentDetails;
    }
    function setPaymentDetails($PaymentDetails, $charset = 'iso-8859-1')
    {
        $this->PaymentDetails = $PaymentDetails;
        $this->_elements['PaymentDetails']['charset'] = $charset;
    }
    function getPromoOverrideFlag()
    {
        return $this->PromoOverrideFlag;
    }
    function setPromoOverrideFlag($PromoOverrideFlag, $charset = 'iso-8859-1')
    {
        $this->PromoOverrideFlag = $PromoOverrideFlag;
        $this->_elements['PromoOverrideFlag']['charset'] = $charset;
    }
    function getPromoCode()
    {
        return $this->PromoCode;
    }
    function setPromoCode($PromoCode, $charset = 'iso-8859-1')
    {
        $this->PromoCode = $PromoCode;
        $this->_elements['PromoCode']['charset'] = $charset;
    }
    function getEnhancedData()
    {
        return $this->EnhancedData;
    }
    function setEnhancedData($EnhancedData, $charset = 'iso-8859-1')
    {
        $this->EnhancedData = $EnhancedData;
        $this->_elements['EnhancedData']['charset'] = $charset;
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
    function getUserSelectedOptions()
    {
        return $this->UserSelectedOptions;
    }
    function setUserSelectedOptions($UserSelectedOptions, $charset = 'iso-8859-1')
    {
        $this->UserSelectedOptions = $UserSelectedOptions;
        $this->_elements['UserSelectedOptions']['charset'] = $charset;
    }
    function getGiftMessage()
    {
        return $this->GiftMessage;
    }
    function setGiftMessage($GiftMessage, $charset = 'iso-8859-1')
    {
        $this->GiftMessage = $GiftMessage;
        $this->_elements['GiftMessage']['charset'] = $charset;
    }
    function getGiftReceiptEnable()
    {
        return $this->GiftReceiptEnable;
    }
    function setGiftReceiptEnable($GiftReceiptEnable, $charset = 'iso-8859-1')
    {
        $this->GiftReceiptEnable = $GiftReceiptEnable;
        $this->_elements['GiftReceiptEnable']['charset'] = $charset;
    }
    function getGiftWrapName()
    {
        return $this->GiftWrapName;
    }
    function setGiftWrapName($GiftWrapName, $charset = 'iso-8859-1')
    {
        $this->GiftWrapName = $GiftWrapName;
        $this->_elements['GiftWrapName']['charset'] = $charset;
    }
    function getGiftWrapAmount()
    {
        return $this->GiftWrapAmount;
    }
    function setGiftWrapAmount($GiftWrapAmount, $charset = 'iso-8859-1')
    {
        $this->GiftWrapAmount = $GiftWrapAmount;
        $this->_elements['GiftWrapAmount']['charset'] = $charset;
    }
    function getBuyerMarketingEmail()
    {
        return $this->BuyerMarketingEmail;
    }
    function setBuyerMarketingEmail($BuyerMarketingEmail, $charset = 'iso-8859-1')
    {
        $this->BuyerMarketingEmail = $BuyerMarketingEmail;
        $this->_elements['BuyerMarketingEmail']['charset'] = $charset;
    }
    function getSurveyQuestion()
    {
        return $this->SurveyQuestion;
    }
    function setSurveyQuestion($SurveyQuestion, $charset = 'iso-8859-1')
    {
        $this->SurveyQuestion = $SurveyQuestion;
        $this->_elements['SurveyQuestion']['charset'] = $charset;
    }
    function getSurveyChoiceSelected()
    {
        return $this->SurveyChoiceSelected;
    }
    function setSurveyChoiceSelected($SurveyChoiceSelected, $charset = 'iso-8859-1')
    {
        $this->SurveyChoiceSelected = $SurveyChoiceSelected;
        $this->_elements['SurveyChoiceSelected']['charset'] = $charset;
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
    function getSkipBACreation()
    {
        return $this->SkipBACreation;
    }
    function setSkipBACreation($SkipBACreation, $charset = 'iso-8859-1')
    {
        $this->SkipBACreation = $SkipBACreation;
        $this->_elements['SkipBACreation']['charset'] = $charset;
    }
}
