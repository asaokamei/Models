<?php

class TaskDao extends DaoBase
{
    var $table = 'DemoTask';
    var $id_name = 'task_id';

    /**
     * @param Db_Sql $sql
     */
    public function __construct( $sql )
    {
        $sql->dbConnect( FORMSQL_DEFAULT_DBCON );
        $this->dba = $sql;
    }

}