<?php
$theme ='gray';// 'crisp'; // aria, gray, classic, neptune, triton, crisp
$lang = $this->get('lang');
$ormLang = $this->get('orm_lang');
$dbConfigs = $this->get('db_configs');
$actions = $this->get('actions');
$fields = $this->get('fields');

?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width; initial-scale=1.0"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>DVelum ORM UI</title>
    <script src="/js/lib/extjs/build/ext-all-debug.js"></script>
    <script src="/js/lib/extjs/build/classic/theme-<?=$theme?>/theme-<?=$theme?>.js"></script>
    <script src="/js/lib/extjs/build/classic/locale/locale-en.js"></script>
    <link rel="stylesheet" type="text/css" href="/js/lib/extjs/build/classic/theme-<?=$theme?>/resources/theme-<?=$theme?>-all.css"/>
    <link rel="stylesheet" type="text/css" href="/css/style.css?v=<?=filemtime(DVELUM_ORM_IU_DIR . '/public/css/style.css')?>" />


</head>
<body>
    <script>
        var app = {wwwRoot:'/'};
        var appLang = <?=$lang;?>;
        var canPublish =  true;
        var canEdit = true;
        var canDelete = true;
        var useForeignKeys = true
        var canUseBackup = false;
        var dbConfigsList = <?=json_encode($dbConfigs)?>;
        var ormTooltips = <?=$ormLang;?>;
        var shardingEnabled = true;
        var ormActionsList = <?=json_encode($actions)?>;
        var ormAddObjectFields = [<?=$fields?>];
    </script>
    <script src="/js/app/common.js"></script>

    <script src="/js/app/system/SearchPanel.js"></script>
    <script src="/js/app/system/ORM.js?v=<?=filemtime(DVELUM_ORM_IU_DIR . '/public/js/app/system/ORM.js')?>"></script>
    <script src="/js/app/system/EditWindow.js"></script>
    <script src="/js/app/system/HistoryPanel.js"></script>
    <script src="/js/app/system/ContentWindow.js"></script>
    <script src="/js/app/system/RevisionPanel.js"></script>
    <script src="/js/app/system/RelatedGridPanel.js"></script>
    <script src="/js/lib/ext_ux/RowExpanderGrid.js"></script>
    <script src="/js/app/system/SelectWindow.js"></script>
    <script src="/js/app/system/ObjectLink.js"></script>

    <link rel="stylesheet" type="text/css" href="/css/system/joint.min.css"/>
    <script src="/js/lib/jquery.js"></script>

    <script src="/js/lib/uml/lodash.min.js"></script>
    <script src="/js/lib/uml/backbone-min.js"></script>
    <script src="/js/lib/uml/joint.min.js"></script>
    
    <script src="/js/app/Application.js"></script>
    <script src="/js/app/orm.js"></script>
</body>
</html>