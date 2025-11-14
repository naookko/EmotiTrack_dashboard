<?php

namespace MongoDB\BSON;

if (!class_exists(UTCDateTime::class)) {
    /**
     * Lightweight stub so IDEs recognize the MongoDB UTCDateTime type when the extension isn't loaded.
     */
    class UTCDateTime extends \DateTimeImmutable
    {
        public function __construct($milliseconds = null)
        {
            parent::__construct();
        }
    }
}
