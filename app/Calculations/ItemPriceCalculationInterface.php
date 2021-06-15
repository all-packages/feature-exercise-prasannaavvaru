<?php 
namespace App\Calculations;

Interface ItemPriceCalculationInterface{
    public function getItemTotalPrice( string $itemName, int $itemQty, array $request );
    public function ItemTotalAmount( int $itemQty );
    public function ItemTotalSpecialAmount( int $itemQty, string $itemName, array $request, string $equals );
    public function ItemSpecialWithAnotherItem( int $itemQty, int $unitPrice, array $request );
    public function ItemSpecialItemMore( int $itemQty, int $unitPrice );
   
}