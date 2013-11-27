<?php

interface ValidateInterface
{
    public function is( $type );
    
    public function setSource( $data );
    
    public function errGetNum();
    
    public function popVariables();
    
    public function popErrors();
    
    public function savePost( $key );
    
    public function loadPost( $key );
}