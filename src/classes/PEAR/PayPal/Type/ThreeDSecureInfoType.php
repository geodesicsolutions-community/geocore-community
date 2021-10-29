<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * ThreeDSecureInfoType
 * 
 * 3DSecureInfoType Information about 3D Secure parameters.
 *
 * @package PayPal
 */
class ThreeDSecureInfoType extends XSDSimpleType
{
    var $ThreeDSecureRequest;

    var $ThreeDSecureResponse;

    function ThreeDSecureInfoType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ThreeDSecureRequest' => 
              array (
                'required' => false,
                'type' => 'ThreeDSecureRequestType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ThreeDSecureResponse' => 
              array (
                'required' => false,
                'type' => 'ThreeDSecureResponseType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getThreeDSecureRequest()
    {
        return $this->ThreeDSecureRequest;
    }
    function setThreeDSecureRequest($ThreeDSecureRequest, $charset = 'iso-8859-1')
    {
        $this->ThreeDSecureRequest = $ThreeDSecureRequest;
        $this->_elements['ThreeDSecureRequest']['charset'] = $charset;
    }
    function getThreeDSecureResponse()
    {
        return $this->ThreeDSecureResponse;
    }
    function setThreeDSecureResponse($ThreeDSecureResponse, $charset = 'iso-8859-1')
    {
        $this->ThreeDSecureResponse = $ThreeDSecureResponse;
        $this->_elements['ThreeDSecureResponse']['charset'] = $charset;
    }
}
