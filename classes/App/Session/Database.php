<?php defined('SYSPATH') OR die('No direct access allowed.');

/*CREATE TABLE `sessions` (
  `session_id` varchar(24) NOT NULL,
  `ip_address` varchar(128) DEFAULT '',
  `user_agent` varchar(128) DEFAULT '',
  `last_active` int(10) unsigned NOT NULL,
  `contents` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_active` (`last_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;*/

class App_Session_Database extends Session {

    protected $_db;

    protected $_table = 'sessions';

    protected $_columns = array(
        'session_id'  => 'session_id',
        'ip_address'  => 'ip_address',
        'user_agent'  => 'user_agent',
        'last_active' => 'last_active',
        'contents'    => 'contents',
    );

    // Garbage collection requests
    protected $_gc = 500;

    // The current session id
    protected $_session_id;

    // The old session id
    protected $_update_id;


    public function __construct(array $config = NULL, $id = NULL)
    {
        if ( ! isset($config['group']))
        {
			$config['group'] = Database::$default;
        }

        $this->_db = Database::instance($config['group']);

        if (isset($config['table']))
        {
            $this->_table = (string) $config['table'];
        }

        if (isset($config['gc']))
        {
            $this->_gc = (int) $config['gc'];
        }

        if (isset($config['columns']))
        {
            $this->_columns = $config['columns'];
        }

        parent::__construct($config, $id);

        if (mt_rand(0, $this->_gc) == $this->_gc)
        {
            $this->_gc();
        }
    }

    public function id()
    {
        return $this->_session_id;
    }

    protected function _read($id = NULL)
    {
        if ($id OR $id = Cookie::get($this->_name))
        {
            $result = DB::select(array($this->_columns['contents'], 'contents'))
                ->from($this->_table)
                ->where($this->_columns['session_id'], '=', ':id')
                ->limit(1)
                ->param(':id', $id)
                ->execute($this->_db);

            if ($result->count())
            {
                // Set the current session id
                $this->_session_id = $this->_update_id = $id;

                // Return the contents
                return $result->get('contents');
            }
        }

        // Create a new session id
        $this->_regenerate();

        return NULL;
    }

    protected function _regenerate()
    {
        // Create the query to find an ID
        $query = DB::select($this->_columns['session_id'])
            ->from($this->_table)
            ->where($this->_columns['session_id'], '=', ':id')
            ->limit(1)
            ->bind(':id', $id);

        do
        {
            // Create a new session id
            $id = str_replace('.', '-', uniqid(NULL, TRUE));

            // Get the the id from the database
            $result = $query->execute($this->_db);
        }
        while ($result->count());

        return $this->_session_id = $id;
    }

    protected function _write()
    {
        if ($this->_update_id === NULL)
        {
            // Insert a new row
            $query = DB::insert($this->_table, $this->_columns)
                ->values(array(':new_id', ':hostname', ':useragent', ':active', ':contents'));
        }
        else
        {
            // Update the row
            $query = DB::update($this->_table)
                ->value($this->_columns['ip_address'], ':hostname')
                ->value($this->_columns['user_agent'], ':useragent')
                ->value($this->_columns['last_active'], ':active')
                ->value($this->_columns['contents'], ':contents')
                ->where($this->_columns['session_id'], '=', ':old_id');

            if ($this->_update_id !== $this->_session_id)
            {
                // Also update the session id
                $query->value($this->_columns['session_id'], ':new_id');
            }
        }

        $query
            ->param(':new_id', $this->_session_id)
            ->param(':old_id', $this->_update_id)
            ->param(':hostname', Request::$client_ip)
            ->param(':useragent', Request::$user_agent)
            ->param(':active', $this->_data['last_active'])
            ->param(':contents', $this->__toString());

        $query->execute($this->_db);

        // The update and the session id are now the same
        $this->_update_id = $this->_session_id;

        // Update the cookie with the new session id
        Cookie::set($this->_name, $this->_session_id, $this->_lifetime);

        return TRUE;
    }

    protected function _destroy()
    {
        if ($this->_update_id === NULL)
        {
            // Session has not been created yet
            return TRUE;
        }

        // Delete the current session
        $query = DB::delete($this->_table)
            ->where($this->_columns['session_id'], '=', ':id')
            ->param(':id', $this->_update_id);

        try
        {
            // Execute the query
            $query->execute($this->_db);

            // Delete the cookie
            Cookie::delete($this->_name);
        }
        catch (Exception $e)
        {
            // An error occurred, the session has not been deleted
            return FALSE;
        }

        return TRUE;
    }

    protected function _gc()
    {
        if ($this->_lifetime)
        {
            // Expire sessions when their lifetime is up
            $expires = $this->_lifetime;
        }
        else
        {
            // Expire sessions after one month
            $expires = Date::MONTH;
        }

        // Delete all sessions that have expired
        DB::delete($this->_table)
            ->where($this->_columns['last_active'], '<', ':time')
            ->param(':time', time() - $expires)
            ->execute($this->_db);
    }

    protected function _restart()
    {
        $this->_regenerate();

        return TRUE;
    }
	
} // END class App_Session_Database