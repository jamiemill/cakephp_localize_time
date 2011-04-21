<?php

/**
 * This class stores a globally-accessible reference to the user's currently set timezone for the duration
 * of the request, as well as global methods to convert times against it.
 */

class LocalizeTime {
	
	/**
	 * Holds the user's timezone string, default is 'UTC'.
	 * @var string
	 */

	private static $_userTimeZone = 'UTC';

	/**
	 * The server time zone is set here to UTC and cannot be changed publicly.
	 * This should match the timezone set in php.ini really.
	 * 
	 * @todo what happens if it doesn't match php.ini?
	 *
	 * @var string
	 */

	private static $_serverTimeZone = 'UTC';

	/**
	 * Sets the user's current timezone from a string, e.g. 'Europe/London' or 'UTC'.
	 * @param string $zoneStr the timezone to set
	 */

	static function setUserTimeZone($zoneStr) {
		self::$_userTimeZone = $zoneStr;
	}

	/**
	 * Gets the user's current timezone string.
	 * @return string the timezone string
	 */

	static function getUserTimeZone() {
		return self::$_userTimeZone;
	}

	/**
	 * Converts a given date from user to server time.
	 * @param mixed $date the date as user time
	 * @return string the date as server time in the format Y-m-d H:i:s
	 */

	static function toServerTime($date){
		return self::convertTimes($date,self::getUserTimeZone(),self::$_serverTimeZone);
	}

	/**
	 * Converts a given date from server to user time.
	 * @param mixed $date the date as server time
	 * @return string the date as user time in the format Y-m-d H:i:s
	 */

	static function toUserTime($date){
		return self::convertTimes($date,self::$_serverTimeZone,self::getUserTimeZone());
	}

	/**
	 * Converts a date from one timezone to another
	 * @param mixed $date the date to convert
	 * @param string $fromZoneStr the original timezone in string format
	 * @param string $toZoneStr the destination timezone in string format
	 * @return string the converted date in the destination timezone, in the format Y-m-d H:i:s
	 */

	static function convertTimes($date,$fromZoneStr,$toZoneStr) {
		$dtime = new DateTime($date, new DateTimeZone($fromZoneStr));
		$dtime->setTimezone(new DateTimeZone($toZoneStr));
		return $dtime->format('Y-m-d H:i:s');
	}

}

?>
