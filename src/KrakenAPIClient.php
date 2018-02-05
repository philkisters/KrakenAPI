<?php

namespace PhilOest;

/**
 * Simple implementation for Kraken's REST API.
 * Example code from "https://github.com/payward/kraken-api-client" has been used.
 *
 * See https://www.kraken.com/help/api for more info.
 */
class KrakenAPIException extends \ErrorException {};
class KrakenAPIClient {

    protected $key;     // API key
    protected $secret;  // API secret
    protected $url;     // API base URL
    protected $version; // API version
    protected $curl;    // curl handle

    /**
     * Constructor for KrakenAPI
     *
     * @param string $key API key
     * @param string $secret API secret
     * @param string $url base URL for Kraken API
     * @param string $version API version
     * @param bool $sslverify enable/disable SSL peer verification.  disable if using beta.api.kraken.com
     */
    function __construct ($key, $secret, $url='https://api.kraken.com', $version='0', $sslverify=true) {

        /* check we have curl */
        if (!function_exists('curl_init')) {
           print "[ERROR] The Kraken API client requires that PHP is compiled with 'curl' support.\n";
           exit(1);
        }

        $this->key = $key;
        $this->secret = $secret;
        $this->url = $url;
        $this->version = $version;
        $this->curl = curl_init();

        curl_setopt_array($this->curl, array(
            CURLOPT_SSL_VERIFYPEER => $sslverify,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT => 'Kraken PHP API Agent',
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true)
        );
    }

    function __destruct () {

        if (function_exists('curl_close')) {
            curl_close($this->curl);
        }
    }

    /**
     * ##############################################################
     * ################## Public API functions ######################
     * ##############################################################
     */

    /**
     * Returns an array with the server time in seconds and as a date.
     * @return array server response | error message
     * 
     * @example getTime()
     */
    function getTime () {
        return $this->queryPublic("Time");
    }

    /**
     * Returns an array of the currencies and their info.
     * @param string $info: info (default)
     * @param string $aclass: currency (default)
     * @param string|array $asset: all (default) | comma delimited list of assets to get info on
     * 
     * @return array server response | error message
     * 
     * @example getAssets($asset="XRP, XBTC")
     */
    function getAssets ($info = "info", $aclass = "currency", $asset = "all") {
        $input = array ();

        $input["aclass"] = $aclass;
        $input["info"] = $info;

        // Add param info if there is a none default one
        if ($asset != "all") {
            if (is_array($asset)) {
                $asset = implode(",", $asset);
            }
            $input["asset"] = $asset; 
        }
        return $this->queryPublic("Assets", $input);
    }

    /**
     * Returns an array of the pair names and their info.
     * @param string $info: info (default) | leverage | fees | margin
     * @param string|array $pair: all (default) | comma delimited list of asset pairs to get info on
     * 
     * @return array server response | error message
     * 
     * @example getAssetPairs("leverage", "XREPZEUR, XREPXXBT")
     */
    function getAssetPairs ($info = "info", $pair = "all") {
        $input = array ();

        $input["info"] = $info;

        // Add param info if there is a none default one
        if ($pair != "all") {
            if (is_array($pair)) {
                $pair = implode(",", $pair);
            }
            $input["pair"] = $pair; 
        }
        return $this->queryPublic("AssetPairs", $input);
    }

    /**
     * Returns an array of the pair names and their ticker info.
     * @param string|array $pair: comma delimited list of asset pairs to get info on ( default: "XBTCZEUR" )
     * 
     * @return array server response | error message
     * 
     * @example getTicker("XREPZEUR, XREPXXBT")
     */
    public function getTicker ($pair = "XBTCZEUR") {
        $input = array ();

        // Add param info if there is a none default one
        if (is_array($pair)) {
            $pair = implode(",", $pair);
        }
        $input["pair"] = $pair;

        return $this->queryPublic("Ticker", $input);

    }

    /**
     * Returns an array of the pair names and their ticker info.
     * @param string|array $pair: comma delimited list of asset pairs to get info on ( default: "XBTCZEUR" )
     * @param integer $interval: 1 ( default ) | 5 | 15 | 30 | 60 | 240 | 1440 | 10080 | 21600
     * @param integer $since: id of the last before requested OHLC-data
     * 
     * @return array server response | error message
     * 
     * @example getOHLCData("XREPZEUR, XREPXXBT", 240)
     */
    public function getOHLCData ($pair = "XBTCZEUR", $interval = 1, $since = "") {
        $input = array ();

        $input["interval"] = $interval;

        if ($since != "") {
            $input["since"] = $since;
        }
        // Add param info if there is a none default one
        if (is_array($pair)) {
            $pair = implode(",", $pair);
        }
        $input["pair"] = $pair;

        return $this->queryPublic("OHLC", $input);
    }

    /**
     * Returns an array of the orders from the pair.
     * @param string $pair: asset pair to get info on ( default: "XBTCZEUR" )
     * @param integer $count: numbers of orders ( optional )
     * 
     * @return array server response | error message
     * 
     * @example getOrderBook("XREPZEUR")
     */
    public function getOrderBook ($pair = "XBTCZEUR", $count = "") {
        $input = array ();


        if ($count != "") {
            $input["count"] = $count;
        }
        // Add param info if there is a none default one
        if (is_array($pair)) {
            $pair = implode(",", $pair);
        }
        $input["pair"] = $pair;

        return $this->queryPublic("Depth", $input);
    }

    /**
     * Returns an array of the recent trades from the pair.
     * @param string $pair: asset pair to get info on ( default: "XBTCZEUR" )
     * @param integer $since: id of the last before requested trade-data
     * 
     * @return array server response | error message
     * 
     * @example getRecentTrades("XREPZEUR")
     */
    public function getRecentTrades ($pair = "XBTCZEUR", $since = "") {
        $input = array ();


        if ($since != "") {
            $input["since"] = $since;
        }
        // Add param info if there is a none default one
        if (is_array($pair)) {
            $pair = implode(",", $pair);
        }
        $input["pair"] = $pair;

        return $this->queryPublic("Trades", $input);
    }

    /**
     * Returns an array of the recent trades from the pair.
     * @param string $pair: asset pair to get info on ( default: "XBTCZEUR" )
     * @param integer $since: id of the last before requested trade-data
     * 
     * @return array server response | error message
     * 
     * @example getRecentTrades("XREPZEUR")
     */
    public function getRecentSpreadData ($pair = "XBTCZEUR", $since = "") {
        $input = array ();


        if ($since != "") {
            $input["since"] = $since;
        }
        // Add param info if there is a none default one
        if (is_array($pair)) {
            $pair = implode(",", $pair);
        }
        $input["pair"] = $pair;

        return $this->queryPublic("Spread", $input);
    }

    /**
     * ##############################################################
     * ################## Private API functions #####################
     * ##############################################################
     */

    /**
     * Returns an array of asset names and balance amount.
     * 
     * @return array server response | error message
     * 
     * @example getAccountBalance()
     */
    public function getAccountBalance () {

        return $this->queryPrivate("Balance");
    }

    /**
     * Returns an array of the trade balance info
     * 
     * @param string $asset: base asset to determine balance ( default: "ZUSD" )
     * @param integer $aclass: asset class ( default: "currency" ) 
     * 
     * @return array server response | error message
     * 
     * @example getTradeBalance("ZEUR")
     */
    public function getTradeBalance ($asset = "ZUSD", $aclass = "currency") {

        $input = array ("asset" => $asset, "aclass" => $aclass);

        return $this->queryPrivate("TradeBalance", $input);
    }

    /**
     * Returns an array of the info of open orders with the txid as the keys
     * 
     * @param string $userref: restrict orders to given user refenrence id ( optional )
     * @param boolean $trades: include trades within the open orders ( default: false )
     * 
     * @return array server response | error message
     * 
     * @example getOpenOrders()
     */
    public function getOpenOrders ($userref = "", $trades = false) {

        $input = array ("trades" => $trades);

        if ($userref != "") {
            $input["userref"] = $userref;
        }

        return $this->queryPrivate("OpenOrders", $input);
    }

    /**
     * Returns an array of the info of closed orders
     * 
     * @param string $userref: restrict orders to given user refenrence id ( optional )
     * @param boolean $trades: include trades within the open orders ( default: false )
     * @param integer $start: starting unix timestamp or order tx id of results ( optional )
     * @param integer $end: ending unix timestamp or order tx id of results ( optional )
     * @param integer $ofs: result offset ( optional )
     * @param string $closetime: both ( default ) | open | close 
     * 
     * @return array server response | error message
     * 
     * @example getClosedOrders("ZEUR")
     */
    public function getClosedOrders ($userref = "", $trades = false, $start = -1, $end = -1, $ofs = -1, $closetime = "both") {

        $input = array ("trades" => $trades, "closetime" => $closetime);

        if ($userref != "") {
            $input["userref"] = $userref;
        }

        if ($start > -1) {
            $input["start"] = $start;
        }

        if ($end > -1) {
            $input["end"] = $end;
        }

        if ($ofs > -1) {
            $input["ofs"] = $ofs;
        }

        return $this->queryPrivate("ClosedOrders", $input);
    }

    /**
     * Returns an associative array of order infos
     * 
     * @param string $userref: restrict orders to given user refenrence id ( optional )
     * @param boolean $trades: include trades within the open orders ( default: false )
     * @param string|array $txid: list of transactiond ids to query info of ( max. 20 )
     * 
     * @return array server response | error message
     * 
     * @example queryOrdersInfo("ABCDE-ABCDE-ABCDEF")
     */
    public function queryOrdersInfo ($txid = "", $userref = "", $trades = false) {

        $input = array ("trades" => $trades);

        if (is_array($txid)) {
            $txid = implode(",", $txid);
            $input["txid"] = $txid;
        } else if ($txid != "") {
            $input["txid"] = $txid;
        }

        if ($userref != "") {
            $input["userref"] = $userref;
        }

        return $this->queryPrivate("QueryOrders", $input);
    }

    /**
     * Returns an array of the trde info
     * 
     * @param string $type: all ( default ) | any position | closed position | closing position | no position 
     * @param boolean $trades: include trades within the open orders ( default: false )
     * @param integer $start: starting unix timestamp or order tx id of results ( optional )
     * @param integer $end: ending unix timestamp or order tx id of results ( optional )
     * @param integer $ofs: result offset ( optional )
     * 
     * @return array server response | error message
     * 
     * @example getTradeHistory()
     */
    public function getTradeHistory ($type = "all", $trades = false, $start = -1, $end = -1, $ofs = -1) {

        $input = array ("trades" => $trades);

        if ($type != "all") {
            $input["type"] = $type;
        }

        if ($start > -1) {
            $input["start"] = $start;
        }

        if ($end > -1) {
            $input["end"] = $end;
        }

        if ($ofs > -1) {
            $input["ofs"] = $ofs;
        }

        return $this->queryPrivate("TradeHistory", $input);
    }

    /**
     * Returns an associative array of order infos
     * 
     * @param boolean $trades: include trades within the open orders ( default: false )
     * @param string|array $txid: list of transactiond ids to query info of ( max. 20 )
     * 
     * @return array server response | error message
     * 
     * @example queryTradesInfo("ABCDE-ABCDE-ABCDEF")
     */
    public function queryTradesInfo ($txid = "", $trades = false) {

        $input = array ("trades" => $trades);

        if (is_array($txid)) {
            $txid = implode(",", $txid);
            $input["txid"] = $txid;
        } else if ($txid != "") {
            $input["txid"] = $txid;
        }

        return $this->queryPrivate("QueryTrades", $input);
    }

    /**
     * Returns an associative array of open position info
     * 
     * @param boolean $docalcs: whether or not to include profit/loss calculations ( default: false )
     * @param string|array $txid: list of transactiond ids to query info of ( max. 20 )
     * 
     * @return array server response | error message
     * 
     * @example getOpenPositions("ABCDE-ABCDE-ABCDEF")
     */
    public function getOpenPositions ($txid = "", $docalcs = false) {

        $input = array ("docalcs" => $docalcs);

        if (is_array($txid)) {
            $txid = implode(",", $txid);
            $input["txid"] = $txid;
        } else if ($txid != "") {
            $input["txid"] = $txid;
        }

        return $this->queryPrivate("OpenPositions", $input);
    }

    /**
     * Returns an associative array of ledgers info
     * 
     * @param string|array $asset: comma delimited list of assets to restrict output to ( default: all )
     * @param integer $aclass: asset class ( default: "currency" ) 
     * @param string $type: all ( default ) | deposit | withdrawal | trade | margin 
     * @param integer $start: starting unix timestamp or order tx id of results ( optional )
     * @param integer $end: ending unix timestamp or order tx id of results ( optional )
     * @param integer $ofs: result offset ( optional )
     * 
     * @return array server response | error message
     * 
     * @example getLedgersInfo()
     */
    public function getLedgersInfo ($asset = "all", $type = "all", $aclass = "currency", $start = -1, $end = -1, $ofs = -1) {

        $input = array ("aclass" => $aclass);

        if ($type != "all") {
            $input["type"] = $type;
        }

        if (is_array($asset)) {
            $asset = implode(",", $asset);
            $input["asset"] = $asset;
        } else if ($asset != "all") {
            $input["asset"] = $asset;
        }

        if ($start > -1) {
            $input["start"] = $start;
        }

        if ($end > -1) {
            $input["end"] = $end;
        }

        if ($ofs > -1) {
            $input["ofs"] = $ofs;
        }

        return $this->queryPrivate("Ledgers", $input);
    }

    /**
     * Returns an associative array of ledgers info
     * 
     * @param string|array $id: list of ledger ids to query info about ( max. 20 )
     * 
     * @return array server response | error message
     * 
     * @example queryLedgersInfo("ABCDE-ABCDE-ABCDEF")
     */
    public function queryLedgersInfo ($id = "") {

        $input = array ();

        if (is_array($id)) {
            $id = implode(",", $id);
            $input["id"] = $id;
        } else if ($id != "") {
            $input["id"] = $id;
        }

        return $this->queryPrivate("QueryLedgers", $input);
    }

    /**
     * Returns an associative array of pairs informations
     * 
     * @param string|array $pair: list of pairs to query get trade volume of ( max. 20 )
     * @param boolean $fee: whether or not to include fee info in results ( default: true )
     * 
     * @return array server response | error message
     * 
     * @example getTradeVolume("XREPZEUR")
     */
    public function getTradeVolume ($pair = "", $fee = true) { 
        $input = array ("fee-info" => $fee);

        if (is_array($pair)) {
            $pair = implode(",", $pair);
            $input["pair"] = $pair;
        } else if ($pair != "") {
            $input["pair"] = $pair;
        }

        return $this->queryPrivate("TradeVolume", $input);
    }


    /**
     * Registers an Order on the Kraken Platform
     * 
     * @param string $pair: the pair you want to trade
     * @param string $type: the type of trade you want to make ( buy|sell )
     * @param string $ordertype: the type of order you want to make, one of the following:
     *                                    market (default)
     *                                    limit (price = limit price)
     *                                    stop-loss (price = stop loss price)
     *                                    take-profit (price = take profit price)
     *                                    stop-loss-profit (price = stop loss price, price2 = take profit price)
     *                                    stop-loss-profit-limit (price = stop loss price, price2 = take profit price)
     *                                    stop-loss-limit (price = stop loss trigger price, price2 = triggered limit price)
     *                                    take-profit-limit (price = take profit trigger price, price2 = triggered limit price)
     *                                    trailing-stop (price = trailing stop offset)
     *                                    trailing-stop-limit (price = trailing stop offset, price2 = triggered limit offset)
     *                                    stop-loss-and-limit (price = stop loss price, price2 = limit price)
     *                                    settle-position
     * @param float  price = price (optional.  dependent upon ordertype)
     * @param float price2 = secondary price (optional.  dependent upon ordertype)
     * @param float volume = order volume in lots
     * @param float leverage = amount of leverage desired (optional.  default = none)
     * @param string oflags = comma delimited list of order flags (optional):
     *                     viqc = volume in quote currency (not available for leveraged orders)
     *                     fcib = prefer fee in base currency
     *                     fciq = prefer fee in quote currency
     *                     nompp = no market price protection
     *                     post = post only order (available when ordertype = limit)
     * @param string starttm = scheduled start time (optional):
     *                     0 = now (default)
     *                     +<n> = schedule start time <n> seconds from now
     *                     <n> = unix timestamp of start time
     * @param string expiretm = expiration time (optional):
     *                     0 = no expiration (default)
     *                     +<n> = expire <n> seconds from now
     *                     <n> = unix timestamp of expiration time
     * @param string userref = user reference id.  32-bit signed number.  (optional)
     * @param string validate = validate inputs only.  do not submit order (optional)
     * 
     * @return array server response | error message
     * 
     * @example addOrder("XREPZEUR", "buy")
     */
    public function addOrder ($pair, $type, $orderType = "market", 
                               $price = -1, $price2 = -1, $volume = -1, 
                               $leverage = -1, $oflags = "", $starttm = "", 
                               $expiretm = "", $userref = "", $validate = "") { 
        $input = array ("pair" => $pair, "type" => $type, "orderType" => $orderType);

        if ($price > 0) {
            $input["price"] = $price;
        }

        if ($price2 > 0) {
            $input["price2"] = $price2;
        }

        if ($volume > 0) {
            $input["volume"] = $volume;
        }

        if ($leverage != "") {
            $input["leverage"] = $leverage;
        }

        if ($oflags != "") {
            $input["oflags"] = $oflags;
        }

        if ($starttm != "") {
            $input["starttm"] = $starttm;
        }

        if ($expiretm != "") {
            $input["expiretm"] = $expiretm;
        }

        if ($userref != "") {
            $input["userref"] = $userref;
        }

        if ($validate != "") {
            $input["validate"] = $validate;
        }

        return $this->queryPrivate("AddOrder", $input);
    }


    /**
     * Cancels an order on the Kraken Platform
     * 
     * @param string txid: ID of the Order
     * 
     * @return array server response | error message
     * 
     * @example cancelOrder("ABCDE-ABCDE-ABCDEF")
     */
    public function cancelOrder ($txid) { 
        $input = array ("txid" => $txid);

        return $this->queryPrivate("cancelOrder", $input);
    }

    /**
     * ##############################################################
     * ################ Private Helper Functions ####################
     * ##############################################################
     */

    /**
     * Query public methods
     *
     * @param string $method method name
     * @param array $request request parameters
     * @return array request result on success
     * @throws KrakenAPIException
     */
    protected function QueryPublic ($method, array $request = array()) {

        // build the POST data string
        $postdata = http_build_query($request, '', '&');
        // make request
        curl_setopt($this->curl, CURLOPT_URL, $this->url . '/' . $this->version . '/public/' . $method);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array());
        $result = curl_exec($this->curl);

        if ($result===false)
            throw new KrakenAPIException('CURL error: ' . curl_error($this->curl));

        // decode results
        $result = json_decode($result, true);
        if (!is_array($result))
            throw new KrakenAPIException('JSON decode error');
        return $result;
    }
    /**
     * Query private methods
     *
     * @param string $method method path
     * @param array $request request parameters
     * @return array request result on success
     * @throws KrakenAPIException
     */
    protected function QueryPrivate ($method, array $request = array()) {
        
        if (!isset($request['nonce'])) {
            // generate a 64 bit nonce using a timestamp at microsecond resolution
            // string functions are used to avoid problems on 32 bit systems
            $nonce = explode(' ', microtime());
            $request['nonce'] = $nonce[1] . str_pad(substr($nonce[0], 2, 6), 6, '0');
        }

        // build the POST data string
        $postdata = http_build_query($request, '', '&');
        // set API key and sign the message
        $path = '/' . $this->version . '/private/' . $method;
        $sign = hash_hmac('sha512', $path . hash('sha256', $request['nonce'] . $postdata, true), base64_decode($this->secret), true);
        $headers = array(
            'API-Key: ' . $this->key,
            'API-Sign: ' . base64_encode($sign)
        );
        // make request
        curl_setopt($this->curl, CURLOPT_URL, $this->url . $path);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($this->curl);

        if ($result===false)
            throw new KrakenAPIException('CURL error: ' . curl_error($this->curl));
        
        // decode results
        $result = json_decode($result, true);
        if (!is_array($result))
            throw new KrakenAPIException('JSON decode error');
        return $result;
    }


}
