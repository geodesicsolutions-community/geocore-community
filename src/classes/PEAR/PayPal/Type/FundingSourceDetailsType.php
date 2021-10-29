<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * FundingSourceDetailsType
 *
 * @package PayPal
 */
class FundingSourceDetailsType extends XSDSimpleType
{
    /**
     * Allowable values: 0,1
     */
    var $AllowPushFunding;

    /**
     * Allowable values: ELV, CreditCard, ChinaUnionPay, BML
     */
    var $UserSelectedFundingSource;

    function FundingSourceDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'AllowPushFunding' => 
              array (
                'required' => false,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'UserSelectedFundingSource' => 
              array (
                'required' => false,
                'type' => 'UserSelectedFundingSourceType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getAllowPushFunding()
    {
        return $this->AllowPushFunding;
    }
    function setAllowPushFunding($AllowPushFunding, $charset = 'iso-8859-1')
    {
        $this->AllowPushFunding = $AllowPushFunding;
        $this->_elements['AllowPushFunding']['charset'] = $charset;
    }
    function getUserSelectedFundingSource()
    {
        return $this->UserSelectedFundingSource;
    }
    function setUserSelectedFundingSource($UserSelectedFundingSource, $charset = 'iso-8859-1')
    {
        $this->UserSelectedFundingSource = $UserSelectedFundingSource;
        $this->_elements['UserSelectedFundingSource']['charset'] = $charset;
    }
}
