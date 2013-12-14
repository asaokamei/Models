<?php
require_once( dirname( __FILE__ ) . '/../lib/TaskModel.php' );
require_once( dirname( __FILE__ ) . '/../tasks.inc.php' );

$model = new TaskModel();
$model->materialize();