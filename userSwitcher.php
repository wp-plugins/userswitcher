<?php
/**
 * Plugin Name: userSwitcher
 * Plugin URI: http://irenemitchell.com/userswitcher
 * Description: Experience what you're user experienced and know what they can do to your site without logging in/out.
 * Version: 1.1
 * Author: Irene A. Mitchell
 * Author URI: http://irenemitchell.com
 * License: GPLv2 or later
 **/

if( ! class_exists( 'userSwitcher' ) ):
        class userSwitcher {
                var $current_switcher;
                var $user_switcher;
                var $is_switching = false;
                var $user_data;
                
                public function __construct(){
                        add_action( 'init', array($this, 'validate_current_user'), 1);
                        add_action( 'admin_init', array($this, 'admin_init'), 1);
                        add_action( 'admin_bar_menu', array( $this, 'switcher_html' ), 1000 );                        
                        add_action( ( is_admin() ? 'admin_footer' : 'wp_footer'), array( $this, 'footer'), 1 );
                }
                
                public function admin_init(){
                        register_setting( 'user_switcher', 'user_switcher', array( $this, 'redirect_switcher' ) );
                }
                
                public function redirect_switcher($req){                       
                        if( preg_match( '%wp-admin%', $_REQUEST['_wp_http_referer']) ){                                
                                $_REQUEST['_wp_http_referer'] = in_array( 'guest', (array) $_REQUEST['user_switcher']) ? site_url() : admin_url();
                        }
                        return $req;
                }
                
                public function validate_current_user(){ 
                        global $pagenow, $current_user;
                        $this->current_switcher = get_current_user_id();
                        $switchers = (array) get_option( 'user_switcher' );
                        $this->user_switcher = $switchers[$this->current_switcher];
                        $this->is_switching = is_super_admin();

                        if( !empty($this->user_switcher)  && $this->current_switcher != $this->user_switcher ){
                                add_filter( 'user_has_cap', array( $this, 'set_option_cap' ), 15, 3 );
                                
                                // Get the switcher's original userdata first
                                $this->user_data = get_userdata( (int) $this->current_switcher );
                                
                                if( intval( $this->user_switcher ) > 0 ){                                        
                                        $user = get_userdata( (int) $this->user_switcher );
                                        $current_user = $user;                                        
                                }
                                else {
                                        $caps = get_role( (string) $this->user_switcher )->capabilities;                                     
                                        $current_user->allcaps = $caps;
                                        $current_user->allcaps[ (string) $this->user_switcher ] = 1;                                        
                                        $current_user->roles = $current_user->caps = array( $this->user_switcher );
                                }
                                
                                if( $pagenow == 'options.php' && isset($_REQUEST['user_switcher']) ) {
                                        $current_user = $this->user_data;
                                }
                        }
                }
                
                public function set_option_cap($caps, $cap, $a){
                        global $pagenow, $current_user;
                        
                        if( $pagenow == 'options.php' && $this->is_switching ){
                                $caps['manage_options']= 1;
                        }
                                
                        if( !is_admin() ){
                                switch( $this->user_switcher ){
                                        case 'subscriber':
                                                $caps = array('read' => 1);
                                                break;
                                }
                        }
                        return $caps;
                }
                
                public function switcher_html(){
                        $current = get_userdata( (int) $this->current_switcher );
                        if( in_array( 'administrator', $current->roles ) ) {
                        
                                global $wp_admin_bar;
                                
                                $wp_admin_bar->add_menu( array(
                                        'id' => 'user-switcher',
                                        'title' => '<span class="switcher-icon">userSwitcher</span>'
                                ));
                        }
                }
                
                public function footer(){                        
                        global $wp_admin_bar, $current_user, $wp_roles;
                        
                        if( $this->is_switching ){        
                                $switcher_url = WP_PLUGIN_URL . "/userSwitcher";
                                wp_enqueue_style( 'switch-css', $switcher_url . '/style.css' );
                                wp_enqueue_script( 'switch-js', $switcher_url . '/switch.js' );
                                $old_userdata = $current_user;
                                $current_user = $this->user_data;
                ?>
                        <script type="text/template" id="switcher-template">
                        <form method="post" action="<?php _e(admin_url()); ?>options.php" class="switch-bg" id="switcher-form">
                                <input type="hidden" name="option_page" value="<?php _e( esc_attr( 'user_switcher' ) ); ?>" />
                                <input type="hidden" name="action" value="update" />
                                <?php wp_nonce_field("user_switcher-options");?>
                                        <span class="switch-icon">userSwitcher</span>
                                        <select tabindex="1" name="user_switcher[<?php _e($this->current_switcher); ?>]">
                                                <option value="guest" <?php selected('guest', $this->user_switcher); ?>>Guest</option>
                                                <?php
                                                global $wp_roles;
                                                $roles = array_map(create_function('$a', ' return $a["name"]; ' ), $wp_roles->roles );
                                                
                                                foreach( $roles as $role => $label ): ?>
                                                        <option value="<?php _e($role); ?>" <?php selected($this->user_switcher, $role); ?>><?php _e($label); ?></option>
                                                        <?php
                                                        $users = get_users( array( 'role' => $role, 'exclude' => $this->current_switcher ) );
                                                        
                                                        if( !is_wp_error( $users ) && count( $users ) > 0 ):
                                                                foreach( $users as $user ): ?>
                                                                <option value="<?php _e($user->ID); ?>" <?php selected($this->user_switcher, $user->ID); ?>>&rarr; <?php _e($user->display_name); ?></option>
                                                                <?php endforeach; ?>
                                                        <?php endif; ?>
                                                <?php endforeach; ?>
                                        </select>
                        </form>
                        </script>
                <?php
                                $current_user = $old_userdata;
                        }
                }
        }
        new userSwitcher;
endif;