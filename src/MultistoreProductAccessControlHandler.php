<?php

namespace Drupal\commerce_multistore;

use Drupal\commerce\EntityAccessControlHandler;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Overrides the Product entity access handler.
 */
class MultistoreProductAccessControlHandler extends EntityAccessControlHandler {

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
