<?php

namespace App\Traits;

use App\Classes\MissionPillarsLog;
use App\Models\PaymentOption;

use Carbon\Carbon;
use Stripe\Stripe;

trait UpdatesPaymentOptions {

    /**
     * Updates the specified PaymentOption expiration date and the related soure for the Stripe Customer 
     */
    public function updatePaymentOptionExpiration($stripecustomer, $payment_option_id, $expiration)
    {
        $card = PaymentOption::findOrFail($payment_option_id);
        //update stripe
        $stripe_card = $stripecustomer->sources->retrieve($card->card_id);
        if ($expiration->month) $stripe_card->exp_month = $expiration->month;
        if ($expiration->year) $stripe_card->exp_year = $expiration->year;
        $stripe_card->save();

        $date = implode('-', [
            $expiration->year,
            $expiration->month,
            '01'
        ]);
        
        $expirationdate = Carbon::parse($date);
        array_set($card, 'card_expiration', $expirationdate->endOfMonth()->toDateString());
        $card->update();
        
        return $card;
    }

    /**
     * Sets the default PaymentOption for the tenant and related payment source for the Stripe Customer
     */
    public function setDefaultPaymentOption($payment_option, $user = null) 
    {
        if (!$user) {
            $user = auth()->user();
        }
        $stripecustomer = $user->asStripeCustomer();
        if (!$stripecustomer) {
            MissionPillarsLog::log([
                'event' => 'setDefaultPaymentOption',
                'message' => "User ({$user->id}) is not stripe customer",
            ]);
        }
        
        
        $tenant = $user->tenant;
        $tenant->payment_option_id = $payment_option->id; // set tenant default
        $tenant->update();
        
        // set Stripe Payment option as well
        $stripecustomer->default_source = $payment_option->card_id;
        $stripecustomer->save();
    }
    
    
    /**
     * Removes the specified PaymentOption for the tenant and related payment source for the Stripe Customer
     */
    public function removeStripePaymentOption($user, $payment_option_id)
    {
        $payment_option = PaymentOption::findOrFail($payment_option_id);
        $stripecustomer = $user->asStripeCustomer();
        Stripe::setApiKey(env('STRIPE_SECRET'));
        try {
            $stripecustomer->deleteSource($stripecustomer->id, $payment_option->card_id);
        } catch (\Exception $e) {
            // most likely a 'no such source' error
        }
        
        if ($user->payment_option_id == $payment_option_id) {
            $user->payment_option_id = null;
            $user->save();
        }
        $payment_option->delete(); // soft-delete (Do not try to undelete)
    }
    
    /**
     * Removes ALL PaymentOption linked with Stripe and all the sources for the related Stripe Customer
     */
    public function removeStripePaymentOptions($user)
    {
        $users = $user->tenant->users()->stripeCustomer();
        
        foreach ($users as $stripeuser) {
            $stripecustomer = $stripeuser->asStripeCustomer();
            Stripe::setApiKey(env('STRIPE_SECRET'));
            // NOTE removing all just in case some are lingering in Stripe for some reason
            foreach ($stripecustomer->sources as $source) {
                $stripecustomer->deleteSource($stripecustomer->id, $source->id);
            }
        }
        
        $user->tenant->payment_option_id = null;
        $user->tenant->save();
        $user->tenant->paymentOptions()->stripe()->delete();
    }
}
