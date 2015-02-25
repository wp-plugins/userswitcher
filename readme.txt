=== userSwitcher ===
Contributors: irenem
Tags: guest, users, roles, capabilities
Requires at least: 3.4
Tested up to: 4.1
Stable tag: 1.1.2
License: GPLv2 or later

Switch between user accounts to xperience what you're users can and cannot do without logging in and out.

== Description ==
userSwitcher allows you to switch between user accounts without the hassle of logging in/out. It allows you to do, experience and know what you're users can and cannot do.

= Features = 
* Switch between user role or account.
* Switch to "Guest" account without logging out.
* Inherit all capabilities to the selected account.


== Installation ==
1. Upload `userSwitcher` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Click `userSwitcher` menu at the top to select user role or account. You will automatically switched to your selected role or account.

== Frequently Asked Questions ==


= How to use userSwitcher? =
Click the `userSwitcher` menu at the top then select the type of role and account you wish to switch to. You will automatically switched to that account. 
Select `Administrator` to change back to your account.

= Why am I redirected to the front page when selecting guest user? =
Dah. Obviously `GUEST` user do not have access to admin's panel. But no worries, you can still able to go back to admin's panel by selecting a different role or account then navigate back.

= Am I logged out when switching to GUEST user? =
No. Regardless of what type of role or user you switch yourself into, you are still logged in as you. 

= Can I add post while switching? =
That depends on the type of user you switch yourself into. If you switch to say editor then you still able to add post. Take note though that if you choose an account and not a role, you will not be the author of the post but the selected user.

= I switch to guest user, why can I still read private posts? =
Private or password protected posts authored by you are still readable if you switch to `GUEST` or any other user `role`.

== Screenshot ==
1. Switcher menu

== Changelog ==
= 1.0 =
* First release.

= 1.1 =
* Fix switcher UI at admin bar.
* Added switcher UI if admin bar is disabled.

= 1.1.2 = 
* Rename directory url not showing on Linux.
* Added "My Account" option for easy switching back to current account.