<div class="wrap">
    <h2>Gist as Post</h2>
    <form method="post" action="options.php">
        <?php @settings_fields('gist-as-post-group'); ?>
        <?php @do_settings_fields('gist-as-post-group'); ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="github_username">Github Username</label></th>
                <td><input type="text" name="github_username" id="github_username" value="<?php echo get_option('github_username'); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="allow_cron">Allow Cron?</label></th>
                <td><input type="checkbox" name="allow_cron" id="allow_cron" value="1" <?php print ((string) get_option('allow_cron') === "1") ? 'checked' : ''; ?> /></td>
            </tr>
        </table>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="github_username">Run Import</label></th>
                <td><input type="button" value="Import" onclick="window.location.href = import.php?dest=options-general.php?page=gist-as-post" /></td>
            </tr>
        </table>

        <?php @submit_button(); ?>
    </form>
</div>
