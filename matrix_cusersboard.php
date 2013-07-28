<?php
/* Centralized Users: Message Board */

# thisfile
  $thisfile = basename(__FILE__, ".php");
 
# language
  i18n_merge($thisfile) || i18n_merge($thisfile, 'en_US');
 
# requires
  require_once(GSPLUGINPATH.$thisfile.'/php/main.class.php');
  
# class instantiation
  $mcusersboard = new MatrixCUsersBoard; // instantiate class

# register plugin
  register_plugin(
    $mcusersboard->pluginInfo('id'),           // id
    $mcusersboard->pluginInfo('name'),         // name
    $mcusersboard->pluginInfo('version'),      // version
    $mcusersboard->pluginInfo('author'),       // author
    $mcusersboard->pluginInfo('url'),          // url
    $mcusersboard->pluginInfo('description'),  // description
    $mcusersboard->pluginInfo('page'),         // page type - on which admin tab to display
    array($mcusersboard, 'admin')              // administration function
  );

# activate actions/filters
  # front-end
    add_action('error-404', array($mcusersboard, 'display')); // display for plugin
  # back-end
    add_action($mcusersboard->pluginInfo('page').'-sidebar', 'createSideMenu' , array($mcusersboard->pluginInfo('id'), $mcusersboard->pluginInfo('sidebar'))); // sidebar link
    add_action('search-index',   array($mcusersboard, 'searchIndex'));
    add_filter('search-item',    array($mcusersboard, 'searchItem'));
    add_filter('search-display', array($mcusersboard, 'searchDisplay'));  
    
?>