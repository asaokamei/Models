<?php 
/* ============================================================================ *
   prevNext.php
   purpose: class to create Prev/Next and page links 
            when displaying many contents.
   author : Asao Kamei @ WorkSpot.JP
   date   : 2003/04/16 ~ 
   文字化け対策→雀の往来（UTF-8）
 * ============================================================================ */
if( !defined( "WORDY" ) ) define( "WORDY",  0 ); // very wordy...

if( !defined( "PrevNext_DEFAULT_ROWS" ) ) { define( "PrevNext_DEFAULT_ROWS",  10 ); }
define( "PrevNext_MAX_PAGES",     10 );

define( "PrevNext_PAGE_VALID",    100 );
define( "PrevNext_PAGE_TOO_LOW",  110 );
define( "PrevNext_PAGE_TOO_HIGH", 111 );
define( "PrevNext_PAGE_CURRENT",  112 );

/* ============================================================================ */
function pn_disp_all( &$pn, $url=NULL )
{
	if( WORDY ) echo "<br>pn_disp_all( $pn, $url )<br>\n";
	if( WORDY > 3 ) wordy_table( $pn, 'pn' );
	if( !$url ) {
		$url = htmlspecialchars( $_SERVER['PHP_SELF'] );
	}
    echo pn_disp_link( $pn, $url, 'top',  '最初' );
    echo ' ';
    echo pn_disp_link( $pn, $url, 'prev', '≪' );
    echo ' ';
    pn_disp_pages( $pn, $url );
    echo pn_disp_link( $pn, $url, 'next', '≫' );
    echo ' ';
    echo pn_disp_link( $pn, $url, 'last', '最後' );
}

/* ============================================================================ */
function pn_disp_pages( &$pn, $url, $word=NULL )
{   
    if( !empty( $pn['page'] ) ) {
        foreach( $pn['page'] as $key=>$arg ) {
            if( empty( $arg ) ) {
                $link = "<strong>{$key}</strong>&nbsp;";
            }
            else {
                $link = "<a href='{$url}?{$arg}'>{$key}</a>&nbsp;";
            }
            echo $link;
        }
    }
}

/* ============================================================================ */
function pn_disp_link( &$pn, $url, $type, $word=NULL, $disp=TRUE )
{
    if( WORDY > 4 ) echo " --disp_pv_link( &$pn, $url, $type, $word )-- \n";
    if( !$word ) $word = ucfirst( strtolower( $type ) );
    
    $args = isset( $pn[ $type ] ) ? $pn[ $type ] : FALSE;
    if( empty( $args ) ) {
		if( $disp ) {
			$link = "{$word}";
		}
		else {
			$link = NULL;
		}
    }
    else {
        $link = "<a href='{$url}?{$args}'>{$word}</a>";
    }
    return $link;
}

/* ============================================================================ */
class prev_next
{
    var $pgid; // page id number
    var $rows; // number of items in page
    var $totl; // total number of items
    var $args; // other arguments not used by prevNext
    
    var $max_page; // maximum number of page used inside this class
    
    /* ------------------------------------------------------------------------ */
    function prev_next( $options=array() )
    {
        if( WORDY ) echo "<b>created instance of prevNext</b>...<br>\n";
        $this->setOptions( $options );
        
        if( !isset( $this->pgid ) || !$this->pgid || $this->pgid < 1 ) {
            // default page id number is 1 (i.e. the first page).
            $this->pgid = 1;
        }
        if( !isset( $this->rows ) || !$this->rows || $this->rows < 1 ) {
            // default number of items in a page is 10 items.
            $this->rows = PrevNext_DEFAULT_ROWS;
        }
        $this->_setMaxPage();
        return TRUE;
    }
    /* ------------------------------------------------------------------------ */
    function setOptions( $options )
    {
        if( WORDY ) echo "<br><b>setOptions...</b><br>\n";
		if( WORDY > 3 ) print_r( $options );
        if( !is_array( $options ) ) return FALSE;
        foreach( $options as $key => $val )
        {
            $key2 = strtoupper( $key );
            switch( $key2 )
            {
            case "PGID":
                $this->pgid = $val;
                break;
            case "ROWS":
                $this->rows = $val;
                break;
            case "TOTL":
                $this->totl = $val;
                break;
            default:
                $this->args[ "$key" ] = $val;
                break;
            }
			if( WORDY > 3 ) echo "{$keys}=>{$val}<br>";
        }
        
        return TRUE;
    }
    /* ------------------------------------------------------------------------ */
    function setTotal( $total )
    {
        if( WORDY ) echo "<b>setTotal( $total )</b>...<br>\n";
        if( $total >= 0 ) $this->totl = $total;
        $this->_setMaxPage();
        
        return TRUE;
    }
    /* ------------------------------------------------------------------------ */
    function getPrevNext()
    {
        // total found
        $pg_info['found'] = $this->totl;
        $pg_info['pgid']  = $this->pgid;
        $pg_info['rows']  = $this->rows;
       
        // ID
        $pg_info['start_num'] = ( $this->pgid - 1 )* $this->rows + 1;
        
        // make SELF button
        $self_pgid = $this->pgid;
        $pg_info[ "self" ] = $this->_makeURL( $self_pgid );
        if( WORDY > 4 ) echo " > SELF: {$pg_info{'self'}}<br>\n";
        
        // make NEXT button
        $next_pgid = $this->pgid + 1;
        $pg_info[ "next" ] = $this->_getURL( $next_pgid );
        if( WORDY > 4 ) echo " > NEXT: {$pg_info{'next'}}<br>\n";
        
        // make PREV button
        $prev_pgid = $this->pgid - 1;
        $pg_info[ "prev" ] = $this->_getURL( $prev_pgid );
        if( WORDY > 4 ) echo " > PREV: {$pg_info{'prev'}}<br>\n";
        
        // make TOP button
        if( $this->pgid != 1 ) {
            $pg_info[ "top" ] = $this->_getURL( 1 );
            if( WORDY > 4 ) echo " > TOP: {$pg_info{'next'}}<br>\n";
        }
        // make LAST button
        if( $this->pgid != $this->max_page ) {
            $pg_info[ "last" ] = $this->_getURL( $this->max_page );
            if( WORDY > 4 ) echo " > LAST: {$pg_info{'next'}}<br>\n";
        }
        // make PAGEs 
        if( $this->max_page < PrevNext_MAX_PAGES * 2 ) {
            $pg_add = $this->getPrevNext1();
        }
        else {
            $pg_add =$this->getPrevNext2();
        }
        $pg_info = $pg_info + $pg_add;
        
        return $pg_info;
    }
    /* ------------------------------------------------------------------------ */
    function getPrevNext2()
    {
        if( WORDY ) echo "<b>getPrevNext2()</b>...<br>\n";
        $repeat     = PrevNext_MAX_PAGES * 2;
        $max_offset = $this->max_page - $repeat + 1;
        
        // getting the first page
        if( $this->pgid > PrevNext_MAX_PAGES ) {
            $page_start = $this->pgid - PrevNext_MAX_PAGES;
        }
        else {
            $page_start = 1;
        }
        if( $page_start > $max_offset ) $page_start = $max_offset;
        
        // make PAGE numbers
        for( $i = 0; $i < $repeat; $i++ ) {
            $page = $page_start + $i;
            $pg_info['page'][ $page ] = $this->_getURL( $page );
            if( WORDY > 4 ) echo " > {$page} {$pg_info{'$page'}}<br>\n";
        }
        
        /** make TOP button
        if( $this->pgid != 1 ) {
            $pg_info[ "top" ] = $this->_getURL( 1 );
            if( WORDY > 4 ) echo " > TOP: {$pg_info{'next'}}<br>\n";
        }
        // make LAST button
        $page_last = $page_start + $repeat;
        if( $page_last != $this->max_page ) {
            $pg_info[ "last" ] = $this->_getURL( $this->max_page );
            if( WORDY > 4 ) echo " > LAST: {$pg_info{'next'}}<br>\n";
        }
        **/
        $pg_info[ "max_page" ]   = $this->max_page;
        $pg_info[ "count_page" ] = PrevNext_MAX_PAGES * 2;
        
        if( WORDY > 4 ) {
            echo "<pre>";
            print_r( $pg_info );
            echo "</pre>";
        }
        return $pg_info;
    }
    /* ------------------------------------------------------------------------ */
    function getPrevNext1()
    {
        if( WORDY ) echo "<b>getPrevNext1()</b>...<br>\n";
        $pn_info = array();
        
        // make PAGE numbers
        for( $i = 1; $i <= $this->max_page; $i++ ) {
            $pg_info['page'][ $i ] = $this->_getURL( $i );
            if( WORDY > 4 ) echo " > {$i} {$pg_info{'$i'}}<br>\n";
        }
        $pg_info[ "max_page" ]   = $this->max_page;
        $pg_info[ "count_page" ] = $this->max_page;
        if( WORDY > 4 ) {
            echo "<pre>";
            print_r( $pg_info );
            echo "</pre>";
        }
        return $pg_info;
    }
    /* ------------------------------------------------------------------------ */
    function getOffset()
    {
        if( WORDY ) echo "<b>get_offset = " . ( $this->pgid - 1 ) * $this->rows . "</b>...<br>\n";
        return ( $this->pgid - 1 )* $this->rows;
    }
    /* ------------------------------------------------------------------------ */
    function getAmount()
    {
        if( WORDY ) echo "<b>get_amount = {$this->rows}</b>...<br>\n";
		if( !$this->rows ) {
			$this->rows = PrevNext_MAX_PAGES;
		}
        return $this->rows;
    }
    /* ------------------------------------------------------------------------ */
    function _getURL( $pgid )
    {
        if( WORDY > 4 ) echo "_getURL( $pgid )...<br>\n";
        $url = "";
        $page_validity = $this->_checkPgID( $pgid );
        switch( $page_validity ) 
        {
        case PrevNext_PAGE_TOO_LOW:
        case PrevNext_PAGE_TOO_HIGH:
        case PrevNext_PAGE_CURRENT:
            break;
        case PrevNext_PAGE_VALID:
        default:
            $url = $this->_makeURL( $pgid );
            break;
        }
        return $url;
    }
    /* ------------------------------------------------------------------------ */
    function _makeURL( $pgid )
    {
        if( WORDY > 4 ) echo "_makeURL( $pgid )...<br>\n";
        $url = "pgid={$pgid}&rows={$this->rows}&totl={$this->totl}";
        if( !empty( $this->args ) && is_array( $this->args ) )
        foreach( $this->args as $key => $val ) {
			if( is_array( $val ) ) {
		        foreach( $val as $k2 => $v2 ) {
		            $url .= "&{$key}[{$k2}]=" . urlencode( $v2 );
				}
			}
			else {
	            $url .= "&{$key}=" . urlencode( $val );
			}
        }
		$url = str_replace( '&', '&amp;', $url );
        return $url;
    }
    /* ------------------------------------------------------------------------ */
    function _checkPgID( $pgid )
    {
        if( WORDY > 4 ) echo "_checkPgID( $pgid )...<br>\n";
        $ret_val = PrevNext_PAGE_VALID;
        if( $pgid < 1 ) {
            $ret_val =  PrevNext_PAGE_TOO_LOW;
        }
        if( $pgid > $this->max_page ) {
            $ret_val =  PrevNext_PAGE_TOO_HIGH;
        }
        if( $pgid == $this->pgid ) {
            $ret_val =  PrevNext_PAGE_CURRENT;
        }
        if( WORDY > 4 ) echo " -> $pgid is $ret_val ...<br>\n";
        
        return $ret_val;
    }
    /* ------------------------------------------------------------------------ */
    function _setMaxPage()
    {
        if( !isset( $this->totl ) || $this->totl == 0 ) {
            $this->max_page = 0;
        }
		else if( !$this->rows ) {
            $this->max_page = 0;
		}
        else {
            $this->max_page = ceil( $this->totl/$this->rows );
        }
        if( WORDY ) echo "_set_max_page ={$this->max_page} ...<br>\n";
        return $this->max_page;
    }
}
/* ============================================================================ */


?>