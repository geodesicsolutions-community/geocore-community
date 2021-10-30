<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * ExternalRememberMeStatusDetailsType
 * 
 * Response information resulting from opt-in operation or current login bypass
 * status.
 *
 * @package PayPal
 */
class ExternalRememberMeStatusDetailsType extends XSDSimpleType
{
    /**
     * Required field that reports status of opt-in or login bypass attempt. 0 =
     * Success - successful opt-in or ExternalRememberMeID specified in
     * SetExpressCheckout is valid. 1 = Invalid ID - ExternalRememberMeID specified in
     * SetExpressCheckout is invalid. 2 = Internal Error - System error or outage
     * during opt-in or login bypass. Can retry opt-in or login bypass next time. Flow
     * will force full authentication and allow buyer to complete transaction. -1 =
     * None - the return value does not signify any valid remember me status.
     */
    var $ExternalRememberMeStatus;

    /**
     * Identifier returned on external-remember-me-opt-in to allow the merchant to
     * request bypass of PayPal login through external remember me on behalf of the
     * buyer in future transactions. The ExternalRememberMeID is a 17-character
     * alphanumeric (encrypted) string. This field has meaning only to the merchant.
     */
    var $ExternalRememberMeID;

    function ExternalRememberMeStatusDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ExternalRememberMeStatus' => 
              array (
                'required' => true,
                'type' => 'integer',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ExternalRememberMeID' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getExternalRememberMeStatus()
    {
        return $this->ExternalRememberMeStatus;
    }
    function setExternalRememberMeStatus($ExternalRememberMeStatus, $charset = 'iso-8859-1')
    {
        $this->ExternalRememberMeStatus = $ExternalRememberMeStatus;
        $this->_elements['ExternalRememberMeStatus']['charset'] = $charset;
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
