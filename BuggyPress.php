<?php
/*
Plugin Name: BuggyPress
Plugin URI: http://www.adeliedesign.com/
Description: A bug-tracking/issue-tracking/case-management system.
Author: Adelie Design
Author URI: http://www.adeliedesign.com/
Version: 0.1
*/
/*
Copyright (c) 2010 Adelie Design, Inc. http://www.AdelieDesign.com/

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
  

if ( !function_exists('adelie_debug') ) {
  function adelie_debug($var) {
    print '<pre class="debug" style="text-align: left; font-size: 12px;">';
    print htmlspecialchars(print_r($var, TRUE));
    print "</pre>";
  }
}

class BuggyPress {
  public $config;
  public function __construct() {
    $this->config = new BuggyPressConfig($this);
    add_action('save_post', array(&$this, 'save_meta_box'), 1);
    add_filter('the_content', array(&$this, 'show_issue_details'), 1);
    add_filter('comment_form_defaults', array(&$this, 'comment_form_defaults'), 1);
    add_action('comment_post', array(&$this, 'comment_post'), 1);
    add_action('pre_comment_on_post', array(&$this, 'pre_comment_on_post'), 1);
    add_action('wp_head', array(&$this, 'print_css'), 10);
    add_action('admin_head', array(&$this, 'admin_css'), 10);
  }
  
  public function print_css() {
    $url = WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__), '', plugin_basename(__FILE__));
    $html = '<link rel="stylesheet" type="text/css" href="'.$url.'BuggyPress.css" />';
    print $html;
  }
  
  public function admin_css() {
    $url = WP_PLUGIN_URL.'/'.str_replace(basename(__FILE__), '', plugin_basename(__FILE__));
    $html = '<link rel="stylesheet" type="text/css" href="'.$url.'BuggyPress.admin.css" />';
    print $html;
  }
    
  public function register_meta_boxes() {
    add_meta_box( 'issue-meta-box', 'Issue Details', array(&$this,'meta_box'), 'issue', 'normal', 'high' );    
  }
  
  public function meta_box() {
    global $post;
    $out = '<input type="hidden" name="issue_noncename" id="issue_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';
    $out .= $this->taxonomy_select_box($post->ID, 'issue_status', 'Status');
    $out .= $this->taxonomy_select_box($post->ID, 'issue_priority', 'Priority');
    $out .= $this->taxonomy_select_box($post->ID, 'issue_type', 'Type');
    $out .= $this->assignment_select_box($post->ID, 'Assigned To');
    print $out;
  }
  
  private function assignment_select_box( $post_id, $name = '' ) {
    $post = get_post($post_id);
    $out .= '<div style="clear: left;">';
    $out .=   '<label for="issue_assign">'.$name.':</label> ';
    $out .=   '<select name="issue_assign" id="issue_assign">';
    $current = get_post_meta($post->ID, '_issue_assign', TRUE);
    $selected = $current?'':' selected="selected"';
    $out .=   '<option value="0"'.$selected.'>Unassigned</option>';
    $users = get_users_of_blog();
    foreach ( $users as $user ) {
      $selected = ($current == $user->ID)?' selected="selected"':'';
      $out .=   '<option value="'.$user->ID.'"'.$selected.'>'.$user->display_name.'</option>';
    }
    $out .=   '</select>';
    $out .= '</div>';
    return $out;
  }
  
  private function taxonomy_select_box( $post_id, $taxonomy, $name = '' ) {
    $out = '<div class="taxonomy-select">';
    $out .=   '<label for="'.$taxonomy.'" style="display: block;">'.$name.':</label> ';
    $out .=   '<select name="'.$taxonomy.'" id="'.$taxonomy.'">';
    $terms = get_terms($taxonomy, array('hierarchical'=>FALSE, 'hide_empty'=>FALSE, 'orderby'=>'id'));
    $current = wp_get_object_terms($post_id, $taxonomy);
    foreach ( $terms as $term ) {
      $selected = '';
      if ( isset($current[0]) && $term->term_id == $current[0]->term_id ) {
        $selected = ' selected="selected"';
      }
      $out .= '<option value="'.$term->term_id.'"'.$selected.'>'.$term->name.'</option>';
    }
    $out .=   '</select>';
    $out .= '</div>';
    return $out;
  }
  
  public function save_meta_box( $post_id ) {
    if ( !wp_verify_nonce( $_POST['issue_noncename'], plugin_basename(__FILE__) )) {  
      return $post_id;
    }
    if ( 'issue' != $_POST['post_type'] ) {
      return $post_id;
    }
    if ( 'issue' != $_POST['post_type'] || !current_user_can('edit_post', $post_id) ) {
      return $post_id;
    }
    $p = get_post($post_id);
    if ( $pid = wp_is_post_revision($p) ) {
      $p = get_post($pid);
    }
    if ( isset($_POST['issue_type']) ) {
      wp_set_object_terms($p->ID, (int)$_POST['issue_type'], 'issue_type');
    }
    if ( isset($_POST['issue_status']) ) {
      wp_set_object_terms($p->ID, (int)$_POST['issue_status'], 'issue_status');
    }
    if ( isset($_POST['issue_priority']) ) {
      wp_set_object_terms($p->ID, (int)$_POST['issue_priority'], 'issue_priority');
    }
    if ( isset($_POST['issue_assign']) ) {
      update_post_meta($p->ID, '_issue_assign', $_POST['issue_assign']);
    }
    
  }
  
  /**
   * Filter: the_content
   * Adds issue details to the beginning of the_content for issues
   * @param string $content
   * @return string The revised $content
   */
  public function show_issue_details( $content = '' ) {
    global $post;
    if ( $post->post_type != 'issue' ) {
      return $content;
    }
    $type = reset(wp_get_object_terms($post->ID, 'issue_type'));
    $status = reset(wp_get_object_terms($post->ID, 'issue_status'));
    $priority = reset(wp_get_object_terms($post->ID, 'issue_priority'));
    //$resolution = reset(wp_get_object_terms($post->ID, 'issue_resolution'));
    $project = reset(wp_get_object_terms($post->ID, 'issue_project'));
    $header = '<div class="buggy issue-details">';
    $header .= '<p class="issue-project"><span class="label">Project:</span> ';
    $header .= '<a href="'.get_bloginfo('url').'/project/'.$project->slug.'/" class="value '.$project->slug.'" title="'.$project->description.'">'.$project->name.'</a></p>';
    $header .= '<p class="issue-type"><span class="label">Type:</span> ';
    $header .= '<span class="value '.$type->slug.'" title="'.$type->description.'">'.$type->name.'</span></p>';
    $header .= '<p class="issue-status"><span class="label">Status:</span> ';
    $header .= '<span class="value '.$status->slug.'" title="'.$status->description.'">'.$status->name.'</span></p>';
    $header .= '<p class="issue-priority"><span class="label">Priority:</span> ';
    $header .= '<span class="value '.$priority->slug.'" title="'.$priority->description.'">'.$priority->name.'</span></p>';
    //$header .= '<p class="issue-resolution"><span class="label">Resolution:</span> ';
    //$header .= '<span class="value '.$resolution->slug.'" title="'.$resolution->description.'">'.$resolution->name.'</span></p>';
    $header .= '</div>';
    return $header.$content;
  }
  
  public function comment_form_defaults( $defaults = array() ) {
    global $post;
    if ( $post->post_type = 'issue' && current_user_can('edit_issues') ) {
      $update_fields = '<div class="issue-update">';
      $update_fields .= $this->taxonomy_select_box($post->ID, 'issue_type', 'Type');
      $update_fields .= $this->taxonomy_select_box($post->ID, 'issue_priority', 'Priority');
      $update_fields .= $this->taxonomy_select_box($post->ID, 'issue_status', 'Status');
      $update_fields .= $this->assignment_select_box($post->ID, 'Assigned To');
      $update_fields .= '</div>';
      $defaults['title_reply'] = 'Update Issue';
      $defaults['label_submit'] = 'Update';
      $defaults['comment_field'] = $update_fields.$defaults['comment_field'];
    }
    return $defaults;
  }
  
  /**
   * @param int $comment_ID
   * @param mixed (int|string) $approved 1 for approved, 0 for not approved, 'spam' for spam
   * @return void
   */
  public function comment_post( $comment_ID, $approved = 1 ) {
    if ( $approved != 1 ) {
      return;
    }
    $comment = get_comment($comment_ID);
    if ( !is_object($comment) ) {
      return;
    }
    $post = get_post($comment->comment_post_ID);
    if ( !is_object($post) || $post->post_type != 'issue' ) {
      return;
    }
    if ( isset($_POST['issue_type']) ) {
      wp_set_object_terms($post->ID, (int)$_POST['issue_type'], 'issue_type');
    }
    if ( isset($_POST['issue_status']) ) {
      wp_set_object_terms($post->ID, (int)$_POST['issue_status'], 'issue_status');
    }
    if ( isset($_POST['issue_priority']) ) {
      wp_set_object_terms($post->ID, (int)$_POST['issue_priority'], 'issue_priority');
    }
    if ( isset($_POST['issue_assign']) ) {
      update_post_meta($post->ID, '_issue_assign', $_POST['issue_assign']);
    }
  }
  
  public function pre_comment_on_post( $post_id ) {
    $post = get_post($post_id);
    if ( !is_object($post) || $post->post_type != 'issue' ) {
      return;
    }
    $changes = array();
    $changes[] = $this->comment_on_term_change($post, 'issue_type', 'Type');
    $changes[] = $this->comment_on_term_change($post, 'issue_status', 'Status');
    $changes[] = $this->comment_on_term_change($post, 'issue_priority', 'Priority');
    $changes[] = $this->comment_on_assignee_change($post, 'Assigned');
    $changes = array_filter($changes);
    if ( !$changes ) {
      return; // nothing to do
    }
    $extra = '<ul class="buggy issue-updates">';
    foreach ( $changes as $change ) {
      $extra .= '<li>'.$change.'</li>';
    }
    $extra .= '</ul>';
    $_POST['comment'] .= $extra;
  }
  
  private function comment_on_term_change( $post, $taxonomy, $label ) {
    if ( isset($_POST[$taxonomy]) ) {
      $term = reset(wp_get_object_terms($post->ID, $taxonomy));
      if ( $term->term_id != $_POST[$taxonomy] ) {
        $new_term = get_term($_POST[$taxonomy], $taxonomy);
        if ( $new_term ) {
          return $label.' changed from <em>'.$term->name.'</em> to <em>'.$new_term->name.'</em>.';
        } else {
          unset($_POST[$taxonomy]); // not a valid value, so don't change it
        }
      }
    }
    return FALSE;
  }
  private function comment_on_assignee_change( $post, $label ) {
    if ( isset($_POST['issue_assign']) ) {
      $current = get_post_meta($post->ID, '_issue_assign', TRUE);
      if ( $current != $_POST['issue_assign'] ) {
        $new_user = get_userdata($_POST['issue_assign']);
        if ( $new_user ) {
          return $label.' to <em>'.$new_user->display_name.'</em>.';
        } elseif ( $_POST['issue_assign'] == 0 ) {
          return $label.' to <em>Unassigned</em>.';
        } else {
          unset($_POST['issue_assign']);
        }
      }
    }
    return FALSE;
  }
}

class BuggyPressConfig {
  private $parent;
  public $updated_message; // a message to display when something has been updated
  public function __construct( $parent ) {
    $this->parent = $parent;
    add_action('init', array(&$this, 'register_taxonomies'), 1);
    add_action('init', array(&$this, 'register_post_type'), 1);
    add_action('admin_menu', array(&$this,'register_admin_menu'), 0);
    add_action('load-settings_page_buggypress_options', array(&$this, 'save_settings_page'), 1);
    
    add_filter('post_type_link', array(&$this, 'post_type_link'), 1, 3);
    
    add_action('wpmu_new_blog', array(&$this, 'wpmu_new_blog'), 1, 5);
  }
  
  public function activate() {
    $this->register_taxonomies(); // init isn't called before activation
    if ( is_multisite() ) {
      $list = get_blog_list(0, 'all');
      foreach ( $list as $blog ) {
        switch_to_blog($blog['blog_id']);
        $this->initialize_terms();
        $this->register_capabilities();
        restore_current_blog();
      }
    } else {
      $this->initialize_terms();
      $this->register_capabilities();
    }
  }
  
  public function wpmu_new_blog( $blog_id, $user_id, $domain, $path, $site_id ) {
    switch_to_blog($blog_id);
    $this->initialize_terms();
    $this->register_capabilities();
    restore_current_blog();
  }
  
  private function initialize_terms() {
    // insert default taxonomy terms
    // issue_type taxonomy
    $this->initialize_term('Bug', 'bug', 'A bug that needs to be fixed', 'issue_type');
    $this->initialize_term('Feature', 'feature', 'A new feature to add', 'issue_type');
    $this->initialize_term('Task', 'task', 'A general task to complete', 'issue_type');
    
    // issue_priority taxonomy
    $this->initialize_term('Critical', 'critical', 'Must be fixed ASAP', 'issue_priority');
    $this->initialize_term('High', 'high', 'High priority', 'issue_priority');
    $this->initialize_term('Medium', 'medium', 'Medium priority', 'issue_priority');
    $this->initialize_term('Low', 'low', 'Low priority', 'issue_priority');
    
    // issue_status taxonomy
    $this->initialize_term('Open', 'open', 'Not yet complete', 'issue_status');
    $this->initialize_term('Resolved', 'resolved', 'Completed, but not yet verified', 'issue_status');
    $this->initialize_term('Closed', 'closed', 'Completed and verified', 'issue_status');
    $this->initialize_term('Deferred', 'deferred', 'Action may be taken in the future', 'issue_status');
    
    // issue_resolution taxonomy
    $this->initialize_term('Fixed', 'fixed', 'All necessary action has been taken', 'issue_resolution');
    $this->initialize_term("Will Not Fix", 'wont-fix', 'A decision has been made to leave it as-is', 'issue_resolution');
    $this->initialize_term('Duplicate', 'duplicate', 'This duplicates another issue', 'issue_resolution');
    $this->initialize_term('Cannot Reproduce', 'cant-reproduce', 'The bug cannot be reproduced', 'issue_resolution');
  }
  
  private function initialize_term( $name, $slug, $description = '', $taxonomy = 'issue_type' ) {
    if ( !is_term($slug, $taxonomy) ) {
      wp_insert_term($name, $taxonomy, array(
        'description' => $description,
        'slug' => $slug,
      ));
    }
  }
  
  public function deactivate() {
    $this->register_taxonomies(); // init isn't called before deactivation
    if ( is_multisite() ) {
      $list = get_blog_list(0, 'all');
      foreach ( $list as $blog ) {
        switch_to_blog($blog['blog_id']);
        $this->delete_terms();
        restore_current_blog();
      }
    } else {
      $this->delete_terms();
    }
  }
  
  private function delete_terms() {
    $terms = get_terms(array('issue_type', 'issue_status', 'issue_resolution', 'issue_priority'), array('hierarchical'=>FALSE, 'hide_empty'=>FALSE));
    foreach ( $terms as $term ) {
      wp_delete_term($term->term_id, $term->taxonomy);
    }
  }
  
  public function register_post_type() {
    register_post_type( 'issue', array(
      'label' => 'Issues',
      'singular_label' => 'Issue',
      'description' => 'A bug to fix, task to complete, or anything else that need to be done',
      'public' => TRUE,
      'publicly_queryable' => TRUE,
      'show_ui' => TRUE,
      'query_var' => TRUE,
      'rewrite' => TRUE,
      'capability_type' => 'issue',
      'hierarchical' => FALSE,
      'menu_position' => NULL,
      'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields', 'revisions'),
      'register_meta_box_cb' => array(&$this->parent, 'register_meta_boxes'),
      'taxonomies' => array('issue_project', 'issue_priority', 'issue_status', 'issue_resolution', 'issue_type', 'post_tag'),
      'menu_position' => 5,
      'rewrite' => array(
        'slug' => 'project/%issue_project%/issue',
        'with_front' => FALSE,
      ),
    ));
    //add_rewrite_rule('project/([^/]+)/issue/([^/]+)/?', '');
    
  }
  
  public function register_taxonomies() {
    register_taxonomy('issue_project', 'issue', array(
      'label' => 'Projects',
      'singular_label' => 'Project',
      'public' => TRUE,
      'show_tagcloud' => FALSE,
      'hierarchical' => TRUE,
      'query_var' => TRUE,
      'rewrite' => array(
        'slug' => 'project'
      ),
    ));
    register_taxonomy('issue_type', 'issue', array(
      'label' => 'Types',
      'singular_label' => 'Type',
      'public' => FALSE,
      'show_ui' => FALSE,
      'show_tagcloud' => FALSE,
      'hierarchical' => FALSE,
      'query_var' => TRUE,
    ));
    register_taxonomy('issue_status', 'issue', array(
      'label' => 'Statuses',
      'singular_label' => 'Status',
      'public' => FALSE,
      'show_ui' => FALSE,
      'show_tagcloud' => FALSE,
      'hierarchical' => FALSE,
      'query_var' => TRUE,
    ));
    register_taxonomy('issue_priority', 'issue', array(
      'label' => 'Priorities',
      'singular_label' => 'Priority',
      'public' => FALSE,
      'show_ui' => FALSE,
      'show_tagcloud' => FALSE,
      'hierarchical' => FALSE,
      'query_var' => TRUE,
    ));
    register_taxonomy('issue_resolution', 'issue', array(
      'label' => 'Resolutions',
      'singular_label' => 'Resolution',
      'public' => FALSE,
      'show_ui' => FALSE,
      'show_tagcloud' => FALSE,
      'hierarchical' => FALSE,
      'query_var' => TRUE,
    ));
  }
  
  private function register_capabilities() {
    $values = $this->default_capabilities();
    $role_objs = array();
    foreach ( $values as $cap => $roles ) {
      foreach ( $roles as $role ) {
        if ( !isset($role_objs[$role]) ) {
          $role_objs[$role] = get_role($role);
        }
        if ( !is_object($role_objs[$role]) ) {
          continue; // not a valid role name
        }
        $role_objs[$role]->add_cap($cap);
      }
    }
  }
  
  private function default_capabilities() {
    global $wp_roles;
    $roles = $wp_roles->get_names();
    $roles = array_keys($roles);
    return array(
      'edit_issue' => $roles,
      'edit_issues' => $roles,
      'edit_others_issues' => $roles,
      'publish_issues' => $roles,
      'read_issues' => $roles,
      'read_private_issues' => $roles,
      'delete_issue' => $roles,
    );
  }
  
  public function register_admin_menu() {
    if ( function_exists('add_submenu_page') ) { // additional pages under E-Commerce
      $page = add_submenu_page( 'options-general.php', 'BuggyPress Settings', 'BuggyPress', 'manage_options', 'buggypress_options', array(&$this, 'settings_page') );
    }
  }
  
  public function settings_page() {
    global $wp_roles;
    $role_names = $wp_roles->get_names();
    $roles = array();
    foreach ( $role_names as $key=>$value ) {
      $roles[$key] = get_role($key);
      $roles[$key]->display_name = $value;
    }
    include('BuggyPress.admin.php');
  }
  
  public function save_settings_page() {
    if ( $_POST['_wpnonce'] && check_admin_referer('buggypress_settings') && current_user_can('manage_options') ) {
      global $wp_roles;
      $role_names = $wp_roles->get_names();
      $roles = array();
      foreach ( $role_names as $key=>$value ) {
        $roles[$key] = get_role($key);
        $roles[$key]->display_name = $value;
      }
      foreach ( $roles as $key => $role ) {
        if ( isset($_POST[$key.'-create'])  && $_POST[$key.'-create'] == 'on' ) {
          $role->add_cap('publish_issues');
        } else {
          $role->remove_cap('publish_issues');
        }
        if ( isset($_POST[$key.'-edit'])  && $_POST[$key.'-edit'] == 'on' ) {
          $role->add_cap('edit_issue');
          $role->add_cap('edit_issues');
          $role->add_cap('edit_others_issues');
        } else {
          $role->remove_cap('edit_issue');
          $role->remove_cap('edit_issues');
          $role->remove_cap('edit_others_issues');
        }
        if ( isset($_POST[$key.'-view-private'])  && $_POST[$key.'-view-private'] == 'on' ) {
          $role->add_cap('read_private_issues');
        } else {
          $role->remove_cap('read_private_issues');
        }
        if ( isset($_POST[$key.'-delete'])  && $_POST[$key.'-delete'] == 'on' ) {
          $role->add_cap('delete_issue');
        } else {
          $role->remove_cap('delete_issue');
        }
      }
      $this->updated_message = 'Settings saved';
    }
  }
  
  /**
   * Filter: post_type_link
   * Replaces %issue_project% with the slug of the lowest-ID issue_project of the issue
   * @param string $post_link The link to filter
   * @param int $id maybe the ID of the post
   * @param bool $leavename
   * @return string The modified $post_link
   */
  public function post_type_link( $post_link, $id = 0, $leavename = FALSE ) {
    if ( strpos('%issue_project%', $post_link) === 'FALSE' ) {
      return $post_link;
    }
    $post = get_post($id);
    if ( !is_object($post) || $post->post_type != 'issue' ) {
      return $post_link;
    }
    $terms = wp_get_object_terms($post->ID, 'issue_project');
    if ( !$terms ) {
      return str_replace('project/%issue_project%/', '', $post_link);
    }
    return str_replace('%issue_project%', $terms[0]->slug, $post_link);
  }
}

$buggy_press = new BuggyPress();
register_activation_hook(__FILE__, array(&$buggy_press->config, 'activate'));
register_deactivation_hook(__FILE__, array(&$buggy_press->config, 'deactivate'));
