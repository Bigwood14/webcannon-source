<?PHP
include "include.php";

if($permissions->hasPermission("PAGE_ONE"))
{
    print_r($profile->getText("EMAIL"));
    print_r($profile->getBigText("DESCRIPTION"));
    echo "Welcome here ".$auth->user['username'];
}
else
{
    echo "Your not welcome";
}
?>