<?php

namespace App;

class Checkout implements CheckoutInterface
{
    /**
     * @var array
     */
    protected $cart = [];

    /**
     * @var int[]
     */
    protected $pricing = [
        'A' => 50,
        'B' => 30,
        'C' => 20,
        'D' => 15,
        'E' => 5,
    ];

    /**
     * @var int[][]
     */
    protected $discounts = [
        'A' => [
            'threshold' => 3,
            'amount' => 20
        ],
        'B' => [
            'threshold' => 2,
            'amount' => 15
        ],
        'C' => [
            0 => [
            'threshold' => 2,
            'amount' => 2
            ],
            1 => [
                
            'threshold' => 3,
            'amount' => 10
            ]
        ],
        'D' => [
            'threshold' => 'A',
            'amount' => 10
        ],
    ];

    /**
     * @var int[]
     */
    protected $stats = [
        'A' => 0,
        'B' => 0,
        'C' => 0,
        'D' => 0,
        'E' => 0,
    ];

    /**
     * Adds an item to the checkout
     *
     * @param $sku string
     */
    public function scan(string $sku)
    {
        if (!array_key_exists($sku, $this->pricing)) {
            return;
        }

        $this->stats[$sku] = $this->stats[$sku] + 1;

        $this->cart[] = [
            'sku' => $sku,
            'price' => $this->pricing[$sku]
        ];
    }

    /**
     * Calculates the total price of all items in this checkout
     *
     * @return int
     */
    public function total(): int
   {
        $standardPrices = array_reduce($this->cart, function ($total, array $product) {
            $total += $product['price'];
            return $total;
        }) ?? 0;

        $totalDiscount = 0;

        foreach ($this->discounts as $key => $discount) {
            if(is_array($key) && count($key) > 1){
                foreach($key as $thresh){
            if ($this->stats[$key] >= $thresh) {
                $numberOfSets = floor($this->stats[$key] / $thresh);
                $totalDiscount += ($discount['amount'] * $numberOfSets);
            }
        }
        } else if(is_numeric($discount['threshold'])) {
            if ($this->stats[$key] >= $discount['threshold']) {
                $numberOfSets = floor($this->stats[$key] / $discount['threshold']);
                $totalDiscount += ($discount['amount'] * $numberOfSets);
            }
        } else {
            foreach($this->cart as $cartkey){
if($cartkey['sku'] == $discount['threshold']){
    $totalDiscount += $discount['amount'];
}
            }
        }
        }

        return $standardPrices - $totalDiscount;
    }
}
