<?php

namespace Drupal\fieldable_panels_panes\Plugin\migrate\source\d7;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Provides Drupal 7 node entity translations source plugin.
 *
 * @MigrateSource(
 *   id = "d7_fieldable_panels_pane_entity_translation",
 *   source_module = "entity_translation"
 * )
 */
class FieldablePanelsPaneEntityTranslation extends FieldableEntity {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('entity_translation', 'et')
      ->fields('et')
      ->fields('fpp', [
        'title',
        'bundle',
      ])
      ->fields('fppr', [
        'log',
        'timestamp',
      ])
      ->condition('et.entity_type', 'fieldable_panels_pane')
      ->condition('et.source', '', '<>');

    $query->addField('fppr', 'uid', 'revision_uid');

    $query->innerJoin('fieldable_panels_panes', 'fpp', 'fpp.fpid = et.entity_id');
    $query->innerJoin('fieldable_panels_panes_revision', 'fppr', 'fppr.vid = et.revision_id');

    if (isset($this->configuration['bundle'])) {
      $query->condition('fpp.bundle', $this->configuration['bundle']);
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $fpid = $row->getSourceProperty('entity_id');
    $vid = $row->getSourceProperty('revision_id');
    $bundle = $row->getSourceProperty('bundle');
    $language = $row->getSourceProperty('language');

    // Get Field API field values.
    foreach ($this->getFields('fieldable_panels_pane', $bundle) as $field_name => $field) {
      // Ensure we're using the right language if the entity is translatable.
      $field_language = $field['translatable'] ? $language : NULL;
      $row->setSourceProperty($field_name, $this->getFieldValues('fieldable_panels_pane', $field_name, $fpid, $vid, $field_language));
    }

    // If the node title was replaced by a real field using the Drupal 7 Title
    // module, use the field value instead of the node title.
    if ($this->moduleExists('title')) {
      $title_field = $row->getSourceProperty('title_field');
      if (isset($title_field[0]['value'])) {
        $row->setSourceProperty('title', $title_field[0]['value']);
      }
    }

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'bundle' => $this->t('The entity type this translation relates to'),
      'entity_id' => $this->t('The entity ID this translation relates to'),
      'revision_id' => $this->t('The entity revision ID this translation relates to'),
      'language' => $this->t('The target language for this translation.'),
      'source' => $this->t('The source language from which this translation was created.'),
      'uid' => $this->t('The author of this translation.'),
      'created' => $this->t('The Unix timestamp when the translation was created.'),
      'changed' => $this->t('The Unix timestamp when the translation was most recently saved.'),
      'title' => $this->t('Panel pane title'),
      'log' => $this->t('Revision log'),
      'timestamp' => $this->t('The timestamp the latest revision of this panel pane was created.'),
      'revision_uid' => $this->t('Revision authored by (uid)'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'entity_id' => [
        'type' => 'integer',
        'alias' => 'et',
      ],
      'language' => [
        'type' => 'string',
        'alias' => 'et',
      ],
    ];
  }

}
