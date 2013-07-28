<?php

// fix themes dropdown
$this->loadThemes(true);

// save changes
if ($_SERVER['REQUEST_METHOD']=='POST') {

  // update the record
  $update = $this->matrix->updateRecord(self::TABLE_CONFIG, 0, $_POST);
  
  // success message
  if ($update) {
    $undo = 'load.php?id='.self::FILE.'&config&undo';
    $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/CONFIG_UPDATESUCCESS'), true, true, $undo);
  }
  // error message
  else {
    $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/PAGES_UPDATEERROR'), false);
  }
}
// undo changes
elseif (isset($_GET['undo'])) {
  // undo the record update
  $undo = $this->matrix->undoRecord(self::TABLE_CONFIG, 0);
  
  // success message
  if ($undo) {
    $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/CONFIG_UNDOSUCCESS'), true);
  }
  // error message
  else {
    $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/CONFIG_UNDOERROR'), false);
  }
  
  
  // refresh the index to reflect the changes
  $this->matrix->refreshIndex();
}

?>

<h3 class="floated"><?php echo i18n_r(MatrixCUsers::FILE.'/CONFIG'); ?></h3>
<div class="edit-nav">
  <a href="<?php echo $this->adminURL; ?>&backups"><?php echo i18n_r(MatrixCUsers::FILE.'/BACKUPS'); ?></a>
  <a href="<?php echo $this->adminURL; ?>&config" class="current"><?php echo i18n_r(MatrixCUsers::FILE.'/CONFIG'); ?></a>
  <a href="<?php echo $this->adminURL; ?>&template=home"><?php echo i18n_r(MatrixCUsers::FILE.'/TEMPLATES'); ?></a>
  <a href="<?php echo $this->adminURL; ?>"><?php echo i18n_r(self::FILE.'/BOARD'); ?></a>
  <div class="clear"></div>
</div>

<form method="post" action="load.php?id=<?php echo self::FILE; ?>&config">
  <?php $this->matrix->displayForm(self::TABLE_CONFIG, 0); ?>
  <input type="submit" class="submit" name="save" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>">
</form>