<?php

declare(strict_types=1);

namespace Drupal\drupal_ai_search\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\drupal_ai_search\Service\OpenCodeApiService;
use Drupal\drupal_ai_search\Service\QueryParserService;
use Drupal\drupal_ai_search\Service\SearchExecutorService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller for the AI search landing page.
 */
final class AiSearchController extends ControllerBase {
  /**
   * Constructs the controller.
   */
  public function __construct(
    private readonly RequestStack $requestStack,
    private readonly QueryParserService $queryParser,
    private readonly SearchExecutorService $searchExecutor,
    private readonly OpenCodeApiService $openCodeApiService,
    private readonly ConfigFactoryInterface $moduleConfigFactory,
  ) {
  }

  /**
   * Creates the controller.
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('request_stack'),
      $container->get('drupal_ai_search.query_parser'),
      $container->get('drupal_ai_search.search_executor'),
      $container->get('drupal_ai_search.opencode_api'),
      $container->get('config.factory'),
    );
  }

  /**
   * Displays search results.
   */
  public function results(): array {
    $request = $this->requestStack->getCurrentRequest();
    $query = (string) $request->query->get('q', '');
    $search_mode = (string) $request->query->get('mode', 'ai');
    $config = $this->moduleConfigFactory->get('drupal_ai_search.settings');

    if ($search_mode === 'keyword') {
      // Force keyword-only: skip AI, use rule-based parsing.
      $parsed = $this->queryParser->parseKeywordOnly($query);
    }
    else {
      $parsed = $this->queryParser->parse($query);
    }

    $parsed = $this->applyLocalDebugOverrides($parsed);
    $results = $this->searchExecutor->execute($parsed, (int) ($config->get('result_limit') ?: 8));

    return [
      '#theme' => 'ai_search_results',
      '#query' => $query,
      '#summary' => $parsed['summary'] ?? '',
      '#filters' => $parsed,
      '#results' => $results,
      '#ai_status' => $parsed['ai_status'] ?? 'unknown',
      '#ai_error' => $parsed['ai_error'] ?? '',
      '#search_mode' => $search_mode,
      '#attached' => [
        'library' => ['drupal_ai_search/search_ui'],
      ],
      '#cache' => [
        'contexts' => ['url.query_args:q', 'url.query_args:mode'],
        'max-age' => 0,
      ],
    ];
  }

  /**
   * Returns search results as JSON for the floating chat widget.
   */
  public function apiResults(): JsonResponse {
    $request = $this->requestStack->getCurrentRequest();
    $query = (string) $request->query->get('q', '');
    $config = $this->moduleConfigFactory->get('drupal_ai_search.settings');
    $parsed = $this->queryParser->parse($query);
    $parsed = $this->applyLocalDebugOverrides($parsed);
    $result_limit = (int) ($config->get('result_limit') ?: 8);
    $results = $this->searchExecutor->execute($parsed, $result_limit);
    $conversation = $this->generateChatResponse($query, $results);

    return new JsonResponse([
      'query' => $query,
      'summary' => $parsed['summary'] ?? '',
      'ai_status' => $parsed['ai_status'] ?? 'unknown',
      'conversation' => $conversation,
      'results' => $results,
    ]);
  }

  /**
   * Builds a short conversational response from search results.
   */
  private function generateChatResponse(string $query, array $results): string {
    if (empty($results)) {
      return "I couldn't find any content matching your query. Try different keywords or browse our articles directly.";
    }

    if (!$this->openCodeApiService->isConfigured()) {
      return 'I found ' . count($results) . ' relevant results related to your question.';
    }

    $titles = [];
    foreach (array_slice($results, 0, 5) as $result) {
      $source = !empty($result['source_label']) ? ' (' . $result['source_label'] . ')' : '';
      $titles[] = '- ' . $result['title'] . $source;
    }

    $context = implode("\n", $titles);
    $response = $this->openCodeApiService->chat([
      [
        'role' => 'system',
        'content' => 'You are a helpful content assistant for a business platform. Based on the search results provided, give a brief friendly conversational response in at most 2 short sentences. Do not make up information. Do not use markdown formatting.',
      ],
      [
        'role' => 'user',
        'content' => "User asked: \"$query\"\n\nContent found:\n$context\n\nRespond conversationally about what content is available.",
      ],
    ]);

    if (!empty($response['ok']) && !empty($response['content'])) {
      return trim((string) $response['content']);
    }

    return 'Here are ' . count($results) . ' results related to your question.';
  }

  /**
   * Allows safe local-only UI testing for AI failure states.
   */
  private function applyLocalDebugOverrides(array $parsed): array {
    $request = $this->requestStack->getCurrentRequest();
    $debug_mode = (string) $request->query->get('ai_debug', '');
    $host = (string) $request->getHost();
    $is_local_host = $host === 'localhost' || $host === '127.0.0.1' || str_ends_with($host, '.ddev.site');

    if (!$is_local_host || $debug_mode === '') {
      return $parsed;
    }

    if ($debug_mode === 'error') {
      $parsed['ai_status'] = 'error';
      $parsed['ai_error'] = 'Simulated provider failure for local UI verification.';
      $parsed['summary'] = $parsed['summary'] ?? 'Showing best-match content for your natural language query.';
    }

    if ($debug_mode === 'fallback') {
      $parsed['ai_status'] = 'fallback';
      unset($parsed['ai_error']);
    }

    return $parsed;
  }

}
