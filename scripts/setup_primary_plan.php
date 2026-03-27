<?php

declare(strict_types=1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\search_api\Entity\Index;
use Drupal\search_api\Entity\Server;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

$entity_type_manager = \Drupal::entityTypeManager();

$ensureVocabulary = static function (string $id, string $label, bool $hierarchical = FALSE): void {
  $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary');
  if (!$storage->load($id)) {
    Vocabulary::create([
      'vid' => $id,
      'name' => $label,
      'description' => $label . ' vocabulary',
      'hierarchy' => $hierarchical ? 1 : 0,
      'langcode' => 'en',
    ])->save();
  }
};

$ensureTerm = static function (string $vocabulary, string $name, int $parent = 0): int {
  $storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
  $existing = $storage->loadByProperties([
    'vid' => $vocabulary,
    'name' => $name,
  ]);
  if ($existing) {
    return (int) reset($existing)->id();
  }

  $term = Term::create([
    'vid' => $vocabulary,
    'name' => $name,
    'parent' => $parent ? [$parent] : [],
    'langcode' => 'en',
  ]);
  $term->save();
  return (int) $term->id();
};

$ensureNodeType = static function (string $type, string $name, string $description = ''): void {
  $storage = \Drupal::entityTypeManager()->getStorage('node_type');
  if (!$storage->load($type)) {
    NodeType::create([
      'type' => $type,
      'name' => $name,
      'description' => $description,
      'new_revision' => TRUE,
      'preview_mode' => 1,
      'display_submitted' => TRUE,
    ])->save();
  }
};

$ensureField = static function (
  string $entity_type,
  string $bundle,
  string $field_name,
  string $field_type,
  array $storage_settings,
  string $label,
  bool $required = FALSE,
  array $field_settings = [],
  int $cardinality = 1
): void {
  if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => $field_type,
      'settings' => $storage_settings,
      'cardinality' => $cardinality,
      'translatable' => TRUE,
    ])->save();
  }

  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'label' => $label,
      'required' => $required,
      'translatable' => TRUE,
      'settings' => $field_settings,
    ])->save();
  }
};

$setFormComponent = static function (string $entity_type, string $bundle, string $field_name, string $widget, int $weight, array $settings = []): void {
  $display = EntityFormDisplay::load($entity_type . '.' . $bundle . '.default') ?: EntityFormDisplay::create([
    'targetEntityType' => $entity_type,
    'bundle' => $bundle,
    'mode' => 'default',
    'status' => TRUE,
  ]);

  $display->setComponent($field_name, [
    'type' => $widget,
    'weight' => $weight,
    'settings' => $settings,
  ]);
  $display->save();
};

$setViewComponent = static function (string $entity_type, string $bundle, string $field_name, string $formatter, int $weight, array $settings = []): void {
  $display = EntityViewDisplay::load($entity_type . '.' . $bundle . '.default') ?: EntityViewDisplay::create([
    'targetEntityType' => $entity_type,
    'bundle' => $bundle,
    'mode' => 'default',
    'status' => TRUE,
  ]);

  $display->setComponent($field_name, [
    'type' => $formatter,
    'label' => 'above',
    'weight' => $weight,
    'settings' => $settings,
  ]);
  $display->save();
};

$ensureVocabulary('category', 'Article Category', TRUE);
$ensureVocabulary('event_type', 'Event Type');
$ensureVocabulary('tags', 'Tags');

$finance = $ensureTerm('category', 'Finance');
$business = $ensureTerm('category', 'Business');
$technology = $ensureTerm('category', 'Technology');
$trade = $ensureTerm('category', 'Trade');
$investment = $ensureTerm('category', 'Investment');
$policy = $ensureTerm('category', 'Policy');

$ensureTerm('event_type', 'Training');
$ensureTerm('event_type', 'Workshop');
$ensureTerm('event_type', 'Conference');
$ensureTerm('event_type', 'Webinar');
$ensureTerm('event_type', 'Networking');

$ensureNodeType('event', 'Event', 'Events and training activities.');
$ensureNodeType('press_release', 'Press Release', 'Official media and announcement content.');
$ensureNodeType('speech_commentary', 'Speech / Commentary', 'Executive speeches and thought leadership content.');

$ensureField('node', 'article', 'field_category', 'entity_reference', ['target_type' => 'taxonomy_term'], 'Category', FALSE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => ['target_bundles' => ['category' => 'category']],
]);
$ensureField('node', 'article', 'field_tags', 'entity_reference', ['target_type' => 'taxonomy_term'], 'Tags', FALSE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => ['target_bundles' => ['tags' => 'tags']],
], -1);
$ensureField('node', 'article', 'field_summary_plain', 'string_long', [], 'Summary');
$ensureField('node', 'article', 'field_published_date', 'datetime', ['datetime_type' => 'datetime'], 'Published Date');
$ensureField('node', 'article', 'field_reading_time', 'integer', ['unsigned' => TRUE], 'Reading Time');

$ensureField('node', 'event', 'field_event_description', 'text_long', [], 'Description');
$ensureField('node', 'event', 'field_event_date', 'daterange', ['datetime_type' => 'datetime'], 'Event Date/Time', TRUE);
$ensureField('node', 'event', 'field_event_location', 'string', ['max_length' => 255], 'Location');
$ensureField('node', 'event', 'field_event_image', 'image', ['uri_scheme' => 'public', 'default_image' => []], 'Featured Image');
$ensureField('node', 'event', 'field_event_category', 'entity_reference', ['target_type' => 'taxonomy_term'], 'Category', FALSE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => ['target_bundles' => ['category' => 'category']],
]);
$ensureField('node', 'event', 'field_registration_link', 'link', [], 'Registration Link');
$ensureField('node', 'event', 'field_event_type', 'entity_reference', ['target_type' => 'taxonomy_term'], 'Event Type', FALSE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => ['target_bundles' => ['event_type' => 'event_type']],
]);

$ensureField('node', 'press_release', 'field_release_date', 'datetime', ['datetime_type' => 'datetime'], 'Release Date');
$ensureField('node', 'press_release', 'field_contact_person', 'string', ['max_length' => 255], 'Contact Person');
$ensureField('node', 'press_release', 'field_media_files', 'file', ['uri_scheme' => 'public'], 'Media Files', FALSE, ['file_extensions' => 'pdf doc docx jpg jpeg png'], -1);
$ensureField('node', 'press_release', 'field_press_category', 'entity_reference', ['target_type' => 'taxonomy_term'], 'Category', FALSE, [
  'handler' => 'default:taxonomy_term',
  'handler_settings' => ['target_bundles' => ['category' => 'category']],
]);

$ensureField('node', 'speech_commentary', 'field_speaker_name', 'string', ['max_length' => 255], 'Speaker Name');
$ensureField('node', 'speech_commentary', 'field_event_occasion', 'string', ['max_length' => 255], 'Event / Occasion');
$ensureField('node', 'speech_commentary', 'field_speech_date', 'datetime', ['datetime_type' => 'datetime'], 'Speech Date');
$ensureField('node', 'speech_commentary', 'field_speech_image', 'image', ['uri_scheme' => 'public', 'default_image' => []], 'Featured Image');

$setFormComponent('node', 'article', 'field_category', 'options_select', 5);
$setFormComponent('node', 'article', 'field_tags', 'entity_reference_autocomplete_tags', 6, [
  'match_operator' => 'CONTAINS',
  'size' => 60,
  'placeholder' => '',
]);
$setFormComponent('node', 'article', 'field_summary_plain', 'string_textarea', 7);
$setFormComponent('node', 'article', 'field_published_date', 'datetime_default', 8);
$setFormComponent('node', 'article', 'field_reading_time', 'number', 9);

$setViewComponent('node', 'article', 'field_category', 'entity_reference_label', 5, ['link' => TRUE]);
$setViewComponent('node', 'article', 'field_tags', 'entity_reference_label', 6, ['link' => TRUE]);
$setViewComponent('node', 'article', 'field_summary_plain', 'basic_string', 7);
$setViewComponent('node', 'article', 'field_published_date', 'datetime_default', 8, ['format_type' => 'medium']);
$setViewComponent('node', 'article', 'field_reading_time', 'number_integer', 9, ['thousand_separator' => '', 'prefix_suffix' => TRUE]);

$setFormComponent('node', 'event', 'field_event_description', 'text_textarea', 2, ['rows' => 6]);
$setFormComponent('node', 'event', 'field_event_date', 'daterange_default', 3);
$setFormComponent('node', 'event', 'field_event_location', 'string_textfield', 4);
$setFormComponent('node', 'event', 'field_event_image', 'image_image', 5);
$setFormComponent('node', 'event', 'field_event_category', 'options_select', 6);
$setFormComponent('node', 'event', 'field_registration_link', 'link_default', 7);
$setFormComponent('node', 'event', 'field_event_type', 'options_select', 8);

$setViewComponent('node', 'event', 'field_event_description', 'text_default', 2);
$setViewComponent('node', 'event', 'field_event_date', 'daterange_default', 3, ['format_type' => 'medium']);
$setViewComponent('node', 'event', 'field_event_location', 'string', 4);
$setViewComponent('node', 'event', 'field_event_image', 'image', 5, ['image_style' => 'large']);
$setViewComponent('node', 'event', 'field_event_category', 'entity_reference_label', 6, ['link' => TRUE]);
$setViewComponent('node', 'event', 'field_registration_link', 'link', 7);
$setViewComponent('node', 'event', 'field_event_type', 'entity_reference_label', 8, ['link' => TRUE]);

$setFormComponent('node', 'press_release', 'field_release_date', 'datetime_default', 2);
$setFormComponent('node', 'press_release', 'field_contact_person', 'string_textfield', 3);
$setFormComponent('node', 'press_release', 'field_media_files', 'file_generic', 4);
$setFormComponent('node', 'press_release', 'field_press_category', 'options_select', 5);

$setViewComponent('node', 'press_release', 'field_release_date', 'datetime_default', 2, ['format_type' => 'medium']);
$setViewComponent('node', 'press_release', 'field_contact_person', 'string', 3);
$setViewComponent('node', 'press_release', 'field_media_files', 'file_default', 4);
$setViewComponent('node', 'press_release', 'field_press_category', 'entity_reference_label', 5, ['link' => TRUE]);

$setFormComponent('node', 'speech_commentary', 'field_speaker_name', 'string_textfield', 2);
$setFormComponent('node', 'speech_commentary', 'field_event_occasion', 'string_textfield', 3);
$setFormComponent('node', 'speech_commentary', 'field_speech_date', 'datetime_default', 4);
$setFormComponent('node', 'speech_commentary', 'field_speech_image', 'image_image', 5);

$setViewComponent('node', 'speech_commentary', 'field_speaker_name', 'string', 2);
$setViewComponent('node', 'speech_commentary', 'field_event_occasion', 'string', 3);
$setViewComponent('node', 'speech_commentary', 'field_speech_date', 'datetime_default', 4, ['format_type' => 'medium']);
$setViewComponent('node', 'speech_commentary', 'field_speech_image', 'image', 5, ['image_style' => 'large']);

$translation_items = [
  ['node', 'article'],
  ['node', 'event'],
  ['node', 'press_release'],
  ['node', 'speech_commentary'],
  ['taxonomy_term', 'category'],
  ['taxonomy_term', 'event_type'],
];

foreach ($translation_items as [$entity_type, $bundle]) {
  \Drupal::configFactory()
    ->getEditable('language.content_settings.' . $entity_type . '.' . $bundle)
    ->set('id', $entity_type . '.' . $bundle)
    ->set('target_entity_type_id', $entity_type)
    ->set('target_bundle', $bundle)
    ->set('default_langcode', 'site_default')
    ->set('language_alterable', TRUE)
    ->set('third_party_settings.content_translation.enabled', TRUE)
    ->save();
}

$server = Server::load('content_server');
if (!$server) {
  $server = Server::create([
    'id' => 'content_server',
    'name' => 'Content Database Server',
    'status' => TRUE,
    'backend' => 'search_api_db',
    'backend_config' => [
      'database' => 'default:default',
      'min_chars' => 3,
      'matching' => 'words',
      'phrase' => 'bigram',
      'autocomplete' => [
        'suggest_suffix' => TRUE,
        'suggest_words' => TRUE,
      ],
    ],
  ]);
  $server->save();
}

$index = Index::load('content_index');
if (!$index) {
  $index = Index::create([
    'id' => 'content_index',
    'name' => 'Content Index',
    'status' => TRUE,
    'datasource_settings' => [
      'entity:node' => [
        'bundles' => [
          'default' => FALSE,
          'selected' => [
            'article' => 'article',
            'event' => 'event',
            'press_release' => 'press_release',
            'speech_commentary' => 'speech_commentary',
          ],
        ],
        'languages' => [
          'default' => FALSE,
          'selected' => [
            'en' => 'en',
            'id' => 'id',
          ],
        ],
      ],
    ],
    'tracker_settings' => [
      'default' => ['indexing_order' => 'fifo'],
    ],
    'server' => 'content_server',
    'options' => [
      'cron_limit' => 50,
      'index_directly' => TRUE,
    ],
    'field_settings' => [
      'title' => [
        'label' => 'Title',
        'datasource_id' => 'entity:node',
        'property_path' => 'title',
        'type' => 'text',
        'boost' => 5.0,
      ],
      'body' => [
        'label' => 'Body',
        'datasource_id' => 'entity:node',
        'property_path' => 'body',
        'type' => 'text',
      ],
      'type' => [
        'label' => 'Content type',
        'datasource_id' => 'entity:node',
        'property_path' => 'type',
        'type' => 'string',
      ],
      'created' => [
        'label' => 'Created',
        'datasource_id' => 'entity:node',
        'property_path' => 'created',
        'type' => 'date',
      ],
      'langcode' => [
        'label' => 'Language',
        'datasource_id' => 'entity:node',
        'property_path' => 'langcode',
        'type' => 'string',
      ],
    ],
    'processor_settings' => [
      'add_url' => ['weights' => ['preprocess_index' => -30]],
      'aggregated_field' => ['weights' => ['add_properties' => 20]],
      'ignorecase' => ['all_fields' => FALSE, 'fields' => ['title' => 'title', 'body' => 'body']],
      'rendered_item' => ['weights' => ['add_properties' => 0, 'pre_index_save' => -10]],
    ],
  ]);
}

$index->set('server', 'content_server');
$index->set('field_settings', [
  'title' => [
    'label' => 'Title',
    'datasource_id' => 'entity:node',
    'property_path' => 'title',
    'type' => 'text',
    'boost' => 5.0,
  ],
  'body' => [
    'label' => 'Body',
    'datasource_id' => 'entity:node',
    'property_path' => 'body',
    'type' => 'text',
  ],
  'type' => [
    'label' => 'Content type',
    'datasource_id' => 'entity:node',
    'property_path' => 'type',
    'type' => 'string',
  ],
  'created' => [
    'label' => 'Created',
    'datasource_id' => 'entity:node',
    'property_path' => 'created',
    'type' => 'date',
  ],
  'langcode' => [
    'label' => 'Language',
    'datasource_id' => 'entity:node',
    'property_path' => 'langcode',
    'type' => 'string',
  ],
]);
$index->set('processor_settings', [
  'add_url' => ['weights' => ['preprocess_index' => -30]],
  'aggregated_field' => ['weights' => ['add_properties' => 20]],
  'ignorecase' => ['all_fields' => FALSE, 'fields' => ['title' => 'title', 'body' => 'body']],
  'rendered_item' => ['weights' => ['add_properties' => 0, 'pre_index_save' => -10]],
]);
$index->save();

$node_storage = $entity_type_manager->getStorage('node');
$existing_event = $node_storage->loadByProperties(['type' => 'event', 'title' => 'Executive Leadership Forum 2026']);
if (!$existing_event) {
  $event = $node_storage->create([
    'type' => 'event',
    'title' => 'Executive Leadership Forum 2026',
    'status' => 1,
    'uid' => 1,
    'langcode' => 'en',
    'field_event_description' => [
      'value' => 'A showcase event for leadership, policy, and regional business conversations.',
      'format' => 'basic_html',
    ],
    'field_event_location' => 'Singapore Conference Hall',
    'field_event_date' => [
      'value' => gmdate('Y-m-d\TH:i:s', strtotime('+20 days')),
      'end_value' => gmdate('Y-m-d\TH:i:s', strtotime('+20 days +3 hours')),
    ],
    'field_event_category' => ['target_id' => $business],
    'field_registration_link' => ['uri' => 'https://example.com/register', 'title' => 'Register now'],
  ]);
  $event->save();
}

$existing_press = $node_storage->loadByProperties(['type' => 'press_release', 'title' => 'FXMedia launches Drupal AI showcase']);
if (!$existing_press) {
  $press_release = $node_storage->create([
    'type' => 'press_release',
    'title' => 'FXMedia launches Drupal AI showcase',
    'status' => 1,
    'uid' => 1,
    'langcode' => 'en',
    'body' => [
      'value' => 'This sample press release demonstrates multilingual publishing, smart search, and personalized recommendations.',
      'format' => 'basic_html',
    ],
    'field_release_date' => ['value' => gmdate('Y-m-d\TH:i:s')],
    'field_contact_person' => 'Client Services Team',
    'field_press_category' => ['target_id' => $technology],
  ]);
  $press_release->save();
}

$existing_speech = $node_storage->loadByProperties(['type' => 'speech_commentary', 'title' => 'Trade and innovation keynote']);
if (!$existing_speech) {
  $speech = $node_storage->create([
    'type' => 'speech_commentary',
    'title' => 'Trade and innovation keynote',
    'status' => 1,
    'uid' => 1,
    'langcode' => 'en',
    'body' => [
      'value' => 'A sample commentary node for executive messaging and thought leadership.',
      'format' => 'basic_html',
    ],
    'field_speaker_name' => 'Chief Executive Officer',
    'field_event_occasion' => 'Annual Member Summit',
    'field_speech_date' => ['value' => gmdate('Y-m-d\TH:i:s', strtotime('-5 days'))],
  ]);
  $speech->save();
}

$articles = $node_storage->loadByProperties(['type' => 'article']);
$category_cycle = [$finance, $business, $technology, $trade, $investment, $policy];
$counter = 0;
foreach ($articles as $article) {
  if ($article->hasField('field_category') && $article->get('field_category')->isEmpty()) {
    $article->set('field_category', ['target_id' => $category_cycle[$counter % count($category_cycle)]]);
  }
  if ($article->hasField('field_summary_plain') && $article->get('field_summary_plain')->isEmpty()) {
    $article->set('field_summary_plain', mb_substr(trim(strip_tags((string) $article->get('body')->value)), 0, 160));
  }
  if ($article->hasField('field_published_date') && $article->get('field_published_date')->isEmpty()) {
    $article->set('field_published_date', ['value' => gmdate('Y-m-d\TH:i:s', (int) $article->getCreatedTime())]);
  }
  if ($article->hasField('field_reading_time') && $article->get('field_reading_time')->isEmpty()) {
    $word_count = str_word_count(strip_tags((string) $article->get('body')->value));
    $article->set('field_reading_time', max(1, (int) ceil($word_count / 180)));
  }
  $article->save();
  $counter++;
}

print "Primary site plan setup complete.\n";
