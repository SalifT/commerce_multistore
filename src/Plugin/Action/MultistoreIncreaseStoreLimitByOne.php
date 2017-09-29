<?php

namespace Drupal\commerce_multistore\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Increases store type limit by one.
 *
 * @Action(
 *   id = "commerce_multistore_increase_store_limit_by_one",
 *   label = @Translation("Increase store type limit by one"),
 *   type = "commerce_store"
 * )
 */
class MultistoreIncreaseStoreLimitByOne extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $stores) {
    /** @var \Drupal\commerce_multistore\StoreStorageInterface $storage */
    $storage = \Drupal::entityTypeManager()->getStorage('commerce_store');
    $user = \Drupal::currentUser();
    $limits = [];

    /** @var \Drupal\commerce_store\Entity\StoreInterface $store */
    foreach ($stores as $store) {
      if (!isset($admin)) {
        $admin = $user->hasPermission($store->getEntityType()->getAdminPermission());
      }
      $store_type = $store->bundle();
      // Skip if the current store type limit is already increased.
      if ($admin && !isset($limits[$store_type])) {
        $limit = $storage->getStoreLimit($store_type);
        $limits[$store_type] = $limit + 1;
        $storage->setStoreLimit($store_type, $limits[$store_type]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function execute($store = NULL) {
    // Do nothing.
  }

  /**
   * {@inheritdoc}
   */
  public function access($store, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\commerce_store\Entity\StoreInterface $store */
    $result = $store->access('update', $account, TRUE)
      ->andIf($store->access('edit', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

}
