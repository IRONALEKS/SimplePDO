<?php
	/**
	* @author Huan Aleks Karbazal <8ironaleks8@gmail.com>
	* A class made to simplify a lot of PDO stuff
	*/
	class SimplePDO
	{
		/**
		* @const DEFAULT_DRIVER_NAME Default driver name
		*/
		const DEFAULT_DRIVER_NAME = 'mysql';
		/**
		* @const DEFAULT_SERVER_NAME Default server name
		*/
		const DEFAULT_SERVER_NAME = 'localhost';
		/**
		* @const DEFAULT_SERVER_DATABASE_NAME Default server database name
		*/
		const DEFAULT_SERVER_DATABASE_NAME = 'main_database';
		/**
		* @const DEFAULT_SERVER_CHARSET Default server charset
		*/
		const DEFAULT_SERVER_CHARSET = 'utf8';
		/**
		* @const DEFAULT_SERVER_USERNAME Default server username
		*/
		const DEFAULT_SERVER_USERNAME = 'root';
		/**
		* @const DEFAULT_SERVER_PASSWORD Default server password
		*/
		const DEFAULT_SERVER_PASSWORD = '';




		/**
		* @var string Instance driver name
		*/
		private $driver_name = null;
		/**
		* @var string Instance server name
		*/
		private $server_name = null;
		/**
		* @var string Instance server database name
		*/
		private $server_database_name = null;
		/**
		* @var string Instance server charset
		*/
		private $server_charset = null;
		/**
		* @var string Instance server username
		*/
		private $server_username = null;
		/**
		* @var string Instance server password
		*/
		private $server_password = null;



		/**
		* @var PDO The backing PDO database connection
		*/
		private $connection = null;
		/**
		* @var bool Is connection open or not
		*/
		private $is_connected = false;



		/**
		* Default connect function, calls connect(driver_name, server_name, server_database_name, server_charset, server_username, server_password)
		* with the default values(SimplePDO::DEFAULT_DRIVER_NAME, SimplePDO::DEFAULT_SERVER_NAME, SimplePDO::DEFAULT_SERVER_DATABASE_NAME, SimplePDO::DEFAULT_SERVER_CHARSET, SimplePDO::DEFAULT_SERVER_USERNAME, SimplePDO::DEFAULT_SERVER_PASSWORD)
		*/
		public function default_connect()
		{
			$this->connect(SimplePDO::DEFAULT_DRIVER_NAME, SimplePDO::DEFAULT_SERVER_NAME, SimplePDO::DEFAULT_SERVER_DATABASE_NAME, SimplePDO::DEFAULT_SERVER_CHARSET, SimplePDO::DEFAULT_SERVER_USERNAME, SimplePDO::DEFAULT_SERVER_PASSWORD);
		}
		
		/**
		* Connects to a database
		* @param string $driver_name The name of the driver
		* @param string $server_name The name of the server
		* @param string $server_database_name The name of the database
		* @param string $server_charset The character set of the database
		* @param string $server_username The username
		* @param string $server_password The password
		*/
		public function connect($driver_name,$server_name,$server_database_name,$server_charset,$server_username,$server_password)
		{
			//SET'S ALL THE INSTANCE VARIABLES TO THE PARAMETER ONES
			$this->driver_name = $driver_name;
			$this->server_name = $server_name;
			$this->server_database_name = $server_database_name;
			$this->server_charset = $server_charset;
			$this->server_username = $server_username;
			$this->server_password = $server_password;

			//THE DATA SOURCE NAME
			$DSN = "$this->driver_name:host=$this->server_name;dbname=$this->server_database_name;charset=$this->server_charset";

			//THE OPTIONS FOR THE PDO CONNECTION
			$options = array(PDO::ATTR_EMULATE_PREPARES=>false, PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION);

			try
			{
				//CREATE NEW PDO CONNECTION AND CONNECT TO THE DATABASE
				$temporary_connection = new PDO($DSN, $this->server_username, $this->server_password, $options);

				//SUCCESSFULLY CONNECTED TO THE DATABASE
				$this->connection = $temporary_connection;
				$this->is_connected = true;
			}
			catch(PDOException $ex)
			{
				//COULDN'T CONNECT TO THE DATABASE
				$this->connection = null;
				$this->is_connected = false;
			}
		}
		
		/**
		* Returns true if this object is connected to a database, false otherwise 
		* @return bool true if this object is connected to a database, false otherwise
		*/
		public function isConnected()
		{
			return $this->is_connected;
		}
		
		/**
		* Closes the backing PDO database connection
		*/
		public function close()
		{
			//SETTING THE PDO VARIABLE TO NULL CLOSES IT
			$this->connection=null;
		}
		
		/**
		* Prepares a Statement
		* @param string $sql_query The sql query to be prepared
		* @return PreparedStatement The prepared statement on success, false otherwise
		*/
		public function prepareStatement($sql_query)
		{
			//THE RESULT OF THIS FUNCTION
			$result = false;

			try
			{
				//CREATE A PREPARED STATEMENT
				$prepared = $this->connection->prepare($sql_query);

				//SUCCESSFULLY CREATED A PREPARED STATEMENT
				$result = new PreparedStatement();
				$result->UNSAFE_init($prepared);
			}
			catch(PDOException $ex)
			{

			}

			//RETURN THE RESULT OF THIS FUNCTION
			return $result;
		}

		/**
		* Returns the ID of the last inserted row
		* @return string The id on success, false otherwise
		*/
		public function lastInsertID()
		{
			$result = $this->connection->lastInsertId();

			if($result>0)
			{
				return $result;
			}
			else
			{
				return false;
			}
		}

		/**
		* Begins a transaction
		* @return bool Returns true on success, false otherwise
		*/
		public function beginTransaction()
		{
			return $this->connection->beginTransaction();
		}

		/**
		* Commits the previous changes
		* @return bool Returns true on success, false otherwise
		*/
		public function commit()
		{
			return $this->connection->commit();
		}

		/**
		* Rolls back the previous changes
		* @return bool Returns true on success, false otherwise
		*/
		public function rollback()
		{
			return $this->connection->rollBack();
		}

		/**
		* Escapes sql injection of the given string and quotes it
		* @param string The string to be escaped and quoted
		* @return string The already escaped and quoted string
		*/
		public function escape_and_quote($string)
		{
			return $this->connection->quote($string);
		}








		/**
		* Returns the backing PDO database connection of this object
		* This function is UNSAFE meaning it should only be used for debugging
		* or inner simplepdo purposes only, and not the user itself
		* @return mixed Returns the backing database connection
		*/
		public function UNSAFE_getConnection()
		{
			return $this->connection;
		}
	}

	/**
	* @author Huan Aleks Karbazal <8ironaleks8@gmail.com>
	* A class made to simplify a lot of PDOPreparedStatement stuff
	*/
	class PreparedStatement
	{
		/**
		* @var mixed The backing PDO prepared statement
		*/
		private $backing_prepared_statement = null;

		/**
		* Initializes this PreparedStatement(shouldn't be used by the user itself)
		* @param mixed $backing_prepared_statement The PDO prepared statement
		*/
		public function UNSAFE_init($backing_prepared_statement)
		{
			//SET THE BACKING PDO PREPARED STATEMENT
			$this->backing_prepared_statement = $backing_prepared_statement;
		}

		/**
		* Binds the value to the parameter
		* @param mixed $parameter The parameter identifier
		* @param mixed $value The value to bind to the parameter
		* @param integer $data_type The data type of the value PDO::PARAM_*
		* @return bool Returns true on success, false otherwise
		*/
		public function set($parameter, $value, $data_type)
		{
			return $this->backing_prepared_statement->bindValue($parameter, $value, $data_type);
		}

		/**
		* Executes this PreparedStatements query
		* @param bool $return_data Whether this execution should return a result set or not
		* @return mixed Returns (true(success) or false(failure)) if $return_data was set to false, 
		* (StatementResult(success) or false(failure)) if $return_data was set to true
		*/
		public function execute($return_data)
		{
			//EXECUTE QUERY AND GET THE RESULT
			$result = $this->backing_prepared_statement->execute();

			//IF THIS EXECUTE SHOULD RETURN DATA
			if($return_data)
			{
				//IF THE EXECUTION WENT SUCCESSFULLY
				if($result)
				{
					//CREATE AND INITIALIZE A STATEMENT RESULT OBJECT
					$result = new StatementResult();
					$result->UNSAFE_init($this->backing_prepared_statement);
				}
			}

			//RETURN THE RESULT
			return $result;
		}






		/**
		* Returns the backing prepared statement of this object
		* This function is UNSAFE meaning it should only be used for debugging
		* or inner simplepdo purposes only, and not the user itself
		* @return mixed Returns the backing prepared statement of this object
		*/
		public function UNSAFE_getBackingPreparedStatement()
		{
			return $this->backing_prepared_statement;
		}
	}
	
	/**
	* @author Huan Aleks Karbazal <8ironaleks8@gmail.com>
	* A class made to simplify a lot of result set fetching stuff
	*/
	class StatementResult
	{
		/**
		* @var mixed $result Contains the backing PreparedStatement
		*/
		private $backing_prepared_statement = null;
		/**
		* @var bool $have_row True if the result set contains a row, false otherwise
		*/
		private $have_row = false;
		/**
		* @var mixed[] $row A row from the result set
		*/
		private $row = null;
		
		/**
		* Initializes this StatementResult(shouldn't be used by the user itself)
		* @param mixed $prepared_statement The PreparedStatement
		*/
		public function UNSAFE_init($backing_prepared_statement)
		{
			//SET THE BACKING PREPARED STATEMENT
			$this->backing_prepared_statement = $backing_prepared_statement;

			//FETCH FIRST ROW
			$this->fetchAssoc();
		}

		/**
		* Fetches the next row from the result set
		*/
		private function fetchAssoc()
		{
			//FETCH FIRST ROW
			$fetch_result = $this->backing_prepared_statement->fetch(PDO::FETCH_ASSOC);

			//IF THE FETCH WAS SUCCESSFUL
			if($fetch_result!==false)
			{
				//SET THE CURRENT ROW TO THE FETCHED ONE AND SET THE $this->have_row VARIABLE TO TRUE
				$this->row = $fetch_result;
				$this->have_row = true;
			}
			else
			{
				//SET THE CURRENT ROW TO TO NULL AND SET THE $this->have_row VARIABLE TO FALSE
				$this->row = null;
				$this->have_row = false;
			}
		}

		/**
		* Returns true if the result set has a current row, false otherwise
		* @return bool Returns true if the result set has a current row, false otherwise
		*/
		public function haveRow()
		{
			return $this->have_row;
		}

		/**
		* Returns the value at the index inside the current row
		* @param string the index of the value inside the current row
		* @return mixed Returns the value at the index inside the current row
		*/
		public function getFromRow($index)
		{
			return $this->row[$index];
		}

		/**
		* Returns the current row
		* @return mixed[] Returns the current row
		*/
		public function getRow()
		{
			return $this->row;
		}

		/**
		* Fetches the next row in the result set
		*/
		public function nextRow()
		{
			$this->fetchAssoc();
		}

		/**
		* Closes the backing result set
		*/
		public function close()
		{
			//CLOSES THE CURSOR JUST IN CASE EVEN THOUGH IT IS NOT ALWAYS NECESSARY
			$this->backing_prepared_statement->closeCursor();
			$this->backing_prepared_statement = null;

			//SET THE CURRENT ROW TO TO NULL AND SET THE $this->have_row VARIABLE TO FALSE
			$this->row = null;
			$this->have_row = false;
		}
	}
?>