<?php namespace Verbant\Livewire\Controllers;

use Request;
use Lang;
use Flash;
use Backend;
use BackendMenu;
use Config;
use System\Helpers\DateTime;
use Backend\Classes\Controller;
use Backend\Traits\InspectableContainer;
use Cms\Widgets\TemplateList;
use Cms\Classes\Theme;
use Verbant\Livewire\Widgets\ComponentList;
use Verbant\Livewire\Classes\Component;

/**
 *
 * @package verbant\livewire
 * @author 
 */
class Index extends Controller
{
  use InspectableContainer;

  /**
   * @var Cms\Classes\Theme
   */
  protected $theme;
  /**
   * Constructor.
   */
  public function __construct()
  {
    parent::__construct();

    BackendMenu::setContext('Verbant.Livewire', 'livewire', 'components');

    $this->bodyClass = 'compact-container';
    $this->pageTitle = 'verbant.livewire::lang.plugin.name';

    if (!($theme = Theme::getEditTheme())) {
      throw new ApplicationException(Lang::get('cms::lang.theme.edit.not_found'));
    }
    $this->theme = $theme;

    // new ComponentList($this, 'componentList');
    new TemplateList($this, 'componentList', function () use ($theme) {
      return Component::listInTheme($theme, true);
    });
  }

  public function index()
  {
    $this->bodyClass = 'compact-container';
    $this->pageTitle = 'verbant.livewire::lang.menu.livewire.label';
    $this->pageTitleTemplate = '%s '.Lang::get($this->pageTitle);
    $this->addJs('/modules/cms/assets/js/winter.cmspage.js', 'core');
    $this->addJs('/modules/cms/assets/js/winter.dragcomponents.js', 'core');
    $this->addJs('/modules/cms/assets/js/winter.tokenexpander.js', 'core');
    $this->addCss('/modules/cms/assets/css/winter.components.css', 'core');
    $this->addJs('/modules/backend/formwidgets/codeeditor/assets/js/build-min.js', 'core');
    $this->addJs('/plugins/verbant/livewire/assets/js/livewire.js', 'livewire');
  }

  /**
   * Opens an existing template from the index page
   * @return array
   */
  public function index_onOpenTemplate()
  {
    $this->validateRequestTheme();

    $type = Request::input('type');
    $template = $this->loadTemplate($type, Request::input('path'));
    $widget = $this->makeTemplateFormWidget($type, $template);

    $this->vars['templatePath'] = Request::input('path');
    $this->vars['lastModified'] = DateTime::makeCarbon($template->mtime);
    $this->vars['canCommit'] = $this->canCommitOrReset($template);
    $this->vars['canReset'] = $this->canCommitOrReset($template);

    $this->addViewPath("~/modules/cms/controllers/index");
    return [
      'tabTitle' => $this->getTabTitle($type, $template),
      'tab'      => $this->makePartial('form_page', [
        'form'          => $widget,
        'templateType'  => $type,
        'templateTheme' => $this->theme->getDirName(),
        'templateMtime' => $template->mtime
      ])
    ];
  }
  /**
   * Create a new template
   * @return array
   */
  public function onCreateTemplate()
  {
    $type = Request::input('type');
    $template = $this->createTemplate($type);
    $widget = $this->makeTemplateFormWidget($type, $template);

    $this->vars['templatePath'] = '';
    $this->vars['canCommit'] = $this->canCommitOrReset($template);
    $this->vars['canReset'] = $this->canCommitOrReset($template);

    $this->addViewPath("~/modules/cms/controllers/index");
    return [
      'tabTitle' => $this->getTabTitle($type, $template),
      'tab'      => $this->makePartial('form_page', [
          'form'          => $widget,
          'templateType'  => $type,
          'templateTheme' => $this->theme->getDirName(),
          'templateMtime' => null
      ])
    ];
  }
  public function onSave()
  {
    $this->validateRequestTheme();
    $type = Request::input('templateType');
    $templatePath = trim(Request::input('templatePath'));
    $template = $templatePath ? $this->loadTemplate($type, $templatePath) : $this->createTemplate($type);
    $formWidget = $this->makeTemplateFormWidget($type, $template);

    $saveData = $formWidget->getSaveData();
    $postData = post();
    $templateData = [];

    $settings = array_get($saveData, 'settings', []) + Request::input('settings', []);
    $settings = $this->upgradeSettings($settings, $template->settings);

    if ($settings) {
        $templateData['settings'] = $settings;
    }

    $fields = ['markup', 'code', 'fileName', 'content'];

    foreach ($fields as $field) {
        if (array_key_exists($field, $saveData)) {
            $templateData[$field] = $saveData[$field];
        }
        elseif (array_key_exists($field, $postData)) {
            $templateData[$field] = $postData[$field];
        }
    }

    if (!empty($templateData['markup']) && Config::get('cms.convertLineEndings', false) === true) {
        $templateData['markup'] = $this->convertLineEndings($templateData['markup']);
    }

    if (!empty($templateData['code']) && Config::get('cms.convertLineEndings', false) === true) {
        $templateData['code'] = $this->convertLineEndings($templateData['code']);
    }

    if (
        !Request::input('templateForceSave') && $template->mtime
        && Request::input('templateMtime') != $template->mtime
    ) {
        throw new ApplicationException('mtime-mismatch');
    }

    $template->attributes = [];
    $template->fill($templateData);
    $template->save();
    Flash::success(Lang::get('cms::lang.template.saved'));

    return $this->getUpdateResponse($template, $type);
  }
  /**
   * Creates a new template of a given type
   * @param string $type
   * @return mixed
   */
  protected function createTemplate($type)
  {
    $class = Component::class;

    if (!($template = $class::inTheme($this->theme))) {
        throw new ApplicationException(Lang::get('cms::lang.template.not_found'));
    }
    return $template;
  }
  
  protected function makeTemplateFormWidget($type, $template, $alias = null)
  {
    $widgetConfig = $this->makeConfig('~/modules/cms/classes/partial/fields.yaml');
    $ext = pathinfo($template->fileName, PATHINFO_EXTENSION);
    $lang = 'php';
    if (array_get($widgetConfig->secondaryTabs, 'fields.markup.type') === 'codeeditor') {
      switch ($ext) {
        case 'htm':
          $lang = 'twig';
          break;
        case 'html':
          $lang = 'html';
          break;
      }

      $widgetConfig->model = $template;
      $widgetConfig->alias = $alias ?: 'form'.studly_case($type).md5($template->exists ? $template->getFileName() : uniqid());

      return $this->makeWidget('Backend\Widgets\Form', $widgetConfig);
    } 
  }

  /**
   * Returns an existing template of a given type
   * @param string $type
   * @param string $path
   * @return mixed
   */
  protected function loadTemplate($type, $path)
  {
    $class = Component::class;
    if (!($template = call_user_func([$class, 'load'], $this->theme, $path))) {
        throw new ApplicationException(Lang::get('cms::lang.template.not_found'));
    }
    return $template;
  }

  /**
   * Check to see if the provided template can be committed
   * Only available in debug mode, the DB layer must be enabled, and the template must exist in the database
   *
   * @param CmsObject $template
   * @return boolean
   */
  protected function canCommitOrReset($template)
  {
    if ($template instanceof Cms\Contracts\CmsObject === false) {
      return false;
    }

    $result = false;

    if (Config::get('app.debug', false) &&
      Theme::databaseLayerEnabled() &&
      $this->getThemeDatasource()->sourceHasModel('database', $template)
    ) {
      $result = true;
    }

    return $result;
  }
    /**
     * Returns the text for a template tab
     * @param string $type
     * @param string $template
     * @return string
     */
    protected function getTabTitle($type, $template)
    {
      $result = $template->getBaseFileName();
      if (!$result) {
        $result = Lang::get('verbant.livewire::lang.component.new');
      }
      return $result;
  }
  /**
   * Get the response to return in an AJAX request that updates a template
   *
   * @param object $template The template that has been affected
   * @param string $type The type of template being affected
   * @return array $result;
   */
  protected function getUpdateResponse($template, string $type)
  {
    $result = [
      'templatePath'  => $template->fileName,
      'templateMtime' => $template->mtime,
      'tabTitle'      => $this->getTabTitle($type, $template)
    ];

    $result['canCommit'] = $this->canCommitOrReset($template);
    $result['canReset'] = $this->canCommitOrReset($template);

    return $result;
  }

  /**
   * Validate that the current request is within the active theme
   * @return void
   */
  protected function validateRequestTheme()
  {
    if ($this->theme->getDirName() != Request::input('theme')) {
      throw new ApplicationException(Lang::get('cms::lang.theme.edit.not_match'));
    }
  }
  /**
   * Processes the component settings so they are ready to be saved.
   * @param array $settings The new settings for this template.
   * @param array $prevSettings The previous settings for this template.
   * @return array
   */
  protected function upgradeSettings($settings, $prevSettings)
  {
    /*
      * Handle component usage
      */
    $componentProperties = post('component_properties');
    $componentNames = post('component_names');
    $componentAliases = post('component_aliases');

    if ($componentProperties !== null) {
      if ($componentNames === null || $componentAliases === null) {
        throw new ApplicationException(Lang::get('cms::lang.component.invalid_request'));
      }

      $count = count($componentProperties);
      if (count($componentNames) != $count || count($componentAliases) != $count) {
        throw new ApplicationException(Lang::get('cms::lang.component.invalid_request'));
      }

      for ($index = 0; $index < $count; $index++) {
        $componentName = $componentNames[$index];
        $componentAlias = $componentAliases[$index];

        $isSoftComponent = (substr($componentAlias, 0, 1) === '@');
        $componentName = ltrim($componentName, '@');
        $componentAlias = ltrim($componentAlias, '@');

        if ($componentAlias !== $componentName) {
          $section = $componentName . ' ' . $componentAlias;
        } else {
          $section = $componentName;
        }
        if ($isSoftComponent) {
          $section = '@' . $section;
        }

        $properties = json_decode($componentProperties[$index], true);
        unset($properties['oc.alias'], $properties['inspectorProperty'], $properties['inspectorClassName']);

        if (!$properties) {
          $oldComponentSettings = array_key_exists($section, $prevSettings['components'])
            ? $prevSettings['components'][$section]
            : null;
          if ($isSoftComponent && $oldComponentSettings) {
            $settings[$section] = $oldComponentSettings;
          } else {
            $settings[$section] = $properties;
          }
        } else {
          $settings[$section] = $properties;
        }
      }
    }
  }
}
