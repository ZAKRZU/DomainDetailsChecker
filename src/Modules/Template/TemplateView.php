<?php

namespace Zakrzu\DDC\Modules\Template;

use Zakrzu\DDC\App;

use Zakrzu\DDC\Modules\Template\TemplateModule;
use Zakrzu\DDC\Modules\Template\TemplateVariables;

class TemplateView
{

    private string $templateName = "";

    private TemplateVariables $vars;

    public function __construct(string $templateName, array $data = [])
    {
        $this->templateName = $templateName;
        $data["app_version"] = APP::VERSION;
        $this->vars = new TemplateVariables($data);
    }

    public function render(): void
    {
        include TemplateModule::TEMPLATE_LOCATION . $this->templateName;
    }

    public function getTemplate(string $key): string
    {
        return App::$app->getTemplateModule()->getTemplate($key);
    }

    public function getHook(string $key): void
    {
        $path = App::$app->getTemplateModule()->getHookPath($key);
        if (strlen($path) > 0)
            include $path;
    }
}
