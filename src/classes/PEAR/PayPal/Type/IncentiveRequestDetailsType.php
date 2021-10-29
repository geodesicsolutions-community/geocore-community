<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * IncentiveRequestDetailsType
 *
 * @package PayPal
 */
class IncentiveRequestDetailsType extends XSDSimpleType
{
    var $RequestId;

    var $RequestType;

    var $RequestDetailLevel;

    function IncentiveRequestDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'RequestId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'RequestType' => 
              array (
                'required' => false,
                'type' => 'IncentiveRequestCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'RequestDetailLevel' => 
              array (
                'required' => false,
                'type' => 'IncentiveRequestDetailLevelCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getRequestId()
    {
        return $this->RequestId;
    }
    function setRequestId($RequestId, $charset = 'iso-8859-1')
    {
        $this->RequestId = $RequestId;
        $this->_elements['RequestId']['charset'] = $charset;
    }
    function getRequestType()
    {
        return $this->RequestType;
    }
    function setRequestType($RequestType, $charset = 'iso-8859-1')
    {
        $this->RequestType = $RequestType;
        $this->_elements['RequestType']['charset'] = $charset;
    }
    function getRequestDetailLevel()
    {
        return $this->RequestDetailLevel;
    }
    function setRequestDetailLevel($RequestDetailLevel, $charset = 'iso-8859-1')
    {
        $this->RequestDetailLevel = $RequestDetailLevel;
        $this->_elements['RequestDetailLevel']['charset'] = $charset;
    }
}
