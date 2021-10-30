<?php
/**
 * @package PayPal
 */

/**
 * Make sure our parent class is defined.
 */
require_once 'PayPal/Type/AbstractResponseType.php';

/**
 * ExternalRememberMeOptOutResponseType
 *
 * @package PayPal
 */
class ExternalRememberMeOptOutResponseType extends AbstractResponseType
{
    function ExternalRememberMeOptOutResponseType()
    {
        parent::AbstractResponseType();
        $this->_namespace = 'urn:ebay:api:PayPalAPI';
    }

}
