<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * SetDataResponseType
 *
 * @package PayPal
 */
class SetDataResponseType extends XSDSimpleType
{
    /**
     * If Checkout session was initialized successfully, the corresponding token is
     * returned in this element.
     */
    var $Token;

    var $SetDataError;

    function SetDataResponseType()
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
              'SetDataError' => 
              array (
                'required' => false,
                'type' => 'ErrorType',
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
    function getSetDataError()
    {
        return $this->SetDataError;
    }
    function setSetDataError($SetDataError, $charset = 'iso-8859-1')
    {
        $this->SetDataError = $SetDataError;
        $this->_elements['SetDataError']['charset'] = $charset;
    }
}
