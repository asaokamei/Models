<?php

class ValidationFailException extends RuntimeException {}

abstract class CheckBase
{
    /**
     * @var ValidateInterface
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
     * @param array $data
     */
    public function setSource( $data ) {
        $this->pgg->setSource( $data );
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