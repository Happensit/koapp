<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Session extends Kohana_Session {

    public static $default = 'database';

}