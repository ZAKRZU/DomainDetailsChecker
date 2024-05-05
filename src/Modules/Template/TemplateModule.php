<?php
namespace Zakrzu\DDC\Modules\Template;

use Zakrzu\DDC\Modules\Module;
use Zakrzu\DDC\Modules\ModuleType;

use Zakrzu\DDC\Modules\Template\TemplateView;

class TemplateModule extends Module {

    const string TEMPLATE_LOCATION = __DIR__ . "/../../../template/";

    private array $templateOverrides;
    private array $templateHooks;

    public function __construct(array $templateOverrides = [], array $templateHooks = [])
    {
        parent::__construct(ModuleType::TEMPLATE);
        $this->templateOverrides = $templateOverrides;
        $this->templateHooks = $templateHooks;
    }

    public function addOverride(string $name, string $value): void
    {
       $this->templateOverrides[$name] = $value;
    }

    public function addHook(string $name, string $value): void
    {
       $this->templateHooks[$name] = $value;
    }

    public function getTemplate(string $key): string
    {
        if (isset($this->templateOverrides[$key]))
            return $this->templateOverrides[$key];
        else
            return TemplateModule::TEMPLATE_LOCATION . $key;
    }

    public function getHookPath(string $key): string
    {
        if (isset($this->templateHooks[$key]))
            return $this->templateHooks[$key];
        else
            return "";
    }

    public function display(TemplateView $template): void
    {
        $template->render();
    }

}
