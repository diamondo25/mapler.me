<?php
$char_config = $_loginaccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));


$characternames = array();

$q = $__database->query("
SELECT 
	chr.id, 
	chr.name, 
	w.world_name 
FROM 
	characters chr 
LEFT JOIN 
	users usr 
	ON 
		usr.ID = chr.userid 
LEFT JOIN 
	world_data w 
	ON 
		w.world_id = chr.world_id 
WHERE 
	usr.account_id = '".$__database->real_escape_string($_loginaccount->GetID())."' 
ORDER BY 
	chr.world_id ASC,
	chr.level DESC
");

// printing table rows
$cache = array();

while ($row = $q->fetch_assoc()) {
	$cache[] = $row;
	$characternames[] = $row['name'];
}
$q->free();
?>

		<div class="span7">
<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['char_shown_option'], $_POST['main_character'])) {
	$char_options = $_POST['char_shown_option'];
	$main_char = $_POST['main_character'];
	$error = '';
	
	// Lets see if the 'new' main selected exists
	$found = in_array($main_char, $characternames);
	
	if (!$found) {
		$error = 'An error occurred while selecting the main character.';
	}
	else {
		// Check if all characters under char_shown_option exist

		foreach ($char_options as $charname => $value) {
			$v = intval($value);
			if (!in_array($charname, $characternames) || $v < 0 || $v > 3) {
				$found = false;
				break;
			}
		}
		
		if (!$found) {
			$error = 'An error occurred. Try again. Error code 1';
		}
		elseif (!isset($char_options[$main_char])) {
			// lolwat.
			$error = 'An error occurred. Try again. Error code 2';
		}
		elseif ($char_options[$main_char] != 0) { // Hiding your main character? NO WAY DUUUDE
			$error = 'Heh, you cannot hide your main character!';
		}
		else {
			$char_config['main_character'] = $main_char;
			
			foreach ($char_options as $charname => $value) {
				if (!in_array($charname, $characternames)) {
					$found = false;
					break;
				}
				$char_config['characters'][$charname] = $value;
			}
			
			$_loginaccount->SetConfigurationOption('character_config', $char_config);
		}
	}
	
	if ($error == '') {
?>
<p class="lead alert-success alert">Successfully saved!</p>
<?php
	}
	else {
?>
<p class="lead alert-error alert"><?php echo $error; ?></p>
<?php
	}
}


?>

			<p class="lead">Characters <sub>(Main character and character settings)</sub></p>
			<form class="form-horizontal" method="post">
<?php
foreach ($cache as $row) {
	if ($char_config['main_character'] == null) {
		$char_config['main_character'] = $row['name'];
	}
	$shown_option_value = isset($char_config['characters'][$row['name']]) ? $char_config['characters'][$row['name']] : 0; // Default = 0
?>
				<div class="span3 character-brick" style="min-width:174px;margin-left: 20px;">
				<div class="caption"><img src="//<?php echo $domain; ?>/inc/img/worlds/<?php echo $row['world_name']; ?>.png" />&nbsp;<?php echo $row['name']; ?></div>
					<center>
						<br />
						<a href="//<?php echo $domain; ?>/player/<?php echo $row['name']; ?>" style="text-decoration: none !important; font-weight: 300; color: inherit;">
							<img src="//<?php echo $domain; ?>/avatar/<?php echo $row['name']; ?>"/>
						</a>
						<br />
						<br />
						<br />
						This character is shown: 
						<br />
						<select name="char_shown_option[<?php echo $row['name']; ?>]" style="height:35px !important;">
							<option value="0"<?php echo $shown_option_value == 0 ? ' selected="selected"' : ''; ?>>Always</option>
							<option value="1"<?php echo $shown_option_value == 1 ? ' selected="selected"' : ''; ?>>Only for friends</option>
							<option value="2"<?php echo $shown_option_value == 2 ? ' selected="selected"' : ''; ?>>Never</option>
						</select>
						<br />
						<input type="radio" name="main_character" value="<?php echo $row['name']; ?>"<?php echo $char_config['main_character'] == $row['name'] ? ' checked="checked"' : ''; ?> /> Main character
					</center>
				</div>
<?php
}
?>
			<div class="control-group">
					<button type="submit" class="btn btn-primary">Save changes?</button>
			</div>
			</form>
		</div>