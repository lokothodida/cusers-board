<?php

class MatrixCUsersBoard {
  /* constants */
  const FILE              = 'matrix_cusersboard';
  const VERSION           = '1.01';
  const AUTHOR            = 'Lawrence Okoth-Odida';
  const URL               = 'http://lokida.co.uk';
  const PAGE              = 'plugins';
  const TABLE_CATEGORIES  = 'cusers-board-categories';
  const TABLE_FORUMS      = 'cusers-board-forums';
  const TABLE_TOPICS      = 'cusers-board-topics';
  const TABLE_POSTS       = 'cusers-board-posts';
  const TABLE_CONFIG      = 'cusers-board-config';
  const SEARCHID          = 'cuboard:';
  
  /* properties */
  private $plugin;
  private $tables;
  private $matrix;
  private $parser;
  private $schema;
  private $uri;
  private $coreConfig;
  private $board;
  private $directories;
  private $template;
  private $adminURL;
  private $categories;
  private $forums; 
  private $title404;
  private $config;
  private $searchItems;
  
  /* methods */

  # constructor
  public function __construct() {
    // plugin details
    $this->plugin = array();
    $this->plugin['id']          = self::FILE;
    $this->plugin['name']        = i18n_r(self::FILE.'/PLUGIN_TITLE');
    $this->plugin['version']     = self::VERSION;
    $this->plugin['author']      = self::AUTHOR;
    $this->plugin['url']         = self::URL;
    $this->plugin['description'] = i18n_r(self::FILE.'/PLUGIN_DESC');
    $this->plugin['page']        = self::PAGE;
    $this->plugin['sidebar']     = i18n_r(self::FILE.'/PLUGIN_SIDEBAR');
    
    // check dependencies
    if ($this->checkDependencies()) {
      $this->matrix = new TheMatrix;
      $this->parser = new TheMatrixParser;
      $this->core = new MatrixCUsers;
      $this->tables = array(self::TABLE_CATEGORIES => array(), self::TABLE_FORUMS => array(), self::TABLE_TOPICS => array(), self::TABLE_POSTS => array(), self::TABLE_CONFIG => array());
      $this->schema = array();
      $this->config = array();
      
      // directories
      $this->directories = array(
        'board' => GSDATAOTHERPATH.'cusers_board/',
        'themes' => GSDATAOTHERPATH.'cusers_board/themes/',
        'cache' => GSDATAOTHERPATH.'cusers_board/cache/',
        'backups' => GSDATAOTHERPATH.'cusers_board/backups/',
      );
      foreach ($this->directories as $dir) {
        if (!file_exists($dir)) mkdir($dir, 0755);
      }
      
      $this->directories['plugins']['core']    = array('dir' => GSPLUGINPATH.self::FILE.'/');
      $this->directories['plugins']['php']     = array('dir' => GSPLUGINPATH.self::FILE.'/php/');
      
      // build tables
      $this->config['topic-types']       = array(i18n_r(self::FILE.'/NORMAL'), i18n_r(self::FILE.'/STICKY'), i18n_r(self::FILE.'/ANNOUNCEMENT'));
      $this->config['topic-statuses']    = array(i18n_r(MatrixCUsers::FILE.'/OPEN'), i18n_r(self::FILE.'/STICKY'), i18n_r(MatrixCUsers::FILE.'/CLOSED'));
      
      
      $this->createTables();
      
      
      
      // configuration
      $config = $this->matrix->query('SELECT * FROM '.self::TABLE_CONFIG.' ORDER BY id DESC', 'SINGLE');
      $this->config['title']             = $config['title'];
      $this->config['slug']              = $config['slug'];
      $this->config['search-slug']       = 'search-board'; //$config['search-slug'];
      $this->config['template']          = $config['template'];
      $this->config['theme']             = $config['theme'];
      $this->config['posts-per-page']    = $config['posts-per-page'];
      $this->config['topics-per-page']   = $config['topics-per-page'];
      $this->config['topic-types']       = array(i18n_r(self::FILE.'/NORMAL'), i18n_r(self::FILE.'/STICKY'), i18n_r(self::FILE.'/ANNOUNCEMENT'));
      $this->config['topic-statuses']    = array(i18n_r(MatrixCUsers::FILE.'/OPEN'), i18n_r(self::FILE.'/STICKY'), i18n_r(MatrixCUsers::FILE.'/CLOSED'));
      
      $this->coreConfig = $this->core->getConfig();
      
      // delete board page (backward compatibility
      if (file_exists(GSDATAPAGESPATH.$this->config['slug'].'.xml')) {
        unlink(GSDATAPAGESPATH.$this->config['slug'].'.xml');
        $this->matrix->refreshIndex();
      }
      
      // slugs
      $this->config['slugs'] = array();
      $this->config['slugs']['home']        = $config['slug'];
      $this->config['slugs']['cpanel']      = 'cpanel';
      
      // urls
      $this->parseURI();
      $this->adminURL = 'load.php?id='.self::FILE;
      
      $this->config['urls'] = array();
      if ($this->matrix->getPrettyURLS()) {
        $this->config['urls']['home']                     = $this->matrix->getSiteURL().$this->config['slug'].'/';
        $this->config['urls']['category']                 = $this->config['urls']['home'].'category/%category%/';
        $this->config['urls']['forum']                    = $this->config['urls']['home'].'forum/%forum%/';
        $this->config['urls']['create-topic']             = $this->config['urls']['home'].'create-topic/%forum%/';
        $this->config['urls']['edit-topic']               = $this->config['urls']['home'].'edit-topic/%topic%/';
        $this->config['urls']['delete-topic']             = $this->config['urls']['home'].'delete-topic/%topic%/';
        $this->config['urls']['topic']                    = $this->config['urls']['home'].'topic/%topic%/';
        $this->config['urls']['reply']                    = $this->config['urls']['home'].'reply/%topic%/';
        $this->config['urls']['edit-post']                = $this->config['urls']['home'].'edit-post/%topic%/%id%/';
        $this->config['urls']['delete-post']              = $this->config['urls']['home'].'delete-post/%topic%/%id%/';
        $this->config['urls']['search']                   = $this->config['urls']['home'].$this->config['search-slug'].'/';
        $this->config['urls']['cpanel']                   = $this->config['urls']['home'].'cpanel/';
        $this->config['urls']['cpanel-category']          = $this->config['urls']['home'].'cpanel/?category=%category%';
        $this->config['urls']['cpanel-create-category']   = $this->config['urls']['home'].'cpanel/?category=create';
        $this->config['urls']['cpanel-delete-category']   = $this->config['urls']['home'].'cpanel/?category=%category%&delete';
        $this->config['urls']['cpanel-forum']             = $this->config['urls']['home'].'cpanel/?forum=%forum%';
        $this->config['urls']['cpanel-create-forum']      = $this->config['urls']['home'].'cpanel/?forum=create';
        $this->config['urls']['cpanel-delete-forum']      = $this->config['urls']['home'].'cpanel/?forum=%forum%&delete';
        $this->config['urls']['paginate']                 = 'page-$1/';
      }
      else {
        $this->config['urls']['home']                     = $this->matrix->getSiteURL().'index.php?id='.$this->config['slug'];
        $this->config['urls']['category']                 = $this->config['urls']['home'].'&category=%category%';
        $this->config['urls']['forum']                    = $this->config['urls']['home'].'&forum=%forum%';
        $this->config['urls']['create-topic']             = $this->config['urls']['home'].'&create-topic=%forum%';
        $this->config['urls']['edit-topic']               = $this->config['urls']['home'].'&edit-topic=%topic%';
        $this->config['urls']['delete-topic']             = $this->config['urls']['home'].'&delete-topic=%topic%';
        $this->config['urls']['topic']                    = $this->config['urls']['home'].'&topic=%topic%';
        $this->config['urls']['reply']                    = $this->config['urls']['home'].'&reply=%topic%';
        $this->config['urls']['edit-post']                = $this->config['urls']['home'].'&edit-post=%topic%&post=%id%';
        $this->config['urls']['delete-post']              = $this->config['urls']['home'].'delete-post/%topic%/%id%/';
        $this->config['urls']['search']                   = $this->config['urls']['home'].'&'.$this->config['search-slug'];
        $this->config['urls']['cpanel']                   = $this->config['urls']['home'].'&cpanel';
        $this->config['urls']['cpanel-category']          = $this->config['urls']['cpanel'].'?category=%category%';
        $this->config['urls']['cpanel-create-category']   = $this->config['urls']['cpanel'].'?category=create';
        $this->config['urls']['cpanel-delete-category']   = $this->config['urls']['cpanel'].'?category=%category%&delete';
        $this->config['urls']['cpanel-forum']             = $this->config['urls']['cpanel'].'?forum=%forum%';
        $this->config['urls']['cpanel-create-forum']      = $this->config['urls']['cpanel'].'?forum=create';
        $this->config['urls']['cpanel-delete-forum']      = $this->config['urls']['cpanel'].'?forum=%forum%&delete';
        $this->config['urls']['paginate']                 = '&page=$1';
      }
      
      // load categories & forums
      if ($this->isBoard() || isset($_GET['board'])) {
        $this->loadBoard();
      }
      
      // default theme
      if (!file_exists($this->directories['themes'].'Default.xml')) {
        $this->createTheme('Default');
      }
      
      // schema
      $this->getSchema(array(self::TABLE_TOPICS, self::TABLE_POSTS));
    }
  }
  
  # get plugin info
  public function pluginInfo($info) {
    if (isset($this->plugin[$info])) {
      return $this->plugin[$info];
    }
    else return null;
  }
  
  # check dependencies
  private function checkDependencies() {
    if (
      (class_exists('TheMatrix') && TheMatrix::VERSION >= '1.02') &&
      (class_exists('MatrixCUsers') && MatrixCUsers::VERSION >= '1.01') && 
      function_exists('i18n_init') &&
      function_exists('get_i18n_search_results') 
    ) return true;
    else return false;
  }
  
  # missing dependencies
  private function missingDependencies() {
    $dependencies = array();
    
    if (!(class_exists('TheMatrix') && TheMatrix::VERSION >= '1.02')) {
      $dependencies[] = array('name' => 'The Matrix (1.02+)', 'url' => 'https://github.com/n00dles/DM_matrix/');
    }
    if (!(class_exists('MatrixCUsers') && MatrixCUsers::VERSION >= '1.01')) {
      $dependencies[] = array('name' => 'Centralized Users (1.01+)', 'url' => 'http://get-simple.info/extend/plugin/centralised-users/657/');
    }
    if (!function_exists('i18n_init')) {
      $dependencies[] = array('name' => 'I18N (3.2.3+)', 'url' => 'http://get-simple.info/extend/plugin/i18n/69/');
    }
    if (!function_exists('get_i18n_search_results')) {
      $dependencies[] = array('name' => 'I18N Search (2.11+)', 'url' => 'http://get-simple.info/extend/plugin/i18n-search/82/');
    }
    
    return $dependencies;
  }
  
  # load schema(s)
  public function getSchema($tables=array()) {
    if (!is_array($tables)) $tables = array($tables);
    foreach ($tables as $table) {
      if (!isset($this->schema[$table]) && $this->matrix->tableExists($table)) {
        $this->schema[$table] = $this->matrix->getSchema($table);
        unset($this->schema[$table]['fields']['id']);
      }
    }
    return $this->schema;
  }
  
  # loads main board array (categories and forums)
  public function loadBoard() {
    $this->board = array();
    $this->board['categories'] = $this->categories = $this->matrix->query('SELECT * FROM '.self::TABLE_CATEGORIES.' ORDER BY order ASC', 'MULTI', false, 'id');
    $this->forums     = $this->matrix->query('SELECT * FROM '.self::TABLE_FORUMS.' ORDER BY order ASC', 'MULTI', false, 'id');
    // adds forums to the categories array (threaded)
    foreach ($this->forums as $forum) {
      if (isset($this->board['categories'][$forum['category']])) {
        $this->board['categories'][$forum['category']]['forums'][$forum['id']] = $forum; 
      }
    }
  }
  
  # parse the URI
  public function parseURI() {
    // load essential globals for changing the 404 error messages
    global $id, $uri, $data_index;
    
    // parse uri
    $tmpuri = trim(str_replace('index.php', '', $_SERVER['REQUEST_URI']), '/#');
    $tmpuri = str_replace('?id=', '', $tmpuri);
    $tmpuri = preg_split('#(&|\?|\/&|\/\?)#', $tmpuri);
    $tmpuri = reset($tmpuri);
    $tmpuri = explode('/', $tmpuri);
    $slug = end($tmpuri);
    $this->slug = $slug;
    
    if (in_array($this->config['slugs']['home'], $tmpuri)) {
      // pagination
      if (!isset($_GET['page'])) $_GET['page'] = 1;
      
      // fix slug for pretty urls
      if (!$this->matrix->getPrettyURLS()) {
        end($_GET);
        if (key($_GET) == 'page') prev($_GET);
        $this->slug = current($_GET);
        foreach ($_GET as $key => $get) {
          if (!in_array($get, $tmpuri)) {
            if ($key != 'page') $tmpuri[$key] = $get;
          }
        }
      }
      else {
        $end = end($tmpuri);
        $tmp = explode('-', $end);
        
        // fix pagination
        if ($tmp[0] == 'page' && isset($tmp[1]) && is_numeric($tmp[1])) {
          $_GET['page'] = $tmp[1];
          $tmp = array_slice($tmpuri, -2, 1);
          sort($tmp);
          $this->slug = $tmp[0];
        }
      }
      
      $this->uri = $tmpuri;
      return $tmpuri;
    }
    else return false;
  }
  
  # get category url
  public function getCategoryURL($category) {
    if (is_array($category) && isset($category['slug'])) {
      return str_replace('%category%', $category['slug'], $this->config['urls']['category']);
    }
  }
  
  # get forum url
  public function getForumURL($forum, $type='forum', $page=null) {
    if (is_array($forum) && isset($forum['slug'])) {
      if (is_numeric($page)) $page = str_replace('$1', $page, $this->config['urls']['paginate']);
      return str_replace('%forum%', $forum['slug'], $this->config['urls'][$type].$page);
    }
  }
  
  # get topic url
  public function getTopicURL($topic, $type='topic', $page=null) {
    // pagination
    if (is_numeric($page)) $page = str_replace('$1', $page, $this->config['urls']['paginate']);
    
    // return
    if (is_array($topic) && isset($topic['slug'])) {
      return str_replace('%topic%', $topic['slug'], $this->config['urls'][$type].$page);
    }
    else {
      return str_replace('%topic%', $topic, $this->config['urls'][$type].$page);
    }
  }
  
  # get edit topic url
  public function getEditTopicURL($topic) {
    if (is_array($topic) && isset($topic['slug'])) {
      return str_replace('%topic%', $topic['slug'], $this->config['urls']['edit-topic']);
    }
  }

  # get delete topic url
  public function getDeleteTopicURL($topic) {
    if (is_array($topic)) {
      return str_replace('%topic%', $topic['slug'], $this->config['urls']['delete-topic']);
    }
  }
  
  # get edit post url
  public function getEditPostURL($post, $topic) {
    if (is_array($post) && is_array($topic)) {
      return str_replace(array('%topic%', '%id%'), array($topic['slug'], $post['id']), $this->config['urls']['edit-post']);
    }
  }
  
  # get delete post url
  public function getDeletePostURL($post, $topic) {
    if (is_array($post) && is_array($topic)) {
      return str_replace(array('%topic%', '%id%'), array($topic['slug'], $post['id']), $this->config['urls']['delete-post']);
    }
  }
  
  # checks to see that current page is in the board
  public function isBoard() {
    if (!empty($this->uri) && in_array($this->config['slug'], $this->uri)) return true;
    else return false;
  }
  
  # destroy tables
  public function destroyTables() {
    $tables = array(self::TABLE_CATEGORIES, self::TABLE_FORUMS, self::TABLE_TOPICS, self::TABLE_POSTS, self::TABLE_CONFIG);
    $tables = array_map('trim', $tables);
    foreach ($tables as $table) {
      $this->matrix->deleteTable($table);
    }
  }
  
  # create tables
  public function createTables() {
    $tables = $this->tables;
    
    // reset main config
    if ($this->matrix->tableExists('CU_board_settings')) {
      $this->matrix->deleteTable('CU_board_settings');
    }
    
    // main array
    include($this->directories['plugins']['php']['dir'].'admin/tables.php');
    $this->core->buildSchema($tables);
  }
  
  # table compatibility
  public function fixCompatibility() {
    $matrixpath = GSDATAOTHERPATH.'matrix/';
    
    // category
    $categories = glob($matrixpath.self::TABLE_CATEGORIES.'/*.xml');
    foreach ($categories as $category) {
      $id = trim(str_replace(array($matrixpath.self::TABLE_CATEGORIES.'/', '.xml'), '', $category));
      $record = $this->matrix->recordExists(self::TABLE_CATEGORIES, $id);
      $update = array();
      if (!isset($record['slug'])) {
        $update['slug'] = $record['name'];
      }
      $this->matrix->updateRecord(self::TABLE_CATEGORIES, $id, $update);
    }
    
    // forum
    $forums = glob($matrixpath.self::TABLE_FORUMS.'/*.xml');
    foreach ($forums as $forum) {
      $id = trim(str_replace(array($matrixpath.self::TABLE_FORUMS.'/', '.xml'), '', $forum));
      $record = $this->matrix->recordExists(self::TABLE_FORUMS, $id);
      $update = array();
      if (!isset($record['slug'])) {
        $update['slug'] = $record['name'];
      }
      $this->matrix->updateRecord(self::TABLE_FORUMS, $id, $update);
    }
    
    // topics
    $topics = glob($matrixpath.self::TABLE_TOPICS.'/*.xml');
    foreach ($topics as $topic) {
      $id = trim(str_replace(array($matrixpath.self::TABLE_TOPICS.'/', '.xml'), '', $topic));
      $record = $this->matrix->recordExists(self::TABLE_TOPICS, $id);
      $update = array();
      if (!isset($record['slug'])) {
        $update['slug'] = $record['subject'];
      }
      $this->matrix->updateRecord(self::TABLE_TOPICS, $id, $update);
    }
    
    // posts
    
    return true;
  }
  
  # get backups
  public function getBackups() {
    // fills in the backups array and sorts it accordingly (latest first)
    $backups = array();
    foreach (glob($this->directories['backups'].'*.zip') as $backup) {
      $tmp = explode('/', $backup);
      $filename = end($tmp);
      $file = $filename;
      $filename = substr($filename, 0, strlen($filename)-4);
      $date = filemtime($backup);
      $backups[$date]['timestamp']  = $date;
      $backups[$date]['date']       = date('r', $date);
      $backups[$date]['link']       = str_replace(GSROOTPATH, $this->matrix->getSiteURL(), $this->directories['backups'].$file);
      $backups[$date]['path']       = $backup;
      $backups[$date]['file']       = $file;
    }
    ksort($backups);
    $backups = array_reverse($backups);
    return $backups;
  }
  
  # backup board
  public function backup() {
    $path = trim(str_replace(GSDATAOTHERPATH, '/data/other/', $this->directories['backups']));
    $backup = GSROOTPATH.$path.time().'.zip';
    $tables = array(self::TABLE_CATEGORIES, self::TABLE_FORUMS, self::TABLE_TOPICS, self::TABLE_POSTS, self::TABLE_CONFIG);
    
    $files = array();
    foreach ($tables as $table) {
      $files[] = $this->matrix->backupTable($table, $path);
    }
    
    $zip = new ZipArchive();
    if ($zip->open($backup, ZipArchive::CREATE)===TRUE) {
      // add files to archive
      foreach ($files as $file) {
        $tmp = explode('/', $file);
        $zip->addFile($file, end($tmp));
      }
      $zip->close();
      
      // remove files from folder
      foreach ($files as $file) {
        unlink($file);
      }
      return $backup;
    }
    else return false;
    
  }
  
  # restore board
  public function restore($timestamp=false) {
    // load backups
    if ($timestamp) {
      $backups = glob($this->directories['backups'].$timestamp.'.zip');
    }
    else {
      $backups = glob($this->directories['backups'].'*.zip');
    }
    
    // extract files
    if ($backups) {
      $backups = array_reverse($backups);
      $file = $backups[0];
      
      $zip = new ZipArchive();
      if ($zip->open($file)===TRUE) {
        $files = array();
        // get zip filenames (taken from comments on PHP site)
        for ($i = 0; $i < $zip->numFiles; $i++) { 
          $files[] = $zip->statIndex($i); 
        }
        foreach ($files as $key => $f) $files[$key] = $f['name']; 

        // unzips files
        $zip->extractTo(GSDATAOTHERPATH.'matrix/');
        $zip->close();
        
        // restore from zips
        foreach ($files as $f) {
          $table = explode('_', $f);
          $this->matrix->restoreTable(reset($table), 'data/other/matrix/', str_replace('.zip', '', $f));
          unlink(GSDATAOTHERPATH.'matrix/'.$f);
        }
        return $file;
      }
      else return false;
    }
    else return false;
  }
  
  # reset the board
  public function reset() {
    if ($this->backup()) {
      $this->destroyTables();
      #$this->createTables();
      return true;
    }
    else return false;
  }
  
  # get users
  public function getUsers($key) {
    $users = $this->core->getUsers($key);
    foreach ($users as $key => $user) {
      $users[$key]['posts'] = $this->matrix->query('SELECT id FROM '.self::TABLE_POSTS.' WHERE author = '.$user['id'], 'COUNT');
    }
    return $users;
  }
  
  # get category
  private function getCategory($id, $key='id') {
    return $this->matrix->query('SELECT * FROM '.self::TABLE_CATEGORIES.' WHERE id = '.$id, 'SINGLE');
  }
  
  # get forum
  private function getForum($id, $key='id') {
    return $this->matrix->query('SELECT * FROM '.self::TABLE_FORUMS.' WHERE id = '.$id, 'SINGLE');
  }
  
  # get topic
  private function getTopic($id, $key='id') {
    return $this->matrix->query('SELECT * FROM '.self::TABLE_TOPICS.' WHERE id = '.$id, 'SINGLE');
  }
  
  # get post (individual)
  private function getPost($id, $key='id') {
    return $this->matrix->query('SELECT * FROM '.self::TABLE_POSTS.' WHERE id = '.$id, 'SINGLE');
  }
  
  # get posts
  private function getPosts($id, $key='id') {
    $posts = $this->matrix->query('SELECT * FROM '.self::TABLE_POSTS.' WHERE topic = '.$id.' ORDER BY credate DESC', 'MULTI');
    if ($posts) {
      $total = count($posts);
      $user   = $this->matrix->query('SELECT * FROM '.MatrixCUsers::TABLE_USERS.' WHERE id = '.$posts[$total-1]['author'], 'SINGLE');
      $posts['results'] = $posts;
      $posts['total'] = $total;
      $posts['latest']['post'] = $posts[$total-1];
      $posts['latest']['user'] = $user;
    }
    else {
      $posts['results'] = array();
      $posts['total'] = 0;
      $posts['latest']['post'] = false;
      $posts['latest']['user'] = false;
    }
    return $posts;
  }
  
  # load themes
  public function loadThemes($save=false) {
    $this->getSchema(array(self::TABLE_CONFIG));
    
    $templates = array();
    foreach (glob($this->directories['themes'].'*.xml') as $template) {
      $tmp = explode('/', $template);
      $tmp = end($tmp);
      $tmp = trim(str_replace('.xml', '', $tmp));
      $templates[] = $tmp;
    }
    
    // saving the templates list
    if ($save) {
      $this->schema[self::TABLE_CONFIG]['fields']['theme']['options'] = $this->matrix->implodeTrim("\n", $templates);
      $this->matrix->modSchema(self::TABLE_CONFIG, $this->schema[self::TABLE_CONFIG]);
    }
    
    if (empty($templates)) $templates = false;
    
    return $templates;
  }
  
  # create theme
  public function createTheme($name) {
    $return = false;
    
    if (!file_exists($this->directories['themes'].$name.'.xml')) {
      include(GSPLUGINPATH.self::FILE.'/php/display/default.php');
      $xml = Array2XML::createXML('channel', $array['channel']);
      $return = $xml->save($this->directories['themes'].$name.'.xml');
      
      // add template to dropdown
      $this->loadThemes($save=true);
    }
    
    return $return;
  }
  
  # edit theme
  public function editTheme($name, $array) {
  }
  
  # delete theme
  public function deleteTheme($name) {
  }
  
  # include template
  public function includeTemplate($template, $vars=array()) {
    $file = XML2Array::createArray(file_get_contents($this->directories['themes'].$this->config['theme'].'.xml'));
    if (isset($file['channel']['item'][$template]['@cdata'])) {
      foreach ($vars as $key => $var) {
        ${$key} = $var;
      }
      eval("?>".$file['channel']['item'][$template]['@cdata']);
    }
  }
  
  # page type
  public function pageType() {
    $return = null;
    
    // main page
    if ($this->slug == $this->config['slug']) {
      $return = 'home';
    }
    // category
    elseif (in_array('category', $this->uri) || isset($this->uri['category'])) {
      $return = 'category';
    }
    // forum
    elseif (in_array('forum', $this->uri) || isset($this->uri['forum'])) {
      $return = 'forum';
    }
    // create a topic
    elseif (in_array('create-topic', $this->uri) || isset($this->uri['create-topic'])) {
      $return = 'create-topic';
    }
    // edit a topic
    elseif (in_array('edit-topic', $this->uri) || isset($this->uri['edit-topic'])) {
      $return = 'edit-topic';
    }
    // delete topic
    elseif (in_array('delete-topic', $this->uri) || isset($this->uri['delete-topic'])) {
      $return = 'delete-topic';
    }
    // topic
    elseif (in_array('topic', $this->uri) || isset($this->uri['topic'])) {
      $return = 'topic';
    }
    // reply
    elseif ((in_array('reply', $this->uri) || isset($this->uri['reply'])) && $this->core->loggedIn()) {
      $return = 'reply';
    }
    // edit post
    elseif ((in_array('edit-post', $this->uri) || isset($this->uri['edit-post'])) && $this->core->loggedIn()) {
      $return = 'edit-post';
    }
    // delete post
    elseif ((in_array('delete-post', $this->uri) || isset($this->uri['delete-post'])) && $this->core->loggedIn()) {
      $return = 'delete-post';
    }
    // search
    elseif (in_array($this->config['search-slug'], $this->uri) || isset($_GET[$this->config['search-slug']])) {
      $return = 'search';
    }
    // cpanel
    elseif ((in_array('cpanel', $this->uri) || isset($_GET['cpanel'])) && $this->core->loggedIn() && $this->core->isAdmin($_SESSION['cuser'])) {
      $return = 'cpanel';
    }
    // return
    return $return;
  }
  
  # load breadcrumb trail array
  public function getBreadcrumbs() {
    $crumbs = array();
    if ($this->isBoard()) {
      $type = $this->pageType();
      
      $crumbs[] = array('title' => $this->config['title'], 'slug' => $this->config['slug'], 'url' => $this->config['urls']['home']); 
      
      if ($type == 'home') {
      }
      elseif ($type == 'category') {
        $category = $this->matrix->query('SELECT * FROM '.self::TABLE_CATEGORIES.' WHERE slug = "'.$this->slug.'"', 'SINGLE');
        $crumbs[] = array('title' => $category['name'], 'slug' => $category['slug'], 'url' => $this->getCategoryURL($category));
      }
      elseif ($type == 'forum' || $type == 'create-topic') {
        $forum = $this->matrix->query('SELECT * FROM '.self::TABLE_FORUMS.' WHERE slug = "'.$this->slug.'"', 'SINGLE');
        if ($forum) {
          $category = $this->matrix->query('SELECT id, name, slug FROM '.self::TABLE_CATEGORIES.' WHERE id = "'.$forum['category'].'"', 'SINGLE');
          if ($category) $crumbs[] = array('title' => $category['name'], 'slug' => $category['slug'], 'url' => $this->getCategoryURL($category));
          $crumbs[] = array('title' => $forum['name'], 'slug' => $forum['slug'], 'url' => $this->getForumURL($forum));
        }
      }
      elseif ($type == 'topic' || $type == 'edit-topic') {
        $topic = $this->matrix->query('SELECT * FROM '.self::TABLE_TOPICS.' WHERE slug = "'.$this->slug.'"', 'SINGLE');
        if ($topic) {
          $forum = $this->matrix->query('SELECT * FROM '.self::TABLE_FORUMS.' WHERE id = "'.$topic['forum'].'"', 'SINGLE');
          if ($forum) {
            $category = $this->matrix->query('SELECT id, name, slug FROM '.self::TABLE_CATEGORIES.' WHERE id = "'.$forum['category'].'"', 'SINGLE');
            if ($category) $crumbs[] = array('title' => $category['name'], 'slug' => $category['slug'], 'url' => $this->getCategoryURL($category));
            $crumbs[] = array('title' => $forum['name'], 'slug' => $forum['slug'], 'url' => $this->getForumURL($forum));
          }
          $crumbs[] = array('title' => $topic['subject'], 'slug' => $topic['slug'], 'url' => $this->getTopicURL($forum));
        }
      }
      elseif ($type == 'search') {
        $crumbs[] = array('title' => i18n_r(MatrixCUsers::FILE.'/SEARCH'), 'slug' => $this->config['search-slug'], 'url' => $this->config['urls']['search']);
      }
      elseif ($type == 'cpanel') {
        $crumbs[] = array('title' => i18n_r(self::FILE.'/PLUGIN_SIDEBAR'), 'slug' => $this->config['slugs']['cpanel'], 'url' => $this->config['urls']['cpanel']);
      }
    }
    
    return $crumbs;
  }
  
  # output breadcrumb trail
  public function displayBreadcrumbs($crumbs, $delim = '&nbsp;&nbsp;&bull;&nbsp;&nbsp;') {
    if (is_array($crumbs)) {
      // setup array to build anchors
      $output = array();
      foreach ($crumbs as $crumb) {
        $output[] = '<a href="'.$crumb['url'].'" class="'.$crumb['slug'].'">'.$crumb['title'].'</a>';
      }
      
      // output
      echo implode($delim, $output);
    }
  }
  
  # get topics (for a particular forum
  public function getTopics($forum) {
    if (!is_array($forum)) {
      $forum = array('id' => $forum);
    }
    if (isset($forum['id'])) {
      // get latest post
      $topics = $this->matrix->query('SELECT * FROM '.self::TABLE_TOPICS.' WHERE forum = '.$forum['id'].' ORDER BY type DESC, pubdate DESC'); 
      if ($topics) {
        $tmp    = $this->matrix->query('SELECT * FROM '.self::TABLE_POSTS.' WHERE topic = '.$topics[0]['id'], 'SINGLE');
        $user   = $this->matrix->query('SELECT * FROM '.MatrixCUsers::TABLE_USERS.' WHERE id = '.$tmp['author'], 'SINGLE');
        $topics['results'] = $topics;
        $topics['total'] = count($topics['results']);
        $topics['latest']['topic'] = $topics[0];
        $topics['latest']['post'] = $tmp;
        $topics['latest']['user'] = $user;
      } 
      else {
        $topics['results'] = array();
        $topics['total'] = 0;
        $topics['latest'] = false;
      }
      return $topics;
    }
  }
  
  # display home
  public function displayHome() {
    global $data_index;
    // metadata
    $data_index->title    = $this->config['title'];
    $data_index->date     = time();
    $data_index->metak    = '';
    $data_index->meta     = '';
    $data_index->url      = $this->slug;
    $data_index->parent   = '';
    $data_index->template = $this->config['template'];
    $data_index->private  = '';
    
    // content
    ob_start();
    
    foreach ($this->board['categories'] as $category) {
      $this->displayCategory($category['slug']);
    }
    
    $this->includeTemplate('home', array());
    
    $data_index->content .= ob_get_contents();
    ob_end_clean();
    
  }
  
  # delete category
  public function deleteCategory($id, $backup=true) {
    if ($this->matrix->recordExists(self::TABLE_CATEGORIES, $id)) {
      if ($backup) $this->backup();
      $status = array();
      $status[] = $this->matrix->deleteRecord(self::TABLE_CATEGORIES, $id);
      if (isset($this->board['categories'][$id]['forums'])) {
        $forums = $this->board['categories'][$id]['forums'];
        foreach ($forums as $forum) $status[] = $this->deleteForum($forum['id'], false);
      }
      if (!in_array(false, $status)) return true;
      else return false;
    }
    else return false;
  }
  
  # display category
  public function displayCategory($slug, $meta=false) {
    $category = $this->matrix->query('SELECT * FROM '.self::TABLE_CATEGORIES.' WHERE slug = "'.trim($slug).'"', 'SINGLE');
    if ($category) {
      global $data_index;
      
      // metadata
      if ($meta) {
        $data_index->title    = $category['name'];
        $data_index->date     = time();
        $data_index->url      = $this->slug;
      }
      
      // content
      ob_start();
      
        // variables
        $forums = $this->matrix->query('SELECT * FROM '.self::TABLE_FORUMS.' WHERE category = '.$category['id'].' ORDER BY order ASC'); 
        $vars = array('category' => $category, 'forums' => $forums);
        
        // template
        $this->includeTemplate('category', $vars);

      $data_index->content .= ob_get_contents();
      ob_end_clean();
    }
  }
  
  # delete forum
  public function deleteForum($id, $backup=true) {
    $status = array();
    if ($this->matrix->recordExists(self::TABLE_FORUMS, $id)) {
      if ($backup) $this->backup();
      $status[] = $this->matrix->deleteRecord(self::TABLE_FORUMS, $id);
      $topics = $this->getTopics($id);
      foreach ($topics['results'] as $topic) $status[] = $this->deleteTopic($topic['id']);
      if (!in_array(false, $status)) return true;
      else return false;
    }
    else return false;
  }
  
  # display forum
  public function displayForum($slug) {
    global $data_index;
    $forum = $this->matrix->query('SELECT * FROM '.self::TABLE_FORUMS.' WHERE slug = "'.$slug.'"', 'SINGLE');
    if ($forum) {
      // metadata
      $data_index->title    = $forum['name'];
      $data_index->date     = time();
      $data_index->url      = $this->slug;
      
      // create topic
      if (!empty($_POST['createTopic'])) {
        $this->createTopic($_POST);
      }
      // edit topic
      if (!empty($_POST['editTopic'])) {
        $this->editTopic($_POST['topic'], $_POST['post'], $_POST);
      }
      // delete topic
      if (!empty($_POST['deleteTopic'])) {
        $this->deleteTopic($_POST['id']);
      }
      
      // content
      ob_start();
        $announcements = $this->matrix->query('SELECT * FROM '.self::TABLE_TOPICS.' WHERE type = 2 ORDER BY pubdate DESC'); 
        $topics = $this->matrix->query('SELECT * FROM '.self::TABLE_TOPICS.' WHERE forum = '.$forum['id'].' ORDER BY type DESC, pubdate DESC');
        
        $users = $this->getUsers('id');
        foreach ($topics as $key => $topic) {
          if ($topic['type'] == 2) unset($topics[$key]);
        }
        
        if ($_GET['page'] > 1) $topics = array_merge($announcements, $topics);
        
        $topics = $this->matrix->paginateQuery($topics, $key='page', $max=$this->config['topics-per-page'], $range=2, $url=$this->getForumURL($forum), $delim=$this->config['urls']['paginate'], $display=array('first'=>'|&lt;&lt;', 'prev'=>'&lt;', 'next'=>'&gt;', 'last'=>'&gt;&gt;|'));
        
        foreach ($topics['results'] as $key => $result) {
          $topics['results'][$key]['type'] = strtolower($this->config['topic-types'][$result['type']]);
          $topics['results'][$key]['status'] = strtolower($this->config['topic-statuses'][$result['status']]);
          $topics['results'][$key]['author'] = $users[$result['author']];
        }
        
        // variables
        $vars = array('forum' => $forum, 'topics' => $topics['results']);
        
        if ($topics['results']) echo '<div class="page_navigation">'.$topics['links'].'</div>';
        $this->includeTemplate('forum', $vars);
        if ($topics['results']) echo '<div class="page_navigation">'.$topics['links'].'</div>';
        echo '<div style="overflow: hidden;">';
        if ($this->core->loggedIn()) echo '<a href="'.$this->getForumURL($forum, 'create-topic').'" class="button reply">'.i18n_r(self::FILE.'/CREATE_TOPIC').'</a>';
        echo '</div>';
        
      $data_index->content .= ob_get_contents();
      ob_end_clean();
    }
  }
  
  # display topic
  public function displayTopic($slug) {
    $topic = $this->matrix->query('SELECT * FROM '.self::TABLE_TOPICS.' WHERE slug = "'.$slug.'"', 'SINGLE');
    if ($topic) {
      global $data_index;
      
      // increase view count
      $topic['views'] = $topic['views'] + 1;
      $this->matrix->updateRecord(self::TABLE_TOPICS, $topic['id'], $topic);
      
      // metadata
      $data_index->title    = $topic['subject'];
      $data_index->date     = time();
      $data_index->url      = $this->slug;
      
      // post reply
      if (!empty($_POST['postReply'])) {
        $_POST['credate'] = $_POST['pubdate'] = time();
        $_POST['author']  = $_POST['editor']  = $_SESSION['cuser']['id'];
        $this->matrix->createRecord(self::TABLE_POSTS, $_POST);
      }
      // edit post
      if (!empty($_POST['editPost'])) {
        $this->editPost($_POST['id'], $_POST);
      }
      // delete post
      if (!empty($_POST['deletePost'])) {
        $this->deletePost($_POST['id']);
      }
      
      // content
      ob_start();
        $users = $this->getUsers('id');
        
        // get user posts count
        $allPosts = $this->matrix->query('SELECT id, author FROM '.self::TABLE_POSTS, 'SINGLE', true, 'author'); 
        foreach ($allPosts as $key => $post) {
          if (array_key_exists($key, $users)) {
            if (!isset($users[$key]['posts'])) {
              $users[$key]['posts'] = 0;
            }
            $users[$key]['posts']++;
          }
        }
        
        // load topic posts
        $posts = $this->matrix->query('SELECT * FROM '.self::TABLE_POSTS.' WHERE topic = '.$topic['id']);
        foreach ($posts as $key => $post) {
          // get post number
          $posts[$key]['num'] = $key + 1;
          $posts[$key]['content'] = $this->parser->bbcode($posts[$key]['content']);
        }
        $posts = $this->matrix->paginateQuery($posts, $key='page', $max=$this->config['posts-per-page'], $range=2, $url=$this->getTopicURL($topic), $delim=$this->config['urls']['paginate'], $display=array('first'=>'|&lt;&lt;', 'prev'=>'&lt;', 'next'=>'&gt;', 'last'=>'&gt;&gt;|'));

        // variables
        $vars = array('topic' => $topic, 'posts' => $posts['results'], 'users' => $users);
        
        // display
        echo '<div class="page_navigation">'.$posts['links'].'</div>';
        $this->includeTemplate('topic', $vars);
        echo '<div class="page_navigation">'.$posts['links'].'</div>';
        
        // only show button if topic is open
        echo '<div style="overflow: hidden;">';
        if ($topic['status'] == 0 && $this->core->loggedIn()) {
          echo '<a href="'.$this->getTopicURL($topic, 'reply').'" class="button reply">'.i18n_r(self::FILE.'/POST_REPLY').'</a>';
        }
        echo '</div>';
      $data_index->content .= ob_get_contents();
      ob_end_clean();
    }
  }
  
  # create topic
  public function createTopic($array) {
    // fill in missing variables
    $array['slug'] = $this->matrix->str2slug($array['post-subject']);
    $array['credate'] = $array['pubdate'] = time();
    $array['author']  = $_SESSION['cuser']['id'];
    
    if (!isset($array['type'])) $array['type'] = 0;
    if (!isset($array['status'])) $array['status'] = 0;
    
    // create records
    $topic = $this->matrix->createRecord(self::TABLE_TOPICS, $array);
    if ($topic) {
      $array['topic'] = (int)$this->matrix->getNextRecord(self::TABLE_TOPICS)-1;
    }
    
    $post  = $this->matrix->createRecord(self::TABLE_POSTS, $array);
    
    // return status
    if ($topic && $post) {
      $this->matrix->refreshIndex();
      return true;
    }
    else return false;
  }
  
  # delete post
  public function deletePost($id) {
    $this->matrix->deleteRecord(self::TABLE_POSTS, $id);
    $this->matrix->refreshIndex();
  }
  
  # edit topic
  public function editTopic($topicID, $postID, $array) {   
    // fill in missing variables
    $array['slug'] = $this->matrix->str2slug($array['post-subject']);
    $array['pubdate'] = time();
    $array['editor']  = $_SESSION['cuser']['id'];
    
    // create records
    $topic = $this->matrix->updateRecord(self::TABLE_TOPICS, $topicID, $array);
    $post  = $this->matrix->updateRecord(self::TABLE_POSTS, $postID, array('content' => $array['post-content'], 'editor' => $array['editor'], 'pubdate' => $array['pubdate']));
    
    // return status
    if ($topic && $post) {
      $this->matrix->refreshIndex();
      return true;
    }
    else return false;
  }
  
  # move topic
  public function moveTopic($topicID, $forumID) {
    $topic = $this->matrix->recordExists(self::TABLE_TOPICS, $topicID);
    $forum = $this->matrix->recordExists(self::TABLE_FORUMS, $forumID);
    if ($topic && $forum) {
      $this->matrix->refreshIndex();
      return $this->matrix->updateRecord(self::TABLE_TOPICS, $topicID, array('forum' => $forumID));
    }
    else return false;
  }
  
  # delete topic
  public function deleteTopic($id) {
    $status = array();
    $topic = $this->getTopic($id);
    $posts = $this->getPosts($id);
    if ($topic && $posts) {
      // delete posts
      foreach ($posts['results'] as $post) { 
        $status[] = $this->matrix->deleteRecord(self::TABLE_POSTS, $post['id']);
      }
      
      // delete topic
      $status[] = $this->matrix->deleteRecord(self::TABLE_TOPICS, $id);
      if (!in_array(false, $status)) {
        $this->matrix->refreshIndex();
        return true;
      }
      else return false;
    }
  }
  
  # display create topic page
  public function displayCreateTopic($slug) {
    $forum = $this->matrix->query('SELECT * FROM '.self::TABLE_FORUMS.' WHERE slug = "'.$slug.'"', 'SINGLE');
    if ($forum) {
      global $data_index;
      
      // metadata
      $data_index->title    = i18n_r(self::FILE.'/CREATE_TOPIC').' - '.$forum['name'];
      $data_index->date     = time();
      $data_index->url      = $this->slug;
      
      // content
      ob_start();
      ?>
      <form method="post" action="<?php echo $this->getForumURL($forum); ?>">
        <div class="tableWrap">
          <table>
            <thead>
              <tr>
                <th colspan="100%" class="head1"><?php echo i18n_r(self::FILE.'/CREATE_TOPIC'); ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th class="row2" style="width: 20%;"><?php echo i18n_r(MatrixCUsers::FILE.'/SUBJECT'); ?></th>
                <td class="row1" style="width: 80%;">
                  <?php $this->matrix->displayField(self::TABLE_TOPICS, 'subject'); ?>
                </td>
              </tr>
              <?php if ($this->core->isMod($_SESSION['cuser'])) {?>
              <tr>
                <th class="row2" style="width: 20%;"><?php echo i18n_r(MatrixCUsers::FILE.'/TYPE'); ?></th>
                <td class="row1" style="width: 80%;">
                  <?php $this->matrix->displayField(self::TABLE_TOPICS, 'type'); ?>
                </td>
              <tr>
                <th class="row2" style="width: 20%;"><?php echo i18n_r(MatrixCUsers::FILE.'/STATUS'); ?></th>
                <td class="row1" style="width: 80%;">
                  <?php $this->matrix->displayField(self::TABLE_TOPICS, 'status'); ?>
                </td>
              </tr>
              <?php } ?>
              <tr>
                <th class="row2" style="width: 20%;"><?php echo i18n_r(MatrixCUsers::FILE.'/TAGS'); ?></th>
                <td class="row1" style="width: 80%;">
                  <?php $this->matrix->displayField(self::TABLE_TOPICS, 'tags'); ?>
                </td>
              </tr>
              <tr>
                <th class="row2" style="width: 20%;"><?php echo i18n_r(MatrixCUsers::FILE.'/CONTENT'); ?></th>
                <td class="row1" style="width: 80%;">
                  <?php $this->matrix->displayField(self::TABLE_POSTS, 'content'); ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <input type="hidden" name="forum" value="<?php echo $forum['id']; ?>">
        <input type="submit" class="reply" name="createTopic" value="<?php echo i18n_r(self::FILE.'/CREATE_TOPIC'); ?>">
      </form>
      <?php
      $data_index->content .= ob_get_contents();
      ob_end_clean();
    }
  }
  
  # display edit post page
  public function displayEditTopic($slug) {
    $post = false;
    $topic = $this->matrix->query('SELECT * FROM '.self::TABLE_TOPICS.' WHERE slug = "'.$slug.'"', 'SINGLE');
    if ($topic) {
      $forum = $this->matrix->query('SELECT * FROM '.self::TABLE_FORUMS.' WHERE id = "'.$topic['forum'].'"', 'SINGLE');
      $post = $this->matrix->query('SELECT * FROM '.self::TABLE_POSTS.' WHERE topic = '.$topic['id'].' ORDER BY credate ASC', 'SINGLE');
    }
    
    if ($topic && $topic['status'] == 0 && $post && ($post['author'] == $_SESSION['cuser']['id'] || $this->core->isMod($_SESSION['cuser']))) {
      global $data_index;
      
      // metadata
      $data_index->title    = i18n_r(self::FILE.'/EDIT_TOPIC').' - '.$topic['subject'];
      $data_index->date     = time();
      $data_index->url      = $slug;
      
      // content
      ob_start();
      ?>
      <form method="post" action="<?php echo $this->getForumURL($forum); ?>">
        <div class="tableWrap">
          <table>
            <thead>
              <tr>
                <th colspan="100%" class="head1"><?php echo i18n_r(self::FILE.'/EDIT_TOPIC'); ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th class="row2" style="width: 20%;"><?php echo i18n_r(MatrixCUsers::FILE.'/SUBJECT'); ?></th>
                <td class="row1" style="width: 80%;">
                  <?php $this->matrix->displayField(self::TABLE_TOPICS, 'subject', $topic['subject']); ?>
                </td>
              </tr>
              <?php if ($this->core->isMod($_SESSION['cuser'])) {?>
              <tr>
                <th class="row2" style="width: 20%;"><?php echo i18n_r(MatrixCUsers::FILE.'/TYPE'); ?></th>
                <td class="row1" style="width: 80%;">
                  <?php $this->matrix->displayField(self::TABLE_TOPICS, 'type', $topic['type']); ?>
                </td>
              <tr>
                <th class="row2" style="width: 20%;"><?php echo i18n_r(MatrixCUsers::FILE.'/STATUS'); ?></th>
                <td class="row1" style="width: 80%;">
                  <?php $this->matrix->displayField(self::TABLE_TOPICS, 'status', $topic['status']); ?>
                </td>
              </tr>
              <?php } ?>
              <tr>
                <th class="row2" style="width: 20%;"><?php echo i18n_r(MatrixCUsers::FILE.'/TAGS'); ?></th>
                <td class="row1" style="width: 80%;">
                  <?php $this->matrix->displayField(self::TABLE_TOPICS, 'tags', $topic['tags']); ?>
                </td>
              </tr>
              <tr>
                <th class="row2" style="width: 20%;"><?php echo i18n_r(MatrixCUsers::FILE.'/CONTENT'); ?></th>
                <td class="row1" style="width: 80%;">
                  <?php $this->matrix->displayField(self::TABLE_POSTS, 'content', $post['content']); ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <input type="hidden" name="topic" value="<?php echo $topic['id']; ?>">
        <input type="hidden" name="post" value="<?php echo $post['id']; ?>">
        <input type="submit" class="reply" name="editTopic" value="<?php echo i18n_r('BTN_SAVECHANGES'); ?>">
      </form>
      <?php
      $data_index->content .= ob_get_contents();
      ob_end_clean();
    }
  }
  
  # display delete topic
  public function displayDeleteTopic($slug) {
    $topic = $this->matrix->query('SELECT * FROM '.self::TABLE_TOPICS.' WHERE slug = "'.$slug.'"', 'SINGLE');
    if ($topic && $this->core->isMod($_SESSION['cuser'])) {
      global $data_index;
      $forum = $this->matrix->query('SELECT * FROM '.self::TABLE_FORUMS.' WHERE id = "'.$topic['forum'].'"', 'SINGLE');
      
      // metadata
      $data_index->title    = i18n_r(self::FILE.'/DELETE_POST').' - '.$topic['subject'];
      $data_index->date     = time();
      $data_index->url      = $slug;
      
      // content
      ob_start();
      ?>
      <form method="post" action="<?php echo $this->getForumURL($forum); ?>">
        <div class="tableWrap">
          <table>
            <thead>
              <tr>
                <th colspan="100%" class="head1"><?php echo i18n_r(self::FILE.'/DELETE_TOPIC'); ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="row1" colspan="100%">
                  <p><?php echo i18n_r(self::FILE.'/DELETE_TOPIC_CONFIRM'); ?></p>
                  <input type="hidden" name="id" value="<?php echo $topic['id']; ?>">
                  <input type="submit" name="deleteTopicdeleteTopic" value="<?php echo i18n_r(MatrixCUsers::FILE.'/YES'); ?>">
                  <input type="submit" value="<?php echo i18n_r(MatrixCUsers::FILE.'/NO'); ?>">
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </form>
      <?php
      $data_index->content .= ob_get_contents();
      ob_end_clean();
    }
  }
  
  # edit post
  public function editPost($id, $array) {
    // fill in missing variables
    $array['pubdate'] = time();
    $array['editor']  = $_SESSION['cuser']['id'];
    
    // create records
    $post = $this->matrix->updateRecord(self::TABLE_POSTS, $id, $array);
    
    // return status
    if ($post) {
      $this->matrix->refreshIndex();
      return true;
    }
    else return false;
  }
  
  # display edit post page
  public function displayEditPost($slug, $id) {
    $topic = $this->matrix->query('SELECT * FROM '.self::TABLE_TOPICS.' WHERE slug = "'.$slug.'"', 'SINGLE');
    $post = $this->matrix->query('SELECT * FROM '.self::TABLE_POSTS.' WHERE id = '.$id, 'SINGLE');
    
    if ($topic && $topic['status'] == 0 && $post && ($post['author'] == $_SESSION['cuser']['id'] || $this->core->isMod())) {
      global $data_index;
      
      // metadata
      $data_index->title    = i18n_r(self::FILE.'/EDIT_REPLY').' - '.$topic['subject'];
      $data_index->date     = time();
      $data_index->url      = $this->slug;
      
      // content
      ob_start();
      ?>
      <form method="post" action="<?php echo $this->getTopicURL($topic); ?>">
        <div class="tableWrap">
          <table>
            <thead>
              <tr>
                <th colspan="100%" class="head1"><?php echo i18n_r(self::FILE.'/POST_REPLY'); ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="row2" style="width: 20%;"><?php echo i18n_r(MatrixCUsers::FILE.'/CONTENT'); ?></td>
                <td class="row1" style="width: 80%;">
                  <?php $this->matrix->displayField(self::TABLE_POSTS, 'content', $post['content']); ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
        <input type="submit" class="reply" name="editPost" value="<?php echo i18n_r(self::FILE.'/EDIT_REPLY'); ?>">
      </form>
      <?php
      $data_index->content .= ob_get_contents();
      ob_end_clean();
    }
  }
  
  # display post reply
  public function displayPostReply($slug) {
    $topic = $this->matrix->query('SELECT * FROM '.self::TABLE_TOPICS.' WHERE slug = "'.$slug.'"', 'SINGLE');
    if ($topic && $topic['status'] == 0) {
      global $data_index;
      
      // metadata
      $data_index->title    = i18n_r(self::FILE.'/POST_REPLY').' - '.$topic['subject'];
      $data_index->date     = time();
      $data_index->url      = $this->slug;
      
      // content
      ob_start();
      ?>
      <form method="post" action="<?php echo $this->getTopicURL($topic); ?>">
        <div class="tableWrap">
          <table>
            <thead>
              <tr>
                <th colspan="100%" class="head1">
                  <input type="hidden" name="topic" value="<?php echo $topic['id']; ?>">
                  <?php echo i18n_r(self::FILE.'/POST_REPLY'); ?>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="row2" style="width: 20%;"><?php echo i18n_r(MatrixCUsers::FILE.'/CONTENT'); ?></td>
                <td class="row1" style="width: 80%;">
                  <?php $this->matrix->displayField(self::TABLE_POSTS, 'content'); ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <input type="submit" class="reply" name="postReply" value="<?php echo i18n_r(self::FILE.'/POST_REPLY'); ?>">
      </form>
      <?php
      $data_index->content .= ob_get_contents();
      ob_end_clean();
    }
  }
  
  # display delete post
  public function displayDeletePost($slug, $id) {
    $topic = $this->matrix->query('SELECT * FROM '.self::TABLE_TOPICS.' WHERE slug = "'.$slug.'"', 'SINGLE');
    $post = $this->getPost($id);
    if ($topic && $topic['status'] == 0 && $post) {
      global $data_index;
      
      // metadata
      $data_index->title    = i18n_r(self::FILE.'/DELETE_POST').' - '.$topic['subject'];
      $data_index->date     = time();
      $data_index->url      = $slug;
      
      // content
      ob_start();
      ?>
      <form method="post" action="<?php echo $this->getTopicURL($topic); ?>">
        <div class="tableWrap">
          <table>
            <thead>
              <tr>
                <th colspan="100%" class="head1"><?php echo i18n_r(self::FILE.'/DELETE_POST'); ?></th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="row1" colspan="100%">
                  <p><?php echo i18n_r(self::FILE.'/DELETE_POST_CONFIRM'); ?></p>
                  <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                  <input type="submit" name="deletePost" value="<?php echo i18n_r(MatrixCUsers::FILE.'/YES'); ?>">
                  <input type="submit" value="<?php echo i18n_r(MatrixCUsers::FILE.'/NO'); ?>">
                </td>
              </tr>
              <tr>
                <td class="row2" colspan="100%" style="height: 20px;"></td>
              </tr>
              <tr>
                <td class="row1" colspan="100%">
                  <?php echo $post['content']; ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </form>
      <?php
      $data_index->content .= ob_get_contents();
      ob_end_clean();
    }
  }
  
  # post options
  public function getPostOptions($user, $post, $topic) {
    echo '<div class="options">';
    if ($this->core->loggedIn()) {
      echo '<a href="" class="button reply">'.i18n_r(MatrixCUsers::FILE.'/REPLY').'</a>';
      if ($_SESSION['cuser']['id'] == $user['id'] || $this->core->isMod($_SESSION['cuser'])) {
        if ($post['num'] == 1) {
          echo '<a href="'.$this->getEditTopicURL($topic).'" class="button edit">'.i18n_r(MatrixCUsers::FILE.'/EDIT').'</a>';
        }
        else {
          echo '<a href="'.$this->getEditPostURL($post, $topic).'" class="button edit">'.i18n_r(MatrixCUsers::FILE.'/EDIT').'</a>';
        }
      }
      if ($this->core->isMod($_SESSION['cuser'])) {
        if ($post['num'] == 1) {
          echo '<a href="'.$this->getDeleteTopicURL($topic).'" class="button delete">'.i18n_r(MatrixCUsers::FILE.'/DELETE').'</a>';
        }
        else {
          echo '<a href="'.$this->getDeletePostURL($post, $topic).'" class="button delete">'.i18n_r(MatrixCUsers::FILE.'/DELETE').'</a>';
        }
      }
    }
    echo '</div>';
  }
  
  # display search
  public function displaySearch() {
    global $data_index;
      
    // metadata
    $data_index->title    = i18n_r(MatrixCUsers::FILE.'/SEARCH');
    $data_index->date     = time();
    $data_index->url      = $this->slug;
    
    // content
    ob_start();
    include(GSPLUGINPATH.self::FILE.'/php/display/search.php');
    $data_index->content  .= ob_get_contents();
    ob_end_clean();
  }
  
  # cpanel (for cuser admins)
  public function displayCPanel() {
    global $data_index;
      
    // metadata
    $data_index->title    = i18n_r(self::FILE.'/PLUGIN_SIDEBAR');
    $data_index->date     = time();
    $data_index->url      = $this->slug;
    
    // content
    ob_start();
    include(GSPLUGINPATH.self::FILE.'/php/admin/cpanel.php');
    $data_index->content  .= ob_get_contents();
    ob_end_clean();
  }
  
  # display
  public function display() {
    if ($this->isBoard()) {
      global $data_index;
      
      // metadata
      $this->title404        = (string) $data_index->title;
      $data_index->date      = time();
      $data_index->template  = $this->config['template'];
      $data_index->content   = $this->coreConfig['header-css'];
      
      // header
      ob_start();
      $this->includeTemplate('header', array());
      $data_index->content .= ob_get_contents();
      ob_end_clean();
      
      $type = $this->pageType();
      
      if ($type == 'home') {
        $this->displayHome();
      }
      elseif ($type == 'category') {
        $this->displayCategory($this->slug, $meta=true);
      }
      elseif ($type == 'forum') {
        $this->displayForum($this->slug);
      }
      elseif ($type == 'create-topic') {
        $this->displayCreateTopic($this->slug);
      }
      elseif ($type == 'edit-topic') {
        $this->displayEditTopic($this->slug);
      }
      elseif ($type == 'delete-topic') {
        $this->displayDeleteTopic($this->slug);
      }
      elseif ($type == 'topic') {
        $this->displayTopic($this->slug);
      }
      elseif ($type == 'reply') {
        $this->displayPostReply($this->slug);
      }
      elseif ($type == 'edit-post') {
        $id = array_slice($this->uri, -1, 1); $id = current($id);
        $slug = array_slice($this->uri, -2, 1); $slug = current($slug);
        $this->displayEditPost($slug, $id);
      }
      elseif ($type == 'delete-post') {
        $id = array_slice($this->uri, -1, 1); $id = current($id);
        $slug = array_slice($this->uri, -2, 1); $slug = current($slug);
        $this->displayDeletePost($slug, $id);
      }
      elseif ($type == 'search') {
        $this->displaySearch();
      }
      elseif ($type == 'cpanel') {
        $this->displayCPanel();
      }
      
      // admin link
      if ($this->core->loggedIn() && $this->core->isAdmin($_SESSION['cuser'])) $data_index->content   .= '<div class="admin"><a href="'.$this->config['urls']['cpanel'].'">'.i18n_r(self::FILE.'/PLUGIN_SIDEBAR').'</a></div>';
    
      // footer
      ob_start();
      $this->includeTemplate('footer', array());
      $data_index->content .= ob_get_contents();
      ob_end_clean();
    }

  }
  
  # admin
  public function admin() {
    if ($this->checkDependencies()) {
      $this->loadBoard();
      if (isset($_GET['compatibility'])) {
        $this->fixCompatibility();
      }
      // categories
      if (isset($_GET['category']) && $_GET['category'] == 'create') {
        include_once(GSPLUGINPATH.self::FILE.'/php/admin/create_category.php');
      }
      elseif (isset($_GET['category']) && $this->matrix->recordExists(self::TABLE_FORUMS, $_GET['category']) && !isset($_GET['delete'])) {
        include_once(GSPLUGINPATH.self::FILE.'/php/admin/edit_category.php');
      }
      // forums
      elseif (isset($_GET['forum']) && $_GET['forum'] == 'create') {
        include_once(GSPLUGINPATH.self::FILE.'/php/admin/create_forum.php');
      }
      elseif (isset($_GET['forum']) && $this->matrix->recordExists(self::TABLE_FORUMS, $_GET['forum']) && !isset($_GET['delete'])) {
        include_once(GSPLUGINPATH.self::FILE.'/php/admin/edit_forum.php');
      }
      // template
      elseif (!empty($_GET['template'])) {
        include_once(GSPLUGINPATH.self::FILE.'/php/admin/edit_theme.php');
      }
      // config
      elseif (isset($_GET['config'])) {
        include_once(GSPLUGINPATH.self::FILE.'/php/admin/config.php');
      }
      // backups
      elseif (isset($_GET['backups'])) {
        include_once(GSPLUGINPATH.self::FILE.'/php/admin/backups.php');
      }
      // main
      else {
        include_once(GSPLUGINPATH.self::FILE.'/php/admin/board.php');
      }
    }
    else {
      $dependencies = $this->missingDependencies();
      include(GSPLUGINPATH.self::FILE.'/php/admin/dependencies.php');
    }
  }

  # return search results
  public function returnSearchResults($tags=array(), $words=null, $range=2, $order=null, $lang=null, $url=null, $delim='&page=$1', $display=array('first'=>'|&lt;&lt;', 'prev'=>'&lt;', 'next'=>'&gt;', 'last'=>'&gt;&gt;|')) {
    $page = ($_GET['page'] - 1) * $this->config['posts-per-page'];
    $results = return_i18n_search_results($tags, $words, $page, $max=$this->config['posts-per-page'], $order, $lang);
    $results['links'] = null;
    $pages = $results['pages'] = $results['totalCount'] / $this->config['posts-per-page'];
    
    // format links
    if (!empty($results['results'])) {
      // current
      $current = $page + 1;
        
      // first
      $first = 1;
        
      // prev
      if ($current==1) $prev = 1; 
      else $prev = $current-1;
        
      // next
      if ($results['totalCount']==1) $next = 1; 
      else $next = $current+1;
        
      // last
      $last = $results['pages'];
        
      // display 
        $results['links'] = ''; // initialisation
          
        // first, prev
        if($current!=$first) $results['links'] = '<a class="first" href="'.$url.str_replace('$1', $first, $delim).'">'.$display['first'].'</a>'."\n".'<a class="prev" href="'.$url.str_replace('$1', $prev, $delim).'">'.$display['prev'].'</a>'."\n";
         
        // numbers
        for ($i = ($current - $range); $i < ($current + $range + 1); $i++) {
          if ($i > 0 && $i <= $results['pages']) {
            // current
            if ($i==$current) {
              $results['links'] .= '<span class="current">'.$i.'</span>'."\n";
            }
            // link
            else {
              $results['links'] .= '<a class="page" href="'.$url.str_replace('$1', $i, $delim).'">'.$i.'</a>'."\n";
            }
          }
        }
          
        // next, last
        if($current!=$last) $results['links'] .= '<a class="next" href="'.$url.str_replace('$1', $next, $delim).'">'.$display['next'].'</a>'."\n".'<a class="last" href="'.$url.str_replace('$1', $last, $delim).'">'.$display['last'].'</a>';
      }
    return $results;
  }

  # board search items
  public function SearchItems() {
    if (empty($this->searchItems)) {
      $users = $this->getUsers('id');
      $items = array();
      
      // topics
      $topics = $this->matrix->query('SELECT * FROM '.self::TABLE_TOPICS, 'MULTI', true, 'id');
      
      foreach ($topics as $topic) {
        $id = 'topic-'.$topic['id'];
        $tmp = array();
        $tmp['id'] = self::SEARCHID.$id;
        $tmp['lang'] = null;
        $tmp['date'] = strtotime(date('j F Y', $topic['credate']));
        $tmp['tags'] = $this->matrix->explodeTrim(',', $topic['tags']);
        $tmp['tags'] = array_merge(array(
          '_cuboard', '_topic', '_cuser_'.$users[$topic['author']]['username'],
          '_cre_'.date('Y', $topic['credate']), '_cre_'.date('Ym', $topic['credate']),
          '_pub_'.date('Y', $topic['pubdate']), '_pub_'.date('Ym', $topic['pubdate']),
        ), $tmp['tags']);
        $tmp['title'] = $topic['subject'];
        $tmp['content'] = $topic['subject'];
        $tmp['resultType'] = 't';
        $tmp['author'] = $users[$topic['author']];
        $items[$id] = array_merge($topic, $tmp);
      }
      
      // posts
      $posts = $this->matrix->query('SELECT * FROM '.self::TABLE_POSTS);
      
      foreach ($posts as $post) {
        $id = 'post-'.$post['id'];
        $tmp = array();
        $tmp['id'] = self::SEARCHID.$id;
        $tmp['lang'] = null;
        $tmp['date'] = strtotime(date('j F Y', $post['credate']));
        $tmp['tags'] = array(
          '_cuboard', '_post', '_cuser_'.$users[$post['author']]['username'],
          '_cre_'.date('Y', $post['credate']), '_cre_'.date('Ym', $post['credate']),
          '_pub_'.date('Y', $post['pubdate']), '_pub_'.date('Ym', $post['pubdate'])
          );
        $tmp['title'] = $topics[$post['topic']]['subject'];
        $tmp['content'] = $post['content'];
        $post['topic'] = $topics[$post['topic']];
        $tmp['resultType'] = 'p';
        $tmp['subject'] = $post['topic']['subject'];
        $tmp['author'] = $users[$topic['author']];
        $items[$id] = array_merge($post, $tmp);
      }
      
      $this->searchItems = $items;
      return $items;
    }
  }
  
  # search index
  public function searchIndex() {
    $this->searchItems();
    foreach ($this->searchItems as $item) {
      i18n_search_index_item($item['id'], $lang=null, $item['date'], $item['date'], $item['tags'], $item['title'], $item['content']);
    }
  }
  
  # search item
  public function searchItem($id, $language, $creDate, $pubDate, $score) {
    $this->searchItems();
    if (substr($id, 0, strlen(self::SEARCHID)) == self::SEARCHID) {
      // load data
      $data = $this->searchItems[substr($id, strlen(self::SEARCHID))];
      
      // get key for items of this plugin
      $key = self::SEARCHID;
      
      // translate search result keys into the relevant content
      $transkey = array('title'=>'title', 'description'=>'content', 'content'=>'content', 'link'=>null);
      return new TheMatrixSearchResultItem($data, $key, $id, $transkey, $language, $creDate, $pubDate, $score);
    }
    // item is not from our plugin - maybe from another plugin
    else return null; 
  }
  
  # search item to array
  private function searchItem2Array($table, $item) {
    $array = array();
    if ($this->matrix->tableExists($table)) {
      foreach ($this->schema[$table]['fields'] as $field) {
        $array[$field['name']] = $item->{$field['name']};
      }
    }
    return $array;
  }
  
  # search display
  public function searchDisplay($item, $showLanguage, $showDate, $dateFormat, $numWords) {
    if (substr($item->id, 0, strlen(self::SEARCHID)) == self::SEARCHID) {
      // convert i18n search object to array
      $entry = array();
      
      // topic
      if (strpos($item->id, 'topic')) {
        foreach ($this->schema[self::TABLE_TOPICS]['fields'] as $field) {
          $entry[$field['name']] = $item->{$field['name']};
        }
        $entry['id'] = substr($item->id, strlen(self::SEARCHID));
        ?>
        <h3><a href="<?php echo $this->getTopicURL($entry); ?>"><?php echo $entry['subject']; ?></a></h3>
        <?php
      }
      // post
      elseif(strpos($item->id, 'post')) {
        foreach ($this->schema[self::TABLE_POSTS]['fields'] as $field) {
          $entry[$field['name']] = $item->{$field['name']};
        }
        $entry['id'] = substr($item->id, strlen(self::SEARCHID));
        ?>
        <h3><a href="<?php echo $this->getTopicURL($entry['topic']); ?>"><?php echo $entry['topic']['subject']; ?></a></h3>
        <p><?php echo $this->parser->bbcode($entry['content']); ?></p>
        <?php
      }
      return true;
    }
    return false;
  }
}

?>
