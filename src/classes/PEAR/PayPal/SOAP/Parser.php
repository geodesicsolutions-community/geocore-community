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
// $Id: Parser.php,v 1.1.1.1 2006/02/19 08:15:20 dennis Exp $
//

require_once 'PayPal/SOAP/Base.php';
require_once 'PayPal/SOAP/Value.php';

/**
 * SOAP Parser
 *
 * This class is used by SOAP::Message and SOAP::Server to parse soap
 * packets. Originally based on SOAPx4 by Dietrich Ayala
 * http://dietrich.ganx4.com/soapx4
 *
 * @access public
 * @package SOAP::Parser
 * @author Shane Caraveo <shane@php.net> Conversion to PEAR and updates
 * @author Dietrich Ayala <dietrich@ganx4.com> Original Author
 */
class SOAP_Parser extends SOAP_Base
{
    public $status = '';
    public $position = 0;
    public $pos_stat = 0;
    public $depth = 0;
    public $default_namespace = '';
    public $message = array();
    public $depth_array = array();
    public $previous_element = '';
    public $soapresponse = null;
    public $soapheaders = null;
    public $parent = 0;
    public $root_struct_name = array();
    public $header_struct_name = array();
    public $curent_root_struct_name = '';
    public $entities = array ( '&' => '&amp;', '<' => '&lt;', '>' => '&gt;', "'" => '&apos;', '"' => '&quot;' );
    public $root_struct = array();
    public $header_struct = array();
    public $curent_root_struct = 0;
    public $references = array();
    public $need_references = array();
    public $XMLSchemaVersion;
    public $bodyDepth; // used to handle non-root elements before root body element

    /**
     * SOAP_Parser constructor
     *
     * @param string xml content
     * @param string xml character encoding, defaults to 'UTF-8'
     */
    function SOAP_Parser(&$xml, $encoding = SOAP_DEFAULT_ENCODING, $attachments = null)
    {
        parent::SOAP_Base('Parser');
        $this->_setSchemaVersion(SOAP_XML_SCHEMA_VERSION);

        $this->attachments = $attachments;

        // Check the xml tag for encoding.
        if (preg_match('/<\?xml[^>]+encoding\s*?=\s*?(\'([^\']*)\'|"([^"]*)")[^>]*?[\?]>/', $xml, $m)) {
            $encoding = strtoupper($m[2] ? $m[2] : $m[3]);
        }

        // Determines where in the message we are
        // (envelope,header,body,method). Check whether content has
        // been read.
        if (!empty($xml)) {
            // Prepare the xml parser.
            $parser = xml_parser_create($encoding);
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
            xml_set_object($parser, $this);
            xml_set_element_handler($parser, 'startElement', 'endElement');
            xml_set_character_data_handler($parser, 'characterData');

            // Some lame soap implementations add null bytes at the
            // end of the soap stream, and expat choaks on that.
            if ($xml[strlen($xml) - 1] == 0) {
                $xml = trim($xml);
            }

            // Parse the XML file.
            if (!xml_parse($parser, $xml, true)) {
                $err = sprintf('XML error on line %d col %d byte %d %s',
                    xml_get_current_line_number($parser),
                    xml_get_current_column_number($parser),
                    xml_get_current_byte_index($parser),
                    xml_error_string(xml_get_error_code($parser)));
                $this->_raiseSoapFault($err,htmlspecialchars($xml));
            }
            xml_parser_free($parser);
        }
    }


    /**
     * domulti
     * recurse to build a multi-dim array, used by buildResponse
     *
     * @access private
     */
    function domulti($d, &$ar, &$r, &$v, $ad=0)
    {
        if ($d) {
            $this->domulti($d-1, $ar, $r[$ar[$ad]], $v, $ad+1);
        } else {
            $r = $v;
        }
    }

    /**
     * buildResponse
     * loop through msg, building response structures
     *
     * @param int position
     * @return SOAP_Value
     * @access private
     */
    function &buildResponse($pos)
    {
        $response = null;

        if (isset($this->message[$pos]['children'])) {
            $children = explode('|', $this->message[$pos]['children']);

            foreach ($children as $c => $child_pos) {
                if ($this->message[$child_pos]['type'] != null) {
                    $response[] =& $this->buildResponse($child_pos);
                }
            }
            if (array_key_exists('arraySize', $this->message[$pos])) {
                $ardepth = count($this->message[$pos]['arraySize']);
                if ($ardepth > 1) {
                    $ar = array_pad(array(), $ardepth, 0);
                    if (array_key_exists('arrayOffset', $this->message[$pos])) {
                        for ($i = 0; $i < $ardepth; $i++) {
                            $ar[$i] += $this->message[$pos]['arrayOffset'][$i];
                        }
                    }
                    $elc = count($response);
                    for ($i = 0; $i < $elc; $i++) {
                        // recurse to build a multi-dimensional array
                        $this->domulti($ardepth, $ar, $newresp, $response[$i]);

                        // increment our array pointers
                        $ad = $ardepth - 1;
                        $ar[$ad]++;
                        while ($ad > 0 && $ar[$ad] >= $this->message[$pos]['arraySize'][$ad]) {
                            $ar[$ad] = 0;
                            $ad--;
                            $ar[$ad]++;
                        }
                    }
                    $response = $newresp;
                } elseif (isset($this->message[$pos]['arrayOffset']) &&
                          $this->message[$pos]['arrayOffset'][0] > 0) {
                    // check for padding
                    $pad = $this->message[$pos]['arrayOffset'][0] + count($response) * -1;
                    $response = array_pad($response, $pad, null);
                }
            }
        }

        // Build attributes.
        $attrs = array();
        foreach ($this->message[$pos]['attrs'] as $atn => $atv) {
            if (!strstr($atn, 'xmlns') &&
                !strpos($atn, ':')) {
                $attrs[$atn] = $atv;
            }
        }

        // Add current node's value.
        if ($response) {
            $nqn = new Qname($this->message[$pos]['name'], $this->message[$pos]['namespace']);
            $tqn = new Qname($this->message[$pos]['type'], $this->message[$pos]['type_namespace']);
            $response = new SOAP_Value($nqn->fqn(), $tqn->fqn(), $response, $attrs);
            if (isset($this->message[$pos]['arrayType'])) {
                $response->arrayType = $this->message[$pos]['arrayType'];
            }
        } else {
            $nqn = new Qname($this->message[$pos]['name'], $this->message[$pos]['namespace']);
            $tqn = new Qname($this->message[$pos]['type'], $this->message[$pos]['type_namespace']);
            $response = new SOAP_Value($nqn->fqn(), $tqn->fqn(), $this->message[$pos]['cdata'], $attrs);
        }

        // handle header attribute that we need
        if (array_key_exists('actor', $this->message[$pos])) {
            $response->actor = $this->message[$pos]['actor'];
        }
        if (array_key_exists('mustUnderstand', $this->message[$pos])) {
            $response->mustunderstand = $this->message[$pos]['mustUnderstand'];
        }
        return $response;
    }

    /**
     * startElement
     * start-element handler used with xml parser
     *
     * @access private
     */
    function startElement($parser, $name, $attrs)
    {
        // position in a total number of elements, starting from 0
        // update class level pos
        $pos = $this->position++;

        // and set mine
        $this->message[$pos] = array();
        $this->message[$pos]['type'] = '';
        $this->message[$pos]['type_namespace'] = '';
        $this->message[$pos]['cdata'] = '';
        $this->message[$pos]['pos'] = $pos;
        $this->message[$pos]['id'] = '';

        // parent/child/depth determinations

        // depth = how many levels removed from root?
        // set mine as current global depth and increment global depth value
        $this->message[$pos]['depth'] = $this->depth++;

        // else add self as child to whoever the current parent is
        if ($pos != 0) {
            if (isset($this->message[$this->parent]['children']))
                $this->message[$this->parent]['children'] .= "|$pos";
            else
                $this->message[$this->parent]['children'] = $pos;
        }

        // set my parent
        $this->message[$pos]['parent'] = $this->parent;

        // set self as current value for this depth
        $this->depth_array[$this->depth] = $pos;
        // set self as current parent
        $this->parent = $pos;
        $qname = new QName($name);
        // set status
        if (strcasecmp('envelope', $qname->name) == 0) {
            $this->status = 'envelope';
        } elseif (strcasecmp('header', $qname->name) == 0) {
            $this->status = 'header';
            $this->header_struct_name[] = $this->curent_root_struct_name = $qname->name;
            $this->header_struct[] = $this->curent_root_struct = $pos;
            $this->message[$pos]['type'] = 'Struct';
        } elseif (strcasecmp('body', $qname->name) == 0) {
            $this->status = 'body';
            $this->bodyDepth = $this->depth;

        // Set method
        } elseif ($this->status == 'body') {
            // Is this element allowed to be a root?
            // XXX this needs to be optimized, we loop through attrs twice now.
            $can_root = $this->depth == $this->bodyDepth + 1;
            if ($can_root) {
                foreach ($attrs as $key => $value) {
                    if (stristr($key, ':root') && !$value) {
                        $can_root = FALSE;
                    }
                }
            }

            if ($can_root) {
                $this->status = 'method';
                $this->root_struct_name[] = $this->curent_root_struct_name = $qname->name;
                $this->root_struct[] = $this->curent_root_struct = $pos;
                $this->message[$pos]['type'] = 'Struct';
            }
        }

        // Set my status.
        $this->message[$pos]['status'] = $this->status;

        // Set name.
        $this->message[$pos]['name'] = htmlspecialchars($qname->name);

        // Set attributes.
        $this->message[$pos]['attrs'] = $attrs;

        // Loop through attributes, logging ns and type declarations.
        foreach ($attrs as $key => $value) {
            // If ns declarations, add to class level array of valid
            // namespaces.
            $kqn = new QName($key);
            if ($kqn->ns == 'xmlns') {
                $prefix = $kqn->name;

                if (in_array($value, $this->_XMLSchema)) {
                    $this->_setSchemaVersion($value);
                }

                $this->_namespaces[$value] = $prefix;

            // Set method namespace.
            } elseif ($key == 'xmlns') {
                $qname->ns = $this->_getNamespacePrefix($value);
                $qname->namespace = $value;
            } elseif ($kqn->name == 'actor') {
                $this->message[$pos]['actor'] = $value;
            } elseif ($kqn->name == 'mustUnderstand') {
                $this->message[$pos]['mustUnderstand'] = $value;

            // If it's a type declaration, set type.
            } elseif ($kqn->name == 'type') {
                $vqn = new QName($value);
                $this->message[$pos]['type'] = $vqn->name;
                $this->message[$pos]['type_namespace'] = $this->_getNamespaceForPrefix($vqn->ns);
                // Should do something here with the namespace of
                // specified type?

            } elseif ($kqn->name == 'arrayType') {
                $vqn = new QName($value);
                $this->message[$pos]['type'] = 'Array';
                if (isset($vqn->arraySize)) {
                    $this->message[$pos]['arraySize'] = $vqn->arraySize;
                }
                $this->message[$pos]['arrayType'] = $vqn->name;

            } elseif ($kqn->name == 'offset') {
                $this->message[$pos]['arrayOffset'] = preg_split('/,/', substr($value, 1, strlen($value) - 2));

            } elseif ($kqn->name == 'id') {
                // Save id to reference array.
                $this->references[$value] = $pos;
                $this->message[$pos]['id'] = $value;

            } elseif ($kqn->name == 'href') {
                if ($value[0] == '#') {
                    $ref = substr($value, 1);
                    if (isset($this->references[$ref])) {
                        // cdata, type, inval.
                        $ref_pos = $this->references[$ref];
                        $this->message[$pos]['children'] = &$this->message[$ref_pos]['children'];
                        $this->message[$pos]['cdata'] = &$this->message[$ref_pos]['cdata'];
                        $this->message[$pos]['type'] = &$this->message[$ref_pos]['type'];
                        $this->message[$pos]['arraySize'] = &$this->message[$ref_pos]['arraySize'];
                        $this->message[$pos]['arrayType'] = &$this->message[$ref_pos]['arrayType'];
                    } else {
                        // Reverse reference, store in 'need reference'.
                        if (!isset($this->need_references[$ref])) {
                            $this->need_references[$ref] = array();
                        }
                        $this->need_references[$ref][] = $pos;
                    }
                } elseif (isset($this->attachments[$value])) {
                    $this->message[$pos]['cdata'] = $this->attachments[$value];
                }
            }
        }
        // See if namespace is defined in tag.
        if (array_key_exists('xmlns:' . $qname->ns, $attrs)) {
            $namespace = $attrs['xmlns:' . $qname->ns];
        } elseif ($qname->ns && !$qname->namespace) {
            $namespace = $this->_getNamespaceForPrefix($qname->ns);
        } else {
            // Get namespace.
            $namespace = $qname->namespace ? $qname->namespace : $this->default_namespace;
        }
        $this->message[$pos]['namespace'] = $namespace;
        $this->default_namespace = $namespace;
    }

    /**
     * endElement
     * end-element handler used with xml parser
     *
     * @access private
     */
    function endElement($parser, $name)
    {
        // Position of current element is equal to the last value left
        // in depth_array for my depth.
        $pos = $this->depth_array[$this->depth];

        // Bring depth down a notch.
        $this->depth--;
        $qname = new QName($name);

        // Get type if not explicitly declared in an xsi:type attribute.
        // XXX check on integrating wsdl validation here
        if ($this->message[$pos]['type'] == '') {
            if (isset($this->message[$pos]['children'])) {
                /* this is slow, need to look at some faster method
                $children = explode('|', $this->message[$pos]['children']);
                if (count($children) > 2 &&
                    $this->message[$children[1]]['name'] == $this->message[$children[2]]['name']) {
                    $this->message[$pos]['type'] = 'Array';
                } else {
                    $this->message[$pos]['type'] = 'Struct';
                }*/
                $this->message[$pos]['type'] = 'Struct';
            } else {
                $parent = $this->message[$pos]['parent'];
                if ($this->message[$parent]['type'] == 'Array' &&
                  array_key_exists('arrayType', $this->message[$parent])) {
                    $this->message[$pos]['type'] = $this->message[$parent]['arrayType'];
                } else {
                    $this->message[$pos]['type'] = 'string';
                }
            }
        }

        // If tag we are currently closing is the method wrapper.
        if ($pos == $this->curent_root_struct) {
            $this->status = 'body';
        } elseif ($qname->name == 'Body' || $qname->name == 'Header') {
            $this->status = 'envelope';
        }

        // Set parent back to my parent.
        $this->parent = $this->message[$pos]['parent'];

        // Handle any reverse references now.
        $idref = $this->message[$pos]['id'];

        if ($idref != '' && array_key_exists($idref, $this->need_references)) {
            foreach ($this->need_references[$idref] as $ref_pos) {
                // XXX is this stuff there already?
                $this->message[$ref_pos]['children'] = &$this->message[$pos]['children'];
                $this->message[$ref_pos]['cdata'] = &$this->message[$pos]['cdata'];
                $this->message[$ref_pos]['type'] = &$this->message[$pos]['type'];
                $this->message[$ref_pos]['arraySize'] = &$this->message[$pos]['arraySize'];
                $this->message[$ref_pos]['arrayType'] = &$this->message[$pos]['arrayType'];
            }
        }
    }

    /**
     * characterData
     * element content handler used with xml parser
     *
     * @access private
     */
    function characterData($parser, $data)
    {
        $pos = $this->depth_array[$this->depth];
        if (isset($this->message[$pos]['cdata'])) {
            $this->message[$pos]['cdata'] .= $data;
        } else {
            $this->message[$pos]['cdata'] = $data;
        }
    }

    /**
     * getResponse
     *
     * returns an array of responses
     * after parsing a soap message, use this to get the response
     *
     * @return   array
     * @access public
     */
    function &getResponse()
    {
        if (isset($this->root_struct[0]) &&
            $this->root_struct[0]) {
            return $this->buildResponse($this->root_struct[0]);
        }
        return $this->_raiseSoapFault("couldn't build response");
    }

    /**
     * getHeaders
     *
     * returns an array of header responses
     * after parsing a soap message, use this to get the response
     *
     * @return   array
     * @access public
     */
    function &getHeaders()
    {
        if (isset($this->header_struct[0]) &&
            $this->header_struct[0]) {
            return $this->buildResponse($this->header_struct[0]);
        }

        // We don't fault if there are no headers that can be handled
        // by the app if necessary.
        return null;
    }

    /**
     * decodeEntities
     *
     * removes entities from text
     *
     * @param string
     * @return   string
     * @access private
     */
    function decodeEntities($text)
    {
        $trans_tbl = array_flip($this->entities);
        return strtr($text, $trans_tbl);
    }

}
