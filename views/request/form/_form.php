<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\widgets\Select2;
use kartik\icons\Icon;

/**
 * @var yii\web\View $this
 * @var frontend\models\Requests $model
 * @var yii\widgets\ActiveForm $form
 */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Requests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$action = $model->getIsNewRecord() ? 'create' : 'update';
?>

<div id="<?= $model->isWhat()?>_form_container">
	<?php if (!$model->getIsNewRecord()) : ?>
	<div class="col-md-7 col-lg-7">
	<?= $this->render('meta_info', ['model' => $model]); ?>
	</div>
	<?php endif ?>
	
	<?php if (!$model->getIsNewRecord()) : ?>
	<div class="col-md-5 col-lg-5 full-height absolute col-md-offset-7 col-lg-offset-7">
	<br><br>
	<?php else: ?>
	<div class="col-md-12 col-lg-12">
	<?php endif ?>
		<?php
			echo Html::tag('div', '', ['id' => 'alert']);
			$form = include(\Yii::getAlias("@nitm/views/layouts/form/header.php"));
		?>
	
		<?= $form->field($model, 'title') ?>
	
		<?=
			$form->field($model, 'type_id')->widget(Select2::className(), [
				'data' => $model->getCategoryList($model->isWhat().'-categories'),
			])->label("Type");
		?>
	
		<?=
			$form->field($model, 'request_for_id')->widget(Select2::className(), [
				'data' => $model->getCategoryList($model->isWhat().'-for'),
			])->label("Request For");
		?>
	
		<?=
			$form->field($model, 'status')->widget(Select2::className(), [
				'data' => $model->getStatuses(),
			])->label("Status");
		?>
		<div class="wrapper">
			<div class="row">
				<div class="col-md-12 col-lg-12">
				<?php
					echo $this->context->RevisionsInputWidget([
						"parentId" => $model->getId(),
						"parentType" => $model->isWhat(),
						'name' => 'request',
						'revisionsModel' => $model->revisionModel(),
						'model' => $model,
						'value' => $model->request,
						'editorOptions' => [
							'toolbarSize' => 'full',
							'size' => 'full'
						]
					]);
				?>
				</div>
			</div>
		</div>
		
		<?php if(!\Yii::$app->request->isAjax): ?>
		<div class="fixed-actions text-right">
			<?= Html::submitButton(ucfirst($action), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
		</div>
		<?php endif; ?>
	
		<?php ActiveForm::end(); ?>
	</div>
</div>
<script type='text/javascript'>
$nitm.onModuleLoad('entity:requests', function () {
	$nitm.module('entity').initForms('<?= $model->isWhat();?>_form_container', 'entity:requests');
});
</script>
