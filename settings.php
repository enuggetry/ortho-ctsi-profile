<?php
/*
	Implements settings page in admin menu for configuring plugin default values
	By: EY
*/
if(!class_exists('Ortho_CTSI_Profile_Settings'))
{
	class Ortho_CTSI_Profile_Settings
	{
		/**
		 * Construct the plugin object
		 */
		public function __construct()
		{
		// register actions
                add_action('admin_init', array(&$this, 'admin_init'));
        	add_action('admin_menu', array(&$this, 'add_menu'));
		} // END public function __construct
		
        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
        	// register plugin's settings
        	register_setting('ortho_ctsi_group', 'ortho_ctsi_display_lines');
        	register_setting('ortho_ctsi_group', 'ortho_ctsi_default_priority');

        	// add settings section
        	add_settings_section(
        	    'ortho_ctsi_profile_section', 
        	    'Ortho CTSI Profile Plugin', 
        	    array(&$this, 'settings_section_ortho_ctsi_profile'), 
        	    'ortho_ctsi_profile'
        	);
        	
        	// add setting's fields
                
            add_settings_field(
                'ortho_ctsi_display_lines', 
                'Default publication lines to display (0-99)', 
                array(&$this, 'settings_field_input_text'), 
                'ortho_ctsi_profile', 
                'ortho_ctsi_profile_section',
                array(
                    'field' => 'ortho_ctsi_display_lines'
                )
            );
            
            add_settings_field(
                'ortho_ctsi_default_priority', 
                'Default publication display priority (0-99)', 
                array(&$this, 'settings_field_input_text'), 
                'ortho_ctsi_profile', 
                'ortho_ctsi_profile_section',
                array(
                    'field' => 'ortho_ctsi_default_priority'
                )
            );
            
            
            // Possibly do additional admin_init tasks
        } // END public static function activate
        
        public function settings_section_ortho_ctsi_profile()
        {
            // Think of this as help text for the section.
            echo 'This plugin implements CTSI Profile acquisition allowing admin to select CTSI publications or faculty templates.';
        }
        
        /**
         * This function provides text inputs for settings fields
         */
        public function settings_field_input_text($args)
        {
            // Get the field name from the $args array
            $field = $args['field'];
            // Get the value of this setting
            $value = get_option($field);
            // echo a proper input type="text"
            echo sprintf('<input type="text" name="%s" id="%s" value="%s" size="3" maxlength="2"/>', $field, $field, $value);
        } // END public function settings_field_input_text($args)
        
        /**
         * add a menu
         */		
        public function add_menu()
        {
            // Add a page to manage this plugin's settings
        	add_options_page(
        	    'Ortho CTSI Profile Settings', 
        	    'Ortho CTSI Profile', 
        	    'manage_options', 
        	    'ortho_ctsi_profile', 
        	    array(&$this, 'plugin_settings_page')
        	);
        } // END public function add_menu()
    
        /**
         * Menu Callback
         */		
        public function plugin_settings_page()
        {
        	if(!current_user_can('manage_options'))
        	{
        		wp_die(__('You do not have sufficient permissions to access this page.'));
        	}
	
        	// Render the settings template
        	include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        } // END public function plugin_settings_page()
    } // END class Ortho_Addthis_Settings
} // END if(!class_exists('Ortho_Addthis_Settings'))
