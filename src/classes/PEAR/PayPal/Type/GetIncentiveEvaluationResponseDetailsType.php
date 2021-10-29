<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * GetIncentiveEvaluationResponseDetailsType
 *
 * @package PayPal
 */
class GetIncentiveEvaluationResponseDetailsType extends XSDSimpleType
{
    var $IncentiveDetails;

    var $RequestId;

    function GetIncentiveEvaluationResponseDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'IncentiveDetails' => 
              array (
                'required' => false,
                'type' => 'IncentiveDetailType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'RequestId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getIncentiveDetails()
    {
        return $this->IncentiveDetails;
    }
    function setIncentiveDetails($IncentiveDetails, $charset = 'iso-8859-1')
    {
        $this->IncentiveDetails = $IncentiveDetails;
        $this->_elements['IncentiveDetails']['charset'] = $charset;
    }
    function getRequestId()
    {
        return $this->RequestId;
    }
    function setRequestId($RequestId, $charset = 'iso-8859-1')
    {
        $this->RequestId = $RequestId;
        $this->_elements['RequestId']['charset'] = $charset;
    }
}
