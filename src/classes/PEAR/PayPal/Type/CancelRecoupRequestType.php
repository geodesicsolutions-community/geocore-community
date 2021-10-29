<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractRequestType.php';

/**
 * CancelRecoupRequestType
 *
 * @package PayPal
 */
class CancelRecoupRequestType extends AbstractRequestType
{
    var $EnhancedCancelRecoupRequestDetails;

    function CancelRecoupRequestType()
    {
        parent::AbstractRequestType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'EnhancedCancelRecoupRequestDetails' => 
              array (
                'required' => true,
                'type' => 'EnhancedCancelRecoupRequestDetailsType',
                'namespace' => 'urn:ebay:apis:EnhancedDataTypes',
              ),
            ));
    }

    function getEnhancedCancelRecoupRequestDetails()
    {
        return $this->EnhancedCancelRecoupRequestDetails;
    }
    function setEnhancedCancelRecoupRequestDetails($EnhancedCancelRecoupRequestDetails, $charset = 'iso-8859-1')
    {
        $this->EnhancedCancelRecoupRequestDetails = $EnhancedCancelRecoupRequestDetails;
        $this->_elements['EnhancedCancelRecoupRequestDetails']['charset'] = $charset;
    }
}
