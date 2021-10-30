<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * EnhancedInitiateRecoupRequestDetailsType
 *
 * @package PayPal
 */
class EnhancedInitiateRecoupRequestDetailsType extends XSDSimpleType
{
    function EnhancedInitiateRecoupRequestDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:EnhancedDataTypes';
    }

}
