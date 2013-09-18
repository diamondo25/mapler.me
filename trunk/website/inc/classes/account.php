<?php
require_once __DIR__.'/database.php';
require_once __DIR__.'/../ranks.php';


class Account {
	private static $___cached_account_list;

	private $_id;
	private $_username,			$_fullname,			$_email,
			$_nickname,			$_lastlogin,		$_lastip,
			$_accountrank,		$_premiumtill,		$_bio,
			$registered,		$_configuration,	$_lastlogin_secs,
			$_muted;
			
	public static function Load($input) {
		global $__database;
		
		if (is_numeric($input) && isset(self::$___cached_account_list[$input])) {
			// Check if loaded
			return self::$___cached_account_list[$input];
		}
		
		$temp = "
SELECT 
	accounts.*,
	TIMESTAMPDIFF(SECOND, last_login, NOW()) AS `last_login_secs_since`
FROM 
	accounts 
WHERE
";
		if (is_numeric($input))
			$temp .= "id = ".$input;
		else
			$temp .= "username = '".$__database->real_escape_string($input)."'";

		$q = $__database->query($temp);
		if ($q->num_rows > 0) {
			$row = $q->fetch_assoc();
			if (isset(self::$___cached_account_list[$row['id']])) {
				// Check if loaded
				return self::$___cached_account_list[$row['id']];
			}
			
			$account = new Account($row);
			
			self::$___cached_account_list[$row['id']] = $account;
			return $account;
		}
		return null;
	}
	
	public function __construct($row) {
		$this->_id = $row['id'];
		$this->_username = $row['username'];
		$this->_fullname = $row['full_name'];
		$this->_email = $row['email'];
		$this->_nickname = $row['nickname'];
		$this->_lastlogin = $row['last_login'];
		$this->_lastip = $row['last_ip'];
		$this->_accountrank = $row['account_rank'];
		$this->_premiumtill = $row['premium_till'];
		$this->_bio = $row['bio'];
		$this->_registered = $row['registered_on'];
		$this->_muted = (int)$row['muted'];
		$this->_configuration = $row['configuration'] == null ? array() : json_decode($row['configuration'], true);
		if (!isset($row['last_login_secs_since']))
			$this->_lastlogin_secs = 1; // Manual load...
		else
			$this->_lastlogin_secs = $row['last_login_secs_since'];
	}
	
	public function Save() {
		global $__database;
		$__database->query("
UPDATE
	accounts
SET
	full_name = '".$__database->real_escape_string($this->_fullname)."',
	email = '".$__database->real_escape_string($this->_email)."',
	nickname = '".$__database->real_escape_string($this->_nickname)."',
	account_rank = '".intval($this->_accountrank)."',
	premium_till = '".$__database->real_escape_string($this->_premiumtill)."',
	bio = '".$__database->real_escape_string($this->_bio)."',
	configuration = '".$__database->real_escape_string(json_encode($this->_configuration))."',
	muted = ".$this->_muted."

WHERE
	id = ".$this->_id);
	}
	
	public function GetID() {
		return $this->_id;
	}
	
	public function GetUsername() {
		return $this->_username;
	}
	
	public function GetFullName() {
		return $this->_fullname;
	}
	
	public function SetFullName($value) {
		$this->_fullname = $value;
	}
	
	public function GetEmail() {
		return $this->_email;
	}
	
	public function SetEmail($value) {
		$this->_email = $value;
	}
	
	public function GetNickname() {
		return $this->_nickname;
	}
	
	public function SetNickname($value) {
		$this->_nickname = $value;
	}
	
	public function GetLastLogin() {
		return $this->_lastlogin;
	}
	
	public function GetLastLoginSeconds() {
		return $this->_lastlogin_secs;
	}
	
	public function GetLastIP() {
		return $this->_lastip;
	}

	public function GetAccountRank() {
		return $this->_accountrank;
	}
	
	public function SetAccountRank($value) {
		$this->_accountrank = $value;
	}
	
	public function GetPremiumTime() {
		return $this->_premiumtill;
	}
	
	public function SetPremiumTime($value) {
		$this->_premiumtill = $value;
	}
	
	public function GetBio() {
		return $this->_bio;
	}
	
	public function SetBio($value) {
		$this->_bio = $value;
	}
	
	public function GetRegisterDate() {
		return $this->_registered;
	}
	
	public function IsRankOrHigher($rank) {
		return $this->_accountrank >= $rank;
	}
	
	
	public function IsMuted() {
		return $this->_muted == 1;
	}
	
	public function SetMute($value) {
		$this->_muted = $value;
	}
	
	public function GetMute() {
		return $this->_muted;
	}
	
	public function GetConfigurationOption($name, $default = null) {
		return isset($this->_configuration[$name]) ? $this->_configuration[$name] : $default;
	}
	
	public function SetConfigurationOption($name, $value, $save = true) {
		global $__database;
		$this->_configuration[$name] = $value;
		
		if ($save) {
			$__database->query("UPDATE accounts SET configuration = '".$__database->real_escape_string(json_encode($this->_configuration))."' WHERE id = ".$this->_id);
		}
	}
	
	public function GetTheme($default = 'light') {
    	$theme = $this->GetConfigurationOption('theme');
    	
    	if ($theme === NULL) {
        	$theme = $default;
    	}
    	return $theme;
	}
	
	// Configuraton functions
	
	public function GetMainCharacterName() {
		$config = $this->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));
		$name = $config['main_character'];
		if ($name !== null) {
			if (strpos($name, ':') === false) {
				$name = 'gms:'.$name;
				$config['main_character'] = $name;
				$this->SetConfigurationOption('character_config', $config);
			}
			
			$parts = explode(':', $name);
			$name = $parts[1];
			$locale = $parts[0];
			
			// Check if exists
			$_char_db = ConnectCharacterDatabase($locale);
			$q = $_char_db->query("SELECT id FROM characters WHERE name = '".$_char_db->real_escape_string($name)."'");
			if ($q->num_rows == 0) {
				$name = null;
				$config['main_character'] = null;
				$this->SetConfigurationOption('character_config', $config);
			}
			$q->free();
			
			if ($name === null) return null;
			return array('locale' => $locale, 'name' => $name);
		}
		return null;
	}
	
	public function GetCharacterDisplayValue($charname) {
		$config = $this->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));
		
		if ($config['characters'] == NULL) return 0;
		if (isset($config['characters'][$charname])) return $config['characters'][$charname];
		
		return 0;
	}
	
	public function AddAccountNotification($type, $data, $email = true) {
		$__database->query("
INSERT INTO
	account_notifications
VALUES
	(
		NULL,
		".$this->_id.",
		'".$__database->real_escape_string($type)."',
		'".$__database->real_escape_string($data)."',
		NOW()
	)
");
		
		if ($email) {
			// to be made...
			switch ($type) {
				case 'status_response': break;
				case 'friend_request': break;
				case 'mentioned': break;
			}
		}
	}
	
	public function CreateAndSaveToken($type, $days_available = 2) {
		global $__database;
		$allowedTypes = array('screenshot', 'reset_password'); // check database
		if (!in_array($type, $allowedTypes)) return null;
		
		$token_len = 12;
		$token = '';
		for ($i = 0; $i < $token_len; $i++) {
			$token .= chr(0x41 + rand(0, 24));
		}
		
		$__database->query("
INSERT INTO
	account_tokens
VALUES
	(
		".$this->id.",
		'".$type."',
		'".$__database->real_escape_string($token)."',
		DATE_ADD(NOW(), INTERVAL ".intval($days_available)." DAY)
	)
ON DUPLICATE KEY
	code = VALUES(`code`)
");

		return $token;
	}
	

	
	public static function IsDisallowedUsername($username) {
		global $__database;
		$username_regex = "/^[a-z0-9\-\_]+$/";
		$error = '';
		$len = strlen($username);
		$disallowed = array("nexon", "nexonamerica", "wizet", "hacker", "waltzing", "maple", "maplestory", 
		"staff", "admin", "administrator", "moderator", "team", "hack", "hacking", "mesos", "meso", "fuck", 
		"shit", "asshole", "nigger", "faggot", "cunt", "pussy", "dick", "vagina", "penis", "mail", "cdn", 
		"user", "users", "contact", "support", "legal", "sales", "bitch", "whore", "slut", "mapleteam", 
		"girasol", "hime", "mrbasil", "basilmarket", "southperry", "leafre", "n3x0n", "maplestorysea", 
		"nexonkorea", "nexn", "w8baby", "gamersoul", "ccplz", "BT", "nexonforums", "mesoseller", 
		//spoofing names of staff or nexon
		"hackshield", "tylerliberman", "timbervvoIf", "timbervvolf", "timberwoIf", "diamondo", 
		"diamondo24", "marys", "maryse1", "marys3", "TyIer", "ThebIuecorsair");
		
		if ($len < 4 || $len > 20) {
			$error = "A Mapler.me username has to be between four and twenty characters long.";
		}
		elseif (preg_match($username_regex, $username) == 0) {
			$error = "A Mapler.me username may only hold alphanumeric characters.";
		}
		else {
			$nope = false;
			foreach ($disallowed as $name) {
				if (strpos($username, $name) !== FALSE) {
					$nope = true;
					break;
				}
			}
			if ($nope) {
				$error = "That username is disallowed, please choose another.";
			}
			else {
				$result = $__database->query("SELECT id FROM ".DB_ACCOUNTS.".accounts WHERE username = '".$__database->real_escape_string($username)."'");
				if ($result->num_rows == 1) {
					$error = "This username has already been taken, please try another.";
				}
				$result->free();
			}
		}
		return $error;
	}
	
	public static function IsCorrectNickname($nickname) {
		global $__database;
		$error = '';
		$len = strlen($nickname);
		if ($len < 4 || $len > 20) {
			$error = "Nickname has to be between four and twenty characters long.";
		}
		return $error;
	}
}

?>