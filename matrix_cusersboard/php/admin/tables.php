<?php
// categories
  $tables[self::TABLE_CATEGORIES]['oldname'] = 'CU_board_categories';
  $tables[self::TABLE_CATEGORIES]['id'] = 0;
  $tables[self::TABLE_CATEGORIES]['fields'] = array(
    array(
      'oldname' => 'category_name',
      'name' => 'name',
      'label' => i18n_r(MatrixCUsers::FILE.'/NAME'),
      'type' => 'input',
      'mask' => 'long',
    ),
    array(
      'name' => 'slug',
      'label' => i18n_r(MatrixCUsers::FILE.'/SLUG'),
      'type' => 'input',
      'mask' => 'slug',
      'class' => 'left',
    ),
    array(
      'oldname' => 'category_order',
      'name' => 'order',
      'label' => i18n_r(self::FILE.'/ORDER'),
      'type' => 'input',
      'mask' => 'number',
      'class' => 'right',
    ),
  );
  $tables[self::TABLE_CATEGORIES]['maxrecords'] = 0;
  $tables[self::TABLE_CATEGORIES]['records'] = array();
  $tables[self::TABLE_CATEGORIES]['records'][] = array(
    'name' => 'Your First Category',
    'slug' => 'your-first-category',
    'order' => 0,
  );
  
  // forums
  $tables[self::TABLE_FORUMS]['oldname'] = 'CU_board_forums';
  $tables[self::TABLE_FORUMS]['id'] = 0;
  $tables[self::TABLE_FORUMS]['fields'] = array(
    array(
      'oldname' => 'forum_name',
      'name' => 'name',
      'placeholder' => i18n_r(MatrixCUsers::FILE.'/NAME'),
      'type' => 'textlong',
      'required' => 'required',
    ),
    array(
      'name' => 'slug',
      'label' => i18n_r(MatrixCUsers::FILE.'/SLUG'),
      'type' => 'slug',
    ),
    array(
      'oldname' => 'forum_category',
      'name' => 'category',
      'label' => i18n_r(self::FILE.'/CATEGORY'),
      'type' => 'int',
      'required' => 'required',
    ),
    array(
      'oldname' => 'forum_description',
      'name' => 'description',
      'label' => i18n_r(MatrixCUsers::FILE.'/DESCRIPTION'),
      'type' => 'wysiwyg',
    ),
    array(
      'oldname' => 'forum_order',
      'name' => 'order',
      'label' => i18n_r(MatrixCUsers::FILE.'/ORDER'),
      'type' => 'int',
      'required' => 'required',
    ),
  );
  $tables[self::TABLE_FORUMS]['maxrecords'] = 0;
  $tables[self::TABLE_FORUMS]['records'] = array();
  $tables[self::TABLE_FORUMS]['records'][] = array(
    'name' => 'Your First Forum',
    'slug' => 'your-first-forum',
    'category' => 0,
    'description' => 'Your First Description',
    'order' => 0,
  );
  
  // topics
  $tables[self::TABLE_TOPICS]['oldname'] = 'CU_board_topics';
  $tables[self::TABLE_TOPICS]['id'] = 0;
  $tables[self::TABLE_TOPICS]['fields'] = array(
    array(
      'oldname' => 'topic_subject',
      'name' => 'subject',
      'type' => 'textlong',
      'required' => 'required',
    ),
    array(
      'oldname' => 'topic_forum',
      'name' => 'forum',
      'type' => 'int',
    ),
    array(
      'oldname' => 'topic_views',
      'name' => 'views',
      'type' => 'int',
      'default' => 0,
    ),
    array(
      'name' => 'slug',
      'type' => 'slug',
    ),
    array(
      'oldname' => 'topic_by',
      'name' => 'author',
      'type' => 'int',
    ),
    array(
      'oldname' => 'topic_date',
      'name' => 'credate',
      'type' => 'datetimelocal',
      'readonly' => 'readonly',
    ),
    array(
      'oldname' => 'topic_update',
      'name' => 'pubdate',
      'type' => 'datetimelocal',
      'readonly' => 'readonly',
    ),
    array(
      'oldname' => 'topic_type',
      'name' => 'type',
      'type' => 'dropdowncustomkey',
      'options' => implode("\n", $this->config['topic-types']),
      'default' => 0,
    ),
    array(
      'oldname' => 'topic_status',
      'name' => 'status',
      'type' => 'dropdowncustomkey',
      'options' => implode("\n", $this->config['topic-statuses']),
      'default' => 0,
    ),
    array(
      'name' => 'tags',
      'label' => i18n_r(MatrixCUsers::FILE.'/TAGS'),
      'type' => 'tags',
    ),
  );
  $tables[self::TABLE_TOPICS]['maxrecords'] = 0;
  $tables[self::TABLE_TOPICS]['records'] = array();
  $tables[self::TABLE_TOPICS]['records'][] = array(
    'subject' => 'Your First Subject',
    'slug' => 'your-first-subject',
    'forum' => 0,
    'author' => 0,
    'credate' => time(),
    'pubdate' => time(),
  );
  
  // posts
  $tables[self::TABLE_POSTS]['oldname'] = 'CU_board_posts';
  $tables[self::TABLE_POSTS]['id'] = 0;
  $tables[self::TABLE_POSTS]['fields'] = array(
    array(
      'oldname' => 'post_topic',
      'name' => 'topic',
      'type' => 'int',
    ),
    array(
      'oldname' => 'post_by',
      'name' => 'author',
      'type' => 'int',
    ),
    array(
      'oldname' => 'post_update_by',
      'name' => 'editor',
      'type' => 'int',
    ),
    array(
      'oldname' => 'post_date',
      'name' => 'credate',
      'type' => 'datetimelocal',
      'readonly' => 'readonly',
    ),
    array(
      'oldname' => 'post_update',
      'name' => 'pubdate',
      'type' => 'datetimelocal',
      'readonly' => 'readonly',
    ),
    array(
      'oldname' => 'post_content',
      'name' => 'content',
      'type' => 'bbcodeeditor',
      'required' => 'required',
    ),
  );
  $tables[self::TABLE_POSTS]['maxrecords'] = 0;
  $tables[self::TABLE_POSTS]['records'] = array();
  $tables[self::TABLE_POSTS]['records'][] = array(
    'topic' => 0,
    'author' => 0,
    'editor' => 0,
    'credate' => time(),
    'pubdate' => time(),
    'content' => 'Your first post content. Welcome to the board!',
  );
  
  // config
  $tables[self::TABLE_CONFIG]['oldname'] = 'CU_board_settings';
  $tables[self::TABLE_CONFIG]['id'] = 0;
  $tables[self::TABLE_CONFIG]['fields'] = array(
    array(
      'oldname' => 'board_name',
      'name' => 'title',
      'type' => 'textlong',
      'placeholder' => i18n_r(MatrixCUsers::FILE.'/NAME'),
      'default' => 'Your GetSimple Board',
    ),
    array(
      'oldname' => 'board_date',
      'name' => 'credate',
      'type' => 'datetimelocal',
      'label' => i18n_r(MatrixCUsers::FILE.'/REGISTERED'),
      'default' => time(),
      'class' => 'leftsec',
    ),
    array(
      'oldname' => 'page_slug',
      'name' => 'slug',
      'type' => 'slug',
      'label' => i18n_r(MatrixCUsers::FILE.'/SLUG'),
      'default' => 'board',
      'class' => 'leftsec',
    ),
    array(
      'oldname' => 'posts_per_page',
      'name' => 'posts-per-page',
      'type' => 'int',
      'label' => i18n_r(self::FILE.'/POSTS_PER_PAGE'),
      'default' => 10,
      'class' => 'leftsec',
    ),
    array(
      'oldname' => 'topics_per_page',
      'name' => 'topics-per-page',
      'type' => 'int',
      'label' => i18n_r(self::FILE.'/TOPICS_PER_PAGE'),
      'default' => 10,
      'class' => 'leftsec',
    ),
    array(
      'name' => 'template',
      'type' => 'template',
      'label' => i18n_r(MatrixCUsers::FILE.'/TEMPLATE'),
      'class' => 'rightsec',
    ),
    array(
      'name' => 'theme',
      'type' => 'dropdowncustom',
      'label' => i18n_r(MatrixCUsers::FILE.'/THEME'),
      'options' => 'Default',
      'default' => 'Default',
      'class' => 'rightsec',
    ),
  );
  $tables[self::TABLE_CONFIG]['maxrecords'] = 0;
  $tables[self::TABLE_CONFIG]['records'] = array();
  $tables[self::TABLE_CONFIG]['records'][] = array();
?>