<?php
require 'gitpuller_config.php';
$j = [];
$plugin_local_path;

if ($gitpuller_config['GITPULLER_KEY'] && $gitpuller_config['GITPULLER_KEY'] != $_POST['GITPULLER_KEY']) {
    die;
}
if ($_POST['repo']) {
    $repo_name = preg_replace('/[^\/]*\/?([^\/]*)/', '$1', $_POST['repo'], 1);
    foreach (['plugins', 'themes'] as $subdir) {
        $plugin_local_path = 'user/'. $subdir . '/' . $repo_name;
        if (is_dir($plugin_local_path)) {
            break;
        } else {
            $plugin_local_path = null;
        }
    }
}

$descriptorspec = [
    0 => ["pipe", "r"],  // stdin
    1 => ["pipe", "w"],  // stdout
    2 => ["pipe", "w"],  // stderr
];
$process = proc_open(($plugin_local_path ? "cd $plugin_local_path &&" : '').'git pull', $descriptorspec, $pipes, dirname(__FILE__), null);
$stdout = stream_get_contents($pipes[1]);
fclose($pipes[1]);
if ($stdout) {
    $j['result'] = $stdout;
}

$stderr = stream_get_contents($pipes[2]);
fclose($pipes[2]);
if ($stderr) {
    $j['error'] = $stderr;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($j);