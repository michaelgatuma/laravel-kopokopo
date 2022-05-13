<?php

use Michaelgatuma\Kopokopo\Facades\Kopokopo;

Route::get('kopokopo', [\App\Http\Controllers\API\Payments\Kopokopo\C2BController::class,'start']);
