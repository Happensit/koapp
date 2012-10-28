<?php defined('SYSPATH') or die('No direct script access.');

class HTTP_Exception_403 extends Kohana_HTTP_Exception_403 {

    public function get_response()
    {
        if (Kohana::$environment !== Kohana::PRODUCTION)
        {
            return parent::get_response();
        }

        if (Request::current()->is_initial() AND ! Request::current()->is_ajax())
        {
            $view = View::factory('error/403');

            $response = Response::factory()
                ->status(403)
                ->body($view->render());
        }
        else
        {
            $response = Response::factory()
                ->status(403)
                ->body('Forbidden! 403');
        }

        return $response;
    }

} // END class HTTP_Exception_403