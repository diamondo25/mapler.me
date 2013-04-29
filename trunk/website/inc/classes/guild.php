<?php
require_once __DIR__.'/../database.php';

class Guild {
	public $name, $id, $world_id, $world_name, $notice, $ranks, 
		$capacity,
		$points, $alliance_id,
		$icon_bg, $icon_bgc, $icon_fg, $icon_fgc;
	public $members;
		
	public function LoadByName($name, $world) {
		global $__database;
		$q = $__database->query("
SELECT
	world_data.world_name,
	guilds.*
FROM
	`guilds`
LEFT JOIN 
	`world_data`
	ON
		world_data.world_id = guilds.world_id
WHERE 
	guilds.name = '".$__database->real_escape_string($name)."'
AND
	world_data.world_name = '".$__database->real_escape_string($world)."'");
		if ($q->num_rows == 0) {
			return false;
		}
		
		$row = $q->fetch_assoc();
		$this->id = $row['id'];
		$this->world_id = $row['world_id'];
		$this->world_name = $row['world_name'];
		$this->name = $row['name'];
		$this->notice = $row['notice'];

		$this->ranks = array();
		for ($i = 1; $i <= 5; $i++)
			$this->ranks[$i - 1] = isset($row['rank'.$i]) ? $row['rank'.$i] : '-';

		$this->icon_bg = $row['emblem_bg'];
		$this->icon_bgc = $row['emblem_bg_color'];
		$this->icon_fg = $row['emblem_fg'];
		$this->icon_fgc = $row['emblem_fg_color'];
		
		$this->points = $row['points'];
		$this->alliance_id = $row['alliance_id'];
		$this->capacity = $row['capacity'];
		
		$q->free();
		
		$this->LoadCharacterList();
		
		return true;
	}
	
	private function LoadCharacterList() {
		global $__database;
		
		$q = $__database->query("
SELECT
	characters.name,
	guild_members.rank,
	guild_members.alliance_rank,
	guild_members.contribution
FROM
	`guild_members`
LEFT JOIN 
	`characters`
	ON
		characters.id = guild_members.character_id
WHERE 
	guild_members.guild_id = ".$this->id."
AND
	guild_members.world_id = ".$this->world_id."
AND
	characters.name IS NOT NULL
ORDER BY
	rank ASC");

		$this->members = array();
		while ($row = $q->fetch_assoc()) {
			$this->members[] = $row;
		}
	}
}


?>