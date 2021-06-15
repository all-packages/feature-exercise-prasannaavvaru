<?php
namespace App;

use App\validate\ValidateRequestInterface;
use App\Calculations\ItemPriceCalculationInterface;
use Exception;

class CheckoutView
{
    private $validate = '';
    private $itemCalculate = '';
    private $itemPriceCalulation = '';

    public function __construct(ValidateRequestInterface $validateInterface, ItemPriceCalculationInterface $itemCalInterface) {
        $this->validate = $validateInterface;
        $this->itemPriceCalulation = $itemCalInterface;
    }

    /**
     * Display Checkout summery
     * @param array $request user selected items list
     * $request = array(
     *       array(
     *           'item' => 'A',
     *           'quantity' => 6,
     *       ),
     *       array(
     *           'item' => 'A',
     *           'quantity' => 9,
     *      ),
     *       array(
     *           'item' => 'C',
     *           'quantity' => 12,
     *       ),
     *       array(
     *           'item' => 'D',
     *           'quantity' => 10,
     *       ),
     *       array(
     *           'item' => 'E',
     *           'quantity' => 1,
     *       )
     *   
     *   );
     * @return array $response Diplay items with total amounts to display in cart
     */
    public function ViewCart(array $request) {
        
        try{
            $status = 200;
            $error = '';
            $data = [];

            $validateCartRequest = $this->validateCartRequest($request);

            if ($validateCartRequest)
                throw new Exception($validateCartRequest);
                
            $totalCart = $this->calculateCart($request);

            $response = array('status' =>$status, 'error'=>$error, 'data'=>$totalCart);

        } catch (Exception $e) {
            $status = 500;
            $error = $e->getMessage();
            $response = array('status' =>$status, 'error'=>$error);
        }

        return $response;

        
    }

    /** Validate request parameter
     * @param array $request User input data
     * $request = array(
     *       array(
     *           'item' => 'A',
     *           'quantity' => 6,
     *       ),
     *       array(
     *           'item' => 'A',
     *           'quantity' => 9,
     *      ),
     *       array(
     *           'item' => 'C',
     *           'quantity' => 12,
     *       ),
     *       array(
     *           'item' => 'D',
     *           'quantity' => 10,
     *       ),
     *       array(
     *           'item' => 'E',
     *           'quantity' => 1,
     *       )
     *   
     *   );
     * @return array $response If all validations done it will throw empty other wise function return error message.
     */
    public function validateCartRequest(array $request): ?array {
        $validateRules = array(
            'item' => 'required|string',
            'quantity' => 'required|int'
        );
        $validateResult = $this->validate::Validate($request, $validateRules);
        if ($validateResult !== false)
            return $validateResult;
        else
            return null;
    }

    /** Calculate item total based on product configurations
     * @param array $request user input data
     * $request = array(
     *       array(
     *           'item' => 'A',
     *           'quantity' => 6,
     *       ),
     *       array(
     *           'item' => 'A',
     *           'quantity' => 9,
     *      ),
     *       array(
     *           'item' => 'C',
     *           'quantity' => 12,
     *       ),
     *       array(
     *           'item' => 'D',
     *           'quantity' => 10,
     *       ),
     *       array(
     *           'item' => 'E',
     *           'quantity' => 1,
     *       )
     *   
     *   );
     * @return array $response It will send all items total along with item name
     */
    public function calculateCart( array $request ): array {

            $itemSummeryDetails = [];

            $request = $this->combineSameItems($request);
            
            foreach ($request as $key=>$itemObj)
            {
                $itemName = $itemObj['item'];
                $itemQty = $itemObj['quantity'];
    
                $itemTotalPrice = $this->itemPriceCalulation->getItemTotalPrice($itemName, $itemQty, $request);
                if (!$itemTotalPrice)
                    throw new Exception('Item details not found');
    
                $itemSummeryDetails[$itemName] = $itemTotalPrice;  
    
            }

        return $itemSummeryDetails;
        
    }


    /**
     * if user send same item in seperate array this function will combine both items
     * @param $requst user input data
     * $request = array(
     *       array(
     *           'item' => 'A',
     *           'quantity' => 6,
     *       ),
     *       array(
     *           'item' => 'A',
     *           'quantity' => 9,
     *      ),
     *       array(
     *           'item' => 'C',
     *           'quantity' => 12,
     *       ),
     *       array(
     *           'item' => 'D',
     *           'quantity' => 10,
     *       ),
     *       array(
     *           'item' => 'D',
     *           'quantity' => 1,
     *       )
     *   
     *   );
     * @return $newRequest new updated request data
     * 
     */
    private function combineSameItems(array $request) : array{
        $newRequest = array();

        foreach ($request as $key=>$item) {   
            $searchItem = array_search($item['item'],array_column($newRequest,'item'));
            if ($searchItem === false) {
                $newRequest[] = $item;
            } else {
                $newRequest[$searchItem]['quantity'] += $item['quantity'];
            }
        }

        return $newRequest;
    }
}

    