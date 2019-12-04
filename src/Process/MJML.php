<?php

namespace Asahasrabuddhe\LaravelMJML\Process;

use Html2Text\Html2Text;
use Illuminate\View\View;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class MJML
{
    /**
     * @var Process
     */
    protected $process;

    /**
     * @var View
     */
    protected $view;

    /**
     * @var string
     */
    protected $path;

    /**
     * MJML constructor.
     *
     * @param View $view
     */
    public function __construct($view)
    {
        $this->view = $view;
        $this->path = storage_path('framework/views/' . sha1($this->view->getPath()).sha1(serialize($this->view->getData())) . '.php');
    }

    /**
     * Build the mjml command.
     *
     * @return string
     */
    public function buildCmdLineFromConfig()
    {
        return implode(' ', [
            config('mjml.auto_detect_path') ? $this->detectBinaryPath() : config('mjml.path_to_binary'),
            $this->path,
            '-o',
            $this->compiledPath,
        ]);
    }

    /**
     * Render the html content.
     *
     * @return HtmlString
     *
     * @throws \Throwable
     */
    public function renderHTML()
    {
        $html = $this->view->render();

        File::put($this->path, $html);

        $contentChecksum    = hash('sha256', $html);
        $this->compiledPath = storage_path("framework/views/{$contentChecksum}.php");

        if (! File::exists($this->compiledPath)) {
            $this->process = new Process($this->buildCmdLineFromConfig());
            $this->process->run();

            if (! $this->process->isSuccessful()) {
                throw new ProcessFailedException($this->process);
            }
        }

        return new HtmlString(File::get($this->compiledPath));
    }

    /**
     * Render the text content.
     *
     * @return HtmlString
     *
     * @throws \Html2Text\Html2TextException
     * @throws \Throwable
     */
    public function renderText()
    {
        return new HtmlString(html_entity_decode(preg_replace("/[\r\n]{2,}/", "\n\n", Html2Text::convert($this->renderHTML())), ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Detect the path to the mjml executable.
     *
     * @return string
     */
    public function detectBinaryPath()
    {
        return base_path('node_modules/.bin/mjml');
    }
}
