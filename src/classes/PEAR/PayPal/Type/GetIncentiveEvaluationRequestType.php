<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractRequestType.php';

/**
 * GetIncentiveEvaluationRequestType
 *
 * @package PayPal
 */
class GetIncentiveEvaluationRequestType extends AbstractRequestType
{
    var $GetIncentiveEvaluationRequestDetails;

    function GetIncentiveEvaluationRequestType()
    {
        parent::AbstractRequestType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'GetIncentiveEvaluationRequestDetails' => 
              array (
                'required' => true,
                'type' => 'GetIncentiveEvaluationRequestDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getGetIncentiveEvaluationRequestDetails()
    {
        return $this->GetIncentiveEvaluationRequestDetails;
    }
    function setGetIncentiveEvaluationRequestDetails($GetIncentiveEvaluationRequestDetails, $charset = 'iso-8859-1')
    {
        $this->GetIncentiveEvaluationRequestDetails = $GetIncentiveEvaluationRequestDetails;
        $this->_elements['GetIncentiveEvaluationRequestDetails']['charset'] = $charset;
    }
}
