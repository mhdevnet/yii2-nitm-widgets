<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var app\models\Token $model
 * @var yii\widgets\ActiveForm $form
 */
$formOptions = array_replace_recursive($formOptions, [
	"type" => ActiveForm::TYPE_HORIZONTAL,
	'options' => [
		"role" => "ajaxForm"
	],
]);
?>

<div id="<?= $model->isWhat()?>_form_container" class="token-form <?= $this->context->getStatusIndicator($model); ?>">

    <?php $form = include(\Yii::getAlias("@nitm/views/layouts/form/header.php")); ?>
		<?= Html::label('User', 'usersearch', []); ?>
		<?php 
			if(!$model->isNewRecord)
			{
				echo Html::tag('h4', \nitm\module\models\User::getFullName($model->user_id));
			}
			else
			{
				echo \yii\jui\AutoComplete::widget([
					'name' => 'name',
					'attribute' => 'name',
					'options' => [
						'class' => 'form-control',
						'id' => 'usersearch',
						'role' => 'autocompleteSelect',
						'data-select' => \yii\helpers\Json::encode([
							"value" => "unique", 
							"label" => "name", 
							"container" => "token-user_id"
						]),
					],
					'clientOptions' => [
						'source' => '/autocomplete/user',
					],
				]);
			}
		?>
		<?= Html::activeHiddenInput($model, 'user_id') ?>
		<?= $form->field($model, 'active')->checkbox() ?>

		<?= $form->field($model, 'revoked')->checkbox() ?>

		<?= $form->field($model, 'level')->dropDownList($model->getLevels()) ?>

		<div class="form-group">
			<?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
		</div>

	<?php ActiveForm::end(); ?>

</div>
