<?php


namespace QCloudSDK\CDN;


use QCloudSDK\Core\AbstractAPI;
use QCloudSDK\Core\ActionTrait;
use QCloudSDK\Core\ArrayParamTrait;
use QCloudSDK\Core\CommonConfiguration;
use QCloudSDK\Core\DateTimeTrait;
use QCloudSDK\Core\GeneralSignatureTrait;
use QCloudSDK\Core\JsonParamTrait;
use QCloudSDK\Core\OnOffParamTrait;
use Tightenco\Collect\Support\Arr;

class API extends AbstractAPI
{

    const QUERY_PROJECT = 'projects';
    const QUERY_HOST = 'hosts';
    const QUERY_ID = 'taskId';
    const QUERY_URL = 'url';
    const QUERY_START = 'startDate';
    const QUERY_END = 'endDate';
    const QUERY_TYPE = 'statType';

    const HOST_CNAME = 'cname';
    const HOST_FTP = 'ftp';

    const STAT_FLUX = 'flux';
    const STAT_BANDWIDTH = 'bandwidth';
    const STAT_REQUEST = 'requests';
    const STAT_VISIT = 'ip_visits';
    const STAT_CACHE = 'cache';

    const CONFIG_CACHE = 'cache';
    const CONFIG_CACHE_MODE = 'cacheMode';
    const CONFIG_REFER = 'refer';
    const CONFIG_FWD_HOST = 'fwdHost';
    const CONFIG_FULL_URL = 'fullUrl';
    const CONFIG_ORIGIN = 'origin';

    const CACHE_MODE_SIMPLE = 'simple';
    const CACHE_MODE_CUSTOM = 'custom';

    use GeneralSignatureTrait;
    use ActionTrait;
    use ArrayParamTrait;
    use DateTimeTrait;
    use JsonParamTrait;
    use OnOffParamTrait;

    protected function request(array $params)
    {
        return $this->parseJSONSigned('post',
            Arr::get($this->config, CommonConfiguration::CONFIG_API_URL, 'cdn.api.qcloud.com/v2/index.php'), $params);
    }

    protected function makePeriodParam(int $period)
    {
        if($period < 5) return [];
        return compact('period');
    }

    protected function makeOriginParam($origin)
    {
        if(is_string($origin)) return compact('origin');
        if(is_array($origin)) return ['origin' => join(';', $origin)];
        throw new \InvalidArgumentException('Origin param must be either a domain or an array of IP[:Port].');
    }

    /**
     * @link https://www.qcloud.com/document/api/228/1406
     * @param string $host
     * @param int $projectId
     * @param string $type
     * @param string|array|null $origin
     * @return \Tightenco\Collect\Support\Collection
     */
    public function addCdnHost(string $host, int $projectId, string $type, $origin = null)
    {
        $params = $this->createAction(__FUNCTION__);
        $params += compact('host', 'projectId', 'type');
        if(isset($origin)) $params += $this->makeOriginParam($origin);
        return $this->request($params);
    }

    /**
     * @link https://www.qcloud.com/document/api/228/1402
     * @param int $hostId
     * @return \Tightenco\Collect\Support\Collection
     */
    public function onlineHost(int $hostId)
    {
        return $this->request($this->createAction(__FUNCTION__) + compact('hostId'));
    }

    /**
     * @link https://www.qcloud.com/document/api/228/1403
     * @param int $hostId
     * @return \Tightenco\Collect\Support\Collection
     */
    public function offlineHost(int $hostId)
    {
        return $this->request($this->createAction(__FUNCTION__) + compact('hostId'));
    }

    /**
     * @link https://www.qcloud.com/document/api/228/1396
     * @param int $hostId
     * @return \Tightenco\Collect\Support\Collection
     */
    public function deleteCdnHost(int $hostId)
    {
        return $this->request($this->createAction(__FUNCTION__) + compact('hostId'));
    }

    /**
     * @link https://www.qcloud.com/document/api/228/1397
     * @param int $hostId
     * @param string $host
     * @param string|array $origin
     * @return \Tightenco\Collect\Support\Collection
     */
    public function updateCdnHost(int $hostId, string $host, $origin)
    {
        $params = $this->createAction(__FUNCTION__) + compact('host', 'hostId') + $this->makeOriginParam($origin);
        return $this->request($params);
    }

    /**
     * @link https://www.qcloud.com/document/api/228/3933
     * @param int $hostId
     * @param int $projectId
     * @param array $params Detailed params
     * @return \Tightenco\Collect\Support\Collection
     */
    public function updateCdnConfig(int $hostId, int $projectId, array $params)
    {
        $params += $this->createAction(__FUNCTION__) + compact('hostId');
        if(isset($projectId)) $params += compact('projectId');
        $this->ensureJsonParam($params, static::CONFIG_CACHE);
        $this->ensureJsonParam($params, static::CONFIG_REFER);
        $this->ensureOnOffParam($params, static::CONFIG_FULL_URL);
        return $this->request($params);
    }

    /**
     * @link https://www.qcloud.com/document/api/228/3934
     * @param int $hostId
     * @param $cache
     * @return \Tightenco\Collect\Support\Collection
     */
    public function updateCache(int $hostId, $cache)
    {
        $params = $this->createAction(__FUNCTION__) + compact('hostId', 'cache');
        $this->ensureJsonParam($params, self::CONFIG_CACHE);
        return $this->request($params);
    }

    /**
     * @link https://www.qcloud.com/document/api/228/3935
     * @param int $hostId
     * @param int $projectId
     * @return \Tightenco\Collect\Support\Collection
     */
    public function updateCdnProject(int $hostId, int $projectId)
    {
        return $this->request($this->createAction(__FUNCTION__) + compact('hostId', 'projectId'));
    }

    /**
     * @link https://www.qcloud.com/document/api/228/3939
     * @param array|int $ids
     * @return \Tightenco\Collect\Support\Collection
     */
    public function getHostInfoById($ids)
    {
        return $this->request($this->createAction(__FUNCTION__) + $this->makeArrayParam('ids', $ids));
    }

    /**
     * @link https://www.qcloud.com/document/api/228/3938
     * @param array|string $hosts
     * @return \Tightenco\Collect\Support\Collection
     */
    public function getHostInfoByHost($hosts)
    {
        return $this->request($this->createAction(__FUNCTION__) + $this->makeArrayParam('hosts', $hosts));
    }

    /**
     * @link https://www.qcloud.com/document/api/228/3937
     * @param int|null $offset
     * @param int|null $limit
     * @return \Tightenco\Collect\Support\Collection
     */
    public function describeCdnHosts(int $offset = null, int $limit = null)
    {
        $params = $this->createAction(__FUNCTION__);
        if(isset($offset)) $params += compact('offset');
        if(isset($limit)) $params += compact('limit');
        return $this->request($params);
    }

    /**
     * @link https://www.qcloud.com/document/api/228/3943
     * @param string|int|\DateTimeInterface $startDate
     * @param string|int|\DateTimeInterface $endDate
     * @param array $projects
     * @param array|null $hosts
     * @param int|null $period
     * @return \Tightenco\Collect\Support\Collection
     */
    public function getCdnStatusCode($startDate, $endDate, array $projects, array $hosts = null, int $period = null)
    {
        $params = $this->createAction(__FUNCTION__);
        $params[self::QUERY_START] = $this->makeDateParam($startDate);
        $params[self::QUERY_END] = $this->makeDateParam($endDate);
        $params += $this->makeArrayParam(self::QUERY_PROJECT, $projects);
        if(isset($hosts)) $params += $this->makeArrayParam(self::QUERY_HOST, $hosts);
        if(isset($period)) $params += $this->makePeriodParam($period);
        return $this->request($params);
    }

    /**
     * @link https://www.qcloud.com/document/api/228/3944
     * @param string|int|\DateTimeInterface $startDate
     * @param string|int|\DateTimeInterface $endDate
     * @param string $statType
     * @param array $projects
     * @param array|null $hosts
     * @param int|null $period
     * @return \Tightenco\Collect\Support\Collection
     */
    public function GetCdnStatTop(
        $startDate,
        $endDate,
        string $statType,
        array $projects,
        array $hosts = null,
        int $period = null
    ) {
        $params = $this->createAction(__FUNCTION__);
        $params[self::QUERY_START] = $this->makeDateParam($startDate);
        $params[self::QUERY_END] = $this->makeDateParam($endDate);
        $params[self::QUERY_TYPE] = $statType;
        $params += $this->makeArrayParam(self::QUERY_PROJECT, $projects);
        if(isset($hosts)) $params += $this->makeArrayParam(self::QUERY_HOST, $hosts);
        if(isset($period)) $params += $this->makePeriodParam($period);
        return $this->request($params);
    }

    /**
     * @link https://www.qcloud.com/document/api/228/3941
     * @param string|int|\DateTimeInterface $startDate
     * @param string|int|\DateTimeInterface $endDate
     * @param string $statType
     * @param array $projects
     * @param array|null $hosts
     * @return \Tightenco\Collect\Support\Collection
     */
    public function describeCdnHostInfo($startDate, $endDate, string $statType, array $projects, array $hosts = null)
    {
        $params = $this->createAction(__FUNCTION__);
        $params[self::QUERY_START] = $this->makeDateParam($startDate);
        $params[self::QUERY_END] = $this->makeDateParam($endDate);
        $params[self::QUERY_TYPE] = $statType;
        $params += $this->makeArrayParam(self::QUERY_PROJECT, $projects);
        if(isset($hosts)) $params += $this->makeArrayParam(self::QUERY_HOST, $hosts);
        return $this->request($params);
    }

    /**
     * @link https://www.qcloud.com/document/api/228/3942
     * @param string|int|\DateTimeInterface $startDate
     * @param string|int|\DateTimeInterface $endDate
     * @param string $statType
     * @param array $projects
     * @param array|null $hosts
     * @return \Tightenco\Collect\Support\Collection
     */
    public function describeCdnHostDetailedInfo($startDate, $endDate, string $statType, array $projects, array $hosts = null)
    {
        $params = $this->createAction(__FUNCTION__);
        $params[self::QUERY_START] = $this->makeDateParam($startDate);
        $params[self::QUERY_END] = $this->makeDateParam($endDate);
        $params[self::QUERY_TYPE] = $statType;
        $params += $this->makeArrayParam(self::QUERY_PROJECT, $projects);
        if(isset($hosts)) $params += $this->makeArrayParam(self::QUERY_HOST, $hosts);
        return $this->request($params);
    }

    /**
     * @link https://www.qcloud.com/document/api/228/3948
     * @param array $conditions
     * @return \Tightenco\Collect\Support\Collection
     */
    public function getCdnRefreshLog(array $conditions)
    {
        $invalid = true;
        if(isset($conditions[self::QUERY_START], $conditions[self::QUERY_END])){
            $conditions[self::QUERY_START] = $this->makeDateTimeParam($conditions[self::QUERY_START]);
            $conditions[self::QUERY_END] = $this->makeDateTimeParam($conditions[self::QUERY_END]);
            $invalid = false;
        }
        if(isset($conditions[self::QUERY_ID])){
            if(is_numeric($id = &$conditions[self::QUERY_ID]) && $id > 0){
                $id = intval($id);
                $invalid = false;
            }else{
                unset($conditions[self::QUERY_ID]);
            }
        }
        if(isset($conditions[self::QUERY_URL])){
            if(filter_var($conditions[self::QUERY_URL], FILTER_VALIDATE_URL)){
                $invalid = false;
            }else{
                unset($conditions[self::QUERY_URL]);
            }
        }
        if($invalid) throw new \InvalidArgumentException('Query condition must contain either date range or task ID.');
        return $this->request($this->createAction('GetCdnRefreshLog') + $conditions);
    }

    protected function assertUrl($url)
    {
        if(substr($url, 0, 4) !== 'http') throw new \InvalidArgumentException('You must specify scheme http or https in every URL.');
    }

    /**
     * @link https://www.qcloud.com/document/api/228/3946
     * @param string|array $urls
     * @return \Tightenco\Collect\Support\Collection
     */
    public function refreshCdnUrl($urls)
    {
        return $this->request($this->createAction(__FUNCTION__) + $this->makeArrayParam('urls', $urls, [$this, 'assertUrl']));
    }

    /**
     * @link https://www.qcloud.com/document/api/228/3947
     * @param string|array $dirs
     * @return \Tightenco\Collect\Support\Collection
     */
    public function refreshCdnDir($dirs)
    {
        return $this->request($this->createAction(__FUNCTION__) + $this->makeArrayParam('dirs', $dirs, function(&$dir){
            $this->assertUrl($dir);
            if(substr($dir, -1) !== '/') $dir .= '/'; // TODO $dir[-1] for PHP >=7.1
        }));
    }

    /**
     * @link https://www.qcloud.com/document/api/228/8087
     * @param string $host
     * @param string|int|\DateTimeInterface|null $startDate
     * @param string|int|\DateTimeInterface|null $endDate
     * @return \Tightenco\Collect\Support\Collection
     */
    public function getCdnLogList(string $host, $startDate = null, $endDate = null)
    {
        $params = $this->createAction(__FUNCTION__) + compact('host');
        if(isset($startDate, $endDate)){
            $startDate = $this->makeDateTimeParam($startDate);
            $endDate = $this->makeDateTimeParam($endDate);
            $params += compact('startDate', 'endDate');
        }
        return $this->request($params);
    }

}