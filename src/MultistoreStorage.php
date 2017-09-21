<?php

namespace Drupal\commerce_multistore;

use Drupal\commerce_store\StoreStorage;
use Drupal\Core\Session\AccountInterface;
use Drupal\commerce_store\Entity\StoreInterface;

/**
 * Overrides the store storage class.
 */
class MultistoreStorage extends StoreStorage {

  /**
   * {@inheritdoc}
   */
  public function loadDefault(AccountInterface $user = NULL) {
    $default = NULL;
    $config = $this->configFactory->get('commerce_store.settings');
    if ($uid = $this->getCurrentUserId($user)) {
      $uuid = $config->get("commerce_multistore.owners.{$uid}.default_store");
      $result = parent::getQuery()->condition('uid', $uid)->execute();
    }
    else {
      $uuid = $config->get('default_store');
      $result = parent::getQuery()->execute();
    }

    if ($result) {
      $stores = parent::loadMultiple($result);
      foreach ($stores as $store) {
        if ($uuid && $store->uuid() == $uuid) {
          $default = $store;
          break;
        }
      }
    }

    if (!$default && isset($store)) {
      // This is the case when previously assigned default store was
      // deleted, so we need to return at least the last found store.
      $default = $store;
      $default->enforceIsNew();
      if (count($stores) > 1) {
        drupal_set_message(t('No one default store is assigned yet. Note that it is recommended to have one explicitly assigned otherwise the last found store will be dimmed as the default. This may lead to unexpected behaviour.'), 'warning', FALSE);
      }
    }

    return $default;
  }

  /**
   * {@inheritdoc}
   */
  public function markAsDefault(StoreInterface $store) {
    $uid = $this->getCurrentUserId();
    $config = $this->configFactory->getEditable('commerce_store.settings');
    // When the current user is admin the global default store is saved.
    if ($uid === FALSE) {
      if ($config->get('default_store') != $store->uuid()) {
        $config->set('default_store', $store->uuid());
        $config->save();
      }
    }
    else if ($uid) {
      if ($config->get("commerce_multistore.owners.{$uid}.default_store") != $store->uuid()) {
        $config->set("commerce_multistore.owners.{$uid}.default_store", $store->uuid());
        $config->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $ids = NULL, AccountInterface $user = NULL) {
    $uid = $this->getCurrentUserId($user);
    if ($uid === 0) {
      // No stores for the anonymous user.
      return [];
    }
    $stores = parent::loadMultiple($ids);

    // Do not return the stores which are not owned by the current user except
    // an admin ($uid === FALSE) which should be able to access any store.
    // @todo Remove when the core #2499645 is fixed.
    // @see https://www.drupal.org/node/2848232
    if ($uid) {
      foreach ($stores as $index => $store) {
        if ($store->getOwnerId() != $uid) {
          unset($stores[$index]);
        }
      }
    }

    return $stores;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery($conjunction = 'AND') {
    $query = parent::getQuery($conjunction);

    // If the current user is not an admin ($uid === FALSE) we restrict the
    // query to the stores owned by the user or, if the $uid === 0, return the
    // query for the anonymous user which should not be the owner of any store.
    $uid = $this->getCurrentUserId();
    if ($uid !== FALSE) {
      $query->condition('uid', $uid);
    }

    return $query;
  }

  /**
   * Helper method to check the current user access to a commerce store.
   *
   * @return FALSE|int
   *   FALSE if the user is admin; user ID if the user has permission to view
   *   own store; an anonymous user ID (0) otherwise.
   */
  protected function getCurrentUserId(AccountInterface $user = NULL) {
    $user = $user ?: \Drupal::currentUser();
    $uid = FALSE;

    if (!$user->hasPermission($this->entityType->getAdminPermission())) {
      $uid = $user->hasPermission('view own commerce_store') ? $user->id() : 0;
    }

    return  $uid;
  }

  /**
   * {@inheritdoc}
   */
  public function setStoreLimit($store_type, $limit, $uid = NULL) {
    $config = $this->configFactory->getEditable('commerce_store.settings');
    if ($uid) {
      $config->set("commerce_multistore.owners.{$uid}.{$store_type}.limit", $limit);
    }
    else {
      $config->set("commerce_multistore.stores.{$store_type}.limit", $limit);
    }
    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getStoreLimit($store_type, $uid = NULL) {
    $config = \Drupal::configFactory()->get('commerce_store.settings');
    $value = $config->get("commerce_multistore.stores.{$store_type}.limit");
    if ($uid) {
      $value = [
        $store_type => $value,
        $uid => $config->get("commerce_multistore.owners.{$uid}.{$store_type}.limit"),
      ];
    }

    return $value;
  }

}
