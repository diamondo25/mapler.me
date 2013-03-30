<?php
require_once __DIR__.'/../database.php';
require_once __DIR__.'/../inventory.php';
require_once __DIR__.'/../character_objects.php';

class Character {
	private $internal_id;
	private $id;
	private $name;
	private $userid;
	private $world_id;
	private $channel_id;
	private $level;
	private $job;
	private $str;
	private $dex;
	private $int;
	private $luk;
	private $chp;
	private $mhp;
	private $cmp;
	private $mmp;
	private $ap;
	private $sp;
	private $exp;
	private $fame;
	private $map;
	private $pos;
	private $honourlevel;
	private $honourexp;
	private $mesos;
	private $demonmark;
	private $gender;
	private $skin;
	private $eyes;
	private $hair;
	private $eqp_slots;
	private $use_slots;
	private $setup_slots;
	private $etc_slots;
	private $cash_slots;
	private $blessingoffairy;
	private $blessingofempress;
	private $ultimateexplorer;
	private $last_update;
	
	private $inventory;
	private $skills;
	
	// When you call the constructor with fullLoad off, it will only load the data from the Characters table.
	// $emulateData is a variable for the character progress points; if it's not null, it will use the data given
	public function __construct($data, $fullLoad = true, $emulateData = null) {
		$this->internal_id = $data['internal_id'];
		$this->id = $data['id'];
		$this->name = $data['name'];
		$this->userid = $data['userid'];
		$this->world_id = $data['world_id'];
		$this->channel_id = $data['channel_id'];
		$this->level = $data['level'];
		$this->job = $data['job'];
		$this->str = $data['str'];
		$this->dex = $data['dex'];
		$this->int = $data['int'];
		$this->luk = $data['luk'];
		$this->chp = $data['chp'];
		$this->mhp = $data['mhp'];
		$this->cmp = $data['cmp'];
		$this->mmp = $data['mmp'];
		$this->ap = $data['ap'];
		$this->sp = $data['sp'];
		$this->exp = $data['exp'];
		$this->fame = $data['fame'];
		$this->map = $data['map'];
		$this->pos = $data['pos'];
		$this->honourlevel = $data['honourlevel'];
		$this->honourexp = $data['honourexp'];
		$this->mesos = $data['mesos'];
		$this->demonmark = $data['demonmark'];
		$this->gender = $data['gender'];
		$this->skin = $data['skin'];
		$this->eyes = $data['eyes'];
		$this->hair = $data['hair'];
		$this->eqp_slots = $data['eqp_slots'];
		$this->use_slots = $data['use_slots'];
		$this->setup_slots = $data['setup_slots'];
		$this->etc_slots = $data['etc_slots'];
		$this->cash_slots = $data['cash_slots'];
		$this->blessingoffairy = $data['blessingoffairy'];
		$this->blessingofempress = $data['blessingofempress'];
		$this->ultimateexplorer = $data['ultimateexplorer'];
		$this->last_update = $data['last_update'];
		
		if ($fullLoad) {
			$this->inventory = new InventoryData($this->id, $emulateData);
			
			if ($emulateData != null) {
				$rows = $emulateData['skills'];
			}
			else {
				$rows = ExtendedMysqli::GetAllRows($__database->query("SELECT * FROM skills WHERE character_id = ".$this->internal_id));
			}
			
			foreach ($rows as $row) {
				$skill = new Skill($row);
				$this->skills[$skill->GetID()] = $skill;
			}
		}
	}

	public function GetInternalID() {
		// Never show this to the client; security purposes.
		return $this->internal_id;
	}

	public function GetID() {
		// Never show this to the client; security purposes. (Nexon)
		return $this->id;
	}
	
	public function GetName() {
		return $this->name;
	}
	
	public function GetUserID() {
		return $this->userid;
	}

	public function GetWorldID() {
		return $this->world_id;
	}

	public function GetChannelID() {
		return $this->channel_id;
	}

	public function GetLevel() {
		return $this->level;
	}

	public function GetJob() {
		return $this->job;
	}

	public function GetSTR() {
		return $this->str;
	}

	public function GetDEX() {
		return $this->dex;
	}

	public function GetINT() {
		return $this->int;
	}

	public function GetLUK() {
		return $this->luk;
	}

	public function GetCurrentMP() {
		return $this->chp;
	}

	public function GetMaxHP() {
		return $this->mhp;
	}

	public function GetCurrentMP() {
		return $this->cmp;
	}

	public function GetMaxMP() {
		return $this->mmp;
	}

	public function GetAP() {
		return $this->ap;
	}

	public function GetSP() {
		return $this->sp;
	}

	public function GetEXP() {
		return $this->exp;
	}

	public function GetFame() {
		return $this->fame;
	}

	public function GetMap() {
		return $this->map;
	}

	public function GetPos() {
		return $this->pos;
	}

	public function GetHonourLevel() {
		return $this->honourlevel;
	}

	public function GetHonourEXP() {
		return $this->honourexp;
	}

	public function GetMesos() {
		return $this->mesos;
	}

	public function GetDemonMark() {
		return $this->demonmark;
	}

	public function GetGender() {
		return $this->gender;
	}
	
	public function IsFemale() {
		return $this->GetGender() == 1;
	}

	public function GetSkin() {
		return $this->skin;
	}

	public function GetEyes() {
		return $this->eyes;
	}

	public function GetHair() {
		return $this->hair;
	}

	public function GetEqpSlots() {
		return $this->eqp_slots;
	}

	public function GetUseSlots() {
		return $this->use_slots;
	}
	
	public function GetSetupSlots() {
		return $this->setup_slots;
	}

	public function GetEtcSlots() {
		return $this->etc_slots;
	}


	public function GetCashSlots() {
		return $this->cash_slots;
	}

	public function GetBlessingOfFairy() {
		return $this->blessingoffairy;
	}


	public function GetBlessingOfEmpress() {
		return $this->blessingofempress;
	}

	public function GetUltimateExplorer() {
		return $this->ultimateexplorer;
	}

	public function GetLastUpdate() {
		return $this->last_update;
	}

}
?>