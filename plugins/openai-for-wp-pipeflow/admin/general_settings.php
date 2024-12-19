<?php

function get_openai_apikey() {
    return esc_attr(get_option('openai_api_key', ""));
}
function oai_admin_general_settings()
{
    ?>
    <div class="wrap">
        <h2>OpenAI for WP-PipeFlow General Settings</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('oai_options');
            do_settings_sections('oai_options');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">OpenAI API Key</th>
                    <td><input type="text" name="openai_api_key" value="<?php echo get_openai_apikey(); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}