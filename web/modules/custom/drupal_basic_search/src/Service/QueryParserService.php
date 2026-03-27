<?php

declare(strict_types=1);

namespace Drupal\drupal_basic_search\Service;

/**
 * Converts a free-form query into keyword-only filters.
 */
final class QueryParserService {

  /**
   * Parses the incoming query using rule-based keyword extraction only.
   */
  public function parse(string $query): array {
    $query = trim($query);
    $filters = [
      'keywords' => $query,
      'keyword_tokens' => [],
      'content_type' => NULL,
      'language' => NULL,
      'sort' => 'created',
      'search_mode' => 'keyword',
    ];

    if ($query === '') {
      return $filters + ['summary' => 'Type a keyword or short phrase to search your content.'];
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

    if (str_contains($lower_query, 'title') || str_contains($lower_query, 'alphabetical')) {
      $filters['sort'] = 'title';
    }

    $filters['keyword_tokens'] = $this->extractKeywordTokens($query);
    if (!empty($filters['content_type']) && !empty($filters['keyword_tokens'])) {
      $filters['keyword_tokens'] = array_values(array_filter(
        $filters['keyword_tokens'],
        fn (string $token): bool => !in_array($token, ['article', 'articles', 'event', 'events', 'press', 'release', 'releases', 'speech', 'speeches', 'commentary', 'training', 'workshop', 'webinar'], TRUE)
      ));
    }

    $filters['summary'] = 'Keyword mode is active for this site. Results are matched directly from titles and body text.';
    return $filters;
  }

  /**
   * Extracts useful tokens from the raw query string.
   */
  private function extractKeywordTokens(string $query): array {
    $normalized = mb_strtolower($query);
    $normalized = preg_replace('/[^a-z0-9\\s]+/i', ' ', $normalized) ?? '';
    $tokens = preg_split('/\\s+/', trim($normalized)) ?: [];
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

    return array_slice(array_values(array_unique($tokens)), 0, 6);
  }

}
