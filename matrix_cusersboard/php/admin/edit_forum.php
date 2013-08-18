<?php
  // save changes
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_POST['post-slug'] = $this->matrix->str2slug($_POST['post-name']);
    $update = $this->matrix->updateRecord(self::TABLE_FORUMS, $_GET['forum'], $_POST);
    
    
    // success message
    if ($update) {
      $this->matrix->getAdminError(str_replace('%s', $update['new']['name'], i18n_r('ER_YOUR_CHANGES')), true);
    }
    
    // error message
    else {
      $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/UPDATE_ERROR'), false);
    }
    
    // refresh the index to reflect the changes
    $this->matrix->refreshIndex();
  }
  
  $forum = $this->matrix->recordExists(self::TABLE_FORUMS, $_GET['forum']);
  $this->loadBoard();
?>

<h3><?php echo i18n_r(self::FILE.'/FORUM'); ?></h3>

<form method="post">
  <p><?php $this->matrix->displayField(self::TABLE_FORUMS, 'name', $forum['name']); ?></p>
  <div class="leftsec">
    <p>
      <label><?php echo i18n_r(self::FILE.'/CATEGORY');  ?> : </label>
      <select name="category" class="text">
        <?php foreach ($this->board['categories'] as $category) { ?>
        <option value="<?php echo $category['id']; ?>" <?php if ($category['id'] == $forum['category']) echo 'selected="selected"'; ?>><?php echo $category['name']; ?></option>
        <?php } ?>
      </select>
    </p>
  </div>
  <div class="rightsec">
    <p>
      <label><?php echo i18n_r(self::FILE.'/ORDER');  ?> : </label>
      <?php $this->matrix->displayField(self::TABLE_FORUMS, 'order', $forum['order']); ?>
    </p>
  </div>
  <div class="clear"></div>
  <p><?php $this->matrix->displayField(self::TABLE_FORUMS, 'description', $forum['description'], true); ?></p>
  <input type="submit" class="submit" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>">&nbsp;&nbsp;
  / 
  <a href="<?php echo $this->adminURL; ?>" class="cancel"><?php echo i18n_r(MatrixCUsers::FILE.'/BACK'); ?></a>
</form>