<?php

/**
 * Interface ValidateInterface
 *
 * @method pushChar()
 * @method pushMail()
 * @method eucjpHankaku()
 * @method pushDate()
 */
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