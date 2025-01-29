<?php

namespace App\Jobs;

use App\Classes\ContinueToGive\ContinueToGiveCampaigns;
use App\Classes\ContinueToGive\ContinueToGiveMissionaries;
use App\Classes\Transactions;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Auth;

class RegistrationDataSync implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $apiKey;
    private $user;
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @param $apiKey
     * @param User $user
     */
    public function __construct($apiKey,User $user)
    {
        $this->apiKey = $apiKey;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Auth::login($this->user);

        (new ContinueToGiveCampaigns($this->apiKey))->run();

        (new ContinueToGiveMissionaries($this->apiKey))->run();

        (new Transactions($this->apiKey))->executeTransactions();

        array_set(auth()->user()->tenant, 'imported_data', true);
        auth()->user()->tenant->update();
    }
}
