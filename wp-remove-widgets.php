<?php
/**
* Plugin Name: WP Remove Widgets
* Author: TJ Webb
* Author URI: http://webb.tj/
* Version: 1.0
* Description: Remove available widgets from the admin widgets management area
*/
add_action('widgets_init', array('WPRemoveWidgets', 'remove_widgets'), 60);

class WPRemoveWidgets{

    public static function remove_widgets(){
        global $pagenow;
        if($pagenow != 'options-general.php'){
            $removes = get_option('wp_remove_widgets_enabled');
            if(is_array($removes)){
                if(!empty($removes)){
                    foreach($removes as $widget => $type){
                        unregister_widget($widget);
                    }
                }
            }
        }
    }

    public static function settings_enabled_callback() {
        global $wp_registered_widgets;
        $widgets = array();
        if(is_array($wp_registered_widgets)){
            foreach($wp_registered_widgets as $widg){
                if(!empty($widg['callback'])){
                    if(!empty($widg['callback'][0])){
                        $class = get_class($widg['callback'][0]);
                        if(!array_key_exists($class, $widgets)){
                            $widgets[$class] = $widg['callback'][0]->name;
                        }
                    }
                }
            }
        }
        $options = get_option('wp_remove_widgets_enabled');
        foreach($widgets as $widget_class => $widget_title){
            echo '<input name="wp_remove_widgets_enabled['.$widget_class.']" id="wp_remove_widgets_enabled-'.$widget_class.'" type="checkbox" value="1" class="code" ' . checked( 1, $options[$widget_class], false ) . ' /> ' . $widget_title . '<br>';
        }
    }

    public static function settings_enabled_section_callback() {
        echo '<p>Check off widgets to be hidden. Widgets that are checked off will <strong>not</strong> be available in the Widgets menu.</p>';
    }

    public static function settings_enabled_init(){
        add_settings_section('wp_remove_widgets_enabled_setting_section',
            'Disable available widgets',
            array('WPRemoveWidgets', 'settings_enabled_section_callback'),
            'wp_remove_widgets_enabled');

        add_settings_field('wp_remove_widgets_enabled',
            'Disabled Widgets',
            array('WPRemoveWidgets', 'settings_enabled_callback'),
            'wp_remove_widgets_enabled',
            'wp_remove_widgets_enabled_setting_section');

        register_setting('wp_remove_widgets_enabled','wp_remove_widgets_enabled');
    }

    public static function settings_enabled_submenu(){
        add_options_page('Remove Widgets', 'Remove Widgets', 'manage_options', 'wp_remove_widgets_enabled', array('WPRemoveWidgets', 'settings_enabled_page'));
    }

    public static function settings_enabled_page(){
        ?>
        <div class="wrap">
            <h2><?php echo __('Remove Widgets Settings'); ?></h2>
            <form method="post" action="options.php"> 
                <?php if (current_user_can('manage_options')) { ?>
                    <?php settings_fields('wp_remove_widgets_enabled'); ?>
                    <?php do_settings_sections( 'wp_remove_widgets_enabled' ); ?>
                    <?php submit_button(); ?>
                <?php } ?>
            </form>
        </div>
        <?php
    }
}

add_action('admin_init', array('WPRemoveWidgets', 'settings_enabled_init'));
add_action('admin_menu', array('WPRemoveWidgets', 'settings_enabled_submenu'));