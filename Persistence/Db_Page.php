<?php 
/* ============================================================================ *
   prevNext.php
   purpose: class to create Prev/Next and page links 
            when displaying many contents.
   author : Asao Kamei @ WorkSpot.JP
   date   : 2003/04/16 ~ 
 * ============================================================================ */
if( !defined( "WORDY" ) ) define( "WORDY",  0 ); // very wordy...
require_once( "class.db_sql.php" );
require_once( dirname( __FILE__ ) . '/../class/class.prev_next.php' );


/* ============================================================================ */
class dao_page 
{
	var $pn;
	var $dao_obj;
	/* ------------------------------------------------------------------------ */
	function dao_page( $dao_obj, $where=NULL )
	{
		$this->dao = & $dao_obj;
		$this->pn  = new prev_next();
		
		if( !is_null( $where ) ) {
			$this->setSql( $where );
		}
	}
	/* ------------------------------------------------------------------------ */
	function setSql( $where, $table=NULL, $order=NULL, $cols='*', $group=NULL )
	{
		if( !$table ) {
			$table = $this->dao->table;
		}
		$this->dao->sql->clear();
		$this->dao->sql->setCols(  $cols );
		$this->dao->sql->setTable( $table );
		$this->dao->sql->setWhere( $where );
		if( !is_null( $group ) ) {
			$this->dao->sql->setGroup( $group );
		}
		if( $order ) {
			$this->dao->sql->setOrder( $order );
		}
		else {
			$this->dao->sql->setOrder( $this->dao->id_name );
		}
	}
	/* ------------------------------------------------------------------------ */
	function setOptions( $options=array(), $sql_option=NULL )
	{
		$this->pn->setOptions( $options );
		if( !$this->pn->total ) {
			$this->pn->setTotal( $this->dao->sql->fetchCount( $sql_option ) );
		}
		$this->setPage();
	}
	/* ------------------------------------------------------------------------ */
	function setPage()
	{
		$limit  = $this->pn->getAmount();
		$offset = $this->pn->getOffset();
		$this->dao->sql->setLimit( $limit, $offset );
	}
	/* ------------------------------------------------------------------------ */
	function getPageData( &$data, $option=NULL )
	{
		if( $option == 'DISTINCT' ) {
			$this->dao->sql->makeSQL( 'SELECT DISTINCT' );
		}
		else {
			$this->dao->sql->makeSQL( 'SELECT' );
		}
		$this->dao->sql->execSQL();
		return $this->dao->sql->fetchAll( $data );
	}
	/* ------------------------------------------------------------------------ */
	function getPrevNext()
	{
		return $this->pn->getPrevNext();
	}
}

/* ============================================================================ */
class db_page extends form_sql
{
	var $pn;
	/* ------------------------------------------------------------------------ */
	function db_page( $options=array() )
	{
		$this->form_sql();
		$this->pn  = new prev_next();
	}
	/* ------------------------------------------------------------------------ */
	function setOptions( $options=array(), $sql_option=NULL )
	{
		$this->pn->setOptions( $options );
		if( TRUE || !$this->pn->total ) {
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