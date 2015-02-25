<?php

namespace nitm\widgets\models;

/**
 * This is the model class for table "rating".
 *
 * @property integer $id
 * @property integer $author_id
 * @property string $created_at
 * @property string $rating
 * @property string $parent_type
 * @property integer $parent_id
 */
class Rating extends BaseWidget
{
	
	public function scenarios()
	{
		$scenarios = [
			'create' => ['parent_id', 'parent_type', 'user_id', 'rating'],
			'update' => ['parent_id', 'parent_type', 'rating'],
			'upVote' => ['parent_id', 'parent_type'],
			'downVote' => ['parent_id', 'parent_type'],
		];
		return array_merge(parent::scenarios(), $scenarios);
	}
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rating';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'author_id', 'created_at', 'rating', 'parent_type', 'parent_id'], 'required'],
            [['id', 'author_id', 'parent_id'], 'integer'],
            [['created_at'], 'safe'],
            [['rating'], 'string'],
            [['parent_type'], 'string', 'max' => 64],
			[['parent_id', 'parent_type'], 'required', 'on' => ['count', 'upVote', 'downVote', 'create', 'update']],
			[['user_id'], 'required', 'on' => ['create']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'author_id' => 'Author',
            'created_at' => 'Created At',
            'rating' => 'Rating',
            'parent_type' => 'parent Type',
            'parent_id' => 'parent ID',
        ];
    }
	
	/**
	 * Get the rating, percentage out of 100%
	 * @return int
	 */
	public function rating()
	{
		$ret_val = 0;
		$userCount = User::find()->where(['disabled' => 0])->count();
		switch(is_null($userCount))
		{
			case false:
			$ret_val = ($this->count()/$userCount) * 100;
			break;
		}
		return $ret_val;
	}
	
	public function getCurrentUserVoted()
	{
		$primaryKey = $this->primaryKey()[0];
		return $this->hasOne(static::className(), [
			'parent_type' => 'parent_type',
			'parent_id' => 'parent_id'
		])->andWhere(['author_id' => static::$currentUser->getId()]);
	}
}
