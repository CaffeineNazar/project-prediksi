<?php
// app/Exceptions/MLPredictionException.php

namespace App\Exceptions;

use Exception;

class MLPredictionException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'success' => false,
            'message' => 'ML Prediction Error',
            'error' => $this->getMessage(),
        ], 500);
    }
}