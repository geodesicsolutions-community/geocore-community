<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * SenderDetailsType
 *
 * @package PayPal
 */
class SenderDetailsType extends XSDSimpleType
{
    var $DeviceDetails;

    function SenderDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'DeviceDetails' => 
              array (
                'required' => false,
                'type' => 'DeviceDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getDeviceDetails()
    {
        return $this->DeviceDetails;
    }
    function setDeviceDetails($DeviceDetails, $charset = 'iso-8859-1')
    {
        $this->DeviceDetails = $DeviceDetails;
        $this->_elements['DeviceDetails']['charset'] = $charset;
    }
}
