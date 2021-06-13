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
    private $checkout = '';

    public function setUp() : void
    {
        $this->checkout = new CheckoutView(new ValidateRequest(), new ItemPriceCalculation());
    }
    
    /** @test 
     * Validate user selected cart items
    */
    public function validate_cart_items()
    {
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

        $this->assertTrue($this->checkout->validateCartRequest($request));
    }

    /** @test 
     * Validate cart total amounts for all scenarios
    */
    public function calculate_cart_total_amount()
    {
        $request = array(
            array(
                'item' => 'A',
                'quantity' => 2,
            ),
            array(
                'item' => 'B',
                'quantity' => 5,
            ),
            array(
                'item' => 'C',
                'quantity' => 8,
            ),
            array(
                'item' => 'D',
                'quantity' => 3,
            ),
            array(
                'item' => 'E',
                'quantity' => 2,
            )

        );
        
        $testArray = $this->checkout->calculateCart($request);
        $this->assertArrayHasKey('data', $testArray,json_encode($testArray));
    }

    /** @test 
     *  validate user selected items 
     *  validate cart totals
     * */    
    public function test_view_cart(){
        $request = array(
            array(
                'item' => 'A',
                'quantity' => 6,
            ),
            array(
                'item' => 'B',
                'quantity' => 9,
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
        
        $testArray = $this->checkout->ViewCart($request);
        $this->assertArrayHasKey('data', $testArray, json_encode($testArray));
    }

}