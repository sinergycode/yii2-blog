<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use sinergycode\blog\models\Blog;
/* @var $this yii\web\View */
/* @var $searchModel sinergycode\blog\models\BlogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Blogs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="blog-index">

    <!--<h1><?= Html::encode($this->title) ?></h1>-->
    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Blog', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            [
                'class' => 'yii\grid\ActionColumn', 
                'template' => '{view} {update} {delete} {check}',
                'buttons' => [
                    'check' => function($url, $model, $key) {
                        return Html::a('<i class="fa fa-check" aria-hidden="true"></i>', $url);
                    }
                ],
                'visibleButtons' => [
                    'check' => function($model, $key, $index) {
                        return ($model->status_id == 0) ? false : true;
                    }
                ],
            ],
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'title',
//            'text:ntext',
            ['attribute' => 'url', 'format' => 'raw', 'headerOptions' => ['class' => 'aoaoaoaoa']],
            ['attribute' => 'status_id', 
                            'filter' => Blog::STATUS_LIST, 
                            'value' => function($model) {
                                            return $model->getStatusName();
                                       }
            ],
            'sort',
            'smallImage:image',
            'date_create:datetime',
            'date_update:datetime',
            ['attribute' => 'tags', 'value' => 'TagsAsString']
        ],
    ]); ?>
    <?php Pjax::end(); ?>
</div>
