<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * IncentiveAppliedToType
 *
 * @package PayPal
 */
class IncentiveAppliedToType extends XSDSimpleType
{
    var $BucketId;

    var $ItemId;

    var $IncentiveAmount;

    var $SubType;

    function IncentiveAppliedToType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'BucketId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ItemId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'IncentiveAmount' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'SubType' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getBucketId()
    {
        return $this->BucketId;
    }
    function setBucketId($BucketId, $charset = 'iso-8859-1')
    {
        $this->BucketId = $BucketId;
        $this->_elements['BucketId']['charset'] = $charset;
    }
    function getItemId()
    {
        return $this->ItemId;
    }
    function setItemId($ItemId, $charset = 'iso-8859-1')
    {
        $this->ItemId = $ItemId;
        $this->_elements['ItemId']['charset'] = $charset;
    }
    function getIncentiveAmount()
    {
        return $this->IncentiveAmount;
    }
    function setIncentiveAmount($IncentiveAmount, $charset = 'iso-8859-1')
    {
        $this->IncentiveAmount = $IncentiveAmount;
        $this->_elements['IncentiveAmount']['charset'] = $charset;
    }
    function getSubType()
    {
        return $this->SubType;
    }
    function setSubType($SubType, $charset = 'iso-8859-1')
    {
        $this->SubType = $SubType;
        $this->_elements['SubType']['charset'] = $charset;
    }
}
