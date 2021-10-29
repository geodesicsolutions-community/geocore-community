<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * MobileIDInfoType
 *
 * @package PayPal
 */
class MobileIDInfoType extends XSDSimpleType
{
    /**
     * The Session token returned during buyer authentication.
     */
    var $SessionToken;

    function MobileIDInfoType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'SessionToken' => 
              array (
                'required' => true,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getSessionToken()
    {
        return $this->SessionToken;
    }
    function setSessionToken($SessionToken, $charset = 'iso-8859-1')
    {
        $this->SessionToken = $SessionToken;
        $this->_elements['SessionToken']['charset'] = $charset;
    }
}
