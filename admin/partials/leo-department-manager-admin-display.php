<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/gr33k01
 * @since      1.0.0
 *
 * @package    Leo_Department_Manager
 * @subpackage Leo_Department_Manager/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap" ng-app="leoDepartmentManager">	


	<form action="options.php" method="post">
        <?php        	
            settings_fields( $this->plugin_name );
            do_settings_sections( $this->plugin_name );      
        ?>
    </form>
</div>