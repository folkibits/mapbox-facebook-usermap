<?php 
/* PDO CONNECTION
*
*   code source: http://weebtutorials.com/2012/03/pdo-connection-class-using-singleton-pattern/
*
*/

 
//db connection class using singleton pattern
class dbConn{
 
//variable to hold connection object.
protected static $db;
 
//private construct - class cannot be instatiated externally.
private function __construct() {
 
try {
// assign PDO object to db variable
self::$db = new PDO( 'mysql:host=HOSTNAME;dbname=DBNAME', 'USERNAME', 'PASSWORD' );
self::$db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
self::$db->exec("SET CHARACTER SET utf8");
}
catch (PDOException $e) {
//Output error - would normally log this to error file rather than output to user.
echo "Connection Error: " . $e->getMessage();
}
 
}
 
// get connection function. Static method - accessible without instantiation
public static function getConnection() {
 
//Guarantees single instance, if no connection object exists then create one.
if (!self::$db) {
//new connection object.
new dbConn();
}
 
//return connection.
return self::$db;
}
 
}//end class
 
?>