<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class NotificationTriggered
{
    use Dispatchable;

    public string $action;
    public array $data;
    public ?array $actor;
    public array $recipients;
    public array $options;

    public function __construct(
        string $action,
        array $data = [],
        ?array $actor = null,
        array $recipients = [],
        array $options = []
    ) {
        $this->action = $action;
        $this->data = $data;
        $this->actor = $actor;
        $this->recipients = $recipients;
        $this->options = $options;
    }
}
