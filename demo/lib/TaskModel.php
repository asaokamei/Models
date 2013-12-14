<?php

require_once( dirname(__FILE__).'/../../Models.php' );
require_once( dirname(__FILE__).'/../../Class/Pgg_Check.php' );
require_once( dirname(__FILE__).'/../../Class/Db_Sql.php' );
require_once( dirname( __FILE__ ) . '/TaskDao.php' );
require_once( dirname( __FILE__ ) . '/TaskForm.php' );
require_once( dirname( __FILE__ ) . '/TaskCheck.php' );
require_once( dirname( __FILE__ ) . '/TaskDto.php' );

class TaskModel extends Models
{
    var $dao = 'DaoDemo';
    var $form = 'FormDemo';
    var $check = 'CheckDemo';
    //var $dto   = 'DtoDemo';
}