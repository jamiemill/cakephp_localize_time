<?php 

require_once(dirname(__FILE__).DS.'..'.DS.'test_models.php');

class LocalizeTimeBehaviorTestCase extends CakeTestCase {
	
	var $fixtures = array(
		'plugin.localize_time.timed_item',
		'plugin.localize_time.timed_item_parent',
	);
	
	var $TimedItem = null;

	function startCase() {
		$this->TimedItem =& new TimedItem();
		App::import('lib','LocalizeTime.LocalizeTime');
	}
	
	function testSaveAndReadPrimary() {
		
		// check the known offset of Asia/Seol from UTC for 1st Jan 2011
		$dtzone = new DateTimeZone('Asia/Seoul');
		$dtime = new DateTime('2011-01-01 00:00:00',$dtzone);
		$seoulOffset = $dtime->getOffset();
		$this->assertEqual($seoulOffset,32400);
		
		// check the known offset of America/New_York from UTC for 1st Jan 2011
		$dtzone = new DateTimeZone('America/New_York');
		$dtime = new DateTime('2011-01-01 00:00:00',$dtzone);
		$newYorkOffset = $dtime->getOffset();
		$this->assertEqual($newYorkOffset,-18000);
		
		// Save a time with a user zone of Asia/Seol 32400 secs or 9 hours ahead
		$data = array('TimedItem'=>array('timestamp'=>'2011-01-01 09:00:00'));
		LocalizeTime::setUserTimeZone('Asia/Seoul');
		$this->assertTrue($this->TimedItem->save($data));
		
		$insertID = $this->TimedItem->getLastInsertID();
		$this->TimedItem->id = $insertID;
		
		// Read the time back with a zero offset and check it's 9 hours behind.
		LocalizeTime::setUserTimeZone('UTC');
		$readTime = $this->TimedItem->field('timestamp');
		$this->assertEqual($readTime,'2011-01-01 00:00:00');
		
		// Read it back with a America/New_York offset and check it's 18000 secs or 5 hours behind
		LocalizeTime::setUserTimeZone('America/New_York');
		$readTime = $this->TimedItem->field('timestamp');
		$this->assertEqual($readTime,'2010-12-31 19:00:00');
	}
	
	// CakePHP does not currently support triggering afterFind callbacks on related models, so note that the TimedItem has
	// a basic afterFind added to trigger the bahavior afterFind.
	
	function testReadAssociated() {
		LocalizeTime::setUserTimeZone('UTC');
		$results = $this->TimedItem->TimedItemParent->find('all');
		$this->assertEqual(Set::extract('/TimedItem/timestamp',$results), array('2011-01-01 00:00:00'));
		
		LocalizeTime::setUserTimeZone('America/New_York');
		$results = $this->TimedItem->TimedItemParent->find('all');
		$this->assertEqual(Set::extract('/TimedItem/timestamp',$results), array('2010-12-31 19:00:00'));
	}
	
	function testConditions() {
		$data = array('TimedItem'=>array('timestamp'=>'2011-02-01 09:00:00'));
		LocalizeTime::setUserTimeZone('UTC');
		$this->assertTrue($this->TimedItem->save($data));
		
		$this->assertEqual(1, count($this->TimedItem->find('all', array(
			'conditions'=>array('timestamp'=>'2011-02-01 09:00:00'),
			'recursive'=>-1
		))));
		
		// Check we know what this time should be in NY
		$dtime = new DateTime('2011-02-01 09:00:00', new DateTimeZone('UTC'));
		$dtime->setTimezone(new DateTimeZone('America/New_York'));
		$this->assertEqual($dtime->format('Y-m-d H:i:s'),'2011-02-01 04:00:00');
		
		// Now query as an American
		LocalizeTime::setUserTimeZone('America/New_York');
		$this->assertEqual(1, count($this->TimedItem->find('all', array(
			'conditions'=>array('timestamp'=>'2011-02-01 04:00:00'),
			'recursive'=>-1
		))));
		$this->assertEqual(1, count($this->TimedItem->find('all', array(
			'conditions'=>array('TimedItem.timestamp'=>'2011-02-01 04:00:00'),
			'recursive'=>-1
		))));
		$this->assertEqual(1, count($this->TimedItem->find('all', array(
			'conditions'=>array('or'=>array(
				array('TimedItem.timestamp'=>'2011-02-01 04:00:00'),
				array('TimedItem.id'=>'99999'), // fake other condition that fails
			)),
			'recursive'=>-1
		))));
		$this->assertEqual(1, count($this->TimedItem->find('all', array(
			'conditions'=>array('or'=>array(
				array('TimedItem.timestamp'=>'2011-02-01 04:00:00'),
				array('TimedItem.timestamp'=>'2011-02-01 05:00:00'),
			)),
			'recursive'=>-1
		))));
		$this->assertEqual(1, count($this->TimedItem->find('all', array(
			'conditions'=>array(
				array('TimedItem.timestamp >='=>'2011-02-01 04:00:00'),
				array('TimedItem.timestamp <='=>'2011-02-01 05:00:00'),
			),
			'recursive'=>-1
		))));
	}
	
}

?>
