<?php defined('SYSPATH') or die('No direct script access.');

class HTTP_Exception extends Kohana_HTTP_Exception {

    public function get_response()
    {
        Kohana_Exception::log($this);

        if (Kohana::$environment !== Kohana::PRODUCTION)
        {
            return parent::get_response();
        }

        if (Request::current()->is_initial() AND ! Request::current()->is_ajax())
        {
            $view = View::factory('error/default');

            $response = Response::factory()
                ->status($this->getCode())
                ->body($view->render());
        }
        else
        {
            $response = Response::factory()
                ->status($this->getCode())
                ->body('Failed to load content!');
        }

        return $response;
    }

} // END class HTTP_Exception