<?php defined('SYSPATH') or die('No direct script access.');

class HTTP_Exception extends Kohana_HTTP_Exception {

    public function get_response()
    {
        Kohana_Exception::log($this);

        if (Kohana::$environment >= Kohana::DEVELOPMENT)
        {
            return parent::get_response();
        }

        $view = View::factory('errors/default');

        $response = Response::factory()
            ->status($this->getCode())
            ->body($view->render());

        return $response;
    }

} // END class HTTP_Exception