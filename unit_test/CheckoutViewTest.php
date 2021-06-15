<?php
declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use App\CheckoutView;
use App\Validate\ValidateRequest;
use App\Calculations\ItemPriceCalculation;

/**
 * CheckoutViewTest
 * @group group
 */
class CheckoutViewTest extends TestCase
{
    private $checkout;
    private $priceCalculation;

    public function setUp() : void {
        $this->priceCalculation = new ItemPriceCalculation();

        $this->checkout = new CheckoutView(new ValidateRequest(), $this->priceCalculation);

    }
    
    /** @test 
     * Validate user selected cart items
    */
    public function validate_cart_items() {
        $request = array(
            array(
                'item' => 'A',
                'quantity' => 4,
            ),
            array(
                'item' => 'B',
                'quantity' => 4,
            ),

        );

        $this->assertNull($this->checkout->validateCartRequest($request));
    }

    /** @test 
     * Validate cart total amounts for all scenarios
    */
    public function calculate_cart_total_amount() {
        $request = array(
            array(
                'item' => 'A',
                'quantity' => 2,
            )
        );
        
        $testArray = $this->checkout->calculateCart($request);
        $this->assertIsArray($testArray, json_encode($testArray));
    }


    

    /** @test 
     *  validate user selected items 
     *  validate cart totals
     * */    
    public function view_cart() {
        $request = array(
            array(
                'item' => 'A',
                'quantity' => 6,
            ),
            array(
                'item' => 'A',
                'quantity' => 2,
            ),
            array(
                'item' => 'C',
                'quantity' => 12,
            ),
            array(
                'item' => 'D',
                'quantity' => 10,
            ),
            array(
                'item' => 'D',
                'quantity' => 1,
            )

        );
        
        $testArray = $this->checkout->ViewCart($request);
        $this->assertArrayHasKey('data', $testArray, json_encode($testArray),true);
    }

    /**
     *  calculate item price provider
     *  validate cart totals
     * */ 
    public function calculate_item_price_provider() {
        $request = array(
            array(
                'item' => 'A',
                'quantity' => 6,
            ),
            array(
                'item' => 'B',
                'quantity' => 2,
            ),
            array(
                'item' => 'C',
                'quantity' => 12,
            ),
            array(
                'item' => 'D',
                'quantity' => 10,
            ),
            array(
                'item' => 'E',
                'quantity' => 1,
            )

        );

        return array(
            array(260, ['A',6,$request]),
            array(75, ['B',3,$request]),
            array(65, ['D',10,$request]),
            array(10, ['E',2,$request])
        );
    }


    /** @test
     * calulate item price
     * @dataProvider calculate_item_price_provider
     */
    public function calculate_item_price($expected, $input) {

        $testArray = $this->priceCalculation->getItemTotalPrice( $input[0], $input[1], $input[2] );
        $this->assertEquals( $expected, $testArray );

        $testArray = $this->priceCalculation->getItemTotalPrice( 'C', 12, $input[2] );
        $this->assertTrue( $testArray === 228 || $testArray === 200 );
    }


}