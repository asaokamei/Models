<?php

require_once( dirname( __FILE__ ) . '/Persistence/DaoBase.php' );
require_once( dirname( __FILE__ ) . '/Presentation/FormBase.php' );
require_once( dirname( __FILE__ ) . '/Presentation/Datum.php' );
require_once( dirname( __FILE__ ) . '/Validation/CheckBase.php' );
require_once( dirname( __FILE__ ) . '/Validation/ValidateInterface.php' );
require_once( dirname( __FILE__ ) . '/Value/EnumInterface.php' );
require_once( dirname( __FILE__ ) . '/Value/EnumCode.php' );
require_once( dirname( __FILE__ ) . '/Value/EnumList.php' );

abstract class Models
{
    /**
     * @var DaoBase
     */
    var $dao;

    /**
     * @var FormBase
     */
    var $form;

    /**
     * @var CheckBase
     */
    var $check;

    /**
     * @var Datum
     */
    var $datum;
    
    /**
     * @var string
     */
    var $id;

    /**
     * last data and errors from check, create, findById, etc.
     *
     * @var array
     */
    var $lastData = array();
    var $lastError = array();

    // +----------------------------------------------------------------------+
    //  construction of Models.
    // +----------------------------------------------------------------------+
    /**
     * constructor. 
     */
    public function __construct() {}

    /**
     * @param DaoBase $dao
     */
    public function setDao( $dao ) {
        $this->dao = $dao;
    }

    /**
     * @return DaoBase
     */
    public function getDao() {
        return $this->dao;
    }

    /**
     * @param FormBase $form
     */
    public function setForm( $form ) {
        $this->form = $form;
    }

    /**
     * @return \FormBase
     */
    public function getForm() {
        return $this->form;
    }

    /**
     * @param CheckBase $check
     */
    public function setCheck( $check ) {
        $this->check = $check;
    }

    /**
     * @return \CheckBase
     */
    public function getCheck() {
        return $this->check;
    }

    /**
     * @param \Datum $datum
     */
    public function setDatum( $datum ) {
        $this->datum = $datum;
    }

    /**
     * @return Datum
     */
    public function getDatum()
    {
        if( !$this->datum ) {
            $this->datum = new Datum( $this->form );
        }
        $datum = $this->datum->factory();
        $datum->set( $this->lastData, $this->lastError );
        return $datum;
    }

    // +----------------------------------------------------------------------+
    //  obtaining information about data. 
    // +----------------------------------------------------------------------+
    /**
     * @return string
     */
    public function getIdName() {
        return $this->dao->id_name;
    }

    /**
     * @param string $id
     */
    public function setId( $id ) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param null|string $reg
     * @throws RuntimeException
     * @return string
     */
    public function pushId( $reg=null ) 
    {
        if( !$reg ) $reg = '[-_0-9a-zA-Z]*';
        $this->id = $this->check->pgg->pushChar( $this->getIdName(), PGG_VALUE_MUST_EXIST, $reg );
        if( !$this->id ) {
            throw new RuntimeException( "no id found for: ". $this->getIdName() );
        }
        return $this->id;
    }

    /**
     * @param $data
     * @param array $error
     */
    protected function merge( $data, $error=array() )
    {
        $this->lastData = array_merge( $this->lastData, $data );
        if( $error ) {
            $this->lastError = array_merge( $this->lastError, $error );
        }
    }

    /**
     * empties lastData and lastErrors.
     */
    public function resetData() {
        $this->lastData = $this->lastError = array();
    }

    // +----------------------------------------------------------------------+
    //  check inputs and data.
    // +----------------------------------------------------------------------+
    /**
     * @param array $data
     * @return array
     * @throws ValidationFailException
     * @throws Exception
     */
    public function create( $data )
    {
        try {

            $dto = $this->check( $data );
            $this->merge( $dto );
            return $dto;

        } catch ( ValidationFailException $e ) {

            $this->merge( $this->check->popData(), $this->check->popErrors() );
            throw $e;

        } catch ( Exception $e ) {
            throw $e;
        }
    }

    /**
     * checks input data ($data) and returns validated data.
     *
     * @param $data
     * @throws ValidationFailException
     * @return array
     */
    public function check( $data=null )
    {
        if( $data ) {
            $this->check->setSource( $data );
        }
        $this->check->check();
        if( !$this->check->isValid() ) {
            throw new ValidationFailException();
        }
        return $this->check->popData();
    }

    public function modify( $dto, $data )
    {
        $dto = array_merge( $dto, $data );
        return $dto;
    }

    // +----------------------------------------------------------------------+
    //  database access. 
    // +----------------------------------------------------------------------+
    /**
     * finds data for id from database.
     *
     * @param null|string $id
     * @throws RuntimeException
     * @return Datum
     */
    public function findById( $id=null )
    {
        if( $id ) {
            $this->id = $id;
        }
        $data = $this->dao->findById( $this->id );
        if( !$data ) {
            throw new RuntimeException( "cannot find data for id=" . $this->id );
        }
        $this->merge( $data );
        return $data;
    }

    /**
     * update database for $this->id with $input array.
     * @param $id
     * @param $input
     */
    public function update( $id, $input )
    {
        $this->updateBefore();
        $this->dao->update( $id, $input );
    }

    /**
     * insert $input data into database.
     *
     * @param $input
     * @return string
     */
    public function insert( $input )
    {
        $this->insertBefore();
        return $this->dao->insertId( $input );
    }

    /**
     * a hook method before updating database.
     */
    public function insertBefore() {}

    /**
     * a hook method before updating database.
     */
    public function updateBefore() {}

}