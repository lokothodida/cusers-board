<?php
  // save changes
  if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $update = $this->matrix->updateRecord(self::TABLE_CATEGORIES, $_GET['category'], $_POST);
    
    
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
  
  $category = $this->matrix->recordExists(self::TABLE_FORUMS, $_GET['category']);
  $this->loadBoard();
?>

<h3><?php echo i18n_r(self::FILE.'/CATEGORY'); ?></h3>

<form method="post">
  <?php $this->matrix->displayForm(self::TABLE_CATEGORIES, $_GET['category']); ?>
  <input type="submit" class="submit" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>">&nbsp;&nbsp; /&nbsp;
  <a href="<?php echo $this->adminURL; ?>" class="cancel"><?php echo i18n_r(MatrixCUsers::FILE.'/BACK'); ?></a>
</form>