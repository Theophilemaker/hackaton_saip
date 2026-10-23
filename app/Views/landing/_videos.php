<?php
/**
 * SAIP Video Library
 * Add / edit / remove videos here — the demo modal reads from this array.
 *
 * Video types supported:
 *   'mp4'     → local file in public/assets/videos/
 *   'youtube' → paste https://www.youtube.com/embed/VIDEO_ID
 *   'vimeo'   → paste https://player.vimeo.com/video/VIDEO_ID
 */
return [
    [
        'id'          => 'platform-overview',
        'category'    => 'Demo',
        'title'       => 'SAIP Platform Overview',
        'description' => 'Full walkthrough of the Smart Agriculture Intelligence Platform — from AI detection to market pricing.',
        'type'        => 'mp4',
        'src'         => BASE_URL . '/assets/videos/platform-demo.mp4',
        'thumbnail'   => BASE_URL . '/assets/videos/thumbs/platform-demo.jpg',
        'duration'    => '2:45',
        'featured'    => true,
    ],
    [
        'id'          => 'agribot',
        'category'    => 'Features',
        'title'       => 'AgriBot AI Assistant',
        'description' => 'Chat with our multilingual AI for instant farming advice in Kinyarwanda, English, French, or Swahili.',
        'type'        => 'mp4',
        'src'         => BASE_URL . '/assets/videos/agribot.mp4',
        'thumbnail'   => BASE_URL . '/assets/videos/thumbs/agribot.jpg',
        'duration'    => '1:20',
    ],
    [
        'id'          => 'disease-detection',
        'category'    => 'Features',
        'title'       => 'AI Disease Detection',
        'description' => 'Snap a leaf → get instant diagnosis and treatment plan from our trained disease library.',
        'type'        => 'mp4',
        'src'         => BASE_URL . '/assets/videos/disease-detection.mp4',
        'thumbnail'   => BASE_URL . '/assets/videos/thumbs/disease.jpg',
        'duration'    => '1:10',
    ],
    [
        'id'          => 'weather',
        'category'    => 'Features',
        'title'       => 'Weather Intelligence',
        'description' => 'District-level forecasts with AI-powered planting and irrigation recommendations.',
        'type'        => 'mp4',
        'src'         => BASE_URL . '/assets/videos/weather.mp4',
        'thumbnail'   => BASE_URL . '/assets/videos/thumbs/weather.jpg',
        'duration'    => '0:55',
    ],
    [
        'id'          => 'marketplace',
        'category'    => 'Features',
        'title'       => 'Smart Marketplace',
        'description' => 'Sell directly to buyers nationwide. Negotiate prices and track deliveries.',
        'type'        => 'mp4',
        'src'         => BASE_URL . '/assets/videos/marketplace.mp4',
        'thumbnail'   => BASE_URL . '/assets/videos/thumbs/marketplace.jpg',
        'duration'    => '1:30',
    ],
    [
        'id'          => 'tutorial-farmer',
        'category'    => 'Tutorials',
        'title'       => 'Getting Started as a Farmer',
        'description' => 'Step-by-step registration, farm setup, and first crop planting.',
        'type'        => 'mp4',
        'src'         => BASE_URL . '/assets/videos/tutorial-farmer.mp4',
        'thumbnail'   => BASE_URL . '/assets/videos/thumbs/tutorial.jpg',
        'duration'    => '3:15',
    ],
    [
        'id'          => 'impact-rwanda',
        'category'    => 'Impact',
        'title'       => 'Real Impact in Rwanda',
        'description' => 'How SAIP is transforming farming across districts with data-driven decisions.',
        'type'        => 'mp4',
        'src'         => BASE_URL . '/assets/videos/impact.mp4',
        'thumbnail'   => BASE_URL . '/assets/videos/thumbs/impact.jpg',
        'duration'    => '4:20',
    ],
];