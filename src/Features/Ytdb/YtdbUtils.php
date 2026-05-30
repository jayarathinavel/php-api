<?php

    namespace Features\Ytdb;
    class YtdbUtils {
        private string $apiKey;

        public function __construct(string $apiKey) {
            $this->apiKey = $apiKey;
        }

        /**
         * Get video duration in MM:SS format
         */
        public function getDuration(string $videoID): ?string {
            $url = "https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id={$videoID}&key={$this->apiKey}";

            $response = file_get_contents($url);

            if (!$response) {
                return null;
            }

            $data = json_decode($response, true);

            if (empty($data['items'])) {
                return null;
            }

            $duration = $data['items'][0]['contentDetails']['duration'];

            preg_match_all('/(\d+)/', $duration, $parts);

            // Handle durations safely
            $minutes = $parts[0][0] ?? '00';
            $seconds = $parts[0][1] ?? '00';

            return sprintf('%02d:%02d', $minutes, $seconds);
        }

        /**
         * Get video title
         */
        public function getTitle(string $videoID): ?string {
            $url = "https://www.googleapis.com/youtube/v3/videos?id={$videoID}&key={$this->apiKey}&part=snippet";

            $response = file_get_contents($url);

            if (!$response) {
                return null;
            }

            $data = json_decode($response, true);

            if (empty($data['items'])) {
                return null;
            }

            return $data['items'][0]['snippet']['title'] ?? null;
        }

        /**
         * Get video thumbnail URL
         */
        public function getThumbnail(string $videoID): string {
            return "https://i.ytimg.com/vi/{$videoID}/mqdefault.jpg";
        }

        /**
         * Extract video ID from YouTube URL
         */
        public function getVideoId(string $url): ?string {
            $videoID = null;

            if ($this->containsWord($url, 'watch')) {
                parse_str(parse_url($url, PHP_URL_QUERY), $queryParams);
                $videoID = $queryParams['v'] ?? null;
            }

            if ($this->containsWord($url, 'youtu.be')) {
                $path = parse_url($url, PHP_URL_PATH);
                $segments = explode('/', trim($path, '/'));
                $videoID = $segments[0] ?? null;
            }

            return $videoID;
        }

        /**
         * Check if a string contains a word
         */
        private function containsWord(string $str, string $word): bool {
            return (bool) preg_match('#\b' . preg_quote($word, '#') . '\b#i', $str);
        }
    }
