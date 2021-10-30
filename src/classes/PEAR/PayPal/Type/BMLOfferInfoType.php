<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * BMLOfferInfoType
 * 
 * BMLOfferInfoType Specific information for BML.
 *
 * @package PayPal
 */
class BMLOfferInfoType extends XSDSimpleType
{
    /**
     * Unique identification for merchant/buyer/offer combo.
     */
    var $OfferTrackingID;

    function BMLOfferInfoType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'OfferTrackingID' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getOfferTrackingID()
    {
        return $this->OfferTrackingID;
    }
    function setOfferTrackingID($OfferTrackingID, $charset = 'iso-8859-1')
    {
        $this->OfferTrackingID = $OfferTrackingID;
        $this->_elements['OfferTrackingID']['charset'] = $charset;
    }
}
