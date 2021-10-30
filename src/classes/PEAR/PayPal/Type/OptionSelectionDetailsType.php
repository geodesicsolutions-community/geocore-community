<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * OptionSelectionDetailsType
 *
 * @package PayPal
 */
class OptionSelectionDetailsType extends XSDSimpleType
{
    /**
     * Option Selection.
     */
    var $OptionSelection;

    /**
     * Option Price.
     */
    var $Price;

    /**
     * Option Type
     */
    var $OptionType;

    var $PaymentPeriod;

    function OptionSelectionDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'OptionSelection' => 
              array (
                'required' => true,
                'type' => 'string',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'Price' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'OptionType' => 
              array (
                'required' => false,
                'type' => 'OptionTypeListType',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'PaymentPeriod' => 
              array (
                'required' => false,
                'type' => 'InstallmentDetailsType',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
            ));
    }

    function getOptionSelection()
    {
        return $this->OptionSelection;
    }
    function setOptionSelection($OptionSelection, $charset = 'iso-8859-1')
    {
        $this->OptionSelection = $OptionSelection;
        $this->_elements['OptionSelection']['charset'] = $charset;
    }
    function getPrice()
    {
        return $this->Price;
    }
    function setPrice($Price, $charset = 'iso-8859-1')
    {
        $this->Price = $Price;
        $this->_elements['Price']['charset'] = $charset;
    }
    function getOptionType()
    {
        return $this->OptionType;
    }
    function setOptionType($OptionType, $charset = 'iso-8859-1')
    {
        $this->OptionType = $OptionType;
        $this->_elements['OptionType']['charset'] = $charset;
    }
    function getPaymentPeriod()
    {
        return $this->PaymentPeriod;
    }
    function setPaymentPeriod($PaymentPeriod, $charset = 'iso-8859-1')
    {
        $this->PaymentPeriod = $PaymentPeriod;
        $this->_elements['PaymentPeriod']['charset'] = $charset;
    }
}
