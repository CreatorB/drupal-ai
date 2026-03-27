<?php

declare(strict_types=1);

namespace Drupal\drupal_ai_search\Service;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Connection;

/**
 * Loads content results for the AI search page.
 */
final class SearchExecutorService {

  /**
   * Constructs the service.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly EntityRepositoryInterface $entityRepository,
    private readonly DateFormatterInterface $dateFormatter,
    private readonly Connection $database,
  ) {
  }

  /**
   * Returns themed search results.
   */
  public function execute(array $filters, int $limit = 8): array {
    $local_results = $this->queryLocalNodes($filters, $limit);
    $external_results = $this->queryExternalIndex($filters, $limit);

    return $this->mergeResults($local_results, $external_results, $limit);
  }

  /**
   * Queries local Drupal nodes.
   */
  private function queryLocalNodes(array $filters, int $limit): array {
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
      // Normalize language names to Drupal langcodes.
      $lang_map = ['english' => 'en', 'indonesian' => 'id', 'bahasa' => 'id'];
      $lang = mb_strtolower($filters['language']);
      $langcode = $lang_map[$lang] ?? $filters['language'];
      // Only filter if it's a valid 2-letter code, not a full word.
      if (mb_strlen($langcode) <= 3) {
        $query->condition('langcode', $langcode);
      }
    }

    $tokens = array_values(array_filter($filters['keyword_tokens'] ?? []));

    if (!empty($tokens)) {
      // Search each keyword token individually with OR logic.
      $keyword_group = $query->orConditionGroup();
      foreach ($tokens as $token) {
        $keyword_group
          ->condition('title', '%' . $token . '%', 'LIKE')
          ->condition('body.value', '%' . $token . '%', 'LIKE');
      }
      $query->condition($keyword_group);
    }
    elseif (!empty($filters['keywords'])) {
      // No tokens extracted -- use full keywords as fallback.
      $keyword_group = $query->orConditionGroup()
        ->condition('title', '%' . $filters['keywords'] . '%', 'LIKE')
        ->condition('body.value', '%' . $filters['keywords'] . '%', 'LIKE');
      $query->condition($keyword_group);
    }

    $nids = $query->execute();
    if (!$nids) {
      return [];
    }

    $nodes = $storage->loadMultiple($nids);
    $items = [];

    foreach ($nodes as $node) {
      $translated = $this->entityRepository->getTranslationFromContext($node);
      $items[] = [
        'title' => $translated->label(),
        'url' => $translated->toUrl()->toString(),
        'type' => $translated->bundle(),
        'langcode' => $translated->language()->getId(),
        'date' => $this->dateFormatter->format($translated->getCreatedTime(), 'custom', 'd M Y'),
        'summary' => $translated->hasField('body') ? trim(strip_tags((string) $translated->get('body')->summary ?: (string) $translated->get('body')->value)) : '',
        'source' => 'local',
        'source_label' => 'Primary Site',
        'is_external' => FALSE,
      ];
    }

    return $items;
  }

  /**
   * Queries the external cross-site content index.
   */
  private function queryExternalIndex(array $filters, int $limit): array {
    if (!$this->database->schema()->tableExists('external_content_index')) {
      return [];
    }

    $query = $this->database->select('external_content_index', 'eci')
      ->fields('eci')
      ->orderBy('created', 'DESC');

    $tokens = array_values(array_filter($filters['keyword_tokens'] ?? []));
    if (!empty($tokens)) {
      $or = $query->orConditionGroup();
      foreach ($tokens as $token) {
        $or->condition('title', '%' . $token . '%', 'LIKE');
        $or->condition('description', '%' . $token . '%', 'LIKE');
        $or->condition('tags', '%' . $token . '%', 'LIKE');
      }
      $query->condition($or);
    }
    elseif (!empty($filters['keywords'])) {
      $or = $query->orConditionGroup()
        ->condition('title', '%' . $filters['keywords'] . '%', 'LIKE')
        ->condition('description', '%' . $filters['keywords'] . '%', 'LIKE')
        ->condition('tags', '%' . $filters['keywords'] . '%', 'LIKE');
      $query->condition($or);
    }

    if (!empty($filters['content_type'])) {
      $query->condition('content_type', $filters['content_type']);
    }

    $rows = $query->execute()->fetchAll();
    $items = [];
    $tokens = array_values(array_filter($filters['keyword_tokens'] ?? []));
    foreach ($rows as $row) {
      $items[] = [
        'title' => $row->title,
        'url' => $row->url,
        'summary' => $row->description,
        'type' => $row->content_type,
        'source' => $row->source,
        'source_label' => $row->source_label,
        'is_external' => TRUE,
        'date' => date('d M Y', (int) $row->created),
        '_score' => $this->scoreExternalRow($row, $tokens),
        '_created' => (int) $row->created,
      ];
    }

    usort($items, static function (array $left, array $right): int {
      if ($left['_score'] === $right['_score']) {
        return $right['_created'] <=> $left['_created'];
      }
      return $right['_score'] <=> $left['_score'];
    });

    return array_slice($items, 0, $limit);
  }

  /**
   * Interleaves local and external results to keep both sources visible.
   */
  private function mergeResults(array $local_results, array $external_results, int $limit): array {
    $merged = [];
    $local_index = 0;
    $external_index = 0;

    while (count($merged) < $limit && ($local_index < count($local_results) || $external_index < count($external_results))) {
      if ($local_index < count($local_results)) {
        $merged[] = $local_results[$local_index];
        $local_index++;
      }
      if (count($merged) >= $limit) {
        break;
      }
      if ($external_index < count($external_results)) {
        $merged[] = $external_results[$external_index];
        $external_index++;
      }
    }

    $merged = array_slice($merged, 0, $limit);

    foreach ($merged as &$item) {
      unset($item['_score'], $item['_created']);
    }

    return $merged;
  }

  /**
   * Calculates a simple relevance score for an external row.
   */
  private function scoreExternalRow(object $row, array $tokens): int {
    if (empty($tokens)) {
      return 0;
    }

    $title = mb_strtolower((string) $row->title);
    $description = mb_strtolower((string) $row->description);
    $tags = mb_strtolower((string) $row->tags);
    $score = 0;

    foreach ($tokens as $token) {
      $token = mb_strtolower($token);
      if ($token === '') {
        continue;
      }
      if (str_contains($title, $token)) {
        $score += 5;
      }
      if (str_contains($tags, $token)) {
        $score += 4;
      }
      if (str_contains($description, $token)) {
        $score += 2;
      }
    }

    return $score;
  }

}
