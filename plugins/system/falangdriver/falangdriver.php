<?php
/**
 * @package     Falang Driver
 * @subpackage  Add Falang Driver
 * @license     GNU General Public License Version 2, or later http://www.gnu.org/licenses/gpl.html
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

//Global definitions use for front
if( !defined('DS') ) {
    define( 'DS', DIRECTORY_SEPARATOR );
}


jimport('joomla.plugin.plugin');

/**
 * Falang Driver Plugin
 */
class plgSystemFalangdriver extends JPlugin
{

    /**
     * System Event: onAfterInitialise
     *
     * @return	string
     */
    function onAfterInitialise()
    {
        // This plugin is only relevant for use within the frontend!
        if (JFactory::getApplication()->isAdmin())
        {
            return;
        }

        if (!$this->isFalangDriverActive())
        {
            $this->setupDatabaseDriverOverride();
        }

        //fix for joomla > 3.4.0
        $app = JFactory::getApplication();
        if ($app->isSite()) {
            $router = $app->getRouter();

            // attach build rules for translation on SEF
            $router->attachBuildRule(array($this, 'buildRule'));

            // attach build rules for translation on SEF
            $router->attachParseRule(array($this, 'parseRule'));
        }
        //end fix
    }

    public function buildRule(&$router, &$uri)
    {
        $lang = $uri->getVar('lang');
        $default_lang	= JComponentHelper::getParams('com_languages')->get('site', 'en-GB');

        //we build the route for category list article
        if ($lang != $default_lang && $uri->getVar('id') != null && $uri->getVar('catid') != null) {

            $fManager = FalangManager::getInstance();
            $id_lang = $fManager->getLanguageID($lang);

            // Make sure we have the id and the alias
            if (strpos($uri->getVar('id'), ':') > 0)
            {
                list($tmp, $id) = explode(':', $uri->getVar('id'), 2);
                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true)
                    ->select('fc.value')
                    ->from('#__falang_content fc')
                    ->where('fc.reference_id = '.(int)$tmp)
                    ->where('fc.language_id = '.(int) $id_lang )
                    ->where('fc.reference_field = \'alias\'')
                    ->where('fc.reference_table = \'content\'');

                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('id',$tmp. ':' . $alias);
                }
            }
            // Make sure we have the id and the alias
            if (strpos($uri->getVar('catid'), ':') > 0)
            {
                list($tmp2, $catid) = explode(':', $uri->getVar('catid'), 2);

                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true)
                    ->select('fc.value')
                    ->from('#__falang_content fc')
                    ->where('fc.reference_id = '.(int)$tmp2)
                    ->where('fc.language_id = '.(int) $id_lang )
                    ->where('fc.reference_field = \'alias\'')
                    ->where('fc.reference_table = \'categories\'');

                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('catid',$tmp2. ':' . $alias);
                }
            }
        }

        //fix canonical if sef plugin is enabled
        $sef_plugin = JPluginHelper::getPlugin('system', 'sef');
        if (!empty($sef_plugin)) {
            if ($lang != $default_lang && $uri->getVar('id') != null && $uri->getVar('catid') != null) {
                $fManager = FalangManager::getInstance();
                $id_lang = $fManager->getLanguageID($lang);

                // Make sure we have the id and the alias
                if (strpos($uri->getVar('id'), ':') === false)
                {
                    //we use id in the query to be translated.
                    $db = JFactory::getDbo();
                    $dbQuery = $db->getQuery(true)
                        ->select('alias,id')
                        ->from('#__content')
                        ->where('id=' . (int) $uri->getVar('id'));
                    $db->setQuery($dbQuery);
                    $alias = $db->loadResult();
                    if (isset($alias)) {
                        $uri->setVar('id',$uri->getVar('id') . ':' . $alias);
                    }
                }
            }
        }

        //build route for hikashop product
        if ( $uri->getVar('option') == 'com_hikashop' &&  $uri->getVar('ctrl') == 'product' &&  $uri->getVar('task')== 'show' ) {
            // on native language look in falang table
            if ($default_lang != $lang ){
                $fManager = FalangManager::getInstance();
                $id_lang = $fManager->getLanguageID($lang);
                $id = $uri->getVar('cid');
                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true)
                    ->select('fc.value')
                    ->from('#__falang_content fc')
                    ->where('fc.reference_id = '.(int)$id)
                    ->where('fc.language_id = '.(int) $id_lang )
                    ->where('fc.reference_field = \'product_alias\'')
                    ->where('fc.reference_table = \'hikashop_product\'');

                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('name', $alias);
                }

            } else {
                // translated languague look in native table
                $id = $uri->getVar('cid');
                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true)
                    ->select('product_alias')
                    ->from('#__hikashop_product')
                    ->where('product_id = '.(int)$id);
                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('name', $alias);
                }
            }
            //
        }
        //build route for hikahsop category list
        if ( $uri->getVar('option') == 'com_hikashop' &&  $uri->getVar('ctrl') == 'category' &&  $uri->getVar('task')== 'listing' ) {
            // on native language look in falang table
            if ($default_lang != $lang) {
                $fManager = FalangManager::getInstance();
                $id_lang = $fManager->getLanguageID($lang);
                $id = $uri->getVar('cid');
                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true)
                    ->select('fc.value')
                    ->from('#__falang_content fc')
                    ->where('fc.reference_id = ' . (int)$id)
                    ->where('fc.language_id = ' . (int)$id_lang)
                    ->where('fc.reference_field = \'category_alias\'')
                    ->where('fc.reference_table = \'hikashop_category\'');

                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('name', $alias);
                }

            } else {
                // translated languague look in native table
                $id = $uri->getVar('cid');
                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true)
                    ->select('category_alias')
                    ->from('#__hikashop_category')
                    ->where('category_id = ' . (int)$id);
                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('name', $alias);
                }
            }
        }
        //build route for k2 category list
        //v2.2.2 add download test due to download link bug in other case.
        if ( $uri->getVar('option') == 'com_k2' &&  $uri->getVar('view') == 'item' && $uri->getVar('task') != 'download' ) {
            // on native language look in falang table
            if ($default_lang != $lang ) {
                $fManager = FalangManager::getInstance();
                $id_lang = $fManager->getLanguageID($lang);

                // Make sure we have the id and the alias
                if (strpos($uri->getVar('id'), ':') > 0) {
                    list($tmp, $id) = explode(':', $uri->getVar('id'), 2);
                    $db = JFactory::getDbo();
                    $dbQuery = $db->getQuery(true)
                        ->select('fc.value')
                        ->from('#__falang_content fc')
                        ->where('fc.reference_id = ' . (int)$tmp)
                        ->where('fc.language_id = ' . (int)$id_lang)
                        ->where('fc.reference_field = \'alias\'')
                        ->where('fc.reference_table = \'k2_items\'');

                    $db->setQuery($dbQuery);
                    $alias = $db->loadResult();
                    if (isset($alias)) {
                        $uri->setVar('id', $tmp . ':' . $alias);
                    }
                }
            } else {
                // translated languague look in native table
                $id = $uri->getVar('id');
                list($tmp, $id) = explode(':', $uri->getVar('id'), 2);
                $db = JFactory::getDbo();
                $dbQuery = $db->getQuery(true)
                    ->select('alias')
                    ->from('#__k2_items')
                    ->where('id = '.(int)$tmp);
                $db->setQuery($dbQuery);
                $alias = $db->loadResult();
                if (isset($alias)) {
                    $uri->setVar('id', $tmp . ':' . $alias);
                }
            }
        }

        return array();
    }


    public function parseRule(&$router, &$uri) {
        static $done = false;
        if (!$done) {
            $done = true;
            //reload menu
            JFactory::getApplication()->getMenu()->__construct();
            //rewrite Menu route with translated alias
            $app = JFactory::getApplication();
            $menu = $app->getMenu()->getMenu();

        }
        return array();
    }

    public function isFalangDriverActive()
    {
        $db = JFactory::getDBO();

        return is_a($db, 'JFalangDatabase');
    }


    function onAfterDispatch()
    {
        if (JFactory::getApplication()->isSite() && $this->isFalangDriverActive()) {
            include_once( JPATH_ADMINISTRATOR . '/components/com_falang/version.php');
            $version = new FalangVersion();
            if ($version->_versiontype == 'free'  ) {
                FalangManager::setBuffer();
            }
            return true;
        }
    }


    function setupDatabaseDriverOverride()
    {
        //override only the override file exist
        if (file_exists(dirname(__FILE__) . '/falang_database.php'))
        {

            require_once( dirname(__FILE__) . '/falang_database.php');

            $conf = JFactory::getConfig();

            $host = $conf->get('host');
            $user = $conf->get('user');
            $password = $conf->get('password');
            $db = $conf->get('db');
            $dbprefix = $conf->get('dbprefix');
            $driver = $conf->get('dbtype');
            $debug = $conf->get('debug');

            $options = array('driver' => $driver,"host" => $host, "user" => $user, "password" => $password, "database" => $db, "prefix" => $dbprefix, "select" => true);
            $db = new JFalangDatabase($options);
            $db->debug($debug);


            if ($db->getErrorNum() > 2)
            {
                JError::raiseError('joomla.library:' . $db->getErrorNum(), 'JDatabase::getInstance: Could not connect to database <br/>' . $db->getErrorMsg());
            }

            // replace the database handle in the factory
            JFactory::$database = null;
            JFactory::$database = $db;

            $test = JFactory::getDBO();

        }

    }

    private function setBuffer()
    {
        $doc = JFactory::getDocument();
        $cacheBuf = $doc->getBuffer('component');

        $cacheBuf2 =
            '<div><a title="Faboba : Cr&eacute;ation de composant'.
            'Joomla" style="font-size: 8px;; visibility: visible;'.
            'display:inline;" href="http://www.faboba'.
            '.com" target="_blank">FaLang tra'.
            'nslation syste'.
            'm by Faboba</a></div>';

        if ($doc->_type == 'html')
            $doc->setBuffer($cacheBuf . $cacheBuf2,'component');

    }


    /*
     * Use trigger to activate the language selection in the template
     */
    function onContentPrepareForm($form, $data)
    {
        if (JFactory::getApplication()->isSite()){return;}

        jimport('joomla.application.component.helper');
        $params = JComponentHelper::getParams('com_falang');
        $show_tpl_lang = $params->get('show_tpl_lang');

        if (!isset($show_tpl_lang) || $show_tpl_lang == '0' ) {return;}


        if (!($form instanceof JForm))
        {
            $this->_subject->setError('JERROR_NOT_A_FORM');

            return false;
        }
        if ((is_array($data) && array_key_exists('home', $data))
            || ((is_object($data) && isset($data->home) ))) {
            $form->setFieldAttribute('home', 'readonly', 'false');
        }
    }

}