<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * AuthorizationRequestType
 *
 * @package PayPal
 */
class AuthorizationRequestType extends XSDSimpleType
{
    var $IsRequested;

    function AuthorizationRequestType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'IsRequested' => 
              array (
                'required' => true,
                'type' => 'boolean',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getIsRequested()
    {
        return $this->IsRequested;
    }
    function setIsRequested($IsRequested, $charset = 'iso-8859-1')
    {
        $this->IsRequested = $IsRequested;
        $this->_elements['IsRequested']['charset'] = $charset;
    }
}
