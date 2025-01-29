<?php
namespace App\Traits\Commons\Utils;
/**
 *
 * @author josemiguel
 */
trait Colors {

    private function randomColorPart() {
        return str_pad(dechex(mt_rand(0, 255)), 2, '0', STR_PAD_LEFT);
    }

    public function randomHexadecimalColor($hash = true) {
        $color = $this->randomColorPart() . $this->randomColorPart() . $this->randomColorPart();
        if($hash){
            $color = '#'.$color;
        }
        return $color;
    }
    
}
