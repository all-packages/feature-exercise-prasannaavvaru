<?php
declare(strict_types=1);
namespace App\Validate;

use App\Validate\ValidateRequestInterface;

class ValidateRequest implements ValidateRequestInterface
{
    /**
     * validate requested values from client side
     * @param array $request user inputs
     * @param array $rules Validation rules like required, int...etc
     * @param $response If any validation fail then function will send error messages otherwise response shoud be empty
     */
    public static function Validate(array $request, array $rules)
    {
        $ErrorResponse = [];

        foreach($request as $requestKey=>$requestObj){
            foreach($requestObj as $validateName=>$validateValue){
                if(!isset($rules[$validateName]))
                    continue;
                $validateRules = $rules[$validateName];
                $validateRules = explode('|',$validateRules);
                foreach($validateRules as $key=>$rule)
                {
                    $error = (new ValidateRequest())->$rule( $validateValue );
                    if( $error !== false)
                        $ErrorResponse[$validateName] = isset($ErrorResponse[$validateName]) ? ','.$validateName.' '.$ErrorResponse[$validateName].', '.$validateName.' '.$error : $validateName.' '.$error;
                }
            }
            
        }

        return (count($ErrorResponse) > 0) ? json_encode($ErrorResponse) : false;
    }

    /**
     * Validate value is empty or not
     * @param $value
     */
    public function required($value){
        if(is_null( $value ) === true || $value == '')
            return "should not be empty";

        return false;
    }

    /**
     * Validate value is integer or not
     * @param $value 
     */
    public function int($value){
        if(is_integer( $value ) === false)
            return 'should be integer';
        
        return false;
    }

    /**
     * Validate value is string or not
     * @param $value 
     */
    public function string($value){
        if(is_string( $value ) === false)
            return 'should be string';
        
        return false;
    }

    /**
     * Validate value is numeric or not
     * @param $value 
     */
    public function numeric($value){
        if(is_numeric( $value ) === false)
            return 'should be numeric';

        return false;
    }

    
}
