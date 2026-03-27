<?php

declare(strict_types=1);

namespace Drupal\drupal_basic_recommendations\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Shows latest articles without AI or personalization.
 *
 * @Block(
 *   id = "basic_recommendations_block",
 *   admin_label = @Translation("Basic recommendations (no AI)")
 * )
 */
final class BasicRecommendationsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
    );
  }

  public function build(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $nids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('status', 1)
      ->condition('type', 'article')
      ->sort('created', 'DESC')
      ->range(0, 6)
      ->execute();

    if (empty($nids)) {
      return [
        '#markup' => '<div class="basic-recs"><h3>' . $this->t('Latest Articles') . '</h3><p>' . $this->t('No articles available yet.') . '</p></div>',
      ];
    }

    $nodes = $storage->loadMultiple($nids);
    $items = [];

    foreach ($nodes as $node) {
      $items[] = [
        'title' => $node->label(),
        'url' => $node->toUrl()->toString(),
      ];
    }

    return [
      '#theme' => 'basic_recommendations_block',
      '#items' => $items,
      '#cache' => [
        'max-age' => 300,
      ],
    ];
  }

}
