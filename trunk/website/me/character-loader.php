<?php include_once('../inc/header.php'); ?>

<?php
if (!$_loggedin):
?>

<p class="lead alert-error alert">Interact with this mapler by joining Mapler.me!  <a href="#" class="btn pull-right">Apply?</a></p>
	
<?php
else:
?>

<?php
?>

<?php

//Prevents PHP errors from displaying (to not expose the ranking method)
error_reporting(0);
 
$charname = isset($_GET['character']) ? $_GET['character'] : '';
 
$len = strlen($charname);
if ($len < 4 || $len > 12) {
        die("<br/><span class='alert alert-danger'><b>Opps!</b> That character is invalid.</span>");
}
 
function RequestData($rankingpage, $onlyrankdata = true) {
        global $charname;
        $ret = array();
        $data = file_get_contents("http://maplestory.nexon.net/rankings/".$rankingpage."?pageIndex=1&character_name=".$charname."&search=true");
 
 
 
        preg_match_all('/<tr>(.*?)<\/tr>/s', $data, $matches);
 
        $ret = array();
 
        for ($i = 1; $i < sizeof($matches[0]); $i++) {
                $match = $matches[0][$i];
                //echo "----------\r\n";
                //print_r($match);
                preg_match_all('/<td(.*?)>(.*?)<\/td>/s', $match, $columns);
               
               
        //print_r($columns);
               
                $name = trim($columns[2][2]);
               
                if (strtolower($name) != strtolower($charname)) continue;
                $rank = $columns[2][0];
               
                if ($onlyrankdata) {
                        if (strpos($rankingpage, "fame") !== false) {
                                //print_r($columns);
                                //preg_match('/<td class="level-move">(.*?)(?P<amount>\d*)(.*?)<\/div>/s', $columns[2][5], $rankdata);
                                //print_r($rankdata);
                                $ret = array(
                                        "rank" => (int)$rank,
                                        "amount" => (int)trim($columns[2][5]));
 
                        }
                        else {
                                preg_match('/<div class="rank-(?P<change>\w+)">(?P<rank>\d*)(.*?)<\/div>/s', $columns[2][5], $rankdata);
                               
                                $ret = array(
                                        "rank" => (int)$rank,
                                        "move_rank" => isset($rankdata['rank']) ? (int)$rankdata['rank'] : '-',
                                        "move_change" => $rankdata['change']);
                        }
                }
                else {
               
                        preg_match_all('/<img (.*?)src=\'(.*?)\'(.*?)\/>/s', $columns[2][1], $image_data);
                        //print_r($image_data);
                       
                        $image = array();
                        for ($j = 0; $j < sizeof($image_data[2]); $j++) {
                                $url = $image_data[2][$j];
                                $type = substr($url, strpos($url, '/', 10) + 1);
                                $type = substr($type, 0, strpos($type, '/'));
                                $image[$type] = $url;
                        }
                       
                        $world = $columns[2][3];
                       
                        $world = substr($world, strpos($world, 'world ') + 6);
                        $world = substr($world, 0, strpos($world, '"'));
                       
                        $job = $columns[2][4];
                       
                        $job = substr($job, strpos($job, 'title="') + 7);
                        $job = substr($job, 0, strpos($job, '"'));
                       
                       
                        preg_match('/(?P<level>\d+)<br\/>\((?P<exp>\d+)\)/s', trim($columns[2][5]), $leveldata);
                        //print_r($leveldata);
                       
                        preg_match('/<div class="rank-(?P<change>\w+)">(?P<rank>\d*)(.*?)<\/div>/s', $columns[2][5], $rankdata);
                        //print_r($rankdata);
                       
                        $ret = array(
                                "name" => $name,
                                "level" => (int)$leveldata['level'],
                                "job" => $job,
                                "world" => ucfirst($world),
                                "images" => $image);
                        
        $link = mysql_connect("direct.craftnet.nl", "maplestats", "maplederp")
        or die(mysql_error()); mysql_select_db("maplestats", $link);

        $name = $_GET['character'];

		$result = mysql_query("SELECT * FROM characters WHERE `name` = '$name'", $link);
		$row = mysql_fetch_assoc($result);
		
		$gender = $row['gender'];
		
		if (!$gender) {
			$gendertxt = 'Male';
		}
						
						//data display
						echo '
						<div class="row">
						
						<img src=\'' .$url. '\' class=\'pull-left thumbnail\'>';
            echo "<div class='span9'><p class='lead'><b>" .$row['name']. "</b>
            
            <sup>
            	<span class='label label-inverse'>" . (int)$leveldata['level'] . " </span>
            </sup>
            <br/>
            A <span style='text-transform:capitalize;'>" .$job. "</span> from
            
            <span style='text-transform:capitalize;'>" .$world. "</span>…</p></div>
            </div><hr/>
            <div class='row'>
            
            <div class='span3'>
            <div class='well'>
            
            <!-- Stats -->
           
            -insert character stats image here please-
            
            </div>
            </div>
            
            <div class='span9'>
            <div class='well'>
            <p class='lead'>Items</p>
            </div>
            </div>
            
            </div>
            ";
                }
                break;
        }
        return $ret;
}
 
$overall = RequestData("overall-ranking/legendary", false);
if (sizeof($overall) == 0) {
        die("<br/><span class='alert alert-danger'><b>Opps!</b> An error occured while loading the character.</span>");
}
else {   
}
?>

<?php
endif;
?>
      </div>

<?php include_once('../inc/footer.php'); ?>
