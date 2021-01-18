<?php
/**
 * @package yii2-widget-datetimepicker
 * @author Simon Karlen <simi.albi@outlook.com>
 */

namespace simialbi\yii2\date;

use simialbi\yii2\helpers\FormatConverter;
use simialbi\yii2\widgets\InputWidget;
use Yii;
use yii\base\InvalidArgumentException;
use yii\bootstrap4\Html;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\View;

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
 * You can also use this widget in an [[\yii\widgets\ActiveForm|ActiveForm]] using the
 * [[\yii\widgets\ActiveField::widget()|widget()]] method, for example like this:
 *
 * ```php
 * <?= $form->field($model, 'from_date')->widget(\simialbi\yii2\date\Datepicker::classname(), [
 *     //'locale' => 'ru',
 *     //'format' => 'yyyy-MM-dd',
 * ]) ?>
 * ```
 *
 * @see http://eonasdan.github.io/bootstrap-datetimepicker/
 * @author Simon Karlen <simi.albi@outlook.com>
 */
class Datetimepicker extends InputWidget
{
    /**
     * The markup to render the calendar icon in the date picker button.
     */
    const CALENDAR_ICON = '&#x1f4c5';
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
     * @var string date, time or datetime ICU format. Alternatively this can be a string prefixed with `php:`
     * representing a format that can be recognized by the PHP date()-function.
     * Format also dictates what components are shown, e.g. MM/dd/yyyy will not display the time picker.
     * @see http://userguide.icu-project.org/formatparse/datetime#TOC-Date-Time-Format-Syntax
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
     * {@inheritdoc}
     */
    public $options = [];

    /**
     * @var array Input group addon options. This value is ignored when type equals [[TYPE_INPUT]] or [[TYPE_INLINE]]
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $inputGroupAddonOptions = [
        'data' => [
            'toggle' => 'datetimepicker'
        ]
    ];

    /**
     * @var array The input group button options. This value is ignored when type equals
     * [[TYPE_INPUT]] or [[TYPE_INLINE]]
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $buttonOptions = [
        'class' => ['btn', 'btn-outline-secondary'],
        'type' => 'button'
    ];

    /**
     * @var array default client options
     */
    private $_defaultClientOptions = [
        'stepping' => 5,
        'useStrict' => true,
        'showTodayButton' => true
    ];

    /**
     * {@inheritDoc}
     * @throws \ReflectionException
     */
    public function init()
    {
        parent::init();

        if (!isset($this->format)) {
            $this->format = Yii::$app->formatter->dateFormat;
        }
        if ($this->hasModel()) {
            try {
                $this->model->{$this->attribute} = Yii::$app->formatter->asDatetime(
                    $this->model->{$this->attribute},
                    $this->format
                );
            } catch (InvalidArgumentException $e) {
                $this->model->{$this->attribute} = null;
            }
            if (false === strtotime($this->model->{$this->attribute})) {
                $this->model->{$this->attribute} = null;
            }
        } else {
            try {
                $this->value = Yii::$app->formatter->asDatetime($this->value, $this->format);
            } catch (InvalidArgumentException $e) {
                $this->value = null;
            }
            if (false === strtotime($this->value)) {
                $this->value = null;
            }
        }

        $this->registerTranslations();

        $this->_defaultClientOptions['debug'] = YII_DEBUG;
        $this->_defaultClientOptions['locale'] = strtolower(Yii::$app->language);
        $this->_defaultClientOptions['timeZone'] = Yii::$app->timeZone;
        $this->_defaultClientOptions['tooltips'] = [
            'today' => Yii::t('simialbi/date/datepicker', 'Go to today'),
            'clear' => Yii::t('simialbi/date/datepicker', 'Clear selection'),
            'close' => Yii::t('simialbi/date/datepicker', 'Close the picker'),
            'selectMonth' => Yii::t('simialbi/date/datepicker', 'Select Month'),
            'prevMonth' => Yii::t('simialbi/date/datepicker', 'Previous Month'),
            'nextMonth' => Yii::t('simialbi/date/datepicker', 'Next Month'),
            'selectYear' => Yii::t('simialbi/date/datepicker', 'Select Year'),
            'prevYear' => Yii::t('simialbi/date/datepicker', 'Previous Year'),
            'nextYear' => Yii::t('simialbi/date/datepicker', 'Next Year'),
            'selectDecade' => Yii::t('simialbi/date/datepicker', 'Select Decade'),
            'prevDecade' => Yii::t('simialbi/date/datepicker', 'Previous Decade'),
            'nextDecade' => Yii::t('simialbi/date/datepicker', 'Next Decade'),
            'prevCentury' => Yii::t('simialbi/date/datepicker', 'Previous Century'),
            'nextCentury' => Yii::t('simialbi/date/datepicker', 'Next Century'),
            'incrementHour' => Yii::t(
                'simialbi/date/datepicker',
                'Increment of {delta, plural, =1{one hour} other{# hours}}',
                [
                    'delta' => 1
                ]
            ),
            'pickHour' => Yii::t('simialbi/date/datepicker', 'Pick Hour'),
            'decrementHour' => Yii::t(
                'simialbi/date/datepicker',
                'Decrement of {delta, plural, =1{one hour} other{# hours}}',
                [
                    'delta' => 1
                ]
            ),
            'incrementMinute' => Yii::t(
                'simialbi/date/datepicker',
                'Increment of {delta, plural, =1{one minute} other{# minutes}}',
                [
                    'delta' => ArrayHelper::getValue(
                        $this->clientOptions,
                        'stepping',
                        $this->_defaultClientOptions['stepping']
                    )
                ]
            ),
            'pickMinute' => Yii::t('simialbi/date/datepicker', 'Pick Minute'),
            'decrementMinute' => Yii::t(
                'simialbi/date/datepicker',
                'Decrement of {delta, plural, =1{one minute} other{# minutes}}',
                [
                    'delta' => ArrayHelper::getValue(
                        $this->clientOptions,
                        'stepping',
                        $this->_defaultClientOptions['stepping']
                    )
                ]
            ),
            'incrementSecond' => Yii::t(
                'simialbi/date/datepicker',
                'Increment of {delta, plural, =1{one second} other{# seconds}}',
                [
                    'delta' => 1
                ]
            ),
            'pickSecond' => Yii::t('simialbi/date/datepicker', 'Pick Second'),
            'decrementSecond' => Yii::t(
                'simialbi/date/datepicker',
                'Decrement of {delta, plural, =1{one second} other{# seconds}}',
                [
                    'delta' => 1
                ]
            ),
            'togglePeriod' => Yii::t('simialbi/date/datepicker', 'Toggle Period'),
            'selectTime' => Yii::t('simialbi/date/datepicker', 'Select Time')
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        parent::run();
        $html = $this->renderInput();
        $this->registerPlugin();

        return $html;
    }

    /**
     * Renders the source input for the DatePicker plugin.
     *
     * @return string
     */
    protected function renderInput()
    {
        $options = $this->options;
        $id = ArrayHelper::getValue($options, 'id');
        $tag = ArrayHelper::remove($inputGroupAddonOptions, 'tag', 'div');
        $inputGroupAddonOptions = $this->inputGroupAddonOptions;
        $inputGroupAddonOptions['data']['target'] = '#' . $id;
        $options['data']['target'] = '#' . $id;
        $buttonOptions = $this->buttonOptions;
        $buttonIcon = ArrayHelper::remove($buttonOptions, 'icon', self::CALENDAR_ICON);
        Html::addCssClass($options, 'form-control');

        if ($this->type === self::TYPE_INPUT) {
            $options['id'] = $id;
            $options['data']['toggle'] = 'datetimepicker';
        }
        if ($this->hasModel()) {
            $input = Html::activeTextInput($this->model, $this->attribute, $options);
        } else {
            $input = Html::textInput($this->name, $this->value, $options);
        }
        switch ($this->type) {
            case self::TYPE_INPUT:
                return $input;
            case self::TYPE_COMPONENT_PREPEND:
                Html::addCssClass($inputGroupAddonOptions, 'input-group-prepend');
                $addon = Html::tag($tag, Html::button($buttonIcon, $buttonOptions), $inputGroupAddonOptions);
                return Html::tag('div', $addon . $input, [
                    'class' => 'input-group',
                    'id' => $id . '-group'
                ]);
            case self::TYPE_COMPONENT_APPEND:
            default:
                Html::addCssClass($inputGroupAddonOptions, 'input-group-append');
                $addon = Html::tag($tag, Html::button($buttonIcon, $buttonOptions), $inputGroupAddonOptions);
                return Html::tag('div', $input . $addon, [
                    'class' => 'input-group',
                    'id' => $id . '-group'
                ]);
            case self::TYPE_INLINE:
                $options['id'] = $id;
                if ($this->hasModel()) {
                    $input = Html::activeHiddenInput($this->model, $this->attribute, $options);
                } else {
                    $input = Html::hiddenInput($this->name, $this->value, $options);
                }

                return $input;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function registerPlugin($pluginName = 'datetimepicker', $selector = null)
    {
        $id = $this->options['id'];
        $view = $this->getView();

        if ($this->type !== self::TYPE_INLINE && $this->type !== self::TYPE_INPUT) {
            $id .= '-group';
        }

        if (empty($selector)) {
            $selector = '#' . $id;
        }

        DatetimepickerAsset::register($view);

        $js = [
            "jQuery('$selector').on('dp.show', function () { var dtp = jQuery(this); window.setTimeout(function () { dtp.trigger('change.datetimepicker'); }, 200); });",
            "jQuery('$selector').$pluginName({$this->getClientOptions()});"
        ];
        if (!empty($this->link)) {
            $js[] = <<<JS
jQuery('#{$this->link}').on('change.datetimepicker', function (e) {
	if (!e.date) {
		return;
	}
	jQuery('#$id').$pluginName('{$this->linkType}Date', e.date);
});
JS;
        }

        $view->registerJs(implode("\n", $js), View::POS_READY);
        $this->registerClientEvents();
    }

    /**
     * Get client options as json encoded string
     *
     * @return string
     */
    protected function getClientOptions()
    {
        if (!empty($this->link)) {
            $this->clientOptions['useCurrent'] = false;
        }

        if ($this->type === static::TYPE_INLINE) {
            $this->clientOptions['inline'] = true;
        }

        if (strncmp($this->format, 'php:', 4) === 0) {
            $this->clientOptions['format'] = FormatConverter::convertDateIcuToMoment(
                FormatConverter::convertDatePhpToIcu(substr($this->format, 4))
            );
        } else {
            $this->clientOptions['format'] = FormatConverter::convertDateIcuToMoment($this->format);
        }

        $options = ArrayHelper::merge($this->_defaultClientOptions, $this->clientOptions);

        return Json::encode($options);
    }
}