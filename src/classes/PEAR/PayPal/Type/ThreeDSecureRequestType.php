<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * ThreeDSecureRequestType
 * 
 * The Common 3DS fields. Common for both GTD and DCC API's.
 *
 * @package PayPal
 */
class ThreeDSecureRequestType extends XSDSimpleType
{
    var $Eci3ds;

    var $Cavv;

    var $Xid;

    var $MpiVendor3ds;

    var $AuthStatus3ds;

    function ThreeDSecureRequestType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'Eci3ds' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Cavv' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Xid' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'MpiVendor3ds' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'AuthStatus3ds' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getEci3ds()
    {
        return $this->Eci3ds;
    }
    function setEci3ds($Eci3ds, $charset = 'iso-8859-1')
    {
        $this->Eci3ds = $Eci3ds;
        $this->_elements['Eci3ds']['charset'] = $charset;
    }
    function getCavv()
    {
        return $this->Cavv;
    }
    function setCavv($Cavv, $charset = 'iso-8859-1')
    {
        $this->Cavv = $Cavv;
        $this->_elements['Cavv']['charset'] = $charset;
    }
    function getXid()
    {
        return $this->Xid;
    }
    function setXid($Xid, $charset = 'iso-8859-1')
    {
        $this->Xid = $Xid;
        $this->_elements['Xid']['charset'] = $charset;
    }
    function getMpiVendor3ds()
    {
        return $this->MpiVendor3ds;
    }
    function setMpiVendor3ds($MpiVendor3ds, $charset = 'iso-8859-1')
    {
        $this->MpiVendor3ds = $MpiVendor3ds;
        $this->_elements['MpiVendor3ds']['charset'] = $charset;
    }
    function getAuthStatus3ds()
    {
        return $this->AuthStatus3ds;
    }
    function setAuthStatus3ds($AuthStatus3ds, $charset = 'iso-8859-1')
    {
        $this->AuthStatus3ds = $AuthStatus3ds;
        $this->_elements['AuthStatus3ds']['charset'] = $charset;
    }
}
