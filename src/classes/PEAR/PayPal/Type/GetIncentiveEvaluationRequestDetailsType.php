<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * GetIncentiveEvaluationRequestDetailsType
 *
 * @package PayPal
 */
class GetIncentiveEvaluationRequestDetailsType extends XSDSimpleType
{
    var $ExternalBuyerId;

    var $IncentiveCodes;

    var $ApplyIndication;

    var $Buckets;

    var $CartTotalAmt;

    var $RequestDetails;

    function GetIncentiveEvaluationRequestDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ExternalBuyerId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'IncentiveCodes' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ApplyIndication' => 
              array (
                'required' => false,
                'type' => 'IncentiveApplyIndicationType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Buckets' => 
              array (
                'required' => false,
                'type' => 'IncentiveBucketType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'CartTotalAmt' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'RequestDetails' => 
              array (
                'required' => false,
                'type' => 'IncentiveRequestDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getExternalBuyerId()
    {
        return $this->ExternalBuyerId;
    }
    function setExternalBuyerId($ExternalBuyerId, $charset = 'iso-8859-1')
    {
        $this->ExternalBuyerId = $ExternalBuyerId;
        $this->_elements['ExternalBuyerId']['charset'] = $charset;
    }
    function getIncentiveCodes()
    {
        return $this->IncentiveCodes;
    }
    function setIncentiveCodes($IncentiveCodes, $charset = 'iso-8859-1')
    {
        $this->IncentiveCodes = $IncentiveCodes;
        $this->_elements['IncentiveCodes']['charset'] = $charset;
    }
    function getApplyIndication()
    {
        return $this->ApplyIndication;
    }
    function setApplyIndication($ApplyIndication, $charset = 'iso-8859-1')
    {
        $this->ApplyIndication = $ApplyIndication;
        $this->_elements['ApplyIndication']['charset'] = $charset;
    }
    function getBuckets()
    {
        return $this->Buckets;
    }
    function setBuckets($Buckets, $charset = 'iso-8859-1')
    {
        $this->Buckets = $Buckets;
        $this->_elements['Buckets']['charset'] = $charset;
    }
    function getCartTotalAmt()
    {
        return $this->CartTotalAmt;
    }
    function setCartTotalAmt($CartTotalAmt, $charset = 'iso-8859-1')
    {
        $this->CartTotalAmt = $CartTotalAmt;
        $this->_elements['CartTotalAmt']['charset'] = $charset;
    }
    function getRequestDetails()
    {
        return $this->RequestDetails;
    }
    function setRequestDetails($RequestDetails, $charset = 'iso-8859-1')
    {
        $this->RequestDetails = $RequestDetails;
        $this->_elements['RequestDetails']['charset'] = $charset;
    }
}
