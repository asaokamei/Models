<?php

abstract class DataArrayAbstract implements ArrayAccess, IteratorAggregate
{
    var $_data_ = array();

    public function __set( $offset, $value )
    {
        $this->_data_[ $offset ] = $value;
    }
    
    public function __get( $offset )
    {
        return $this->offsetGet( $offset );
    }
    
    /**
     */
    public function getIterator()
    {
        return new ArrayIterator( $this->_data_ );
    }

    /**
     * Whether a offset exists
     */
    public function offsetExists( $offset )
    {
        return array_key_exists( $offset, $this->_data_ );
    }

    /**
     * Offset to retrieve
     */
    public function offsetGet( $offset )
    {
        if ( $this->offsetExists( $offset ) ) {
            return $this->_data_[ $offset ];
        }
        return null;
    }

    /**
     * Offset to set
     */
    public function offsetSet( $offset, $value )
    {
        $this->_data_[ $offset ] = $value;
    }

    /**
     * Offset to unset
     */
    public function offsetUnset( $offset )
    {
        if ( $this->offsetExists( $offset ) ) {
            unset( $this->_data_[ $offset ] );
        }
    }
}