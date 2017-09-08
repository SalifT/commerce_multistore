<?php

/**
 * @file
 * Views integration for Commerce Multistore.
 */

/**
 * Implements hook_views_data_alter().
 */
function commerce_multistore_views_data_alter(array &$data) {
  // Allows variations to be filtered by raw price value in views.
  if (isset($data['commerce_product_variation_field_data']['price__number']['filter']['id'])) {
    $data['commerce_product_variation_field_data']['price__number']['filter']['id'] = 'numeric';
  }
}
