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
		LocalizeTimeBehavior::setUserTimeZone('Asia/Seoul');
		$this->assertTrue($this->TimedItem->save($data));
		
		$insertID = $this->TimedItem->getLastInsertID();
		$this->TimedItem->id = $insertID;
		
		// Read the time back with a zero offset and check it's 9 hours behind.
		LocalizeTimeBehavior::setUserTimeZone('UTC');
		$readTime = $this->TimedItem->field('timestamp');
		$this->assertEqual($readTime,'2011-01-01 00:00:00');
		
		// Read it back with a America/New_York offset and check it's 18000 secs or 5 hours behind
		LocalizeTimeBehavior::setUserTimeZone('America/New_York');
		$readTime = $this->TimedItem->field('timestamp');
		$this->assertEqual($readTime,'2010-12-31 19:00:00');
	}
	
	// CakePHP does not currently support triggering afterFind callbacks on related models, so this fails.
	
	function testReadAssociated() {
		LocalizeTimeBehavior::setUserTimeZone('UTC');
		$results = $this->TimedItem->TimedItemParent->find('all');
		$this->assertEqual(Set::extract('/TimedItem/timestamp',$results), array('2011-01-01 00:00:00'));
		
		LocalizeTimeBehavior::setUserTimeZone('America/New_York');
		$results = $this->TimedItem->TimedItemParent->find('all');
		$this->assertEqual(Set::extract('/TimedItem/timestamp',$results), array('2010-12-31 19:00:00'));
	}
	
}

?>