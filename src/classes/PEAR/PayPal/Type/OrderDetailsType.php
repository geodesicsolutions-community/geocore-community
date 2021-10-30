<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * OrderDetailsType
 *
 * @package PayPal
 */
class OrderDetailsType extends XSDSimpleType
{
    /**
     * Description of the Order.
     */
    var $Description;

    /**
     * Expected maximum amount that the merchant may pull using DoReferenceTransaction
     */
    var $MaxAmount;

    function OrderDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'Description' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'MaxAmount' => 
              array (
                'required' => false,
                'type' => 'BasicAmountType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getDescription()
    {
        return $this->Description;
    }
    function setDescription($Description, $charset = 'iso-8859-1')
    {
        $this->Description = $Description;
        $this->_elements['Description']['charset'] = $charset;
    }
    function getMaxAmount()
    {
        return $this->MaxAmount;
    }
    function setMaxAmount($MaxAmount, $charset = 'iso-8859-1')
    {
        $this->MaxAmount = $MaxAmount;
        $this->_elements['MaxAmount']['charset'] = $charset;
    }
}
