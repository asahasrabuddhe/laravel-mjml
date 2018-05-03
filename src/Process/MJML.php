<?php

namespace Asahasrabuddhe\LaravelMJML\Process;

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
        $this->view = $view;
        $this->path = storage_path('framework/views/' . sha1($this->view->getPath()) . '.php');
    }

    public function buildCmdLineFromConfig()
    {
        return implode(' ', [
            config('mjml.auto_detect_path') ? $this->detectBinaryPath() : config('mjml.path_to_binary'),
            $this->path,
            '-o',
            $this->path,
        ]);
    }

    public function render()
    {
        $html = $this->view->render();
        File::put($this->path, $html);

        $this->process = new Process($this->buildCmdLineFromConfig());
        $this->process->run();
        // executes after the command finishes
        if (! $this->process->isSuccessful()) {
            throw new ProcessFailedException($this->process);
        }

        return new HtmlString(File::get($this->path));
    }

    public function detectBinaryPath()
    {
        return base_path('node_modules/.bin/mjml');
    }
}
