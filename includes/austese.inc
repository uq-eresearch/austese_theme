<?php

/**
 * A wrapper function for austese_theme_get_settings().
 * 
 * @param $name
 *   The name of the setting that you want to retrieve. 
 * @param $default (optional)
 *   The name (key) of the theme that you want to fetch the
 *   setting for. Defaults to NULL.   
 * @param $theme (optional)
 *   The key (machin-readable name) of a theme. Defaults to the key of the
 *   current theme if not defined.
 *   
 * @see 
 *   austese_theme_get_setting().
 */
function austese_theme_get_setting($name, $theme = NULL) {
  switch($name){
	case 'exclude':
		$setting = austese_theme_get_info($name, $theme);
		break;
	default:
	  $setting = theme_get_setting($name, $theme);
		break;
  }

  return isset($setting) ? $setting : NULL; 
}

function austese_get_settings($theme = NULL) {

  if (!isset($theme)) {
    $theme = !empty($GLOBALS['theme_key']) ? $GLOBALS['theme_key'] : '';
  }
	if($theme) {
		$themes = list_themes();
    $theme_object = $themes[$theme];
	}
	return $theme_object->info['settings'];
}

function austese_theme_get_info($setting_name, $theme = NULL) {
// If no key is given, use the current theme if we can determine it.
  if (!isset($theme)) {
    $theme = !empty($GLOBALS['theme_key']) ? $GLOBALS['theme_key'] : '';
  }

  $output = array();

  if ($theme) {
    $themes = list_themes();
    $theme_object = $themes[$theme];

    // Create a list which includes the current theme and all its base themes.
    if (isset($theme_object->base_themes)) {
      $theme_keys = array_keys($theme_object->base_themes);
      $theme_keys[] = $theme;
    }
    else {
      $theme_keys = array($theme);
    }
    foreach ($theme_keys as $theme_key) {
      if (!empty($themes[$theme_key]->info[$setting_name])) {
        $output[$setting_name] = $themes[$theme_key]->info[$setting_name];
      }
    }
  }
  
  return $output;
}


/**
 * Returns navigational links based on a menu tree
 */
function austese_menu_navigation_links($tree, $lvl = 0) {
  $result = array();

  if(count($tree) > 0) {
	foreach($tree as $id => $item) {
	  $new_item = array('title' => $item['link']['title'], 'link_path' => $item['link']['link_path'], 'href' => $item['link']['href']);
	  
	  // Dont do drugs and don't do any levels deeper then 1
	  if($lvl < 1)
		$new_item['below'] = austese_menu_navigation_links($item['below'], $lvl+1);
	  
	  $result['menu-'. $item['link']['mlid']] = $new_item;
	}
  }
  
  return $result;
}


/*
 * Custom theme functions
 *
 */

/**
 * Preprocess function for theme_austese_btn_dropdown
 */
function austese_preprocess_austese_btn_dropdown(&$variables) {
  // Add default class
  //$variables['attributes']['class'][] = 'btn-group';
  
  // Check if its a array of links so we need to theme it first here.
  if(is_array($variables['links'])) {
	$variables['links'] = theme('links', array(
	  'links' => $variables['links'],
	  'attributes' => array(
		'class' => array('dropdown-menu'),
	  ),
	));
  }
}  

/**
 * theme_austese_btn_dropdown
 */
function austese_austese_btn_dropdown($variables) {

  // Start markup
  $output = '<ul'. drupal_attributes($variables['attributes']) .'>';
  
  // Ad as string if its not a link
  if(is_array($variables['label'])) {
	$output .= l($variables['label']['title'], $$variables['label']['href'], $variables['label']);
  }
  
  

  $output .= '<li class="dropdown" data-dropdown="dropdown"><a class="dropdown-toggle" href="#">';
  
  // Its a link so create one
  if(is_string($variables['label'])) {
	$output .= check_plain($variables['label']);
  }
  
  // Finish markup 	
  $output .= '
  	<b class="caret"></b>
	</a>
	' . $variables['links'] . '
  </li></ul>';
  
  return $output;
}  

/**
 * theme_austese_progress_bar
 */
function austese_austese_progress_bar($variables) {
  $variables['attributes']['class'][] = 'progress';
  
  return "<div". drupal_attributes($variables['attributes']) .">
  <div class=\"bar\"
       style=\"width: ". $variables['percent'] ."%;\"></div>
  </div>";
}
