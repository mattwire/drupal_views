<?php

/**
 * @file
 * Definition of Drupal\views\Tests\Handler\FieldDateTest.
 */

namespace Drupal\views\Tests\Handler;

use Drupal\views\Tests\ViewsSqlTest;

/**
 * Tests the core views_handler_field_date handler.
 */
class FieldDateTest extends ViewsSqlTest {
  public static function getInfo() {
    return array(
      'name' => 'Field: Date',
      'description' => 'Test the core views_handler_field_date handler.',
      'group' => 'Views Handlers',
    );
  }

  function viewsData() {
    $data = parent::viewsData();
    $data['views_test']['created']['field']['handler'] = 'views_handler_field_date';
    return $data;
  }

  public function testFieldDate() {
    $view = $this->getBasicView();

    $view->display['default']->handler->override_option('fields', array(
      'created' => array(
        'id' => 'created',
        'table' => 'views_test',
        'field' => 'created',
        'relationship' => 'none',
        // c is iso 8601 date format @see http://php.net/manual/en/function.date.php
        'custom_date_format' => 'c',
      ),
    ));
    $time = gmmktime(0, 0, 0, 1, 1, 2000);

    $this->executeView($view);

    $timezones = array(
      NULL,
      'UTC',
      'America/New_York',
    );
    foreach ($timezones as $timezone) {
      $dates = array(
        'small' => format_date($time, 'small', '', $timezone),
        'medium' => format_date($time, 'medium', '', $timezone),
        'large' => format_date($time, 'large', '', $timezone),
        'custom' => format_date($time, 'custom', 'c', $timezone),
      );
      $this->assertRenderedDatesEqual($view, $dates, $timezone);
    }

    $intervals = array(
      'raw time ago' => format_interval(REQUEST_TIME - $time, 2),
      'time ago' => t('%time ago', array('%time' => format_interval(REQUEST_TIME - $time, 2))),
      // TODO write tests for them
//       'raw time span' => format_interval(REQUEST_TIME - $time, 2),
//       'time span' => t('%time hence', array('%time' => format_interval(REQUEST_TIME - $time, 2))),
    );
    $this->assertRenderedDatesEqual($view, $intervals);
  }

  protected function assertRenderedDatesEqual($view, $map, $timezone = NULL) {
    foreach ($map as $date_format => $expected_result) {
      $view->field['created']->options['date_format'] = $date_format;
      $t_args = array(
        '%value' => $expected_result,
        '%format' => $date_format,
      );
      if (isset($timezone)) {
        $t_args['%timezone'] = $timezone;
        $message = t('Value %value in %format format for timezone %timezone matches.', $t_args);
        $view->field['created']->options['timezone'] = $timezone;
      }
      else {
        $message = t('Value %value in %format format matches.', $t_args);
      }
      $actual_result = $view->field['created']->advanced_render($view->result[0]);
      $this->assertEqual($expected_result, $actual_result, $message);
    }
  }
}
