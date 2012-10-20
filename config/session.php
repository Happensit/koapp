<?php defined('SYSPATH') OR die('No direct script access.');

return array(
    'database' => array(
        'name'      => 'kohana',
        'group'     => 'default',
        'table'     => 'sessions',
        'lifetime'  => 1209600,
        'gc'        => 500,
    ),
);