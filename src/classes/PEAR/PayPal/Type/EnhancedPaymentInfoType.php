<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * EnhancedPaymentInfoType
 *
 * @package PayPal
 */
class EnhancedPaymentInfoType extends XSDSimpleType
{
    function EnhancedPaymentInfoType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:EnhancedDataTypes';
    }

}
