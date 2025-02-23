<?PHP
include "include.php";

if($permissions->hasPermission("PAGE_TWO"))
{
    echo "Welcome here";
}
else
{
    echo "Your not welcome";
}
?>