<?php
require_once __DIR__.'/../inc/header.php';

function RunCMD($cmd) {
        $descriptorspec = array(
           0 => array('pipe', 'r'),  // stdin is a pipe that the child will read from
           1 => array('pipe', 'w'),  // stdout is a pipe that the child will write to
           2 => array('file', '/tmp/error-output.txt', "a") // stderr is a file to write to
        );

        $cwd = '/mplrserver';

        $process = proc_open($cmd, $descriptorspec, $pipes, $cwd);

        if (is_resource($process)) {
                $data = stream_get_contents($pipes[1]);
                fclose($pipes[1]);

                $return_value = proc_close($process);
        }
        else {
                $data = "ERROR";
        }

        return $data;
}


$oldestTime = 0;
$name = '';
foreach (glob('/mplrserver/logs/*') as $filename) {
        $t = filectime($filename);
        if ($t > $oldestTime) {
                $oldestTime = $t;
                $name = $filename;
        }
}
?>
<pre>
<?php echo RunCMD('tail '.escapeshellarg($name).' -n 50'); ?>
</pre>
<?php
require_once __DIR__.'/../inc/footer.php';
?>