<?php
/**
* PHPLib Sessions using PHP 4 built-in Session Support.
* 
* @copyright 1998,1999 NetUSE AG, Boris Erdmann, Kristian Koehntopp
*            2000 Teodor Cimpoesu <teo@digiro.net>
* @author    Teodor Cimpoesu <teo@digiro.net>, Ulf Wendel <uw@netuse.de>, Maxim Derkachev <kot@books.ru
* @version   session4.inc,v 1.14 2001/08/29 07:26:44 richardarcher
* @access    public
* @package   PHPLib
*/ 
class Session {


  /**
  * Session name 
  * 
  */
  var $classname = "Session";

  
  /**
  * Name of the autoinit-File, if any.
  *
  * @var  string
  */ 
  var $auto_init = "";
  
  
  /**
  * Depreciated! There's no need for page_close in PHP4 sessions. 
  * @deprec 
  * @var  integer
  */
  var $secure_auto_init = 1;
  
  
  /**
  * Don't work. Use something better than this class' property to set the marker.
  * @deprec 
  * Marker: Did we already include the autoinit file? 
  *
  * @var  boolean
  */  
  var $in = false;

  
  /**
  * Current session id.
  * 
  * @var  string
  * @see  id(), Session()
  */
  var $id = "";
  
  
  /**
  * [Current] Session name.
  *
  * @var  string
  * @see  name(), Session()
  */
  var $name = "";
  
  /**
  *
  * @var  string
  */
  var $cookie_path = '/';
  
  
  /**
  *
  * @var  strings
  */
  var $cookiename;
  
  
  /**
  * 
  * @var  int
  */
  var $lifetime = 0;
  
  
  /**
  * If set, the domain for which the session cookie is set.
  * 
  * @var  string 
  */
  var $cookie_domain = '';
  
  
  /**
  *
  * @var    string  
  * @deprec 
  */
  var $fallback_mode;
  
  
  /**
  * Was the PHP compiled using --enable-trans-sid?
  *
  * PHP 4 can automatically rewrite all URLs to append the session ID 
  * as a get parameter if you enable the feature. If you've done so, 
  * the old session3.inc method url() is no more needed, but as your
  * application might still call it you can disable it by setting this 
  * flag to false.
  *  
  * @var  boolean
  */
  var $trans_id_enabled = true;
  
  
  /**
  * See the session_cache_limit() options
  * 
  * @var  string
  */
  var $allowcache = 'nocache';
  
  
  /**
  * Sets the session name before the session starts.
  * 
  * Make sure that all derived classes call the constructor
  * 
  * @see  name()
  */
  function Session() {
    $this->name($this->name);
  } // end constructor
  
  
  /**
  * Start a new session or recovers from an existing session 
  * 
  * @return boolean   session_start() return value
  * @access public
  */
  function start() {
    
    $this->set_tokenname(); 
    $this->put_headers();

    $ok = session_start();
    $this->id = session_id();
    
    return $ok;
  } // end func start

  
  /**
  * Sets or returns the name of the current session
  *
  * @param  string  If given, sets the session name
  * @return string  session_name() return value
  * @access public  
  */
  function name($name = '') {
  
    if ($name = (string)$name) {
    
      $this->name = $name;
      $ok = session_name($name);
      
    } else {
    
      $ok = session_name();
      
    }
    
    return $ok;
  } // end func name
  
   
  /**
  * Returns the session id for the current session. 
  *
  * If id is specified, it will replace the current session id.
  *
  * @param  string  If given, sets the new session id
  * @return string  current session id
  * @access public
  */
  function id($sid = '') {
    
    if (!$sid)
      $sid = ("" == $this->cookiename) ? $this->classname : $this->cookiename;
      
    if ($sid = (string)$sid) {
    
      $this->id = $sid;
      $ok = session_id($sid);
      
    } else {
    
      $ok = session_id();
      
    }
    
    return $ok;
  } // end func id
  
  
  /**
  * @brother id()
  * @deprec  
  * @access public  
  */  
  function get_id($sid = '') {
    return $this->id($sid);
  } // end func get_id

  
  /**
  * Register the variable(s) that should become persistent.
  *
  * @param   mixed String with the name of one or more variables seperated by comma
  *                 or a list of variables names: "foo"/"foo,bar,baz"/{"foo","bar","baz"}
  * @return boolean  false if registration failed, true on success.
  * @access public  
  */
  function register ($var_names) {
    if (!is_array($var_names)) {
	
      // spaces spoil everything
      $var_names = trim($var_names);
      return session_register( preg_split('/\s*,\s*/', $var_names) );
    
    }
    
    return session_register($var_names); 
  } // end func register

  /**
  * see if a variable is registered in the current session
  *
  * @param  $var_name a string with the variable name 
  * @return false if variable not registered true on success.
  * @access public   
  */
  function is_registered ($var_name) {
    $var_name = trim($var_name);  // to be sure
    return session_is_registered($var_name);
  } // end func is_registered
	
  
  /**
  * Recall the session registration for named variable(s)
  *
  * @param	  mixed   String with the name of one or more variables seperated by comma
  *                   or a list of variables names: "foo"/"foo,bar,baz"/{"foo","bar","baz"}
  * @return boolean  false if any error, true on success.
  * @access public  
  */
  function unregister ($var_names) {
    
    $ok = true;
    foreach (explode (',', $var_names) as $var_name) {
      $ok = $ok && session_unregister ( trim($var_name) );
    }
    
    return $ok;
  } // end func unregister
  
  
  /**
  * Delete the cookie holding the session id.
  * 
  * RFC: is this really needed? can we prune this function?
  * 		 the only reason to keep it is if one wants to also
  *		 unset the cookie when session_destroy()ing,which PHP
  *		 doesn't seem to do (looking @ the session.c:940)
  * uw: yes we should keep it to remain the same interface, but deprec. 
  *
  * @deprec 
  * @access public  
  * @global $HTTP_COOKIE_VARS
  */
  function put_id() {
    global $HTTP_COOKIE_VARS;
     
    if (get_cfg_var ('session.use_cookies') == 1) {
      $cookie_params = session_get_cookie_params();
      setCookie($this->name, '', 0, $cookie_params['path'], $cookie_params['domain']);
      $HTTP_COOKIE_VARS[$this->name] = "";
    }
    
  } // end func put_id
  
  /**
  * Delete the current session destroying all registered data.
  * 
  * Note that it does more but the PHP 4 session_destroy it also 
  * throws away a cookie is there's one.
  *
  * @return boolean session_destroy return value
  * @access public  
  */
  function delete() {
   
    $this->put_id();
    
    return session_destroy();
  } // end func delete

  
  /**
  * Helper function: returns $url concatenated with the current session id
  * 
  * Don't use this function any more. Please use the PHP 4 build in 
  * URL rewriting feature. This function is here only for compatibility reasons.
  *
  * @param	$url	  URL to which the session id will be appended
  * @return string  rewritten url with session id included
  * @see    $trans_id_enabled
  * @global $HTTP_COOKIE_VARS
  * @deprec 
  * @access public  
  */
  function url($url) {
     global $HTTP_COOKIE_VARS;
    
    if ($this->trans_id_enabled) 
      return $url;
    
    // Remove existing session info from url
    $url = ereg_replace(
      "([&?])".quotemeta(urlencode($this->name))."=".$this->id."(&|$)",
      "\\1", $url);

    // Remove trailing ?/& if needed
    $url = ereg_replace("[&?]+$", "", $url);

    if (!$HTTP_COOKIE_VARS[$this->name]) {
      $url .= ( strpos($url, "?") != false ?  "&" : "?" ) . urlencode($this->name) . "=" . $this->id;
    }

    // Encode naughty characters in the URL
    $url = str_replace(array("<", ">", " ", "\"", "'"), 
                       array("%3C", "%3E", "+", "%22", "%27"), $url);
    return $url;
  } // end func url


  /**
  * @brother url()
  */  
  function purl($url) {
    print $this->url($url);
  } // end func purl

  
  /**
  * Get current request URL.
  * 
  * WARNING: I'm not sure with the $this->url() call. Can someone check it?
  * WARNING: Apache variable $REQUEST_URI used - 
  * this it the best you can get but there's warranty the it's set beside 
  * the Apache world.
  * 
  * @return string
  * @global $REQUEST_URI
  * @access public  
  */
  function self_url() {
    return $this->url(getenv('REQUEST_URI'));
  } // end func self_url


  /**
  * Print the current URL
  * @return void
  */
  function pself_url() {
    print $this->self_url();
  } // end func pself_url


  /**
  * Stores session id in a hidden variable (part of a form).
  *
  * @return string
  * @access public  
  */
  function get_hidden_session() {
  
    if ($this->trans_id_enabled) 
      return "";
    else 
      return sprintf('<input type="hidden" name="%s" value="%s">', 
                    $this->name, 
                    $this->id
      );
      
  } // end fun get_hidden_session


  /**
  * @brother  get_hidden_session
  * @return   void
  */
  function hidden_session() {
    print $this->get_hidden_session();
  } // end func hidden_session


  /**
  * @brother get_hidden_session
  */
  function get_hidden_id() {
    return $this->get_hidden_session();
  } // end func get_hidden_id


  /**
  * @brother hidden_session
  */
  function hidden_id() {
    print $this->get_hidden_session();
  } // end func hidden_id

 
  /**
  * Prepend variables passed into an array to a query string.
  * 
  * @param  array   $qarray an array with var=>val pairs
  * @param  string  $query_string probably getenv ('QUERY_STRING')
  * @return string  the resulting quetry string, of course :)
  * @access public  
  */
  function add_query($qarray, $query_string = '') {

    ('' == $query_string) && ($query_string = getenv ('QUERY_STRING'));
    $qstring = $query_string . (strrpos ($query_string, '?') == false ? '?' : '&');

    foreach ($qarray as $var => $val) {
      $qstring .= sprintf ( '%s=%s&', $var, urlencode ($val)) ;
    }

    return $qstring;
  } // end func add_query

  
  /**
  * @brother  add_query()
  */
  function padd_query ($qarray, $query_string = '') {
    print $this->add_query($qarray, $query_string);
  } // end func padd_query

  /**
  * Get the serialized string of session variables
  * 
  * Note that the serialization format is different from what it 
  * was in session3.inc. So clear all session data when switching 
  * to the PHP 4 code, it's not possible to load old session. 
  * 
  * @return string
  */
  function serialize() {
    return session_encode();
  } // end func serialze

  
  /**
  * Import (session) variables from a string 
  * 
  * @param  string
  *
  * @return boolean
  */
  function deserialize (&$data_string) {
    return session_decode($data_string);
  } // end func deserialize

  /**
  * ?
  * 
  */
  function set_tokenname(){
  
      //$this->name = ("" == $this->cookiename) ? $this->classname : $this->cookiename;
      session_name ($this->classname);
      
      if (!$this->cookie_domain) {
        $this->cookie_domain = get_cfg_var ("session.cookie_domain");
      }
      
      if (!$this->cookie_path && get_cfg_var('session.cookie_path')) {
        $this->cookie_path = get_cfg_var('session.cookie_path');
      } elseif (!$this->cookie_path) {
        $this->cookie_path = "/";
      }
      
      if ($this->lifetime > 0) {
        $lifetime = time()+$this->lifetime*60;
      } else {
        $lifetime = 0;
      }
      
      session_set_cookie_params($lifetime, $this->cookie_path, $this->cookie_domain);
  } // end func set_tokenname
  
  
  /**
  * ?
  *
  */
  function put_headers() {
    # set session.cache_limiter corresponding to $this->allowcache.
    
    switch ($this->allowcache) {

      case 'passive':
      case 'public':
        session_cache_limiter ('public');  
        break;
 
      case 'private':
        session_cache_limiter ('private'); 
        break;

      case 'nocache':
        session_cache_limiter ('nocache');  
        break;
				
			default :
				session_cache_limiter ('');
				break;
    }
  } // end func put_headers

  
  /**
  * Reimport HTTP_GET_VARS into the global namespace previously overriden by session variables.
  * @see  reimport_post_vars(), reimport_cookie_vars()
  */
  function reimport_get_vars() {
    $this->reimport_any_vars("HTTP_GET_VARS");
  } // end func reimport_get_vars


  /**
  * Reimport HTTP_POST_VARS into the global namespace previously overriden by session variables.
  * @see  reimport_get_vars(), reimport_cookie_vars()
  */
  function reimport_post_vars() {
    $this->reimport_any_vars("HTTP_POST_VARS");
  } // end func reimport_post_vars

  
  /**
  * Reimport HTTP_COOKIE_VARS into the global namespace previously overriden by session variables.
  * @see  reimport_post_vars(), reimport_fwr_vars()
  */
  function reimport_cookie_vars() {
    $this->reimport_any_vars("HTTP_COOKIE_VARS");
  } // end func reimport_cookie_vars

  
  /**
  *
  * @var  array
  */
  function reimport_any_vars($arrayname) {
    global $$arrayname;
    $GLOBALS = array_merge ($GLOBALS, $arrayname);
  } // end func reimport_any_vars


} // end func session

?>
