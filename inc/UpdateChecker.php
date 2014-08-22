<?php

/**
 * UpdateChecker is library for periodicaly check for software updates
 *
 * @author a-v-k (https://github.com/a-v-k/)
 *
 */
class UpdateChecker {

    private $checkPeriod = 85400; // 86400 (one day) - 1000sec
    private $checkFailPeriod = 600; //seconds to rechceck after failed check
    private $url = 'http://www.not-defined-url.ru/';
    private $status = array();
    private $installId = 'none';
    private $currentReleaseNum = 0;

    public function check() {
        $now = time();
        $this->loadStatus();
        if (($now - $this->status['lastCheck'] > $this->checkPeriod) && ($now - $this->status['lastFailCheck'] > $this->checkFailPeriod)) {
            $this->status['lastFailCheck'] = $now;
            $this->saveStatus();
            $file = null;
            $success = false;
            try {
                $opts = array('http' =>
                    array(
                        'method' => 'GET',
                        'timeout' => 3
                    )
                );
                $context = stream_context_create($opts);

                $fullUrl = $this->url;

                if (strpos($fullUrl, '?') === false) {
                    $fullUrl .= '?install_id=' . $this->installId;
                } else {
                    $fullUrl .= '&install_id=' . $this->installId;
                }
                $fullUrl .= '&rn=' . $this->currentReleaseNum;

                $file = file_get_contents($fullUrl, false, $context);
            } catch (Exception $exc) {
                //throw $exc;
                //echo $exc->getTraceAsString();
            }

            if (strlen($file) > 3) {
                $arr = explode("\n", $file);

                foreach ($arr as $line) {
                    $line = trim($line);
                    if (strpos($line, '=') > 1) {
                        $pair = explode('=', $line);
                        //PRODUCT_VERSION=1.5.3
                        //PRODUCT_RELEASE_NUM=1000
                        if (trim($pair[0]) == 'PRODUCT_VERSION') {
                            $this->status['lastResultVersion'] = trim($pair[1]);
                        }
                        if ((trim($pair[0]) == 'PRODUCT_RELEASE_NUM') && (is_numeric(trim($pair[1])))) {
                            $this->status['lastResultReleaseNum'] = trim($pair[1]);
                            $success = true;
                        }
                    }
                }
                if ($success) {
                    $this->status['lastFailCheck'] = 0;
                    $this->status['lastCheck'] = $now;
                    $this->saveStatus();
                }
            }
        }
    }

    private function loadStatus() {
        if (isset($_SESSION['updateCheckerStatus'])) {
            $this->status = $_SESSION['updateCheckerStatus'];
        }
        //$this->status = array();
        //print_r($this->status);

        if (!isset($this->status['lastCheck'])) {
            $this->status['lastCheck'] = 0;
        }
        if (!isset($this->status['lastFailCheck'])) {
            $this->status['lastFailCheck'] = 0;
        }
        if (!isset($this->status['lastResultVersion'])) {
            $this->status['lastResultVersion'] = 0;
        }
        if (!isset($this->status['lastResultReleaseNum'])) {
            $this->status['lastResultReleaseNum'] = 0;
        }
    }

    private function saveStatus() {
        $_SESSION['updateCheckerStatus'] = $this->status;
    }

    public function getLastVertion() {
        if (empty($this->status)) {
            $this->loadStatus();
        }
        return $this->status['lastResultVersion'];
    }

    public function getLastReleaseNum() {
        if (empty($this->status)) {
            $this->loadStatus();
        }
        return $this->status['lastResultReleaseNum'];
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function setInstallId($installId) {
        $this->installId = $installId;
    }

    public function setCurrentReleaseNum($releaseNum) {
        $this->currentReleaseNum = $releaseNum;
    }

}
