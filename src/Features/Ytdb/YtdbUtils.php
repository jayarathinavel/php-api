<?php

    namespace Features\Ytdb;

    use Features\Ytdb\YtdbException;

    class YtdbUtils {
        private string $apiKey;

        public function __construct(string $apiKey) {
            if (empty($apiKey)) {
                throw new YtdbException(
                    'YouTube API key is not configured',
                    500,
                    'MISSING_API_KEY'
                );
            }
            $this->apiKey = $apiKey;
        }

        /**
         * Make API request to YouTube with error handling
         */
        private function makeApiRequest(string $url): array {
            // Suppress warnings and capture errors
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'timeout' => 10
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                $error = error_get_last();
                throw new YtdbException(
                    'Failed to connect to YouTube API: ' . ($error['message'] ?? 'Network error'),
                    503,
                    'API_CONNECTION_ERROR'
                );
            }

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new YtdbException(
                    'Invalid response from YouTube API: ' . json_last_error_msg(),
                    500,
                    'INVALID_API_RESPONSE'
                );
            }

            // Check for YouTube API errors
            if (isset($data['error'])) {
                $errorMessage = $data['error']['message'] ?? 'Unknown YouTube API error';
                $errorCode = $data['error']['code'] ?? 500;
                
                // Map YouTube API error codes to meaningful messages
                $userMessage = $this->mapYouTubeError($errorCode, $errorMessage);
                
                throw new YtdbException(
                    $userMessage,
                    $errorCode >= 400 && $errorCode < 500 ? 400 : 500,
                    'YOUTUBE_API_ERROR'
                );
            }

            return $data;
        }

        /**
         * Map YouTube API errors to user-friendly messages
         */
        private function mapYouTubeError(int $code, string $message): string {
            switch ($code) {
                case 400:
                    return 'Invalid video URL or ID provided';
                case 403:
                    if (strpos($message, 'quota') !== false) {
                        return 'YouTube API quota exceeded. Please try again later';
                    }
                    return 'YouTube API access denied. Please check API key configuration';
                case 404:
                    return 'Video not found on YouTube. It may be private, deleted, or the URL is incorrect';
                default:
                    return 'YouTube API error: ' . $message;
            }
        }

        /**
         * Get video duration in MM:SS format
         */
        public function getDuration(string $videoID): string {
            if (empty($videoID)) {
                throw new YtdbException(
                    'Video ID is required',
                    400,
                    'INVALID_VIDEO_ID'
                );
            }

            $url = "https://www.googleapis.com/youtube/v3/videos?part=contentDetails&id={$videoID}&key={$this->apiKey}";

            $data = $this->makeApiRequest($url);

            if (empty($data['items'])) {
                throw new YtdbException(
                    'Video not found on YouTube. The video may be private, deleted, or the ID is incorrect',
                    404,
                    'VIDEO_NOT_FOUND'
                );
            }

            $duration = $data['items'][0]['contentDetails']['duration'] ?? null;

            if (!$duration) {
                throw new YtdbException(
                    'Unable to retrieve video duration from YouTube',
                    500,
                    'MISSING_DURATION'
                );
            }

            preg_match_all('/(\d+)/', $duration, $parts);

            // Handle durations safely
            $minutes = $parts[0][0] ?? '00';
            $seconds = $parts[0][1] ?? '00';

            return sprintf('%02d:%02d', $minutes, $seconds);
        }

        /**
         * Get video title
         */
        public function getTitle(string $videoID): string {
            if (empty($videoID)) {
                throw new YtdbException(
                    'Video ID is required',
                    400,
                    'INVALID_VIDEO_ID'
                );
            }

            $url = "https://www.googleapis.com/youtube/v3/videos?id={$videoID}&key={$this->apiKey}&part=snippet";

            $data = $this->makeApiRequest($url);

            if (empty($data['items'])) {
                throw new YtdbException(
                    'Video not found on YouTube. The video may be private, deleted, or the ID is incorrect',
                    404,
                    'VIDEO_NOT_FOUND'
                );
            }

            $title = $data['items'][0]['snippet']['title'] ?? null;

            if (!$title) {
                throw new YtdbException(
                    'Unable to retrieve video title from YouTube',
                    500,
                    'MISSING_TITLE'
                );
            }

            return $title;
        }

        /**
         * Get video thumbnail URL
         */
        public function getThumbnail(string $videoID): string {
            if (empty($videoID)) {
                throw new YtdbException(
                    'Video ID is required',
                    400,
                    'INVALID_VIDEO_ID'
                );
            }
            return "https://i.ytimg.com/vi/{$videoID}/mqdefault.jpg";
        }

        /**
         * Extract video ID from YouTube URL
         */
        public function getVideoId(string $url): string {
            if (empty($url)) {
                throw new YtdbException(
                    'YouTube URL is required',
                    400,
                    'INVALID_URL'
                );
            }

            $videoID = null;

            // Handle youtube.com/watch?v=VIDEO_ID format
            if ($this->containsWord($url, 'watch')) {
                $parsedUrl = parse_url($url);
                if (!isset($parsedUrl['query'])) {
                    throw new YtdbException(
                        'Invalid YouTube URL format. Expected format: https://www.youtube.com/watch?v=VIDEO_ID',
                        400,
                        'INVALID_URL_FORMAT'
                    );
                }
                parse_str($parsedUrl['query'], $queryParams);
                $videoID = $queryParams['v'] ?? null;
            }

            // Handle youtu.be/VIDEO_ID format
            if ($this->containsWord($url, 'youtu.be')) {
                $parsedUrl = parse_url($url);
                if (!isset($parsedUrl['path'])) {
                    throw new YtdbException(
                        'Invalid YouTube short URL format. Expected format: https://youtu.be/VIDEO_ID',
                        400,
                        'INVALID_URL_FORMAT'
                    );
                }
                $path = $parsedUrl['path'];
                $segments = explode('/', trim($path, '/'));
                $videoID = $segments[0] ?? null;
            }

            if (empty($videoID)) {
                throw new YtdbException(
                    'Could not extract video ID from URL. Please provide a valid YouTube URL (e.g., https://www.youtube.com/watch?v=VIDEO_ID or https://youtu.be/VIDEO_ID)',
                    400,
                    'VIDEO_ID_EXTRACTION_FAILED'
                );
            }

            // Validate video ID format (YouTube video IDs are typically 11 characters)
            if (!preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoID)) {
                throw new YtdbException(
                    'Invalid YouTube video ID format. Video ID should be 11 characters long',
                    400,
                    'INVALID_VIDEO_ID_FORMAT'
                );
            }

            return $videoID;
        }

        /**
         * Get all video information at once (optimized to reduce API calls)
         */
        public function getVideoInfo(string $videoID): array {
            if (empty($videoID)) {
                throw new YtdbException(
                    'Video ID is required',
                    400,
                    'INVALID_VIDEO_ID'
                );
            }

            $url = "https://www.googleapis.com/youtube/v3/videos?part=snippet,contentDetails&id={$videoID}&key={$this->apiKey}";

            $data = $this->makeApiRequest($url);

            if (empty($data['items'])) {
                throw new YtdbException(
                    'Video not found on YouTube. The video may be private, deleted, or the ID is incorrect',
                    404,
                    'VIDEO_NOT_FOUND'
                );
            }

            $item = $data['items'][0];
            $title = $item['snippet']['title'] ?? null;
            $duration = $item['contentDetails']['duration'] ?? null;

            if (!$title) {
                throw new YtdbException(
                    'Unable to retrieve video title from YouTube',
                    500,
                    'MISSING_TITLE'
                );
            }

            if (!$duration) {
                throw new YtdbException(
                    'Unable to retrieve video duration from YouTube',
                    500,
                    'MISSING_DURATION'
                );
            }

            // Parse duration
            preg_match_all('/(\d+)/', $duration, $parts);
            $minutes = $parts[0][0] ?? '00';
            $seconds = $parts[0][1] ?? '00';
            $formattedDuration = sprintf('%02d:%02d', $minutes, $seconds);

            return [
                'title' => $title,
                'duration' => $formattedDuration,
                'thumbnail' => $this->getThumbnail($videoID)
            ];
        }

        /**
         * Check if a string contains a word
         */
        private function containsWord(string $str, string $word): bool {
            return (bool) preg_match('#\b' . preg_quote($word, '#') . '\b#i', $str);
        }
    }
