<?php
//
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Shane Caraveo <Shane@Caraveo.com>   Port to PEAR and more   |
// | Authors: Dietrich Ayala <dietrich@ganx4.com> Original Author         |
// +----------------------------------------------------------------------+
//
// $Id: Value.php,v 1.1.1.1 2006/02/19 08:15:20 dennis Exp $
//
require_once 'PayPal/SOAP/Base.php';

/**
 * SOAP::Value
 *
 * This class converts values between PHP and SOAP.
 *
 * Originally based on SOAPx4 by Dietrich Ayala
 * http://dietrich.ganx4.com/soapx4
 *
 * @access public
 * @package SOAP::Client
 * @author Shane Caraveo <shane@php.net> Conversion to PEAR and updates
 * @author Dietrich Ayala <dietrich@ganx4.com> Original Author
 */
class SOAP_Value
{
    /**
     *
     *
     * @var  string
     */
    public $value = null;

    /**
     *
     * @var  string
     */
    public $name = '';

    /**
     *
     * @var  string
     */
    public $type = '';

    /**
     * Namespace
     *
     * @var  string
     */
    public $namespace = '';
    public $type_namespace = '';

    public $attributes = array();

    /**
     *
     * @var string
     */
    public $arrayType = '';

    public $options = array();

    public $nqn;
    public $tqn;

    /**
     * Constructor.
     *
     * @param string $name        name of the soap-value {namespace}name
     * @param mixed  $type        soap value {namespace}type, if not set an automatic
     * @param mixed  $value       value to set
     * @param array  $attributes  (optional) Attributes.
     */
    function SOAP_Value($name = '', $type = false, $value = null, $attributes = array())
    {
        // Detect type if not passed.
        $this->nqn = new QName($name);
        $this->name = $this->nqn->name;
        $this->namespace = $this->nqn->namespace;
        $this->tqn = new QName($type);
        $this->type = $this->tqn->name;
        $this->type_prefix = $this->tqn->ns;
        $this->type_namespace = $this->tqn->namespace;
        $this->value =& $value;
        $this->attributes = $attributes;
    }

    /**
     * Serialize.
     *
     * @param SOAP_Base &$serializer  A SOAP_Base instance or subclass to serialize with.
     *
     * @return string  XML representation of $this.
     */
    function serialize(&$serializer)
    {
        return $serializer->_serializeValue($this->value, $this->name, $this->type, $this->namespace, $this->type_namespace, $this->options, $this->attributes, $this->arrayType);
    }

}

/**
 * SOAP::Header
 *
 * This class converts values between PHP and SOAP. It is a simple
 * wrapper around SOAP_Value, adding support for SOAP actor and
 * mustunderstand parameters.
 *
 * Originally based on SOAPx4 by Dietrich Ayala
 * http://dietrich.ganx4.com/soapx4
 *
 * @access public
 * @package SOAP::Header
 * @author Shane Caraveo <shane@php.net> Conversion to PEAR and updates
 * @author Dietrich Ayala <dietrich@ganx4.com> Original Author
 */
class SOAP_Header extends SOAP_Value
{
    /**
     * Constructor
     *
     * @param string  $name            name of the soap-value {namespace}name
     * @param mixed   $type            soap value {namespace}type, if not set an automatic
     * @param mixed   $value           value to set
     * @param integer $mustunderstand  Zero or one.
     * @param mixed   $attributes      (optional) Attributes.
     */
    function SOAP_Header($name = '', $type, $value,
                         $mustunderstand = 0,
                         $attributes = array())
    {
        if (!is_array($attributes)) {
            $actor = $attributes;
            $attributes = array();
        }

        parent::SOAP_Value($name, $type, $value, $attributes);

        if (isset($actor)) {
            $this->attributes['SOAP-ENV:actor'] = $actor;
        } elseif (!isset($this->attributes['SOAP-ENV:actor'])) {
            $this->attributes['SOAP-ENV:actor'] = 'http://schemas.xmlsoap.org/soap/actor/next';
        }
        $this->attributes['SOAP-ENV:mustUnderstand'] = (int)$mustunderstand;
    }

}

/**
 * SOAP::Attachment
 * this class converts values between PHP and SOAP
 * it handles Mime attachements per W3C Note on Soap Attachements at
 * http://www.w3.org/TR/SOAP-attachments
 *
 *
 * @access public
 * @package SOAP::Attachment
 * @author Shane Caraveo <shane@php.net> Conversion to PEAR and updates
 */
class SOAP_Attachment extends SOAP_Value
{
    /**
     * Constructor
     *
     * @param    string  name of the soap-value <value_name>
     * @param    mixed   soap header value
     * @param    string namespace
     */
    function SOAP_Attachment($name = '', $type = 'application/octet-stream',
                             $filename, $file = null)
    {
        global $SOAP_options;
        if (!isset($SOAP_options['Mime'])) {
            return PEAR::raiseError('Mail_mime is not installed, unable to support SOAP Attachements');
        }
        parent::SOAP_Value($name, null, null);

        $filedata = ($file === null) ? $this->_file2str($filename) : $file;
        $filename = basename($filename);
        if (PEAR::isError($filedata)) {
            return $filedata;
        }

        $cid = md5(uniqid(time()));

        $this->attributes['href'] = 'cid:'.$cid;

        $this->options['attachment'] = array(
                                'body'     => $filedata,
                                'disposition'     => $filename,
                                'content_type'   => $type,
                                'encoding' => 'base64',
                                'cid' => $cid
                               );
    }

    /**
     * Returns the contents of the given file name as string
     * @param string $file_name
     * @return string
     * @acces private
     */
    function &_file2str($file_name)
    {
        if (!is_readable($file_name)) {
            return PEAR::raiseError('File is not readable ' . $file_name);
        }
        if (!$fd = fopen($file_name, 'rb')) {
            return PEAR::raiseError('Could not open ' . $file_name);
        }
        $cont = fread($fd, filesize($file_name));
        fclose($fd);
        return $cont;
    }

}
