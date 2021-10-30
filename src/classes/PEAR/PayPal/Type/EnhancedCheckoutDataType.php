<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * EnhancedCheckoutDataType
 *
 * @package PayPal
 */
class EnhancedCheckoutDataType extends XSDSimpleType
{
    function EnhancedCheckoutDataType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:EnhancedDataTypes';
    }

}
