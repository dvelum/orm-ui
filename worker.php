<?php

ini_set('display_errors', 'On');
error_reporting(E_ALL);



//if (php_sapi_name() == "cli-server") {
    // running under built-in server so
    // route static assets and return false
    $extensions = array_flip(['jpg', 'png', 'jpeg', 'gif', 'css', 'js', 'woff', 'woff2', 'ttf']);
    $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
    $ext = pathinfo($path, PATHINFO_EXTENSION);

    if (isset($extensions[$ext])) {
        return false;
    }

    $env = file_get_contents(__DIR__.'/env.json');
    /*
    $env = getenv('DVELUM_ORM_UI_ENV');
    if (empty($env)) {
        echo 'DVELUM_ORM_UI_ENV variable is not set';
        exit();
    }
    */

    $env = json_decode($env, true);

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
    
    $psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();
    $creator = new \Nyholm\Psr7Server\ServerRequestCreator(
        $psr17Factory, // ServerRequestFactory
        $psr17Factory, // UriFactory
        $psr17Factory, // UploadedFileFactory
        $psr17Factory  // StreamFactory
    );
    $serverRequest = $creator->fromGlobals();
    $response = $psr17Factory->createResponse(200);

    //try {
        $response = $server->run($serverRequest, $response , $envParams);
        (new \Laminas\HttpHandlerRunner\Emitter\SapiEmitter())->emit($response);
    //} catch (\Throwable $e) {
        //error_log((string)$e->getMessage(), 0);
        //echo $e->getMessage();
    //}
/*} else {
    echo 'ORM UI can be used only with cli-server ';
    exit();
}*/