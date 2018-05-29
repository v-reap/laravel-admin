<?php

namespace Encore\Admin\Form\Field;

use Encore\Admin\Form\Field;
use Illuminate\Support\Facades\Validator;

class Bpmn extends Field
{

    protected $view = 'admin::form.bpmn';

    /**
     * Css.
     *
     * @var array
     */
    protected static $css = [
        '/bpmn/dist/assets/diagram-js.css?v=1',
        '/bpmn/dist/assets/bpmn-font/css/bpmn-embedded.css?v=1',
//        '/bpmn/css/app.css?v=1',
    ];

    /**
     * Js.
     *
     * @var array
     */
    protected static $js = [
        '/bpmn/dist/bpmn-modeler.production.min.js?v=1',
    ];

    /**
     * Render file upload field.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function render()
    {
        $name = $this->formatName($this->column);

        $this->script = <<<EOT
//{$this->id} -  $name  llllllllllll

  var bpmnXML;

  var viewer = new BpmnJS({
        container: '#canvas',
        propertiesPanel: {
            parent: '#js-properties-panel'
        },
//        additionalModules: [
//            propertiesPanelModule,
//            propertiesProviderModule
//        ],
//        moddleExtensions: {
//            camunda: camundaModdleDescriptor
//        }
   });
  createNewDiagram();

//  viewer.importXML(bpmnXML, function(err) {
//    if (err) {
//      // import failed :-(
//    } else {
//      // we did well!
//
//      var canvas = viewer.get('canvas');
//      canvas.zoom('fit-viewport');
//    }
//  });
EOT;
        return parent::render();
    }
}
