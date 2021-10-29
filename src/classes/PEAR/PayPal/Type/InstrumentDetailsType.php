<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * InstrumentDetailsType
 * 
 * InstrumentDetailsType Promotional Instrument Information.
 *
 * @package PayPal
 */
class InstrumentDetailsType extends XSDSimpleType
{
    /**
     * This field holds the category of the instrument only when it is promotional.
     * Return value 1 represents BML.
     */
    var $InstrumentCategory;

    function InstrumentDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'InstrumentCategory' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getInstrumentCategory()
    {
        return $this->InstrumentCategory;
    }
    function setInstrumentCategory($InstrumentCategory, $charset = 'iso-8859-1')
    {
        $this->InstrumentCategory = $InstrumentCategory;
        $this->_elements['InstrumentCategory']['charset'] = $charset;
    }
}
