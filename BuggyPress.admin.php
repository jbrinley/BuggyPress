<div class="wrap buggypress-settings">
  <?php if ( $this->updated_message ) : ?>
    <div id="message" class="updated fade"><p><?php echo $this->updated_message; ?></p></div>
  <?php endif; ?>
  <h2>BuggyPress Settings</h2>
  <form id="buggypress-settings-form" method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
    <?php wp_nonce_field('buggypress_settings'); ?>
    <div class="buggypress-capabilities">
      <h3>Capabilities</h3>
      <p>Select which roles will have the following capabilities:</p>
      <div class="buggypress-capabilitiy create">
        <h4>Create Issues</h4>
        <?php foreach ( $roles as $role ): ?>
          <label for="<?php echo $role->name; ?>-create">
            <input type="checkbox" id="<?php echo $role->name; ?>-create" name="<?php echo $role->name; ?>-create"<?php if ($role->capabilities['publish_issues'] == 1) { echo ' checked="checked"'; } ?>"/>
            <?php echo $role->display_name; ?>
          </label>
        <?php endforeach; ?>
      </div>
      <div class="buggypress-capability edit">
        <h4>Edit Issues</h4>
        <?php foreach ( $roles as $role ): ?>
          <label for="<?php echo $role->name; ?>-edit">
            <input type="checkbox" id="<?php echo $role->name; ?>-edit" name="<?php echo $role->name; ?>-edit"<?php if ($role->capabilities['edit_issues'] == 1) { echo ' checked="checked"'; } ?>"/>
            <?php echo $role->display_name; ?>
          </label>
        <?php endforeach; ?>
        <p>Note: Users with the "Edit Issues" capability will be able to update issues from the comment form.</p>
      </div>
      <div class="buggypress-capability view-private">
        <h4>View Private Issues</h4>
        <?php foreach ( $roles as $role ): ?>
          <label for="<?php echo $role->name; ?>-view-private">
            <input type="checkbox" id="<?php echo $role->name; ?>-view-private" name="<?php echo $role->name; ?>-view-private"<?php if ($role->capabilities['read_private_issues'] == 1) { echo ' checked="checked"'; } ?>"/>
            <?php echo $role->display_name; ?>
          </label>
        <?php endforeach; ?>
      </div>
      <div class="buggypress-capability delete">
        <h4>Delete Issues</h4>
        <?php foreach ( $roles as $role ): ?>
          <label for="<?php echo $role->name; ?>-delete">
            <input type="checkbox" id="<?php echo $role->name; ?>-delete" name="<?php echo $role->name; ?>-delete"<?php if ($role->capabilities['delete_issue'] == 1) { echo ' checked="checked"'; } ?>"/>
            <?php echo $role->display_name; ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
    
    <div class="save-settings-form" style="margin-top: 1em;">
      <input class="button button-highlighted" type="submit" value="Save" />
    </div>
  </form>
</div>