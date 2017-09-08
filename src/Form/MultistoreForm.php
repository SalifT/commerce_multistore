<?php

namespace Drupal\commerce_multistore\Form;

use Drupal\commerce_store\Form\StoreForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Overrides the store add/edit form.
 */
class MultistoreForm extends StoreForm {

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    // Redirect to the store/ID page.
    $form_state->setRedirectUrl($this->entity->toUrl());
  }

}
