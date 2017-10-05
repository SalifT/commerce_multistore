<?php

namespace Drupal\commerce_multistore\Plugin\views\argument_default;

use Drupal\user\Plugin\views\argument_default\User;
use Drupal\user\UserInterface;

/**
 * Overrides default argument plugin to extract a user from request.
 */
class MultistoreUser extends User {

  /**
   * {@inheritdoc}
   */
  public function getArgument() {
    // If there is a user object in the current route.
    if ($user = $this->routeMatch->getParameter('user')) {
      if ($user instanceof UserInterface) {
        return $user->id();
      }
    }

    // If option to use an entity author; and entity in current route.
    if (!empty($this->options['user'])) {
      $bag = $this->routeMatch->getParameters()->all();
      $entity = reset($bag);
      if (method_exists($entity, 'getOwnerId')) {
        return $entity->getOwnerId();
      }
    }
  }

}
