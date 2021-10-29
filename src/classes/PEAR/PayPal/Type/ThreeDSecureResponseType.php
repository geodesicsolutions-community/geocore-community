<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * ThreeDSecureResponseType
 * 
 * 3DS remaining fields.
 *
 * @package PayPal
 */
class ThreeDSecureResponseType extends XSDSimpleType
{
    var $Vpas;

    var $EciSubmitted3DS;

    function ThreeDSecureResponseType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'Vpas' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'EciSubmitted3DS' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getVpas()
    {
        return $this->Vpas;
    }
    function setVpas($Vpas, $charset = 'iso-8859-1')
    {
        $this->Vpas = $Vpas;
        $this->_elements['Vpas']['charset'] = $charset;
    }
    function getEciSubmitted3DS()
    {
        return $this->EciSubmitted3DS;
    }
    function setEciSubmitted3DS($EciSubmitted3DS, $charset = 'iso-8859-1')
    {
        $this->EciSubmitted3DS = $EciSubmitted3DS;
        $this->_elements['EciSubmitted3DS']['charset'] = $charset;
    }
}
