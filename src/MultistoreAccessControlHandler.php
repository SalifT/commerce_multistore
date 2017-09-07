<?php

namespace Drupal\commerce_multistore;

use Drupal\commerce\EntityAccessControlHandler;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;

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

  /**
   * {@inheritdoc}
   */
  protected function checkFieldAccess($operation, FieldDefinitionInterface $field_definition, AccountInterface $account, FieldItemListInterface $items = NULL) {
    $result = parent::checkFieldAccess($operation, $field_definition, $account, $items);
    if ($result->isNeutral() || !$result->isForbidden()) {
      if ($operation == 'edit' && $field_definition->getName() == 'uid') {
        $admin = $account->hasPermission($this->entityType->getAdminPermission());
        $result = AccessResult::allowedIf($admin);
      }
    }

    return $result;
  }

}
