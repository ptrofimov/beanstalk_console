<?php

/**
 * Builds review batch names that should stay consistent across UI and backend fallbacks.
 */
class ReviewBatchNaming {

    /**
     * Returns the default review tube name for a source tube.
     *
     * The source tube stays first so sorted tube lists keep the review tube next to it.
     *
     * @param string $sourceTube Source tube name.
     * @param string|null $suffix Unique suffix, or null to use the current timestamp.
     * @return string
     */
    public static function defaultReviewTube($sourceTube, $suffix = null) {
        if ($suffix === null) {
            $suffix = date('Ymd-His');
        }
        return self::sanitizeTubePart($sourceTube) . '.REVIEW.' . self::sanitizeTubePart($suffix);
    }

    /**
     * Sanitizes a tube-name part using the existing review batch naming character set.
     *
     * @param string $value Tube-name part.
     * @return string
     */
    public static function sanitizeTubePart($value) {
        return preg_replace('/[^A-Za-z0-9_.-]/', '_', $value);
    }
}
