<?php 
namespace App\Validate;

Interface ValidateRequestInterface{
    public static function Validate(array $request, array $rules);
    public function int($value);
    public function required($value);
    public function string($value);
    public function numeric($value);
}