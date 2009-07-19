<?php
/**
 * @package SCore
 */

/**
 * @ignore
 */
function _new_user($row) {
	return new User($row);
}

/**
 * An object representing a row in the "users" table.
 *
 * The currently logged in user will always be accessable via the global variable $user
 */
class User {
	var $config;
	var $database;

	var $id;
	var $name;
	var $email;
	var $join_date;
	var $admin;

	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	* Initialisation                                               *
	*                                                              *
	* User objects shouldn't be created directly, they should be   *
	* fetched from the database like so:                           *
	*                                                              *
	*    $user = User::by_name("bob");                             *
	* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

	/**
	 * One will very rarely construct a user directly, more common
	 * would be to use User::by_id, User::by_session, etc
	 */
	public function User($row) {
		$this->id = int_escape($row['id']);
		$this->name = $row['name'];
		$this->email = $row['email'];
		$this->join_date = $row['joindate'];
		$this->admin = ($row['admin'] == 'Y');
	}

	public static function by_session($name, $session) {
		global $config, $database;
		$row = $database->get_row(
				"SELECT * FROM users WHERE name = ? AND md5(concat(pass, ?)) = ?",
				array($name, get_session_ip($config), $session)
		);
		return is_null($row) ? null : new User($row);
	}

	public static function by_id($id) {
		assert(is_numeric($id));
		global $database;
		$row = $database->get_row("SELECT * FROM users WHERE id = ?", array($id));
		return is_null($row) ? null : new User($row);
	}

	public static function by_name($name) {
		assert(is_string($name));
		global $database;
		$row = $database->get_row("SELECT * FROM users WHERE name = ?", array($name));
		return is_null($row) ? null : new User($row);
	}

	public static function by_name_and_hash($name, $hash) {
		assert(is_string($name));
		assert(is_string($hash));
		assert(strlen($hash) == 32);
		global $database;
		$row = $database->get_row("SELECT * FROM users WHERE name = ? AND pass = ?", array($name, $hash));
		return is_null($row) ? null : new User($row);
	}

	public static function by_list($offset, $limit=50) {
		assert(is_numeric($offset));
		assert(is_numeric($limit));
		global $database;
		$rows = $database->get_all("SELECT * FROM users WHERE id >= ? AND id < ?", array($offset, $offset+$limit));
		return array_map("_new_user", $rows);
	}


	/*
	 * useful user object functions start here
	 */

	/**
	 * Test if this user is anonymous (not logged in)
	 *
	 * @var bool
	 */
	public function is_anonymous() {
		global $config;
		return ($this->id == $config->get_int('anon_id'));
	}

	/**
	 * Test if this user is an administrator
	 *
	 * @var bool
	 */
	public function is_admin() {
		return $this->admin;
	}

	public function set_admin($admin) {
		assert(is_bool($admin));
		global $database;
		$yn = $admin ? 'Y' : 'N';
		$database->Execute("UPDATE users SET admin=? WHERE id=?", array($yn, $this->id));
		log_info("core-user", "Made {$this->name} admin=$yn");
	}

	public function set_password($password) {
		global $database;
		$hash = md5(strtolower($this->name) . $password);
		$database->Execute("UPDATE users SET pass=? WHERE id=?", array($hash, $this->id));
		log_info("core-user", "Set password for {$this->name}");
	}
}
?>
