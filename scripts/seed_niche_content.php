<?php

declare(strict_types=1);

/**
 * Seeds diverse niche content for a specific site.
 *
 * Usage via drush:
 *   ddev drush --uri=primary.ddev.site php:script scripts/seed_niche_content.php -- finance
 *   ddev drush --uri=site1.ddev.site php:script scripts/seed_niche_content.php -- technology
 *   ddev drush --uri=site2.ddev.site php:script scripts/seed_niche_content.php -- health
 */

$niche = $extra[0] ?? 'finance';
$node_storage = \Drupal::entityTypeManager()->getStorage('node');
$term_storage = \Drupal::entityTypeManager()->getStorage('taxonomy_term');

// Resolve category term IDs.
$category_ids = [];
foreach ($term_storage->loadByProperties(['vid' => 'category']) as $term) {
  $category_ids[$term->label()] = (int) $term->id();
}

// Resolve event type term IDs.
$event_type_ids = [];
foreach ($term_storage->loadByProperties(['vid' => 'event_type']) as $term) {
  $event_type_ids[$term->label()] = (int) $term->id();
}

// ----- CONTENT DEFINITIONS PER NICHE -----

$niches = [
  'finance' => [
    'articles' => [
      ['title' => 'Central bank digital currencies reshape cross-border payments', 'body' => 'Central banks across Southeast Asia are piloting digital currency platforms that could reduce remittance costs by up to 40 percent. This article examines the latest CBDC developments and their implications for trade finance, correspondent banking, and SME payment corridors.', 'cat' => 'Finance'],
      ['title' => 'ESG reporting standards gain traction among ASEAN listed firms', 'body' => 'New sustainability disclosure requirements are reshaping how publicly listed companies in the region report environmental, social, and governance metrics. We explore the harmonisation efforts and their impact on institutional investment flows.', 'cat' => 'Investment'],
      ['title' => 'Private equity deal flow surges in Southeast Asian fintech', 'body' => 'Venture capital and private equity investments in financial technology startups across the region reached a three-year high. This analysis covers the sectors attracting capital, from embedded finance to insurtech.', 'cat' => 'Finance'],
      ['title' => 'Singapore dollar outlook: monetary policy and inflation trends', 'body' => 'The Monetary Authority of Singapore maintains its exchange-rate-based policy amid moderating inflation. We assess the macroeconomic indicators shaping the Singapore dollar trajectory for the remainder of 2026.', 'cat' => 'Finance'],
      ['title' => 'Green bonds issuance doubles in Asia-Pacific markets', 'body' => 'Sovereign and corporate green bond issuance in the Asia-Pacific region has doubled year-on-year, driven by transition finance frameworks and investor demand for sustainable fixed-income instruments.', 'cat' => 'Investment'],
      ['title' => 'Trade finance digitisation reduces document processing time', 'body' => 'Electronic bills of lading and blockchain-based letters of credit are cutting trade finance processing times from days to hours. We review the platforms gaining adoption across major Asian trading hubs.', 'cat' => 'Trade'],
      ['title' => 'Regional banking consolidation accelerates post-pandemic', 'body' => 'Mid-sized banks across ASEAN are pursuing mergers and acquisitions to build scale, improve digital capabilities, and meet rising regulatory capital requirements.', 'cat' => 'Finance'],
      ['title' => 'Wealth management trends: family offices expand in Singapore', 'body' => 'Singapore continues to attract single and multi-family offices from across Asia. We examine the regulatory framework, tax incentives, and asset allocation preferences driving this growth.', 'cat' => 'Finance'],
      ['title' => 'Microfinance innovation reaches underserved rural communities', 'body' => 'Mobile-first microfinance platforms are extending credit and savings products to previously unbanked populations in Indonesia, Vietnam, and the Philippines.', 'cat' => 'Finance'],
      ['title' => 'Commodity price volatility and its impact on regional supply chains', 'body' => 'Fluctuations in crude oil, palm oil, and rare earth prices continue to challenge supply chain planning across manufacturing-dependent economies in the region.', 'cat' => 'Trade'],
      ['title' => 'Insurance sector embraces AI for claims processing and underwriting', 'body' => 'Artificial intelligence is transforming how insurers assess risk, detect fraud, and process claims, with adoption rates among ASEAN insurers rising sharply.', 'cat' => 'Finance'],
      ['title' => 'Debt capital markets see record corporate bond issuance', 'body' => 'Asian corporates raised record amounts through bond issuance in the first half of 2026, taking advantage of relatively stable interest rates and strong investor appetite.', 'cat' => 'Investment'],
    ],
    'events' => [
      ['title' => 'ASEAN Banking Summit 2026', 'desc' => 'Annual summit bringing together central bankers, regulators, and commercial bank executives to discuss financial stability, digital innovation, and cross-border cooperation.', 'loc' => 'Marina Bay Sands, Singapore', 'type' => 'Conference', 'days' => 14],
      ['title' => 'Trade Finance Masterclass', 'desc' => 'Intensive two-day workshop on letters of credit, supply chain finance, and digital trade documentation for corporate treasury professionals.', 'loc' => 'Raffles City Convention Centre', 'type' => 'Workshop', 'days' => 28],
      ['title' => 'ESG Investing Webinar Series', 'desc' => 'Four-part webinar series covering ESG integration, impact measurement, and sustainable portfolio construction for institutional investors.', 'loc' => 'Online', 'type' => 'Webinar', 'days' => 7],
      ['title' => 'Fintech Demo Day', 'desc' => 'Showcase event for early-stage fintech startups presenting to venture capital investors and banking innovation teams.', 'loc' => 'One Raffles Quay, Singapore', 'type' => 'Networking', 'days' => 35],
      ['title' => 'Risk Management Certification Programme', 'desc' => 'Professional certification programme covering market risk, credit risk, operational risk, and regulatory compliance.', 'loc' => 'Singapore Management University', 'type' => 'Training', 'days' => 42],
    ],
    'press_releases' => [
      ['title' => 'Industry council publishes updated guidelines on digital asset custody', 'body' => 'The financial industry council has released comprehensive guidelines for digital asset custody, addressing security standards, insurance requirements, and regulatory reporting.', 'cat' => 'Finance'],
      ['title' => 'Regional trade body signs MOU with three central banks on payment interoperability', 'body' => 'A landmark memorandum of understanding has been signed to establish cross-border real-time payment linkages between Singapore, Thailand, and Indonesia.', 'cat' => 'Trade'],
      ['title' => 'New sustainability-linked loan framework launched for SMEs', 'body' => 'A consortium of regional banks has introduced a standardised framework enabling small and medium enterprises to access preferential loan terms tied to sustainability targets.', 'cat' => 'Finance'],
      ['title' => 'Annual financial literacy survey shows improved digital banking confidence', 'body' => 'The latest survey reveals a 15 percent increase in consumer confidence using digital banking services, with mobile payments adoption reaching new highs across the region.', 'cat' => 'Finance'],
    ],
    'speeches' => [
      ['title' => 'Keynote on financial inclusion and digital identity infrastructure', 'body' => 'Remarks on how digital identity systems can accelerate financial inclusion by enabling remote account opening, credit scoring, and insurance access for underserved populations.', 'speaker' => 'Managing Director', 'occasion' => 'Financial Inclusion Forum 2026', 'days' => -5],
      ['title' => 'Opening address at the Regional Capital Markets Conference', 'body' => 'Address on the role of deep and liquid capital markets in channelling savings into productive investment and supporting long-term economic growth.', 'speaker' => 'Chairman', 'occasion' => 'Regional Capital Markets Conference', 'days' => -12],
      ['title' => 'Remarks on regulatory innovation and sandbox frameworks', 'body' => 'Commentary on how regulatory sandboxes have enabled responsible fintech innovation while maintaining consumer protection and systemic stability.', 'speaker' => 'President', 'occasion' => 'Fintech Policy Dialogue', 'days' => -3],
      ['title' => 'Closing remarks on sustainable finance roadmap for ASEAN', 'body' => 'Reflections on the progress made in developing a common sustainable finance taxonomy and the challenges ahead in mobilising transition finance.', 'speaker' => 'Secretary General', 'occasion' => 'Sustainable Finance Summit', 'days' => -8],
    ],
  ],

  'technology' => [
    'articles' => [
      ['title' => 'Kubernetes adoption hits mainstream in Southeast Asian enterprises', 'body' => 'Container orchestration has moved from early adopter territory to mainstream deployment across banking, telecoms, and government agencies in the region. We examine the tooling, skills, and operational patterns driving this shift.', 'cat' => 'Technology'],
      ['title' => 'Zero-trust architecture becomes mandatory for government cloud deployments', 'body' => 'Several ASEAN governments have mandated zero-trust security models for cloud infrastructure. This article reviews the architectural patterns and vendor solutions gaining traction.', 'cat' => 'Technology'],
      ['title' => 'Large language models in production: lessons from regional deployments', 'body' => 'Enterprises across the region share their experience deploying LLMs for customer service, document processing, and code generation, including cost management and accuracy challenges.', 'cat' => 'Technology'],
      ['title' => 'Edge computing enables real-time analytics for manufacturing IoT', 'body' => 'Manufacturers in Thailand and Vietnam are deploying edge computing infrastructure to process sensor data locally, reducing latency and enabling predictive maintenance.', 'cat' => 'Technology'],
      ['title' => 'Open-source contributions from Southeast Asia grow 60 percent', 'body' => 'Developers in Indonesia, Vietnam, and the Philippines are increasingly contributing to major open-source projects. We profile the communities and companies driving this growth.', 'cat' => 'Technology'],
      ['title' => 'Cybersecurity talent shortage prompts automation-first strategies', 'body' => 'With cybersecurity job vacancies exceeding supply by 3:1, organisations are turning to SOAR platforms, AI-powered threat detection, and managed security services.', 'cat' => 'Technology'],
      ['title' => 'API economy matures as platforms monetise integration layers', 'body' => 'Banks, logistics companies, and healthcare providers are building revenue streams by exposing APIs to third-party developers. We examine the business models and governance frameworks.', 'cat' => 'Technology'],
      ['title' => 'Low-code platforms gain enterprise adoption beyond prototyping', 'body' => 'Low-code and no-code development platforms are moving into mission-critical applications as enterprises seek to reduce development backlogs and empower citizen developers.', 'cat' => 'Technology'],
      ['title' => 'Data sovereignty laws reshape cloud architecture decisions', 'body' => 'New data localisation requirements in Indonesia, Vietnam, and Thailand are forcing enterprises to rethink their cloud strategy, with hybrid and multi-cloud architectures becoming the norm.', 'cat' => 'Policy'],
      ['title' => 'Quantum computing readiness: what enterprises should prepare for now', 'body' => 'While practical quantum computing remains years away, enterprises should begin assessing cryptographic vulnerabilities and exploring quantum-resistant algorithms.', 'cat' => 'Technology'],
      ['title' => 'DevOps maturity assessment reveals gaps in testing automation', 'body' => 'A regional survey of DevOps practices shows strong CI/CD adoption but significant gaps in automated testing, security scanning, and observability.', 'cat' => 'Technology'],
      ['title' => 'Cloud cost optimisation saves enterprises up to 35 percent annually', 'body' => 'FinOps practices including rightsizing, reserved instance planning, and automated scaling are delivering significant cost reductions for organisations with large cloud footprints.', 'cat' => 'Technology'],
    ],
    'events' => [
      ['title' => 'Cloud Native Conference Southeast Asia', 'desc' => 'Two-day conference featuring talks on Kubernetes, service mesh, serverless, and cloud-native security from practitioners across the region.', 'loc' => 'Suntec Convention Centre, Singapore', 'type' => 'Conference', 'days' => 21],
      ['title' => 'Cybersecurity Bootcamp: Incident Response', 'desc' => 'Hands-on training covering threat hunting, digital forensics, incident containment, and recovery procedures for security operations teams.', 'loc' => 'NUS Computing, Singapore', 'type' => 'Training', 'days' => 14],
      ['title' => 'AI/ML in Production Workshop', 'desc' => 'Practical workshop on deploying machine learning models to production, covering MLOps pipelines, model monitoring, and drift detection.', 'loc' => 'Online', 'type' => 'Workshop', 'days' => 35],
      ['title' => 'Open Source Meetup: Contributing to Major Projects', 'desc' => 'Community meetup for developers interested in contributing to open-source projects, featuring lightning talks and mentorship sessions.', 'loc' => 'Google Singapore, Mapletree Business City', 'type' => 'Networking', 'days' => 7],
      ['title' => 'DevSecOps Certification Programme', 'desc' => 'Professional programme covering secure coding practices, container security, infrastructure as code scanning, and compliance automation.', 'loc' => 'Online', 'type' => 'Training', 'days' => 49],
    ],
    'press_releases' => [
      ['title' => 'Tech association launches AI governance framework for ASEAN enterprises', 'body' => 'A comprehensive framework addressing responsible AI deployment, algorithmic transparency, and bias mitigation has been published for regional enterprise adoption.', 'cat' => 'Technology'],
      ['title' => 'New data centre campus announced in Johor with 100MW capacity', 'body' => 'A major hyperscale data centre campus has been announced in Johor, Malaysia, offering colocation and cloud services with renewable energy commitments.', 'cat' => 'Technology'],
      ['title' => 'Regional cybersecurity alliance formed to combat cross-border threats', 'body' => 'Five national cybersecurity agencies have established a formal alliance for real-time threat intelligence sharing, joint incident response, and capacity building.', 'cat' => 'Technology'],
      ['title' => 'Startup ecosystem report shows record funding for deeptech ventures', 'body' => 'The annual ecosystem report reveals record venture funding for deeptech startups in AI, quantum computing, and advanced materials across the region.', 'cat' => 'Technology'],
    ],
    'speeches' => [
      ['title' => 'Keynote on building digital public infrastructure at scale', 'body' => 'Address on how national digital identity, payment rails, and data exchange layers form the foundation for inclusive digital economies across ASEAN.', 'speaker' => 'CEO', 'occasion' => 'Digital Government Summit', 'days' => -4],
      ['title' => 'Remarks on responsible AI deployment in regulated industries', 'body' => 'Commentary on the unique challenges of deploying AI systems in banking, healthcare, and government, where explainability and fairness requirements are paramount.', 'speaker' => 'CTO', 'occasion' => 'AI Ethics Conference', 'days' => -10],
      ['title' => 'Opening address at Southeast Asian Developer Conference', 'body' => 'Reflections on the growth of the regional developer community and the importance of open-source contribution, knowledge sharing, and inclusive tech education.', 'speaker' => 'President', 'occasion' => 'SEA DevCon 2026', 'days' => -2],
      ['title' => 'Panel remarks on cloud migration strategies for legacy systems', 'body' => 'Insights on practical approaches to modernising legacy monolithic applications, including the strangler fig pattern, domain-driven design, and incremental containerisation.', 'speaker' => 'VP Engineering', 'occasion' => 'Enterprise Cloud Forum', 'days' => -7],
    ],
  ],

  'health' => [
    'articles' => [
      ['title' => 'Telemedicine adoption accelerates across rural Southeast Asia', 'body' => 'Video consultation platforms and remote diagnostics are expanding healthcare access to underserved rural communities in Indonesia, the Philippines, and Myanmar. We examine the regulatory frameworks enabling this growth.', 'cat' => 'Technology'],
      ['title' => 'AI-powered diagnostic imaging reduces radiology backlogs', 'body' => 'Hospitals across the region are deploying AI systems that assist radiologists in detecting abnormalities in X-rays, CT scans, and MRIs, reducing reporting times from days to hours.', 'cat' => 'Technology'],
      ['title' => 'Mental health awareness campaigns gain corporate momentum', 'body' => 'Major employers in Singapore and Malaysia are implementing comprehensive workplace mental health programmes, including counselling services, flexible work arrangements, and stress management training.', 'cat' => 'Business'],
      ['title' => 'Preventive health screening programmes show measurable impact', 'body' => 'National screening programmes for diabetes, hypertension, and certain cancers are demonstrating cost-effectiveness in early detection and reduced downstream treatment costs.', 'cat' => 'Policy'],
      ['title' => 'Traditional medicine integration with modern healthcare systems', 'body' => 'Several ASEAN countries are developing regulatory frameworks to integrate traditional medicine practices with evidence-based modern healthcare, creating complementary treatment pathways.', 'cat' => 'Policy'],
      ['title' => 'Nutrition science advances reshape public health recommendations', 'body' => 'New research on gut microbiome health, personalised nutrition, and the role of ultra-processed foods is influencing updated dietary guidelines across the region.', 'cat' => 'Business'],
      ['title' => 'Healthcare workforce development addresses critical nursing shortage', 'body' => 'Accelerated training programmes, international recruitment initiatives, and technology-assisted care models are being deployed to address nursing shortages.', 'cat' => 'Business'],
      ['title' => 'Wearable health technology enables continuous patient monitoring', 'body' => 'Smartwatches and medical-grade wearables are enabling continuous monitoring of heart rate, blood oxygen, and glucose levels, supporting proactive chronic disease management.', 'cat' => 'Technology'],
      ['title' => 'Regional pharmaceutical supply chain resilience post-pandemic', 'body' => 'ASEAN nations are investing in local pharmaceutical manufacturing capacity and regional supply chain redundancy to reduce dependence on single-source suppliers.', 'cat' => 'Trade'],
      ['title' => 'Health data interoperability standards gain regional adoption', 'body' => 'FHIR-based health data exchange standards are enabling interoperability between hospitals, clinics, and national health information systems across multiple countries.', 'cat' => 'Technology'],
      ['title' => 'Community health worker programmes scale with mobile technology', 'body' => 'Mobile apps for community health workers are improving maternal health monitoring, childhood vaccination tracking, and disease surveillance in remote areas.', 'cat' => 'Technology'],
      ['title' => 'Genomic medicine pilot programmes identify hereditary disease risks', 'body' => 'Population genomics projects in Singapore and Thailand are generating insights into hereditary disease prevalence and pharmacogenomics, paving the way for personalised medicine.', 'cat' => 'Technology'],
    ],
    'events' => [
      ['title' => 'ASEAN Health Innovation Summit', 'desc' => 'Annual summit on healthcare technology, digital health policy, and innovation in patient care delivery across the region.', 'loc' => 'Shangri-La Hotel, Singapore', 'type' => 'Conference', 'days' => 18],
      ['title' => 'Digital Health Implementation Workshop', 'desc' => 'Practical workshop on implementing electronic medical records, telemedicine platforms, and health information exchanges.', 'loc' => 'National University Hospital, Singapore', 'type' => 'Workshop', 'days' => 30],
      ['title' => 'Mental Health First Aid Training', 'desc' => 'Certification programme teaching participants to recognise signs of mental health issues and provide initial support in workplace settings.', 'loc' => 'Online', 'type' => 'Training', 'days' => 10],
      ['title' => 'Public Health Data Analytics Webinar', 'desc' => 'Webinar on using data analytics for disease surveillance, outbreak prediction, and resource allocation in public health agencies.', 'loc' => 'Online', 'type' => 'Webinar', 'days' => 5],
      ['title' => 'Healthcare Leadership Networking Forum', 'desc' => 'Invitation-only networking event for hospital administrators, health ministry officials, and health-tech investors.', 'loc' => 'Mandarin Oriental, Singapore', 'type' => 'Networking', 'days' => 40],
    ],
    'press_releases' => [
      ['title' => 'Health alliance launches regional vaccine distribution tracking platform', 'body' => 'A blockchain-based platform for tracking vaccine distribution across ASEAN has been launched, ensuring cold chain integrity and equitable allocation.', 'cat' => 'Technology'],
      ['title' => 'New clinical trial network connects research hospitals across five countries', 'body' => 'A collaborative clinical trial network has been established to accelerate drug and treatment research, with standardised protocols and shared data governance.', 'cat' => 'Business'],
      ['title' => 'Health ministry adopts AI-assisted triage for emergency departments', 'body' => 'An AI-powered triage system has been deployed across 15 public hospitals, helping emergency department staff prioritise patients based on symptom severity.', 'cat' => 'Technology'],
      ['title' => 'Annual health systems performance report shows improved outcomes', 'body' => 'The latest health systems report reveals improvements in life expectancy, maternal mortality, and childhood vaccination rates across the region.', 'cat' => 'Policy'],
    ],
    'speeches' => [
      ['title' => 'Keynote on building resilient health systems for future pandemics', 'body' => 'Address on the lessons learned from COVID-19 and the investments needed in surveillance, workforce, and infrastructure to prepare for future health emergencies.', 'speaker' => 'Director General', 'occasion' => 'Health Security Conference', 'days' => -6],
      ['title' => 'Remarks on digital transformation in primary healthcare', 'body' => 'Commentary on how digital tools can strengthen primary care delivery, improve chronic disease management, and reduce the burden on hospital systems.', 'speaker' => 'Minister of Health', 'occasion' => 'Primary Care Summit', 'days' => -9],
      ['title' => 'Opening address at the Regional Nutrition Forum', 'body' => 'Reflections on the double burden of malnutrition and obesity in the region, and the policy interventions needed to promote healthy diets.', 'speaker' => 'WHO Representative', 'occasion' => 'ASEAN Nutrition Forum', 'days' => -3],
      ['title' => 'Panel remarks on health workforce migration and retention', 'body' => 'Discussion on the challenges of healthcare professional migration, ethical recruitment practices, and strategies for retention in source countries.', 'speaker' => 'Dean of Medicine', 'occasion' => 'Health Workforce Planning Forum', 'days' => -14],
    ],
  ],
];

if (!isset($niches[$niche])) {
  print "Unknown niche: $niche. Use: finance, technology, or health.\n";
  return;
}

$data = $niches[$niche];
$created = 0;

// Seed articles.
foreach ($data['articles'] as $i => $item) {
  $existing = $node_storage->loadByProperties(['type' => 'article', 'title' => $item['title']]);
  if ($existing) {
    continue;
  }

  $values = [
    'type' => 'article',
    'title' => $item['title'],
    'langcode' => 'en',
    'uid' => 1,
    'status' => 1,
    'promote' => 1,
    'body' => ['value' => $item['body'], 'format' => 'basic_html'],
    'field_summary_plain' => mb_substr($item['body'], 0, 160),
    'field_published_date' => ['value' => gmdate('Y-m-d\TH:i:s', strtotime('-' . $i . ' days'))],
    'field_reading_time' => rand(2, 8),
  ];

  if (!empty($category_ids[$item['cat']])) {
    $values['field_category'] = ['target_id' => $category_ids[$item['cat']]];
  }

  $node_storage->create($values)->save();
  $created++;
}

// Seed events.
foreach ($data['events'] as $item) {
  $existing = $node_storage->loadByProperties(['type' => 'event', 'title' => $item['title']]);
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
      'value' => gmdate('Y-m-d\TH:i:s', strtotime('+' . $item['days'] . ' days')),
      'end_value' => gmdate('Y-m-d\TH:i:s', strtotime('+' . $item['days'] . ' days +3 hours')),
    ],
    'field_registration_link' => ['uri' => 'https://example.com/register', 'title' => 'Register now'],
  ];

  if (!empty($event_type_ids[$item['type']])) {
    $values['field_event_type'] = ['target_id' => $event_type_ids[$item['type']]];
  }

  $node_storage->create($values)->save();
  $created++;
}

// Seed press releases.
foreach ($data['press_releases'] as $i => $item) {
  $existing = $node_storage->loadByProperties(['type' => 'press_release', 'title' => $item['title']]);
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
    'field_release_date' => ['value' => gmdate('Y-m-d\TH:i:s', strtotime('-' . ($i * 3) . ' days'))],
    'field_contact_person' => 'Communications Team',
  ];

  if (!empty($category_ids[$item['cat']])) {
    $values['field_press_category'] = ['target_id' => $category_ids[$item['cat']]];
  }

  $node_storage->create($values)->save();
  $created++;
}

// Seed speeches.
foreach ($data['speeches'] as $item) {
  $existing = $node_storage->loadByProperties(['type' => 'speech_commentary', 'title' => $item['title']]);
  if ($existing) {
    continue;
  }

  $values = [
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
  ];

  $node_storage->create($values)->save();
  $created++;
}

print "Seeded $created new $niche content items.\n";
