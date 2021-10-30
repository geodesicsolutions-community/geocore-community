<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * TaxIdDetailsType
 * 
 * Details about the payer's tax info passed in by the merchant or partner.
 *
 * @package PayPal
 */
class TaxIdDetailsType extends XSDSimpleType
{
    /**
     * The payer's Tax ID type; CNPJ/CPF for BR country.
     */
    var $TaxIdType;

    /**
     * The payer's Tax ID
     */
    var $TaxId;

    function TaxIdDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'TaxIdType' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'TaxId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getTaxIdType()
    {
        return $this->TaxIdType;
    }
    function setTaxIdType($TaxIdType, $charset = 'iso-8859-1')
    {
        $this->TaxIdType = $TaxIdType;
        $this->_elements['TaxIdType']['charset'] = $charset;
    }
    function getTaxId()
    {
        return $this->TaxId;
    }
    function setTaxId($TaxId, $charset = 'iso-8859-1')
    {
        $this->TaxId = $TaxId;
        $this->_elements['TaxId']['charset'] = $charset;
    }
}
