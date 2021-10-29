<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractRequestType.php';

/**
 * CompleteRecoupRequestType
 *
 * @package PayPal
 */
class CompleteRecoupRequestType extends AbstractRequestType
{
    var $EnhancedCompleteRecoupRequestDetails;

    function CompleteRecoupRequestType()
    {
        parent::AbstractRequestType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'EnhancedCompleteRecoupRequestDetails' => 
              array (
                'required' => true,
                'type' => 'EnhancedCompleteRecoupRequestDetailsType',
                'namespace' => 'urn:ebay:apis:EnhancedDataTypes',
              ),
            ));
    }

    function getEnhancedCompleteRecoupRequestDetails()
    {
        return $this->EnhancedCompleteRecoupRequestDetails;
    }
    function setEnhancedCompleteRecoupRequestDetails($EnhancedCompleteRecoupRequestDetails, $charset = 'iso-8859-1')
    {
        $this->EnhancedCompleteRecoupRequestDetails = $EnhancedCompleteRecoupRequestDetails;
        $this->_elements['EnhancedCompleteRecoupRequestDetails']['charset'] = $charset;
    }
}
