<?php defined('SYSPATH') or die('No direct script access.');

class HTTP_Exception_404 extends Kohana_HTTP_Exception_404 {

    public function get_response()
    {
        if (Kohana::$environment >= Kohana::DEVELOPMENT)
        {
            return parent::get_response();
        }

        if ($this->_request->is_initial())
        {
            $view = View::factory('errors/404');

            $response = Response::factory()
                ->status(404)
                ->body($view->render());
        }
        else
        {
            $response = Response::factory()
                ->status(404)
                ->body('Failed to load content!');
        }

        return $response;
    }

} // END class HTTP_Exception_404