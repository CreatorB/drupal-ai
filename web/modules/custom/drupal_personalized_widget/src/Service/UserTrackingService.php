<?php

declare(strict_types=1);

namespace Drupal\drupal_personalized_widget\Service;

use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Persists lightweight reading activity for the demo.
 */
final class UserTrackingService {

  /**
   * Constructs the service.
   */
  public function __construct(
    private readonly Connection $database,
    private readonly AccountProxyInterface $currentUser,
    private readonly RequestStack $requestStack,
  ) {
  }

  /**
   * Records a page view event.
   */
  public function track(int $nodeId, int $timeSpent = 0, int $scrollDepth = 0): void {
    if ($nodeId <= 0) {
      return;
    }

    $request = $this->requestStack->getCurrentRequest();
    $session = $request?->getSession();
    $session_id = $session ? $session->getId() : 'anonymous';

    $this->database->insert('user_reading_history')
      ->fields([
        'user_id' => (int) $this->currentUser->id(),
        'session_id' => $session_id,
        'node_id' => $nodeId,
        'node_type' => 'article',
        'categories' => json_encode([]),
        'tags' => json_encode([]),
        'time_spent' => $timeSpent,
        'scroll_depth' => $scrollDepth,
        'viewed_at' => time(),
      ])
      ->execute();
  }

}
