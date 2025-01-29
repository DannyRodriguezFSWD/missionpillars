<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Purpose;
use App\Models\Tag;
use App\Models\TransactionSplit;
use App\Scopes\TenantScope;

class CreateMissingAutoTagsForOfflineTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        $contacts = Contact::noTenantScope()->whereHas('transactions', function($t) { $t->noTenantScope()->offline(); })->get();
        
        $contacts->each(function($c) {
            TenantScope::useTenantId($c->tenant_id);
            
            $purposeids = $c->tags()->where('relation_type',Purpose::class)
            ->pluck('relation_id')->toArray();
            $campaignids = $c->tags()->where('relation_type',Campaign::class)
            ->pluck('relation_id')->toArray();
            
            $missingpurposeids = $c->transactionSplits()
            ->whereHas('transaction', function($t) { $t->offline(); })
            ->whereNotIn('purpose_id',$purposeids)
            ->where('purpose_id','!=',1)
            ->pluck('purpose_id')->toArray();
            $missingcampaignids = $c->transactionSplits()
            ->whereHas('transaction', function($t) { $t->offline(); })
            ->whereNotIn('campaign_id',$campaignids)
            ->where('campaign_id','!=',1)
            ->pluck('campaign_id')->toArray();
            
            $missingtagids = Tag::where(function($q) use ($missingpurposeids) {
                $q->where('relation_type', Purpose::class)
                ->whereIn('relation_id', $missingpurposeids);
            })
            ->orWhere(function($q) use ($missingcampaignids) {
                $q->where('relation_type', Campaign::class)
                ->whereIn('relation_id', $missingcampaignids);
            })
            ->pluck('id')->toArray();
            
            if (!empty($missingtagids)) {
                echo "{$c->id}: attaching tags " . implode(',', $missingtagids) . "\n";
                $c->tags()->attach($missingtagids);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
