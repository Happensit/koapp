<?php defined('SYSPATH') or die('No direct script access.');

abstract class App_Error {

    public static $error_view   = 'error/500';
    public static $content_type = 'text/html';

    /**
     * Replaces Kohana_Exception::handler()
     *
     * @param   Exception  $e
     * @return  boolean
     */
    public static function handler(Exception $e)
    {
        $response = Error::_handler($e);
        echo $response->send_headers()->body();
        exit(1);
    }

    /**
     * Replaces Kohana_Exception::_handler()
     *
     * @param   Exception  $e
     * @return  boolean
     */
    public static function _handler(Exception $e)
    {
        try
        {
            $response = Error::response($e);

            return $response;
        }
        catch (Exception $e)
        {
            ob_get_level() and ob_clean();
            header('Content-Type: text/plain; charset='.Kohana::$charset, TRUE, 500);
            echo 'Internal Server Error! 500';
            exit(1);
        }
    }

    /**
     * Replaces Kohana_Exception::response()
     *
     * @param   Exception  $e
     * @return  Response
     */
    public static function response(Exception $e)
    {
        try
        {
            if ( ! Request::current()->is_ajax())
            {
                $response = Response::factory();
                $response->status(500);
                $response->headers('Content-Type', static::$content_type.'; charset='.Kohana::$charset);
                $view = View::factory(static::$error_view);
                $response->body($view->render());
            }
            else
            {
                $response = Response::factory();
                $response->status(200);
                $response->headers('Content-Type', static::$content_type.'; charset='.Kohana::$charset);
                $response->headers('Content-Type', 'text/plain');
                $response->body('Server not responding!');
            }
        }
        catch (Exception $e)
        {
            $response = Response::factory();
            $response->status(500);
            $response->headers('Content-Type', 'text/plain');
            $response->body('Internal Server Error! 500');
        }

        return $response;
    }

    /**
     * Replace Kohana::shutdown_handler()
     *
     * @uses    Error::handler
     * @return  void
     */
    public static function shutdown_handler()
    {
        if (Kohana::$errors AND $error = error_get_last() AND in_array($error['type'], Kohana::$shutdown_errors))
        {
            ob_get_level() and ob_clean();
            Error::handler(new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
            exit(1);
        }
    }

} // END class App_Error