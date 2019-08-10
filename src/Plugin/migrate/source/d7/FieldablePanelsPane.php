<?php

namespace Drupal\fieldable_panels_panes\Plugin\migrate\source\d7;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drupal 7 node source from database.
 *
 * @MigrateSource(
 *   id = "d7_fieldable_panels_pane",
 *   source_module = "fieldable_panels_panes"
 * )
 */
class FieldablePanelsPane extends FieldableEntity {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, StateInterface $state, EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $state, $entity_manager);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $migration,
      $container->get('state'),
      $container->get('entity.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * The join options between the node and the node_revisions table.
   */
  const JOIN = 'fpp.vid = fppr.vid';

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Select node in its last revision.
    $query = $this->select('fieldable_panels_panes_revision', 'fppr')
      ->fields('fpp', [
        'fpid',
        'bundle',
        'link',
        'path',
        'admin_title',
        'admin_description',
        'category',
        'reusable',
        'view_access',
        'edit_access',
        'language',
        'created',
        'changed',
      ])
      ->fields('fppr', [
        'vid',
        'title',
        'log',
        'timestamp',
      ]);
    $query->addField('fppr', 'uid', 'entity_uid');
    $query->addField('fppr', 'uid', 'revision_uid');
    $query->innerJoin('fieldable_panels_panes', 'fpp', static::JOIN);

    if (isset($this->configuration['bundle'])) {
      $query->condition('fpp.bundle', $this->configuration['bundle']);
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $fpid = $row->getSourceProperty('fpid');
    $vid = $row->getSourceProperty('vid');
    $bundle = $row->getSourceProperty('bundle');

    // If this entity was translated using Entity Translation, we need to get
    // its source language to get the field values in the right language.
    // The  translations will be migrated by the
    // d7_fieldable_panels_pane_entity_translation migration.
    $entity_translatable = $this->isEntityTranslatable('fieldable_panels_pane');
    $source_language = $this->getEntityTranslationSourceLanguage('fieldable_panels_pane', $fpid);
    $language = $entity_translatable && $source_language ? $source_language : $row->getSourceProperty('language');

    // Get Field API field values.
    foreach ($this->getFields('fieldable_panels_pane', $bundle) as $field_name => $field) {
      // Ensure we're using the right language if the entity and the field are
      // translatable.
      $field_language = $entity_translatable && $field['translatable'] ? $language : NULL;
      $row->setSourceProperty($field_name, $this->getFieldValues('fieldable_panels_pane', $field_name, $fpid, $vid, $field_language));
    }

    // If the title was replaced by a real field using the Drupal 7 Title
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
    $fields = [
      'fpid' => $this->t('Fieldable Panel Pane ID'),
      'bundle' => $this->t('Bundle'),
      'title' => $this->t('Title'),
      'entity_uid' => $this->t('Panel pane authored by (uid)'),
      'revision_uid' => $this->t('Revision authored by (uid)'),
      'admin_title' => $this->t('Administrative title for panel pane'),
      'admin_description' => $this->t('Administrative description for panel pane'),
      'category' => $this->t('Panel pane category'),
      'reusable' => $this->t('Whether the panel pane is reusable'),
      'created' => $this->t('Created timestamp'),
      'changed' => $this->t('Modified timestamp'),
      'revision' => $this->t('Create new revision'),
      'language' => $this->t('Language (fr, en, ...)'),
      'timestamp' => $this->t('The timestamp the latest revision of this panel pane was created.'),
    ];
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['fpid']['type'] = 'integer';
    $ids['fpid']['alias'] = 'fpp';
    return $ids;
  }

}
