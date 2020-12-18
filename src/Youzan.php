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
    protected $appSource;

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
        $this->appSource = $config['app_source'];
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
     * 获取店铺客户列表
     *
     * @param int $page
     * @param int $pageSize
     * @param int|null $createdAtStart
     * @return mixed
     */
    public function getUsers(int $createdAtStart = null, int $page = 1, int $pageSize = 50)
    {
        $this->getAccessToken();
        $client = new Client($this->accessToken);

        $method = 'youzan.scrm.customer.search';
        $apiVersion = '3.1.2';

        $params = [
            "page" => $page,
            "page_size" => $pageSize
        ];

        if (!is_null($createdAtStart)) $params["created_at_start"] = $createdAtStart;

        return $client->post($method, $apiVersion, $params);
    }

    /**
     * 获取用户有赞openid
     *
     * @param int $userId
     * @return mixed
     */
    public function getUserOpenid(int $userId)
    {
        $this->getAccessToken();
        $client = new Client($this->accessToken);

        $method = 'youzan.user.openid.get';
        $apiVersion = '1.0.0';

        $params = ['user_id' => $userId];

        return $client->post($method, $apiVersion, $params);
    }

    /**
     * 获取用户的微信 openid 和 unionid
     *
     * @param string $yzOpenid
     * @return mixed
     */
    public function getWeixinOpenid(string $yzOpenid)
    {
        $this->getAccessToken();
        $client = new Client($this->accessToken);

        $method = 'youzan.user.weixin.openid.get';
        $apiVersion = '3.0.0';

        $params = ['yz_open_id' => $yzOpenid];

        return $client->post($method, $apiVersion, $params);
    }

    /**
     * 获取客户详情
     *
     * @param string $yzOpenid
     * @return mixed
     */
    public function getUserInfo(string $yzOpenid)
    {
        $this->getAccessToken();
        $client = new Client($this->accessToken);

        $method = 'youzan.scrm.customer.detail.get';
        $apiVersion = '1.0.1';

        $params = [
            'yz_open_id' => $yzOpenid,
            "fields" => "user_base,behavior,prepaid",
        ];

        return $client->post($method, $apiVersion, $params);
    }

    /**
     * 获取用户简要信息
     *
     * @param string $userId
     * @return mixed
     */
    public function getUserBasic(string $userId)
    {
        $this->getAccessToken();
        $client = new Client($this->accessToken);

        $method = 'youzan.user.basic.get';
        $apiVersion = '3.0.1';

        $params = ['yz_open_id' => $userId];

        return $client->post($method, $apiVersion, $params);
    }

    /* =====================================
     * =========== 优惠券/码相关 =============
     ==================================== */

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
            'app_source' => $this->appSource,
            'activity_id' => $activityId,
            'page_num' => $page,
            'page_size' => $pageSize
        ];

        return $client->post($method, $apiVersion, $params);
    }

    /**
     * 获取优惠码详情
     *
     * @param string $id
     * @return mixed
     */
    public function getCouponCodeDetail(string $id)
    {
        $this->getAccessToken();
        $client = new Client($this->accessToken);

        $method = 'youzan.ump.promocode.detail.get';
        $apiVersion = '3.0.1';

        $params = ['id' => $id];

        return $client->post($method, $apiVersion, $params);
    }

    /**
     * 获取优惠码领取日志
     *
     * @param string $groupId
     * @param string|null $startAt
     * @param int $page
     * @return mixed
     */
    public function getCouponCodeLogs(string $groupId, string $startAt = null, int $page = 1)
    {
        $this->getAccessToken();
        $client = new Client($this->accessToken);

        $method = 'youzan.ump.coupon.consume.fetchlogs.get';
        $apiVersion = '3.0.1';

        // 设置参数
        $params = [
            "page_no" => $page,
            "coupon_group_id" => $groupId,
            "page_size" => 50
        ];

        if (!is_null($startAt)) $params['start_taked'] = $startAt;

        return $client->post($method, $apiVersion, $params);
    }

    /**
     * 核销码获取优惠码
     *
     * @param string $codeValue
     * @return mixed
     */
    public function getCouponCodeConsume(string $codeValue)
    {
        $this->getAccessToken();
        $client = new Client($this->accessToken);

        $method = 'youzan.ump.coupon.consume.get';
        $apiVersion = '3.0.0';

        $params = ['code' => $codeValue];

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

    /* =====================================
     * =========== 销售管理相关 ==============
     ==================================== */

    /**
     * 获取销售分组列表
     *
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function getSalesmanGroupList(int $page = 1, int $pageSize = 20)
    {
        $this->getAccessToken();
        $client = new Client($this->accessToken);

        $method = 'youzan.salesman.groups.get';
        $apiVersion = '3.0.0';

        $params = [
            'page_no' => $page,
            'page_size' => $pageSize
        ];

        return $client->post($method, $apiVersion, $params);
    }

    /**
     * 获取销售列表
     *
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function getSalesmanList(int $page = 1, int $pageSize = 20)
    {
        $this->getAccessToken();
        $client = new Client($this->accessToken);

        $method = 'youzan.salesman.accounts.get';
        $apiVersion = '3.0.0';

        $params = [
            'page_no' => $page,
            'page_size' => $pageSize
        ];

        return $client->post($method, $apiVersion, $params);
    }

    /**
     * 获取销售和客户的绑定关系列表
     *
     * @param string $mobile
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function getSalesmanCustomerList(string $mobile, int $page = 1, int $pageSize = 20)
    {
        $this->getAccessToken();
        $client = new Client($this->accessToken);

        $method = 'youzan.salesman.customers.get';
        $apiVersion = '3.0.1';

        $params = [
            'type' => 0,
            'mobile' => $mobile,
            'page_no' => $page,
            'page_size' => $pageSize
        ];

        return $client->post($method, $apiVersion, $params);
    }

    /* =====================================
     * ============== 标签相关 ==============
     ==================================== */

    /**
     * 获取标签列表
     *
     * @param string $keyword
     * @param int $page
     * @param int $pageSize
     * @return mixed
     */
    public function getTags(string $keyword = null, int $page = 1, int $pageSize = 50)
    {
        $params = [
            'page_no' => $page,
            'page_size' => $pageSize
        ];

        if (!is_null($keyword)) $params['tag_name_keyword'] = $keyword;

        return $this->yzResponse(
            'youzan.scrm.tag.search.template',
            '1.0.0',
            $params);
    }

    /**
     * 给用户打标
     *
     * @param string $accountType 帐号类型。
     * 目前支持以下选项（只支持传一种）：
     * FansID：自有粉丝ID，
     * Mobile：手机号，
     * YouZanAccount：有赞账号，
     * OpenUserId：三方自身账号，
     * WeiXinOpenId：微信openId，
     * YzOpenId：有赞OpenId
     * @param string $accountId
     * @param string $tags 签名字符串，多个签名使用英文逗号","分开
     * @return boolean|mixed
     */
    public function setCustomerTags(string $accountType = 'YzOpenId', string $accountId, string $tags)
    {
        // 如果标签为空直接终止
        if (empty($tags)) return false;

        // 字符串格式的标签转为数组格式
        $tagArr = explode(',', $tags);
        $arr = [];
        foreach ($tagArr as $tag) {
            $arr[] = ['tag_name' => $tag];
        }

        $params = [
            'account_type' => 'YzOpenId',
            'account_id' => 'qNiMHPtf642667478442356736',
            'tags' => $arr
        ];

        return $this->yzResponse(
            'youzan.scrm.tag.relation.add',
            '4.0.0',
            $params
        );
    }

    /**
     * 请求有赞平台
     *
     * @param string $method
     * @param string $apiVersion
     * @param array $params
     * @return mixed
     */
    protected function yzResponse(string $method, string $apiVersion, array $params = [])
    {
        $this->getAccessToken();
        $client = new Client($this->accessToken);

        return $client->post($method, $apiVersion, $params);
    }
}