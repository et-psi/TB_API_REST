<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
include './config.php';
/*
spl_autoload_register(function ($classname) {
    require ("./classes/" . $classname . ".php");
});
*/


$app = new Slim\App(["settings" => $config]);

//Handle Dependencies
$container = $app->getContainer();

$container['db'] = function ($c) {
   try{
       $db = $c['settings']['db'];
       $options  = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
       $pdo = new PDO("mysql:host=" . $db['servername'] . ";dbname=" . $db['dbname'], $db['username'], $db['password'],$options);
       return $pdo;
   }
   catch(\Exception $ex){
       return $ex->getMessage();
   }
};


$app->get('/roles', function (Request $request, Response $response) {
   try{
       
       $con = $this->db;
       $sql = "SELECT id_role, droits FROM roles";
       $result = null;

       foreach ($con->query($sql) as $row) {
           $result[] = $row;
       }    
    
       if($result){
           return $response->withJson($result,200);
       }else{
           return $response->withJson(array('status' => 'Roles : Not Found'),422);
       }
   }
   catch(\Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
   
});


$app->get('/role/{id}', function ($request,$response) {
   try{
       $id = $request->getAttribute('id');
       $con = $this->db;
       $sql = "SELECT id_role, droits FROM roles WHERE id_role = :id";
       $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
       $values = array(':id' => $id);
       $pre->execute($values);
       $result = $pre->fetch();
       if($result){
           return $response->withJson($result,200);
       }else{
           return $response->withJson(array('status' => 'User Not Found'),422);
       }
      
   }
   catch(\Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
   
});

/*
$app->post('/user', function ($request, $response) {
   
   try{
       $con = $this->db;
       $sql = "INSERT INTO `users`(`username`, `email`,`password`) VALUES (:username,:email,:password)";
       $pre  = $con->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
       $values = array(
       ':username' => $request->getParam('username'),
       ':email' => $request->getParam('email'),
//Using hash for password encryption
       'password' => password_hash($request->getParam('password'),PASSWORD_DEFAULT)
       );
       $result = $pre->execute($values);
       return $response->withJson(array('status' => 'User Created'),200);
       
   }
   catch(\Exception $ex){
       return $response->withJson(array('error' => $ex->getMessage()),422);
   }
   
});

*/
$app->run();