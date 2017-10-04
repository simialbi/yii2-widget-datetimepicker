# yii2-widget-datetimepicker
This extension provides a `date`, `time` or `datetime` picker widget for yii2 framework in bootstrap style. It's based 
on [Bootstrap Datetimepicker](http://eonasdan.github.io/bootstrap-datetimepicker/) from [Eonasdan](https://github.com/Eonasdan).
 
## Resources
 * [yii2](https://github.com/yiisoft/yii2) framework
 * [Bootstrap Datetimepicker](http://eonasdan.github.io/bootstrap-datetimepicker/)
 
## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ php composer.phar require --prefer-dist simialbi/yii2-widget-datetimepicker
```

or add 

```
"simialbi/yii2-widget-datetimepicker": "*"
```

to the ```require``` section of your `composer.json`

## Example Usage

To include datepicker instance in one of your pages, call the widget like this:
```php
<?php
/* @var $this yii\web\View */
/* @var $model yii\base\Model */

use yii\widgets\ActiveForm;
use simialbi\yii2\date\Datepicker;

$this->title = 'myForm';
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="my-form">
	<?php $form = ActiveForm::begin(['id' => 'my-form']); ?>
		<?= $form->field($model, 'name')->textInput(['autofocus' => true]) ?>
		<?= $form->field($model, 'birthday')->widget(Datepicker::className(), [
				'format' => 'mm/dd/yyyy',
				'type'   => Datepicker::TYPE_COMPONENT_APPEND
			]) ?>
		<?= $form->field($model, 'email') ?>
	<?php ActiveForm::end(); ?>
</div>
```

## License

**yii2-widget-datetimepicker** is released under MIT license. See bundled [LICENSE](LICENSE) for details.