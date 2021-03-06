<?php

namespace nitm\widgets\controllers;

use nitm\widgets\models\Vote;

/**
 * Upvoting based on democratic one vote per user system
 *
 */

class VoteController extends \nitm\controllers\DefaultController
{

	public function behaviors()
	{
		$behaviors = [
			'access' => [
				//'class' => \yii\filters\AccessControl::className(),
				'only' => ['down', 'up', 'reset'],
				'rules' => [
					[
						'actions' => ['down', 'up', 'reset'],
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
			'verbs' => [
				//'class' => \yii\filters\VerbFilter::className(),
				'actions' => [
					'down' => ['post'],
					'up' => ['post'],
					'reset' => ['post'],
				],
			],
		];

		return array_merge_recursive(parent::behaviors(), $behaviors);
	}

	/**
	 * Place a downvote
	 * @param string $type the type of object
	 * @param int $id the id
	 * @return boolean should we allow more downvoting?
	 */
    public function actionDown($type, $id)
    {
		$ret_val = ['success' => false, 'value' => null, 'id' => (int)$id];
		$existing = new Vote();
		$existing->queryOptions['andWhere'] = [
			'author_id' => \Yii::$app->user->getId(),
			'parent_type' => $type,
			'parent_id' => $id
		];
		$vote = $existing->find()->where($existing->queryOptions['andWhere'])->one();
		switch($vote instanceof Vote)
		{
			case false:
			$vote = new Vote();
			$vote->setScenario('create');
			$vote->load([
				'parent_type' => $type,
				'parent_id' => $id,
				'author_id' => \Yii::$app->user->getId()
			]);
			break;

			default:
			$vote->setScenario('update');
			break;
		}

		if(Vote::$allowMultiple)
			$vote->value -= 1;
		else
			$vote->value = ($vote->value == 1 || $vote->value == -1 ? 0 : -1);

		$ret_val['success'] = $vote->save();
		unset($existing->queryOptions['andWhere']['author_id']);
		//Recalculate the fetched value after updating the new vote value
		$vote->fetchedValue;
		$ret_val['value'] = $vote->rating(null, true);
		$ret_val['atMax'] = Vote::$allowMultiple ? false : ($vote->value == 1);
		$ret_val['atMin'] = Vote::$allowMultiple ? false : ($vote->value == -1);
		$ret_val['class'] = $vote->getIndicators();
		$this->setResponseFormat('json');
		return $this->renderResponse($ret_val);
    }

	/**
	 * Place an upvote
	 * @param string $type the type of object
	 * @param int $id the id
	 * @return boolean should we allow more upvoting?
	 */
    public function actionUp($type, $id)
    {
		$ret_val = ['success' => false, 'value' => null, 'id' => (int)$id];
		$existing = new Vote();
		$existing->queryOptions['andWhere'] = [
			'author_id' => \Yii::$app->user->getId(),
			'parent_type' => $type,
			'parent_id' => $id
		];
		$vote = $existing->find()->where($existing->queryOptions['andWhere'])->one();
		switch($vote instanceof Vote)
		{
			case false:
			$vote = new Vote();
			$vote->setScenario('create');
			$vote->load([
				'Vote' => [
					'parent_type' => $type,
					'parent_id' => $id,
					'author_id' => \Yii::$app->user->getId()
				]
			]);
			break;

			default:
			$vote->setScenario('update');
			break;
		}

		$originalValue = $vote->value;

		if(Vote::$allowMultiple)
			$vote->value += 1;
		else
			$vote->value = ($vote->value == 1 || $vote->value == -1 ? 0 : 1);

		$ret_val['success'] = $vote->save();
		unset($existing->queryOptions['andWhere']['author_id']);
		//Recalculate the fetched value after updating the new vote value
		$vote->fetchedValue;
		$ret_val['value'] = $vote->rating(null, true);
		$ret_val['atMax'] = Vote::$allowMultiple ? false : ($vote->value == 1);
		$ret_val['atMin'] = Vote::$allowMultiple ? false : ($vote->value == -1);
		$ret_val['class'] = $vote->getIndicators();
		$this->setResponseFormat('json');
		return $this->renderResponse($ret_val);
    }

	/**
	 * Place an upvote
	 * @param string $type the type of object
	 * @param int $id the id
	 * @return boolean should we allow more upvoting?
	 */
    public function actionReset($type, $id)
    {
		$ret_val = ['success' => false, 'value' => null, 'id' => $id];
		switch(\Yii::$app->user->identity->isAdmin())
		{
			case true:
			$deleted = Vote::deleteAll([
				'parent_id' => $id,
				'parent_type' => $type
			]);
			$ret_val['success'] = $deleted;
			break;
		}
		$this->setResponseFormat('json');
		return $this->renderResponse($ret_val);
    }
}
