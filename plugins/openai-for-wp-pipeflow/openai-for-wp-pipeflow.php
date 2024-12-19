<?php
/**
 * Plugin Name: WP-PipeFlow OpenAI Stages
 * Plugin URI: https://marcosiino.it
 * Description: OpenAI integration for WP PipeFlow plugin which provides stages to requests image generations to DALL-E and text completions to ChatGPT.
 * Version: 1.0.0
 * Author: Marco Siino
 * Requires Plugins: wp-pipeflow
 * Requires at least: 5.9
 * Author URI: http://marcosiino.it
 * License: GPL v3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */

defined('ABSPATH') or die('Accesso non permesso.');

//Core PipeFlow
require_once ABSPATH . "wp-content/plugins/wp-pipeflow/classes/Pipeline/CorePipeFlow.php";

//Stages
require_once ABSPATH . "wp-content/plugins/openai-for-pipeflow-wp-plugin/classes/Stages/AIImageGeneration/AIImageGenerationStageFactory.php";
require_once ABSPATH . "wp-content/plugins/openai-for-pipeflow-wp-plugin/classes/Stages/AITextCompletion/AITextCompletionStageFactory.php";

//Admin pages
require_once ABSPATH . "wp-content/plugins/openai-for-pipeflow-wp-plugin/admin/general_settings.php";

/**
 * Plugin activation
 */
function oai_activation() {
}
register_activation_hook(__FILE__, 'oai_activation');

/**
 * Plugin deactivation
 */
function oai_deactivation() {
}
register_deactivation_hook(__FILE__, 'oai_deactivation');

/**
 * Plugin initialization
 */
function oai_init() {
    // Initialize your plugin here
}
add_action('init', 'oai_init');


/**
 * Register the plugin settings
 */
function oai_register_plugin_settings() {
    register_setting('oai_options', 'openai_api_key');
}
add_action('admin_init', 'oai_register_plugin_settings');

/**
 * Registers all the provided stages to WP PipeFlow
 */
function oai_register_pipeline_stages() {
    StageFactory::registerFactory(new AIImageGenerationStageFactory());
    StageFactory::registerFactory(new AITextCompletionStageFactory());
}
add_action('plugins_loaded', 'oai_register_pipeline_stages');

/**
 * Setups the plugin admin menu
 */
function oai_setup_admin_menu() {
    add_menu_page(
        'General Settings', // Page Title
        'OpenAI for WP PipeFlow', // Menu Title
        'manage_options', // Capability
        'openai-for-wp-pipeflow',
        'oai_admin_general_settings',
        'dashicons-admin-customizer', // Menu Icon
        6 //Position
    );
}
add_action('admin_menu', 'oai_setup_admin_menu');
