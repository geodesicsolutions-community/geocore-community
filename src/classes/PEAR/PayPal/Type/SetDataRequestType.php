<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * SetDataRequestType
 *
 * @package PayPal
 */
class SetDataRequestType extends XSDSimpleType
{
    /**
     * Details about Billing Agreements requested to be created.
     */
    var $BillingApprovalDetails;

    /**
     * Only needed if Auto Authorization is requested. The authentication session token
     * will be passed in here.
     */
    var $BuyerDetail;

    /**
     * Requests for specific buyer information like Billing Address to be returned
     * through GetExpressCheckoutDetails should be specified under this.
     */
    var $InfoSharingDirectives;

    function SetDataRequestType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'BillingApprovalDetails' => 
              array (
                'required' => false,
                'type' => 'BillingApprovalDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'BuyerDetail' => 
              array (
                'required' => false,
                'type' => 'BuyerDetailType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'InfoSharingDirectives' => 
              array (
                'required' => false,
                'type' => 'InfoSharingDirectivesType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getBillingApprovalDetails()
    {
        return $this->BillingApprovalDetails;
    }
    function setBillingApprovalDetails($BillingApprovalDetails, $charset = 'iso-8859-1')
    {
        $this->BillingApprovalDetails = $BillingApprovalDetails;
        $this->_elements['BillingApprovalDetails']['charset'] = $charset;
    }
    function getBuyerDetail()
    {
        return $this->BuyerDetail;
    }
    function setBuyerDetail($BuyerDetail, $charset = 'iso-8859-1')
    {
        $this->BuyerDetail = $BuyerDetail;
        $this->_elements['BuyerDetail']['charset'] = $charset;
    }
    function getInfoSharingDirectives()
    {
        return $this->InfoSharingDirectives;
    }
    function setInfoSharingDirectives($InfoSharingDirectives, $charset = 'iso-8859-1')
    {
        $this->InfoSharingDirectives = $InfoSharingDirectives;
        $this->_elements['InfoSharingDirectives']['charset'] = $charset;
    }
}
