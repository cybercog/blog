<?php

namespace amilna\blog\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use amilna\blog\models\StaticPage;

/**
 * StaticPageSearch represents the model behind the search form about `amilna\blog\models\StaticPage`.
 */
class StaticPageSearch extends StaticPage
{

	
	public $term;
	
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'status', 'isdel'], 'integer'],
            [['title', 'term', 'description', 'content', 'tags', 'time'], 'safe'],
        ];
    }
	
	public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(),[
            'term' => Yii::t('app', 'Search'),                 
        ]);
    }
	
	/* uncomment to undisplay deleted records (assumed the table has column isdel) */
	public static function find()
	{
		return parent::find()->where([StaticPage::tableName().'.isdel' => 0]);
	}	

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

	private function queryString($fields)
	{		
		$params = [];
		foreach ($fields as $afield)
		{
			$field = $afield[0];
			$tab = isset($afield[1])?$afield[1]:false;			
			if (!empty($this->$field))
			{				
				if (substr($this->$field,0,2) == "< " || substr($this->$field,0,2) == "> " || substr($this->$field,0,2) == "<=" || substr($this->$field,0,2) == ">=" || substr($this->$field,0,2) == "<>") 
				{					
					array_push($params,[str_replace(" ","",substr($this->$field,0,2)), "lower(".($tab?$tab.".":"").$field.")", strtolower(trim(substr($this->$field,2)))]);
				}
				else
				{					
					array_push($params,["like", "lower(".($tab?$tab.".":"").$field.")", strtolower($this->$field)]);
				}				
			}
		}	
		return $params;
	}	
	
	private function queryNumber($fields)
	{		
		$params = [];
		foreach ($fields as $afield)
		{
			$field = $afield[0];
			$tab = isset($afield[1])?$afield[1]:false;			
			if (!empty($this->$field))
			{				
				$number = explode(" ",trim($this->$field));							
				if (count($number) == 2)
				{									
					if (in_array($number[0],['>','>=','<','<=','<>']) && is_numeric($number[1]))
					{
						array_push($params,[$number[0], ($tab?$tab.".":"").$field, $number[1]]);	
					}
				}
				elseif (count($number) == 3)
				{															
					if (is_numeric($number[0]) && is_numeric($number[2]))
					{
						array_push($params,['>=', ($tab?$tab.".":"").$field, $number[0]]);		
						array_push($params,['<=', ($tab?$tab.".":"").$field, $number[2]]);		
					}
				}
				elseif (count($number) == 1)
				{					
					if (is_numeric($number[0]))
					{
						array_push($params,['=', ($tab?$tab.".":"").$field, str_replace(["<",">","="],"",$number[0])]);		
					}	
				}
			}
		}	
		return $params;
	}
	
	private function queryTime($fields)
	{		
		$params = [];
		foreach ($fields as $afield)
		{
			$field = $afield[0];
			$tab = isset($afield[1])?$afield[1]:false;			
			if (!empty($this->$field))
			{				
				$time = explode(" - ",$this->$field);			
				if (count($time) > 1)
				{								
					array_push($params,[">=", "concat('',".($tab?$tab.".":"").$field.")", $time[0]]);	
					array_push($params,["<=", "concat('',".($tab?$tab.".":"").$field.")", $time[1]." 24:00:00"]);
				}
				else
				{
					if (substr($time[0],0,2) == "< " || substr($time[0],0,2) == "> " || substr($time[0],0,2) == "<=" || substr($time[0],0,2) == ">=" || substr($time[0],0,2) == "<>") 
					{					
						array_push($params,[str_replace(" ","",substr($time[0],0,2)), "concat('',".($tab?$tab.".":"").$field.")", trim(substr($time[0],2))]);
					}
					else
					{					
						array_push($params,["like", "concat('',".($tab?$tab.".":"").$field.")", $time[0]]);
					}
				}	
			}
		}	
		return $params;
	}

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = $this->find();
        
                
        $query->joinWith([/**/]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        
        /* uncomment to sort by relations table on respective column*/
        
        $dataProvider->sort->attributes['term'] = [			
			'asc' => ['title' => SORT_ASC],
			'desc' => ['title' => SORT_DESC],
		];

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }				
		
        $params = self::queryNumber([['id'],['status'],['isdel']]);
		foreach ($params as $p)
		{
			$query->andFilterWhere($p);
		}
        $params = self::queryString([['title'],['description'],['content'],['tags']]);
		foreach ($params as $p)
		{
			$query->andFilterWhere($p);
		}
        $params = self::queryTime([['time']]);
		foreach ($params as $p)
		{
			$query->andFilterWhere($p);
		}				
		
		if ($this->term)
		{
			$query->andFilterWhere(["OR","lower(title) like '%".strtolower($this->term)."%'",
				["OR","lower(description) like '%".strtolower($this->term)."%'",
					["OR","lower(tags) like '%".strtolower($this->term)."%'",
						["OR","lower(content) like '%".strtolower($this->term)."%'",
							"{{%blog_static}}.id = ANY ('".$res."')"
						]
					]
				]
			]);
		}
		
        return $dataProvider;
    }
}
