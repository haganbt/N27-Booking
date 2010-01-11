<?
  function wrap_session_start() {

    return session_start();

  }

  function wrap_session_register($variable) {

    return session_register($variable);

  }

  function wrap_session_is_registered($variable) {

    return session_is_registered($variable);

  }

  function wrap_session_unregister($variable) {

    return session_unregister($variable);

  }

  function wrap_session_id($sessid='') {

    if ($sessid) 
       return session_id($sessid);
    else
       return session_id();
      
  }

  function wrap_session_name($name='') {

    if ($name)
      return session_name($name);
    else
      return session_name();

  }

  function wrap_session_close() {

    if (function_exists('session_close')) {
      return session_close();
    }

  }

  function wrap_session_destroy() {

    return session_destroy();

  }
?>