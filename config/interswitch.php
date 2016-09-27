<?php

/*
 * Laravel Interswitch Web Pay Configuration File.
 *
 * (c) Bobby <connect@devs.ng> [http://community.devs.ng]
 *
 */

return [

    /*
     * Product Identifier code provided by Interswitch
     *
     * If not provided, Test Product ID would be used.
     */

    'product_id' => env('INTERSWITCH_PRODUCT_ID', 6205),

    /*
     *  Payment Item ID provided by Interswitch
     *
     *  If not provided, Test Payment ID would be used.
     */

     'pay_item_id' => env('INTERSWITCH_PAY_ITEM_ID', 101),

     /*
      *  Mac Key provided by Interswitch
      *
      *  If not provided, Test Mac Key would be used.
      */

     'mac_key' => env('INTERSWITCH_MAC_KEY', 'D3D1D05AFE42AD50818167EAC73C109168A0F108F32645C8B59E897FA930DA44F9230910DAC9E20641823799A107A02068F7BC0F4CC41D2952E249552255710F'),

      /*
       *  Default Currency for Transactions.
       *
       *  566 = Naira.
       *
       */

     'currency' => 566,

     /*
      *  Set Test Mode to false if you are ready to Go Live
      *
      */

     'test_mode' => env('INTERSWITCH_TEST_MODE', true),

     /*
      *  Redirect URL for complete transactions on Interswitch.
      *
      *  This can also be provided when calling the form generation
      *  method.
      *
      */

     'redirect_url' => 'http://homestead.app/callback',

];
