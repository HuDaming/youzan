<?php

namespace Hudm\Youzan;

use Cache;
use Carbon\Carbon;
use Youzan\Open\Client;
use Youzan\Open\Token;

class Youzan
{
    /**
     * @var mixed
     */
    protected $id;

    /**
     * @var mixed
     */
    protected $secret;

    /**
     * 授权店铺ID
     *
     * @var int
     */
    protected $storeId;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * @var string
     */
    public static $cacheKey = 'youzan-access-token';

    public function __construct(array $config = [])
    {
        $this->id = $config['client_id'];
        $this->secret = $config['client_secret'];
        $this->storeId = $config['store_id'];
    }

    /**
     * 获取授权token
     *
     * @return string
     */
    public function getAccessToken()
    {
        $this->accessToken = Cache::remember(self::$cacheKey, 10080, function () {
            $res = (new Token($this->id, $this->secret))->getSelfAppToken($this->storeId);
            return $res['access_token'];
        });

        return $this;
    }

    /**
     * 获取未结束活动列表
     *
     * @return mixed
     */
    public function getCouponActivities()
    {
        $this->getAccessToken();
        $client = new Client($this->accessToken);

        $method = 'youzan.ump.coupons.unfinished.search';
        $apiVersion = '3.0.0';

        $params = [
            "fields" => ""
        ];

        return $client->post($method, $apiVersion, $params);
    }

    /**
     * 获取活动的优惠券/码列表
     *
     * @param string $activityId
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function getCouponCodes(string $activityId, $page = 1, $pageSize = 200)
    {
        $this->getAccessToken();
        $client = new Client($this->accessToken);

        $method = 'youzan.ump.codestock.query';
        $apiVersion = '3.0.0';

        $params = [
            'app_source' => 'COMMUNITY',
            'activity_id' => $activityId,
            'page_num' => $page,
            'page_size' => $pageSize
        ];

        return $client->post($method, $apiVersion, $params);
    }

    /**
     * 按照创建时间获取订单
     *
     * @param string|null $startAt
     * @param string|null $endAt
     */
    public function getOrders(string $startAt = null, string $endAt = null)
    {
        $startAt = $startAt ?: Carbon::yesterday()->toDateTimeString();
        $endAt = $endAt ?: Carbon::today()->toDateTimeString();

        $this->getAccessToken();
        $client = new Client($this->accessToken);

        $method = 'youzan.trades.sold.get';
        $apiVersion = '4.0.1';

        $params = [
            'start_created' => $startAt,
            'end_created' => $endAt
        ];

        return $client->post($method, $apiVersion, $params);
    }
}