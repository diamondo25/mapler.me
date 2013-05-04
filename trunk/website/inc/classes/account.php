<?php
require_once __DIR__.'/../database.php';
require_once __DIR__.'/../ranks.php';


class Account {
	private static $___cached_account_list;

	private $_id;
	private $_username,			$_fullname,			$_email,
			$_nickname,			$_lastlogin,		$_lastip,
			$_accountrank,		$_premiumtill,		$_bio,
			$registered,		$_configuration,	$_lastlogin_secs;
			
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
	configuration = '".$__database->real_escape_string(json_encode($this->_configuration))."'

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
	
	// Configuraton functions
	
	public function GetMainCharacterName() {
		$config = $this->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));
		return $config['main_character'];
	}
	
	public function GetCharacterDisplayValue($charname) {
		$config = $this->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));
		
		if ($config['characters'] == NULL) return 0;
		if (isset($config['characters'][$charname])) return $config['characters'][$charname];
		
		return 0;
	}
}

?>