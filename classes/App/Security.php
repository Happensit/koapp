<?php defined('SYSPATH') or die('No direct script access.');

abstract class App_Security extends Kohana_Security {

    protected static $csrf_key = 'xid';

    /**
     * Checking if the post is real and has a valid CSRF
     *
     *  Security::valid_post($_POST, TRUE)
     *
     * @param   array $post
     * @param   bool  $csrfv
     * @return  bool
     */
    public static function valid_post( array $post = array(), $csrfv = FALSE)
    {
        if (empty($post))
        {
            return FALSE;
        }

        if (Request::current()->method() !== HTTP_Request::POST)
        {
            return FALSE;
        }

        if ($csrfv !== FALSE)
        {
            if ( ! isset($post[static::$csrf_key]))
            {
                return FALSE;
            }

            return Security::check($post[static::$csrf_key]);
        }

        return TRUE;
    }

} // END class App_Security