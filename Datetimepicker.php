<?php
/**
 * Created by PhpStorm.
 * User: karlen
 * Date: 04.10.2017
 * Time: 14:15
 */

namespace simialbi\yii2\date;

use simialbi\yii2\date\helpers\FormatConverter;
use yii\bootstrap\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\View;
use yii\widgets\InputWidget;
use Yii;

/**
 * Datepicker renders a bootstrap styled `date`, `time` or `datetimepicker` widget.
 *
 * For example to use the datepicker with a [[\yii\base\Model|model]]:
 *
 * ```php
 * echo Datepicker::widget([
 *     'model' => $model,
 *     'attribute' => 'from_date',
 *     //'locale' => 'ru',
 *     //'format' => 'yyyy-MM-dd',
 * ]);
 * ```
 *
 * The following example will use the name property instead:
 *
 * ```php
 * echo Datepicker::widget([
 *     'name'  => 'from_date',
 *     'value'  => $value,
 *     //'locale' => 'ru',
 *     //'format' => 'yyyy-MM-dd',
 * ]);
 * ```
 *
 * You can also use this widget in an [[\yii\widgets\ActiveForm|ActiveForm]] using the [[\yii\widgets\ActiveField::widget()|widget()]]
 * method, for example like this:
 *
 * ```php
 * <?= $form->field($model, 'from_date')->widget(\simialbi\yii2\date\Datepicker::classname(), [
 *     //'locale' => 'ru',
 *     //'format' => 'yyyy-MM-dd',
 * ]) ?>
 * ```
 *
 * @see http://eonasdan.github.io/bootstrap-datetimepicker/
 * @author Simon Karlen <simi.albi@gmail.com>
 */
class Datetimepicker extends InputWidget {
	/**
	 * The markup to render the calendar icon in the date picker button.
	 */
	const CALENDAR_ICON = '<i class="glyphicon glyphicon-calendar"></i>';
	/**
	 * Datepicker rendered as a plain input.
	 */
	const TYPE_INPUT = 1;
	/**
	 * Datepicker with the date picker button rendered as a prepended bootstrap addon component
	 */
	const TYPE_COMPONENT_PREPEND = 2;
	/**
	 * Datepicker with the date picker button rendered as a appended bootstrap addon component
	 */
	const TYPE_COMPONENT_APPEND = 3;
	/**
	 * Datepicker calendar directly rendered inline
	 */
	const TYPE_INLINE = 4;
	/**
	 * Link defines minimum
	 */
	const LINK_MIN = 'min';
	/**
	 * Link defines maximum
	 */
	const LINK_MAX = 'max';

	/**
	 * @var string the markup type of widget markup must be one of the TYPE constants. Defaults to
	 * [[TYPE_COMPONENT_APPEND]]
	 */
	public $type = self::TYPE_COMPONENT_APPEND;

	/**
	 * @var string date, time or datetime format. See momentjs' docs for valid formats. Format also
	 * dictates what components are shown, e.g. MM/dd/YYYY will not display the time picker.
	 * @see http://momentjs.com/docs/#/displaying/format/
	 */
	public $format;

	/**
	 * @var string id of the linked picker
	 */
	public $link;

	/**
	 * @var string defines if linked picker defines min or max value of this picker. Defaults to
	 * [[LINK_MIN]]
	 */
	public $linkType = self::LINK_MIN;

	/**
	 * @var array default client options
	 */
	private $_defaultClientOptions = [
		'stepping'        => 5,
		'useStrict'       => true,
		'showTodayButton' => true
	];

	/**
	 * @var array client options
	 * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
	 * @see also http://eonasdan.github.io/bootstrap-datetimepicker/Options/
	 */
	public $clientOptions = [];

	/**
	 * @inheritdoc
	 */
	public function init() {
		parent::init();

		if (!isset($this->format)) {
			$this->format = Yii::$app->formatter->dateFormat;
		}

		$this->registerTranslations();

		$this->_defaultClientOptions['debug']    = YII_DEBUG;
		$this->_defaultClientOptions['locale']   = strtolower(Yii::$app->language);
		$this->_defaultClientOptions['tooltips'] = [
			'today'           => Yii::t('simialbi/date/datepicker', 'Go to today'),
			'clear'           => Yii::t('simialbi/date/datepicker', 'Clear selection'),
			'close'           => Yii::t('simialbi/date/datepicker', 'Close the picker'),
			'selectMonth'     => Yii::t('simialbi/date/datepicker', 'Select Month'),
			'prevMonth'       => Yii::t('simialbi/date/datepicker', 'Previous Month'),
			'nextMonth'       => Yii::t('simialbi/date/datepicker', 'Next Month'),
			'selectYear'      => Yii::t('simialbi/date/datepicker', 'Select Year'),
			'prevYear'        => Yii::t('simialbi/date/datepicker', 'Previous Year'),
			'nextYear'        => Yii::t('simialbi/date/datepicker', 'Next Year'),
			'selectDecade'    => Yii::t('simialbi/date/datepicker', 'Select Decade'),
			'prevDecade'      => Yii::t('simialbi/date/datepicker', 'Previous Decade'),
			'nextDecade'      => Yii::t('simialbi/date/datepicker', 'Next Decade'),
			'prevCentury'     => Yii::t('simialbi/date/datepicker', 'Previous Century'),
			'nextCentury'     => Yii::t('simialbi/date/datepicker', 'Next Century'),
			'incrementHour'   => Yii::t('simialbi/date/datepicker', 'Increment Hour'),
			'pickHour'        => Yii::t('simialbi/date/datepicker', 'Pick Hour'),
			'decrementHour'   => Yii::t('simialbi/date/datepicker', 'Decrement Hour'),
			'incrementMinute' => Yii::t('simialbi/date/datepicker', 'Increment Minute'),
			'pickMinute'      => Yii::t('simialbi/date/datepicker', 'Pick Minute'),
			'decrementMinute' => Yii::t('simialbi/date/datepicker', 'Decrement Minute'),
			'incrementSecond' => Yii::t('simialbi/date/datepicker', 'Increment Second'),
			'pickSecond'      => Yii::t('simialbi/date/datepicker', 'Pick Second'),
			'decrementSecond' => Yii::t('simialbi/date/datepicker', 'Decrement Second')
		];
	}

	/**
	 * @inheritdoc
	 */
	public function run() {
		parent::run();
		echo $this->renderInput();
		$this->registerPlugin();
	}

	/**
	 * Renders the source input for the DatePicker plugin.
	 *
	 * @return string
	 */
	protected function renderInput() {
		$button  = Html::tag('span', static::CALENDAR_ICON, [
			'class' => 'input-group-addon'
		]);
		$options = $this->options;
		Html::addCssClass($options, 'form-control');

		if ($this->hasModel()) {
			$input = Html::activeTextInput($this->model, $this->attribute, $options);
		} else {
			$input = Html::textInput($this->name, $this->value, $options);
		}
		switch ($this->type) {
			case self::TYPE_INPUT:
				return $input;
			case self::TYPE_COMPONENT_PREPEND:
				return Html::tag('div', $button.$input, [
					'class' => 'input-group'
				]);
			case self::TYPE_COMPONENT_APPEND:
			default:
				return Html::tag('div', $input.$button, [
					'class' => 'input-group'
				]);
			case self::TYPE_INLINE:
				if ($this->hasModel()) {
					$input = Html::activeHiddenInput($this->model, $this->attribute, $options);
				} else {
					$input = Html::hiddenInput($this->name, $this->value, $options);
				}

				return $input;
		}
	}

	/**
	 * Init translations
	 */
	protected function registerTranslations() {
		Yii::$app->i18n->translations['simialbi/date*'] = [
			'class'          => 'yii\i18n\GettextMessageSource',
			'sourceLanguage' => 'en-US',
			'basePath'       => __DIR__.'/messages'
		];
	}

	/**
	 * Registers the assets and builds the required js for the widget
	 */
	protected function registerPlugin() {
		$id   = $this->options['id'];
		$view = $this->getView();

		DatetimepickerAsset::register($view);

		$js = [
			"var dp$id = jQuery('#$id');",
			"dp$id.datetimepicker({$this->getClientOptions()});"
		];
		if (!empty($this->link)) {
			$js[] = <<<JS
jQuery('#{$this->link}').on('dp.change', function (e) {
	dp$id.data('DateTimePicker').{$this->linkType}Date(e.date);
});
JS;
		}

		$view->registerJs(implode("\n", $js), View::POS_READY);
	}

	/**
	 * Get client options as json encoded string
	 *
	 * @return string
	 */
	protected function getClientOptions() {
		if (!empty($this->link)) {
			$this->clientOptions['useCurrent'] = false;
		}

		if ($this->type === static::TYPE_INLINE) {
			$this->clientOptions['inline'] = true;
		}

		if (strncmp($this->format, 'php:', 4) === 0) {
			$this->clientOptions['format'] = FormatConverter::convertDateIcuToMoment(FormatConverter::convertDatePhpToIcu(substr($this->format, 4)));
		} else {
			$this->clientOptions['format'] = FormatConverter::convertDateIcuToMoment($this->format);
		}

		$options = ArrayHelper::merge($this->_defaultClientOptions, $this->clientOptions);

		return Json::encode($options);
	}
}