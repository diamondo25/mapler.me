<?php
//Default set to Pacific Time (MapleStory Time)
date_default_timezone_set('America/Los_Angeles');
include_once "domains.php";
include_once "ranks.php";

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
	
	if (strlen($key) > 5) {
		// Yea...
		$key = substr($key, 0, 5);
	}
	
	$q = $__database->query("SELECT `value` FROM `strings` WHERE `objecttype` = '".$__database->real_escape_string($type)."' AND `objectid` = ".intval($id)." AND `key` = '".$__database->real_escape_string($key)."'");
	if ($q->num_rows >= 1) {
		$row = $q->fetch_array();
		$tmp = $row[0];
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
	return isset($_SESSION['login_data']);
}

function IsOwnAccount() {
	global $subdomain;
	return (IsLoggedin() && (strtolower($subdomain) == strtolower($_SESSION['login_data']['username']) || $_SESSION['login_data']['account_rank'] >= RANK_MODERATOR));
}
?>