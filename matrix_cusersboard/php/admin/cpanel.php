<?php

// post queries
if (!empty($_POST['updateCategory'])) {
  $this->matrix->updateRecord(self::TABLE_CATEGORIES, $_POST['id'], $_POST);
}
elseif (!empty($_POST['updateForum'])) {
  $this->matrix->updateRecord(self::TABLE_FORUMS, $_POST['id'], $_POST);
}
elseif (!empty($_POST['createCategory'])) {
  if (empty($_POST['post-slug'])) $_POST['post-slug'] = $this->matrix->str2slug($_POST['post-name']);
  $create = $this->matrix->createRecord(self::TABLE_CATEGORIES, $_POST);
}
elseif (!empty($_POST['createForum'])) {
  if (empty($_POST['post-slug'])) $_POST['post-slug'] = $this->matrix->str2slug($_POST['post-name']);
  $create = $this->matrix->createRecord(self::TABLE_FORUMS, $_POST);
}


// category
if (isset($_GET['category']) && ((is_numeric($_GET['category']) && $this->matrix->recordExists(self::TABLE_CATEGORIES, $_GET['category']) && !isset($_GET['delete'])) || $_GET['category'] == 'create')) {
  $this->getSchema(array(self::TABLE_CATEGORIES));
  if (is_numeric($_GET['category'])) {
    $category = $this->matrix->recordExists(self::TABLE_CATEGORIES, $_GET['category']);
    $title = i18n_r(self::FILE.'/EDIT_CATEGORY');
  }
  else {
    $category = array('id' => null);
    $title = i18n_r(self::FILE.'/CREATE_CATEGORY');
  }
  ?>
  
  <form method="post" action="<?php echo $this->config['urls']['cpanel']; ?>">
    <div class="tableWrap">
      <table>
        <thead>
          <tr>
            <th class="head1" colspan="100%">
              <?php if ($category['id'] != null) { ?>
              <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
              <?php } ?>
              <?php echo $title; ?>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php
            foreach ($this->schema[self::TABLE_CATEGORIES]['fields'] as $field) {
              if ($field['type'] == 'wysiwyg') $ckeditor = true;
              else $ckeditor = false;
              
              if (!isset($category[$field['name']])) $category[$field['name']] = $field['default'];
          ?>
          <tr>
            <th class="row2" style="width: 20%;"><?php echo $field['label']; ?></th>
            <td class="row1" style="width: 80%;">
              <?php $this->matrix->displayField(self::TABLE_CATEGORIES, $field['name'], $category[$field['name']]); ?>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <?php if ($category['id'] != null) { ?>
    <input type="submit" class="submit" name="updateCategory" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>">
    <?php } else { ?>
    <input type="submit" class="submit" name="createCategory" value="<?php echo i18n_r(self::FILE.'/CREATE_CATEGORY'); ?>">
    <?php } ?>
  </form>
  <?php
}
// forum
elseif (isset($_GET['forum']) && ((is_numeric($_GET['forum']) && $this->matrix->recordExists(self::TABLE_FORUMS, $_GET['forum']) && !isset($_GET['delete'])) || $_GET['forum'] == 'create')) {
  $this->getSchema(array(self::TABLE_FORUMS));
  if (is_numeric($_GET['forum'])) {
    $forum = $this->matrix->recordExists(self::TABLE_FORUMS, $_GET['forum']);
    $title = i18n_r(self::FILE.'/EDIT_FORUM');
  }
  else {
    $forum = array('id' => null);
    $title = i18n_r(self::FILE.'/CREATE_FORUM');
  }
  ?>
  
  <form method="post" action="<?php echo $this->config['urls']['cpanel']; ?>">
    <div class="tableWrap">
      <table>
        <thead>
          <tr>
            <th class="head1" colspan="100%">
              <?php if ($forum['id'] != null) { ?>
              <input type="hidden" name="id" value="<?php echo $forum['id']; ?>">
              <?php } ?>
              <?php echo $title; ?>
            </th>
          </tr>
        </thead>
        <tbody>
          <?php
            foreach ($this->schema[self::TABLE_FORUMS]['fields'] as $field) {
              if ($field['type'] == 'wysiwyg') $ckeditor = true;
              else $ckeditor = false;
              
              if (!isset($forum[$field['name']])) $forum[$field['name']] = $field['default'];
          ?>
          <tr>
            <th class="row2" style="width: 20%;"><?php echo $field['label']; ?></th>
            <td class="row1" style="width: 80%;">
              <?php if ($field['name'] == 'category') { ?>
              <select name="category">
                <?php foreach ($this->board['categories'] as $category) { ?>
                  <option value="<?php echo $category['id']; ?>" <?php if ($forum['category'] == $category['id']) echo 'selected="selected"'; ?>><?php echo $category['name']; ?></option>
                <?php } ?>
              </select>
              <?php } else { $this->matrix->displayField(self::TABLE_FORUMS, $field['name'], $forum[$field['name']], $ckeditor); } ?>
            </td>
          </tr>
          <?php } ?>
        </tbody>
      </table>
    </div>
    <?php if ($forum['id'] != null) { ?>
    <input type="submit" class="submit" name="updateForum" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>">
    <?php } else { ?>
    <input type="submit" class="submit" name="createForum" value="<?php echo i18n_r(self::FILE.'/CREATE_FORUM'); ?>">
    <?php } ?>
  </form>
  <?php
}
// main
else {
  // delete category
  if (isset($_GET['category']) && isset($_GET['delete'])) {
    $delete = $this->deleteCategory($_GET['category']);
  }
  // delete forum
  if (isset($_GET['forum']) && isset($_GET['delete'])) {
    $delete = $this->deleteForum($_GET['forum']);
  }
  
  $this->loadBoard();
  ?>
  <div class="links">
    <a href="<?php echo $this->config['urls']['cpanel-create-category']; ?>" class="create category"><?php echo i18n_r(self::FILE.'/CREATE_CATEGORY'); ?></a> 
    <a href="<?php echo $this->config['urls']['cpanel-create-forum']; ?>" class="create forum"><?php echo i18n_r(self::FILE.'/CREATE_FORUM'); ?></a>
  </div>
  <div class="tableWrap">
    <table>
      <tbody>
        <?php foreach ($this->board['categories'] as $category) { ?>
        <tr>
          <th class="head1">
            <a href="<?php echo str_replace('%category%', $category['id'], $this->config['urls']['cpanel-category']); ?>"><?php echo $category['name']; ?></a>
          </th>
          <th class="head1" style="width: 5%;">
            <a href="#" class="delete" data-id="<?php echo $category['id']; ?>" data-name="<?php echo $category['name']; ?>" data-url="<?php echo str_replace('%category%', $category['id'], $this->config['urls']['cpanel-delete-category']); ?>">&times;</a>
          </th>
        </tr>
        <?php 
          if (isset($category['forums'])) {
            foreach ($category['forums'] as $forum) { ?>
        <tr>
          <td class="row1">
            <a href="<?php echo str_replace('%forum%', $forum['id'], $this->config['urls']['cpanel-forum']); ?>"><?php echo $forum['name']; ?></a><br>
            <?php echo $forum['description']; ?>
          </td>
          <td class="row2" style="width: 5%; text-align: center;">
            <a href="#" class="delete" data-id="<?php echo $forum['id']; ?>" data-name="<?php echo $forum['name']; ?>" data-url="<?php echo str_replace('%forum%', $forum['id'], $this->config['urls']['cpanel-delete-forum']); ?>">&times;</a>
          </td>
        </tr>
        <?php
            }
          }
        ?>
        <?php } ?>
      </tbody>
    </table>
  </div>
  <script>
    $(document).ready(function() {
      $('.options input').show().click(function() {
        window.location = $(this).data('href');
      });
      $('.options a').hide();
      
      // show a dialog box when clicking on a link
      $('.delete').bind('click', function(e) {
        var url = $(this).data('url');
        var id = $(this).data('id');
        var name = $(this).data('name');
          e.preventDefault();
          $.Zebra_Dialog(<?php echo json_encode(i18n_r(MatrixCUsers::FILE.'/ARE_YOU_SURE')); ?>, {
              'type':     'question',
              'title':    <?php echo json_encode(i18n_r(MatrixCUsers::FILE.'/DELETE').' : '); ?> + name,
              'buttons':  [
                    {caption: 'No', },
                    {caption: 'Yes', callback: function() { window.location = url }},
                ]
          });
      });
    }); // ready
  </script>
  <?php
}


?>