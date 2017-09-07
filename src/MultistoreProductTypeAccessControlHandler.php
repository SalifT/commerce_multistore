<?php

namespace Drupal\commerce_multistore;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;

/**
 * Defines the access control handler for the product type entity type.
 *
 * @see \Drupal\commerce_product\Entity\ProductType
 */
class MultistoreProductTypeAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view any commerce_product');

      default:
        return parent::checkAccess($entity, $operation, $account);

    }
  }

}
