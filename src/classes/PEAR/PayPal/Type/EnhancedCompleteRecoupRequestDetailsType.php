<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/XSDSimpleType.php';

/**
 * EnhancedCompleteRecoupRequestDetailsType
 *
 * @package PayPal
 */
class EnhancedCompleteRecoupRequestDetailsType extends XSDSimpleType
{
    function EnhancedCompleteRecoupRequestDetailsType()
    {
        parent::XSDSimpleType();
        $this->_namespace = 'urn:ebay:apis:EnhancedDataTypes';
    }

}
