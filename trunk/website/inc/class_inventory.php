<?php

class InventoryData {
	private $inventories;
	private $equips;
	
	public function __construct($character_id) {
		global $__database;
		
		$this->equips = array();
		
		$q = $__database->query("SELECT eqp_slots, use_slots, setup_slots, etc_slots, cash_slots FROM characters WHERE internal_id = ".$character_id);
		$row = $q->fetch_row();
		
		$this->inventories = new SplFixedArray(count($row));
		for ($i = 0; $i < count($row); $i++) {
			$this->inventories[$i] = new SplFixedArray($row[$i]);
		}
		
		$q->free();
		
		$q = $__database->query("SELECT *, ceil((expires/10000000) - 11644473600) as expires FROM items WHERE character_id = ".$character_id);
		
		while ($row = $q->fetch_assoc()) {
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
				$this->inventories[$inv][$slot] = $inv == 0 ? new ItemEquip($row) : new ItemBase($row);
			}
		}
	}
	
	public function GetInventory($inventory) {
		return $this->inventories[$inventory];
	}
	
	public function GetEquips($inventory) {
		return $this->inventories[$inventory];
	}
}

class ItemBase {
	public $inventory, $slot, $itemid, $expires, $cashid, $amount;
	
	public function __construct($row) {
		$this->inventory = $row['inventory'];
		$this->slot = $row['slot'];
		$this->itemid = $row['itemid'];
		$this->expires = $row['expires'];
		$this->cashid = $row['cashid'];
		$this->amount = $row['amount'];
	}
}

class ItemRechargable extends ItemBase {
	public $crafter, $flags;
	
	public function __construct($row) {
		parent::__construct($row);
		
		$this->crafter = $row['name'];
		$this->flags = $row['flags'];
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
	public $potential1;
	public $potential2;
	public $potential3;
	public $potential4;
	public $potential5;
	public $socketstate;
	public $socket1;
	public $socket2;
	public $socket3;

	public function __construct($row) {
		parent::__construct($row);
		
		$this->slots = $row['slots'];
		$this->scrolls = $row['scrolls'];
		$this->str = $row['str'];
		$this->dex = $row['dex'];
		$this->int = $row['int'];
		$this->luk = $row['luk'];
		$this->maxhp = $row['maxhp'];
		$this->maxmp = $row['maxmp'];
		$this->weaponatt = $row['weaponatt'];
		$this->weapondef = $row['weapondef'];
		$this->magicatt = $row['magicatt'];
		$this->magicdef = $row['magicdef'];
		$this->acc = $row['acc'];
		$this->avo = $row['avo'];
		$this->hands = $row['hands'];
		$this->jump = $row['jump'];
		$this->speed = $row['speed'];
		$this->name = $row['name'];
		$this->flags = $row['flags'];
		$this->hammers = $row['hammers'];
		$this->potential1 = $row['potential1'];
		$this->potential2 = $row['potential2'];
		$this->potential3 = $row['potential3'];
		$this->potential4 = $row['potential4'];
		$this->potential5 = $row['potential5'];
		$this->socketstate = $row['socketstate'];
		$this->socket1 = $row['socket1'];
		$this->socket2 = $row['socket2'];
		$this->socket3 = $row['socket3'];
	}

	public function HasLock() {
		return ($this->flags & 0x01) == 0x01 ? 1 : 0;
	}

	public function HasSpikes() {
		return ($this->flags & 0x02) == 0x02 ? 1 : 0;
	}

	public function HasColdProtection() {
		return ($this->flags & 0x04) == 0x04 ? 1 : 0;
	}

	public function TradeBlocked() {
		return ($this->flags & 0x08) == 0x08 ? 1 : 0;
	}

	public function IsKarmad() {
		return ($this->flags & 0x10) == 0x10 ? 1 : 0;
	}
}
?>