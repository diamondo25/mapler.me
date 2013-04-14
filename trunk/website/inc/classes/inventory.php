<?php
require_once __DIR__.'/../database.php';

define("ITEM_RECHARGE", 1);
define("ITEM_EQUIP", 2);
define("ITEM_PET", 3);

class InventoryData {
	private $inventories;
	private $bags;
	private $equips;
	
	// $emulateData SEE character.php; push the full array
	public function __construct($character_id, $emulateData = null) {
		global $__database;
		
		$this->equips = array();
		
		if ($emulateData != null) {
			$row = array(
				$emulateData['own_data']['eqp_slots'],
				$emulateData['own_data']['use_slots'], 
				$emulateData['own_data']['setup_slots'], 
				$emulateData['own_data']['etc_slots'],
				$emulateData['own_data']['cash_slots']
			);
		}
		else {
			$q = $__database->query("SELECT eqp_slots, use_slots, setup_slots, etc_slots, cash_slots FROM characters WHERE internal_id = ".$character_id);
			$row = $q->fetch_row();
			$q->free();
		}
		
		$this->inventories = new SplFixedArray(count($row));
		for ($i = 0; $i < count($row); $i++) {
			$this->inventories[$i] = new SplFixedArray($row[$i]);
		}
		
		if ($emulateData != null) {
			$rows = array_filter($emulateData['items'], 'FilterOnlyInventories');
			for ($i = 0; $i < count($rows); $i++) {
				$rows[$i]['expires'] = ceil(($rows[$i]['expires'] / 10000000) - 11644473600);
			}
		}
		else {
			$q = $__database->query("SELECT *, ceil((expires / 10000000) - 11644473600) as expires FROM items WHERE character_id = ".$character_id." AND inventory < 10"); // Only inventory items
			$rows = array();
			while ($row = $q->fetch_assoc()) {
				$rows[] = $row;
			}
			$q->free();
		}
		
		foreach ($rows as $row) {
			$inv = $row['inventory'];
			$slot = $row['slot'];
			if ($inv == 0 && $slot < 0) {
				$this->equips[$slot] = new ItemEquip($row);
			}
			else {
				$slot -= 1;
				if ($slot >= $this->inventories[$inv]->getSize()) {
					$this->inventories[$inv]->setSize($slot + ($slot % 4) + 1);
				}
				$item = ItemBase::MakeItem($row, $emulateData);
				$this->inventories[$inv][$slot] = $item;
				
				if ($item->bagid != -1) {
					$this->bags[$item->bagid] = array();
				}
			}
		}
		
		
		if ($emulateData != null) {
			$rows = array_filter($emulateData['items'], 'FilterOnlyBags');
			for ($i = 0; $i < count($rows); $i++) {
				$rows[$i]['expires'] = ceil(($rows[$i]['expires'] / 10000000) - 11644473600);
			}
		}
		else {
			$q = $__database->query("SELECT *, ceil((expires / 10000000) - 11644473600) as expires FROM items WHERE character_id = ".$character_id." AND inventory >= 10"); // Only bag items
		
			$rows = array();
			while ($row = $q->fetch_assoc()) {
				$rows[] = $row;
			}
			$q->free();
		}
		
		foreach ($rows as $row) {
			$bagid = $row['inventory'];
			if (!isset($this->bags[$bagid])) continue;
			
			$slot = $row['slot'];
			$this->bags[$bagid][$slot] = new ItemBase($row); // Bags only contain regular items (mostly etc)
		}
	}
	
	private function FilterOnlyInventories($value) {
		return $value['inventory'] < 10;
	}
	
	private function FilterOnlyBags($value) {
		return $value['inventory'] >= 10;
	}
	
	public function GetInventory($inventory) {
		return $this->inventories[$inventory];
	}
	
	public function GetEquips() {
		return $this->equips;
	}
}

class ItemBase {
	public $inventory, $slot, $itemid, $expires, $cashid, $amount, $bagid;
	public $type;
	
	public function __construct($row) {
		$this->inventory = (int)$row['inventory'];
		$this->slot = (int)$row['slot'];
		$this->itemid = (int)$row['itemid'];
		$this->expires = $row['expires'];
		$this->cashid = (int)$row['cashid'];
		$this->amount = (int)$row['amount'];
		$this->bagid = (int)$row['bagid'];
	}
	
	public static function MakeItem($row, $emulateData) {
		if ($row['inventory'] == 0)
			$item = new ItemEquip($row);
		elseif (GetItemType($row['itemid']) == 500)
			$item = new ItemPet($row, $emulateData);
		else
			$item = new ItemBase($row);
		return $item;
	}
	
	public function IsExpired() {
		return $this->expires <= time();
	}
}

class ItemRechargable extends ItemBase {
	public $crafter, $flags;
	
	public function __construct($row) {
		parent::__construct($row);
		$this->type = ITEM_RECHARGE;
		
		$this->crafter = $row['name'];
		$this->flags = (int)$row['flags'];
	}
}

class ItemEquip extends ItemBase {
	public $slots;
	public $scrolls;
	public $str;
	public $dex;
	public $int;
	public $luk;
	public $maxhp;
	public $maxmp;
	public $weaponatt;
	public $weapondef;
	public $magicatt;
	public $magicdef;
	public $acc;
	public $avo;
	public $hands;
	public $jump;
	public $speed;
	public $name;
	public $flags;
	public $hammers;
	public $itemlevel;
	public $itemexp;
	public $statusflag;
	public $potential1;
	public $potential2;
	public $potential3;
	public $potential4;
	public $potential5;
	public $potential6;
	public $socketstate;
	public $nebulite1;
	public $nebulite2;
	public $nebulite3;
	public $display_id;

	public function __construct($row) {
		parent::__construct($row);
		$this->type = ITEM_EQUIP;
		
		$this->slots = (int)$row['slots'];
		$this->scrolls = (int)$row['scrolls'];
		$this->str = (int)$row['str'];
		$this->dex = (int)$row['dex'];
		$this->int = (int)$row['int'];
		$this->luk = (int)$row['luk'];
		$this->maxhp = (int)$row['maxhp'];
		$this->maxmp = (int)$row['maxmp'];
		$this->weaponatt = (int)$row['weaponatt'];
		$this->weapondef = (int)$row['weapondef'];
		$this->magicatt = (int)$row['magicatt'];
		$this->magicdef = (int)$row['magicdef'];
		$this->acc = (int)$row['acc'];
		$this->avo = (int)$row['avo'];
		$this->hands = (int)$row['hands'];
		$this->jump = (int)$row['jump'];
		$this->speed = (int)$row['speed'];
		$this->name = $row['name'];
		$this->flags = (int)$row['flags'];
		$this->hammers = (int)$row['hammers'];
		$this->itemlevel = (int)$row['itemlevel'];
		$this->itemexp = (int)$row['itemexp'];
		$this->statusflag = (int)$row['statusflag'];
		$this->potential1 = (int)$row['potential1'];
		$this->potential2 = (int)$row['potential2'];
		$this->potential3 = (int)$row['potential3'];
		$this->potential4 = (int)$row['potential4'];
		$this->potential5 = (int)$row['potential5'];
		$this->potential6 = (int)$row['potential6'];
		$this->socketstate = (int)$row['socketstate'];
		$this->nebulite1 = (int)$row['nebulite1'];
		$this->nebulite2 = (int)$row['nebulite2'];
		$this->nebulite3 = (int)$row['nebulite3'];
		$this->display_id = (int)$row['display_id'];
	}

	public function HasLock() {
		return ($this->flags & 0x01) != 0 ? 1 : 0;
	}

	public function HasSpikes() {
		return ($this->flags & 0x02) != 0 ? 1 : 0;
	}

	public function HasColdProtection() {
		return ($this->flags & 0x04) != 0 ? 1 : 0;
	}

	public function TradeBlocked() {
		return ($this->flags & 0x08) != 0 ? 1 : 0;
	}

	public function IsKarmad() {
		return ($this->flags & 0x10) != 0 ? 1 : 0;
	}

	public function HasClosedPotential() {
		return ($this->statusflag & 0x0001) != 0 ? 1 : 0;
	}
}

class ItemPet extends ItemBase {
	public $name, $closeness, $fullness, $level;
	
	public function __construct($row, $emulateData = null) {
		global $__database;
		$this->type = ITEM_PET;
		
		parent::__construct($row);
		
		$temp = null;
		if ($emulateData != null) {
			foreach ($emulateData['pets'] as $petrow) {
				if ($petrow['cashid'] == $this->cashid) {
					$temp = $petrow;
					break;
				}
			}
		}
		else {
			$q = $__database->query("SELECT * FROM pets WHERE cashid = ".$this->cashid." LIMIT 1");
			$temp = $q->fetch_assoc();
			$q->free();
		}
		
		$this->name = $temp['name'];
		$this->closeness = (int)$temp['closeness'];
		$this->fullness = (int)$temp['fullness'];
		$this->level = (int)$temp['level'];
	}
}
?>