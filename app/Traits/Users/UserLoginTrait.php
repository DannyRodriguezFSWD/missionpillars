<?php

namespace App\Traits\Users;
use Carbon\Carbon;
use App\Constants;

/**
 *
 * @author josemiguel
 */
trait UserLoginTrait {
    
    public function setLastLoginAt() {
        array_set($this, 'last_login_at', Carbon::now());
        $this->update();
        $this->incrementLogins();
    }

    public function incrementLogins() {
        $times = array_get($this, 'logins', 0);
        $logins = $times + 1;
        array_set($this, 'logins', $logins);
        $this->update();
    }
    
}
