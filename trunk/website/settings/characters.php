<?php
require_once __DIR__.'/../inc/avatar_faces.php';

$char_config = $_loginaccount->GetConfigurationOption('character_config', array('characters' => array(), 'main_character' => null));


$characternames = array();

$query = "
SELECT 
	chr.internal_id,
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
";


// printing table rows
$cache = array();
$name_internal_id_list = array();

foreach (array('gms', 'ems') as $locale) {
	$db = ConnectCharacterDatabase($locale);
	$q = $db->query($query);
	while ($row = $q->fetch_assoc()) {
		$row['locale'] = $locale;
		$row['internal_name'] = $locale.':'.$row['name'];
		$cache[] = $row;
		$characternames[] = $row['internal_name'];
		$name_internal_id_list[$row['internal_name']] = $row['internal_id'];
	}
	$q->free();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['char_shown_option'], $_POST['main_character'])) {
	$char_options = $_POST['char_shown_option'];
	$char_face_options = $_POST['char_face_option'];
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

		foreach ($char_face_options as $charname => $value) {
			if (!in_array($charname, $characternames) || !isset($avatar_faces[$value])) {
				$found = false;
				break;
			}
		}
		
		if (!$found) {
			$error = 'An error occurred. Try again.';
		}
		elseif (!isset($char_options[$main_char])) {
			// lolwat.
			$error = 'An error occurred. Try again.';
		}
		else {
			$char_config['main_character'] = $main_char;
			
			foreach ($char_options as $charname => $value) {
				$char_config['characters'][$charname] = $value;
			}
			foreach ($char_face_options as $charname => $value) {
				$locale = substr($charname, 0, strpos($charname, ':'));
				SetCharacterOption($name_internal_id_list[$charname], 'avatar_face', $locale, $avatar_faces[$value][0]);
			}
			
			$_loginaccount->SetConfigurationOption('character_config', $char_config);
		}
	}
	
	if ($error == '') {
?>
<p class="lead alert-success alert">You successfully updated your characters.</p>
<?php
	}
	else {
?>
<p class="lead alert-error alert"><?php echo $error; ?></p>
<?php
	}
}


?>
			<form class="form-horizontal" method="post">
<?php
$i = 0;
$chars_per_row = 3;
foreach ($cache as $row) {
	if ($char_config['main_character'] == null) {
		$char_config['main_character'] = $row['name'];
	}
	$shown_option_value = isset($char_config['characters'][$row['internal_name']]) ? $char_config['characters'][$row['internal_name']] : 0; // Default = 0
	$shown_face_value = GetCharacterOption($row['internal_id'], 'avatar_face', $row['locale'], 'default');
	$data_domain = $row['locale'].'.'.$domain;
	if ($i % $chars_per_row == 0) {
		if ($i > 0) {
?>
			</div>
<?php
		}
?>
			<div class="row">
<?php
	}
?>

<script>
$(function ()
{ $("#<?php echo $row['name']; ?>").popover({title: 'Additional Options', content: "<a href='#' class='btn btn-danger btn-block'>Delete Character</a>", html: 'true', placement: 'bottom'});
});
</script>

				<div class="span3 character-brick" style="min-width: 174px;">
				<div class="caption"><img src="//<?php echo $data_domain; ?>/inc/img/worlds/<?php echo $row['world_name']; ?>.png" style="vertical-align: middle;" />&nbsp;<?php echo $row['name']; ?></div>
					<center>
						<br />
							<img src="//mapler.me/avatar/<?php echo $row['name']; ?>" id="<?php echo $row['name']; ?>"/>
						<br />
						<br />
						<br />
						This character is shown: 
						<br />
						<select name="char_shown_option[<?php echo $row['internal_name']; ?>]" style="height:35px !important;width: 150px !important;">
							<option value="0"<?php echo $shown_option_value == 0 ? ' selected="selected"' : ''; ?>>Always</option>
							<option value="1"<?php echo $shown_option_value == 1 ? ' selected="selected"' : ''; ?>>Only for friends</option>
							<option value="2"<?php echo $shown_option_value == 2 ? ' selected="selected"' : ''; ?>>Never</option>
						</select>
						<br />
						Using face: 
						<br />
						<select name="char_face_option[<?php echo $row['internal_name']; ?>]" style="height:35px !important;width: 150px !important;">
<?php foreach ($avatar_faces as $facename => $text): ?>
							<option value="<?php echo $facename; ?>"<?php echo $shown_face_value == $facename ? ' selected="selected"' : ''; ?>><?php echo $text; ?></option>
<?php endforeach; ?>
						</select>
						<br />
						<input type="radio" name="main_character" value="<?php echo $row['internal_name']; ?>"<?php echo $char_config['main_character'] == $row['internal_name'] ? ' checked="checked"' : ''; ?> /> Main character
					</center>
				</div>
<?php
	$i++;
}
?>
			</div>
	
			<div class="control-group span2" style="clear:both;">
					<button type="submit" class="btn btn-primary" style="margin-top:20px;">Save changes?</button>
			</div>
			</form>
			
<script type="text/javascript">
$('#expand').click(function(e) {
     // do something fancy
     return false; // prevent default click action from happening!
     e.preventDefault(); // same thing as above
});
</script>