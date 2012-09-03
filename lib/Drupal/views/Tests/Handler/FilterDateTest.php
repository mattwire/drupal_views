<?php

/**
 * @file
 * Definition of Drupal\views\Tests\Handler\FilterDateTest.
 */

namespace Drupal\views\Tests\Handler;

use Drupal\views\View;

/**
 * Tests the core Drupal\views\Plugin\views\filter\Date handler.
 */
class FilterDateTest extends HandlerTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('views_ui');

  public static function getInfo() {
    return array(
      'name' => 'Filter: Date',
      'description' => 'Test the core Drupal\views\Plugin\views\filter\Date handler.',
      'group' => 'Views Handlers',
    );
  }

  function setUp() {
    parent::setUp();
    // Add some basic test nodes.
    $this->nodes = array();
    $this->nodes[] = $this->drupalCreateNode(array('created' => 100000));
    $this->nodes[] = $this->drupalCreateNode(array('created' => 200000));
    $this->nodes[] = $this->drupalCreateNode(array('created' => 300000));
    $this->nodes[] = $this->drupalCreateNode(array('created' => time() + 86400));

    $this->map = array(
      'nid' => 'nid',
    );
  }

  /**
   * Test the general offset functionality.
   */
  function testOffset() {
    $view = $this->views_test_offset();
    // Test offset for simple operator.
    $view->setDisplay('default');
    $view->initHandlers();
    $view->filter['created']->operator = '>';
    $view->filter['created']->value['type'] = 'offset';
    $view->filter['created']->value['value'] = '+1 hour';
    $view->executeDisplay('default');
    $expected_result = array(
      array('nid' => $this->nodes[3]->nid),
    );
    $this->assertIdenticalResultset($view, $expected_result, $this->map);
    $view->destroy();

    // Test offset for between operator.
    $view->setDisplay('default');
    $view->initHandlers();
    $view->filter['created']->operator = 'between';
    $view->filter['created']->value['type'] = 'offset';
    $view->filter['created']->value['max'] = '+2 days';
    $view->filter['created']->value['min'] = '+1 hour';
    $view->executeDisplay('default');
    $expected_result = array(
      array('nid' => $this->nodes[3]->nid),
    );
    $this->assertIdenticalResultset($view, $expected_result, $this->map);
    $view->destroy();
  }


  /**
   * Tests the filter operator between/not between.
   */
  function testBetween() {
    // Test between with min and max.
    $view = $this->views_test_between();
    $view->setDisplay('default');
    $view->initHandlers();
    $view->filter['created']->operator = 'between';
    $view->filter['created']->value['min'] = format_date(150000, 'custom', 'Y-m-d H:s');
    $view->filter['created']->value['max'] = format_date(250000, 'custom', 'Y-m-d H:s');
    $view->executeDisplay('default');
    $expected_result = array(
      array('nid' => $this->nodes[1]->nid),
    );
    $this->assertIdenticalResultset($view, $expected_result, $this->map);
    $view->destroy();

    // Test between with just max.
    $view = $this->views_test_between();
    $view->setDisplay('default');
    $view->initHandlers();
    $view->filter['created']->operator = 'between';
    $view->filter['created']->value['max'] = format_date(250000, 'custom', 'Y-m-d H:s');
    $view->executeDisplay('default');
    $expected_result = array(
      array('nid' => $this->nodes[0]->nid),
      array('nid' => $this->nodes[1]->nid),
    );
    $this->assertIdenticalResultset($view, $expected_result, $this->map);
    $view->destroy();

    // Test not between with min and max.
    $view = $this->views_test_between();
    $view->setDisplay('default');
    $view->initHandlers();
    $view->filter['created']->operator = 'not between';
    $view->filter['created']->value['min'] = format_date(150000, 'custom', 'Y-m-d H:s');
    $view->filter['created']->value['max'] = format_date(250000, 'custom', 'Y-m-d H:s');
    $view->executeDisplay('default');
    $expected_result = array(
      array('nid' => $this->nodes[0]->nid),
      array('nid' => $this->nodes[2]->nid),
      array('nid' => $this->nodes[3]->nid),
    );
    $this->assertIdenticalResultset($view, $expected_result, $this->map);
    $view->destroy();

    // Test not between with just max.
    $view = $this->views_test_between();
    $view->setDisplay('default');
    $view->initHandlers();
    $view->filter['created']->operator = 'not between';
    $view->filter['created']->value['max'] = format_date(150000, 'custom', 'Y-m-d H:s');
    $view->executeDisplay('default');
    $expected_result = array(
      array('nid' => $this->nodes[1]->nid),
      array('nid' => $this->nodes[2]->nid),
      array('nid' => $this->nodes[3]->nid),
    );
    $this->assertIdenticalResultset($view, $expected_result, $this->map);
    $view->destroy();
  }

  /**
   * Make sure the validation callbacks works.
   */
  function testUiValidation() {
    $view = $this->views_test_between();
    $view->save();

    $admin_user =   $this->drupalCreateUser(array('administer views', 'administer site configuration'));
    $this->drupalLogin($admin_user);
    menu_router_rebuild();
    $this->drupalGet('admin/structure/views/view/test_filter_date_between/edit');
    $this->drupalGet('admin/structure/views/nojs/config-item/test_filter_date_between/default/filter/created');

    $edit = array();
    // Generate a definitive wrong value, which should be checked by validation.
    $edit['options[value][value]'] = $this->randomString() . '-------';
    $this->drupalPost(NULL, $edit, t('Apply'));
    $this->assertText(t('Invalid date format.'), 'Make sure that validation is runned and the invalidate date format is identified.');
  }

  function views_test_between() {
    return $this->createViewFromConfig('test_filter_date_between');
  }

  function views_test_offset() {
    $view = $this->views_test_between();
    return $view;
  }

}
