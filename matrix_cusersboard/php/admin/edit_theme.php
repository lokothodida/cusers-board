<?php
  // load template
  $file = $this->directories['themes'].$this->config['theme'].'.xml';
  $template = XML2Array::createArray(file_get_contents($file));
  
  // save changes
  if (!empty($_POST['save'])) {
    // update the template
    $template['channel']['item'][$_GET['template']]['@cdata'] = $_POST['edit-template'];
    $xml = Array2XML::createXML('channel', $template['channel']);
    $xml->save($file);

    // success message
    if ($xml) {
      $this->matrix->getAdminError(str_replace('%s', $_GET['template'], i18n_r('ER_YOUR_CHANGES')), true);
    }
    // error message
    else {
      $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/UPDATE_ERROR'), false);
    }
  }
  elseif(!empty($_POST['create'])) {
    $create = $this->createTheme($_POST['name']);
    if ($create) {
      $this->matrix->getAdminError(str_replace('%s', $_POST['name'], i18n_r('ER_YOUR_CHANGES')), true);
    }
    else {
      $this->matrix->getAdminError(i18n_r(MatrixCUsers::FILE.'/UPDATE_ERROR'), false);
    }
  }
  
  // load templates for menu (reversed because of float: right property)
  $templates = array_reverse($template['channel']['item']);
  $template = $template['channel']['item'][$_GET['template']]['@cdata'];

  ?>
  
<!--header-->
  <h3 class="floated"><?php echo i18n_r(MatrixCUsers::FILE.'/TEMPLATE'); ?></h3>
  <div class="edit-nav">
    <?php foreach ($templates as $slug => $tmp) {?>
    <a href="<?php echo $this->adminURL; ?>&template=<?php echo $slug; ?>" <?php if ($slug == $_GET['template']) echo 'class="current"';?>><?php echo $slug; ?></a>
    <?php } ?>
    <div class="clear"></div>
  </div>
  
  <!--create new template-->
  <div id="metadata_window">
    <form method="post">
      <p><input name="name" type="text" class="text"></p>
      <p><input name="create" style="width: 150px;" type="submit" class="submit" value="<?php echo i18n_r(MatrixCUsers::FILE.'/CREATE'); ?>"></p>
      <div class="clear"></div>
    </form>
  </div>
  
  <!--template-->
  <form method="post">
    <?php
      $params = array();
      $params['properties'] = 'name="edit-template" class="codeeditor DM_codeeditor text" id="post-edit-template"';
      $params['value'] = $template;
      $params['id'] = 'post-edit-template';
      $this->matrix->getEditor($params);
    ?>
    <input type="submit" name="save" class="submit" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>"/>&nbsp;&nbsp; /&nbsp;
    <a href="<?php echo $this->adminURL; ?>" class="cancel"><?php echo i18n_r(MatrixCUsers::FILE.'/BACK'); ?></a>
  </form>
  
  <script>
    $(document).ready(function() {
      $('#metadata_window').hide();
    }); // ready
  </script>