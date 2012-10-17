<?php defined('SYSPATH') or die('No direct script access.');

return array(
    'default' => array(
        'view' => 'template/default',
        'meta' => array(
    	    'title'       => 'Default Page',
    	    'description' => 'Default page description',
            'keywords'    => 'default, page keywords',
        ),
    	'css' => array(
            'static/css/master.css',
        ),
        'js' => array(
            'static/js/jquery.js',
        ),
    ),
);