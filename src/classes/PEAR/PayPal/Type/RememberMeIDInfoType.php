<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * RememberMeIDInfoType
 *
 * @package PayPal
 */
class RememberMeIDInfoType extends XSDSimpleType
{
    /**
     * External remember-me ID returned by GetExpressCheckoutDetails on successful
     * opt-in. The ExternalRememberMeID is a 17-character alphanumeric (encrypted)
     * string that identifies the buyer's remembered login with a merchant and has
     * meaning only to the merchant. If present, requests that the web flow attempt
     * bypass of login.
     */
    var $ExternalRememberMeID;

    function RememberMeIDInfoType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ExternalRememberMeID' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getExternalRememberMeID()
    {
        return $this->ExternalRememberMeID;
    }
    function setExternalRememberMeID($ExternalRememberMeID, $charset = 'iso-8859-1')
    {
        $this->ExternalRememberMeID = $ExternalRememberMeID;
        $this->_elements['ExternalRememberMeID']['charset'] = $charset;
    }
}
