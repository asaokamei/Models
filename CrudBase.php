<?php

class CrudBase
{
    /**
     * @var Models
     */
    var $model;

    /**
     * @var Token
     */
    var $token;

    /**
     * @param Models $model
     * @param Token  $token
     */
    public function __construct( $model, $token )
    {
        $this->model = $model;
        $this->token = $token;
    }

    // +----------------------------------------------------------------------+
    //  update data in database. 
    // +----------------------------------------------------------------------+
    /**
     * @param string|null $id
     */
    public function updateForm( $id=null )
    {
        // get data from db. 
        $this->model->findById( $id );

        // push XSRF token.
        $this->token->pushToken();
    }

    /**
     * @param null|array   $source
     */
    public function updateDb( $source=null )
    {
        // get data from db. 
        $this->model->findById();
        
        // check the input
        $this->model->check( $source );
        
        // check XSRF token.
        $this->token->verifyToken();

        // update db.
        $this->model->update();
    }

    // +----------------------------------------------------------------------+
    //  insert data into database. 
    // +----------------------------------------------------------------------+
    /**
     */
    public function insertCheck( $source=null )
    {
        // check the input
        $this->model->check( $source );

        // push XSRF token.
        $this->token->pushToken();
    }

    /**
     * @param null|array   $source
     */
    public function insertDb( $source=null )
    {
        // check the input
        $this->model->check( $source );

        // check XSRF token.
        $this->token->verifyToken();

        // update db.
        $this->model->insert();
    }
}