<?php

class TimedItem extends CakeTestModel {
	
	var $actsAs = array('LocalizeTime.LocalizeTime'=>array(
		'fields'=>array('timestamp')
	));
	
	var $belongsTo = array('TimedItemParent');
	
	/**
	* When found as an associted model, the behavior callback is not triggered, so we have to do it manually like this if desired.
	* The other alternative is to do it in the view with a helper, but you'll also need to process form data before populating edit forms.
	*/
	
	function afterFind($results,$primary) {
		$results = parent::afterFind($results,$primary);
		$results = $this->doLocalizeTimeAfterFind($results,$primary);
		return $results;
	}
}

class TimedItemParent extends CakeTestModel {
	
	var $hasMany = array('TimedItem');
	
}

?>