<?php

class Filters
{
    public $charset = 'UTF-8';

    /**
     * @param string|array $value
     * @param string       $filter
     * @return string|array
     */
    public function apply( $value, $filter ) 
    {
        // check if method $f exists. 
        if( !method_exists( $this, $filter ) ) {
            return $value;
        }
        // in case $value is an array, apply filter to all of them. 
        if( is_array( $value ) ) {
            foreach( $value as $key => $v ) {
                $value[$key] = $this->apply( $v, $filter );
            }
            return $value;
        }
        // if $value is an object, evaluate it as a string. 
        if( is_object( $value ) ) {
            $value = (string) $value;
        }
        return $this->$filter( $value );
    }
    
    /**
     * @param string $v
     * @return string
     */
    public function htmlSafe( $v )
    {
        return htmlspecialchars( $v, ENT_QUOTES, $this->charset );
    }
}