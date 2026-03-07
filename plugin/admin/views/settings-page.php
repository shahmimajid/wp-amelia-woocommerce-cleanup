<?php

$settings = get_option('awc_cleanup_settings');
?>

<div class="wrap">

<h1>Amelia WooCommerce Cleanup</h1>

<form method="post" action="options.php">

<?php settings_fields('awc_cleanup_group'); ?>

<table class="form-table">

<tr>
<th>Enable Cleanup</th>
<td>
<input type="checkbox"
name="awc_cleanup_settings[enabled]"
value="1"
<?php checked($settings['enabled'] ?? 0,1); ?>>
</td>
</tr>

<tr>
<th>Dry Run</th>
<td>
<input type="checkbox"
name="awc_cleanup_settings[dry_run]"
value="1"
<?php checked($settings['dry_run'] ?? 0,1); ?>>
</td>
</tr>

<tr>
<th>Timeout (minutes)</th>
<td>
<input type="number"
name="awc_cleanup_settings[timeout]"
value="<?php echo esc_attr($settings['timeout'] ?? 30); ?>">
</td>
</tr>

</table>

<?php submit_button(); ?>

</form>

</div>