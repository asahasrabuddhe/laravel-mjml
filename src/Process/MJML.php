<?php

namespace Asahasrabuddhe\LaravelMJML\Process;

use Html2Text\Html2Text;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MJML
{
    protected $process;

    protected $view;

    protected $path;

    protected $options;

    public function __construct($view)
    {
        $this->view         = $view;
        $this->path         = storage_path('framework/views/' . sha1($this->view->getPath()) . '.php');
        $this->compiledPath = storage_path('framework/views/' . sha1($this->view->getPath() . '_compiled') . '.php');
    }

    public function buildCmdLineFromConfig()
    {
        return implode(' ', [
            config('mjml.auto_detect_path') ? $this->detectBinaryPath() : config('mjml.path_to_binary'),
            $this->path,
            '-o',
            $this->compiledPath,
        ]);
    }

    public function renderHTML()
    {
        if ($this->isExpired()) {
            $html = $this->view->render();
            File::put($this->path, $html);

            $this->process = new Process($this->buildCmdLineFromConfig());
            $this->process->run();
            // executes after the command finishes
            File::delete($this->path);
            if (! $this->process->isSuccessful()) {
                throw new ProcessFailedException($this->process);
            }
        }

        return new HtmlString(File::get($this->compiledPath));
    }

    public function renderText()
    {
        return new HtmlString(html_entity_decode(preg_replace("/[\r\n]{2,}/", "\n\n", Html2Text::convert($this->renderHTML())), ENT_QUOTES, 'UTF-8'));
    }

    public function detectBinaryPath()
    {
        return base_path('node_modules/.bin/mjml');
    }

    public function isExpired()
    {
        if (! File::exists($this->compiledPath)) {
            return true;
        }

        return File::lastModified($this->view->getPath()) >=
            File::lastModified($this->compiledPath);
    }
}
