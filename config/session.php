<?php defined('SYSPATH') OR die('No direct script access.');

return array(
    'database' => array(
        'name'      => 'kohana',
        'group'     => 'default',
        'table'     => 'sessions',
        'lifetime'  => 1209600,
        'gc'        => 500,
        'columns'   => array(
            'session_id'  => 'session_id',
            'ip_address'  => 'ip_address',
            'user_agent'  => 'user_agent',
            'last_active' => 'last_active',
            'contents'    => 'contents',
        ),
    ),
);