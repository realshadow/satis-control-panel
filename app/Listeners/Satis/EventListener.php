<?php

namespace App\Listeners\Satis;

use App\Events\Satis\ConfigWasUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventListener implements ShouldQueue {
    /**
     * Create the event listener.
     *
     * @return EventListener
     */
    public function __construct() {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SomeEvent  $event
     * @return void
     */
    public function handle(ConfigWasUpdated $event) {
        #$this
    }
}
