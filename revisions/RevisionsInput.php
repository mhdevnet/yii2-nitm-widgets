<?php
/**
* @link http://www.yiiframework.com/
* @copyright Copyright (c) 2008 Yii Software LLC
* @license http://www.yiiframework.com/license/
*/

namespace nitm\widgets\revisions;

use Yii;
use yii\helpers\Html;
use yii\widgets\InputWidget;
use nitm\widgets\models\Revisions as RevisionsModel;
use nitm\widgets\helpers\BaseWidget;
use kartik\icons\Icon;
use nitm\widgets\editor\Editor;
use nitm\helpers\ArrayHelper;

class RevisionsInput extends BaseWidget
{
	public $revisionsModel;
	
	/**
	 * The name of this input widget
	 */
	public $attribute;
	
	/**
	 * The value for this input widget
	 */
	public $value;
	
	public $editorOptions = [
		'role' => 'createRevisions',
		'toolbarSize' => 'medium',
		'size' => 'medium'
	];
		
	/*
	 * HTML options for generating the widget
	 */
	public $widgetOptions = [
		'class' => 'form-group',
		'id' => 'revision_input_div'
	];
	
	/*
	 * HTML options for generating the widget elements
	 */
	public $options = [
		'id' => 'revision_input',
		'role' => 'createRevision',
	];
	
	/**
	 * Autosave every X seconds
	 */
	public $revisionSaveInterval = 60;
	
	/**
	 * Revision autosave path handler. With trailing slash
	 */
	public $revisionSavePath = '/revisions/create/';
	
	/**
	 * Enable redactor? True by default
	 */
	public $enableRedactor = true;
	
	/**
	 * Autosave path handler. With trailing slash
	 */
	public $autoSavePath;
	
	/**
	 * Autosave every X seconds
	 */
	public $autoSaveInterval = 5;
	
	private $_enableRevisions = true;
	
	public function init()
	{
		switch(1)
		{
			case !($this->revisionsModel instanceof RevisionsModel) && (($this->parentType == null) || ($this->parentId == null)):
			$this->_enableRevisions = false;
			break;
			
			default:
			$this->revisionsModel = ($this->revisionsModel instanceof RevisionsModel) ? $this->model : RevisionsModel::findModel([$this->parentId, $this->parentType]);
			break;
		}
		parent::init();
		$this->revisionSavePath .= $this->parentType.'/'.$this->parentId;
		$this->options['id'] .= $this->parentId;
		$this->widgetOptions['id'] .= $this->parentId;
	}
	
	public function run()
	{
		switch($this->_enableRevisions)
		{
			case true:
			$this->revisionsModel->setScenario('validateNew');
			$this->editorOptions =  [
				'role' => $this->options['role'],
				'id' => $this->options['id'].$this->parentId,
			];

			if(!$this->model->isNewRecord) {
				$this->editorOptions += [
					'enableAutoSave' => true,
					'autoSavePath' => $this->autoSavePath,
					'autoSaveInterval' => $this->autoSaveInterval,
					'autoSaveName' => $this->model->formName().'['.$this->attribute.']',
				];
				$this->options += [
					'data-save-path' => $this->revisionSavePath,
					'data-save-interval' => $this->revisionSaveInterval,
				];
			}

			Asset::register($this->getView());
			break;
			
			default:
			$revisionOptions = [];
			break;
		}
		$this->options['data-enable-redactor'] = (int)$this->enableRedactor;
		switch($this->enableRedactor)
		{
			case true:
			$this->editorOptions['id'] = $this->options['id'];
			$this->editorOptions['model'] = $this->model;
			$this->editorOptions['attribute'] = $this->attribute;
			$this->editorOptions['htmlOptions'] = $this->options;
			$input = Editor::widget($this->editorOptions);
			break;
			
			default:
			$input = Html::activeTextarea($this->model, $this->attribute, $revisionOptions);
			break;
		}
		$result = Html::tag('div', '', ['role' => 'revisionStatus']);
		return Html::tag('div', $input.$result, $this->widgetOptions).Html::script('$nitm.onModuleLoad("revisions", function (module) {
			module.attributeName = "'.$this->attribute.'";
			module.saveUrl = "'.$this->revisionSavePath.'";
			module.interval = '.($this->revisionSaveInterval*1000).';
			module.initInterval("#'.$this->widgetOptions['id'].'");
			module.initActivity("#'.$this->widgetOptions['id'].'");
		});');
	}
}
?>