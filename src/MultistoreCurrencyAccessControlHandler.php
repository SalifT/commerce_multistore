<?php

namespace Drupal\commerce_multistore;

use Drupal\commerce\EntityAccessControlHandler;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Overrides the Currency entity access handler.
 */
class MultistoreCurrencyAccessControlHandler extends EntityAccessControlHandler {



  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    $result = parent::checkAccess($entity, $operation, $account);
    if ($result->isNeutral() || !$result->isForbidden()) {
      if ($operation == 'view') {
        // Allow store owner view currency label in views.
        $result = AccessResult::allowedIf($account->hasPermission('view own commerce_store'));
      }
    }

    return $result;
  }

}
