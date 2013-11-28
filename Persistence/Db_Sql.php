<?php
/* fmSQL.php
 * by Asao Kamei @ WorkSpot.JP, 
 * PURPOSE: to create SQL statements, and to manage PostgreSQL db.
 * ALL RIGHTS RESERVED 2002-2010
 *
*/
if( !defined( "WORDY" ) ) define( "WORDY",  0 ); // very wordy...
require_once( dirname( __FILE__ ) . "/Db_Rdb.php" );

// Basic constants
if( !defined( 'FORMSQL_ALL_LOG' ) ) 
	 define(  "FORMSQL_ALL_LOG",                  FALSE );

define( "FORMSQL_USE_POSTGRESQL8x",   "PgSQL.8.x" ); // PostgreSQL (after  8.x)
define( "FORMSQL_USE_MYSQL",              "MySQL" ); // MySQL (before 5.0)
define( "FORMSQL_USE_MYSQL5_EUC",   "MySQL.5-EUC" ); // MySQL (after 5.0 and using EUC)
define( "FORMSQL_USE_SQLITE",            "SQLite" ); // SQLite
define( "FORMSQL_USE_PDO",            "PDO-class" ); // PDO class

if( !function_exists( 'have_value' ) ) {
    /**
     * @param mixed $value
     * @return bool
     */
    function have_value( $value ) 
    {
        if( is_array( $value ) ) {
            return( count( $value ) );
        }
        if( is_object( $value ) ) {
            return TRUE;
        }
        if( "$value" == "" ) {
            return FALSE;
        }
        return true;
    }
}

if( !function_exists( 'wordy_table' ) ) {
    function wordy_table( $value ) {
        var_dump( $value );
    }
}
// +-----------------------------------------------------------+

class DbSqlException extends Exception {}

class Db_Sql
{
    // variables to build SQL statement...
    var $cols;        // array of columns used in SELECT sql statement. 
    var $vals;        // array of values used in INSERT/UPDATE sql statement.
    var $func;        // array of functions used in INSERT/UPDATE sql statement.
    var $table;       // names of db table
    var $order_by;    // order by statement
    var $where, $count_p_in_where;       // where statement
    var $group;       // group by statement
    var $having;      // having statement
    var $misc;        // misc statement such as LIMIT
	var $limit;       // limit 
	var $offset;      // offset
    var $distinct  = false;
    var $forUpdate = false;
    
    // variables to manage SQL and DB connection...
    var $style;       // style of SQL (SELECT, UPDATE, etc.)
    var $sql;         // the last make_sql_yyy result.
    var $conn;        // dbconnection handle from PG_CONNECT.
    var $db_con;      // db connection parameter
    
    // variables to manage errors...
    var $err_num;     // the first error number 
    var $err_msg;     // the first error message
    var $err_all;     // all the error number as it occured
    
    // variables for multiple rdb systems.
    /**
     * @var Db_Rdb
     */
    var $rdb;
	
    /* -------------------------------------------------------------- */
    /**
     * @param Db_Rdb $rdb
     */
    function __construct( $rdb )
    {
        if( defined( 'FORMSQL_DBCON_FNAME' ) && file_exists( FORMSQL_DBCON_FNAME ) ) { // found db_connection setup file...
            include FORMSQL_DBCON_FNAME;
        }
        $this->rdb = $rdb;
		$this->count_p_in_where=0;
		$this->err_num=0;
		if( WORDY ) echo "form_sql instance...<br>\n";
    }

    /**
     * @return Db_Sql
     */
    public static function factory()
    {
        return new Db_Sql( new Db_Rdb() );
    }
	/* -------------------------------------------------------------- */
    function clear() {
        $this->cols     = array();
        $this->vals     = array();
        $this->func     = array();
        $this->table    = NULL;
        $this->order_by = NULL;
        $this->where    = NULL;
        $this->group    = NULL;
        $this->having   = NULL;
        $this->misc     = NULL;
        $this->limit    = NULL;
        $this->offset   = NULL;
        $this->style    = NULL;
        $this->sql      = NULL;
    }
    /* -------------------------------------------------------------- */
    function setCols( $cols=NULL ) {
		if( !have_value( $cols ) ) $cols = array( '*' );
        $this->cols = $cols;
    }
    /* -------------------------------------------------------------- */
    function setVals( $vals, $func=NULL ) {
        $this->vals = $vals;
		if( have_value( $func ) ) {
	        $this->func = $func;
		} else {
	        $this->func = array();
		}
    }
    /* -------------------------------------------------------------- */
    function setWhere( $where ) {
        if( WORDY > 3 ) echo "<br><i>formSQL::setWhere( $where )...</i><br>\n";
        $this->where = $where;
    }
    /* -------------------------------------------------------------- */
    function addWhere( $where, $type="AND" ) {
		if( WORDY > 3 ) echo "<br><i>form_sql::addWhere( $where, $type )</i><br>\n";
		$this->where = trim( $this->where );
		$where = trim( $where );
        if( $where ) {
			if( substr( $this->where, -1, 1 ) == '(' ) {
				$this->where .= " {$where}";
			}
			elseif( $this->where ) {
				$this->where .= " {$type} {$where}";
			}
			else {
				$this->where  = "{$where}";
			}
		}
		if( WORDY > 3 ) echo "->{$this->where}<br>\n";
		return $this->where;
    }
    /* -------------------------------------------------------------- */
    function addWhereParenthesis( $parenthesis="ADD", $type=NULL ) {
		$parenthesis = strtoupper( $parenthesis );
		switch( $parenthesis ) {
			case '(':
			case 'ADD':
				$this->where .= " {$type} (";
				$this->count_p_in_where++;
				break;
			case ')':
			case 'CLOSE':
				$this->where .= ' )';
				$this->count_p_in_where--;
				break;
			case 'DONE':
			default:
				if( $this->count_p_in_where > 0 ) 
				while( $this->count_p_in_where-- ) {
					$this->where .= ' )';
				}
				break;
		}
		if( WORDY > 3 ) echo "<br>form_sql::addWhereParenthesis( $type )=>{$this->where}<br>\n";
    }
    /* -------------------------------------------------------------- */
    function setOrder( $order ) {
        $this->order_by = $order;
    }
    /* -------------------------------------------------------------- */
    function setGroup( $group ) {
        $this->group = $group;
    }
    /* -------------------------------------------------------------- */
    function setTable( $table ) {
        $this->table = $table;
    }
    /* -------------------------------------------------------------- */
    function setHaving( $having ) {
        $this->having = $having;
    }
    /* -------------------------------------------------------------- */
    function setMisc( $misc ) {
        $this->misc = $misc;
    }
    /* -------------------------------------------------------------- */
    function setLimit( $limit, $offset=0 ) {
        $this->limit  = $limit;
        $this->offset = $offset;
    }
    /* -------------------------------------------------------------- */
    function setDistinct( $distinct = true ) {
        $this->distinct = $distinct;
    }
    /* -------------------------------------------------------------- */
    function setForUpdate( $for=true ) {
        $this->forUpdate = $for;
    }
    /* -------------------------------------------------------------- */
    function setErrNum( $err_num ) {
        $this->err_num = $err_num;
    }
    /* -------------------------------------------------------------- */
    function addValue( $var, $val ) {
        $this->vals["$var"] = $val;
    }
    /* -------------------------------------------------------------- */
    function addFunc( $var, $func ) {
        $this->func["$var"] = $func;
    }
    /* -------------------------------------------------------------- */
    function delVal( $var_name ) 
    {
        if( isset( $this->vals[ $var_name ] ) ) {
            unset( $this->vals[ $var_name ] );
        }
    }
    /* -------------------------------------------------------------- */
    function delFunc( $var_name ) {
        if( isset( $this->func[ $var_name ] ) ) {
            unset( $this->func[ $var_name ] );
        }
    }
    /* -------------------------------------------------------------- */
    function dbConnect( $db_con=NULL, $new=FALSE )
    {
        if( WORDY > 1 ) echo "<br><i>formSQL::dbConnect( $db_con )...</i><br>\n";
        
        if( !defined( 'FORMSQL_DEFAULT_DBCON' ) ) {
            throw new DbSqlException( 'FORMSQL_DEFAULT_DBCON not set: set connection settings.' );
        }
        $conn = $this->rdb->connect( FORMSQL_DEFAULT_DBCON, $new );
        
		// check if the connection is made
        if( !$conn ) {
            throw new DbSqlException( 'failed to connect to db.' );
        }
        elseif( WORDY > 1 ) {
            echo "Connected to DB successfully<br>\n";
        }
        
        return $conn; 
    }
    /* -------------------------------------------------------------- */
    function makeSQL( $style )
    {
        if( WORDY > 2 ) echo "<br><i>formSQL::makeSQL( $style )...</i><br>\n";
        $style = strtoupper( $style );
        $this->style = $style;
        switch( $style )
        {
            case "INSERT":
                $sql = $this->makeSqlInsert();
                break;
            case "INSERT2":
                $sql = $this->makeSqlInsert(2);
                break;
            case "UPDATE":
                $sql = $this->makeSqlUpdate();
                break;
            case "SELECT":
                $sql = $this->makeSqlSelect();
                break;
            case "DELETE":
                $sql = $this->makeSqlDelete();
                break;
            default:
                $sql = NULL;
        }
		$this->sql = $sql;
        $this->style = $style;
        if( WORDY > 3 ) echo "-- SQL: {$this->sql} <br>\n";
        return $sql;
    }
    /* -------------------------------------------------------------- */
    function makeSqlInsert( $type=1 )
    {
        if( !$this->table ) { 
			throw new DbSqlException( 'makeSqlInsert: missing table' );
        }
        if( !$this->vals  ) { 
			throw new DbSqlException( 'makeSqlInsert: missing vals' );
        }
        $sql  = "INSERT INTO " . $this->table;
        $cols = NULL;
        $vals = NULL;
		reset( $this->vals );
        while( list( $var, $val ) = each( $this->vals ) )
        {
            if( !have_value( $val ) ) continue;
            if( !have_value( $var ) ) continue;
            
            if( !$cols ) $cols .=    $var  ;  else  $cols .=  ", $var";
            if( !$vals ) $vals .=  "'$val'";  else  $vals .= ", '$val'";
        }
        while( list( $var, $val ) = each( $this->func ) )
        {
            if( !have_value( $val ) ) continue;
            if( !have_value( $var ) ) continue;
            
            if( !$cols ) $cols .=  $var ; else $cols .= ", $var";
            if( !$vals ) $vals .= "$val"; else $vals .= ", $val";
        }
		if( $type == 2 ) {
	        $sql .= " VALUES ( $vals );";
		} else {
	        $sql .= " ( $cols ) VALUES ( $vals );";
		}
        
        return $sql;
    }
    /* -------------------------------------------------------------- */
    function makeSqlUpdate( )
    {
        if( !$this->table ) { 
			throw new DbSqlException( 'makeSqlUpdate: missing table' );
        }
        if( !$this->vals  ) { 
			throw new DbSqlException( 'makeSqlUpdate: missing vals' );
        }
        $sql = "UPDATE " . $this->table;
        $update = NULL;
		reset( $this->vals );
        while( list( $var, $val ) = each( $this->vals ) )
        {
            if( !have_value( $val ) )    { $val_sql = "DEFAULT"    ; }
            else                         { $val_sql = "'{$val}'"; }
            if( !have_value( $update ) ) { $update .=   "$var=$val_sql"; }
            else                         { $update .= ", $var=$val_sql"; }
        }
		reset( $this->func );
        while( list( $var, $val ) = each( $this->func ) )
        {
            if( !have_value( $val ) ) continue; 
            if( !have_value( $update ) ) { $update .=   "$var=$val"; }
            else                         { $update .= ", $var=$val"; }
        }
        if( !$update ) {
            throw new DbSqlException( "make_sql_update: missing vals." );
        }
        
        $sql .= " SET $update "; 
        if( $this->where) $sql .= "WHERE " . $this->where;
        $sql .= ";";
        
        return $sql;
    }
    /* -------------------------------------------------------------- */
    function makeSqlSelect( )
    {
        if( !$this->table ) { 
			throw new DbSqlException( 'makeSqlSelect: missing table' );
        }
        if( !$this->cols  ) { $this->cols = array( "*" ); }
        if( $this->distinct ) {
            $sql = "SELECT DISTINCT ";
        }
        else {
            $sql = "SELECT ";
        }
		if( !$this->cols  )               { $cols = "*"; }
		elseif( is_array( $this->cols ) ) { $cols = implode( ", ", $this->cols ); }
		else                              { $cols = $this->cols; }
		
        $sql .= " {$cols} FROM {$this->table}";
        if( have_value( $this->where    ) ) $sql .= " WHERE {$this->where}";
        if( have_value( $this->group    ) ) $sql .= " GROUP BY {$this->group}";
        if( have_value( $this->having   ) ) $sql .= " HAVING {$this->having}";
        if( have_value( $this->order_by ) ) $sql .= " ORDER BY {$this->order_by}";
        if( have_value( $this->misc     ) ) $sql .= " {$this->misc}";
		if( $this->limit  > 0 ) {
			switch( $this->rdb->getDbType() ) {
				case FORMSQL_USE_POSTGRESQL8x:
					$sql .= " LIMIT {$this->limit}";
					if( $this->offset > 0 ) $sql .= " OFFSET {$this->offset}";
					break;
				case FORMSQL_USE_MYSQL:
				case FORMSQL_USE_MYSQL5_EUC:
					if( $this->offset > 0 ) 
						$sql .= " LIMIT {$this->offset}, {$this->limit}";
					else 
						$sql .= " LIMIT 0, {$this->limit}";
					break;
			}
		}
        if( $this->forUpdate ) {
            $sql .= " FOR UPDATE";
        }
        $sql .= ";";
        
        return $sql;
    }
    /* -------------------------------------------------------------- */
    function makeSqlDelete( )
    {
        if( !$this->table ) { 
			throw new DbSqlException( 'makeSqlDelete: missing table' );
        }
        if( !$this->where ) { return FALSE; }
        $sql = "DELETE FROM " . $this->table;
        if( $this->where  ) $sql .= " WHERE " . $this->where;
        $sql .= ";";
        
        return $sql;
    }
    /* -------------------------------------------------------------- */
    function execSQL( $sql=NULL )
    {
        if( WORDY>2 ) echo "<i>fmSQL::execSQL( $sql )...</i><br>\n";
        if( !$this->rdb->conn ) $this->dbConnect();
        if( !$sql ) { // check if sql statement is given
            if( $this->sql ) { $sql = $this->sql; }
        }
        if( !$sql ) {
			throw new DbSqlException( 'no SQL statement' );
		}
        if( WORDY > 5 ) echo " -- SQL: $sql<br>\n";
        
        if( FORMSQL_ALL_LOG ) $time1 = $this->getmicrotime();
        $sqlh = $this->rdb->exec( $sql );
        if( FORMSQL_ALL_LOG ) $time2 = $this->getmicrotime();
        if( !$sqlh )
        {
            $msg = $this->rdb->errorMessage();
			throw new DbSqlException( $msg . ', SQL:' . $sql );
        }
        
        if( FORMSQL_ALL_LOG ) /*** FORMSQL_ALL_LOG for logging SQL ***/
        {
            global $formsql_log_info;
            $item   = array();
            $item[] = date( "Y/m/d H:s:i" );
			if( !empty( $formsql_log_info ) )
            while( list( $key, $val ) = each( $formsql_log_info ) ) { 
                $item[] = "{$key}=>{$val}"; 
            }
            $item[] = sprintf( "%f", $time2 - $time1 );
            $item[] = $sql;
            if( $this->style == "SELECT" ) {
                $num_effected = $this->rdb->numRows( $sqlh );
            }
            elseif( $this->style == "UPDATE" || $this->style == "INSERT" || $this->style == "DELETE" ){
                $num_effected = $this->rdb->cmdtuples( $sqlh );
            }
            else $num_effected = "?{$this->style}?";
            $item[] = "Rows({$num_effected})";
            
            $log    = implode( "|", $item ) . "\n";
            $filename = $this->getLogFile();
            if( $fd = fopen( $filename, "a" ) ) 
            {
                set_file_buffer( $fd, 0 );
                flock( $fd, LOCK_EX );
                fwrite( $fd, $log );
                flock( $fd, LOCK_UN );
                fclose( $fd );
            }
        } /*** end of FORMSQL_ALL_LOG ***/
        
        return $sqlh;
    }
    /* -------------------------------------------------------------- */
    function dbFree()
    {
        if( WORDY ) echo "<i>formSQL::db_free: freeing sql handle...</i><br>\n";
		if( !$this->rdb->free() ) {
			return FALSE;
		}
        return TRUE;
    }
    /* -------------------------------------------------------------- */
    function dbClose()
    {
        if( WORDY ) echo "<i>formSQL::db_close: closing connection/result to DB...</i><br>\n";
		$this->rdb->free();
		if( !$this->rdb->close() ) {
			return FALSE;
		}
        return TRUE;
    }
    /* -------------------------------------------------------------- */
    function getmicrotime()
    {
        // taken from www.php.net, about microtime
        list($usec, $sec) = explode(" ",microtime());
        return ((float)$usec + (float)$sec);
    }
    /* -------------------------------------------------------------- */
    function _reportError( $err_num, $err_msg )
    {
        if( !$err_num ) return FALSE;
        
        if( !isset( $this->err_type ) ) {
            $this->err_num = $err_num;
            $this->err_msg = $err_msg;
        }
        $this->err_all[] = $err_num;
        if( WORDY ) echo "<b><font color=red>formSQL Error: $err_num, $err_msg </font></b><br>\n";
        return TRUE;
    }
    /* -------------------------------------------------------------- */
    function errorMessage( &$err_num, &$err_msg )
    {
        $err_num = $this->err_num;
        $err_msg = $this->err_msg;
        
        if( isset( $this->err_all ) )
            return count( $this->err_all );
        else 
            return FALSE;
    }
    /* -------------------------------------------------------------- */
    function getLogFile( $file_id=NULL )
    {
        // use $file_id to log to different files. 
        // ex) set $file_id as userID to make a log file for each user.
        $dir_log = "./logs";
        if( !is_dir( $dir_log ) ) mkdir( $dir_log, 0777 );
        
        $to_day = date( "Y-m-d" );
        $dir_log .= "/" . $to_day;
        if( !is_dir( $dir_log ) ) mkdir( $dir_log, 0777 );
        
        if( $file_id ) {
            $file_name = "sql_{$file_id}.log";
        }
        else {
            $file_name = "sql.log";
        }
        return "{$dir_log}/{$file_name}";
    }
    /* -------------------------------------------------------------- */
    function nextCounter( $next_name ) 
    {
        if( WORDY ) echo "<br><i>next( $next_name ), DB=" . $this->rdb->getDbType() . " </i><br>\n";
        switch( $this->rdb->getDbType() ) 
        {
            case FORMSQL_USE_POSTGRESQL8x:
                $this->execSQL( "SELECT nextval( '{$next_name}' );" );
                return $this->rdb->result(0,0);
            
            case FORMSQL_USE_MYSQL:
            case FORMSQL_USE_MYSQL5_EUC:
                $this->execSQL( "SELECT next_val FROM {$next_name};" );
                $next_val = $this->fetchRow( 0 );
				$next_val = $next_val['next_val'] + 1;
                $this->execSQL( "UPDATE {$next_name} SET next_val={$next_val};" );
                return $next_val;
        }
        return FALSE;
    }
    /* -------------------------------------------------------------- */
    function fetchNumRow()
    {
        if( WORDY > 1 ) echo "<br><i>formSQL::fetchNumRow()...</i><br>\n";
        
        $num = $this->rdb->numRows(); 
        if( WORDY > 1 ) echo "num={$num} <br>\n";
        
        return $num;
    }
    /* -------------------------------------------------------------- */
    function fetchAffected()
    {
        if( WORDY > 1 ) echo "<br><i>formSQL::fetchAffected()...</i><br>\n";
        
        $num = $this->rdb->cmdTuples(); 
        if( WORDY > 1 ) echo "num=$num <br>\n";
        
        return $num;
    }
    /* -------------------------------------------------------------- */
    function fetchAll( &$td )
    {
        if( WORDY > 1 ) echo "<br><i>formSQL::fetchAll( \$data )...</i><br>\n";
		$num = $this->rdb->numRows();
		if( WORDY > 3 ) echo "-- number of rows: {$num} <br>\n";
		
		for( $i = 0; $i < $num; $i++ )
		{
			$td[ $i ] = $this->rdb->fetchAssoc( $i );
            if( WORDY > 3 ) $this->wordyArray( $td[$i] );
		}
		return $num;
    }
    /* -------------------------------------------------------------- */
    function fetchRow( $row )
    {
        if( WORDY > 1 ) echo "<br><i>formSQL::fetchRow( $row )...</i><br>\n";
        
		if( WORDY > 3 ) {
			$data = $this->rdb->fetchAssoc( $row );
			$this->wordyArray( $data );
			return $data;
		}
		else {
			return $this->rdb->fetchAssoc( $row );
		}
    }
    /* -------------------------------------------------------------- */
    function getCount( $option=NULL )
    {
        if( WORDY > 1 ) echo "<br><i>formSQL::fetchCount()...</i><br>\n";
		
		if( !$this->table ) return 0;
		if( $option == 'DISTINCT' ) {
			$distinct = 'DISTINCT';
		}
		
        $sql = "SELECT COUNT(*) AS ccc FROM {$this->table}";
        if( have_value( $this->where    ) ) $sql .= " WHERE {$this->where}";
        if( have_value( $this->group    ) ) $sql .= " GROUP BY {$this->group}";
        if( have_value( $this->having   ) ) $sql .= " HAVING {$this->having}";
        //if( have_value( $this->order_by ) ) $sql .= " ORDER BY {$this->order_by}";
        if( have_value( $this->misc     ) ) $sql .= " {$this->misc}";
		
		$this->execSQL( $sql );
		$count = $this->fetchRow( 0 );
		$count = $count[ 'ccc' ];
		
		return $count;
    }
    /* -------------------------------------------------------------- */
    function fetchCount( $option=NULL )
    {
        if( WORDY > 1 ) echo "<br><i>formSQL::fetchCount()...</i><br>\n";
		
		if( !$this->table ) return 0;
		if( $option == 'DISTINCT' ) {
			$distinct = ' DISTINCT';
		} else {
            $distinct = '';
        }
		if( !$this->cols  )               { $cols = "*"; }
		elseif( is_array( $this->cols ) ) { $cols = implode( ", ", $this->cols ); }
		else                              { $cols = $this->cols; }
		
        $sql = "SELECT{$distinct} {$cols} FROM {$this->table}";
        if( have_value( $this->where    ) ) $sql .= " WHERE {$this->where}";
        if( have_value( $this->group    ) ) $sql .= " GROUP BY {$this->group}";
        if( have_value( $this->having   ) ) $sql .= " HAVING {$this->having}";
        if( have_value( $this->order_by ) ) $sql .= " ORDER BY {$this->order_by}";
        if( have_value( $this->misc     ) ) $sql .= " {$this->misc}";
		
		$this->execSQL( $sql );
		$count = $this->fetchNumRow();
		
		return $count;
    }
    /* -------------------------------------------------------------- */
    function wordyArray( $data )
    {
		if( is_array( $data ) ) {
			wordy_table( $data );
		}
		else { echo "[data:{$data}] ";}
		echo "<br>\n";
    }
    /* -------------------------------------------------------------- */
	function execSelect( $table, $cols, $where, &$data )
	{
        if( WORDY > 1 ) echo "<br><i>formSQL::execSelect( $table, $cols, $where )...</i><br>\n";
		$this->setTable( $table );
		$this->setCols(  $cols  );
		$this->setWhere( $where );
		$this->makeSQL( 'SELECT' );
		$this->execSQL();
		
		$numrows = $this->fetchAll( $data );
		
		return $numrows;
    }
    /* -------------------------------------------------------------- */
}



?>