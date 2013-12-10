<?php
require_once( dirname( __FILE__ ) . '/Html_Forms.php' );

class Html_Select extends Html_Forms
{
    var $name;
    var $style;
    var $item_data;
    var $add_head_option;
    var $err_msg_empty;
    var $default_items = false;
    static $item_list = array();

    var $ime, 
        $size, 
        $max, 
        $width, 
        $height; // for TEXT & TEXTAREA
    var $item_sep = null; // separator for radio/checks items
    var $class; // other options
    var $attach_list; // clickable select text for TEXT/TEXTAREA

    var $db_table, $db_key, $db_name, $db_where, $db_order;
    var $disabled_list = false;

    var $html_append_func = array();
    var $pickup_text, $pick_item_sep, $pick_copy_sep; // for pickup_text
    /* -------------------------------------------------------- */
    function __construct( $name = 'name' )
    {
        $this->name  = $name;

        /*
        $this->option         = array();
        $this->default_items  = '';
        $this->err_msg_empty  = "<font color=red>←　選択して下さい</font>";
        $this->disp_separator = "<br>\n";

        // example of pick_up_text
        $this->html_append_func[ ] = 'pickup_text';
        $this->pick_item_sep       = '&nbsp;／&nbsp;';
        $this->pick_copy_sep       = "・";
        $this->pickup_text         = 'text1,example2,test3';

        // example of sel_copy_value
        $this->html_append_func[ ] = 'sel_copy_value';
        $this->sel_copyval_data    = $this->item_data;
        $this->sel_copyval_val     = '0';
        $this->sel_copyval_map     = array( '1' => 'disp_value' );

        // example of sel_set_option
        // see selPrefByRegion for details...
        */
    }

    /* -------------------------------------------------------- */
    function dbRead()
    {
        if ( $this->db_table && $this->db_key && $this->db_name ) {
            $sql = new Db_Sql( new Db_Rdb() );
            $sql->setTable( $this->db_table );
            $sql->setCols( array( $this->db_key, $this->db_name ) );
            if ( $this->db_where ) $sql->setWhere( $this->db_where );
            if ( $this->db_order ) $sql->setOrder( $this->db_order );
            else                  $sql->setOrder( $this->db_key );

            $sql->makeSQL( 'SELECT' );
            $sql->execSQL();
            $num = $sql->fetchAll( $data );

            for ( $i = 0; $i < $num; $i++ ) {
                $this->item_data[ $i ] =
                    array( $data[ $i ][ $this->db_key ], $data[ $i ][ $this->db_name ] );
                self::$item_list[ ]    = $data[ $i ][ $this->db_key ];
            }
        }
    }

    /* -------------------------------------------------------- */
    function popHtml( $type = "NEW", $values = null, $err_msgs = null )
    {
        if ( is_array( $values ) ) {
            if ( isset( $values[ $this->name ] ) ) {
                $value = $values[ $this->name ];
            }
            else {
                $value = null;
            }
        }
        else {
            $value = $values;
        }
        if ( is_array( $err_msgs ) ) {
            if ( isset( $err_msgs[ $this->name ] ) ) {
                $err_msg = $err_msgs[ $this->name ];
            }
            else {
                $err_msg = null;
            }
        }
        else {
            $err_msg = $err_msgs;
        }
        return $this->show( $type, $value ) . $err_msg;
    }

    /* -------------------------------------------------------- */
    function show( $type = "NEW", $value = "" )
    {
        if ( WORDY > 3 ) echo "htmlSelect::show( $type, $value ), default={$this->default_items}<br>\n";
        if ( in_array( $this->style, array( 'CHECK_HOR', 'CHECK_VER', 'MULT_SELECT' ) ) ) {
            if ( !is_array( $value ) ) $value = explode( ',', $value );
        }
        switch ( $type ) {
            case "PASS":
                $ret_html = $this->makeName( $value );
                $ret_html .= $this->getHidden( $this->name, $value );
                break;
            case "HIDE":
                $ret_html = $this->getHidden( $this->name, $value );
                break;
            case "EDIT":
                $ret_html = $this->makeHtml( $value );
                break;
            case "NEW":
                if ( !have_value( $value ) ) $value = $this->default_items;
                $ret_html = $this->makeHtml( $value );
                break;
            case "DISP":
            case "NAME":
            default:
                $ret_html = $this->makeName( $value );
                break;
        }
        return $ret_html;
    }

    /* -------------------------------------------------------- */
    function isMultiple()
    {
        switch ( $this->style ) {
            case 'CHECK_HOR':
            case 'CHECK_VER':
            case 'MULT_SELECT':
                $is_multiple = true;
                break;

            default:
                $is_multiple = false;
                break;
        }
        return $is_multiple;
    }

    /* -------------------------------------------------------- */
    function makeName( $value )
    {
        $style = strtoupper( trim( $this->style ) );
        switch ( $style ) {
            case 'RADIO_HOR':
            case 'RADIO_VER':
            case 'CHECK_ONE':
            case 'CHECK_TWO':
            case 'CHECK_HOR':
            case 'CHECK_VER':
            case 'MULT_SELECT':
            case 'SELECT':
                $name = $this->makeNameItems( $value );
                break;

            case 'HIDDEN':
                $name = ''; // hide hidden value
                break;
            case 'SERIAL':
                if ( have_value( $value ) ) {
                    $name = $value;
                }
                else {
                    $name = '自動で設定されます'; // for serial
                }
                break;

            case 'TEXTAREA':
                $name = nl2br( $value );
                break;

            case 'PASSWORD':
                $name = str_repeat( '*', strlen( $value ) );
                break;

            default:
            case 'TEXT':
                $name = $value;
                break;
        }
        return $name;
    }

    /* -------------------------------------------------------- */
    function getCode( $value )
    {
        if ( !is_array( $value ) ) {
            $value = array( $value );
        }
        $getCode = false;
        for ( $i = 0; $i < count( $this->item_data ); $i++ ) {
            $code = $this->item_data[ $i ][ 0 ];
            $val  = $this->item_data[ $i ][ 1 ];
            if ( in_array( $val, $value ) ) {
                $getCode = $code;
                break;
            }
        }
        return $getCode;
    }

    /* -------------------------------------------------------- */
    function makeNameItems( $value )
    {
        if ( !is_array( $value ) ) {
            $value = array( $value );
        }
        if ( !isset( $this->item_sep ) ) {
            if ( in_array( $this->style, array( 'CHECK_VER', 'RADIO_HOR', 'MULT_SELECT' ) ) ) {
                $this->item_sep = "<br>\n";
            }
            else {
                $this->item_sep = "、";
            }
        }
        $count_items = 0;
        $name        = '';
        for ( $i = 0; $i < count( $this->item_data ); $i++ ) {
            if ( WORDY > 3 ) echo ">>> i=$i : {$this->item_data{$i}{0}} - {$this->item_data{$i}{1}}<br>\n";
            $key = $this->item_data[ $i ][ 0 ];
            $val = $this->item_data[ $i ][ 1 ];
            if ( $this->item_chop > 0 ) {
                if ( $count_items > 0 && $count_items % $this->item_chop == 0 ) $name .= "<br />\n";
            }
            if ( in_array( $key, $value ) ) {
                if ( $name ) {
                    $name .= "{$this->item_sep}" . $val;
                }
                else {
                    $name .= $val;
                }
                $count_items++;
            }
        }
        if ( !have_value( $name ) && $this->err_msg_empty ) {
            $name = $this->err_msg_empty;
        }

        return $name;
    }

    /* -------------------------------------------------------- */
    function makeHtml( $value )
    {
        $style = strtoupper( trim( $this->style ) );
        switch ( $style ) {
            case 'SERIAL':
            case 'HIDDEN':
                $html = $this->getHidden( $this->name, $value );
                break;

            case 'RADIO_HOR':
                if ( is_null( $this->item_sep ) ) $this->item_sep = '&nbsp;';
                $html = $this->getRadio( $this->name, $this->item_data, $value, $this->item_sep, $this->add_head_option );
                break;

            case 'RADIO_VER':
                if ( is_null( $this->item_sep ) ) $this->item_sep = "<br />\n";
                $html = $this->getRadio( $this->name, $this->item_data, $value, $this->item_sep, $this->add_head_option );
                break;

            case 'CHECK_ONE':
                $html = $this->getCheckOne( $this->name, $this->item_data, $value );
                break;

            case 'CHECK_TWO':
                $html = $this->getCheckTwo( $this->name, $this->item_data, $value );
                break;

            case 'CHECK_HOR':
                if ( is_null( $this->item_sep ) ) $this->item_sep = '&nbsp;';
                $html = $this->getCheck( $this->name, $this->item_data, $value, $this->item_sep );
                break;

            case 'CHECK_VER':
                if ( is_null( $this->item_sep ) ) $this->item_sep = "<br />\n";
                $html = $this->getCheck( $this->name, $this->item_data, $value, $this->item_sep );
                break;

            case 'MULT_SELECT':
                $html = $this->getMultSelect( $this->name, $this->item_data, $this->size, $value, $this->add_head_option );
                break;

            case 'SELECT':
                $html = $this->getSelect( $this->name, $this->item_data, $this->size, $value, $this->add_head_option );
                break;

            case 'TEXTAREA':
                $html = $this->getTextArea( $this->name, $this->width, $this->height, $value );
                //if( have_value( $this->pickup_text ) ) $html .= $this->pickup_text( $this->name );
                break;

            case 'PASSWORD':
                $html = Html_Forms::htmlPasswd( $this->name, $this->size, $this->max, $value );
                break;

            default:
            case 'TEXT':
                $html = $this->getText( $this->name, $this->size, $this->max, $value );
                //if( have_value( $this->pickup_text ) ) $html .= $this->pickup_text( $this->name );
                break;
        }
        if ( !empty( $this->html_append_func ) )
            foreach ( $this->html_append_func as $app_func ) {
                $html .= $this->$app_func();
            }
        return $html;
    }

    /* -------------------------------------------------------- */
    //  for pickup_text div/jquery.
    /* -------------------------------------------------------- */
    function pickup_text()
    {
        if ( !have_value( $this->pickup_text ) ) {
            return '';
        }
        else if ( !is_array( $this->pickup_text ) ) {
            $this->pickup_text = explode( ',', $this->pickup_text );
        }
        $var_name = self::getIdName( $this->name );
        if ( !have_value( $this->pick_item_sep ) ) $this->pick_item_sep = ",&nbsp;";
        if ( !isset( $this->pick_copy_sep ) ) $this->pick_copy_sep = " ";

        $add_sep = false;
        $html    = '';
        if ( $this->pick_copy_sep === false ) {
            $pick_copy_sep = 'false';
        }
        else {
            $pick_copy_sep = "'{$this->pick_copy_sep}'";
        }
        for ( $i = 0; $i < count( $this->pickup_text ); $i++ ) {
            $attach = addslashes( trim( $this->pickup_text[ $i ] ) );
            if ( $add_sep ) {
                $html .= $this->pick_item_sep;
            }
            else {
                $add_sep = true;
            }
            if ( !have_value( $attach ) ) {
                $html .= '<br />';
                $add_sep = false;
            }
            if ( $this->item_chop > 0 ) {
                if ( $i > 0 && $i % $this->item_chop == 0 ) $html .= "<br />\n";
            }
            if ( $attach ) {
                $html .= "<a href=\"javascript:pickup_text_{$var_name}( '{$attach}', {$pick_copy_sep} );\">{$attach}</a>\n";
            }
        }
        $html = "\n" . '<div class="pickuptext">' . $html . "\n" . $this->get_pickup_js() . '</div>';
        return $html;
    }

    /* -------------------------------------------------------- */
    function get_pickup_js()
    {
        $var_name = self::getIdName( $this->name );
        $end_scr  = '</' . 'script>';

        $js = <<<END_OF_JS
<script language="JavaScript">
<!--
function pickup_text_{$var_name}( attach, sep ) {
	var value = $( '#{$var_name}' ).val();
	if( value && sep !== false ) {
		$( '#{$var_name}' ).val( value + sep + attach );
	}
	else {
		$( '#{$var_name}' ).val( attach );
	}
	$( '#{$var_name}' ).focus();
}
-->
{$end_scr}
END_OF_JS;
        return $js;
    }

    /* -------------------------------------------------------- */
    //  for sel_copy_value div/jquery.
    /* -------------------------------------------------------- */
    function sel_copy_value()
    {
        // copy a value to text based on a selected value
        //  - sel_copyval_val  : selected value 
        //  - sel_copyval_map  : specify column name and target id name
        //  - sel_copyval_data : data as shown below
        //      array( col_name1 => id_name1, => col_name2 => id_name2, ... )
        $var_name = self::getIdName( $this->name );
        if ( empty( $this->sel_copyval_data ) ) {
            return null;
        }
        if ( empty( $this->sel_copyval_val ) ) {
            return null;
        }
        if ( empty( $this->sel_copyval_map ) ) {
            return null;
        }

        $jq_list = array();
        foreach ( $this->sel_copyval_data as $data ) {
            $jq_arr  = array();
            $sel_val = $data[ $this->sel_copyval_val ];
            if ( !empty( $this->sel_copyval_map ) )
                foreach ( $this->sel_copyval_map as $col_name => $id_name ) {
                    $col_val   = $data[ $col_name ];
                    $jq_arr[ ] = "{$id_name}:'{$col_val}'";
                }
            if ( !empty( $jq_arr ) ) {
                $jq_list[ ] = "\t\t'{$sel_val}' : { " . implode( ", ", $jq_arr ) . "}";
            }
        }
        if ( empty( $jq_list ) ) {
            return null;
        }
        $jq_copy_values = implode( ",\n", $jq_list );

        $end_scr = '</' . 'script>';
        $jq      = <<<END_OF_JQ
<script language="JavaScript">
<!--
// ------------------------------------------------------------------------
\$( '#{$var_name}' ).change( function() {
	var sel_val = \$( '#{$var_name}' ).val();
	var copy_values = {
{$jq_copy_values}
	};
	if( copy_values[ sel_val ] ) 
	{
		var id_name, id_val;
		for( var id_name in copy_values[ sel_val ] ) {
			id_val = copy_values[ sel_val ][ id_name ]
			\$( '#' + id_name ).val( id_val );
		}
	}
});
-->
{$end_scr}
END_OF_JQ;

        if ( WORDY ) echo "<PRE>{$jq}</PRE>";
        return $jq;
    }

    /* -------------------------------------------------------- */
    //  for sel_set_option div/jquery.
    /* -------------------------------------------------------- */
    function sel_set_option()
    {
        //  sets options in target select based on a selected value.
        //  - sel_setopt_target: target select.
        //  - sel_addopt_data  : value and text to-be-set in the target select.
        //      array( val1 => array( array( val, text ), array( val2, text2 ), ... ), 
        //             val2 => array( array( val, text ), array( val2, text2 ), ... ), ...
        $var_name = self::getIdName( $this->name );
        if ( empty( $this->sel_setopt_data ) ) {
            return null;
        }
        if ( empty( $this->sel_setopt_target ) ) {
            return null;
        }

        $jq_list = array();
        foreach ( $this->sel_setopt_data as $sel_val => $data ) {
            $jq_arr = array();
            if ( !empty( $data ) )
                foreach ( $data as $prefname ) {
                    $pref      = $prefname[ 0 ];
                    $name      = $prefname[ 1 ];
                    $jq_arr[ ] = "{$pref}:'{$name}'";
                }
            if ( !empty( $jq_arr ) ) {
                $jq_list[ ] = "\t\t'{$sel_val}' : { " . implode( ", ", $jq_arr ) . "}";
            }
        }
        if ( empty( $jq_list ) ) {
            return null;
        }
        $jq_copy_values = implode( ",\n", $jq_list );

        $end_scr = '</' . 'script>';
        $jq      = <<<END_OF_JQ
<script language="JavaScript">
<!--
// ------------------------------------------------------------------------
\$( '#{$var_name}' ).change( function() {
	var sel_val = \$( '#{$var_name}' ).val();
	var copy_values = {
{$jq_copy_values}
	};
	if( copy_values[ sel_val ] ) 
	{
		\$( '#{$this->sel_setopt_target}' ).children().remove();
		var id_name, id_val;
		for( var id_name in copy_values[ sel_val ] ) {
			id_val = copy_values[ sel_val ][ id_name ]
			\$( '#{$this->sel_setopt_target}' ).append( $( '<option>' ).attr( { value: id_name } ).text( id_val ) );
		}
		\$( '#{$this->sel_setopt_target}' ).width();
		\$( '#{$this->sel_setopt_target}' ).focus();
	}
});
-->
{$end_scr}
END_OF_JQ;

        if ( WORDY ) echo "<PRE>{$jq}</PRE>";
        return $jq;
    }
    /* -------------------------------------------------------- */
    /* -------------------------------------------------------- */
    /* -------------------------------------------------------- */
}
