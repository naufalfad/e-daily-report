<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\NotificationService;

class SendUnitNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $unitId;
    protected $type;
    protected $message;
    protected $relatedId;

    /**
     * Create a new job instance.
     */
    public function __construct($unitId, $type, $message, $relatedId = null)
    {
        $this->unitId = $unitId;
        $this->type = $type;
        $this->message = $message;
        $this->relatedId = $relatedId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        // Panggil service optimized kita tadi di background
        NotificationService::broadcastToUnit(
            $this->unitId, 
            $this->type, 
            $this->message, 
            $this->relatedId
        );
    }
}