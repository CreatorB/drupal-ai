<?php

declare(strict_types=1);

$node_storage = \Drupal::entityTypeManager()->getStorage('node');

$updates = [
  'Pembaruan ekonomi kawasan' => [
    'title' => 'Regional economic outlook update',
    'langcode' => 'en',
    'body' => 'An English sample article about market signals, trade policy, and business outlook across Southeast Asia.',
    'summary' => 'An English sample article about market signals and business outlook across Southeast Asia.',
  ],
  'Strategi ekspor untuk anggota' => [
    'title' => 'Export strategy update for members',
    'langcode' => 'en',
    'body' => 'A content sample about trade priorities, growth strategy, and digital transformation for members.',
    'summary' => 'A content sample about trade priorities, growth strategy, and digital transformation for members.',
  ],
  'Peluang investasi hijau di Asia Tenggara' => [
    'title' => 'Green investment opportunities across Southeast Asia',
    'langcode' => 'en',
    'body' => 'An English showcase article about green investment opportunities, industrial transition, and supportive policy frameworks.',
    'summary' => 'An English showcase article about green investment opportunities and industrial transition.',
  ],
  'Pelatihan ekspor untuk UKM' => [
    'title' => 'Export readiness training for SMEs',
    'langcode' => 'en',
    'description' => 'A practical training session focused on export readiness, partner discovery, and market entry planning.',
  ],
  'Organisasi luncurkan agenda inovasi anggota' => [
    'title' => 'Association launches new member innovation agenda',
    'langcode' => 'en',
    'body' => 'A sample press release about member innovation programs, digital acceleration, and cross-sector collaboration.',
  ],
  'Komentar pimpinan tentang transformasi digital' => [
    'title' => 'Leadership commentary on digital transformation',
    'langcode' => 'en',
    'body' => 'A commentary sample focused on innovation, talent, and regional collaboration.',
    'speaker' => 'Executive Director',
    'occasion' => 'Annual Business Forum',
  ],
  'Pidato tentang transformasi industri dan talenta' => [
    'title' => 'Remarks on industrial transformation and talent readiness',
    'langcode' => 'en',
    'body' => 'A sample speech highlighting industrial transformation, talent readiness, and public-private collaboration.',
    'speaker' => 'President',
    'occasion' => 'National Industry Forum',
  ],
];

foreach ($updates as $original_title => $update) {
  $matches = $node_storage->loadByProperties(['title' => $original_title]);
  foreach ($matches as $node) {
    $node->setTitle($update['title']);
    $node->set('langcode', $update['langcode']);

    if ($node->hasField('body') && isset($update['body'])) {
      $node->set('body', ['value' => $update['body'], 'format' => 'basic_html']);
    }
    if ($node->hasField('field_summary_plain') && isset($update['summary'])) {
      $node->set('field_summary_plain', $update['summary']);
    }
    if ($node->hasField('field_event_description') && isset($update['description'])) {
      $node->set('field_event_description', ['value' => $update['description'], 'format' => 'basic_html']);
    }
    if ($node->hasField('field_speaker_name') && isset($update['speaker'])) {
      $node->set('field_speaker_name', $update['speaker']);
    }
    if ($node->hasField('field_event_occasion') && isset($update['occasion'])) {
      $node->set('field_event_occasion', $update['occasion']);
    }

    $node->save();
  }
}

print "Demo content normalized to English.\n";
