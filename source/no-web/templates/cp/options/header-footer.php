<h1>Header/Footer Config</h1>

<div id="headers" class="section">
<h2>Headers</h2>

<?php if ($template->headers) { ?>
<table>
    <tr>
        <th class="name">Name</th>
        <th class="actions">Actions</th>
    </tr>
    <?php foreach ($template->headers as $header) { ?>
    <?php $default = $header['is_default'] ?>
    <tr<?php if ($default) { echo ' class="default"'; } ?>>
        <td class="name">
            <?php echo htmlspecialchars($header['name']); ?>
        </td>
        <td class="actions">
            <?php if (!$default) { ?><a href="/cp/options/set_default_content.php?id=<?php echo $header['id']; ?>">Set Default</a> |<?php } ?>
            <a href="/cp/options/edit_content.php?id=<?php echo $header['id']; ?>">Edit</a> |
            <a href="/cp/options/delete_content.php?id=<?php echo $header['id']; ?>">Delete</a>
        </td>
    </tr>
    <?php } ?>
</table>
<?php } else { ?>
<p class="empty">No headers found.</p>
<?php } ?>

<p class="add"><a href="/cp/options/add_header.php">Add a new header...</a></p>
</div>


<div id="footers" class="section">
<h2>Footers</h2>

<?php if ($template->footers) { ?>
<table>
    <tr>
        <th class="name">Name</th>
        <th class="actions">Actions</th>
    </tr>
    <?php foreach ($template->footers as $footer) { ?>
    <?php $default = $footer['is_default'] ?>
    <tr<?php if ($default) { echo ' class="default"'; } ?>>
        <td class="name">
            <?php echo htmlspecialchars($footer['name']); ?>
        </td>
        <td class="actions">
            <?php if (!$default) { ?><a href="/cp/options/set_default_content.php?id=<?php echo $footer['id']; ?>">Set Default</a> |<?php } ?>
            <a href="/cp/options/edit_content.php?id=<?php echo $footer['id']; ?>">Edit</a> |
            <a href="/cp/options/delete_content.php?id=<?php echo $footer['id']; ?>">Delete</a>
        </td>
    </tr>
    <?php } ?>
</table>
<?php } else { ?>
<p class="empty">No footers found.</p>
<?php } ?>

<p class="add"><a href="/cp/options/add_footer.php">Add a new footer...</a></p>
</div>
