<?php
if( !defined( "WORDY" ) ) define( "WORDY",  0 ); // very wordy...

/***
  class RDB
  by Asao Kamei @ WorkSpot.JP, 
  to be replaced by PEAR's DB (or equivalent class)... 
***/

class RdbConnException extends Exception {
}

class rdb
{
	var $db_type; // type of db; postgresql, mysql, sqlite, etc. 
    var $db_name; // db name used for MySQL
	var $conn;    // db connector resource
	var $sqlh;    // sql result resource
    
    /* -------------------------------------------------------------- */
    function rdb() 
    {
        if( WORDY > 3 ) echo "<br><b>rdb::instance created...</b><br>\n";
    }
    /* -------------------------------------------------------------- */
    function connect( $db_con, $new=FALSE ) 
    {
		$cons = $this->parseDbCon( $db_con );
		if( isset( $cons[ 'db' ] ) ) {
			$this->db_type = $cons[ 'db' ];
		}
		else 
		if( defined( 'FORMSQL_DB_TYPE' ) ) {
			$this->db_type = FORMSQL_DB_TYPE;
		}
		else {
			$this->db_type = FORMSQL_USE_POSTGRESQL;
		}
		if( WORDY ) echo "rdb::connecting( <b>{$db_con}, $new</b> ) to {$this->db_type}...<br>\n ";
		$this->conn = FALSE;
		switch( $this->db_type ) 
		{
		case FORMSQL_USE_PDO:
			if( WORDY > 5 ) echo "PDO( {$cons['dsn']}, {$cons['user']}, {$cons['password']} );<br>";
			try { 
				$this->conn = @new PDO( $cons["dsn"], $cons["user"], $cons["password"] );
				//$this->conn = @new PDO( 'mysql:host=localhost;dbname=sales', 'admin', 'admin' );
			}
			catch ( PDOException $exception ) {
				throw new RdbConnException( $exception->getMessage() );
			}
			break;
		
		case FORMSQL_USE_SQLITE:
			if( WORDY > 3 ) echo "sqlite_open( {$cons['file']}, {$cons['mode']} );<br>";
			$this->conn = @sqlite_open( $cons["file"], $cons["mode"] );
			break;
		
		case FORMSQL_USE_POSTGRESQL:
		case FORMSQL_USE_POSTGRESQL8x:
			if( !function_exists( 'pg_connect' ) ) {
				throw new RdbConnException( 'pg_connect not exists' );
			}
			if( $new ) {
				$this->conn = @pg_connect( $db_con, PGSQL_CONNECT_FORCE_NEW );
			}
			else {
				$this->conn = pg_connect( $db_con );
			}
			break;
		
		case FORMSQL_USE_ODBC_DB:
		case FORMSQL_USE_CACHE_DB:
			if( !function_exists( 'odbc_connect' ) ) {
				throw new RdbConnException( 'odbc_connect not exists' );
			}
			$this->conn = @odbc_connect( $cons["dsn"], $cons["user"], $cons["password"] );
			break;
		
		case FORMSQL_USE_MYSQL:
		case FORMSQL_USE_MYSQL5_EUC:
		
			if( !function_exists( 'mysql_connect' ) ) {
				throw new RdbConnException( 'mysql_connect not exists' );
			}
			$this->db_name = $cons[ "dbname" ];
			if( isset( $cons["port"] ) && $cons["port"] ) {
				$server = $cons["host"] . ":" . $cons["port"];
			} else {
				$server = $cons["host"];
			}
			if( isset( $cons["user"] ) && isset( $cons["password"] ) ) {
				if( $new ) {
					$this->conn = mysql_connect( $server, $cons["user"], $cons["password"], TRUE );
				}
				else {
					$this->conn = mysql_connect( $server, $cons["user"], $cons["password"] );
				}
			}
			elseif( isset( $cons["user"] ) ) {
				$this->conn = mysql_connect( $server, $cons["user"] );
			}
			else {
				$this->conn = mysql_connect( $server );
			}
			$success = mysql_select_db( $this->db_name, $this->conn );
			if( !$success ) { 
				$this->conn = FALSE; 
			}
			else if( PHP_CHAR_SET == CHAR_CODE_EUC  ) { 
				$this->exec( 'set names ujis' ); 
			}
			else { // default is UTF-8
				$this->exec( 'set names utf8' ); 
			}
			break;
		
		default:
			break;
		}
		if( !$this->conn ) {
			throw new RdbConnException( "Cannot connect to database \n({$this->db_type}:{$db_con}). " );
		}
        return $this->conn;
    }
    /* -------------------------------------------------------------- */
    function parseDbCon( $db_con )
    {
        $conn_str = array( 
			"db", 
			"dbname", "port", "host", "user", "password", 
			"file", "mode", 
			"dsn"
		);
        $return_array = array();
        foreach( $conn_str as $parameter )
        {
            $pattern = "/{$parameter}=(\S+)/";
            if( preg_match( $pattern, $db_con, $matches ) )
            {
                $return_array[ $parameter ] = $matches[1];
            }
        }
		if( WORDY > 5 ) wordy_table( $return_array, 'parseDbCon' );
        return $return_array;
    }
    /* -------------------------------------------------------------- */
    function query( $sql ) 
	{
		// SQL queries that requires returning set
		$this->sqlh = FALSE;
        if( WORDY > 3 ) echo "rdb::query( <font color=blue>$sql</font> )...<br>\n ";
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_PDO:
                $this->sqlh = @$this->conn->query( $sql );
            	break;
            
            case FORMSQL_USE_SQLITE:
                $this->sqlh = @sqlite_query( $this->conn, $sql );
            	break;
            
			default: 
				return @$this->exec( $sql );
				break;
			
		}
		return $this->sqlh;
    }
    /* -------------------------------------------------------------- */
    function exec( $sql ) 
	{
		// SQL queries that simply effects on database and have no returning set 
		// for PDO.
        if( WORDY > 3 ) echo "rdb::exec( <font color=blue>$sql</font> )...<br>\n ";
		if( !$this->conn ) return FALSE;
		
		$this->sqlh = FALSE;
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_PDO:
                $this->sqlh = @$this->conn->exec( $sql );
            	break;
            
            case FORMSQL_USE_SQLITE:
                $this->sqlh = @sqlite_exec( $this->conn, $sql );
            	break;
            
            case FORMSQL_USE_POSTGRESQL:
            case FORMSQL_USE_POSTGRESQL8x:
				if( function_exists( 'pg_query' ) ) {
	                $this->sqlh = @pg_query( $this->conn, $sql );
				}
				else {
	                $this->sqlh = @pg_exec( $this->conn, $sql );
				}
            	break;
            
            case FORMSQL_USE_ODBC_DB:
            case FORMSQL_USE_CACHE_DB:
                $this->sqlh = @odbc_exec( $this->conn, $sql );
            	break;
            
            case FORMSQL_USE_MYSQL:
            case FORMSQL_USE_MYSQL5_EUC:
                if( WORDY > 5 ) echo $this->db_name . ".. ";
                $this->sqlh = mysql_query( $sql, $this->conn );
            	break;
			
			default:
        }
        return $this->sqlh;
    }
    /* -------------------------------------------------------------- */
    function errorMessage() 
	{
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_PDO:
                if( is_object( $this->sqlh ) ) { 
					$err = $this->sqlh->errorInfo(); 
				}
				else { 
					$err = $this->conn->errorInfo(); 
				}
				return $err[2];
            
            case FORMSQL_USE_SQLITE:
                $error_code = @sqlite_last_error( $this->conn );
				return sqlite_error_string( $error_code );
            
            case FORMSQL_USE_POSTGRESQL:
            case FORMSQL_USE_POSTGRESQL8x:
                return @pg_errormessage( $this->conn );
            
            case FORMSQL_USE_ODBC_DB:
            case FORMSQL_USE_CACHE_DB:
                return @odbc_errormsg( $this->conn );
            
            case FORMSQL_USE_MYSQL:
            case FORMSQL_USE_MYSQL5_EUC:
                return @mysql_error( $this->conn );
			default:
        }
        return FALSE;
    }
    /* -------------------------------------------------------------- */
    function numFields() 
	{
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_PDO:
                if( is_object( $this->sqlh ) ) { 
					return $this->sqlh->columnCount(); 
				}
				else { 
					return 0; 
				}
			
            case FORMSQL_USE_POSTGRESQL:
            case FORMSQL_USE_POSTGRESQL8x:
				if( function_exists( pg_num_fields ) ) {
	                return @pg_num_fields( $this->sqlh );
				}
                return @pg_numfields( $this->sqlh );
            
            case FORMSQL_USE_ODBC_DB:
            case FORMSQL_USE_CACHE_DB:
                return @odbc_num_fields( $this->sqlh );
            
            case FORMSQL_USE_MYSQL:
            case FORMSQL_USE_MYSQL5_EUC:
                return mysql_num_fields( $this->sqlh );
			default:
        }
        return FALSE;
    }
    /* -------------------------------------------------------------- */
    function fieldName( $col ) 
	{
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_POSTGRESQL:
            case FORMSQL_USE_POSTGRESQL8x:
				if( function_exists( pg_field_name ) ) {
	                return @pg_field_name( $this->sqlh, $col );
				}
                return @pg_FieldName( $this->sqlh, $col );
            
            case FORMSQL_USE_ODBC_DB:
            case FORMSQL_USE_CACHE_DB:
                return @odbc_field_name( $this->sqlh, $col );
            
            case FORMSQL_USE_MYSQL:
            case FORMSQL_USE_MYSQL5_EUC:
                return @mysql_field_name( $this->sqlh, $col );
			default:
        }
        return FALSE;
    }
    /* -------------------------------------------------------------- */
    function result( $row, $col ) 
	{
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_PDO:
                if( is_object( $this->sqlh ) ) { 
					$rowdata = $this->sqlh->fetch( PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $row ); 
      	        	return $rowdata[ $col ];
				}
				else { 
					return FALSE; 
				}
				
            case FORMSQL_USE_SQLITE:
                if( is_resource( $this->sqlh ) ) { 
					sqlite_seek( $this->sqlh, $row );
					$data = sqlite_current( $this->sqlh, SQLITE_NUM ); 
					return $data[ $col ];
				}
				else { 
					return FALSE; 
				}
				
            case FORMSQL_USE_POSTGRESQL:
            case FORMSQL_USE_POSTGRESQL8x:
				if( function_exists( pg_fetch_result ) ) {
	                return @pg_fetch_result( $this->sqlh, $row, $col );
				}
                return @pg_Result( $this->sqlh, $row, $col );
            
            case FORMSQL_USE_ODBC_DB:
            case FORMSQL_USE_CACHE_DB:
                // PHP manual: may not work. 
                $rowdata = @odbc_fetchRow( $this->sqlh, $row );
                return $rowdata[ $col ];
            
            case FORMSQL_USE_MYSQL:
            case FORMSQL_USE_MYSQL5_EUC:
                $result = @mysql_result( $this->sqlh, $row, $col );
				if( WORDY > 5 ) echo "rdb::result( $row, $col ) => '{$result}'<br>";
				return $result;
			default:
        }
        return FALSE;
    }
    /* -------------------------------------------------------------- */
    function numRows() 
	{
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_PDO:
                if( is_object( $this->sqlh ) ) { 
					return $this->sqlh->rowCount(); 
				}
				else { 
					return 0; 
				}
			
            case FORMSQL_USE_SQLITE:
                if( is_resource( $this->sqlh ) ) { 
					return sqlite_num_rows( $this->sqlh ); 
				}
				else { 
					return $this->sqlh; 
				}
			
            case FORMSQL_USE_POSTGRESQL:
            case FORMSQL_USE_POSTGRESQL8x:
				if( function_exists( 'pg_num_rows' ) ) {
	                return @pg_num_rows( $this->sqlh );
				}
                return @pg_NumRows( $this->sqlh );
            
            case FORMSQL_USE_ODBC_DB:
            case FORMSQL_USE_CACHE_DB:
                return @odbc_num_rows( $this->sqlh );
            
            case FORMSQL_USE_MYSQL:
            case FORMSQL_USE_MYSQL5_EUC:
                return @mysql_num_rows( $this->sqlh );
			default:
        }
        return FALSE;
    }
    /* -------------------------------------------------------------- */
    function fetchAssoc( $row ) 
	{
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_PDO:
                if( is_object( $this->sqlh ) ) { 
					return $this->sqlh->fetch( PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $row ); 
				}
				else { 
					return FALSE; 
				}
				
            case FORMSQL_USE_SQLITE:
                if( is_resource( $this->sqlh ) ) { 
					sqlite_seek( $this->sqlh, $row );
					return sqlite_current( $this->sqlh, SQLITE_ASSOC ); 
				}
				else { 
					return FALSE; 
				}
				
            case FORMSQL_USE_POSTGRESQL:
            case FORMSQL_USE_POSTGRESQL8x:
	            return  @pg_fetch_assoc( $this->sqlh, $row );
				
            case FORMSQL_USE_MYSQL:
            case FORMSQL_USE_MYSQL5_EUC:
				if( is_int( $row ) && $row >= 0 ) {
					if( @mysql_data_seek( $this->sqlh, $row ) ) {
						$result = @mysql_fetch_assoc( $this->sqlh );
						if( WORDY > 5 ) wordy_table( $result, "rdb::fetchAssoc( $row )" );
						return $result;
					}
				}
            	break;
				
            case FORMSQL_USE_ODBC_DB:
            case FORMSQL_USE_CACHE_DB:
                return @odbc_fetchRow( $this->sqlh, $row );
			default:
				break;
        }
        return array();
    }
    /* -------------------------------------------------------------- */
    function cmdTuples() 
	{
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_PDO:
				if( is_numeric( $this->sqlh ) ) {
					return $this->sqlh;
				}
				else {
					return FALSE;
				}
			
            case FORMSQL_USE_SQLITE:
				if( is_numeric( $this->sqlh ) ) {
					return $this->sqlh;
				}
				else {
					return FALSE;
				}
			
            case FORMSQL_USE_POSTGRESQL:
            case FORMSQL_USE_POSTGRESQL8x:
                return @pg_cmdtuples( $this->sqlh );
            
            case FORMSQL_USE_MYSQL:
            case FORMSQL_USE_MYSQL5_EUC:
                // PHP manual: may not work. 
                return @mysql_affected_rows( $this->sqlh );
			
            case FORMSQL_USE_ODBC_DB:
            case FORMSQL_USE_CACHE_DB:
			default:
                // PHP manual: may not work. 
                return @odbc_num_rows( $this->sqlh );
            
        }
        return FALSE;
    }
    /* -------------------------------------------------------------- */
    function close() 
	{
		if( !$this->conn ) return TRUE;
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_POSTGRESQL:
            case FORMSQL_USE_POSTGRESQL8x:
				if( pg_close( $this->conn ) ) {
					$this->conn = NULL;
	                return TRUE;
				}
            	break;
            
            case FORMSQL_USE_MYSQL:
            case FORMSQL_USE_MYSQL5_EUC:
				if( mysql_close( $this->conn ) ) {
					$this->conn = NULL;
	                return TRUE;
				}
            	break;
            
			default:
            	break;
        }
        return FALSE;
    }
    /* -------------------------------------------------------------- */
    function free() 
	{
		if( !$this->sqlh ) return TRUE;
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_POSTGRESQL:
            case FORMSQL_USE_POSTGRESQL8x:
                if( @pg_freeresult( $this->sqlh ) ) {
					$this->sqlh = NULL;
					return TRUE;
				}
            	break;
            
            case FORMSQL_USE_MYSQL:
            case FORMSQL_USE_MYSQL5_EUC:
                if( @mysql_freeresult( $this->sqlh ) ) {
					$this->sqlh = NULL;
					return TRUE;
				}
            	break;
            
			default:
        }
        return FALSE;
    }
    /* -------------------------------------------------------------- */
    function begin() 
	{
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_PDO:
				return $this->conn->beginTransaction();
			default:
                return @$this->exec( "BEGIN;" );
        }
        return FALSE;
    }
    /* -------------------------------------------------------------- */
    function commit() 
	{
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_PDO:
				return $this->conn->commit();
			
			default:
                return @$this->exec( "COMMIT;" );
        }
        return FALSE;
    }
    /* -------------------------------------------------------------- */
    function rollback() 
	{
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_PDO:
				return $this->conn->beginTransaction();
			
			default:
                return @$this->exec( "ROLLBACK;" );
        }
        return FALSE;
    }
    /* -------------------------------------------------------------- */
    function lockTable( $table ) 
	{
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_POSTGRESQL:
            case FORMSQL_USE_POSTGRESQL8x:
                return @$this->exec( "LOCK TABLE {$table} IN ACCESS EXCLUSIVE MODE;" );
            
			default:
                return @$this->exec( "LOCK TABLE {$table};" );
        }
        return FALSE;
    }
    /* -------------------------------------------------------------- */
    function lastId() 
	{
        switch( $this->db_type ) 
        {
            case FORMSQL_USE_PDO:
				$last_id = @$this->conn->lastInsertId();
				break;
			
            case FORMSQL_USE_SQLITE:
				$last_id = @sqlite_last_insert_rowid( $this->conn );
				break;
			
            case FORMSQL_USE_POSTGRESQL:
                $last_id = @pg_last_oid( $this->sqlh );
 				break;
           
            case FORMSQL_USE_POSTGRESQL8x:
				
				$sql = "SELECT LASTVAL();";
				$this->exec( $sql );
				$last_id = $this->result( 0, 0 );
				break;
			
            case FORMSQL_USE_MYSQL:
            case FORMSQL_USE_MYSQL5_EUC:
                return @mysql_insert_id( $this->conn );
            
            case FORMSQL_USE_ODBC_DB:
            case FORMSQL_USE_CACHE_DB:
			default:
				$last_id = FALSE;
        }
        return $last_id;
    }
    /* -------------------------------------------------------------- */
}


?>