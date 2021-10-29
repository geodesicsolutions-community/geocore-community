<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * ReverseTransactionRequestDetailsType
 *
 * @package PayPal
 */
class ReverseTransactionRequestDetailsType extends XSDSimpleType
{
    /**
     * Identifier of the transaction to reverse.
     */
    var $TransactionID;

    function ReverseTransactionRequestDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'TransactionID' => 
              array (
                'required' => false,
                'type' => 'TransactionId',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getTransactionID()
    {
        return $this->TransactionID;
    }
    function setTransactionID($TransactionID, $charset = 'iso-8859-1')
    {
        $this->TransactionID = $TransactionID;
        $this->_elements['TransactionID']['charset'] = $charset;
    }
}
