
<?php



function getDB()
{

  $host = host;
  $user = user;
  $pass = pass;
  $db_name = db_name;

    try 
    {
      $db = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass); 
      $db->exec("set names utf8");
      $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      return $db ;
    }
      catch(PDOException $err)
    {


      
      exit;
     
      if (mode_env === 'dev') {
        echo json_encode(['error' => 'Database connection failed: ' . $err->getMessage()]);
    } else {
      error_log($err->getMessage(), 3, '/errorgetDB.log');
      //die('An error has been detected in the database. If you are attempting to tamper with the site or exploit vulnerabilities, you must contact site support and request prior authorization. Failure to do so will result in a full investigation against you, and you may face legal action. We closely monitor activities on the site, and any hacking attempts will be met with strict legal consequences.');
   
    }
 }
} 


?>