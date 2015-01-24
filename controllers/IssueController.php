<?php

namespace nitm\widgets\controllers;

use Yii;
use nitm\widgets\models\Issues;
use nitm\widgets\models\search\Issues as IssuesSearch;
use nitm\helpers\Response;
use nitm\helpers\Icon;
use nitm\widgets\issueTracker\IssueTracker;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\data\ArrayDataProvider;

/**
 * IssueController implements the CRUD actions for Issues model.
 */
class IssueController extends \nitm\controllers\DefaultController
{
	use \nitm\traits\Controller;
	
	public $legend = [
		'success' => 'Closed and Resolved',
		'warning' => 'Closed and Unresolved',
	];
	
	protected $result;
	protected $enableComments;
	
	public function init()
	{
		parent::init();
		$this->model = new Issues(['scenario' => 'default']);
		$this->enableComments = (\Yii::$app->request->get(Issues::COMMENT_PARAM) == true) ? true : false;
	}
	
    public function behaviors()
    {
		$behaviors = [
			'access' => [
				'rules' => [
					[
						'actions' => ['issues', 'duplicate'],
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
		];
        return array_merge_recursive(parent::behaviors(), $behaviors);
    }
	
	public static function has()
	{
		$has = [
			'\nitm\widgets\issueTracker'
		];
		return array_merge(parent::has(), $has);
	}

    /**
     * Lists all Issues models.
	 * @param string $type The parent type of the issue
	 * @param int $id The id of the parent
     * @return mixed
     */
    public function actionIndex($type, $id)
    {
		Response::$viewOptions = [
			'args' => [
				"content" => IssueTracker::widget([
					"parentId" => $id, 
					"parentType" => $type,
					'useModal' => false,
					'enableComments' => \Yii::$app->request->get(Issues::COMMENT_PARAM)
				])
			],
			'modalOptions' => [
				'contentOnly' => true
			]
		];
		return $this->renderResponse(null, null, \Yii::$app->request->isAjax);
    }

    /**
     * Displays a single Issues model.
     * @param integer $id
     * @return mixed
     */
    public function actionIssues($type, $id, $key=null)
    {
		switch($type)
		{
			case 'all':
			$this->model = new Issues();
			break;
			
			default:	
			$this->model = Issues::findModel([$id, $type]);
			break;
		}
		$searchModel = new IssuesSearch;
		$searchModel->addWith(['closedBy', 'resolvedBy']);
		$get = \Yii::$app->request->getQueryParams();
		$params = array_merge($get, $this->model->constraints);
		unset($params['type'], $params['id'], $params['key']);
		
		$options = [
			'enableComments' => $this->enableComments
		];
		switch($key)
		{
			case 'duplicate':
			$params = array_merge($params, ['duplicate' => 1]);
			$orderBy = ['id' => SORT_DESC];
			break;
			
			case 'closed':
			$params = array_merge($params, ['closed' => 1]);
			$orderBy = ['closed_at' => SORT_DESC];
			break;
			
			case 'open':
			$params = array_merge($params, ['closed' => 0]);
			$orderBy = ['id' => SORT_DESC];
			break;
			
			case 'resolved':
			$params = array_merge($params, ['resolved' => 1]);
			$orderBy = ['resolved_at' => SORT_DESC];
			break;
			
			case 'unresolved':
			$params = array_merge($params, ['resolved' => 0]);
			$orderBy = ['id' => SORT_DESC];
			break;
			
			default:
			$orderBy = [];
			break;
		}
		$dataProvider = $searchModel->search($params);
		$dataProvider->query->orderBy($orderBy);
		Response::viewOptions(null, [
			'args' => [
				"content" => $this->renderAjax('issues', [
					'enableComments' => $this->enableComments,
					'searchModel' => $searchModel,
					'dataProvider' => $dataProvider,
					'options' => $options,
					'parentId' => $id,
					'parentType' => $type,
					'filterType' => $key
				])
			],
			'modalOptions' => [
				'contentOnly' => true
			]
		], true);
		//$this->setResponseFormat(\Yii::$app->request->isAjax ? 'modal' : 'html');
		return $this->renderResponse(null, null, \Yii::$app->request->isAjax);
    }

    /**
     * Displays a single Issues model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel(Issues::className(), $id),
        ]);
    }

    /**
     * Creates a new Issues model.
	 * @param string $type The parent type of the issue
	 * @param int $id The id of the parent
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
		$post = \Yii::$app->request->post();
		$this->model->setScenario('create');
		$this->model->load($post);
		switch(\Yii::$app->request->isAjax && (@\nitm\helpers\Helper::boolval($_REQUEST['do']) !== true))
		{
			case true:
			$this->setResponseFormat('json');
            return \yii\widgets\ActiveForm::validate($this->model);
			break;
		}
		return $this->finalAction();
    }

    /**
     * Updates an existing Issues model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
	 * @param string $scenario
     * @return mixed
     */
    public function actionUpdate($id, $scenario='update')
    {
		$post = \Yii::$app->request->post();
        $this->model = $this->findModel(Issues::className(), $id);
		$this->model->edits++;
		$this->model->setScenario($scenario);
		$this->model->load($post);
		switch(\Yii::$app->request->isAjax && (@\nitm\helpers\Helper::boolval($_REQUEST['do']) !== true))
		{
			case true:
			$this->setResponseFormat('json');
            return \yii\widgets\ActiveForm::validate($this->model);
			break;
		}
		return $this->finalAction();
    }
	
	public function actionClose($id)
	{
		\Yii::$app->request->setQueryParams([]);
		return $this->booleanAction($this->action->id, $id);
	}
	
	public function actionDuplicate($id)
	{
		\Yii::$app->request->setQueryParams([]);
		return $this->booleanAction($this->action->id, $id);
	}
	
	public function actionResolve($id)
	{
		\Yii::$app->request->setQueryParams([]);
		return $this->booleanAction($this->action->id, $id);
	}
	
	protected function booleanAction($action, $id)
	{
		\Yii::$app->request->setQueryParams([]);
        $this->model = $this->findModel(Issues::className(), $id);
		switch($action)
		{
			case 'close':
			$scenario = 'close';
			$attributes = [
				'attribute' => 'closed',
				'blamable' => 'closed_by',
				'date' => 'closed_at'
			];
			break;
			
			case 'resolve':
			$scenario = 'resolve';
			$attributes = [
				'attribute' => 'resolved',
				'blamable' => 'resolved_by',
				'date' => 'resolved_at'
			];
			break;
			
			case 'duplicate':
			$scenario = 'duplicate';
			$this->model->load(\Yii::$app->request->post());
			switch(is_array($this->model->duplicate_id))
			{
				case true:
				$this->model->duplicate_id = implode(',', $this->model->duplicate_id);
				break;
			}
			$attributes = [
				'attribute' => 'duplicate',
				'blamable' => 'duplicated_by',
			];
			break;
		}
		$this->model->setScenario($scenario);
		$this->result = !$this->model->getAttribute($attributes['attribute']) ? 1 : 0;
		foreach($attributes as $key=>$value)
		{
			switch($key)
			{
				case 'blamable':
				$this->model->setAttribute($value, (!$this->result ? null : \Yii::$app->user->getId()));
				break;
				
				case 'date':
				$this->model->setAttribute($value, (!$this->result ? null : new \yii\db\Expression('NOW()')));
				break;
			}
		}
		$this->model->setAttribute($attributes['attribute'], $this->result);
		$this->setResponseFormat('json');
		return $this->finalAction();
	}

    /**
     * Deletes an existing Issues model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }
	
	/**
	 * Put here primarily to handle action after create/update
	 */
	protected function finalAction($args=[])
	{
		$ret_val = is_array($args) ? $args : [
			'success' => false,
		];
        if ($this->model->save()) {
			switch(\Yii::$app->request->isAjax)
			{
				case true:
				switch($this->action->id)
				{
					case 'close':
					case 'duplicate':
					case 'resolve':
					$ret_val['success'] = true;
					switch($this->action->id)
					{
						case 'resolve':
						$attribute = 'resolved';
						$ret_val['title'] = ($this->model->$attribute == 0) ? 'Resolve' : 'Un-Resolve';
						break;
						
						case 'close':
						$attribute = 'closed';
						$ret_val['title'] = ($this->model->$attribute == 0) ? 'Close' : 'Open';
						break;
						
						case 'duplicate':
						$attribute = 'duplicate';
						$ret_val['title'] = ($this->model->$attribute == 0) ? 'Set to duplicate' : 'Set to not duplicate';
						break;
					}
					$ret_val['actionHtml'] = Icon::forAction($this->action->id, $attribute, $this->model);
					$ret_val['data'] = $this->result;
					$ret_val['class'] = 'wrapper '.\nitm\helpers\Statuses::getIndicator($this->model->getStatus());
					break;
					
					default:
					$format = Response::formatSpecified() ? $this->getResponseFormat() : 'json';
					$this->setResponseFormat($format);
					$this->model->created_at = \nitm\helpers\DateFormatter::formatDate($this->model->created_at);
					switch($this->action->id)
					{
						case 'update':
						$this->model->updated_at = \nitm\helpers\DateFormatter::formatDate($this->model->updated_at);
						break;
					}
					switch($this->getResponseFormat())
					{
						case 'json':
						$ret_val = [
							'data' => $this->renderAjax('view', ["model" => $this->model]),
							"enableComments" => $this->enableComments,
							'success' => true
						];
						break;
						
						default:
						Response::$viewOptions['content'] = $this->renderPartial('view', [
							"model" => $this->model,
							"enableComments" => $this->enableComments,
						]);
						break;
					}
					break;
				}
				break;
					
				default:
				return $this->redirect(['index']);
				break;
			}
        }
		$ret_val['action'] = $this->action->id;
		$ret_val['id'] = $this->model->id;
		return $this->renderResponse($ret_val, Response::$viewOptions, \Yii::$app->request->isAjax);
	}
}
