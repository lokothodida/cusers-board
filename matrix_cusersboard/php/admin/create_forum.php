<?php $this->loadBoard(); ?>

<h3><?php echo i18n_r(self::FILE.'/CREATE_FORUM'); ?></h3>

<form method="post" action="<?php echo $this->adminURL; ?>">
  <p><?php $this->matrix->displayField(self::TABLE_FORUMS, 'name'); ?></p>
  <div class="leftsec">
    <p>
      <label><?php echo i18n_r(self::FILE.'/CATEGORY');  ?> : </label>
      <select name="category" class="text">
        <?php foreach ($this->board['categories'] as $category) { ?>
        <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
        <?php } ?>
      </select>
    </p>
  </div>
  <div class="rightsec">
    <p>
      <label><?php echo i18n_r(self::FILE.'/ORDER');  ?> : </label>
      <?php $this->matrix->displayField(self::TABLE_FORUMS, 'order'); ?>
    </p>
  </div>
  <div class="clear"></div>
  <p><?php $this->matrix->displayField(self::TABLE_FORUMS, 'description', '', true); ?></p>
  <input type="submit" class="submit" value="<?php echo i18n_r(self::FILE.'/CREATE_FORUM'); ?>" name="create_forum">&nbsp;&nbsp; /&nbsp;
  <a href="<?php echo $this->adminURL; ?>" class="cancel"><?php echo i18n_r(MatrixCUsers::FILE.'/BACK'); ?></a>
</form>