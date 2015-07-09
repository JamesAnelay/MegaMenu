<?php class TheExtensionLab_MegaMenu_Model_Prefetcher
{
    protected $_directiveValues = array();
    protected $_prefetchModels = array();
    protected $_parser = null;

    public function __construct()
    {
        $this->_prepareModels();
    }

    public function prefetch($value)
    {
        $this->_getDirectiveValues($value);
        $this->_prefetchWaitingData();

        return $value;
    }

    private function _getDirectiveValues($value)
    {
        $this->_directiveValues = $this->_parser->getDirectiveValues($value);
    }

    private function _prefetchWaitingData()
    {
        Varien_Profiler::start('megamenu_prefetch_and_store_data');

        foreach ($this->_prefetchModels as $modelAlias) {
            $model = Mage::getModel($modelAlias);
            if (is_callable(array($model, 'prefetchData'))) {
                $model->prefetchData($this->_directiveValues);
            }
        }

        Varien_Profiler::stop('megamenu_prefetch_and_store_data');
    }

    private function _prepareModels()
    {
        $this->preparePrefetchers();
        $defaultParsers = $this->_getDefaultParsers();
        $this->_parser = Mage::getModel('theextensionlab_megamenu/parser', $defaultParsers);

        $this->_dispatchEventToAllowPrefetchAndParserModelUpdates();
    }

    private function _dispatchEventToAllowPrefetchAndParserModelUpdates()
    {
        Mage::dispatchEvent(
            'megamenu_prepare_prefetch_models_after', array('prefetcher' => $this, 'parser' => $this->_parser)
        );
    }

    private function preparePrefetchers()
    {
        $this->setPrefetchModels(
            array(
                'product' => 'theextensionlab_megamenu/prefetcher_product',
                'attribute_option' => 'theextensionlab_megamenu/prefetcher_attribute_option',
                'attribute' => 'theextensionlab_megamenu/prefetcher_attribute',
                'url_rewrite' => 'theextensionlab_megamenu/prefetcher_url_rewrite'
            )
        );
    }

    private function _getDefaultParsers()
    {
        return array(
            'featured_product' => 'theextensionlab_megamenu/parser_product_featured',
            'attribute_option' => 'theextensionlab_megamenu/parser_attribute_option',
            'url_rewrite'      => 'theextensionlab_megamenu/parser_url_rewrite'
        );
    }

    protected function getPrefetchModels()
    {
        return $this->_prefetchModels;
    }

    protected function setPrefetchModels($models)
    {
        $this->_prefetchModels = $models;
    }

}