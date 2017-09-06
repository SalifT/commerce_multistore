<?php

namespace Drupal\commerce_multistore;

use Drupal\commerce\EntityAccessControlHandler;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;

/**
 * Overrides the Store entity access handler.
 */
class MultistoreAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    // Only allow users to create permitted store types.
    $result = parent::checkCreateAccess($account, $context, $entity_bundle);
    if ($result->isNeutral() || !$result->isForbidden()) {
      $admin = $account->hasPermission($this->entityType->getAdminPermission());
      $allowed = $admin ?: $account->hasPermission("create {$entity_bundle} commerce_store");
      $result = AccessResult::allowedIf($allowed);
    }

    return $result;
  }

}
