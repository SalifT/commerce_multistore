<?php

namespace Drupal\commerce_multistore\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Marks store as owner default.
 *
 * @Action(
 *   id = "commerce_multistore_mark_as_owner_default",
 *   label = @Translation("Mark as owner default store"),
 *   type = "commerce_store"
 * )
 */
class MultistoreMarkAsOwnerDefault extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($store = NULL) {
    /** @var \Drupal\commerce_store\Entity\StoreInterface $store */
    $uid = $store->getOwnerId();
    $user = \Drupal::currentUser();
    // Skip if the current store owner is admin, as they have global default
    // store and should assign it with its own action.
    if ($uid != $user->id()) {
      $config = \Drupal::configFactory()->getEditable("commerce_store.settings");
      if ($config->get("commerce_multistore.owners.{$uid}.default_store") != $store->uuid()) {
        $config->set("commerce_multistore.owners.{$uid}.default_store", $store->uuid());
        $config->save();
      }
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
