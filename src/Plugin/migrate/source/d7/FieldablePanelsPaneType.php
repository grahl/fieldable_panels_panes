<?php

namespace Drupal\fieldable_panels_panes\Plugin\migrate\source\d7;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Drupal 7 fieldable panels pane types source from database.
 *
 * @MigrateSource(
 *   id = "d7_fieldable_panels_pane_type",
 *   source_module = "fieldable_panels_panes"
 * )
 */
class FieldablePanelsPaneType extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('fieldable_panels_pane_type', 'fppt')->fields('fppt');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'name' => $this->t('Human name of the panel pane type.'),
      'description' => $this->t('Description of the panel pane type.'),
      'title' => $this->t('Title label.'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['name']['type'] = 'string';
    return $ids;
  }

}
