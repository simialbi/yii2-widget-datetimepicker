<?php
/**
 * @package yii2-simialbi-base
 * @author Simon Karlen <simi.albi@gmail.com>
 */

namespace simialbi\yii2\date;

use simialbi\yii2\web\AssetBundle;

/**
 * Asset bundle for DatePicker Widget
 *
 * @author Simon Karlen <simi.albi@gmail.com>
 */
class DatetimepickerAsset extends AssetBundle {
	/**
	 * @var string the directory that contains the source asset files for this asset bundle.
	 */
	public $sourcePath = '@bower/tempusdominus-bootstrap-4/build';

	/**
	 * @var array list of CSS files that this bundle contains.
	 */
	public $css = [
		'css/tempusdominus-bootstrap-4.min.css'
	];

	/**
	 * @var array list of JavaScript files that this bundle contains.
	 */
	public $js = [
		'js/tempusdominus-bootstrap-4.min.js'
	];

	/**
	 * @var array list of bundle class names that this bundle depends on.
	 */
	public $depends = [
		'simialbi\yii2\web\MomentAsset',
		'yii\bootstrap\BootstrapPluginAsset'
	];

	/**
	 * @var array the options to be passed to [[AssetManager::publish()]] when the asset bundle
	 * is being published.
	 */
	public $publishOptions = [
		'forceCopy' => YII_DEBUG,
		'only'      => [
			'css/*',
			'js/*'
		]
	];
}