<?php
namespace App;

use App\validate\ValidateRequestInterface;

use App\Calculations\ItemPriceCalculationInterface;

class CheckoutView
{
    private $validate = '';
    private $itemCalculate = '';
    private $itemPriceCalulation = '';
    public function __construct(ValidateRequestInterface $validateInterface, ItemPriceCalculationInterface $itemCalInterface){
        $this->validate = $validateInterface;
        $this->itemPriceCalulation = $itemCalInterface;
    }

    /**
     * Display Checkout summery
     * @param array $request user selected items list
     * @return array $response Diplay items with total amounts to display in cart
     */
    public function ViewCart(array $request){
        
        try{
            $status = 200;
            $error = '';
            $data = [];

            $validateCartRequest = $this->validateCartRequest($request);

            if($validateCartRequest !== true)
                throw new Exception($validateCartRequest);
                
            $totalCart = $this->calculateCart($request);
            if($totalCart['status'] == 500)
                throw new Exception($totalCart['error']);
            
            $data = $totalCart;
            $response = array('status' =>$status, 'error'=>$error, 'data'=>$data);
        }catch(Exception $e){
            $status = 500;
            $error = $e->getMessage();
            $response = array('status' =>$status, 'error'=>$error);
        }

        return $response;

        
    }

    /** Validate request parameter
     * @param array $request User input data
     * @return array $response If all validations done it will throw empty other wise function return error message.
     */
    public function validateCartRequest(array $request)
    {
        $validateRules = array(
            'item' => 'required|string',
            'quantity' => 'required|int'
        );
        $validateResult = $this->validate::Validate($request, $validateRules);
        if($validateResult !== false)
            return $validateResult;
        else
            return true;
    }

    /** Calculate item total based on product configurations
     * @param array $request user input data
     * @return array $response It will send all items total along with item name
     */
    public function calculateCart(array $request){
        try{
            $status = 200;
            $error = '';
            $data = [];
    
            $productsJsonMasterData = json_decode(file_get_contents('./app/products.json'),true);
    
            $productsJsonMasterData = $productsJsonMasterData[0];
    
            $itemSummeryDetails = [];
            
            foreach($request as $key=>$itemObj)
            {
                $itemName = $itemObj['item'];
                $itemQty = $itemObj['quantity'];
    
                if(!isset($productsJsonMasterData[$itemName])){
                    throw new Exception("Item master data not found");
                }
        
                $productMasterData = $productsJsonMasterData[$itemName];
                
                if(!isset($productMasterData['special_price_details'])){
                    $itemSummery = $this->itemPriceCalulation->ItemTotalAmount($itemQty, $productMasterData);
                }elseif(count($productMasterData['special_price_details']) == 1){
                    $itemSummery = $this->itemPriceCalulation->ItemTotalSpecialAmount($itemQty, $itemName, $request, $productsJsonMasterData);
                }else{
                    $itemSummery = $this->itemPriceCalulation->ItemTotalSpecialAmount($itemQty, $itemName, $request, $productsJsonMasterData, 'M');
                }
                
                if($itemSummery['status'] === false)
                    throw new Exception($itemSummery['details']);
    
                $itemSummeryDetails[$itemName] = $itemSummery['details'];  
    
            }
            
            $data = $itemSummeryDetails;
            
            $response = array('status' =>$status, 'error'=>$error, 'data'=>$data);
            
        }
        catch(Exception $e){
            $status = 500;
            $error = $e->getMessage();
            $response = array('status' =>$status, 'error'=>$error);
        }

        return $response;
        
    }
}