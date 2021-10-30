<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * ExternalRememberMeOwnerDetailsType
 * 
 * E-mail address or secure merchant account ID of merchant to associate with new
 * external remember-me.
 *
 * @package PayPal
 */
class ExternalRememberMeOwnerDetailsType extends XSDSimpleType
{
    /**
     * A discriminant that tells SetEC what kind of data the ExternalRememberMeOwnerID
     * parameter contains. Currently, the owner must be either the API actor or
     * omitted/none. In the future, we may allow the owner to be a 3rd party merchant
     * account. Possible values are: None, ignore the ExternalRememberMeOwnerID. An
     * empty value for this field also signifies None. Email, the owner ID is an email
     * address SecureMerchantAccountID, the owner id is a string representing the
     * secure merchant account ID
     */
    var $ExternalRememberMeOwnerIDType;

    /**
     * When opting in to bypass login via remember me, this parameter specifies the
     * merchant account associated with the remembered login. Currentl, the owner must
     * be either the API actor or omitted/none. In the future, we may allow the owner
     * to be a 3rd party merchant account. If the Owner ID Type field is not present or
     * "None", this parameter is ignored.
     */
    var $ExternalRememberMeOwnerID;

    function ExternalRememberMeOwnerDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ExternalRememberMeOwnerIDType' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ExternalRememberMeOwnerID' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getExternalRememberMeOwnerIDType()
    {
        return $this->ExternalRememberMeOwnerIDType;
    }
    function setExternalRememberMeOwnerIDType($ExternalRememberMeOwnerIDType, $charset = 'iso-8859-1')
    {
        $this->ExternalRememberMeOwnerIDType = $ExternalRememberMeOwnerIDType;
        $this->_elements['ExternalRememberMeOwnerIDType']['charset'] = $charset;
    }
    function getExternalRememberMeOwnerID()
    {
        return $this->ExternalRememberMeOwnerID;
    }
    function setExternalRememberMeOwnerID($ExternalRememberMeOwnerID, $charset = 'iso-8859-1')
    {
        $this->ExternalRememberMeOwnerID = $ExternalRememberMeOwnerID;
        $this->_elements['ExternalRememberMeOwnerID']['charset'] = $charset;
    }
}
