<?php

abstract class DtoAbstract extends DataArrayAbstract
{
    var $_data_ = array();

    /**
     * @param array $data
     */
    public function __construct( $data=array() )
    {
        if( $data ) {
            $this->set( $data );
        }
    }

    /**
     * convert some columns to value objects. 
     */
    public function toObjects() {}
    
    /**
     * @param array $data
     */
    public function set( $data ) 
    {
        $this->_data_ = $data + $this->_data_;
        $this->toObjects();
    }

    /**
     * @return array
     */
    public function getAll() {
        return $this->_data_;
    }
}