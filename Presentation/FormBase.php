<?php

abstract class FormBase
{
    /**
     * definition of selectors to instantiate.
     * $this->def = array(
     *   key => [ className, arg1, arg2, ime ],
     * );
     *
     * for htmlText,
     *  - arg1: size of htmlText
     *  - arg2: max characters of htmlText.
     *  - ime : 'ON' or 'OFF' for ime control.
     *
     * @var array
     */
    var $def = array();

    var $type = array(
        'text'     => 'htmlText',
        'textarea' => 'htmlTextArea',
        'pref'     => 'sel_pref',
    );

    var $instances = array();


    /**
     * @param string $key
     * @return null|Html_Select
     */
    public function getSelInstance( $key )
    {
        if( !array_key_exists( $key, $this->instances ) ) {
            $this->instances[ $key ] = $this->getSelector( $key );
        }
        return $this->instances[ $key ];
    }

    /**
     * @param $key
     * @return null|Html_Select
     */
    public function getSelector( $key )
    {
        if( !$class = $this->get( $key ) ) {
            return null;
        }
        if( array_key_exists( strtolower( $class ), $this->type ) ) {
            $class = $this->type[ strtolower( $class ) ];
        }
        $arg1 = $key;
        $arg2 = $this->get( $key, 1 );
        $arg3 = $this->get( $key, 2 );
        $arg4 = $this->get( $key, 3 );
        return new $class( $arg1, $arg2, $arg3, $arg4 );
    }

    /**
     * @param string $key
     * @param int    $offset
     * @return null|string
     */
    public function get( $key, $offset=0 )
    {
        if( array_key_exists( $key, $this->def ) ) {
            $data = $this->def[ $key ];
            if( array_key_exists( $offset, $data ) ) {
                return $data[ $offset ];
            } 
        }
        return null;
    }

}