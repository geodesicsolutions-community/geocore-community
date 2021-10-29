<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * EnhancedCancelRecoupRequestDetailsType
 *
 * @package PayPal
 */
class EnhancedCancelRecoupRequestDetailsType extends XSDSimpleType
{
    function EnhancedCancelRecoupRequestDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:EnhancedDataTypes';
    }

}
