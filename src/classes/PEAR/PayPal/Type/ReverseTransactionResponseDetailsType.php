<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * ReverseTransactionResponseDetailsType
 *
 * @package PayPal
 */
class ReverseTransactionResponseDetailsType extends XSDSimpleType
{
    /**
     * Unique transaction identifier of the reversal transaction created.
     */
    var $ReverseTransactionID;

    /**
     * Status of reversal request.
     */
    var $Status;

    function ReverseTransactionResponseDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:eBLBaseComponents';
        $this->_elements = array_merge($this->_elements,
            array (
              'ReverseTransactionID' => 
              array (
                'required' => false,
                'type' => 'TransactionId',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
              'Status' => 
              array (
                'required' => true,
                'type' => 'string',
                'namespace' => 'urn:ebay:apis:eBLBaseComponents',
              ),
            ));
    }

    function getReverseTransactionID()
    {
        return $this->ReverseTransactionID;
    }
    function setReverseTransactionID($ReverseTransactionID, $charset = 'iso-8859-1')
    {
        $this->ReverseTransactionID = $ReverseTransactionID;
        $this->_elements['ReverseTransactionID']['charset'] = $charset;
    }
    function getStatus()
    {
        return $this->Status;
    }
    function setStatus($Status, $charset = 'iso-8859-1')
    {
        $this->Status = $Status;
        $this->_elements['Status']['charset'] = $charset;
    }
}
