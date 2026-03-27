<?php

declare(strict_types=1);

use Drupal\path_alias\Entity\PathAlias;

$storage = \Drupal::entityTypeManager()->getStorage('node');
$alias_storage = \Drupal::entityTypeManager()->getStorage('path_alias');

$pages = [
  [
    'title' => 'About',
    'alias' => '/about',
    'body' => '<p>This showcase demonstrates how a Drupal 11 multisite platform can support a flagship institutional website with clear governance, flexible publishing, and AI-assisted content discovery.</p><p>The architecture is designed for associations, chambers, public-facing institutions, and member organisations that need multiple sites on one shared platform while maintaining a premium primary experience.</p>',
  ],
  [
    'title' => 'Membership',
    'alias' => '/membership',
    'body' => '<p>This sample membership page illustrates how the platform can present member benefits, leadership programmes, event access, advocacy updates, and partner opportunities in a single clear journey.</p><p>In a production rollout, this section can connect to forms, CRM workflows, gated resources, and member-only content.</p>',
  ],
];

foreach ($pages as $page) {
  $existing = $storage->loadByProperties(['type' => 'page', 'title' => $page['title']]);
  $node = $existing ? reset($existing) : $storage->create([
    'type' => 'page',
    'title' => $page['title'],
    'langcode' => 'en',
    'uid' => 1,
    'status' => 1,
    'promote' => 0,
  ]);

  $node->set('body', ['value' => $page['body'], 'format' => 'basic_html']);
  $node->save();

  $alias = $alias_storage->loadByProperties(['alias' => $page['alias']]);
  if (!$alias) {
    PathAlias::create([
      'path' => '/node/' . $node->id(),
      'alias' => $page['alias'],
      'langcode' => 'en',
    ])->save();
  }
}

print "Showcase pages created.\n";
