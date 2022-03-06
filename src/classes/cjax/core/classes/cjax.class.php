<?php

 /*
    CJAX FRAMEWORK
    ajax made easy with cjax

    -- DO NOT REMOVE THIS --
    -- AUTHOR COPYRIGHT MUST REMAIN INTACT --
    CJAX FRAMEWORK
    Written by: Carlos Galindo
    Website: @WEBSITE@
    Email: cjxxi@msn.com
    Date: @DATE@
    Last Updated:  05/22/2008
*/

/**
 * Load core events
 */

require_once 'core.class.php';
class CJAX_FRAMEWORK extends CoreEvents
{

    /**
     * Set debug mode on/off
     *
     */
    function debug($on = true)
    {
        $this->xml("<debug>" . (($on) ? 1 : 0) . "</debug>");
    }

    /**
     * set the focus to an element
     *
     * var $element_id
     */
    function focus($element_id)
    {
        $this->xml("<element>$element_id</element>");
    }

    /**
     * Display a message in the middle of the screen
     *
     * @param string $data
     * @param integer $seconds if specified, this is the number of seconds the message will appear in the screen
     * then it will dissapear.
     */
    function message($data, $seconds = '')
    {
        if ($seconds) {
            $seconds = "<secs>$seconds</secs>";
        }
        $data = $this->encode($data);
        $this->xml("<data>$data</data>{$seconds}");
    }

    /**
     * Send a click event to an element
     *
     * @param string $element_id
     */
    function click($element_id)
    {
        $this->xml("<element>$element_id</element>");
    }

    /**
     * Create a dinamic textbox on using dom.
     *
     * @param string $element
     * @param string $parent
     * @param string $label
     * @param string $value
     * @param string $class
     * @return TextBox
     */
    function textbox($new_textbox_id, $element_parent, $label = '', $value = '', $class = '')
    {
        $this->xml("<element>$new_textbox_id</element><parent>$element_parent</parent><label>$label</label><value>$value</value><class>$class</class>");
    }

    /**
     * Add event to elements
     * --
     * AddEventTo();
     *
     * @param string $element
     * @param string $event
     * @param string $method
     */
    function AddEventTo($element, $event = 'onload', $method = '')
    {
        $method = str_replace('+', 'PLUSSIGN', $method);

        $method = urlencode($method);
        $this->xml("<element>$element</element><event>$event</event><method>$method</method>");
    }

    /**
     * An alian for AddEventTo
     *
     * @param string $element
     * @param string $event
     * @param string $method
     */
    function setEvent($element, $event = 'onload', $method = '')
    {
        $this->AddEventTo($element, $event, $method);
    }

/*  *//**
     * When checking for inputs on the back-end to return a user friendly-error
     * use invalidate, this function is to highlight a specified text element if the input does not validate
     *
     *
     * @param string $elem [ELEMENT ID]
     *//*
    function invalidate($element_id,$data='')
    {
        $data = $this->encode($data);
        $this->xml("<element>$element_id</element><error>$data</error>");
    }*/

    /**
     * Apply a CSS defined class to elements which  contains a specified type
     * such as "text" or "checkbox", "password"
     *
     * @param string $type
     * @param string $class
     * @param string $tag
     */
    function applyClassToType($type, $class, $tag = null)
    {
        $this->xml("<type>$type</type><class>$class</class><tag>$tag</tag>");
    }

    /**
     * Apply a CSS class to any elements of the page specifying a tag
     * for example  "input" will apply that class to all elements input in the page
     *
     * @param string $tag
     * @param string $class
     */
    function applyClass($tag, $class)
    {
        $this->xml("<elem_tag>$tag</elem_tag><class>$class</class>");
    }

    /**
     * Hide any element on the page
     *
     * @param string $element_id
     */
    function hide($element_id)
    {
        $this->xml("<element>$element_id</element>");
    }

    /**
     * creates an html element on the page
     * @param string $obj
     * @param string $type
     * @param string $parent
     * @param string $class
     * @param string $html
     */
    function create_element($obj, $type = 'div', $parent, $class = '', $html = '')
    {
        $this->xml("<do>create_element</do><element>$obj</element><parent>$parent</parent><class>$class</class><type>$type</type><html>$html</html>");
    }

    /**
     * *set value to an element
     * Usage: $CJAX->set_value('element_id','Hello World');
     * @param string $element_id
     * @param string $value
     */
    function set_value($element, $value = '')
    {
        $this->xml("<element>{$element}</element><value>{$value}</value>");
    }


    /**
     * This function is to get content of files or send large amount of data
     *
     */
    function updateContent($element, $data, $itsSource = false)
    {
        if ($itsSource) {
            $data  = $this->syntax_hilight($data);
        }
        $data = urlencode($data);
        $this->xml("<element>$element</element><data>$data</data>");
    }

    /**
     * Update any element on the page by specifying the element ID
     * Usage:  $CJAX->hide('element_id');
     * @param string $obj
     * @param string $data
     */
    function update($obj, $data)
    {
        $data = $this->encode($data);
        $this->xml("<element>$obj</element><data>$data</data>");
    }

    /**
     * Will execute a command in a specified amouth of time
     * e.g $CJAX->wait(5);
     * Will wait 5 seconds before executes the next CJAX command
     *
     * @param integer $seconds
     */

    function wait($seconds)
    {
        $this->xml("<seconds>{$seconds}</seconds>");
    }

    /**
     * This is an alias for remove function.
     * will remove an specified element from the
     * page.
     *
     * @param string $obj
     */
    function destroy($obj)
    {
        $this->remove($obj);
    }

    /**
     * Will remove an specified element from the page
     *
     * @param string $obj
     */
    function remove($obj)
    {
         $this->xml("<do>remove</do><element>$obj</element>");
    }

    /**
     * Redirect the page.
     * this is a recommended alternative to the built-in php function Header();
     *
     * @param string $where [URL]
     */
    function location($where = "")
    {
         $this->xml("<url>$where</url>");
    }

    /**
     * Alert a message
     *
     * @param string $message
     */
    function alert($message)
    {
        $message = $this->encode($message);
        $this->xml("<msg>$message</msg>");
    }

    /**
     * deprecated
     * will load a page on the "fly"
     *
     * @param string $element
     * @param string $page
     */
    function loadpage($element, $page)
    {
        $this->xml("<element>$element</element><page>$page</page>");
    }

    /**
     * Will execute a javascript function
     *
     * @param string $function_name
     * @param string $params
     */
    function load_function($function_name, $params = 'source')
    {
        $this->xml("<function>$function_name</function><param>$params</param>");
    }

    /**
     * Will dynamically load external javascript files into the page, hiding the source code.
     *
     * @param string $path
     * @param optional string $append_tag
     */
    function load_script($src, $use_domain = false)
    {
        if ($use_domain) {
            $use_domain = '__domain__';
        } else {
            $use_domain = '';
        }
        $this->xml("<script>$use_domain/$src</script>");
    }

    /**
     * deprecated
     * a quick way to make ajax calls
     *
     * @param string $element_id
     * @param string $url
     * @param string $mode
     */
    function fire_page($element_id, $url, $mode = 'get')
    {
        $this->xml("<element>$element_id</element><url>$url</url><mode>$mode</mode><text>Loading..</text><skiploop>true</skiploop>");
    }

    /**
     * deprecated
     * used with the old wait() method to execute delayed functionalities
     * @param string $function_name
     * @param string $params
     * @return unknown
     */
    function load_method($function_name, $params = '')
    {
        return "<function>$function_name</function><param>$params</param>";
    }

    /**
     * Get the value on any html element such as inputs, text, checkbox, radio buttons, select elements
     *
     * @param string $element_id
     * @return string
     */
    function value($element_id)
    {
        return "'+CJAX.passvalue('$element_id')+'";
    }
}
