<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractRequestType.php';

/**
 * ReverseTransactionRequestType
 *
 * @package PayPal
 */
class ReverseTransactionRequestType extends AbstractRequestType
{
    var $ReverseTransactionRequestDetails;

    function ReverseTransactionRequestType()
    {
        parent::AbstractRequestType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'ReverseTransactionRequestDetails' => 
              array (
                'required' => true,
                'type' => 'ReverseTransactionRequestDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getReverseTransactionRequestDetails()
    {
        return $this->ReverseTransactionRequestDetails;
    }
    function setReverseTransactionRequestDetails($ReverseTransactionRequestDetails, $charset = 'iso-8859-1')
    {
        $this->ReverseTransactionRequestDetails = $ReverseTransactionRequestDetails;
        $this->_elements['ReverseTransactionRequestDetails']['charset'] = $charset;
    }
}
