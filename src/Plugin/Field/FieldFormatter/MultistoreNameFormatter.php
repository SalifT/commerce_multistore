<?php

namespace Drupal\commerce_multistore\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\commerce_store\Entity\StoreInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\StringFormatter;

/**
 * Plugin implementation of the 'commerce_multistore_name' formatter.
 *
 * @FieldFormatter(
 *   id = "commerce_multistore_name",
 *   label = @Translation("Store name"),
 *   field_types = {
 *     "string",
 *     "uri",
 *   }
 * )
 */
class MultistoreNameFormatter extends StringFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $labels = [];

    if ($items->getEntity() instanceof StoreInterface) {
      $config = \Drupal::configFactory()->get('commerce_store.settings');
      $default_store = $config->get('default_store');
      $global = $this->t('Global default store');
      $owner = $this->t('Owner default store');
      $config = \Drupal::configFactory()->get('commerce_multistore.settings');

      foreach ($items as $delta => $item) {
        $settings = [];

        $uuid = $item->getEntity()->uuid->getValue();
        $uuid = reset($uuid);
        if ($uuid['value'] == $default_store) {
          $settings['global'] = $global;
        }
        $uid = $item->getEntity()->getOwnerId();
        if ($config->get("owners.{$uid}.default_store") == $uuid['value']) {
          $settings['owner'] = $owner;
        }

        $content['type'] = $elements[$delta]['#type'];
        if ($content['type'] == 'inline_template') {
          $content['title'] = $elements[$delta]['#context']['value'];
        }
        else if ($content['type'] == 'link') {
          $content['title'] = $elements[$delta]['#title']['#context']['value'];
          $content['url'] = $elements[$delta]['#url'];
        }

        $labels[$delta] = [
          '#theme' => 'commerce_multistore_name',
          '#settings' => $settings,
          '#content' => $content,
          '#attached' => [
            'library' => ['commerce_multistore/multistore_default'],
          ],
        ];
      }
    }

    return $labels;
  }

}
