<?php
namespace App\Calculations;

use App\Calculations\ItemPriceCalculationInterface;

class ItemPriceCalculation implements ItemPriceCalculationInterface
{
    private $productsJsonMasterData;
    private $productMasterData;
    private $specialPriceDetails;
    public function __construct() {
        $this->productsJsonMasterData = json_decode(file_get_contents('./app/products.json'),true)[0];
    }

    public function getItemTotalPrice(
        string $itemName,
        int $itemQty,
        array $request
    ): ?int {
        if (!isset($this->productsJsonMasterData[$itemName])) {
            return null;
        }

        $this->productMasterData = $this->productsJsonMasterData[$itemName];
        
        if (!isset($this->productMasterData['special_price_details'])) {
            $itemTotalPrice = $this->ItemTotalAmount($itemQty);
        } elseif ( count($this->productMasterData['special_price_details']) == 1 ){
            $itemTotalPrice = $this->ItemTotalSpecialAmount($itemQty, $itemName, $request);
        } else {
            $itemTotalPrice = $this->ItemTotalSpecialAmount($itemQty, $itemName, $request, 'M');
        }

        return $itemTotalPrice;
    }


    /**
     * Calculate item total based on unit price
     * @param int $itemQty specific item quantity
     * @param array $productMasterData specific item master data from products table. It contains unit price, special price rules
     * @return array $response It will return item total for selected quantity
     */
    public function ItemTotalAmount(int $itemQty): int {
                $unitPrice = (isset($this->productMasterData['unit_price'])) ? $this->productMasterData['unit_price'] : 0;
                $itemTotalPrice = $itemQty * $unitPrice;

            return $itemTotalPrice;
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
    public function ItemTotalSpecialAmount(
        int $itemQty, 
        string $itemName, 
        array $request,
        string $equals = 'S'
    ): int {
                if ($equals == 'S') {
                    $this->specialPriceDetails = $this->productMasterData['special_price_details'][0];
                } else {
                    $randSpecialPriceDetails = array_rand($this->productMasterData['special_price_details']); 
                    $this->specialPriceDetails = $this->productMasterData['special_price_details'][$randSpecialPriceDetails];
                }
                $specialPriceItemEquals = $this->specialPriceDetails['equals'];
                $unitPrice = (isset($this->productMasterData['unit_price'])) ? $this->productMasterData['unit_price'] : 0;
                $specialPrice = $this->specialPriceDetails['price'];
                
                if (isset($this->productsJsonMasterData[$specialPriceItemEquals])) {
                    $itemTotalPrice = $this->ItemSpecialWithAnotherItem($itemQty, $unitPrice, $request);
                } else {
                    if ($itemQty < $specialPriceItemEquals) {
                        $itemTotalPrice = $unitPrice * $itemQty;
                    } elseif ($itemQty === $specialPriceItemEquals) {
                        $itemTotalPrice = $specialPrice;
                    } elseif ($itemQty > $specialPriceItemEquals) {
                        $itemTotalPrice = $this->ItemSpecialItemMore($itemQty, $unitPrice);   
                    }     
                }

                return $itemTotalPrice;
    }
    

    /**
     * Item calculation depends on another item
     * @param int $itemQty selected item quantity
     * @param array @specialPriceDetails special price rules
     * @param int $unitPrice Item unit price
     * @param array $request user selected Item list
     * @return array $response User will get total amounts for reach item after rules applied
     */
    public function ItemSpecialWithAnotherItem(
        int $itemQty,
        int $unitPrice, 
        array $request
    ): int {
                $linkedItem = $this->specialPriceDetails['equals'];
                $linkedItemKey = array_search($linkedItem, array_column($request, 'item'));
                if ($linkedItemKey !== false) {
                    $linkedItemArray = $request[$linkedItemKey];
                    $linkedItemQty = $linkedItemArray['quantity'];
                    if ($itemQty > $linkedItemQty)
                        $itemTotalPrice = 5 + (($itemQty - $linkedItemQty) * $unitPrice);
                    elseif ($itemQty <= $linkedItemQty)
                        $itemTotalPrice = 5;
                } else {
                    $itemTotalPrice = $itemQty * $unitPrice;
                }

            return $itemTotalPrice;

    }

    /**
     * Items can have one or more special price rules based on this item calculation will be happen
     * @param int $itemQty selected item qty
     * @param array $specialPriceDetails special price rule details, for some items can have multiple rules.
     * @param int $unitPrice item unit price
     * @return array $response User will get total amounts for reach item after rules applied 
     */
    public function ItemSpecialItemMore(
        int $itemQty,
        int $unitPrice
    ): int {
                $specialPriceItemEquals = $this->specialPriceDetails['equals'];
                $specialPrice = $this->specialPriceDetails['price'];

                $itemTotalPrice = 0;

                $itemNewQty = $itemQty;
                while($itemNewQty > 0) {
                    if ($itemNewQty < $specialPriceItemEquals) {
                        $itemTotalPrice =  $itemTotalPrice + ($itemNewQty * $unitPrice);
                        break;  
                    }
                    
                    $itemTotalPrice = $itemTotalPrice + $specialPrice;

                    $itemNewQty = $itemNewQty - $specialPriceItemEquals;
                }

                return $itemTotalPrice;

    }
}
