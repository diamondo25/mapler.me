<?php
class ExtendedMysqli extends mysqli {
	public $last_query = "";
	public $query_count = 0;
	
	public function query($pQuery) {
		$this->last_query = $pQuery;
		$this->query_count++;
		
		$result = parent::query($pQuery) or die($this->get_debug_info());
		return $result;
	}
	
	public function get_debug_info() {
		$error_msg = <<<NO_END
<h2>Oh noes!</h2>
The server made a boo-boo! Our technical Coolie Zombies are after this problem. For now, <a href="/">please return to the landing page of Mapler.me</a>!
<!--
{ERROR_DATA_HERE}
-->
NO_END;

		$error_msg = str_replace("{ERROR_DATA_HERE}", $this->error." (errno. ".$this->errno.")\r\n".base64_encode($this->last_query), $error_msg);
		return $error_msg;
	}
}


$__database = new ExtendedMysqli("stats.craftnet.nl", "maplestats", "maplederp", "maplestats");
if ($__database->connect_errno != 0) {
	die("<strong>Failed to connect to the MySQL server: ".$__database->connect_error." (errno: ".$__database->connect_errno.")</strong>");
}

?>