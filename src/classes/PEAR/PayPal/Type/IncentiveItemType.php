<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * IncentiveItemType
 *
 * @package PayPal
 */
class IncentiveItemType extends XSDSimpleType
{
    var $ItemId;

    var $PurchaseTime;

    var $ItemCategoryList;

    var $ItemPrice;

    var $ItemQuantity;

    function IncentiveItemType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ItemId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'PurchaseTime' => 
              array (
                'required' => false,
                'type' => 'dateTime',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ItemCategoryList' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ItemPrice' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ItemQuantity' => 
              array (
                'required' => false,
                'type' => 'integer',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
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
    function getPurchaseTime()
    {
        return $this->PurchaseTime;
    }
    function setPurchaseTime($PurchaseTime, $charset = 'iso-8859-1')
    {
        $this->PurchaseTime = $PurchaseTime;
        $this->_elements['PurchaseTime']['charset'] = $charset;
    }
    function getItemCategoryList()
    {
        return $this->ItemCategoryList;
    }
    function setItemCategoryList($ItemCategoryList, $charset = 'iso-8859-1')
    {
        $this->ItemCategoryList = $ItemCategoryList;
        $this->_elements['ItemCategoryList']['charset'] = $charset;
    }
    function getItemPrice()
    {
        return $this->ItemPrice;
    }
    function setItemPrice($ItemPrice, $charset = 'iso-8859-1')
    {
        $this->ItemPrice = $ItemPrice;
        $this->_elements['ItemPrice']['charset'] = $charset;
    }
    function getItemQuantity()
    {
        return $this->ItemQuantity;
    }
    function setItemQuantity($ItemQuantity, $charset = 'iso-8859-1')
    {
        $this->ItemQuantity = $ItemQuantity;
        $this->_elements['ItemQuantity']['charset'] = $charset;
    }
}
