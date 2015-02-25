<?php
/**
* @link http://www.yiiframework.com/
* @copyright Copyright (c) 2008 Yii Software LLC
* @license http://www.yiiframework.com/license/
*/

namespace nitm\widgets\replies;

use Yii;
use yii\helpers\Html;
use nitm\widgets\helpers\BaseWidget;
use nitm\widgets\models\Replies as RepliesModel;
use nitm\widgets\models\User;
use kartik\icons\Icon;

class RepliesCount extends BaseWidget
{
	public $fullDetails = true;
	/*
	 * HTML options for generating the widget
	 */
	public $options = [
		'class' => 'btn btn-sm',
		'role' => 'replyCount',
		'id' => 'reply-count',
		'tag' => 'a'
	];
	
	public $widgetOptions = [
		'class' => 'btn-group'
	];
	
	public function init()
	{
		switch(1)
		{
			case !($this->model instanceof RepliesModel) && (($this->parentType == null) || ($this->parentId == null) || ($this->parentKey == null)):
			$this->model = null;
			break;
			
			default:
			$this->model = ($this->model instanceof RepliesModel) ? $this->model : RepliesModel::findModel([$this->parentId, $this->parentType, $this->parentKey]);
			break;
		}
		parent::init();
	}
	
	public function run()
	{
		$this->options['id'] .= $this->parentId;
		$this->options['class'] .= ' '.($this->model->count() >= 1 ? 'btn-primary' : 'btn-transparent');
		$this->options['label'] = (int)$this->model->count().' Replies '.Icon::show('eye');
		$this->options['href'] = \Yii::$app->urlManager->createUrl(['/reply/index/'.$this->parentType."/".$this->parentId, '__format' => 'modal']);
		$this->options['title'] = \Yii::t('yii', 'View Replies');
		$info = \nitm\widgets\modal\Modal::widget([
			'options' => [
				'id' => $this->options['id'].'-modal'
			],
			'size' => 'large',
			'header' => 'Comments',
			'toggleButton' => $this->options,
			'dialogOptions' => [
				'class' => 'modal-full'
			],
		]);
		$new = $this->model->hasNew();
		switch($new >= 1)
		{
			case true:
			$new = \nitm\widgets\activityIndicator\ActivityIndicator::widget([
				'type' => 'new',
				'position' => 'top right',
				'text' => Html::tag('span', $new." new")
			]);
			break;
			
			default:
			$new = '';
			break;
		}
		switch(((int)$this->model->count() >= 1) && ($this->model->last instanceof RepliesModel) && $this->fullDetails)
		{
			case true:
			$info .= Html::tag('span', " on ".$this->model->last->created_at, $this->options);
			$info .= Html::tag('span', " Last by ".$this->model->last->author()->fullName(true), $this->options);
			break;
		}
		echo Html::tag('div', $info, $this->widgetOptions).$new;
	}
}
?>