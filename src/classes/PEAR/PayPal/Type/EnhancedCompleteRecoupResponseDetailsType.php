<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * EnhancedCompleteRecoupResponseDetailsType
 *
 * @package PayPal
 */
class EnhancedCompleteRecoupResponseDetailsType extends XSDSimpleType
{
    function EnhancedCompleteRecoupResponseDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:EnhancedDataTypes';
    }

}
