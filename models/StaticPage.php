<?php

namespace amilna\blog\models;

use Yii;

/**
 * This is the model class for table "{{%blog_static}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property string $content
 * @property string $tags
 * @property integer $status
 * @property string $time
 * @property integer $isdel
 */
class StaticPage extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%blog_static}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'description', 'content'], 'required'],
            [['content'], 'string'],
            [['status', 'isdel'], 'integer'],
            [['time'], 'safe'],
            [['title'], 'string', 'max' => 65],
            [['description'], 'string', 'max' => 155],
            [['tags'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'title' => Yii::t('app', 'Title'),
            'description' => Yii::t('app', 'Description'),
            'content' => Yii::t('app', 'Content'),
            'tags' => Yii::t('app', 'Tags'),
            'status' => Yii::t('app', 'Status'),
            'time' => Yii::t('app', 'Time'),
            'isdel' => Yii::t('app', 'Isdel'),
        ];
    }	
    
	public function itemAlias($list,$item = false,$bykey = false)
	{
		$lists = [
			/* example list of item alias for a field with name field */			
			'status'=>[							
						0=>Yii::t('app','Draft'),							
						1=>Yii::t('app','Published'),
						2=>Yii::t('app','Archived'),
					],			
			
		];				
		
		if (isset($lists[$list]))
		{					
			if ($bykey)
			{				
				$nlist = [];
				foreach ($lists[$list] as $k=>$i)
				{
					$nlist[$i] = $k;
				}
				$list = $nlist;				
			}
			else
			{
				$list = $lists[$list];
			}
							
			if ($item !== false)
			{			
				return	(isset($list[$item])?$list[$item]:false);
			}
			else
			{
				return $list;	
			}			
		}
		else
		{
			return false;	
		}
	}    
    
    public function getTags()
	{
		$models = $this->find()->all();
		$tags = [];
		foreach ($models as $m)
		{
			$ts = explode(",",$m->tags);
			foreach ($ts as $t)
			{	
				if (!in_array($t,$tags))
				{
					array_push($tags,$t);	
				}
			}	
		}
		return $tags;
	}
}
