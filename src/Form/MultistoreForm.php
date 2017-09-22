<?php

namespace Drupal\commerce_multistore\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_store\Form\StoreForm;

/**
 * Overrides the store add/edit form.
 */
class MultistoreForm extends StoreForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $user = $this->currentUser();
    /** @var \Drupal\commerce_multistore\StoreStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('commerce_store');
    $default_store = $storage->loadDefault();
    // If there is no default store saved then the currently edited store will
    // be forced to default. After saving the default that can be reassigned to
    // any other store available.
    $isDefault = TRUE;
    if ($default_store && !$default_store->isNew()) {
      if(!$isDefault = $default_store->uuid() == $this->entity->uuid()) {
        $link = $default_store->toLink($default_store->getName(), 'edit-form')->toString()->getGeneratedLink();
        $form['warning'] = [
          '#markup' => $this->t('Current default store: ') . "<strong>{$link}</strong>",
          '#weight' => $form['name']['#weight'] - 1,
        ];
      }
    }

    $form['default']['#description'] = $this->t('The basic role of default store is to be the last in a chain of stores resolved for a particular commerce action. For example, you may have the same product added to different stores and sold in some countries with different taxes applied. So, if no one country condition is met then this product will be handled as if it belongs to the current default store.');

    if ($user->hasPermission($this->entity->getEntityType()->getAdminPermission())) {
      $form['default']['#title'] = $this->t('Global default store');
      $form['default']['#description'] .= ' ' . $this->t("As admin you may assign for this purpose your own store or any other owner's store. Note that disregarding of this setting each regular store owner have their own default store.");
      if (($uid = $this->entity->getOwnerId()) && $uid != $user->id()) {
        $entity_type = $this->entity->bundle();
        $limit = $storage->getStoreLimit($entity_type, $uid);

        $form['multistore_limit'] = [
          '#type' => 'number',
          '#step' => 1,
          '#min' => 0,
          '#required' => TRUE,
          '#weight' =>  $form['uid']['#weight'] + 1,
          '#title' => t('The maximum stores'),
          '#description' => t('Override the number of stores of this type allowed to create by the current store owner.'),
          '#default_value' => $limit[$uid] ?: $limit[$entity_type],
        ];
      }
    }
    else {
      $form['default']['#title'] = $this->t('Default store');
      $form['default']['#description'] .= ' ' . $this->t('Only one store might be set as default per a store owner. You can change the default store by assigning it to any other of your stores.');
    }

    $form['default']['#weight'] = $form['name']['#weight'] - 1;
    $form['default']['#default_value'] = $isDefault;
    $form['default']['#disabled'] = $isDefault;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    if ($this->entity->isNew()) {
      $this->entity->enforceIsNew(FALSE);
    }
    parent::save($form, $form_state);

    $limit = $form_state->getValue('multistore_limit');
    if (isset($form['multistore_limit']) && $limit != $form['multistore_limit']['#default_value']) {
      /** @var \Drupal\commerce_multistore\StoreStorageInterface $storage */
      $storage = $this->entityTypeManager->getStorage('commerce_store');
      $storage->setStoreLimit($this->entity->bundle(), $limit, $this->entity->getOwnerId());
    }

    // Redirect to the store/ID page.
    $form_state->setRedirectUrl($this->entity->toUrl());
  }

}
