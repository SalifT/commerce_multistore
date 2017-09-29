<?php

namespace Drupal\commerce_multistore\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Sets store type limit to one for an owner.
 *
 * @Action(
 *   id = "commerce_multistore_set_owner_limit_to_one",
 *   label = @Translation("Set owner store type limit to one"),
 *   type = "commerce_store"
 * )
 */
class MultistoreSetOwnerLimitToOne extends ActionBase {

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

      if ($admin && $uid != $cuid && !isset($limits[$uid][$store_type])) {
        $limits[$uid][$store_type] = 1;
        $storage->setStoreLimit($store_type, $limits[$uid][$store_type], $uid);
      }
      else if ($uid == $cuid) {
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
