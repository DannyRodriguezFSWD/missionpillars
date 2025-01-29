<?php

namespace App\Http\Controllers;

use App\Models\Promocode;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Trexology\Promocodes\Model\Promocodes;

class PromocodesController extends Controller
{
    public function validatePromoCode()
    {
        $promoCode = request()->get('promoCode');

        return response()->json($this->checkPromoCode($promoCode) ? 'true' : 'false');
    }

    public function checkPromoCode($code)
    {
        return Promocode::code($code)->valid()->count() > 0;
    }

    public function getPromoCode()
    {
        $reward = request()->get('percent') ? (request()->get('percent') / 100) : 0.2;
        $numberOfCodes = request()->get('num_of_codes') ? request()->get('num_of_codes') : 1;
        $expiry_date = request()->get('expiration') 
        ? Carbon::today()->addDays(request()->get('expiration'))
        : Carbon::today()->addDay(1);
        $quantity = request()->get('quantity') ? request()->get('quantity') : -1;
        

        $codes = $this->generateCodes($reward, $expiry_date, $quantity, $numberOfCodes);

        if ($codes->count() == 1) return response()->json($codes->first());
        return response()->json($codes);
    }


    /**
     * Gets the number of codes that fit specified criteria
     * @param  float $discount
     * @param  Carbon $expiration
     * @param  boolean $unlimited     True if unlimited, false if limited
     * @param  boolean $created_today Optional. If specified and false, includes codes regardless of creation date
     * @return Collection   A collection of promotion code strings
     */
    private function getValidCodes($discount, $expiration, $unlimited, $created_today = true)
    {
        $valid_codes = Promocode::valid()
        ->where('reward',$discount)
        ->where('expiry_date',$expiration);
        
        if ($unlimited) {
            $valid_codes->unlimited();
        } else {
            $valid_codes->limited();
        }
        
        if ($created_today) {
            $valid_codes->createdToday();
        }

        return $valid_codes->pluck('code');
    }

    /**
     * Ensures that valid promocodes are 
     * @param  float  $discount      
     * @param  Carbon  $expiration 
     * @param  integer  $quantity      
     * @param  integer $numberOfCodes  Optional. 1 if unspecified
     * @return Collection                 A collection of promotion code strings
     */
    private function generateCodes($discount, $expiration, $quantity, $numberOfCodes = 1)
    {
        $valid_codes = $this->getValidCodes($discount, $expiration, $quantity == -1);
        $alwaysGenerate = request()->get('always_generate');
        if ($valid_codes->count() < $numberOfCodes || (isset($alwaysGenerate) && $alwaysGenerate == 1)) {
            $codes = new Promocodes();
            $generatedCodes = $codes->generate($numberOfCodes - $valid_codes->count());
            foreach ($generatedCodes as $code) {
                $c = new Promocodes();
                $c->code = $code;
                $c->reward = $discount;
                $c->quantity = $quantity;
                $c->expiry_date = $expiration;
                $c->save();
            }
            return $valid_codes->concat($generatedCodes);
        }
        return $valid_codes->slice(0,$numberOfCodes);
    }
}
