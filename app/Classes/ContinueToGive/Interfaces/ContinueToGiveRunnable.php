<?php
namespace App\Classes\ContinueToGive\Interfaces;

/**
 *
 * @author josemiguel
 */
interface ContinueToGiveRunnable {
    public function call($params = []);
    public function store($json);
    public function run($params = []);
}
