<?php

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
     * @param FormBase $selector
     */
    public function __construct( $selector )
    {
        $this->selector = $selector;
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
     * @param string|mix $key
     * @return null
     */
    public function popError( $key )
    {
        if( isset( $this->error[ $key ] ) ) {
            return $this->error[ $key ];
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType( $type ) {
        $this->type = ucwords( $type );
    }

    /**
     * @param string $value
     * @return string
     */
    public function h( $value ) {
        return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
    }
}