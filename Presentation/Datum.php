<?php

require_once( dirname( __FILE__ ) . '/Filters.php' );

class Datum
{
    const HTML  = 'Html';  // show as html-safe value.
    const FORM  = 'Form';  // show as html form element.
    const VALUE = 'Value'; // show as is. 

    var $type = 'Html';

    var $data = array();

    var $error = array();

    /**
     * @var FormBase
     */
    var $selector;

    /**
     * @var Filters
     */
    var $filter;

    // +----------------------------------------------------------------------+
    //  construction and initialization
    // +----------------------------------------------------------------------+
    /**
     * @param FormBase      $selector
     * @param Filters|null  $filter
     */
    public function __construct( $selector, $filter=null )
    {
        $this->selector = $selector;
        if( $filter ) {
            $this->filter = $filter;
        } else {
            $this->filter = new Filters();
        }
    }

    /**
     * @param array $data
     * @param array $error
     */
    public function set( $data, $error=array() )
    {
        $this->data  = $data;
        $this->error = $error;
    }

    /**
     * @param mixed $type
     */
    public function setType( $type ) {
        $this->type = ucwords( $type );
    }

    /**
     * @return mixed
     */
    public function getType() {
        return $this->type;
    }

    // +----------------------------------------------------------------------+
    //  get information from Datum object. 
    // +----------------------------------------------------------------------+
    /**
     * @param string $key
     * @return null|string
     */
    public function get( $key )
    {
        if( array_key_exists( $key, $this->data ) ) {
            return $this->data[ $key ];
        }
        return null;
    }

    /**
     * @param string $key
     * @return null
     */
    public function popError( $key )
    {
        if( isset( $this->error[ $key ] ) ) {
            return $this->error[ $key ];
        }
        return null;
    }

    // +----------------------------------------------------------------------+
    //  pop HTML view output. 
    // +----------------------------------------------------------------------+
    /**
     * @param string $key
     * @return mixed
     */
    public function pop( $key )
    {
        $method = 'pop'. $this->type;
        if( method_exists( $this, $method ) ) {
            return $this->$method( $key );
        }
        return $this->get( $key );
    }

    /**
     * @param string $key
     * @return string
     */
    public function popHtml( $key )
    {
        $value = $this->get( $key );
        if( $this->selector->get( $key ) == 'text' ) {
            $value = $this->h( $value );
        }
        elseif( $this->selector->get( $key ) == 'textarea' ) {
            $value = nl2br( $this->h( $value ) );
        }
        elseif( $sel = $this->selector->getSelInstance( $key ) ) {
            $value = array_key_exists( $key, $this->data ) ? $this->data[$key] : "";
            $value = $this->h( $value );
            $value = $sel->popHtml( 'NAME', $value );
        }
        else {
            $value = $this->h( $value );
        }
        return $value . $this->popError( $key );
    }

    /**
     * @param string $key
     * @return null|string
     */
    public function popForm( $key )
    {
        $form = '';
        if( $sel = $this->selector->getSelInstance( $key ) ) {
            $value = array_key_exists( $key, $this->data ) ? $this->data[$key] : "";
            $value = $this->h( $value );
            $form = $sel->popHtml( 'EDIT', $value );
        }
        $form .= $this->popError( $key );
        return $form;
    }

    /**
     * @param string $value
     * @return string
     */
    public function h( $value ) 
    {
        $value = $this->filter->apply( $value, 'htmlSafe' );
        return $value;
    }

    /**
     * @param string       $value
     * @param string|array $filter
     * @return mixed
     */
    public function f( $value, $filter )
    {
        if( !is_array( $filter ) ) {
            if( strpos( $filter, '|' ) !== false ) {
                $filter = explode( '|', $filter );
            } else {
                $filter = array( $filter );
            }
        }
        foreach( $filter as $f ) {
            $value = $this->filter->apply( $value, $f );
        }
        return $value;
    }
}