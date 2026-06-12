<?php

namespace App\Application\Assistant;

use RuntimeException;

/**
 * Thrown when the assistant cannot run — typically because no API key
 * is configured, or the upstream model call failed.
 */
class AssistantUnavailableException extends RuntimeException {}
