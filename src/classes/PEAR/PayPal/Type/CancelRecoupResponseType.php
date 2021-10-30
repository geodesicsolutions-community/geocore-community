<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractResponseType.php';

/**
 * CancelRecoupResponseType
 *
 * @package PayPal
 */
class CancelRecoupResponseType extends AbstractResponseType
{
    function CancelRecoupResponseType()
    {
        parent::AbstractResponseType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
    }

}
