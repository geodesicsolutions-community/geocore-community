<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * AuthorizationResponseType
 *
 * @package PayPal
 */
class AuthorizationResponseType extends XSDSimpleType
{
    /**
     * Status will denote whether Auto authorization was successful or not.
     */
    var $Status;

    var $AuthorizationError;

    function AuthorizationResponseType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'Status' => 
              array (
                'required' => true,
                'type' => 'AckCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'AuthorizationError' => 
              array (
                'required' => false,
                'type' => 'ErrorType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
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
    function getAuthorizationError()
    {
        return $this->AuthorizationError;
    }
    function setAuthorizationError($AuthorizationError, $charset = 'iso-8859-1')
    {
        $this->AuthorizationError = $AuthorizationError;
        $this->_elements['AuthorizationError']['charset'] = $charset;
    }
}
