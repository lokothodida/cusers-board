<?php
/* Centralized Users: Message Board */

# thisfile
  $thisfile = basename(__FILE__, ".php");
 
# language
  i18n_merge($thisfile) || i18n_merge($thisfile, 'en_US');
 
# requires
  require_once(GSPLUGINPATH.$thisfile.'/php/main.class.php');
  
# class instantiation
  $mcusersboard = new MatrixCUsersBoard;

# register plugin
  register_plugin(
    $mcusersboard->pluginInfo('id'),
    $mcusersboard->pluginInfo('name'),
    $mcusersboard->pluginInfo('version'),
    $mcusersboard->pluginInfo('author'),
    $mcusersboard->pluginInfo('url'),
    $mcusersboard->pluginInfo('description'),
    $mcusersboard->pluginInfo('page'),
    array($mcusersboard, 'admin')
  );

# activate actions/filters
  # front-end
    add_action('error-404', array($mcusersboard, 'display'));
  # back-end
    add_action($mcusersboard->pluginInfo('page').'-sidebar', 'createSideMenu' , array($mcusersboard->pluginInfo('id'), $mcusersboard->pluginInfo('sidebar'))); // sidebar link
    add_action('search-index',   array($mcusersboard, 'searchIndex'));
    add_filter('search-item',    array($mcusersboard, 'searchItem'));
    add_filter('search-display', array($mcusersboard, 'searchDisplay'));  
    
?>