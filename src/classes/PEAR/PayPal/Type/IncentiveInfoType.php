<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * IncentiveInfoType
 *
 * @package PayPal
 */
class IncentiveInfoType extends XSDSimpleType
{
    var $ReverseTransactionResponseDetails;

    /**
     * Incentive redemption code.
     */
    var $IncentiveCode;

    /**
     * Defines which bucket or item that the incentive should be applied to.
     */
    var $ApplyIndication;

    function IncentiveInfoType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ReverseTransactionResponseDetails' => 
              array (
                'required' => true,
                'type' => NULL,
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'IncentiveCode' => 
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
            ));
    }

    function getReverseTransactionResponseDetails()
    {
        return $this->ReverseTransactionResponseDetails;
    }
    function setReverseTransactionResponseDetails($ReverseTransactionResponseDetails, $charset = 'iso-8859-1')
    {
        $this->ReverseTransactionResponseDetails = $ReverseTransactionResponseDetails;
        $this->_elements['ReverseTransactionResponseDetails']['charset'] = $charset;
    }
    function getIncentiveCode()
    {
        return $this->IncentiveCode;
    }
    function setIncentiveCode($IncentiveCode, $charset = 'iso-8859-1')
    {
        $this->IncentiveCode = $IncentiveCode;
        $this->_elements['IncentiveCode']['charset'] = $charset;
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
}
