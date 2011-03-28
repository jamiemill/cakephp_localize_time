<?php

class LocalizeTimeBehavior extends ModelBehavior {
	
	private static $_userTimeZone = 'UTC';
	
	var $defaults = array(
		'fields'=>array()
	);
	
	function setup(&$model, $config = array()) {
		$this->settings[$model->name] = am($this->defaults,$config);
	}
	
	static function setUserTimeZone($zoneStr) {
		self::$_userTimeZone = $zoneStr;
	}
	
	static function getUserTimeZone() {
		return self::$_userTimeZone;
	}

	function beforeSave(&$model) {
		$settings = $this->settings[$model->name];
		foreach($settings['fields'] as $fieldName) {
			if(!empty($model->data[$model->alias][$fieldName])) {
				$model->data[$model->alias][$fieldName] = $this->_toServerTime($model->data[$model->alias][$fieldName]);
			}
		}
	}
	
	/**
	* Here we don't use the behavior afterFind method because this is only triggered for primary finds on this model. Instead you must
	* put this in your model so that it happens for related finds too:
	* 
	* function afterFind($results,$primary) {
	* 	$results = parent::afterFind($results,$primary);
	* 	$results = $this->doLocalizeTimeAfterFind($results,$primary);
	* 	return $results;
	* }
	*/
	
	function doLocalizeTimeAfterFind(&$model, $results, $primary) {
		if($primary) {
			$results = $this->_walkResults($model,$results);
		} else {
			// Docs lead me to beleive that data could be in a different format in this clause, but seems the same in my test. 
			// Perhaps different depending on relationship type?
			$results = $this->_walkResults($model,$results);
		}
		return $results;
	}
	
	function _walkResults(&$model,$results) {
		$settings = $this->settings[$model->name];
		foreach ($results as $key => $val) {
			foreach($settings['fields'] as $fieldName) {
				if(!empty($results[$key][$model->alias][$fieldName])) {
					$results[$key][$model->alias][$fieldName] = $this->_toUserTime($results[$key][$model->alias][$fieldName]);
				}
			}
		}
		return $results;
	}
	
	function _toServerTime($date){
		return $this->_convertTimes($date,self::getUserTimeZone(),'UTC');
	}

	function _toUserTime($date){
		return $this->_convertTimes($date,'UTC',self::getUserTimeZone());
	}
	
	function _convertTimes($date,$fromZoneStr,$toZoneStr) {
		$dtime = new DateTime($date, new DateTimeZone($fromZoneStr));
		$dtime->setTimezone(new DateTimeZone($toZoneStr));
		return $dtime->format('Y-m-d H:i:s');
	}
	
}
?>
