<?php
/**
 * @link https://github.com/ptrofimov/beanstalk_console
 * @link http://kr.github.com/beanstalkd/
 * @author Petr Trofimov, Sergey Lysenko
 */
function __autoload($class)
{
    require_once str_replace('_', '/', $class) . '.php';
}

require_once 'BeanstalkInterface.class.php';
require_once dirname(__FILE__) . '/../config.php';

$server = !empty($_GET['server']) ? $_GET['server'] : '';
$action = !empty($_GET['action']) ? $_GET['action'] : '';
$count = !empty($_GET['count']) ? $_GET['count'] : '';
$tube = !empty($_GET['tube']) ? $_GET['tube'] : '';

class Console
{
    public $interface;

    protected $_tplVars = array();
    protected $_globalVar = array();
    protected $_errors = array();
    private $servers = array();

    public function __construct()
    {
        $this->__init();
        $this->_main();
    }

    /** @return array */
    public function getServers()
    {
        return $this->servers;
    }

    public function getServerStats($server)
    {
        try {
            $interface = new BeanstalkInterface($server);
            $stats = $interface->getServerStats();
        } catch (Pheanstalk_Exception_ConnectionException $e) {
            $stats = [];
        }

        return $stats;
    }

    protected function __init()
    {
        global $server, $action, $count, $tube, $config;

        $this->_globalVar = array(
            'server' => $server,
            'action' => $action,
            'count' => $count,
            'tube' => $tube,
            'config' => $config);
        $this->_tplVars = $this->_globalVar;
        $this->_tplVars['_tplMain'] = 'main';

        $this->servers = $config['servers'];
        if (isset($_COOKIE['beansServers'])) {
            foreach (explode(';', $_COOKIE['beansServers']) as $server) {
                $this->servers[] = $server;
            }
        }
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getTplVars($var = null)
    {
        if (!empty($var)) {
            $result = !empty($this->_tplVars[$var]) ? $this->_tplVars[$var] : null;
        } else {
            $result = $this->_tplVars;
        }

        return $result;
    }

    protected function _main()
    {
        if (!empty($_GET['action'])) {
            $funcName = "_action" . ucfirst($this->_globalVar['action']);
            if (method_exists($this, $funcName)) {
                $this->$funcName();
            }
            return;
        }

        if (!isset($_GET['server'])) {
            return;
        }

        try {
            $this->interface = new BeanstalkInterface($this->_globalVar['server']);

            $this->_tplVars['tubes'] = $this->interface->getTubes();

            $stats = $this->interface->getTubesStats();

            $this->_tplVars['tubesStats'] = $stats;
            $this->_tplVars['peek'] = $this->interface->peekAll($this->_globalVar['tube']);
            $this->_tplVars['contentType'] = $this->interface->getContentType();
        } catch (Pheanstalk_Exception_ConnectionException $e) {
            $this->_errors[] = 'The server is unavailable';
        }
        catch (Exception $e) {
            $this->_errors[] = $e->getMessage();
        }
    }

    protected function _actionKick()
    {
        $this->interface->kick($this->_globalVar['tube'], $this->_globalVar['count']);
        header(
            sprintf('Location: index.php?server=%s&tube=%s', $this->_globalVar['server'],
                $this->_globalVar['tube']));
        exit();
    }

    protected function _actionDelete()
    {
        $this->interface->deleteReady($this->_globalVar['tube']);
        header(
            sprintf('Location: index.php?server=%s&tube=%s', $this->_globalVar['server'],
                $this->_globalVar['tube']));
        exit();
    }

    protected function _actionServersRemove()
    {
        $server = $_GET['removeServer'];
        $this->servers = array_diff($this->servers, array($server));
        setcookie('beansServers', implode(';', $this->servers), time() + 86400 * 365);
        header('Location: /');
        exit();
    }

    protected function _actionAddjob()
    {
        $result = array('result' => false);

        $tubeName = !empty($_POST['tubeName']) ? $_POST['tubeName'] : '';
        $tubeData = !empty($_POST['tubeData']) ? stripcslashes($_POST['tubeData']) : '';
        $tubePriority = !empty($_POST['tubePriority']) ? $_POST['tubePriority'] : '';
        $tubeDelay = !empty($_POST['tubeDelay']) ? $_POST['tubeDelay'] : '';
        $tubeTtr = !empty($_POST['tubeTtr']) ? $_POST['tubeTtr'] : '';

        $id = $this->interface->addJob($tubeName, $tubeData, $tubePriority, $tubeDelay, $tubeTtr);

        if (!empty($result)) {
            $result = array('result' => true, 'id' => $result);
        }

        echo json_encode($result);
        exit();
    }

    protected function _actionReloader()
    {
        $this->_tplVars['_tplMain'] = 'ajax';
        $this->_tplVars['_tplBlock'] = 'allTubes';
    }
}
