<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * ExternalRememberMeOptInDetailsType
 * 
 * This element contains information that allows the merchant to request to opt
 * into external remember me on behalf of the buyer or to request login bypass
 * using external remember me.
 *
 * @package PayPal
 */
class ExternalRememberMeOptInDetailsType extends XSDSimpleType
{
    /**
     * 1 = opt in to external remember me. 0 or omitted = no opt-in Other values are
     * invalid
     */
    var $ExternalRememberMeOptIn;

    /**
     * E-mail address or secure merchant account ID of merchant to associate with new
     * external remember-me. Currently, the owner must be either the API actor or
     * omitted/none. In the future, we may allow the owner to be a 3rd party merchant
     * account.
     */
    var $ExternalRememberMeOwnerDetails;

    function ExternalRememberMeOptInDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ExternalRememberMeOptIn' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ExternalRememberMeOwnerDetails' => 
              array (
                'required' => false,
                'type' => 'ExternalRememberMeOwnerDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getExternalRememberMeOptIn()
    {
        return $this->ExternalRememberMeOptIn;
    }
    function setExternalRememberMeOptIn($ExternalRememberMeOptIn, $charset = 'iso-8859-1')
    {
        $this->ExternalRememberMeOptIn = $ExternalRememberMeOptIn;
        $this->_elements['ExternalRememberMeOptIn']['charset'] = $charset;
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
