<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * InstallmentDetailsType
 *
 * @package PayPal
 */
class InstallmentDetailsType extends XSDSimpleType
{
    /**
     * Installment Period.
     */
    var $BillingPeriod;

    /**
     * Installment Frequency.
     */
    var $BillingFrequency;

    /**
     * Installment Cycles.
     */
    var $TotalBillingCycles;

    /**
     * Installment Amount.
     */
    var $Amount;

    /**
     * Installment Amount.
     */
    var $ShippingAmount;

    /**
     * Installment Amount.
     */
    var $TaxAmount;

    function InstallmentDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'BillingPeriod' => 
              array (
                'required' => false,
                'type' => 'BillingPeriodType',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'BillingFrequency' => 
              array (
                'required' => false,
                'type' => 'int',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'TotalBillingCycles' => 
              array (
                'required' => false,
                'type' => 'int',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'Amount' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'ShippingAmount' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
              'TaxAmount' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:api:PayPalAPI',
              ),
            ));
    }

    function getBillingPeriod()
    {
        return $this->BillingPeriod;
    }
    function setBillingPeriod($BillingPeriod, $charset = 'iso-8859-1')
    {
        $this->BillingPeriod = $BillingPeriod;
        $this->_elements['BillingPeriod']['charset'] = $charset;
    }
    function getBillingFrequency()
    {
        return $this->BillingFrequency;
    }
    function setBillingFrequency($BillingFrequency, $charset = 'iso-8859-1')
    {
        $this->BillingFrequency = $BillingFrequency;
        $this->_elements['BillingFrequency']['charset'] = $charset;
    }
    function getTotalBillingCycles()
    {
        return $this->TotalBillingCycles;
    }
    function setTotalBillingCycles($TotalBillingCycles, $charset = 'iso-8859-1')
    {
        $this->TotalBillingCycles = $TotalBillingCycles;
        $this->_elements['TotalBillingCycles']['charset'] = $charset;
    }
    function getAmount()
    {
        return $this->Amount;
    }
    function setAmount($Amount, $charset = 'iso-8859-1')
    {
        $this->Amount = $Amount;
        $this->_elements['Amount']['charset'] = $charset;
    }
    function getShippingAmount()
    {
        return $this->ShippingAmount;
    }
    function setShippingAmount($ShippingAmount, $charset = 'iso-8859-1')
    {
        $this->ShippingAmount = $ShippingAmount;
        $this->_elements['ShippingAmount']['charset'] = $charset;
    }
    function getTaxAmount()
    {
        return $this->TaxAmount;
    }
    function setTaxAmount($TaxAmount, $charset = 'iso-8859-1')
    {
        $this->TaxAmount = $TaxAmount;
        $this->_elements['TaxAmount']['charset'] = $charset;
    }
}
