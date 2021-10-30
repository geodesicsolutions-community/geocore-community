<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractResponseType.php';

/**
 * GetIncentiveEvaluationResponseType
 *
 * @package PayPal
 */
class GetIncentiveEvaluationResponseType extends AbstractResponseType
{
    var $GetIncentiveEvaluationResponseDetails;

    function GetIncentiveEvaluationResponseType()
    {
        parent::AbstractResponseType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'GetIncentiveEvaluationResponseDetails' => 
              array (
                'required' => true,
                'type' => 'GetIncentiveEvaluationResponseDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getGetIncentiveEvaluationResponseDetails()
    {
        return $this->GetIncentiveEvaluationResponseDetails;
    }
    function setGetIncentiveEvaluationResponseDetails($GetIncentiveEvaluationResponseDetails, $charset = 'iso-8859-1')
    {
        $this->GetIncentiveEvaluationResponseDetails = $GetIncentiveEvaluationResponseDetails;
        $this->_elements['GetIncentiveEvaluationResponseDetails']['charset'] = $charset;
    }
}
