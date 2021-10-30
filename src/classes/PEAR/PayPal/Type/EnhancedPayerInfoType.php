<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * EnhancedPayerInfoType
 *
 * @package PayPal
 */
class EnhancedPayerInfoType extends XSDSimpleType
{
    function EnhancedPayerInfoType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:EnhancedDataTypes';
    }

}
