<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractRequestType.php';

/**
 * ExecuteCheckoutOperationsRequestType
 *
 * @package PayPal
 */
class ExecuteCheckoutOperationsRequestType extends AbstractRequestType
{
    var $ExecuteCheckoutOperationsRequestDetails;

    function ExecuteCheckoutOperationsRequestType()
    {
        parent::AbstractRequestType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'ExecuteCheckoutOperationsRequestDetails' => 
              array (
                'required' => true,
                'type' => 'ExecuteCheckoutOperationsRequestDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getExecuteCheckoutOperationsRequestDetails()
    {
        return $this->ExecuteCheckoutOperationsRequestDetails;
    }
    function setExecuteCheckoutOperationsRequestDetails($ExecuteCheckoutOperationsRequestDetails, $charset = 'iso-8859-1')
    {
        $this->ExecuteCheckoutOperationsRequestDetails = $ExecuteCheckoutOperationsRequestDetails;
        $this->_elements['ExecuteCheckoutOperationsRequestDetails']['charset'] = $charset;
    }
}
