<?php

namespace Drupal\commerce_multistore\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Clears store type limit.
 *
 * @Action(
 *   id = "commerce_multistore_clear_store_type_limit",
 *   label = @Translation("Clear store type limit"),
 *   type = "commerce_store"
 * )
 */
class MultistoreClearStoreTypeLimit extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function executeMultiple(array $stores) {
    /** @var \Drupal\commerce_multistore\StoreStorageInterface $storage */
    $storage = \Drupal::entityTypeManager()->getStorage('commerce_store');
    $user = \Drupal::currentUser();
    $cuid = $user->id();
    $limits = [];

    /** @var \Drupal\commerce_store\Entity\StoreInterface $store */
    foreach ($stores as $store) {
      if (!isset($admin)) {
        $admin = $user->hasPermission($store->getEntityType()->getAdminPermission());
      }
      $store_type = $store->bundle();
      $uid = $store->getOwnerId();

      if ($admin && $uid != $cuid && !isset($limits[$store_type])) {
        $limits[$store_type] = [
          'delete' => TRUE,
          'store_type' => $store_type,
        ];
        $storage->clearStoreLimit($limits[$store_type]);
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
