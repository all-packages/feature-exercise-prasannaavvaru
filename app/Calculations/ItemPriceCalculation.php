<?php
namespace App\Calculations;

use App\Calculations\ItemPriceCalculationInterface;

class ItemPriceCalculation implements ItemPriceCalculationInterface
{
    public function __construct(){

    }


    /**
     * Calculate item total based on unit price
     * @param int $itemQty specific item quantity
     * @param array $productMasterData specific item master data from products table. It contains unit price, special price rules
     * @return array $response It will return item total for selected quantity
     */
    public function ItemTotalAmount(int $itemQty, array $productMasterData) : array{
            try{
                $unitPrice = (isset($productMasterData['unit_price'])) ? $productMasterData['unit_price'] : 0;
                $status = true;
                $details = $itemQty * $unitPrice;
                $error = 'Success';
            }catch(Exception $e){
                $status = false;
                $error = $e->getMessage();
                $details = '';
            }

            return array('status' => $status, 'details' => $details, 'error' => $error);
    }

    /**
     * Calculate total items based special price rules
     * @param int $itemQty item selected quantity
     * @param string $itemName selected Item name
     * @param array $request user input data
     * @param array $productsJsonMasterData Master data for selected items
     * @param string @equals S/M base S or M total calculation will be change, form some products multiple rules are mentioned based on that calculation will do
     * @return array $response after all calculations total amounts with items list
     */
    public function ItemTotalSpecialAmount(int $itemQty, string $itemName, array $request, array $productsJsonMasterData, string $equals = 'S') : array{
            try{
                $getItemsTotal = 0;
                $productMasterData = $productsJsonMasterData[$itemName];
                if($equals == 'S'){
                    $specialPriceDetails = $productMasterData['special_price_details'][0];
                }                
                else{
                    $randSpecialPriceDetails = array_rand($productMasterData['special_price_details']); 
                    $specialPriceDetails = $productMasterData['special_price_details'][$randSpecialPriceDetails];
                }
                $specialPriceItemEquals = $specialPriceDetails['equals'];
                $unitPrice = (isset($productMasterData['unit_price'])) ? $productMasterData['unit_price'] : 0;
                $specialPrice = $specialPriceDetails['price'];
                if(isset($productsJsonMasterData[$specialPriceItemEquals])){
                    $getItemsTotal = $this->ItemSpecialWithAnotherItem($itemQty, $specialPriceDetails, $unitPrice, $request);
                    if($getItemsTotal['status'] === false)
                        throw new Exception($getItemsTotal['error']);
                }
                else{
                    if($itemQty <= $specialPriceItemEquals)
                    {
                        $getItemsTotal = $unitPrice * $itemQty;
                    }                       
                    elseif($itemQty === $specialPriceItemEquals)
                    {
                        $getItemsTotal = $itemQty * $specialPrice;
                    }                        
                    elseif($itemQty > $specialPriceItemEquals){
                        $getItemsTotal = $this->ItemSpecialItemMore($itemQty, $specialPriceDetails, $unitPrice);   
                    
                        if($getItemsTotal['status'] === false)
                            throw new Exception($getItemsTotal['error']);
                    }     
                }
                $status = true;
                $details = isset($getItemsTotal['details']) ? $getItemsTotal['details'] : $getItemsTotal;
                $error = 'Success';
            }catch(Exception $e){
                $status = false;
                $error = $e->getMessage();
                $details = '';
            }

            return array('status' => $status, 'details' => $details, 'error' => $error);
    }
    

    /**
     * Item calculation depends on another item
     * @param int $itemQty selected item quantity
     * @param array @specialPriceDetails special price rules
     * @param int $unitPrice Item unit price
     * @param array $request user selected Item list
     * @return array $response User will get total amounts for reach item after rules applied
     */
    public function ItemSpecialWithAnotherItem(int $itemQty, array $specialPriceDetails, int $unitPrice, array $request) : array{
            try{
                $itemPrice = 0;
                $linkedItem = $specialPriceDetails['equals'];

                $linkedItemKey = array_search($linkedItem, array_column($request, 'item'));

                if($linkedItemKey !== false){
                    $linkedItemArray = $request[$linkedItemKey];
                    $linkedItemQty = $linkedItemArray['quantity'];
                    if($itemQty > $linkedItemQty)
                        $itemPrice = 5 + (($itemQty - $linkedItemQty) * $unitPrice);
                    elseif($itemQty <= $linkedItemQty)
                        $itemPrice = 5;
                }else{
                    $itemPrice = $itemQty * $unitPrice;
                }

                $status = true;
                $details = $itemPrice;
                $error = 'Success';
            }catch(Exception $e){
                $status = false;
                $error = $e->getMessage();
                $details = '';
            }

            return array('status' => $status, 'details' => $details, 'error' => $error);

    }

    /**
     * Items can have one or more special price rules based on this item calculation will be happen
     * @param int $itemQty selected item qty
     * @param array $specialPriceDetails special price rule details, for some items can have multiple rules.
     * @param int $unitPrice item unit price
     * @return array $response User will get total amounts for reach item after rules applied 
     */
    public function ItemSpecialItemMore(int $itemQty, array $specialPriceDetails, int $unitPrice) : array{
            try{ 
                $itemPrice = 0;
                $specialPriceItemEquals = $specialPriceDetails['equals'];
                $specialPrice = $specialPriceDetails['price'];

                $itemNewQty = $itemQty;
                while($itemNewQty > 0)
                {
                    if($itemNewQty < $specialPriceItemEquals)
                    {
                        $itemPrice += $itemNewQty * $unitPrice;
                        break;  
                    }
                    
                    $itemPrice += $specialPrice;

                    $itemNewQty = $itemNewQty - $specialPriceItemEquals;
                }

                $status = true;
                $details = $itemPrice;
                $error = 'Success';
            }catch(Exception $e){
                $status = false;
                $error = $e->getMessage();
                $details = '';
            }

            return array('status' => $status, 'details' => $details, 'error' => $error);

    }
}
