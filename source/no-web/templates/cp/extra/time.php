<div id="contenttable">
    <div class="section">
        <h2>Set Date/Time</h2>

        <div class="notes">
            <p>Enter the desired date/time in this format:<br>
                MM/DD/YYYY HH:MM</p>

            <p>The time is in 24 hour format. For example, 1:15pm would
                be 13:15.</p>
        </div>

        <form method="POST">
            <input type="text" name="time" value="<?php h($template->current_time); ?>">
            <input type="submit" value="Set">
        </form>
    </div>
</div>

