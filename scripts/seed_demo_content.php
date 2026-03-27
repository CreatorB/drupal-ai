<?php

declare(strict_types=1);

$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');
$node_storage = \Drupal::entityTypeManager()->getStorage('node');

$category_ids = [];
foreach (['Finance', 'Business', 'Technology', 'Trade', 'Investment', 'Policy'] as $name) {
  $term = reset($term_storage->loadByProperties([
    'vid' => 'category',
    'name' => $name,
  ]));
  if ($term) {
    $category_ids[$name] = (int) $term->id();
  }
}

$event_type_ids = [];
foreach (['Training', 'Workshop', 'Conference', 'Webinar', 'Networking'] as $name) {
  $term = reset($term_storage->loadByProperties([
    'vid' => 'event_type',
    'name' => $name,
  ]));
  if ($term) {
    $event_type_ids[$name] = (int) $term->id();
  }
}

$items = [
  [
    'type' => 'article',
    'title' => 'Cross-border payment trends for 2026',
    'langcode' => 'en',
    'body' => 'A showcase article focused on payment rails, SME readiness, and regional digital trade momentum.',
    'category' => 'Finance',
  ],
  [
    'type' => 'article',
    'title' => 'Green investment opportunities across Southeast Asia',
    'langcode' => 'en',
    'body' => 'An English showcase article about green investment opportunities, industrial transition, and supportive policy frameworks.',
    'category' => 'Investment',
  ],
  [
    'type' => 'article',
    'title' => 'Manufacturing resilience and trade corridors',
    'langcode' => 'en',
    'body' => 'An editorial sample about logistics, policy coordination, and industry resilience for business members.',
    'category' => 'Trade',
  ],
  [
    'type' => 'event',
    'title' => 'ASEAN Digital Trade Webinar',
    'langcode' => 'en',
    'description' => 'A webinar covering customs modernization, e-invoicing, and AI-enabled export workflows.',
    'location' => 'Online',
    'event_type' => 'Webinar',
    'category' => 'Technology',
    'days' => 12,
  ],
  [
    'type' => 'event',
    'title' => 'Export readiness training for SMEs',
    'langcode' => 'en',
    'description' => 'A practical training session focused on export readiness, partner discovery, and market entry planning.',
    'location' => 'Surabaya Business Hub',
    'event_type' => 'Training',
    'category' => 'Trade',
    'days' => 28,
  ],
  [
    'type' => 'press_release',
    'title' => 'Industry council welcomes new trade facilitation measures',
    'langcode' => 'en',
    'body' => 'A sample press release announcing stronger support for exporters, logistics improvements, and business competitiveness.',
    'category' => 'Policy',
  ],
  [
    'type' => 'press_release',
    'title' => 'Association launches new member innovation agenda',
    'langcode' => 'en',
    'body' => 'A sample press release about member innovation programs, digital acceleration, and cross-sector collaboration.',
    'category' => 'Business',
  ],
  [
    'type' => 'speech_commentary',
    'title' => 'Chairman\'s remarks on regional competitiveness',
    'langcode' => 'en',
    'body' => 'A speech sample highlighting competitiveness, talent, and productivity priorities for the business community.',
    'speaker' => 'Chairman',
    'occasion' => 'Regional Competitiveness Forum',
    'days' => -3,
  ],
  [
    'type' => 'speech_commentary',
    'title' => 'Remarks on industrial transformation and talent readiness',
    'langcode' => 'en',
    'body' => 'A sample speech highlighting industrial transformation, talent readiness, and public-private collaboration.',
    'speaker' => 'President',
    'occasion' => 'National Industry Forum',
    'days' => -6,
  ],
];

foreach ($items as $item) {
  $existing = $node_storage->loadByProperties([
    'type' => $item['type'],
    'title' => $item['title'],
  ]);
  if ($existing) {
    continue;
  }

  $values = [
    'type' => $item['type'],
    'title' => $item['title'],
    'langcode' => $item['langcode'],
    'uid' => 1,
    'status' => 1,
    'promote' => 1,
  ];

  if ($item['type'] === 'article') {
    $values['body'] = ['value' => $item['body'], 'format' => 'basic_html'];
    $values['field_summary_plain'] = mb_substr($item['body'], 0, 140);
    $values['field_published_date'] = ['value' => gmdate('Y-m-d\TH:i:s')];
    $values['field_reading_time'] = 2;
    if (!empty($category_ids[$item['category']])) {
      $values['field_category'] = ['target_id' => $category_ids[$item['category']]];
    }
  }

  if ($item['type'] === 'event') {
    $values['field_event_description'] = ['value' => $item['description'], 'format' => 'basic_html'];
    $values['field_event_location'] = $item['location'];
    $values['field_event_date'] = [
      'value' => gmdate('Y-m-d\TH:i:s', strtotime('+' . $item['days'] . ' days')),
      'end_value' => gmdate('Y-m-d\TH:i:s', strtotime('+' . $item['days'] . ' days +2 hours')),
    ];
    $values['field_registration_link'] = ['uri' => 'https://example.com/register', 'title' => 'Register now'];
    if (!empty($category_ids[$item['category']])) {
      $values['field_event_category'] = ['target_id' => $category_ids[$item['category']]];
    }
    if (!empty($event_type_ids[$item['event_type']])) {
      $values['field_event_type'] = ['target_id' => $event_type_ids[$item['event_type']]];
    }
  }

  if ($item['type'] === 'press_release') {
    $values['body'] = ['value' => $item['body'], 'format' => 'basic_html'];
    $values['field_release_date'] = ['value' => gmdate('Y-m-d\TH:i:s')];
    $values['field_contact_person'] = 'Communications Team';
    if (!empty($category_ids[$item['category']])) {
      $values['field_press_category'] = ['target_id' => $category_ids[$item['category']]];
    }
  }

  if ($item['type'] === 'speech_commentary') {
    $values['body'] = ['value' => $item['body'], 'format' => 'basic_html'];
    $values['field_speaker_name'] = $item['speaker'];
    $values['field_event_occasion'] = $item['occasion'];
    $values['field_speech_date'] = ['value' => gmdate('Y-m-d\TH:i:s', strtotime($item['days'] . ' days'))];
  }

  $node = $node_storage->create($values);
  $node->save();
}

print "Demo content seeded.\n";
