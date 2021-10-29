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
// | Authors: Shane Caraveo <Shane@Caraveo.com>                           |
// +----------------------------------------------------------------------+
//
// $Id: HTTP.php,v 1.1.1.1 2006/02/19 08:15:20 dennis Exp $
//
/**
 * Needed Classes
 */
require_once 'PayPal/SOAP/Base.php';
require_once 'PayPal/SDKProxyProperties.php';
require_once 'PayPal/CallerServices.php';
require_once 'PayPal.php';

session_start();


/**
 *  HTTP Transport for SOAP
 *
 * @access public
 * @package SOAP::Transport::HTTP
 * @author Shane Caraveo <shane@php.net>
 */
class SOAP_Transport_HTTP extends SOAP_Base
{

    /**
     * Basic Auth string
     *
     * @var  array
     */
    public $headers = array();

    /**
     * Cookies
     *
     * @var apublic $y
     */
    public $cookies;

    /**
     *
     * @var  int connection timeout in seconds - 0 = none
     */
    public $timeout = 300;

    /**
     * Array containing urlparts - parse_url()
     *
     * @var  mixed
     */
    public $urlparts = NULL;

    /**
     * Connection endpoint - URL
     *
     * @var  string
     */
    public $url = '';

    /**
     * Incoming payload
     *
     * @var  string
     */
    public $incoming_payload = '';

    /**
     * HTTP-Request User-Agent
     *
     * @var  string
     */
    public $_userAgent = SOAP_LIBRARY_NAME;

    /**
     * HTTP Encoding
     *
     * @var string
     */
    public $encoding = SOAP_DEFAULT_ENCODING;

    /**
     * HTTP-Response Content-Type encoding
     *
     * we assume UTF-8 if no encoding is set
     * @var  string
     */
    public $result_encoding = 'UTF-8';

    /**
     * HTTP-Response Content-Type
     */
    public $result_content_type;

    public $result_headers = array();

    public $result_cookies = array();

    /**
     * SOAP_Transport_HTTP Constructor
     *
     * @param string $URL    http url to soap endpoint
     *
     * @access public
     * @param  string $URI
     * @param  string $encoding  encoding to use
     */
    function SOAP_Transport_HTTP($URL, $encoding = SOAP_DEFAULT_ENCODING)
    {
        parent::SOAP_Base('HTTP');
        $this->urlparts = @parse_url($URL);
        $this->url = $URL;
        $this->encoding = $encoding;
    }

    /**
     * send and receive soap data
     *
     * @param string outgoing post data
     * @param array  options
     *
     * @return string|fault response
     * @access public
     */
    function &send(&$msg, $options = null)
    {
        if (!$this->_validateUrl()) {
            return $this->fault;
        }

        if (isset($options['timeout']))
            $this->timeout = (int)$options['timeout'];

        if (strcasecmp($this->urlparts['scheme'], 'HTTP') == 0) {
            return $this->_sendHTTP($msg, $options);
        } else if (strcasecmp($this->urlparts['scheme'], 'HTTPS') == 0) {
            return $this->_sendHTTPS($msg, $options);
        }

        return $this->_raiseSoapFault('Invalid url scheme '.$this->url);
    }

    /**
     * set data for http authentication
     * creates Authorization header
     *
     * @param string $username   username
     * @param string $password   response data, minus http headers
     *
     * @return none
     * @access public
     */
    function setCredentials($username, $password)
    {
        $this->headers['Authorization'] = 'Basic ' . base64_encode($username . ':' . $password);
    }

    /**
     * Add a cookie
     *
     * @access public
     * @param  string  $name   cookie name
     * @param  mixed   $value  cookie value
     * @return void
     */
    function addCookie($name, $value)
    {
        $this->cookies[$name]=$value;
    }

    // private methods

    /**
     * Generates the correct headers for the cookies
     *
     * @access private
     * @return void
     */
    function _genCookieHeader()
    {
        foreach ($this->cookies as $name=>$value) {
            $cookies = (isset($cookies) ? $cookies. '; ' : '') .
                        urlencode($name) . '=' . urlencode($value);
        }
        return $cookies;
    }

    /**
     * validate url data passed to constructor
     *
     * @access private
     * @return boolean
     */
    function _validateUrl()
    {
        if ( ! is_array($this->urlparts) ) {
            $this->_raiseSoapFault("Unable to parse URL " . $this->url);
            return false;
        }
        if (!isset($this->urlparts['host'])) {
            $this->_raiseSoapFault("No host in URL " . $this->url);
            return false;
        }
        if (!isset($this->urlparts['port'])) {

            if (strcasecmp($this->urlparts['scheme'], 'HTTP') == 0)
                $this->urlparts['port'] = 80;
            else if (strcasecmp($this->urlparts['scheme'], 'HTTPS') == 0)
                $this->urlparts['port'] = 443;

        }
        if (isset($this->urlparts['user'])) {
            $this->setCredentials(urldecode($this->urlparts['user']),
                                    urldecode($this->urlparts['pass']));
        }
        if (!isset($this->urlparts['path']) || !$this->urlparts['path'])
            $this->urlparts['path'] = '/';
        return true;
    }

    /**
     * Finds out what is the encoding.
     *
     * Sets the object property accordingly.
     *
     * @access private
     * @param  array $headers  headers
     * @return void
     */
    function _parseEncoding($headers)
    {
        $h = stristr($headers, 'Content-Type');
        preg_match_all('/^Content-Type:\s*(.*)$/im', $h, $ct, PREG_SET_ORDER);
        $n = count($ct);
        $ct = $ct[$n - 1];
        $this->result_content_type = str_replace("\r", '', $ct[1]);
        if (preg_match('/(.*?)(?:;\s?charset=)(.*)/i', $this->result_content_type, $m)) {
            // strip the string of \r
            $this->result_content_type = $m[1];
            if (count($m) > 2) {
                $enc = strtoupper(str_replace('"', '', $m[2]));
                if (in_array($enc, $this->_encodings)) {
                    $this->result_encoding = $enc;
                }
            }
        }
        // deal with broken servers that don't set content type on faults
        if (!$this->result_content_type) $this->result_content_type = 'text/xml';
    }

    /**
     * Parses the headers
     *
     * @param  array $headers the headers
     * @return void
     */
    function _parseHeaders($headers)
    {
        /* largely borrowed from HTTP_Request */
        $this->result_headers = array();
        $headers = preg_split("/\r?\n/", $headers);
        foreach ($headers as $value) {
            if (strpos($value,':') === false) {
                $this->result_headers[0]=$value;
                continue;
            }
            list($name,$value) = explode(':',$value);
            $headername = strtolower($name);
            $headervalue = trim($value);
            $this->result_headers[$headername]=$headervalue;

            if ($headername == 'set-cookie') {
                // Parse a SetCookie header to fill _cookies array
                $cookie = array(
                    'expires' => null,
                    'domain'  => $this->urlparts['host'],
                    'path'    => null,
                    'secure'  => false
                );

                // Only a name=value pair
                if (!strpos($headervalue, ';')) {
                    list($cookie['name'], $cookie['value']) = array_map('trim', explode('=', $headervalue));
                    $cookie['name']  = urldecode($cookie['name']);
                    $cookie['value'] = urldecode($cookie['value']);

                // Some optional parameters are supplied
                } else {
                    $elements = explode(';', $headervalue);
                    list($cookie['name'], $cookie['value']) = array_map('trim', explode('=', $elements[0]));
                    $cookie['name']  = urldecode($cookie['name']);
                    $cookie['value'] = urldecode($cookie['value']);

                    for ($i = 1; $i < count($elements);$i++) {
                        list ($elName, $elValue) = array_map('trim', explode('=', $elements[$i]));
                        if ('secure' == $elName) {
                            $cookie['secure'] = true;
                        } elseif ('expires' == $elName) {
                            $cookie['expires'] = str_replace('"', '', $elValue);
                        } elseif ('path' == $elName OR 'domain' == $elName) {
                            $cookie[$elName] = urldecode($elValue);
                        } else {
                            $cookie[$elName] = $elValue;
                        }
                    }
                }
                $this->result_cookies[] = $cookie;
            }
        }
    }

    /**
     * Remove http headers from response
     *
     * @return boolean
     * @access private
     */
    function _parseResponse()
    {
        if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $this->incoming_payload, $match)) {
            #$this->response = preg_replace("/[\r|\n]/", '', $match[2]);

// find the response error, some servers response with 500 for soap faults
            //Check if proxy headers are found
            //If found, ignore those headers
            if (preg_match("/^(.*?)\r?\n\r?\n(.*)/s", $match[2], $match1))
            {
                if($match1[2] != '')
                {
                    $match[1] = $match1[1];
                    $match[2] = $match1[2];
                }
            }

            $this->response =& $match[2];
            // find the response error, some servers response with 500 for soap faults
            $this->_parseHeaders($match[1]);

            list($protocol, $code, $msg) = sscanf($this->result_headers[0], '%s %s %s');
            unset($this->result_headers[0]);

            switch($code) {
                case 100: // Continue
                   $this->incoming_payload = $match[2];
                   return $this->_parseResponse();
                case 400:
                    $this->_raiseSoapFault("HTTP Response $code Bad Request");
                    return false;
                    break;
                case 401:
                    $this->_raiseSoapFault("HTTP Response $code Authentication Failed");
                    return false;
                    break;
                case 403:
                    $this->_raiseSoapFault("HTTP Response $code Forbidden");
                    return false;
                    break;
                case 404:
                    $this->_raiseSoapFault("HTTP Response $code Not Found");
                    return false;
                    break;
                case 407:
                    $this->_raiseSoapFault("HTTP Response $code Proxy Authentication Required");
                    return false;
                    break;
                case 408:
                    $this->_raiseSoapFault("HTTP Response $code Request Timeout");
                    return false;
                    break;
                case 410:
                    $this->_raiseSoapFault("HTTP Response $code Gone");
                    return false;
                    break;
                default:
                    if ($code >= 400 && $code < 500) {
                        $this->_raiseSoapFault("HTTP Response $code Not Found, Server message: $msg");
                        return false;
                    }
            }

            $this->_parseEncoding($match[1]);

            if ($this->result_content_type == 'application/dime') {
                // XXX quick hack insertion of DIME
                if (PEAR::isError($this->_decodeDIMEMessage($this->response,$this->headers,$this->attachments))) {
                    // _decodeDIMEMessage already raised $this->fault
                    return false;
                }
                $this->result_content_type = $this->headers['content-type'];
            } else if (stristr($this->result_content_type,'multipart/related')) {
                $this->response = $this->incoming_payload;
                if (PEAR::isError($this->_decodeMimeMessage($this->response,$this->headers,$this->attachments))) {
                    // _decodeMimeMessage already raised $this->fault
                    return false;
                }
            } else if ($this->result_content_type != 'text/xml') {
                $this->_raiseSoapFault($this->response);
                return false;
            }
            // if no content, return false
            return strlen($this->response) > 0;
        }
        $this->_raiseSoapFault('Invalid HTTP Response');
        return false;
    }

    /**
     * Create http request, including headers, for outgoing request
     *
     * @param string   &$msg   outgoing SOAP package
     * @param $options
     * @return string outgoing_payload
     * @access private
     */
    function &_getRequest(&$msg, $options)
    {
        $action = isset($options['soapaction'])?$options['soapaction']:'';
        $fullpath = $this->urlparts['path'].
                        (isset($this->urlparts['query'])?'?'.$this->urlparts['query']:'').
                        (isset($this->urlparts['fragment'])?'#'.$this->urlparts['fragment']:'');

        if (isset($options['proxy_host'])) {
            $fullpath = 'http://' . $this->urlparts['host'] . ':' . $this->urlparts['port'] . $fullpath;
        }

        if (isset($options['proxy_user'])) {
            $this->headers['Proxy-Authorization'] = 'Basic ' . base64_encode($options['proxy_user'].":".$options['proxy_pass']);
        }

        if (isset($options['user'])) {
            $this->setCredentials($options['user'], $options['pass']);
        }

        $this->headers['User-Agent'] = $this->_userAgent;
        $this->headers['Host'] = $this->urlparts['host'];
        $this->headers['Content-Type'] = "text/xml; charset=$this->encoding";
        $this->headers['Content-Length'] = strlen($msg);
        $this->headers['SOAPAction'] = "\"$action\"";
        if (isset($options['headers'])) {
            $this->headers = array_merge($this->headers, $options['headers']);
        }

        $this->cookies = array();
        if (!isset($options['nocookies']) || !$options['nocookies']) {
            // add the cookies we got from the last request
            if (isset($this->result_cookies)) {
                foreach ($this->result_cookies as $cookie) {
                    if ($cookie['domain'] == $this->urlparts['host'])
                        $this->cookies[$cookie['name']]=$cookie['value'];
                }
            }
        }
        // add cookies the user wants to set
        if (isset($options['cookies'])) {
            foreach ($options['cookies'] as $cookie) {
                if ($cookie['domain'] == $this->urlparts['host'])
                    $this->cookies[$cookie['name']]=$cookie['value'];
            }
        }
        if (count($this->cookies)) {
            $this->headers['Cookie'] = $this->_genCookieHeader();
        }
        $headers = '';
        foreach ($this->headers as $k => $v) {
            $headers .= "$k: $v\r\n";
        }
        $this->outgoing_payload =
                "POST $fullpath HTTP/1.0\r\n".
                $headers."\r\n".
                $msg;
        return $this->outgoing_payload;
    }

    /**
     * Send outgoing request, and read/parse response
     *
     * @param string  &$msg   outgoing SOAP package
     * @param string  $action   SOAP Action
     * @return string &$response   response data, minus http headers
     * @access private
     */
    function &_sendHTTP(&$msg, $options)
    {
        		
		$this->incoming_payload = '';
        $this->_getRequest($msg, $options);
        $host = $this->urlparts['host'];
        $port = $this->urlparts['port'];
        if (isset($options['proxy_host'])) {
            $host = $options['proxy_host'];
            $port = isset($options['proxy_port']) ? $options['proxy_port'] : 8080;
        }
        // send
        if ($this->timeout > 0) {
            $fp = @fsockopen($host, $port, $this->errno, $this->errmsg, $this->timeout);
        } else {
            $fp = @fsockopen($host, $port, $this->errno, $this->errmsg);
        }
        if (!$fp) {
            return $this->_raiseSoapFault("Connect Error to $host:$port");
        }
        if ($this->timeout > 0) {
            // some builds of php do not support this, silence
            // the warning
            @socket_set_timeout($fp, $this->timeout);
        }
        if (!fputs($fp, $this->outgoing_payload, strlen($this->outgoing_payload))) {
            return $this->_raiseSoapFault("Error POSTing Data to $host");
        }

        // get reponse
        // XXX time consumer
        do {
            $data = fread($fp, 4096);
            $_tmp_status = socket_get_status($fp);
            if ($_tmp_status['timed_out']) {
                return $this->_raiseSoapFault("Timed out read from $host");
            } else {
                $this->incoming_payload .= $data;
            }
        } while (!$_tmp_status['eof']);

        fclose($fp);

        if (!$this->_parseResponse()) {
            return $this->fault;
        }
        return $this->response;
    }

    /**
     * Send outgoing request, and read/parse response, via HTTPS
     *
     * @param string  &$msg   outgoing SOAP package
     * @param string  $action   SOAP Action
     * @return string &$response   response data, minus http headers
     * @access private
     */
    function &_sendHTTPS(&$msg, $options)
    {
    	global $authstringvar;
    	
        /* NOTE This function uses the CURL functions
         *  Your php must be compiled with CURL
         */

		//adding proxy supprt for HTTPS connections.
	   //if USE_PROXY set to TRUE in SDKProxyProperties.php(php-sdk\lib\PayPal),
	  //then only proxy is setted.
		if(USE_PROXY){
				$options['proxy_host'] = PROXY_HOST;
				$options['proxy_port']=  PROXY_PORT;
				$options['proxy_user'] = PROXY_USER;
				$options['proxy_pass']=  PROXY_PASSWORD;
		}

        if (!extension_loaded('curl')) {
            return $this->_raiseSoapFault('CURL Extension is required for HTTPS');
        }

        $ch = curl_init();

        if (isset($options['proxy_host'])) {
            // $options['http_proxy'] == 'hostname:port'
            $host = $options['proxy_host'];
            $port = isset($options['proxy_port']) ? $options['proxy_port'] : 8080;
            curl_setopt($ch, CURLOPT_PROXY, $host . ":" . $port);
        }

        if (isset($options['proxy_user'])) {
            // $options['http_proxy_userpw'] == 'username:password'
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $options['proxy_user'] . ':' . $options['proxy_pass']);
        }

        if (isset($options['user'])) {
            curl_setopt($ch, CURLOPT_USERPWD, $options['user'] . ':' . $options['pass']);
        }

        if (!isset($options['soapaction'])) {
            $options['soapaction'] = '';
        }
       
        $tst1= $authstringvar;
        //send permission credential as http headers
       // $tmp=$profile->_authSignature;
       // $authstring=$this->buildAuthString($profile->_authToken,$this->_profile->_authSignature,$this->_profile->_authTimestamp);
        curl_setopt($ch, CURLOPT_HTTPHEADER ,    array('Content-Type: text/xml;charset=' . $this->encoding, 'SOAPAction: "'.$options['soapaction'].'"',$authstringvar));
        curl_setopt($ch, CURLOPT_USERAGENT ,     $this->_userAgent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
        if ($this->timeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout); //times out after 4s
        }

        curl_setopt($ch, CURLOPT_POSTFIELDS,       $msg);
        curl_setopt($ch, CURLOPT_URL,              $this->url);
        curl_setopt($ch, CURLOPT_POST,             1);
        curl_setopt($ch, CURLOPT_FAILONERROR,      0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,   1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,   1);
        curl_setopt($ch, CURLOPT_HEADER,           1);
        if (defined('CURLOPT_HTTP_VERSION')) {
            curl_setopt($ch, CURLOPT_HTTP_VERSION, 1);
        }

        if (isset($options['curl'])) {
            foreach ($options['curl'] as $key => $val) {
                curl_setopt($ch, $key, $val);
            }
        }

        // Save the outgoing XML. This doesn't quite match _sendHTTP
        // as CURL generates the headers, but having the XML is
        // usually the most important part for tracing/debugging.
        $this->outgoing_payload = $msg;

        $this->incoming_payload = curl_exec($ch);
        if (!$this->incoming_payload) {
            $m = 'curl_exec error ' . curl_errno($ch) . ' ' . curl_error($ch);
            curl_close($ch);
            return $this->_raiseSoapFault($m);
        }
        curl_close($ch);

        if (!$this->_parseResponse()) {
            return $this->fault;
        }

        return $this->response;
    }
function buildAuthString($auth_token,$auth_signature,$auth_timestamp)
{
	$authString="token=".$auth_token.",signature=".$auth_signature.",timestamp=".$auth_timestamp ;
	return $authString;
}
}
