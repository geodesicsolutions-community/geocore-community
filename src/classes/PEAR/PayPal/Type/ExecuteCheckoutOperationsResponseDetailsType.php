<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * ExecuteCheckoutOperationsResponseDetailsType
 *
 * @package PayPal
 */
class ExecuteCheckoutOperationsResponseDetailsType extends XSDSimpleType
{
    var $SetDataResponse;

    var $AuthorizationResponse;

    function ExecuteCheckoutOperationsResponseDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'SetDataResponse' => 
              array (
                'required' => true,
                'type' => 'SetDataResponseType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'AuthorizationResponse' => 
              array (
                'required' => false,
                'type' => 'AuthorizationResponseType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getSetDataResponse()
    {
        return $this->SetDataResponse;
    }
    function setSetDataResponse($SetDataResponse, $charset = 'iso-8859-1')
    {
        $this->SetDataResponse = $SetDataResponse;
        $this->_elements['SetDataResponse']['charset'] = $charset;
    }
    function getAuthorizationResponse()
    {
        return $this->AuthorizationResponse;
    }
    function setAuthorizationResponse($AuthorizationResponse, $charset = 'iso-8859-1')
    {
        $this->AuthorizationResponse = $AuthorizationResponse;
        $this->_elements['AuthorizationResponse']['charset'] = $charset;
    }
}
