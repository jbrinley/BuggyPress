<?php
/*
Plugin Name: BuggyPress
Plugin URI: http://www.adeliedesign.com/
Description: A bug-tracking/issue-tracking/case-management system.
Author: Adelie Design
Author URI: http://www.adeliedesign.com/
Version: 0.3
*/
/*
Copyright (c) 2011 Adelie Design, Inc. http://www.AdelieDesign.com/

Permission is hereby granted, free of charge, to any person obtaining
a copy of this software and associated documentation files (the
"Software"), to deal in the Software without restriction, including
without limitation the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software, and to
permit persons to whom the Software is furnished to do so, subject to
the following conditions:

The above copyright notice and this permission notice shall be included
in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/


/**
 * Load all the plugin files and initialize appropriately
 *
 * @return void
 */
function BuggyPress_load() {
	if ( !class_exists('BuggyPress') ) {
		// load the base class
		require_once 'BuggyPress.class.php';

		if ( BuggyPress::prerequisites_met(phpversion(), get_bloginfo('version')) ) {
			// we can continue. Load all supporting files and hook into wordpress
			require_once 'BuggyPress_File_Loader.php';
			$loader = new BuggyPress_File_Loader();
			$loader->load();
			$loader->initialize();
			$loader->__destruct();
			unset($loader);
		} else {
			// let the user know prerequisites weren't met
			add_action('admin_head', array('BuggyPress', 'failed_to_load_notices'), 0, 0);
		}
	}
}

// Fire it up!
BuggyPress_load();