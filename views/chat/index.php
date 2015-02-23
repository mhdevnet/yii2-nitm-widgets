<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use kartik\icons\Icon;
use nitm\widgets\replies\ChatModal;
use nitm\widgets\models\Replies;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var app\models\search\Chat $searchModel
 */

$title = Yii::t('app', 'Chat');
switch(\Yii::$app->request->isAjax)
{
	case true:
	$this->title = $title;
	break;
}
$this->params['breadcrumbs'][] = $title;

$widget->withForm = isset($widget->withForm) ? $widget->withForm : \Yii::$app->request->get(Replies::FORM_PARAM);

?>
<?php
	$_GET[Replies::FORM_PARAM] = 0;
	$widget->listOptions['class'] = isset($widget->listOptions['class']) ? $widget->listOptions['class'] : 'chat-messages';
	$dataProvider->pagination->route = '/reply/index/chat/0/1';
	$params = array_intersect_key($_GET, [
		Replies::FORM_PARAM => null,
		'page' => null
	]);
	$dataProvider->pagination->params = $params;
	$messages = ListView::widget([
		'layout' => "{items}\n{pager}",
		'options' => $widget->listOptions,
		'dataProvider' => $dataProvider,
		'itemOptions' => ['class' => 'item'],
		'itemView' => function ($model, $key, $index, $_widget) use($widget) {
				return $widget->render('@nitm/widgets/views/chat/view',['model' => $model, 'primaryModel' => $widget->model]);
		},
		'pager' => [
			'class' => \nitm\widgets\ias\ScrollPager::className(),
			'overflowContainer' => '#chat-messages-container',
			'container' => '#'.$widget->listOptions['id'],
			'item' => ".item",
			'negativeMargin' => 200,
			'noneLeftText' => 'No More messages'
		]
	]);
	$form = ($widget->withForm == true) ? \nitm\widgets\replies\ChatForm::widget(['model' => $widget->model]) : '';
	switch(isset($widget->noContainer) && $widget->noContainer == true)
	{
		case false:
		$messages = Html::tag('div', $messages, [
			'id' => 'chat-messages-container', 
			'class' => 'chat-messages-container'
		]);
		$messages = Html::tag('div', $messages.$form, $widget->options);
		break;
	}
	echo $messages;
?>
<script type="text/javascript">
$nitm.onModuleLoad('replies', function () {
	<?php if(\Yii::$app->request->isAjax): ?>
	$nitm.module('nitm-ias').initIas("<?= $widget->options['id']?>");
	<?php endif; ?>
	$nitm.module('replies').initDefaults("<?= $widget->options['id']?>");
});
</script>
