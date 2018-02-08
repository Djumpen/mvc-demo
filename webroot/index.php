<?php

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('ROOT')) {
    define('ROOT', dirname(dirname(__FILE__)));
}

if (!defined('APP_DIR')) {
    define('APP_DIR', ROOT . DS . 'app');
}

// Dev mode
error_reporting(E_ALL);
ini_set('display_errors', 1);

require APP_DIR . '/Config/bootstrap.php';

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);
if(strlen($uri) > 1){
    $uri = preg_replace('/\/$/', '', $uri);
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

try {
    switch ($routeInfo[0]) {

        case FastRoute\Dispatcher::NOT_FOUND:
            throw new \Exception('Not Found', 404);

        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            throw new \Exception('Not Allowed', 405);

        case FastRoute\Dispatcher::FOUND:
            $handler = explode('.' ,$routeInfo[1]);
            $vars = $routeInfo[2];
            $vars['_uri'] = $uri;

            if(empty($handler[0]) || empty($handler[1])) {
                throw new \Exception('Router configuration error');
            }

            $controllerClassName = 'MVCApp\\Controller\\' . $handler[0] . 'Controller';
            $controllerMethod = $handler[1] . 'Action';

            if(!class_exists($controllerClassName)){
                throw new \Exception('Class "' . $controllerClassName . '" not found');
            }

            if(!method_exists($controllerClassName, $controllerMethod)){
                throw new \Exception('Method "' . $controllerMethod . '()" on class "' . $controllerClassName . '" not found');
            }

            $controllerClass = new $controllerClassName($di, $vars, $uri);

            $result = $controllerClass->_getContent();
            if($controllerClass->_isExecute()) {
                $result .= $controllerClass->$controllerMethod();
            }

            echo $result;

            break;
    }

} catch (\Exception $e) {

    $status = $e->getCode() ?: 500;
    http_response_code($status);

    if(
        !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        header('Content-Type: application/json');
        echo json_encode([
            'message' => $e->getMessage()
        ]);
    } else {
        echo $e->getMessage();
    }

}
