<?php

/**
 * @file
 * Definition of views_handler_filter_user_current.
 */

namespace Views\user\Plugin\views\filter;

use Drupal\Core\Annotation\Plugin;
use Drupal\views\Plugin\views\filter\BooleanOperator;

/**
 * Filter handler for the current user.
 *
 * @ingroup views_filter_handlers
 *
 * @Plugin(
 *   id = "user_current",
 *   module = "user"
 * )
 */
class Current extends BooleanOperator {

  function construct() {
    parent::construct();
    $this->value_value = t('Is the logged in user');
  }

  function query() {
    $this->ensure_my_table();

    $field = $this->table_alias . '.' . $this->real_field . ' ';
    $or = db_or();

    if (empty($this->value)) {
      $or->condition($field, '***CURRENT_USER***', '<>');
      if ($this->accept_null) {
        $or->isNull($field);
      }
    }
    else {
      $or->condition($field, '***CURRENT_USER***', '=');
    }
    $this->query->add_where($this->options['group'], $or);
  }

}
