<?php


namespace QCloudSDK\CDN;



use Tightenco\Collect\Support\Collection;

class Refresh extends API
{
    protected function getQueryResult(Collection $data)
    {
        return $this->expectResult('data.logs', $data, 'Expected payload in path data.logs.');
    }

    public function queryById(int $taskId)
    {
        return $this->getQueryResult($this->getCdnRefreshLog(compact('taskId')));
    }

    public function queryByUrl(string $url)
    {
        return $this->getQueryResult($this->getCdnRefreshLog(compact('url')));
    }

    public function queryByDate($startDate, $endDate)
    {
        return $this->getQueryResult($this->getCdnRefreshLog(compact('startDate', 'endDate')));
    }

    protected function getTaskId(Collection $data)
    {
        return $this->expectResult('data', $data);
    }

    public function ensureRefreshUrls($urls)
    {
        return $this->getTaskId($this->refreshCdnUrl($urls));
    }

    public function ensureRefreshDirs($dirs)
    {
        return $this->getTaskId($this->refreshCdnDir($dirs));
    }

}