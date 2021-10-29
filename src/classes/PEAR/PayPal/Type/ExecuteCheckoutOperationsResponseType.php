<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractResponseType.php';

/**
 * ExecuteCheckoutOperationsResponseType
 *
 * @package PayPal
 */
class ExecuteCheckoutOperationsResponseType extends AbstractResponseType
{
    var $ExecuteCheckoutOperationsResponseDetails;

    function ExecuteCheckoutOperationsResponseType()
    {
        parent::AbstractResponseType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'ExecuteCheckoutOperationsResponseDetails' => 
              array (
                'required' => true,
                'type' => 'ExecuteCheckoutOperationsResponseDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getExecuteCheckoutOperationsResponseDetails()
    {
        return $this->ExecuteCheckoutOperationsResponseDetails;
    }
    function setExecuteCheckoutOperationsResponseDetails($ExecuteCheckoutOperationsResponseDetails, $charset = 'iso-8859-1')
    {
        $this->ExecuteCheckoutOperationsResponseDetails = $ExecuteCheckoutOperationsResponseDetails;
        $this->_elements['ExecuteCheckoutOperationsResponseDetails']['charset'] = $charset;
    }
}
