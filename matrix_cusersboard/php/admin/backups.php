<?php

  // backup
  if ($_GET['backups'] == 'create') {
    $backup = $this->backup();
    if ($backup) {
      $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/BACKUP_SUCCESS'), true);
    }
    else {
      $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/BACKUP_ERROR'), false);
    }
  }
  // reset
  elseif ($_GET['backups'] == 'reset') {
    $reset = $this->reset();
    if ($reset) {
      $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/BACKUP_SUCCESS'), true);
    }
    else {
      $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/BACKUP_ERROR'), false);
    }
  }
  // restore
  elseif (is_numeric($_GET['backups']) && isset($_GET['restore'])) {
    $restore = $this->restore($_GET['backups']);
    if ($restore) {
      $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/RESTORE_SUCCESS'), true);
    }
    else {
      $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/RESTORE_ERROR'), false);
    }
  }
  // delete
  elseif (is_numeric($_GET['backups']) && isset($_GET['delete'])) {
    $delete = unlink($this->directories['backups'].$_GET['backups'].'.zip');
    if ($delete) {
      $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/DELETE_SUCCESS'), true);
    }
    else {
      $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/DELETE_ERROR'), false);
    }
  }
  
  // get backups
  $backups = $this->getBackups();
?>

<h3 class="floated"><?php echo i18n_r(MatrixCUsers::FILE.'/BACKUPS'); ?></h3>
<div class="edit-nav">
  <a href="<?php echo $this->adminURL; ?>&backups" class="current"><?php echo i18n_r(MatrixCUsers::FILE.'/BACKUPS'); ?></a>
  <a href="<?php echo $this->adminURL; ?>&config"><?php echo i18n_r(MatrixCUsers::FILE.'/CONFIG'); ?></a>
  <a href="<?php echo $this->adminURL; ?>&template=home"><?php echo i18n_r(MatrixCUsers::FILE.'/TEMPLATES'); ?></a>
  <a href="<?php echo $this->adminURL; ?>"><?php echo i18n_r(self::FILE.'/BOARD'); ?></a>
  <div class="clear"></div>
</div>

<table class="edittable highlight pajinate">
  <thead>
    <tr>
      <th style="width: 99%;"><?php echo i18n_r(MatrixCUsers::FILE.'/BACKUPS'); ?></th>
      <th style="width: 1%;"><?php echo i18n_r(MatrixCUsers::FILE.'/OPTIONS'); ?></th>
    </tr>
  </thead>
  <tbody class="content">
    <?php foreach ($backups as $backup) { ?>
    <tr>
      <td><a href=""><?php echo $backup['date']; ?></a></td>
      <td style="text-align: right;">
        <a href="<?php echo $this->adminURL; ?>&backups=<?php echo $backup['timestamp']; ?>&restore" class="cancel restore" data-date="<?php echo $backup['timestamp']; ?>">#</a> 
        <a href="<?php echo $this->adminURL; ?>&backups=<?php echo $backup['timestamp']; ?>&delete" class="cancel delete" data-date="<?php echo $backup['timestamp']; ?>">&times;</a>
      </td>
    </tr>
    <?php } ?>
  </tbody>
  <thead>
    <tr>
      <th colspan="100%" style="overflow: hidden;">
        <div style="float: left;">
          <a href="<?php echo $this->adminURL; ?>&backups=create" class="cancel create"><?php echo i18n_r(MatrixCUsers::FILE.'/CREATE'); ?></a> 
          <a href="<?php echo $this->adminURL; ?>&backups=reset" class="cancel reset"><?php echo i18n_r(MatrixCUsers::FILE.'/RESET'); ?></a>
        </div>
        <div class="page_navigation" style="float: right;"></div>
      </th>
    </tr>
  </thead>
</table>

<script>
  $(document).ready(function() {
    // pajinate
    var pajinateSettings = {
        'items_per_page'  : 10,
        'nav_label_first' : '|&lt;&lt;', 
        'nav_label_prev'  : '&lt;', 
        'nav_label_next'  : '&gt;', 
        'nav_label_last'  : '&gt;&gt;|', 
      };
    $('.pajinate').pajinate(pajinateSettings);
    $('.pajinate .page_navigation a').addClass('cancel');
    
    // reset
    $('.reset').bind('click', function(e) {
      var date = $(this).data('date');
      e.preventDefault();
      $.Zebra_Dialog(<?php echo json_encode(i18n_r(MatrixCUsers::FILE.'/ARE_YOU_SURE')); ?>, {
        'type':     'question',
        'title':    <?php echo json_encode(i18n_r(MatrixCUsers::FILE.'/RESET')); ?>,
        'buttons':  [
          {caption: 'No', },
          {caption: 'Yes', callback: function() { window.location = "<?php echo $this->adminURL; ?>&backups=reset" }},
        ]
      });
    });
    // restore
    $('.restore').bind('click', function(e) {
      var date = $(this).data('date');
      e.preventDefault();
      $.Zebra_Dialog(<?php echo json_encode(i18n_r(MatrixCUsers::FILE.'/ARE_YOU_SURE')); ?>, {
        'type':     'question',
        'title':    <?php echo json_encode(i18n_r(MatrixCUsers::FILE.'/RESTORE')); ?>,
        'buttons':  [
          {caption: 'No', },
          {caption: 'Yes', callback: function() { window.location = "<?php echo $this->adminURL; ?>&backups=" + date + "&restore" }},
        ]
      });
    });
    // delete
    $('.delete').bind('click', function(e) {
      var date = $(this).data('date');
      e.preventDefault();
      $.Zebra_Dialog(<?php echo json_encode(i18n_r(MatrixCUsers::FILE.'/ARE_YOU_SURE')); ?>, {
        'type':     'question',
        'title':    <?php echo json_encode(i18n_r(MatrixCUsers::FILE.'/DELETE')); ?>,
        'buttons':  [
          {caption: 'No', },
          {caption: 'Yes', callback: function() { window.location = "<?php echo $this->adminURL; ?>&backups=" + date + "&delete" }},
        ]
      });
    });
  });
</script>