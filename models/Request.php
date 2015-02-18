<?php

namespace nitm\widgets\models;

/**
 * This is the model class for table "Request".
 *
 * @property integer $id
 * @property string $added
 * @property string $completed_by
 * @property string $closed_by
 * @property string $title
 * @property string $author_id
 * @property string $edited
 * @property string $editor_id
 * @property integer $edits
 * @property string $request
 * @property string $type
 * @property string $request_for
 * @property string $status
 * @property integer $completed
 * @property string $completed_on
 * @property integer $closed
 * @property string $closed_on
 * @property integer $rating
 * @property string $rated_on
 */
class Request extends \nitm\models\Entity
{	
	use \nitm\traits\Relations, \nitm\widgets\traits\relations\Request, \nitm\widgets\traits\Relations;
	
	protected $is = 'request';
	
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'requests';
    }
	
	/*
	 * Initialize with 
	 */
	public function init()
	{
		parent::init();
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title','request', 'type_id', 'request_for_id'], 'required'],
            [['added', 'edited', 'completed_on', 'closed_on', 'rated_on'], 'safe'],
            [['title', 'request', 'type', 'request_for'], 'string'],
            [['edits', 'completed', 'closed', 'rating', 'status'], 'integer'],
            [['completed_by', 'closed_by'], 'string', 'max' => 64],
			[['title'], 'unique', 'targetAttribute' => ['title', 'type_id', 'request_for_id'], 'message' => 'There is a request exactly matching this one. Please update that one instead']
        ];
    }
	
	public function scenarios()
	{
		$scenarios = [
			'create' => ['title', 'request', 'type_id', 'request_for_id', 'status'],
			'create' => ['title', 'request', 'type_id', 'request_for_id', 'status'],
			'complete' => ['completed', 'completed_at', 'closed'],
			'close' => ['closed', 'closed_at', 'completed', 'resolved'],
		];
		return array_merge(parent::scenarios(), $scenarios);
	}

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'added' => 'Added',
            'completed_by' => 'Completed By',
            'closed_by' => 'Closed By',
            'title' => 'Title',
            'author_id' => 'Author',
            'edited' => 'Edited',
            'editor_id' => 'Editor',
            'edits' => 'Edits',
            'request' => 'Request',
            'type' => 'Type',
            'request_for' => 'Request For',
            'status' => 'Status',
            'completed' => 'Completed',
            'completed_on' => 'Completed On',
            'closed' => 'Closed',
            'closed_on' => 'Closed On',
            'rating' => 'Rating',
            'rated_on' => 'Rated On',
        ];
    }
	
	public static function filters()
	{
		return array_merge(
			parent::filters(),
			[
				'type' => null,
				'request_for' => null,
				'completed' => null,
				'closed' => null,
				'order_by' => null,
				'show' => null,
				'status' => null,
			]
		);
	}
	
	public function afterSaveEvent($event)
	{
		$event->data['variables'] = array_merge((array)$event->data['variables'], [
			'%type%' => $event->sender->typeOf()->name,
			'%requestFor%' => $event->sender->requestFor()->name,
			'%urgency%' => $event->sender->getUrgency(),
			'%title%' => $event->sender->title,
		]);
		parent::afterSaveEvent($event);
	}
}
