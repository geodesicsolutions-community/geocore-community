<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * IncentiveDetailType
 *
 * @package PayPal
 */
class IncentiveDetailType extends XSDSimpleType
{
    var $RedemptionCode;

    var $DisplayCode;

    var $ProgramId;

    var $IncentiveType;

    var $IncentiveDescription;

    var $AppliedTo;

    var $Status;

    var $ErrorCode;

    function IncentiveDetailType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'RedemptionCode' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'DisplayCode' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ProgramId' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'IncentiveType' => 
              array (
                'required' => false,
                'type' => 'IncentiveTypeCodeType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'IncentiveDescription' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'AppliedTo' => 
              array (
                'required' => false,
                'type' => 'IncentiveAppliedToType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Status' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'ErrorCode' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getRedemptionCode()
    {
        return $this->RedemptionCode;
    }
    function setRedemptionCode($RedemptionCode, $charset = 'iso-8859-1')
    {
        $this->RedemptionCode = $RedemptionCode;
        $this->_elements['RedemptionCode']['charset'] = $charset;
    }
    function getDisplayCode()
    {
        return $this->DisplayCode;
    }
    function setDisplayCode($DisplayCode, $charset = 'iso-8859-1')
    {
        $this->DisplayCode = $DisplayCode;
        $this->_elements['DisplayCode']['charset'] = $charset;
    }
    function getProgramId()
    {
        return $this->ProgramId;
    }
    function setProgramId($ProgramId, $charset = 'iso-8859-1')
    {
        $this->ProgramId = $ProgramId;
        $this->_elements['ProgramId']['charset'] = $charset;
    }
    function getIncentiveType()
    {
        return $this->IncentiveType;
    }
    function setIncentiveType($IncentiveType, $charset = 'iso-8859-1')
    {
        $this->IncentiveType = $IncentiveType;
        $this->_elements['IncentiveType']['charset'] = $charset;
    }
    function getIncentiveDescription()
    {
        return $this->IncentiveDescription;
    }
    function setIncentiveDescription($IncentiveDescription, $charset = 'iso-8859-1')
    {
        $this->IncentiveDescription = $IncentiveDescription;
        $this->_elements['IncentiveDescription']['charset'] = $charset;
    }
    function getAppliedTo()
    {
        return $this->AppliedTo;
    }
    function setAppliedTo($AppliedTo, $charset = 'iso-8859-1')
    {
        $this->AppliedTo = $AppliedTo;
        $this->_elements['AppliedTo']['charset'] = $charset;
    }
    function getStatus()
    {
        return $this->Status;
    }
    function setStatus($Status, $charset = 'iso-8859-1')
    {
        $this->Status = $Status;
        $this->_elements['Status']['charset'] = $charset;
    }
    function getErrorCode()
    {
        return $this->ErrorCode;
    }
    function setErrorCode($ErrorCode, $charset = 'iso-8859-1')
    {
        $this->ErrorCode = $ErrorCode;
        $this->_elements['ErrorCode']['charset'] = $charset;
    }
}
