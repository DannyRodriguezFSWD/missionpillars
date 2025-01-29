<?php

namespace App\Providers;

use App\Http\Requests\Contacts\ListContacts;
use App\Models\CalendarEvent;
use App\Models\CalendarEventTemplateSplit;
use App\Models\Campaign;
use App\Models\Contact;
use App\Models\Folder;
use App\Models\Form;
use App\Models\Lists;
use App\Models\OauthAccessToken;
use App\Models\Purpose;
use App\Models\Role;
use App\Models\Tag;
use App\Models\Tenant;
use App\Models\User;
use App\NewMenuItem;
use App\Policies\CalendarEventPolicy;
use App\Policies\CampaignPolicy;
use App\Policies\ContactPolicy;
use App\Policies\FolderPolicy;
use App\Policies\FormPolicy;
use App\Policies\ListPolicy;
use App\Policies\NewMenuItemWidgetPolicy;
use App\Policies\OauthAccessTokenPolicy;
use App\Policies\PurposePolicy;
use App\Policies\RolePolicy;
use App\Policies\TagPolicy;
use App\Policies\TenantPolicy;
use App\Policies\UserPolicy;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Role::class => RolePolicy::class,
        Tag::class => TagPolicy::class,
        Folder::class => FolderPolicy::class,
        Purpose::class => PurposePolicy::class,
        User::class => UserPolicy::class,
        OauthAccessToken::class => OauthAccessTokenPolicy::class,
        Campaign::class => CampaignPolicy::class,
        Form::class => FormPolicy::class,
        Contact::class => ContactPolicy::class,
        Lists::class => ListPolicy::class,
        CalendarEvent::class => CalendarEventPolicy::class,
        CalendarEventTemplateSplit::class => CalendarEventPolicy::class,
        NewMenuItem::class => NewMenuItemWidgetPolicy::class,
        Tenant::class => TenantPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes();
        //
    }
}
