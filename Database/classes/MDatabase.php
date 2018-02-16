<?php namespace EC\Database;
defined( '_ESPADA' ) or die( NO_ACCESS);

use E, EC;

class MDatabase extends E\Module
{

	private $prefix = null;

	private $mysqli = null;
	private $useTransactions = true;

	private $transaction_Autocommit = true;
	private $transaction_InProgress = false;

	private $lastQuery = null;

	public function __construct($prefix = 'default')
	{
		parent::__construct();

		$this->prefix = $prefix;
	}

	/* Escapes */
	public function escapeArray_Int($array)
	{
		$database_arr_vals = [];
		foreach ($array as $arr_val)
			$database_arr_vals[] = $this->escapeInt($arr_val);
		return '(' . implode(',', $database_arr_vals) . ')';
	}

	public function escapeArray_String($array)
	{
		$database_arr_vals = [];
		foreach ($array as $arr_val)
			$database_arr_vals[] = $this->escapeString($arr_val);
		return '(' . implode(',', $database_arr_vals) . ')';
	}

	public function escapeBool($value)
	{
		if ($value === null)
			return 'null';

		if ((bool)$value)
			return '1';

		return '0';
	}

	public function escapeTime_Date($time)
	{
		if ($time === null)
			return 'NULL';

		return '\'' . gmdate('Y-m-d', (0 + $time)) . '\'';
	}

	// public function escapeDateMillis($time)
	// {
	// 	if ($time === null)
	// 		return 'null';
	//
	// 	return '\'' . gmdate('Y-m-d', $time / 1000) . '\'';
	// }

	public function escapeTime_DateTime($time)
	{
		if ($time === null)
			return 'NULL';

		return '\'' . gmdate('Y-m-d H:i:s', (0 + $time)) . '\'';
	}

	public function escapeFloat($value)
	{
		if ($value === null)
			return 'NULL';

		return (string)((float)$value);
	}

	public function escapeInt($value)
	{
		if ($value === null)
			return 'NULL';

		return (string)((int)$value);
	}

	public function escapeString($value)
	{
		if ($value === null)
			return 'NULL';

		return '\'' . $this->mysqli->escape_string((string)$value) . '\'';
	}

	/* Gets */
	public function getAffectedRows()
	{
		return $this->mysqli->affected_rows;
	}

	public function getError()
	{
		return $this->mysqli->error;
	}

	public function getErrorNumber()
	{
		return $this->mysqli->errno;
	}

	public function getInsertedId()
	{
		return $this->mysqli->insert_id;
	}

	public function getLastQuery()
	{
		return $this->lastQuery;
	}

	public function requireNoTransaction()
	{
		if (!$this->transaction_IsAutocommit())
			throw new \Exception('Transaction detected.');
	}

	public function requireTransaction()
	{
		if ($this->transaction_IsAutocommit())
			throw new \Exception('No transaction detected.');
	}

	/* Transaction */
	public function transaction_IsAutocommit()
	{
		return $this->transaction_Autocommit;
	}

	public function transaction_Commit()
	{
		$this->transaction_InProgress = false;
		return $this->mysqli->commit();
	}

	public function transaction_Finish($commit = null)
	{
		$result = true;

		if ($commit === null && $this->transaction_InProgress)
			$result = $this->transaction_Rollback();
		else if ($commit === true)
			$result = $this->transaction_Commit();
		else if ($commit === false)
			$result = $this->transaction_Rollback();

		$this->mysqli->autocommit(true);
		$this->transaction_Autocommit = true;

		return $result;
	}

	public function transaction_Rollback()
	{
		$this->transaction_InProgress = false;
		return $this->mysqli->rollback();
	}

	public function transaction_Start()
	{
		if (!$this->useTransactions)
			throw new \Exception('Transactions not supported.');

		$this->transaction_InProgress = false;

		$this->mysqli->autocommit(false);
		$this->transaction_Autocommit = false;
	}

	/* Unescapes */
	public function unescapeBool($bool)
	{
		if ($bool === null)
			return null;

		return $bool == 0 ? false : true;
	}

	public function unescapeFloat($value)
	{
		if ($value === null)
			return null;

		return (float)$value;
	}

	public function unescapeTime_Date($date)
	{
		if ($date === null)
			return null;

		return strtotime($date . ' UTC');
	}

	// public function unescapeTimeDateMillis($date)
	// {
	// 	if ($date == null)
	// 		return null;
	//
	// 	return strtotime($date . ' UTC') * 1000;
	// }

	public function unescapeTime_DateTime($date_time)
	{
		if ($date_time === null)
			return null;

		return strtotime($date_time . ' UTC');
	}

	public function unescapeInt($value)
	{
		if ($value === null)
			return null;

		return (int)$value;
	}

	public function unescapeString($value)
	{
		if ($value === null)
			return null;

		return $value . '';
	}

	/* Query */
	public function query_Select($query)
	{
		$this->requirePreInitialize();

		$this->lastQuery = $query;

		$this->transaction_InProgress = true;

		if ($result = $this->mysqli->query($query)) {
			$assoc = [];

			while ($row = $result->fetch_assoc())
				$assoc[] = $row;

			$result->close();

			return $assoc;
		}

		throw new \Exception('Database error: ' . $query . ' # ' .
				$this->mysqli->error);

		return null;
	}

	public function query_Execute($query)
	{
		$this->requirePreInitialize();

		$this->lastQuery = $query;

		$this->transaction_InProgress = true;

		if ($result = $this->mysqli->query($query))
			return $result == 1;

		throw new \Exception('Database error: ' . $query . ' # ' .
				$this->mysqli->error);

		return false;
	}

	public function quote($name)
	{
		$this->requirePreInitialize();

		$name_array = explode('.', $name);

		$q_name_array = [];
		foreach ($name_array as $name_part) {
			if ($name_part === '*')
				$q_name_array[] = $name_part;
			else
				$q_name_array[] = '`' .
						$this->mysqli->real_escape_string($name_part) . '`';
		}

		return implode('.', $q_name_array);
	}

	/* Initialization */
	protected function _preInitialize(\E\Site $site)
	{
		$this->connect();
	}

	protected function _deinitialize()
	{
		$this->disconnect();
	}

	public function connect()
	{
		if ($this->mysqli !== null)
			throw new \Exception('`disconnect` from database before calling `conenct`.');

		$config = new EC\CConfig('Database');

		$host = 	$config->getRequired("{$this->prefix}_Host");
		$user = 	$config->getRequired("{$this->prefix}_User");
		$password = $config->getRequired("{$this->prefix}_Password");
		$name = 	$config->getRequired("{$this->prefix}_Name");

		$port = $config->get("{$this->prefix}_Port", 3306);
		$charset_encoding = $config->get("{$this->prefix}_CharsetEncoding", 'utf-8');
		$this->useTransactions = $config->get("{$this->prefix}_UseTransactions", true);

		$this->mysqli = new \mysqli($host, $user, $password, $name, $port);

		if ($this->mysqli->connect_errno)
			throw new \Exception('Cannot connect to MySQL database.');

		$this->mysqli->set_charset($charset_encoding);
		$this->mysqli->query('SET NAMES ' . str_replace('-', '', $charset_encoding));
	}

	public function disconnect()
	{
		$this->transaction_Finish(false);
		$this->mysqli->close();
		$this->mysqli = null;
	}

}
