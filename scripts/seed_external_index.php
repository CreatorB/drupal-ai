<?php

declare(strict_types=1);

/**
 * Seeds the external content index for primary-site cross-source search.
 */

$database = \Drupal::database();
$schema = $database->schema();

if (!$schema->tableExists('external_content_index')) {
  print "external_content_index table does not exist. Run drush updb first.\n";
  return;
}

$database->truncate('external_content_index')->execute();

$sbf_items = [
  ['title' => 'SBF Signs MOUs With 11 Partners To Strengthen Employment Facilitation', 'description' => 'Collaboration initiative between SBF and strategic partners to support workforce development and employment readiness programmes.', 'url' => 'https://www.sbf.org.sg/media-centre/press-releases/detail/sbf-signs-mous', 'source' => 'sbf', 'source_label' => 'SBF.org.sg', 'content_type' => 'press_release', 'tags' => 'employment partnership workforce mou collaboration', 'created' => strtotime('-5 days')],
  ['title' => 'SBF welcomes stronger regional digital trade cooperation', 'description' => 'An update on regional business cooperation with a focus on digital trade readiness, cross-border trust, and SME participation.', 'url' => 'https://www.sbf.org.sg/media-centre/news', 'source' => 'sbf', 'source_label' => 'SBF.org.sg', 'content_type' => 'article', 'tags' => 'digital trade regional cooperation smes cross-border', 'created' => strtotime('-8 days')],
  ['title' => 'Business sentiment improves on manufacturing outlook', 'description' => 'A business update covering sentiment trends, export outlook, and operational planning among regional manufacturers.', 'url' => 'https://www.sbf.org.sg/media-centre/news', 'source' => 'sbf', 'source_label' => 'SBF.org.sg', 'content_type' => 'article', 'tags' => 'manufacturing outlook business sentiment exports supply chain', 'created' => strtotime('-12 days')],
  ['title' => 'SBF statement on workforce transformation priorities', 'description' => 'A policy-focused statement on job redesign, upskilling, and digital capability priorities for the business community.', 'url' => 'https://www.sbf.org.sg/media-centre/speeches-and-commentaries', 'source' => 'sbf', 'source_label' => 'SBF.org.sg', 'content_type' => 'speech_commentary', 'tags' => 'workforce skills transformation upskilling digital talent', 'created' => strtotime('-15 days')],
  ['title' => 'SBF highlights SME readiness for sustainability reporting', 'description' => 'Guidance for SMEs preparing for sustainability disclosure expectations and reporting maturity improvements.', 'url' => 'https://www.sbf.org.sg/media-centre/news', 'source' => 'sbf', 'source_label' => 'SBF.org.sg', 'content_type' => 'article', 'tags' => 'sustainability reporting smes disclosure governance', 'created' => strtotime('-18 days')],
  ['title' => 'Remarks on trusted digitalisation for Singapore businesses', 'description' => 'Commentary on digital trust, cybersecurity, and responsible adoption of new technology across business sectors.', 'url' => 'https://www.sbf.org.sg/media-centre/speeches-and-commentaries', 'source' => 'sbf', 'source_label' => 'SBF.org.sg', 'content_type' => 'speech_commentary', 'tags' => 'digital trust cybersecurity responsible technology business', 'created' => strtotime('-22 days')],
  ['title' => 'SBF supports stronger ASEAN market connectivity', 'description' => 'A regional business note covering market access, partnerships, and long-term ASEAN competitiveness.', 'url' => 'https://www.sbf.org.sg/media-centre/news', 'source' => 'sbf', 'source_label' => 'SBF.org.sg', 'content_type' => 'article', 'tags' => 'asean connectivity market access regional growth trade', 'created' => strtotime('-25 days')],
  ['title' => 'Press briefing on business cost pressures and productivity', 'description' => 'A press briefing on the operating environment facing companies, including manpower, energy, and technology productivity priorities.', 'url' => 'https://www.sbf.org.sg/media-centre/press-releases', 'source' => 'sbf', 'source_label' => 'SBF.org.sg', 'content_type' => 'press_release', 'tags' => 'productivity cost pressure manpower business operations', 'created' => strtotime('-28 days')],
  ['title' => 'SBF commentary on AI adoption and business trust', 'description' => 'A practical reflection on how organisations can adopt AI tools while preserving trust, governance, and workforce clarity.', 'url' => 'https://www.sbf.org.sg/media-centre/speeches-and-commentaries', 'source' => 'sbf', 'source_label' => 'SBF.org.sg', 'content_type' => 'speech_commentary', 'tags' => 'ai business trust governance adoption workforce', 'created' => strtotime('-31 days')],
  ['title' => 'Regional business mission expands technology partnerships', 'description' => 'An update on partnership-building efforts involving technology exchange, innovation, and market entry collaboration.', 'url' => 'https://www.sbf.org.sg/media-centre/news', 'source' => 'sbf', 'source_label' => 'SBF.org.sg', 'content_type' => 'article', 'tags' => 'technology partnerships innovation business mission regional', 'created' => strtotime('-34 days')],
];

$site1_items = [
  ['title' => 'Building scalable APIs with Python and FastAPI', 'description' => 'Python teams that need speed without giving up readability are increasingly standardising on FastAPI for modern service delivery.', 'url' => 'https://site1.ddev.site/node/39', 'source' => 'site1', 'source_label' => 'FXMedia Tech', 'content_type' => 'article', 'tags' => 'python fastapi api backend programming', 'created' => strtotime('-2 days')],
  ['title' => 'Modern JavaScript architecture for enterprise front ends', 'description' => 'A practical look at modular front-end architecture, typed contracts, shared state discipline, and build reliability.', 'url' => 'https://site1.ddev.site/node/40', 'source' => 'site1', 'source_label' => 'FXMedia Tech', 'content_type' => 'article', 'tags' => 'javascript frontend architecture typescript ui', 'created' => strtotime('-3 days')],
  ['title' => 'DNS architecture choices that reduce production downtime', 'description' => 'An operations-oriented review of authoritative DNS design, failover patterns, TTL strategy, and outage prevention.', 'url' => 'https://site1.ddev.site/node/44', 'source' => 'site1', 'source_label' => 'FXMedia Tech', 'content_type' => 'article', 'tags' => 'dns networking reliability infrastructure operations', 'created' => strtotime('-6 days')],
  ['title' => 'Kubernetes rollout patterns that reduce deployment risk', 'description' => 'A delivery guide covering canaries, probes, health signals, traffic shifting, and practical rollout guardrails.', 'url' => 'https://site1.ddev.site/node/60', 'source' => 'site1', 'source_label' => 'FXMedia Tech', 'content_type' => 'article', 'tags' => 'kubernetes deployment containers cloud server', 'created' => strtotime('-1 days')],
  ['title' => 'Docker image hygiene for secure and predictable builds', 'description' => 'A focused piece on base images, multi-stage builds, package pinning, and image scanning for cleaner delivery pipelines.', 'url' => 'https://site1.ddev.site/node/61', 'source' => 'site1', 'source_label' => 'FXMedia Tech', 'content_type' => 'article', 'tags' => 'docker containers security builds cloud', 'created' => strtotime('-4 days')],
  ['title' => 'Comparing managed services across AWS GCP and Azure', 'description' => 'A practical comparison of cloud platforms through the lens of identity, logging, networking, compute, and database operations.', 'url' => 'https://site1.ddev.site/node/62', 'source' => 'site1', 'source_label' => 'FXMedia Tech', 'content_type' => 'article', 'tags' => 'aws gcp azure cloud platform architecture', 'created' => strtotime('-7 days')],
  ['title' => 'Zero trust adoption lessons from real engineering teams', 'description' => 'A grounded review of rollout sequencing, identity controls, and adoption friction in real zero-trust initiatives.', 'url' => 'https://site1.ddev.site/node/64', 'source' => 'site1', 'source_label' => 'FXMedia Tech', 'content_type' => 'article', 'tags' => 'zero trust security identity access', 'created' => strtotime('-9 days')],
  ['title' => 'PostgreSQL indexing strategies for high-write systems', 'description' => 'A guide to index strategy, query paths, and maintenance tradeoffs in high-write PostgreSQL environments.', 'url' => 'https://site1.ddev.site/node/69', 'source' => 'site1', 'source_label' => 'FXMedia Tech', 'content_type' => 'article', 'tags' => 'postgresql database indexing performance', 'created' => strtotime('-10 days')],
  ['title' => 'GitOps operating models for multi-environment delivery', 'description' => 'A practical framework for repository ownership, promotion flows, and drift handling in GitOps-driven delivery.', 'url' => 'https://site1.ddev.site/node/74', 'source' => 'site1', 'source_label' => 'FXMedia Tech', 'content_type' => 'article', 'tags' => 'gitops devops delivery environments platform', 'created' => strtotime('-11 days')],
  ['title' => 'MLOps workflows that keep models useful after launch', 'description' => 'A production-first view of model versioning, drift monitoring, deployment risk, and operational discipline for ML teams.', 'url' => 'https://site1.ddev.site/node/79', 'source' => 'site1', 'source_label' => 'FXMedia Tech', 'content_type' => 'article', 'tags' => 'mlops machine learning deployment monitoring ai', 'created' => strtotime('-13 days')],
];

$items = array_merge($sbf_items, $site1_items);

foreach ($items as $item) {
  $database->insert('external_content_index')
    ->fields($item)
    ->execute();
}

print sprintf("Seeded %d external index rows.\n", count($items));
