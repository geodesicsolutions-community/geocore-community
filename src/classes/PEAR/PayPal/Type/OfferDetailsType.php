<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * OfferDetailsType
 * 
 * OfferDetailsType Specific information for an offer.
 *
 * @package PayPal
 */
class OfferDetailsType extends XSDSimpleType
{
    /**
     * Code used to identify the promotion offer.
     */
    var $OfferCode;

    /**
     * Specific infromation for BML, Similar structure could be added for sepcific
     */
    var $BMLOfferInfo;

    function OfferDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'OfferCode' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'BMLOfferInfo' => 
              array (
                'required' => false,
                'type' => 'BMLOfferInfoType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getOfferCode()
    {
        return $this->OfferCode;
    }
    function setOfferCode($OfferCode, $charset = 'iso-8859-1')
    {
        $this->OfferCode = $OfferCode;
        $this->_elements['OfferCode']['charset'] = $charset;
    }
    function getBMLOfferInfo()
    {
        return $this->BMLOfferInfo;
    }
    function setBMLOfferInfo($BMLOfferInfo, $charset = 'iso-8859-1')
    {
        $this->BMLOfferInfo = $BMLOfferInfo;
        $this->_elements['BMLOfferInfo']['charset'] = $charset;
    }
}
