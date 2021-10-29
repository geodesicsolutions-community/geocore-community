<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * ExecuteCheckoutOperationsRequestDetailsType
 *
 * @package PayPal
 */
class ExecuteCheckoutOperationsRequestDetailsType extends XSDSimpleType
{
    /**
     * On your first invocation of ExecuteCheckoutOperationsRequest, the value of this
     * token is returned by ExecuteCheckoutOperationsResponse.
     */
    var $Token;

    /**
     * All the Data required to initiate the checkout session is passed in this
     * element.
     */
    var $SetDataRequest;

    /**
     * If auto authorization is required, this should be passed in with IsRequested set
     * to yes.
     */
    var $AuthorizationRequest;

    function ExecuteCheckoutOperationsRequestDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'Token' => 
              array (
                'required' => false,
                'type' => 'ExpressCheckoutTokenType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'SetDataRequest' => 
              array (
                'required' => true,
                'type' => 'SetDataRequestType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'AuthorizationRequest' => 
              array (
                'required' => false,
                'type' => 'AuthorizationRequestType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
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
    function getSetDataRequest()
    {
        return $this->SetDataRequest;
    }
    function setSetDataRequest($SetDataRequest, $charset = 'iso-8859-1')
    {
        $this->SetDataRequest = $SetDataRequest;
        $this->_elements['SetDataRequest']['charset'] = $charset;
    }
    function getAuthorizationRequest()
    {
        return $this->AuthorizationRequest;
    }
    function setAuthorizationRequest($AuthorizationRequest, $charset = 'iso-8859-1')
    {
        $this->AuthorizationRequest = $AuthorizationRequest;
        $this->_elements['AuthorizationRequest']['charset'] = $charset;
    }
}
