<?php

require_once( dirname( __FILE__ ) . '/Persistence/DaoBase.php' );
require_once( dirname( __FILE__ ) . '/Presentation/FormBase.php' );
require_once( dirname( __FILE__ ) . '/Presentation/Datum.php' );
require_once( dirname( __FILE__ ) . '/Validation/CheckBase.php' );
require_once( dirname( __FILE__ ) . '/Validation/ValidateInterface.php' );

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

    // +----------------------------------------------------------------------+
    //  construction of Models.
    // +----------------------------------------------------------------------+
    /**
     * constructor. 
     */
    public function __construct() 
    {
        $args = func_get_args();
        call_user_func_array( array( $this, 'construct' ), $args );
    }

    /**
     * set objects based on its class. 
     */
    public function construct()
    {
        $args = func_get_args();
        foreach( $args as $object ) {
            if( is_object( $object ) ) {
                $class = get_class( $object );
                switch( $class ) {
                    case 'DaoBase':    $this->dao   = $object;    break;
                    case 'FormBase':   $this->form  = $object;    break;
                    case 'CheckBase':  $this->check = $object;    break;
                    case 'Datum':      $this->datum = $object;    break;
                }
            }
        }
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
     * @param null $key
     */
    public function loadPost( $key=null ) {
        $this->check->pgg->loadPost($key);
    }

    /**
     * @param array|null $data
     * @throws ValidationFailException
     */
    public function check( $data=null ) 
    {
        try {
            
            if( $data ) {
                $this->check->setSource( $data );
            }
            $this->getDatum();
            $this->check->check();
            $this->datum->set( $this->check->popData() );
            
        } catch ( ValidationFailException $e ) {
            
            $this->datum->set( $this->check->popData(), $this->check->popErrors() );
            throw $e;
        }
    }

    /**
     * @return Datum
     */
    public function getDatum() 
    {
        if( !$this->datum ) {
            $this->datum = new Datum( $this->form );
        }
        return $this->datum;
    }

    // +----------------------------------------------------------------------+
    //  database access. 
    // +----------------------------------------------------------------------+
    /**
     * @return DaoBase
     */
    public function getDao() {
        return $this->dao;
    }
    
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
        $where = $this->getIdName() . "='{$this->id}'";
        $data = $this->dao->dba->setWhere( $where );
        if( !$data ) {
            throw new RuntimeException( "cannot find data for id=" . $this->id );
        }
        $this->datum->set( $data );
        return $this->datum;
    }
    
    /**
     * update database for $this->id with $input array. 
     */
    public function update()
    {
        $this->updateBefore();
        $input = $this->datum->data;
        $input = sql_safe( $input );
        $this->dao->update( $this->id, $input );
    }

    /**
     * insert $input data into database. 
     */
    public function insert()
    {
        $this->insertBefore();
        $input = $this->datum->data;
        $input = sql_safe( $input );
        $this->dao->insertId( $input );
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