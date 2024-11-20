<?php
/**
 * Plugin Name: OpenAI for WP PipeFlow
 * Plugin URI: https://marcosiino.it
 * Description: OpenAI Stages implementation for WP PipeFlow plugin
 * Version: 1.0
 * Author: Marco Siino
 * Requires Plugins: wp-pipeflow
 * Requires at least: 5.9
 * Author URI: http://marcosiino.it
 */

defined('ABSPATH') or die('Accesso non permesso.');

require_once ABSPATH . "wp-content/plugins/wp-pipeflow/classes/Pipeline/CorePipeFlow.php";

require_once ABSPATH . "wp-content/plugins/openai-for-pipeflow-wp-plugin/classes/Stages/AIImageGeneration/AIImageGenerationStageFactory.php";
require_once ABSPATH . "wp-content/plugins/openai-for-pipeflow-wp-plugin/classes/Stages/AITextCompletion/AITextCompletionStageFactory.php";

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
    //register_setting('openai_for_wp_pipeflow_general_options', 'openai_api_key');
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
}
add_action('admin_menu', 'oai_setup_admin_menu');
