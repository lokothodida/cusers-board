<?php
  // create category/forum
  if (!empty($_POST['create_category']) || !empty($_POST['create_forum'])) {
    if (!empty($_POST['create_category'])) {
      $type = 'CATEGORY';
      if (empty($_POST['name'])) {
        $_POST['slug'] = $this->matrix->str2slug($_POST['post-name']);
      }
      $create = $this->matrix->createRecord(self::TABLE_CATEGORIES, $_POST);
    }
    elseif (!empty($_POST['create_forum'])) {
      $type = 'FORUM';
      $_POST['slug'] = $this->matrix->str2slug($_POST['post-name']);
      $create = $this->matrix->createRecord(self::TABLE_FORUMS, $_POST);
    }
    
    // success message
    if ($create) {
      $this->matrix->getAdminError(i18n_r(self::FILE.'/'.$type.'_CREATESUCCESS'), true);
    }
    
    // error message
    else {
      $this->matrix->getAdminError(i18n_r(self::FILE.'/'.$type.'_CREATEERROR'), false);
    }
    
    // refresh the index to reflect the changes
    $this->matrix->refreshIndex();
  }
  
  // delete category
  if (isset($_GET['category']) && isset($_GET['delete'])) {
    $delete = $this->deleteCategory($_GET['category']);
    if ($delete) $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/DELETE_SUCCESS'), true);
    else         $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/DELETE_ERROR'), false);
  }
  // delete forum
  if (isset($_GET['forum']) && isset($_GET['delete'])) {
    $delete = $this->deleteForum($_GET['forum']);
    if ($delete) $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/DELETE_SUCCESS'), true);
    else         $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/DELETE_ERROR'), false);
  }
  // compatibility fix
  if (isset($_GET['compatibility'])) {
    $fix = $this->fixCompatibility();
    if ($fix) $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/COMPATIBILITY_SUCCESS'), true);
    else      $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/COMPATIBILITY_ERROR'), false);
  }
  
  

  $this->loadBoard();
  if (isset($_GET['reset'])) {
    $this->reset();
    echo '<script>window.location = "'.$this->adminURL.'";</script>';
    #$this->createTables();
    #$this->loadBoard();
  }
?>

<h3 class="floated"><?php echo i18n_r(self::FILE.'/PLUGIN_SIDEBAR'); ?></h3>
<div class="edit-nav">
  <a href="<?php echo $this->adminURL; ?>&backups"><?php echo i18n_r(MatrixCUsers::FILE.'/BACKUPS'); ?></a>
  <a href="<?php echo $this->adminURL; ?>&compatibility"><?php echo i18n_r(MatrixCUsers::FILE.'/COMPATIBILITY'); ?></a>
  <a href="<?php echo $this->adminURL; ?>&config"><?php echo i18n_r(MatrixCUsers::FILE.'/CONFIG'); ?></a>
  <a href="<?php echo $this->adminURL; ?>&template=home"><?php echo i18n_r(MatrixCUsers::FILE.'/TEMPLATES'); ?></a>
  <a href="<?php echo $this->adminURL; ?>" class="current"><?php echo i18n_r(self::FILE.'/BOARD'); ?></a>
  <div class="clear"></div>
</div>

<table>
  <tbody>
    <?php foreach ($this->board['categories'] as $category) { ?>
    <tr>
      <th>
        <a href="<?php echo $this->adminURL; ?>&category=<?php echo $category['id']; ?>"><?php echo $category['name']; ?></a>
      </th>
      <th style="width: 10%; text-align: right;">
        <a href="<?php echo $this->getCategoryURL($category); ?>" target="_blank" class="view cancel">#</a> 
        <a href="#" class="delete cancel" data-id="<?php echo $category['id']; ?>" data-name="<?php echo $category['name']; ?>" data-type="category">&times;</a>
      </th>
    </tr>
    <?php 
      if (isset($category['forums'])) {
        foreach ($category['forums'] as $forum) { ?>
    <tr>
      <td style="width: 90%;">
        <a href="<?php echo $this->adminURL; ?>&forum=<?php echo $forum['id']; ?>"><?php echo $forum['name']; ?></a><br>
        <?php echo $forum['description']; ?>
      </td>
      <td style="width: 10%; text-align: right;">
        <a href="<?php echo $this->getForumURL($forum); ?>" target="_blank" class="view cancel">#</a> 
        <a href="#" class="delete cancel" data-id="<?php echo $forum['id']; ?>" data-name="<?php echo $forum['name']; ?>" data-type="forum">&times;</a>
      </td>
    </tr>
    <?php
        }
      }
    ?>
    <?php } ?>
  </tbody>
</table>

<div class="options">
  <input type="submit" class="submit" value="<?php echo i18n_r(self::FILE.'/CREATE_CATEGORY'); ?>" data-href="<?php echo $this->adminURL; ?>&category=create">&nbsp;&nbsp;
  <input type="submit" class="submit" value="<?php echo i18n_r(self::FILE.'/CREATE_FORUM'); ?>" data-href="<?php echo $this->adminURL; ?>&forum=create">
  <a href="<?php echo $this->adminURL; ?>&category=create" class="cancel"><?php echo i18n_r(self::FILE.'/CREATE_CATEGORY'); ?></a>
  <a href="<?php echo $this->adminURL; ?>&forum=create" class="cancel"><?php echo i18n_r(self::FILE.'/CREATE_FORUM'); ?></a>
</div>

<style>
  .options input { display: none; }
</style>
<script>
  $(document).ready(function() {
    $('.options input').show().click(function() {
      window.location = $(this).data('href');
    });
    $('.options a').hide();
    
    // show a dialog box when clicking on a link
    $('.delete').bind('click', function(e) {
      var id = $(this).data('id');
      var name = $(this).data('name');
      var type = $(this).data('type');
        e.preventDefault();
        $.Zebra_Dialog(<?php echo json_encode(i18n_r(MatrixCUsers::FILE.'/ARE_YOU_SURE')); ?>, {
            'type':     'question',
            'title':    <?php echo json_encode(i18n_r(MatrixCUsers::FILE.'/DELETE').' : '); ?> + name,
            'buttons':  [
                  {caption: 'No', },
                  {caption: 'Yes', callback: function() { window.location = "<?php echo $this->adminURL; ?>&" + type + "=" + id + "&delete" }},
              ]
        });
    });
  }); // ready
</script>