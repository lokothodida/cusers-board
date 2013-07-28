<?php

// default template
$array = array();
$array['channel']['item']['header']['@cdata']   = <<<EOT
<style>
  /* category */
	table.category .forum { width: 60%; }
	table.category .total { width: 15%; }
	table.category .latest { width: 25%; }
  
  /* forum */
  td.announcement:before {
    content: "Announcement";
  }
  td.sticky:before {
    content: "Sticky";
  }
  td.announcement:before, td.sticky:before {
    display: block;
    float: left;
    background:#333;
    border-top:1px solid rgba(255,255,255,.4);
    text-shadow: 1px 1px 0px rgba(0,0,0,.5);
    text-transform:uppercase;
    background: -moz-linear-gradient(top, #444 0%, #222 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#444), color-stop(100%,#222));
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#444444', endColorstr='#222222',GradientType=0 ); 
    font-family: 'Yanone Kaffeesatz', arial, helvetica, sans-serif;
    font-weight: 50;
    color:#fff !important;
    font-size: 12px;
    line-height: 12px;
    margin: 2px 5px 2px 2px;
    padding: 2px 5px 2px 5px;
    border-radius: 4px;
    -moz-border-radius: 4px;
    -khtml-border-radius: 4px;
    -webkit-border-radius: 4px;
    background: #316594;
    background: -moz-linear-gradient(top, #316594 0%, #2C5983 100%); 
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#316594), color-stop(100%,#2C5983));
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#316594', endColorstr='#2C5983',GradientType=0 ); 
    text-decoration: none !important;
  }
  
  /* topic */
	.profile { width: 20%; }
	.post { width: 80%; }
  .post .date {
    width: 100%;
    padding: 3px;
    border-bottom: 1px dotted #000000;
    overflow: hidden;
  }
	.post .signature { border-top: 1px dotted #000000; }
  .post .content { padding: 5px; }
  .post .options {
    overflow: hidden;
    float: right;
  }
  .button {
    display: block;
    float: left;
    background:#333;
    border-top:1px solid rgba(255,255,255,.4);
    text-shadow: 1px 1px 0px rgba(0,0,0,.5);
    text-transform:uppercase;
    background: -moz-linear-gradient(top, #444 0%, #222 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#444), color-stop(100%,#222));
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#444444', endColorstr='#222222',GradientType=0 ); 
    font-family: 'Yanone Kaffeesatz', arial, helvetica, sans-serif;
    font-weight:100;
    color:#fff !important;
    font-size: 19px;
    line-height:19px;
    margin: 2px;
    padding: 5px;
    border-radius: 4px;
    -moz-border-radius: 4px;
    -khtml-border-radius: 4px;
    -webkit-border-radius: 4px;
    background: #316594;
    background: -moz-linear-gradient(top, #316594 0%, #2C5983 100%); 
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#316594), color-stop(100%,#2C5983));
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#316594', endColorstr='#2C5983',GradientType=0 ); 
    text-decoration: none !important;
  }
  .button:hover {
    text-decoration: underline !important;
  }
  .post .edited {
    font-size: 80%;
    padding: 3px;
  }
</style>
EOT;

$array['channel']['item']['home']['@cdata'] = '';

$array['channel']['item']['category']['@cdata']     = <<<EOT
<div class="tableWrap">
  <table class="category">
    <thead>
      <tr>
        <th colspan="100%" class="head1"><a href="<?php echo \$this->getCategoryURL(\$category); ?>"><?php echo \$category['name']; ?></a></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (\$forums as \$forum) { ?>
        <?php
          \$topics = \$this->getTopics(\$forum);
        ?>
        <tr>
          <td class="row2 forum">
            <h3><a href="<?php echo \$this->getForumURL(\$forum); ?>"><?php echo \$forum['name']; ?></a></h3>
            <?php echo \$forum['description']; ?>
		  </td>
          <td class="row1 total"><?php echo \$topics['total']; ?> Topics</td>
          <td class="row2 latest">
            <?php if (\$topics['latest']) { ?>
            <a href="<?php echo \$this->getTopicURL(\$topics['latest']['topic']); ?>"><?php echo \$topics['latest']['topic']['subject']; ?></a><br />
            by <?php echo \$topics['latest']['user']['displayname']; ?><br />
            <?php echo \$this->core->date(\$topics['latest']['post']['credate']); ?><br />
            <?php } else { ?>
            Never
            <?php } ?>
          </td>
        <tr>
      <?php } ?>
    </tbody>
	</table>
</div>
EOT;

$array['channel']['item']['forum']['@cdata'] = <<<EOT
<div class="tableWrap">
  <table>
    <thead>
      <tr>
        <th colspan="100%" class="head1"><a href="<?php echo \$this->getForumURL(\$forum); ?>"><?php echo \$forum['name']; ?></a></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (\$topics as \$topic) { ?>
        <?php
          \$posts = \$this->getPosts(\$topic['id']);
        ?>
        <tr>
          <td class="row2 <?php echo \$topic['type']; ?> <?php echo \$topic['status']; ?>">
            <h3><a href="<?php echo \$this->getTopicURL(\$topic); ?>"><?php echo \$topic['subject']; ?></a></h3>
            <span>By <a href="<?php echo \$this->core->getProfileURL(\$topic['author']['username']); ?>"><?php echo \$topic['author']['displayname']; ?></a></span>
          </td>
          <td class="row1"><?php echo \$posts['total']; ?> Posts</td>
          <td class="row2">
          <?php if (\$posts['latest']) { ?>
            by <a href="<?php echo \$this->core->getProfileURL(\$posts['latest']['user']['username']); ?>"><?php echo \$posts['latest']['user']['displayname']; ?></a><br />
            <?php echo \$this->core->date(\$posts['latest']['post']['credate']); ?><br />
          <?php } else { ?>
          Never
          <?php } ?>
		  </td>
        <tr>
      <?php } ?>
      <?php if (empty(\$topics)) { ?>
      <tr>
        <td colspan="100%" class="row1">No topics</td>
      </tr>
      <?php } ?>
    </tbody>
	</table>
</div>
EOT;

$array['channel']['item']['topic']['@cdata'] = <<<EOT
<div class="tableWrap">
  <table>
    <thead>
      <tr>
        <th colspan="100%" class="head1"><a href="<?php echo \$this->getTopicURL(\$topic); ?>"><?php echo \$topic['subject']; ?></a></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (\$posts as \$post) { ?>
        <?php
          // parsing details
          \$user = \$users[\$post['author']];
          \$editor = \$users[\$post['editor']];
        ?>
        <tr>
          <td class="row2 profile">
            <!--profile-->
              <h3><a href="<?php echo \$this->core->getProfileURL(\$user['username']); ?>"><?php echo \$user['displayname']; ?></a></h3>
              <p><?php echo \$this->core->displayAvatar(\$user); ?></p>
              <p>Email: <?php echo \$user['email']; ?></p>
              <p>Posts: <?php echo \$user['posts']; ?></p>
          </td>
          <td class="row1 post">
            <!--post-->
            <div class="date">
				<span style="display: block; float: left; padding: 4px 0 4px 0;">Posted <?php echo \$this->core->date(\$post['credate']); ?></span>
				<?php \$this->getPostOptions(\$user, \$post, \$topic); ?>
			</div>
            <div class="content"><?php echo \$post['content']; ?></div>
            <div class="signature"><?php echo \$user['signature']; ?></div>
            <?php if (\$post['credate'] != \$post['pubdate']) { ?>
            <div class="edited">Edited by <?php echo \$editor['displayname']; ?> @ <?php echo \$this->core->date(\$post['credate']); ?></div>
            <?php } ?>
          </td>
        <tr>
      <?php } ?>
    </tbody>
	</table>
</div>
EOT;

$array['channel']['item']['footer']['@cdata'] = <<<EOT
<div class="footer">Powered by <a href="http://get-simple.info/extend/plugin/cusers-message-board/663/">Centralized Users: Message Board</a> Version <?php echo self::VERSION; ?></div>
EOT;

?>