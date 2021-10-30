<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * ExternalPartnerTrackingDetailsType
 * 
 * Contains elements that allow tracking for an external partner.
 *
 * @package PayPal
 */
class ExternalPartnerTrackingDetailsType extends XSDSimpleType
{
    /**
     * PayPal will just log this string. There will NOT be any business logic around
     * it, nor any decisions made based on the value of the string that is passed in.
     * From a tracking/analytical perspective, PayPal would not infer any meaning to
     * any specific value. We would just segment the traffic based on the value passed
     * (Cart and None as an example) and track different metrics like risk/conversion
     * etc based on these segments. The external partner would control the value of
     * what gets passed and we take that value as is and generate data based on it.
     */
    var $ExternalPartnerSegmentID;

    function ExternalPartnerTrackingDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ExternalPartnerSegmentID' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getExternalPartnerSegmentID()
    {
        return $this->ExternalPartnerSegmentID;
    }
    function setExternalPartnerSegmentID($ExternalPartnerSegmentID, $charset = 'iso-8859-1')
    {
        $this->ExternalPartnerSegmentID = $ExternalPartnerSegmentID;
        $this->_elements['ExternalPartnerSegmentID']['charset'] = $charset;
    }
}
