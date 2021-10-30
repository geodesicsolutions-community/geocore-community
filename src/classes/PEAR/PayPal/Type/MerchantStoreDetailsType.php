<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * MerchantStoreDetailsType
 *
 * @package PayPal
 */
class MerchantStoreDetailsType extends XSDSimpleType
{
    /**
     * Store ID
     */
    var $StoreID;

    /**
     * Terminal ID
     */
    var $TerminalID;

    function MerchantStoreDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'StoreID' => 
              array (
                'required' => true,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'TerminalID' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getStoreID()
    {
        return $this->StoreID;
    }
    function setStoreID($StoreID, $charset = 'iso-8859-1')
    {
        $this->StoreID = $StoreID;
        $this->_elements['StoreID']['charset'] = $charset;
    }
    function getTerminalID()
    {
        return $this->TerminalID;
    }
    function setTerminalID($TerminalID, $charset = 'iso-8859-1')
    {
        $this->TerminalID = $TerminalID;
        $this->_elements['TerminalID']['charset'] = $charset;
    }
}
