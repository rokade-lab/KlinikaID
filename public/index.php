<?php

declare(strict_types=1);

use KlinikaID\Http\AppContext;
use KlinikaID\Http\Kernel;
use KlinikaID\Http\Request;
use KlinikaID\Http\Router;

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

$request = Request::fromGlobals();
$app = new AppContext($root, $request);
$kernel = new Kernel(Router::fromFile($root . '/config/routes.php'), $app);
$response = $kernel->handle($request);
$response->send($request->method !== 'HEAD');
