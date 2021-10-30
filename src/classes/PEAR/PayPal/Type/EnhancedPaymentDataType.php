<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * EnhancedPaymentDataType
 *
 * @package PayPal
 */
class EnhancedPaymentDataType extends XSDSimpleType
{
    function EnhancedPaymentDataType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:EnhancedDataTypes';
    }

}
