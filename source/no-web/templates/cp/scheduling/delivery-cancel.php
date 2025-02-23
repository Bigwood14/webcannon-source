<div id="contentbox">

  <h1>Delivery Cancel</h1>
  <p>
  <?php if(@$template->confirmed) {?>
  Delivery has been confirmed for cancelation.
  <?php } else { ?>
  Are you sure you want to cancel this delivey? <a href="/cp/scheduling/delivery-cancel.php?id=<?php echo $_GET['id']?>&confirm=y">YES</a>
  <?php } ?>
  </p>
  </div>
