<?php

if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('awc_cleanup_settings');

$enabled = $settings['enabled'] ?? 0;
$dry_run = $settings['dry_run'] ?? 1;
$timeout = $settings['timeout'] ?? 30;
$limit   = $settings['limit'] ?? 50;

?>

<div class="wrap">

<h1>Amelia WooCommerce Cleanup</h1>

<p>
Automatically cancel abandoned WooCommerce booking orders and release Amelia booking slots.
</p>

<hr>

<form method="post" action="options.php">

<?php settings_fields('awc_cleanup_group'); ?>

<table class="form-table">

<tr>
<th>Enable Cleanup</th>
<td>
<label>
<input type="checkbox"
name="awc_cleanup_settings[enabled]"
value="1"
<?php checked($enabled,1); ?>>

Enable automatic cancellation
</label>
</td>
</tr>

<tr>
<th>Dry Run Mode</th>
<td>
<label>
<input type="checkbox"
name="awc_cleanup_settings[dry_run]"
value="1"
<?php checked($dry_run,1); ?>>

Dry Run (log only)
</label>

<p class="description">
Recommended during testing.
</p>
</td>
</tr>

<tr>
<th>Timeout (minutes)</th>
<td>
<input type="number"
min="1"
name="awc_cleanup_settings[timeout]"
value="<?php echo esc_attr($timeout); ?>">

<p class="description">
Orders older than this will be considered abandoned.
</p>
</td>
</tr>

<tr>
<th>Limit per Run</th>
<td>
<input type="number"
min="1"
name="awc_cleanup_settings[limit]"
value="<?php echo esc_attr($limit); ?>">

<p class="description">
Maximum orders processed per cron execution.
</p>
</td>
</tr>

</table>

<?php submit_button(); ?>

</form>

<hr>

<h2>Dry Run Preview</h2>

<p>
Run a simulation to see which orders would be cancelled.
</p>

<button id="awc-dry-run" class="button button-primary">
Run Dry Run Test
</button>

<div id="awc-dry-results" style="margin-top:20px;"></div>

</div>

<script>

document.addEventListener("DOMContentLoaded", function(){

    const btn = document.getElementById("awc-dry-run");
    const results = document.getElementById("awc-dry-results");

    btn.addEventListener("click", function(){

        results.innerHTML = "<p>Checking abandoned orders...</p>";

        fetch(ajaxurl + "?action=awc_dry_run_preview")
            .then(res => res.json())
            .then(data => {

                if (!data.success) {

                    results.innerHTML =
                        "<p style='color:red;'>Error running test.</p>";

                    return;

                }

                if (!data.data.length) {

                    results.innerHTML =
                        "<p>No abandoned orders detected.</p>";

                    return;

                }

                let html = "<h3>Orders Detected</h3>";

                html += "<table class='widefat striped'>";
                html += "<thead>";
                html += "<tr>";
                html += "<th>Order ID</th>";
                html += "<th>Status</th>";
                html += "<th>Age (minutes)</th>";
                html += "<th>Customer Email</th>";
                html += "<th>Amelia Booking Time</th>";
                html += "</tr>";
                html += "</thead><tbody>";

                data.data.forEach(order => {

                    html += "<tr>";

                    html += "<td>#"+order.id+"</td>";
                    html += "<td>"+order.status+"</td>";
                    html += "<td>"+order.age+"</td>";
                    html += "<td>"+(order.email ?? '')+"</td>";
                    html += "<td>"+(order.booking ?? '')+"</td>";

                    html += "</tr>";

                });

                html += "</tbody></table>";

                results.innerHTML = html;

            })
            .catch(() => {

                results.innerHTML =
                    "<p style='color:red;'>AJAX request failed.</p>";

            });

    });

});
</script>