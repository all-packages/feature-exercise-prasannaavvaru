<?php 
namespace App\Calculations;

Interface ItemPriceCalculationInterface{
    public function ItemTotalAmount(int $itemQty, array $productMasterData);
    public function ItemTotalSpecialAmount(int $itemQty, string $itemName, array $request, array $productsJsonMasterData, string $equals);
    public function ItemSpecialWithAnotherItem(int $itemQty, array $specialPriceDetails, int $unitPrice, array $request);
    public function ItemSpecialItemMore(int $itemQty, array $specialPriceDetails, int $unitPrice);
   
}