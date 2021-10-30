<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * IncentiveBucketType
 *
 * @package PayPal
 */
class IncentiveBucketType extends XSDSimpleType
{
    var $Items;

    var $BucketId;

    var $SellerId;

    var $ExternalSellerId;

    var $BucketSubtotalAmt;

    var $BucketShippingAmt;

    var $BucketInsuranceAmt;

    var $BucketSalesTaxAmt;

    var $BucketTotalAmt;

    function IncentiveBucketType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'Items' => 
              array (
                'required' => false,
                'type' => 'IncentiveItemType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'BucketId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'SellerId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ExternalSellerId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'BucketSubtotalAmt' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'BucketShippingAmt' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'BucketInsuranceAmt' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'BucketSalesTaxAmt' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'BucketTotalAmt' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getItems()
    {
        return $this->Items;
    }
    function setItems($Items, $charset = 'iso-8859-1')
    {
        $this->Items = $Items;
        $this->_elements['Items']['charset'] = $charset;
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
    function getSellerId()
    {
        return $this->SellerId;
    }
    function setSellerId($SellerId, $charset = 'iso-8859-1')
    {
        $this->SellerId = $SellerId;
        $this->_elements['SellerId']['charset'] = $charset;
    }
    function getExternalSellerId()
    {
        return $this->ExternalSellerId;
    }
    function setExternalSellerId($ExternalSellerId, $charset = 'iso-8859-1')
    {
        $this->ExternalSellerId = $ExternalSellerId;
        $this->_elements['ExternalSellerId']['charset'] = $charset;
    }
    function getBucketSubtotalAmt()
    {
        return $this->BucketSubtotalAmt;
    }
    function setBucketSubtotalAmt($BucketSubtotalAmt, $charset = 'iso-8859-1')
    {
        $this->BucketSubtotalAmt = $BucketSubtotalAmt;
        $this->_elements['BucketSubtotalAmt']['charset'] = $charset;
    }
    function getBucketShippingAmt()
    {
        return $this->BucketShippingAmt;
    }
    function setBucketShippingAmt($BucketShippingAmt, $charset = 'iso-8859-1')
    {
        $this->BucketShippingAmt = $BucketShippingAmt;
        $this->_elements['BucketShippingAmt']['charset'] = $charset;
    }
    function getBucketInsuranceAmt()
    {
        return $this->BucketInsuranceAmt;
    }
    function setBucketInsuranceAmt($BucketInsuranceAmt, $charset = 'iso-8859-1')
    {
        $this->BucketInsuranceAmt = $BucketInsuranceAmt;
        $this->_elements['BucketInsuranceAmt']['charset'] = $charset;
    }
    function getBucketSalesTaxAmt()
    {
        return $this->BucketSalesTaxAmt;
    }
    function setBucketSalesTaxAmt($BucketSalesTaxAmt, $charset = 'iso-8859-1')
    {
        $this->BucketSalesTaxAmt = $BucketSalesTaxAmt;
        $this->_elements['BucketSalesTaxAmt']['charset'] = $charset;
    }
    function getBucketTotalAmt()
    {
        return $this->BucketTotalAmt;
    }
    function setBucketTotalAmt($BucketTotalAmt, $charset = 'iso-8859-1')
    {
        $this->BucketTotalAmt = $BucketTotalAmt;
        $this->_elements['BucketTotalAmt']['charset'] = $charset;
    }
}
