<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractRequestType.php';

/**
 * InitiateRecoupRequestType
 *
 * @package PayPal
 */
class InitiateRecoupRequestType extends AbstractRequestType
{
    var $EnhancedInitiateRecoupRequestDetails;

    function InitiateRecoupRequestType()
    {
        parent::AbstractRequestType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'EnhancedInitiateRecoupRequestDetails' => 
              array (
                'required' => true,
                'type' => 'EnhancedInitiateRecoupRequestDetailsType',
                'namespace' => 'urn:ebay:apis:EnhancedDataTypes',
              ),
            ));
    }

    function getEnhancedInitiateRecoupRequestDetails()
    {
        return $this->EnhancedInitiateRecoupRequestDetails;
    }
    function setEnhancedInitiateRecoupRequestDetails($EnhancedInitiateRecoupRequestDetails, $charset = 'iso-8859-1')
    {
        $this->EnhancedInitiateRecoupRequestDetails = $EnhancedInitiateRecoupRequestDetails;
        $this->_elements['EnhancedInitiateRecoupRequestDetails']['charset'] = $charset;
    }
}
