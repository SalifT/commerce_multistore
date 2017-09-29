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
      $admin = $store->getOwner()->hasPermission($store->getEntityType()->getAdminPermission());
      $uid = $store->getOwnerId();
      $store_type = $store->bundle();
      // Skip if the current store owner is the current user (admin) or if the
      // the limit is already increased diring this bulk operation.
      if (!$admin && $uid != $cuid && !isset($limits[$uid][$store_type])) {
        $limit = $storage->getStoreLimit($store_type, $uid);
        $limits[$uid][$store_type] = $limit[$uid] + 1;
        $storage->setStoreLimit($store_type, $limits[$uid][$store_type], $uid);
      }
      else if ($admin) {
        $name = $user->getUsername();
        $msg = $this->t('The store type limit cannot be set for the %name because they have admin permission.', ['%name' => $name]);
        drupal_set_message($msg, 'warning', FALSE);
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
