<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * PaymentTransactionType
 * 
 * PaymentTransactionType Information about a PayPal payment from the seller side
 *
 * @package PayPal
 */
class PaymentTransactionType extends XSDSimpleType
{
    /**
     * Information about the recipient of the payment
     */
    var $ReceiverInfo;

    /**
     * Information about the payer
     */
    var $PayerInfo;

    /**
     * Information about the transaction
     */
    var $PaymentInfo;

    /**
     * Information about an individual item in the transaction
     */
    var $PaymentItemInfo;

    /**
     * Information about the user selected options.
     */
    var $UserSelectedOptions;

    /**
     * Information about the Gift message.
     */
    var $GiftMessage;

    /**
     * Information about the Gift receipt.
     */
    var $GiftReceipt;

    /**
     * Information about the Gift Wrap name.
     */
    var $GiftWrapName;

    /**
     * Information about the Gift Wrap amount.
     */
    var $GiftWrapAmount;

    /**
     * Information about the Buyer email.
     */
    var $BuyerEmailOptIn;

    /**
     * Information about the survey question.
     */
    var $SurveyQuestion;

    /**
     * Information about the survey choice selected by the user.
     */
    var $SurveyChoiceSelected;

    function PaymentTransactionType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ReceiverInfo' => 
              array (
                'required' => true,
                'type' => 'ReceiverInfoType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PayerInfo' => 
              array (
                'required' => true,
                'type' => 'PayerInfoType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PaymentInfo' => 
              array (
                'required' => true,
                'type' => 'PaymentInfoType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PaymentItemInfo' => 
              array (
                'required' => false,
                'type' => 'PaymentItemInfoType',
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
              'GiftReceipt' => 
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
              'BuyerEmailOptIn' => 
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
            ));
    }

    function getReceiverInfo()
    {
        return $this->ReceiverInfo;
    }
    function setReceiverInfo($ReceiverInfo, $charset = 'iso-8859-1')
    {
        $this->ReceiverInfo = $ReceiverInfo;
        $this->_elements['ReceiverInfo']['charset'] = $charset;
    }
    function getPayerInfo()
    {
        return $this->PayerInfo;
    }
    function setPayerInfo($PayerInfo, $charset = 'iso-8859-1')
    {
        $this->PayerInfo = $PayerInfo;
        $this->_elements['PayerInfo']['charset'] = $charset;
    }
    function getPaymentInfo()
    {
        return $this->PaymentInfo;
    }
    function setPaymentInfo($PaymentInfo, $charset = 'iso-8859-1')
    {
        $this->PaymentInfo = $PaymentInfo;
        $this->_elements['PaymentInfo']['charset'] = $charset;
    }
    function getPaymentItemInfo()
    {
        return $this->PaymentItemInfo;
    }
    function setPaymentItemInfo($PaymentItemInfo, $charset = 'iso-8859-1')
    {
        $this->PaymentItemInfo = $PaymentItemInfo;
        $this->_elements['PaymentItemInfo']['charset'] = $charset;
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
    function getGiftReceipt()
    {
        return $this->GiftReceipt;
    }
    function setGiftReceipt($GiftReceipt, $charset = 'iso-8859-1')
    {
        $this->GiftReceipt = $GiftReceipt;
        $this->_elements['GiftReceipt']['charset'] = $charset;
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
    function getBuyerEmailOptIn()
    {
        return $this->BuyerEmailOptIn;
    }
    function setBuyerEmailOptIn($BuyerEmailOptIn, $charset = 'iso-8859-1')
    {
        $this->BuyerEmailOptIn = $BuyerEmailOptIn;
        $this->_elements['BuyerEmailOptIn']['charset'] = $charset;
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
}
