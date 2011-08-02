<?php

App::import('lib','LocalizeTime.LocalizeTime');

class LocalizeTimeBehavior extends ModelBehavior {
	
	
	var $defaults = array(
		'fields'=>array()
	);
	
	function setup(&$model, $config = array()) {
		$this->settings[$model->name] = am($this->defaults,$config);
	}
	
	function beforeSave(&$model) {
		$settings = $this->settings[$model->name];
		foreach($settings['fields'] as $fieldName) {
			if(!empty($model->data[$model->alias][$fieldName])) {
				$model->data[$model->alias][$fieldName] = LocalizeTime::toServerTime($model->data[$model->alias][$fieldName]);
			}
		}
	}
	
	function beforeFind(&$model,$queryData) {
		if(!empty($queryData['conditions'])) {
			$queryData['conditions'] = $this->_localizeConditions($model,$queryData['conditions']);
		}
		return $queryData;
	}
	
	function _localizeConditions(&$model,$conditions) {
		$settings = $this->settings[$model->name];
		foreach($conditions as $key => $value) {
			// if the value is an array, recurse
			// TODO: check the $key is not one of our fields, in which case they wanted an IN() query
			if(is_array($value)) {
				$conditions[$key] = $this->_localizeConditions($model,$value);
			}
			// if the key looks like a field name, and the value is a date, inspect deeper.
			elseif (!is_numeric($key) && strftime($value)) {
				$foundFieldName = $key;
				// if there's a dot, abort if the model alias doesn't match
				if(strpos($foundFieldName,'.') !== false) {
					if(substr($foundFieldName,0,strpos($foundFieldName,'.')) != $model->alias) {
						continue;
					} else {
						$foundFieldName = substr($foundFieldName,strpos($foundFieldName,'.')+1);
					}
				}
				// if there's a space, ignore it and everything after it, as it's probably an sql modifier
				if(strpos($foundFieldName,' ') !== false) {
					$foundFieldName = substr($foundFieldName,0,strpos($foundFieldName,' '));
				}
				// finally check if it's in the list of fields to localize
				if(in_array($foundFieldName,$settings['fields'])) {
					$conditions[$key] = LocalizeTime::toServerTime($value);
				}
			}
		}
		return $conditions;
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
		return $this->_walkResults($model, $results, $primary);
	}
	
	function _walkResults(&$model,$results,$primary = true) {
		$settings = $this->settings[$model->name];
		if ($primary) {
			foreach ($results as $key => $val) {
				foreach($settings['fields'] as $fieldName) {
					if(!empty($results[$key][$model->alias][$fieldName])) {
						$results[$key][$model->alias][$fieldName] = LocalizeTime::toUserTime($results[$key][$model->alias][$fieldName]);
					}
				}
			}
		} else {
			for ($i = 0; $i < count($results); $i++ ) {
				foreach($settings['fields'] as $fieldName) {
					if(!empty($results[$i][$fieldName])) {
						$results[$i][$fieldName] = LocalizeTime::toUserTime($results[$i][$fieldName]);
					}
				}
			}
		}
		return $results;
	}
	
}
?>
