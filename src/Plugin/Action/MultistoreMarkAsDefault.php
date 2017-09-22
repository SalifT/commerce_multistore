<?php

namespace Drupal\commerce_multistore\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Marks store as default.
 *
 * @Action(
 *   id = "commerce_multistore_mark_as_default",
 *   label = @Translation("Mark as default store"),
 *   type = "commerce_store"
 * )
 */
class MultistoreMarkAsDefault extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /** @var \Drupal\commerce_store\Entity\StoreInterface $entity */
    /** @var \Drupal\commerce_multistore\StoreStorageInterface $storage */
    $storage = \Drupal::entityTypeManager()->getStorage('commerce_store');
    $storage->markAsDefault($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\commerce_store\Entity\StoreInterface $object */
    $result = $object->access('update', $account, TRUE)
      ->andIf($object->access('edit', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

}
