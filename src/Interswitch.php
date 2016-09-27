<?php
/**
 * Interswitch WebPay Integration package for Laravel 5.
 *
 *
 * @author     Bobby <connect@devs.ng>
 * @copyright  2016 DevsNG
 * @license    https://opensource.org/licenses/MIT  MIT License
 *
 * @version    Release: @package_version@
 *
 * @link       https://github.com/devsng/laravel-interswitch
 * @since      Class available since Release 1.0
 */

namespace DevsNG\Interswitch;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use DevsNG\Interswitch\Exceptions\InvalidArgException;

class Interswitch
{
    protected $productid;

    protected $payitemid;

    protected $amount;

    protected $currency;

    protected $redirecturl;

    protected $txnref;

//    protected $testmode;

    protected $testurl = 'https://stageserv.interswitchng.com/test_paydirect/pay';

    protected $liveurl = 'https://webpay.interswitchng.com/paydirect/pay';

    protected $testqueryurl = 'https://stageserv.interswitchng.com/test_paydirect/api/v1/gettransaction.json';

    protected $livequeryurl = 'https://webpay.interswitchng.com/paydirect/api/v1/gettransaction.json';

    protected $queryurl;

    protected $action;

    protected $mackey;

    protected $hash;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->setProductID();
        $this->setPayItemID();
        $this->setMacKey();
        $this->setCurrency();
        $this->setBaseUrls();
    }

    /**
     * Sanitize and generate form values.
     *
     * @param array $options
     *
     * @return array
     */
    public function raw(array $options = [])
    {
        $inputs['action'] = $this->action;
        $inputs['product_id'] = $this->productid;
        $inputs['amount'] = $this->convert(array_get($options, 'amount'));
        $inputs['currency'] = $this->currency;

        $inputs['site_redirect_url'] = $this->setRedirect(
        array_get($options, 'site_redirect_url', config('interswitch.redirect_url'))
      );

        $inputs['txn_ref'] = $this->setTxnRef(
        array_get($options, 'txn_ref', TransRef::getHashedToken(7))
      );

        $hashValues = $this->txnref.
                      $this->productid.
                      $this->payitemid.
                      $inputs['amount'].
                      $inputs['site_redirect_url'].
                      $this->mackey;

        $inputs['hash'] = array_get($options, 'hash', $this->hash($hashValues));

        $inputs['pay_item_id'] = $this->payitemid;
        $inputs['cust_name'] = $this->setCustName(array_get($options, 'cust_name'));
        $inputs['cust_id'] = $this->txnref;

        return $inputs;
    }

    /**
     * Create a new HTML form with sanitized values.
     *
     * @param array $options
     *
     * @return HTML
     */
    public function form(array $options = [])
    {
        $array = $this->raw($options);

        $action = array_get($array, 'action');

        $form = '<form action ="'.$action.'" method="post">'.PHP_EOL;
        $array = array_except($array, ['action']);

        foreach ($array as $key => $value) {
            $form .= '<input type="hidden" name="'.$key.'" value="'.$value.'" />'.PHP_EOL;
        }

        $form .= '<input class="submit" type="submit" name="submit" value="Pay" />'.PHP_EOL;
        $form .= '</form>'.PHP_EOL;

        return $form;
    }

    /**
     *  Process and Output Callback information from Interswitch.
     *
     * @param int $amount
     *
     * @return JSON
     */
    public function callback()
    {
        $array = request()->all();
        $query = $this->query($array);

        $json = json_decode($query, true);

        dd($json['ResponseDescription']);
    //    return json_decode($query, true);
    }

    /**
     *  Make a GET request for Transaction status.
     *
     * @param array $array
     *
     * @return array
     */
    public function query(array $array)
    {
        $product_id = array_get($array, 'product_id', $this->productid);
        $txnref = $this->getTxnRef(array_get($array, 'txnref'));
        $amount = $this->convert(array_get($array, 'amount'));
        $hash = array_get($array, 'hash', $this->hash($product_id.$txnref.$this->mackey));

        $client = new Client([
                                'base_uri' => $this->queryurl,
                                  'headers' => [
                                      'Hash' => $hash,
                                  ],
                              ]);
        $q = "?productid={$product_id}&transactionreference={$txnref}&amount={$amount}";

        $response = $client->request('GET', $q);

    //    $responseCode = $response->getStatusCode();

        return $response->getBody();
    }

    private function hash(string $str)
    {
        $hash = hash('sha512', $str);
        $this->hash = $hash;

        return $this->hash;
    }

    private function setCurrency()
    {
        $this->currency = config('interswitch.currency');
    }

    private function setRedirect($url)
    {
        if (is_null($url)) {
            throw new InvalidArgException('Missing site_redirect_url.');
        }

        $this->redirecturl = $url;

        return $this->redirecturl;
    }

    private function setPayItemID()
    {
        $this->payitemid = config('interswitch.pay_item_id');
    }

    private function setProductID()
    {
        $this->productid = config('interswitch.product_id');
    }

    private function setBaseUrls()
    {
        $testmode = config('interswitch.test_mode');

        if ($testmode) {
            $this->action = $this->testurl;
            $this->queryurl = $this->testqueryurl;
        } else {
            $this->action = $this->liveurl;
            $this->queryurl = $this->livequeryurl;
        }
    }

    private function setTxnRef($txn_ref)
    {
        if (is_null($txn_ref)) {
            throw new InvalidArgException('Missing value for txn_ref');
        }

        $this->txnref = $txn_ref;

        return $this->txnref;
    }

    private function setCustName($custName)
    {
        if (is_null($custName)) {
            throw new InvalidArgException('Missing Customer Name');
        }

        return $custName;
    }

    private function setMacKey()
    {
        $this->mackey = config('interswitch.mac_key');
    }

    private function convert($amount)
    {
        if (is_null($amount)) {
            throw new InvalidArgException('Missing value for amount.');
        }

        $amount = intval($amount) * 100;
        $this->amount = $amount;

        return $this->amount;
    }

    private function getTxnRef($txnref)
    {
        if (is_null($txnref)) {
            throw new InvalidArgException('Missing value for TxnRef');
        }

        return $txnref;
    }
}
