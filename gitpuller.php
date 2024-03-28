<?php
require 'gitpuller_config.php';
header('Content-Type: application/json; charset=utf-8');
$j = ['code'=>200];
$plugin_local_path;

if (isset($gitpuller_config['DEBUG']) && $gitpuller_config['DEBUG']) {
    $j['debug']['POST'] = $_POST;
    $j['debug']['REQUEST'] = $_REQUEST;
    $j['debug']['CONTENT_LENGTH'] = $_SERVER['CONTENT_LENGTH'];
    $j['debug']['CONTENT_TYPE'] = $_SERVER['CONTENT_TYPE'];
    $j['debug']['REQUEST_METHOD'] = $_SERVER['REQUEST_METHOD'];
}

if (isset($gitpuller_config['GITPULLER_KEY']) && ($gitpuller_config['GITPULLER_KEY'] != $_POST['GITPULLER_KEY'])) {
    $j['error'] = 'Access denied!';
    $j['code'] = 304;
    echo json_encode($j);
    die;
}
if (isset($_POST['repo'])) {
    $repo_name = preg_replace('/[^\/]*\/?([^\/]*)/', '$1', $_POST['repo'], 1);
    foreach (['plugins', 'themes'] as $subdir) {
        $plugin_local_path = 'user/'. $subdir . '/' . $repo_name;
        if (is_dir($plugin_local_path)) {
            break;
        }
    }
    if (!isset($plugin_local_path)) {
        $j['error'] = 'repo "'. $_POST['repo'] .'" not installed';
        $j['code'] = 404;
        echo json_encode($j);
        die;
    }
}

$descriptorspec = [
    0 => ["pipe", "r"],  // stdin
    1 => ["pipe", "w"],  // stdout
    2 => ["pipe", "w"],  // stderr
];
$process = proc_open((isset($plugin_local_path) ? "cd $plugin_local_path &&" : '').'git pull', $descriptorspec, $pipes, dirname(__FILE__), null);
$stdout = stream_get_contents($pipes[1]);
fclose($pipes[1]);
if ($stdout) {
    $j['result'] = $stdout;
}

$stderr = stream_get_contents($pipes[2]);
fclose($pipes[2]);
if ($stderr) {
    $j['error'] = $stderr;
    if (preg_match('/^(error|fatal):/',$stderr)) {
        $j['code'] = 500;
    }
}

echo json_encode($j);