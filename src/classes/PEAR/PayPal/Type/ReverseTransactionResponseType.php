<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractResponseType.php';

/**
 * ReverseTransactionResponseType
 *
 * @package PayPal
 */
class ReverseTransactionResponseType extends AbstractResponseType
{
    var $ReverseTransactionResponseDetails;

    function ReverseTransactionResponseType()
    {
        parent::AbstractResponseType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
        $this->_elements = array_merge($this->_elements,
            array (
              'ReverseTransactionResponseDetails' => 
              array (
                'required' => true,
                'type' => 'ReverseTransactionResponseDetailsType',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getReverseTransactionResponseDetails()
    {
        return $this->ReverseTransactionResponseDetails;
    }
    function setReverseTransactionResponseDetails($ReverseTransactionResponseDetails, $charset = 'iso-8859-1')
    {
        $this->ReverseTransactionResponseDetails = $ReverseTransactionResponseDetails;
        $this->_elements['ReverseTransactionResponseDetails']['charset'] = $charset;
    }
}
