<?php

class business {
	private static $businessId;
	private static $business;

	function __construct($businessId) {
		$this->setbusinessId($businessId);
		$this->setbusiness();
	}

	public static function getbusinessId() {
		return self::$businessId;
	}

	private function setbusinessId($businessId) {
		self::$businessId = $businessId;
		return $this;
	}

	public static function getbusiness() {
		if(!isset(self::$business)) {
			self::setbusiness();
		}
		return self::$business;
	}

	private static function setbusiness() {
		self::$business = business_get_business(self::$businessId);
	}
}