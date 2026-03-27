<?php

declare(strict_types=1);

namespace Drupal\drupal_personalized_widget\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\drupal_personalized_widget\Service\UserTrackingService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for tracking beacon requests.
 */
final class TrackingController extends ControllerBase {

  /**
   * Constructs the controller.
   */
  public function __construct(private readonly UserTrackingService $trackingService) {
  }

  /**
   * Creates the controller.
   */
  public static function create(ContainerInterface $container): self {
    return new self($container->get('drupal_personalized_widget.user_tracking'));
  }

  /**
   * Persists a lightweight tracking hit.
   */
  public function track(Request $request): JsonResponse {
    $node_id = (int) ($request->request->get('node_id') ?? 0);
    $time_spent = (int) ($request->request->get('time_spent') ?? 0);
    $scroll_depth = (int) ($request->request->get('scroll_depth') ?? 0);

    $this->trackingService->track($node_id, $time_spent, $scroll_depth);

    return new JsonResponse(['status' => 'ok']);
  }

}
