<?php

require_once( dirname(__FILE__).'/../../Models.php' );
require_once( dirname(__FILE__).'/../../Class/Pgg_Check.php' );
require_once( dirname(__FILE__).'/../../Class/Db_Sql.php' );
require_once( dirname(__FILE__).'/DaoDemo.php' );
require_once( dirname(__FILE__).'/FormDemo.php' );
require_once( dirname(__FILE__).'/CheckDemo.php' );
require_once( dirname(__FILE__).'/DtoDemo.php' );

class TaskModel extends Models
{
    var $dao = 'DaoDemo';
    var $form = 'FormDemo';
    var $check = 'CheckDemo';
    //var $dto   = 'DtoDemo';
}