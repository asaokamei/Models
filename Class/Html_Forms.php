<?php

class html_forms
{
    static $this_wordy = 3;
    static $default = array(); // cleared only manually
    static $var_footer = ''; // add to var_name (i.e. var[1])
    var $options = array(); // automatically cleared 
    var $disable_list = array(); // show item as disabled
    var $item_chop = 0; // chops radio/checks items
    /* -------------------------------------------------------- */
    // static methods.
    /* -------------------------------------------------------- */
    static function getVarName( $var_name )
    {
        return $var_name . self::$var_footer;
    }

    /* -------------------------------------------------------- */
    static function getIdName( $var_name )
    {
        $var_name = self::getVarName( $var_name );
        return str_replace( array( '[', ']' ), '_', $var_name );
    }

    /* -------------------------------------------------------- */
    static function htmlPasswd( $var_name, $size = 30, $max = null, $text = null, $option = null )
    {
        return self::htmlTextType( 'password', $var_name, $size, $max, $text, $option );
    }

    /* -------------------------------------------------------- */
    static function htmlText( $var_name, $size = 30, $max = null, $text = null, $option = null )
    {
        return self::htmlTextType( 'text', $var_name, $size, $max, $text, $option );
    }

    /* -------------------------------------------------------- */
    static function htmlTextType( $type, $var_name, $size = 30, $max = null, $text = null, $option = null )
    {
        if ( WORDY > 3 ) echo "htmlText( $var_name, $size, $max, $text )<br>";
        if ( !$var_name ) return false;
        $id_name  = self::getIdName( $var_name );
        $var_name = self::getVarName( $var_name );
        $html     = "<input type=\"{$type}\" name=\"{$var_name}\" id=\"{$id_name}\"";

        $html .= " size=\"{$size}\"";
        if ( have_value( $max ) ) {
            $html .= " maxlength=\"{$max}\"";
        }
        if ( have_value( $text ) ) {
            $html .= " value=\"{$text}\"";
        }
        $html .= $option;

        $html .= " />";
        return $html;
    }

    /* -------------------------------------------------------- */
    static function htmlTextArea( $var_name, $width = 40, $height = 5, $text = null, $option = null )
    {
        if ( WORDY > 5 ) echo "htmlTextArea( $var_name, $width=40, $height=5, $text )<br>";
        if ( !$var_name ) return false;
        $id_name  = self::getIdName( $var_name );
        $var_name = self::getVarName( $var_name );

        $html = "<textarea name=\"{$var_name}\" id=\"{$id_name}\"";
        $html .= " cols=\"{$width}\"";
        $html .= " rows=\"{$height}\"";
        $html .= $option;
        $html .= '>' . $text . '</textarea>';

        return $html;
    }

    /* -------------------------------------------------------- */
    static function htmlHidden( $var_name, $text = null, $option = null )
    {
        if ( !$var_name ) return false;
        $id_name  = self::getIdName( $var_name );
        $var_name = self::getVarName( $var_name );

        $html = "<input type=\"hidden\" name=\"{$var_name}\" id=\"{$id_name}\" value=\"$text\"";
        $html .= $option;
        $html .= ' />';

        return $html;
    }

    /* -------------------------------------------------------- */
    static function htmlSelect(
        $var_name, // 
        $items, // list of items for options.
        $size = 1, // size (height) of select box.
        $selected = false, // list of values of selected options.
        $head = false, // add head option with no value.
        $mult = false, // anable multiple selection
        $option = null, // optional property (i.e. class, etc.)
        $disabled = false // list of values of disabled options.
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
        if ( !$size ) $size = 1;
        $id_name  = self::getIdName( $var_name );
        $var_name = self::getVarName( $var_name );
        if ( $mult ) {
            $var_name .= "[]";
        }
        $html = "<select name=\"{$var_name}\" size=\"{$size}\" id=\"{$id_name}\"";
        if ( $mult ) {
            $html .= " multiple";
        }
        $html .= $option;
        $html .= ">\n";

        $prev_label = null;
        if ( have_value( $head ) ) $html .= "<option value=\"\">" . $head . "</option>\n";
        for ( $i = 0; isset( $items[ $i ][ 0 ] ); $i++ ) {
            $val  = $items[ $i ][ 0 ];
            $name = $items[ $i ][ 1 ];
            if ( isset( $items[ $i ][ 2 ] ) ) $label = $items[ $i ][ 2 ];
            else $label = null;

            if ( have_value( $label ) && $label != $prev_label ) {
                if ( !is_null( $prev_label ) ) $html .= "</optgroup>\n";
                $html .= "<optgroup label=\"{$label}\">\n";
                $prev_label = $label;
            }
            $html .= "<option value=\"{$val}\"";
            if ( self::checkSelected( $val, $selected ) ) {
                $html .= " selected";
            }
            else if ( self::checkSelected( $val, $disabled ) ) {
                $html .= " disabled=\"disabled\"";
            }
            $html .= ">";
            $html .= $name;
            $html .= "</option>\n";
        }
        if ( !is_null( $prev_label ) ) $html .= "</optgroup>\n";
        $html .= "</select>\n";
        return $html;
    }

    /* -------------------------------------------------------- */
    static function htmlRadio( $var_name, $items, $checked = false, $sep = ' ', $chop = 0, $option = null, $disabled = false )
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
        $id_name  = self::getIdName( $var_name );
        $var_name = self::getVarName( $var_name );
        $html     = '';
        if ( empty( $items ) ) return '';
        foreach ( $items as $item_data ) {
            $val  = $item_data[ 0 ];
            $name = $item_data[ 1 ];
            if ( $chop > 0 ) {
                if ( $i > 0 && $i % $chop == 0 ) $html .= "<br />\n";
            }
            $id = "ID_{$id_name}_{$val}";
            $html .= "<LABEL FOR='{$id}'><input type=\"radio\" name=\"{$var_name}\" value=\"{$val}\" id=\"{$id}\"";
            $html .= $option;
            if ( self::checkSelected( $val, $checked ) ) {
                $html .= " checked=\"checked\"";
            }
            else if ( self::checkSelected( $val, $disabled ) ) {
                $html .= " disabled=\"disabled\"";
            }
            $html .= " />{$name}</LABEL>{$sep}";
        }
        return $html;
    }

    /* -------------------------------------------------------- */
    static function htmlCheck( $var_name, $val, $name, $idx, $checked = null, $option = null, $disabled = false )
    {
        if ( substr( $var_name, -2, 2 ) === '[]' ) {
            $var_name = substr( $var_name, 0, -2 );
            $var_arr  = '[]';
        }
        else {
            $var_arr = '';
        }
        $id_name  = self::getIdName( $var_name );
        $var_name = self::getVarName( $var_name );
        $html     = '';
        $id       = "ID_{$id_name}_{$idx}";
        $html .= "<label for=\"{$id}\"><input type='checkbox' name='{$var_name}{$var_arr}' value='{$val}' id='{$id}'\n";
        $html .= $option;
        if ( self::checkSelected( $val, $checked ) ) {
            $html .= " checked";
        }
        else if ( self::checkSelected( $val, $disabled ) ) {
            $html .= " disabled=\"disabled\"";
        }
        $html .= ">{$name}</label>";
        return $html;
    }

    /* -------------------------------------------------------- */
    static function htmlCheckOne( $var_name, $val, $checked = null, $option = null, $disabled = false )
    {
        // quick access to make checkbox.
        //   <input type=check name=var_name value=val>
        return self::htmlCheck( $var_name, $val, '', '1', $checked, $option, $disabled );
    }

    /* -------------------------------------------------------- */
    static function htmlCheckTwo( $var_name, $val1, $val2, $checked = null, $option = null, $disabled = false )
    {
        // quick access to make on/off using checkbox.
        //   <input type=hidden name=var_name value=val1>
        //   <input type=check  name=var_name value=val2>
        $html = self::htmlHidden( $var_name, $val1 ) . "\n";
        $html .= self::htmlCheck( $var_name, $val2, '', '1', $checked, $option, $disabled );
        return $html;
    }

    /* -------------------------------------------------------- */
    static function setArray( &$data, $key, $val, $sep = false )
    {
        if ( $key == 'class' ) $sep = ' ';
        if ( $key == 'style' ) $sep = ';';
        if ( isset( $data[ $key ] ) && have_value( $data[ $key ] ) ) {
            if ( $sep === false ) {
                $data[ $key ] = $val;
            }
            else {
                $data[ $key ] .= $sep . $val;
            }
        }
        else {
            $data[ $key ] = $val;
        }
    }

    /* -------------------------------------------------------- */
    static function setDefault( $key, $val, $sep = false )
    {
        self::setArray( self::$default, $key, $val, $sep );
    }

    /* -------------------------------------------------------- */
    // instance's methods.
    /* -------------------------------------------------------- */
    function addOption( $key, $val, $sep = false )
    {
        self::setArray( $this->options, $key, $val, $sep );
    }

    /* -------------------------------------------------------- */
    function delOption( $key )
    {
        unset( $this->options[ $key ] );
    }

    /* -------------------------------------------------------- */
    function addClass( $class )
    {
        return self::setArray( $this->options, 'class', $class );
    }

    /* -------------------------------------------------------- */
    function addStyle( $style )
    {
        return self::setArray( $this->options, 'style', $style );
    }

    /* -------------------------------------------------------- */
    function clearDefault()
    {
        self::$default = array();
    }

    /* -------------------------------------------------------- */
    function clearOptions()
    {
        $this->options = array();
    }

    /* -------------------------------------------------------- */
    function setDisabled( $list )
    {
        if ( is_array( $list ) ) {
            $this->disabled_list = $list;
        }
        else {
            $this->disabled_list[ ] = $list;
        }
    }

    /* -------------------------------------------------------- */
    function setOption( $option )
    {
        // $option is... 
        //  - array( 'key' => 'val', 'key2' => 'val2',... )
        //  - "key=>val | key2=>val2, ..."
        if ( empty( $option ) ) return;
        if ( !is_array( $option ) ) {
            $list = explode( '|', $option );
            if ( !empty( $list ) )
                foreach ( $list as $keyval ) {
                    $keyval = trim( $keyval );
                    list( $key, $val ) = explode( $keyval, '=>', 2 );
                    self::setArray( $this->options, $key, $val );
                }
        }
        else {
            foreach ( $option as $key => $val ) {
                self::setArray( $this->options, $key, $val );
            }
        }
    }

    /* -------------------------------------------------------- */
    function setIME( $ime )
    {
        if ( WORDY > 5 ) echo "html_forms::setIME( $ime )<br>";
        $ime = strtoupper( trim( $ime ) );
        if ( $ime == 'ON' ) {
            $this->addStyle( 'ime-mode: active' );
        }
        else if ( $ime == 'OFF' ) {
            $this->addStyle( 'ime-mode: inactive' );
        }
        else if ( $ime == 'I1' ) {
            $this->addOption( 'istyle', '1' );
        }
        else if ( $ime == 'I2' ) {
            $this->addOption( 'istyle', '2' );
        }
        else if ( $ime == 'I3' ) {
            $this->addOption( 'istyle', '3' );
        }
        else if ( $ime == 'I4' ) {
            $this->addOption( 'istyle', '4' );
        }
    }

    /* -------------------------------------------------------- */
    function getOption()
    {
        $default = self::$default;
        $options = $this->options;
        $html    = '';
        if ( !empty( $options ) )
            foreach ( $options as $key => $val ) {
                if ( isset( $default[ $key ] ) && have_value( $default[ $key ] ) ) {
                    self::setArray( $options, $key, $default[ $key ] );
                    unset( $default[ $key ] );
                }
                $val = $options[ $key ];
                if ( $val === false ) {
                    $html .= " {$key}";
                }
                else {
                    $html .= " {$key}=\"{$val}\"";
                }
            }
        if ( !empty( $default ) )
            foreach ( $default as $key => $val ) {
                if ( $val === false ) {
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
        $found = false;
        if ( $selected !== false ) {
            if ( is_array( $selected ) && in_array( $val, $selected ) ) {
                $found = true;
            }
            else if ( $val == $selected ) {
                $found = true;
            }
        }
        if ( WORDY > 5 ) echo "checkSelected( $val, $selected ) => $found / ";
        return $found;
    }

    /* -------------------------------------------------------- */
    // get HTML's form elements
    // obviously, these methods are not so necessary...
    /* -------------------------------------------------------- */
    function getHidden( $var_name, $text = null )
    {
        $option = $this->getOption();
        if ( $this->isMultiple() ) { // check, multiple-select...
            $html = '';
            if ( have_value( $text ) && is_array( $text ) ) {
                foreach ( $text as $ttt ) {
                    $html .= self::htmlHidden( $var_name . '[]', $ttt, $option );
                }
            }
            return $html;
        }
        return self::htmlHidden( $var_name, $text, $option );
    }

    /* -------------------------------------------------------- */
    function getTextArea( $var_name, $width = 40, $height = 5, $text = null )
    {
        $option = $this->getOption();
        return self::htmlTextArea( $var_name, $width, $height, $text, $option );
    }

    /* -------------------------------------------------------- */
    function getText( $var_name, $size = 30, $max = null, $text = null )
    {
        $option = $this->getOption();
        return self::htmlText( $var_name, $size, $max, $text, $option );
    }

    /* -------------------------------------------------------- */
    function getMultSelect( $var_name, $items, $size = 1, $selected = false, $head = false )
    {
        $html = $this->getSelect( $var_name, $items, $size, $selected, $head, true );
        return $html;
    }

    /* -------------------------------------------------------- */
    function getSelect( $var_name, $items, $size = 1, $selected = false, $head = false, $mult = false )
    {
        $option   = $this->getOption();
        $disabled = $this->disable_list;
        return self::htmlSelect( $var_name, $items, $size, $selected, $head, $mult, $option, $disabled );
    }

    /* -------------------------------------------------------- */
    function getRadio( $var_name, $items, $checked = false, $sep = ' ', $head = false )
    {
        $option   = $this->getOption();
        $disabled = $this->disable_list;
        $chop     = $this->item_chop;
        if ( have_value( $head ) ) {
            $items = array_merge( array( array( '', $head ) ), $items );
        }
        return self::htmlRadio( $var_name, $items, $checked, $sep, $chop, $option, $disabled );
    }

    /* -------------------------------------------------------- */
    function getCheckOne( $var_name, $items, $checked = false )
    {
        $var_name .= self::$var_footer;
        $val  = $items[ 0 ][ 0 ];
        $name = $items[ 0 ][ 1 ];

        $id = "{$var_name}_{$val}";
        $html .= "<input type='checkbox' name='{$var_name}' value='{$val}' id='{$id}'";
        $html .= $this->getOption();
        if ( self::checkSelected( $val, $checked ) ) {
            $html .= " checked";
        }
        $html .= ">";
        $html .= "<label for='{$id}'>{$name}</label>";

        return $html;
    }

    /* -------------------------------------------------------- */
    function getCheckTwo( $var_name, $items, $checked = false )
    {
        // DESCRIPTION create two-state checkbox (on/off etc.)
        // <input type=hidden name=name value=1>
        // <input type=check  name=name value=2>

        $var_name .= self::$var_footer;
        $val  = $items[ 0 ][ 0 ];
        $name = $items[ 0 ][ 1 ];
        $html = self::htmlHidden( $var_name, $val );
        // $html  = "<input type='hidden' name='{$var_name}' value='{$val}' id='{$var_name}'>";

        $val  = $items[ 1 ][ 0 ];
        $name = $items[ 1 ][ 1 ];
        // $id    = "{$var_name}_{$val}";
        $html = self::htmlHidden( $var_name, $val, $name, '1', $checked, $this->getOption() );
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
    function getCheck( $var_name, $items, $checked = array(), $sep = ' ' )
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
        $html     = '';
        $option   = $this->getOption();
        $disabled = $this->disable_list;
        $chop     = $this->item_chop;
        for ( $i = 0; isset( $items[ $i ][ 0 ] ); $i++ ) {
            $idx  = $i + 1;
            $val  = $items[ $i ][ 0 ];
            $name = $items[ $i ][ 1 ];
            $html .= self::htmlCheck( $var_name . '[]', $val, $name, $idx, $checked, $option, $disabled );
            if ( $this->item_chop > 0 && $i > 0 && $idx % $this->item_chop == 0 ) {
                $html .= "<br />\n";
            }
            else {
                $html .= $sep . "\n";
            }
        }
        return $html;
    }
}

