<?php

namespace Drupal\commerce_multistore\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Increase store type limit by one for an owner.
 *
 * @Action(
 *   id = "commerce_multistore_increase_owner_limit_by_one",
 *   label = @Translation("Increase owner store limit by one"),
 *   type = "commerce_store"
 * )
 */
class MultistoreIncreaseOwnerLimitByOne extends ActionBase {

  /**
   * The store types limits.
   *
   * @var array
   */
  protected $limits = [];

  /**
   * {@inheritdoc}
   */
  public function execute($store = NULL) {
    /** @var \Drupal\commerce_store\Entity\StoreInterface $store */
    /** @var \Drupal\commerce_multistore\StoreStorageInterface $storage */
    $user = \Drupal::currentUser();
    $uid = $store->getOwnerId();
    $store_type = $store->bundle();
    // Skip if the current store owner is the current user (admin) or if the
    // the limit is already increased diring this bulk operation.
    if (!isset($this->limits[$uid][$store_type]) && $uid != $user->id()) {
      $storage = \Drupal::entityTypeManager()->getStorage('commerce_store');
      $limit = $storage->getStoreLimit($store_type, $uid);
      $this->limits[$uid][$store_type] = $limit[$uid] + 1;
      $storage->setStoreLimit($store_type, $this->limits[$uid][$store_type], $uid);
    }
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
