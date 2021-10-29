<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * BuyerDetailType
 *
 * @package PayPal
 */
class BuyerDetailType extends XSDSimpleType
{
    /**
     * Information that is used to indentify the Buyer. This is used for auto
     * authorization. Mandatory if Authorization is requested.
     */
    var $IdentificationInfo;

    function BuyerDetailType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'IdentificationInfo' => 
              array (
                'required' => false,
                'type' => 'IdentificationInfoType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getIdentificationInfo()
    {
        return $this->IdentificationInfo;
    }
    function setIdentificationInfo($IdentificationInfo, $charset = 'iso-8859-1')
    {
        $this->IdentificationInfo = $IdentificationInfo;
        $this->_elements['IdentificationInfo']['charset'] = $charset;
    }
}
