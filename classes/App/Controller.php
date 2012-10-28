<?php defined('SYSPATH') or die('No direct script access.');

abstract class App_Controller extends Kohana_Controller {

    protected $output = NULL;
    protected $render = TRUE;

    /**
    * Loads the Output as object.
    *
    * @return  void
    */
    public function before()
    {
        parent::before();

        if ($this->request->is_ajax())
        {
            $this->render = FALSE;
        }

        if ($this->request->is_initial() AND $this->render === TRUE)
        {
            $config = Kohana::$config->load('templates');

            if ( ! $template = $config->get(strtolower($this->request->controller())))
            {
                $template = $config->get('default');
            }

            $this->output = View::factory($template['view']);

            $this->output->title       = $template['meta']['title'];
            $this->output->description = $template['meta']['description'];
            $this->output->keywords    = $template['meta']['keywords'];
            $this->output->script      = '';
            $this->output->content     = '';

            $this->inject_css($template['css']);
            $this->inject_js($template['js']);
        }
        else
        {
            $this->output = new stdClass();
        }
    }

    /**
    * Adds the css files in the initial set.
    *
    * @param   string|array $css css-file(s)
    * @return  void
    */
    protected function inject_css($css = NULL)
    {
        if ( ! isset($this->output->css))
        {
            $this->output->css = array();
        }

        if (empty($css)) return;

        if ( ! is_array($css))
        {
            $css = array($css);
        }

        foreach ($css as $item)
        {
            $this->output->css[] = $item;
        }
    }

    /**
    * Adds the js files in the initial set.
    *
    * @param   string|array $js js-file(s)
    * @return  void
    */
    protected function inject_js($js = NULL)
    {
        if ( ! isset($this->output->js))
        {
            $this->output->js = array();
        }

        if (empty($js)) return;

        if ( ! is_array($js))
        {
            $js = array($js);
        }

        foreach ($js as $item)
        {
            $this->output->js[] = $item;
        }
    }

    protected function inject_script($file, $data = NULL)
    {
        $view = new View($file, array('data' => $data));

        $this->output->script = $view->render();
    }

    /**
    * Assigns the Output as the request response.
    *
    * @return  void
    */
    public function after()
    {
        if ($this->output instanceof View)
        {
            $this->response->body($this->output);
        }
        else
        {
            if ($this->request->is_ajax() AND is_array($this->output->content))
            {
                $this->response->headers('Content-Type', 'application/json');

                $this->output->content = json_encode($this->output->content);
            }

            $this->response->body($this->output->content);
        }

        parent::after();
    }

} // END class App_Controller