<?php
require_once __DIR__.'/database.php';

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

class Pet {
	public $character_id, $cashid, $itemid, $name, $closeness, $fullness, $level;
	public $expires;
	public $petslot, $equip, $nametag;
	
	public function __construct($data, $petslot) {
		$this->petslot = $petslot;
		$this->character_id = $data['character_id'];
		$this->cashid = $data['cashid'];
		$this->name = $data['name'];
		$this->closeness = $data['closeness'];
		$this->fullness = $data['fullness'];
		$this->level = $data['level'];
		$this->LoadEquipsAndExtraInfo();
	}
	
	private function LoadEquipsAndExtraInfo() {
		global $__database;
		$equipslot = 14;
		$nameslot = 21;
		if ($this->petslot == 2) {
			$equipslot = 30;
			$nameslot = 31;
		}
		elseif ($this->petslot == 3) {
			$equipslot = 38;
			$nameslot = 39;
		}
		
		$this->equip = -1;
		$this->nametag = -1;
		
		$q = $__database->query("
SELECT
	slot, itemid, expires
FROM
	items
WHERE
	character_id = ".$this->character_id."
	AND
	(
		slot = ".$equipslot."
		OR
		slot = ".$nameslot."
		OR
		cashid = ".$this->cashid."
	)");
		while ($row = $q->fetch_row()) {
			if ($row[0] == $equipslot) {
				$this->equip = $row[1];
			}
			elseif ($row[0] == $nameslot) {
				$this->nametag = $row[1];
			}
			else {
				$this->expires = ceil(($row[2] / 10000000) - 11644473600);
				$this->itemid = $row[1];
			}
		}
	}
	
	public static function LoadPet($character_id, $cashid, $petslot = -1) {
		global $__database;
		$q = $__database->query("
SELECT
	*
FROM
	pets
WHERE
	character_id = ".$character_id."
	AND
	cashid = ".$cashid."
	");
		$pet = new Pet($q->fetch_assoc(), $petslot);
		$q->free();
		return $pet;
	}
	
	public static function LoadPets($character_id) {
		global $__database;
		$ret = array(null, null, null);
		
		$q = $__database->query("
SELECT
	petcashid1, petcashid2, petcashid3
FROM
	characters
WHERE
	internal_id = ".$character_id);
		if ($q->num_rows > 0) {
			$data = $q->fetch_row();
			for ($i = 0; $i < 3; $i++) {
				if ($data[$i] != 0)
					$ret[$i] = Pet::LoadPet($character_id, $data[$i], $i + 1);
			}
		}
		$q->free();
		return $ret;
	}
	
	public function IsExpired() {
		return time() > $this->expires;
	}
}
?>