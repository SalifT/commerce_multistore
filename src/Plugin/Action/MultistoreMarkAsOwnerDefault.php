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
  public function executeMultiple(array $stores) {
    $config = \Drupal::configFactory()->getEditable("commerce_multistore.settings");
    $user = \Drupal::currentUser();
    $cuid = $user->id();
    $owners = [];

    /** @var \Drupal\commerce_store\Entity\StoreInterface $store */
    foreach ($stores as $store) {
      $uid = $store->getOwnerId();
      $uuid = $store->uuid();
      if (!isset($admin)) {
        $admin = $user->hasPermission($store->getEntityType()->getAdminPermission());
      }

      // Just one new default store for an owner. For perfomance reasons ignore
      // an attempt to mark the last in a chain as an owner default store.
      if (($owner = $uid != $cuid) && $admin && !isset($owners[$uid])) {
        if ($config->get("owners.{$uid}.default_store") != $uuid) {
          $save = $config->set("owners.{$uid}.default_store", $uuid);
          $owners[$uid] = $uuid;
        }
      }
      else if (!$owner && $admin) {
        $name = $user->getUsername();
        $msg = $this->t('The %name store cannot be set as owner default because they have admin permission and should use a global default store.', ['%name' => $name]);
        drupal_set_message($msg, 'warning', FALSE);
      }
    }
    if (isset($save)) {
      $config->save();
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
