<?php

namespace Drupal\fieldable_panels_panes\Plugin\migrate\field;

use Drupal\migrate_drupal\Plugin\migrate\field\FieldPluginBase;

/**
 * MigrateField Plugin for FPP admin fields.
 *
 * @MigrateField(
 *   id = "fieldable_panel_panes_admin_fields",
 *   core = {7},
 *   type_map = {
 *     "admin_description" = "text",
 *     "admin_title" = "text",
 *     "category" = "text"
 *   },
 *   source_module = "fieldable_panels_pane",
 *   destination_module = "core"
 * )
 */
class AdminFields extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFieldWidgetMap() {
    return [
      'admin_title' => 'string',
      'admin_description' => 'string',
      'category' => 'string',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldFormatterMap() {
    return [
      'admin_title' => 'string',
      'admin_description' => 'string',
      'category' => 'string',
    ];
  }

}
