<?php

class html_forms
{
	static $this_wordy   = 3;
	static $default      = array(); // cleared only manually
	static $var_footer   = '';      // add to var_name (i.e. var[1])
	var    $options      = array(); // automatically cleared 
	var    $disable_list = array(); // show item as disabled
	var    $item_chop    = 0;       // chops radio/checks items
    /* -------------------------------------------------------- */
	// static methods.
    /* -------------------------------------------------------- */
    static function getVarName( $var_name ) {
		return $var_name . self::$var_footer;
    }
    /* -------------------------------------------------------- */
    static function getIdName( $var_name ) {
		$var_name = self::getVarName( $var_name );
		return str_replace( array( '[',']' ), '_', $var_name );
    }
    /* -------------------------------------------------------- */
    static function htmlPasswd( $var_name, $size=30, $max=NULL, $text=NULL, $option=NULL ) {
		return self::htmlTextType( 'password', $var_name, $size, $max, $text, $option );
    }
    /* -------------------------------------------------------- */
    static function htmlText( $var_name, $size=30, $max=NULL, $text=NULL, $option=NULL ) {
		return self::htmlTextType( 'text', $var_name, $size, $max, $text, $option );
    }
    /* -------------------------------------------------------- */
    static function htmlTextType( $type, $var_name, $size=30, $max=NULL, $text=NULL, $option=NULL )
    {
		if( WORDY > 3 ) echo "htmlText( $var_name, $size, $max, $text )<br>";
		if( !$var_name ) return FALSE;
		$id_name  = self::getIdName(  $var_name );
		$var_name = self::getVarName( $var_name );
		$html = "<input type=\"{$type}\" name=\"{$var_name}\" id=\"{$id_name}\"";
		
		$html .= " size=\"{$size}\"";
		if( have_value( $max ) ) {
			$html .= " maxlength=\"{$max}\"";
		}
		if( have_value( $text ) ) {
			$html .= " value=\"{$text}\"";
		}
		$html .= $option;
		
		$html .= " />";
		return $html;
	}
    /* -------------------------------------------------------- */
    static function htmlTextArea( $var_name, $width=40, $height=5, $text=NULL, $option=NULL )
    {
		if( WORDY > 5 ) echo "htmlTextArea( $var_name, $width=40, $height=5, $text )<br>";
		if( !$var_name ) return FALSE;
		$id_name  = self::getIdName(  $var_name );
		$var_name = self::getVarName( $var_name );
		
		$html  = "<textarea name=\"{$var_name}\" id=\"{$id_name}\"";
		$html .= " cols=\"{$width}\"";
		$html .= " rows=\"{$height}\"";
		$html .= $option;
		$html .= '>' . $text . '</textarea>';
		
		return $html;
	}
    /* -------------------------------------------------------- */
    static function htmlHidden( $var_name, $text=NULL, $option=NULL )
    {
		if( !$var_name ) return FALSE;
		$id_name  = self::getIdName(  $var_name );
		$var_name = self::getVarName( $var_name );
		
		$html  = "<input type=\"hidden\" name=\"{$var_name}\" id=\"{$id_name}\" value=\"$text\"";
		$html .= $option;
		$html .= ' />';
		
		return $html;
	}
    /* -------------------------------------------------------- */
    static function htmlSelect( 
		$var_name,         // 
		$items,            // list of items for options.
		$size=1,           // size (height) of select box.
		$selected=FALSE,   // list of values of selected options.
		$head=FALSE,       // add head option with no value.
		$mult=FALSE,       // anable multiple selection
		$option=NULL,      // optional property (i.e. class, etc.)
		$disabled=FALSE    // list of values of disabled options.
	)
    {
        // DESCRIPTION create form/select like...
        // <select name=\"$var_name\">
        //     <option name=$items[$i][0]>$items[$i][1]
        // </select>
        // 
        // $var_name: name of html form name
        // $items   : an array pair of code/name.
        //          : $items[$i][0] = code value
        //          : $items[$i][1] = display name
        //          : $items[$i][2] = group label
        // $selected: used as selected item 
		if( !$size ) $size = 1;
		$id_name  = self::getIdName(  $var_name );
		$var_name = self::getVarName( $var_name );
		if( $mult ) {
			$var_name .= "[]";
		}
        $html  = "<select name=\"{$var_name}\" size=\"{$size}\" id=\"{$id_name}\"";
		if( $mult ) {
			$html .= " multiple";
		}
		$html .= $option;
        $html .= ">\n";
		
        $prev_label = NULL;
        if( have_value( $head ) ) $html .=  "<option value=\"\">" . $head . "</option>\n";
        for( $i = 0; isset( $items[$i][0] ); $i++ )
        {
            $val   = $items[$i][0];
            $name  = $items[$i][1];
			if( isset( $items[$i][2] ) ) $label = $items[$i][2]; else $label = NULL;
			
			if( have_value( $label ) && $label != $prev_label ) {
				if( !is_null( $prev_label ) ) $html .= "</optgroup>\n";
				$html .= "<optgroup label=\"{$label}\">\n";
				$prev_label = $label;
			}
            $html .= "<option value=\"{$val}\"";
            if( self::checkSelected( $val, $selected ) ) {
				$html .= " selected";
            }
			else
			if( self::checkSelected( $val, $disabled ) ) {
                $html .= " disabled=\"disabled\"";
			}
            $html .= ">";
            $html .= $name; 
            $html .= "</option>\n";
        }
		if( !is_null( $prev_label ) ) $html .= "</optgroup>\n";
        $html .= "</select>\n";
        return $html;
    }
    /* -------------------------------------------------------- */
    static function htmlRadio( $var_name, $items, $checked=FALSE, $sep=' ', $chop=0, $option=NULL, $disabled=FALSE )
    {
        // DESCRIPTION create form/select like...
        // <select name=\"$var_name\">
        //     <option name=$items[$i][0]>$items[$i][1]
        // </select>
        // 
        // $var_name: name of html form name
        // $items   : an array pair of code/name.
        //          : $items[$i][0] = code value
        //          : $items[$i][1] = display name
        // $checked : used as checked item 
		$id_name  = self::getIdName(  $var_name );
		$var_name = self::getVarName( $var_name );
		$html = '';
		if( empty( $items ) ) return '';
        foreach( $items as $item_data )
        {
            $val   = $item_data[0];
            $name  = $item_data[1];
			if( $chop > 0 ) {
				if( $i > 0 && $i % $chop == 0 ) $html .= "<br />\n";
			}
			$id    = "ID_{$id_name}_{$val}";
            $html .= "<LABEL FOR='{$id}'><input type=\"radio\" name=\"{$var_name}\" value=\"{$val}\" id=\"{$id}\"";
			$html .= $option;
            if( self::checkSelected( $val, $checked ) ) {
                $html .= " checked=\"checked\"";
            }
			else
			if( self::checkSelected( $val, $disabled ) ) {
                $html .= " disabled=\"disabled\"";
			}
            $html .= " />{$name}</LABEL>{$sep}";
        }
        return $html;
    }
    /* -------------------------------------------------------- */
    static function htmlCheck( $var_name, $val, $name, $idx, $checked=NULL, $option=NULL, $disabled=FALSE )
    {
		if( substr( $var_name, -2, 2 ) === '[]' ) {
			$var_name = substr( $var_name, 0, -2 );
			$var_arr  = '[]';
		}
		else {
			$var_arr  = '';
		}
		$id_name  = self::getIdName(  $var_name );
		$var_name = self::getVarName( $var_name );
		$html      = '';
		$id        = "ID_{$id_name}_{$idx}";
        $html .= "<label for=\"{$id}\"><input type='checkbox' name='{$var_name}{$var_arr}' value='{$val}' id='{$id}'\n";
		$html .= $option;
		if( self::checkSelected( $val, $checked ) ) {
			$html .= " checked";
		}
		else
		if( self::checkSelected( $val, $disabled ) ) {
			$html .= " disabled=\"disabled\"";
		}
		$html .= ">{$name}</label>";
        return $html;
    }
    /* -------------------------------------------------------- */
    static function htmlCheckOne( $var_name, $val, $checked=NULL, $option=NULL, $disabled=FALSE )
    {
        // quick access to make checkbox.
        //   <input type=check name=var_name value=val>
		return self::htmlCheck( $var_name, $val, '', '1', $checked, $option, $disabled );
    }
    /* -------------------------------------------------------- */
    static function htmlCheckTwo( $var_name, $val1, $val2, $checked=NULL, $option=NULL, $disabled=FALSE )
    {
        // quick access to make on/off using checkbox.
        //   <input type=hidden name=var_name value=val1>
        //   <input type=check  name=var_name value=val2>
		$html  = self::htmlHidden( $var_name, $val1 ) . "\n";
		$html .= self::htmlCheck(  $var_name, $val2, '', '1', $checked, $option, $disabled );
		return $html;
    }
    /* -------------------------------------------------------- */
    static function setArray( &$data, $key, $val, $sep=FALSE )
    {
		if( $key == 'class' ) $sep = ' ';
		if( $key == 'style' ) $sep = ';';
		if( isset( $data[ $key ] ) && have_value( $data[ $key ] ) ) {
			if( $sep === FALSE ) {
				$data[ $key ] = $val;
			}
			else {
				$data[ $key ] .= $sep . $val;
			}
		}
		else {
			$data[ $key ]  = $val;
		}
	}
    /* -------------------------------------------------------- */
    static function setDefault( $key, $val, $sep=FALSE ) {
		self::setArray( self::$default, $key, $val, $sep );
	}
    /* -------------------------------------------------------- */
	// instance's methods.
    /* -------------------------------------------------------- */
    function addOption( $key, $val, $sep=FALSE ) {
		self::setArray( $this->options, $key, $val, $sep );
	}
    /* -------------------------------------------------------- */
    function delOption( $key ) {
		unset( $this->options[ $key ] );
	}
    /* -------------------------------------------------------- */
    function addClass( $class ) {
		return self::setArray( $this->options, 'class', $class );
	}
    /* -------------------------------------------------------- */
    function addStyle( $style ) {
		return self::setArray( $this->options, 'style', $style );
	}
    /* -------------------------------------------------------- */
    function clearDefault() {
		self::$default = array();
	}
    /* -------------------------------------------------------- */
    function clearOptions() {
		$this->options = array();
	}
    /* -------------------------------------------------------- */
    function setDisabled( $list )
    {
		if( is_array( $list ) ) {
			$this->disabled_list = $list;
		}
		else {
			$this->disabled_list[] = $list;
		}
	}
    /* -------------------------------------------------------- */
    function setOption( $option ) 
	{
		// $option is... 
		//  - array( 'key' => 'val', 'key2' => 'val2',... )
		//  - "key=>val | key2=>val2, ..."
		if( empty( $option ) ) return;
		if( !is_array( $option ) ) {
			$list = explode( '|', $option );
			if( !empty( $list ) )
			foreach( $list as $keyval ) {
				$keyval = trim( $keyval );
				list( $key, $val ) = explode( $keyval, '=>', 2 );
				self::setArray( $this->options, $key, $val );
			}
		}
		else {
			foreach( $option as $key => $val ) {
				self::setArray( $this->options, $key, $val );
			}
		}
	}
    /* -------------------------------------------------------- */
    function setIME( $ime )
    {
		if( WORDY > 5 ) echo "html_forms::setIME( $ime )<br>";
		$ime = strtoupper( trim( $ime ) );
		if( $ime == 'ON' ) {
			$this->addStyle( 'ime-mode: active' );
		}
		else 
		if( $ime == 'OFF' ) {
			$this->addStyle( 'ime-mode: inactive' );
		}
		else 
		if( $ime == 'I1' ) {
			$this->addOption( 'istyle', '1' );
		}
		else 
		if( $ime == 'I2' ) {
			$this->addOption( 'istyle', '2' );
		}
		else 
		if( $ime == 'I3' ) {
			$this->addOption( 'istyle', '3' );
		}
		else 
		if( $ime == 'I4' ) {
			$this->addOption( 'istyle', '4' );
		}
	}
    /* -------------------------------------------------------- */
    function getOption()
    {
		$default = self::$default;
		$options = $this->options;
		$html    = '';
		if( !empty( $options ) )
		foreach( $options as $key => $val ) 
		{
			if( isset( $default[ $key ] ) && have_value( $default[ $key ] ) ) {
				self::setArray( $options, $key, $default[ $key ] );
				unset( $default[ $key ] );
			}
			$val   = $options[ $key ];
			if( $val === FALSE ) {
				$html .= " {$key}";
			}
			else {
				$html .= " {$key}=\"{$val}\"";
			}
		}
		if( !empty( $default ) )
		foreach( $default as $key => $val ) {
			if( $val === FALSE ) {
				$html .= " {$key}";
			}
			else {
				$html .= " {$key}=\"{$val}\"";
			}
		}
		return $html;
	}
    /* -------------------------------------------------------- */
    function checkSelected( $val, $selected )
    {
		$found = FALSE;
		if( $selected !== FALSE ) {
			if( is_array( $selected ) && in_array( $val, $selected ) ) {
				$found = TRUE;
			}
			else
			if( $val == $selected ) {
				$found = TRUE;
			}
		}
		if( WORDY > 5 ) echo "checkSelected( $val, $selected ) => $found / ";
		return $found;
	}
    /* -------------------------------------------------------- */
	// get HTML's form elements
	// obviously, these methods are not so necessary...
    /* -------------------------------------------------------- */
    function getHidden( $var_name, $text=NULL )
    {
		$option   = $this->getOption();
		if( $this->isMultiple() ) { // check, multiple-select...
			$html = '';
			if( have_value( $text ) && is_array( $text ) ) {
				foreach( $text as $ttt ) {
					$html .= self::htmlHidden( $var_name . '[]', $ttt, $option );
				}
			}
			return $html;
		}
		return self::htmlHidden( $var_name, $text, $option );
	}
    /* -------------------------------------------------------- */
    function getTextArea( $var_name, $width=40, $height=5, $text=NULL )
    {
		$option   = $this->getOption();
		return self::htmlTextArea( $var_name, $width, $height, $text, $option );
	}
    /* -------------------------------------------------------- */
    function getText( $var_name, $size=30, $max=NULL, $text=NULL )
    {
		$option   = $this->getOption();
		return self::htmlText( $var_name, $size, $max, $text, $option );
	}
    /* -------------------------------------------------------- */
    function getMultSelect( $var_name, $items, $size=1, $selected=FALSE, $head=FALSE )
    {
		$html = $this->getSelect( $var_name, $items, $size, $selected, $head, TRUE );
		return $html;
	}
    /* -------------------------------------------------------- */
    function getSelect( $var_name, $items, $size=1, $selected=FALSE, $head=FALSE, $mult=FALSE )
    {
		$option   = $this->getOption();
		$disabled = $this->disable_list;
		return self::htmlSelect( $var_name, $items, $size, $selected, $head, $mult, $option, $disabled );
	}
    /* -------------------------------------------------------- */
    function getRadio( $var_name, $items, $checked=FALSE, $sep=' ', $head=FALSE )
    {
		$option   = $this->getOption();
		$disabled = $this->disable_list;
		$chop     = $this->item_chop;
		if( have_value( $head ) ) {
			$items = array_merge( array( array( '', $head ) ), $items );
		}
		return self::htmlRadio( $var_name, $items, $checked, $sep, $chop, $option, $disabled );
	}
    /* -------------------------------------------------------- */
    function getCheckOne( $var_name, $items, $checked=FALSE )
    {
		$var_name .= self::$var_footer;
		$val   = $items[0][0];
		$name  = $items[0][1];
		
		$id    = "{$var_name}_{$val}";
		$html .= "<input type='checkbox' name='{$var_name}' value='{$val}' id='{$id}'";
		$html .= $this->getOption();
            if( self::checkSelected( $val, $checked ) ) {
			$html .= " checked";
		}
        $html .= ">";
        $html .= "<label for='{$id}'>{$name}</label>";
		
        return $html;
    }
    /* -------------------------------------------------------- */
    function getCheckTwo( $var_name, $items, $checked=FALSE )
    {
        // DESCRIPTION create two-state checkbox (on/off etc.)
        // <input type=hidden name=name value=1>
        // <input type=check  name=name value=2>
		
		$var_name .= self::$var_footer;
		$val   = $items[0][0];
		$name  = $items[0][1];
		$html  = self::htmlHidden( $var_name, $val );
		// $html  = "<input type='hidden' name='{$var_name}' value='{$val}' id='{$var_name}'>";
		
		$val   = $items[1][0];
		$name  = $items[1][1];
		// $id    = "{$var_name}_{$val}";
		$html  = self::htmlHidden( $var_name, $val, $name, '1', $checked, $this->getOption() );
		// $html .= "<input type='checkbox' name='{$var_name}' value='{$val}' id='{$id}'";
		// $html .= $this->getOption();
        //     if( self::checkSelected( $val, $checked ) ) {
		// 	$html .= " checked";
		// }
        // $html .= ">";
        // $html .= "<label for='{$id}'>{$name}</label>";
		
        return $html;
    }
    /* -------------------------------------------------------- */
    function getCheck( $var_name, $items, $checked=array(), $sep=' ' )
    {
        // DESCRIPTION create form/select like...
        // <select name=\"$var_name\">
        //     <option name=$items[$i][0]>$items[$i][1]
        // </select>
        // 
        // $var_name: name of html form name
        // $items   : an array pair of code/name.
        //          : $items[$i][0] = code value
        //          : $items[$i][1] = display name
        // $checked : used as default item if $type==NEW
        //          : used as ??? if $type==EDIT
		$html = '';
		$option   = $this->getOption();
		$disabled = $this->disable_list;
		$chop     = $this->item_chop;
        for( $i = 0; isset( $items[$i][0] ); $i++ )
        {
			$idx   = $i + 1;
            $val   = $items[$i][0];
            $name  = $items[$i][1];
            $html .= self::htmlCheck( $var_name.'[]', $val, $name, $idx, $checked, $option, $disabled );
			if( $this->item_chop > 0 && $i > 0 && $idx % $this->item_chop == 0 ) {
				$html .= "<br />\n";
			}
			else {
				$html .= $sep . "\n";
			}
        }
        return $html;
    }
    /* -------------------------------------------------------- */
    function xxx_getCheck( $var_name, $items, $checked=array(), $sep=' ' )
    {
        // DESCRIPTION create form/select like...
        // <select name=\"$var_name\">
        //     <option name=$items[$i][0]>$items[$i][1]
        // </select>
        // 
        // $var_name: name of html form name
        // $items   : an array pair of code/name.
        //          : $items[$i][0] = code value
        //          : $items[$i][1] = display name
        // $checked : used as default item if $type==NEW
        //          : used as ??? if $type==EDIT
		$var_name .= self::$var_footer;
		$html = '';
        for( $i = 0; isset( $items[$i][0] ); $i++ )
        {
			$idx   = $i + 1;
            $val   = $items[$i][0];
            $name  = $items[$i][1];
			$id    = "ID_{$var_name}_{$idx}";
			if( $this->item_chop > 0 ) {
				if( $i > 0 && $i % $this->item_chop == 0 ) $html .= "<br />\n";
			}
            $html .= "<input type='checkbox' name='{$var_name}[]' value='{$val}' id='{$id}'\n";
			$html .= $this->getOption();
            if( self::checkSelected( $val, $checked ) ) {
                $html .= " checked";
            }
			else
			if( have_value( $this->disable_list ) && in_array( $val, $this->disable_list ) ) {
                $html .= " disabled=\"disabled\"";
			}
            $html .= "><label for='{$id}'>{$name}</label>{$sep}\n";
        }
        return $html;
    }
    /* -------------------------------------------------------- */
}


// +----------------------------------------------------------------------+
class htmlSelect extends html_forms
{
    var $name;
    var $style;
    var $item_data;
    var $add_head_option;
    var $err_msg_empty;
	var $default_items=FALSE;
	static $item_list = array();
	
	var $ime, $size, $max, $width, $height; // for TEXT & TEXTAREA
	var $item_sep = NULL;    // separator for radio/checks items
	var $class; // other options
	var $attach_list; // clickable select text for TEXT/TEXTAREA
	
	var $db_table, $db_key, $db_name, $db_where;
	var $disable_list=FALSE;
	
	var $html_append_func = array(); 
	var $pickup_text, $pick_item_sep, $pick_copy_sep; // for pickup_text
    /* -------------------------------------------------------- */
    function __construct( $name='name' )
    {
		$this->name  = $name;
		$this->style = NULL; // 'SELECT';
		
		$this->option          = array();
		$this->default_items   = '';
		$this->err_msg_empty   = "<font color=red>←　選択して下さい</font>";
		$this->disp_separator  = "<br>\n";
		
		// example of pick_up_text
		$this->html_append_func[] = 'pickup_text';
		$this->pick_item_sep = '&nbsp;／&nbsp;';
		$this->pick_copy_sep = "・";
		$this->pickup_text = 'text1,example2,test3';
		
		// example of sel_copy_value
		$this->html_append_func[] = 'sel_copy_value';
		$this->sel_copyval_data  = $this->item_data;
		$this->sel_copyval_val = '0';
		$this->sel_copyval_map   = array( '1'=>'disp_value' );
		
		// example of sel_set_option
		// see selPrefByRegion for details...
    }
    /* -------------------------------------------------------- */
    function dbRead()
    {
		if( $this->db_table && $this->db_key && $this->db_name )
		{
			$sql = new form_sql();
			$sql->setTable( $this->db_table );
			$sql->setCols( array( $this->db_key, $this->db_name ) );
			if( $this->db_where ) $sql->setWhere( $this->db_where );
			if( $this->db_order ) $sql->setOrder( $this->db_order );
			else                  $sql->setOrder( $this->db_key );
			
			$sql->makeSQL( 'SELECT' );
			$sql->execSQL();
			$num = $sql->fetchAll( $data );
			
			for( $i = 0; $i < $num; $i++ )
			{
				$this->item_data[$i] = 
					array( $data[$i][$this->db_key], $data[$i][$this->db_name] );
					self::$item_list[] = $data[$i][$this->db_key];
			}
		}
    }
    /* -------------------------------------------------------- */
    function popHtml( $type="NEW", $values=NULL, $err_msgs=NULL )
    {
		if( is_array( $values ) ) {
			if( isset( $values[ $this->name ] ) ) {
				$value = $values[ $this->name ];
			}
			else {
				$value = NULL;
			}
		}
		else {
				$value = $values;
		}
		if( is_array( $err_msgs ) ) { 
			if( isset( $err_msgs[ $this->name ] ) ) {
				$err_msg = $err_msgs[ $this->name ];
			}
			else {
				$err_msg = NULL;
			}
		}
		else {
			$err_msg = $err_msgs;
		}
		return $this->show( $type, $value ) . $err_msg;
    }
    /* -------------------------------------------------------- */
    function show( $type="NEW", $value="" )
    {
        if( WORDY > 3 ) echo "htmlSelect::show( $type, $value ), default={$this->default_items}<br>\n";
		if( in_array( $this->style, array( 'CHECK_HOR', 'CHECK_VER', 'MULT_SELECT' ) ) ) {
			if( !is_array( $value ) ) $value = explode( ',', $value );
		}
        switch( $type )
        {
        case "PASS":
			$ret_html  = $this->makeName( $value );
			$ret_html .= $this->getHidden( $this->name, $value );
			break;
        case "HIDE":
            $ret_html = $this->getHidden( $this->name, $value );
            break;
        case "EDIT":
            $ret_html = $this->makeHtml( $value );
            break;
        case "NEW":
			if( !have_value( $value ) ) $value = $this->default_items;
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
		switch( $this->style ) {
			case 'CHECK_HOR':
			case 'CHECK_VER':
			case 'MULT_SELECT':
				$is_multiple = TRUE;
				break;
			
			default:
				$is_multiple = FALSE;
				break;
		}
		return $is_multiple;
    }
    /* -------------------------------------------------------- */
    function makeName( $value )
    {
		$style = strtoupper( trim( $this->style ) );
		switch( $style ) {
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
				if( have_value( $value ) ) {
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
		if( !is_array( $value ) ) {
			$value = array( $value );
		}
		$getCode = FALSE;
        for( $i = 0; $i < count( $this->item_data ); $i++ )
        {
			$code = $this->item_data[$i][0];
			$val  = $this->item_data[$i][1];
			if( in_array( $val, $value ) ) {
				$getCode = $code;
				break;
			}
		}
		return $getCode;
    }
    /* -------------------------------------------------------- */
    function makeNameItems( $value )
    {
		if( !is_array( $value ) ) {
			$value = array( $value );
		}
		if( !isset( $this->item_sep ) ) { 
			if( in_array( $this->style, array( 'CHECK_VER', 'RADIO_HOR', 'MULT_SELECT' ) ) ) {
				$this->item_sep = "<br>\n"; 
			}
			else {
				$this->item_sep = "、"; 
			}
		}
        if( WORDY > 3 ) wt( $value, "htmlSelect::makeName( $value )<br>\n");
		$count_items = 0;
		$name        = '';
        for( $i = 0; $i < count( $this->item_data ); $i++ )
        {
            if( WORDY > 3 ) echo ">>> i=$i : {$this->item_data{$i}{0}} - {$this->item_data{$i}{1}}<br>\n";
			$key = $this->item_data[$i][0];
			$val = $this->item_data[$i][1];
			if( $this->item_chop > 0 ) {
				if( $count_items > 0 && $count_items % $this->item_chop == 0 ) $name .= "<br />\n";
			}
            if( in_array( $key, $value ) ) {
				if( $name ) {
					$name .=  "{$this->item_sep}". $val;
				}
				else {
					$name .= $val;
				}
				$count_items ++;
			}
        }
        if( !have_value( $name ) && $this->err_msg_empty   ) { $name = $this->err_msg_empty; }
        
        return $name;
    }
    /* -------------------------------------------------------- */
    function makeHtml( $value )
    {
		$style = strtoupper( trim( $this->style ) );
		$html  = '';
		switch( $style ) {
			case 'SERIAL':
			case 'HIDDEN':
				$html  = $this->getHidden( $this->name, $value );
				break;
			
			case 'RADIO_HOR':
				if( is_null( $this->item_sep ) ) $this->item_sep = '&nbsp;';
				$html  = $this->getRadio( $this->name, $this->item_data, $value, $this->item_sep, $this->add_head_option );
				break;
			
			case 'RADIO_VER':
				if( is_null( $this->item_sep ) ) $this->item_sep = "<br />\n";
				$html  = $this->getRadio( $this->name, $this->item_data, $value, $this->item_sep, $this->add_head_option );
				break;
			
			case 'CHECK_ONE':
				$html  = $this->getCheckOne( $this->name, $this->item_data, $value );
				break;
			
			case 'CHECK_TWO':
				$html  = $this->getCheckTwo( $this->name, $this->item_data, $value );
				break;
			
			case 'CHECK_HOR':
				if( is_null( $this->item_sep ) ) $this->item_sep = '&nbsp;';
				$html  = $this->getCheck( $this->name, $this->item_data, $value, $this->item_sep );
				break;
			
			case 'CHECK_VER':
				if( is_null( $this->item_sep ) ) $this->item_sep = "<br />\n";
				$html  = $this->getCheck( $this->name, $this->item_data, $value, $this->item_sep );
				break;
			
			case 'MULT_SELECT':
				$html  = $this->getMultSelect( $this->name, $this->item_data, $this->size, $value, $this->add_head_option );
				break;
			
			case 'SELECT':
				$html  = $this->getSelect( $this->name, $this->item_data, $this->size, $value, $this->add_head_option );
				break;
			
			case 'TEXTAREA':
				$html  = $this->getTextArea( $this->name, $this->width, $this->height, $value );
				//if( have_value( $this->pickup_text ) ) $html .= $this->pickup_text( $this->name );
				break;
			
			case 'PASSWORD':
				$html = html_forms::htmlPasswd( $this->name, $this->size, $this->max, $value );
				break;
				
			default:
			case 'TEXT':
				$html  = $this->getText( $this->name, $this->size, $this->max, $value );
				//if( have_value( $this->pickup_text ) ) $html .= $this->pickup_text( $this->name );
				break;
		}
		if( !empty( $this->html_append_func ) ) 
		foreach( $this->html_append_func as $app_func ) {
			$html .= $this->$app_func();
		}
		return $html;
    }
    /* -------------------------------------------------------- */
	//  for pickup_text div/jquery.
    /* -------------------------------------------------------- */
    function pickup_text()
    {
		if( !have_value( $this->pickup_text ) ) {
			return '';
		}
		else
		if( !is_array( $this->pickup_text ) ) {
			$this->pickup_text = explode( ',', $this->pickup_text );
		}
		$var_name = self::getIdName( $this->name );
		if( WORDY > 3 ) wt( $this->pickup_text, "make_attach() for $id_name" );
		if( !have_value( $this->pick_item_sep ) ) $this->pick_item_sep = ",&nbsp;";
		if( !isset(      $this->pick_copy_sep ) ) $this->pick_copy_sep = " ";
		
		$add_sep = FALSE;
		$html    = '';
		if( $this->pick_copy_sep === FALSE ) {
			$pick_copy_sep = 'false';
		}
		else {
			$pick_copy_sep = "'{$this->pick_copy_sep}'";
		}
		for( $i = 0; $i < count( $this->pickup_text ); $i++ ) 
		{
			$attach = addslashes( trim( $this->pickup_text[$i] ) );
			if( $add_sep ) {
				$html .= $this->pick_item_sep;
			}
			else {
				$add_sep = TRUE;
			}
			if( !have_value( $attach ) ) {
				$html .= '<br />';
				$add_sep = FALSE;
			}
			if( $this->item_chop > 0 ) {
				if( $i > 0 && $i % $this->item_chop == 0 ) $html .= "<br />\n";
			}
			if( $attach ) {
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
		$end_scr = '</' . 'script>';
		
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
		if( empty( $this->sel_copyval_data  ) ) { return NULL; }
		if( empty( $this->sel_copyval_val   ) ) { return NULL; }
		if( empty( $this->sel_copyval_map   ) ) { return NULL; }
		
		$jq_list = array();
		foreach( $this->sel_copyval_data as $data ) 
		{
			$jq_arr  = array();
			$sel_val = $data[ $this->sel_copyval_val ];
			if( !empty( $this->sel_copyval_map ) )
			foreach( $this->sel_copyval_map as $col_name => $id_name ) 
			{
				$col_val = $data[ $col_name ];
				$jq_arr[] = "{$id_name}:'{$col_val}'";
			}
			if( !empty( $jq_arr ) ) {
				$jq_list[] = "\t\t'{$sel_val}' : { " . implode( ", ", $jq_arr ) . "}";
			}
		}
		if( empty( $jq_list ) ) { return NULL; }
		$jq_copy_values = implode( ",\n", $jq_list );
		
		$end_scr = '</' . 'script>';
		$jq =<<<END_OF_JQ
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
		
		if( WORDY ) echo "<PRE>{$jq}</PRE>";
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
		if( empty( $this->sel_setopt_data   ) ) { return NULL; }
		if( empty( $this->sel_setopt_target ) ) { return NULL; }
		
		$jq_list = array();
		foreach( $this->sel_setopt_data as $sel_val => $data ) 
		{
			$jq_arr  = array();
			if( !empty( $data ) )
			foreach( $data as $prefname ) 
			{
				$pref = $prefname[0];
				$name = $prefname[1];
				$jq_arr[] = "{$pref}:'{$name}'";
			}
			if( !empty( $jq_arr ) ) {
				$jq_list[] = "\t\t'{$sel_val}' : { " . implode( ", ", $jq_arr ) . "}";
			}
		}
		if( empty( $jq_list ) ) { return NULL; }
		$jq_copy_values = implode( ",\n", $jq_list );
		
		$end_scr = '</' . 'script>';
		$jq =<<<END_OF_JQ
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
		
		if( WORDY ) echo "<PRE>{$jq}</PRE>";
		return $jq;
    }
    /* -------------------------------------------------------- */
    /* -------------------------------------------------------- */
    /* -------------------------------------------------------- */
}

// +----------------------------------------------------------------------+
class htmlDivText 
{
	var $divider;
	var $num_div;
	var $d_forms = array();
	var $default_items   = FALSE;
	var $implode_with_div = TRUE;
    /* -------------------------------------------------------- */
	function __construct( $name, $opt1=NULL, $opt2=NULL, $ime='ON', $option=NULL )
	{
		// example constructor.
	}
    /* -------------------------------------------------------- */
    function popHtml( $type="NAME", $values=NULL, $err_msgs=NULL )
    {
		if( is_array( $values ) ) {
			if( isset( $values[ $this->name ] ) ) {
				$value = $values[ $this->name ];
			}
			else {
				$value = NULL;
			}
		}
		else {
				$value = $values;
		}
		if( is_array( $err_msgs ) ) { 
			if( isset( $err_msgs[ $this->name ] ) ) {
				$err_msg = $err_msgs[ $this->name ];
			}
			else {
				$err_msg = NULL;
			}
		}
		else {
			$err_msg = $err_msgs;
		}
        if( WORDY > 3 ) echo "htmlDivText::popHtml( $type, $value, $err_msg ) w/ {$this->divider} x {$this->num_div}<br>\n";
		return $this->show( $type, $value ) . $err_msg;
    }
    /* -------------------------------------------------------- */
	function show( $style="NAME", $value=NULL )
	{
        if( WORDY > 3 ) echo "htmlDivText::show( $style, $value ) w/ {$this->divider} x {$this->num_div}<br>\n";
		
		if( in_array( $style, array( 'NEW', 'EDIT' ) ) ) 
		{
			$vals = array();
			if( $style == 'NEW' && !have_value( $value ) ) {
				$value = $this->default_items;
			}
			if( $value ) {
				$vals = $this->splitValue( $value );
			}
			$forms = array();
			for( $i = 0; $i < $this->num_div; $i ++ ) {
				if( have_value( $vals[$i] ) ) {
					$html = $this->d_forms[$i]->show( $style, $vals[$i] );
				}
				else {
					$html = $this->d_forms[$i]->show( $style, NULL );
				}
				if( have_value( $html ) ) $forms[] = $html;
			}
			if( $this->implode_with_div ) {
				$ret_html = implode( $this->divider, $forms );
			}
			else {
				$ret_html = implode( '', $forms );
			}
		}
		else 
		if( $style == 'PASS' )
		{
			$ret_html  = $this->makeName( $value );
			$ret_html .= "<input type=\"hidden\" name=\"{$this->name}\" value=\"{$value}\" />";
		}
		else 
		{
			$ret_html = $this->makeName( $value );
		}
        return $ret_html;
	}
    /* -------------------------------------------------------- */
	function splitValue( $value )
	{
		// split value into each forms.
		// overload this method if necessary. 
		return explode( $this->divider, $value );
	}
    /* -------------------------------------------------------- */
	function makeName( $value )
	{
		// display input value (for style=NAME/DISP). 
		// overload this method if necessary. 
		return $value;
	}
    /* -------------------------------------------------------- */
}


?>