<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * FlowControlDetailsType
 * 
 * An optional set of values related to flow-specific details.
 *
 * @package PayPal
 */
class FlowControlDetailsType extends XSDSimpleType
{
    /**
     * The URL to redirect to for an unpayable transaction. This field is currently
     * used only for the inline checkout flow.
     */
    var $ErrorURL;

    /**
     * The URL to redirect to after a user clicks the "Pay" or "Continue" button on the
     * merchant's site. This field is currently used only for the inline checkout flow.
     */
    var $InContextReturnURL;

    function FlowControlDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ErrorURL' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'InContextReturnURL' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getErrorURL()
    {
        return $this->ErrorURL;
    }
    function setErrorURL($ErrorURL, $charset = 'iso-8859-1')
    {
        $this->ErrorURL = $ErrorURL;
        $this->_elements['ErrorURL']['charset'] = $charset;
    }
    function getInContextReturnURL()
    {
        return $this->InContextReturnURL;
    }
    function setInContextReturnURL($InContextReturnURL, $charset = 'iso-8859-1')
    {
        $this->InContextReturnURL = $InContextReturnURL;
        $this->_elements['InContextReturnURL']['charset'] = $charset;
    }
}
