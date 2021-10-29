<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * InfoSharingDirectivesType
 *
 * @package PayPal
 */
class InfoSharingDirectivesType extends XSDSimpleType
{
    /**
     * If Billing Address should be returned in GetExpressCheckoutDetails response,
     * this parameter should be set to yes here
     */
    var $ReqBillingAddress;

    function InfoSharingDirectivesType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ReqBillingAddress' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getReqBillingAddress()
    {
        return $this->ReqBillingAddress;
    }
    function setReqBillingAddress($ReqBillingAddress, $charset = 'iso-8859-1')
    {
        $this->ReqBillingAddress = $ReqBillingAddress;
        $this->_elements['ReqBillingAddress']['charset'] = $charset;
    }
}
