<?php

declare(strict_types=1);

namespace Drupal\drupal_personalized_widget\Service;

use Drupal\Core\Database\Connection;
use Drupal\drupal_ai_search\Service\OpenCodeApiService;

/**
 * Handles optional AI embeddings for future recommendation tuning.
 */
final class EmbeddingService {

  /**
   * Constructs the service.
   */
  public function __construct(
    private readonly Connection $database,
    private readonly OpenCodeApiService $openCodeApiService,
  ) {
  }

  /**
   * Whether semantic enrichment can be used.
   */
  public function isEnabled(): bool {
    return $this->openCodeApiService->isConfigured();
  }

  /**
   * Returns a placeholder list of similar articles.
   */
  public function getSimilarArticles(array $viewedNodeIds, int $limit = 6): array {
    if (empty($viewedNodeIds)) {
      return [];
    }

    $query = $this->database->select('article_embeddings', 'ae')
      ->fields('ae', ['node_id'])
      ->condition('node_id', $viewedNodeIds, 'NOT IN')
      ->range(0, $limit);

    return $query->execute()->fetchCol();
  }

}
