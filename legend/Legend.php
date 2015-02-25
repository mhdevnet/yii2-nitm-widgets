<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace nitm\widgets\legend;

use yii\helpers\Html;
use nitm\widgets\helpers\BaseWidget;

/**
 * Alert widget renders a message from session flash. All flash messages are displayed
 * in the sequence they were assigned using setFlash. You can set message as following:
 *
 * - \Yii::$app->getSession()->setFlash('error', 'This is the message');
 * - \Yii::$app->getSession()->setFlash('success', 'This is the message');
 * - \Yii::$app->getSession()->setFlash('info', 'This is the message');
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @author Alexander Makarov <sam@rmcerative.ru>
 */
class Legend extends BaseWidget
{
	public $options = [
	];
	
	/**
	 * Array containing legend mappint for classes
	 */
	public $legend = [];
	
	/*
	 * HTML options for generating the widget
	 */
	public $labelOptions = [
		'class' => 'label',
		'role' => 'legend',
	];
	
	public function init()
	{
		parent::init();
		
		$legend = Html::tag('span', 'Legend:&nbsp;', []);
		foreach ($this->legend as $type => $message) {
			/* initialize css class for each alert box */
			$options['class'] = $this->labelOptions['class'].' label-'.$type;
			if($type == 'normal')
				$options['style'] = 'color:black';
			else 
				$options['style'] = '';

			/* assign unique id to each alert box */
			$options['id'] = $this->getId() . '-' . $type;

			$legend .= Html::tag('span', $message, $options);
		}
		echo Html::tag('div', $legend, $this->options);
	}
}
