<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractResponseType.php';

/**
 * CompleteRecoupResponseType
 *
 * @package PayPal
 */
class CompleteRecoupResponseType extends AbstractResponseType
{
    var $EnhancedCompleteRecoupResponseDetails;

    function CompleteRecoupResponseType()
    {
        parent::AbstractResponseType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'EnhancedCompleteRecoupResponseDetails' => 
              array (
                'required' => true,
                'type' => 'EnhancedCompleteRecoupResponseDetailsType',
                'namespace' => 'urn:ebay:apis:EnhancedDataTypes',
              ),
            ));
    }

    function getEnhancedCompleteRecoupResponseDetails()
    {
        return $this->EnhancedCompleteRecoupResponseDetails;
    }
    function setEnhancedCompleteRecoupResponseDetails($EnhancedCompleteRecoupResponseDetails, $charset = 'iso-8859-1')
    {
        $this->EnhancedCompleteRecoupResponseDetails = $EnhancedCompleteRecoupResponseDetails;
        $this->_elements['EnhancedCompleteRecoupResponseDetails']['charset'] = $charset;
    }
}
