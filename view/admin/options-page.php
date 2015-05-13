<div class="wrap">
    <form method="post" action="options.php">
        <?php settings_fields('mf100-options'); ?>
        <?php do_settings_sections('mf100-options-page'); ?>
        <?php submit_button(); ?>
    </form>
</div>
