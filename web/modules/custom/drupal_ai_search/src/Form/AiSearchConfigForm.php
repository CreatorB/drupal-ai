<?php

declare(strict_types=1);

namespace Drupal\drupal_ai_search\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Settings form for the AI search module.
 */
final class AiSearchConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'drupal_ai_search_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['drupal_ai_search.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('drupal_ai_search.settings');

    $form['opencode_endpoint'] = [
      '#type' => 'url',
      '#title' => $this->t('OpenCode endpoint'),
      '#default_value' => $config->get('opencode_endpoint'),
      '#required' => TRUE,
    ];

    $form['opencode_model'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Primary model'),
      '#default_value' => $config->get('opencode_model'),
      '#required' => TRUE,
    ];

    $form['opencode_fallback_model'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Fallback model'),
      '#default_value' => $config->get('opencode_fallback_model'),
    ];

    $form['provider_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Provider name'),
      '#default_value' => $config->get('provider_name'),
      '#description' => $this->t('Examples: OpenCode, OpenRouter, Groq.'),
    ];

    $form['provider_site_url'] = [
      '#type' => 'url',
      '#title' => $this->t('Provider site URL'),
      '#default_value' => $config->get('provider_site_url'),
      '#description' => $this->t('Used for providers like OpenRouter that accept app attribution headers.'),
    ];

    $form['provider_app_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Provider app name'),
      '#default_value' => $config->get('provider_app_name'),
    ];

    $form['opencode_embedding_model'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Embedding model'),
      '#default_value' => $config->get('opencode_embedding_model'),
      '#description' => $this->t('Used later for semantic recommendation improvements.'),
    ];

    $form['enable_embeddings'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable embeddings'),
      '#default_value' => $config->get('enable_embeddings'),
    ];

    $form['result_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Result limit'),
      '#default_value' => $config->get('result_limit'),
      '#min' => 1,
      '#max' => 24,
    ];

    $form['cache_ttl'] = [
      '#type' => 'number',
      '#title' => $this->t('Cache TTL'),
      '#default_value' => $config->get('cache_ttl'),
      '#min' => 60,
      '#description' => $this->t('Number of seconds to cache parsed AI queries.'),
    ];

    $form['api_timeout'] = [
      '#type' => 'number',
      '#title' => $this->t('API timeout'),
      '#default_value' => $config->get('api_timeout'),
      '#min' => 5,
      '#max' => 120,
    ];

    $form['search_prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Search prompt'),
      '#default_value' => $config->get('search_prompt'),
      '#description' => $this->t('The API key is injected from environment variables, so it is not stored in config exports.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->configFactory()->getEditable('drupal_ai_search.settings')
      ->set('opencode_endpoint', $form_state->getValue('opencode_endpoint'))
      ->set('opencode_model', $form_state->getValue('opencode_model'))
      ->set('opencode_fallback_model', $form_state->getValue('opencode_fallback_model'))
      ->set('provider_name', $form_state->getValue('provider_name'))
      ->set('provider_site_url', $form_state->getValue('provider_site_url'))
      ->set('provider_app_name', $form_state->getValue('provider_app_name'))
      ->set('opencode_embedding_model', $form_state->getValue('opencode_embedding_model'))
      ->set('enable_embeddings', (bool) $form_state->getValue('enable_embeddings'))
      ->set('result_limit', (int) $form_state->getValue('result_limit'))
      ->set('cache_ttl', (int) $form_state->getValue('cache_ttl'))
      ->set('api_timeout', (int) $form_state->getValue('api_timeout'))
      ->set('search_prompt', $form_state->getValue('search_prompt'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
