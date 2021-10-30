<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractResponseType.php';

/**
 * InitiateRecoupResponseType
 *
 * @package PayPal
 */
class InitiateRecoupResponseType extends AbstractResponseType
{
    function InitiateRecoupResponseType()
    {
        parent::AbstractResponseType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
    }

}
