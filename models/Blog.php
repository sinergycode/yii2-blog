<?php

namespace sinergycode\blog\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\db\Expression;
use yii\behaviors\TimestampBehavior;
use yii\web\UploadedFile;
use yii\helpers\Url;
use common\components\behaviors\StatusBehavior;
use common\models\User;
use common\models\ImageManager;

/**
 * This is the model class for table "blog".
 *
 * @property int $id
 * @property string $title
 * @property string $text
 * @property string $url
 * @property string $date_create
 * @property string $date_update
 * @property string $image
 * @property int $status_id
 * @property int $sort
 */
class Blog extends \yii\db\ActiveRecord
{
    const STATUS_LIST = ['off', 'on'];
    public $tags_array;
    public $file;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'blog';
    }
    
    public function behaviors()
    {
        return [
            'timestampBehavior' => [ // поскольку есть название то мы можем при тех или иных условиях это поведение отключать
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'date_create',
                'updatedAtAttribute' => 'date_update',
                'value' => new Expression('NOW()'),
            ],
            'statusBehavior' => [
                'class' => StatusBehavior::classname(),
                'statusList' => self::STATUS_LIST,
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'url'], 'required'],
            [['url'], 'unique'],
            [['text'], 'string'],
            [['status_id', 'sort'], 'integer'],
            [['sort'], 'integer', max => 99, 'min' => '1' ],
            [['title', 'url'], 'string', 'max' => 150],
            [['image'], 'string', 'max' => 100], // название файла
            [['file'], 'image'], // сам файл
            [['tags_array', 'date_create', 'date_update'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'text' => 'Text',
            'url' => 'Url',
            'status_id' => 'Status ID',
            'sort' => 'Сортировка',
            'tags_array' => 'Тэги',
            'image' => 'Картинка',
            'file' => 'Картинка',
            'tagsAsString' => 'Тэги',
            'author.username' => 'Автор',
            'date_create' => 'Создано',
            'date_update' => 'Обновлено',
        ];
    }
    
    public function getAuthor() {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    // связь для загрузки нескольких фото
    public function getImages(){
        return $this->hasMany(ImageManager::className(), ['item_id' => 'id'])->andWhere(['class'=>self::tableName()])->orderBy('sort');
    }
    
    public function getImagesLinks() {
        return ArrayHelper::getColumn($this->images,'imageUrl');
    }
    
    public function getImagesLinksData() {
        return ArrayHelper::toArray($this->images,[
                ImageManager::className() => [
                    'caption'=>'name',
                    'key'=>'id',
                ]]
        );
    }
    
    public function getBlogTag() {
        return $this->hasMany(BlogTag::className(), ['blog_id' => 'id']);
    }
    
    public function getTags() {
        return $this->hasMany(Tag::className(), ['id' => 'tag_id'])->via('blogTag');
    }

    // это событие запускается когда удаляется один блог $blog. Т.е. если мы удалим блог, то надо удалить и 
    // все связанные тэг. Мы удаляем не сами тэги из Teg а только связи из BlogTag для данного блога
    public function beforeDelete() {
        if(parent::beforeDelete()){
            BlogTag::deleteAll(['blog_id' => $this->id]);
            return true;
        }else {
            return false;
        }
    }
        
    // функия для index.php
    public function getSmallImage() {
        if($this->image) {
            $path = str_replace('admin.', '', Url::home(true)) . 'uploads/images/blog/50x50/' . $this->image;
        }else {
            $path = str_replace('admin.', '', Url::home(true)) . 'uploads/images/no-image.svg';
        } 
        return  $path;
    }
 
    // функия для index.php, view.php
    public function getTagsAsString() {
        $arr = ArrayHelper::map($this->tags, 'id', 'name');
        return  implode(', ', $arr);
    }
    
    // когда произошел запрос в базе данных - модели $blog заполнилась данными
    // нужно чтобы сразу после этого она в свою переменную $tags_array засунула данные из модели
    // происходит когда мы загрузили данные из базы в модель: когда мы нажимаем update в эту форму подтягиваются тэги которые связаны. 
    // Массив с объектами который хранится скармливаем виждету Select2 (tag-ключ, tag-значение - так нужно виждету)
    public function afterFind() {
        parent::afterFind();
        $this->tags_array = ArrayHelper::map($this->tags, 'name', 'id');
    }

    // функция для загрузки отдельных от редактора фоток - kartig widget
    public function beforeSave() {
        if($file = UploadedFile::getInstance($this, 'file')) {
            $dir = Yii::getAlias("@images") .'/blog/';
            if (!is_dir($dir . $this->image)) {
                if(file_exists($dir . $this->image)) {
                    unlink($dir . $this->image);
                }
                if(file_exists($dir . '50x50/' . $this->image)) {
                    unlink($dir . '50x50/' . $this->image);
                }
                if(file_exists($dir . '800x/' . $this->image)) {
                    unlink($dir . '800x/' . $this->image);
                } 
            }
            $this->image = strtotime('now') . '_' . Yii::$app->getSecurity()->generateRandomString(6) . '.' . $file->extension;
            $file->saveAs($dir . $this->image);
            $imag = Yii::$app->image->load($dir . $this->image);
            $imag->background('#fff', 0);
            $imag->resize('50', '50', Yii\image\drivers\Image::INVERSE);
            $imag->save($dir . '50x50/' . $this->image, 90);
            $imag = Yii::$app->image->load($dir . $this->image);
            $imag->background('#fff', 0);
            $imag->resize('800', null, Yii\image\drivers\Image::INVERSE);
            $imag->save($dir . '800x/' . $this->image, 90);
        }
        return parent::beforeSave($insert);
    }

    
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        
        if(is_array($this->tags_array)) {
            $old_tags = ArrayHelper::map($this->tags, 'id', 'id'); // список тэгов при загрузке update взятые из tag через blog_tag
            foreach ($this->tags_array as $tag_id) { // изменненый список тэгов --> который select2 дополняет или удаляет в tags_array. $tag_id = tag_id или имя нового тега
                if(isset($old_tags[$tag_id])) { // если тэг есть в старом массиве - ничего не делаем
                    unset($old_tags[$tag_id]); // и удаляем его из старого массива. Здесь остаются тэги которые надо удалить
                } 
                else { // если тэга нету в старом массиве
                    $this->createNewTag($tag_id); // добавляем в список или создаем и добавляем в список если нету
                }
            }
            BlogTag::deleteAll(['tag_id' => $old_tags, 'blog_id' => $this->id]);
        } else  {
            BlogTag::deleteAll(['blog_id' => $this->id]);
        }
    }

    public function createNewTag($new_tag) {
        if(!$tag = Tag::find()->andWhere(['id' => $new_tag])->one()) { // ищем если есть уже такие тэги (при создании)
            $tag = new Tag(); // если нету, то создаем
            $tag->name = $new_tag;
            if(!$tag->save()) { // если произошли ошибки при сохранении 
                $tag = null;
                Yii::$app->session->addFlash('error', 'Тэг ' . $new_tag .  ' не создался');
            }else{
                Yii::$app->session->addFlash('success', 'создан тег ' . $new_tag);
            }
        } // если такой тэг нашелся, то все ок, новый создавать не надо
        if($tag instanceof Tag) { // если новый тэг сохранился то здесь имеем объект который является экземпляром класса Tag
            $blog_tag = new BlogTag(); // создаем новую связь
            $blog_tag->blog_id = $this->id;
            $blog_tag->tag_id = $tag->id;
            if($blog_tag->save()) { // если связь сохранилась
//                Yii::$app->session->addFlash('success', 'Тэг ' . $new_tag .  ' добавлен');
                return $blog_tag->id;
            } else {
                Yii::$app->session->addFlash('success', 'Тег ' . $new_tag . 'не добавлен');
            }
        }
    }
    
    
    public function CreateDirectory() {
        $dir = Yii::getAlias('@images') . '/blog_azi/';
            if(!file_exists($dir)) {
                Yii::$app->session->addFlash('error', 'file not exist, need to create');
                if(\yii\helpers\FileHelper::createDirectory($dir)) {
                    Yii::$app->session->addFlash('success', 'created');
                }else{
                    Yii::$app->session->addFlash('error', 'not created');
                }
            }
            else {
                Yii::$app->session->addFlash('error', 'even exist');
            }
    }

}
