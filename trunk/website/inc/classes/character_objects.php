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


class Quest {
	public $id, $data, $completion_time;
	
	public static function GetQuest($character_id, $quest_id, $internal_quest) {
		global $__database;
		
		$q = $__database->query("
SELECT
	questid,
	`data`
FROM
	quests_running".($internal_quest == true ? '_party' : '')."
WHERE
	character_id = ".$character_id."
	AND
	questid = ".$quest_id);
	
		if ($q->num_rows == 0) {
			// Check if exists in completed quests
			$q->free();
			$q = $__database->query("
SELECT
	questid,
	FROM_FILETIME(`time`) AS `completed`
FROM
	quests_done".($internal_quest == true ? '_party' : '')."
WHERE
	character_id = ".$character_id."
	AND
	questid = ".$quest_id);
			if ($q->num_rows == 0) {
				// Does not exist
				$q->free();
				return null;
			}
			
			$row = $q->fetch_assoc();
			$q->free();
			$quest = new Quest();
			$quest->id = $row['questid'];
			$quest->completion_time = $row['completed'];
			return $quest;
		}
		
		$row = $q->fetch_assoc();
		$q->free();
		$quest = new Quest();
		$quest->id = $row['questid'];
		$quest->data = $row['data'];
		$quest->completion_time = null;
		
		$quest->data = Explode2(';', '=', $quest->data);
		return $quest;
		
	}

	public function IsCompleted() {
		return $this->completion_time !== null;
	}
}

class Android {
	public $name, $type, $skin, $face, $hair;

	public static function GetAndroid($character_id) {
		global $__database;
		
		$q = $__database->query("
SELECT
	*
FROM
	androids
WHERE
	character_id = ".$character_id);
		if ($q->num_rows == 1) {
			$row = $q->fetch_assoc();
			$q->free();
			$droid = new Android();
			$droid->name = $row['name'];
			$droid->type = $row['type'];
			$droid->face = $row['face'];
			$droid->skin = $row['skin'];
			$droid->hair = $row['hair'];
			return $droid;
		}
		$q->free();
		return null;
	}

}