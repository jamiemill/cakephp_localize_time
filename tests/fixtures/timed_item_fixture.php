<?php

class TimedItemFixture extends CakeTestFixture {

	var $name = 'TimedItem';

	var $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'timed_item_parent_id' => array('type' => 'integer', 'null' => false),		
		'timestamp' => 'datetime'
	);

	var $records = array(
		array('timed_item_parent_id'=>1,'timestamp'=>'2011-01-01 00:00:00'),
	);
}

?>