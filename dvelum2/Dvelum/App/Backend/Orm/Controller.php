<?php
declare(strict_types=1);

namespace Dvelum\App\Backend\Orm;

use Dvelum\Config;
use Dvelum\Model;
use Dvelum\Orm;
use Dvelum\Lang;
use Dvelum\View;
use Dvelum\Template;

class Controller extends \Dvelum\App\Backend\Controller implements \Router_Interface
{
    protected $routes = [
        'dictionary' => 'Backend_Orm_Dictionary',
        'dataview' => 'Backend_Orm_Dataview',
        'connections' => 'Backend_Orm_Connections_Controller',
        'log' => 'Backend_Orm_Log'
    ];

    public function route()
    {
        $action = $this->request->getPart(2);
        if(isset($this->routes[$action])){
            $router = new \Backend_Router();
            $router->runController($this->routes[$action], $this->request->getPart(3));
            return;
        }

        if(method_exists($this,$action.'Action')){
            $this->{$action.'Action'}();
        }else{
            $this->indexAction();
        }
    }


    public function __construct()
    {
        parent::__construct();
        /*
         * Set Orm Builder log paths
         */
        Orm\Object\Builder::writeLog($this->appConfig['use_orm_build_log']);
        Orm\Object\Builder::setLogPrefix($this->appConfig['development_version'].'_build_log.sql');
        Orm\Object\Builder::setLogsPath($this->appConfig['orm_log_path']);
    }

    public function indexAction()
    {
        $version = Config::storage()->get('versions.php')->get('orm');
        $dbConfigs = [];

        foreach ($this->appConfig->get('db_configs') as $k=>$v){
            $dbConfigs[]= [
                'id'=>$k ,
                'title'=>$this->lang->get($v['title'])
            ];
        }
        //tooltips
        $lPath = $this->appConfig->get('language').'/orm.php';
        Lang::addDictionaryLoader('orm_tooltips', $lPath, Config\Factory::File_Array);

        $this->resource->addInlineJs('
          var canPublish =  '.((integer)$this->moduleAcl->canPublish($this->module)).';
          var canEdit = '.((integer)$this->moduleAcl->canEdit($this->module)).';
          var canDelete = '.((integer)$this->moduleAcl->canDelete($this->module)).';
          var useForeignKeys = '.((integer)$this->appConfig['foreign_keys']).';
          var canUseBackup = false;
          var dbConfigsList = '.json_encode($dbConfigs).';
        ');

        $this->resource->addRawJs('var ormTooltips = '.Lang::lang('orm_tooltips')->getJson().';');

        $this->resource->addJs('/js/app/system/SearchPanel.js', 0);
        $this->resource->addJs('/js/app/system/ORM.js?v='.$version, 2);

        $this->resource->addJs('/js/app/system/EditWindow.js', 1);
        $this->resource->addJs('/js/app/system/HistoryPanel.js', 1);
        $this->resource->addJs('/js/app/system/ContentWindow.js', 1);
        $this->resource->addJs('/js/app/system/RevisionPanel.js', 2);
        $this->resource->addJs('/js/app/system/RelatedGridPanel.js', 2);

        $this->resource->addJs('/js/app/system/SelectWindow.js', 2);
        $this->resource->addJs('/js/app/system/ObjectLink.js', 3);

        Model::factory('Medialib')->includeScripts();
        $this->resource->addCss('/css/system/joint.min.css', 1);
        $this->resource->addJs('/js/lib/uml/lodash.min.js', 2);
        $this->resource->addJs('/js/lib/uml/backbone-min.js', 3);
        $this->resource->addJs('/js/lib/uml/joint.min.js', 4);
        $this->resource->addJs('/js/app/system/crud/orm.js', 7);
    }


    /**
     * Get DB Objects list
     */
    public function listAction()
    {
        $stat = new Orm\Stat();
        $data = $stat->getInfo();

        if($this->request->post('hideSysObj', 'boolean', false)){
            foreach ($data as $k => $v)
                if($v['system'])
                    unset($data[$k]);
            sort($data);
        }
        $this->response->success($data);
    }

    /**
     * Validate Object Db Structure
     */
    public function validateAction()
    {
        $engineUpdate = false;

        $name = $this->request->post('name', 'string', false);

        if(!$name)
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        $objectConfig = Orm\Object\Config::factory($name);

        // Check ACL permissions
        $acl = $objectConfig->getAcl();
        if($acl){
            if(!$acl->can(Orm\Object\Acl::ACCESS_CREATE , $name) || !$acl->can(Orm\Object\Acl::ACCESS_VIEW , $name)){
                $this->response->error($this->lang->get('ACL_ACCESS_DENIED'));
            }
        }

        try {
            $obj = Orm\Object::factory($name);
        } catch (\Exception $e){
            $this->response->error($this->lang->get('CANT_GET_VALIDATE_INFO'));
        }

        $builder = new Orm\Object\Builder($name);
        $tableExists = $builder->tableExists();

        $colUpd = [];
        $indUpd = [];
        $keyUpd = [];

        if($tableExists){
            $colUpd =  $builder->prepareColumnUpdates();
            $indUpd =  $builder->prepareIndexUpdates();
            $keyUpd =  $builder->prepareKeysUpdate();
            $engineUpdate = $builder->prepareEngineUpdate();
        }

        $objects = $builder->getObjectsUpdatesInfo();

        if(empty($colUpd) && empty($indUpd) && empty($keyUpd) && $tableExists && !$engineUpdate && empty($objects)){
            $this->response->success([],['nothingToDo'=>true]);
        }

        $template = new \Dvelum\View();
        $template->disableCache();
        $template->engineUpdate = $engineUpdate;
        $template->columns = $colUpd;
        $template->indexes = $indUpd;
        $template->objects = $objects;
        $template->keys = $keyUpd;
        $template->tableExists = $tableExists;
        $template->tableName = $obj->getTable();
        $template->lang = $this->lang;

        $msg = $template->render(\Dvelum\App\Application::getTemplatesPath() . 'orm_validate_msg.php');

        $this->response->success([],array('text'=>$msg,'nothingToDo'=>false));
    }
}