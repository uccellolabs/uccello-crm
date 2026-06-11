<?php

namespace App\Infrastructure\Assistant;

use RuntimeException;

/**
 * Thrown when the assistant cannot run — typically because no Anthropic API key
 * is configured, or the upstream model call failed. The controller turns this
 * into a friendly, user-facing message rather than a 500.
 */
class AssistantUnavailableException extends RuntimeException {}
