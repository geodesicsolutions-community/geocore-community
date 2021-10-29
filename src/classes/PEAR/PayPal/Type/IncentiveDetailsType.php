<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * IncentiveDetailsType
 * 
 * Information about the incentives that were applied from Ebay RYP page and PayPal
 * RYP page.
 *
 * @package PayPal
 */
class IncentiveDetailsType extends XSDSimpleType
{
    /**
     * Unique Identifier consisting of redemption code, user friendly descripotion,
     * incentive type, campaign code, incenitve application order and site redeemed o
     * n.
     */
    var $UniqueIdentifier;

    /**
     * Defines if the incentive has been applied on Ebay or PayPal.
     */
    var $SiteAppliedOn;

    /**
     * The total discount amount for the incentive, summation of discounts up across
     * all the buckets/items.
     */
    var $TotalDiscountAmount;

    /**
     * Status of incentive processing. Sussess or Error.
     */
    var $Status;

    /**
     * Error code if there are any errors. Zero otherwise.
     */
    var $ErrorCode;

    /**
     * Details of incentive application on individual bucket/item.
     */
    var $IncentiveAppliedDetails;

    function IncentiveDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'UniqueIdentifier' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'SiteAppliedOn' => 
              array (
                'required' => false,
                'type' => 'IncentiveSiteAppliedOnType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'TotalDiscountAmount' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Status' => 
              array (
                'required' => false,
                'type' => 'IncentiveAppliedStatusType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ErrorCode' => 
              array (
                'required' => false,
                'type' => 'integer',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'IncentiveAppliedDetails' => 
              array (
                'required' => false,
                'type' => 'IncentiveAppliedDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getUniqueIdentifier()
    {
        return $this->UniqueIdentifier;
    }
    function setUniqueIdentifier($UniqueIdentifier, $charset = 'iso-8859-1')
    {
        $this->UniqueIdentifier = $UniqueIdentifier;
        $this->_elements['UniqueIdentifier']['charset'] = $charset;
    }
    function getSiteAppliedOn()
    {
        return $this->SiteAppliedOn;
    }
    function setSiteAppliedOn($SiteAppliedOn, $charset = 'iso-8859-1')
    {
        $this->SiteAppliedOn = $SiteAppliedOn;
        $this->_elements['SiteAppliedOn']['charset'] = $charset;
    }
    function getTotalDiscountAmount()
    {
        return $this->TotalDiscountAmount;
    }
    function setTotalDiscountAmount($TotalDiscountAmount, $charset = 'iso-8859-1')
    {
        $this->TotalDiscountAmount = $TotalDiscountAmount;
        $this->_elements['TotalDiscountAmount']['charset'] = $charset;
    }
    function getStatus()
    {
        return $this->Status;
    }
    function setStatus($Status, $charset = 'iso-8859-1')
    {
        $this->Status = $Status;
        $this->_elements['Status']['charset'] = $charset;
    }
    function getErrorCode()
    {
        return $this->ErrorCode;
    }
    function setErrorCode($ErrorCode, $charset = 'iso-8859-1')
    {
        $this->ErrorCode = $ErrorCode;
        $this->_elements['ErrorCode']['charset'] = $charset;
    }
    function getIncentiveAppliedDetails()
    {
        return $this->IncentiveAppliedDetails;
    }
    function setIncentiveAppliedDetails($IncentiveAppliedDetails, $charset = 'iso-8859-1')
    {
        $this->IncentiveAppliedDetails = $IncentiveAppliedDetails;
        $this->_elements['IncentiveAppliedDetails']['charset'] = $charset;
    }
}
