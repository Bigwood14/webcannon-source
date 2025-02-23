<?PHP
include "include.php";

if($permissions->hasPermission("PAGE_THREE"))
{
    echo "Welcome here";
}
else
{
    echo "Your not welcome";
}
?>