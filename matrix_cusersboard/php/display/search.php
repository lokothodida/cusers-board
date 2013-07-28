<?php

if (($_GET['page']) == 1 && empty($_POST) && isset($_SESSION['board']['search'])) unset($_SESSION['board']['search']);

if (
  (!empty($_POST['searchBoard']) && !(empty($_POST['words']) && empty($_POST['tags']) && empty($_POST['user']))) || 
  isset($_SESSION['board']['search'])
  ) {
  if (!isset($_SESSION['board']['search'])) {
    $_SESSION['board']['search'] = array(
      'tags' => array('_cuboard', $_POST['postOrTopic']),
      'words' => $_POST['words'],
      'order' => $_POST['ascDesc'].$_POST['sortBy'],
    );
    if (!empty($_POST['user'])) $_SESSION['board']['search']['tags'][] = $_POST['user'];
    if (!empty($_POST['date'])) $_SESSION['board']['search']['tags'][] = '_pub_'.str_replace('-', '', $_POST['date']);
  }
  
  // clean up tags
  $_SESSION['board']['search']['tags'] = array_map('trim', $_SESSION['board']['search']['tags']);
  $_SESSION['board']['search']['tags'] = array_filter($_SESSION['board']['search']['tags']);
  
  // results array
  $results = $this->returnSearchResults($_SESSION['board']['search']['tags'], $_SESSION['board']['search']['words'], $range=2, $_SESSION['board']['search']['order'], $lang=null, $url=$this->config['urls']['search'], $delim=$this->config['urls']['paginate']);
  ?>
  <div class="page_navigation">
  <?php echo $results['links']; ?>
  </div>
  <div class="tableWrap">
    <table>
      <thead>
        <tr>
          <th class="head1" colspan="100%"><?php echo i18n_r(MatrixCUsers::FILE.'/SEARCH'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($results['results'] as $result) { ?>
        <tr>
        <?php
          if ($result->resultType == 't') {
          $topic = $this->searchItem2Array(self::TABLE_TOPICS, $result);
        ?>
          <td class="row1"><h3><a href="<?php echo $this->getTopicURL($topic); ?>"><?php echo $topic['subject']; ?></a></h3></td>
        <?php } else {
          $post = $this->searchItem2Array(self::TABLE_POSTS, $result);
        ?>
        <td class="row2" style="width: 20%;">
          <h3><a href="<?php echo $this->core->getProfileURL($post['author']['username']); ?>"><?php echo $post['author']['displayname']; ?></a></h3>
        </td>
        <td class="row1" style="width: 80%;">
          <h3><a href="<?php echo $this->getTopicURL($post['topic']); ?>"><?php echo $post['topic']['subject']; ?></a></h3>
          <?php echo $this->parser->bbcode($post['content']); ?>
        </td>
        
        <?php } ?>
        </tr>
        <tr>
          <td class="row2" colspan="100%" style="height: 5px;"></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
  <div class="page_navigation">
  <?php echo $results['links']; ?>
  </div>
<?php
}
else {
?>
  <form method="post">
    <div class="tableWrap">  
      <table>
        <thead>
          <tr>
            <th class="head1" colspan="100%"><?php echo i18n_r(MatrixCUsers::FILE.'/SEARCH'); ?></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="row1" style="width: 50%;">
              <input type="text" name="words" placeholder="<?php echo i18n_r(MatrixCUsers::FILE.'/WORDS'); ?>">
              <input type="text" name="tags" placeholder="<?php echo i18n_r(MatrixCUsers::FILE.'/TAGS'); ?>">
            </td>
            <td class="row1" style="width: 50%;">
              <select name="user">
                <option value="">-- <?php echo i18n_r(MatrixCUsers::FILE.'/USER'); ?> --</option>
                <?php
                  $users = $this->getUsers();
                  foreach ($users as $user) {
                ?>
                <option value="_cuser_<?php echo $user['username']; ?>"><?php echo $user['displayname']; ?></option>
                <?php } ?>
              </select>
            </td>
          </tr>
        </tbody>
        <thead>
          <tr>
            <th class="head1" colspan="100%"><?php echo i18n_r(MatrixCUsers::FILE.'/OPTIONS'); ?></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td class="row1" colspan="100%" style="text-align: center;">
              <select name="sortBy">
                <option value="subject"><?php echo i18n_r(MatrixCUsers::FILE.'/SUBJECT'); ?></option>
                <option value="credate"><?php echo i18n_r(MatrixCUsers::FILE.'/DATE'); ?></option>
              <select>
              <select name="ascDesc">
                <option value="+"><?php echo i18n_r(MatrixCUsers::FILE.'/ASCENDING'); ?></option>
                <option value="-"><?php echo i18n_r(MatrixCUsers::FILE.'/DESCENDING'); ?></option>
              <select>
              <select name="postOrTopic">
                <option value="_post"><?php echo i18n_r(self::FILE.'/POSTS'); ?></option>
                <option value="_topic"><?php echo i18n_r(self::FILE.'/TOPICS'); ?></option>
              <select>
              <input type="month" name="date">
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <input name="searchBoard" type="submit">
  </form>
<?php
}

?>