<?php

include_once(dirname(__FILE__) . '/includes/austese.inc');

function austese_form_system_theme_settings_alter(&$form, &$form_state) {
  $form['theme_settings']['toggle_search'] = array(
    '#type' => 'checkbox', 
    '#title' => t('Search box'), 
    '#default_value' => theme_get_setting('toggle_search'), 
  );
}

