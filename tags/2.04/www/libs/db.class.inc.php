<?php
//////////////////////////////////////////
//
//	db.class.inc.php
//
//	Class to create easy to use
//	interface with the database
//
//	By David Slater
//	June 2009
//
//////////////////////////////////////////


class db {

	////////////////Private Variables//////////

	private $link; //mysql database link
	private $host;	//hostname for the database
	private $database; //database name
	private $username; //username to connect to the database
	private $password; //password of the username

	////////////////Public Functions///////////

	public function __construct($host,$database,$username,$password) {
		$this->open($host,$database,$username,$password);


	}
	public function __destruct() {
		$this->close();

	}

	//open()
	//$host - hostname
	//$port - mysql port
	//$database - name of the database
	//$username - username to connect to the database with
	//$password - password of the username
	//$port - mysql port, defaults to 3306
	//opens a connect to the database
	public function open($host,$database,$username,$password,$port = 3306) {
		//Connects to database.
		$this->link = mysql_connect($host .":" . $port,$username,$password);
		if ($this->link) {
			@mysql_select_db($database,$this->link) or die("Unable to select database " . $database);
			$this->host = $host;
			$this->database = $database;
			$this->username = $username;
			$this->password = $password;
		}
		else { return false;
		}

	}

	//close()
	//closes database connection
	public function close() {
		if ($this->link) {
			mysql_close($this->link);
		}
	}

	//insert_query()
	//$sql - sql string to run on the database
	//returns the id number of the new record, 0 if it fails
	public function insert_query($sql) {
		if (mysql_query($sql,$this->link)) {
			return mysql_insert_id($this->link);
		}
		else {
			return 0;
		}

	}

	//build_insert()
	//$table - string - database table to insert data into
	//$data - associative array with index being the column and value the data.
	//returns the id number of the new record, 0 if it fails
	public function build_insert($table,$data) {
		$sql = "INSERT INTO " . $table;
		$values_sql = "VALUES(";
		$columns_sql = "(";
		$count = 0;
		foreach ($data as $key=>$value) {
			if ($count == 0) {
				$columns_sql .= $key;
				$values_sql .= "'" . mysql_real_escape_string($value,$this->get_link()) . "'";
			}
			else {
				$columns_sql .= "," . $key;
				$values_sql .= ",'" . mysql_real_escape_string($value,$this->get_link()) . "'";
			}

			$count++;
		}
		$values_sql .= ")";
		$columns_sql .= ")";
		$sql = $sql . $columns_sql . " " . $values_sql;
		return $this->insert_query($sql);
	}
	
	//non_select_query()
	//$sql - sql string to run on the database
	//For update and delete queries
	//returns true on success, false otherwise
	public function non_select_query($sql) {
		return mysql_query($sql,$this->link);
	}

	//query()
	//$sql - sql string to run on the database
	//Used for SELECT queries
	//returns an associative array of the select query results.
	public function query($sql) {
		$result = mysql_query($sql,$this->link);
		return $this->mysqlToArray($result);
	}

	//query_single()
	//$sql - sql string to run on the database
	//$row - row to retrieve
	//$field - column to retrieve
	//returns a single value from the specified row and field.
	public function query_single($sql,$row,$field) {
		$result = mysql_query($sql,$this->link);
		return mysql_result($result,$row,$field);

	}
	//getLink
	//returns the mysql resource link
	public function get_link() {
		return $this->link;
	}

	/////////////////Private Functions///////////////////

	//mysqlToArray
	//$mysqlResult - a mysql result
	//returns an associative array of the mysql result.
	private function mysqlToArray($mysqlResult) {
		$dataArray = array();
		$i =0;
		while($row = mysql_fetch_array($mysqlResult,MYSQL_ASSOC)){
			foreach($row as $key=>$data) {
				$dataArray[$i][$key] = $data;
			}
			$i++;
		}
		return $dataArray;
	}

}

?>
