<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FormSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $formId,
        public string $source,
        public array $data,
        public string $createdAt,
    ) {}

    public function broadcastOn(): array
    {
        return [new Channel('admin')];
    }

    public function broadcastAs(): string
    {
        return 'form.submitted';
    }
}
