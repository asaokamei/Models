<?php

class Html_Divs
{
    var $divider;
    var $num_div;
    
    /**
     * @var Html_Select[]
     */
    var $d_forms = array();
    var $default_items   = FALSE;
    var $implode_with_div = TRUE;
    var $name;
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
