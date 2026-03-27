<?php

declare(strict_types=1);

namespace Drupal\drupal_basic_search\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\drupal_basic_search\Service\QueryParserService;
use Drupal\drupal_basic_search\Service\SearchExecutorService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller for the keyword-only search landing page.
 */
final class BasicSearchController extends ControllerBase {

  /**
   * Constructs the controller.
   */
  public function __construct(
    private readonly RequestStack $requestStack,
    private readonly QueryParserService $queryParser,
    private readonly SearchExecutorService $searchExecutor,
  ) {
  }

  /**
   * Creates the controller.
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('request_stack'),
      $container->get('drupal_basic_search.query_parser'),
      $container->get('drupal_basic_search.search_executor'),
    );
  }

  /**
   * Displays search results.
   */
  public function results(): array {
    $query = (string) $this->requestStack->getCurrentRequest()->query->get('q', '');
    $parsed = $this->queryParser->parse($query);
    $results = $this->searchExecutor->execute($parsed, 8);

    return [
      '#theme' => 'basic_search_results',
      '#query' => $query,
      '#summary' => $parsed['summary'] ?? '',
      '#filters' => $parsed,
      '#results' => $results,
      '#attached' => [
        'library' => ['drupal_basic_search/search_ui'],
      ],
      '#cache' => [
        'contexts' => ['url.query_args:q'],
        'max-age' => 0,
      ],
    ];
  }

}
