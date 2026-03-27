<?php

declare(strict_types=1);

namespace Drupal\drupal_ai_search\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;

/**
 * Small service wrapper for OpenCode API requests.
 */
final class OpenCodeApiService {

  /**
   * Constructs the service.
   */
  public function __construct(
    private readonly ClientInterface $httpClient,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly CacheBackendInterface $cache,
    private readonly LoggerInterface $logger,
  ) {
  }

  /**
   * Returns whether the integration is configured.
   */
  public function isConfigured(): bool {
    return (bool) $this->getConfig()->get('opencode_api_key');
  }

  /**
   * Sends a chat completion request.
   */
  public function chat(array $messages, ?string $model = NULL): array {
    $config = $this->getConfig();
    $api_key = (string) $config->get('opencode_api_key');
    $endpoint = rtrim((string) $config->get('opencode_endpoint'), '/');
    $cache_ttl = (int) ($config->get('cache_ttl') ?: 3600);
    $cache_key = 'drupal_ai_search:chat:' . hash('sha256', serialize([$messages, $model, $endpoint]));
    $provider_name = mb_strtolower((string) ($config->get('provider_name') ?: 'opencode'));
    $headers = [
      'Authorization' => 'Bearer ' . $api_key,
      'Content-Type' => 'application/json',
    ];

    if (str_contains($provider_name, 'openrouter') || str_contains($endpoint, 'openrouter.ai')) {
      $headers['HTTP-Referer'] = (string) ($config->get('provider_site_url') ?: 'https://primary.ddev.site');
      $headers['X-OpenRouter-Title'] = (string) ($config->get('provider_app_name') ?: 'Drupal AI Multisite Demo');
    }

    if ($api_key === '') {
      return [
        'ok' => FALSE,
        'content' => '',
        'error' => 'OpenCode API key is not configured.',
      ];
    }

    if ($cached = $this->cache->get($cache_key)) {
      return $cached->data;
    }

    try {
      $response = $this->httpClient->request('POST', $endpoint . '/chat/completions', [
        'headers' => $headers,
        'json' => [
          'model' => $model ?: $config->get('opencode_model'),
          'messages' => $messages,
          'temperature' => 0.2,
        ],
        'timeout' => (int) ($config->get('api_timeout') ?: 20),
      ]);

      $data = json_decode((string) $response->getBody(), TRUE) ?: [];
      $content = $data['choices'][0]['message']['content'] ?? '';

      $result = [
        'ok' => TRUE,
        'content' => $content,
        'raw' => $data,
      ];

      $this->cache->set($cache_key, $result, time() + $cache_ttl);
      return $result;
    }
    catch (\Throwable $exception) {
      $fallback_model = (string) $config->get('opencode_fallback_model');
      if ($fallback_model !== '' && $fallback_model !== $model) {
        try {
          return $this->chat($messages, $fallback_model);
        }
        catch (\Throwable) {
        }
      }

      $this->logger->error('OpenCode request failed: @message', ['@message' => $exception->getMessage()]);

      return [
        'ok' => FALSE,
        'content' => '',
        'error' => $exception->getMessage(),
      ];
    }
  }

  /**
   * Pings the service.
   */
  public function testConnection(): array {
    return $this->chat([
      ['role' => 'system', 'content' => 'Reply with the single word OK.'],
      ['role' => 'user', 'content' => 'Connection check'],
    ]);
  }

  /**
   * Sends an embeddings request when supported.
   */
  public function createEmbedding(string $text): ?array {
    $config = $this->getConfig();
    if (!$config->get('enable_embeddings')) {
      return NULL;
    }

    $api_key = (string) $config->get('opencode_api_key');
    $endpoint = rtrim((string) $config->get('opencode_endpoint'), '/');
    $model = (string) ($config->get('opencode_embedding_model') ?: 'text-embedding-3-small');
    $provider_name = mb_strtolower((string) ($config->get('provider_name') ?: 'opencode'));
    $headers = [
      'Authorization' => 'Bearer ' . $api_key,
      'Content-Type' => 'application/json',
    ];

    if (str_contains($provider_name, 'openrouter') || str_contains($endpoint, 'openrouter.ai')) {
      $headers['HTTP-Referer'] = (string) ($config->get('provider_site_url') ?: 'https://primary.ddev.site');
      $headers['X-OpenRouter-Title'] = (string) ($config->get('provider_app_name') ?: 'Drupal AI Multisite Demo');
    }

    try {
      $response = $this->httpClient->request('POST', $endpoint . '/embeddings', [
        'headers' => $headers,
        'json' => [
          'model' => $model,
          'input' => $text,
        ],
        'timeout' => (int) ($config->get('api_timeout') ?: 20),
      ]);

      $data = json_decode((string) $response->getBody(), TRUE) ?: [];
      return $data['data'][0]['embedding'] ?? NULL;
    }
    catch (\Throwable $exception) {
      $this->logger->warning('Embedding request failed: @message', ['@message' => $exception->getMessage()]);
      return NULL;
    }
  }

  /**
   * Gets module configuration.
   */
  private function getConfig() {
    return $this->configFactory->get('drupal_ai_search.settings');
  }

}
