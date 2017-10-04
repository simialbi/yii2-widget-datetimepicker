<?php
/**
 * Created by PhpStorm.
 * User: karlen
 * Date: 04.10.2017
 * Time: 14:10
 */

namespace simialbi\yii2\date;


use yii\web\AssetBundle;

/**
 * Asset bundle for DatePicker Widget
 *
 * @author Simon Karlen <simi.albi@gmail.com>
 */
class DatepickerAsset extends AssetBundle {
	/**
	 * @var string the directory that contains the source asset files for this asset bundle.
	 */
	public $sourcePath = '@bower';

	/**
	 * @var array list of CSS files that this bundle contains.
	 */
	public $css = [
		'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.min.css'
	];

	/**
	 * @var array list of JavaScript files that this bundle contains.
	 */
	public $js = [
		'moment/min/moment-with-locales.min.js',
		'eonasdan-bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js'
	];

	/**
	 * @var array list of bundle class names that this bundle depends on.
	 */
	public $depends = [
		'yii\bootstrap\BootstrapPluginAsset'
	];

	/**
	 * @var array the options to be passed to [[AssetManager::publish()]] when the asset bundle
	 * is being published.
	 */
	public $publishOptions = [
		'only'      => [
			'moment/min/*',
			'eonasdan-bootstrap-datetimepicker/build/css/*',
			'eonasdan-bootstrap-datetimepicker/build/js/*'
		],
		'forceCopy' => YII_DEBUG
	];
}