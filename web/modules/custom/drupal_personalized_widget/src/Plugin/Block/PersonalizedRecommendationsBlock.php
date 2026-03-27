<?php

declare(strict_types=1);

namespace Drupal\drupal_personalized_widget\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\drupal_personalized_widget\Service\RecommendationEngine;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a personalized recommendations block.
 *
 * @Block(
 *   id = "personalized_recommendations_block",
 *   admin_label = @Translation("Personalized recommendations")
 * )
 */
final class PersonalizedRecommendationsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs the block.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly RecommendationEngine $recommendationEngine,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('drupal_personalized_widget.recommendation_engine'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $nids = $this->recommendationEngine->getRecommendations(6);

    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);
    $items = [];

    foreach ($nodes as $node) {
      $items[] = [
        'title' => $node->label(),
        'url' => $node->toUrl()->toString(),
        'summary' => $node->hasField('body') ? trim(strip_tags((string) $node->get('body')->summary ?: (string) $node->get('body')->value)) : '',
        'date' => $node->getCreatedTime(),
      ];
    }

    return [
      '#theme' => 'personalized_recommendations_block',
      '#items' => $items,
      '#cache' => [
        'contexts' => ['user', 'session', 'url.path'],
        'max-age' => 0,
      ],
    ];
  }

}
