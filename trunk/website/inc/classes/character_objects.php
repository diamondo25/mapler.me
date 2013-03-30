<?php
require_once __DIR__.'/../database.php';

class Skill {
	private $id, $level, $maxlevel, $expires;
	
	public function __construct($data) {
		$this->id = $data['skillid'];
		$this->level = $data['level'];
		$this->maxlevel = $data['maxlevel'];
		$this->expires = ceil(($data['expires'] / 10000000) - 11644473600);
	}
	
	public function GetID() {
		return $this->id;
	}
	
	public function GetLevel() {
		return $this->level;
	}
	
	public function GetMaxLevel() {
		return $this->maxlevel;
	}
	
	public function GetExpireTime() {
		return $this->expires;
	}
	
	public function IsExpired() {
		return time() > $this->expires;
	}
}
?>