<?php

declare(strict_types=1);

namespace Drupal\drupal_ai_search\Service;

/**
 * Converts natural language search into simple Drupal filters.
 */
final class QueryParserService {

  /**
   * Constructs the parser.
   */
  public function __construct(private readonly OpenCodeApiService $openCodeApiService) {
  }

  /**
   * Parses a query using AI when available, with a rule-based fallback.
   */
  public function parse(string $query): array {
    $query = trim($query);
    $filters = [
      'keywords' => $query,
      'keyword_tokens' => [],
      'content_type' => NULL,
      'language' => NULL,
      'sort' => 'created',
    ];

    if ($query === '') {
      return $filters + ['summary' => 'Type a question to search your content.'];
    }

    if ($this->openCodeApiService->isConfigured()) {
      $result = $this->openCodeApiService->chat([
        ['role' => 'system', 'content' => 'Return compact JSON with keys summary, keywords, content_type, language, sort. Allowed content_type values: article, event, press_release, speech_commentary, null.'],
        ['role' => 'user', 'content' => $query],
      ]);

      if (!empty($result['ok']) && !empty($result['content'])) {
        $raw = (string) $result['content'];
        // Strip markdown code fences (```json ... ```) that some LLMs wrap around JSON.
        if (preg_match('/```(?:json)?\s*([\s\S]*?)```/', $raw, $matches)) {
          $raw = trim($matches[1]);
        }
        // Also handle case where response has leading/trailing whitespace or text before {.
        if (($pos = strpos($raw, '{')) !== FALSE) {
          $raw = substr($raw, $pos);
        }
        $decoded = json_decode($raw, TRUE);
        if (is_array($decoded)) {
          // Normalize keywords to string if AI returned array.
          if (isset($decoded['keywords']) && is_array($decoded['keywords'])) {
            $decoded['keywords'] = implode(' ', $decoded['keywords']);
          }
          // Avoid over-filtering when the user did not explicitly ask for a
          // specific content type. This keeps branded/source queries broad.
          if (!$this->hasExplicitTypeIntent($query)) {
            $decoded['content_type'] = NULL;
          }
          // Merge AI keywords with original query tokens so we match both synonyms and exact terms.
          $ai_tokens = $this->extractKeywordTokens((string) ($decoded['keywords'] ?? $query));
          $query_tokens = $this->extractKeywordTokens($query);
          $decoded['keyword_tokens'] = array_values(array_unique(array_merge($query_tokens, $ai_tokens)));
          $decoded['ai_status'] = 'ok';
          return $decoded + $filters;
        }
      }

      if (isset($result['ok']) && $result['ok'] === FALSE) {
        $filters['ai_status'] = 'error';
        $filters['ai_error'] = $result['error'] ?? 'Unknown error';
      }
    }

    $lower_query = mb_strtolower($query);
    if (str_contains($lower_query, 'event') || str_contains($lower_query, 'workshop') || str_contains($lower_query, 'training') || str_contains($lower_query, 'webinar') || str_contains($lower_query, 'conference')) {
      $filters['content_type'] = 'event';
    }
    elseif (str_contains($lower_query, 'press')) {
      $filters['content_type'] = 'press_release';
    }
    elseif (str_contains($lower_query, 'speech') || str_contains($lower_query, 'commentary') || str_contains($lower_query, 'remarks') || str_contains($lower_query, 'keynote')) {
      $filters['content_type'] = 'speech_commentary';
    }
    elseif (str_contains($lower_query, 'news') || str_contains($lower_query, 'article')) {
      $filters['content_type'] = 'article';
    }

    if (str_contains($lower_query, 'indonesia') || str_contains($lower_query, 'bahasa')) {
      $filters['language'] = 'id';
    }

    if (str_contains($lower_query, 'latest') || str_contains($lower_query, 'recent') || str_contains($lower_query, 'new')) {
      $filters['sort'] = 'created';
    }

    if (!isset($filters['ai_status'])) {
      $filters['ai_status'] = 'fallback';
    }

    $filters['keyword_tokens'] = $this->extractKeywordTokens($query);
    if (!empty($filters['content_type']) && !empty($filters['keyword_tokens'])) {
      $filters['keyword_tokens'] = array_values(array_filter(
        $filters['keyword_tokens'],
        fn (string $token): bool => !in_array($token, ['article', 'articles', 'event', 'events', 'press', 'release', 'releases', 'speech', 'speeches', 'commentary', 'training', 'workshop', 'webinar'], TRUE)
      ));
    }

    $filters['summary'] = 'Showing best-match content for your natural language query.';
    return $filters;
  }

  /**
   * Parses using keyword-only mode (no AI). Used for comparison demos.
   */
  public function parseKeywordOnly(string $query): array {
    $query = trim($query);
    $filters = [
      'keywords' => $query,
      'keyword_tokens' => [],
      'content_type' => NULL,
      'language' => NULL,
      'sort' => 'created',
      'ai_status' => 'keyword',
      'summary' => 'Keyword-only mode: searching by exact text match without AI interpretation.',
    ];

    if ($query === '') {
      return $filters;
    }

    $filters['keyword_tokens'] = $this->extractKeywordTokens($query);
    return $filters;
  }

  /**
   * Extracts useful keyword tokens from a natural language prompt.
   */
  private function extractKeywordTokens(string $query): array {
    $normalized = mb_strtolower($query);
    $normalized = preg_replace('/[^a-z0-9\s]+/i', ' ', $normalized) ?? '';
    $tokens = preg_split('/\s+/', trim($normalized)) ?: [];
    $stopwords = [
      'a', 'about', 'an', 'and', 'any', 'are', 'for', 'from', 'in', 'into', 'latest',
      'me', 'new', 'of', 'on', 'or', 'recent', 'show', 'the', 'to', 'upcoming', 'what',
      'with', 'articles', 'article', 'events', 'event', 'speeches', 'speech', 'press',
      'release', 'releases', 'commentary', 'training', 'workshop', 'workshops', 'webinar',
      'webinars', 'next', 'month',
    ];

    $tokens = array_values(array_filter($tokens, static function (string $token) use ($stopwords): bool {
      return $token !== '' && mb_strlen($token) > 2 && !in_array($token, $stopwords, TRUE);
    }));

    return array_slice(array_values(array_unique($tokens)), 0, 5);
  }

  /**
   * Detects whether the user explicitly asked for a content type.
   */
  private function hasExplicitTypeIntent(string $query): bool {
    $lower_query = mb_strtolower($query);
    $type_markers = [
      'article',
      'articles',
      'event',
      'events',
      'press release',
      'press releases',
      'speech',
      'speeches',
      'commentary',
      'training',
      'webinar',
      'conference',
      'workshop',
    ];

    foreach ($type_markers as $marker) {
      if (str_contains($lower_query, $marker)) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
