<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractRequestType.php';

/**
 * ExternalRememberMeOptOutRequestType
 *
 * @package PayPal
 */
class ExternalRememberMeOptOutRequestType extends AbstractRequestType
{
    /**
     * The merchant passes in the ExternalRememberMeID to identify the user to opt out.
     * This is a 17-character alphanumeric (encrypted) string that identifies the
     * buyer's remembered login with a merchant and has meaning only to the merchant.
     */
    var $ExternalRememberMeID;

    /**
     * E-mail address or secure merchant account ID of merchant to associate with
     * external remember-me.
     */
    var $ExternalRememberMeOwnerDetails;

    function ExternalRememberMeOptOutRequestType()
    {
        parent::AbstractRequestType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'ExternalRememberMeID' => 
              array (
                'required' => true,
                'type' => 'string',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'ExternalRememberMeOwnerDetails' => 
              array (
                'required' => false,
                'type' => 'ExternalRememberMeOwnerDetailsType',
                'namespace' => 'urn:ebay:api:PayPalAPI',
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
    function getExternalRememberMeOwnerDetails()
    {
        return $this->ExternalRememberMeOwnerDetails;
    }
    function setExternalRememberMeOwnerDetails($ExternalRememberMeOwnerDetails, $charset = 'iso-8859-1')
    {
        $this->ExternalRememberMeOwnerDetails = $ExternalRememberMeOwnerDetails;
        $this->_elements['ExternalRememberMeOwnerDetails']['charset'] = $charset;
    }
}
