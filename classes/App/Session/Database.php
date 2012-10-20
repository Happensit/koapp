<?php defined('SYSPATH') OR die('No direct access allowed.');

/*-------------------------------------
CREATE TABLE `sessions` (
  `session_id` varchar(24) NOT NULL,
  `ip_address` varchar(128) DEFAULT '',
  `user_agent` varchar(128) DEFAULT '',
  `last_active` int(10) unsigned NOT NULL,
  `contents` text NOT NULL,
  PRIMARY KEY (`session_id`),
  KEY `last_active` (`last_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
---------------------------------------*/

class App_Session_Database extends Session {

    protected $db;

    protected $table = 'sessions';

    protected $columns = array(
        'session_id'  => ':session_id',
        'ip_address'  => ':ip_address',
        'user_agent'  => ':user_agent',
        'last_active' => ':last_active',
        'contents'    => ':contents',
    );

    protected $gc = 500;

    protected $session_id;

    protected $update_id;


    public function __construct(array $config = NULL, $id = NULL)
    {
        if ( ! isset($config['group']))
        {
			$config['group'] = Database::$default;
        }

        $this->db = Database::instance($config['group']);

        if (isset($config['table']))
        {
            $this->table = (string) $config['table'];
        }

        if (isset($config['gc']))
        {
            $this->gc = (int) $config['gc'];
        }

        parent::__construct($config, $id);

        if (mt_rand(0, $this->gc) == $this->gc)
        {
            $this->_gc();
        }
    }


    public function id()
    {
        return $this->session_id;
    }


    protected function _read($id = NULL)
    {
        if ($id OR $id = Cookie::get($this->_name))
        {
            $sql = "SELECT contents FROM ".$this->table." WHERE session_id = :session_id LIMIT 1";

            $result = DB::query(Database::SELECT, $sql)->param(':session_id', $id)->execute($this->db);

            if ($result->count())
            {
                $this->session_id = $this->update_id = $id;

                return $result->get('contents');
            }
        }

        $this->_regenerate();

        return NULL;
    }


    protected function _regenerate()
    {
        $sql = "SELECT session_id FROM ".$this->table." WHERE session_id = :session_id LIMIT 1";

        $query = DB::query(Database::SELECT, $sql)->bind(':session_id', $id);

        do
        {
            $id = str_replace('.', '-', uniqid(NULL, TRUE));

            $result = $query->execute($this->db);
        }
        while ($result->count());

        return $this->session_id = $id;
    }


    protected function _write()
    {
        $keys = array_keys($this->columns);
        $vals = array_values($this->columns);

        $parameters = array(
            $vals[0] => $this->session_id,
            $vals[1] => Request::$client_ip,
            $vals[2] => Request::$user_agent,
            $vals[3] => $this->_data['last_active'],
            $vals[4] => $this->__toString()
        );

        if ($this->update_id === NULL)
        {
            $columns = '('.implode(', ', $keys).')';
            $values  = '('.implode(', ', $vals).')';

            $sql = "INSERT INTO ".$this->table." ".$columns." VALUES ".$values;

            $query = DB::query(Database::INSERT, $sql)->parameters($parameters);
        }
        else
        {
            if ($this->update_id == $this->session_id)
            {
                unset($this->columns['session_id']);
            }

            $updates = '';

            foreach ($this->columns AS $key => $val)
            {
                $updates .= $key.' = '.$val.', ';
            }

            $updates = substr($updates, 0, -2);

            $sql = "UPDATE ".$this->table." SET ".$updates." WHERE session_id = :old_session_id";

            $query = DB::query(Database::UPDATE, $sql);

            $values = array_values($this->columns);

            $query->parameters($parameters + array(':old_session_id' => $this->update_id));
        }

        $query->execute($this->db);

        $this->update_id = $this->session_id;

        Cookie::set($this->_name, $this->session_id, $this->_lifetime);

        return TRUE;
    }


    protected function _destroy()
    {
        if ($this->update_id === NULL)
        {
            return TRUE;
        }

        $sql = "DELETE FROM ".$this->table." WHERE session_id = :session_id";

        $query = DB::query(Database::DELETE, $sql)->param(':session_id', $this->update_id);

        try
        {
            $query->execute($this->db);

            Cookie::delete($this->_name);
        }
        catch (Exception $e)
        {
            return FALSE;
        }

        return TRUE;
    }


    protected function _gc()
    {
        if ($this->_lifetime)
        {
            $expires = $this->_lifetime;
        }
        else
        {
            $expires = Date::MONTH;
        }

        $sql = "DELETE FROM ".$this->table." WHERE last_active < :time";

        $query = DB::query(Database::DELETE, $sql)->param(':time', time() - $expires)->execute($this->db);
    }


    protected function _restart()
    {
        $this->_regenerate();

        return TRUE;
    }
	
} // END class App_Session_Database