<?php

namespace Drupal\fieldable_panels_panes\Plugin\migrate\source\d7;

/**
 * Drupal 7 node revision source from database.
 *
 * @MigrateSource(
 *   id = "d7_fieldable_panels_pane_revision",
 *   source_module = "fieldable_panels_panes"
 * )
 */
class FieldablePanelsPaneRevision extends FieldablePanelsPane {

  /**
   * JOIN addendum.
   *
   * The join options between the fieldable_panels_panes and the
   * fieldable_panels_panes_revision table.
   */
  const JOIN = 'fpp.fpid = fppr.fpid AND fpp.vid <> fppr.vid';

  /**
   * {@inheritdoc}
   */
  public function fields() {
    // Use all the fieldable_panels_panes fields plus the vid that
    // identifies the version.
    return parent::fields() + [
      'vid' => $this->t('The primary identifier for this version.'),
      'log' => $this->t('Revision Log message'),
      'timestamp' => $this->t('Revision timestamp'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['vid']['type'] = 'integer';
    $ids['vid']['alias'] = 'fppr';
    return $ids;
  }

}
