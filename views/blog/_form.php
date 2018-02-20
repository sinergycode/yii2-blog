<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url; 
use yii\helpers\ArrayHelper;
use sinergycode\blog\models\Tag;
use sinergycode\blog\models\Blog;
use vova07\imperavi\Widget;
use kartik\select2\Select2;
use kartik\file\FileInput;
use yii\web\JsExpression;


/* @var $this yii\web\View */
/* @var $model sinergycode\blog\models\Blog */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="blog-form">

    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
    ]); ?>

    <!-- Благодаря тому что мы с помощью col-xs-6 сделали все в два ряда, то обернем в див чтобы не вылезала фото -->
    <div class="row"> 
        
    <?= $form->field($model, 'file', ['options' => ['class' => 'col-xs-6']])->widget(\kartik\file\FileInput::classname(), [
        'options' => ['accept' => 'image/*'],
        'pluginOptions' => [
            'showCaption' => false,
            'showRemove' => false,
            'showUpload' => false,
            'browseClass' => 'btn btn-primary btn-block',
            'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
            'browseLabel' =>  'Выбрать фото'
        ],
    ]) ?>
        
    <?= $form->field($model, 'title', ['options' => ['class' => 'col-xs-6']])->textInput(['maxlength' => true]) ?>
    
    <?= $form->field($model, 'url', ['options' => ['class' => 'col-xs-6']])->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status_id', ['options' => ['class' => 'col-xs-6']])->dropDownList(Blog::STATUS_LIST) ?>

    <?= $form->field($model, 'sort', ['options' => ['class' => 'col-xs-6']])->textInput() ?>
        
    <?= $form->field($model, 'tags_array', ['options' => ['class' => 'col-xs-6']])->widget(kartik\select2\Select2::classname(), [
        'data' => ArrayHelper::map(Tag::find()->all(), 'id', 'name'),
//        'data' => [
//            'ключ1' => 'значение1',
//            'ключ2' => 'значение2'
//        ],
        'language' => 'ru',
        'options' => [
            'placeholder' => 'Выбрать тэг ...', 
            'multiple' => true
            ],
        'pluginOptions' => [
            'allowClear' => true,
            'tags' => true,
            'maximumInputLength' => 10,
        ],
    ]);
    ?>
    </div>
    
        <?= $form->field($model, 'text')->widget(Widget::className(), [
        'settings' => [
            'lang' => 'ru',
            'minHeight' => 200,
            'formatting' => ['p', 'blockquote', 'h2'],
            'imageUpload' => \yii\helpers\Url::to(['site/save-redactor-image', 'sub' => 'blog']),
//            'imageUpload' => \yii\helpers\Url::to(['site/native-imperavi']),
            'plugins' => [
                'clips',
                'fullscreen',
                'imagemanager'
            ],
        ],
    ]); ?>
        
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
        <?= FileInput::widget([
        'name' => 'ImageManager[attachment]',
        'options'=>[
            'multiple'=>true
        ],
        'pluginOptions' => [
            'deleteUrl' => Url::toRoute(['/blog/delete-image']),
            'initialPreview'=> $model->imagesLinks,
            'initialPreviewAsData'=>true,
            'overwriteInitial'=>false,
            'initialPreviewConfig'=>$model->imagesLinksData,
            'uploadUrl' => Url::to(['/site/save-img']),
            'uploadExtraData' => [
                'ImageManager[class]' => $model->formName(),
                'ImageManager[item_id]' => $model->id
            ],
            'maxFileCount' => 10
        ],
        'pluginEvents' => [
            'filesorted' => new JsExpression('function(event, params){
                  $.post("'.Url::toRoute(["/blog/sort-image","id"=>$model->id]).'",{sort: params});
            }')
        ],
     ]);?>
    
</div>
