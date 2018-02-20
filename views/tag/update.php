<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model sinergycode\blog\models\Tag */

$this->title = 'Update Tag: {nameAttribute}';
$this->params['breadcrumbs'][] = ['label' => 'Tags', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="tag-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
