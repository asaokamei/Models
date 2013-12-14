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
    var $datum = 'Datum';

    /**
     * @var string
     */
    var $dto = 'DtoGeneric';
    
    // +----------------------------------------------------------------------+
    //  construction of Models.
    // +----------------------------------------------------------------------+
    /**
     * constructor. 
     * yap, gave up on easy testing. 
     */
    public function __construct()
    {
        if( is_string( $this->dao   ) ) $this->dao   = new $this->dao;
        if( is_string( $this->form  ) ) $this->form  = new $this->form;
        if( is_string( $this->check ) ) $this->check = new $this->check;
        if( is_string( $this->datum ) ) $this->datum = new $this->datum;
    }

    /**
     * @return DaoBase
     */
    public function getDao() {
        return $this->dao;
    }

    /**
     * @return DbaInterface
     */
    public function getDba() {
        return $this->dao->dba;
    }

    /**
     * @return FormBase
     */
    public function getForm() {
        return $this->form;
    }

    /**
     * @return CheckBase
     */
    public function getCheck() {
        return $this->check;
    }

    /**
     * @return Datum
     */
    public function getDatum()
    {
        $datum = $this->datum->factory();
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
     * @param null|array  $input
     * @param null|string $reg
     * @throws RuntimeException
     * @return string
     */
    public function getId( $input=null, $reg=null ) 
    {
        if( !$reg ) $reg = '[-_0-9a-zA-Z]*';
        if( !$input ) $input = $_REQUEST;
        if( isset( $input[ $this->dao->id_name ] ) ) {
            $id = $input[ $this->dao->id_name ];
            if( preg_match( "/{$reg}/", $id ) ) {
                return $id;
            }
        }
        return false;
    }
    
    // +----------------------------------------------------------------------+
    //  check inputs and data.
    // +----------------------------------------------------------------------+
    /**
     * @param array $data
     * @return DtoAbstract|array
     * @throws ValidationFailException
     */
    public function create( $data )
    {
        if( $data ) {
            $this->check->setSource( $data );
        }
        $this->check->validate();
        $data = $this->check->popData();
        return $this->toDto( $data );
    }

    /**
     * @param $data
     * @return DtoAbstract|array
     */
    public function toDto( $data )
    {
        if( $this->dto ) {
            $data = new $this->dto( $data );
        }
        return $data;
    }

    /**
     * @param DtoAbstract $dto
     * @param array       $data
     * @return \DtoAbstract
     */
    public function modify( $dto, $data )
    {
        $this->getCheck()->setSource( $data );
        $this->getCheck()->validate();
        $dto->set( $this->getCheck()->popData() );
        return $dto;
    }
    // +----------------------------------------------------------------------+
    //  database access. 
    // +----------------------------------------------------------------------+
    /**
     * finds data for id from database.
     *
     * @param string $id
     * @throws RuntimeException
     * @return DtoAbstract|array
     */
    public function findById( $id )
    {
        $data = $this->dao->findById( $id );
        if( !$data ) {
            throw new RuntimeException( "cannot find data for id=" . $this->id );
        }
        return $this->toDto( $data );
    }

    /**
     * @param $id
     * @return DtoAbstract|array
     */
    public function selectById( $id )
    {
        return $this->dao->findById( $id );
    }

    /**
     * update database for $this->id with $input array.
     * @param array $input
     */
    public function update( $input )
    {
        $input = $this->updateBefore( $input );
        $id = $this->getId( $input );
        $this->dao->update( $id, $input );
    }

    /**
     * insert $input data into database.
     *
     * @param array $input
     * @return string
     */
    public function insert( $input )
    {
        $input = $this->insertBefore( $input );
        return $this->dao->insertId( $input );
    }

    /**
     * a hook method before inserting database.
     * @param DtoAbstract|array $input
     * @return array
     */
    public function insertBefore( $input ) {}

    /**
     * a hook method before updating database.
     * @param DtoAbstract|array $input
     * @return array
     */
    public function updateBefore( $input ) {}

}