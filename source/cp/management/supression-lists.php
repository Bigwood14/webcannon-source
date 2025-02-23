<?PHP
require '../../lib/control_panel.php';
require 'suppression_lists.php';

if(isset($_POST['add']))
{
    header("Location: supression-lists-add.php?list-id=".$_POST['sel']);
}

show_suppression_lists();
?>
