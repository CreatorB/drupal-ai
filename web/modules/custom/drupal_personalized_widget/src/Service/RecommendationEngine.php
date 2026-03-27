<?php

declare(strict_types=1);

namespace Drupal\drupal_personalized_widget\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Builds article recommendations from recent reading history.
 */
final class RecommendationEngine {

  /**
   * Constructs the engine.
   */
  public function __construct(
    private readonly Connection $database,
    private readonly AccountProxyInterface $currentUser,
    private readonly RequestStack $requestStack,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly EmbeddingService $embeddingService,
  ) {
  }

  /**
   * Returns node ids for recommended content.
   */
  public function getRecommendations(int $limit = 6): array {
    $history = $this->getHistory();
    $excluded = array_map(static fn ($row) => (int) $row->node_id, $history);

    if ($this->embeddingService->isEnabled()) {
      $semantic = $this->embeddingService->getSimilarArticles($excluded, $limit);
      if (!empty($semantic)) {
        return array_slice(array_map('intval', $semantic), 0, $limit);
      }
    }

    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('status', 1)
      ->condition('type', 'article')
      ->sort('created', 'DESC')
      ->range(0, $limit);

    if (!empty($excluded)) {
      $query->condition('nid', $excluded, 'NOT IN');
    }

    $result = $query->execute();
    return $result ? array_values(array_map('intval', $result)) : [];
  }

  /**
   * Gets recent history for current user or session.
   */
  private function getHistory(): array {
    $request = $this->requestStack->getCurrentRequest();
    $session = $request?->getSession();
    $session_id = $session ? $session->getId() : 'anonymous';

    $query = $this->database->select('user_reading_history', 'urh')
      ->fields('urh')
      ->range(0, 20)
      ->orderBy('viewed_at', 'DESC');

    if ($this->currentUser->isAuthenticated()) {
      $query->condition('user_id', (int) $this->currentUser->id());
    }
    else {
      $query->condition('session_id', $session_id);
    }

    return $query->execute()->fetchAll();
  }

}
