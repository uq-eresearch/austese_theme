<?php

include_once(drupal_get_path('theme', 'austese') . '/includes/austese.inc');
include_once(drupal_get_path('theme', 'austese') . '/includes/modules/theme.inc');
include_once(drupal_get_path('theme', 'austese') . '/includes/modules/pager.inc');
include_once(drupal_get_path('theme', 'austese') . '/includes/modules/form.inc');
include_once(drupal_get_path('theme', 'austese') . '/includes/modules/admin.inc');
include_once(drupal_get_path('theme', 'austese') . '/includes/modules/menu.inc');


// Load module include files
$modules = module_list();

foreach ($modules as $module) {
  if (is_file(drupal_get_path('theme', 'austese') . '/includes/modules/' . str_replace('_', '-', $module) . '.inc')) {
    include_once(drupal_get_path('theme', 'austese') . '/includes/modules/' . str_replace('_', '-', $module) . '.inc');
  }    
}

/**
 * hook_theme() 
 */
function austese_theme() {
  return array(
    'austese_links' => array(
      'variables' => array('links' => array(), 'attributes' => array(), 'heading' => NULL),
    ),
    'austese_btn_dropdown' => array(
      'variables' => array('links' => array(), 'attributes' => array(), 'type' => NULL),
    ), 
  );
}

/**
 * Preprocess variables for html.tpl.php
 *
 * @see system_elements()
 * @see html.tpl.php
 */
function austese_preprocess_html(&$variables) {
   // Try to load the library
  if (module_exists('austese_ui')){
    $library = libraries_load('austese', 'minified');
  }  
}

function austese_breadcrumb($variables) {
  $breadcrumb = $variables['breadcrumb'];

  if (!empty($breadcrumb)) {
    $breadcrumbs = '<ul class="breadcrumb">';
    
    $count = count($breadcrumb) - 1;
    foreach($breadcrumb as $key => $value) {
      if($count != $key) {
        $breadcrumbs .= '<li>'.$value.'<span class="divider">/</span></li>';
      }else{
        $breadcrumbs .= '<li>'.$value.'</li>';
      }
    }
    $breadcrumbs .= '</ul>';
    
    return $breadcrumbs;
  }
}

/**
 * Preprocess variables for node.tpl.php
 *
 * @see node.tpl.php
 */
function austese_preprocess_node(&$variables) {
  if($variables['teaser'])
    $variables['classes_array'][] = 'row-fluid';
}

/**
 * Preprocess variables for block.tpl.php
 *
 * @see block.tpl.php
 */
function austese_preprocess_block(&$variables, $hook) {
  //$variables['classes_array'][] = 'row';
  // Use a bare template for the page's main content.
  if ($variables['block_html_id'] == 'block-system-main') {
    $variables['theme_hook_suggestions'][] = 'block__no_wrapper';
  }
  $variables['title_attributes_array']['class'][] = 'block-title';
}

/**
 * Override or insert variables into the block templates.
 *
 * @param $variables
 *   An array of variables to pass to the theme template.
 * @param $hook
 *   The name of the template being rendered ("block" in this case.)
 */
function austese_process_block(&$variables, $hook) {
  // Drupal 7 should use a $title variable instead of $block->subject.
  $variables['title'] = $variables['block']->subject;
}

/**
 * Preprocess variables for page.tpl.php
 *
 * @see page.tpl.php
 */
function austese_preprocess_page(&$variables) {
  // Add information about the number of sidebars.
  if (!empty($variables['page']['sidebar_first']) && !empty($variables['page']['sidebar_second'])) {
    $variables['columns'] = 3;
  }
  elseif (!empty($variables['page']['sidebar_first'])) {
    $variables['columns'] = 2;
  }
  elseif (!empty($variables['page']['sidebar_second'])) {
    $variables['columns'] = 2;
  }
  else {
    $variables['columns'] = 1;
  }
  
  // Our custom search because its cool :)
  $variables['search'] = FALSE;
  if(theme_get_setting('toggle_search') && module_exists('search'))
    $variables['search'] = drupal_get_form('_austese_search_form');

  // Primary nav
  $variables['primary_nav'] = FALSE;
  if($variables['main_menu']) {
    // Build links
    $tree = menu_tree_page_data(variable_get('menu_main_links_source', 'main-menu'));
    $variables['main_menu'] = austese_menu_navigation_links($tree);
    
    // Build list
    $variables['primary_nav'] = theme('austese_links', array(
      'links' => $variables['main_menu'],
      'attributes' => array(
        'id' => 'main-menu',
        'class' => array('nav'),
      ),
      'heading' => array(
        'text' => t('Main menu'),
        'level' => 'h2',
        'class' => array('element-invisible'),
      ),
    ));
  }
  
  // Secondary nav
  $variables['secondary_nav'] = FALSE;
  if($variables['secondary_menu']) {
    $secondary_menu = menu_load(variable_get('menu_secondary_links_source', 'user-menu'));
    
    // Build links
    $tree = menu_tree_page_data($secondary_menu['menu_name']);
    $variables['secondary_menu'] = austese_menu_navigation_links($tree);
    $links = $variables['secondary_menu'];
    global $user; //decl var
    if ($user->uid) {
      $menulabel = $user->name;
      $href = '/user';
      // Build list
    } else {
      $menulabel = 'Log in';
      $href = '/user/login';
    }
    $variables['secondary_nav'] = theme('austese_btn_dropdown', array(
        'links' => $links,
        'href'=> $href,
        'label' => $menulabel,
        'type' => 'success',
        'attributes' => array(
          'id' => 'user-menu',
          'class' => array('nav'),
        ),
        'heading' => array(
          'text' => t('Secondary menu'),
          'level' => 'h2',
          'class' => array('element-invisible'),
        ),
    ));
  }
}

function _austese_search_form($form, &$form_state) {
  // Get custom search form for now
  $form = search_form($form, $form_state);

  // Cleanup
  $form['#attributes']['class'][] = 'navbar-search';
  //$form['#attributes']['class'][] = 'pull-right';
  $form['basic']['keys']['#title'] = '';
  $form['basic']['keys']['#attributes']['class'][] = 'search-query';
  $form['basic']['keys']['#attributes']['class'][] = 'span2';
  $form['basic']['keys']['#attributes']['placeholder'] = t('Search');
  unset($form['basic']['submit']);
  unset($form['basic']['#type']);
  unset($form['basic']['#attributes']);
  $form += $form['basic'];
  unset($form['basic']);

  return $form;
}



/**
 * Preprocess variables for region.tpl.php
 *
 * @see region.tpl.php
 */
function austese_preprocess_region(&$variables, $hook) {
  if ($variables['region'] == 'content') {
    $variables['theme_hook_suggestions'][] = 'region__no_wrapper';
  }
  
  // Me likes
  if($variables['region'] == "sidebar_first")
    $variables['classes_array'][] = 'well';
}

/**
 * Returns the correct span class for a region
 */
function _austese_content_span($columns = 1) {
  $class = FALSE;
  
  switch($columns) {
    case 1:
      $class = 'span12';
      break;
    case 2:
      $class = 'span9';
      break;
    case 3:
      $class = 'span6';
      break;
  }
  
  return $class;
}