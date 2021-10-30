<?php

//addons/example/api/hello_world.php
/**
 * Example Remote API call, returns the text "Hello World!" or "Hello Name!" if
 * the argument "name" is sent.
 *
 * If starting from the Example addon as a starting point for your addon, be
 * sure to delete the entire api/ directory if your addon does not need to add
 * any Remote API calls.
 *
 * Remote API files are procedural files, that are meant to return whatever
 * data should be sent back to the client that made the API call.  It can
 * actually handle
 *
 * In an API call, we can access certain "core objects" since this is called from
 * inside the geoAPI class:
 *
 * - $this->db = The standard db object, can be used to access the database
 * - $this->session = The Session object, note that since this is a remote API call
 *   no cookies are sent, so the user will always be guest.
 * - $this->product_configuration = The {@link geoPC} object.
 * - $this->addon = the {@link geoAddon} object.
 *
 * @see geoAPI
 * @package ExampleAddon
 */

/**
 * Be sure to put this next part, to ensure the file is never called directly.
 * It must be processed through the Remote API system.
 */

if (!defined('IN_GEO_API')) {
    exit('No access.');
}

if ($some_error) {
    //example of how to return back an error
    return $this->failure("Example failure message.");
}

if (isset($args['name'])) {
    //The argument "name" was sent.  Reply with a special message.
    return "Hello {$args['name']}!  Welcome to the World!";
}
//no name was sent.  Reply with "Hello World!"
return "Hello World!";

//The file always needs to return a value.  Do not echo anything, as it will be
//discarded by the API system, instead return the string if you want to send a
//string back to the API client that made the call.
