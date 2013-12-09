<?php 
/* ============================================================================ *
   prevNext.php
   purpose: class to create Prev/Next and page links 
            when displaying many contents.
   author : Asao Kamei @ WorkSpot.JP
   date   : 2003/04/16 ~ 
 * ============================================================================ */
if( !defined( "WORDY" ) ) define( "WORDY",  0 ); // very wordy...
require_once( "Db_Sql.php" );
require_once( dirname( __FILE__ ) . '/Db_PrevNext.php' );


class Db_Page extends Db_Sql
{
    /**
     * @var Db_PrevNext
     */
    var $pn;
	/* ------------------------------------------------------------------------ */
    /**
     * @param Db_Rdb $rdb
     */
    function __construct( $rdb )
	{
		parent::__construct( $rdb );
		$this->pn  = new DB_PrevNext();
	}
    
    /**
     * @return Db_Page
     */
    public static function factory()
    {
        return new Db_Page( new Db_Rdb() );
    }
	/* ------------------------------------------------------------------------ */
	function setOptions( $options=array(), $sql_option=NULL )
	{
		$this->pn->setOptions( $options );
		if( !$this->pn->totl ) {
			$this->pn->setTotal( $this->fetchCount( $sql_option ) );
		}
		$this->setPage();
	}
	/* ------------------------------------------------------------------------ */
	function setPage()
	{
		$limit  = $this->pn->getAmount();
		$offset = $this->pn->getOffset();
		$this->setLimit( $limit, $offset );
	}
	/* ------------------------------------------------------------------------ */
	function getPageData( &$data, $option=NULL )
	{
		if( $option == 'DISTINCT' ) {
			$this->makeSQL( 'SELECT DISTINCT' );
		}
		else {
			$this->makeSQL( 'SELECT' );
		}
		$this->execSQL();
		return $this->fetchAll( $data );
	}
	/* ------------------------------------------------------------------------ */
	function getPrevNext()
	{
		return $this->pn->getPrevNext();
	}
}



?>