<?php

interface DbaInterface
{
    public function setTable( $table );
    
    public function addWhere( $where );
    
    public function setOrder( $order );
    
    public function makeSQL( $type );
    
    public function execSQL();
    
    public function fetchAll( &$data );
    
    public function setWhere( $where );
    
    public function clear();
    
    public function setVals( $data );
    
    public function lastId( $table=null );

    public function quote( $data );
}