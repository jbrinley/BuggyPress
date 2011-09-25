=== BuggyPress ===
Contributors: jbrinley
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=A69NZPKWGB6H2
Tags: bug-tracking, issue-tracking, case-management
Requires at least: 3.2
Tested up to: 3.2
Stable tag: 0.4

A simple bug-tracking/issue-tracking/case-management system.

== Description ==

A simple bug-tracking/issue-tracking/case-management system.

Create new issues just as you would a post. Each issue can be assigned a status, priority, and issue type. Issues can be assigned to specific users on the site.

Projects are stored in a separate taxonomy. Issues can be assigned to projects just as you assign posts to categories.

Issues can be update or reassigned through the comment form. 

Created by [Adelie Design](http://www.AdelieDesign.com)

== Installation ==

1. Download and unzip the plugin
1. Upload the `BuggyPress` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Create new issues (the Issues menu is just under the Posts menu)

== Frequently Asked Questions ==

= How do I see a list of issues? =

See all of your issues at http://example.com/?post_type=issue

Issues for a particular project can be found at http://example.com/project/your-project-name/

A specific issue has a URL such as http://example.com/project/your-project-name/issue/your-issue-name/

= My comment form doesn't have options to update issues. =

Your theme must implement the [`comment_form` template tag](http://codex.wordpress.org/Template_Tags/comment_form). Otherwise, the plugin will not be able to modify the comment form to insert the necessary fields.

= How do I attach files to a comment/update? =

BuggyPress doesn't yet support this. You might try another plugin, like [Easy Comment Uploads](http://wordpress.org/extend/plugins/easy-comment-uploads/) to add this functionality.

= Is this compatible with multi-site installs? =

Yes. Each site will have its own projects and its own issues.

= Where can I get support? =
Contact [Adelie Design](http://www.AdelieDesign.com) at [http://www.AdelieDesign.com](http://www.AdelieDesign.com)

== Changelog ==

= 0.1 =
*Initial version

= 0.2 =
*Complete rewrite