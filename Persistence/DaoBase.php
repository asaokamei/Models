<?php

abstract class DaoBase
{
    /**
     * @var DbaInterface
     */
    var $dba;

    /**
     * DBテーブル名。必須
     *
     * @var  string
     */
    var $table;

    /**
     * @var string
     */
    var $originalTable;

    /**
     * プライマリキー名。必須
     *
     * @var  string
     */
    var $id_name;

    /**
     * テーブルのコラム一覧
     *
     * @var array
     */
    var $columns = array();

    /**
     * 登録日時コラム名
     *
     * @var  string
     */
    var $createdAt;

    /**
     * 修正日時コラム名
     *
     * @var  string
     */
    var $updatedAt;

    // +----------------------------------------------------------------------+
    /**
     * @param $sql
     * @throws RuntimeException
     */
    public function __construct( $sql )
    {
        if( !$this->table ) {
            throw new RuntimeException( "テーブル名が定義されていません。" );
        }
        if( !$this->id_name ) {
            throw new RuntimeException( "プライマリキー名が定義されていません。" );
        }
        $this->originalTable = $this->table;

        $sql->table = $this->table;
        $this->dba = $sql;
    }

    /**
     * データのテーブルを設定。
     *
     * @param string $table
     */
    public function setTable( $table )
    {
        $this->table = $table;
        $this->dba->setTable( $table );
    }

    /**
     * テーブル名を元の値に戻す。
     */
    public function resetTable() {
        $this->setTable( $this->originalTable );
    }

    /**
     * @param string $where
     * @param string $order
     * @return array
     */
    public function select( $where=null, $order=null )
    {
        $this->dba->setTable( $this->table );
        if( $where ) {
            $this->dba->addWhere( $where );
        }
        if( !$order ) {
            $order = $this->id_name;
        }
        $this->dba->setOrder( $order );

        $this->dba->makeSQL( 'SELECT' );
        $this->dba->execSQL();
        if( $this->dba->fetchAll( $data ) ) {
            return $data;
        }
        return array();
    }

    /**
     * @param string $id
     * @return array
     */
    public function findById( $id )
    {
        $this->dba->setWhere( "{$this->id_name}='{$id}'" );
        $data = $this->select();
        if( $data ) {
            return $data[0];
        }
        return array();
    }

    /**
     * @param string      $id
     * @param array       $data
     * @param null|string $key
     * @return bool|mixed|resource
     */
    public function update( $id, $data, $key=null )
    {
        if( !$key ) {
            $key = $this->id_name;
        }
        if( method_exists( $this->dba, 'quote' ) ) {
            $data = $this->dba->quote( $data );
        } else {
            $data = array_map( $data, 'addslashes' );
        }
        if( $this->updatedAt ) $data[ $this->updatedAt ] = date( 'Y-m-d H:i:s' );
        $this->dba->clear();
        $this->dba->setTable( $this->originalTable );
        $this->dba->setWhere( "{$key}='{$id}'" );
        $this->dba->setVals( $data );
        $this->dba->makeSQL( 'UPDATE' );
        return $this->dba->execSQL();
    }

    /**
     * @param $id
     * @throws BadFunctionCallException
     */
    public function delete( $id )
    {
        $this->dba->clear();
        $this->dba->setTable( $this->originalTable );
        $this->dba->setWhere( "{$this->id_name}='{$id}'" );
        $this->dba->makeSQL( 'DELETE' );
        return $this->dba->execSQL();
    }

    /**
     * @param array $data
     * @return bool|mixed|resource
     */
    public function insert( $data )
    {
        if( method_exists( $this->dba, 'quote' ) ) {
            $data = $this->dba->quote( $data );
        } else {
            $data = array_map( $data, 'addslashes' );
        }
        if( $this->createdAt ) $data[ $this->createdAt ] = date( 'Y-m-d H:i:s' );
        if( $this->updatedAt ) $data[ $this->updatedAt ] = date( 'Y-m-d H:i:s' );
        $this->dba->clear();
        $this->dba->setTable( $this->originalTable );
        $this->dba->setVals( $data );
        $this->dba->makeSQL( 'INSERT' );
        return $this->dba->execSQL();
    }

    /**
     * @param $data
     * @return bool|int|string|void
     */
    public function insertId( $data )
    {
        $this->insert( $data );
        return $this->dba->lastId();
    }
}