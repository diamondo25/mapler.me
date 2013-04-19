<?php
session_start();

//Default set to Pacific Time (MapleStory Time)
date_default_timezone_set('America/Los_Angeles');
set_time_limit(60);

require_once __DIR__.'/database.php';
require_once __DIR__.'/classes/account.php';
require_once __DIR__.'/classes/inventory.php';
require_once __DIR__.'/classes/statusses.php';
require_once __DIR__.'/domains.php';
require_once __DIR__.'/ranks.php';
require_once __DIR__.'/functions.datastorage.php';
require_once __DIR__.'/bbcode.php';

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
	<label class="control-label" for="input">Do you agree to our <a href="/terms" target="_blank">Terms of Use?</a></label>
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
		$this->MakeButton('submit', $text, '', 'btn-success btn-large');
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

function GetUniqueID() {
	return substr(uniqid(), -5);
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

function time_elapsed_string($etime) {
   if ($etime < 1) {
	   return 'moments';
   }

   $a = array( 12 * 30 * 24 * 60 * 60  =>  'year',
			   30 * 24 * 60 * 60       =>  'month',
			   24 * 60 * 60            =>  'day',
			   60 * 60                 =>  'hour',
			   60                      =>  'minute',
			   1                       =>  'second'
			   );

   foreach ($a as $secs => $str) {
	   $d = $etime / $secs;
	   if ($d > 1) {
		   $r = round($d);
		   return $r . ' ' . $str . ($r > 1 ? 's' : '');
	   }
   }
}

function Explode2($seperator, $subseperator, $value) {
	$result = array();
	foreach (explode($seperator, $value) as $chunk) {
		$pos = strpos($chunk, $subseperator);
		$key = substr($chunk, 0, $pos);
		$value = substr($chunk, $pos + 1);
		$result[$key] = $value;
	}
	return $result;
}

function IGTextToWeb($data, $extraOptions = array()) {
	// Escape quotes
	$data = str_replace(array('"', "'"), array('&quot;', '&#39;'), $data); // Single quote = &#39; -.-

	// Fix newlines
	$data = str_replace('\\\\', '\\', $data); // Triple slashes
	$data = str_replace('\\\\', '\\', $data); // Double slashes
	$data = str_replace(array('\r', '\n'), array("\r", "\n"), $data);
	// Replace all newlines to <br />'s
	$data = nl2br($data);
	// Remove newlines
	$data = str_replace(array("\r", "\n"), array('', ''), $data);

	// Ingame things

	// For 'extra' options, like #incrDAM and such
	if (count($extraOptions) > 0) {
		$_from = array();
		$_to = array();
		foreach ($extraOptions as $from => $to) {
			$_from[] = $form;
			$_to[] = $to;
		}
		$data = str_replace($_from, $_to, $data);
	}

	$endTag = '';
	$result = '';
	$datalen = strlen($data);
	for ($i = 0; $i < $datalen; $i++) {
		$end = ($i + 1 == $datalen);
		$c = $data[$i];
		$preobj = $i > 0 ? $data[$i - 1] : '-';
		if ($c == '#' && $preobj != '&') {
			if ($end) continue;
			$nc = $data[$i + 1];
			$i++;
			if ($nc == 'w') { // no typewriting; ignore
				// CutoutText($data, $i, 2);
			}
			elseif ($nc == 'e') { // Bold
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<strong>';
				$endTag = '</strong>';
			}
			elseif ($nc == 'r') { // Red
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<span style="color: red;">';
				$endTag = '</span>';
			}
			elseif ($nc == 'b') { // Blue
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<span style="color: blue;">';
				$endTag = '</span>';
			}
			elseif ($nc == 'g') { // Green
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<span style="color: green;">';
				$endTag = '</span>';
			}
			elseif ($nc == 'k') { // Black
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<span style="color: black;">';
				$endTag = '</span>';
			}
			elseif ($nc == 'c') { // Orange?
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<span style="color: darkorange;">';
				$endTag = '</span>';
			}
			elseif ($nc == '*') { // Orange? | * == Nebulite info -.-
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '*';
				$result .= '<span style="color: darkorange;">';
				$endTag = '</span>';
			}
			elseif ($nc == 'd') { // Purple
				if ($endTag != '') {
					$result .= $endTag;
				}
				$result .= '<span style="color: purple;">';
				$endTag = '</span>';
			}
			else {
				// Break current one!
				if ($endTag != '') {
					$result .= $endTag;
					$endTag = '';
				}
				else {
					$result .= $c;
				}
				$i--;
			}
		}
		else {
			$result .= $c;
		}
	}

	return $result;
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
	if ($time == 3439756800)
		return '';
	return date("Y-m-d h:i:s", $time);
}


function GetCorrectStat($internal_id) {
	global $__database;

	// Item buffs
	$tmp = NULL;
	$q = $__database->query("SELECT SUM(`str`) AS `str`, SUM(`dex`) AS `dex`, SUM(`int`) AS `int`, SUM(`luk`) AS `luk`, SUM(`maxhp`) AS `mhp`, SUM(`maxmp`) AS `mmp` FROM `items` WHERE `character_id` = ".intval($internal_id)." AND slot < 0");
	if ($q->num_rows >= 1) {
		$tmp = $q->fetch_assoc();
	}
	$q->free();

	return $tmp;
}


function CalculateSkillValue($what, $x) {
	$what = str_replace(
		array("u", "d", "x"), // u(x/2) = ceil(x/2)
		array("ceil", "floor", $x), // d(x/2) = floor(x/2)
		$what
	);

	eval('$value = intval('.$what.');'); // ohboy...
	return $value;
}

function GetSkillBuffs($internal_id) {
	global $__database;

	$q = $__database->query("
SELECT
	s.skillid,
	s.level,
	st.value
FROM
	`skills` s
LEFT JOIN
	`strings` st
		ON
	st.objectid = s.skillid
		AND
	st.key = 'buff'
WHERE
	s.`character_id` = ".intval($internal_id)."
		AND
	st.value IS NOT NULL
");
	$temp = array();
	while ($row = $q->fetch_assoc()) {
		$temp[$row['skillid']] = array('level' => $row['level'], 'data' => Explode2(';', '=', $row['value']));
	}
	$q->free();
	return $temp;
}


function GetCharacterName($id) {
	global $__database;

	$q = $__database->query("SELECT name FROM characters WHERE id = ".intval($id));
	if ($q->num_rows >= 1) {
		$tmp = $q->fetch_row();
		$q->free();
		return $tmp[0];
	}
	$q->free();
	return 'Unknown Character';
}

function GetCharacterAccountId($id) {
	global $__database;

	$q = $__database->query("SELECT GetCharacterAccountId(".intval($id).")");
	$tmp = $q->fetch_row();
	$q->free();
	return $tmp[0];
}

function GetCharacterStatus($id, $account = NULL) {
	global $__database;
	
	$accountLoaded = $account != NULL;
	if (!$accountLoaded) {
		$account = Account::Load(intval(GetCharacterAccountId($id)));
	}
	
	$name = GetCharacterName($id);
	
	$value = $account->GetCharacterDisplayValue($name);
	
	if (!$accountLoaded) { // Clear data
		unset($account);
	}
	
	return $value;
}

function GetFriendStatus($you, $it) {
	global $__database, $_loginaccount;

	$q = $__database->query("SELECT FriendStatus(".intval($you).", ".intval($it).")");
	$tmp = $q->fetch_row();
	$q->free();
	return $tmp[0];
}

function AccountExists($name) {
	global $__database;

	$q = $__database->query("SELECT COUNT(*) FROM accounts WHERE username = '".$__database->real_escape_string($name)."'");
	$tmp = $q->fetch_row();
	$q->free();
	return $tmp[0] != 0;
}

function GetAccountID($name) {
	global $__database;

	$q = $__database->query("SELECT id FROM accounts WHERE username = '".$__database->real_escape_string($name)."'");
	if ($q->num_rows == 0) {
		$q->free();
		return NULL;
	}
	$tmp = $q->fetch_row();
	$q->free();
	return $tmp[0];
}

// only notifications will be friend requests for now.
function GetNotification() {
	global $__database, $_loginaccount;

	$q = $__database->query("SELECT COUNT(*) FROM friend_list WHERE friend_id = ".$_loginaccount->GetId()." AND accepted_on IS NULL");
	$tmp = $q->fetch_row();
	$q->free();
	return $tmp[0];
}

function GetMapname($id, $full = true) {
	$map = GetMapleStoryString("map", $id, "name");
	if ($full) {
		$subname = GetMapleStoryString("map", $id, "street");
		if ($subname != NULL) {
			$map = $subname." - ".$map;
		}
	}
	return $map;
}

function MakeStatAddition($name, $value, $statarray) {
	$add = $statarray[$name];
	if ($add > 0) {
		return ($value + $add);
	}
	else {
		return $value;
	}
}

function IsLoggedin() {
	return isset($_SESSION['username']);
}

function IsOwnAccount() {
	global $subdomain, $_loginaccount;
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
		case 154: // 1542061 is the only wep, 1532061 is missing... NEXON
		case 155: // Fans of the wall, oh wait
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

function GetItemIconID($id) {
	$type = GetItemType($id);
	if ($type != 306) return $id;

	$nebtype = ($id / 1000) % 5;
	$main_id = 3800274;

	return $main_id + $nebtype;
}

function GetItemIcon($id, $addition = '') {
	$domain = '//static_images.mapler.me/';
	$inv = GetItemInventory($id);
	if ($inv == 1) {
		$name = GetWZItemTypeName($id);
		$url = $domain.'Character/'.$name.'/'.str_pad($id, 8, '0', STR_PAD_LEFT).'.img/info.icon'.$addition.'.png';
	}
	else {
		$type = GetItemType($id);
		if ($type == 500) {
			$url = $domain.'Inventory/Pet/'.$id.'.img/info.icon'.$addition.'.png';
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
			$url = $domain.'Inventory/'.$typename.'/'.$typeid.'/'.str_pad($id, 8, '0', STR_PAD_LEFT).'/info.icon'.$addition.'.png';
		}
	}

	return $url;
}

function ValueOrDefault($what, $default) {
	return isset($what) ? $what : $default;
}

// Initialize Login Data
$_loggedin = false;
if (isset($_SESSION['username'])) {
	$username = $_SESSION['username'];
	$_loggedin = (strpos($_SERVER['REQUEST_URI'], '/logoff') === FALSE);
	$_loginaccount = Account::Load($username);
}

// Set to null by default
$__url_useraccount = null;

if ($subdomain != "" && $subdomain != "www" && $subdomain != "direct" && $subdomain != "dev" && $subdomain != "cdn" && $subdomain != "status") {
	// Tries to receive userdata for the subdomain. If it fails, results in a 404.

	$__url_useraccount = Account::Load($subdomain);
	if ($__url_useraccount == null) {
		// User Not Found Results In 404
		header("HTTP/1.1 404 File Not Found", 404);
		header("Location: http://".$domain."/");
		exit;
	}
}
?>