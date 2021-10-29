<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * DeviceDetailsType
 *
 * @package PayPal
 */
class DeviceDetailsType extends XSDSimpleType
{
    /**
     * Device ID
     */
    var $DeviceID;

    function DeviceDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'DeviceID' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getDeviceID()
    {
        return $this->DeviceID;
    }
    function setDeviceID($DeviceID, $charset = 'iso-8859-1')
    {
        $this->DeviceID = $DeviceID;
        $this->_elements['DeviceID']['charset'] = $charset;
    }
}
