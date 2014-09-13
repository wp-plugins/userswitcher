<?php
/**
 * Plugin Name: userSwitcher
 * Description: Experience what you're user experienced and know what they can do to your site without logging in/out.
 * Version: 1.0
 * Author: Irene A. Mitchell
 * License: GPLv2 or later
 **/

if( ! class_exists( 'userSwitcher' ) ):
        class userSwitcher {
                var $current_switcher;
                var $user_switcher;
                
                public function __construct(){
                        add_action( 'init', array($this, 'validate_current_user') );
                        add_action( 'admin_init', array($this, 'admin_init') );
                        add_action( 'admin_bar_menu', array( $this, 'switcher_html' ), 1000 );
                        add_action( ( is_admin() ? 'admin_footer' : 'wp_footer'), array( $this, 'footer'), 1 );
                }
                
                public function admin_init(){
                        register_setting( 'user_switcher', 'user_switcher', array( $this, 'redirect_switcher' ) );
                }
                
                public function redirect_switcher($req){
                        if( preg_match( '%wp-admin%', $_REQUEST['_wp_http_referer']) ){
                                $_REQUEST['_wp_http_referer'] = $_REQUEST['user_switcher'] == 'guest' ? site_url() : admin_url();
                        }
                        return $req;
                }
                
                public function validate_current_user(){
                        global $pagenow, $current_user;
                        $this->current_switcher = get_current_user_id();
                        $switchers = (array) get_option( 'user_switcher' );
                        $this->user_switcher = $switchers[$this->current_switcher];

                        if( !empty($this->user_switcher)  && $this->current_switcher != $this->user_switcher ){
                                add_filter( 'user_has_cap', array( $this, 'set_option_cap' ), 500, 3 );
                                
                                if( intval( $this->user_switcher ) > 0 ){                                        
                                        $user = get_userdata( (int) $this->user_switcher );
                                        $current_user = $user;
                                }
                                else {
                                        $caps = get_role( (string) $this->user_switcher )->capabilities;
                                        $current_user->allcaps = $caps;
                                        $current_user->roles = $current_user->caps = array( $this->user_switcher );
                                }                             
                        }
                        
                }
                
                public function set_option_cap($caps, $cap, $a){
                        global $pagenow;
                        if( $pagenow == 'options.php' && $this->current_switcher != $this->user_switcher )
                                $caps['manage_options']= 1;
                                
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
                        global $wp_admin_bar;
                        
                        $wp_admin_bar->add_menu( array(
                                'id' => 'user-switcher',
                                'title' => '<span class="switcher-icon">userSwitcher</span>'
                        ));
                }
                
                public function footer(){
                        global $wp_admin_bar;
                        if ( ! is_admin_bar_showing() || ! is_object( $wp_admin_bar ) )
                        return;
                
                        $switcher_url = WPMU_PLUGIN_URL . "/userSwitcher";
                ?>
                        <form method="post" action="<?php _e(admin_url()); ?>/options.php" id="switcher-form">
                                <?php
                                echo "<input type='hidden' name='option_page' value='" . esc_attr('user_switcher') . "' />";
                                echo '<input type="hidden" name="action" value="update" />';
                                wp_nonce_field("user_switcher-options");                               
                                ?>
                                        <select name="user_switcher[<?php _e($this->current_switcher); ?>]">
                                                <option value="guest" <?php selected('guest', $this->user_switcher); ?>>Guest</option>
                                                <?php
                                                global $wp_roles;
                                                $roles = array_map(create_function('$a', ' return $a["name"]; ' ), $wp_roles->roles );
                                                
                                                foreach( $roles as $role => $label ): ?>
                                                        <option style="font-weight:bold; font-size:18px;" value="<?php _e($role); ?>" <?php selected($this->user_switcher, $role); ?>><?php _e($label); ?></option>
                                                        <?php
                                                        $users = get_users( array( 'role' => $role ) );
                                                        
                                                        if( !is_wp_error( $users ) && count( $users ) > 0 ):
                                                                foreach( $users as $user ): ?>
                                                                <option value="<?php _e($user->ID); ?>" <?php selected($this->user_switcher, $user->ID); ?>>&rarr; <?php _e($user->display_name); ?></option>
                                                                <?php endforeach; ?>
                                                        <?php endif; ?>
                                                <?php endforeach; ?>
                                        </select>
                        </form>
                        <style>
                                #wp-admin-bar-user-switcher .ab-item {
                                        border: 1px #999 solid;
                                        border-top: 0;
                                        border-bottom:0;
                                        min-width: 20px;
                                        vertical-align: top;
                                }
                                #wp-admin-bar-user-switcher .ab-item .switcher-icon {
                                        display: inline-block;
                                        float: left;
                                        text-indent: 35px;
                                        padding-right: 10px;
                                        margin-left: -10px;
                                        background: url(<?php _e($switcher_url); ?>/arrow_switch_right.png) no-repeat left center;
                                }
                                #wp-admin-bar-user-switcher .switch-open .switcher-icon {
                                        background-image: url(<?php _e($switcher_url); ?>/arrow_switch_right2.png);
                                }
                                #switcher-form {
                                        position: relative;
                                        display: inline-block;
                                        margin: 2px 0;
                                        padding: 0;
                                        height: 25px;
                                        line-height: 16px;
                                        overflow: hidden;
                                        width:0;
                                }
                                #switcher-form select {
                                        margin: -3px 0 0 0;
                                        font-size: 14px;
                                }
                                #switcher-form option {
                                        padding: 0 10px;
                                }
                        </style>
                        <script type="text/javascript">
                        +function($){
                                $(document).on('ready', function(){
                                        var userSwitcher = $('#wp-admin-bar-user-switcher div:eq(0)')
                                        var switcherIcon = $('.switcher-icon', userSwitcher)
                                        var form = $('form#switcher-form').appendTo(userSwitcher)
                                        
                                        switcherIcon.click(function(){
                                                var isOpen = userSwitcher.is('.switch-open')
                                        
                                                form.animate({
                                                        width: isOpen ? 0 : 180
                                                }, 'normal', function(){
                                                        userSwitcher[ isOpen ? 'removeClass' : 'addClass']('switch-open')
                                                        })
                                                
                                        })
                                        form.find('select').change(function(){
                                                form.submit()
                                        })
                                })
                        }(jQuery);
                        </script>
                <?php
                }
        }
        new userSwitcher;
endif;