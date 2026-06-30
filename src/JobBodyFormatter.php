<?php

/**
 * Applies configured body decoders and formats job bodies for display.
 */
class JobBodyFormatter {

    /**
     * Applies decoders while preserving the legacy peek return shape.
     *
     * @param string $body Raw job body.
     * @param array $settings Body display settings.
     * @return array Decoded value and content type.
     */
    public function decodeForPeek($body, $settings) {
        $result = $this->applyDecoders($body, $settings, false);
        return array(
            'body' => $result['peek_body'],
            'content_type' => $result['content_type'],
        );
    }

    /**
     * Applies decoders and returns a display string.
     *
     * @param string $body Raw job body.
     * @param array $settings Body display settings.
     * @param bool $html Whether to HTML-escape the formatted body.
     * @return array Display body and content type.
     */
    public function formatForDisplay($body, $settings, $html) {
        $result = $this->applyDecoders($body, $settings, true);
        $display = $result['display_body'];

        if (!is_string($display)) {
            $display = print_r($display, true);
        }
        if (!preg_match('//u', $display)) {
            $display = base64_encode($display);
            $result['content_type'] = 'base64';
        }

        return array(
            'body' => $html ? htmlspecialchars($display) : $display,
            'content_type' => $result['content_type'],
        );
    }

    /**
     * Applies enabled decoders in display order.
     *
     * Base64 runs first so wrapped serialized or JSON payloads can be decoded next.
     *
     * @param string $body Raw job body.
     * @param array $settings Body display settings.
     * @param bool $formatJson Whether successful JSON should be pretty-printed.
     * @return array Decoded values and content type.
     */
    private function applyDecoders($body, $settings, $formatJson) {
        $display = $body;
        $peek = $body;
        $contentType = 'text';

        if (!empty($settings['enableBase64Decode'])) {
            $decoded = base64_decode($display, true);
            if ($decoded !== false) {
                $display = $decoded;
                $peek = $decoded;
                $contentType = 'base64';
            }
        }

        if (!empty($settings['enableUnserialization'])) {
            $unserialized = @unserialize($display);
            if ($unserialized !== false || $display === serialize(false)) {
                $display = print_r($unserialized, true);
                $peek = $unserialized;
                $contentType = 'php';
            }
        }

        if (!empty($settings['enableJsonDecode'])) {
            $decodedJson = json_decode($display, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if ($formatJson) {
                    $display = json_encode($decodedJson, JSON_PRETTY_PRINT);
                }
                $contentType = 'json';
            }
        }

        return array(
            'display_body' => $display,
            'peek_body' => $peek,
            'content_type' => $contentType,
        );
    }
}
