<form action="/cp/options/header-footer.php" method="post">
<div id="contentbox">
  <h1>Schedule</h1>

  <p>
  <?php
  if($template->error == 1)
  {
  ?>
  <span class="error">Error message allready scheduled for delivery</span><br />
  <?php
  }
  ?>
  <?php
  if($template->sent == 1)
  {
  ?>
  <a class="error">Message has been scheduled to send <a href="/cp/scheduling/delivery-queue.php">view delivery queue</a></span><br />
  <?php
  }
  ?>
  
  </p>
  </div>
</form>