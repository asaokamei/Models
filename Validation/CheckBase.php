<?php

class ValidationFailException extends RuntimeException 
{
    var $data = array();
    var $error = array();
    
    function set( $data, $error=array() ) {
        $this->data = $data;
        $this->error = $error;
    }
    
    function getData() {
        return $this->data;
    }
    
    function getError() {
        return $this->error;
    }
}

/**
 * Class CheckBase
 */
abstract class CheckBase
{
    /**
     * @var ValidateInterface|pgg_check
     */
    var $pgg;

    /**
     * @param ValidateInterface $pgg
     */
    public function __construct( $pgg )
    {
        $this->pgg = $pgg;
    }

    /**
     * 本メソード内で入力チェックを行う。
     *
     * @return int
     */
    abstract function check();

    /**
     * @throw ValidationFailException
     */
    function validate()
    {
        $this->check();
        if( !$this->isValid() ) {
            $e = new ValidationFailException();
            $e->set( $this->popData(), $this->popErrors() );
            throw new $e;
        }
    }

    /**
     * @param array $data
     */
    public function setSource( $data ) {
        $this->pgg->post_vars = $data;
    }

    // +----------------------------------------------------------------------+
    //  入力内容の判定
    // +----------------------------------------------------------------------+
    /**
     * @return bool
     */
    public function isValid() {
        return !$this->pgg->errGetNum();
    }

    /**
     * @return array
     */
    public function popData() {
        return $this->pgg->popVariables();
    }

    /**
     * @return mixed|array
     */
    public function popErrors() {
        return $this->pgg->err_getmsgs();
    }

    /**
     * @param null|string $key
     * @return string
     */
    public function savePost( $key=null ) {
        return $this->pgg->savePost( $key );
    }

    /**
     * @param null|string $key
     */
    public function loadPost( $key=null ) {
        $this->pgg->loadPost( $key );
    }
    // +----------------------------------------------------------------------+
}