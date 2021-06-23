<?php
require __DIR__ . '/../vendor/autoload.php';
require 'initial.php';
// provide aliases for long classname--
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

set_local_error_log(); // redirect error_log to ../php_server_errors.log
// Instantiate the app
$app = new \Slim\App();
// Add middleware that can add CORS headers to response (if uncommented)
// These CORS headers allow any client to use this service (the wildcard star)
// We don't need CORS for the ch05_gs client-server project, because
// its network requests don't come from the browser. Only requests that
// come from the browser need these headers in the response to satisfy
// the browser that all is well. Even in that case, the headers are not
// needed unless the server for the REST requests is different than
// the server for the HTML and JS. When we program in Javascript we do
// send requests from the browser, and then the server may need to
// generate these headers.
// Also specify JSON content-type, and overcome default Allow of GET, PUT
// Note these will be added on failing cases as well as sucessful ones
$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
                    ->withHeader('Access-Control-Allow-Origin', '*')
                    ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                    ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')
                    ->withHeader('Content-Type', 'application/json')
                    ->withHeader('Allow', 'GET, POST, PUT, DELETE');
});
// Turn PHP errors and warnings (div by 0 is a warning!) into exceptions--
// From https://stackoverflow.com/questions/1241728/can-i-try-catch-a-warning
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // error was suppressed with the @-operator--
    // echo 'in error handler...';
    if (0 === error_reporting()) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Slim has default error handling, but not super useful
// so we'll override those handlers so we can handle errors 
// in this code, and report file and line number.
// This also means we don't set $config['displayErrorDetails'] = true;
// because that just affects the default error handler.
// See https://akrabat.com/overriding-slim-3s-error-handling/
// To see this in action, put a parse error in your code
$container = $app->getContainer();
$container['errorHandler'] = function ($container) {
    return function (Request $request, Response $response, $exception) {
        // retrieve logger from $container here and log the error
        $response->getBody()->rewind();
        $errorJSON = '{"error":{"text":' . $exception->getMessage() .
                ', "line":' . $exception->getLine() .
                ', "file":' . $exception->getFile() . '}}';
        //     echo 'error JSON = '. $errorJSON;           
        error_log("server error: $errorJSON");
        return $response->withStatus(500)
                        //            ->withHeader('Content-Type', 'text/html')
                        ->write($errorJSON);
    };
};

// This function should not be called because errors are turned into exceptons
// but it still is, on error 'Call to undefined function' for example
$container['phpErrorHandler'] = function ($container) {
    return function (Request $request, Response $response, $error) {
        // retrieve logger from $container here and log the error
        $response->getBody()->rewind();
        echo 'PHP error:  ';
        print_r($error->getMessage());
        $errorJSON = '{"error":{"text":' . $error->getMessage() .
                ', "line":' . $error->getLine() .
                ', "file":' . $error->getFile() . '}}';
        error_log("server error: $errorJSON");
        return $response->withStatus(500)
                        //  ->withHeader('Content-Type', 'text/html)
                        ->write($errorJSON);
    };
};
$app->get('/day', 'getDay');
$app->post('/day', 'postDay');
$app->get('/toppings', 'getToppings');
$app->get('/toppings/{id}', 'getTopping');
$app->get('/sizes', 'getSizes');
$app->get('/users', 'getUsers');
$app->get('/orders', 'getOrders');
$app->get('/orders/{id}', 'getOrder');
$app->post('/orders', 'addOrder');
$app->put('/orders/{id}', 'updateOrder');

// Take over response to URLs that don't match above rules, to avoid sending
// HTML back in these cases
$app->map(['GET', 'POST', 'PUT', 'DELETE'], '/{routes:.+}', function($req, $res) {
    $uri = $req->getUri();
    $errorJSON = '{"error": "HTTP 404 (URL not found) for URL ' . $uri . '"}';
    return $res->withStatus(404)
                    ->write($errorJSON);
});
$app->run();

// functions without try-catch are depending on overall
// exception handlers set up above, which generate HTTP 500
// Functions that need to generate HTTP 400s (client errors)
// have try-catch
// Function calls that don't throw return HTTP 200
function getDay(Request $request, Response $response) {
    error_log("server getDay");
    $sql = "select current_day FROM pizza_sys_tab";
    $db = getConnection();
    $stmt = $db->query($sql);
    return $stmt->fetch(PDO::FETCH_COLUMN, 0);
}

function postDay(Request $request, Response $response) {
    error_log("server postDay");
    $db = getConnection();
    initial_db($db);
    return "1";  // new day value
}

function getToppings() {
    error_log("server getToppings");
    $sql = "select id, topping FROM menu_toppings";
    $db = getConnection();
    $stmt = $db->query($sql);
    $toppings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($toppings);
}

function getSizes() {
    error_log("server getSizes");
    $sql = "select id, size, diameter FROM menu_sizes";
    $db = getConnection();
    $stmt = $db->query($sql);
    $sizes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($sizes);
}

function getUsers() {
    error_log("server getUsers");
    $sql = "select id, username, room FROM shop_users";
    $db = getConnection();
    $stmt = $db->query($sql);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users);
}

function getOrders() {
    error_log("server getOrders");
    $sql = "SELECT o.id, o.user_id, o.size, o.day, o.status FROM pizza_orders o";
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $orders0 = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $orders = array();
    foreach ($orders0 as $order) {
        $sql2 = "SELECT ot.topping FROM order_topping ot WHERE ot.order_id=:id";
        $stmt = $db->prepare($sql2);
        $stmt->bindParam("id", $order['id']);
        $stmt->execute();
        $toppings = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        $order["toppings"] = $toppings;
        $orders[] = $order;
    }
    echo json_encode($orders);
}

function getTopping(Request $request, Response $response, $args) {
    error_log("server getTopping");
    $id = $args['id'];
    //   echo 'id = ' . $id;
    $sql = "SELECT id, topping FROM menu_toppings t
            WHERE id = :id";
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $topping = $stmt->fetch(PDO::FETCH_ASSOC);
    //  print_r($topping);
    echo json_encode($topping);
}

function getOrder(Request $request, Response $response, $args) {
    error_log("server getOrder");
    $id = $args['id'];
    $sql = "SELECT o.id, o.user_id, o.size, o.day, o.status "
            . "FROM pizza_orders o where o.id = :id";
    $db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->bindParam("id", $id);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($order === FALSE) {
        $errorJSON = '{"error":{"text":"HTTP 404: no such order"}';
        error_log("server error $errorJSON");
        return $response->withStatus(404)  //client error
                        ->write($errorJSON);
    }

    $sql2 = "SELECT ot.topping FROM order_topping ot WHERE ot.order_id=:id";
    $stmt2 = $db->prepare($sql2);
    $stmt2->bindParam("id", $id);
    $stmt2->execute();
    $toppings = $stmt2->fetchAll(PDO::FETCH_COLUMN, 0);
    $order["toppings"] = $toppings;
    echo json_encode($order);
}

function addOrder(Request $request, Response $response) {
    try {
        error_log("server addOrder");
        $db = getConnection();
        $order = $request->getParsedBody();  // Slim does JSON_decode here
        if ($order == NULL) { // parse failed (bad JSON)
            $errorJSON = '{"error":{"text":"bad JSON in request"}';
            error_log("server error $errorJSON");
            return $response->withStatus(400)  //client error
                            ->write($errorJSON);
        } else {
            error_log("inserting");
            $orderId = insertOrder($db, $order);
            $order['id'] = $orderId;  // fix up orderid to current one
            foreach ($order['toppings'] as $topping) {
                insertOrderTopping($db, $orderId, $topping);
            }
            //   echo json_encode($order);
            $location = $request->getUri() . '/' . $orderId;
            return $response->withHeader('Location', $location)
                            ->withStatus(200)
                            ->write(json_encode($order));
        }
    } catch (Exception $e) {
        // if duplicate product, blame client--
        if (strstr($e->getMessage(), 'SQLSTATE[23000]')) {
            $errorJSON = '{"error":{"text":' . $e->getMessage() .
                    ', "line":' . $e->getLine() .
                    ', "file":' . $e->getFile() . '}}';
            error_log("server error $errorJSON");
            return $response->withStatus(400) // client error
                            ->write($errorJSON);
        } else {
            throw($e);  // generate HTTP 500 as usual         
        }
    }
}

// helpers to above--
function insertOrder($db, $order) {
    $sql2 = "INSERT INTO pizza_orders (user_id, size, day, status) VALUES (" .
            " :user_id, :size, :day, :status)";
    $stmt = $db->prepare($sql2);
    $stmt->bindParam("user_id", $order['user_id']);
    $stmt->bindParam("size", $order['size']);
    $stmt->bindParam("day", $order['day']);
    $stmt->bindParam("status", $order['status']);
    $stmt->execute();
    $id = $db->lastInsertId();
    return $id;
}

function insertOrderTopping($db, $orderId, $topping) {
    $sql2 = "INSERT INTO order_topping (order_id, topping) VALUES (" .
            " :order_id, :topping)";
    $stmt = $db->prepare($sql2);
    $stmt->bindParam("order_id", $orderId);
    $stmt->bindParam("topping", $topping);
    $stmt->execute();
}

// See https://stackoverflow.com/questions/2790004 for discussion of
// how to handle the id here if the supplied id is diffeeent
// from the id in the incoming URL
function updateOrder(Request $request, Response $response, $args) {
    error_log("server updateOrder");
    $url_id = $args['id'];
    $db = getConnection();
    $order = $request->getParsedBody();  // Slim does JSON_decode here
    if ($order == NULL) { // parse failed (bad JSON)
        $errorJSON = '{"error":{"text":"bad JSON in request"}';
        error_log("server error: $errorJSON");
        return $response->withStatus(400)  //client error
                        ->write($errorJSON);
    }
    // no id in order is OK, but if it's there it should agree with $url_id
    if (!empty($order['id']) && $order['id'] !== $url_id) {
        $errorJSON = '{"error":{"text":"id in order does not match id in url"}';
        error_log("server error: $errorJSON");
        return $response->withStatus(400)  //client error
                        ->write($errorJSON);
    }
    $order['id'] = $url_id;  // add id if needed
    error_log('server updateOrder' . json_encode($order));
    updateOrderRow($db, $order);
    echo json_encode($order);
}

// Mainly for updating status: assumes toppings stay the same
function updateOrderRow($db, $order) {
    $sql = "UPDATE pizza_orders SET user_id = :user_id, size=:size, day=:day, status=:status WHERE id=:id";
    $stmt = $db->prepare($sql);
    $stmt->bindParam("user_id", $order['user_id']);
    $stmt->bindParam("size", $order["size"]);
    $stmt->bindParam("day", $order['day']);
    $stmt->bindParam("status", $order['status']);
    $stmt->bindParam("id", $order['id']);
    $stmt->execute();
}

// set up to execute on XAMPP or at pe07.cs.umb.edu:
// --set up a mysql user named pizza_user on your own system
// --see database/dev_setup.sql and database/createdb.sql
// --load your mysql database on pe07 with the pizza db
// Then this code figures out which setup to use at runtime
function getConnection() {
    if (gethostname() === 'pe07') {
        $dbuser = 'smit999';  // CHANGE THIS to your cs.umb.edu username
        $dbpass = 'smit999';  // CHANGE THIS to your mysql DB password on pe07 
        $dbname = $dbuser . 'db'; // our convention for mysql dbs on pe07   
    } else {  // dev machine, can create pizzadb
        $dbuser = 'pizza_user';
        $dbpass = 'pa55word';  // or your choice
        $dbname = 'pizzadb';
    }
    $dsn = 'mysql:host=localhost;dbname=' . $dbname;
    $dbh = new PDO($dsn, $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}
