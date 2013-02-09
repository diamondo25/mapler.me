<?php
session_start();

//Default set to Pacific Time (MapleStory Time)
date_default_timezone_set('America/Los_Angeles');

require_once 'database.php';
require_once 'class_account.php';
require_once 'class_inventory.php';
require_once 'domains.php';
require_once 'ranks.php';

class Form {
	public $output;

	public function __construct($action, $class = null) {
		$this->output = '<form action="'.$action.'" method="post"';
		if ($class != null) {
			$this->output .= ' class="'.$class.'"';
		}
		$this->output .= '>'."\r\n";
		$this->output .= '<fieldset>'."\r\n";
	}
	
	public function AddBlock($text, $name, $addedClass, $inputType, $inputValue = null, $inputPlaceholder = null, $errorMessage = null) {
		$tmp = <<<END
<div class="control-group{CLASS}">
	<label class="control-label" for="input{NAME}">{TEXT}</label>
	<div class="controls">
		<input type="{TYPE}" id="input{NAME}" name="{NAME}" placeholder="{INPUT_PLACEHOLDER}" value="{INPUT_VALUE}" />{ERROR_MSG}
	</div>
</div>
END;
		$tmp = str_replace(
			array('{NAME}', '{TEXT}', '{CLASS}', '{TYPE}', '{INPUT_PLACEHOLDER}', '{INPUT_VALUE}'), 
			array($name, $text, ($addedClass == '' ? '' : ' '.$addedClass), $inputType, ($inputPlaceholder == null ? '' : $inputPlaceholder), ($inputValue == null ? '' : $inputValue)),
			$tmp
		);
		$tmp = str_replace(
			'{ERROR_MSG}', 
			$errorMessage == null ? '' : '<span class="help-inline">'.$errorMessage.'</span>',
			$tmp
		);
		$this->output .= $tmp;
	}
	
	
	public function AddEmptyBlock() {
		$this->output .= '<div class="control-group">&nbsp;</div>';
	}
	
	public function Agreement() {
		$this->output .= '
<div class="control-group">
	<label class="control-label" for="input">Do you agree to our <a href="/terms">Terms of Use?</a></label>
	<div class="controls">
		<input type="checkbox" name="tou" />
	</div>
</div>';
	}
	
	public function MakeButton($type, $text, $name = '', $addedClass = '') {
		$tmp = <<<END
<div class="form-actions">
	<button type="{TYPE}" class="btn btn-danger{CLASS}"{NAME}>{TEXT}</button>
</div>
END;
		$tmp = str_replace(
			array('{NAME}', '{TEXT}', '{CLASS}', '{TYPE}'), 
			array(($name == '' ? '' : 'name="'.$name.'"'), $text, ($addedClass == '' ? '' : ' '.$addedClass), $type),
			$tmp
		);
		$this->output .= $tmp;
	}
	
	public function MakeSubmit($text) {
		$this->MakeButton('submit', $text, '', 'btn-primary');
	}
	
	public function Write($text) {
		$this->output .= $text;
	}
	public function End() {
		$this->output .= '</fieldset>'."\r\n";
		$this->Write('</form>');
		echo $this->output;
	}
}

function CheckArrayOf($from, $arrayValues, &$errorList) {
	$errorList = array();
	foreach ($arrayValues as $name) {
		if (empty($from[$name])) $errorList[$name] = true;
	}
	return count($errorList) == 0;
}

// $vals: array(array("herp", 0, 12))
function IsInBetween($vals) {
	foreach ($vals as $val) {
		if ($val[1] == -1 || strlen($val[0]) >= $val[1]) {
			if ($val[2] == -1 || strlen($val[0]) <= $val[2]) {
				continue;
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
	return true;
}


// Password = 28 characters in DB, but uses MD5 (32) characters to confuse hackers. And has a salt aswell.
function GetPasswordHash($password, $salt) {
	return substr(md5($salt.$password), 0, 28);
}

function GetMapleStoryString($type, $id, $key) {
	global $__database;
	
	$apcinstalled = function_exists("apc_add");
	
	if (strlen($key) > 5) {
		// Yea...
		$key = substr($key, 0, 5);
	}
	
	if ($apcinstalled && !apc_exists("data_cache")) {
		apc_add("data_cache", array());
	}
	
	$temp = apc_fetch("data_cache");
	if ($apcinstalled && isset($temp[$type.'|'.$id.'|'.$key])) {
		return $temp[$type.'|'.$id.'|'.$key];
	}
	
	$q = $__database->query("SELECT `value` FROM `strings` WHERE `objecttype` = '".$__database->real_escape_string($type)."' AND `objectid` = ".intval($id)." AND `key` = '".$__database->real_escape_string($key)."'");
	if ($q->num_rows >= 1) {
		$row = $q->fetch_array();
		$tmp = $row[0];
		
		if ($apcinstalled) {
			$temp[$type.'|'.$id.'|'.$key] = $tmp;
			apc_store("data_cache", $temp);
		}
		
		$q->free();
		return $tmp;
	}
	$q->free();
	return NULL;
}

function GetInventoryName($id) {
	switch ($id) {
		case 0: return "Equipment";
		case 1: return "Usage";
		case 2: return "Set-Up";
		case 3: return "Etc";
		case 4: return "Cash";
	}
}

function GetSystemTimeFromFileTime($time) {
	return date("Y-m-d h:i:s", $time);
}


function GetCorrectStat($internal_id) {
	global $__database;
	
	$q = $__database->query("SELECT SUM(`str`) AS `str`, SUM(`dex`) AS `dex`, SUM(`int`) AS `int`, SUM(`luk`) AS `luk`, SUM(`maxhp`) AS `mhp`, SUM(`maxmp`) AS `mmp` FROM `items` WHERE `character_id` = ".intval($internal_id)." AND slot < 0");
	if ($q->num_rows >= 1) {
		$tmp = $q->fetch_assoc();
		$q->free();
		return $tmp;
	}
	$q->free();
	return NULL;
}


function GetCharacterName($id) {
	global $__database;
	
	$q = $__database->query("SELECT name FROM characters WHERE id = '".intval($id)."'");
	if ($q->num_rows >= 1) {
		$tmp = $q->fetch_row();
		$q->free();
		return $tmp[0];
	}
	$q->free();
	return 'Unknown Character';
}


function MakeStatAddition($name, $value, $statarray) {
	$add = $statarray[$name];
	if ($add > 0) {
		return ($value + $add).' ('.$value.' + '.$add.')';
	}
	else {
		return $value;
	}
}

function IsLoggedin() {
	return isset($_SESSION['username']);
}

function IsOwnAccount() {
	global $subdomain;
	return (IsLoggedin() && (strtolower($subdomain) == strtolower($_loginaccount->GetUsername()) || $_loginaccount->GetAccountRank() >= RANK_MODERATOR));
}

function GetItemType($id) {
	return floor($id / 10000);
}

function GetItemInventory($id) {
	return floor($id / 1000000);
}

function GetWZItemTypeName($id) {
	$tmp = GetItemType($id);
	
	switch ($tmp) {
		case 100: return "Cap";
		case 104: return "Coat";
		case 105: return "Longcoat";
		case 106: return "Pants";
		case 107: return "Shoes";
		case 108: return "Glove";
		case 109: return "Shield";
		case 110: return "Cape";
		case 111: return "Ring";
		case 117: return "MonsterBook";
		case 120: return "Totem";
		
		
		case 101:
		case 102:
		case 103:
		case 112:
		case 113:
		case 114:
		case 115:
		case 116:
		case 118:
		case 119:
			return "Accessory";
		
		
		case 121:
		case 122:
		case 130:
		case 131:
		case 132:
		case 133:
		case 134:
		case 135:
		case 136:
		case 137:
		case 138:
		case 139: // FISTFIGHT!!! (sfx: barehands, only 1 item: 1392000)
		case 140:
		case 141:
		case 142:
		case 143:
		case 144:
		case 145:
		case 146:
		case 147:
		case 148:
		case 149:
		case 150:
		case 151:
		case 152:
		case 153:
		case 160:
		case 170:
			return "Weapon";
		
		case 161: 
		case 162: 
		case 163: 
		case 164: 
		case 165: 
			return "Mechanic";
			
		case 180: 
		case 181: 
			return "PetEquip";
		
		case 190: 
		case 191: 
		case 192: 
		case 193: 
		case 198: 
		case 199: 
			return "TamingMob";

		case 194:
		case 195:
		case 196:
		case 197:
			return "Dragon";
			
		
		case 166: 
		case 167: 
			return "Android";
			
		case 996: return "Familiar";
	}
}

function GetItemIcon($id) {
	$domain = '//static_images.mapler.me/';
	$inv = GetItemInventory($id);
	if ($inv == 1) {
		$name = GetWZItemTypeName($id);
		$url = $domain.'Character/'.$name.'/'.str_pad($id, 8, '0', STR_PAD_LEFT).'.img/info.icon.png';
	}
	else {
		$type = GetItemType($id);
		if ($type == 500) {
			$url = $domain.'Inventory/Pet/'.$id.'.img/info.icon.png';
		}
		else {
			$typeid = str_pad($type, 4, '0', STR_PAD_LEFT).'.img';
			$typename = '';
			switch (floor($type / 100)) {
				case 2: $typename = 'Consume'; break;
				case 3: $typename = 'Install'; break;
				case 4: $typename = 'Etc'; break;
				case 5: $typename = 'Cash'; break;
			}
			$url = $domain.'Inventory/'.$typename.'/'.$typeid.'/'.str_pad($id, 8, '0', STR_PAD_LEFT).'/info.icon.png';
		}
	}
	
	return $url;
}



// Initialize more stuffs



// Initialize Login Data
$_loggedin = false;
if (isset($_SESSION['username'])) {
	$username = $_SESSION['username'];
	$_loggedin = (strpos($_SERVER['REQUEST_URI'], '/logoff') === FALSE);
	$_loginaccount = Account::Load($username);
}

// Set to null by default
$__url_useraccount = null;

if ($subdomain != "" && $subdomain != "www" && $subdomain != "direct" && $subdomain != "dev" && $subdomain != "social") {
	// Tries to recieve userdata for the subdomain. If it fails, results in a 404.
	
	$__url_useraccount = Account::Load($subdomain);
	if ($__url_useraccount == null) {
		// User Not Found Results In 404
		header("HTTP/1.1 404 File Not Found", 404);
		exit;
	}
	
	/*
	
	$username = $__database->real_escape_string($subdomain);
	$q = $__database->query("SELECT * FROM accounts WHERE username = '".$username."'");
	if ($q->num_rows > 0) {
		$__url_userdata = $q->fetch_assoc();
	}
	else {
		// User Not Found Results In 404
		header("HTTP/1.1 404 File Not Found", 404);
		exit;
	}
	$q->free();
	*/
}



?>