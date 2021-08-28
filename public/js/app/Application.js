app.application = false;
app.content =  Ext.create('Ext.Panel',{
    frame:false,
    border:false,
    bodyBorder:false,
    layout:'fit',
    scrollable:false,
    items:[],
    collapsible:false,
    flex : 1
});


Ext.application({
    name: 'DVelum ORM UI',
    launch: function() {
        app.application = this;
        app.viewport = Ext.create('Ext.container.Viewport', {
            cls:'formBody',
            layout: {
                type: 'vbox',
                pack: 'start',
                align: 'stretch'
            },
            items:app.content
        });
    }
});