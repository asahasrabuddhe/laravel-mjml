<?php

namespace Asahasrabuddhe\LaravelMJML\Process;

use Soundasleep\Html2Text;
use Illuminate\Support\Facades\File;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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
    protected $mjml;

    /**
     * @var string
     */
    protected $path;

    /**
     * MJML constructor.
     *
     * @param View|string $mjmlViewOrMjml
     */
    public function __construct($mjmlViewOrMjml)
    {
        if (is_string($mjmlViewOrMjml)) {
            $this->mjmlContent = $mjmlViewOrMjml;
            // Hash combined data and path.  If either change, new pre-compiled file is generated.
            $dataPathChecksum = hash('sha256', $mjmlViewOrMjml);
        } else {
            $this->view = $mjmlViewOrMjml;
            // Hash combined data and path.  If either change, new pre-compiled file is generated.
            $dataPathChecksum = hash('sha256', json_encode([
                'path' => $this->view->getPath(),
                'data' => $this->view->getData(),
            ]));
        }

        $this->path = rtrim(config('view.compiled'), '/') . "/{$dataPathChecksum}.mjml.php";
    }

    /**
     * Build the mjml command.
     *
     * @return string
     */
    public function buildCmdLineFromConfig()
    {
        return implode(' ', [
            config('mjml.node_path'),
            config('mjml.auto_detect_path') ? $this->detectBinaryPath() : config('mjml.path_to_binary'),
            $this->path,
            $this->view ? '--config.filePath=' . dirname($this->view->getPath()) : '',
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
        if ($this->view) {
            $this->mjmlContent = $this->view->render();
        }

        File::put($this->path, $this->mjmlContent);

        $contentChecksum    = hash('sha256', $this->mjmlContent);
        $this->compiledPath = rtrim(config('view.compiled'), '/') . "/{$contentChecksum}.php";

        if (! File::exists($this->compiledPath)) {
            $this->process = Process::fromShellCommandline($this->buildCmdLineFromConfig());
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
        return new HtmlString(html_entity_decode(preg_replace("/[\r\n]{2,}/", "\n\n", Html2Text::convert($this->renderHTML(), ['ignore_errors' => true])), ENT_QUOTES, 'UTF-8'));
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
