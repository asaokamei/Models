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
     * @return array
     * @throws ValidationFailException
     * @throws Exception
     */
    public function create( $data )
    {
        if( $data ) {
            $this->check->setSource( $data );
        }
        $this->check->validate();
        return $this->check->popData();
    }
    // +----------------------------------------------------------------------+
    //  database access. 
    // +----------------------------------------------------------------------+
    /**
     * finds data for id from database.
     *
     * @param string $id
     * @throws RuntimeException
     * @return array
     */
    public function findById( $id )
    {
        $data = $this->dao->findById( $id );
        if( !$data ) {
            throw new RuntimeException( "cannot find data for id=" . $this->id );
        }
        return $data;
    }

    /**
     * @param $id
     * @return array
     */
    public function getById( $id )
    {
        return $this->dao->findById( $id );
    }

    /**
     * update database for $this->id with $input array.
     * @param array $input
     */
    public function update( $input )
    {
        $this->updateBefore( $input );
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
        $this->insertBefore( $input );
        return $this->dao->insertId( $input );
    }

    /**
     * a hook method before updating database.
     * @param array $input
     */
    public function insertBefore( $input ) {}

    /**
     * a hook method before updating database.
     * @param array $input
     */
    public function updateBefore( $input ) {}

}