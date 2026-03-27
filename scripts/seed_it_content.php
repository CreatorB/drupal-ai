<?php

declare(strict_types=1);

use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Seeds a shared IT dataset for Site1 and Site2.
 *
 * The data is deterministic so both sites can be compared with the same
 * content corpus while using different search behavior.
 */

$entity_type_manager = \Drupal::entityTypeManager();
$node_storage = $entity_type_manager->getStorage('node');
$term_storage = $entity_type_manager->getStorage('taxonomy_term');
$vocabulary_storage = $entity_type_manager->getStorage('taxonomy_vocabulary');

if (!$vocabulary_storage->load('category')) {
  Vocabulary::create([
    'vid' => 'category',
    'name' => 'Article Category',
    'description' => 'Article category vocabulary',
    'hierarchy' => 1,
    'langcode' => 'en',
  ])->save();
}

if (!$vocabulary_storage->load('tags')) {
  Vocabulary::create([
    'vid' => 'tags',
    'name' => 'Tags',
    'description' => 'Keyword tags for IT content',
    'hierarchy' => 0,
    'langcode' => 'en',
  ])->save();
}

$ensureTerm = static function (string $vocabulary, string $name) use ($term_storage): int {
  $existing = $term_storage->loadByProperties([
    'vid' => $vocabulary,
    'name' => $name,
  ]);

  if ($existing) {
    return (int) reset($existing)->id();
  }

  $term = Term::create([
    'vid' => $vocabulary,
    'name' => $name,
    'langcode' => 'en',
  ]);
  $term->save();

  return (int) $term->id();
};

$buildTagReferences = static function (array $tags) use ($ensureTerm): array {
  $references = [];
  foreach ($tags as $tag) {
    $references[] = ['target_id' => $ensureTerm('tags', $tag)];
  }
  return $references;
};

$category_terms = [
  'Programming',
  'Networking',
  'System Administration',
  'Web Design',
  'Cloud/Server',
  'Security',
  'Database',
  'DevOps',
  'AI/ML',
  'Mobile',
];

$category_ids = [];
foreach ($category_terms as $category_name) {
  $category_ids[$category_name] = $ensureTerm('category', $category_name);
}

$event_type_ids = [];
foreach (['Conference', 'Workshop', 'Training', 'Webinar', 'Networking'] as $event_type_name) {
  $event_type_ids[$event_type_name] = $ensureTerm('event_type', $event_type_name);
}

$articles = [
  ['title' => 'Building scalable APIs with Python and FastAPI', 'body' => 'Python teams that need speed without giving up readability are increasingly standardising on FastAPI. This piece walks through async request handling, dependency injection, structured validation, and deployment patterns that keep internal services responsive under load.', 'cat' => 'Programming', 'tags' => ['python', 'fastapi', 'api', 'backend']],
  ['title' => 'Modern JavaScript architecture for enterprise front ends', 'body' => 'Enterprise JavaScript projects fail when module boundaries are fuzzy and shared state grows without discipline. We look at routing strategies, typed contracts, component isolation, and build pipelines that help large front-end teams ship safely.', 'cat' => 'Programming', 'tags' => ['javascript', 'frontend', 'architecture', 'typescript']],
  ['title' => 'Rust services for memory-safe systems programming', 'body' => 'Rust continues to earn trust for services where memory safety and predictable performance matter. This article covers ownership patterns, error handling, crate selection, and the kinds of workloads where Rust pays off quickly.', 'cat' => 'Programming', 'tags' => ['rust', 'systems', 'performance', 'backend']],
  ['title' => 'Practical concurrency patterns in Go for backend teams', 'body' => 'Go remains a strong fit for backend services that depend on clean concurrency primitives. We review goroutines, worker pools, context cancellation, and observability practices that keep concurrent applications maintainable.', 'cat' => 'Programming', 'tags' => ['go', 'golang', 'concurrency', 'microservices']],
  ['title' => 'PHP 8 application patterns that still scale elegantly', 'body' => 'PHP continues to power serious publishing and commerce stacks when the codebase is structured deliberately. We examine service boundaries, queues, caching, and testing conventions that make long-lived PHP systems easier to evolve.', 'cat' => 'Programming', 'tags' => ['php', 'architecture', 'testing', 'scalability']],

  ['title' => 'DNS architecture choices that reduce production downtime', 'body' => 'DNS is one of the least visible but most consequential layers in modern infrastructure. This guide covers authoritative DNS design, failover patterns, TTL strategy, and the operational mistakes that tend to turn small incidents into major outages.', 'cat' => 'Networking', 'tags' => ['dns', 'networking', 'infrastructure', 'reliability']],
  ['title' => 'TCP/IP troubleshooting habits every engineer should know', 'body' => 'When distributed systems slow down, the problem is often somewhere between the application and the wire. We revisit packet flow, retransmissions, MTU issues, and basic diagnostics that help engineers find network faults quickly.', 'cat' => 'Networking', 'tags' => ['tcpip', 'networking', 'latency', 'troubleshooting']],
  ['title' => 'Load balancing strategies for mixed traffic workloads', 'body' => 'A single balancing policy rarely works for APIs, streaming traffic, dashboards, and background services at once. This article compares round robin, least connections, weighted routing, and active health-aware balancing in production environments.', 'cat' => 'Networking', 'tags' => ['load-balancing', 'proxy', 'traffic', 'availability']],
  ['title' => 'CDN design beyond static assets and image delivery', 'body' => 'CDNs are no longer limited to caching images and scripts. Teams are using edge logic for security headers, request normalization, API shielding, and regional traffic control without pushing complexity back into the origin.', 'cat' => 'Networking', 'tags' => ['cdn', 'edge', 'performance', 'security']],
  ['title' => 'Segmenting internal networks without slowing delivery teams', 'body' => 'Network segmentation becomes painful when it is implemented as a blanket restriction instead of a service design tool. We look at practical segmentation models that improve isolation while preserving workable paths for engineering teams.', 'cat' => 'Networking', 'tags' => ['segmentation', 'networking', 'zero-trust', 'operations']],

  ['title' => 'Linux observability stacks for lean operations teams', 'body' => 'Lean platform teams need fast visibility into Linux hosts without maintaining a maze of disconnected tools. This article outlines a balanced observability stack that combines metrics, logs, process insight, and simple alert routing.', 'cat' => 'System Administration', 'tags' => ['linux', 'monitoring', 'observability', 'ops']],
  ['title' => 'Windows Server hardening without breaking legacy apps', 'body' => 'Hardening Windows Server in mixed estates is usually a negotiation between security goals and old dependencies. We cover baseline policies, patching cadence, service accounts, and rollback planning for business-critical environments.', 'cat' => 'System Administration', 'tags' => ['windows-server', 'hardening', 'security', 'operations']],
  ['title' => 'Patch management workflows that actually hold up at scale', 'body' => 'Patch compliance improves when teams design the workflow around maintenance windows, exceptions, and verification, not just approvals. We review the operational model that reduces both drift and change fatigue.', 'cat' => 'System Administration', 'tags' => ['patching', 'operations', 'compliance', 'infrastructure']],
  ['title' => 'Monitoring noisy fleets without drowning in alerts', 'body' => 'Alert fatigue often comes from instrumenting everything and deciding later what matters. This article focuses on signal design, escalation hygiene, and ownership boundaries that keep infrastructure monitoring actionable.', 'cat' => 'System Administration', 'tags' => ['monitoring', 'alerts', 'sre', 'operations']],
  ['title' => 'Identity and access reviews for hybrid admin environments', 'body' => 'Hybrid server estates tend to accumulate privileged access paths over time. We walk through recurring access reviews, account scoping, service account cleanup, and the audit evidence operations teams usually wish they had sooner.', 'cat' => 'System Administration', 'tags' => ['iam', 'access-control', 'servers', 'audit']],

  ['title' => 'Designing accessible interfaces without flattening the brand', 'body' => 'Accessibility work should sharpen the product, not make it visually generic. We cover contrast systems, focus treatment, content hierarchy, and interaction design decisions that support accessibility while preserving a strong visual identity.', 'cat' => 'Web Design', 'tags' => ['accessibility', 'ui', 'design-system', 'ux']],
  ['title' => 'Responsive layout decisions that survive real content', 'body' => 'Responsive failures often appear only after real headlines, translations, and editorial blocks are added. This article looks at layout systems, spacing rules, and content-aware breakpoints that hold up in production.', 'cat' => 'Web Design', 'tags' => ['responsive', 'css', 'layout', 'frontend']],
  ['title' => 'CSS architecture patterns for long-lived product teams', 'body' => 'CSS becomes expensive when naming, scope, and override habits drift. We compare component-driven naming, token-based theming, and layered stylesheet structure that help teams make changes without fear.', 'cat' => 'Web Design', 'tags' => ['css', 'architecture', 'design-system', 'frontend']],
  ['title' => 'UX research signals that should influence backlog priorities', 'body' => 'Strong UX research does more than generate quotes for slide decks. We examine how teams translate usability findings, task friction, and navigation confusion into concrete engineering priorities and measurable improvements.', 'cat' => 'Web Design', 'tags' => ['ux', 'research', 'product', 'frontend']],
  ['title' => 'Navigation systems for content-heavy publishing platforms', 'body' => 'Editorial platforms need navigation that scales with categories, campaigns, and evolving content strategies. This guide covers mega menus, contextual navigation, and search-first patterns for content-rich properties.', 'cat' => 'Web Design', 'tags' => ['navigation', 'publishing', 'ux', 'content-design']],

  ['title' => 'Designing AWS landing zones for fast-moving product teams', 'body' => 'A good AWS landing zone gives teams self-service paths without leaving guardrails behind. We cover account structure, identity federation, network boundaries, and cost visibility practices that support sustainable growth.', 'cat' => 'Cloud/Server', 'tags' => ['aws', 'cloud', 'governance', 'platform']],
  ['title' => 'Kubernetes rollout patterns that reduce deployment risk', 'body' => 'Progressive delivery in Kubernetes is much safer when health signals, rollback rules, and traffic shifting are treated as first-class concerns. We walk through canaries, probes, and release guardrails that teams can adopt incrementally.', 'cat' => 'Cloud/Server', 'tags' => ['kubernetes', 'deployment', 'containers', 'platform']],
  ['title' => 'Docker image hygiene for secure and predictable builds', 'body' => 'Container image sprawl quietly increases both cost and risk. This article reviews base image selection, multi-stage builds, package pinning, and image scanning habits that keep delivery pipelines cleaner.', 'cat' => 'Cloud/Server', 'tags' => ['docker', 'containers', 'security', 'builds']],
  ['title' => 'Comparing managed services across AWS GCP and Azure', 'body' => 'Cloud comparisons are only useful when they focus on operational tradeoffs rather than feature checklists. We compare identity, logging, networking, compute, and database services through the lens of day-two operations.', 'cat' => 'Cloud/Server', 'tags' => ['aws', 'gcp', 'azure', 'cloud']],
  ['title' => 'Server runtime baselines for resilient application hosting', 'body' => 'Even in container-heavy environments, teams still need sensible runtime baselines for compute hosts and supporting services. We outline practical server standards covering logging, secrets, backup posture, and lifecycle management.', 'cat' => 'Cloud/Server', 'tags' => ['servers', 'hosting', 'infrastructure', 'operations']],

  ['title' => 'Zero trust adoption lessons from real engineering teams', 'body' => 'Zero trust moves from slogan to reality only when identity, device posture, and service access policies are implemented in a way teams can actually live with. We examine rollout sequencing and common adoption friction.', 'cat' => 'Security', 'tags' => ['zero-trust', 'security', 'identity', 'access']],
  ['title' => 'Penetration testing scopes that produce useful findings', 'body' => 'A penetration test is most useful when the scope matches the system architecture and threat model. This article explains how teams define test boundaries, validation goals, and remediation handoffs that lead to stronger outcomes.', 'cat' => 'Security', 'tags' => ['penetration-testing', 'security', 'risk', 'assurance']],
  ['title' => 'Security baselines for SaaS-heavy organizations', 'body' => 'Organizations with hundreds of SaaS tools need a clear security baseline for identity, provisioning, file sharing, and admin access. We review the controls that make the biggest practical difference without overwhelming teams.', 'cat' => 'Security', 'tags' => ['saas', 'security', 'identity', 'governance']],
  ['title' => 'Threat modeling workshops engineers will actually use', 'body' => 'Threat modeling works best when it is concise, visual, and connected to real architecture decisions. We cover workshop formats, facilitation prompts, and documentation patterns that make the practice sustainable.', 'cat' => 'Security', 'tags' => ['threat-modeling', 'security', 'architecture', 'engineering']],
  ['title' => 'Incident response tabletop scenarios for modern product stacks', 'body' => 'Tabletop exercises help teams discover weak communication paths long before a live incident does. This guide proposes realistic scenarios around credential exposure, ransomware, and cloud misconfiguration for multidisciplinary drills.', 'cat' => 'Security', 'tags' => ['incident-response', 'security', 'tabletop', 'operations']],
  ['title' => 'PostgreSQL indexing strategies for high-write systems', 'body' => 'PostgreSQL indexing is most effective when it matches real query paths and write patterns, not generic best practice lists. We look at composite indexes, partial indexes, and the maintenance tradeoffs they introduce.', 'cat' => 'Database', 'tags' => ['postgresql', 'database', 'indexing', 'performance']],
  ['title' => 'MySQL replication patterns for dependable read scaling', 'body' => 'Read replicas help only when consistency expectations and failure handling are clear. This article covers replication lag, topology choices, failover planning, and the application decisions that keep MySQL scale-outs predictable.', 'cat' => 'Database', 'tags' => ['mysql', 'replication', 'database', 'availability']],
  ['title' => 'Redis design choices for caching sessions and queues', 'body' => 'Redis gets overloaded when teams treat it as a catch-all utility without defining usage boundaries. We review data structures, expiration design, memory policies, and operational guardrails for safer Redis adoption.', 'cat' => 'Database', 'tags' => ['redis', 'caching', 'queues', 'performance']],
  ['title' => 'MongoDB document modeling for fast-moving product teams', 'body' => 'Document databases work best when the model reflects access patterns rather than abstract data purity. We examine embedding, referencing, indexing, and schema discipline for teams building quickly on MongoDB.', 'cat' => 'Database', 'tags' => ['mongodb', 'database', 'schema', 'backend']],
  ['title' => 'Database migration planning that avoids weekend fire drills', 'body' => 'Successful migrations depend on rehearsed cutovers, compatibility planning, and rollback options that are tested before the big day. This guide focuses on the practical moves teams make to reduce avoidable migration risk.', 'cat' => 'Database', 'tags' => ['database', 'migration', 'cutover', 'operations']],

  ['title' => 'GitOps operating models for multi-environment delivery', 'body' => 'GitOps can simplify environment management when teams define clear ownership over repositories, promotion flows, and drift handling. We explore the practical controls that prevent GitOps from turning into hidden manual ops.', 'cat' => 'DevOps', 'tags' => ['gitops', 'devops', 'delivery', 'platform']],
  ['title' => 'CI/CD pipelines that balance speed and release confidence', 'body' => 'Fast pipelines matter, but not at the expense of verification quality. This article covers pipeline stages, test selection, deployment approvals, and observability hooks that support both speed and confidence.', 'cat' => 'DevOps', 'tags' => ['cicd', 'pipelines', 'testing', 'delivery']],
  ['title' => 'Infrastructure as code review habits that catch real risk', 'body' => 'IaC reviews are strongest when teams look for behavioral changes, security implications, and lifecycle assumptions instead of formatting. We share review prompts that help engineers find meaningful issues earlier.', 'cat' => 'DevOps', 'tags' => ['iac', 'terraform', 'review', 'infrastructure']],
  ['title' => 'Release engineering practices for busy platform teams', 'body' => 'Release engineering improves when ownership, change windows, and rollback mechanics are explicit. This guide looks at branching, build provenance, artifact retention, and handoff patterns that reduce release friction.', 'cat' => 'DevOps', 'tags' => ['release-engineering', 'devops', 'automation', 'ops']],
  ['title' => 'Platform engineering metrics that leadership can understand', 'body' => 'Good platform metrics bridge technical quality and business value. We focus on lead time, deployment health, incident recovery, and self-service adoption metrics that help leadership invest in the right platform work.', 'cat' => 'DevOps', 'tags' => ['platform-engineering', 'metrics', 'devops', 'leadership']],

  ['title' => 'MLOps workflows that keep models useful after launch', 'body' => 'Production ML work is mostly operational discipline: versioning data, monitoring drift, and controlling deployment risk. We review the workflows that help ML teams move from isolated experiments to dependable services.', 'cat' => 'AI/ML', 'tags' => ['mlops', 'machine-learning', 'models', 'operations']],
  ['title' => 'NLP product patterns for search summarization and routing', 'body' => 'Natural language processing becomes more useful when paired with narrow product problems like summarization, intent routing, and metadata extraction. This article maps those patterns to realistic implementation choices.', 'cat' => 'AI/ML', 'tags' => ['nlp', 'search', 'summarization', 'ai']],
  ['title' => 'Computer vision deployment tradeoffs at the edge', 'body' => 'Edge computer vision systems must balance model accuracy, latency, device limitations, and connectivity constraints. We look at inference packaging, monitoring, and fallback behavior for field deployments.', 'cat' => 'AI/ML', 'tags' => ['computer-vision', 'edge-ai', 'inference', 'ml']],
  ['title' => 'LLM evaluation methods for production-facing assistants', 'body' => 'LLM features need evaluation beyond anecdotal prompts. We cover rubric design, structured test sets, hallucination checks, and release gates that help teams improve assistant quality over time.', 'cat' => 'AI/ML', 'tags' => ['llm', 'evaluation', 'ai', 'quality']],
  ['title' => 'Choosing retrieval pipelines for enterprise AI search', 'body' => 'Retrieval quality depends on chunking, metadata, ranking strategy, and feedback loops, not just embeddings. This guide walks through the practical design decisions behind enterprise AI search systems.', 'cat' => 'AI/ML', 'tags' => ['retrieval', 'ai-search', 'rag', 'embeddings']],

  ['title' => 'React Native release discipline for product teams', 'body' => 'React Native can support fast cross-platform delivery when release engineering is handled deliberately. We cover dependency alignment, native bridge risk, store submission routines, and observability for mobile releases.', 'cat' => 'Mobile', 'tags' => ['react-native', 'mobile', 'release', 'javascript']],
  ['title' => 'Flutter component strategies for shared mobile design systems', 'body' => 'Flutter makes it possible to ship a unified design language quickly, but shared component libraries still need documentation and ownership. We look at theming, composition, and package hygiene for growing teams.', 'cat' => 'Mobile', 'tags' => ['flutter', 'mobile', 'design-system', 'ui']],
  ['title' => 'iOS performance tuning for content-heavy applications', 'body' => 'Content-rich iOS apps often struggle with startup time, rendering, and offline behavior as features pile up. This article highlights practical optimization techniques teams can apply without rewriting the product.', 'cat' => 'Mobile', 'tags' => ['ios', 'mobile', 'performance', 'apple']],
  ['title' => 'Android reliability patterns for fragmented device fleets', 'body' => 'Android delivery gets harder when teams support a wide device matrix and inconsistent network conditions. We focus on background work, state persistence, crash insight, and release controls that improve reliability.', 'cat' => 'Mobile', 'tags' => ['android', 'mobile', 'reliability', 'kotlin']],
  ['title' => 'Mobile analytics events that actually improve product decisions', 'body' => 'Mobile analytics is only useful when events are named consistently and tied to user questions the team genuinely needs answered. We review instrumentation patterns that make analytics cleaner and more trustworthy.', 'cat' => 'Mobile', 'tags' => ['mobile', 'analytics', 'product', 'instrumentation']],
];

$events = [
  ['title' => 'Cloud Native Delivery Summit 2026', 'desc' => 'A practitioner-led conference for engineering leaders building production platforms with Kubernetes, observability, secure delivery pipelines, and internal developer portals.', 'loc' => 'Suntec Convention Centre, Singapore', 'type' => 'Conference', 'days' => 8, 'cat' => 'Cloud/Server'],
  ['title' => 'Applied Kubernetes Workshop for Platform Teams', 'desc' => 'An intensive workshop covering cluster design, workload rollout strategies, ingress patterns, and day-two Kubernetes operations for delivery teams.', 'loc' => 'Online', 'type' => 'Workshop', 'days' => 15, 'cat' => 'Cloud/Server'],
  ['title' => 'Modern Python Systems Training', 'desc' => 'A hands-on training session for backend teams working with FastAPI, testing, background jobs, and production diagnostics in Python services.', 'loc' => 'FXMedia Lab, Jakarta', 'type' => 'Training', 'days' => 21, 'cat' => 'Programming'],
  ['title' => 'Zero Trust Security Forum', 'desc' => 'Security leaders and platform engineers discuss practical identity, device posture, and service access controls for modern enterprise environments.', 'loc' => 'Marina Bay Financial Centre, Singapore', 'type' => 'Conference', 'days' => 28, 'cat' => 'Security'],
  ['title' => 'LLM Systems for Enterprise Search Webinar', 'desc' => 'A live webinar on retrieval pipelines, evaluation methods, metadata strategy, and UX tradeoffs when teams introduce LLM features into search products.', 'loc' => 'Online', 'type' => 'Webinar', 'days' => 11, 'cat' => 'AI/ML'],
  ['title' => 'DevOps Metrics and Platform Governance Roundtable', 'desc' => 'A small-group networking session for engineering managers comparing delivery metrics, platform investment models, and governance decisions across product teams.', 'loc' => 'One Raffles Quay, Singapore', 'type' => 'Networking', 'days' => 19, 'cat' => 'DevOps'],
  ['title' => 'PostgreSQL Performance Tuning Masterclass', 'desc' => 'A workshop for engineers responsible for query tuning, indexing strategy, replication design, and operational resiliency in PostgreSQL estates.', 'loc' => 'Online', 'type' => 'Training', 'days' => 33, 'cat' => 'Database'],
  ['title' => 'Designing Accessible Interfaces for Real Products', 'desc' => 'A practical UI session that covers accessibility audits, semantic structure, keyboard flows, and design system decisions for public-facing digital products.', 'loc' => 'Google Singapore, Mapletree Business City', 'type' => 'Workshop', 'days' => 24, 'cat' => 'Web Design'],
];

$press_releases = [
  ['title' => 'FXMedia Tech launches regional engineering editorial series', 'body' => 'FXMedia Tech has launched a new editorial initiative focused on practical engineering topics across cloud infrastructure, security, data, and software delivery. The series is designed to help technical teams compare tools, tradeoffs, and implementation patterns with grounded operational context.', 'cat' => 'Programming'],
  ['title' => 'New industry alliance formed to improve cloud cost transparency', 'body' => 'A new cross-industry alliance has been formed to promote better cost visibility, workload tagging standards, and governance practices for organizations operating large cloud estates across Southeast Asia.', 'cat' => 'Cloud/Server'],
  ['title' => 'Regional platform teams publish shared DevOps maturity benchmark', 'body' => 'Engineering leaders from multiple organizations have released a shared maturity benchmark covering release confidence, platform self-service, infrastructure automation, and operational resilience.', 'cat' => 'DevOps'],
  ['title' => 'Security leaders release guidance for zero trust rollout sequencing', 'body' => 'A practical guidance paper now outlines recommended sequencing for identity hardening, privileged access cleanup, and service access controls as organizations move toward zero trust operating models.', 'cat' => 'Security'],
  ['title' => 'Database operators forum announces PostgreSQL reliability playbook', 'body' => 'A new operations playbook for PostgreSQL environments has been published with recommendations for backups, replication monitoring, indexing hygiene, and change management.', 'cat' => 'Database'],
  ['title' => 'Mobile engineering survey highlights testing gaps in cross-platform apps', 'body' => 'A new survey of mobile product teams reveals that release issues are still heavily tied to inadequate device coverage, inconsistent analytics instrumentation, and weak pre-release validation.', 'cat' => 'Mobile'],
];

$speeches = [
  ['title' => 'Keynote on building trustworthy AI search experiences', 'body' => 'This keynote explores how product teams can introduce AI-assisted search without losing clarity, editorial control, or operational safety. The remarks focus on grounding, transparent fallback behavior, and better result interpretation for users.', 'speaker' => 'Chief Product Officer', 'occasion' => 'Applied AI Product Summit', 'days' => -2],
  ['title' => 'Opening remarks at the Southeast Asia Platform Engineering Forum', 'body' => 'Opening remarks on why platform engineering succeeds when it reduces friction for delivery teams while staying honest about governance, operating cost, and support boundaries.', 'speaker' => 'VP of Engineering', 'occasion' => 'SEA Platform Engineering Forum', 'days' => -5],
  ['title' => 'Commentary on resilient cloud architectures for regulated sectors', 'body' => 'A short address on how teams in regulated industries can modernize infrastructure while preserving auditability, security control, and business continuity across complex estates.', 'speaker' => 'Head of Cloud Strategy', 'occasion' => 'Cloud Governance Exchange', 'days' => -8],
  ['title' => 'Remarks on accessible product design as an engineering discipline', 'body' => 'These remarks frame accessibility not as a checklist at the end of delivery, but as a product engineering habit that improves structure, clarity, and usability for everyone.', 'speaker' => 'Director of Experience Design', 'occasion' => 'Digital Experience Leaders Forum', 'days' => -11],
  ['title' => 'Speech on modern database operations and service reliability', 'body' => 'An operations-focused talk on how disciplined change management, observability, and performance hygiene help database teams support product growth without unnecessary risk.', 'speaker' => 'Principal Database Architect', 'occasion' => 'Regional Data Systems Conference', 'days' => -15],
  ['title' => 'Closing address on the future of cross-platform mobile delivery', 'body' => 'A closing address on how mobile teams can move faster by improving release discipline, shared component ownership, analytics design, and engineering feedback loops.', 'speaker' => 'Mobile Practice Lead', 'occasion' => 'Connected Apps Summit', 'days' => -20],
];

$created_counts = [
  'article' => 0,
  'event' => 0,
  'press_release' => 0,
  'speech_commentary' => 0,
];

foreach ($articles as $index => $item) {
  $existing = $node_storage->loadByProperties([
    'type' => 'article',
    'title' => $item['title'],
  ]);
  if ($existing) {
    continue;
  }

  $article = $node_storage->create([
    'type' => 'article',
    'title' => $item['title'],
    'langcode' => 'en',
    'uid' => 1,
    'status' => 1,
    'promote' => 1,
    'body' => ['value' => $item['body'], 'format' => 'basic_html'],
    'field_category' => ['target_id' => $category_ids[$item['cat']]],
    'field_summary_plain' => mb_substr($item['body'], 0, 160),
    'field_published_date' => ['value' => gmdate('Y-m-d\TH:i:s', strtotime('-' . $index . ' days'))],
    'field_reading_time' => max(3, (int) ceil(str_word_count($item['body']) / 170)),
  ]);

  if ($article->hasField('field_tags')) {
    $article->set('field_tags', $buildTagReferences($item['tags']));
  }

  $article->save();
  $created_counts['article']++;
}

foreach ($events as $item) {
  $existing = $node_storage->loadByProperties([
    'type' => 'event',
    'title' => $item['title'],
  ]);
  if ($existing) {
    continue;
  }

  $values = [
    'type' => 'event',
    'title' => $item['title'],
    'langcode' => 'en',
    'uid' => 1,
    'status' => 1,
    'promote' => 1,
    'field_event_description' => ['value' => $item['desc'], 'format' => 'basic_html'],
    'field_event_location' => $item['loc'],
    'field_event_date' => [
      'value' => gmdate('Y-m-d\TH:i:s', strtotime('+' . $item['days'] . ' days 09:00')),
      'end_value' => gmdate('Y-m-d\TH:i:s', strtotime('+' . $item['days'] . ' days 17:00')),
    ],
    'field_registration_link' => ['uri' => 'https://example.com/register', 'title' => 'Register now'],
  ];

  if (isset($event_type_ids[$item['type']])) {
    $values['field_event_type'] = ['target_id' => $event_type_ids[$item['type']]];
  }
  if (isset($category_ids[$item['cat']])) {
    $values['field_event_category'] = ['target_id' => $category_ids[$item['cat']]];
  }

  $node_storage->create($values)->save();
  $created_counts['event']++;
}

foreach ($press_releases as $index => $item) {
  $existing = $node_storage->loadByProperties([
    'type' => 'press_release',
    'title' => $item['title'],
  ]);
  if ($existing) {
    continue;
  }

  $values = [
    'type' => 'press_release',
    'title' => $item['title'],
    'langcode' => 'en',
    'uid' => 1,
    'status' => 1,
    'promote' => 1,
    'body' => ['value' => $item['body'], 'format' => 'basic_html'],
    'field_release_date' => ['value' => gmdate('Y-m-d\TH:i:s', strtotime('-' . ($index * 4) . ' days'))],
    'field_contact_person' => 'FXMedia Communications Desk',
  ];

  if (isset($category_ids[$item['cat']])) {
    $values['field_press_category'] = ['target_id' => $category_ids[$item['cat']]];
  }

  $node_storage->create($values)->save();
  $created_counts['press_release']++;
}

foreach ($speeches as $item) {
  $existing = $node_storage->loadByProperties([
    'type' => 'speech_commentary',
    'title' => $item['title'],
  ]);
  if ($existing) {
    continue;
  }

  $node_storage->create([
    'type' => 'speech_commentary',
    'title' => $item['title'],
    'langcode' => 'en',
    'uid' => 1,
    'status' => 1,
    'promote' => 1,
    'body' => ['value' => $item['body'], 'format' => 'basic_html'],
    'field_speaker_name' => $item['speaker'],
    'field_event_occasion' => $item['occasion'],
    'field_speech_date' => ['value' => gmdate('Y-m-d\TH:i:s', strtotime($item['days'] . ' days'))],
  ])->save();
  $created_counts['speech_commentary']++;
}

print sprintf(
  "Seed complete: %d articles, %d events, %d press releases, %d speeches/commentaries.\n",
  $created_counts['article'],
  $created_counts['event'],
  $created_counts['press_release'],
  $created_counts['speech_commentary']
);
