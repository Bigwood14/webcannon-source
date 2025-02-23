<?PHP
include "include.php";

$auth->login("tom","tom");
$permissions->getGroups($auth->user['user_id']);
echo "Logged in: <br><a href='index1.php'>you can access</a>";
echo "<br><a href='index2.php'>you cannot access</a>";
echo "<br><a href='index3.php'>you can access</a>";
?>