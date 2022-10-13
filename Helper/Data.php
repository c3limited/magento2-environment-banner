<?php

namespace C3\EnvironmentBanner\Helper;

use C3\EnvironmentBanner\Model\Colours;
use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $_environments = null;
    protected $_colours2 = null;
    protected $_colours = null;

    /**
     * @param Context $context
     * @param Colours                $colours
     */
    public function __construct(Context $context, Colours $colours)
    {
        parent::__construct($context);
        $this->_colours2 = $colours;
    }

    /**
     * Whether to display, given environment, settings etc.
     *
     * @return bool
     */
    public function isDisplayFrontendBanner()
    {
        //Check that output is enabled, else return false
        if (!$this->isFrontendBannerEnabled()) {
            return false;
        }

        // Check that the given environment is recognised, else false
        $environments = $this->getEnvironments();
        if (!isset($environments[$this->getEnvironment()])) {
            return false;
        }

        // Never display on production or if no background colour set (can be used to indicate production)
        if ($this->getEnvironment() == 'production' || $this->getEnvColours()->getFeBgcolor() === null) {
            return false;
        }

        // We're enabled, in a recognised environment that is not production, so... display!
        return true;
    }

    /**
     * Whether to display, given environment, settings etc.
     *
     * @return bool
     */
    public function isDisplayAdminBanner()
    {
        // Check that output is enabled, else return false
        if (!$this->isChangeAdminColour()) {
            return false;
        }

        // Check that the given environment is recognised, else false
        $environments = $this->getEnvironments();
        if (!isset($environments[$this->getEnvironment()])) {
            return false;
        }

        // Never display if no background colour set (can be used to indicate skipping)
        if ($this->getEnvColours()->getBeColor() === null) {
            return false;
        }

        // We're enabled, in a recognised environment, so... display!
        return true;
    }

    /**
     * Get environments array, indexed by environment code
     *
     * @return array
     */
    protected function getEnvironments()
    {
        // Lazily load environments from config
        if ($this->_environments === null) {
            //$envConfig = unserialize(Mage::getStoreConfig("{$this->_configPrefix}/environments/environments"));
            $envConfig = json_decode($this->scopeConfig->getValue('environmentbanner/environments/environments', \Magento\Store\Model\ScopeInterface::SCOPE_STORE), true) ?: [];

            // Make into associative array
            $this->_environments = array();
            foreach ($envConfig as $env) {
                $this->_environments[$env['env']] = $env;
            }
        }

        return $this->_environments;
    }

    /**
     * Get colours set for current environment
     *
     * @return C3_EnvironmentBanner_Model_Colours|null Null if cannot find colours for current environment
     */
    public function getEnvColours()
    {
        // Lazily load colours from environment data
        if ($this->_colours === null) {
            $environments = $this->getEnvironments();
            if (!isset($environments[$this->getEnvironment()])) {
                return null;
            }
            $data = $environments[$this->getEnvironment()];
            //$this->_colours = Mage::getModel('c3_environmentbanner/colours')
              //  ->setData($data);
            $this->_colours = $this->_colours2;
            $this->_colours->setData($data);

        }

        return $this->_colours;
    }

    /**
     * Whether the display-banner functionality is turned on
     *
     * @return bool
     */
    public function isFrontendBannerEnabled()
    {
        return ($this->scopeConfig->getValue('environmentbanner/frontend/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == true);
    }

    /**
     * Whether to change the colour of the admin banner according to the environment
     *
     * @return bool
     */
    public function isDisplayEnvName() {
        return ($this->scopeConfig->getValue('environmentbanner/admin/display_env', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == true);
    }

    public function isChangeAdminColour()
    {
        return ($this->scopeConfig->getValue('environmentbanner/admin/colour_change') == true);
    }

    /**
     * Whether to display the name of the environment in admin
     *
     * @return bool
     */
    public function isDisplayAdminEnv()
    {
        return ($this->scopeConfig->getValue('environmentbanner/admin/display_env') == true);
    }

    /**
     * Filename of the admin logo - defaults to 'logo.gif'.
     *
     * @return string
     */
    public function getAdminLogoFilename() {
        return ($this->scopeConfig->getValue('environmentbanner/admin/logo_filename') == true);
    }

    /**
     * Return the current application environment. If not set, return null
     *
     * @return null|string
     */
    public function getEnvironment()
    {
        if (!isset($_SERVER['APPLICATION_ENV'])) {
            return null;
        }

        return $_SERVER['APPLICATION_ENV'];
    }
}