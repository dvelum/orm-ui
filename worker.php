<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);


//if (php_sapi_name() == "cli-server") {

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
// running under built-in server so
// route static assets and return false
$extensions = array_flip(['jpg', 'png', 'jpeg', 'gif', 'css', 'js', 'woff', 'woff2', 'ttf']);
$ext = pathinfo($path, PATHINFO_EXTENSION);


$env = file_get_contents(__DIR__ . '/env.json');
/*
$env = getenv('DVELUM_ORM_UI_ENV');
if (empty($env)) {
    echo 'DVELUM_ORM_UI_ENV variable is not set';
    exit();
}
*/
$env = json_decode($env, true);

if (strpos($path, '/js/lib/extjs/') === 0 && isset($extensions[$ext])) {
    // proxy ExtJs lib
    if($ext === 'css'){
        header('Content-Type: text/css');
    }else{
        header('Content-Type: ' . mime_content_type($env['dir'] . '/www' . $path));
    }

    echo file_get_contents($env['dir'] . '/www' . $path);
    return true;
}

if (isset($extensions[$ext])) {
    return false;
}

define('DVELUM_ORM_IU_DIR', __DIR__);

$app = include $env['dir'] . '/' . $env['bootstrap'];
$app->init();

$envParams = new \Dvelum\Orm\Ui\EnvParams();
$envParams->setDir($env['dir'])
    ->setBootstrap($env['bootstrap'])
    ->setApplication($app);

/**
 * @todo remove
 */
chdir($env['dir']);

$_SERVER['DVELUM_ORM_UI_ENV'] = $envParams;
$server = new \Dvelum\Orm\Ui\Server();

//try {
    $response = $server->run(new \Dvelum\Request(), new Dvelum\Response\Response(), $envParams);
//} catch (\Throwable $e) {
//error_log((string)$e->getMessage(), 0);
//echo $e->getMessage();
//}
/*} else {
    echo 'ORM UI can be used only with cli-server ';
    exit();
}*/