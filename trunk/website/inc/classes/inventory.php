<?php
require_once __DIR__.'/database.php';
require_once __DIR__.'/../functions.php';

define("ITEM_RECHARGE", 1);
define("ITEM_EQUIP", 2);
define("ITEM_PET", 3);

class InventoryData {
	private $inventories;
	private $bags;
	private $equips;
	
	public function __construct($character_id, $locale) {
		$db = ConnectCharacterDatabase($locale);		
		$this->equips = array();
		
		$q = $db->query("SELECT eqp_slots, use_slots, setup_slots, etc_slots, cash_slots FROM characters WHERE internal_id = ".$character_id);
		$row = $q->fetch_row();
		$q->free();
		
		$this->inventories = new SplFixedArray(count($row));
		for ($i = 0; $i < count($row); $i++) {
			$this->inventories[$i] = new SplFixedArray($row[$i]);
		}
		
		$q = $db->query("SELECT *, ceil((expires / 10000000) - 11644473600) as expires FROM items WHERE character_id = ".$character_id." AND inventory < 10"); // Only inventory items
		$rows = array();
		while ($row = $q->fetch_assoc()) {
			$rows[] = $row;
		}
		$q->free();
		
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
				$item = ItemBase::MakeItem($row, $locale);
				$this->inventories[$inv][$slot] = $item;
				
				if ($item->bagid != -1) {
					$this->bags[$item->bagid] = array();
				}
			}
		}
		
	
		$q = $db->query("SELECT *, ceil((expires / 10000000) - 11644473600) as expires FROM items WHERE character_id = ".$character_id." AND inventory >= 10"); // Only bag items
	
		$rows = array();
		while ($row = $q->fetch_assoc()) {
			$rows[] = $row;
		}
		$q->free();
		
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
	
	public static function MakeItem($row, $locale) {
		if ($row['inventory'] == 0)
			$item = new ItemEquip($row);
		elseif (GetItemType($row['itemid']) == 500)
			$item = new ItemPet($row, $locale);
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
	public $moreflags;

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
		$this->moreflags = $row['moreflags'];
	}

	public function HasLock() {
		return ($this->flags & 0x0001) > 0 ? 1 : 0;
	}

	public function HasSpikes() {
		return ($this->flags & 0x0002) > 0 ? 1 : 0;
	}

	public function HasColdProtection() {
		return ($this->flags & 0x0004) > 0 ? 1 : 0;
	}

	public function TradeBlocked() {
		return ($this->flags & 0x0008) > 0 ? 1 : 0;
	}

	public function IsKarmad() {
		return ($this->flags & 0x0010) > 0 ? 1 : 0;
	}

	public function IsLuckyScrolled() {
		return ($this->flags & 0x0200) > 0 ? 1 : 0;
	}

	public function HasClosedPotential() {
		return ($this->statusflag & 0x0001) > 0 ? 1 : 0;
	}
}

class ItemPet extends ItemBase {
	public $name, $closeness, $fullness, $level;
	
	public function __construct($row, $locale) {
		$db = ConnectCharacterDatabase($locale);
		$this->type = ITEM_PET;
		
		parent::__construct($row);
		
		$temp = null;
		$q = $db->query("SELECT * FROM pets WHERE cashid = ".$this->cashid." LIMIT 1");
		$temp = $q->fetch_assoc();
		$q->free();
		
		$this->name = $temp['name'];
		$this->closeness = (int)$temp['closeness'];
		$this->fullness = (int)$temp['fullness'];
		$this->level = (int)$temp['level'];
	}
}
?>