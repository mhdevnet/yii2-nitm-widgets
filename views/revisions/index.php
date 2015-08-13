<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use nitm\helpers\Icon;

/**
 * @var yii\web\View $this
 * @var yii\data\ActiveDataProvider $dataProvider
 * @var nitm\module\models\search\Revisions $searchModel
 */

$this->title = 'Revisions';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="revisions-index" id="revisions-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
		'striped' => false,
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
        'columns' => [
			'version',
            [
				'attribute' => 'author',
				'label' => 'Author',
				'format' => 'html',
				'value' => function ($model, $index, $widget) {
					return $model->author()->url();
				}
			],
            'created_at',
            'parent_type',
            // 'parent_id',
			[
				'class' => 'yii\grid\ActionColumn',
				'buttons' => [
					'view' => function ($url, $model) {
						return Html::a(Icon::show('eye'), $url."?__format=modal", [
							'title' => Yii::t('yii', 'View Revision'),
							'class' => 'fa-2x',
							'role' => 'dynamicAction',
							'data-pjax' => '0',
							'data-toggle' => 'modal',
							'data-target' => '#revisionsViewModal'
						]);
					},
					'restore' => function ($url, $model) {
						return Html::a(Icon::show('reply'), $url, [
							'title' => Yii::t('yii', 'Restore Revision'),
							'class' => 'fa-2x',
							'role' => 'metaAction',
							'data-parent' => 'tr',
							'data-pjax' => '0',
						]);
					},
					'disable' => function ($url, $model) {
						return Html::a(Icon::forAction('delete', 'disabled', $model), \Yii::$app->urlManager->createUrl([$url]), [
							'title' => Yii::t('yii', ($model->disabled ? 'Un-Delete' : 'Delete')),
							'class' => 'fa-2x',
							'role' => 'metaAction '.(\Yii::$app->getUser()->getIdentity()->isAdmin() ? 'disableAction' : 'deleteAction'),
							'data-parent' => 'tr',
							'data-pjax' => '0',
						]);
					},
				],
				'template' => "{view} {restore} {disable}",
				'urlCreator' => function($action, $model, $key, $index) {
					return '/revisions/'.$action.'/'.$model->getId();
				},
				'options' => [
					'rowspan' => 3
				]
			],
        ],
		'rowOptions' => function ($model, $key, $index, $grid)
		{
			return [
				"class" => 'item '.\nitm\helpers\Statuses::getIndicator($model->getStatus()),
				'id' => 'documentation'.$model->getId(),
				'role' => 'iasItem statusIndicator'.$model->getId()
			];
		},
    ]); ?>

</div>

<div role="dialog" class="modal fade" id="revisionsViewModal" style="z-index: 10001">
	<div class="modal-lg modal-full modal-dialog">
		<div class="modal-content"></div>
	</div>
</div><!-- /.modal -->
<?php if(\Yii::$app->request->isAjax ): ?>
<script type="text/javascript">
$nitm.onModuleLoad('revisions', function (module) {
	module.initDefaults("#revisions-index", 'revisions');
});
</script>
<?php endif; ?>
