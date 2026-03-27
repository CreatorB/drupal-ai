<?php

declare(strict_types=1);

namespace Drupal\drupal_basic_search\Service;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Loads keyword-only search results.
 */
final class SearchExecutorService {

  /**
   * Constructs the service.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly EntityRepositoryInterface $entityRepository,
    private readonly DateFormatterInterface $dateFormatter,
  ) {
  }

  /**
   * Returns keyword-matched results.
   */
  public function execute(array $filters, int $limit = 8): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('status', 1)
      ->range(0, $limit);

    $sort = $filters['sort'] ?? 'created';
    $query->sort($sort === 'title' ? 'title' : 'created', 'DESC');

    if (!empty($filters['content_type'])) {
      $query->condition('type', $filters['content_type']);
    }

    if (!empty($filters['language'])) {
      $lang_map = ['english' => 'en', 'indonesian' => 'id', 'bahasa' => 'id'];
      $lang = mb_strtolower((string) $filters['language']);
      $langcode = $lang_map[$lang] ?? $filters['language'];
      if (mb_strlen((string) $langcode) <= 3) {
        $query->condition('langcode', $langcode);
      }
    }

    $tokens = array_values(array_filter($filters['keyword_tokens'] ?? []));
    if (!empty($tokens)) {
      $keyword_group = $query->orConditionGroup();
      foreach ($tokens as $token) {
        $keyword_group
          ->condition('title', '%' . $token . '%', 'LIKE')
          ->condition('body.value', '%' . $token . '%', 'LIKE');
      }
      $query->condition($keyword_group);
    }
    elseif (!empty($filters['keywords'])) {
      $keyword_group = $query->orConditionGroup()
        ->condition('title', '%' . $filters['keywords'] . '%', 'LIKE')
        ->condition('body.value', '%' . $filters['keywords'] . '%', 'LIKE');
      $query->condition($keyword_group);
    }

    $nids = $query->execute();
    if (!$nids) {
      return [];
    }

    $items = [];
    foreach ($storage->loadMultiple($nids) as $node) {
      $translated = $this->entityRepository->getTranslationFromContext($node);
      $items[] = [
        'title' => $translated->label(),
        'url' => $translated->toUrl()->toString(),
        'type' => $translated->bundle(),
        'langcode' => $translated->language()->getId(),
        'date' => $this->dateFormatter->format($translated->getCreatedTime(), 'custom', 'd M Y'),
        'summary' => $translated->hasField('body') ? trim(strip_tags((string) ($translated->get('body')->summary ?: $translated->get('body')->value))) : '',
      ];
    }

    return $items;
  }

}
