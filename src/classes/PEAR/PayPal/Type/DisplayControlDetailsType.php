<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * DisplayControlDetailsType
 * 
 * Contains elements that allows customization of display (user interface)
 * elements.
 *
 * @package PayPal
 */
class DisplayControlDetailsType extends XSDSimpleType
{
    /**
     * Optional URL to pay button image for the inline checkout flow. Currently
     * applicable only to the inline checkout flow when the
     * FlowControlDetails/InlineReturnURL is present.
     */
    var $InContextPaymentButtonImage;

    function DisplayControlDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'InContextPaymentButtonImage' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getInContextPaymentButtonImage()
    {
        return $this->InContextPaymentButtonImage;
    }
    function setInContextPaymentButtonImage($InContextPaymentButtonImage, $charset = 'iso-8859-1')
    {
        $this->InContextPaymentButtonImage = $InContextPaymentButtonImage;
        $this->_elements['InContextPaymentButtonImage']['charset'] = $charset;
    }
}
