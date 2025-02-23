<h1>Direct Routing Configuration</h1>

<div class="section">
    <h2>Routes</h2>

    <div class="notes">
        <p>These routes are connected and can be selected on the draft
        creation screen.</p>
    </div>

    <p class="empty">No routes available</p>

    <p class="add"><a href="/cp/server/dr_add_route.php">Add a new route...</a></p>
</div>

<div class="section">
    <h2>Available Hosts</h2>

    <div class="notes">
        <p>These hosts have been configured. Green hosts are ready to
        have routes assigned. Yellow hosts are pending login confirmation
        and should turn green or red soon. Red hosts have connection or
        authentication problems.</p>
    </div>

    <?php if ($template->hosts) { ?>
    <table>
        <tr>
            <th class="address">Address</th>
            <th class="actions"></th>
        </tr>
        <?php foreach ($template->hosts as $host) { ?>
        <tr class="<?php echo dr_host_status_class($host); ?>">
            <td class="address"><?php echo htmlspecialchars($host['address']); ?></td>
            <td class="actions">
                <a href="/cp/server/dr_edit_host.php?id=<?php echo $host['id']; ?>">Edit</a> |
                <a href="/cp/server/dr_delete_host.php?id=<?php echo $host['id']; ?>">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
    <?php } else { ?>
    <p class="empty">No hosts available</p>
    <?php } ?>

    <p class="add"><a href="/cp/server/dr_add_host.php">Add a new host...</a></p>
</div>
