<?php

/**
 * Created by IntelliJ IDEA.
 * User: dan
 * Date: 25/01/18
 * Time: 12:23
 */
class Revenuehits_UrlGenerator
{
    private static $RH_BASE_URL = 'revenuehits.com/publishers/createclient';

    private $options = array(
        "format" => "json"
    );

    public function __construct($pid, $cid)
    {
        $this->options['pid'] = $pid;
        if (!empty($cid))
            $this->options['cid'] = $cid;
    }

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    public function __toString()
    {
        return Revenuehits_UrlGenerator::$RH_BASE_URL . "?" . http_build_query($this->options);
    }
}

class RevenueHits_ExternalTagGenerator
{
    static private $APC_KEY_BASE = "rhapcid_";

    private $cid;

    public function __construct($client_id, $pid)
    {
        $this->cid = $client_id;
        $this->pid = $pid;
    }

    public function getExternalTag()
    {
        try {
            $fromAPC = $this->fromAPC();
            return empty($fromAPC) ? "" : $fromAPC;
        } catch (Exception $e) {
            return "";
        }

    }

    private function fromREMOTE()
    {
        try {
            $external_tag = file_get_contents('http://clksite.com/adServe/extTag?tid=' . $this->cid . '&pid=' . $this->pid);
            return empty($external_tag) ? false : $external_tag;
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * If APC support exists use it
     * @return bool|mixed|string
     */
    private function fromAPC()
    {
        if (function_exists("apc_fetch")) {
            try {
                $key = RevenueHits_ExternalTagGenerator::$APC_KEY_BASE . "_" . $this->cid;
                $contents = apc_fetch($key);
                if (!empty($contents)) {
                    $contents = $this->fromREMOTE();
                    if (!empty($contents)) {
                        apc_store($key, $contents, 3600);
                        return $contents;
                    }
                }
                return $contents;
            } catch (Exception $e) {
                return $this->fromREMOTE();
            }
        }
        return $this->fromREMOTE();
    }
}


if (!isset($GLOBALS['RH_ZONE_TYPES']))
    $GLOBALS['RH_ZONE_TYPES'] = json_decode(file_get_contents(REVENUEHITS__PLUGIN_DIR . DIRECTORY_SEPARATOR . "config.json"));

