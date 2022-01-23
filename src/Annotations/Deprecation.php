<?php

namespace Mpokket\APIHelper\Annotations;

/**
 * @Annotation
 */
class Deprecation {
    /**
     * Deprecation timestamp or date in any standard date time format.
     * Examples:
     * - 12-12-2022
     * - 2022-12-12 14:30:00
     * @var mixed
     */
    public $since;
    /**
     * Alternate URL for the deprecated API method
     * @var string
     */
    public $alternate;
    /**
     * Sunset policy URL for the deprecated API method
     * @var string
     */
    public $policy;
    /**
     * Sunset timestamp or date in any standard date time format
     * - 12-12-2022
     * - 2022-12-12 14:30:00
     * @var string
     */
    public $sunset;
}
