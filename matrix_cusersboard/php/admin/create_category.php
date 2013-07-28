<h3><?php echo i18n_r(self::FILE.'/CREATE_CATEGORY'); ?></h3>

<form method="post" action="<?php echo $this->adminURL; ?>">
  <?php $this->matrix->displayForm(self::TABLE_CATEGORIES); ?>
  <input type="submit" class="submit" value="<?php echo i18n_r(self::FILE.'/CREATE_CATEGORY'); ?>" name="create_category">&nbsp;&nbsp; /&nbsp;
  <a href="<?php echo $this->adminURL; ?>" class="cancel"><?php echo i18n_r(MatrixCUsers::FILE.'/BACK'); ?></a>
</form>
