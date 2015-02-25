<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model nitm\models\Alerts */

$this->title = Yii::t('app', 'Update {modelClass}: ', [
    'modelClass' => 'Alerts',
])
.' Matching '.$model->properName($model->priority)
.' '.($model->remote_type == 'any' ? 'Anything' : $model->properName($model->remote_type));
if(!empty($model->remote_for) && !($model->remote_for == 'any'))
	$this->title .= ' for '.$model->properName($model->remote_for);
if(!empty($model->remote_id))
	$this->title .= ' '.(!$model->remote_id ? 'with Any id' : ' with id '.$model->remote_id);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Alerts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');
?>
<div class="alerts-update">
	<?php if(!\Yii::$app->request->isAjax): ?>
	<?= \yii\widgets\Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]); ?>
	<h2><?= Html::encode($this->title) ?></h2>
	<?php endif; ?>

    <?= $this->render('form/_form', [
        'model' => $model,
		'formOptions' => $formOptions,
		'scenario' => $scenario,
		'action' => $action,
		'type' => $type
    ]) ?>

</div>
